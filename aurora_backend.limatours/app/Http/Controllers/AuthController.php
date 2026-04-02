<?php

namespace App\Http\Controllers;

use App\ClientSeller;
use App\Client;
use App\DepartmentTeam;
use App\LoginLog;
use App\User;
use Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    const SOURCE_ME = 'FRONTED';

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'testing']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return JsonResponse
     */
    public function login(Request $request)
    {
        $user_exists = User::where(function ($query) {
            $query->orWhere('code', request('code'))
                ->orWhere('email', request('code'));
        })
            ->where('password', md5(request('password')))->where('authorization', 0)->get();

        if ($user_exists->count() > 0) {
            $user = User::find($user_exists[0]["id"]);
            $user->authorization = 1;
            $user->password = bcrypt(request('password'));
            $user->save();
        }

        $credentials = [
            'email' => request('code'),
            'password' => request('password'),
            'status' => 1,
            'deleted_at' => NULL
        ];

        $user = User::where(function ($query) {
            $query->orWhere('code', request('code'))
                ->orWhere('email', request('code'));
        })
        ->whereNull('deleted_at')
        ->first();

        if (!$user) {
            return response()->json(['success' => false, 'error' => 'unauthorized'], 401);
        }

        // Verificar si está bloqueado
        if ($user->locked_at && now()->lt(Carbon::parse($user->locked_at)->addMinutes(15))) {
            $remaining = Carbon::parse($user->locked_at)->addMinutes(15)->diffInMinutes(now());
            return response()->json([
                'message' => 'account_locked',
                'time' => $remaining,
            ], 423); // Locked
        }

        $token = auth()->attempt($credentials);

        if (!$token) {
            $credentials = [
                'code' => request('code'),
                'password' => request('password'),
                'status' => 1,
                'deleted_at' => NULL
            ];

            $token = auth()->attempt($credentials);

            if (!$token) {
                $user->increment('login_attempts');

                if ($user->login_attempts >= 3) {
                    $user->locked_at = now();
                }

                $user->save();

                return response()->json(['success' => false, 'error' => 'unauthorized'], 401);
            }
        }

        if (Auth::user()->status == 0) {
            auth()->logout();
            return response()->json(['success' => false, 'error' => 'deactivated_status'], 401);
        }

        $request->session()->put('password_in', request('password'));

        $role = Auth::user()->roles()->first();
        if (Auth::user()->user_type_id != User::USER_TYPE_SELLER) {
            if (!$role or $role->status == 0) {
                auth()->logout();
                return response()->json(['success' => false, 'error' => 'Unassigned role'], 401);
            }
        } else { // Seller
            $client_seller = ClientSeller::where('user_id', (Auth::user()->id))->first();
            if (!$client_seller) {
                auth()->logout();
                return response()->json(['success' => false, 'error' => 'Client seller without relation'], 401);
            } else {
                $client = Client::where('id', $client_seller->client_id)->where('status', 1)->first();
                if (!$client) {
                    auth()->logout();
                    return response()->json(['success' => false, 'error' => 'Client inactive or deleted'], 401);
                }
            }
        }

        $department = collect();
        if (Auth::user()->user_type_id === User::USER_TYPE_EMPLOYEE) {
            $department_team_id = Auth::user()->employee->department_team_id;
            $department_team = DepartmentTeam::find($department_team_id);
            if ($department_team) {
                $department = [
                    'id' => $department_team->department_id,
                    'name' => $department_team->department->name,
                    'team' => [
                        'id' => $department_team->id,
                        'name' => $department_team->name,
                    ],
                ];
            }
        }

        $user = User::find(Auth::user()->id);
        $user->count_login = ((int)$user->count_login) + 1;
        $user->session_active = 1;

        // Login exitoso ---------------
        $user->login_attempts = 0;
        $user->locked_at = null;
        // -----------------------------
        $user->save();

        $login_log = new LoginLog;
        $login_log->user_id = $user->id;
        $login_log->date = $user->updated_at;
        $login_log->save();

        $response = [
            'success' => true,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'permissions' => $this->permissions(),
            'code' => Auth::user()->code,
            'email' => Auth::user()->email,
            'user_type_id' => Auth::user()->user_type_id,
            'photo' => Auth::user()->photo,
            'name' => Auth::user()->name,
            'department' => $department,
            'user_id' => Auth::user()->id,
            'rol' => $role->slug
        ];

        return response()->json($response);
    }

    public function unlockAccount(Request $request, $id)
    {
        $user = User::where('id', '=', $id)->first();

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $user->locked_at = null;
        $user->login_attempts = 0;
        $user->save();

        return response()->json([
            'message' => 'Cuenta desbloqueada. Ya puedes volver a intentar.',
            'success' => true,
        ]);
    }

    public function permissions()
    {
        $user = auth()->user();
        $permissions = [];
        foreach ($user->roles as $user_role) {
            foreach ($user_role->permissions as $permission) {
                $tmp = explode(".", $permission->slug);
                $subject = $tmp[0];
                $action = $tmp[1];
                if (!in_array($subject, array_column($permissions, 'subject'))) {
                    $permissions[] = array('subject' => $subject, 'actions' => array($action));
                } else {
                    $subject_index = array_search($subject, array_column($permissions, 'subject'));
                    $permissions[$subject_index]['actions'][] = $action;
                }
            }
        }

        return $permissions;
    }

    /**
     * Get the authenticated User.
     *
     * @return JsonResponse
     */
    public function me(Request $request)
    {

        try {
            $user = auth()->user();
            $permissions = [];
            if ($request->source && $request->source == self::SOURCE_ME) {
                $permissions = $this->permissions();
            } else {
                foreach ($user->roles as $user_role) {
                    foreach ($user_role->permissions as $permission) {
                        $tmp = explode(".", $permission->slug);
                        if (array_key_exists($tmp[0], $permissions)) {
                            array_push($permissions[$tmp[0]], $tmp[1]);
                        } else {
                            $permissions[$tmp[0]] = [$tmp[1]];
                        }
                    }
                }
            }

            $response = $this->mapUsers();

            $response['rol'] = $response['roles'][0]['slug'];

            $employee = auth()->user()->employee;
            if(!empty($employee))
            {
                $response['is_kam'] = (int) $employee->is_kam;
            }

            unset($response['roles']);
            $response['permissions'] = $permissions;
            return response()->json($response, 200);
        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 204);
        }
    }

    public function mapUsers()
    {
        $authUser = auth()->user();
        // Mapear los datos del usuario
        $userData = [
            'id' => $authUser->id,
            'name' => $authUser->name,
            'email' => $authUser->email,
            'email_alt' => $authUser->email_alt,
            'email_verified_at' => $authUser->email_verified_at,
            'code' => $authUser->code,
            'level' => $authUser->level,
            'automatic' => $authUser->automatic,
            'use_email' => $authUser->use_email,
            'use_contract' => $authUser->use_contract,
            'client_representative_id' => $authUser->client_representative_id,
            'use_attached' => $authUser->use_attached,
            'token' => $authUser->token,
            'status' => $authUser->status,
            'reset_password' => $authUser->reset_password,
            'relations' => $authUser->relations,
            'language_id' => $authUser->language_id,
            'user_type_id' => $authUser->user_type_id,
            'authorization' => $authUser->authorization,
            'logo' => $authUser->logo,
            'auth_maling' => $authUser->auth_maling,
            'color_code' => $authUser->color_code,
            'class_code' => $authUser->class_code,
            'grupo_code' => $authUser->grupo_code,
            'category_code' => $authUser->category_code,
            'generic_equivalence' => $authUser->generic_equivalence,
            'position' => $authUser->position,
            'session_active' => $authUser->session_active,
            'photo' => $authUser->photo,
            'auto_password' => $authUser->auto_password,
            'count_login' => $authUser->count_login,
            'client_seller' => null,
            'roles' => $authUser->roles->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'slug' => $role->slug,
                    'description' => $role->description,
                    'level' => $role->level,
                    'status' => $role->status,
                    'pivot' => [
                        'user_id' => $role->pivot->user_id,
                        'role_id' => $role->pivot->role_id,
                    ],
                    'permissions' => $role->permissions->map(function ($permissions) {
                        return [
                            'id' =>$permissions->id,
                            'name' =>$permissions->name,
                            'slug' =>$permissions->slug,
                            'description' =>$permissions->description,
                            'model' =>$permissions->model,
                        ];
                    })
                ];
            }),
        ];

        if($authUser->client_seller and $authUser->client_seller !== null){
//            dd($authUser->client_seller);
            $userData['client_seller'] = [
                'id' => $authUser->client_seller['id'],
                'status' => $authUser->client_seller['status'],
                'client_id' => $authUser->client_seller['client_id'],
                'user_id' => $authUser->client_seller['user_id'],
                'client' => [
                    'id' => $authUser->client_seller['client']['id'],
                    'code' => $authUser->client_seller['client']['code'],
                    'name' => $authUser->client_seller['client']['name'],
                    'business_name' => $authUser->client_seller['client']['business_name'],
                    'address' => $authUser->client_seller['client']['address'],
                    'web' => $authUser->client_seller['client']['web'],
                    'anniversary' => $authUser->client_seller['client']['anniversary'],
                    'postal_code' => $authUser->client_seller['client']['postal_code'],
                    'ruc' => $authUser->client_seller['client']['ruc'],
                    'email' => $authUser->client_seller['client']['email'],
                    'phone' => $authUser->client_seller['client']['phone'],
                    'use_email' => $authUser->client_seller['client']['use_email'],
                    'have_credit' => $authUser->client_seller['client']['have_credit'],
                    'credit_line' => $authUser->client_seller['client']['credit_line'],
                    'status' => $authUser->client_seller['client']['status'],
                    'market_id' => $authUser->client_seller['client']['market_id'],
                    'classification_code' => $authUser->client_seller['client']['classification_code'],
                    'classification_name' => $authUser->client_seller['client']['classification_name'],
                    'executive_code' => $authUser->client_seller['client']['executive_code'],
                    'bdm_id' => $authUser->client_seller['client']['bdm_id'],
                    'general_markup' => $authUser->client_seller['client']['general_markup'],
                    'ecommerce' => $authUser->client_seller['client']['ecommerce'],
                    'allow_direct_passenger_creation' => $authUser->client_seller['client']['allow_direct_passenger_creation'],
                    'country_id' => $authUser->client_seller['client']['country_id'],
                    'city_code' => $authUser->client_seller['client']['city_code'],
                    'city_name' => $authUser->client_seller['client']['city_name'],
                    'language_id' => $authUser->client_seller['client']['language_id'],
                    'logo' => $authUser->client_seller['client']['logo'],
                    'bdm' => $authUser->client_seller['client']['bdm'],
                    'markets' => $authUser->client_seller['client']['markets']
                ],
            ];
        }
        return $userData;
    }

    public function password(Request $request)
    {
        try {
            $password = $request->session()->get('password_in');
            $response = ['password' => $password];
            return response()->json($response, 200);
        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 500);
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return JsonResponse
     */
    public function logout()
    {
        $user = User::find(Auth::user()->id);
        $user->session_active = 0;
        $user->save();

        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
        ]);
    }

    public function generateSsoSupportDesk(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Datos base
        $email = $user->email;
        $name = trim($user->name ?? 'Invitado');
        $userTypeId = $request->input('user_type_id', 1);

        // Nonce y timestamp
        $nonce = bin2hex(random_bytes(8));
        $ts = time();

        // Clave secreta
        $secret = config('app.sso_sopportedesk_key');

        // Datos a firmar (idéntico formato que usa tu decodificador)
        $dataToSign = json_encode([
            $email,
            $name,
            (int)$userTypeId,
            $ts,
            $nonce,
        ]);

        // Firma HMAC
        $sig = hash_hmac('sha256', $dataToSign, $secret);

        // Payload final
        $payload = [
            'email' => $email,
            'name' => $name,
            'user_type_id' => (int)$userTypeId,
            'ts' => $ts,
            'nonce' => $nonce,
            'sig' => $sig,
        ];

        // Codificar todo el payload en base64
        $token = base64_encode(json_encode($payload));

        // URL destino (la del sistema receptor)
        $finalUrl = 'https://lito.soportedesk.com/sso/login?token=' . urlencode($token);

        return response()->json([
            'url' => $finalUrl,
        ]);
    }


}
