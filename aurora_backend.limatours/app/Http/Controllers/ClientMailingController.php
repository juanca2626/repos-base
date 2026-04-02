<?php

namespace App\Http\Controllers;

use App\Client;
use App\ClientMailing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;


class ClientMailingController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:masimailing.read');
    }

    /**
     * Display a listing of the resource.
     * @return JsonResponse
     */

    public function index(Request $request)
    {
        try {
            // client_mailing?lang=en&status=1&market=0&query=&limit=10&ascending=1&page=1&byColumn=0&orderBy=market_name
            $paging = $request->input('page') ? $request->input('page') : 1;
            $orderBy = $request->input('orderBy') ? $request->input('orderBy') : 'clients.name';
            $ascending = $request->input('ascending') == "0" ? 'desc' : 'asc';
            $limit = $request->input('limit');
            $querySearch = $request->input('query');
            $search = $request->input('search');
            $status = 1; // FILTRADO POR STATUS: $request->input('search');
            $market = $request->input('market');
            //DB:RAW en el query permite obtener las variables como "true" o "false"

            $clients = Client::select(
                'clients.id',
                'clients.code',
                'clients.name',
                DB::raw('CASE WHEN client_mailing.weekly IS NULL or client_mailing.weekly=0 THEN "false"
                ELSE "true" END as weekly'),
                DB::raw('CASE WHEN client_mailing.day_before IS NULL or client_mailing.day_before=0 THEN "false"
                ELSE "true" END as day_before'),
                DB::raw('CASE WHEN client_mailing.daily IS NULL or client_mailing.daily=0 THEN "false"
                ELSE "true" END as daily'),
                DB::raw('CASE WHEN client_mailing.survey IS NULL or client_mailing.survey=0 THEN "false"
                ELSE "true" END as survey'),
                DB::raw('CASE WHEN client_mailing.whatsapp IS NULL or client_mailing.whatsapp=0 THEN "false"
                ELSE "true" END as whatsapp'),
                'clients.logo',
                'clients.market_id',
                DB::raw('CASE WHEN client_mailing.status IS NULL or client_mailing.status=0 THEN "false"
                ELSE "true" END as status'),
                'markets.name AS market_name'
            )->search($search)
                ->status($status)//Se obtiene todos los clientes con STATUS=1 dentro de la tabla "clients"
                ->market($market)//Se obtiene todos los clientes del MERCADO FILTRADO dentro de la tabla "clients", usando markets_id
                ->leftjoin('client_mailing', 'clients.id', '=', 'client_mailing.clients_id')
                ->leftjoin('markets', 'clients.market_id', '=', 'markets.id')
                ->orderBy($orderBy, $ascending);

            if ($querySearch) {
                $clients->where(function ($query) use ($querySearch) {
                    $query->orWhere('clients.code', 'like', '%'.$querySearch.'%');
                    $query->orWhere('clients.name', 'like', '%'.$querySearch.'%');
                });
            }

            $count = $clients->count(); // Se cuenta los registros para el vue-tables-2
            $clients = $clients->paginate($limit, ['*'], 'page',
                $paging); // Se filtra la página seleccionada dentro de la paginación de vue-tables-2
            foreach ($clients->items() as $item) {
                $item->weekly = ($item->weekly == 'true') ? true : false;
                $item->survey = ($item->survey == 'true') ? true : false;
                $item->status = ($item->status == 'true') ? true : false;
                $item->whatsapp = ($item->whatsapp == 'true') ? true : false;
                $item->day_before = ($item->day_before == 'true') ? true : false;
                $item->daily = ($item->daily == 'true') ? true : false;
            }
            $data = [
                'data' => $clients->items(),
                'count' => $count,
                'success' => true,
                // 'client_mailing'=> $client_mailing,
            ];
            return Response::json($data);
        } catch (\Exception $ex) {
            return Response::json(['error' => $ex->getMessage(), 'success' => false]);
        } catch (\Error $ex) {
            return Response::json(['error' => $ex->getMessage(), 'success' => false]);
        }
    }

    /**
     * Display a listing of the resource.
     * @return JsonResponse
     */
    public function update($clients_id, Request $request)
    {
        try {
            $status = (int) $request->input('status');
            $newstatus = ($status === 0) ? 0 : 1;
            $config = $request->input('config');
            //Se crea o se actualiza la tabla client_mailing con la configuración deseada (daily, weekly, day_before,etc.)
            $clients = ClientMailing::where('clients_id', $clients_id)->first();
            if ($clients) {
                DB::table('client_mailing')
                    ->where('id', $clients->id)
                    ->update([$config => $newstatus]);
            } else {
                $newMailing = new ClientMailing();
                $newMailing->clients_id = $clients_id;
                $newMailing->weekly = 0;
                $newMailing->day_before = 0;
                $newMailing->daily = 0;
                $newMailing->survey = 0;
                $newMailing->whatsapp = 0;
                $newMailing->status = 0;
                $newMailing->save();
                DB::table('client_mailing')
                    ->where('id', $newMailing->id)
                    ->update([$config => $newstatus]);
            }

            // LOG - UPDATE
            /*
            $log = new ClientMailingLog();
            $log->user_id = auth()->user()->id;
            $log->config = $config;
            $log->status = $newstatus;
            $log->save();
            */

            $data = [
                'success' => true,
            ];
            return Response::json($data);
        } catch (\Exception $ex) {
            return Response::json(['error' => $ex->getMessage(), 'success' => false]);
        } catch (\Error $ex) {
            return Response::json(['error' => $ex->getMessage(), 'success' => false]);
        }
    }

    public function getClientsMasiByEmailType($type, Request $request)
    {
        try {
            $clients = Client::select(
                'clients.name',
                'clients.code',
                'clients.logo'
            )->status(1)
                ->leftjoin('client_mailing', 'clients.id', '=', 'client_mailing.clients_id')
                ->where('client_mailing.status', 1)
                ->where('client_mailing.'.$type, 1)
                ->get();

            $clients = $clients->transform(function ($item) {
                $logo_explode = explode("/", $item['logo']);
                $item['logo'] = end($logo_explode);
                return $item;
            });
            $clients_query = $clients->implode('code', "','");
            $data = [
                'success' => true,
                'data' => [
                    'clients_data' => $clients,
                    'clients_query' => $clients_query,
                ]
            ];
            return Response::json($data);
        } catch (\Exception $ex) {
            return Response::json(['error' => $ex->getMessage(), 'success' => false]);
        } catch (\Error $ex) {
            return Response::json(['error' => $ex->getMessage(), 'success' => false]);
        }
    }
}
