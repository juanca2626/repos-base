<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDataDepartmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $created_at = date("Y-m-d H:i:s");

        // Todo Dirección de recursos
        $id = DB::table('departments')->insertGetId([
            'name' => 'Dirección de recursos',
            'created_at' => $created_at,
            'updated_at' => $created_at
        ]);

        DB::table('department_teams')->insert([
            'name' => 'Contabilidad',
            'department_id' => $id,
            'created_at' => $created_at,
            'updated_at' => $created_at
        ]);

        DB::table('department_teams')->insert([
            'name' => 'Dirección de recursos',
            'department_id' => $id,
            'created_at' => $created_at,
            'updated_at' => $created_at
        ]);

        DB::table('department_teams')->insert([
            'name' => 'Recursos Humanos',
            'department_id' => $id,
            'created_at' => $created_at,
            'updated_at' => $created_at
        ]);

        DB::table('department_teams')->insert([
            'name' => 'Servicios Generales',
            'department_id' => $id,
            'created_at' => $created_at,
            'updated_at' => $created_at
        ]);

        DB::table('department_teams')->insert([
            'name' => 'SIG',
            'department_id' => $id,
            'created_at' => $created_at,
            'updated_at' => $created_at
        ]);

        DB::table('department_teams')->insert([
            'name' => 'Tesoreria',
            'department_id' => $id,
            'created_at' => $created_at,
            'updated_at' => $created_at
        ]);

        DB::table('department_teams')->insert([
            'name' => 'TI',
            'department_id' => $id,
            'created_at' => $created_at,
            'updated_at' => $created_at
        ]);

        // Todo Direccion General
        $id = DB::table('departments')->insertGetId([
            'name' => 'Dirección General',
            'created_at' => $created_at,
            'updated_at' => $created_at
        ]);

        DB::table('department_teams')->insert([
            'name' => 'Dirección General',
            'department_id' => $id,
            'created_at' => $created_at,
            'updated_at' => $created_at
        ]);

        DB::table('department_teams')->insert([
            'name' => 'Marketing',
            'department_id' => $id,
            'created_at' => $created_at,
            'updated_at' => $created_at
        ]);

        // Todo Negociacion, Prod. & Ots
        $id = DB::table('departments')->insertGetId([
            'name' => 'Negociacion, Prod. & Ots',
            'created_at' => $created_at,
            'updated_at' => $created_at
        ]);

        DB::table('department_teams')->insert([
            'name' => 'Negociacion, Prod. & Ots',
            'department_id' => $id,
            'created_at' => $created_at,
            'updated_at' => $created_at
        ]);

        DB::table('department_teams')->insert([
            'name' => 'Negociaciones',
            'department_id' => $id,
            'created_at' => $created_at,
            'updated_at' => $created_at
        ]);

        DB::table('department_teams')->insert([
            'name' => 'Ots',
            'department_id' => $id,
            'created_at' => $created_at,
            'updated_at' => $created_at
        ]);

        DB::table('department_teams')->insert([
            'name' => 'Producto',
            'department_id' => $id,
            'created_at' => $created_at,
            'updated_at' => $created_at
        ]);

        // Todo Operaciones
        $id = DB::table('departments')->insertGetId([
            'name' => 'Operaciones',
            'created_at' => $created_at,
            'updated_at' => $created_at
        ]);

        DB::table('department_teams')->insert([
            'name' => 'Operaciones',
            'department_id' => $id,
            'created_at' => $created_at,
            'updated_at' => $created_at
        ]);

        DB::table('department_teams')->insert([
            'name' => 'Operaciones - Boletaje',
            'department_id' => $id,
            'created_at' => $created_at,
            'updated_at' => $created_at
        ]);

        DB::table('department_teams')->insert([
            'name' => 'Operaciones - Guía',
            'department_id' => $id,
            'created_at' => $created_at,
            'updated_at' => $created_at
        ]);

        DB::table('department_teams')->insert([
            'name' => 'Operaciones - Reps',
            'department_id' => $id,
            'created_at' => $created_at,
            'updated_at' => $created_at
        ]);

        DB::table('department_teams')->insert([
            'name' => 'Operaciones Nacional',
            'department_id' => $id,
            'created_at' => $created_at,
            'updated_at' => $created_at
        ]);

        // Todo R1 - Usa & Canada
        $id = DB::table('departments')->insertGetId([
            'name' => 'R1 - Usa & Canada',
            'created_at' => $created_at,
            'updated_at' => $created_at
        ]);

        DB::table('department_teams')->insert([
            'name' => 'Usa & Canada',
            'department_id' => $id,
            'created_at' => $created_at,
            'updated_at' => $created_at
        ]);

        // Todo R2 - Europa & Asia Pacífico
        $id = DB::table('departments')->insertGetId([
            'name' => 'R2 - Europa & Asia Pacífico',
            'created_at' => $created_at,
            'updated_at' => $created_at
        ]);

        DB::table('department_teams')->insert([
            'name' => 'Asia Pacífico',
            'department_id' => $id,
            'created_at' => $created_at,
            'updated_at' => $created_at
        ]);

        DB::table('department_teams')->insert([
            'name' => 'Europa',
            'department_id' => $id,
            'created_at' => $created_at,
            'updated_at' => $created_at
        ]);

        DB::table('department_teams')->insert([
            'name' => 'Europa & Asia Pacífico',
            'department_id' => $id,
            'created_at' => $created_at,
            'updated_at' => $created_at
        ]);

        DB::table('department_teams')->insert([
            'name' => 'Mice',
            'department_id' => $id,
            'created_at' => $created_at,
            'updated_at' => $created_at
        ]);

        // Todo R3 - Esp.Ita.Por & Latam
        $id = DB::table('departments')->insertGetId([
            'name' => 'R2 - Europa & Asia Pacífico',
            'created_at' => $created_at,
            'updated_at' => $created_at
        ]);

        DB::table('department_teams')->insert([
            'name' => 'España, Italia, Portugal & Latam',
            'department_id' => $id,
            'created_at' => $created_at,
            'updated_at' => $created_at
        ]);

        DB::table('department_teams')->insert([
            'name' => 'España, Italia, Portugal',
            'department_id' => $id,
            'created_at' => $created_at,
            'updated_at' => $created_at
        ]);

        DB::table('department_teams')->insert([
            'name' => 'Latam',
            'department_id' => $id,
            'created_at' => $created_at,
            'updated_at' => $created_at
        ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
