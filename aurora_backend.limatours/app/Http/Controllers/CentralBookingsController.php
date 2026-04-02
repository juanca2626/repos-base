<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\CentralBooking;

class CentralBookingsController extends Controller
{

    public function index(Request $request)
    {
        $page = $request->input('page');
        $limit = $request->input('limit');

        $type_date =  $request->input('type_date');
        $date = ( $request->input('date') != '' ) ? $this->parseDate( $request->input('date') ) : '';
        $date_from = ( $request->input('date_from') != '' ) ? $this->parseDate($request->input('date_from')) : '';
        $date_to = ( $request->input('date_to') ) ? $this->parseDate($request->input('date_to')) : '';
        $active = $request->input('active');
        $final_check = $request->input('final_check');
        $lead_customer_surname = strtolower( $request->input('lead_customer_surname') );

        $filter = $request->input('filter');
        $order = $request->input('order');

        $result = CentralBooking::whereIn('model',
            ['App\TourcmsHeader', 'App\ExtensionExpediaService', 'App\ExtensionDespegarService', 'App\ExtensionPentagramaService']);

        // Dates
        if( $type_date == 'component_start_date' ){
            $result = $result->where('start_date', $date);
        }
        if( $type_date == 'made' ){
            $result = $result->where('made_date_time', '>=', $date_from )
                ->where('made_date_time', '<=', $date_to );
        }
        if( $type_date == 'start' ){
            $result = $result->where('start_date', '>=', $date_from )
                ->where('start_date', '<=', $date_to );
        }
        if( $type_date == 'end' ){
            $result = $result->where('end_date', '>=', $date_from )
                ->where('end_date', '<=', $date_to );
        }

        // State
        if( $active != '' and $active != '2' ){
            $result = $result->where('status', $active);
        }

        // lead_customer_surname
        if( $lead_customer_surname != '' ){
            $result = $result->where('passenger', 'like', '%'.$lead_customer_surname.'%');
        }

        $_count = $result->count();

//        var_export( $filter ); die;
        if( $filter != "undefined" && $filter != null ){
            $_order = ( $order == 'true' ) ? 'desc' : 'asc';
            $result = $result
                ->orderBy($filter, $_order);
        } else {
            $result = $result
                ->orderBy('created_at','desc');
        }

        $result = $result->skip($limit * ($page - 1))
            ->take($limit)->get();

        $data = [
            'count' => $_count,
            'data' => $result,
            'success' => true
        ];
        return Response::json($data);

    }

    private function parseDate($date){
        $explode = explode("/", $date);
        $response = $explode[2] . '-' . $explode[1] . '-' . $explode[0];
        return $response;
    }

}
