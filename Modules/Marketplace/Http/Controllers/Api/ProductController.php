<?php

namespace Modules\Marketplace\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Attachment;
use Modules\Marketplace\Entities\MarketplaceProduct;
use Modules\Marketplace\Entities\MarketplaceProductGallery;
use App\Http\Controllers\CoreController;
use App\Http\Traits\UploadImageTrait;

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
                'keywords' => 'required|max:255',
                'product_category_id' => 'required',
                'product_subcategory_id' => 'required',
                'quantity_available' => 'required|max:255',
                'brand_label_id' => 'required|max:255',
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

    
}
