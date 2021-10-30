<?php

namespace Modules\Marketplace\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Attachment;
use Modules\Marketplace\Entities\WalkthroughScreen;
use Modules\User\Entities\State;
use Modules\Marketplace\Entities\MarketplaceStore;
use Modules\Marketplace\Entities\MarketplaceProduct;
use Modules\Marketplace\Entities\MarketplaceRating;
use Modules\Marketplace\Entities\MarketplaceFavourite;
use Modules\Marketplace\Entities\MarketplaceProductGallery;
use Modules\Marketplace\Entities\MarketplaceBanner;
use Modules\User\Entities\User;
use App\Http\Controllers\CoreController;
use Illuminate\Support\Facades\Auth; 
use Validator;
use DB;

class HomepageController extends CoreController
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


    /*
     * Get Box details
     * 
     */
    public function getBoxDetails($boxId='')
    {
        try
        {
            $user = $this->user;

            if($boxId == 1)
            {
            	return $this->getAllStores();
            }
            elseif($boxId == 2)
            {
            	return $this->getConservationMethod();
            }
            elseif($boxId == 3)
            {
            	return $this->getAllRegions();
            }
            elseif($boxId == 4)
            {
            	return $this->getProductCategories();
            }
            elseif($boxId == 5)
            {
            	return $this->getProductProperties();
            }
            elseif($boxId == 6)
            {
            	return $this->getFDACertifiedProducts();
            }
            elseif($boxId == 7)
            {
            	return $this->getMyFavouriteProducts();
            }
            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }

    }

     /*
     * get filter data
     * 
     */
    public function filter(Request $request)
    {
        try
        {
            $condition = '';
            $storCondition = '';
            $productsArray = [];
            $usersArray = [];
            $storesArray = [];
            $storesUserArray = [];
            $validator = Validator::make($request->all(), [ 
                'type' => 'required'
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }
            if(!empty($request->category))
            {
                if($request->type == 1)
                {
                    $categoryIds = explode(",", $request->category);
                    $productList = MarketplaceProduct::whereIn('product_category_id', $categoryIds)->get();
                    if(count($productList))
                    {
                        $productIds = $productList->pluck('marketplace_store_id')->toArray();
                        foreach($productIds as $productId)
                        {
                            array_push($storesArray, $productId);
                        }
                    }
                }
                elseif($request->type == 2)
                {
                    $categoryIds = explode(",", $request->category);
                    $productList = MarketplaceProduct::whereIn('product_category_id', $categoryIds)->get();
                    if(count($productList))
                    {
                        $productIds = $productList->pluck('marketplace_product_id')->toArray();
                        foreach($productIds as $productId)
                        {
                            array_push($productsArray, $productId);
                        }
                    }    
                }
                
            }
            
            if(!empty($request->property))
            {
                $properties = explode(",", $request->property);
                $options = DB::table('user_field_options')
                                    ->whereIn('option', $properties)
                                    ->where('user_field_id', 2)
                                    ->get();

                if(count($options) > 0)
                {
                    $getOptionIds = $options->pluck('user_field_option_id');
                    $values = DB::table('user_field_values')
                                        ->whereIn('value', $getOptionIds)
                                        ->where('user_field_id', 2)
                                        ->get();
                    $userIds = $values->pluck('user_id')->toArray();
                    foreach($userIds as $userId)
                    {
                        array_push($usersArray, $userId);
                        array_push($storesUserArray, $userId);
                    }
                }
            }
            if(!empty($request->method) && ($request->type == 1))
            {
                $methods = explode(",", $request->method);
                $optionMethods = DB::table('user_field_options')
                                    ->whereIn('option', $methods)
                                    ->where('user_field_id', 2)
                                    ->get();


                if(count($optionMethods) > 0)
                {
                    $optionMethodsIds = $optionMethods->pluck('user_field_option_id');
                    $methodValues = DB::table('user_field_values')
                                        ->whereIn('value', $optionMethodsIds)
                                        ->where('user_field_id', 2)
                                        ->get();
                    $idUsers = $methodValues->pluck('user_id')->toArray();
                    foreach($idUsers as $idUser)
                    {
                        array_push($usersArray, $idUser);
                    }                
                }
            }
            if(!empty($request->region))
            {
                $regionIds = explode(",", $request->region);
                $userList = User::whereIn('state', $regionIds)->get();
                if(count($userList))
                {
                    $userIds = $productList->pluck('user_id')->toArray();
                    foreach($userIds as $userId)
                    {
                        array_push($usersArray, $userId);
                        array_push($storesUserArray, $userId);
                    }
                }
            }
            if(!empty($request->fda_certified) && ($request->type == 1))
            {
                if($request->fda_certified == 1)
                {
                    $fdaUsers = User::whereNotNull('fda_no')->get();
                    if(count($fdaUsers) > 0)
                    {
                        $fdaCertUsers = $fdaUsers->pluck('user_id')->toArray();
                        foreach($fdaCertUsers as $fdaCertUser)
                        {
                            array_push($usersArray, $fdaCertUser);
                        }
                    }
                }
                else
                {
                    $fdaUsers = User::whereNull('fda_no')->get();
                    if(count($fdaUsers) > 0)
                    {
                        $fdaCertUsers = $fdaUsers->pluck('user_id')->toArray();
                        foreach($fdaCertUsers as $fdaCertUser)
                        {
                            array_push($usersArray, $fdaCertUser);
                        }
                    }
                }
                
            }
            if(!empty($request->sort_by_producer))
            {
                if($request->sort_by_producer == 1) //accending
                {
                    $producers = User::orderBy('company_name')->get();
                    if(count($producers) > 0)
                    {
                        $producersIds = $producers->pluck('user_id')->toArray();
                        foreach($producersIds as $producersId)
                        {
                            array_push($usersArray, $producersId);
                        }
                    }
                }
                else //decending
                {
                    $producers = User::orderBy('company_name', 'DESC')->get();
                    if(count($producers) > 0)
                    {
                        $producersIds = $producers->pluck('user_id')->toArray();
                        foreach($producersIds as $producersId)
                        {
                            array_push($usersArray, $producersId);
                        }
                    }
                }
                
            }
            /*if(!empty($request->sort_by_product))
            {
                if($request->sort_by_product == 1) //accending
                {                
                    if($condition != '')
                    $condition .=" and marketplace_products.user_id in(".$joinProducersId.")";
                    else
                    $condition .="marketplace_products.user_id in(".$joinProducersId.")";
                    
                }
                else //decending
                {
                    $producers = User::orderBy('company_name', 'DESC')->get();
                    if(count($producers) > 0)
                    {
                        $producersId = $producers->pluck('user_id')->toArray();
                        $joinProducersId = join(",", $producersId);
                        if($condition != '')
                        $condition .=" and marketplace_products.user_id in(".$joinProducersId.")";
                        else
                        $condition .="marketplace_products.user_id in(".$joinProducersId.")";
                    }
                }
                
            }*/
            if(!empty($request->rating))
            {
                if($request->rating == 1) //most rated
                {
                    if($request->type == 1)
                    {
                        $avgRating = MarketplaceRating::where('type', '1')->groupBy('id')->orderBy(DB::raw("count(*)"), "DESC")->get();
                        if(count($avgRating) > 0)
                        {
                            $productId = $avgRating->pluck('id')->toArray();
                            foreach($productId as $productIdss)
                            {
                                array_push($storesArray, $productIdss);
                            }
                        }
                    }
                    elseif($request->type == 2)
                    {
                        $avgRating = MarketplaceRating::where('type', '2')->groupBy('id')->orderBy(DB::raw("count(*)"), "DESC")->get();
                        if(count($avgRating) > 0)
                        {
                            $productId = $avgRating->pluck('id')->toArray();
                            foreach($productId as $productIdss)
                            {
                                array_push($productsArray, $productIdss);
                            }
                        }
                    }
                    
                }
                if($request->rating == 2) //5 star
                {
                    if($request->type == 1)
                    {
                        $fiveRating = MarketplaceRating::where('type', '1')->where('rating', 5)->get();
                        if(count($fiveRating) > 0)
                        {
                            $productIds = $fiveRating->pluck('id')->toArray();
                            foreach($productIds as $prodId)
                            {
                                array_push($productsArray, $prodId);
                            }
                        }
                    }
                    elseif($request->type == 2)
                    {
                        $fiveRating = MarketplaceRating::where('type', '2')->where('rating', 5)->get();
                        if(count($fiveRating) > 0)
                        {
                            $productIds = $fiveRating->pluck('id')->toArray();
                            foreach($productIds as $prodId)
                            {
                                array_push($productsArray, $prodId);
                            }
                        }
                    }
                    
                }
                
            }

            if(count($productsArray) > 0)
            {
                $join = join(",", $productsArray);
                if($condition != '')
                $condition .=" and marketplace_products.marketplace_product_id in(".$join.")";
                else
                $condition .="marketplace_products.marketplace_product_id in(".$join.")";
            }
            if(count($usersArray) > 0)
            {
                $joinUsers = join(",", $usersArray);
                if($condition != '')
                $condition .=" and marketplace_products.user_id in(".$joinUsers.")";
                else
                $condition .="marketplace_products.user_id in(".$joinUsers.")";
            }
            if(count($storesArray) > 0)
            {
                $joinStoresId = join(",", $storesArray);
                if($storCondition != '')
                $storCondition .=" and marketplace_stores.marketplace_store_id in(".$joinStoresId.")";
                else
                $storCondition .="marketplace_stores.marketplace_store_id in(".$joinStoresId.")";
            }
            if(count($storesUserArray) > 0)
            {
                $joinStoresUsers = join(",", $storesUserArray);
                if($storCondition != '')
                $storCondition .=" and marketplace_stores.user_id in(".$joinStoresUsers.")";
                else
                $storCondition .="marketplace_stores.user_id in(".$joinStoresUsers.")";
            }
            if(!empty($request->keyword))
            {
                if($request->type == 1)
                {
                    if($storCondition != '')
                    $storCondition .=" and marketplace_stores.name LIKE "."'%".$request->keyword."%'"."";
                    else
                    $storCondition .="marketplace_stores.name LIKE "."'%".$request->keyword."%'"."";
                }
                elseif($request->type == 2)
                {
                    if($condition != '')
                    $condition .=" and marketplace_products.title LIKE "."'%".$request->keyword."%'"."";
                    else
                    $condition .="marketplace_products.title LIKE "."'%".$request->keyword."%'"."";
                }
                
            }
            if(!empty($request->sort_by_product))
            {
                if($request->sort_by_product == 1) //accending
                {
                    if($condition != '')
                    $condition .=" and marketplace_products.title LIKE '%".$request->keyword."%'";
                    else
                    $condition .="marketplace_products.title LIKE '%".$request->keyword."%'";
                }
            }
            $getFilterProducts = [];
            if($request->type == 1)
            {
                if($storCondition != '')
                {
                    $getFilterProducts = MarketplaceStore::with('store_gallery')->where('status','1')->whereRaw('('.$storCondition.')')->orderBy('marketplace_store_id', 'DESC')->paginate(10);
                }
                else
                {
                    $getFilterProducts = MarketplaceStore::with('store_gallery')->where('status','1')->orderBy('marketplace_store_id', 'DESC')->paginate(10);
                }

                if(count($getFilterProducts) > 0)
                {
                    foreach($getFilterProducts as $key => $product)
                    {
                        $avgRating = MarketplaceRating::where('type', '1')->where('id', $product->marketplace_store_id)->avg('rating');
                        $totalReviews = MarketplaceRating::where('type', '1')->where('id', $product->marketplace_store_id)->count();
                        $store = MarketplaceStore::where('marketplace_store_id', $product->marketplace_store_id)->first();

                        $logoId = Attachment::where('id', $store->logo_id)->first();
                        $bannerId = Attachment::where('id', $store->banner_id)->first();
                        $getFilterProducts[$key]->logo_id = $logoId->attachment_url;
                        $getFilterProducts[$key]->avg_rating = number_format((float)$avgRating, 1, '.', '');
                        $getFilterProducts[$key]->total_reviews = $totalReviews;
                        $getFilterProducts[$key]->store_name = $store->name;
                    }
                }          
                
            }
            elseif($request->type == 2)
            {
                if($condition != '')
                {
                    if($request->box_id == 6){
                        if(count($this->getCertifiedProducts()) > 0)
                        {
                            $userIds = $this->getCertifiedProducts();
                            $getFilterProducts = MarketplaceProduct::with('product_gallery')->whereRaw('('.$condition.')')->whereIn('user_id', $userIds)->paginate(10);
                        } 
                    }
                    elseif($request->box_id == 2 || $request->box_id == 5)
                    {
                        if(count($this->getAllUsersByProductTypes($request->title)) > 0)
                        {
                            $conservationUsers = $this->getAllUsersByProductTypes($request->title);
                            $getFilterProducts = MarketplaceProduct::with('product_gallery')->whereIn('user_id', $conservationUsers)->whereRaw('('.$condition.')')->paginate(10);
                        }
                    }
                    elseif($request->box_id == 3)
                    {
                        if(count($this->getAllProductsByRegions($request->title)) > 0)
                        {
                            $usersByRegion = $this->getAllProductsByRegions($request->title);
                            $getFilterProducts = MarketplaceProduct::with('product_gallery')->whereIn('user_id', $usersByRegion)->whereRaw('('.$condition.')')->paginate(10);
                        }
                    }
                    elseif($request->box_id == 4)
                    {
                        $getFilterProducts = MarketplaceProduct::with('product_gallery')->where('product_category_id', $request->title)->whereRaw('('.$condition.')')->paginate(10);
                    }
                    elseif($request->box_id == 7)
                    {
                        if(count($this->getAllFavouriteProducts($request->title)) > 0)
                        {
                            $favProducts = $this->getAllFavouriteProducts($request->title);
                            $getFilterProducts = MarketplaceProduct::with('product_gallery')->whereIn('marketplace_product_id', $favProducts)->whereRaw('('.$condition.')')->paginate(10);
                        }
                    }
                }
                else
                {
                    if($request->box_id == 6){
                        if(count($this->getCertifiedProducts()) > 0)
                        {
                            $userIds = $this->getCertifiedProducts();
                            $getFilterProducts = MarketplaceProduct::with('product_gallery')->whereIn('user_id', $userIds)->paginate(10);
                        } 
                    }
                    elseif($request->box_id == 2 || $request->box_id == 5)
                    {
                        if(count($this->getAllUsersByProductTypes($request->title)) > 0)
                        {
                            $conservationUsers = $this->getAllUsersByProductTypes($request->title);
                            $getFilterProducts = MarketplaceProduct::with('product_gallery')->whereIn('user_id', $conservationUsers)->paginate(10);
                        }
                    }
                    elseif($request->box_id == 3)
                    {
                        if(count($this->getAllProductsByRegions($request->title)) > 0)
                        {
                            $usersByRegion = $this->getAllProductsByRegions($request->title);
                            $getFilterProducts = MarketplaceProduct::with('product_gallery')->whereIn('user_id', $usersByRegion)->paginate(10);
                        }
                    }
                    elseif($request->box_id == 4)
                    {
                        $getFilterProducts = MarketplaceProduct::with('product_gallery')->where('product_category_id', $request->title)->paginate(10);
                    }
                    elseif($request->box_id == 7)
                    {
                        if(count($this->getAllFavouriteProducts($request->title)) > 0)
                        {
                            $favProducts = $this->getAllFavouriteProducts($request->title);
                            $getFilterProducts = MarketplaceProduct::with('product_gallery')->whereIn('marketplace_product_id', $favProducts)->paginate(10);
                        }
                    }
                }



                /***/

                if(count($getFilterProducts) > 0)
                {
                    foreach($getFilterProducts as $key => $product)
                    {
                        $avgRating = MarketplaceRating::where('type', '2')->where('id', $product->marketplace_store_id)->avg('rating');
                        $totalReviews = MarketplaceRating::where('type', '2')->where('id', $product->marketplace_product_id)->count();
                        $store = MarketplaceStore::where('marketplace_store_id', $product->marketplace_store_id)->first();

                        $options = DB::table('user_field_options')
                                    ->where('user_field_option_id', $product->product_category_id)
                                    ->first();
                        if(!empty($options->option))
                        {
                            $getFilterProducts[$key]->product_category_name = $options->option;
                        }
                        else
                        {
                            $getFilterProducts[$key]->product_category_name = '';
                        }

                        $logoId = Attachment::where('id', $store->logo_id)->first();
                        $bannerId = Attachment::where('id', $store->banner_id)->first();
                        $getFilterProducts[$key]->logo_id = $logoId->attachment_url;
                        $getFilterProducts[$key]->avg_rating = number_format((float)$avgRating, 1, '.', '');
                        $getFilterProducts[$key]->total_reviews = $totalReviews;
                        $getFilterProducts[$key]->store_name = $store->name;
                    }
                }
                else
                {
                    $message = "Nothing found";
                    return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                }
                
            }
            
            return response()->json(['success' => $this->successStatus,
                                    'count' => count($getFilterProducts),
                                     'data' => $getFilterProducts   
                                    ],$this->successStatus);
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }         
    }

    /*
     * Get all entities for homescreen
     * 
     */
    public function getAllEntities($entityId = '')
    {
        try
        {
            $user = $this->user;

            if($entityId == 1)
            {
                return $this->getRecentlyAddedProducts();
            }
            elseif($entityId == 2)
            {
                return $this->getNewlyAddedStores();
            }
            elseif($entityId == 3)
            {
                return $this->gettopRatedProducts();
            }
            else
            {
                $message = "wrong entity selected";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }

    }

    /*
     * Get recently added products
     * 
     */
    public function getRecentlyAddedProducts()
    {
        $allProducts = MarketplaceProduct::orderBy('marketplace_product_id', 'DESC')->paginate(10);
        if(count($allProducts) > 0)
        {
            foreach($allProducts as $key => $product)
            {
                $avgRating = MarketplaceRating::where('type', '2')->where('id', $product->marketplace_product_id)->avg('rating');
                $totalReviews = MarketplaceRating::where('type', '2')->where('id', $product->marketplace_product_id)->count();
                $store = MarketplaceStore::where('marketplace_store_id', $product->marketplace_store_id)->first();
                $productImg = MarketplaceProductGallery::where('marketplace_product_id', $product->marketplace_product_id)->first();

                if(!empty($productImg->attachment_url))
                {
                    $allProducts[$key]->logo_id = $productImg->attachment_url;    
                }
                else
                {
                    $allProducts[$key]->logo_id = "";
                }
                
                $allProducts[$key]->avg_rating = number_format((float)$avgRating, 1, '.', '');
                $allProducts[$key]->total_reviews = $totalReviews;
                $allProducts[$key]->store_name = $store->name;

                return response()->json(['success' => $this->successStatus,
                                    'data' => $allProducts,
                                    ],$this->successStatus);
            }
            
        }
        else
        {
            $message = "We did not found any products";
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
        }
    }

    /*
     * Get newly added stores
     * 
     */
    public function getNewlyAddedStores()
    {
        $newAddedStores = MarketplaceStore::with('logo_id')->where('status', '1')->orderBy('marketplace_store_id', 'DESC')->paginate(10);
        if(count($newAddedStores) > 0)
        {
            return response()->json(['success' => $this->successStatus,
                                    'data' => $newAddedStores,
                                    ],$this->successStatus);
        }
        else
        {
            $message = "We did not found any stores";
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
        }
    }

    /*
     * Get top rated products
     * 
     */
    public function gettopRatedProducts()
    {
        $topRatedProductsArray = [];
        $topRatedProductsData = DB::select(DB::raw("select id from marketplace_review_ratings where type = '2' GROUP BY id ORDER BY count(*) DESC"));
        if(count($topRatedProductsData) > 0)
        {
            foreach($topRatedProductsData as $topRatedProductData)
            {
                array_push($topRatedProductsArray, $topRatedProductData->id);
            }

            $topRatedProducts = MarketplaceProduct::whereIn('marketplace_product_id', $topRatedProductsArray)->orderBy('marketplace_product_id', 'DESC')->paginate(10);

            if(count($topRatedProducts) > 0)
            {
                foreach($topRatedProducts as $topKey => $topRatedProduct)
                {
                    $avgRatingOfTopRated = MarketplaceRating::where('type', '2')->where('id', $topRatedProduct->marketplace_product_id)->avg('rating');
                    $totalReviewsOfToprated = MarketplaceRating::where('type', '2')->where('id', $topRatedProduct->marketplace_product_id)->count();
                    $storeOfTopRated = MarketplaceStore::where('marketplace_store_id', $topRatedProduct->marketplace_store_id)->first();
                    $productOfTopRatedImg = MarketplaceProductGallery::where('marketplace_product_id', $topRatedProduct->marketplace_product_id)->first();
                    

                    if(!empty($productOfTopRatedImg->attachment_url))
                    {
                        $topRatedProducts[$topKey]->logo_id = $productOfTopRatedImg->attachment_url;    
                    }
                    else
                    {
                        $topRatedProducts[$topKey]->logo_id = "";
                    }
                    $topRatedProducts[$topKey]->avg_rating = number_format((float)$avgRatingOfTopRated, 1, '.', '');
                    $topRatedProducts[$topKey]->total_reviews = $totalReviewsOfToprated;
                    $topRatedProducts[$topKey]->store_name = $storeOfTopRated->name;
                }
                
                return response()->json(['success' => $this->successStatus,
                                    'data' => $topRatedProducts,
                                    ],$this->successStatus);
            }
        }
        else
        {
            $message = "We did not found any products";
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
        }
    }

     /*
     * Get homepage data
     * 
     */
    public function getHomeScreen()
    {
        try
        {
            $user = $this->user;
            $topRatedProductsArray = [];
            $topFavouriteProductsArray = [];
            $allProducts = MarketplaceProduct::orderBy('marketplace_product_id', 'DESC')->limit(8)->get();
            $allStores = MarketplaceStore::with('logo_id')->where('status', '1')->orderBy('marketplace_store_id', 'DESC')->limit(8)->get();
            $allRegions = State::select('id','name')->where('status', '1')->where('country_id', 107)->orderBy('name', 'DESC')->limit(8)->get();
            $topBanners = MarketplaceBanner::with('attachment')->where('type', '1')->orderBy('marketplace_banner_id', 'DESC')->get();
            $lowerBanners = MarketplaceBanner::with('attachment')->where('type', '2')->orderBy('marketplace_banner_id', 'DESC')->get();

            if(count($allProducts) > 0)
            {
                foreach($allProducts as $key => $product)
                {
                    $avgRating = MarketplaceRating::where('type', '2')->where('id', $product->marketplace_product_id)->avg('rating');
                    $totalReviews = MarketplaceRating::where('type', '2')->where('id', $product->marketplace_product_id)->count();
                    $store = MarketplaceStore::where('marketplace_store_id', $product->marketplace_store_id)->first();
                    $productImg = MarketplaceProductGallery::where('marketplace_product_id', $product->marketplace_product_id)->first();

                    if(!empty($productImg->attachment_url))
                    {
                        $allProducts[$key]->logo_id = $productImg->attachment_url;    
                    }
                    else
                    {
                        $allProducts[$key]->logo_id = "";
                    }
                    
                    $allProducts[$key]->avg_rating = number_format((float)$avgRating, 1, '.', '');
                    $allProducts[$key]->total_reviews = $totalReviews;
                    $allProducts[$key]->store_name = $store->name;
                }
                
            }

            //top rated products
            $topRatedProductsData = DB::select(DB::raw("select id from marketplace_review_ratings where type = '2' GROUP BY id ORDER BY count(*) DESC LIMIT 8"));
            foreach($topRatedProductsData as $topRatedProductData)
            {
                array_push($topRatedProductsArray, $topRatedProductData->id);
            }

            $topRatedProducts = MarketplaceProduct::whereIn('marketplace_product_id', $topRatedProductsArray)->orderBy('marketplace_product_id', 'DESC')->limit(8)->get();
            if(count($topRatedProducts) > 0)
            {
                foreach($topRatedProducts as $topKey => $topRatedProduct)
                {
                    $avgRatingOfTopRated = MarketplaceRating::where('type', '2')->where('id', $topRatedProduct->marketplace_product_id)->avg('rating');
                    $totalReviewsOfToprated = MarketplaceRating::where('type', '2')->where('id', $topRatedProduct->marketplace_product_id)->count();
                    $storeOfTopRated = MarketplaceStore::where('marketplace_store_id', $topRatedProduct->marketplace_store_id)->first();
                    $productOfTopRatedImg = MarketplaceProductGallery::where('marketplace_product_id', $topRatedProduct->marketplace_product_id)->first();
                    

                    if(!empty($productOfTopRatedImg->attachment_url))
                    {
                        $topRatedProducts[$topKey]->logo_id = $productOfTopRatedImg->attachment_url;    
                    }
                    else
                    {
                        $topRatedProducts[$topKey]->logo_id = "";
                    }
                    $topRatedProducts[$topKey]->avg_rating = number_format((float)$avgRatingOfTopRated, 1, '.', '');
                    $topRatedProducts[$topKey]->total_reviews = $totalReviewsOfToprated;
                    $topRatedProducts[$topKey]->store_name = $storeOfTopRated->name;
                }
                
            }
            //

            //top favourite products
            $topFavouriteProductsData = DB::select(DB::raw("select id from marketplace_favourites where favourite_type = '2' GROUP BY id ORDER BY count(*) DESC LIMIT 8"));
            foreach($topFavouriteProductsData as $topFavouriteProductData)
            {
                array_push($topFavouriteProductsArray, $topFavouriteProductData->id);
            }

            $topFavouriteProducts = MarketplaceProduct::whereIn('marketplace_product_id', $topFavouriteProductsArray)->orderBy('marketplace_product_id', 'DESC')->limit(8)->get();
            if(count($topFavouriteProducts) > 0)
            {
                foreach($topFavouriteProducts as $topFavKey => $topFavouriteProduct)
                {
                    $avgRatingOfTopFavourite = MarketplaceRating::where('type', '2')->where('id', $topFavouriteProduct->marketplace_product_id)->avg('rating');
                    $totalReviewsOfTopFavourite = MarketplaceRating::where('type', '2')->where('id', $topFavouriteProduct->marketplace_product_id)->count();
                    $storeOfTopFavourite = MarketplaceStore::where('marketplace_store_id', $topFavouriteProduct->marketplace_store_id)->first();
                    $productOfTopFavouriteImg = MarketplaceProductGallery::where('marketplace_product_id', $topFavouriteProduct->marketplace_product_id)->first();

                    if(!empty($productOfTopFavouriteImg->attachment_url))
                    {
                        $topFavouriteProducts[$topFavKey]->logo_id = $productOfTopFavouriteImg->attachment_url;    
                    }
                    else
                    {
                        $topFavouriteProducts[$topFavKey]->logo_id = "";
                    }
                    $topFavouriteProducts[$topFavKey]->avg_rating = number_format((float)$avgRatingOfTopFavourite, 1, '.', '');
                    $topFavouriteProducts[$topFavKey]->total_reviews = $totalReviewsOfTopFavourite;
                    $topFavouriteProducts[$topFavKey]->store_name = $storeOfTopFavourite->name;
                }
                
            }
            User::where('user_id', $user->user_id)->update(['is_visited_marketplace' => '1']);

            $data = ['top_banners' => $topBanners, 'recently_added_product' => $allProducts, 'newly_added_store' => $allStores, 'regions' => $allRegions, 'top_rated_products' => $topRatedProducts,'top_favourite_products' => $topFavouriteProducts, 'bottom_banners' => $lowerBanners];
            return response()->json(['success' => $this->successStatus,
                                     'is_visited_marketplace' => ($user->is_visited_marketplace == '1' ? 1 : 0),
                                     'data' => $data   
                                    ],$this->successStatus);
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }
    }


    /*
     * Get all stores
     * 
     */
    public function getAllStores()
    {
    	$allStores = MarketplaceStore::where('status', '1')->orderBy('marketplace_store_id', 'DESC')->paginate(10);
        if(count($allStores) > 0)
        {
        	foreach($allStores as $key => $store)
        	{
        		$avgRating = MarketplaceRating::where('type', '1')->where('id', $store->marketplace_store_id)->avg('rating');
                $totalReviews = MarketplaceRating::where('type', '1')->where('id', $store->marketplace_store_id)->count();
                $store = MarketplaceStore::where('marketplace_store_id', $store->marketplace_store_id)->first();


                $logoId = Attachment::where('id', $store->logo_id)->first();
                $bannerId = Attachment::where('id', $store->banner_id)->first();
                $allStores[$key]->logo_id = $logoId->attachment_url;
                $allStores[$key]->avg_rating = number_format((float)$avgRating, 1, '.', '');
                $allStores[$key]->total_reviews = $totalReviews;
                $allStores[$key]->store_name = $store->name;
        	}
            return response()->json(['success' => $this->successStatus,
                                    'data' => $allStores,
                                	],$this->successStatus); 
        }
        else
        {
            $message = "We did not found any stores";
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
        }
    }

    /*
     * Get conservation methods
     * 
     */
    public function getConservationMethod()
    {
        $options = DB::table('user_field_options')
                                ->where('head','!=', 0)->where('parent','!=', 0)
                                ->where('user_field_id', 2)
                                ->first();
        if($options)
        {
            $childOptions = DB::table('user_field_options')
                                ->where('head', 0)->where('parent', $options->user_field_option_id)
                                ->where('user_field_id', 2)
                                ->get();
            
            return response()->json(['success' => $this->successStatus,
                                    'data' => $childOptions,
                                    ],$this->successStatus); 
        }
        else
        {
            $message = "We did not found any conservation methods";
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
        }
    }

    /*
     * Get Products box-2/5
     * 
     */
    public function getProducts(Request $request)
    {
        try
        {
            $user = $this->user;

            $validator = Validator::make($request->all(), [ 
                'keyword' => 'required'
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            return $this->getProductsBySelection($request->keyword);
            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }
        
    }

    /*
     * Get Products by regions
     * 
     */
    public function getProductsByRegions(Request $request)
    {
        try
        {
            $user = $this->user;

            $validator = Validator::make($request->all(), [ 
                'region_id' => 'required'
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $getUsersByRegion = User::where('state', $request->region_id)->get();
            if(count($getUsersByRegion) > 0)
            {
                $getUserIds = $getUsersByRegion->pluck('user_id');
                $products = MarketplaceProduct::with('product_gallery')->whereIn('user_id', $getUserIds)->paginate(10);
                if(count($products) > 0)
                {
                    foreach($products as $key => $product)
                    {
                        $options = DB::table('user_field_options')
                                    ->where('user_field_option_id', $product->product_category_id)
                                    ->first();
                        if(!empty($options->option))
                        {
                            $products[$key]->product_category_name = $options->option;
                        }
                        else
                        {
                            $products[$key]->product_category_name = '';
                        }
                        $avgRating = MarketplaceRating::where('type', '2')->where('id', $product->marketplace_store_id)->avg('rating');
                        $totalReviews = MarketplaceRating::where('type', '2')->where('id', $product->marketplace_product_id)->count();
                        $store = MarketplaceStore::where('marketplace_store_id', $product->marketplace_store_id)->first();

                        $products[$key]->avg_rating = number_format((float)$avgRating, 1, '.', '');
                        $products[$key]->total_reviews = $totalReviews;
                        $products[$key]->store_name = $store->name;
                    }
                    return response()->json(['success' => $this->successStatus,
                                    'count' => count($products),
                                    'data' => $products,
                                    ], $this->successStatus);
                }
                else
                {
                    $message = "No product found";
                    return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);    
                }
            }
            else
            {
                $message = "No product found";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);    
            }

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }
        
    }

    /*
     * Get Products by category
     * 
     */
    public function getProductsByCategory(Request $request)
    {
        try
        {
            $user = $this->user;

            $validator = Validator::make($request->all(), [ 
                'category_id' => 'required'
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            
            $products = MarketplaceProduct::with('product_gallery')->where('product_category_id', $request->category_id)->paginate(10);
            if(count($products) > 0)
            {
                foreach($products as $key => $product)
                {
                    $options = DB::table('user_field_options')
                                    ->where('user_field_option_id', $product->product_category_id)
                                    ->first();
                    if(!empty($options->option))
                    {
                        $products[$key]->product_category_name = $options->option;
                    }
                    else
                    {
                        $products[$key]->product_category_name = '';
                    }
                    $avgRating = MarketplaceRating::where('type', '2')->where('id', $product->marketplace_store_id)->avg('rating');
                    $totalReviews = MarketplaceRating::where('type', '2')->where('id', $product->marketplace_product_id)->count();
                    $store = MarketplaceStore::where('marketplace_store_id', $product->marketplace_store_id)->first();

                    $products[$key]->avg_rating = number_format((float)$avgRating, 1, '.', '');
                    $products[$key]->total_reviews = $totalReviews;
                    $products[$key]->store_name = $store->name;
                }
                return response()->json(['success' => $this->successStatus,
                                'count' => count($products),
                                'data' => $products,
                                ], $this->successStatus);
            }
            else
            {
                $message = "No product found";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);    
            }
            
            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }
        
    }

    /*
    *
    * Get products by conservation methods or product properties
    *
    */
    public function getProductsBySelection($keyword='')
    {
        $options = DB::table('user_field_options')
                                ->where('option', 'LIKE', '%'.$keyword.'%')
                                ->where('user_field_id', 2)
                                ->get();
                                

        if(count($options) > 0)
        {
            $getOptionIds = $options->pluck('user_field_option_id');
            $values = DB::table('user_field_values')
                                ->whereIn('value', $getOptionIds)
                                ->where('user_field_id', 2)
                                ->get();
            if(count($values) > 0)
            {
                $userIds = $values->pluck('user_id');
                $products = MarketplaceProduct::with('product_gallery')->whereIn('user_id', $userIds)->paginate(10);
                foreach($products as $key => $product)
                {
                    $options = DB::table('user_field_options')
                                ->where('user_field_option_id', $product->product_category_id)
                                ->first();

                    $avgRating = MarketplaceRating::where('type', '2')->where('id', $product->marketplace_store_id)->avg('rating');
                    $totalReviews = MarketplaceRating::where('type', '2')->where('id', $product->marketplace_product_id)->count();
                    $store = MarketplaceStore::where('marketplace_store_id', $product->marketplace_store_id)->first();

                    $products[$key]->avg_rating = number_format((float)$avgRating, 1, '.', '');
                    $products[$key]->total_reviews = $totalReviews;
                    $products[$key]->store_name = $store->name;
                    if(!empty($options->option))
                    {
                        $products[$key]->product_category_name = $options->option;
                    }
                    else
                    {
                        $products[$key]->product_category_name = '';
                    }
                    
                }
                
            }
            else
            {
                $message = "No product found";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }            
            return response()->json(['success' => $this->successStatus,
                                    'data' => $products,
                                    ],$this->successStatus); 
        }
        else
        {
            $message = "No product found";
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
        }
    }

    /*
     * Get product properties
     * 
     */
    public function getProductProperties()
    {
        $options = DB::table('user_field_options')
                                ->where('head','!=', 0)->where('parent','!=', 0)
                                ->where('user_field_id', 2)->skip(1)
                                ->first();
        if($options)
        {
            $childOptions = DB::table('user_field_options')
                                ->where('head', 0)->where('parent', $options->user_field_option_id)
                                ->where('user_field_id', 2)
                                ->get();
            
            return response()->json(['success' => $this->successStatus,
                                    'data' => $childOptions,
                                    ],$this->successStatus); 
        }
        else
        {
            $message = "We did not found any product properties";
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
        }
    }

    /*
     * Get all regions
     * 
     */
    public function getAllRegions()
    {
    	$allRegions = State::select('id','name')->where('status', '1')->where('country_id', 107)->orderBy('name', 'DESC')->get();
        if(count($allRegions) > 0)
        {
            return response()->json(['success' => $this->successStatus,
                                    'data' => $allRegions,
                                	],$this->successStatus); 
        }
        else
        {
            $message = "We did not found any regions";
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
        }
    }

    /*
     * Get product categories
     * 
     */
    public function getProductCategories()
    {
    	$options = DB::table('user_field_options')
                                ->where('head', 0)->where('parent', 0)
                                ->where('user_field_id', 2)
                                ->get();
        if(count($options) > 0)
        {
            foreach($options as $key => $option)
            {
                $arrayValues[] = ['marketplace_product_category_id'=>$option->user_field_option_id, 'name' => $option->option];    
            }
            return response()->json(['success' => $this->successStatus,
                            'count' => count($arrayValues),
                            'data' => $arrayValues,
                            ], $this->successStatus);
            
        }   
        else
        {
            $message = "No product categories found";
            return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);    
        }
    }

    /*
     * Get my favourite products
     * 
     */
    public function getMyFavouriteProducts()
    {
    	$user = $this->user;
    	$favouriteList = MarketplaceFavourite::where('favourite_type', '2')->where('user_id', $user->user_id)->get();
    	if(count($favouriteList) > 0)
    	{
    		$productIds = $favouriteList->pluck('id');
    		$products = MarketplaceProduct::with('product_gallery')->whereIn('marketplace_product_id', $productIds)->paginate(10);
    		foreach($products as $key => $product)
    		{
                $options = DB::table('user_field_options')
                                    ->where('user_field_option_id', $product->product_category_id)
                                    ->first();
                if(!empty($options->option))
                {
                    $products[$key]->product_category_name = $options->option;
                }
                else
                {
                    $products[$key]->product_category_name = '';
                }
    			$avgRating = MarketplaceRating::where('type', '2')->where('id', $product->marketplace_store_id)->avg('rating');
                $totalReviews = MarketplaceRating::where('type', '2')->where('id', $product->marketplace_product_id)->count();
                $store = MarketplaceStore::where('marketplace_store_id', $product->marketplace_store_id)->first();

                $products[$key]->avg_rating = number_format((float)$avgRating, 1, '.', '');
                $products[$key]->total_reviews = $totalReviews;
                $products[$key]->store_name = $store->name;
    		}
    		return response()->json(['success' => $this->successStatus,
                            'count' => count($products),
                            'data' => $products,
                            ], $this->successStatus);
    	}
        else
        {
            $message = "No product found";
            return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);    
        }
    }

    /*
     * Get FDA certified products
     * 
     */
    public function getFDACertifiedProducts()
    {
    	$user = $this->user;
    	$userList = User::where('fda_no','!=',null)->get();
    	if(count($userList) > 0)
    	{
    		$userIds = $userList->pluck('user_id');
    		$products = MarketplaceProduct::with('product_gallery')->whereIn('user_id', $userIds)->paginate(10);
    		foreach($products as $key => $product)
    		{
                $options = DB::table('user_field_options')
                                    ->where('user_field_option_id', $product->product_category_id)
                                    ->first();
                if(!empty($options->option))
                {
                    $products[$key]->product_category_name = $options->option;
                }
                else
                {
                    $products[$key]->product_category_name = '';
                }
    			$avgRating = MarketplaceRating::where('type', '2')->where('id', $product->marketplace_store_id)->avg('rating');
                $totalReviews = MarketplaceRating::where('type', '2')->where('id', $product->marketplace_product_id)->count();
                $store = MarketplaceStore::where('marketplace_store_id', $product->marketplace_store_id)->first();

                $products[$key]->avg_rating = number_format((float)$avgRating, 1, '.', '');
                $products[$key]->total_reviews = $totalReviews;
                $products[$key]->store_name = $store->name;
    		}
    		return response()->json(['success' => $this->successStatus,
                            'count' => count($products),
                            'data' => $products,
                            ], $this->successStatus);
    	}
        else
        {
            $message = "No product found";
            return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);    
        }
    }

    /*
     * Get all product types
     * 
     */
    public function getAllUsersByProductTypes($title='')
    {
        $userIds = [];

        $options = DB::table('user_field_options')
                                ->where('option', 'LIKE', '%'.$title.'%')
                                ->where('user_field_id', 2)
                                ->get();
        if(count($options) > 0)
        {
            $getOptionIds = $options->pluck('user_field_option_id');
            $values = DB::table('user_field_values')
                                ->whereIn('value', $getOptionIds)
                                ->where('user_field_id', 2)
                                ->get();
            if(count($values) > 0)
            {
                $userIds = $values->pluck('user_id');
            }
        }    
        return $userIds;
    }

    /*
     * Get all products by regions
     * 
     */
    public function getAllProductsByRegions($regionId='')
    {
        $getUserRegions = [];
        $getUsersByRegion = User::where('state', $regionId)->get();
        if(count($getUsersByRegion) > 0)
        {
            $getUserRegions = $getUsersByRegion->pluck('user_id');
        }
        return $getUserRegions;
    }

     /*
     * Get certified products
     * 
     */
    public function getCertifiedProducts()
    {
        $user = $this->user;
        $userList = User::where('fda_no','!=',null)->get();
        if(count($userList) > 0)
        {
            $userIds = $userList->pluck('user_id');
        }
        else
        {
            $userIds = [];  
        }
        return $userIds;
    }

    /*
    * 
    *
    */
    public function getAllFavouriteProducts()
    {
        $user = $this->user;
        $productIds = [];
        $favouriteList = MarketplaceFavourite::where('favourite_type', '2')->where('user_id', $user->user_id)->get();
        if(count($favouriteList) > 0)
        {
            $productIds = $favouriteList->pluck('id');
        }
        return $productIds;
    }
}