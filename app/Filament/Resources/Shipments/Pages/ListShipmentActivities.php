<?php

namespace App\Filament\Resources\Shipments\Pages;

use App\Filament\Resources\Shipments\ShipmentResource;
use pxlrbt\FilamentActivityLog\Pages\ListActivitiesBySubject;

class ListShipmentActivities extends ListActivitiesBySubject
{
    protected static string $resource = ShipmentResource::class;

    public static function canAccess(array $parameters = []): bool
    {
        return parent::canAccess($parameters)
            && (bool) auth()->user()?->can('View:ActivityHistory');
    }
}
