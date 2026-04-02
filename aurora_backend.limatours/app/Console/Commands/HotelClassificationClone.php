<?php

namespace App\Console\Commands;

use App\HotelPreferentials;
use App\HotelTypeClass;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class HotelClassificationClone extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hotels:hotels_classification_clone';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Esto se ejecuta 1 vez al año y Clonamos las clases y preferencias de un año a otro, las preferencias no clonamos solo crearmos los registros con valor 0 
                             para que negociaciones lo ingrese. al clonar validamos que el año destino no existe para el hotel procesado, si existe no lo clonamos. ';

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
                
        try {
            $year_origin='2024';
            $year_clone='2025';
            
            $preferentials = HotelPreferentials::where('year',$year_origin)->get()->groupBy('hotel_id')->toArray();
            $preferentials_clone = HotelPreferentials::where('year',$year_clone)->get()->groupBy('hotel_id')->toArray();
            $types_year_origin = HotelTypeClass::where('year',$year_origin)->get()->groupBy('hotel_id')->toArray();
            $types_year_origin_clone = HotelTypeClass::where('year',$year_clone)->get()->groupBy('hotel_id')->toArray();

            $created_at = date("Y-m-d H:i:s");          
            
            foreach ($types_year_origin as $hotel_id => $origin) {
               
                if(!isset($types_year_origin_clone[$hotel_id])){                     
                    foreach ($origin as $type) {                          
                        
                        $hotel = new HotelTypeClass();
                        $hotel->year =  $year_clone;
                        $hotel->typeclass_id = $type['typeclass_id'] ? $type['typeclass_id'] : '';
                        $hotel->hotel_id = $type["hotel_id"] ? $type["hotel_id"] : '';                       
                        $hotel->created_at = $created_at;
                        $hotel->updated_at = $created_at;
                        $hotel->save();                        
                    }
                }                 
            }

            foreach ($preferentials as $hotel_id => $preferent) {                
                                  
                if(!isset($preferentials_clone[$hotel_id])){
                    foreach ($preferent as $hotel) {                            
                        $preferents_hotel = new HotelPreferentials();                            
                        $preferents_hotel->year = $year_clone;
                        $preferents_hotel->value = 0;
                        $preferents_hotel->hotel_id = $hotel['hotel_id'] ? $hotel['hotel_id'] : '';                       
                        $preferents_hotel->created_at = $created_at;
                        $preferents_hotel->updated_at = $created_at;
                        $preferents_hotel->save();
                    }
                }                   
            }
                              
        }catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }

    }
}
