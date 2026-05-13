<?php

namespace App\Filament\SuperAdmin\Pages;

use App\Models\Conversation;
use App\Models\Gym;
use App\Models\Message;
use App\Services\ChatService;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;

class SuperAdminChat extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static string|\UnitEnum|null $navigationGroup = 'Communication';

    protected static ?string $navigationLabel = 'Support Chat';

    protected static ?string $title = 'Support Chat';

    protected static ?int $navigationSort = 80;

    protected string $view = 'filament.super-admin.pages.super-admin-chat';

    public ?int $selectedConversationId = null;

    public string $messageContent = '';

    public ?int $selectedGymId = null;

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

        return Conversation::with(['messages.user', 'gym'])->find($this->selectedConversationId);
    }

    #[Computed]
    public function messages()
    {
        if (! $this->selectedConversation) {
            return collect();
        }

        return $this->selectedConversation->messages()->with('user')->orderBy('created_at')->get();
    }

    #[Computed]
    public function gyms()
    {
        return Gym::orderBy('name')->get();
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
        if (! $this->selectedGymId) {
            return;
        }

        $gym = Gym::find($this->selectedGymId);

        if (! $gym) {
            return;
        }

        $chatService = app(ChatService::class);
        $conversation = $chatService->getOrCreateConversation($gym, Auth::user());

        $this->selectedConversationId = $conversation->id;
        $this->selectedGymId = null;
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
