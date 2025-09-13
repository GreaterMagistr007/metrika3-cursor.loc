<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Message;
use App\Models\MessageRecipient;
use App\Models\User;
use App\Models\UserMessage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final class MessageService
{
    /**
     * Send a message to specific recipients.
     */
    public function send(array $messageData, array $recipients): Message
    {
        return DB::transaction(function () use ($messageData, $recipients) {
            // Create the message
            $message = Message::create($messageData);

            // Create recipients
            foreach ($recipients as $recipient) {
                MessageRecipient::create([
                    'message_id' => $message->id,
                    'recipient_type' => $recipient['type'],
                    'recipient_id' => $recipient['id'] ?? null,
                ]);
            }

            // Distribute message to users
            $this->distributeToUsers($message);

            return $message;
        });
    }

    /**
     * Send a toast message (temporary, not stored in DB).
     */
    public function sendToast(string $type, string $text, ?string $title = null): array
    {
        return [
            'type' => 'toast',
            'message_type' => $type,
            'title' => $title,
            'text' => $text,
        ];
    }

    /**
     * Send a persistent message to specific users.
     */
    public function sendPersistent(
        string $type,
        string $text,
        array $userIds,
        ?string $title = null,
        ?string $url = null,
        ?string $buttonText = null,
        ?string $buttonUrl = null
    ): Message {
        $message = $this->send([
            'type' => $type,
            'title' => $title,
            'text' => $text,
            'url' => $url,
            'button_text' => $buttonText,
            'button_url' => $buttonUrl,
            'is_active' => true,
        ], array_map(fn($userId) => ['type' => 'user', 'id' => $userId], $userIds));

        return $message;
    }

    /**
     * Send a broadcast message to all users or specific cabinet.
     */
    public function broadcast(
        string $type,
        string $text,
        string $recipientType = 'all',
        ?int $recipientId = null,
        ?string $title = null,
        ?string $url = null,
        ?string $buttonText = null,
        ?string $buttonUrl = null,
        ?array $triggerCondition = null,
        ?\DateTime $expiresAt = null
    ): Message {
        $message = $this->send([
            'type' => $type,
            'title' => $title,
            'text' => $text,
            'url' => $url,
            'button_text' => $buttonText,
            'button_url' => $buttonUrl,
            'is_active' => true,
            'trigger_condition' => $triggerCondition,
            'expires_at' => $expiresAt,
        ], [['type' => $recipientType, 'id' => $recipientId]]);

        return $message;
    }

    /**
     * Get unread messages for a user.
     */
    public function getUnreadMessagesForUser(int $userId): Collection
    {
        return Message::whereHas('userMessages', function ($query) use ($userId) {
            $query->where('user_id', $userId)
                  ->where('is_read', false);
        })
        ->where('is_active', true)
        ->notExpired()
        ->with(['userMessages' => function ($query) use ($userId) {
            $query->where('user_id', $userId);
        }])
        ->get();
    }

    /**
     * Get all messages for a user (read and unread).
     */
    public function getAllMessagesForUser(int $userId, int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Message::whereHas('userMessages', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->where('is_active', true)
        ->notExpired()
        ->with(['userMessages' => function ($query) use ($userId) {
            $query->where('user_id', $userId);
        }])
        ->orderBy('created_at', 'desc')
        ->paginate($perPage);
    }

    /**
     * Mark message as read for a user.
     */
    public function markAsRead(int $userId, int $messageId): bool
    {
        $userMessage = UserMessage::where('user_id', $userId)
            ->where('message_id', $messageId)
            ->first();

        if (!$userMessage) {
            return false;
        }

        $userMessage->markAsRead();
        return true;
    }

    /**
     * Mark all messages as read for a user.
     */
    public function markAllAsRead(int $userId): int
    {
        return UserMessage::where('user_id', $userId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    /**
     * Get message statistics.
     */
    public function getStatistics(): array
    {
        $totalMessages = Message::count();
        $activeMessages = Message::where('is_active', true)->count();
        $expiredMessages = Message::where('expires_at', '<', now())->count();
        $unreadMessages = UserMessage::where('is_read', false)->count();

        return [
            'total_messages' => $totalMessages,
            'active_messages' => $activeMessages,
            'expired_messages' => $expiredMessages,
            'unread_messages' => $unreadMessages,
        ];
    }

    /**
     * Distribute message to users based on recipients.
     */
    public function distributeToUsers(Message $message): void
    {
        $userIds = [];

        foreach ($message->recipients as $recipient) {
            switch ($recipient->recipient_type) {
                case 'user':
                    if ($recipient->recipient_id) {
                        $userIds[] = $recipient->recipient_id;
                    }
                    break;

                case 'cabinet':
                    if ($recipient->recipient_id) {
                        $cabinetUserIds = User::whereHas('cabinets', function ($query) use ($recipient) {
                            $query->where('cabinets.id', $recipient->recipient_id);
                        })->pluck('id')->toArray();
                        $userIds = array_merge($userIds, $cabinetUserIds);
                    }
                    break;

                case 'all':
                    $allUserIds = User::pluck('id')->toArray();
                    $userIds = array_merge($userIds, $allUserIds);
                    break;
            }
        }

        // Remove duplicates
        $userIds = array_unique($userIds);

        // Create user_messages records
        foreach ($userIds as $userId) {
            UserMessage::create([
                'user_id' => $userId,
                'message_id' => $message->id,
                'is_read' => false,
            ]);
        }
    }

    /**
     * Check and process trigger conditions for messages.
     */
    public function processTriggers(User $user): void
    {
        $userMessages = UserMessage::where('user_id', $user->id)
            ->where('is_read', false)
            ->with('message')
            ->get();

        foreach ($userMessages as $userMessage) {
            $message = $userMessage->message;
            
            if (!$message->trigger_condition) {
                continue;
            }

            if ($this->checkTriggerCondition($message->trigger_condition, $user)) {
                $userMessage->markAsRead();
            }
        }
    }

    /**
     * Check if trigger condition is met.
     */
    private function checkTriggerCondition(array $condition, User $user): bool
    {
        // Simple trigger condition checking
        // This can be extended based on business requirements
        
        if (isset($condition['field']) && isset($condition['value'])) {
            $field = $condition['field'];
            $value = $condition['value'];
            
            // Check user attributes
            if (str_contains($field, 'user.')) {
                $attribute = str_replace('user.', '', $field);
                return $user->$attribute === $value;
            }
            
            // Check cabinet attributes
            if (str_contains($field, 'cabinet.')) {
                $attribute = str_replace('cabinet.', '', $field);
                return $user->cabinets()->where($attribute, $value)->exists();
            }
        }

        return false;
    }
}
