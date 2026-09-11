<?php

namespace App\Filament\Resources\Shipments\Tables;

use App\Models\Shipment;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ShipmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('shipment_number')
                    ->label('شماره حواله')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('order.customer.name')
                    ->label('مشتری')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('order.order_number')
                    ->label('شماره سفارش')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('driver_name')
                    ->label('راننده')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('total_master_boxes')
                    ->label('کارتن مادر')
                    ->numeric(locale: 'en')
                    ->sortable(),

                TextColumn::make('total_weight')
                    ->label('وزن کل')
                    ->numeric(decimalPlaces: 3, locale: 'en')
                    ->suffix(' کیلوگرم')
                    ->sortable(),

                TextColumn::make('shipped_at')
                    ->label('تاریخ خروج')
                    ->jalaliDate()
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),

                // دکمه چاپ حواله برای راننده
                Action::make('printGatePass')
                    ->label('چاپ حواله')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->openUrlInNewTab()
                    ->url(fn (Shipment $record): string => route('shipments.gate-pass.download', $record)),

                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
