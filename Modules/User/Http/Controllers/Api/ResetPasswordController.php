<?php

namespace Modules\User\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Http\Controllers\CoreController;
use App\Events\ForgotPassword;
use Illuminate\Support\Facades\Auth; 
use Modules\User\Entities\User; 
use Validator;
use DB;
use Cache;
use Hash;

class ResetPasswordController extends CoreController
{
    public $successStatus = 200;

    /** 
     * Forgot password api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function forgotPassword(Request $request) 
    {
        try
        {
            $validator = Validator::make($request->all(), [  
                'email' => 'required|email', 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()], $this->successStatus);            
            }

            $userDetail = User::where('email', $request->email)->first();
            if(!empty($userDetail))
            {
                
                $forgotKey = rand(1111,9999);

                $userDetail->otp = $forgotKey;
                $userDetail->save();

                //Send Otp Over Mail
                
                event(new ForgotPassword($userDetail->user_id,$forgotKey));

                $message = $this->translate('messages.Reset password otp has been sent on your email','Reset password otp has been sent on your email');

                return response()->json(['success' => true,
                                         'message' => $message,
                                         //'otp' => $forgotKey,
                                        ], $this->successStatus); 
            }
            else
            {
                $message = $this->translate("messages.Invalid email","Invalid email");
                return response()->json(['success'=>false,'errors' =>['exception' => [$message]]], $this->successStatus); 
            }

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->successStatus); 
        } 
    }

    /** 
     * Verify OTP 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function verifyForgotPasswordOtp(Request $request) 
    { 
        try
        {
            $validator = Validator::make($request->all(), [  
                'email' => 'required|email', 
                'otp' => 'required',
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()], $this->successStatus);            
            }

            $userDetail = User::where('email', $request->email)->where('otp', $request->otp)->first();
            if(!empty($userDetail))
            {
                $userDetail->otp = null;
                $userDetail->save();

                $message = $this->translate("messages.OTP Verified","OTP Verified");

                return response()->json(['success' => true,
                                         'message' => $message,
                                        ], $this->successStatus); 
            }
            else
            {
                $message = $this->translate("messages.Invalid OTP","Invalid OTP");
                return response()->json(['success'=>false,'errors' =>['opt_failed' => [$message]]], $this->successStatus); 
            }

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->successStatus); 
        }
    }

    /** 
     * Reset Password after Otp verified api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function resetPassword(Request $request) 
    { 
        try
        {
            $validator = Validator::make($request->all(), [  
                'email' => 'required|email', 
                'password' => 'required|min:8', 
                'confirm_password' => 'required|same:password',
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()], $this->successStatus);            
            }

            $userDetail = User::where('email', $request->email)->first();
            if(!empty($userDetail))
            {
                $userDetail->password = bcrypt($request->password); 
                $userDetail->save();

                $message = $this->translate("passwords.reset");

                return response()->json(['success' => true,
                                         'message' => $message,
                                         'user' => $userDetail,
                                        ], $this->successStatus); 
            }
            else
            {
                return response()->json(['success'=>false,'errors' =>['exception' => ['The token has been expired']]], $this->successStatus); 
            }

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->successStatus); 
        }
    }

    /** 
     * Change Password
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function changePassword(Request $request) 
    {  
        try 
        {
            $validator = Validator::make($request->all(), [  
                'old_password' => 'required', 
                'new_password' => 'required', 
                //'confirm_password' => 'required|same:new_password',
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()], $this->successStatus);            
            }

            $userId = Auth()->user()->user_id;
            if(!empty($userId))
            {

                $userUpdate = User::where('user_id', $userId)->first();

                if (Hash::check($request->old_password, $userUpdate->password)) {
                    $userUpdate->password = bcrypt($request->new_password); 
                    $userUpdate->save();


                    return response()->json(['success' => true,
                                             'message' => $this->translate("passwords.reset"),
                                            ], $this->successStatus);
                }else{
                    return response()->json(['success'=>false,'errors' =>['failed' => [$this->translate("passwords.old_password")]]], $this->successStatus); 
                }

                 
            }
            else
            {
                return response()->json(['success'=>false,'errors' =>['failed' => [$this->translate("passwords.user")]]], $this->successStatus); 
            }

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->successStatus); 
        }
    }
}
