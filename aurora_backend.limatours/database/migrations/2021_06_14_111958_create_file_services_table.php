<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFileServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('file_services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('file_id');
            $table->foreign('file_id')->references('id')->on('files');
            $table->integer('item_number')->comment('"nroite":9, auto se usa como llave');
            $table->integer('item_number_parent')->comment('"itepaq"=> cuando el clasif es 3, aqui mostraria su nroite (5 es padre)');
            $table->string('classification_iso', 1)->nullable()
                ->comment('"clase" puede ser relacionada a classifications, Posibles valores de t13: (NULL, A=Aereo, H=Hotel, N, P=Paquete, R, T=Terrestre, X)');
            $table->string('code', 45)->comment('"codsvs"');
            // Deleted:
            $table->boolean('flag_accommodation')->nullable()->comment('"flag_acomodo"="1", Cuando esta asignado a un pax');
            // Deleted:
            $table->smallInteger('total_rooms')->nullable()
                ->comment('"cantid":2, cantidad de habitaciones (Cuando es hotel)');
            $table->string('code_request_book', 45)->nullable()
                ->comment('"preped":"LIMHDZ", codigo del servicio/hotel a quien le pido la reserva ');
            $table->string('code_request_invoice', 45)->nullable()
                ->comment('"prefac":"LIMHDZ", codigo del servicio/hotel quien me manda factura, a quien le pago');
            $table->string('code_request_voucher', 45)->nullable()
                ->comment('"prevou"=> codigo del servicio/hotel a quien va el voucher');
            $table->string('status_ifx', 2)->default('OK')
                ->comment('"estado":status del registro OK=>Vigente, XL=Eliminado');
            $table->string('classification', 1)->nullable()
                ->comment('"clasif":"1" classification (pero nada que ver con la tabla de mysql classifications) 1=servicio de tercero / 3=componente / 5=paquete propio (NULL,1,2,3,5,6,7,8,9,O)');
            $table->string('base_name_initial')->nullable()
                ->comment('"desbas_inicial":"CLASSIC DBL", o desbas Descripcion de la base de tarifas de la hab, que hayan puesto al reservar');
            $table->string('base_code', 10)->nullable()->comment('"bastar":"RCH2", base de tarifas');
            $table->string('base_name_original')->nullable()
                ->comment('"desbas":null, viene de la t01 como desbas_inicial Descripcion de la base de tarifas de la hab original');
            $table->string('additional_information')->nullable()
                ->comment('"infoad":null, Informacion adicional');
            $table->smallInteger('total_paxs')->nullable()->comment('"canpax":4, Cantidad de pasajeros');
            $table->string('category_hotel_name')->nullable()
                ->comment('"categoria_hotel":"PRIMERA SUPERIOR", la categoria original de hotel traida de la t02 identi H ');
            $table->smallInteger('number_annulments')->default(0)
                ->comment('"anulado":"0", Es un count para saber si hay gastos de cancelacion producto de una anulacion');
            $table->integer('relation_nights')->default(0)
                ->comment('"relation":"2", es el count de registros de la t21 ligado al nroite representa los dias por registro (puede ser cnt. de noches sin contar diaout)');
            $table->string('airline_name')->nullable()
                ->comment('"razon":null,nombre de la aerolínea. subquerys a t02 y t21 (estan las rutas) Descripcion del vuelo (si es q es vuelo)');
            $table->string('airline_code', 2)->nullable()->comment('"ciavue":null, sub t21 compañia aerea  código de la aerolinea');
            $table->string('airline_number', 4)->nullable()->comment('"nrovue":null, sub t21 nro de vuelo');
            $table->string('category_code_ifx', 5)->nullable()->comment('catser, se usa porque hemos agrupado los servicios en bloques, en modal para crear o editar servicios Están separados por pestañas en la cabecera agrupado (ejm. traslados definidos por TIN TOT) se mapeó en t01t');
            $table->string('type_code_ifx', 5)->nullable()->comment('"tipsvs":"HTL", tipo de servicio, puede ser guia, tren, miscelanios, y muchos más, con eso identifican al tipo de servicio');
            $table->string('description')->nullable()->comment('"descri"');
            $table->string('start_time', 5)->nullable()->comment('"horin" as "horin_prime":null, Hora de inicio');
            $table->string('departure_time', 5)->nullable()->comment('"horout":null, hora de fin del servicio');
            $table->string('description_ES', 900)->nullable()->comment('descri_es texto t19d identi="K"');
            $table->string('description_ES_code', 10)->nullable()->comment('flag_es clave t19d identi="K"');
            $table->string('description_EN', 900)->nullable()->comment('texto t19d identi="K"');
            $table->string('description_EN_code', 10)->nullable()->comment('clave t19d identi="K"');
            $table->string('description_PT', 900)->nullable()->comment('texto t19d identi="K"');
            $table->string('description_PT_code', 10)->nullable()->comment('clave t19d identi="K"');
            $table->string('description_IT', 900)->nullable()->comment('descri_it texto t19d identi="K"');
            $table->string('description_IT_code', 10)->nullable()->comment('flag_it clave t19d identi="K"');
            $table->string('city_in_iso', 4)->nullable()->comment('ciuin ciudad de servicio');
            $table->string('city_out_iso', 4)->nullable()->comment('ciuout ciudad de termino, destino del servicio');
            $table->string('city_name')->nullable()->comment('"descri_ciudad":"LIMA",  sub descri de la ciudad');
            $table->string('country_name')->nullable()->comment('"descri_pais":"PERU", sub descri del pais');
            $table->date('date_in')->nullable()->comment('"fecin": fecha de inicio');
            $table->date('date_out')->nullable()->comment('"fecout": fecha fin del servicio');
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
        Schema::dropIfExists('file_services');
    }
}
