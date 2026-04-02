<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PayModesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $_id = DB::table('pay_modes')->insertGetId(
            [
                'name' => 'Prorrateo'
            ]
        );

        DB::table('translations')->insert(
            ['type' => 'paymode', 'object_id' => $_id,'slug'=>'name','value'=>'Prorrateo','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'paymode', 'object_id' => $_id,'slug'=>'name','value'=>'Apportionment','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'paymode', 'object_id' => $_id,'slug'=>'name','value'=>'Apportionment','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'paymode', 'object_id' => $_id,'slug'=>'name','value'=>'Apportionment','language_id'=>4]
        );

        $_id = DB::table('pay_modes')->insertGetId([
            'name' => 'Tarifa Especial'
        ]);

        DB::table('translations')->insert(
            ['type' => 'paymode', 'object_id' => $_id,'slug'=>'name','value'=>'Tarifa Especial','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'paymode', 'object_id' => $_id,'slug'=>'name','value'=>'Special rate','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'paymode', 'object_id' => $_id,'slug'=>'name','value'=>'Special rate','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'paymode', 'object_id' => $_id,'slug'=>'name','value'=>'Special rate','language_id'=>4]
        );

    }
}
