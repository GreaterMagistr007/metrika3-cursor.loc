<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\MarkMessageReadRequest;
use App\Http\Resources\MessageResource;
use App\Services\MessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MessageController extends Controller
{
    public function __construct(
        private readonly MessageService $messageService
    ) {}

    /**
     * Get all messages for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 15);
        $messages = $this->messageService->getAllMessagesForUser($request->user()->id, $perPage);

        return response()->json([
            'data' => MessageResource::collection($messages->items()),
            'meta' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
                'from' => $messages->firstItem(),
                'to' => $messages->lastItem(),
            ],
        ]);
    }

    /**
     * Get unread messages for the authenticated user.
     */
    public function unread(Request $request): JsonResponse
    {
        $messages = $this->messageService->getUnreadMessagesForUser($request->user()->id);

        return response()->json([
            'data' => MessageResource::collection($messages),
            'count' => $messages->count(),
        ]);
    }

    /**
     * Mark a message as read.
     */
    public function markAsRead(MarkMessageReadRequest $request, int $messageId): JsonResponse
    {
        $success = $this->messageService->markAsRead($request->user()->id, $messageId);

        if (!$success) {
            return response()->json([
                'message' => 'Сообщение не найдено или уже прочитано',
            ], 404);
        }

        return response()->json([
            'message' => 'Сообщение отмечено как прочитанное',
        ]);
    }

    /**
     * Mark all messages as read for the authenticated user.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $count = $this->messageService->markAllAsRead($request->user()->id);

        return response()->json([
            'message' => "Отмечено как прочитанные: {$count} сообщений",
            'count' => $count,
        ]);
    }

    /**
     * Get message statistics for the authenticated user.
     */
    public function statistics(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $unreadCount = $this->messageService->getUnreadMessagesForUser($user->id)->count();
        $totalCount = $this->messageService->getAllMessagesForUser($user->id, 1)->total();

        return response()->json([
            'statistics' => [
                'total_messages' => $totalCount,
                'unread_messages' => $unreadCount,
                'read_messages' => $totalCount - $unreadCount,
            ],
        ]);
    }
}
