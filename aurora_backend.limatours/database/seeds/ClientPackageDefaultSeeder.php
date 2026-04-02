<?php

use App\BusinessRegion;
use App\BusinessRegionClient;
use App\Client;
use App\ClientConfiguration;
use App\Markup;
use Illuminate\Database\Seeder;

class ClientPackageDefaultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $code = 'CLDEPA';
        $name = 'CLIENT PACKAGE';
        $ifExists = Client::where('code', $code)->exists();

        if (!$ifExists) {
            // CLIENT
            $client = Client::create([
                'code'                              => $code,
                'name'                              => $name,
                'business_name'                     => $name,
                'address'                           => 'LIMA TOURS',
                'web'                               => NULL,
                'anniversary'                       => NULL,
                'postal_code'                       => NULL,
                'ruc'                               => NULL,
                'email'                             => NULL,
                'phone'                             => NULL,
                'use_email'                         => "NO",
                'have_credit'                       => '0',
                'credit_line'                       => NULL,
                'status'                            => '1',
                'market_id'                         => 9, // LATINOAMERICA 1
                'classification_code'               => NULL,
                'classification_name'               => NULL,
                'executive_code'                    => NULL,
                'bdm_id'                            => NULL,
                'general_markup'                    => 0,
                'ecommerce'                         => 0,
                'allow_direct_passenger_creation'   => 0,
                'created_at'                        => NOW(),
                'country_id'                        => 89, // PERU
                'city_code'                         => 'LIM',
                'city_name'                         => 'LIMA',
                'language_id'                       => 2,
                'bdm'                               => NULL
            ]);
            // CLIENT CONFIGURATION
            $ClientConfiguration = new ClientConfiguration();
            $ClientConfiguration->client_id = $client->id;
            $ClientConfiguration->hotel_allowed_on_request = 1;
            $ClientConfiguration->service_allowed_on_request = 1;
            $ClientConfiguration->save();
            // TRAER TODAS LAS REGIONES
            $businessRegions = BusinessRegion::all();

            foreach($businessRegions as $region){
                //MARKUPS
                Markup::create([
                    'period'                => now(),
                    'hotel'                 => 0.00,
                    'service'               => 0.00,
                    'status'                => 1,
                    'client_id'             => $client->id,
                    'clone'                 => 0,
                    'business_region_id'    => $region->id,
                    'created_at'            => now()
                ]);

                BusinessRegionClient::create([
                    'business_region_id' => $region->id,
                    'client_id'          => $client->id
                ]);
            }

            $this->command->info("CREACIÓN DE CLIENTE DE DEFAULT PARA PAQUETES");
        }

    }
}
