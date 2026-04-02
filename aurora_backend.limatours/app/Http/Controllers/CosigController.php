<?php

namespace App\Http\Controllers;

use App\LogModal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class CosigController extends Controller
{
    public function __construct()
    {
        // $this->middleware('permission:countries.read')->only('index');
        // $this->middleware('permission:countries.create')->only('store');
        // $this->middleware('permission:countries.update')->only('update');
        // $this->middleware('permission:countries.delete')->only('delete');
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function access(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        try
        {
            $fecini = $request->__get('fecini'); $fecfin = $request->__get('fecfin');
            $sector = (int) $request->__get('sector'); $type = $request->__get('type');

            $logs = LogModal::with(['user', 'client.market_regions'])
                ->where(function ($query) use ($type) {

                    if(!empty($type))
                    {
                        if($type == 'C')
                        {
                            $query->orWhereHas('user', function ($query) use ($type) {
                                $query->where('user_type_id', '=', 4);
                            });

                            $query->orWhereNull('user_id');
                            $query->orWhere('user_id', '=', 0);
                        }
                        
                        if($type == 'E')
                        {
                            $query->whereHas('user', function ($query) use ($type) {
                                $query->where('user_type_id', '=', 3);
                            });
                        }
                    }
                })
                ->whereHas('client.market_regions', function ($query) use ($sector) {

                    if($sector > 0)
                    {
                        $query->where('region_id', '=', $sector);
                    }

                })
                ->whereBetween('created_at', [$fecini, $fecfin]);

            if($type == 'E')
            {
                $logs = $logs->where('user_id', '>', 0);
            }

            /*
            if($type == 'C')
            {
                $logs = $logs->where('client_id', '>', 0);
            }
            */

            $logs = $logs->get()->toArray();

            return response()->json([
                'fecini' => $fecini,
                'fecfin' => $fecfin,
                'sector' => $sector,
                'type_user' => $type,
                'type' => 'success',
                'logs' => $logs,
            ]);
        }
        catch(\Exception $ex)
        {
            return $this->throwError($ex);
        }
    }

    public function clients(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        
        try
        {
            $fecini = $request->__get('fecini'); $fecfin = $request->__get('fecfin'); $sector = (int) $request->__get('sector');

            $sql_market = '';

            if($sector > 0)
            {
                $sql_market = sprintf("c.market_id in (select market_id from market_regions where region_id = '%d') and ", $sector);
            }

            $sql = sprintf("select r.file_code as file,
                r.date_init as fecha_inicio,
                p.package_id as paquete_id,
                p.name as paquete_nombre,
                CASE
                    WHEN reservator_type = 'excecutive' THEN 'Ejecutiva'
                    WHEN reservator_type = 'client' THEN 'Cliente'
                END as reservado_por,
                r.customer_name as nombre_grupo,
                r.client_code as cliente,
                c.name as cliente_name,
                mr.region_id as region,
                m.name as mercado,
                t.value as pais,
                r.executive_name as ejecutiva_asignada,
                r.created_at as fecha_creacion from reservations as r
                inner join package_translations as p on r.object_id = p.package_id
                inner join clients as c on r.client_id = c.id
                inner join markets as m on c.market_id = m.id
                inner join market_regions as mr on c.market_id = mr.market_id
                inner join translations as t on c.country_id = t.object_id
                where r.entity = '%s' and 
                %s
                r.reservator_type = 'client' and 
                t.type = '%s' and t.slug = '%s' and t.language_id = 1 and
                p.language_id = 1  and 
                r.created_at between '%s' and '%s' and r.deleted_at is null 
                order by r.date_init", 'Package', $sql_market, 'country', 'country_name', $fecini, $fecfin);
        
            $clients_packages = DB::select($sql);

            $sql_cotis = sprintf("select r.file_code as file,
            r.date_init as fecha_inicio,
            r.object_id as nro_cotizacion,
            (select object_id from quote_logs where type = 'from_package' and quote_id = r.object_id limit 1) as paquete_id,
            (select name from package_translations where language_id = 1 and package_id = (select object_id from quote_logs where type = 'from_package' and quote_id = r.object_id limit 1)) as paquete_nombre,
            CASE
                WHEN reservator_type = 'excecutive' THEN 'Ejecutiva'
                WHEN reservator_type = 'client' THEN 'Cliente'
            END as reservado_por,
            r.customer_name as nombre_grupo,
            r.client_code as cliente,
            c.name as cliente_name,
            m.name as mercado,
            mr.region_id as region,
            t.value as pais,
            r.executive_name as ejecutiva_asignada,
            r.created_at as fecha_creacion 
            from reservations as r
            inner join clients as c on r.client_id = c.id
            inner join markets as m on c.market_id = m.id
            inner join market_regions as mr on c.market_id = mr.market_id
            inner join translations as t on c.country_id = t.object_id
            where r.entity = '%s' and 
            r.reservator_type = 'client' and 
            %s
            t.type = '%s' and t.slug = '%s' and t.language_id = 1 and
            r.created_at between '%s' and '%s' and r.deleted_at is null and
            (select object_id from quote_logs where type = 'from_package' and quote_id = r.object_id limit 1) is not null
            order by r.date_init;", 'Quote', $sql_market, 'country', 'country_name', $fecini, $fecfin);

            $clients_cotis = DB::select($sql_cotis);
            
            return response()->json([
                'type' => 'success',
                'clients_packages' => $clients_packages,
                'clients_cotis' => $clients_cotis,
            ]);
        }
        catch(\Exception $ex)
        {
            return $this->throwError($ex);
        }
    }
}