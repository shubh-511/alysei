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

trait UploadImageTrait
{
    
    /***
    Upload Image
    ***/
    public function uploadImage($img)
    {
        $date = date("Y/m");
    	$target='uploads/'.$date."/";
        $baseUrl = 'https://' . env('AWS_BUCKET') . '.s3.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com/' . env('AWS_BUCKET') . '/';

        $basePath = 'https://' . env('AWS_BUCKET') . '.s3.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com/';

        if(env('FILESYSTEM') == 'storage_file')
        {
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
        }
        else
        {
            $status = [];
            
            
            $ext1 = $img->getClientOriginalExtension();
            $name = $img->getClientOriginalName();
            echo $headerTarget = $target.''. $name;exit;
            $url = Storage::disk('s3')->put($headerTarget, file_get_contents($img));

        }
        $attachment = new Attachment;
        $attachment->attachment_url = $headerTarget;
        $attachment->attachment_type = $ext1;
        $attachment->base_url = $basePath;
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
            unlink('/home/ibyteworkshop/alyseiapi_ibyteworkshop_com/'.$attachment->attachment_url);
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
        $baseUrl = 'https://' . env('AWS_BUCKET') . '.s3.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com/' . env('AWS_BUCKET') . '/';
        $basePath = 'https://' . env('AWS_BUCKET') . '.s3.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com/';

        if(env('FILESYSTEM') == 'storage_file')
        {
            if(!empty($img))
            {
                $headerImageName=$img->getClientOriginalName();
                $ext1=$img->getClientOriginalExtension();
                $temp1=explode(".",$headerImageName);
                $newHeaderLogo=rand()."".round(microtime(true)).".".end($temp1);
                $headerTarget='public/uploads/'.$date.'/'.$newHeaderLogo;
                $img->move($target,$newHeaderLogo);
                list($width, $height, $type, $attr) = getimagesize(env('APP_URL').''.$headerTarget);
                
            }
            else
            {
                $headerTarget = '';
            }
        }
        else
        {
            $status = [];
            
            
            $ext1 = $img->getClientOriginalExtension();
            $name = $img->getClientOriginalName();
            $headerTarget = $target.''. $name;
            $url = Storage::disk('s3')->put($headerTarget, file_get_contents($img));
            //list($width, $height, $type, $attr) = getimagesize(env('APP_URL').''.$headerTarget);
        }
        

        $activityAttachmentLink = new ActivityAttachmentLink;
        $activityAttachmentLink->attachment_url = $headerTarget;
        $activityAttachmentLink->attachment_type = $ext1;
        $activityAttachmentLink->height = $height;
        $activityAttachmentLink->width = $width;
        $activityAttachmentLink->base_url = $basePath;
        $activityAttachmentLink->save();
        
        return $activityAttachmentLink->activity_attachment_link_id;
    }

    /***
    Post Galleries
    ***/
    public function postGallery($img, $moduleId, $storeOrProduct)
    {
        $date = date("Y/m");
        $target='uploads/'.$date;

        if(env('FILESYSTEM') == 'storage_file')
        {
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
        }
        else
        {
            $status = [];
            $baseUrl = 'https://' . env('AWS_BUCKET') . '.s3.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com/' . env('AWS_BUCKET') . '/';
            
            $ext1 = $img->getClientOriginalExtension();
            $name = $img->getClientOriginalName();
            $headerTarget = $target.''. $name;
            $url = Storage::disk('s3')->put($headerTarget, file_get_contents($img));
        }
        

        if($storeOrProduct == 1)
        {
            $activityAttachmentLink = new MarketplaceStoreGallery;
            $activityAttachmentLink->marketplace_store_id = $moduleId;    
        }
        elseif($storeOrProduct == 2)
        {
            $activityAttachmentLink = new MarketplaceProductGallery;
            $activityAttachmentLink->marketplace_product_id = $moduleId;
        }
        
        $activityAttachmentLink->attachment_url = $headerTarget;
        $activityAttachmentLink->attachment_type = $ext1;
        $activityAttachmentLink->save();

        /*if($storeOrProduct == 1)
            return $activityAttachmentLink->marketplace_store_gallery_id;
        elseif($storeOrProduct == 2)
            return $activityAttachmentLink->marketplace_product_gallery_id;*/
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
            //unlink(env('APP_URL').''.$attachment->attachment_url);
            unlink('/home/ibyteworkshop/alyseiapi_ibyteworkshop_com/'.$attachment->attachment_url);
            ActivityAttachmentLink::where('activity_attachment_link_id',$attachmentId)->delete();
        }
        
    }

    /****
    Upload Media using S3
    ****/
    public function uploadMediaUsingS3($img)
    {
        $status = [];
        $baseUrl = 'https://' . env('AWS_BUCKET') . '.s3.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com/' . env('AWS_BUCKET') . '/';
        
        $date = date("Y/m");
        $target='uploads/'.$date;

        $folderPath = "uploads/".$date."/";
        
            $ext1 = $img->getClientOriginalExtension();
            $name = $img->getClientOriginalName();
            $filePath = $folderPath.''. $name;
            $url = Storage::disk('s3')->put($filePath, file_get_contents($img));

            $status = [$filePath, $ext1];
            return $status; 
        


        
        /*if(!empty($img))
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

        $status = [$headerTarget, $ext1];
        return $status; */

        
    }

}
