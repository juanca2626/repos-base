<?php

namespace App\Http\Traits;

use App\Client;
use App\Employee;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use jeremykenedy\LaravelRoles\Models\Role;

trait Users
{
    /**
     * @param Request $request
     * @return array
     */
    public function userSave(Request $request)
    {
        $user = new User();
        $user_type_id = User::USER_TYPE_CLIENT;
        $user->name = $request->input('name');
        $user->language_id = $request->input('language') ? $request->input('language') : 1;
        $user->status = $request->input('status');
        $user->use_email = $request->input('use_email');
        $user->email_verified_at = $request->input('email_verified_at');
        if (!empty($request->input('code'))) {
            $user->code = strtoupper($request->input('code'));
        } else {
            $client_id = ($request->has('client_id')) ? $request->input('client_id') : null;
            $user->code = $this->getUserCodeSeller($client_id);
        }

        if (!empty($request->input('user_type_id'))) {
            $user->user_type_id = $request->input('user_type_id');
            $user_type_id = $request->input('user_type_id');
        } else {
            $user->user_type_id = User::USER_TYPE_CLIENT;
        }
        if (!empty($request->input('email'))) {
            $user->email = $request->input('email');
        }
        if (!empty($request->input('position'))) {
            $user->position = $request->input('position');
        }

        $user->auto_password = (int)$request->input('auto_password');

        if (empty($request->input('password'))) {
            $user->password = bcrypt('secret');
        } else {
            $user->password = bcrypt($request->input('password'));
        }

        $user->save();
        if ((int)$user_type_id == User::USER_TYPE_SELLER) {
            $user = config('roles.defaultUserModel')::find($user->id);
            if ($request->has('ecommerce') and $request->input('ecommerce') == 1) {
                $role = Role::find(38);//Rol de clientes ecommerce
            } else {
                $role = Role::find(20);//Rol de clientes
            }
            $user->attachRole($role);
        } else {
            $user = config('roles.defaultUserModel')::find($user->id);
            $role = Role::find($request->input('role'));
            $isKam = $request->has('is_kam') ? $request->input('is_kam') : false;
            $isBdm = $request->has('is_bdm') ? $request->input('is_bdm') : false;
            if ((int)$user_type_id == User::USER_TYPE_EMPLOYEE) {
                $this->saveEmployee($user->id, $request->input('department_team_id'), $request->input('position_id'), $isKam, $isBdm);
            }
            $user->attachRole($role);
        }


        return $user;
    }

    private function getUserCodeSeller($client_id)
    {
        //Todo variable de intentos
        $attempts = 9999;
        $code_seller = '';

        //Todo Si el codigo del cliente biene con datos
        if (!empty($client_id)) {
            //Todo Obtenemos el iso del pais del cliente para generar el codigo del seller asi logramos segmentar los codigos
            $client = Client::where('id', $client_id)->with('countries')->first(['id', 'country_id']);
            if ($client and !empty($client->country_id)) {
                $country_iso = $client->countries->iso;
            } else {
                $country_iso = 'PE';
            }
        } else {
            //Todo Obtenemos el iso del pais del cliente para generar el codigo del seller
            $country_iso = 'PE';
        }

        for ($i = 0; $i <= $attempts; $i++) {
            //Todo Generamos el codigo con el codigo del pais obtenido del cliente
            $code_seller = createUserCode($country_iso);
            $users_with_count = User::where('code', $code_seller)->count();
            //Todo Si no existe entonces paramos el ciclo y devolvemos el codigo si no buscara otro hasta encontrar uno libre
            if ($users_with_count === 0) {
                break;
            }
        }

        return $code_seller;

    }

    /**
     * @param Request $request
     * @return array
     */
    public function userUpdate(Request $request, $id)
    {
        $user = User::find($id);
        $user->name = $request->input('name');
        $user->status = $request->input('status');
        if (!empty($request->input('code'))) {
            $user->code = strtoupper($request->input('code'));
        }
        if (!empty($request->input('user_type_id'))) {
            $user->user_type_id = $request->input('user_type_id');
        }
        if (!empty($request->input('email'))) {
            $user->email = $request->input('email');
        }
        if (!empty($request->input('position'))) {
            $user->position = $request->input('position');
        }

        $user->auto_password = (int)$request->input('auto_password');

        if (!empty($request->input('password'))) {
            $user->password = bcrypt($request->input('password'));
        }
        $user->status = $request->input("status") ? '1' : '0';
        $user->save();

        $user = config('roles.defaultUserModel')::find($user->id);
        $role_user = DB::table('role_user')->where('user_id', $user->id)->first();
        if ($role_user) {
            $role_id = ($request->has('role')) ? $request->input('role') : $role_user->role_id;
            DB::table('role_user')->where('id', $role_user->id)->update([
                'role_id' => $role_id,
            ]);
        } else {
            $role = Role::find($request->input('role'));
            $user->attachRole($role);
        }

        return $user;
    }

    /**
     * @throws \Exception
     */
    public function saveEmployee($user_id, $department_team_id, $position_id, $is_kam = false, $is_bdm = false)
    {
        try {
            DB::beginTransaction();
            $find_user_employee_exist = Employee::where('user_id', $user_id)->first(['id']);
            if ($find_user_employee_exist) {
                $employee = Employee::find($find_user_employee_exist->id);
                $employee->user_id = $user_id;
                $employee->department_team_id = $department_team_id;
                $employee->position_id = $position_id;
                $employee->is_kam = $is_kam;
                $employee->is_bdm = $is_bdm;
                $employee->save();
            } else {
                $newEmployee = new Employee();
                $newEmployee->user_id = $user_id;
                $newEmployee->department_team_id = $department_team_id;
                $newEmployee->position_id = $position_id;
                $newEmployee->is_kam = $is_kam;
                $newEmployee->is_bdm = $is_bdm;
                $newEmployee->save();
            }
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            throw new \Exception($exception->getMessage());
        }


    }
}
