@extends('layouts.app')
@section('content')
    <multimedia-photos-component></multimedia-photos-component>
    @include('layouts.partials.write_us')
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
	            active: true,
	            pager: {},
	            photos: [],
	            slidePhotos: 0,
            },
            created: function () {

            },
            mounted () {

            },
            methods: {

            }
        })
    </script>
@endsection
