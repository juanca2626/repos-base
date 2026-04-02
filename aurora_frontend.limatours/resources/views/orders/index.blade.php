@extends('layouts.app')
@section('content')
    <section class="page-board">
        <div class="container">
            <div class="tabs" style="padding-left:0px !important; padding-right:0px !important;">
                <ul class="nav nav-primary text-center">
                    <li class="nav-item" style="width:33.3% !important;">
                        <a v-bind:class="[(tab == 'response-time') ? 'active' : '', 'nav-link' ]" href="javascript:;"
                           @click="toggleView('response-time')">
                            <i class="icon-chevron-down"></i>
                            @{{ translations.label.response_times }}
                        </a>
                    </li>
                    <li class="nav-item" style="width:33.3% !important;">
                        <a v-bind:class="[(tab == 'unspecified-orders') ? 'active' : '', 'nav-link' ]"
                           href="javascript:;" @click="toggleView('unspecified-orders')">
                            <i class="icon-bar-chart"></i>
                            @{{ translations.label.unspecified_orders_estimated_amount }}
                        </a>
                    </li>
                    <!-- li class="nav-item" style="width:33.3% !important;">
                        <a v-bind:class="[(tab == 'executive-report') ? 'active' : '', 'nav-link' ]"
                        href="javascript:;" @click="toggleView('executive-report')">
                        <i class="icon-activity"></i>
                        @{{ translations.label.executive_report }}
                        </a>
                    </li -->
                </ul>
                <hr>
            </div>
        </div>

        <component v-if="!loading" ref="template" v-bind:is="template"
                   v-bind:options="{ translations: translations, dateRange: dateRange }">
        </component>
    </section>
@endsection

@section('css')
    <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">
    <link href="//cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css" rel="stylesheet">
    <style type="text/css">
        /* Helpers */
        .fecha { width: 250px }
        .table th, .table td, .dropdown-menu, .popover { font-size: 12px }
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
                loading: true,
                tab: '{{ $format }}',
                template: 'response-time-component',
                teams: [],
                executives: [],
                quantity: 0,
                quantityTeams: 0,
                products: [],
                quantityProducts: 0,
                translations: {
                    label: {},
                    btn: {}
                },
                dateRange: {
                    'startDate': '{!! $date['startDate'] !!}',
                    'endDate': '{!! $date['endDate'] !!}'
                }
            },
            created: function () {

            },
            mounted() {
                this.setTranslations()
            },
            computed: {

            },
            methods: {
                setTranslations(){
                    this.loading = true
                    axios.get(baseURL+'translation/'+localStorage.getItem('lang')+'/slug/dashboard')
                        .then((data) => {
                        this.translations = data.data
                        this.loading = false
                    })
                },
                toggleView: function (_tab) {
                    this.template = _tab + '-component'
                    this.tab = _tab
                }
            }
        })
    </script>
@endsection
