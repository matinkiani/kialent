<?php

namespace App\Filament\Resources\Packagings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PackagingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('نام بسته‌بندی')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('brand_name')
                    ->label('نام برند')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('packaging_items_count')
                    ->label('تعداد محصولات')
                    ->counts('packagingItems'),

                TextColumn::make('created_at')
                    ->label('تاریخ ثبت')
                    ->jalaliDateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
