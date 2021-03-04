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
use App\Attachment;
use DB;
use Cache;
use App\Http\Traits\UploadImageTrait;

class FeaturedListingsController extends CoreController
{
    use UploadImageTrait;
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

                $fieldsTypes = $this->getFeaturedListingTypes($this->user->role_id);
                
                foreach($fieldsTypes as $fieldsTypesKey => $fieldsTypesValue){
                    //dd($this->user->user_id);
                    $featuredListing[$fieldsTypesValue->title] = FeaturedListing::with('image')
                                        ->where('user_id', $this->user->user_id)
                                        ->where('featured_listing_type_id', $fieldsTypesValue->featured_listing_type_id)
                                        ->orderBy('featured_listing_id','DESC')->get(); 
                    
                }
                
                //Get Featured Listing Fields

                //Get Featured Type
                $featuredTypes = $this->getFeaturedListingFieldsByRoleId($this->user->role_id);
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

                //END
                $data = ['user_settings'=>$userDetails,'featured_listing_fields'=> $fieldsData,'products' => $featuredListing];
                return response()->json(['success' => $this->successStatus,
                                       'data' => $data                      
                                
                                ], $this->successStatus);

            }catch(\Exception $e){

                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
            }
    }

    /*
     *
     */
    public function postFeaturedListing(Request $request){
        try{
                $stateId='';
                $input = $request->all();
                $rules = [];
                $rules['featured_listing_type_id'] = 'required';
                $validator = Validator::make($input, $rules);

                if ($validator->fails()) { 
                    return response()->json(['success'=>$this->validationStatus,'errors'=>$validator->errors()->first()], $this->validationStatus);
                }

                $featuredListingFields = $this->getFeaturedListingFields($this->user->role_id,$input['featured_listing_type_id']);

                if(count($featuredListingFields) == 0){
                    return response()->json(['success'=>$this->validationStatus,'errors' =>'Sorry,There are no fields for current role_id'], $this->validationStatus);
                }else{

                    $rules = $this->makeValidationRules($featuredListingFields);
                    $inputData = $this->segregateInputData($input,$featuredListingFields);
                }

                
                if(!empty($rules) && !empty($inputData)){
                
                    $validator = Validator::make($inputData, $rules);

                    if ($validator->fails()) { 

                        return response()->json(['success'=>$this->validationStatus,'errors'=>$validator->errors()->first()], $this->validationStatus);
                    }

                    if(array_key_exists('title',$inputData) && 
                       array_key_exists('description',$inputData) && 
                       array_key_exists('featured_listing_type_id',$input)
                    ){

                        $featuredListingData = [];
                        $featuredListingData['title'] = strip_tags($inputData['title']);
                        $featuredListingData['description'] = strip_tags($inputData['description']);
                        $featuredListingData['user_id'] = $this->user->user_id;
                        $featuredListingData['featured_listing_type_id'] = $input['featured_listing_type_id'];
                        //$featuredListingData['image_id'] = $this->uploadImage($inputData['image_id']);

                        $featuredListing = FeaturedListing::create($featuredListingData);
                            
                        unset($input["featured_listing_type_id"]);

                        foreach ($input as $key => $value) {
                            $data = [];
                            if($key == 1)
                            {
                                $value = $this->uploadImage($key);
                            }
                            $data['featured_listing_field_id'] = $key;
                            $data['user_id'] = $this->user->user_id;
                            $data['value'] = $value;

                            DB::table('featured_listing_values')->insert($data);
                        }

                        $mes = 'Successfully added';
                        $message = $this->translate('messages.'.$mes,$mes);

                        return response()->json(['success' => $this->successStatus,
                                        'message' => $message,
                                    ], $this->successStatus);

                    }
                }else{
                    return response()->json(['success'=>$this->validationStatus,'errors'=>"parameters are missing"], $this->validationStatus);
                }

                //dd($fields);

        }catch(\Exception $e){
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()], $this->exceptionStatus); 
        }    
    }
    /*
     * Get Featured Listing Fields Using Role Id
     * @params $roleId
     */
    public function getFeaturedListingFieldsByRoleId($roleId){
        
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

    /*
     * Get Featured Type Using Role Id
     * @params $roleId
     */
    public function getFeaturedListingTypes($roleId){
        $featuredTypes = DB::table("featured_listing_types as flt")
            ->join("featured_listing_type_role_maps as fltrm", 'fltrm.featured_listing_type_id', '=', 'flt.featured_listing_type_id')

            ->where("fltrm.role_id","=",$roleId)
            ->get();

        return $featuredTypes;
    }

    /*
     * Get Fields 
     * @params $roleId and $featuredListingTypeId
     */
    public function getFeaturedListingFields($roleId,$featuredListingTypeId){
        $featuredListingFields = DB::table("featured_listing_field_role_maps as flfrm")
            ->join("featured_listing_fields as flf", 'flf.featured_listing_field_id', '=', 'flfrm.featured_listing_field_id')

            ->where("flfrm.role_id","=",$roleId)
            ->where("flfrm.featured_listing_type_id","=",$featuredListingTypeId)
            ->get();

        return $featuredListingFields;
    }

    /*
     * Make Validation Rules
     * @Params $featuredListingFields
     */

    public function makeValidationRules($featuredListingFields){
        $rules = [];
        foreach ($featuredListingFields as $key => $field) {
            
            if($field->name == 'email' && $field->required == 'yes'){

                $rules[$field->name] = 'required|email|unique:users|max:50';

            }else if($field->name == 'password' && $field->required == 'yes'){

                $rules[$field->name] = 'required|min:8';

            }else if($field->name == 'first_name' && $field->required == 'yes'){

                $rules[$field->name] = 'required|min:3';

            }else if($field->name == 'last_name' && $field->required == 'yes'){

                $rules[$field->name] = 'required|min:3';

            }else if($field->type == 'file'){

            }else {

                if($field->required == 'yes'){
                    $rules[$field->name] = 'required|max:100';
                }
            }
        }

        return $rules;

    }

    /*
     * Segregate user input data
     * @Params $input and @featuredListingFields
     */
    public function segregateInputData($input,$featuredListingFields){
        $inputData = [];

        foreach($featuredListingFields as $key => $field){
            if(array_key_exists($field->featured_listing_field_id, $input)){
                $inputData[$field->name] = $input[$field->featured_listing_field_id];
            }
        }

        return $inputData;

    }
}
