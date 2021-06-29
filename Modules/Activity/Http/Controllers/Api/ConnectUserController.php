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
use DB;
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
     * Get Permission For Sending Requests
     * 
     */
    public function getPermissions()
    {
        try
        {
            $user = $this->user;

            $permissions = ConnectFollowPermission::select('connect_follow_permission_id','role_id','permission_type')->where('role_id', $user->role_id)->get();
            if(count($permissions) > 0)
            {
                foreach($permissions as $key => $permission)
                {
                    $mapPermission = MapPermissionRole::select('map_permission_role_id','connect_follow_permission_id','role_id')->where('connect_follow_permission_id', $permission->connect_follow_permission_id)->get();
                    $permissions[$key]->map_permissions = $mapPermission;
                }

                return response()->json(['success' => $this->successStatus,
                                        'data' => $permissions,
                                    ], $this->successStatus);
            }
            else
            {
                $message = "No privillege granted for sending request or following someone";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }


    /*
     * Get COnnections tab (pending/recieved/my connection)
     * @Params $request
     */
    public function getConnectionTabs(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'tab' => 'required'
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            if($request->tab == 1)
            {
                return $this->getMyPendingRecievedRequest($user);
            }
            elseif($request->tab == 2)
            {
                return $this->getMyConnections($user);
            }
            elseif($request->tab == 3)
            {
                return $this->getMyPendingSentRequest($user);
            }
            else
            {
                $message = "Invalid tab!";
                return response()->json(['success' => $this->successStatus,
                                        'message' => $this->translate('messages.'.$message,$message),
                                        ], $this->successStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
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
                'reason_to_connect' =>  'required'
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

                        /*if(!empty($request->user_field_option_id))
                        {
                            
                        }*/

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
    * Get my connections list
    */
    public function getMyConnections($user)
    {
        try
        {
            $requests = Connection::with('user:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','user.avatar_id')
            ->where('is_approved', '1')
            //->where('user_id', $user->user_id)
            ->orWhere(function ($query) use ($user) {
                $query->where('resource_id', $user->user_id)
                ->where('user_id', $user->user_id);
            })
            ->orderBy('connection_id', 'DESC')
            ->get();

            if(count($requests) > 0)
            {
                return response()->json(['success' => $this->successStatus,
                                    'count' => count($requests),
                                    'data' => $requests,
                                    ], $this->successStatus);
            }
            else
            {
                $message = "No connections found";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Get Pending Recieved Requests
     * 
     */
    public function getMyPendingRecievedRequest($user)
    {
        try
        {
            $requests = Connection::with('user:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','user.avatar_id')->where('user_id', $user->user_id)->where('is_approved', '0')->orderBy('connection_id', 'DESC')->get();
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
     * Get Pending Recieved Requests
     * 
     */
    public function getProductListToConnect()
    {
        try
        {
            $user = $this->user;
            
            $arrayValues = array();
            $fieldValues = DB::table('user_field_values')
                        ->where('user_id', $user->user_id)
                        ->where('user_field_id', 2)
                        ->get();
            if(count($fieldValues) > 0)
            {
                foreach($fieldValues as $fieldValue)
                {
                    $options = DB::table('user_field_options')
                            ->where('head', 0)->where('parent', 0)
                            ->where('user_field_option_id', $fieldValue->value)
                            ->first();
                    
                    //$arrayValues[] = $options->option;
                    if(!empty($options->option))
                    $arrayValues[] = ['user_field_option_id'=>$options->user_field_option_id, 'option' => $options->option];
                }
                return response()->json(['success' => $this->successStatus,
                                'count' => count($arrayValues),
                                'data' => $arrayValues,
                                ], $this->successStatus);
            }
            else
            {
                $message = "No products found";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }      
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Get Pending Sent Requests
     * 
     */
    public function getMyPendingSentRequest($user)
    {
        try
        {
            $requests = Connection::with('user:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','user.avatar_id')->where('resource_id', $user->user_id)->where('is_approved', '0')->orderBy('connection_id', 'DESC')->get();
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
            $validator = Validator::make($request->all(), [ 
                'connection_id' => 'required', 
                'accept_or_reject' => 'required', // 1=accept, 2=reject
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $checkConnectionId = Connection::with('user')->where('connection_id', $request->connection_id)->where('is_approved', '0')->where('user_id', $user->user_id)->first();
            if(!empty($checkConnectionId))
            {
                if($request->accept_or_reject == 1)
                {
                    $checkConnectionId->is_approved = '1';
                    $checkConnectionId->save();

                    $message = "Connection request accepted!";
                }
                elseif($request->accept_or_reject == 2)
                {
                    $rejectRequest = Connection::where('connection_id', $request->connection_id)->delete();

                    $message = "Connection request rejected";
                }
                else
                {
                    $message = "accept/reject type is not valid";
                    return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                }

                return response()->json(['success' => $this->successStatus,
                                    'message' => $this->translate('messages.'.$message,$message),
                                    ], $this->successStatus);
            }
            else
            {
                $message = "Connection id is not valid";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }


    /*
     * Cancel/Remove connection
     * @Params $request
     */
    public function cancelConnectionRequest(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'visitor_profile_id' => 'required'
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $checkConnection = Connection::with('user')->where('user_id', $request->visitor_profile_id)->where('resource_id', $user->user_id)->first();
            if(!empty($checkConnection))
            {
                $checkConnection->delete();

                $message = "The request has been cancelled";
                    
                return response()->json(['success' => $this->successStatus,
                                    'message' => $this->translate('messages.'.$message,$message),
                                    ], $this->successStatus);
            }
            else
            {
                $message = "You have not send a connection request to this user";
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
