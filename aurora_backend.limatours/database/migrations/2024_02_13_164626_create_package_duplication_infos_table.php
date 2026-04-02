<?php

use App\PackageDuplicationInfo;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePackageDuplicationInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('package_duplication_infos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('package_id')->unique();
            $table->foreign('package_id')->references('id')->on('packages')->onDelete('CASCADE');
            $table->unsignedBigInteger('duplicated_package_id');
            $table->foreign('duplicated_package_id')->references('id')->on('packages')->onDelete('CASCADE');
            $table->text('processed_plan_rate_ids');
            $table->string('duplication_status')->default(PackageDuplicationInfo::DUPLICATION_STATUS_PROCESSING);
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
        Schema::dropIfExists('package_duplication_infos');
    }
}
