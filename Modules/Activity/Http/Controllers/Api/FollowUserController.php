<?php

namespace Modules\Activity\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CoreController;
use Modules\User\Entities\User; 
use Modules\User\Entities\DeviceToken; 
use App\Http\Traits\UploadImageTrait;
use App\Http\Traits\NotificationTrait;
use Modules\Activity\Entities\ActivityAction;
use Modules\Activity\Entities\Follower;
use Modules\Activity\Entities\ActivityLike;
use Modules\Activity\Entities\ActivityActionType;
use Modules\Activity\Entities\ActivityAttachment;
use Modules\Activity\Entities\ActivityAttachmentLink;
use App\Notification;
use Modules\Activity\Entities\ConnectFollowPermission;
use Modules\Activity\Entities\MapPermissionRole;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth; 
use Validator;
//use App\Events\UserRegisterEvent;

class FollowUserController extends CoreController
{
    use UploadImageTrait;
    use NotificationTrait;
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
     * Follow/Unfollow User
     * @Params $request
     */
    public function followUnfollowUser(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'follow_user_id' => 'required', 
                'follow_or_unfollow' => 'required', // 1 for follow 0 for unfollow
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $followingUserRoleId = User::select('role_id')->where('user_id', $request->follow_user_id)->first();
            if(!empty($followingUserRoleId))
            {
                if($request->follow_or_unfollow == 1)
                {
                    $rolePermission = $this->checkRolePermission($user->role_id, $followingUserRoleId->role_id);
                    if($rolePermission[1] > 0)
                    {
                        return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $rolePermission[0]]], $this->exceptionStatus);
                    }
                    else
                    {
                        $isFollowedUser = Follower::where('user_id', $user->user_id)->where('follow_user_id', $request->follow_user_id)->first();
                        if(empty($isFollowedUser))
                        {
                            $follower = new Follower;
                            $follower->user_id = $user->user_id;
                            $follower->follow_user_id = $request->follow_user_id;
                            $follower->save();

                            if($user->role_id == 7 || $user->role_id == 10)
                            {
                                $name = ucwords(strtolower($user->first_name)) . ' ' . ucwords(strtolower($user->last_name));
                            }
                            else
                            {
                                $name = $user->company_name;
                            }

                            $title = $name . " started following you";

                            $saveNotification = new Notification;
                            $saveNotification->from = $user->user_id;
                            $saveNotification->to = $request->follow_user_id;
                            $saveNotification->notification_type = 'follow';
                            $saveNotification->title = $this->translate('messages.'.$title,$title);
                            $saveNotification->redirect_to = 'user_screen';
                            $saveNotification->redirect_to_id = $user->user_id;
                            $saveNotification->save();

                            $tokens = DeviceToken::where('user_id', $request->follow_user_id)->get();
                            if(count($tokens) > 0)
                            {
                                $collectedTokenArray = $tokens->pluck('device_token');
                                $this->sendNotification($collectedTokenArray, $title, $saveNotification->redirect_to, $saveNotification->redirect_to_id);
                            }

                            $message = "You are now following this user";
                            return response()->json(['success' => $this->successStatus,
                                                 'message' => $this->translate('messages.'.$message,$message),
                                                ], $this->successStatus);
                            
                        }
                        else
                        {
                            $message = "You are already following this user";
                            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus); 
                        }
                    }
                }
                elseif($request->follow_or_unfollow == 0)
                {
                    $isExistFollowUser = Follower::where('user_id', $user->user_id)->where('follow_user_id', $request->follow_user_id)->first();

                    if(!empty($isExistFollowUser))
                    {
                        $isFollowUserDeleted = Follower::where('user_id', $user->user_id)->where('follow_user_id', $request->follow_user_id)->delete();
                        if($isFollowUserDeleted == 1)
                        {
                            $message = "You unfollowed this user";
                            return response()->json(['success' => $this->successStatus,
                                                 'message' => $this->translate('messages.'.$message,$message),
                                                ], $this->successStatus);
                        }
                        else
                        {
                            $message = "You have to first follow this user";
                            return response()->json(['success' => $this->exceptionStatus,
                                                 'message' => $this->translate('messages.'.$message,$message),
                                                ], $this->exceptionStatus);
                        }
                    }
                    else
                    {
                        $message = "You are not following this user";
                        return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                    }
                }
                else
                {
                    $message = "Invalid follow/unfollow type";
                    return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                }
            }
            else
            {
                $message = "Invalid following id";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Get Followers list
     *
     */
    public function getFollowersList()
    {
        try
        {
            $user = $this->user;
            $myFollowers = Follower::with('user:user_id,name,email')->with('follow_user:user_id,name,email')->where('follow_user_id', $user->user_id)->orderBy('id', 'DESC')->get();
            if(count($myFollowers) > 0)
            {
                return response()->json(['success' => $this->successStatus,
                                         'follower_count' => count($myFollowers),  
                                         'data' => $myFollowers
                                        ], $this->successStatus);
            }
            else
            {
                $message = "No followers found";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Get Following list
     *
     */
    public function getFollowingsList()
    {
        try
        {
            $user = $this->user;
            $followings = Follower::with('user:user_id,name,email')->with('follow_user:user_id,name,email')->where('user_id', $user->user_id)->orderBy('id', 'DESC')->get();
            if(count($followings) > 0)
            {
                return response()->json(['success' => $this->successStatus,
                                         'follower_count' => count($followings),  
                                         'data' => $followings
                                        ], $this->successStatus);
            }
            else
            {
                $message = "No followers found";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
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
