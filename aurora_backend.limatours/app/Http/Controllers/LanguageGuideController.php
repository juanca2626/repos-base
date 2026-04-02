<?php

namespace App\Http\Controllers;

use App\LanguageServiceGuide;
use Illuminate\Http\Request;

class LanguageGuideController extends Controller
{
    public function add(Request $request)
    {
        $language_guide = new LanguageServiceGuide();
        $language_guide->language_id = $request->post('language_id');
        $language_guide->service_id = $request->post('service_id');
        $language_guide->save();

        return response()->json("idioma de guia guardado");
    }

    public function delete(Request $request)
    {
        $language_guide = LanguageServiceGuide::where('service_id', $request->post('service_id'))->where('language_id', $request->post('language_id'))->first();
        $language_guide->delete();

        return response()->json("idioma de guia eliminado");
    }
}
