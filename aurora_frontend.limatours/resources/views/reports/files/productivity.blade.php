@extends('layouts.app')
@section('content')
    <section class="page-board">
        <div class="container">
            <div class="alert alert-warning" v-if="loading">Cargando..</div>
        </div>
        <productivity-component v-if="!loading" v-bind:data="{ 'bossFlag': bossFlag, 'translations': translations, 'dateRange': dateRangeResume }"></productivity-component>
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
                loading: true,
                dateRangeResume: {
                    'startDate': '{!! $date['startDate'] !!}',
                    'endDate': '{!! $date['endDate'] !!}'
                },
                bossFlag: '{{ $bossFlag }}',
                translations: {
                    label: {},
                    btn: {}
                }
            },
            created: function () {
                this.setTranslations()
            },
            mounted: function() {

            },
            computed: {

            },
            methods: {
                setTranslations(){
                    this.loading = true
                    axios.get(baseURL+'translation/'+localStorage.getItem('lang')+'/slug/board').then((data) => {
                        this.loading = false
                        this.translations = data.data
                    }).catch((data) => {
                        this.loading = false
                    })
                }
            }
        });
    </script>
@endsection
