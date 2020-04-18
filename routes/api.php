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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

//Public routes
Route::post('/register', 'AuthController@register');
Route::post('/login', 'AuthController@login');
Route::post('/logout', 'AuthController@logout');

//Protected routes 
Route::group(['middleware' => 'auth.jwt'], function () {
	//Coins Routes
    Route::get('/coins', 'ApiController@getCoins');
    Route::get('/coins/{id}', 'ApiController@getCoin');

    //Wallet Routes
    Route::post('/wallet', 'ApiController@createWallet');
    Route::get('/wallet/{id}', 'ApiController@getWallet');
    Route::get('/wallet/{id}/address/{address}/balance', 'ApiController@getAddressBalance');

    //Keys
    Route::post('/key', 'ApiController@generatePrivateKey');
});