@extends('layouts.app')
@section('content')
    <section class="page-board">
        <pending-statements-component v-bind:data="{ translations: translations, bossFlag: bossFlag }"></pending-statements-component>
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
                bossFlag: "{!! $bossFlag !!}"
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
