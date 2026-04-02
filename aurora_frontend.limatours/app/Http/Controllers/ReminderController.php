<?php

namespace App\Http\Controllers;

use App\Country;
use App\Doctype;
use App\Language;
use Illuminate\Http\Request;

class ReminderController extends Controller
{
    protected $baseUrlExtra = '';
    protected $files_onedb_ms = '';

    public function __construct(Request $request)
    {
        $this->baseUrlExtra = config('app.mix_base_external_url');
        $this->files_onedb_ms = config('services.aurora_files.domain');
    }

    public function index(Request $request)
    {
        return view('reminders.index');
    }

    public function search_hotels(Request $request)
    {
        $_services = (array) $request->__get('services');

        $variations = [];
        $_ignore = [];
        $hotels = [];
        $type_room = [];
        $simple_general = 0;
        $doble_general = 0;
        $triple_general = 0;

        foreach ($_services as $key => $value) {
            if ($value['catser'] == 'HOT' and !in_array($key, $_ignore)) {
                $type_room[$value['codsvs']][$value['nroite']] = ($value['canpax'] / $value['cantid']);

                foreach ($_services as $k => $v) {
                    if (
                        $v['fecin'] == $value['fecin'] and
                        $v['fecout'] == $value['fecout'] and
                        $v['nroite'] != $value['nroite'] and
                        $v['catser'] == 'HOT'
                    ) {
                        $variations[] = $v;
                        $type_room[$v['codsvs']][$v['nroite']] = ($v['canpax'] / $v['cantid']);
                        $_ignore[] = $k;
                    }
                }

                $_services[$key]['variations'] = $variations;

                $simple = 0;
                $doble = 0;
                $triple = 0;

                foreach ($variations as $k => $v) {
                    $type = $v['canpax'] / $v['cantid'];

                    if ($type == 1) {
                        $simple += $v['cantid'];
                    }

                    if ($type == 2) {
                        $doble += $v['cantid'];
                    }

                    if ($type == 3) {
                        $triple += $v['cantid'];
                    }
                }

                $simple_general = ($simple_general > 0) ? $simple_general : $simple;
                $doble_general = ($doble_general > 0) ? $doble_general : $doble;
                $triple_general = ($triple_general > 0) ? $triple_general : $triple;

                $_services[$key]['check'] = ($simple == $simple_general and $doble == $doble_general and $triple == $triple_general) ? true : false;
                $_services[$key]['SGL'] = $simple;
                $_services[$key]['DBL'] = $doble;
                $_services[$key]['TPL'] = $triple;

                $hotels[] = $_services[$key];
            }
        }

        return response()->json(['hotels' => $hotels, 'services' => $_services, 'type_room' => $type_room]);
    }

    public function register_codcfm(Request $request)
    {
        $lang = $request->__get('lang');
        $nrofile = $request->__get('nrofile');
        $codsvs = $request->__get('codsvs');

        session()->put('lang', $lang);

        return view('notifications.register_codcfm')->with('lang', $lang)->with('nrofile', $nrofile)
            ->with('codsvs', $codsvs);
    }

    public function register_paxs(Request $request)
    {
        $lang = $request->__get('lang');
        $nrofile = $request->__get('nrofile');
        $paxs = $request->__get('paxs');
        $canadl = $request->__get('canadl');
        $canchd = $request->__get('canchd');
        $caninf = $request->__get('caninf');

        session()->put('lang', $lang);

        return view('notifications.register_paxs')->with('lang', $lang)->with('nrofile', $nrofile)
            ->with('paxs', $paxs)->with('canadl', $canadl)->with('canchd', $canchd)->with('caninf', $caninf);
    }

    public function register_flights(Request $request)
    {
        $lang = $request->__get('lang');
        $nrofile = $request->__get('nrofile');

        session()->put('lang', $lang);

        return view('notifications.register_flights')->with('lang', $lang)->with('nrofile', $nrofile);
    }

    public function register_file(Request $request)
    {
        $lang = $request->__get('lang');
        $nrofile = $request->__get('nrofile');
        $paxs = $request->__get('paxs');
        $canadl = $request->__get('canadl');
        $canchd = $request->__get('canchd');
        $caninf = $request->__get('caninf');

        session()->put('lang', $lang);

        return view('notifications.register_file')->with('lang', $lang)->with('nrofile', $nrofile)
            ->with('paxs', $paxs)->with('canadl', $canadl)->with('canchd', $canchd)->with('caninf', $caninf);
    }

    public function all_countries(Request $request)
    {
        $term = $request->__get('term');

        $data = [
            'term' => $term,
        ];

        $params = http_build_query($data);

        $client = new \GuzzleHttp\Client([
            'verify' => false,
        ]);
        $url = sprintf('%scountries/search', $this->files_onedb_ms);
        $request_api = $client->get($url, [
            'query' => $data,
        ]);
        $response_api = json_decode($request_api->getBody()->getContents(), true);
        $countries = $response_api['data'];

        /*
        $countries = Country::with([
            'translations' => function ($query) use ($lang) {
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->orderBy('iso')->get();
        */

        return response()->json(['countries' => $countries, 'quantity' => count($countries)]);
    }

    public function all_countries_iso(Request $request)
    {
        $lang = $request->input('lang');
        $search = $request->__get('search');
        $take = (int) $request->__get('take');

        if (empty($lang)) {
            $lang = session()->get('lang');
        }

        $language = Language::where('iso', $lang)->first();

        $countries = Country::with([
            'translations' => function ($query) use ($language) {
                $query->where('language_id', $language->id);
            }
        ])->orderBy('iso');

        if ($search != '') {
            $countries = $countries->where(function ($query) use ($search) {
                $query->orWhere('iso', 'like', '%' . $search . '%');
                $query->orWhere('iso_ifx', 'like', '%' . $search . '%');
                $query->orWhereHas('translations', function ($q) use ($search) {
                    $q->where('value', 'like', '%' . $search . '%');
                });
            });
        }

        if ($take > 0) {
            $countries = $countries->take($take);
        }

        $countries = $countries->get()->toArray();

        $countries = array_map(function ($country) {
            $country['label_country'] = $country['iso'] . '-' . $country['iso_ifx'] . '-' . $country['translations'][0]['value'];

            return $country;
        }, $countries);

        return response()->json(['countries' => $countries, 'quantity' => count($countries)]);
    }

    public function all_doctypes(Request $request)
    {
        $lang = session()->get('lang');
        if (empty($lang)) {
            $lang = $request->input('lang');
        }
        $language = Language::where('iso', $lang)->first();
        $doctypes = Doctype::with([
            'translations' => function ($query) use ($language) {
                $query->where('language_id', $language->id);
            }
        ])->get();

        return response()->json(['doctypes' => $doctypes, 'quantity' => count($doctypes)]);
    }

    public function save_passengers(Request $request)
    {
        $passengers = $request->__get('passengers');
        $repeat = (int)$request->__get('repeat');
        $modePassenger = (int)$request->__get('modePassenger');
        $paxs = [];

        foreach ($passengers as $key => $value) {
            $value = (object)$value;

            if ($repeat == 1 && $modePassenger == 1) {
                $value = (object)$passengers[0];
            }

            $paxs[$key] = $value;
        }

        session()->put('passengers_list', $paxs);

        $response['data'] = $paxs;
        $response['process'] = true;
        $response['success'] = true;
        $response['message'] = 'Lista de pasajeros guardada correctamente.';

        return response()->json($response);
    }

    public function search_passengers(Request $request)
    {
        $type = $request->__get('type');
        $file = $request->__get('file');
        $quote = $request->__get('quote');

        try
        {
            if($type == 'file' OR $type == 'quote' OR $type == 'package')
            {
                $data = [
                    'type' => $type,
                    'file' => $file,
                    'quote' => $quote,
                ];

                $requestURL = $this->baseUrlExtra . 'api/search_passengers';

                // dd($requestURL);

                $client = new \GuzzleHttp\Client([
                    "verify" => false,
                    'base_uri' => ''
                ]);

                $response = $client->request('POST', $requestURL, [
                    'form_params' => $data,
                ]);

                $response = json_decode($response->getBody()->getContents(), true);
            } else {
                $_response = (array)session()->get('passengers_list');
                $passengers = [];
                $repeat_passenger = 0;
                $_passengers = [];

                if ($_response != '' and is_array($_response)) {
                    foreach ($_response as $key => $value) {
                        $value = (array)$value;

                        if ($value['nombres'] != '' and $value['apellidos'] != '') {
                            if (!in_array($value['nombres'] . ' ' . $value['apellidos'], $_passengers)) {
                                $repeat_passenger = 0; // Porque de varios registros, dos pueden ser el mismo..
                                $_passengers[] = $value['nombres'] . ' ' . $value['apellidos'];
                            } else {
                                $repeat_passenger = 1;
                            }
                        }

                        foreach ($value as $k => $v) {
                            if ($v == '' or is_null($v)) {
                                $value[$k] = '';
                            }
                        }

                        $passengers[$key] = $value;
                    }
                }

                $response['type'] = 'success';
                $response['prohib'] = 0;
                $response['lock_list'] = 1;
                $response['passengers'] = $passengers;
                $response['repeat_passenger'] = $repeat_passenger;
            }

            return response()->json($response);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function load_passengers(Request $request)
    {
        $passengers = (array)$request->__get('passengers');
        $file = $request->__get('file');

        session()->put('nrofile', $file);
        session()->put('passengers', $passengers); // Guardando los datos de los pasajeros en sessión..

        return response()->json([
            'passengers' => $passengers,
            'file' => $file
        ]);
    }

    /*
    public function import_passenger(Request $request)
    {
        set_time_limit(0);

        try
        {
            $response = ['type' => 'error', 'content' => 'Error al importar los datos de los pasajeros. Por favor, intente nuevamente.'];

            Excel::import(new PassengersImport($request->__get('paxs')), $request->file('excel'));

            $response_import = (array) session()->get('data_import');

            if(count($response_import) > 0)
            {
                foreach($response_import as $key => $value)
                {
                    if($value['estado'] == 1)
                    {
                        $response = ['type' => 'success', 'content' => $value['mensaje']]; break;
                    }
                }
            }
        }
        catch(\SoapFault $ex)
        {
            $response['message'] = $ex;
        }

        return response()->json($response);
    }

    public function passenger(Request $request, $type)
    {
        try
        {
            $this->wsdl_passengers = 'http://genero.limatours.com.pe:8203/WS_RegistroPasajero?wsdl';
            $this->client_passengers = new \SoapClient($this->wsdl_passengers, [
                'encoding' => 'UTF-8',
                'trace' => true
            ]);

            $types = ['save' => 'OK', 'edit' => 'OK', 'delete' => 'OK'];
            $params = $request->__get('params'); $fill = array('nombres', 'apellidos', 'observ');

            foreach($fill as $key => $value)
            {
                if(@$params[$value] == '')
                {
                    $params[$value] = 'PENDIENTE';
                }
            }

            $array = [
                'secuencia' => $params['nrosec'],
                'nrofile' => $params['nroref'],
                'nombre' => $params['apellidos'] . ', ' . $params['nombres'],
                'tipo' => $params['tipo'],
                'sexo' => $params['sexo'],
                'fecha' => $params['fecnac'],
                'ciudad' => '',
                'pais' => $params['nacion'],
                'tipodoc' => $params['tipdoc'],
                'nrodoc' => $params['nrodoc'],
                'estado' => $types[$type],
                'correo_electronico' => $params['correo'],
                'telefono' => $params['celula'],
                'resmed' => $params['resmed'],
                'resali' => $params['resali'],
                'observ' => $params['observ']
            ];

            $array = implode("|", $array);

            $data = array('nroref' => $params['nroref'], 'datos' => ($array . '|'));
            $response = (array) $this->client_passengers->__call("registropasajero", $data);
            $response['data'] = $data;

            return response()->json($response);
        }
        catch(Exception $ex)
        {
            return response()->json($ex->getMessage());
        }
    }
    */

    public function search_flights(Request $request)
    {
        try {
            $nrofile = $request->__get('nrofile');

            $requestURL = $this->baseUrlExtra . 'api/search_flights/' . $nrofile;

            $client = new \GuzzleHttp\Client([
                "verify" => false,
                'base_uri' => ''
            ]);

            $response = $client->request('POST', $requestURL, [
                'form_params' => [],
            ]);

            $response = json_decode($response->getBody()->getContents(), true);
            return $response;
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }
}
