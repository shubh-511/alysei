<?php

namespace Modules\User\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Http\Controllers\CoreController;
use Modules\User\Entities\Role;
use Modules\User\Entities\FeaturedListing;
use Illuminate\Support\Facades\Auth; 
use Modules\User\Entities\User; 
use Validator;
use App\Image;
use DB;
use Cache;
use App\Http\Traits\UploadImageTrait;

class FeaturedListingsController extends CoreController
{
    public $successStatus = 200;
    public $validationStatus = 422;
    public $exceptionStatus = 409;

    public $user = '';

    public function __construct(){

        $this->middleware(function ($request, $next) {

            $this->user = Auth::user();
            return $next($request);
        });
    }

    /* 
     * User Settings
     */
    public function userSettings(){
        try{
                $userDetails = $this->user->only(['name', 'email','display_name','locale']);

                $userFieldInfo = [];

                // foreach($userDetails as $key => $user){

                //     $userFieldInfo[] = ["title" => $this->translate("messages.".$key,$key),"value"=>$user];
                // }

                //Get Featured Listing Fields

                //Get Featured Type
                $featuredTypes = $this->getFeaturedTypeByRoleId($this->user->role_id);
                $fieldsData = [];
                foreach ($featuredTypes as $key => $value) {


                    $value->title = $this->translate('messages.'.$value->title,$value->title);

                    $value->options = $this->getFeaturedListingFieldOptionParent($value->featured_listing_field_id);

                    if(!empty($value->options)){
                        foreach ($value->options as $k => $oneDepth) {

                                $value->options[$k]->option = $this->translate('messages.'.$oneDepth->option,$oneDepth->option);
                            }
                    }

                    $fieldsData[$value->featured_listing_type_title][] = $value;
                }

                return response()->json(['success' => $this->successStatus,
                                 "user_settings"=>$userDetails,'featured_listing_type_title'=> $fieldsData
                                ], $this->successStatus);
                //END

                return response()->json(['success' => $this->successStatus,
                                 'data' => [$userDetails]
                                ], $this->successStatus);

            }catch(\Exception $e){

                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
            }
    }

    /*
     * Get Featured Type Using Role Id
     * @params $roleId
     */
    public function getFeaturedTypeByRoleId($roleId){
        
        $featuredTypes = DB::table("featured_listing_types as flt")
            ->select("flt.title as featured_listing_type_title","flfrm.*","fltrm.*","flf.*")
            ->join("featured_listing_type_role_maps as fltrm", 'fltrm.featured_listing_type_id', '=', 'flt.featured_listing_type_id')

            ->join("featured_listing_field_role_maps as flfrm",function ($join) {
                $join->on('flfrm.featured_listing_type_id', '=', 'fltrm.featured_listing_type_id');
                $join->on('flfrm.featured_listing_type_id','=','flt.featured_listing_type_id');
            }) 

            ->join("featured_listing_fields as flf", 'flf.featured_listing_field_id', '=', 'flfrm.featured_listing_field_id')

            ->where("fltrm.role_id","=",$roleId)
            ->where("flfrm.role_id","=",$roleId)
            ->get();

        return $featuredTypes;
    }

    /*
     * Get All Fields Option who are child
     * @params $featured_listing_field_id 
    */
    public function getFeaturedListingFieldOptionParent($fieldId){

        $fieldOptionData = [];
        
        if($fieldId > 0){
            $fieldOptionData = DB::table('featured_listing_field_options')
                    ->where('featured_listing_field_id','=',$fieldId)
                    ->where('parent','=',0)
                    ->get();

            foreach ($fieldOptionData as $key => $option) {
                $fieldOptionData[$key]->option = $this->translate('messages.'.$option->option,$option->option);
            }
        }
        
        return $fieldOptionData;    
        
    }
}
