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
use Modules\Recipe\Entities\RecipeSavedCategory;
use Modules\Recipe\Entities\RecipeStep;
use Modules\Recipe\Entities\RecipeSavedIngredient;
use Modules\Recipe\Entities\RecipeSavedTool;
use Modules\Recipe\Entities\RecipeMapStepTool;
use Modules\Recipe\Entities\RecipeMapStepIngredient;
use App\Notification;
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
     * Get recipie categories
     * 
     */
    public function getRecipeCourses()
    {
        try
        {
            $user = $this->user;

            $courses = RecipeCourse::with('image_id')->get();
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
     * Get recipie meals
     * 
     */
    public function getRecipeMeals()
    {
        try
        {
            $user = $this->user;

            $meals = RecipeMeal::with('image_id')->get();
            if(count($meals) > 0)
            {
                foreach($meals as $key => $meal)
                {
                    $meals[$key]->name = $this->translate('messages.'.$meals->name,$meals->name);
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
     * Get recipie categories
     * 
     */
    public function getRecipeRegions()
    {
        try
        {
            $user = $this->user;

            $regions = RecipeRegion::with('image_id')->get();
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
                    $parentTools[$key]->ingredients = $childTools;

                    $categoriesWithCount[] = ['ingredient_types' => $parentTool->title, 'name' => $parentTool->name, 'count' => $childIngredientCounts];
                    
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
            $recipe->region_id = $requestedFields['region_id'];
            //$recipe->image_id = $this->uploadImage($request->file($requestedFields['image_id']));
            $recipe->image_id = 1;
            $recipe->save();

            

            //save category
            foreach($requestedFields['save_categories'] as $savedCategory)
            {
                $categories = new RecipeSavedCategory;
                $categories->recipe_id = $recipe->id;
                $categories->category_id = $savedCategory;
                $categories->save();
            }
            

            //save ingredients
            foreach($requestedFields['saved_ingredients'] as $savedIngredients)
            {
                $savedIng = new RecipeSavedIngredient;
                $savedIng->recipe_id = $recipe->id;
                $savedIng->ingredient_id = $savedIngredients['ingredient_id'];
                $savedIng->quantity = $savedIngredients['quantity'];
                $savedIng->unit = $savedIngredients['unit'];
                $savedIng->save();
            }
            

            //save tools
            foreach($requestedFields['saved_tools'] as $savedTools)
            {
                $savedTool = new RecipeSavedTool;
                $savedTool->recipe_id = $recipe->id;
                $savedTool->tool_id = $savedTools['tool_id'];
                $savedTool->quantity = $savedTools['quantity'];
                $savedTool->unit = $savedTools['unit'];
                $savedTool->save();
            }
            

            //steps
            foreach($requestedFields['recipe_steps'] as $key => $recipeStep)
            {
                $step = new RecipeStep;
                $step->recipe_id = $recipe->id;
                $step->title = $recipeStep['title'];
                $step->description = $recipeStep['description'];
                $step->save();

                foreach($recipeStep['ingredients'] as $recipeStepsIngredient)
                {
                    $mapIngredientSteps = new RecipeMapStepIngredient;
                    $mapIngredientSteps->recipe_id = $recipe->id;
                    $mapIngredientSteps->recipe_step_id = $step->id;
                    $mapIngredientSteps->recipe_saved_ingredient_id = $recipeStepsIngredient;
                    $mapIngredientSteps->save();
                }

                foreach($recipeStep['tools'] as $recipeStepstool)
                {
                    $mapIngredientSteps = new RecipeMapStepTool;
                    $mapIngredientSteps->recipe_id = $recipe->id;
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
            elseif($key == 'region_id')
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
