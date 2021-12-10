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
    

    //Admin Ingredients Routes
    Route::get('/ingredients', 'IngredientsController@index');
    Route::get('/ingredient/add', 'IngredientsController@create');
    Route::post('/ingredient/store', 'IngredientsController@store');
    Route::get('/ingredient/edit/{id}', 'IngredientsController@edit');
    Route::post('/ingredient/update/{id}', 'IngredientsController@update');

    //Admin Meal Routes
    Route::get('/meals', 'MealsController@index');
    Route::get('/meal/add', 'MealsController@create');
    Route::post('/meal/store', 'MealsController@store');
    Route::get('/meal/edit/{id}', 'MealsController@edit');
    Route::post('/meal/update/{id}', 'MealsController@update');

    //Admin Regions Routes
    Route::get('/regions', 'RegionsController@index');
    Route::get('/region/add', 'RegionsController@create');
    Route::post('/region/store', 'RegionsController@store');
    Route::get('/region/edit/{id}', 'RegionsController@edit');
    Route::post('/region/update/{id}', 'RegionsController@update');
});
