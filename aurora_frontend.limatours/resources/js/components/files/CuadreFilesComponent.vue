<template>
    <div>
        <div class="container">
            <div class="form">
                <div class="form-row justify-content-center">
                    <div class="form-group mx-4 fecha">
                        <label>
                            <strong>Rango de Fechas</strong>
                        </label>
                        <date-range-picker
                            :locale-data="locale_data"
                            :time-picker24-hour="timePicker24Hour"
                            :show-week-numbers="showWeekNumbers"
                            :ranges="false"
                            :auto-apply="true"
                            v-model="dateRange">
                        </date-range-picker>
                        <div class="text-muted mt-2">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="check" v-model="check" id="customer" value="C" />
                                <label class="form-check-label" for="customer">Cliente</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="check" v-model="check" id="qrr" value="QRR">
                                <label class="form-check-label" for="qrr">QRR</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="check" v-model="check" id="qrv" value="QRV">
                                <label class="form-check-label" for="qrv">QRV</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="check" v-model="check" id="kam" value="KAM">
                                <label class="form-check-label" for="kam">KAM</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mx-4" v-if="quantityTeams > 0 && (check == 'QRR' || check == 'QRV')">
                        <label>
                            <strong>Equipo</strong>
                        </label>
                        <b-form-select v-model="team" :options="teams" v-on:change="searchExecutives()" class="form-control ml-1">
                        </b-form-select>
                    </div>
                    <div class="form-group mx-4 fecha" v-if="check == 'QRR' || check == 'QRV'">
                        <label>
                            <strong>Especialista</strong>
                        </label>
                        <v-select label="text" :reduce="executives => executives.value" :options="executives" v-model="executive" class="form-control"></v-select>
                    </div>
                    <div class="form-group mx-4 fecha" v-if="check == 'KAM'">
                        <label>
                            <strong>Kam</strong>
                        </label>
                        <v-select :reduce="kams => kams.code" :options="kams" v-model="kam" v-if="check == 'KAM'" class="form-control"></v-select>
                    </div>
                    <div class="form-group mx-4 reporte-boton mt-4">
                        <button class="btn btn-primary" v-bind:disabled="loading || loading_button" v-on:click="search()">
                            Buscar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-5">
            <div class="container">
                <div class="alert alert-warning mt-3 mb-3" v-if="loading">
                    <p class="mb-0">Cargando..</p>
                </div>
                <div class="alert alert-warning" v-if="quantity == 0 && !loading">
                    <p class="mb-0">No se encontró información para mostrar. Por favor, intente con nuevos filtros.</p>
                </div>
            </div>

            <div class="container-fluid">
                <div class="table-responsive">
                    <table class="table text-center table-facturacion" id="_files" v-show="quantity > 0 && !loading">
                        <thead>
                        <tr>
                            <th scope="col">COD. CLIENTE</th>
                            <th scope="col">CLIENTE</th>
                            <th scope="col">REFERENCIA FILE</th>
                            <th scope="col">QRR <i class="fa fa-info-circle" v-b-tooltip title="QR - RESERVA"></i></th>
                            <th scope="col">QRV <i class="fa fa-info-circle" v-b-tooltip title="QR - VENDEDOR"></i></th>
                            <th scope="col">KAM</th>
                            <th scope="col">FILE</th>
                            <th scope="col">NRO PTO</th>
                            <th scope="col">CANTIDAD PAXS</th>
                            <th scope="col">FECHA IN</th>
                            <th scope="col">FECHA OUT</th>
                            <th scope="col">MU FICHA DE CLIENTE (%)</th>
                            <th scope="col">MU QR (%)</th>
                            <th scope="col">MU FINAL (%)</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(file, f) in files">
                            <td>{{ file.CODCLI }}</td>
                            <td>{{ file.RAZON }}</td>
                            <td>{{ file.DESCRI }}</td>
                            <td>{{ file.OPERAD }}</td>
                            <td>{{ file.CODOPE }}</td>
                            <td>{{ file.CODVEN }}</td>
                            <td>{{ file.NROREF }}</td>
                            <td>{{ file.NROPTO }}</td>
                            <td>{{ file.CNTMAXPAXS }}</td>
                            <td>{{ file.DIAIN2 }}</td>
                            <td>{{ file.DIAOUT2 }}</td>
                            <td>{{ (file.COMIS1 != '' && file.COMIS1 != null) ? file.COMIS1 : '0' }}</td>
                            <td>{{ (file.MUQR_POR != '' && file.MUQR_POR != null) ? file.MUQR_POR : '0' }}</td>
                            <td v-bind:class="file.CLASS">{{ (file.MUQR_FINAL != '' && file.MUQR_FINAL != null) ? file.MUQR_FINAL : '0' }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    // Using font-awesome 5 icons
    $.extend(true, $.fn.datetimepicker.defaults, {
        icons: {
            time: 'far fa-clock',
            date: 'far fa-calendar',
            up: 'fas fa-arrow-up',
            down: 'fas fa-arrow-down',
            previous: 'fas fa-chevron-left',
            next: 'fas fa-chevron-right',
            today: 'fas fa-calendar-check',
            clear: 'far fa-trash-alt',
            close: 'far fa-times-circle'
        }
    })

    export default {
        data: () => {
            return {
                lang: '',
                dateRange: '',
                loading: false,
                loading_button: false,
                quantity: 0,
                timePicker24Hour: false,
                showWeekNumbers: false,
                singleDatePicker: true,
                startDate: '',
                minDate: '',
                locale_data: {
                    direction: 'ltr',
                    format: 'YYYY-MM-DD',
                    separator: ' - ',
                    applyLabel: 'Guardar',
                    cancelLabel: 'Cancelar',
                    weekLabel: 'W',
                    customRangeLabel: 'Rango de Fechas',
                    daysOfWeek: moment.weekdaysMin(),
                    monthNames: moment.monthsShort(),
                    firstDay: moment.localeData().firstDayOfWeek()
                },
                team: '',
                teams: [],
                quantityTeams: 0,
                check: 'C',
                executives: [],
                executive: '',
                customer: '',
                kams: [],
                kam: '',
                quantityKams: 0,
                files: []
            }
        },
        created: function () {

        },
        mounted: function() {
            this.lang = localStorage.getItem('lang')
            this.searchExecutives()
            this.searchKams()
            this.searchTeams()
            this.customer = localStorage.getItem('client_code')
        },
        computed: {

        },
        methods: {
            searchExecutives: function () {
                this.loading_button = true

                axios.post(
                    baseURL + 'board/executives_user', {
                        lang: this.lang,
                        team: this.team
                    }
                )
                    .then((result) => {
                        this.loading_button = false
                        this.executives = result.data.executives
                        this.quantityExecutives = result.data.quantity
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            searchKams: function () {
                this.loading_button = true

                axios.post(
                    baseURL + 'board/kams', {
                        lang: this.lang
                    }
                )
                    .then((result) => {
                        this.loading_button = false
                        this.kams = result.data.kams
                        this.quantityKams = result.data.quantity
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            searchTeams: function () {
                this.loading_button = true

                axios.post(
                    baseURL + 'board/teams', {
                        lang: this.lang,
                    }
                )
                    .then((result) => {
                        this.loading_button = false
                        this.teams = result.data.teams
                        this.quantityTeams = result.data.quantity

                        if(this.quantityTeams == 1)
                        {
                            this.team = result.data.team
                        }
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            search: function () {

                this.customer = localStorage.getItem('client_code')

                if(this.dateRange == '')
                {
                    this.$toast.error('Seleccione un rango de fechas para poder filtrar los reportes', {
                        // override the global option
                        position: 'top-right'
                    })
                    return false
                }

                if(this.check == 'KAM' && (this.kam == '' || this.kam == null))
                {
                    this.$toast.error('Seleccione un(a) kam para poder filtrar los reportes', {
                        // override the global option
                        position: 'top-right'
                    })
                    return false
                }

                if(this.check == 'C' && (this.customer == '' || this.customer == null))
                {
                    this.$toast.error('Seleccione un cliente para poder filtrar los reportes', {
                        // override the global option
                        position: 'top-right'
                    })
                    return false
                }

                this.loading = true

                setTimeout(function(){
                    $('#_files').DataTable().destroy()
                }, 10)

                axios.post(
                    baseURL + 'filter_files', {
                        lang: this.lang,
                        type: this.check,
                        team: this.team,
                        user: (this.check == 'C') ? this.customer : ((this.check == 'KAM') ? this.kam : this.executive),
                        dateRange: this.dateRange
                    }
                )
                    .then((result) => {
                        this.loading = false

                        if(result.data.type == 'error')
                        {
                            window.location.reload()
                        }
                        else
                        {
                            this.files = result.data.files
                            this.quantity = result.data.quantity

                            if(this.quantity > 0)
                            {
                                setTimeout(function(){
                                    $('#_files').DataTable({
                                        order: false,
                                        language: {
                                            "sProcessing":     "Procesando...",
                                            "sLengthMenu":     "Mostrar _MENU_ registros",
                                            "sZeroRecords":    "No se encontraron resultados",
                                            "sEmptyTable":     "Ningún dato disponible en esta tabla =(",
                                            "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                                            "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
                                            "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
                                            "sInfoPostFix":    "",
                                            "sSearch":         "Buscar:",
                                            "sUrl":            "",
                                            "sInfoThousands":  ",",
                                            "sLoadingRecords": "Cargando...",
                                            "oPaginate": {
                                                "sFirst":    "Primero",
                                                "sLast":     "Último",
                                                "sNext":     "Siguiente",
                                                "sPrevious": "Anterior"
                                            },
                                            "oAria": {
                                                "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                                                "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                                            },
                                            "buttons": {
                                                "copy": "Copiar",
                                                "colvis": "Visibilidad"
                                            }
                                        }
                                    })
                                }, 10)
                            }
                        }
                    })
                    .catch((e) => {
                        this.loading = false
                        console.log(e)
                    })
            },
        }
    };
</script>
