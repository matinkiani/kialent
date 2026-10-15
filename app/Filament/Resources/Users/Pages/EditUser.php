<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('activities')
                ->label('تاریخچه تغییرات')
                ->icon('heroicon-o-clock')
                ->color('gray')
                ->url(fn (User $record): string => ListUserActivities::getUrl(['record' => $record]))
                ->visible(fn (): bool => (bool) auth()->user()?->can('View:ActivityHistory')),

            DeleteAction::make()
                ->visible(fn (User $record): bool => $record->id !== auth()->id()),
        ];
    }
}
