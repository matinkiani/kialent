<?php

namespace App\Filament\Resources\Shipments\Schemas;

use App\Models\Shipment;
use App\Models\ShipmentItem;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ShipmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('مشخصات حواله خروج')
                    ->description('اطلاعات لجستیک، باربری و مقصد محموله')
                    ->columns(4)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('shipment_number')
                            ->label('شماره حواله')
                            ->weight('bold')
                            ->badge()
                            ->color('primary'),
                        TextEntry::make('order.order_number')
                            ->label('شماره سفارش مرجع')
                            ->weight('bold'),
                        TextEntry::make('order.customer.name')
                            ->label('مشتری')
                            ->weight('bold')
                            ->placeholder('ثبت نشده'),
                        TextEntry::make('shipped_at')
                            ->label('تاریخ خروج از کارخانه')
                            ->formatStateUsing(fn ($state): string => $state ? jalali($state)->format('Y/m/d') : '---'),
                        TextEntry::make('driver_name')
                            ->label('نام راننده / شرکت باربری'),
                        TextEntry::make('vehicle_plate')
                            ->label('شماره پلاک خودرو')
                            ->placeholder('ثبت نشده'),
                        TextEntry::make('total_master_boxes')
                            ->label('تعداد کل کارتن مادر')
                            ->formatStateUsing(fn (int $state): string => number_format($state)),
                        TextEntry::make('total_weight')
                            ->label('وزن کل محموله')
                            ->formatStateUsing(fn (float|string $state): string => number_format((float) $state, 3))
                            ->suffix(' کیلوگرم')
                            ->weight('bold'),
                        TextEntry::make('destination')
                            ->label('آدرس دقیق مقصد')
                            ->columnSpanFull()
                            ->placeholder('ثبت نشده'),
                    ]),

                Section::make('اقلام محموله (حواله خروج)')
                    ->description('لیست محصولاتی که در این محموله بارگیری و ارسال شده‌اند')
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('shipmentItems')
                            ->label('')
                            ->state(fn (Shipment $record): array => self::itemStates($record))
                            ->columns(6)
                            ->schema([
                                TextEntry::make('product_name')
                                    ->label('محصول')
                                    ->weight('bold')
                                    ->columnSpan(2),
                                TextEntry::make('ordered_quantity')
                                    ->label('تعداد سفارش')
                                    ->formatStateUsing(fn (int $state): string => number_format($state))
                                    ->suffix(' دست'),
                                TextEntry::make('shipped_quantity')
                                    ->label('مقدار ارسالی')
                                    ->formatStateUsing(fn (int $state): string => number_format($state))
                                    ->suffix(' دست')
                                    ->color('success')
                                    ->weight('bold'),
                                TextEntry::make('remaining_quantity')
                                    ->label('تعداد باقیمانده')
                                    ->formatStateUsing(fn (int $state): string => number_format($state))
                                    ->suffix(' دست')
                                    ->color(fn (int $state): string => $state === 0 ? 'success' : 'warning')
                                    ->weight('bold'),
                                TextEntry::make('weight')
                                    ->label('وزن')
                                    ->formatStateUsing(fn (string $state): string => number_format((float) $state, 3))
                                    ->suffix(' کیلوگرم'),
                            ]),
                    ]),
            ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function itemStates(Shipment $record): array
    {
        $record->loadMissing([
            'order.customer',
            'shipmentItems.orderItem.product',
        ]);

        return $record->shipmentItems
            ->map(function (ShipmentItem $shipmentItem): array {
                $product = $shipmentItem->orderItem?->product;

                $shippedQuantity = (int) $shipmentItem->shipped_quantity;
                $orderedQuantity = (int) ($shipmentItem->orderItem?->quantity ?? 0);

                return [
                    'product_name' => $product?->name ?? '---',
                    'ordered_quantity' => $orderedQuantity,
                    'shipped_quantity' => $shippedQuantity,
                    'remaining_quantity' => $orderedQuantity - $shippedQuantity,
                    'weight' => (string) round(((float) ($product?->weight ?? 0) * $shippedQuantity) / 1000, 3),
                ];
            })
            ->values()
            ->all();
    }
}
