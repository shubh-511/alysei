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

/*Route::middleware('auth:api')->get('/recipe', function (Request $request) {
    return $request->user();
});*/

Route::group(['middleware' => 'auth:api'], function(){

    Route::get('get/recipe/categories', 'Api\RecipeController@getRecipeCategories');
    Route::get('get/recipe/ingredients', 'Api\RecipeController@getRecipeIngredients');
    Route::get('get/recipe/tools', 'Api\RecipeController@getRecipeTools');
    Route::get('get/recipe/regions', 'Api\RecipeController@getRecipeRegions');
    Route::get('get/recipe/meals', 'Api\RecipeController@getRecipeMeals');
    Route::get('get/recipe/courses', 'Api\RecipeController@getRecipeCourses');

    Route::post('create/recipe', 'Api\RecipeController@createRecipe');

});