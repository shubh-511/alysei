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

/*Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});*/
Route::get('get/privacy/policy', 'PageController@privacyAndPolicy');
Route::group(['middleware' => 'auth:api'], function(){

	Route::get('get/states', 'Api\SearchController@getStates');
	Route::get('get/my/selected/hubs', 'Api\SearchController@getMySelectedHubs');
	Route::get('get/field/value/{fieldId}', 'Api\SearchController@getFieldValues');
	Route::get('get/pickup/delivery/fields', 'Api\SearchController@getPickupOrDelivery');
	Route::get('search', 'Api\SearchController@search');

});
