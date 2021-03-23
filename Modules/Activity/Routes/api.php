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

/*Route::middleware('auth:api')->get('/activity', function (Request $request) {
    return $request->user();
});*/

Route::group(['middleware' => 'auth:api'], function(){
	Route::post('add/post', 'Api\ActivityController@addPost');
	Route::post('edit/post', 'Api\ActivityController@editPost');
	Route::post('delete/post', 'Api\ActivityController@deletePost');
	Route::get('get/post/detail', 'Api\ActivityController@getPostDetails');

	Route::post('post/like', 'Api\ActivityController@likeUnlikePost');
	Route::post('post/comment', 'Api\ActivityController@commentPost');
	Route::post('delete/post/comment', 'Api\ActivityController@deletePostComment');
	Route::post('reply/post/comment', 'Api\ActivityController@replyPost');

	Route::post('follow/user', 'Api\FollowUserController@followUnfollowUser');
	Route::get('get/followers', 'Api\FollowUserController@getFollowersList');

});