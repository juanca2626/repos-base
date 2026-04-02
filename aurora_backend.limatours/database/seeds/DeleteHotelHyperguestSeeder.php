<?php

use App\Room;
use App\Hotel;
use App\RatesPlans;
use App\ChannelRoom;
use App\Translation;
use App\ChannelHotel;
use App\HotelTypeClass;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeleteHotelHyperguestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $year = now();
        $channelHotels = ChannelHotel::withTrashed()
            ->where('channel_id', 6)
            ->where('type', 2)
            ->where('code', 19912)
            ->get();

        $count = $channelHotels->count();
        $stats = [
            'hotels' => 0,
            'soft_deleted_hotels' => 0,
            'rooms' => 0,
            'rates' => 0,
            'translations' => 0
        ];

        foreach ($channelHotels as $channelHotel) {
            $hotelId = $channelHotel->hotel_id;

            // ELIMINAR TARIFAS (incluyendo soft deleted)
            $ratesDeleted = RatesPlans::withTrashed()
                ->where('hotel_id', $hotelId)
                ->where('channel_id', 6)
                ->forceDelete();
            $stats['rates'] += $ratesDeleted;

            // ELIMINAR ROOMS y sus relaciones
            $rooms = Room::withTrashed()->where('hotel_id', $hotelId)->get();
            $stats['rooms'] += $rooms->count();

            foreach ($rooms as $room) {
                // Eliminar ChannelRoom (incluyendo soft deleted)
                $channelRoomsDeleted = ChannelRoom::withTrashed()
                    ->where('channel_id', 6)
                    ->where('type', 2)
                    ->where('room_id', $room->id)
                    ->forceDelete();
                $stats['rates'] += $channelRoomsDeleted;

                // Eliminar traducciones
                $translationsDeleted = Translation::withTrashed()
                    ->where('slug', 'room_name')
                    ->where('object_id', $room->id)
                    ->forceDelete();
                $stats['translations'] += $translationsDeleted;

                // Eliminar room permanentemente
                $room->forceDelete();
            }

            // ELIMINAR HotelTypeClass
            HotelTypeClass::where('hotel_id', $hotelId)
                ->where('year', $year)
                ->forceDelete();

            // ELIMINAR HOTEL (verificar si estaba soft deleted)
            $hotel = Hotel::withTrashed()->find($hotelId);
            if ($hotel) {
                $stats['hotels']++;

                // Verificar si el hotel ya estaba eliminado (soft delete)
                if (!is_null($hotel->deleted_at)) {
                    $stats['soft_deleted_hotels']++;
                }

                $hotel->forceDelete();
            }

            // ELIMINAR CHANNEL HOTEL
            $channelHotel->forceDelete();
        }

        // Mostrar reporte
        $this->command->info("=== ELIMINACIÓN COMPLETADA (Laravel 5.8) ===");
        $this->command->info("Total de hoteles procesados: " . $count);
        $this->command->info("Hoteles eliminados permanentemente: {$stats['hotels']}");
        $this->command->info("Hoteles que ya estaban eliminados (soft delete): {$stats['soft_deleted_hotels']}");
        $this->command->info("Habitaciones eliminadas: {$stats['rooms']}");
        $this->command->info("Tarifas/planes eliminados: {$stats['rates']}");
        $this->command->info("Traducciones eliminadas: {$stats['translations']}");

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
