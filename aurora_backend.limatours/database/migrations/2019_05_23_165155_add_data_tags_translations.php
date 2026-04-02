<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Role;

class AddDataTagsTranslations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();
        App\Tag::truncate();
        App\Translation::where('type', 'tag')->forceDelete();
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
            '--class' => TagsTableSeeder::class,
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
          App\Tag::truncate();
          App\Translation::where('type', 'tag')->forceDelete();
          Schema::enableForeignKeyConstraints();
    }
}
