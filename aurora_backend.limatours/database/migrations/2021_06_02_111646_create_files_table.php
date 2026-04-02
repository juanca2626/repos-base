<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('client_id');
            $table->foreign('client_id')->references('id')->on('clients');
            $table->unsignedBigInteger('reservation_id');
            $table->foreign('reservation_id')->references('id')->on('reservations');
            // deleted
            $table->smallInteger('total_accommodation')->nullable()->comment('(select count(*) from t41 where nroemp = 5 and nroref = t11.nroref and tipsvs = "H") as flag_hotel
  Es para saber si existe el acomodo del hotel');
            // deleted
            $table->integer('order_number')->nullable()->comment('(select nrore2 from t89b where nroemp = 5 and tipmov = "F" and nrore1 = t11.nroref) as nroped
Nro de pedido');
            $table->integer('file_number')->comment('nroref-> numero de file');
            $table->string('reservation_number', 45)->nullable()->comment('nrores-> numero de reserva');
            $table->string('budget_number', 45)->nullable()->comment('nropre-> numero de presupuesto ');
            $table->string('sector_code', 4)->nullable()->comment('codsec-> codigo del sector');
            $table->string('group', 6)->nullable()->comment('grupo');
            $table->integer('sale_type')->nullable()->comment('concta-> Si es al contado o a credito o (tipoventa) (viene de la ficha del cliente)(tmb esta en t2 como flci)');
            $table->string('tariff', 6)->nullable()->comment('tarifa-> tarifario');
            $table->string('currency', 4)->nullable()->comment('moncot-> tipo de moneda currency');
            $table->smallInteger('revision_stages')->nullable()->comment('succli-> 1 / 0 / null esta en DTR Receptivo - Cuando es 2 esta en operaciones (HAY UN PROCESO) estapas de revision');
            $table->string('executive_code', 6)->nullable()->comment('codven-> codigo de ejecutivo KAM del cli');
            $table->string('executive_code_sale', 6)->nullable()->comment('codope-> codigo Ejecutivo QRV Cotizaciones de Reservas Ventas');
            $table->string('applicant', 10)->nullable()->comment('solici-> solicitante, persona que solicito. Agencia ');
            $table->string('file_code_agency', 14)->nullable()->comment('refext-> numero de file pero del cliente de su agencia, de su sistema');
            $table->string('description')->nullable()->comment('descri');
            $table->string('lang', 2)->nullable()->comment('idioma-> language');
            $table->date('date_in')->nullable();
            $table->date('date_out')->nullable();
            $table->smallInteger('adults')->nullable();
            $table->smallInteger('children')->nullable();
            $table->smallInteger('infants')->nullable();
            $table->string('use_invoice', 1)->nullable()->comment('coniva-> Si es que factura (t2) Ficha de cliente*');
            $table->string('observation')->nullable()->comment('observ-> observacion');
            $table->integer('total_paxs')->nullable()->comment('nropax-> cantidad de pax total');
            $table->string('executive_code_process', 4)->nullable()->comment('operad-> Ejecutivo QRR Cotizaciones de Reservas que procesan');
            $table->string('have_quote', 2)->nullable()->comment('cotiza');
            $table->string('have_voucher', 2)->nullable()->comment('vouche');
            $table->string('have_ticket', 2)->nullable()->comment('ticket');
            $table->string('have_invoice', 2)->nullable()->comment('factur');
            $table->string('status', 2)->nullable();
            $table->string('promotion', 6)->nullable()->comment('promos-> codigo promocional');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('files');
    }
}
