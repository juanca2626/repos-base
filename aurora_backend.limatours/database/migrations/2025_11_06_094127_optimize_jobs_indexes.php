<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class OptimizeJobsIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('jobs', function (Blueprint $t) {
            // Mejora el pick de jobs por cola y disponibilidad
            $t->index(['queue', 'reserved_at', 'available_at'], 'jobs_queue_reserved_available');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('jobs', function (Blueprint $t) {
            $t->dropIndex('jobs_queue_reserved_available');
        });
    }
}
