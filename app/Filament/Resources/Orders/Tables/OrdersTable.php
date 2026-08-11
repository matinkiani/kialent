<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderPriority;
use App\Enums\OrderStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

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

                TextColumn::make('status')
                    ->label('وضعیت')
                    ->badge()
                    ->sortable(),

                TextColumn::make('order_items_count')
                    ->label('اقلام')
                    ->counts('orderItems')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('تاریخ ثبت')
                    ->jalaliDateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('customer_id')
                    ->label('مشتری')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('priority')
                    ->options(OrderPriority::class),

                SelectFilter::make('status')
                    ->options(OrderStatus::class),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
