<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('نام مشتری')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                TextInput::make('code')
                    ->label('کد مشتری')
                    ->helperText('یک کد کوتاه و یکتا برای پیشوند شماره سفارش‌های این مشتری؛ مثل 14 یا AL.')
                    ->required()
                    ->maxLength(10)
                    ->unique(ignoreRecord: true)
                    ->alphaDash()
                    ->mutateStateForValidationUsing(fn (?string $state): ?string => $state === null ? null : mb_strtoupper($state))
                    ->dehydrateStateUsing(fn (?string $state): ?string => $state === null ? null : mb_strtoupper($state)),
            ]);
    }
}
