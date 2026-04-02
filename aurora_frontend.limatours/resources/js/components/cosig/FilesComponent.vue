<template>
    <div>
        <div class="container">
            <div class="form">
                <div class="form-row justify-content-between">
                    <div class="form-group fecha">
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
                    </div>
                    <div class="form-group fecha">
                        <label>
                            <strong>Sector</strong>
                        </label>
                        <v-select label="label" :reduce="sector => sector.code"
                            :options="sectors" v-model="sector" class="form-control"></v-select>
                    </div>
                    <button type="button" v-on:click="searchReport()"
                            v-bind:disabled="loading" class="btn btn-primary mt-3">BUSCAR</button>
                    <!-- button type="button" v-on:click="downloadExcel()"
                            v-bind:disabled="loading || clients.length == 0"
                            class="btn btn-primary mt-3">EXCEL</button -->
                </div>
            </div>

            <div class="alert alert-warning" v-if="loading">Cargando..</div>

            <div v-if="!loading">
                <div class="mb-3" v-if="clients_cotis.length > 0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mt-3">
                            <li class="breadcrumb-item"><a href="javascript:;">Reporte de reservas</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Reservas desde cotizaciones</li>
                        </ol>
                    </nav>
                    <div class="table-responsive">
                        <table class="table table-cotis table-striped">
                            <thead>
                                <tr>
                                    <th>File</th>
                                    <th>Fecha de inicio</th>
                                    <th>Fecha de creación</th>
                                    <th>Nombre de grupo</th>
                                    <th>Cliente</th>
                                    <th>Mercado</th>
                                    <th>País</th>
                                    <th>Ejecutiva</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template v-for="(client, c) in clients_cotis">
                                    <tr v-bind:key="'client-' + c">
                                        <td>{{ client.file }}</td>
                                        <td>{{ client.fecha_inicio }}</td>
                                        <td>{{ client.fecha_creacion }}</td>
                                        <td>{{ client.nombre_grupo }}</td>
                                        <td>{{ client.cliente }} - {{ client.cliente_name }}</td>
                                        <td>{{ client.mercado }}</td>
                                        <td>{{ client.pais }}</td>
                                        <td>{{ client.ejecutiva_asignada }}</td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mb-3" v-if="clients_packages.length > 0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mt-3">
                            <li class="breadcrumb-item"><a href="javascript:;">Reporte de Reservas</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Reservas desde paquetes directos</li>
                        </ol>
                    </nav>
                    <div class="table-responsive">
                        <table class="table table-packages table-striped">
                            <thead>
                                <tr>
                                    <th>File</th>
                                    <th>Fecha de inicio</th>
                                    <th>Fecha de creación</th>
                                    <th>Paquete</th>
                                    <th>Nombre de grupo</th>
                                    <th>Cliente</th>
                                    <th>Mercado</th>
                                    <th>País</th>
                                    <th>Ejecutiva</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template v-for="(client, c) in clients_packages">
                                    <tr v-bind:key="'package-' + c">
                                        <td>{{ client.file }}</td>
                                        <td>{{ client.fecha_inicio }}</td>
                                        <td>{{ client.fecha_creacion }}</td>
                                        <td>{{ client.paquete_nombre }}</td>
                                        <td>{{ client.nombre_grupo }}</td>
                                        <td>{{ client.cliente }} - {{ client.cliente_name }}</td>
                                        <td>{{ client.mercado }}</td>
                                        <td>{{ client.pais }}</td>
                                        <td>{{ client.ejecutiva_asignada }}</td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="alert alert-warning" v-if="clients_packages.length == 0 && clients_cotis.length == 0">
                    No se encontraron registros para la fecha seleccionada.
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
        props: ['translations'],
        data: () => {
            return {
                data: [],
                flag: 0,
                lang: '',
                customer: '',
                loading: false,
                timePicker24Hour: false,
                showWeekNumbers: false,
                singleDatePicker: true,
                locale_data: {
                    direction: 'ltr',
                    format: 'DD-MM-YYYY',
                    separator: ' - ',
                    applyLabel: 'Guardar',
                    cancelLabel: 'Cancelar',
                    weekLabel: 'W',
                    customRangeLabel: 'Rango de Fechas',
                    daysOfWeek: moment.weekdaysMin(),
                    monthNames: moment.monthsShort(),
                    firstDay: moment.localeData().firstDayOfWeek()
                },
                dateRange: '',
                report: '',
                reports: [
                    { code: 1, label: 'Cotizaciones reservadas' },
                    { code: 2, label: 'Paquetes reservados' },
                ],
                sector: '',
                sectors: [
                    { code: '', label: 'TODOS' },
                    { code: 1, label: 'C1 - Estados unidos y Canadá' },
                    { code: 2, label: 'C2 - Europa y APAC' },
                    { code: 3, label: 'C3 - Latinoamérica y España, Italia y Portugal' },
                ],
                clients: [],
                clients_packages: [],
                clients_cotis: [],
            }
        },
        created: function () {

        },
        mounted: function() {
            this.lang = localStorage.getItem('lang')
            this.flag = localStorage.getItem('bossFlag')
            this.customer = localStorage.getItem('client_code')
        },
        computed: {

        },
        methods: {
            downloadExcel: function () {
                window.location = baseURL + 'export_excel?type=stats_clients&table=' + this.report;
            },
            searchReport: function () {
                $('.table-cotis').DataTable().destroy()
                $('.table-packages').DataTable().destroy()

                let vm = this
                vm.loading = true

                setTimeout(function () {

                    axios.post(
                        baseURL + 'reports/cosig/clients', {
                            sector: vm.sector,
                            date_range: vm.dateRange
                        }
                    )
                        .then((result) => {
                            vm.loading = false

                            if(result.data.type == 'success')
                            {
                                vm.clients_cotis = result.data.clients_cotis
                                vm.clients_packages = result.data.clients_packages

                                if(vm.clients_cotis.length > 0)
                                {
                                    setTimeout(function() {
                                        $('.table-cotis').DataTable({
                                            searching: true,
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
                                            }
                                        })
                                    }, 10)
                                }

                                if(vm.clients_packages.length > 0)
                                {
                                    setTimeout(function() {
                                        $('.table-packages').DataTable({
                                            searching: true,
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
                                            }
                                        })
                                    }, 10)
                                }
                            }
                        })
                        .catch((e) => {
                            vm.loading = false
                            console.log(e)
                        })
                }, 10)
            }
        }
    };
</script>
