<?php

use App\Http\Controllers\Api\AuthController;
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

// Public authentication routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
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
        Route::put('/{cabinet}', [\App\Http\Controllers\Api\CabinetController::class, 'update']);
        Route::delete('/{cabinet}', [\App\Http\Controllers\Api\CabinetController::class, 'destroy']);
        
        // Cabinet user management
        Route::post('/{cabinet}/invite', [\App\Http\Controllers\Api\CabinetUserController::class, 'invite']);
        Route::delete('/{cabinet}/users/{user}', [\App\Http\Controllers\Api\CabinetUserController::class, 'remove']);
        Route::patch('/{cabinet}/transfer-ownership', [\App\Http\Controllers\Api\CabinetUserController::class, 'transferOwnership']);
    });
});
