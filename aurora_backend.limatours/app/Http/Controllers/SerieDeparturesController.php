<?php

namespace App\Http\Controllers;

use App\SerieDeparture;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class SerieDeparturesController extends Controller
{
    public function index($serie_id)
    {
        $data_departures = SerieDeparture::where('serie_id', $serie_id)->first();

        $departures = [];
        $count = 0;
        $total_dates = 0;
        $serie_departure_id = null;

        if( $data_departures ){
            $serie_departure_id = $data_departures->id;
            $data_departures = explode(',', $data_departures->dates);
            $total_dates = count($data_departures);
            asort( $data_departures );

            foreach ( $data_departures as $date ){
                if( $count === 0 ){
                    $departures[0]['month'] = Carbon::parse($date)->month;
                    $departures[0]['year'] = Carbon::parse($date)->year;
                    $departures[0]['dates'] = [$date];
                    $count++;
                } else {
                    $current_count = count($departures);
                    if( $departures[ $current_count - 1 ]['month'] === Carbon::parse($date)->month &&
                        $departures[ $current_count - 1 ]['year'] === Carbon::parse($date)->year ){
                        array_push( $departures[ $current_count - 1 ]['dates'], $date );
                    } else {
                        $departures[$current_count]['month'] = Carbon::parse($date)->month;
                        $departures[$current_count]['year'] = Carbon::parse($date)->year;
                        $departures[$current_count]['dates'] = [$date];
                        $count++;
                    }
                }
            }
        }

        return Response::json(['success' => true,
            'data'=> [
                'serie_departure_id' => $serie_departure_id,
                'departures' => $departures,
                'total' => $total_dates
            ] ]);
    }

    public function store($serie_id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mode' => 'required',
            'dates' => 'required'
        ]);

        if ($validator->fails()) {
            return Response::json(['success' => false, 'error' => $validator->errors()]);
        } else {

            $mode = $request->input('mode');
            $dates = $request->input('dates');
            $dates = implode(",",$dates);

            if( $mode === 'fixed' ){
                $value = '{}';
            } else {
                $value = json_encode( $request->input('value') );
            }

            SerieDeparture::where('serie_id', $serie_id)->delete();

            $new_departure = new SerieDeparture();
            $new_departure->serie_id = $serie_id;
            $new_departure->mode = $mode;
            $new_departure->value = $value;
            $new_departure->dates = $dates;
            $new_departure->save();

            return Response::json(['success' => true]);
        }
    }

    public function destroy_dates($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dates' => 'required'
        ]);

        if ($validator->fails()) {
            return Response::json(['success' => false, 'error' => $validator->errors()]);
        } else {

            $dates_for_delete = $request->input('dates');
            $departure = SerieDeparture::find($id);

            $departure_dates = explode(",",$departure->dates);
            foreach ( $dates_for_delete as $date_for_delete ){
                foreach ( $departure_dates as $key_d => $departure_date ){
                    if( $departure_date === $date_for_delete ){
                        unset($departure_dates[$key_d]);
                        break;
                    }
                }
            }

            // Registrando en campo aparte dates_deleted, las fechas que han sido borradas
            // para que el backup en json "value" inicial tenga sentido en algún proceso de restauración
            $new_dates_deleted = ( $departure->dates_deleted !== null ) ? json_decode( $departure->dates_deleted ) : [];
            array_push( $new_dates_deleted, $dates_for_delete );

            $departure->dates = implode(",", $departure_dates);
            $departure->dates_deleted = json_encode( $new_dates_deleted );
            $departure->save();

            return Response::json(['success' => true]);
        }
    }

}
