<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SpaceController;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\IsUserAuth;
use Illuminate\Support\Facades\Route;

//Rutas públicas
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

//Rutas Privadas

Route::middleware([IsUserAuth::class])->group(function () {

    Route::controller(AuthController::class)->group(function () {

        Route::post('/logout', 'logout');
        Route::get('/me', 'getUser');

    });

    Route::get('/spaces', [SpaceController::class, 'getSpaces']);
    Route::get('/spaces/{id}', [SpaceController::class, 'getSpaceById']);

    Route::middleware([IsAdmin::class])->group(function () {

        Route::controller(SpaceController::class)->group(function () {
    
            Route::post('/spaces', 'addSpace');
            Route::patch('/spaces/{id}', 'updateSpaceById');
            Route::delete('/spaces/{id}', 'deleteSpaceById');

        });
    
    });

});