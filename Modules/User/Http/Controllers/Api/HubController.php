<?php

namespace Modules\User\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\User\Entities\Hub; 
use Illuminate\Routing\Controller;

class HubController extends Controller
{
    /* 
        Get All Hubs
    */
    public function getHubs(){

        try
        {
            $response_time = (microtime(true) - LARAVEL_START)*1000;

            $hubs = Hub::where('status', '1')->get();
            if(count($hubs) > 0)
            {
                return response()->json(['success'=>true,
                'title' => 'What are hubs?',
                'description' => 'Hubs allow you to connect with other located or working in specific loactions.',
                'hubs' => $hubs]);
            }
            else
            {
                return response()->json(['success'=>true,
                'title' => 'What are hubs?',
                'description' => 'Hubs allow you to connect with other located or working in specific loactions.',
                'message' => "currently no hubs found"]); 
            }
            
        }catch(\Exception $e){
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]]); 
        }

    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('user::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return view('user::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('user::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
