<template>
    <section>
        <div class="container p-2 p-md-5" style="position: relative;">
            <div class="alert alert-warning text-justify">
                <strong>Importante:</strong>
                Las estadísticas solo estarán habilitadas a partir de Octubre del 2022. Debido a unos ajustes en los cuadros estadísticos.
            </div>
            <h2 class="py-5 d-inline-block">Masi: Reportes Estadísticos</h2>
            <div class="loader ml-4" v-show="loading">
                <span class="spinner-border" role="status" aria-hidden="true"></span>
            </div>
            <div class="filters pb-5">
                <div class="form-row mb-0">
                    <div class="form-group col-md-2 py-2">
                        <label for="product">PRODUCTO:</label>
                        <v-select class="w-100 form-control p-2"
                                :options="products" :reduce="product => product.code" :filterable="true"
                                placeholder="TODOS" v-bind:disabled="loading || loading_button"
                                v-model="filter.product" style="min-width:150px;">
                            <template slot="option" slot-scope="option">
                                <div class="d-center">
                                    {{ option.label }}
                                </div>
                            </template>
                            <template slot="selected-option" slot-scope="option">
                                <div class="selected d-center">
                                    {{ option.label }}
                                </div>
                            </template>
                        </v-select>
                    </div>
                    <div class="form-group col-md-3 py-2">
                        <label for="region">REGION:</label>
                        <v-select class="w-100 form-control p-2" @input="setRegion()"
                                :options="regions" :reduce="region => region.id" :filterable="true"
                                placeholder="TODOS" label="title" code="id" v-bind:disabled="loading || loading_button"
                                v-model="filter.region" style="min-width:150px;">
                            <template slot="option" slot-scope="option">
                                <div class="d-center">
                                    {{ option.title }}
                                </div>
                            </template>
                            <template slot="selected-option" slot-scope="option">
                                <div class="selected d-center">
                                    {{ option.title }}
                                </div>
                            </template>
                        </v-select>
                    </div>
                    <div class="form-group col-md-3 py-2">
                        <label for="country">PAIS:</label>
                        <v-select class="w-100 form-control p-2" @input="setCountry()" v-bind:disabled="loading || loading_button"
                                :options="countries" label="pais" code="pais" :reduce="country => country.pais" :filterable="true"
                                placeholder="TODOS" v-model="filter.country" style="min-width:150px;">
                            <template slot="option" slot-scope="option">
                                <div class="d-center">
                                    {{ option.pais }}
                                </div>
                            </template>
                            <template slot="selected-option" slot-scope="option">
                                <div class="selected d-center">
                                    {{ option.pais }}
                                </div>
                            </template>
                        </v-select>
                    </div>
                    <div class="form-group col-md-4 py-2">
                        <label for="client">CLIENTE:</label>
                        <v-select class="w-100 form-control p-2" v-bind:disabled="loading || loading_button"
                                :options="clients" :reduce="client => client.codigo" :filterable="true"
                                placeholder="TODOS" label="razon" code="codigo" v-model="filter.client" style="min-width:150px;">
                            <template slot="option" slot-scope="option">
                                <div class="d-center">
                                    {{ option.razon }}
                                </div>
                            </template>
                            <template slot="selected-option" slot-scope="option">
                                <div class="selected d-center">
                                    {{ option.razon }}
                                </div>
                            </template>
                        </v-select>
                    </div>
                </div>
                <!-- input type="hidden" name="selectClient" id="selectClient" v-model="clientFilter" -->
                <div class="form-row mb-0">
                    <div class="form-group col-md-4 py-2">
                        <label for="from">DESDE:</label>
                        <input type="date" class="form-control" id="from" v-model="filter.from"
                               :max="getEndDate()" min="2019-01-01" />
                    </div>
                    <div class="form-group col-md-4 py-2">
                        <label for="to">HASTA:</label>
                        <input type="date" class="form-control" id="to" v-model="filter.to"
                               :max="getEndDate()" :min="filter.from" />
                    </div>
                    <div class="form-group col-md-4 py-2 text-right mt-3">
                        <button type="button" v-on:click="clearFilters()"
                                class="btn btn-secondary"
                                style="width:max-content;height: 52px;padding: 0 30px;font-size: 17px;"
                                :disabled="loading || loading_button">LIMPIAR
                        </button>
                        <button type="button" v-on:click="searchReport()"
                                class="btn btn-primary btn-update"
                                :disabled="loading || loading_button">FILTRAR
                        </button>
                    </div>
                    <div class="col-md-12 pb-0 pt-3 px-3 mb-0">
                        <small>El rango de fechas seleccionado hace referencia a la llegada del pasajero.</small>
                        <small v-show="currentTab==2"><br>Para consultas estadísticas de
                            notificaciones, se sugiere realizar consultas con 1 mes de diferencia en el rango de
                            fechas</small></div>
                </div>
                <div class="divider"></div>
                <div class="form-row mb-0">
                    <div class="form-group col-md-4 py-2">
                        <label for="fileSearch" class="mb-3">FILTRAR POR NRO DE FILE:</label>
                        <div class="file-search">
                            <input :disabled="currentTab==2 || loading || loading_button" type="number" class="form-control"
                                   name="fileSearch" id="fileSearch" v-model="filter.file">
                            <button :disabled="currentTab==2 || loading || loading_button" type="button" class="btn btn-clear-file"
                                    v-if="filter.file != ''">
                                    <i class="icon-trash"></i></button>
                            <button type="button"
                                    :disabled="loading || loading_button || currentTab==2"
                                    v-on:click="searchReport()" class="btn btn-danger btn-search">
                                    <i class="icon-search"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tabs-container">
                <div class="loader-container" v-if="loading">
                    <div class="loading-spinner">
                        <div class="bar-loader">
                            <div></div>
                            <div></div>
                            <div></div>
                        </div>
                    </div>
                    <div class="loading-message">
                        <transition name="fade">
                            <p>Consideraciones: Consultas de mayor de rango de fechas resultan en
                                mayor
                                tiempo de espera de
                                respuesta.</p>
                        </transition>
                    </div>
                </div>
                <b-tabs v-model="currentTab">
                    <b-tab title="GENERAL" active>
                        <div class="statistics py-5 px-3" v-if="!loading">
                            <div class="statistics-container py-5">
                                <div class="graph-container row no-gutters" v-if="dashboard_general.total > 0">
                                    <div class="col-md-12 px-0">
                                        <h2 class="statistics-title mb-1 mt-3 mt-md-0">
                                            Files con o sin datos para uso de MASI
                                        </h2>

                                        <div class="table-responsive">
                                            <table class="datatable table table-bordered b-none text-center table-hover">
                                                <thead>
                                                    <tr class="bg-dark text-white">
                                                        <th>ESTADO</th>
                                                        <th class="text-right">CANTIDAD DE FILES</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>Files sin datos</td>
                                                        <td class="text-right">{{ dashboard_general.without_data_count }}
                                                            (<b>{{ parseFloat(dashboard_general.without_data_count * 100 / dashboard_general.total).toFixed(2) }}%</b>)
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>Con datos comercial</td>
                                                        <td class="text-right">{{ dashboard_general.with_data_count }}
                                                            (<b>{{ parseFloat(dashboard_general.with_data_count * 100 / dashboard_general.total).toFixed(2) }}%</b>)
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>Con datos operaciones/landing</td>
                                                        <td class="text-right">{{ dashboard_general.with_data_count_modal }}
                                                            (<b>{{ parseFloat(dashboard_general.with_data_count_modal * 100 / dashboard_general.total).toFixed(2) }}%</b>)
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="2" class="column-totals text-right"><b>TOTAL: {{ dashboard_general.total }}</b></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="alert bg-light shadow-sm my-4"
                                            style="border: none; border-left: 5px solid #dc3545; border-radius: 4px;">

                                            <div class="media">
                                                <div class="mr-3">
                                                    <span style="font-size: 2rem; color: #dc3545; line-height: 0.5rem;">&bull;</span>
                                                </div>

                                                <div class="media-body">
                                                    <h5 class="font-weight-bold text-dark text-uppercase mb-2" style="letter-spacing: 0.5px;">
                                                        Consideraciones del Reporte
                                                    </h5>

                                                    <div class="text-secondary" style="font-size: 1.2rem; line-height: 1.6;">
                                                        <div class="d-flex mb-1">
                                                            <span class="mr-2 text-danger">&rsaquo;</span>
                                                            <span>
                                                                Al seleccionar el tipo de file <strong class="text-dark">"TODOS"</strong>: se incluyen todas las categorías (FITS y Grupos).
                                                            </span>
                                                        </div>

                                                        <div class="d-flex mb-1">
                                                            <span class="mr-2 text-danger">&rsaquo;</span>
                                                            <span>
                                                                Los resultados <strong class="text-dark">no incluyen</strong> los acompañamientos de especialistas en files.
                                                            </span>
                                                        </div>

                                                        <div class="d-flex">
                                                            <span class="mr-2 text-danger">&rsaquo;</span>
                                                            <span>
                                                                No se consideran los <strong class="text-dark">files opcionales</strong> dentro de este reporte.
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12 text-right" v-if="dashboard_general.all_files.length > 0">
                                        <download-excel :data="dashboard_general.all_files" :disabled="loading"
                                            :fields="json_fields" worksheet="Reporte de Datos - MASI"
                                            name="masi_report_general.xls" class="btn btn-primary">
                                            Descargar relación
                                        </download-excel>
                                    </div>
                                </div>
                                <div class="no-data-message" v-else>
                                    <h2 class="statistics-title mb-5 mt-3 mt-md-0">Files con o sin datos para uso de
                                        MASI
                                    </h2>
                                    <h3 class="w-100 text-center">No se encontraron datos en los filtros
                                        seleccionados</h3>
                                </div>
                            </div>
                        </div>
                    </b-tab>
                    <b-tab title="CHATBOT">
                        <div class="statistics" v-if="currentTab == 1 && !loading">
                            <div class="statistics-container py-5">
                                <div class="graph-container row no-gutters" v-if="dashboard_chatbot.chatbot.total > 0">
                                    <!-- div class="col-md-4 px-0" -->
                                        <!-- pie-chart :data="[]" suffix="%" legend="bottom"
                                                    :colors="['rgb(255, 99, 132)', 'rgb(54, 162, 235)' , 'rgb(255, 205, 86)']"
                                                    :library="{
                                        layout:{
                                            padding:{
                                            }
                                        },
                                        legend:{ display: true},
                                        responsive: true,
                                        tooltips: {
                                            callbacks: {
                                                label: function(tooltipItem, data) {
                                                    const total = data.datasets[0].data.reduce((a, b) => a + b)
                                                    const label = data.labels[tooltipItem.index];
                                                    const value = data.datasets[0].data[tooltipItem.index];
                                                    let per = value/total * 100;
                                                    per = Math.round(per * 100) / 100
                                                    return `${label}: ${per} %`;
                                                }
                                            }
                                        }
                                    }"></pie-chart -->
                                    <!-- /div -->
                                    <div class="col-md-12 px-0">
                                        <h2 class="statistics-title mb-5 mt-3 mt-md-0">Porcentaje de uso del chatbot</h2>
                                        <div class="table-responsive">
                                            <table class="datatable table table-bordered b-none text-center">
                                                <thead>
                                                <th>FILES</th>
                                                <th>OPCIONES</th>
                                                <th>WEB</th>
                                                <th>WHATSAPP</th>
                                                <th>MESSENGER</th>
                                                <th>CANTIDAD USO</th>
                                                <th>INDICADOR</th>
                                                </thead>
                                                <tbody>
                                                <template v-for="(intent, i) in dashboard_chatbot.intents">
                                                    <tr v-bind:key="'intent-' + i">
                                                        <td>{{ dashboard_chatbot.chatbot[i].quantity_files }}</td>
                                                        <td>{{ intent.content[0].value }}</td>
                                                        <td>{{ dashboard_chatbot.chatbot[i].web }}</td>
                                                        <td>{{ dashboard_chatbot.chatbot[i].whatsapp }}</td>
                                                        <td>{{ dashboard_chatbot.chatbot[i].messenger }}</td>
                                                        <td>{{ dashboard_chatbot.chatbot[i].quantity }}</td>
                                                        <td>{{ parseFloat(dashboard_chatbot.chatbot[i].quantity * 100 / dashboard_chatbot.chatbot.total).toFixed(2) }}%</td>
                                                    </tr>
                                                </template>
                                                <tr>
                                                    <td class="column-totals"></td>
                                                    <td class="column-totals"></td>
                                                    <td class="column-totals"></td>
                                                    <td class="column-totals"></td>
                                                    <td class="column-totals">TOTAL</td>
                                                    <td class="column-totals">
                                                        {{ dashboard_chatbot.chatbot.total }}
                                                    </td>
                                                    <td class="column-totals">100%
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <p class="data-legend">
                                            - NÚMERO DE FILES: Representa la cantidad de files únicos que usaron el
                                            canal
                                            del
                                            chatbot.
                                        </p>
                                    </div>
                                </div>
                                <div class="no-data-message5" v-else>
                                    <h2 class="statistics-title mb-5 mt-3 mt-md-0">Porcentaje de uso del chatbot</h2>
                                    <h3 class="w-100 text-center">No se encontraron registros de uso en los filtros
                                        seleccionados</h3>
                                </div>
                            </div>
                        </div>
                    </b-tab>
                    <b-tab title="NOTIFICACIONES">
                        <div class="statistics py-5 px-3" v-if="currentTab == 2 && !loading">
                            <div class="statistics-container py-5">
                                <div class="graph-container row no-gutters"
                                    v-if="dashboard_mailing.quantity_mailing > 0 || dashboard_mailing.quantity_wsp > 0">
                                    <h3 class="text-center mb-5 col-12">Total de files con
                                        datos para
                                        uso de MASI:
                                        <br>
                                        <span class="font-weight-bold">Celular:
                                    @{{dashboard_mailing.quantity_wsp}}</span><br>
                                        <span class="font-weight-bold">Correo:
                                    @{{dashboard_mailing.quantity_mailing}}</span><br />
                                    <span class="font-weight-bold">Clientes:
                                    @{{dashboard_mailing.quantity_people}}</span>
                                    </h3>
                                    <div class="col-md-12 px-0">
                                        <h2 class="statistics-title mb-5 mt-3 mt-md-0">Estadísticas de
                                            notificaciones
                                            enviadas por correo y Whatsapp</h2>
                                        <div class="table-responsive">
                                            <table class="datatable table table-bordered b-none text-center">
                                                <thead>
                                                <th colspan="2"></th>
                                                <th>1 SEMANA ANTES</th>
                                                <th>1 DÍA ANTES</th>
                                                <th>PRIMER DÍA</th>
                                                <th>MENSAJE DE DESPEDIDA<br>(ENCUESTA)</th>
                                                </thead>
                                                <tbody>
                                                <!-- tr>
                                                    <td class="border-1" rowspan="2">WHATSAPP</td>
                                                    <td class="border-1">ENVIADOS</td>
                                                    <td class="border-1">{{ dashboard_mailing.quantity_wsp_weekly }}</td>
                                                    <td class="border-1">{{ dashboard_mailing.quantity_wsp_day_before }}</td>
                                                    <td class="border-1">{{ dashboard_mailing.quantity_wsp_daily }}
                                                    </td>
                                                    <td class="border-1">{{ dashboard_mailing.quantity_wsp_survey }}</td>
                                                </tr -->
                                                <template v-for="(item, i) in wsp_options">
                                                    <tr v-bind:key="'mailing-option-' + i">
                                                        <td v-if="i == 'sent'"
                                                            v-bind:rowspan="Object.entries(wsp_options).length"
                                                            class="align-middle">WHATSAPP</td>
                                                        <td>{{ item }}</td>
                                                        <td>{{ showOption('wsp', i, 'weekly') }}</td>
                                                        <td>{{ showOption('wsp', i, 'day_before') }}</td>
                                                        <td>{{ showOption('wsp', i, 'daily') }}</td>
                                                        <td>{{ showOption('wsp', i, 'survey') }}</td>
                                                    </tr>
                                                </template>
                                                <template v-for="(item, i) in mailing_options">
                                                    <tr v-bind:key="'mailing-option-' + i">
                                                        <td v-if="i == 'delivered'"
                                                            v-bind:rowspan="Object.entries(mailing_options).length"
                                                            class="align-middle">MAILING</td>
                                                        <td>{{ item }}</td>
                                                        <td>{{ showOption('mailing', i, 'weekly') }}</td>
                                                        <td>{{ showOption('mailing', i, 'day_before') }}</td>
                                                        <td>{{ showOption('mailing', i, 'daily') }}</td>
                                                        <td>{{ showOption('mailing', i, 'survey') }}</td>
                                                    </tr>
                                                </template>
                                                <tr>
                                                    <td class="border-0"></td>
                                                    <td class="border-0"></td>
                                                    <td class="border-0"></td>
                                                    <td class="border-0"></td>
                                                    <td class="column-totals">TOTAL NOTIFICACIONES ENVIADAS:</td>
                                                    <td class="column-totals">{{ parseFloat(dashboard_mailing.quantity_global) }}</td>
                                                </tr>
                                                </tbody>
                                            </table>
                                            <button type="button" v-on:click="downloadReportMailing()"
                                                class="btn btn-primary" v-bind:disabled="loading_button || loading">
                                                Descargar relación
                                            </button>
                                            <!-- p>
                                                - % RECIBIDOS, REBOTADOS Y OTROS: El porcentaje se calculó sobre la
                                                cantidad
                                                de correos de
                                                estado "ENVIADOS".<br>
                                                - % ABIERTOS: El porcentaje se calculó sobre la cantidad de correos
                                                de
                                                estado "RECIBIDOS".<br>
                                                - % CLICKS: El porcentaje se calculó sobre la cantidad de correos de
                                                estado
                                                "ABIERTOS".<br>
                                                - ESTADO OTROS: Refiere a aquellos que no se ha enviado
                                                correctamente el
                                                correo, por error tipográfico o <br>
                                                respuesta fallida de la plataforma.<br>
                                                - CLICKS y ABIERTOS: En el caso de que la cantidad de "ABIERTOS" sea
                                                menor o
                                                nula en comparación al a cantidad de<br>
                                                "CLICKS", puede deberse a que el usuario abrió un adjunto del correo
                                                desde
                                                la vista previa en el listado de correos.<br>
                                                - Porcentaje ND: Refiere a "No determinado", debido a que el cálculo
                                                porcentaje se ha intentado sobre una cantidad nula.
                                            </p -->
                                        </div>
                                    </div>
                                </div>
                                <div class="no-data-message" v-else>
                                    <h2 class="statistics-title mb-5 mt-3 mt-md-0">Estadísticas de notificaciones
                                        enviadas
                                        por correo y Whatsapp</h2>
                                    <h3 class="w-100 text-center">No se encontraron datos en los filtros
                                        seleccionados</h3>
                                </div>
                            </div>
                        </div>
                    </b-tab>
                </b-tabs>
            </div>
        </div>
    </section>
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
        props: ['data'],
        data: () => {
            return {
                days: 21,
                loading: false,
                loading_button: false,
                filter: {
                    country: '',
                    region: '',
                    product: '',
                    client: '',
                    from: '',
                    to: '',
                    file: ''
                },
                products: [
                    { code: '', label: 'TODOS' },
                    { code: 1, label: 'FITS' },
                    { code: 2, label: 'GRUPOS' }
                ],
                countries: [],
                regions: [],
                clients: [],
                lang: '',
                currentTab: 0,
                mailing_options: {
                    'delivered': 'ENVIADOS',
                    'error': 'NO ENTREGADOS',
                    'invalid_email': 'EMAIL INVALIDO',
                    'unique_opened': 'ABIERTOS',
                    'click': 'CLICK REALIZADO',
                    'soft_bounce': 'REBOTADOS',
                    'hard_bounce': 'OTROS',
                },
                wsp_options: {
                    'sent': 'ENVIADOS',
                    'delivered': 'ENTREGADOS',
                    'failed': 'NO ENTREGADOS',
                    'read': 'LEIDOS',
                },
                dashboard_general: {
                    total: 0,
                    all_files: []
                },
                dashboard_mailing: {},
                dashboard_chatbot: {
                    chatbot: {
                        total: 0
                    }
                },
                json_fields: {
                    "File": "file",
                    "Área": "region",
                    "Producto": "product",
                    "Nombre Producto" : "type",
                    "Cliente": "client",
                    "País": "country",
                    "Especialista": "executive",
                    "Con Datos/ Sin Datos": "flag_data",
                    "Datos ingresados desde": "flag_from",
                    "Fecha FILE": "arrive_date",
                    "Area" : "area",
                },
            }
        },
        watch: {

        },
        created: function () {
            this.lang = localStorage.getItem('lang')

            this.filter.from = moment().subtract(this.days, 'days').format('YYYY-MM-DD')
            this.filter.to = moment().format('YYYY-MM-DD')

            this.searchRegions()
            this.searchReport()
        },
        mounted: function () {

        },
        computed: {

        },
        methods: {
            getEndDate: function () {
                return moment().format('YYYY-MM-DD')
            },
            showOption: function (_format, _option, _type) {
                return this.dashboard_mailing['quantity_' + _format + '_' + _option + '_' + _type]
            },
            searchRegions: function () {
                axios.get(baseExternalURL + 'api/masi_statistics/regions', {
                    lang: this.lang
                }).then((result) => {
                    this.regions = result.data.regions
                }).catch((error) => {
                    this.$toast.error('Ocurrieron errores al conseguir la información de las regiones', {
                        position: 'top-right'
                    })
                })
            },
            setRegion: function () {
                let vm = this

                setTimeout(() => {
                    vm.searchCountries()
                }, 10)
            },
            searchCountries: function () {
                this.filter.country = ''
                this.countries = []

                if(this.filter.region > 0)
                {
                    this.loading_button = true

                    axios.get(baseExternalURL + `api/masi_statistics/regions/${this.filter.region}/countries`)
                        .then((result) => {
                            this.loading_button = false
                            this.countries = result.data.data
                            this.searchClients()
                        })
                        .catch((error) => {
                            this.loading_button = false
                            this.$toast.error('Ocurrieron errores al conseguir la información de los países', {
                                position: 'top-right'
                            })
                        })
                }
            },
            searchClients: function () {
                this.filter.client = ''
                this.clients = []

                if(this.filter.country != '')
                {
                    this.loading_button = true

                    axios.get(baseExternalURL + `api/masi_statistics/regions/${this.filter.region}/countries/${this.filter.country}/clients`)
                        .then((result) => {
                            this.loading_button = false
                            this.clients = result.data.data
                        })
                        .catch((error) => {
                            this.loading_button = false
                            this.$toast.error('Ocurrieron errores al conseguir la información de los clientes', {
                                position: 'top-right'
                            })
                        })
                }
            },
            setCountry: function () {
                let vm = this

                setTimeout(() => {
                    vm.searchClients()
                }, 10)
            },
            clearFilters: function () {
                this.filter.region = ''
                this.filter.country = ''
                this.filter.client = ''
                this.filter.download = 0
                this.countries = []
                this.clients = []
            },
            searchReport: function () {
                if(this.currentTab == 0)
                {
                    this.searchReportGeneral()
                }

                if(this.currentTab == 1)
                {
                    this.searchReportChatbot()
                }

                if(this.currentTab == 2)
                {
                    this.searchReportMailing()
                }
            },
            downloadReportMailing: function () {

                this.loading_button = true

                Vue.set(this.filter, 'download', 1)
                axios.get(baseExternalURL + 'api/masi_statistics/mailing', {
                    params: this.filter,
                    responseType: "blob"
                }).then((result) => {
                    Vue.set(this.filter, 'download', 0)
                    this.loading_button = false

                    var fileURL = window.URL.createObjectURL(new Blob([result.data]))
                    var fileLink = document.createElement('a')
                    fileLink.href = fileURL
                    fileLink.setAttribute('download', 'Reporte-Mailing.xls')
                    document.body.appendChild(fileLink)
                    fileLink.click()
                })
                    .catch((error) => {
                        this.loading_button = false
                    })
                // window.location.href = baseExternalURL + 'api/masi_statistics/mailing?' + JSON.stringify(this.filter)
            },
            searchReportGeneral: function () {
                this.loading = true

                axios.get(baseExternalURL + 'api/masi_statistics/general', {
                    params: this.filter
                }).then((result) => {
                    this.loading = false
                    Vue.set(this, 'dashboard_general', result.data)
                })
                    .catch((error) => {
                        this.loading = false
                        this.$toast.error('Ocurrieron errores al conseguir la información', {
                            position: 'top-right'
                        })
                    })
            },
            searchReportChatbot: function () {
                this.loading = true

                axios.get(baseExternalURL + 'api/masi_statistics/chatbot', {
                    params: this.filter
                }).then((result) => {
                    this.loading = false
                    Vue.set(this, 'dashboard_chatbot', result.data)
                })
                    .catch((error) => {
                        this.loading = false
                        this.$toast.error('Ocurrieron errores al conseguir la información', {
                            position: 'top-right'
                        })
                    })
            },
            searchReportMailing: function () {
                this.loading = true

                axios.get(baseExternalURL + 'api/masi_statistics/mailing', {
                    params: this.filter
                }).then((result) => {
                    this.loading = false
                    Vue.set(this, 'dashboard_mailing', result.data)
                })
                    .catch((error) => {
                        this.loading = false
                        this.$toast.error('Ocurrieron errores al conseguir la información', {
                            position: 'top-right'
                        })
                    })
            },

        }
    }
</script>
