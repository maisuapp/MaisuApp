<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;


class LangController extends Controller
{
    //
    public function lang_change(Request $request)
    {
       \App::setLocale($request->lang);
//        app()->setLocale($request->lang);
        session()->put('locale', $request->lang);
        return redirect()->back();
    }
    public function setTimezone(Request $request){

        if($request->get('timezone') != Null)
        {
            session()->put('timezone', $request->timezone);
            return true;
        }else{
            session()->forget('timezone');
        }

    }
}
