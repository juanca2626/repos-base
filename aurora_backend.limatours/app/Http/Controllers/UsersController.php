<?php

namespace App\Http\Controllers;

use App\ClientExecutive;
use App\DepartmentTeam;
use App\Http\Requests\GetUsersDetailsRequest;
use App\Http\Traits\Users;
use App\User;
use App\UserMarket;
use App\UserTypes;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use jeremykenedy\LaravelRoles\Models\Role;

class UsersController extends Controller
{
    use  Users;

    public function __construct()
    {
//        $this->middleware('permission:users.read')->only('index');
        $this->middleware('permission:users.create')->only('store');
        $this->middleware('permission:users.update')->only('update');
        $this->middleware('permission:users.delete')->only('delete');
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $active = $request->get('user_actives');
        $role = $request->get('role');
        $users = User::select(['id', 'code', 'name', 'email', 'user_type_id', 'session_active',
            'status', 'locked_at'])->search(request()->search)->typeUser(3)
            ->with('roles', 'userType')
            ->with(['employee' => function ($query) {
                $query->select(['id', 'user_id', 'department_team_id', 'position_id', 'is_kam', 'is_bdm']);
                $query->with(['team' => function ($query) {
                    $query->select(['id', 'name', 'department_id']);
                    $query->with(['department' => function ($query) {
                        $query->select(['id', 'name']);
                    }]);
                }]);
                $query->with(['position' => function ($query) {
                    $query->select(['id', 'name']);
                }]);
            }]);
        if (!empty($active)) {
            $users = $users->where('session_active', $active);
        }
        if (!empty($role)) {
            if ($role === 'kam') {
                $users = $users->whereHas('employee', function ($q) {
                    $q->where('is_kam', 1);
                });
            } else if ($role === 'bdm') {
                $users = $users->whereHas('employee', function ($q) {
                    $q->where('is_bdm', 1);
                });
            } else {
                // es un role_id
                $users = $users->whereHas('role_user', function ($q) use ($role) {
                    $q->where('role_id', $role);
                });
            }
        }
        $users = $users->get();

        $users_on_count = $users->where('session_active', 1)->count();
        $users_off_count = $users->where('session_active', 0)->count();
        return Response::json([
            'success' => true,
            'data' => $users,
            'users_on_count' => $users_on_count,
            'users_off_count' => $users_off_count
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $codeMarkets = $request->input('codeMarkets');
        $arrayErrors = [];
        $countErrors = 0;

        $users = DB::table('users')->where('code', '=', $request->input('code'))
            ->whereNotNull('deleted_at')->get();

        foreach ($users as $key => $value) {
            $key = ($key < 9) ? ('0' . ($key + 1)) : ($key + 1);

            DB::table('users')->where('id', '=', $value->id)->update([
                'code' => (substr($value->code, 0, 3) . '-' . $key),
                'updated_at' => date("Y-m-d H:i:s")
            ]);
        }

        $rules = [
            'name' => 'required',
            'password' => 'required',
            'code' => 'required|string|max:6|unique:users',
            'regions' => 'required|array',
            'regions.*' => 'exists:business_regions,id'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $errors = $validator->errors();

            foreach ($errors->all() as $error) {
                array_push($arrayErrors, $error);
            }
            $countErrors++;
        }
        if ($countErrors > 0) {
            return Response::json(['success' => false, 'errors' => $arrayErrors, 'users' => $users]);
        } else {
            $request->request->add(['user_type_id' => 3]);
            $request->request->add(['email_verified_at' => Carbon::now()]);
            $request->merge(['status' => 1]);

            $user = $this->userSave($request);

            if ($user->save()) {
                foreach ($codeMarkets as $code) {
                    $userMarket = new UserMarket();
                    $userMarket->user_id = $user->id;
                    $userMarket->market_id = $code;
                    $userMarket->save();
                }
            }
            $user = config('roles.defaultUserModel')::find($user->id);
            $role = Role::find($request->input('role'));
            $user->attachRole($role);

            // asignar el usuario a la region
            $user->businessRegions()->sync($request->regions);

            return Response::json(['success' => true]);

        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id)
    {
        $filter_ = ( is_numeric($id) ) ? 'id' : 'code';

        $user = User::where($filter_, $id)
            ->with('roles')
            ->with('markets')
            ->with(['employee' => function ($query) {
                $query->select(['id', 'user_id', 'department_team_id', 'position_id', 'is_kam', 'is_bdm']);
                $query->with(['team' => function ($query) {
                    $query->select(['id', 'name', 'department_id']);
                    $query->with(['department' => function ($query) {
                        $query->select(['id', 'name']);
                    }]);
                }]);
                $query->with(['position' => function ($query) {
                    $query->select(['id', 'name']);
                }]);
            }])
            ->first();

        return Response::json(['success' => true, 'data' => $user]);
    }

    public function getByCode($user_code)
    {
        $user = User::where('code', $user_code)->first();

        return Response::json(['success' => true, 'data' => $user]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        $codeMarkets = $request->input('codeMarkets');
//        if (Auth::user()->hasRole('admin') || Auth::user()->id === $id) {
        $arrayErrors = [];
        $countErrors = 0;
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'name' => 'required',
            'email' => 'required|email',
            'regions' => 'required|array',
            'regions.*' => 'exists:business_regions,id'
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();

            foreach ($errors->all() as $error) {
                array_push($arrayErrors, $error);
            }

            $countErrors++;
        }
        if ($countErrors > 0) {
            return Response::json(['success' => false, 'erros' => $arrayErrors]);
        } else {
            $user = User::find($id);
            $user->name = $request->input('name');
            $user->code = strtoupper($user->code);
            if (!empty($request->input('email'))) {
                $user->email = $request->input('email');
            }
            if (!empty($request->input('use_email'))) {
                $user->use_email = $request->input('use_email');
            }
            //$user->position = $request->input('position');
            if (!empty($request->input('status'))) {
                $user->status = $request->input('status');
            }

            if (!empty($request->input('password'))) {
                $user->password = bcrypt($request->input('password'));
            }
            //if ($user->save() && $request->input("userType") == 3) {
            if ($user->save()) {
                if ($request->input("userType") == User::USER_TYPE_EMPLOYEE) {
                    $isKam = $request->has('is_kam') ? $request->input('is_kam') : false;
                    $isBdm = $request->has('is_bdm') ? $request->input('is_bdm') : false;
                    $this->saveEmployee($user->id, $request->input('department_team_id'), $request->input('position_id'), $isKam, $isBdm);
                }

                if ($codeMarkets != '' && $codeMarkets != null) {
                    UserMarket::where(['user_id' => $id])->delete();
                    foreach ($codeMarkets as $code) {
                        $userMarket = new UserMarket();
                        $userMarket->user_id = $user->id;
                        $userMarket->market_id = $code;
                        $userMarket->save();
                    }
                }
            }
            if (!empty($request->input('role'))) {
                $user = config('roles.defaultUserModel')::find($user->id);
                $user->detachAllRoles();
                $role = Role::find($request->input('role'));
                $user->attachRole($role);
            }

            // asignar el usuario a la region
            $user->businessRegions()->sync($request->regions);
        }

        return Response::json(['success' => true]);
    }

    public function updateStatus($id, Request $request)
    {
        $user = User::find($id);
        if ($request->input("status")) {
            $user->status = false;
        } else {
            $user->status = true;
        }
        $user->save();
        return Response::json(['success' => true]);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $user = User::find($id);

        $user->delete();

        return Response::json(['success' => true]);
    }

    public function userExecutive(Request $request)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $querySearch = $request->input('query');
        $withCountSubstitutes = $request->input('withCountSubstitutes');
        $market = $request->input('market');

        $client_id = $request->input('client_id');

        $users_database = User::select(['id', 'code', 'name', 'email'])->market($market)->where('user_type_id',
            3)->where('code', '!=', 'ADMIN');

        if ($withCountSubstitutes) {
            $users_database = $users_database->withCount('executive_substitutes');
        }

        $users_frontend = [];

        $executive_client_database = ClientExecutive::select('user_id')->where('client_id',
            $client_id)->pluck('user_id');
        $executive_client_ids = $executive_client_database->pluck('user_id');

        if ($executive_client_database->count() > 0) {
            $users_database->whereNotIn('id', $executive_client_database);
        }
        $count = $users_database->count();

        if ($querySearch) {
            $users_database->where(function ($query) use ($querySearch) {
                $query->orWhere('name', 'like', '%' . $querySearch . '%');
                $query->orWhere('code', 'like', '%' . $querySearch . '%');
            });
        }

        if ($paging === 1) {
            $users_database = $users_database->take($limit)->get();
        } else {
            $users_database = $users_database->skip($limit * ($paging - 1))->take($limit)->get();
        }

        if ($count > 0) {
            for ($j = 0; $j < count($users_database); $j++) {
                $users_frontend[$j]["id"] = "";
                $users_frontend[$j]["user_code"] = $users_database[$j]["code"];
                $users_frontend[$j]["user_id"] = $users_database[$j]["id"];
                $users_frontend[$j]["name"] = $users_database[$j]['name'] ? $users_database[$j]['name'] : $users_database[$j]['email'];
                $users_frontend[$j]["client_id"] = $client_id;
                $users_frontend[$j]["selected"] = false;
                if ($withCountSubstitutes) {
                    $users_frontend[$j]["executive_substitutes_count"] = $users_database[$j]['executive_substitutes_count'];
                }
            }
        }
        $data = [
            'data' => $users_frontend,
            'count' => $count,
            'success' => true
        ];

        return Response::json($data);
    }

    public function userExecutiveSeller(Request $request)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $querySearch = $request->input('query');
        $lang = $request->input('lang');

        $users_database = User::select(['id', 'name', 'language_id', 'code', 'email', 'user_type_id'])
            ->with(['language', 'userType'])
            ->whereIn('user_type_id', [3, 4])
            ->where('code', '!=', 'ADMIN');

        $users_frontend = [];

        $count = $users_database->count();

        if ($querySearch) {
            $users_database->where(function ($query) use ($querySearch) {
                $query->orWhere('name', 'like', '%' . $querySearch . '%');
                $query->orWhere('code', 'like', '%' . $querySearch . '%');
            });
        }

        if ($paging === 1) {
            $users_database = $users_database->take($limit)->get();
        } else {
            $users_database = $users_database->skip($limit * ($paging - 1))->take($limit)->get();
        }

        if ($count > 0) {
            for ($j = 0; $j < count($users_database); $j++) {
                $users_frontend[$j]["code"] = $users_database[$j]["id"];
                $_code = $users_database[$j]['code'] ? '(' . $users_database[$j]['code'] . ') ' : '';
                $_name = $users_database[$j]['name'] ? $users_database[$j]['name'] : $users_database[$j]['email'];
                $users_frontend[$j]["label"] = $_code . $_name;
                $users_frontend[$j]["language"] = $users_database[$j]['language'];
                $users_frontend[$j]["userType"] = $users_database[$j]['userType'];
            }
        }
        $data = [
            'data' => $users_frontend,
            'count' => $count,
            'success' => true
        ];

        return Response::json($data);
    }

    public function validationErrorsToString($errArray)
    {
        $valArr = array();
        foreach ($errArray->toArray() as $key => $value) {
            $errStr = $key . ' ' . $value[0];
            array_push($valArr, $errStr);
        }
        if (!empty($valArr)) {
            $errStrFinal = implode(',', $valArr);
        }
        return $errStrFinal;
    }

    public function syncStoreExecutive(Request $request)
    {
        $arrayErrors = [];
        $countErrors = 0;
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'code' => 'required|string|max:6|unique:users',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $error) {

                array_push($arrayErrors, $error);
            }
            $countErrors++;
        }
        if ($countErrors > 0) {
            return Response::json([
                'success' => false,
                'message' => $this->validationErrorsToString($validator->errors())
            ]);
        } else {
            $request->request->add(['user_type_id' => 3]);
            $request->request->add(['email_verified_at' => Carbon::now()]);
            $request->merge(['status' => 1]);
            $user = $this->userSave($request);
            $user->save();
//            if ($user->save()) {
//                $userMarket = new UserMarket();
//                $userMarket->user_id = $user->id;
//                $userMarket->market_id = $code;
//                $userMarket->save();
//            }
            $user = config('roles.defaultUserModel')::find($user->id);
            $role = Role::find(3);
            $user->attachRole($role);

            return Response::json(['success' => true, 'message' => '']);

        }
    }

    public function syncUpdateExecutive($code, Request $request)
    {
        $arrayErrors = [];
        $countErrors = 0;
        $validator = Validator::make($request->all(), [
//            'code' => 'required',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $error) {
                array_push($arrayErrors, $error);
            }
            $countErrors++;
        }
        if ($countErrors > 0) {
            return Response::json([
                'success' => false,
                'message' => $this->validationErrorsToString($validator->errors())
            ]);
        } else {
            $user = User::where('code', $code)->first();
            if ($user) {

                if (!empty($request->input('name'))) {
                    $user->name = $request->input('name');
                }
                if (!empty($request->input('email'))) {
                    $user->email = $request->input('email');
                }
                if (!empty($request->input('status'))) {
                    $user->status = $request->input('status');
                }
                if (!empty($request->input('password'))) {
                    $user->password = bcrypt($request->input('password'));
                }
                $user->save();
            } else {
                return Response::json(['success' => true, 'message' => 'not found user ' . $code]);
            }
        }

        return Response::json(['success' => true, 'message' => '']);
    }

    //Trae los usuarios con roles de: Marketing,Producto,Negociaciones,Operaciones
    public function getUserNotifyService(Request $request)
    {
        $users = DB::table('users as u')
            ->join('role_user as r', 'u.id', '=', 'r.user_id')
            ->where('u.status', '=', 1)
            ->whereIn('r.role_id', [7, 8, 9, 17])->get([
                'u.name',
                'u.email',
//                'r.role_id',
            ]);
        return Response::json(['success' => true, 'data' => $users]);
    }

    public function updatePasswordAdmin()
    {
        $user = User::find(1);
        $user->password = bcrypt("admin.limatours");
        $user->save();

        return response()->json("Usuario Actualizado");
    }

    public function searchExecutives(Request $request)
    {
        $limit = ($request->has('limit')) ? $request->input('limit') : 20;
        $query = $request->input('queryCustom');
        $executives = User::with('markets');

        if ($query) {
            $executives = $executives->searchExecutives($query);
        }

        $executives = $executives->where('user_type_id', 3)->limit($limit)->orderBy('id', 'desc')->get([
            'id',
            'code',
            'name',
            'user_type_id'
        ]);

        $data = [
            'data' => $executives,
            'success' => true,
        ];

        return Response::json($data);
    }

    public function getExecutivesByTeam($team_id)
    {

        $executives = User::where('user_type_id', User::USER_TYPE_EMPLOYEE)
            ->with(['employee' => function ($query) use ($team_id) {
                $query->select(['user_id', 'department_team_id']);
                $query->where('department_team_id', $team_id);
                $query->with(['team' => function ($query) {
                    $query->select(['id', 'name']);
                }]);
            }])
            ->whereHas('employee', function ($query) use ($team_id) {
                $query->where('department_team_id', $team_id);

            })
            ->get(['id', 'code', 'name']);
        return Response::json(['success' => true, 'data' => $executives]);
    }

    public function getUsersDetails(GetUsersDetailsRequest $request)
    {
        $perPage = $request->input('per_page') ?? 10;
        try {
            $users = User::where('user_type_id', UserTypes::INTERNAL_USER)
                ->where('status', User::STATUS_ACTIVE)
                ->whereIn('id', $request->input('user_ids'))
                ->paginate($perPage, [
                    'id',
                    'code',
                    'name',
                    'email'
                ]);
            $pagination = [
                "total" => $users->total(),
                "per_page" => $users->perPage(),
                "from" => 1,
                "to" => $users->lastPage(),
                "last_page" => $users->lastPage(),
                "current_page" => $users->currentPage()
            ];
            return Response::json(['success' => true, 'data' => $users->items(), 'pagination' => $pagination]);
        } catch (\Exception $ex) {
            return Response::json(['success' => false, 'error' => $ex->getMessage()]);
        }
    }

}
