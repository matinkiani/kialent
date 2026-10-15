<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('activities')
                ->label('تاریخچه تغییرات')
                ->icon('heroicon-o-clock')
                ->color('gray')
                ->url(fn (Product $record): string => ListProductActivities::getUrl(['record' => $record]))
                ->visible(fn (): bool => (bool) auth()->user()?->can('View:ActivityHistory')),

            DeleteAction::make()
                ->visible(fn (Product $record): bool => $record->orderItems()->doesntExist()),
        ];
    }
}
