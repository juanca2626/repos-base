<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChannelHotelAndRoomTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // HOTELES
        DB::table('channel_hotel')
            ->where('channel_id', 6)
            ->update(['type' => 1]);

        $this->command->info('[ChannelHotel] Se actualizo el campo type con valor 1, a los registros que tiene el channel_id = 6');

        DB::table('channel_hotel')
            ->where('channel_id', 7)
            ->update([
                'type' => 2,
                'channel_id' => 6
            ]);

        $this->command->info('[ChannelHotel] Se actualizo el campo type con valor 2 y channel_id  a 6, a los registros que tiene el channel_id = 7');

        // ROOMS
        DB::table('channel_room')
            ->where('channel_id', 6)
            ->update(['type' => 1]);

        $this->command->info('[ChannelRoom] Se actualizo el campo type con valor 1, a los registros que tiene el channel_id = 6');

        DB::table('channel_room')
            ->where('channel_id', 7)
            ->update([
                'type' => 2,
                'channel_id' => 6
            ]);

        $this->command->info('[ChannelHotel] Se actualizo el campo type con valor 2 y channel_id  a 6, a los registros que tiene el channel_id = 7');

        DB::table('rates_plans')->where('channel_id', 7)->update([
            'channel_id' => 6
        ]);

        $this->command->info('[RatesPlans] Se actualizo el campo channel_id 7 a 6');

        DB::table('channels')->where('id', 6)->update([
            'name' => 'HYPERGUEST'
        ]);

        DB::table('channels')->where('id', 7)->update([
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => now()
        ]);

        $this->command->info('[Channels] Se Elimina de manera lógica el channel id: 7 , si en caso existe');
    }
}
