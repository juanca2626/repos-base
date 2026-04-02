<template>
    <div>
        <div class="form container">
            <div class="d-flex justify-content-between align-items-center">
                <div class="col">
                    <div class="form-group">
                        <label><strong>Rango de Fechas</strong></label>
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
                        <b-form-select v-model="team"
                            v-bind:disabled="loading"
                            :options="teams" class="form-control ml-1">
                        </b-form-select>
                    </div>
                </div>
                <div class="col-auto mt-3">
                    <button class="btn btn-primary" v-bind:disabled="loading"
                        v-on:click="search()">
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
                <div v-if="!detail">
                    <h4 class="mt-5"><strong>ESPECIALISTA</strong></h4>
                    <div class="table-responsive">
                    <table class="datatable table table-hover text-center table-facturacion-area">
                        <thead>
                            <tr>
                                <th scope="col">ESPECIALISTA</th>
                                <th scope="col">PEDIDOS RECIBIDOS</th>
                                <th scope="col">VALOR DE LOS PEDIDOS RECIBIDOS</th>
                                <th scope="col">COTIZACIONES TRABAJADAS</th>
                                <th scope="col">COTIZACIONES RESPONDIDAS A TIEMPO</th>
                                <th scope="col">INDICADOR TIEMPO DE RESPUESTA (%)</th>
                                <th scope="col">PEDIDOS CONCRETADOS</th>
                                <th scope="col">MONTO CONCRETADO</th>
                                <th scope="col">INDICADOR PEDIDOS CONCRETADOS (%)</th>
                                <th scope="col">RATIO DE TRABAJO <i class="fa fa-info-circle" v-b-tooltip title="#Recotizaciones"></i></th>
                                <th scope="col">RATIO DE TRABAJO <i class="fa fa-info-circle" v-b-tooltip title="#cotis trabajadas por file concretado"></i></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(executive, k) in executives">
                                <td><a href="javascript:;" v-on:click="showDetail('executive', k, executive)">{{ k }}</a></td>
                                <td>{{ executive.stats.all_orders }}</td>
                                <td>{{ executive.stats.mount_all_orders | formatPrice }}</td>
                                <td>{{ executive.stats.all_quotes }}</td>
                                <td>{{ executive.stats.quotes_ok }}</td>
                                <td v-bind:class="[(executive.stats.time_response < 90) ? 'table-danger' : '' ]">{{ executive.stats.time_response }}</td>
                                <td>{{ executive.stats.orders_placed }}</td>
                                <td>{{ executive.stats.mount_orders_placed | formatPrice }}</td>
                                <td>{{ executive.stats.percent_placed }}</td>
                                <td v-bind:class="[(executive.stats.work_rate_orders > 3) ? 'table-danger' : '' ]">{{ executive.stats.work_rate_orders }}</td>
                                <td v-bind:class="[(executive.stats.work_rate > 3) ? 'table-danger' : '' ]">{{ executive.stats.work_rate }}</td>
                            </tr>
                        </tbody>
                        <tfoot v-if="quantity > 0">
                            <tr>
                                <th>REGION {{ all.region }}</th>
                                <th>{{ all.stats.all_orders }}</th>
                                <th>{{ all.stats.mount_all_orders | formatPrice }}</th>
                                <th>{{ all.stats.all_quotes }}</th>
                                <th>{{ all.stats.quotes_ok }}</th>
                                <th v-bind:class="[(all.stats.time_response < 90) ? 'table-danger' : '' ]">{{ all.stats.time_response }}</th>
                                <th>{{ all.stats.orders_placed }}</th>
                                <th>{{ all.stats.mount_orders_placed | formatPrice }}</th>
                                <th>{{ all.stats.percent_placed }}</th>
                                <th v-bind:class="[(all.stats.work_rate_orders > 3) ? 'table-danger' : '' ]">{{ all.stats.work_rate_orders }}</th>
                                <th v-bind:class="[(all.stats.work_rate > 3) ? 'table-danger' : '' ]">{{ all.stats.work_rate }}</th>
                            </tr>
                        </tfoot>
                    </table>
                    </div>

                    <download-excel :data="executives_export" :disabled="loading"
                        :fields="json_fields" worksheet="Reporte"
                        v-if="executives_export.length > 0"
                        name="area_report_executives.xls" class="btn btn-primary btn-md">
                        Descargar datos en Excel
                    </download-excel>

                    <!-- button type="button" v-on:click="downloadExcel('area_report', 'executives')" v-if="quantity > 0 && !loading" class="btn btn-primary btn-md">Descargar datos en Excel</button -->

                    <h4 class="mt-5"><strong>PRODUCTO</strong></h4>
                    <div class="table-responsive">
                    <table class="datatable table table-hover text-center table-facturacion-area">
                        <thead>
                        <tr>
                            <th scope="col">PRODUCTO</th>
                            <th scope="col">PEDIDOS RECIBIDOS</th>
                            <th scope="col">VALOR DE LOS PEDIDOS RECIBIDOS</th>
                            <th scope="col">COTIZACIONES TRABAJADAS</th>
                            <th scope="col">COTIZACIONES RESPONDIDAS A TIEMPO</th>
                            <th scope="col">INDICADOR TIEMPO DE RESPUESTA (%)</th>
                            <th scope="col">PEDIDOS CONCRETADOS</th>
                            <th scope="col">MONTO CONCRETADO</th>
                            <th scope="col">INDICADOR PEDIDOS CONCRETADOS (%)</th>
                            <th scope="col">RATIO DE TRABAJO <i class="fa fa-info-circle" v-b-tooltip title="#Recotizaciones"></i></th>
                            <th scope="col">RATIO DE TRABAJO <i class="fa fa-info-circle" v-b-tooltip title="#cotis trabajadas por file concretado"></i></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(product, k) in products">
                            <td><a href="javascript:;" v-on:click="showDetail('product', k, product)">{{ k }}</a></td>
                            <td>{{ product.stats.all_orders }}</td>
                            <td>{{ product.stats.mount_all_orders | formatPrice }}</td>
                            <td>{{ product.stats.all_quotes }}</td>
                            <td>{{ product.stats.quotes_ok }}</td>
                            <td v-bind:class="[(product.stats.time_response < 90) ? 'table-danger' : '' ]">{{ product.stats.time_response }}</td>
                            <td>{{ product.stats.orders_placed }}</td>
                            <td>{{ product.stats.mount_orders_placed | formatPrice }}</td>
                            <td>{{ product.stats.percent_placed }}</td>
                            <td v-bind:class="[(product.stats.work_rate_orders > 3) ? 'table-danger' : '' ]">{{ product.stats.work_rate_orders }}</td>
                            <td v-bind:class="[(product.stats.work_rate < 3) ? 'table-danger' : '' ]">{{ product.stats.work_rate }}</td>
                        </tr>
                        </tbody>
                        <tfoot v-if="quantity > 0">
                        <tr>
                            <th>REGION {{ all.region }}</th>
                            <th>{{ all.stats.all_orders }}</th>
                            <th>{{ all.stats.mount_all_orders | formatPrice }}</th>
                            <th>{{ all.stats.all_quotes }}</th>
                            <th>{{ all.stats.quotes_ok }}</th>
                            <th v-bind:class="[(all.stats.time_response < 90) ? 'table-danger' : '' ]">{{ all.stats.time_response }}</th>
                            <th>{{ all.stats.orders_placed }}</th>
                            <th>{{ all.stats.mount_orders_placed | formatPrice }}</th>
                            <th>{{ all.stats.percent_placed }}</th>
                            <th v-bind:class="[(all.stats.work_rate_orders > 3) ? 'table-danger' : '' ]">{{ all.stats.work_rate_orders }}</th>
                            <th v-bind:class="[(all.stats.work_rate > 3) ? 'table-danger' : '' ]">{{ all.stats.work_rate }}</th>
                        </tr>
                        </tfoot>
                    </table>
                    </div>

                    <download-excel :data="products_export" :disabled="loading"
                        :fields="json_fields" worksheet="Reporte"
                        v-if="products_export.length > 0"
                        name="area_report_products.xls" class="btn btn-primary btn-md">
                        Descargar datos en Excel
                    </download-excel>

                    <!-- button type="button" v-on:click="downloadExcel('area_report', 'products')" v-if="quantity > 0 && !loading" class="btn btn-primary btn-md">Descargar datos en Excel</button -->
                </div>
                <div v-if="detail">
                    <div class="table-responsive" v-if="quantityExecutive > 0">
                        <table class="table text-center table-facturacion-area-resume">
                            <thead>
                            <tr>
                                <th scope="col">ESPECIALISTA</th>
                                <th scope="col">PEDIDOS RECIBIDOS</th>
                                <th scope="col">VALOR DE LOS PEDIDOS RECIBIDOS</th>
                                <th scope="col">COTIZACIONES TRABAJADAS</th>
                                <th scope="col">COTIZACIONES RESPONDIDAS A TIEMPO</th>
                                <th scope="col">INDICADOR TIEMPO DE RESPUESTA (%)</th>
                                <th scope="col">PEDIDOS CONCRETADOS</th>
                                <th scope="col">MONTO CONCRETADO</th>
                                <th scope="col">INDICADOR PEDIDOS CONCRETADOS (%)</th>
                                <th scope="col">RATIO DE TRABAJO <i class="fa fa-info-circle" v-b-tooltip title="#Recotizaciones"></i></th>
                                <th scope="col">RATIO DE TRABAJO <i class="fa fa-info-circle" v-b-tooltip title="#cotis trabajadas por file concretado"></i></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(executive, k) in ordersExecutive">
                                <td>{{ k }}</td>
                                <td>{{ executive.stats.all_orders }}</td>
                                <td>{{ executive.stats.mount_all_orders | formatPrice }}</td>
                                <td>{{ executive.stats.all_quotes }}</td>
                                <td>{{ executive.stats.quotes_ok }}</td>
                                <td v-bind:class="[(executive.stats.time_response < 90) ? 'table-danger' : '' ]">{{ executive.stats.time_response }}</td>
                                <td>{{ executive.stats.orders_placed }}</td>
                                <td>{{ executive.stats.mount_orders_placed | formatPrice }}</td>
                                <td>{{ executive.stats.percent_placed }}</td>
                                <td v-bind:class="[(executive.stats.work_rate_orders > 3) ? 'table-danger' : '' ]">{{ executive.stats.work_rate_orders }}</td>
                                <td v-bind:class="[(executive.stats.work_rate > 3) ? 'table-danger' : '' ]">{{ executive.stats.work_rate }}</td>
                            </tr>
                            </tbody>
                            <tfoot v-if="quantityDetail > 0">
                            <tr>
                                <th>{{ allDetail.executive }}</th>
                                <th>{{ allDetail.stats.all_orders }}</th>
                                <th>{{ allDetail.stats.mount_all_orders | formatPrice }}</th>
                                <th>{{ allDetail.stats.all_quotes }}</th>
                                <th>{{ allDetail.stats.quotes_ok }}</th>
                                <th v-bind:class="[(allDetail.stats.time_response < 90) ? 'table-danger' : '' ]">{{ allDetail.stats.time_response }}</th>
                                <th>{{ allDetail.stats.orders_placed }}</th>
                                <th>{{ allDetail.stats.mount_orders_placed | formatPrice }}</th>
                                <th>{{ allDetail.stats.percent_placed }}</th>
                                <th v-bind:class="[(allDetail.stats.work_rate_orders > 3) ? 'table-danger' : '' ]">{{ allDetail.stats.work_rate_orders }}</th>
                                <th v-bind:class="[(allDetail.stats.work_rate > 3) ? 'table-danger' : '' ]">{{ allDetail.stats.work_rate }}</th>
                            </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="table-responsive" v-if="quantityCustomer > 0">
                        <table class="table text-center table-facturacion-area-resume">
                            <thead>
                            <tr>
                                <th scope="col">CLIENTE</th>
                                <th scope="col">PEDIDOS RECIBIDOS</th>
                                <th scope="col">VALOR DE LOS PEDIDOS RECIBIDOS</th>
                                <th scope="col">COTIZACIONES TRABAJADAS</th>
                                <th scope="col">COTIZACIONES RESPONDIDAS A TIEMPO</th>
                                <th scope="col">INDICADOR TIEMPO DE RESPUESTA</th>
                                <th scope="col">PEDIDOS CONCRETADOS</th>
                                <th scope="col">MONTO CONCRETADO</th>
                                <th scope="col">INDICADOR PEDIDOS CONCRETADOS</th>
                                <th scope="col">RATIO DE TRABAJO <i class="fa fa-info-circle" v-b-tooltip title="#Recotizaciones"></i></th>
                                <th scope="col">RATIO DE TRABAJO <i class="fa fa-info-circle" v-b-tooltip title="#cotis trabajadas por file concretado"></i></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(customer, k) in ordersCustomer">
                                <td>{{ k }}</td>
                                <td>{{ customer.stats.all_orders }}</td>
                                <td>{{ customer.stats.mount_all_orders | formatPrice }}</td>
                                <td>{{ customer.stats.all_quotes }}</td>
                                <td>{{ customer.stats.quotes_ok }}</td>
                                <td v-bind:class="[(customer.stats.time_response < 90) ? 'table-danger' : '' ]">{{ customer.stats.time_response }}</td>
                                <td>{{ customer.stats.orders_placed }}</td>
                                <td>{{ customer.stats.mount_orders_placed | formatPrice }}</td>
                                <td>{{ customer.stats.percent_placed }}</td>
                                <td v-bind:class="[(customer.stats.work_rate_orders > 3) ? 'table-danger' : '' ]">{{ customer.stats.work_rate_orders }}</td>
                                <td v-bind:class="[(customer.stats.work_rate > 3) ? 'table-danger' : '' ]">{{ customer.stats.work_rate }}</td>
                            </tr>
                            </tbody>
                            <tfoot v-if="quantityDetail > 0">
                            <tr>
                                <th>{{ allDetail.executive }}</th>
                                <th>{{ allDetail.stats.all_orders }}</th>
                                <th>{{ allDetail.stats.mount_all_orders | formatPrice }}</th>
                                <th>{{ allDetail.stats.all_quotes }}</th>
                                <th>{{ allDetail.stats.quotes_ok }}</th>
                                <th v-bind:class="[(allDetail.stats.time_response < 90) ? 'table-danger' : '' ]">{{ allDetail.stats.time_response }}</th>
                                <th>{{ allDetail.stats.orders_placed }}</th>
                                <th>{{ allDetail.stats.mount_orders_placed | formatPrice }}</th>
                                <th>{{ allDetail.stats.percent_placed }}</th>
                                <th v-bind:class="[(allDetail.stats.work_rate_orders > 3) ? 'table-danger' : '' ]">{{ allDetail.stats.work_rate_orders }}</th>
                                <th v-bind:class="[(allDetail.stats.work_rate > 3) ? 'table-danger' : '' ]">{{ allDetail.stats.work_rate }}</th>
                            </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="table-responsive" v-if="quantityProduct > 0">
                        <table class="table text-center table-facturacion-area-resume">
                            <thead>
                            <tr>
                                <th scope="col">PRODUCTO</th>
                                <th scope="col">PEDIDOS RECIBIDOS</th>
                                <th scope="col">VALOR DE LOS PEDIDOS RECIBIDOS</th>
                                <th scope="col">COTIZACIONES TRABAJADAS</th>
                                <th scope="col">COTIZACIONES RESPONDIDAS A TIEMPO</th>
                                <th scope="col">INDICADOR TIEMPO DE RESPUESTA</th>
                                <th scope="col">PEDIDOS CONCRETADOS</th>
                                <th scope="col">MONTO CONCRETADO</th>
                                <th scope="col">INDICADOR PEDIDOS CONCRETADOS</th>
                                <th scope="col">RATIO DE TRABAJO <i class="fa fa-info-circle" v-b-tooltip title="#Recotizaciones"></i></th>
                                <th scope="col">RATIO DE TRABAJO <i class="fa fa-info-circle" v-b-tooltip title="#cotis trabajadas por file concretado"></i></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(product, k) in ordersProduct">
                                <td>{{ k }}</td>
                                <td>{{ product.stats.all_orders }}</td>
                                <td>{{ product.stats.mount_all_orders | formatPrice }}</td>
                                <td>{{ product.stats.all_quotes }}</td>
                                <td>{{ product.stats.quotes_ok }}</td>
                                <td v-bind:class="[(product.stats.time_response < 90) ? 'table-danger' : '' ]">{{ product.stats.time_response }}</td>
                                <td>{{ product.stats.orders_placed }}</td>
                                <td>{{ product.stats.mount_orders_placed | formatPrice }}</td>
                                <td>{{ product.stats.percent_placed }}</td>
                                <td v-bind:class="[(product.stats.work_rate_orders > 3) ? 'table-danger' : '' ]">{{ product.stats.work_rate_orders }}</td>
                                <td v-bind:class="[(product.stats.work_rate > 3) ? 'table-danger' : '' ]">{{ product.stats.work_rate }}</td>
                            </tr>
                            </tbody>
                            <tfoot v-if="quantityDetail > 0">
                            <tr>
                                <th>{{ allDetail.executive }}</th>
                                <th>{{ allDetail.stats.all_orders }}</th>
                                <th>{{ allDetail.stats.mount_all_orders | formatPrice }}</th>
                                <th>{{ allDetail.stats.all_quotes }}</th>
                                <th>{{ allDetail.stats.quotes_ok }}</th>
                                <th v-bind:class="[(allDetail.stats.time_response < 90) ? 'table-danger' : '' ]">{{ allDetail.stats.time_response }}</th>
                                <th>{{ allDetail.stats.orders_placed }}</th>
                                <th>{{ allDetail.stats.mount_orders_placed | formatPrice }}</th>
                                <th>{{ allDetail.stats.percent_placed }}</th>
                                <th v-bind:class="[(allDetail.stats.work_rate_orders > 3) ? 'table-danger' : '' ]">{{ allDetail.stats.work_rate_orders }}</th>
                                <th v-bind:class="[(allDetail.stats.work_rate > 3) ? 'table-danger' : '' ]">{{ allDetail.stats.work_rate }}</th>
                            </tr>
                            </tfoot>
                        </table>
                    </div>

                    <button class="btn btn-primary" v-if="!loading" v-on:click="regresar()">
                        Regresar
                    </button>
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
                quantityTeams: 0,
                team: 'TODOS',
                teams: [],
                all: [],
                executives: [],
                products: [],
                detail: false,
                quantityDetail: 0,
                ordersExecutive: [],
                ordersProduct: [],
                ordersCustomer: [],
                quantityExecutive: 0,
                quantityProduct: 0,
                quantityCustomer: 0,
                allDetail: [],
                page: 1,
                pages: 1,
                orders: [],
                products_export: [],
                executives_export: [],
                json_fields: {
                    "Especialista / Producto": "index",
                    "Pedidos recibidos": "stats.all_orders",
                    "Valor de los pedidos recibidos": "stats.mount_all_orders",
                    "Cotizaciones trabajadas": "stats.all_quotes",
                    "Cotizaciones respondidas a tiempo": "stats.quotes_ok",
                    "Indicador de respuesta (%)": "stats.time_response",
                    "Pedidos concretados": "stats.orders_placed",
                    "Monto concretado": "stats.mount_orders_placed",
                    "Indicador pedidos concretados (%)": "stats.percent_placed",
                    "Ratio de trabajo (Recotizaciones)": "stats.work_rate_orders",
                    "Ratio de trabajo (Cotis trabajadas por file concretado)": "stats.work_rate"
                },
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
            searchTeams: function () {
                axios.post(
                    baseURL + 'board/teams', {
                        lang: this.lang
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
                    if(e.message == 'Unauthenticated.')
                    {
                        window.location.reload()
                    }
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

                vm.executives = await window.groupBy(orders, 'codusu')
                vm.products = await window.groupBy(orders, 'producto')
                vm.$set(vm.all, 'region', '')
                vm.$set(vm.all, 'stats', await window.allStatsOrders(orders))
                vm.quantity = orders.length

                if(vm.quantity > 0)
                {
                    vm.products_export = await window.getData(vm.products)
                    vm.executives_export = await window.getData(vm.executives)

                    vm.showTable(vm)
                }
            },
            showTable: function (vm) {
                vm.page = 'show'
                setTimeout(function() {

                    $('.table-facturacion-area').DataTable({
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
            showDetail: async function(_type, executive, items) {
                let vm = this; let quantity = [];
                vm.loading = true

                if(_type != 'executive')
                {
                    vm.ordersExecutive = await window.groupBy(items['orders'], 'codusu')
                    quantity = Object.entries(vm.ordersExecutive)
                    vm.quantityExecutive = quantity[0].length
                }

                if(_type != 'product')
                {
                    vm.ordersProduct = await window.groupBy(items['orders'], 'producto')
                    quantity = Object.entries(vm.ordersProduct)
                    vm.quantityProduct = quantity[0].length
                }

                if(_type != 'customer')
                {
                    vm.ordersCustomer = await window.groupBy(items['orders'], 'codigo')
                    quantity = Object.entries(vm.ordersCustomer)
                    vm.quantityCustomer = quantity[0].length
                }

                vm.$set(vm.allDetail, 'executive', executive)
                vm.$set(vm.allDetail, 'stats', await window.allStatsOrders(items['orders']))
                vm.quantityDetail = items['orders'].length

                setTimeout(function () {
                    vm.loading = false
                    vm.detail = true

                    // vm.showTable(vm)
                })
            },
            regresar: function () {
                this.detail = false
                this.ordersExecutive = []
                this.ordersProduct = []
                this.ordersCustomer = []
                this.allDetail = []
                this.quantityDetail = 0
                this.quantityExecutive = 0
                this.quantityCustomer = 0
                this.quantityProduct = 0
            },
            downloadExcel: function (_type, _table) {
                window.location = baseURL + 'export_excel?type=' + _type + '&table=' + _table;
            }
        }

    };
</script>
