<?php

namespace App\Console\Commands;

use App\Client;
use App\Http\Stella\StellaService;
use App\Http\Traits\Mailing;
use App\MasiActivityJobLogs;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use SendinBlue\Client\Api\TransactionalEmailsApi;
use SendinBlue\Client\Configuration;
use SendinBlue\Client\Model\SendSmtpEmail;

class MailingMasiReminderTest extends Command
{
    use Mailing;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mailing:masi-reminder-test {file} {client_code} {date} {type_email} {type}';

    protected $file = 0;
    protected $date = '';
    protected $client_code = '';
    protected $type_email = '';
    protected $type = 'email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera mailing para masi.. (7 días antes, 24 horas antes, despedida, dia a dia)';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    protected $stellaService;

    /*
     * Todo
     *  file = Numero de file
     *  client_code =  Codigo de cliente del file
     *  date = Fecha de inicio del file o de algun dia formato (dd-mm-yyyy)
     *  type_email = 'weekly', 'day_before', 'daily', 'survey'
     *  type = 'email', 'wts'
     *
     * Todo Example: php artisan mailing:masi-reminder-test 311496 '4VHOY' '11-04-2022' 'daily' 'email'
     */

    public function __construct(StellaService $stellaService)
    {
        $this->stellaService = $stellaService;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */

    public function handle()
    {
        $this->file = ($this->argument('file') == 0) ? '' : $this->argument('file');
        $this->date = $this->argument('date');
        $this->client_code = $this->argument('client_code');
        $this->type_email = $this->argument('type_email');
        $this->type = $this->argument('type');

        // $types = ['', 'weekly', 'day_before', 'daily', '', 'survey'];

        if ($this->type_email === 'weekly') {
            $key = 1;
        }
        if ($this->type_email === 'day_before') {
            $key = 2;
        }
        if ($this->type_email === 'daily') {
            $key = 3;
        }
        if ($this->type_email === 'survey') {
            $key = 5;
        }

        if ($this->type == 'email') {
            $field = 'test_mailing_'.$this->type_email;
        }

        if ($this->type == 'wts') {
            $field = 'wsp_mailing_'.$this->type_email;
        }

        $this->$field($key, $this->type_email, [], [], [], false);

    }

    public function sendMail($body_html, $email_to, $subject, $tag = [])
    {
        $config = Configuration::getDefaultConfiguration()->setApiKey('api-key',
            config('services.sendinblue.api_key_v3'));
        $apiInstance = new TransactionalEmailsApi(
            new \GuzzleHttp\Client(),
            $config
        );

        $send = [
            'code' => false,
            'message' => 'Email empty',
            'message-id' => '',
        ];

        if (!empty($email_to)) {
            $sendSMTPEmail = new SendSmtpEmail();
            $sendSMTPEmail['subject'] = $subject;
            $sendSMTPEmail['htmlContent'] = $body_html;
            $sendSMTPEmail['sender'] = array('name' => 'MASI', 'email' => 'no-reply@masi.pe');
            $sendSMTPEmail['tags'] = $tag;
            $sendSMTPEmail['to'] = array(
                array('email' => 'jgq@limatours.com.pe', 'name' => 'Jean Pierre Garay Quiñones'),
            );

            $sendSMTPEmail['headers'] = array("X-Mailin-Tag" => '', "Content-Type" => "text/html; charset=iso-8859-1");
            try {
                $sendEmail = $apiInstance->sendTransacEmail($sendSMTPEmail);
                $send = [
                    'code' => true,
                    'message' => 'Email sent successfully.',
                    'message-id' => $sendEmail['messageId'],
                ];
            } catch (\Exception $e) {
                $send = [
                    'code' => false,
                    'message' => $e->getMessage(),
                    'message-id' => '',
                ];
            }
        }

        return $send;

    }

    /**
     * PUSH WSP
     * @since 2021
     * @params Notification
     * @author KLuizSv
     */
    public function sendWhatsApp($phone_number, $message)
    {
        $data = array(
            'phone' => '51945599802',
            'body' => $message
        );
        $json_data = json_encode($data);
        $url = 'https://eu7.chat-api.com/instance84824/sendMessage?token=ivgvobk9ewbg907z';
        // Hacer una solicitud POST
        $options = stream_context_create(array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type: application/json',
                'content' => $json_data
            )
        ));

        // Enviar una solicitud
        $result = file_get_contents($url, false, $options);
        $response = (array)(json_decode($result, true));

        return $response;
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

    public function getLogo($cliente_logos, $client_code)
    {
        if (is_array($cliente_logos) OR is_object($cliente_logos)) {
            $logoFile = 'masi.png';
            foreach ($cliente_logos as $logos) {
                if ($logos->code == $client_code) {
                    $logoFile = $logos->logo;
                    break;
                }
            }
        } else {
            $logoFile = $client_code;
        }
        $folder = 'logos';
        $logo = 'https://res.cloudinary.com/litomarketing/image/upload/c_scale,h_190,q_100/aurora/'.$folder.'/'.trim($logoFile);
        $useLogo = true;
        $response = array(
            'logo' => $logo,
            'useLogo' => $useLogo,
            'logoFile' => $logoFile
        );

        return $response;
    }

    public function super_unique($array, $key)
    {
        // dd($array);
        $temp_array = array();
        $array = (array)$array;
        foreach ($array as $v) {
            $v = (array)$v;
            if (!isset($temp_array[$v[$key]])) {
                $temp_array[$v[$key]] = $v;
            }
        }
        $array = array_values($temp_array);
        return $array;
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
                        $destinos .= ' - '.ucwords(strtolower(trim($valor['ciuin'])));
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
                    $markers = '&markers=color:red|label:'.$num.'|'.trim($lat).','.trim($log);
                } else {
                    $markers .= '&markers=color:red|label:'.$num.'|'.trim($lat).','.trim($log);
                }
                $num++;
            }
            return $url.$markers.$apiKey;
        } catch (Exception $e) {
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

    public function getItinerary($nroref, $lang, $logo, $pax)
    {
        try {
            $data = $logo.','.$pax.','.$nroref.','.$lang;
            $url = 'https://www.masi.pe/app/itinerary/'.base64_encode($data);
            return $url;
        } catch (Exception $e) {
            return print_r($e);
        }
    }

    public function getPoll($nroref, $destinos, $lang, $logo, $pax)
    {
        try {
            $data = $logo.','.'uEo1ZJ'.','.$destinos.','.$pax.','.$nroref.','.$lang;
            $url = 'https://masi.pe/app/poll/'.base64_encode($data);
            return $url;
        } catch (Exception $e) {
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

    public function getHotel($hoteles, $date)
    {
        $hotel = '';
        $hoteles = (array)$hoteles;
        foreach ($hoteles as $clave => $valor) {
            $valor = (array)$valor;
            if ($valor['fecin'] === $date) {
                $hotel = $valor['nombre'];
                break;
            }
        }
        return $hotel;
    }

    public function getVuelo($vuelos, $date)
    {
        $vuelo = array(
            'ciu_desde' => '',
            'ciu_hasta' => '',
            'fecin' => '',
            'horain' => '',
            'fecout' => '',
            'horaout' => '',
            'nrovuelo' => '',
            'companyair' => ''
        );
        foreach ($vuelos as $clave => $valor) {
            $valor = (array)$valor;
            if ($valor['fecin'] === $date) {
                $vuelo = $valor;
                break;
            }
        }
        return $vuelo;
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

    public function getTextSkeleton($servicios, $lang)
    {
        $skeletons = array();
        $i = 0;
        foreach ($servicios as $clave => $valor) {
            try {
                $valor = (array)$valor;
                $params = [
                    'codsvs' => $valor['codsvs'].'0000',
                    'lang' => $lang
                ];

                $response = (array)$this->stellaService->getTextoSkeleton($params);
                if (isset($response['data']) and isset($response['success']) and count((array)$response['data']) > 0) {
                    $texto = (array)$response['data'];
                    if(isset($texto[0]->texto)){
                        $skeletons[$i]['horin'] = $valor['horin'];
                        $skeletons[$i]['descrip'] = trim($texto[0]->texto);
                        $i++;
                    }
                }
            } catch (\Exception $exception) {
            }
        }
        $skeletons = $this->orderMultiDimensionalArray($skeletons, 'horin');
        return $skeletons;
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
                    'https://res-5.cloudinary.com/litomarketing/image/upload/c_thumb,h_'.
                    $h.',w_'.$w.'/Parapente_bthb8r';
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
                        'https://res-5.cloudinary.com/litomarketing/image/upload/c_thumb,h_'.
                        $h.',w_'.$w.'/'.trim($img);
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
        $data = date("d/m/Y", strtotime($data));

        if ($data != '') {
            for ($i = 0; $i <= 53; $i++) {
                $setmana[] = (object)[
                    'name' => __('masi.semana').' '.$i
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
                    $dateString = $setmana[date('w', $data)]->name.' '.date('d', $data).' '.__('masi.de').' '.$mes[date(
                            'm',
                            $data
                        ) - 1]->name.''.__('masi.del').' '.date('Y', $data);
                } else {
                    $dateString = $setmana[date('w', $data)]->name.' '.$mes[date('m', $data) - 1]->name.' '.date(
                            'd',
                            $data
                        ).','.' '.date('Y', $data);
                }
            } elseif ($tipo == 2) {
                $dateString = date('d', $data).' '.ucwords(substr($mes[date('m', $data) - 1]->name, 0, 3)).' '.date(
                        'Y',
                        $data
                    );
            } elseif ($tipo == 3) {
                $dateString = ucwords(substr($setmana[date('w', $data)]->name, 0, 3)).' '.date(
                        'd',
                        $data
                    ).' '.ucwords(substr($mes[date('m', $data) - 1]->name, 0, 3));
            } elseif ($tipo == 4) {
                $dateString = ucwords($mes[date('m', $data) - 1]->name);
            } elseif ($tipo == 5) {
                if (strtolower($lang) == 'es' or strtolower($lang) == 'pt') {
                    $dateString = date('d', $data).' '.__('masi.de').' '.$mes[date('m', $data) - 1]->name;
                } elseif (strtolower($lang) == 'en') {
                    $dateString = $mes[date('m', $data) - 1]->name.' '.date('d', $data);
                } elseif (strtolower($lang) == 'it' or strtolower($lang) == 'fr') {
                    $dateString = date('d', $data).' '.$mes[date('m', $data) - 1]->name;
                }
            }
            return $dateString;
        } else {
            return 0;
        }
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

            $clima = array(
                'clima_min' => round($clima_min, 0, PHP_ROUND_HALF_UP),
                'clima_max' => round($clima_max, 0, PHP_ROUND_HALF_UP),
                'icon' => $icon
            );

            return $clima;
        } catch (Exception $e) {
            $clima = array(
                "clima_min" => 0,
                "clima_max" => 0
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
        } catch (Exception $e) {
            return $e;
        }
    }

    public function toArray($data)
    {
        $new_array = [];

        if (is_array($data) OR is_object($data)) {
            foreach ($data as $key => $value) {
                if (is_array($value) OR is_object($value)) {
                    $value = $this->toArray($value);
                }
                $new_array[$key] = $value;
            }
        }

        return $new_array;
    }

    public function test_mailing_weekly($_key, $type, $email = [], $form = [], $isDev = false)
    {

        $clients = $this->getClients($type, $this->client_code);
        $clients_query = $clients->implode('code', "','");

        $date_now = $this->date;
        $date_future = strtotime('+7 day', strtotime($date_now));
        $date_future = date('d-m-Y', $date_future);
        $date = str_replace('-', '/', $date_future);

        $file = $this->file;

        $params = [
            'type' => $_key,
            'date' => $date,
            'file' => $file,
            'clients_query' => $clients_query,
            'isDev' => false
        ];

        $response = (array)$this->stellaService->masi_mailing_job($params);
        $files = (array)json_decode(json_encode($response['data']), true);

        if (count($files) > 0) {
            $this->storeLogs(null, 'email', $type, $this->encodeArrayWithJsonColumns($files),
                'Busqueda de Files '.$date);
            $_date = explode("/", $date);
            $date = $_date[2].'-'.$_date[1].'-'.$_date[0];

            foreach ($files as $clave => $valor) {
                $valor = (array)$valor;
                //Verifica si tiene permiso para poder enviar mailing
                $emails = $this->toArray(json_decode($valor['emails_pasajeros']));
                $checkEmails = $this->checkEmailsFile($emails, $isDev);
                $this->storeLogs($valor['nroref'], 'email', $type, json_encode($emails), 'Lista de Pasajeros');
                if ($checkEmails) {
                    $lang = strtolower(trim($valor['idioma']));
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

                    $langs = ['', 'es', 'en', 'pt', 'it', 'fr'];

                    $params = [
                        'nroref' => $valor['nroref'],
                        'lang' => array_search($lang, $langs)
                    ];

                    $response = (array)$this->stellaService->masi_services_file($params);
                    $destinos = (array)json_decode(json_encode($response['data']), true);

                    if (count($destinos) > 0) {
                        $this->storeLogs($valor['nroref'], 'email', $type,
                            $this->encodeArrayWithJsonColumns($destinos, 'services_pax'), 'Servicios del file');
                        $params = [
                            'nroref' => $valor['nroref'],
                            'cliente' => $valor['cliente'],
                            'fecha' => date("d/m/Y"),
                            'hora' => date("H:i"),
                            'tiplog' => 'E'
                        ];

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
                            ).' - '.$valor['nroref'];

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
                                $val['id']
                            );
                            $encuesta = $this->getPoll(
                                $valor['nroref'],
                                $ciudadesPool,
                                $lang,
                                $getLogo['logoFile'],
                                $val['id']
                            );
                            $certificate = $this->checkPaxCertificationFile($valor['nroref'], $val['id']);
                            $dataEmail = array(
                                'file' => $valor['nroref'],
                                'cliente' => ucwords(strtolower($valor['razon'])),
                                'paxId' => $val['id'],
                                'paxName' => $paxName,
                                'docItinerario' => $docItinerario,
                                'certification_carbon' => $certificate,
                                'poll' => $encuesta,
                                'data' => $file
                            );

                            $status_mail = '';
                            $message_id = '';
                            $message_mail = '';
                            if (!empty($val['email'])) {
                                try {
                                    $html = (new \App\Mail\MailingMasiReminder('7_dias_antes', $lang, $subject,
                                        $dataEmail))->render();
                                    $send = $this->sendMail($html, $val['email'], $subject, ['MASI_MAILING']);
                                    $this->storeLogs($valor['nroref'], 'email', $type, json_encode($send),
                                        'Envio de Email: '.$val['email']);
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
                                    $this->storeLogs($valor['nroref'], 'email', $type, json_encode($ex->getMessage()),
                                        'Envio de Email: '.$val['email'], false);
                                    $msg = $ex->getMessage();
                                    $status = false;
                                }
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

                    } else {
                        $this->storeLogs($valor['nroref'], 'email', $type, null,
                            'No se encontraron servicios', false);
                        $msg = "No existen serv. file: ".$valor['nroref'];
                        $status = false;
                    }
                } else {
                    $this->storeLogs(trim($valor['nroref']), 'email', $type, null,
                        'No se encontraron emails para los pasajeros',false);
                }
            }
        } else {
            $this->storeLogs(null, 'email', $type, null,
                'No se encontraron files para el dia'.$date, false);
        }

    }

    public function test_mailing_day_before($_key, $type, $email = [], $form = [], $isDev = false)
    {
        $clients = $this->getClients($type, $this->client_code);
        $clients_query = $clients->implode('code', "','");

        $date_now = $this->date;
        $date = str_replace('-', '/', $date_now);

        $file = $this->file;

        $params = [
            'type' => $_key,
            'date' => $date,
            'file' => $file,
            'clients_query' => $clients_query,
            'isDev' => $isDev
        ];

        $response = (array)$this->stellaService->masi_mailing_job($params);
        $files = (array)json_decode(json_encode($response['data']), true);
        if (count($files) > 0) {
            $this->storeLogs(null, 'email', $type, $this->encodeArrayWithJsonColumns($files),
                'Busqueda de Files '.$date);
            $_date = explode("/", $date);
            $date = $_date[2].'-'.$_date[1].'-'.$_date[0];

            foreach ($files as $clave => $valor) {
                $valor = (array)$valor;

                //Verifica si tiene permiso para poder enviar mailing
                $emails = $this->toArray(json_decode($valor['emails_pasajeros']));
                $checkEmails = $this->checkEmailsFile($emails, $isDev);
                $this->storeLogs($valor['nroref'], 'email', $type, json_encode($emails), 'Lista de Pasajeros');
                if ($checkEmails) {
                    $lang = strtolower(trim($valor['idioma']));
                    if (trim($lang) == '' or trim($lang) == null) {
                        $lang = 'en';
                    }
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

                    $langs = ['', 'es', 'en', 'pt', 'it', 'fr'];

                    $params = [
                        'nroref' => $valor['nroref'],
                        'lang' => array_search($lang, $langs)
                    ];

                    $response = (array)$this->stellaService->masi_services_file($params);
                    $destinos = (array)json_decode(json_encode($response['data']), true);
                    if (count($destinos) > 0) {
                        $this->storeLogs($valor['nroref'], 'email', $type,
                            $this->encodeArrayWithJsonColumns($destinos, 'services_pax'), 'Servicios del file');
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
                            ).' - '.$valor['nroref'];
                        $hoteles = (array)json_decode($valor['hoteles'], true);
                        foreach ($emails as $k => $val) {
                            //Obtengo las ciudades, hoteles, vuelos
                            $idPax = $val['id'];
                            $ciudades = $this->getServicesByPax($destinos, $idPax);
                            $vuelos = (array)json_decode($valor['vuelos'], true);
                            //--------
                            $servicios = $this->searchServicesByDate($ciudades, $date);
                            $hotel = $this->getHotel($hoteles, $date);
                            $vuelo = $this->getVuelo($vuelos, $date);
                            $skeleton = $this->getTextSkeleton($servicios, strtoupper($lang));
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
                                $val['id']
                            );
                            $encuesta = $this->getPoll(
                                $valor['nroref'],
                                $ciudadesPool,
                                $lang,
                                $getLogo['logoFile'],
                                $val['id']
                            );
                            $dataEmail = array(
                                'file' => $valor['nroref'],
                                'cliente' => ucwords(strtolower($valor['razon'])),
                                'paxId' => $val['id'],
                                'paxName' => $paxName,
                                'docItinerario' => $docItinerario,
                                'poll' => $encuesta,
                                'data' => $file
                            );
                            if (!empty($val['email'])) {
                                try {
//                                    $html = (new \App\Mail\MailingMasiReminder('24_horas_antes', $lang, $subject,
//                                        $dataEmail))->render();
//                                    $send = $this->sendMail($html, $val['email'], $subject, ['MASI_MAILING']);
                                    $send = [
                                        'code' => true,
                                        'message-id' => 0,
                                        'message' => 'test-email',
                                    ];
                                    $this->storeLogs($valor['nroref'], 'email', $type, json_encode($send),
                                        'Envio de Email: '.$val['email']);
                                    $status_mail = $send['code'];
                                    $message_id = $send['message-id'];
                                    $message_mail = $send['message'];
                                } catch (\Exception $ex) {
                                    $status_mail = 'error';
                                    $message_mail = $ex->getMessage();
                                    $message_id = '';
                                    $this->storeLogs($valor['nroref'], 'email', $type, json_encode($ex->getMessage()),
                                        'Envio de Email: '.$val['email'], false);
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

                        }
                    } else {
                        $this->storeLogs($valor['nroref'], 'email', $type, null,
                            'No se encontraron servicios', false);
                    }
                } else {
                    $this->storeLogs(trim($valor['nroref']), 'email', $type, null,
                        'No se encontraron emails para los pasajeros', false);
                }
            }
        } else {
            $this->storeLogs(null, 'email', $type, null,
                'No se encontraron files para el dia'.$date, false);
        }

    }

    public function test_mailing_daily($_key, $type, $email = [], $form = [], $isDev = false)
    {
        $clients = $this->getClients($type, $this->client_code);
        $clients_query = $clients->implode('code', "','");

        $date_now = $this->date;
        $date = str_replace('-', '/', $date_now);

        $file = $this->file;


        $params = [
            'type' => $_key,
            'date' => $date,
            'file' => $file,
            'clients_query' => $clients_query,
            'isDev' => $isDev
        ];
        $response = (array)$this->stellaService->masi_mailing_job($params);
        $files = (array)json_decode(json_encode($response['data']), true);

        if (count($files) > 0) {
            $this->storeLogs(null, 'email', $type, $this->encodeArrayWithJsonColumns($files),
                'Busqueda de Files '.$date);
            $_date = explode("/", $date);
            $date = $_date[2].'-'.$_date[1].'-'.$_date[0];

            foreach ($files as $clave => $valor) {
                $valor = (array)$valor;

                $__llegada = Carbon::parse($valor['llegada']);
                $__salida = Carbon::parse($valor['salida']);

                $days_diff = $__llegada->diffInDays($__salida);

                //Verifica si tiene permiso para poder enviar mailing
                $emails = $this->toArray(json_decode($valor['emails_pasajeros']));
                $checkEmails = $this->checkEmailsFile($emails, $isDev);
                $this->storeLogs($valor['nroref'], 'email', $type, json_encode($emails), 'Lista de Pasajeros');
                if ($checkEmails) {
                    $lang = strtolower(trim($valor['idioma']));
                    if (trim($lang) == '' or trim($lang) == null) {
                        $lang = 'en';
                    }

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

                    $langs = ['', 'es', 'en', 'pt', 'it', 'fr'];

                    $params = [
                        'nroref' => $valor['nroref'],
                        'lang' => array_search($lang, $langs)
                    ];

                    $response = (array)$this->stellaService->masi_services_file($params);
                    $destinos = (array)json_decode(json_encode($response['data']), true);

                    if (count($destinos) > 0) {
                        $this->storeLogs($valor['nroref'], 'email', $type,
                            $this->encodeArrayWithJsonColumns($destinos, 'services_pax'), 'Servicios del file');
                        $params = [
                            'nroref' => $valor['nroref'],
                            'cliente' => $valor['cliente'],
                            'fecha' => date("d/m/Y"),
                            'hora' => date("H:i"),
                            'tiplog' => 'E'
                        ];
//                        $this->stellaService->masi_store($params);
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
                            ).' - '.$valor['nroref'];

                        $hoteles = (array)json_decode($valor['hoteles'], true);
                        foreach ($emails as $k => $val) {
                            //Obtengo las ciudades, hoteles, vuelos
                            $idPax = $val['id'];
                            $ciudades = $this->getServicesByPax($destinos, $idPax);
                            $vuelos = (array)json_decode($valor['vuelos'], true);
                            //--------
                            $servicios = $this->searchServicesByDate($ciudades, $date);

                            if (count($servicios) == 0) {
                                $this->storeLogs($valor['nroref'], 'email', $type, json_encode($val),
                                    'El pasajero no cuenta con servicios asignados');
                                $notFoundServices = true;
                                continue;
                            }

                            $hotel = $this->getHotel($hoteles, $date);
                            $vuelo = $this->getVuelo($vuelos, $date);
                            $skeleton = $this->getTextSkeleton($servicios, strtoupper($lang));
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
                                $val['id']
                            );
                            $encuesta = $this->getPoll(
                                $valor['nroref'],
                                $ciudadesPool,
                                $lang,
                                $getLogo['logoFile'],
                                $val['id']
                            );
                            $dataEmail = array(
                                'file' => $valor['nroref'],
                                'cliente' => ucwords(strtolower($valor['razon'])),
                                'paxId' => $val['id'],
                                'paxName' => $paxName,
                                'docItinerario' => $docItinerario,
                                'poll' => $encuesta,
                                'data' => $file,
                                'days_diff' => $days_diff
                            );
                            if (!empty($val['email'])) {
                                try {
                                    $html = (new \App\Mail\MailingMasiReminder('dia_a_dia', $lang, $subject,
                                        $dataEmail))->render();
                                    $send = $this->sendMail($html, $val['email'], $subject, ['MASI_MAILING']);
//                                    $send = [
//                                        'code' => true,
//                                        'message-id' => 0,
//                                        'message' => 'test',
//                                    ];
                                    $this->storeLogs($valor['nroref'], 'email', $type, json_encode($send),
                                        'Envio de Email: '.$val['email']);
                                    $status_mail = $send['code'];
                                    $message_id = $send['message-id'];
                                    $message_mail = $send['message'];
                                } catch (\Exception $ex) {
                                    $this->storeLogs($valor['nroref'], 'email', $type, json_encode($ex->getMessage()),
                                        'Envio de Email: '.$val['email'], false);
                                    $status_mail = 'error';
                                    $message_mail = $ex->getMessage();
                                    $message_id = '';
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

                        }
                    } else {
                        $this->storeLogs($valor['nroref'], 'email', $type, null,
                            'No se encontraron servicios', false);
                    }
                } else {
                    $this->storeLogs(trim($valor['nroref']), 'email', $type, null,
                        'No se encontraron emails para los pasajeros', false);
                }
            }
        } else {
            $this->storeLogs(null, 'email', $type, null,
                'No se encontraron files para el dia'.$date, false);
        }

    }

    public function test_mailing_survey($_key, $type, $email = [], $form = [], $isDev = false)
    {
        $clients = $this->getClients($type, $this->client_code);
        $clients_query = $clients->implode('code', "','");

        $date_now = $this->date;
        $date = str_replace('-', '/', $date_now);

        $file = $this->file;

        $params = [
            'type' => $_key,
            'date' => $date,
            'file' => $file,
            'clients_query' => $clients_query,
            'isDev' => $isDev
        ];

        $response = (array)$this->stellaService->masi_mailing_job($params);
        $files = (array)json_decode(json_encode($response['data']), true);
        if (count($files) > 0) {
            $this->storeLogs(null, 'email', $type, $this->encodeArrayWithJsonColumns($files),
                'Busqueda de Files '.$date);
            $_date = explode("/", $date);
            $date = $_date[2].'-'.$_date[1].'-'.$_date[0];

            foreach ($files as $clave => $valor) {
                $valor = (array)$valor;

                //Verifica si tiene permiso para poder enviar mailing
                $emails = $this->toArray(json_decode($valor['emails_pasajeros']));
                $checkEmails = $this->checkEmailsFile($emails, $isDev);
                $this->storeLogs($valor['nroref'], 'email', $type, json_encode($emails), 'Lista de Pasajeros');
                if ($checkEmails) {
                    $lang = strtolower(trim($valor['idioma']));
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

                    $langs = ['', 'es', 'en', 'pt', 'it', 'fr'];

                    $params = [
                        'nroref' => $valor['nroref'],
                        'lang' => array_search($lang, $langs)
                    ];

                    $response = (array)$this->stellaService->masi_services_file($params);
                    $destinos = (array)json_decode(json_encode($response['data']), true);
                    if (count($destinos) > 0) {
                        $this->storeLogs($valor['nroref'], 'email', $type,
                            $this->encodeArrayWithJsonColumns($destinos, 'services_pax'), 'Servicios del file');
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
                            ).' - '.$valor['nroref'];

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
                                $val['id']
                            );
                            $encuesta = $this->getPoll(
                                $valor['nroref'],
                                $ciudadesPool,
                                $lang,
                                $getLogo['logoFile'],
                                $val['id']
                            );
                            $certificate = $this->checkPaxCertificationFile($valor['nroref'], $val['id']);
                            $dataEmail = array(
                                'file' => $valor['nroref'],
                                'cliente' => ucwords(strtolower($valor['razon'])),
                                'paxId' => $val['id'],
                                'paxName' => $paxName,
                                'docItinerario' => $docItinerario,
                                'certification_carbon' => $certificate,
                                'poll' => $encuesta,
                                'data' => $file
                            );
                            if (!empty($val['email'])) {
                                try {
//                                    $html = (new \App\Mail\MailingMasiReminder('bye', $lang, $subject,
//                                        $dataEmail))->render();
//                                    $send = $this->sendMail($html, $val['email'], $subject, ['MASI_MAILING']);
                                    $send = [
                                        'code' => true,
                                        'message-id' => 0,
                                        'message' => 'test',
                                    ];
                                    $this->storeLogs($valor['nroref'], 'email', $type, json_encode($send),
                                        'Envio de Email: '.$val['email']);
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

                        }
                    } else {
                        $this->storeLogs($valor['nroref'], 'email', $type, null,
                            'No se encontraron servicios', false);
                    }
                } else {
                    $this->storeLogs(trim($valor['nroref']), 'email', $type, null,
                        'No se encontraron emails para los pasajeros', false);
                }
            }
        } else {
            $this->storeLogs(null, 'email', $type, null,
                'No se encontraron files para el dia'.$date, false);
        }
    }

    public function build_message_wsp($tipo, $data, $name_pax, $lang, $days_diff = 0)
    {
        \App::setLocale($lang);

        $enlaces_bio = [
            'es' => 'https://drive.google.com/file/d/1woXlswNjULFsYws_An4eEq2IR0vB4qwD/view?usp=sharing',
            'en' => 'https://drive.google.com/file/d/10YueILA5WclayjNHXpUBL-B3y_2MBBC4/view?usp=sharing',
            'pt' => 'https://drive.google.com/file/d/1tZcBoZtSNA4jE_r_sT5Olu02fQjyYw9O/view?usp=sharing',
            'it' => 'https://drive.google.com/file/d/10YueILA5WclayjNHXpUBL-B3y_2MBBC4/view?usp=sharing',
        ];

        $enlaces_info = [
            'es' => 'https://drive.google.com/file/d/1JJq-rsODtGJEj28drHDfSvcCjC2NYCVG/view?usp=sharing',
            'en' => 'https://drive.google.com/file/d/1HJODPl9rFJ4Dbbb5oHV3cOlFnWgCaa5M/view?usp=sharing',
            'pt' => 'https://drive.google.com/file/d/1YhrVBdG5Qt34CmA0gM4U9_hTZFIoKqGQ/view?usp=sharing',
            'it' => 'https://drive.google.com/file/d/1HJODPl9rFJ4Dbbb5oHV3cOlFnWgCaa5M/view?usp=sharing',
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

        $message = '';
        $message .= ''."\r\n";
        $message .= __('masi.text_hola').' '.trim($name_pax).', 😁';
        $message .= ''."\r\n";
        $message .= ''."\r\n";
        if ($tipo == 3) {
            $message .= __('masi.a_continuacion').' '.$data['file']['dia']; //.' '.utf8_decode("\xF0\x9F\x98\xA1");
            $message .= ''."\r\n";
            $message .= ''."\r\n";
            if (count($data['file']['skeleton']) > 0) {
                for ($i = 0; $i < count($data['file']['skeleton']); $i++) {
                    $message .= '*'.' '.trim($data['file']['skeleton'][$i]['horin']).' '.trim($data['file']['skeleton'][$i]['descrip']).'.'."\r\n";
                }
            }
            $message .= ''."\r\n";
            $message .= __('masi.services_shared_text')."\r\n";
            $message .= ''."\r\n";
            if ($data['file']['hotel'] !== '') {
                $message .= '*'.' '.$data['file']['hotel']."\r\n";
            }
            $message .= ''."\r\n";
            $message .= __('masi.para_descargar_tu').' '.__('masi.itinerario_completo').' '.__('masi.haz_click_en_el_siguiente_enlace').' '.$data['file']['itinerary']."\r\n";
            $message .= ''."\r\n";
            $message .= __('masi.si_requierees_otro_tipo').' '.__('masi.text_web')."\r\n";
            $message .= ''."\r\n";
            $message .= __('masi.esperemos_que_tengas_un_gran_dia')."\r\n";
        }

        if ($tipo == 1) {
            $message .= __('masi.texto_quedan_7_dias').' 🥳';
            $message .= ''."\r\n";
            $message .= ''."\r\n";
            $message .= __('masi.texto_te_damos_bienvenida').__('masi.masi').', '.__('masi.texto_tu_comanero').' '.__('masi.texto_esta_herramienta');
            $message .= ''."\r\n";
            $message .= ''."\r\n";
            $message .= __('masi.texto_aqui_enviamos');
            $message .= ''."\r\n";
            $message .= ''."\r\n";
            $message .= '👉🏼 '.__('masi.tlt_reserva').': '.$data['file']['reserva']['dias'].' '.__('masi.text_dias').' / '.$data['file']['reserva']['noches'].' '.__('masi.text_noches');
            $message .= ''."\r\n";
            $message .= '👉🏼 '.__('masi.tlt_destinos').': '.$data['file']['destinos'];
            $message .= ''."\r\n";
            $message .= '👉🏼 '.__('masi.tlt_llegada').': '.$data['file']['llegada'];
            $message .= ''."\r\n";
            $message .= '👉🏼 '.__('masi.tlt_salida').': '.$data['file']['salida'];
            $message .= ''."\r\n";
            $message .= ''."\r\n";
            $message .= __('masi.texto_preparate');
            $message .= ''."\r\n";
            $message .= '👉🏼 '.__('masi.title_datos_generales_del_viaje').' '.$enlaces_info[$lang];
            $message .= ''."\r\n";
            $message .= '👉🏼 '.__('masi.tit_protocolos').' '.$enlaces_bio[$lang];
            /*
            $message .= ''."\r\n";
            $message .= '👉🏼 ' . __('masi.title_sin_compensacion_tu_huella_de_carbono');
            */
            $message .= ''."\r\n";
            $message .= ''."\r\n";
            $message .= ''.__('masi.texto_estamos_contacto_1').__('masi.texto_estamos_contacto_2').' 😊';
        }

        if ($tipo == 2) {
            $message .= __('masi.bienvenido_masi').' 💻, '.__('masi.texto_tu_comanero').' '.__('masi.texto_esta_herramienta_web').' www.masi.pe';
            $message .= ''."\r\n";
            $message .= ''."\r\n";
            $message .= __('masi.texto_detalle_de_llegada').':';
            $message .= ''."\r\n";

            if ($data['file']['vuelo']['nrovuelo'] != '') {
                $message .= '👉🏼 '.__('masi.texto_vuelo').': '.$data['file']['vuelo']['ciu_desde'].' - '.$data['file']['vuelo']['ciu_hasta'].__('masi.tlt_salida').': '.$data['file']['vuelo']['horain'].' - '.__('masi.tlt_llegada').': '.$data['file']['vuelo']['horaout'];
                $message .= ''."\r\n";

                $message .= '👉🏼 '.__('masi.texto_nrovuelo').': '.$data['file']['vuelo']['nrovuelo'];
                $message .= ''."\r\n";
            }

            if (count($data['file']['skeleton']) > 0) {
                $message .= '👉🏼 '.__('masi.texto_servicio').'s:';
                $message .= ''."\r\n";
                for ($i = 0; $i < count($data['file']['skeleton']); $i++) {
                    $message .= trim($data['file']['skeleton'][$i]['horin']).' - '.trim($data['file']['skeleton'][$i]['descrip']);
                    $message .= ''."\r\n";
                }
            }

            if ($data['file']['hotel'] !== '') {
                $message .= '👉🏼 '.__('masi.texto_hotel').':';
                $message .= ''."\r\n";
                $message .= $data['file']['hotel'];
            }

            $message .= '👉🏼 '.__('masi.texto_pronostico_tiempo').': '.$data['file']['pronostico'][0]['clima']['clima_min'].'°C - '.$data['file']['pronostico'][0]['clima']['clima_max'].'°C';
            $message .= ''."\r\n";
            $message .= '*'.__('masi.services_shared_text');
            $message .= ''."\r\n";
            $message .= ''."\r\n";
            $message .= __('masi.btn_revisa_tu_itineario').' 👉🏼 '.$data['docItinerario'];
            $message .= ''."\r\n";

            if ($data['file']['vuelo']['nrovuelo'] != '' AND ((strtolower($data['file']['vuelo']['ciu_hasta']) == 'cusco') OR strtolower($data['file']['vuelo']['ciu_hasta']) == 'lima')) {
                $message .= __('masi.texto_localiza_nuestro_representantes').' 👉🏼 '.$aeropuerto[strtolower($data['file']['vuelo']['ciu_hasta'])][$lang];
                $message .= ''."\r\n";
            }

            $message .= __('masi.btn_info_de_viaje').' 👉🏼 '.$enlaces_info[$lang];
            $message .= ''."\r\n";
            $message .= __('masi.tit_protocolos').' 👉🏼 '.$enlaces_bio[$lang];
            $message .= ''."\r\n";
            $message .= ''."\r\n";
            $message .= __('masi.tlt_contacto');
            $message .= ''."\r\n";
            $message .= __('masi.text_contacto');
            $message .= ''."\r\n";
            $message .= '🟡 '.__('masi.text_email');
            $message .= ''."\r\n";
            $message .= '🟡 '.__('masi.text_celular');
            $message .= ''."\r\n";
            $message .= '🟡 '.__('masi.text_telefono');
            $message .= ''."\r\n";
            $message .= ''."\r\n";
            $message .= __('masi.texto_nos_vemos_24_horas');
        }

        if ($days_diff >= 0 AND $days_diff <= 3 AND ($tipo == 3 OR $tipo == 5)) {
            $message .= ''."\r\n";
            $message .= ''."\r\n";
            $message .= __('masi.return_home_title')."\r\n";
            $message .= __('masi.return_home_subtitle')."\r\n";
        }

        if ($tipo == 5) {
            $message .= __('masi.gracias_por_tu_visita'); //.' '.$json->decode('"\uD83D\uDE04"');
            $message .= ''."\r\n";
            $message .= ''."\r\n";
            $message .= __('masi.text_apreciaremos_1');
            $message .= ''."\r\n";
            $message .= ''."\r\n";
            $message .= __('masi.ayudanos_completanto').' '.__('masi.breve_encuesta').' '.__('masi.que_no_te_tomara').' '.$data['file']['poll'];
            $message .= ''."\r\n";
            $message .= ''."\r\n";
            $message .= __('masi.muchas_gracias'); //.' '.$json->decode('"\uD83D\uDC50"');
        }


        return $message;
    }

    public function checkPhoneNumbersFile($phoneNumbers, $isDev = false)
    {
        if ($isDev) {
            return true;
        }

        $check = false;
        if (is_array($phoneNumbers) AND count($phoneNumbers) > 0) {
            foreach ($phoneNumbers as $clave => $valor) {
                if (!empty($valor['celular']) AND $valor['celular'] !== '') {
                    $check = true;
                }
            }
        }
        return $check;
    }

    public function wsp_mailing_weekly($key, $type, $phone_number = [], $form = [], $isDev = false)
    {
        $notFoundServices = false;
        $date_now = $this->date;
        $date_future = strtotime('+7 day', strtotime($date_now));
        $date_future = date('d-m-Y', $date_future);
        $date = str_replace('-', '/', $date_future);

        $file = $this->file;


        $params = [
            'type' => $key,
            'date' => $date,
            'file' => $file,
            'isDev' => $isDev
        ];

        $response = $this->toArray($this->stellaService->masi_find_mailing($params));
        $files = $response['data'];


        if (count($files) > 0) {
            $this->storeLogs(null, 'whatsapp', $type, $this->encodeArrayWithJsonColumns($files),
                'Busqueda de Files '.$date);
            $_date = explode("/", $date);
            $date = $_date[2].'-'.$_date[1].'-'.$_date[0];

            foreach ($files as $clave => $valor) {

                $__llegada = Carbon::parse($valor['llegada']);
                $__salida = Carbon::parse($valor['salida']);

                $days_diff = $__llegada->diffInDays($__salida);

                //Verifica si tiene permiso para poder enviar mailing
                $phone_numbers = $this->toArray(json_decode($valor['emails_pasajeros']));
                $checkPhoneNumbers = $this->checkPhoneNumbersFile($phone_numbers, $isDev);
                $this->storeLogs($valor['nroref'], 'whatsapp', $type, json_encode($phone_numbers),
                    'Lista de Pasajeros');
                if ($checkPhoneNumbers) {
                    $lang = strtolower(trim($valor['idioma']));
                    $langs = ['', 'es', 'en', 'pt', 'it', 'fr'];
                    $params = [
                        'nroref' => $valor['nroref'],
                        'lang' => array_search($lang, $langs)
                    ];

                    $response = $this->toArray($this->stellaService->masi_services_file($params));
                    $destinos = (array)json_decode(json_encode($response['data']), true);

                    if (count($destinos) > 0) {
                        $this->storeLogs($valor['nroref'], 'whatsapp', $type,
                            $this->encodeArrayWithJsonColumns($destinos, 'services_pax'), 'Servicios del file');
                        //Guardo Log
                        $params = [
                            'nroref' => $valor['nroref'],
                            'cliente' => $valor['cliente'],
                            'fecha' => date("d/m/Y"),
                            'hora' => date("H:i"),
                            'tiplog' => 'W'
                        ];
                        $this->stellaService->masi_store($params);
                        $getLogo = $this->getLogo($valor['authlogo'], $valor['logo'], trim($valor['cliente']));
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
                            $nameExl = explode(',', $val['nombre']);
                            $paxName = (count($nameExl) > 1) ? ucwords(strtolower($nameExl[1])) : ucwords(strtolower($nameExl[0]));
                            $docItinerario = $this->getItinerary(
                                $valor['nroref'],
                                $lang,
                                $getLogo['logoFile'],
                                $val['id']
                            );

                            $encuesta = $this->getPoll(
                                $valor['nroref'],
                                $ciudadesPool,
                                $lang,
                                $getLogo['logoFile'],
                                $val['id']
                            );

                            $certificate = $this->checkPaxCertificationFile($valor['nroref'], $val['id']);

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

                            if (!empty($number)) {
                                $send = $this->sendWhatsApp($number, $templateMessageWhatsApp);
                                $this->storeLogs($valor['nroref'], 'whatsapp', $type, json_encode($send),
                                    'Envio de WhatsApp: '.$val['celular']);
                                $inserts[] = array(
                                    'email' => $number,
                                    'nombre' => $val['nombre'],
                                    'status' => $send['sent'],
                                    'message' => $send['message'],
                                    'dia' => $dia,
                                    'message_id' => $send['id'],
                                );
                            }
                        }

                        if ($notFoundServices == false) {
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
                        $this->storeLogs($valor['nroref'], 'whatsapp', $type, null,
                            'No se encontraron servicios', false);
                    }
                } else {
                    $this->storeLogs(trim($valor['nroref']), 'whatsapp', $type, null,
                        'No se encontraron numeros telefonicos para los pasajeros');
                }
            }
        } else {
            $this->storeLogs(null, 'whatsapp', $type, null, 'No existen files para el dia '.$date);

        }

        return $response;
    }

    public function wsp_mailing_day_before($key, $type, $phone_number = [], $form = [], $isDev = false)
    {
        $notFoundServices = false;
        $date_now = $this->date;
        $date = str_replace('-', '/', $date_now);

        $file = $this->file;

        $params = [
            'type' => $key,
            'date' => $date,
            'file' => $file,
            'isDev' => $isDev
        ];


        $response = $this->toArray($this->stellaService->masi_find_mailing($params));
        $files = $response['data'];

        if (count($files) > 0) {
            $this->storeLogs(null, 'whatsapp', $type, $this->encodeArrayWithJsonColumns($files),
                'Busqueda de Files '.$date);
            $_date = explode("/", $date);
            $date = $_date[2].'-'.$_date[1].'-'.$_date[0];

            foreach ($files as $clave => $valor) {

                $__llegada = Carbon::parse($valor['llegada']);
                $__salida = Carbon::parse($valor['salida']);

                $days_diff = $__llegada->diffInDays($__salida);

                //Verifica si tiene permiso para poder enviar mailing
                $phone_numbers = $this->toArray(json_decode($valor['emails_pasajeros']));
                $checkPhoneNumbers = $this->checkPhoneNumbersFile($phone_numbers, $isDev);
                $this->storeLogs($valor['nroref'], 'whatsapp', $type, json_encode($phone_numbers),
                    'Lista de Pasajeros');
                if ($checkPhoneNumbers) {
                    $lang = strtolower(trim($valor['idioma']));
                    $langs = ['', 'es', 'en', 'pt', 'it', 'fr'];
                    $params = [
                        'nroref' => $valor['nroref'],
                        'lang' => array_search($lang, $langs)
                    ];

                    $response = $this->toArray($this->stellaService->masi_services_file($params));
                    $destinos = (array)json_decode(json_encode($response['data']), true);
                    if (count($destinos) > 0) {
                        $this->storeLogs($valor['nroref'], 'whatsapp', $type,
                            $this->encodeArrayWithJsonColumns($destinos, 'services_pax'), 'Servicios del file');
                        //Guardo Log
                        $params = [
                            'nroref' => $valor['nroref'],
                            'cliente' => $valor['cliente'],
                            'fecha' => date("d/m/Y"),
                            'hora' => date("H:i"),
                            'tiplog' => 'W'
                        ];
                        $this->stellaService->masi_store($params);
                        $getLogo = $this->getLogo($valor['authlogo'], trim($valor['cliente']));


                        $inserts = array();
                        if ($isDev) {
                            $phone_numbers = $phone_number;
                        }

                        $hoteles = (array)json_decode($valor['hoteles']);
                        $color = '';
                        $linkAero = '';
                        $linkIcons = '';

                        foreach ($phone_numbers as $k => $val) {
                            //Obtengo las ciudades, hoteles, vuelos
                            $idPax = $val['id'];
                            $ciudades = $this->getServicesByPax($destinos, $idPax);
                            $vuelos = (array)json_decode($valor['vuelos']);
                            //--------
                            $servicios = $this->searchServicesByDate($ciudades, $date);
                            $hotel = $this->getHotel($hoteles, $date);
                            $vuelo = $this->getVuelo($vuelos, $date);
                            $skeleton = $this->getTextSkeleton($servicios, strtoupper($lang));
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
                                $val['id']
                            );
                            $encuesta = $this->getPoll(
                                $valor['nroref'],
                                $ciudadesPool,
                                $lang,
                                $getLogo['logoFile'],
                                $val['id']
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
                            if (!empty($number)) {
                                $send = $this->sendWhatsApp($number, $templateMessageWhatsApp);
                                $this->storeLogs($valor['nroref'], 'whatsapp', $type, json_encode($send),
                                    'Envio de WhatsApp: '.$val['celular']);
                                $inserts[] = array(
                                    'email' => $number,
                                    'nombre' => $val['nombre'],
                                    'status' => $send['sent'],
                                    'message' => $send['message'],
                                    'dia' => $dia,
                                    'message_id' => $send['id'],
                                );
                            }

                        }

                    } else {
                        $this->storeLogs($valor['nroref'], 'whatsapp', $type, null,
                            'No se encontraron servicios', false);
                    }
                } else {
                    $this->storeLogs(trim($valor['nroref']), 'whatsapp', $type, null,
                        'No se encontraron numeros telefonicos para los pasajeros');
                }
            }
        } else {
            $this->storeLogs(null, 'whatsapp', $type, null, 'No existen files para el dia '.$date);
        }
    }

    public function wsp_mailing_daily($key, $type, $phone_number = [], $form = [], $isDev = false)
    {
        $notFoundServices = false;
        $date_now = $this->date;
        $date = str_replace('-', '/', $date_now);

        $file = $this->file;

        $params = [
            'type' => $key,
            'date' => $date,
            'file' => $file,
            'isDev' => $isDev
        ];


        $response = $this->toArray($this->stellaService->masi_find_mailing($params));
        $files = $response['data'];

        if (count($files) > 0) {
            $this->storeLogs(null, 'whatsapp', $type, $this->encodeArrayWithJsonColumns($files),
                'Busqueda de Files '.$date);
            $_date = explode("/", $date);
            $date = $_date[2].'-'.$_date[1].'-'.$_date[0];

            foreach ($files as $clave => $valor) {

                $__llegada = Carbon::parse($valor['llegada']);
                $__salida = Carbon::parse($valor['salida']);

                $days_diff = $__llegada->diffInDays($__salida);

                //Verifica si tiene permiso para poder enviar mailing
                $phone_numbers = $this->toArray(json_decode($valor['emails_pasajeros']));
                $checkPhoneNumbers = $this->checkPhoneNumbersFile($phone_numbers, $isDev);
                $this->storeLogs($valor['nroref'], 'whatsapp', $type, json_encode($phone_numbers),
                    'Lista de Pasajeros');
                if ($checkPhoneNumbers) {
                    $lang = strtolower(trim($valor['idioma']));
                    $langs = ['', 'es', 'en', 'pt', 'it', 'fr'];
                    $params = [
                        'nroref' => $valor['nroref'],
                        'lang' => array_search($lang, $langs)
                    ];

                    $response = $this->toArray($this->stellaService->masi_services_file($params));
                    $destinos = (array)json_decode(json_encode($response['data']), true);
                    if (count($destinos) > 0) {
                        $this->storeLogs($valor['nroref'], 'whatsapp', $type,
                            $this->encodeArrayWithJsonColumns($destinos, 'services_pax'), 'Servicios del file');
                        //Guardo Log
                        $params = [
                            'nroref' => $valor['nroref'],
                            'cliente' => $valor['cliente'],
                            'fecha' => date("d/m/Y"),
                            'hora' => date("H:i"),
                            'tiplog' => 'W'
                        ];
                        $getLogo = $this->getLogo($valor['authlogo'], trim($valor['cliente']));
                        $inserts = array();
                        if ($isDev) {
                            $phone_numbers = $phone_number;
                        }
                        $hoteles = $this->toArray(json_decode($valor['hoteles']));
                        foreach ($phone_numbers as $k => $val) {
                            //Obtengo las ciudades, hoteles, vuelos
                            $idPax = $val['id'];
                            $ciudades = $this->getServicesByPax($destinos, $idPax);
                            $vuelos = (array)$this->toArray(json_decode($valor['vuelos']));
                            $servicios = $this->searchServicesByDate($ciudades, $date);
                            if (count($servicios) == 0) {
                                $this->storeLogs($valor['nroref'], 'whatsapp', $type, json_encode($val),
                                    'El pasajero no tiene servicios asignados');
                                continue;
                            }
                            $hotel = $this->getHotel($hoteles, $date);
                            $vuelo = $this->getVuelo($vuelos, $date);
                            $skeleton = $this->getTextSkeleton($servicios, strtoupper($lang));
                            $numeroDia = $this->getNumeroDia($ciudades, $date, $lang);
                            $docItinerario = $this->getItinerary(
                                $valor['nroref'],
                                $lang,
                                $getLogo['logoFile'],
                                $val['id']
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
                            if (!empty($number)) {
                                $send = $this->sendWhatsApp($number, $templateMessageWhatsApp);
                                $this->storeLogs($valor['nroref'], 'whatsapp', $type, json_encode($send),
                                    'Envio de WhatsApp: '.$val['celular']);
                                $inserts[] = array(
                                    'email' => $number,
                                    'nombre' => $val['nombre'],
                                    'status' => $send['sent'],
                                    'message' => $send['message'],
                                    'dia' => $numeroDia['dia'],
                                    'message_id' => $send['id'],
                                );
                            }

                        }
                    } else {
                        $this->storeLogs($valor['nroref'], 'whatsapp', $type, null,
                            'No se encontraron servicios', false);
                    }
                } else {
                    $this->storeLogs(trim($valor['nroref']), 'whatsapp', $type, null,
                        'No se encontraron numeros telefonicos para los pasajeros');
                }
            }
        } else {
            $this->storeLogs(null, 'whatsapp', $type, null, 'No existen files para el dia '.$date);
        }

        return $response;
    }

    public function wsp_mailing_survey($key, $type, $phone_number = [], $form = [], $isDev = false)
    {
        $date_now = $this->date;
        $date = str_replace('-', '/', $date_now);

        $file = $this->file;


        $params = [
            'type' => $key,
            'date' => $date,
            'file' => $file,
            'isDev' => $isDev
        ];

        $response = $this->toArray($this->stellaService->masi_find_mailing($params));
        $files = $response['data'];
        if (count($files) > 0) {
            $this->storeLogs(null, 'whatsapp', $type, $this->encodeArrayWithJsonColumns($files),
                'Busqueda de Files '.$date);
            $_date = explode("/", $date);
            $date = $_date[2].'-'.$_date[1].'-'.$_date[0];

            foreach ($files as $clave => $valor) {
                //Verifica si tiene permiso para poder enviar mailing
                $phone_numbers = $this->toArray(json_decode($valor['emails_pasajeros']));
                $checkPhoneNumbers = $this->checkPhoneNumbersFile($phone_numbers, $isDev);
                $this->storeLogs($valor['nroref'], 'whatsapp', $type, json_encode($phone_numbers),
                    'Lista de Pasajeros');
                if ($checkPhoneNumbers) {
                    $lang = strtolower(trim($valor['idioma']));
                    $langs = ['', 'es', 'en', 'pt', 'it', 'fr'];
                    $params = [
                        'nroref' => $valor['nroref'],
                        'lang' => array_search($lang, $langs)
                    ];
                    $response = $this->toArray($this->stellaService->masi_services_file($params));
                    $destinos = (array)json_decode(json_encode($response['data']), true);
                    if (count($destinos) > 0) {
                        $this->storeLogs($valor['nroref'], 'whatsapp', $type,
                            $this->encodeArrayWithJsonColumns($destinos, 'services_pax'), 'Servicios del file');
                        $params = [
                            'nroref' => $valor['nroref'],
                            'cliente' => $valor['cliente'],
                            'fecha' => date("d/m/Y"),
                            'hora' => date("H:i"),
                            'tiplog' => 'W'
                        ];
                        $this->stellaService->masi_store($params);
                        $getLogo = $this->getLogo($valor['authlogo'], trim($valor['cliente']));
                        $dia = 0;
                        $inserts = array();
                        if ($isDev) {
                            $phone_numbers = $phone_number;
                        }
                        foreach ($phone_numbers as $k => $val) {
                            //Obtengo las ciudades, hoteles, vuelos
                            $idPax = $val['id'];
                            $ciudades = $this->getServicesByPax($destinos, $idPax);
                            $ciudadesPool = $this->getInfoDestinos($ciudades, 2, $lang);
                            //Envio de Mail a los Pasajeros
                            $encuesta = $this->getPoll(
                                $valor['nroref'],
                                $ciudadesPool,
                                $lang,
                                $getLogo['logoFile'],
                                $val['id']
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
                            if (!empty($number)) {
                                $send = $this->sendWhatsApp($number, $templateMessageWhatsApp);
                                $this->storeLogs($valor['nroref'], 'whatsapp', $type, json_encode($send),
                                    'Envio de WhatsApp: '.$val['celular']);
                                $inserts[] = array(
                                    'email' => $val['email'],
                                    'nombre' => $val['nombre'],
                                    'status' => $send['sent'],
                                    'message' => $send['message'],
                                    'dia' => $dia,
                                    'message_id' => $send['id'],
                                );
                            }


                        }
                    } else {
                        $this->storeLogs($valor['nroref'], 'whatsapp', $type, null,
                            'No se encontraron servicios', false);
                    }
                } else {
                    $this->storeLogs(trim($valor['nroref']), 'whatsapp', $type, null,
                        'No se encontraron numeros telefonicos para los pasajeros');
                }
            }
        } else {
            $this->storeLogs(null, 'whatsapp', $type, null, 'No existen files para el dia '.$date);
        }
    }

    public function getClients($type, $client_code)
    {
        $clients = Client::select(
            'clients.name',
            'clients.code',
            'clients.logo'
        )->status(1)
            ->leftjoin('client_mailing', 'clients.id', '=', 'client_mailing.clients_id')
            ->where('client_mailing.status', 1)
            ->where('client_mailing.'.$type, 1);

        if (!empty($this->client_code)) {
            $clients = $clients->where('clients.code', $client_code);
        }

        $clients = $clients->get();

        $clients = $clients->transform(function ($item) {
            $logo_explode = explode("/", $item['logo']);
            $item['logo'] = end($logo_explode);
            return $item;
        });

        return $clients;
    }

    public function storeLogs($file, $type_send, $type_message, $data, $message, $status_validation = true)
    {
        $newLog = new MasiActivityJobLogs();
        $newLog->file = $file;
        $newLog->type_send = $type_send;
        $newLog->type_message = $type_message;
        $newLog->data = $data;
        $newLog->message = $message;
        $newLog->status_validation = $status_validation;
        $newLog->save();
    }

    public function encodeArrayWithJsonColumns($data, $source = 'files')
    {
        foreach ($data as $key => $value) {
            if ($source === 'files') {
                $data[$key]['vuelos'] = json_decode($value['vuelos']);
                $data[$key]['emails_pasajeros'] = json_decode($value['emails_pasajeros']);
                $data[$key]['hoteles'] = json_decode($value['hoteles']);
            }

            if ($source === 'services_pax') {
                $data[$key]['paxs_service'] = json_decode($value['paxs_service']);
            }
        }

        return json_encode($data);
    }
}
