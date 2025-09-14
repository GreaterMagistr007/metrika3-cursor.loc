<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\AdminUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CheckAdminPermission
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $role = 'admin'): Response
    {
        $admin = $request->user('admin');

        // Check if user is authenticated as admin
        if (!$admin instanceof AdminUser) {
            return response()->json([
                'message' => 'Доступ запрещен. Требуется аутентификация администратора.',
                'error_code' => 'ADMIN_AUTH_REQUIRED',
            ], 401);
        }

        // Check role permissions
        if ($role === 'super_admin' && !$admin->isSuperAdmin()) {
            return response()->json([
                'message' => 'Доступ запрещен. Требуются права супер-администратора.',
                'error_code' => 'SUPER_ADMIN_REQUIRED',
            ], 403);
        }

        if ($role === 'admin' && !$admin->isAdmin() && !$admin->isSuperAdmin()) {
            return response()->json([
                'message' => 'Доступ запрещен. Требуются права администратора.',
                'error_code' => 'ADMIN_REQUIRED',
            ], 403);
        }

        return $next($request);
    }
}
