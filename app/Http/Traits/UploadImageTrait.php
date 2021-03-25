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
