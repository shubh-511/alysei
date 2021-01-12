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

class UserController extends CoreController
{

    /* 
        Get Registration Roles
        Get Registration Roles Except Super Admin, Admin, Impoters & Distributors
    */
    public function getRegistrationRoles(){

        try{

            $response_time = (microtime(true) - LARAVEL_START)*1000;
            $roles = Role::select('role_id','name','slug','display_name')->whereNotIn('slug',['super_admin','admin','importer','distributer'])->get();

            $importerRoles = Role::select('role_id','name','slug','display_name')->whereNotIn('slug',['super_admin','admin','Italian_F_and_B_Producers','voice_of_expert','travel_agencies','restaurents','voyagers'])->get();
            
            return response()->json(['success'=>true,'roles' =>$roles,'importer_roles'=>$importerRoles]); 

        }catch(\Exception $e){
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]]); 
        }

    }

    /*
     * Rendering Registration Form According to Roles
     * @Params $role_id
     */
    public function getRegistrationForm(Request $request,$role_id)
    {
        try{    
                $response_time = (microtime(true) - LARAVEL_START)*1000;
                $steps = Cache::get('registration_form');

                if($role_id && (env("cache") == false) || $steps==null){
                    $steps = [];
                    $roleFields = DB::table('user_field_map_roles')
                                      ->join('user_fields', 'user_fields.user_field_id', '=', 'user_field_map_roles.user_field_id')
                                      ->where("role_id","=",$role_id)
                                      ->orderBy("order","asc")
                                      ->get();

                    if($roleFields){
                        foreach ($roleFields as $key => $value) {
                            $data = [];
                            
                            //Check Fields has option
                            if($value->type !='text' && $value->type !='email' && $value->type !='password'){
                                
                                $value->values = $this->getUserFieldOptionParent($value->user_field_id);

                                if(!empty($value->values)){
                                    foreach ($value->values as $k => $v) {
                                        $data = $this->getUserFieldOptionsNoneParent($value->user_field_id,$v->user_field_option_id);
                                        if(!empty($data)){
                                            $value->values[$k]->values = $data;
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

        }
        
        return $fieldOptionData;    
        
    }

    /*
     * Register 
     * @params $request 
     */
    public function register(Request $request){

        try{

            $input = $request->all();

            if(!array_key_exists('role_id', $input)){
                return response()->json(['success'=>false,'errors' =>['role_id is required']], 200); 
            }else{
                if($input['role_id'] == ''){
                    return response()->json(['success'=>false,'errors' =>['role_id should not blank']], 200);
                }
            }

            $roleFields = $this->checkFieldsByRoleId($input['role_id']);

            if(count($roleFields) == 0){
                return response()->json(['success'=>false,'errors' =>['Sorry,There are no fields for current role_id']], 200);
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
                    $userData['account_enabled'] = 3;

                    if(array_key_exists('first_name',$inputData) && array_key_exists('last_name',$inputData)
                      ){
                        $userData['first_name'] = $inputData['first_name'];
                        $userData['last_name'] = $inputData['last_name'];
                    }

                    $user = User::create($userData); 

                    if($user){

                        foreach ($input as $key => $value) {
                            if($key == 'role_id'){
                                continue;
                            }

                            $data = [];
                            $data['user_field_id'] = $key;
                            $data['user_id'] = $user->id;
                            $data['value'] = $value;
                            DB::table('user_field_values')->insert($data);
                        }

                        return response()->json(['success' => true,
                                     'user' => $user,
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

    public function segregateInputData($input,$userFields){

        $inputData = [];

        foreach($userFields as $key => $field){
            if(array_key_exists($field->user_field_id, $input)){
                $inputData[$field->name] = $input[$field->user_field_id];
            }
        }

        return $inputData;

    }
}
