<?php

namespace App\Filament\SuperAdmin\Resources\Gyms\Pages;

use App\Filament\SuperAdmin\Resources\Gyms\GymResource;
use App\Models\Gym;
use App\Notifications\SaasRenewalReminderNotification;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Throwable;

class ListGyms extends ListRecords
{
    protected static string $resource = GymResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('notify_due_gyms')
                ->label("Pr\u{00E9}venir les abonnements \u{00E0} relancer")
                ->icon('heroicon-o-bell-alert')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading("Pr\u{00E9}venir les salles concern\u{00E9}es")
                ->modalDescription("Envoie une relance aux salles expir\u{00E9}es ou proches de l\u{2019}\u{00E9}ch\u{00E9}ance.")
                ->action(function (): void {
                    $sent = 0;
                    $failed = 0;

                    Gym::query()
                        ->with('owner')
                        ->get()
                        ->each(function (Gym $gym) use (&$sent, &$failed): void {
                            if (! $gym->needsSaasReminder() || ! filled($gym->owner?->email)) {
                                return;
                            }

                            try {
                                $gym->owner->notify(new SaasRenewalReminderNotification($gym));
                                $sent++;
                            } catch (Throwable $e) {
                                $failed++;
                                report($e);
                            }
                        });

                    if ($failed > 0 && $sent === 0) {
                        Notification::make()
                            ->title('Echec de l\'envoi des emails')
                            ->body('Impossible de se connecter au serveur SMTP. Verifiez la configuration mail dans .env.')
                            ->danger()
                            ->send();

                        return;
                    }

                    $body = "{$sent} salle(s) ont recu une notification.";

                    if ($failed > 0) {
                        $body .= " {$failed} envoi(s) ont echoue.";
                    }

                    Notification::make()
                        ->title('Campagne de relance terminee')
                        ->body($body)
                        ->success()
                        ->send();
                }),
            CreateAction::make(),
        ];
    }
}
