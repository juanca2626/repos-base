<?php

namespace App\Http\Allotment\Controller;
 
use App\Http\Controllers\Controller; 
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Response; 
use Illuminate\Support\Facades\DB;
use App\Http\Allotment\Traits\ReportFilters;

class GraficController extends Controller
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
            $type = $request->input('type', 'day');
   
            $result = $this->getInventory($type);
                             
            return response()->json([
                'success' => 'true',
                'data' => $result
            ]);

 
        }
        catch(\Exception $ex)
        {
            return response()->json([$this->throwError($ex)]);
        }
    }
 
    /*
    |--------------------------------------------------------------------------
    | LÓGICA DE CÁLCULO DE INVENTARIO POR TYPECLASS
    |--------------------------------------------------------------------------
    |
    | Esta función calcula el estado y score de inventario a nivel de:
    |  1) Habitación / tarifa
    |  2) Hotel
    |  3) TypeClass (grupo de hoteles)
    |
    |--------------------------------------------------------------------------
    | PASO 1: SCORE POR HABITACIÓN (room_score)
    |--------------------------------------------------------------------------
    |
    | A partir del campo inventories.inventory_num:
    |
    | inventory_num = 0        → room_score = 0   (agotado)
    | inventory_num = 1 ó 2    → room_score = 1 ó 2 (mínimo)
    | inventory_num = 3 ó 4 ó 5→ room_score = 3,4,5 (disponible)
    |
    | Cada habitación aporta un score individual.
    |
    |--------------------------------------------------------------------------
    | PASO 2: CÁLCULO DE PORCENTAJES POR HOTEL
    |--------------------------------------------------------------------------
    |
    | Para cada hotel se agrupan todas sus habitaciones y se calculan:
    |
    | porc_agotado     = % de habitaciones con score 0
    | porc_disponible  = % de habitaciones con score 3,4,5
    | porc_minima      = 100 - porc_agotado - porc_disponible
    |
    | Todos los porcentajes se formatean a 2 decimales.
    |
    |--------------------------------------------------------------------------
    | PASO 3: ESTADO DEL HOTEL (state_group)
    |--------------------------------------------------------------------------
    |
    | Según los porcentajes calculados:
    |
    | - estado = "agotado"
    |     si porc_agotado >= 20%
    |
    | - estado = "disponible"
    |     si porc_disponible >= 50%
    |
    | - estado = "minima"
    |     caso contrario
    |
    |--------------------------------------------------------------------------
    | PASO 4: SCORE DEL HOTEL (hotel_score)
    |--------------------------------------------------------------------------
    |
    | El hotel_score SIEMPRE es coherente con su estado:
    |
    | - Si estado = "agotado"
    |     → hotel_score = 0
    |
    | - Si estado = "minima"
    |     → se evalúan SOLO scores 1 y 2
    |     → hotel_score = el score más repetido (empate → mayor)
    |     → fallback seguro = 1
    |
    | - Si estado = "disponible"
    |     → se evalúan SOLO scores 3,4,5
    |     → hotel_score = el score más repetido (empate → mayor)
    |     → fallback seguro = 3
    |
    |--------------------------------------------------------------------------
    | PASO 5: CONTADORES POR TYPECLASS
    |--------------------------------------------------------------------------
    |
    | Para cada typeclass y fecha se cuentan:
    |
    | cnt_score_0 ... cnt_score_5 → cantidad de hoteles por score
    | agotados     → hoteles con estado "agotado"
    | minimas      → hoteles con estado "minima"
    | disponibles  → hoteles con estado "disponible"
    |
    |--------------------------------------------------------------------------
    | PASO 6: SCORE DEL TYPECLASS (score_del_periodo)
    |--------------------------------------------------------------------------
    |
    | El score_del_periodo se obtiene así:
    |
    | - Se toma el score (0–5) que MÁS se repite entre los hoteles
    | - En caso de empate, se elige el score MÁS ALTO
    |
    | Este valor es el que se usa para el gráfico por typeclass.
    |
    |--------------------------------------------------------------------------
    | PASO 7: ESTRUCTURA DE RESPUESTA
    |--------------------------------------------------------------------------
    |
    | Por cada fecha y typeclass se devuelve:
    |
    | - date
    | - typeclass_id
    | - typeclass_color
    | - typeclass_name
    | - total_hoteles_procesados
    | - score_del_periodo
    | - cnt_score_0 ... cnt_score_5
    | - agotados, minimas, disponibles
    | - detalle[] (lista de hoteles con:
    |       hotel_id,
    |       hotel_name,
    |       hotel_score,
    |       state_group,
    |       porc_agotado,
    |       porc_minima,
    |       porc_disponible
    |   )
    |
    |--------------------------------------------------------------------------
    | NOTA IMPORTANTE
    |--------------------------------------------------------------------------
    |
    | - La función está optimizada para usar UN SOLO QUERY SQL
    | - Toda la lógica de negocio se consolida en PHP
    | - Se evitan errores por arreglos vacíos (fallbacks seguros)
    | - Compatible con modo diario y mensual
    |
    |--------------------------------------------------------------------------
    */    

    public function getInventory(string $mode = 'day')
    {
        $isMonth = $mode === 'month';
        $dateField = $isMonth ? "DATE_FORMAT(i.date,'%Y-%m')" : "i.date";
        $dateAlias = $isMonth ? "ym" : "date";
        $dateCondition = $isMonth
            ? "DATE_FORMAT(i.date,'%Y-%m') BETWEEN DATE_FORMAT(?,'%Y-%m') AND DATE_FORMAT(?,'%Y-%m')"
            : "i.date BETWEEN ? AND ?";

        $sql = "
            SELECT
                {$dateField} AS {$dateAlias},
                htc.typeclass_id,
                r.hotel_id,
                h.name AS hotel_name,
                CASE
                    WHEN i.inventory_num = 0 THEN 0
                    WHEN i.inventory_num IN (1,2) THEN i.inventory_num
                    ELSE i.inventory_num
                END AS room_score,
                tc.color AS typeclass_color,
                t.value AS typeclass_name
            FROM inventories i
            JOIN rates_plans_rooms rpr ON rpr.id=i.rate_plan_rooms_id
                AND rpr.status=1 AND rpr.bag=0 AND rpr.deleted_at IS NULL
            JOIN rates_plans rp ON rp.id=rpr.rates_plans_id
                AND rp.status=1 AND rp.allotment=1 AND rp.deleted_at IS NULL
                {$this->params['sql_plans_type_id']}
                {$this->params['sql_channel']}
            JOIN rooms r ON r.id=rpr.room_id
                AND r.inventory=1 AND r.state=1 AND r.deleted_at IS NULL
            JOIN room_types rt ON rt.id=r.room_type_id
                AND rt.occupation={$this->params['occupation']}
            JOIN hotels h ON h.id=r.hotel_id
                AND h.status=1 AND h.deleted_at IS NULL
                AND h.country_id={$this->params['country_id']}
                {$this->params['sql_country_state']}
                {$this->params['city_id']}
                {$this->params['district_id']}
                {$this->params['sql_chain']}
                {$this->params['sql_start']}
            JOIN hotel_type_classes htc
                ON htc.id = (
                    SELECT MIN(id)
                    FROM hotel_type_classes
                    WHERE hotel_id=h.id
                    {$this->params['sql_type_classes']}
                    AND year='{$this->params['anio']}'
                )
            JOIN type_classes tc ON tc.id=htc.typeclass_id
            JOIN translations t ON t.type='typeclass'
                AND t.slug='typeclass_name'
                AND t.language_id=1
                AND t.object_id=htc.typeclass_id
                AND t.deleted_at IS NULL
            WHERE (i.locked IS NULL OR i.locked<>1)
            AND i.deleted_at IS NULL
            AND {$dateCondition}
        ";

        $rows = DB::select($sql, [$this->params['date_from'], $this->params['date_to']]);
     
        $grouped = collect($rows)->groupBy(function ($r) use ($dateAlias) {
            return $r->{$dateAlias} . '-' . $r->typeclass_id;
        });        

        $result = [];

        foreach ($grouped as $hotelesTypeclass) {

            $first = $hotelesTypeclass->first();

            $hoteles = $hotelesTypeclass
                ->groupBy('hotel_id')
                ->map(function ($habitaciones, $hotel_id) {

                    $scores = array_count_values($habitaciones->pluck('room_score')->toArray());
                    $total = count($habitaciones);

                    // $porc_agotado = ($scores[0] ?? 0) * 100 / $total;
                    // $porc_disponible = (($scores[3] ?? 0) + ($scores[4] ?? 0) + ($scores[5] ?? 0)) * 100 / $total;
                    // $porc_minima = 100 - $porc_agotado - $porc_disponible;

                    $porc_agotado = round((($scores[0] ?? 0) * 100) / $total, 2);

                    $porc_disponible = round(
                        ((($scores[3] ?? 0) + ($scores[4] ?? 0) + ($scores[5] ?? 0)) * 100) / $total,
                        2
                    );

                    $porc_minima = round(100 - $porc_agotado - $porc_disponible, 2);


                    if ($porc_agotado >= 20) {
                        $estado = 'agotado';
                        $hotel_score = 0;
                    }
                    elseif ($porc_disponible >= 50) {
                        $estado = 'disponible';
                        $subset = array_intersect_key($scores, array_flip([3,4,5]));
                        $hotel_score = $subset
                            ? max(array_keys($subset, max($subset)))
                            : 3;
                    }
                    else {
                        $estado = 'minima';
                        $subset = array_intersect_key($scores, array_flip([1,2]));
                        $hotel_score = $subset
                            ? max(array_keys($subset, max($subset)))
                            : 1;
                    }

                    return [
                        'hotel_id' => $hotel_id,
                        'hotel_name' => $habitaciones->first()->hotel_name,
                        'hotel_score' => $hotel_score,
                        'state_group' => $estado,
                        'porc_agotado' => $porc_agotado,
                        'porc_minima' => $porc_minima,
                        'porc_disponible' => $porc_disponible,
                    ];
                })->values();

            $cnt_score = array_fill(0, 6, 0);
            $agotados = $minimas = $disponibles = 0;

            foreach ($hoteles as $h) {
                $cnt_score[$h['hotel_score']]++;
                if ($h['state_group'] === 'agotado') $agotados++;
                elseif ($h['state_group'] === 'minima') $minimas++;
                else $disponibles++;
            }

            $max = max($cnt_score);
            // $score_del_periodo = max(array_keys(array_filter($cnt_score, fn($v) => $v === $max)));

            $score_del_periodo = max(
                array_keys(
                    array_filter($cnt_score, function ($v) use ($max) {
                        return $v === $max;
                    })
                )
            );

            $result[] = [
                'date' => $first->{$dateAlias},
                'typeclass_id' => $first->typeclass_id,
                'typeclass_color' => $first->typeclass_color,
                'typeclass_name' => $first->typeclass_name,
                'total_hoteles_procesados' => count($hoteles),
                'score_del_periodo' => $score_del_periodo,
                'cnt_score_0' => $cnt_score[0],
                'cnt_score_1' => $cnt_score[1],
                'cnt_score_2' => $cnt_score[2],
                'cnt_score_3' => $cnt_score[3],
                'cnt_score_4' => $cnt_score[4],
                'cnt_score_5' => $cnt_score[5],
                'agotados' => $agotados,
                'minimas' => $minimas,
                'disponibles' => $disponibles,
                'detalle' => $hoteles,
            ];
        }

        return $result;
    }

 
    public function getInventory__base(string $mode = 'day')
    { 
        $isMonth = $mode === 'month';

        // Campo DATE o YEAR-MONTH según modo
        $dateField = $isMonth 
            ? "DATE_FORMAT(i.date,'%Y-%m')" 
            : "i.date";

        $dateAlias = $isMonth ? "ym" : "date";

        // Condición WHERE
        // Día → BETWEEN normal
        // Mes → comparar primeras fechas del mes
        $dateCondition = $isMonth
            ? "DATE_FORMAT(i.date,'%Y-%m') BETWEEN DATE_FORMAT(?,'%Y-%m') AND DATE_FORMAT(?,'%Y-%m')"
            : "i.date BETWEEN ? AND ?";

        /**
         * -------------------------------
         * 1) SQL PRINCIPAL - TU SQL ADAPTADO
         * -------------------------------
         */
        $summarySql = "
            WITH hotel_daily AS (
                SELECT
                    {$dateField} AS {$dateAlias},
                    htc.typeclass_id,
                    r.hotel_id,
                    MIN(COALESCE(i.inventory_num, 0)) AS min_inventory
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
                JOIN hotel_type_classes htc
                    ON htc.id = (
                        SELECT MIN(id)
                        FROM hotel_type_classes
                        WHERE hotel_id = h.id
                        {$this->params['sql_type_classes']}
                        AND year = '{$this->params['anio']}' 
                    )                                     
                WHERE
                    (i.locked IS NULL OR i.locked <> 1) and i.deleted_at IS NULL
                    AND {$dateCondition}
                GROUP BY
                    {$dateAlias}, htc.typeclass_id, r.hotel_id
            ),

            hotel_scores AS (
                SELECT
                    {$dateAlias},
                    typeclass_id,
                    hotel_id,
                    CASE
                        WHEN min_inventory <= 0 THEN 0
                        WHEN min_inventory = 1 THEN 1
                        WHEN min_inventory = 2 THEN 2
                        WHEN min_inventory = 3 THEN 3
                        WHEN min_inventory = 4 THEN 4
                        ELSE 5
                    END AS hotel_score
                FROM hotel_daily
            ),

            state_group AS (
                SELECT
                    {$dateAlias},
                    typeclass_id,
                    hotel_id,
                    hotel_score,
                    CASE
                        WHEN hotel_score = 0 THEN 'agotado'
                        WHEN hotel_score IN (1,2) THEN 'minima'
                        ELSE 'disponible'
                    END AS state_group
                FROM hotel_scores
            ),

            day_state AS (
                SELECT
                    {$dateAlias},
                    typeclass_id,
                    state_group,
                    COUNT(*) AS qty
                FROM state_group
                GROUP BY
                    {$dateAlias},
                    typeclass_id,
                    state_group
            ),

            day_winner AS (
                SELECT
                    {$dateAlias},
                    typeclass_id,
                    state_group,
                    qty,
                    ROW_NUMBER() OVER (
                        PARTITION BY {$dateAlias}, typeclass_id
                        ORDER BY
                            qty DESC,
                            CASE state_group
                                WHEN 'agotado' THEN 1
                                WHEN 'minima' THEN 2
                                WHEN 'disponible' THEN 3
                            END ASC
                    ) AS rn
                FROM day_state
            ),

            day_winner_only AS (
                SELECT {$dateAlias}, typeclass_id, state_group
                FROM day_winner
                WHERE rn = 1
            ),

            score_candidates AS (
                SELECT
                    sg.{$dateAlias},
                    sg.typeclass_id,
                    sg.hotel_score,
                    COUNT(*) AS cnt,
                    ROW_NUMBER() OVER (
                        PARTITION BY sg.{$dateAlias}, sg.typeclass_id
                        ORDER BY COUNT(*) DESC, sg.hotel_score ASC
                    ) AS rn
                FROM state_group sg
                JOIN day_winner_only w
                    ON sg.{$dateAlias} = w.{$dateAlias}
                    AND sg.typeclass_id = w.typeclass_id
                    AND sg.state_group = w.state_group
                GROUP BY sg.{$dateAlias}, sg.typeclass_id, sg.hotel_score
            ),

            final_score AS (
                SELECT
                    {$dateAlias},
                    typeclass_id,
                    hotel_score AS score_del_dia
                FROM score_candidates
                WHERE rn = 1
            )

            SELECT
                f.{$dateAlias} AS date,
                f.typeclass_id,
                (SELECT color FROM type_classes WHERE id=f.typeclass_id) AS typeclass_color,
                (SELECT value FROM translations WHERE type = 'typeclass' AND slug = 'typeclass_name' AND language_id = 1 AND object_id = f.typeclass_id AND deleted_at IS NULL) AS typeclass_name,
                f.score_del_dia,
                COUNT(*) AS total_hoteles_procesados,
                SUM(CASE WHEN hs.hotel_score = 0 THEN 1 ELSE 0 END) AS cnt_score_0,
                SUM(CASE WHEN hs.hotel_score = 1 THEN 1 ELSE 0 END) AS cnt_score_1,
                SUM(CASE WHEN hs.hotel_score = 2 THEN 1 ELSE 0 END) AS cnt_score_2,
                SUM(CASE WHEN hs.hotel_score = 3 THEN 1 ELSE 0 END) AS cnt_score_3,
                SUM(CASE WHEN hs.hotel_score = 4 THEN 1 ELSE 0 END) AS cnt_score_4,
                SUM(CASE WHEN hs.hotel_score = 5 THEN 1 ELSE 0 END) AS cnt_score_5,
                SUM(CASE WHEN hs.hotel_score = 0 THEN 1 ELSE 0 END) AS agotados,
                SUM(CASE WHEN hs.hotel_score IN (1,2) THEN 1 ELSE 0 END) AS minimas,
                SUM(CASE WHEN hs.hotel_score >= 3 THEN 1 ELSE 0 END) AS disponibles  

            FROM final_score f
            JOIN hotel_scores hs
                ON hs.{$dateAlias} = f.{$dateAlias}
                AND hs.typeclass_id = f.typeclass_id            
            GROUP BY
                f.{$dateAlias},
                f.typeclass_id,
                f.score_del_dia
            ORDER BY
                f.{$dateAlias},
                f.typeclass_id
        ";
 
        // dd($summarySql);
        // dd(vsprintf(str_replace('?', "'%s'", $summarySql), [$this->params['date_from'], $this->params['date_to']]));
        // Ejecutamos el SUMMARY
        $summary = DB::select($summarySql, [$this->params['date_from'], $this->params['date_to']]);

        /**
         * --------------------------
         * 2) DETALLE POR HOTEL
         * --------------------------
         */

        $result = [];

        foreach ($summary as $row) {
            $keyDate = $row->date;
            $typeclass = $row->typeclass_id;

            // Query detalle por hotel (día o mes)
            $detailSql = $isMonth
                ? "
                    SELECT
                        DATE_FORMAT(i.date,'%Y-%m') AS ym,
                        htc.typeclass_id,
                        r.hotel_id,
                        h.name as hotel_name,
                        MIN(COALESCE(i.inventory_num,0)) AS min_inventory
                    FROM inventories i
                    JOIN rates_plans_rooms rpr ON rpr.id=i.rate_plan_rooms_id
                        AND rpr.status=1 AND rpr.bag = 0 AND rpr.deleted_at IS NULL
                    JOIN rates_plans rp
                        ON rp.id = rpr.rates_plans_id
                        AND rp.status = 1
                        AND rp.allotment = 1
                        AND rp.deleted_at IS NULL     
                        {$this->params['sql_plans_type_id']}
                        {$this->params['sql_channel']}
                    JOIN rooms r ON r.id=rpr.room_id AND r.inventory=1 AND r.state=1 AND r.deleted_at IS NULL
                    JOIN room_types rt  ON rt.id = r.room_type_id AND rt.occupation = {$this->params['occupation']}                 
                    JOIN hotels h ON h.id=r.hotel_id AND h.status=1 AND h.deleted_at IS NULL AND h.state_id =  {$this->params['state_id']} {$this->params['sql_chain']} {$this->params['sql_start']}   
                  
                    JOIN hotel_type_classes htc
                    ON htc.id = (
                        SELECT MIN(id)
                        FROM hotel_type_classes
                        WHERE hotel_id = h.id
                        AND typeclass_id=?
                        AND year = '{$this->params['anio']}' 
                    )


                    WHERE (i.locked IS NULL OR i.locked <> 1)  and DATE_FORMAT(i.date,'%Y-%m') = ?
                    GROUP BY r.hotel_id
                "
                : "
                    SELECT
                        i.date,
                        htc.typeclass_id,
                        r.hotel_id,
                        h.name as hotel_name,
                        MIN(COALESCE(i.inventory_num,0)) AS min_inventory
                    FROM inventories i
                    JOIN rates_plans_rooms rpr ON rpr.id=i.rate_plan_rooms_id AND rpr.status=1 AND rpr.bag = 0 AND rpr.deleted_at IS NULL
                    JOIN rates_plans rp
                        ON rp.id = rpr.rates_plans_id
                        AND rp.status = 1
                        AND rp.allotment = 1
                        AND rp.deleted_at IS NULL     
                        {$this->params['sql_plans_type_id']}
                        {$this->params['sql_channel']}                    
                    JOIN rooms r ON r.id=rpr.room_id AND r.inventory=1 AND r.state=1 AND r.deleted_at IS NULL
                    JOIN room_types rt  ON rt.id = r.room_type_id AND rt.occupation = {$this->params['occupation']} 
                    JOIN hotels h ON h.id=r.hotel_id AND h.status=1 AND h.deleted_at IS NULL AND h.state_id =  {$this->params['state_id']} {$this->params['sql_chain']} {$this->params['sql_start']}
                                                                              
                    JOIN hotel_type_classes htc
                    ON htc.id = (
                        SELECT MIN(id)
                        FROM hotel_type_classes
                        WHERE hotel_id = h.id
                        AND typeclass_id=?
                        AND year = '{$this->params['anio']}' 
                    )

                    WHERE (i.locked IS NULL OR i.locked <> 1) and i.date = ?
                    GROUP BY r.hotel_id
                ";
            // dd(vsprintf(str_replace('?', "'%s'", $detailSql), [2, $keyDate]));
            $detailRows = DB::select($detailSql, [$typeclass, $keyDate]);

            // Mapear estados de inventario
            $detail = collect($detailRows)->map(function ($h) {
                $min = $h->min_inventory;

                $score = $min <= 0 ? 0 :
                        ($min == 1 ? 1 :
                        ($min == 2 ? 2 :
                        ($min == 3 ? 3 :
                        ($min == 4 ? 4 : 5))));

                $state = $score == 0 ? 'agotado' :
                        (in_array($score,[1,2]) ? 'minima' : 'disponible');

                return [
                    'hotel_id' => $h->hotel_id,
                    'hotel_name' => $h->hotel_name,
                    'typeclass_id' => $h->typeclass_id,
                    'min_inventory' => $min,
                    'hotel_score' => $score,
                    'state_group' => $state,
                ];
            });

            $stateCount = $detail
                ->countBy('state_group'); 
                // Ej: ['disponible' => 3, 'minima' => 1]

            /*
            |--------------------------------------------------------------------------
            | 2. Definimos prioridad cuando todos se repiten igual
            |--------------------------------------------------------------------------
            */
            $priority = [
                'agotado'    => 1,
                'minima'     => 2,
                'disponible' => 3,
            ];

            $detailSorted = $detail->sort(function ($a, $b) use ($stateCount, $priority) {

                // A) primero por cantidad de repeticiones (desc)
                $countCompare = $stateCount[$b['state_group']]
                    <=> $stateCount[$a['state_group']];

                if ($countCompare !== 0) {
                    return $countCompare;
                }

                // B) si se repiten igual, usamos prioridad personalizada
                $priorityCompare = $priority[$a['state_group']]
                    <=> $priority[$b['state_group']];

                if ($priorityCompare !== 0) {
                    return $priorityCompare;
                }

                // C) dentro del mismo grupo, ordenar por nombre
                return strcmp($a['hotel_name'], $b['hotel_name']);
            })
            ->values();
    
    
            // Agregar resultado
            $result[] = [
                'date' => $keyDate,
                'typeclass_id' => $typeclass,
                'typeclass_color' => $row->typeclass_color,
                'typeclass_name' => $row->typeclass_name,                
                'total_hoteles_procesados' => $row->total_hoteles_procesados,
                'score_del_periodo' => $row->score_del_dia,
                'cnt_score_0' => $row->cnt_score_0,
                'cnt_score_1' => $row->cnt_score_1,
                'cnt_score_2' => $row->cnt_score_2,
                'cnt_score_3' => $row->cnt_score_3,
                'cnt_score_4' => $row->cnt_score_4,
                'cnt_score_5' => $row->cnt_score_5,
                'agotados' => $row->agotados,
                'minimas' => $row->minimas,
                'disponibles' => $row->disponibles,
                'detalle' => $detailSorted,
            ];
        }

        return $result;
    }
}
