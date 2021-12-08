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
Route::group(['prefix'=>'dashboard/recipe','middleware'=>['web','isAdminLogin']], function(){
    Route::get('/', 'RecipeController@index');
    Route::get('/ingredients', 'IngredientsController@index');
    Route::get('/ingredient/add', 'IngredientsController@create');
    Route::post('/ingredient/store', 'IngredientsController@store');
});
