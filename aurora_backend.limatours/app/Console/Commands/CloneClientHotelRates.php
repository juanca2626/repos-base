<?php

namespace App\Console\Commands;

use App\Client;
use App\ClientRatePlan;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CloneClientHotelRates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clients:clone_hotel_plan_rates';

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

            Client::
//                where('id', 1)->
                chunk( 5, function($clients) use ($year, $year_to){
                    foreach ( $clients as $client ){

                        $client_rate_plans = ClientRatePlan::where('client_id', $client->id)
                            ->whereIn('period', [$year, $year_to])
                            ->with(['rate_plan'])
                            ->get();

                        $hotels_object = [];
                        $hotels_array = [];

                        foreach ( $client_rate_plans as $rate_plan ){
                            if( $rate_plan->rate_plan ){
                                if( !isset( $hotels_object[ $rate_plan->rate_plan->hotel_id ] ) ){
                                    $hotels_object[ $rate_plan->rate_plan->hotel_id ] = count($hotels_array);
                                    if( $rate_plan->period == $year ){
                                        $years_from = [
                                            array(
                                                "id" => $rate_plan->id,
                                                "period" => $rate_plan->period,
                                                "client_id" => $rate_plan->client_id,
                                                "rate_plan_id" => $rate_plan->rate_plan_id
                                            )
                                        ];
                                        $years_to = [];
                                    }
                                    if( $rate_plan->period == $year_to ){
                                        $years_from = [];
                                        $years_to = [
                                            array(
                                                "id" => $rate_plan->id,
                                                "period" => $rate_plan->period,
                                                "client_id" => $rate_plan->client_id,
                                                "rate_plan_id" => $rate_plan->rate_plan_id
                                            )
                                        ];
                                    }
                                    array_push( $hotels_array, [
                                        'hotel_id' => $rate_plan->rate_plan->hotel_id,
                                        'years_from' => $years_from,
                                        'years_to' => $years_to,
                                    ] );
                                } else {
                                    if( $rate_plan->period == $year ){
                                        array_push( $hotels_array[$hotels_object[ $rate_plan->rate_plan->hotel_id ]]['years_from'],
                                            array(
                                                "id" => $rate_plan->id,
                                                "period" => $rate_plan->period,
                                                "client_id" => $rate_plan->client_id,
                                                "rate_plan_id" => $rate_plan->rate_plan_id
                                            ) );
                                    }
                                    if( $rate_plan->period == $year_to ){
                                        array_push( $hotels_array[$hotels_object[ $rate_plan->rate_plan->hotel_id ]]['years_to'],
                                            array(
                                                "id" => $rate_plan->id,
                                                "period" => $rate_plan->period,
                                                "client_id" => $rate_plan->client_id,
                                                "rate_plan_id" => $rate_plan->rate_plan_id
                                            ) );
                                    }
                                }
                            }
                        }

                        foreach ( $hotels_array as $hotel ){
                            if( count($hotel['years_from']) > 0 && count($hotel['years_to']) === 0 ){
                                foreach ( $hotel['years_from'] as $rate_plan_from ){
                                    DB::table('client_rate_plans')->insert([
                                        'period' => $year_to,
                                        'client_id' => $client->id,
                                        'rate_plan_id' => $rate_plan_from['rate_plan_id'],
                                        'created_at' => Carbon::now(),
                                        'updated_at' => Carbon::now()
                                    ]);
                                }
                            }
                        }

                    }
                } );

        });
    }
}
