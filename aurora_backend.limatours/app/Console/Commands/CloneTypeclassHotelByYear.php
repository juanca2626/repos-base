<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CloneTypeclassHotelByYear extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clone:typeclass-hotel-by-year {year}';

    /**
     * The console command description.
     *
     * @var string
     */
     protected $description = 'Clona datos de typeclass y preferenciales de hoteles del año anterior al año especificado';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $targetYear = (int) $this->argument('year');
        $sourceYear = $targetYear - 1;

        $this->info("Iniciando clonación desde el año {$sourceYear} al año {$targetYear}");

        // Obtener todos los hoteles activos
        $hotels = DB::table('hotels')->get();

        foreach ($hotels as $hotel) {

            // Verificar si ya existe registro en hotel_type_classes para evitar duplicados
            $existsTypeClass = DB::table('hotel_type_classes')
                ->where('hotel_id', $hotel->id)
                ->where('year', $targetYear)
                ->exists();

            if (!$existsTypeClass) {
                // Clonar hotel_type_classes
                $typeClasses = DB::table('hotel_type_classes')
                    ->where('hotel_id', $hotel->id)
                    ->where('year', $sourceYear)
                    ->get();

                foreach ($typeClasses as $typeClass) {
                    DB::table('hotel_type_classes')->insert([
                        'year' => $targetYear,
                        'typeclass_id' => $typeClass->typeclass_id,
                        'hotel_id' => $typeClass->hotel_id,
                    ]);
                }

                $this->info("Clonado hotel_type_classes para hotel ID: {$hotel->id}");
            } else {
                $this->warn("hotel_type_classes ya existe para hotel ID: {$hotel->id} en el año {$targetYear}");
            }

            // Verificar si ya existe registro en hotel_preferentials para evitar duplicados
            $existsPreferential = DB::table('hotel_preferentials')
                ->where('hotel_id', $hotel->id)
                ->where('year', $targetYear)
                ->exists();

            if (!$existsPreferential) {
                // Clonar hotel_preferentials
                $preferentials = DB::table('hotel_preferentials')
                    ->where('hotel_id', $hotel->id)
                    ->where('year', $sourceYear)
                    ->get();

                foreach ($preferentials as $pref) {
                    DB::table('hotel_preferentials')->insert([
                        'year' => $targetYear,
                        'value' => $pref->value,
                        'hotel_id' => $pref->hotel_id,
                    ]);
                }

                $this->info("Clonado hotel_preferentials para hotel ID: {$hotel->id}");
            } else {
                $this->warn("hotel_preferentials ya existe para hotel ID: {$hotel->id} en el año {$targetYear}");
            }
        }

        $this->info("Proceso finalizado.");

        return 0;
    }
}
