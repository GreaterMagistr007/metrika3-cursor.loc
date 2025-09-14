<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\Admin\CabinetController as AdminCabinetController;
use App\Http\Controllers\Api\Admin\AuditLogController as AdminAuditLogController;
use App\Http\Controllers\Api\Admin\MessageController as AdminMessageController;
use App\Http\Controllers\Api\Admin\AdminUserController as AdminAdminUserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "admin" middleware group. Make something great!
|
*/

// Admin authentication routes (no middleware)
Route::prefix('api/admin/auth')->group(function () {
    Route::post('register', [AdminAuthController::class, 'register']);
    Route::post('login', [AdminAuthController::class, 'login']);
    Route::post('logout', [AdminAuthController::class, 'logout'])->middleware('auth:admin');
    Route::get('profile', [AdminAuthController::class, 'profile'])->middleware('auth:admin');
    Route::put('profile', [AdminAuthController::class, 'updateProfile'])->middleware('auth:admin');
});

// Admin routes (require authentication and admin role)
Route::prefix('api/admin')->middleware('auth:admin')->group(function () {
    
    // Admin users management (super admin only)
    Route::middleware('admin:super_admin')->group(function () {
        Route::apiResource('admin-users', AdminAdminUserController::class);
        Route::put('admin-users/{adminUser}/role', [AdminAdminUserController::class, 'updateRole']);
    });

    // Users management
    Route::apiResource('users', AdminUserController::class)->middleware('admin');
    Route::get('users/{user}/cabinets', [AdminUserController::class, 'getUserCabinets'])->middleware('admin');
    Route::get('users/{user}/audit-logs', [AdminUserController::class, 'getUserAuditLogs'])->middleware('admin');
    Route::get('users/{user}/deletion-summary', [AdminUserController::class, 'getDeletionSummary'])->middleware('admin');

    // Cabinets management
    Route::apiResource('cabinets', AdminCabinetController::class)->middleware('admin');
    Route::get('cabinets/{cabinet}/users', [AdminCabinetController::class, 'getCabinetUsers'])->middleware('admin');
    Route::get('cabinets/{cabinet}/audit-logs', [AdminCabinetController::class, 'getCabinetAuditLogs'])->middleware('admin');
    Route::post('cabinets/{cabinet}/transfer-ownership', [AdminCabinetController::class, 'transferOwnership'])->middleware('admin');

    // Audit logs
    Route::get('audit-logs', [AdminAuditLogController::class, 'index'])->middleware('admin');
    Route::get('audit-logs/statistics', [AdminAuditLogController::class, 'statistics'])->middleware('admin');
    Route::get('audit-logs/recent', [AdminAuditLogController::class, 'recent'])->middleware('admin');

    // Messages management
    Route::apiResource('messages', AdminMessageController::class)->middleware('admin');
    Route::get('messages-statistics', [AdminMessageController::class, 'statistics'])->middleware('admin');
    Route::get('message-types', [AdminMessageController::class, 'types'])->middleware('admin');
    Route::patch('messages/{message}/toggle-active', [AdminMessageController::class, 'toggleActive'])->middleware('admin');

    // System statistics
    Route::get('statistics', function () {
        return response()->json([
            'users' => [
                'total' => \App\Models\User::count(),
                'active' => \App\Models\User::whereNotNull('last_login_at')->count(),
            ],
            'cabinets' => [
                'total' => \App\Models\Cabinet::count(),
                'active' => \App\Models\Cabinet::where('is_active', true)->count(),
            ],
            'messages' => [
                'total' => \App\Models\Message::count(),
                'active' => \App\Models\Message::where('is_active', true)->count(),
            ],
            'audit_logs' => [
                'total' => \App\Models\AuditLog::count(),
                'today' => \App\Models\AuditLog::whereDate('created_at', today())->count(),
            ],
        ]);
    })->middleware('admin');
});
