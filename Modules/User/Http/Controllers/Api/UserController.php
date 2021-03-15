<?php

namespace Modules\User\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Http\Controllers\CoreController;
use Modules\User\Entities\Role;
use Modules\User\Entities\FeaturedListing;
use Illuminate\Support\Facades\Auth; 
use Modules\User\Entities\User; 
use Modules\User\Entities\Certificate;
use Validator;
use App\Image;
use DB;
use Cache;
use App\Events\Welcome;
use App\Http\Traits\UploadImageTrait;

class UserController extends CoreController
{
    use UploadImageTrait;
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

    public function updateUserSettings(Request $request){
        try{
                $loggedInUser = $this->user;

                $validator = Validator::make($request->all(), [ 
                    //'name' => 'required|unique:users,name,'.$loggedInUser->user_id,
                    'name' => 'required',
                    'display_name' => 'required|max:190',
                    'locale' => 'required',
                    'website' => 'required|max:190',
                    //'avatar_id' => 'required',
                ]);

            
                if ($validator->fails()) { 
                    return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
                }
                
                $user = User::where('user_id','=',$this->user->user_id)->first();
                $user->website = $request->website;
                $user->name = $request->name;
                $user->display_name = $request->display_name;
                $user->locale = $request->locale;
                $user->save();

                $userData = User::select('*','name as username')->with('profile_image','roles')->where('user_id','=',$this->user->user_id)->first();
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

                /*$validator = Validator::make($input, [ 
                    'email' => 'required|unique:users,email,'.$this->user->user_id.',user_id', 
                ]);

                if ($validator->fails()) { 
                    return response()->json(['errors'=>$validator->errors(),'success' => $this->validationStatus], $this->validationStatus);
                }*/
                
                $user = User::where('user_id','=',$this->user->user_id)->update($input);

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

    public function getUserSubmitedFields()
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
                            if(!empty($fieldValue) && $value->type == 'text')
                            {
                                $roleFields[$key]->is_selected = $fieldValue->value;
                            }
                            elseif(!empty($fieldValue) && $value->type == 'radio' && ($fieldValue->value == 'Yes' ||  $fieldValue->value == '1'))
                            {
                                $roleFields[$key]->is_selected = true;
                            }
                            else
                            {
                                $roleFields[$key]->is_selected = false;
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

                                            /*$userOptionValue = DB::table('user_field_values')
                                            ->where('user_id', $user_id)
                                            ->where('user_field_id', $value->user_field_id)
                                            ->get();

                                            $userOptionValue = $userOptionValue->pluck('value');

                                            $userOptionValue = DB::table('user_field_values')
                                            ->where('user_id', $user_id)
                                            ->where('user_field_id', $value->user_field_id)
                                            ->whereIn('value', $userOptionValue)
                                            ->first();*/
                                            
                                            if($grandParent == $oneDepth->user_field_option_id)
                                            {
                                                $value->options[$k]->is_selected = true;

                                            }else{
                                                $value->options[$k]->is_selected = false;
                                            }

                                            /*if(!empty($userOptionValue))
                                            {
                                                $value->options[$k]->is_selected = true;

                                            }else{
                                                $value->options[$k]->is_selected = false;
                                            }*/
                                            

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
            return response()->json(['success'=>$this->successStatus,'data' =>['step_1'=>$steps],'response_time'=>$response_time], $this->successStatus); 
        }      
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
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

            if(!empty($rules)){
                
                $validator = Validator::make($inputData, $rules);

                if ($validator->fails()) { 

                    return response()->json(['success'=>$this->validationStatus,'errors'=>$validator->errors()->first()], $this->validationStatus);
                }

                /*if(array_key_exists('email',$inputData) && array_key_exists('password',$inputData)
                  ){*/

                    $userData = [];
                    
                    
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
		            $userProfile->photo_of_label = $this->uploadImage($request->file('avatar_id'));
		        }
		        if(!empty($request->file('cover_id')))
		        {
		            $userProfile->photo_of_label = $this->uploadImage($request->file('cover_id'));
		        }

                            return response()->json(['success' => $this->successStatus,
                                 'message' => $this->translate('messages.'."Profile updated","Profile updated")
                                ], $this->successStatus);
                        
                //}
                
            }
        }catch(\Exception $e){
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()], $this->exceptionStatus); 
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

            $fieldOptions = DB::table('user_field_options')->where('parent','=',0)->where('head','=',0)->get();

            $fieldOptions = $fieldOptions->pluck('user_field_option_id');

            $userFieldValues = DB::table('user_field_values')->where('user_id', $loggedInUser->user_id)->where('user_field_id', 2)->whereIn('value', $fieldOptions)->get();

            $userFieldValues = $userFieldValues->pluck('value');

            $fieldOptions = DB::table('user_field_options')->whereIn('user_field_option_id',$userFieldValues)->get();



            foreach($fieldOptions as $key => $fieldOption)
            {
                $userCertificates = Certificate::with('photo_of_label','fce_sid_certification','phytosanitary_certificate','packaging_for_usa','food_safety_plan','animal_helath_asl_certificate')->where('user_id', $loggedInUser->user_id)->where('user_field_option_id', $fieldOption->user_field_option_id)->first();
                $fieldOptions[$key]->certificates = $userCertificates;
            } 

            
            return response()->json(['success' => $this->successStatus,
                             'data' => $fieldOptions,
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
                'user_field_option_id' => 'required'                
            ]);

        
            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }
            
            $checkExistingCertificate = Certificate::where('user_field_option_id', $request->user_field_option_id)->where('user_id', $loggedInUser->user_id)->first();
            
            if(!empty($checkExistingCertificate))
            {
                if(!empty($request->file('photo_of_label')))
                {
                    $checkExistingCertificate->photo_of_label = $this->uploadImage($request->file('photo_of_label'));
                }
                if(!empty($request->file('fce_sid_certification')))
                {
                    $checkExistingCertificate->fce_sid_certification = $this->uploadImage($request->file('fce_sid_certification'));
                }
                if(!empty($request->file('phytosanitary_certificate')))
                {
                    $checkExistingCertificate->phytosanitary_certificate = $this->uploadImage($request->file('phytosanitary_certificate'));
                }
                if(!empty($request->file('packaging_for_usa')))
                {
                    $checkExistingCertificate->packaging_for_usa = $this->uploadImage($request->file('packaging_for_usa'));
                }
                if(!empty($request->file('food_safety_plan')))
                {
                    $checkExistingCertificate->food_safety_plan = $this->uploadImage($request->file('food_safety_plan'));
                }
                if(!empty($request->file('animal_helath_asl_certificate')))
                {
                    $checkExistingCertificate->animal_helath_asl_certificate = $this->uploadImage($request->file('animal_helath_asl_certificate'));
                }
                $checkExistingCertificate->save();
            }
            else
            {
                $userCertificate = new Certificate;
                $userCertificate->user_id = $loggedInUser->user_id;
                $userCertificate->user_field_option_id = $request->user_field_option_id;
               
                if(!empty($request->file('photo_of_label')))
                {
                    $userCertificate->photo_of_label = $this->uploadImage($request->file('photo_of_label'));
                }
                if(!empty($request->file('fce_sid_certification')))
                {
                    $userCertificate->fce_sid_certification = $this->uploadImage($request->file('fce_sid_certification'));
                }
                if(!empty($request->file('phytosanitary_certificate')))
                {
                    $userCertificate->phytosanitary_certificate = $this->uploadImage($request->file('phytosanitary_certificate'));
                }
                if(!empty($request->file('packaging_for_usa')))
                {
                    $userCertificate->packaging_for_usa = $this->uploadImage($request->file('packaging_for_usa'));
                }
                if(!empty($request->file('food_safety_plan')))
                {
                    $userCertificate->food_safety_plan = $this->uploadImage($request->file('food_safety_plan'));
                }
                if(!empty($request->file('animal_helath_asl_certificate')))
                {
                    $userCertificate->animal_helath_asl_certificate = $this->uploadImage($request->file('animal_helath_asl_certificate'));
                }
                $userCertificate->save();
            }
            

            
            return response()->json(['success' => $this->successStatus,
                             'message' => $this->translate('messages.'.'Uploaded successfully','Uploaded successfully'),
                            ], $this->successStatus);
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()], $this->exceptionStatus); 
        }
        
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
                    $rules[$field->name] = 'required|max:100';
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
