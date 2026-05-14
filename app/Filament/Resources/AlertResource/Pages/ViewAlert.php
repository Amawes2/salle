<?php

namespace App\Filament\Resources\AlertResource\Pages;

use App\Filament\Resources\AlertResource;
use App\Models\Alert;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewAlert extends ViewRecord
{
    protected static string $resource = AlertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('mark_as_read')
                ->label('Marquer comme lu')
                ->icon('heroicon-o-check')
                ->color('success')
                ->visible(fn (): bool => ! $this->record->is_read)
                ->action(function (): void {
                    /** @var Alert $record */
                    $record = $this->record;
                    $record->markAsRead();
                }),
            Action::make('mark_as_unread')
                ->label('Marquer comme non lu')
                ->icon('heroicon-o-x-mark')
                ->color('gray')
                ->visible(fn (): bool => $this->record->is_read)
                ->action(function (): void {
                    /** @var Alert $record */
                    $record = $this->record;
                    $record->markAsUnread();
                }),
        ];
    }
}
