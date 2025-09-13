<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\CreateMessageRequest;
use App\Http\Requests\Api\Admin\UpdateMessageRequest;
use App\Models\Message;
use App\Services\MessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MessageController extends Controller
{
    public function __construct(
        private readonly MessageService $messageService
    ) {}

    /**
     * Get all messages with filtering and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 15);
        $type = $request->get('type');
        $isActive = $request->get('is_active');

        $query = Message::with(['recipients', 'userMessages']);

        if ($type) {
            $query->where('type', $type);
        }

        if ($isActive !== null) {
            $query->where('is_active', (bool) $isActive);
        }

        $messages = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'messages' => $messages->items(),
            'pagination' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
                'from' => $messages->firstItem(),
                'to' => $messages->lastItem(),
            ],
            'filters' => [
                'type' => $type,
                'is_active' => $isActive,
            ],
        ]);
    }

    /**
     * Create a new message.
     */
    public function store(CreateMessageRequest $request): JsonResponse
    {
        $messageData = $request->validated();
        $recipients = $messageData['recipients'];
        unset($messageData['recipients']);

        $message = $this->messageService->send($messageData, $recipients);

        return response()->json([
            'message' => 'Сообщение успешно создано',
            'data' => $message->load(['recipients', 'userMessages']),
        ], 201);
    }

    /**
     * Get a specific message.
     */
    public function show(Message $message): JsonResponse
    {
        $message->load(['recipients', 'userMessages.user']);

        return response()->json([
            'message' => $message,
        ]);
    }

    /**
     * Update a message.
     */
    public function update(UpdateMessageRequest $request, Message $message): JsonResponse
    {
        $messageData = $request->validated();
        
        if (isset($messageData['recipients'])) {
            $recipients = $messageData['recipients'];
            unset($messageData['recipients']);
            
            // Update message
            $message->update($messageData);
            
            // Update recipients
            $message->recipients()->delete();
            foreach ($recipients as $recipient) {
                $message->recipients()->create([
                    'recipient_type' => $recipient['type'],
                    'recipient_id' => $recipient['id'] ?? null,
                ]);
            }
            
            // Redistribute to users
            $this->messageService->distributeToUsers($message);
        } else {
            $message->update($messageData);
        }

        return response()->json([
            'message' => 'Сообщение успешно обновлено',
            'data' => $message->load(['recipients', 'userMessages']),
        ]);
    }

    /**
     * Delete a message.
     */
    public function destroy(Message $message): JsonResponse
    {
        $message->delete();

        return response()->json([
            'message' => 'Сообщение успешно удалено',
        ]);
    }

    /**
     * Toggle message active status.
     */
    public function toggleActive(Message $message): JsonResponse
    {
        $message->update(['is_active' => !$message->is_active]);

        return response()->json([
            'message' => $message->is_active ? 'Сообщение активировано' : 'Сообщение деактивировано',
            'data' => $message,
        ]);
    }

    /**
     * Get message statistics.
     */
    public function statistics(): JsonResponse
    {
        $statistics = $this->messageService->getStatistics();

        return response()->json([
            'statistics' => $statistics,
        ]);
    }

    /**
     * Get message types.
     */
    public function types(): JsonResponse
    {
        return response()->json([
            'types' => Message::getTypes(),
        ]);
    }
}
