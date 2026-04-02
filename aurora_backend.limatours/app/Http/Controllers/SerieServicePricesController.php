<?php

namespace App\Http\Controllers;

use App\SerieCategory;
use App\SerieService;
use App\SerieServicePrice;
use App\Http\Traits\Serie;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class SerieServicePricesController extends Controller
{
    use Serie;

    public function store_all($serie_category_id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'serie_range_id' => 'required',
            'date' => 'required'
        ]);

        if ($validator->fails()) {
            return Response::json(['success' => false, 'error' => $validator->errors()]);
        } else {

            $serie_range_id = $request->input('serie_range_id');
            $date = $request->input('date');

            $serie_services = SerieService::where('serie_category_id', $serie_category_id)
                ->orderBy('date', 'asc')->get();

            // Update de dates
            $date_pivot_services = null;
            foreach ( $serie_services as $key => $service ){
                if ($key == 0) {
                    $date_pivot_services = $service->date;
                    $service->date = $date;
                    $service->save();
                } else {
                    $date_service_in = Carbon::parse($service->date);
                    $old_difference_days_service = Carbon::parse($date_pivot_services)->diffInDays($date_service_in);
                    $service->date = Carbon::parse($date)->addDays($old_difference_days_service)->format('Y-m-d');
                    $service->save();
                }
            }
            // Update de dates

            $service_codes = [];
            foreach ( $serie_services as $serie_service ){
                $serie_service_prices_count = SerieServicePrice::where('serie_service_id', $serie_service->id)
                    ->where('serie_range_id', $serie_range_id)
                    ->where('date', $serie_service->date)
                    ->count();
                if( $serie_service_prices_count === 0 ){
                    $parent_code = null;
                    if( $serie_service->parent_id !== null ){
                        $parent_code = SerieService::find($serie_service->parent_id)->code;
                    }
                    array_push( $service_codes, [
                        "id" => $serie_service->id,
                        "code" => $serie_service->code,
                        "parent_code" => $parent_code,
                        "type_code_service" => $serie_service->type_code_service,
                        "item_number" => $serie_service->item_number,
                        "date" => $serie_service->date,
                    ] );
                }
            }

            $response = true;

            if( count($service_codes) > 0 ){
                $response = $this->get_and_store_prices( $service_codes, $serie_range_id );
            }

        }
        return Response::json(['success' => $response]);

    }

    public function update_status(Request $request){

        /*
            option_range_date :
                1 = Aplicar sólo al rango y fecha seleccionada
                2 = Sólo al rango seleccionado y todas las fechas
                3 = Sólo a la fecha seleccionada y todos los rangos
                4 = Todos los rangos y todas las fechas
            option_category : this.radio_mode_prices_category :
                1 = Sólo a la categoría actual
                2 = Aplicar a todas las categorías
         * */

        $validator = Validator::make($request->all(), [
            'serie_id' => 'required',
            'serie_category_id' => 'required',
            'serie_range_id' => 'required',
            'date' => 'required',
            'serie_service_id' => 'required',
            'serie_service_code' => 'required',
            'serie_service_price_id_selected' => 'required',
            'option_range_date' => 'required',
            'option_category' => 'required',
        ]);

        if ($validator->fails()) {
            return Response::json(['success' => false, 'error' => $validator->errors()]);
        } else {

            $serie_id = $request->input('serie_id');
            $serie_category_id = $request->input('serie_category_id');
            $serie_range_id = $request->input('serie_range_id');
            $date = $request->input('date');
            $serie_service_id = $request->input('serie_service_id');
            $serie_service_code = $request->input('serie_service_code');
            $serie_service_price_id_selected = $request->input('serie_service_price_id_selected');
            $option_range_date = (int)($request->input('option_range_date'));
            $option_category = (int)($request->input('option_category'));
            /****/
            if( $option_category === 1){ // Sólo a la categoría actual
                $services = SerieService::where('id', $serie_service_id)->get();
            } else { // 2 Aplicar a todas las categorías
                $categories_ids = SerieCategory::where('serie_id', $serie_id)->pluck('id');
                $services = SerieService::whereIn('serie_category_id', $categories_ids)
                    ->where('code', $serie_service_code)
                    ->get();
            }
            /****/
            foreach ( $services as $service ){
                if( $option_range_date === 1 ){ // 1 = Aplicar sólo al rango y fecha seleccionada
                    $prices = SerieServicePrice::where('serie_service_id', $service->id)
                        ->where('serie_range_id', $serie_range_id)
                        ->where('date', $date)
                        ->get();
                    foreach ( $prices as $price ){
                        if( $price->id === $serie_service_price_id_selected ){
                            $price->status = true;
                        }else{
                            $price->status = false;
                        }
                        $price->save();
                    }
                } else if($option_range_date === 2){ // 2 = Sólo al rango seleccionado y todas las fechas
                    $dates = SerieServicePrice::select('date')
                        ->where('serie_service_id', $service->id)
                        ->where('serie_range_id', $serie_range_id)
                        ->groupBy('date')
                        ->get();

                    $base_code_selected = SerieServicePrice::find($serie_service_price_id_selected)->base_code;

                    foreach ( $dates as $date_ ){
                        $prices = SerieServicePrice::where('serie_service_id', $service->id)
                            ->where('serie_range_id', $serie_range_id)
                            ->where('date', $date_->date)
                            ->get();
                        $done = false;
                        foreach ( $prices as $price ){
                            if( $price->base_code === $base_code_selected && !($done) ){
                                $price->status = true;
                                $done = true;
                            }else{
                                $price->status = false;
                            }
                            $price->save();
                        }
                    }
                } else if($option_range_date === 3){ // 3 = Sólo a la fecha seleccionada y todos los rangos
                    $ranges = SerieServicePrice::select('serie_range_id')
                        ->where('serie_service_id', $service->id)
                        ->where('date', $date)
                        ->groupBy('serie_range_id')
                        ->get();

                    $base_code_selected = SerieServicePrice::find($serie_service_price_id_selected)->base_code;

                    foreach ( $ranges as $range_ ){
                        $prices = SerieServicePrice::where('serie_service_id', $service->id)
                            ->where('serie_range_id', $range_->serie_range_id)
                            ->where('date', $date)
                            ->get();
                        $done = false;
                        foreach ( $prices as $price ){
                            if( $price->base_code === $base_code_selected && !($done) ){
                                $price->status = true;
                                $done = true;
                            }else{
                                $price->status = false;
                            }
                            $price->save();
                        }
                    }
                } else { // 4 = Todos los rangos y todas las fechas
                    $dates = SerieServicePrice::select('date')
                        ->where('serie_service_id', $service->id)
                        ->groupBy('date')
                        ->get();

                    $base_code_selected = SerieServicePrice::find($serie_service_price_id_selected)->base_code;

                    foreach ( $dates as $date_ ){

                        $ranges = SerieServicePrice::select('serie_range_id')
                            ->where('serie_service_id', $service->id)
                            ->where('date', $date_->date)
                            ->groupBy('serie_range_id')
                            ->get();

                        foreach ( $ranges as $range_ ){
                            $prices = SerieServicePrice::where('serie_service_id', $service->id)
                                ->where('serie_range_id', $range_->serie_range_id)
                                ->where('date', $date_->date)
                                ->get();
                            $done = false;
                            foreach ( $prices as $price ){
                                if( $price->base_code === $base_code_selected && !($done) ){
                                    $price->status = true;
                                    $done = true;
                                }else{
                                    $price->status = false;
                                }
                                $price->save();
                            }
                        }
                    }
                }
            }

        }
        return Response::json(['success' => true]);
    }

}
