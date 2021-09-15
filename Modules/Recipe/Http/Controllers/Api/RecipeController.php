<?php

namespace Modules\Recipe\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CoreController;
use Modules\User\Entities\User; 
use Modules\User\Entities\DeviceToken; 
use Modules\Recipe\Entities\RecipeCategory; 
use Modules\Recipe\Entities\RecipeCourse;
use Modules\Recipe\Entities\RecipeIngredient; 
use Modules\Recipe\Entities\RecipeMeal; 
use Modules\Recipe\Entities\RecipeRegion; 
use Modules\Recipe\Entities\RecipeTool; 
use Modules\Recipe\Entities\Recipe; 
use Modules\Recipe\Entities\RecipeFavourite;
use Modules\Recipe\Entities\RecipeSavedCategory;
use Modules\Recipe\Entities\RecipeCookingSkill;
use Modules\Recipe\Entities\RecipeDiet;
use Modules\Recipe\Entities\RecipeStep;
use Modules\Recipe\Entities\RecipeSavedIngredient;
use Modules\Recipe\Entities\RecipeSavedTool;
use Modules\Recipe\Entities\RecipeMapStepTool;
use Modules\Recipe\Entities\RecipeMapStepIngredient;
use Modules\Recipe\Entities\RecipeEnquery;
use Modules\Recipe\Entities\RecipeFoodIntolerance;
use Modules\Recipe\Entities\RecipeReviewRating;
use Modules\Recipe\Entities\Preference;
use Modules\Recipe\Entities\PreferenceMapCousin;
use Modules\User\Entities\Cousin;
use Modules\Recipe\Entities\PreferenceMapIntolerance;
use Modules\Recipe\Entities\PreferenceMapDiet;
use Modules\Recipe\Entities\PreferenceMapIngredient;
use Modules\Recipe\Entities\PreferenceMapCookingSkill;
use Modules\Recipe\Entities\PreferenceMapUser;
use App\Notification;
use App\Attachment;
use DB;
use Illuminate\Support\Facades\Auth; 
use Validator;
use App\Http\Traits\UploadImageTrait;
//use App\Events\UserRegisterEvent;

class RecipeController extends CoreController
{
    use UploadImageTrait;
    public $successStatus = 200;
    public $validationStatus = 422;
    public $exceptionStatus = 409;
    public $unauthorisedStatus = 401;

    public $user = '';

    public function __construct(){

        $this->middleware(function ($request, $next) {

            $this->user = Auth::user();
            return $next($request);
        });
    }

    /*
     * Get recipie food intolerance
     * 
     */
    public function getFoodIntolerance()
    {
        try
        {
            $user = $this->user;

            $tolerances = RecipeFoodIntolerance::with('image_id')->get();
            if(count($tolerances) > 0)
            {
                foreach($tolerances as $key => $tolerance)
                {
                    $tolerances[$key]->name = $this->translate('messages.'.$tolerance->name,$tolerance->name);
                }

                return response()->json(['success' => $this->successStatus,
                                        'count' =>  count($tolerances),
                                        'data' => $tolerances,
                                    ], $this->successStatus);
            }
            else
            {
                $message = "No food tolerance found";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Get recipie categories
     * 
     */
    public function getRecipeCategories()
    {
        try
        {
            $user = $this->user;

            $categories = RecipeCategory::with('image_id')->where('status', '1')->get();
            if(count($categories) > 0)
            {
                foreach($categories as $key => $category)
                {
                    $categories[$key]->name = $this->translate('messages.'.$category->name,$category->name);
                }

                return response()->json(['success' => $this->successStatus,
                                        'count' =>  count($categories),
                                        'data' => $categories,
                                    ], $this->successStatus);
            }
            else
            {
                $message = "No categories found";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Get recipie diets
     * 
     */
    public function getRecipeDiets()
    {
        try
        {
            $user = $this->user;

            $diets = RecipeDiet::with('image_id')->get();
            if(count($diets) > 0)
            {
                foreach($diets as $key => $diet)
                {
                    $diets[$key]->name = $this->translate('messages.'.$diet->name,$diet->name);
                }

                return response()->json(['success' => $this->successStatus,
                                        'count' =>  count($diets),
                                        'data' => $diets,
                                    ], $this->successStatus);
            }
            else
            {
                $message = "No recipe diets found";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Get recipie categories
     * 
     */
    public function getRecipeCourses()
    {
        try
        {
            $user = $this->user;

            $courses = RecipeCourse::get();
            if(count($courses) > 0)
            {
                foreach($courses as $key => $course)
                {
                    $courses[$key]->name = $this->translate('messages.'.$course->name,$course->name);
                }

                return response()->json(['success' => $this->successStatus,
                                        'count' =>  count($courses),
                                        'data' => $courses,
                                    ], $this->successStatus);
            }
            else
            {
                $message = "No courses found";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Get Recipe ingredients
     * 
     */
    public function getRecipeIngredients()
    {
        try
        {
            $user = $this->user;

            $parentIngredients = RecipeIngredient::with('image_id')->where('parent', 0)->get();
            if(count($parentIngredients) > 0)
            {
                $categoriesWithCount = [];
                foreach($parentIngredients as $key => $parentIngredient)
                {
                    $parentIngredients[$key]->title = $this->translate('messages.'.$parentIngredient->title,$parentIngredient->title);

                    $childIngredients = RecipeIngredient::with('image_id')->where('parent', $parentIngredient->recipe_ingredient_id)->get();

                    $childIngredientCounts = RecipeIngredient::with('image_id')->where('parent', $parentIngredient->recipe_ingredient_id)->count();
                    foreach($childIngredients as $keys => $childIngredient)
                    {
                        $childIngredients[$keys]->title = $this->translate('messages.'.$childIngredient->title,$childIngredient->title);    
                    }
                    $parentIngredients[$key]->ingredients = $childIngredients;

                    $categoriesWithCount[] = ['ingredient_types' => $parentIngredient->title, 'name' => $parentIngredient->name, 'count' => $childIngredientCounts];
                    
                }

                return response()->json(['success' => $this->successStatus,
                                        'count' =>  count($parentIngredients),
                                        'types' => $categoriesWithCount,
                                        'data' => $parentIngredients,
                                    ], $this->successStatus);
            }
            else
            {
                $message = "No ingredients found";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Search ingredients
     * 
     */
    public function searchIngredients(Request $request)
    {
        try
        {
            $user = $this->user;

            $validator = Validator::make($request->all(), [ 
                'keyword' => 'required', 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $ingredients = RecipeIngredient::with('image_id')->where('parent','!=', 0)->where('title','LIKE','%'.$request->keyword.'%')->get();
            if(count($ingredients) > 0)
            {
                $categoriesWithCount = [];
                foreach($ingredients as $key => $ingredient)
                {
                    $ingredients[$key]->title = $this->translate('messages.'.$ingredient->title, $ingredient->title);
                }

                return response()->json(['success' => $this->successStatus,
                                        'count' =>  count($ingredients),
                                        'data' => $ingredients,
                                    ], $this->successStatus);
            }
            else
            {
                $message = "No ingredients found";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Get recipie meals
     * 
     */
    public function getRecipeMeals()
    {
        try
        {
            $user = $this->user;

            $meals = RecipeMeal::get();
            if(count($meals) > 0)
            {
                foreach($meals as $key => $meal)
                {
                    $meals[$key]->name = $this->translate('messages.'.$meal->name,$meal->name);
                }

                return response()->json(['success' => $this->successStatus,
                                        'count' =>  count($meals),
                                        'data' => $meals,
                                    ], $this->successStatus);
            }
            else
            {
                $message = "No meals found";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Get Cooking skills
     * 
     */
    public function getCookingSkills()
    {
        try
        {
            $user = $this->user;

            $skills = RecipeCookingSkill::with('image_id')->get();
            if(count($skills) > 0)
            {
                foreach($skills as $key => $skill)
                {
                    $skills[$key]->name = $this->translate('messages.'.$skill->name,$skill->name);
                }

                return response()->json(['success' => $this->successStatus,
                                        'count' =>  count($skills),
                                        'data' => $skills,
                                    ], $this->successStatus);
            }
            else
            {
                $message = "No cooking skills found";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Get recipie categories
     * 
     */
    public function getRecipeRegions(Request $request)
    {
        try
        {
            $user = $this->user;

            $validator = Validator::make($request->all(), [ 
                'cousin_id' => 'required', 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $regions = RecipeRegion::with('image_id')->where('cousin_id', $request->cousin_id)->get();
            if(count($regions) > 0)
            {
                foreach($regions as $key => $region)
                {
                    $regions[$key]->name = $this->translate('messages.'.$region->name,$region->name);
                }

                return response()->json(['success' => $this->successStatus,
                                        'count' =>  count($regions),
                                        'data' => $regions,
                                    ], $this->successStatus);
            }
            else
            {
                $message = "No regions found";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Get Permission For Sending Requests
     * 
     */
    public function getRecipeTools()
    {
        try
        {
            $user = $this->user;

            $parentTools = RecipeTool::with('image_id')->where('parent', 0)->get();
            if(count($parentTools) > 0)
            {
                $categoriesWithCount = [];
                foreach($parentTools as $key => $parentTool)
                {
                    $parentTools[$key]->title = $this->translate('messages.'.$parentTool->title,$parentTool->title);

                    $childTools = RecipeTool::with('image_id')->where('parent', $parentTool->recipe_tool_id)->get();

                    $childIngredientCounts = RecipeTool::with('image_id')->where('parent', $parentTool->recipe_tool_id)->count();
                    foreach($childTools as $keys => $childTool)
                    {
                        $childTools[$keys]->title = $this->translate('messages.'.$childTool->title,$childTool->title);    
                    }
                    $parentTools[$key]->tools = $childTools;

                    $categoriesWithCount[] = ['tool_types' => $parentTool->title, 'name' => $parentTool->name, 'count' => $childIngredientCounts];
                    
                }

                return response()->json(['success' => $this->successStatus,
                                        'count' =>  count($parentTools),
                                        'types' => $categoriesWithCount,
                                        'data' => $parentTools,
                                    ], $this->successStatus);
            }
            else
            {
                $message = "No tools found";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Create recipe
     * 
     */
    public function createRecipe(Request $request)
    {
        try
        {
            $user = $this->user;

            $requestedFields = $request->params;
            $rules = $this->validateData($requestedFields);
            
            $validator = Validator::make($requestedFields, $rules);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            //recipe detail
            $recipe = new Recipe;
            $recipe->user_id = $user->user_id;
            $recipe->name = $requestedFields['name'];
            $recipe->meal_id = $requestedFields['meal_id'];
            $recipe->course_id = $requestedFields['course_id'];
            $recipe->hours = $requestedFields['hours'];
            $recipe->minutes = $requestedFields['minutes'];
            $recipe->serving = $requestedFields['serving'];
            $recipe->cousin_id = $requestedFields['cousin_id'];
            $recipe->diet_id = $requestedFields['diet_id'];
            $recipe->intolerance_id = $requestedFields['intolerance_id'];
            $recipe->cooking_skill_id = $requestedFields['cooking_skill_id'];
            $recipe->region_id = $requestedFields['region_id'];
            $recipe->image_id = $this->createImage($requestedFields['image_id']);
            //return $recipe->image_id;
            //$this->uploadImage($request->file($requestedFields['image_id']));
            $recipe->status = '1';
            $recipe->save();

            //save category
            /*foreach($requestedFields['save_categories'] as $savedCategory)
            {
                $categories = new RecipeSavedCategory;
                $categories->recipe_id = $recipe->id;
                $categories->category_id = $savedCategory;
                $categories->save();
            }*/

            //save ingredients
            foreach($requestedFields['saved_ingredients'] as $savedIngredients)
            {
                $savedIng = new RecipeSavedIngredient;
                $savedIng->recipe_id = $recipe->recipe_id;
                $savedIng->ingredient_id = $savedIngredients['ingredient_id'];
                $savedIng->quantity = $savedIngredients['quantity'];
                $savedIng->unit = $savedIngredients['unit'];
                $savedIng->save();
            }
            

            //save tools
            foreach($requestedFields['saved_tools'] as $savedTools)
            {
                $savedTool = new RecipeSavedTool;
                $savedTool->recipe_id = $recipe->recipe_id;
                $savedTool->tool_id = $savedTools['tool_id'];
                //$savedTool->quantity = $savedTools['quantity'];
                //$savedTool->unit = $savedTools['unit'];
                $savedTool->save();
            }
            

            //steps
            foreach($requestedFields['recipe_steps'] as $key => $recipeStep)
            {
                $step = new RecipeStep;
                $step->recipe_id = $recipe->recipe_id;
                $step->title = $recipeStep['title'];
                $step->description = $recipeStep['description'];
                $step->save();

                foreach($recipeStep['ingredients'] as $recipeStepsIngredient)
                {
                    $mapIngredientSteps = new RecipeMapStepIngredient;
                    $mapIngredientSteps->recipe_id = $recipe->recipe_id;
                    $mapIngredientSteps->recipe_step_id = $step->id;
                    $mapIngredientSteps->recipe_saved_ingredient_id = $recipeStepsIngredient;
                    $mapIngredientSteps->save();
                }

                foreach($recipeStep['tools'] as $recipeStepstool)
                {
                    $mapIngredientSteps = new RecipeMapStepTool;
                    $mapIngredientSteps->recipe_id = $recipe->recipe_id;
                    $mapIngredientSteps->recipe_step_id = $step->id;
                    $mapIngredientSteps->recipe_saved_tool_id = $recipeStepstool;
                    $mapIngredientSteps->save();
                }
                

            }
            $message = "Your recipe has been saved successfully";
            return response()->json(['success' => $this->successStatus,
                                        'message' => $this->translate('messages.'.$message,$message),
                                    ], $this->successStatus);       
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Create recipe
     * 
     */
    public function updateRecipe(Request $request, $recipeId)
    {
        try
        {
            $user = $this->user;

            $requestedFields = $request->params;
            $rules = $this->validateData($requestedFields);
            
            $validator = Validator::make($requestedFields, $rules);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            RecipeSavedIngredient::where('recipe_id', $recipeId)->delete();
            RecipeSavedTool::where('recipe_id', $recipeId)->delete();
            RecipeStep::where('recipe_id', $recipeId)->delete();
            RecipeMapStepIngredient::where('recipe_id', $recipeId)->delete();
            RecipeMapStepTool::where('recipe_id', $recipeId)->delete();

            //recipe detail
            $recipe = Recipe::where('recipe_id', $recipeId)->first();
            $recipe->name = $requestedFields['name'];
            $recipe->meal_id = $requestedFields['meal_id'];
            $recipe->course_id = $requestedFields['course_id'];
            $recipe->hours = $requestedFields['hours'];
            $recipe->minutes = $requestedFields['minutes'];
            $recipe->serving = $requestedFields['serving'];
            $recipe->cousin_id = $requestedFields['cousin_id'];
            $recipe->diet_id = $requestedFields['diet_id'];
            $recipe->intolerance_id = $requestedFields['intolerance_id'];
            $recipe->cooking_skill_id = $requestedFields['cooking_skill_id'];
            $recipe->region_id = $requestedFields['region_id'];
            if(!empty($requestedFields['image_id']))
            $recipe->image_id = $this->createImage($requestedFields['image_id']);
            //$this->uploadImage($request->file($requestedFields['image_id']));
            $recipe->save();

            //save category
            /*foreach($requestedFields['save_categories'] as $savedCategory)
            {
                $categories = new RecipeSavedCategory;
                $categories->recipe_id = $recipe->id;
                $categories->category_id = $savedCategory;
                $categories->save();
            }*/
            
            //save ingredients
            foreach($requestedFields['saved_ingredients'] as $savedIngredients)
            {
                $savedIng = new RecipeSavedIngredient;
                $savedIng->recipe_id = $recipe->recipe_id;
                $savedIng->ingredient_id = $savedIngredients['ingredient_id'];
                $savedIng->quantity = $savedIngredients['quantity'];
                $savedIng->unit = $savedIngredients['unit'];
                $savedIng->save();
            }
            

            //save tools
            foreach($requestedFields['saved_tools'] as $savedTools)
            {
                $savedTool = new RecipeSavedTool;
                $savedTool->recipe_id = $recipe->recipe_id;
                $savedTool->tool_id = $savedTools['tool_id'];
                //$savedTool->quantity = $savedTools['quantity'];
                //$savedTool->unit = $savedTools['unit'];
                $savedTool->save();
            }
            

            //steps
            foreach($requestedFields['recipe_steps'] as $key => $recipeStep)
            {
                $step = new RecipeStep;
                $step->recipe_id = $recipe->recipe_id;
                $step->title = $recipeStep['title'];
                $step->description = $recipeStep['description'];
                $step->save();

                foreach($recipeStep['ingredients'] as $recipeStepsIngredient)
                {
                    $mapIngredientSteps = new RecipeMapStepIngredient;
                    $mapIngredientSteps->recipe_id = $recipe->recipe_id;
                    $mapIngredientSteps->recipe_step_id = $step->id;
                    $mapIngredientSteps->recipe_saved_ingredient_id = $recipeStepsIngredient;
                    $mapIngredientSteps->save();
                }

                foreach($recipeStep['tools'] as $recipeStepstool)
                {
                    $mapIngredientSteps = new RecipeMapStepTool;
                    $mapIngredientSteps->recipe_id = $recipe->recipe_id;
                    $mapIngredientSteps->recipe_step_id = $step->id;
                    $mapIngredientSteps->recipe_saved_tool_id = $recipeStepstool;
                    $mapIngredientSteps->save();
                }
                

            }
            $message = "Your recipe has been updated successfully";
            return response()->json(['success' => $this->successStatus,
                                        'message' => $this->translate('messages.'.$message,$message),
                                    ], $this->successStatus);       
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Create recipe
     * 
     */
    public function saveOrUpdateRecipeInDraft(Request $request, $recipeId = '')
    {
        try
        {
            $user = $this->user;

            $requestedFields = $request->params;
            $rules = $this->validateData($requestedFields);
            
            if($recipeId > 0)
            {
                RecipeSavedIngredient::where('recipe_id', $recipeId)->delete();
                RecipeSavedTool::where('recipe_id', $recipeId)->delete();
                RecipeStep::where('recipe_id', $recipeId)->delete();
                RecipeMapStepIngredient::where('recipe_id', $recipeId)->delete();
                RecipeMapStepTool::where('recipe_id', $recipeId)->delete();
                $recipe = Recipe::where('recipe_id', $recipeId)->first();
            }
            else
            {
                $recipe = new Recipe;
            }

            //recipe detail
            
            $recipe->user_id = $user->user_id;
            $recipe->name = $requestedFields['name'];
            $recipe->meal_id = $requestedFields['meal_id'];
            $recipe->course_id = $requestedFields['course_id'];
            $recipe->hours = $requestedFields['hours'];
            $recipe->minutes = $requestedFields['minutes'];
            $recipe->serving = $requestedFields['serving'];
            $recipe->cousin_id = $requestedFields['cousin_id'];
            $recipe->diet_id = $requestedFields['diet_id'];
            $recipe->intolerance_id = $requestedFields['intolerance_id'];
            $recipe->cooking_skill_id = $requestedFields['cooking_skill_id'];
            $recipe->region_id = $requestedFields['region_id'];
            if(!empty($requestedFields['image_id']))
            $recipe->image_id = $this->createImage($requestedFields['image_id']);
            //return $recipe->image_id;
            //$this->uploadImage($request->file($requestedFields['image_id']));
            $recipe->status = '0';
            $recipe->save();

            

            //save category
            /*if(count($requestedFields['save_categories']) > 0)
            {
                foreach($requestedFields['save_categories'] as $savedCategory)
                {
                    $categories = new RecipeSavedCategory;
                    $categories->recipe_id = $recipe->id;
                    $categories->category_id = $savedCategory;
                    $categories->save();
                }
            }*/

            if(count($requestedFields['saved_ingredients']) > 0)
            {
                //save ingredients
                foreach($requestedFields['saved_ingredients'] as $savedIngredients)
                {
                    $savedIng = new RecipeSavedIngredient;
                    $savedIng->recipe_id = $recipe->recipe_id;
                    $savedIng->ingredient_id = $savedIngredients['ingredient_id'];
                    $savedIng->quantity = $savedIngredients['quantity'];
                    $savedIng->unit = $savedIngredients['unit'];
                    $savedIng->save();
                }       
            }
            
            

            if(count($requestedFields['saved_tools']) > 0)
            {
                //save tools
                foreach($requestedFields['saved_tools'] as $savedTools)
                {
                    $savedTool = new RecipeSavedTool;
                    $savedTool->recipe_id = $recipe->recipe_id;
                    $savedTool->tool_id = $savedTools['tool_id'];
                    //$savedTool->quantity = $savedTools['quantity'];
                    //$savedTool->unit = $savedTools['unit'];
                    $savedTool->save();
                }    
            }
            
            

            if(count($requestedFields['recipe_steps']) > 0)
            {
                //steps
                foreach($requestedFields['recipe_steps'] as $key => $recipeStep)
                {
                    $step = new RecipeStep;
                    $step->recipe_id = $recipe->recipe_id;
                    $step->title = $recipeStep['title'];
                    $step->description = $recipeStep['description'];
                    $step->save();

                    if(count($recipeStep['ingredients']) > 0)
                    {
                        foreach($recipeStep['ingredients'] as $recipeStepsIngredient)
                        {
                            $mapIngredientSteps = new RecipeMapStepIngredient;
                            $mapIngredientSteps->recipe_id = $recipe->recipe_id;
                            $mapIngredientSteps->recipe_step_id = $step->id;
                            $mapIngredientSteps->recipe_saved_ingredient_id = $recipeStepsIngredient;
                            $mapIngredientSteps->save();
                        }    
                    }   
                    
                    if(count($recipeStep['tools']) > 0)
                    {
                        foreach($recipeStep['tools'] as $recipeStepstool)
                        {
                            $mapIngredientSteps = new RecipeMapStepTool;
                            $mapIngredientSteps->recipe_id = $recipe->recipe_id;
                            $mapIngredientSteps->recipe_step_id = $step->id;
                            $mapIngredientSteps->recipe_saved_tool_id = $recipeStepstool;
                            $mapIngredientSteps->save();
                        }
                    }
                }
            }
            
            $message = "Your recipe has been saved successfully";
            return response()->json(['success' => $this->successStatus,
                                        'message' => $this->translate('messages.'.$message,$message),
                                    ], $this->successStatus);       
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /** 
     * Create Image From Base64 string
     * 
     * Pamameters $img
     */ 
    public function createImage($img)
    {
        $folderPath = "/home/ibyteworkshop/alyseiapi_ibyteworkshop_com/public/uploads/";
        $image_parts = explode(";base64,", $img);
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_type = $image_type_aux[1];
        $image_base64 = base64_decode($image_parts[1]);
        
        $uniqueName = uniqid() . '.'.$image_type;
        $file = $folderPath .''. $uniqueName;

        file_put_contents($file, $image_base64);

        $attachment = new Attachment;
        $attachment->attachment_url = 'public/uploads/'.$uniqueName;
        $attachment->attachment_type = $image_type;
        $attachment->save();
        return $attachment->id;
    }

    /*
     * Get recipie detail
     * 
     */
    public function getRecipeDetail($recipeId)
    {
        try
        {
            $user = $this->user;

            $myRecipes = Recipe::with('image','meal','region')->where('recipe_id', $recipeId)->first();
            if(!empty($myRecipes))
            {
                $recipeUsedIngredients = RecipeSavedIngredient::with('ingredient','ingredient.image_id')->where('recipe_id', $recipeId)->get();
                $recipeUsedTools = RecipeSavedTool::with('tool','tool.image_id')->where('recipe_id', $recipeId)->get();

                $recipeUsedSteps = RecipeStep::where('recipe_id', $recipeId)->get();
                if(count($recipeUsedSteps) > 0)
                {
                    $arrayValues = [];
                    $arrayValuesTools = [];
                    foreach($recipeUsedSteps as $key => $recipeUsedStep)
                    {
                        //for saved ingredients for the steps
                        $mapIngredients = RecipeMapStepIngredient::where('recipe_id', $recipeId)->where('recipe_step_id', $recipeUsedStep->recipe_step_id)->get(); 
                        //return $mapIngredients;
                        if(count($mapIngredients) > 0)
                        {
                            foreach($mapIngredients as $mapIngredient)
                            {
                                array_push($arrayValues, $mapIngredient->recipe_saved_ingredient_id);
                            }
                            //$arrayValues = $mapIngredients->pluck('recipe_saved_ingredient_id');
                            foreach($recipeUsedIngredients as $keyIng => $recipeUsedIngredient)
                            {
                                if(in_array($recipeUsedIngredient->ingredient_id, $arrayValues))
                                    $recipeUsedIngredients[$keyIng]->is_selected = true;
                                else
                                    $recipeUsedIngredients[$keyIng]->is_selected = false;

                            }
                        }
                        else
                        {
                            foreach($recipeUsedIngredients as $keyIng => $recipeUsedIngredient)
                            {
                                $recipeUsedIngredients[$keyIng]->is_selected = false;
                            }
                        }                   
                        
                        $recipeUsedSteps[$key]->step_ingredients = $recipeUsedIngredients;

                        //for saved tools for the steps
                        $mapTools = RecipeMapStepTool::where('recipe_id', $recipeId)->where('recipe_step_id', $recipeUsedStep->recipe_step_id)->get(); 
                        //return $mapIngredients;
                        if(count($mapTools) > 0)
                        {
                            foreach($mapTools as $mapTool)
                            {
                                array_push($arrayValuesTools, $mapTool->recipe_saved_tool_id);
                            }
                            //$arrayValues = $mapIngredients->pluck('recipe_saved_ingredient_id');
                            foreach($recipeUsedTools as $keyTool => $recipeUsedTool)
                            {
                                if(in_array($recipeUsedTool->tool_id, $arrayValuesTools))
                                    $recipeUsedTools[$keyTool]->is_selected = true;
                                else
                                    $recipeUsedTools[$keyTool]->is_selected = false;

                            }
                        }
                        else
                        {
                            foreach($recipeUsedTools as $keyTool => $recipeUsedTool)
                            {
                                $recipeUsedTools[$keyTool]->is_selected = false;
                            }
                        }

                        $recipeUsedSteps[$key]->step_tools = $recipeUsedTools;
                    }
                }
                
                return response()->json(['success' => $this->successStatus,
                                         'recipe' => $myRecipes,
                                         'used_ingredients' => $recipeUsedIngredients,
                                         'used_tools' => $recipeUsedTools,
                                         'steps' => $recipeUsedSteps
                                    ], $this->successStatus);
            }
            else
            {
                $message = "No recipe found";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Get all my recipie
     * 
     */
    public function getMyRecipes()
    {
        try
        {
            $user = $this->user;

            $myRecipes = Recipe::with('image','meal','course','cousin','region','diet','intolerance','cookingskill')->where('user_id', $user->user_id)->orderBy('recipe_id', 'DESC')->get();
            if(count($myRecipes) > 0)
            {
                foreach($myRecipes as $key => $myRecipe)
                {
                    $myRecipes[$key]->total_likes = 3;
                    $myRecipes[$key]->avg_rating = 3;
                }

                return response()->json(['success' => $this->successStatus,
                                        'count' =>  count($myRecipes),
                                        'data' => $myRecipes,
                                    ], $this->successStatus);
            }
            else
            {
                $message = "No regions found";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }


    /*
     * Favourite/unfavourite recipe
     * @Params $request
     */
    public function makeFavouriteOrUnfavourite(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'recipe_id' => 'required',
                'favourite_or_unfavourite' => 'required', // 1 for favourite 0 for unfavourite
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $getRecipe = Recipe::where('recipe_id', $request->recipe_id)->first();
            if(!empty($getRecipe))
            {
                if($request->favourite_or_unfavourite == 1)
                {
                    $isLikedActivityPost = RecipeFavourite::where('user_id', $user->user_id)->where('recipe_id', $request->recipe_id)->first();


                    if(!empty($isLikedActivityPost))
                    {
                        $message = "You have already added this recipe in your favourite list";
                        return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus); 
                    }
                    else
                    {
                        $activityLike = new RecipeFavourite;
                        $activityLike->user_id = $user->user_id;
                        $activityLike->recipe_id = $request->recipe_id;
                        $activityLike->save();

                        /*$getRecipe->favourite_count = $getRecipe->favourite_count + 1;
                        $getRecipe->save();*/

                        $message = "You added this recipe in your favourite list";
                        return response()->json(['success' => $this->successStatus,
                                                 'total_favourite_count' => $getRecipe->like_count,
                                                 'message' => $this->translate('messages.'.$message,$message),
                                                ], $this->successStatus);
                    }
                }
                elseif($request->favourite_or_unfavourite == 0)
                {
                    $isLikedActivityPost = RecipeFavourite::where('user_id', $user->user_id)->where('recipe_id', $request->recipe_id)->first();
                    if(!empty($isLikedActivityPost))
                    {
                        $isUnlikedActivityPost = RecipeFavourite::where('user_id', $user->user_id)->where('recipe_id', $request->recipe_id)->delete();
                        if($isUnlikedActivityPost == 1)
                        {
                            /*$isLikedActivityPost->favourite_count = $isLikedActivityPost->favourite_count - 1;
                            $isLikedActivityPost->save();*/

                            $message = "You removed this recipe from your favourite list";
                            return response()->json(['success' => $this->successStatus,
                                                 'total_likes' => $isLikedActivityPost->favourite_count,
                                                 'message' => $this->translate('messages.'.$message,$message),
                                                ], $this->successStatus);
                        }
                        else
                        {
                            $message = "You have to first add this recipe in your favourite list";
                            return response()->json(['success' => $this->exceptionStatus,
                                                 'message' => $this->translate('messages.'.$message,$message),
                                                ], $this->exceptionStatus);
                        }
                    }
                    else
                    {
                        $message = "You have not added this recipe in your favourite list";
                        return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                    }
                }
                else
                {
                    $message = "Invalid favourite/unfavourite type";
                    return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                }
                
            }
            else
            {
                $message = "Invalid recipe id";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }


    /*
     * Get my favourite recipie
     * 
     */
    public function getMyFavouriteRecipes()
    {
        try
        {
            $user = $this->user;

            $myFavRecipes = RecipeFavourite::where('user_id', $user->user_id)->orderBy('recipe_favourite_id', 'DESC')->get();
            if(count($myFavRecipes) > 0)
            {
                $myFavRecipesId = $myFavRecipes->pluck('recipe_id');
                $myRecipes = Recipe::with('image','meal','course','cousin','region','diet','intolerance','cookingskill')->whereIn('recipe_id', $myFavRecipesId)->orderBy('recipe_id', 'DESC')->get();

                if(count($myRecipes) > 0)
                {
                    foreach($myRecipes as $key => $myRecipe)
                    {
                        $myRecipes[$key]->total_likes = 3;
                        $myRecipes[$key]->avg_rating = 3;
                    }
                    return response()->json(['success' => $this->successStatus,
                                        'count' =>  count($myRecipes),
                                        'data' => $myRecipes,
                                    ], $this->successStatus);
                }
                else
                {
                    $message = "No recipes found";
                    return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                }   
            }
            else
            {
                $message = "You have not liked any recipe";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Add ingredients
     * 
     */
    public function addIngredient(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'title' => 'required',
                'image_id' => 'required',
                'category' => 'required',
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $newIngredient = new RecipeIngredient;
            $newIngredient->image_id = $this->uploadImage($request->file('image_id'));
            $newIngredient->title = $request->title;
            $newIngredient->name = strtolower(str_replace(' ', '_', $request->title));
            $newIngredient->parent = $request->category;
            $newIngredient->save();

           
            $message = "ingredient added successfuly";
            return response()->json(['success' => $this->successStatus,
                                    'message' =>  $this->translate('messages.'.$message,$message),
                                    'data' => $newIngredient,
                                    ], $this->successStatus);
                       
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Add ingredients
     * 
     */
    public function addTool(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'title' => 'required',
                'image_id' => 'required',
                'category' => 'required',
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $newIngredient = new RecipeTool;
            $newIngredient->image_id = $this->uploadImage($request->file('image_id'));
            $newIngredient->title = $request->title;
            $newIngredient->name = strtolower(str_replace(' ', '_', $request->title));
            $newIngredient->parent = $request->category;
            $newIngredient->save();

           
            $message = "Tool added successfuly";
            return response()->json(['success' => $this->successStatus,
                                    'message' =>  $this->translate('messages.'.$message,$message),
                                    'data' => $newIngredient,
                                    ], $this->successStatus);
                       
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Make a Review on recipe
     * @Params $request
     */
    public function doReview(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'recipe_id' => 'required',
                'rating' => 'required',
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $isRated = RecipeReviewRating::where('user_id', $user->user_id)->where('recipe_id', $request->recipe_id)->first();
            if(!empty($isRated))
            {
                $message = "You have already done a review on this recipe";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
            else
            {
                $review = new RecipeReviewRating;
                $review->user_id = $user->user_id;
                $review->recipe_id = $request->recipe_id;
                $review->rating = $request->rating;
                $review->review = $request->review;
                $review->save();

                $message = "Your rating has been done";
                return response()->json(['success' => $this->successStatus,
                                            'message' => $this->translate('messages.'.$message,$message),
                                            'data' => $review,
                                         ], $this->successStatus);
            }

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * get all ratings
     * @Params $request
     */
    public function getReviews(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'recipe_id'   => 'required',
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $getAllRatings = RecipeReviewRating::with('user:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','user.avatar_id')->where('recipe_id', $request->recipe_id)->orderBy('recipe_review_rating_id', 'DESC')->get();
            if(count($getAllRatings) > 0)
            {
                    return response()->json(['success' => $this->successStatus,
                                            'data' => $getAllRatings,
                                         ], $this->successStatus);
            }
            else
            {
                $message = "No review found on this recipe";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Save preferences
     * @Params $request
     */
    public function savePreferences(Request $request)
    {
        try
        {
            $user = $this->user;
            if(!empty($request->params))
            {
                $getPreferences = PreferenceMapUser::where('user_id', $user->user_id)->get();
                if(count($getPreferences) > 0)
                {
                    PreferenceMapUser::where('user_id', $user->user_id)->delete();
                    PreferenceMapCousin::where('user_id', $user->user_id)->delete();
                    PreferenceMapIntolerance::where('user_id', $user->user_id)->delete();
                    PreferenceMapDiet::where('user_id', $user->user_id)->delete();
                    PreferenceMapIngredient::where('user_id', $user->user_id)->delete();
                    PreferenceMapCookingSkill::where('user_id', $user->user_id)->delete();
                }
                foreach($request->params as $key => $preferences)
                {
                    $newPreference = new PreferenceMapUser;
                    $newPreference->user_id = $user->user_id;
                    $newPreference->preference = $preferences['preference'];
                    $newPreference->save();
                        
                    if($preferences['preference'] == 1)
                    {
                        foreach($preferences['id'] as $preferenceId)
                        {
                            $mapPreference = new PreferenceMapCousin;
                            $mapPreference->user_id = $user->user_id;
                            $mapPreference->preference_id = $newPreference->id;
                            $mapPreference->cousin_id = $preferenceId;
                            $mapPreference->save();
                        }
                        
                    }
                    elseif($preferences['preference'] == 2)
                    {
                        foreach($preferences['id'] as $preferenceId)
                        {
                            $mapPreference = new PreferenceMapIntolerance;
                            $mapPreference->user_id = $user->user_id;
                            $mapPreference->preference_id = $newPreference->id;
                            $mapPreference->intolerance_id = $preferenceId;
                            $mapPreference->save();
                        }
                        
                    } 
                    elseif($preferences['preference'] == 3)
                    {
                        foreach($preferences['id'] as $preferenceId)
                        {
                            $mapPreference = new PreferenceMapDiet;
                            $mapPreference->user_id = $user->user_id;
                            $mapPreference->preference_id = $newPreference->id;
                            $mapPreference->diet_id = $preferenceId;
                            $mapPreference->save();
                        }
                        
                    }  
                    elseif($preferences['preference'] == 4)
                    {
                        foreach($preferences['id'] as $preferenceId)
                        {
                            $mapPreference = new PreferenceMapIngredient;
                            $mapPreference->user_id = $user->user_id;
                            $mapPreference->preference_id = $newPreference->id;
                            $mapPreference->ingredient_id = $preferenceId;
                            $mapPreference->save();
                        }
                        
                    }    
                    elseif($preferences['preference'] == 5)
                    {
                        foreach($preferences['id'] as $preferenceId)
                        {
                            $mapPreference = new PreferenceMapCookingSkill;
                            $mapPreference->user_id = $user->user_id;
                            $mapPreference->preference_id = $newPreference->id;
                            $mapPreference->cooking_skill_id = $preferenceId;
                            $mapPreference->save();
                        }
                        
                    }  
                    
                } 
            }
            $message = "Preferences has been saved successfully";
            return response()->json(['success' => $this->successStatus,
                                    'message' => $this->translate('messages.'.$message,$message),
                                    ], $this->successStatus);
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * get Saved preferences
     * @Params $request
     */
    public function getPreferences(Request $request)
    {
        try
        {
            $user = $this->user;
            
            $allPreferences = Preference::get();
            
            if(count($allPreferences) > 0)
            {
                //return $allPreferences;
                foreach($allPreferences as $key => $allPreference)
                {
                    if($allPreference->preference_id == 1)
                    {
                        $cousins = Cousin::with('image_id')->where('status', 1)->get();

                        $myPreferences = PreferenceMapUser::where('user_id', $user->user_id)->where('preference', $allPreference->preference_id)->first();
                        $arrayValues1 = [];
                        foreach($cousins as $keys => $cousin)
                        {
                            if(!empty($myPreferences) && $myPreferences->user_id == $user->user_id)
                            {
                                $cousinsPreference = PreferenceMapCousin::where('preference_id', $myPreferences->id)->get();
                                foreach($cousinsPreference as $cousinsPref)
                                {
                                    array_push($arrayValues1, $cousinsPref->cousin_id);
                                }
                                //$cousinsPreference = $cousinsPreference->pluck('cousin_id');
                                if(in_array($cousin->cousin_id, $arrayValues1))
                                    $cousins[$keys]->is_selected = true;
                                else
                                    $cousins[$keys]->is_selected = false;
                            }
                            else
                            {
                                $cousins[$keys]->is_selected = false;
                            }
                        }

                        $allPreferences[$key]->maps = $cousins;
                    }
                    elseif($allPreference->preference_id == 2)
                    {
                        $intolerances = RecipeFoodIntolerance::with('image_id')->get();

                        $myPreferences = PreferenceMapUser::where('user_id', $user->user_id)->where('preference', $allPreference->preference_id)->first();
                        $arrayValues2 = [];
                        foreach($intolerances as $keys => $intolerance)
                        {
                            if(!empty($myPreferences) && $myPreferences->user_id == $user->user_id)
                            {
                                $intolerancePreference = PreferenceMapIntolerance::where('preference_id', $myPreferences->id)->get();
                                foreach($intolerancePreference as $intolerancePref)
                                {
                                    array_push($arrayValues2, $intolerancePref->intolerance_id);
                                }
                                if(in_array($intolerance->recipe_food_intolerance_id, $arrayValues2))
                                    $intolerances[$keys]->is_selected = true;
                                else
                                    $intolerances[$keys]->is_selected = false;
                            }
                            else
                            {
                                $intolerances[$keys]->is_selected = false;
                            }
                        }

                        $allPreferences[$key]->maps = $intolerances;
                    }
                    elseif($allPreference->preference_id == 3)
                    {
                        $diets = RecipeDiet::with('image_id')->get();

                        $myPreferences = PreferenceMapUser::where('user_id', $user->user_id)->where('preference', $allPreference->preference_id)->first();

                        $arrayValues3 = [];
                        foreach($diets as $keys => $diet)
                        {
                            if(!empty($myPreferences) && $myPreferences->user_id == $user->user_id)
                            {
                                $dietPreference = PreferenceMapDiet::where('preference_id', $myPreferences->id)->get();
                                foreach($dietPreference as $dietPref)
                                {
                                    array_push($arrayValues3, $dietPref->diet_id);
                                }
                                if(in_array($diet->recipe_diet_id, $arrayValues3))
                                    $diets[$keys]->is_selected = true;
                                else
                                    $diets[$keys]->is_selected = false;
                            }
                            else
                            {
                                $diets[$keys]->is_selected = false;
                            }
                        }

                        $allPreferences[$key]->maps = $diets;
                    }
                    elseif($allPreference->preference_id == 4)
                    {
                        $ingredients = RecipeIngredient::with('image_id')->get();

                        $myPreferences = PreferenceMapUser::where('user_id', $user->user_id)->where('preference', $allPreference->preference_id)->first();

                        $arrayValues4 = [];
                        foreach($ingredients as $keys => $ingredient)
                        {
                            if(!empty($myPreferences) && $myPreferences->user_id == $user->user_id)
                            {
                                $ingredientPreference = PreferenceMapIngredient::where('preference_id', $myPreferences->id)->get();
                                foreach($ingredientPreference as $ingredientPref)
                                {
                                    array_push($arrayValues4, $ingredientPref->ingredient_id);
                                }
                                if(in_array($ingredient->recipe_ingredient_id, $arrayValues4))
                                    $ingredients[$keys]->is_selected = true;
                                else
                                    $ingredients[$keys]->is_selected = false;
                            }
                            else
                            {
                                $ingredients[$keys]->is_selected = false;
                            }
                        }

                        $allPreferences[$key]->maps = $ingredients;
                    }
                    elseif($allPreference->preference_id == 5)
                    {
                        $cookingSkills = RecipeCookingSkill::with('image_id')->get();

                        $myPreferences = PreferenceMapUser::where('user_id', $user->user_id)->where('preference', $allPreference->preference_id)->first();

                        $arrayValues5 = [];
                        foreach($cookingSkills as $keys => $cookingSkill)
                        {
                            if(!empty($myPreferences) && $myPreferences->user_id == $user->user_id)
                            {
                                $cookingPreference = PreferenceMapCookingSkill::where('preference_id', $myPreferences->id)->get();
                                foreach($cookingPreference as $cookingPref)
                                {
                                    array_push($arrayValues5, $cookingPref->cooking_skill_id);
                                }
                                if(in_array($cookingSkill->recipe_cooking_skill_id, $arrayValues5))
                                    $cookingSkills[$keys]->is_selected = true;
                                else
                                    $cookingSkills[$keys]->is_selected = false;
                            }
                            else
                            {
                                $cookingSkills[$keys]->is_selected = false;
                            }
                        }

                        $allPreferences[$key]->maps = $cookingSkills;
                    } 
                                      
                }
            }
            
            return response()->json(['success' => $this->successStatus,
                                    'data' => $allPreferences,
                                    ], $this->successStatus);
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    
    /*
     * Get home for recipie
     * 
     */
    public function getHomeScreen()
    {
        try
        {
            $user = $this->user;
            $recipeArray = array();
            $quickRecipeArray = array();
            $categories = RecipeCategory::with('image_id')->where('status', '1')->get();
            
            foreach($categories as $key => $category)
            {
                $categories[$key]->name = $this->translate('messages.'.$category->name,$category->name);
            }

            $parentIngredients = RecipeIngredient::with('image_id')->where('parent','!=', 0)->get();
            if(count($parentIngredients) > 0)
            {
                foreach($parentIngredients as $key => $parentIngredient)
                {
                    $parentIngredients[$key]->title = $this->translate('messages.'.$parentIngredient->title,$parentIngredient->title);                    
                }
            }

            $regions = RecipeRegion::with('image_id')->get();
            
            foreach($regions as $key => $region)
            {
                $regions[$key]->name = $this->translate('messages.'.$region->name,$region->name);
            }

            //trending

            $favouriteRecipes = DB::select(DB::raw("select recipe_id from recipe_favourites GROUP BY recipe_id ORDER BY count(*) DESC LIMIT 8"));
            foreach($favouriteRecipes as $favouriteRecipe)
            {
                array_push($recipeArray, $favouriteRecipe->recipe_id);
            }

            $myRecipes = Recipe::with('image','meal','course','cousin','region','diet','intolerance','cookingskill')->whereIn('recipe_id', $recipeArray)->orderBy('recipe_id', 'DESC')->get();

            //quick easy
            $easyRecipes = DB::select(DB::raw("select recipe_id from `recipe_steps` GROUP BY recipe_id ORDER BY count(recipe_step_id) ASC LIMIT 8"));
            foreach($easyRecipes as $easyRecipe)
            {
                array_push($quickRecipeArray, $easyRecipe->recipe_id);
            }

            $quickEasyRecipes = Recipe::with('image','meal','course','cousin','region','diet','intolerance','cookingskill')->whereIn('recipe_id', $quickRecipeArray)->orderBy('recipe_id', 'DESC')->get();



            $data = ['ingredients' => $parentIngredients, 'categories' => $categories, 'regions' => $regions, 'trending_recipes' => $myRecipes, 'quick_easy' => $quickEasyRecipes];


            return response()->json(['success' => $this->successStatus,
                                    'data' => $data,
                                ], $this->successStatus);
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Search Recipe
     * @Params $request
     */
    public function searchRecipe(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'keyword'   => 'required',
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $recipes = Recipe::with('image')->where('name', 'LIKE', '%'.$request->keyword.'%')->paginate(10);    
            
            if(count($recipes) > 0)
            {
                return response()->json(['success' => $this->successStatus,
                                        'data' => $recipes
                                ], $this->successStatus);
            }
            else
            {
                $message = "No recipe found";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Search Meal
     * @Params $request
     */
    public function searchMeal(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'keyword'   => 'required',
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $recipes = RecipeMeal::with('image_id')->where('name', 'LIKE', '%'.$request->keyword.'%')->get();    
            
            if(count($recipes) > 0)
            {
                return response()->json(['success' => $this->successStatus,
                                        'data' => $recipes
                                ], $this->successStatus);
            }
            else
            {
                $message = "No meals found";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * filter recipe
     * @Params $request
     */
    public function filterRecipe(Request $request)
    {
        try
        {
            $user = $this->user;
            $recipeArray = array();
            $condition = '';
            $isSearch = 0;
            
            if(!empty($request->cook_time))
            {
                $isSearch = 1;
                if($request->cook_time == 1 || $request->cook_time == 2)
                {
                    if($condition != '')
                    $condition .=" and recipes.hours = ".$request->cook_time;
                    else
                    $condition .="recipes.hours = ".$request->cook_time;
                }
                else
                {
                    if($condition != '')
                    $condition .=" and recipes.minutes = ".$request->cook_time;
                    else
                    $condition .="recipes.minutes = ".$request->cook_time;
                }
            }
            if(!empty($request->no_of_ingredients))
            {
                $isSearch = 1;
                $ingredientArray = array();
                $recipeByIngredients = DB::select(DB::raw("select `recipe_id` from `recipe_saved_ingredients` group by recipe_id having count(ingredient_id) < ".$request->no_of_ingredients));
                if(count($recipeByIngredients) > 0)
                {
                    foreach($recipeByIngredients as $recipeByIngredient)
                    {
                        array_push($ingredientArray, $recipeByIngredient->recipe_id);
                    }
                    $ingredientValues = join(",", $ingredientArray);

                    if($condition != '')
                    $condition .=" and recipes.recipe_id in(".$ingredientValues.")";
                    else
                    $condition .="recipes.recipe_id in(".$ingredientValues.")";
                }
            }
            if(!empty($request->meal_type))
            {
                $isSearch = 1;
                
                if($condition != '')
                $condition .=" and recipes.meal_id = ".$request->meal_type;
                else
                $condition .="recipes.meal_id = ".$request->meal_type;
                
            }
            if(!empty($request->cousin_id))
            {
                $isSearch = 1;
                
                if($condition != '')
                $condition .=" and recipes.cousin_id = ".$request->cousin_id;
                else
                $condition .="recipes.cousin_id = ".$request->cousin_id;
                
            }

            
            if($condition != '')
            {
                $recipes = Recipe::with('image')->whereRaw('('.$condition.')')->paginate(10);    
                return response()->json(['success' => $this->successStatus,
                                        'data' => $recipes
                                ], $this->successStatus);
            }
            else
            {
                $message = "No recipe found";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }


    /*
     * Validate Data
     * @Params $requestedfields
    */

    public function validateData($requestedFields){
        $rules = [];
        foreach ($requestedFields as $key => $field) 
        {
            if($key == 'name')
            {
                $rules[$key] = 'required|max:190';
            }
            elseif($key == 'meal_id')
            {
                $rules[$key] = 'required';
            }
            elseif($key == 'course_id')
            {
                $rules[$key] = 'required';
            }
            elseif($key == 'minutes')
            {
                $rules[$key] = 'required';
            }
            elseif($key == 'serving')
            {
                $rules[$key] = 'required';
            }
            elseif($key == 'cousin_id')
            {
                $rules[$key] = 'required';
            }
            elseif($key == 'region_id')
            {
                $rules[$key] = 'required';
            }
            elseif($key == 'intolerance_id')
            {
                $rules[$key] = 'required';
            }
            elseif($key == 'diet_id')
            {
                $rules[$key] = 'required';
            }
            elseif($key == 'cooking_skill_id')
            {
                $rules[$key] = 'required';
            }
            elseif($key == 'image_id')
            {
                $rules[$key] = 'required';
            }

            //Categories
            elseif($key == 'category_id')
            {
                $rules[$key] = 'required';
            }


            
        }

        return $rules;

    }



    

   
}
