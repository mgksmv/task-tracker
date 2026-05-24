<?php

use App\Api\V1\Controllers\AuthController;
use App\Api\V1\Controllers\UserController;
use App\Api\V1\Controllers\UserMeController;
use Illuminate\Support\Facades\Route;

Route::prefix('/v1')->group(function () {
    Route::prefix('/auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
        });
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('/users')->group(function () {
            Route::prefix('/me')->group(function () {
                Route::get('/', [UserMeController::class, 'show']);
                Route::put('/', [UserMeController::class, 'update']);
                Route::put('/password', [UserMeController::class, 'updatePassword']);
            });

            Route::get('/', [UserController::class, 'index']);
            Route::post('/all', [UserController::class, 'getAll']);
            Route::post('/', [UserController::class, 'store']);
            Route::get('/{user}', [UserController::class, 'show']);
            Route::put('/{user}', [UserController::class, 'update']);
            Route::delete('/{user}', [UserController::class, 'destroy']);
        });
    });
});
