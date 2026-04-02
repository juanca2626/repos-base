@extends('layouts.app')
@section('content')
    <section class="page-board">
        <master-sheet-edit :translations="translations" master_sheet_id="{{ Request::route('master_sheet_id')}}"></master-sheet-edit>
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
        // import store from 'vuex'
        new Vue({
            el: '#app',
            data: {
                translations: {
                    label: {
                        attach_file : 'Empty'
                    },
                    messages: {},
                    validations: {}
                }
            },
            mounted() {
                this.setTranslations()
            },
            methods: {
                setTranslations () {
                    axios.get(baseURL + 'translation/' + localStorage.getItem('lang') + '/slug/master_sheet').then((data) => {
                        this.translations = data.data
                    })
                },
            }
        })
    </script>
@endsection
