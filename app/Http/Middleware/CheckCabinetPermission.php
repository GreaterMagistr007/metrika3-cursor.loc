<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\CabinetUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CheckCabinetPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'message' => 'Не авторизован',
                'error_code' => 'UNAUTHORIZED'
            ], 401);
        }

        // Get cabinet ID from request
        $cabinetId = $this->getCabinetId($request);
        
        if (!$cabinetId) {
            return response()->json([
                'message' => 'ID кабинета не указан',
                'error_code' => 'CABINET_ID_REQUIRED'
            ], 400);
        }

        // Check if user has permission in cabinet
        if (!$user->hasPermissionInCabinet($permission, $cabinetId)) {
            return response()->json([
                'message' => 'Недостаточно прав для выполнения действия',
                'error_code' => 'INSUFFICIENT_PERMISSIONS',
                'required_permission' => $permission,
                'cabinet_id' => $cabinetId
            ], 403);
        }

        // Add cabinet_id to request for use in controllers
        $request->merge(['current_cabinet_id' => $cabinetId]);

        return $next($request);
    }

    /**
     * Get cabinet ID from request.
     */
    private function getCabinetId(Request $request): ?int
    {
        // Try to get from header first
        $cabinetId = $request->header('X-Cabinet-Id');
        
        if ($cabinetId) {
            return (int) $cabinetId;
        }

        // Try to get from route parameter
        $cabinetId = $request->route('cabinet');
        
        if ($cabinetId) {
            return (int) $cabinetId;
        }

        // Try to get from request body
        $cabinetId = $request->input('cabinet_id');
        
        if ($cabinetId) {
            return (int) $cabinetId;
        }

        return null;
    }
}
