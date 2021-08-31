<?php

namespace Modules\Activity\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CoreController;
use Modules\User\Entities\User; 
use Modules\User\Entities\DeviceToken; 
use App\Http\Traits\NotificationTrait;
use Modules\Activity\Entities\ActivityAction;
use Modules\Activity\Entities\Follower;
use Modules\User\Entities\Certificate;
use Modules\Activity\Entities\Connection;
use Modules\Activity\Entities\ActivityActionType;
use Modules\Activity\Entities\ActivityAttachment;
use Modules\Activity\Entities\ActivityAttachmentLink;
use Modules\Activity\Entities\ConnectFollowPermission;
use Modules\Activity\Entities\MapPermissionRole;
use Carbon\Carbon;
use App\Notification;
use DB;
use Illuminate\Support\Facades\Auth; 
use Validator;
//use App\Events\UserRegisterEvent;

class ConnectUserController extends CoreController
{
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
            elseif($request->tab == 4)
            {
                if($user->role_id == 10)
                {
                    return $this->getFollowingsList($user);
                }
                else
                {
                    return $this->getFollowersList($user);
                }
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
     * Get Followers list
     *
     */
    public function getFollowersList($user)
    {
        try
        {
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
    public function getFollowingsList($user)
    {
        try
        {
            $followings = Follower::with('user:user_id,name,email,company_name,first_name,last_name,')->with('follow_user:user_id,name,email,company_name,first_name,last_name,')->where('user_id', $user->user_id)->orderBy('id', 'DESC')->get();
            if(count($followings) > 0)
            {
                return response()->json(['success' => $this->successStatus,
                                         'follower_count' => count($followings),  
                                         'data' => $followings
                                        ], $this->successStatus);
            }
            else
            {
                $message = "You are not following anyone";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * View Connection Request
     *
     */
    public function viewConnectionRequestOfProducer(Request $request)
    {
        try
        {
            $user = $this->user;

            $validator = Validator::make($request->all(), [ 
                'connection_id' => 'required'
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $isConnectedUser = Connection::where('connection_id', $request->connection_id)->where('user_id', $user->user_id)->first();
            if(!empty($isConnectedUser))
            {
                $user = User::where('user_id', $isConnectedUser->resource_id)->first();
                $fieldOptions = DB::table('user_field_options')->where('parent','=',0)->where('head','=',0)->get();

                $fieldOptions = $fieldOptions->pluck('user_field_option_id');

                $userFieldValues = DB::table('user_field_values')->where('user_id', $isConnectedUser->resource_id)->where('user_field_id', 2)->whereIn('value', $fieldOptions)->get();

                $userFieldValues = $userFieldValues->pluck('value');

                $fieldOptions = DB::table('user_field_options')->whereIn('user_field_option_id',$userFieldValues)->get();



                foreach($fieldOptions as $key => $fieldOption)
                {
                    $userCertificates = Certificate::with('photo_of_label','fce_sid_certification','phytosanitary_certificate','packaging_for_usa','food_safety_plan','animal_helath_asl_certificate')->where('user_id', $isConnectedUser->resource_id)->where('user_field_option_id', $fieldOption->user_field_option_id)->first();
                    
                    $fieldOptions[$key]->photo_of_label = (!empty($userCertificates->photo_of_label))?($this->getCertificatesById($userCertificates->photo_of_label)):"";

                    $fieldOptions[$key]->fce_sid_certification = (!empty($userCertificates->fce_sid_certification))?($this->getCertificatesById($userCertificates->fce_sid_certification)):"";

                    $fieldOptions[$key]->phytosanitary_certificate = (!empty($userCertificates->phytosanitary_certificate))?($this->getCertificatesById($userCertificates->phytosanitary_certificate)):"";

                    $fieldOptions[$key]->packaging_for_usa = (!empty($userCertificates->packaging_for_usa))?($this->getCertificatesById($userCertificates->packaging_for_usa)):"";

                    $fieldOptions[$key]->food_safety_plan = (!empty($userCertificates->food_safety_plan))?($this->getCertificatesById($userCertificates->food_safety_plan)):"";

                    $fieldOptions[$key]->animal_helath_asl_certificate = (!empty($userCertificates->animal_helath_asl_certificate))?($this->getCertificatesById($userCertificates->animal_helath_asl_certificate)):"";
                    
                }
                return response()->json(['success' => $this->successStatus, 
                                         'certificates' => $fieldOptions
                                        ], $this->successStatus);
            }
            else
            {
                $message = "Invalid connection id";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
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

            $myRole = User::where('user_id', $user->user_id)->first();
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

                        if($myRole->role_id == 7 || $myRole->role_id == 10)
                        {
                            $name = ucwords(strtolower($myRole->first_name)) . ' ' . ucwords(strtolower($myRole->last_name));
                        }
                        else
                        {
                            $name = $myRole->company_name;
                        }

                        $title = $name . " sent you a connection request";

                        $saveNotification = new Notification;
                        $saveNotification->from = $myRole->user_id;
                        $saveNotification->to = $request->user_id;
                        $saveNotification->notification_type = 'connections';
                        $saveNotification->title = $this->translate('messages.'.$title,$title);
                        $saveNotification->redirect_to = 'user_screen';
                        $saveNotification->redirect_to_id = $myRole->user_id;
                        $saveNotification->save();

                        $tokens = DeviceToken::where('user_id', $request->user_id)->get();
                        if(count($tokens) > 0)
                        {
                            $collectedTokenArray = $tokens->pluck('device_token');
                            $this->sendNotification($collectedTokenArray, $title, $saveNotification->redirect_to, $saveNotification->redirect_to_id);
                        }
                        

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
            $requests = Connection::
            //with('user:user_id,name,email,company_name,first_name,last_name,restaurant_name,role_id,avatar_id','user.avatar_id')
            where('is_approved', '1')
            ->where('resource_id', $user->user_id)
            ->orWhere('user_id', $user->user_id)
            ->orderBy('connection_id', 'DESC')
            ->get();
            foreach($requests as $key => $request)
            {
                if($request->user_id == $user->user_id)
                {
                    $user = User::select('user_id','email','company_name','first_name','last_name','restaurant_name','role_id','avatar_id')->with('avatar_id')->where('user_id', $request->resource_id)->first();
                    $requests[$key]->user = $user;
                }
                else
                {
                    $user = User::select('user_id','email','company_name','first_name','last_name','restaurant_name','role_id','avatar_id')->with('avatar_id')->where('user_id', $request->user_id)->first();
                    $requests[$key]->user = $user;
                }

            }

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
            $requests = Connection::with('user:user_id,name,email,company_name,first_name,last_name,restaurant_name,role_id,avatar_id','user.avatar_id')->where('user_id', $user->user_id)->where('is_approved', '0')->orderBy('connection_id', 'DESC')->get();
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
            $requests = Connection::with('user:user_id,name,email,first_name,last_name,company_name,restaurant_name,role_id,avatar_id','user.avatar_id')->where('resource_id', $user->user_id)->where('is_approved', '0')->orderBy('connection_id', 'DESC')->get();
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

                    if($user->role_id == 7 || $user->role_id == 10)
                    {
                        $name = ucwords(strtolower($user->first_name)) . ' ' . ucwords(strtolower($user->last_name));
                    }
                    else
                    {
                        $name = $user->company_name;
                    }

                    $title = $name . " accepted your connection request";

                    $saveNotification = new Notification;
                    $saveNotification->from = $user->user_id;
                    $saveNotification->to = $checkConnectionId->resource_id;
                    $saveNotification->notification_type = 'connections';
                    $saveNotification->title = $this->translate('messages.'.$title,$title);
                    $saveNotification->redirect_to = 'user_screen';
                    $saveNotification->redirect_to_id = $user->user_id;
                    $saveNotification->save();

                    $tokens = DeviceToken::where('user_id', $checkConnectionId->resource_id)->get();
                    if(count($tokens) > 0)
                    {
                        $collectedTokenArray = $tokens->pluck('device_token');
                        $this->sendNotification($collectedTokenArray, $title, $saveNotification->redirect_to, $saveNotification->redirect_to_id);
                    }

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
