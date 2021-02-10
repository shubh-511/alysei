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
    public $unauthorisedStatus = 401;
    
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
                if(Auth::user()->role_id == 1)
                {
                    $mess = 'You are not allowed to login';
                    $message = $this->translate('messages.'.$mess,$mess);
                        
                    return response()->json(['error'=> $message], 401);  
                }
                else
                {
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
            } 
            else{ 

                $message = $this->translate('messages.'."login_failed","Login Failed");

                return response()->json(['error'=> $message], 401); 
            }
            
        }catch(\Exception $e){
            return response()->json(['success'=>$this->validationStatus,'errors' =>$e->getMessage()], $this->validationStatus);
        }
    }


    /***
    logout 
    ***/
    public function logout(Request $request)
    {
        $mes = 'Logout successfully';
        $message = $this->translate('messages.'.$mes,$mes);

        $token = $request->user()->token();
        $token->revoke();
        return response()->json(['success' => $this->successStatus,
                                 'data' => $message,
                                ], $this->successStatus); 
    }

    /***
    Alysei Progress 
    ***/
    public function alyseiProgress(Request $request)
    {
        try
        {
            $user = Auth::user();
            
            $userData = User::select('user_id','email','role_id','alysei_review','alysei_certification','alysei_recognition','alysei_qualitymark')->where('user_id', $user->user_id)->first();
            
            if(!empty($userData))
            {
                return response()->json(['success' => $this->successStatus,
                                         'data' => $userData,
                                        ], $this->successStatus);
            }
            else
            {
                return response()->json(['success'=>false,'errors' =>['exception' => 'Unauthorised']], $this->unauthorisedStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->unauthorisedStatus); 
        } 
        
    }
}
