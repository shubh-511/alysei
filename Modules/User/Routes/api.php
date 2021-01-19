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

Route::get('get/registration/fields/{id}', 'Api\UserController@getRegistrationFormFields');
Route::get('get/roles', 'Api\UserController@getRoles');
Route::post('user/register', 'Api\UserController@register');
Route::post('user/login', 'Api\UserController@login');

Route::group(['middleware' => 'auth:api'], function(){
	Route::post('userinfo', 'Api\UserController@userinfo');
});

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });