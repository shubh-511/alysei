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
	Route::post('share/post', 'Api\ActivityController@sharePost');
	Route::post('delete/post', 'Api\ActivityController@deletePost');
	Route::get('get/post/detail', 'Api\ActivityController@getPostDetails');

	
	Route::post('post/comment', 'Api\ActivityController@commentPost');
	Route::post('delete/post/comment', 'Api\ActivityController@deletePostComment');
	Route::post('reply/post/comment', 'Api\ActivityController@replyPost');

	Route::post('follow/user', 'Api\FollowUserController@followUnfollowUser');
	Route::get('get/followers', 'Api\FollowUserController@getFollowersList');

	Route::get('get/permissions', 'Api\ConnectUserController@getPermissions');

	Route::post('send/connection/request', 'Api\ConnectUserController@sendConnectionRequest');
	Route::get('get/pending/recieved/request', 'Api\ConnectUserController@getMyPendingRecievedRequest');
	Route::get('get/pending/sent/request', 'Api\ConnectUserController@getMyPendingSentRequest');
	Route::post('accept/reject/request', 'Api\ConnectUserController@acceptOrRejectConnection');

	Route::get('get/all/user/post/{postType}', 'Api\ActivityController@getAllUserPosts');
	Route::get('get/activity/feed', 'Api\ActivityController@getActivityFeeds');

	Route::get('get/roles/for/connection', 'Api\PrivacyController@getRolesForConnection');
	Route::get('get/user/privacy', 'Api\PrivacyController@getPrivacyData');
	Route::post('save/privacy', 'Api\PrivacyController@savePrivacy');
	Route::post('save/email/preference', 'Api\PrivacyController@saveEmailPreference');

});