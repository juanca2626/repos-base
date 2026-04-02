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
                        @if (session('message'))
                            <div class="alert alert-danger">
                                {{ session('message') }}
                            </div>
                        @endif
                        <form method="POST" action="{{ route('login') }}" name="login_form">
                            @csrf

                            <a v-show="!show_invited" class="btn btn-link mb-4" href="javascript:;" @click="show_login_guest()">
                                < Login as a guest
                            </a>

                            <span v-show="show_invited" style="color: white">Hello, please enter your name:</span>
                            <br v-show="show_invited">
                            <br v-show="show_invited">

                            <div class="form-group" v-show="!show_invited" >
                                <input id="code" type="text" class="form-control @error('code') is-invalid @enderror"
                                       name="code" v-model="code" required autocomplete="code" autofocus
                                       placeholder="User">
                                @error('code')
                                <div class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </div>
                                @enderror
                            </div>

                            <div class="form-group" v-show="!show_invited">
                                <input id="password" type="password"
                                       class="form-control @error('password') is-invalid @enderror" name="password"
                                       required
                                       autocomplete="current-password" v-model="password" placeholder="Password">
                                @error('password')
                                <div class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </div>
                                @enderror
                            </div>

                            <div class="alert alert-danger text-center" v-if="validateLogin">
                                <span style="font-size: 14px;text-align: center;">Incorrect username / password</span>
                            </div>

                            <div v-show="show_invited" class="form-group">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                                       name="name" v-model="name" required autocomplete="off"
                                       placeholder="Name">
                                @error('name')
                                <div class="invalid-feedback" role="alert">
                                    <strong>Invalid name</strong>
                                </div>
                                @enderror
                            </div>

                            <div class="custom-control custom-checkbox" v-show="!show_invited">
                                <input type="checkbox" class="custom-control-input" name="remember" id="remember"
                                    {{ old('remember') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="remember">{{ __('Remember Me') }}</label>
                            </div>

                            <button type="submit" class="btn btn-primary mb-4" @click.prevent="login($event)" v-show="!show_invited">
                                {{ __('Login') }} <span v-if="loading"><i class="fa fa-spinner fa-spin"></i></span>
                            </button>

                            <button type="submit" class="btn btn-primary mb-4" @click.prevent="login($event)" v-show="show_invited">
                                Login as a guest <span v-if="loading"><i class="fa fa-spinner fa-spin"></i></span>
                            </button>

                            @if (Route::has('password.request'))
                                <a v-show="!show_invited" class="btn btn-link mb-4" href="{{ route('password.request') }}">
                                    {{ __('Forgot Your Password?') }}
                                </a>
                            @endif

                            <a v-show="show_invited" class="btn btn-link mb-4" href="javascript:;" @click="show_login_normal()">
                                Start with my account
                            </a>

                        </form>
                    </div>
                    <div class="login_footer">
                        <img src="{{asset('images/logo/logo_mini.jpg')}}">
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/js-cookie/3.0.1/js.cookie.min.js" integrity="sha512-wT7uPE7tOP6w4o28u1DN775jYjHQApdBnib5Pho4RB0Pgd9y7eSkAV1BTqQydupYDB9GBhTcQQzyNMPMV3cAew==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script>
        new Vue({
            el: '#app',
            data: {
                loading: false,
                validateLogin: false,
                password: '1nv1t@d0_L1t0',
                code: 'GUEST',
                name: '',
                password_invited: '1nv1t@d0_L1t0',
                code_invited: 'GUEST',
                name_backup: 'default',
                show_invited: true,
                TokenKey: window.tokenKey,
                UserKey: window.userKey,
                Domain: { domain: window.domain },
            },
            created: function () {
            },
            mounted () {
                const cookiesAuth = this.getDataCookieTokenAndUser()
                if (cookiesAuth.token && cookiesAuth.userId) {
                    this.loading = true

                    this.token = cookiesAuth.token
                    this.userId = cookiesAuth.userId

                    window.axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.token

                    let {
                        data
                    } = await axios.get('api/me')

                    data['access_token'] = this.token;
                    this.setDataLocalStorageLogin(data);

                    let {
                        data: dataUser
                    } = await axios.get('api/user')

                    this.setDataLocalStorageUser(dataUser);

                    this.loading = false

                    document.login_form.submit()

                }
            },
            methods: {
                show_login_normal(){
                    this.password = ""
                    this.code = ""
                    this.name = this.name_backup
                    this.show_invited = false
                },
                show_login_guest(){
                    this.password = this.password_invited
                    this.code = this.code_invited
                    this.name = ""
                    this.show_invited = true
                },
                hasPermission (data, permission) {
                    const permission_split = permission.split('.')
                    const subject = permission_split[0]
                    const action = permission_split[1]
                    if (data.subject === subject) {
                        if (data.actions.indexOf(action) > -1) {
                            return true
                        }
                    }
                    return false
                },
                login: function (event) {
                    this.loading = true

                    if(this.code == '' || this.password == ''){
                        document.login_form.submit()
                    }

                    axios.post('api/login', {
                        code: this.code,
                        password: this.password,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    })
                        .then(response => {
                            this.setDataCookieTokenAndUser(response.data.access_token, response.data.user_id)
                            this.setDataLocalStorage(response.data)

                            const cookiesAuth = this.getDataCookieTokenAndUser()

                            window.axios.defaults.headers.common['Authorization'] = 'Bearer ' + cookiesAuth.token

                            axios.get('api/user').then(response => {
                                localStorage.setItem('user_id', response.data.id)
                                if (response.data.user_type_id === '4') {
                                    if (response.data.client_seller.client_id === '' || response.data.client_seller.client_id <= 0) {
                                        alert('El usuario no tiene un cliente asociado')
                                        return false
                                    }
                                    localStorage.setItem('client_id', response.data.client_seller.client_id)
                                    document.login_form.submit()
                                } else {
                                    document.login_form.submit()
                                }
                                this.loading = false
                            })
                                .catch(error => {
                                    console.log(error)
                                    this.loading = false
                                    document.login_form.submit()
                                })
                        })
                        .catch(error => {
                            if (401 === error.response.status) {
                                this.validateLogin = true
                            } else {
                                document.login_form.submit()
                            }
                            this.loading = false
                        })
                },
                setDataLocalStorage(data) {
                    localStorage.setItem('access_token', data.access_token)
                    localStorage.setItem('user_type_id', data.user_type_id)
                    localStorage.setItem('code', data.code.toLowerCase())
                    localStorage.setItem('client_id', '')
                    localStorage.setItem('client_code', '')
                    localStorage.setItem('photo', data.photo)
                    localStorage.setItem('user_id', data.user_id)
                    localStorage.setItem('user_email', '')

                    if( this.show_invited ){
                        localStorage.setItem('name', this.name)
                        let d_ = new Date()
                        let m_ = d_.getMilliseconds()
                        localStorage.setItem('code_guest', data.code.toLowerCase()+'_'+this.name+'_'+m_)
                    } else {
                        localStorage.setItem('name', data.name)
                    }

                    localStorage.setItem('permissions', JSON.stringify(data.permissions))

                    if (localStorage.getItem('lang') === null) {
                        localStorage.setItem('lang', 'en')
                    }
                },
                setDataCookieTokenAndUser(token, userId) {
                    Cookies.set(this.TokenKey, token, this.Domain)
                    Cookies.set(this.UserKey, userId, this.Domain)
                },
                getDataCookieTokenAndUser() {
                    return {
                        'token' : Cookies.get(this.TokenKey),
                        'userId' : Cookies.get(this.UserKey),
                    }
                },
            }
        })
    </script>
@endsection
@section('css')
    @include('auth.css.auth_css')
@endsection
