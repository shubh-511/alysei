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

	Route::get('get/store/prefilled/values', 'Api\StoreController@getPreFilledValues');
	Route::get('get/marketplace/walkthrough', 'Api\WalkthroughScreenController@getWalkThroughScreens');
	Route::get('get/marketplace/packages', 'Api\PackageController@getPackages');
	Route::get('get/marketplace/product/categories/{allCategories?}', 'Api\ProductController@getProductCategories');
	Route::get('get/marketplace/product/subcategories', 'Api\ProductController@getProductSubcategories');
	Route::get('get/marketplace/brand/label', 'Api\ProductController@getBrandLabels');
	Route::get('checkif/store/created', 'Api\StoreController@checkIfStoreCreated');
	Route::get('get/dashboard/screen', 'Api\StoreController@getDashboardScreen');

	Route::get('get/store/details', 'Api\StoreController@getStoreDetails');
	Route::post('update/store/details', 'Api\StoreController@updateStoreDetails');
	Route::post('update/product/details', 'Api\ProductController@updateProductDetails');
	Route::get('get/myproduct/list', 'Api\ProductController@getMyProductList');
	Route::post('delete/product', 'Api\ProductController@deleteProduct');

	Route::post('save/store', 'Api\StoreController@saveStoreDetails');
	Route::post('save/product', 'Api\ProductController@saveProductDetails');
	Route::post('delete/gallery/image', 'Api\StoreController@deleteGalleryImage');
	Route::get('search/product', 'Api\ProductController@searchProduct');
	Route::get('recent/search/product', 'Api\ProductController@recentSearchProduct');
	Route::get('get/product/detail', 'Api\ProductController@getProductDetail');

	Route::post('make/favourite/store/product', 'Api\FavouriteController@makeFavourite');
	Route::post('make/unfavourite/store/product', 'Api\FavouriteController@makeUnfavourite');

	Route::post('do/review/store/product', 'Api\RatingController@doReview');
	Route::get('get/all/reviews', 'Api\RatingController@getAllReviews');
	Route::get('get/seller/profile/{storeid?}', 'Api\StoreController@getSellerProfile');
	Route::get('get/search/product/listing', 'Api\ProductController@getSearchProductListing');
	Route::post('save/product/enquery', 'Api\ProductController@saveProductEnquery');
	Route::get('get/enqueries/{tab?}', 'Api\ProductController@getProductEnquery');

	Route::get('get/box/detail/{boxId}', 'Api\HomepageController@getBoxDetails');

	Route::get('get/homescreen', 'Api\HomepageController@getHomeScreen');
	Route::get('get/products', 'Api\HomepageController@getProducts');
	Route::get('get/products/by/region', 'Api\HomepageController@getProductsByRegions');
	Route::get('get/products/by/category', 'Api\HomepageController@getProductsByCategory');
	Route::get('filter', 'Api\HomepageController@filter');

	Route::get('get/product/properties', 'Api\HomepageController@getProductProperties');
	Route::get('get/conservation/methods', 'Api\HomepageController@getConservationMethod');

	
});