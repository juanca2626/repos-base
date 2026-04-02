<?php

namespace App\Http\Controllers;

use App\Country;
use App\Doctype;
use Illuminate\Http\Request;

class ContentsController extends Controller
{

    public function index(Request $request)
    {
        $lang = $request->__get("lang");
        $filters = $request->__get("filters");


        $response = [
            'countries' => [],
            'doctypes' => []
        ];

        if (!$filters or (is_array($filters) and isset($filters['countries']))) {
            $countries = Country::with([
                'translations' => function ($query) use ($lang) {
                    $query->whereHas('language', function ($q) use ($lang) {
                        $q->where('iso', $lang);
                    });
                }
            ])->get();
            $response['countries'] = $countries;
        }

        if (!$filters or (is_array($filters) and isset($filters['doctypes']))) {
            $doctypes = Doctype::with([
                'translations' => function ($query) use ($lang) {
                    $query->whereHas('language', function ($q) use ($lang) {
                        $q->where('iso', $lang);
                    });
                }
            ])->get();

            $response['doctypes'] = $doctypes;
        }


        return response()->json($response);
    }
}
