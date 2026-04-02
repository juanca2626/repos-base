@extends('layouts.app')
@section('content')
    <section class="page-board">
        <div class="container">
            <div class="tabs" style="padding-left:0px !important; padding-right:0px !important;">
                <ul class="nav nav-primary text-center">
                    <li class="nav-item" style="width:33.3% !important;">
                        <a v-bind:class="[(tab == 'access-link') ? 'active' : '', 'nav-link' ]" href="javascript:;" @click="toggleView('access-link')"><i class="icon-bar-chart"></i>Reporte de ingreso a links</a>
                    </li>
                    <li class="nav-item" style="width:33.3% !important;">
                        <a v-bind:class="[(tab == 'files') ? 'active' : '', 'nav-link' ]" href="javascript:;" @click="toggleView('files')"><i class="icon-bar-chart"></i>Reporte de Reservas</a>
                    </li>
                </ul>
                <hr>
            </div>
        </div>
        <!---------- Mis pendientes ---------->
        <div class="reporte-area" v-if="tab == 'access-link'">
            <cosig-access-link-component />
        </div>
        <!-------- End Mis pendientes -------->
        <!-------- Reporte de pedidos -------->
        <div class="reporte-cliente" v-if="tab == 'files'">
            <cosig-files-component />
        </div>
        <!-------- End Repor. Pedidos -------->
    </section>
@endsection
@section('css')
    <link href="//cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css" rel="stylesheet">
    <style type="text/css">
        /* Helpers */
        .fecha { width: 250px }
        .table th { font-size: 12px }
        .table-responsive { margin: 20px 0; }
        .dataTables_wrapper input[type="search"] {
            border: 1px solid;
            padding: 7px;
        }
        .dataTables_wrapper .dataTables_length { padding-top: 7px; }
    </style>
@endsection
@section('js')
    <script src="//cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
    <script>
        new Vue({
            el: '#app',
            data: {
                tab: 'access-link',
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