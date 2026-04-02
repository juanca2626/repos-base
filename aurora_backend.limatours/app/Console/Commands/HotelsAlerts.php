<?php

namespace App\Console\Commands;

use App\Translation;
use App\Hotel;
use App\HotelAlert;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class HotelsAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hotels:alert_years';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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

        DB::transaction(function () {

            $years = [Carbon::now()->year,Carbon::now()->year + 1]; 

            Hotel::where('status', 1)->chunk(100, function ($hotels) use ($years) {  //->where('id', '171')

                foreach ($hotels as $hotel ){

                    $translations = Translation::where('type', '=', 'hotel')->where('object_id', '=', $hotel->id)->get();

               
                    $translation_notes = $translations->filter(function($item) {
                        return $item->slug == 'notes';
                    })->unique('language_id'); 

                    $translation_summary = $translations->filter(function($item) {
                        return $item->slug == 'summary';
                    })->unique('language_id'); 

                    $data_translations = [];
                    foreach($translation_notes as $notes){
                        $data_translations[$notes->language_id]['notes'] = $notes->value;
                    }
                    foreach($translation_summary as $summary){
                        $data_translations[$summary->language_id]['summary'] = $summary->value;
                    }
                    foreach($data_translations as $language_id => $data){
                        foreach($years as $year){
                            HotelAlert::create([
                                'year' =>$year,
                                'remarks' => isset($data['notes']) ? $data['notes'] : '' ,
                                'notes' => isset($data['summary']) ? $data['summary'] : '' ,
                                'language_id' => $language_id,
                                'hotel_id' => $hotel->id
                            ]);
                        }
                    }                                                    
                   
                }

            });

        });

    }
 
}
