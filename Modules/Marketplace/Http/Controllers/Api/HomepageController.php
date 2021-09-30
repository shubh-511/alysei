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
            	return $this->getAllStores();
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
            	return $this->getProductCategories();
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
     * Get homepage data
     * 
     */
    public function getHomeScreen()
    {
        $allProducts = MarketplaceProduct::orderBy('marketplace_product_id', 'DESC')->get();
        $allStores = MarketplaceStore::with('logo_id')->where('status', '1')->orderBy('marketplace_store_id', 'DESC')->get();
        $allRegions = State::select('id','name')->where('country_id', 107)->orderBy('name', 'DESC')->get();
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
        return response()->json(['success' => $this->successStatus,
                                    'recently_added_product' => $allProducts,
                                    'newly_added_sore' => $allStores,
                                    'regions' => $allRegions,
                                    ],$this->successStatus); 
    }


    /*
     * Get all stores
     * 
     */
    public function getAllStores()
    {
    	$allStores = MarketplaceStore::where('status', '1')->orderBy('marketplace_store_id', 'DESC')->get();
        if(count($allStores) > 0)
        {
        	foreach($allStores as $key => $store)
        	{
        		$avgRating = MarketplaceRating::where('type', '2')->where('id', $product->marketplace_store_id)->avg('rating');
                $totalReviews = MarketplaceRating::where('type', '2')->where('id', $product->marketplace_product_id)->count();
                $store = MarketplaceStore::where('marketplace_store_id', $product->marketplace_store_id)->first();


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
    		$products = MarketplaceProduct::with('product_gallery')->whereIn('marketplace_product_id', $productIds)->get();
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
    		$products = MarketplaceProduct::with('product_gallery')->whereIn('user_id', $userIds)->get();
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