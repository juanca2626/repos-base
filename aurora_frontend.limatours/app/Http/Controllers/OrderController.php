<?php

namespace App\Http\Controllers;

use App\Notification;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\URL;
use App\User;
use DB;
use App\Market;
use App\Region;
use App\UserMarket;
use Illuminate\Support\Facades\App;
use Config;

class OrderController extends Controller
{
    public $markets = [];
    public $regions = [];

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:board.view');
        //$this->middleware('permission:orders.view');
    }

    public function list(Request $request)
    {
        $return = (int) $request->__get('return');
        $_dataURL = (array) $request->__get('data');
        $dataURL = [];

        $usuario = UserMarket::where('user_id', '=', Auth::user()->id)->first();

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

        return view('orders.list')->with('bossFlag', (int) $this->hasPermission('orders.boss'))->with('return', $return)->with('dataURL', $dataURL)->with('regions', $this->regions)->with('markets', $this->markets);
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

        $usuario = UserMarket::where('user_id', '=', Auth::user()->id)->first();

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

        $startDate = (date("Y-m") . "-01T" . date("H:i:s"));
        $endDate = (date("Y-m-d") . "T" . date("H:i:s"));
        if ($request->__get('startDate') != '') {
            $startDate = $request->__get('startDate');
        }
        if ($request->__get('endDate') != '') {
            $endDate = $request->__get('endDate');
        }

        $date = [
            'startDate' => $startDate,
            'endDate' => $endDate
        ];

        $format = 'response-time';
        if ($request->__get('format') == 'unspecified-orders') {
            $format = 'unspecified-orders';
        }

        return view('orders.index')->with('date', $date)->with('format', $format)
            ->with('bossFlag', (int) $this->hasPermission('orders.boss'))
            ->with('return', $return)->with('dataURL', $dataURL)
            ->with('regions', $this->regions)->with('markets', $this->markets);
    }

    public function find(Request $request)
    {
        try {
            set_time_limit(0);
            $client = new \nusoap_client('http://extranet.limatours.com.pe/OrderLITO.php?wsdl', true);
            $client->soap_defencoding = 'UTF-8';
            $client->decode_utf8 = FALSE;

            $data = [
                'nroped' => $request->__get('nroped')
            ];

            $order = $client->call('findNropedNew', ['data' => $data]);
            $order = (array) json_decode($order);

            return response()->json([$order]);
        } catch (Exception $ex) {
            //
        }
    }

    public function total(Request $request)
    {
        try {
            $fecini = '';
            $fecfin = '';
            $per_page = 30;

            if ($request->__get('dateRange') != '') {
                $date_range = (array) $request->__get('dateRange');
                $fecini = $date_range['startDate'];
                $fecfin = $date_range['endDate'];
                $fecini = explode("T", $fecini);
                $fecini = date("Y-m-d", strtotime($fecini[0]));
                $fecfin = explode("T", $fecfin);
                $fecfin = date("Y-m-d", strtotime($fecfin[0]));
            }

            $data = [
                'date_from' => $fecini,
                'date_to' => $fecfin,
                'page' => 1,
                'per_page' => 1,
            ];

            session()->forget('orders');

            $client = new \GuzzleHttp\Client();
            // $baseUrlExtra = config('app.mix_base_express_ws_url');
            $baseUrlExtra = config('services.orders_ms.domain');

            $token = $_COOKIE['token_cognito_limatour'] ?? null;

            $response = $client->get($baseUrlExtra . 'orders', [
                'query' => $data,
                /*
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept'        => 'application/json',
                ]
                */
            ]);

            $decoded = json_decode($response->getBody(), true)['data'] ?? [];

            $pagination = $decoded['pagination'] ?? [];

            $total = $pagination['total'];
            $pages = ceil($total / $per_page);

            /*
            $client = new \GuzzleHttp\Client();
            $baseUrlExtra = config('services.aurora_files.domain');

            $request = $client->request('POST', $baseUrlExtra . 'orders/total', [
                'form_params' => $data
            ]);
            $_response = (array) json_decode($request->getBody()->getContents(), true);
            $total = $_response['data'][0]['total'];
            */

            return response()->json([
                'type' => 'success',
                'total' => $total,
                'pages' => $pages,
            ]);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function search(Request $request)
    {
        try {
            $baseUrlExtra = ''; $data = [];
            $limit = $request->__get('limit');

            if ($limit != 'show') {
                $customer = $request->__get('user');
                $user_type = $request->__get('type');
                $lang = $request->__get('lang');
                $flag_report = (int) $request->__get('flag_report');

                if ($customer == '') {
                    if ($this->hasPermission('board.boss') or $this->hasPermission('orders.boss')) {
                        $customer = 'TODOS';
                        $user_type = 'E';
                    } else {
                        $customer = auth()->user()->code;
                    }
                }

                $region = $request->__get('sector');

                if ($customer != 'TODOS') {
                    $user = User::where('code', '=', $customer)->first();

                    if ($user_type != 'C') {
                        // -- REGION FOR MARKETS.. BY USER..
                        $usuario = UserMarket::where('user_id', '=', $user->id)->first();
                        $region = '';

                        if ($usuario != null) {
                            $this->markets = $usuario->markets()->get();
                        }

                        foreach ($this->markets as $key => $value) {
                            $region = $value->market_region->region->title;
                            $this->regions[$region] = $region;
                        }
                    }

                    $this->regions = implode(", ", $this->regions);
                }
                // -- MARKETS..

                $fecini = '';
                $fecfin = '';
                if ($request->__get('dateRange') != '') {
                    $date_range = (array) $request->__get('dateRange');
                    $fecini = $date_range['startDate'];
                    $fecfin = $date_range['endDate'];
                    $fecini = explode("T", $fecini);
                    $fecini = date("Y-m-d", strtotime($fecini[0]));
                    $fecfin = explode("T", $fecfin);
                    $fecfin = date("Y-m-d", strtotime($fecfin[0]));
                }

                $team = $request->__get('team');
                $product = $request->__get('product');
                $status = $request->__get('status') ?? '';
                $nroped = $request->__get('nroped');
                $kam = $request->__get('kam');

                $per_page = 30;

                if(empty($kam)) {
                    $customer = $kam;
                }

                if(!empty($team)) {
                    $customer = $team;
                }

                if($customer === 'TODOS') {
                    $customer = '';
                }

                $data = [
                    'customer' => ($user_type == 'C') ? $customer : '',
                    'executive' => ($user_type == 'E') ? $customer : '',
                    'filterBy' => ($user_type == 'C') ? 'customer' : 'executive',
                    'region' => (!empty($region)) ? str_replace(array("C", "c"), "", $region) : '', //
                    'product' => (!empty($product)) ? $product : '',
                    'status_iso' => $status, // CE | PE
                    'date_from' => $fecini,
                    'date_to' => $fecfin,
                    'filter' => (!empty($nroped)) ? $nroped : '',
                    // 'seguimiento' => '',
                    // 'team' => (!empty($team)) ? $team : '',
                    'page' => $limit,
                    'per_page' => $per_page,
                    // 'kam_code' => (!empty($kam)) ? $kam : '' // LHL
                ];

                $client = new \GuzzleHttp\Client();
                // $baseUrlExtra = config('app.mix_base_express_ws_url');
                $baseUrlExtra = config('services.orders_ms.domain');

                $token = $_COOKIE['token_cognito_limatour'] ?? null;

                $response = $client->get($baseUrlExtra . 'orders', [
                    'query' => $data,
                    /*
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token,
                        'Accept'        => 'application/json',
                    ]
                    */
                ]);

                $decoded = json_decode($response->getBody(), true)['data'] ?? [];
                $orders = $decoded['orders'] ?? [];

                /*
                if (count($orders) > 0) {
                    $orders = $this->verifyQuotes($orders, $lang, $flag_report);
                }
                */

                foreach ($orders as $key => $order) {

                    if ($order['type']['code'] == 7) {
                        $order['product']['code'] = 4;
                        $order['product']['name'] = 'TAILORMADE';

                        $orders[$key]['product'] = $order['product'];
                    }

                    $orders[$key]['fecrec'] = date("Y-m-d", strtotime($order['createdAt']));
                    $orders[$key]['nroped'] = $order['code'] ?? '';
                    $orders[$key]['nroord'] = $order['order'];
                    $orders[$key]['codsec'] = $order['product']['code'] ?? '-';
                    $orders[$key]['codusu'] = $order['executiveCode'] ?? '-';
                    $orders[$key]['codigo'] = $order['customerCode'];
                    $orders[$key]['producto'] = $order['product']['name'] ?? '-';
                    $orders[$key]['nroref'] = $order['quotations'][0]['number'] ?? '';
                    $orders[$key]['nrofile'] = $order['quotations'][0]['file']['number'] ?? '';
                    $orders[$key]['nroref_identi'] = 'B';
                    $orders[$key]['price_estimated'] = $order['quotations'][0]['prices']['estimated'] ?? '';
                    $orders[$key]['fectravel'] = $order['quotations'][0]['travelDate']['date'] ?? '';
                    $orders[$key]['fectravel_tca'] = $order['quotations'][0]['travelDate']['dateTca'] ?? '';
                    $orders[$key]['nompaq'] = '-';
                    $orders[$key]['price_end'] = $order['quotations'][0]['prices']['final'] ?? '';
                    $orders[$key]['horas'] = $order['lights']['hours'] ?? '';
                    $orders[$key]['class'] = $order['lights']['alert'] ?? '';
                }

                $orders =  $this->toArray($orders);

                if (is_array($orders)) {
                    session(['orders' => array_merge(session('orders', []), $orders)]);
                }
            } else {
                $orders = session()->get('orders');
            }

            return response()->json([
                'type' => 'success',
                'orders' => $orders,
                'url' => $baseUrlExtra,
                'payload' => $data,
                'quantity' => count($orders)
            ]);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function total_aurora(Request $request)
    {
        try {
            $fecini = '';
            $fecfin = '';
            if ($request->__get('dateRange') != '') {
                $date_range = (array) $request->__get('dateRange');
                $fecini = $date_range['startDate'];
                $fecfin = $date_range['endDate'];
                $fecini = explode("T", $fecini);
                $fecini = date("d/m/Y", strtotime($fecini[0]));
                $fecfin = explode("T", $fecfin);
                $fecfin = date("d/m/Y", strtotime($fecfin[0]));
            }

            $data = [
                'date_from' => $fecini,
                'date_to' => $fecfin,
            ];

            $client = new \GuzzleHttp\Client();
            $baseUrlExtra = config('services.aurora_files.domain');

            $request = $client->request('POST', $baseUrlExtra . 'orders/total', [
                'form_params' => $data
            ]);
            $_response = (array) json_decode($request->getBody()->getContents(), true);
            $total = $_response['data'][0]['total'];

            return response()->json([
                'type' => 'success',
                'total' => $total,
                'pages' => ceil($total / 30),
            ]);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function search_aurora(Request $request)
    {
        try {
            $baseUrlExtra = '';
            $limit = $request->__get('limit');

            if ($limit != 'show') {
                $customer = $request->__get('user');
                $user_type = $request->__get('type');
                $lang = $request->__get('lang');
                $flag_report = (int) $request->__get('flag_report');

                if ($customer == '') {
                    if ($this->hasPermission('board.boss') or $this->hasPermission('orders.boss')) {
                        $customer = 'TODOS';
                        $user_type = 'E';
                    } else {
                        $customer = auth()->user()->code;
                    }
                }

                $region = $request->__get('sector');

                if ($customer != 'TODOS') {
                    $user = User::where('code', '=', $customer)->first();

                    if ($user_type != 'C') {
                        // -- REGION FOR MARKETS.. BY USER..
                        $usuario = UserMarket::where('user_id', '=', $user->id)->first();
                        $region = '';

                        if ($usuario != null) {
                            $this->markets = $usuario->markets()->get();
                        }

                        foreach ($this->markets as $key => $value) {
                            $region = $value->market_region->region->title;
                            $this->regions[$region] = $region;
                        }
                    }

                    $this->regions = implode(", ", $this->regions);
                }
                // -- MARKETS..

                $fecini = '';
                $fecfin = '';
                if ($request->__get('dateRange') != '') {
                    $date_range = (array) $request->__get('dateRange');
                    $fecini = $date_range['startDate'];
                    $fecfin = $date_range['endDate'];
                    $fecini = explode("T", $fecini);
                    $fecini = date("d/m/Y", strtotime($fecini[0]));
                    $fecfin = explode("T", $fecfin);
                    $fecfin = date("d/m/Y", strtotime($fecfin[0]));
                }

                $team = $request->__get('team');
                $product = $request->__get('product');
                $status = $request->__get('status') ?? 'ALL';
                $nroped = $request->__get('nroped');
                $kam = $request->__get('kam');

                $data = [
                    'executive' => $customer,
                    'type' => ($user_type == 'C') ? $user_type : 'E',
                    'region' => (!empty($region)) ? str_replace(array("C", "c"), "", $region) : '',
                    'sector' => (!empty($product)) ? $product : '',
                    'state' => $status,
                    'date_from' => $fecini,
                    'date_to' => $fecfin,
                    'nroped' => (!empty($nroped)) ? $nroped : '',
                    'seguimiento' => '',
                    'team' => (!empty($team)) ? $team : '',
                    'limit' => $limit,
                    'per_page' => 30,
                    'kam' => (!empty($kam)) ? $kam : ''
                ];
                if ($limit <= 1) {
                    session()->forget('orders');
                }

                $client = new \GuzzleHttp\Client();
                // $baseUrlExtra = config('app.mix_base_express_ws_url');
                $baseUrlExtra = config('services.aurora_files.domain');

                $request = $client->request('POST', $baseUrlExtra . 'orders/search', [
                    'form_params' => $data
                ]);
                $_response = (array) json_decode($request->getBody()->getContents(), true);
                $orders = $_response['data'];

                if (count($orders) > 0) {
                    $orders = $this->verifyQuotes($orders, $lang, $flag_report);
                }

                $orders =  $this->toArray($orders);

                if (is_array($orders)) {
                    session(['orders' => array_merge(session('orders', []), $orders)]);
                }
            } else {
                $orders = session()->get('orders');
            }

            return response()->json([
                'type' => 'success',
                'orders' => $orders,
                'url' => $baseUrlExtra,
                'quantity' => count($orders)
            ]);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function search_labels(Request $request)
    {
        $identi = $request->__get('identi');

        $data = [
            'identi' => $identi
        ];

        $params = http_build_query($data);

        $client = new \GuzzleHttp\Client();
        $baseUrlExtra = config('services.aurora_extranet.domain');
        // $baseUrlLocal = 'http://localhost:9000';
        $request = $client->get($baseUrlExtra . '/api/orders/api.php?method=searchLabels&' . $params);
        $labels = json_decode($request->getBody()->getContents(), true);

        return response()->json(['labels' => $labels, 'quantity' => count($labels)]);
    }

    public function save_label(Request $request)
    {
        $data = $request->all();
        $data['user'] = Auth::user()->code;
        $params = http_build_query($data);

        $client = new \GuzzleHttp\Client();
        $baseUrlExtra = config('services.aurora_extranet.domain');
        // $baseUrlLocal = 'http://localhost:9000';
        $request = $client->get($baseUrlExtra . '/api/orders/api.php?method=saveLabel&' . $params);
        $response = json_decode($request->getBody()->getContents(), true);

        return response()->json(['response' => $response]);
    }

    public function active_label(Request $request)
    {
        $data = $request->all();
        $params = http_build_query($data);

        $client = new \GuzzleHttp\Client();
        $baseUrlExtra = config('services.aurora_extranet.domain');
        // $baseUrlLocal = 'http://localhost:9000';
        $request = $client->get($baseUrlExtra . '/api/orders/api.php?method=activeLabel&' . $params);
        $response = json_decode($request->getBody()->getContents(), true);

        return response()->json(['response' => $response]);
    }

    public function remove_label(Request $request)
    {
        $data = $request->all();
        $data['usuario'] = Auth::user()->code;
        $params = http_build_query($data);

        $client = new \GuzzleHttp\Client();
        $baseUrlExtra = config('services.aurora_extranet.domain');
        // $baseUrlLocal = 'http://localhost:9000';
        $request = $client->get($baseUrlExtra . '/api/orders/api.php?method=removeLabel&' . $params);
        $response = json_decode($request->getBody()->getContents(), true);

        return response()->json(['response' => $response]);
    }

    public function search_media_tracing(Request $request)
    {
        $client = new \GuzzleHttp\Client();
        $baseUrlExtra = config('services.aurora_extranet.domain');
        // $baseUrlLocal = 'http://localhost:9000';
        $request = $client->get($baseUrlExtra . '/api/orders/api.php?method=searchMediaTracing');
        $media = json_decode($request->getBody()->getContents(), true);

        return response()->json(['media' => $media, 'quantity' => count($media)]);
    }

    public function save_tracing(Request $request)
    {
        $_data = $request->all();
        $data = [];
        $ignore = ['lang', 'order'];

        foreach ($_data as $key => $value) {
            if (!in_array($key, $ignore)) {
                $data[$key] = $value;
            }
        }

        $params = http_build_query($data);

        $notification = new Notification;
        $notification->title = "Notificación de Seguimiento";
        $notification->content = "Se generó una notificación de seguimiento en el pedido: " . $_data['nroped'];
        $notification->target = 1;
        $notification->type = 2;
        $notification->url = 'loadTracing';
        $notification->user = $_data['correo_desde'];
        $notification->status = 1;
        $notification->data = json_encode(['order' => $_data['order']]);
        $notification->created_by = Auth::user()->code;
        $notification->updated_by = Auth::user()->code;
        $notification->module = 'orders';
        $notification->created_at = date("Y-m-d H:i:s", strtotime($_data['fecha']));
        $notification->updated_at = date("Y-m-d H:i:s", strtotime($_data['fecha']));
        $notification->save();

        $client = new \GuzzleHttp\Client();
        $baseUrlExtra = config('services.aurora_extranet.domain');
        // $baseUrlLocal = 'http://localhost:9000';
        $request = $client->get($baseUrlExtra . '/api/orders/api.php?method=saveTracing&' . $params);
        $response = json_decode($request->getBody()->getContents(), true);

        return response()->json(['response' => $response]);
    }

    public function search_status_tracing(Request $request)
    {
        $client = new \GuzzleHttp\Client();
        $baseUrlExtra = config('services.aurora_extranet.domain');
        // $baseUrlLocal = 'http://localhost:9000';
        $request = $client->get($baseUrlExtra . '/api/orders/api.php?method=searchStatusTracing');
        $status = json_decode($request->getBody()->getContents(), true);

        return response()->json(['status' => $status, 'quantity' => count($status)]);
    }

    public function search_emails(Request $request)
    {
        $nroped = $request->__get('nroped');

        $data = [
            'nroped' => $nroped,
            'tipo' => 1
        ];

        $params = http_build_query($data);

        $client = new \GuzzleHttp\Client();
        $baseUrlExtra = config('services.aurora_extranet.domain');
        // $baseUrlLocal = 'http://localhost:9000';
        $request = $client->get($baseUrlExtra . '/api/orders/api.php?method=searchEmails&' . $params);
        $tracings = json_decode($request->getBody()->getContents(), true);

        foreach ($tracings as $key => $value) {
            $tracings[$key]['ACCION'] = '';
        }

        return response()->json(['tracings' => $tracings, 'data' => $data, 'params' => $params, 'quantity' => count($tracings)]);
    }

    public function search_tracings(Request $request)
    {
        $nroped = $request->__get('nroped');

        $data = [
            'nroped' => $nroped,
            'tipo' => 1
        ];

        $params = http_build_query($data);

        $client = new \GuzzleHttp\Client();
        $baseUrlExtra = config('services.aurora_extranet.domain');
        // $baseUrlLocal = 'http://localhost:9000';
        $request = $client->get($baseUrlExtra . '/api/orders/api.php?method=searchTracings&' . $params);
        $tracings = json_decode($request->getBody()->getContents(), true);

        foreach ($tracings as $key => $value) {
            $tracings[$key]['ACCION'] = '';
        }

        return response()->json(['tracings' => $tracings, 'data' => $data, 'params' => $params, 'quantity' => count($tracings)]);
    }

    public function find_file_tracing(Request $request)
    {
        $nroped = $request->__get('nroped');
        $fecreg = $request->__get('fecreg');
        $horreg = $request->__get('horreg');

        $data = [
            'nroped' => $nroped,
            'fecreg' => $fecreg,
            'horreg' => $horreg
        ];

        $params = http_build_query($data);

        $client = new \GuzzleHttp\Client();
        $baseUrlExtra = config('services.aurora_extranet.domain');
        // $baseUrlLocal = 'http://localhost:9000';
        $request = $client->get($baseUrlExtra . '/api/orders/api.php?method=findAccionTracing&' . $params);
        $response = json_decode($request->getBody()->getContents(), true);

        return response()->json(['content' => $response]);
    }

    public function find_content_tracing(Request $request)
    {
        $nroped = $request->__get('nroped');
        $file = $request->__get('file');

        $content = '';

        try {
            $client = new \GuzzleHttp\Client();
            $baseUrlExtra = config('services.aurora_extranet.domain');
            $request = $client->get($baseUrlExtra . '/logs/pedidos/' . $nroped . '/' . $file);
            $content = $request->getBody()->getContents();
        } catch (\Exception $ex) {
            $content = '';
        }

        return response()->json(['content' => $content, 'nroped' => $nroped, 'file' => $file]);
    }

    public function reassign(Request $request)
    {
        $data = $request->all();
        $params = http_build_query($data);

        $client = new \GuzzleHttp\Client();
        $baseUrlExtra = config('services.aurora_extranet.domain');
        // $baseUrlLocal = 'http://localhost:9000';
        $request = $client->get($baseUrlExtra . '/api/orders/api.php?method=reassign&' . $params);
        $response = json_decode($request->getBody()->getContents(), true);

        return response()->json(['response' => $response]);
    }

    public function update(Request $request)
    {
        $data = $request->all();
        $params = http_build_query($data);

        $client = new \GuzzleHttp\Client();
        $baseUrlExtra = config('services.aurora_extranet.domain');
        // $baseUrlLocal = 'http://localhost:9000';
        $request = $client->get($baseUrlExtra . '/api/orders/api.php?method=update&' . $params);
        $response = json_decode($request->getBody()->getContents(), true);

        return response()->json(['response' => $response]);
    }

    public function update_response(Request $request)
    {
        $data = $request->all();
        $params = http_build_query($data);

        $client = new \GuzzleHttp\Client();
        $baseUrlExtra = config('services.aurora_extranet.domain');
        // $baseUrlLocal = 'http://localhost:9000';
        // dd($baseUrlExtra.'/api/orders/api.php?method=updateResponse&'. $params);
        $request = $client->get($baseUrlExtra . '/api/orders/api.php?method=updateResponse&' . $params);
        $response = json_decode($request->getBody()->getContents(), true);

        return response()->json(['response' => $response]);
    }

    public function update_obs(Request $request)
    {
        $data = $request->all();
        $params = http_build_query($data);

        $client = new \GuzzleHttp\Client();
        $baseUrlExtra = config('services.aurora_extranet.domain');
        // $baseUrlLocal = 'http://localhost:9000';
        // dd($baseUrlExtra.'/api/orders/api.php?method=updateObs&'. $params);
        $request = $client->get($baseUrlExtra . '/api/orders/api.php?method=updateObs&' . $params);
        $response = json_decode($request->getBody()->getContents(), true);

        return response()->json(['response' => $response]);
    }

    public function remove(Request $request)
    {
        $data = $request->all();
        $params = http_build_query($data);

        $client = new \GuzzleHttp\Client();
        $baseUrlExtra = config('services.aurora_extranet.domain');
        // $baseUrlLocal = 'http://localhost:9000';
        // dd($baseUrlExtra.'/api/orders/api.php?method=deleteOrder&'. $params);
        $request = $client->get($baseUrlExtra . '/api/orders/api.php?method=deleteOrder&' . $params);
        $response = json_decode($request->getBody()->getContents(), true);

        return response()->json(['response' => $response]);
    }

    public function dashboard(Request $request)
    {
        try {
            set_time_limit(0);

            $customer = $request->__get('user');
            $user_type = $request->__get('type');

            if ($customer == '' or $customer == 'TODOS') {
                if ($this->hasPermission('orders.boss')) {
                    $customer = 'TODOS';
                    $user_type = 'E';
                }

                $user = Auth::user();
            } else {
                $user = User::where('code', '=', $customer)->first();
            }

            // -- REGION FOR MARKETS.. BY USER..
            $usuario = UserMarket::where('user_id', '=', $user->id)->first();
            $region = '';

            if ($usuario != null) {
                $this->markets = $usuario->markets()->get();
            }

            foreach ($this->markets as $key => $value) {
                $region = $value->market_region->region->title;
                $this->regions[$region] = $region;
            }

            $this->regions = implode(", ", $this->regions);
            // -- MARKETS..

            $date_range = (array) $request->__get('dateRange');
            $fecini = $date_range['startDate'];
            $fecfin = $date_range['endDate'];

            $fecini = explode("T", $fecini);
            $fecini = date("d/m/Y", strtotime($fecini[0]));
            $fecfin = explode("T", $fecfin);
            $fecfin = date("d/m/Y", strtotime($fecfin[0]));
            $team = $request->__get('team');
            $product = $request->__get('product');
            $status = $request->__get('status');

            $data = [
                'executive' => $customer,
                'type' => $user_type,
                'region' => str_replace(array("C", "c"), "", $region),
                'sector' => ($product != '' and $product != NULL) ? $product : '', // tipo de producto..
                'state' => $status,
                'fecini' => $fecini,
                'fecfin' => $fecfin,
                'nroped' => '',
                'seguimiento' => '',
                'team' => ($team != '' and $team != NULL) ? $team : '',
                'limit' => 0
            ];

            $params = http_build_query($data);

            $client = new \GuzzleHttp\Client();
            $baseUrlExtra = config('services.aurora_extranet.domain');
            // $baseUrlLocal = 'http://localhost:9000';
            $request = $client->get($baseUrlExtra . '/api/orders/api.php?method=searchDashboard&' . $params);
            $orders = json_decode($request->getBody()->getContents(), true);

            //$orders = $client->call('searchDashboard', ['data' => $data]);
            //$orders = (array) json_decode($orders);

            return response()->json(['orders' => $orders, 'params' => $data, 'quantity' => count($orders)]);
        } catch (Exception $ex) {
            //
        }
    }

    public function report_by_executive(Request $request)
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

            $orders = session()->get('orders');
            $_orders = array();
            $ignore = '';
            $executives = '';

            foreach ($orders as $key => $value) {
                if ($key > 0) {
                    $ignore .= ', ';
                    $executives .= ', ';
                }
                $ignore .= "'" . $value['NROPED'] . "'";
                $executives .= "'" . trim($value['CODUSU']) . "'";

                $_orders[trim($value['CODUSU'])][] = $value;
            }

            $data = [
                'fecini' => $fecini,
                'fecfin' => $fecfin,
                'ignore' => $ignore,
                'executives' => $executives
            ];

            $params = http_build_query($data);

            $client = new \GuzzleHttp\Client();
            $baseUrlExtra = config('services.aurora_extranet.domain');
            // $baseUrlLocal = 'http://localhost:9000';

            // dd($baseUrlExtra.'/api/orders/api.php?method=searchReportByExecutive&'. $params);
            $request = $client->get($baseUrlExtra . '/api/orders/api.php?method=searchReportByExecutive&' . $params);
            $_response = json_decode($request->getBody()->getContents(), true); // promedio de files..

            $_executives = explode(",", $executives);
            $detail = array();
            $response = array();

            foreach ($_executives as $k => $v) {
                $v = str_replace("'", "", trim($v));
                $quantity = 0;

                foreach ($_orders[$v] as $key => $value) {
                    if ($value['ESTADO'] == 'PE') {
                        $quantity++;
                    }

                    $detail[$v][$value['NROPED'] . '-' . $value['NROORD']] = array('CLIENTE' => trim($value['RAZON']), 'CODIGO' => trim($value['CODIGO']), 'NROPED' => $value['NROPED'], 'ESTADO' => $value['ESTADO']);
                }

                $response[$v]['ORDERS_PENDIENTES'] = $quantity;
                $response[$v]['ORDERS_TOTALES'] = count($_orders[$v]);
                $response[$v]['FILES'] = $_response['PROMEDIO'][$v];
                $response[$v]['FILES_TOTALES'] = isset($_response['FILES'][$v]) ? $_response['FILES'][$v] : [];
            }

            return response()->json(['ITEMS' => $response, 'DETAIL' => $detail, 'params' => $data, 'quantity' => count($detail)]);
        } catch (Exception $ex) {
        }
    }


    public function searchPendByClient($code)
    {
        try {
            set_time_limit(0);
            $client = new \nusoap_client('http://extranet.limatours.com.pe/OrderLITO.php?wsdl', 'wsdl');
            //            $client = new \nusoap_client('http://localhost:9000/OrderLITO.php?wsdl', 'wsdl');
            $client->soap_defencoding = 'UTF-8';
            $client->decode_utf8 = FALSE;

            //            $codcli = '9CRUSE';
            $codcli = $code;

            $data = [
                'codcli' => $codcli
            ];

            $response = $client->call('searchPendByClient', ['data' => $data]);
            $response = (array) json_decode($response);

            return response()->json(['data' => $response]);
        } catch (Exception $ex) {
        }
    }

    public function relate(Request $request)
    {
        try {
            set_time_limit(0);
            $client = new \nusoap_client('http://extranet.limatours.com.pe/OrderLITO.php?wsdl', 'wsdl');
            //            $client = new \nusoap_client('http://localhost:9000/OrderLITO.php?wsdl', 'wsdl');
            $client->soap_defencoding = 'UTF-8';
            $client->decode_utf8 = FALSE;

            $nroref = $request->input('nroref');
            $nroped = $request->input('nroped');
            $nroord = $request->input('nroord');

            $data = [
                'nroref' => $nroref,
                'nroped' => $nroped,
                'nroord' => $nroord
            ];

            $response = $client->call('doRelaciOrder', ['data' => $data]);
            $response = (array) json_decode($response);

            return response()->json(['data' => $response]);
        } catch (Exception $ex) {
        }
    }
}
