<?php
  
use Illuminate\Database\Migrations\Migration;
use App\RoleAdmin;
use App\User;
use Carbon\Carbon; 
use Illuminate\Support\Facades\DB;
class AddUserFileTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
   
        DB::transaction(function () {

        
            $role = RoleAdmin::firstOrCreate(
                ['slug' => 'file_svc'], // condición única
                [
                    'name' => 'File Services',
                    'description' => 'Microservicio Files',
                    'level' => 1,
                    'status' => 1
                ]
            );
        
            $user = User::firstOrCreate(
                ['email' => 'filems@svc.com'], // condición única
                [
                    'name' => 'File Services',
                    'code' => 'filems',
                    'language_id' => 1,
                    'user_type_id' => 3,
                    'password' => Hash::make('9fileIKKI@9999_L1t0'),
                    'email_verified_at' => Carbon::now()
                ]
            );

        
            if (!$user->roles()->where('role_id', $role->id)->exists()) {
                $user->attachRole($role->id);
            }

        
            if (!$user->employee()->exists()) {
                $user->employee()->create([
                    'department_team_id' => 7,
                    'position_id' => 6,
                    'is_kam' => 0,
                    'is_bdm' => 0
                ]);
            }

        });


    }

 
}
