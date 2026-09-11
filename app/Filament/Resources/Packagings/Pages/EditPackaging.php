<?php

namespace App\Filament\Resources\Packagings\Pages;

use App\Filament\Resources\Packagings\PackagingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPackaging extends EditRecord
{
    protected static string $resource = PackagingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
