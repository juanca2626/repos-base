<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePermissionModulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 2) Si no existe, créala básica
        if (!Schema::hasTable('permission_modules')) {
            Schema::create('permission_modules', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('slug')->unique();
                $table->timestamps();
            });
        }

        // 3) Asegura columnas jerárquicas (si faltan)
        Schema::table('permission_modules', function (Blueprint $table) {
            if (!Schema::hasColumn('permission_modules', 'kind')) {
                $table->string('kind', 20)->nullable()->after('slug');
            }
            if (!Schema::hasColumn('permission_modules', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0)->after('kind');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Quita FK y columnas extra (opcional)
        if (Schema::hasTable('permission_modules')) {
            Schema::table('permission_modules', function (Blueprint $table) {
                if (Schema::hasColumn('permission_modules', 'kind')) {
                    $table->dropColumn('kind');
                }
                if (Schema::hasColumn('permission_modules', 'sort_order')) {
                    $table->dropColumn('sort_order');
                }
            });
        }
    }
}
