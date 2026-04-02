<?php

namespace App\Http\Controllers;

use App\Country;
use App\Doctype;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Clients;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use App\User;
use Illuminate\Support\Facades\DB;
use App\BoardNote;
use App\Notification;
use App\UserMarket;
use App\Mail\MailReminder;

use PayPal\Api\Amount;
use PayPal\Api\Item;

/** All Paypal Details class **/

use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;

use GuzzleHttp\Client;

class BoardController extends Controller
{
    // Paxs pendientes : http://genero.limatours.com.pe:8200/wa/r/litt1630?
    public $modules = [];
    public $notifications = [];

    // WS GENERAL
    public $wsdl = '';
    public $client;

    // WS PASAJEROS
    public $wsdl_passengers = '';
    public $client_passengers;

    // WS PEDIDOS
    public $wsdl_orders = '';
    public $client_orders;

    // WS FACTURACION
    public $wsdl_billings = '';
    public $client_billings;

    public $markets = [];
    public $regions = [];

    public $baseUrlExtra = '';
    public $files_onedb_ms = '';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->baseUrlExtra = config('app.mix_base_external_url');
        $this->files_onedb_ms = config('services.aurora_files.domain');
    }

    public function getModules()
    {
        return [
            'A' => ['url' => 'getURL', 'key' => 'PAO', 'value' => trans('board.label.' . Str::slug('Pase a Operaciones'))],
            'B' => ['url' => 'getURL', 'key' => 'LPO', 'value' => trans('board.label.' . Str::slug('Horarios Servicios y vuelos'))],
            'M' => ['url' => '', 'key' => '', 'value' => trans('board.label.' . Str::slug('Vuelos'))],
            'C' => ['url' => 'getURL', 'key' => 'HTL', 'value' => trans('board.label.' . Str::slug('Hoteles sin Confirmación'))],
            'D' => ['url' => 'getURL', 'key' => 'CNP', 'value' => trans('board.label.' . Str::slug('Confirmaciones Pendientes'))],
            'E' => ['url' => 'modalPassengers', 'key' => 'PXP', 'value' => trans('board.label.' . Str::slug('Paxs Pendientes'))],
            'F' => ['url' => 'getURL', 'key' => 'PPP', 'value' => trans('board.label.' . Str::slug('Pasaportes Pendientes'))],
            'G' => ['url' => 'http://genero.limatours.com.pe:8200/wa/r/litt1640?', 'key' => 'VCP', 'value' => trans('board.label.' . Str::slug('Vouchers Pendientes'))],
            'H' => ['url' => 'getURL', 'key' => 'FPI', 'value' => trans('board.label.' . Str::slug('Facturas pendientes de Ingreso'))],
            'I' => ['url' => 'getURL', 'key' => 'PGP', 'value' => trans('board.label.' . Str::slug('Pagos Pendientes'))],
            'J' => ['url' => 'getURL', 'key' => 'FSM', 'value' => trans('board.label.' . Str::slug('Files sin Memos'))],
            'K' => ['url' => 'https://www.machupicchu.gob.pe/consulta/disponibilidad', 'key' => 'HP/MM', 'value' => trans('board.label.' . Str::slug('Huayna Picchu/Mapi Montaña'))],
            'L' => ['url' => 'https://www.machupicchu.gob.pe/consulta/disponibilidad', 'key' => 'INCA', 'value' => trans('board.label.' . Str::slug('Camino Inca'))]
        ];
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $return = (int) $request->__get('return');
        $_dataURL = (array) $request->__get('data');
        $dataURL = [];

        $usuario = UserMarket::where('user_id', '=', auth()->user()->id)->first();

        if ($usuario != null) {
            $this->markets = $usuario->markets()->get();
        }

        foreach ($this->markets as $key => $value) {
            $this->regions[@$value->market_region->region->title] = @$value->market_region->region->title;
        }

        $this->regions = implode(", ", $this->regions);

        $_token = '';
        $ignore = ['action', 'token'];
        foreach ($_dataURL as $key => $value) {
            if (!in_array($key, $ignore)) {
                $_token .= $value;
            }
        }

        if (@$_dataURL['token'] == sha1($_token)) {
            $dataURL = $_dataURL;
        }

        $dataURL = json_encode($dataURL);

        $date = [
            'startDate' => (date("Y-m") . "-01T" . date("H:i:s")),
            'endDate' => (date("Y-m-d") . "T" . date("H:i:s"))
        ];

        if ($this->hasPermission('board.boss') || auth()->user()->rol->role_id == 1) {
            return view('board.board')
                ->with('bossFlag', 1)
                ->with('date', $date)
                ->with('return', $return)
                ->with('dataURL', $dataURL)
                ->with('regions', $this->regions)
                ->with('markets', $this->markets);
        } else {
            return view('board.board')
                ->with('bossFlag', 0)
                ->with('date', $date)
                ->with('return', $return)
                ->with('dataURL', $dataURL)
                ->with('regions', $this->regions)
                ->with('markets', $this->markets);
        }
    }

    public function report_orders(Request $request)
    {
        $return = (int) $request->__get('return');
        $_dataURL = (array) $request->__get('data');
        $dataURL = [];

        $usuario = UserMarket::where('user_id', '=', auth()->user()->id)->first();

        if ($usuario != null) {
            $this->markets = $usuario->markets()->get();
        }

        foreach ($this->markets as $key => $value) {
            $this->regions[@$value->market_region->region->title] = @$value->market_region->region->title;
        }

        $this->regions = implode(", ", $this->regions);

        $_token = '';
        $ignore = ['action', 'token'];
        foreach ($_dataURL as $key => $value) {
            if (!in_array($key, $ignore)) {
                $_token .= $value;
            }
        }

        if (@$_dataURL['token'] == sha1($_token)) {
            $dataURL = $_dataURL;
        }

        $dataURL = json_encode($dataURL);

        $date = [
            'startDate' => (date("Y-m") . "-01T" . date("H:i:s")),
            'endDate' => (date("Y-m-d") . "T" . date("H:i:s"))
        ];

        if ($this->hasPermission('board.boss')) {
            return view('menu.report_orders')->with('regions', $this->regions)->with('date', $date)->with('bossFlag', 1)->with('return', $return)->with('dataURL', $dataURL)->with('regions', $this->regions)->with('markets', $this->markets);
        } else {
            return view('menu.report_orders')->with('regions', $this->regions)->with('date', $date)->with('bossFlag', 0)->with('return', $return)->with('dataURL', $dataURL)->with('regions', $this->regions)->with('markets', $this->markets);
        }
    }

    public function search_budget(Request $request)
    {
        $client = new \GuzzleHttp\Client([
            'verify' => false,
        ]);

        $region = $request->__get('region');
        $area = $request->__get('area');
        $user = auth()->user()->code;
        $mes = (int) date("m");
        $region = str_replace("C", "0", $region);

        $anio_desde = (($mes < 9) ? (date("Y") - 1) : date("Y"));
        $anio_hasta = (($mes > 9) ? (date("Y") + 1) : date("Y"));

        $mes = ((int) date("m") - 1);
        $data = [
            'identi' => 'N',
            'region' => $region,
            'area' => '',
            'fecini' => $anio_desde . '-10',
            'fecfin' => $anio_hasta . '-' . (($mes > 9) ? $mes : ('0' . $mes))
        ];

        $url = sprintf('%sorders/searchBudget', $this->baseUrlExtra);

        $request_api = $client->get($url, [
            'query' => $data
        ]);
        $response_api = json_decode($request_api->getBody()->getContents(), true);
        $response_venta = $response_api['data'] ?? [];

        $venta_ejecutado = 0;
        $costo_ejecutado = 0;
        foreach ($response_venta as $key => $value) {
            $venta_ejecutado += (float) $value['habeba'] - (float) $value['debeba'];
            $costo_ejecutado += (float) $value['debelo'] - (float) $value['habelo'];
        }
        $beneficio_ejecutado = (float) $venta_ejecutado - (float) $costo_ejecutado;

        $mes = ((int) date("m") - 1);
        $data = [
            'identi' => 'P',
            'region' => $region,
            'area' => '',
            'fecini' => $anio_desde . '-10',
            'fecfin' => $anio_hasta . '-' . (($mes > 9) ? $mes : ('0' . $mes))
        ];

        $url = sprintf('%sorders/budget', $this->baseUrlExtra);

        $request_api = $client->get($url, [
            'query' => $data
        ]);
        $response_api = json_decode($request_api->getBody()->getContents(), true);
        $response_esperado = $response_api['data'] ?? [];

        $venta_esperado = 0;
        $costo_esperado = 0;
        foreach ($response_esperado as $key => $value) {
            $venta_esperado += (float) $value['debeba'];
            $costo_esperado += (float) $value['habeba'];
        }

        $venta_esperado = 0;
        $costo_esperado = 0;
        foreach ($response_esperado as $key => $value) {
            $venta_esperado += (float) $value['DEBEBA'];
            $costo_esperado += (float) $value['HABEBA'];
        }
        $beneficio_esperado = (float) $venta_esperado - (float) $costo_esperado;

        $data = [
            'identi' => 'P',
            'region' => $region,
            'area' => '',
            'fecini' => $anio_desde . '-10',
            'fecfin' => $anio_hasta . '-09'
        ];

        $url = sprintf('%sorders/searchBudget', $this->baseUrlExtra);

        $request_api = $client->get($url, [
            'query' => $data
        ]);
        $response_api = json_decode($request_api->getBody()->getContents(), true);
        $presupuesto = $response_api['data'] ?? [];

        $venta_presupuesto = 0;
        $costo_presupuesto = 0;
        foreach ($presupuesto as $key => $value) {
            $venta_presupuesto += (float) $value['debeba']; // (double) $value['HABEBA'] - (double) $value['DEBEBA'];
            $costo_presupuesto += (float) $value['habeba']; // (double) $value['DEBELO'] - (double) $value['HABELO'];
        }

        $beneficio_presupuesto = $venta_presupuesto - $costo_presupuesto;

        return response()->json([
            'response_venta' => $response_venta,
            'response_esperado' => $response_esperado,
            'presupuesto' => $presupuesto,
            'beneficio' => [
                'ejecutado' => number_format($beneficio_ejecutado, 0, "", ""),
                'esperado' => number_format($beneficio_esperado, 0, "", ""),
                'presupuesto_anual' => number_format($beneficio_presupuesto, 0, "", "")
            ],
            'venta' => [
                'ejecutado' => number_format($venta_ejecutado, 0, "", ""),
                'esperado' => number_format($venta_esperado, 0, "", ""),
                'presupuesto_anual' => number_format($venta_presupuesto, 0, "", "")
            ],
            'costo' => [
                'ejecutado' => number_format($costo_ejecutado, 0, "", ""),
                'esperado' => number_format($costo_esperado, 0, "", ""),
                'presupuesto_anual' => number_format($costo_presupuesto, 0, "", "")
            ]
        ]);
    }

    public function billing_report(Request $request)
    {
        try {
            $return = (int) $request->__get('return');
            $_dataURL = (array) $request->__get('data');
            $dataURL = [];

            $usuario = UserMarket::where('user_id', '=', auth()->user()->id)->first();

            if ($usuario != null) {
                $this->markets = $usuario->markets()->get();
            }

            foreach ($this->markets as $key => $value) {
                $this->regions[@$value->market_region->region->title] = @$value->market_region->region->title;
            }

            $this->regions = implode(", ", $this->regions);

            $_token = '';
            $ignore = ['action', 'token'];
            foreach ($_dataURL as $key => $value) {
                if (!in_array($key, $ignore)) {
                    $_token .= $value;
                }
            }

            if (@$_dataURL['token'] == sha1($_token)) {
                $dataURL = $_dataURL;
            }

            $dataURL = json_encode($dataURL);

            $date = [
                'startDate' => (date("Y-m") . "-01T" . date("H:i:s")),
                'endDate' => (date("Y-m-d") . "T" . date("H:i:s"))
            ];

            if ($this->hasPermission('board.boss')) {
                return view('reports.billings.index')->with('date', $date)->with('bossFlag', 1)->with('return', $return)->with('dataURL', $dataURL)->with('regions', $this->regions)->with('markets', $this->markets);
            } else {
                return view('reports.billings.index')->with('date', $date)->with('bossFlag', 0)->with('return', $return)->with('dataURL', $dataURL)->with('regions', $this->regions)->with('markets', $this->markets);
            }
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function productivity_report(Request $request)
    {
        $return = (int) $request->__get('return');
        $_dataURL = (array) $request->__get('data');
        $dataURL = [];

        $usuario = UserMarket::where('user_id', '=', auth()->user()->id)->first();

        if ($usuario != null) {
            $this->markets = $usuario->markets()->get();
        }

        foreach ($this->markets as $key => $value) {
            $this->regions[@$value->market_region->region->title] = @$value->market_region->region->title;
        }

        $this->regions = implode(", ", $this->regions);

        $_token = '';
        $ignore = ['action', 'token'];
        foreach ($_dataURL as $key => $value) {
            if (!in_array($key, $ignore)) {
                $_token .= $value;
            }
        }

        if (@$_dataURL['token'] == sha1($_token)) {
            $dataURL = $_dataURL;
        }

        $dataURL = json_encode($dataURL);

        $date = [
            'startDate' => (date("Y-m") . "-01T" . date("H:i:s")),
            'endDate' => (date("Y-m-d") . "T" . date("H:i:s"))
        ];

        if ($this->hasPermission('board.boss')) {
            return view('reports.files.productivity')->with('date', $date)->with('bossFlag', 1)->with('return', $return)->with('dataURL', $dataURL)->with('regions', $this->regions)->with('markets', $this->markets);
        } else {
            return view('reports.files.productivity')->with('date', $date)->with('bossFlag', 0)->with('return', $return)->with('dataURL', $dataURL)->with('regions', $this->regions)->with('markets', $this->markets);
        }
    }

    /**
     * @since 2019
     **/
    public function dashboard(Request $request)
    {
        $hoy = date("d/m/Y");
        $siete_dias = date("Y-m-d", strtotime("+7 days", strtotime(date("Y-m-d"))));
        $quince_dias = date("Y-m-d", strtotime("+15 days", strtotime(date("Y-m-d"))));
        $treinta_dias = date("Y-m-d", strtotime("+1 month", strtotime(date("Y-m-d"))));
        $limite_dias = date("Y-m-d", strtotime("+2 month", strtotime(date("Y-m-d"))));

        $user = auth()->user();
        $executive = trim($request->__get('executive'));
        $bossFlag = $request->__get('bossFlag');

        $client = new \GuzzleHttp\Client([
            'verify' => false,
        ]);

        $items = ['siete', 'quince', 'treinta', 'mes'];
        $files_totales = 0;
        $tareas_totales = 0;
        $vips = 0;
        $files_siete = [];
        $files_quince = [];
        $files_treinta = [];
        $files_mes = [];

        $explode = explode("-", $executive);

        if (count($explode) > 0) {
            $executive = trim($explode[0]);
        }

        if ($executive == '' or $executive == null) {
            $executive = $user->code;
        }

        if ($bossFlag == 0) {
            $params = array('type' => 'executive', 'codigo' => $executive);
            $_request = $client->get($this->baseUrlExtra . 'api/board?' . http_build_query($params));
            $response_group = (array) json_decode($_request->getBody()->getContents(), true);
            $groups = [];

            foreach ($response_group as $key => $value) {
                foreach ($value as $k => $v) {
                    $new = [];

                    foreach ($v as $i => $j) {
                        if ($i != 'codigo') {
                            $new[$i] = $j;
                        }
                    }

                    $groups[$v['codigo']][$key] = $new;
                }
            }

            $_response = @$groups[$executive];

            foreach ($_response as $k => $v) {
                $$k = $v;
            }

            foreach ($items as $k => $v) {
                $files = 'files_' . $v;
                $response = 'response_' . $v;
                $_nroref = [];
                $_modulo = [];
                $_vip = [];

                // print_r($$response);

                if (isset($$response['nroref']) and is_array($$response['nroref'])) {
                    // dd($response_siete);

                    // Limpiando valores que vienen por el webservice..
                    foreach ($$response['nroref'] as $key => $value) {
                        foreach ($$response['module'] as $a => $b) {
                            if (trim($$response['nroref'][$a]) != '') {
                                $_nroref[$$response['nroref'][$a] . '_' . $b] = $$response['nroref'][$a];
                                $_modulo[$$response['nroref'][$a] . '_' . $b] = $b;
                            }
                        }

                        if (isset($$response['operad'][$key])) {
                            $_vip[$$response['nroref'][$key]] = $$response['operad'][$key];
                        }
                    }

                    $$response['nroref'] = $_nroref;
                    $$response['module'] = $_modulo;
                    $$response['operad'] = $_vip;
                    // Valores seteados por el webservice..

                    if (isset($$response['nroref']) and count($$response['nroref']) > 0) {
                        // $$files['vips'] = array_unique($$response['operad']);
                        $$files['vips'] = [];
                        // $vips += count($$files['vips']);
                        $tareas = 0;

                        foreach ($$response['operad'] as $key => $value) {
                            if ($value == 'VIP') {
                                $$files['vips'][$key] = $key;
                            }
                        }

                        $vips += count($$files['vips']);
                        $__key = 0;

                        foreach ($$response['module'] as $key => $value) {
                            $nroref = $$response['nroref'][$key];

                            if (key_exists($value, $this->getModules()) and trim($nroref) != '' and $$response['pendie'][$__key] == 'SI') {
                                if ($value == 'E') {
                                    // $this->sendMail($nroref); // Enviar notificaciones cuando sea falta de pasajeros..
                                }

                                $$files[$value][] = $nroref;
                                $tareas += 1;
                                $tareas_totales += 1;
                            } else {
                                unset($$response['nroref'][$key]);
                            }

                            $__key++;
                        }

                        if (count($$response['module']) == 0) {
                            $$files['items'] = [];
                            $$files['quantity'] = 0;
                        } else {
                            $$files['items'] = array_unique($$response['nroref']);
                            $$files['quantity'] = count($$files['items']);
                        }

                        $files_totales += $$files['quantity'];
                        $$files['tareas'] = $tareas;
                    } else {
                        $$files['items'] = [];
                        $$files['quantity'] = 0;
                        $$files['tareas'] = 0;
                    }
                } else {
                    $$files['items'] = [];
                    $$files['quantity'] = 0;
                    $$files['tareas'] = 0;
                }

                $$files['response_ws'] = $$response;
            }

            $data = [
                'usuario' => $user,
            ];

            $params = http_build_query($data);

            $client = new \GuzzleHttp\Client([
                'verify' => false,
            ]);


            $url = sprintf('%sexecutives/team/%s/executives', $this->files_onedb_ms, $user);
            $request_api = $client->get($url);
            $response_api = json_decode($request_api->getBody()->getContents(), true);
            $executives = $response_api['data']['executives'] ?? [];

            /*
                $executives = User::select('code as value', DB::raw('concat(code, " - ", name) as text'))
                    ->where('user_type_id', '=', 3)->where('status', '=', 1)
                    ->where('class_code', 'like', 'QR%')->get();
                */

            $lang = session()->get('lang');
            $countries = Country::with([
                'translations' => function ($query) use ($lang) {
                    $query->whereHas('language', function ($q) use ($lang) {
                        $q->where('iso', $lang);
                    });
                }
            ])->get();

            $doctypes = Doctype::with([
                'translations' => function ($query) use ($lang) {
                    $query->whereHas('language', function ($q) use ($lang) {
                        $q->where('iso', $lang);
                    });
                }
            ])->get();

            $response = [
                'user' => $user,
                'modules' => $this->getModules(),
                'dashboard' => [
                    'hoy' => $hoy,
                    'fecha_7' => $siete_dias,
                    'fecha_15' => $quince_dias,
                    'fecha_30' => $treinta_dias,
                    'fecha_limite' => $limite_dias,
                    'response_7' => $files_siete,
                    'response_15' => $files_quince,
                    'response_30' => $files_treinta,
                    'response_mes' => $files_mes,
                    'files_totales' => $files_totales,
                    'tareas_totales' => $tareas_totales,
                    'vips' => $vips,
                    'porcentaje_7' => ($files_totales > 0) ? (number_format(count($files_siete['items']) * 100 / $files_totales, 2)) : 0,
                    'porcentaje_15' => ($files_totales > 0) ? (number_format(count($files_quince['items']) * 100 / $files_totales, 2)) : 0,
                    'porcentaje_30' => ($files_totales > 0) ? (number_format(count($files_treinta['items']) * 100 / $files_totales, 2)) : 0,
                    'porcentaje_mes' => ($files_totales > 0) ? (number_format(count($files_mes['items']) * 100 / $files_totales, 2)) : 0,
                ],
                'countries' => $countries,
                'doctypes' => $doctypes,
                'executives' => $executives,
                'executive' => $executive
            ];

            session()->put('dashboard', $response['dashboard']);
        } else {
            $params = array('type' => 'boss', 'codigo' => $executive);
            $url = $this->baseUrlExtra . 'api/board?' . http_build_query($params);

            $_request = $client->get($url);
            $_response = (array) json_decode($_request->getBody()->getContents(), true);
            $executives = [];

            if (isset($_response['operad']) and count($_response['operad']) > 0) {
                foreach ($_response['operad'] as $_executive) {
                    if ($_executive != '' and $_executive != null) {
                        $user = User::select('code as value', DB::raw('concat(code, " - ", name) as text'))
                            ->where('user_type_id', '=', 3)->where('status', '=', 1)
                            ->where('code', '=', $_executive)->first();

                        if (@$user->text != '') {
                            $executives[trim($_executive)] = $user->text;
                        }
                    }
                }
            }

            $modulesFilter = ['' => 'TODOS'];

            foreach ($this->getModules() as $key => $value) {
                $modulesFilter[$key] = $value['value'];
            }

            $response = [
                'user' => $user,
                'modules' => [],
                'dashboard' => [],
                'executives' => $executives,
                'executive' => $executive,
                'modulesFilter' => $modulesFilter,
            ];
        }

        return response()->json($response);
    }

    public function executives(Request $request)
    {
        $hoy = date("Y-m-d");
        $siete_dias = date("Y-m-d", strtotime("+7 days", strtotime(date("Y-m-d"))));
        $quince_dias = date("Y-m-d", strtotime("+15 days", strtotime(date("Y-m-d"))));
        $treinta_dias = date("Y-m-d", strtotime("+1 month", strtotime(date("Y-m-d"))));
        $limite_dias = date("Y-m-d", strtotime("+2 month", strtotime(date("Y-m-d"))));

        $executives = (array) $request->__get('executives');
        $module = $request->__get('module');

        $items = ['siete', 'quince', 'treinta', 'mes'];
        $groups = [];

        try {
            $client = new \GuzzleHttp\Client();
            $_executives = [];
            $response_ = [];

            foreach ($executives as $key => $value) {
                $_executives[] = $key;
            }

            $_executive = implode(",", $_executives);

            if ($_executive != '' and $_executive != null) {
                $params = array('type' => 'executive', 'codigo' => $_executive);
                $_request = $client->get($this->baseUrlExtra . 'api/board?' . http_build_query($params));
                $response_group = (array) json_decode($_request->getBody()->getContents(), true);

                foreach ($response_group as $key => $value) {
                    foreach ($value as $k => $v) {
                        $new = [];

                        foreach ($v as $i => $j) {
                            if ($i != 'codigo') {
                                $new[$i] = $j;
                            }
                        }

                        $groups[$v['codigo']][$key] = $new;
                        $response_[$v['codigo']] = [];
                    }
                }

                foreach ($groups as $_executive => $group) {
                    $_response = $group;

                    $files_totales = 0;
                    $tareas_totales = 0;
                    $vips = 0;
                    $files_siete = [];
                    $files_quince = [];
                    $files_treinta = [];
                    $files_mes = [];

                    foreach ($_response as $k => $item) {
                        $$k = $item;
                    }

                    foreach ($items as $k => $v) {
                        $files = 'files_' . $v;
                        $response = 'response_' . $v;
                        $_nroref = [];
                        $_modulo = [];
                        $_vip = [];
                        $_pendie = [];

                        if (isset($$response['nroref']) and is_array($$response['nroref'])) {
                            // Limpiando valores que vienen por el webservice..
                            foreach ($$response['module'] as $a => $b) {
                                if (key_exists($b, $this->getModules()) and ($module == '' or $module == 'TODOS' or $module == $b)) {
                                    if (trim($$response['nroref'][$a]) != '') {
                                        $_nroref[$$response['nroref'][$a] . '_' . $b] = $$response['nroref'][$a];
                                        $_pendie[$$response['nroref'][$a] . '_' . $b] = $$response['pendie'][$a];
                                        $_modulo[$$response['nroref'][$a] . '_' . $b] = $b;
                                    }
                                }
                            }

                            foreach ($$response['nroref'] as $key => $value) {
                                if (isset($$response['operad'][$key])) {
                                    $_vip[$$response['nroref'][$key]] = $$response['operad'][$key];
                                }
                            }

                            $$response['nroref'] = $_nroref;
                            $$response['module'] = $_modulo;
                            $$response['operad'] = $_vip;
                            $$response['pendie'] = $_pendie;
                            // Valores seteados por el webservice..

                            // dd($$response);

                            if (isset($$response['nroref']) and count($$response['nroref']) > 0) {
                                // $$files['vips'] = array_unique($$response['operad']);
                                $$files['vips'] = [];
                                // $vips += count($$files['vips']);
                                $tareas = 0;

                                foreach ($$response['operad'] as $key => $value) {
                                    if ($value == 'VIP') {
                                        $$files['vips'][$key] = $key;
                                    }
                                }

                                $vips += count($$files['vips']);

                                foreach ($$response['module'] as $key => $value) {
                                    if ($module == '' or $module == 'TODOS' or $module == $value) {
                                        $nroref = $$response['nroref'][$key];
                                        $pendie = $$response['pendie'][$key];

                                        if (key_exists($value, $this->getModules()) and $pendie == 'SI') {
                                            if ($value == 'E') // Paxs Pendientes..
                                            {
                                                // $this->sendMail($nroref, $_executive);
                                            }

                                            // $$files[$value][] = $nroref;
                                            $tareas += 1;
                                            $tareas_totales += 1;
                                        } else {
                                            // unset($$response['nroref'][$key]);
                                        }
                                    }
                                }

                                if (count($$response['module']) == 0) {
                                    $$files[$_executive]['items'] = [];
                                    $$files[$_executive]['quantity'] = 0;
                                    $$files[$_executive]['tareas'] = 0;
                                } else {
                                    $$files[$_executive]['items'] = array_unique($$response['nroref']);
                                    $$files[$_executive]['modules'] = array_unique($$response['module']);
                                    $$files[$_executive]['quantity'] = count($$files[$_executive]['items']);
                                    $$files[$_executive]['tareas'] = $tareas;
                                }

                                $files_totales += $$files[$_executive]['quantity'];
                            } else {
                                $$files[$_executive]['items'] = [];
                                $$files[$_executive]['quantity'] = 0;
                                $$files[$_executive]['tareas'] = 0;
                            }
                        } else {
                            $$files[$_executive]['items'] = [];
                            $$files[$_executive]['quantity'] = 0;
                            $$files[$_executive]['tareas'] = 0;
                        }

                        $$files['tareas'] = ((isset($$files['tareas'])) ? ($$files['tareas']) : 0) + $$files[$_executive]['tareas'];
                    }

                    $response_[$_executive] = [
                        'response' => $_response,
                        'dashboard' => [
                            'hoy' => $hoy,
                            'fecha_7' => $siete_dias,
                            'fecha_15' => $quince_dias,
                            'fecha_30' => $treinta_dias,
                            'fecha_limite' => $limite_dias,
                            'response_7' => $files_siete,
                            'response_15' => $files_quince,
                            'response_30' => $files_treinta,
                            'response_mes' => $files_mes,
                            'files_totales' => $files_totales,
                            'tareas_totales' => $tareas_totales,
                            'vips' => $vips,
                            'porcentaje_7' => 0,
                            'porcentaje_15' => 0,
                            'porcentaje_30' => 0,
                            'porcentaje_mes' => 0,
                        ]
                    ];
                }
            }

            return response()->json($response_);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function files(Request $request)
    {
        try {
            $client = new \GuzzleHttp\Client([
                'verify' => false
            ]);

            $type = $request->__get('type');
            $category = $request->__get('category');
            $dashboard = session()->get('dashboard');
            $files = @$dashboard[$type][$category];
            $categories = $dashboard[$type];
            $_response = [];

            $types = [
                'response_7' => ['icon' => 'icon-filter-fire', 'title' => 'Files próximos a caducar', 'class' => 'min-7'],
                'response_15' => ['icon' => 'icon-filter-flame', 'title' => 'Files que necesitan tu atención', 'class' => 'min-15'],
                'response_30' => ['icon' => 'icon-filter-leaf', 'title' => 'Files en proceso', 'class' => 'min-30'],
                'response_mes' => ['icon' => 'icon-filter-plant', 'title' => 'Files por trabajar', 'class' => 'max-30']
            ];

            if (is_array($files) and count($files) > 0) {
                $params = array('type' => 'detail', 'codigo' => $files);
                $_request = $client->get($this->baseUrlExtra . 'api/board?' . http_build_query($params));
                $response = (array) json_decode($_request->getBody()->getContents(), true);

                foreach ($response as $key => $group) {
                    $detalle = $group[0];
                    $value = $key;

                    $notas = BoardNote::where('file', '=', $detalle['nroref'][0])->whereNull('parent')->count();

                    if (is_numeric($detalle['nroref'][5])) {
                        $detalle['nroref'][5] = number_format($detalle['nroref'][5], 0, '.', ',');
                    } else {
                        $detalle['nroref'][5] = 0;
                    }

                    $_response[$value]['detalle'] = $detalle['nroref'];
                    $_response[$value]['notas'] = $notas; // Cantidad de notas..

                    foreach ($this->getModules() as $k => $v) {
                        if (isset($categories[$k]) and count($categories[$k]) > 0) {
                            if (in_array($value, $categories[$k])) {
                                $_response[$value][$k] = 1;
                            } else {
                                $_response[$value][$k] = 0;
                            }
                        } else {
                            $_response[$value][$k] = 0;
                        }
                    }
                }
            }

            $response['files'] = $_response;
            $response['quantity'] = count($_response);

            // Combinando la data..
            $response = array_merge($response, $types[$type]);
            return response()->json($response);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function file(Request $request)
    {
        try {
            $client = new \GuzzleHttp\Client([
                'verify' => false
            ]);

            $file = $request->__get('file');

            $params = array('type' => 'detail', 'codigo' => $file);
            $_request = $client->get($this->baseUrlExtra . 'api/board?' . http_build_query($params));
            $response = (array) json_decode($_request->getBody()->getContents(), true);
            $response = $response[$file][0];

            if (is_numeric($response['nroref'][5])) {
                $response['nroref'][5] = number_format($response['nroref'][5], 0, '.', ',');
            } else {
                $response['nroref'][5] = 0;
            }

            $response['nroref']['date'] = strtotime($response['nroref'][3]);

            foreach ($this->getModules() as $key => $value) {
                $_key = array_search($key, $response['module']);

                $response['modulos'][$value['value']] = (in_array($key, $response['module'])) ? 1 : 0; //AND $response['pendie'][$_key] == 'SI'
            }

            return response()->json($response);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function notes(Request $request)
    {
        $inputs = (object) $request->all();
        $notes = BoardNote::where('file', '=', $inputs->file)->whereNull('parent')->orderBy('id', 'desc')->get();

        foreach ($notes as $key => $value) {
            $user = User::where('code', '=', $value->user)->first();
            $notes[$key]['user'] = $user;
            $n = BoardNote::where('parent', '=', $value->id)->orderBy('id', 'desc')->get();

            foreach ($n as $k => $v) {
                $u = User::where('code', '=', $v->user)->first();
                $n[$k]['user'] = $u;
            }

            // $notes[$key]['created_at'] = date("F,d Y H:i:s", strtotime($value->created_at));
            // $notes[$key]['updated_at'] = date("F,d Y H:i:s", strtotime($value->updated_at));

            $notes[$key]['notes'] = $n;
        }

        $response['notes'] = $notes;

        return response()->json($response);
    }

    public function save_note(Request $request)
    {
        $inputs = (object) $request->all();

        try {
            $notification = new Notification;
            $responseNoti = [];

            if ($inputs->id > 0) // Update
            {
                $note = BoardNote::find($inputs->id);
                $note->comment = $inputs->content;

                $notification->title = "Actualización de Nota";
                $notification->content = "Se ha actualizado la nota en el file: " . $note->file;
            } else // Create
            {
                $note = new BoardNote;
                $note->file = $inputs->file;
                $note->user = auth()->user()->code;
                $note->comment = $inputs->content;
                $note->status = 1;

                $notification->title = "Nueva Nota";
                $notification->content = "Se ha incluido una nota en el file: " . $note->file;
            }

            if ($inputs->parent != '') {
                $note->parent = $inputs->parent;

                $first_note = BoardNote::where('id', '=', $inputs->parent)->first();
                $user_response = $first_note->user; // Usuario que creó la nota..

                if ($user_response != auth()->user()->code) {
                    // Notificación de Repuesta..
                    $response_notification = new Notification;
                    $response_notification->title = "Respuesta a una Nota";
                    $response_notification->content = "Te han respondido en el file: " . $note->file;
                    $response_notification->target = 1;
                    $response_notification->type = 2;
                    $response_notification->url = 'showNotification';
                    $response_notification->user = $user_response;
                    $response_notification->status = 1;
                    $response_notification->data = json_encode(['file' => $note->file]);
                    $response_notification->created_by = auth()->user()->code;
                    $response_notification->updated_by = auth()->user()->code;
                    $response_notification->module = 'board';
                    $response_notification->save();

                    $pushNoti = (object) ['user' => $response_notification->user, 'title' => $response_notification->title, 'body' => $response_notification->content, 'click_action' => URL::to('/board')];
                    $this->sendPushNotification($pushNoti);
                }

                $notification->title = "Respuesta a una Nota";
                $notification->content = "Han respondido un comentario de nota en el file: " . $note->file;
            }

            $note->save();

            // Renderización de la nota..
            if ($inputs->user != auth()->user()->code) {
                $notification->target = 1;
                $notification->type = 2;
                $notification->url = 'showNotification';
                $notification->user = $inputs->user;
                $notification->status = 1;
                $notification->data = json_encode(['file' => $note->file]);
                $notification->created_by = auth()->user()->code;
                $notification->updated_by = auth()->user()->code;
                $notification->module = 'board';
                $notification->save();

                $pushNoti = (object) ['user' => $notification->user, 'title' => $notification->title, 'body' => $notification->content, 'click_action' => URL::to('/board')];
                $responseNoti = $this->sendPushNotification($pushNoti);
            }

            $response = array('type' => 'success', 'text' => 'Información guardada correctamente.', 'response_noti' => $responseNoti);
        } catch (\Exception $ex) {
            $response = array('type' => 'danger', 'text' => $ex->getMessage());
        }

        return response()->json($response);
    }

    public function delete_note(Request $request)
    {
        $inputs = (object) $request->all();

        try {
            if ($inputs->id > 0) {
                $note = BoardNote::find($inputs->id);
                $note->status = 0;
                $note->save();
            }

            $response = array('type' => 'success', 'text' => 'Nota eliminada correctamente.');
        } catch (\Exception $ex) {
            $response = array('type' => 'danger', 'text' => $ex->getMessage());
        }

        return response()->json($response);
    }

    public function details(Request $request)
    {
        return view('board.details')->with('file', $request->__get('file'));
    }

    public function detailsWS($file)
    {
        try {
            $client = new \GuzzleHttp\Client([
                'verify' => false,
            ]);
            $_request = $client->get($this->files_onedb_ms . 'files/' . $file . '/calendar_executive');
            $response = json_decode($_request->getBody()->getContents(), true);
            return response()->json($response);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function detail_billing(Request $request)
    {
        if (auth()->user()->user_type_id == 1) {
            $customer = auth()->user()->code;
        } else {
            $customer = $request->__get('customer');
        }

        $fecini = '-';
        $fecfin = '-';
        $file = '-';

        if ($request->input('flag_search') !== "true") {
            $date_range = (array) $request->__get('dateRange');
            $fecini = $date_range['startDate'];
            $fecfin = $date_range['endDate'];

            $fecini = explode("T", $fecini);
            $fecini = date("d/m/Y", strtotime($fecini[0]));
            $fecfin = explode("T", $fecfin);
            $fecfin = date("d/m/Y", strtotime($fecfin[0]));

            $file = (!empty($request->__get('nrofile'))) ? $request->__get('nrofile') : '';
        }

        $check = $request->__get('check');
        $type = $request->__get('type');

        try {
            $check = ($check == 1) ? 'in' : 'out';

            $client = new Client([
                'verify' => false
            ]);

            $url = sprintf('%sboard/billing', $this->files_onedb_ms);

            $request_api = $client->get($url, [
                'query' => [
                    'customer' => $customer,
                    'check' => $check,
                    'type' => $type,
                    'fecini' => $fecini,
                    'fecfin' => $fecfin,
                    'file' => $file,
                ],
            ]);
            $response_api = json_decode($request_api->getBody()->getContents(), true);
            $detalle = $response_api['data'] ?? [];
            $_array = [];

            $monto_total_stdebe = 0;
            $monto_total_impago = 0;
            $monto_total = 0;
            $_quantity = 0;
            $_quantityFailed = 0;

            $_array = $detalle['files'] ?? [];

            foreach ($_array as $key => $value) {
                $value['saldo'] = ($value['stdebe'] - ($value['ncdebe'] + $value['impago']));

                $monto_total_stdebe += ($value['saldo'] > 0) ? $value['saldo'] : 0;
                $monto_total_impago += ($value['impago'] > 0) ? $value['impago'] : 0;
                $monto_total += ($value['stdebe'] > 0) ? $value['stdebe'] : 0;

                if ($value['saldo'] > 0) {
                    $_quantityFailed++;
                }

                $_array[$key]['saldo'] = number_format($value['saldo'], 2, ".", ",");
                $_array[$key]['stdebe'] = number_format($value['stdebe'], 2, ".", ",");
                $_array[$key]['impago'] = number_format($value['impago'], 2, ".", ",");
                $_array[$key]['ncdebe'] = number_format($value['ncdebe'], 2, ".", ",");
            }

            if ($monto_total > 0) {
                $_detail['porcentaje_stdebe'] = number_format(($monto_total_stdebe * 100 / $monto_total), 2, ".", ",");
                $_detail['porcentaje_impago'] = number_format(($monto_total_impago * 100 / $monto_total), 2, ".", ",");
            } else {
                $_detail['porcentaje_stdebe'] = number_format(0, 2, ".", ",");
                $_detail['porcentaje_impago'] = number_format(0, 2, ".", ",");
            }

            $_detail['monto_total_stdebe'] = number_format($monto_total_stdebe, 2, ".", ",");
            $_detail['monto_total_impago'] = number_format($monto_total_impago, 2, ".", ",");

            $_quantity = count($_array);

            session()->put('billings', $_array);

            return response()->json(['billings' => $_array, 'detail' => $_detail, 'quantityBillings' => $_quantity, 'quantityBillingsFailed' => $_quantityFailed]);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function orders(Request $request)
    {
        try {
            $orders_totales = session()->get('orders');
            $response = [];
            $all_stats = $this->orderStats($orders_totales);
            $quantity = count($orders_totales);

            // Agrupando por ejecutivas..
            $orders = $this->groupArrayBy($orders_totales, 'PRODUCTO');
            $colors = ['FIT' => 60, 'TAILORMADE' => 45, 'GRUPOS' => 25, 'MICE' => 22];

            foreach ($orders as $key => $value) {
                $response[$key]['stats'] = $this->orderStats($value);
                $response[$key]['orders'] = $value;

                // $stats = $response[$key]['stats'];
                $response[$key]['limit'] = $colors[$key];

                $__orders = 0;

                if ($response[$key]['stats']['all_orders'] > 0) {
                    $__orders = ceil($response[$key]['stats']['all_orders'] * $colors[$key] / 100);
                }

                $response[$key]['__orders'] = $__orders;

                /*
                if($stats['percent_placed'] > 0)
                {
                    $porcentaje = $stats['percent_placed'] * 100 / $colors[$key];
                }
                $response[$key]['porcentaje'] = number_format($porcentaje, 0);
                */
            }

            return response()->json(['sections' => $response, 'all_stats' => $all_stats, 'quantity' => $quantity]);
        } catch (\Exception $ex) {
            //
        }
    }

    public function payment(Request $request)
    {
        $mount = str_replace(array(",", ".00"), "", $request->__get('mount'));
        $file = $request->__get('file');
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $item_1 = new Item();

        $item_1->setName('Item 1')
            /** item name **/
            ->setCurrency('USD')
            ->setQuantity(1)
            ->setPrice($mount);
        /** unit price **/

        $item_list = new ItemList();
        $item_list->setItems(array($item_1));

        $amount = new Amount();
        $amount->setCurrency('USD')
            ->setTotal($mount);

        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setItemList($item_list)
            ->setDescription('Your transaction description');

        $redirect_urls = new RedirectUrls();
        $redirect_urls->setReturnUrl(url('/board/payment/success'))
            /** Specify return URL **/
            ->setCancelUrl(url('/board/payment/cancel'));

        $payment = new Payment();
        $payment->setIntent('Sale')
            ->setPayer($payer)
            ->setRedirectUrls($redirect_urls)
            ->setTransactions(array($transaction));
        /** dd($payment->create($this->_api_context));exit; **/
        try {
            $payment->create($this->_api_context);
        } catch (\PayPal\Exception\PPConnectionException $ex) {
            if (config('app.debug')) {
                session()->put('error', 'Connection timeout');
                return redirect()->to('board');
            } else {
                session()->put('error', 'Some error occur, sorry for inconvenient');
                return redirect()->to('board');
            }
        }

        foreach ($payment->getLinks() as $link) {
            if ($link->getRel() == 'approval_url') {
                $redirect_url = $link->getHref();
                break;
            }
        }

        /** add payment ID to session **/
        session()->put('paypal_payment_id', $payment->getId());

        if (isset($redirect_url)) {
            /** redirect to paypal **/
            return redirect()->away($redirect_url);
        }

        session()->put('error', 'Unknown error occurred');
        return redirect()->to('board');
    }

    // Billings..
    public function status(Request $request)
    {
        /** Get the payment ID before session clear **/
        $payment_id = session()->get('paypal_payment_id');

        /** clear the session payment ID **/
        session()->forget('paypal_payment_id');
        if (empty($request->get('PayerID')) || empty($request->get('token'))) {
            session()->put('error', 'Payment failed');
            return redirect()->to('/board');
        }

        $payment = Payment::get($payment_id, $this->_api_context);
        $execution = new PaymentExecution();
        $execution->setPayerId($request->get('PayerID'));

        /**Execute the payment **/
        $result = $payment->execute($execution, $this->_api_context);

        if ($result->getState() == 'approved') {
            // Generar los registros adicionales para generar los statements y los pagos en litopay..
            session()->put('success', 'Payment success');
            return redirect()->to('/board');
        }

        session()->put('error', 'Payment failed');
        return redirect()->to('/board');
    }

    public function teams(Request $request)
    {
        try {
            set_time_limit(0);

            $executive = auth()->user()->code;

            $client = new \GuzzleHttp\Client([
                'verify' => false,
            ]);

            $url = sprintf('%sexecutives/%s/team', $this->files_onedb_ms, $executive);
            $request_api = $client->get($url);
            $response_api = json_decode($request_api->getBody()->getContents(), true);
            $response = $response_api['data'];
            $response['teams'][''] = 'TODOS';

            return response()->json($response);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function kams(Request $request)
    {
        try {
            set_time_limit(0);

            $client = new \GuzzleHttp\Client([
                'verify' => false,
            ]);
            $url = sprintf('%sorders/kams', $this->files_onedb_ms);
            $request_api = $client->get($url);
            $response_api = json_decode($request_api->getBody()->getContents(), true);
            $kams = $response_api['data'];
            $response = [];

            foreach ($kams as $key => $value) {
                $value =  (object) $value;

                if (!isset($response[$value->codven])) {
                    $response[] = ['code' => $value->codven, 'label' => $value->codven];
                }
            }

            $response[] = ['code' => 'TODOS', 'label' => 'TODOS'];

            return response()->json(['kams' => $response, 'quantity' => count($response)]);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function executives_user(Request $request)
    {
        $usuario = $request->__get('team');

        if ($usuario == '' or $usuario == 'TODOS') {
            $usuario = auth()->user()->code;
        }

        $client = new \GuzzleHttp\Client([
            'verify' => false,
        ]);
        $url = sprintf('%sexecutives/team/%s/executives', $this->files_onedb_ms, $usuario);
        $request_api = $client->get($url);
        $response_api = json_decode($request_api->getBody()->getContents(), true);
        $executives = $response_api['data'];

        $flag = 'INFORMIX';

        if (count($executives) == 0) {
            $executives = User::select('code as value', DB::raw('concat(code, " - ", name) as text'))
                ->where('user_type_id', '=', 3)->where('status', '=', 1)
                ->where('class_code', 'like', 'QR%')->get();

            $flag = 'MySQL';
        }

        session()->put('executives_user', $executives);
        return response()->json(['flag' => $flag, 'executives' => $executives, 'quantity' => count($executives)]);
    }

    public function all_clients(Request $request)
    {

        $clients = Clients::select('code as value', DB::raw('concat(code, " - ", name) as text'))
            ->where(function ($query) use ($request) {
                $query->orWhere('code', 'like', '%' . $request->__get('client') . '%');
                $query->orWhere('name', 'like', '%' . $request->__get('client') . '%');
            })
            ->where('status', '=', 1)->limit(20)->get();

        return response()->json(['clients' => $clients, 'quantity' => count($clients)]);
    }

    public function all_executives(Request $request)
    {
        $client = new \GuzzleHttp\Client([
            'verify' => false,
        ]);
        $url = sprintf('%sexecutives/all', $this->files_onedb_ms);
        $request_api = $client->get($url);
        $response_api = json_decode($request_api->getBody()->getContents(), true);
        $executives = $response_api['data'];

        if (count($executives) == 0) {
            $executives = User::select('code as value', DB::raw('concat(code, " - ", name) as text'))
                ->where('user_type_id', '=', 3)->where('status', '=', 1)
                ->where('class_code', 'like', 'QR%')->get();
        }

        $executives[] = ['value' => '', 'text' => 'TODOS'];

        return response()->json(['executives' => $executives, 'quantity' => count($executives)]);
    }

    public function all_sectors(Request $request)
    {
        $client = new \GuzzleHttp\Client([
            'verify' => false,
        ]);

        $url = sprintf('%sorders/sectors', $this->files_onedb_ms);
        $api_request = $client->get($url);
        $api_response = json_decode($api_request->getBody()->getContents(), true);
        $sectors = isset($api_response['data']) ? $api_response['data'] : [];

        return response()->json(['sectors' => $sectors, 'quantity' => count($sectors)]);
    }

    public function all_regions(Request $request)
    {
        $client = new \GuzzleHttp\Client([
            'verify' => false,
        ]);
        $url = sprintf('%sorders/regions', $this->files_onedb_ms);
        $api_request = $client->get($url);
        $api_response = json_decode($api_request->getBody()->getContents(), true);
        $regions = isset($api_response['data']) ? $api_response['data'] : [];

        return response()->json(['regions' => $regions, 'quantity' => count($regions)]);
    }

    public function all_packages(Request $request)
    {
        $filter = $request->__get('filter');
        $lang = $request->__get('lang');

        $client = new \GuzzleHttp\Client([
            'verify' => false,
        ]);
        $url = sprintf('%sorders/packages', $this->files_onedb_ms);
        $api_request = $client->get($url, [
            'query' => [
                'filter' => $filter,
                'lang' => $lang,
            ],
        ]);
        $api_response = json_decode($api_request->getBody()->getContents(), true);
        $packages = isset($api_response['data']) ? $api_response['data'] : [];

        return response()->json(['packages' => $packages, 'quantity' => count($packages)]);
    }

    public function all_products(Request $request)
    {
        try {
            set_time_limit(0);
            $client = new \GuzzleHttp\Client();
            $url = $this->files_onedb_ms . 'orders/sectors';

            $api_request = $client->get($url);
            $api_response = json_decode($api_request->getBody()->getContents(), true);
            $sectors = isset($api_response['data']) ? $api_response['data'] : [];
            $sectors[] = array('codgru' => '', 'descri' => 'TODOS');

            $ignore = [];

            if ($request->__get('ignore') != '' and $request->__get('ignore') != null) {
                $ignore = explode(",", $request->__get('ignore'));
            }

            $response = [];

            foreach ($sectors as $key => $value) {
                $value = (array) $value;
                $codigo = isset($value['codgru']) ? $value['codgru'] : '';
                $descripcion = isset($value['descri']) ? $value['descri'] : '';

                if (!in_array($codigo, $ignore)) {
                    $response[] = array(
                        'value' => $codigo,
                        'text' => strtoupper($descripcion)
                    );
                }
            }

            return response()->json(['products' => $response, 'quantity' => count($response)]);
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function organize_orders(Request $request)
    {
        try {
            set_time_limit(0);
            $orders_totales = (array) json_decode($request->__get('orders'));
            $executive = (string) $request->__get('executive');
            $type = (string) $request->__get('type');

            // Response..
            $responseExecutives = [];
            $responseProducts = [];
            $responseCustomers = [];
            $allStats = $this->orderStats($orders_totales);

            if ($type != 'executive') {
                // Agrupando por ejecutivas..
                $orders = $this->groupArrayBy($orders_totales, 'codusu');

                foreach ($orders as $key => $value) {
                    $responseExecutives[$key]['stats'] = $this->orderStats($value);
                    $responseExecutives[$key]['orders'] = $value;
                }
            }

            if ($type != 'product') {
                // Agrupando por producto..
                $orders = $this->groupArrayBy($orders_totales, 'producto');

                foreach ($orders as $key => $value) {
                    $responseProducts[$key]['stats'] = $this->orderStats($value);
                    $responseProducts[$key]['orders'] = $value;
                }
            }

            if ($type != 'customer') {
                // Agrupando por clientes..
                $orders = $this->groupArrayBy($orders_totales, 'codigo');

                foreach ($orders as $key => $value) {
                    $responseCustomers[$key]['stats'] = $this->orderStats($value);
                    $responseCustomers[$key]['orders'] = $value;
                }
            }

            return response()->json(
                [
                    'quantity' => count($orders_totales),
                    'executives' => $responseExecutives,
                    'customers' => $responseCustomers,
                    'products' => $responseProducts,
                    'quantityExecutive' => count($responseExecutives),
                    'quantityCustomer' => count($responseCustomers),
                    'quantityProduct' => count($responseProducts),
                    'all' => [
                        'executive' => strtoupper($executive),
                        'stats' => $allStats
                    ]
                ]
            );
            // return response()->json($data);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function order_area_report(Request $request)
    {
        set_time_limit(0);

        try {
            // $orders_totales = json_decode($request->getBody()->getContents(), true);
            $orders_totales = session()->get('orders');
            $region = $request->__get('region');

            // Response..
            $responseExecutives = [];
            $responseProducts = [];
            $allStats = $this->orderStats($orders_totales);

            // Agrupando por ejecutivas..
            $orders = $this->groupArrayBy($orders_totales, 'codusu');

            foreach ($orders as $key => $value) {
                $responseExecutives[$key]['stats'] = $this->orderStats($value);
                $responseExecutives[$key]['orders'] = $value;
            }

            // Agrupando por producto..
            $orders = $this->groupArrayBy($orders_totales, 'producto');

            foreach ($orders as $key => $value) {
                $responseProducts[$key]['stats'] = $this->orderStats($value);
                $responseProducts[$key]['orders'] = $value;
            }

            session()->put('area_report', ['quantity' => count($orders_totales), 'executives' => $responseExecutives, 'products' => $responseProducts, 'all' => ['region' => strtoupper($region), 'stats' => $allStats]]);

            return response()->json(['quantity' => count($orders_totales), 'executives' => $responseExecutives, 'products' => $responseProducts, 'all' => ['region' => strtoupper($region), 'stats' => $allStats]]);
            // return response()->json($data);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function order_customer_report(Request $request)
    {
        set_time_limit(0);

        try {
            // $orders_totales = json_decode($request->getBody()->getContents(), true);
            $orders_totales = session()->get('orders');

            // Response..
            $responseCustomers = [];
            $allStats = $this->orderStats($orders_totales);

            // Agrupando por cliente..
            $orders = $this->groupArrayBy($orders_totales, 'codigo');

            foreach ($orders as $key => $value) {
                $responseCustomers[$key]['stats'] = $this->orderStats($value);
                $responseCustomers[$key]['orders'] = $value;
            }

            session()->put('customer_report', ['quantity' => count($orders_totales), 'customers' => $responseCustomers, 'all' => $allStats]);

            return response()->json(['quantity' => count($orders_totales), 'customers' => $responseCustomers, 'all' => $allStats]);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function order_ranking_report(Request $request)
    {
        set_time_limit(0);

        try {
            // $orders_totales = json_decode($request->getBody()->getContents(), true);
            $orders_totales = session()->get('orders');
            $meses = [];

            foreach ($orders_totales as $key => $value) {
                $mes = (int) date("m", strtotime($value['fecrec']));
                $meses[$mes][] = $value;
            }

            // Response..
            $responseExecutives = [];
            $responseCustomers = [];
            $responseMonths = [];

            $allStats = $this->orderStats($orders_totales);

            // Agrupando por especialista..
            $orders = $this->groupArrayBy($orders_totales, 'codusu');

            foreach ($orders as $key => $value) {
                $responseExecutives[$key]['stats'] = $this->orderStats($value);
                $responseExecutives[$key]['orders'] = $value;
            }

            // Agrupando por cliente..
            $orders = $this->groupArrayBy($orders_totales, 'codigo');

            foreach ($orders as $key => $value) {
                $responseCustomers[$key]['stats'] = $this->orderStats($value);
                $responseCustomers[$key]['orders'] = $value;
            }
            // -- FIN DEL ACUMULADO..
            $response = [
                'quantity' => count($orders_totales),
                'customers' => $responseCustomers,
                'executives' => $responseExecutives,
                'all' => $allStats
            ];

            foreach ($meses as $k => $orders_totales) {
                $mes = $this->mes($k);
                // Response..
                $responseCustomers = [];
                $responseExecutives = [];
                $allStats = $this->orderStats($orders_totales);

                // Agrupando por especialista..
                $orders = $this->groupArrayBy($orders_totales, 'codusu');

                foreach ($orders as $key => $value) {
                    $responseExecutives[$key]['stats'] = $this->orderStats($value);
                    $responseExecutives[$key]['orders'] = $value;
                }

                // Agrupando por cliente..
                $orders = $this->groupArrayBy($orders_totales, 'codigo');

                foreach ($orders as $key => $value) {
                    $responseCustomers[$key]['stats'] = $this->orderStats($value);
                    $responseCustomers[$key]['orders'] = $value;
                }

                $responseMonths[$mes] = [
                    'quantity' => count($orders_totales),
                    'customers' => $responseCustomers,
                    'executives' => $responseExecutives,
                    'all' => $allStats
                ];
            }

            session()->put('ranking_report', ['ranking' => $responseMonths, 'acumulado' => $response, 'meses' => $meses]);

            return response()->json(['ranking' => $responseMonths, 'acumulado' => $response, 'meses' => $meses]);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function test(Request $request)
    {
        $quote = 12966;
        $quotes = DB::table('quotes')->where('id', $quote)->first();

        $quote_people = DB::table('quote_people')->where('quote_id', $quote)->first();

        dd($quotes);
    }

    protected function sendMail($title, $user, $content) // Funcion para las notificaciones tambien..
    {
        $notification = new Notification;
        $notification->title = $title;
        $notification->content = $content;
        $notification->target = 1;
        $notification->type = 1;
        $notification->url = '';
        $notification->user = $user;
        $notification->status = 1;
        $notification->data = '';
        $notification->created_by = 'ADMIN';
        $notification->updated_by = 'ADMIN';
        $notification->module = '';
        $notification->save();

        $data = [
            'title' => $title,
            'content' => $content,
            'user' => $user
        ];

        Mail::to('kluizsv@gmail.com')->send(new MailReminder($data));
        $user = (object) User::where('code', '=', $user)->first();
        Mail::to($user->email)->send(new MailReminder($data));

        $pushNoti = (object) ['user' => $notification->user, 'title' => $notification->title, 'body' => $notification->content, 'click_action' => '/'];
        $this->sendPushNotification($pushNoti);
    }

    public function search_productivity(Request $request)
    {
        $client = new \GuzzleHttp\Client();

        $date_range = (array) $request->__get('dateRange');
        $fecini = $date_range['startDate'];
        $fecfin = $date_range['endDate'];
        $fecini = explode("T", $fecini);
        $fecini = date("d/m/Y", strtotime($fecini[0]));
        $fecfin = explode("T", $fecfin);
        $fecfin = date("d/m/Y", strtotime($fecfin[0]));
        $type = $request->__get('type');
        $view = $request->__get('view');
        $type_sort = $request->__get('type_sort');
        $value_sort = $request->__get('value_sort');

        $_executives = (array) $request->get('executives');
        $_teams = (array) $request->__get('teams');

        if (!empty($_teams)) {
            $_teams = array_keys($_teams);
        }

        $team = $request->__get('team') ?? 'TODOS';

        if ($team === 'TODOS') {
            $_teams = [];
        }

        $teams = [];

        foreach ($_teams as $key => $team) {
            if ($team) {
                $teams[] = "'" . $team . "'";
            }
        }

        $executives = [];

        foreach ($_executives as $key => $value) {
            if (isset($value['value'])) {
                $executives[] = "'" . $value['value'] . "'";
            }

            if (isset($value['codusu']) or isset($value['nomesp'])) {
                $executives[] = "'" . ((isset($value['codusu'])) ? trim($value['codusu']) : trim($value['nomesp'])) . "'";
            }
        }

        $data = [
            'fecini' => $fecini,
            'fecfin' => $fecfin,
            'executives' => implode(",", $executives),
            'type' => $type,
            'view' => $view,
            'teams' => implode(",", $teams),
            'type_sort' => $type_sort,
            'value_sort' => $value_sort,
        ];

        $url = sprintf('%sorders/productivity', $this->files_onedb_ms);
        $request_api = $client->get($url, [
            'query' => $data,
        ]);
        $response_api = json_decode($request_api->getBody()->getContents(), true);
        $response_api = $response_api['data'];

        $key_type = sprintf('cod%s', strtolower($type));

        $_response = $this->groupArrayBy($response_api, ($view == 'E') ? $key_type : 'codcli');
        $response = [];

        foreach ($_response as $key => $value) {
            $venta = 0;
            $beneficio = 0;
            foreach ($value as $k => $v) {
                $venta += $v['impven'];
                $beneficio += $v['impben'];
            }
            $porcentaje = ($venta > 0) ? (($beneficio / $venta) * 100) : 0;

            $response[$key]['view'] = $view;
            $response[$key]['items'] = $value;
            $response[$key]['venta'] = $venta;
            $response[$key]['beneficio'] = $beneficio;
            $response[$key]['porcentaje'] = $porcentaje;
            $response[$key]['quantity'] = count($value);

            $detail = $this->groupArrayBy($value, ($view == 'E') ? 'codcli' : $key_type);
            $_detail = [];
            foreach ($detail as $k => $v) {
                $_venta = 0;
                $_beneficio = 0;
                $_items = [];
                foreach ($v as $a => $b) {
                    $_venta += $b['impven'];
                    $_beneficio += $b['impben'];

                    $b['venta'] = $b['impven'];
                    $b['beneficio'] = $b['impben'];
                    $b['porcentaje'] = ($b['venta'] > 0) ? (($b['beneficio'] / $b['venta']) * 100) : 0;

                    $_items[] = $b;
                }
                $_porcentaje = ($_venta > 0) ? (($_beneficio / $_venta) * 100) : 0;

                $__order = [];
                if ($type_sort != '') {
                    // Obtener una lista de columnas
                    foreach ($_items as $clave => $fila) {
                        $__order[$clave] = $fila[$type_sort];
                    }

                    // Ordenar los datos con volumen descendiente, edición ascendiente
                    // Agregar $datos como el último parámetro, para ordenar por la clave común
                    array_multisort($__order, ($value_sort == 'desc') ? SORT_DESC : SORT_ASC, $_items);
                }

                $_detail[$k]['items'] = $_items;
                $_detail[$k]['venta'] = $_venta;
                $_detail[$k]['beneficio'] = $_beneficio;
                $_detail[$k]['porcentaje'] = $_porcentaje;
                $_detail[$k]['quantity'] = count($_items);
            }

            $_order = [];
            if ($type_sort != '') {
                // Obtener una lista de columnas
                foreach ($_detail as $clave => $fila) {
                    $_order[$clave] = $fila[$type_sort];
                }

                // Ordenar los datos con volumen descendiente, edición ascendiente
                // Agregar $datos como el último parámetro, para ordenar por la clave común
                array_multisort($_order, ($value_sort == 'desc') ? SORT_DESC : SORT_ASC, $_detail);
            }

            $response[$key]['detail'] = $_detail;
        }

        $order = [];
        if ($type_sort != '') {
            // Obtener una lista de columnas
            foreach ($response as $clave => $fila) {
                $order[$clave] = $fila[$type_sort];
            }

            // Ordenar los datos con volumen descendiente, edición ascendiente
            // Agregar $datos como el último parámetro, para ordenar por la clave común
            array_multisort($order, ($value_sort == 'desc') ? SORT_DESC : SORT_ASC, $response);
        }

        session()->put('productivity', ['data' => $data, 'response' => $response]);

        return response()->json(['files' => $response, 'quantity' => count($response), 'data' => $data]);
    }
}
