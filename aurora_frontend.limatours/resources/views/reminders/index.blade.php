@extends('layouts.app')
@section('content')
    <section class="page-board">
        <div class="container">
            <h4>{{ trans('reminders.label.title') }}</h4>
        </div>
        <div class="alert alert-warning" v-if="loading">{{ trans('board.label.loading') }}</div>
        <reminders-component v-if="!loading" v-bind:options="{ translations: translations }"></reminders-component>
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
                translations: {
                    label: {},
                    btn: {}
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
                    axios.get(baseURL+'translation/'+localStorage.getItem('lang')+'/slug/reminders').then((data) => {
                        this.translations = data.data
                        this.loading = false
                    })
                }
            }
        })
    </script>
@endsection
