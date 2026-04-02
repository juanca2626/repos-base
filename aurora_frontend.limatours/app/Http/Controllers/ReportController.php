<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReportController extends Controller
{
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

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('reports.index');
    }

    /**
     * @param Request $request
     * @since 2019
     * @author Luis Shepherd
     */
    public function dashboard(Request $request)
    {
        $date = [
            'startDate' => (date("Y-m") . "-01T" . date("H:i:s")),
            'endDate' => (date("Y-m-d") . "T" . date("H:i:s"))
        ];

        if ($this->hasPermission('stadisticcharts.view')) {
            return view('reports.dashboard')->with('date', $date);
        } else {
            return abort(404);
        }
    }

    public function excel(Request $request)
    {
        return view('reports.orders.excel');
    }

    public function orders(Request $request)
    {
        return view('reports.orders.index');
    }

    public function files(Request $request)
    {
        return view('reports.files.index');
    }

    public function files_operations(Request $request)
    {
        return view('reports.files.pendings_files')->with('bossFlag', (int) $this->hasPermission('board.boss'));
    }

    public function files_statements(Request $request)
    {
        return view('reports.files.pendings_statements')->with('bossFlag', (int) $this->hasPermission('board.boss'));
    }

    public function cosig(Request $request)
    {
        try {
            return view('reports.cosig.index');
        } catch (\Exception $ex) {
            return abort(404);
        }
    }

    public function reports_dashboard_files_concretados(Request $request)
    {
        try {
            $date_range = (array) $request->__get('dateRange');
            $fecini = $date_range['startDate'];
            $fecfin = $date_range['endDate'];

            $fecini = explode("T", $fecini);
            $fecini = date("d/m/Y", strtotime($fecini[0]));
            $fecfin = explode("T", $fecfin);
            $fecfin = date("d/m/Y", strtotime($fecfin[0]));
            $user = $request->__get('user');
            $type = $request->__get('type');
            $filter = $request->__get('filter');
            $product = $request->__get('product');

            $data = [
                'filter' => $filter,
                'user' => $user,
                'type' => $type,
                'sector' => ($product != '' and $product != NULL) ? $product : '',
                'state' => 'XL',
                'fecini' => $fecini,
                'fecfin' => $fecfin,
            ];

            $client = new \GuzzleHttp\Client([
                'verify' => false,
            ]);

            $url = sprintf('%sorders/files-concretados', $this->files_onedb_ms);

            $request_api = $client->get($url, [
                'query' => $data,
            ]);
            $response_api = json_decode($request_api->getBody()->getContents(), true);
            $response = $response_api['data'] ?? [];

            return response()->json([
                'users' => $response,
                'params' => $data,
                'quantity' => count($response)
            ]);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function reports_dashboard_cotizaciones_realizadas(Request $request)
    {
        try {
            $date_range = (array) $request->__get('dateRange');
            $fecini = $date_range['startDate'];
            $fecfin = $date_range['endDate'];

            $fecini = explode("T", $fecini);
            $fecini = date("d/m/Y", strtotime($fecini[0]));
            $fecfin = explode("T", $fecfin);
            $fecfin = date("d/m/Y", strtotime($fecfin[0]));
            $user = $request->__get('user');
            $type = $request->__get('type');
            $filter = $request->__get('filter');
            $product = $request->__get('product');

            $data = [
                'filter' => $filter,
                'user' => $user,
                'type' => $type,
                'sector' => ($product != '' and $product != NULL) ? $product : '',
                'state' => 'XL',
                'fecini' => $fecini,
                'fecfin' => $fecfin,
            ];

            $client = new \GuzzleHttp\Client([
                'verify' => false,
            ]);

            $url = sprintf('%sorders/cotis-realizadas', $this->files_onedb_ms);
            $request_api = $client->get($url, [
                'query' => $data,
            ]);
            $response_api = json_decode($request_api->getBody()->getContents(), true);
            $response = $response_api['data'] ?? [];

            return response()->json([
                'users' => $response,
                'params' => $data,
                'quantity' => count($response)
            ]);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function reports_dashboard_report_files(Request $request)
    {
        try {
            $date_range = (array) $request->__get('dateRange');
            $fecini = $date_range['startDate'];
            $fecfin = $date_range['endDate'];

            $fecini = explode("T", $fecini);
            $fecini = date("d/m/Y", strtotime($fecini[0]));
            $fecfin = explode("T", $fecfin);
            $fecfin = date("d/m/Y", strtotime($fecfin[0]));
            $user = $request->__get('user');
            $type = $request->__get('type');
            $filter = $request->__get('filter');
            $product = $request->__get('product');
            $package = $request->__get('package');

            $data = [
                'filter' => $filter,
                'user' => ($user != '' and $user != NULL) ? $user : 'TODOS',
                'type' => $type,
                'sector' => ($product != '' and $product != NULL) ? $product : '',
                'paquete' => ($package != '' and $package != NULL) ? $package : '',
                'state' => 'XL',
                'fecini' => $fecini,
                'fecfin' => $fecfin,
            ];

            $client = new \GuzzleHttp\Client([
                'verify' => false,
            ]);

            $url = sprintf('%sorders/report-files', $this->files_onedb_ms);
            $request_api = $client->get($url, [
                'query' => $data,
            ]);
            $response_api = json_decode($request_api->getBody()->getContents(), true);
            $response = $response_api['data'] ?? [];

            return response()->json([
                'users' => $response,
                'params' => $data,
                'quantity' => count($response)
            ]);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function search_files(Request $request)
    {
        $hoy = date("d/m/Y");
        $siete_dias = date("d/m/Y", strtotime("+7 days", strtotime(date("Y-m-d"))));
        $quince_dias = date("d/m/Y", strtotime("+15 days", strtotime(date("Y-m-d"))));
        $treinta_dias = date("d/m/Y", strtotime("+1 month", strtotime(date("Y-m-d"))));
        $sesenta_dias = date("d/m/Y", strtotime("+2 month", strtotime(date("Y-m-d"))));

        $executives = (array) $request->__get('executives');
        $module = $request->__get('module');
        $groups = [];
        $_executives = [];

        foreach ($executives as $key => $value) {
            $_executives[] = (@$value['value'] != '') ? $value['value'] : $value['NOMESP'];
        }

        $_executive = implode(",", $_executives);

        try {
            $client = new \GuzzleHttp\Client([
                'verify' => false,
            ]);
            $response_ = [];

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

            $items = ['siete', 'quince', 'treinta', 'mes'];
            $files_totales = 0;
            $tareas_totales = 0;
            $vips = 0;
            $files_siete = [];
            $files_quince = [];
            $files_treinta = [];
            $files_mes = [];

            $_sectores = [
                'FITS' => ['C1A1', 'C1A5', 'C1A9'],
                'GRUPOS & SERIES' => ['C1A2', 'C1A3', 'C1A4', 'C1A7'],
                'OTROS' => ['C1A0', 'C1A6'],
                'EXCEPCIONES' => []

            ];

            $_clientes = [
                'FITS' => [],
                'GRUPOS & SERIES' => [],
                'OTROS' => [],
                'EXCEPCIONES' => ['2SILVE', '3HOLLA', '2SEABO', '2STEXP', '3VIKI', '3MSC', '2AZAMA', '2INFI', '3PRINT']
            ];

            foreach ($groups as $_executive => $group) {
                $_response = $group;

                $files_totales = 0;
                $tareas_totales = 0;
                $vips = 0;
                $files_siete = [];
                $files_quince = [];
                $files_treinta = [];
                $files_mes = [];
                $response_siete = [];
                $response_quince = [];
                $response_treinta = [];
                $response_mes = [];

                foreach ($_response as $k => $item) {
                    $$k = $item;
                }

                foreach ($items as $k => $v) {
                    $files = 'files_' . $v;
                    $response = 'response_' . $v;
                    $_nroref = [];
                    $_modulo = [];
                    $_vip = [];
                    $$files['FITS'] = [];
                    $$files['GRUPOS & SERIES'] = [];
                    $$files['OTROS'] = [];
                    $$files['EXCEPCIONES'] = [];

                    if (isset($$response['nroref']) and is_array($$response['nroref'])) {
                        // Limpiando valores que vienen por el webservice..
                        foreach ($$response['nroref'] as $key => $value) {
                            if ($module == '' or $module == $$response['module'][$key]) {
                                if (trim($$response['nroref'][$key]) != '' && trim($$response['nroref'][$key]) != NULL) {
                                    foreach ($_sectores as $a => $b) {
                                        if (
                                            in_array($$response['codsec'][$key], $b) or
                                            in_array($$response['codsec'][$key], $_clientes[$a])
                                        ) {
                                            $$files[$a][] = [
                                                'modulo' => $$response['module'][$key],
                                                'nroref' => $$response['nroref'][$key],
                                                'reference' => @$$response['nomfil'][$key],
                                                'codcli' => $$response['codcli'][$key],
                                                'codsec' => $$response['codsec'][$key],
                                                'codqrr' => @$$response['codqrr'][$key],
                                                'codope' => $$response['codope'][$key],
                                                'fecin' => @$$response['diain'][$key]
                                            ];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                $response_[$_executive] = [
                    'dashboard' => [
                        'fecha_7' => $siete_dias,
                        'fecha_15' => $quince_dias,
                        'fecha_30' => $treinta_dias,
                        'response_7' => ['files' => $files_siete, 'response' => $response_siete],
                        'response_15' => ['files' => $files_quince, 'response' => $response_quince],
                        'response_30' => ['files' => $files_treinta, 'response' => $response_treinta],
                        'response_mes' => ['files' => $files_mes, 'response' => $response_mes],
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

            return response()->json($response_);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function cosig_access(Request $request)
    {
        try {
            $client = new \GuzzleHttp\Client();
            $response = [];

            $date_range = (array) $request->__get('date_range');
            $fecini = $date_range['startDate'];
            $fecfin = $date_range['endDate'];

            $fecini = explode("T", $fecini);
            $fecini = date("Y-m-d", strtotime($fecini[0]));
            $fecfin = explode("T", $fecfin);
            $fecfin = date("Y-m-d", strtotime($fecfin[0]));
            $sector = (int) $request->__get('sector');
            $type = $request->__get('type');

            $params = [
                'fecini' => $fecini,
                'fecfin' => $fecfin,
                'sector' => $sector,
                'type' => $type,
            ];
            $_request = $client->get($this->baseUrlExtra . 'api/cosig/access?' . http_build_query($params));
            $response = (array) json_decode($_request->getBody()->getContents(), true);
            $response['endpoint'] = $this->baseUrlExtra;

            return response()->json($response);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function cosig_clients(Request $request)
    {
        try {
            $client = new \GuzzleHttp\Client();
            $response = [];

            $date_range = (array) $request->__get('date_range');
            $fecini = $date_range['startDate'];
            $fecfin = $date_range['endDate'];

            $fecini = explode("T", $fecini);
            $fecini = date("Y-m-d", strtotime($fecini[0]));
            $fecfin = explode("T", $fecfin);
            $fecfin = date("Y-m-d", strtotime($fecfin[0]));
            $sector = (int) $request->__get('sector');

            $params = [
                'fecini' => $fecini,
                'fecfin' => $fecfin,
                'sector' => $sector,
            ];
            $_request = $client->get($this->baseUrlExtra . 'api/cosig/clients?' . http_build_query($params));
            $response = (array) json_decode($_request->getBody()->getContents(), true);
            $response['endpoint'] = $this->baseUrlExtra;

            return response()->json($response);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }
}
