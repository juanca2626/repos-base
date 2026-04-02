<?php

namespace App\Http\Controllers;

use App\Country;
use App\File;
use App\Http\Stella\StellaService;
use App\Imports\PassengersImport;
use App\LogModal;
use App\Quote;
use App\QuoteLog;
use App\QuotePassenger;
use App\QuoteServicePassenger;
use App\Reservation;
use App\ReservationPassenger;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Traits\QuoteHistories;
use App\Http\Traits\Files;
use App\User;
use Illuminate\Support\Facades\Mail;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\NumberParseException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PaxsController extends Controller
{
    protected $stellaService;

    use QuoteHistories, Files;

    public function __construct(StellaService $stellaService)
    {
        $this->stellaService = $stellaService;
    }

    public $keys = [
        'sequence_number' => 'nrosec',
        'name' => 'nombres',
        'surnames' => 'apellidos',
        'first_name' => 'nombres',
        'last_name' => 'apellidos',
        'gender' => 'sexo',
        'genre' => 'sexo',
        'birthday' => 'fecnac',
        'date_birth' => 'fecnac',
        'city_iso' => 'ciunac',
        'document_number' => 'nrodoc',
        'doctype_iso' => 'tipdoc',
        'country_iso' => 'nacion',
        'email' => 'correo',
        'phone' => 'celula',
        'notes' => 'observ',
        'dietary_restrictions' => 'resali',
        'medical_restrictions' => 'resmed',
        'type' => 'tipo',
        'localizador' => 'nropax',
    ];

    public function transformPhoneNumberString($number, $country_iso = null)
    {
        $number = ltrim($number, "0");

        if (strpos($number, "+") === false) {
            $result['phone_code'] = "";
            $result['phone_number'] = $number;
        }

        $result = [];

        if ($number == '') {
            return [
                'phone_code' => '',
                'phone_number' => '',
            ];
        }

        try {
            $phoneUtil = PhoneNumberUtil::getInstance();
            $phoneNumberObject = $phoneUtil->parse($number, $country_iso);

            $result['phone_code'] = $phoneNumberObject->getCountryCode();
            $result['phone_number'] = $phoneNumberObject->getNationalNumber();
        } catch (\Exception $ex) {
            $result['phone_code'] = "";
            $result['phone_number'] = $number;
        }

        return $result;
    }

    private function validatePhoneConsistency($phoneNumber, $countryCode)
    {
        // Validación básica de parámetros
        if (empty($phoneNumber) || empty($countryCode)) {
            return 0;
        }

        $phoneUtil = PhoneNumberUtil::getInstance();

        try {
            $expectedCode = $phoneUtil->getCountryCodeForRegion($countryCode);
            if (!$expectedCode) {
                return 0;
            }

            $cleanNumber = preg_replace('/[^\d]/', '', $phoneNumber);
            $actualPrefix = substr($cleanNumber, 0, strlen((string)$expectedCode));

            return ($actualPrefix == $expectedCode) ? 1 : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function fixPhoneNumber($rawPhone)
    {
        $phoneUtil = PhoneNumberUtil::getInstance();
        $cleanPhone = preg_replace('/[^\d]/', '', $rawPhone);

        // Probar substrings del número hasta que encuentre uno válido
        for ($i = 0; $i < strlen($cleanPhone) - 6; $i++) {
            $possiblePhone = substr($cleanPhone, $i);

            try {
                $parsed = $phoneUtil->parse('+' . $possiblePhone, 'ZZ');

                if ($phoneUtil->isValidNumber($parsed)) {
                    return [
                        'phone_code' => $parsed->getCountryCode(),
                        'phone_number' => (string)$parsed->getNationalNumber(),
                        'full' => $parsed->getCountryCode() . $parsed->getNationalNumber(),
                    ];
                }
            } catch (NumberParseException $e) {
                // continuar probando
            }
        }

        // Si no encuentra válido
        return [
            'phone_code' => null,
            'phone_number' => null,
            'full' => null
        ];
    }

    public function search_passengers(Request $request)
    {
        $response = [];

        try {
            $_type = $request->get('type');
            $reference = $request->get('file');
            $quote = $request->get('quote');
            $hide_passport = (int) $request->get('hide_passport', 0);

            if ($_type == 'file' || ($_type == 'package' && $reference != null)) {

                $flag_save = false;
                $passengers = [];
                $repeat_passenger = 0;
                $_passengers = [];
                $prohib = 0;
                $lock_list = 1;
                $clean = false;

                // Consulta optimizada
                $reservationsPassengers = ReservationPassenger::whereHas('reservation', function ($query) use ($reference) {
                    $query->where('file_code', $reference);
                })
                    ->get()
                    ->toArray();

                if (empty($reservationsPassengers)) {
                    $array = ['nroref' => $reference];
                    $flag_save = true;
                    $data = (array) $this->stellaService->search_paxs($array);

                    $reservationsPassengers = $this->toArray($data['datpax']);
                }

                $response['flag_save'] = $flag_save;

                foreach ($reservationsPassengers as $key => $value) {
                    $value = (array) $value;

                    foreach ($value as $k => $v) {
                        if (isset($this->keys[$k]) && $this->keys[$k] != '') {
                            $value[$this->keys[$k]] = $v;
                        }
                    }

                    if (!$flag_save) {
                        $value['nombre'] = $value['apellidos'] . ',' . $value['nombres'];
                    }

                    $value['nombre'] = trim($value['nombre']);


                    if ($flag_save) {
                        $nombre = explode(",", $value['nombre']);
                        $value['nombres'] = trim(@$nombre[1]);
                        $value['apellidos'] = trim($nombre[0]);
                    }

                    if ($flag_save) {
                        $clean = strpos(strtoupper($value['nombre']), 'PASAJERO');
                    }

                    $value['city_ifx_iso'] = trim($value['ciunac']);

                    if (!$flag_save) {
                        $value['tiphab'] = $value['suggested_room_type'];
                    }

                    if (!in_array($value['nombre'], $_passengers)) {
                        $repeat_passenger = 0;
                        $_passengers[] = $value['nombre'];
                    } else {
                        $repeat_passenger = 1;
                    }

                    $fecha_nacimiento = explode("-", $value['fecnac']);
                    if (count($fecha_nacimiento) > 1) {
                        $value['fecnac'] = $fecha_nacimiento[2] . '/' . $fecha_nacimiento[1] . '/' . $fecha_nacimiento[0];
                    }

                    if ($hide_passport === 1) {
                        $value['nrodoc'] = encrypt($value['nrodoc']);
                    }

                    if ($flag_save) {
                        foreach ($value as $k => $v) {
                            $value[$k] = ($clean !== false || $v === '' || is_null($v)) ? '' : trim($v);
                        }
                    }

                    if ($flag_save) {
                        $phone_parse = $this->fixPhoneNumber($value['celula']);

                        if ($phone_parse['phone_code']) {
                            $value['phone_code'] = $phone_parse['phone_code'];
                            $value['phone'] = $phone_parse['full'] ?? '';
                            $value['celula'] = $phone_parse['phone_number'];
                        } else {
                            $value['phone_code'] = '';
                            $value['phone'] = '';
                            $value['celula'] = '';
                        }
                    }

                    // $sequenceNumber = trim($value['nrosec']);
                    // $reservation_passenger = $reservationsPassengers->get(trim($sequenceNumber));
                    // $value['document_url'] = $reservation_passenger ? $reservation_passenger->document_url : '';

                    $passengers[$key] = $value;

                    if (isset($value['prohib']) && $value['prohib'] == 1) {
                        $prohib = 1;
                    }
                }

                if ($quote > 0) {
                    $lock_list = 0;
                }

                $response['clean'] = $clean;
                $response['prohib'] = $prohib;
                $response['lock_list'] = $lock_list;
                $response['passengers'] = $passengers;
                $response['repeat_passenger'] = $repeat_passenger;

                // Datos adicionales
                $array1 = ['modulo' => 1, 'nroref' => $reference];
                $response1 = (array) $this->stellaService->status_pax($array1);

                $response['detail'] = $response1;
                $response['canadl'] = $response1['canadl'] ?? 0;
                $response['canchd'] = $response1['canchd'] ?? 0;
                $response['caninf'] = $response1['caninf'] ?? 0;

                $array2 = ['modulo' => 2, 'nroref' => $reference];
                $response2 = (array) $this->stellaService->status_pax($array2);

                $response['detail']['estado'] = $response2['estado'] ?? '';
                $response['detail']['mensaje'] = $response2['mensaje'] ?? '';
                $response['type'] = 'success';


                if ($quote > 0) {
                    $response['quote_passengers'] = $this->save_quote_passengers($quote, $passengers);
                }

                if ($flag_save) {
                    $response['reservation_paxs'] = $this->save_reservation_passengers($reference, $passengers, $hide_passport);
                }
            }

            if ($_type == 'quote') {
                $_response = QuotePassenger::where('quote_id', '=', $reference)->get()->toArray();
                $passengers = [];
                $repeat_passenger = 0;
                $_passengers = [];
                $prohib = 0;
                $lock_list = 0;
                $adults = 0;
                $childs = 0;
                $infants = 0;

                if ($_response != '' && is_array($_response)) {
                    foreach ($_response as $key => $value) {
                        foreach ($value as $k => $v) {
                            if (isset($this->keys[$k]) && $this->keys[$k] != '') {
                                $_response[$key][$this->keys[$k]] = $v;
                            }
                        }

                        $_response[$key]['nombre'] = $value['last_name'] . ',' . $value['first_name'];

                        $phone_parse = $this->transformPhoneNumberString($value['phone'], $value['country_iso']);
                        $_response[$key]['phone_code'] = $phone_parse['phone_code'];
                        if ($phone_parse['phone_code']) {
                            $_response[$key]['celula'] = $phone_parse['phone_number'];
                        }
                    }

                    foreach ($_response as $key => $value) {
                        $value = (array)$value;

                        if (!in_array($value['nombre'], $_passengers)) {
                            $repeat_passenger = 0;
                            $_passengers[] = $value['nombre'];
                        } else {
                            $repeat_passenger = 1;
                        }

                        if ($value['type'] == 'ADL') $adults++;
                        if ($value['type'] == 'CHD') $childs++;
                        if ($value['type'] == 'INF') $infants++;

                        $nombre = explode(",", $value['nombre']);
                        $value['nombres'] = trim($nombre[1] ?? '');
                        $value['apellidos'] = trim($nombre[0] ?? '');

                        $fecha_nacimiento = explode("-", $value['fecnac']);
                        if (count($fecha_nacimiento) > 1) {
                            $value['fecnac'] = $fecha_nacimiento[2] . '/' . $fecha_nacimiento[1] . '/' . $fecha_nacimiento[0];
                        }

                        foreach ($value as $k => $v) {
                            if ($v == '' || is_null($v)) {
                                $value[$k] = '';
                            }
                            if ($k == 'is_direct_client') {
                                $value[$k] = $v;
                            }
                        }

                        $passengers[$key] = $value;

                        if (isset($value['prohib']) && $value['prohib'] == 1) {
                            $prohib = 1;
                        }
                    }

                    $lock_list = 1;
                }

                $response = $_response;
                $response['canadl'] = $adults;
                $response['canchd'] = $childs;
                $response['caninf'] = $infants;
                $response['detail']['estado'] = '';
                $response['detail']['mensaje'] = '';
                $response['prohib'] = $prohib;
                $response['lock_list'] = $lock_list;
                $response['passengers'] = $passengers;
                $response['repeat_passenger'] = 0;
                $response['type'] = 'success';
            }
        } catch (\Exception $ex) {
            $response['error'] = $this->throwError($ex);
        } finally {
            $response['type_modal'] = $_type;
            return response()->json($response);
        }
    }


    public function save_quote_passengers($quote_id, $passengers = [])
    {
        try {
            $paxs = QuotePassenger::where('quote_id', '=', $quote_id)->get();
            // $quote_service_paxs = []; // $passengers = [];

            // foreach($paxs as $key => $value)
            // {
            // $_paxs = QuoteServicePassenger::where('quote_passenger_id', '=', $value->id)->get();
            // $quote_service_paxs[$key] = [];

            // if(count($_paxs) > 0)
            // {
            //     foreach($_paxs as $k => $v)
            //     {
            //         $quote_service_paxs[$key][] = $v->quote_service_id;
            //     }
            // }

            // QuoteServicePassenger::where('quote_passenger_id', '=', $value->id)->delete();
            // }

            // QuotePassenger::where('quote_id', '=', $quote_id)->delete();

            foreach ($passengers as $key => $value) {
                $nombre = explode(",", $value['nombre']);

                // $pax = new QuotePassenger;
                $pax = QuotePassenger::find($paxs[$key]->id);
                $pax->quote_id = $quote_id;
                $pax->first_name = (@$nombre[1]);
                $pax->last_name = ($nombre[0]);
                $pax->gender = $value['sexo'];

                $fecha = explode("/", $value['fecnac']);

                if (count($fecha) > 1) {
                    $pax->birthday = $fecha[2] . '-' . $fecha[1] . '-' . $fecha[0];
                } else {
                    $pax->birthday = '';
                }

                $pax->document_number = ($value['nrodoc']);
                $pax->doctype_iso = ($value['tipdoc']);
                $pax->country_iso = ($value['nacion']);
                $pax->city_ifx_iso = ($value['ciunac']);
                $pax->email = ($value['correo']);
                $pax->phone = ($value['celula']);
                $pax->notes = ($value['observ']);
                $pax->address = '';
                $pax->dietary_restrictions = ($value['resali']);
                $pax->medical_restrictions = ($value['resmed']);
                $pax->type = ($value['tipo']);
                $pax->is_direct_client = 0;
                $pax->save();

                // foreach($quote_service_paxs[$key] as $k => $v)
                // {
                //     $quote_service_pax = new QuoteServicePassenger;
                //     $quote_service_pax->quote_service_id = $v;
                //     $quote_service_pax->quote_passenger_id = $pax->id;
                //     $quote_service_pax->save();
                // }

            }

            // Los pasajeros que están en draft..
            $quote_id_general = '';
            $logs = QuoteLog::where('object_id', $quote_id)->get();
            foreach ($logs as $l) {
                if ($l->type == "editing_quote") {
                    $quote_id_general = $l->quote_id;
                    break;
                }
            }

            if ($quote_id_general != '') {
                $paxs = QuotePassenger::where('quote_id', '=', $quote_id_general)->get();
                // $quote_service_paxs = [];

                // foreach($paxs as $key => $value)
                // {
                //     $_paxs = QuoteServicePassenger::where('quote_passenger_id', '=', $value->id)->get();

                //     $quote_service_paxs[$key] = [];
                //     if(count($_paxs) > 0)
                //     {
                //         foreach($_paxs as $k => $v)
                //         {
                //             $quote_service_paxs[$key][] = $v->quote_service_id;
                //         }
                //     }

                //     QuoteServicePassenger::where('quote_passenger_id', '=', $value->id)->delete();
                // }

                // QuotePassenger::where('quote_id', '=', $quote_id_general)->delete();

                foreach ($passengers as $key => $value) {
                    $nombre = explode(",", $value['nombre']);

                    // $pax = new QuotePassenger;
                    $pax = QuotePassenger::find($paxs[$key]->id);
                    $pax->quote_id = $quote_id_general;
                    $pax->first_name = (@$nombre[1]);
                    $pax->last_name = ($nombre[0]);
                    $pax->gender = $value['sexo'];

                    $fecha = explode("/", $value['fecnac']);

                    if (count($fecha) > 1) {
                        $pax->birthday = $fecha[2] . '-' . $fecha[1] . '-' . $fecha[0];
                    } else {
                        $pax->birthday = '';
                    }

                    $pax->document_number = ($value['nrodoc']);
                    $pax->doctype_iso = ($value['tipdoc']);
                    $pax->country_iso = ($value['nacion']);
                    $pax->city_ifx_iso = ($value['ciunac']);
                    $pax->email = ($value['correo']);
                    $pax->phone = ($value['celula']);
                    $pax->notes = ($value['observ']);
                    $pax->address = '';
                    $pax->dietary_restrictions = ($value['resali']);
                    $pax->medical_restrictions = ($value['resmed']);
                    $pax->type = ($value['tipo']);
                    $pax->is_direct_client = 0;
                    $pax->save();

                    // foreach($quote_service_paxs[$key] as $k => $v)
                    // {
                    //     $quote_service_pax = new QuoteServicePassenger;
                    //     $quote_service_pax->quote_service_id = $v;
                    //     $quote_service_pax->quote_passenger_id = $pax->id;
                    //     $quote_service_pax->save();
                    // }
                }
            }

            // Los pasajeros que están en draft..
            $quote_id_general = '';
            $logs = QuoteLog::where('quote_id', $quote_id)->get();
            foreach ($logs as $l) {
                if ($l->type == "editing_quote") {
                    $quote_id_general = $l->object_id;
                    break;
                }
            }

            if ($quote_id_general != '') {
                $paxs = QuotePassenger::where('quote_id', '=', $quote_id_general)->get();
                // $quote_service_paxs = [];

                // foreach($paxs as $key => $value)
                // {
                //     $_paxs = QuoteServicePassenger::where('quote_passenger_id', '=', $value->id)->get();

                //     $quote_service_paxs[$key] = [];
                //     if(count($_paxs) > 0)
                //     {
                //         foreach($_paxs as $k => $v)
                //         {
                //             $quote_service_paxs[$key][] = $v->quote_service_id;
                //         }
                //     }

                //     QuoteServicePassenger::where('quote_passenger_id', '=', $value->id)->delete();
                // }

                // QuotePassenger::where('quote_id', '=', $quote_id_general)->delete();

                foreach ($passengers as $key => $value) {
                    $nombre = explode(",", $value['nombre']);

                    // $pax = new QuotePassenger;
                    $pax = QuotePassenger::find($paxs[$key]->id);
                    $pax->quote_id = $quote_id_general;
                    $pax->first_name = (@$nombre[1]);
                    $pax->last_name = ($nombre[0]);
                    $pax->gender = $value['sexo'];

                    $fecha = explode("/", $value['fecnac']);

                    if (count($fecha) > 1) {
                        $pax->birthday = $fecha[2] . '-' . $fecha[1] . '-' . $fecha[0];
                    } else {
                        $pax->birthday = '';
                    }

                    $pax->document_number = ($value['nrodoc']);
                    $pax->doctype_iso = ($value['tipdoc']);
                    $pax->country_iso = ($value['nacion']);
                    $pax->city_ifx_iso = ($value['ciunac']);
                    $pax->email = ($value['correo']);
                    $pax->phone = ($value['celula']);
                    $pax->notes = ($value['observ']);
                    $pax->address = '';
                    $pax->dietary_restrictions = ($value['resali']);
                    $pax->medical_restrictions = ($value['resmed']);
                    $pax->type = ($value['tipo']);
                    $pax->is_direct_client = 0;
                    $pax->save();

                    // foreach($quote_service_paxs[$key] as $k => $v)
                    // {
                    //     $quote_service_pax = new QuoteServicePassenger;
                    //     $quote_service_pax->quote_service_id = $v;
                    //     $quote_service_pax->quote_passenger_id = $pax->id;
                    //     $quote_service_pax->save();
                    // }
                }
            }
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function save_reservation_passengers($file_code, $passengers = [], $hide_passport)
    {
        try {

            $response = DB::transaction(function () use ($file_code, $passengers, $hide_passport) {
                // 1. Obtener la reserva una sola vez para evitar consultas en el bucle
                $reservation = Reservation::where('file_code', $file_code)->first();

                $paxs = ReservationPassenger::where('reservation_id', $reservation->id)
                    ->orderBy('sequence_number')
                    ->get();

                if (!$reservation) {
                    return []; // O manejar el error según tu necesidad
                }

                $response = [];
                $keptIds = [];

                foreach ($passengers as $index => $value) {
                    $pax = $paxs[$index] ?? false;
                    $status = strtolower($value['status'] ?? '');

                    if (!$pax) {
                        $pax = new ReservationPassenger();
                        $pax->reservation_id = $reservation->id;
                    } else {
                        if ($status === 'xl') {
                            $pax->delete();
                        }
                    }

                    if ($status === 'xl') {
                        continue;
                    }

                    $nombre = explode(",", $value['nombre'] ?? "");
                    $pax->sequence_number = $value['nrosec'] ?? null;
                    $pax->name = isset($nombre[1]) ? trim($nombre[1]) : '';
                    $pax->surnames = isset($nombre[0]) ? trim($nombre[0]) : '';
                    $pax->genre = $value['sexo'] ?? null;

                    // Fecha de nacimiento (Compatible PHP 7.4)
                    if (!empty($value['fecnac']) && strpos($value['fecnac'], '/') !== false) {
                        $fecha = explode("/", $value['fecnac']);
                        if (count($fecha) === 3) {
                            $pax->date_birth = $fecha[2] . '-' . $fecha[1] . '-' . $fecha[0];
                        }
                    } else {
                        $pax->date_birth = null;
                    }

                    $nrodoc_ = !empty($value['nrodoc']) ? $value['nrodoc'] : null;
                    if ($hide_passport === 1 && !empty($nrodoc_)) {
                        try {
                            $nrodoc_ = decrypt($nrodoc_);
                        } catch (\Exception $e) {
                            // Si falla el decrypt, mantenemos el valor original o null
                        }
                    }

                    $pax->document_number = $nrodoc_;
                    $pax->doctype_iso = $value['tipdoc'] ?? null;
                    $pax->country_iso = $value['nacion'] ?? null;
                    $pax->city_iso = $value['ciunac'] ?? null;
                    $pax->email = $value['correo'] ?? null;
                    $pax->phone = $value['celula'] ?? null;
                    $pax->phone_code = $value['phone_code'] ?? null;
                    $pax->notes = $value['observ'] ?? null;
                    $pax->dietary_restrictions = $value['resali'] ?? null;
                    $pax->medical_restrictions = $value['resmed'] ?? null;
                    $pax->type = $value['tipo'] ?? null;
                    $pax->document_url = $value['document_url'] ?? null;
                    $pax->suggested_room_type = $value['tiphab'] ?? null;
                    $pax->localizador = $value['nropax'] ?? null;

                    $pax->save();

                    // 5. Registrar IDs que sobreviven
                    $keptIds[] = $pax->id;
                    $response[] = $pax;
                }

                // 6. ELIMINACIÓN MASIVA: Borrar los que estaban en la DB pero no llegaron en el request
                $queryDelete = ReservationPassenger::where('reservation_id', $reservation->id);

                if (!empty($keptIds)) {
                    $queryDelete->whereNotIn('id', $keptIds);
                }

                $queryDelete->delete();

                return $response;
            });

            return $response;
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function import_passengers(Request $request)
    {
        set_time_limit(0);

        try {
            $response = ['type' => 'error', 'content' => 'Error al importar los datos de los pasajeros. Por favor, intente nuevamente.'];

            Excel::import(new PassengersImport($request->__get('file'), $request->__get('paxs')), $request->file('excel'));

            $response_import = (array) session()->get('data_import');

            if (count($response_import) > 0) {
                foreach ($response_import as $key => $value) {
                    if ($value['estado'] == 1) {
                        $response = ['type' => 'success', 'content' => $value['mensaje']];
                        break;
                    }
                }
            }
        } catch (\SoapFault $ex) {
            $response['message'] = $ex;
        }

        return response()->json($response);
    }

    public function passenger(Request $request, $type)
    {
        $response = [];

        try {
            $passengers = $this->toArray($request->get('passengers'));
            $file = $request->get('file');
            $repeat = (int) $request->get('repeat', 0);
            $modePassenger = (int) $request->get('modePassenger', 0);
            $_type = $request->get('type');
            $hide_passport = (int) $request->get('hide_passport', 0);
            $paxs = (int) $request->get('paxs', count($passengers));
            $ignore_stella = @$request->__get('ignore_stella');

            if ($_type === 'file') {

                $items = [];

                foreach ($passengers as $key => $params) {
                    if ($key === $paxs) break;

                    if ($repeat === 1 && $modePassenger === 1) {
                        $params = $passengers[0];
                    }

                    foreach ($params as $k => $v) {
                        $params[$k] = ($v === '' || is_null($v) || $v === '-1') ? '' : $v;
                    }

                    $fecnac_ = null;
                    if (!empty($params['fecnac'])) {
                        $fecnac = explode("/", $params['fecnac']);
                        $fecnac_ = count($fecnac) > 1 ? $fecnac[2] . '-' . $fecnac[1] . '-' . $fecnac[0] : null;
                    }

                    $nrodoc_ = !empty($params['nrodoc']) ? $params['nrodoc'] : null;
                    if ($hide_passport === 1 && !empty($nrodoc_)) {
                        $nrodoc_ = decrypt($nrodoc_);
                    }

                    $telefono = (!empty($params['celula'])) ? ($params['phone_code'] ?? '') . $params['celula'] : '';

                    if (!empty($params['nombres']) && !empty($params['apellidos'])) {
                        $items[] = [
                            'nrosec' => $key + 1,
                            'secuencia' => $key + 1,
                            'nrofile' => $file,
                            'nombre' => $params['apellidos'] . ', ' . $params['nombres'],
                            'tipo' => $params['tipo'],
                            'sexo' => $params['sexo'],
                            'fecnac' => $fecnac_,
                            'fecha' => $fecnac_,
                            'ciudad' => $params['city_ifx_iso'],
                            'pais' => $params['nacion'],
                            'tipodoc' => $params['tipdoc'],
                            'nrodoc' => $nrodoc_,
                            'correo_electronico' => $params['correo'],
                            'telefono' => $telefono,
                            'resmed' => $params['resmed'],
                            'resali' => $params['resali'],
                            'observ' => $params['observ'],
                            'estado' => $params['status'] ?? 'OK',
                            'tiphab' => $params['tiphab'],
                            'document_url' => $params['document_url'] ?? '',
                            'phone_code' => $params['phone_code'] ?? '',
                            'phone' => $params['celula'] ?? '',
                            'nropax' => $params['nropax'] ?? '',
                        ];
                    }
                }

                if (!empty($items)) {
                    $params = ['datapax' => $items];
                    $request->session()->put('params', $params);

                    if (empty($ignore_stella)) {
                        $response = (array) $this->stellaService->register_paxs($file, $params);
                    }

                    $response['reservation_paxs'] = $this->save_reservation_passengers($file, $passengers, $hide_passport);
                    $response['passengers'] = $passengers;
                    $response['post_passengers'] = $items;
                    $response['data_json'] = $params;
                    $response['stella'] = $params;
                } else {
                    $response = [
                        'process' => true,
                        'success' => true,
                        'message' => 'Lista de pasajeros guardada correctamente.',
                        'post_passengers' => []
                    ];
                }

                $log = new LogModal;
                $log->type = 'paxs';
                $log->user_id = 1;
                $log->client_id = null;
                $log->nrofile = (int) $file;
                $log->data = json_encode($passengers);
                $log->save();
            }

            if ($_type === 'quote') {
                $quote_id = $file;

                foreach ($passengers as $key => $value) {
                    if ($repeat === 1 && $modePassenger === 1) {
                        $value = $passengers[0];
                    }

                    $value = (object) $value;

                    if (isset($value->id) && $value->id > 0) {
                        $passenger = QuotePassenger::find($value->id);
                    } else {
                        $passenger = new QuotePassenger;
                    }

                    if (!empty($value->fecnac)) {
                        $fecnac = explode("/", $value->fecnac);
                        $value->fecnac = count($fecnac) > 1 ? $fecnac[2] . '-' . $fecnac[1] . '-' . $fecnac[0] : null;
                    }

                    if (is_array($value->tipdoc)) {
                        $value->tipdoc = $value->tipdoc['value'] ?? null;
                    }

                    $passenger->type = $value->tipo;
                    $passenger->first_name = $value->nombres;
                    $passenger->last_name = $value->apellidos;
                    $passenger->gender = $value->sexo;
                    $passenger->birthday = $value->fecnac;
                    $passenger->document_number = $value->nrodoc;
                    $passenger->doctype_iso = $value->tipdoc;
                    $passenger->country_iso = $value->nacion;
                    $passenger->email = $value->correo;
                    $passenger->phone = (!empty($value->celula)) ? ($value->phone_code ?? '') . $value->celula : '';
                    $passenger->quote_id = !empty($quote_id) ? $quote_id : $value->quote_id;
                    $passenger->address = $value->address ?? null;
                    $passenger->city_ifx_iso = $value->city_ifx_iso ?? null;
                    $passenger->is_direct_client = isset($value->is_direct_client) ? (int) $value->is_direct_client : 0;
                    $passenger->document_url = $value->document_url ?? '';

                    $passenger->dietary_restrictions = $value->resali;
                    $passenger->medical_restrictions = $value->resmed;
                    $passenger->notes = $value->observ;

                    $passenger->save();
                    $response[$passenger->id] = $passenger;
                }

                if (!empty($quote_id)) {
                    $this->store_history_logs($quote_id, [[
                        "type" => "update",
                        "slug" => "update_data_paxs",
                        "previous_data" => "",
                        "current_data" => "",
                        "description" => "Actualizó datos de pasajero"
                    ]]);
                }

                $response['process'] = true;
                $response['success'] = true;
                $response['message'] = 'Lista de pasajeros guardada correctamente.';
            }
        } catch (\Exception $ex) {
            $response = $this->throwError($ex);
        } finally {
            $params = $request->session()->get('params');
            $response['params'] = $params;
            return response()->json($response);
        }
    }

    public function getCitiesByIsoCountry($iso)
    {
        $_response = (array) $this->stellaService->getCitiesByIsoCountry($iso);
        return response()->json($_response);
    }

    public function getCountries(Request $request)
    {
        $lang = $request->input("lang");
        $countries = Country::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'country');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->get();

        return response()->json(['success' => true, 'data' => $countries]);
    }

    public function modal_update(Request $request)
    {
        try {
            $log = new LogModal;
            $log->type = 'paxs';
            $log->user_id = (int) $request->__get('user_id');
            $log->client_id = (int) $request->__get('client_id');
            $log->nrofile = $request->__get('nrofile');
            $log->data = json_encode($request->__get('data'));
            $log->save();

            // Notificar para saber que un cliente actualizó la info de los pasajeros..
            if ($request->__get('flag_notify') == 1) {
                $reservation = Reservation::where('file_code', '=', $log->nrofile)->first();
                $user = User::where('user_type_id', '=', 3)->where('id', '=', $reservation->executive_id)->first();

                $data = [
                    'file' => $reservation,
                    'user' => $user,
                ];

                $mail = Mail::to($user->email);
                $mail->bcc("juancarlos.huaman@tui.com");
                $mail->send(new \App\Mail\NotificationPaxsExecutive($data));
            }

            return response()->json([
                'type' => 'success',
                'log' => $log,
            ]);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }
}
