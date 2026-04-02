<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Mail\ResetPassword;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ResetPasswordController extends Controller
{
    public function sendEmail(Request $request)
    {
        if (!$this->validateEmail($request->email)) {
            return $this->failedResponse();
        }
        $this->send($request->email,$request->lang);

        return $this->successResponse();
    }

    public function validateEmail($email)
    {
        return !!User::where('email', $email)->first();
    }

    public function failedResponse()
    {
        return response()->json(['message' => 'Email could not be sent to this email address.', 'status' => 500]);
    }

    public function send($email,$lang)
    {
        $token = $this->createToken($email);
        $time = config('auth.passwords.users.expire');
        $link = url("/#/reset-password/" . $token);

        Mail::to($email)->send(new ResetPassword($link, $time,$lang));
    }

    public function createToken($email)
    {
        $length = 60;

        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $token = '';

        for ($i = 0; $i < $length; $i++) {
            $token .= $characters[rand(0, $charactersLength - 1)];
        }

        $this->saveToken($token, $email);

        return $token;
    }

    public function saveToken($token, $email)
    {
        DB::table('password_resets')->where('email', $email)->delete();

        DB::table('password_resets')->insert([
            'email' => $email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);
    }

    public function successResponse()
    {
        return response()->json([
            'data' => 'Reset Email is send successfully, please check your inbox',
            'status' => 200
        ]);
    }

    public function callResetPassword(ChangePasswordRequest $request)
    {
        return $this->getPasswordResetTabletRow($request)->count() > 0 ? $this->changePassword($request) : $this->tokenNotFoundResponse();
    }

    private function getPasswordResetTabletRow($request)
    {
        return DB::table('password_resets')->where([
            'email' => $request->email,
            'token' => $request->token
        ]);
    }

    private function changePassword($request)
    {
        $user = User::whereEmail($request->email)->first();

        $user->update([
            'password' => bcrypt($request->password)
        ]);

        $this->getPasswordResetTabletRow($request)->delete();

        return response()->json(['message' => 'Password Successfully Changed', 'status' => 200]);
    }

    private function tokenNotFoundResponse()
    {
        return response()->json(['message' => 'Token and Email is incorrect', 'status' => 500]);
    }
}
