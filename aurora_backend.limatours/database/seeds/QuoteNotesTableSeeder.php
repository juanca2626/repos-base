<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuoteNotesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $quote_id = 1;
        $faker = Faker\Factory::create();

        DB::transaction(function () use ($quote_id,$faker)
        {
            for ($i=0;$i<5;$i++)
            {
                $quote_note_id = DB::table('quote_notes')->insertGetId([
                    'comment' => $faker->paragraph,
                    'status' => $faker->boolean,
                    'quote_id' => $quote_id,
                    'user_id' => 1,
                    "created_at" =>  \Carbon\Carbon::now(),
                    "updated_at" => \Carbon\Carbon::now()
                ]);
                for ($j=0;$j<2;$j++) {
                    DB::table('quote_notes')->insert([
                        'parent_note_id' => $quote_note_id,
                        'comment' => $faker->paragraph,
                        'status' => $faker->boolean,
                        'quote_id' => $quote_id,
                        'user_id' => $faker->randomElement([1,4,5]),
                        "created_at" => \Carbon\Carbon::now(),
                        "updated_at" => \Carbon\Carbon::now()
                    ]);
                }
            }
        });
    }
}
