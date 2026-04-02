<?php

namespace App\Http\Controllers;

use App\Client;
use App\LogModal;
use App\Quote;
use App\Reservation;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ClientsStatsController extends Controller
{
    public function search(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $response = [];

        try {
            $type = $request->__get('type');
            $sector = $request->__get('sector');

            $start = ($request->__get('start') != '') ? $request->__get('start') : '2021-01-01';
            $end = ($request->__get('end') != '') ? $request->__get('end') : date("Y-m-d");

            $sql = '';
            $count = 0;

            if ($type == 1 || $type == 2 || $type == 3) {
                $response = Client::on('mysql_read')->with(['markets']);

                if (!empty($sector)) {
                    if ($sector == 4) {
                        $sector = 'hb';
                    }
                    $response = $response->whereHas('markets', function ($query) use ($sector) {
                        $query->where('code', 'like', $sector . '%');
                    });
                }
            }

            if ($type == 1) // Clientes nuevos..
            {
                $count = Client::on('mysql_read')->where('clients.status', '=', 1);

                if (!empty($sector)) {
                    if ($sector == 4) {
                        $sector = 'hb';
                    }
                    $count = $count->whereHas('markets', function ($query) use ($sector) {
                        $query->where('code', 'like', $sector . '%');
                    });
                }

                $count = $count->count();

                $response = $response->whereBetween('created_at', [$start, $end]);
                $response = $response->whereColumn('created_at', '=', 'updated_at');
            }

            if ($type == 2) // Clientes con acceso a A2..
            {
                // $response = $response->whereBetween('created_at', [$start, $end]);

                $count = Client::on('mysql_read')->whereHas('client_sellers', function ($query) {
                    $query->where('client_sellers.status', '=', 1);
                })->where('clients.status', '=', 1);

                if (!empty($sector)) {
                    if ($sector == 4) {
                        $sector = 'hb';
                    }
                    $count = $count->whereHas('markets', function ($query) use ($sector) {
                        $query->where('code', 'like', $sector . '%');
                    });
                }

                $count = $count->count();

                $response = $response->with(['client_sellers'])
                    ->whereHas('client_sellers', function ($query) use ($start, $end) {
                        $query->whereBetween('client_sellers.created_at', [$start, $end]);
                    })->where('clients.status', '=', 1);
            }

            if ($type == 3) // Clientes que han ingresado a A2..
            {
                $count = Client::on('mysql_read')->where('clients.code', '!=', '5PRUEB')
                    ->whereHas('client_sellers', function ($query) {
                        // $query->whereBetween('users.updated_at', [$start, $end]);
                        $query->where('users.count_login', '>', 0)
                            ->where('users.id', '!=', 2827);
                    });

                if (!empty($sector)) {
                    if ($sector == 4) {
                        $sector = 'hb';
                    }
                    $count = $count->whereHas('markets', function ($query) use ($sector) {
                        $query->where('code', 'like', $sector . '%');
                    });
                }



                $response = $response->with(['client_sellers'])
                    ->whereHas('client_sellers', function ($query) {
                        // $query->whereBetween('users.updated_at', [$start, $end]);
                        $query->where('users.count_login', '>', 0)
                            ->where('users.id', '!=', 2827);
                    })
                    ->where('clients.code', '!=', '5PRUEB')
                    ->with(['client_sellers.login_logs' => function ($query) use ($start, $end) {
                        $query->whereBetween('login_logs.created_at', [$start, $end])
                            ->where('login_logs.user_id', '!=', 2827);
                    }])
                    ->whereHas('client_sellers.login_logs', function ($query) use ($start, $end) {
                        $query->whereBetween('login_logs.created_at', [$start, $end])
                            ->where('login_logs.user_id', '!=', 2827);
                    })->where('clients.status', '=', 1);

                $count = $response->count();
            }

            if ($type == 4) // Uso de A2 (Reservas / clientes con acceso)
            {
                $reservations_items = Reservation::on('mysql_read')->with(['client', 'create_user'])
                    ->where('status', '=', 1)
                    ->whereHas('client', function ($query) {
                        $query->where('code', '!=', '5PRUEB');
                    })
                    ->whereBetween('created_at', [$start, $end]); // fecha de creación..

                if (!empty($sector)) {
                    if ($sector == 4) {
                        $sector = 'hb';
                    }
                    $reservations_items = $reservations_items->whereHas('client.markets', function ($query) use ($sector) {
                        $query->where('code', 'like', $sector . '%');
                    });
                }

                $all_reservations = $reservations_items->count();

                $reservations_items = $reservations_items->where('reservator_type', '=', 'client');

                $reservations = $reservations_items->count();
                $reservations_items = $reservations_items->get();
                // $reservations_items = $reservations_items->toSql(); $reservations = '';

                $ignore_quotes = [];

                $reservations_items->each(function ($item, $i) use (&$ignore_quotes) {

                    if ($item->entity == 'Quote' and $item->object_id > 0) {
                        $ignore_quotes[] = $item->object_id;
                    }
                });

                $quotes_items = Quote::on('mysql_read')->with(['user'])
                    ->where('status', '=', 1)
                    ->where(function ($query) use ($start, $end) {
                        $query->orWhere(function ($q) use ($start, $end) {
                            $q->whereHas('user', function ($_query) {
                                $_query->where('user_type_id', '=', 4);
                            });
                            $q->whereBetween('created_at', [$start, $end]);
                        });

                        $query->orWhereBetween('date_received', [$start, $end]);
                    });

                if (!empty($sector)) {

                    if ($sector == 4) {
                        $sector = 'hb';
                    }

                    $quotes_items = $quotes_items->whereHas('user', function ($query) use ($sector) {
                        $query->where(function ($q) use ($sector) {
                            $q->where('grupo_code', 'like', $sector . '%')
                                ->orWhereHas('markets', function ($q) use ($sector) {
                                    $q->where('code', 'like', $sector . '%');
                                })
                                ->orWhereHas('clientUsers.client.markets', function ($q) use ($sector) {
                                    $q->where('code', '!=', '5PRUEB')
                                        ->where('code', 'like', $sector . '%');
                                });
                        });
                    });
                }

                $all_quotes = $quotes_items->count();
                $all_quote_items = $quotes_items->pluck('id')->toArray();

                $quotes_items = $quotes_items->whereHas('user', function ($query) {
                    $query->where('user_type_id', '=', 4);
                })
                    ->whereNotIn('id', $ignore_quotes);

                $quotes = $quotes_items->count();
                $quotes_items = $quotes_items->get();
                // $quotes_items = $quotes_items->toSql(); $quotes = '';

                $response = [
                    'all_quote_items' => $all_quote_items,
                    'ignore_quotes' => $ignore_quotes,
                    'quotes' => $quotes,
                    'quotes_items' => $quotes_items,
                    'all_quotes' => $all_quotes,
                    'reservations' => $reservations,
                    'reservations_items' => $reservations_items,
                    'all_reservations' => $all_reservations,
                    'total' => 0,
                ];
            }

            if ($type == 5) //
            {
                $logs_items = LogModal::on('mysql_read')->with(['user', 'client.markets'])
                    ->whereHas('user.roles', function ($query) {
                        $query->whereIn('roles.id', [20, 24]);
                    });

                if ($sector != '') {

                    if ($sector == 4) {
                        $sector = 'hb';
                    }

                    $logs_items = $logs_items->whereHas('client.markets', function ($query) use ($sector) {
                        $query->where('code', 'like', $sector . '%');
                    });
                }

                $logs_items = $logs_items->whereBetween('created_at', [$start, $end]);
                $logs = $logs_items->count();
                $logs_items = $logs_items->get();

                $reservations_items = Reservation::on('mysql_read')->with(['client', 'create_user'])
                    ->where('status_cron_job_reservation_stella', '=', 9)
                    ->where('entity', '<>', 'Stella')
                    ->where('status', '=', 1)
                    ->whereBetween('date_init', [$start, $end]);

                if ($sector != '') {
                    $reservations_items = $reservations_items->whereHas('client.markets', function ($query) use ($sector) {
                        $query->where('code', 'like', $sector . '%');
                    });
                }

                $reservations = $reservations_items->count();
                $reservations_items = $reservations_items->get();

                $response = [
                    'logs' => $logs,
                    'logs_items' => $logs_items,
                    'reservations' => $reservations,
                    'reservations_items' => $reservations_items,
                    'total' => (($reservations > 0) ? ceil($logs / $reservations) : 0),
                ];
            }

            if ($type == 1 or $type == 2 or $type == 3) {
                $sql = $response->toSql();
                $response = $response->get();
            }

            if ($type == 3) {
                $response = $response->toArray();

                foreach ($response as $key => $value) {
                    $count_login = 0;
                    $updated_at = '';

                    foreach ($value['client_sellers'] as $k => $v) {
                        $updated_at = $v['created_at'];
                        $count_login += count($v['login_logs']);
                    }

                    $response[$key]['count_login'] = $count_login;

                    if ($updated_at != '') {
                        $response[$key]['updated_at'] = $updated_at;
                    }
                }
            }

            return response()->json([
                'type' => 'success',
                'sql' => $sql,
                'start' => $start,
                'end' => $end,
                'count' => $count,
                'response' => $response,
            ]);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }
}
