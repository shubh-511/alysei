<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CoreController;
use Modules\User\Entities\User; 
use Modules\User\Entities\BlockList; 
use Modules\User\Entities\UserSelectedHub; 
use Modules\User\Entities\Hub;
use Modules\User\Entities\State;
use Modules\User\Entities\UserField;
use Modules\User\Entities\UserFieldValue;
use Modules\User\Entities\Event;
use Modules\User\Entities\Trip;
use Modules\User\Entities\Blog;
use Modules\User\Entities\Award;
use App\Http\Traits\UploadImageTrait;
use Modules\User\Entities\FeaturedListing;
use Modules\Activity\Entities\UserPrivacy;
use Modules\Activity\Entities\Connection;
use Modules\Activity\Entities\Follower;
use Modules\Activity\Entities\ActivityAction;
use Modules\User\Entities\Role;
use Carbon\Carbon;
use DB;
use App\Attachment;
use Illuminate\Support\Facades\Auth; 
use Validator;
//use App\Events\UserRegisterEvent;

class SearchController extends CoreController
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

    /*
     * Get Featured Type Using Role Id
     * @params $roleId
     */
    public function getFeaturedListingTypes($roleId){
        $featuredTypes = DB::table("featured_listing_types as flt")
            ->join("featured_listing_type_role_maps as fltrm", 'fltrm.featured_listing_type_id', '=', 'flt.featured_listing_type_id')

            ->where("fltrm.role_id","=",$roleId)
            ->get();

        return $featuredTypes;
    }


    /*
     * Search user and hubs
     *
     */
    public function search(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'search_type' => 'required' 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            if($request->search_type == 1)
            {
                $validateSearchType = Validator::make($request->all(), [ 
                    'keyword' => 'required' 
                ]);

                if ($validateSearchType->fails()) { 
                    return response()->json(['errors'=>$validateSearchType->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
                }

                return $this->globalSearch($request->keyword);   
            }
            elseif($request->search_type == 2)
            {
                $validateSearchType = Validator::make($request->all(), [ 
                    'role_id' => 'required' 
                ]);

                if ($validateSearchType->fails()) { 
                    return response()->json(['errors'=>$validateSearchType->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
                }

                return $this->searchUserByRoles($request->role_id, $request, $user->user_id);
            }
            elseif($request->search_type == 3)
            {
                return $this->searchUserByHubs($request, $user->user_id);
            }
            else
            {
                $message = "Invalid search type";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
    * Search users by hubs
    */
    public function searchUserByHubs($request, $myId)
    {
        $hubsArray = array();
        $condition = '';
        $isSearch = 0;
        
        if(!empty($request->keyword))
        {
            $isSearch = 1;
            $hubs = Hub::where('title', 'LIKE', '%' . $request->keyword . '%')->get();
            if(count($hubs) > 0)
            {
                foreach($hubs as $hub)
                {
                    array_push($hubsArray, $hub->id);
                }
            }
            if(count($hubsArray) > 0)
            {
                if($condition != '')
                $condition .=" and hubs.title LIKE "."'%".$request->keyword."%'"."";
                else
                $condition .="hubs.title LIKE "."'%".$request->keyword."%'"."";
            }
        }
        if(!empty($request->state))
        {
            $isSearch = 1;
            $hubs = explode(",", $request->state);
            $hubsByStates = Hub::whereIn('state_id', $hubs)->get();
            if(count($hubsByStates) > 0)
            {
                if($condition != '')
                $condition .=" and hubs.state_id in(".$request->state.")";
                else
                $condition .="hubs.state_id in(".$request->state.")";
            }
            /*$hubsByState = Hub::where('state_id', $request->state)->first();
            if(!empty($hubsByState))
            {
                if($condition != '')
                $condition .=" and hubs.state_id = ".$hubsByState->state_id."";
                else
                $condition .="hubs.state_id = ".$hubsByState->state_id."";
                array_push($hubsArray, $hubsByState->id);
            }*/
            
        }

        if($isSearch == 0)
        {
            $myHubsArray = [];
            $myHubs = UserSelectedHub::where('user_id', $myId)->get();
            if(count($myHubs) > 0)
            {
                $myHubsArray = $myHubs->pluck('hub_id');
                $hubs = Hub::with('image:id,attachment_url','country:id,name','state:id,name')->whereIn('id', $myHubsArray)->where('status', '1')->paginate(10);
            }
            else
            {
                $hubs = [];
            }
            
        }
        else
        {
            if($condition != '')
            {
                $hubs = Hub::with('image:id,attachment_url','country:id,name','state:id,name')->whereRaw('('.$condition.')')->where('status', '1')->paginate(10);    
            }
            else
            {
                $message = "No hubs found";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
            
        }

        
        if(count($hubs) > 0)
        {
            return response()->json(['success' => $this->successStatus,
                                'data' => $hubs
                                ], $this->successStatus);
        }
        else
        {
            $message = "No hubs found";
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
        }
    }

    /*
    /*Subscribe/Unsubscribe hub
    */
    public function subscribeOrUnsubscribeHub(Request $request)
    {
        try
        {
            $user = $this->user;   
            $validateData = Validator::make($request->all(), [ 
                'hub_id' => 'required', 
                'subscription_type' => 'required'  // 1 = subscribe, 0 = unsubscribe
            ]);

            if ($validateData->fails()) { 
                return response()->json(['errors'=>$validateData->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $hub = Hub::where('id', $request->hub_id)->first();
            if(!empty($hub))
            {
                $isSubscribedWithHub = UserSelectedHub::where('user_id', $user->user_id)->where('hub_id', $request->hub_id)->first();
                if($request->subscription_type == 1)
                {
                    if(!empty($isSubscribedWithHub))
                    {
                        $message = "You have already subscribed to this hub";
                        return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus); 
                    }
                    else
                    {
                        $selectedHub = new UserSelectedHub;
                        $selectedHub->user_id = $user->user_id;
                        $selectedHub->hub_id = $request->hub_id;
                        $selectedHub->save();

                        $message = "You have subscribed to this hub";
                        return response()->json(['success' => $this->successStatus,
                                                 'message' => $this->translate('messages.'.$message,$message),
                                                ], $this->successStatus);
                    }
                }
                elseif($request->subscription_type == 0)
                {
                    if(!empty($isSubscribedWithHub))
                    {
                        $unsubscribeWithHub = UserSelectedHub::where('user_id', $user->user_id)->where('hub_id', $request->hub_id)->delete();
                        if($unsubscribeWithHub == 1)
                        {
                            $message = "You have unsubscribed from this hub";
                            return response()->json(['success' => $this->successStatus,
                                                 'message' => $this->translate('messages.'.$message,$message),
                                                ], $this->successStatus);
                        }
                        else
                        {
                            $message = "You have to first subscribe to this hub";
                            return response()->json(['success' => $this->exceptionStatus,
                                                 'message' => $this->translate('messages.'.$message,$message),
                                                ], $this->exceptionStatus);
                        }
                    }
                    else
                    {
                        $message = "You have to first subscribe to this hub";
                        return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                    }
                }
                else
                {
                    $message = "Invalid subscription type";
                    return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                }
                
            }
            else
            {
                $message = "Invalid hub id";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
        
    }

    /*Get states list
    */
    public function getStates(Request $request)
    {
        try
        {
            $user = $this->user;   
            $userCountry = User::where('user_id', $user->user_id)->first();         
            
            if(!empty($request) && $request->param == 'usa')
            {
                $states = State::select('id','name','country_id')->where('country_id', 233)->where('status', '1')->get();
            }
            else
            {
                $states = State::select('id','name','country_id')->where('country_id', $userCountry->country_id)->where('status', '1')->get();
            }
            
            if(count($states) > 0)
            {
                foreach($states as $key => $state)
                {
                    $states[$key]->name = $this->translate('messages.'.$state->name,$state->name);
                }
                return response()->json(['success' => $this->successStatus,
                                    'data' => $states
                                    ], $this->successStatus);
            }
            else
            {
                $message = "No states found";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
        
    }

    /*Get roles by hub
    */
    public function getRolesByHub($hubId)
    {
        try
        {
            $user = $this->user;   

            $isHubSelected = UserSelectedHub::where('user_id', $user->user_id)->where('hub_id', $hubId)->first();
            if(!empty($isHubSelected))
            {
                $subscription = 1;
            }
            else
            {
                $subscription = 0;
            }
            
            $hub = Hub::where('id', $hubId)->first();         
            if(!empty($hub))
            {
                $users = UserSelectedHub::where('hub_id', $hubId)->get();
                if(count($users) > 0)
                {
                    $users = $users->pluck('user_id');
                    if($user->role_id != 10)
                    {
                        $roles = Role::select('role_id','name','slug')->whereNotIn('slug',['super_admin','admin','importer','distributer','voyagers'])->orderBy('order')->get();
                    }
                    else
                    {
                        $roles = Role::select('role_id','name','slug')->whereNotIn('slug',['super_admin','admin','importer','distributer'])->orderBy('order')->get();
                    }
                    

                    foreach($roles as $key => $role)
                    {
                        if($roles[$key]->name == "US Importers & Distributers")
                        {
                            $roles[$key]->name = $this->translate('messages.'.'Importer & Distributor','Importer & Distributor');
                        }
                        $roles[$key]->name = $this->translate('messages.'.$role->name,$role->name);
                        $roles[$key]->image = "public/images/roles/".$role->slug.".jpg";

                        /*$userWithRole = User::whereHas(
                            'roles', function($q) use ($role){
                                $q->where('slug', $role->slug);
                            }
                        )->whereIn('user_id', $users)->count();*/
                        if($role->role_id == 6)
                        {
                            $userWithRole = User::whereHas(
                            'roles', function($q) use ($role){
                                $q->where('role_id', 4)
                                ->orwhere('role_id', 5)
                                ->orwhere('role_id', 6);
                                }
                            )->whereIn('user_id', $users)->count();
                        }
                        else
                        {
                            $userWithRole = User::whereHas(
                            'roles', function($q) use ($role){
                                $q->where('slug', $role->slug);
                                }
                            )->whereIn('user_id', $users)->count();
                        }

                        $roles[$key]->user_count = $userWithRole;
                    }

                    return response()->json(['success' => $this->successStatus,
                                    'is_subscribed_with_hub' => $subscription,
                                    'data' => $roles
                                    ], $this->successStatus);
                }
            }
            else
            {
                $message = "Invalid hub";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
        
    }

    /*Get User list by roles
    */
    public function getUserInCurrentRole(Request $request)
    {
        try
        {
            $user = $this->user;   
            $newArr = [];
            $validateSearchType = Validator::make($request->all(), [ 
                'hub_id' => 'required', 
                'role_id' => 'required' 
            ]);

            if ($validateSearchType->fails()) { 
                return response()->json(['errors'=>$validateSearchType->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $users = UserSelectedHub::where('hub_id', $request->hub_id)->get();
            if(count($users) > 0)
            {
                $users = $users->pluck('user_id');
                /*$blockList = BlockList::where('user_id', $user->user_id)->whereIn('block_user_id', $users)->get();
                if(count($blockList) > 0)
                {
                    $blockUsers = $blockList->pluck('block_user_id');
                }*/

                if($request->role_id == 6)
                {
                    $userWithRole = User::select('user_id','name','email','first_name','last_name','company_name','restaurant_name','role_id','avatar_id')->with('avatar_id')->where('user_id', '!=', $user->user_id)->whereIn('user_id', $users)->whereIn('role_id', [4,5,6])->orderBy('user_id', 'DESC')->get();
                }
                else
                {
                    $userWithRole = User::select('user_id','name','email','first_name','last_name','company_name','restaurant_name','role_id','avatar_id')->with('avatar_id')->where('user_id', '!=', $user->user_id)->where('role_id', $request->role_id)->whereIn('user_id', $users)->orderBy('user_id', 'DESC')->get();
                }
                
                /*foreach($userWithRole as $key => $getUser)
                {
                    if(in_array($getUser->user_id, $users))
                    {
                        $newArr[] = $userWithRole[$key];    
                    }                    
                }
                if(!empty($newArr))
                {
                    $userWithRole = $newArr;
                }*/
                    
                return response()->json(['success' => $this->successStatus,
                                'count' => count($userWithRole),
                                'data' => $userWithRole
                                ], $this->successStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
        
    }

    /*
    * Search user by roles
    */
    public function searchUserByRoles($roleId, $request, $myId)
    {
        $usersArray = array();
        $condition = 0;
        $userType = 6;
        $hubUserCount = $countryUsercount = $regionUserCount = $productUserCount = $horecaUserCount = $privateLabelCount = $brandLabelCount = $restaurantTypeCount = $pickUpsUserCount = $deliveryUserCount = $expertiseUserCount = $titleCount = $specialityCount = 1;

        if(!empty($request->hubs))
        {
            $condition = 1;
            $hubs = explode(",", $request->hubs);
            $selectedHubs = UserSelectedHub::whereIn('hub_id', $hubs)->groupBy('user_id')->get();
            if(count($selectedHubs))
            {
                $selectedHubs = $selectedHubs->pluck('user_id');
                
                foreach($selectedHubs as $selectedHub)
                {
                    array_push($usersArray, $selectedHub);
                }
            }
            if(count($usersArray) == 0)
            {
                $hubUserCount = 0;
            }
            
        }
        if(!empty($request->country))
        {
            $condition = 1;
            $countries = UserFieldValue::where('value', $request->country)->where('user_field_id', 13)->groupBy('user_id')->get();
            if(count($countries))
            {
                $countries = $countries->pluck('user_id');
               
                foreach($countries as $selectedCountry)
                {
                    array_push($usersArray, $selectedCountry);
                }
            }
            if(count($usersArray) == 0)
            {
                $countryUsercount = 0;
            }
            
        }
        if(!empty($request->region))
        {
            $condition = 1;
            $regions = UserFieldValue::where('value', $request->region)->where('user_field_id', 28)->groupBy('user_id')->get();
            if(count($regions))
            {
                $regions = $regions->pluck('user_id');
               
                foreach($regions as $selectedRegion)
                {
                    array_push($usersArray, $selectedRegion);
                }
            }
            if(count($usersArray) == 0)
            {
                $regionUserCount = 0;
            }
            
        }
        
        if($roleId == 3 || $roleId == 6)
        {
            if(!empty($request->product_type))
            {
                $condition = 1;
                $productTypeArray = explode(",", $request->product_type);
                $productTypes = UserFieldValue::whereIn('value', $productTypeArray)->where('user_field_id', 2)->groupBy('user_id')->get();
                if(count($productTypes))
                {
                    $productTypes = $productTypes->pluck('user_id');
                    
                    foreach($productTypes as $productType)
                    {
                        array_push($usersArray, $productType);
                    }
                }
                if(count($usersArray) == 0)
                {
                    $productUserCount = 0;
                }
                
            }
            if(!empty($request->horeca))
            {
                $condition = 1;
                $horeca = UserFieldValue::where('value', $request->horeca)->where('user_field_id', 4)->groupBy('user_id')->get();
                if(count($horeca))
                {
                    $horeca = $horeca->pluck('user_id');
                    
                    foreach($horeca as $horecaUsers)
                    {
                        array_push($usersArray, $horecaUsers);
                    }
                }
                if(count($usersArray) == 0)
                {
                    $horecaUserCount = 0;
                }
                
            }
            if(!empty($request->private_label))
            {
                $condition = 1;
                $privateLabels = UserFieldValue::where('value', $request->private_label)->where('user_field_id', 5)->groupBy('user_id')->get();
                if(count($privateLabels))
                {
                    $privateLabels = $privateLabels->pluck('user_id');
                   
                    foreach($privateLabels as $privateLabel)
                    {
                        array_push($usersArray, $privateLabel);
                    }
                }
                if(count($usersArray) == 0)
                {
                    $privateLabelCount = 0;
                }
                
            }
            if(!empty($request->alysei_brand_label))
            {
                $condition = 1;
                $brandLabels = UserFieldValue::where('value', $request->alysei_brand_label)->where('user_field_id', 6)->groupBy('user_id')->get();
                if(count($brandLabels))
                {
                    $brandLabels = $brandLabels->pluck('user_id')->toArray();
                    
                    foreach($brandLabels as $brandLabel)
                    {
                        array_push($usersArray, $brandLabel);
                    }
                }
                if(count($usersArray) == 0)
                {
                    $brandLabelCount = 0;
                }
                
            }
            if(!empty($request->user_type) && $request->user_type == 4)
            {
                $roleId = 4;
            }
            elseif(!empty($request->user_type) && $request->user_type == 5)
            {
                $roleId = 5;
            }
        }
        elseif($roleId == 9)
        {
            if(!empty($request->restaurant_type))
            {
                $restaurantTypesArray = explode(",", $request->restaurant_type);
                $condition = 1;
                $restaurantTypes = UserFieldValue::whereIn('value', $restaurantTypesArray)->where('user_field_id', 10)->groupBy('user_id')->get();
                if(count($restaurantTypes))
                {
                    $restaurantTypes = $restaurantTypes->pluck('user_id');
                   
                    foreach($restaurantTypes as $restaurantType)
                    {
                        array_push($usersArray, $restaurantType);
                    }
                }
                if(count($usersArray) == 0)
                {
                    $restaurantTypeCount = 0;
                }
                
            }
            if(!empty($request->pickup))
            {
                $condition = 1;
                $pickUps = UserFieldValue::where('value', $request->pickup)->where('user_field_id', 9)->groupBy('user_id')->get();
                if(count($pickUps))
                {
                    $pickUps = $pickUps->pluck('user_id');
                   
                    foreach($pickUps as $pickUp)
                    {
                        array_push($usersArray, $pickUp);
                    }
                }
                if(count($usersArray) == 0)
                {
                    $pickUpsUserCount = 0;
                }
                
            }
            if(!empty($request->pickupdiscount))
            {
                $condition = 1;
                $pickUpDiscounts = UserFieldValue::where('value', $request->pickupdiscount)->where('user_field_id', 21)->groupBy('user_id')->get();
                if(count($pickUpDiscounts))
                {
                    $pickUpDiscounts = $pickUpDiscounts->pluck('user_id');
                   
                    foreach($pickUpDiscounts as $pickUpDiscount)
                    {
                        array_push($usersArray, $pickUpDiscount);
                    }
                }
                if(count($usersArray) == 0)
                {
                    $count = 0;
                }
                
            }
            if(!empty($request->delivery))
            {
                $condition = 1;
                $deleveries = UserFieldValue::where('value', $request->delivery)->where('user_field_id', 9)->groupBy('user_id')->get();
                if(count($deleveries))
                {
                    $deleveries = $deleveries->pluck('user_id');
                   
                    foreach($deleveries as $delevery)
                    {
                        array_push($usersArray, $delevery);
                    }
                }
                if(count($usersArray) == 0)
                {
                    $deliveryUserCount = 0;
                }
                
            }
            if(!empty($request->delivery_discount))
            {
                $condition = 1;
                $deleveryDiscounts = UserFieldValue::where('value', $request->delivery_discount)->where('user_field_id', 22)->groupBy('user_id')->get();
                if(count($deleveryDiscounts))
                {
                    $deleveryDiscounts = $deleveryDiscounts->pluck('user_id');
                   
                    foreach($deleveryDiscounts as $deleveryDiscount)
                    {
                        array_push($usersArray, $deleveryDiscount);
                    }
                }
                if(count($usersArray) == 0)
                {
                    $count = 0;
                }
                
            }

        }
        elseif($roleId == 7)
        {
            if(!empty($request->expertise))
            {
                $condition = 1;
                $expertis = explode(",", $request->expertise);
                $userExpertise = UserFieldValue::whereIn('value', $expertis)->where('user_field_id', 11)->groupBy('user_id')->get();
                if(count($userExpertise))
                {
                    $userExpertise = $userExpertise->pluck('user_id');
                   
                    foreach($userExpertise as $expertise)
                    {
                        array_push($usersArray, $expertise);
                    }
                }
                if(count($usersArray) == 0)
                {
                    $expertiseUserCount = 0;
                }
                
            }
            if(!empty($request->title))
            {
                $condition = 1;
                $titl = explode(",", $request->title);
                $titles = UserFieldValue::whereIn('value', $titl)->where('user_field_id', 12)->groupBy('user_id')->get();
                if(count($titles))
                {
                    $titles = $titles->pluck('user_id');
                   
                    foreach($titles as $title)
                    {
                        array_push($usersArray, $title);
                    }
                }
                if(count($usersArray) == 0)
                {
                    $titleCount = 0;
                }
                
            }
        }
        elseif($roleId == 8)
        {
            if(!empty($request->speciality))
            {
                $condition = 1;
                $speciality = explode(",", $request->speciality);
                $specialities = UserFieldValue::whereIn('value', $speciality)->where('user_field_id', 14)->groupBy('user_id')->get();
                if(count($specialities))
                {
                    $specialities = $specialities->pluck('user_id');
                   
                    foreach($specialities as $specialit)
                    {
                        array_push($usersArray, $specialit);
                    }
                }
                if(count($usersArray) == 0)
                {
                    $specialityCount = 0;
                }
                
            }
        }

        if($condition == 0)
        {
            $myHubs = UserSelectedHub::where('user_id', $myId)->get();
            if(isset($myHubs) && count($myHubs) > 0 && $roleId != 10)
            {
                $myHubs = $myHubs->pluck('hub_id');
                $defaultHubs = UserSelectedHub::whereIn('hub_id', $myHubs)->get();
                $defaultHubsUser = $defaultHubs->pluck('user_id');

                $users = User::select('user_id','name','email','first_name','last_name','company_name','restaurant_name','role_id','avatar_id')
                ->with('avatar_id')
                ->whereIn('user_id', $defaultHubsUser)
                ->where('user_id', '!=' , $myId)
                ->where('role_id', $roleId)
                ->groupBy('user_id')
                ->orderBy('user_id', 'DESC')
                ->paginate(10);
            }
            else
            {
                $users = User::select('user_id','name','email','first_name','last_name','company_name','restaurant_name','role_id','avatar_id')
                ->with('avatar_id')
                ->where('user_id', '!=' , $myId)
                ->where('role_id', $roleId)
                ->groupBy('user_id')
                ->orderBy('user_id', 'DESC')
                ->paginate(10);
            }
            
        }
        else
        {
            if($countryUsercount == 0 || $regionUserCount == 0 || $productUserCount == 0 || $horecaUserCount == 0 || $privateLabelCount == 0 || $brandLabelCount == 0 || $restaurantTypeCount == 0 || $pickUpsUserCount == 0 || $deliveryUserCount == 0 || $expertiseUserCount == 0 || $titleCount == 0 || $specialityCount == 0)
            {
                $users = [];
            }
            else
            {
                if(!empty($request->hub_id))
                {
                    $users = DB::table('users')->select('users.user_id','name','email','first_name','last_name','company_name','restaurant_name','role_id','avatar_id')
                    //->with('avatar_id')
                    ->join('user_selected_hubs','user_selected_hubs.user_id','=','users.user_id')
                    ->where('user_selected_hubs.hub_id','=',$request->hub_id)
                    ->where('role_id', $roleId)
                    ->where('users.user_id', '!=' , $myId)
                    ->whereIn('users.user_id', $usersArray)
                    ->groupBy('users.user_id')
                    ->orderBy('users.user_id', 'DESC')
                    ->paginate(10);
                    foreach($users as $keyVal => $user)
                    {
                        $avatarId = Attachment::where('id', $user->avatar_id)->first();
                        $users[$keyVal]->avatar_id = $avatarId;
                    }
                }
                else
                {
                    $users = User::select('user_id','name','email','first_name','last_name','company_name','restaurant_name','role_id','avatar_id')
                    ->with('avatar_id')
                    ->where('role_id', $roleId)
                    ->where('user_id', '!=' , $myId)
                    ->whereIn('user_id', $usersArray)
                    ->groupBy('user_id')
                    ->orderBy('user_id', 'DESC')
                    ->paginate(10);
                }
                  
            }
              
        }
        
        if(count($users) > 0)
        {
            return response()->json(['success' => $this->successStatus,
                                'data' => $users
                                ], $this->successStatus);
        }
        else
        {
            $message = "No users found";
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
        }
    }

    
    /*
    * Searching User
    */
    public function globalSearch($keyWord)
    {
        $user = $this->user;
        $users = User::select('user_id','role_id','name','email','first_name','last_name','company_name','restaurant_name','avatar_id')->with('avatar_id')
        ->whereNotIn('role_id', [1,2])
        ->where('email', 'LIKE', '%' . $keyWord . '%')
        ->orWhere('first_name', 'LIKE', '%' . $keyWord . '%')
        ->orWhere('company_name', 'LIKE', '%' . $keyWord . '%')
        ->orWhere('restaurant_name', 'LIKE', '%' . $keyWord . '%')
        ->orderBy('user_id', 'DESC')
        ->paginate(10);

        $events = Event::with('user:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','user.avatar_id','attachment')->where('status', '1')->Where('event_name', 'LIKE', '%' . $keyWord . '%')->paginate(10);

        $blogs = Blog::with('user:user_id,name,email,first_name,last_name,company_name,restaurant_name,role_id,avatar_id','user.avatar_id','attachment')->where('title', 'LIKE', '%' . $keyWord . '%')->where('status', '1')->paginate(10);

        $trips = Trip::with('user:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','user.avatar_id','attachment','intensity','country:id,name','region:id,name')->where('trip_name', 'LIKE', '%' . $keyWord . '%')->where('status', '1')->paginate(10);

        $awards = Award::with('user:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','user.avatar_id','attachment','medal')->where('award_name', 'LIKE', '%' . $keyWord . '%')->where('status', '1')->paginate(10);


        $myConnections = Connection::select('*','user_id as poster_id')->where('resource_id', $user->user_id)->where('is_approved', '1')->get();
        $myConnections = $myConnections->pluck('poster_id');

        $myFollowers = Follower::select('*','follow_user_id as poster_id')->where('user_id', $user->user_id)->get();
        $myFollowers = $myFollowers->pluck('poster_id');

        $merged = $myConnections->merge($myFollowers);
        $userIds = $merged->all();
        

        if(count($userIds) > 0)
        {
            array_push($userIds, $user->user_id);
            $userIds = array_unique($userIds);
            $activityPosts = ActivityAction::select('activity_action_id','type','subject_id','body','shared_post_id','attachment_count','comment_count','like_count','privacy','created_at','height','width')
            ->with('attachments.attachment_link')
            ->with('subject_id:user_id,name,email,company_name,first_name,last_name,restaurant_name,role_id,avatar_id','subject_id.avatar_id')
            ->where('body', 'LIKE', '%' . $keyWord . '%')
            ->Where(function ($query) use ($user, $userIds) {
            $query->where('privacy', 'public')
              ->orWhere('privacy', 'followers')
              ->whereIn('subject_id', $userIds);
             })
            ->orderBy('created_at', 'DESC')
            ->paginate(10);
        }
        else
        {
            $activityPosts = ActivityAction::select('activity_action_id','type','subject_id','body','shared_post_id','attachment_count','comment_count','like_count','privacy','created_at','height','width')
            ->with('attachments.attachment_link')
            ->with('subject_id:user_id,name,email,company_name,first_name,last_name,restaurant_name,role_id,avatar_id','subject_id.avatar_id')
            ->where('body', 'LIKE', '%' . $keyWord . '%')
            ->Where(function ($query) use ($user) {
            $query->where('privacy', 'public')
              ->orWhere('subject_id', $user->user_id);
             })
            ->orderBy('created_at', 'DESC')
            ->paginate(10);
        }

        $fieldsTypes = $this->getFeaturedListingTypes($this->user->role_id);
            
        $products = [];

        foreach($fieldsTypes as $fieldsTypesKey => $fieldsTypesValue){
            
            $featuredListing = FeaturedListing::with('image')
                                ->where('user_id', $this->user->user_id)
                                ->where('featured_listing_type_id', $fieldsTypesValue->featured_listing_type_id)
                                ->where('title', 'LIKE', '%' . $keyWord . '%')
                                ->orderBy('featured_listing_id','DESC')->paginate(10); 

            $products[] = ["title" => $fieldsTypesValue->title,"slug" => $fieldsTypesValue->slug,"products" => $featuredListing];
            
        }

        $data = ['peoples' => $users, 'posts' => $activityPosts, 'events' => $events, 'blogs' => $blogs, 'trips' => $trips, 'awards' => $awards, 'products' => $products];
        return response()->json(['success' => $this->successStatus,
                                 'data' => $data
                                ], $this->successStatus);
   
    }


    /*
     * Get list of hubs seleted by user
     *
     */
    public function getAllHubs()
    {
        try
        {
            $user = $this->user;
            $checkUser = User::where('user_id', $user->user_id)->first();
            $myHubs = UserSelectedHub::where('user_id', $user->user_id)->get();
            if(!empty($checkUser))
            {
                $hubs = Hub::select('id','title')->where('status', '1')->get();
                if(count($hubs) > 0)
                {
                    return response()->json(['success' => $this->successStatus,
                                 'hubs' => $hubs
                                ], $this->successStatus);
                }
                else
                {
                    $message = "No hubs available";
                    return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                }
                /*if(count($myHubs) > 0)
                {
                    $myHubs = $myHubs->pluck('hub_id')->toArray();
                    $hubs = Hub::select('id','title')->whereIn('id', $myHubs)->where('status', '1')->get();
                    if(count($hubs) > 0)
                    {
                        return response()->json(['success' => $this->successStatus,
                                     'hubs' => $hubs
                                    ], $this->successStatus);
                    }
                    else
                    {
                        $message = "No hubs available";
                        return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                    }
                }
                else
                {
                    $message = "You have not selected any hubs";
                    return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                }*/
            }
            else
            {
                $message = "Invalid user";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }               
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Get pickup or delivery
     *
     */
    public function getPickupOrDelivery()
    {
        try
        {
            $user = $this->user;
            $role_id = $user->role_id;
            $steps = [];
            $checkUser = User::where('user_id', $user->user_id)->first();
            
            $roleFields = DB::table('user_fields')
                                      ->whereIn("user_field_id", [9,21,22])
                                      ->get();
            if(!empty($roleFields))
            {
                foreach ($roleFields as $key => $value)
                {
                    $roleFields[$key]->title = $this->translate('messages.'.$value->title,$value->title);

                    //Check Fields has option
                    if($value->type !='text' && $value->type !='email' && $value->type !='password')
                    {
                        
                        $value->options = $this->getUserFieldOptionParent($value->user_field_id);

                        if(!empty($value->options))
                        {
                            foreach ($value->options as $k => $oneDepth) 
                            {

                                $value->options[$k]->option = $this->translate('messages.'.$oneDepth->option,$oneDepth->option);

                                //Check Option has any Field Id
                                $checkRow = DB::table('user_field_maps')->where('user_field_id','=',$value->user_field_id)->where('role_id', 9)->first();

                                if($checkRow){
                                    $value->parentId = $checkRow->option_id;
                                }

                                $data = $this->getUserFieldOptionsNoneParent($value->user_field_id,$oneDepth->user_field_option_id);

                                $value->options[$k]->options = $data;

                                
                                foreach ($value->options[$k]->options as $optionKey => $optionValue) 
                                {
                                    $options = $this->getUserFieldOptionsNoneParent($optionValue->user_field_id,$optionValue->user_field_option_id);

                                    $value->options[$k]->options[$optionKey]->options = $options;
                                }  

                            }
                        }
                    }// End Check Fields has option

                    $steps[] = $value;
                }
                return response()->json(['success'=>$this->successStatus,'data' =>$steps], $this->successStatus); 
            }
            else
            {
                $message = "The field does not exist";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }               
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Get list of field values
     *
     */
    public function getFieldValues($fieldId)
    {
        try
        {
            $user = $this->user;
            $checkUser = User::where('user_id', $user->user_id)->first();
            
            $roleFields = DB::table('user_fields')
                                      ->where("user_field_id","=", $fieldId)
                                      ->first();
            if(!empty($checkUser))
            {
                if($roleFields->type !='text' && $roleFields->type !='email' && $roleFields->type !='password')
                {
                                
                    $roleFields->options = $this->getUserFieldOptionParent($roleFields->user_field_id);

                    if(!empty($roleFields->options)){

                        foreach ($roleFields->options as $k => $oneDepth) 
                        {

                            $roleFields->options[$k]->option = $this->translate('messages.'.$oneDepth->option,$oneDepth->option);

                            $data = $this->getUserFieldOptionsNoneParent($roleFields->user_field_id,$oneDepth->user_field_option_id);

                            $roleFields->options[$k]->options = $data;

                            
                            foreach ($roleFields->options[$k]->options as $optionKey => $optionValue) 
                            {
                                $options = $this->getUserFieldOptionsNoneParent($optionValue->user_field_id,$optionValue->user_field_option_id);

                                $roleFields->options[$k]->options[$optionKey]->options = $options;
                            }  
                                
                        }
                    }
                    
                    return response()->json(['success' => $this->successStatus,
                                     'data' => $roleFields
                                    ], $this->successStatus);
                }
                else
                {
                    $message = "Undefined field type";
                    return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                }
            }
            else
            {
                $message = "The field does not exist";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }               
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /* Get All Fields Option who are child
     * @params $user_field_id 
    */
    public function getUserFieldOptionParent($fieldId){

        $fieldOptionData = [];
        
        if($fieldId > 0){
            $fieldOptionData = DB::table('user_field_options')
                    ->where('user_field_id','=',$fieldId)
                    ->where('parent','=',0)
                    ->get();

            foreach ($fieldOptionData as $key => $option) {
                $fieldOptionData[$key]->option = $this->translate('messages.'.$option->option,$option->option);
            }
        }
        
        return $fieldOptionData;    
        
    }

    /*
     * Get All Fields Option who are child
     * @params $user_field_id and $user_field_option_id
     */
    public function getUserFieldOptionsNoneParent($fieldId, $parentId){

        $fieldOptionData = [];
        
        if($fieldId > 0 && $parentId > 0){
            $fieldOptionData = DB::table('user_field_options')
                ->where('user_field_id','=',$fieldId)
                ->where('parent','=',$parentId)
                ->get();                                

            foreach ($fieldOptionData as $key => $option) {
                $fieldOptionData[$key]->option = $this->translate('messages.'.$option->option,$option->option);
            }
        }
        
        return $fieldOptionData;    
        
    }

    /*
     * Save privacy data
     * @Params $request
     */
    public function savePrivacy(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'allow_message_from' => 'required', 
                'who_can_view_age' => 'required',
                'who_can_view_profile' => 'required',
                'who_can_connect' => 'required',
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $checkUser = User::where('user_id', $user->user_id)->first();
            $checkPrivacyDataExist = UserPrivacy::where('user_id', $user->user_id)->first();

            if(!empty($checkUser))
            {
                if(empty($checkPrivacyDataExist))
                {
                    $privacy = new UserPrivacy;
                    $privacy->user_id = $user->user_id;
                    $privacy->allow_message_from = $request->allow_message_from;
                    $privacy->who_can_view_age = $request->who_can_view_age;
                    $privacy->who_can_view_profile = $request->who_can_view_profile;
                    $privacy->who_can_connect = $request->who_can_connect;
                    $privacy->save();
                }
                else
                {
                    UserPrivacy::where('user_id', $user->user_id)->update(['allow_message_from' => $request->allow_message_from, 'who_can_view_age' => $request->who_can_view_age, 'who_can_view_profile' => $request->who_can_view_profile, 'who_can_connect' => $request->who_can_connect]);

                }             
                    $message = "Privacy settings has been saved";
                    return response()->json(['success' => $this->successStatus,
                                         'message' => $this->translate('messages.'.$message,$message),
                                        ], $this->successStatus);
            }
            else
            {
                $message = "Invalid user";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }


    
    /*
     * Check Role Permission
     * @Params $userRole, $FollowingUserRole
     */

    public function checkRolePermission($userRole, $FollowingUserRole)
    {
        $status = [];
        $permission = ConnectFollowPermission::where("role_id", $userRole)->where('permission_type', '2')->first();

        if(!empty($permission))
        {
            $permissionRole = MapPermissionRole::where("connect_follow_permission_id", $permission->connect_follow_permission_id)->get();
            $checkRoleWisePermission = $permissionRole->pluck('role_id')->toArray();

            if(in_array($FollowingUserRole, $checkRoleWisePermission))
            {
                $status = [$this->translate('messages.'."Success","Success"), 0];
            }
            else
            {
                $status = [$this->translate('messages.'."Failed","Failed"), 1];
            }
            
        }
        else
        {
            $status = [$this->translate('messages.'."You are not authorized to follow this user","You are not authorized to follow this user"), 2];
        }
        
        return $status;
    }

   
}
