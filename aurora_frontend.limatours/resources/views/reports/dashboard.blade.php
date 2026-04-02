@extends('layouts.app')
@section('content')
    <section class="page-board">
        <reports-general v-bind:options="{ translations:translations, format: format, dateRange: dateRange }"></reports-general>
    </section>
@endsection

@section('css')
    <style type="text/css">
        /* Helpers */
        .fecha { width: 250px }
    </style>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">
@endsection

@section('js')
    <script>
        new Vue({
            el: '#app',
            data: {
                translations: {
                    label: {},
                    btn: {}
                },
                format: 1
                ,dateRange: {
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
                setTranslations() {
                    axios.get(baseURL+'translation/'+localStorage.getItem('lang')+'/slug/reports').then((data) => {
                        this.translations = data.data
                    })
                }
            }
        })
    </script>
@endsection
