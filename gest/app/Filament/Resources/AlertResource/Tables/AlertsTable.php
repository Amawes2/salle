<?php

namespace App\Filament\Resources\AlertResource\Tables;

use App\Models\Alert;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class AlertsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('type')
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
                TextColumn::make('user.name')
                    ->label('Destinataire')
                    ->searchable()
                    ->placeholder('Tous'),
                IconColumn::make('is_read')
                    ->label('Lu')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        Alert::TYPE_SUBSCRIPTION_EXPIRY => 'Expiration abonnement',
                        Alert::TYPE_LOW_SESSIONS => 'Séances faibles',
                        Alert::TYPE_PAYMENT_DUE => 'Paiement dû',
                        Alert::TYPE_SYSTEM_NOTIFICATION => 'Notification système',
                        Alert::TYPE_NEW_MESSAGE => 'Nouveau message',
                    ]),
                TernaryFilter::make('is_read')
                    ->label('Lu')
                    ->placeholder('Tous')
                    ->trueLabel('Lu')
                    ->falseLabel('Non lu'),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('mark_as_read')
                    ->label('Marquer comme lu')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (Alert $record): bool => ! $record->is_read)
                    ->action(fn (Alert $record) => $record->markAsRead()),
                Action::make('mark_as_unread')
                    ->label('Marquer comme non lu')
                    ->icon('heroicon-o-x-mark')
                    ->color('gray')
                    ->visible(fn (Alert $record): bool => $record->is_read)
                    ->action(fn (Alert $record) => $record->markAsUnread()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('mark_as_read')
                        ->label('Marquer comme lu')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->markAsRead();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Marquer les alertes comme lues')
                        ->modalDescription('Êtes-vous sûr de vouloir marquer ces alertes comme lues ?')
                        ->modalSubmitActionLabel('Marquer comme lu'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
