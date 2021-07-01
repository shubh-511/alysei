<?php

namespace Modules\Activity\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CoreController;
use App\Http\Traits\UploadImageTrait;
use Modules\Activity\Entities\ActivityAction;
use Modules\Activity\Entities\CoreComment;
use Modules\User\Entities\UserSelectedHub;
use App\Attachment;
use Modules\Activity\Entities\Connection;
use Modules\Activity\Entities\Follower;
use Modules\Activity\Entities\ActivityLike;
use Modules\Activity\Entities\ActivityActionType;
use Modules\Activity\Entities\ActivityAttachment;
use Modules\Activity\Entities\ActivityAttachmentLink;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth; 
use Validator;
//use App\Events\UserRegisterEvent;

class ActivityController extends CoreController
{
    use UploadImageTrait;
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

    /*
     * Add Post
     * @Params $request
     */
    public function addPost(Request $request)
    {
        try
        {
            $user = $this->user;
            
            $validator = Validator::make($request->all(), [ 
                'action_type' => 'required|max:190',
                'privacy'     => 'required' 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            /*$requestFields = $request->params;
            
            $requestedFields = $requestFields;
            
            $rules = $this->validateData($requestedFields, 1);

            $validator = Validator::make($requestedFields, $rules);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }*/

            $actionType = $this->checkActionType($request->action_type, 1);
            if($actionType[1] > 0)
            {
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $actionType[0]]], $this->exceptionStatus);
            }
            else
            {
                $activityActionType = ActivityActionType::where("type", $request->action_type)->first();
                $activityAction = new ActivityAction;
                $activityAction->type = $activityActionType->activity_action_type_id;
                $activityAction->subject_type = "user";
                $activityAction->subject_id = $user->user_id;
                $activityAction->object_type = "user";
                $activityAction->object_id = $user->user_id;
                $activityAction->body = $request->body;
                $activityAction->privacy = $request->privacy;
                if(!empty($request->attachments))
                {
                    $activityAction->attachment_count = count($request->attachments);
                }
                else
                {
                    $activityAction->attachment_count = 0;   
                }
                
                $activityAction->save();
            }

            if(!empty($request->attachments) && count($request->attachments) > 0)
            {
                $this->uploadAttchments($request->attachments, $activityAction->activity_action_id);
            }
            if($activityAction)
            {
                return response()->json(['success' => $this->successStatus,
                                         'message' => $this->translate('messages.'."Post added successfuly!","Post added successfuly!"),
                                        ], $this->successStatus);
            }
            else
            {
                $message = 'Something went wrong!';
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
           
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Share Post
     * @Params $request
     */
    public function sharePost(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'action_type' => 'required|max:190',
                'privacy'     => 'required',
                'shared_post_id'    => 'required' 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

           /* $requestFields = $request->params;
            
            $requestedFields = $requestFields;
            
            $rules = $this->validateSharePostData($requestedFields);

            $validator = Validator::make($requestedFields, $rules);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }*/

            $actionType = $this->checkActionType($request->action_type, 5);
            if($actionType[1] > 0)
            {
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $actionType[0]]], $this->exceptionStatus);
            }
            else
            {
                $activityActionType = ActivityActionType::where("type", $request->action_type)->first();
                $activityAction = new ActivityAction;
                $activityAction->type = $activityActionType->activity_action_type_id;
                $activityAction->subject_type = "user";
                $activityAction->subject_id = $user->user_id;
                $activityAction->object_type = "user";
                $activityAction->object_id = $user->user_id;
                $activityAction->body = $request->body;
                $activityAction->privacy = $request->privacy;
                $activityAction->shared_post_id = $request->shared_post_id;
                //$activityAction->attachment_count = count($requestedFields["attachments"]);
                $activityAction->save();
            }

            
            if($activityAction)
            {
                return response()->json(['success' => $this->successStatus,
                                         'message' => $this->translate('messages.'."Post shared successfuly!","Post shared successfuly!"),
                                        ], $this->successStatus);
            }
            else
            {
                $message = 'Something went wrong!';
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
           
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Edit Post
     * @Params $request
     */
    public function editPost(Request $request)
    {
        try
        {
            $user = $this->user;
            /*$requestFields = $request->params;
            
            $requestedFields = $requestFields;
            
            $rules = $this->validateData($requestedFields, 2);*/

            $validator = Validator::make($request->all(), [ 
                'post_id' => 'required',
                'action_type' => 'required|max:190',
                'privacy'     => 'required'
            ]);


            //$validator = Validator::make($requestedFields, $rules);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $actionType = $this->checkActionType($request->action_type, 2);
            if($actionType[1] > 0)
            {
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $actionType[0]]], $this->exceptionStatus);
            }
            else
            {
                $activityActionType = ActivityActionType::where("type", $request->action_type)->first();
                $activityAction = ActivityAction::where('activity_action_id', $request->post_id)->where('subject_id', $user->user_id)->first();
                if(!empty($activityAction))
                {
                    $activityAction->type = $activityActionType->activity_action_type_id;
                    $activityAction->body = $request->body;
                    $activityAction->privacy = $request->privacy;
                    $activityAction->save();

                    return response()->json(['success' => $this->successStatus,
                                        'message' => $this->translate('messages.'."Post updated successfuly!","Post updated successfuly!"),
                                        ], $this->successStatus);
                }
                else
                {
                    $message = $this->translate('messages.'."Invalid post id","Invalid post id");
                    return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $message]], $this->exceptionStatus);
                }
                
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Delete Post
     * @Params $request
     */
    public function deletePost(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'post_id' => 'required', 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $activityPost = ActivityAction::where('activity_action_id', $request->post_id)->where('subject_id', $user->user_id)->first();
            if(!empty($activityPost))
            {
                $this->deleteSelectedPost($request->post_id, $user->user_id);

                $message = "Post deleted successfully";
                return response()->json(['success' => $this->successStatus,
                                         'message' => $this->translate('messages.'.$message,$message),
                                        ], $this->successStatus);
            }
            else
            {
                $message = "This post does not exist";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus); 
            }
            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Get Activity Feeds
     * @Params $request
     */
    public function getActivityFeeds()
    {
        try
        {
            $user = $this->user;
            $loggedInUserHubs = UserSelectedHub::where('user_id', $user->user_id)->get();
            $loggedInUserHubs = $loggedInUserHubs->pluck('hub_id')->toArray();


            $myConnections = Connection::select('*','user_id as poster_id')->where('resource_id', $user->user_id)->where('is_approved', '1')->get();
            $myConnections = $myConnections->pluck('poster_id');

            $myFollowers = Follower::select('*','follow_user_id as poster_id')->where('user_id', $user->user_id)->get();
            $myFollowers = $myFollowers->pluck('poster_id');

            $merged = $myConnections->merge($myFollowers);
            $userIds = $merged->all();
            

            if(count($userIds) > 0)
            {
                array_push($userIds, $user->user_id);
                $userIds = array_unique($userIds);
                $activityPosts = ActivityAction::select('activity_action_id','type','subject_id','body','shared_post_id','attachment_count','comment_count','like_count','privacy','created_at')
                ->with('attachments.attachment_link')
                ->with('subject_id:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','subject_id.avatar_id')
                ->whereIn('subject_id', $userIds)
                ->where('privacy', 'public')
                ->orWhere('privacy', 'followers')
                //->inRandomOrder()
                ->orderBy('created_at', 'DESC')
                ->paginate(10);
                
                
            }
            else
            {
                $activityPosts = ActivityAction::select('activity_action_id','type','subject_id','body','shared_post_id','attachment_count','comment_count','like_count','privacy','created_at')
                ->with('attachments.attachment_link')
                ->with('subject_id:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','subject_id.avatar_id')
                ->where('privacy', 'public')
                ->orWhere('subject_id', $user->user_id)
                //->inRandomOrder()
                ->orderBy('created_at', 'DESC')
                ->paginate(10);
            }

            if(count($activityPosts) > 0)
            {
                foreach($activityPosts as $key => $activityPost)
                {
                    //is activity liked
                    $isLikedActivityPost = ActivityLike::where('resource_id', $activityPost->activity_action_id)->where('poster_id', $user->user_id)->first();
                    if(!empty($isLikedActivityPost))
                    {
                        $activityPosts[$key]->like_flag = 1;
                    }
                    else
                    {
                        $activityPosts[$key]->like_flag = 0;
                    }

                    //shared post
                    $activityShared = ActivityAction::select('activity_action_id','type','subject_id','body','shared_post_id','attachment_count','comment_count','like_count','privacy','created_at')->with('attachments.attachment_link')->with('subject_id:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','subject_id.avatar_id')->where('activity_action_id', $activityPost->shared_post_id)->first();
                    if(!empty($activityShared))
                    {
                        $activityPosts[$key]->shared_post_id = $activityShared;
                    }
                    else
                    {
                        $activityPosts[$key]->shared_post_id = '';   
                    }

                    $activityPosts[$key]->posted_at = $activityPost->created_at->diffForHumans();   
                }

                return response()->json(['success' => $this->successStatus,
                                         'data' => $activityPosts,
                                        ], $this->successStatus);
            }
            else
            {
                $message = "No post to display";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus); 
            }
            
            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Get Post Details
     * @Params $request
     */
    public function getPostDetails(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'post_id' => 'required', 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $activityPost = ActivityAction::with('attachments.attachment_link','subject_id')->where('activity_action_id', $request->post_id)->where('subject_id', $user->user_id)->first();
            if(!empty($activityPost))
            {
                $activityActionType = ActivityActionType::where('activity_action_type_id', $activityPost->type)->first();
                $actionType = $this->checkActionType($activityActionType->type, 3);
                if($actionType[1] > 0)
                {
                    return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $actionType[0]]], $this->exceptionStatus);
                }
                else
                {
                    return response()->json(['success' => $this->successStatus,
                                         'data' => $activityPost,
                                        ], $this->successStatus);
                }
            }
            else
            {
                $message = "Invalid post Id";
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
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
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
                    $isLikedActivityPost = ActivityLike::where('resource_id', $request->post_id)->where('poster_id', $user->user_id)->first();


                    if(!empty($isLikedActivityPost))
                    {
                        $message = "You have already liked this post";
                        return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus); 
                    }
                    else
                    {
                        $activityLike = new ActivityLike;
                        $activityLike->resource_id = $request->post_id;
                        $activityLike->poster_type = "user";
                        $activityLike->poster_id = $user->user_id;
                        $activityLike->save();

                        $activityPost->like_count = $activityPost->like_count + 1;
                        $activityPost->save();

                        $message = "You liked this post";
                        return response()->json(['success' => $this->successStatus,
                                                 'message' => $this->translate('messages.'.$message,$message),
                                                ], $this->successStatus);
                    }
                }
                elseif($request->like_or_unlike == 0)
                {
                    $isLikedActivityPost = ActivityLike::where('resource_id', $request->post_id)->where('poster_id', $user->user_id)->first();
                    if(!empty($isLikedActivityPost))
                    {
                        $isUnlikedActivityPost = ActivityLike::where('resource_id', $request->post_id)->where('poster_id', $user->user_id)->delete();
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
     * Comment Post
     * @Params $request
     */
    public function commentPost(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
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
                $actionType = $this->checkActionType($activityActionType->type, 4);
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
                    $activityComment->poster_id = $user->user_id;
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
     * Comment Post
     * @Params $request
     */
    public function replyPost(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'post_id' => 'required',
                'comment_id' => 'required',
                'reply' => 'required', 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $activityPost = ActivityAction::with('attachments.attachment_link','subject_id')->where('activity_action_id', $request->post_id)->first();
            if(!empty($activityPost))
            {
                $activityComment = new CoreComment;
                $activityComment->resource_type = "user";
                $activityComment->resource_id = $request->post_id;
                $activityComment->poster_type = "user";
                $activityComment->poster_id = $user->user_id;
                $activityComment->body = $request->reply;
                $activityComment->parent_id = $request->comment_id;
                $activityComment->save();

                $message = "Your reply has been posted successfully";
                return response()->json(['success' => $this->successStatus,
                                         'message' => $this->translate('messages.'.$message,$message),
                                        ], $this->successStatus);
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
     * Comment Post
     * @Params $request
     */
    public function deletePostComment(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'comment_id' => 'required',
                'post_id' => 'required',
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $activityPostComment = CoreComment::where('core_comment_id', $request->comment_id)->where('poster_id', $user->user_id)->first();
            if(!empty($activityPostComment))
            {
                $activityPostCommentDelete = CoreComment::where('core_comment_id', $request->comment_id)->where('poster_id', $user->user_id)->delete();
                if($activityPostCommentDelete == 1)
                {
                    $activityPost = ActivityAction::where('activity_action_id', $request->post_id)->first();
                    $activityPost->comment_count = $activityPost->comment_count - 1;
                    $activityPost->save();

                    $message = "Your comment has been deleted successfully";
                    return response()->json(['success' => $this->successStatus,
                                         'message' => $this->translate('messages.'.$message,$message),
                                        ], $this->successStatus);
                }
                else
                {
                    $message = "Invalid comment";
                    return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                }
                
                
            }
            else
            {
                $message = "Invalid comment";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Get Member Post tab
     *
     */
    public function getAllUserPosts(Request $request, $havingAttachment)
    {
        try
        {
            $loggedInUser = $this->user;
            if($havingAttachment == 1)
            {
                if(!empty($request->per_page))
                {
                    $activityPost = ActivityAction::select('activity_action_id','type','subject_id','body','shared_post_id','attachment_count','comment_count','like_count','privacy','created_at')
                    ->with('attachments.attachment_link')
                    ->with('subject_id:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','subject_id.avatar_id')
                    ->where('subject_id', $loggedInUser->user_id)
                    ->where('attachment_count','>', 0)
                    ->orderBy('activity_action_id','DESC')->paginate($request->per_page);
                }
                else
                {
                    $activityPost = ActivityAction::select('activity_action_id','type','subject_id','body','shared_post_id','attachment_count','comment_count','like_count','privacy','created_at')
                    ->with('attachments.attachment_link')
                    ->with('subject_id:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','subject_id.avatar_id')
                    ->where('subject_id', $loggedInUser->user_id)
                    ->where('attachment_count','>', 0)
                    ->orderBy('activity_action_id','DESC')->paginate(15);
                }
                
            }
            elseif($havingAttachment == 0)
            {
                if(!empty($request->per_page))
                {
                    $activityPost = ActivityAction::select('activity_action_id','type','subject_id','body','shared_post_id','attachment_count','comment_count','like_count','privacy','created_at')
                    ->with('attachments.attachment_link')
                    ->with('subject_id:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','subject_id.avatar_id')
                    ->where('subject_id', $loggedInUser->user_id)
                    ->orderBy('activity_action_id','DESC')
                    ->paginate($request->per_page);
                }
                else
                {
                    $activityPost = ActivityAction::select('activity_action_id','type','subject_id','body','shared_post_id','attachment_count','comment_count','like_count','privacy','created_at')
                    ->with('attachments.attachment_link')
                    ->with('subject_id:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','subject_id.avatar_id')
                    ->where('subject_id', $loggedInUser->user_id)
                    ->orderBy('activity_action_id','DESC')
                    ->paginate(15);
                }
                
            }
            else
            {
                $message = "Please select either 1 or 0";
                    return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
            
            if(!empty($activityPost))
            {
                return response()->json(['success' => $this->successStatus,
                                         'data' => $activityPost,
                                        ], $this->successStatus);
            }
            else
            {
                $message = "No post found";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()], $this->exceptionStatus); 
        }
    }

    /*
     * Delete Post
     * @Params $postId, $userId
     */

    public function deleteSelectedPost($postId, $userId)
    {
        $isDeletedPost = ActivityAction::where('activity_action_id', $postId)->where('subject_id', $userId)->delete();
        if($isDeletedPost == 1)
        {
            $activityAttchments = ActivityAttachment::where('action_id', $postId)->get();
            if(count($activityAttchments) > 0)
            {
                foreach($activityAttchments as $activityAttchment)
                {
                    $this->deletePostAttachment($activityAttchment->id);
                }
                
                $isDeletedActivityAttachment = ActivityAttachment::where('action_id', $postId)->delete();

            }
        }
    }

    /*
     * Validate Data
     * @Params $requestedfields
     */

    public function validateData($requestedFields, $addOrUpdate)
    {
        $rules = [];
        if($addOrUpdate == 1) // validation for adding activity post
        {
            foreach ($requestedFields as $key => $field) {
                if($key == 'action_type'){
                    $rules[$key] = 'required|max:190';
                }
                elseif($key == 'privacy'){
                    $rules[$key] = 'required|max:190';
                }
            }
        }
        elseif($addOrUpdate == 2) // validation for updating activity post
        {
            foreach ($requestedFields as $key => $field) {
                if($key == 'post_id'){
                    $rules[$key] = 'required';
                }
                elseif($key == 'action_type'){
                    $rules[$key] = 'required|max:190';
                }
                elseif($key == 'privacy'){
                    $rules[$key] = 'required|max:190';
                }
            }
        }
        
        return $rules;
    }

    public function validateSharePostData($requestedFields)
    {
        $rules = [];
          foreach ($requestedFields as $key => $field) {
                if($key == 'action_type'){
                    $rules[$key] = 'required|max:190';
                }
                elseif($key == 'privacy'){
                    $rules[$key] = 'required|max:190';
                }
                elseif($key == 'shared_post_id'){
                    $rules[$key] = 'required';
                }
            }
        return $rules;    
    }

    /*
     * Check Action Type
     * @Params $type
     */

    public function checkActionType($type, $addOrUpdate)
    {
        $status = [];
        $activityActionType = ActivityActionType::where("type", $type)->first();
        if(!empty($activityActionType))
        {
            if($addOrUpdate == 1) // adding a new activity post
            {
                if($activityActionType->enabled == '0')
                {
                    $status = [$this->translate('messages.'."Currently you are not authorised to post anything","Currently you are not authorised to post anything"), 1];
                }
                elseif($activityActionType->attachable == '0')
                {
                    $status = [$this->translate('messages.'."You are not authorised to attach a media","You are not authorised to attach a media"), 2];
                }
                else
                {
                    $status = [$this->translate('messages.'."Success","Success"), 0];
                }
            }
            elseif($addOrUpdate == 2) // updating existing activity post
            {
                if($activityActionType->editable == '0')
                {
                    $status = [$this->translate('messages.'."Currently you are not authorised to edit this post","Currently you are not authorised to edit this post"), 1];
                }
                else
                {
                    $status = [$this->translate('messages.'."Success","Success"), 0];
                }
            }
            elseif($addOrUpdate == 3) // check is_displayble activity post
            {
                if($activityActionType->displayable == '0')
                {
                    $status = [$this->translate('messages.'."Currently you are not authorised to view this post","Currently you are not authorised to view this post"), 1];
                }
                else
                {
                    $status = [$this->translate('messages.'."Success","Success"), 0];
                }
            }
            elseif($addOrUpdate == 4) // check is_commentable activity post
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
            elseif($addOrUpdate == 5) // check is_sharable activity post
            {
                if($activityActionType->shareable == '0')
                {
                    $status = [$this->translate('messages.'."You are not authorised to share this post","You are not authorised to share this post"), 1];
                }
                else
                {
                    $status = [$this->translate('messages.'."Success","Success"), 0];
                }
            }
            
        }
        else
        {
            $status = [$this->translate('messages.'."Invalid action type","Invalid action type"), 3];
        }
        
        return $status;
    }

    /*
    * Upload Post Attachments
    * @Params $attchments,$actionId
    */

    public function uploadAttchments($attchments, $actionId)
    {
        foreach($attchments as $key => $attachment)
        {
            /*if($attchments[$key]->hasFile($attchments[$key]))
            {*/
                $attachmentLinkId = $this->postAttchment($attachment);
                //$attachmentLinkId = $this->createPostImage($attachment);

                $activityAttchments = new ActivityAttachment;
                $activityAttchments->action_id = $actionId;
                $activityAttchments->type = "storage_file";
                $activityAttchments->id = $attachmentLinkId;
                $activityAttchments->save();

            //}
            
        }
        
    }

    
}
