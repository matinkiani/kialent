<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderPriority;
use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label('شماره سفارش')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('brand')
                    ->label('برند')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label('مشتری')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('ordered_at')
                    ->label('تاریخ سفارش')
                    ->jalaliDate()
                    ->sortable(),

                TextColumn::make('priority')
                    ->label('اولویت')
                    ->badge()
                    ->sortable(),

                SelectColumn::make('status')
                    ->label('وضعیت')
                    ->options(OrderStatus::class)
                    ->sortable()
                    ->selectablePlaceholder(false),

                TextColumn::make('order_items_count')
                    ->label('تعداد اقلام')
                    ->counts('orderItems')
                    ->formatStateUsing(fn ($state): string => number_format((int) $state))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                TextColumn::make('total_quantity')
                    ->label('تعداد کل')
                    ->state(fn (Order $record): int => $record->orderItems->sum('quantity'))
                    ->formatStateUsing(fn (int $state): string => number_format($state))
                    ->sortable(),

                TextColumn::make('remaining_quantity')
                    ->label('تعداد باقیمانده')
                    ->state(fn (Order $record): int => $record->orderItems->sum(
                        fn ($orderItem): int => $orderItem->quantity - $orderItem->shipmentItems->sum('shipped_quantity'),
                    ))
                    ->formatStateUsing(fn (int $state): string => number_format($state))
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('تاریخ ثبت')
                    ->jalaliDateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with([
                'customer',
                'orderItems.shipmentItems',
            ]))
            ->filters([
                SelectFilter::make('customer_id')
                    ->label('مشتری')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('priority')
                    ->options(OrderPriority::class),

                SelectFilter::make('status')
                    ->options(OrderStatus::class)
                    ->multiple()
                    ->default(collect(OrderStatus::cases())
                        ->reject(fn (OrderStatus $status): bool => $status === OrderStatus::CompletelyShipped)
                        ->map(fn (OrderStatus $status): string => $status->value)
                        ->all()),
            ])
            ->recordActions([
                // ViewAction::make(),
                OrderResource::downloadFormsAction()->color('success')->label('چاپ'),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function (Collection $records, DeleteBulkAction $action): void {
                            $hasShipments = Order::query()
                                ->whereKey($records->pluck('id'))
                                ->has('shipments')
                                ->exists();

                            if ($hasShipments) {
                                Notification::make()
                                    ->title('سفارش‌های دارای حواله خروج قابل حذف نیستند.')
                                    ->danger()
                                    ->send();

                                $action->halt();
                            }
                        }),
                ]),
            ])
            ->defaultSort('ordered_at', direction: 'desc')
            ->defaultPaginationPageOption(25);
    }
}
