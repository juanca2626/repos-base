@extends('layouts.app')
@section('content')
    <iframe src="https://destinationservices.bokun.io/extranet/login" frameborder="0" width="100%" height="100%" style="height: 780px;"></iframe>
@endsection
@section('js')
    <script>
        new Vue({
            el: '#app',
            data: {
                code:localStorage.getItem('code')
            },
            created: function () {

            },
            mounted() {

            },
            computed: {},
            methods: {}
        })
    </script>
@endsection
