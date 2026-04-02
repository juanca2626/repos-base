<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDataContactHotelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('contacts', function (Blueprint $table) {
            $table->unsignedBigInteger('surname')->nullable()->change();
            $table->unsignedBigInteger('lastname')->nullable()->change();
        });

        DB::transaction(function ()  {

            $created_at = date("Y-m-d H:i:s");

            $contactos = json_decode(File::get("database/data/hotels/rates/contact.json"));


            $channel_hotel = DB::table('channel_hotel')->get()->toArray();

            $hotelesAurora = [];
            foreach ($channel_hotel as $key => $hotel) {
                $hotelesAurora[$hotel->code] = $hotel->hotel_id;
            }

            foreach ($contactos as $contacto)
            {

                if(!array_key_exists($contacto->hotel,$hotelesAurora))continue;

                $hotel_id = $hotelesAurora[$contacto->hotel];

                $results = DB::table('contacts')->insertGetId([
                    'hotel_id'=> $hotel_id,
                    'name' => $contacto->nombre,
                    'position' => $contacto->cargo,
                    'status' => '1',
                    'email' => $contacto->email,
                    'principal' => 0,
                    'created_at'=>$created_at
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
}
