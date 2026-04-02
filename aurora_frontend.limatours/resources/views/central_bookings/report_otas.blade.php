@extends('layouts.app')
@section('content')
    <section class="page-central">
        <div class="container">
            <div class="row">
                <div id="_overlay"></div>
                <nav-central-bookings></nav-central-bookings>
                <div class="col-12 mb-5">
                    <form class="form">
                        <!-- Opciones -->
                        <div class="mt-4 mb-3 d-flex justify-content-around">
                            <div class="">
                                <label class="col-form-label">Desde</label>
                                <date-picker
                                    v-bind:disabled="loading_button"
                                    class="form-control"
                                    :config="datePickerFromOptions"
                                    id="date" autocomplete="off"
                                    name="date" ref="datePicker"
                                    v-model="date_from">
                                </date-picker>
                            </div>
                            <div class="">
                                <label class="col-form-label">Hasta</label>
                                <date-picker
                                    v-bind:disabled="loading_button"
                                    class="form-control"
                                    :config="datePickerToOptions"
                                    id="date" autocomplete="off"
                                    name="date" ref="datePicker"
                                    v-model="date_to">
                                </date-picker>
                            </div>
                        </div>
                        <hr>
                        <!-- Filtros -->
                        <div class="">
                            <div class="text-right">
                                <button @click="search()" v-bind:disabled="loading_button"
                                    class="btn btn-primary w-25" type="button">
                                    <i class="fa fa-search mr-2"></i> Buscar
                                </button>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </form>
                </div>

                <div class="col-12 my-5" style="margin-top: 20px;">
                    <ul class="list-group">
                        <li class="list-group-item">Total Reservas: 
                            @{{ total_reserves }}  (100%)</li>
                        <li class="list-group-item">Reservas con Files: 
                            @{{ total_reserve_with_files }} (@{{ percentage_total_reserve_with_files }}%)</li>
                        <li class="list-group-item">Reservas sin Files: 
                            @{{ total_reserve_no_files }} (@{{ percentage_total_reserve_no_files }}%)</li>
                    </ul>
                </div>

                <h3 class="text-center" v-if="reserve_no_files.length > 0">Listado de reservas sin file</h3>

                <table class="table table-bordered" v-if="reserve_no_files.length > 0">
                    <thead>
                        <th>OTA</th>
                        <th>SERVICIO</th>
                        <th>FECHA DE INICIO</th>
                        <th>PASAJERO</th>
                        <th>AGENTE</th>
                    </thead>
                    <tbody>
                        <tr v-for="reserve in reserve_no_files">
                            <td>@{{ reserve.channel_name }}</td>
                            <td>@{{ reserve.description }}</td>
                            <td>@{{ reserve.start_date }}</td>
                            <td>@{{ reserve.passenger }}</td>
                            <td>@{{ reserve.agent }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection
@section('js')
    <script>
        new Vue({
            el: '#app',
            data: {
                loading_button: false,
                date_from:'',
                date_to:'',
                total_reserves:0,
                total_reserve_with_files:0,
                percentage_total_reserve_with_files:0,
                total_reserve_no_files:0,
                percentage_total_reserve_no_files:0,
                reserve_no_files:[],
                datePickerFromOptions: {
                    format: 'DD/MM/YYYY',
                    useCurrent: false,
                    locale: localStorage.getItem('lang')
                },
                datePickerToOptions: {
                    format: 'DD/MM/YYYY',
                    useCurrent: false,
                    locale: localStorage.getItem('lang')
                },
            },
            created: function () {
                this.date_from = moment().format('DD/MM/YYYY')
            },
            mounted() {

            },
            computed: {
            },
            methods: {
                search: function() {
                    this.loading_button = true

                    this.total_reserves = 0
                    this.total_reserve_with_files = 0
                    this.percentage_total_reserve_with_files = 0
                    this.total_reserve_no_files = 0
                    this.percentage_total_reserve_no_files = 0
                    this.reserve_no_files = []

                    axios.post('/api/generic_otas/report',{date_from:this.date_from,date_to:this.date_to})
                        .then((result) => {
                            this.loading_button = false

                            if(result.data.type == 'success')
                            {
                                this.total_reserves = result.data.total_files
                                this.total_reserve_with_files = result.data.total_reserve_with_files
                                this.percentage_total_reserve_with_files = result.data.percentage_total_reserve_with_files.toFixed(2)
                                this.total_reserve_no_files = result.data.total_reserve_no_files
                                this.percentage_total_reserve_no_files = result.data.percentage_total_reserve_no_files.toFixed(2)
                                this.reserve_no_files = result.data.reserve_no_files
                            }
                            else
                            {

                            }
                        })
                        .catch((e) => {
                            this.loading_button = false
                            console.log(e)
                    })
                }
            }
        })
    </script>
@endsection
@section('css')
    <style>
        .custom-control-input:checked ~ .custom-control-label::before {
            border-color: #3ea662;
            background-color: #3a9d5d;
        }
        .v-select input{
            height: 25px;
        }
        .btn-search{
            margin-top: 19px;
        }
        .right, .btn-search{
            float: right;
        }
        .modal-backdrop {
            background-color: #00000052;
        }
        .canSelectText{
            user-select: text;
        }
        .rooms-table{
            padding: 5px;
        }
        .rowTitle{
            text-align: center;
            font-weight: 800;
            border: solid 1px #8a9b9b;
            margin: 3px;
            border-radius: 6px;
            margin-right: 35px;
        }
        .rowTitleChoosed-N{
            background: #343a40;
            color: #ffffff;
        }
        .rowTitleChoosed-C{
            background: #ffba66;
        }
        .datepickerbutton{
            z-index: 1 !important;
        }
        .check_true{
            color: #04bd12;
        }
        .cursor-pointer{
            cursor: pointer;
        }

        .check_false:hover, .check_true:hover{
            opacity: 0.7;
            cursor: pointer;
        }
        .btn-nrofile{
            float: left;
            background: #ffe8e8;
            margin-top: 5px;
            color: #b24343;
        }
        .btn-estado-danger{
            color: white !important;
        }
    </style>
@endsection
