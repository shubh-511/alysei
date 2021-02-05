<?php

namespace Modules\User\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\CoreController;
use Illuminate\Support\Facades\Auth; 
use Modules\User\Entities\User; 
use Validator;
use DB;
use Cache;
use Hash;

class LoginController extends CoreController
{
    public $successStatus = 200;
    public $validationStatus = 422;
    public $exceptionStatus = 409;
    
    public $userFieldsArray = ['user_id', 'name', 'email','first_name','last_name','middle_name','phone','postal_code','last_login_date','roles'];
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
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            //Check Auth 
            if (Auth::attempt(array('email' => $request->getUser(), 'password' => $request->getPassword()), true)){
                
                $user = Auth::user(); 
                
                if($user->account_enabled == 'active' || $user->account_enabled == 'incomplete')
                {
                    Auth::user()->roles;
                    $token =  $user->createToken('yss')->accessToken; 

                    return response()->json(['success' => $this->successStatus,
                                         'data' => $user->only($this->userFieldsArray),
                                         'token'=> $token
                                        ], $this->successStatus); 
                }else{

                    $message = $this->translate('messages.'.$user->account_enabled,$user->account_enabled);
                    
                    return response()->json(['error'=> $message], 401);  
                }
            } 
            else{ 

                $message = $this->translate('messages.'."login_failed","Login Failed");

                return response()->json(['error'=> $message], 401); 
            }
            
        }catch(\Exception $e){
            return response()->json(['success'=>$this->validationStatus,'errors' =>$e->getMessage()], $this->validationStatus);
        }
    }
}
