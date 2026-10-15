<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use App\Models\Customer;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCustomer extends EditRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('activities')
                ->label('تاریخچه تغییرات')
                ->icon('heroicon-o-clock')
                ->color('gray')
                ->url(fn (Customer $record): string => ListCustomerActivities::getUrl(['record' => $record]))
                ->visible(fn (): bool => (bool) auth()->user()?->can('View:ActivityHistory')),

            DeleteAction::make()
                ->visible(fn (Customer $record): bool => $record->orders()->doesntExist()),
        ];
    }
}
