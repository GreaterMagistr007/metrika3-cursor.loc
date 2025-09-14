<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

function getUserIp(): ?string
{
    $request = app(Request::class);

    return $request->getClientIp();
}

if (getUserIp() === env('ADMIN_IP')) {
    // Админ-панель (должна быть ПЕРЕД основным приложением)
    Route::prefix('admin')->group(function () {
        Route::get('/', function () {
            return view('admin-panel');
        });

        Route::get('/{any}', function () {
            return view('admin-panel');
        })->where('any', '.*');
    });
}


// Основное приложение
Route::get('/', function () {
    return view('main-app');
});

Route::get('/{any}', function () {
    return view('main-app');
})->where('any', '^(?!api|test_api_browser\.html).*');
