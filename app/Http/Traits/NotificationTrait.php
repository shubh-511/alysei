<?php

namespace App\Http\Traits;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User; 
use Carbon\Carbon;
use App\Attachment;
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
    public function sendNotification($img)
    {
        $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
        $jsonArray['result']=$booking_details;
        $jsonArray['result']->title=$title;
        
        $tok=array();
        $notify=array();
        if(!empty($tokens))
        {
            if(count($tokens)>0)
            {
                foreach($tokens as $token)
                {
                    array_push($tok,$token->device_token);
                }
            }
        }
        $fcmNotification = [
        	'to'        => $token, //single token
            //'registration_ids' => $tok, //multple token array
            'data' => $jsonArray
        ];
        $headers =[
            'Authorization: key=AAAAKqjUV_0:APA91bHT0Ktkrx8ojjKPVuDtf3kdaKeiRZ7LwWo1X3wfJJOEDJ2lIGWiH-qGh3qs6uupkgOmKcrCiELPWFVywqIYJZzcNlZUGg-6SGsKF3quE31_mTzOfngwKQVcpYLDFlOc3-e7oTy_',//.$this->fcm_key_business_android,//AAAAu0eqOAg:APA91bGCcy1meCwMFJce7lxyiKg7UVckeacdWB7RAQWAHTpK3YW0OEECGIZlUXAzriK-Mld_aP7AfoCbxmx1N3CUZ8L0YgCmihffVAvtpfYQ4XXzKkklLhUEA6TV3Fnuu7GRKauvgHDd',
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