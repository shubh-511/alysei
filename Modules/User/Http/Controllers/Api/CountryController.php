<?php

namespace Modules\User\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\User\Entities\Country;
use Modules\User\Entities\State;
use Modules\User\Entities\City;
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
    get States
    ***/
    public function getStates(Request $request)
    {
        try
        {
            $stateData = State::where('country_id', $request->country_id)->get();
            
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
            $cityData = City::where('state_id', $request->state_id)->get();
            
            if(count($cityData) > 0)
            {
                return response()->json(['success' => $this->successStatus,
                                         'data' => $cityData,
                                        ], $this->successStatus);
            }
            else
            {
                return response()->json(['success'=>false,'errors' =>['exception' => 'No cities found']], $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    
    
}
