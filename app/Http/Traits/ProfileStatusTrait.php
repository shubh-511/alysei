<?php

namespace App\Http\Traits;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Modules\User\Entities\User;
use Modules\User\Entities\FeaturedListing;
use Modules\User\Entities\UserSelectedHub;
use Modules\User\Entities\UserTempHub;
use App\Attachment;
use Illuminate\Support\Facades\Auth; 
use Validator;
use DB;
//use App\Events\UserRegisterEvent;

trait ProfileStatusTrait
{
    
    /***
    Get Profile Status
    ***/
    public function profileStatus($userId)
    {
        $profilePercentage = '';

        $user = User::where('user_id', $userId)->first();  // 10%
        $FeaturedListing = FeaturedListing::where('user_id', $userId)->get();  // 25%
        $userSelectedHub = UserSelectedHub::where('user_id', $userId)->get();  // 25%
        $userTempHub = UserTempHub::where('user_id', $userId)->get();
        if($user->role_id == 3 || $user->role_id == 4 || $user->role_id == 5 || $user->role_id == 6)
        {
            $fieldValue = DB::table('user_field_values')
                        ->where('user_id', $userId)
                        ->where('user_field_id', 35)
                        ->first();    
        }
        elseif($user->role_id == 8)
        {
            $fieldValue = DB::table('user_field_values')
                        ->where('user_id', $userId)
                        ->where('user_field_id', 38)
                        ->first();
        }
        else
        {
            $fieldValue = DB::table('user_field_values')
                        ->where('user_id', $userId)
                        ->where('user_field_id', 37)
                        ->first();
        }
        

        if($user->role_id == 10)
        {
            if(empty($user->cover_id) && empty($user->avatar_id) && empty($user->about) && empty($user->phone))
            {
                $profilePercentage = "20";
            }
            elseif(!empty($user->cover_id) && empty($user->avatar_id) && empty($user->about) && empty($user->phone))
            {
                $profilePercentage = "40";
            }
            elseif(empty($user->cover_id) && !empty($user->avatar_id) && empty($user->about) && empty($user->phone))
            {
                $profilePercentage = "40";
            }
            elseif(empty($user->cover_id) && empty($user->avatar_id) && !empty($user->about) && empty($user->phone))
            {
                $profilePercentage = "40";
            }
            elseif(empty($user->cover_id) && empty($user->avatar_id) && empty($user->about) && !empty($user->phone))
            {
                $profilePercentage = "40";
            }


            elseif(!empty($user->cover_id) && !empty($user->avatar_id) && empty($user->about) && empty($user->phone))
            {
                $profilePercentage = "60";
            }
            elseif(empty($user->cover_id) && empty($user->avatar_id) && !empty($user->about) && !empty($user->phone))
            {
                $profilePercentage = "60";
            }
            elseif(!empty($user->cover_id) && empty($user->avatar_id) && !empty($user->about) && empty($user->phone))
            {
                $profilePercentage = "60";
            }
            elseif(empty($user->cover_id) && !empty($user->avatar_id) && empty($user->about) && !empty($user->phone))
            {
                $profilePercentage = "60";
            }
            elseif(!empty($user->cover_id) && empty($user->avatar_id) && empty($user->about) && !empty($user->phone))
            {
                $profilePercentage = "60";
            }


            elseif(!empty($user->cover_id) && !empty($user->avatar_id) && !empty($user->about) && empty($user->phone))
            {
                $profilePercentage = "80";
            }
            elseif(empty($user->cover_id) && !empty($user->avatar_id) && !empty($user->about) && !empty($user->phone))
            {
                $profilePercentage = "80";
            }
            elseif(!empty($user->cover_id) && empty($user->avatar_id) && !empty($user->about) && !empty($user->phone))
            {
                $profilePercentage = "80";
            }
            elseif(!empty($user->cover_id) && !empty($user->avatar_id) && empty($user->about) && !empty($user->phone))
            {
                $profilePercentage = "80";
            }
            else
            {
                $profilePercentage = "100";   
            }
        }
        elseif($user->role_id == 7)
        {            
            if(empty($user->cover_id) && empty($user->avatar_id) && empty($user->about) && empty($user->phone) && (count($userSelectedHub) == 0 || count($userTempHub) == 0))
            {
                $profilePercentage = "20";
            }
            elseif(!empty($user->cover_id) && empty($user->avatar_id) && empty($user->about) && empty($user->phone) && (count($userSelectedHub) == 0 || count($userTempHub) == 0))
            {
                $profilePercentage = "30";
            }
            elseif(empty($user->cover_id) && !empty($user->avatar_id) && empty($user->about) && empty($user->phone) && (count($userSelectedHub) == 0 || count($userTempHub) == 0))
            {
                $profilePercentage = "30";
            }
            elseif(empty($user->cover_id) && empty($user->avatar_id) && !empty($user->about) && empty($user->phone) && (count($userSelectedHub) == 0 || count($userTempHub) == 0))
            {
                $profilePercentage = "40";
            }
            elseif(empty($user->cover_id) && empty($user->avatar_id) && empty($user->about) && !empty($user->phone) && (count($userSelectedHub) == 0 || count($userTempHub) == 0))
            {
                $profilePercentage = "40";
            }
            elseif(empty($user->cover_id) && empty($user->avatar_id) && empty($user->about) && empty($user->phone) && (count($userSelectedHub) > 0 || count($userTempHub) > 0))
            {
                $profilePercentage = "40";
            }


            elseif(!empty($user->cover_id) && !empty($user->avatar_id) && empty($user->about) && empty($user->phone) && (count($userSelectedHub) == 0 || count($userTempHub) == 0))
            {
                $profilePercentage = "40";
            }
            elseif(empty($user->cover_id) && empty($user->avatar_id) && !empty($user->about) && !empty($user->phone) && (count($userSelectedHub) == 0 || count($userTempHub) == 0))
            {
                $profilePercentage = "60";
            }
            elseif(!empty($user->cover_id) && empty($user->avatar_id) && empty($user->about) && empty($user->phone) && (count($userSelectedHub) > 0 || count($userTempHub) > 0))
            {
                $profilePercentage = "50";
            }
            elseif(!empty($user->cover_id) && empty($user->avatar_id) && !empty($user->about) && empty($user->phone) && (count($userSelectedHub) == 0 || count($userTempHub) == 0))
            {
                $profilePercentage = "50";
            }
            elseif(!empty($user->cover_id) && empty($user->avatar_id) && empty($user->about) && !empty($user->phone) && (count($userSelectedHub) == 0 || count($userTempHub) == 0))
            {
                $profilePercentage = "50";
            }
            elseif(empty($user->cover_id) && !empty($user->avatar_id) && !empty($user->about) && empty($user->phone) && (count($userSelectedHub) == 0 || count($userTempHub) == 0))
            {
                $profilePercentage = "50";
            }
            elseif(empty($user->cover_id) && !empty($user->avatar_id) && empty($user->about) && !empty($user->phone) && (count($userSelectedHub) == 0 || count($userTempHub) == 0))
            {
                $profilePercentage = "50";
            }
            elseif(empty($user->cover_id) && !empty($user->avatar_id) && empty($user->about) && empty($user->phone) && (count($userSelectedHub) > 0 || count($userTempHub) > 0))
            {
                $profilePercentage = "50";
            }
            elseif(empty($user->cover_id) && empty($user->avatar_id) && !empty($user->about) && empty($user->phone) && (count($userSelectedHub) > 0 || count($userTempHub) > 0))
            {
                $profilePercentage = "60";
            }
            elseif(empty($user->cover_id) && empty($user->avatar_id) && empty($user->about) && !empty($user->phone) && (count($userSelectedHub) > 0 || count($userTempHub) > 0))
            {
                $profilePercentage = "60";
            }



            elseif(!empty($user->cover_id) && !empty($user->avatar_id) && !empty($user->about) && empty($user->phone) && (count($userSelectedHub) == 0 || count($userTempHub) == 0))
            {
                $profilePercentage = "60";
            }
            elseif(empty($user->cover_id) && !empty($user->avatar_id) && !empty($user->about) && !empty($user->phone) && (count($userSelectedHub) == 0 || count($userTempHub) == 0))
            {
                $profilePercentage = "70";
            }
            elseif(empty($user->cover_id) && empty($user->avatar_id) && !empty($user->about) && !empty($user->phone) && (count($userSelectedHub) > 0 || count($userTempHub) > 0))
            {
                $profilePercentage = "80";
            }
            elseif(!empty($user->cover_id) && empty($user->avatar_id) && !empty($user->about) && empty($user->phone) && (count($userSelectedHub) > 0 || count($userTempHub) > 0))
            {
                $profilePercentage = "50";
            }
            elseif(!empty($user->cover_id) && !empty($user->avatar_id) && empty($user->about) && !empty($user->phone) && (count($userSelectedHub) == 0 || count($userTempHub) == 0))
            {
                $profilePercentage = "60";
            }
            elseif(!empty($user->cover_id) && !empty($user->avatar_id) && empty($user->about) && empty($user->phone) && (count($userSelectedHub) > 0 || count($userTempHub) > 0))
            {
                $profilePercentage = "60";
            }
            elseif(!empty($user->cover_id) && !empty($user->avatar_id) && empty($user->about) && !empty($user->phone) && (count($userSelectedHub) == 0 || count($userTempHub) == 0))
            {
                $profilePercentage = "60";
            }
            elseif(empty($user->cover_id) && !empty($user->avatar_id) && !empty($user->about) && empty($user->phone) && (count($userSelectedHub) > 0 || count($userTempHub) > 0))
            {
                $profilePercentage = "70";
            }
            elseif(!empty($user->cover_id) && empty($user->avatar_id) && !empty($user->about) && !empty($user->phone) && (count($userSelectedHub) == 0 || count($userTempHub) == 0))
            {
                $profilePercentage = "70";
            }


            /*elseif(!empty($user->cover_id) && !empty($user->avatar_id) && !empty($user->about) && !empty($user->phone) && (count($userSelectedHub) == 0 || count($userTempHub) == 0))
            {
                $profilePercentage = "80";
            }*/
            elseif(empty($user->cover_id) && !empty($user->avatar_id) && !empty($user->about) && !empty($user->phone) && (count($userSelectedHub) > 0 || count($userTempHub) > 0))
            {
                $profilePercentage = "90";
            }
            elseif(!empty($user->cover_id) && empty($user->avatar_id) && !empty($user->about) && !empty($user->phone) && (count($userSelectedHub) > 0 || count($userTempHub) > 0))
            {
                $profilePercentage = "90";
            }
            elseif(!empty($user->cover_id) && !empty($user->avatar_id) && empty($user->about) && !empty($user->phone) && (count($userSelectedHub) > 0 || count($userTempHub) > 0))
            {
                $profilePercentage = "80";
            }
            elseif(!empty($user->cover_id) && !empty($user->avatar_id) && !empty($user->about) && empty($user->phone) && (count($userSelectedHub) > 0 || count($userTempHub) > 0))
            {
                $profilePercentage = "80";
            }
            elseif(!empty($user->cover_id) && !empty($user->avatar_id) && !empty($user->about) && !empty($user->phone) && (count($userSelectedHub) == 0))
            {
                //dd($userSelectedHub);
                $profilePercentage = "80";
            }
            else
            {
                $profilePercentage = "100";
            }
        }
        else
        {
            if(empty($user->cover_id) && empty($user->avatar_id) && empty($user->about) && empty($user->phone) && count($FeaturedListing) == 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0) && empty($fieldValue->value))
            {
                $profilePercentage = "10";
            }
            elseif(!empty($user->cover_id) && empty($user->avatar_id) && empty($user->phone) && empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0) && empty($fieldValue->value))
            {
                $profilePercentage = "20";
            }
            elseif(empty($user->cover_id) && !empty($user->avatar_id) && empty($user->phone) && empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0) && empty($fieldValue->value))
            {
                $profilePercentage = "20";
            }
            elseif(empty($user->cover_id) && empty($user->avatar_id) && !empty($user->phone) && empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0) && empty($fieldValue->value))
            {
                $profilePercentage = "15";
            }
            elseif(empty($user->cover_id) && empty($user->avatar_id) && empty($user->phone) && !empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0) && empty($fieldValue->value))
            {
                $profilePercentage = "30";
            }
            elseif(empty($user->cover_id) && empty($user->avatar_id) && empty($user->phone) && empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0) && empty($fieldValue->value))
            {
                $profilePercentage = "30";
            }
            elseif(empty($user->cover_id) && empty($user->avatar_id) && empty($user->phone) && empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) > 0 || count($userTempHub) > 0) && empty($fieldValue->value))
            {
                $profilePercentage = "30";
            }
            elseif(empty($user->cover_id) && empty($user->avatar_id) && empty($user->phone) && empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0) && !empty($fieldValue->value))
            {
                $profilePercentage = "15";
            }
            //done



            elseif(!empty($user->cover_id) && !empty($user->avatar_id) && empty($user->phone) && empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0) && empty($fieldValue->value))
            {
                $profilePercentage = "30";
            }
            elseif(empty($user->cover_id) && empty($user->avatar_id) && !empty($user->phone) && !empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0) && empty($fieldValue->value))
            {
                $profilePercentage = "35";
            }
            elseif(empty($user->cover_id) && empty($user->avatar_id) && empty($user->phone) && empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) > 0 || count($userTempHub) > 0) && empty($fieldValue->value))
            {
                $profilePercentage = "50";
            }
            elseif(!empty($user->cover_id) && empty($user->avatar_id) && empty($user->phone) && empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) > 0 || count($userTempHub) > 0) && empty($fieldValue->value))
            {
                $profilePercentage = "40";
            }
            elseif(!empty($user->cover_id) && empty($user->avatar_id) && empty($user->phone) && empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0) && !empty($fieldValue->value))
            {
                $profilePercentage = "25";
            }
            elseif(!empty($user->cover_id) && empty($user->avatar_id) && !empty($user->phone) && empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0) && empty($fieldValue->value))
            {
                $profilePercentage = "25";
            }
            elseif(!empty($user->cover_id) && empty($user->avatar_id) && empty($user->phone) && empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0) && !empty($fieldValue->value))
            {
                $profilePercentage = "25";
            }
            elseif(!empty($user->cover_id) && empty($user->avatar_id) && empty($user->phone) && !empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0) && empty($fieldValue->value))
            {
                $profilePercentage = "40";
            }
            elseif(!empty($user->cover_id) && empty($user->avatar_id) && empty($user->phone) && empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0) && empty($fieldValue->value))
            {
                $profilePercentage = "40";
            }
            elseif(empty($user->cover_id) && !empty($user->avatar_id) && !empty($user->phone) && empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0) && empty($fieldValue->value))
            {
                $profilePercentage = "25";
            }//done
            elseif(empty($user->cover_id) && !empty($user->avatar_id) && empty($user->phone) && !empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0) && empty($fieldValue->value))
            {
                $profilePercentage = "40";
            }
            elseif(empty($user->cover_id) && !empty($user->avatar_id) && empty($user->phone) && empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0) && empty($fieldValue->value))
            {
                $profilePercentage = "40";
            }
            elseif(empty($user->cover_id) && !empty($user->avatar_id) && empty($user->phone) && empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) > 0 || count($userTempHub) > 0) && empty($fieldValue->value))
            {
                $profilePercentage = "40";
            }
            elseif(empty($user->cover_id) && !empty($user->avatar_id) && empty($user->phone) && empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0) && !empty($fieldValue->value))
            {
                $profilePercentage = "25";
            }
            elseif(empty($user->cover_id) && empty($user->avatar_id) && !empty($user->phone) && empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0) && empty($fieldValue->value))
            {
                $profilePercentage = "35";
            }
            elseif(empty($user->cover_id) && empty($user->avatar_id) && !empty($user->phone) && empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) > 0 || count($userTempHub) > 0) && empty($fieldValue->value))
            {
                $profilePercentage = "35";
            }
            elseif(empty($user->cover_id) && empty($user->avatar_id) && !empty($user->phone) && empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0) && !empty($fieldValue->value))
            {
                $profilePercentage = "20";
            }//done
            elseif(!empty($user->cover_id) && empty($user->avatar_id) && empty($user->phone) && !empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0) && empty($fieldValue->value))
            {
                $profilePercentage = "40";
            }
            elseif(empty($user->cover_id) && !empty($user->avatar_id) && empty($user->phone) && !empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0) && empty($fieldValue->value))
            {
                $profilePercentage = "40";
            }
            elseif(empty($user->cover_id) && empty($user->avatar_id) && empty($user->phone) && !empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0) && empty($fieldValue->value))
            {
                $profilePercentage = "50";
            }
            elseif(empty($user->cover_id) && empty($user->avatar_id) && empty($user->phone) && !empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) > 0 || count($userTempHub) > 0) && empty($fieldValue->value))
            {
                $profilePercentage = "50";
            }
            elseif(empty($user->cover_id) && empty($user->avatar_id) && empty($user->phone) && !empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0) && !empty($fieldValue->value))
            {
                $profilePercentage = "35";
            }//done
            elseif(!empty($user->cover_id) && empty($user->avatar_id) && empty($user->phone) && empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0) && empty($fieldValue->value))
            {
                $profilePercentage = "40";
            }
            elseif(empty($user->cover_id) && !empty($user->avatar_id) && empty($user->phone) && empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0) && empty($fieldValue->value))
            {
                $profilePercentage = "40";
            }
            elseif(empty($user->cover_id) && empty($user->avatar_id) && !empty($user->phone) && empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0) && empty($fieldValue->value))
            {
                $profilePercentage = "35";
            }
            elseif(empty($user->cover_id) && empty($user->avatar_id) && empty($user->phone) && !empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0) && empty($fieldValue->value))
            {
                $profilePercentage = "50";
            }
            elseif(empty($user->cover_id) && empty($user->avatar_id) && empty($user->phone) && empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0) && !empty($fieldValue->value))
            {
                $profilePercentage = "35";
            }//done

            elseif(!empty($user->cover_id) && !empty($user->avatar_id) && !empty($user->phone) && empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0) && empty($fieldValue->value))
            {
                $profilePercentage = "35";
            }
            elseif(empty($user->cover_id) && empty($user->avatar_id) && empty($user->phone) && !empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) > 0 || count($userTempHub) > 0) && empty($fieldValue->value))
            {
                $profilePercentage = "70";
            }
            elseif(!empty($user->cover_id) && !empty($user->avatar_id) && empty($user->phone) && !empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0) && empty($fieldValue->value))
            {
                $profilePercentage = "50";
            }
            elseif(!empty($user->cover_id) && !empty($user->avatar_id) && empty($user->phone) && empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0) && !empty($fieldValue->value))
            {
                $profilePercentage = "35";
            }
            elseif(!empty($user->cover_id) && !empty($user->avatar_id) && empty($user->phone) && empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0) && empty($fieldValue->value))
            {
                $profilePercentage = "50";
            }
            elseif(!empty($user->cover_id) && !empty($user->avatar_id) && empty($user->phone) && empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) > 0 || count($userTempHub) > 0) && empty($fieldValue->value))
            {
                $profilePercentage = "50";
            }//done



            elseif(empty($user->cover_id) && !empty($user->avatar_id) && !empty($user->phone) && !empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0) && empty($fieldValue->value))
            {
                $profilePercentage = "45";
            }
            elseif(empty($user->cover_id) && !empty($user->avatar_id) && !empty($user->phone) && empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0) && empty($fieldValue->value))
            {
                $profilePercentage = "50";
            }
            elseif(empty($user->cover_id) && !empty($user->avatar_id) && !empty($user->phone) && empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) > 0 || count($userTempHub) > 0) && empty($fieldValue->value))
            {
                $profilePercentage = "50";
            }
            elseif(empty($user->cover_id) && !empty($user->avatar_id) && !empty($user->phone) && empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0) && !empty($fieldValue->value))
            {
                $profilePercentage = "30";
            }//done


            elseif(!empty($user->cover_id) && empty($user->avatar_id) && !empty($user->phone) && !empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0) && empty($fieldValue->value))
            {
                $profilePercentage = "45";
            }
            elseif(empty($user->cover_id) && !empty($user->avatar_id) && !empty($user->phone) && !empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0) && empty($fieldValue->value))
            {
                $profilePercentage = "45";
            }
            elseif(empty($user->cover_id) && empty($user->avatar_id) && !empty($user->phone) && !empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0) && empty($fieldValue->value))
            {
                $profilePercentage = "60";
            }
            elseif(empty($user->cover_id) && empty($user->avatar_id) && !empty($user->phone) && !empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) > 0 || count($userTempHub) > 0) && empty($fieldValue->value))
            {
                $profilePercentage = "60";
            }
            elseif(empty($user->cover_id) && empty($user->avatar_id) && !empty($user->phone) && !empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0) && !empty($fieldValue->value))
            {
                $profilePercentage = "40";
            }//done

            elseif(!empty($user->cover_id) && empty($user->avatar_id) && empty($user->phone) && !empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0) && empty($fieldValue->value))
            {
                $profilePercentage = "60";
            }
            elseif(empty($user->cover_id) && !empty($user->avatar_id) && empty($user->phone) && !empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0) && empty($fieldValue->value))
            {
                $profilePercentage = "60";
            }
            elseif(empty($user->cover_id) && empty($user->avatar_id) && !empty($user->phone) && !empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0) && empty($fieldValue->value))
            {
                $profilePercentage = "55";
            }
            elseif(empty($user->cover_id) && empty($user->avatar_id) && empty($user->phone) && !empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0) && !empty($fieldValue->value))
            {
                $profilePercentage = "55";
            }//done

            elseif(!empty($user->cover_id) && empty($user->avatar_id) && empty($user->phone) && empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) > 0 || count($userTempHub) > 0) && empty($fieldValue->value))
            {
                $profilePercentage = "60";
            }
            elseif(empty($user->cover_id) && !empty($user->avatar_id) && empty($user->phone) && empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) > 0 || count($userTempHub) > 0) && empty($fieldValue->value))
            {
                $profilePercentage = "60";
            }
            elseif(empty($user->cover_id) && empty($user->avatar_id) && !empty($user->phone) && empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) > 0 || count($userTempHub) > 0) && empty($fieldValue->value))
            {
                $profilePercentage = "55";
            }
            elseif(empty($user->cover_id) && empty($user->avatar_id) && empty($user->phone) && empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) > 0 || count($userTempHub) > 0) && !empty($fieldValue->value))
            {
                $profilePercentage = "55";
            }//done

            elseif(!empty($user->cover_id) && !empty($user->avatar_id) && empty($user->phone) && empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) > 0 || count($userTempHub) > 0) && empty($fieldValue->value))
            {
                $profilePercentage = "50";
            }
            elseif(!empty($user->cover_id) && empty($user->avatar_id) && !empty($user->phone) && empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) > 0 || count($userTempHub) > 0) && empty($fieldValue->value))
            {
                $profilePercentage = "45";
            }
            elseif(!empty($user->cover_id) && empty($user->avatar_id) && empty($user->phone) && !empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) > 0 || count($userTempHub) > 0) && empty($fieldValue->value))
            {
                $profilePercentage = "50";
            }
            elseif(!empty($user->cover_id) && empty($user->avatar_id) && empty($user->phone) && empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) > 0 || count($userTempHub) > 0) && empty($fieldValue->value))
            {
                $profilePercentage = "60";
            }
            elseif(!empty($user->cover_id) && empty($user->avatar_id) && empty($user->phone) && empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) > 0 || count($userTempHub) > 0) && !empty($fieldValue->value))
            {
                $profilePercentage = "45";
            }//done



            elseif(!empty($user->cover_id) && !empty($user->avatar_id) && !empty($user->phone) && !empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0) && empty($fieldValue->value))
            {
                $profilePercentage = "55";
            }
            elseif(empty($user->cover_id) && !empty($user->avatar_id) && !empty($user->phone) && !empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0) && empty($fieldValue->value))
            {
                $profilePercentage = "65";
            }
            elseif(empty($user->cover_id) && empty($user->avatar_id) && !empty($user->phone) && !empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) > 0 || count($userTempHub) > 0) && empty($fieldValue->value))
            {
                $profilePercentage = "75";
            }
            elseif(empty($user->cover_id) && empty($user->avatar_id) && empty($user->phone) && !empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) > 0 || count($userTempHub) > 0) && !empty($fieldValue->value))
            {
                $profilePercentage = "75";
            }
            elseif(!empty($user->cover_id) && empty($user->avatar_id) && empty($user->phone) && empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) > 0 || count($userTempHub) > 0) && !empty($fieldValue->value))
            {
                $profilePercentage = "65";
            }//done
            

            elseif(!empty($user->cover_id) && !empty($user->avatar_id) && !empty($user->phone) && empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0) && empty($fieldValue->value))
            {
                $profilePercentage = "55";
            }
            elseif(!empty($user->cover_id) && !empty($user->avatar_id) && !empty($user->phone) && empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) > 0 || count($userTempHub) > 0) && empty($fieldValue->value))
            {
                $profilePercentage = "55";
            }
            elseif(empty($user->cover_id) && !empty($user->avatar_id) && !empty($user->phone) && !empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) > 0 || count($userTempHub) > 0) && empty($fieldValue->value))
            {
                $profilePercentage = "65";
            }
            elseif(empty($user->cover_id) && !empty($user->avatar_id) && !empty($user->phone) && !empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0) && empty($fieldValue->value))
            {
                $profilePercentage = "65";
            }
            elseif(empty($user->cover_id) && !empty($user->avatar_id) && !empty($user->phone) && !empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0) && !empty($fieldValue->value))
            {
                $profilePercentage = "50";
            }//done

            
            elseif(!empty($user->cover_id) && empty($user->avatar_id) && !empty($user->phone) && !empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0) && empty($fieldValue->value))
            {
                $profilePercentage = "65";
            }

            elseif(!empty($user->cover_id) && empty($user->avatar_id) && empty($user->phone) && !empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) > 0 || count($userTempHub) > 0) && empty($fieldValue->value) && empty($fieldValue))
            {
                $profilePercentage = "80";
            }
            elseif(empty($user->cover_id) && !empty($user->avatar_id) && empty($user->phone) && !empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) > 0 || count($userTempHub) > 0) && empty($fieldValue->value))
            {
                $profilePercentage = "80";
            }
            elseif(empty($user->cover_id) && empty($user->avatar_id) && empty($user->phone) && !empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) > 0 || count($userTempHub) > 0) && !empty($fieldValue->value))
            {
                $profilePercentage = "75";
            }
            /*elseif(empty($user->cover_id) && empty($user->avatar_id) && !empty($user->phone) && !empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) > 0 || count($userTempHub) > 0))
            {
                $profilePercentage = "80";
            }*/


            /*elseif(!empty($user->cover_id) && !empty($user->avatar_id) && !empty($user->phone) && !empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0))
            {
                $profilePercentage = "840";
            }*/
            elseif(!empty($user->cover_id) && !empty($user->avatar_id) && !empty($user->phone) && !empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) > 0 || count($userTempHub) > 0) && empty($fieldValue->value))
            {
                $profilePercentage = "75";
            }
            elseif(empty($user->cover_id) && !empty($user->avatar_id) && !empty($user->phone) && !empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) > 0 || count($userTempHub) > 0) && empty($fieldValue->value))
            {
                $profilePercentage = "85";
            }
            elseif(empty($user->cover_id) && empty($user->avatar_id) && !empty($user->phone) && !empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) > 0 || count($userTempHub) > 0) && !empty($fieldValue->value))
            {
                $profilePercentage = "80";
            }
            elseif(!empty($user->cover_id) && empty($user->avatar_id) && !empty($user->phone) && !empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) > 0 || count($userTempHub) > 0) && empty($fieldValue->value))
            {
                $profilePercentage = "85";
            }
            elseif(!empty($user->cover_id) && empty($user->avatar_id) && empty($user->phone) && !empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) > 0 || count($userTempHub) > 0) && !empty($fieldValue->value))
            {
                $profilePercentage = "85";
            }//done

            
            elseif(!empty($user->cover_id) && !empty($user->avatar_id) && empty($user->phone) && !empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) > 0 || count($userTempHub) > 0) && empty($fieldValue->value))
            {
                $profilePercentage = "90";
            }

            /*****/
            elseif(!empty($user->cover_id) && !empty($user->avatar_id) && !empty($user->phone) && !empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) > 0 || count($userTempHub) > 0) && empty($fieldValue->value))
            {
                $profilePercentage = "95";
            }

            elseif(empty($user->cover_id) && !empty($user->avatar_id) && empty($user->phone) && !empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) > 0 || count($userTempHub) > 0) && !empty($fieldValue->value))
            {
                $profilePercentage = "60";
            }

            elseif(empty($user->cover_id) && !empty($user->avatar_id) && empty($user->phone) && !empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) > 0 || count($userTempHub) > 0) && !empty($fieldValue->value))
            {
                $profilePercentage = "85";
            }
            /****/
            elseif(!empty($user->cover_id) && !empty($user->avatar_id) && !empty($user->phone) && empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) > 0 || count($userTempHub) > 0))
            {
                $profilePercentage = "80";
            }
            elseif(!empty($user->cover_id) && !empty($user->avatar_id) && !empty($user->phone) && empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) > 0 || count($userTempHub) > 0))
            {
                $profilePercentage = "80";
            }
            elseif(!empty($user->cover_id) && !empty($user->avatar_id) && !empty($user->phone) && !empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) > 0 || count($userTempHub) > 0))
            {
                $profilePercentage = "80";
            }
            elseif(!empty($user->cover_id) && !empty($user->avatar_id) && empty($user->phone) && !empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0))
            {
                $profilePercentage = "70";
            }
            elseif(!empty($user->cover_id) && !empty($user->avatar_id) && !empty($user->phone) && !empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) == 0))
            {
                $profilePercentage = "80";
            }
            elseif(!empty($user->cover_id) && !empty($user->avatar_id) && empty($user->phone) && !empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) > 0))
            {
                $profilePercentage = "60";
            }
            else
            {
                $profilePercentage = "100";
               
            }
        }

        if($user->profile_percentage != 100)
        {
            $userUpdate = User::where('user_id', $userId)->update(['profile_percentage' => $profilePercentage]);
            return $profilePercentage;
        }
        else
        {
            return $user->profile_percentage;
        }

        
       
    }
    

}
