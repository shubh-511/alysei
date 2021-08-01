<?php

namespace Modules\User\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\Response;
use App\Http\Controllers\CoreController;
use Modules\User\Entities\User;
use Modules\User\Entities\Trip;
use Modules\User\Entities\AdventureType;
use Modules\User\Entities\Intensity;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth; 
use Validator;
//use App\Events\UserRegisterEvent;
use App\Http\Traits\UploadImageTrait;

class TripController extends CoreController
{
    use UploadImageTrait;
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

    /***
    Get trip listing
    ***/
    public function getTripListing()
    {
        try
        {
            $loggedInUser = $this->user;
            
            $tripLists = Trip::with('user:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','user.avatar_id','attachment','intensity','adventure')->where('user_id', $loggedInUser->user_id)->where('status', '1')->get();
            if(count($tripLists) > 0)
            {
                foreach($tripLists as $key => $tripList)
                {
                    $tripLists[$key]->trip_name = $this->translate('messages.'.$tripList->trip_name, $tripList->trip_name);
                }
                return response()->json(['success' => $this->successStatus,
                                         'data' => $tripLists,
                                        ], $this->successStatus);
            }
            else
            {
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'."No trips found","No trips found")]], $this->exceptionStatus);       
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /***
    Get adventure types
    ***/
    public function getAdventureTypes()
    {
        try
        {
            $loggedInUser = $this->user;
            
            $AdventureTypes = AdventureType::where('status', '1')->get();
            if(count($AdventureTypes) > 0)
            {
                foreach($AdventureTypes as $key => $AdventureType)
                {
                    $AdventureTypes[$key]->adventure_type = $this->translate('messages.'.$AdventureType->adventure_type, $AdventureType->adventure_type);
                }
                return response()->json(['success' => $this->successStatus,
                                         'data' => $AdventureTypes,
                                        ], $this->successStatus);
            }
            else
            {
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'."No adventure type found","No adventure type found")]], $this->exceptionStatus);       
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /***
    Get Intensity list
    ***/
    public function getIntensityList()
    {
        try
        {
            $loggedInUser = $this->user;
            
            $intensities = Intensity::where('status', '1')->get();
            if(count($intensities) > 0)
            {
                foreach($intensities as $key => $intensity)
                {
                    $intensities[$key]->intensity = $this->translate('messages.'.$intensity->intensity, $intensity->intensity);
                }
                return response()->json(['success' => $this->successStatus,
                                         'data' => $intensities,
                                        ], $this->successStatus);
            }
            else
            {
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'."No Intensity found","No Intensity found")]], $this->exceptionStatus);       
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /***
    Create trip
    ***/
    public function createTrip(Request $request)
    {
        try
        {
            $loggedInUser = $this->user;
            $validator = Validator::make($request->all(), [ 
                'trip_name' => 'required', 
                'travel_agency' => 'required',
                'region' => 'required',
                'adventure_type' => 'required',
                'duration' => 'required',  
                'intensity' => 'required', 
                'website' => 'required', 
                'price' => 'required', 
                'description' => 'required', 
                'image_id' => 'required', 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $createTrip = new Trip;
            $createTrip->user_id = $loggedInUser->user_id;
            $createTrip->trip_name = $request->trip_name;
            $createTrip->travel_agency = $request->travel_agency;
            $createTrip->region = $request->region;
            $createTrip->adventure_type = $request->adventure_type;
            $createTrip->duration = $request->duration;
            $createTrip->intensity = $request->intensity;
            $createTrip->website = $request->website;
            $createTrip->price = $request->price;
            $createTrip->description = $request->description;
            $createTrip->image_id = $this->uploadImage($request->file('image_id'));
            $createTrip->save();

            return response()->json(['success' => $this->successStatus,
                                    'message' => $this->translate('messages.'."Trip created successfuly!","Trip created successfuly!")
                                    ], $this->successStatus);
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }


    /***
    Edit Trip
    ***/
    public function editTrip($tripId = '')
    {
        try
        {
            $loggedInUser = $this->user;
            
            $trip = Trip::with('user:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','user.avatar_id','attachment','intensity','adventure')->where('trip_id', $tripId)->where('status', '1')->first();
            if(!empty($trip))
            {
                return response()->json(['success' => $this->successStatus,
                                         'data' => $trip,
                                        ], $this->successStatus);
            }
            else
            {
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'."Trip not found","Trip not found")]], $this->exceptionStatus);       
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }
    

    /***
    Update trip
    ***/
    public function updateTrip(Request $request)
    {
        try
        {
            $loggedInUser = $this->user;
            $validator = Validator::make($request->all(), [ 
                'trip_id'   =>  'required',
                'trip_name' => 'required', 
                'travel_agency' => 'required',
                'region' => 'required',
                'adventure_type' => 'required',
                'duration' => 'required',  
                'intensity' => 'required', 
                'website' => 'required', 
                'price' => 'required', 
                'description' => 'required', 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $trip = Trip::where('trip_id', $request->trip_id)->where('user_id', $loggedInUser->user_id)->first();
            if(!empty($trip))
            {
                $trip->trip_name = $request->trip_name;
                $trip->travel_agency = $request->travel_agency;
                $trip->region = $request->region;
                $trip->adventure_type = $request->adventure_type;
                $trip->intensity = $request->intensity;
                $trip->duration = $request->duration;
                $trip->website = $request->website;
                $trip->price = $request->price;
                $trip->description = $request->description;

                if(!empty($request->image_id))
                {
                    $this->deleteAttachment($trip->image_id);
                    $trip->image_id = $this->uploadImage($request->file('image_id'));
                }
            
                $trip->save();

                return response()->json(['success' => $this->successStatus,
                                        'message' => $this->translate('messages.'."Trip updated successfuly!","Trip updated successfuly!")
                                        ], $this->successStatus);
            }
            else
            {
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'."Something went wrong","Something went wrong")]], $this->exceptionStatus);
            }
            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /***
    Delete trip
    ***/
    public function deleteTrip(Request $request)
    {
        try
        {
            $loggedInUser = $this->user;
            $validator = Validator::make($request->all(), [ 
                'trip_id' => 'required', 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $trip = Trip::where('trip_id', $request->trip_id)->where('user_id', $loggedInUser->user_id)->first();
            if(!empty($trip))
            {
                $this->deleteAttachment($trip->image_id);
                $isTripDeleted = Trip::where('trip_id', $request->trip_id)->delete();
                if($isTripDeleted == 1)
                {
                    return response()->json(['success' => $this->successStatus,
                                    'message' => $this->translate('messages.'."Trip deleted successfuly!","Trip deleted successfuly!")
                                    ], $this->successStatus);
                }
                else
                {
                    return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'."Something went wrong","Something went wrong")]], $this->exceptionStatus);    
                }
            }
            else
            {
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'."Invalid event","Invalid event")]], $this->exceptionStatus);    
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }
    
}
