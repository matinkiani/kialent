<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // ۱. فرم دستورکار بسته‌بندی (جدید)
            Action::make('downloadPackagingForm')
                ->label('دستورکار بسته‌بندی')
                ->icon('heroicon-o-archive-box')
                ->color('success')
                ->openUrlInNewTab()
                ->url(fn (Order $record): string => route('orders.packaging-form.download', $record)),

            // ۲. فرم تحویل مقطعی
            Action::make('downloadDeliveryForm')
                ->label('فرم تحویل مقطعی')
                ->icon('heroicon-o-truck')
                ->color('warning')
                ->openUrlInNewTab()
                ->url(fn (Order $record): string => route('orders.delivery-form.download', $record)),

            // ۳. فرم دستورکار تولید
            Action::make('downloadProductionForm')
                ->label('دستورکار تولید')
                ->icon('heroicon-o-clipboard-document-list')
                ->color('info')
                ->openUrlInNewTab()
                ->url(fn (Order $record): string => route('orders.production-form.download', $record)),

            // ۴. فرم جامع مدیریتی
            Action::make('downloadManagementForm')
                ->label('فرم مدیریتی')
                ->icon('heroicon-o-document-chart-bar')
                ->color('gray')
                ->openUrlInNewTab()
                ->url(fn (Order $record): string => route('orders.management-form.download', $record)),

            DeleteAction::make(),
        ];
    }
}
