<?php

namespace Modules\User\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Http\Controllers\CoreController;
use Modules\User\Entities\Role;
use Modules\User\Entities\BlockList;
use Modules\Activity\Entities\ActivityAction;
use Modules\User\Entities\FeaturedListing;
use Modules\User\Entities\UserProfileProgress;
use Modules\Activity\Entities\Connection;
use Modules\Activity\Entities\Follower;
use Modules\User\Entities\UserSelectedHub;
use Modules\User\Entities\UserTempHub;
use Modules\User\Entities\Cousin;
use Illuminate\Support\Facades\Auth; 
use Modules\User\Entities\User; 
use Modules\Marketplace\Entities\MarketplaceStore;
use Modules\User\Entities\Certificate;
use Modules\Activity\Entities\ConnectFollowPermission;
use Modules\Activity\Entities\MapPermissionRole;
use Modules\User\Entities\Country;
use App\Http\Traits\ProfileStatusTrait;
use Validator;
use App\Image;
use DB;
use Cache;
use App\Events\Welcome;
use App\Http\Traits\UploadImageTrait;

class UserController extends CoreController
{
    use UploadImageTrait;
    use ProfileStatusTrait;
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
     * User Info
     */
    public function userinfo(){
        
        return response()->json(['success' => $this->successStatus,
                                 'user' => $this->user->only($this->userFieldsArray),
                                ], $this->successStatus);  
    }


    /*
     * Get Cousins
     * 
     */
    public function getCousins()
    {
        try
        {
            $user = $this->user;

            $cousins = Cousin::with('image_id')->where('status', '1')->get();
            if(count($cousins) > 0)
            {
                foreach($cousins as $key => $cousin)
                {
                    $cousins[$key]->name = $this->translate('messages.'.$cousin->name,$cousin->name);
                }

                return response()->json(['success' => $this->successStatus,
                                        'count' =>  count($cousins),
                                        'data' => $cousins,
                                    ], $this->successStatus);
            }
            else
            {
                $message = "No cousins found";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /* 
     * User Settings
     */
    public function userSettings(){
        try{
                $loggedInUser = $this->user;
                $featuredinfo = [];
                $userDetails = $loggedInUser->only(['name', 'email','display_name','locale']);

                $userFieldInfo = [];
                $featuredListing = [];

                foreach($userDetails as $key => $user){

                    $userFieldInfo[$key] = ["title" => $this->translate("messages.".$key,$key),"value"=>$user];
                }
                if($loggedInUser->role_id == 3 || $loggedInUser->role_id == 6) //producers & importers
                {
                    $featuredListing = FeaturedListing::with('image')->where('user_id', $loggedInUser->user_id)->where('listing_type', '1')->orderBy('id','DESC')->get(); //products
                    foreach ($featuredListing as $k => $value) 
                    {
                        $featuredListing[$k]->title
                         = $this->translate('messages.'.$value->title
                        ,$value->title
                        );
                        $featuredListing[$k]->description
                         = $this->translate('messages.'.$value->description
                        ,$value->description
                        );
                        $featuredListing[$k]->anonymous
                         = $this->translate('messages.'.$value->anonymous
                        ,$value->anonymous
                        );
                    }
                }
                elseif($loggedInUser->role_id == 9) //restaurant
                {
                    $featuredListing = FeaturedListing::with('image')->where('user_id', $loggedInUser->user_id)->where('listing_type', '2')->orderBy('id','DESC')->get(); //recipies
                    foreach ($featuredListing as $k => $value) 
                    {
                        $featuredListing[$k]->title
                         = $this->translate('messages.'.$value->title
                        ,$value->title
                        );
                        $featuredListing[$k]->description
                         = $this->translate('messages.'.$value->description
                        ,$value->description
                        );
                        $featuredListing[$k]->anonymous
                         = $this->translate('messages.'.$value->anonymous
                        ,$value->anonymous
                        );
                    }
                }
                elseif($loggedInUser->role_id == 7) //voe
                {
                    $featuredListing = FeaturedListing::with('image')->where('user_id', $loggedInUser->user_id)->where('listing_type', '3')->orderBy('id','DESC')->get(); //blogs
                    foreach ($featuredListing as $k => $value) 
                    {
                        $featuredListing[$k]->title
                         = $this->translate('messages.'.$value->title
                        ,$value->title
                        );
                        $featuredListing[$k]->description
                         = $this->translate('messages.'.$value->description
                        ,$value->description
                        );
                        $featuredListing[$k]->anonymous
                         = $this->translate('messages.'.$value->anonymous
                        ,$value->anonymous
                        );
                    }
                }
                elseif($loggedInUser->role_id == 8) //travel agencies
                {
                    $featuredListing = FeaturedListing::with('image')->where('user_id', $loggedInUser->user_id)->where('listing_type', '4')->orderBy('id','DESC')->get(); //blogs
                    foreach ($featuredListing as $k => $value) 
                    {
                        $featuredListing[$k]->title
                         = $this->translate('messages.'.$value->title
                        ,$value->title
                        );
                        $featuredListing[$k]->description
                         = $this->translate('messages.'.$value->description
                        ,$value->description
                        );
                        $featuredListing[$k]->anonymous
                         = $this->translate('messages.'.$value->anonymous
                        ,$value->anonymous
                        );
                    }
                }
                
                $userDetails['featured_listing'] = $featuredListing;
                    
                return response()->json(['success' => $this->successStatus,
                                 'data' => [$userDetails]
                                ], $this->successStatus);

        }catch(\Exception $e){
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /* 
     * User Settings
     * @params $request
     */

    /*public function updateUserSettings(Request $request){
        try{
                $input = $request->all();

                $validator = Validator::make($input, [ 
                    'name' => 'required|min:3|unique:users,name,'.$this->user->user_id.',user_id', 
                ]);

                if ($validator->fails()) { 
                    return response()->json(['errors'=>$validator->errors(),'success' => $this->validationStatus], $this->validationStatus);
                }
                
                $user = User::where('user_id','=',$this->user->user_id)->update($input);

                return response()->json(['success' => $this->successStatus,
                                 'data' => $user,
                                ], $this->successStatus);
                                  
        }catch(\Exception $e){
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }*/

    public function updateUserSettings(Request $request)
    {
        try
        {
            $loggedInUser = $this->user;
            if($loggedInUser->role_id == 7 || $loggedInUser->role_id == 10)
            {
                $validator = Validator::make($request->all(), [ 
                'name' => 'required|unique:users,name,'.$loggedInUser->user_id.',user_id',
                'locale' => 'required',
                ],
                [
                    'name.unique' => 'The username has already been taken'
                ]);
            }
            else
            {
                $validator = Validator::make($request->all(), [ 
                'name' => 'required|unique:users,name,'.$loggedInUser->user_id.',user_id',
                'website' => 'required|max:190',
                'locale' => 'required',
                ],
                [
                    'name.unique' => 'The username has already been taken'
                ]);
            }
            
            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }
            
            $user = User::where('user_id','=',$this->user->user_id)->first();
            $user->website = $request->website;
            $user->name = $request->name;
            if(!empty($request->first_name))
            {
                $user->first_name = $request->first_name;
            }
            if(!empty($request->last_name))
            {
                $user->last_name = $request->last_name;
            }
            //$user->display_name = $request->display_name;
            $user->locale = $request->locale;
            $user->save();

            $userData = User::select('*','name as username')->with('avatar_id','roles')->where('user_id','=',$this->user->user_id)->first();
            $token =  $userData->createToken('alysei')->accessToken; 
            
            return response()->json(['success' => $this->successStatus,
                             'data' => $userData,
                             'token' => $token
                            ], $this->successStatus);
                                  
        }catch(\Exception $e){
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Validate Data
     * @Params $requestedfields
     */

    public function validateData($requestedFields){
        $rules = [];
        foreach ($requestedFields as $key => $field) {
            //return $key;
            if($key == 'name'){

                $rules[$key] = 'required|min:3|unique:users,name,'.$this->user->user_id.',user_id';

            }
        }

        return $rules;
    }

    /* 
     * Contact Details
     * @params $request
     */

    public function updateContactDetails(Request $request){
        try{
                $input = $request->all();

                $validator = Validator::make($input, [ 
                    'address' => 'required', 
                    //'lattitude' => 'required|max:255', 
                    //'longitude' => 'required|max:255', 
                ]);

                if ($validator->fails()) { 
                    return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
                }
                
                $user = User::where('user_id','=',$this->user->user_id)->update($input);
                $userDetail = User::where('user_id','=',$this->user->user_id)->first();
                $myStore = MarketplaceStore::where('user_id', $this->user->user_id)->first();
                if(!empty($myStore))
                {
                    if(!empty($request->phone))
                    {
                        $myStore->phone = $request->phone;
                        $myStore->save();
                    }
                    if(!empty($request->address))
                    {
                        $myStore->location = $request->address;
                        $myStore->lattitude = $request->lattitude;
                        $myStore->longitude = $request->longitude;
                        $myStore->save();
                    }
                    if(!empty($request->website))
                    {
                        $myStore->website = $request->website;
                        $myStore->save();
                    }
                    $myStore->name = $userDetail->company_name;
                    $myStore->description = $userDetail->about;
                    $myStore->store_region = $userDetail->state;
                    $myStore->save();
                }

                return response()->json(['success' => $this->successStatus,
                                 'data' => $user,
                                ], $this->successStatus);
                                  
        }catch(\Exception $e){
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }



    /* 
     * Get user submitted fields
     * @params 
     */

    public function getUserSubmitedFields($visitorId='')
    {
        try
        { 
            $role_id = $this->user->role_id;
            $user_id = $this->user->user_id;

            $response_time = (microtime(true) - LARAVEL_START)*1000;
                $steps = Cache::get('profile_update_form');

                if($role_id && (env("cache") == false) || $steps==null){
                    $steps = [];
                    $roleFields = DB::table('user_field_map_roles')
                                      ->join('user_fields', 'user_fields.user_field_id', '=', 'user_field_map_roles.user_field_id')
                                      ->where("role_id","=",$role_id)
                                      ->where("require_update","=",'true')
                                      ->where("conditional","=",'no')
                                      ->orderBy("edit_profile_field_order","asc")
                                      ->get();


                    if($roleFields){
                        foreach ($roleFields as $key => $value) {
                            $data = [];

                            $fieldValue = DB::table('user_field_values')
                                    ->where('user_id', $user_id)
                                    ->where('user_field_id', $value->user_field_id)
                                    ->first();
                            
                            $roleFields[$key]->title = $this->translate('messages.'.$value->title,$value->title);
                            if(!empty($fieldValue) && $value->type == 'radio' && ($fieldValue->value == 'Yes' ||  $fieldValue->value == '1'))
                            {
                                $roleFields[$key]->is_selected = true;
                            }
                            elseif(!empty($fieldValue) && $value->type == 'text')
                            {
                                $roleFields[$key]->is_selected = $fieldValue->value;
                            }
                            else
                            {
                                if($value->type == 'radio')
                                {
                                    $roleFields[$key]->is_selected = false;
                                }
                                else
                                {
                                    $roleFields[$key]->is_selected = '';
                                }
                                
                            }
                            
                            
                            //Check Fields has option
                            if($value->type !='text' && $value->type !='email' && $value->type !='password'){
                                
                                $value->options = $this->getUserFieldOptionParent($value->user_field_id);

                                if(!empty($value->options)){

                                    foreach ($value->options as $k => $oneDepth) {

                                            $grandParent = '';

                                            if(!empty($fieldValue)){
                                                $grandParent = $this->getUserFieldOptionGrandParent($fieldValue->value);
                                            }

                                            $fieldValuessParents = DB::table('user_field_values')
                                                ->where('user_id', $user_id)
                                                ->where('user_field_id', $oneDepth->user_field_id)
                                                ->where('value', $oneDepth->user_field_option_id)
                                                ->first();

                                            if(!empty($fieldValuessParents) )
                                            {
                                                $value->options[$k]->is_selected = true;

                                            }else{
                                                $value->options[$k]->is_selected = false;
                                            }

                                            $value->options[$k]->option = $this->translate('messages.'.$oneDepth->option,$oneDepth->option);

                                            //Check Option has any Field Id
                                            $checkRow = DB::table('user_field_maps')->where('user_field_id','=',$value->user_field_id)->where('role_id','=',$role_id)->first();

                                            if($checkRow){
                                                $value->parentId = $checkRow->option_id;
                                            }
                                                $fieldValuesParent = DB::table('user_field_values')
                                                ->where('user_id', $user_id)
                                                ->where('user_field_id', $value->user_field_id)
                                                ->get();

                                                $userFieldValuesParent = $fieldValuesParent->pluck('value')->toArray();

                                            
                                            $data = $this->getUserFieldOptionsNoneParent($value->user_field_id,$oneDepth->user_field_option_id,$userFieldValuesParent);

                                            $value->options[$k]->options = $data;

                                            
                                            foreach ($value->options[$k]->options as $optionKey => $optionValue) {

                                                $fieldValues = DB::table('user_field_values')
                                                ->where('user_id', $user_id)
                                                ->where('user_field_id', $optionValue->user_field_id)
                                                ->get();

                                                $userFieldValues = $fieldValues->pluck('value')->toArray();


                                                $options = $this->getUserFieldOptionsNoneParent($optionValue->user_field_id, $optionValue->user_field_option_id, $userFieldValues);

                                                $value->options[$k]->options[$optionKey]->options = $options;
                                                
                                            }  
                                            

                                    }
                                }
                            }// End Check Fields has option

                            $steps[] = $value;
                        }
                    }

                    Cache::forever('profile_update_form', $steps);                      
            }

            /*****Featured Listings****/

            $userFieldInfo = [];

            $fieldsTypes = $this->getFeaturedListingTypes($this->user->role_id, 'featured');
            
            $products = [];
            
            foreach($fieldsTypes as $fieldsTypesKey => $fieldsTypesValue){
                if(!empty($visitorId))
                {
                    $featuredListing = FeaturedListing::with('image')
                                    ->where('user_id', $visitorId)
                                    ->where('featured_listing_type_id', $fieldsTypesValue->featured_listing_type_id)
                                    ->orderBy('featured_listing_id','DESC')->get();
                }
                else
                {
                    $featuredListing = FeaturedListing::with('image')
                                    ->where('user_id', $this->user->user_id)
                                    ->where('featured_listing_type_id', $fieldsTypesValue->featured_listing_type_id)
                                    ->orderBy('featured_listing_id','DESC')->get();
                }
                 

                $products[] = ["title" => $fieldsTypesValue->title,"slug" => $fieldsTypesValue->slug,"products" => $featuredListing];
               
            }

            //Get Featured Listing Fields

            //Get Featured Type
            $featuredTypes = $this->getFeaturedListingFieldsByRoleId($this->user->role_id, 'featured');
            $fieldsData = [];
            foreach ($featuredTypes as $key => $value) {

                $value->title = $this->translate('messages.'.$value->title,$value->title);

                $value->options = $this->getFeaturedListingFieldOptionParent($value->featured_listing_field_id);

                if(!empty($value->options)){
                    foreach ($value->options as $k => $oneDepth) {

                            $value->options[$k]->option = $this->translate('messages.'.$oneDepth->option,$oneDepth->option);
                        }
                }

                $fieldsData[$value->featured_listing_type_slug][] = $value;
            }

            foreach($fieldsData as $fieldsDataKey => $fieldsDataValue){
                

                $key = array_search($fieldsDataKey, array_column($products, 'slug'));

                $products[$key]['fields'] = $fieldsDataValue;
            }
            
            //END
            $userDataImage = User::select('avatar_id','cover_id')->with('avatar_id','cover_id')->where('user_id', $user_id)->first();
            $data = ['step_1'=>$steps,'products' => $products,'profile_data' => $userDataImage];

            
                return response()->json(['success'=>$this->successStatus,'data' => $data,'response_time'=>$response_time], $this->successStatus); 
            

            
        }      
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Get Featured Type Using Role Id
     * @params $roleId
     */
    public function getFeaturedListingTypes($roleId, $featuredType){
        
        $featuredTypes = DB::table("featured_listing_types as flt")
        ->join("featured_listing_type_role_maps as fltrm", 'fltrm.featured_listing_type_id', '=', 'flt.featured_listing_type_id')

        ->where("fltrm.role_id","=",$roleId)
        ->where("flt.position","=",$featuredType)
        ->get();
        
        return $featuredTypes;
    }

    /*
     * Get Featured Listing Fields Using Role Id
     * @params $roleId
     */
    public function getFeaturedListingFieldsByRoleId($roleId, $featuredType){
        
        $featuredTypes = DB::table("featured_listing_types as flt")
        ->select("flt.title as featured_listing_type_title","flt.slug as featured_listing_type_slug","flfrm.*","fltrm.*","flf.*")
        ->join("featured_listing_type_role_maps as fltrm", 'fltrm.featured_listing_type_id', '=', 'flt.featured_listing_type_id')

        ->join("featured_listing_field_role_maps as flfrm",function ($join) {
            $join->on('flfrm.featured_listing_type_id', '=', 'fltrm.featured_listing_type_id');
            $join->on('flfrm.featured_listing_type_id','=','flt.featured_listing_type_id');
        }) 

        ->join("featured_listing_fields as flf", 'flf.featured_listing_field_id', '=', 'flfrm.featured_listing_field_id')

        ->where("fltrm.role_id","=",$roleId)
        ->where("flfrm.role_id","=",$roleId)
        ->where("flt.position","=", $featuredType)
        ->get();
        

        return $featuredTypes;
    }

    /*
     * Get All Fields Option who are child
     * @params $featured_listing_field_id 
    */
    public function getFeaturedListingFieldOptionParent($fieldId){

        $fieldOptionData = [];
        
        if($fieldId > 0){
            $fieldOptionData = DB::table('featured_listing_field_options')
                    ->where('featured_listing_field_id','=',$fieldId)
                    ->where('parent','=',0)
                    ->get();

            foreach ($fieldOptionData as $key => $option) {
                $fieldOptionData[$key]->option = $this->translate('messages.'.$option->option,$option->option);
            }
        }
        
        return $fieldOptionData;    
        
    }

    /*
     * Update user profile 
     * @params $request 
     */
    public function updateUserProfile(Request $request)
    {
        try
        {
            $role_id = $this->user->role_id;
            $user_id = $this->user->user_id;
            
            $input = $request->all();
            $rules = [];
            

            $roleFields = $this->checkFieldsByRoleId($role_id);

            if(count($roleFields) == 0){
                return response()->json(['success'=>$this->validationStatus,'errors' =>'Sorry,There are no fields for current role_id'], $this->validationStatus);
            }else{

                $rules = $this->makeValidationRules($roleFields);
                $inputData = $this->segregateInputData($input,$roleFields);
            }

            if(!empty($rules) || !empty($inputData)){
                
                $validator = Validator::make($inputData, $rules);

                if ($validator->fails()) { 

                    return response()->json(['success'=>$this->validationStatus,'errors'=>$validator->errors()->first()], $this->validationStatus);
                }

                if(array_key_exists('about',$inputData))
                {
                    User::where('user_id', $user_id)->update(['about' => $inputData['about']]);
                }

                if(array_key_exists('company_name',$inputData))
                {
                    User::where('user_id', $user_id)->update(['company_name' => $inputData['company_name']]);
                }

                    
                        foreach ($input as $key => $value) {

                            $this->deleteValueIfExist($user_id, $key);

                            $checkMultipleOptions = explode(',', $value);

                            if(count($checkMultipleOptions) == 1)
                            {
                                $data = [];
                                if(!empty($key))
                                {
                                    $data['user_field_id'] = $key;
                                    $data['user_id'] = $user_id;
                                    $data['value'] = $value; 
                                    DB::table('user_field_values')->insert($data);
                                }
                                
                            }else{

                                foreach($checkMultipleOptions as $option){
                                    $data = [];
                                    if(!empty($key))
                                    {
                                        $data['user_field_id'] = $key;
                                        $data['user_id'] = $user_id;
                                        $data['value'] = $option;
                                        DB::table('user_field_values')->insert($data);
                                    }
                                    
                                }
                            }
                           
                        }

                        

                $userProfile = User::where('user_id', $user_id)->first();
                if(!empty($request->file('avatar_id')))
                {
                    $this->deleteAttachment($userProfile->avatar_id);
                    $userProfile->avatar_id = $this->uploadImage($request->file('avatar_id'));
                }
                if(!empty($request->file('cover_id')))
                {
                    $this->deleteAttachment($userProfile->cover_id);
                    $userProfile->cover_id = $this->uploadImage($request->file('cover_id'));
                }
                
                $userProfile->save();
                $userDataImage = User::select('avatar_id','cover_id')->with('avatar_id','cover_id')->where('user_id', $user_id)->first();

                            return response()->json(['success' => $this->successStatus,
                                 'message' => $this->translate('messages.'."Profile updated","Profile updated"),
                                 'data' => $userDataImage
                                ], $this->successStatus);
                 
            }
        }catch(\Exception $e){
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()], $this->exceptionStatus); 
        }
        
    }


    /* 
     * Update Profile/cover image
     * @params $request
     */
    public function updateProfileCoverImage(Request $request)
    {
        try
        {
            //$input = $request->all();
            $user = $this->user;

            $userProfile = User::where('user_id', $user->user_id)->first();
            if(!empty($request->file('avatar_id')))
            {
                $this->deleteAttachment($userProfile->avatar_id);
                $userProfile->avatar_id = $this->uploadImage($request->file('avatar_id'));
            }
            if(!empty($request->file('cover_id')))
            {
                $this->deleteAttachment($userProfile->cover_id);
                $userProfile->cover_id = $this->uploadImage($request->file('cover_id'));
            }
            $userProfile->save();

            $message = "Updated successfully";
            return response()->json(['success' => $this->successStatus,
                                     'message' => $this->translate('messages.'.$message,$message),
                                    ], $this->successStatus);
            
            

            /*$validator = Validator::make($input, [ 
                'image_type' => 'required', 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $userProfile = User::where('user_id', $this->user->user_id)->first();
            if($request->image_type == 1)
            {
                $validatorAvatar = Validator::make($input, [ 
                    'avatar_id' => 'required', 
                ]);

                if ($validatorAvatar->fails()) { 
                    return response()->json(['errors'=>$validatorAvatar->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
                }
                if(!empty($request->file('avatar_id')))
                {
                    $userProfile->avatar_id = $this->uploadImage($request->file('avatar_id'));
                    $userProfile->save();
                }

                $message = "Profile image updated successfully";
                return response()->json(['success' => $this->successStatus,
                             'message' => $this->translate('messages.'.$message,$message),
                            ], $this->successStatus);
            }
            elseif($request->image_type == 2)
            {
                $validatorCover = Validator::make($input, [ 
                    'cover_id' => 'required', 
                ]);

                if ($validatorCover->fails()) { 
                    return response()->json(['errors'=>$validatorCover->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
                }
                if(!empty($request->file('cover_id')))
                {
                    $userProfile->cover_id = $this->uploadImage($request->file('cover_id'));
                    $userProfile->save();
                }
                
                $message = "Cover image updated successfully";
                return response()->json(['success' => $this->successStatus,
                             'message' => $this->translate('messages.'.$message,$message),
                            ], $this->successStatus);
            }
            else
            {
                $message = "Image type is not valid";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus); 
            }*/
                         
        }
        catch(\Exception $e){
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /* 
     * Remove profile cover image
     * @params $request
     */
    public function removeProfileCoverImage(Request $request)
    {
        try
        {
            $input = $request->all();

            $validator = Validator::make($input, [ 
                'image_type' => 'required', 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }
            $userData = User::where('user_id','=',$this->user->user_id)->first();
            if($request->image_type == 1) //removing profile image
            {
                $userData->avatar_id = null;
                $userData->save();

                $message = "Profile image removed successfully";
                return response()->json(['success' => $this->successStatus,
                             'message' => $this->translate('messages.'.$message,$message),
                            ], $this->successStatus);
            }
            elseif($request->image_type == 2) //removing cover image
            {
                $userData->cover_id = null;
                $userData->save();

                $message = "Cover image removed successfully";
                return response()->json(['success' => $this->successStatus,
                             'message' => $this->translate('messages.'.$message,$message),
                            ], $this->successStatus);
            }
            else
            {
                $message = "Image type is not valid";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus); 
            }
                         
        }
        catch(\Exception $e){
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Get User Certificates
     * @params $request 
     */
    public function getUserCertificates()
    {
        try
        {
            $loggedInUser = $this->user;

            $userData = User::select('user_id','fda_no','vat_no')->where('user_id', $loggedInUser->user_id)->first();

            $fieldOptions = DB::table('user_field_options')->where('parent','=',0)->where('head','=',0)->get();

            $fieldOptions = $fieldOptions->pluck('user_field_option_id');

            $userFieldValues = DB::table('user_field_values')->where('user_id', $loggedInUser->user_id)->where('user_field_id', 2)->whereIn('value', $fieldOptions)->get();

            $userFieldValues = $userFieldValues->pluck('value');

            $fieldOptions = DB::table('user_field_options')->whereIn('user_field_option_id',$userFieldValues)->get();



            foreach($fieldOptions as $key => $fieldOption)
            {
                $userCertificates = Certificate::with('photo_of_label','fce_sid_certification','phytosanitary_certificate','packaging_for_usa','food_safety_plan','animal_helath_asl_certificate')->where('user_id', $loggedInUser->user_id)->where('user_field_option_id', $fieldOption->user_field_option_id)->first();
                //$userCertificates = Certificate::where('user_id', $loggedInUser->user_id)->where('user_field_option_id', $fieldOption->user_field_option_id)->first();

               
                    //$fieldOptions[$key]->certificates = $userCertificates;
                    
                $fieldOptions[$key]->photo_of_label = (!empty($userCertificates->photo_of_label))?($this->getCertificatesById($userCertificates->photo_of_label)):"";

                $fieldOptions[$key]->fce_sid_certification = (!empty($userCertificates->fce_sid_certification))?($this->getCertificatesById($userCertificates->fce_sid_certification)):"";

                $fieldOptions[$key]->phytosanitary_certificate = (!empty($userCertificates->phytosanitary_certificate))?($this->getCertificatesById($userCertificates->phytosanitary_certificate)):"";

                $fieldOptions[$key]->packaging_for_usa = (!empty($userCertificates->packaging_for_usa))?($this->getCertificatesById($userCertificates->packaging_for_usa)):"";

                $fieldOptions[$key]->food_safety_plan = (!empty($userCertificates->food_safety_plan))?($this->getCertificatesById($userCertificates->food_safety_plan)):"";

                $fieldOptions[$key]->animal_helath_asl_certificate = (!empty($userCertificates->animal_helath_asl_certificate))?($this->getCertificatesById($userCertificates->animal_helath_asl_certificate)):"";
                
            } 

            $data = ['user_data' => $userData, 'data_certificates' => $fieldOptions];
            return response()->json(['success' => $this->successStatus,
                            'data' => $data
                            ], $this->successStatus);
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()], $this->exceptionStatus); 
        }
        
    }


    /*
     * Upload User Certificates for Producers
     * @params $request 
     */
    public function updateUserCertificates(Request $request)
    {
        try
        {
            $loggedInUser = $this->user;

            $validator = Validator::make($request->all(), [ 
                'vat_no' => 'required',
                //'fda_no' => 'required',
                //'user_field_option_id' => 'required'                
            ]);

        
            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $userDetail = User::where('user_id', $loggedInUser->user_id)->first();

            $userDetail->vat_no = $request->vat_no;
            if(!empty($request->fda_no))
            {
                $userDetail->fda_no = $request->fda_no;
            }
            $userDetail->save();

            $userData = User::where('user_id', $loggedInUser->user_id)->first();

            if(!empty($request->user_field_option_id))
            {
                $checkExistingCertificate = Certificate::where('user_field_option_id', $request->user_field_option_id)->where('user_id', $loggedInUser->user_id)->first();
            
                if(!empty($checkExistingCertificate))
                {
                    if(!empty($request->file('photo_of_label')))
                    {
                        $checkExistingCertificate->photo_of_label = $this->uploadImage($request->file('photo_of_label'));
                        $checkExistingCertificate->save();
                    }
                    if(!empty($request->file('fce_sid_certification')))
                    {
                        $checkExistingCertificate->fce_sid_certification = $this->uploadImage($request->file('fce_sid_certification'));
                        $checkExistingCertificate->save();
                    }
                    if(!empty($request->file('phytosanitary_certificate')))
                    {
                        $checkExistingCertificate->phytosanitary_certificate = $this->uploadImage($request->file('phytosanitary_certificate'));
                        $checkExistingCertificate->save();
                    }
                    if(!empty($request->file('packaging_for_usa')))
                    {
                        $checkExistingCertificate->packaging_for_usa = $this->uploadImage($request->file('packaging_for_usa'));
                        $checkExistingCertificate->save();
                    }
                    if(!empty($request->file('food_safety_plan')))
                    {
                        $checkExistingCertificate->food_safety_plan = $this->uploadImage($request->file('food_safety_plan'));
                        $checkExistingCertificate->save();
                    }
                    if(!empty($request->file('animal_helath_asl_certificate')))
                    {
                        $checkExistingCertificate->animal_helath_asl_certificate = $this->uploadImage($request->file('animal_helath_asl_certificate'));
                        $checkExistingCertificate->save();
                    }
                }
                else
                {
                    $userCertificate = new Certificate;
                    $userCertificate->user_id = $loggedInUser->user_id;
                    $userCertificate->user_field_option_id = $request->user_field_option_id;
                   
                    if(!empty($request->file('photo_of_label')))
                    {
                        $userCertificate->photo_of_label = $this->uploadImage($request->file('photo_of_label'));
                        $userCertificate->save();
                    }
                    if(!empty($request->file('fce_sid_certification')))
                    {
                        $userCertificate->fce_sid_certification = $this->uploadImage($request->file('fce_sid_certification'));
                        $userCertificate->save();
                    }
                    if(!empty($request->file('phytosanitary_certificate')))
                    {
                        $userCertificate->phytosanitary_certificate = $this->uploadImage($request->file('phytosanitary_certificate'));
                        $userCertificate->save();
                    }
                    if(!empty($request->file('packaging_for_usa')))
                    {
                        $userCertificate->packaging_for_usa = $this->uploadImage($request->file('packaging_for_usa'));
                        $userCertificate->save();
                    }
                    if(!empty($request->file('food_safety_plan')))
                    {
                        $userCertificate->food_safety_plan = $this->uploadImage($request->file('food_safety_plan'));
                        $userCertificate->save();
                    }
                    if(!empty($request->file('animal_helath_asl_certificate')))
                    {
                        $userCertificate->animal_helath_asl_certificate = $this->uploadImage($request->file('animal_helath_asl_certificate'));
                        $userCertificate->save();
                    }
                }
            }
            
            
            return response()->json(['success' => $this->successStatus,
                             'message' => $this->translate('messages.'.'Updated successfully','Updated successfully'),
                            ], $this->successStatus);
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()], $this->exceptionStatus); 
        }
        
    }

    /* 
     * User user fields saperately
     * @params $request
     */

    public function updateUserFieldValues(Request $request)
    {
        try
        {
            $input = $request->all();

            $validator = Validator::make($input, [ 
                'user_field_id' => 'required',
                'value' => 'required'
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }
            
            if($request->user_field_id == 36)
            $updatedData = User::where('user_id','=',$this->user->user_id)->update(['about' => $request->value]);


            $status = DB::table('user_field_values')
                ->where('user_id', $this->user->user_id)
                ->where('user_field_id', $request->user_field_id)->first();

            if(!empty($status))
            {
                $status = DB::table('user_field_values')
                ->where('user_id', $this->user->user_id)
                ->where('user_field_id', $request->user_field_id)
                ->update(['value' => $request->value]);
            }   
            else
            {
                DB::insert('insert into user_field_values (user_id, user_field_id, value) values (?, ?, ?)', [$this->user->user_id, $request->user_field_id, $request->value]);
            }
            $message = "updated successfully";                
            return response()->json(['success' => $this->successStatus,
                             'message' => $this->translate('messages.'.$message,$message),
                            ], $this->successStatus);
                                  
        }
        catch(\Exception $e){
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Get profile progress
     *
     */
    public function getProfileProgress()
    {
        try
        {
            $loggedInUser = $this->user;

            $userSelectedHub = UserSelectedHub::where('user_id', $loggedInUser->user_id)->count();  
            $userTempHub = UserTempHub::where('user_id', $loggedInUser->user_id)->count();
            $featuredListing = FeaturedListing::where('user_id', $loggedInUser->user_id)->count();

            $userData = User::select('user_id','about','phone','profile_percentage','role_id','company_name','restaurant_name','first_name','last_name','name as username','avatar_id','cover_id')->with('avatar_id','cover_id')->where('user_id', $loggedInUser->user_id)->first();
            $profilePercentage = $this->profileStatus($loggedInUser->user_id);
            
            $userFeaturedListing = ($featuredListing > 0) ? true : false;
            $userSelectedHub = ($userSelectedHub > 0 || $userTempHub > 0) ? true : false;
            $userAvatar = (!empty($userData->avatar_id)) ? true : false;
            $userCover = (!empty($userData->cover_id)) ? true : false;
            $userAbout = (!empty($userData->about)) ? true : false;
            $userContact = (!empty($userData->phone)) ? true : false;

            $fieldValue = DB::table('user_field_values')
                            ->where('user_id', $loggedInUser->user_id)
                            ->where('user_field_id', 36)
                            ->first();
            $aboutStatus = (!empty($fieldValue->value)) ? true : false; 
            $aboutLabel = "About";
            $aboutUserFieldId = 36;

            

            if($loggedInUser->role_id != 10)
            {
                if($loggedInUser->role_id == 3 || $loggedInUser->role_id == 4 || $loggedInUser->role_id == 5 || $loggedInUser->role_id == 6)
                {
                    $fieldValue = DB::table('user_field_values')
                                    ->where('user_id', $loggedInUser->user_id)
                                    ->where('user_field_id', 35)
                                    ->first();
                    $status = (!empty($fieldValue->value)) ? true : false;     
                    $about = "Our Products";
                    $userFieldId = 35;    
                }
                elseif($loggedInUser->role_id == 8)
                {
                    $fieldValue = DB::table('user_field_values')
                                    ->where('user_id', $loggedInUser->user_id)
                                    ->where('user_field_id', 38)
                                    ->first();
                    $status = (!empty($fieldValue->value)) ? true : false; 
                    $about = "Our Tours";
                    $userFieldId = 38;
                }
                else
                {
                    $fieldValue = DB::table('user_field_values')
                                    ->where('user_id', $loggedInUser->user_id)
                                    ->where('user_field_id', 37)
                                    ->first();
                    $status = (!empty($fieldValue->value)) ? true : false; 
                    $about = "Our Menu";
                    $userFieldId = 37;
                }
                $description = UserProfileProgress::where('role_id', $loggedInUser->role_id)->first();
                
                $aboutUs = ['title' => $about,'status' => $status, 'redirect_to' => 'edit_profile', 'user_field_id' => $userFieldId,'description' => $description->products];

                $fieldsType = $this->getFeaturedType($this->user->role_id);
                $dataFeaturedListing = ['title' => $fieldsType->title,'status' => $userFeaturedListing, 'redirect_to' => 'edit_listing', 'user_field_id' => 0,'description' => $description->featured];

                $data = ['user_id' => $loggedInUser->user_id,'role_id' => $loggedInUser->role_id, 'profile_percentage' => $profilePercentage, 'featured_listing_type_id' => $fieldsType->featured_listing_type_id, 'user_details' => $userData];

            }
            else
            {
                $data = ['user_id' => $loggedInUser->user_id,'role_id' => $loggedInUser->role_id, 'profile_percentage' => $profilePercentage, 'featured_listing_type_id' => 0, 'user_details' => $userData];
            }
            

            /*$dataProfileImage = ['title' => $this->translate('messages.'.'Profile Picture','Profile Picture'),'status' => $userAvatar, 'redirect_to' => 'edit_profile_image', 'user_field_id' => 0];
            $dataCoverImage = ['title' => $this->translate('messages.'.'Cover Image','Cover Image'),'status' => $userCover, 'redirect_to' => 'edit_cover_image', 'user_field_id' => 0];
            $dataAbout = ['title' => $this->translate('messages.'.$aboutLabel,$aboutLabel),'status' => $userAbout, 'redirect_to' => 'edit_profile', 'user_field_id' => $aboutUserFieldId];

            $dataHubSelection = ['title' => $this->translate('messages.'.'Hub Selection','Hub Selection'),'status' => $userSelectedHub, 'redirect_to' => 'edit_hub', 'user_field_id' => 0];
            $dataContactInfo = ['title' => $this->translate('messages.'.'Contact Info','Contact Info'),'status' => $userContact, 'redirect_to' => 'edit_contact', 'user_field_id' => 0];*/
            

            if($loggedInUser->role_id == 10)
            {
                $description = UserProfileProgress::where('role_id', $loggedInUser->role_id)->first();
                $dataProfileImage = ['title' => $this->translate('messages.'.'Profile Picture','Profile Picture'),'status' => $userAvatar, 'redirect_to' => 'edit_profile_image', 'user_field_id' => 0,'description' => $description->profile_img];
                $dataCoverImage = ['title' => $this->translate('messages.'.'Cover Image','Cover Image'),'status' => $userCover, 'redirect_to' => 'edit_cover_image', 'user_field_id' => 0,'description' => $description->cover_img];
                $dataAbout = ['title' => $this->translate('messages.'.$aboutLabel,$aboutLabel),'status' => $userAbout, 'redirect_to' => 'edit_profile', 'user_field_id' => $aboutUserFieldId,'description' => $description->about];

                $dataContactInfo = ['title' => $this->translate('messages.'.'Contact Info','Contact Info'),'status' => $userContact, 'redirect_to' => 'edit_contact', 'user_field_id' => 0,'description' => $description->contact];

                $dataProgress = [$dataProfileImage, $dataCoverImage, $dataAbout, $dataContactInfo];
            }
            elseif($loggedInUser->role_id == 7)
            {
                $description = UserProfileProgress::where('role_id', $loggedInUser->role_id)->first();
                $dataProfileImage = ['title' => $this->translate('messages.'.'Profile Picture','Profile Picture'),'status' => $userAvatar, 'redirect_to' => 'edit_profile_image', 'user_field_id' => 0,'description' => $description->profile_img];
                $dataCoverImage = ['title' => $this->translate('messages.'.'Cover Image','Cover Image'),'status' => $userCover, 'redirect_to' => 'edit_cover_image', 'user_field_id' => 0,'description' => $description->cover_img];
                $dataAbout = ['title' => $this->translate('messages.'.$aboutLabel,$aboutLabel),'status' => $userAbout, 'redirect_to' => 'edit_profile', 'user_field_id' => $aboutUserFieldId,'description' => $description->about];

                $dataHubSelection = ['title' => $this->translate('messages.'.'Hub Selection','Hub Selection'),'status' => $userSelectedHub, 'redirect_to' => 'edit_hub', 'user_field_id' => 0,'description' => $description->hub];
                $dataContactInfo = ['title' => $this->translate('messages.'.'Contact Info','Contact Info'),'status' => $userContact, 'redirect_to' => 'edit_contact', 'user_field_id' => 0,'description' => $description->contact];

                $dataProgress = [$dataHubSelection, $dataProfileImage, $dataCoverImage, $dataAbout, $dataContactInfo];
            }
            else
            {
                $description = UserProfileProgress::where('role_id', $loggedInUser->role_id)->first();
                $dataProfileImage = ['title' => $this->translate('messages.'.'Profile Picture','Profile Picture'),'status' => $userAvatar, 'redirect_to' => 'edit_profile_image', 'user_field_id' => 0,'description' => $description->profile_img];
                $dataCoverImage = ['title' => $this->translate('messages.'.'Cover Image','Cover Image'),'status' => $userCover, 'redirect_to' => 'edit_cover_image', 'user_field_id' => 0,'description' => $description->cover_img];
                $dataAbout = ['title' => $this->translate('messages.'.$aboutLabel,$aboutLabel),'status' => $userAbout, 'redirect_to' => 'edit_profile', 'user_field_id' => $aboutUserFieldId,'description' => $description->about];

                $dataHubSelection = ['title' => $this->translate('messages.'.'Hub Selection','Hub Selection'),'status' => $userSelectedHub, 'redirect_to' => 'edit_hub', 'user_field_id' => 0,'description' => $description->hub];
                $dataContactInfo = ['title' => $this->translate('messages.'.'Contact Info','Contact Info'),'status' => $userContact, 'redirect_to' => 'edit_contact', 'user_field_id' => 0,'description' => $description->contact];

                $dataProgress = [$dataHubSelection, $dataProfileImage, $dataCoverImage, $dataAbout, $aboutUs, $dataContactInfo, $dataFeaturedListing];
            }

            
                           
            return response()->json(['success' => $this->successStatus,
            					'data' => $data,
                                'data_progress' => $dataProgress
                                
                            ], $this->successStatus);


        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()], $this->exceptionStatus); 
        }
    }

    /*
     * Get Featured Tabs
     *
     */
    public function getFeaturedTabs()
    {
        try
        {
            $loggedInUser = $this->user;

            
            $userFieldInfo = [];

            $fieldsTypes = $this->getFeaturedListingTypes($this->user->role_id, 'tabs');
            
            $products = [];
            
            foreach($fieldsTypes as $fieldsTypesKey => $fieldsTypesValue){
                
                $featuredListing = FeaturedListing::with('image')
                                    ->where('user_id', $this->user->user_id)
                                    ->where('featured_listing_type_id', $fieldsTypesValue->featured_listing_type_id)
                                    ->orderBy('featured_listing_id','DESC')->get(); 

                $products[] = ["title" => $fieldsTypesValue->title,"slug" => $fieldsTypesValue->slug,"products" => $featuredListing];
                
            }

            //Get Featured Listing Fields

            //Get Featured Type
            $featuredTypes = $this->getFeaturedListingFieldsByRoleId($this->user->role_id, 'tabs');
            $fieldsData = [];
            foreach ($featuredTypes as $key => $value) {

                $value->title = $this->translate('messages.'.$value->title,$value->title);

                $value->options = $this->getFeaturedListingFieldOptionParent($value->featured_listing_field_id);

                if(!empty($value->options)){
                    foreach ($value->options as $k => $oneDepth) {

                            $value->options[$k]->option = $this->translate('messages.'.$oneDepth->option,$oneDepth->option);
                        }
                }

                $fieldsData[$value->featured_listing_type_slug][] = $value;
            }

            foreach($fieldsData as $fieldsDataKey => $fieldsDataValue){
                    

                $key = array_search($fieldsDataKey, array_column($products, 'slug'));

                $products[$key]['fields'] = $fieldsDataValue;
            }

                       
            return response()->json(['success' => $this->successStatus,
                                'data' => $products
                            ], $this->successStatus);


        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()], $this->exceptionStatus); 
        }
    }

    /*
     * Get Profile API in single
     *
     */
    public function getProfile()
    {
        try
        {
            $loggedInUser = $this->user;
            $profilePercentage = $this->profileStatus($loggedInUser->user_id);
            User::where('user_id', $loggedInUser->user_id)->update(['profile_percentage' => $profilePercentage]);

            if($loggedInUser->role_id == 3)
            {
                $color = "8EC9BB";
            }
            elseif($loggedInUser->role_id == 4 || $loggedInUser->role_id == 5 || $loggedInUser->role_id == 6) 
            {
                $color = "#A02C2D";
            }
            elseif($loggedInUser->role_id == 7) 
            {
                $color = "AB6393";
            }
            elseif($loggedInUser->role_id == 8) 
            {
                $color = "CA7E8D";
            }
            elseif($loggedInUser->role_id == 9) 
            {
                $color = "FDCF76";
            }
            elseif($loggedInUser->role_id == 10) 
            {
                $color = "9C8ADE";
            }


            $userData = User::select('user_id','profile_percentage','role_id','company_name','restaurant_name','first_name','last_name','name as username','avatar_id','cover_id')->with('avatar_id','cover_id')->where('user_id', $loggedInUser->user_id)->first();
            $userData->profile_color = $color;

            $userAbout = User::select('about')->where('user_id', $loggedInUser->user_id)->first();

            $postCount = ActivityAction::where('subject_id', $loggedInUser->user_id)->count();
            $connectionsCount = Connection::where('is_approved', '1')

            ->Where(function ($query) use ($loggedInUser) {
                $query->where('resource_id', $loggedInUser->user_id)
                  ->orWhere('user_id', $loggedInUser->user_id);
            })->count();
            $followerCount = Follower::where('follow_user_id', $loggedInUser->user_id)->count();

            $followingCount = Follower::where('user_id', $loggedInUser->user_id)->count();


            /*****Featured Listings****/

            $userFieldInfo = [];

            $fieldsTypes = $this->getFeaturedListingTypes($this->user->role_id, 'featured');
            
            $products = [];
            
            foreach($fieldsTypes as $fieldsTypesKey => $fieldsTypesValue){
                
                $featuredListing = FeaturedListing::with('image')
                                    ->where('user_id', $this->user->user_id)
                                    ->where('featured_listing_type_id', $fieldsTypesValue->featured_listing_type_id)
                                    ->orderBy('featured_listing_id','DESC')->get(); 

                $products[] = ["title" => $fieldsTypesValue->title,"slug" => $fieldsTypesValue->slug,"products" => $featuredListing];
                
            }

            //Get Featured Listing Fields

            //Get Featured Type
            $featuredTypes = $this->getFeaturedListingFieldsByRoleId($this->user->role_id, 'featured');
            $fieldsData = [];
            foreach ($featuredTypes as $key => $value) {

                $value->title = $this->translate('messages.'.$value->title,$value->title);

                $value->options = $this->getFeaturedListingFieldOptionParent($value->featured_listing_field_id);

                if(!empty($value->options)){
                    foreach ($value->options as $k => $oneDepth) {

                            $value->options[$k]->option = $this->translate('messages.'.$oneDepth->option,$oneDepth->option);
                        }
                }

                $fieldsData[$value->featured_listing_type_slug][] = $value;
            }

            foreach($fieldsData as $fieldsDataKey => $fieldsDataValue){
                    

                $key = array_search($fieldsDataKey, array_column($products, 'slug'));

                $products[$key]['fields'] = $fieldsDataValue;
            }

            /*************************/

            /******Post Tab********/

            $activityPost = ActivityAction::with('attachments.attachment_link','subject_id')->where('subject_id', $loggedInUser->user_id)->orderBy('activity_action_id','DESC')->paginate(15);
            
            /*********************/

            /********About tab***/

            $userAboutTab = $this->getUserSubmitedFields(1);

            $role_id = $this->user->role_id;
            $user_id = $this->user->user_id;

            
            $roleFields = DB::table('user_field_map_roles')->select('user_fields.title','user_fields.user_field_id','user_fields.type')
                          ->join('user_fields', 'user_fields.user_field_id', '=', 'user_field_map_roles.user_field_id')
                          ->where("role_id","=",$role_id)
                          ->where("require_update","=",'true')
                          ->where("conditional","=",'no')
                          ->orderBy("edit_profile_field_order","asc")
                          ->get();


            if($roleFields){
                foreach ($roleFields as $key => $value) {
                    $radioFieldValue = DB::table('user_field_values')
                                    ->where('user_id', $user_id)
                                    ->where('user_field_id', $value->user_field_id)
                                    ->first();
                    
                            
                    $roleFields[$key]->title = $this->translate('messages.'.$value->title,$value->title);
                    if($roleFields[$key]->type == 'radio')
                    {
                        if(($radioFieldValue->value == 'Yes' || $radioFieldValue->value == '621' || $radioFieldValue->value == '623' || $radioFieldValue->value == '625'))
                            $roleFields[$key]->value = 'Yes';
                        else
                            $roleFields[$key]->value = 'No';
                    }
                    elseif($roleFields[$key]->type !='text' && $roleFields[$key]->type !='email')
                    {
                        $arrayValues = array();
                        $fieldValues = DB::table('user_field_values')
                                    ->where('user_id', $user_id)
                                    ->where('user_field_id', $value->user_field_id)
                                    ->get();
                        if(count($fieldValues) > 0)
                        {
                            foreach($fieldValues as $fieldValue)
                            {
                                $options = DB::table('user_field_options')
                                        ->where('head', 0)->where('parent', 0)
                                        ->where('user_field_option_id', $fieldValue->value)
                                        ->first();
                                if(!empty($options->option))
                                $arrayValues[] = $options->option;
                                
                            }
                        }
                        $roleFields[$key]->value = join(", ", $arrayValues);
                        
                    }
                    else
                    {
                        $fieldValue = DB::table('user_field_values')
                                    ->where('user_id', $user_id)
                                    ->where('user_field_id', $value->user_field_id)
                                    ->first();
                        if($fieldValue->user_field_id == 36)
                        {
                            $userAbout = User::select('about')->where('user_id', $loggedInUser->user_id)->first();
                            $roleFields[$key]->value = $userAbout->about;
                        }
                        else
                        {
                            $roleFields[$key]->value = $fieldValue->value??'';    
                        }
                        
                    }
                    

                }
            }

            $newArr = [];
            foreach($roleFields as $keyRole => $role)
            {
                if($roleFields[$keyRole]->value != "")
                {
                    $newArr[] = $roleFields[$keyRole];
                }
            }
            $roleFields = $newArr;


            /*********************/

            /********Contact tab***/

            $contact = User::select('user_id','role_id','email','phone','country_code','address','website','fb_link')->where('user_id', $loggedInUser->user_id)->first();

            /*********************/


           
            $data = ['post_count' => $postCount, 'connection_count' => $connectionsCount, 'follower_count' => $followerCount, 'following_count' => $followingCount, 'user_data' => $userData, 'about' => $userAbout->about, 'products' => $products, 'about_tab' => $roleFields, 'contact_tab' => $contact];

            return response()->json(['success' => $this->successStatus,
                                'data' => $data
                            ], $this->successStatus);
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()], $this->exceptionStatus); 
        }
        
    }


    /*
     * Get Permission For Sending Requests
     * 
     */
    public function getPermissions($roleId)
    {
        try
        {
            $permissions = ConnectFollowPermission::select('connect_follow_permission_id','role_id','permission_type')->where('role_id', $roleId)->get();
            if(count($permissions) > 0)
            {
                foreach($permissions as $key => $permission)
                {
                    $mapPermission = MapPermissionRole::select('map_permission_role_id','connect_follow_permission_id','role_id')->where('connect_follow_permission_id', $permission->connect_follow_permission_id)->get();
                    $permissions[$key]->map_permissions = $mapPermission;
                }

                return $permissions;
            }
            else
            {
                $message = "No privillege granted for sending request or following someone";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }


    /*
     * Get Visitor Profile API
     *
     */
    public function getVisitorProfile(Request $request)
    {
        try
        {
            $loggedInUser = $this->user;
            $validator = Validator::make($request->all(), [ 
                'visitor_profile_id' => 'required'           
            ]);

        
            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $userData = User::select('user_id','profile_percentage','role_id','company_name','restaurant_name','first_name','last_name','name as username','avatar_id','cover_id','allow_message_from','who_can_view_age','who_can_view_profile','who_can_connect')->with('avatar_id','cover_id')->where('user_id', $request->visitor_profile_id)->first();
            if(!empty($userData))
            {
                if($loggedInUser->role_id == 3)
                {
                    $color = "8EC9BB";
                }
                elseif($loggedInUser->role_id == 4 || $loggedInUser->role_id == 5 || $loggedInUser->role_id == 6) 
                {
                    $color = "#A02C2D";
                }
                elseif($loggedInUser->role_id == 7) 
                {
                    $color = "AB6393";
                }
                elseif($loggedInUser->role_id == 8) 
                {
                    $color = "CA7E8D";
                }
                elseif($loggedInUser->role_id == 9) 
                {
                    $color = "FDCF76";
                }
                elseif($loggedInUser->role_id == 10) 
                {
                    $color = "9C8ADE";
                }

                $userData->profile_color = $color;

                $isBlockUser = BlockList::where('user_id', $loggedInUser->user_id)->where('block_user_id', $request->visitor_profile_id)->first();

                $permissions = $this->getPermissions($loggedInUser->role_id);

                //return $permissions;
                if(count($permissions) > 0) 
                {
                    foreach($permissions as $permission)
                    {
                        if($permission->permission_type == 1)
                        {
                            foreach($permission->map_permissions as $per)
                            {
                                if($userData->role_id == $per->role_id)
                                {
                                    $userData->available_to_connect = 1;
                                    break;
                                }                        
                            } 
                        }
                        if($permission->permission_type == 2)
                        {
                            foreach($permission->map_permissions as $per)
                            {
                                if($userData->role_id == $per->role_id)
                                {
                                    $userData->available_to_follow = 1;
                                    break;
                                }
                            }
                        }
                    }
                }

                $checkPrivacy = User::whereRaw("find_in_set(".$loggedInUser->role_id.",who_can_connect)")->where('user_id', $request
                    ->visitor_profile_id)->first();
                
                if(!empty($userData->available_to_connect) && !empty($checkPrivacy))
                {
                    $userData->available_to_connect = 1;

                    //$checkIfConnected = Connection::where('resource_id', 1)->orWhere('user_id', 1)->first();

                    $checkIfConnected = Connection::where(function ($query) use ($loggedInUser, $request) {
                    $query->where('resource_id', $loggedInUser->user_id)->where('user_id', $request->visitor_profile_id);
                      })->oRwhere(function ($query) use ($loggedInUser, $request) {
                          $query->where('resource_id', $request->visitor_profile_id)->where('user_id', $loggedInUser->user_id);
                      })->first();

                    if(!empty($checkIfConnected))
                    {
                        if($checkIfConnected->is_approved == '1')
                        {
                            $userData->connection_flag = 1;
                        }
                        elseif($checkIfConnected->resource_id == $loggedInUser->user_id)
                        {
                            $userData->connection_flag = 2;
                        }
                        elseif($checkIfConnected->resource_id == $request->visitor_profile_id)
                        {
                            $userData->connection_flag = 3;
                        }
                    } 
                    else
                    {
                        $userData->connection_flag = 0;    
                    }
                }
                else
                {   
                    $userData->available_to_connect = 0;
                    $userData->connection_flag = 0;
                }

                if(!empty($userData->available_to_follow))
                {
                    $userData->available_to_follow = 1;
                    $checkIfFollowing = Follower::where('user_id', $loggedInUser->user_id)->where('follow_user_id', $request->visitor_profile_id)->first();
                    (!empty($checkIfFollowing)) ? $userData->follow_flag = 1 : $userData->follow_flag = 0;
                }
                else
                {   
                    $userData->available_to_follow = 0;
                    $userData->follow_flag = 0;
                }

                (!empty($isBlockUser)) ? $userData->block_flag = 1 : $userData->block_flag = 0;

                //(!empty($userData->available_to_connect)) ? $userData->available_to_connect = 1 : $userData->available_to_connect = 0;
                //(!empty($userData->available_to_follow)) ? $userData->available_to_follow = 1 : $userData->available_to_follow = 0;

                
                $loggedInUserData = User::where('user_id', $loggedInUser->user_id)->first();

                $userAbout = User::select('about')->where('user_id', $request->visitor_profile_id)->first();

                $postCount = ActivityAction::where('subject_id', $request->visitor_profile_id)->count();
                
                $connectionsCount = Connection::where(function ($query) use ($request) {
                    $query->where('is_approved', '1');
                      })->where(function ($query) use ($request) {
                          $query->where('resource_id', $request->visitor_profile_id)->orWhere('user_id', $request->visitor_profile_id);
                      })->count();

                $followerCount = Follower::where('follow_user_id', $request->visitor_profile_id)->count();

                $followingCount = Follower::where('user_id', $request->visitor_profile_id)->count();


                /*****Featured Listings****/

                $userFieldInfo = [];

                $fieldsTypes = $this->getFeaturedListingTypes($userData->role_id, 'featured');
                
                $products = [];
                
                foreach($fieldsTypes as $fieldsTypesKey => $fieldsTypesValue){
                    
                    $featuredListing = FeaturedListing::with('image')
                                        ->where('user_id', $request->visitor_profile_id)
                                        ->where('featured_listing_type_id', $fieldsTypesValue->featured_listing_type_id)
                                        ->orderBy('featured_listing_id','DESC')->get(); 

                    $products[] = ["title" => $fieldsTypesValue->title,"slug" => $fieldsTypesValue->slug,"products" => $featuredListing];
                    
                }

                //Get Featured Listing Fields

                //Get Featured Type
                $featuredTypes = $this->getFeaturedListingFieldsByRoleId($userData->role_id, 'featured');
                $fieldsData = [];
                foreach ($featuredTypes as $key => $value) {

                    $value->title = $this->translate('messages.'.$value->title,$value->title);

                    $value->options = $this->getFeaturedListingFieldOptionParent($value->featured_listing_field_id);

                    if(!empty($value->options)){
                        foreach ($value->options as $k => $oneDepth) {

                                $value->options[$k]->option = $this->translate('messages.'.$oneDepth->option,$oneDepth->option);
                            }
                    }

                    $fieldsData[$value->featured_listing_type_slug][] = $value;
                }

                foreach($fieldsData as $fieldsDataKey => $fieldsDataValue){
                        

                    $key = array_search($fieldsDataKey, array_column($products, 'slug'));

                    $products[$key]['fields'] = $fieldsDataValue;
                }

                /*************************/

                /******Post Tab********/

                $activityPost = ActivityAction::with('attachments.attachment_link','subject_id')->where('subject_id', $request->visitor_profile_id)->orderBy('activity_action_id','DESC')->paginate(15);
                
                /*********************/

                /********About tab***/

                $role_id = $userData->role_id;
                $user_id = $request->visitor_profile_id;

                
                $roleFields = DB::table('user_field_map_roles')->select('user_fields.title','user_fields.user_field_id','user_fields.type')
                              ->join('user_fields', 'user_fields.user_field_id', '=', 'user_field_map_roles.user_field_id')
                              ->where("role_id","=",$role_id)
                              ->where("require_update","=",'true')
                              ->where("conditional","=",'no')
                              ->orderBy("edit_profile_field_order","asc")
                              ->get();


                if($roleFields){
                    foreach ($roleFields as $key => $value) {
                        $radioFieldValue = DB::table('user_field_values')
                                        ->where('user_id', $user_id)
                                        ->where('user_field_id', $value->user_field_id)
                                        ->first();
                        
                                
                        $roleFields[$key]->title = $this->translate('messages.'.$value->title,$value->title);
                        if($roleFields[$key]->type == 'radio')
                        {
                            if(($radioFieldValue->value == 'Yes' ||  $radioFieldValue->value == '1'))
                                $roleFields[$key]->value = $radioFieldValue->value;
                            else
                                $roleFields[$key]->value = 'No';
                        }
                        elseif($roleFields[$key]->type !='text' && $roleFields[$key]->type !='email')
                        {
                            $arrayValues = array();
                            $fieldValues = DB::table('user_field_values')
                                        ->where('user_id', $user_id)
                                        ->where('user_field_id', $value->user_field_id)
                                        ->get();
                            if(count($fieldValues) > 0)
                            {
                                foreach($fieldValues as $fieldValue)
                                {
                                    $options = DB::table('user_field_options')
                                            //->where('user_id', $user_id)
                                            ->where('head', 0)->where('parent', 0)
                                            ->where('user_field_option_id', $fieldValue->value)
                                            ->first();
                                    if(!empty($options->option))
                                    $arrayValues[] = $options->option;
                                    
                                }
                            }
                            $roleFields[$key]->value = join(", ", $arrayValues);
                            
                        }
                        else
                        {
                            $fieldValue = DB::table('user_field_values')
                                        ->where('user_id', $user_id)
                                        ->where('user_field_id', $value->user_field_id)
                                        ->first();
                            $roleFields[$key]->value = $fieldValue->value??'';
                        }
                        

                    }
                }

                $newArr = [];
                foreach($roleFields as $keyRole => $role)
                {
                    if($roleFields[$keyRole]->value != "")
                    {
                        $newArr[] = $roleFields[$keyRole];
                    }
                }
                $roleFields = $newArr;
                /*********************/

                /********Contact tab***/

                $contact = User::select('user_id','role_id','email','phone','country_code','address','website','fb_link')->where('user_id', $request->visitor_profile_id)->first();

                /*********************/
                
                //$loggedInUserData;
                $data = ['post_count' => $postCount, 'connection_count' => $connectionsCount, 'follower_count' => $followerCount, 'following_count' => $followingCount, 'user_data' => $userData, 'about' => $userAbout->about, 'products' => $products, 'posts' => $activityPost,'about_tab' => $roleFields, 'contact_tab' => $contact];

                return response()->json(['success' => $this->successStatus,
                                    'data' => $data
                                ], $this->successStatus);
            }
            else
            {
                $message = "No user found";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()], $this->exceptionStatus); 
        }
        
    }

    /*
     * Get Member Profile
     *
     */
    public function getMemberProfile()
    {
        try
        {
            $loggedInUser = $this->user;
            $profilePercentage = $this->profileStatus($loggedInUser->user_id);
            User::where('user_id', $loggedInUser->user_id)->update(['profile_percentage' => $profilePercentage]);

            $userData = User::select('user_id','profile_percentage','role_id','company_name','restaurant_name','first_name','last_name','name as username','avatar_id','cover_id')->with('avatar_id','cover_id')->where('user_id', $loggedInUser->user_id)->first();

            $userAbout = User::select('about')->where('user_id', $loggedInUser->user_id)->first();

            $postCount = ActivityAction::where('subject_id', $loggedInUser->user_id)->count();
            $connectionsCount = Connection::where('is_approved', '1')->where('resource_id', $loggedInUser->user_id)->orWhere('user_id', $loggedInUser->user_id)->count();
            $followerCount = Follower::where('follow_user_id', $loggedInUser->user_id)->count();


            /*****Featured Listings****/

            $userFieldInfo = [];

            $fieldsTypes = $this->getFeaturedListingTypes($this->user->role_id, 'featured');
            
            $products = [];
            
            foreach($fieldsTypes as $fieldsTypesKey => $fieldsTypesValue){
                
                $featuredListing = FeaturedListing::with('image')
                                    ->where('user_id', $this->user->user_id)
                                    ->where('featured_listing_type_id', $fieldsTypesValue->featured_listing_type_id)
                                    ->orderBy('featured_listing_id','DESC')->get(); 

                $products[] = ["title" => $fieldsTypesValue->title,"slug" => $fieldsTypesValue->slug,"products" => $featuredListing];
                
            }

            //Get Featured Listing Fields

            //Get Featured Type
            $featuredTypes = $this->getFeaturedListingFieldsByRoleId($this->user->role_id, 'featured');
            $fieldsData = [];
            foreach ($featuredTypes as $key => $value) {

                $value->title = $this->translate('messages.'.$value->title,$value->title);

                $value->options = $this->getFeaturedListingFieldOptionParent($value->featured_listing_field_id);

                if(!empty($value->options)){
                    foreach ($value->options as $k => $oneDepth) {

                            $value->options[$k]->option = $this->translate('messages.'.$oneDepth->option,$oneDepth->option);
                        }
                }

                $fieldsData[$value->featured_listing_type_slug][] = $value;
            }

            foreach($fieldsData as $fieldsDataKey => $fieldsDataValue){
                    

                $key = array_search($fieldsDataKey, array_column($products, 'slug'));

                $products[$key]['fields'] = $fieldsDataValue;
            }

            /*************************/


           
            $data = ['post_count' => $postCount, 'connection_count' => $connectionsCount, 'follower_count' => $followerCount, 'user_data' => $userData, 'about' => $userAbout->about, 'products' => $products];
            return response()->json(['success' => $this->successStatus,
                                'data' => $data
                            ], $this->successStatus);
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()], $this->exceptionStatus); 
        }
        
    }

    /*
     * Get Member About tab
     *
     */
    public function getMemberAboutTab()
    {
        try
        {
            $loggedInUser = $this->user;

            $data = $this->getFieldValueOnAboutTab($loggedInUser->role_id, $loggedInUser->user_id);

            return response()->json(['success' => $this->successStatus,
                                'data' => $data
                            ], $this->successStatus);


        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()], $this->exceptionStatus); 
        }
    }

    /*
    *
    *Get Member Contact Tab
    */
    public function getMemberContactTab($visitorProfileId = '')
    {
        try
        {
            $loggedInUser = $this->user;
            if(!empty($visitorProfileId))
            {
                $data = User::select('user_id','role_id','email','phone','country_code','address','website','fb_link')->where('user_id', $visitorProfileId)->first();    
            }
            else
            {
                $data = User::select('user_id','role_id','email','phone','country_code','address','website','fb_link')->where('user_id', $loggedInUser->user_id)->first();
            }
            

            return response()->json(['success' => $this->successStatus,
                                'data' => $data
                            ], $this->successStatus);


        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()], $this->exceptionStatus); 
        }
    }

    /*
    *Get Tab content
    */
    public function getFieldValueOnAboutTab($role_id, $user_id)
    {
        $values = [];

        $userFieldProductType = DB::table('user_fields')
            ->where('name', 'product_type')
            ->first();

        $userFieldAbout = DB::table('user_fields')
            ->where('name', 'about')
            ->first();

        $userFieldOurProduct = DB::table('user_fields')
            ->where('name', 'our_product')
            ->first();

        $userFieldCountry = DB::table('user_fields')
            ->where('name', 'country')
            ->first();

        $userFieldExpertise = DB::table('user_fields')
            ->where('name', 'expertise')
            ->first();    

        $userFieldTitle = DB::table('user_fields')
            ->where('name', 'title')
            ->first(); 

        $userFieldTour = DB::table('user_fields')
            ->where('name', 'our_tour')
            ->first();   

        $userFieldSpecialityTrip = DB::table('user_fields')
            ->where('name', 'speciality')
            ->first(); 

        $userFieldRestaurantType = DB::table('user_fields')
            ->where('name', 'restaurant_type')
            ->first();        

        $userFieldMenu = DB::table('user_fields')
            ->where('name', 'our_menu')
            ->first();    

        if($role_id == 3 || $role_id == 4 || $role_id == 5 || $role_id == 6)
        {
            $productTypeArray = [];
            $ourProductsArray = [];
            $aboutArray = [];

            if(!empty($userFieldProductType))
            {
                $fieldValueProductTypes = DB::table('user_field_values')
                ->where('user_id', $user_id)
                ->where('user_field_id', $userFieldProductType->user_field_id)
                ->get();
                if($fieldValueProductTypes->count() > 0)
                {
                    foreach($fieldValueProductTypes as $fieldValueProductType)
                    {
                        $fieldValue = DB::table('user_field_options')
                        ->where('user_field_option_id', $fieldValueProductType->value)
                        ->first();
                        $productTypeArray[] = $this->translate('messages.'.$fieldValue->option,$fieldValue->option);
                    }
                }
            }
            
            if(!empty($userFieldAbout))
            {
                $fieldValueAbout = User::select('about')->where('user_id', $user_id)->first();
                $aboutArray = $this->translate('messages.'.$fieldValueAbout->about,$fieldValueAbout->about);
            }

            if(!empty($userFieldOurProduct))
            {

                $fieldValueOurProduct = DB::table('user_field_values')
                ->where('user_id', $user_id)
                ->where('user_field_id', $userFieldOurProduct->user_field_id)
                ->first();
                $ourProductsArray = $this->translate('messages.'.$fieldValueOurProduct->value,$fieldValueOurProduct->value);
            }

            $values = ["Product Type" => $productTypeArray, "About" => $aboutArray, "Our Products" => $ourProductsArray];
            
        }
        elseif($role_id == 7)
        {
            $countryArray = [];
            $expertiseArray = [];
            $titleArray = [];
            $aboutArray = [];

            if(!empty($userFieldCountry))
            {
                $fieldValueCountry = DB::table('user_field_values')
                ->where('user_id', $user_id)
                ->where('user_field_id', $userFieldCountry->user_field_id)
                ->first();

                $country = Country::where('id', $fieldValueCountry->value)->first();
                $countryArray = $this->translate('messages.'.$country->name,$country->name);
            }

            if(!empty($userFieldExpertise))
            {
                $fieldValueEpertise = DB::table('user_field_values')
                ->where('user_id', $user_id)
                ->where('user_field_id', $userFieldExpertise->user_field_id)
                ->get();

                if($fieldValueEpertise->count() > 0)
                {
                    foreach($fieldValueEpertise as $expertise)
                    {
                        $fieldValue = DB::table('user_field_options')
                        ->where('user_field_option_id', $expertise->value)
                        ->first();
                        $expertiseArray[] = $this->translate('messages.'.$fieldValue->option,$fieldValue->option);
                    }
                }
            }

            if(!empty($userFieldTitle))
            {
                $fieldValueTitle = DB::table('user_field_values')
                ->where('user_id', $user_id)
                ->where('user_field_id', $userFieldTitle->user_field_id)
                ->get();

                if($fieldValueTitle->count() > 0)
                {
                    foreach($fieldValueTitle as $title)
                    {
                        $fieldValue = DB::table('user_field_options')
                        ->where('user_field_option_id', $title->value)
                        ->first();
                        $titleArray[] = $this->translate('messages.'.$fieldValue->option,$fieldValue->option);
                    }
                }
            }

            if(!empty($userFieldAbout))
            {
                $fieldValueAbout = User::select('about')->where('user_id', $user_id)->first();
                $aboutArray = $this->translate('messages.'.$fieldValueAbout->about,$fieldValueAbout->about);
            }

            $values = ["Country" => $countryArray, "Expertise" => $expertiseArray, "Title" => $titleArray, "About" => $aboutArray];

        }
        elseif($role_id == 8)
        {
            $countryArray = [];
            $ourTourArray = [];
            $aboutArray = [];
            $specialityTripArray = [];

            if(!empty($userFieldCountry))
            {
                $fieldValueCountry = DB::table('user_field_values')
                ->where('user_id', $user_id)
                ->where('user_field_id', $userFieldCountry->user_field_id)
                ->first();

                $country = Country::where('id', $fieldValueCountry->value)->first();
                $countryArray = $this->translate('messages.'.$country->name,$country->name);
            }

            if(!empty($userFieldAbout))
            {
                $fieldValueAbout = User::select('about')->where('user_id', $user_id)->first();
                $aboutArray = $this->translate('messages.'.$fieldValueAbout->about,$fieldValueAbout->about);
            }

            if(!empty($userFieldTour))
            {
                $fieldValueTour = DB::table('user_field_values')
                ->where('user_id', $user_id)
                ->where('user_field_id', $userFieldTour->user_field_id)
                ->first();
                $ourTourArray = $this->translate('messages.'.$fieldValueTour->value,$fieldValueTour->value);
            }

            if(!empty($userFieldSpecialityTrip))
            {
                $fieldValueTrips = DB::table('user_field_values')
                ->where('user_id', $user_id)
                ->where('user_field_id', $userFieldSpecialityTrip->user_field_id)
                ->get();

                if($fieldValueTrips->count() > 0)
                {
                    foreach($fieldValueTrips as $fieldValueTrip)
                    {
                        $fieldValue = DB::table('user_field_options')
                        ->where('user_field_option_id', $fieldValueTrip->value)
                        ->first();
                        $specialityTripArray[] = $this->translate('messages.'.$fieldValue->option,$fieldValue->option);
                    }
                }
            }

            $values = ["Country" => $countryArray, "Speciality" => $specialityTripArray, "About" => $aboutArray, "Our tours" => $ourTourArray];
        }
        elseif($role_id == 9)
        {
            $restaurantTypeArray = [];
            $aboutArray = [];
            $menuArray = [];

            if(!empty($userFieldAbout))
            {
                $fieldValueAbout = User::select('about')->where('user_id', $user_id)->first();
                $aboutArray = $this->translate('messages.'.$fieldValueAbout->about,$fieldValueAbout->about);
            }

            if(!empty($userFieldRestaurantType))
            {
                $fieldValueRetaurantTypes = DB::table('user_field_values')
                ->where('user_id', $user_id)
                ->where('user_field_id', $userFieldRestaurantType->user_field_id)
                ->get();

                if($fieldValueRetaurantTypes->count() > 0)
                {
                    foreach($fieldValueRetaurantTypes as $fieldValueRetaurantType)
                    {
                        $fieldValue = DB::table('user_field_options')
                        ->where('user_field_id', $fieldValueRetaurantType->user_field_id)
                        ->first();
                        $restaurantTypeArray[] = $this->translate('messages.'.$fieldValue->option,$fieldValue->option);
                    }
                }
            }

            if(!empty($userFieldMenu))
            {
                $fieldValueMenu = DB::table('user_field_values')
                ->where('user_id', $user_id)
                ->where('user_field_id', $userFieldMenu->user_field_id)
                ->first();
                $menuArray = $this->translate('messages.'.$fieldValueMenu->value,$fieldValueMenu->value);
            }

            $values = ["Restaurant Type" => $restaurantTypeArray, "About" => $aboutArray, "Menu" => $menuArray];
        }
        elseif($role_id == 10)
        {

        }
        /*else
        {
            $values = [$this->translate('messages.'."Invalid Role Id","Invalid Role Id"), 1];
        }*/
        return $values;

        
    }   

    /*
     * Get Featured Type Using Role Id
     * @params $roleId
     */
    public function getFeaturedType($roleId){
        $featuredTypes = DB::table("featured_listing_types as flt")
            ->join("featured_listing_type_role_maps as fltrm", 'fltrm.featured_listing_type_id', '=', 'flt.featured_listing_type_id')

            ->where("fltrm.role_id","=",$roleId)
            ->first();

        return $featuredTypes;
    }
    

    /*
     * Check Exist Fields Values and delete
     * @Params $roleId
     */
    public function deleteValueIfExist($user_id, $key)
    {
        $fieldValue = DB::table('user_field_values')
            ->where('user_id', $user_id)
            ->where('user_field_id', $key)
            ->first();
        if(!empty($fieldValue))
        {
            DB::table('user_field_values')
                ->where('user_id', $user_id)
                ->where('user_field_id', $key)
                ->delete();
        }
    }
    


    /*
     * Check Fields based on role id
     * @Params $roleId
     */

    public function checkFieldsByRoleId($roleId){

            $roleFields = DB::table('user_field_map_roles')
                      ->join('user_fields', 'user_fields.user_field_id', '=', 'user_field_map_roles.user_field_id')
                      ->where("role_id","=",$roleId)
                      ->where("require_update","=",'true')
                      ->where("conditional","=",'no')
                      ->orderBy("order","asc")
                      ->get();

        return $roleFields;
    }

    /*
     * Make Validation Rules
     * @Params $userFields
     */

    public function makeValidationRules($userFields){
        $rules = [];
        foreach ($userFields as $key => $field) {
            
            if($field->name == 'email' && $field->required == 'yes'){

                $rules[$field->name] = 'required|email|unique:users|max:50';

            }else {

                if($field->required == 'yes'){
                    $rules[$field->name] = 'required|max:200';
                }
            }
        }

        return $rules;

    }

    /*
     * Segregate user input data
     * @Params $input and @userFields
     */
    public function segregateInputData($input,$userFields){

        $inputData = [];

        foreach($userFields as $key => $field){
            if(array_key_exists($field->user_field_id, $input)){
                $inputData[$field->name] = $input[$field->user_field_id];
            }
        }

        return $inputData;

    }

    /*
     * Get All Fields Option who are child
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
     * Get user field parent
     * @params $user_field_id 
    */
    public function getUserFieldOptionGrandParent($fieldId){
        $fieldOptionDataSuperParent = '';
        $fieldOptionData = [];

        if($fieldId > 0){
            $fieldOptionData = DB::table('user_field_options')
                    ->where('user_field_option_id','=',$fieldId)
                    ->first();

            
            /*if(!empty($fieldOptionData->parent))
            {

                $fieldOptionDataParent = DB::table('user_field_options')
                    ->where('user_field_option_id','=',$fieldOptionData->parent)
                    ->first();
                if(!empty($fieldOptionDataParent)){

                    $fieldOptionDataSuperParent = DB::table('user_field_options')
                    ->where('user_field_option_id','=',$fieldOptionDataParent->parent)
                    ->first();
                }
                    
            }*/
            
        }

        if(!empty($fieldOptionData)){
            return $fieldOptionData->user_field_option_id;     
        }else{
            return '';
        }
        
        
        if(!empty($fieldOptionDataSuperParent)){
            return $fieldOptionDataSuperParent->user_field_option_id;    
        }else{
            return $fieldOptionDataSuperParent;
        }
        
        
    }

    /*
     * Get All Fields Option who are child
     * @params $user_field_id and $user_field_option_id
     */
    public function getUserFieldOptionsNoneParent($fieldId, $parentId, $userFieldValues){

        $fieldOptionData = [];
        //echo $parentId;
        if($fieldId > 0 && $parentId > 0){
            $fieldOptionData = DB::table('user_field_options')
                ->where('user_field_id','=',$fieldId)
                ->where('parent','=',$parentId)
                ->get();                                


            foreach ($fieldOptionData as $key => $option) {
                $fieldOptionData[$key]->option = $this->translate('messages.'.$option->option,$option->option);

                if(in_array($option->user_field_option_id, $userFieldValues))
                {
                    //echo $parentId;
                    $fieldOptionData[$key]->is_selected = true;    
                }
                else
                {
                    $fieldOptionData[$key]->is_selected = false;
                }
                
            }
        }
        
        return $fieldOptionData;    
        
    }
    

    /*
     * Validate Change Password
     * @Params $requestedfields
     */

    public function validatePassword($requestedFields){
        $rules = [];
        foreach ($requestedFields as $key => $field) {
            //return $key;
            if($key == 'old_password'){

                $rules[$key] = 'required|max:190';

            }else if($key == 'new_password'){

                $rules[$key] = 'required|max:190';

            }else if($key == 'c_password'){

                $rules[$key] = 'required|same:new_password';

            }
        }

        return $rules;

    }

}
