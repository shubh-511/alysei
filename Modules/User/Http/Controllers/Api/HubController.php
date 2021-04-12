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
                $countryData = Country::select('id','name','flag_id','status')->with('flag_id')->where('status', '1')->whereIn('id', $getCountries)->orderBy('name','ASC')->get();
                $countryUpcomingCountrieData = Country::select('id','name','flag_id','status')->with('flag_id')->where('status', '1')->whereIn('id', $getComingCountries)->orderBy('name','ASC')->get();
            }
            else
            {
                $countryData = Country::select('id','name','flag_id','status')->with('flag_id')->where('status', '1')->orderBy('name','ASC')->get();
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

                $UserTempHubs = UserTempHub::where('user_id', $user->user_id)->whereIn('state_id', $request->params)->get();
                $allCity = $UserTempHubs->pluck('city_id')->toArray();

                if(!empty($UserTempHubs))
                {
                    foreach($cities as $key => $city)
                    {
                        if(in_array($city->id, $allCity))
                        {
                            $cities[$key]->is_selected = true;
                        }
                        else
                        {
                            $cities[$key]->is_selected = false;
                        }
                    }
                }
                
                
                $harray[] = ['state_id'=>$stateData->id,'state_name'=>$stateData->name,'city_array'=>$cities];
               
                    
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
                    
                    foreach($hubs as $key => $hub)
                    {
                        $UserSelectedHub = UserSelectedHub::where('user_id', $user->user_id)->where('hub_id', $hub->id)->first();
                        if(!empty($UserSelectedHub) && $hub->id == $UserSelectedHub->hub_id)
                        {
                            $hubs[$key]->is_selected = true;
                        }
                        else
                        {
                            $hubs[$key]->is_selected = false;
                        }
                    }
                    $harray[] = ['state_id'=>$stateData->id,'state_name'=>$stateData->name,'hubs_array'=>$hubs];
                    
                    
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
    Review Hubs Selection
    ***/
    public function hubsReviewSelection(Request $request)
    {
        try
        {
            $user = $this->user;
            $jsonArray = [];
            $harray = [];
            
            $UserSelectedHubs = UserSelectedHub::where('user_id', $user->user_id)->get();
            $UserTempHubs = UserTempHub::where('user_id', $user->user_id)->get();
            $selectedCountries = array();
            if(count($UserSelectedHubs) > 0 )
            {
                foreach($UserSelectedHubs as $UserSelectedHub)
                {
                    $selectedHub = Hub::where('id', $UserSelectedHub->hub_id)->first();
                    $selectedCountries[] = $selectedHub->country_id;
                }
                $getHubs = Hub::whereIn('country_id', $selectedCountries)->get();
                foreach($getHubs as $getHub)
                {
                    $countryData = Country::where('id', $getHub->country_id)->first();
                    $harray[] = ['country'=>$countryData->id,'country_name'=>$countryData->name,'hubs_array'=>$getHubs];
                }

            }
            /*if(count($UserTempHubs) > 0)
            {
                foreach($UserTempHubs as $UserTempHub)
                {
                    $selectedCountries[] = $UserTempHub->country_id;
                }
            }*/



            
              
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

            if(!empty($request->params['add_or_update']) && $request->params['add_or_update'] != 1) // 1=save, 2=update
            {
                UserSelectedHub::where('user_id', $user->user_id)->delete();
                UserTempHub::where('user_id', $user->user_id)->delete();
            }
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

            if(!empty($request->params['selectedcity']) || !empty($request->params['selectedhubs']))
            {
                return response()->json(['success' => $this->successStatus,
                                    'message' => 'Successfully added',
                                    ], $this->successStatus);
            }
            else
            {
                return response()->json(['success'=>false,'errors' =>['exception' => ['Please select atleast a hub or a city']]], $this->exceptionStatus); 
            }
            
            
                
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /***
    get Selected Hub Countries
    ***/
    public function getSelectedHubCountries(Request $request)
    {
        try
        {
            $user = $this->user;
            $jsonArray = [];
            $hubsArray = [];
            
            $UserSelectedHubs = UserSelectedHub::where('user_id', $user->user_id)->get();
            $UserTempHubs = UserTempHub::where('user_id', $user->user_id)->get();
            $selectedCountries = array();
            if(count($UserSelectedHubs) > 0 )
            {
                foreach($UserSelectedHubs as $UserSelectedHub)
                {
                    $selectedHub = Hub::where('id', $UserSelectedHub->hub_id)->first();
                    $selectedCountries[] = $selectedHub->country_id;
                }
            }
            if(count($UserTempHubs) > 0)
            {
                foreach($UserTempHubs as $UserTempHub)
                {
                    $selectedCountries[] = $UserTempHub->country_id;
                }
            }

            $getUpcomingCountries = MapHubCountryRole::where('is_active', '0')->get();
            $getComingCountries = $getUpcomingCountries->pluck('country_id')->toArray();

            $countryUpcomingCountrieData = Country::with('flag_id')->select('id','name','flag_id','status')->where('status', '1')->whereIn('id', $getComingCountries)->orderBy('name','ASC')->get();

            $getAssignedCountries = MapHubCountryRole::where('role_id', $user->role_id)->where('is_active', '1')->get();
            $getCountries = $getAssignedCountries->pluck('country_id')->toArray();

            if(count($getCountries) > 0)
            {
                $countryData = Country::with('flag_id')->select('id','name','flag_id','status')->where('status', '1')->whereIn('id', $getCountries)->orderBy('name','ASC')->get();
            }
            else
            {
                $countryData = Country::with('flag_id')->select('id','name','flag_id','status')->where('status', '1')->orderBy('name','ASC')->get();
            }

            foreach($countryData as $key => $country)
            {
                if(in_array($country->id, $selectedCountries))
                {
                    $countryData[$key]->is_selected = true;
                }
                else
                {
                    $countryData[$key]->is_selected = false;
                }
            }

            $data = ['active_countries' => $countryData, 'upcoming_countries' => $countryUpcomingCountrieData];

            return response()->json(['success' => $this->successStatus,
                                        'data' => $data,
                                    ], $this->successStatus);
                
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /***
    get Selected Hub States
    ***/
    public function getSelectedHubStates(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [ 
                'country_id' => 'required', 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $user = $this->user;

            $UserSelectedHubs = UserSelectedHub::where('user_id', $user->user_id)->get();
            $UserTempHubs = UserTempHub::where('user_id', $user->user_id)->where('country_id', $request->country_id)->get();

            $selectedStates = array();
            if(count($UserSelectedHubs) > 0 )
            {
                foreach($UserSelectedHubs as $UserSelectedHub)
                {
                    $selectedHub = Hub::where('id', $UserSelectedHub->hub_id)->first();
                    $selectedStates[] = $selectedHub->state_id;
                }
            }
            if(count($UserTempHubs) > 0)
            {
                foreach($UserTempHubs as $UserTempHub)
                {
                    $selectedStates[] = $UserTempHub->state_id;
                }
            }

            $states = State::where('status', '1')->where('country_id', $request->country_id)->orderBy('name','ASC')->get();
            
            if(count($states) > 0)
            {
                foreach($states as $key => $state)
                {
                    if(in_array($state->id, $selectedStates))
                    {
                        $states[$key]->is_selected = true;
                    }
                    else
                    {   
                        $states[$key]->is_selected = false;
                    }   
                }
                return response()->json(['success' => $this->successStatus,
                                         'data' => $states,
                                        ], $this->successStatus);
            }
            else
            {
                return response()->json(['success'=>false,'errors' =>['exception' => 'No states found']], $this->exceptionStatus);
            }
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
