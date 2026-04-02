<?php

namespace App\Http\Allotment\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class HotelExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{  
 
    protected $params; 

    public function __construct($params = [])
    {
        $this->params = $params;
    }

    public function headings(): array
    {
        return [
            'PERIODO',
            'CADENA',
            'CIUDAD',
            'CÓDIGO AURORA',
            'HOTEL',
            'PREFERENTE',
            'CHANNEL',
            'CATEGORIA LITO',
            'RELEASE',
            'ID',
            'RATE PLANS ACTIVO',
            'NOMBRE DE HAB',
            // 'Occupation',
            'COD HAB',
            // 'Rate Name',
            // 'Total Inventory',
            'CUPO DE HABITACIONES SIMPLE',
            'CUPO DE HABITACIONES DOBLE',
            'CUPO DE HABITACIONES TRIPLE',
        ];
    }

 public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:P1')->applyFromArray([
            'font' => [
                'bold' => true, 
            ], 
            'alignment' => [
                'horizontal' => 'center',
                'vertical' => 'center',
            ],
        ]);
    }
    
    
    public function array(): array
    {


        $from = Carbon::parse($this->params['date_from']);
        $to   = Carbon::parse($this->params['date_to']);

        $period = strtoupper(
            $from->format('M-Y') . ' - ' . $to->format('M-Y')
        );


        $sql = "
        
            SELECT
                ANY_VALUE(cha.name)                                   AS cadena,
                ANY_VALUE(tr_state.value)                             AS city,

                MAX(CASE WHEN ch.channel_id = 1 THEN ch.code END)     AS hotel_code_aurora,
                ANY_VALUE(h.name)                                     AS hotel,

                ANY_VALUE(IF(htp.value = 1, 'Si', 'NO'))              AS preferente,
                ANY_VALUE(IF(rp.channel_id = 6, 'HP', 'AURORA'))      AS channel,

                -- ANY_VALUE(htc.typeclass_id)                           AS typeclass_id,
                ANY_VALUE(tr_typeclass.value)                         AS typeclass_description,

                '-'                                             AS release_type,

                MAX(CASE WHEN ch.channel_id = 6 THEN ch.code END)     AS hotel_code_hyperguest,

                ANY_VALUE(rpt.name)                                   AS rate_plans_activo,
                ANY_VALUE(tr_room.value)                              AS room_name,
                -- rt.occupation                                         AS occupation,

                MAX(CASE WHEN chr.channel_id = 6 THEN chr.code END)   AS room_code_hyperguest,

                -- ANY_VALUE(rp.name)                                    AS rate_name,

                /* =========================
                INVENTARIO (YA CORRECTO)
                ========================= */
                -- ANY_VALUE(i.total_inventory)                          AS total_inventory,

                CASE WHEN rt.occupation = 1 THEN ANY_VALUE(i.total_inventory) ELSE 0 END AS simples,
                CASE WHEN rt.occupation = 2 THEN ANY_VALUE(i.total_inventory) ELSE 0 END AS dobles,
                CASE WHEN rt.occupation = 3 THEN ANY_VALUE(i.total_inventory) ELSE 0 END AS triples

            FROM (

                /* ==================================
                PRE-AGREGACIÓN INVENTORIES
                (SUMA DIARIA REAL)
                ================================== */
                SELECT
                    i.rate_plan_rooms_id,
                    SUM(
                        CASE 
                            WHEN i.locked = 0 THEN i.inventory_num 
                            ELSE 0 
                        END
                    ) AS total_inventory
                FROM inventories i
                WHERE (i.locked IS NULL OR i.locked <> 1) AND i.deleted_at IS NULL
                AND i.date BETWEEN '{$this->params['date_from']}' AND '{$this->params['date_to']}'
                GROUP BY i.rate_plan_rooms_id

            ) i

            /* =========================
            JOINS PRINCIPALES
            ========================= */

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
            JOIN rates_plans_types rpt
                ON rpt.id = rp.rates_plans_type_id

            JOIN rooms r
                ON r.id = rpr.room_id
                AND r.inventory = 1
                AND r.state = 1
                AND r.deleted_at IS NULL

            JOIN room_types rt
                ON rt.id = r.room_type_id

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
                {$this->params['sql_chain']}
                {$this->params['sql_start']} 

            /* =========================
            TYPE CLASS (OPTIMIZADO)
            ========================= */
            
            JOIN (
                SELECT hotel_id, MIN(id) AS min_id
                FROM hotel_type_classes                
                WHERE YEAR = '{$this->params['anio']}'
                {$this->params['sql_type_classes']} 
                GROUP BY hotel_id
            ) htc_min ON htc_min.hotel_id = h.id

            JOIN hotel_type_classes htc
                ON htc.id = htc_min.min_id
            

            LEFT JOIN translations tr_typeclass
                ON tr_typeclass.type = 'typeclass'
                AND tr_typeclass.object_id = htc.typeclass_id
                AND tr_typeclass.language_id = 1
                AND tr_typeclass.deleted_at IS NULL 

            /* =========================
            PREFERENTE (OPTIMIZADO)
            ========================= */
             
            JOIN (
                SELECT hotel_id, MIN(id) AS min_id
                FROM hotel_preferentials
                WHERE YEAR = '{$this->params['anio']}'
                GROUP BY hotel_id
            ) htp_min ON htp_min.hotel_id = h.id

            JOIN hotel_preferentials htp
                ON htp.id = htp_min.min_id            

            JOIN translations tr_state
                ON tr_state.type = 'state'
                AND tr_state.slug = 'state_name'
                AND tr_state.language_id = 1
                AND tr_state.object_id = h.state_id
                AND tr_state.deleted_at IS NULL

            /* =========================
            CANALES (SIN DUPLICAR)
            ========================= */
            LEFT JOIN channel_hotel ch
                ON ch.hotel_id = h.id
               -- AND ch.channel_id {$this->params['sql_channel_property']}

            LEFT JOIN channel_room chr
                ON chr.room_id = r.id
              --  AND chr.channel_id {$this->params['sql_channel_property']}

            JOIN chains cha
                ON cha.id = h.chain_id

            /* =========================
            GROUP FINAL
            ========================= */
            GROUP BY
                h.id,
                r.id,
                rp.id,
                rt.occupation;
            
        ";
        // dd($sql);
        $rows = DB::select($sql); 
        $rows = json_decode(json_encode($rows), true); 
        
        $rows = array_map(function ($row) use ($period) {
            return array_merge(
                ['periodo' => $period], // 👈 columna fija
                $row
            );
        }, $rows);

        return $rows;

    }

    public function backup(): array
    {
        $sql = "
            SELECT
                cha.name AS cadena, 
                tr_state.value AS city,  
                MAX(CASE WHEN ch.channel_id = 1 THEN ch.code END) AS hotel_code_aurora,
                h.name AS hotel,
                IF(htp.value = 1, 'Si', 'NO') AS preferente,
                IF(rp.channel_id = 6, 'HP', 'AURORA') AS channel,
                htc.`typeclass_id`,
                'release',
                MAX(CASE WHEN ch.channel_id = 6 THEN ch.code END) AS hotel_code_hyperguest,
                rpt.name AS rate_plans_activo,
                tr_room.value AS room_name,
                rt.occupation AS occupation, 
                -- MAX(CASE WHEN chr.channel_id = 1 THEN chr.code END) AS room_code_aurora,
                MAX(CASE WHEN chr.channel_id = 6 THEN chr.code END) AS room_code_hyperguest,    
                rp.name AS rate_name,
                
                
                -- Total general sin considerar bloqueados
                SUM(CASE WHEN i.locked = 0 THEN i.inventory_num ELSE 0 END) AS total_inventory,

                -- Nuevas columnas según occupation (solo inventario no bloqueado)
                SUM(
                    CASE 
                        WHEN rt.occupation = 1 AND i.locked = 0 THEN i.inventory_num
                        ELSE 0
                    END
                ) AS simples,

                SUM(
                    CASE 
                        WHEN rt.occupation = 2 AND i.locked = 0 THEN i.inventory_num
                        ELSE 0
                    END
                ) AS dobles,

                SUM(
                    CASE 
                        WHEN rt.occupation = 3 AND i.locked = 0 THEN i.inventory_num
                        ELSE 0
                    END
                ) AS triples

            FROM inventories i
            JOIN rates_plans_rooms rpr
                ON rpr.id = i.rate_plan_rooms_id
                AND rpr.status = 1
                AND rpr.deleted_at IS NULL
            JOIN rates_plans rp
                ON rp.id = rpr.rates_plans_id
                AND rp.status = 1
                AND rp.allotment = 1
                -- {$this->params['sql_plans_type_id']}
                -- {$this->params['sql_channel']}
                AND rp.deleted_at IS NULL
            JOIN rates_plans_types rpt
            ON rpt.id = rp.rates_plans_type_id    
            JOIN rooms r
                ON r.id = rpr.room_id
                AND r.inventory = 1
                AND r.state = 1
                AND r.deleted_at IS NULL
            JOIN channel_room chr 
                    ON r.id = chr.room_id    
            JOIN room_types rt
                ON rt.id = r.room_type_id                     
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
                {$this->params['sql_chain']}
                {$this->params['sql_start']}  
            JOIN hotel_type_classes htc
                ON htc.id = (
                SELECT MIN(id)
                FROM hotel_type_classes
                WHERE hotel_id = h.id
                {$this->params['sql_type_classes']}
                AND YEAR = '{$this->params['anio']}'
                )
            JOIN hotel_preferentials htp
                ON htp.id = (
                SELECT MIN(id)
                FROM hotel_preferentials
                WHERE hotel_id = h.id
                AND YEAR = '{$this->params['anio']}'
                )   
            JOIN translations tr_state
                ON tr_state.type = 'state'
                AND tr_state.slug = 'state_name'
                AND tr_state.language_id = 1
                AND tr_state.object_id = h.state_id
                AND tr_state.deleted_at IS NULL 
                    
            JOIN channel_hotel ch 
                    ON h.id = ch.hotel_id    
            JOIN chains cha
                ON cha.id = h.chain_id    
            WHERE
                i.deleted_at IS NULL
                AND i.date BETWEEN  '{$this->params['date_from']}' AND '{$this->params['date_to']}'

            GROUP BY

                r.hotel_id,
                r.id,
                i.rate_plan_rooms_id,
                tr_state.value,
                tr_room.value,
                rt.occupation, 
                htp.value,
                rp.id,
                rp.name;
            
        ";
        // dd($sql);
        $rows = DB::select($sql);

        return json_decode(json_encode($rows), true); 
    }    

}
