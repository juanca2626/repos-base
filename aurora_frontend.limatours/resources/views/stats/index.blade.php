@extends('layouts.app')
@section('content')
    <section class="page-board">
        <stats-list v-bind:translations="translations"></stats-list>
    </section>
@endsection

@section('css')
    <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">
    <link href="//cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css" rel="stylesheet">
    <style type="text/css">
        /* Helpers */
        .fecha { width: 33% }
        .vs--searchable .vs__selected-options .vs__selected { width:100%!important; }
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
                teams: [],
                executives: [],
                quantity: 0,
                quantityTeams: 0,
                products: [],
                quantityProducts: 0,
                translations: {
                    label: {},
                    btn: {}
                }
            },
            created: function () {
                localStorage.setItem('bossFlag', '{{ $bossFlag }}')
            },
            mounted() {
                this.setTranslations()
            },
            computed: {

            },
            methods: {
                setTranslations(){
                    /*
                    axios.get(baseURL+'translation/'+localStorage.getItem('lang')+'/slug/stats').then((data) => {
                        this.translations = data.data
                    })
                    */
                }
            }
        })
    </script>
@endsection
