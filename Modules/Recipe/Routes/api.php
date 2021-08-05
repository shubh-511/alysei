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

    Route::get('get/recipe/categories', 'Api\RecipeCategoryController@getRecipeCategories');
    Route::get('get/recipe/ingredients', 'Api\RecipeIngredientController@getRecipeIngredients');
    Route::get('get/recipe/tools', 'Api\RecipeToolController@getRecipeTools');
    Route::get('get/recipe/regions', 'Api\RecipeRegionController@getRecipeRegions');
    Route::get('get/recipe/meals', 'Api\RecipeMealController@getRecipeMeals');
    Route::get('get/recipe/courses', 'Api\RecipeCourseController@getRecipeCourses');

});