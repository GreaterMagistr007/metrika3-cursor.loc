<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\TestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Test routes (for development only)
Route::prefix('test')->group(function () {
    Route::get('/get-current-otp', [TestController::class, 'getCurrentOtp']);
});

// Public authentication routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/complete-registration', [AuthController::class, 'completeRegistration']);
    Route::post('/request-otp', [AuthController::class, 'requestOtp']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/telegram', [AuthController::class, 'telegram']);
});

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    // Cabinet routes
    Route::prefix('cabinets')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\CabinetController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Api\CabinetController::class, 'store']);
        Route::get('/{cabinet}', [\App\Http\Controllers\Api\CabinetController::class, 'show']);
        Route::put('/{cabinet}', [\App\Http\Controllers\Api\CabinetController::class, 'update'])->middleware('cabinet.permission:cabinet.manage');
        Route::delete('/{cabinet}', [\App\Http\Controllers\Api\CabinetController::class, 'destroy'])->middleware('cabinet.permission:cabinet.manage');
        
        // Cabinet user management
        Route::post('/{cabinet}/invite', [\App\Http\Controllers\Api\CabinetUserController::class, 'invite'])->middleware('cabinet.permission:user.invite');
        Route::delete('/{cabinet}/users/{user}', [\App\Http\Controllers\Api\CabinetUserController::class, 'remove'])->middleware('cabinet.permission:user.remove');
        Route::patch('/{cabinet}/transfer-ownership', [\App\Http\Controllers\Api\CabinetUserController::class, 'transferOwnership'])->middleware('cabinet.permission:cabinet.manage');
        
        // Cabinet user permissions
        Route::get('/{cabinet}/users/{user}/permissions', [\App\Http\Controllers\Api\CabinetUserPermissionController::class, 'index'])->middleware('cabinet.permission:user.view');
        Route::post('/{cabinet}/users/{user}/permissions', [\App\Http\Controllers\Api\CabinetUserPermissionController::class, 'store'])->middleware('cabinet.permission:user.manage');
        Route::delete('/{cabinet}/users/{user}/permissions', [\App\Http\Controllers\Api\CabinetUserPermissionController::class, 'destroy'])->middleware('cabinet.permission:user.manage');
    });

    // Message routes
    Route::prefix('messages')->group(function () {
        Route::get('/', [MessageController::class, 'index']);
        Route::get('/unread', [MessageController::class, 'unread']);
        Route::get('/statistics', [MessageController::class, 'statistics']);
        Route::post('/{message}/read', [MessageController::class, 'markAsRead']);
        Route::post('/mark-all-read', [MessageController::class, 'markAllAsRead']);
    });

});
