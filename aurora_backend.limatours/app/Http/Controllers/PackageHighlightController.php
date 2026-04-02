<?php

namespace App\Http\Controllers;

use App\Language;
use App\PackageHighlight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class PackageHighlightController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $id)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $querySearch = $request->input('query');
        $lang = $request->input('lang');
        $language = Language::where('iso', $lang)->first();

        $packageHighlights = PackageHighlight::with([
            'highlights.translations' => function ($query) use ($language) {
                $query->where('type', 'image_highlights');
                $query->where('language_id', $language->id);
            }
        ])->where('package_id', $id);


        $count = $packageHighlights->count();
        if ($paging === 1) {
            $packageHighlights = $packageHighlights->take($limit)->orderBy('day')->get();
        } else {
            $packageHighlights = $packageHighlights->skip($limit * ($paging - 1))->take($limit)
                ->orderBy('order')->get();
        }

        $data = [
            'data' => $packageHighlights,
            'count' => $count,
            'success' => true
        ];

        return Response::json($data);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\PackageHighlight  $packageHighlight
     * @return \Illuminate\Http\Response
     */
    public function show(PackageHighlight $packageHighlight)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\PackageHighlight  $packageHighlight
     * @return \Illuminate\Http\Response
     */
    public function edit(PackageHighlight $packageHighlight)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\PackageHighlight  $packageHighlight
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PackageHighlight $packageHighlight)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\PackageHighlight  $packageHighlight
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $packageHighlight = PackageHighlight::find($id);
        $packageHighlight->delete();

        return Response::json([
            'success' => true
        ]);
    }

    public function upHighlight(Request $request)
    {
        $package_highlight_id = $request->input('package_highlight_id');
        $order = $request->input('order');

        $service_up = PackageHighlight::find($package_highlight_id);

        $service_down = PackageHighlight::where('package_id', $service_up->package_id)->where('order', '<',
            $order)->orderBy('order', 'desc')->first();

        if ($service_down != null) {
            $service_up->order = $service_down->order;
            $service_up->save();

            $service_down_updated = PackageHighlight::find($service_down->id);
            $service_down_updated->order = $order;
            $service_down_updated->save();
        }

        return \response()->json("orden de inclusion del servicio actualizada");
    }

    public function downHighlight(Request $request)
    {
        $package_highlight_id = $request->input('package_highlight_id');
        $order = $request->input('order');

        $service_up = PackageHighlight::find($package_highlight_id);

        $service_down = PackageHighlight::where('package_id', $service_up->package_id)->where('order', '>',
            $order)->orderBy('order', 'asc')->first();

        if ($service_down != null) {
            $service_up->order = $service_down->order;
            $service_up->save();

            $service_down_updated = PackageHighlight::find($service_down->id);
            $service_down_updated->order = $order;
            $service_down_updated->save();
        }

        return \response()->json("Orden de highlight del paquete actualizada");
    }
}
