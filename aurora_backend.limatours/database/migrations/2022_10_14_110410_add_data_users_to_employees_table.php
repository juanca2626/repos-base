<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use \Illuminate\Support\Facades\DB;

class AddDataUsersToEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $getUsers = \App\User::where('user_type_id', \App\User::USER_TYPE_EMPLOYEE)->get();
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $created_at = date("Y-m-d H:i:s");
        foreach ($getUsers as $user) {
            DB::table('employees')->insert([
                'user_id' => $user->id,
                'created_at' => $created_at,
                'updated_at' => $created_at
            ]);
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("SET foreign_key_checks=0");
        \App\Employee::truncate();
        DB::statement("SET foreign_key_checks=1");
    }
}
