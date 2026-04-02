<?php

namespace App\Console\Commands;

use App\File;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Http\Stella\StellaService;
use App\FileImportLog;
use App\FileService;
use App\LogOrder;
use App\Http\Traits\Files;
use DateTime;

class MigrateLogOrders extends Command
{
    protected $stella_service;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:migrate_logs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->stella_service = new StellaService;
//         $this->files_service = new Files;
    }

    public function throwError($ex)
    {
        return [
            'file' => $ex->getFile(),
            'line' => $ex->getLine(),
            'message' => $ex->getMessage(),
            'type' => 'error',
        ];
    }

    public function checkDateFormat($date1, $date2)
    {
        $date1 = strtotime($date1); $date2 = strtotime($date2);

        if ($date1>$date2)
        {
            $tmp=$date1; $date1=$date2;
            $date2=$tmp; unset($tmp); $sign=-1;
        }
        else $sign = 1;
        if ($date1==$date2) return 0;
        $days = 0;
        $working_days = array(1,2,3,4,5); // Monday-->Friday
        $working_hours = array(9, 18); // from 9:00(am) to 18
        $current_date = $date1;
        $beg_h = floor($working_hours[0]);
        $beg_m = 0;
        $end_h = floor($working_hours[1]);
        $end_m = 0;
        $seconds = 0;
        // setup the very next first working timestamp
        if (!in_array(date('w',$current_date) , $working_days)) {
            // the current day is not a working day
            // the current timestamp is set at the begining of the working day
            $current_date = mktime( $beg_h, $beg_m, 0, date('n',$current_date), date('j',$current_date), date('Y',$current_date) );
            // search for the next working day
            while ( !in_array(date('w',$current_date) , $working_days) )
            {
                $current_date += 24*3600;
            } // next day
        }
        else
        {
            // check if the current timestamp is inside working hours
            $date0 = mktime( $beg_h, $beg_m, 0, date('n',$current_date), date('j',$current_date), date('Y',$current_date) );
            // it's before working hours, let's update it
            if ($current_date<$date0) $current_date = $date0;
            $date3 = mktime( $end_h, $end_m, 0, date('n',$current_date), date('j',$current_date), date('Y',$current_date) );

            if ($date3<$current_date)
            {
                // outch ! it's after working hours, let's find the next working day
                $current_date += 24*3600; // the day after // and set timestamp as the begining of the working day
                $current_date = mktime( $beg_h, $beg_m, 0, date('n',$current_date), date('j',$current_date), date('Y',$current_date) );

                while ( !in_array(date('w',$current_date) , $working_days) )
                {
                    $current_date += 24*3600; // next day
                }
            }

            // so, $current_date is now the first working timestamp available...
            // calculate the number of seconds from current timestamp to the end of the working day
            $date0 = mktime( $end_h, $end_m, 0, date('n',$current_date), date('j',$current_date), date('Y',$current_date) );

            if(date("Y-m-d", $date2) == date("Y-m-d", $current_date))
            {
                $date0 = $date2;
            }

            $seconds = $date0-$current_date;
            // printf("\nFrom %s To %s : %d hours\n",date('d/m/y H:i',$date1),date('d/m/y H:i',$date0),$seconds/3600);
            // calculate the number of days from the current day to the end day
            $date3 = mktime( $beg_h, $beg_m, 0, date('n',$date2), date('j',$date2), date('Y',$date2) );

            while ( $current_date < $date3 )
            {
                $current_date += 24 * 3600; // next day
                if (in_array(date('w',$current_date) , $working_days) ) $days++;
                // it's a working day
            }
        }

        if ($days>0) $days--; //because we've allready count the first day (in $seconds)
        // printf("\nFrom %s To %s : %d working days\n",date('d/m/y H:i',$date1),date('d/m/y H:i',$date3),$days);
        // check if end's timestamp is inside working hours
        $date0 = mktime( $end_h, $end_m, 0, date('n',$date2), date('j',$date2), date('Y',$date2) );

        if ($date2<$date0)
        {
            // it's before, so nothing more !
        }
        else
        {
            $date3 = mktime( $end_h, $end_m, 0, date('n',$date2), date('j',$date2), date('Y',$date2) );
            if ($date2>=$date3) $date2=$date3;
            $date3 = mktime( $end_h, $end_m, 0, date('n',$current_date), date('j',$current_date), date('Y',$current_date) );
            // calculate the number of seconds from current timestamp to the final timestamp
            $tmp = $date2-$date3; $seconds += $tmp;
            // printf("\nFrom %s To %s : %d hours\n",date('d/m/y H:i',$date2),date('d/m/y H:i',$date3),$tmp/3600);
        }

        // calculate the working days in seconds
        $seconds += 3600*($working_hours[1]-$working_hours[0])*$days;
        // printf("\nFrom %s To %s : %d hours\n",date('d/m/y H:i',$date1),date('d/m/y H:i',$date2),$seconds/3600);

        $horas = ceil($sign * $seconds/3600); // to get hours

        $ret['day'] = ceil($horas / 8);
        $ret['hour'] = $horas;
        $ret['days'] = $days;
        $ret['date0'] = date("Y-m-d H:i:s", $date0);
        $ret['date1'] = date("Y-m-d H:i:s", $current_date);
        $ret['date2'] = date("Y-m-d H:i:s", $date2);
        if(isset($date3))
        {
            $ret['date3'] = date("Y-m-d H:i:s", @$date3);
        }

        return $ret;
    }

    public function verifyQuotes($orders, $lang, $flag_report = 0)
    {
        try
        {
            $chkpro_desc = [
                'Ninguno',
                'Programación Cliente',
                'Programación LITO',
                'Programación LITO sin cambios',
                'Revisión de cotización hecha por cliente',
                'Sólo hoteles o servicios sueltos',
                'Programación exclusiva CLIENTE sin cambios'
            ];

            foreach($orders as $key => $value)
            {
                $value = (array) $value; $_nroref = @$value['nroref'];

                $_codsec = trim($value['codsec']); $codsec = (strlen($_codsec) == 1) ? $_codsec : $_codsec[2];

                $_etiquetas = array(); $etiquetas = json_decode($value['label']);

                if(count($etiquetas) > 0)
                {
                    foreach($etiquetas as $a => $b)
                    {
                        $_etiquetas[] = array('id' => $b->id, 'etiqueta' => $b->nombre, 'colbac' => $b->colbac, 'coltex' => $b->coltex);
                    }
                }

                $orders[$key]['etiquetas'] = $_etiquetas;

                if (trim($value['fecres']) == '') {
                    $fecres = date("Y-m-d H:i:s");
                }
                else {
                    $fecres = trim($value['fecres']) . ' ' . trim($value['horres']);
                }

                $fecrec = trim($value['fecrec']) . ' ' . trim($value['horrec']);

                $dias = $this->checkDateFormat($fecrec, $fecres);
                $orders[$key]['days'] = $dias['days'];
                $orders[$key]['response_days'] = $dias;
                $orders[$key]['horas'] = ($dias['hour'] > 0) ? $dias['hour'] : 0;
                $orders[$key]['dias'] = ($dias['day'] > 0) ? $dias['day'] : 0;

                // Cambios en la validación del tiempo..
                $alerta = ''; $_times = array(0, 12, 72, 0, 48, 120); // Tiempo por sectores..
                //
                $limite = $_times[$codsec]; $horas = ($orders[$key]['horas'] > 0) ? $orders[$key]['horas'] : 0;

                if($limite > 0)
                {
                    if($horas <= $limite)
                    {
                        $alerta = 'success';
                    }
                    else
                    {
                        if($orders[$key]['estado'] == 'OK')
                        {
                            $alerta = 'warning';
                        }
                        else
                        {
                            $alerta = 'danger';
                        }
                    }
                }

                $orders[$key]['horas'] = $horas;
                $orders[$key]['class'] = $alerta;

                $orders[$key]['chkpro_desc'] = '';

                if($orders[$key]['chkpro'] > -1)
                {
                    $orders[$key]['chkpro_desc'] = $chkpro_desc[$orders[$key]['chkpro']];
                }

                // $orders[$key]['nompaq'] = OrderModel::searchPaq($orders[$key]['NROREF']);

                if($value['nroref_identi'] == 'B') // Aurora 2
                {
                    $quote = DB::table('quotes')->where('id', '=', $_nroref)->first();

                    if($quote == NULL || $quote == '')
                    {
                        $log = DB::table('quote_logs')
                            ->where('object_id', '=', $_nroref)
                            ->where(function ($q) {
                                $q->orWhere('type', '=', 'editing_quote');
                            })
                            ->first();

                        if($log != '' AND $log != null)
                        {
                            $orders[$key]['quote_log'] = $log;
                            $_nroref = $log->quote_id;
                        }

                        $quote = DB::table('quotes')->where('id', '=', $_nroref)->first();
                    }

                    $orders[$key]['nroref_nuevo'] = $_nroref;
                    $orders[$key]['quote'] = $quote;

                    if($quote != '' AND $quote != null)
                    {
                        if($quote->operation == 'passengers')
                        {
                            $quote_people = DB::table('quote_people')
                                ->where('quote_id', '=', $quote->id)
                                ->first();

                            $orders[$key]['quote_people'] = $quote_people;

                            $client = new \GuzzleHttp\Client();
                            $baseUrlExtra = url('/'); // 'https://backend.limatours.com.pe';
                            // $baseUrlExtra = 'http://127.0.0.1:8000/';
                            $request = $client->get($baseUrlExtra . '/quote_passengers_frontend?quote_id=' . $quote->id . '&lang=' . $lang);
                            $response = (array) json_decode($request->getBody()->getContents(), true);
                            $orders[$key]['DATA_AURORA_2'] = $response;

                            $mount_total = NULL; $count = 0; $items = ['', 'SGL', 'DBL', 'TPL'];

                            if(isset($response['data'][0]['passengers']))
                            {
                                foreach($response['data'][0]['passengers'] as $k => $v)
                                {
                                    $_name = explode("-", $v['first_name']);
                                    $_key = array_search(trim(last($_name)), $items);

                                    if($_key > 0)
                                    {
                                        $count += $_key;
                                    }
                                    else
                                    {
                                        $count += 1;
                                    }

                                    if($mount_total == NULL)
                                    {
                                        $mount_total = $v['total'];
                                    }
                                    else
                                    {
                                        $mount_total += $v['total'];
                                    }
                                }
                            }

                            if(((float) $quote_people->adults + (float) $quote_people->child) > $count)
                            {
                                if($mount_total != NULL AND $mount_total > 0)
                                {
                                    $mount_total = $mount_total * $quote_people->adults;
                                }
                            }
                        }

                        if($quote->operation == 'ranges')
                        {
                            $client = new \GuzzleHttp\Client();
                            $baseUrlExtra = url('/'); // 'https://backend.limatours.com.pe';
                            // $baseUrlExtra = 'http://127.0.0.1:8000/';
                            $request = $client->get($baseUrlExtra . '/quote_ranges_frontend?quote_id=' . $quote->id . '&lang=' . $lang);
                            $response = (array) json_decode($request->getBody()->getContents(), true);
                            $orders[$key]['DATA_AURORA_2'] = $response;

                            $mount_total = NULL;

                            if(isset($response['ranges']))
                            {
                                foreach($response['ranges'] as $k => $v)
                                {
                                    if($mount_total == NULL)
                                    {
                                        $mount_total = $v['promedio'];
                                    }
                                    else
                                    {
                                        if($v['promedio'] <= $mount_total AND $v['promedio'] > 0)
                                        {
                                            $mount_total = $v['promedio'];
                                        }
                                    }
                                }
                            }
                        }

                        $orders[$key]['price_estimated'] = number_format($mount_total, 2, ".", "");
                        $orders[$key]['fectravel'] = $quote->date_in;
                        $orders[$key]['fectravel_tca'] = $quote->estimated_travel_date;
                        // $orders[$key]['FECTRAVEL_ESTIMATED'] = ($quote->estimated_travel_date != '' AND $quote->estimated_travel_date != NULL) ? $quote->estimated_travel_date : $quote->date_in;
                    }

                    // $orders[$key]['price_estimated'] = 0;
                    // $orders[$key]['fectravel'] = '';
                    // $orders[$key]['fectravel_tca'] = '';

                    if(@$value['nompaq'] == '' OR @$value['nompaq'] == NULL)
                    {
                        $quote_log = DB::table('quote_logs')
                            ->where('quote_id', '=', $_nroref)
                            ->where('type', '=', 'from_package')
                            ->first();

                        $package = DB::table('package_translations')
                            ->where('package_id', '=', @$quote_log->object_id)
                            ->first();

                        $orders[$key]['nompaq'] = @$package->name;
                        $orders[$key]['data_log'] = $quote_log;
                        $orders[$key]['data_package'] = $package;
                    }
                }

                if( strlen( trim($value['horrec']) ) == 3 ){
                    $value['horrec'] = trim($value['horrec']) . '00';
                }

                $orders[$key]['horrec'] = date("H:i", strtotime($value['horrec']));

                if($value['horres'] != '' AND $value['horres'] != NULL)
                {
                    $orders[$key]['horres'] = date("H:i", strtotime($value['horres']));
                }
            }

            return $orders;
        }
        catch(\Exception $ex)
        {
            return $this->throwError($ex);
        }
    }

    public function toArray($object = [])
    {
        if(is_object($object) OR is_array($object))
        {
            $array = [];

            foreach($object as $key => $value)
            {
                if(is_object($value) OR is_array($value))
                {
                    $value = $this->toArray($value);
                }

                $array[$key] = $value;
            }

            return $array;
        }
        else
        {
            return $object;
        }
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        try
        {
            $response = [];
            $teams = $this->toArray($this->stella_service->search_teams());
            $fecini = '2021-10-01'; $fecfin = '2022-09-30';

            $date1 = new DateTime($fecini);
            $date2 = new DateTime($fecfin);
            $diff = $date1->diff($date2);

            $months = 0;

            if($diff->y > 0)
            {
                $months = 12 * $diff->y;
            }

            if($diff->m > 0)
            {
                $months += $diff->m;
            }

            // $month = (int) date("m", strtotime($fecfin)); $year = (int) date("Y", strtotime($fecfin));
            $max_process = 5;

            foreach($teams as $key => $value)
            {
                $team = $value['team']; $month_count = 0;
                $month_initial = (int) date("m", strtotime($fecini)); $year_initial = (int) date("Y", strtotime($fecini));

                do
                {
                    if($max_process > 0)
                    {
                        $page = LogOrder::where('team', '=', $team)->where('month', '=', $month_initial)
                        ->where('year', '=', $year_initial)->max('page'); $page += 1;

                        $date_in = '01/' . str_pad($month_initial, 2, '0') . '/' . $year_initial; $date_out = date('t/m/Y',strtotime($year_initial . '-' . str_pad($month_initial, 2, 0) . '-01'));

                        $data = [
                            'executive' => 'TODOS',
                            'type' => 'E',
                            'region' => '',
                            'sector' => '', // tipo de producto..
                            'state' => 'ALL',
                            'fecini' => $date_in,
                            'fecfin' => $date_out,
                            'nroped' => '',
                            'seguimiento' => '',
                            'team' => $team,
                            'limit' => $page,
                            'kam' => ''
                        ];

                        $orders = $this->verifyQuotes($this->toArray($this->stella_service->search_orders($data)), 'en');

                        if(count($orders) > 0)
                        {
                            $log = new LogOrder;
                            $log->team = $team;
                            $log->month = $month_initial;
                            $log->year = $year_initial;
                            $log->page = $page;
                            $log->data = json_encode($orders);
                            $log->save();

                            $max_process -= 1;
                        }

                        if(count($orders) == 0)
                        {
                            if($month_initial == 12)
                            {
                                $month_initial = 1; $year_initial += 1;
                            }
                            else
                            {
                                $month_initial += 1;
                            }

                            $log = "No hay pedidos..";
                        }

                        $response[$team] = [
                            'page' => $page,
                            'orders' => $orders,
                            'log' => $log,
                            'data' => $data,
                            'max_process' => $max_process,
                        ];
                    }
                    else
                    {
                        break 2;
                    }
                }
                while ($month_count < $months);
            }
        }
        catch(\Exception $ex)
        {
            print_r($this->throwError($ex));
        }
    }
}
