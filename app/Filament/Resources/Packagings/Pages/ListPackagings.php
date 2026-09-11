<?php

namespace App\Filament\Resources\Packagings\Pages;

use App\Filament\Resources\Packagings\PackagingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPackagings extends ListRecords
{
    protected static string $resource = PackagingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
