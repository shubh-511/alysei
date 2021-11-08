<?php

namespace Modules\Marketplace\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Attachment;
use Modules\Marketplace\Entities\MarketplaceProduct;
use Modules\Marketplace\Entities\MarketplaceStore;
use Modules\Marketplace\Entities\MarketplaceStoreGallery;
use Modules\Marketplace\Entities\MarketplaceRating;
use Modules\Marketplace\Entities\MarketplaceProductGallery;
use Modules\Marketplace\Entities\MarketplaceFavourite;
use App\Http\Controllers\CoreController;
use Modules\User\Entities\User;
use App\Http\Traits\UploadImageTrait;
use Illuminate\Support\Facades\Auth; 
use Carbon\Carbon;
use Validator;
use DB;

class StoreController extends CoreController
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
    * Check if store previously created
    *
    */
    public function checkIfStoreCreated()
    {
        try
        {
            $user = $this->user;
            $myStore = MarketplaceStore::where('user_id', $user->user_id)->first();
            $productCount = MarketplaceProduct::where('user_id', $user->user_id)->count();
            
            if(!empty($myStore))
            {
                $checkIfStoreCreated = 1;
                $storeId = $myStore->marketplace_store_id;
                $logoId = Attachment::where('id', $myStore->logo_id)->first();
                $storeLogo = $logoId->attachment_url;

                $storeName = $myStore->name;
            }
            else
            {   
                $checkIfStoreCreated = 0;
                $storeId = 0;
                $storeName = null;
                $storeLogo = null;
            }
            
            return response()->json(['success' => $this->successStatus,
                                'is_store_created' => $checkIfStoreCreated,
                                'marketplace_store_id' => $storeId,
                                'product_count' => $productCount,
                                'name' => $storeName,
                                'logo_id' => $storeLogo
                            ], $this->successStatus);
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }
    }

    /*
     * Get Dashboard Screen
     * 
     */
    public function getDashboardScreen($filterType='')
    {
        try
        {
            $user = $this->user;
            $myStore = MarketplaceStore::where('user_id', $user->user_id)->first();
            if(!empty($myStore))
            {
                $logoId = Attachment::where('id', $myStore->logo_id)->first();
                $bannerId = Attachment::where('id', $myStore->banner_id)->first();
                $myStore->logo_id = $logoId->attachment_url;
                $myStore->banner_id = $bannerId->attachment_url;

                $getAnalytics = $this->getAnalyticsByFilter($filterType, $myStore);               
                
                
                return response()->json(['success' => $this->successStatus,
                                        'banner' => $myStore->banner_id,
                                        'logo' => $myStore->logo_id,
                                        'total_product' => $getAnalytics[0],
                                        'total_category' => count($getAnalytics[1]),
                                        'total_reviews' => $getAnalytics[2],
                                        'total_enquiries' => 0,
                                        //'data' => $myStore
                                    ],$this->successStatus); 
            }
            else
            {
                $message = "You have not setup your store yet!";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }

    }

    /**
    * get analytics by filter
    * 
    * */
    public function getAnalyticsByFilter($filterType, $myStore)
    {
        $user = $this->user;
        $returnedArray = [];
        $arrayValues = [];
        if($filterType == 1)
        {
            
            $productCount = MarketplaceProduct::where('user_id', $user->user_id)->whereYear('created_at', date('Y'))->count();
            $fieldValues = DB::table('user_field_values')
                        ->where('user_id', $user->user_id)
                        ->where('user_field_id', 2)
                        ->whereYear('created_at', date('Y'))
                        ->get();
            $totalReviewCount = MarketplaceRating::where('type', '1')->where('id', $myStore->store_id)->whereYear('created_at', date('Y'))->count();
        }
        elseif($filterType == 2)
        {
            
            $productCount = MarketplaceProduct::where('user_id', $user->user_id)->whereMonth('created_at', date('m'))->count();
            $fieldValues = DB::table('user_field_values')
                        ->where('user_id', $user->user_id)
                        ->where('user_field_id', 2)
                        ->whereMonth('created_at', date('m'))
                        ->get();
            $totalReviewCount = MarketplaceRating::where('type', '1')->where('id', $myStore->store_id)->whereMonth('created_at', date('m'))->count();
        }
        elseif($filterType == 3)
        {
            
            $productCount = MarketplaceProduct::where('user_id', $user->user_id)->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count();
            $fieldValues = DB::table('user_field_values')
                        ->where('user_id', $user->user_id)
                        ->where('user_field_id', 2)
                        ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                        ->get();
            $totalReviewCount = MarketplaceRating::where('type', '1')->where('id', $myStore->store_id)->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count();
        }
        elseif($filterType == 4)
        {
            
            $productCount = MarketplaceProduct::where('user_id', $user->user_id)->where('created_at','=', Carbon::yesterday())->count();
            $fieldValues = DB::table('user_field_values')
                        ->where('user_id', $user->user_id)
                        ->where('user_field_id', 2)
                        ->where('created_at','=', Carbon::yesterday())
                        ->get();
            $totalReviewCount = MarketplaceRating::where('type', '1')->where('id', $myStore->store_id)->where('created_at','=', Carbon::yesterday())->count();
        }
        elseif($filterType == 5)
        {
            
            $productCount = MarketplaceProduct::where('user_id', $user->user_id)->whereDate('created_at', Carbon::today())->count();
            $fieldValues = DB::table('user_field_values')
                        ->where('user_id', $user->user_id)
                        ->where('user_field_id', 2)
                        ->whereDate('created_at', Carbon::today())
                        ->get();
            $totalReviewCount = MarketplaceRating::where('type', '1')->where('id', $myStore->store_id)->whereDate('created_at', Carbon::today())->count();
        }
        else
        {
            $productCount = MarketplaceProduct::where('user_id', $user->user_id)->count();
            $fieldValues = DB::table('user_field_values')
                        ->where('user_id', $user->user_id)
                        ->where('user_field_id', 2)
                        ->get();
            $totalReviewCount = MarketplaceRating::where('type', '1')->where('id', $myStore->store_id)->count();
        }

        if(count($fieldValues) > 0)
        {
            foreach($fieldValues as $fieldValue)
            {
                $options = DB::table('user_field_options')
                        ->where('head', 0)->where('parent', 0)
                        ->where('user_field_option_id', $fieldValue->value)
                        ->first();
                
                if(!empty($options->option))
                $arrayValues[] = $options->option;
            }
        }

        $returnedArray = [$productCount, $arrayValues, $totalReviewCount];
        return $returnedArray;
    }


    /*
    * Get Store Prefilled values
    *
    */
    public function getPreFilledValues()
    {
        try
        {
            $user = $this->user;
            $userDetail = User::select('company_name','about','phone','email','website','address','lattitude','longitude','state')->with('state:id,name')->where('user_id', $user->user_id)->first();
            
            return response()->json(['success' => $this->successStatus,
                                'data' => $userDetail
                            ], $this->successStatus);
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }
    }
    
    /*
     * Save Store Details
     * @Params $request
     */
    public function saveStoreDetails(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [ 
                /*'name' => 'required|max:255', 
                'description' => 'required',
                'website' => 'required|max:255',*/
                //'store_region' => 'required',
                //'phone' =>  'required',
                //'location' => 'required|max:255',
                //'lattitude' => 'required|max:255',
                //'longitude' => 'required|max:255',
                'logo_id' => 'required',
                'banner_id' => 'required',
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $user = $this->user;

            $userData = User::where('user_id', $user->user_id)->first();
            $myStore = MarketplaceStore::where('user_id', $user->user_id)->first();
            if(empty($myStore))
            {
                $store = new MarketplaceStore;
                $store->user_id = $user->user_id;
                $store->package_id = $request->package_id;
                $store->logo_id = $this->uploadImage($request->file('logo_id'));
                $store->banner_id = $this->uploadImage($request->file('banner_id'));
                $store->save();

                $userDetail = MarketplaceStore::where('user_id', $user->user_id)->update(['description' => $userData->about, 'name' => $userData->company_name, 'website' => $userData->website, 'phone' => $userData->phone, 'location' => $userData->address, 'store_region' => $userData->state, 'lattitude' => $userData->lattitude, 'longitude' => $userData->longitude]);

                if(!empty($request->gallery_images) && count($request->gallery_images) > 0)
                {
                    foreach($request->gallery_images as $images)
                    {
                        $attachmentLinkId = $this->postGallery($images, $store->marketplace_store_id, 1);
                    }
                }

                $createdStore = MarketplaceStore::with('logo_id')->where('user_id', $user->user_id)->first();

                return response()->json(['success'=>$this->successStatus,'data' => $createdStore],$this->successStatus); 
            }
            else
            {
                $message = "Your store has already been setup";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }

    }

    /*
     * Get store details
     * @Params $request
     */
    public function getStoreDetails()
    {
        try
        {
            $user = $this->user;

            $myStore = MarketplaceStore::where('user_id', $user->user_id)->first();
            if(!empty($myStore))
            {
                $userDetail = User::select('company_name','about','phone','email','website','address','lattitude','longitude','state')->with('state:id,name')->where('user_id', $user->user_id)->first();
                
                $myStore->prefilled = $userDetail;
                $logoId = Attachment::where('id', $myStore->logo_id)->first();
                $bannerId = Attachment::where('id', $myStore->banner_id)->first();
                $myStore->logo_id = $logoId->attachment_url;
                $myStore->banner_id = $bannerId->attachment_url;


                $avgRating = MarketplaceRating::where('type', '1')->where('id', $myStore->marketplace_store_id)->avg('rating');
                $totalReviews = MarketplaceRating::where('type', '1')->where('id', $myStore->marketplace_store_id)->count();

                $oneStar = MarketplaceRating::where('type', '1')->where('id', $myStore->marketplace_store_id)->where('rating', 1)->count();
                $twoStar = MarketplaceRating::where('type', '1')->where('id', $myStore->marketplace_store_id)->where('rating', 2)->count();
                $threeStar = MarketplaceRating::where('type', '1')->where('id', $myStore->marketplace_store_id)->where('rating', 3)->count();
                $fourStar = MarketplaceRating::where('type', '1')->where('id', $myStore->marketplace_store_id)->where('rating', 4)->count();
                $fiveStar = MarketplaceRating::where('type', '1')->where('id', $myStore->marketplace_store_id)->where('rating', 5)->count();

                $isfavourite = MarketplaceFavourite::where('user_id', $user->user_id)->where('favourite_type', '1')->where('id', $myStore->marketplace_store_id)->first();

                $myStore->avg_rating = number_format((float)$avgRating, 1, '.', '');
                $myStore->total_reviews = $totalReviews;

                $myStore->total_one_star = $oneStar;
                $myStore->total_two_star = $twoStar;
                $myStore->total_three_star = $threeStar;
                $myStore->total_four_star = $fourStar;
                $myStore->total_five_star = $fiveStar;
                $myStore->is_favourite = (!empty($isfavourite)) ? 1 : 0;

                $arrayValues = array();
                $fieldValues = DB::table('user_field_values')
                            ->where('user_id', $user->user_id)
                            ->where('user_field_id', 2)
                            ->get();
                if(count($fieldValues) > 0)
                {
                    foreach($fieldValues as $fieldValue)
                    {
                        $options = DB::table('user_field_options')
                                ->where('head', 0)->where('parent', 0)
                                ->where('user_field_option_id', $fieldValue->value)
                                ->first();
                        
                        //$arrayValues[] = $options->option;
                        if(!empty($options->option))
                        $arrayValues[] = $options->option;
                    }
                }
                $myStore->total_category = count($arrayValues);

                $getLatestReview = MarketplaceRating::with('user:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','user.avatar_id')->where('type', '1')->where('id', $myStore->marketplace_store_id)->orderBy('marketplace_review_rating_id', 'DESC')->first();

                
                $getLatestReviewCounts = MarketplaceRating::where('type', '1')->where('id', $myStore->marketplace_store_id)->count();

                $myStore->latest_review = $getLatestReview;
                if(!empty($getLatestReview))
                $myStore->latest_review->review_count = $getLatestReviewCounts;


                $galleries = MarketplaceStoreGallery::where('marketplace_store_id', $myStore->marketplace_store_id)->get();
                (count($galleries) > 0) ? $myStore->store_gallery = $galleries : $myStore->store_gallery = [];

                $storeProducts = MarketplaceProduct::with('product_gallery')->where('marketplace_store_id', $myStore->marketplace_store_id)->get();
                
                return response()->json(['success'=>$this->successStatus,'data' =>$myStore, 'store_products' => $storeProducts],$this->successStatus); 
            }
            else
            {
                $message = "You have not setup your store yet!";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }

    }


    /*
     * Get store details
     * @Params $request
     */
    public function getSellerProfile($storeId='')
    {
        try
        {
            $user = $this->user;

            $myStore = MarketplaceStore::where('marketplace_store_id', $storeId)->first();
            if(!empty($myStore))
            {
                $userDetail = User::select('company_name','about','phone','email','website','address','lattitude','longitude','state','avatar_id')->with('avatar_id')->with('state:id,name')->where('user_id', $myStore->user_id)->first();
                
                $myStore->prefilled = $userDetail;
                $logoId = Attachment::where('id', $myStore->logo_id)->first();
                $bannerId = Attachment::where('id', $myStore->banner_id)->first();
                $myStore->logo_id = $logoId->attachment_url;
                $myStore->banner_id = $bannerId->attachment_url;


                $avgRating = MarketplaceRating::where('type', '1')->where('id', $myStore->marketplace_store_id)->avg('rating');
                $totalReviews = MarketplaceRating::where('type', '1')->where('id', $myStore->marketplace_store_id)->count();

                $oneStar = MarketplaceRating::where('type', '1')->where('id', $myStore->marketplace_store_id)->where('rating', 1)->count();
                $twoStar = MarketplaceRating::where('type', '1')->where('id', $myStore->marketplace_store_id)->where('rating', 2)->count();
                $threeStar = MarketplaceRating::where('type', '1')->where('id', $myStore->marketplace_store_id)->where('rating', 3)->count();
                $fourStar = MarketplaceRating::where('type', '1')->where('id', $myStore->marketplace_store_id)->where('rating', 4)->count();
                $fiveStar = MarketplaceRating::where('type', '1')->where('id', $myStore->marketplace_store_id)->where('rating', 5)->count();

                $isfavourite = MarketplaceFavourite::where('user_id', $user->user_id)->where('favourite_type', '1')->where('id', $myStore->marketplace_store_id)->first();

                $myStore->avg_rating = number_format((float)$avgRating, 1, '.', '');
                $myStore->total_reviews = $totalReviews;

                $myStore->total_one_star = $oneStar;
                $myStore->total_two_star = $twoStar;
                $myStore->total_three_star = $threeStar;
                $myStore->total_four_star = $fourStar;
                $myStore->total_five_star = $fiveStar;
                $myStore->is_favourite = (!empty($isfavourite)) ? 1 : 0;

                $arrayValues = array();
                $fieldValues = DB::table('user_field_values')
                            ->where('user_id', $user->user_id)
                            ->where('user_field_id', 2)
                            ->get();
                if(count($fieldValues) > 0)
                {
                    foreach($fieldValues as $fieldValue)
                    {
                        $options = DB::table('user_field_options')
                                ->where('head', 0)->where('parent', 0)
                                ->where('user_field_option_id', $fieldValue->value)
                                ->first();
                        
                        //$arrayValues[] = $options->option;
                        if(!empty($options->option))
                        $arrayValues[] = $options->option;
                    }
                }
                $myStore->total_category = count($arrayValues);

                $getLatestReview = MarketplaceRating::with('user:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','user.avatar_id')->where('type', '1')->where('id', $myStore->marketplace_store_id)->orderBy('marketplace_review_rating_id', 'DESC')->first();

                $myStore->latest_review = $getLatestReview;



                $galleries = MarketplaceStoreGallery::where('marketplace_store_id', $myStore->marketplace_store_id)->get();
                (count($galleries) > 0) ? $myStore->store_gallery = $galleries : $myStore->store_gallery = [];

                $storeProducts = MarketplaceProduct::with('product_gallery')->where('marketplace_store_id', $myStore->marketplace_store_id)->get();

                foreach($storeProducts as $key => $storeProduct)
                {
                    $avgRatingStoreProducts = MarketplaceRating::where('type', '1')->where('id', $storeProduct->marketplace_store_id)->avg('rating');
                    $totalReviewsStoreProducts = MarketplaceRating::where('type', '1')->where('id', $storeProduct->marketplace_store_id)->count();

                    $storeProducts[$key]->avg_rating = number_format((float)$avgRatingStoreProducts, 1, '.', '');
                    $storeProducts[$key]->total_reviews = $totalReviewsStoreProducts;
                }
                
                return response()->json(['success'=>$this->successStatus,'data' =>$myStore, 'store_products' => $storeProducts],$this->successStatus); 
            }
            else
            {
                $message = "Store not availabel!";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }

    }

    /*
     * Update Store Details
     * @Params $request
     */
    public function updateStoreDetails(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [ 
                /*'name' => 'required|max:255', 
                'description' => 'required',
                'website' => 'required|max:255',*/
                //'store_region' => 'required',
                //'phone' =>  'required',
                //'location' => 'required|max:255',
                //'lattitude' => 'required|max:255',
                //'longitude' => 'required|max:255'
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $user = $this->user;

            $store = MarketplaceStore::where('user_id', $user->user_id)->first();
            if(!empty($store))
            {
                $store->name = $request->name;
                $store->description = $request->description;
                $store->website = $request->website;
                $store->phone = $request->phone;
                $store->store_region = $request->store_region;
                $store->location = $request->location;
                $store->lattitude = $request->lattitude;
                $store->longitude = $request->longitude;

                if(!empty($request->file('logo_id')))
                {
                    $this->deleteAttachment($store->logo_id);
                    $store->logo_id = $this->uploadImage($request->file('logo_id'));    
                }
                if(!empty($request->file('banner_id')))
                {
                    $this->deleteAttachment($store->banner_id);
                    $store->banner_id = $this->uploadImage($request->file('banner_id'));    
                }
                $store->save();

                $userData = User::where('user_id', $user->user_id)->first();
                $userDetail = MarketplaceStore::where('user_id', $user->user_id)->update(['description' => $userData->about, 'name' => $userData->company_name, 'website' => $userData->website, 'phone' => $userData->phone, 'location' => $userData->address, 'store_region' => $userData->state, 'lattitude' => $userData->lattitude, 'longitude' => $userData->longitude]);

                $existingGalleries = MarketplaceStoreGallery::where('marketplace_store_id', $store->marketplace_store_id)->get();
                /*if(count($existingGalleries) > 0)
                {
                    foreach($existingGalleries as $existingGallery)
                    {
                        unlink('/home/ibyteworkshop/alyseiapi_ibyteworkshop_com/'.$existingGallery->attachment_url);
                        MarketplaceStoreGallery::where('marketplace_store_gallery_id',$existingGallery->marketplace_store_gallery_id)->delete();
                    }
                }*/
                

                //$userDetail = User::where('user_id', $user->user_id)->update(['about' => $request->description, 'company_name' => $request->name, 'website' => $request->website, 'phone' => $request->phone, 'address' => $request->location]);

                if(!empty($request->gallery_images) && count($request->gallery_images) > 0)
                {
                    foreach($request->gallery_images as $images)
                    {
                        $attachmentLinkId = $this->postGallery($images, $store->marketplace_store_id, 1);
                    }
                }
                $galleries = MarketplaceStoreGallery::where('marketplace_store_id', $store->marketplace_store_id)->get();
                (count($galleries) > 0) ? $store->store_gallery = $galleries : $store->store_gallery = [];

                return response()->json(['success'=>$this->successStatus,'data' =>$store],$this->successStatus); 
            }
            else
            {
                $message = "This store is not valid";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }

    }

    /*
    * Delete Gallery
    *
    */
    public function deleteGalleryImage(Request $request)
    {
        try
        {
            $user = $this->user;

            $validator = Validator::make($request->all(), [ 
                'gallery_type' => 'required'
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }
            
            if($request->gallery_type == 1)
            {
                $validator = Validator::make($request->all(), [ 
                    'marketplace_store_gallery_id' => 'required'
                ]);

                if ($validator->fails()) { 
                    return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
                }

                $myStoreGallery = MarketplaceStoreGallery::where('marketplace_store_gallery_id', $request->marketplace_store_gallery_id)->first();
                if(!empty($myStoreGallery))
                {
                    unlink('/home/ibyteworkshop/alyseiapi_ibyteworkshop_com/'.$myStoreGallery->attachment_url);
                    MarketplaceStoreGallery::where('marketplace_store_gallery_id',$request->marketplace_store_gallery_id)->delete();

                    return response()->json(['success' => $this->successStatus,
                                            'message' => $this->translate('messages.'.'Deleted successfully','Deleted successfully')
                                            ], $this->successStatus);
                }
                else
                {
                    $message = "This gallery image is not valid";
                    return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                }

            }
            elseif($request->gallery_type == 2)
            {   
                $validator = Validator::make($request->all(), [ 
                    'marketplace_product_gallery_id' => 'required'
                ]);

                if ($validator->fails()) { 
                    return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
                }

                $myProductGallery = MarketplaceProductGallery::where('marketplace_product_gallery_id', $request->marketplace_product_gallery_id)->first();
                if(!empty($myProductGallery))
                {
                    unlink('/home/ibyteworkshop/alyseiapi_ibyteworkshop_com/'.$myProductGallery->attachment_url);
                    MarketplaceProductGallery::where('marketplace_product_gallery_id',$request->marketplace_product_gallery_id)->delete();
                    
                    return response()->json(['success' => $this->successStatus,
                                            'message' => $this->translate('messages.'.'Deleted successfully','Deleted successfully')
                                            ], $this->successStatus);
                }
                else
                {
                    $message = "This gallery image is not valid";
                    return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                }

            }
            else
            {
                $message = "This gallery type is not valid";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }
    }

    
}
