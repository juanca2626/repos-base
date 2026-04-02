@extends('layouts.app')

@section('content')
    <div class="login">
        <img class="bk-image" src="{{asset('images/bg-login.jpg')}}">
        <div class="row no-gutters justify-content-center align-items-center">
            <div class="col-md-6">
                <div class="login-box">
                    <div class="login_header">
                        <img src="{{asset('images/logo/logo-aurora.png')}}" alt="Aurora">
                    </div>
                    {{--                        <div class="card-header">{{ __('Reset Password') }}</div>--}}

                    <div class="login_body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('password.email') }}">
                            @csrf
                            <div class="form-group">
                                {{--                                <label for="email"--}}
                                {{--                                       class="col-md-4 col-form-label text-md-right">{{ __('E-Mail Address') }}</label>--}}

                                <input id="email" type="email"
                                       class="form-control @error('email') is-invalid @enderror" name="email" placeholder="{{ __('E-Mail Address') }}"
                                       value="{{ old('email') }}" required autocomplete="email" autofocus>

                                @error('email')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror

                            </div>

                            <div class="form-group mb-5">

                                <button type="submit" class="btn btn-primary w-100 p-0" style="font-size:15px;">
                                    {{ __('Send Password Reset Link') }}
                                </button>

                            </div>
                        </form>
                    </div>
                    <div class="login_footer">
                        <img src="{{asset('images/logo/logo_mini.jpg')}}" alt="LimaTours">
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
