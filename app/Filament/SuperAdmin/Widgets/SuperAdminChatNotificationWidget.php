<?php

namespace App\Filament\SuperAdmin\Widgets;

use App\Services\ChatService;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;

class SuperAdminChatNotificationWidget extends Widget
{
    protected string $view = 'filament.super-admin.widgets.super-admin-chat-notification-widget';

    protected static bool $isLazy = false;

    #[Computed]
    public function unreadMessagesCount(): int
    {
        return app(ChatService::class)->getUnreadMessagesCount(Auth::user());
    }
}
