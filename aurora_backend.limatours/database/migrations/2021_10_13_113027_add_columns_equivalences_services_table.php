<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsEquivalencesServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('type_ifx', '6')->after('deleted_at')->nullable()->default('SVS')->comment('grupo	[type] tipo HTL / LOD / SVS   para equiv, se filtra != HTL');
            $table->string('description_ifx')->after('deleted_at')->nullable()->comment('descri	[description] descripcion');
            $table->string('language_iso_ifx', 2)->after('deleted_at')->nullable()->comment('idioma	[language_iso] idioma');
            $table->smallInteger('pax_max_ifx')->after('deleted_at')->nullable()->comment('nropax	[pax_max] limite maximo de pax');
            $table->string('status_ifx', 2)->after('deleted_at')->nullable()->comment('status	[status_ifx] OK / 00 / XL para el importe traeria los != "XL" 00 q se usaban desde a1');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->removeColumn(['type_ifx', 'description_ifx', 'language_iso_ifx', 'pax_max_ifx', 'status_ifx']);
        });
    }
}
