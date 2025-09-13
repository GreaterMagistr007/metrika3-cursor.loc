<?php

use Illuminate\Support\Facades\Route;

// Основное приложение
Route::get('/', function () {
    return view('main-app');
});

Route::get('/{any}', function () {
    return view('main-app');
})->where('any', '.*');

// Админ-панель
Route::prefix('admin')->group(function () {
    Route::get('/', function () {
        return view('admin-panel');
    });
    
    Route::get('/{any}', function () {
        return view('admin-panel');
    })->where('any', '.*');
});
