<?php

namespace Modules\Activity\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CoreController;
use Modules\User\Entities\User; 
use App\Http\Traits\UploadImageTrait;
use Modules\Activity\Entities\ActivityAction;
use Modules\Activity\Entities\Follower;
use Modules\Activity\Entities\Connection;
use Modules\Activity\Entities\ActivityActionType;
use Modules\Activity\Entities\ActivityAttachment;
use Modules\Activity\Entities\ActivityAttachmentLink;
use Modules\Activity\Entities\ConnectFollowPermission;
use Modules\Activity\Entities\MapPermissionRole;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth; 
use Validator;
//use App\Events\UserRegisterEvent;

class ConnectUserController extends CoreController
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
     * Send Connection Request
     * @Params $request
     */
    public function sendConnectionRequest(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'user_id' => 'required', 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $myRole = User::select('role_id')->where('user_id', $user->user_id)->first();
            $checkUser = User::where('user_id', $request->user_id)->first();
            if(!empty($checkUser))
            {
                $connectPermission = $this->checkConnectPermission($myRole->role_id, $checkUser->role_id);
                if($connectPermission[1] > 0)
                {
                    return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $connectPermission[0]]], $this->exceptionStatus);
                }
                else
                {
                    $isConnectedUser = Connection::where('user_id', $request->user_id)->where('resource_id', $user->user_id)->first();
                    if(empty($isConnectedUser))
                    {
                        $newConnection = new Connection;
                        $newConnection->user_id = $request->user_id;
                        $newConnection->resource_id = $user->user_id;
                        $newConnection->reason_to_connect = $request->reason_to_connect;
                        $newConnection->save();

                        $message = "Connection request has been sent!";
                        return response()->json(['success' => $this->successStatus,
                                             'message' => $this->translate('messages.'.$message,$message),
                                            ], $this->successStatus);
                        
                    }
                    else
                    {
                        $message = "You have already sent a connection request to this user";
                        return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus); 
                    }
                }
            }
            else
            {
                $message = "Invalid user id";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Get Pending Requests
     * 
     */
    public function getMyPendingRequest()
    {
        try
        {
            $user = $this->user;

            $requests = Connection::with('user')->where('user_id', $user->user_id)->get();
            if(count($requests) > 0)
            {
                return response()->json(['success' => $this->successStatus,
                                    'count' => count($requests),
                                    'data' => $requests,
                                    ], $this->successStatus);
            }
            else
            {
                $message = "No pending requests found";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Accept/Reject Connection Requests
     * @Params $request
     */
    public function acceptOrRejectConnection(Request $request)
    {
        try
        {
            $user = $this->user;

            $requests = Connection::with('user')->where('user_id', $user->user_id)->get();
            if(count($requests) > 0)
            {
                return response()->json(['success' => $this->successStatus,
                                    'count' => count($requests),
                                    'data' => $requests,
                                    ], $this->successStatus);
            }
            else
            {
                $message = "No pending requests found";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }


    /*
     * Check Connect Permission
     * @Params $myRole, $userRole
     */

    public function checkConnectPermission($myRole, $userRole)
    {
        $status = [];
        $permission = ConnectFollowPermission::where("role_id", $myRole)->where('permission_type', '1')->first();

        if(!empty($permission))
        {
            $permissionRole = MapPermissionRole::where("connect_follow_permission_id", $permission->connect_follow_permission_id)->get();
            $checkRoleWisePermission = $permissionRole->pluck('role_id')->toArray();

            if(in_array($userRole, $checkRoleWisePermission))
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
            $status = [$this->translate('messages.'."You are not authorized to connect wih this user","You are not authorized to connect wih this user"), 2];
        }
        
        return $status;
    }

   
}
