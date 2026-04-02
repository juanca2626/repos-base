<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCloneLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clone_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('type', ['service', 'hotel']);
            $table->bigInteger('item_id');
            $table->bigInteger('item_rate_plan_id')->nullable();
            $table->bigInteger('item_rate_id')->nullable();
            $table->date('date');
            $table->decimal('markup');
            $table->smallInteger('status'); // 0 => antiguo, 1 => nuevo
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
        Schema::dropIfExists('clone_logs');
    }
}
