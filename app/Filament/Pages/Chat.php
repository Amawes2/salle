<?php

namespace App\Filament\Pages;

use App\Models\Conversation;
use App\Models\Message;
use App\Services\ChatService;
use App\Support\CurrentGymResolver;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;

class Chat extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static string|\UnitEnum|null $navigationGroup = 'Communication';

    protected static ?string $navigationLabel = 'Support Chat';

    protected static ?string $title = 'Support Chat';

    protected static ?int $navigationSort = 80;

    protected string $view = 'filament.pages.chat';

    public ?int $selectedConversationId = null;

    public string $messageContent = '';

    public function mount(): void
    {
        $conversations = $this->conversations;

        if ($conversations->isNotEmpty()) {
            $this->selectedConversationId = $conversations->first()->id;
            $this->markConversationAsRead();
        }
    }

    #[Computed]
    public function conversations()
    {
        return app(ChatService::class)->getConversationsForUser(Auth::user());
    }

    #[Computed]
    public function selectedConversation()
    {
        if (! $this->selectedConversationId) {
            return null;
        }

        return Conversation::with(['messages.user'])->find($this->selectedConversationId);
    }

    #[Computed]
    public function messages()
    {
        if (! $this->selectedConversation) {
            return collect();
        }

        return $this->selectedConversation->messages()->with('user')->orderBy('created_at')->get();
    }

    public function selectConversation(int $conversationId): void
    {
        $this->selectedConversationId = $conversationId;
        $this->markConversationAsRead();
    }

    public function sendMessage(): void
    {
        if (empty(trim($this->messageContent)) || ! $this->selectedConversation) {
            return;
        }

        $chatService = app(ChatService::class);
        $chatService->sendMessage(
            $this->selectedConversation,
            Auth::user(),
            $this->messageContent
        );

        $this->messageContent = '';
        $this->dispatch('messageSent');
    }

    public function startNewConversation(): void
    {
        $gym = app(CurrentGymResolver::class)->resolve();

        if (! $gym) {
            return;
        }

        $chatService = app(ChatService::class);
        $conversation = $chatService->getOrCreateConversation($gym);

        $this->selectedConversationId = $conversation->id;
    }

    public function markConversationAsRead(): void
    {
        if (! $this->selectedConversation) {
            return;
        }

        app(ChatService::class)->markConversationAsRead($this->selectedConversation);
    }

    #[On('messageSent')]
    public function refreshMessages(): void
    {
        // This method will be called when a new message is sent
        // The computed property will automatically refresh
    }

    public function getUnreadCount(int $conversationId): int
    {
        $conversation = Conversation::find($conversationId);

        if (! $conversation) {
            return 0;
        }

        return $conversation->unreadMessagesCount(Auth::user());
    }
}
