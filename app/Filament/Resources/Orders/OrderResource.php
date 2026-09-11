<?php

namespace App\Filament\Resources\Orders;

use App\Enums\OrderFormType;
use App\Filament\Resources\Orders\Pages\CreateOrder;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\Pages\ViewOrder;
use App\Filament\Resources\Orders\Schemas\OrderForm;
use App\Filament\Resources\Orders\Schemas\OrderInfolist;
use App\Filament\Resources\Orders\Tables\OrdersTable;
use App\Models\Order;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Livewire\Component;
use UnitEnum;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'عملیات';

    public static function form(Schema $schema): Schema
    {
        return OrderForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return OrderInfolist::configure($schema);
    }

    public static function getNavigationLabel(): string
    {
        return 'سفارش‌ها';
    }

    public static function getModelLabel(): string
    {
        return 'سفارش';
    }

    public static function getPluralModelLabel(): string
    {
        return 'سفارش‌ها';
    }

    public static function table(Table $table): Table
    {
        return OrdersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'create' => CreateOrder::route('/create'),
            'view' => ViewOrder::route('/{record}'),
            'edit' => EditOrder::route('/{record}/edit'),
        ];
    }

    /**
     * دانلود یک‌جای فرم‌های سفارش؛ مشترک بین صفحه ویرایش و مشاهده.
     *
     * گزینهٔ «کم کردن ارسال شده‌ها» فرم‌ها را با «سفارش منهای ارسال‌شده» چاپ می‌کند
     * و اقلامِ کامل ارسال‌شده را حذف می‌کند.
     */
    public static function downloadFormsAction(): Action
    {
        return Action::make('downloadForms')
            ->label('دانلود فرم‌ها')
            ->icon('heroicon-o-printer')
            ->color('primary')
            ->modalHeading('دانلود فرم‌های سفارش')
            ->modalSubmitActionLabel('دانلود')
            ->schema([
                CheckboxList::make('forms')
                    ->label('کدام فرم‌ها را می‌خواهید؟')
                    ->helperText('حداقل یک فرم انتخاب کنید. هر فرم هنگام چاپ، روی صفحه جداگانه قرار می‌گیرد.')
                    ->options(OrderFormType::class)
                    ->columns(1)
                    ->required()
                    ->minItems(1),

                Checkbox::make('deduct_shipped')
                    ->label('کم کردن ارسال شده‌ها')
                    ->helperText('فرم‌ها با «سفارش منهای ارسال‌شده» چاپ می‌شوند؛ اقلامِ کامل ارسال‌شده نمایش داده نمی‌شوند.')
                    ->default(false),
            ])
            ->action(function (array $data, Order $record, Component $livewire): void {
                /** @var array<int, string|OrderFormType> $selectedForms */
                $selectedForms = is_array($data['forms'] ?? null) ? $data['forms'] : [];

                $forms = collect($selectedForms)
                    ->map(fn (string|OrderFormType $form): string => $form instanceof OrderFormType ? $form->value : $form)
                    ->values()
                    ->all();

                $url = route('orders.forms.download', [
                    'order' => $record->id,
                    'forms' => $forms,
                    'deduct_shipped' => (int) ($data['deduct_shipped'] ?? false),
                ]);

                $livewire->js('window.open('.json_encode($url).', "_blank")');
            });
    }
}
