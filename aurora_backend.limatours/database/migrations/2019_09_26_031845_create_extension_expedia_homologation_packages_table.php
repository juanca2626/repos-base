<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExtensionExpediaHomologationPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('extension_expedia_homologation_packages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('external_code');
            $table->string('internal_code');
            $table->string('description');
            $table->string('detail');
            $table->tinyInteger('room_type');
            $table->char('service_type_code',3);
            $table->char('category_code',5);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('extension_expedia_homologation_packages');
    }
}
