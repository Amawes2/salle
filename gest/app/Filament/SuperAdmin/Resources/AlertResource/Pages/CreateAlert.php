<?php

namespace App\Filament\SuperAdmin\Resources\AlertResource\Pages;

use App\Filament\SuperAdmin\Resources\AlertResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAlert extends CreateRecord
{
    protected static string $resource = AlertResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
