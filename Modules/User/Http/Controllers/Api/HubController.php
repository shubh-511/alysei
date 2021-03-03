<?php

namespace Modules\User\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\User\Entities\Hub; 
use Modules\User\Entities\City;
use Modules\User\Entities\State;
use Modules\User\Entities\Country;
use Modules\User\Entities\MapHubCity;
use Illuminate\Routing\Controller;

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
            $hubData = [];
            foreach($request->params as $country => $states)
            {
                $countryData = Country::where('id', $country)->first();
                
                foreach($states as $state)
                {
                    $stateData = State::where('id', $state)->first();
                    
                    $hubs = Hub::where('country_id', $country)->where('state_id', $state)->first();
                    if(!empty($hubs))
                    {
                        $hubData = MapHubCity::with('hub:id,title')->where('hub_id', $hubs->id)
                        ->where('status',1)
                        ->groupBy('hub_id')
                        ->get();
                      
                    }
                    if(count($hubData) > 0)
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

   
}
