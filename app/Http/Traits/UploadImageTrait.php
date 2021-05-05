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
use Validator;
//use App\Events\UserRegisterEvent;

trait UploadImageTrait
{
    
    /***
    Upload Image
    ***/
    public function uploadImage($img)
    {
        $date = date("Y/m");
    	$target='uploads/'.$date;
    	if(!empty($img))
        {
            $headerImageName=$img->getClientOriginalName();
            $ext1=$img->getClientOriginalExtension();
            $temp1=explode(".",$headerImageName);
            $newHeaderLogo=rand()."".round(microtime(true)).".".end($temp1);
            $headerTarget='public/uploads/'.$date.'/'.$newHeaderLogo;
            $img->move($target,$newHeaderLogo);
        }
        else
        {
        	$headerTarget = '';
        }

        $attachment = new Attachment;
        $attachment->attachment_url = $headerTarget;
        $attachment->attachment_type = $ext1;
        $attachment->save();

        return $attachment->id;
    }

    public function getAttachment($attachmentId){
       return Attachment::where('id',$attachmentId)->first();
    }

    public function getCertificatesById($attachmentId){
       $certificate = Attachment::where('id',$attachmentId)->first();
       if(!empty($certificate))
       {
        return $certificate->attachment_url;
       }
       else
       {
        return "";
       }
    }

    public function deleteAttachment($attachmentId){
        $attachment = Attachment::where('id',$attachmentId)->first();
        
        if($attachment){
            unlink(env('BASE_URL').''.$attachment->attachment_url);
            Attachment::where('id',$attachmentId)->delete();
        }
        
    }

    /***
    Post Attchments
    ***/
    public function postAttchment($img)
    {
        $date = date("Y/m");
        $target='uploads/'.$date;
        if(!empty($img))
        {
            $headerImageName=$img->getClientOriginalName();
            $ext1=$img->getClientOriginalExtension();
            $temp1=explode(".",$headerImageName);
            $newHeaderLogo=rand()."".round(microtime(true)).".".end($temp1);
            $headerTarget='public/uploads/'.$date.'/'.$newHeaderLogo;
            $img->move($target,$newHeaderLogo);
        }
        else
        {
            $headerTarget = '';
        }

        $activityAttachmentLink = new ActivityAttachmentLink;
        $activityAttachmentLink->attachment_url = $headerTarget;
        $activityAttachmentLink->attachment_url = $ext1;
        $activityAttachmentLink->save();
        
        return $activityAttachmentLink->activity_attachment_link_id;
    }

    /** 
     * Create Post Image From Base64 string
     * 
     * Pamameters $img
     */ 
    public function createPostImage($img)
    {
        $date = date("Y/m");
        $year = date("Y");
        $month = date("m");

        $folderPath = "public/uploads/".$date."/";

        $image_parts = explode(";base64,", $img);
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_type = $image_type_aux[1];
        $image_base64 = base64_decode($image_parts[1]);
        $file = $folderPath . uniqid() . '. '.$image_type;

        if (!is_dir('public/uploads/' . $year)) {
          // dir doesn't exist, make it
          mkdir('public/uploads/' . $year);
        }
        if (!is_dir('public/uploads/' . $month)) {
          // dir doesn't exist, make it
          mkdir('public/uploads/' . $month);
        }

        file_put_contents($file, $image_base64);

        $activityAttachmentLink = new ActivityAttachmentLink;
        $activityAttachmentLink->attachment_url = $file;
        $activityAttachmentLink->attachment_url = $image_type;
        $activityAttachmentLink->save();
        
        return $activityAttachmentLink->activity_attachment_link_id;

    }

    /***
    Delete Post Attchments
    ***/
    public function deletePostAttachment($attachmentId){
        $attachment = ActivityAttachmentLink::where('activity_attachment_link_id',$attachmentId)->first();
        
        if($attachment){
            unlink(env('BASE_URL').''.$attachment->attachment_url);
            ActivityAttachmentLink::where('activity_attachment_link_id',$attachmentId)->delete();
        }
        
    }

}
