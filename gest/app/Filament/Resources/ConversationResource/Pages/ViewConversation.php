<?php

namespace App\Filament\Resources\ConversationResource\Pages;

use App\Filament\Resources\ConversationResource;
use App\Services\ChatService;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;

class ViewConversation extends ViewRecord
{
    protected static string $resource = ConversationResource::class;

    public string $messageContent = '';

    public function mount($record): void
    {
        parent::mount($record);
        app(ChatService::class)->markConversationAsRead($this->record);
    }

    public function sendMessage(): void
    {
        if (empty(trim($this->messageContent))) {
            return;
        }

        app(ChatService::class)->sendMessage(
            $this->record,
            Auth::user(),
            $this->messageContent
        );

        $this->messageContent = '';
        $this->dispatch('messageSent');
    }

    #[Computed]
    public function messages()
    {
        return $this->record->messages()
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
