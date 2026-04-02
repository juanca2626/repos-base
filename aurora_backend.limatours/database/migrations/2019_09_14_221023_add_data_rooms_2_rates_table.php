<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDataRooms2RatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        DB::transaction(function () {

            $created_at = date("Y-m-d H:i:s");
            $languages = array('1','2','3','4');
            $typeRooms = ['1' => 'SGL' , '2' => 'DBL' , '3' => 'TPL'];
            $created_at = date('Y-m-d H:i:s');
            $policies_rates = $this->getPoliticaTarifa();
            $meal_id = 2;
            $inventario = 10;


            $channel_hotel = DB::table('channel_hotel')->get()->toArray();

            $hotelesAurora = [];
            foreach ($channel_hotel as $key => $hotel) {
                $hotelesAurora[$hotel->code] = $hotel->hotel_id;
            }

            $ratesEstela = json_decode(File::get("database/data/hotels/rates/rates.json"));



            $rates = [];
            foreach ($ratesEstela as $rate)
            {
                if(!array_key_exists($rate->hotel,$hotelesAurora))continue;
                if(!is_numeric($rate->precio))continue;

                //if($rate->hotel != "AQPHAD")continue;
                //if($rate->hotel != "LIMHTB")continue;
                //if($rate->hotel != "LIMHTR")continue;

                $rate->fechaini = $this->retornaFecha($rate->fechaini);
                $rate->fechafin = $this->retornaFecha($rate->fechafin);
                $rates[$rate->hotel][$rate->habitacion][$rate->ocupacion][] =  $rate;

            }



            $roomsAurorDB = DB::select('SELECT * FROM rooms');

            $roomsAurora = [];
            foreach ($roomsAurorDB as $room)
            {
                $roomsAurora[$room->estela_id][$room->max_capacity] =  $room->id;

            }

            foreach ($rates as $hotel_code_estela => $habitaciones ){

                $hotel_id = $hotelesAurora[$hotel_code_estela];
                $rate_plan_id = $this->crearPlanTarifario2($hotel_id,$hotel_code_estela,$meal_id,$created_at);
                $dataRooms = [];
                $grupo = 0;
                foreach ($habitaciones as $room_id_estela => $ocupaciones ){

                    if(!array_key_exists($room_id_estela,$roomsAurora))continue;

                    foreach ($ocupaciones as $ocupacion =>  $periodos){

                        if(!array_key_exists($ocupacion,$roomsAurora[$room_id_estela]))continue;


                        $periodosOrdenados = $this->orderMultiDimensionalObjeto($periodos,'fechaini' , 'asc');

                        $calendario = [];

                        foreach ($periodos as $key => $periodo) {

                            $precio = $periodo->ocupacion * $periodo->precio;

                            $fechas = $this->listaFechaPorDia($periodo->fechaini, $periodo->fechafin);
                            foreach ($fechas as $key => $fecha) {
                                $calendario[$fecha] = $precio;
                            }
                        }


                        $room_id = $roomsAurora[$room_id_estela][$ocupacion];


                        $rates_plans_room_id = $this->crearPlanTarifarioHabitciones2($room_id,$rate_plan_id,$created_at);

                        foreach ($calendario as $fecha => $importe) {
                            $this->crearCalendario2($fecha,$policies_rates->id,$rates_plans_room_id,$created_at,$importe);
                            $this->createInventario2($rates_plans_room_id,$fecha,$inventario,$created_at);
                        }

                        // armamos la estructura para el historial de tarifas
                        foreach ($periodos as $key => $periodo) {

                            $precio = $periodo->ocupacion * $periodo->precio;

                            $habit = new \stdClass();
                            $habit->id = $room_id;
                            $habit->adult = $precio;
                            $habit->child = 0;
                            $habit->extra = 0;
                            $habit->infant = 0;
                            $habit->dates_from = $this->retornaFechaBD($periodo->fechaini);
                            $habit->dates_to = $this->retornaFechaBD($periodo->fechafin);
                            $habit->policy_id = $policies_rates->id;
                            $habit->group = $grupo;
                            $habit->translations = $this->getRoomsTranslate($room_id);

                            $dataRooms[] = $habit;
                            $grupo++;
                        }

                    }
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



    public function crearCalendario2($date,$policies_rate_id,$rates_plans_room_id,$created_at,$price){

        $rates_plans_calendarys_id = DB::table('rates_plans_calendarys')->insertGetId([
            'date' => $date,
            'policies_rate_id'=>$policies_rate_id,
            'status' => 1,
            'rates_plans_room_id' => $rates_plans_room_id,
            'created_at' => $created_at
        ]);


        $rate_id = DB::table('rates')->insertGetId([
            'rates_plans_calendarys_id' => $rates_plans_calendarys_id,
            'price_adult'=>$price,
            'price_child'=>0,
            'price_infant'=>0,
            'price_extra'=>0
        ]);


    }

    public function createInventario2($rate_plan_rooms_id,$fecha,$inventario,$created_at){

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

    public function crearPlanTarifarioHabitciones2($room_id,$rates_plans_id,$created_at){

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

    public function crearPlanTarifario2($hotel_id,$hotel_code,$meal_id,$created_at){

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

    public function getPoliticaTarifa(){


        $policies_rates = DB::select('SELECT * FROM policies_rates where hotel_id is null ');

        if(count($policies_rates)>0){
            return $policies_rates[0];
        }

    }

    public  function retornaFechaBD($fecha)
    {
        $dia = date("d/m/Y", strtotime($fecha));
        return $dia;
    }


    public function retornaFecha($fecha)
    {
        if (trim($fecha)) {
            $fecha = explode("/", trim($fecha));
            $fecha = $fecha[2] . "-" . $fecha[1] . "-" . $this->diaMes($fecha[0]);
            return $fecha;
        }
        return "";
    }
    public function diaMes($dM)
    {
        return substr("00" . $dM, -2);
    }

    public function getLanguages(){


        $languages = DB::select(' SELECT * FROM languages WHERE state=1 ');

        return $languages;

    }

    public static function orderMultiDimensionalObjeto($array, $sortby, $direction = 'asc')
    {
        $sortedArr = array();
        $tmp_Array = array();

        foreach ($array as $k => $v) {
            $tmp_Array[] = strtolower($v->$sortby);
        }

        if ($direction == 'asc') {
            asort($tmp_Array);
        } else {
            arsort($tmp_Array);
        }

        foreach ($tmp_Array as $k => $tmp) {
            $sortedArr[] = $array[$k];
        }

        return $sortedArr;
    }

    public  function listaFechaPorDia($desdeFecha, $hastaFecha)
    {
        $listaFechas = Array();
        do {
            array_push($listaFechas, $desdeFecha);
            $desdeFecha = $this->agregaDias($desdeFecha, "1");
        } while ($desdeFecha <= $hastaFecha);

        return $listaFechas;
    }

    public static function agregaDias($fecha, $dias)
    {
        $fec_vencimi = date("Y-m-d", strtotime("$fecha + $dias days"));
        return $fec_vencimi;
    }

    public function getRoomsTranslate($room_id){


        $languages = DB::select(' SELECT * FROM translations WHERE TYPE="room"  AND object_id = ' . $room_id);

        return $languages;

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
}
