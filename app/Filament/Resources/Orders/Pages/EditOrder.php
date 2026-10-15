<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    /**
     * وضعیت سفارش در پایگاه داده، درست قبل از ذخیره‌ی فرم.
     */
    protected ?OrderStatus $statusBeforeSave = null;

    protected function beforeSave(): void
    {
        $this->statusBeforeSave = $this->getRecord()->getOriginal('status');
    }

    protected function afterSave(): void
    {
        $submittedStatus = $this->data['status'] ?? null;
        $submittedValue = $submittedStatus instanceof OrderStatus
            ? $submittedStatus->value
            : $submittedStatus;

        // هر بار که فرم سفارش ذخیره شود، وضعیت خودکار به «تغییر یافته» تغییر می‌کند؛
        // مگر اینکه کاربر خودش وضعیت جدیدی در همین ذخیره انتخاب کرده باشد که همان محترم شمرده می‌شود.
        if ($submittedValue !== $this->statusBeforeSave?->value) {
            return;
        }

        $this->getRecord()->update(['status' => OrderStatus::Modified]);
    }

    protected function getHeaderActions(): array
    {
        return [
            // دانلود یک‌جا چند فرم به دلخواه؛ هر فرم در صفحه جداگانه چاپ می‌شود
            OrderResource::downloadFormsAction(),

            Action::make('activities')
                ->label('تاریخچه تغییرات')
                ->icon('heroicon-o-clock')
                ->color('gray')
                ->url(fn (Order $record): string => ListOrderActivities::getUrl(['record' => $record]))
                ->visible(fn (): bool => (bool) auth()->user()?->can('View:ActivityHistory')),

            // سفارش دارای حواله خروج قابل حذف نیست؛ دکمه اصلاً نمایش داده نمی‌شود
            DeleteAction::make()
                ->visible(fn (Order $record): bool => $record->shipments()->doesntExist()),
        ];
    }
}
