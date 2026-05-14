<?php

namespace App\Filament\Widgets;

use App\Services\ChatService;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;

class ChatNotificationWidget extends Widget
{
    protected string $view = 'filament.widgets.chat-notification-widget';

    protected static bool $isLazy = false;

    #[Computed]
    public function unreadMessagesCount(): int
    {
        return app(ChatService::class)->getUnreadMessagesCount(Auth::user());
    }
}
