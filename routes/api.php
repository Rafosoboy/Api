<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::get('auth/init', 'AuthenticateController@init');
Route::post('auth/login', 'AuthenticateController@login');
Route::post('auth/register', 'AuthenticateController@register');
Route::post('auth/logout', 'AuthenticateController@logout');
Route::post('auth/sendResetLink', 'AuthenticateController@sendResetLink');
Route::post('auth/resetPassword', 'AuthenticateController@resetPassword');


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
