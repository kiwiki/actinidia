<?php

use App\Http\V1\Controllers\AuthController;
use Illuminate\Http\Request;
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

Route::middleware(['auth:api', 'throttle:60,1,default'])->group(function () {
    Route::get('/users', function (Request $request) {
        return \App\Models\User::all();
    });
});


Route::get('/', function (Request $request) {
    return 'assdasd';
});
