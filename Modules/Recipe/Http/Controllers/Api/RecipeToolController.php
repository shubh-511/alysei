<?php

namespace Modules\Recipe\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CoreController;
use Modules\User\Entities\User; 
use Modules\User\Entities\DeviceToken; 
use App\Http\Traits\NotificationTrait;
use Modules\Recipe\Entities\RecipeTool; 
use App\Notification;
use DB;
use Illuminate\Support\Facades\Auth; 
use Validator;
//use App\Events\UserRegisterEvent;

class RecipeToolController extends CoreController
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


    

   
}
