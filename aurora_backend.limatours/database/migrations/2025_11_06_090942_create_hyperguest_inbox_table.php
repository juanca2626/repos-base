<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHyperguestInboxTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hyperguest_inbounds', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->string('method', 64);
            $t->string('hotel_code', 64)->nullable();
            $t->string('echo_token', 128)->nullable();
            $t->string('message_id', 128)->nullable();
            $t->string('idempotency_key', 64)->unique(); // sha1
            $t->enum('status', ['queued','processing','processed','failed'])->default('queued');
            $t->unsignedInteger('attempts')->default(0);
            $t->text('error')->nullable();
            $t->longText('payload_xml'); // XML completo
            $t->timestamps();

            $t->index(['method', 'hotel_code']);
            $t->index(['status', 'created_at']);          // idempotente
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hyperguest_inbounds');
    }
}
