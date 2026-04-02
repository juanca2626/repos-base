<?php

namespace App\Http\Controllers;

use App\Hotel;
use App\Http\Traits\Translations;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class MinorPolicyController extends Controller
{
    use Translations;

    public function __construct()
    {
        // $this->middleware('permission:accounts.read')->only('index');
        // $this->middleware('permission:accounts.create')->only('store');
        // $this->middleware('permission:accounts.update')->only('update');
        // $this->middleware('permission:accounts.delete')->only('delete');
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {

        $lang = $request->input("lang");
        $id = $request->input("id");

        $hotel = Hotel::with([
            'translations' => function ($query) use ($lang) {
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->where('id', $id)->get();

        return Response::json(['success' => true, 'data' => $hotel]);
    }

    public function update(Request $request, $id)
    {
        $allows_child = $request->input('allows_child');
        $allows_teenagers = $request->input('allows_teenagers');
        $hotel = Hotel::find($id);
        if ($allows_child === 'ok') {
            $hotel->allows_child = 1;
        } else {
            $hotel->allows_child = 0;
        }
        if ($allows_teenagers === 'ok') {
            $hotel->allows_teenagers = 1;
        } else {
            $hotel->allows_teenagers = 0;
        }
        $hotel->min_age_child = $request->min_age_child;
        $hotel->max_age_child = $request->max_age_child;
        $hotel->min_age_teenagers = $request->min_age_teenagers;
        $hotel->max_age_teenagers = $request->max_age_teenagers;
        $hotel->save();

        return Response::json(['success' => true]);
    }
}
