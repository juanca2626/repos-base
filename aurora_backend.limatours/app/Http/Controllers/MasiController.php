<?php

namespace App\Http\Controllers;

use App\Client;
use App\MasiConfiguration;
use App\Http\Stella\StellaService;
use App\MasiActivityJobLogs;
use App\Language;
use App\MasiFileConfig;
use App\MasiFileDetail;
use App\MasterService;
use App\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class MasiController extends Controller
{
    protected $stellaService;

    public function __construct(StellaService $stellaService)
    {
        $this->stellaService = $stellaService;
        parent::__construct();
    }

    public function search_logs(Request $request)
    {
        $response = [];

        try {

            $file = $request->__get('file');

            $array = [
                'identi' => $request->__get('identi'),
                'usuario' => $request->__get('usuario'),
                'skip' => $request->__get('skip'),
                'total' => '',
                'first' => ((!empty($file)) ? 1 : $request->__get('first')),
                'file' => $file,
            ];

            $_response = $this->toArray($this->stellaService->masi_search_logs($array));

            $logs = $_response['data']['logs'];
            $total = $_response['data']['total'];

            if (!empty($file)) {
                foreach ($logs as $key => $value) {
                    if (!(isset($response[$value['file']]))) {
                        $response[$value['file']] = $value;
                    }
                }
            } else {
                $response['data'] = $logs;
            }

            $pages = ceil($total / $request->__get('first'));
            $response['pages'] = $pages;
        } catch (\Exception $ex) {
            $response = $this->throwError($ex);
        } finally {
            return $response;
        }
    }

    public function report_all_logs(Request $request, $type = 3)
    {
        try {
            $from = '01/' . date("m/Y");
            $to = '';
            $status = 'OK';

            if ($request->__get('desde') != '' and $request->__get('desde') != null) {
                $from = date("d/m/Y", strtotime($request->__get('desde')));
            }

            if ($request->__get('hasta') != '' and $request->__get('hasta') != null) {
                $to = date("d/m/Y", strtotime($request->__get('hasta')));
            }

            if ($request->__get('status') != '' and $request->__get('status') != null) {
                $status = $request->__get('status');
            }

            $types = ['', '1 SEMANA ANTES', '24 HORAS ANTES', 'DIA A DIA', '', 'DESPEDIDA'];

            $array = [
                'type' => $type,
                'from' => $from,
                'to' => $to,
                'status' => $status,
            ];

            $data = $this->toArray($this->stellaService->masi_report_all_logs($array));

            return Excel::download(
                new \App\Exports\MasiLogsReport($data),
                'Reporte - ' . $types[$type] . '.xlsx'
            );
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function search_all_logs(Request $request)
    {
        $response = [];

        try {
            $array = [
                'file' => $request->__get('file'),
                'codcli' => $request->__get('codcli')
            ];

            $response = (array)$this->stellaService->masi_search_all_logs($array);
        } catch (\Exception $ex) {
            $response = $this->throwError($ex);
        } finally {
            return $response;
        }
    }

    public function search_paxs_by_file(Request $request)
    {
        $response = [
            'success' => true,
            'data' => [
                'passengers' => [],
                'dates' => []
            ]
        ];

        try {
            $array = [
                'file' => $request->__get('nrofile')
            ];

            $dates = collect();

            $response_file = (array)$this->stellaService->get_file_information($array);

            if (isset($response_file['success']) and count($response_file['data']) > 0) {
                $date_in = $response_file['data'][0]->llegada;
                $date_out = $response_file['data'][0]->salida;
                $dates->add([
                    'date' => $date_in
                ]);
                $dates->add([
                    'date' => $date_out
                ]);
            }


            $passengers_request = (array)$this->stellaService->masi_search_paxs_by_file($array);
            if (isset($passengers_request['success']) and count($passengers_request['data']) > 0) {
                $response['data']['passengers'] = $passengers_request['data'];
            }
            $dates_request = (array)$this->stellaService->masi_get_dates_services_by_file($array);
            if (isset($dates_request['success']) and count($dates_request['data']) > 0) {
                foreach ($dates_request['data'] as $date) {
                    $dates->add([
                        'date' => $date->date
                    ]);
                }
                $response['data']['dates'] = $dates->unique()->sortBy('date')->values();
            }
        } catch (\Exception $ex) {
            $response = [
                'success' => false,
                'error' => $this->throwError($ex->getMessage()),
                'data' => [
                    'file' => [],
                    'passengers' => [],
                    'dates' => []
                ]
            ];
        } finally {
            return $response;
        }
    }

    public function checkEmailsFile($emails, $isDev = false)
    {
        if ($isDev) {
            return true;
        }

        $check = false;
        if (count($emails) > 0) {
            foreach ($emails as $clave => $valor) {
                if (!empty($valor['email'])) {
                    $check = true;
                }
            }
        }

        return $check;
    }

    public function getLogo($clients_code, $client_code)
    {
        $client = Client::where('code', $client_code)->first(['id', 'code', 'logo']);
        if ($client) {
            $logoFile = 'masi.png';
            if (!empty($client->logo)) {
                $logo_explode = explode("/", $client->logo);
                $logoFile = end($logo_explode);
            }
        } else {
            $logoFile = $client_code;
        }

        $folder = 'logos';
        $logo = 'https://res.cloudinary.com/litodti/image/upload/c_scale,h_190,q_100/aurora/' . $folder . '/' . trim($logoFile);

        if (strpos($logoFile, 'masi') !== false) {
            $logo = 'https://lito.pe/landing/assets/images/logo-lito.jpg?_=1731022559';
        }

        $useLogo = true;
        $response = array(
            'logo' => $logo,
            'useLogo' => $useLogo,
            'logoFile' => $logoFile
        );

        return $response;
    }

    public function getLogoGlobal(Request $request, $client_code)
    {
        $client = Client::where('code', $client_code)->first(['id', 'code', 'logo']);
        if ($client) {
            $logoFile = 'masi.png';
            if (!empty($client->logo)) {
                $logo_explode = explode("/", $client->logo);
                $logoFile = end($logo_explode);
            }
        } else {
            $logoFile = $client_code;
        }

        $folder = 'logos';
        $logo = 'https://res.cloudinary.com/litodti/image/upload/c_scale,h_190,q_100/aurora/' . $folder . '/' . trim($logoFile);

        if (strpos($logoFile, 'masi') !== false) {
            $logo = 'https://lito.pe/landing/assets/images/logo-lito.jpg?_=1731022559';
        }

        $useLogo = true;
        $response = array(
            'logo' => $logo,
            'useLogo' => $useLogo,
            'logoFile' => $logoFile
        );

        return response()->json($response);
    }

    public function getInfoDestinos($ciudades, $tipo, $lang)
    {
        $ciudades = $this->super_unique($ciudades, 'ciuin');
        //        $ciudades = Mailing::uniqueCities($ciudadesServicios);
        switch ($tipo) {
            case 1: //Return: Lima - Cusco - Urubamba - Machu Picchu
                $destinos = '';
                foreach ($ciudades as $clave => $valor) {
                    if ($clave == 0) {
                        $destinos = ucwords(strtolower(trim($valor['ciuin'])));
                    } else {
                        $destinos .= ' - ' . ucwords(strtolower(trim($valor['ciuin'])));
                    }
                }
                return $destinos;
                break;
            case 2: // return LIM, AQP, PUN, CUS
                $codCiudades = array();
                $ciudadConcat = '';
                foreach ($ciudades as $valor) {
                    $codCiudades[] = $valor['codciu'];
                }

                if (count($codCiudades) > 0) {
                    $ciudadConcat = implode('-', $codCiudades);
                }
                return $ciudadConcat;
                break;
        }
    }

    public function array_elimina_duplicados($array, $campo)
    {
        $new = array();
        $exclude = array("");
        $array = (array)$array;
        for ($i = 0; $i <= count($array) - 1; $i++) {
            $array[$i] = (array)$array[$i];
            if (!in_array(trim($array[$i][$campo]), $exclude)) {
                $new[] = $array[$i];
                $exclude[] = trim($array[$i][$campo]);
            }
        }

        return $new;
    }

    public function getMapaStatic($ciudades)
    {
        try {
            $destinos = $this->array_elimina_duplicados($ciudades, 'ciuin');
            $apiKey = '&key=AIzaSyDetTg210Jsl-d7DwfTe5shwGhFTJj3iNg';
            $url = 'https://maps.googleapis.com/maps/api/staticmap?zoom=auto&size=600x270&maptype=roadmap&
            sensor=false&';
            $markers = '';
            $num = 1;
            foreach ($destinos as $key => $val) {
                if ($val['coorde'] != null or $val['coorde'] != '') {
                    $geo = explode(',', $val['coorde']);
                    $lat = $geo[0];
                    $log = $geo[1];
                } else {
                    $lat = '-12.0463731';
                    $log = '-77.042754';
                }
                if ($key === 0) {
                    $markers = '&markers=color:red|label:' . $num . '|' . trim($lat) . ',' . trim($log);
                } else {
                    $markers .= '&markers=color:red|label:' . $num . '|' . trim($lat) . ',' . trim($log);
                }
                $num++;
            }
            return $url . $markers . $apiKey;
        } catch (\Exception $e) {
            return print_r($e);
        }
    }

    public function getTranslation($text, $lang)
    {
        switch ($lang) {
            case 'en': // (EN)
                switch ($text) {
                    case 'Tu viaje a Perú: Información Importante':
                        $text = "Your trip to Peru: Important information";
                        break;
                    case 'Tu viaje a Perú: Revisa tu itinerario':
                        $text = "Your trip to Peru: Check your itinerary";
                        break;
                    case 'Tu viaje a Perú: Comparte tu experiencia':
                        $text = "Your trip to Peru: Share your experience";
                        break;
                }
                break;
            case 'pt': // (PT)
                switch ($text) {
                    case 'Tu viaje a Perú: Información Importante':
                        $text = "Sua viagem ao Peru: Informação Importante";
                        break;
                    case 'Tu viaje a Perú: Revisa tu itinerario':
                        $text = "Sua viagem ao Peru: verifique seu itinerário";
                        break;
                    case 'Tu viaje a Perú: Comparte tu experiencia':
                        $text = "Sua viagem ao Peru: compartilhe sua experiência";
                        break;
                }
                break;
            case 'it': // (IT)
                switch ($text) {
                    case 'Tu viaje a Perú: Información Importante':
                        $text = "Il tuo viaggio in Perù: informazioni importanti";
                        break;
                    case 'Tu viaje a Perú: Revisa tu itinerario':
                        $text = "Il tuo viaggio in Perù: controlla il tuo itinerario";
                        break;
                    case 'Tu viaje a Perú: Comparte tu experiencia':
                        $text = "Il tuo viaggio in Perù: condividi la tua esperienza";
                        break;
                }
                break;
            default: // (ESP)
                switch ($text) {
                    case 'Tu viaje a Perú: Información Importante':
                        $text = "Tu viaje a Perú: Información Importante";
                        break;
                    case 'Tu viaje a Perú: Revisa tu itinerario':
                        $text = "Tu viaje a Perú: Revisa tu itinerario";
                        break;
                    case 'Tu viaje a Perú: Comparte tu experiencia':
                        $text = "Tu viaje a Perú: Comparte tu experiencia";
                        break;
                }
                break;
        }

        return $text;
    }

    public function getFiles()
    {
        $files = MasiFileConfig::select('nroref')->get();
        return $files;
    }

    public function files_locked()
    {
        $files = MasiFileConfig::all();

        return response()->json([
            'success' => true,
            'count' => count($files),
            'data' => $files,
        ]);
    }

    public function save_file_locked(Request $request)
    {
        try {
            $file_locked = new MasiFileConfig;
            $file_locked->nroref = $request->__get('nroref');
            $file_locked->user_id = auth()->user()->id;
            $file_locked->save();

            return response()->json([
                'type' => 'success',
                'success' => true,
                'file' => $file_locked,
                'message' => 'File bloqueado correctamente',
            ]);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function delete_file_locked(Request $request, $file_id)
    {
        try {
            MasiFileConfig::where('id', '=', $file_id)->delete();

            return response()->json([
                'type' => 'success',
                'success' => true,
                'message' => 'File desbloqueado correctamente',
            ]);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function getItinerary($nroref, $lang, $logo, $pax)
    {
        try {
            $language = Language::where('iso', '=', strtolower($lang))->where('state', '=', 1)->first();
            $lang = ($language) ? $language->iso : 'en';

            $data = ((!empty($logo)) ? $logo : 'masi.png') . ',' . $pax . ',' . $nroref . ',' . $lang;
            $url = 'https://lito.pe/app/#/itinerary/' . base64_encode($data);
            return $url;
        } catch (\Exception $e) {
            return print_r($e);
        }
    }

    public function getPoll($nroref, $destinos, $lang, $logo, $pax)
    {
        try {
            $data = ((!empty($logo)) ? $logo : 'masi.png') . ',' . 'uEo1ZJ' . ',' . $destinos . ',' . $pax . ',' . $nroref . ',' . $lang;
            $url = 'https://lito.pe/app/#/poll/' . base64_encode($data);
            return $url;
        } catch (\Exception $e) {
            return print_r($e);
        }
    }

    public function checkPaxCertificationFile($nroref, $paxId)
    {
        $certification = false;

        $params = [
            'nroref' => $nroref,
            'pax' => $paxId
        ];
        $response = (array)$this->stellaService->getCertificationCarbonByFile($params);
        $getData = (array)$response['data'];

        if (count($getData) > 0) {
            $certification = true;
        }

        return $certification;
    }

    public function getServicesByPax($servicios, $pax)
    {
        $serviciosPax = array();
        $servicios = (array)$servicios;
        foreach ($servicios as $key => $val) {
            $val = (array)$val;
            $paxs = (array)json_decode(@$val['paxs_service'], true);
            if (count($paxs) > 0) {
                foreach ($paxs as $pax_id) {
                    if ((int)$pax_id['id'] === (int)$pax) {
                        array_push($serviciosPax, $val);
                        break;
                    }
                }
            } else {
                array_push($serviciosPax, $val);
            }
        }

        return $serviciosPax;
    }

    public function searchServicesByDate($services, $dateFind)
    {
        $servicesFind = array();
        $services = (array)$services;

        foreach ($services as $clave => $value) {
            if ($services[$clave]['fecin'] === $dateFind) {
                $servicesFind[] = $services[$clave];
            }
        }

        return $servicesFind;
    }

    public function getHotel($hoteles, $date, $paxId)
    {
        $hotel = '';
        $hoteles = (array)$hoteles;
        $hotelsDate = [];

        foreach ($hoteles as $clave => $valor) {
            if ($hoteles[$clave]['fecin'] === $date) {
                $hotelsDate[] = [
                    'hotel' => $hoteles[$clave]['nombre'],
                    'pax' => $hoteles[$clave]['pax'],
                ];
            }
        }

        foreach ($hotelsDate as $valor) {
            $passengers = explode(',', $valor['pax']);
            foreach ($passengers as $passengerId) {
                if ($passengerId == $paxId) {
                    $hotel = $valor['hotel'];
                    break;
                }
            }
        }

        if (empty($hotel)) {
            foreach ($hoteles as $clave => $valor) {
                if ($hoteles[$clave]['fecin'] === $date) {
                    $hotel = $hoteles[$clave]['nombre'];
                    break;
                }
            }
        }

        return $hotel;
    }

    public function getVuelo($vuelos, $date, $paxId)
    {
        $vuelo = array(
            'ciu_desde' => '',
            'ciu_hasta' => '',
            'fecin' => '',
            'horain' => '',
            'fecout' => '',
            'horaout' => '',
            'nrovuelo' => '',
            'companyair' => '',
            'nroite' => '',
            'codsvs' => '',
            'pax' => ''
        );

        $flightsDate = [];

        foreach ($vuelos as $clave => $valor) {
            $valor = (array)$valor;
            if ($valor['fecin'] === $date) {
                $flightsDate[] = $valor;
            }
        }

        foreach ($flightsDate as $clave => $valor) {
            $valor = (array)$valor;
            $passengers = explode(',', $valor['pax']);
            foreach ($passengers as $passengerId) {
                if ($passengerId == $paxId) {
                    $vuelo = $valor;
                    break;
                }
            }
        }
        return $vuelo;
    }

    public function getVueloMinMax($vuelos, $paxId)
    {
        $flightsDate = [];

        foreach ($vuelos as $valor) {
            $valor = (array)$valor;
            $passengers = explode(',', $valor['pax']);
            foreach ($passengers as $passengerId) {
                if ($passengerId == $paxId) {
                    $flightsDate[] = $valor['fecin'];
                }
            }
        }

        return [
            'min' => (count($flightsDate) > 1) ? $flightsDate[0] : '',
            'max' => (count($flightsDate) > 1) ? $flightsDate[count($flightsDate) - 1] : '',
        ];
    }

    public function orderMultiDimensionalArray($toOrderArray, $field, $inverse = false)
    {
        $position = array();
        $newRow = array();
        foreach ($toOrderArray as $key => $row) {
            $position[$key] = $row[$field];
            $newRow[$key] = $row;
        }
        if ($inverse) {
            arsort($position);
        } else {
            asort($position);
        }
        $returnArray = array();
        foreach ($position as $key => $pos) {
            $returnArray[] = $newRow[$key];
        }
        return $returnArray;
    }

    public function validateHorinServices($service, $lang)
    {
        \App::setLocale($lang);
        $codsvs = $service['codsvs'];

        $services = [
            'LIN432',
            'LIN476',
            'LIN615',
            'LIN617',
            'CUZ419',
            'CUZ581',
            'UR1452',
            'CUZ430',
            'CUZ446',
            'AQV411',
            'PUV528'
        ];

        if (in_array($codsvs, $services)) {
            $horin_fin = date("H:i", strtotime("+30 min", strtotime(trim($service['horin']))));

            $response = [
                'inicio' => trim($service['horin']) . ' - ' . $horin_fin . __('masi.recojo_min_prev') . ' ',
                'fin' => __('masi.recojo_min_next'),
            ];
        } else {
            $response = [
                'inicio' => trim($service['horin']) . ' - ',
                'fin' => '',
            ];
        }

        return $response;
    }

    public function getTextSkeletonGlobal(Request $request, $service_code, $lang)
    {
        $language = Language::where('id', '=', $lang)
            ->where('state', '=', 1)->first();

        $data = MasterService::with([
            'translations' => function ($query) use ($language) {
                $query->where('language_id', '=', $language->id);
                $query->whereIn('slug', ['skeleton']);
            }
        ])
            ->where('status', '=', 1)
            ->where('code', '=', $service_code)
            ->first(['master_services.id', 'master_services.code']);

        return response()->json($data);
    }

    public function putTextSkeleton($destinos, $lang)
    {
        $language = Language::where('id', '=', $lang)
            ->where('state', '=', 1)->first();
        $new_destinos = [];

        foreach ($destinos as $key => $valor) {
            $valor = (array)$valor;

            $data = MasterService::with([
                'translations' => function ($query) use ($language) {
                    $query->where('language_id', '=', $language->id);
                    $query->whereIn('slug', ['skeleton']);
                }
            ])
                ->where('status', '=', 1)
                ->where('code', '=', $valor['codsvs'])
                ->first(['master_services.id', 'master_services.code']);

            $valor['descrip'] = '';

            if ($data) {
                $data = $data->toArray();

                if (!empty(@$data['translations'][0]['value'])) {
                    $valor['descrip'] = $data['translations'][0]['value'];
                }
            }

            $new_destinos[] = $valor;
        }

        return $new_destinos;
    }

    public function getTextSkeleton($nroref, $servicios, $lang)
    {
        try {
            $skeletons = array();
            $i = 0;

            foreach ($servicios as $clave => $valor) {
                $valor = (array)$valor;

                $skeletons[$i]['codsvs'] = $valor['codsvs'];
                $skeletons[$i]['descrip'] = '';
                $horin = $valor['horin'];

                if (!empty(@$valor['horin_t91'])) {
                    $horin = $valor['horin_t91'];
                }

                if (!empty(@$valor['horin_t91b'])) {
                    $horin = $valor['horin_t91b'];
                }

                $skeletons[$i]['horin'] = $horin;

                $params = [
                    'nroref' => $nroref,
                    'docupr' => $valor['docupr'],
                    'nroite' => $valor['nroite'],
                    'codsvs' => $valor['codsvs'] . '0000',
                    'lang' => $lang
                ];

                $response = (array)$this->stellaService->getTextoSkeleton($params);

                if (isset($response['data']) and isset($response['success']) and count((array)$response['data']) > 0) {
                    $texto = (array)$response['data'];
                    if (isset($texto[0]->texto)) {
                        $skeletons[$i]['descrip'] = trim($texto[0]->texto);
                    }
                }

                if (empty($skeletons[$i]['descrip'])) {
                    $skeletons[$i]['descrip'] = $valor['descrip'];
                }

                $i++;
            }

            foreach ($skeletons as $s => $skeleton) {
                $horin = $this->validateHorinServices($skeleton, strtolower($lang));

                $skeletons[$s]['order'] = $skeleton['horin'];
                $skeletons[$s]['horin'] = $horin['inicio'];
                $skeletons[$s]['descrip'] = trim($skeleton['descrip']) . '. ' . $horin['fin'];
            }

            $skeletons = $this->orderMultiDimensionalArray($skeletons, 'order');
            return $skeletons;
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function sync_skeleton(Request $request)
    {
        try {
            $codes = str_replace(" ", "", $request->__get('aurora_codes'));
            $aurora_codes = explode(",", $codes);
            $lang = $request->__get('lang');
            $language = Language::where('iso', '=', strtolower($lang))
                ->where('state', '=', 1)->first();
            $response = [];
            $lang = ($language) ? $language->id : 2;

            $services = MasterService::with([
                'translations' => function ($query) use ($lang) {
                    $query->where('language_id', '=', $lang);
                    $query->whereIn('slug', ['skeleton']);
                }
            ])
                ->where('status', '=', 1)
                ->whereIn('code', $aurora_codes)
                ->get(['master_services.id', 'master_services.code'])
                ->toArray();

            /*
            $services = Service::with(['service_translations' => function ($query) use ($language) {
                $query->orWhere('language_id', '=', @$language->id);
                $query->orWhere('language_id', '=', 2);
            }])->whereHas('service_translations', function ($query) use ($language) {
                $query->orWhere('language_id', '=', @$language->id);
                $query->orWhere('language_id', '=', 2);
            })->whereIn('aurora_code', $aurora_codes)->get()->toArray();
            */

            foreach ($services as $key => $value) {
                if (!empty($value['translations'][0]['value'])) {
                    $response[$value['code']] = $value['translations'][0]['value'];
                }
            }

            return response()->json([
                'type' => 'success',
                'data' => $response,
            ]);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function verifyCloudinaryImg($var, $w, $h, $request)
    {
        //https://res-5.cloudinary.com/litomarketing/image/upload/c_thumb,h_80,w_70/v1432940982/peru/amazonas/Tacacho_con_Cecina_024325_300.jpg
        //Default: Parapente_bthb8r
        if ($var == '') {
            if ($request == 'nom') {
                $var = 'Parapente_bthb8r';
            } else { // link
                $var =
                    'https://res-5.cloudinary.com/litomarketing/image/upload/c_thumb,h_' .
                    $h . ',w_' . $w . '/Parapente_bthb8r';
            }
        } else {
            $explode = explode("cloudinary.com", $var);
            if (count($explode) > 1) {
                $img = explode("upload/", $var);
                $img = $img[1];

                $verifyThumb = explode("c_thumb", $img);
                if (count($verifyThumb) > 1) {
                    $img = explode("/", $img);
                    $img = $img[1];
                }

                if ($request == 'nom') {
                    $var = $img;
                } else { // link
                    $var =
                        'https://res-5.cloudinary.com/litomarketing/image/upload/c_thumb,h_' .
                        $h . ',w_' . $w . '/' . trim($img);
                }
            }
        }

        return trim(preg_replace('/\s\s+/', ' ', $var));
    }

    public function getImagesServices($servicios)
    {
        $servicios = $this->super_unique($servicios, 'ciuin');
        foreach ($servicios as $clave => $valor) {
            $valor = (array)$valor;
            $servicios[$clave]['img_resize'] = $this->verifyCloudinaryImg($valor['img'], 360, 230, '');
        }
        return $servicios;
    }

    public function groupedArray($array, $value)
    {
        $groupedArray = array();
        $paisArray = array();
        foreach ($array as $key => $valuesAry) {
            $pais = $valuesAry[$value];
            if (!in_array($pais, $paisArray)) {
                //si no existe, lo agrego
                $paisArray[] = $pais;
            }
            $paisIndex = array_search($pais, $paisArray);
            $groupedArray[$paisIndex][] = $valuesAry;
        }
        return $groupedArray;
    }

    public function date_text($data, $tipo = 1, $lang)
    {
        \App::setLocale($lang);
        $count = substr_count($data, "-");
        $data = ($count == 0) ? $data : date("d/m/Y", strtotime($data));
        $dateString = '';

        if ($data != '') {
            for ($i = 0; $i <= 53; $i++) {
                $setmana[] = (object)[
                    'name' => __('masi.semana') . ' ' . $i
                ];
            }
            $mes = [
                (object)['name' => __('masi.enero')],
                (object)['name' => __('masi.febrero')],
                (object)['name' => __('masi.marzo')],
                (object)['name' => __('masi.abril')],
                (object)['name' => __('masi.mayo')],
                (object)['name' => __('masi.junio')],
                (object)['name' => __('masi.julio')],
                (object)['name' => __('masi.agosto')],
                (object)['name' => __('masi.setiembre')],
                (object)['name' => __('masi.octubre')],
                (object)['name' => __('masi.noviembre')],
                (object)['name' => __('masi.diciembre')],
            ];
            // \ereg('([0-9]{1,2})/([0-9]{1,2})/([0-9]{2,4})', $data, $data);
            $data = explode("/", $data);
            $data = mktime(0, 0, 0, $data[1], $data[0], $data[2]);
            if ($tipo == 1) {
                if (strtolower($lang) == 'es' or strtolower($lang) == 'pt') {
                    $dateString = $setmana[date('w', $data)]->name . ' ' . date(
                        'd',
                        $data
                    ) . ' ' . __('masi.de') . ' ' . $mes[date(
                        'm',
                        $data
                    ) - 1]->name . __('masi.del') . ' ' . date('Y', $data);
                } else {
                    $dateString = $setmana[date('w', $data)]->name . ' ' . $mes[date(
                        'm',
                        $data
                    ) - 1]->name . ' ' . date(
                        'd',
                        $data
                    ) . ',' . ' ' . date('Y', $data);
                }
            } elseif ($tipo == 2) {
                $dateString = date('d', $data) . ' ' . ucwords(substr(
                    $mes[date('m', $data) - 1]->name,
                    0,
                    3
                )) . ' ' . date(
                    'Y',
                    $data
                );
            } elseif ($tipo == 3) {
                $dateString = ucwords(substr($setmana[date('w', $data)]->name, 0, 3)) . ' ' . date(
                    'd',
                    $data
                ) . ' ' . ucwords(substr($mes[date('m', $data) - 1]->name, 0, 3));
            } elseif ($tipo == 4) {
                $dateString = ucwords($mes[date('m', $data) - 1]->name);
            } elseif ($tipo == 5) {
                if (strtolower($lang) == 'es' or strtolower($lang) == 'pt') {
                    $dateString = date('d', $data) . ' ' . __('masi.de') . ' ' . $mes[date('m', $data) - 1]->name;
                } elseif (strtolower($lang) == 'en') {
                    $dateString = $mes[date('m', $data) - 1]->name . ' ' . date('d', $data);
                } elseif (strtolower($lang) == 'it' or strtolower($lang) == 'fr') {
                    $dateString = date('d', $data) . ' ' . $mes[date('m', $data) - 1]->name;
                }
            }
        }

        return $dateString;
    }

    public function getNumeroDia($servicios, $dateSearch, $lang)
    {
        $serviciosGroup = $this->groupedArray($servicios, 'fecin');
        $ultimo = (count($serviciosGroup) > 0) ? (count($serviciosGroup) - 1) : count($serviciosGroup);
        $dia = '';
        foreach ($serviciosGroup as $clave => $valor) {
            foreach ($valor as $clave2 => $valor2) {
                if ($valor2['fecin'] === $dateSearch) {
                    if ($clave === 0) {
                        $dia = ($clave + 1);
                        $primerDia = true;
                        $ultimoDia = false;
                        $ciudad = $valor2['ciuin'];
                    } elseif ($clave === $ultimo) {
                        $dia = ($clave + 1);
                        $primerDia = false;
                        $ultimoDia = true;
                        $ciudad = '';
                    } else {
                        $dia = ($clave + 1);
                        $primerDia = false;
                        $ultimoDia = false;
                        $ciudad = '';
                    }
                    break;
                }
            }
        }

        if ($dia != '') {
            $diaArreglo = array(
                "dia" => $dia,
                "diaLetras" => $this->date_text($dateSearch, 5, $lang),
                "ciuin" => $ciudad,
                "primerDia" => $primerDia,
                "ultimoDia" => $ultimoDia
            );
        } else {
            $diaArreglo = array(
                "dia" => 0,
                "diaLetras" => "",
                "ciuin" => "",
                "primerDia" => "",
                "ultimoDia" => ""
            );
        }

        return $diaArreglo;
    }

    public function getWeather($lat, $lon, $type = 'today', $index = 0)
    {
        try {
            if ($lat == null or $lat == '' or $lon == null or $lon == '') {
                $lat = '-13.52'; //Geo Lima
                $lon = '-71.98';
            }

            $date = (($type == 'today') ? date("Y-m-d") : date("Y-m-d", strtotime("+1 days")));
            $query = ($lat . ',' . $lon);

            $params = [
                'q' => $query,
                'lang' => 'en',
                'dt' => $date,
            ];

            $params = http_build_query($params);

            $endpoint = sprintf('%s?%s', 'https://weatherapi-com.p.rapidapi.com/forecast.json', $params);

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $endpoint,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => [
                    "X-RapidAPI-Host: weatherapi-com.p.rapidapi.com",
                    "X-RapidAPI-Key: b43f058d04msh2a35aec5f4af3bap1548c4jsn345d3398f8f2"
                ],
            ]);

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                $icon = '';
                $clima_min = 0;
                $clima_max = 0;
            } else {
                $response = $this->toArray(json_decode($response));

                $clima_min = $response['forecast']['forecastday'][0]['day']['mintemp_c'];
                $clima_max = $response['forecast']['forecastday'][0]['day']['maxtemp_c'];
                $icon = $response['forecast']['forecastday'][0]['day']['condition']['icon']; // Icono
            }

            /*
            $apiKey = '7dd6975fe6e1fae365ae693df2e771ac';
            if ($type === 'today') {
                $params = [
                    'lat' => $lat,
                    'lon' => $lon,
                    'appid' => $apiKey
                ];

                $params = http_build_query($params);
                $ch = curl_init(sprintf('%s?%s', 'http://api.openweathermap.org/data/2.5/weather', $params));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($ch);
                curl_close($ch);

                // $request = Requests::get($apiUrl);
                $decode = (array)(json_decode($response));
                $clima_min = ((float)$decode['main']->temp_min - 273.15) * 1; // Kelvin a Celcius
                $clima_max = ((float)$decode['main']->temp_max - 273.15) * 1; // Kelvin a Celcius
                $icon = 'http://openweathermap.org/img/w/'.$decode['weather'][0]->icon.'.png'; // Icono
            } elseif ($type === 'tomorrow') {
                $apiUrl = 'http://api.openweathermap.org/data/2.5/forecast?lat='.$lat.'&lon='.$lon.
                    '&cnt=30&appid='.$apiKey;
                // $request = Requests::get($apiUrl);
                $params = [
                    'lat' => $lat,
                    'lon' => $lon,
                    'appid' => $apiKey
                ];

                $params = http_build_query($params);
                $ch = curl_init(sprintf('%s?%s', 'http://api.openweathermap.org/data/2.5/weather', $params));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($ch);
                curl_close($ch);

                $decode = (array)json_decode($response);
                //var_dump($decode['list'][$index]['main']['temp_min']);die;
                $clima_min = ((float)$decode['list'][$index]['main']->temp_min - 273.15) * 1; // Kelvin a Celcius
                $clima_max = ((float)$decode['list'][$index]['main']->temp_max - 273.15) * 1; // Kelvin a Celcius
                $icon = 'http://openweathermap.org/img/w/'.$decode['list'][$index]['weather']->icon.'.png'; // Icono
            } else {
                $clima_min = 0;
                $clima_max = 0;
            }
            */

            $clima = array(
                'clima_min' => round($clima_min, 0, PHP_ROUND_HALF_UP),
                'clima_max' => round($clima_max, 0, PHP_ROUND_HALF_UP),
                'icon' => $icon
            );

            return $clima;
        } catch (\Exception $e) {
            $clima = array(
                "clima_min" => 0,
                "clima_max" => 0,
                "icon" => '',
            );
            return $clima;
        }
    }

    public function getWeatherFuture2($ciudades, $date)
    {
        try {
            $weather = array();
            $destinos = (array)$this->array_elimina_duplicados($ciudades, 'ciuin');
            $count = 0;
            foreach ($destinos as $clave => $valor) {
                $geo = explode(',', $destinos[$clave]['coorde']);
                $lat = $geo[0];
                $log = $geo[1];
                $weather[$count]['fecha'] = date("d/m/Y", strtotime($destinos[$clave]['fecin']));
                $weather[$count]['clima'] = $this->getWeather($lat, $log, 'today');
                $weather[$count]['ciudad'] = ucwords(strtolower($destinos[$clave]['ciuin']));
                $count++;
            }
            return $weather;
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function mailing(Request $request)
    {
        try {
            $lang_ = \App::getLocale();
            $format = $request->__get('format');
            $form = (array)$request->__get('form');
            $senders = (array)$request->__get('senders');

            $types = ['', 'weekly', 'day_before', 'daily', '', 'survey'];
            $value = $types[$form['type']];

            if ($format == 1) {
                $field = 'test_mailing_' . $value;
            } else {
                $field = 'wsp_mailing_' . $value;
            }

            $response = $this->$field($form['type'], $value, $senders, $form, true, $request);
            \App::setLocale($lang_);

            return $response;
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function test_mailing_weekly($_key, $type, $email = [], $form = [], $isDev = false, Request $request)
    {
        $clients = $this->getClients($type);
        $clients_query = $clients->implode('code', "','");

        $date_now = date('d-m-Y');
        $date_future = strtotime('+7 day', strtotime($date_now));
        $date_future = date('d-m-Y', $date_future);
        $date = str_replace('-', '/', $date_future);

        $files = $this->getFiles();
        $files_query = $files->implode('nroref', "','");

        $file = '';

        if (!empty($form)) {
            $file = $form['file'];
            $date = $form['date'];

            foreach ($email as $key => $value) {
                $item = $form['passenger'];
                $item['email'] = $value;

                $email[$key] = $item;
            }
        }

        $params = [
            'type' => $_key,
            'date' => $date,
            'file' => $file,
            'files_query' => $files_query,
            'clients_query' => $clients_query,
            'isDev' => $isDev
        ];

        $response = (array)$this->stellaService->masi_mailing_job($params);
        $files = (array)$response['data'];

        if (count($files) > 0) {
            $_date = explode("/", $date);
            $date = $_date[2] . '-' . $_date[1] . '-' . $_date[0];

            foreach ($files as $clave => $valor) {
                $valor = (array)$valor;

                //Verifica si tiene permiso para poder enviar mailing
                $emails = (array)(json_decode($valor['emails_pasajeros'], true));
                $checkEmails = $this->checkEmailsFile($emails, $isDev);
                if ($checkEmails) {
                    $lang = strtolower(trim($valor['idioma']));
                    //Todo Si el dioma esta vacio se le asigna por default el idioma ingles
                    if (trim($lang) == '' or trim($lang) == null) {
                        $lang = 'en';
                    }

                    if ($lang == 'es') {
                        $linkIcons = "https://drive.google.com/file/d/1JJq-rsODtGJEj28drHDfSvcCjC2NYCVG/view";
                    } else {
                        if ($lang == 'it') {
                            $linkIcons = "https://drive.google.com/file/d/16gQWjjIFtSp5Tp90GyhXW9Vjgb3-y5db/view";
                        } else {
                            if ($lang == "pt") {
                                $linkIcons = "https://drive.google.com/file/d/16gQWjjIFtSp5Tp90GyhXW9Vjgb3-y5db/view";
                            } else {
                                if ($lang == "fr") {
                                    $linkIcons = "https://drive.google.com/file/d/1shlc811sHWmVdOCYlOOpqoh1DMEGfEnh/view";
                                } else {
                                    $linkIcons = "https://drive.google.com/file/d/1HJODPl9rFJ4Dbbb5oHV3cOlFnWgCaa5M/view";
                                }
                            }
                        }
                    }

                    $langs = ['', 'es', 'en', 'pt'];
                    $key_lang = array_search($lang, $langs);
                    $key_lang = ($key_lang > 0) ? $key_lang : 2;
                    $lang = $langs[$key_lang];

                    $params = [
                        'nroref' => $valor['nroref'],
                        'lang' => $key_lang,
                    ];

                    $response = (array)$this->stellaService->masi_services_file($params);
                    $destinos = (array)$response['data'];
                    $destinos = $this->putTextSkeleton($destinos, $key_lang);

                    if (count($destinos) > 0) {

                        $params = [
                            'nroref' => $valor['nroref'],
                            'cliente' => $valor['cliente'],
                            'fecha' => date("d/m/Y"),
                            'hora' => date("H:i"),
                            'tiplog' => 'E'
                        ];
                        $this->stellaService->masi_store($params);

                        $color = (empty($valor['color'])) ? "#D68F02" : $valor['color'];
                        // Verifico si tiene logo y si tiene la opcion de poder enviar emails con su logo
                        $getLogo = $this->getLogo($clients, trim($valor['cliente']));
                        //Obtengo las ciudades
                        $ciudades = $destinos;
                        $destinos = $this->getInfoDestinos($ciudades, 1, $lang);
                        $mapa = $this->getMapaStatic($ciudades);
                        $dia = 7;
                        $file = array(
                            'idioma' => $lang,
                            'color' => $color,
                            'logo' => $getLogo['logo'],
                            'useLogo' => $getLogo['useLogo'],
                            'mapa' => $mapa,
                            'linkIcons' => $linkIcons,
                            'file' => array(
                                'reserva' => array(
                                    'dias' => ((int)$valor['noches'] + 1),
                                    'noches' => ((int)$valor['noches'])
                                ),
                                'destinos' => $destinos,
                                'llegada' => date("d/m/Y", strtotime($valor['llegada'])),
                                'salida' => date("d/m/Y", strtotime($valor['salida'])),
                            )
                        );
                        $subject = $this->getTranslation(
                            'Tu viaje a Perú: Información Importante',
                            $lang
                        ) . ' - ' . $valor['nroref'];

                        //Envio de Mail a los Pasajeros
                        $inserts = array();
                        if ($isDev) {
                            $emails = $email;
                        }

                        $ciudadesPool = $this->getInfoDestinos($ciudades, 2, $lang);
                        foreach ($emails as $k => $val) {
                            $idPax = $val['nrosec'];
                            $vuelos = (array)json_decode($valor['vuelos'], true);
                            //--------
                            $vuelos = $this->getVueloMinMax($vuelos, $idPax);

                            if (!empty($vuelos['min']) && !empty($vuelos['max'])) {
                                $__llegada = Carbon::parse($vuelos['min']);
                                $__salida = Carbon::parse($vuelos['max']);

                                $nights = $__llegada->diffInDays($__salida);

                                $file['file']['reserva'] = array(
                                    'dias' => $nights + 1,
                                    'noches' => $nights,
                                );
                                $file['file']['llegada'] = date("d/m/Y", strtotime($__llegada));
                                $file['file']['salida'] = date("d/m/Y", strtotime($__salida));
                            }

                            $nameExl = explode(',', $val['nombre']);
                            $paxName = (count($nameExl) > 1) ? ucwords(strtolower($nameExl[1])) : ucwords(strtolower($nameExl[0]));
                            $docItinerario = $this->getItinerary(
                                $valor['nroref'],
                                $lang,
                                $getLogo['logoFile'],
                                $val['nrosec']
                            );
                            $encuesta = $this->getPoll(
                                $valor['nroref'],
                                $ciudadesPool,
                                $lang,
                                $getLogo['logoFile'],
                                $val['nrosec']
                            );
                            $certificate = $this->checkPaxCertificationFile($valor['nroref'], $val['nrosec']);
                            $dataEmail = array(
                                'file' => $valor['nroref'],
                                'cliente' => ucwords(strtolower($valor['razon'])),
                                'paxId' => $val['nrosec'],
                                'paxName' => $paxName,
                                'docItinerario' => $docItinerario,
                                'certification_carbon' => $certificate,
                                'poll' => $encuesta,
                                'data' => $file
                            );

                            try {
                                $html = (new \App\Mail\MailingMasiReminder(
                                    '7_dias_antes',
                                    $lang,
                                    $subject,
                                    $dataEmail
                                ))->render();
                                $send = $this->sendMail($html, $val['email'], $subject, ['MASI_MAILING']);
                                $status_mail = $send['code'];
                                $message_id = $send['message-id'];
                                $message_mail = $send['message'];
                                if ($send['code']) {
                                    $msg = 'Enviado correctamente';
                                    $status = true;
                                } else {
                                    return [
                                        'message' => $message_mail,
                                        'status' => false
                                    ];
                                }
                            } catch (\Exception $ex) {
                                $status_mail = 'error';
                                $message_mail = $ex->getMessage();
                                $message_id = '';
                                $msg = $ex->getMessage();
                                $status = false;
                            }

                            $inserts[] = array(
                                'email' => $val['email'],
                                'nombre' => $val['nombre'],
                                'status' => $status_mail,
                                'message' => $message_mail,
                                'dia' => $dia,
                                'message_id' => $message_id,
                            );
                        }

                        $params = [
                            'type' => $_key,
                            'fecha' => date("d/m/Y"),
                            'hora' => date("H:i"),
                            'nroref' => $valor['nroref'],
                            'cliente' => $valor['cliente'],
                            'inserts' => $inserts,
                            'category' => 1
                        ];

                        $this->stellaService->masi_store_logs($params);
                    } else {
                        $msg = "No existen serv. file: " . $valor['nroref'];
                        $status = false;
                    }
                } else {
                    $msg = "No existen emails file: " . $valor['nroref'];
                    $status = false;
                }
            }
        } else {
            $msg = 'No existen files ' . $date;
            $status = false;
        }

        return [
            'message' => $msg,
            'status' => $status,
            'last_params' => $params,
        ];
    }

    public function test_mailing_day_before($_key, $type, $email = [], $form = [], $isDev = false, Request $request)
    {
        try {
            $clients = $this->getClients($type);
            $clients_query = $clients->implode('code', "','");

            $files = $this->getFiles();
            $files_query = $files->implode('nroref', "','");

            $date_now = date('d-m-Y');
            $date = str_replace('-', '/', $date_now);

            $file = '';

            if (count($form) > 0) {
                $file = $form['file'];
                $date = $form['date'];

                foreach ($email as $key => $value) {
                    $item = $form['passenger'];
                    $item['email'] = $value;

                    $email[$key] = $item;
                }
            }

            $params = [
                'type' => $_key,
                'date' => $date,
                'file' => $file,
                'files_query' => $files_query,
                'clients_query' => $clients_query,
                'isDev' => $isDev
            ];

            $response = (array)$this->stellaService->masi_mailing_job($params);
            $files = (array)$response['data'];

            if (count($files) > 0) {
                $_date = explode("/", $date);
                $date = $_date[2] . '-' . $_date[1] . '-' . $_date[0];

                foreach ($files as $clave => $valor) {
                    $valor = (array)$valor;

                    //Verifica si tiene permiso para poder enviar mailing
                    $emails = (array)(json_decode($valor['emails_pasajeros'], true));
                    $checkEmails = $this->checkEmailsFile($emails, $isDev);
                    if ($checkEmails) {
                        $lang = strtolower(trim($valor['idioma']));

                        if ($lang == 'es') {
                            $linkIcons = "https://drive.google.com/file/d/1JJq-rsODtGJEj28drHDfSvcCjC2NYCVG/view";
                            $linkAero = "https://res.cloudinary.com/litomarketing/image/upload/c_scale,w_300/v1560430025/aurora/mailing/mapa_es.png";
                        } else {
                            if ($lang == 'it') {
                                $linkIcons = "https://drive.google.com/file/d/16gQWjjIFtSp5Tp90GyhXW9Vjgb3-y5db/view";
                                $linkAero = "https://res.cloudinary.com/litomarketing/image/upload/c_scale,w_300/v1560430025/aurora/mailing/mapa_en.png";
                            } else {
                                if ($lang == "pt") {
                                    $linkIcons = "https://drive.google.com/file/d/16gQWjjIFtSp5Tp90GyhXW9Vjgb3-y5db/view";
                                    $linkAero = "https://res.cloudinary.com/litomarketing/image/upload/c_scale,w_300/v1560430025/aurora/mailing/mapa_en.png";
                                } else {
                                    if ($lang == "fr") {
                                        $linkIcons = "https://drive.google.com/file/d/1shlc811sHWmVdOCYlOOpqoh1DMEGfEnh/view";
                                        $linkAero = "https://res.cloudinary.com/litomarketing/image/upload/c_scale,w_300/v1560430025/aurora/mailing/mapa_en.png";
                                    } else {
                                        $lang = "en";
                                        $linkIcons = "https://drive.google.com/file/d/1HJODPl9rFJ4Dbbb5oHV3cOlFnWgCaa5M/view";
                                        $linkAero = "https://res.cloudinary.com/litomarketing/image/upload/c_scale,w_300/v1560430025/aurora/mailing/mapa_en.png";
                                    }
                                }
                            }
                        }

                        $langs = ['', 'es', 'en', 'pt'];
                        $key_lang = array_search($lang, $langs);
                        $key_lang = ($key_lang > 0) ? $key_lang : 2;
                        $lang = $langs[$key_lang];

                        $params = [
                            'nroref' => $valor['nroref'],
                            'lang' => $key_lang,
                        ];

                        $response = (array)$this->stellaService->masi_services_file($params);
                        $destinos = (array)$response['data'];
                        $destinos = $this->putTextSkeleton($destinos, $key_lang);

                        if (count($destinos) > 0) {

                            $params = [
                                'nroref' => $valor['nroref'],
                                'cliente' => $valor['cliente'],
                                'fecha' => date("d/m/Y"),
                                'hora' => date("H:i"),
                                'tiplog' => 'E'
                            ];
                            $this->stellaService->masi_store($params);
                            $color = (empty($valor['color'])) ? "#D68F02" : $valor['color'];
                            $folder = 'logos';
                            // Verifico si tiene logo y si tiene la opcion de poder enviar emails con su logo
                            $getLogo = $this->getLogo($clients, trim($valor['cliente']));

                            $inserts = array();
                            if ($isDev) {
                                $emails = $email;
                            }
                            $subject = $this->getTranslation(
                                'Tu viaje a Perú: Revisa tu itinerario',
                                $lang
                            ) . ' - ' . $valor['nroref'];
                            $hoteles = (array)json_decode($valor['hoteles'], true);
                            foreach ($emails as $k => $val) {
                                //Obtengo las ciudades, hoteles, vuelos
                                $idPax = $val['nrosec'];
                                $ciudades = $this->getServicesByPax($destinos, $idPax);
                                $vuelos = (array)json_decode($valor['vuelos'], true);
                                //--------
                                $servicios = $this->searchServicesByDate($ciudades, $date);
                                $hotel = $this->getHotel($hoteles, $date, $idPax);
                                $vuelo = $this->getVuelo($vuelos, $date, $idPax);
                                $skeleton = $this->getTextSkeleton($valor['nroref'], $servicios, strtoupper($lang));
                                $imagesServices = $this->getImagesServices($servicios);
                                $numeroDia = $this->getNumeroDia($ciudades, $date, $lang);
                                $dia = $numeroDia['dia'];
                                $pronosticoTiempo = $this->getWeatherFuture2($servicios, $date);
                                $certification_carbon = $this->checkPaxCertificationFile($valor['nroref'], $idPax);
                                $file = array(
                                    'idioma' => $lang,
                                    'color' => $color,
                                    'logo' => $getLogo['logo'],
                                    'useLogo' => $getLogo['useLogo'],
                                    'linkAero' => $linkAero,
                                    'linkIcons' => $linkIcons,
                                    'certification_carbon' => $certification_carbon,
                                    'file' => array(
                                        'dia' => $numeroDia,
                                        'hotel' => $hotel,
                                        'vuelo' => $vuelo,
                                        'skeleton' => $skeleton,
                                        'images' => $imagesServices,
                                        'pronostico' => $pronosticoTiempo,
                                    )
                                );
                                $ciudadesPool = $this->getInfoDestinos($ciudades, 2, $lang);
                                //Envio de Mail a los Pasajeros
                                $nameExl = explode(',', $val['nombre']);
                                $paxName = (count($nameExl) > 1) ? ucwords(strtolower($nameExl[1])) : ucwords(strtolower($nameExl[0]));
                                $docItinerario = $this->getItinerary(
                                    $valor['nroref'],
                                    $lang,
                                    $getLogo['logoFile'],
                                    $val['nrosec']
                                );
                                $encuesta = $this->getPoll(
                                    $valor['nroref'],
                                    $ciudadesPool,
                                    $lang,
                                    $getLogo['logoFile'],
                                    $val['nrosec']
                                );
                                $dataEmail = array(
                                    'file' => $valor['nroref'],
                                    'cliente' => ucwords(strtolower($valor['razon'])),
                                    'paxId' => $val['nrosec'],
                                    'paxName' => $paxName,
                                    'docItinerario' => $docItinerario,
                                    'poll' => $encuesta,
                                    'data' => $file
                                );

                                try {
                                    $html = (new \App\Mail\MailingMasiReminder(
                                        '24_horas_antes',
                                        $lang,
                                        $subject,
                                        $dataEmail
                                    ))->render();
                                    $send = $this->sendMail($html, $val['email'], $subject, ['MASI_MAILING']);
                                    $status_mail = $send['code'];
                                    $message_id = $send['message-id'];
                                    $message_mail = $send['message'];
                                    if ($send['code']) {
                                        $msg = 'Enviado correctamente';
                                        $status = true;
                                    } else {
                                        return [
                                            'message' => $message_mail,
                                            'status' => false
                                        ];
                                    }
                                } catch (\Exception $ex) {
                                    $status_mail = 'error';
                                    $message_mail = $ex->getMessage();
                                    $message_id = '';

                                    $msg = $ex->getMessage();
                                    $status = false;
                                }

                                $inserts[] = array(
                                    'email' => $val['email'],
                                    'nombre' => $val['nombre'],
                                    'status' => $status_mail,
                                    'message' => $message_mail,
                                    'dia' => $dia,
                                    'message_id' => $message_id,
                                );
                            }

                            $params = [
                                'type' => $_key,
                                'fecha' => date("d/m/Y"),
                                'hora' => date("H:i"),
                                'nroref' => $valor['nroref'],
                                'cliente' => $valor['cliente'],
                                'inserts' => $inserts,
                                'category' => 1
                            ];

                            $this->stellaService->masi_store_logs($params);
                        } else {
                            $msg = "No existen serv. file: " . $valor['nroref'];
                            $status = false;
                        }
                    } else {
                        $msg = "No existen emails file: " . $valor['nroref'];
                        $status = false;
                    }
                }
            } else {
                $msg = 'No existen files ' . $date;
                $status = false;
            }

            $response = array(
                'status' => $status,
                'message' => $msg
            );

            return $response;
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function test_mailing_daily($_key, $type, $email = [], $form = [], $isDev = false, Request $request)
    {
        $clients = $this->getClients($type);
        $clients_query = $clients->implode('code', "','");

        $files = $this->getFiles();
        $files_query = $files->implode('nroref', "','");

        $date_now = date('d-m-Y');
        $date = str_replace('-', '/', $date_now);

        $file = '';

        if (count($form) > 0) {
            $file = $form['file'];
            $date = $form['date'];

            foreach ($email as $key => $value) {
                $item = $form['passenger'];
                $item['email'] = $value;

                $email[$key] = $item;
            }
        }

        $params = [
            'type' => $_key,
            'date' => $date,
            'file' => $file,
            'files_query' => $files_query,
            'clients_query' => $clients_query,
            'isDev' => $isDev
        ];

        $response = (array)$this->stellaService->masi_mailing_job($params);
        $files = (array)($response['data']);
        $notFoundServices = false;
        $days_diff = -1;

        if (count($files) > 0) {
            $_date = explode("/", $date);
            $date = $_date[2] . '-' . $_date[1] . '-' . $_date[0];

            foreach ($files as $clave => $valor) {
                $valor = (array)$valor;

                $__llegada = Carbon::parse($valor['llegada']);
                $__salida = Carbon::parse($valor['salida']);

                $days_diff = $__llegada->diffInDays($__salida);

                //Verifica si tiene permiso para poder enviar mailing
                $emails = (array)(json_decode($valor['emails_pasajeros'], true));
                $checkEmails = $this->checkEmailsFile($emails, $isDev);
                if ($checkEmails) {
                    $lang = strtolower(trim($valor['idioma']));

                    if ($lang == 'es') {
                        $linkIcons = "https://drive.google.com/file/d/1JJq-rsODtGJEj28drHDfSvcCjC2NYCVG/view";
                        $linkAero = "https://res.cloudinary.com/litomarketing/image/upload/c_scale,w_300/v1560430025/aurora/mailing/mapa_es.png";
                    } else {
                        if ($lang == 'it') {
                            $linkIcons = "https://drive.google.com/file/d/16gQWjjIFtSp5Tp90GyhXW9Vjgb3-y5db/view";
                            $linkAero = "https://res.cloudinary.com/litomarketing/image/upload/c_scale,w_300/v1560430025/aurora/mailing/mapa_en.png";
                        } else {
                            if ($lang == "pt") {
                                $linkIcons = "https://drive.google.com/file/d/16gQWjjIFtSp5Tp90GyhXW9Vjgb3-y5db/view";
                                $linkAero = "https://res.cloudinary.com/litomarketing/image/upload/c_scale,w_300/v1560430025/aurora/mailing/mapa_en.png";
                            } else {
                                if ($lang == "fr") {
                                    $linkIcons = "https://drive.google.com/file/d/1shlc811sHWmVdOCYlOOpqoh1DMEGfEnh/view";
                                    $linkAero = "https://res.cloudinary.com/litomarketing/image/upload/c_scale,w_300/v1560430025/aurora/mailing/mapa_en.png";
                                } else {
                                    $lang = "en";
                                    $linkIcons = "https://drive.google.com/file/d/1HJODPl9rFJ4Dbbb5oHV3cOlFnWgCaa5M/view";
                                    $linkAero = "https://res.cloudinary.com/litomarketing/image/upload/c_scale,w_300/v1560430025/aurora/mailing/mapa_en.png";
                                }
                            }
                        }
                    }

                    $langs = ['', 'es', 'en', 'pt'];
                    $key_lang = array_search($lang, $langs);
                    $key_lang = ($key_lang > 0) ? $key_lang : 2;
                    $lang = $langs[$key_lang];

                    $params = [
                        'nroref' => $valor['nroref'],
                        'lang' => $key_lang,
                    ];

                    $response = (array)$this->stellaService->masi_services_file($params);
                    $destinos = (array)$response['data'];
                    $destinos = $this->putTextSkeleton($destinos, $key_lang);

                    if (count($destinos) > 0) {

                        $params = [
                            'nroref' => $valor['nroref'],
                            'cliente' => $valor['cliente'],
                            'fecha' => date("d/m/Y"),
                            'hora' => date("H:i"),
                            'tiplog' => 'E'
                        ];
                        $this->stellaService->masi_store($params);
                        $color = (empty($valor['color'])) ? "#D68F02" : $valor['color'];
                        $folder = 'logos';
                        // Verifico si tiene logo y si tiene la opcion de poder enviar emails con su logo
                        $getLogo = $this->getLogo($clients, trim($valor['cliente']));

                        $inserts = array();
                        if ($isDev) {
                            $emails = $email;
                        }
                        $subject = $this->getTranslation(
                            'Tu viaje a Perú: Revisa tu itinerario',
                            $lang
                        ) . ' - ' . $valor['nroref'];

                        $hoteles = (array)json_decode($valor['hoteles'], true);
                        foreach ($emails as $k => $val) {
                            //Obtengo las ciudades, hoteles, vuelos
                            $idPax = $val['nrosec'];
                            $ciudades = $this->getServicesByPax($destinos, $idPax);
                            $vuelos = (array)json_decode($valor['vuelos'], true);
                            //--------
                            $servicios = $this->searchServicesByDate($ciudades, $date);

                            if (count($servicios) == 0) {
                                $notFoundServices = true;
                                continue;
                            }

                            $hotel = $this->getHotel($hoteles, $date, $idPax);
                            $vuelo = $this->getVuelo($vuelos, $date, $idPax);
                            $skeleton = $this->getTextSkeleton($valor['nroref'], $servicios, strtoupper($lang));
                            $imagesServices = $this->getImagesServices($servicios);
                            $numeroDia = $this->getNumeroDia($ciudades, $date, $lang);
                            $dia = $numeroDia['dia'];
                            $pronosticoTiempo = $this->getWeatherFuture2($servicios, $date);
                            $certification_carbon = $this->checkPaxCertificationFile($valor['nroref'], $idPax);
                            $file = array(
                                'idioma' => $lang,
                                'color' => $color,
                                'logo' => $getLogo['logo'],
                                'useLogo' => $getLogo['useLogo'],
                                'linkAero' => $linkAero,
                                'linkIcons' => $linkIcons,
                                'certification_carbon' => $certification_carbon,
                                'file' => array(
                                    'dia' => $numeroDia['diaLetras'],
                                    'hotel' => $hotel,
                                    'vuelo' => $vuelo,
                                    'skeleton' => $skeleton,
                                    'images' => $imagesServices,
                                    'pronostico' => $pronosticoTiempo,
                                )
                            );
                            $ciudadesPool = $this->getInfoDestinos($ciudades, 2, $lang);
                            //Envio de Mail a los Pasajeros
                            $nameExl = explode(',', $val['nombre']);
                            $paxName = (count($nameExl) > 1) ? ucwords(strtolower($nameExl[1])) : ucwords(strtolower($nameExl[0]));
                            $docItinerario = $this->getItinerary(
                                $valor['nroref'],
                                $lang,
                                $getLogo['logoFile'],
                                $val['nrosec']
                            );
                            $encuesta = $this->getPoll(
                                $valor['nroref'],
                                $ciudadesPool,
                                $lang,
                                $getLogo['logoFile'],
                                $val['nrosec']
                            );
                            $dataEmail = array(
                                'file' => $valor['nroref'],
                                'cliente' => ucwords(strtolower($valor['razon'])),
                                'paxId' => $val['nrosec'],
                                'paxName' => $paxName,
                                'docItinerario' => $docItinerario,
                                'poll' => $encuesta,
                                'data' => $file,
                                'days_diff' => $days_diff,
                            );

                            try {
                                $html = (new \App\Mail\MailingMasiReminder(
                                    'dia_a_dia',
                                    $lang,
                                    $subject,
                                    $dataEmail
                                ))->render();
                                $send = $this->sendMail($html, $val['email'], $subject, ['MASI_MAILING']);
                                $status_mail = $send['code'];
                                $message_id = $send['message-id'];
                                $message_mail = $send['message'];
                                if ($send['code']) {
                                    $msg = 'Enviado correctamente';
                                    $status = true;
                                } else {
                                    return [
                                        'message' => $message_mail,
                                        'status' => false
                                    ];
                                }
                            } catch (\Exception $ex) {
                                $status_mail = 'error';
                                $message_mail = $ex->getMessage();
                                $message_id = '';

                                $msg = $ex->getMessage();
                                $status = false;
                            }

                            $inserts[] = array(
                                'email' => $val['email'],
                                'nombre' => $val['nombre'],
                                'status' => $status_mail,
                                'message' => $message_mail,
                                'dia' => $dia,
                                'message_id' => $message_id,
                            );
                        }

                        if ($notFoundServices == false) {
                            $params = [
                                'type' => $_key,
                                'fecha' => date("d/m/Y"),
                                'hora' => date("H:i"),
                                'nroref' => $valor['nroref'],
                                'cliente' => $valor['cliente'],
                                'inserts' => $inserts,
                                'category' => 1
                            ];

                            $this->stellaService->masi_store_logs($params);
                        }
                    } else {
                        $msg = "No existen serv. file: " . $valor['nroref'];
                        $status = false;
                    }
                } else {
                    $msg = "No existen emails file: " . $valor['nroref'];
                    $status = false;
                }
            }
        } else {
            $msg = 'No existen files ' . $date;
            $status = false;
        }

        if ($notFoundServices and $isDev) {
            $msg = 'No existen servicios para el día ' . $date;
            $status = false;
        }

        $response = array(
            'days' => $days_diff,
            'status' => $status,
            'message' => $msg
        );

        return $response;
    }

    public function test_mailing_survey($_key, $type, $email = [], $form = [], $isDev = false, Request $request)
    {
        $clients = $this->getClients($type);
        $clients_query = $clients->implode('code', "','");

        $files = $this->getFiles();
        $files_query = $files->implode('nroref', "','");

        $date_now = date('d-m-Y');
        $date = str_replace('-', '/', $date_now);

        $file = '';

        if (count($form) > 0) {
            $file = $form['file'];
            $date = $form['date'];

            foreach ($email as $key => $value) {
                $item = $form['passenger'];
                $item['email'] = $value;

                $email[$key] = $item;
            }
        }

        $params = [
            'type' => $_key,
            'date' => $date,
            'file' => $file,
            'files_query' => $files_query,
            'clients_query' => $clients_query,
            'isDev' => $isDev
        ];

        $response = (array)$this->stellaService->masi_mailing_job($params);
        $files = (array)$response['data'];

        if (count($files) > 0) {
            $_date = explode("/", $date);
            $date = $_date[2] . '-' . $_date[1] . '-' . $_date[0];

            foreach ($files as $clave => $valor) {
                $valor = (array)$valor;

                //Verifica si tiene permiso para poder enviar mailing
                $emails = (array)(json_decode($valor['emails_pasajeros'], true));
                $checkEmails = $this->checkEmailsFile($emails, $isDev);
                if ($checkEmails) {
                    $lang = strtolower(trim($valor['idioma']));

                    if ($lang == 'es') {
                        $linkIcons = "https://drive.google.com/file/d/1JJq-rsODtGJEj28drHDfSvcCjC2NYCVG/view";
                    } else {
                        if ($lang == 'it') {
                            $linkIcons = "https://drive.google.com/file/d/16gQWjjIFtSp5Tp90GyhXW9Vjgb3-y5db/view";
                        } else {
                            if ($lang == "pt") {
                                $linkIcons = "https://drive.google.com/file/d/16gQWjjIFtSp5Tp90GyhXW9Vjgb3-y5db/view";
                            } else {
                                if ($lang == "fr") {
                                    $linkIcons = "https://drive.google.com/file/d/1shlc811sHWmVdOCYlOOpqoh1DMEGfEnh/view";
                                } else {
                                    $linkIcons = "https://drive.google.com/file/d/1HJODPl9rFJ4Dbbb5oHV3cOlFnWgCaa5M/view";
                                }
                            }
                        }
                    }

                    $langs = ['', 'es', 'en', 'pt'];
                    $key_lang = array_search($lang, $langs);
                    $key_lang = ($key_lang > 0) ? $key_lang : 2;
                    $lang = $langs[$key_lang];

                    $params = [
                        'nroref' => $valor['nroref'],
                        'lang' => $key_lang,
                    ];

                    $response = (array)$this->stellaService->masi_services_file($params);
                    $destinos = (array)$response['data'];
                    $destinos = $this->putTextSkeleton($destinos, $key_lang);

                    if (count($destinos) > 0) {

                        $params = [
                            'nroref' => $valor['nroref'],
                            'cliente' => $valor['cliente'],
                            'fecha' => date("d/m/Y"),
                            'hora' => date("H:i"),
                            'tiplog' => 'E'
                        ];
                        $this->stellaService->masi_store($params);

                        $color = (empty($valor['color'])) ? "#D68F02" : $valor['color'];
                        // Verifico si tiene logo y si tiene la opcion de poder enviar emails con su logo
                        $getLogo = $this->getLogo($clients, trim($valor['cliente']));
                        //Obtengo las ciudades
                        $ciudades = $destinos;
                        $destinos = $this->getInfoDestinos($ciudades, 1, $lang);
                        $mapa = $this->getMapaStatic($ciudades);
                        $dia = 0;
                        $file = array(
                            'idioma' => $lang,
                            'color' => $color,
                            'logo' => $getLogo['logo'],
                            'useLogo' => $getLogo['useLogo'],
                            'mapa' => $mapa,
                            'linkIcons' => $linkIcons,
                            'file' => array(
                                'reserva' => array(
                                    'dias' => ((int)$valor['noches'] + 1),
                                    'noches' => ((int)$valor['noches'])
                                ),
                                'destinos' => $destinos,
                                'llegada' => date("d/m/Y", strtotime($valor['llegada'])),
                                'salida' => date("d/m/Y", strtotime($valor['salida'])),
                            )
                        );
                        $subject = $this->getTranslation(
                            'Tu viaje a Perú: Información Importante',
                            $lang
                        ) . ' - ' . $valor['nroref'];

                        //Envio de Mail a los Pasajeros
                        $inserts = array();
                        if ($isDev) {
                            $emails = $email;
                        }
                        $ciudadesPool = $this->getInfoDestinos($ciudades, 2, $lang);
                        foreach ($emails as $k => $val) {
                            $nameExl = explode(',', $val['nombre']);
                            $paxName = (count($nameExl) > 1) ? ucwords(strtolower($nameExl[1])) : ucwords(strtolower($nameExl[0]));
                            $docItinerario = $this->getItinerary(
                                $valor['nroref'],
                                $lang,
                                $getLogo['logoFile'],
                                $val['nrosec']
                            );
                            $encuesta = $this->getPoll(
                                $valor['nroref'],
                                $ciudadesPool,
                                $lang,
                                $getLogo['logoFile'],
                                $val['nrosec']
                            );
                            $certificate = $this->checkPaxCertificationFile($valor['nroref'], $val['nrosec']);
                            $dataEmail = array(
                                'file' => $valor['nroref'],
                                'cliente' => ucwords(strtolower($valor['razon'])),
                                'paxId' => $val['nrosec'],
                                'paxName' => $paxName,
                                'docItinerario' => $docItinerario,
                                'certification_carbon' => $certificate,
                                'poll' => $encuesta,
                                'data' => $file
                            );

                            try {
                                $html = (new \App\Mail\MailingMasiReminder(
                                    'bye',
                                    $lang,
                                    $subject,
                                    $dataEmail
                                ))->render();
                                $send = $this->sendMail($html, $val['email'], $subject, ['MASI_MAILING']);
                                $status_mail = $send['code'];
                                $message_id = $send['message-id'];
                                $message_mail = $send['message'];
                                if ($send['code']) {
                                    $msg = 'Enviado correctamente';
                                    $status = true;
                                } else {
                                    return [
                                        'message' => $message_mail,
                                        'status' => false
                                    ];
                                }
                            } catch (\Exception $ex) {
                                $status_mail = 'error';
                                $message_mail = $ex->getMessage();
                                $message_id = '';
                                $msg = $ex->getMessage();
                                $status = false;
                            }

                            $inserts[] = array(
                                'email' => $val['email'],
                                'nombre' => $val['nombre'],
                                'status' => $status_mail,
                                'message' => $message_mail,
                                'dia' => $dia,
                                'message_id' => $message_id,
                            );
                        }

                        $params = [
                            'type' => $_key,
                            'fecha' => date("d/m/Y"),
                            'hora' => date("H:i"),
                            'nroref' => $valor['nroref'],
                            'cliente' => $valor['cliente'],
                            'inserts' => $inserts,
                            'category' => 1
                        ];

                        $this->stellaService->masi_store_logs($params);
                    } else {
                        $msg = "No existen serv. file: " . $valor['nroref'];
                        $status = false;
                    }
                } else {
                    $msg = "No existen emails file: " . $valor['nroref'];
                    $status = false;
                }
            }
        } else {
            $msg = 'No existen files ' . $date;
            $status = false;
        }

        $response = [
            'message' => $msg,
            'status' => $status
        ];

        return $response;
    }

    public function preview_message_wsp(Request $request)
    {
        $key = $request->__get('type');
        $date = $request->__get('date');
        $file = $request->__get('file');
        $nrosec = $request->__get('pax_id');
        $lang = strtolower(trim($request->__get('lang')));

        $clients_query = '';
        $files_query = '';
        $isDev = true;
        $phone_number = [
            ['nombre' => 'Testing, Testing', 'phone' => '+51960799963', 'nrosec' => $nrosec],
        ];
        $html = 'No hay información..';

        $params = [
            'type' => $key,
            'date' => $date,
            'file' => $file,
            'clients_query' => $clients_query,
            'files_query' => $files_query,
            'isDev' => $isDev
        ];

        $response = $this->toArray($this->stellaService->masi_mailing_job($params));
        $files = $response['data'];

        if (count($files) > 0) {
            $_date = explode("/", $date);
            $date = $_date[2] . '-' . $_date[1] . '-' . $_date[0];

            $valor = $files[0];

            $__llegada = Carbon::parse($valor['llegada']);
            $__salida = Carbon::parse($valor['salida']);

            $days_diff = $__llegada->diffInDays($__salida);

            //Verifica si tiene permiso para poder enviar mailing
            $phone_numbers = $this->toArray(json_decode($valor['emails_pasajeros'], true));
            $checkPhoneNumbers = $this->checkPhoneNumbersFile($phone_numbers, $isDev);
            if ($checkPhoneNumbers) {
                $langs = ['', 'es', 'en', 'pt'];
                $key_lang = array_search($lang, $langs);
                $key_lang = ($key_lang > 0) ? $key_lang : 2;
                $lang = $langs[$key_lang];

                $params = [
                    'nroref' => $valor['nroref'],
                    'lang' => $key_lang,
                ];

                $response = $this->toArray($this->stellaService->masi_services_file($params));
                $destinos = $response['data'];
                $destinos = $this->putTextSkeleton($destinos, $key_lang);

                if (count($destinos) > 0) {
                    $getLogo = $this->getLogo([], trim($valor['cliente']));
                    $ciudades = $destinos;
                    $destinos = $this->getInfoDestinos($ciudades, 1, $lang);
                    $color = '';
                    $linkIcons = '';
                    $linkAero = '';
                    $mapa = $this->getMapaStatic($ciudades);

                    if ($isDev) {
                        $phone_numbers = $phone_number;
                    }

                    $ciudadesPool = $this->getInfoDestinos($ciudades, 2, $lang);
                    $val = $phone_numbers[0];
                    //Obtengo las ciudades, hoteles, vuelos
                    $nameExl = explode(',', $val['nombre']);
                    $paxName = (count($nameExl) > 1) ? ucwords(strtolower($nameExl[1])) : ucwords(strtolower($nameExl[0]));
                    $docItinerario = $this->getItinerary(
                        $valor['nroref'],
                        $lang,
                        $getLogo['logoFile'],
                        $val['nrosec']
                    );
                    $encuesta = $this->getPoll(
                        $valor['nroref'],
                        $ciudadesPool,
                        $lang,
                        $getLogo['logoFile'],
                        $val['nrosec']
                    );
                    $certificate = $this->checkPaxCertificationFile($valor['nroref'], $val['nrosec']);
                    $idPax = $val['nrosec'];

                    $numeroDia = $this->getNumeroDia($ciudades, $date, $lang);
                    $dia = $numeroDia['dia'];

                    $hoteles = (array)json_decode($valor['hoteles'], true);
                    $ciudades = $this->getServicesByPax($ciudades, $idPax);
                    $vuelos = (array)json_decode($valor['vuelos'], true);
                    //--------
                    $servicios = $this->searchServicesByDate($ciudades, $date);
                    $hotel = $this->getHotel($hoteles, $date, $idPax);
                    $vuelo = $this->getVuelo($vuelos, $date, $idPax);
                    $skeleton = $this->getTextSkeleton($valor['nroref'], $servicios, strtoupper($lang));
                    $imagesServices = $this->getImagesServices($servicios);
                    $pronosticoTiempo = $this->getWeatherFuture2($servicios, $date);
                    $certification_carbon = $this->checkPaxCertificationFile($valor['nroref'], $idPax);

                    if ($key == 1) {
                        $dia = 7;
                        $file = array(
                            'cliente' => $valor['cliente'],
                            'idioma' => $lang,
                            'color' => $color,
                            'logo' => $getLogo['logo'],
                            'useLogo' => $getLogo['useLogo'],
                            'mapa' => $mapa,
                            'linkIcons' => $linkIcons,
                            'file' => array(
                                'reserva' => array(
                                    'dias' => ((int)$valor['noches'] + 1),
                                    'noches' => ((int)$valor['noches'])
                                ),
                                'destinos' => $destinos,
                                'llegada' => date("d/m/Y", strtotime($valor['llegada'])),
                                'salida' => date("d/m/Y", strtotime($valor['salida'])),
                            )
                        );
                    }

                    if ($key == 2) {
                        $file = array(
                            'cliente' => $valor['cliente'],
                            'idioma' => $lang,
                            'color' => $color,
                            'logo' => $getLogo['logo'],
                            'useLogo' => $getLogo['useLogo'],
                            'linkAero' => $linkAero,
                            'linkIcons' => $linkIcons,
                            'certification_carbon' => $certification_carbon,
                            'file' => array(
                                'dia' => $numeroDia['diaLetras'],
                                'hotel' => $hotel,
                                'vuelo' => $vuelo,
                                'skeleton' => $skeleton,
                                'images' => $imagesServices,
                                'pronostico' => $pronosticoTiempo,
                            )
                        );
                    }

                    if ($key == 3) {
                        $file = array(
                            'cliente' => $valor['cliente'],
                            'idioma' => $lang,
                            'file' => array(
                                'dia' => $numeroDia['diaLetras'],
                                'hotel' => $hotel,
                                'vuelo' => $vuelo,
                                'skeleton' => $skeleton,
                                'itinerary' => $docItinerario,
                            )
                        );
                    }

                    if ($key == 5) {
                        $file = array(
                            'cliente' => $valor['cliente'],
                            'idioma' => $lang,
                            'file' => array(
                                'poll' => $encuesta,
                            )
                        );
                    }

                    $file['docItinerario'] = $docItinerario;
                    $file['certification_carbon'] = $certificate;
                    $file['poll'] = $encuesta;

                    //Envio de Mail a los Pasajeros
                    $html = $this->build_message_wsp(
                        $key,
                        $file,
                        $paxName,
                        $lang,
                        $days_diff
                    );
                }
            }
        }

        return response()->json([
            'file' => $file,
            'html' => $html
        ]);
    }

    public function build_message_wsp($tipo, $data, $name_pax, $lang, $days_diff = -1)
    {
        $langs = ['', 'es', 'en', 'pt'];
        $key_lang = array_search($lang, $langs);
        $key_lang = ($key_lang > 0) ? $key_lang : 2;
        $lang = $langs[$key_lang];

        \App::setLocale($lang);

        $enlaces_bio = [
            'es' => 'https://drive.google.com/file/d/1woXlswNjULFsYws_An4eEq2IR0vB4qwD/view?usp=sharing',
            'en' => 'https://drive.google.com/file/d/10YueILA5WclayjNHXpUBL-B3y_2MBBC4/view?usp=sharing',
            'pt' => 'https://drive.google.com/file/d/1tZcBoZtSNA4jE_r_sT5Olu02fQjyYw9O/view?usp=sharing',
            'it' => 'https://drive.google.com/file/d/10YueILA5WclayjNHXpUBL-B3y_2MBBC4/view?usp=sharing',
        ];

        $enlaces_info = [
            'es' => 'https://drive.google.com/file/d/1gv_ztGee2nk6QFvu-C0n_5Rak58zzV9p/view?usp=sharing',
            'en' => 'https://drive.google.com/file/d/1HJqhBlHaFHNd23UfYi_QLvn2VrDlsUcr/view?usp=sharing',
            'pt' => 'https://drive.google.com/file/d/1ZBnc68dx_vqcJHSTJHmGa97nfyQM5O2-/view?usp=sharing',
            'it' => 'https://drive.google.com/file/d/1VMPO1wsqt_IDhctfPeQRlfv2ohvcCwmV/view?usp=sharing',
            'fr' => 'https://drive.google.com/file/d/1RQDXvdaPjHmhOIeWgJgdkcXr1sRM8bTa/view?usp=sharing',
        ];

        $aeropuerto = [
            'cusco' => [
                'es' => 'https://drive.google.com/file/d/1yXpcfwCn8v400T_bt7_98MQjus-niBCh/view?usp=sharing',
                'en' => 'https://drive.google.com/file/d/1gsto7q9ADEbcaRNszr_0efn5X0Suad4a/view?usp=sharing',
                'pt' => 'https://drive.google.com/file/d/1iE820scmYB_nD0PgXhhZOvKWs1hFfQrC/view?usp=sharing',
                'it' => 'https://drive.google.com/file/d/1gsto7q9ADEbcaRNszr_0efn5X0Suad4a/view?usp=sharing',
            ],
            'lima' => [
                'es' => 'https://drive.google.com/file/d/1UtZRSF6uI19RtDg7_LtSqVeAeQeJ-J-d/view?usp=sharing',
                'en' => 'https://drive.google.com/file/d/11s6eJMIgw94I5wbaFrWco_IhOJlHAKud/view?usp=sharing',
                'pt' => 'https://drive.google.com/file/d/1Jwj_2gXK9Z5rESHKtNr7p48Lup7qIdSE/view?usp=sharing',
                'it' => 'https://drive.google.com/file/d/11s6eJMIgw94I5wbaFrWco_IhOJlHAKud/view?usp=sharing',
            ]
        ];

        $templates = [
            '1' => [
                'es' => 'HX65f14e8aee55c01fc55042856e8309fc',
                'en' => 'HXffd8947431643db7e362120047a8c524',
                'pt' => 'HXd07bd406ebc65b07c1380a27ed6f8def'
            ],
            '2' => [
                'v1' => [
                    'es' => 'HXfe80b9264ad37aa43066dbf5c3d1c846',
                    'en' => 'HXeb871e5d2b18e7bf859bf96a8f990f38',
                    'pt' => 'HX86e19e1feacbb7f82456da31caae5b89',
                ],
                'v2' => [
                    'es' => 'HXe882b1368f923be4f1bd9789abefef3d',
                    'en' => 'HXb97e6bda54cff92023519767fa94380f',
                    'pt' => 'HX1866dcebac6c1242c942ae90fb89f6ed',
                ],
                'v3' => [
                    'es' => 'HX99576f7d04c8c20b31ac77dc11cd4d59',
                    'en' => 'HXf56d360b755ec51b64f38d78c37e7269',
                    'pt' => 'HXa126b937e59e14ee4c601f84342712f9',
                ],
                'v4' => [
                    'es' => 'HX70a2c06b8ae548131c4365246e520e16',
                    'en' => 'HXc4a88cc556deda0e89c3d599c523695e',
                    'pt' => 'HXd041a4ae2f5c934f78688d550531b79d',
                ],
                'v5' => [
                    'es' => 'HXd89e53fe38934b5ac78d884f26a518a2',
                    'en' => 'HX2591100a473878d4d3c2ca60799aba9e',
                    'pt' => 'HX0981ec5d09b3efa970d77662c2e48c09',
                ],
                'v6' => [
                    'es' => 'HXa86ceaceb82bdb824df5cdfeeaf4bff5',
                    'en' => 'HX4c0e2055f17847eb238b0fc2b79c8ee2',
                    'pt' => 'HXf5be090c3ba978526d428d321fa151d9',
                ],
                'v7' => [
                    'es' => 'HXd447b74ec1b7fe86c7c551413515211d',
                    'en' => 'HXf0761284afd450eef270e794557b0217',
                    'pt' => 'HXa833f982967d7d0ea79bd617fa5d1481',
                ]
            ],
            '3' => [
                'v1' => [
                    'es' => 'HX0a3e8102113a7bc5417169a24926d212',
                    'en' => 'HX0086a24b9478b796dfcb76db632770e0',
                    'pt' => 'HXa878a87c693c7de92d3335e5d8d68844',
                ],
                'v2' => [
                    'es' => 'HX732e1260c69fbdf988a23fde8b912ead',
                    'en' => 'HX27951835ae5a021514ea254d0da80464',
                    'pt' => 'HXb9bcfb1200c384ae8eef90e1c7562ed0',
                ]
            ],
            '5' => [
                'es' => 'HXbf99cd667749de7300eb8fa678f2c12d',
                'en' => 'HX3bf66763e04a22ff8b2a21f4d96d4f83',
                'pt' => 'HX463a93a60e33c4be12080d0bf02f1c4e',
            ]
        ];

        $message = '';
        $parameters = [trim($name_pax)];
        $template = '';
        $message .= __('masi.text_hola') . ' ' . trim($name_pax) . ', 😁';
        $message .= "\r\n";

        if ($tipo == 1) {
            $message .= __('masi.texto_te_damos_bienvenida') . ' ' . __('masi.texto_esta_herramienta_web');
            $message .= "\r\n";
            $message .= __('masi.texto_quedan_7_dias') . ' 🥳';
            $message .= "\r\n";
            $message .= __('masi.texto_aqui_enviamos');
            $message .= "\r\n";
            $message .= '👉🏼 ' . __('masi.tlt_reserva') . ': ' . $data['file']['reserva']['dias'] . ' ' . __('masi.text_dias') . ' / ' . $data['file']['reserva']['noches'] . ' ' . __('masi.text_noches');
            $message .= "\r\n";
            $message .= '👉🏼 ' . __('masi.tlt_destinos') . ': ' . $data['file']['destinos'];
            $message .= "\r\n";
            $message .= '👉🏼 ' . __('masi.tlt_llegada') . ': ' . $data['file']['llegada'];
            $message .= "\r\n";
            $message .= '👉🏼 ' . __('masi.tlt_salida') . ': ' . $data['file']['salida'];
            $message .= "\r\n";
            $message .= __('masi.texto_preparate');
            $message .= "\r\n";
            $message .= '👉🏼 ' . __('masi.title_datos_generales_del_viaje') . ': ' . $enlaces_info[$lang];
            $message .= "\r\n";
            $message .= __('masi.texto_estamos_contacto_1') . ' ' . __('masi.texto_estamos_contacto_2') . ' 😊';

            $parameters[] = $data['file']['reserva']['dias'] . ' ' . __('masi.text_dias') . ' / ' . $data['file']['reserva']['noches'] . ' ' . __('masi.text_noches');
            $parameters[] = $data['file']['destinos'];
            $parameters[] = $data['file']['llegada'];
            $parameters[] = $data['file']['salida'];
            $parameters[] = $enlaces_info[$lang];

            $template = $templates[$tipo][$lang];
        }

        if ($tipo == 2) {
            $message .= __('masi.bienvenido_masi');
            $message .= "\r\n";
            $message .= __('masi.texto_esta_herramienta_web');
            $message .= "\r\n";
            $message .= __('masi.texto_recuerda') . ' ' . __('masi.text_web');
            $message .= "\r\n";
            $message .= __('masi.texto_detalle_de_llegada') . ':';

            $flag_flight = false;
            $flag_service = false;
            $flag_hotel = false;

            if ($data['file']['vuelo']['nrovuelo'] != '') {
                $message .= "\r\n";
                $message .= '*' . __('masi.texto_vuelo') . '*: ' . $data['file']['vuelo']['ciu_desde'] . ' - ' . $data['file']['vuelo']['ciu_hasta'] . ' | ' . __('masi.tlt_salida') . ': ' . $data['file']['vuelo']['horain'] . ' - ' . __('masi.tlt_llegada') . ': ' . $data['file']['vuelo']['horaout'];
                $message .= "\r\n";
                $message .= '*' . __('masi.texto_nrovuelo') . '*: ' . ((!empty(@$data['file']['vuelo']['nrovuelo'])) ? $data['file']['vuelo']['nrovuelo'] : '-');

                $parameters[] = $data['file']['vuelo']['ciu_desde'] . ' - ' . $data['file']['vuelo']['ciu_hasta'] . ' | ' . __('masi.tlt_salida') . ': ' . $data['file']['vuelo']['horain'] . ' - ' . __('masi.tlt_llegada') . ': ' . $data['file']['vuelo']['horaout'];
                $parameters[] = ((!empty(@$data['file']['vuelo']['nrovuelo'])) ? $data['file']['vuelo']['nrovuelo'] : '-');

                $flag_flight = true;
            }

            if (count($data['file']['skeleton']) > 0) {
                $message .= "\r\n";
                $message .= '*' . __('masi.texto_servicio') . 's*:';
                $message .= "\r\n";
                $data_parameter = '';
                for ($i = 0; $i < count($data['file']['skeleton']); $i++) {
                    $data_parameter .= ($i == 0) ? '- ' : '';
                    $data_parameter .= ($i > 0) ? ' | ' : '';
                    $data_parameter .= $data['file']['skeleton'][$i]['horin'] . ' ' . trim($data['file']['skeleton'][$i]['descrip']);
                }

                $message .= $data_parameter;
                $parameters[] = $data_parameter;

                $flag_service = true;
            }

            if ($data['file']['hotel'] !== '') {
                $message .= "\r\n";
                $message .= '*' . __('masi.texto_hotel') . '*:';
                $message .= "\r\n";
                $message .= '- ' . $data['file']['hotel'];

                $parameters[] = '- ' . $data['file']['hotel'];

                $flag_hotel = true;
            }

            if (isset($data['file']['pronostico']) && count($data['file']['pronostico']) > 0) {
                $message .= "\r\n";
                $message .= '*' . __('masi.texto_pronostico_tiempo') . '*: ' . $data['file']['pronostico'][0]['clima']['clima_min'] . '°C - ' . $data['file']['pronostico'][0]['clima']['clima_max'] . '°C';

                $parameters[] = $data['file']['pronostico'][0]['clima']['clima_min'] . '°C - ' . $data['file']['pronostico'][0]['clima']['clima_max'] . '°C';
            } else {
                $message .= "\r\n";
                $message .= '*' . __('masi.texto_pronostico_tiempo') . '*: -';

                $parameters[] = '-';
            }

            $message .= "\r\n";
            $message .= '*' . __('masi.services_shared_text');
            $message .= "\r\n";

            if ($data['file']['vuelo']['nrovuelo'] != '' && ((strtolower($data['file']['vuelo']['ciu_hasta']) == 'cusco') || strtolower($data['file']['vuelo']['ciu_hasta']) == 'lima')) {
                $message .= __('masi.texto_localiza_nuestro_representantes') . ' ' . $aeropuerto[strtolower($data['file']['vuelo']['ciu_hasta'])][$lang];
                $message .= "\r\n";

                $parameters[] = $aeropuerto[strtolower($data['file']['vuelo']['ciu_hasta'])][$lang];
            } else {
                if ($flag_flight) {
                    $message .= __('masi.texto_localiza_nuestro_representantes') . ' -';
                    $message .= "\r\n";

                    $parameters[] = '-';
                }
            }

            $message .= __('masi.btn_revisa_tu_itineario') . ' 👉🏼 ' . $data['docItinerario'];
            $message .= "\r\n";
            $message .= __('masi.btn_info_de_viaje') . ' 👉🏼 ' . $enlaces_info[$lang];
            $message .= "\r\n";
            $message .= __('masi.tlt_contacto');
            $message .= "\r\n";
            $message .= __('masi.text_contacto');
            $message .= "\r\n";
            $message .= '🟡 ' . __('masi.legend_wsp_email') . ': ' . __('masi.text_email');
            $message .= "\r\n";
            $message .= '🟡 ' . __('masi.legend_wsp_telefono') . ': ' . __('masi.text_celular') . ' / ' . __('masi.text_telefono');
            $message .= "\r\n";
            $message .= '🟡 ' . __('masi.legend_wsp_whatsapp') . ': ' . __('masi.text_whatsapp');
            $message .= "\r\n";
            $message .= __('masi.texto_nos_vemos_24_horas');

            $parameters[] = $data['docItinerario'];
            $parameters[] = $enlaces_info[$lang];
            $parameters[] = __('masi.text_email');
            $parameters[] = __('masi.text_celular') . ' / ' . __('masi.text_celular_alt');
            $parameters[] = __('masi.text_telefono');

            if ($flag_flight && $flag_service && $flag_hotel) // Vuelo, servicio y hotel
            {
                $template = $templates[$tipo]['v1'][$lang];
            }

            if ($flag_flight && $flag_service && !$flag_hotel) // vuelo y servicio
            {
                $template = $templates[$tipo]['v5'][$lang];
            }

            if ($flag_flight && !$flag_service && $flag_hotel) // vuelo y hotel
            {
                $template = $templates[$tipo]['v2'][$lang];
            }

            if ($flag_flight && !$flag_service && !$flag_hotel) // solo vuelo
            {
                $template = $templates[$tipo]['v7'][$lang];
            }

            if (!$flag_flight && $flag_service && !$flag_hotel) // solo servicio
            {
                $template = $templates[$tipo]['v3'][$lang];
            }

            if (!$flag_flight && $flag_service && $flag_hotel) // servicio y hotel
            {
                $template = $templates[$tipo]['v4'][$lang];
            }

            if (!$flag_flight && !$flag_service && $flag_hotel) // solo hotel
            {
                $template = $templates[$tipo]['v6'][$lang];
            }
        }

        if ($tipo == 3) {
            $message .= __('masi.a_continuacion') . ' ' . $data['file']['dia'] . ':'; //.' '.utf8_decode("\xF0\x9F\x98\xA1");
            $message .= "\r\n";

            $parameters[] = $data['file']['dia'];
            $flag_service = false;
            $flag_hotel = false;

            if (count($data['file']['skeleton']) > 0) {
                $data_parameter = '';
                for ($i = 0; $i < count($data['file']['skeleton']); $i++) {
                    $data_parameter .= ($i == 0) ? '- ' : '';
                    $data_parameter .= ($i > 0) ? ' | ' : '';
                    $data_parameter .= $data['file']['skeleton'][$i]['horin'] . ' '  . trim($data['file']['skeleton'][$i]['descrip']);
                }
                $message .= $data_parameter;
                $parameters[] = $data_parameter;
                $flag_service = true;
            }
            if ($data['file']['hotel'] !== '') {
                $message .= "\r\n";
                $message .= '*' . __('masi.texto_hotel') . '*:';
                $message .= "\r\n";
                $message .= '- ' . $data['file']['hotel'];

                $parameters[] = '- ' . $data['file']['hotel'];
                $flag_hotel = true;
            }

            $message .= "\r\n";
            $message .= '*' . __('masi.services_shared_text') . "\r\n";
            $message .= __('masi.para_descargar_tu') . ' ' . __('masi.itinerario_completo') . ' ' . __('masi.haz_click_en_el_siguiente_enlace') . ' ' . $data['file']['itinerary'] . "\r\n";
            $message .= __('masi.esperemos_que_tengas_un_gran_dia');

            $parameters[] = $data['file']['itinerary'];
            $template = $templates[$tipo]['v1'][$lang];

            if ($flag_service && $flag_hotel) {
                $template = $templates[$tipo]['v2'][$lang];
            }
        }

        if ($tipo == 5) {
            $message .= __('masi.gracias_por_tu_visita'); // .' '.$json->decode('"\uD83D\uDE04"');
            $message .= "\r\n";
            $message .= __('masi.text_apreciaremos_1');
            $message .= "\r\n";
            $message .= __('masi.ayudanos_completanto') . ' ' . __('masi.breve_encuesta') . ' ' . __('masi.que_no_te_tomara') . ' ' . $data['file']['poll'];
            $message .= "\r\n";
            $message .= __('masi.muchas_gracias'); //.' '.$json->decode('"\uD83D\uDC50"');

            $parameters[] = $data['file']['poll'];

            $template = $templates[$tipo][$lang];
        }

        return ['body' => $message, 'parameters' => $parameters, 'template' => $template];
    }

    public function checkPhoneNumbersFile($phoneNumbers, $isDev = false)
    {
        if ($isDev) {
            return true;
        }

        $check = false;
        if (is_array($phoneNumbers) and count($phoneNumbers) > 0) {
            foreach ($phoneNumbers as $clave => $valor) {
                if (!empty($valor['celular']) and $valor['celular'] !== '') {
                    $check = true;
                }
            }
        }
        return $check;
    }

    public function wsp_mailing_weekly($key, $type, $phone_number = [], $form = [], $isDev = false, Request $request)
    {
        $notFoundServices = false;
        $date_now = date('d-m-Y');
        $date = str_replace('-', '/', $date_now);

        $file = '';

        if (count($form) > 0) {
            $file = $form['file'];
            $date = $form['date'];

            foreach ($phone_number as $k => $value) {
                $item = $form['passenger'];
                $item['email'] = $value;

                $phone_number[$k] = $item;
            }
        }

        $clients = $this->getClientsWsp($type);
        $clients_query = $clients->implode('code', "','");

        $files = $this->getFiles();
        $files_query = $files->implode('nroref', "','");

        $params = [
            'type' => $key,
            'date' => $date,
            'file' => $file,
            'clients_query' => $clients_query,
            'files_query' => $files_query,
            'isDev' => $isDev
        ];

        $response = $this->toArray($this->stellaService->masi_mailing_job($params));
        $files = $response['data'];

        if (count($files) > 0) {
            $_date = explode("/", $date);
            $date = $_date[2] . '-' . $_date[1] . '-' . $_date[0];

            foreach ($files as $clave => $valor) {

                $__llegada = Carbon::parse($valor['llegada']);
                $__salida = Carbon::parse($valor['salida']);

                $days_diff = $__llegada->diffInDays($__salida);

                //Verifica si tiene permiso para poder enviar mailing
                $phone_numbers = $this->toArray(json_decode($valor['emails_pasajeros'], true));
                $checkPhoneNumbers = $this->checkPhoneNumbersFile($phone_numbers, $isDev);
                if ($checkPhoneNumbers) {
                    $lang = strtolower(trim($valor['idioma']));
                    $langs = ['', 'es', 'en', 'pt'];
                    $key_lang = array_search($lang, $langs);
                    $key_lang = ($key_lang > 0) ? $key_lang : 2;
                    $lang = $langs[$key_lang];

                    $params = [
                        'nroref' => $valor['nroref'],
                        'lang' => $key_lang,
                    ];

                    $response = $this->toArray($this->stellaService->masi_services_file($params));
                    $destinos = $response['data'];
                    $destinos = $this->putTextSkeleton($destinos, $key_lang);

                    if (count($destinos) > 0) {
                        //Guardo Log
                        $params = [
                            'nroref' => $valor['nroref'],
                            'cliente' => $valor['cliente'],
                            'fecha' => date("d/m/Y"),
                            'hora' => date("H:i"),
                            'tiplog' => 'W'
                        ];
                        $this->stellaService->masi_store($params);
                        $getLogo = $this->getLogo([], trim($valor['cliente']));
                        $ciudades = $destinos;
                        $destinos = $this->getInfoDestinos($ciudades, 1, $lang);
                        $color = '';
                        $linkIcons = '';
                        $mapa = $this->getMapaStatic($ciudades);
                        $dia = 7;
                        $file = array(
                            'idioma' => $lang,
                            'color' => $color,
                            'logo' => $getLogo['logo'],
                            'useLogo' => $getLogo['useLogo'],
                            'mapa' => $mapa,
                            'linkIcons' => $linkIcons,
                            'file' => array(
                                'reserva' => array(
                                    'dias' => ((int)$valor['noches'] + 1),
                                    'noches' => ((int)$valor['noches'])
                                ),
                                'destinos' => $destinos,
                                'llegada' => date("d/m/Y", strtotime($valor['llegada'])),
                                'salida' => date("d/m/Y", strtotime($valor['salida'])),
                            )
                        );
                        $inserts = array();
                        if ($isDev) {
                            $phone_numbers = $phone_number;
                        }

                        $ciudadesPool = $this->getInfoDestinos($ciudades, 2, $lang);
                        foreach ($phone_numbers as $k => $val) {
                            //Obtengo las ciudades, hoteles, vuelos
                            $idPax = $val['nrosec'];
                            $vuelos = (array)json_decode($valor['vuelos'], true);
                            //--------
                            $vuelos = $this->getVueloMinMax($vuelos, $idPax);

                            if (!empty($vuelos['min']) && !empty($vuelos['max'])) {
                                $__llegada = Carbon::parse($vuelos['min']);
                                $__salida = Carbon::parse($vuelos['max']);

                                $nights = $__llegada->diffInDays($__salida);

                                $file['file']['reserva'] = array(
                                    'dias' => $nights + 1,
                                    'noches' => $nights,
                                );
                                $file['file']['llegada'] = date("d/m/Y", strtotime($__llegada));
                                $file['file']['salida'] = date("d/m/Y", strtotime($__salida));
                            }

                            $nameExl = explode(',', $val['nombre']);
                            $paxName = (count($nameExl) > 1) ? ucwords(strtolower($nameExl[1])) : ucwords(strtolower($nameExl[0]));
                            $docItinerario = $this->getItinerary(
                                $valor['nroref'],
                                $lang,
                                $getLogo['logoFile'],
                                $val['nrosec']
                            );
                            $encuesta = $this->getPoll(
                                $valor['nroref'],
                                $ciudadesPool,
                                $lang,
                                $getLogo['logoFile'],
                                $val['nrosec']
                            );
                            $certificate = $this->checkPaxCertificationFile($valor['nroref'], $val['nrosec']);

                            $file['docItinerario'] = $docItinerario;
                            $file['certification_carbon'] = $certificate;
                            $file['poll'] = $encuesta;

                            //Envio de Mail a los Pasajeros
                            $templateMessageWhatsApp = $this->build_message_wsp(
                                $key,
                                $file,
                                $paxName,
                                $lang,
                                $days_diff
                            );
                            if ($isDev) {
                                $number = $val['email'];
                            } else {
                                $number = preg_replace("/[^0-9]/", "", $val['celular']);
                            }

                            $send = $this->sendWhatsApp($number, $templateMessageWhatsApp);

                            if (@$send['id'] != null && @$send['id'] != '') {
                                $inserts[] = array(
                                    'email' => $number,
                                    'nombre' => $val['nombre'],
                                    'status' => $send['sent'],
                                    'message' => $send['message'],
                                    'dia' => $dia,
                                    'message_id' => $send['id'],
                                );

                                $msg = 'Enviado correctamente';
                                $status = true;
                            } else {
                                $msg = $send['report_error'];
                                $status = false;
                            }
                        }

                        if ($notFoundServices == false && $status == true) {
                            $params = [
                                'type' => $key,
                                'fecha' => date("d/m/Y"),
                                'hora' => date("H:i"),
                                'nroref' => $valor['nroref'],
                                'cliente' => $valor['cliente'],
                                'inserts' => $inserts,
                                'category' => 2
                            ];

                            $this->stellaService->masi_store_logs($params);
                        }
                    } else {
                        $msg = "No existen serv. file: " . $valor['nroref'];
                        $status = false;
                    }
                } else {
                    $msg = "No existen emails file: " . $valor['nroref'];
                    $status = false;
                }
            }
        } else {
            $msg = 'No existen files ' . $date;
            $status = false;
        }

        if ($notFoundServices and $isDev) {
            $msg = 'No existen servicios para el día' . $date;
            $status = false;
        }

        $response = array(
            'status' => $status,
            'message' => $msg,
            'params' => $params,
            'template' => @$templateMessageWhatsApp,
        );

        return $response;
    }

    public function wsp_mailing_day_before(
        $key,
        $type,
        $phone_number = [],
        $form = [],
        $isDev = false,
        Request $request
    ) {
        $notFoundServices = false;
        $date_now = date('d-m-Y');
        $date = str_replace('-', '/', $date_now);

        $file = '';

        if (count($form) > 0) {
            $file = $form['file'];
            $date = $form['date'];

            foreach ($phone_number as $k => $value) {
                $item = $form['passenger'];
                $item['email'] = $value;

                $phone_number[$k] = $item;
            }
        }

        $clients = $this->getClientsWsp($type);
        $clients_query = $clients->implode('code', "','");

        $files = $this->getFiles();
        $files_query = $files->implode('nroref', "','");

        $params = [
            'type' => $key,
            'date' => $date,
            'file' => $file,
            'clients_query' => $clients_query,
            'files_query' => $files_query,
            'isDev' => $isDev
        ];

        $response = $this->toArray($this->stellaService->masi_mailing_job($params));
        $files = $response['data'];

        if (count($files) > 0) {
            $_date = explode("/", $date);
            $date = $_date[2] . '-' . $_date[1] . '-' . $_date[0];

            foreach ($files as $clave => $valor) {

                $__llegada = Carbon::parse($valor['llegada']);
                $__salida = Carbon::parse($valor['salida']);

                $days_diff = $__llegada->diffInDays($__salida);

                //Verifica si tiene permiso para poder enviar mailing
                $phone_numbers = $this->toArray(json_decode($valor['emails_pasajeros'], true));
                $checkPhoneNumbers = $this->checkPhoneNumbersFile($phone_numbers, $isDev);
                if ($checkPhoneNumbers) {
                    $lang = strtolower(trim($valor['idioma']));
                    $langs = ['', 'es', 'en', 'pt'];
                    $key_lang = array_search($lang, $langs);
                    $key_lang = ($key_lang > 0) ? $key_lang : 2;
                    $lang = $langs[$key_lang];

                    $params = [
                        'nroref' => $valor['nroref'],
                        'lang' => $key_lang,
                    ];

                    $response = $this->toArray($this->stellaService->masi_services_file($params));
                    $destinos = $response['data'];
                    $destinos = $this->putTextSkeleton($destinos, $key_lang);

                    if (count($destinos) > 0) {
                        //Guardo Log
                        $params = [
                            'nroref' => $valor['nroref'],
                            'cliente' => $valor['cliente'],
                            'fecha' => date("d/m/Y"),
                            'hora' => date("H:i"),
                            'tiplog' => 'W'
                        ];
                        $this->stellaService->masi_store($params);
                        $getLogo = $this->getLogo([], trim($valor['cliente']));


                        $inserts = array();
                        if ($isDev) {
                            $phone_numbers = $phone_number;
                        }

                        $hoteles = (array)json_decode($valor['hoteles'], true);
                        $color = '';
                        $linkAero = '';
                        $linkIcons = '';

                        foreach ($phone_numbers as $k => $val) {
                            //Obtengo las ciudades, hoteles, vuelos
                            $idPax = $val['nrosec'];
                            $ciudades = $this->getServicesByPax($destinos, $idPax);
                            $vuelos = (array)json_decode($valor['vuelos'], true);
                            //--------
                            $servicios = $this->searchServicesByDate($ciudades, $date);
                            $hotel = $this->getHotel($hoteles, $date, $idPax);
                            $vuelo = $this->getVuelo($vuelos, $date, $idPax);
                            $skeleton = $this->getTextSkeleton($valor['nroref'], $servicios, strtoupper($lang));
                            $imagesServices = $this->getImagesServices($servicios);
                            $numeroDia = $this->getNumeroDia($ciudades, $date, $lang);
                            $dia = $numeroDia['dia'];
                            $pronosticoTiempo = $this->getWeatherFuture2($servicios, $date);
                            $certification_carbon = $this->checkPaxCertificationFile($valor['nroref'], $idPax);
                            $file = array(
                                'idioma' => $lang,
                                'color' => $color,
                                'logo' => $getLogo['logo'],
                                'useLogo' => $getLogo['useLogo'],
                                'linkAero' => $linkAero,
                                'linkIcons' => $linkIcons,
                                'certification_carbon' => $certification_carbon,
                                'file' => array(
                                    'dia' => $numeroDia,
                                    'hotel' => $hotel,
                                    'vuelo' => $vuelo,
                                    'skeleton' => $skeleton,
                                    'images' => $imagesServices,
                                    'pronostico' => $pronosticoTiempo,
                                )
                            );
                            $ciudadesPool = $this->getInfoDestinos($ciudades, 2, $lang);
                            //Envio de Mail a los Pasajeros
                            $nameExl = explode(',', $val['nombre']);
                            $paxName = (count($nameExl) > 1) ? ucwords(strtolower($nameExl[1])) : ucwords(strtolower($nameExl[0]));
                            $docItinerario = $this->getItinerary(
                                $valor['nroref'],
                                $lang,
                                $getLogo['logoFile'],
                                $val['nrosec']
                            );
                            $encuesta = $this->getPoll(
                                $valor['nroref'],
                                $ciudadesPool,
                                $lang,
                                $getLogo['logoFile'],
                                $val['nrosec']
                            );

                            $file['docItinerario'] = $docItinerario;
                            $file['certification_carbon'] = $certification_carbon;
                            $file['poll'] = $encuesta;

                            //Envio de Mail a los Pasajeros
                            $templateMessageWhatsApp = $this->build_message_wsp(
                                $key,
                                $file,
                                $paxName,
                                $lang,
                                $days_diff
                            );
                            if ($isDev) {
                                $number = $val['email'];
                            } else {
                                $number = preg_replace("/[^0-9]/", "", $val['celular']);
                            }

                            $send = $this->sendWhatsApp($number, $templateMessageWhatsApp);

                            if (@$send['id'] != null and @$send['id'] != '') {
                                $inserts[] = array(
                                    'email' => $number,
                                    'nombre' => $val['nombre'],
                                    'status' => $send['sent'],
                                    'message' => $send['message'],
                                    'dia' => $dia,
                                    'message_id' => $send['id'],
                                );

                                $msg = 'Enviado correctamente';
                                $status = true;
                            } else {
                                $msg = $send['report_error'];
                                $status = false;
                            }
                        }

                        if ($notFoundServices == false and $status == true) {
                            $params = [
                                'type' => $key,
                                'fecha' => date("d/m/Y"),
                                'hora' => date("H:i"),
                                'nroref' => $valor['nroref'],
                                'cliente' => $valor['cliente'],
                                'inserts' => $inserts,
                                'category' => 2
                            ];

                            $this->stellaService->masi_store_logs($params);
                        }
                    } else {
                        $msg = "No existen serv. file: " . $valor['nroref'];
                        $status = false;
                    }
                } else {
                    $msg = "No existen emails file: " . $valor['nroref'];
                    $status = false;
                }
            }
        } else {
            $msg = 'No existen files ' . $date;
            $status = false;
        }

        if ($notFoundServices and $isDev) {
            $msg = 'No existen servicios para el día' . $date;
            $status = false;
        }

        $response = array(
            'status' => $status,
            'message' => $msg,
            'template' => @$templateMessageWhatsApp,
        );

        return $response;
    }

    public function wsp_mailing_daily($key, $type, $phone_number = [], $form = [], $isDev = false, Request $request)
    {
        $notFoundServices = false;
        $date_now = date('d-m-Y');
        $date = str_replace('-', '/', $date_now);

        $file = '';

        if (count($form) > 0) {
            $file = $form['file'];
            $date = $form['date'];

            foreach ($phone_number as $k => $value) {
                $item = $form['passenger'];
                $item['email'] = $value;

                $phone_number[$k] = $item;
            }
        }

        $clients = $this->getClientsWsp($type);
        $clients_query = $clients->implode('code', "','");

        $files = $this->getFiles();
        $files_query = $files->implode('nroref', "','");

        $params = [
            'type' => $key,
            'date' => $date,
            'file' => $file,
            'clients_query' => $clients_query,
            'files_query' => $files_query,
            'isDev' => $isDev
        ];

        $response = $this->toArray($this->stellaService->masi_mailing_job($params));
        $files = $response['data'];

        if (count($files) > 0) {
            $_date = explode("/", $date);
            $date = $_date[2] . '-' . $_date[1] . '-' . $_date[0];

            foreach ($files as $clave => $valor) {
                $__llegada = Carbon::parse($valor['llegada']);
                $__salida = Carbon::parse($valor['salida']);

                $days_diff = $__llegada->diffInDays($__salida);

                //Verifica si tiene permiso para poder enviar mailing
                $phone_numbers = $this->toArray(json_decode($valor['emails_pasajeros'], true));
                $checkPhoneNumbers = $this->checkPhoneNumbersFile($phone_numbers, $isDev);
                if ($checkPhoneNumbers) {
                    $lang = strtolower(trim($valor['idioma']));
                    $langs = ['', 'es', 'en', 'pt'];
                    $key_lang = array_search($lang, $langs);
                    $key_lang = ($key_lang > 0) ? $key_lang : 2;
                    $lang = $langs[$key_lang];

                    $params = [
                        'nroref' => $valor['nroref'],
                        'lang' => $key_lang,
                    ];

                    $response = $this->toArray($this->stellaService->masi_services_file($params));
                    $destinos = $response['data'];
                    $destinos = $this->putTextSkeleton($destinos, $key_lang);

                    if (count($destinos) > 0) {
                        //Guardo Log
                        $params = [
                            'nroref' => $valor['nroref'],
                            'cliente' => $valor['cliente'],
                            'fecha' => date("d/m/Y"),
                            'hora' => date("H:i"),
                            'tiplog' => 'W'
                        ];
                        $this->stellaService->masi_store($params);
                        $getLogo = $this->getLogo([], trim($valor['cliente']));
                        $inserts = array();
                        if ($isDev) {
                            $phone_numbers = $phone_number;
                        }
                        $hoteles = $this->toArray(json_decode($valor['hoteles'], true));
                        foreach ($phone_numbers as $k => $val) {
                            //Obtengo las ciudades, hoteles, vuelos
                            $idPax = $val['nrosec'];
                            $ciudades = $this->getServicesByPax($destinos, $idPax);
                            $vuelos = (array)$this->toArray(json_decode($valor['vuelos'], true));
                            $servicios = $this->searchServicesByDate($ciudades, $date);
                            if (count($servicios) == 0) {
                                $notFoundServices = true;
                                continue;
                            }
                            $hotel = $this->getHotel($hoteles, $date, $idPax);
                            $vuelo = $this->getVuelo($vuelos, $date, $idPax);
                            $skeleton = $this->getTextSkeleton($valor['nroref'], $servicios, strtoupper($lang));
                            $numeroDia = $this->getNumeroDia($ciudades, $date, $lang);
                            $docItinerario = $this->getItinerary(
                                $valor['nroref'],
                                $lang,
                                $getLogo['logoFile'],
                                $val['nrosec']
                            );

                            $file = array(
                                'idioma' => $lang,
                                'file' => array(
                                    'dia' => $numeroDia['diaLetras'],
                                    'hotel' => $hotel,
                                    'vuelo' => $vuelo,
                                    'skeleton' => $skeleton,
                                    'itinerary' => $docItinerario,
                                )
                            );
                            //Envio de Mail a los Pasajeros
                            $nameExl = explode(',', $val['nombre']);
                            $paxName = (count($nameExl) > 1) ? ucwords(strtolower($nameExl[1])) : ucwords(strtolower($nameExl[0]));
                            $templateMessageWhatsApp = $this->build_message_wsp(
                                $key,
                                $file,
                                $paxName,
                                $lang,
                                $days_diff
                            );
                            if ($isDev) {
                                $number = $val['email'];
                            } else {
                                $number = preg_replace("/[^0-9]/", "", $val['celular']);
                            }

                            $send = $this->sendWhatsApp($number, $templateMessageWhatsApp);

                            if (@$send['id'] != '' and @$send['id'] != null) {
                                $inserts[] = array(
                                    'email' => $number,
                                    'nombre' => $val['nombre'],
                                    'status' => $send['sent'],
                                    'message' => $send['message'],
                                    'dia' => $numeroDia['dia'],
                                    'message_id' => $send['id'],
                                );

                                $msg = 'Enviado correctamente';
                                $status = true;
                            } else {
                                $msg = $send['report_error'];
                                $status = false;
                            }
                        }

                        if ($notFoundServices == false and $status == true) {
                            $params = [
                                'type' => $key,
                                'fecha' => date("d/m/Y"),
                                'hora' => date("H:i"),
                                'nroref' => $valor['nroref'],
                                'cliente' => $valor['cliente'],
                                'inserts' => $inserts,
                                'category' => 2
                            ];

                            $this->stellaService->masi_store_logs($params);
                        }
                    } else {
                        $msg = "No existen serv. file: " . $valor['nroref'];
                        $status = false;
                    }
                } else {
                    $msg = "No existen emails file: " . $valor['nroref'];
                    $status = false;
                }
            }
        } else {
            $msg = 'No existen files ' . $date;
            $status = false;
        }

        if ($notFoundServices and $isDev) {
            $msg = 'No existen servicios para el día' . $date;
            $status = false;
        }

        $response = array(
            'status' => $status,
            'message' => $msg,
            'template' => @$templateMessageWhatsApp,
        );

        return $response;
    }

    public function wsp_mailing_survey($key, $type, $phone_number = [], $form = [], $isDev = false, Request $request)
    {
        $date_now = date('d-m-Y');
        $date = str_replace('-', '/', $date_now);

        $file = '';

        if (count($form) > 0) {
            $file = $form['file'];
            $date = $form['date'];

            foreach ($phone_number as $k => $value) {
                $item = $form['passenger'];
                $item['email'] = $value;

                $phone_number[$k] = $item;
            }
        }

        $clients = $this->getClientsWsp($type);
        $clients_query = $clients->implode('code', "','");

        $files = $this->getFiles();
        $files_query = $files->implode('nroref', "','");

        $params = [
            'type' => $key,
            'date' => $date,
            'file' => $file,
            'clients_query' => $clients_query,
            'files_query' => $files_query,
            'isDev' => $isDev
        ];

        $response = $this->toArray($this->stellaService->masi_mailing_job($params));
        $files = $response['data'];

        if (count($files) > 0) {
            $_date = explode("/", $date);
            $date = $_date[2] . '-' . $_date[1] . '-' . $_date[0];

            foreach ($files as $clave => $valor) {
                //Verifica si tiene permiso para poder enviar mailing
                $phone_numbers = $this->toArray(json_decode($valor['emails_pasajeros'], true));
                $checkPhoneNumbers = $this->checkPhoneNumbersFile($phone_numbers, $isDev);
                if ($checkPhoneNumbers) {
                    $lang = strtolower(trim($valor['idioma']));
                    $langs = ['', 'es', 'en', 'pt'];
                    $key_lang = array_search($lang, $langs);
                    $key_lang = ($key_lang > 0) ? $key_lang : 2;
                    $lang = $langs[$key_lang];

                    $params = [
                        'nroref' => $valor['nroref'],
                        'lang' => $key_lang,
                    ];
                    $response = $this->toArray($this->stellaService->masi_services_file($params));
                    $destinos = $response['data'];
                    $destinos = $this->putTextSkeleton($destinos, $key_lang);

                    if (count($destinos) > 0) {
                        $params = [
                            'nroref' => $valor['nroref'],
                            'cliente' => $valor['cliente'],
                            'fecha' => date("d/m/Y"),
                            'hora' => date("H:i"),
                            'tiplog' => 'W'
                        ];
                        $this->stellaService->masi_store($params);
                        $getLogo = $this->getLogo([], trim($valor['cliente']));
                        $dia = 0;
                        $inserts = array();
                        if ($isDev) {
                            $phone_numbers = $phone_number;
                        }
                        foreach ($phone_numbers as $k => $val) {
                            //Obtengo las ciudades, hoteles, vuelos
                            $idPax = $val['nrosec'];
                            $ciudades = $this->getServicesByPax($destinos, $idPax);
                            $ciudadesPool = $this->getInfoDestinos($ciudades, 2, $lang);
                            //Envio de Mail a los Pasajeros
                            $encuesta = $this->getPoll(
                                $valor['nroref'],
                                $ciudadesPool,
                                $lang,
                                $getLogo['logoFile'],
                                $val['nrosec']
                            );

                            $file = array(
                                'idioma' => $lang,
                                'file' => array(
                                    'poll' => $encuesta,
                                )
                            );
                            //Envio de Mail a los Pasajeros
                            $nameExl = explode(',', $val['nombre']);
                            $paxName = (count($nameExl) > 1) ? ucwords(strtolower($nameExl[1])) : ucwords(strtolower($nameExl[0]));
                            $templateMessageWhatsApp = $this->build_message_wsp(
                                $key,
                                $file,
                                $paxName,
                                $lang
                            );
                            if ($isDev) {
                                $number = $val['email'];
                            } else {
                                $number = $val['celular'];
                            }
                            $send = $this->sendWhatsApp($number, $templateMessageWhatsApp);

                            if (@$send['id'] != null and @$send['id'] != '') {
                                $inserts[] = array(
                                    'email' => $val['email'],
                                    'nombre' => $val['nombre'],
                                    'status' => $send['sent'],
                                    'message' => $send['message'],
                                    'dia' => $dia,
                                    'message_id' => $send['id'],
                                );

                                $msg = 'Enviado correctamente';
                                $status = true;
                            } else {
                                $msg = $send['report_error'];
                                $status = false;
                            }
                        }

                        if ($status == true) {
                            $params = [
                                'type' => $key,
                                'fecha' => date("d/m/Y"),
                                'hora' => date("H:i"),
                                'nroref' => $valor['nroref'],
                                'cliente' => $valor['cliente'],
                                'inserts' => $inserts,
                                'category' => 2
                            ];

                            $this->stellaService->masi_store_logs($params);
                        }
                    } else {
                        $msg = "No existen serv. file: " . $valor['nroref'];
                        $status = false;
                    }
                } else {
                    $msg = "No existen emails file: " . $valor['nroref'];
                    $status = false;
                }
            }
        } else {
            $msg = 'No existen files ' . $date;
            $status = false;
        }

        $response = array(
            'status' => $status,
            'message' => $msg,
            'template' => @$templateMessageWhatsApp,
        );

        return $response;
    }

    public function getClients($type)
    {
        $clients = Client::select('clients.name', 'clients.code', 'clients.logo')
            ->where('status', '=', 1)
            ->where(function ($query) use ($type) {
                $query->whereDoesntHave('client_mailing');
                $query->orWhereHas('client_mailing', function ($query) use ($type) {
                    $query->where('status', '=', 0);
                    $query->orWhere($type, '=', 0);
                });
            })
            ->get();

        $clients = $clients->transform(function ($item) {
            $logo_explode = explode("/", $item['logo']);
            $item['logo'] = end($logo_explode);
            return $item;
        });

        return $clients;
    }

    public function getClientsWsp($type)
    {
        $clients = Client::select('clients.name', 'clients.code', 'clients.logo')
            ->where('status', '=', 1)
            ->where(function ($query) use ($type) {
                $query->whereDoesntHave('client_mailing');
                $query->orWhereHas('client_mailing', function ($query) use ($type) {
                    $query->where('status', '=', 0);
                    $query->where('whatsapp', '=', 0);
                    $query->orWhere($type, '=', 0);
                });
            })
            ->get();

        $clients = $clients->transform(function ($item) {
            $logo_explode = explode("/", $item['logo']);
            $item['logo'] = end($logo_explode);
            return $item;
        });

        return $clients;
    }

    public function getClientsAll(Request $request, $type = 'email')
    {
        $types = ['', 'weekly', 'day_before', 'daily', '', 'survey'];
        $clients = [];
        $ignore_files = [];

        if ($type == 'ignore_files') {
            $ignore_files = $this->getFiles();
        }

        foreach ($types as $key => $value) {
            if (!empty($value)) {
                if ($type == 'email') {
                    $clients[$value] = $this->getClients($value);
                }

                if ($type == 'wsp') {
                    $clients[$value] = $this->getClientsWsp($value);
                }

                if ($type == 'ignore_files') {
                    $clients[$value] = $ignore_files;
                }
            }
        }

        return response()->json($clients);
    }

    public function import_detail_file(Request $request)
    {
        try {
            $file = $request->__get('file');

            $files = MasiFileDetail::orderBy('created_at', 'desc')
                ->where('file', '=', $file)->get();

            return response()->json([
                'type' => 'success',
                'files' => $files,
            ]);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function update_detail_file(Request $request)
    {
        try {
            $files = (array)json_decode($request->__get('files'));

            MasiFileDetail::whereIn('file', $files)->update([
                'status_migrate' => 1
            ]);

            return response()->json([
                'type' => 'success',
                'files' => $files,
            ]);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function update_time_notification(Request $request)
    {
        try {
            $time = $request->__get('time');
            $configuration = MasiConfiguration::where('status', '=', 1)->first();
            $date = $request->__get('date');
            $flag_schedule = (int)$request->__get('flag_schedule');

            if (!$configuration) {
                $configuration = new MasiConfiguration;
                $configuration->status = 1;
            }

            $configuration->time = $time;
            $configuration->date = $date;
            $configuration->flag_schedule = $flag_schedule;
            $configuration->save();

            return response()->json([
                'success' => true,
                'configuration' => $configuration,
                'time' => $time,
                'date' => $date,
                'flag_schedule' => $flag_schedule
            ]);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function search_time_notification(Request $request)
    {
        try {
            $configuration = MasiConfiguration::where('status', '=', 1)->first();
            $time = '18:00';
            $date = '';
            $flag_schedule = 0;

            if ($configuration) {
                $time = $configuration->time;
                $date = $configuration->date;
                $flag_schedule = $configuration->flag_schedule;
            }

            return response()->json([
                'success' => true,
                'time' => $time,
                'date' => $date,
                'flag_schedule' => $flag_schedule
            ]);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function force_notification_masi(Request $request)
    {
        try {
            $date = $request->__get('date');
            $file = $request->__get('file');
            $service = $request->__get('service');

            $date = date("Y-m-d", strtotime("-1 days", strtotime($date)));

            $count = MasiActivityJobLogs::where('file', '=', $file)
                ->where('type_message', '=', 'daily')
                ->where('message', 'like', 'Envio de%')
                ->where('created_at', 'like', $date . '%')
                ->count();

            if ($count > 0) {
                // Validación de notificación por wsp y email..
            }

            return response()->json([
                'type' => 'success',
                'count' => $count,
                'service' => $service,
                'date' => $date,
                'file' => $file,
            ]);
        } catch (\Exception $ex) {
            $response = $this->throwError($ex);
            return response()->json($response);
        }
    }

    public function getLinkItinerary(Request $request)
    {
        try {
            $logo = $request->__get('logo');
            $nroref = $request->__get('nroref');
            $lang = $request->__get('lang');
            $pax = $request->__get('pax');

            $link = $this->getItinerary($nroref, $lang, $logo, $pax);

            return response()->json([
                'type' => 'success',
                'link' => $link,
            ]);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function is_time(Request $request)
    {
        try {
            $configuration = MasiConfiguration::where('status', '=', 1)->first();
            $time = '18:00';
            $date = '';
            $flag_schedule = 0;
            $time_survey = '09:00';
            $flag_time = FALSE;
            $flag_time_survey = FALSE;

            if (!empty(@$configuration->time)) {
                $flag_schedule = $configuration->flag_schedule;
                $date = $configuration->date;

                if ($flag_schedule == 1 or (!empty($date) and $date <= date("Y-m-d") and $flag_schedule == 0)) {
                    $time = $configuration->time;
                }
            }

            if ($time == date("H:i")) {
                $flag_time = TRUE;
            }

            if ($time_survey == date("H:i")) {
                $flag_time_survey = TRUE;
            }

            return response()->json([
                'flag_time' => $flag_time,
                'flag_time_survey' => $flag_time_survey,
            ]);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function updateStoreLogs(Request $request)
    {
        try {
            $logs = $this->toArray($request->__get('logs'));

            foreach ($logs as $key => $value) {
                $newLog = new MasiActivityJobLogs();
                $newLog->file = $value['file'];
                $newLog->nropax = $value['nropax'];
                $newLog->type_send = $value['type_send'];
                $newLog->type_message = $value['type_message'];
                $newLog->data = $value['data'];
                $newLog->message = $value['message'];
                $newLog->status_validation = $value['status_validation'];
                $newLog->save();
            }

            return response()->json([
                'type' => 'success',
                'logs' => $logs,
            ]);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function updateFileDetail(Request $request)
    {
        try {
            $files = $this->toArray($request->__get('files'));

            foreach ($files as $key => $value) {
                $value = $this->toArray($value);

                $detail = MasiFileDetail::where('file', '=', $value['nroref'])->first();

                if ($detail == '' || $detail == null) {
                    $detail = new MasiFileDetail();
                    $detail->file = $value['nroref'];
                    $detail->status_migrate = 0;
                }

                $detail->area = @$value['region'];
                $detail->product = @$value['producto'];
                $detail->country = @$value['pais'];
                $detail->client = @$value['cliente'];
                $detail->save();
            }

            return response()->json([
                'type' => 'success',
                'files' => $files,
            ]);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }
}
