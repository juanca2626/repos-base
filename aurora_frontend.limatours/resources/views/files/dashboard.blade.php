@extends('layouts.app')
@section('content')
    <section class="page-board">
        <files-dashboard-component v-bind:data="{{ json_encode($data) }}"></files-dashboard-component>
    </section>
@endsection
@section('js')
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
