<?php

namespace Modules\Activity\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CoreController;
use App\Http\Traits\UploadImageTrait;
use Modules\Activity\Entities\ActivityAction;
use Modules\Activity\Entities\CoreComment;
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
            $requestFields = $request->params;
            //$requestedFields = json_decode($requestFields, true);
            $requestedFields = $requestFields;
            
            $rules = $this->validateData($requestedFields, 1);

            $validator = Validator::make($requestedFields, $rules);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $actionType = $this->checkActionType($requestedFields["action_type"], 1);
            if($actionType[1] > 0)
            {
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $actionType[0]]], $this->exceptionStatus);
            }
            else
            {
                $activityActionType = ActivityActionType::where("type", $requestedFields["action_type"])->first();
                $activityAction = new ActivityAction;
                $activityAction->type = $activityActionType->activity_action_type_id;
                $activityAction->subject_type = "user";
                $activityAction->subject_id = $user->user_id;
                $activityAction->object_type = "user";
                $activityAction->object_id = $user->user_id;
                $activityAction->body = $requestedFields["body"];
                $activityAction->privacy = $requestedFields["privacy"];
                $activityAction->attachment_count = count($requestedFields["attachments"]);
                $activityAction->save();
            }

            if(count($requestedFields["attachments"]) > 0)
            {
                $this->uploadAttchments($requestedFields["attachments"], $activityAction->activity_action_id);
            }
            if($activityAction)
            {
                return response()->json(['success' => $this->successStatus,
                                         'message' => $this->translate('messages.'."Post added successfuly!","Post added successfuly!"),
                                        ], $this->successStatus);
            }
            else
            {
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => 'Something went wrong']], $this->exceptionStatus);
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
            $requestFields = $request->params;
            //$requestedFields = json_decode($requestFields, true);
            $requestedFields = $requestFields;
            
            $rules = $this->validateData($requestedFields, 2);

            $validator = Validator::make($requestedFields, $rules);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $actionType = $this->checkActionType($requestedFields["action_type"], 2);
            if($actionType[1] > 0)
            {
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $actionType[0]]], $this->exceptionStatus);
            }
            else
            {
                $activityActionType = ActivityActionType::where("type", $requestedFields["action_type"])->first();
                $activityAction = ActivityAction::where('activity_action_id', $requestedFields["post_id"])->where('subject_id', $user->user_id)->first();
                if(!empty($activityAction))
                {
                    $activityAction->type = $activityActionType->activity_action_type_id;
                    $activityAction->body = $requestedFields["body"];
                    $activityAction->privacy = $requestedFields["privacy"];
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
    public function likePost(Request $request)
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
                            $message = "You liked this post";
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
                $activityComment = new CoreComment;
                $activityComment->resource_type = "user";
                $activityComment->resource_id = $request->post_id;
                $activityComment->poster_type = "user";
                $activityComment->poster_id = $user->user_id;
                $activityComment->body = $user->user_id;
                $activityComment->save();

                $activityPost->comment_count = $activityPost->comment_count + 1;
                $activityPost->save();

                $message = "Your comment has been posted successfully";
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

            $activityPostComment = CoreComment::where('comment_id', $request->comment_id)->where('poster_id', $user->user_id)->first();
            if(!empty($activityPostComment))
            {
                $activityPostCommentDelete = CoreComment::where('comment_id', $request->comment_id)->where('poster_id', $user->user_id)->delete();
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
            if($attchments[$key]->hasFile($attchments[$key]))
            {
                $attachmentLinkId = $this->postAttchment($attachment);

                $activityAttchments = new ActivityAttachment;
                $activityAttchments->action_id = $actionId;
                $activityAttchments->type = "storage_file";
                $activityAttchments->id = $attachmentLinkId;
                $activityAttchments->save();

            }
            
        }
        
    }

    
}
