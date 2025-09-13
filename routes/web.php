<?php

use Illuminate\Support\Facades\Route;

// Админ-панель (должна быть ПЕРЕД основным приложением)
Route::prefix('admin')->group(function () {
    Route::get('/', function () {
        return view('admin-panel');
    });
    
    Route::get('/{any}', function () {
        return view('admin-panel');
    })->where('any', '.*');
});

// Основное приложение
Route::get('/', function () {
    return view('main-app');
});

Route::get('/{any}', function () {
    return view('main-app');
})->where('any', '.*');
