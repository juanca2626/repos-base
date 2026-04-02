<?php

namespace App\Http\Controllers;

use App\SerieService;
use App\SerieServicePrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class SerieServicesController extends Controller
{
    public function index($serie_category_id)
    {
        $data = SerieService::where('serie_category_id', $serie_category_id)
            ->orderBy('date')
            ->get();

        $data = $data->transform(function ($serie_service) {
            $serie_service['prices'] = SerieServicePrice::where('serie_service_id', $serie_service['id'])
                ->where('date', $serie_service['date'])->get();
            return $serie_service;
        });

        return Response::json(['success' => true, 'data'=> $data ]);
    }

    public function destroy_multiple(Request $request)
    {

        $serie_services_ids = $request->input('serie_services_ids');

        SerieServicePrice::whereIn('serie_service_id', $serie_services_ids )->delete();
        SerieService::whereIn('id', $serie_services_ids )->delete();

        return Response::json(['success' => true]);
    }

}
