<?php

namespace App\Filament\SuperAdmin\Resources\Gyms\Pages;

use App\Filament\SuperAdmin\Resources\Gyms\GymResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGym extends EditRecord
{
    protected static string $resource = GymResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
