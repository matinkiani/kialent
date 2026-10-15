<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use pxlrbt\FilamentActivityLog\Pages\ListActivitiesBySubject;

class ListProductActivities extends ListActivitiesBySubject
{
    protected static string $resource = ProductResource::class;

    public static function canAccess(array $parameters = []): bool
    {
        return parent::canAccess($parameters)
            && (bool) auth()->user()?->can('View:ActivityHistory');
    }
}
