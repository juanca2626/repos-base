<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('file_temporary_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_id')->constrained('files');
            $table->enum('entity', ['service-temporary'])->comment('service-temporary');
            $table->unsignedBigInteger('object_id')->comment('id service');
            $table->text('name')->comment('Nombre del service');
            $table->string('category')->nullable();
            $table->string('object_code')->comment('codsvs');
            $table->char('country_in_iso', 2)->nullable()->comment('paiin->pais inicio (PE)');
            $table->string('country_in_name',100)->nullable()->comment('"descri_pais":"PERU", sub descri del pais');
            $table->char('city_in_iso', 4)->nullable()->comment('ciuin ciudad de servicio');
            $table->string('city_in_name',100)->nullable()->comment('"descri_ciudad":"LIMA",  sub descri de la ciudad');
            $table->char('zone_in_iso', 3)->nullable()->comment('zonin zona de inicio. t01.codgru[3,4] ejem: select * from t01 where nroemp = 5 and identi = "C" and codigo = var13.ciuin');
            $table->bigInteger('zone_in_id')->nullable();   
            $table->boolean('zone_in_airport')->nullable();
            $table->char('country_out_iso', 2)->nullable()->comment('paiout->pais salida  (PE)');
            $table->string('country_out_name',100)->nullable()->comment('"descri_pais":"PERU", sub descri del pais');
            $table->char('city_out_iso', 4)->nullable()->comment('ciuout ciudad de termino, destino del servicio');
            $table->string('city_out_name',100)->nullable()->comment('"descri_ciudad":"LIMA",  sub descri de la ciudad');
            $table->char('zone_out_iso', 3)->nullable()->comment('zonout zona de salida . t01.codgru[3,4] ejem: select * from t01 where nroemp = 5 and identi = "C" and codigo = var13.ciuin');
            $table->bigInteger('zone_out_id')->nullable();   
            $table->boolean('zone_out_airport')->nullable();
            $table->time('start_time')->nullable()->comment('"horin" as "horin_prime":null, Hora de inicio');
            $table->time('departure_time')->nullable()->comment('"horout":null, hora de fin del servicio');
            $table->date('date_in');
            $table->date('date_out');
            $table->smallInteger('total_adults')->default(0);
            $table->smallInteger('total_children')->default(0);
            $table->smallInteger('total_infants')->default(0);
            $table->decimal('markup_created', 8, 3)
                ->default(0)->comment('piaced->comision del cliente en el momento que se creo en el file, y es posible modificarlo');
            $table->decimal('total_amount', 8, 2)->default(0)->comment('tarifa->(double)Monto total');
            $table->decimal('total_cost_amount', 8, 2)->default(0)->comment('tarifa->(double)Monto total');
            $table->decimal('profitability', 8, 2)->default(0)->comment('tarifa->(double)Monto total');
            $table->boolean('serial_sharing')->default(0)
                ->comment('Por defecto 0=No, si es 1=Si entonces el servicio entre compuestos tendría la tarifa compartida entre el total de pax de la salida de la serie');
            $table->string('executive_code')->nullable();
            $table->longText('data_passengers')->nullable()->comment('Información de pasajeros que vienen de Aurora 2 que se usará en el proceso de servicios que se ejecuta en segundo plano');
            $table->boolean('status')->default(1);
            $table->boolean('confirmation_status')->default(1)->comment('Estado de la habitacion OK = 1, RQ = 0');
            $table->longText('policies_cancellation_service')->nullable();  
            $table->bigInteger('service_rate_id')->nullable();   
            $table->boolean('is_in_ope')->nullable()->default(0); 
            $table->boolean('sent_to_ope')->nullable()->default(0); 
            $table->boolean('hotel_origin',3)->nullable();
            $table->boolean('hotel_destination',3)->nullable();       
            $table->char('service_supplier_code', 20)->nullable();
            $table->string('service_supplier_name')->nullable();            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temporary_services');
    }
};
