<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogClientRatePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_client_rate_plans', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Tipo de operación
            $table->enum('type', ['hotel', 'service'])->comment('Tipo de tarifa: hotel o servicio');
            $table->enum('action', ['inserted', 'deleted'])->comment('Acción: inserted (agregado) o deleted (removido)');

            // Identificadores
            $table->unsignedInteger('rate_plan_id')->nullable()->comment('ID de la tarifa (hotel o servicio)');
            $table->unsignedInteger('client_id')->comment('ID del cliente afectado');
            $table->string('period', 4)->comment('Año de la restricción (ej: 2026)');

            // Contexto de ejecución
            $table->enum('source', ['command', 'observer', 'manual'])->comment('Origen: command, observer o manual');

            // Información adicional
            $table->string('reason')->nullable()->comment('Razón de la acción');

            // Metadatos
            $table->timestamp('executed_at')->useCurrent()->comment('Fecha y hora de ejecución');

            // Índices
            $table->index(['type', 'action'], 'idx_type_action');
            $table->index(['client_id', 'period'], 'idx_client_period');
            $table->index('rate_plan_id', 'idx_rate_plan');
            $table->index('executed_at', 'idx_executed_at');
            $table->index('source', 'idx_source');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('log_client_rate_plans');
    }
}
