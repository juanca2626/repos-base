@extends('layouts.app')
@section('content')
    <section class="page-board">
        <div class="container p-0">
            <div class="tabs">
                <ul class="nav nav-tabs">
                    <li class="nav-item col p-0">
                        <a v-bind:class="[(tab == 'area') ? 'active' : '', 'nav-link', 'py-3', 'text-center']"
                            href="javascript:;" @click="toggleView('area')">
                            <i class="icon-bar-chart"></i>Reporte por área
                        </a>
                    </li>
                    <li class="nav-item col p-0">
                        <a v-bind:class="[(tab == 'customer') ? 'active' : '', 'nav-link', 'py-3', 'text-center']"
                            href="javascript:;" @click="toggleView('customer')">
                            <i class="icon-bar-chart"></i>Reporte por cliente
                        </a>
                    </li>
                    <li class="nav-item col p-0">
                        <a v-bind:class="[(tab == 'ranking') ? 'active' : '', 'nav-link', 'py-3', 'text-center']"
                            href="javascript:;" @click="toggleView('ranking')">
                            <i class="icon-bar-chart"></i>Ranking por mes y acumulado
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <!---------- Mis pendientes ---------->
        <div class="reporte-area" v-show="tab == 'area'">
            <area-component></area-component>
        </div>
        <!-------- End Mis pendientes -------->
        <!-------- Reporte de pedidos -------->
        <div class="reporte-cliente" v-show="tab == 'customer'">
            <customer-component></customer-component>
        </div>
        <!-------- End Repor. Pedidos -------->
        <!-------- Reporte de facturacion -------->
        <div class="reporte-ranking" v-show="tab == 'ranking'">
            <ranking-component></ranking-component>
        </div>
        <!-------- End Repor. facturacion -------->
    </section>
@endsection
@section('css')
    <link href="//cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css" rel="stylesheet">
    <style type="text/css">
        /* Helpers */
        .table th { font-size: 12px }
        .table-responsive { margin: 20px 0; }
        .dataTables_wrapper input[type="search"] {
            border: 1px solid;
            padding: 7px;
        }
        .dataTables_wrapper .dataTables_length { padding-top: 7px; }
        .vs--searchable .vs__selected-options .vs__selected {
            width:100%!important;
            max-width:100%!important;
        }
        .vs--searchable {
            padding: 0 10px!important;
        }
        .vs--disabled {
            background-color:rgb(248, 248, 248)!important;
        }
    </style>
@endsection
@section('js')
    <script src="//cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
    <script>
        new Vue({
            el: '#app',
            data: {
                tab: 'area',
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
