@extends('layouts.app')
@section('content')
    <iframe :src="'http://extranet.litoapps.com/migration/reportes.php?u='+code+'&t=u&l='+lang" frameborder="0"
            width="100%" height="100%" style="height: 1100px;"></iframe>
@endsection
@section('js')
    <script>
      new Vue({
        el: '#app',
        data: {
          code: '',
          lang: 0
        },
        created: function () {
          this.code = localStorage.getItem('code')
          let lang = localStorage.getItem('lang')
          if (lang === 'en') {
            this.lang = 1
          }
        },
        mounted () {

        },
        computed: {},
        methods: {}
      })
    </script>
@endsection
