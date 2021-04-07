<?php

namespace Modules\User\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\User\Entities\User;
use Modules\User\Entities\BlockList;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth; 
use Validator;
//use App\Events\UserRegisterEvent;

class BlockUserController extends Controller
{
    public $successStatus = 200;
    public $validationStatus = 422;
    public $exceptionStatus = 409;
    public $unauthorisedStatus = 401;

    public $user = '';

    public function __construct(){

        $this->middleware(function ($request, $next) {

            $this->user = Auth::user();
            return $next($request);
        });
    }

    /***
    Block user
    ***/
    public function blockUser(Request $request)
    {
        try
        {
            $loggedInUser = $this->user;
            $validator = Validator::make($request->all(), [ 
            'block_user_id' => 'required', 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $user = User::where('user_id', $request->block_user_id)->first();
            
            if(!empty($user))
            {
                $blockList = new BlockList;
                $blockList->user_id = $loggedInUser->user_id;
                $blockList->block_user_id = $request->block_user_id;
                $blockList->save();

                return response()->json(['success' => $this->successStatus,
                                        'message' => $this->translate('messages.'."User blocked successfuly!","User blocked successfuly!")
                                         'data' => $blockList,
                                        ], $this->successStatus);
            }
            else
            {
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'."This user does not exist","This user does not exist")]], $this->exceptionStatus);
            }

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /***
    UnBlock user
    ***/
    public function unBlockUser(Request $request)
    {
        try
        {
            $loggedInUser = $this->user;
            $validator = Validator::make($request->all(), [ 
            'block_user_id' => 'required', 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $user = User::where('user_id', $request->block_user_id)->first();
            
            if(!empty($user))
            {
                $blockList = new BlockList::where('user_id', $loggedInUser->user_id)->where('block_user_id', $request->block_user_id)->first();
                if(!empty($blockList))
                {
                    $blockList = new BlockList::where('user_id', $loggedInUser->user_id)->where('block_user_id', $request->block_user_id)->delete();

                    return response()->json(['success' => $this->successStatus,
                                            'message' => $this->translate('messages.'."User unblocked successfuly!","User unblocked successfuly!")
                                        ], $this->successStatus);
                }
                else
                {
                    return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'."This user does not exist in your block list","This user does not exist in your block list")]], $this->exceptionStatus);
                }
            }
            else
            {
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'."This user does not exist","This user does not exist")]], $this->exceptionStatus);
            }

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }


    /***
    get Cities
    ***/
    public function getBlockedUserList(Request $request)
    {
        try
        {
            $loggedInUser = $this->user;
            $blockList = new BlockList::with('user:user_id')->with('blockuser:user_id')->where('user_id', $loggedInUser->user_id)->orderBy('block_list_id','DESC')->get();
            if(count($blockList) > 0)
            {
                return response()->json(['success' => $this->successStatus,
                                        'block_count_user'  =>  count($blockList),
                                        'data' => $blockList,
                                        ], $this->successStatus);
            }
            else
            {
                return response()->json(['success' => $this->exceptionStatus,
                                        'block_count_user'  =>  count($blockList),
                                        'errors' =>['exception' => $this->translate('messages.'."This user found in your block list","This user found in your block list")]],
                                         $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    
    
}
