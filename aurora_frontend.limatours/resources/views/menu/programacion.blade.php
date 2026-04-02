@extends('layouts.app')
@section('content')
    <iframe :src="'http://extranet.litoapps.com/migration-facile/programacion/?u='+code+'&t=u&l='+lang"
            frameborder="0" width="100%" height="100%" style="height: 1200px;"></iframe>
@endsection
@section('js')
    <script>
      new Vue({
        el: '#app',
        data: {
          code: localStorage.getItem('code'),
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
