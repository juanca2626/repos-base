<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public $files_onedb_ms = '';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:mfcustomercard.read');
        $this->files_onedb_ms = config('services.aurora_files.domain');
    }

    public function card()
    {
        return view('customers.card');
    }

    //    public function search_customers(Request $request)
    //    {
    //        $customer = $request->__get('customer');
    //        $data = [
    //            'client' => $customer
    //        ];
    //
    //        $params = http_build_query($data);
    //
    //        $client = new \GuzzleHttp\Client();
    //        $baseUrlExtra = config('services.aurora_extranet.domain');
    //        $request = $client->get($baseUrlExtra.'/api/customers/api.php?method=searchCustomers&'. $params);
    //        $customers = json_decode($request->getBody()->getContents(), true);
    //
    //        foreach($customers as $key => $value)
    //        {
    //            $customers[$key] = ['label' => $value['CODIGO'] . ' - ' . $value['RAZON'], 'code' => $value['CODIGO']];
    //        }
    //
    //        return response()->json([
    //            'customers' => $customers,
    //            'quantity' => count($customers)
    //        ]);
    //    }

    public function search_customers(Request $request)
    {

        $customer = $request->__get('customer');

        $data = [
            'take' => 10,
            'code' => $customer
        ];

        $client = new \GuzzleHttp\Client();
        $url = $this->files_onedb_ms . 'customers';
        $api_request = $client->get($url, [
            'query' => $data
        ]);

        $api_response = json_decode($api_request->getBody()->getContents(), true);
        $customers = isset($api_response['data']) ? $api_response['data'] : [];

        foreach ($customers as $key => $value) {
            $customers[$key] = ['label' => trim($value['codigo']) . ' - ' . $value['razon'], 'code' => trim($value['codigo'])];
        }

        return response()->json([
            'customers' => $customers,
            'quantity' => count($customers)
        ]);
    }

    public function filter_data(Request $request)
    {
        $format = $request->__get('format');
        return $this->$format($request);
    }

    //    public function update(Request $request)
    //    {
    //        $__params = $request->__get('params'); $customer = $request->__get('customer');
    //        $type = $request->__get('type');
    //
    //        $data = [
    //            '__params' => $__params,
    //            'type' => $type,
    //            'customer' => $customer
    //        ];
    //
    //        $params = http_build_query($data);
    //
    //        $client = new \GuzzleHttp\Client();
    //        $baseUrlExtra = config('services.aurora_extranet.domain');
    //        $request = $client->get($baseUrlExtra.'/api/customers/api.php?method=update&'. $params);
    //        $response = json_decode($request->getBody()->getContents(), true);
    //
    //        return response()->json($response);
    //    }
    public function update(Request $request)
    {
        try {
            $__params = $request->__get('params');
            $customer = $request->__get('customer');
            $type = $request->__get('type');

            $client = new \GuzzleHttp\Client();

            // Usamos la nueva ruta base y apuntamos al endpoint de actualización
            $url = $this->files_onedb_ms . 'customers/update';

            // Enviamos la petición por POST con formato JSON
            $api_request = $client->post($url, [
                'json' => [
                    '__params' => $__params,
                    'type' => $type,
                    'customer' => $customer
                ]
            ]);

            $response = json_decode($api_request->getBody()->getContents(), true);

            return response()->json($response);
        } catch (\Exception $ex) {
            return response()->json(['error' => $ex->getMessage()]);
        }
    }

    //    public function find_policies(Request $request)
    //    {
    //        $customer = $request->__get('customer');
    //
    //        $data = [
    //            'customer' => $customer
    //        ];
    //
    //        $params = http_build_query($data);
    //
    //        $client = new \GuzzleHttp\Client();
    //        $baseUrlExtra = config('services.aurora_extranet.domain');
    //        $request = $client->get($baseUrlExtra.'/api/customers/api.php?method=find_policies&'. $params);
    //        $response = json_decode($request->getBody()->getContents(), true);
    //
    //        return response()->json($response);
    //    }

    public function find_policies(Request $request)
    {
        try {
            $customer = $request->__get('customer');

            $client = new \GuzzleHttp\Client();
            $url = $this->files_onedb_ms . 'customers/policies';
            $api_request = $client->get($url, [
                'query' => [
                    'customer' => $customer
                ]
            ]);

            $api_response = json_decode($api_request->getBody()->getContents(), true);
            $result = isset($api_response['data']) ? $api_response['data'] : [];

            return response()->json($result);
        } catch (\Exception $ex) {
            return response()->json([]);
        }
    }

    //    public function info_general(Request $request)
    //    {
    //        $customer = $request->__get('customer');
    //        $data = [
    //            'customer' => $customer
    //        ];
    //
    //        $params = http_build_query($data);
    //
    //        $client = new \GuzzleHttp\Client();
    //        $baseUrlExtra = config('services.aurora_extranet.domain');
    //        $request = $client->get($baseUrlExtra.'/api/customers/api.php?method=info&'. $params);
    //        $response = json_decode($request->getBody()->getContents(), true);
    //
    //        return response()->json($response);
    //    }

    public function info_general(Request $request)
    {
        try {
            $customer = $request->__get('customer');

            $client = new \GuzzleHttp\Client();
            $url = $this->files_onedb_ms . 'customers/info';
            $api_request = $client->get($url, [
                'query' => [
                    'customer' => $customer
                ]
            ]);

            $api_response = json_decode($api_request->getBody()->getContents(), true);
            $result = isset($api_response['data']) ? $api_response['data'] : [];

            return response()->json($result);
        } catch (\Exception $ex) {
            return response()->json([]);
        }
    }

    //    public function facturacion(Request $request)
    //    {
    //        $customer = $request->__get('customer');
    //        $data = [
    //            'customer' => $customer
    //        ];
    //
    //        $params = http_build_query($data);
    //
    //        $client = new \GuzzleHttp\Client();
    //        $baseUrlExtra = config('services.aurora_extranet.domain');
    //        $request = $client->get($baseUrlExtra.'/api/customers/api.php?method=facturacion&'. $params);
    //        $response = json_decode($request->getBody()->getContents(), true);
    //
    //        return response()->json($response);
    //    }

    public function facturacion(Request $request)
    {
        try {
            $customer = $request->__get('customer');

            $client = new \GuzzleHttp\Client();
            $url = $this->files_onedb_ms . 'customers/billing';
            $api_request = $client->get($url, [
                'query' => [
                    'customer' => $customer
                ]
            ]);

            $api_response = json_decode($api_request->getBody()->getContents(), true);
            $result = isset($api_response['data']) ? $api_response['data'] : [];

            return response()->json($result);
        } catch (\Exception $ex) {
            return response()->json([]);
        }
    }

    //    public function emergencia(Request $request)
    //    {
    //        $customer = $request->__get('customer');
    //        $data = [
    //            'customer' => $customer
    //        ];
    //
    //        $params = http_build_query($data);
    //
    //        $client = new \GuzzleHttp\Client();
    //        $baseUrlExtra = config('services.aurora_extranet.domain');
    //        $request = $client->get($baseUrlExtra.'/api/customers/api.php?method=emergencia&'. $params);
    //        $response = json_decode($request->getBody()->getContents(), true);
    //
    //        return response()->json($response);
    //    }

    public function emergencia(Request $request)
    {
        try {
            $customer = $request->__get('customer');
            $client = new \GuzzleHttp\Client();

            // Usamos la nueva ruta raíz y pasamos el customer como parámetro en la URL
            $url = $this->files_onedb_ms . 'customers/emergency?customer=' . $customer;

            $api_request = $client->get($url);
            $response = json_decode($api_request->getBody()->getContents(), true);

            return response()->json($response);
        } catch (\Exception $ex) {
            // En caso de error en el microservicio, devolvemos un JSON con el error
            // o la misma estructura vacía para no romper el frontend
            return response()->json([
                'title' => '<i class="fa fa-ban"></i> Error',
                'text' => $ex->getMessage(),
                'contacts' => [],
                'idiomas' => []
            ]);
        }
    }

    //    public function agregar_emergencia(Request $request)
    //    {
    //        $__params = $request->__get('params');
    //        $customer = $request->__get('customer');
    //        $data = [
    //            '__params' => $__params,
    //            'customer' => $customer
    //        ];
    //
    //        $params = http_build_query($data);
    //
    //        $client = new \GuzzleHttp\Client();
    //        $baseUrlExtra = config('services.aurora_extranet.domain');
    //        $request = $client->get($baseUrlExtra.'/api/customers/api.php?method=agregar_emergencia&'. $params);
    //        $response = json_decode($request->getBody()->getContents(), true);
    //
    //        return response()->json($response);
    //    }

    public function agregar_emergencia(Request $request)
    {
        try {
            $__params = $request->__get('params');
            $customer = $request->__get('customer');

            $client = new \GuzzleHttp\Client();

            // Usamos la nueva ruta base y apuntamos al endpoint POST
            $url = $this->files_onedb_ms . 'customers/emergency';

            $api_request = $client->post($url, [
                'json' => [
                    '__params' => $__params,
                    'customer' => $customer
                ]
            ]);

            $response = json_decode($api_request->getBody()->getContents(), true);

            return response()->json($response);
        } catch (\Exception $ex) {
            return response()->json([
                'title' => '<i class="fa fa-ban"></i> Error',
                'text' => $ex->getMessage()
            ]);
        }
    }

    //    public function editar_emergencia(Request $request)
    //    {
    //        $__params = $request->__get('params');
    //        $codref = $request->__get('codref');
    //        $data = [
    //            '__params' => $__params,
    //            'codref' => $codref
    //        ];
    //
    //        $params = http_build_query($data);
    //
    //        $client = new \GuzzleHttp\Client();
    //        $baseUrlExtra = config('services.aurora_extranet.domain');
    //        $request = $client->get($baseUrlExtra.'/api/customers/api.php?method=editar_emergencia&'. $params);
    //        $response = json_decode($request->getBody()->getContents(), true);
    //
    //        return response()->json($response);
    //    }

    public function editar_emergencia(Request $request)
    {
        try {
            $params = $request->__get('params');
            $codref = $request->__get('codref');

            $client = new \GuzzleHttp\Client();

            $url = $this->files_onedb_ms . 'customers/emergency/edit';

            $api_request = $client->post($url, [
                'json' => [
                    'params' => $params,
                    'codref' => $codref
                ]
            ]);

            $response = json_decode($api_request->getBody()->getContents(), true);

            return response()->json($response);
        } catch (\Exception $ex) {
            return response()->json([
                'title' => '<i class="fa fa-ban"></i> Error',
                'text' => $ex->getMessage()
            ]);
        }
    }

    //    public function eliminar_emergencia(Request $request)
    //    {
    //        $codref = $request->__get('codref');
    //        $data = [
    //            'codref' => $codref
    //        ];
    //
    //        $params = http_build_query($data);
    //
    //        $client = new \GuzzleHttp\Client();
    //        $baseUrlExtra = config('services.aurora_extranet.domain');
    //        $request = $client->get($baseUrlExtra.'/api/customers/api.php?method=eliminar_emergencia&'. $params);
    //        $response = json_decode($request->getBody()->getContents(), true);
    //
    //        return response()->json($response);
    //    }

    public function eliminar_emergencia(Request $request)
    {
        try {
            $codref = $request->__get('codref');

            $client = new \GuzzleHttp\Client();

            $url = $this->files_onedb_ms . 'customers/emergency';

            $api_request = $client->delete($url, [
                'json' => [
                    'codref' => $codref
                ]
            ]);

            $response = json_decode($api_request->getBody()->getContents(), true);

            return response()->json($response);
        } catch (\Exception $ex) {
            return response()->json([
                'title' => '<i class="fa fa-ban"></i> Error',
                'text' => $ex->getMessage()
            ]);
        }
    }

    //    public function guias(Request $request)
    //    {
    //        $customer = $request->__get('customer');
    //        $data = [
    //            'customer' => $customer
    //        ];
    //
    //        $params = http_build_query($data);
    //
    //        $client = new \GuzzleHttp\Client();
    //        $baseUrlExtra = config('services.aurora_extranet.domain');
    //        $request = $client->get($baseUrlExtra.'/api/customers/api.php?method=guias&'. $params);
    //        $response = json_decode($request->getBody()->getContents(), true);
    //
    //        return response()->json($response);
    //    }

    public function guias(Request $request)
    {
        try {
            $customer = $request->__get('customer');
            $client = new \GuzzleHttp\Client();
            $url = $this->files_onedb_ms . 'customers/guides?customer=' . $customer;
            $api_request = $client->get($url);
            $response = json_decode($api_request->getBody()->getContents(), true);

            return response()->json($response);
        } catch (\Exception $ex) {
            return response()->json([
                'title' => '<i class="fa fa-ban"></i> Error',
                'text' => $ex->getMessage()
            ]);
        }
    }

    //    public function hoteles(Request $request)
    //    {
    //        $customer = $request->__get('customer');
    //        $data = [
    //            'customer' => $customer
    //        ];
    //
    //        $params = http_build_query($data);
    //
    //        $client = new \GuzzleHttp\Client();
    //        $baseUrlExtra = config('services.aurora_extranet.domain');
    //        $request = $client->get($baseUrlExtra.'/api/customers/api.php?method=hoteles&'. $params);
    //        $response = json_decode($request->getBody()->getContents(), true);
    //
    //        return response()->json($response);
    //    }

    public function hoteles(Request $request)
    {
        try {
            $customer = $request->__get('customer');

            $client = new \GuzzleHttp\Client();

            // Apuntamos a la nueva ruta en Node.js
            $url = $this->files_onedb_ms . 'customers/hotels?customer=' . $customer;

            $api_request = $client->get($url);
            $response = json_decode($api_request->getBody()->getContents(), true);

            return response()->json($response);
        } catch (\Exception $ex) {
            return response()->json([
                'title' => '<i class="fa fa-ban"></i> Error',
                'text' => $ex->getMessage()
            ]);
        }
    }

    //    public function tours_opcionales(Request $request)
    //    {
    //        $customer = $request->__get('customer');
    //        $data = [
    //            'customer' => $customer
    //        ];
    //
    //        $params = http_build_query($data);
    //
    //        $client = new \GuzzleHttp\Client();
    //        $baseUrlExtra = config('services.aurora_extranet.domain');
    //        $request = $client->get($baseUrlExtra.'/api/customers/api.php?method=tours_opcionales&'. $params);
    //        $response = json_decode($request->getBody()->getContents(), true);
    //
    //        return response()->json($response);
    //    }

    public function tours_opcionales(Request $request)
    {
        try {
            $customer = $request->__get('customer');

            $client = new \GuzzleHttp\Client();

            // Apuntamos a la nueva ruta en Node.js
            $url = $this->files_onedb_ms . 'customers/optional-tours?customer=' . $customer;

            $api_request = $client->get($url);
            $response = json_decode($api_request->getBody()->getContents(), true);

            return response()->json($response);
        } catch (\Exception $ex) {
            return response()->json([
                'title' => '<i class="fa fa-ban"></i> Error',
                'text' => $ex->getMessage()
            ]);
        }
    }

    //    public function filter_destinos(Request $request)
    //    {
    //        $term = $request->__get('term');
    //        $data = [
    //            'term' => $term
    //        ];
    //
    //        $params = http_build_query($data);
    //
    //        $client = new \GuzzleHttp\Client();
    //        $baseUrlExtra = config('services.aurora_extranet.domain');
    //        $request = $client->get($baseUrlExtra.'/api/customers/api.php?method=search_destinos&'. $params);
    //        $response = json_decode($request->getBody()->getContents(), true);
    //
    //        return response()->json($response);
    //    }
    public function filter_destinos(Request $request)
    {
        try {
            $term = $request->__get('term');

            $client = new \GuzzleHttp\Client();
            $url = $this->files_onedb_ms . 'customers/destinies';
            $api_request = $client->get($url, [
                'query' => [
                    'term' => $term
                ]
            ]);

            $api_response = json_decode($api_request->getBody()->getContents(), true);

            $result = isset($api_response['data']) ? $api_response['data'] : [];

            return response()->json($result);
        } catch (\Exception $ex) {
            return response()->json([]);
        }
    }

    //    public function filter_guias(Request $request)
    //    {
    //        $destino = $request->__get('destino'); $term = $request->__get('term');
    //        $data = [
    //            'destino' => $destino,
    //            'term' => $term
    //        ];
    //
    //        $params = http_build_query($data);
    //
    //        $client = new \GuzzleHttp\Client();
    //        $baseUrlExtra = config('services.aurora_extranet.domain');
    //        $request = $client->get($baseUrlExtra.'/api/customers/api.php?method=search_guias&'. $params);
    //        $response = json_decode($request->getBody()->getContents(), true);
    //
    //        return response()->json($response);
    //    }

    public function filter_guias(Request $request)
    {
        try {
            $destino = $request->__get('destino');
            $term = $request->__get('term');

            $client = new \GuzzleHttp\Client();

            $url = $this->files_onedb_ms . 'customers/guides/search';

            $api_request = $client->get($url, [
                'query' => [
                    'destino' => $destino,
                    'term' => $term
                ]
            ]);

            $response = json_decode($api_request->getBody()->getContents(), true);

            return response()->json($response);
        } catch (\Exception $ex) {
            return response()->json([]);
        }
    }

    //    public function agregar_guia(Request $request)
    //    {
    //        $customer = $request->__get('customer');
    //        $identi = $request->__get('identi');
    //        $destino = $request->__get('destino');
    //        $guia = $request->__get('guia');
    //
    //        $data = [
    //            'customer' => $customer,
    //            'identi' => $identi,
    //            'destino' => $destino,
    //            'guia' => $guia
    //        ];
    //
    //        $params = http_build_query($data);
    //
    //        $client = new \GuzzleHttp\Client();
    //        $baseUrlExtra = config('services.aurora_extranet.domain');
    //        $request = $client->get($baseUrlExtra.'/api/customers/api.php?method=agregar_guia&'. $params);
    //        $response = json_decode($request->getBody()->getContents(), true);
    //
    //        return response()->json($response);
    //    }

    public function agregar_guia(Request $request)
    {
        try {
            $customer = $request->__get('customer');
            $identi = $request->__get('identi');
            $destino = $request->__get('destino');
            $guia = $request->__get('guia');

            $client = new \GuzzleHttp\Client();

            $url = $this->files_onedb_ms . 'customers/guides';

            $api_request = $client->post($url, [
                'json' => [
                    'customer' => $customer,
                    'identi' => $identi,
                    'destino' => $destino,
                    'guia' => $guia
                ]
            ]);

            $response = json_decode($api_request->getBody()->getContents(), true);

            return response()->json($response);
        } catch (\Exception $ex) {
            return response()->json([
                'title' => '<i class="fa fa-ban"></i> Error',
                'text' => $ex->getMessage()
            ]);
        }
    }

    //    public function eliminar_guia(Request $request)
    //    {
    //        $customer = $request->__get('customer');
    //        $identi = $request->__get('identi');
    //        $destino = $request->__get('destino');
    //        $guia = $request->__get('guia');
    //
    //        $data = [
    //            'customer' => $customer,
    //            'identi' => $identi,
    //            'destino' => $destino,
    //            'guia' => $guia
    //        ];
    //
    //        $params = http_build_query($data);
    //
    //        $client = new \GuzzleHttp\Client();
    //        $baseUrlExtra = config('services.aurora_extranet.domain');
    //        $request = $client->get($baseUrlExtra.'/api/customers/api.php?method=eliminar_guia&'. $params);
    //        $response = json_decode($request->getBody()->getContents(), true);
    //
    //        return response()->json($response);
    //    }

    public function eliminar_guia(Request $request)
    {
        try {
            $customer = $request->__get('customer');
            $identi = $request->__get('identi');
            $destino = $request->__get('destino');
            $guia = $request->__get('guia');

            $client = new \GuzzleHttp\Client();
            $url = $this->files_onedb_ms . 'customers/guides/delete';

            $api_request = $client->delete($url, [
                'json' => [
                    'customer' => $customer,
                    'identi' => $identi,
                    'destino' => $destino,
                    'guia' => $guia
                ]
            ]);

            $response = json_decode($api_request->getBody()->getContents(), true);
            return response()->json($response);
        } catch (\Exception $ex) {
            return response()->json(['error' => $ex->getMessage()]);
        }
    }

    //    public function filter_hoteles(Request $request)
    //    {
    //        $destino = $request->__get('destino');
    //        $term = $request->__get('term');
    //        $data = [
    //            'destino' => $destino,
    //            'term' => $term
    //        ];
    //
    //        $params = http_build_query($data);
    //
    //        $client = new \GuzzleHttp\Client();
    //        $baseUrlExtra = config('services.aurora_extranet.domain');
    //        $request = $client->get($baseUrlExtra.'/api/customers/api.php?method=search_hoteles&'. $params);
    //        $response = json_decode($request->getBody()->getContents(), true);
    //
    //        return response()->json($response);
    //    }

    public function filter_hoteles(Request $request)
    {
        try {
            $destino = $request->__get('destino');
            $term = $request->__get('term');

            $client = new \GuzzleHttp\Client();
            $url = $this->files_onedb_ms . 'customers/hotels/search';

            $api_request = $client->get($url, [
                'query' => [
                    'destino' => $destino,
                    'term' => $term
                ]
            ]);

            $response = json_decode($api_request->getBody()->getContents(), true);
            return response()->json($response);
        } catch (\Exception $ex) {
            return response()->json([]);
        }
    }

    //    public function agregar_hotel(Request $request)
    //    {
    //        $customer = $request->__get('customer');
    //        $identi = $request->__get('identi');
    //        $destino = $request->__get('destino');
    //        $hotel = $request->__get('hotel');
    //        $data = [
    //            'customer' => $customer,
    //            'identi' => $identi,
    //            'destino' => $destino,
    //            'hotel' => $hotel
    //        ];
    //
    //        $params = http_build_query($data);
    //
    //        $client = new \GuzzleHttp\Client();
    //        $baseUrlExtra = config('services.aurora_extranet.domain');
    //        $request = $client->get($baseUrlExtra.'/api/customers/api.php?method=agregar_hotel&'. $params);
    //        $response = json_decode($request->getBody()->getContents(), true);
    //
    //        return response()->json($response);
    //    }

    public function agregar_hotel(Request $request)
    {
        try {
            $customer = $request->__get('customer');
            $identi = $request->__get('identi');
            $destino = $request->__get('destino');
            $hotel = $request->__get('hotel');

            $client = new \GuzzleHttp\Client();
            $url = $this->files_onedb_ms . 'customers/hotels/add';

            $api_request = $client->post($url, [
                'json' => [
                    'customer' => $customer,
                    'identi' => $identi,
                    'destino' => $destino,
                    'hotel' => $hotel
                ]
            ]);

            $response = json_decode($api_request->getBody()->getContents(), true);
            return response()->json($response);
        } catch (\Exception $ex) {
            return response()->json(['error' => $ex->getMessage()]);
        }
    }

    //    public function eliminar_hotel(Request $request)
    //    {
    //        $customer = $request->__get('customer');
    //        $identi = $request->__get('identi');
    //        $destino = $request->__get('destino');
    //        $hotel = $request->__get('hotel');
    //        $data = [
    //            'customer' => $customer,
    //            'identi' => $identi,
    //            'destino' => $destino,
    //            'hotel' => $hotel
    //        ];
    //
    //        $params = http_build_query($data);
    //
    //        $client = new \GuzzleHttp\Client();
    //        $baseUrlExtra = config('services.aurora_extranet.domain');
    //        $request = $client->get($baseUrlExtra.'/api/customers/api.php?method=eliminar_hotel&'. $params);
    //        $response = json_decode($request->getBody()->getContents(), true);
    //
    //        return response()->json($response);
    //    }

    public function eliminar_hotel(Request $request)
    {
        try {
            $customer = $request->__get('customer');
            $identi = $request->__get('identi');
            $destino = $request->__get('destino');
            $hotel = $request->__get('hotel');

            $client = new \GuzzleHttp\Client();
            $url = $this->files_onedb_ms . 'customers/hotels/delete';

            $api_request = $client->delete($url, [
                'json' => [
                    'customer' => $customer,
                    'identi' => $identi,
                    'destino' => $destino,
                    'hotel' => $hotel
                ]
            ]);

            $response = json_decode($api_request->getBody()->getContents(), true);
            return response()->json($response);
        } catch (\Exception $ex) {
            return response()->json(['error' => $ex->getMessage()]);
        }
    }

    //    public function recordatorios(Request $request)
    //    {
    //        $customer = $request->__get('customer');
    //        $data = [
    //            'customer' => $customer
    //        ];
    //
    //        $params = http_build_query($data);
    //
    //        $client = new \GuzzleHttp\Client();
    //        $baseUrlExtra = config('services.aurora_extranet.domain');
    //        $request = $client->get($baseUrlExtra.'/api/customers/api.php?method=recordatorios&'. $params);
    //        $response = json_decode($request->getBody()->getContents(), true);
    //
    //        return response()->json($response);
    //
    //        // recordatorios..
    //        [
    //            id => integer,
    //            title => varchar (255), // FITS, GRUPOS
    //            category => varchar(255), // VUELOS, PAXS
    //            content => varchar (255), // ruta de carpeta para traer los contenidos (para el push, para el correo, etc)..
    //            status => smallint,
    //        ];
    //
    //        // tiempo recordatorios..
    //        [
    //            reminder_id => 'reminders.id',
    //            time => smallint, // (3, 5, 7, 15, 30, 45)
    //            format => 'days', // (days, week, month)
    //            type_notification => 'reminder_types.id',
    //            status => smallint
    //        ];
    //
    //        // tipos recordatorios..
    //        [
    //            id => integer,
    //            title => varchar(255), // correo,
    //        ];
    //    }
    public function recordatorios(Request $request)
    {
        try {
            $customer = $request->__get('customer');

            $client = new \GuzzleHttp\Client();
            $url = $this->files_onedb_ms . 'customers/reminders';

            $api_request = $client->get($url, [
                'query' => [
                    'customer' => $customer
                ]
            ]);

            $response = json_decode($api_request->getBody()->getContents(), true);
            return response()->json($response);
        } catch (\Exception $ex) {
            return response()->json(['error' => $ex->getMessage()]);
        }
    }
}
