<?php

namespace Modules\User\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Http\Controllers\CoreController;
use Modules\User\Entities\Role;
use Modules\User\Entities\FeaturedProduct;
use Illuminate\Support\Facades\Auth; 
use Modules\User\Entities\User; 
use Validator;
use DB;
use Cache;
use App\Events\Welcome;

class UserController extends CoreController
{
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
                return $loggedInUser->role_id;
                $userDetails = $loggedInUser->only(['name', 'email','display_name','locale']);

                $userFieldInfo = [];

                foreach($userDetails as $key => $user){

                    $userFieldInfo[$key] = ["title" => $this->translate("messages.".$key,$key),"value"=>$user];
                }

                return response()->json(['success' => $this->successStatus,
                                 'data' => $userFieldInfo,
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
                $requestFields = $request->params;

                $rules = $this->validateData($requestFields);
                
                $validator = Validator::make($requestFields, $rules);


                if ($validator->fails()) { 
                    return response()->json(['errors'=>$validator->errors(),'success' => $this->validationStatus], $this->validationStatus);
                }
                
                $user = User::where('user_id','=',$this->user->user_id)->first();
                $user->name = $requestFields['name'];
                $user->display_name = $requestFields['display_name'];
                $user->locale = $requestFields['locale'];
                $user->save();

                if(count($requestFields['featured_products']) > 0)
                {
                    foreach($requestFields['featured_products'] as $featuredProduct)
                    {
                        $featProduct = new FeaturedProduct;
                        $featProduct->product_title = $featuredProduct['title'];
                        $featProduct->product_description = $featuredProduct['product_description'];
                        $featProduct->product_tags = $featuredProduct['product_tags'];
                        $featProduct->save();
                    }
                }

                return response()->json(['success' => $this->successStatus,
                                 'data' => $user,
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
    

}
