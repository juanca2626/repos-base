<?php

use App\Client;
use App\Markup;
use App\Service;
use App\ServiceClient;
use App\ServiceClientRatePlan;
use App\ServiceRate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceClientsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $file_service_permissions = File::get("database/data/service_permissions.json");
        $permissions = json_decode($file_service_permissions, true);
        $created_at = date("Y-m-d H:i:s");

        DB::transaction(function () use ($permissions, $created_at) {
            $serviceIds = [];
            $clientIds = [];
            foreach ($permissions as $permission) {
                if (count($permission["permisions"]) > 0) {
                    $service_id = '';
                    if (count($serviceIds) > 0) {
                        foreach ($serviceIds as $service) {
                            if (($service['aurora_code'] == $permission['aurora_code']) and
                                ($service['equivalence_aurora'] == $permission['equivalence_aurora'])) {
                                $service_id = $service['service_id'];
                            }
                        }
                    }

                    if ($service_id == '') {
                        $findService = Service::where('aurora_code', $permission['aurora_code'])
                            ->where('equivalence_aurora', $permission['equivalence_aurora'])->get()->toArray();
                        if ($findService) {
                            $service_id = $findService[0]['id'];
                            $serviceIds[] = [
                                'service_id' => $service_id,
                                'aurora_code' => $permission['aurora_code'],
                                'equivalence_aurora' => $permission['equivalence_aurora']
                            ];
                        }
                    }

                    if ($service_id !== '') {
                        foreach ($permission["permisions"] as $permision) {
                            $client_id = '';
                            if (count($clientIds) > 0) {
                                foreach ($clientIds as $client) {
                                    if (($client['cliente'] == $permision['cliente'])) {
                                        $client_id = $client['cliente'];
                                    }
                                }
                            }

                            if ($client_id == '') {
                                $findClient = Client::where('code', $permision['cliente'])->get()->toArray();
                                if ($findClient) {
                                    $client_id = $findClient[0]['id'];
                                    $clientIds[] = [
                                        'cliente' => $client_id,
                                    ];
                                }
                            }
                            if ($client_id !== '') {
                                $markup = Markup::where('client_id', $client_id)
                                    ->where('period', $permision["anio"])->get()->toArray();
                                if ($markup) {
                                    $newServiceCliente = new ServiceClient();
                                    $newServiceCliente->period = $permision["anio"];
                                    $newServiceCliente->markup = $markup[0]["service"];
                                    $newServiceCliente->client_id = $client_id;
                                    $newServiceCliente->service_id = $service_id;
                                    $newServiceCliente->created_at = $created_at;
                                    $newServiceCliente->updated_at = $created_at;
                                    $newServiceCliente->save();

                                    $service_rate = ServiceRate::where('service_id', $service_id)
                                        ->where('name', 'like', '%' . $permision["anio"] . '%')
                                        ->where('status', 1)->get()->toArray();
                                    if ($service_rate) {
                                        $newServiceClienteRatePlan = new ServiceClientRatePlan();
                                        $newServiceClienteRatePlan->period = $permision["anio"];
                                        $newServiceClienteRatePlan->markup = $markup[0]["service"];
                                        $newServiceClienteRatePlan->client_id = $client_id;
                                        $newServiceClienteRatePlan->service_rate_id = $service_rate[0]["id"];
                                        $newServiceClienteRatePlan->created_at = $created_at;
                                        $newServiceClienteRatePlan->updated_at = $created_at;
                                        $newServiceClienteRatePlan->save();
                                    }
                                }
                            }

                        }
                    }
                }

            }
        });
    }
}
