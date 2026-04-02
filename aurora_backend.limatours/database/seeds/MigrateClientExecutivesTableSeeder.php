<?php

use Illuminate\Database\Seeder;

class MigrateClientExecutivesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $client = new \GuzzleHttp\Client();
        $url = config('services.aurora_extranet.domain').'/api/orders/api.php?method=usersTOM&nomesp=';
        $request = $client->get($url);
        $users = json_decode($request->getBody()->getContents(), true);

        $n=0;

        foreach ( $users as $user ){
            $nomesp_code = TRIM($user['NOMESP']);

            $user = \App\User::where('code', $nomesp_code)->where('status', 1)->first();

            if( $user ){
                $params = "executive=".$nomesp_code."&identi=J";
                $url = config('services.aurora_extranet.domain').'api/orders/api.php?method=customersTOM&' . $params;
                $request = $client->get($url);
                $customers = json_decode($request->getBody()->getContents(), true);

                foreach ( $customers as $customer ){
                    $noming_code = TRIM($customer['NOMING']);
                    $customer_ = \App\Client::where('code', $noming_code)->where('status', 1)->first();

                    if( $customer_ ){

                        $find_client_executive = \App\ClientExecutive::where("status", 1)
                            ->where("client_id", $customer_->id)
                            ->where("user_id", $user->id)
                            ->count();

                        if( $find_client_executive === 0 ){
                           $client_executive = new \App\ClientExecutive();
                           $client_executive->status = 1;
                           $client_executive->client_id = $customer_->id;
                           $client_executive->user_id = $user->id;
                           $client_executive->save();
                        }
                    } else {
                        $today = \Carbon\Carbon::now();
                        \Illuminate\Support\Facades\DB::table('activity_log')->insert([
                            "log_name" => "fail_migration_client",
                            "description" => "El proceso de migración de usuarios tom a client_executives no encontró el código de cliente",
                            "properties" => '{"code":"'.$noming_code.'"}',
                            "created_at" => $today,
                            "updated_at" => $today,
                        ]);
                    }
                }

            } else {
                $today = \Carbon\Carbon::now();
                \Illuminate\Support\Facades\DB::table('activity_log')->insert([
                    "log_name" => "fail_migration_executive",
                    "description" => "El proceso de migración de usuarios tom a client_executives no encontró el código de ejecutivo",
                    "properties" => '{"code":"'.$nomesp_code.'"}',
                    "created_at" => $today,
                    "updated_at" => $today,
                ]);
            }

//            if($n==1){
//                break;
//            }
//            $n++;
        }

    }
}
