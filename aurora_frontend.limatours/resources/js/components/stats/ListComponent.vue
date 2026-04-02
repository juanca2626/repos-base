<template>
    <div>
        <div class="container">
            <div class="form mb-3">
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
                            v-bind:disabled="loading"
                            :auto-apply="true"
                            v-model="dateRange">
                        </date-range-picker>
                    </div>
                    <div class="form-group fecha">
                        <label>
                            <strong>Tipo de reporte</strong>
                        </label>
                        <v-select label="label" :reduce="report => report.code" @input="resetData()"
                                  :options="reports" v-bind:disabled="loading" v-model="report" class="form-control"></v-select>
                    </div>
                    <div class="form-group fecha">
                        <label>
                            <strong>Sector</strong>
                        </label>
                        <v-select label="label" :reduce="sector => sector.code" @input="resetData()"
                                  :options="sectors" v-model="sector" class="form-control"></v-select>
                    </div>
                    <button type="button" v-on:click="searchReport()"
                            v-bind:disabled="loading" class="btn btn-primary mt-3">Buscar</button>
                    <button type="button" v-on:click="downloadExcel()"
                            v-bind:disabled="loading || clients.length == 0"
                            class="btn btn-primary mt-3">Descargar Relación</button>
                </div>
            </div>

            <div v-if="loading" class="d-flex align-items-center justify-content-center p-5 my-5">
                <div class="spinner-border text-danger me-3" role="status"></div>
                <span class="h5 mb-0 text-muted fw-light mx-2">Obteniendo registros...</span>
            </div>

            <div v-if="flag_report && !loading">
                <div class="report-main-container p-3">

                    <div v-if="shouldShowEmptyAlert" class="alert alert-warning shadow-sm p-4 mb-4">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-exclamation-circle-fill text-danger fs-4 me-3"></i>
                            <span class="fw-bold">No se encontraron registros para la fecha seleccionada.</span>
                        </div>
                    </div>

                    <div v-if="all_clients > 0 && [1, 2, 3].includes(report)" class="mb-4">
                        <div class="d-flex align-items-center justify-content-between p-4 bg-light border-start border-danger border-5 rounded shadow-sm">
                            <div>
                                <p class="text-muted small fw-bold mb-1 uppercase text-spacing">Métrica de Clientes</p>
                                <h3 class="mb-0 fw-bold text-dark">{{ all_clients }} Registros</h3>
                            </div>
                            <span class="badge bg-danger px-4 py-3 fs-6 rounded-pill shadow-sm text-white">
                                {{ report == 1 ? 'TOTALES' : (report == 2 ? 'CON ACCESO' : 'LOGINS A2') }}
                            </span>
                        </div>
                    </div>

                    <div v-if="[1, 2, 3].includes(report) && clients.length > 0" class="report-card mb-5 border-0 rounded-3 overflow-hidden">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 table-custom-red">
                                <thead class="bg-dark text-white">
                                    <tr>
                                        <th class="py-3 ps-4 border-0">Código</th>
                                        <th class="py-3 border-0">Descripción / Empresa</th>
                                        <th class="py-3 border-0">Sector / Mercado</th>
                                        <th class="py-3 border-0">Fecha {{ report == 3 ? 'Actividad' : 'Registro' }}</th>
                                        <th v-if="report == 3" class="py-3 pe-4 border-0">Usuario & Login</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(client, c) in clients" :key="'cl-'+c">
                                        <td class="ps-4 text-red-bold">{{ client.code }}</td>
                                        <td class="fw-medium">{{ client.business_name }}</td>
                                        <td>
                                            <div v-if="client.markets">
                                                <span class="fw-bold d-block small text-dark">{{ client.markets.code }}</span>
                                                <span class="text-muted extra-small">{{ client.markets.name }}</span>
                                            </div>
                                            <span v-else class="text-muted italic small">Sin sector</span>
                                        </td>
                                        <td class="text-muted small">
                                            {{ report == 3 ? client.updated_at : client.created_at }}
                                        </td>
                                        <td v-if="report == 3" class="pe-4">
                                            <div class="small fw-bold text-dark">{{ getMostRecentLoginUser(client)?.email || 'N/A' }}</div>
                                            <div class="text-muted extra-small">{{ getMostRecentLoginDate(client) }}</div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <template v-if="report == 4">
                        <div v-if="clients.all_reservations > 0" class="mb-4">
                            <div class="p-4 bg-light border-start border-danger border-5 rounded shadow-sm d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="fw-bold text-dark mb-1 text-uppercase">Reservas Realizadas</h5>
                                    <p class="text-muted mb-0 small">
                                        Se han realizado <strong>{{ clients.reservations }}</strong> reservas de un total de <strong>{{ clients.all_reservations }}</strong> files generados.
                                    </p>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-danger text-white px-3 py-2 fs-6">
                                        {{ parseFloat(clients.reservations * 100 / clients.all_reservations).toFixed(2) }}% Éxito
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div v-if="clients.reservations > 0" class="mb-5">
                            <div class="report-card border-0 rounded-3 overflow-hidden">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0 table-custom-red">
                                        <thead class="bg-dark text-white">
                                            <tr>
                                                <th class="py-3 ps-4">File</th>
                                                <th class="py-3">Cliente</th>
                                                <th class="py-3">Usuario Responsable</th>
                                                <th class="py-3">Inicio Viaje</th>
                                                <th class="py-3 pe-4">Entidad</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(res, r) in clients.reservations_items" :key="'res-'+r">
                                                <td class="ps-4 text-red-bold">{{ res.file_code }}</td>
                                                <td>{{ res.client_code }}</td>
                                                <td class="small">
                                                    <template v-if="res.create_user">
                                                        {{ res.create_user.code }} - {{ res.create_user.email }}
                                                    </template>
                                                </td>
                                                <td class="text-muted small">{{ res.date_init }}</td>
                                                <td class="pe-4 extra-small text-muted">
                                                    <template v-if="res.entity">
                                                        {{ res.entity }}<template v-if="res.object_id"> - N° {{ res.object_id }}</template>
                                                    </template>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div v-if="clients.all_quotes > 0" class="mb-4">
                            <div class="p-4 bg-light border-start border-info border-5 rounded shadow-sm d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="fw-bold text-dark mb-1 text-uppercase">Cotizaciones Realizadas</h5>
                                    <p class="text-muted mb-0 small">
                                        Se han realizado <strong>{{ clients.quotes }}</strong> cotizaciones de un total de <strong>{{ clients.all_quotes }}</strong> intentos.
                                    </p>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-info text-white px-3 py-2 fs-6">
                                        {{ parseFloat(clients.quotes * 100 / clients.all_quotes).toFixed(2) }}% Conversión
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div v-if="clients.quotes > 0" class="mb-5">
                            <div class="report-card border-0 rounded-3 overflow-hidden">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0 table-custom-red">
                                        <thead class="bg-dark text-white text-center">
                                            <tr>
                                                <th class="py-3">N° Cotización</th>
                                                <th class="py-3 text-start">Usuario / Email</th>
                                                <th class="py-3">Fecha Inicio</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(quote, q) in clients.quotes_items" :key="'q-'+q">
                                                <td class="text-red-bold text-center fw-bold">{{ quote.id }}</td>
                                                <td>{{ quote.user?.code }} - {{ quote.user?.email }}</td>
                                                <td class="text-muted small text-center">{{ quote.date_in }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template v-if="report == 5">
                        <div v-if="clients.logs > 0" class="mb-5">
                            <h5 class="fw-bold text-dark mb-3 px-2 border-start border-danger border-4 ms-2">LOGS DE CLIENTES (ACTUALIZACIONES)</h5>
                            <div class="report-card border-0 rounded-3 overflow-hidden">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0 table-custom-red">
                                        <thead class="bg-dark text-white">
                                            <tr>
                                                <th class="py-3 ps-4">File</th>
                                                <th class="py-3">Usuario</th>
                                                <th class="py-3">Cliente</th>
                                                <th class="py-3 pe-4">Tipo</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(log, l) in clients.logs_items" :key="'log-'+l">
                                                <td class="ps-4 text-red-bold">{{ log.nrofile }}</td>
                                                <td class="small">{{ log.user?.name }}</td>
                                                <td class="small">{{ log.client?.business_name }}</td>
                                                <td class="pe-4"><span class="badge bg-light text-dark border small text-uppercase">{{ log.type }}</span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </template>

                    <div class="mt-5 p-4 bg-white border rounded shadow-sm">
                        <h6 class="fw-bold text-danger border-bottom pb-2 mb-3">NOTAS DEL REPORTE</h6>
                        <div class="row small text-muted">
                            <div class="col-md-6">
                                <p class="mb-1">· Files y cotizaciones corresponden solo a registros generados en <strong>A2</strong>.</p>
                                <p class="mb-1" v-if="report == 4">· Se incluyen files cancelados; no se incluyen tickets.</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1">· Reporte generado automáticamente según el rango de fechas seleccionado.</p>
                                <p class="mb-1" v-if="report == 3">· El "Último Login" refleja el acceso más reciente detectado.</p>
                            </div>
                        </div>
                    </div>

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
                flag_report: false,
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
                    { code: 1, label: 'Clientes nuevos' },
                    { code: 2, label: 'Clientes con acceso a A2' },
                    { code: 3, label: 'Clientes que han ingresado a A2' },
                    { code: 4, label: 'Reservas y Cotizaciones de clientes A2' },
                    { code: 5, label: 'Ingreso de datos por clientes A2' },
                ],
                sector: '',
                sectors: [
                    { code: '', label: 'TODOS'},
                    { code: 1, label: 'C1 - Estados unidos y Canadá' },
                    { code: 2, label: 'C2 - Europa y APAC' },
                    { code: 3, label: 'C3 - Latinoamérica y España, Italia y Portugal' },
                    { code: 4, label: 'OTS' },
                ],
                clients: [],
                all_clients: 0,
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
            shouldShowEmptyAlert() {
                if ([1, 2, 3].includes(this.report)) {
                    return this.clients.length == 0 && this.all_clients == 0;
                }
                if (this.report == 4) {
                    return this.clients.all_reservations == 0 && this.clients.all_quotes == 0;
                }
                if (this.report == 5) {
                    return this.clients.reservations == 0 && this.clients.logs == 0;
                }
                return false;
            }
        },
        methods: {
            resetData: function () {
                let vm = this

                setTimeout(() => {
                    vm.flag_report = false
                }, 10)
            },
            downloadExcel: function () {
                window.location = baseURL + 'export_excel?type=stats_clients&table=' + this.report;
            },
            searchReport: function () {

                if(this.report == '')
                {
                    this.$toast.error('Seleccione un tipo de reporte para continuar', {
                        // override the global option
                        position: 'top-right'
                    })
                    return false
                }

                let vm = this

                setTimeout(function () {
                    vm.loading = true
                    vm.clients = []

                    axios.post(
                        baseURL + 'search_stats_clients', {
                            type: vm.report,
                            sector: vm.sector,
                            date_range: vm.dateRange
                        }
                    )
                        .then((result) => {
                            vm.loading = false
                            vm.flag_report = true

                            if(result.data.type == 'success')
                            {
                                vm.clients = result.data.response
                                vm.all_clients = result.data.count

                                if(vm.report == 1 || vm.report == 2 || vm.report == 3)
                                {
                                    if(vm.clients.length > 0)
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
                                else
                                {
                                    if(vm.clients.reservations > 0 || vm.clients.logs > 0 || vm.clients.clients > 0)
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
                            }
                        })
                        .catch((e) => {
                            console.log(e)

                            setTimeout(() => {
                                vm.searchReport()
                            }, 10)
                        })
                }, 10)
            },
            // Obtener el usuario con el login más reciente
            getMostRecentLoginUser(client) {
                let userWithRecentLogin = null;
                let mostRecentDate = null;

                client.client_sellers.forEach(seller => {
                    if (seller.login_logs && seller.login_logs.length > 0) {
                        const lastLogin = this.getLastLoginDate(seller);
                        if (!mostRecentDate || new Date(lastLogin) > new Date(mostRecentDate)) {
                            mostRecentDate = lastLogin;
                            userWithRecentLogin = seller;
                        }
                    }
                });

                return userWithRecentLogin;
            },
            // Obtener la fecha del login más reciente
            getMostRecentLoginDate(client) {
                const user = this.getMostRecentLoginUser(client);
                return user ? this.getLastLoginDate(user) : 'Nunca';
            },
            getLastLoginDate(seller) {
                if (!seller.login_logs || seller.login_logs.length === 0) return '';

                // Ordenar logs por fecha descendente y tomar la más reciente
                const sortedLogs = [...seller.login_logs].sort((a, b) =>
                    new Date(b.created_at) - new Date(a.created_at)
                );

                return sortedLogs[0].created_at;
            }
        }
    };
</script>

<style scoped>
    /* Elimina el borde doble y asegura que el espaciado de Bootstrap mande */
    table.dataTable,
    table.table {
        border-collapse: separate !important;
        border-spacing: 0 !important;
        margin-top: 15px !important;
        margin-bottom: 15px !important;
        border: none !important;
    }

    /* Evita que el borde inferior de la cabecera se pegue */
    .table-responsive {
        border: none !important;
        padding: 5px; /* Espacio para que la sombra de la tabla no se corte */
    }

    /* Estilo para las celdas para que no pierdan el alineado */
    .table > :not(caption) > * > * {
        border-bottom-width: 1px;
        border-bottom-color: #f0f0f0;
    }
</style>
