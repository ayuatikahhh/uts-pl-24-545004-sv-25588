<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Publik: login (dibatasi 10 percobaan per menit untuk mengurangi tebak-tebakan password)
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

// Terlindungi: wajib token Sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});