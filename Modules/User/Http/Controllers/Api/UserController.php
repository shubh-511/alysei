<?php

namespace Modules\User\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Http\Controllers\CoreController;
use Modules\User\Entities\Role;
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
            return response()->json(['success'=>true,'roles' =>$roles]); 

        }catch(\Exception $e){
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]]); 
        }

    }

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

        $data = [];
        
        if($fieldId > 0){
            $data = DB::table('user_field_options')
                    ->where('user_field_id','=',$fieldId)
                    ->where('parent','=',0)
                    ->get();

        }
        
        return $data;    
        
    }

    /*
     * Get All Fields Option who are child
     * @params $user_field_id and $user_field_option_id
     */
    public function getUserFieldOptionsNoneParent($fieldId, $parentId){

        $data = [];
        
        if($fieldId > 0 && $parentId > 0){
            $data = DB::table('user_field_options')
                ->where('user_field_id','=',$fieldId)
                ->where('parent','=',$parentId)
                ->get();                                

        }
        
        return $data;    
        
    }
}
