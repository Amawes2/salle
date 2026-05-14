<?php

namespace App\Filament\SuperAdmin\Resources\AlertResource\Pages;

use App\Filament\SuperAdmin\Resources\AlertResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAlert extends ViewRecord
{
    protected static string $resource = AlertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
