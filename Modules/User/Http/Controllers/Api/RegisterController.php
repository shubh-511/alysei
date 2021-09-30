<?php

namespace Modules\User\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\CoreController;
use Modules\User\Entities\Role;
use Illuminate\Support\Facades\Auth; 
use Modules\User\Entities\User; 
use Modules\User\Entities\DeviceToken;
use App\Attachment;
use Modules\User\Entities\City;
use Validator;
use Str;
use DB;
use Kreait\Firebase\Factory;
use Cache;
use App\Events\Welcome;
use App\Events\VerifyEmail;

class RegisterController extends CoreController
{
    public $successStatus = 200;
    public $validationStatus = 422;
    public $exceptionStatus = 409;

    public function conn_firbase(){
        
        $factory = (new Factory)
        ->withServiceAccount('/home/ibyteworkshop/alyseiapi_ibyteworkshop_com/storage/credentials/firebase_credential.json')
        //->withDatabaseUri('https://alysei-a2f37-default-rtdb.firebaseio.com/');
        ->withDatabaseUri('https://alysei-add21-default-rtdb.firebaseio.com/');
        $database = $factory->createDatabase();    
        return $database;
    }

    public function addUserOrUpdateInFirebase($user_id, $name)
    {
        $data = $this->conn_firbase()->getReference('users/'.$user_id)
        ->update([
        'user_id' => $user_id,
        'name' => $name
        ]);

        return $data;

    }

    /* 
        Get Registration Roles
        Get Registration Roles Except Super Admin, Admin, Impoters & Distributors
    */
    public function getRoles(){

        try{

            $response_time = (microtime(true) - LARAVEL_START)*1000;
            $roles = Role::select('role_id','name','slug','display_name')->whereNotIn('slug',['super_admin','admin','importer','distributer'])->orderBy('order')->get();

            $importerRoles = Role::select('role_id','name','slug','display_name')->whereNotIn('slug',['super_admin','admin','Italian_F_and_B_Producers','voice_of_expert','travel_agencies','restaurents','voyagers'])->get();
            

            foreach ($roles as $key => $role) {
                $roles[$key]->name = $this->translate('messages.'.$roles[$key]->name,$roles[$key]->name);
                $roles[$key]->image = "public/images/roles/".$role->slug.".jpg";
            }

            foreach ($importerRoles as $key => $role) {
                if($importerRoles[$key]->name == "US Importers & Distributers")
                {
                    $importerRoles[$key]->name = $this->translate('messages.'.'Importer & Distributor','Importer & Distributor');
                }
                else
                {
                    $importerRoles[$key]->name = $this->translate('messages.'.$importerRoles[$key]->name,$importerRoles[$key]->name);
                }

                $importerRoles[$key]->image = "public/images/roles/".$role->slug.".png";
            }


            $data = ['roles'=> $roles,'importer_roles' => $importerRoles, 'title' => $this->translate('messages.'.'Select your role','Select your role'),
                'subtitle' => $this->translate('messages.'.'Join Alysei Today','Join Alysei Today'),
                'description' => $this->translate('messages.'.'Become an Alysei Member by signing up for the Free Trial Beta Version, Your access request will be subject to approval.','Become an Alysei Member by signing up for the Free Trial Beta Version, Your access request will be subject to approval.')];

            return response()->json(['success'=>$this->successStatus,'data' =>$data],$this->successStatus); 

        }catch(\Exception $e){
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }

    }

    /*
     * Get Walk Through Screens
     * @Params $request and $roleId
     */
    public function getWalkThroughScreens(Request $request,$roleId = 0){

        try{

            $response_time = (microtime(true) - LARAVEL_START)*1000;
            $screens = DB::table('walk_through_screens')
                        ->select('title','description','order','role_id','image_id')
                        ->where('role_id','=',$roleId)
                        ->orderBy('order','asc')->get();

            foreach ($screens as $key => $screen) {
                $screens[$key]->title = $this->translate('messages.'.$screen->title,$screen->title);
                $screens[$key]->description = $this->translate('messages.'.$screen->description,$screen->description);
                $attachment = Attachment::where('id', $screen->image_id)->first();
                if(!empty($attachment))
                {
                    $screens[$key]->image_id = $attachment->attachment_url;
                }
                else
                {
                    $screens[$key]->image_id = '';
                }
            }

            return response()->json(['success'=>$this->successStatus,'data' =>$screens,'response_time'=>$response_time],$this->successStatus); 

        }catch(\Exception $e){
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }

    }

    public function generate_unique_username($name)
    {
        $new_username   = $name;

        $query = \DB::table("users")->select('count(id) as user_count')
        ->where("name",'LIKE',"'%'".$new_username."'%'")
        ->get();
        $count = $query->user_count;

        if(!empty($count)) {
            $new_username = $new_username . $count;
        }

        return $new_username;
    }

    /*
     * Register 
     * @params $request 
     */
    public function register(Request $request){

        try{
            $stateId='';
            $input = $request->all();
            $rules = [];
            $rules['role_id'] = 'required';
            /*$rules['device_type'] = 'required';
            $rules['device_token'] = 'required';*/
            $validator = Validator::make($input, $rules);

            if ($validator->fails()) { 
                return response()->json(['success'=>$this->validationStatus,'errors'=>$validator->errors()->first()], $this->validationStatus);
            }

            $roleFields = $this->checkFieldsByRoleId($input['role_id']);

            if(count($roleFields) == 0){
                return response()->json(['success'=>$this->validationStatus,'errors' =>'Sorry,There are no fields for current role_id'], $this->validationStatus);
            }else{

                $rules = $this->makeValidationRules($roleFields);
                $inputData = $this->segregateInputData($input,$roleFields);
            }

            if(!empty($rules) && !empty($inputData)){
                
                $validator = Validator::make($inputData, $rules);

                if ($validator->fails()) { 

                    return response()->json(['success'=>$this->validationStatus,'errors'=>$validator->errors()->first()], $this->validationStatus);
                }

                if(array_key_exists('email',$inputData) && array_key_exists('password',$inputData)
                  ){

                    $userData = [];
                    $userData['email'] = $inputData['email'];
                    //$userData['name'] = $inputData['email'];
                    $userData['password'] = bcrypt($inputData['password']);
                    $userData['role_id'] = $input['role_id'];
                    if($input['role_id'] == 10)
                    {
                        $userData['account_enabled'] = "incomplete";
                        //$userData['otp'] = $this->generateOTP();
                        $userData['otp'] = "123456";
                    }
                    else
                    {
                        $userData['account_enabled'] = "active";
                    }
                    
                    if(array_key_exists('first_name',$inputData) && array_key_exists('last_name',$inputData)
                      ){
                        $userData['first_name'] = $inputData['first_name'];
                        $userData['last_name'] = $inputData['last_name'];

                        //$userData['name'] = ucwords(strtolower($inputData['first_name'])).' '.ucwords(strtolower($inputData['last_name']));
                    }

                    if(array_key_exists('timezone',$input) && array_key_exists('locale',$input)
                      ){
                        $userData['timezone'] = $input['timezone'];
                        $userData['locale'] = $input['locale'];
                    }
                    if(array_key_exists('vat_no',$inputData)){
                        $userData['vat_no'] = $inputData['vat_no'];
                        
                    }
                    if(array_key_exists('company_name',$inputData)){
                        $userData['company_name'] = $inputData['company_name'];
                        
                    }
                    if(array_key_exists('restaurant_name',$inputData)){
                        $userData['restaurant_name'] = $inputData['restaurant_name'];
                        
                    }
                    if(array_key_exists('country',$inputData)){
                        $userData['country_id'] = $inputData['country'];
                        
                    }
                    if(array_key_exists('state',$inputData)){
                        $userData['state'] = $inputData['state'];
                        
                    }

                    if(!empty($inputData['first_name']) && !empty($inputData['last_name']))
                    {
                        $userName = ucwords(strtolower($inputData['first_name']).' '.strtolower($inputData['first_name']));
                    }
                    elseif(!empty($inputData['company_name']))
                    {
                        $userName = $inputData['company_name'];
                    }
                    elseif(!empty($inputData['restaurant_name']))
                    {
                        $userName = $inputData['restaurant_name'];   
                    }

                    $new_username = strtolower(str_replace(' ', '_', $userName));
                    
                    $query = DB::table('users')->select(DB::raw('count(user_id) as user_count'))->where('name','LIKE','%'.$userName.'%')->first();
                    
                    $count = $query->user_count;

                    if(!empty($count)) {
                        $new_username = $new_username . $count;
                    }
                    else
                    {
                        $new_username;
                    }
                    $userData['name'] = $new_username;

                    $user = User::create($userData); 
                    
                    if($user){

                        foreach ($input as $key => $value) {
                            if($key == 'role_id' || $key == 'timezone' || $key == 'locale'){
                                continue;
                            }

                            $checkMultipleOptions = explode(',', $value);

                            if(count($checkMultipleOptions) == 1)
                            {
                                $data = [];
                                if(!empty($key))
                                {
                                    /*if($key == 28)
                                    {
                                        $stateId = $value;
                                    }
                                    if($key == 32)
                                    {
                                        $createdCityId = $this->createNewCityForApproval($key, $value, $stateId);
                                        $data['user_field_id'] = $key;
                                        $data['user_id'] = $user->user_id;
                                        $data['value'] = $createdCityId;
                                    }
                                    else
                                    {*/
                                        if($key == 13)
                                        {
                                            $data['table_name'] = 'countries';    
                                        }
                                        if($key == 28)
                                        {
                                            $data['table_name'] = 'states';    
                                        }
                                        if($key == 29)
                                        {
                                            $data['table_name'] = 'cities';    
                                        }
                                        $data['user_field_id'] = $key;
                                        $data['user_id'] = $user->user_id;
                                        $data['value'] = $value;
                                    //}
                                    DB::table('user_field_values')->insert($data);
                                }
                                
                            }else{

                                foreach($checkMultipleOptions as $option){
                                    $data = [];
                                    if(!empty($key))
                                    {
                                        $data['user_field_id'] = $key;
                                        $data['user_id'] = $user->user_id;
                                        $data['value'] = $option;
                                        DB::table('user_field_values')->insert($data);
                                    }
                                    
                                }
                            }
                            

                            
                        }
                        if(!empty($user->first_name) && !empty($user->last_name))
                        {
                            $userName = ucwords(strtolower($user->first_name).' '.strtolower($user->last_name));
                        }
                        elseif(!empty($user->company_name))
                        {
                            $userName = $user->company_name;
                        }
                        elseif(!empty($user->restaurant_name))
                        {
                            $userName = $user->restaurant_name;   
                        }

                        DeviceToken::where('user_id', $user->user_id)->delete();
                        if(isset($input['device_token']) && !empty($input['device_token']))
                        {
                            $deviceInfo = [];
                            $deviceInfo['user_id'] = $user->user_id;
                            $deviceInfo['device_type'] = $input['device_type'];
                            $deviceInfo['device_token'] = $input['device_token'];

                            DeviceToken::create($deviceInfo);
                        }
                          

                        if($input['role_id'] == 10)
                        {
                            //Send verify eMail OTP
                    
                            //event(new VerifyEmail($user->user_id));

                            $this->addUserOrUpdateInFirebase($user->user_id, $userName);
                            return response()->json(['success' => $this->successStatus,
                                        'message' => 'OTP has been sent on your email ID',
                                        'data' => $user->only($this->userFieldsArray)                  
                                    ], $this->successStatus);
                        }
                        else
                        {
                            $this->addUserOrUpdateInFirebase($user->user_id, $userName);
                            $token =  $user->createToken('alysei')->accessToken; 

                            //Send Welcome Mail
                    
                            //event(new Welcome($user->user_id));

                            return response()->json(['success' => $this->successStatus,
                                         'data' => $user->only($this->userFieldsArray),
                                         'token' => $token
                                        ], $this->successStatus);
                        }
                         

                    }else{
                        return response()->json(['success' => $this->exceptionStatus,
                                     'errors' => ['Something went wrong'],
                                    ], $this->exceptionStatus); 
                    }

                }
                
            }

            

        }catch(\Exception $e){
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()], $this->exceptionStatus); 
        }
        
    }


    /** 
     * Verify Otp api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function verifyOtp(Request $request) 
    { 
        try
        {
            $validator = Validator::make($request->all(), [  
                'email' => 'required|max:190|email', 
                'otp' => 'required',
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first()], $this->validationStatus);            
            }

            $userDetail = User::with('roles')->where('email', $request->email)->where('otp', $request->otp)->first();
            if(!empty($userDetail))
            {
                $userDetail->otp = null;
                $userDetail->account_enabled = 'active';
                $userDetail->save();


                $message = $this->translate("messages.OTP Verified","OTP Verified");
                Auth::loginUsingId($userDetail->id);
                //Auth::user()->roles;
                $token =  $userDetail->createToken('yss')->accessToken; 

                return response()->json(['success' => $this->successStatus,
                                     'data' => $userDetail->only($this->userFieldsArray),
                                     'token'=> $token
                                    ], $this->successStatus); 
                /*return response()->json(['success' => $this->successStatus,
                                         'message' => $message,
                                        ], $this->successStatus);  */
            }
            else
            {
                return response()->json(['success'=>$this->validationStatus,
                                        'message' => 'Invalid email or otp'
                                    ], $this->validationStatus); 
            }

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /** 
     * Resend Otp api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function resendOtp(Request $request) 
    { 
        try
        {
            $validator = Validator::make($request->all(), [  
                'email' => 'required|max:190|email', 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first()], $this->validationStatus);            
            }

            $userDetail = User::where('email', $request->email)->first();
            if(!empty($userDetail))
            {
                //$userDetail->otp = $this->generateOTP();
                $userDetail->otp = "123456";
                $userDetail->save();


                return response()->json(['success' => $this->successStatus,
                                         'message' => 'OTP has been sent!'
                                        ], $this->successStatus); 
            }
            else
            {
                return response()->json(['success'=>$this->validationStatus,
                                        'errors' => 'Invalid email ID'
                                    ], $this->validationStatus); 
            }

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Rendering Registration Form According to Roles
     * @Params $role_id
     */
    public function getRegistrationFormFields(Request $request,$role_id)
    {
        try{    
                $response_time = (microtime(true) - LARAVEL_START)*1000;
                $steps = Cache::get('registration_form');

                if($role_id && (env("cache") == false) || $steps==null){
                    $steps = [];
                    $roleFields = DB::table('user_field_map_roles')
                                      ->join('user_fields', 'user_fields.user_field_id', '=', 'user_field_map_roles.user_field_id')
                                      ->where("role_id","=",$role_id)
                                      ->where("display_on_registration","=",'true')
                                      ->where("conditional","=",'no')
                                      ->orderBy("order","asc")
                                      ->get();

                    $importerRoles = Role::select('role_id','name','slug','display_name')->whereNotIn('slug',['super_admin','admin','Italian_F_and_B_Producers','voice_of_expert','travel_agencies','restaurents','voyagers'])->get();                  

                    if($roleFields){
                        foreach ($roleFields as $key => $value) {
                            $data = [];
                            
                            //Set Locale
                            if($role_id == 3 && $value->user_field_id == 28)
                            {
                                $roleFields[$key]->title = $this->translate('messages.'.'Region','Region');
                            }
                            if($role_id == 6 && $value->user_field_id == 28)
                            {
                                $roleFields[$key]->title = $this->translate('messages.'.'State','State');
                            }
                            
                            $roleFields[$key]->title = $this->translate('messages.'.$value->title,$value->title);

                            //Check Fields has option
                            if($value->type !='text' && $value->type !='email' && $value->type !='password'){
                                
                                $value->options = $this->getUserFieldOptionParent($value->user_field_id);

                                if(!empty($value->options)){

                                    foreach ($value->options as $k => $oneDepth) {

                                            $value->options[$k]->option = $this->translate('messages.'.$oneDepth->option,$oneDepth->option);

                                            //Check Option has any Field Id
                                            $checkRow = DB::table('user_field_maps')->where('user_field_id','=',$value->user_field_id)->where('role_id','=',$role_id)->first();

                                            if($checkRow){
                                                $value->parentId = $checkRow->option_id;
                                            }

                                            // if($checkRow){
                                            //     $field = $this->getUserField($checkRow->child_id);

                                            //     if($field){

                                            //         $field->name = $this->translate('messages.'.$field->title,$field->title);
                                            //         $value->options[$k]->child = $field;

                                            //         $data = $this->getUserFieldOptionParent($field->user_field_id);

                                            //         $value->options[$k]->child->options = $data;

                                            //     }
                                            // }else{

                                                $data = $this->getUserFieldOptionsNoneParent($value->user_field_id,$oneDepth->user_field_option_id);

                                                $value->options[$k]->options = $data;

                                                
                                                foreach ($value->options[$k]->options as $optionKey => $optionValue) {

                                                    $options = $this->getUserFieldOptionsNoneParent($optionValue->user_field_id,$optionValue->user_field_option_id);

                                                    $value->options[$k]->options[$optionKey]->options = $options;
                                                }  
                                            //}

                                    }
                                }
                            }// End Check Fields has option

                            $steps[$value->step][] = $value;
                        }
                    }



                    if($role_id == 6){

                        foreach ($importerRoles as $key => $role) {
                            if($importerRoles[$key]->name == "US Importers & Distributers")
                            {
                                $importerRoles[$key]->name = $this->translate('messages.'.'Importer & Distributor','Importer & Distributor');
                            }
                            else
                            {
                                $importerRoles[$key]->name = $this->translate('messages.'.$importerRoles[$key]->name,$importerRoles[$key]->name);
                            }
                            
                            $importerRoles[$key]->image = env("APP_URL")."/images/roles/".$role->slug.".png";
                        }

                        $newArray =  [['type' => 'select','name' => 'role_id','title' => 'Select Role','required' => 'yes','Placeholder'=>'Select Role','options' => $importerRoles]];

                        array_splice( $steps['step_2'], -1, 0, $newArray );
                    }

                    Cache::forever('registration_form', $steps);
                 

                }

                return response()->json(['success'=>$this->successStatus,'data' =>$steps,'response_time'=>$response_time], $this->successStatus); 
                
                
        }catch(\Exception $e){
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()], $this->exceptionStatus); 
        }
    }

    /*
     * Get User Field
     * @params $fieldId = user_field_id 
    */
    public function getUserField($fieldId){

        $fieldData = DB::table('user_fields')
                    ->where('user_field_id','=',$fieldId)
                    ->first();
        return $fieldData;    
        
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
     * Check Fields based on role id
     * @Params $roleId
     */

    public function checkFieldsByRoleId($roleId){

        $roleFields = DB::table('user_field_map_roles')
                                  ->join('user_fields', 'user_fields.user_field_id', '=', 'user_field_map_roles.user_field_id')
                                  ->where("role_id","=",$roleId)
                                  ->where("user_fields.display_on_registration","=",true)
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

            }else if($field->name == 'password' && $field->required == 'yes'){

                $rules[$field->name] = 'required|min:8';

            }else if($field->name == 'first_name' && $field->required == 'yes'){

                $rules[$field->name] = 'required|min:3';

            }else if($field->name == 'last_name' && $field->required == 'yes'){

                $rules[$field->name] = 'required|min:3';

            }else {

                if($field->required == 'yes'){
                    $rules[$field->name] = 'required';
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
     * Generate OTP
     */ 
    public function generateOTP(){
        $otp = mt_rand(100000,999999);
        return $otp;
    }

    /*
     * Create new city for admin approval
     */ 
    public function createNewCityForApproval($key, $value, $stateId)
    {
        $newCity = new City;
        $newCity->name = $value;
        $newCity->state_id = $stateId;
        $newCity->save();

        return $newCity->id;
    }
}
