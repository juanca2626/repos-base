@extends('layouts.app')

@section('content')
    <div class="login">
        <img class="bk-image" src="{{asset('images/bg-login.jpg')}}">
        <div class="row no-gutters justify-content-center align-items-center">
            <div class="col-md-6">
                <div class="login-box">
                    <div class="login_header">
                        <img src="{{asset('images/logo/logo-aurora.png')}}">
                    </div>
                    <div class="login_body">
                        <form method="POST" action="{{ route('password.update') }}" name="login_form">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">
                            <div class="form-group">
                                <label for="email"
                                       class="col-md-12 col-form-label text-md-left login_label">{{ __('E-Mail Address') }}</label>
                                <div class="col-md-12">
                                    <input id="email" type="email"
                                           class="form-control @error('email') is-invalid @enderror" name="email"
                                           value="{{ $email ?? old('email') }}" required autocomplete="email"
                                           autofocus>

                                    @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="password"
                                       class="col-md-12 col-form-label text-md-left login_label">{{ __('Password') }}</label>
                                <div class="col-md-12">
                                    <input id="password" type="password"
                                           class="form-control @error('password') is-invalid @enderror"
                                           name="password" required autocomplete="new-password">

                                    @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="password-confirm"
                                       class="col-md-12 col-form-label text-md-left login_label">{{ __('Confirm Password') }}</label>

                                <div class="col-md-12">
                                    <input id="password-confirm" type="password" class="form-control"
                                           name="password_confirmation" required autocomplete="new-password">
                                </div>
                            </div>
                            <div class="form-group mt-5">
                                <div class="col-md-12 text-center">
                                    <button type="submit" class="btn btn-primary" style="width: 100%">
                                        {{ __('Reset Password') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="login_footer mt-5">
                        <img src="{{asset('images/logo/logo_mini.jpg')}}" alt="LimaTours">
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
