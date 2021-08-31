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
                    $notificationData[$key]->user->avatar_image = $attachment->attachment_url;
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



}