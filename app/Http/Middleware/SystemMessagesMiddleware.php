<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\MessageService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SystemMessagesMiddleware
{
    public function __construct(
        private readonly MessageService $messageService
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only process for authenticated users and API requests
        if (!$request->user() || !$request->is('api/*')) {
            return $response;
        }

        // Skip for admin users
        if ($request->user() instanceof \App\Models\AdminUser) {
            return $response;
        }

        try {
            // Process trigger conditions
            $this->messageService->processTriggers($request->user());

            // Get unread messages for the user
            $unreadMessages = $this->messageService->getUnreadMessagesForUser($request->user()->id);

            if ($unreadMessages->isNotEmpty()) {
                // Add messages to response header
                $messages = $unreadMessages->map(function ($message) {
                    return [
                        'id' => $message->id,
                        'type' => $message->type,
                        'title' => $message->title,
                        'text' => $message->text,
                        'url' => $message->url,
                        'button_text' => $message->button_text,
                        'button_url' => $message->button_url,
                        'is_system' => $message->isSystem(),
                        'created_at' => $message->created_at->toISOString(),
                    ];
                })->toArray();

                $response->headers->set('X-System-Messages', json_encode($messages));
            }
        } catch (\Exception $e) {
            // Log error but don't break the response
            \Log::error('SystemMessagesMiddleware error: ' . $e->getMessage());
        }

        return $response;
    }
}
