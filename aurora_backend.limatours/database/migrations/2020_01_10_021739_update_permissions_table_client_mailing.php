<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdatePermissionsTableClientMailing extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('permissions')->where('slug', 'masimailing.show')->update(['slug' => 'masimailing.read',
        'name' => 'MasiMailing: Read',
        'description' => 'Show Masi Mailing module']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('permissions')->where('slug', 'masimailing.read')->update(['slug' => 'masimailing.show',
        'name' => 'MasiMailing: Show',
        'description' => 'Show Masi Mailing module']);
    }
}
