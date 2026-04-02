<?php

namespace App\Http\Allotment\Controller;
 
use App\Http\Controllers\Controller; 
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Response; 
use Illuminate\Support\Facades\DB;
use App\Http\Allotment\Traits\ReportFilters;
use Carbon\Carbon;

class CalenderController extends Controller
{
    use ReportFilters;
    private $params = [];

    public function __construct(Request $request) {
        $this->params = $this->getReportFilters($request);
    }

    public function index(Request $request)
    {               
       
        $porcentaje = 0.2;     
         

        try
        {
            
            $sql = "

                SELECT  
                    hday.date,

                    CASE
                        WHEN SUM(hday.is_blocked) 
                            >= FLOOR(COUNT(DISTINCT hday.hotel_id) * ".$porcentaje.")
                            THEN 'B'

                        WHEN SUM(hday.is_no_inventory) 
                            >= FLOOR(COUNT(DISTINCT hday.hotel_id) * ".$porcentaje.")
                            THEN 'S'

                        ELSE 'I'
                    END AS inventory_status_day

                    /* Debug opcional */
                    -- ,COUNT(DISTINCT hday.hotel_id)            AS total_hotels,
                    -- FLOOR(COUNT(DISTINCT hday.hotel_id) * ".$porcentaje.") AS min_hotels_30pct,
                    -- SUM(hday.is_blocked)                     AS blocked_hotels,
                    -- SUM(hday.is_no_inventory)                AS no_inventory_hotels



                    FROM (
                        /* ===== Estado POR HOTEL y DÍA ===== */
                        SELECT
                            x.date,
                            x.hotel_id,

                            /* HOTEL BLOQUEADO */
                            CASE
                                WHEN x.total_rows = x.locked_rows THEN 1
                                ELSE 0
                            END AS is_blocked,

                            /* HOTEL SIN INVENTARIO */
                            CASE
                                WHEN x.total_rows <> x.locked_rows
                                AND x.inventory_positive_rows = 0
                                THEN 1
                                ELSE 0
                            END AS is_no_inventory

                        FROM (
                            SELECT
                                i.date,
                                r.hotel_id,

                                COUNT(*) AS total_rows,

                                SUM(i.locked = 1) AS locked_rows,

                                SUM(
                                    CASE
                                        WHEN i.locked = 0
                                        AND i.inventory_num > 0
                                        THEN 1
                                        ELSE 0
                                    END
                                ) AS inventory_positive_rows

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
                                AND rp.deleted_at IS NULL
                                {$this->params['sql_plans_type_id']}
                                {$this->params['sql_channel']}
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

                            {$this->params['sql_type_classes_table']}   
                                                        
                            WHERE
                                i.deleted_at IS NULL
                                AND i.date BETWEEN '{$this->params['date_from']}' AND '{$this->params['date_to']}'
                            GROUP BY
                                i.date,
                                r.hotel_id
                        ) x
                    ) hday
                    GROUP BY
                        hday.date
                    ORDER BY
                        hday.date;            
            
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
