<?php

namespace App\Filament\Resources\AlertResource\Schemas;

use App\Models\Alert;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AlertInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Notification')
                    ->schema([
                        TextEntry::make('title')
                            ->label('Titre'),
                        TextEntry::make('content')
                            ->label('Contenu')
                            ->columnSpanFull(),
                        TextEntry::make('type')
                            ->label('Type')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                Alert::TYPE_SUBSCRIPTION_EXPIRY => 'danger',
                                Alert::TYPE_LOW_SESSIONS => 'warning',
                                Alert::TYPE_PAYMENT_DUE => 'warning',
                                Alert::TYPE_SYSTEM_NOTIFICATION => 'info',
                                Alert::TYPE_NEW_MESSAGE => 'primary',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                Alert::TYPE_SUBSCRIPTION_EXPIRY => 'Expiration abonnement',
                                Alert::TYPE_LOW_SESSIONS => 'Séances faibles',
                                Alert::TYPE_PAYMENT_DUE => 'Paiement dû',
                                Alert::TYPE_SYSTEM_NOTIFICATION => 'Notification système',
                                Alert::TYPE_NEW_MESSAGE => 'Nouveau message',
                                default => $state,
                            }),
                        TextEntry::make('user.name')
                            ->label('Destinataire')
                            ->placeholder('Tous les utilisateurs du gym'),
                        IconEntry::make('is_read')
                            ->label('Lu')
                            ->boolean(),
                        TextEntry::make('created_at')
                            ->label('Créé le')
                            ->dateTime('d/m/Y H:i'),
                    ])
                    ->columns(2),
            ]);
    }
}
