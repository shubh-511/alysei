<?php

namespace Modules\Activity\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CoreController;
use Modules\User\Entities\User; 
use Modules\User\Entities\DeviceToken; 
use App\Http\Traits\NotificationTrait;
use Modules\Recipe\Entities\RecipeCategory; 
use App\Notification;
use DB;
use Illuminate\Support\Facades\Auth; 
use Validator;
//use App\Events\UserRegisterEvent;

class RecipeCategoryController extends CoreController
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


    

   
}
