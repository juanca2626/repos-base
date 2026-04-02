<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('file_itinerary_flights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_itinerary_id')->constrained('file_itineraries');
            $table->string('airline_name')->nullable();
            $table->char('airline_code', 2)->nullable()->comment('airline');
            $table->char('airline_number', 10)->nullable()->comment('number_flight');
            $table->char('pnr', 20)->nullable()->comment('pnr');
            $table->time('departure_time')->nullable()->comment('hora de salida');
            $table->time('arrival_time')->nullable()->comment('hora de llegada');
            $table->smallInteger('nro_pax')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $comment = "let sql = 'SELECT nroref, nroite, tipsvs, codsvs, ciuin, fecin, TO_CHAR(fecin, \"%d/%m/%Y\") as date' +
            ', horin, ciuout, fecout, horout, canpax,' +
            ' (select ciavue from t21 where nroemp = t13.nroemp and ideref = t13.ideref and nroref = t13.nroref and nroite = t13.nroite) as airline,' +
            ' (select nrovue from t21 where nroemp = t13.nroemp and ideref = t13.ideref and nroref = t13.nroref and nroite = t13.nroite) as number_flight,' +
            ' (select codrsv from t21 where nroemp = t13.nroemp and ideref = t13.ideref and nroref = t13.nroref and nroite = t13.nroite) as pnr' +
            ' FROM t13 WHERE nroemp = ? AND ideref = ? AND estado != ? and codsvs in (\"AEI\", \"AEC\", \"AEIFLT\", \"AECFLT\") AND nroref = ?';";

            $table->comment($comment);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_itinerary_flights');
    }
};
