<?php

namespace App\Filament\SuperAdmin\Resources\Gyms\Tables;

use App\Models\Gym;
use App\Notifications\SaasRenewalReminderNotification;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class GymsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('owner.name')
                    ->label('Gérant')
                    ->searchable(),
                TextColumn::make('owner.email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),
                BadgeColumn::make('plan_saas')
                    ->label('Offre')
                    ->formatStateUsing(fn (?string $state): string => match (strtolower((string) $state)) {
                        'trial' => 'Essai',
                        'basic' => 'Basic',
                        'pro' => 'Pro',
                        'premium' => 'Premium',
                        default => ucfirst((string) $state),
                    })
                    ->colors([
                        'gray' => 'trial',
                        'info' => 'basic',
                        'success' => 'pro',
                        'warning' => 'premium',
                    ])
                    ->searchable(),
                BadgeColumn::make('saas_status')
                    ->label('Statut SaaS')
                    ->state(fn (Gym $record): string => $record->getSaasStatusLabel())
                    ->color(fn (Gym $record): string => $record->getSaasStatusColor()),
                IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),
                TextColumn::make('expires_at')
                    ->label('Date fin')
                    ->date()
                    ->sortable(),
                TextColumn::make('expiry_summary')
                    ->label('Relance')
                    ->state(fn (Gym $record): string => $record->getExpirySummary())
                    ->color(fn (Gym $record): string => $record->getSaasStatusColor()),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('notify_owner')
                    ->label('Prévenir')
                    ->icon('heroicon-o-envelope')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Prévenir le gérant')
                    ->modalDescription('Envoie un email de relance pour le paiement ou la fin d’abonnement.')
                    ->visible(fn (Gym $record): bool => filled($record->owner?->email))
                    ->action(function (Gym $record): void {
                        $record->owner?->notify(new SaasRenewalReminderNotification($record));

                        Notification::make()
                            ->title('Relance envoyée')
                            ->body("Le gérant de {$record->name} a bien été notifié.")
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('notify_due_gyms')
                        ->label('Prévenir les salles sélectionnées')
                        ->icon('heroicon-o-bell-alert')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Envoyer des relances')
                        ->modalDescription('Envoie une relance de paiement aux salles sélectionnées.')
                        ->action(function (Collection $records): void {
                            $sent = 0;

                            foreach ($records as $record) {
                                if (! $record instanceof Gym) {
                                    continue;
                                }

                                if (! filled($record->owner?->email)) {
                                    continue;
                                }

                                $record->owner->notify(new SaasRenewalReminderNotification($record));
                                $sent++;
                            }

                            Notification::make()
                                ->title('Relances envoyées')
                                ->body("{$sent} salle(s) ont été notifiées.")
                                ->success()
                                ->send();
                        }),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
