<?php

namespace App\Http\Allotment\Controller;

use App\DateRangeHotel;
use App\Http\Controllers\Controller; 
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Response; 
use Illuminate\Support\Facades\DB;
use App\Http\Allotment\Traits\ReportFilters;
use App\RatesPlansCalendarys;
use Carbon\Carbon;

class HotelsRoomsListController extends Controller
{
    use ReportFilters;
    private $params = [];

    public function __construct(Request $request) {
        $this->params = $this->getReportFilters($request);
    }

    public function index(Request $request)
    {               
        try
        {
            
            $pageSize = 10;
            $order = $request->input('order', 'preferente');
            $dir = $request->input('dir', 'asc');

            try
            {
    
                $sql = "
                SELECT
                    base.hotel_id,
                    base.name,
                    base.chain,
                    base.typeclass,
                    base.preferente,                
                    base.channel_mark,
                    base.inventory_status,
                    base.porcent_agotado,
                    base.porcent_disponible,
                    base.porcent_minima,

                    JSON_ARRAYAGG(
                        JSON_OBJECT(
                            'date', d.date,
                            'room_id', d.room_id,   
                            'room_name', d.room_name,                    
                            'rate_id', d.rate_id,
                            'occupation', d.occupation, 
                            'occupancy', d.occupancy,                                           
                            'rate_name', d.rate_name,
                            'channel_mark', d.channel_mark,                            
                            'inventory_num', d.inventory_num,
                            'locked', d.locked,
                            'rate_plan_rooms_id', d.rate_plan_rooms_id,
                            'rates_plans_type_id', d.rates_plans_type_id
                        )
                    ) AS details

                FROM (

                    /* ================== TU QUERY ORIGINAL ================== */

                    SELECT
                        hday.hotel_id,
                        hday.name,
                        hday.chain,
                        (SELECT VALUE 
                            FROM translations 
                            WHERE TYPE='typeclass'
                            AND object_id = hday.typeclass_id
                            AND language_id = 1
                            AND deleted_at IS NULL
                        ) AS typeclass,
                        hday.value AS preferente,
                        hday.channel_mark,    

                        /* ===== PORCENTAJES ===== */
                        ROUND(
                            (hday.exhausted_rows / NULLIF(hday.total_rows, 0)) * 100,
                            2
                        ) AS porcent_agotado,

                        ROUND(
                            (hday.inventory_positive_rows / NULLIF(hday.total_rows, 0)) * 100,
                            2
                        ) AS porcent_disponible,

                        ROUND(
                            (hday.inventory_minimal_rows / NULLIF(hday.total_rows, 0)) * 100,
                            2
                        ) AS porcent_minima,
                        
                        /* ===== INVENTORY STATUS ===== */

                        CASE
                            /* 100% agotado */
                            WHEN hday.exhausted_rows = hday.total_rows
                                THEN 'Agotado'

                            /* ≥ 20% agotado */
                            WHEN (hday.exhausted_rows / NULLIF(hday.total_rows, 0)) >= 0.20
                                THEN 'Agotado'

                            /* ≥ 51% disponible */
                            WHEN (hday.inventory_positive_rows / NULLIF(hday.total_rows, 0)) >= 0.51
                                THEN 'Disponible'

                            /* Todo lo demás */
                            ELSE 'Mínima'
                        END AS inventory_status                        

                    FROM (
                        SELECT
                            r.hotel_id,
                            h.name,
                            cha.name as chain,
                            htc.typeclass_id,
                            htp.value,
                            
                            /* ================== CONTADORES ================== */
			                SUM(
                                CASE                                     
                                    WHEN i.inventory_num < 1
                                    THEN 1 ELSE 0
                                END
                            ) AS exhausted_rows,
                            SUM(
                                CASE                            
                                    WHEN i.inventory_num BETWEEN 1 AND 2
                                    THEN 1 ELSE 0
                                END
                            ) AS inventory_minimal_rows,

                            SUM(
                                CASE                       
                                    WHEN i.inventory_num > 2
                                    THEN 1 ELSE 0
                                END
                            ) AS inventory_positive_rows,

                            /* ================== TOTAL ================== */
                            (
                            SUM(CASE WHEN i.inventory_num < 1 THEN 1 ELSE 0 END) +
                            SUM(CASE WHEN i.inventory_num BETWEEN 1 AND 2 THEN 1 ELSE 0 END) +
                            SUM(CASE WHEN i.inventory_num > 2 THEN 1 ELSE 0 END)
                            ) AS total_rows,

                            /* ================== CHANNEL ================== */
                            CASE
                                WHEN SUM(rp.channel_id = 1) > 0
                                AND SUM(rp.channel_id = 6) > 0 THEN 'aurora/hyperguest'
                                WHEN SUM(rp.channel_id = 1) > 0 THEN 'aurora'
                                WHEN SUM(rp.channel_id = 6) > 0 THEN 'hyperguest'
                            END AS channel_mark

                        FROM inventories i
                        JOIN rates_plans_rooms rpr
                            ON rpr.id = i.rate_plan_rooms_id
                            AND rpr.status = 1
                            AND rpr.bag = 0
                            AND rpr.deleted_at IS NULL
                        JOIN rates_plans rp
                            ON rp.id = rpr.rates_plans_id
                            AND rp.status = 1
                            AND rp.allotment = 1
                            {$this->params['sql_plans_type_id']}
                            {$this->params['sql_channel']}
                            AND rp.deleted_at IS NULL
                        JOIN rooms r
                            ON r.id = rpr.room_id
                            AND r.inventory = 1
                            AND r.state = 1
                            AND r.deleted_at IS NULL                   
                        JOIN hotels h
                            ON h.id = r.hotel_id
                            AND h.status = 1
                            AND h.deleted_at IS NULL
                            AND h.country_id = {$this->params['country_id']}
                            {$this->params['sql_country_state']}
                            {$this->params['city_id']}
                            {$this->params['district_id']}
                            {$this->params['sql_chain']}
                            {$this->params['sql_start']}
                        JOIN chains cha
                            ON cha.id = h.chain_id                        
                        JOIN hotel_type_classes htc
                            ON htc.id = (
                                SELECT MIN(id)
                                FROM hotel_type_classes
                                WHERE hotel_id = h.id
                                {$this->params['sql_type_classes']}
                                AND year = '{$this->params['anio']}'
                            )
                        JOIN hotel_preferentials htp
                            ON htp.id = (
                                SELECT MIN(id)
                                FROM hotel_preferentials
                                WHERE hotel_id = h.id
                                AND year = '{$this->params['anio']}'
                            )
                        WHERE                            
                            (i.locked IS NULL OR i.locked <> 1) and i.deleted_at IS NULL
                            AND i.date BETWEEN '{$this->params['date_from']}' AND '{$this->params['date_to']}'
                        GROUP BY
                            r.hotel_id,
                            htc.typeclass_id,
                            htp.value
                    ) hday
                ) base

                /* ================== DETAIL ================== */
                LEFT JOIN (
                    SELECT
                        r.hotel_id,
                        i.date,
                        r.id AS room_id,
                        tr_room.value AS room_name,
                        rt.occupation AS occupation, 
                        tr_room_types.value as occupancy,
                        rp.id AS rate_id,
                        rp.name AS rate_name,
                        rp.channel_id,
                        rp.rates_plans_type_id,
                        CASE
                            WHEN rp.channel_id = 1 THEN 'aurora'
                            WHEN rp.channel_id = 6 THEN 'hyperguest'
                        END AS channel_mark,                        
                        i.inventory_num,
                        i.locked,
                        i.rate_plan_rooms_id
                    FROM inventories i
                    JOIN rates_plans_rooms rpr
                        ON rpr.id = i.rate_plan_rooms_id
                        AND rpr.status = 1
                        AND rpr.bag = 0
                        AND rpr.deleted_at IS NULL
                    JOIN rates_plans rp
                        ON rp.id = rpr.rates_plans_id
                        AND rp.status = 1
                        AND rp.allotment = 1
                        {$this->params['sql_plans_type_id']}
                        {$this->params['sql_channel']}
                        AND rp.deleted_at IS NULL
                    JOIN rooms r
                        ON r.id = rpr.room_id
                        AND r.inventory = 1
                        AND r.state = 1
                        AND r.deleted_at IS NULL
                    JOIN room_types rt
                        ON rt.id = r.room_type_id 
                    LEFT JOIN translations tr_room_types
                        ON tr_room_types.type = 'roomtype'
                        AND tr_room_types.slug = 'roomtype_name'
                        AND tr_room_types.language_id = 1
                        AND tr_room_types.object_id = rt.id
                        AND tr_room_types.deleted_at IS NULL                     
                    LEFT JOIN translations tr_room
                            ON tr_room.type = 'room'
                            AND tr_room.slug = 'room_name'
                            AND tr_room.language_id = 1
                            AND tr_room.object_id = r.id
                            AND tr_room.deleted_at IS NULL                    
                    JOIN hotels h
                        ON h.id = r.hotel_id
                        AND h.status = 1
                        AND h.deleted_at IS NULL
                        AND h.country_id = {$this->params['country_id']}
                        {$this->params['sql_country_state']}
                        {$this->params['city_id']}
                        {$this->params['district_id']}
                        {$this->params['sql_chain']}
                        {$this->params['sql_start']}
                    WHERE
                        i.deleted_at IS NULL
                        AND i.date BETWEEN '{$this->params['date_from']}' AND '{$this->params['date_to']}'
                ) d ON d.hotel_id = base.hotel_id

                GROUP BY
                    base.hotel_id,
                    base.name,
                    base.typeclass,
                    base.preferente,
                    base.channel_mark,
                    base.inventory_status
                ";

                // dd($sql);

                $query  = DB::table(DB::raw("
                    (
                        $sql
                    ) AS hotels_query
                "));

                if ($order === 'hotel') {
                    $query->orderBy('name', $dir);
                }  

                if ($order === 'channel') {
                    $query->orderByRaw("
                        CASE channel_mark
                            WHEN 'aurora' THEN 1
                            WHEN 'aurora/hyperguest' THEN 2
                            WHEN 'hyperguest' THEN 3
                        END $dir
                    ");
                }            
                
                if ($order === 'status') {
                    $query->orderByRaw("
                        CASE inventory_status
                            WHEN 'Agotado' THEN 1
                            WHEN 'Mínima' THEN 2
                            WHEN 'Disponible' THEN 3
                        END $dir
                    ");
                }

                if ($order === 'preferente') {
                    $query->orderByRaw("
                        CASE preferente
                            WHEN 1 THEN 0
                            WHEN 0 THEN 1 
                        END $dir
                    ");
                }

                
                $hotels = $query->paginate($pageSize);
                $header = $this->getHeaderDays($this->params['date_from'], $this->params['date_to']);
                $hotels->getCollection()->transform(function ($hotel) use ($header) {
                
                    $details = collect(json_decode($hotel->details, true));

                    $grouped = $details
                        ->groupBy(function ($item) {
                            return $item['room_id'].'-'.$item['rate_id'];
                        })
                        ->map(function ($items) {

                            $first = $items->first();
                            
                            $price = 0;

                            if($first['channel_mark'] == 'aurora')
                            {
                                $dateRangeHotel = DateRangeHotel::where('rate_plan_room_id', $first['rate_plan_rooms_id'])->whereRaw('? BETWEEN date_from AND date_to', [$this->params['date_from']])->first();
                                if($dateRangeHotel){
                                    $price = $dateRangeHotel->price_adult;
                                }
                            }

                            if($first['channel_mark'] == 'hyperguest')
                            {                            
                                $ratesPlansCalendarys = RatesPlansCalendarys::with('rate')->where('rates_plans_room_id', $first['rate_plan_rooms_id'])->where('date', $this->params['date_from'])->first();
                                if($ratesPlansCalendarys){                                                                     
                                    

                                    if(isset($ratesPlansCalendarys->rate[0]))
                                    {
                                        $price_adult = $ratesPlansCalendarys->rate[0]->price_adult;
                                        $price_total = $ratesPlansCalendarys->rate[0]->price_total;

                                        if($price_total>0)
                                        {
                                           $price =  $price_total;

                                        }elseif($price_adult>0){
                                            $price =  $price_adult;
                                        }

                                    }
                                }
                            }

                            return [
                                'room_id'   => $first['room_id'],
                                'room_name' => $first['room_name'],
                                'occupation'=> $first['occupation'],
                                'occupancy' => $first['occupancy'],                                
                                'rate_id'   => $first['rate_id'],
                                'rate_name' => $first['rate_name'],
                                'channel_mark' => $first['channel_mark'],
                                'price' => number_format($price, 2),
                                'rates_plans_type_id' => $first['rates_plans_type_id'],                                

                                // 🔥 AGRUPAR POR MES
                                'days' => $items
                                    ->groupBy(function ($day) {
                                        return Carbon::parse($day['date'])->format('Y-m');
                                    })
                                    ->map(function ($monthItems, $monthKey) {

                                        // Lista real de días existentes
                                        $days = $monthItems->map(function ($day) {
                                            return [
                                                'date'          => $day['date'],
                                                'inventory_num' => $day['inventory_num'],
                                                'locked'        => $day['locked']
                                            ];
                                        })->values();

                                        // 🔥 calcular día mínimo y máximo dentro del mes
                                        // $dayNumbers = $days->map(function ($d) {
                                        //     return Carbon::parse($d['date'])->day;
                                        // });

                                        // $minDay = $dayNumbers->min();
                                        // $maxDay = $dayNumbers->max();

                                        // // 🔥 generar solo números de día completos del rango
                                        // $monthDayList = collect();
                                        // for ($i = $minDay; $i <= $maxDay; $i++) {
                                        //     $monthDayList->push($i);
                                        // }

                                        return [
                                            'period' => $monthKey,   // 2025-01, 2025-02...
                                            // 'header'  => $monthDayList, // 🔥 SOLO NUMEROS DE DÍA
                                            'days'   => $days          // días reales
                                        ];
                                    })
                                    ->values()
                            ];
                        })
                        ->values();
                    
                    $inventory_status_porcent =  ''  ; 
                    if($hotel->inventory_status == 'Agotado')
                    {
                        $inventory_status_porcent =  $hotel->porcent_agotado . ' %'  ;
                        if($hotel->porcent_agotado >=100){
                            $inventory_status_porcent =  'Agotado'  ;
                        }
                    }

                    if($hotel->inventory_status == 'Disponible')
                    {
                        $inventory_status_porcent =  $hotel->porcent_disponible . ' %'   ;                        
                    }

                    if($hotel->inventory_status == 'Mínima')
                    {
                        $inventory_status_porcent =  $hotel->porcent_minima . ' %'   ;                        
                    }


                    return collect([
                        'hotel_id'                    => $hotel->hotel_id,
                        'name'                        => $hotel->name,
                        'chain'                       => $hotel->chain,
                        'category'                    => $hotel->typeclass,
                        'preferente'                  => $hotel->preferente,
                        'chain'                       => $hotel->chain,
                        'channel_mark'                => $hotel->channel_mark,
                        'inventory_status'            => $hotel->inventory_status,
                        'inventory_status_porcent'    => $inventory_status_porcent,
                        'cupos'                       => $grouped->where('occupation', 2)->count(),
                        'header'                      => $header,
                        'details'                     => $grouped,
                    ]);


                });

                return response()->json($hotels);
            }
            catch(\Exception $ex)
            {
                return response()->json([$this->throwError($ex)]);
            }            

        }
        catch(\Exception $ex)
        {
            return response()->json([$this->throwError($ex)]);
        }
    }
 


    public function getHeaderDays($date_from, $date_to){
 
        $start = Carbon::parse($date_from);
        $end = Carbon::parse($date_to);

        $header = [];

        $current = $start->copy()->startOfMonth();

        while ($current->lessThanOrEqualTo($end)) {
            // Definir primer y último día del mes a considerar
            $month_start = $current->copy()->startOfMonth()->greaterThan($start) ? $current->copy()->startOfMonth() : $start->copy();
            $month_end = $current->copy()->endOfMonth()->lessThan($end) ? $current->copy()->endOfMonth() : $end->copy();

            // Crear arreglo de días
            $days = [];
            for ($d = $month_start->day; $d <= $month_end->day; $d++) {
                $days[] = str_pad($d, 2, '0', STR_PAD_LEFT); // agrega cero a la izquierda
            }

            $header[] = [
                'period' => $current->format('Y-m'),
                'days' => $days,
            ];

            $current->addMonth();
        }

        return $header;
    }


    public function totales(Request $request)
    {               
        try
        {
                
            try
            {
    
                $sql = "                                      
                        SELECT
                            COUNT(DISTINCT h.id) AS total_hotels,
			                COUNT(DISTINCT r.id) AS total_rooms
			                -- i.date,
                            -- r.hotel_id,
                            -- rpr.id as rates_plans_rooms
                            -- h.name,
                            -- rpr.id,
                            -- rpr.room_id,
                            -- rpr.rates_plans_id,
                            -- i.`inventory_num`,i.`locked` 

                        FROM inventories i
                        JOIN rates_plans_rooms rpr
                            ON rpr.id = i.rate_plan_rooms_id
                            AND rpr.status = 1
                            AND rpr.bag = 0
                            AND rpr.deleted_at IS NULL
                        JOIN rates_plans rp
                            ON rp.id = rpr.rates_plans_id
                            AND rp.status = 1
                            AND rp.allotment = 1
                            {$this->params['sql_plans_type_id']}
                            {$this->params['sql_channel']}
                            AND rp.deleted_at IS NULL
                        JOIN rooms r
                            ON r.id = rpr.room_id
                            AND r.inventory = 1
                            AND r.state = 1
                            AND r.deleted_at IS NULL                   
                        JOIN hotels h
                            ON h.id = r.hotel_id
                            AND h.status = 1
                            AND h.deleted_at IS NULL
                            AND h.country_id = {$this->params['country_id']}
                            {$this->params['sql_country_state']}
                            {$this->params['city_id']}
                            {$this->params['district_id']}
                            {$this->params['sql_chain']}
                            {$this->params['sql_start']}
                        JOIN chains cha
                            ON cha.id = h.chain_id                        
                        JOIN hotel_type_classes htc
                            ON htc.id = (
                                SELECT MIN(id)
                                FROM hotel_type_classes
                                WHERE hotel_id = h.id
                                {$this->params['sql_type_classes']}
                                AND year = '{$this->params['anio']}'
                            )
                        JOIN hotel_preferentials htp
                            ON htp.id = (
                                SELECT MIN(id)
                                FROM hotel_preferentials
                                WHERE hotel_id = h.id
                                AND year = '{$this->params['anio']}'
                            )
                        WHERE
                            (i.locked IS NULL OR i.locked <> 1) and i.deleted_at IS NULL
                            AND i.date BETWEEN '{$this->params['date_from']}' AND '{$this->params['date_to']}'                       
                              
                ";

                // dd($sql);

               $results = DB::select($sql);
                return response()->json([
                    'success' => 'true',
                    'data' => $results
                ]);



            }
            catch(\Exception $ex)
            {
                return response()->json([$this->throwError($ex)]);
            }            

        }
        catch(\Exception $ex)
        {
            return response()->json([$this->throwError($ex)]);
        }
    }
 


}
