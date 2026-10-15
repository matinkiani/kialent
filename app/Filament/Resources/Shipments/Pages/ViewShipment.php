<?php

namespace App\Filament\Resources\Shipments\Pages;

use App\Filament\Resources\Shipments\ShipmentResource;
use App\Models\Shipment;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewShipment extends ViewRecord
{
    protected static string $resource = ShipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('activities')
                ->label('تاریخچه تغییرات')
                ->icon('heroicon-o-clock')
                ->color('gray')
                ->url(fn (Shipment $record): string => ListShipmentActivities::getUrl(['record' => $record]))
                ->visible(fn (): bool => (bool) auth()->user()?->can('View:ActivityHistory')),

            Action::make('printGatePass')
                ->label('چاپ حواله')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->openUrlInNewTab()
                ->url(fn (Shipment $record): string => route('shipments.gate-pass.download', $record)),
            EditAction::make(),
        ];
    }
}
