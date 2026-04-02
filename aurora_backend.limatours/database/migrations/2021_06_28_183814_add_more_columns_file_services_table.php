<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMoreColumnsFileServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('file_services', function (Blueprint $table) {
            $table->string('provider_assigned', 2)->nullable()->after('date_out')->comment('asiemi= Si ya fue asignado a un proveedor (Puede ser CE de cerrado para que no lo modifiquen)');
            $table->string('provider_for_assign', 1)->nullable()->after('date_out')->comment('asigna= Si se asigna el servicio o no (P=Programable, SI o NO)');
            $table->string('reservation_sent', 2)->nullable()->after('date_out')->comment('comemi->SI se envió la reserva');
            $table->string('reservation_for_send', 1)->nullable()->after('date_out')->comment('viacom->Si es que se envia reserva');
            $table->integer('lending_accountant')->nullable()->after('date_out')->comment('nropro->(con Cesar) "contador prestador" guarda el contador que crea al generar la comunicación a los prestadores');
            $table->string('document_purchase_order', 2)->nullable()->after('date_out')->comment('doprem->documento orden de compra. Se utiliza para cuando se envía la orden de compra desde el File. Puede ir S/N (S=dependiendo de unas condiciones en misma t13/ N=t19)');
            $table->string('document_skeleton', 1)->nullable()->after('date_out')->comment('docupr->documento skeleton. Se utiliza cuando se genera el Skeleton. Puede ir S/N (S=dependiendo de unas condiciones en t19f/ N=t19d)');
            $table->integer('document_number')->nullable()->after('date_out')->comment('nrodoc->numero de documento');
            $table->string('document_type', 2)->nullable()->after('date_out')->comment('tipdoc-> tipo de documento');
            $table->string('serie', 1)->nullable()->after('date_out')->comment('serie-> serie del documento');
            $table->smallInteger('branch_number')->nullable()->after('date_out')->comment('nrosuc-> Numero de sucursal');
            $table->string('accounting_document_sent', 2)->nullable()->after('date_out')->comment('docemi->Si ya se emitio el documento contable');
            $table->string('use_accounting_document', 1)->nullable()->after('date_out')->comment('docum->S/N si va o no documento contable');
            $table->string('ticket_sent', 2)->nullable()->after('date_out')->comment('tktemi->SI/NO si ya fue emitido');
            $table->string('use_ticket', 1)->nullable()->after('date_out')->comment('ticket->SI/NO si se emite o no aereo solo para vuelos');
            $table->integer('voucher_number')->nullable()->after('date_out')->comment('nrovou-> numero de voucher');
            $table->string('voucher_sent', 2)->nullable()->after('date_out')->comment('vouemi->SI / NO si es que el voucher si ya fue emitido');
            $table->string('use_itinerary', 1)->nullable()->after('date_out')->comment('itiner-> S o N si va o no en el itinerario');
            $table->string('use_voucher', 1)->nullable()->after('date_out')->comment('vouche->S o N si se genera o no');
            $table->string('use_quote', 1)->nullable()->after('date_out')->comment('cotiza->S si se cotiza');
            $table->double('taxes')->nullable()->after('date_out')->comment('tax3->(double)impuestos igv');
            $table->double('total_amount_exempt')->nullable()->after('date_out')->comment('netexe->(double)neto excento');
            $table->double('total_amount_taxed')->nullable()->after('date_out')->comment('netgra->(double)si hay igv, es la parte gravada');
            $table->double('total_amount_invoice')->nullable()->after('date_out')->comment('netfac->(double)neto que se le va a facturar al cliente');
            $table->double('total_amount_created')->nullable()->after('date_out')->comment('iatced->tarifa resultante Resultante en monto del calculo con el piaced');
            $table->double('markup_created')->nullable()->after('date_out')->comment('piaced->comision del cliente en el momento que se creo en el file, y es posible modificarlo');
            $table->double('total_amount_provider')->nullable()->after('date_out')->comment('netpag->pago neto al proveedor');
            $table->double('total_amount')->nullable()->after('date_out')->comment('tarifa->(double)Monto total');
            $table->smallInteger('total_services')->nullable()->after('date_out')->comment('cansvs->Cantidad de servicios. En hoteles, cantidad de paxs');
            $table->string('mode_calculation_paxs', 1)->nullable()->after('date_out')->comment('paxuni->Calculo por pax o por unidad. Se usa en el calculo de totales P=aereos y hoteles por pasajero /U=por unidad ');
            $table->string('mode_calculation_days', 1)->nullable()->after('date_out')->comment('diario->Indicador de dias del servicio e usa en el calculo de totales G=Global(la cantidad, cantid x canpax) N=por noche. (las demas letras no se usan');
            $table->double('unit_cost_taxes')->nullable()->after('date_out')->comment('ivcuni->impuesto costo unitario. SUNAT t04.iva dentro del programa lo pone en 0');
            $table->double('unit_sale_taxes')->nullable()->after('date_out')->comment('ivvuni->impuesto venta unitario. SUNAT t04.iva dentro del programa lo pone en 0');
            $table->double('taxed_unit_cost')->nullable()->after('date_out')->comment('grcuni->gravado costo unitario. SUNAT t04.gravad dentro del programa lo pone en 0');
            $table->double('taxed_unit_sale')->nullable()->after('date_out')->comment('grvuni->gravado venta unitario. Dato de sunat q lo pone en 0, sale de la t04.gravad');
            $table->double('amount_cost_unit')->nullable()->after('date_out')->comment('cosuni->monto costo por unidad');
            $table->double('amount_sale_unit')->nullable()->after('date_out')->comment('vtauni->monto venta por unidad (con moneda de venta)');
            $table->double('amount_cost')->nullable()->after('date_out')->comment('cosloc->monto costo en moneda local');
            $table->double('amount_sale')->nullable()->after('date_out')->comment('vtaloc->monto venta en moneda local');
            $table->string('currency_cost', 4)->nullable()->after('date_out')->comment('moncos->moneda para costo, puede ser otra');
            $table->string('currency_sale', 4)->nullable()->after('date_out')->comment('monvta->moneda venta');
            $table->string('currency', 4)->nullable()->after('date_out')->comment('moneda-> tipo de moneda currency');
            $table->string('out_country_grouping', 4)->nullable()->after('date_out')->comment('gruout agrupamiento del pais inicio (SA= Sudamerica, de la t01 identi=P su codgru)');
            $table->string('out_zone_ifx', 2)->nullable()->after('date_out')->comment('zonout zona de salida . t01.codgru[3,4] ejem: select * from t01 where nroemp = 5 and identi = "C" and codigo = var13.ciuin ');
            $table->string('out_country_iso_ifx', 2)->nullable()->after('date_out')->comment('paiout->pais salida  (PE)');
            $table->string('starting_country_grouping', 4)->nullable()->after('date_out')->comment('gruin agrupamiento del pais inicio (SA= Sudamerica, de la t01 identi=P su codgru)');
            $table->string('start_zone_ifx', 2)->nullable()->after('date_out')->comment('zonin zona de inicio. t01.codgru[3,4] ejem: select * from t01 where nroemp = 5 and identi = "C" and codigo = var13.ciuin ');
            $table->string('starting_country_iso_ifx', 2)->nullable()->after('date_out')->comment('paiin->pais inicio (PE)');
            $table->smallInteger('passenger_sequence_number')->nullable()->after('date_out')->comment('"secpax"=> para vuelos y la info viene de la t12.nrosec del pasajero');
            $table->string('passenger_type', 3)->nullable()->after('date_out')->comment('"tippax"=> tipo de pasajero');
            $table->string('operation_type', 1)->nullable()->after('date_out')->comment('"tipope"=> Es de la t03.tipo[4,4] relacionado por el codigo');
            $table->date('operation_date')->nullable()->after('date_out')->comment('"fecope"=> Fecha de operacion, que crean o modifican el servicio. Se carga cuando se crea el servicio por única vez en la t13');
            $table->smallInteger('operation_number')->nullable()->after('date_out')->comment('"nroope"=> Numero de operacion, es de uso exclusivo del proceso de la Cotización');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('file_services', function (Blueprint $table) {
            $table->dropColumn(['provider_assigned', 'provider_for_assign', 'reservation_sent', 'reservation_for_send',
                'lending_accountant', 'document_purchase_order', 'document_skeleton', 'document_number', 'document_type',
                'serie', 'branch_number', 'accounting_document_sent', 'use_accounting_document', 'ticket_sent', 'use_ticket',
                'voucher_number', 'voucher_sent', 'use_itinerary', 'use_voucher', 'use_quote', 'taxes', 'total_amount_exempt',
                'total_amount_taxed', 'total_amount_invoice', 'total_amount_created', 'markup_created', 'total_amount_provider',
                'total_amount', 'total_services', 'mode_calculation_paxs', 'mode_calculation_days', 'unit_cost_taxes',
                'unit_sale_taxes', 'taxed_unit_cost', 'taxed_unit_sale', 'amount_cost_unit', 'amount_sale_unit',
                'amount_cost', 'amount_sale', 'currency_cost', 'currency_sale', 'currency', 'out_country_grouping',
                'out_zone_ifx', 'out_country_iso_ifx', 'starting_country_grouping', 'start_zone_ifx', 'starting_country_iso_ifx',
                'passenger_sequence_number', 'passenger_type', 'operation_type', 'operation_date', 'operation_number']);
        });
    }
}
