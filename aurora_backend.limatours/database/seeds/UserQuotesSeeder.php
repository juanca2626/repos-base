<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserQuotesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $file_users = File::get("database/data/users_quotes.json");
        $users = json_decode($file_users, true);
        $created_at = date("Y-m-d H:i:s");

        DB::transaction(function () use ($users,$created_at) {
            foreach ($users as $user) {
                $find = DB::table('users')->where('code',$user['code']);
                if( $find->count() == 0 ){
                    DB::table('users')->insert([
                        'code' => $user['code'],
                        'password' => $user['password'],
                        'name' => $user['name'],
                        'email' => $user['email'],
                        'level' => $user['level'],
                        'automatic' => $user['automatic'],
                        'use_email' => $user['use_email'],
                        'use_contract' => $user['use_contract'],
                        'use_attached' => $user['use_attached'],
                        'token' => $user['token'],
                        'status' => $user['status'],
                        'reset_password' => $user['reset_password'],
                        'relations' => $user['relations'],
                        'language_id' => $user['language_id'],
                        'user_type_id' => (strlen($user['code'])==3) ? 3 : 4,
                        'authorization' => $user['authorization'],
                        'logo' => $user['logo'],
                        'color_code' => $user['color_code'],
                        'auth_maling' => $user['auth_maling'],
                        'class_code' => $user['class_code'],
                        'grupo_code' => $user['grupo_code'],
                        'category_code' => $user['category_code'],
                        'generic_equivalence' => $user['generic_equivalence'],
                        'created_at' => $created_at,
                        'updated_at' => $created_at
                    ]);
                }
            }
        });
    }
}
