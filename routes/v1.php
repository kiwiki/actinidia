<?php

use App\Http\V1\Controllers\AuthController;
use App\Http\V1\Controllers\ComponentController;
use Illuminate\Support\Facades\Route;

// Authentication
Route::group(['prefix' => 'auth', 'as' => 'auth.'], function () {
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('confirm', [AuthController::class, 'confirm'])->name('confirm');
    Route::post('refresh', [AuthController::class, 'refresh'])->name('refresh');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('me', [AuthController::class, 'me'])->name('me');
    Route::patch('me', [AuthController::class, 'update'])->name('update');
    Route::post('check', [AuthController::class, 'check'])->name('check');
});

Route::group(['middleware' => 'throttle:60,1,default'], function(){
    // Components
    Route::apiResource('components', ComponentController::class);
});
