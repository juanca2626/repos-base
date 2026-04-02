<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTourcmsChannelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tourcms_channels', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('channel_id');
            $table->string('api_key');
            $table->string('name');
            $table->timestamps();
        });

        DB::table('tourcms_channels')->insert([
           'channel_id' => 14513,
            'api_key' => "efa83074a898",
            'name' => "Channel",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now()
        ]);
        DB::table('tourcms_channels')->insert([
            'channel_id' => 14576,
            'api_key' => "8f47f323f9e8",
            'name' => "OTA",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now()
        ]);
        DB::table('tourcms_channels')->insert([
            'channel_id' => 3930,
            'api_key' => "0df0db4dc340",
            'name' => "Test",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now()
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tourcms_channels');
    }
}
