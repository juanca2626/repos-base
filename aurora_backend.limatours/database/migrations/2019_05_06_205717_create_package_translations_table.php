<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePackageTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('package_translations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 250)->nullable()->comment('NOMPAQ (t11p)');
            $table->string('tradename', 250)->nullable();
            $table->text('description')->nullable()->comment('TEXPAQ (t11p)');
            $table->string('label', 45)->nullable()->comment('ETIQUE (t11p)');
            $table->string('itinerary_link', 250)->nullable()->comment('TEXTSK (t19g)');
            $table->string('itinerary_label', 80)->nullable()->comment('COORDE (t19g)');
            $table->text('itinerary_description')->nullable()->comment('TEXTDE (t19g)');
            $table->text('inclusion')->nullable();
            $table->text('restriction')->nullable();
            $table->text('policies')->nullable();
            $table->unsignedBigInteger('language_id');
            $table->foreign('language_id')->references('id')->on('languages');
            $table->unsignedBigInteger('package_id');
            $table->foreign('package_id')->references('id')->on('packages');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('package_translations');
    }
}
