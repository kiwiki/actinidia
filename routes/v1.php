<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Authentication
Route::group(['prefix' => 'auth', 'as' => 'auth.'], function () {
    Route::post('login', 'AuthController@login')->name('login');
    Route::post('confirm', 'AuthController@confirm')->name('confirm');
    Route::post('refresh', 'AuthController@refresh')->name('refresh');
    Route::post('logout', 'AuthController@logout')->name('logout');
    Route::get('me', 'AuthController@me')->name('me');
});

Route::get('/users', function(Request $request){
    return \App\User::all();
});

Route::get('/', function (Request $request) {
    return 'assdasd';
});
