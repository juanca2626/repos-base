@extends('layouts.app')

@section('content')
    <div class="login" v-if="show_login">
        <img class="bk-image" src="{{asset('images/bg-login.jpg')}}">
        <div class="row no-gutters justify-content-center align-items-center">
            <div class="col-md-6">
                <div class="login-box">
                    <div class="login_header">
                        <img src="{{asset('images/logo/logo-aurora.png')}}" alt="Aurora">
                    </div>
                    <div class="login_body">
                        @if (session('message'))
                            <div class="alert alert-danger">
                                {{ session('message') }}
                            </div>
                        @endif
                        <form method="POST" action="{{ route('login') }}" name="login_form">
                            @csrf


                            <template v-if="userId">
                                <input type="hidden" v-model="userId" name="user_id">
                            </template>
                            <template v-else>

                                <div class="form-group">
                                    <input id="code" type="text"
                                           class="form-control @error('code') is-invalid @enderror" name="code"
                                           value="{{ old('code') }}" required autocomplete="code" autofocus
                                           placeholder="User">
                                    @error('code')
                                    <div class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <input id="password" type="password"
                                           class="form-control @error('password') is-invalid @enderror" name="password"
                                           required autocomplete="current-password" v-model="password"
                                           placeholder="Password">
                                    @error('password')
                                    <div class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                    @enderror
                                </div>

                                <div class="alert alert-danger text-center" v-if="validateLogin">
                                    <span
                                        style="font-size: 14px;text-align: center;">Incorrect username / password</span>
                                </div>

                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" name="remember"
                                           id="remember" {{ old('remember') ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="remember">{{ __('Remember Me') }}</label>
                                </div>

                                <button type="submit" class="btn btn-primary mb-2" @click.prevent="login($event)"
                                        style="width: 100%">
                                    {{ __('Login') }} <span v-if="loading"><i class="fa fa-spinner fa-spin"></i></span>
                                </button>

                                @if (Route::has('password.request'))
                                    <a class="btn btn-link mb-2" href="{{ route('password.request') }}">
                                        {{ __('Forgot Your Password?') }}
                                    </a>
                                @endif

                            </template>


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
@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/js-cookie/3.0.1/js.cookie.min.js"
            integrity="sha512-wT7uPE7tOP6w4o28u1DN775jYjHQApdBnib5Pho4RB0Pgd9y7eSkAV1BTqQydupYDB9GBhTcQQzyNMPMV3cAew=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script>
        new Vue({
            el: "#app",
            data: {
                show_login: false,
                loading: true,
                validateLogin: false,
                password: "",
                userId: null,
                token: null,
                TokenKey: window.tokenKey,
                TokenKeyCognito: window.tokenKeyCognito,
                UserKey: window.userKey,
                UserType: window.userType,
                UserName: window.userName,
                UserClientId: window.userClientId,
                UserDisableReservation: window.userDisableReservation,
                Domain: {
                    domain: window.domain
                },
                source: "FRONTED"

            },
            created: function() {
                localStorage.removeItem("search_parameters_packages");
                localStorage.removeItem("parameters_packages_details");
                localStorage.removeItem("package_reservation");
                localStorage.removeItem("client_code");
                localStorage.removeItem("permissions");
                localStorage.removeItem("client_name");
                localStorage.removeItem("client_id");
                localStorage.removeItem("client_allow_direct_passenger_create");
                localStorage.removeItem("client_disable_reservation");
                localStorage.removeItem("user_type_id");
                localStorage.removeItem("service_selected_general_double");
                localStorage.removeItem("name");
                localStorage.removeItem("access_token");
                localStorage.removeItem("photo");
                localStorage.removeItem("user_id");
                localStorage.removeItem("code");
                localStorage.removeItem("user_email");
                localStorage.removeItem("market_id");
                localStorage.removeItem("market_name");
            },
            async mounted() {
                const cookiesAuth = this.getDataCookieTokenAndUser();
                if (cookiesAuth.token && cookiesAuth.userId && cookiesAuth.token_cognito) {
                    this.show_login = true;
                    this.loading = true;

                    this.token = cookiesAuth.token;
                    this.token_cognito = cookiesAuth.token_cognito;
                    this.userId = cookiesAuth.userId;

                    window.axios.defaults.headers.common["Authorization"] = "Bearer " + this.token;
                    window.axios.defaults.headers.common["Accept"] = "application/json";

                    try {

                        let { data } = await axios.get(`api/me?source=${this.source}`);
                        console.log("api/me", data);

                        if (data["user_type_id"] == 4) {
                            isNewUser = "false";

                            if (data["count_login"] == 1 || data["count_login"] == null) {
                                isNewUser = "true";
                            }

                            dataLayer.push({
                                event: "login",
                                new_client: isNewUser,
                                demo: "false"
                            });

                        }

                        data["access_token"] = this.token;

                        this.setDataLocalStorageLogin(data);

                        let { data: dataUser } = await axios.get("api/user");

                        this.setDataLocalStorageUser(dataUser);

                        this.loading = false;

                        setTimeout(() => {
                            document.login_form.submit();
                        }, 500);


                    } catch (error) {

                        if (error.response && error.response.status == 401) {
                            Cookies.remove(this.TokenKey, this.Domain);
                            Cookies.remove(this.UserKey, this.Domain);
                            Cookies.remove(this.UserType, this.Domain);
                            Cookies.remove(this.UserName, this.Domain);
                            Cookies.remove(this.UserClientId, this.Domain);
                            Cookies.remove(this.UserDisableReservation, this.Domain);
                            Cookies.remove(this.TokenKeyCognito, this.Domain);
                        } else if (error.response && error.response.status == 500 && error.response.data) {
                            if (error.response.data.error == "Unauthenticated.") {
                                Cookies.remove(this.TokenKey, this.Domain);
                                Cookies.remove(this.UserKey, this.Domain);
                                Cookies.remove(this.UserType, this.Domain);
                                Cookies.remove(this.UserName, this.Domain);
                                Cookies.remove(this.UserClientId, this.Domain);
                                Cookies.remove(this.UserDisableReservation, this.Domain);
                                Cookies.remove(this.TokenKeyCognito, this.Domain);
                            }
                        }

                        window.location.href = window.a3BaseUrl + "login";
                    }

                } else {
                    window.location.href = window.a3BaseUrl + "login";
                }
            },
            methods: {
                hasPermission(data, permission) {
                    const permission_split = permission.split(".");
                    const subject = permission_split[0];
                    const action = permission_split[1];
                    if (data.subject === subject) {
                        if (data.actions.indexOf(action) > -1) {
                            return true;
                        }
                    }
                    return false;
                },
                login: function(event) {
                    this.loading = true;
                    let code = document.getElementById("code").value;

                    if (code == "" || this.password == "") {
                        document.login_form.submit();
                    }

                    axios.post("api/login", {
                        code: code,
                        password: this.password,
                        _token: $("meta[name=\"csrf-token\"]").attr("content")
                    })
                        .then(response => {
                            this.setDataLocalStorageLogin(response.data);
                            this.setDataCookieTokenAndUser(response.data.access_token, response.data.user_id, response.data.user_type_id, response.data.name);
                            const cookiesAuth = this.getDataCookieTokenAndUser();
                            window.axios.defaults.headers.common["Authorization"] = "Bearer " + cookiesAuth.token;
                            axios.get("api/user").then(response => {
                                localStorage.setItem("user_email", response.data.email);

                                if (response.data.user_type_id == "4") {
                                    if (response.data.client_seller.client_id == "" || response.data.client_seller.client_id <= 0) {
                                        alert("El usuario no tiene un cliente asociado");
                                        return false;
                                    }
                                    this.setDataLocalStorageUser(response.data);
                                    document.login_form.submit();
                                } else {
                                    document.login_form.submit();
                                }

                                this.loading = false;
                            })
                                .catch(error => {
                                    console.log(error);
                                    this.loading = false;
                                    document.login_form.submit();
                                });
                        })
                        .catch(error => {
                            if (401 === error.response.status) {
                                this.validateLogin = true;
                            } else {
                                document.login_form.submit();
                            }
                            this.loading = false;
                        });
                },
                setDataCookieTokenAndUser(token, userId, userType, userName) {
                    Cookies.set(this.TokenKey, token, this.Domain);
                    Cookies.set(this.UserKey, userId, this.Domain);
                    Cookies.set(this.UserType, userType, this.Domain);
                    Cookies.set(this.UserName, userName, this.Domain);
                },
                getDataCookieTokenAndUser() {
                    return {
                        "token": Cookies.get(this.TokenKey),
                        "token_cognito": Cookies.get(this.TokenKeyCognito),
                        "userId": Cookies.get(this.UserKey),
                        "userType": Cookies.get(this.UserType),
                        "userName": Cookies.get(this.UserName)
                    };
                },
                setDataLocalStorageLogin(data) {
                    localStorage.setItem("access_token", data.access_token);
                    localStorage.setItem("code", data.code.toLowerCase());
                    localStorage.setItem("user_type_id", data.user_type_id);
                    localStorage.setItem("client_id", "");
                    localStorage.setItem("client_code", "");
                    localStorage.setItem("market_id", "");
                    localStorage.setItem("market_name", "");
                    localStorage.setItem("user_email", data.email);
                    localStorage.setItem("client_name", "");
                    localStorage.setItem("client_allow_direct_passenger_create", "");
                    localStorage.setItem("client_disable_reservation", "");
                    localStorage.setItem("photo", data.photo);
                    localStorage.setItem("name", data.name);
                    localStorage.setItem("rol", data.rol);

                    if (data.permissions) {
                        localStorage.setItem("permissions", JSON.stringify(data.permissions));
                    }

                    localStorage.setItem("user_id", data.id);

                    if (localStorage.getItem("lang") === null) {
                        localStorage.setItem("lang", "en");
                    }
                    Cookies.set(this.UserClientId, "", this.Domain);
                },
                setDataLocalStorageUser(data) {
                    if (data.client_seller) {
                        localStorage.setItem("client_id", data.client_seller.client_id);
                        localStorage.setItem("client_name", data.client_seller.client.name);
                        localStorage.setItem("client_code", data.client_seller.client.code);
                        localStorage.setItem("market_id", data.client_seller.client.markets.id);
                        localStorage.setItem("market_name", data.client_seller.client.markets.name);
                        localStorage.setItem("client_allow_direct_passenger_create", data.client_seller.client.allow_direct_passenger_creation);
                        localStorage.setItem("client_disable_reservation", data.client_seller.disable_reservation);

                        console.log("TRAYENDO DATA..", this.UserDisableReservation)
                        Cookies.set(this.UserDisableReservation, data.client_seller.disable_reservation, this.Domain);
                        Cookies.set(this.UserClientId, data.client_seller.client_id, this.Domain);
                    }

                }
            }
        });
    </script>
@endsection
@section('css')
    @include('auth.css.auth_css')
@endsection
