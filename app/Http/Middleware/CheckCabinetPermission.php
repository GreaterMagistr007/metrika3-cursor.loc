<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Cabinet;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

final class CheckCabinetPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): BaseResponse
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'message' => 'Необходима аутентификация',
                'error_code' => 'UNAUTHENTICATED'
            ], 401);
        }

        // Получаем ID кабинета из заголовка или тела запроса
        $cabinetId = $this->getCabinetId($request);
        
        if (!$cabinetId) {
            return response()->json([
                'message' => 'Не указан ID кабинета',
                'error_code' => 'CABINET_ID_REQUIRED'
            ], 400);
        }

        // Проверяем, что кабинет существует
        $cabinet = Cabinet::find($cabinetId);
        if (!$cabinet) {
            return response()->json([
                'message' => 'Кабинет не найден',
                'error_code' => 'CABINET_NOT_FOUND'
            ], 404);
        }

        // Проверяем права пользователя в кабинете
        if (!$user->hasPermissionInCabinet($permission, $cabinetId)) {
            return response()->json([
                'message' => 'Недостаточно прав для выполнения действия',
                'error_code' => 'INSUFFICIENT_PERMISSIONS',
                'required_permission' => $permission,
                'cabinet_id' => $cabinetId
            ], 403);
        }

        // Добавляем кабинет в запрос для использования в контроллерах
        $request->merge(['current_cabinet' => $cabinet]);

        return $next($request);
    }

    /**
     * Get cabinet ID from request.
     */
    private function getCabinetId(Request $request): ?int
    {
        // 1. Из заголовка X-Cabinet-Id
        if ($request->hasHeader('X-Cabinet-Id')) {
            return (int) $request->header('X-Cabinet-Id');
        }

        // 2. Из параметра маршрута {cabinet}
        if ($request->route('cabinet')) {
            $cabinet = $request->route('cabinet');
            return is_numeric($cabinet) ? (int) $cabinet : $cabinet->id;
        }

        // 3. Из тела запроса
        if ($request->has('cabinet_id')) {
            return (int) $request->input('cabinet_id');
        }

        return null;
    }
}