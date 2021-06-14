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
use Modules\Activity\Entities\CoreComment;
use Carbon\Carbon;
use DB;
use Modules\Activity\Entities\ActivityLike;
use Modules\Activity\Entities\ActivityAction;
use Modules\Activity\Entities\ActivityActionType;
use Modules\Activity\Entities\ActivityAttachment;
use Modules\Activity\Entities\ActivityAttachmentLink;
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
     * Comment Post
     * @Params $request
     */
    public function commentPost(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [ 
                'user_id'  => 'required',
                'post_id' => 'required',
                'comment' => 'required', 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $activityPost = ActivityAction::with('attachments.attachment_link','subject_id')->where('activity_action_id', $request->post_id)->first();
            if(!empty($activityPost))
            {
                $activityActionType = ActivityActionType::where('activity_action_type_id', $activityPost->type)->first();
                $actionType = $this->checkActionType($activityActionType->type);
                if($actionType[1] > 0)
                {
                    return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $actionType[0]]], $this->exceptionStatus);
                }
                else
                {
                    $activityComment = new CoreComment;
                    $activityComment->resource_type = "user";
                    $activityComment->resource_id = $request->post_id;
                    $activityComment->poster_type = "user";
                    $activityComment->poster_id = $request->user_id;
                    $activityComment->body = $request->comment;
                    $activityComment->save();

                    $activityPost->comment_count = $activityPost->comment_count + 1;
                    $activityPost->save();

                    $message = "Your comment has been posted successfully";
                    return response()->json(['success' => $this->successStatus,
                                             'message' => $this->translate('messages.'.$message,$message),
                                            ], $this->successStatus);
                }
            }
            else
            {
                $message = "Invalid post id";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Like Post
     * @Params $request
     */
    public function likeUnlikePost(Request $request)
    {
        try
        {
            //$user = $this->user;
            $validator = Validator::make($request->all(), [ 
                //'ww' => 'required',
                'user_id' => 'required',
                'post_id' => 'required',
                'like_or_unlike' => 'required', // 1 for like 0 for unlike
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $activityPost = ActivityAction::with('attachments.attachment_link','subject_id')->where('activity_action_id', $request->post_id)->first();
            if(!empty($activityPost))
            {
                if($request->like_or_unlike == 1)
                {
                    $isLikedActivityPost = ActivityLike::where('resource_id', $request->post_id)->where('poster_id', $request->user_id)->first();


                    /*if(!empty($isLikedActivityPost))
                    {
                        $message = "You have already liked this post";
                        return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus); 
                    }
                    else
                    {*/
                        $activityLike = new ActivityLike;
                        $activityLike->resource_id = $request->post_id;
                        $activityLike->poster_type = "user";
                        $activityLike->poster_id = $request->user_id;
                        $activityLike->save();

                        $activityPost->like_count = $activityPost->like_count + 1;
                        $activityPost->save();

                        $message = "You liked this post";
                        return response()->json(['success' => $this->successStatus,
                                                 'message' => $this->translate('messages.'.$message,$message),
                                                ], $this->successStatus);
                    //}
                }
                elseif($request->like_or_unlike == 0)
                {
                    $isLikedActivityPost = ActivityLike::where('resource_id', $request->post_id)->where('poster_id', $request->user_id)->first();
                    if(!empty($isLikedActivityPost))
                    {
                        $isUnlikedActivityPost = ActivityLike::where('resource_id', $request->post_id)->where('poster_id', $request->user_id)->delete();
                        if($isUnlikedActivityPost == 1)
                        {
                            $activityPost->like_count = $activityPost->like_count - 1;
                            $activityPost->save();

                            $message = "You unliked this post";
                            return response()->json(['success' => $this->successStatus,
                                                 'message' => $this->translate('messages.'.$message,$message),
                                                ], $this->successStatus);
                        }
                        else
                        {
                            $message = "You have to first like this post";
                            return response()->json(['success' => $this->exceptionStatus,
                                                 'message' => $this->translate('messages.'.$message,$message),
                                                ], $this->exceptionStatus);
                        }
                    }
                    else
                    {
                        $message = "You have not liked this post";
                        return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                    }
                }
                else
                {
                    $message = "Invalid like/unlike type";
                    return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                }
                
            }
            else
            {
                $message = "Invalid post id";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }


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

            $isConnectedUser = SocketConnection::where('user_id', $request->user_id)->first();
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
                $connection = SocketConnection::where('user_id', $request->user_id)->update(["socket_id" => $request->socket_id]);
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
    public function getAllConnections($userId,$postOwner='')
    {
        try
        {
            $isConnectedUser = SocketConnection::where('user_id', $userId)->orWhere('user_id', $postOwner)->get();
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

    /*
     * Check Action Type
     * @Params $type
     */

    public function checkActionType($type)
    {
        $status = [];
        $activityActionType = ActivityActionType::where("type", $type)->first();
        if(!empty($activityActionType))
        {
            if($activityActionType->commentable == '0')
            {
                $status = [$this->translate('messages.'."You are not authorised to comment on this post","You are not authorised to comment on this post"), 1];
            }
            else
            {
                $status = [$this->translate('messages.'."Success","Success"), 0];
            }            
        }
        else
        {
            $status = [$this->translate('messages.'."Invalid action type","Invalid action type"), 3];
        }
        
        return $status;
    }
}