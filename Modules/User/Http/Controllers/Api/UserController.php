<?php

namespace Modules\User\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Http\Controllers\CoreController;
use Modules\User\Entities\Role;
use Illuminate\Support\Facades\Auth; 
use Modules\User\Entities\User; 
use Validator;
use DB;
use Cache;
use Lang;


class UserController extends CoreController
{
    public $successStatus = 200;

    public $userFieldsArray = ['user_id', 'name', 'email','first_name','last_name','middle_name','phone','postal_code','last_login_date','roles'];
    /* 
     * User Info
     */
    public function userinfo(){
        $user = Auth::user(); 
        Auth::user()->roles;
        return response()->json(['success' => true,
                                 'user' => $user->only($this->userFieldsArray),
                                ], $this->successStatus);  
    }

    /** 
     * Login
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function login(Request $request){
        
        try{

            $input = [];

            if($request->getUser() || $request->getPassword()){
                $input['name']  = $request->getUser();
                $input['password']  = $request->getPassword();
            }
            
            $validator = Validator::make($input, [ 
                'name' => 'required', 
                'password' => 'required'
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors(),'success' => false], $this->successStatus);
            }

            //Check Auth 
            if (Auth::attempt(array('email' => $request->getUser(), 'password' => $request->getPassword()), true)){
                
                $user = Auth::user(); 
                
                if($user->account_enabled == 'active' || $user->account_enabled == 'incomplete')
                {
                    Auth::user()->roles;
                    $token =  $user->createToken('yss')->accessToken; 

                    return response()->json(['success' => true,
                                         'user' => $user->only($this->userFieldsArray),
                                         'token'=> $token
                                        ], $this->successStatus); 
                }else{

                    $message = trans('messages.'.$user->account_enabled,[$user->account_enabled]);
                    
                    return response()->json(['error'=> ['login_failed' => [$message]]], 401);  
                }
            } 
            else{ 

                $message = trans('messages.'."login_failed",["Login Failed"]);

                return response()->json(['error'=> ['login_failed' => [$message]]], 401); 
            }
            
        }catch(\Exception $e){
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->successStatus);
        }
    }
    /*
     * Register 
     * @params $request 
     */
    public function register(Request $request){

        try{

            $input = $request->all();
            $rules = [];
            $rules['role_id'] = 'required|digits:2';
            $validator = Validator::make($input, $rules);

            if ($validator->fails()) { 
                return response()->json(['sucess'=>false,'errors'=>$validator->errors()], 200);
            }

            $roleFields = $this->checkFieldsByRoleId($input['role_id']);

            if(count($roleFields) == 0){
                return response()->json(['success'=>false,'errors' =>['role_id'=>['Sorry,There are no fields for current role_id']]], 200);
            }else{

                $rules = $this->makeValidationRules($roleFields);
                $inputData = $this->segregateInputData($input,$roleFields);
            }

            if(!empty($rules) && !empty($inputData)){
                
                $validator = Validator::make($inputData, $rules);

                if ($validator->fails()) { 
                    return response()->json(['sucess'=>false,'errors'=>$validator->errors()], 200);
                }

                if(array_key_exists('email',$inputData) && array_key_exists('password',$inputData)
                  ){

                    $userData = [];
                    $userData['email'] = $inputData['email'];
                    $userData['name'] = $inputData['email'];
                    $userData['password'] = bcrypt($inputData['password']);
                    $userData['role_id'] = $input['role_id'];

                    if($input['role_id'] == 10){
                        $userData['account_enabled'] = "active";    
                    }else{
                        $userData['account_enabled'] = "incomplete";
                    }
                    

                    if(array_key_exists('first_name',$inputData) && array_key_exists('last_name',$inputData)
                      ){
                        $userData['first_name'] = $inputData['first_name'];
                        $userData['last_name'] = $inputData['last_name'];
                    }

                    if(array_key_exists('timezone',$input) && array_key_exists('locale',$input)
                      ){
                        $userData['timezone'] = $input['timezone'];
                        $userData['locale'] = $input['locale'];
                    }

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
                                $data['user_field_id'] = $key;
                                $data['user_id'] = $user->user_id;
                                $data['value'] = $value;
                            }else{

                                foreach($checkMultipleOptions as $option){
                                    $data = [];
                                    $data['user_field_id'] = $key;
                                    $data['user_id'] = $user->user_id;
                                    $data['value'] = $option;
                                }
                            }
                            

                            DB::table('user_field_values')->insert($data);
                        }

                        $token =  $user->createToken('alysei')->accessToken; 

                        return response()->json(['success' => true,
                                     'user' => $user,
                                     'token' => $token
                                    ], 200); 

                    }else{
                        return response()->json(['success' => false,
                                     'errors' => ['Something went wrong'],
                                    ], 200); 
                    }

                }
                
            }

            

        }catch(\Exception $e){
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], 200); 
        }
        
    }

    /* 
        Get Registration Roles
        Get Registration Roles Except Super Admin, Admin, Impoters & Distributors
    */
    public function getRoles(){

        try{

            $response_time = (microtime(true) - LARAVEL_START)*1000;
            $roles = Role::select('role_id','name','slug','display_name')->whereNotIn('slug',['super_admin','admin','importer','distributer'])->get();

            $importerRoles = Role::select('role_id','name','slug','display_name')->whereNotIn('slug',['super_admin','admin','Italian_F_and_B_Producers','voice_of_expert','travel_agencies','restaurents','voyagers'])->get();
            

            foreach ($roles as $key => $role) {
                $roles[$key]->name = trans('messages.'.$roles[$key]->name,[$roles[$key]->name]);
                $roles[$key]->image = env("APP_URL")."/images/roles/".$role->slug.".png";
            }

            foreach ($importerRoles as $key => $role) {
                $importerRoles[$key]->name = trans('messages.'.$importerRoles[$key]->name,[$importerRoles[$key]->name]);
                $importerRoles[$key]->image = env("APP_URL")."/images/roles/".$role->slug.".png";
            }

            return response()->json(['success'=>true,'roles' =>$roles,'importer_roles'=>$importerRoles]); 

        }catch(\Exception $e){
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]]); 
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
                                      ->where("user_fields.contiditional","=","no")
                                      ->orderBy("order","asc")
                                      ->get();

                    if($roleFields){
                        foreach ($roleFields as $key => $value) {
                            $data = [];
                            
                            //Set Locale

                            $roleFields[$key]->title = trans('messages.'.$value->name,[$value->name]);

                            //Check Fields has option
                            if($value->type !='text' && $value->type !='email' && $value->type !='password'){
                                
                                $value->values = $this->getUserFieldOptionParent($value->user_field_id);

                                if(!empty($value->values)){

                                    foreach ($value->values as $k => $v) {

                                        $value->values[$k]->option = trans('messages.'.$v->option,[$v->option]);

                                        $data = $this->getUserFieldOptionsNoneParent($value->user_field_id,$v->user_field_option_id);

                                        $value->values[$k]->values = $data;

                                        foreach ($value->values[$k]->values as $optionKey => $optionValue) {

                                            $options = $this->getUserFieldOptionsNoneParent($optionValue->user_field_id,$optionValue->user_field_option_id);

                                            $value->values[$k]->values[$optionKey]->values = $options;
                                        }
                                    }
                                }
                            }// End Check Fields has option


                            $steps[$value->step][] = $value;
                        }
                    }

                    Cache::forever('registration_form', $steps);
                    
                }

                return response()->json(['success'=>true,'steps' =>$steps,'response_time'=>$response_time]); 
                
                
        }catch(\Exception $e){
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]]); 
        }
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
                $fieldOptionData[$key]->option = trans('messages.'.$option->option,[$option->option]);
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
                $fieldOptionData[$key]->option = trans('messages.'.$option->option,[$option->option]);
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
     * Get Field Options
     * @Params $request
     */
    public function getFieldOption(Request $request){

        $inputData = [];

        foreach($userFields as $key => $field){
            if(array_key_exists($field->user_field_id, $input)){
                $inputData[$field->name] = $input[$field->user_field_id];
            }
        }

        return $inputData;

    }
}
