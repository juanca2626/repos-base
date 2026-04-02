<?php

namespace App\Http\Tourcms\Controller;

use App\CentralBooking;
use App\Http\Controllers\Controller;
use App\Ota;
use App\TourcmsChannel;
use App\TourcmsHeader;
use App\Http\Traits\Tourcms;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use App\Http\Traits\Aurora3;

class TourcmsController extends Controller
{
    use Tourcms, Aurora3;
    /*
    -- PROD
    Channel 14513    (NO ACTIVE)
    efa83074a898

    OTA    14576
    8f47f323f9e8
     *
    TEST:
    Channel ID: 3930
    Private key: 0df0db4dc340
     */
    // Marketplace ID will be 0 for Tour Operators, non-zero for Marketplace Agents
    // Agents can find their Marketplace ID in the API page in TourCMS settings
    const MARKETPLACE_ID = 0;
    // API key will be a string, find it in the API page in TourCMS settings
    const API_KEY = "efa83074a898";
    // Timeout will set the maximum execution time, in seconds. If set to zero, no time limit is imposed.
    const TIMEOUT = 30;
    // Channel ID represents the Tour Operator channel to call the API against
    // Tour Operators may have multiple channels, so enter the correct one here
    // Agents can make some calls (e.g. tour_search()) across multiple channels
    // by entering a Channel ID of 0 or omitting it, or they can restrict to a
    // specific channel by providing the Channel ID
    const CHANNEL_ID = 14513;

    public function searchBookings(Request $request)
    {
//        $this->connect();
        //
        //        $params = "page=" . $request->input('page') . "&per_page=" . $request->input('limit');

        $page = $request->input('page');
        $limit = $request->input('limit');

        $type_date = $request->input('type_date');
        $date = $request->input('date');
        $date_from = $request->input('date_from');
        $date_to = $request->input('date_to');
        $active = $request->input('active');
        $final_check = $request->input('final_check');
        $lead_customer_surname = strtolower($request->input('lead_customer_surname'));
        $agent_id = strtolower($request->input('agent_id'));

        $filter = $request->input('filter');
        $order = $request->input('order');

        $reserve_passed = $request->input('reserve_passed');

        $result = TourcmsHeader::with(['components', 'customers', 'reserves.reserve_file']);

        // Dates
        if ($type_date == 'component_start_date') {
//            $params .= "&component_start_date=" . $date;
            $result = $result->whereDate('start_date', $date);
        }
        if ($type_date == 'made') {
            $result = $result->whereDate('made_date_time', '>=', $date_from)
                ->whereDate('made_date_time', '<=', $date_to);
        }
        if ($type_date == 'start') {
            $result = $result->whereDate('start_date', '>=', $date_from)
                ->whereDate('start_date', '<=', $date_to);
        }
        if ($type_date == 'end') {
            $result = $result->whereDate('end_date', '>=', $date_from)
                ->whereDate('end_date', '<=', $date_to);
        }

        if ($reserve_passed == 0){
            $result = $result->where('start_date','>=',Carbon::now());
        }

        // State
        if ($active != '' and $active != '2') {
            $result = $result->where('status', $active);
        }

        // final_check
        if ($final_check != '') {
            $result = $result->where('final_check', $final_check);
        }
        // lead_customer_surname
        if ($lead_customer_surname != '') {
            $result = $result->where('lead_customer_surname', 'like', '%' . $lead_customer_surname . '%');
        }

        // agent_name
        if ($agent_id != '' and $agent_id != "undefined") {
            $result = $result->where('agent_id', $agent_id);
        }

        $_count = $result->count();

//        var_export( $filter ); die;
        if ($filter != "undefined" && $filter != null) {
            $_order = ($order == 'true') ? 'desc' : 'asc';
            $result = $result
                ->orderBy($filter, $_order);
        } else {
            $result = $result
                ->orderBy('start_date', 'asc');
        }

        $result = $result->skip($limit * ($page - 1))
            ->take($limit)->get();

        $data = [
            'count' => $_count,
            'data' => $result,
            'success' => true,
        ];
        return Response::json($data);

    }

    public function showBooking($booking_id)
    {
        $this->connect();

        $result = $this->doShowBooking($booking_id, '');

        return Response::json($result);

    }

    public function sendBooking(Request $request)
    {
        $servs = $request->input('services');
        $nrofile = $request->input('nrofile');

        $json_params = $this->do_params($servs, $nrofile);

//        return json_encode( $json_params['response']['datapla'] );

        if ($json_params['status'] == 'ok') {

            $client = new \GuzzleHttp\Client();
            $response = $client->request('POST',
                config('services.stella.domain') . 'api/v1/stelaFiles/savemeridiam', [
                    "json" => [
                        'datapla' => $json_params['response']['datapla'],
                        'datasvs' => $json_params['response']['datasvs'],
                        'datahtl' => $json_params['response']['datahtl'],
                        'datapax' => $json_params['response']['datapax'],
                    ],
                ]);

//            $response = [
            //                'data' => json_decode($response->getBody()->getContents()), // $call
            //                'detail' => 'ss',
            //                'success' => false
            //            ];
            //            return Response::json($response);

            $response = json_decode($response->getBody()->getContents());

            if ($response->success && $response->process) {
                // Create File A3..
                $this->createFileOTSA3($json_params['response'], $json_params['response']['datapla']['operad']);

                $_nrofile = $response->data->nroref;
                $reserve_file_id = DB::table('reserve_files')->insertGetId([
                    "file_number" => $_nrofile,
                    "type" => 'tourcms',
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now(),
                ]);

                foreach ($servs as $serv) {

                    $channel = TourcmsChannel::where('channel_id', $serv['channel_id'])->first();
                    DB::table('tourcms_reserves')->insert([
                        "reserve_file_id" => $reserve_file_id,
                        "tourcms_channel_id" => $channel->id,
                        "booking_id" => $serv['booking_id'],
                        "created_at" => Carbon::now(),
                        "updated_at" => Carbon::now(),
                    ]);
                }
                $response = [
                    'data' => $response, // $call
                    'detail' => $_nrofile,
                    'success' => true,
                ];

                if (count($servs) > 0) {
                    $update_central = CentralBooking::where('object_id', $servs[0]['tourcms_header_id'])
                        ->where('model', 'App\TourcmsHeader')
                        ->first();
                    $update_central->file_number = $_nrofile;
                    $update_central->status = 0;
                    $update_central->save();

                    $update_status_header = TourcmsHeader::find($servs[0]['tourcms_header_id']);
                    $update_status_header->status = 0;
                    $update_status_header->save();
                }

            } else {
                $response = [
                    'data' => $response, // $call
                    'detail' => 'Error en el web service de creación',
                    'success' => false,
                ];
            }

        } else {
            $response = [
                'detail' => $json_params['response'], // $call(int)$call['files'][0]
                'success' => false,
            ];
        }

        return Response::json($response);

    }

//    public function sendBooking_backup(Request $request)
    //    {
    //        $servs = $request->input('services');
    //        $nrofile = $request->input('nrofile');
    //        $xml = $this->doXML( $servs, $nrofile );
    //
    ////        return $xml['response'];
    //
    //        if( $xml['status'] == 'ok' ){
    //
    //            //url del webservice
    ////            $wsdl = "http://genero.limatours.com.pe:8099/AllotWebMeridi?wsdl";
    //            $wsdl = "http://genero.limatours.com.pe:8206/WS_AllotWebMeridi?wsdl";
    //            //       http://genero.limatours.com.pe:8095/AllocationWeb?WSDL   // Paquetes
    //            //instanciando un nuevo objeto cliente para consumir el webservice
    //            // new SoapClient("some.wsdl", array('soap_version'   => SOAP_1_2));
    //            $client=new \SoapClient($wsdl,[
    //                'encoding'=>'UTF-8',
    //                'trace'=>true,
    //                'soap_version'=>SOAP_1_1,
    //                'exceptions'=>true
    //            ]);
    //
    //            //pasando los parámetros a un array
    //            $filter=array(
    //                'dato'=>$xml['response'],
    //                'file'=>"LITO00".date('Ymdhis').".xml",
    //                'subdirs'=>false
    //            );
    //
    //            //llamando al método y pasándole el array con los parámetros
    //            $result = $client->__call('FileSearch', $filter);
    //
    //            if( $result['estado'] == "1" ){
    //                $_nrofile = (int)$result['files'][0];
    //                $reserve_file_id = DB::table('reserve_files')->insertGetId([
    //                    "file_number" => $_nrofile,
    //                    "type" => 'tourcms',
    //                    "created_at" => Carbon::now(),
    //                    "updated_at" => Carbon::now()
    //                ]);
    //
    ////                    DB::table('extension_despegar_services')->where('id', $serv['id'])
    ////                        ->update([
    ////                            'status' => 0
    ////                        ]);
    //
    //                foreach ( $servs as $serv ){
    //
    //                    $channel = TourcmsChannel::where('channel_id',$serv['channel_id'])->first();
    //                    DB::table('tourcms_reserves')->insert([
    //                        "reserve_file_id" => $reserve_file_id,
    //                        "tourcms_channel_id" => $channel->id,
    //                        "booking_id" => $serv['booking_id'],
    //                        "created_at" => Carbon::now(),
    //                        "updated_at" => Carbon::now()
    //                    ]);
    //                }
    //                $response = [
    //                    'data' => $result, // $call
    //                    'detail' => $_nrofile,
    //                    'success' => true
    //                ];
    //            } else {
    //                $response = [
    //                    'data' => $result, // $call
    //                    'detail' => 'Error en el web service de creación',
    //                    'success' => false
    //                ];
    //            }
    //
    //        } else{
    //            $response = [
    //                'detail' => $xml['response'], // $call(int)$call['files'][0]
    //                'success' => false
    //            ];
    //        }
    //
    //        return Response::json($response);
    //
    //    }

    public function downloadBookings(Request $request)
    {

        // ?event=new_confirmed_web&account_id=4069&account_name=testoperator&channel_id=3930&booking_id=1234
        // http://aurora_backend.test/api/channel/tourcms/integration?event=new&channel_id=14513&booking_id=34

        $event = $request->input('event');
        $channel_id = $request->input('channel_id');
        $booking_id = $request->input('booking_id');

        try {

            if (substr($event, 0, 3) == 'new') {

                $this->connect();
                $result = $this->doShowBooking($booking_id, $channel_id);

                /**** ELIMINAR */
                // return Response::json($result);die;
                /**** ELIMINAR */

                // return Response::json( $result ); die;

                if ($result->error == "OK") {

                    DB::transaction(function () use ($result, $channel_id, $event) {

                        $_channel_id = DB::table('tourcms_channels')
                            ->select('id')
                            ->where('channel_id', $channel_id)
                            ->first()->id;

                        $booking_has_net_price_ =
                        (!isset($result->booking->booking_has_net_price) || is_object($result->booking->booking_has_net_price))
                        ? 0
                        : $result->booking->booking_has_net_price;

                        $balance_ = (!isset($result->booking->balance) || is_object($result->booking->balance)) ? '' : $result->booking->balance;
                        $sales_revenue_ = (!isset($result->booking->sales_revenue) || is_object($result->booking->sales_revenue)) ? '' : $result->booking->sales_revenue;
                        $commission_ = (!isset($result->booking->commission) || is_object($result->booking->commission)) ? '' : $result->booking->commission;
                        $lead_customer_email_ = (!isset($result->booking->lead_customer_email) || is_object($result->booking->lead_customer_email)) ? '' : $result->booking->lead_customer_email;
                        $agent_code_ = (!isset($result->booking->agent_code) || is_object($result->booking->agent_code)) ? '' : $result->booking->agent_code;
                        $agent_ref_ = (!isset($result->booking->agent_ref) || is_object($result->booking->agent_ref)) ? '' : $result->booking->agent_ref;
                        $channel_name_ = (!isset($result->booking->channel_name) || is_object($result->booking->channel_name)) ? '' : $result->booking->channel_name;
                        $booking_id_ = (!isset($result->booking->booking_id) || is_object($result->booking->booking_id)) ? '' : $result->booking->booking_id;
                        $made_date_time_ = (!isset($result->booking->made_date_time) || is_object($result->booking->made_date_time)) ? '' : $result->booking->made_date_time;
                        $start_date_ = (!isset($result->booking->start_date) || is_object($result->booking->start_date)) ? '' : $result->booking->start_date;
                        $end_date_ = (!isset($result->booking->end_date) || is_object($result->booking->end_date)) ? '' : $result->booking->end_date;
                        $booking_name_ = (!isset($result->booking->booking_name) || is_object($result->booking->booking_name)) ? '' : $result->booking->booking_name;
                        $lead_customer_name_ = (!isset($result->booking->lead_customer_name) || is_object($result->booking->lead_customer_name)) ? '' : $result->booking->lead_customer_name;
                        $agent_name_ = (!isset($result->booking->agent_name) || is_object($result->booking->agent_name)) ? '' : $result->booking->agent_name;
                        $status_text_ = (!isset($result->booking->status_text) || is_object($result->booking->status_text)) ? '' : $result->booking->status_text;
                        $customer_count_ = (!isset($result->booking->customer_count) || is_object($result->booking->customer_count)) ? '' : $result->booking->customer_count;

                        $data_tourcms_headers = array(
                            'tourcms_channel_id' => $_channel_id,
                            'booking_id' => $booking_id_,
                            'channel_id' => $channel_id,
                            'account_id' => (!isset($result->booking->account_id) || is_object($result->booking->account_id)) ? '' : $result->booking->account_id,
                            'channel_name' => $channel_name_,
                            'made_date_time' => $made_date_time_,
                            'made_username' => (!isset($result->booking->made_username) || is_object($result->booking->made_username)) ? '' : $result->booking->made_username,
                            'made_type' => (!isset($result->booking->made_type) || is_object($result->booking->made_type)) ? '' : $result->booking->made_type,
                            'made_name' => (!isset($result->booking->made_name) || is_object($result->booking->made_name)) ? '' : $result->booking->made_name,
                            'start_date' => $start_date_,
                            'end_date' => $end_date_,
                            'booking_name' => $booking_name_,
                            'status' => 1,
                            'status_external' => (!isset($result->booking->status) || is_object($result->booking->status)) ? '' : $result->booking->status,
                            'status_text' => $status_text_,
                            'voucher_url' => (!isset($result->booking->voucher_url) || is_object($result->booking->voucher_url)) ? '' : $result->booking->voucher_url,
                            'barcode_data' => (!isset($result->booking->barcode_data) || is_object($result->booking->barcode_data)) ? '' : $result->booking->barcode_data,
                            'cancel_reason' => (!isset($result->booking->cancel_reason) || is_object($result->booking->cancel_reason)) ? '' : $result->booking->cancel_reason,
                            'cancel_text' => (!isset($result->booking->cancel_text) || is_object($result->booking->cancel_text)) ? '' : $result->booking->cancel_text,
                            'final_check' => (!isset($result->booking->final_check) || is_object($result->booking->final_check)) ? '' : $result->booking->final_check,
                            'lead_customer_id' => (!isset($result->booking->lead_customer_id) || is_object($result->booking->lead_customer_id)) ? '' : $result->booking->lead_customer_id,
                            'lead_customer_name' => $lead_customer_name_,
                            'lead_customer_email' => $lead_customer_email_,
                            'lead_customer_travelling' => (!isset($result->booking->lead_customer_travelling) || is_object($result->booking->lead_customer_travelling)) ? '' : $result->booking->lead_customer_travelling,
                            'customer_count' => $customer_count_,
                            'sale_currency' => (!isset($result->booking->sale_currency) || is_object($result->booking->sale_currency)) ? '' : $result->booking->sale_currency,
                            'sales_revenue' => $sales_revenue_,
                            'sales_revenue_display' => (!isset($result->booking->sales_revenue_display) || is_object($result->booking->sales_revenue_display)) ? '' : $result->booking->sales_revenue_display,
                            'deposit' => (!isset($result->booking->deposit) || is_object($result->booking->deposit)) ? '' : $result->booking->deposit,
                            'deposit_display' => (!isset($result->booking->deposit_display) || is_object($result->booking->deposit_display)) ? '' : $result->booking->deposit_display,
                            'agent_type' => (!isset($result->booking->agent_type) || is_object($result->booking->agent_type)) ? '' : $result->booking->agent_type,
                            'agent_id' => (!isset($result->booking->agent_id) || is_object($result->booking->agent_id)) ? '' : $result->booking->agent_id,
                            'marketplace_agent_id' => (!isset($result->booking->marketplace_agent_id) || is_object($result->booking->marketplace_agent_id)) ? '' : $result->booking->marketplace_agent_id,
                            'agent_name' => $agent_name_,
                            'agent_code' => $agent_code_,
                            'agent_ref' => $agent_ref_,
                            'tracking_miscid' => (!isset($result->booking->tracking_miscid) || is_object($result->booking->tracking_miscid)) ? '' : $result->booking->tracking_miscid,
                            'commission' => $commission_,
                            'commission_tax' => (!isset($result->booking->commission_tax) || is_object($result->booking->commission_tax)) ? '' : $result->booking->commission_tax,
                            'commission_currency' => (!isset($result->booking->commission_currency) || is_object($result->booking->commission_currency)) ? '' : $result->booking->commission_currency,
                            'commission_display' => (!isset($result->booking->commission_display) || is_object($result->booking->commission_display)) ? '' : $result->booking->commission_display,
                            'commission_tax_display' => (!isset($result->booking->commission_tax_display) || is_object($result->booking->commission_tax_display)) ? '' : $result->booking->commission_tax_display,
                            'booking_has_net_price' => $booking_has_net_price_,
                            'payment_status' => (!isset($result->booking->payment_status) || is_object($result->booking->payment_status)) ? '' : $result->booking->payment_status,
                            'payment_status_text' => (!isset($result->booking->payment_status_text) || is_object($result->booking->payment_status_text)) ? '' : $result->booking->payment_status_text,
                            'balance_owed_by' => (!isset($result->booking->balance_owed_by) || is_object($result->booking->balance_owed_by)) ? '' : $result->booking->balance_owed_by,
                            'balance' => $balance_,
                            'balance_display' => (!isset($result->booking->balance_display) || is_object($result->booking->balance_display)) ? '' : $result->booking->balance_display,
                            'balance_due' => (!isset($result->booking->balance_due) || is_object($result->booking->balance_due)) ? '' : $result->booking->balance_due,
                            'customers_agecat_breakdown' => (!isset($result->booking->customers_agecat_breakdown) || is_object($result->booking->customers_agecat_breakdown)) ? '' : $result->booking->customers_agecat_breakdown,
                            'event' => $event,
                            'created_at' => date("Y-m-d H:i:s"),
                            'updated_at' => date("Y-m-d H:i:s"));
                        $header_id = DB::table('tourcms_headers')->insertGetId($data_tourcms_headers);

                        // -------- GENERAL TABLE UNIFIED ----------------
                        $sale_ = ((int) ($booking_has_net_price_))
                        ? $balance_
                        : $sales_revenue_ - $commission_;

                        $tags_tourcms = json_encode([
                            "channel_id" => $channel_id,
                            "lead_customer_email" => $lead_customer_email_,
                            "agent_code" => $agent_code_,
                            "agent_ref" => $agent_ref_,
                            "sale" => $sale_,
                        ]);
                        $ota = Ota::select('id','name')->where('name','tourcms')->first();

                        DB::table('central_bookings')->insert([
                            "ota_id"=>$ota->id,
                            "channel_name" => $ota->name,
                            "model" => 'App\TourcmsHeader',
                            "object_id" => $header_id,
                            "code" => $booking_id_,
                            "tags" => $tags_tourcms,
                            "status" => 1,
                            "made_date_time" => $made_date_time_,
                            "start_date" => $start_date_,
                            "end_date" => $end_date_,
                            "description" => $booking_name_,
                            "passenger" => $lead_customer_name_,
                            "agent" => $agent_name_,
                            "file_number" => null,
                            "aurora_code" => "",
                            "type_service" => $status_text_,
                            "quantity_pax" => $customer_count_,
                            "created_at" => Carbon::now(),
                            "updated_at" => Carbon::now(),
                        ]);
                        // -------- GENERAL TABLE UNIFIED ----------------

                        foreach ($result->booking->customers->customer as $c) {
                            DB::table('tourcms_customers')->insert([
                                'tourcms_header_id' => $header_id,
                                'customer_id' => (!isset($c->customer_id) || is_object($c->customer_id)) ? '' : $c->customer_id,
                                'customer_name' => (!isset($c->customer_name) || is_object($c->customer_name)) ? '' : $c->customer_name,
                                'firstname' => (!isset($c->firstname) || is_object($c->firstname)) ? '' : $c->firstname,
                                'surname' => (!isset($c->surname) || is_object($c->surname)) ? '' : $c->surname,
                                'customer_email' => (!isset($c->customer_email) || is_object($c->customer_email)) ? '' : $c->customer_email,
                                'agecat_text' => (!isset($c->agecat_text) || is_object($c->agecat_text)) ? '' : $c->agecat_text,
                                'agecat' => (!isset($c->agecat) || is_object($c->agecat)) ? '' : $c->agecat,
                                'gender' => (!isset($c->gender) || is_object($c->gender)) ? '' : $c->gender,
                                'created_at' => date("Y-m-d H:i:s"),
                                'updated_at' => date("Y-m-d H:i:s"),
                            ]);
                        }

                        foreach ($result->booking->components->component as $c) {

                            $net_quantity_rule = "";
                            $net_price_quantity = 0;
                            $net_price_tax_total = 0;
                            $net_price_inc_tax_total = 0;
                            $net_price = 0;
                            if ((int) $booking_has_net_price_ === 1) {
                                $net_quantity_rule = (!isset($c->net_quantity_rule) || is_object($c->net_quantity_rule)) ? '' : $c->net_quantity_rule;
                                $net_price_quantity = (!isset($c->net_price_quantity) || is_object($c->net_price_quantity)) ? '' : $c->net_price_quantity;
                                $net_price_tax_total = (!isset($c->net_price_tax_total) || is_object($c->net_price_tax_total)) ? '' : $c->net_price_tax_total;
                                $net_price_inc_tax_total = (!isset($c->net_price_inc_tax_total) || is_object($c->net_price_inc_tax_total)) ? '' : $c->net_price_inc_tax_total;
                                $net_price = (!isset($c->net_price) || is_object($c->net_price)) ? '' : $c->net_price;
                            }

                            $data_tourcms_components = array(
                                'tourcms_header_id' => $header_id,
                                'component_id' => (!isset($c->component_id) || is_object($c->component_id)) ? '' : $c->component_id,
                                'linked_component_id' => (!isset($c->linked_component_id) || is_object($c->linked_component_id)) ? '' : $c->linked_component_id,
                                'product_id' => (!isset($c->product_id) || is_object($c->product_id)) ? '' : $c->product_id,
                                'date_id' => (!isset($c->date_id) || is_object($c->date_id)) ? '' : $c->date_id,
                                'date_type' => (!isset($c->date_type) || is_object($c->date_type)) ? '' : $c->date_type,
                                'product_code' => (!isset($c->product_code) || is_object($c->product_code)) ? '' : $c->product_code,
                                'date_code' => (!isset($c->date_code) || is_object($c->date_code)) ? '' : $c->date_code,
                                'start_date' => (!isset($c->start_date) || is_object($c->start_date)) ? '' : $c->start_date,
                                'end_date' => (!isset($c->end_date) || is_object($c->end_date)) ? '' : $c->end_date,
                                'local_payment' => (!isset($c->local_payment) || is_object($c->local_payment)) ? '' : $c->local_payment,
                                'customer_payment' => (!isset($c->customer_payment) || is_object($c->customer_payment)) ? '' : $c->customer_payment,
                                'component_added_datetime' => (!isset($c->component_added_datetime) || is_object($c->component_added_datetime)) ? '' : $c->component_added_datetime,
                                'start_time' => (!isset($c->start_time) || is_object($c->start_time)) ? '' : $c->start_time,
                                'end_time' => (!isset($c->end_time) || is_object($c->end_time)) ? '' : $c->end_time,
                                'component_name' => (!isset($c->component_name) || is_object($c->component_name)) ? '' : $c->component_name,
                                'product_note' => (!isset($c->product_note) || is_object($c->product_note)) ? '' : $c->product_note,
                                'component_note' => (!isset($c->component_note) || is_object($c->component_note)) ? '' : $c->component_note,
                                'rate_breakdown' => (!isset($c->rate_breakdown) || is_object($c->rate_breakdown)) ? '' : $c->rate_breakdown,
                                'rate_description' => (!isset($c->rate_description) || is_object($c->rate_description)) ? '' : $c->rate_description,
                                'supplier_id' => (!isset($c->supplier_id) || is_object($c->supplier_id)) ? '' : $c->supplier_id,
                                'redeem' => (!isset($c->redeem) || is_object($c->redeem)) ? '' : $c->redeem,
                                'product_type' => (!isset($c->product_type) || is_object($c->product_type)) ? '' : $c->product_type,
                                'sale_quantity' => (!isset($c->sale_quantity) || is_object($c->sale_quantity)) ? '' : $c->sale_quantity,
                                'sale_quantity_rule' => (!isset($c->sale_quantity_rule) || is_object($c->sale_quantity_rule)) ? '' : $c->sale_quantity_rule,
                                'sale_tax_percentage' => (!isset($c->sale_tax_percentage) || is_object($c->sale_tax_percentage)) ? '' : $c->sale_tax_percentage,
                                'sale_tax_inclusive' => (!isset($c->sale_tax_inclusive) || is_object($c->sale_tax_inclusive)) ? '' : $c->sale_tax_inclusive,
                                'sale_currency' => (!isset($c->sale_currency) || is_object($c->sale_currency)) ? '' : $c->sale_currency,
                                'sale_price' => (!isset($c->sale_price) || is_object($c->sale_price)) ? '' : $c->sale_price,
                                'tax_total' => (!isset($c->tax_total) || is_object($c->tax_total)) ? '' : $c->tax_total,
                                'sale_price_inc_tax_total' => (!isset($c->sale_price_inc_tax_total) || is_object($c->sale_price_inc_tax_total)) ? '' : $c->sale_price_inc_tax_total,
                                'sale_exchange_rate' => (!isset($c->sale_exchange_rate) || is_object($c->sale_exchange_rate)) ? '' : $c->sale_exchange_rate,
                                'currency_base' => (!isset($c->currency_base) || is_object($c->currency_base)) ? '' : $c->currency_base,
                                'sale_price_base' => (!isset($c->sale_price_base) || is_object($c->sale_price_base)) ? '' : $c->sale_price_base,
                                'tax_total_base' => (!isset($c->tax_total_base) || is_object($c->tax_total_base)) ? '' : $c->tax_total_base,
                                'sale_price_inc_tax_total_base' => (!isset($c->sale_price_inc_tax_total_base) || is_object($c->sale_price_inc_tax_total_base)) ? '' : $c->sale_price_inc_tax_total_base,
                                'net_quantity_rule' => $net_quantity_rule,
                                'net_price_quantity' => $net_price_quantity,
                                'net_price_tax_total' => $net_price_tax_total,
                                'net_price_inc_tax_total' => $net_price_inc_tax_total,
                                'net_price' => $net_price,
                                'cost_quantity' => (!isset($c->cost_quantity) || is_object($c->cost_quantity)) ? '' : $c->cost_quantity,
                                'cost_quantity_rule' => (!isset($c->cost_quantity_rule) || is_object($c->cost_quantity_rule)) ? '' : $c->cost_quantity_rule,
                                'cost_tax_percentage' => (!isset($c->cost_tax_percentage) || is_object($c->cost_tax_percentage)) ? '' : $c->cost_tax_percentage,
                                'cost_tax_inclusive' => (!isset($c->cost_tax_inclusive) || is_object($c->cost_tax_inclusive)) ? '' : $c->cost_tax_inclusive,
                                'cost_currency' => (!isset($c->cost_currency) || is_object($c->cost_currency)) ? '' : $c->cost_currency,
                                'cost_price' => (!isset($c->cost_price) || is_object($c->cost_price)) ? '' : $c->cost_price,
                                'cost_tax_total' => (!isset($c->cost_tax_total) || is_object($c->cost_tax_total)) ? '' : $c->cost_tax_total,
                                'cost_price_inc_tax_total' => (!isset($c->cost_price_inc_tax_total) || is_object($c->cost_price_inc_tax_total)) ? '' : $c->cost_price_inc_tax_total,
                                'cost_exchange_rate' => (!isset($c->cost_exchange_rate) || is_object($c->cost_exchange_rate)) ? '' : $c->cost_exchange_rate,
                                'cost_price_base' => (!isset($c->cost_price_base) || is_object($c->cost_price_base)) ? '' : $c->cost_price_base,
                                'cost_tax_total_base' => (!isset($c->cost_tax_total_base) || is_object($c->cost_tax_total_base)) ? '' : $c->cost_tax_total_base,
                                'cost_price_inc_tax_total_base' => (!isset($c->cost_price_inc_tax_total_base) || is_object($c->cost_price_inc_tax_total_base)) ? '' : $c->cost_price_inc_tax_total_base,
                                'created_at' => date("Y-m-d H:i:s"),
                                'updated_at' => date("Y-m-d H:i:s"),
                            );
                            DB::table('tourcms_components')->insert($data_tourcms_components);

                            $users = [];
                            $permission =
                            DB::table("permissions")->where('slug', "reservationcentertourcms.create")->first();
                            if ($permission) {
                                $permission_role_ids =
                                DB::table("permission_role")->where('permission_id', $permission->id)->pluck('role_id');
                                $role_user_ids = DB::table('role_user')->whereIn('role_id', $permission_role_ids)->pluck('user_id');
                                $users = User::whereIn('id', $role_user_ids)->get();
                            }
                            foreach ($users as $user) {
                                $pushNoti = (object) [
                                    'user' => $user->code,
                                    'title' => "Tourcms - Nueva reserva",
                                    'body' => 'Ha llegado una nueva reserva a la plataforma Aurora de Tourcms. Toque este mensaje para darle seguimiento.',
                                    'click_action' => 'https://aurora.limatours.com.pe/central_bookings/tourcms'];
                                $this->sendPushNotification($pushNoti);
                            }

                            if (config('app.env') == 'production') {
                                $user_emails = User::whereIn('id', $role_user_ids)->pluck('email');
                                $mail = mail::to(["destinationservices@limatours.com.pe"]);
                            }

                            // $data_email_start_date = (!isset($c->start_date) || is_object($c->start_date)) ? '' : Carbon::parse($c->start_date);
                            if (!empty($data_tourcms_components['start_date'])) {
                                $data_tourcms_components['start_date'] = Carbon::parse($data_tourcms_components['start_date']);
                                $data_tourcms_components['start_date'] = $data_tourcms_components['start_date']->format('d/m/Y');
                            }

                            $data_email = array(
                                'agent_name' => $data_tourcms_headers['agent_name'],
                                'booking_id' => $data_tourcms_headers['booking_id'],
                                'booking_name' => $data_tourcms_headers['booking_name'],
                                'start_date' => $data_tourcms_components['start_date'],
                                'lead_customer_name' => $data_tourcms_headers['lead_customer_name'],
                                'customers_agecat_breakdown' => $data_tourcms_headers['customers_agecat_breakdown'],
                                'product_code' => $data_tourcms_components['product_code'],
                                'agent_ref' => $data_tourcms_headers['agent_ref'],
                            );

                            $mail->send(new \App\Mail\NotificationTourcms($data_email));

                        }

                    });

                }

            }
        } catch (\Exception $e) {
            return ["error_line" => $e->getLine(), "error_message" => $e->getMessage()];
        }
    }

    public function downloadBookings2(Request $request)
    {

        // ?event=new_confirmed_web&account_id=4069&account_name=testoperator&channel_id=3930&booking_id=1234
        // http://aurora_backend.test/api/channel/tourcms/integration2?event=new&channel_id=14513&booking_id=34

        $event = $request->input('event');
        $channel_id = $request->input('channel_id');
        $booking_id = $request->input('booking_id');

        try {

            if (substr($event, 0, 3) == 'new') {
                $this->connect();
                $result = $this->doShowBooking($booking_id, $channel_id);
                return Response::json($result);die;
            }
        } catch (\Exception $e) {
            return ["error_line" => $e->getLine(), "error_message" => $e->getMessage()];
        }
    }

    public function filterAgents()
    {

        $response = DB::table('tourcms_headers')
            ->select('agent_id as code', 'agent_name as label')
            ->distinct()->get();

        return Response::json($response);
    }

    public function updateStatus($id, Request $request)
    {
        $package = TourcmsHeader::find($id);
        $update_central = CentralBooking::where('model', 'App\TourcmsHeader')
            ->where('object_id', $id)
            ->first();
        if ($request->input("status")) {
            $update_central->status = 0;
            $package->status = 0;
        } else {
            $update_central->status = 1;
            $package->status = 1;
        }
        $package->save();
        $update_central->save();

        return Response::json(['success' => true]);
    }

    public function updateStatusExternal($id, Request $request)
    {
        $package = TourcmsHeader::find($id);
        /*
        $update_central = CentralBooking::where('model', 'App\TourcmsHeader')
            ->where('object_id', $id)
            ->first();
        */

        $package->status_external = $request->__get('code');
        $package->status_text = $request->__get('name');

        /*
        if ($request->input("code")) {
            $update_central->status = 0;
            $package->status = 0;
        } else {
            $update_central->status = 1;
            $package->status = 1;
        }
        */
        $package->save();
        // $update_central->save();

        return Response::json(['success' => true]);
    }
}
