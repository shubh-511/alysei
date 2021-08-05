<?php

namespace Modules\Recipe\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CoreController;
use Modules\User\Entities\User; 
use Modules\User\Entities\DeviceToken; 
use App\Http\Traits\NotificationTrait;
use Modules\Recipe\Entities\RecipeIngredient; 
use App\Notification;
use DB;
use Illuminate\Support\Facades\Auth; 
use Validator;
//use App\Events\UserRegisterEvent;

class RecipeIngredientController extends CoreController
{
    use NotificationTrait;
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
     * Get Permission For Sending Requests
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


    

   
}
