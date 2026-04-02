@extends('layouts.app')
@section('content')
    <iframe :src="" frameborder="0" width="100%" height="100%" ></iframe>
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
