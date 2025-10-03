<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransaccionController;
use App\Http\Controllers\UserController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rutas protegidas
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('/users', UserController::class);
    Route::apiResource('/transacciones', TransaccionController::class);
    Route::get('/export', [TransaccionController::class, 'exportCsv']);
    Route::get('/resumen-usuario', [TransaccionController::class, 'resumenPorUsuario']);
});

