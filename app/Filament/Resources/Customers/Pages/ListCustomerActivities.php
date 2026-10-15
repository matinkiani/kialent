<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use pxlrbt\FilamentActivityLog\Pages\ListActivitiesBySubject;

class ListCustomerActivities extends ListActivitiesBySubject
{
    protected static string $resource = CustomerResource::class;

    public static function canAccess(array $parameters = []): bool
    {
        return parent::canAccess($parameters)
            && (bool) auth()->user()?->can('View:ActivityHistory');
    }
}
