<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Http\Traits\Equivalence;

class EquivalenceServicesTableSeeder extends Seeder
{
    use Equivalence;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $client = new \GuzzleHttp\Client(['verify' => false]);
        $response = $client->request('POST',
            config('services.stella.domain') . 'api/v1/equivalences/composition', [
                "json" => [
                    'string_equivalence_numbers_not_in' => ""
                ]
            ]);
        $response = json_decode( $response->getBody()->getContents() );

        $response = $this->insert_equivalences($response->data, 1);

//        print_r($response);

    }
}
