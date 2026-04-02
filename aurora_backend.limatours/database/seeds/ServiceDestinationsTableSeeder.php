<?php

use App\City;
use App\Country;
use App\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceDestinationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $file_service = File::get("database/data/services_new.json");
        $services = json_decode($file_service, true);
        $created_at = date("Y-m-d H:i:s");
        DB::transaction(function () use ($services, $created_at) {
            foreach ($services as $service) {
                $findService = Service::where('aurora_code', $service['aurora_code'])
                    ->where('equivalence_aurora', $service['equivalence_aurora'])->get()->toArray();
                if (count($findService) > 0) {
                    $findCountry = Country::with('states')->where('iso', $service['country_destiny'])->get()->toArray();
                    $country_id = $findCountry[0]['id'];
                    if ($service['city_destiny'] === 'PEM') {
                        $stateIso = 'MDD';
                    } elseif ($service['city_destiny'] == 'CHJ') {
                        $stateIso = 'AMA';
                    } elseif ($service['city_destiny'] == 'CIX') {
                        $stateIso = 'LAM';
                    } elseif ($service['city_destiny'] == 'COL') {
                        $stateIso = 'APQ';
                    } elseif ($service['city_destiny'] == 'HAZ') {
                        $stateIso = 'ANC';
                    } elseif ($service['city_destiny'] == 'HAZ') {
                        $stateIso = 'ANC';
                    } elseif ($service['city_destiny'] == 'IQT') {
                        $stateIso = 'LOR';
                    } elseif ($service['city_destiny'] == 'JUL') {
                        $stateIso = 'PUN';
                    } elseif ($service['city_destiny'] == 'MPI' or $service['city_destiny'] == 'URU') {
                        $stateIso = 'CUS';
                    } elseif ($service['city_destiny'] == 'TYL') {
                        $stateIso = 'PIU';
                    } elseif ($service['city_destiny'] == 'TPP') {
                        $stateIso = 'SAM';
                    } elseif ($service['city_destiny'] == 'TRU') {
                        $stateIso = 'LAL';
                    } elseif ($service['city_destiny'] == 'TBP') {
                        $stateIso = 'TUM';
                    } elseif ($service['city_destiny'] == 'NAZ' or $service['city_destiny'] == 'PCS') {
                        $stateIso = 'ICA';
                    } else {
                        $stateIso = $service['city_destiny'];
                    }
                    foreach ($findCountry[0]['states'] as $state) {
                        if ($stateIso == $state['iso']) {
                            $state_id = $state['id'];
                            break;
                        }
                    }
                    $findCities = City::with([
                        'translations' => function ($query) use ($service) {
                            $query->where('language_id', 1);
                            $query->where('type', 'city');
                            $query->where('slug', 'city_name');
                            $query->where('value', ucwords(strtolower($service['city_destiny_name'])));
                        }
                    ])->where('state_id', $state_id)->whereHas('translations')->get()->toArray();
                    foreach ($findCities as $city) {
                        if (count($city['translations']) > 0) {
                            $city_id = $city['id'];
                            break;
                        }
                    }
                    DB::table('service_destinations')->insert([
                        'service_id' => $findService[0]['id'],
                        'country_id' => $country_id,
                        'state_id' => $state_id,
                        'city_id' => $city_id,
                        'created_at' => $created_at,
                        'updated_at' => $created_at,
                    ]);
                }
            }
        });
    }
}
