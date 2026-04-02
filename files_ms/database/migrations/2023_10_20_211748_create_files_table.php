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
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('serie_reserve_id')->nullable();
            $table->unsignedBigInteger('client_id');
            $table->char('client_code', 20);
            $table->string('client_name');
            $table->unsignedBigInteger('reservation_id');
            $table->integer('order_number')->nullable()->comment('(select nrore2 from t89b where nroemp = 5 and tipmov = "F" and nrore1 = t11.nroref) as nroped Nro de pedido');
            $table->unsignedInteger('file_number')->comment('nroref-> numero de file');
            $table->string('reservation_number',45)->nullable()->comment('nrores-> numero de reserva');
            $table->string('budget_number',45)->nullable()->comment('nropre-> numero de presupuesto');
            $table->char('sector_code', 4)->nullable()->comment('codsec-> codigo del sector | 1 = Fit, 2 = Grupo, 3 = Serie');
            $table->char('group', 6)->nullable()->comment('grupo');
            $table->integer('sale_type')->nullable()->comment('concta-> Si es al contado o a credito o (tipoventa) (viene de la ficha del cliente)(tmb esta en t2 como flci)');
            $table->char('tariff', 6)->nullable()->comment('tarifa-> tarifario');
            $table->char('currency', 4)->nullable()->comment('moncot-> tipo de moneda currency');
            $table->smallInteger('revision_stages')->nullable()->comment('succli-> 1 / 0 / null esta en DTR Receptivo - Cuando es 2 esta en operaciones (HAY UN PROCESO) estapas de revision');
            $table->boolean('ope_assign_stages')->default(0)->comment('revision_stages = 2 ?  0=plomo, 1=verde');
            $table->char('executive_code', 3)->nullable()->comment('codven-> codigo de ejecutivo KAM del cli');
            $table->char('executive_code_sale', 3)->nullable()->comment('codope-> codigo Ejecutivo QRV Cotizaciones de Reservas Ventas');
            $table->char('executive_code_process', 3)->nullable()->comment('operad-> Ejecutivo QRR Cotizaciones de Reservas que procesan');
            $table->char('applicant', 10)->nullable()->comment('solici-> solicitante, persona que solicito. Agencia');
            $table->char('file_code_agency', 14)->nullable()->comment('refext-> numero de file pero del cliente de su agencia, de su sistema');
            $table->string('description')->nullable()->comment('descri');
            $table->char('lang', 2)->nullable()->comment('descri');
            $table->date('date_in')->nullable();
            $table->date('date_out')->nullable();
            $table->smallInteger('adults')->nullable();
            $table->smallInteger('children')->default(0)->nullable();
            $table->smallInteger('infants')->default(0)->nullable();
            $table->char('use_invoice', 1)->nullable()->comment('coniva-> Si es que factura (t2) Ficha de cliente');
            $table->string('observation')->nullable()->comment('coniva-> Si es que factura (t2) Ficha de cliente');
            $table->smallInteger('total_pax')->nullable()->comment('nropax-> cantidad de pax total');
            $table->boolean('have_quote')->default(0)->comment('cotiza');
            $table->boolean('have_voucher')->default(0)->comment('vouche');
            $table->boolean('have_ticket')->default(0)->comment('ticket');
            $table->boolean('have_invoice')->default(0)->comment('NO= tiene servicios(detalle) para factura pero no se han facturado aun / 00= no tiene servicios o los servicios que tiene ninguno de ellos se factura / PL= se ha facturado algún servicio / SI= se ha facturado todo lo que corresponde al file');
            $table->enum('status', ['OK', 'XL', 'BL', 'CE', 'PF'])->default('OK')->comment('status->OK (Vigente) / XL (Anulado) / BL (Bloqueado) / CE (Cerrado)  / PF (Por Facturar)');
            $table->char('promotion', 6)->nullable()->comment('promos-> codigo promocional');            
            $table->decimal('total_amount', 8, 2)->comment('statement, actualmente es la suma de todos los total_amount (precio venta) de todos los itinerarios'); 
            $table->decimal('markup_client', 8, 2);
            $table->smallInteger('type_class_id');            
            $table->boolean('passenger_changes')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
