<template>
    <div>
        <div class="container">
            <div class="form">
                <div class="form-row justify-content-between">
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
                    <div class="form-group col-md-3" v-bind:disabled="check != 'E'">
                        <label><strong>{{ translations.label.specialist }}</strong></label>
                        <v-select label="text" :reduce="executives => executives.value" v-bind:disabled="check != 'E'" :options="executives" v-model="executive" class="form-control"></v-select>
                    </div>
                    <div class="form-group col-md-2" v-bind:disabled="check != 'T'">
                        <label><strong>{{ translations.label.team }}</strong></label>
                        <b-form-select v-model="team" v-bind:disabled="check != 'T'" :options="teams" class="form-control ml-1">
                        </b-form-select>
                    </div>
                    <div class="form-group col-md-2">
                        <button class="btn btn-primary" v-bind:disabled="loading" v-on:click="search()" style="margin-top:23px;">
                            {{ translations.btn.search }}
                        </button>
                    </div>
                </div>
                <div class="form-row justify-content-between" style="margin-top:-15px;">
                    <div class="form-group col-md-6">
                        <div class="text-muted mt-3">
                            <div class="form-check form-check-inline">
                                <label>{{ translations.label.made_in }}:</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="filtro" v-model="filtro" id="todos" value="" />
                                <label class="form-check-label" for="todos">{{ translations.label.all }}</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="filtro" v-model="filtro" id="stela" value="E" />
                                <label class="form-check-label" for="stela">Stela</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="filtro" v-model="filtro" id="aurora" value="A" />
                                <label class="form-check-label" for="aurora">Aurora</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="filtro" v-model="filtro" id="concretado" value="C" />
                                <label class="form-check-label" for="concretado">{{ translations.label.orders_placed_without_quotation }}</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-md-6">
                        <div class="text-muted mt-3">
                            <div class="form-check form-check-inline">
                                <label>{{ translations.label.filter_by }}:</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="check" v-model="check" id="customer" value="C" />
                                <label class="form-check-label" for="customer">{{ translations.label.customer }}</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="check" v-model="check" id="executive" value="E" />
                                <label class="form-check-label" for="executive">{{ translations.label.specialist }}</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="check" v-model="check" id="team" value="T" />
                                <label class="form-check-label" for="team">{{ translations.label.team }}</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="alert alert-warning" v-if="loading">{{ translations.label.loading }}</div>
            <div class="alert alert-warning" v-if="!loading && quantity == 0">{{ translations.label.no_data }}</div>

            <template v-if="page == 'show'">

                <h4 class="p-0 mr-0 ml-0">Monto Estimado por Pedidos</h4>

                <div id="chart-container">
                    <bar-chart
                        id="bar"
                        :data="dataGraph"
                        resize="true"
                        :xkey="xkey"
                        :ykeys=ykeys
                        :labels="labelsGraph"
                        grid="true"
                        horizontal="true"
                        :height="height"
                    >
                        {{ translations.label.loading }}
                    </bar-chart>
                </div>

                <div class="detail_no_concretado" v-show="detail">
                    <h4 style="margin:0; margin-top:2rem!important; margin-bottom:2rem!important;">{{ translations.label.order_detail }}</h4>
                    <p>{{ translations.label.order_nro }}: {{ detalle.nroped }}</p>
                    <p>{{ translations.label.specialist }}: {{ detalle.codusu }}</p>
                    <p>{{ translations.label.customer }}: {{ detalle.codigo }} - {{ detalle.razon }}</p>
                    <p>{{ translations.label.estimated_amount }}: {{ detalle.price_estimated }}</p>
                    <p>{{ translations.label.travel_date }}: {{ (detalle.fectravel_tca != '' && detalle.fectrtavel_tca != null) ? (detalle.fectravel_tca) : (detalle.fectravel) }}</p>
                    <p>{{ translations.label.order_response_date }}: {{ detalle.fecres }}</p>
                    <p>{{ translations.label.product }}: {{ detalle.producto }}</p>
                </div>
            </template>
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
        props: ['options'],
        data: () => {
            return {
                // GRAPH
                dataGraph: [],
                xkey: 'label',
                ykeys: ['value'],
                labelsGraph: ['Monto estimado'],
                lang: '',
                page: 0,
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
                executives: [],
                executive: '',
                quantityExecutives: 0,
                quantity: 0,
                quantityTeams: 0,
                products: [],
                product: '',
                quantityProducts: 0,
                dateRange: '',
                filtro: '',
                status: 'ALL',
                check: 'E',
                detail: false,
                detalle: [],
                height: 0,
            }
        },
        created: function () {
            if(this.options.dateRange != undefined && this.options.dateRange != '')
            {
                this.dateRange = this.options.dateRange
            }

            this.translations = this.options.translations
        },
        mounted: function() {
            this.lang = localStorage.getItem('lang')
            this.searchTeams()
            this.searchExecutives()
            this.searchProducts()
            this.customer = localStorage.getItem('client_code')
        },
        computed: {

        },
        methods: {
            handler: function (e) {
                let props = e.data

                this.detail = false;
                let vm = this;

                $.each(vm.orders, function(i, item){
                    if(item['nroped'] == props.categoryLabel)
                    {
                        console.log(item);
                        vm.detail = true;
                        vm.detalle = item;
                        /*
                        $.each(vm.all_sectors, function(j, jtem){
                            if(jtem.CODGRU == item.CODSEC)
                            {
                                $scope._detalle_no_concretado.PRODUCTO = jtem.DESCRI;
                            }
                        });
                        return;
                        */
                    }
                });
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
            searchExecutives: function () {
                this.executive = ''
                this.quantityExecutives = 0

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
            searchProducts: function () {
                axios.post(
                    baseURL + 'board/products', {
                        lang: this.lang,
                    }
                )
                    .then((result) => {
                        this.quantityProducts = result.data.quantity
                        this.products = result.data.products
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            search: function (page) {
                this.customer = localStorage.getItem('client_code')

                if(page == undefined)
                {
                    this.page = 1
                }

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
                }

                if(this.check == 'E' && this.executive == '')
                {
                    this.executive = 'TODOS'
                }

                if(this.check == 'T')
                {
                    this.customer = 'TODOS'
                }

                this.loading = true
                this.dataGraph = []

                axios.post(
                    baseURL + 'search_orders', {
                        lang: this.lang,
                        type: this.check,
                        user: (this.check == 'C') ? this.customer : this.executive,
                        status: this.status,
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

                            if(this.page == 'show')
                            {
                                this.loading = false
                                this.orders = result.data.orders

                                let vm = this

                                vm.orders.forEach((item, i) => {
                                    if(item['class'] != '') // Es cualquier sector menos del sector 3
                                    {
                                        if(vm.filtro == '' || (vm.filtro == 'E' && (item['nroref_tipmov'] == 'C' || item['nrofile_tipmov'] == 'F')) || (vm.filtro == 'A' && (item['nroref_tipmov'] == 'D' || item['nrofile_tipmov'] == 'G')) || (vm.filtro == 'C' && item['nrofile'] != '' && (item['nroref'] == '' || item['nroref'] == null)))
                                        {
                                            if(vm.filtro == 'C' || (vm.filtro != 'C' && (item['nrofile'] == '' || item['nrofile'] == null))) // No tiene que tener file..
                                            {
                                                let price = (item['price_estimated'] > 0) ? item['price_estimated'] : 0

                                                vm.dataGraph.push({
                                                    label: item['nroped'].toString(),
                                                    value: parseFloat(price).toFixed(2)
                                                });
                                            }
                                        }
                                    }
                                })

                                setTimeout(function(){
                                    vm.height = vm.dataGraph.length * 40
                                    $('#bar').find('svg')
                                        .attr('height', vm.height)
                                        .css('height', vm.height + 'px')

                                    setTimeout(function () {
                                        $(window).trigger('resize')
                                    }, 10)
                                }, 10)
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
