@extends('layouts.app')
@section('content')
@endsection
@section('css')
    <style>

    </style>
@endsection
@section('js')
    <script>
        new Vue({
        el: '#app',
        data: {
            code:localStorage.getItem('code')
        },
        created: function () {
            this.code = localStorage.getItem('code')
        },
        mounted() {

        },
        computed: {},
        methods: {}
        })
    </script>
@endsection
