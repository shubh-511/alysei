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
Route::post('verfiy/password/otp', 'Api\ResetPasswordController@verifyForgotPasswordOtp'); // not required
Route::post('reset/password', 'Api\ResetPasswordController@resetPassword');
Route::post('verify/otp', 'Api\RegisterController@verifyOtp');
Route::post('resend/otp', 'Api\RegisterController@resendOtp');

Route::get('get/hubs', 'Api\HubController@getHubs');
Route::get('get/states', 'Api\CountryController@getStates');
Route::get('get/cities', 'Api\CountryController@getCities');
Route::get('get/countries', 'Api\CountryController@getCountries');


Route::group(['middleware' => 'auth:api'], function(){

	Route::get('userinfo', 'Api\UserController@userinfo');
	Route::get('user/settings', 'Api\FeaturedListingsController@userSettings');
	Route::post('update/user/settings', 'Api\UserController@updateUserSettings');
	Route::post('change/password', 'Api\ResetPasswordController@changePassword');
	Route::post('logout', 'Api\LoginController@logout');
	Route::get('get/alysei/progress', 'Api\LoginController@alyseiProgress');

	Route::post('update/contact/details', 'Api\UserController@updateContactDetails');
	Route::post('post/featured/listing', 'Api\FeaturedListingsController@postFeaturedListing');
	Route::get('get/featured/listing/{featuredListingid}', 'Api\FeaturedListingsController@editFeaturedListing');

	Route::get('get/hub/countries', 'Api\HubController@getHubCountries');
	Route::get('get/active/upcoming/countries', 'Api\HubController@getActiveAndUpcomingCountries');

	Route::post('get/hub/city', 'Api\HubController@getHubsCity');
	Route::post('get/hubs', 'Api\HubController@getHubs');
	Route::post('post/hubs', 'Api\HubController@postUserHubs');

	Route::get('get/user/submited/fields', 'Api\UserController@getUserSubmitedFields');
	Route::post('update/user/profile', 'Api\UserController@updateUserProfile');

	Route::get('get/user/certificates', 'Api\UserController@getUserCertificates');
	Route::post('update/user/certificates', 'Api\UserController@updateUserCertificates');


});

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });