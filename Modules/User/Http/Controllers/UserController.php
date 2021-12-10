<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CoreController;
use Modules\User\Entities\User; 
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth; 
use Validator;
use App\Notification;
use Modules\User\Entities\DeviceToken; 
use App\Http\Traits\NotificationTrait;
use DB;
//use App\Events\UserRegisterEvent;

class UserController extends CoreController
{
    use NotificationTrait;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    /***
    dashboard
    ***/
    public function dashboard(Request $request)
    {
        return view('admin.home');
    }

    
    /***
    logout
    ***/
    public function logout(Request $request)
    {
        Auth::logout();
        return Redirect('login');
    }

    /***
    user list method
    ***/
    public function list(Request $request)
    { 
        $users = User::where('role_id','!=',1)->orderBy('user_id', 'DESC')->paginate(25);
        return view('user::admin.user.list', compact('users'));
    }
    
    
    public function userDelete(Request $request)
    {
        User::where('user_id', $request->id)->delete();
        DB::table('events')->where('user_id', $request->id)->delete();
        DB::table('blogs')->where('user_id', $request->id)->delete();
        DB::table('trips')->where('user_id', $request->id)->delete();
        DB::table('marketplace_stores')->where('user_id', $request->id)->delete();
        DB::table('recipes')->where('user_id', $request->id)->delete();
        DB::table('awards')->where('user_id', $request->id)->delete();
        DB::table('featured_listings')->where('user_id', $request->id)->delete();
        DB::table('marketplace_products')->where('user_id', $request->id)->delete();
        DB::table('certificates')->where('user_id', $request->id)->delete();
        DB::table('connections')->where('user_id', $request->id)->delete();
        DB::table('followers')->where('user_id', $request->id)->delete();
        DB::table('user_selected_hubs')->where('user_id', $request->id)->delete();
        DB::table('device_tokens')->where('user_id', $request->id)->delete();
        DB::table('featured_listing_values')->where('user_id', $request->id)->delete();
        DB::table('event_likes')->where('user_id', $request->id)->delete();
        DB::table('temp_hubs')->where('user_id', $request->id)->delete();
        DB::table('preference_map_users')->where('user_id', $request->id)->delete();
        DB::table('recipe_review_ratings')->where('user_id', $request->id)->delete();
        DB::table('recipe_favourites')->where('user_id', $request->id)->delete();
        DB::table('marketplace_favourites')->where('user_id', $request->id)->delete();
        DB::table('marketplace_review_ratings')->where('user_id', $request->id)->delete();
        DB::table('marketplace_recent_search')->where('user_id', $request->id)->delete();
        DB::table('marketplace_product_enqueries')->where('user_id', $request->id)->delete();
        DB::table('activity_actions')->where('subject_id', $request->id)->where('subject_type','user')->delete();
        DB::table('core_comments')->where('resource_id', $request->id)->where('poster_type','user')->delete();
        DB::table('activity_likes')->where('resource_id', $request->id)->where('poster_type','user')->delete();
        DB::table('block_lists')->where('block_user_id', $request->id)->delete();
        
    }

    /***
    update user status
    ***/
    public function userStatus(Request $request)
    {
        $sql= User::whereIn('user_id',$request->id)
        ->update(['account_enabled' => $request->status]);
             
        return $sql;
    }

    /***
    user edit method
    ***/
    public function edit(Request $request, $id)
    { 
        $user = User::where('user_id',$id)->first();
        return view('user::admin.user.edit', compact('user'));
    }

    /***
    update alysei progress status
    ***/
    public function updateProgressStatus(Request $request, $userId = '')
    {
        if($request->progress_level == 'alysei_review')
        {
            $title = "Welcome on board! You are now a Certified Alysei member. You can now complete your profile to access the Alysei world.";
            $user = User::where('user_id', $userId)->update(['alysei_review' => '1']);
        }
        elseif($request->progress_level == 'alysei_certification')
        {
            $title = "Your profile is certified from admin";
            $user = User::where('user_id', $userId)->update(['alysei_certification' => '1']);
        }
        elseif($request->progress_level == 'alysei_recognition')
        {
            $title = "Your profile has been recognised from admin";
            $user = User::where('user_id', $userId)->update(['alysei_recognition' => '1']);
        }
        elseif($request->progress_level == 'alysei_qualitymark')
        {
            $title = "Your profile has been marked as qualitymark";
            $user = User::where('user_id', $userId)->update(['alysei_qualitymark' => '1']);
        }
        elseif($request->progress_level == 'level_empty')
        {
            return redirect('dashboard/users/edit/'.$userId)->with('success','All steps has been completed');
        }

        $admin = User::where('role_id', '1')->first();

        $saveNotification = new Notification;
        $saveNotification->from = $admin->user_id;
        $saveNotification->to = $userId;
        $saveNotification->notification_type = '9';
        $saveNotification->title = $this->translate('messages.'.$title,$title);
        $saveNotification->redirect_to = 'membership_progress';
        $saveNotification->redirect_to_id = 0;
        $saveNotification->save();

        $tokens = DeviceToken::where('user_id', $userId)->get();
        if(count($tokens) > 0)
        {

            $collectedTokenArray = $tokens->pluck('device_token');

            
            
            $this->sendNotification($collectedTokenArray, $title, $saveNotification->redirect_to, $saveNotification->redirect_to_id,null,null,null,null,null,null,null,null,null,null);

            $this->sendNotificationToIOS($collectedTokenArray, $title, $saveNotification->redirect_to, $saveNotification->redirect_to_id,null,null,null,null,null,null,null,null,null,null);

            
        }
             
        return redirect('dashboard/users/edit/'.$userId)->with('success','Updated successfully');
    }

    /***
    Alysei Review Status
    ***/
    public function reviewStatus(Request $request)
    {
        //echo $request->status; die;
        if($request->isMethod('post')){
            $user = User::where('user_id', $request->id)->update(['alysei_review' => $request->status]);
            /*$user->alysei_review = $request->status;
            $user->save();*/
            return 1;
        }
    }

    /***
    Alysei Certification Status
    ***/
    public function certifiedStatus(Request $request)
    {
        
        if($request->isMethod('post')){
            $user = User::where('user_id', $request->id)->update(['alysei_certification' => $request->status]);
            /*$user->alysei_review = $request->status;
            $user->save();*/
            return 1;
        }
    }

    /***
    Alysei Recognised Status
    ***/
    public function recognisedStatus(Request $request)
    {
        //echo $request->status; die;
        if($request->isMethod('post')){
            $user = User::where('user_id', $request->id)->update(['alysei_recognition' => $request->status]);
            /*$user->alysei_review = $request->status;
            $user->save();*/
            return 1;
        }
    }

    /***
    Alysei Quality Marked Status
    ***/
    public function qmStatus(Request $request)
    {
        //echo $request->status; die;
        if($request->isMethod('post')){
            $user = User::where('user_id', $request->id)->update(['alysei_qualitymark' => $request->status]);
            /*$user->alysei_review = $request->status;
            $user->save();*/
            return 1;
        }
    }

   

   

}
