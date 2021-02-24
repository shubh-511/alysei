<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Page;

class PageController extends Controller
{
    /***
    Privacy Policy
    ***/
    public function privacyAndPolicy()
    {
    	$privacyContent = Page::where('id', 1)->where('status', '1')->first();
    	return view('static_pages.privacy',compact('privacyContent'));
    }
}
