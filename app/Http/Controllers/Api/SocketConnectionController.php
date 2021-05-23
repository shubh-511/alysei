<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CoreController;
use Modules\User\Entities\User; 
use App\SocketConnection;
use App\Http\Traits\UploadImageTrait;
use Modules\User\Entities\Role;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Auth; 
use Validator;
//use App\Events\UserRegisterEvent;

class SocketConnectionController extends CoreController
{
    use UploadImageTrait;
    public $successStatus = 200;
    public $validationStatus = 422;
    public $exceptionStatus = 409;
    public $unauthorisedStatus = 401;

    /*public $user = '';

    public function __construct(){

        $this->middleware(function ($request, $next) {

            $this->user = Auth::user();
            return $next($request);
        });
    }*/


    /*
     * Save connnection
    */
    public function saveConnection(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [ 
                'user_id' => 'required', 
                'socket_id' => 'required', 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $isConnectedUser = SocketConnection::where('user_id', $request->user_id)->where('socket_id', $request->socket_id)->first();
            if(empty($isConnectedUser))
            {
                $newConnection = new SocketConnection;
                $newConnection->user_id = $request->user_id;
                $newConnection->socket_id = $request->socket_id;
                $newConnection->status = '1';
                $newConnection->save();

                return response()->json(['success' => $this->successStatus,
                                     'data' => $newConnection,
                                    ], $this->successStatus);
                
            }
            else
            {
                return response()->json(['success' => $this->successStatus,
                                     //'data' => $newConnection,
                                    ], $this->successStatus);
            }      
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Get all connection by userid
    */
    public function getAllConnections($userId)
    {
        try
        {
            $isConnectedUser = SocketConnection::where('user_id', $userId)->get();
            if(count($isConnectedUser) > 0)
            {
                return response()->json(['success' => $this->successStatus,
                                     'data' => $isConnectedUser,
                                    ], $this->successStatus);
                
            }
            else
            {
                $message = "No socket connection for this userId";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus); 
            }    
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Remove connection by socketid
    */
    public function removeSocketConnection($socketId)
    {
        try
        {
            $isConnectedUser = SocketConnection::where('socket_id', $socketId)->first();
            if(!empty($isConnectedUser))
            {
                SocketConnection::where('socket_id', $socketId)->delete();
                return response()->json(['success' => $this->successStatus,
                                     'message' => 'Removed successfully',
                                    ], $this->successStatus);
                
            }
            else
            {
                $message = "Invalid socket Id";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus); 
            }    
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }
}