<template>
    <div>
        <div class="container">
            <div class="form">
                <div class="form-row justify-content-between">
                    <div class="col-md-1"></div>
                    <div class="form-group col-md-3">
                        <label><strong>{{ translations.label.date_range }}</strong></label>
                        <date-range-picker
                            :locale-data="locale_data"
                            :time-picker24-hour="timePicker24Hour"
                            :show-week-numbers="showWeekNumbers"
                            :ranges="false"
                            :auto-apply="true"
                            v-model="dateRange">
                        </date-range-picker>
                    </div>
                    <div class="form-group col-md-2">
                        <label><strong>{{ translations.label.product }}</strong></label>
                        <b-form-select v-model="product" :reduce="products => products.value" :options="products" class="form-control ml-1">
                        </b-form-select>
                    </div>
                    <div class="form-group col-md-2">
                        <label><strong>{{ translations.label.team }}</strong></label>
                        <b-form-select v-model="team" :options="teams" class="form-control ml-1">
                        </b-form-select>
                    </div>
                    <div class="form-group col-md-3">
                        <button class="btn btn-primary" v-bind:disabled="loading" v-on:click="search()" style="margin-top:23px;">
                            {{ translations.btn.search }}
                        </button>
                    </div>
                    <div class="col-md-1"></div>
                </div>
            </div>

            <div class="alert alert-warning" v-if="loading">{{ translations.label.loading }}</div>
            <div class="alert alert-warning" v-if="!loading && quantity == 0">{{ translations.label.no_data }}</div>
            <fusioncharts v-if="!loading && quantity > 0"
                id="mychartcontainer"
                chartid="vueChart"
                width="100%"
                height="1000"
                type="msbar2d"
                :dataSource="dataSource">
                {{ translations.label.loading }}
            </fusioncharts>

            <div class="detail_reporte_ejecutivo" v-show="detail == true">
                <h6>{{ translations.label.order_detail }}</h6>

                <p>{{ translations.label.specialist }}: <strong>{{ executive_reporte_ejecutivo }}</strong></p>

                <div v-for="(customer, key) in customer_reporte_ejecutivo">
                    <hr />
                    <p>{{ translations.label.customer }}: <strong>{{ customer.CODIGO }} - {{ customer.CLIENTE }}</strong></p>
                    <p>{{ translations.label.order_nro }}: <strong>{{ customer.NROPED }}</strong></p>
                    <p>{{ translations.label.state }}: <strong>{{ customer.ESTADO }}</strong></p>
                </div>

                <div class="alert alert-warning" v-show="customer_reporte_ejecutivo.length == 0">No hay pedidos asignados.</div>
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
                lang: '',
                loading: false,
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
                teams: [],
                team: '',
                quantityTeams: 0,
                products: [],
                product: '',
                quantityProducts: 0,
                quantity: 0,
                dateRange: '',
                filtro: '',
                status: 'ALL',
                // graph
                dataSource: { },
                detail: false,
                detail_customers_ejecutivo: [],
                customer_reporte_ejecutivo: [],
                executive_reporte_ejecutivo: '',
                page: 1
            }
        },
        created: function () {

        },
        mounted: function() {
            this.lang = localStorage.getItem('lang')
            this.searchTeams()
            this.searchProducts()
            this.customer = localStorage.getItem('client_code')

            this.dataSource = {
                "chart": {
                    "theme": "zune",
                    "caption": "Reporte de Carga de Trabajo por Ejecutivo",
                    "yAxisname": "Cantidad de Pedidos y Files"
                },
                "categories": [{
                    "category": []
                }],
                "dataset": []
            };

            FusionCharts.addEventListener('dataPlotClick', this.handler)
        },
        computed: {

        },
        methods: {
            handler: function(e) {
                let props = e.data

                this.detail = true;
                this.customer_reporte_ejecutivo = this.detail_customers_ejecutivo[props.categoryLabel];
                this.executive_reporte_ejecutivo = props.categoryLabel;
            },
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
                    }
                )
                    .then((result) => {
                        this.loading = false
                        this.quantityProducts = result.data.quantity
                        this.products = result.data.products
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            search: function (page) {

                if(page == undefined)
                {
                    this.page = 1
                }

                this.customer = localStorage.getItem('client_code')

                if(this.dateRange == '')
                {
                    this.$toast.error('Seleccione un rango de fechas para poder filtrar los reportes', {
                        // override the global option
                        position: 'top-right'
                    })
                    return false
                }

                if(this.check == 'C' && (this.customer == '' || this.customer == null))
                {
                    this.customer = 'TODOS'
                    this.team = ''
                }

                if(this.check == 'E' && this.executive == '')
                {
                    this.executive = 'TODOS'
                    this.team = ''
                }

                if(this.check == 'T')
                {
                    this.customer = 'TODOS'
                }

                this.loading = true
                this.dataSource.dataset = []
                this.dataSource.categories[0].category = []

                axios.post(
                    baseURL + 'search_orders', {
                        lang: this.lang,
                        type: 'E',
                        user: 'TODOS',
                        status: 'ALL',
                        dateRange: this.dateRange,
                        team: this.team,
                        product: this.product,
                        limit: this.page,
                        flag_report: 1
                    }
                )
                    .then((result) => {

                        if(result.data.quantity != undefined)
                        {
                            this.quantity = result.data.quantity

                            if (this.page == 'show') {
                                axios.post(
                                    baseURL + 'report_by_executive', {
                                        lang: this.lang,
                                        product: this.product,
                                        dateRange: this.dateRange,
                                        team: this.team
                                    }
                                )
                                    .then((result) => {
                                        this.loading = false
                                        this.quantity = result.data.quantity
                                        this.detail_customers_ejecutivo = result.data.DETAIL

                                        let vm = this

                                        setTimeout(function () {

                                            vm.dataSource.dataset.push({
                                                'data': [],
                                                'seriesname': 'Cantidad de Pedidos - Totales',
                                                'color': '#8A0808'
                                            });
                                            vm.dataSource.dataset.push({
                                                'data': [],
                                                'seriesname': 'Cantidad de Pedidos - Pendientes',
                                                'color': '#E54444'
                                            });
                                            vm.dataSource.dataset.push({
                                                'data': [],
                                                'seriesname': 'Cantidad de Files',
                                                'color': '#088A08'
                                            });

                                            $.each(result.data.ITEMS, function (i, item) {
                                                // Dibujando el elemento..
                                                vm.dataSource.categories[0].category.push({'label': i});
                                                vm.dataSource.dataset[0].data.push({'value': item.ORDERS_TOTALES});
                                                vm.dataSource.dataset[1].data.push({'value': item.ORDERS_PENDIENTES});
                                                vm.dataSource.dataset[2].data.push({'value': item.FILES});
                                            });
                                        }, 10)
                                    })
                                    .catch((e) => {
                                        this.loading = false
                                        console.log(e)
                                    })
                            }
                            else
                            {
                                if(this.quantity == 0)
                                {
                                    this.page = 'show'
                                }
                                else
                                {
                                    this.page = this.page + 1
                                }

                                this.search(this.page)
                            }
                        }
                        else
                        {
                            this.loading = false
                        }
                    })
                    .catch((e) => {
                        this.loading = false
                        console.log(e)
                    })
            }
        }
    };
</script>
