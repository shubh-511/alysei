<?php

namespace App\Http\Traits;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User; 
use Carbon\Carbon;
use App\Attachment;
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
        $date = date("Y/m/");
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


}
