<?php

use Illuminate\Database\Seeder;
use App\Http\Traits\MasterService;

class MasterServicesTableSeeder extends Seeder
{
    use MasterService;
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        set_time_limit ( 0 );

        $client = new \GuzzleHttp\Client(['verify' => false]);
        $response = $client->request('POST',
            config('services.stella.domain'). 'api/v1/services/services/master', [
                "json" => [
                    'use_limit' => 0,
                    'string_services_codes_not_in' => "",
                    'texts' => 1
                ]
            ]);
        $response = json_decode( $response->getBody()->getContents() );

        $services = [];
        foreach ( $response->data as $data ){
            array_push( $services, (array)$data );
        }
        $this->insert_master_services($services);
    }
}
