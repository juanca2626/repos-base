<?php

namespace App\Http\Allotment\Controller;
 
use App\Http\Controllers\Controller; 
use Illuminate\Http\Request;  
use Illuminate\Support\Facades\DB;  
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\URL;


class DashboeardDetailsController extends Controller
{
 
    public function __construct(Request $request) {
     
    }

    public function index(Request $request)
    {
        $pageSize = 10;

        try {
           
            $sql = "

                   WITH base AS (

                        SELECT
                            h.id   AS hotel_id,
                            h.name AS hotel_name,
                            h.state_id,
                            (SELECT VALUE 
                            FROM translations 
                            WHERE TYPE = 'state'
                            AND slug = 'state_name'
                            AND language_id = 1
                            AND object_id = h.state_id
                            ) AS city,
                            (
                                SELECT VALUE
                                FROM translations
                                WHERE TYPE = 'typeclass'
                                AND object_id = htc.typeclass_id
                                AND language_id = 1
                                AND deleted_at IS NULL
                            ) AS category,
                            CASE
                                WHEN a.has_aurora = 1 AND hg.has_hyperguest = 1 THEN 'Ambos'
                                WHEN a.has_aurora = 1 THEN 'Aurora'
                                WHEN hg.has_hyperguest = 1 THEN 'Hyperguest'
                            END AS tipo_canal
                        FROM hotels h
                        JOIN hotel_type_classes htc
                            ON htc.id = (
                                SELECT MIN(id)
                                FROM hotel_type_classes
                                WHERE hotel_id = h.id
                                AND YEAR = '2026'
                            )

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
                            WHERE i.deleted_at IS NULL
                            AND i.date > NOW()
                        ) a ON a.hotel_id = h.id

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
                            WHERE i.deleted_at IS NULL
                            AND i.date > NOW()
                        ) hg ON hg.hotel_id = h.id

                        WHERE
                            h.status = 1
                            AND h.deleted_at IS NULL
                            AND (
                                a.has_aurora = 1
                                OR hg.has_hyperguest = 1
                            )
                    )

                    SELECT
                        b.state_id,
                        ANY_VALUE(b.city) AS city,

                        COUNT(*) AS total_hoteles,

                        SUM(b.tipo_canal = 'Aurora')     AS aurora,
                        SUM(b.tipo_canal = 'Hyperguest') AS hyperguest,
                        SUM(b.tipo_canal = 'Ambos')      AS ambos,

                        ROUND(SUM(b.tipo_canal = 'Aurora') * 100 / COUNT(*), 2)     AS pct_aurora,
                        ROUND(SUM(b.tipo_canal = 'Hyperguest') * 100 / COUNT(*), 2) AS pct_hyperguest,
                        ROUND(SUM(b.tipo_canal = 'Ambos') * 100 / COUNT(*), 2)      AS pct_ambos,

                        JSON_OBJECT(
                            'aurora',
                                COALESCE(
                                    (
                                        SELECT JSON_ARRAYAGG(
                                            JSON_OBJECT(
                                                'hotel_id', hotel_id,
                                                'hotel_name', hotel_name,
                                                'category', category
                                            )  
                                        )
                                        FROM base
                                        WHERE state_id = b.state_id
                                        AND tipo_canal = 'Aurora'
                                        
                                    ),
                                    JSON_ARRAY()
                                ),

                            'hyperguest',
                                COALESCE(
                                    (
                                        SELECT JSON_ARRAYAGG(
                                            JSON_OBJECT(
                                                'hotel_id', hotel_id,
                                                'hotel_name', hotel_name,
                                                'category', category
                                            )                                            
                                        )
                                        FROM base
                                        WHERE state_id = b.state_id
                                        AND tipo_canal = 'Hyperguest'
                                        
                                    ),
                                    JSON_ARRAY()
                                ),

                            'ambos',
                                COALESCE(
                                    (
                                        SELECT JSON_ARRAYAGG(
                                            JSON_OBJECT(
                                                'hotel_id', hotel_id,
                                                'hotel_name', hotel_name,
                                                'category', category
                                            ) 
                                        )
                                        FROM base
                                        WHERE state_id = b.state_id
                                        AND tipo_canal = 'Ambos'
                                        
                                    ),
                                    JSON_ARRAY()
                                )
                        ) AS details

                    FROM base b
                    GROUP BY b.state_id                     
                    ORDER BY 
                    b.city 
                    


            ";
            // dd($sql);
             
            $hotels = DB::table(DB::raw("($sql) as states"))->paginate($pageSize);

            $hotels->getCollection()->transform(function ($row) {
                $details = $row->details ? json_decode($row->details, true) : [];
                
                foreach (['aurora', 'hyperguest', 'ambos'] as $key) {
                    if (!empty($details[$key])) {
                        usort($details[$key], function ($a, $b) {
                            return strcasecmp($a['hotel_name'], $b['hotel_name']);
                        });
                    }
                }

                $row->hotels_aurora = $details['aurora'] ;
                $row->hotels_hyperguest = $details['hyperguest'] ;
                $row->hotels_ambos = $details['ambos'];
                unset($row->details); 
                return $row;
            });

            return response()->json($hotels);
        } catch (\Exception $ex) {
            return response()->json([
                'success' => false,
                'error'   => $ex->getMessage()
            ], 500);
        }
    }

 

}
