<?php

namespace App\Http\Allotment\Controller;
 
use App\Http\Controllers\Controller; 
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Response; 
use Illuminate\Support\Facades\DB; 

class DashboeardController extends Controller
{
 
    public function __construct(Request $request) {
     
    }

    public function index(Request $request)
    {
        try {

            $preferente = (int) $request->input('preferente', 0);
            $sql_preferent = '';

            if ($preferente === 1) {
                $year = date('Y');
                $sql_preferent = "
                    JOIN hotel_preferentials htp
                        ON htp.id = (
                            SELECT MIN(id)
                            FROM hotel_preferentials
                            WHERE hotel_id = h.id
                            AND year = '{$year}'
                            AND value = 1
                        )
                ";
            }

            $sql = "

                    SELECT
                        h.id   AS hotel_id,
                        h.name AS hotel_name,
                        (SELECT VALUE 
                            FROM translations 
                            WHERE TYPE='typeclass'
                            AND object_id = htc.typeclass_id
                            AND language_id = 1
                            AND deleted_at IS NULL
                            ) AS category,                        
                        CASE
                            WHEN a.has_aurora = 1 AND hg.has_hyperguest = 1 THEN 'ambos'
                            WHEN a.has_aurora = 1 THEN 'aurora'
                            WHEN hg.has_hyperguest = 1 THEN 'hyperguest'
                            ELSE 'no_canal'
                        END AS tipo_canal
                    FROM hotels h
                        JOIN hotel_type_classes htc
                            ON htc.id = (
                                SELECT MIN(id)
                                FROM hotel_type_classes
                                WHERE hotel_id = h.id 
                                AND year = '".date('Y')."'
                            )

                    $sql_preferent
                    -- AURORA (inventory = 1 + canal 1 + inventory futuro)
                    LEFT JOIN (
                        SELECT DISTINCT r.hotel_id, 1 AS has_aurora
                        FROM inventories i
                        JOIN rates_plans_rooms rpr
                            ON rpr.id = i.rate_plan_rooms_id
                            AND rpr.status = 1
                            AND rpr.bag = 0
                            AND rpr.deleted_at IS NULL
                        JOIN rates_plans rp
                            ON rp.id = rpr.rates_plans_id
                            AND rp.channel_id = 1
                            AND rp.status = 1
                            AND rp.allotment = 1
                            AND rp.deleted_at IS NULL
                        JOIN rooms r
                            ON r.id = rpr.room_id
                            AND r.inventory = 1
                            AND r.state = 1
                            AND r.deleted_at IS NULL
                        WHERE
                            i.deleted_at IS NULL
                            AND i.date > NOW()
                    ) a ON a.hotel_id = h.id

                    -- HYPERGUEST (canal 6 + inventory futuro)
                    LEFT JOIN (
                        SELECT DISTINCT r.hotel_id, 1 AS has_hyperguest
                        FROM inventories i
                        JOIN rates_plans_rooms rpr
                            ON rpr.id = i.rate_plan_rooms_id
                            AND rpr.status = 1
                            AND rpr.bag = 0
                            AND rpr.deleted_at IS NULL
                        JOIN rates_plans rp
                            ON rp.id = rpr.rates_plans_id
                            AND rp.channel_id = 6
                            AND rp.status = 1
                            AND rp.allotment = 1
                            AND rp.deleted_at IS NULL
                        JOIN rooms r
                            ON r.id = rpr.room_id
                            AND r.state = 1
                            AND r.deleted_at IS NULL
                        WHERE
                            i.deleted_at IS NULL
                            AND i.date > NOW()
                    ) hg ON hg.hotel_id = h.id

                    WHERE
                        h.status = 1                        
                        AND h.deleted_at IS NULL 
                        -- AND (
                           --  a.has_aurora = 1
                            -- OR hg.has_hyperguest = 1
                       --  )
                    ORDER BY tipo_canal, h.name;


            ";
            // dd($sql);
            $rows = collect(DB::select($sql));

            // Totales
            $totalHoteles = $rows->count();

            $results = [ 
                'aurora'         => $rows->where('tipo_canal', 'aurora')->count(),
                'hyperguest'     => $rows->where('tipo_canal', 'hyperguest')->count(),
                'ambos'          => $rows->where('tipo_canal', 'ambos')->count(),
                'no_canal'          => $rows->where('tipo_canal', 'no_canal')->count(),
                'total_hoteles'  => $totalHoteles,
                'pct_aurora'     => $totalHoteles ? round($rows->where('tipo_canal', 'aurora')->count() * 100 / $totalHoteles, 2) : 0,
                'pct_hyperguest' => $totalHoteles ? round($rows->where('tipo_canal', 'hyperguest')->count() * 100 / $totalHoteles, 2) : 0,
                'pct_ambos'      => $totalHoteles ? round($rows->where('tipo_canal', 'ambos')->count() * 100 / $totalHoteles, 2) : 0,
                'details' => [
                    'aurora'     => $rows->where('tipo_canal', 'aurora')->values(),
                    'hyperguest' => $rows->where('tipo_canal', 'hyperguest')->values(),
                    'ambos'      => $rows->where('tipo_canal', 'ambos')->values(),
                    'no_canal'      => $rows->where('tipo_canal', 'no_canal')->values()
                ]
            ];

       
            

            return response()->json([
                'success' => 'true',
                'data' => [$results]
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'success' => false,
                'error'   => $ex->getMessage()
            ], 500);
        }
    }
 

    public function index_base(Request $request)
    {               
        try
        {

            $preferente = $request->input('preferente', 0);
            $sql_preferent = '';

            if($preferente == 1)
            {
                $year = date('Y');
                $sql_preferent = "

                    JOIN hotel_preferentials htp
                        ON htp.id = (
                            SELECT MIN(id)
                            FROM hotel_preferentials
                            WHERE hotel_id = h.id
                            AND year = '{$year}'
                            and value=1
                    )                

                ";
            }

            $sql = "

                    SELECT
                        aurora,
                        hyperguest,
                        ambos,
                        total_hoteles,

                        ROUND(aurora * 100.0 / total_hoteles, 2) AS pct_aurora,
                        ROUND(hyperguest * 100.0 / total_hoteles, 2) AS pct_hyperguest,
                        ROUND(ambos * 100.0 / total_hoteles, 2) AS pct_ambos

                    FROM (
                        SELECT
                            SUM(CASE WHEN channels = '1' THEN 1 ELSE 0 END) AS aurora,
                            SUM(CASE WHEN channels = '6' THEN 1 ELSE 0 END) AS hyperguest,
                            SUM(CASE WHEN channels = '1,6' THEN 1 ELSE 0 END) AS ambos,
                            COUNT(*) AS total_hoteles
                        FROM (
                            SELECT
                                r.hotel_id,
                                GROUP_CONCAT(DISTINCT rp.channel_id ORDER BY rp.channel_id) AS channels
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
                                AND rp.channel_id IN (1,6)
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
                            $sql_preferent
                            WHERE
                                i.deleted_at IS NULL 
                                AND i.date > NOW()
                            GROUP BY r.hotel_id
                        ) AS sub
                    ) AS final;
                 
                               
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
