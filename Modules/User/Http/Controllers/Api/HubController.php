<?php

namespace Modules\User\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\User\Entities\Hub; 
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
            
            //return response()->json(['success'=>false,'errors' =>['exception' => 'No states found']], $this->exceptionStatus);
                
            $jsonArray = [];
            foreach($request->countries as $country => $states)
            {
                $hubs = Hub::where('country_id', $country)->first();
                if(!empty($hubs))
                {
                    
                    $hubData = MapHubCity::where('hub_id', $hubs->id)->whereIn('state_id', $states)->get();

                    if(count($hubData) > 0)
                    {
                        $countryData = Country::where('id', $country)->first();
                        $stateData = State::where('id', $states)->first();
                        $jsonArray[$countryData->name.' / '.$stateData->name][] = $hubData;
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
