<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CoreController;
use Modules\User\Entities\User; 
use Modules\User\Entities\UserSelectedHub; 
use App\Http\Traits\NotificationTrait;
use Modules\User\Entities\State;
use Modules\User\Entities\DeviceToken; 
use Modules\User\Entities\UserField;
use Modules\User\Entities\UserFieldValue;
use App\Http\Traits\UploadImageTrait;
use Modules\Activity\Entities\UserPrivacy;
use Modules\Activity\Entities\ConnectFollowPermission;
use Modules\Activity\Entities\MapPermissionRole;
use Modules\User\Entities\Role;
use App\Notification;
use App\Attachment;
use DB;
use Illuminate\Support\Facades\Auth; 
use Validator;

class NotificationController extends CoreController
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
     * Upload media for chats using S3
     *
     */
    public function getAllNotifications()
    {
        try
        {
            $user = $this->user;

            $notificationData = Notification::with('user:user_id,name,email,company_name,role_id,avatar_id')->where('to', $user->user_id)->orderBy('notification_id', 'DESC')->paginate(10);
            if(count($notificationData) > 0)
            {
                foreach($notificationData as $key => $notification)
                {
                    $attachment = Attachment::where('id', $notification->user->avatar_id)->first();
                    $notificationData[$key]->user->avatar_image = (!empty($attachment->attachment_url) ? $attachment->attachment_url : null);
                }
                return response()->json(['success' => $this->successStatus,
                                'data' => $notificationData,
                                ], $this->successStatus);
            }        
            else
            {
                $message = "No notifications found";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus); 
            }
            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Upload media for chats using S3
     *
     */
    public function sendNewMessageNotification(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [ 
                'from_id' => 'required', 
                'to_id' =>  'required'
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }
            $fromUser = User::where('user_id', $request->from_id)->first();
            $toUser = User::where('user_id', $request->to_id)->first();
            if(!empty($fromUser) && !empty($toUser))
            {
                if($fromUser->role_id == 7 || $fromUser->role_id == 10)
                {
                    $name = ucwords(strtolower($fromUser->first_name)) . ' ' . ucwords(strtolower($fromUser->last_name));
                }
                elseif($fromUser->role_id == 9)
                {
                    $name = $fromUser->restaurant_name;
                }
                else
                {
                    $name = $fromUser->company_name;
                }

                $title = $name . " sent you a new message";

                $saveNotification = new Notification;
                $saveNotification->from = $fromUser->user_id;
                $saveNotification->to = $request->to_id;
                $saveNotification->notification_type = 'messaging';
                $saveNotification->title = $this->translate('messages.'.$title,$title);
                $saveNotification->redirect_to = 'message_screen';
                $saveNotification->redirect_to_id = $fromUser->user_id;
                $saveNotification->save();

                $tokens = DeviceToken::where('user_id', $request->to_id)->get();
                if(count($tokens) > 0)
                {
                    $collectedTokenArray = $tokens->pluck('device_token');
                    $this->sendNotification($collectedTokenArray, $title, $saveNotification->redirect_to, $saveNotification->redirect_to_id);
                }
            }            
            else
            {
                $message = "something went wrong";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus); 
            }
            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }



}