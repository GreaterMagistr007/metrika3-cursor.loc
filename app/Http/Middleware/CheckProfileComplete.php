<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckProfileComplete
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $return
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Если пользователь не авторизован, пропускаем проверку
        if (!$user) {
            return $next($request);
        }

        // Проверяем, заполнен ли профиль
        if (empty($user->name) || empty($user->phone)) {
            // Если это API запрос, возвращаем JSON ответ
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Необходимо заполнить профиль для доступа к функционалу',
                    'error_code' => 'PROFILE_INCOMPLETE',
                    'redirect_to' => '/complete-profile'
                ], 403);
            }

            // Для веб-запросов перенаправляем на страницу завершения профиля
            return redirect('/complete-profile');
        }

        return $next($request);
    }
}