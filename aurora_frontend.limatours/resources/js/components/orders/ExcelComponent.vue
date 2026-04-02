<template>
    <div>
        <div class="container">
            <div class="form">
                <div class="form-row justify-content-between">
                    <div class="form-group mx-4">
                        <label>
                            <strong>Tipo de Reporte</strong>
                        </label>
                        <b-form-select v-model="flag_report" :options="all_reports" class="form-control ml-1"></b-form-select>
                    </div>
                </div>
                <div class="form-row justify-content-between">
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
                    </div>
                    <div class="form-group mx-4 fecha" v-if="check == 'E' && flag_report != 'performance'">
                        <label>
                            <strong>Especialista</strong>
                        </label>
                        <v-select label="text" :disabled="check != 'E'" :reduce="executives => executives.value" :options="executives" v-model="executive" class="form-control"></v-select>
                    </div>
                    <div class="form-group mx-4" v-if="quantityProducts > 0 && check == 'P'">
                        <label>
                            <strong>Producto</strong>
                        </label>
                        <b-form-select v-model="product" :reduce="products => products.value" :options="products" class="form-control ml-1"></b-form-select>
                    </div>
                    <div class="form-group mx-4" v-if="quantityTeams > 0 && check == 'T'">
                        <label>
                            <strong>Equipo</strong>
                        </label>
                        <b-form-select v-model="team" :options="teams" v-on:change="searchExecutives()" class="form-control ml-1"></b-form-select>
                    </div>
                    <div class="form-group mx-4">
                        <label>
                            <strong>Filtrar por</strong>
                        </label>
                        <b-form-select v-model="check" :options="filters" class="form-control ml-1"></b-form-select>
                    </div>
                    <div class="form-group mx-4 reporte-boton" style="margin-top:23px;">
                        <button class="btn btn-primary" v-bind:disabled="loading" v-on:click="search()">
                            Exportar Excel
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
        </div>
    </div>
</template>

<script>

    export default {
        props: ['translations'],
        data: () => {
            return {
                loading: false,
                timePicker24Hour: false,
                showWeekNumbers: false,
                singleDatePicker: true,
                startDate: '',
                minDate: '',
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
                quantity: 0,
                teams: [],
                team: '',
                quantityTeams: 0,
                products: [],
                product: '',
                quantityProducts: 0,
                check: 'E',
                filters: {
                    'E': 'ESPECIALISTA',
                    'C' : 'CLIENTE',
                    'P': 'PRODUCTO',
                    'T': 'EQUIPO'
                },
                all_reports: {
                    'reporte_global': 'REPORTE GLOBAL',
                    'monto_estimado': 'REPORTE POR MONTO ESTIMADO',
                    'performance': 'REPORTE POR PERFORMANCE',
                    'tiempo_respuesta' : 'REPORTE POR TIEMPO DE RESPUESTA',
                    'concrecion': 'REPORTE POR CONCRECION DE PEDIDOS',
                    'ranking': 'REPORTE POR RANKING DE CLIENTES (TOP 20)'
                },
                flag_report: 'reporte_global',
                page: 1,
            }
        },
        created: function () {

        },
        mounted: function() {
            this.lang = localStorage.getItem('lang')
            this.flag = localStorage.getItem('boss')
            this.searchTeams()
            this.searchProducts()
            this.searchExecutives()
            this.customer = localStorage.getItem('client_code')
        },
        computed: {

        },
        methods: {
            searchTeams: function () {
                this.loading = true

                axios.post(
                    baseURL + 'board/teams', {
                        lang: this.lang,
                    }
                )
                    .then((result) => {
                        this.loading = false
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
            searchProducts: function () {
                this.loading = true
                axios.post(
                    baseURL + 'board/products', {
                        lang: this.lang,
                        ignore: '3'
                    }
                )
                    .then((result) => {
                        this.loading = false
                        this.products = result.data.products
                        this.quantityProducts = result.data.quantity
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            searchExecutives: function () {
                this.loading = true

                axios.post(
                    baseURL + 'board/executives_user', {
                        lang: this.lang,
                        team: this.team
                    }
                )
                    .then((result) => {
                        this.loading = false
                        this.executives = result.data.executives
                        this.quantityExecutives = result.data.quantity
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            search: function () {
                this.customer = localStorage.getItem('client_code')


            }
        }
    };
</script>
