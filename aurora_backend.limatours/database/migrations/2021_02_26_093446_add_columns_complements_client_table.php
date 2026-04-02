<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsComplementsClientTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clients', function(Blueprint $table){
            $table->string('phone')->after('email')->nullable();
            $table->string('postal_code')->after('address')->nullable();
            $table->string('web')->after('address')->nullable();
            $table->string('city_name')->after('country_id')->nullable();
            $table->string('city_code')->after('country_id')->nullable();
            $table->integer('general_markup')->after('market_id')->nullable();
            $table->string('executive_code')->after('market_id')->nullable();
            $table->string('classification_name')->after('market_id')->nullable();
            $table->string('classification_code')->after('market_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clients', function(Blueprint $table){
            $table->removeColumn(['phone','postal_code','web','city_name','city_code','general_markup','executive_code',
                'classification_name','classification_code']);
        });
    }
}
