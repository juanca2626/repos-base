<?php

namespace App\Console\Commands;

use App\Hotel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportPreferentialAndTypeClass extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hotels:import_preferential_typeclass';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'transfer data of hotel to hotel preferentials and hotel type class';

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
        $years = ['2024','2025'];
        try {
            $hotels = Hotel::where('status', 1)->get();
           
            DB::transaction(function () use ($hotels, $years) {
                $created_at = date("Y-m-d H:i:s");
                foreach ($hotels as $hotel) {
                    
                    foreach($years as $year){

                        DB::table('hotel_type_classes')->insert([
                            'year' => $year,
                            'typeclass_id' => $hotel->typeclass_id ? $hotel->typeclass_id : '',
                            'hotel_id' => $hotel->id,                       
                            'created_at' => $created_at,
                            'updated_at' => $created_at
                        ]);

                        //Karia Yances, solicito que solo se pase a esta tabla del año 2024 y del 2025 todo se inicialice en 0 para que ellos lo ingresen solo a los hoteles que son preferentes.
                        $preferential = $hotel->preferential ? $hotel->preferential : '';
                        if($year == '2025'){
                            $preferential = 0; 
                        }
                        DB::table('hotel_preferentials')->insert([
                            'year' => $year,
                            'value' => $preferential,
                            'hotel_id' => $hotel->id,                       
                            'created_at' => $created_at,
                            'updated_at' => $created_at
                        ]);
                        
                    }
                        
                }
            
                $this->info('Se agregaron correctamente');
            });
                             
                
        }catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }

    }
}
