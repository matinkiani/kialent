<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Packagings\PackagingResource;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Shipments\ShipmentResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Packaging;
use App\Models\PackagingItem;
use App\Models\Product;
use App\Models\Shipment;
use App\Models\ShipmentItem;
use App\Models\User;
use BackedEnum;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use UnitEnum;

class ActivityHistory extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'تنظیمات';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.activity-history';

    /**
     * برچسب فارسی انواع رکورد.
     */
    private const SUBJECT_LABELS = [
        Order::class => 'سفارش',
        OrderItem::class => 'قلم سفارش',
        Customer::class => 'مشتری',
        Product::class => 'محصول',
        Packaging::class => 'بسته‌بندی',
        PackagingItem::class => 'قلم بسته‌بندی',
        Shipment::class => 'حواله',
        ShipmentItem::class => 'قلم حواله',
        User::class => 'کاربر',
    ];

    /**
     * ریسورسی که برچسب‌های فارسی فیلدها از فرم آن استخراج می‌شود.
     */
    private const SUBJECT_RESOURCES = [
        Order::class => OrderResource::class,
        OrderItem::class => OrderResource::class,
        Customer::class => CustomerResource::class,
        Product::class => ProductResource::class,
        Packaging::class => PackagingResource::class,
        PackagingItem::class => PackagingResource::class,
        Shipment::class => ShipmentResource::class,
        ShipmentItem::class => ShipmentResource::class,
        User::class => UserResource::class,
    ];

    public static function canAccess(array $parameters = []): bool
    {
        return (bool) auth()->user()?->can('View:ActivityHistory');
    }

    public static function getNavigationLabel(): string
    {
        return 'گزارش تغییرات';
    }

    public function getTitle(): string
    {
        return 'گزارش تغییرات';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Activity::query())
            ->columns([
                TextColumn::make('causer.name')
                    ->label('چه کسی')
                    ->placeholder('سیستم'),

                TextColumn::make('event')
                    ->label('رویداد')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $this->eventLabel($state))
                    ->color(fn (Activity $record): string => match ($record->event) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        'restored' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('subject_type')
                    ->label('نوع')
                    ->formatStateUsing(fn (Activity $record): string => static::SUBJECT_LABELS[$record->subject_type] ?? class_basename($record->subject_type)),

                TextColumn::make('subject')
                    ->label('عنوان')
                    ->state(fn (Activity $record): string => $this->describeSubject($record)),

                TextColumn::make('created_at')
                    ->label('زمان')
                    ->jalaliDateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('causer')
                    ->label('چه کسی')
                    ->options(fn (): array => User::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'] ?? null,
                            fn (Builder $query, int|string $id): Builder => $query
                                ->where('causer_type', (new User)->getMorphClass())
                                ->where('causer_id', $id),
                        );
                    }),

                SelectFilter::make('event')
                    ->label('رویداد')
                    ->options([
                        'created' => 'ایجاد شد',
                        'updated' => 'به‌روزرسانی شد',
                        'deleted' => 'حذف شد',
                        'restored' => 'بازیابی شد',
                    ]),

                SelectFilter::make('subject_type')
                    ->label('نوع')
                    ->options(static::SUBJECT_LABELS),
            ])
            ->recordActions([
                Action::make('details')
                    ->label('جزئیات')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading('جزئیات تغییرات')
                    ->modalContent(fn (Activity $record): View => view('filament.activity-changes', [
                        'activity' => $record,
                        'labels' => $this->fieldLabelsFor($record->subject_type),
                    ])),
            ])
            ->defaultSort('id', 'desc');
    }

    private function eventLabel(string $event): string
    {
        return match ($event) {
            'created' => 'ایجاد شد',
            'updated' => 'به‌روزرسانی شد',
            'deleted' => 'حذف شد',
            'restored' => 'بازیابی شد',
            default => $event,
        };
    }

    private function describeSubject(Activity $record): string
    {
        $label = static::SUBJECT_LABELS[$record->subject_type] ?? class_basename($record->subject_type);

        $subject = $record->subject;

        if (! $subject instanceof Model) {
            return $label.' (حذف‌شده)';
        }

        return $label.' «'.$this->subjectTitle($subject).'»';
    }

    private function subjectTitle(Model $subject): string
    {
        return match (true) {
            $subject instanceof Order => $subject->order_number ?? (string) $subject->getKey(),
            $subject instanceof Customer,
            $subject instanceof Product,
            $subject instanceof Packaging,
            $subject instanceof User => $subject->name ?? (string) $subject->getKey(),
            $subject instanceof Shipment => $subject->shipment_number ?? (string) $subject->getKey(),
            $subject instanceof OrderItem => $subject->product?->name ?? 'قلم #'.$subject->getKey(),
            $subject instanceof PackagingItem => $subject->product?->name ?? 'قلم #'.$subject->getKey(),
            $subject instanceof ShipmentItem => $subject->orderItem?->product?->name ?? 'قلم #'.$subject->getKey(),
            default => (string) $subject->getKey(),
        };
    }

    /**
     * برچسب‌های فارسی فیلدها را از فرم ریسورس مربوطه استخراج می‌کند
     * (همان روش pxlrbt) و به‌ازای هر نوع مدل کش می‌کند.
     */
    public function fieldLabelsFor(string $subjectType): array
    {
        static $cache = [];

        if (array_key_exists($subjectType, $cache)) {
            return $cache[$subjectType];
        }

        $resourceClass = static::SUBJECT_RESOURCES[$subjectType] ?? null;

        if ($resourceClass === null) {
            return $cache[$subjectType] = [];
        }

        try {
            $schema = $resourceClass::form(new Schema($this));
        } catch (Exception) {
            return $cache[$subjectType] = [];
        }

        $components = collect($schema->getComponents());
        $extracted = collect();

        while (($component = $components->shift()) !== null) {
            if ($component instanceof Field || $component instanceof MorphToSelect) {
                $extracted->push($component);

                continue;
            }

            $components = $components->merge($component->getChildComponents());
        }

        return $cache[$subjectType] = $extracted
            ->filter(fn ($component): bool => $component instanceof Field)
            ->mapWithKeys(fn (Field $field): array => [
                $field->getName() => $field->getLabel() ?? $field->getName(),
            ])
            ->all();
    }
}
