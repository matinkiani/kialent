<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

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

            EditAction::make(),
        ];
    }
}
