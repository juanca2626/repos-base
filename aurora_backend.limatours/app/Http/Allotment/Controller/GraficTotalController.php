<?php

namespace App\Http\Allotment\Controller;
 
use App\Http\Controllers\Controller; 
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Response; 
use Illuminate\Support\Facades\DB;
use App\Http\Allotment\Traits\ReportFilters;

class GraficTotalController extends Controller
{
    use ReportFilters;
    private $params = [];

    public function __construct(Request $request) {
        $this->params = $this->getReportFilters($request);
    }

    public function index(Request $request)
    {
 
        $sql = "
            SELECT
                -- habitaciones disponibles
                SUM(
                    CASE 
                        WHEN i.inventory_num > 0 AND i.locked = 0 
                        THEN i.inventory_num 
                        ELSE 0 
                    END
                ) AS available_rooms,

                -- habitaciones agotadas
                SUM(
                    CASE 
                        WHEN i.inventory_num <= 0 AND i.locked = 0 
                        THEN 1 
                        ELSE 0 
                    END
                ) AS sold_out_rooms,

                -- habitaciones bloqueadas
                SUM(
                    CASE 
                        WHEN i.locked = 1 
                        THEN 1 
                        ELSE 0 
                    END
                ) AS blocked_rooms

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
                AND rt.occupation = {$this->params['occupation']}
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
            {$this->params['sql_type_classes_table']} 

            WHERE
                i.deleted_at IS NULL
                AND i.date BETWEEN '{$this->params['date_from']}' AND '{$this->params['date_to']}'
        ";

        $totals = DB::selectOne($sql);

 
        return response()->json([
            'success' => 'true',
            'data' => [
                [
                    'available_rooms' => number_format((int) $totals->available_rooms, 0),
                    'sold_out_rooms'  => number_format((int) $totals->sold_out_rooms, 0),
                    'blocked_rooms'   => number_format((int) $totals->blocked_rooms, 0),
                    'total_count' => number_format((int) $totals->available_rooms + (int) $totals->sold_out_rooms + (int) $totals->blocked_rooms,0)
                ]
            ]
        ]);
    }
 
    public function index_bk(Request $request)
    {               
        try
        {
            
            $sql = "

                    SELECT

                        -- Total de habitaciones disponibles (formateado)
                        FORMAT(
                            SUM(CASE WHEN i.inventory_num > 0 AND i.locked = 0 THEN i.inventory_num ELSE 0 END),
                            0,
                            'en_US'
                        ) AS available_rooms,

                        -- Total de habitaciones agotadas (formateado)
                        FORMAT(
                            SUM(CASE WHEN i.inventory_num <= 0 AND i.locked = 0 THEN 1 ELSE 0 END),
                            0,
                            'en_US'
                        ) AS sold_out_rooms,

                        -- Total de habitaciones bloqueadas (formateado)
                        FORMAT(
                            SUM(CASE WHEN i.locked = 1 THEN 1 ELSE 0 END),
                            0,
                            'en_US'
                        ) AS blocked_rooms,

                        -- Total global sumado (formateado)
                        FORMAT(
                            (
                                SUM(CASE WHEN i.inventory_num > 0 AND i.locked = 0 THEN i.inventory_num ELSE 0 END) +
                                SUM(CASE WHEN i.inventory_num <= 0 AND i.locked = 0 THEN 1 ELSE 0 END) +
                                SUM(CASE WHEN i.locked = 1 THEN 1 ELSE 0 END)
                            ),
                            0,
                            'en_US'
                        ) AS total_count


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
                        AND rt.occupation = {$this->params['occupation']}                                             
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

                    {$this->params['sql_type_classes_table']}                         
                    WHERE
                        i.deleted_at IS NULL
                        AND i.date BETWEEN  '{$this->params['date_from']}' AND '{$this->params['date_to']}';
                   
            
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
     

}
