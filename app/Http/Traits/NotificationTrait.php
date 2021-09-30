<?php

namespace App\Http\Traits;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User; 
use Carbon\Carbon;
use App\Attachment;
use App\Notification;
use Modules\Activity\Entities\ActivityAttachment;
use Modules\Activity\Entities\ActivityAttachmentLink;
use Illuminate\Support\Facades\Auth; 
use Modules\Marketplace\Entities\MarketplaceStoreGallery;
use Modules\Marketplace\Entities\MarketplaceProductGallery;
use Validator;
use Storage;
use League\Flysystem\Filesystem;
use Aws\S3\S3Client;
use League\Flysystem\Filesystem\AwsS3v3\AwsS3Adapter;
//use App\Events\UserRegisterEvent;

trait NotificationTrait
{
    
    /***
    Send Notification
    ***/
    public function sendNotification($token, $title, $redirectTo, $redirectToId, $notificationType, $senderId, $senderName, $senderImg, $postId='', $connectionId='', $senderRoleId='', $commentId='', $reply='', $likeUnlike)
    {
        $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
        $jsonArray['title'] = $title;
        $jsonArray['notification_type'] = $notificationType;
        $jsonArray['redirect_to'] = $redirectTo;
        $jsonArray['redirect_to_id'] = $redirectToId;
        
        $jsonArray['sender_id'] = $senderId;
        $jsonArray['sender_name'] = $senderName;
        $jsonArray['sender_image'] = $senderImg;
        $jsonArray['post_id'] = $postId;
        $jsonArray['connection_id'] = $connectionId;
        $jsonArray['sender_role'] = $senderRoleId;
        $jsonArray['comment_id'] = $commentId;
        $jsonArray['reply'] = $reply;
        $jsonArray['likeUnlike'] = $likeUnlike;
        
        $fcmNotification = [
        	//'to'        => $token, //single token
            'registration_ids' => $token, //multple token array
            'data' => $jsonArray
        ];

        $headers =[
            'Authorization: key=AAAAxEbwo_A:APA91bFW4-x78zNh5J8UDFnjYP1t0qCgUWcTLwLN2gZsKHBzy3lDwqih8l6SIcka92-WTEX1M36sQc63i_G8AzUpc_pZda5KxkEa9mNQKyJVCMrXCGYdep9kfM_nFe_iUKyumczMu5Tk',
            'Content-Type: application/json'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$fcmUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));
        $result = curl_exec($ch);
        curl_close($ch);
        return true;
    }

    /***
    Send Notification to ios
    ***/
    public function sendNotificationToIOS($token, $title, $redirectTo, $redirectToId, $notificationType, $senderId, $senderName, $senderImg, $postId='', $connectionId='', $senderRoleId='', $commentId='', $reply='', $likeUnlike)
    {
        $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
        $jsonArray['title'] = $title;
        $jsonArray['notification_type'] = $notificationType;
        $jsonArray['redirect_to'] = $redirectTo;
        $jsonArray['redirect_to_id'] = $redirectToId;
        
        $jsonArray['sender_id'] = $senderId;
        $jsonArray['sender_name'] = $senderName;
        $jsonArray['sender_image'] = $senderImg;
        $jsonArray['post_id'] = $postId;
        $jsonArray['connection_id'] = $connectionId;
        $jsonArray['sender_role'] = $senderRoleId;
        $jsonArray['comment_id'] = $commentId;
        $jsonArray['reply'] = $reply;
        $jsonArray['likeUnlike'] = $likeUnlike;
        
       
        $serverKey = 'AAAAxEbwo_A:APA91bFW4-x78zNh5J8UDFnjYP1t0qCgUWcTLwLN2gZsKHBzy3lDwqih8l6SIcka92-WTEX1M36sQc63i_G8AzUpc_pZda5KxkEa9mNQKyJVCMrXCGYdep9kfM_nFe_iUKyumczMu5Tk';
        
        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: key='. $serverKey;


        $notification = array('title' =>$title , 'data' => $jsonArray, 'sound' => 'default', 'badge' =>'1');
        $arrayToSend = array('registration_ids' => $token, 'notification'=>$notification,'priority'=>'high');
        $fcmNotification = [
                'registration_ids' => $token, //multple token array
                //'to'        => $token, //single token
                'data' => $jsonArray,
            ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$fcmUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($arrayToSend));
        $result = curl_exec($ch);
        curl_close($ch);
        return true;
    }

}