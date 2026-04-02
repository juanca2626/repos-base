<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablesAppMenuSubmenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('apps', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('icon')->nullable();
        });

        Schema::create('menus', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('icon', 20);
            $table->string('slug')->nullable();
            $table->string('path')->nullable();
            $table->unsignedInteger('app_id');
        });

        Schema::create('sub_menus', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('icon', 20);
            $table->string('slug')->nullable();
            $table->string('path')->nullable();
            $table->unsignedInteger('menu_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('apps');
        Schema::dropIfExists('menus');
        Schema::dropIfExists('sub_menus');

    }
}
