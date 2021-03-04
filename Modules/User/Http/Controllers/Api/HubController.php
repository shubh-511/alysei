<?php

namespace Modules\User\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\User\Entities\Hub; 
use Modules\User\Entities\City;
use Modules\User\Entities\State;
use Modules\User\Entities\Country;
use Modules\User\Entities\UserTempHub;
use Illuminate\Support\Facades\Auth; 
use Modules\User\Entities\MapHubCity;
use Modules\User\Entities\UserSelectedHub;
use Illuminate\Routing\Controller;
use Validator;

class HubController extends Controller
{
    public $successStatus = 200;
    public $validationStatus = 422;
    public $exceptionStatus = 409;

    public $user = '';

    public function __construct(){

        $this->middleware(function ($request, $next) {

            $this->user = Auth::user();
            return $next($request);
        });
    }


    /* 
        Get All Hubs
    */
    /*public function getHubs($role){

        try
        {
            $response_time = (microtime(true) - LARAVEL_START)*1000;

            $hubs = Hub::where('status', '1')->where('role_id', $role)->get();
            if(count($hubs) > 0)
            {
                return response()->json(['success'=>$this->successStatus,
                'title' => 'What are hubs?',
                'description' => 'Hubs allow you to connect with other located or working in specific loactions.',
                'data' => $hubs]);
            }
            else
            {
                return response()->json(['success'=>$this->successStatus,
                'title' => 'What are hubs?',
                'description' => 'Hubs allow you to connect with other located or working in specific loactions.',
                'message' => "currently no hubs found"]); 
            }
            
        }catch(\Exception $e){
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()]); 
        }

    }*/

    /***
    get Hubs
    ***/
    public function getHubs(Request $request)
    {
        try
        {
            $user = $this->user;
            $jsonArray = [];
            $hubs = [];
            foreach($request->params as $country => $states)
            {
                $countryData = Country::where('id', $country)->first();
                
                foreach($states as $state)
                {
                    $stateData = State::where('id', $state)->first();
                    
                    $hubs = Hub::where('country_id', $country)->where('state_id', $state)->get();
                    if(count($hubs) > 0)
                    {
                        $jsonArray[$countryData->name.' / '.$stateData->name] = $hubs;
                    }
                    else
                    {
                        $jsonArray[$countryData->name.' / '.$stateData->name] = [];
                    }
                    
                }
            }
            $hubs = ['hubs' => [$jsonArray]];
            return response()->json(['success' => $this->successStatus,
                                        'data' => $hubs,
                                    ], $this->successStatus);
                
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /***
    Post User Hubs
    ***/
    public function postUserHubs(Request $request)
    {
        try
        {
            $user = $this->user;

            if(!empty($request->params['selectedhubs']))
            {
                foreach($request->params['selectedhubs'] as $hub)
                {
                    $userHub = new UserSelectedHub;
                    $userHub->user_id = $user->user_id;
                    $userHub->hub_id = $hub;
                    $userHub->save();
                }
            }
            if(!empty($request->params['selectedcity']))
            {
                foreach($request->params['selectedcity'] as $city)
                {
                    $userHub = new UserTempHub;
                    $userHub->user_id = $user->user_id;
                    $userHub->country_id = $city['country_id'];
                    $userHub->state_id = $city['state_id'];
                    $userHub->city_id = $city['city_id'];
                    $userHub->save();
                }
            }
            
            return response()->json(['success' => $this->successStatus,
                                    'message' => 'Successfully added',
                                    ], $this->successStatus);
                
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }


    /*
     * Make Validation Rules
     * @Params $params
     */

    public function makeValidationRules($params){
        $rules = [];
        
        foreach ($params as $key => $field) {
            //return $key;
            if($key == 'hubs'){

                $rules[$key] = 'required';

            }else if($key == 'cities'){

                //$rules[$key] = 'required|max:190';

            }
        }

        return $rules;

    }

   
}
