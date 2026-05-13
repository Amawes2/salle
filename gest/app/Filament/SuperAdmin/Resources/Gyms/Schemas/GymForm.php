<?php

namespace App\Filament\SuperAdmin\Resources\Gyms\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class GymForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Select::make('owner_id')
                    ->relationship('owner', 'name')
                    ->required(),
                TextInput::make('plan_saas')
                    ->required()
                    ->default('basic'),
                TextInput::make('walk_in_price')
                    ->label('Tarif séances uniques (FCFA)')
                    ->numeric()
                    ->minValue(0)
                    ->nullable()
                    ->prefix('FCFA')
                    ->helperText('Tarif appliqué aux séances ponctuelles.'),
                Toggle::make('is_active')
                    ->required(),
                DatePicker::make('expires_at'),
            ]);
    }
}
