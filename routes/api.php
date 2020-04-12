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

Route::post('/register', 'AuthController@register');
Route::post('/login', 'AuthController@login');
Route::post('/logout', 'AuthController@logout');

//Protected routes 
Route::group(['middleware' => 'auth.jwt'], function () {
    Route::get('test', 'ApiController@test');
 
    // Route::get('user', 'ApiController@getAuthUser');
 
    // Route::get('products', 'ProductController@index');
    // Route::get('products/{id}', 'ProductController@show');
    // Route::post('products', 'ProductController@store');
    // Route::put('products/{id}', 'ProductController@update');
    // Route::delete('products/{id}', 'ProductController@destroy');
});