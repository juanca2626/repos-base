<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Role;
use App\RoleUser;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function username()
    {
        return 'code';
    }

    public function login(Request $request)
    {
        if($request->user_id) {
            $user = User::find($request->user_id);

            if($user)
            {
                Auth::login($user);
                //ToDo
                /** agregar mas logica de validaciones*/
            }

            return redirect()->intended('/home');
        }


        $this->validateLogin($request);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        $credentials = [
            'email' => request('code'),
            'password' => request('password'),
            'status' => 1,
            'deleted_at' => NULL
        ];

        $token = auth()->attempt($credentials);

        // Attempt login..
        if (!$token)
        {
            $credentials = [
                'code' => request('code'),
                'password' => request('password'),
                'status' => 1,
                'deleted_at' => NULL
            ];

            $token = auth()->attempt($credentials);

            if (!$token)
            {
                return redirect()->intended('/home');
            }
        }
        else
        {
            return redirect()->intended('/home');
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }



    public function logout(Request $request)
    {
        Session::flush();
        $this->guard()->logout();

        $request->session()->invalidate();

        return $this->loggedOut($request) ?: redirect('/login');
    }

    public function authenticated(Request $request, $user)
    {
        /*$role_user = RoleUser::where('user_id',$user->id)->first();
        $role = Role::where('id',$role_user->role_id)->first();

        if ($user->status ==0 || $role->status ==0){
            Session::flush();
            $this->guard()->logout();

            $request->session()->invalidate();

            return $this->loggedOut($request) ?: redirect('/login')->with(["message"=>"Usuario Desactivado"]);
        }*/

        return redirect('/home');
    }
}
