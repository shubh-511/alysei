<?php

namespace Modules\Recipe\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Recipe\Entities\RecipeIngredient; 
use App\Http\Traits\UploadImageTrait;
use Validator;

class IngredientsController extends Controller
{
    use UploadImageTrait;
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $ingredients = RecipeIngredient::with('attachment')->paginate('10');
        return view('recipe::ingredients.index',compact('ingredients'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        $ingredients = RecipeIngredient::select("title","recipe_ingredient_id")->where('parent',0)->get();
        return view('recipe::ingredients.create',compact('ingredients'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        try
        {   
            $validator = Validator::make($request->all(), [ 
                    'name' => 'required',
                    'image' => 'required'
                ]);

            if ($validator->fails()) { 

                return redirect()->back()->with('error', $validator->errors()->first());   
            }

            $newIngredient = new RecipeIngredient;
            $newIngredient->image_id = $this->uploadImage($request->file('image'));
            $newIngredient->title = $request->name;
            $newIngredient->name = strtolower(str_replace(' ', '_', $request->title));
            $newIngredient->parent = isset($request->parent) ? 0 : $request->parent_id;
            $newIngredient->featured = isset($request->featured) ? 1 : 0;
            $newIngredient->save();

            $message = "ingredient added successfuly";
            return redirect()->back()->with('success', $message); 

        }catch(\Exception $e)
        {
            dd($e->getMessage());
            //return redirect()->back()->with('error', "Something went wrong");   
            //return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }

    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return view('recipe::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('recipe::edit');
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
