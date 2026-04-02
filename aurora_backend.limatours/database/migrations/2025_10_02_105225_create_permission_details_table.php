<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePermissionDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permission_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('permission_id')->unique(); // 1 a 1
            $table->unsignedBigInteger('permission_module_id');
            $table->timestamps();

            // Tabla 'permissions' del paquete
            $table->foreign('permission_id')
                ->references('id')->on('permissions')
                ->onDelete('cascade');

            $table->foreign('permission_module_id')
                ->references('id')->on('permission_modules')
                ->onDelete('cascade');

            $table->index(['permission_module_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('permission_details');
    }
}
