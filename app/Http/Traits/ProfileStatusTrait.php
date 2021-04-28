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

        if($user->role_id == 10)
        {
            if(empty($user->cover_id) && empty($user->avatar_id) && empty($user->about))
            {
                $profilePercentage = "25";
            }
            elseif(!empty($user->cover_id) && empty($user->avatar_id) && empty($user->about))
            {
                $profilePercentage = "50";
            }
            elseif(empty($user->cover_id) && !empty($user->avatar_id) && empty($user->about))
            {
                $profilePercentage = "50";
            }
            elseif(empty($user->cover_id) && empty($user->avatar_id) && !empty($user->about))
            {
                $profilePercentage = "50";
            }
            elseif(!empty($user->cover_id) && empty($user->avatar_id) && !empty($user->about))
            {
                $profilePercentage = "75";
            }
            elseif(empty($user->cover_id) && !empty($user->avatar_id) && !empty($user->about))
            {
                $profilePercentage = "75";
            }
            elseif(!empty($user->cover_id) && !empty($user->avatar_id) && empty($user->about))
            {
                $profilePercentage = "75";
            }
            else
            {
                $profilePercentage = "100";
            }
        }
        else
        {
            if(empty($user->cover_id) && empty($user->avatar_id) && empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0))
            {
                $profilePercentage = "25";
            }
            elseif(!empty($user->cover_id) && empty($user->avatar_id) && empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0))
            {
                $profilePercentage = "30";
            }
            elseif(!empty($user->avatar_id) && empty($user->cover_id) && empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0))
            {
                $profilePercentage = "30";
            }
            elseif(empty($user->avatar_id) && empty($user->cover_id) && !empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0))
            {
                $profilePercentage = "40";
            }
            elseif(empty($user->avatar_id) && empty($user->cover_id) && empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0))
            {
                $profilePercentage = "50";
            }
            elseif(empty($user->avatar_id) && empty($user->cover_id) && empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) > 0 || count($userTempHub) > 0))
            {
                $profilePercentage = "50";
            }




            /***/
            
            elseif(!empty($user->avatar_id) && !empty($user->cover_id) && empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0))
            {
                $profilePercentage = "35";
            }
            elseif(!empty($user->avatar_id) && empty($user->cover_id) && !empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0))
            {
                $profilePercentage = "45";
            }
            elseif(!empty($user->avatar_id) && empty($user->cover_id) && empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0))
            {
                $profilePercentage = "55";
            }
            elseif(!empty($user->avatar_id) && empty($user->cover_id) && empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) > 0 || count($userTempHub) > 0))
            {
                $profilePercentage = "55";
            }
            /***/

            elseif(empty($user->avatar_id) && !empty($user->cover_id) && !empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0))
            {
                $profilePercentage = "45";
            }
           
            elseif(empty($user->avatar_id) && !empty($user->cover_id) && empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0))
            {
                $profilePercentage = "55";
            }
            elseif(empty($user->avatar_id) && !empty($user->cover_id) && empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) > 0 || count($userTempHub) > 0))
            {
                $profilePercentage = "55";
            }
            /***/
            elseif(empty($user->avatar_id) && !empty($user->cover_id) && !empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0))
            {
                $profilePercentage = "45";
            }
            elseif(empty($user->avatar_id) && empty($user->cover_id) && !empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0))
            {
                $profilePercentage = "55";
            }
            elseif(empty($user->avatar_id) && empty($user->cover_id) && !empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) > 0 || count($userTempHub) > 0))
            {
                $profilePercentage = "65";
            }
            
            /****/
            elseif(!empty($user->avatar_id) && empty($user->cover_id) && empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0))
            {
                $profilePercentage = "55";
            }
            elseif(empty($user->avatar_id) && !empty($user->cover_id) && empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0))
            {
                $profilePercentage = "55";
            }
            elseif(empty($user->avatar_id) && empty($user->cover_id) && !empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0))
            {
                $profilePercentage = "65";
            }
            elseif(empty($user->avatar_id) && empty($user->cover_id) && empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) > 0 || count($userTempHub) > 0))
            {
                $profilePercentage = "75";
            }
            /***/

            elseif(!empty($user->avatar_id) && !empty($user->cover_id) && !empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0))
            {
                $profilePercentage = "50";
            }
            elseif(!empty($user->avatar_id) && !empty($user->cover_id) && !empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0))
            {
                $profilePercentage = "50";
            }

            elseif(!empty($user->avatar_id) && !empty($user->cover_id) && empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0))
            {
                $profilePercentage = "60";
            }
            elseif(!empty($user->avatar_id) && !empty($user->cover_id) && empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) > 0 || count($userTempHub) > 0))
            {
                $profilePercentage = "60";
            }

            elseif(empty($user->avatar_id) && !empty($user->cover_id) && !empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) == 0 || count($userTempHub) == 0))
            {
                $profilePercentage = "70";
            }

            elseif(empty($user->avatar_id) && !empty($user->cover_id) && !empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) > 0 || count($userTempHub) > 0))
            {
                $profilePercentage = "70";
            }

            elseif(empty($user->avatar_id) && empty($user->cover_id) && !empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) > 0 || count($userTempHub) > 0))
            {
                $profilePercentage = "90";
            }

            elseif(!empty($user->avatar_id) && empty($user->cover_id) && empty($user->about) && count($FeaturedListing) > 0 && (count($userSelectedHub) > 0 || count($userTempHub) > 0))
            {
                $profilePercentage = "90";
            }
            elseif(!empty($user->avatar_id) && !empty($user->cover_id) && !empty($user->about) && count($FeaturedListing) == 0 && (count($userSelectedHub) > 0 || count($userTempHub) > 0))
            {
                $profilePercentage = "75";
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
