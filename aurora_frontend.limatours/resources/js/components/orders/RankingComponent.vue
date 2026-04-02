<template>
    <div>
        <div class="form container">
            <div class="d-flex justify-content-between align-items-start">
                <div class="col">
                    <div class="form-group">
                        <label><strong>Rango de Fechas</strong> <i class="fa fa-info-circle" v-b-tooltip
                            title="Es recomendable filtrar fechas exactas: El primer día del mes y el primer día del mes final."></i>
                        </label>
                        <date-range-picker
                            :locale-data="locale_data"
                            :time-picker24-hour="timePicker24Hour"
                            :show-week-numbers="showWeekNumbers"
                            :ranges="false"
                            :auto-apply="true"
                            :disabled="loading"
                            :readonly="loading"
                            v-model="dateRange">
                        </date-range-picker>
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label><strong>Sector</strong></label>
                        <v-select label="label" :reduce="sector => sector.code" :disabled="loading"
                            :options="sectors" v-model="sector" class="form-control"></v-select>
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label><strong>Equipo</strong></label>
                        <b-form-select v-model="team" :options="teams" v-bind:disabled="loading"
                            v-on:change="searchExecutives()" class="form-control ml-1">
                        </b-form-select>
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label><strong>Especialista</strong></label>
                        <v-select label="text" :reduce="executives => executives.value"
                            v-bind:disabled="loading" :options="executives" v-model="executive"
                            class="form-control" :disabled="check == 'C'"></v-select>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <div class="col-auto text-right">
                    <div class="form-group">
                        <div class="text-muted">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="check"
                                    v-model="check" id="customer" value="C" v-bind:disabled="loading" />
                                <label class="form-check-label" for="customer">Cliente</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="check"
                                    v-model="check" v-bind:disabled="loading" id="executive" value="E">
                                <label class="form-check-label" for="executive">Especialista</label>
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-primary mb-3" v-bind:disabled="loading" v-on:click="search()">
                        Buscar
                    </button>
                </div>
            </div>
        </div>

        <div class="mt-5">
            <div class="container">
                <template v-if="loading && message_loading">
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-danger"
                            role="progressbar" :aria-valuenow="getPercentPages" aria-valuemin="0" aria-valuemax="100"
                            :style="`width: ${getPercentPages}%`"></div>
                    </div>
                    <div class="alert alert-warning mt-3 mb-3">
                        <p class="mb-0">{{ message_loading }}</p>
                    </div>
                </template>
                <div class="alert alert-warning" v-if="quantity == 0 && !loading && message_loading !== ''">
                    <p class="mb-0">No se encontró información para mostrar. Por favor, intente con nuevos filtros.</p>
                </div>
            </div>

            <div class="p-4" v-if="quantity > 0 && !loading">
                <div class="table-responsive" v-if="quantity > 0 && !loading">
                    <table class="table table-hover text-center table-facturacion-ranking" id="_executives">
                    <thead>
                    <tr>
                        <th scope="col" rowspan="2">ESPECIALISTA</th>
                        <th colspan="4" scope="row">ACUMULADO</th>
                        <th v-bind:class="((k % 2) == 0) ? 'bg-dark text-light' : ''"
                            v-for="(r, k) in ranking" scope="row" colspan="4">{{ r.month }}</th>
                    </tr>
                    <tr>
                        <th scope="col">INDICADOR TIEMPO DE RESPUESTA (%)</th>
                        <th scope="col">INDICADOR PEDIDOS CONCRETADOS (%)</th>
                        <th scope="col">RATIO DE TRABAJO <i class="fa fa-info-circle" v-b-tooltip title="#Recotizaciones"></i></th>
                        <th scope="col">RATIO DE TRABAJO <i class="fa fa-info-circle" v-b-tooltip title="#cotis trabajadas por file concretado"></i></th>
                        <template v-for="(r, k) in ranking">
                            <th scope="col">INDICADOR TIEMPO DE RESPUESTA (%)</th>
                            <th scope="col">INDICADOR PEDIDOS CONCRETADOS (%)</th>
                            <th scope="col">RATIO DE TRABAJO <i class="fa fa-info-circle" v-b-tooltip title="#Recotizaciones"></i></th>
                            <th scope="col">RATIO DE TRABAJO <i class="fa fa-info-circle" v-b-tooltip title="#cotis trabajadas por file concretado"></i></th>
                        </template>
                    </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(executive, e) in responseExecutives">
                            <td>{{ e }}</td>
                            <td v-bind:class="[(executive.stats.time_response < 90) ? 'table-danger' : '' ]">{{ executive.stats.time_response }}</td>
                            <td>{{ executive.stats.percent_placed }}</td>
                            <td v-bind:class="[(executive.stats.work_rate_orders > 3) ? 'table-danger' : '' ]">{{ executive.stats.work_rate_orders }}</td>
                            <td v-bind:class="[(executive.stats.work_rate > 3) ? 'table-danger' : '' ]">{{ executive.stats.work_rate }}</td>
                            <template v-for="(r, k) in ranking">
                                <template v-if="r.executives[e] != undefined">
                                    <td v-bind:class="[(r.executives[e].stats.time_response < 90) ? 'table-danger' : '' ]">{{ r.executives[e].stats.time_response }}</td>
                                    <td>{{ r.executives[e].stats.percent_placed }}</td>
                                    <td v-bind:class="[(r.executives[e].stats.work_rate_orders > 3) ? 'table-danger' : '' ]">{{ r.executives[e].stats.work_rate_orders }}</td>
                                    <td v-bind:class="[(r.executives[e].stats.work_rate > 3) ? 'table-danger' : '' ]">{{ r.executives[e].stats.work_rate }}</td>
                                </template>
                                <template v-else>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                </template>
                            </template>
                        </tr>
                    </tbody>
                    <tfoot v-if="quantity > 0">
                        <tr>
                            <th>TOTAL GENERAL</th>
                            <th v-bind:class="[(all.time_response < 90) ? 'table-danger' : '' ]">{{ all.time_response }}</th>
                            <th>{{ all.percent_placed }}</th>
                            <th v-bind:class="[(all.work_rate_orders > 3) ? 'table-danger' : '' ]">{{ all.work_rate_orders }}</th>
                            <th v-bind:class="[(all.work_rate > 3) ? 'table-danger' : '' ]">{{ all.work_rate }}</th>
                            <template v-for="(r, k) in ranking">
                                <th v-bind:class="[(r.all.time_response < 90) ? 'table-danger' : '' ]">{{ r.all.time_response }}</th>
                                <th>{{ r.all.percent_placed }}</th>
                                <th v-bind:class="[(r.all.work_rate_orders > 3) ? 'table-danger' : '' ]">{{ r.all.work_rate_orders }}</th>
                                <th v-bind:class="[(r.all.work_rate > 3) ? 'table-danger' : '' ]">{{ r.all.work_rate }}</th>
                            </template>
                        </tr>
                    </tfoot>
                </table>
                </div>

                <!-- download-excel :data="getData(responseExecutives)" :disabled="loading"
                    :fields="json_fields" worksheet="Reporte"
                    v-if="quantity > 0 && !loading"
                    name="ranking_report_executives.xls" class="btn btn-primary btn-md">
                    Descargar datos en Excel
                </download-excel -->

                <!-- button type="button" v-on:click="downloadExcel('ranking_report', 'executives')" v-if="quantity > 0 && !loading" class="btn btn-primary btn-md">Descargar datos en Excel</button -->

                <div class="table-responsive" v-if="quantity > 0">
                    <table class="table table-hover text-center table-facturacion-ranking" id="_customers">
                    <thead>
                    <tr>
                        <th scope="col" rowspan="2">CLIENTE</th>
                        <th colspan="4" scope="row" v-if="quantity > 0">ACUMULADO</th>
                        <th v-bind:class="((k % 2) == 0) ? 'bg-dark text-light' : ''"
                            v-for="(r, k) in ranking" colspan="4" scope="row">{{ r.month }}</th>
                    </tr>
                    <tr>
                        <template v-if="quantity > 0">
                            <th scope="col">INDICADOR TIEMPO DE RESPUESTA (%)</th>
                            <th scope="col">INDICADOR PEDIDOS CONCRETADOS (%)</th>
                            <th scope="col">RATIO DE TRABAJO <i class="fa fa-info-circle" v-b-tooltip title="#Recotizaciones"></i></th>
                            <th scope="col">RATIO DE TRABAJO <i class="fa fa-info-circle" v-b-tooltip title="#cotis trabajadas por file concretado"></i></th>
                        </template>
                        <template v-for="(r, k) in ranking">
                            <th scope="col">INDICADOR TIEMPO DE RESPUESTA (%)</th>
                            <th scope="col">INDICADOR PEDIDOS CONCRETADOS (%)</th>
                            <th scope="col">RATIO DE TRABAJO <i class="fa fa-info-circle" v-b-tooltip title="#Recotizaciones"></i></th>
                            <th scope="col">RATIO DE TRABAJO <i class="fa fa-info-circle" v-b-tooltip title="#cotis trabajadas por file concretado"></i></th>
                        </template>
                    </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(customer, c) in responseCustomers">
                            <td>{{ c }}</td>
                            <template v-if="quantity > 0">
                                <td v-bind:class="[(customer.stats.time_response < 90) ? 'table-danger' : '' ]">{{ customer.stats.time_response }}</td>
                                <td>{{ customer.stats.percent_placed }}</td>
                                <td v-bind:class="[(customer.stats.work_rate_orders > 3) ? 'table-danger' : '' ]">{{ customer.stats.work_rate_orders }}</td>
                                <td v-bind:class="[(customer.stats.work_rate > 3) ? 'table-danger' : '' ]">{{ customer.stats.work_rate }}</td>
                            </template>
                            <template v-for="(r, k) in ranking">
                                <template v-if="r.customers[c] != undefined">
                                    <td v-bind:class="[(r.customers[c].stats.time_response < 90) ? 'table-danger' : '' ]">{{ r.customers[c].stats.time_response }}</td>
                                    <td>{{ r.customers[c].stats.percent_placed }}</td>
                                    <td v-bind:class="[(r.customers[c].stats.work_rate_orders > 3) ? 'table-danger' : '' ]">{{ r.customers[c].stats.work_rate_orders }}</td>
                                    <td v-bind:class="[(r.customers[c].stats.work_rate > 3) ? 'table-danger' : '' ]">{{ r.customers[c].stats.work_rate }}</td>
                                </template>
                                <template v-else>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                </template>
                            </template>
                        </tr>
                    </tbody>
                    <tfoot v-if="quantity > 0">
                    <tr>
                        <th>TOTAL GENERAL</th>
                        <template v-if="quantity > 0">
                            <th v-bind:class="[(all.time_response < 90) ? 'table-danger' : '' ]">{{ all.time_response }}</th>
                            <th>{{ all.percent_placed }}</th>
                            <th v-bind:class="[(all.work_rate_orders > 3) ? 'table-danger' : '' ]">{{ all.work_rate_orders }}</th>
                            <th v-bind:class="[(all.work_rate > 3) ? 'table-danger' : '' ]">{{ all.work_rate }}</th>
                        </template>
                        <template v-for="(r, k) in ranking">
                            <th v-bind:class="[(r.all.time_response < 90) ? 'table-danger' : '' ]">{{ r.all.time_response }}</th>
                            <th>{{ r.all.percent_placed }}</th>
                            <th v-bind:class="[(r.all.work_rate_orders > 3) ? 'table-danger' : '' ]">{{ r.all.work_rate_orders }}</th>
                            <th v-bind:class="[(r.all.work_rate > 3) ? 'table-danger' : '' ]">{{ r.all.work_rate }}</th>
                        </template>
                    </tr>
                    </tfoot>
                </table>
                </div>

                <!-- download-excel :data="getData(responseCustomers)" :disabled="loading"
                    :fields="json_fields" worksheet="Reporte"
                    v-if="quantity > 0 && !loading"
                    name="ranking_report_customers.xls" class="btn btn-primary btn-md">
                    Descargar datos en Excel
                </download-excel -->
                <!-- button type="button" v-on:click="downloadExcel('ranking_report', 'customers')" v-if="quantity > 0 && !loading" class="btn btn-primary btn-md">Descargar datos en Excel</button -->
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

    const toNumericPairs = input => {
        const entries = Object.entries(obj);
        entries.forEach(entry => entry[0] = +entry[0]);
        return entries;
    }

    export default {
        data: () => {
            return {
                lang: '',
                dateRange: '',
                loading: false,
                message_loading: '',
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
                team: 'TODOS',
                quantityTeams: 0,
                teams: [],
                executives: [],
                executive: '',
                quantityExecutives: 0,
                customer: '',
                ranking: [],
                acumulado: [],
                all: [],
                check: 'C',
                responseExecutives: [],
                responseCustomers: [],
                page: 1,
                pages: 1,
                orders: [],
                sector: '',
                sectors: [
                    { code: '', label: 'TODOS' },
                    { code: 1, label: 'C1 - Estados unidos y Canadá' },
                    { code: 2, label: 'C2 - Europa y APAC' },
                    { code: 3, label: 'C3 - Latinoamérica y España, Italia y Portugal' },
                ],
            }
        },
        created: function () {

        },
        mounted: function() {
            this.lang = localStorage.getItem('lang')
            this.searchTeams()
            this.searchExecutives()
            this.customer = localStorage.getItem('client_code')
        },
        computed: {
            getPercentPages() {
                if (this.pages > 0) {
                    return Math.floor((this.page / this.pages) * 100);
                }
                return 0;
            }
        },
        methods: {
            getData: async function (_data) {
                return await window.getData(_data)
            },
            searchTeams: function () {
                axios.post(
                    baseURL + 'board/teams', {
                        lang: this.lang,
                    }
                )
                    .then((result) => {
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
            searchExecutives: function (search, loading) {
                this.executive = ''

                axios.post(
                    baseURL + 'board/executives_user', {
                        lang: this.lang,
                        team: this.team
                    }
                )
                    .then((result) => {
                        this.executives = result.data.executives
                        this.quantityExecutives = result.data.quantity
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            delay: async function (ms) {
                return new Promise(resolve => setTimeout(resolve, ms));
            },
            makeRequestWithRetry: async function (url, data, maxRetries = 3) {
                let lastError;

                for (let attempt = 1; attempt <= maxRetries; attempt++) {
                    try {
                        const response = await axios.post(url, data);

                        if(response.data.type === 'success')
                        {
                            this.page++;
                            return response;
                        }
                        else
                        {
                            lastError = response.data.message ?? '';

                            if (attempt < maxRetries) {
                                await this.delay(attempt * 1000);
                                continue;
                            }
                        }
                    } catch (error) {
                        lastError = error;

                        // Solo reintentar para errores 503 o de conexión
                        if (error.response?.status === 503 || error.code === 'ECONNABORTED') {
                            console.log(`Intento ${attempt} fallido. Reintentando en ${attempt * 1000}ms...`);

                            if (attempt < maxRetries) {
                                await this.delay(attempt * 1000);
                                continue;
                            }
                        }

                        // Si no es un error 503, no reintentar
                        throw error;
                    }
                }

                throw lastError;
            },
            // Función para procesar grupos de páginas
            processInBatches: async function (pages, batchSize = 10) {
                this.customer = localStorage.getItem('client_code') ?? '';

                if(this.check == 'C' && this.customer == '')
                {
                    this.customer = 'TODOS'
                }

                const baseParams = {
                    lang: this.lang,
                    type: this.check,
                    user: this.check === 'C' ? this.customer : this.executive,
                    status: this.status,
                    dateRange: this.dateRange,
                    sector: this.sector,
                    team: this.team,
                    product: this.product,
                    nroped: this.query
                };

                let stopProcessing = false;

                for (let i = 0; i < pages.length; i += batchSize) {
                    if (stopProcessing) break;

                    const batch = pages.slice(i, i + batchSize);
                    this.message_loading = `Procesando página ${Math.floor(i/batchSize) + 1} de ${Math.ceil(pages.length/batchSize)}.. Espere un momento..`;

                    const batchPromises = batch.map(page =>
                        this.makeRequestWithRetry(`${baseURL}search_orders`, {
                            ...baseParams,
                            limit: page
                        }, 3)
                    );

                    const batchResponses = await Promise.all(batchPromises);

                    for (const response of batchResponses) {
                        const orders = response.data?.orders;

                        if (Array.isArray(orders)) {
                            if (orders.length === 0) {
                                stopProcessing = true;
                            }
                            this.orders.push(...orders);
                        }
                    }

                    if (!stopProcessing) {
                        await this.delay(1000);
                    } else {
                        throw new Error('STOP_BATCH_PROCESS');
                    }
                }
            },
            search: async function () {
                this.message_loading = '';
                this.page = 0;

                if(this.dateRange == '' && this.query == '')
                {
                    this.$toast.error('Seleccione un rango de fechas para poder filtrar los reportes', {
                        // override the global option
                        position: 'top-right'
                    })
                    return false
                }

                this.loading = true
                this.orders = [];
                this.quantity = 0;

                const dataTableConfig = {
                    searching: true,
                    language: {
                        "sProcessing": "Procesando...",
                        "sLengthMenu": "Mostrar _MENU_ registros",
                        "sZeroRecords": "No se encontraron resultados",
                        "sEmptyTable": "Ningún dato disponible en esta tabla =(",
                        "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                        "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                        "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
                        "sInfoPostFix": "",
                        "sSearch": "Buscar:",
                        "sUrl": "",
                        "sInfoThousands": ",",
                        "sLoadingRecords": "Cargando...",
                        "oPaginate": {
                            "sFirst": "Primero",
                            "sLast": "Último",
                            "sNext": "Siguiente",
                            "sPrevious": "Anterior"
                        },
                        "oAria": {
                            "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                            "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                        },
                        "buttons": {
                            "copy": "Copiar",
                            "colvis": "Visibilidad"
                        }
                    }
                };

                let totalPages = 1;

                if(this.dateRange && !this.query)
                {
                    const response = await axios.post(`${baseURL}total_orders`, {
                        dateRange: this.dateRange,
                    });

                    totalPages = response.data.pages ?? 0;
                    this.pages = totalPages;
                }

                this.message_loading = 'Cargando.. Espere un momento..';

                try {
                    const allPages = Array.from({ length: totalPages }, (_, i) => i + 1);
                    await this.processInBatches(allPages, 7);

                } catch (error) {
                    if (error.message === 'STOP_BATCH_PROCESS') {
                        console.warn('Proceso detenido: Se encontró una página con un array de pedidos vacío.');
                    } else {
                        console.error('Error grave al cargar:', error);
                    }
                } finally {
                    this.quantity = this.orders.length;
                    this.loading = false;
                    this.renderReport(this.orders);
                }
            },
            renderReport: async function (orders) {

                let vm = this
                vm.detail = false
                vm.loading = false

                let report = await window.rankingOrders(orders)

                vm.ranking = report.ranking
                vm.all = report.acumulado.all
                vm.responseExecutives = report.acumulado.executives
                vm.responseCustomers = report.acumulado.customers
                vm.quantity = report.acumulado.quantity

                if(vm.quantity > 0)
                {
                    vm.showTable(vm)
                }
            },
            showTable: function (vm) {
                vm.page = 'show'

                setTimeout(function() {
                    $('.table-facturacion-ranking').DataTable({
                        /*
                        buttons: [
                            'excel'
                        ],*/
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
            },
            downloadExcel: function (_type, _table) {
                window.location = baseURL + 'export_excel?type=' + _type + '&table=' + _table;
            }
        }
    };
</script>
