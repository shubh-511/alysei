<?php

namespace App\Http\Traits;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User; 
use Carbon\Carbon;
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
    	$target='public/images/listing';
    	if(!empty($img))
        {
            $headerImageName=$img->getClientOriginalName();
            $ext1=$img->getClientOriginalExtension();
            $temp1=explode(".",$headerImageName);
            $newHeaderLogo=rand()."".round(microtime(true)).".".end($temp1);
            $headerTarget='public/images/listing/'.$newHeaderLogo;
            $img->move($target,$newHeaderLogo);
        }
        else
        {
        	$headerTarget = '';
        }
        return $headerTarget;
    }


}
