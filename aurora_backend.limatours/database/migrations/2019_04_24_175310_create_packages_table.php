<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// @codingStandardsIgnoreLine
class CreatePackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('code', 7)->comment('CODPAQ (t11p)');
            $table->tinyInteger('nights')->nullable()->comment('DURACI (t11p)');
            $table->string('map_link')->nullable()->comment('NUMGPS (t11p)');
            $table->string('image_link')->nullable()->comment('FOTURL (t11p)');
            $table->tinyInteger('status')->nullable()->comment('OK = 1 | XL  = 0');
            $table->string('reference', 120)->nullable()->comment('CODCLI (t11p)');
            $table->char('rate_type', 1)->default('D')->comment('F = Fijo | D = Dinámico');
            $table->char('use_igv', 1)->nullable()->comment('1 = YES | 0 = NO');
            $table->double('igv')->nullable();
            $table->char('allow_guide', 1)->nullable();
            $table->char('allow_child', 1)->nullable();
            $table->char('allow_infant', 1)->nullable();
            $table->tinyInteger('limit_confirmation_hours')->nullable();
            $table->integer('infant_min_age')->nullable();
            $table->integer('infant_max_age')->nullable();
            $table->unique(["code"], 'code_UNIQUE');
            $table->unsignedBigInteger('physical_intensity_id')->default('1');
            $table->unsignedBigInteger('tag_id')->default('1');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('physical_intensity_id')->references('id')->on('physical_intensities');
            $table->foreign('tag_id')->references('id')->on('tags');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('packages');
    }
}
