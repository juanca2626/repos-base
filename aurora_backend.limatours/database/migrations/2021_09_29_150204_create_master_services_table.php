<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMasterServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string("code", 6)->comment('codigo	code');
            $table->string("classification", 1)->comment('clasvs	classification');
            $table->string("city_in_iso", 4)->comment('ciudes');
            $table->string("city_out_iso", 4)->comment('ciuhas');
            $table->string("description")->nullable()->comment('descri');
            $table->string("description_large")->nullable()->comment('lintlx');
            $table->string("type_iso", 4)->nullable()->comment('tipo	type_iso tipo de servicio | t01 identi T');
            $table->string("country_iso", 4)->nullable()->comment('codgru	country_iso Pais iso codigo');
            $table->string("provider_code_request", 6)->nullable()->comment('preped	provider_code_request prestador/proveedor al q se le pide el svs | t02 identi P');
            $table->string("provider_code_bill", 6)->nullable()->comment('prefac	provider_code_bill prestador al q se factura | pero si es 000000->es papa');
            $table->string("provider_code_voucher", 6)->nullable()->comment('prevou	provider_code_voucher prestador al q se le envia el voucher');
            $table->string("unit", 10)->nullable()->comment('unidad	unit unidad de venta / como nota referencial');
            $table->string("pricing_code_time", 1)->nullable()->comment('diario	pricing_code_time forma de tarifación segun tiempo en q se da el svs (ingreso limitado) d=dia / n=noche / g=global');
            $table->string("pricing_code_sale", 1)->nullable()->comment('paxuni	pricing_code_sale forma de tarifación respecto a la venta svs (selección limitada) p=pax / u=unidad');
            $table->string("allow_provider_email", 1)->nullable()->comment('via	allow_provider_email S / N si envia comunicación al prestador');
            $table->string("allow_voucher", 1)->nullable()->comment('vouche	allow_voucher S / N si envia voucher');
            $table->string("allow_itinerary", 1)->nullable()->comment('itiner	allow_itinerary S / N si esta dentro de la generacion del itinerario cuando se genera file (Aparece en el file, pero no exporta como itinerario)');
            $table->string("assignable", 1)->nullable()->comment('asigna	assignable S / N si el servicio es asignable (un guia o transportista x ejm.) y le sale en operaciones');
            $table->smallInteger("nights")->default(0)->nullable()->comment('cantnt	nights cantidad de noches (aplica a svs q duran mas de 1 d, paquetes)');
            $table->string("allow_markup", 1)->nullable()->comment('basein	allow_markup S / N si permite margen/markup o no');
            $table->string("accounting_account_sale", 8)->nullable()->comment('ctavta	accounting_account_sale cuenta contable venta');
            $table->string("accounting_account_cost", 8)->nullable()->comment('ctacos	accounting_account_cost cuenta contable costo');
            $table->string("intermediation", 1)->nullable()->comment('interm	intermediation S / N intermediacion, dato para facturacion');
            $table->string("status_ifx", 6)->nullable()->comment('operad	status_ifx del svs (RETIRA/ACTIVA/COAMOR o NULL)');
            $table->boolean("status")->default(1);
            $table->string("codaux", 1)->nullable()->comment('codaux');
            $table->string("allow_export", 1)->nullable()->comment('codfac	allow_export S / N si el servicio es de exportación o no, para cuando se factura el file');
            $table->softDeletes();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('master_services');
    }
}
