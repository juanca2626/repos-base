@extends('layouts.app')
@section('content')
    <section class="page-board">
        <masi-logs-component v-bind:data="{{ json_encode($data) }}"></masi-logs-component>
    </section>
@endsection

@section('css')
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

            },
            created: function () {

            },
            mounted: function() {

            },
            computed: {

            },
            methods: {

            }
        });
    </script>
@endsection
