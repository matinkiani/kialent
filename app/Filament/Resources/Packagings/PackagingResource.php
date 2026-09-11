<?php

namespace App\Filament\Resources\Packagings;

use App\Filament\Resources\Packagings\Pages\CreatePackaging;
use App\Filament\Resources\Packagings\Pages\EditPackaging;
use App\Filament\Resources\Packagings\Pages\ListPackagings;
use App\Filament\Resources\Packagings\Schemas\PackagingForm;
use App\Filament\Resources\Packagings\Tables\PackagingsTable;
use App\Models\Packaging;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PackagingResource extends Resource
{
    protected static ?string $model = Packaging::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static string|UnitEnum|null $navigationGroup = 'اطلاعات پایه';

    protected static bool $hasTitleCaseModelLabel = false;

    public static function form(Schema $schema): Schema
    {
        return PackagingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PackagingsTable::configure($table);
    }

    public static function getNavigationLabel(): string
    {
        return 'بسته‌بندی‌ها';
    }

    public static function getModelLabel(): string
    {
        return 'بسته‌بندی';
    }

    public static function getPluralModelLabel(): string
    {
        return 'بسته‌بندی‌ها';
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPackagings::route('/'),
            'create' => CreatePackaging::route('/create'),
            'edit' => EditPackaging::route('/{record}/edit'),
        ];
    }
}
