<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use pxlrbt\FilamentActivityLog\Pages\ListActivitiesByCauser;

class ListUserActivities extends ListActivitiesByCauser
{
    protected static string $resource = UserResource::class;

    public static function canAccess(array $parameters = []): bool
    {
        return parent::canAccess($parameters)
            && (bool) auth()->user()?->can('View:ActivityHistory');
    }
}
