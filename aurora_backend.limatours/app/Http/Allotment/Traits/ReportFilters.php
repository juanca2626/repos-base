<?php
namespace App\Http\Allotment\Traits;

use Illuminate\Http\Request;

trait ReportFilters
{
    public function getReportFilters(Request $request)
    {
        $min_hotels = $request->input('min_hotels', 3);        
        $type_classes_id = $request->input('type_classes_id');
        $channel_id = $request->input('channel_id');
        $chain_id = $request->input('chain_id');
        $stars = $request->input('stars', []);
        $rates_plans_type_id = $request->input('rates_plans_type_id', []);
        $date_from = $request->input('date_from', '2025-12-01');
        $date_to = $request->input('date_to', '2026-01-31');
        $occupation = $request->input('occupation', 2);

        $anio = date("Y", strtotime($date_from));

        $destiny = $request->input('destination');
        $destiny_codes = explode(",", $destiny);
        $country_id = "89";
        $state_id = "";  //1610
        $city_id = "";
        $district_id = "";

        //separar codigos de destino         
        for ($i = 0; $i < count($destiny_codes); $i++) {
            if ($i == 0) {
                $country_id = $destiny_codes[$i];
            }
            if ($i == 1) {
                $state_id = $destiny_codes[$i];
            }
            if ($i == 2) {
                $city_id = " AND h.city_id = ".$destiny_codes[$i];
            }
            if ($i == 3) {
                $district_id = " AND h.district_id = ". $destiny_codes[$i];
            }
        }
        
        $sql_country_state = '';
        if($state_id){
            $sql_country_state .= ' and h.state_id ='.$state_id;
        }

        // SQL dinámicos
        $sql_type_classes = $type_classes_id ? "AND typeclass_id = $type_classes_id" : "AND typeclass_id <> 8";
        $sql_type_classes_table = ''; 

        if($type_classes_id !== NULL or $type_classes_id <> '')
        {             
            $sql_type_classes_table = "
               
                JOIN (
                    SELECT 
                        hotel_id,
                        MIN(id) AS min_id
                    FROM hotel_type_classes
                    WHERE typeclass_id =  ". $type_classes_id ."
                    AND YEAR = '".$anio."'
                    GROUP BY hotel_id
                ) htc_min
                    ON htc_min.hotel_id = h.id

                JOIN hotel_type_classes htc
                    ON htc.id = htc_min.min_id        
            ";

        }else{

            $sql_type_classes_table = "
               
                JOIN (
                    SELECT 
                        hotel_id,
                        MIN(id) AS min_id
                    FROM hotel_type_classes
                    WHERE typeclass_id <> 8
                    AND YEAR = '".$anio."'
                    GROUP BY hotel_id
                ) htc_min
                    ON htc_min.hotel_id = h.id

                JOIN hotel_type_classes htc
                    ON htc.id = htc_min.min_id        
            ";


        }

        $sql_channel = $channel_id 
            ? "AND rp.channel_id = '$channel_id'" 
            : "AND rp.channel_id IN (1,6)";

        $sql_channel_property  = $channel_id 
            ? "IN ('$channel_id')" 
            : "IN (1,6)";

        $sql_chain = $chain_id ? "AND h.chain_id = '$chain_id'" : "";
        
        $sql_start = '';
        if (!empty($stars) && is_array($stars)) { 
            $stars_clean = array_map('intval', $stars);
            $sql_start = " AND h.stars IN (" . implode(',', $stars_clean) . ") ";
        }

        $sql_plans_type_id = '';
        if (!empty($rates_plans_type_id) && is_array($rates_plans_type_id)) { 
            $rates_plans_clean = array_map('intval', $rates_plans_type_id);
            $sql_plans_type_id = " AND rp.rates_plans_type_id IN (" . implode(',', $rates_plans_clean) . ") ";
        }
 

        return [
            'min_hotels' => $min_hotels,
            'country_id' => $country_id,
            'state_id' => $state_id,
            'city_id' => $city_id,
            'district_id' => $district_id,
            'type_classes_id' => $type_classes_id,
            'channel_id' => $channel_id,
            'chain_id' => $chain_id,
            'stars' => $stars,
            'rates_plans_type_id' => $rates_plans_type_id,
            'date_from' => $date_from,
            'date_to' => $date_to,
            'occupation' => $occupation,
            'anio' => $anio,

            // SQL dinámicos
            'sql_type_classes' => $sql_type_classes,
            'sql_type_classes_table' => $sql_type_classes_table,
            // 'sql_type_classes_table_JOIN' => $sql_type_classes_table_JOIN,
            'sql_channel' => $sql_channel,
            'sql_chain' => $sql_chain,
            'sql_start' => $sql_start,
            'sql_plans_type_id' => $sql_plans_type_id,
            'sql_channel_property' => $sql_channel_property,
            'sql_country_state' => $sql_country_state
        ];
    }
}