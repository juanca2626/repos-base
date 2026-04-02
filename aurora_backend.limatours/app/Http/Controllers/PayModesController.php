<?php

namespace App\Http\Controllers;

use App\Language;
use App\PayMode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class PayModesController extends Controller
{
    public function index(Request $request)
    {
        $lang = strtolower($request->input('lang'));
        $language_id = Language::select('id')->where('iso',$lang)->first()->id;

        $data_pay_modes = PayMode::with(['translations'=>function($query) use($language_id){

            $query->where('language_id',$language_id);

        }])->get();

        return Response::json(['success' => true, 'data'=> $data_pay_modes ]);
    }
}
