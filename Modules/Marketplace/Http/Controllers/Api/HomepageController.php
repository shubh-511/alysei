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
        $condition = '';

        if(!empty($request->category))
        {
            $categoryIds = explode(",", $request->category);
            $productList = MarketplaceProduct::whereIn('product_category_id', $categoryIds)->get();
            if(count($productList))
            {
                $productIds = $productList->pluck('marketplace_product_id')->toArray();
                
                $products = join(",", $productIds);
                if($condition != '')
                $condition .=" and marketplace_products.marketplace_product_id in(".$products.")";
                else
                $condition .="marketplace_products.marketplace_product_id in(".$products.")";
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
                $userIds = join(",", $userIds);
                if($condition != '')
                $condition .=" and marketplace_products.user_id in(".$userIds.")";
                else
                $condition .="marketplace_products.user_id in(".$userIds.")";
            }
        }
        if(!empty($request->method))
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
                $idUsers = join(",", $idUsers);
                if($condition != '')
                $condition .=" and marketplace_products.user_id in(".$idUsers.")";
                else
                $condition .="marketplace_products.user_id in(".$idUsers.")";
            }
        }
        if(!empty($request->region))
        {
            $regionIds = explode(",", $request->region);
            $userList = User::whereIn('state', $regionIds)->get();
            if(count($userList))
            {
                $userIds = $productList->pluck('user_id')->toArray();
                
                $users = join(",", $userIds);
                if($condition != '')
                $condition .=" and marketplace_products.user_id in(".$users.")";
                else
                $condition .="marketplace_products.user_id in(".$users.")";
            }
        }
        if(!empty($request->fda_certified))
        {
            if($request->fda_certified == 1)
            {
                $fdaUsers = User::whereNotNull('fda_no')->get();
                if(count($fdaUsers) > 0)
                {
                    $fdaCertUsers = $fdaUsers->pluck('user_id')->toArray();
                    $fdaUsersIds = join(",", $fdaCertUsers);
                    if($condition != '')
                    $condition .=" and marketplace_products.user_id in(".$fdaUsersIds.")";
                    else
                    $condition .="marketplace_products.user_id in(".$fdaUsersIds.")";
                }
            }
            else
            {
                $fdaUsers = User::whereNull('fda_no')->get();
                if(count($fdaUsers) > 0)
                {
                    $fdaCertUsers = $fdaUsers->pluck('user_id')->toArray();
                    $fdaUsersIds = join(",", $fdaCertUsers);
                    if($condition != '')
                    $condition .=" and marketplace_products.user_id in(".$fdaUsersIds.")";
                    else
                    $condition .="marketplace_products.user_id in(".$fdaUsersIds.")";
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
                    $producersId = $producers->pluck('user_id')->toArray();
                    $joinProducersId = join(",", $producersId);
                    if($condition != '')
                    $condition .=" and marketplace_products.user_id in(".$joinProducersId.")";
                    else
                    $condition .="marketplace_products.user_id in(".$joinProducersId.")";
                }
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
            
        }
        if(!empty($request->sort_by_product))
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
            
        }
        if(!empty($request->rating))
        {
            if($request->rating == 1)
            {
                $avgRating = MarketplaceRating::where('type', '2')->groupBy('id')->orderBy(DB::raw("count(*)"), "DESC")->get();
                
                if(count($avgRating) > 0)
                {
                    $productId = $avgRating->pluck('id')->toArray();
                    $joinproductId = join(",", $productId);
                    if($condition != '')
                    $condition .=" and marketplace_products.marketplace_product_id in(".$joinproductId.")";
                    else
                    $condition .="marketplace_products.marketplace_product_id in(".$joinproductId.")";
                }
            }
            
        }
        
        $getFilterProducts = MarketplaceProduct::with('product_gallery')->whereRaw('('.$condition.')')->paginate(10);
        foreach($getFilterProducts as $key => $product)
        {
            $avgRating = MarketplaceRating::where('type', '2')->where('id', $product->marketplace_store_id)->avg('rating');
            $totalReviews = MarketplaceRating::where('type', '2')->where('id', $product->marketplace_product_id)->count();
            $store = MarketplaceStore::where('marketplace_store_id', $product->marketplace_store_id)->first();

            $logoId = Attachment::where('id', $store->logo_id)->first();
            $bannerId = Attachment::where('id', $store->banner_id)->first();
            $getFilterProducts[$key]->logo_id = $logoId->attachment_url;
            $getFilterProducts[$key]->avg_rating = number_format((float)$avgRating, 1, '.', '');
            $getFilterProducts[$key]->total_reviews = $totalReviews;
            $getFilterProducts[$key]->store_name = $store->name;
        }
        return response()->json(['success' => $this->successStatus,
                                 'data' => $getFilterProducts   
                                ],$this->successStatus); 
    }

     /*
     * Get homepage data
     * 
     */
    public function getHomeScreen()
    {
        $allProducts = MarketplaceProduct::orderBy('marketplace_product_id', 'DESC')->get();
        $allStores = MarketplaceStore::with('logo_id')->where('status', '1')->orderBy('marketplace_store_id', 'DESC')->get();
        $allRegions = State::select('id','name')->where('country_id', 107)->orderBy('name', 'DESC')->get();
        $topBanners = MarketplaceBanner::with('attachment')->where('type', '1')->orderBy('marketplace_banner_id', 'DESC')->get();
        $lowerBanners = MarketplaceBanner::with('attachment')->where('type', '2')->orderBy('marketplace_banner_id', 'DESC')->get();

        if(count($allProducts) > 0)
        {
            foreach($allProducts as $key => $product)
            {
                $avgRating = MarketplaceRating::where('type', '2')->where('id', $product->marketplace_store_id)->avg('rating');
                $totalReviews = MarketplaceRating::where('type', '2')->where('id', $product->marketplace_product_id)->count();
                $store = MarketplaceStore::where('marketplace_store_id', $product->marketplace_store_id)->first();

                $logoId = Attachment::where('id', $store->logo_id)->first();
                $bannerId = Attachment::where('id', $store->banner_id)->first();
                $allProducts[$key]->logo_id = $logoId->attachment_url;
                $allProducts[$key]->avg_rating = number_format((float)$avgRating, 1, '.', '');
                $allProducts[$key]->total_reviews = $totalReviews;
                $allProducts[$key]->store_name = $store->name;
            }
            
        }

        $data = ['top_banners' => $topBanners, 'recently_added_product' => $allProducts, 'newly_added_sore' => $allStores, 'regions' => $allRegions, 'bottom_banners' => $lowerBanners];
        return response()->json(['success' => $this->successStatus,
                                 'data' => $data   
                                ],$this->successStatus); 
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
        		$avgRating = MarketplaceRating::where('type', '2')->where('id', $store->marketplace_store_id)->avg('rating');
                $totalReviews = MarketplaceRating::where('type', '2')->where('id', $store->marketplace_product_id)->count();
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
                $products = MarketplaceProduct::with('product_gallery')->whereIn('user_id', $userIds)->paginate();
                foreach($products as $key => $product)
                {
                    $avgRating = MarketplaceRating::where('type', '2')->where('id', $product->marketplace_store_id)->avg('rating');
                    $totalReviews = MarketplaceRating::where('type', '2')->where('id', $product->marketplace_product_id)->count();
                    $store = MarketplaceStore::where('marketplace_store_id', $product->marketplace_store_id)->first();

                    $products[$key]->avg_rating = number_format((float)$avgRating, 1, '.', '');
                    $products[$key]->total_reviews = $totalReviews;
                    $products[$key]->store_name = $store->name;
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
    	$allRegions = State::select('id','name')->where('country_id', 107)->orderBy('name', 'DESC')->get();
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
}