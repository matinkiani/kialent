<?php

namespace App\Filament\Resources\Customers\Tables;

use App\Models\Customer;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('کد')
                    ->badge()
                    ->searchable(),

                TextColumn::make('name')
                    ->label('نام مشتری')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('orders_count')
                    ->label('سفارش‌ها')
                    ->counts('orders')
                    ->sortable(),

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
                DeleteAction::make()
                    ->visible(fn (Customer $record): bool => $record->orders()->doesntExist()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function (Collection $records, DeleteBulkAction $action): void {
                            $hasOrders = Customer::query()
                                ->whereKey($records->pluck('id'))
                                ->has('orders')
                                ->exists();

                            if ($hasOrders) {
                                Notification::make()
                                    ->title('مشتریان دارای سفارش قابل حذف نیستند.')
                                    ->danger()
                                    ->send();

                                $action->halt();
                            }
                        }),
                ]),
            ])
            ->defaultSort('name');
    }
}
