<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Gym;
use App\Models\Message;
use App\Models\User;
use App\Services\ChatService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ChatSystemTest extends TestCase
{
    private ChatService $chatService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->chatService = app(ChatService::class);
    }

    #[Test]
    public function it_can_create_or_get_conversation(): void
    {
        $gym = Gym::factory()->create();
        $superAdmin = User::factory()->create(['is_super_admin' => true]);

        $conversation = $this->chatService->getOrCreateConversation($gym, $superAdmin);

        $this->assertDatabaseHas('conversations', [
            'gym_id' => $gym->id,
            'super_admin_id' => $superAdmin->id,
        ]);
    }

    #[Test]
    public function it_can_send_message(): void
    {
        $gym = Gym::factory()->create();
        $superAdmin = User::factory()->create(['is_super_admin' => true]);
        $conversation = Conversation::factory()->create([
            'gym_id' => $gym->id,
            'super_admin_id' => $superAdmin->id,
        ]);

        $message = $this->chatService->sendMessage(
            $conversation,
            $superAdmin,
            'Test message'
        );

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'user_id' => $superAdmin->id,
            'content' => 'Test message',
        ]);
    }

    #[Test]
    public function it_can_mark_conversation_as_read(): void
    {
        $this->actingAs($user = User::factory()->create());

        $gym = Gym::factory()->create(['owner_id' => $user->id]);
        $superAdmin = User::factory()->create(['is_super_admin' => true]);
        $conversation = Conversation::factory()->create([
            'gym_id' => $gym->id,
            'super_admin_id' => $superAdmin->id,
        ]);

        Message::factory()->count(3)->create([
            'conversation_id' => $conversation->id,
            'user_id' => $superAdmin->id,
            'is_read' => false,
        ]);

        $this->chatService->markConversationAsRead($conversation);

        $unreadMessages = Message::where('conversation_id', $conversation->id)
            ->where('is_read', false)
            ->count();

        $this->assertEquals(0, $unreadMessages);
    }

    #[Test]
    public function it_can_get_conversations_for_super_admin(): void
    {
        $superAdmin = User::factory()->create(['is_super_admin' => true]);
        $gym1 = Gym::factory()->create();
        $gym2 = Gym::factory()->create();

        Conversation::factory()->create(['gym_id' => $gym1->id, 'super_admin_id' => $superAdmin->id]);
        Conversation::factory()->create(['gym_id' => $gym2->id, 'super_admin_id' => $superAdmin->id]);
        Conversation::factory()->create(['gym_id' => $gym1->id, 'super_admin_id' => null]);

        $conversations = $this->chatService->getConversationsForUser($superAdmin);

        $this->assertCount(3, $conversations);
    }

    #[Test]
    public function it_can_get_conversations_for_gym_owner(): void
    {
        $owner = User::factory()->create();
        $gym = Gym::factory()->create(['owner_id' => $owner->id]);
        $superAdmin = User::factory()->create(['is_super_admin' => true]);

        Conversation::factory()->create(['gym_id' => $gym->id, 'super_admin_id' => $superAdmin->id]);
        Conversation::factory()->create(['gym_id' => $gym->id, 'super_admin_id' => null]);

        $other_gym = Gym::factory()->create();
        Conversation::factory()->create(['gym_id' => $other_gym->id, 'super_admin_id' => $superAdmin->id]);

        $conversations = $this->chatService->getConversationsForUser($owner);

        $this->assertCount(2, $conversations);
    }

    #[Test]
    public function it_can_get_conversations_for_gym_manager(): void
    {
        $manager = User::factory()->create();
        $gym = Gym::factory()->create();
        $gym->users()->attach($manager->id);
        $superAdmin = User::factory()->create(['is_super_admin' => true]);

        Conversation::factory()->create(['gym_id' => $gym->id, 'super_admin_id' => $superAdmin->id]);
        Conversation::factory()->create(['gym_id' => $gym->id, 'super_admin_id' => null]);

        $otherGym = Gym::factory()->create();
        Conversation::factory()->create(['gym_id' => $otherGym->id, 'super_admin_id' => $superAdmin->id]);

        $conversations = $this->chatService->getConversationsForUser($manager);

        $this->assertCount(2, $conversations);
    }

    #[Test]
    public function it_can_get_unread_messages_count(): void
    {
        $owner = User::factory()->create();
        $gym = Gym::factory()->create(['owner_id' => $owner->id]);
        $superAdmin = User::factory()->create(['is_super_admin' => true]);
        $conversation = Conversation::factory()->create(['gym_id' => $gym->id, 'super_admin_id' => $superAdmin->id]);

        Message::factory()->count(5)->create([
            'conversation_id' => $conversation->id,
            'user_id' => $superAdmin->id,
            'is_read' => false,
        ]);

        Message::factory()->count(3)->create([
            'conversation_id' => $conversation->id,
            'user_id' => $owner->id,
            'is_read' => false,
        ]);

        $unreadCount = $this->chatService->getUnreadMessagesCount($owner);

        $this->assertEquals(5, $unreadCount);
    }

    #[Test]
    public function it_creates_message_alert_with_conversation_gym_id(): void
    {
        $owner = User::factory()->create();
        $gym = Gym::factory()->create(['owner_id' => $owner->id]);
        $superAdmin = User::factory()->create(['is_super_admin' => true]);
        $conversation = Conversation::factory()->create([
            'gym_id' => $gym->id,
            'super_admin_id' => $superAdmin->id,
        ]);

        $this->chatService->sendMessage($conversation, $superAdmin, 'Message test pour gym');

        $this->assertDatabaseHas('alerts', [
            'user_id' => $owner->id,
            'gym_id' => $gym->id,
            'type' => 'new_message',
        ]);
    }
}
