@extends('layouts.app')
@section('content')
    <section class="page-board">
        <customer-card v-bind:translations="translations"></customer-card>
    </section>
@endsection

@section('css')
    <style>
        .nav-item > .nav-link {
            padding:10px 15px;
        }
        .nav-item > .nav-link.active {
            background-color:#f6f6f6;
        }
    </style>
@endsection

@section('js')
    <!-- script src="//cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script -->
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
                setTranslations(){
                    /*
                    axios.get(baseURL+'translation/'+localStorage.getItem('lang')+'/slug/customers').then((data) => {
                        this.translations = data.data
                    })
                     */
                }
            }
        })
    </script>
@endsection
