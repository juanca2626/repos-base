<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExtensionDespegarHomologationTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $file_extension_homologations = File::get("database/data/extension_despegar_homologations.json");
        $extension_despegar_homologations = json_decode($file_extension_homologations, true);
        $created_at = date("Y-m-d H:i:s");

        DB::transaction(function () use ($extension_despegar_homologations,$created_at) {
            foreach ($extension_despegar_homologations as $e_e_h) {
                DB::table('extension_despegar_homologations')->insert([
                    'service_type' => $e_e_h['service_type'],
                    'internal_code' => $e_e_h['internal_code'],
                    'description' => $e_e_h['description'],
                    'created_at' => $created_at,
                    'updated_at' => $created_at
                ]);
            }
        });
    }
}
