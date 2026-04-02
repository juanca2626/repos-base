<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDataHotelsRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        DB::transaction(function () {

            $date_ini_2019 = date('Y-m-d');
            $date_fin_2019 = '2019-12-31';
            $date_ini_2020 = '2020-01-01';
            $date_fin_2020 = '2020-12-31';

            $fechas2019 = $this->listaFechaPorDia($date_ini_2019, $date_fin_2019);
            $fechas2020 = $this->listaFechaPorDia($date_ini_2020, $date_fin_2020);

            $created_at = date('Y-m-d H:i:s');
            $policies_rates = $this->getPoliticaTarifa();
            $meal_id = 2;
            $inventario = 10;

            $tarifasEstelas = json_decode(File::get("database/data/hotels/rates.json"));



            foreach ($tarifasEstelas as $key => $tarifaEstela) {

                $rooms = $this->getRooms($tarifaEstela->hotel_code);

                if(count($rooms)==0)continue;

                $hotel_id = $rooms[0]->hotel_id;
                $rate_plan_id = $this->crearPlanTarifario($hotel_id,$tarifaEstela->hotel_code,$meal_id,$created_at);

                foreach ($rooms as $key => $room) {

                    $rates_plans_room_id = $this->crearPlanTarifarioHabitciones($room->id,$rate_plan_id,$created_at);



                    foreach ($fechas2019 as $fecha) {
                        $this->crearCalendario($fecha,$policies_rates->id,$rates_plans_room_id,$created_at,$tarifaEstela->price_2019,$room->max_capacity);
                        $this->createInventario($rates_plans_room_id,$fecha,$inventario,$created_at);
                    }

                    foreach ($fechas2020 as $fecha) {
                        $this->crearCalendario($fecha,$policies_rates->id,$rates_plans_room_id,$created_at,$tarifaEstela->price_2020,$room->max_capacity);
                        $this->createInventario($rates_plans_room_id,$fecha,$inventario,$created_at);
                    }


                }



                $dataRooms = [];



                foreach ($rooms as $key => $room) {

                    $precioAdulto2019 = $tarifaEstela->price_2019 * $room->max_capacity;

                    $habit = new \stdClass();
                    $habit->id = $room->id;
                    $habit->adult = $precioAdulto2019;
                    $habit->child = 0;
                    $habit->extra = 0;
                    $habit->infant = 0;
                    $habit->dates_from = $this->retornaFechaBD($date_ini_2019);
                    $habit->dates_to = $this->retornaFechaBD($date_fin_2019);
                    $habit->policy_id = $policies_rates->id;
                    $habit->group = 0;
                    $habit->translations = $this->getRoomsTranslate($room->id);

                    $dataRooms[] = $habit;
                }

                foreach ($rooms as $key => $room) {

                    $precioAdulto2020 = $tarifaEstela->price_2020 * $room->max_capacity;

                    $habit = new \stdClass();
                    $habit->id = $room->id;
                    $habit->adult = $precioAdulto2020;
                    $habit->child = 0;
                    $habit->extra = 0;
                    $habit->infant = 0;
                    $habit->dates_from = $this->retornaFechaBD($date_ini_2020);
                    $habit->dates_to = $this->retornaFechaBD($date_fin_2020);
                    $habit->policy_id = $policies_rates->id;
                    $habit->group = 1;
                    $habit->translations = $this->getRoomsTranslate($room->id);

                    $dataRooms[] = $habit;
                }

                $rate_id = DB::table('rates_histories')->insertGetId([
                    'rates_plan_id' => $rate_plan_id,
                    'meal_id'=>$meal_id,
                    'hotel_id'=>$hotel_id,
                    'data'=>'{}',
                    'dataRooms'=> json_encode($dataRooms),
                    'created_at' => $created_at
                ]);

            }


        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }


    public function getRoomsTranslate($room_id){


        $languages = DB::select(' SELECT * FROM translations WHERE TYPE="room"  AND object_id = ' . $room_id);

        return $languages;

    }

    public function crearCalendario($date,$policies_rate_id,$rates_plans_room_id,$created_at,$price,$ocupacion){

        $rates_plans_calendarys_id = DB::table('rates_plans_calendarys')->insertGetId([
            'date' => $date,
            'policies_rate_id'=>$policies_rate_id,
            'status' => 1,
            'rates_plans_room_id' => $rates_plans_room_id,
            'created_at' => $created_at
        ]);

        $precioAdulto = $price * $ocupacion;

        $rate_id = DB::table('rates')->insertGetId([
            'rates_plans_calendarys_id' => $rates_plans_calendarys_id,
            'price_adult'=>$precioAdulto,
            'price_child'=>0,
            'price_infant'=>0,
            'price_extra'=>0
        ]);


    }

    public function createInventario($rate_plan_rooms_id,$fecha,$inventario,$created_at){

        $dia = date("d", strtotime($fecha));
        $dia = intval($dia);

        $inventories_id = DB::table('inventories')->insertGetId([
            'day' => $dia,
            'date'=>$fecha,
            'inventory_num' => $inventario,
            'rate_plan_rooms_id' => $rate_plan_rooms_id,
            'locked' => 0,
            'created_at' => $created_at
        ]);

    }


    public function crearPlanTarifarioHabitciones($room_id,$rates_plans_id,$created_at){

        $rate_plan_room_id = DB::table('rates_plans_rooms')->insertGetId([
            'rates_plans_id' => $rates_plans_id,
            'room_id'=>$room_id,
            'status' => 1,
            'created_at' => $created_at,
            'bag' => 0,
            'channel_id' => 1
        ]);

        return $rate_plan_room_id;

    }

    public function crearPlanTarifario($hotel_id,$hotel_code,$meal_id,$created_at){

        $rate_plan_id = DB::table('rates_plans')->insertGetId([
            'code' => $hotel_code,
            'name'=>"Tarifa Base Estela",
            'allotment' => 0,
            'taxes' => 0,
            'services' => 0,
            'timeshares' => 0,
            'promotions' => 0,
            'status' => 1,
            'meal_id' => $meal_id,
            'rates_plans_type_id' => 1,
            'charge_type_id' => 1,
            'hotel_id' => $hotel_id,
            'channel_id' => 1 ,
            'created_at' => $created_at
        ]);

        $languages = $this->getLanguages();

        foreach ($languages as $key => $language) {

            $translations_id = DB::table('translations')->insertGetId([
                'type' => 'rates_plan',
                'object_id'=>$rate_plan_id,
                'slug' => 'commercial_name',
                'value' => "Tarifa Base Estela",
                'language_id' => $language->id
            ]);

        }

        return $rate_plan_id;

    }

    public function getLanguages(){


        $languages = DB::select(' SELECT * FROM languages WHERE state=1 ');

        return $languages;

    }

    public function getPoliticaTarifa(){


        $policies_rates = DB::select('SELECT * FROM policies_rates where hotel_id is null ');

        if(count($policies_rates)>0){
            return $policies_rates[0];
        }

    }

    public function getRooms($code){


        $rooms = DB::select('SELECT rooms.* FROM `channel_hotel` INNER JOIN rooms ON channel_hotel.`hotel_id` = rooms.`hotel_id`
                                        WHERE channel_hotel.code="'.$code.'"');

        return $rooms;

    }

    public function listaFechaPorDia($desdeFecha, $hastaFecha)
    {
        $listaFechas = Array();
        do {
            array_push($listaFechas, $desdeFecha);
            $desdeFecha = $this->agregaDias($desdeFecha, "1");
        } while ($desdeFecha <= $hastaFecha);

        return $listaFechas;
    }

    public function agregaDias($fecha, $dias)
    {
        $fec_vencimi = date("Y-m-d", strtotime("$fecha + $dias days"));
        return $fec_vencimi;
    }


    public function retornaFechaBD($fecha)
    {

        return date("d/m/Y", strtotime($fecha));
    }




}
