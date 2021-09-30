<?php

namespace Modules\Marketplace\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Attachment;
use Modules\Marketplace\Entities\WalkthroughScreen;
use App\Http\Controllers\CoreController;
use Illuminate\Support\Facades\Auth; 

class WalkthroughScreenController extends CoreController
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
     * Get Walk Through Screens
     * @Params $request
     */
    public function getWalkThroughScreens()
    {
        try
        {
            $response_time = (microtime(true) - LARAVEL_START)*1000;
            $screens = WalkthroughScreen::select('title','description','order','image_id')
                        ->orderBy('order','asc')->get();

            foreach ($screens as $key => $screen) {
                $screens[$key]->title = $this->translate('messages.'.$screen->title,$screen->title);
                $screens[$key]->description = $this->translate('messages.'.$screen->description,$screen->description);
                $attachment = Attachment::where('id', $screen->image_id)->first();
                if(!empty($attachment))
                {
                    $screens[$key]->image_id = $attachment->attachment_url;
                }
                else
                {
                    $screens[$key]->image_id = '';
                }
            }

            return response()->json(['success'=>$this->successStatus,'data' =>$screens,'response_time'=>$response_time],$this->successStatus); 

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }

    }

    
}
