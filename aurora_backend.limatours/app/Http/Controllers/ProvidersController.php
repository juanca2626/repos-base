<?php

namespace App\Http\Controllers;

use App\Client;
use App\ClientExecutive;
use App\Http\Traits\Users;
use App\User;
use App\UserMarket;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use jeremykenedy\LaravelRoles\Models\Role;

class ProvidersController extends Controller
{
    use  Users;

    public function __construct()
    {
        $this->middleware('permission:suppliers.read')->only('index');
        $this->middleware('permission:suppliers.create')->only('store');
        $this->middleware('permission:suppliers.update')->only('update');
        $this->middleware('permission:suppliers.delete')->only('delete');
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        $suppliers = User::select('id', 'code', 'name', 'email', 'user_type_id', 'language_id',
            'status')->search(request()->search)->typeUser(2)->with('roles', 'userType')->get();
        return Response::json(['success' => true, 'data' => $suppliers]);
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
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'password' => 'required',
            'code' => 'required|string|max:6|unique:users',
        ]);

        
        if ($validator->fails()) {
            return Response::json([
                'success' => false,
                'errors' => $validator->errors()->all() // Se envían los mensajes de error
            ]);
        } else {
            /*
            if (empty($request->input("userType"))) {
                $request->request-> add(['user_type_id' => '1']);
            } else {
                $request->request-> add(['user_type_id' => $request->input("userType")]);
            }
            */
            $request->request->add(['user_type_id' => 2]);
            $request->request->add(['email_verified_at' => Carbon::now()]);
            $request->merge(['status' => 1]);

            $user = $this->userSave($request);

            //if ($user->save() && $request->input("userType") == 3) {
            if ($user->save()) {
                foreach ($codeMarkets as $code) {
                    $userMarket = new UserMarket();
                    $userMarket->user_id = $user->id;
                    $userMarket->market_id = $code;
                    if ($userMarket->save()) {
                        $dataClients = [];
                        $dataClients = Client::where('market_id', $code)->where('status', '1')->get();
                        foreach ($dataClients as $key => $client) {
                            $executive = new ClientExecutive();
                            $executive->status = 1;
                            $executive->user_id = $user->id;
                            $executive->client_id = $client->id;
                            $executive->save();
                        }
                    }
                }
            }
            $user = config('roles.defaultUserModel')::find($user->id);
            $role = Role::find($request->input('role'));
            $user->attachRole($role);

            return Response::json(['success' => true]);

        }
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
            'code' => 'required|string|max:6|unique:users,code,' . $id,
            'name' => 'required',
            'email' => 'required|email',
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
            $user->code = $request->input('code');
            if (!empty($request->input('email'))) {
                $user->email = $request->input('email');
            }
            //$user->position = $request->input('position');

            $user->status = $request->input('status');

            if (!empty($request->input('password'))) {
                $user->password = bcrypt($request->input('password'));
            }
            //if ($user->save() && $request->input("userType") == 3) {
            if ($user->save()) {
                $data_user = UserMarket::where(['user_id' => $id])->delete();
                foreach ($codeMarkets as $code) {
                    $userMarket = new UserMarket();
                    $userMarket->user_id = $user->id;
                    $userMarket->market_id = $code;
                    if ($userMarket->save()) {
                        $dataClients = [];
                        $dataClients = Client::where('market_id', $code)->where('status', '1')->get();
                        foreach ($dataClients as $key => $client) {
                            $executive = new ClientExecutive();
                            $executive->status = 1;
                            $executive->user_id = $user->id;
                            $executive->client_id = $client->id;
                            $executive->save();
                        }
                    }
                }
            }

            $user = config('roles.defaultUserModel')::find($user->id);
            $user->detachAllRoles();
            $role = Role::find($request->input('role'));
            $user->attachRole($role);
        }
//        }

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

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id)
    {
        $user = User::where('id', $id)->with('roles', 'markets')->first();

        return Response::json(['success' => true, 'data' => $user]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function selectBox(Request $request)
    {
        $querySearch = strtoupper($request->input('query'));

        $providers = User::with('userType')
            ->where(function ($query) use ($querySearch) {
                $query->orWhere('code', 'like', '%' . $querySearch . '%');
                $query->orWhere('name', 'like', '%' . $querySearch . '%');
            })
            ->where('user_type_id',2)
            ->take(10)->get();

        return Response::json(['success' => true, 'data' => $providers]);
    }
}
