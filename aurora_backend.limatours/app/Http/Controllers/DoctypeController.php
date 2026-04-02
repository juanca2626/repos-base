<?php

namespace App\Http\Controllers;

use App\Doctype; 
use App\Language; 
use Illuminate\Http\Request; 

class DoctypeController extends Controller
{
 
    public function list(Request $request)
    {
        $lang = session()->get('lang');
        if (empty($lang)) {
            $lang = $request->input('lang');
        }
        $language = Language::where('iso', $lang)->first();
        $doctypes = Doctype::with([
            'translations' => function ($query) use ($language) {
                $query->where('language_id', $language->id);
            }
        ])->get();

        return response()->json(['success' => true, 'data' => $doctypes]);
    }
 
}
