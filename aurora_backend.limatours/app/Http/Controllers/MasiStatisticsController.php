<?php

namespace App\Http\Controllers;

use App\Region;
use App\LogModal;
use App\Reservation;
use GuzzleHttp\Client;
use App\MasiFileDetail;
use App\MasiActivityJobLogs;
use Illuminate\Http\Request;
use App\Http\Stella\StellaService;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use GuzzleHttp\Psr7\Request as Psr7Request;

class MasiStatisticsController extends Controller
{
    private $ENDPOINT = '';
    // config('services.stella.domain')
    private $ENDPOINT_EXTRANET = 'https://extranet.litoapps.com/';
    private $SENDINBLUE_KEY = '';
    protected $stellaService;

    public function __construct(StellaService $stellaService)
    {
        $this->SENDINBLUE_KEY = config('services.sendinblue.api_key_v3');
        $this->ENDPOINT = config('services.stella.domain') . 'api/v1';
        $this->middleware('permission:masistatistics.read');

        $this->stellaService = $stellaService;
    }

    public function webhook_sendinblue(Request $request)
    {
        // --------------------------------------------
    }

    public function getRegions()
    {
        $regions = Region::all();
        $regions->prepend(['id' => '', 'title' => 'TODOS']);

        return response()->json(['result' => 'success', 'regions' => $regions]);
    }

    public function getCountriesByRegion(Request $request, $region_id)
    {
        $client = new Client();
        $queryParams = [
            'query' => [
                'region' => $region_id
            ]
        ];
        $countriesReq = $client->request('GET', $this->ENDPOINT . '/countries', $queryParams);
        $countries = json_decode($countriesReq->getBody()->getContents(), true)['data'];

        $countries = array_merge([
            [
                'pais' => 'TODOS',
                'region' => '',
            ]
        ], $countries);

        $countries = array_values(array_filter($countries, function ($pais) {
            return $pais['pais'] !== "U.S.A.";
        }));

        return response()->json(
            [
                'result' => 'success',
                'data' => $countries
            ]
        );
    }
    // public function getMarketsByRegion($region_id)
    // {
    //     $region = Region::find($region_id);
    //     return response()->json(['result' => 'success', 'markets' => ($region) ? $region->markets : []]);
    // }
    public function getClientsByCountryRegion($region_id, $country_name)
    {
        $country_name = ($country_name == 'ESTADOS UNIDOS') ? 'U.S.A.' : $country_name;

        $client = new Client();
        $queryParams = [
            'query' => [
                'country' => $country_name,
                'region' => $region_id
            ]
        ];
        $clientsReq = $client->request('GET', $this->ENDPOINT . '/clients', $queryParams);
        $clients = json_decode($clientsReq->getBody()->getContents(), true)['data'];

        $clients = array_merge([
            [
                'razon' => 'TODOS',
                'codigo' => '',
            ]
        ], $clients);

        return response()->json(
            [
                'result' => 'success',
                'data' => $clients
            ]
        );
    }
    // public function getClientsByMarket($market_code)
    // {
    //     $market = Market::where('code', '=', $market_code)->first();
    //     return response()->json(['result' => 'success', 'clients' => ($market) ? $market->clients : []]);
    // }
    public function getChatbotStatistics(Request $request)
    {
        $filter = $request->input('filter');
        $client = new Client();
        if (isset($filter) && $filter == 'byFile') {
            $queryParams = [
                'query' => [
                    'contactInfo' => null,
                    'fileType' => null,
                    'source' => null,
                    'filter' => $filter,
                    'file' => $request->input('file')
                ]
            ];
        } else {
            $queryParams = [
                'query' => [
                    'contactInfo' => null,
                    'fileType' => $request->input('fileType'),
                    'source' => null,
                    'filter' => null,
                    'fromDate' => $request->input('fromDate'),
                    'toDate' => $request->input('toDate'),
                    'regionCode' => ($request->input('regionCode')) ? $request->input('regionCode') : '',
                    'countryName' => ($request->input('countryCode')) ? $request->input('countryCode') : '',
                    'clientCode' => ($request->input('clientCode')) ? $request->input('clientCode') : ''
                ]
            ];
        }
        $queryParams['query']['source'] = 'function';
        $functionsReq = $client->request('GET', $this->ENDPOINT . '/masi/log/chatbot', $queryParams);
        $functions = json_decode($functionsReq->getBody()->getContents(), true)['data'];

        $queryParams['query']['source'] = 'channel';
        $channelsReq = $client->request('GET', $this->ENDPOINT . '/masi/log/chatbot', $queryParams);

        if (isset($filter)) {
            $total_files_with_phonenumber = 0;
            $total_files_with_email = 0;
        } else {
            $queryParams['query']['contactInfo'] = 'phonenumber';
            $withPhone = $client->request('GET', $this->ENDPOINT . '/masi/log/files', $queryParams);
            $data = json_decode($withPhone->getBody()->getContents(), true)['data'];
            $total_files_with_phonenumber = count($data);

            $queryParams['query']['contactInfo'] = 'email';
            $withEmail = $client->request('GET', $this->ENDPOINT . '/masi/log/files', $queryParams);
            $data = json_decode($withEmail->getBody()->getContents(), true)['data'];
            $total_files_with_email = count($data);

            $queryParams['query']['contactInfo'] = 'atleastone';
            $files_with_contact_data = $client->request('GET', $this->ENDPOINT . '/masi/log/files', $queryParams);
            $data = json_decode($files_with_contact_data->getBody()->getContents(), true)['data'];
            $total_files_with_contact_data = count($data);
        }
        $channels = json_decode($channelsReq->getBody()->getContents(), true)['data'];
        return response()->json([
            'result' => 'success',
            'data' => [
                'channels' => $channels,
                'functions' => $functions,
                'total' => [
                    'email' => $total_files_with_email,
                    'phonenumber' => $total_files_with_phonenumber,
                    'totals' => $total_files_with_contact_data
                ]
            ]
        ]);
    }

    public function search_general_notification(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        try {
            $filter = (object) $request->all();

            $filter->country = (!empty($filter->country) and $filter->country == 'ESTADOS UNIDOS') ? 'U.S.A.' : $filter->country;

            $params = [
                'filter' => (!empty($filter->file)) ? 'byFile' : 'all',
                'file' => $filter->file,
                'fileType' => (!empty($filter->product) and $filter->product != 'TODOS') ? $filter->product : 'all',
                'contactInfo' => 'all',
                'fromDate' => date("d/m/Y", strtotime($filter->from)),
                'toDate' => date("d/m/Y", strtotime($filter->to)),
                'regionCode' => $filter->region,
                'countryName' => (!empty($filter->country) and $filter->country != 'TODOS') ? $filter->country : '',
                'clientCode' => (!empty($filter->client) and $filter->client != 'TODOS') ? $filter->client : '',
            ];

            $response = $this->toArray($this->stellaService->masi_data($params));

            $files = Collect($response['data']);

            /*
            $file_numbers = Reservation::on('mysql_read')->select(['file_code', 'date_init'])
                ->whereBetween('date_init', [$from, $to])
                ->pluck('file_code')->toArray();

            $files = MasiFileDetail::on('mysql_read')
                ->select(['masi_file_detail.*', 'masi_activity_job_logs.message', 'masi_activity_job_logs.status_validation'])
                ->with(['reservation'])
                ->leftjoin('masi_activity_job_logs', 'masi_file_detail.file', '=', 'masi_activity_job_logs.file')
                ->where(function ($query) {
                    $query->orWhere('masi_activity_job_logs.message', 'like', 'No se encontraron%');
                    $query->orWhere('masi_activity_job_logs.message', 'like', 'Envio de%');
                    $query->orderBy('masi_activity_job_logs.message', 'ASC');
                });

            $files = $files->where(function ($query) use ($filter) {

                if($filter->product != '' AND $filter->product != 'TODOS')
                {
                    if($filter->product == 1)
                    {
                        $query->whereIn('masi_file_detail.product', [1, 5]);
                    }
                    else
                    {
                        $query->where('masi_file_detail.product', '=', $filter->product);
                    }
                }
                else
                {
                    // $query->whereIn('product', [1, 2]);
                }

                if($filter->region != '' AND $filter->region != 'TODOS')
                {
                    $query->where('masi_file_detail.area', '=', $filter->region);
                }
                else
                {
                    // $query->whereIn('area', [1, 2, 3]);
                }

                if($filter->country != '' AND $filter->country != 'TODOS')
                {
                    $countries = ['ESTADOS UNIDOS' => 'U.S.A.', 'U.S.A.' => 'ESTADOS UNIDOS'];

                    if(isset($countries[$filter->country]))
                    {
                        $query->where(function ($query) use ($countries, $filter) {
                            $query->orWhere('masi_file_detail.country', '=', $filter->country);
                            $query->orWhere('masi_file_detail.country', '=', $countries[$filter->country]);
                        });
                    }
                    else
                    {
                        $query->where('masi_file_detail.country', '=', $filter->country);
                    }
                }

                if($filter->client != '' AND $filter->client != 'TODOS')
                {
                    $query->where('masi_file_detail.client', '=', $filter->client);
                }
            });

            // $files = $files->whereBetween('created_at', [$from, $to]);
            $files = $files->whereIn('masi_file_detail.file', $file_numbers);
            // $files = $files->orderBy('message', 'ASC');

            $sql = $files->toSql(); $files = $files->get();

            // return $sql;

            */

            $files_ignore = [];
            $all_files = [];
            $files_with_data = [];
            $files_without_data = [];
            $files_modal = [];

            $files->each(function ($value, $key) use (
                &$files_ignore,
                &$all_files,
                &$files_with_data,
                &$files_without_data,
                &$files_modal
            ) {

                // $value = $value->toArray();

                $value['file'] = $value['nroref'];
                $value['status_validation'] = 0;
                $value['message'] = 'No se encontraron datos de pasajero.';

                if (strpos($value['phone_number'], "null") !== false) {
                    $value['phone_number'] = null;
                }

                if (!empty($value['phone_number']) || !empty($value['email'])) {
                    $value['status_validation'] = 1;
                    $value['message'] = 'Envio de correo / wsp.';
                }

                if (
                    !in_array($value['file'], $files_ignore) and
                    strpos($value['message'], 'Envio de') !== FALSE
                ) {
                    if ($value['status_validation'] == 1) {
                        $files_ignore[] = $value['file'];
                        $all_files[] = $value;

                        $files_with_data[] = $value;
                        $files_modal[] = $value['file'];
                    } else {
                        $value['message'] = 'No se encontraron datos de pasajero.';
                        $value['status_validation'] = 0;
                    }
                }

                if (
                    !in_array($value['file'], $files_ignore) and
                    strpos($value['message'], 'No se encontraron') !== FALSE
                ) {
                    $files_ignore[] = $value['file'];
                    $all_files[] = $value;

                    $files_without_data[] = $value;
                }
            });

            $files_with_data_modal = LogModal::whereIn('nrofile', $files_modal)
                ->where('user_id', '<>', 1)
                ->where(function ($query) {
                    $query->orWhereNull('client_id');
                    $query->orWhere('client_id', '=', 0);
                })->where(function ($query) {
                    $query->orWhereNull('user_id');
                    $query->orWhere('user_id', '=', 0);
                })
                ->where('type', '=', 'paxs')
                ->pluck('nrofile')->toArray();

            $files_with_data_modal = array_unique($files_with_data_modal);

            foreach ($all_files as $key => $file) {
                $all_files[$key]['flag_from'] = '-';
                $all_files[$key]['flag_data'] = 'SIN DATOS';

                $executiveCode = trim($file['executive']);
                $userId = \App\User::where('code', $executiveCode)->value('id');
                $departmentTeamName = null;
                if ($userId) {
                    $departmentTeamName = DB::table('employees as e')
                        ->join('department_teams as dt', 'dt.id', '=', 'e.department_team_id')
                        ->where('e.user_id', $userId)
                        ->value('dt.name');
                }
                $all_files[$key]['area'] = $departmentTeamName ?? null;

                if ($file['status_validation'] == 1) {
                    $all_files[$key]['flag_data'] = 'CON DATOS';
                    $all_files[$key]['flag_from'] = 'COMERCIAL';

                    if (in_array($file['file'], $files_with_data_modal)) {
                        $all_files[$key]['flag_from'] = 'OPE/LANDING';
                    }
                }
            }

            return response()->json([
                'files_modal' => $files_modal,
                // 'files_ignore' => $files_ignore,
                'files_ignore_count' => count($files_ignore),
                'all_files' => $all_files,
                'all_files_count' => count($all_files),
                'files_without_data' => $files_without_data,
                'files_with_data' => $files_with_data,
                // 'with_data' => $array_with_data,
                'with_data_count' => count($files_with_data) - count($files_with_data_modal),
                'with_data_modal' => $files_with_data_modal,
                'with_data_count_modal' => count($files_with_data_modal),
                // 'without_data' => $array_without_data,
                'without_data_count' => count($files_without_data),
                'total' => count($files_with_data) + count($files_without_data),
                'type' => 'success',
            ]);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function search_whatsapp_notification(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        try {
            $filter = (object) $request->all();

            $client = new Client([
                "verify" => false,
                'base_uri' => config('services.masi.domain')
            ]);

            if ($filter->from != '' and $filter->from != NULL) {
                $from = explode("/", $filter->from);

                if (count($from) > 1) {
                    $from = $from[2] . '-' . $from[1] . '-' . $from[0];
                }
            }

            if ($filter->to != '' and $filter->to != NULL) {
                $to = explode("/", $filter->to);

                if (count($to) > 1) {
                    $to = $to[2] . '-' . $to[1] . '-' . $to[0];
                }
            }

            $file = $filter->file;

            $request = $client->request('post', 'api/statistics', [
                'form_params' => [
                    'lang' => 'es',
                    'from' => $from,
                    'to' => $to,
                    'file' => $file,
                    'filter' => $filter,
                ]
            ]);
            $response = json_decode($request->getBody()->getContents(), true);

            return response()->json($response);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function search_mailing_notification(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        try {
            $filter = (object) $request->all();

            // 1. Date Formatting
            $from = null;
            $to = null;

            if (!empty($filter->from)) {
                $dateParts = explode("/", $filter->from);
                if (count($dateParts) > 1) {
                    $from = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];
                }
            }

            if (!empty($filter->to)) {
                $dateParts = explode("/", $filter->to);
                if (count($dateParts) > 1) {
                    $to = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];
                }
            }

            // 2. Base Query Construction
            $query = MasiActivityJobLogs::on('mysql_read')
                ->where('status_validation', '=', 1)
                ->whereNotNull('file');

            // Apply Relationship Filters
            if (
                (!empty($filter->product) && $filter->product != 'TODOS') ||
                (!empty($filter->region) && $filter->region != 'TODOS') ||
                (!empty($filter->country) && $filter->country != 'TODOS') ||
                (!empty($filter->client) && $filter->client != 'TODOS')
            ) {
                $query->whereHas('detail', function ($q) use ($filter) {
                    if (!empty($filter->product) && $filter->product != 'TODOS') {
                        $q->where('product', '=', $filter->product);
                    }
                    if (!empty($filter->region) && $filter->region != 'TODOS') {
                        $q->where('area', '=', $filter->region);
                    }
                    if (!empty($filter->country) && $filter->country != 'TODOS') {
                        $q->where('country', '=', $filter->country);
                    }
                    if (!empty($filter->client) && $filter->client != 'TODOS') {
                        $q->where('client', '=', $filter->client);
                    }
                });
            }

            // Apply Date Filter
            if ($from && $to) {
                $query->whereBetween('created_at', [$from, $to]);
            }

            // Apply Status Filter (Email or WhatsApp validity)
            $query->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->where('type_send', '=', 'email')
                        ->whereNotNull('status_email');
                })->orWhere(function ($sub) {
                    $sub->where('type_send', '=', 'whatsapp')
                        ->whereNotNull('status_wsp');
                });
            });

            // 3. Export Logic (Still needs raw data if exporting)
            if ((int) @$filter->download === 1) {
                $mailing = $query->orderBy('created_at', 'DESC')->get();
                return Excel::download(
                    new \App\Exports\MasiStatisticsExportReport(2, ['mailing' => $mailing]),
                    'mailing_global.xlsx'
                );
            }

            // 4. Aggregation Logic (The main performance boost)
            // We construct a massive SELECT statement to count everything in one go.

            $selects = [];

            // Count unique people (file - nropax)
            // Note: handling "nropax > 0" logic inside SQL
            $selects[] = "COUNT(DISTINCT CONCAT(file, '-', CASE WHEN nropax > 0 THEN nropax ELSE 0 END)) as quantity_people";

            // Count unique valid WhatsApps (file)
            $selects[] = "COUNT(DISTINCT CASE WHEN type_send = 'whatsapp' THEN file END) as quantity_wsp";

            // Count unique valid Emails (file)
            $selects[] = "COUNT(DISTINCT CASE WHEN type_send = 'email' THEN file END) as quantity_mailing";

            // Count Global Interactions (status matches specific subtypes keys)
            // This replicates the logic: if ($value->status_wsp == $k) $quantity_global += 1;
            // We look at specific status codes:
            // WSP: 3=failed, 4=sent, 5=delivered, 6=read
            // Email: 0=soft, 1=hard, 2=invalid, 3=error, 4=delivered, 5=opened, 6=click
            $selects[] = "SUM(CASE
        WHEN type_send = 'whatsapp' AND status_wsp IN (3,4,5,6) THEN 1
        WHEN type_send = 'email' AND status_email IN (0,1,2,3,4,5,6) THEN 1
        ELSE 0 END) as quantity_global";


            $types = ['weekly', 'day_before', 'daily', 'survey'];
            $subtypes = [
                'mailing' => ['soft_bounce', 'hard_bounce', 'invalid_email', 'error', 'delivered', 'unique_opened', 'click'],
                'wsp' => ['', '', '', 'failed', 'sent', 'delivered', 'read'],
            ];

            foreach ($types as $typeKey => $type) {
                // Base counts for types per channel
                // Logic: if ($value->type_message == $type) ...
                // Note: The original PHP code had logic to prevent double counting based on files for these specific metrics.
                // Replicating "unique file-nropax" counting for specific types in SQL:

                // WSP Type Counts
                $selects[] = "COUNT(DISTINCT CASE WHEN type_send = 'whatsapp' AND type_message = '{$type}' THEN CONCAT(file, '-', CASE WHEN nropax > 0 THEN nropax ELSE 0 END) END) as quantity_wsp_{$type}";

                // Email Type Counts
                $selects[] = "COUNT(DISTINCT CASE WHEN type_send = 'email' AND type_message = '{$type}' THEN CONCAT(file, '-', CASE WHEN nropax > 0 THEN nropax ELSE 0 END) END) as quantity_mailing_{$type}";

                // Subtype Counts (WSP)
                foreach ($subtypes['wsp'] as $k => $v) {
                    if ($v != '') {
                        // Logic: ($value->status_wsp == $k and $k <= 3) or ($value->status_wsp >= $k and $k > 3)
                        // Since keys are 3,4,5,6 for valid statuses, the condition $k<=3 only applies to 3 (failed).
                        // Simplified: status_wsp = k OR (status_wsp >= k AND k > 3)
                        $condition = "status_wsp = {$k}";
                        if ($k > 3) {
                            $condition = "status_wsp >= {$k}";
                        }

                        $selects[] = "SUM(CASE WHEN type_send = 'whatsapp' AND type_message = '{$type}' AND ({$condition}) THEN 1 ELSE 0 END) as quantity_wsp_{$v}_{$type}";
                    }
                }

                // Subtype Counts (Mailing)
                foreach ($subtypes['mailing'] as $k => $v) {
                    // Logic: ($value->status_email == $k and $k <= 3) or ($value->status_email >= $k and $k > 3)
                    $condition = "status_email = {$k}";
                    if ($k > 3) {
                        $condition = "status_email >= {$k}";
                    }

                    $selects[] = "SUM(CASE WHEN type_send = 'email' AND type_message = '{$type}' AND ({$condition}) THEN 1 ELSE 0 END) as quantity_mailing_{$v}_{$type}";
                }
            }

            // Execute the aggregated query
            // We use first() because all results are aggregates in a single row
            $results = $query->selectRaw(implode(', ', $selects))->first();

            // Prepare response data
            $data = $results->toArray();
            $data['type'] = 'success';

            // Ensure integers
            foreach ($data as $key => $val) {
                if ($key !== 'type') {
                    $data[$key] = (int)$val;
                }
            }

            return response()->json($data);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function quantity_people(Request $request)
    {
        set_time_limit(0);
        // Ya no necesitamos memoria ilimitada porque no traeremos todos los registros a PHP
        // ini_set('memory_limit', '-1');

        try {
            $filter = (object) $request->all();

            // 1. Formateo de Fechas (Optimizado con Carbon para robustez, o mantenemos tu lógica manual si prefieres)
            $from = null;
            $to = null;

            if (!empty($filter->from)) {
                $dateParts = explode("/", $filter->from);
                if (count($dateParts) > 1) {
                    $from = $dateParts[2] . '-' . str_pad($dateParts[1], 2, '0', STR_PAD_LEFT) . '-' . $dateParts[0];
                }
            }

            if (!empty($filter->to)) {
                $dateParts = explode("/", $filter->to);
                if (count($dateParts) > 1) {
                    $to = $dateParts[2] . '-' . str_pad($dateParts[1], 2, '0', STR_PAD_LEFT) . '-' . $dateParts[0];
                }
            }

            // 2. Construcción de la Consulta Base
            $query = MasiActivityJobLogs::on('mysql_read')
                ->where('status_validation', '=', 1)
                ->whereNotNull('file')
                ->whereBetween('created_at', [$from, $to])
                ->where('message', 'like', 'Envio%')
                ->where('type_message', '=', 'survey');

            // 3. Filtros de Relación (Optimizados)
            // Solo agregamos el whereHas si realmente hay filtros que aplicar
            $regions = $filter->region ?? []; // Asegurar que sea array
            $clients = $filter->client ?? [];

            if (!empty($regions) || !empty($clients)) {
                $query->whereHas('detail', function ($q) use ($regions, $clients) {
                    if (!empty($regions)) {
                        $q->whereIn('area', $regions);
                    }
                    if (!empty($clients)) {
                        $q->whereIn('client', $clients);
                    }
                });
            }

            // 4. Filtro opcional nroref
            if (!empty($filter->nroref)) {
                $query->where('file', '=', $filter->nroref);
            }

            $quantity_people = $query->count(DB::raw("DISTINCT CONCAT(file, '-', IF(nropax > 0, nropax, 0))"));

            return response()->json([
                'type' => 'success',
                'from' => $from,
                'to' => $to,
                'quantity_people' => $quantity_people,
            ]);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function getNotificationsStatistics(Request $request)
    {
        set_time_limit(0);

        $filter = $request->input('filter');
        $client = new Client();
        $message_initial_status = [
            'bounces' => 0,
            'hardBounces' => 0,
            'softBounces' => 0,
            'delivered' => 0,
            'spam' => 0,
            'requests' => 0,
            'opened' => 0,
            'clicks' => 0,
            'invalid' => 0,
            'deferred' => 0,
            'blocked' => 0,
            'unsubscribed' => 0,
            'other' => 0,
        ];
        //Todo Tipo de mensaje ID / Descripción
        // 1 -> 1 semana antes
        // 2 -> 1 dia antes
        // 3 -> Dia a dia
        // 4 -> Ultimo dia
        // 5 -> Mensaje despedida
        $message_types = [1, 2, 3, 5];
        $response = [
            'whatsapp' => [
                1 => 0,
                2 => 0,
                3 => 0,
                5 => 0,
                'total' => 0

            ],
            'mailing' => [
                1 => $message_initial_status,
                2 => $message_initial_status,
                3 => $message_initial_status,
                5 => $message_initial_status,
                'total' => $message_initial_status
            ],
            'totalSent' => 0,
            'totalFiles' => ['totals' => 0, 'phonenumber' => 0, 'email' => 0]
        ];
        if (isset($filter) && $filter == 'byFile') {
            $queryParams = [
                'query' => [
                    'contactInfo' => null,
                    'notificationType' => null,
                    'source' => null,
                    'filter' => $filter,
                    'file' => $request->input('file'),
                ]
            ];
        } else {
            $queryParams = [
                'query' => [
                    'contactInfo' => null,
                    'notificationType' => null,
                    'source' => null,
                    'filter' => null,
                    'file' => null,
                    'fromDate' => $request->input('fromDate'),
                    'toDate' => $request->input('toDate'),
                    'regionCode' => ($request->input('regionCode')) ? $request->input('regionCode') : '',
                    'countryName' => ($request->input('countryCode')) ? $request->input('countryCode') : '',
                    'clientCode' => ($request->input('clientCode')) ? $request->input('clientCode') : '',
                ]
            ];
        }
        $queryParams['query']['contactInfo'] = 'phonenumber';
        $withPhone = $client->request('GET', $this->ENDPOINT . '/masi/log/files', $queryParams);
        try {
            $data = json_decode($withPhone->getBody()->getContents(), true)['data'];
            $response['totalFiles']['phonenumber'] = count($data);
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }


        $queryParams['query']['contactInfo'] = 'email';
        $withEmail = $client->request('GET', $this->ENDPOINT . '/masi/log/files', $queryParams);
        try {
            $data = json_decode($withEmail->getBody()->getContents(), true)['data'];
            $response['totalFiles']['email'] = count($data);
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }


        $queryParams['query']['contactInfo'] = 'atleastone';
        $files_with_contact_data = $client->request('GET', $this->ENDPOINT . '/masi/log/files', $queryParams);
        try {
            $data = json_decode($files_with_contact_data->getBody()->getContents(), true)['data'];
            $response['totalFiles']['totals'] = count($data);
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }

        //Se obtiene datos desde informix, según parámetros (WHATSAPP)
        try {
            $whatsappReq = $client->request('GET', $this->ENDPOINT . '/masi/log/whatsapp', $queryParams);
            $whatsappRes = json_decode($whatsappReq->getBody()->getContents(), true)['data'];

            $response['whatsappRes'] = $whatsappRes;
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }


        if (isset($whatsappRes) && count($whatsappRes) > 0) { //Si se obtuvieron datos desde informix
            //Se itera en el log obtenido para trabajar por rows
            foreach ($whatsappRes as $whatsapp) {
                $response['whatsapp'][$whatsapp['type']] = $whatsapp['total'];
                if ($whatsapp['type'] == 3) {
                    $response['whatsapp']['total'] += $whatsapp['total'];
                }
            }
        }
        foreach ($message_types as $messageType) {
            try {
                //Se define el tipo de mensaje: 7 días antes, 1 día antes...
                $queryParams['query']['notificationType'] = $messageType;
                //Se obtiene datos desde informix, según parámetros (MAILING)
                $mailing_request = $client->request('GET', $this->ENDPOINT . '/masi/log/mailing', $queryParams);
                $mailing_log_database = json_decode($mailing_request->getBody()->getContents(), true)['data'];
                if (isset($mailing_log_database) && count($mailing_log_database) > 0) { //Si se obtuvieron datos desde informix
                    $message_event_types = [
                        'bounces',
                        'hardBounces',
                        'softBounces',
                        'delivered',
                        'spam',
                        'requests',
                        'opened',
                        'clicks',
                        'invalid',
                        'deferred',
                        'blocked',
                        'unsubscribed',
                    ];
                    //Se itera en el log obtenido para trabajar por rows
                    foreach ($mailing_log_database as $mailing_log) {
                        $messageId = trim($mailing_log['messageid']);
                        //                        if (strlen($messageId) > 0) { //Si se ha obtenido el messageID desde informix
                        //                            //Solicitando información de eventos del messageId obtenido
                        //                            $sendinblueReq = $client->request('GET', 'https://api.sendinblue.com/v3/smtp/statistics/events',
                        //                                [
                        //                                    'query' => [
                        //                                        'messageId' => $messageId
                        //                                    ],
                        //                                    'headers' => [
                        //                                        'Accept' => 'application/json',
                        //                                        'Api-key' => $this->SENDINBLUE_KEY
                        //                                    ],
                        //                                    'verify' => false,
                        //                                ]);
                        //                            $sendinblueRes = json_decode($sendinblueReq->getBody()->getContents(), true);
                        //                            if (isset($sendinblueRes['events'])) { //Si se obtuvo información del mensaje por su messageID
                        //                                foreach ($sendinblueRes['events'] as $event) {
                        //                                    //Si el evento se encuentra dentro de los que se requieren ($message_event_types)
                        //                                    if (in_array($event['event'], $message_event_types)) {
                        //                                        //Se suma uno al contador del tipo de mensaje y evento
                        //                                        $response['mailing'][$messageType][$event['event']] += 1;
                        //                                    }
                        //                                }
                        //                            }
                        //                        }
                    }
                    $response['mailing'][$messageType]['bounces'] = $response['mailing'][$messageType]['hardBounces'] +
                        $response['mailing'][$messageType]['softBounces'];
                    $response['mailing'][$messageType]['other'] = $response['mailing'][$messageType]['requests'] -
                        $response['mailing'][$messageType]['delivered'] - $response['mailing'][$messageType]['bounces'];
                    foreach ($message_event_types as $event_type) {
                        $response['mailing']['total'][$event_type] += $response['mailing'][$messageType][$event_type];
                    }
                    $response['mailing']['total']['other'] += $response['mailing'][$messageType]['other'];
                }
            } catch (\Exception $exception) {
                throw new \Exception($exception->getMessage());
            }
        }
        $response['totalSent'] = $response['mailing']['total']['requests'] + $response['whatsapp']['total'];

        return response()->json(
            [
                'result' => 'success',
                'data' => $response
            ]
        );
    }

    public function getFileStatistics(Request $request)
    {
        $filter = $request->input('filter');
        $client = new Client();
        if (isset($filter) && $filter == 'byFile') {
            $queryParams = [
                'query' => [
                    'fileType' => null,
                    'source' => null,
                    'contactInfo' => null,
                    'filter' => $filter,
                    'file' => $request->input('file')
                ]
            ];
        } else {
            $queryParams = [
                'query' => [
                    'fileType' => ($request->input('fileType')) ? $request->input('fileType') : '',
                    'source' => null,
                    'contactInfo' => null,
                    'filter' => null,
                    'fromDate' => $request->input('fromDate'),
                    'toDate' => $request->input('toDate'),
                    'regionCode' => ($request->input('regionCode')) ? $request->input('regionCode') : '',
                    'countryName' => ($request->input('countryCode')) ? $request->input('countryCode') : '',
                    'clientCode' => ($request->input('clientCode')) ? $request->input('clientCode') : ''
                ]
            ];
        }
        if (isset($filter) && $filter == 'byFile') {
            $fileData = $client->request('GET', $this->ENDPOINT . '/masi/log/files', $queryParams);
            $data = json_decode($fileData->getBody()->getContents(), true)['data'];
            return response()->json(['data' => $data]);
        }

        //Todo FILES  SIN EMAIL O TELEFONO:
        $queryParams['query']['contactInfo'] = 'empty';
        $noDataReq = $client->request('GET', $this->ENDPOINT . '/masi/log/files', $queryParams);
        $data_without_contact = json_decode($noDataReq->getBody()->getContents(), true)['data'];
        $data_files = collect();
        foreach ($data_without_contact as $item) {
            $data_files->add([
                'nroref' => trim($item['nroref']),
                'type' => trim($item['type']),
                'region' => trim($item['region']),
                'country' => trim($item['country']),
                'client' => trim($item['client']),
                'arrive_date' => $item['arrive_date'],
                'executive' => trim($item['executive']),
                'phone_number' => trim($item['phone_number']),
                'email' => trim($item['email']),
            ]);
        }

        //Todo FILES CON AL MENOS 1 EMAIL O TELEFONO:
        $queryParams['query']['contactInfo'] = 'atleastone';
        $withDataReq = $client->request('GET', $this->ENDPOINT . '/masi/log/files', $queryParams);
        $data_with_contact = json_decode($withDataReq->getBody()->getContents(), true)['data'];
        foreach ($data_with_contact as $item) {
            $data_files->add([
                'nroref' => trim($item['nroref']),
                'type' => trim($item['type']),
                'region' => trim($item['region']),
                'country' => trim($item['country']),
                'client' => trim($item['client']),
                'arrive_date' => $item['arrive_date'],
                'executive' => trim($item['executive']),
                'phone_number' => trim($item['phone_number']),
                'email' => trim($item['email']),
            ]);
        }

        $total_files_with_contact_data = 0;
        $total_files_without_contact_data = 0;

        //Todo Agrupamos los registros por el numero de file
        $data_group_files = $data_files->groupBy('nroref');
        //Todo Recorremos lo que agrupamos y buscamos los que tienen datos
        foreach ($data_group_files as $key => $data_group_file) {
            //Todo Filtramos los files que tienen datos
            $filter_count_with_data = $data_group_file->filter(function ($value, $key) {
                return !empty($value['phone_number']) or !empty($value['email']);
            });
            //Todo Si tiene datos de los pasajeros contamos mas 1
            if ($filter_count_with_data->count() > 0) {
                $total_files_with_contact_data++;
            } else {
                $total_files_without_contact_data++;
            }
        }


        return response()->json(
            [
                'result' => 'success',
                'data' => [
                    'with_data' => $total_files_with_contact_data,
                    'without_data' => $total_files_without_contact_data,
                    'total' => $total_files_without_contact_data + $total_files_with_contact_data
                ]
            ]
        );
    }

    public function exportFileStatistics(Request $request)
    {
        $client = new Client();
        $queryParams = [
            'query' => [
                'fileType' => null,
                'source' => null,
                'contactInfo' => null,
                'filter' => null,
                'fromDate' => $request->input('fromDate'),
                'toDate' => $request->input('toDate'),
                'regionCode' => ($request->input('regionCode')) ? $request->input('regionCode') : '',
                'countryName' => ($request->input('countryCode')) ? $request->input('countryCode') : '',
                'clientCode' => ($request->input('clientCode')) ? $request->input('clientCode') : ''
            ]
        ];
        if (!$request->input('fileType')) {

            //Todo Traigo los files que son de tipo FITS
            //            $queryParams['query']['fileType'] = 'fits';
            $dataRequest = $client->request('GET', $this->ENDPOINT . '/masi/log/files', $queryParams);
            $data_fits = json_decode($dataRequest->getBody()->getContents(), true)['data'];
            $data_files = collect();
            foreach ($data_fits as $item) {
                $data_files->add([
                    'nroref' => trim($item['nroref']),
                    'type' => trim($item['type']),
                    'region' => trim($item['region']),
                    'country' => trim($item['country']),
                    'client' => trim($item['client']),
                    'arrive_date' => $item['arrive_date'],
                    'executive' => trim($item['executive']),
                    'phone_number' => trim($item['phone_number']),
                    'email' => trim($item['email']),
                ]);
            }

            $data_group_files = $data_files->groupBy('nroref');
            $files = collect();
            $files_without_data = collect();
            foreach ($data_group_files as $data_group_file) {
                //Todo Filtramos los files que tienen datos
                $filter_count_with_data = $data_group_file->filter(function ($value, $key) {
                    return !empty($value['phone_number']) or !empty($value['email']);
                });
                if ($filter_count_with_data->count() > 0) {
                    $filter_count_with_data = $filter_count_with_data->values();
                    $files->add([
                        'nroref' => trim($filter_count_with_data[0]['nroref']),
                        'type' => trim($filter_count_with_data[0]['type']),
                        'region' => trim($filter_count_with_data[0]['region']),
                        'country' => trim($filter_count_with_data[0]['country']),
                        'client' => trim($filter_count_with_data[0]['client']),
                        'arrive_date' => $filter_count_with_data[0]['arrive_date'],
                        'executive' => trim($filter_count_with_data[0]['executive']),
                        'phone_number' => $filter_count_with_data->implode('phone_number', '; '),
                        'email' => $filter_count_with_data->implode('email', '; '),
                    ]);
                } else {
                    $files_without_data->add([
                        'nroref' => trim($data_group_file[0]['nroref']),
                        'type' => trim($data_group_file[0]['type']),
                        'region' => trim($data_group_file[0]['region']),
                        'country' => trim($data_group_file[0]['country']),
                        'client' => trim($data_group_file[0]['client']),
                        'arrive_date' => $data_group_file[0]['arrive_date'],
                        'executive' => trim($data_group_file[0]['executive']),
                        'phone_number' => '',
                        'email' => '',
                    ]);
                }
            }
        } else {
            $queryParams['query']['fileType'] = $request->input('fileType');
            $dataRequest = $client->request('GET', $this->ENDPOINT . '/masi/log/files', $queryParams);
            $data = json_decode($dataRequest->getBody()->getContents(), true)['data'];
            $data_files = collect();
            foreach ($data as $item) {
                $data_files->add([
                    'nroref' => trim($item['nroref']),
                    'type' => trim($item['type']),
                    'region' => trim($item['region']),
                    'country' => trim($item['country']),
                    'client' => trim($item['client']),
                    'arrive_date' => $item['arrive_date'],
                    'executive' => trim($item['executive']),
                    'phone_number' => trim($item['phone_number']),
                    'email' => trim($item['email']),
                ]);
            }

            $data_group_files = $data_files->groupBy('nroref');
            $files = collect();
            $files_without_data = collect();
            foreach ($data_group_files as $data_group_file) {
                //Todo Filtramos los files que tienen datos
                $filter_count_with_data = $data_group_file->filter(function ($value, $key) {
                    return !empty($value['phone_number']) or !empty($value['email']);
                });

                if ($filter_count_with_data->count() > 0) {
                    $filter_count_with_data = $filter_count_with_data->values();
                    $files->add([
                        'nroref' => trim($filter_count_with_data[0]['nroref']),
                        'type' => trim($filter_count_with_data[0]['type']),
                        'region' => trim($filter_count_with_data[0]['region']),
                        'country' => trim($filter_count_with_data[0]['country']),
                        'client' => trim($filter_count_with_data[0]['client']),
                        'arrive_date' => $filter_count_with_data[0]['arrive_date'],
                        'executive' => trim($filter_count_with_data[0]['executive']),
                        'phone_number' => $filter_count_with_data->implode('phone_number', '; '),
                        'email' => $filter_count_with_data->implode('email', '; '),
                    ]);
                } else {
                    $files_without_data->add([
                        'nroref' => trim($data_group_file[0]['nroref']),
                        'type' => trim($data_group_file[0]['type']),
                        'region' => trim($data_group_file[0]['region']),
                        'country' => trim($data_group_file[0]['country']),
                        'client' => trim($data_group_file[0]['client']),
                        'arrive_date' => $data_group_file[0]['arrive_date'],
                        'executive' => trim($data_group_file[0]['executive']),
                        'phone_number' => '',
                        'email' => '',
                    ]);
                }
            }
        }
        return Excel::download(
            new  \App\Exports\MasiStatisticsReportGeneral($files, $files_without_data),
            'información_de_contacto_por_file.xlsx'
        );
    }

    public function downloadExcel(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        try {
            $filter = (object) $request->all();
            $type = $filter->type;

            if ($type === 3) {
                $filename = 'reporte-ots';

                $client = new \GuzzleHttp\Client();
                //                $_request = new Psr7Request('POST', $this->ENDPOINT_EXTRANET . "masi/api.php?ctrl=poll&fx=getResultsOTS");
                //                $_request = new Psr7Request('POST', "https://extranet.limatours.dev/masi/api.php?ctrl=poll&fx=getResultsOTS");
                $_request = new Psr7Request('POST', config('services.files_onedb.domain') . "files/polls/resultsOTS");

                // Send Request
                $response = $client->send($_request, [
                    'headers' => ['Content-Type' => 'application/json'],
                    'body' => json_encode($this->toArray($filter))
                ]);
            } else {
                $filename = ($type == 0) ? 'reporte-general' : 'reporte-desglosado';

                $client = new \GuzzleHttp\Client();
                //                $_request = new Psr7Request('POST', $this->ENDPOINT_EXTRANET . "masi/api.php?ctrl=poll&fx=getResults");
                //                $_request = new Psr7Request('POST', "https://extranet.limatours.dev/masi/api.php?ctrl=poll&fx=getResults");
                $_request = new Psr7Request('POST', config('services.files_onedb.domain') . "files/polls/results");

                // Send Request
                $response = $client->send($_request, [
                    'headers' => ['Content-Type' => 'application/json'],
                    'body' => json_encode($this->toArray($filter))
                ]);
            }

            $data = json_decode($response->getBody()->getContents(), true);
            // ["data"]
            return Excel::download(
                new \App\Exports\MasiStatisticsExportReport($type, $data["data"]),
                $filename . '.xlsx'
            );
        } catch (\Exception $ex) {
            return response()->json($this->throwError($ex));
        }
    }

    public function downloadExcelMKT(Request $request, $reference_id)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        try {
            $filter = [];
            $type = 0;
            $filename = '';

            if ($reference_id == 7793) {
                $type = 4;
                $filename = 'reporte';

                $filter = [
                    'date_from' => '01/01/2025',
                    'date_to' => '30/10/2025',
                    'services' => ['CUZ557', 'CUZ401', 'CUZ431', 'CUZ441', 'MPI500'],
                    'destiny' => 4,
                ];

                $client = new Client();
                $_request = new Psr7Request('POST', config('services.files_onedb.domain') . "files/polls/resultsMKT");

                // Send Request
                $response = $client->send($_request, [
                    'headers' => ['Content-Type' => 'application/json'],
                    'body' => json_encode($this->toArray($filter))
                ]);
            }

            if ($type > 0) {
                $data = json_decode($response->getBody()->getContents(), true);

                return Excel::download(
                    new \App\Exports\MasiStatisticsExportReport($type, $data["data"]),
                    $filename . '.xlsx'
                );
            }
        } catch (\Exception $ex) {
            return response()->json($this->throwError($ex));
        }
    }

    public function downloadExcelCanada(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        try {
            $type = 4;
            $filename = 'reporte';

            $filter = [
                'date_from' => '01/01/2025',
                'date_to' => date("d/m/Y"),
                'services' => [],
                'destiny' => 4,
            ];

            $client = new Client();
            $_request = new Psr7Request('POST', config('services.files_onedb.domain') . "files/polls/resultsCanada");

            // Send Request
            $response = $client->send($_request, [
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode($this->toArray($filter))
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return Excel::download(
                new \App\Exports\MasiStatisticsExportReport($type, $data["data"]),
                $filename . '.xlsx'
            );
        } catch (\Exception $ex) {
            return response()->json($this->throwError($ex));
        }
    }
}
