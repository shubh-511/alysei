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

/*Route::middleware('auth:api')->get('/marketplace', function (Request $request) {
    return $request->user();
});*/

Route::group(['middleware' => 'auth:api'], function(){

	Route::get('get/marketplace/walkthrough', 'Api\WalkthroughScreenController@getWalkThroughScreens');
	Route::get('get/marketplace/packages', 'Api\PackageController@getPackages');
	Route::get('get/marketplace/product/categories', 'Api\ProductController@getProductCategories');
	Route::get('get/marketplace/product/subcategories', 'Api\ProductController@getProductSubcategories');
	Route::get('get/marketplace/brand/label', 'Api\ProductController@getBrandLabels');

	Route::post('save/store', 'Api\StoreController@saveStoreDetails');
	Route::post('save/product', 'Api\ProductController@saveProductDetails');
});