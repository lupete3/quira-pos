<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ClientController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\SaleController;
use App\Http\Controllers\Api\V1\SyncController;
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

// API Version 1
Route::prefix('v1')->group(function () {

    // Public authentication routes
    Route::post('/login', [AuthController::class, 'login']);

    // Protected routes (requires Sanctum token)
    Route::middleware('auth:sanctum')->group(function () {

        // Authentication
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);

        // Synchronization endpoints
        Route::prefix('sync')->group(function () {
            Route::get('/initial/{storeId}', [SyncController::class, 'initial']);
            Route::post('/incremental', [SyncController::class, 'incremental']);
            Route::post('/upload-sales', [SyncController::class, 'uploadSales']);
        });

        // Resource endpoints
        Route::apiResource('products', ProductController::class)->only(['index', 'show']);
        Route::apiResource('clients', ClientController::class);
        Route::apiResource('sales', SaleController::class);
    });
});
