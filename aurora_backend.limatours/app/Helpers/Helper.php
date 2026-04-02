<?php
/**
 * Created by PhpStorm.
 * User: Jose
 * Date: 12/08/2019
 * Time: 10:43
 */


use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

if (!function_exists('addDateDay')) {
    /**
     * @param \DateTime $DateTime
     * @param int $amount
     * @return \DateTime|string
     */
    function addDateDay(\DateTime $DateTime, int $amount = 1, $format = null)
    {
        $date = clone $DateTime;

        if ($format != null) {
            return $date->modify('+' . $amount . ' day')->format($format);
        } else {
            return $date->modify('+' . $amount . ' day');
        }
    }
}

if (!function_exists('subDateDays')) {
    /**
     * @param Carbon $date
     * @param int $amount
     * @return \DateTime
     */
    function subDateDays(Carbon $date, int $amount)
    {
        return (clone $date)->subDays($amount);
    }
}

if (!function_exists('difDateDays')) {
    /**
     * @param Carbon $date1
     * @param Carbon $date2
     * @return string
     */
    function difDateDays(Carbon $date1, Carbon $date2)
    {
        return (clone $date1)->diff($date2)->format('%a');
    }
}

if (!function_exists('currentDate')) {
    /**
     * @return Carbon
     */
    function currentDate()
    {
        return Carbon::now('America/Lima')->startOfDay();
    }
}

if (!function_exists('createFileCode')) {
    /**
     * @param $countryIso
     * @return string
     */
    function createFileCode($countryIso)
    {
        return strtoupper($countryIso . '-' . substr(md5(uniqid(rand(), true)), 0, 6));
    }
}

if (!function_exists('priceRound')) {
    /**
     * @param float $value
     * @param int $decimals
     * @return float
     */
    function priceRound(float $value, int $decimals = 2)
    {
        return number_format(round($value, $decimals), $decimals);
    }
}

if (!function_exists('addMarkUp')) {
    /**
     * @param float $price
     * @param float $percent
     * @return float
     */
    function addMarkUp(float $price, float $percent)
    {
        return $price + ($price * ($percent / 100));
    }
}
if (!function_exists('pricePercent')) {
    /**
     * @param float $price
     * @param float $percent
     * @return float
     */
    function pricePercent(float $price, float $percent)
    {
        return ($price * ($percent / 100));
    }
}
if (!function_exists('convertDate')) {
    /**
     * @param $_date
     * @param $char_from
     * @param $char_to
     * @param boolean $orientation
     * @return string
     */
    function convertDate($_date, $char_from, $char_to, $orientation)
    {
        $explode = explode($char_from, $_date);

        if(count($explode) > 1)
        {
            $response =
            ($orientation)
                ? $explode[2] . $char_to . $explode[1] . $char_to . $explode[0]
                : $explode[0] . $char_to . $explode[1] . $char_to . $explode[2];
        }
        else
        {
            $response = $explode[0];
        }

        return $response;
    }
}

if (!function_exists('convertDateTime')) {
    /**
     * @param $_datetime "2022-02-15T20:56:57.000000Z" || "2022-02-15 20:56"
     * @param $with_hour boolean
     * @return string "15 - Febrero - 2022 20:56:57"
     */
    function convertDateTime($_datetime, $with_hour)
    {
        $date = Carbon::parse($_datetime);
        $months = array(
            "Ene",
            "Feb",
            "Mar",
            "Abr",
            "May",
            "Jun",
            "Jul",
            "Ago",
            "Sep",
            "Oct",
            "Nov",
            "Dic"
        );
        $month = $months[($date->format('n')) - 1];
        $response = $date->format('d') . ' ' . $month . '. ' . $date->format('Y');
        if ($with_hour) {
            $response .= ' ' . $date->format('H:i');
        }
        return $response;
    }
}

if (!function_exists('ordinalNumber')) {
    /**
     * @param $number | 0 - 1 - 2 - 3 ...
     * @return string | Primer, Tercer, Trigésimo octavo ...
     */
    function ordinalNumber($number)
    {
        $ordinals = array(
            "Primer",
            "Segundo",
            "Tercer",
            "Cuarto",
            "Quinto",
            "Sexto",
            "Séptimo",
            "Octavo",
            "Noveno",
            "Décimo",
            "Undécimo",
            "Duodécimo",
            "Décimo Tercer",
            "Décimo Cuarto",
            "Décimo Quinto",
            "Décimo Sexto",
            "Décimo Séptimo"
        );
        $ordinal_number = ($number > 16) ? "Siguiente" : $ordinals[$number];
        return $ordinal_number;
    }
}

if (!function_exists('auth_user')) {
    /**
     * @return User|null
     */
    function auth_user()
    {
        return Auth::user();
    }
}

if (!function_exists('get_cancelation_penalty')) {
    /**
     * @param $first_penalty_date
     * @param null $policies_cancellation
     * @return array|\Illuminate\Support\Collection
     */
    function get_cancelation_penalty($first_penalty_date, $policies_cancellation = null)
    {
        if (empty($policies_cancellation)) {
            return [];
        }

        $data = collect(json_decode($policies_cancellation, true))->filter(function ($penalty) use ($first_penalty_date
        ) {
            return $penalty['apply_date'] >= $first_penalty_date;
        });

        $data = $data->sortBy('apply_date');

        return $data;
    }
}

if (!function_exists('difDateHours')) {
    /**
     * @param Carbon $date1
     * @param Carbon $date2
     * @return string
     */
    function difDateHours(Carbon $date1, Carbon $date2)
    {
        return (clone $date1)->diffInHours($date2);
    }
}

if (!function_exists('roundLito')) {
    /**
     * Redondeo de precios Lito
     * @param double $num
     * @return string
     */
    function roundLito($num, $module = '')
    {
        $num = number_format($num, 2, '.', '');
        $res = explode('.', $num);
        $nEntero = $res[0];
        $nDecimal = 0;
        if (count($res) > 1) {
            $nDecimal = (int)$res[1];
        }
        //TODO Si el decimal es menor a 0.10 es igual 0
        if ($nDecimal <= 10) {
            $newDecimal = 0;
        } elseif ($nDecimal > 10 && $nDecimal <= 50) { //TODO si es mayor a 0.10 hasta 0.50 que sea 0.5
            $newDecimal = 5;

            if ($module == 'hotel') {
                $nEntero = ((int)$nEntero) + 1;
                $newDecimal = 0;
            }

        } else { //TODO y de 0.51 hasta 0.99 que sea 1 +
            $nEntero = ((int)$nEntero) + 1;
            $newDecimal = 0;
        }
        $numeroRed = $nEntero . '.' . $newDecimal;
        return $numeroRed;
    }
}

if (!function_exists('getBrowserByUserAgent')) {
    function getBrowserByUserAgent($user_agent)
    {
        $bname = 'Unknown';
        $platform = 'Unknown';

        //First get the platform?
        if (preg_match('/linux/i', $user_agent)) {
            $platform = 'Linux';
        } elseif (preg_match('/macintosh|mac os x/i', $user_agent)) {
            $platform = 'Mac';
        } elseif (preg_match('/windows|win32/i', $user_agent)) {
            $platform = 'Windows';
        }


        // Next get the name of the useragent yes seperately and for good reason
        if (preg_match('/MSIE/i', $user_agent) && !preg_match('/Opera/i', $user_agent)) {
            $bname = 'Internet Explorer';
            $ub = "MSIE";
        } elseif (preg_match('/Firefox/i', $user_agent)) {
            $bname = 'Mozilla Firefox';
            $ub = "Firefox";
        } elseif (preg_match('/Chrome/i', $user_agent)) {
            $bname = 'Google Chrome';
            $ub = "Chrome";
        } elseif (preg_match('/Safari/i', $user_agent)) {
            $bname = 'Apple Safari';
            $ub = "Safari";
        } elseif (preg_match('/Opera/i', $user_agent)) {
            $bname = 'Opera';
            $ub = "Opera";
        } elseif (preg_match('/Netscape/i', $user_agent)) {
            $bname = 'Netscape';
            $ub = "Netscape";
        }

        return $platform . ' - ' . $bname;
    }

}

if (!function_exists('verifyCloudinaryImg')) {
    function verifyCloudinaryImg($var, $w = 0, $h = 0, $request = '')
    {
        //https://res-5.cloudinary.com/{litomarketing/litodti}/image/upload/c_thumb,h_80,w_70/v1432940982/peru/amazonas/Tacacho_con_Cecina_024325_300.jpg

        $cloud = extractValueFromUrl($var);

        if ($w === 0) {
            $with = 'c_scale';
        } else {
            $with = 'w_' . $w;
        }

        if ($h === 0) {
            $height = 'c_scale';
        } else {
            $height = 'h_' . $h;
        }

        if ($var == '') {
            if ($request == 'nom') {
                $var = 'Parapente_bthb8r';
            } else { // link
                $var =
                    'https://res-5.cloudinary.com/' . $cloud . '/image/upload/q_100,c_thumb,' . $height
                    . ',' . $with . '/Parapente_bthb8r';
            }
        } else {
            $explode = explode("cloudinary.com", $var);
            if (count($explode) > 1) {
                $img = explode("upload/", $var);
                $img = $img[1];

                $verifyThumb = explode("c_thumb", $img);
                if (count($verifyThumb) > 1) {
                    $img = explode("/", $img);
                    $img = $img[count($img) - 1];
                }

                if ($request == 'nom') {
                    $var = $img;
                } else { // link
                    $var =
                        'https://res-5.cloudinary.com/' . $cloud . '/image/upload/q_100,c_thumb,' . $height
                        . ',' . $with . '/' . trim($img);
                }
            } else {
                $var = "https://backend.limatours.com.pe" . '/images/' . $var;
            }
        }
        return trim(preg_replace('/\s\s+/', ' ', $var));
    }

}

if (!function_exists('htmlDecode')) {
    function htmlDecode($var)
    {
        $text = html_entity_decode(trim($var), ENT_QUOTES, "UTF-8");
        $text = str_replace("\\", '', $text);
//        $text = preg_replace_callback('/&#([0-9a-fx]+);/mi', 'replace_num_entity', $text);
        $text = htmlspecialchars_decode($text);
        return $text;
    }
}

if (!function_exists('sortFunction')) {
    function sortFunction($a, $b)
    {
        return ($a < $b) ? -1 : 1;
    }

}

if (!function_exists('clearSpecialCharacters')) {
    function clearSpecialCharacters($text)
    {
        return preg_replace('([^A-Za-z0-9 ])', '', $text);
    }
}

if (!function_exists('containsWordString')) {
    function containsWordString($text, $word_search)
    {
        if (preg_match('*\b' . preg_quote($word_search) . '\b*i', $text, $matches, PREG_OFFSET_CAPTURE)) {
            return $matches[0][1];
        }
        return -1;

    }
}


if (!function_exists('createUserCode')) {
    /**
     * @param $countryIso string
     * @return string
     */
    function createUserCode($countryIso = null)
    {
        if (empty($countryIso)) {
            return strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
        } else {
            return strtoupper($countryIso . substr(md5(uniqid(rand(), true)), 0, 4));
        }
    }
}

if (!function_exists('validateRuc')) {
    function validateRuc($ruc)
    {
        $error = [];

        //Todo Validacion para solo numeros letras
        if (!preg_match("/^[a-zA-Z0-9\s]+$/", $ruc)) {
            $error[] = 'ERROR_INVALID_CHARACTERS';
        }
        //Todo Validacion para minimo de 6 caracteres
        if (strlen($ruc) < 6) {
            $error[] = 'ERROR_MINIMUM_CHARACTERS';
        }
        //Todo Validacion para maximo de 15 caracteres
        if (strlen($ruc) > 15) {
            $error[] = 'ERROR_MAXIMUM_CHARACTERS';
        }

        //Todo Validacion para no repetir mas de 3 veces concecutivos un caracter (Ejm: xxx => true => xxxx => false)
        if (preg_match('/([a-zA-Z0-9])\1{3,}/', $ruc, $matches)) {
            $error[] = 'ERROR_INVALID_REPEATED_CHARACTERS';
        }

        return [
            'success' => count($error) > 0 ? false : true,
            'errors' => $error
        ];
    }
}


if (!function_exists('createServiceAuroraCode')) {
    /**
     * @param $stateIso
     * @return string
     */
    function createServiceAuroraCode($stateIso)
    {
        return strtoupper($stateIso . '-' . substr(md5(uniqid(rand(), true)), 0, 6));
    }
}

if (!function_exists('clearNameRoom')) {
    /**
     * @param $stateIso
     * @return string
     */
    function clearNameRoom($name)
    {
        $name = str_replace("SGL", "", $name);
        $name = str_replace("DBL", "", $name);
        $name = str_replace("TPL", "", $name);
        $name = str_replace("MAT", "", $name);
        $name = str_replace("MATRIMONIAL", "", $name);
        $name = str_replace("+ CAMA ADICIONAL", "", $name);
        $name = str_replace("+ ADD BED", "", $name);
        $name = str_replace("+ SOFA CAMA", "", $name);
        $name = str_replace("TRP", "", $name);
        $name = str_replace("+ CAMA ADD", "", $name);
        $name = str_replace("SIMPLE", "", $name);
        return $name;
    }
}

if (!function_exists('isImageUrlActive')) {
    function isImageUrlActive($imageUrl)
    {
        // Agregar el protocolo si no está presente
        $url = (strpos($imageUrl, 'http://') === false && strpos($imageUrl,
                'https://') === false) ? 'http://' . $imageUrl : $imageUrl;

        // Intentar obtener el contenido de la URL
        $contents = file_get_contents($url);

        // Comprobar si se obtuvo el contenido correctamente
        return $contents !== false;
    }
}

if (!function_exists('extractValueFromUrl')) {
    function extractValueFromUrl($url)
    {
        // Normalizar la URL para asegurarse de que tenga un esquema
        if (strpos($url, 'http://') !== 0 && strpos($url, 'https://') !== 0) {
            $url = 'http:' . $url;
        } else {
            return 'litomarketing';
        }

        // Parsear la URL para obtener el path
        $parsedUrl = parse_url($url, PHP_URL_PATH);

        // Dividir el path en sus componentes
        $pathComponents = explode('/', $parsedUrl);

        // Obtener el valor específico (en este caso, el tercer componente)
        $value = isset($pathComponents[1]) ? $pathComponents[1] : 'litomarketing';

        // Verificar si el valor es 'litodti' o 'litomarketing'
        if (in_array($value, ['litodti', 'litomarketing'])) {
            return $value;
        } else {
            return 'litomarketing';
        }
    }

}

//if (!function_exists('loginToCognito')) {
//    function loginToCognito($code, $password)
//    {
//        $client = new \GuzzleHttp\Client();
//        $response_sqs = $client->request('POST',
//            config('services.auth_cognito.domain'), [
//                "json" => [
//                    'sync' => false,
//                    'username' => $code,
//                    'password' => $password,
//                ],
//                "headers" => ['Content-Type' => 'application/json']
//            ]);
//        $responseBody = json_decode($response_sqs->getBody()->getContents(), true);
//
//        $token = '';
//        if($responseBody['success']){
//            $token = $responseBody['access_token'];
//        }
//        if (Auth::check() && Auth::user()->token_cognito) {
//            Auth::user()->token_cognito = $token;
//        }
//
//        return $token;
//    }
//
//}



