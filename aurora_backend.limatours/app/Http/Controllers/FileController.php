<?php

namespace App\Http\Controllers;

use App\ChannelHotel;
use App\Client;
use App\FileAccommodation;
use App\FileService;
use App\Hotel;
use App\Http\Stella\StellaService;
use App\File;
use App\Reservation;
use App\Language;
use App\ReservationPassenger;
use App\Http\Traits\Reservations;
use App\Http\Traits\Files;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class FileController extends Controller
{
    use Reservations, Files;

    protected $stellaService;

    public function __construct(StellaService $stellaService)
    {
        $this->stellaService = $stellaService;
    }

    public function search(Request $request){

        try {
            $id_greater_than = $request->input('id_greater_than');
            $first = ($request->input('first')) ? $request->input('first') : 20;

            $_response = File::with(['services.accommodations']);

            if($id_greater_than){
                $_response->where('id', '>', $id_greater_than);
            }

            $_response = $_response->take($first)
                ->orderBy('id')
                ->get();

            $response = [
                'data' => $_response,
                'type' => 'success',
            ];
        } catch (\Exception $ex) {
            $response = [
                'type' => 'error',
                'message' => $ex->getMessage()
            ];
        }

        return response()->json($response);
    }

    public function show($nroref, Request $request){

        $lang = ( $request->input('lang') ) ? $request->input('lang') : "es";

        $language_id = Language::select('id')->where('iso',$lang)->first()->id;

        $file = File::where('file_number', $nroref)
            ->with(['services.accommodations', 'client.countries.translations'=>function($query) use($language_id){
                $query->where('language_id',$language_id);
            }])
            ->first();

        if(!$file){

            $file_import_ifx = $this->import_file($nroref);

//            return $file_import_ifx;

            if($file_import_ifx['success']) {
                $file = File::where('file_number', $nroref)
                    ->with(['services.accommodations', 'client.countries.translations'=>function($query) use($language_id){
                        $query->where('language_id',$language_id);
                    }])
                    ->first();
                $file->total_accommodation = 0;
                $data = array(
                    "data" => $file,
                    "success" => true,
                    "message" => "Importado correctamente"
                );
            } else {
                $data = array(
                    "data" => [],
                    "success" => false,
                    "message" => "Error import ". $file_import_ifx['message']
                );
            }
        } else {

            $file_services_ids = FileService::where('file_id', $file->id)->pluck('id');
            $total_accommodation_ =
                ( count($file_services_ids) > 0 )
                    ? FileAccommodation::whereIn('file_service_id', $file_services_ids)->count()
                    : 0;

            $file->total_accommodation = $total_accommodation_;
            $data = array(
                "data" => $file,
                "success" => true,
                "message" => "Ya en mysql!"
            );
        }

        return Response::json($data);

    }

    public function save_service_file(Request $request, $nrofile)
    {
        try {
            $array = (array)$request->all();
            $array['event']['descri'] = @$array['event']['contentFull'];

            if (@$array['event']['ciuin'] == '' OR is_null(@$array['event']['ciuin'])) {
                $array['event']['ciuin'] = '';
            }

            if (@$array['event']['ciuout'] == '' OR is_null(@$array['event']['ciuout'])) {
                $array['event']['ciuout'] = '';
            }

            if (is_array($array['event']['bastar'])) {
                $array['event']['bastar'] = $array['event']['bastar']['bastar'];
                $array['bastar'] = $array['event']['bastar'];
            }

            if (is_array($array['event']['ciuin'])) {
                $array['event']['ciuin'] = $array['event']['ciuin']['codciu'];
            }

            if (is_array($array['event']['ciuout'])) {
                $array['event']['ciuout'] = $array['event']['ciuout']['codciu'];
            }

            if (isset($array['event']['ciavue'])) {
                if (is_array($array['event']['ciavue'])) {
                    $array['event']['ciavue'] = $array['event']['ciavue']['codigo'];
                }
            } else {
                $array['event']['ciavue'] = '';
                $array['event']['nrovue'] = '';
            }

            $bastar_venta = [];
            $bastar_costo = [];
            $bastar = '';
            $tipo_cambio = $this->stellaService->search_exchange();

            if ($array['event']['codsvs'] == 'AEIFLT' OR $array['event']['codsvs'] == 'AECFLT') {
                $bastar = (object)[
                    'bastar' => $array['bastar'],
                    'desbas' => ''
                ];

                if ($array['event']['ciuin'] != '' AND $array['event']['ciuout'] != '') {
                    $array['event']['service']['descri'] = $array['event']['ciuin'].'/'.$array['event']['ciuout'];
                } else {
                    $array['event']['service']['descri'] = $array['event']['codsvs'].'( DATO INCOMPLETO )';
                }
            } else {
                $_data = [
                    'bastar' => $array['bastar'],
                    'fecin' => $array['event']['fecin'],
                    'fecout' => $array['event']['fecout']
                ];
                $bastars = $this->stellaService->search_bastar($array['event']['codsvs'], $_data);

                foreach ($bastars as $k => $v) {
                    if ($bastar == '') {
                        $bastar = $v;
                    }

                    if ($v->vtacos == 'V') {
                        $bastar_venta = $v;
                    }

                    if ($v->vtacos == 'C') {
                        $bastar_costo = $v;
                    }
                }
            }

            $costo_venta_unitario = (double)@$bastar_costo->valor;

            if (trim(@$bastar_costo->moneda) != 'USD' AND $costo_venta_unitario > 0) {
                $costo_venta_unitario = $costo_venta_unitario / $tipo_cambio;
            }

            $array['event']['fecope'] = date("d/m/Y");
            $array['event']['bastar'] = @$bastar->bastar;
            $array['event']['desbas'] = @$bastar->desbas;
            $array['event']['preped'] = @$bastar->preped;
            $array['event']['prefac'] = @$bastar->prefac;
            $array['event']['prevou'] = @$bastar->prevou;
            $array['event']['clasif'] = 5;
            $array['event']['infoad'] = $array['event']['descri'];
            $array['event']['descri'] = @$array['event']['service']['descri'];
            $array['event']['moneda'] = $array['file']['moncot'];
            $array['event']['monvta'] = @$bastar_venta->moneda;
            $array['event']['moncos'] = @$bastar_costo->moneda;
            $array['event']['vtaloc'] = (double)@$bastar_venta->valor;
            $array['event']['cosloc'] = (double)@$bastar_costo->valor;
            $array['event']['vtauni'] = (double)@$bastar_venta->valor;
            $array['event']['cosuni'] = $costo_venta_unitario;

            $array['event']['cantid'] = $array['file']['paxs'];
            $array['event']['piaced'] = $array['file']['piaced'];

            $array['event']['tarifa'] = @number_format($array['event']['vtauni'] * $array['event']['cantid'], 2);
            $array['event']['netpag'] = @number_format($array['event']['cosuni'] * $array['event']['cantid'], 2);
            $array['event']['iatced'] = @number_format($array['event']['tarifa'] * $array['event']['piaced'], 2);
            $array['event']['netfac'] = @number_format($array['event']['tarifa'] - $array['event']['iatced'], 2);
            $array['event']['netexe'] = $array['event']['netfac'];
            $array['event']['iatgan'] = @number_format($array['event']['tarifa'] - $array['event']['netpag'], 2);

            $array['event']['grvuni'] = 0;
            $array['event']['ivvuni'] = 0;
            $array['event']['grcuni'] = 0;
            $array['event']['ivcuni'] = 0;
            $array['event']['taruni'] = 0;

            $tipdoc = '';

            if (@$bastar->interm == 'N') {
                $tipdoc = 'FA';
            }

            if (@$bastar->interm == 'S') {
                $tipdoc = 'HI';
            }

            $array['event']['tipdoc'] = $tipdoc;
            $array['event']['diario'] = @$bastar->diario;
            $array['event']['paxuni'] = @$bastar->paxuni;
            $array['event']['cotiza'] = @$bastar->cotiza;
            $array['event']['vouche'] = @$bastar->vouche;
            $array['event']['itiner'] = @$bastar->itiner;
            $array['event']['vouemi'] = @$bastar->vouemi;
            $array['event']['nrovou'] = 0;
            $array['event']['ticket'] = $array['file']['ticket'];
            $array['event']['tktemi'] = $array['file']['ticket'];
            $array['event']['tipope'] = @$bastar->tipope;
            $array['event']['clase'] = @$bastar->clase;
            $array['event']['pain'] = @$bastar->pain;
            $array['event']['paiout'] = @$bastar->paiout;
            $array['event']['gruin'] = @$bastar->gruin;
            $array['event']['gruout'] = @$bastar->gruout;

            $event = $array['event'];

//            return $array; die;

            $_nroite = (array)$this->stellaService->save_service_file($nrofile, $array);
            $nroite = $_nroite[0];
            $_nroite_event = $_nroite[0];

            $this->stellaService->create_relation_t21($nrofile, $nroite, $array);

            $component = [];
            $components = [];

            $_array = [
                'bastar' => $array['bastar']
            ];
            $_components = $this->stellaService->search_components($array['event']['codsvs'], $_array);

            if (count($_components) > 0) {
                foreach ($_components as $key => $value) {
                    $_component = $this->stellaService->find_component($value->codsvs);
                    $component = $_component[0];

                    $_data = [
                        'bastar' => $value->bastar,
                        'fecin' => $array['event']['fecin'],
                        'fecout' => $array['event']['fecout']
                    ];
                    $bastars = $this->stellaService->search_bastar($value->codsvs, $_data);
                    $bastar_venta = [];
                    $bastar_costo = [];
                    $bastar = '';

                    foreach ($bastars as $k => $v) {
                        if ($bastar == '') {
                            $bastar = $v;
                        }

                        if ($v->vtacos == 'V') {
                            $bastar_venta = $v;
                        }

                        if ($v->vtacos == 'C') {
                            $bastar_costo = $v;
                        }
                    }

                    if ($bastar != '') {
                        $costo_venta_unitario = @$bastar_costo->valor;

                        if (trim(@$bastar_costo->moneda) != 'USD') {
                            $costo_venta_unitario = $bastar_costo->valor / $tipo_cambio;
                        }

                        $_data = [
                            'nroite' => $nroite,
                            '_nroite' => 0,
                            'fecope' => date("d/m/Y"),
                            'tipope' => @$bastar->tipope,
                            'clase' => @$bastar->clase,
                            'tipsvs' => substr($component->tipo, 0, 3),
                            'codsvs' => $value->codsvs,
                            'descri' => $component->descri,
                            'bastar' => @$bastar->bastar,
                            'desbas' => @$bastar->desbas,
                            'preped' => @$bastar->preped,
                            'prefac' => @$bastar->prefac,
                            'prevou' => @$bastar->prevou,
                            'ciuin' => $component->ciudes,
                            'ciuout' => $component->ciuhas,
                            'fecin' => $array['event']['fecin'],
                            'fecout' => $array['event']['fecout'],
                            'horin' => $array['event']['horin'],
                            'horout' => $array['event']['horout'],
                            'cantid' => $value->cansvs,
                            'canpax' => $value->canpax,
                            'tippax' => 'ADL',
                            'grupo' => $array['file']['grupo'],
                            'estado' => 'OK',
                            'secpax' => 3.155,
                            'operad' => $array['file']['operad'],
                            'codsec' => $array['file']['codsec'],
                            'succli' => $array['file']['succli'],
                            'codcli' => $array['file']['codcli'],
                            'codven' => $array['file']['codven'],
                            'codope' => $array['file']['codope'],
                            // 'paiin' => $array['file']['paiopc'],
                            'moneda' => @$bastar->moneda,
                            'monvta' => @$bastar_venta->moneda,
                            'moncos' => @$bastar_costo->moneda,
                            'vtaloc' => @$bastar_venta->valor,
                            'cosloc' => @$bastar_costo->valor,
                            'vtauni' => @$bastar_venta->valor,
                            'cosuni' => $costo_venta_unitario,
                            'grvuni' => 0,
                            'ivvuni' => 0,
                            'grcuni' => 0,
                            'ivcuni' => 0,
                            'taruni' => 0,
                            'diario' => @$bastar->diario,
                            'paxuni' => @$bastar->paxuni,
                            'cansvs' => $value->cansvs,
                            'tarifa' => 0,
                            'online' => 0,
                            'offline' => 0,
                            'piagan' => 0,
                            'iatgan' => 0,
                            'povgan' => 0,
                            'ovegan' => 0,
                            'netpag' => 0,
                            'piaced' => 0,
                            'iatced' => 0,
                            'povced' => 0,
                            'oveced' => 0,
                            'netfac' => 0,
                            'netgra' => 0,
                            'netexe' => 0,
                            'piapro' => 0,
                            'iatpro' => 0,
                            'povpro' => 0,
                            'ovepro' => 0,
                            'tax1' => 0,
                            'tax2' => 0,
                            'tax3' => 0,
                            'cotiza' => @$bastar->cotiza,
                            'vouche' => @$bastar->vouche,
                            'itiner' => @$bastar->itiner,
                            'vouemi' => @$bastar->vouemi,
                            'nrovou' => 0,
                            'ticket' => $array['file']['ticket'],
                            'tktemi' => $array['file']['ticket'],
                            'docum' => '',
                            'docemi' => '',
                            'nrosuc' => '',
                            'serie' => '',
                            'tipdoc' => '',
                            'nrodoc' => '',
                            'viacom' => @$bastar->viacom,
                            'comemi' => '',
                            'asigna' => '',
                            'asiemi' => '',
                            'ciavue' => '',
                            'nrovue' => '',
                            'pain' => @$bastar->pain,
                            'paiout' => @$bastar->paiout,
                            'gruin' => @$bastar->gruin,
                            'gruout' => @$bastar->gruout
                        ];

                        $components[] = $_data;
                        $_nroite = (array)$this->stellaService->create_component($array['file']['nroref'], $_data);
                        $nroite_component = $_nroite[0];

                        $array['event'] = $_data;
                        $this->stellaService->create_relation_t21($nrofile, $nroite_component, $array);
                    }
                }
            }

            //***
            $file_id_ = File::where('file_number', $nrofile)->first()->id;
            $import_ = $this->import_services($nrofile, $file_id_, '');

            $event['nroite'] = $_nroite_event;
            $event['title'] = $event['descri'];

            $response = [
                'components_post' => $components,
                'components' => $_components,
                'component' => $component,
                'type' => 'success',
                'event' => $event,
                'lang' => $array['lang'],
                'import' => $import_
            ];
        } catch (\Exception $ex) {
            $response = [
                'type' => 'error',
                'message' => $ex->getMessage(),
                'line' => $ex->getLine(),
            ];
        }

        return response()->json($response);
    }

    public function update_service_file(Request $request, $nrofile, $nroite)
    {
        try {
            $array = (array)$request->all();
            $tipo_cambio = $this->stellaService->search_exchange();

            if ((int)$array['flag_edit'] == 0) {
                $fecin = date("d/m/Y", strtotime($array['event']['start']));
                $fecout = date("d/m/Y", strtotime($array['event']['end']));
                $horin = date("H:i", strtotime($array['event']['start']));
                $horout = date("H:i", strtotime($array['event']['end']));

                $array['event']['fecin'] = $fecin;
                $array['event']['fecout'] = $fecout;
                $array['event']['horin'] = $horin;
                $array['event']['horout'] = $horout;

                $dia_in = explode("/", $array['event']['fecin']);
                $dia = $dia_in[2] . '-' . $dia_in[1] . '-' . $dia_in[0];
            } else {
                $dia = $array['event']['fecin'];
            }

            $array['event']['descri'] = @$array['event']['contentFull'];

            if (is_array($array['event']['ciuin'])) {
                $array['event']['ciuin'] = $array['event']['ciuin']['codciu'];
            }

            if (is_array($array['event']['ciuout'])) {
                $array['event']['ciuout'] = $array['event']['ciuout']['codciu'];
            }

            if ($array['event']['catser'] == 'VUELO') {
                if ($array['event']['ciuin'] != '' AND $array['event']['ciuout'] != '') {
                    $array['event']['title'] = $array['event']['ciuin'] . '/' . $array['event']['ciuout'];
                } else {
                    $array['event']['title'] = $array['event']['codsvs'] . '( DATO INCOMPLETO )';
                }
            }

            $fecin_prime_original = $array['event']['fecin_prime'];
            $fecin_prime = $array['event']['fecin_prime'];

            $dias = $this->checkDates($fecin_prime, $dia, 'day');

            $array['event']['fecin_prime'] = date("d/m/Y", strtotime($fecin_prime));
            $array['dias'] = $dias;
            $array['dia'] = $dia;
            $array['update_events'] = (int)@$array['update_events'];
            $flag_bastar = (int)!($array['event']['bastar'] == $array['bastar']);

            if (is_array(@$array['event']['ciavue'])) {
                $array['event']['ciavue'] = $array['event']['ciavue']['codigo'];
            }

            if (is_array(@$array['event']['bastar'])) {
                $array['event']['bastar'] = $array['event']['bastar']['bastar'];
                $array['bastar'] = $array['event']['bastar'];
            }

            $bastar = '';
            $bastar_venta = [];
            $bastar_costo = [];

            $_data = [
                'bastar' => $array['bastar'],
                'fecin' => $array['event']['fecin'],
                'fecout' => $array['event']['fecout']
            ];

            $bastars = (array)$this->stellaService->search_bastar($array['event']['codsvs'], $_data);

            foreach ($bastars as $k => $v) {
                if ($bastar == '') {
                    $bastar = $v;
                }

                if ($v->vtacos == 'V') {
                    $bastar_venta = $v;
                }

                if ($v->vtacos == 'C') {
                    $bastar_costo = $v;
                }
            }

            $array['event']['fecope'] = date("d/m/Y");

            if ($array['event']['catser'] != 'VUELO') {
                $array['event']['bastar'] = @$bastar->bastar;
                $array['event']['desbas'] = @$bastar->desbas;
            }

            $array['event']['preped'] = @$bastar->preped;
            $array['event']['prefac'] = @$bastar->prefac;
            $array['event']['prevou'] = @$bastar->prevou;
            $array['event']['clasif'] = 5;
            $array['event']['infoad'] = $array['event']['descri'];

            if (@$array['event']['service']['descri'] != '' && @$array['event']['service']['descri'] != null) {
                $array['event']['descri'] = @$array['event']['service']['descri'];
            }
            if( $array['event']['descri'] == null ){
                $array['event']['descri'] = $array['event']['title'];
            }

            $costo_venta_unitario = (double)@$bastar_costo->valor;

            if (trim(@$bastar_costo->moneda) != 'USD' AND $costo_venta_unitario > 0) {
                $costo_venta_unitario = $costo_venta_unitario / $tipo_cambio;
            }

            $array['event']['moneda'] = $array['file']['moncot'];
            $array['event']['monvta'] = @$bastar_venta->moneda;
            $array['event']['moncos'] = @$bastar_costo->moneda;
            $array['event']['vtaloc'] = @$bastar_venta->valor;
            $array['event']['cosloc'] = @$bastar_costo->valor;
            $array['event']['vtauni'] = @$bastar_venta->valor;
            $array['event']['cosuni'] = $costo_venta_unitario;
            $array['event']['cantid'] = $array['file']['paxs'];
            $array['event']['piaced'] = $array['file']['piaced'];

            $array['event']['tarifa'] = @number_format($array['event']['vtauni'] * $array['event']['cantid'], 2);
            $array['event']['netpag'] = @number_format($array['event']['cosuni'] * $array['event']['cantid'], 2);
            $array['event']['iatced'] = @number_format($array['event']['tarifa'] * $array['event']['piaced'], 2);
            $array['event']['netfac'] = @number_format($array['event']['tarifa'] - $array['event']['iatced'], 2);
            $array['event']['netexe'] = $array['event']['netfac'];
            $array['event']['iatgan'] = @number_format($array['event']['tarifa'] - $array['event']['netpag'], 2);

            $array['event']['grvuni'] = 0;
            $array['event']['ivvuni'] = 0;
            $array['event']['grcuni'] = 0;
            $array['event']['ivcuni'] = 0;
            $array['event']['taruni'] = 0;

            $tipdoc = '';

            if (@$bastar->interm == 'N') {
                $tipdoc = 'FA';
            }

            if (@$bastar->interm == 'S') {
                $tipdoc = 'HI';
            }

            $array['event']['tipdoc'] = $tipdoc;
            $array['event']['diario'] = @$bastar->diario;
            $array['event']['paxuni'] = @$bastar->paxuni;
            $array['event']['cotiza'] = @$bastar->cotiza;
            $array['event']['vouche'] = @$bastar->vouche;
            $array['event']['itiner'] = @$bastar->itiner;
            $array['event']['vouemi'] = @$bastar->vouemi;
            $array['event']['nrovou'] = 0;
            $array['event']['ticket'] = $array['file']['ticket'];
            $array['event']['tktemi'] = $array['file']['ticket'];
            $array['event']['tipope'] = @$bastar->tipope;
            $array['event']['clase'] = @$bastar->clase;
            $array['event']['pain'] = @$bastar->pain;
            $array['event']['paiout'] = @$bastar->paiout;
            $array['event']['gruin'] = @$bastar->gruin;
            $array['event']['gruout'] = @$bastar->gruout;

            $event = $array['event'];

            $type = (array)$this->stellaService->update_service_file($nrofile, $nroite, $array);
//            //******
//            $file_id_ = File::where('file_number', $nrofile)->first()->id;
//            $service_file = FileService::where('file_id', $file_id_)->where('item_number', $nroite)->first();
//            $service_file->total_rooms = $array['event']['cantid'];
//            $service_file->code_request_book = $array['event']['preped'];
//            $service_file->code_request_invoice = $array['event']['prefac'];
//            $service_file->code_request_voucher = $array['event']['prevou'];
//            $service_file->base_code = $array['event']['bastar'];
//            $service_file->base_name_initial = $array['event']['desbas'];
//            $service_file->additional_information = $array['event']['infoad'];
//            $service_file->total_paxs = $array['event']['canpax'];
//            if( $array['event']['descri'] !== '' && $array['event']['descri'] !== null ){
//                $service_file->description = $array['event']['descri'];
//            }
//            $service_file->start_time = $array['event']['horin'];
//            $service_file->departure_time = $array['event']['horout'];
//            $service_file->city_in_iso = $array['event']['ciuin'];
//            $service_file->city_out_iso = $array['event']['ciuout'];
//            $service_file->date_in = convertDate( $array['event']['fecin'], '/', '-', 1);
//            $service_file->date_out = convertDate( $array['event']['fecout'], '/', '-', 1);
//            $service_file->save();
//
//            if($array['update_events'] == 1){
//                $file_services = FileService::where('file_id', $file_id_)
//                    ->where('id', '!=', $service_file->id)
//                    ->get();
//
//                foreach ($file_services as $file_service){
//                    if( ( $file_service->date_in == $fecin_prime_original && $file_service->start_time > $array['event']['horin_prime'] ) ||
//                            $file_service->date_in > $fecin_prime_original ){
//                        $file_service->date_in = Carbon::parse($file_service->date_in)->addDays($dias);
//                        $file_service->date_out = Carbon::parse($file_service->date_out)->addDays($dias);
//                        $file_service->save();
//                    }
//                }
//            }
//            //******

            if ($array['event']['relation'] > 0) {
//                return $array; die;
                $this->stellaService->update_relation_t21($nrofile, $nroite, $array);
            } else {
                $this->stellaService->create_relation_t21($nrofile, $nroite, $array);
            }

//            //***
//            $get_more_info_updated = $this->stellaService->get_service_complements($nrofile, $nroite);
//            $service_file->base_name_original = $get_more_info_updated[0]->desbas; // t01 *
//            $service_file->relation_nights = $get_more_info_updated[0]->relation; // t21 *
//            $service_file->airline_code = $get_more_info_updated[0]->ciavue; // t21 *
//            $service_file->airline_number = $get_more_info_updated[0]->nrovue; // t21 *
//            $service_file->save();
//            //***

            $_components = [];
            $component = [];
            $components = [];

            if ($flag_bastar == 1) {
                $this->stellaService->cancel_components($array['file']['nroref'], $array['event']['nroite']);
//                //***
//                $service_file->status_ifx = "XL";
//                $service_file->save();
//                //***

                $_array = [
                    'bastar' => $array['bastar']
                ];

                $_components = (array)$this->stellaService->search_components($array['event']['codsvs'], $_array);

                if (count($_components) > 0) {
                    foreach ($_components as $key => $value) {
                        $_component = $this->stellaService->find_component($value->codsvs);
                        $component = $_component[0];

                        $_data = [
                            'bastar' => $value->bastar,
                            'fecin' => $array['event']['fecin'],
                            'fecout' => $array['event']['fecout']
                        ];
                        $bastars = $this->stellaService->search_bastar($value->codsvs, $_data);
                        $bastar_venta = [];
                        $bastar_costo = [];
                        $bastar = '';

                        foreach ($bastars as $k => $v) {
                            if ($bastar == '') {
                                $bastar = $v;
                            }

                            if ($v->vtacos == 'V') {
                                $bastar_venta = $v;
                            }

                            if ($v->vtacos == 'C') {
                                $bastar_costo = $v;
                            }
                        }

                        if ($bastar != '') {
                            $costo_venta_unitario = @$bastar_costo->valor;

                            if (trim(@$bastar_costo->moneda) != 'USD') {
                                $costo_venta_unitario = $bastar_costo->valor / $tipo_cambio;
                            }

                            $_data = [
                                'nroite' => $array['event']['nroite'],
                                '_nroite' => 0,
                                'fecope' => date("d/m/Y"),
                                'tipope' => @$bastar->tipope,
                                'clase' => @$bastar->clase,
                                'tipsvs' => substr($component->tipo, 0, 3),
                                'codsvs' => $value->codsvs,
                                'descri' => $component->descri,
                                'bastar' => @$bastar->bastar,
                                'desbas' => @$bastar->desbas,
                                'preped' => @$bastar->preped,
                                'prefac' => @$bastar->prefac,
                                'prevou' => @$bastar->prevou,
                                'ciuin' => $component->ciudes,
                                'ciuout' => $component->ciuhas,
                                'fecin' => $array['event']['fecin'],
                                'fecout' => $array['event']['fecout'],
                                'horin' => $array['event']['horin'],
                                'horout' => $array['event']['horout'],
                                'cantid' => $value->cansvs,
                                'canpax' => $value->canpax,
                                'tippax' => 'ADL',
                                'grupo' => $array['file']['grupo'],
                                'estado' => 'OK',
                                'secpax' => 3.155,
                                'operad' => $array['file']['operad'],
                                'codsec' => $array['file']['codsec'],
                                'succli' => $array['file']['succli'],
                                'codcli' => $array['file']['codcli'],
                                'codven' => $array['file']['codven'],
                                'codope' => $array['file']['codope'],
                                // 'paiin' => $array['file']['paiopc'],
                                'moneda' => @$bastar->moneda,
                                'monvta' => @$bastar->moneda,
                                'moncos' => @$bastar->moneda,
                                'vtaloc' => @$bastar_venta->valor,
                                'cosloc' => @$bastar_costo->valor,
                                'vtauni' => @$bastar_venta->valor,
                                'cosuni' => $costo_venta_unitario,
                                'grvuni' => 0,
                                'ivvuni' => 0,
                                'grcuni' => 0,
                                'ivcuni' => 0,
                                'taruni' => 0,
                                'diario' => @$bastar->diario,
                                'paxuni' => @$bastar->paxuni,
                                'cansvs' => $value->cansvs,
                                'tarifa' => 0,
                                'online' => 0,
                                'offline' => 0,
                                'piagan' => 0,
                                'iatgan' => 0,
                                'povgan' => 0,
                                'ovegan' => 0,
                                'netpag' => 0,
                                'piaced' => 0,
                                'iatced' => 0,
                                'povced' => 0,
                                'oveced' => 0,
                                'netfac' => 0,
                                'netgra' => 0,
                                'netexe' => 0,
                                'piapro' => 0,
                                'iatpro' => 0,
                                'povpro' => 0,
                                'ovepro' => 0,
                                'tax1' => 0,
                                'tax2' => 0,
                                'tax3' => 0,
                                'cotiza' => @$bastar->cotiza,
                                'vouche' => @$bastar->vouche,
                                'itiner' => @$bastar->itiner,
                                'vouemi' => @$bastar->vouemi,
                                'nrovou' => 0,
                                'ticket' => $array['file']['ticket'],
                                'tktemi' => $array['file']['ticket'],
                                'docum' => '',
                                'docemi' => '',
                                'nrosuc' => '',
                                'serie' => '',
                                'tipdoc' => '',
                                'nrodoc' => '',
                                'viacom' => @$bastar->viacom,
                                'comemi' => '',
                                'asigna' => '',
                                'asiemi' => '',
                                'pain' => @$bastar->pain,
                                'paiout' => @$bastar->paiout,
                                'gruin' => @$bastar->gruin,
                                'gruout' => @$bastar->gruout
                            ];

                            $components[] = $_data;
                            // Crea Nuevo componentes en t13
                            $this->stellaService->create_component($array['file']['nroref'], $_data);
                        }
                    }
                }
            }

            //***
            $file_id_ = File::where('file_number', $nrofile)->first()->id;
            $import_ = $this->import_services($nrofile, $file_id_, '');

            $response = [
                'flag_bastar' => $flag_bastar,
                'bastars' => $bastars,
                'bastar' => $array['bastar'],
                'response' => $type,
                'components' => $_components,
                'components_post' => $components,
                'component' => $component,
                'type' => 'success',
                'event' => $event,
                'lang' => $array['lang'],
                'import' => $import_
            ];
        } catch (\Exception $ex) {
            $response = [
                'type' => 'error',
                'message' => $ex->getMessage(),
                'line' => $ex->getLine(),
            ];
        }

        return response()->json($response);
    }

    public function delete_service_file(Request $request, $nrofile, $nroite)
    {
        try {
            $_response = (array)$this->stellaService->delete_service_file($nrofile, $nroite);

            //***
            $file = File::where('file_number', $nrofile)->first();
            $file_service = FileService::where('file_id', $file->id)
                ->where('item_number', $nroite)->first();
            $file_service->status_ifx = "XL";
            $file_service->save();
            //***

            $response = [
                'response' => $_response,
                'type' => 'success',
                'event' => $nrofile,
                'lang' => $nroite
            ];
        } catch (\Exception $ex) {
            $response = [
                'type' => 'error',
                'message' => $ex->getMessage()
            ];
        }

        return response()->json($response);
    }

    public function save_component(Request $request)
    {
        try {

            $import_ = '';
            $array = (array)$request->all();
            $component_post = (array)$array['component'];

            if (is_array(@$component_post['service'])) {
                $component_post['codsvs'] = $component_post['service']['codigo'];
            }

            if (is_array(@$component_post['tarifa'])) {
                $component_post['bastar'] = $component_post['tarifa']['bastar'];
            }
            // t03
            $_component = $this->stellaService->find_component($component_post['codsvs']);
            $component = $_component[0];

            $_data = [
                'bastar' => $component_post['bastar'],
                'fecin' => $component_post['fecin'],
                'fecout' => $component_post['fecin']
            ];
            $bastars = $this->stellaService->search_bastar($component_post['codsvs'], $_data);
            $bastar_venta = [];
            $bastar_costo = [];
            $bastar = '';

            foreach ($bastars as $k => $v) {
                if ($bastar == '') {
                    $bastar = $v;
                }

                if ($v->vtacos == 'V') {
                    $bastar_venta = $v;
                }

                if ($v->vtacos == 'C') {
                    $bastar_costo = $v;
                }
            }

            $_response = '';
            $components = [];
            $message = '';

            if ($bastar != '') {
                $_data = [
                    'nroite' => $array['event']['nroite'],
                    '_nroite' => (int)$component_post['nroite'],
                    'fecope' => date("d/m/Y"),
                    'tipope' => @$bastar->tipope,
                    'clase' => @$bastar->clase,
                    'tipsvs' => substr($component->tipo, 0, 3),
                    'codsvs' => $component_post['codsvs'],
                    'descri' => $component->descri,
                    'bastar' => @$bastar->bastar,
                    'desbas' => @$bastar->desbas,
                    'preped' => @$bastar->preped,
                    'prefac' => @$bastar->prefac,
                    'prevou' => @$bastar->prevou,
                    'ciuin' => $component->ciudes,
                    'ciuout' => $component->ciuhas,
                    'fecin' => $component_post['fecin'],
                    'fecout' => $component_post['fecin'],
                    'horin' => $component_post['horin'],
                    'horout' => $component_post['horout'],
                    'cantid' => $component_post['cansvs'],
                    'canpax' => $component_post['canpax'],
                    'tippax' => 'ADL',
                    'grupo' => $array['file']['grupo'],
                    'estado' => 'OK',
                    'secpax' => 3.155,
                    'operad' => $array['file']['operad'],
                    'codsec' => $array['file']['codsec'],
                    'succli' => $array['file']['succli'],
                    'codcli' => $array['file']['codcli'],
                    'codven' => $array['file']['codven'],
                    'codope' => $array['file']['codope'],
                    // 'paiin' => $array['file']['paiopc'],
                    'moneda' => @$bastar->moneda,
                    'monvta' => @$bastar->moneda,
                    'moncos' => @$bastar->moneda,
                    'vtaloc' => @$bastar_venta->valor,
                    'cosloc' => @$bastar_costo->valor,
                    'vtauni' => @$bastar_costo->valor,
                    'cosuni' => @$bastar_costo->valor,
                    'grvuni' => 0,
                    'ivvuni' => 0,
                    'grcuni' => 0,
                    'ivcuni' => 0,
                    'taruni' => 0,
                    'diario' => @$bastar->diario,
                    'paxuni' => @$bastar->paxuni,
                    'cansvs' => $component_post['cansvs'],
                    'tarifa' => 0,
                    'online' => 0,
                    'offline' => 0,
                    'piagan' => 0,
                    'iatgan' => 0,
                    'povgan' => 0,
                    'ovegan' => 0,
                    'netpag' => 0,
                    'piaced' => 0,
                    'iatced' => 0,
                    'povced' => 0,
                    'oveced' => 0,
                    'netfac' => 0,
                    'netgra' => 0,
                    'netexe' => 0,
                    'piapro' => 0,
                    'iatpro' => 0,
                    'povpro' => 0,
                    'ovepro' => 0,
                    'tax1' => 0,
                    'tax2' => 0,
                    'tax3' => 0,
                    'cotiza' => @$bastar->cotiza,
                    'vouche' => @$bastar->vouche,
                    'itiner' => @$bastar->itiner,
                    'vouemi' => @$bastar->vouemi,
                    'nrovou' => 0,
                    'ticket' => $array['file']['ticket'],
                    'tktemi' => $array['file']['ticket'],
                    'docum' => '',
                    'docemi' => '',
                    'nrosuc' => '',
                    'serie' => '',
                    'tipdoc' => '',
                    'nrodoc' => '',
                    'viacom' => @$bastar->viacom,
                    'comemi' => '',
                    'asigna' => '',
                    'asiemi' => '',
                    'pain' => @$bastar->pain,
                    'paiout' => @$bastar->paiout,
                    'gruin' => @$bastar->gruin,
                    'gruout' => @$bastar->gruout
                ];

                $components[] = $_data;
                $_response = $this->stellaService->create_component($array['file']['nroref'], $_data);

                //***
                $file_id_ = File::where('file_number', $array['file']['nroref'])->first()->id;
                $services_current_nroites = FileService::where('file_id', $file_id_)->pluck('item_number');
                $nroites_not_in = [];
                foreach ( $services_current_nroites as $current_nroite ){
                    array_push( $nroites_not_in, $current_nroite );
                }
                $nroites_not_in = implode(',', $nroites_not_in);
                $import_ = $this->import_services($array['file']['nroref'], $file_id_, $nroites_not_in);

            } else {
                $message = 'No se encontró una base de tarifas para el servicio seleccionado.';
            }

            $response = [
                'components' => $components,
                'response' => $_response,
                'type' => (count($components) > 0) ? 'success' : 'error',
                'component' => $component,
                'message' => $message,
                'import' => $import_
            ];
        } catch (\Exception $ex) {
            $response = [
                'type' => 'error',
                'message' => $ex->getMessage()
            ];
        }

        return response()->json($response);
    }

    public function delete_component(Request $request)
    {
        try {
            $file = $request->__get('file');
            $nroite = $request->__get('component');

            $file_service_id = $request->__get('file_service_id');

            $_response = $this->stellaService->delete_component($file, $nroite);

            //***
            $file_service = FileService::find($file_service_id);
            $file_service->status_ifx = "XL";
            $file_service->save();
            //***

            $response = [
                'response' => $_response,
                'type' => 'success',
            ];
        } catch (\Exception $ex) {
            $response = [
                'type' => 'error',
                'message' => $ex->getMessage()
            ];
        }

        return response()->json($response);
    }

    public function search_services_by_types(Request $request)
    {
        try {
            $type = $request->__get('type');
            $filter = $request->__get('filter_service');
            $ciuin = $request->__get('ciuin');
            $ciuout = $request->__get('ciuout');
            $fecin = $request->__get('fecin');
            $fecout = $request->__get('fecout');

            if (is_array($ciuin)) {
                $ciuin = $ciuin['codciu'];
            }

            if (is_array($ciuout)) {
                $ciuout = $ciuout['codciu'];
            }

            $query = '%'.$request->__get('query').'%';
            $ciuin = '%'.$ciuin.'%';
            $ciuout = '%'.$ciuout.'%';

            $array = [
                'filter' => ($type == 'RES') ? $filter : '',
                'type' => $type,
                'query' => $query,
                'ciuin' => $ciuin,
                'ciuout' => $ciuout,
                'fecin' => $fecin,
                'fecout' => $fecout
            ];

            $response = (array)$this->stellaService->search_services_by_types($array);
        } catch (\Exception $ex) {
            $response = [
                'type' => 'error',
                'message' => $ex->getMessage()
            ];
        }

        return response()->json($response);
    }

    public function search_services_component(Request $request)
    {
        try {
            $query = '%'.$request->__get('query').'%';
            $ciuin = '%'.trim($request->__get('ciuin')).'%';
            $ciuout = '%'.trim($request->__get('ciuout')).'%';

            $array = [
                'query' => $query,
                'ciuin' => $ciuin,
                'ciuout' => $ciuout
            ];

            $response = (array)$this->stellaService->search_services_component($array);
        } catch (\Exception $ex) {
            $response = [
                'type' => 'error',
                'message' => $ex->getMessage()
            ];
        }

        return response()->json($response);
    }

    public function search_rates_services(Request $request)
    {
        try {
            $codsvs = $request->__get('service');
            $fecin = date("d/m/Y", strtotime($request->__get('fecin')));
            $fecout = date("d/m/Y", strtotime($request->__get('fecout')));
            $query = '%'.trim($request->__get('query')).'%';

            $_data = [
                'fecin' => $fecin,
                'fecout' => $fecout,
                'query' => $query
            ];
            $response = $this->stellaService->search_bastar_service($codsvs, $_data);
        } catch (\Exception $ex) {
            $response = [
                'type' => 'error',
                'message' => $ex->getMessage()
            ];
        }

        return response()->json($response);
    }

    public function update(Request $request, $nrofile)
    {
        try {
            $file = (array)$request->__get('file');

            $dia_in = explode("/", $file['diain_show']);
            $dia = $dia_in[2].'-'.$dia_in[1].'-'.$dia_in[0];

            $dias = $this->checkDates($file['diain'], $dia, 'day');

            $file['fecin_prime'] = date("d/m/Y", strtotime($file['diain']));

            $file['fecha'] = $file['fecha_show'];
            $file['diain'] = $file['diain_show'];
            $file['diaout'] = $file['diaout_show'];

            if (is_array($file['codcli'])) {
                $file['codcli'] = $file['codcli']['client_code'];
            }

            $file['dias'] = $dias;
            $file['fecsis'] = date("d/m/Y");
            $file['horsis'] = date("H:i:s");

            $this->stellaService->update_file($nrofile, $file);

            $client = Client::where('code', $file['codcli'])->first();
            if( $client ){
                $file_ = File::find($file['id']);
                $file_->client_id = $client->id;
                $file_->order_number = $file['nroped'];
                $file_->sector_code = $file['codsec'];
                $file_->tariff = $file['tarifa'];
                $file_->executive_code = $file['codven'];
                $file_->executive_code_sale = $file['codope'];
                $file_->applicant = $file['solici'];
                $file_->file_code_agency = $file['refext'];
                $file_->description = $file['descri'];
                $file_->lang = $file['idioma'];
                $file_->created_at = Carbon::parse($file['fecha']);
                $file_->date_in = convertDate($file['diain_show'], '/', '-', 1);
                $file_->date_out = convertDate($file['diaout_show'], '/', '-', 1);
                $file_->adults = $file['canadl'];
                $file_->children = $file['canchd'];
                $file_->infants = $file['caninf'];
                $file_->observation = $file['observ'];
                $file_->executive_code_process = $file['operad'];
                $file_->promotion = $file['promos'];
                $file_->save();
                // ***

                $canpax = (int)$file['canadl'] + (int)$file['canchd'];
                $file_services = FileService::where('file_id', $file_->id)->get();

                foreach ($file_services as $file_service){
                    if( $file['paxs'] === $file_service->total_paxs){
                        $file_service->total_paxs = $canpax;
                    }
                    $file_service->date_in = Carbon::parse($file_service->date_in)->addDays($dias);
                    $file_service->date_out = Carbon::parse($file_service->date_out)->addDays($dias);
                    $file_service->save();
                }

                //----
                // total_rooms = cantid
                // total_paxs = canpax

                $_data = [
                    'paxs' => $file['paxs'],
                    'paxs_prime' => $file['paxs_prime']
                ];
                $this->stellaService->update_passengers_file($nrofile, $_data);

                foreach ($file_services as $file_service){
                    if( $file['paxs_prime'] === $file_service->total_paxs){
                        $file_service->total_paxs =  $file['paxs'];
                    }
                    $file_service->save();

                    $services_ids = FileService::where('code', $file_service->code)->pluck('id');
                    $file_service->flag_accommodation = FileAccommodation::whereIn('file_service_id', $services_ids)->count();
                }

                $response = [
                    'nrofile' => $nrofile,
                    'file' => $file,
                    'file_' => $file_,
                    'services' => $file_services
                ];

            } else {
                $response = [
                    'type' => 'error',
                    'message' => "Client not found"
                ];
            }


        } catch (\Exception $ex) {
            $response = [
                'type' => 'error',
                'message' => $ex->getMessage()
            ];
        }

        return response()->json($response);
    }

    public function save_service_passengers_file(Request $request, $nrofile, $nroite)
    {
        try {
            $nrosec_passengers = (array)$request->input('nrosec_passengers');
            $passengers = (array)$request->input('passengers');
            $tipsvs = $request->input('tipsvs');
            $codsvs = $request->input('codsvs');
            $bastar = $request->input('bastar');

            $_array = [
                'nrosec_passengers' => $nrosec_passengers,
                'passengers' => $passengers,
                'tipsvs' => $tipsvs,
                'codsvs' => $codsvs,
                'bastar' => $bastar
            ];

            $save_ifx = $this->stellaService->save_passengers_file($nrofile, $nroite, $_array);
            //$save_ifx = [];

            $save_mysql = $this->save_service_passenger( $nrofile, $nroite, $_array );

            $response = [
                'success' => true,
                'save_ifx' => $save_ifx,
                'save_mysql' => $save_mysql
            ];
        } catch (\Exception $ex) {
            $response = [
                'success' => false,
                'message' => $ex->getMessage()
            ];
        }

        return response()->json($response);
    }

    public function save_passengers_file(Request $request, $nrofile)
    {
        try
        {
            $paxs = (int)$request->__get('paxs');
            $passengers = (array)$request->__get('passengers');
            $response_paxs = []; $services = [];

            if(count($passengers) > 0)
            {
                //            $services = $this->stellaService->search_services_by_paxs($nrofile, $paxs);
                // "select * from t13 where nroemp = ? and ideref = ? and nroref = ? and canpax = ? and estado = ?"
                $file = File::where('file_number', $nrofile)->first();

                if($file)
                {
                    $services_ = FileService::where('file_id', $file->id)
                        ->where('status_ifx', "OK")
                        ->where('total_paxs', $paxs)
                        ->get();
                    $services = $this->translate_file_services_ifx($file, $services_, 1);

                    $nrosec_passengers = [];
                    for ($i = 0; $i < $paxs; $i++) {
                        $nrosec_passengers[$i] = 1;
                    }

                    foreach ($services as $key => $value) {
                        $value = (array)$value;

                        $_array = [
                            'nrosec_passengers' => $nrosec_passengers,
                            'passengers' => $passengers,
                            'tipsvs' => $value['clase'],
                            'codsvs' => $value['codsvs'],
                            'bastar' => $value['bastar']
                        ];

                        $response_paxs[$key] = $_array;
                        $this->stellaService->save_passengers_file($nrofile, $value['nroite'], $_array);
                        //**
                        $this->save_service_passenger($nrofile, $value['nroite'], $_array);
                        //**
                    }
                }
            }

            $response = [
                'type' => 'success',
                'response_paxs' => $response_paxs,
                'services' => $services,
                'message' => 'Lista de pasajeros actualizada.'
            ];
        }
        catch (\Exception $ex)
        {
            $response = $this->throwError($ex);
        }

        return response()->json($response);
    }

    public function save_service_passenger($nrofile, $nroite, $_array)
    {
        try {

            $file = File::where('file_number', $nrofile)->first();
            $file_service = FileService::where('file_id', $file->id)->where('item_number', $nroite)->first();

            if( $file_service ){
                // DELETE ACCOMMODATIONS CURRENT
                FileAccommodation::where('file_service_id', $file_service->id)->delete();
                // 'UPDATE SERVICE
                $file_service->base_code = $_array['bastar'];
                $file_service->total_paxs = count($_array['nrosec_passengers']);
                $file_service->total_rooms = count($_array['nrosec_passengers']);
                $file_service->save();
                // INSERTS ACCOMMODATIONS
                foreach ($_array['nrosec_passengers'] as $key => $nrosec_passenger){
                    if( $nrosec_passenger === 1 || $nrosec_passenger === true ){
//                        $passenger = $_array['passengers'][$key]['nombre'];
                        $nrosec = $_array['passengers'][$key]['nrosec'];
                        $passenger_ = ReservationPassenger::where('reservation_id', $file->reservation_id)
                            ->where('sequence_number', $nrosec)->first();
                        if($passenger_){
                            $new_accommodation = new FileAccommodation();
                            $new_accommodation->file_service_id = $file_service->id;
                            $new_accommodation->reservation_passenger_id = $passenger_->id;
                            $new_accommodation->save();
                        }
                    }
                }
            }

            $response = [
                'type' => 'success',
                'message' => 'Proceso de guardado correcto'
            ];
        } catch (\Exception $ex) {
            $response = [
                'type' => 'error',
                'message' => $ex->getMessage()
            ];
        }

        return $response;
    }

    public function update_passengers_file(Request $request)
    {
        try {
            $file = (array)$request->__get('file');

            $data = [
                'paxs_prime' => $file['paxs_prime'],
                'paxs' => $file['paxs']
            ];

            $response = (array)$this->stellaService->update_passengers_file($file['nrofile'], $data);

            $response = [
                'type' => 'success',
                'response' => $response,
                'message' => 'Lista de pasajeros actualizada.'
            ];
        } catch (\Exception $ex) {
            $response = [
                'type' => 'error',
                'message' => $ex->getMessage()
            ];
        }

        return response()->json($response);
    }

    public function search_remarks(Request $request, $_code)
    {
        $_service = (array)$request->__get('service');
        $response_in = [];
        $response_out = [];
        $content = '';

        $fecin = explode("/", $_service['fecin']);
        $fecout = explode("/", $_service['fecout']);

        $code_in = $_code.((count($fecin) > 1) ? $fecin[2] : date("Y", strtotime($_service['fecin'])));
        for ($i = strlen($code_in); $i < 10; $i++) {
            $code_in .= '0';
        }

        $response_in = (array)$this->stellaService->search_remarks($code_in);

        if (count($response_in) > 0) {
            $anio = ((count($fecin) > 1) ? $fecin[2] : date("Y", strtotime($_service['fecin'])));

            $content .= "AÑO: ".$anio."\n";

            foreach ($response_in as $k => $v) {
                $content .= trim($v->texto)."\n";
            }
        }

        $code_out = $_code.((count($fecout) > 1) ? $fecout[2] : date("Y", strtotime($_service['fecout'])));
        for ($i = strlen($code_out); $i < 10; $i++) {
            $code_out .= '0';
        }

        if ($_service['fecin'] != $_service['fecout']) {
            $response_out = (array)$this->stellaService->search_remarks($code_out);
        }

        if (count($response_out) > 0) {
            $content .= "\nAÑO: ".((count($fecout) > 1) ? $fecout[2] : date("Y", strtotime($_service['fecout'])))."\n";

            foreach ($response_out as $k => $v) {
                $content .= trim($v->texto)."\n";
            }
        }

        $response = [
            'content' => $content,
            'code_in' => $code_in,
            'code_out' => $code_out,
            'response_in' => $response_in,
            'response_out' => $response_out
        ];

        /*
        }
        catch(\Exception $ex)
        {
            $response = [
                'type' => 'error',
                'message' => $ex->getMessage()
            ];
        }
        */

        return response()->json($response);
    }

    public function search_restrictions(Request $request, $_code)
    {
        try {
            $service = (object)$request->_get('service');
            $fecin = explode("/", $service->fecin);
            $__fecha = $fecin[2].'-'.$fecin[1].'-'.$fecin[0];

            $response = (array)$this->stellaService->search_restrictions($_code);
            $content = '';

            $days = [
                "lunes" => "Lunes",
                "martes" => "Martes",
                "mierco" => "Miercoles",
                "jueves" => "Jueves",
                "vierne" => "Viernes",
                "sabado" => "Sábado",
                "doming" => "Domingo"
            ];

            foreach ($response as $k => $v) {
                if (($v->identi == 'R' AND ($v->fecdes == $__fecha OR $v->fechas == $__fecha)) OR $v->identi != 'R') {
                    if ($v->identi == 'O') {
                        $content .= "Disponible\n";
                    }

                    if ($v->identi == 'R') {
                        $content .= "No Disponible\n";
                    }

                    if ($v->identi == 'D') {
                        $content .= "Horarios: $v->hordes hasta $v->horhas\n\n";
                    } else {
                        if (($v->fecdes != '' AND $v->fecdes != null) OR ($v->fechas != '' OR $v->fechas != null) OR ($v->hordes != '' AND $v->hordes != null) OR ($v->horhas != '' AND $v->horhas != null)) {
                            $content .= "Aplicado desde ".$v->fecdes." ".$v->hordes." hasta ".$v->fechas." ".$v->horhas."\n";
                        }

                        if ($v->fecdes == '' OR $v->fecdes == null OR $v->fechas == '' OR $v->fechas == null) {
                            $content .= "En los siguientes días: \n\n";

                            foreach ($days as $d => $day) {
                                if ($v->$d == "X") {
                                    $content .= $day."\n";
                                }
                            }
                        }
                    }
                }
            }

            $response = [
                'content' => $content,
                'response' => $response,
                'code' => $_code
            ];
        } catch (\Exception $ex) {
            $response = [
                'type' => 'error',
                'message' => $ex->getMessage()
            ];
        }

        return response()->json($response);
    }

    public function search_bastar(Request $request, $_code)
    {
        try {
            $_service = (array)$request->__get('service');
            $tipser = $request->__get('tipser');
            $paxs = $_service['canpax'];
            $paxs = ($paxs < 10) ? ('0'.$paxs) : $paxs;
            $year = explode("-", $_service['fecin']);

            $fecin = $_service['fecin'];
            $fecout = $_service['fecout'];
            $response = [];

            if (count($year) == 1) {
                /*
                $year = explode("/", $year[0]);
                $year = $year[2];
                */
            } else {
                $fecin = date("d/m/Y", strtotime($fecin));
                $fecout = date("d/m/Y", strtotime($fecout));
                // $year = $year[0];
            }

            // $year = substr($year, 2, 2);

            $_bastar = '';
            $bastars = [];
            $__response = [];

            switch ($tipser) {
                case 'TRAS':
                    {
                        $_bastar = '20'.$paxs;
                    };
                    break;
                case 'RES':
                    {
                        $array = [
                            'code' => $_code,
                            'fecin' => $fecin,
                            'fecout' => $fecout
                        ];

                        $bastars = (array)$this->stellaService->search_bastars_service($_code, $array);
                    };
                    break;
                case 'PAQ':
                    {
                        if (@$_service['service']['prefac'] == '000000' OR @$_service['prefac'] == '000000') {
                            $_bastar = '20'.$paxs;
                        } else {

                        }
                    };
                    break;
                case 'TOUR':
                    {
                        $__response = (array)$this->stellaService->filter_tour($_code);
                        $__response = (array)@$__response[0];

                        if (is_array($__response) AND count($__response) > 0) {
                            $paxs = ($__response['canpax'] > 9) ? $__response['canpax'] : ('0'.$__response['canpax']);
                            $_bastar = ($__response['clasif'] == 'M') ? $__response['bastar'] : ('20'.$paxs);
                        } else {
                            $_bastar = '20'.$paxs;
                        }
                    };
                    break;
                case 'VUELO':
                    {
                        $_bastar = '2000';
                    };
                    break;
                case 'ENT':
                    {
                        $_bastar = 'M100';
                    };
                    break;
                case 'ASI':
                    {
                        $_bastar = 'ASI';
                    };
                    break;
                case 'MISC':
                    {
                        $_bastar = 'MISC';
                    };
                    break;
                case 'TREN':
                    {
                        $_bastar = 'TREN';
                    };
                    break;
            }

            $ignore = ['M100', 'ASI', 'MISC', 'TREN'];

            if ($_bastar != '') {
                $array = [
                    'code' => $_code,
                    'bastar' => (!in_array($_bastar, $ignore)) ? $_bastar : '',
                    'fecin' => $fecin,
                    'fecout' => $fecout
                ];

                $bastars = (array)$this->stellaService->search_bastar($_code, $array);
            }

            $all_bastars = [];

            foreach ($bastars as $key => $value) {
                if ($value->vtacos == 'V') {
                    $all_bastars[] = $value;
                }
            }

            if ($tipser == 'RES' || $tipser == 'ASI' || $tipser == 'MISC' || $tipser == 'TREN') {
                if (count($all_bastars) > 0) {
                    $_bastar = @$all_bastars[0]->bastar;
                }
                // $_bastar = @$bastars[0]['bastar'];
            }

            if ($tipser == 'VUELO') {
                $all_bastars[] = [
                    'bastar' => $_bastar
                ];
            }

            $response = [
                'type' => $tipser,
                'bastar' => $_bastar,
                'bastars' => $all_bastars
            ];
        } catch (\Exception $ex) {
            $response = [
                'type' => 'error',
                'message' => $ex->getMessage()
            ];
        }

        return response()->json($response);
    }

    public function flight_info(Request $request)
    {
        $params = http_build_query($request->all());
        $ch = curl_init(sprintf('%s?%s', 'http://api.aviationstack.com/v1/flights', $params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        $api_result = json_decode($response, true);

        /*
        foreach ($api_result['results'] as $flight) {
            if (!$flight['live']['is_ground']) {
                echo sprintf("%s flight %s from %s (%s) to %s (%s) is in the air.",
                    $flight['airline']['name'],
                    $flight['flight']['iata'],
                    $flight['departure']['airport'],
                    $flight['departure']['iata'],
                    $flight['arrival']['airport'],
                    $flight['arrival']['iata']
                ), PHP_EOL;
            }
        }
        */

        return response()->json($api_result);
    }

    public function flight_info_dos(Request $request)
    {
        $username = 'kluizsv';
        $password = '05c91d104a6c18f8b76b9fd569b8192de6663371';

        $options = array(
            'trace' => true,
            'exceptions' => 0,
            'login' => $username,
            'password' => $password,
        );
        $client = new \SoapClient('http://flightxml.flightaware.com/soap/FlightXML2/wsdl', $options);

        // get the weather.
        $params = array();
        // $result = $client->FlightInfo($params);
        $result = $client->AllAirlines();
        print_r($result);
    }

    // FlightStats
    public function flight_stats(Request $request)
    {
        // Now set some options (most are optional)
        $today = getdate();
        $url = "https://api.flightstats.com/flex/flightstatus/rest/v2/json/airport/status/SJC/dep/$today[year]/$today[mon]/$today[mday]/14?appId=baf80a89&appKey=5d343e19eb74a7809f6f3fc15a4a99de&utc=false&numHours=1&maxFlights=5";

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // Don't print the result
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_FAILONERROR, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // Don't verify SSL connection
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); //         ""           ""
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json")); // Send as JSON


        $return = curl_exec($curl);

        echo($return);
    }

    public function toggle_view_hotels(Request $request)
    {
        $limite = 2;
        $file = $request->__get('file');
        $adults = $request->__get('adults');
        $child = $request->__get('child');

        /*
        $reference_quote = $this->stellaService->reference_quote($file); $quote_id = 0;

        if(count($reference_quote) > 0)
        {
            $quote_id = $reference_quote[0]['nrore2'];
        }
        else
        {
            $reservation = Reservation::where('booking_code', '=', $file)->where('entity', '=', 'QUOTE')->first();
            $quote_id = @$reservation->object_id;
        }

        if((int) $quote_id > 0)
        {
            $people = QuotePeople::where('quote_id', '=', $quote_id)->first();
            $adults = $people->adults; $child = $people->child;
        }
        */

        $adults_for_room = ceil($adults / $limite);
        $resto = ceil($adults % $limite);
        $childs_for_room = $child;
        $rooms = [];
        $passengers = $request->__get('passengers');
        $ages = [];

        foreach ($passengers as $key => $value) {
            if ($value['tipo'] == 'CHD' AND $value['fecnac'] != '' AND $value['fecnac'] != null) {
                $fecnac = explode("/", $value['fecnac']);
                $fecha = $fecnac[2].'-'.$fecnac[1].'-'.$fecnac[0];
//                $ages[] = $this->calculaedad($fecha);
            }
        }

        for ($i = 1; $i <= $adults_for_room; $i++) {
            $rooms[] = [
                'room' => $i,
                'adults' => (($i == $adults_for_room AND $resto > 0) ? $resto : $limite),
                'child' => (($childs_for_room >= $i) ? 1 : 0),
                'ages_child' => [
                    [
                        'child' => (($childs_for_room >= $i) ? 1 : 0),
                        'age' => (int)@$ages[$i]
                    ]
                ]
            ];
        }

        $destiny_ = $request->__get('ciuin');
        $hotels_search_code_ = $request->__get('hotels_search_code');
        if( $request->__get('hotel_code') ){
            $hotel_code = $request->__get('hotel_code');
            $hotel_ = ChannelHotel::where('code', $hotel_code)->first();
            $hotel = Hotel::where('id', $hotel_->hotel_id)
                ->with(['country.translations'=>function($query){
                    $query->where('language_id',1);
                }, 'city.translations'=>function($query){
                    $query->where('language_id',1);
                }])
                ->first();
            $hotels_search_code_ = $hotel_code;
//            { "code": "PE,LIM", "label": "Perú,Lima" }
            $destiny_ = [
                "code" => $hotel->country->iso.",".$hotel->city->iso,
                "label" => $hotel->country->translations[0]->value.",".$hotel->city->translations[0]->value,
            ];
        }

        $items = [
            'file' => $file,
            'quantity_persons_rooms' => $rooms,
            'quantity_adults' => $request->__get('adults'),
            'quantity_child' => $request->__get('child'),
            'quantity_rooms' => count($rooms),
            'quantity_persons' => (((int)$request->__get('adults')) + ((int)$request->__get('child'))),
            'date_from' => $request->__get('fecini'),
            'date_to' => $request->__get('fecfin'),
            'destiny' => $destiny_,
            'typeclass_id' => $request->__get('typeclass_id'),
            'hotels_search_code' => $hotels_search_code_
        ];

        return response()->json([
            'items' => $items
            //'reference_quote' => $reference_quote
        ]);
    }

    public function reservation_hotel_frontend(Request $request)
    {
        $nrofile = $request->__get('nrofile');
        $fecin = $request->__get('fecin');
        $hotel = $request->__get('hotel');
        $sql = "select * from aurora_prod.reservations_hotels_rates_plans_rooms where reservations_hotel_id IN (select id from aurora_prod.reservations_hotels where reservation_id IN (select id from aurora_prod.reservations where file_code = ?) and hotel_code = ? and check_in <= ? and check_out >= ?)";
        $response = DB::select($sql, [$nrofile, $hotel, $fecin, $fecin]);

        return response()->json($response);
    }

    public function reservation_hotel_frontend_id(Request $request)
    {
        $nrofile = $request->__get('nrofile');
        $fecin = $request->__get('fecin');
        $hotel = $request->__get('hotel');
        $sql = "select id from aurora_prod.reservations_hotels where reservation_id IN (select id from aurora_prod.reservations where file_code = ?) and hotel_code = ? and check_in <= ? and check_out >= ?";
        $response = DB::select($sql, [$nrofile, $hotel, $fecin, $fecin]);

        return response()->json($response);
    }

    public function cancel_hotel(Request $request)
    {
        $event = (object)$request->__get('event');
        $nroref = $request->__get('nroref');
        $variation = (@$request->__get('variation') != '') ? $request->__get('variation') : '';
        $room = (@$request->__get('room') != '') ? $request->__get('room') : '';

        try {
            $data = [
                'file_code' => $nroref,
                'reservation_hotel_id' => $event->id_hotel,
                'message_provider' => ""
            ];

            $rooms = [];

            if ($variation != '') {
                foreach ($variation['rooms'] as $key => $value) {
                    $rooms[] = @$value['room_mysql']['id'];
                }
            }

            if ($room != '') {
                $rooms = [
                    @$room['room_mysql']['id']
                ];
            }

            if (count($rooms) > 0) {
                $data['reservation_hotel_room_id'] = $rooms;
            }

            $reservation = $this->cancelReservationHotel($data);
            // Toca *t13g(file_servicios) - *t13 - *t21(file_servicios) - t13e - tlo - t19i
            $update_in_mysql = $this->update_import_services($nroref);

            $response = ['success' => true, 'data' => $reservation, 'post' => $data, 'import'=> $update_in_mysql];
        } catch (\Exception $e) {
            $response = ['success' => false, 'error' => $e->getMessage()];
        }

        return response()->json($response);
    }

    public function detail_file_quote(Request $request)
    {
        try
        {
            $quote_id = $request->__get('quote_id');
            $reservation = Reservation::with([
                'reservationsHotel.hotel.translations',
                'reservationsHotel.hotel.city',
                'reservationsService.service.service_translations',
                'reservationsService.service.serviceOrigin.city',
                'reservationsService.service.serviceOrigin.country',
                'reservationsService.service.serviceDestination.city',
                'reservationsService.service.serviceDestination.country',
                'reservationsFlight'
            ])
                ->where('entity', '=', 'Quote')
                ->where('object_id', '=', $quote_id)
                ->first();

            return response()->json([
                'type' => 'success',
                'reservation' => $reservation
            ]);
        }
        catch(\Exception $ex)
        {
            return response()->json([
                'type' => 'error',
                'message' => $ex->getMessage(),
                'file' => $ex->getFile()
            ]);
        }
    }
}
