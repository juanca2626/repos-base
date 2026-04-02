<?php

use App\Package;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateTagIdColumnFromPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE packages MODIFY COLUMN tag_id BIGINT UNSIGNED NULL");

        Package::query()
            ->where('status', -1)
            ->update([
                'tag_id' => null,
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE packages MODIFY COLUMN tag_id BIGINT UNSIGNED NOT NULL");
    }
}
