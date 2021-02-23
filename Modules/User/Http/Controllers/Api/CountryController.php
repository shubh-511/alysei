<?php

namespace Modules\User\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\User\Entities\Country;
use Modules\User\Entities\State;
use Modules\User\Entities\City;
use Modules\User\Entities\MapCountryRole;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth; 
use Validator;
//use App\Events\UserRegisterEvent;

class CountryController extends Controller
{
    public $successStatus = 200;
    public $validationStatus = 422;
    public $exceptionStatus = 409;
    public $unauthorisedStatus = 401;


    /***
    get Countries
    ***/
    public function getCountries(Request $request)
    {
        try
        {
            $getAssignedCountries = MapCountryRole::where('role_id', $request->role_id)->get();
            $getCountries = $getAssignedCountries->pluck('country_id')->toArray();

            if(count($getCountries) > 0)
            {
                $countryData = Country::whereIn('id', $getCountries)->orderBy('name','ASC')->get();
            }
            else
            {
                $countryData = Country::orderBy('name','ASC')->get();
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
    get States
    ***/
    public function getStates(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [ 
                'country_id' => 'required', 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $stateData = State::where('country_id', $request->country_id)->orderBy('name','ASC')->get();
            
            if(count($stateData) > 0)
            {
                return response()->json(['success' => $this->successStatus,
                                         'data' => $stateData,
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


    /***
    get Cities
    ***/
    public function getCities(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [ 
                'state_id' => 'required', 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $cityData = City::where('state_id', $request->state_id)->orderBy('name','ASC')->get()->toArray();
            $newArray =  [['id' => '','name' => 'Other','state_id' => '','status' => '1']];
            
            array_splice( $cityData, 0, 0, $newArray );
            
            if(count($cityData) > 0)
            {
                return response()->json(['success' => $this->successStatus,
                                         'data' => $cityData,
                                        ], $this->successStatus);
            }
            else
            {
                return response()->json(['success'=>false,'errors' => 'No cities found in this region'], $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    
    
}
