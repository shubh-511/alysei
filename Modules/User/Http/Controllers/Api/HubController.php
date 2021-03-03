<?php

namespace Modules\User\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\User\Entities\Hub; 
use Modules\User\Entities\City;
use Modules\User\Entities\State;
use Modules\User\Entities\Country;
use Modules\User\Entities\MapHubCity;
use Modules\User\Entities\UserSelectedHub;
use Illuminate\Routing\Controller;
use Validator;

class HubController extends Controller
{
    public $successStatus = 200;
    public $validationStatus = 422;
    public $exceptionStatus = 409;
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
            $jsonArray = [];
            $allHubs = [];
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

            return response()->json(['success' => $this->successStatus,
                                        'data' => $jsonArray,
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
            $rules = $this->makeValidationRules($request->params);
            $validator = Validator::make($request->params, $rules);

            if ($validator->fails()) { 

                return response()->json(['success'=>$this->validationStatus,'errors'=>$validator->errors()->first()], $this->validationStatus);
            }

            foreach($request->params as $hub)
            {
                $userHub = new UserSelectedHub;
                $userHub->user_id = $user;
                //$userHub->hub_id = 
            }

            return response()->json(['success' => $this->successStatus,
                                        'data' => $jsonArray,
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
