<?php

namespace Modules\User\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\Response;
use App\Http\Controllers\CoreController;
use Modules\User\Entities\User;
use Modules\User\Entities\Blog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth; 
use Validator;
//use App\Events\UserRegisterEvent;
use App\Http\Traits\UploadImageTrait;

class BlogController extends CoreController
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
    Get blog listing
    ***/
    public function getBlogListing(Request $request)
    {
        try
        {
            $loggedInUser = $this->user;
            
            if(!empty($request->visitor_profile_id))
            {
                $blogLists = Blog::with('user:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','user.avatar_id','attachment')->where('user_id', $$request->visitor_profile_id)->get();
            }
            else
            {
                $blogLists = Blog::with('user:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','user.avatar_id','attachment')->where('user_id', $loggedInUser->user_id)->get();    
            }
            
            if(count($blogLists) > 0)
            {
                foreach($blogLists as $key => $blogList)
                {
                    if(($blogLists[$key]->status == '0' || $blogLists[$key]->status == '1') &&  $blogLists[$key]->user_id == $loggedInUser->user_id)
                    {
                        $blogLists[$key]->title = $this->translate('messages.'.$blogList->title, $blogList->title);
                        $blogLists[$key]->description = $this->translate('messages.'.$blogList->description, $blogList->description);
                    }
                    elseif($blogLists[$key]->status == '1')
                    {
                        $blogLists[$key]->title = $this->translate('messages.'.$blogList->title, $blogList->title);
                        $blogLists[$key]->description = $this->translate('messages.'.$blogList->description, $blogList->description);
                    }
                    
                }
                return response()->json(['success' => $this->successStatus,
                                         'data' => $blogLists,
                                        ], $this->successStatus);
            }
            else
            {
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'."No blogs found","No blogs found")]], $this->exceptionStatus);       
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /***
    Create blog
    ***/
    public function createBlog(Request $request)
    {
        try
        {
            $loggedInUser = $this->user;
            $validator = Validator::make($request->all(), [ 
                'title' => 'required', 
                'date' => 'required',
                'time' => 'required',  
                'description' => 'required', 
                'status' => 'required', 
                'image_id' => 'required', 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $createBLog = new Blog;
            $createBLog->user_id = $loggedInUser->user_id;
            $createBLog->title = $request->title;
            $createBLog->date = $request->date;
            $createBLog->time = $request->time;
            $createBLog->description = $request->description;
            $createBLog->status = $request->status;
            $createBLog->image_id = $this->uploadImage($request->file('image_id'));
            $createBLog->save();

            return response()->json(['success' => $this->successStatus,
                                    'message' => $this->translate('messages.'."Blog created successfuly!","Blog created successfuly!")
                                    ], $this->successStatus);
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }


    /***
    Update blog
    ***/
    public function updateBlog(Request $request)
    {
        try
        {
            $loggedInUser = $this->user;
            $validator = Validator::make($request->all(), [ 
                'blog_id' => 'required', 
                'title' => 'required', 
                'date' => 'required',
                'time' => 'required',  
                'description' => 'required', 
                'status' => 'required', 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $createBLog = Blog::where('blog_id', $request->blog_id)->first();
            $createBLog->title = $request->title;
            $createBLog->date = $request->date;
            $createBLog->time = $request->time;
            $createBLog->description = $request->description;
            $createBLog->status = $request->status;
            if(!empty($request->image_id))
            {
                $this->deleteAttachment($createBLog->image_id);
                $createBLog->image_id = $this->uploadImage($request->file('image_id'));
            }
                
            $createBLog->save();

            return response()->json(['success' => $this->successStatus,
                                    'message' => $this->translate('messages.'."Blog updated successfuly!","Blog updated successfuly!")
                                    ], $this->successStatus);
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /***
    Delete blog
    ***/
    public function deleteBlog(Request $request)
    {
        try
        {
            $loggedInUser = $this->user;
            $validator = Validator::make($request->all(), [ 
                'blog_id' => 'required', 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $blog = Blog::where('blog_id', $request->blog_id)->where('user_id', $loggedInUser->user_id)->first();
            if(!empty($blog))
            {
                $this->deleteAttachment($blog->image_id);
                $isBlogDeleted = Blog::where('blog_id', $request->blog_id)->delete();
                if($isBlogDeleted == 1)
                {
                    return response()->json(['success' => $this->successStatus,
                                    'message' => $this->translate('messages.'."Blog deleted successfuly!","Blog deleted successfuly!")
                                    ], $this->successStatus);
                }
                else
                {
                    return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'."Something went wrong","Something went wrong")]], $this->exceptionStatus);    
                }
            }
            else
            {
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'."Invalid blog","Invalid blog")]], $this->exceptionStatus);    
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    
    
}
