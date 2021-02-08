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
        $users = User::where('role_id','!=',1)->paginate(25);
        return view('admin.user.list', compact('users'));
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
        return view('admin.user.edit', compact('user'));
    }

}
