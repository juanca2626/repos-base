<template>
    <div>
        <div class="container">
            <div class="form p-5">
                <div class="form-row justify-content-between">
                    <div class="form-group col">
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
                    <div class="form-group col">
                        <label>
                            <strong>Sector</strong>
                        </label>
                        <v-select label="label" :reduce="sector => sector.code"
                            :options="sectors" v-model="sector" class="form-control"></v-select>
                    </div>
                    <div class="form-group col">
                        <label>
                            <strong>Tipo Usuario</strong>
                        </label>
                        <v-select label="label" :reduce="type => type.code"
                            :options="types" v-model="type" class="form-control"></v-select>
                    </div>
                    <div class="col-auto">
                        <button type="button" v-on:click="searchReport()"
                                v-bind:disabled="loading" class="btn btn-primary mt-3">BUSCAR</button>
                    </div>
                    <!-- button type="button" v-on:click="downloadExcel()"
                            v-bind:disabled="loading || logs.length == 0"
                            class="btn btn-primary mt-3">EXCEL</button -->
                </div>
            </div>

            <div class="alert alert-warning" v-if="loading">Cargando..</div>

            <div v-if="!loading">
                <div class="table-responsive" v-if="logs.length > 0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>File</th>
                                <th>Cliente</th>
                                <th>Usuario</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-for="(log, l) in logs">
                                <tr v-bind:key="'log-' + l">
                                    <td>{{ log.type.toUpperCase() }}</td>
                                    <td>{{ log.nrofile }}</td>
                                    <td>
                                        <template v-if="log.client_id > 0 && log.client != null">
                                            {{ log.client.code }} - {{ log.client.name }}
                                        </template>
                                    </td>
                                    <td>
                                        <template v-if="log.user_id > 0 && log.user != null">
                                            {{ log.user.code }} - {{ log.user.email }}
                                        </template>
                                    </td>
                                    <td>{{ log.created_at }}</td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div class="alert alert-warning" v-if="logs.length == 0">
                    No se encontraron registros para la fecha seleccionada.
                </div>

                <div class="col-md-12 text-right" v-if="logs.length > 0">
                    <download-excel :data="logs" :disabled="loading"
                        :fields="json_fields" worksheet="Reporte de Uso - Ingreso de Información"
                        name="registro_data_modales.xls" class="btn btn-primary">
                        Descargar información
                    </download-excel>
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
                logs: [],
                type: '',
                sector: '',
                types: [
                    { code: '', label: 'TODOS' },
                    { code: 'C', label: 'Cliente' },
                    { code: 'E', label: 'Especialista' },
                ],
                sectors: [
                    { code: '', label: 'TODOS' },
                    { code: 1, label: 'C1 - Estados unidos y Canadá' },
                    { code: 2, label: 'C2 - Europa y APAC' },
                    { code: 3, label: 'C3 - Latinoamérica y España, Italia y Portugal' },
                ],
                json_fields: {
                    "Tipo": "type",
                    "File": "nrofile",
                    "Cliente": "client.name",
                    "Usuario": "user.code",
                    "Fecha": "created_at",
                },
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
                $('.table').DataTable().destroy()

                let vm = this
                vm.loading = true

                setTimeout(function () {
                    axios.post(
                        baseURL + 'reports/cosig/access', {
                            date_range: vm.dateRange,
                            sector: vm.sector,
                            type: vm.type
                        }
                    )
                        .then((result) => {
                            vm.loading = false

                            if(result.data.type == 'success')
                            {
                                vm.logs = result.data.logs

                                if(vm.logs.length > 0)
                                {
                                    setTimeout(function() {
                                        $('.table').DataTable({
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
