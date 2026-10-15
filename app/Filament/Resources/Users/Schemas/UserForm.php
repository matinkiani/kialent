<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('name')
                            ->label('نام')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('ایمیل')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('password')
                            ->label('گذرواژه')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn ($state): bool => filled($state))
                            ->same('passwordConfirmation')
                            ->minLength(8)
                            ->maxLength(255),

                        TextInput::make('passwordConfirmation')
                            ->label('تکرار گذرواژه')
                            ->password()
                            ->revealable()
                            ->dehydrated(false)
                            ->required(fn (string $operation): bool => $operation === 'create'),

                        Select::make('roles')
                            ->label('نقش‌ها')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable(),
                    ])
                    ->columns(2),
            ]);
    }
}
