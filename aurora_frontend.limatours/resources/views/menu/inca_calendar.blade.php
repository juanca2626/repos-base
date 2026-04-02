@extends('layouts.app')
@section('content')
<!-- <section class="page-board">
    <master-sheet v-bind:translations="translations"></master-sheet>
</section> -->

<section class="page-consulting__files">
    <div class="container">
        <div class="motor-busqueda">
            <h2>Calendario Inca</h2>
            <div class="form justify-content-around">
                <div class="form-row d-flex align-items-end">

                    <div class="form-group btn-buscar mx-12">
                        <a :href="downloadExcelText" class="btn btn-primary" @click="search()"><i class="icon-xls"></i> Descargar EXCEL</a>
                    </div>

                </div>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-12">

                <table id="datatableContacts" class="datatable table text-center">
                    <thead>
                        <tr>
                            <th scope="col" class="text-muted">Id</th>
                            <th scope="col" class="text-muted">Nombre</th>
                            <th scope="col" class="text-muted">Fec. Nacimiento</th>
                            <th scope="col" class="text-muted">Email</th>
                            <th scope="col" class="text-muted">Teléfono</th>
                            <th scope="col" class="text-muted">Compañia</th>
                            <th scope="col" class="text-muted">Aniversario</th>
                            <th scope="col" class="text-muted">Fec. Modificación</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        <tr v-for="(v,i) in contacts" :key="i">
                            <td>@{{i+1}}</td>
                            <td>@{{v.name}}</td>
                            <td>@{{v.birthday}}</td>
                            <td>@{{v.email}}</td>
                            <td>@{{v.phone}}</td>
                            <td>@{{v.company}}</td>
                            <td>@{{v.companybirth}}</td>
                            <td>@{{v.log}}</td>
                        </tr>
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</section>
@endsection
@section('css')
<style>

</style>
@endsection
@section('js')
<!-- <script src="{{ asset('js/components/pizza.js')}}"></script> -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.js"></script>

<script>
    new Vue({
        el: '#app',
        data: {
            translations: {
                label: {},
                messages: {},
                validations: {}
            },
            code: localStorage.getItem('code'),
            client_id: localStorage.getItem('client_id'),
            loading: false,
            contacts: [],
            downloadExcelText: `${baseExpressURL}api/v1/incaCalendar/excel`,
        },

        created: function() {

            axios.get(`${baseExpressURL}api/v1/incaCalendar`)
                .then(response => {
                    this.contacts = response.data.data;
                })
                .then(response => {
                    $(document).ready(function() {
                        $('#datatableContacts').DataTable();
                    });
                })
                .catch(error => {
                    console.log(error)
                })

            // setTimeout(() => {
            //     $(document).ready(function() {
            //         $('#xxx').DataTable();
            //     });
            // }, 1000);
        },
        mounted() {
            this.setTranslations()
            this.client_id = localStorage.getItem('client_id')
            switch (localStorage.getItem('lang')) {
                case 'es':
                    this.lang = 0
                    break;
                case 'pt':
                    this.lang = 2
                    break;
                case 'it':
                    this.lang = 3
                    break;
                default:
                    this.lang = 1
                    break;
            }
            this.baseExternalURL = window.baseExternalURL
        },
        computed: {},
        watch: {},
        methods: {
            setTranslations() {}
        }
    })
</script>
@endsection