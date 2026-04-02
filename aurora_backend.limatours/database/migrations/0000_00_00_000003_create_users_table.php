<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// @codingStandardsIgnoreLine
class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('email');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('code', 6)->unique()->comment('CODIGO (t02p)');
            $table->tinyInteger('level')->nullable()->comment('NIVPRO (t02p)');
            $table->string('automatic', 6)->nullable()->comment('AUTOMA (t02p)');
            $table->char('use_email', 2)->nullable()->comment('CONCOR (t02p)');
            $table->char('use_contract', 2)->nullable()->comment('CONCON (t02p)');
            $table->char('use_attached', 2)->nullable()->comment('CONADJ (t02p)');
            $table->string('token', 45)->nullable()->comment('CTOKEN (t02p)');
            $table->char('status', 2)->nullable()->comment('ESTADO (t02p)');
            $table->char('reset_password', 1)->nullable()->comment('RESETP (t02p)');
            $table->text('relations')->nullable()->comment('RELACI (t02p)');
            $table->unsignedBigInteger('language_id')->index();
            $table->unsignedBigInteger('user_type_id')->index();
            $table->string('authorization', 20)->nullable()->comment('AUTORI (t02p)');
            $table->string('logo', 180)->nullable()->comment('LINKLO (t02p)');
            $table->char('auth_maling', 2)->nullable()->comment('AUMAIL (t02p)');
            $table->string('color_code', 20)->nullable()->comment('CODCOL (t02p)');
            $table->char('class_code', 4)->nullable()->comment('CODCLA (T02)');
            $table->char('grupo_code', 4)->nullable()->comment('CODGRU (t02)');
            $table->char('category_code', 1)->nullable()->comment('NUMERA (t02)');
            $table->string('generic_equivalence', 6)->nullable()->comment('PREFAC (t02)');
            $table->string('position')->nullable();

            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('language_id')->references('id')->on('languages');
            $table->foreign('user_type_id')->references('id')->on('user_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
