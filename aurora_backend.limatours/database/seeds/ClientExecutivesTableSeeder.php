<?php

use App\ClientExecutive;
use App\User;
use App\Client;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

// @codingStandardsIgnoreLine
class ClientExecutivesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
//        if (App::environment('local') === true) {
//            factory(ClientExecutive::class)->times(10)->create();
//        }

        $created_at = date("Y-m-d H:i:s");
        $users = User::where('relations','!=','')->where('relations','!=',null)->get();
        DB::transaction(function () use ($users, $created_at) {
            foreach ($users as $user) {

                $user_id = $user->id;
                $relations = explode(',', $user->relations);

                foreach ($relations as $relation) {
                    $client = Client::where('code',$relation);
                    if( $client->count() ){
                        $client = $client->first();
                        DB::table('client_executives')->insert([
                            'status' => 1,
                            'client_id' => $client->id,
                            'user_id' => $user_id,
                            'created_at' => $created_at,
                            'updated_at' => $created_at
                        ]);
                    }
                }
            }
        });
    }
}
