<?php

namespace App\Http\Controllers;

use App\HotelInclusionChildren;
use App\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class HotelInclusionChildrenController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $hotel_id)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $querySearch = $request->input('query');
        $lang = $request->input('lang');
        $language = Language::where('iso', $lang)->first();
        if (!empty($hotel_id)) {
            $inclusion = HotelInclusionChildren::with([
                'inclusions.translations' => function ($query) use ($language) {
                    $query->where('type', 'inclusion');
                    $query->where('language_id', $language->id);
                }
            ])->where('hotel_id', $hotel_id);

            if ($querySearch) {
                $inclusion->where(function ($query) use ($querySearch) {
                    $query->orWhere('name', 'like', '%'.$querySearch.'%');
                });
            }
            $count = $inclusion->count();
            if ($paging === 1) {
                $inclusion = $inclusion->take($limit)->orderBy('order')->get();
            } else {
                $inclusion = $inclusion->skip($limit * ($paging - 1))->take($limit)->orderBy('order')->get();
            }
        } else {
            $inclusion = [];
            $count = 0;
        }

        $data = [
            'data' => $inclusion,
            'count' => $count,
            'success' => true
        ];

        return Response::json($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'inclusion_id' => 'required',
            'hotel_id' => 'required',
            'include' => 'required',
        ]);
        $validateInclusion = HotelInclusionChildren::where('hotel_id', $request->input('hotel_id'))
            ->where('inclusion_id', $request->input('inclusion_id'))
            ->count();
        if ($validator->fails() or $validateInclusion > 0) {
            return Response::json(['success' => false, 'error' => 'validation_duplicate']);
        } else {
            $childrenInclusionOrder = HotelInclusionChildren::where('hotel_id', $request->input('hotel_id'))
                ->orderBy('order')
                ->max('order');
            $childrenInclusion = new HotelInclusionChildren();
            $childrenInclusion->hotel_id = $request->input('hotel_id');
            $childrenInclusion->inclusion_id = $request->input('inclusion_id');
            $childrenInclusion->include = $request->input('include');
            $childrenInclusion->order = $childrenInclusionOrder + 1;
            $childrenInclusion->save();
        }

        return Response::json(['success' => true]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\HotelInclusionChildren  $hotelInclusionChildren
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $childrenInclusion = HotelInclusionChildren::find($id);
            $childrenInclusion->delete();
            return Response::json(['success' => true]);
        } catch (\Exception $e) {
            return Response::json(['success' => false, 'message' => $e]);
        }
    }


    public function upService(Request $request)
    {
        $hotel_children_inclusion_id = $request->input('hotel_children_inclusion_id');
        $order = $request->input('order');

        $service_up = HotelInclusionChildren::find($hotel_children_inclusion_id);

        $service_down = HotelInclusionChildren::where('hotel_id', $service_up->hotel_id)
            ->where('order', '<', $order)->orderBy('order', 'desc')->first();

        if ($service_down != null) {
            $service_up->order = $service_down->order;
            $service_up->save();

            $service_down_updated = HotelInclusionChildren::find($service_down->id);
            $service_down_updated->order = $order;
            $service_down_updated->save();
        }

        return \response()->json("Orden de inclusion del hotel actualizada");
    }

    public function downService(Request $request)
    {
        $hotel_children_inclusion_id = $request->input('hotel_children_inclusion_id');
        $order = $request->input('order');
        $service_up = HotelInclusionChildren::find($hotel_children_inclusion_id);
        $service_down = HotelInclusionChildren::where('hotel_id', $service_up->hotel_id)
            ->where('order', '>', $order)->orderBy('order', 'asc')->first();

        if ($service_down != null) {
            $service_up->order = $service_down->order;
            $service_up->save();
            $service_down_updated = HotelInclusionChildren::find($service_down->id);
            $service_down_updated->order = $order;
            $service_down_updated->save();
        }

        return \response()->json("Orden de inclusion del hotel actualizada");
    }

    public function updateStatus($id, Request $request)
    {
        $childrenInclusion = HotelInclusionChildren::find($id);
        if ($request->input("include")) {
            $childrenInclusion->include = 0;
        } else {
            $childrenInclusion->include = 1;
        }
        $childrenInclusion->save();
        return Response::json(['success' => true]);
    }
}
