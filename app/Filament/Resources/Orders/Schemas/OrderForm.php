<?php
namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\BackPlateType;
use App\Enums\ChamferType;
use App\Enums\OrderPriority;
use App\Enums\OrderStatus;
use App\Enums\SlotType;
use App\Models\Customer;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('جزئیات سفارش')
                    ->columnSpan(2)
                    ->schema([
                        Fieldset::make('اطلاعات سفارش')
                            ->columns(2)
                            ->schema([
                                Select::make('customer_id')
                                    ->label('مشتری')
                                    ->relationship('customer', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->createOptionForm([
                                        TextInput::make('name')
                                            ->label('نام مشتری')
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('code')
                                            ->label('کد مشتری')
                                            ->required()
                                            ->maxLength(10)
                                            ->unique(Customer::class, 'code')
                                            ->alphaDash()
                                            ->mutateStateForValidationUsing(fn(?string $state): ?string => $state === null ? null : mb_strtoupper($state))
                                            ->dehydrateStateUsing(fn(?string $state): ?string => $state === null ? null : mb_strtoupper($state)),
                                    ]),

                                TextInput::make('brand')
                                    ->label('برند')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('order_number')
                                    ->label('شماره سفارش')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->placeholder('در زمان ثبت به‌صورت خودکار ساخته می‌شود')
                                    ->visibleOn('edit'),

                                Select::make('priority')
                                    ->label('اولویت')
                                    ->options(OrderPriority::class)
                                    ->required()
                                    ->default(OrderPriority::Normal),

                                Select::make('status')
                                    ->label('وضعیت')
                                    ->options(OrderStatus::class)
                                    ->required()
                                    ->default(OrderStatus::Pending),

                                DatePicker::make('ordered_at')
                                    ->label('تاریخ سفارش')
                                    ->jalali()
                                    ->hasToday()
                                    ->default(now())
                                    ->required(),

                                Textarea::make('payment_notes')
                                    ->label('یادداشت پرداخت')
                                    ->columnSpanFull()
                                    ->rows(2),
                            ]),

                        Section::make('اقلام سفارش')
                            ->columnSpan(2)
                            ->schema([
                                Repeater::make('orderItems')
                                    ->relationship()
                                    ->label('اقلام')
                                    ->addActionLabel('افزودن قلم')
                                    ->addAction(function (Action $action): Action {
                                        return $action->action(function (Repeater $component): void {
                                            // ۱. گرفتن داده‌های فعلی به کمک getState (به جای getRawState)
                                            $items = $component->getState() ?? [];

                                            // ۲. پیدا کردن آخرین ردیف موجود
                                            $lastItem = end($items) ?: [];

                                            // ۳. ساخت کلید اختصاصی (UUID) برای ردیف جدید
                                            $newUuid = (string) str()->uuid();

                                            // ۴. اضافه کردن ردیف جدید با استفاده از مقادیر آخرین ردیف
                                            $items[$newUuid] = [
                                                'product_id'              => null, // محصول را خالی می‌گذاریم تا کاربر انتخاب کند
                                                'quantity'                => null,
                                                'backing_plate_thickness' => $lastItem['backing_plate_thickness'] ?? null,
                                                'color'                   => $lastItem['color'] ?? null,
                                                'slot_type'               => $lastItem['slot_type'] ?? SlotType::None->value,
                                                'chamfer_type'            => $lastItem['chamfer_type'] ?? ChamferType::None->value,
                                                'has_shim'                => $lastItem['has_shim'] ?? false,
                                                'production_notes'        => null,
                                                'packaging_notes'         => null,
                                            ];

                                            // ۵. ثبت state جدید (فیلامنت به صورت خودکار مقادیر را در فیلدها قرار می‌دهد)
                                            $component->state($items);

                                            // ۶. باز کردن آکاردئون تا ردیف جدید قابل مشاهده باشد
                                            $component->collapsed(false);

                                            // ۷. اجرای هوک‌های بعد از آپدیت برای اعمال تغییرات در Livewire
                                            $component->callAfterStateUpdated();
                                        });
                                    })
                                    ->schema([
                                        Grid::make(4)
                                            ->schema([
                                                Select::make('product_id')
                                                    ->label('محصول')
                                                    ->relationship('product', 'name')
                                                    ->searchable()
                                                    ->preload()
                                                    ->required()
                                                    ->createOptionForm([
                                                        TextInput::make('name')
                                                            ->label('نام محصول')
                                                            ->required()
                                                            ->maxLength(255),
                                                        Select::make('back_plate_type')
                                                            ->label('نوع بک‌پلیت')
                                                            ->options(BackPlateType::class)
                                                            ->required(),
                                                    ]),

                                                TextInput::make('quantity')
                                                    ->label('تعداد (دست)')
                                                    ->numeric()
                                                    ->minValue(1)
                                                    ->default(1)
                                                    ->suffix('دست')
                                                    ->required(),

                                                TextInput::make('backing_plate_thickness')
                                                    ->label('ضخامت بک پلیت')
                                                    ->numeric()
                                                    ->step(0.01)
                                                    ->suffix('mm'),

                                                TextInput::make('color')
                                                    ->label('رنگ')
                                                    ->maxLength(50)
                                                    ->required(),

                                                Select::make('slot_type')
                                                    ->label('نوع شیار')
                                                    ->options(SlotType::class)
                                                    ->default(SlotType::None)
                                                    ->required(),

                                                Select::make('chamfer_type')
                                                    ->label('نوع پخ')
                                                    ->options(ChamferType::class)
                                                    ->default(ChamferType::None)
                                                    ->required(),

                                                Toggle::make('has_shim')
                                                    ->label('شیم دارد')
                                                    ->default(false),
                                            ]),
                                        Grid::make(2)
                                            ->schema([
                                                Textarea::make('production_notes')
                                                    ->label('توضیحات تولید')
                                                    ->rows(2)
                                                    ->nullable(),

                                                Textarea::make('packaging_notes')
                                                    ->label('توضیحات بسته‌بندی')
                                                    ->rows(2)
                                                    ->nullable(),
                                            ]),
                                    ])
                                    ->defaultItems(1)
                                    ->orderColumn('sort')
                                    ->reorderableWithButtons()
                                    ->reorderableWithDragAndDrop()
                                    ->collapsible()
                                    ->itemLabel(function (array $state, Repeater $component, string $uuid): ?string {
                                        $productId = $state['product_id'] ?? null;

                                        if (! is_numeric($productId)) {
                                            return null;
                                        }

                                        // گرفتن نام محصول از دیتابیس
                                        $productName = Product::query()->whereKey($productId)->value('name');

                                        // ۱. دریافت تمام ردیف‌های فعلی ریپیتر
                                        $items = $component->getState();

                                        // ۲. اگر ردیف‌ها خالی بود (حالت احتیاط)
                                        if (! is_array($items)) {
                                            return $productName;
                                        }

                                        // ۳. پیدا کردن ایندکس این ردیف خاص با استفاده از UUID و تبدیل آن به عدد خوانا (+1)
                                        $rowIndex = array_search($uuid, array_keys($items)) + 1;

                                        // خروجی نهایی: مثلاً "ردیف 1: لنت پژو پارس"
                                        return "{$rowIndex} - {$productName}";
                                    })
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }
}
