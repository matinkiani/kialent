<?php

namespace App\Filament\Resources\Products\Tables;

use App\Enums\BackPlateType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('technical_code')
                    ->label('کد فنی')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable()
                    ->placeholder('ندارد'),

                TextColumn::make('name')
                    ->label('نام محصول')
                    ->weight('bold')
                    ->searchable(),

                TextColumn::make('description')
                    ->label('توضیحات')
                    ->limit(40)
                    ->toggleable(),

                TextColumn::make('weight')
                    ->label('وزن')
                    ->suffix(' گرم')
                    ->sortable(),

                TextColumn::make('back_plate_type')
                    ->label('نوع بک‌پلیت')
                    ->badge()
                    ->formatStateUsing(function ($state): string {
                        $backPlateType = $state instanceof BackPlateType ? $state : BackPlateType::tryFrom((string) $state);

                        return $backPlateType?->getLabel() ?? (string) $state;
                    })
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('تاریخ ثبت')
                    ->jalaliDateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('آخرین بروزرسانی')
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
            ])
            ->defaultSort('created_at', 'desc'); // مرتب‌سازی پیش‌فرض (اختیاری)
    }
}
