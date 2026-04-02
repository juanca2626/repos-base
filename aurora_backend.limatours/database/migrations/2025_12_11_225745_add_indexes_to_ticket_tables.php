<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexesToTicketTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ticket_tables', function (Blueprint $table) {
            // 1) Índice compuesto: route_id + date
            Schema::table('ticket_available_routes', function (Blueprint $table) {
                $table->index(['ticket_route_id', 'date'], 'idx_tar_route_date');
            });

            // 2) Índice compuesto: available_route_id + time
            Schema::table('ticket_available_route_times', function (Blueprint $table) {
                $table->index(['ticket_available_route_id', 'time'], 'idx_tart_route_time');
            });

            // 3) Llaves únicas → evitar duplicados (upserts)
            Schema::table('ticket_available_routes', function (Blueprint $table) {
                $table->unique(['ticket_route_id', 'date'], 'uq_tar_route_date');
            });

            Schema::table('ticket_available_route_times', function (Blueprint $table) {
                $table->unique(['ticket_available_route_id', 'time'], 'uq_tart_route_time');
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ticket_tables', function (Blueprint $table) {
            // Eliminar índices y llaves únicas
            Schema::table('ticket_available_routes', function (Blueprint $table) {
                $table->dropIndex('idx_tar_route_date');
                $table->dropUnique('uq_tar_route_date');
            });

            Schema::table('ticket_available_route_times', function (Blueprint $table) {
                $table->dropIndex('idx_tart_route_time');
                $table->dropUnique('uq_tart_route_time');
            });
        });
    }
}
