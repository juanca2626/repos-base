<?php

namespace App\Http\Allotment\Controller;
 
use App\Http\Controllers\Controller; 
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Response; 
use Illuminate\Support\Facades\DB;
use App\Http\Allotment\Traits\ReportFilters;

class CalenderDetailsController extends Controller
{
    use ReportFilters;
    private $params = [];

    public function __construct(Request $request) {
        $this->params = $this->getReportFilters($request);
    }


    /**
        Hotel BLOQUEADO
        - basta que 1 habitación este bloqueada dentro de un hotel para que ese hotel este bloqueado (habitaciones → locked = 1)

        Hotel AGOTADO
        - no debe de haber ninguna habitación bloqueada en el hotel
        - basta que 1 habitación tenga (habitaciones → inventory_num <= 0)

        Hotel DISPONIBLE
        - no debe de tener ninguna bloqueada, ni agotada 
        - todas las habitaciones tienen que tener inventario habitación → inventory_num > 0 AND locked = 0
           
     **/

    public function index(Request $request)
    {
      

        $sql = "
            SELECT
                i.date,

                h.id   AS hotel_id,
                h.name AS hotel_name,
                htc.typeclass_id,

                /* total habitaciones/tarifas */
                COUNT(*) AS total_rooms,

                /* conteos */
                SUM(CASE WHEN i.locked = 1 THEN 1 ELSE 0 END) AS blocked_rooms,

                SUM(
                    CASE 
                        WHEN i.locked = 0
                        AND (i.inventory_num <= 0 OR i.inventory_num IS NULL)
                        THEN 1 
                        ELSE 0 
                    END
                ) AS sold_out_rooms,

                SUM(
                    CASE 
                        WHEN i.locked = 0
                        AND i.inventory_num > 1
                        THEN 1 
                        ELSE 0 
                    END
                ) AS available_rooms,

                /* porcentaje de agotados (para el detalle) */
                ROUND(
                    SUM(
                        CASE 
                            WHEN i.locked = 0
                            AND (i.inventory_num <= 0 OR i.inventory_num IS NULL)
                            THEN 1 ELSE 0 
                        END
                    ) * 100.0 / COUNT(*),
                    2
                ) AS sold_out_percent,

                /* porcentaje disponibles */
                ROUND(
                    SUM(
                        CASE 
                            WHEN i.locked = 0
                            AND i.inventory_num > 1
                            THEN 1 ELSE 0 
                        END
                    ) * 100.0 / COUNT(*),
                    2
                ) AS available_percent,

                /* estado final */
                CASE
                    /* 1. Bloqueado */
                    WHEN SUM(CASE WHEN i.locked = 1 THEN 1 ELSE 0 END) = COUNT(*)
                        THEN 'bloqueado'

                    /* 2. Agotado */
                    WHEN
                        (
                            SUM(
                                CASE 
                                    WHEN i.locked = 0
                                    AND (i.inventory_num <= 0 OR i.inventory_num IS NULL)
                                    THEN 1 ELSE 0 
                                END
                            ) * 100.0 / COUNT(*)
                        ) >= 20
                        THEN 'agotado'

                    /* 3. Disponible */
                    ELSE 'disponible'
                END AS state_group

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
            {$this->params['sql_type_classes_table']}   

            WHERE
                i.deleted_at IS NULL
                AND i.date BETWEEN '{$this->params['date_from']}' AND '{$this->params['date_to']}'

            GROUP BY
                i.date,
                h.id,
                h.name,
                htc.typeclass_id

            ORDER BY
                i.date,
                h.name
        ";
        // dd($sql);
        $rows = collect(DB::select($sql));

        // 🔥 Armado del JSON final por día
        $response = $rows
            ->groupBy('date')
            ->map(function ($hotels, $date) {

                $totals = [
                    'available_hotels' => $hotels->where('state_group', 'disponible')->count(),
                    'sold_out_hotels'  => $hotels->where('state_group', 'agotado')->count(),
                    'blocked_hotels'   => $hotels->where('state_group', 'bloqueado')->count(),
                    'total_hotels'     => $hotels->count(),
                ];

                return [
                    'date'   => $date,
                    'totals' => $totals,
                    'details'=> $hotels->values()
                ];
            })
            ->values();

        return response()->json([  
            'success' => 'true',
            'data' => $response
        ]);
    }    
 
 
}
