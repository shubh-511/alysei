<?php

namespace Modules\Recipe\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CoreController;
use Modules\User\Entities\User; 
use Modules\User\Entities\DeviceToken; 
use App\Http\Traits\NotificationTrait;
use Modules\Recipe\Entities\RecipeRegion; 
use App\Notification;
use DB;
use Illuminate\Support\Facades\Auth; 
use Validator;
//use App\Events\UserRegisterEvent;

class RecipeRegionController extends CoreController
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


    

   
}
