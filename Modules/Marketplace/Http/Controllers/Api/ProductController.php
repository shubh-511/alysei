<?php

namespace Modules\Marketplace\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Attachment;
use Modules\Marketplace\Entities\MarketplaceProduct;
use Modules\Marketplace\Entities\MarketplaceFavourite;
use Modules\Marketplace\Entities\MarketplaceProductGallery;
use Modules\Marketplace\Entities\MarketplaceRating;
use Modules\Marketplace\Entities\MarketplaceStore;
use Modules\Marketplace\Entities\MarketplaceProductCategory;
use Modules\Marketplace\Entities\MarketplaceProductSubcategory;
use Modules\Marketplace\Entities\MarketplaceRecentSearch;
use Modules\Marketplace\Entities\MarketplaceBrandLabel;
use App\Http\Controllers\CoreController;
use App\Http\Traits\UploadImageTrait;
use Illuminate\Support\Facades\Auth; 
use Validator;
use DB;

class ProductController extends CoreController
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
     * Get Product Categories
     * 
     */
    public function getProductCategories($allCategories='')
    {
        try
        {
            $user = $this->user;

            if($allCategories == '')
            {
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
                        $arrayValues[] = ['marketplace_product_category_id'=>$options->user_field_option_id, 'name' => $options->option];
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
            else
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
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

     /*
     * Get Product SubCategories
     * 
     */
    public function getProductSubcategories(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'product_category_id' => 'required'
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $subCategories = MarketplaceProductSubcategory::where('marketplace_product_category_id', $request->product_category_id)->where('status', '1')->get();
            if(count($subCategories) > 0)
            {
                return response()->json(['success' => $this->successStatus,
                                    'count' => count($subCategories),
                                    'data' => $subCategories,
                                    ], $this->successStatus);
            }
            else
            {
                $message = "No product subcategories found";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }
    
    /*
     * Get Brand Labels
     * 
     */
    public function getBrandLabels()
    {
        try
        {
            $user = $this->user;

            $labels = MarketplaceBrandLabel::where('status', '1')->get();
            if(count($labels) > 0)
            {
                return response()->json(['success' => $this->successStatus,
                                    'count' => count($labels),
                                    'data' => $labels,
                                    ], $this->successStatus);
            }
            else
            {
                $message = "No brand labels found";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Save Product Details
     * @Params $request
     */
    public function saveProductDetails(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [ 
                'marketplace_store_id' => 'required', 
                'title' => 'required|max:255',
                'description' => 'required',
                //'keywords' => 'required|max:255',
                'product_category_id' => 'required',
                //'product_subcategory_id' => 'required',
                'quantity_available' => 'required|max:255',
                //'brand_label_id' => 'required|max:255',
                'min_order_quantity' => 'required',
                'handling_instruction' => 'required',
                'dispatch_instruction' => 'required',
                'available_for_sample' => 'required',
                'product_price' => 'required'
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $user = $this->user;

            
            $product = new MarketplaceProduct;
            $product->user_id = $user->user_id;
            $product->marketplace_store_id = $request->marketplace_store_id;
            $product->title = $request->title;
            $product->description = $request->description;
            $product->keywords = $request->keywords;
            $product->product_category_id = $request->product_category_id;
            $product->product_subcategory_id = $request->product_subcategory_id;
            $product->quantity_available = $request->quantity_available;
            $product->brand_label_id = $request->brand_label_id;

            $product->min_order_quantity = $request->min_order_quantity;
            $product->handling_instruction = $request->handling_instruction;
            $product->dispatch_instruction = $request->dispatch_instruction;
            $product->available_for_sample = $request->available_for_sample;
            $product->product_price = $request->product_price;
            $product->save();

            if(!empty($request->gallery_images) && count($request->gallery_images) > 0)
            {
                foreach($request->gallery_images as $images)
                {
                    $attachmentLinkId = $this->postGallery($images, $product->marketplace_product_id, 2);
                }
            }

            return response()->json(['success'=>$this->successStatus,'data' =>$product],$this->successStatus); 

           /* $message = "Your store has already been setup";
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);*/
            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }
    }


    /*
     * Update Product Details
     * @Params $request
     */
    public function updateProductDetails(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [ 
                'marketplace_product_id' => 'required', 
                'title' => 'required|max:255',
                'description' => 'required',
                //'keywords' => 'required|max:255',
                'product_category_id' => 'required',
                //'product_subcategory_id' => 'required',
                'quantity_available' => 'required|max:255',
                //'brand_label_id' => 'required|max:255',
                'min_order_quantity' => 'required',
                'handling_instruction' => 'required',
                'dispatch_instruction' => 'required',
                'available_for_sample' => 'required',
                'product_price' => 'required'
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $user = $this->user;

            
            $product = MarketplaceProduct::where('marketplace_product_id', $request->marketplace_product_id)->first();
            if(!empty($product))
            {
                $product->title = $request->title;
                $product->description = $request->description;
                $product->keywords = $request->keywords;
                $product->product_category_id = $request->product_category_id;
                $product->product_subcategory_id = $request->product_subcategory_id;
                $product->quantity_available = $request->quantity_available;
                $product->brand_label_id = $request->brand_label_id;

                $product->min_order_quantity = $request->min_order_quantity;
                $product->handling_instruction = $request->handling_instruction;
                $product->dispatch_instruction = $request->dispatch_instruction;
                $product->available_for_sample = $request->available_for_sample;
                $product->product_price = $request->product_price;
                $product->save();

                $existingGalleries = MarketplaceProductGallery::where('marketplace_product_id', $product->marketplace_product_id)->get();
                if(count($existingGalleries) > 0)
                {
                    foreach($existingGalleries as $existingGallery)
                    {
                        unlink('/home/ibyteworkshop/alyseiapi_ibyteworkshop_com/'.$existingGallery->attachment_url);
                        MarketplaceProductGallery::where('marketplace_product_gallery_id',$existingGallery->marketplace_product_gallery_id)->delete();
                    }
                }
                

                if(!empty($request->gallery_images) && count($request->gallery_images) > 1)
                {
                    foreach($request->gallery_images as $images)
                    {
                        $attachmentLinkId = $this->postGallery($images, $product->marketplace_product_id, 2);
                    }
                }
                $galleries = MarketplaceProductGallery::where('marketplace_product_id', $product->marketplace_product_id)->get();
                (count($galleries) > 0) ? $product->product_gallery = $galleries : $product->product_gallery = [];

                return response()->json(['success'=>$this->successStatus,'data' =>$product],$this->successStatus); 
            }
            else
            {
                $message = "This product does not exist";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }
    }


    /*
     * Get my product list
     * 
     */
    public function getMyProductList()
    {
        try
        {
            $user = $this->user;
            $productCount = MarketplaceProduct::with('labels')->where('user_id', $user->user_id)->count();
            $myProductLists = MarketplaceProduct::with('labels')->where('user_id', $user->user_id)->paginate(10);
            if(count($myProductLists))
            {
                foreach($myProductLists as $key => $myProductList)
                {
                    $options = DB::table('user_field_options')
                                ->where('head', 0)->where('parent', 0)
                                ->where('user_field_option_id', $myProductList->product_category_id)
                                ->first();
                    $myProductLists[$key]->product_category_name = $options->option;
                                              
                    $galleries = MarketplaceProductGallery::where('marketplace_product_id', $myProductList->marketplace_product_id)->get();
                    (count($galleries) > 0) ? $myProductLists[$key]->product_gallery = $galleries : $myProductLists[$key]->product_gallery = [];

                    $avgRating = MarketplaceRating::where('type', '2')->where('id', $myProductList->marketplace_product_id)->avg('rating');
                    $totalReviews = MarketplaceRating::where('type', '2')->where('id', $myProductList->marketplace_product_id)->count();

                    $myProductLists[$key]->avg_rating = $avgRating;
                    $myProductLists[$key]->total_reviews = $totalReviews;
                }
                return response()->json(['success'=>$this->successStatus, 'count' => $productCount, 'data' =>$myProductLists],$this->successStatus); 
            }
            else
            {
                $message = "No product list found";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }

    }

    /*
     * Get all product list
     * 
     */
    public function getSearchProductListing(Request $request)
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

            return $this->applyFiltersToProductSearch($request);

            $productLists = MarketplaceProduct::with('labels')->where('title', 'LIKE', '%' . $request->keyword . '%')->where('status', '1')->paginate(10);  

            if(count($productLists) > 0)
            {

                foreach($productLists as $key => $myProductList)
                {
                    $options = DB::table('user_field_options')
                                ->where('head', 0)->where('parent', 0)
                                ->where('user_field_option_id', $myProductList->product_category_id)
                                ->first();
                    if(!empty($options))
                    {
                        $productLists[$key]->product_category_name = $options->option;
                    }
                    else
                    {
                        $productLists[$key]->product_category_name = '';
                    }
                
                    $storeName = MarketplaceStore::where('marketplace_store_id', $myProductList->marketplace_store_id)->first();
                                              
                    $galleries = MarketplaceProductGallery::where('marketplace_product_id', $myProductList->marketplace_product_id)->get();
                    (count($galleries) > 0) ? $productLists[$key]->product_gallery = $galleries : $productLists[$key]->product_gallery = [];

                    $avgRating = MarketplaceRating::where('type', '2')->where('id', $myProductList->marketplace_product_id)->avg('rating');
                    $totalReviews = MarketplaceRating::where('type', '2')->where('id', $myProductList->marketplace_product_id)->count();

                    $productLists[$key]->avg_rating = $avgRating;
                    $productLists[$key]->total_reviews = $totalReviews;

                    $productLists[$key]->store_name = $storeName->name;
                }
                return response()->json(['success' => $this->successStatus,
                                            'count' => count($productLists),
                                            'data' => $productLists,
                                            ], $this->successStatus);
            }
            else
            {
                $message = "No products found";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }

    }


    /*
    Apply filters
    */
    public function applyFiltersToProductSearch($request)
    {
        $condition = '';

        if(isset($request->available_for_sample))
        {
            if($request->available_for_sample == 1)
            {
                if($condition != '')
                $condition .=" and marketplace_products.available_for_sample = 'Yes'";
                else
                $condition .="marketplace_products.available_for_sample = 'Yes'";
            }
            elseif($request->available_for_sample == 0)
            {
                if($condition != '')
                $condition .=" and marketplace_products.available_for_sample = 'No'";
                else
                $condition .="marketplace_products.available_for_sample = 'No'";
            }
            
        }
        if(!empty($request->category))
        {
            if($condition != '')
                $condition .=" and marketplace_products.product_category_id in(".$request->category.")";
            else
                $condition .="marketplace_products.product_category_id in(".$request->category.")";
        }
        if(!empty($request->price_from))
        {
            if(!empty($request->price_to))
            {
                if($condition != '')
                $condition .=" and marketplace_products.product_price BETWEEN ".$request->price_from." AND ".$request->price_to;
                else
                $condition .="marketplace_products.product_price BETWEEN ".$request->price_from." AND ".$request->price_to;    
            }
            else
            {
                if($condition != '')
                $condition .=" and marketplace_products.product_price >= ".$request->price_from;
                else
                $condition .="marketplace_products.product_price >= ".$request->price_from;
            }
            
        }
        
        if(!empty($request->sort_by))
        {
            //1=popularity, 2=ratings, 3=price lowtohigh, 4=price hightolow, 5=new first
            if($request->sort_by == 1)
            {
                if($condition != '')
                $condition .=" and marketplace_products.status = '1'";
                else
                $condition .="marketplace_products.status = '1'";
                //$productLists = MarketplaceProduct::with('labels')->where('status', '1')->get();    
            }
            elseif($request->sort_by == 2)
            {
                if($condition != '')
                $condition .=" and marketplace_products.status = '1'";
                else
                $condition .="marketplace_products.status = '1'";
                //$productLists = MarketplaceProduct::with('labels')->where('status', '1')->get();
            }
            elseif($request->sort_by == 3)
            {
                if($condition != '')
                $condition .=" and marketplace_products.status = '1' order by product_price ASC";
                else
                $condition .="marketplace_products.status = '1' order by product_price ASC";
                //$productLists = MarketplaceProduct::with('labels')->where('status', '1')->orderBy('product_price', 'ASC')->get();
            }
            elseif($request->sort_by == 4)
            {
                if($condition != '')
                $condition .=" and marketplace_products.status = '1' order by product_price DESC";
                else
                $condition .="marketplace_products.status = '1' order by product_price DESC";
                //$productLists = MarketplaceProduct::with('labels')->where('status', '1')->orderBy('product_price', 'DESC')->get();
            }
            elseif($request->sort_by == 5)
            {
                if($condition != '')
                $condition .=" and marketplace_products.status = '1' order by marketplace_product_id DESC";
                else
                $condition .="marketplace_products.status = '1' order by marketplace_product_id DESC";
                //$productLists = MarketplaceProduct::with('labels')->where('status', '1')->orderBy('marketplace_product_id', 'DESC')->get();
            }
            else
            {
                $message = "No products found";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
        }


        if($condition == '')
        {
            $productLists = MarketplaceProduct::with('labels')->where('title', 'LIKE', '%' . $request->keyword . '%')->where('status', '1')->get();    
        }
        else
        {
            //$productLists = MarketplaceProduct::with('labels')->where('title', 'LIKE', '%' . $request->keyword . '%')->whereRaw(''.$condition.'')->get();    
            $productLists = DB::table('marketplace_products')
                     //->with('labels')
                     //->select(DB::raw('count(*) as user_count, status'))
                     ->where('title', 'LIKE', '%' . $request->keyword . '%')
                     ->whereRaw(''.$condition.'')->get();  
        }

        if(count($productLists) > 0)
        {
            foreach($productLists as $key => $myProductList)
            {
                $options = DB::table('user_field_options')
                            ->where('head', 0)->where('parent', 0)
                            ->where('user_field_option_id', $myProductList->product_category_id)
                            ->first();

                if(!empty($options))
                {
                    $productLists[$key]->product_category_name = $options->option;
                }
                else
                {
                    $productLists[$key]->product_category_name = '';
                }            
                $storeName = MarketplaceStore::where('marketplace_store_id', $myProductList->marketplace_store_id)->first();
                                          
                $galleries = MarketplaceProductGallery::where('marketplace_product_id', $myProductList->marketplace_product_id)->get();
                (count($galleries) > 0) ? $productLists[$key]->product_gallery = $galleries : $productLists[$key]->product_gallery = [];

                $avgRating = MarketplaceRating::where('type', '2')->where('id', $myProductList->marketplace_product_id)->avg('rating');
                $totalReviews = MarketplaceRating::where('type', '2')->where('id', $myProductList->marketplace_product_id)->count();

                $productLists[$key]->avg_rating = $avgRating;
                $productLists[$key]->total_reviews = $totalReviews;

                $productLists[$key]->store_name = $storeName->name;
            }
            return response()->json(['success' => $this->successStatus,
                                        'count' => count($productLists),
                                        'data' => $productLists,
                                        ], $this->successStatus);
        }
        else
        {
            $message = "No products found";
            return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
        }
    }


    /*
     * Delete product
     * @Params $request
     */
    public function deleteProduct(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'marketplace_product_id' => 'required'
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $product = MarketplaceProduct::where('marketplace_product_id', $request->marketplace_product_id)->where('user_id', $user->user_id)->first();
            
            if(!empty($product))
            {
                $product->delete();
                $message = "Product deleted successfully";
                return response()->json(['success'=>$this->successStatus, 'message' => $message],$this->successStatus); 
            }
            else
            {
                $message = "No product found";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }

    }

    /*
     * Search Product
     * 
    */
    public function searchProduct(Request $request)
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
            
            return $this->getSearchProductList($request, $user);    
                 
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Get Recent Search Product
     * 
    */
    public function recentSearchProduct()
    {
        try
        {
            $user = $this->user;

            $recentSearch = MarketplaceRecentSearch::where('user_id', $user->user_id)->orderBy('marketplace_recent_search_id', 'DESC')->get();
            
            if(count($recentSearch) > 0)
            {
                
                return response()->json(['success'=>$this->successStatus, 'data' => $recentSearch],$this->successStatus); 
            }
            else
            {
                $message = "No recent search found";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

     /*
     * Get product detail
     * 
     */
    public function getProductDetail(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'marketplace_product_id' => 'required'
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }
            
            $productDetail = MarketplaceProduct::with('product_gallery')->with('labels')->where('marketplace_product_id', $request->marketplace_product_id)->first();
            if(!empty($productDetail))
            {
                $options = DB::table('user_field_options')
                            ->where('head', 0)->where('parent', 0)
                            ->where('user_field_option_id', $productDetail->product_category_id)
                            ->first();
                $productDetail->product_category_name = $options->option;
                $storeName = MarketplaceStore::where('marketplace_store_id', $productDetail->marketplace_store_id)->first();
                $logoId = Attachment::where('id', $storeName->logo_id)->first();
                $storeName->store_logo = $logoId->attachment_url;
                $productDetail->store_logo = $logoId->attachment_url;

                $avgRating = MarketplaceRating::where('type', '2')->where('id', $productDetail->marketplace_product_id)->avg('rating');
                $totalReviews = MarketplaceRating::where('type', '2')->where('id', $productDetail->marketplace_product_id)->count();

                $oneStar = MarketplaceRating::where('type', '2')->where('id', $productDetail->marketplace_product_id)->where('rating', 1)->count();
                $twoStar = MarketplaceRating::where('type', '2')->where('id', $productDetail->marketplace_product_id)->where('rating', 2)->count();
                $threeStar = MarketplaceRating::where('type', '2')->where('id', $productDetail->marketplace_product_id)->where('rating', 3)->count();
                $fourStar = MarketplaceRating::where('type', '2')->where('id', $productDetail->marketplace_product_id)->where('rating', 4)->count();
                $fiveStar = MarketplaceRating::where('type', '2')->where('id', $productDetail->marketplace_product_id)->where('rating', 5)->count();

                $isfavourite = MarketplaceFavourite::where('user_id', $user->user_id)->where('favourite_type', '2')->where('id', $productDetail->marketplace_product_id)->first();

                $productDetail->avg_rating = $avgRating;
                $productDetail->total_reviews = $totalReviews;

                $productDetail->total_one_star = $oneStar;
                $productDetail->total_two_star = $twoStar;
                $productDetail->total_three_star = $threeStar;
                $productDetail->total_four_star = $fourStar;
                $productDetail->total_five_star = $fiveStar;

                $productDetail->is_favourite = (!empty($isfavourite)) ? 1 : 0;
                $productDetail->store_detail = $storeName;
                                          
                /*$galleries = MarketplaceProductGallery::where('marketplace_product_id', $productDetail->marketplace_product_id)->get();
                (count($galleries) > 0) ? $productDetail->product_gallery = $galleries : $productDetail->product_gallery = [];*/

                $relatedProducts = MarketplaceProduct::with('product_gallery')->with('labels')->where('product_category_id', $productDetail->product_category_id)->get();

                $data = ['product_detail' => $productDetail, 'related_products' => $relatedProducts];

                return response()->json(['success' => $this->successStatus,
                                        'data' => $data,
                                        ], $this->successStatus);
                
            }
            else
            {
                $message = "Invalid product Id";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
            
                 
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }


    /*
    Search product list
    */
    public function getSearchProductList($request, $user)
    {
        
        $productLists = MarketplaceProduct::select('marketplace_product_id','title','product_category_id')->where('title', 'LIKE', '%' . $request->keyword . '%')->where('status', '1')->get();    

        $recentSearch = new MarketplaceRecentSearch; 
        $recentSearch->user_id = $user->user_id;
        $recentSearch->search_keyword = $request->keyword;
        $recentSearch->save();
        
        if(count($productLists) > 0)
        {
            foreach($productLists as $key => $productList)
            {
                $options = DB::table('user_field_options')
                            ->where('head', 0)->where('parent', 0)
                            ->where('user_field_option_id', $productList->product_category_id)
                            ->first();
                $productLists[$key]->product_category_name = (!empty($options->option)) ? $options->option : "";
            }
            return response()->json(['success' => $this->successStatus,
                                        'count' => count($productLists),
                                        'data' => $productLists,
                                        ], $this->successStatus);
        }
        else
        {
            $message = "No products found";
            return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
        }
        
    }
    
}
