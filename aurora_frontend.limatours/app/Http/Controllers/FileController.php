<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Clients;
use App\Notification;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PassengersImport;

class FileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
//        $this->middleware('permission:board.view');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function dashboard(Request $request)
    {
        $data = ['nrofile' => $request->__get('file')];

        return view('files.dashboard')->with('data', $data);
    }

    public function order_services_file(Request $request)
    {
        $_services = (array) $request->__get('services'); $_city = $request->__get('city');
        $lang = $request->__get('lang'); $services = [];
        $_inicio = 4; $_fin = 5; $flag_date = ''; $cities = []; $_cities = [];

        $ignore = [
            '3' => [
                'G', 'T'
            ],
            '2' => [
                'G', 'T'
            ],
            '0' => [
                '2098', '2099'
            ]
        ];

        $filters = (array) $request->__get('filters');

        $icons = [
            '' => '',
            'HTL' => 'icon-briefcase',
            'AEI' => 'icon-flight',
            'AEC' => 'icon-flight',
            'AEIFLT' => 'icon-flight',
            'AECFLT' => 'icon-flight',
            'TOT' => 'icon-car',
            'TIN' => 'icon-car',
            'EXC' => 'icon-bus',
            'RES' => 'icon-am-food',
            'NET' => 'icon-bus',
            'MUS' => 'icon-tag',
            'TRN' => 'icon-am-bus',
            'TRF' => 'icon-car',
        ];

        $_ignore = []; $hotels = []; $type_room = [];
        $simple_general = 0; $doble_general = 0; $triple_general = 0;

        foreach($_services as $key => $value)
        {
            $variations = [];

            if($value['catser'] == 'HOT' AND !in_array($key, $_ignore, true))
            {
                $id_hotel = $this->searchReservationHotelID($value['nroref'], $value['codsvs'], $value['fecin']);
                $rooms = $this->searchReservationHotel($value['nroref'], $value['codsvs'], $value['fecin']);

                $item = (array) $value;
                $variations[] = $item;

                $type_room[$value['codsvs']][$value['nroite']] = ($value['canpax'] / $value['cantid']);

                foreach($_services as $k => $v)
                {
                    if( $v['codsvs'] === $value['codsvs'] AND $v['fecin'] === $value['fecin'] AND
                        $v['fecout'] === $value['fecout'] AND $v['catser'] === 'HOT' AND
                        $v['nroite'] !== $value['nroite'] ) {
                            $item = (array) $v;
                            $variations[] = $item;

                            $type_room[$v['codsvs']][$v['nroite']] = ($v['canpax'] / $v['cantid']);
                            $_ignore[] = $k;
                    }
                }

                $_services[$key]['variations'] = $variations;
                $_services[$key]['all_rooms_mysql'] = $rooms;
                $_services[$key]['id_hotel'] = $id_hotel;

                $simple = 0; $doble = 0; $triple = 0;

                foreach($variations as $k => $v)
                {
                    $type = $v['canpax'] / $v['cantid'];

                    if($type == 1)
                    {
                        $simple += $v['cantid'];
                    }

                    if($type == 2)
                    {
                        $doble += $v['cantid'];
                    }

                    if($type == 3)
                    {
                        $triple += $v['cantid'];
                    }
                }

                $simple_general = ($simple_general > 0) ? $simple_general : $simple;
                $doble_general = ($doble_general > 0) ? $doble_general : $doble;
                $triple_general = ($triple_general > 0) ? $triple_general : $triple;

                $_services[$key]['check'] = ($simple == $simple_general AND $doble == $doble_general AND $triple == $triple_general) ? true : false;
                $_services[$key]['SGL'] = $simple;
                $_services[$key]['DBL'] = $doble;
                $_services[$key]['TPL'] = $triple;

                $hotels[] = $_services[$key];
            }
        }

        foreach($_ignore as $k => $v)
        {
            unset($_services[$v]);
        }

        foreach($_services as $key => $value)
        {
            $value = (object) $value; $_continue = TRUE;

            if(in_array('guias', $filters))
            {
                $_continue = FALSE;

                foreach($ignore as $k => $v)
                {
                    if($k == 0)
                    {
                        if(in_array($value->bastar, $v))
                        {
                            $_continue = TRUE; break;
                        }
                    }
                    else
                    {
                        $_value = substr($value->bastar, $k, 1);

                        if(in_array($_value, $v))
                        {
                            $_continue = TRUE; break;
                        }
                    }
                }
            }
            else
            {
                foreach($ignore as $k => $v)
                {
                    if($k == 0)
                    {
                        if(in_array($value->bastar, $v))
                        {
                            $_continue = FALSE; break;
                        }
                    }
                    else
                    {
                        $_value = substr($value->bastar, $k, 1);

                        if(in_array($_value, $v))
                        {
                            $_continue = FALSE; break;
                        }
                    }
                }
            }

            if($_continue)
            {
                if($_city != '' AND $_city != NULL)
                {
                    $__city = $_city['code'];

                    if($__city != '' AND $__city != NULL)
                    {
                        $_continue = ($value->ciuin == $__city);
                    }
                }
            }

            if($_continue)
            {
                if(count($filters) > 0)
                {
                    if(!in_array('guias', $filters))
                    {
                        $_continue = FALSE;
                    }

                    foreach($filters as $k => $v)
                    {
                        $v_ = strtolower($v);
                        switch($v_)
                        {
                            case 'all': {
                                $_continue = FALSE;
                            }; break;
                            case 'traslados': {
                                if($value->tipsvs == 'TOT' OR $value->tipsvs == 'TIN' OR $value->tipsvs == 'TRF')
                                {
                                    $_continue = TRUE;
                                }
                            }; break;
                            case 'vuelos': {
                                if($value->tipsvs == 'AEC' OR $value->tipsvs == 'AEI' OR $value->tipsvs == 'AECFLT' OR $value->tipsvs == 'AEIFLT')
                                {
                                    $_continue = TRUE;
                                }
                            }; break;
                            case 'tour': {
                                if($value->tipsvs == 'EXC')
                                {
                                    $_continue = TRUE;
                                }
                            }; break;
                            case 'paquetes': {
                                if($value->tipsvs == 'NET')
                                {
                                    $_continue = TRUE;
                                }
                            }; break;
                            case 'hoteles': {
                                if($value->tipsvs == 'HTL')
                                {
                                    $_continue = TRUE;
                                }
                            }; break;
                            case 'trenes': {
                                if($value->tipsvs == 'TRN')
                                {
                                    $_continue = TRUE;
                                }
                            }; break;
                            case 'restaurantes': {
                                if($value->tipsvs == 'RES')
                                {
                                    $_continue = TRUE;
                                }
                            }; break;
                        }
                    }
                }
            }

            if($_continue)
            {
                if($flag_date != $value->fecin AND $key > 0)
                {
                    $_inicio = 4; $_fin = 5; $flag_date = $value->fecin;
                }

                $fecini = $value->fecin; $item = [];

                $inicio = ($_inicio < 10) ? ('0'.$_inicio) : $_inicio;
                $fin = ($_fin < 10) ? ('0'.$_fin) : $_fin;

                if($value->horin != '' AND !is_null($value->horin))
                {
                    if($value->horin == $value->horout)
                    {
                        $value->horout = date("H:i", strtotime("+1 hour", strtotime($value->horin)));
                    }
                }
                $item['see_preview_communications'] = 0;
                $item['anulado'] = @$value->anulado;
                $item['nroite'] = $value->nroite;
                $item['canadl'] = $value->canadl;
                $item['canchd'] = $value->canchd;
                $item['caninf'] = $value->caninf;
                $item['canpax'] = $value->canpax;
                $item['totalpaxs'] = (((int) $value->canadl) + ((int) $value->canchd) + ((int) $value->caninf));
                $item['start'] = $value->fecin . (($value->horin != '' && $value->horin != null) ? (' ' . $value->horin) : (' ' . $inicio . ':00'));
                $item['end'] = $value->fecout . (($value->horout != '' && $value->horout != null) ? (' ' . $value->horout) : (' ' . $fin . ':00') );
                $item['ciuin'] = $value->ciuin;
                $item['ciuout'] = $value->ciuout;
                $item['fecin'] = $value->fecin;
                $item['fecout'] = $value->fecout;
                $item['horin'] = $value->horin;
                $item['horout'] = $value->horout;
                $item['title'] = $value->descri;
                $item['content'] = ($value->flag_acomodo > 0) ? ' <i class="icon-user-check"></i>' : '';
                $item['bastar'] = $value->bastar;
                $item['desbas'] = $value->desbas;
                $item['desbas_inicial'] = $value->desbas_inicial;
                $item['categoria_hotel'] = $value->categoria_hotel;
                $item['codsvs'] = $value->codsvs;
                $item['tipsvs'] = $value->tipsvs;
                $item['catser'] = $value->catser;
                $item['class'] = ('EVENT ' . @$value->tipsvs);
                $item['icon'] = @$icons[@$value->tipsvs];
                $item['contentFull'] = $value->infoad;
                // $item['flag'] = $flag;
                $item['fecin_prime'] = $value->fecin;
                $item['horin_prime'] = $value->horin;
                $item['ciavue'] = $value->ciavue;
                $item['nrovue'] = $value->nrovue;
                $item['relation'] = $value->relation;
                $item['clase'] = $value->clase;
                $item['preped'] = $value->preped;
                $item['clasif'] = $value->clasif;
                $item['prefac'] = $value->prefac;
                $item['cantid'] = $value->cantid;
                $item['estado'] = $value->estado;
                $item['razon'] = $value->razon; // Línea aérea..
                $item['variations'] = @$value->variations;
                $item['all_rooms_mysql'] = @$value->all_rooms_mysql;
                $item['id_hotel'] = @$value->id_hotel;
                $item['allDay'] = false; //($value->fecin == $value->fecout && $value->horin == $value->horout) ? true : false

                if(!in_array($value->ciuin, $_cities))
                {
                    $_cities[] = $value->ciuin;
                    $cities[] = [
                        'label' => $value->descri_ciudad . ', ' . $value->descri_pais,
                        'code' => $value->ciuin
                    ];
                }

                if($fecini == $value->fecout)
                {
                    $services[] = $item;

                    if($value->horout != '' AND $value->horout != NULL)
                    {
                        $inicio = explode(":", $value->horin); $_inicio = (int) $inicio[0];
                    }

                    $_inicio += 1; $_fin = $_inicio + 1;
                }
                else
                {
                    while ($fecini < $value->fecout)
                    {
                        $item['start'] = $fecini . " 00:00";
                        $item['end'] = $fecini . " 01:00";
                        $item['fecin'] = $fecini;
                        $item['fecout'] = $fecini;
                        $item['allDay'] = true;

                        $services[] = $item;
                        $fecini = date("Y-m-d", strtotime("+1 day", strtotime($fecini)));
                    }
                }
            }
        }

        $cities[] = [
            'label' => 'TODOS',
            'code' => '',
        ];

        return response()->json([
            'city' => $_city,
            'type_room' => $type_room,
            'services' => $services,
            'cities' => $cities,
            'hotels' => $hotels
        ]);
    }

    function calculaedad($fechanacimiento){
        list($anio,$mes,$dia) = explode("-",$fechanacimiento);
        $anio_diferencia  = date("Y") - $anio;
        $mes_diferencia = date("m") - $mes;
        $dia_diferencia   = date("d") - $dia;
        if ($dia_diferencia < 0 || $mes_diferencia < 0)
            $anio_diferencia--;
        return $anio_diferencia;
    }

    public function toggle_view_hotels(Request $request)
    {
        $items = $request->__get('items');
        $request->session()->put('filter_hotels_file', $items);

        return response()->json(['filters' => $request->session()->get('filter_hotels_file')]);
    }

    public function filter_hotels_file(Request $request)
    {
        $data = $request->session()->get('filter_hotels_file');
        return response()->json($data);
    }
}
