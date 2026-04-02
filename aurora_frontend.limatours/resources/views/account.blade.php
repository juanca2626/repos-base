@extends('layouts.app')
@section('content')
    <section class="page-board">
        <div class="container account">
            <div class="tabs">
                <ul class="nav nav-primary text-center">
                    <li class="nav-item">
                        <a v-bind:class="[(tab == 'profile') ? 'active' : '', 'nav-link' ]" href="javascript:;" @click="toggleView('profile')"><i class="icon-user"></i>Perfil</a>
                    </li>
                    <li class="nav-item">
                        <a v-bind:class="[(tab == 'password') ? 'active' : '', 'nav-link' ]" href="javascript:;" @click="toggleView('password')"><i class="icon-lock"></i>Contraseña</a>
                    </li>
                </ul>
                <hr>
            </div>

            <div class="pendientes container" v-show="tab == 'profile'">
                <profile-component></profile-component>
            </div>

            <div class="reporte-pedidos align-content-center" v-show="tab == 'password'">
                <password-component></password-component>
            </div>
        </div>
    </section>
@endsection
@section('css')
@endsection
@section('js')
    <script>
        new Vue({
            el: '#app',
            data: {
                tab: 'profile',
            },
            created: function () {

            },
            mounted: function() {

            },
            computed: {

            },
            methods: {
                toggleView: function (_tab) {
                    this.tab = _tab
                }
            }
        });
    </script>
@endsection
