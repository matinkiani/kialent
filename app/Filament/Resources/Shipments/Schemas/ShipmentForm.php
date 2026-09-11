<?php

namespace App\Filament\Resources\Shipments\Schemas;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PackagingItem;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ShipmentForm
{
    public static function configure(Schema $schema): Schema
    {
        $sumShipmentWeights = function (array $items): float {
            $totalWeight = 0.0;

            foreach ($items as $item) {
                $totalWeight += ((float) ($item['product_weight'] ?? 0) * (int) ($item['shipped_quantity'] ?? 0)) / 1000;
            }

            return round($totalWeight, 3);
        };

        $calculateTotalWeight = function (Get $get, Set $set) use ($sumShipmentWeights): void {
            $items = $get('shipmentItems');
            $items = is_array($items) ? $items : [];

            $set('total_weight', $sumShipmentWeights($items));
        };

        $updateShipmentItemWeight = function (Get $get, Set $set, ?string $state = null) use ($sumShipmentWeights): void {
            $shippedQuantity = $state ?? $get('shipped_quantity');
            $productWeight = $get('product_weight');

            if (is_numeric($productWeight) && is_numeric($shippedQuantity)) {
                $set('weight', round(((float) $productWeight * (int) $shippedQuantity) / 1000, 3));
            }

            $items = $get('../../shipmentItems');
            $items = is_array($items) ? $items : [];

            $set('../../total_weight', $sumShipmentWeights($items));
        };

        $resolveQuantityPerBoxForOrderItem = function (Get $get): ?int {
            $orderItemId = $get('order_item_id');

            if (! is_numeric($orderItemId)) {
                return null;
            }

            $orderItem = OrderItem::query()
                ->with(['order:id,packaging_id', 'product:id'])
                ->find((int) $orderItemId);

            if (! $orderItem) {
                return null;
            }

            return PackagingItem::quantityPerBox($orderItem->order->packaging_id, $orderItem->product_id);
        };

        // دست → کارتن (گرد به بالا) و به‌روزرسانی وزن
        $syncShipmentBoxFromQuantity = function (Get $get, Set $set) use ($resolveQuantityPerBoxForOrderItem, $updateShipmentItemWeight): void {
            $quantityPerBox = $resolveQuantityPerBoxForOrderItem($get);
            $shippedQuantity = $get('shipped_quantity');

            if ($quantityPerBox !== null && is_numeric($shippedQuantity)) {
                $set('box_quantity', (int) ceil($shippedQuantity / $quantityPerBox));
            }

            $updateShipmentItemWeight($get, $set);
        };

        // کارتن → دست (ضرب دقیق) و به‌روزرسانی وزن بر اساس دستِ جدید
        $syncShipmentQuantityFromBox = function (Get $get, Set $set) use ($resolveQuantityPerBoxForOrderItem, $updateShipmentItemWeight): void {
            $quantityPerBox = $resolveQuantityPerBoxForOrderItem($get);
            $boxQuantity = $get('box_quantity');

            if ($quantityPerBox !== null && is_numeric($boxQuantity)) {
                $set('shipped_quantity', (int) $boxQuantity * $quantityPerBox);
            }

            $updateShipmentItemWeight($get, $set);
        };

        return $schema
            ->components([

                // بخش اطلاعات اصلی حواله - چیدمان ۳ ستونه در بالای صفحه
                Section::make('اطلاعات حواله خروج')
                    ->description('مشخصات لجستیک، باربری و مقصد محموله')
                    ->columns(3)       // چیدمان ۳ ستونه
                    ->columnSpanFull() // عرض کامل صفحه
                    ->schema([
                        Select::make('order_id')
                            ->label('سفارش مرجع')
                            ->relationship(
                                'order',
                                'order_number',
                                fn (Builder $query) => $query->orderByDesc('id'),
                            )
                            ->getOptionLabelFromRecordUsing(fn (Order $record): string => "شماره: {$record->order_number} | مشتری: {$record->customer->name} | تاریخ: ".($record->created_at ? jalali($record->created_at)->format('Y/m/d') : '---'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set, ?string $state) use ($sumShipmentWeights): void {
                                if (! $state) {
                                    $set('shipmentItems', []);
                                    $set('destination', null);

                                    return;
                                }

                                $order = Order::with(['customer', 'orderItems.product', 'orderItems.shipmentItems'])->find($state);

                                if ($order) {
                                    $set('destination', $order->customer->address);

                                    $items = [];

                                    foreach ($order->orderItems as $orderItem) {
                                        $alreadyShipped = $orderItem->shipmentItems->sum('shipped_quantity');
                                        $remaining = $orderItem->quantity - $alreadyShipped;

                                        if ($remaining > 0) {
                                            $quantityPerBox = PackagingItem::quantityPerBox($order->packaging_id, $orderItem->product_id);

                                            $uuid = (string) str()->uuid();
                                            $items[$uuid] = [
                                                'order_item_id' => $orderItem->id,
                                                'product_name' => $orderItem->product->name,
                                                'ordered_quantity' => $orderItem->quantity,
                                                'remaining_quantity' => $remaining,
                                                'shipped_quantity' => $remaining,
                                                'box_quantity' => $quantityPerBox !== null
                                                    ? (int) ceil($remaining / $quantityPerBox)
                                                    : null,
                                                'product_weight' => $orderItem->product->weight,
                                                'weight' => round(((float) ($orderItem->product->weight ?? 0) * $remaining) / 1000, 3),
                                            ];
                                        }
                                    }

                                    $set('shipmentItems', $items);
                                    $set('total_weight', $sumShipmentWeights($items));
                                }
                            }),

                        TextInput::make('shipment_number')
                            ->label('شماره سریال حواله')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('تولید خودکار سیستمی'),

                        DatePicker::make('shipped_at')
                            ->label('تاریخ خروج از کارخانه')
                            ->jalali()
                            ->default(now())
                            ->required(),

                        TextInput::make('driver_name')
                            ->label('نام راننده / شرکت باربری')
                            ->required(),

                        TextInput::make('vehicle_plate')
                            ->label('شماره پلاک خودرو'),

                        TextInput::make('total_master_boxes')
                            ->label('تعداد کل کارتن مادر')
                            ->numeric()
                            ->minValue(1)
                            ->required(),

                        TextInput::make('total_weight')
                            ->label('وزن کل محموله')
                            ->numeric()
                            ->type('text')
                            ->minValue(0)
                            ->default(0)
                            ->suffix('کیلوگرم')
                            ->beforeStateDehydrated($calculateTotalWeight, shouldUpdateValidatedStateAfter: true),

                        Textarea::make('destination')
                            ->label('آدرس دقیق مقصد')
                            ->columnSpanFull() // آدرس تمام عرض را می‌گیرد تا جای تایپ زیاد باشد
                            ->rows(2),
                    ]),

                // بخش اقلام ارسالی - تمام‌عرض در پایین صفحه
                Section::make('اقلام محموله (حواله خروج)')
                    ->description('لیست محصولاتی که در این محموله بارگیری و ارسال می‌شوند')
                    ->columnSpanFull() // باعث می‌شود این سکشن کل عرض صفحه را اشغال کند
                    ->schema([
                        Repeater::make('shipmentItems')
                            ->relationship()
                            ->label('')
                            ->addable(false)
                            ->deletable(false)
                            ->columns(4)
                            ->afterStateHydrated(function (Repeater $component, ?Model $record) {
                                $state = $component->getState();
                                if (empty($state)) {
                                    return;
                                }

                                foreach ($state as $uuid => $item) {
                                    $orderItemId = $item['order_item_id'] ?? null;
                                    if (! $orderItemId) {
                                        continue;
                                    }

                                    /** @var OrderItem|null $orderItem */
                                    $orderItem = OrderItem::with(['product', 'shipmentItems'])->find($orderItemId);
                                    if ($orderItem) {
                                        $state[$uuid]['product_name'] = $orderItem->product->name;
                                        $state[$uuid]['product_weight'] = $orderItem->product->weight;
                                        $state[$uuid]['weight'] = round(((float) ($orderItem->product->weight ?? 0) * (int) ($state[$uuid]['shipped_quantity'] ?? 0)) / 1000, 3);

                                        $currentShipmentId = $record?->getKey();
                                        $shippedElsewhere = $orderItem->shipmentItems()
                                            ->when($currentShipmentId, fn (Builder $query): Builder => $query->where('shipment_id', '!=', $currentShipmentId))
                                            ->sum('shipped_quantity');

                                        $state[$uuid]['remaining_quantity'] = $orderItem->quantity - $shippedElsewhere;
                                    }
                                }

                                $component->state($state);
                            })
                            ->schema([
                                TextInput::make('product_name')
                                    ->label('عنوان محصول')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->columnSpan(1),

                                TextInput::make('remaining_quantity')
                                    ->label('تعداد سفارش باقی مانده')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->suffix('دست'),

                                TextInput::make('shipped_quantity')
                                    ->label('مقدار ارسالی فعلی')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->suffix('دست')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated($syncShipmentBoxFromQuantity)
                                    ->rules([
                                        function (Get $get): \Closure {
                                            return function (string $attribute, $value, \Closure $fail) use ($get): void {
                                                $remaining = (int) $get('remaining_quantity');

                                                if ($value > $remaining) {
                                                    $fail("مقدار ارسالی نمی‌تواند بیشتر از سقف مجاز ({$remaining} دست) باشد.");
                                                }
                                            };
                                        },
                                    ]),

                                TextInput::make('box_quantity')
                                    ->label('مقدار ارسالی فعلی (کارتن)')
                                    ->numeric()
                                    ->integer()
                                    ->required()
                                    ->minValue(0)
                                    ->suffix('کارتن')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated($syncShipmentQuantityFromBox),

                                // مقدار ۰ مجاز است (این قلم در این حواله ارسال نمی‌شود).
                                // فقط ارسال بیشتر از «باقی‌مانده» غیرمجاز است که در قانون بالا بررسی می‌شود.

                                Hidden::make('order_item_id'),
                                Hidden::make('product_weight')
                                    ->dehydrated(false),

                                TextInput::make('weight')
                                    ->label('وزن')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->suffix('کیلوگرم'),
                            ]),
                    ]),
            ]);
    }
}
