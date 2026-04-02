<?php

use App\RoleAdmin;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class UserGuestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role = new RoleAdmin();
        $role->name = "Guest";
        $role->slug = "guest";
        $role->description = "Invitados";
        $role->level = 1;
        $role->status = 1;
        $role->save();

        $user = new User();
        $user->name = 'Guest';
        $user->email = 'admin@admin.com';
        $user->code = 'GUEST';
        $user->language_id = 1;
        $user->user_type_id = 3;
        $user->password = Hash::make('1nv1t@d0_L1t0');
        $user->email_verified_at = Carbon::now();
        $user->save();

        $user->attachRole($role->id);

    }
}
