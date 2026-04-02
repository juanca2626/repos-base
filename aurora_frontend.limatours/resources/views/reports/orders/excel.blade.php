@extends('layouts.app')
@section('content')
    <section class="page-board">
        <order-excel v-bind:translations="translations"></order-excel>
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
                    axios.get(baseURL+'translation/'+localStorage.getItem('lang')+'/slug/orders').then((data) => {
                        this.translations = data.data
                    })
                }
            }
        })
    </script>
@endsection
