<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDataHotelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        $this->down();

        DB::transaction(function () {


            $file_chains = json_decode(File::get("database/data/hotels/chains.json"));
            $file_hotels = json_decode(File::get("database/data/hotels/hotels.json"));
            $file_channel_hotel = json_decode(File::get("database/data/hotels/channel_hotel.json"));
            $file_hotel_users = json_decode(File::get("database/data/hotels/hotel_users.json"));
            $file_rooms = json_decode(File::get("database/data/hotels/rooms.json"));
            $file_channel_room = json_decode(File::get("database/data/hotels/channel_room.json"));
            $file_translations = json_decode(File::get("database/data/hotels/translations.json"));


            $chains = [];
            foreach ($file_chains as $key => $f_chains) {

                $f_chains = (array)$f_chains;
                $chain_id = $f_chains['id'];
                unset($f_chains['id']);
                $chain_id_new = DB::table('chains')->insertGetId($f_chains);

                $f_chains['id'] = $chain_id_new;

                $chains[$chain_id] = $f_chains;


            }



            $hotels = [];
            foreach ($file_hotels as $key => $f_hotels) {

                $f_hotels = (array)$f_hotels;
                $hotel_id = $f_hotels['id'];
                unset($f_hotels['id']);
                $f_hotels['chain_id'] = $chains[$f_hotels['chain_id']]['id'];

                $hotel_id_new = DB::table('hotels')->insertGetId($f_hotels);
                $f_hotels['id'] = $hotel_id_new;
                $hotels[$hotel_id] = $f_hotels;

            }


            foreach ($file_channel_hotel as $key => $f_channels) {

                $f_channels = (array)$f_channels;
                unset($f_channels['id']);
                $f_channels['hotel_id'] = $hotels[$f_channels['hotel_id']]['id'];
                DB::table('channel_hotel')->insertGetId($f_channels);

            }

            foreach ($file_hotel_users as $key => $f_users) {

                $f_users = (array)$f_users;
                $f_users['hotel_id'] = $hotels[$f_users['hotel_id']]['id'];
                unset($f_users['id']);
                DB::table('hotel_users')->insertGetId($f_users);

            }


            $rooms = [];
            foreach ($file_rooms as $key => $f_rooms) {

                $f_rooms = (array)$f_rooms;
                $room_id = $f_rooms['id'];
                unset($f_rooms['id']);
                $f_rooms['hotel_id'] = $hotels[$f_rooms['hotel_id']]['id'];
                $room_id_new = DB::table('rooms')->insertGetId($f_rooms);
                $f_rooms['id'] = $room_id_new;
                $rooms[$room_id] = $f_rooms;

            }


            foreach ($file_channel_room as $key => $f_channel_room) {

                $f_channel_room = (array)$f_channel_room;
                $f_channel_room['room_id'] = $rooms[$f_channel_room['room_id']]['id'];
                unset($f_channel_room['id']);
                DB::table('channel_room')->insertGetId($f_channel_room);

            }

            foreach ($file_translations as $key => $f_translations) {

                $f_translations = (array)$f_translations;
                $f_translations['object_id'] = $rooms[$f_translations['object_id']]['id'];
                unset($f_translations['id']);
                DB::table('translations')->insertGetId($f_translations);

            }


            Schema::table('policies_rates', function (Blueprint $table) {
                $table->text('description')->nullable()->change();
            });


            $created_at = date("Y-m-d H:i:s");

            $policie_cancelation_id = DB::table('policies_cancelations')->insertGetId([
                'name' => 'Política tarifa global',
                'status' => 1,
                'created_at' => $created_at
            ]);

            $policy_cancellation_parameters_id = DB::table('policy_cancellation_parameters')->insertGetId([
                'min_day' => 0,
                'max_day' => 0,
                'penalty_id' =>3,
                'created_at' => $created_at,
                'amount' =>NULL,
                'tax' =>0,
                'service' =>0,
                'policy_cancelation_id' => $policie_cancelation_id
            ]);


            $rate_plan_id = DB::table('policies_rates')->insertGetId([
                'name' => 'Política tarifa global',
                'status' => 1,
                'days_apply' => 'all',
                'max_ab_offset' => 0,
                'min_ab_offset' => 0,
                'min_length_stay' => 1,
                'max_length_stay' => 100,
                'max_occupancy' => 3,
                'policies_cancelation_id' => $policie_cancelation_id,
                'created_at' => $created_at
            ]);

            for($i=1; $i<5 ; $i++) {

                $policie_cancelation_id = DB::table('translations')->insertGetId([
                    'type' => 'rate_policies',
                    'object_id' => $rate_plan_id,
                    'slug' => 'policy_description',
                    'value' => 'Política de cancelación global para las tarifas de estela',
                    'language_id' => $i,
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

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');


        DB::table('progress_bars')->where('type', 'hotel')->delete();
        DB::table('progress_bars')->where('type', 'room')->delete();
        DB::table('translations')->where('type', 'hotel')->delete();
        DB::table('translations')->where('type', 'room')->delete();
        DB::table('translations')->where('type', 'rates_plan')->delete();
        DB::table('translations')->where('type', 'rate_policies')->delete();
        DB::table('galeries')->where('type', 'hotel')->delete();
        DB::table('galeries')->where('type', 'room')->delete();

        DB::table('contacts')->truncate();
        DB::table('chains')->truncate();
        DB::table('chains_multiple_properties')->truncate();
        DB::table('hotel_clients')->truncate();
        DB::table('client_rate_plans')->truncate();
        DB::table('inventory_bags')->truncate();
        DB::table('inventories')->truncate();
        DB::table('channel_room')->truncate();
        DB::table('bag_rooms')->truncate();
        DB::table('rates_plans_rooms')->truncate();
        DB::table('up_sellings')->truncate();
        DB::table('cross_sellings')->truncate();
        DB::table('policy_cancellation_parameters')->truncate();
        DB::table('policies_cancelations')->truncate();
        DB::table('bag_rooms')->truncate();
        DB::table('bag_rates')->truncate();
        DB::table('client_rate_plans')->truncate();
        DB::table('policies_rates')->truncate();
        DB::table('rate_supplements')->truncate();
        DB::table('rates_plans_rooms')->truncate();
        DB::table('rates_plans_promotions')->truncate();
        DB::table('rates_plans_calendarys')->truncate();
        DB::table('rates_plans')->truncate();
        DB::table('rates_histories')->truncate();
        DB::table('rates')->truncate();
        DB::table('contacts')->truncate();
        DB::table('hotel_taxes')->truncate();
        DB::table('amenity_hotel')->truncate();
        DB::table('channel_hotel')->truncate();
        DB::table('hotel_option_supplement_calendars')->truncate();
        DB::table('hotel_option_supplements')->truncate();
        DB::table('hotel_supplements')->truncate();
        DB::table('hotel_clients')->truncate();
        DB::table('hotel_users')->truncate();
        DB::table('rooms')->truncate();
        DB::table('bags')->truncate();
        DB::table('hotels')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');


    }
}
