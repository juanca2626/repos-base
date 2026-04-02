<?php

namespace App\Console\Commands;

use App\ConfigMarkup;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Hotel;
use Illuminate\Support\Facades\DB;

class CloneHotelRatesSpecified extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hotels:clone_rates_specified';

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

            $year = Carbon::now()->year;
            $year_to = Carbon::now()->year + 1;

            $hotel_codes = [
                'LIMHMR', 'LIMHOU', 'LIMHWE', 'LIMHPP',
                'CUSHMS', 'CUSHPN', 'URUHLI', 'URUHHR',
                'MPIHSL', 'PUNHTH', 'COLHCB', 'URUHSL',
                'AQPHCQ'
            ];

            Hotel::with(['rates_plans'])
                ->whereHas('channels', function ($q) use ($hotel_codes) {
                    $q->whereIn('channel_hotel.code', $hotel_codes);
                })
//                ->where('id', 4)
                ->chunk( 5, function($hotels) use ($year, $year_to){
                    foreach ( $hotels as $hotel ){

                        $markup = 1.05;

                        foreach ($hotel->rates_plans as $rate_plan) {
                            if( !($rate_plan->promotions) ){ // SI NO ES PROMOCIONA

                                $date_range_hotels = DB::table('date_range_hotels')
                                    ->where('rate_plan_id', $rate_plan->id)
                                    ->where('date_from', 'LIKE', "%" . $year_to . "%")
                                    ->where('created_at', 'LIKE', "%2022-02-21%")
                                    ->orderBy('created_at', 'desc')->get();

                                foreach ($date_range_hotels as $date_range_hotel)
                                {
                                    DB::table('date_range_hotels')
                                        ->where('id', '=', $date_range_hotel->id)
                                        ->update([
                                        'price_adult' => $date_range_hotel->price_adult * $markup,
                                        'price_child' => $date_range_hotel->price_child * $markup,
                                        'price_infant' => $date_range_hotel->price_infant * $markup,
                                        'price_extra' => $date_range_hotel->price_extra * $markup,
                                        'flag_migrate' => 1
                                    ]);
                                }
                            }
                        }

                    }
                } );

        });
    }
}
