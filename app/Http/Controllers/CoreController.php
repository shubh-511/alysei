<?php
namespace App\Http\Controllers;
use Illuminate\Routing\Controller;
use Lang;

class CoreController extends Controller
{
    public $userFieldsArray = ['user_id', 'name', 'email','first_name','last_name','middle_name','phone','postal_code','last_login_date','roles'];

    
    public function translate($id, $fallback = null)
    {
    	if (Lang::has($id)) {
    		return trans($id);
    	}else{
    		return $fallback;
    	}

    }
}
