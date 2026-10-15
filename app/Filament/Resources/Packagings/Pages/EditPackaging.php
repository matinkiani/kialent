<?php

namespace App\Filament\Resources\Packagings\Pages;

use App\Filament\Resources\Packagings\PackagingResource;
use App\Models\Packaging;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPackaging extends EditRecord
{
    protected static string $resource = PackagingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('activities')
                ->label('تاریخچه تغییرات')
                ->icon('heroicon-o-clock')
                ->color('gray')
                ->url(fn (Packaging $record): string => ListPackagingActivities::getUrl(['record' => $record]))
                ->visible(fn (): bool => (bool) auth()->user()?->can('View:ActivityHistory')),

            DeleteAction::make(),
        ];
    }
}
