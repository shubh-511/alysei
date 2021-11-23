<?php

namespace Modules\Activity\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CoreController;
use App\Http\Traits\UploadImageTrait;
use Modules\Activity\Entities\ActivityAction;
use Modules\Activity\Entities\CoreComment;
use Modules\User\Entities\DeviceToken; 
use Modules\User\Entities\User;
use Modules\User\Entities\Blog;
use Modules\User\Entities\Trip;
use Modules\User\Entities\Event; 
use Modules\User\Entities\UserSelectedHub;
use Modules\User\Entities\Hub;
use Modules\Recipe\Entities\PreferenceMapUser;
use App\Attachment;
use App\Notification;
use App\Http\Traits\NotificationTrait;
use Modules\Activity\Entities\Connection;
use Modules\Activity\Entities\Follower;
use Modules\Activity\Entities\DiscoverAlysei;
use Modules\Activity\Entities\ActivityLike;
use Modules\Activity\Entities\ActivityActionType;
use Modules\Activity\Entities\ActivityAttachment;
use Modules\Activity\Entities\ActivityAttachmentLink;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Auth; 
use Validator;
//use App\Events\UserRegisterEvent;

class ActivityController extends CoreController
{
    use UploadImageTrait;
    use NotificationTrait;
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
     * Get all hubs
     * @Params $request
     */
    public function getAllHubs()
    {
        $user = $this->user;
        $hubs = DB::table('hubs')->select('id','title')
                        ->where('status', '1')
                        ->get();
        if(count($hubs) > 0)
        {
            return response()->json(['success' => $this->successStatus,
                                     'hubs' => $hubs
                                    ], $this->successStatus);
        }
        else
        {
            $message = 'Nothing found';
            return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
        }
    }

    /*
     * Get Specialization
     * @Params $request
     */
    public function getSpecialization()
    {
        $user = $this->user;
        $specializations = DB::table('user_field_options')->select('user_field_option_id','option')
                        ->where('user_field_id', 11)
                        ->get();
        if(count($specializations) > 0)
        {
            return response()->json(['success' => $this->successStatus,
                                     'data' => $specializations
                                    ], $this->successStatus);
        }
        else
        {
            $message = 'Nothing found';
            return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
        }
    }

    /*
     * Get Restaurant types
     * @Params $request
     */
    public function getRestaurantTypes()
    {
        $user = $this->user;
        $restaurants = DB::table('user_field_options')->select('user_field_option_id','option')
                        ->where('user_field_id', 10)
                        ->get();
        if(count($restaurants) > 0)
        {
            return response()->json(['success' => $this->successStatus,
                                     'data' => $restaurants
                                    ], $this->successStatus);
        }
        else
        {
            $message = 'Nothing found';
            return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
        }
    }


    /*
     * Get Discover Alysei
     * @Params $request
     */
    public function getDiscoverAlysei($user='')
    {
        $user = $this->user;
        
        $discoverAlysei = DiscoverAlysei::select('discover_alysei_id','title','name','description','image_id','status')->with('attachment')->where('status', '1')->get();            
        return $discoverAlysei; 
    }

    /*
     * Get Discover Alysei listing
     * @Params $request
     */
    public function getDetailListingOfStories($user='')
    {
        $usersArray =[];
        $getConnections = Connection::where('is_approved', '1')
        ->Where(function ($query) use ($user) {
            $query->where('resource_id', $user->user_id)
              ->orWhere('user_id', $user->user_id);
        })
        ->get();

        if(count($getConnections) > 0)
        {
            foreach($getConnections as $connections)
            {
                if($connections->resource_id == $user->user_id)
                {
                    array_push($usersArray, $connections->user_id);
                }
                else
                {
                    array_push($usersArray, $connections->resource_id);   
                }
            }
        }

        $getFollowings = Follower::with('user:user_id,name,email,company_name,first_name,last_name,avatar_id','user.avatar_id')->where('user_id', $user->user_id)->orderBy('id', 'DESC')->get();
        if(count($getFollowings))
        {
            foreach($getFollowings as $getFollowing)
            {
                array_push($usersArray, $getFollowing->follow_user_id);
            }    
        }
        
        return $usersArray;
    }

    /*
     * Get Bubbles Shuffling
     * @Params $request
     */
    public function getBubblesShuffling($user='')
    {
        $user = $this->user;
        $userIds =  $this->getDetailListingOfStories($user);

        $events = Event::whereIn('user_id', $userIds)->where('status', '1')->orderBy('created_at','DESC')->first();
        $trips = Trip::whereIn('user_id', $userIds)->where('status', '1')->orderBy('created_at','DESC')->first();
        $blogs = Blog::whereIn('user_id', $userIds)->where('status', '1')->orderBy('created_at','DESC')->first();
        $users = User::where('role_id', 9)->whereIn('user_id', $userIds)->orderBy('created_at','DESC')->first();

        if(($events['created_at'] > $trips['created_at']) && ($events['created_at'] > $blogs['created_at']) && ($events['created_at'] > $users['created_at']))
        {
            $top = 'events';
        }
        elseif(($trips['created_at'] > $events['created_at']) && ($trips['created_at'] > $blogs['created_at']) && ($trips['created_at'] > $users['created_at']))
        {   
            $top = 'trips';
        }
        elseif(($blogs['created_at'] > $events['created_at']) && ($blogs['created_at'] > $trips['created_at']) && ($blogs['created_at'] > $users['created_at']))
        {
            $top = 'blogs';
        }
        elseif(($users['created_at'] > $events['created_at']) && ($users['created_at'] > $trips['created_at']) && ($users['created_at'] > $blogs['created_at']))
        {
            $top = 'users';
        }
        else
        {
            $top = '';
        }

        $discoverAlysei = DiscoverAlysei::with('attachment')->where('status', '1')->get()->toArray();
        foreach($discoverAlysei as $key => $stories)
        {
            if($stories['name'] == $top)
            {
                $new_value = $discoverAlysei[$key];
                unset($discoverAlysei[$key]);
                array_unshift($discoverAlysei, $new_value);    
            }
        }        
        
        return $discoverAlysei;
    }

    /*
     * Get Discover Alysei(Circle) detail
     * @Params $request
     */
    public function getCircleDetail(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'type' => 'required'
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }
            $usersArray = [];
            $users =  $this->getDetailListingOfStories($user);
            $message = 'Nothing found!';
            switch($request->type)
            {
                case ('events'):
                    /*if(count($users) > 0)
                    {*/
                        $data = Event::with('user:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','user.avatar_id','attachment')->where('status', '1')->paginate(10);
                    /*}
                    else
                    {
                        return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                    }*/
                    
                break;

                case ('trips'):
                    /*if(count($users) > 0)
                    {*/
                        $data = Trip::with('user:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','user.avatar_id','attachment','intensity','country:id,name','region:id,name')->where('status', '1')->paginate(10);
                        foreach($data as $key => $datas)
                        {
                            $specialityTrip = DB::table('user_field_options')
                                    ->where('user_field_option_id', $datas->adventure_type)
                                    ->where('user_field_id', 14)
                                    ->first();
                                    
                            if(!empty($specialityTrip))  
                            {
                                $data[$key]->adventure = ['adventure_type_id' => $specialityTrip->user_field_option_id, 'adventure_type' => $specialityTrip->option];    
                            }
                            else
                            {
                                $data->adventure = null;   
                            }
                        }

                    /*}
                    else
                    {
                        return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                    }*/
                    
                break;

                case ('blogs'):
                    /*if(count($users) > 0)
                    {*/
                        $data = Blog::with('user:user_id,name,email,first_name,last_name,company_name,restaurant_name,role_id,avatar_id','user.avatar_id','attachment')->where('status', '1')->paginate(10);
                    /*}
                    else
                    {
                        return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                    }*/
                    
                break;

                case ('restaurants'):
                    /*if(count($users) > 0)
                    {*/
                        $data = User::select('user_id','name','email','restaurant_name','role_id','address','lattitude','longitude','avatar_id')
                            ->with('avatar_id')->where('role_id', 9)->paginate(10);
                    /*}
                    else
                    {
                        return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                    }*/                    
                break;

                default:
                $message = 'Something went wrong!';
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }

            return response()->json(['success' => $this->successStatus,
                                     'data' => $data
                                    ], $this->successStatus);           
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }        
    }

    /*
     * filter stories
     * @Params $request
     */
    public function filterDiscoverStories(Request $request)
    {
        $user = $this->user;
        $condition = '';
        
        $validator = Validator::make($request->all(), [ 
            'type' => 'required'
        ]);

        if ($validator->fails()) { 
            return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
        }
        $users =  $this->getDetailListingOfStories($user);
        $message = 'Nothing found!';
        $usersArray = [];
        switch($request->type)
        {
            case ('events'):
                /*if(count($users) > 0)
                {*/
                    if(!empty($request->date))
                    {
                        if($condition != '')
                            $condition .=" and events.date = ".$request->date;
                        else
                            $condition .="events.date = ".$request->date;
                    }
                    if(!empty($request->event_type))
                    {
                        if($condition != '')
                            $condition .=" and events.event_type = '".$request->event_type."'";
                        else
                            $condition .="events.event_type = '".$request->event_type."'";
                    }
                    if(!empty($request->registration_type))
                    {
                        if($condition != '')
                            $condition .=" and events.registration_type = '".$request->registration_type."'";
                        else
                            $condition .="events.registration_type = '".$request->registration_type."'";
                    }
                    if(!empty($request->restaurant_type))
                    {
                        $values = DB::table('user_field_values')
                                            ->where('value', $request->restaurant_type)
                                            ->where('user_field_id', 10)
                                            ->get();
                        $userIds = $values->pluck('user_id')->toArray();
                        foreach($userIds as $userId)
                        {
                            array_push($usersArray, $userId);
                        }
                    }

                    if(count($usersArray) > 0)
                    {
                        $join = join(",", $usersArray);
                        if($condition != '')
                        $condition .=" and events.user_id in(".$join.")";
                        else
                        $condition .="events.user_id in(".$join.")";
                    }

                    if($condition != '')
                    {
                        $data = Event::with('user:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','user.avatar_id','attachment')->whereRaw('('.$condition.')')->where('status', '1')->paginate(10);
                    }
                    else
                    {
                        $data = Event::with('user:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','user.avatar_id','attachment')->where('status', '1')->paginate(10);    
                    }
                /*}
                else
                {
                    return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                }*/
                
            break;

            case ('trips'):
                /*if(count($users) > 0)
                {*/
                    if(!empty($request->region))
                    {
                        if($condition != '')
                            $condition .=" and trips.region = ".$request->region;
                        else
                            $condition .="trips.region = ".$request->region;
                    }
                    if(!empty($request->adventure_type))
                    {
                        if($condition != '')
                            $condition .=" and trips.adventure_type = ".$request->adventure_type;
                        else
                            $condition .="trips.adventure_type = ".$request->adventure_type;
                    }
                    if(!empty($request->duration))
                    {
                        /*if($condition != '')
                            $condition .=" and trips.duration = ".$request->duration;
                        else
                            $condition .="trips.duration = ".$request->duration;*/
                    }
                    if(!empty($request->intensity))
                    {
                        if($condition != '')
                            $condition .=" and trips.intensity = ".$request->intensity;
                        else
                            $condition .="trips.intensity = ".$request->intensity;
                    }
                    if(!empty($request->price))
                    {
                        if($condition != '')
                            $condition .=" and trips.price = ".$request->price;
                        else
                            $condition .="trips.price = ".$request->price;
                    }

                    if($condition != '')
                    {
                        $data = Trip::with('user:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','user.avatar_id','attachment','intensity','country:id,name','region:id,name')->whereRaw('('.$condition.')')->where('status', '1')->paginate(10);
                    }
                    else
                    {
                        $data = Trip::with('user:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','user.avatar_id','attachment','intensity','country:id,name','region:id,name')->where('status', '1')->paginate(10);
                    }

                    foreach($data as $key => $datas)
                    {
                        $specialityTrip = DB::table('user_field_options')
                                ->where('user_field_option_id', $datas->adventure_type)
                                ->where('user_field_id', 14)
                                ->first();
                                
                        if(!empty($specialityTrip))  
                        {
                            $data[$key]->adventure = ['adventure_type_id' => $specialityTrip->user_field_option_id, 'adventure_type' => $specialityTrip->option];    
                        }
                        else
                        {
                            $data->adventure = null;   
                        }
                    }
                /*}
                else
                {
                    return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                }*/
                
            break;

            case ('blogs'):
                /*if(count($users) > 0)
                {*/
                    if(!empty($request->title))
                    {
                        if($condition != '')
                            $condition .=" and blogs.title LIKE '%".$request->title."%'";
                        else
                            $condition .="blogs.title LIKE '%".$request->title."%'";
                    }

                    if(!empty($request->specialization))
                    {
                        $speciality = explode(",", $request->specialization);
                        $values = DB::table('user_field_values')
                                            ->whereIn('value', $speciality)
                                            ->where('user_field_id', 11)
                                            ->get();
                        $userIds = $values->pluck('user_id')->toArray();
                        foreach($userIds as $userId)
                        {
                            array_push($usersArray, $userId);
                        }
                    }

                    if(count($usersArray) > 0)
                    {
                        $join = join(",", $usersArray);
                        if($condition != '')
                        $condition .=" and blogs.user_id in(".$join.")";
                        else
                        $condition .="blogs.user_id in(".$join.")";
                    }

                    if($condition != '')
                    {
                        $data = Blog::with('user:user_id,name,email,first_name,last_name,company_name,restaurant_name,role_id,avatar_id','user.avatar_id','attachment')->whereRaw('('.$condition.')')->where('status', '1')->paginate(10);    
                    }
                    else
                    {
                        $data = Blog::with('user:user_id,name,email,first_name,last_name,company_name,restaurant_name,role_id,avatar_id','user.avatar_id','attachment')->where('status', '1')->paginate(10);
                    }
                /*}
                else
                {
                    return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                }*/
                
            break;

            case ('restaurants'):
                /*if(count($users) > 0)
                {*/
                    if(!empty($request->restaurant_type))
                    {
                        $values = DB::table('user_field_values')
                                            ->where('value', $request->restaurant_type)
                                            ->where('user_field_id', 10)
                                            ->get();
                        $userIds = $values->pluck('user_id')->toArray();
                        foreach($userIds as $userId)
                        {
                            array_push($usersArray, $userId);
                        }
                    }

                    if(count($usersArray) > 0)
                    {
                        $join = join(",", $usersArray);
                        if($condition != '')
                        $condition .=" and users.user_id in(".$join.")";
                        else
                        $condition .="users.user_id in(".$join.")";
                    }
                    if($condition != '')
                    {
                        $data = User::select('user_id','name','email','restaurant_name','role_id','address','lattitude','longitude','avatar_id')->with('avatar_id')->whereRaw('('.$condition.')')->where('role_id', 9)->paginate(10);
                    }
                    else
                    {
                        $data = User::select('user_id','name','email','restaurant_name','role_id','address','lattitude','longitude','avatar_id')->with('avatar_id')->where('role_id', 9)->paginate(10);
                    }
                /*}
                else
                {
                    return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                }*/                    
            break;

            default:
            $message = 'Something went wrong!';
            return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
        }

        if(count($data) > 0)
        {
            return response()->json(['success' => $this->successStatus,
                                     'data' => $data
                                    ], $this->successStatus);
        }
        else
        {
            $message = 'Nothing found!';
            return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
        }
    }

    /*
     * Get Stories detail
     * @Params $request
     */
    public function getStoriesDetails(Request $request)
    {
        $user = $this->user;
        $condition = '';
        
        $validator = Validator::make($request->all(), [ 
            'type' => 'required',
            'id'   => 'required'
        ]);

        if ($validator->fails()) { 
            return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
        }
        
        if($request->type == 'events')
        {
            $details = Event::with('user:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','user.avatar_id','attachment')->where('event_id', $request->id)->where('status', '1')->first();
        }
        elseif($request->type == 'trips')
        {
            $details = Trip::with('user:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','user.avatar_id','attachment','intensity','country:id,name','region:id,name')->where('trip_id', $request->id)->where('status', '1')->first();
        }
        elseif($request->type == 'blogs')
        {
            $details = Blog::with('user:user_id,name,email,first_name,last_name,company_name,restaurant_name,role_id,avatar_id','user.avatar_id','attachment')->where('blog_id', $request->id)->where('status', '1')->first();
        }
        else
        {
            $message = 'Something went wrong';
            return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
        }

        if(!empty($details))
        {
            return response()->json(['success' => $this->successStatus,
                                     'data' => $details
                                    ], $this->successStatus);
        }
        else
        {
            $message = 'Invalid id provided!';
            return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
        }
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
                $activityAction->height = $request->height;
                $activityAction->width = $request->width;
                if(!empty($request->attachments))
                {
                    $activityAction->attachment_count = count($request->attachments);
                }
                else
                {
                    $activityAction->attachment_count = 0;   
                }
                
                $activityAction->save();
                $slug = rand().''.$activityAction->activity_action_id;
                ActivityAction::where('activity_action_id', $activityAction->activity_action_id)->update(['slug' => $slug]);
            }

            if(!empty($request->attachments) && count($request->attachments) > 0)
            {
                $this->uploadAttchments($request->attachments, $activityAction->activity_action_id);
            }
            if($activityAction)
            {
                return response()->json(['success' => $this->successStatus,
                                         'message' => $this->translate('messages.'."Post added successfuly!","Post added successfuly!"),
                                         'post_id' => $activityAction->activity_action_id,
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

            $actionType = $this->checkActionType($request->action_type, 5);
            if($actionType[1] > 0)
            {
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $actionType[0]]], $this->exceptionStatus);
            }
            else
            {
                $activityPost = ActivityAction::where('activity_action_id', $request->shared_post_id)->first();
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
                $getUserDetail = User::with('avatar_id')->where('user_id', $user->user_id)->first();

                if($user->role_id == 7 || $user->role_id == 10)
                {
                    $name = ucwords(strtolower($user->first_name)) . ' ' . ucwords(strtolower($user->last_name));
                }
                elseif($user->role_id == 9)
                {
                    $name = $user->restaurant_name;
                }
                else
                {
                    $name = $user->company_name;
                }

                $title1 = $name." shared your post";
                $title = "shared your post";

                $saveNotification = new Notification;
                $saveNotification->from = $user->user_id;
                $saveNotification->to = $activityPost->subject_id;
                $saveNotification->notification_type = 2; //post share
                $saveNotification->title = $this->translate('messages.'.$title,$title);
                $saveNotification->redirect_to = 'post_screen';
                $saveNotification->redirect_to_id = $request->shared_post_id;

                $saveNotification->sender_id = $user->user_id;
                $saveNotification->sender_name = $name;
                $saveNotification->sender_image = null;
                $saveNotification->post_id =$request->shared_post_id;
                $saveNotification->connection_id = null;
                $saveNotification->sender_role = $user->role_id;
                $saveNotification->comment_id = null;
                $saveNotification->reply = null;
                $saveNotification->likeUnlike = null;

                $saveNotification->save();

                $tokens = DeviceToken::where('user_id', $activityPost->subject_id)->get();
                if(count($tokens) > 0)
                {
                    $collectedTokenArray = $tokens->pluck('device_token');
                    $this->sendNotification($collectedTokenArray, $title1, $saveNotification->redirect_to, $saveNotification->redirect_to_id, $saveNotification->notification_type, $user->user_id, $name, null, /*(!empty($getUserDetail->avatar_id->attachment_url) ?? $getUserDetail->avatar_id->attachment_url : null),*/ $request->shared_post_id, null, $user->role_id, null,null,null);

                    $this->sendNotificationToIOS($collectedTokenArray, $title1, $saveNotification->redirect_to, $saveNotification->redirect_to_id, $saveNotification->notification_type, $user->user_id, $name, null, /*(!empty($getUserDetail->avatar_id->attachment_url) ?? $getUserDetail->avatar_id->attachment_url : null),*/ $request->shared_post_id, null, $user->role_id, null,null,null);
                }

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
                $activityPosts = ActivityAction::select('activity_action_id','type','subject_id','body','shared_post_id','attachment_count','comment_count','like_count','privacy','created_at','height','width')
                ->with('attachments.attachment_link')
                ->with('subject_id:user_id,name,email,company_name,first_name,last_name,restaurant_name,role_id,avatar_id','subject_id.avatar_id')
                //->whereIn('subject_id', $userIds)

                ->Where(function ($query) use ($user, $userIds) {
                $query->where('privacy', 'public')
                  ->orWhere('privacy', 'followers')
                  ->whereIn('subject_id', $userIds);
                 })
                //->inRandomOrder()
                ->orderBy('created_at', 'DESC')
                ->paginate(10);
                
                
            }
            else
            {
                $activityPosts = ActivityAction::select('activity_action_id','type','subject_id','body','shared_post_id','attachment_count','comment_count','like_count','privacy','created_at','height','width')
                ->with('attachments.attachment_link')
                ->with('subject_id:user_id,name,email,company_name,first_name,last_name,restaurant_name,role_id,avatar_id','subject_id.avatar_id')

                ->Where(function ($query) use ($user) {
                $query->where('privacy', 'public')
                  ->orWhere('subject_id', $user->user_id);
                 })
                /*->where('privacy', 'public')
                ->orWhere('subject_id', $user->user_id)*/
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
                    $activityShared = ActivityAction::select('activity_action_id','type','subject_id','body','shared_post_id','attachment_count','comment_count','like_count','privacy','created_at','height','width')->with('attachments.attachment_link')->with('subject_id:user_id,name,email,company_name,first_name,last_name,restaurant_name,role_id,avatar_id','subject_id.avatar_id')->where('activity_action_id', $activityPost->shared_post_id)->first();
                    if(!empty($activityShared))
                    {
                        $activityPosts[$key]->shared_post = $activityShared;
                    }
                    else
                    {
                        $activityPosts[$key]->shared_post = null;   
                    }

                    $activityPosts[$key]->posted_at = $activityPost->created_at->diffForHumans(); 
                    $followerCount = Follower::where('follow_user_id', $activityPost->subject_id)->count();  
                    $activityPosts[$key]->follower_count = $followerCount; 
                }

                $getPreferences = PreferenceMapUser::where('user_id', $user->user_id)->count();
                if($getPreferences > 0)
                {
                    $preferenceStatus = 1;
                }
                else
                {
                    $preferenceStatus = 0;
                }

                $getDiscoverAlysei = $this->getBubblesShuffling($user);

                return response()->json(['success' => $this->successStatus,
                                         'having_preferences' => $preferenceStatus,
                                         'discover_alysei' => $getDiscoverAlysei,
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

            $activityPost = ActivityAction::select('activity_action_id','type','subject_id','body','shared_post_id','attachment_count','comment_count','like_count','privacy','created_at','height','width')
                ->with('attachments.attachment_link')
                ->with('subject_id:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','subject_id.avatar_id')->where('activity_action_id', $request->post_id)->first();
            if(!empty($activityPost))
            {
                $isLikedActivityPost = ActivityLike::where('resource_id', $activityPost->activity_action_id)->where('poster_id', $user->user_id)->first();
                $activityPost->posted_at = $activityPost->created_at->diffForHumans();
                if(!empty($isLikedActivityPost))
                {
                    $activityPost->like_flag = 1;
                }
                else
                {
                    $activityPost->like_flag = 0;
                }
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

            if(!empty($request->visitor_profile_id))
            {
                if($havingAttachment == 1)
                {
                    if(!empty($request->per_page))
                    {
                        $activityPost = ActivityAction::select('activity_action_id','type','subject_id','body','shared_post_id','attachment_count','comment_count','like_count','privacy','created_at','height','width')
                        ->with('attachments.attachment_link')
                        ->with('subject_id:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','subject_id.avatar_id')
                        ->where('subject_id', $request->visitor_profile_id)
                        ->where('attachment_count','>', 0)
                        ->orderBy('activity_action_id','DESC')->paginate($request->per_page);
                    }
                    else
                    {
                        $activityPost = ActivityAction::select('activity_action_id','type','subject_id','body','shared_post_id','attachment_count','comment_count','like_count','privacy','created_at','height','width')
                        ->with('attachments.attachment_link')
                        ->with('subject_id:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','subject_id.avatar_id')
                        ->where('subject_id', $request->visitor_profile_id)
                        ->where('attachment_count','>', 0)
                        ->orderBy('activity_action_id','DESC')->paginate(15);
                    }
                    
                }
                elseif($havingAttachment == 0)
                {
                    if(!empty($request->per_page))
                    {
                        $activityPost = ActivityAction::select('activity_action_id','type','subject_id','body','shared_post_id','attachment_count','comment_count','like_count','privacy','created_at','height','width')
                        ->with('attachments.attachment_link')
                        ->with('subject_id:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','subject_id.avatar_id')
                        ->where('subject_id', $request->visitor_profile_id)
                        ->orderBy('activity_action_id','DESC')
                        ->paginate($request->per_page);
                    }
                    else
                    {
                        $activityPost = ActivityAction::select('activity_action_id','type','subject_id','body','shared_post_id','attachment_count','comment_count','like_count','privacy','created_at','height','width')
                        ->with('attachments.attachment_link')
                        ->with('subject_id:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','subject_id.avatar_id')
                        ->where('subject_id', $request->visitor_profile_id)
                        ->orderBy('activity_action_id','DESC')
                        ->paginate(15);
                    }
                    
                }
                else
                {
                    $message = "Please select either 1 or 0";
                        return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                }
            }
            else
            {
                if($havingAttachment == 1)
                {
                    if(!empty($request->per_page))
                    {
                        $activityPost = ActivityAction::select('activity_action_id','type','subject_id','body','shared_post_id','attachment_count','comment_count','like_count','privacy','created_at','height','width')
                        ->with('attachments.attachment_link')
                        ->with('subject_id:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','subject_id.avatar_id')
                        ->where('subject_id', $loggedInUser->user_id)
                        ->where('attachment_count','>', 0)
                        ->orderBy('activity_action_id','DESC')->paginate($request->per_page);
                    }
                    else
                    {
                        $activityPost = ActivityAction::select('activity_action_id','type','subject_id','body','shared_post_id','attachment_count','comment_count','like_count','privacy','created_at','height','width')
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
                        $activityPost = ActivityAction::select('activity_action_id','type','subject_id','body','shared_post_id','attachment_count','comment_count','like_count','privacy','created_at','height','width')
                        ->with('attachments.attachment_link')
                        ->with('subject_id:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','subject_id.avatar_id')
                        ->where('subject_id', $loggedInUser->user_id)
                        ->orderBy('activity_action_id','DESC')
                        ->paginate($request->per_page);
                    }
                    else
                    {
                        $activityPost = ActivityAction::select('activity_action_id','type','subject_id','body','shared_post_id','attachment_count','comment_count','like_count','privacy','created_at','height','width')
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
            }

            
            
            if(!empty($activityPost))
            {
                foreach($activityPost as $key => $post)
                {
                    //is activity liked
                    $isLikedActivityPost = ActivityLike::where('resource_id', $post->activity_action_id)->where('poster_id', $loggedInUser->user_id)->first();
                    if(!empty($isLikedActivityPost))
                    {
                        $activityPost[$key]->like_flag = 1;
                    }
                    else
                    {
                        $activityPost[$key]->like_flag = 0;
                    }
                    $activityPost[$key]->posted_at = $post->created_at->diffForHumans();
                }
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
