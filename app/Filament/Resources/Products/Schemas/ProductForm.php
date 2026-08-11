<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\BackPlateType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('technical_code')
                    ->label('کد فنی')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->nullable()
                    ->placeholder('مثال: WVA-23584'),

                TextInput::make('name')
                    ->label('نام محصول')
                    ->required(),

                Textarea::make('description')
                    ->label('توضیحات')
                    ->rows(4)
                    ->columnSpanFull(),

                Select::make('back_plate_type')
                    ->label('نوع بک‌پلیت')
                    ->options(BackPlateType::class)
                    ->required(),
            ])
            ->columns(2); // برای اینکه کد فنی و نام محصول کنار هم بیفتند
    }
}
