<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\User\Entities\User; 
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth; 
use Validator;
//use App\Events\UserRegisterEvent;

class UserController extends Controller
{
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
            $user = User::where('user_id', $userId)->update(['alysei_review' => '1']);
        }
        elseif($request->progress_level == 'alysei_certification')
        {
            $user = User::where('user_id', $userId)->update(['alysei_certification' => '1']);
        }
        elseif($request->progress_level == 'alysei_recognition')
        {
            $user = User::where('user_id', $userId)->update(['alysei_recognition' => '1']);
        }
        elseif($request->progress_level == 'alysei_qualitymark')
        {
            $user = User::where('user_id', $userId)->update(['alysei_qualitymark' => '1']);
        }
        elseif($request->progress_level == 'level_empty')
        {
            return redirect('login/users/edit/'.$userId)->with('success','All steps has been completed');
        }
             
        return redirect('login/users/edit/'.$userId)->with('success','Updated successfully');
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
