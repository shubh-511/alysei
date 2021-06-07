<?php

namespace Modules\Marketplace\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Attachment;
use Modules\Marketplace\Entities\MarketplaceStore;
use Modules\Marketplace\Entities\MarketplaceStoreGallery;
use App\Http\Controllers\CoreController;
use App\Http\Traits\UploadImageTrait;

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
     * Save Store Details
     * @Params $request
     */
    public function saveStoreDetails(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [ 
                'name' => 'required|max:255', 
                'description' => 'required',
                'website' => 'required|max:255',
                'store_region' => 'required',
                'location' => 'required|max:255',
                'lattitude' => 'required|max:255',
                'longitude' => 'required|max:255',
                'logo_id' => 'required',
                'banner_id' => 'required',
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $user = $this->user;

            $myStore = MarketplaceStore::where('user_id', $user->user_id)->first();
            if(empty($myStore))
            {
                $store = new MarketplaceStore;
                $store->user_id = $user->user_id;
                $store->name = $request->name;
                $store->description = $request->description;
                $store->website = $request->website;
                $store->store_region = $request->store_region;
                $store->location = $request->location;
                $store->lattitude = $request->lattitude;
                $store->longitude = $request->longitude;

                $store->logo_id = $this->uploadImage($request->file('logo_id'));
                $store->banner_id = $this->uploadImage($request->file('banner_id'));
                $store->save();

                if(!empty($request->gallery_images) && count($request->gallery_images) > 0)
                {
                    foreach($request->gallery_images as $images)
                    {
                        $attachmentLinkId = $this->postGallery($images, $store->marketplace_store_id, 1);
                    }
                }

                return response()->json(['success'=>$this->successStatus,'data' =>$store],$this->successStatus); 
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

    
}
