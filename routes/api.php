<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductPriceController;
use Illuminate\Support\Facades\Route;

// Rutas de consulta. No requieren iniciar sesión.
Route::get('/health', fn () => response()->json([
    'success' => true,
    'message' => 'API de productos funcionando correctamente',
]));
Route::get('/currencies', [CurrencyController::class, 'index']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);
Route::get('/products/{product}/prices', [ProductPriceController::class, 'index']);

// Se limita el número de intentos para evitar ataques de fuerza bruta.
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

// Cualquier cambio en la base de datos requiere un token válido.
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{product}', [ProductController::class, 'update']);
    Route::patch('/products/{product}', [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);
    Route::post('/products/{product}/prices', [ProductPriceController::class, 'store']);
});
