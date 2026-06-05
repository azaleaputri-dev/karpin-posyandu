<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\IotMeasurementController;
use App\Http\Controllers\Api\MobileDataController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('mobile')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/dashboard', [MobileDataController::class, 'dashboard']);
        Route::get('/children', [MobileDataController::class, 'children']);
        Route::post('/children', [MobileDataController::class, 'storeChild']);
        Route::put('/children/{child}', [MobileDataController::class, 'updateChild']);
        Route::delete('/children/{child}', [MobileDataController::class, 'destroyChild']);
        Route::get('/children/{child}', [MobileDataController::class, 'childDetail']);
        Route::get('/measurements', [MobileDataController::class, 'measurements']);
        Route::post('/measurements', [MobileDataController::class, 'storeMeasurement']);
        Route::put('/measurements/{measurement}', [MobileDataController::class, 'updateMeasurement']);
        Route::delete('/measurements/{measurement}', [MobileDataController::class, 'destroyMeasurement']);
    });
});

Route::prefix('iot')->group(function () {
    Route::get('/ping', [IotMeasurementController::class, 'ping']);
    Route::post('/measurements', [IotMeasurementController::class, 'store']);
});
