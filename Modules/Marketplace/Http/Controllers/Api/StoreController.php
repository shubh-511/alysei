<?php

namespace Modules\Marketplace\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Attachment;
use Modules\Marketplace\Entities\MarketplaceProduct;
use Modules\Marketplace\Entities\MarketplaceStore;
use Modules\Marketplace\Entities\MarketplaceStoreGallery;
use App\Http\Controllers\CoreController;
use Modules\User\Entities\User;
use App\Http\Traits\UploadImageTrait;
use Illuminate\Support\Facades\Auth; 
use Validator;

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
    public function getDashboardScreen()
    {
        try
        {
            $user = $this->user;

            $myStore = MarketplaceStore::where('user_id', $user->user_id)->first();
            if(!empty($myStore))
            {
                $productCount = MarketplaceProduct::where('user_id', $user->user_id)->count();
                $logoId = Attachment::where('id', $myStore->logo_id)->first();
                $bannerId = Attachment::where('id', $myStore->banner_id)->first();
                $myStore->logo_id = $logoId->attachment_url;
                $myStore->banner_id = $bannerId->attachment_url;
                
                return response()->json(['success' => $this->successStatus,
                                        'banner' => $myStore->banner_id,
                                        'logo' => $myStore->logo_id,
                                        'total_product' => $productCount,
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


    /*
    * Get Store Prefilled values
    *
    */
    public function getPreFilledValues()
    {
        try
        {
            $user = $this->user;
            $userDetail = User::select('company_name','about','phone','email','website','address','state')->with('state:id,name')->where('user_id', $user->user_id)->first();
            
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
                'name' => 'required|max:255', 
                'description' => 'required',
                'website' => 'required|max:255',
                'store_region' => 'required',
                'phone' =>  'required',
                //'location' => 'required|max:255',
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
                $store->phone = $request->phone;
                $store->store_region = $request->store_region;
                $store->location = $request->location;
                $store->lattitude = $request->lattitude;
                $store->longitude = $request->longitude;

                $store->logo_id = $this->uploadImage($request->file('logo_id'));
                $store->banner_id = $this->uploadImage($request->file('banner_id'));
                $store->save();

                $userDetail = User::where('user_id', $user->user_id)->update(['about' => $request->description, 'company_name' => $request->name, 'website' => $request->website, 'phone' => $request->phone, 'address' => $request->location]);

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
                $logoId = Attachment::where('id', $myStore->logo_id)->first();
                $bannerId = Attachment::where('id', $myStore->banner_id)->first();
                $myStore->logo_id = $logoId->attachment_url;
                $myStore->banner_id = $bannerId->attachment_url;

                $galleries = MarketplaceStoreGallery::where('marketplace_store_id', $myStore->marketplace_store_id)->get();
                (count($galleries) > 0) ? $myStore->store_gallery = $galleries : $myStore->store_gallery = [];
                
                return response()->json(['success'=>$this->successStatus,'data' =>$myStore],$this->successStatus); 
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
     * Update Store Details
     * @Params $request
     */
    public function updateStoreDetails(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [ 
                'name' => 'required|max:255', 
                'description' => 'required',
                'website' => 'required|max:255',
                'store_region' => 'required',
                'phone' =>  'required',
                //'location' => 'required|max:255',
                'lattitude' => 'required|max:255',
                'longitude' => 'required|max:255',
                'logo_id' => 'required',
                'banner_id' => 'required',
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
                    $store->logo_id = $this->uploadImage($request->file('logo_id'));    
                }
                if(!empty($request->file('banner_id')))
                {
                    $store->banner_id = $this->uploadImage($request->file('banner_id'));    
                }
                $store->save();

                $userDetail = User::where('user_id', $user->user_id)->update(['about' => $request->description, 'company_name' => $request->name, 'website' => $request->website, 'phone' => $request->phone, 'address' => $request->location]);

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
                $message = "This store is not valid";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }

    }

    
}
