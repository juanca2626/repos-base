<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuotesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $file_quotes = File::get("database/data/quotes.json");
        $quotes = json_decode($file_quotes, true);
        $created_at = date("Y-m-d H:i:s");

        DB::transaction(function () use ($quotes,$created_at) {
            foreach ($quotes as $quote) {
                $user = DB::table('users')->where('code',$quote['user_code'])->first();
                $s_type = ( $quote['service_type_code'] == 'COM' ) ? 'SIM' : 'PC';
                $service_type = DB::table('service_types')->where('code',$s_type)->first();
                DB::table('quotes')->insert([
                    'code' => $quote['code'],
                    'name' => $quote['name'],
                    'date_in' => $quote['date_in'],
                    'cities' => trim($quote['cities']),
                    'nights' => $quote['nights'],
                    'user_id' => $user->id,
                    'service_type_id' => $service_type->id,
                    'status' => 1,
                    'created_at' => $created_at,
                    'updated_at' => $created_at
                ]);
            }
        });
    }
}
