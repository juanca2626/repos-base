<?php

namespace App\Console\Commands;

use App\Client;
use App\Markup;
use App\Service;
use App\ServiceClient;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddClientMarkups extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'markups:add';

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

            $year1 = Carbon::now()->year + 1;
            $year2 = Carbon::now()->year + 2;

            Client::where('status', 1)
                ->with(['markups'=>function($query){
                    $query->withTrashed();
                    $query->orderBy("period", "desc");
                }])
                ->chunk(20, function ($clients) use ($year1, $year2) {
                    foreach ( $clients as $client ){
                        $year_[$year1] = false;
                        $year_[$year2] = false;
                        foreach ($client['markups'] as $markup) {
                            if ( isset( $year_[$markup['period']] ) ) {
                                $year_[$markup['period']] = true;
                                if( $markup['status'] == 0 || $markup['deleted_at'] !== null ){
                                    $update_markup = Markup::where('id',$markup['id'])->withTrashed()->first();
                                    $update_markup->hotel = $client['markups'][0]['hotel'];
                                    $update_markup->service = $client['markups'][0]['service'];
                                    $update_markup->status = 1;
                                    $update_markup->deleted_at = null;
                                    $update_markup->save();
                                }
                            }
                        }
                        if( $year_[$year1] === false && count($client['markups']) > 0 ){
                            $new_markup = new Markup();
                            $new_markup->period = $year1;
                            $new_markup->hotel = $client['markups'][0]['hotel'];
                            $new_markup->service = $client['markups'][0]['service'];
                            $new_markup->status = 1;
                            $new_markup->client_id = $client["id"];
                            $new_markup->save();
                        }

                        if( $year_[$year2] === false && count($client['markups']) > 0 ){
                            $new_markup = new Markup();
                            $new_markup->period = $year2;
                            $new_markup->hotel = $client['markups'][0]['hotel'];
                            $new_markup->service = $client['markups'][0]['service'];
                            $new_markup->status = 1;
                            $new_markup->client_id = $client["id"];
                            $new_markup->save();
                        }

                        $this->block_services($year1, $client["id"]);
                        $this->block_services($year2, $client["id"]);

                    }
                });

        });

    }

    public function block_services($year, $client_id){
        // Bloqueo de servicios
        $services_blocks = Service::select('id')
            ->where('status', 1)
            ->where('exclusive', 1)
            ->where('exclusive_client_id', '!=', null)
            ->where('exclusive_client_id', '!=', "")
            ->where('exclusive_client_id', '!=', $client_id)
            ->get();

        foreach ( $services_blocks as $service ){
            $find_service_client = ServiceClient::where('period', $year)
                ->where('service_id', $service->id)
                ->where('client_id', $client_id)
                ->count();
            if( $find_service_client === 0 ){
                $new_service_client = new ServiceClient();
                $new_service_client->period = $year;
                $new_service_client->client_id = $client_id;
                $new_service_client->service_id = $service->id;
                $new_service_client->save();
            }
        }
    }

}
