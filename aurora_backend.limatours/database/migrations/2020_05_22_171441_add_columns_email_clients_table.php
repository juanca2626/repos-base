<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsEmailClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('use_email', 2)->after('name')->nullable()->default('NO');
            $table->string('email')->after('name')->nullable();
        });

        $clients = \App\Client::all();
        foreach ( $clients as $c ){
            $user = \App\User::where('code',$c->code)->first();
            if($user){
                $c->email = $user->email;
                $c->use_email = $user->use_email;
                $c->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->removeColumn(['email', 'use_email']);
        });
    }
}
