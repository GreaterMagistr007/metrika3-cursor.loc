<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Message;
use App\Models\User;
use App\Services\MessageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class MessageTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test')->plainTextToken;
    }

    public function test_can_get_user_messages(): void
    {
        // Create test messages
        $message1 = Message::factory()->create(['type' => 'info', 'text' => 'Test message 1']);
        $message2 = Message::factory()->create(['type' => 'warning', 'text' => 'Test message 2']);

        // Create user_messages records
        $message1->users()->attach($this->user->id, ['is_read' => false]);
        $message2->users()->attach($this->user->id, ['is_read' => true]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/messages');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'messages' => [
                    '*' => [
                        'id',
                        'type',
                        'title',
                        'text',
                        'created_at',
                    ]
                ],
                'pagination' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ]
            ]);
    }

    public function test_can_get_unread_messages(): void
    {
        // Create test messages
        $message1 = Message::factory()->create(['type' => 'info', 'text' => 'Unread message', 'is_active' => true]);
        $message2 = Message::factory()->create(['type' => 'warning', 'text' => 'Read message', 'is_active' => true]);

        // Create user_messages records
        $message1->users()->attach($this->user->id, ['is_read' => false]);
        $message2->users()->attach($this->user->id, ['is_read' => true]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/messages/unread');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'messages',
                'count'
            ])
            ->assertJson([
                'count' => 1
            ]);
    }

    public function test_can_mark_message_as_read(): void
    {
        $message = Message::factory()->create(['type' => 'info', 'text' => 'Test message']);
        $message->users()->attach($this->user->id, ['is_read' => false]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/messages/{$message->id}/read");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Сообщение отмечено как прочитанное'
            ]);

        $this->assertDatabaseHas('user_messages', [
            'user_id' => $this->user->id,
            'message_id' => $message->id,
            'is_read' => true,
        ]);
    }

    public function test_can_mark_all_messages_as_read(): void
    {
        // Create test messages
        $message1 = Message::factory()->create(['type' => 'info', 'text' => 'Message 1']);
        $message2 = Message::factory()->create(['type' => 'warning', 'text' => 'Message 2']);

        // Create user_messages records
        $message1->users()->attach($this->user->id, ['is_read' => false]);
        $message2->users()->attach($this->user->id, ['is_read' => false]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/messages/mark-all-read');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'count'
            ]);

        $this->assertDatabaseHas('user_messages', [
            'user_id' => $this->user->id,
            'message_id' => $message1->id,
            'is_read' => true,
        ]);

        $this->assertDatabaseHas('user_messages', [
            'user_id' => $this->user->id,
            'message_id' => $message2->id,
            'is_read' => true,
        ]);
    }

    public function test_can_get_message_statistics(): void
    {
        // Create test messages
        $message1 = Message::factory()->create(['type' => 'info', 'text' => 'Message 1']);
        $message2 = Message::factory()->create(['type' => 'warning', 'text' => 'Message 2']);

        // Create user_messages records
        $message1->users()->attach($this->user->id, ['is_read' => false]);
        $message2->users()->attach($this->user->id, ['is_read' => true]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/messages/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'statistics' => [
                    'total_messages',
                    'unread_messages',
                    'read_messages',
                ]
            ]);
    }

    public function test_message_service_can_send_persistent_message(): void
    {
        $messageService = app(MessageService::class);
        
        $message = $messageService->sendPersistent(
            'info',
            'Test persistent message',
            [$this->user->id],
            'Test Title'
        );

        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'type' => 'info',
            'text' => 'Test persistent message',
            'title' => 'Test Title',
        ]);

        $this->assertDatabaseHas('user_messages', [
            'user_id' => $this->user->id,
            'message_id' => $message->id,
            'is_read' => false,
        ]);
    }

    public function test_message_service_can_send_broadcast_message(): void
    {
        $messageService = app(MessageService::class);
        
        $message = $messageService->broadcast(
            'system',
            'System maintenance',
            'all',
            null,
            'Maintenance Notice'
        );

        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'type' => 'system',
            'text' => 'System maintenance',
            'title' => 'Maintenance Notice',
        ]);

        $this->assertDatabaseHas('message_recipients', [
            'message_id' => $message->id,
            'recipient_type' => 'all',
            'recipient_id' => null,
        ]);
    }

    public function test_message_service_can_send_toast_message(): void
    {
        $messageService = app(MessageService::class);
        
        $toast = $messageService->sendToast('success', 'Operation completed successfully', 'Success');

        $this->assertEquals('toast', $toast['type']);
        $this->assertEquals('success', $toast['message_type']);
        $this->assertEquals('Operation completed successfully', $toast['text']);
        $this->assertEquals('Success', $toast['title']);
    }

    public function test_system_messages_middleware_adds_messages_to_header(): void
    {
        // Create a message for the user
        $message = Message::factory()->create(['type' => 'info', 'text' => 'Test system message', 'is_active' => true]);
        $message->users()->attach($this->user->id, ['is_read' => false]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/messages');

        $response->assertStatus(200);
        
        // Check if X-System-Messages header is present
        $this->assertTrue($response->headers->has('X-System-Messages'));
        
        $messages = json_decode($response->headers->get('X-System-Messages'), true);
        $this->assertIsArray($messages);
        $this->assertCount(1, $messages);
        $this->assertEquals('Test system message', $messages[0]['text']);
    }

    public function test_returns_401_for_unauthenticated_requests(): void
    {
        $response = $this->getJson('/api/messages');
        $response->assertStatus(401);
    }
}
