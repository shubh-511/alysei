<?php

namespace Modules\Activity\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Traits\UploadImageTrait;
use Modules\Activity\Entities\ActivityAction;
use Modules\Activity\Entities\ActivityActionType;
use Modules\Activity\Entities\ActivityAttachment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth; 
use Validator;
//use App\Events\UserRegisterEvent;

class ActivityController extends Controller
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

    /***
    Add Post
    ***/
    public function addPost(Request $request)
    {
        try
        {
            $user = $this->user;
            $requestFields = $request->params;
            //$requestedFields = json_decode($requestFields, true);
            $requestedFields = $requestFields;
            
            $rules = $this->validateData($requestedFields);

            $validator = Validator::make($requestedFields, $rules);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $actionType = $this->checkActionType($requestedFields["action_type"]);
            if($actionType[1] > 0)
            {
                return response()->json(['success'=>false,'errors' =>['exception' => $actionType[0]]], $this->exceptionStatus);
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
                return response()->json(['success'=>false,'errors' =>['exception' => 'Something went wrong']], $this->exceptionStatus);
            }
           
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }


    /*
     * Validate Data
     * @Params $requestedfields
     */

    public function validateData($requestedFields)
    {
        $rules = [];
        foreach ($requestedFields as $key => $field) {
            
            if($key == 'action_type'){
                $rules[$key] = 'required|max:190';
            }
            elseif($key == 'privacy'){
                $rules[$key] = 'required|max:190';
            }
        }

        return $rules;

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
            $attachmentUrl = $this->postAttchment($attachment);

            $activityAttchments = new ActivityAttachment;
            $activityAttchments->action_id = $actionId;
            $activityAttchments->type = "post_link";
            $activityAttchments->attachment_url = $attachmentUrl;
            $activityAttchments->save();
        }
        
    }

    
}
