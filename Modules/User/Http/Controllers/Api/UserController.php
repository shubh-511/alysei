<?php

namespace Modules\User\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Http\Controllers\CoreController;
use DB;

class UserController extends CoreController
{
    public function getRegistrationForm(Request $request,$role_id)
    {
        if($role_id){

            $steps = [];

            $roleFields = DB::table('user_field_map_roles')
                              ->join('user_fields', 'user_fields.user_field_id', '=', 'user_field_map_roles.user_field_id')
                              ->where("role_id","=",$role_id)
                              ->orderBy("order","asc")
                              ->get();

            if($roleFields){
                foreach ($roleFields as $key => $value) {
                    $data = [];
                    if($value->type !='text' && $value->type !='email' && $value->type !='password'){
                        $value->values = DB::table('user_field_options')
                            ->where('user_field_id','=',$value->user_field_id)
                            ->where('parent','=',0)
                            ->get();

                        if(!empty($value->values)){
                            foreach ($value->values as $k => $v) {
                                $data = DB::table('user_field_options')
                                ->where('user_field_id','=',$value->user_field_id)
                                ->where('parent','=',$v->user_field_option_id)
                                ->get();                                

                                if(!empty($data)){
                                    $value->values[$k]->values = $data;
                                }
                            }
                        }
                        
                    }


                    $steps[$value->step][] = $value;
                }
            }

            dd($steps);

            echo json_encode($steps);exit;
        }
    }

    
}
