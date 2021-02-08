<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::prefix('user')->group(function() {
    Route::get('/', 'UserController@index');
});

Route::get('/', function () {
    //return view('welcome');
    return redirect('login');
});
Route::get('/login', 'LoginController@index')->name('login');
Route::post('login/admin-login', 'LoginController@adminLogin');
Route::get('/logout', 'AdminController@logout');


//Route::get('/home', 'HomeController@index')->name('home');
Route::group(['prefix'=>'login','middleware'=>['web','isAdminLogin']], function(){

	Route::get('dashboard', 'AdminController@dashboard');
	Route::get('/users', 'UserController@list');
	Route::get('/users/edit/{id}', 'UserController@edit');
	Route::post('/users/update/{id}', 'UserController@update');
	Route::get('/users/show/{id}', 'UserController@show');

	Route::post('/user-status', 'UserController@userStatus');

});
