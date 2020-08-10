<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Authentication
Route::group(['prefix' => 'auth'], function () {
    Route::post('login', 'AuthController@login');
    Route::post('confirm', 'AuthController@confirm')->name('confirm');

    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');
});

Route::get('/users', function(Request $request){
    return \App\User::all();
});

Route::get('/', function (Request $request) {
    return 'assdasd';
});
