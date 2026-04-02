@extends('layouts.app')
@section('content')
    <section class="page-board">
        <div class="container">
            <h2 class="mb-5">Reporte de ingreso por fechas</h2>
        </div>
        <stats-login v-bind:translations="translations"></stats-login>
    </section>
@endsection

@section('js')
    <script src="//cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
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
                localStorage.setItem('bossFlag', '{{ $bossFlag }}')
            },
            mounted() {
                this.setTranslations()
            },
            computed: {

            },
            methods: {
                setTranslations(){
                    axios.get(baseURL+'translation/'+localStorage.getItem('lang')+'/slug/stats').then((data) => {
                        this.translations = data.data
                    })
                }
            }
        })
    </script>
@endsection
