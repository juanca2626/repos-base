<?php

namespace App\Http\Controllers;

use App\Client;
use App\ClientSeller;
use App\Language;
use App\Mail\NotificationSellerCreated;
use App\Http\Traits\Users;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ClientSellersController extends Controller
{
    use  Users;

    public function __construct()
    {
        // $this->middleware('permission:clientsellers.read')->only('index');
        $this->middleware('permission:clientsellers.create')->only('store');
        $this->middleware('permission:clientsellers.update')->only('update');
        $this->middleware('permission:clientsellers.delete')->only('delete');
    }

    /**
     * Display a listing of the resource.
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $lang = $request->has("lang") ? $request->input("lang") : 'en';
        $language = Language::where('iso', $lang)->first();
        if (!$language) {
            $language_id = 1;
        } else {
            $language_id = $language->id;
        }
        $client_id = $request->input("client_id");
        $status = $request->input("status");
        $search = $request->input("search");

        $clients = User::with([
            'translations' => function ($query) use ($language_id) {
                $query->where('type', 'user');
                $query->where('language_id', $language_id);
            }
        ])->with('clientUsers')
            ->searchSeller($search, $status)
            ->clientSeller($client_id)
            ->where('user_type_id', User::USER_TYPE_SELLER)
            ->get();

        return Response::json(['success' => true, 'data' => $clients]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        try {
            //Todo Para contar con where no sirve el modelo, no cuenta si está deleted, es mejor hacerlo con db.
            // Que sí trae en caso el deleted esté con dato..
            $exist = DB::table('users')->whereNull('deleted_at')->where('email', '=',
                $request->__get('email'))->count();
            $arrayErrors = [];
            $countErrors = 0;
            $inputs = [
                'name' => 'required',
                'email' => (($exist == 0) ? 'required' : 'required|unique:users,email'),
            ];

            if ((int)$request->input('auto_password') == 0) {
                $inputs['password'] = 'required';
            }

            $validator = Validator::make($request->all(), $inputs);

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
                    'error' => $this->validationErrorsToString($validator->errors())
                ]);
            } else {
                \Illuminate\Support\Facades\DB::beginTransaction();
                $client = Client::find($request->input('client_id'), ['id', 'ecommerce']);
                $request->request->add(['user_type_id' => '4']);
                $request->request->add(['email_verified_at' => Carbon::now()]);
                $request->request->add(['ecommerce' => $client->ecommerce]);

                $password = $request->input('password');

                if ((int)$request->input('auto_password') == 1) {
                    $password = Str::random(10);
                    $request->request->add(['password' => $password]);
                }

                $request->merge(['status' => 1]);

                $user = $this->userSave($request);

                if (!empty($user) && $user->count() > 0) {

                    $client = Client::with(['languages'])
                        ->where('id', '=', $request->input('client_id'))
                        ->first();

                    if ($client) {
                        $kam = User::with(['markets'])->where('code', '=', $client->executive_code)
                            ->first();

                        if (!$kam) {
                            return Response::json([
                                'success' => false,
                                'error' => 'La Ficha cliente se encuentra incompleta. Por favor agregar el dato del KAM'
                            ]);
                        } else {
                            $kam = $kam->toArray();
                        }

                        $seller = new ClientSeller();
                        $seller->status = 1;
                        $seller->user_id = $user->id;
                        $seller->client_id = $client->id;
                        $seller->save();

                        $lang = @$client->languages->iso;

                        if ($lang == '') {
                            $lang = 'en';
                        }

                        //Todo Enviando correo de confirmación..
                        $user->password = $password;

                        $markets = array_map(function ($market) {

                            $market = $market['name'];
                            return $market;

                        }, $kam['markets']);

                        $_data = [
                            'user' => $user,
                            'client' => $client,
                            'kam' => $kam,
                            'markets' => $markets,
                        ];

                        if (App::environment('production') === true) {
                            Mail::to($user->email)
                                ->cc(Auth::user()->email)
                                ->send(new NotificationSellerCreated($_data, $lang));
                        } else {
                            Mail::to('jgq@limatours.com.pe')
                                ->send(new NotificationSellerCreated($_data, $lang));
                        }

                    }


                }
                \Illuminate\Support\Facades\DB::commit();
                return Response::json(['success' => true, 'object_id' => $user->id]);
            }
        } catch (\Exception $exception) {
            \Illuminate\Support\Facades\DB::rollBack();
            return Response::json([
                'success' => false,
                'error' => $exception->getLine() . ' - ' . $exception->getMessage()
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(Request $request, $id)
    {
        $lang = $request->has("lang") ? $request->input("lang") : 'en';
        $language = Language::where('iso', $lang)->first();
        if (!$language) {
            $language_id = 1;
        } else {
            $language_id = $language->id;
        }
        $seller = User::with([
            'translations' => function ($query) use ($language_id) {
                $query->where('type', 'user');
                $query->where('language_id', $language_id);
            }
        ])->with([
            'galeries' => function ($query) {
                $query->where('type', 'client');
                $query->where('slug', 'client_logo');
            }
        ])->with('clientUsers')->where('id', $id)->first();

        if ($seller) {
            $user_role = DB::table('role_user')->where('user_id', $id)->first();
            $seller->role_id = $user_role->role_id;
        }
        return Response::json(['success' => true, 'data' => $seller]);
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
        // Para contar con where no sirve el modelo, no cuenta si está deleted, es mejor hacerlo con db.
        // Que sí trae en caso el deleted esté con dato..
        $exist = DB::table('users')->whereNull('deleted_at')->where('id', '=', $id)->where('email', '=',
            $request->__get('email'))->count();

        $arrayErrors = [];
        $countErrors = 0;
        $inputs = [
            'name' => 'required',
            'email' => ($exist == 1) ? 'required' : 'unique:users,email,' . $id . ',id,deleted_at,NULL',
        ];

        $validator = Validator::make($request->all(), $inputs);

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
                'error' => $this->validationErrorsToString($validator->errors())
            ]);
        } else {
            $user = $this->userUpdate($request, $id);

            if (!empty($user) && $user->count() > 0) {
                $seller = ClientSeller::where('user_id', $user->id)->first();
                $seller->status = $request->input('status_seller');
                $seller->save();
            }
        }

        return Response::json(['success' => true, 'object_id' => $user->id]);
    }

    public function updateStatus($id, Request $request)
    {
        $seller = User::find($id);
        $seller->status = $request->input("status") ? '0' : '1';
        $seller->save();

        return Response::json(['success' => true]);
    }

    public function updateReservation($id, Request $request)
    {
        $seller = ClientSeller::where('user_id', '=', $id)->first();
        $seller->disable_reservation = $request->input("disable_reservation") ? '0' : '1';
        $seller->save();

        return Response::json(['success' => true]);
    }

    public function selectBox()
    {
        // client Seller sólo tiene la referencia.. con el usuario..
        // clients tiene los datos generales..
        $sellers = ClientSeller::select('id', 'name')->where('status', 1)->get();
        $result = [];
        foreach ($sellers as $seller) {
            array_push($result, ['text' => $seller->name, 'value' => $seller->id]);
        }
        return Response::json(['success' => true, 'data' => $result]);
    }

    public function get_more_sellers(Request $request)
    {
        $lang = $request->input("lang");
        $status = $request->input("status");
        $search = $request->input("search");

        $client_id = ClientSeller::where('user_id', Auth::user()->id)->first()->client_id;

        $clients = User::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'user');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with('clientUsers')->searchSeller($search, $status)
            ->clientSeller($client_id)->where('user_type_id', 4)->get();

        $clients = $clients->transform(function ($item) {

            $item['its_me'] = false;
            if (Auth::user()->id === $item['id']) {
                $item['its_me'] = true;
            }

            return $item;
        });

        return Response::json(['success' => true, 'data' => $clients]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        try {
            $seller = ClientSeller::find($id);
            $user_id = $seller->user_id;
            $seller->delete();
            $user = User::find($user_id);
            $user->delete();
            return Response::json(['success' => true]);
        } catch (\Exception $exception) {
            return Response::json(['success' => true, 'error' => $exception->getMessage()]);
        }
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

}
