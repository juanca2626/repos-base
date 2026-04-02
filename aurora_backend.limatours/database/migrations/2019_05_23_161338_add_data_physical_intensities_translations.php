<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDataPhysicalIntensitiesTranslations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Schema::disableForeignKeyConstraints();
        App\PhysicalIntensity::truncate();
        App\Translation::where('type', 'physicalintensity')->forceDelete();
        //DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        Schema::enableForeignKeyConstraints();

        $cant=DB::table('languages')->count();
        if($cant==0){
          DB::table('languages')->insert([
              'name' => 'Español',
              'iso' => 'es',
              'created_at' => date('Y-m-d H:m:s')
          ]);
          DB::table('languages')->insert([
              'name' => 'Ingles',
              'iso' => 'en',
              'created_at' => date('Y-m-d H:m:s')
          ]);
          DB::table('languages')->insert([
              'name' => 'Portuguese',
              'iso' => 'pt',
              'created_at' => date('Y-m-d H:m:s')

          ]);
          DB::table('languages')->insert([
              'name' => 'Italian',
              'iso' => 'it',
              'created_at' => date('Y-m-d H:m:s')
          ]);
        }

        Artisan::call('db:seed', [
            '--class' => PhysicalIntensitiesTableSeeder::class,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
          Schema::disableForeignKeyConstraints();
          App\PhysicalIntensity::truncate();
          App\Translation::where('type', 'physicalintensity')->forceDelete();
          Schema::enableForeignKeyConstraints();
    }
}
