@extends('layouts.app')
@section('content')
<section class="page-board">
    <serie v-bind:translations="translations"></serie>
</section>
@endsection

@section('css')
<style>
    .nav-item>.nav-link {
        padding: 10px 15px;
    }

    .nav-item>.nav-link.active {
        background-color: #f6f6f6;
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
                messages: {},
                validations: {}
            }
        },
        mounted() {
            this.setTranslations()
        },
        methods: {
            setTranslations() {
                axios.get(baseURL + 'translation/' + localStorage.getItem('lang') + '/slug/master_sheet')
                    .then((data) => {
                        this.translations = data.data
                    })
            },
        }
    })
</script>
@endsection