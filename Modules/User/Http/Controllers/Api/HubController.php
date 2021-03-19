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
use Modules\User\Entities\MapHubCountryRole;
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


    /***
    get Countries
    ***/
    public function getHubCountries(Request $request)
    {
        try
        {
            $user = $this->user;
            $getAssignedCountries = MapHubCountryRole::where('role_id', $user->role_id)->get();
            $getCountries = $getAssignedCountries->pluck('country_id')->toArray();

            if(count($getCountries) > 0)
            {
                $countryData = Country::where('status', '1')->whereIn('id', $getCountries)->orderBy('name','ASC')->get();
            }
            else
            {
                $countryData = Country::where('status', '1')->orderBy('name','ASC')->get();
            }
            
            if(count($countryData) > 0)
            {
                return response()->json(['success' => $this->successStatus,
                                         'data' => $countryData,
                                        ], $this->successStatus);
            }
            else
            {
                return response()->json(['success'=>false,'errors' =>['exception' => 'No countries found']], $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /***
    get Active and Upcoming Countries
    ***/
    public function getActiveAndUpcomingCountries(Request $request)
    {
        try
        {
            $user = $this->user;
            $getAssignedCountries = MapHubCountryRole::where('role_id', $user->role_id)->where('is_active', '1')->get();
            $getUpcomingCountries = MapHubCountryRole::where('is_active', '0')->get();

            $getCountries = $getAssignedCountries->pluck('country_id')->toArray();
            $getComingCountries = $getUpcomingCountries->pluck('country_id')->toArray();

            if(count($getCountries) > 0)
            {
                $countryData = Country::where('status', '1')->whereIn('id', $getCountries)->orderBy('name','ASC')->get();
                $countryUpcomingCountrieData = Country::where('status', '1')->whereIn('id', $getComingCountries)->orderBy('name','ASC')->get();
            }
            else
            {
                $countryData = Country::where('status', '1')->orderBy('name','ASC')->get();
            }
            
            if(count($countryData) > 0)
            {
                $data = ['active_countries' => $countryData, 'upcoming_countries' => $countryUpcomingCountrieData];
                return response()->json(['success' => $this->successStatus,
                                         'data' => $data,
                                        ], $this->successStatus);
            }
            else
            {
                return response()->json(['success'=>false,'errors' =>['exception' => 'No countries found']], $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /***
    get Cities for Hubs
    ***/
    public function getHubsCity(Request $request)
    {
        try
        {
            $user = $this->user;
            $jsonArray = [];
            foreach($request->params as $state)
            {
                $stateData = State::where('id', $state)->first();
                
                $cities = City::where('state_id', $state)->where('status', '1')->get();
                if(count($cities) > 0)
                {
                    $harray[] = ['state_id'=>$stateData->id,'state_name'=>$stateData->name,'city_array'=>$cities];
                }
                else
                {
                    $harray[] = ['state_id'=>$stateData->id,'state_name'=>$stateData->name,'city_array'=>$cities];
                }
                    
            }
            $hubs = ['cities' => $harray];
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
    get Hubs
    ***/
    public function getHubs(Request $request)
    {
        try
        {
            $user = $this->user;
            $jsonArray = [];
            $hubsArray = [];
            foreach($request->params as $country => $states)
            {
                $countryData = Country::where('id', $country)->first();
                
                foreach($states as $state)
                {
                    $stateData = State::where('id', $state)->first();
                    
                    $hubs = Hub::with('image')->where('country_id', $country)->where('state_id', $state)->get();
                    if(count($hubs) > 0)
                    {
                        $harray[] = ['state_id'=>$stateData->id,'state_name'=>$stateData->name,'hubs_array'=>$hubs];
                    }
                    else
                    {
                        $harray[] = ['state_id'=>$stateData->id,'state_name'=>$stateData->name,'hubs_array'=>$hubs];
                    }
                    
                }
            }
            $hubs = ['hubs' => $harray];
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
