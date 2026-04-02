<?php

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use jeremykenedy\LaravelRoles\Models\Role;

// @codingStandardsIgnoreLine
class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $admin = User::where('code', '=', 'ADMIN')->first();

        if ($admin === null) {
            $user = new User();
            $user->name = 'Admin';
            $user->email = 'admin@admin.com';
            $user->code = 'ADMIN';
            $user->language_id = 1;
            $user->user_type_id = 3;
            $user->password = bcrypt('123456');
            $user->email_verified_at = Carbon::now();
            $user->save();

            $user->attachRole(1);
        }
        DB::connection()->disableQueryLog();
        $file_users = File::get("database/data/users.json");
        $users = json_decode($file_users, true);

        $counter = 0;

        $userRole = Role::where('slug', '=', 'user')->first();

        foreach ($users as $user) {
            if (App::environment('local') === true && $counter > 100) {
                break;
            }

            $user['password'] = bcrypt('123456');

            $newUser = User::create($user);
            $newUser->attachRole($userRole);

            $counter++;
        }


//        $created_at = date("Y-m-d H:i:s");
//
//        DB::transaction(function () use ($users,$created_at) {
//            foreach ($users as $user) {
//                $find = DB::table('users')->where('code',$user['code']);
//                if( $find->count() == 0 ){
//                    DB::table('users')->insert([
//                        'code' => $user['code'],
//                        'password' => $user['password'],
//                        'name' => $user['name'],
//                        'email' => $user['email'],
//                        'level' => $user['level'],
//                        'automatic' => $user['automatic'],
//                        'use_email' => $user['use_email'],
//                        'use_contract' => $user['use_contract'],
//                        'use_attached' => $user['use_attached'],
//                        'token' => $user['token'],
//                        'status' => $user['status'],
//                        'reset_password' => $user['reset_password'],
//                        'relations' => $user['relations'],
//                        'language_id' => $user['language_id'],
//                        'user_type_id' => $user['user_type_id'],
//                        'authorization' => $user['authorization'],
//                        'logo' => $user['logo'],
//                        'color_code' => $user['color_code'],
//                        'auth_maling' => $user['auth_maling'],
//                        'class_code' => $user['class_code'],
//                        'grupo_code' => $user['grupo_code'],
//                        'category_code' => $user['category_code'],
//                        'generic_equivalence' => $user['generic_equivalence'],
//                        'created_at' => $created_at,
//                        'updated_at' => $created_at
//                    ]);
//                }
//            }
//        });

    }
}
