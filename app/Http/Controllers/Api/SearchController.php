<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CoreController;
use Modules\User\Entities\User; 
use Modules\User\Entities\UserSelectedHub; 
use Modules\User\Entities\Hub;
use App\Http\Traits\UploadImageTrait;
use Modules\Activity\Entities\UserPrivacy;
use Modules\Activity\Entities\ConnectFollowPermission;
use Modules\Activity\Entities\MapPermissionRole;
use Modules\User\Entities\Role;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Auth; 
use Validator;
//use App\Events\UserRegisterEvent;

class SearchController extends CoreController
{
    use UploadImageTrait;
    public $successStatus = 200;
    public $validationStatus = 422;
    public $exceptionStatus = 409;
    public $unauthorisedStatus = 401;

    public $user = '';

    public function __construct(){

        $this->middleware(function ($request, $next) {

            $this->user = Auth::user();
            return $next($request);
        });
    }


    /*
     * Search user and hubs
     *
     */
    public function search(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'search_type' => 'required' 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            if($request->search_type == 1)
            {
                $validateSearchType = Validator::make($request->all(), [ 
                    'keyword' => 'required' 
                ]);

                if ($validateSearchType->fails()) { 
                    return response()->json(['errors'=>$validateSearchType->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
                }

                return $this->searchGlobalUsers($request->keyword);   
            }
            elseif($request->search_type == 2)
            {
                $validateSearchType = Validator::make($request->all(), [ 
                    'role_id' => 'required' 
                ]);

                if ($validateSearchType->fails()) { 
                    return response()->json(['errors'=>$validateSearchType->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
                }

                $this->searchUserByRoles($roleId, $request);
            }
            else
            {
                $message = "Invalid search type";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
    * Search user by roles
    */
    public function searchUserByRoles($roleId, $request)
    {
        $if(empty($request->user_type))
        {
            
        }
        $users = User::select('user_id','role_id','name','email','company_name','restaurant_name','avatar_id')->with('avatar_id')
        ->where('email', 'LIKE', '%' . $keyWord . '%')
        ->paginate(10);

        if(count($users) > 0)
        {
            return response()->json(['success' => $this->successStatus,
                                 'data' => $users
                                ], $this->successStatus);
        }
        else
        {
            $message = "No users found for this keyword";
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
        }
    }

    
    /*
    * Searching User
    */
    public function searchGlobalUsers($keyWord)
    {
        $users = User::select('user_id','role_id','name','email','company_name','restaurant_name','avatar_id')->with('avatar_id')
        ->where('email', 'LIKE', '%' . $keyWord . '%')
        ->paginate(10);

        if(count($users) > 0)
        {
            return response()->json(['success' => $this->successStatus,
                                 'data' => $users
                                ], $this->successStatus);
        }
        else
        {
            $message = "No users found for this keyword";
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
        }        
    }


    /*
     * Get list of hubs seleted by user
     *
     */
    public function getMySelectedHubs()
    {
        try
        {
            $user = $this->user;
            $checkUser = User::where('user_id', $user->user_id)->first();
            $myHubs = UserSelectedHub::where('user_id', $user->user_id)->get();
            if(!empty($checkUser))
            {
                if(count($myHubs) > 0)
                {
                    $myHubs = $myHubs->pluck('hub_id')->toArray();
                    $hubs = Hub::select('id','title')->whereIn('id', $myHubs)->where('status', '1')->get();
                    if(count($hubs) > 0)
                    {
                        return response()->json(['success' => $this->successStatus,
                                     'hubs' => $hubs
                                    ], $this->successStatus);
                    }
                    else
                    {
                        $message = "No hubs available";
                        return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                    }
                }
                else
                {
                    $message = "You have not selected any hubs";
                    return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                }
            }
            else
            {
                $message = "Invalid user";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }               
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Get pickup or delivery
     *
     */
    public function getPickupOrDelivery()
    {
        try
        {
            $user = $this->user;
            $role_id = $user->role_id;
            $steps = [];
            $checkUser = User::where('user_id', $user->user_id)->first();
            
            $roleFields = DB::table('user_fields')
                                      ->whereIn("user_field_id", [9,21,22])
                                      ->get();
            if(!empty($roleFields))
            {
                foreach ($roleFields as $key => $value)
                {
                    $roleFields[$key]->title = $this->translate('messages.'.$value->title,$value->title);

                    //Check Fields has option
                    if($value->type !='text' && $value->type !='email' && $value->type !='password')
                    {
                        
                        $value->options = $this->getUserFieldOptionParent($value->user_field_id);

                        if(!empty($value->options))
                        {
                            foreach ($value->options as $k => $oneDepth) 
                            {

                                $value->options[$k]->option = $this->translate('messages.'.$oneDepth->option,$oneDepth->option);

                                //Check Option has any Field Id
                                $checkRow = DB::table('user_field_maps')->where('user_field_id','=',$value->user_field_id)->where('role_id', 9)->first();

                                if($checkRow){
                                    $value->parentId = $checkRow->option_id;
                                }

                                $data = $this->getUserFieldOptionsNoneParent($value->user_field_id,$oneDepth->user_field_option_id);

                                $value->options[$k]->options = $data;

                                
                                foreach ($value->options[$k]->options as $optionKey => $optionValue) 
                                {
                                    $options = $this->getUserFieldOptionsNoneParent($optionValue->user_field_id,$optionValue->user_field_option_id);

                                    $value->options[$k]->options[$optionKey]->options = $options;
                                }  

                            }
                        }
                    }// End Check Fields has option

                    $steps[] = $value;
                }
                return response()->json(['success'=>$this->successStatus,'data' =>$steps], $this->successStatus); 
            }
            else
            {
                $message = "The field does not exist";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }               
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Get list of field values
     *
     */
    public function getFieldValues($fieldId)
    {
        try
        {
            $user = $this->user;
            $checkUser = User::where('user_id', $user->user_id)->first();
            
            $roleFields = DB::table('user_fields')
                                      ->where("user_field_id","=", $fieldId)
                                      ->first();
            if(!empty($checkUser))
            {
                if($roleFields->type !='text' && $roleFields->type !='email' && $roleFields->type !='password')
                {
                                
                    $roleFields->options = $this->getUserFieldOptionParent($roleFields->user_field_id);

                    if(!empty($roleFields->options)){

                        foreach ($roleFields->options as $k => $oneDepth) 
                        {

                            $roleFields->options[$k]->option = $this->translate('messages.'.$oneDepth->option,$oneDepth->option);

                            $data = $this->getUserFieldOptionsNoneParent($roleFields->user_field_id,$oneDepth->user_field_option_id);

                            $roleFields->options[$k]->options = $data;

                            
                            foreach ($roleFields->options[$k]->options as $optionKey => $optionValue) 
                            {
                                $options = $this->getUserFieldOptionsNoneParent($optionValue->user_field_id,$optionValue->user_field_option_id);

                                $roleFields->options[$k]->options[$optionKey]->options = $options;
                            }  
                                
                        }
                    }
                    
                    return response()->json(['success' => $this->successStatus,
                                     'data' => $roleFields
                                    ], $this->successStatus);
                }
                else
                {
                    $message = "Undefined field type";
                    return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                }
            }
            else
            {
                $message = "The field does not exist";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }               
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /* Get All Fields Option who are child
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
     * Save privacy data
     * @Params $request
     */
    public function savePrivacy(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'allow_message_from' => 'required', 
                'who_can_view_age' => 'required',
                'who_can_view_profile' => 'required',
                'who_can_connect' => 'required',
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $checkUser = User::where('user_id', $user->user_id)->first();
            $checkPrivacyDataExist = UserPrivacy::where('user_id', $user->user_id)->first();

            if(!empty($checkUser))
            {
                if(empty($checkPrivacyDataExist))
                {
                    $privacy = new UserPrivacy;
                    $privacy->user_id = $user->user_id;
                    $privacy->allow_message_from = $request->allow_message_from;
                    $privacy->who_can_view_age = $request->who_can_view_age;
                    $privacy->who_can_view_profile = $request->who_can_view_profile;
                    $privacy->who_can_connect = $request->who_can_connect;
                    $privacy->save();
                }
                else
                {
                    UserPrivacy::where('user_id', $user->user_id)->update(['allow_message_from' => $request->allow_message_from, 'who_can_view_age' => $request->who_can_view_age, 'who_can_view_profile' => $request->who_can_view_profile, 'who_can_connect' => $request->who_can_connect]);

                }             
                    $message = "Privacy settings has been saved";
                    return response()->json(['success' => $this->successStatus,
                                         'message' => $this->translate('messages.'.$message,$message),
                                        ], $this->successStatus);
            }
            else
            {
                $message = "Invalid user";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }


    
    /*
     * Check Role Permission
     * @Params $userRole, $FollowingUserRole
     */

    public function checkRolePermission($userRole, $FollowingUserRole)
    {
        $status = [];
        $permission = ConnectFollowPermission::where("role_id", $userRole)->where('permission_type', '2')->first();

        if(!empty($permission))
        {
            $permissionRole = MapPermissionRole::where("connect_follow_permission_id", $permission->connect_follow_permission_id)->get();
            $checkRoleWisePermission = $permissionRole->pluck('role_id')->toArray();

            if(in_array($FollowingUserRole, $checkRoleWisePermission))
            {
                $status = [$this->translate('messages.'."Success","Success"), 0];
            }
            else
            {
                $status = [$this->translate('messages.'."Failed","Failed"), 1];
            }
            
        }
        else
        {
            $status = [$this->translate('messages.'."You are not authorized to follow this user","You are not authorized to follow this user"), 2];
        }
        
        return $status;
    }

   
}
