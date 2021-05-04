<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CoreController;
use Modules\User\Entities\User; 
use App\Http\Traits\UploadImageTrait;
use Modules\Activity\Entities\UserPrivacy;
use Modules\Activity\Entities\ConnectFollowPermission;
use Modules\Activity\Entities\MapPermissionRole;
use Modules\User\Entities\Role;
use Carbon\Carbon;
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
                $this->searchUser();
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    
    /*
    * Searching User
    */
    public function searchUser()
    {
        $validator = Validator::make($request->all(), [ 
            'keyword' => 'required' 
        ]);

        if ($validator->fails()) { 
            return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
        }

        $users = User::select('user_id','role_id','name')
        ->where('email', 'LIKE', '%' . $request->keyword . '%')
        //->orderBy('name')
        ->get();

        if(count($users) > 0)
        {
            return response()->json(['success' => $this->successStatus,
                                 'data' => $roles
                                ], $this->successStatus);
        }
        else
        {
            $message = "No users found for this keyword";
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
        }
    }


    /*
     * Get Privacy data
     *
     */
    public function getPrivacyData()
    {
        try
        {
            $user = $this->user;
            $checkUser = User::where('user_id', $user->user_id)->first();
            $checkPrivacyDataExist = UserPrivacy::where('user_id', $user->user_id)->first();

            if(!empty($checkUser))
            {
                if(empty($checkPrivacyDataExist))
                {
                    $privacyData = ['user_id' => $user->user_id, 'allow_message_from' => 'anyone', 'who_can_view_age' => 'anyone', 'who_can_view_profile' => 'anyone', 'who_can_connect' => 'anyone'];

                    $messagePreference = ['user_id' => $user->user_id, 'private_messages' => '1', 'when_someone_request_to_follow' => '1', 'weekly_updates' => '1'];

                    return response()->json(['success' => $this->successStatus,
                                         'privacy_data' => $privacyData,
                                         'email_preference' => $messagePreference,
                                        ], $this->successStatus);
                }
                else
                {
                    $privacyData = ['user_id' => $user->user_id, 'allow_message_from' => $checkPrivacyDataExist->allow_message_from, 'who_can_view_age' => $checkPrivacyDataExist->who_can_view_age, 'who_can_view_profile' => $checkPrivacyDataExist->who_can_view_profile, 'who_can_connect' => $checkPrivacyDataExist->who_can_connect];

                    $messagePreference = ['user_id' => $user->user_id, 'private_messages' => $checkPrivacyDataExist->private_messages, 'when_someone_request_to_follow' => $checkPrivacyDataExist->when_someone_request_to_follow, 'weekly_updates' => $checkPrivacyDataExist->weekly_updates];
                    
                    return response()->json(['success' => $this->successStatus,
                                         'privacy_data' => $privacyData,
                                         'email_preference' => $messagePreference,
                                        ], $this->successStatus);
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
     * Save Email preference data
     * @Params $request
     */
    public function saveEmailPreference(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'private_messages' => 'required', 
                'when_someone_request_to_follow' => 'required',
                'weekly_updates' => 'required'
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
                    $privacy = new Privacy;
                    $privacy->user_id = $user->user_id;
                    $privacy->private_messages = $request->private_messages;
                    $privacy->when_someone_request_to_follow = $request->when_someone_request_to_follow;
                    $privacy->weekly_updates = $request->weekly_updates;
                    $privacy->save();
                }
                else
                {
                    UserPrivacy::where('user_id', $user->user_id)->update(['private_messages' => $request->private_messages, 'when_someone_request_to_follow' => $request->when_someone_request_to_follow, 'weekly_updates' => $request->weekly_updates]);

                }             
                $message = "Email preferences has been saved";
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
