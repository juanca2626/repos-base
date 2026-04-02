<?php

namespace App\Http\Controllers;

// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
// curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
date_default_timezone_set('America/Lima');

use App\Exports\ReportsExport;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\User;
use App\UserNotification;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Quote;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function toArray($object = [])
    {
        $array = [];

        foreach ($object as $key => $value) {
            if (is_object($value) or is_array($value)) {
                $value = $this->toArray($value);
            } else {
                $value = trim($value);
            }

            $array[$key] = $value;
        }

        return $array;
    }

    public function throwError($ex)
    {
        return response()->json([
            'type' => 'error',
            'message' => $ex->getMessage(),
            'file' => $ex->getFile(),
            'line' => $ex->getLine(),
        ]);
    }

    /**
     * Heredado Auth::user()->can
     * @since 2019
     * @params $slug
     * @author KLuizSv
     */
    public function hasPermission($slug)
    {
        $user = auth()->user();

        Log::info('User: ' ,[
            auth()->user()
        ]);

        if (!$user || !$user->rol) {
            return false;
        }

        $permissions = $user->rol->permissions;

        if (!$permissions) {
            return false;
        }

        $response = false;
        foreach($permissions as $permission)
        {
            if (!$permission || !$permission->permission) {
                continue;
            }

            $_slug = $permission->permission->slug;

            if ($_slug == $slug) {
                $response = true;
                break;
            }
        }

        return $response;
    }

    /**
     * GROUP ARRAY BY
     * @since 2019
     * @params Array, campo agrupación
     * @author KLuizSv
     * @response Array
     */
    public function groupArrayBy($array = [], $field = '')
    {
        $response = [];
        $array = (array) $array;

        foreach ($array as $key => $value) {
            $value = (array) $value;
            $response[trim($value[$field])][] = $value;
        }

        return $response;
    }

    public function orderStats($cotizaciones_totales = [])
    {
        $cantidad_respondidas_a_tiempo = 0;
        $_pedidos = array();
        $_files = array();

        $cantidad_pedidos = 0;
        $cantidad_pedidos_concretados = 0;
        $cantidad_files_concretados = 0;
        $cantidad_cotizaciones = 0;
        $monto_estimado = 0;
        $monto_estimado_concretado = 0;
        $monto_cotizaciones = 0;
        $_monto_estimado = array();
        $cantidad_cotis_aurora = 0;
        $cantidad_cotis_stela = 0;
        $ratio_trabajo = 0;
        $ratio_trabajo_recotizacion = 0;

        $cotizaciones_realizadas = array();

        foreach ($cotizaciones_totales as $key => $value) {
            $value = (array) $value;

            $_key = trim($value['nroped']) . '_' . trim($value['nroord']) . '_' . trim($value['nroref']) . '_' . trim($value['nrofile']);

            if (!(key_exists($_key, $cotizaciones_realizadas))) {
                $cotizaciones_realizadas[$_key] = $value;
            }

            if (!in_array(trim($value['nroped']), $_pedidos)) {
                $_pedidos[] = trim($value['nroped']);
                $cantidad_pedidos += 1;
            }
        }

        foreach ($cotizaciones_realizadas as $key => $value) {
            if (($value['nroref'] != '' and $value['nroref'] != NULL) or $value['chkpro'] > 0) {
                if ($value['nroref'][0] == '2') // Stela
                {
                    $cantidad_cotis_stela += 1;
                }

                if ($value['nroref'][0] == '1') // Aurora
                {
                    $cantidad_cotis_aurora += 1;
                }

                $_codsec = (int) trim($value['codsec']);
                $codsec = (strlen($_codsec) > 1) ? $_codsec[2] : $_codsec;

                if (
                    $this->getDayWeek($cotizaciones_realizadas[$key]['fecrec']) == 'VIERNE' or
                    $this->getDayWeek($cotizaciones_realizadas[$key]['fecrec']) == 'SABADO' or
                    $this->getDayWeek($cotizaciones_realizadas[$key]['fecrec']) == 'DOMING'
                ) {
                    $dia = $this->getDayWeek($cotizaciones_realizadas[$key]['fecrec']);
                    if ($cotizaciones_realizadas[$key]['fecrec'] == $cotizaciones_realizadas[$key]['fecres']) {
                        $fecrec = trim($cotizaciones_realizadas[$key]['fecrec']) . ' ' . trim($cotizaciones_realizadas[$key]['horrec']);
                    } else {
                        if ($dia == 'VIERNE') {
                            $fecha = $this->addToDate('+3 day', trim($cotizaciones_realizadas[$key]['fecrec']));
                        } elseif ($dia == 'SABADO') {
                            $fecha = $this->addToDate('+2 day', trim($cotizaciones_realizadas[$key]['fecrec']));
                        } elseif ($dia == 'DOMING') {
                            $fecha = $this->addToDate('+1 day', trim($cotizaciones_realizadas[$key]['fecrec']));
                        }
                        $fecrec = trim($fecha) . ' ' . trim($cotizaciones_realizadas[$key]['horrec']); // a partir del dia lunes a la misma hora de recepcion se empezara a contar los dias
                    }
                } else {
                    $fecrec = trim($cotizaciones_realizadas[$key]['fecrec']) . ' ' . trim($cotizaciones_realizadas[$key]['horrec']);
                }

                if (trim($cotizaciones_realizadas[$key]['fecres']) == '') {
                    $fecres = date("Y-m-d H:i:s");
                } else {
                    $fecres = trim($cotizaciones_realizadas[$key]['fecres']) . ' ' . trim($cotizaciones_realizadas[$key]['horres']);
                }

                $dias = $this->checkDateFormat($fecrec, $fecres);
                $cotizaciones_realizadas[$key]['horas'] = ($dias['hour'] > 0) ? $dias['hour'] : 0;
                $cotizaciones_realizadas[$key]['dias'] = ($dias['day'] > 0) ? $dias['day'] : 0;

                // Cambios en la validación del tiempo..
                $_times = array(0, 12, 72, 0, 48, 120); // Tiempo por sectores..
                // $_times = array(0, 24, 120, 0, 48, 120); // Tiempo por sectores..
                //
                $limite = $_times[$codsec];
                $horas = $cotizaciones_realizadas[$key]['horas'];

                if ($limite > 0) {
                    if ($horas <= $limite) {
                        $cantidad_respondidas_a_tiempo += 1;
                    }
                } else {
                    // $cantidad_respondidas_a_tiempo += 1;
                }

                $cantidad_cotizaciones += 1;

                $monto_cotizaciones += (float) $value['price_estimated'];
                $_monto_estimado[$value['nroped']][] = (float) $value['price_estimated'];
            }

            if ($value['nrofile'] != '' and $value['nrofile'] != NULL) {
                if (!in_array($value['nrofile'], $_files)) {
                    $_files[] = $value['nrofile'];

                    $cantidad_pedidos_concretados += 1;
                    $cantidad_files_concretados += 1;
                    $monto_estimado_concretado += (float) $value['price_end'];
                }
            }

            /*
            else
            {
                if((double) $value['PRICE_END'] > 0)
                {
                    $cantidad_pedidos_concretados += 1;
                    $cantidad_files_concretados += 1;
                    $monto_estimado_concretado += (double) $value['PRICE_END'];
                }
            }
            */

            /*
            if($value['NROFILE'] != '' AND $value['NROFILE'] != NULL)
            {
                if(!in_array($value['NROFILE'], $_files))
                {
                    $_files[] = $value['NROFILE'];

                }
            }
            */
        }

        $porcentaje = 0;

        if ($cantidad_cotizaciones > 0) {
            $porcentaje = number_format(($cantidad_respondidas_a_tiempo * 100 / $cantidad_cotizaciones), 0, ".", ",");
        }

        foreach ($_monto_estimado as $key => $value) {
            $subtotal = 0;

            foreach ($value as $k => $v) {
                $subtotal += $v;
            }

            $monto_estimado += ($subtotal / count($value));
        }

        // Pedidos Recibidos..
        $monto_estimado = number_format($monto_estimado, 2);

        $porcentaje_concrecion = 0;

        $monto_estimado_concretado = number_format($monto_estimado_concretado, 2);

        if ($cantidad_pedidos > 0) {
            $porcentaje_concrecion = number_format($cantidad_pedidos_concretados * 100 / $cantidad_pedidos, 0, ".", ",");
        }

        if ($cantidad_pedidos_concretados > 0) {
            $ratio_trabajo = number_format($cantidad_cotizaciones / $cantidad_pedidos_concretados, 2, ".", ",");
        }

        if ($cantidad_pedidos > 0) {
            $ratio_trabajo_recotizacion = number_format($cantidad_cotizaciones / $cantidad_pedidos, 2, ".", ",");
        }

        $response = [
            'all_quotes' => $cantidad_cotizaciones,
            'quotes_ok' => $cantidad_respondidas_a_tiempo,
            'work_rate' => $ratio_trabajo,
            'work_rate_orders' => $ratio_trabajo_recotizacion,
            'all_orders' => $cantidad_pedidos,
            'mount_all_orders' => $monto_estimado,
            'orders_placed' => $cantidad_pedidos_concretados,
            'files_placed' => $cantidad_files_concretados,
            'mount_orders_placed' => $monto_estimado_concretado,
            'percent_placed' => $porcentaje_concrecion,
            'time_response' => $porcentaje,
            'stela_quotes' => $cantidad_cotis_stela,
            'aurora_quotes' => $cantidad_cotis_aurora,
            'percent_stela_quotes' => ($cantidad_cotizaciones > 0) ? (number_format($cantidad_cotis_stela / $cantidad_cotizaciones * 100, 2, ".", ",")) : 0,
            'percent_aurora_quotes' => ($cantidad_cotizaciones > 0) ? (number_format($cantidad_cotis_aurora / $cantidad_cotizaciones * 100, 2, ".", ",")) : 0
        ];

        return $response;
    }

    public function checkDates($fecini, $fecfin, $field)
    {
        $d_one = strtotime($fecini);
        $d_two = strtotime($fecfin);
        $segundos = $d_two - $d_one;
        $segundos = intval($segundos);

        $units = array(
            'month' => '30 / 24 / 60 / 60',
            'day' => '24 / 60 / 60',
            'hour' => '60 / 60',
            'minute' => '60',
            'second' => '1',
        );

        //calculo el año, mes, dia, hora, minutos, segundos
        foreach ($units as $unit => $val) {
            $_val = explode("/", $val);
            $_segundos = $segundos;

            foreach ($_val as $k => $v) {
                $value = floor($_segundos / (int) $v);
                $_segundos = $value;
            }

            $ret[$unit] = $value;
        }

        return ($field != '' and isset($ret[$field])) ? $ret[$field] : $ret;
    }

    public function mes($mes)
    {
        $meses = ['', 'ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO', 'JULIO', 'AGOSTO', 'SETIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE'];
        return $meses[$mes];
    }

    /**
     * PUSH NOTIFICATION
     * @since 2019
     * @params Notification
     * @author KLuizSv
     */
    public function sendPushNotification($notification)
    {
        // Traer los navegadores registrados..
        $user = User::where('code', '=', $notification->user)->first();

        if ($user) {
            $tokens = UserNotification::where('user_id', '=', $user->id);
            $cantidad = $tokens->count();
            $tokens = $tokens->get();

            $response = [];
            $err = [];

            if ($cantidad > 0) {
                foreach ($tokens as $key => $value) {
                    $curl = curl_init();

                    $payload = json_encode(array('to' => $value->token, 'notification' => $notification));

                    curl_setopt_array($curl, array(
                        CURLOPT_URL => "https://fcm.googleapis.com/fcm/send",
                        CURLOPT_POST => true,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "POST",
                        CURLOPT_POSTFIELDS => $payload,
                        CURLOPT_HTTPHEADER => array(
                            "Authorization: key=AAAAfiUDU-g:APA91bGI-fdhLGFb9PrvB0OSVUOV2RzmFoKIPSL10df9U7u5J-K8t4hk-kPq1ZQRlGOENFBoGOHfRoELTVR--h1j4FD_O1eejbE5-TP7S9SM2TNtSbJdebrA-qgxxqFi9qfkQoT8pUPH",
                            'Content-Type: application/json',
                            "cache-control: no-cache"
                        ),
                    ));

                    $response[] = curl_exec($curl);
                    $err[] = curl_error($curl);

                    curl_close($curl);
                }
            }

            return response()->json(['message' => $response, 'errors' => $err]);
        } else {
            return response()->json(['message' => [], 'errors' => ['El usuario no existe']]);
        }
    }

    public function getFeriados($date)
    {
        $feriados = array('-10-08', '-10-31', '-11-01', '-12-08', '-12-25');
        $response = 0;

        foreach ($feriados as $key => $value) {
            if (strpos($date, $value) > 0) {
                $response = 1;
                break;
            }
        }

        return $response;
    }

    /** Retorna el día de la semana o "error" de no obtener cualquier nombre de semana
     ** Nombres de los días invariables, asi se estan manejando en la BD
     * @param string $date 2015-05-15
     * @return string
     */
    public function getDayWeek($date)
    {
        $dias = array("DOMING", "LUNES", "MARTES", "MIERCO", "JUEVES", "VIERNE", "SABADO");
        $dia = substr($date, 8, 2);
        $mes = substr($date, 5, 2);
        $anio = substr($date, 0, 4);

        $responseFeriado = $this->getFeriados($anio . '-' . $mes . '-' . $dia);

        if ($responseFeriado == 0) {
            $response = strtoupper($dias[intval((date("w", mktime(0, 0, 0, $mes, $dia, $anio))))]);
            $check = 0;

            for ($i = 0; $i < count($dias); $i++) {
                if ($dias[$i] == $response) {
                    $check++;
                }
            }

            if ($check != 1) {
                $response = 'error';
            }
        } else {
            $response = 'DOMING';
        }

        return $response;
    }

    public function addToDate($suma, $fechaInicial = false)
    {
        if (is_null($suma) or empty($suma)) return date('Y-m-d');
        $fecha = !empty($fechaInicial) ? $fechaInicial : date('Y-m-d');
        $nuevaFecha = strtotime($suma, strtotime($fecha));
        $nuevaFecha = date('Y-m-d', $nuevaFecha);
        return $nuevaFecha;
    }

    public function checkDateFormat($date1_str, $date2_str)
    {
        // --- 1. Configuración y Preparación

        // Definición de las reglas de trabajo
        $HORA_INICIO = 9;   // 09:00:00
        $HORA_FIN = 18; // 18:00:00
        $SEGUNDOS_JORNADA = ($HORA_FIN - $HORA_INICIO) * 3600; // 9 horas * 3600 seg/hora

        // Días laborables (Lunes=1 a Viernes=5)
        $DIAS_LABORABLES = [1, 2, 3, 4, 5];

        // Crear objetos DateTime inmutables
        try {
            $date1 = new DateTimeImmutable($date1_str);
            $date2 = new DateTimeImmutable($date2_str);
        } catch (\Exception $e) {
            // Manejo básico de error de formato de fecha
            return ['error' => 'Formato de fecha inválido'];
        }

        $sign = 1;
        if ($date1 > $date2) {
            $temp = $date1;
            $date1 = $date2;
            $date2 = $temp;
            $sign = -1;
        }

        if ($date1 == $date2) return ['day' => 0, 'hour' => 0, 'days' => 0];

        // --- 2. Preparar el punto de inicio ($current)

        $current = $date1;
        $total_segundos = 0;

        // Asegurar que $current comience en un momento de trabajo válido.

        // A. Si no es día laboral, avanza al próximo lunes/día laboral a las 9:00.
        while (!in_array($current->format('N'), $DIAS_LABORABLES)) {
            $current = $current->modify('+1 day')->setTime($HORA_INICIO, 0);
        }

        // B. Si es día laboral, ajusta la hora:
        $inicio_jornada = $current->setTime($HORA_INICIO, 0);
        $fin_jornada = $current->setTime($HORA_FIN, 0);

        // Si es antes de las 9:00, ajusta a las 9:00.
        if ($current < $inicio_jornada) {
            $current = $inicio_jornada;
        }
        // Si es después de las 18:00, avanza al día siguiente a las 9:00.
        elseif ($current >= $fin_jornada) {
            do {
                $current = $current->modify('+1 day')->setTime($HORA_INICIO, 0);
            } while (!in_array($current->format('N'), $DIAS_LABORABLES));
        }

        // --- 3. Calcular los Segundos Laborales

        // Mientras el punto de inicio ($current) no haya llegado al final ($date2)
        while ($current < $date2) {
            $dia_actual = $current->format('Y-m-d');

            // Calcular el final del día de trabajo actual (18:00:00)
            $fin_laboral_hoy = (new DateTimeImmutable($dia_actual))->setTime($HORA_FIN, 0);

            // 3.1. Caso: $date2 cae en el día laboral actual
            if ($date2->format('Y-m-d') === $dia_actual) {

                // Si $date2 es después del fin de jornada de hoy, usamos el fin de jornada (18:00)
                $final_calculo = min($date2, $fin_laboral_hoy);

                // Los segundos a añadir son la diferencia entre $current y el punto final
                $total_segundos += $final_calculo->getTimestamp() - $current->getTimestamp();
                break; // Terminamos el bucle
            }

            // 3.2. Caso: Es un día completo de trabajo (o el bucle aún no ha terminado)

            // Segundos desde $current hasta el final de la jornada de hoy (18:00)
            $segundos_hoy = $fin_laboral_hoy->getTimestamp() - $current->getTimestamp();
            $total_segundos += $segundos_hoy;

            // Avanzar $current al inicio de la jornada del próximo día laboral
            do {
                $current = $current->modify('+1 day')->setTime($HORA_INICIO, 0);
            } while (!in_array($current->format('N'), $DIAS_LABORABLES));

            // Optimización: Si el próximo día laboral está muy lejos de $date2, podemos saltar días completos
            // (Esta optimización compleja se omite para mantener la claridad, pero es la base de la complejidad del código original).
        }

        // --- 4. Resultados

        $horas_totales = ceil($sign * $total_segundos / 3600);
        $dias_totales = ceil($horas_totales / ($HORA_FIN - $HORA_INICIO));

        return [
            'hour' => $horas_totales,
            'day' => $dias_totales,
            'days' => floor($horas_totales / ($HORA_FIN - $HORA_INICIO)), // Días laborables completos
        ];
    }

    public function searchReservationHotel($nrofile, $hotel, $fecin)
    {
        $client = new \GuzzleHttp\Client(["verify" => false]);
        $baseUrlExtra = ((config('app.APP_ENV') == 'production') ? 'https://backend.limatours.com.pe' : 'https://auroraback.limatours.com.pe');
        // $baseUrlExtra = 'http://127.0.0.1:8000/';
        $request = $client->get($baseUrlExtra . '/reservation_hotel_frontend?nrofile=' . $nrofile . '&fecin=' . $fecin . '&hotel=' . $hotel);
        $response = (array) json_decode($request->getBody()->getContents(), true);

        return $response;
    }

    public function searchReservationHotelID($nrofile, $hotel, $fecin)
    {
        $client = new \GuzzleHttp\Client(["verify" => false]);
        $baseUrlExtra = ((config('app.APP_ENV') == 'production') ? 'https://backend.limatours.com.pe' : 'https://auroraback.limatours.com.pe');
        // $baseUrlExtra = 'http://127.0.0.1:8000/';
        $request = $client->get($baseUrlExtra . '/reservation_hotel_frontend_id?nrofile=' . $nrofile . '&fecin=' . $fecin . '&hotel=' . $hotel);
        $response = (array) json_decode($request->getBody()->getContents(), true);

        return (int) @$response[0]['id'];
    }

    public function trimArray($array)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $value = $this->trimArray($value);
            } else {
                $value = trim($value);
            }

            $array[$key] = $value;
        }

        return $array;
    }

    protected function validateTime($value, $codsec)
    {
        // PHP 7: Usamos '??' para obtener un valor, o una cadena vacía si la clave no existe (evita avisos/errores)
        $fecres_base = trim($value['fecres'] ?? '');
        $horres_base = trim($value['horres'] ?? '');

        // Asignación de fecha/hora de respuesta
        $fecres = !empty($fecres_base)
            ? $fecres_base . ' ' . $horres_base
            : date("Y-m-d H:i:s");

        // Asignación de fecha/hora de recepción (asumiendo que siempre hay valores)
        $fecrec_base = trim($value['fecrec'] ?? '');
        $horrec_base = trim($value['horrec'] ?? '');
        $fecrec = $fecrec_base . ' ' . $horrec_base;


        // --- 2. Cálculo de Días/Horas
        $dias = $this->checkDateFormat($fecrec, $fecres);

        // Extracción de resultados con Fusión Nula para arrays:
        // Si $dias['days'] no existe, se asigna 0.
        $days_result = $dias['days'] ?? 0;
        $hour_result = $dias['hour'] ?? 0;
        $day_result  = $dias['day'] ?? 0;

        // Asignación de valores
        $value['days']          = is_array($dias) ? (int)$days_result : 0; // Se asegura el tipo (int)
        $value['response_days'] = $dias;
        $value['horas']         = (int) $hour_result;
        $value['dias']          = (int) $day_result;


        // --- 3. Validación del Tiempo

        // Uso de un array asociativo para mejor claridad
        $limites_por_sector = [
            0 => 0,
            1 => 12,
            2 => 72,
            3 => 0,
            4 => 48,
            5 => 120
        ];

        // PHP 7: Fusión Nula para obtener el límite o 0 si $codsec no existe
        $limite = $limites_por_sector[$codsec] ?? 0;
        $horas  = $value['horas'];

        $alerta = '';

        if ($limite > 0) {
            if ($horas <= $limite) {
                $alerta = 'success';
            } else {
                // Uso de ternario para simplificar la lógica anidada
                $alerta = ($value['estado'] === 'OK') ? 'warning' : 'danger';
            }
        }

        return [
            'class' => $alerta,
            'horas' => $horas,
        ];
    }

    public function verifyQuotes($orders, $lang, $flag_report = 0)
    {
        try {
            $chkpro_desc = [
                'Ninguno',
                'Programación Cliente',
                'Programación Lito',
                'Programación Lito sin cambios',
                'Revisión de cotización hecha por cliente',
                'Hoteles o Servicios sueltos',
                'Programación exclusiva CLIENTE sin cambios',
                'Tailormade',
                'Programación Lito con cambios',
            ];

            $new_orders = [];

            foreach ($orders as $key => $value) {
                $value = (array) $value;
                $value = $this->trimArray($value);

                if (!empty($value['nroped'])) {
                    $_nroref = @$value['nroref'];

                    if ($value['chkpro'] == 7) {
                        $orders[$key]['codsec'] = 4;
                        $orders[$key]['producto'] = 'TAILORMADE';
                    }

                    $_codsec = isset($value['codsec']) ? trim($value['codsec']) : '';

                    $codsec = (strlen($_codsec) >= 3) ? $_codsec[2] : $_codsec;

                    $_etiquetas = array();
                    $etiquetas = json_decode($value['label']);

                    if (count($etiquetas) > 0) {
                        foreach ($etiquetas as $a => $b) {
                            $_etiquetas[] = array('id' => $b->id, 'etiqueta' => $b->nombre, 'colbac' => $b->colbac, 'coltex' => $b->coltex);
                        }
                    }

                    $orders[$key]['etiquetas'] = $_etiquetas;

                    $validate = $this->validateTime($value, $codsec);

                    $orders[$key]['horas'] = $validate['horas'];
                    $orders[$key]['class'] = $validate['class'];

                    $orders[$key]['chkpro_desc'] = '';

                    if ($orders[$key]['chkpro'] > -1) {
                        $orders[$key]['chkpro_desc'] = $chkpro_desc[$orders[$key]['chkpro']];
                    }

                    $orders[$key]['quote'] = null;

                    if ($value['nroref_identi'] == 'B') // Aurora 2
                    {
                        $quote = Quote::where('id', '=', $_nroref)->first();

                        if ($quote) {
                            $log = DB::table('quote_logs')
                                ->where('object_id', '=', $_nroref)
                                ->where(function ($q) {
                                    $q->orWhere('type', '=', 'editing_quote');
                                })
                                ->first();

                            if ($log != '' and $log != null) {
                                $orders[$key]['quote_log'] = $log;
                                $_nroref = $log->quote_id;
                            }

                            $quote = Quote::where('id', '=', $_nroref)->first();
                        }

                        $orders[$key]['nroref_nuevo'] = $_nroref;
                        $orders[$key]['quote'] = $quote;

                        if ($quote) {
                            if ($quote->estimated_price > 0) {
                                $orders[$key]['price_estimated'] = number_format($quote->estimated_price, 2, ".", "");
                                $orders[$key]['fectravel'] = $quote->date_in;
                                $orders[$key]['fectravel_tca'] = $quote->estimated_travel_date;
                            } else {
                                if ($quote->operation == 'passengers') {
                                    $quote_people = DB::table('quote_people')
                                        ->where('quote_id', '=', $quote->id)
                                        ->first();

                                    $orders[$key]['quote_people'] = $quote_people;

                                    $client = new \GuzzleHttp\Client();
                                    $baseUrlExtra = 'https://backend.limatours.com.pe';
                                    // $baseUrlExtra = 'http://127.0.0.1:8000/';
                                    $request = $client->get($baseUrlExtra . '/quote_passengers_frontend?quote_id=' . $quote->id . '&lang=' . $lang);
                                    $response = (array) json_decode($request->getBody()->getContents(), true);
                                    $orders[$key]['DATA_AURORA_2'] = $response;

                                    $mount_total = NULL;
                                    $count = 0;
                                    $items = ['', 'SGL', 'DBL', 'TPL'];

                                    if (isset($response['data'][0]['passengers'])) {
                                        foreach ($response['data'][0]['passengers'] as $k => $v) {
                                            $_name = explode("-", $v['first_name']);
                                            $_key = array_search(trim(last($_name)), $items);

                                            if ($_key > 0) {
                                                $count += $_key;
                                            } else {
                                                $count += 1;
                                            }

                                            if ($mount_total == NULL) {
                                                $mount_total = $v['total'];
                                            } else {
                                                $mount_total += $v['total'];
                                            }
                                        }
                                    }

                                    if (((float) $quote_people->adults + (float) $quote_people->child) > $count) {
                                        if ($mount_total != NULL and $mount_total > 0) {
                                            $mount_total = $mount_total * $quote_people->adults;
                                        }
                                    }
                                }

                                if ($quote->operation == 'ranges') {
                                    $client = new \GuzzleHttp\Client();
                                    $baseUrlExtra = 'https://backend.limatours.com.pe';
                                    // $baseUrlExtra = 'http://127.0.0.1:8000/';
                                    $request = $client->get($baseUrlExtra . '/quote_ranges_frontend?quote_id=' . $quote->id . '&lang=' . $lang);
                                    $response = (array) json_decode($request->getBody()->getContents(), true);
                                    $orders[$key]['DATA_AURORA_2'] = $response;

                                    $mount_total = NULL;

                                    if (isset($response['ranges'])) {
                                        foreach ($response['ranges'] as $k => $v) {
                                            if ($mount_total == NULL) {
                                                $mount_total = $v['promedio'];
                                            } else {
                                                if ($v['promedio'] <= $mount_total and $v['promedio'] > 0) {
                                                    $mount_total = $v['promedio'];
                                                }
                                            }
                                        }
                                    }
                                }

                                $orders[$key]['price_estimated'] = number_format($mount_total, 2, ".", "");
                                $orders[$key]['fectravel'] = $quote->date_in;
                                $orders[$key]['fectravel_tca'] = $quote->estimated_travel_date;

                                $quote->estimated_price = (float) $mount_total;
                                $quote->save();
                            }
                        }

                        if (empty(@$value['nompaq'])) {
                            $quote_log = DB::table('quote_logs')
                                ->whereIn('quote_id', [
                                    $orders[$key]['nroref_nuevo'],
                                    $orders[$key]['nroref'],
                                ])
                                ->where('type', '=', 'from_package')
                                ->first();

                            $package = DB::table('package_translations')
                                ->where('package_id', '=', @$quote_log->object_id)
                                ->first();

                            $orders[$key]['nompaq'] = @$package->name;
                            // $orders[$key]['data_log'] = $quote_log;
                            // $orders[$key]['data_package'] = $package;
                        }

                        if (empty(@$value['price_end']) and !empty($value['nrofile'])) {
                            $reservation = DB::table('reservations')->where('file_code', '=', $value['nrofile'])->first();
                            $orders[$key]['price_end'] = number_format($reservation->total_amount, 2, ".", ",");
                        }
                    }

                    if (strlen(trim($value['horrec'])) == 3) {
                        $value['horrec'] = trim($value['horrec']) . '00';
                    }

                    $orders[$key]['horrec'] = date("H:i", strtotime($value['horrec']));

                    if ($value['horres'] != '' and $value['horres'] != NULL) {
                        $orders[$key]['horres'] = date("H:i", strtotime($value['horres']));
                    }

                    $new_orders[] = $orders[$key];
                }
            }

            return $new_orders;
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function export_excel(Request $request)
    {
        $type = $request->__get('type');
        $table = $request->__get('table');

        return Excel::download(new ReportsExport($type, $table), $type . '.xls');
    }

    public function export_pdf(Request $request)
    {
        $type = $request->__get('type');
        $pdf = \PDF::loadView('exports.' . $type, [
            'data' => session()->get($type)
        ]);
        $format_pdf = session()->get('format_pdf');
        $pdf->setPaper('a4', ($format_pdf != null and $format_pdf != '') ? ($format_pdf) : 'landscape');
        //return $pdf->stream();
        session()->forget('format_pdf');
        return $pdf->download($type . '.pdf');
    }
}
