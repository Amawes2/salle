<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Gym;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatService
{
    protected AlertService $alertService;

    public function __construct(AlertService $alertService)
    {
        $this->alertService = $alertService;
    }

    /**
     * Get or create a conversation between a gym and a super admin.
     */
    public function getOrCreateConversation(Gym $gym, ?User $superAdmin = null): Conversation
    {
        $query = Conversation::query()->where('gym_id', $gym->id);

        if ($superAdmin) {
            $query->where('super_admin_id', $superAdmin->id);
        } else {
            $query->whereNull('super_admin_id');
        }

        $conversation = $query->first();

        if (! $conversation) {
            $conversation = Conversation::create([
                'gym_id' => $gym->id,
                'super_admin_id' => $superAdmin?->id,
                'title' => 'Support '.$gym->name,
            ]);
        }

        return $conversation;
    }

    /**
     * Send a message in a conversation.
     */
    public function sendMessage(Conversation $conversation, User $sender, string $content): Message
    {
        return DB::transaction(function () use ($conversation, $sender, $content) {
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'user_id' => $sender->id,
                'content' => $content,
                'is_read' => false,
            ]);

            // Determine the recipient(s)
            $recipients = $this->getMessageRecipients($conversation, $sender);

            // Create alerts for recipients
            foreach ($recipients as $recipient) {
                $this->alertService->createNewMessageAlert(
                    $recipient,
                    $sender,
                    substr($content, 0, 50).(strlen($content) > 50 ? '...' : ''),
                    $conversation->id,
                    $conversation->gym_id
                );
            }

            return $message;
        });
    }

    /**
     * Get the recipients for a message.
     */
    private function getMessageRecipients(Conversation $conversation, User $sender): array
    {
        $recipients = [];

        // If sender is super admin, notify gym owner
        if ($sender->is_super_admin) {
            $recipients[] = $conversation->gym->owner;
        }
        // If sender is gym owner, notify super admin
        elseif ($conversation->super_admin_id) {
            $recipients[] = $conversation->superAdmin;
        }
        // If no super admin assigned yet, notify all super admins
        else {
            $superAdmins = User::where('is_super_admin', true)->get();
            foreach ($superAdmins as $superAdmin) {
                $recipients[] = $superAdmin;
            }
        }

        return array_filter($recipients);
    }

    /**
     * Mark all messages in a conversation as read for the current user.
     */
    public function markConversationAsRead(Conversation $conversation): void
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        $conversation->messages()
            ->where('user_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    /**
     * Get conversations for a user.
     */
    public function getConversationsForUser(User $user)
    {
        if ($user->is_super_admin) {
            return Conversation::with(['gym', 'latestMessage'])
                ->where('super_admin_id', $user->id)
                ->orWhereNull('super_admin_id')
                ->orderBy('updated_at', 'desc')
                ->get();
        }

        return Conversation::with(['superAdmin', 'latestMessage'])
            ->whereHas('gym', function ($query) use ($user) {
                $query->where('owner_id', $user->id)
                    ->orWhereHas('users', function ($managerQuery) use ($user) {
                        $managerQuery->where('users.id', $user->id);
                    });
            })
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    /**
     * Get unread messages count for a user.
     */
    public function getUnreadMessagesCount(User $user): int
    {
        $query = Message::query()
            ->where('user_id', '!=', $user->id)
            ->where('is_read', false);

        if ($user->is_super_admin) {
            $query->whereHas('conversation', function ($q) use ($user) {
                $q->where('super_admin_id', $user->id)
                    ->orWhereNull('super_admin_id');
            });
        } else {
            $query->whereHas('conversation.gym', function ($q) use ($user) {
                $q->where('owner_id', $user->id)
                    ->orWhereHas('users', function ($managerQuery) use ($user) {
                        $managerQuery->where('users.id', $user->id);
                    });
            });
        }

        return $query->count();
    }
}
