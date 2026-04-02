<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDataRooms2Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::transaction(function ()  {

            $created_at = date("Y-m-d H:i:s");
            $languages = array('1','2','3','4');
            $typeRooms = ['1' => 'SGL' , '2' => 'DBL' , '3' => 'TPL'];


            $habitaciones = json_decode(File::get("database/data/hotels/rates/rooms.json"));


            $channel_hotel = DB::table('channel_hotel')->get()->toArray();

            $hotelesAurora = [];
            foreach ($channel_hotel as $key => $hotel) {
                $hotelesAurora[$hotel->code] = $hotel->hotel_id;
            }

            foreach ($habitaciones as $habitacion)
            {

                $hotel_code = trim($habitacion->hotel);
                if(!array_key_exists($hotel_code,$hotelesAurora))continue;

                $hotel_id = $hotelesAurora[$habitacion->hotel];

                for($i=1;$i<=3;$i++){

                    $roomId = DB::table('rooms')->insertGetId([
                        'hotel_id' => $hotel_id,
                        'room_type_id' => $i,
                        'max_capacity'=> $i,
                        'min_adults'=> '1',
                        'max_adults'=> $i,
                        'max_child'=> '0',
                        'max_infants'=> '0',
                        'min_inventory'=> '0',
                        'state' => '1',
                        'estela_id' => $habitacion->id,
                        'created_at' => $created_at
                    ]);


                    $results = DB::table('channel_room')->insertGetId([
                        'code' => $roomId,
                        'room_id'=> $roomId,
                        'state' => '1',
                        'channel_id' => '1',
                        'created_at' => $created_at
                    ]);

                    $nameRoom = $habitacion->descripcion .' '. $typeRooms[$i];

                    foreach ($languages as $value) {

                        $results = DB::table('translations')->insertGetId([
                            'type' => 'room',
                            'object_id'=> $roomId,
                            'slug' => 'room_name',
                            'value' => $nameRoom,
                            'language_id' => $value,
                            'created_at' => $created_at
                        ]);

                        $results = DB::table('translations')->insertGetId([
                            'type' => 'room',
                            'object_id'=> $roomId,
                            'slug' => 'room_description',
                            'value' => $nameRoom,
                            'language_id' => $value,
                            'created_at' => $created_at
                        ]);
                    }
                }


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
}
