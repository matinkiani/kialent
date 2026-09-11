<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('مشخصات سفارش')
                    ->columns(4)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('order_number')
                            ->label('شماره سفارش')
                            ->weight('bold')
                            ->badge()
                            ->color('primary'),
                        TextEntry::make('customer.name')
                            ->label('مشتری')
                            ->weight('bold'),
                        TextEntry::make('ordered_at')
                            ->label('تاریخ سفارش')
                            ->formatStateUsing(fn ($state): string => $state ? jalali($state)->format('Y/m/d') : '---'),
                        TextEntry::make('status')
                            ->label('وضعیت')
                            ->badge(),
                        TextEntry::make('priority')
                            ->label('اولویت')
                            ->badge(),
                        TextEntry::make('customer.phone')
                            ->label('تلفن مشتری')
                            ->placeholder('ثبت نشده'),
                        TextEntry::make('customer.address')
                            ->label('آدرس مشتری')
                            ->columnSpan(1)
                            ->placeholder('ثبت نشده'),
                        TextEntry::make('total_weight')
                            ->label('وزن کل سفارش')
                            ->state(fn (Order $record): string => self::formatWeight(self::totalWeight($record)))
                            ->suffix(' کیلوگرم')
                            ->weight('bold'),
                        TextEntry::make('payment_notes')
                            ->label('یادداشت پرداخت')
                            ->columnSpanFull()
                            ->placeholder('ثبت نشده'),
                    ]),

                Section::make('اقلام سفارش و وضعیت ارسال')
                    ->description('جمع ارسال‌ها شامل تمام حواله‌های ثبت‌شده برای این سفارش است')
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('orderItems')
                            ->label('')
                            ->state(fn (Order $record): array => self::itemStates($record))
                            ->columns(6)
                            ->schema([
                                TextEntry::make('product_name')
                                    ->label('محصول')
                                    ->weight('bold')
                                    ->columnSpan(2),
                                TextEntry::make('weight')
                                    ->label('وزن')
                                    ->formatStateUsing(fn (string $state): string => self::formatWeight($state))
                                    ->suffix(' کیلوگرم'),
                                TextEntry::make('ordered_quantity')
                                    ->label('کل سفارش')
                                    ->formatStateUsing(fn (int $state): string => number_format($state))
                                    ->suffix(' دست'),
                                TextEntry::make('total_shipped')
                                    ->label('جمع ارسال‌شده')
                                    ->formatStateUsing(fn (int $state): string => number_format($state))
                                    ->suffix(' دست')
                                    ->color('success')
                                    ->weight('bold'),
                                TextEntry::make('remaining_quantity')
                                    ->label('باقی‌مانده')
                                    ->formatStateUsing(fn (int $state): string => number_format($state))
                                    ->suffix(' دست')
                                    ->color(fn (int $state): string => $state === 0 ? 'success' : 'warning')
                                    ->weight('bold'),
                            ]),
                    ]),
            ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function itemStates(Order $record): array
    {
        $record->loadMissing([
            'customer',
            'orderItems.product',
            'orderItems.shipmentItems',
        ]);

        return $record->orderItems
            ->map(function ($orderItem): array {
                $totalShipped = $orderItem->shipmentItems->sum('shipped_quantity');

                return [
                    'product_name' => $orderItem->product->name,
                    'ordered_quantity' => (int) $orderItem->quantity,
                    'total_shipped' => (int) $totalShipped,
                    'remaining_quantity' => (int) $orderItem->quantity - (int) $totalShipped,
                    'weight' => (string) ((float) ($orderItem->product->weight ?? 0) * (int) $orderItem->quantity),
                ];
            })
            ->values()
            ->all();
    }

    private static function totalWeight(Order $record): float
    {
        $record->loadMissing('orderItems.product');

        return (float) $record->orderItems->sum(
            fn ($orderItem): float => (float) ($orderItem->product->weight ?? 0) * (int) $orderItem->quantity,
        );
    }

    private static function formatWeight(float|string $weight): string
    {
        return number_format((float) $weight / 1000, 3);
    }
}
