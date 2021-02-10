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

Route::get('get/roles', 'Api\RegisterController@getRoles');
Route::get('get/walkthroughscreens/{role_id?}', 'Api\RegisterController@getWalkThroughScreens');
Route::get('get/registration/fields/{role_id}', 'Api\RegisterController@getRegistrationFormFields');
Route::post('user/register', 'Api\RegisterController@register');
Route::post('user/login', 'Api\LoginController@login');
Route::post('forgot/password', 'Api\ResetPasswordController@forgotPassword');
Route::post('verfiy/password/otp', 'Api\ResetPasswordController@verifyForgotPasswordOtp');
Route::post('reset/password', 'Api\ResetPasswordController@resetPassword');

Route::get('get/hubs/{role_id}', 'Api\HubController@getHubs');


Route::group(['middleware' => 'auth:api'], function(){
	Route::get('userinfo', 'Api\UserController@userinfo');
	Route::get('user/settings', 'Api\UserController@userSettings');
	Route::post('update/user/settings', 'Api\UserController@updateUserSettings');
	Route::post('change/password', 'Api\ResetPasswordController@changePassword');
	Route::post('logout', 'Api\LoginController@logout');
});

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });