@extends('layouts.app')
@section('content')
    <section class="page-board">
        <div class="container-fluid">
            <!-- div class="container">
                {{ trans('board.label.order_report') }}
            </div -->

            <div class="reporte-pedidos container align-content-center">
                <div class="box-report m-3">
                    <div class="d-flex justify-content-between mb-3">
                        <div>
                            {{ trans('board.label.region_summary') }}: @{{ regions }}
                        </div>
                        <div class="form-group mx-4" style="width: 250px;">
                            <date-range-picker
                                :locale-data="locale_data"
                                :time-picker24-hour="timePicker24Hour"
                                :show-week-numbers="showWeekNumbers"
                                :auto-apply="true"
                                :ranges="false"
                                :auto-apply="false"
                                v-model="dateRangeResume">
                            </date-range-picker>
                        </div>
                        <div class="form-group">
                            <button class="btn btn-primary" v-on:click="showDetailOrders()" v-bind:disabled="loading">
                                {{ trans('board.btn.search') }}
                            </button>
                        </div>
                    </div>

                    <fusioncharts v-if="!loading_budget"
                                  id="mychartcontainer_budget"
                                  chartid="vueChart_budget"
                                  width="100%"
                                  height="200"
                                  type="msbar2d"
                                  :dataSource="dataSource_budget">
                        {{ trans('board.label.loading') }}
                    </fusioncharts>

                    <div class="alert alert-warning mt-3 mb-3" v-if="loading">
                        <p class="mb-0">{{ trans('board.label.loading') }}</p>
                    </div>
                    <div class="alert alert-warning mt-3 mb-3" v-if="!loading && Object.values(sections).length == 0">
                        <p class="mb-0">{{ trans('board.label.no_data') }}</p>
                    </div>
                    <div v-if="!loading && quantityOrders > 0">
                        <hr class="hr-result mt-4" />
                        <div class="report-result">
                            <div clas="d-flex justify-content-between mb-3">
                                <div class="d-flex justify-content-around text-center">
                                    <div class="box-result d-flex justify-content-between align-items-center">
                                        <span>{{ trans('board.label.worked_quotes') }} </span> <div>@{{ all_stats.all_quotes }}</div>
                                    </div>
                                    <div class="box-result d-flex justify-content-between align-items-center">
                                        <span>{{ trans('board.label.quotes_answered_on_time') }} </span> <div>@{{ all_stats.quotes_ok }}</div>
                                    </div>
                                    <div class="box-result d-flex justify-content-between align-items-center">
                                        <span>{{ trans('board.label.work_rate') }} </span> <div>@{{ all_stats.work_rate }}</div>
                                    </div>
                                </div>

                                <div class="graphic text-center">
                                    <div class="progress" style="width:30%;margin:0 auto;">
                                        <div class="progress-bar" role="progressbar"
                                             v-bind:style="{
                                                'width': '100%',
                                                'background':('linear-gradient(to right' +
                                                ((all_stats.time_response > 0) ? (', #086EA5 0% ' + all_stats.time_response + '%') : '') +
                                                ((all_stats.time_response > 0 && all_stats.time_response < 90) ? (', #DD0100 ' + all_stats.time_response + '% 90%') : '') +
                                                ', #CCC 0% 100%' +
                                                ')'),
                                                'color': '#FFF'
                                             }"
                                             aria-valuenow="0"
                                             aria-valuemin="0"
                                             aria-valuemax="100"
                                        >
                                            <div class="descripcion" style="line-height:normal!important;">
                                                {{ trans('board.label.percentage_of_response_time') }}
                                            </div>
                                            <div class="porcentaje" style="line-height:normal!important;">
                                                @{{ all_stats.time_response }}%
                                            </div>
                                        </div>
                                    </div>

                                    <br />
                                    <a href="/dashboard?startDate={{ $date['startDate'] }}&endDate={{ $date['endDate'] }}&format=response-time" target="_blank"><strong>Visualizar reporte adicional</strong></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="box-report m-3">
                <hr class="hr-result">
                <div class="row">
                    <div v-bind:class="'col' + ((Object.values(sections).length % 2 == 0) ? '-6' : '-4')" v-for="(item, i) in sections">
                        <div class="d-flex justify-content-between mb-3">
                            <div>
                                @{{ i }} {{-- trans('board.label.orders') --}}
                            </div>
                        </div>
                        <div class="report-result">
                            <div class="d-flex justify-content-around text-center p-5">
                                <table class="table">
                                    <thead>
                                    <tr>
                                        <th scope="col"></th>
                                        <th scope="col">{{ trans('board.th.quantity') }}</th>
                                        <th scope="col">{{ trans('board.th.value') }}(USD)</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <th scope="row">{{ trans('board.th.orders_received') }}</th>
                                        <td>@{{ item.stats.all_orders }}</td>
                                        <td>@{{ item.stats.mount_all_orders }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">{{ trans('board.th.orders_placed') }}</th>
                                        <td>@{{ item.stats.orders_placed }}</td>
                                        <td>@{{ item.stats.mount_orders_placed }}</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="graphic">
                                <vc-donut
                                    :sections="graph_section[i]" :total="100"
                                    :start-angle="0" :auto-adjust-text-size="true"
                                    has-legend legend-placement="bottom">
                                    @{{ item.stats.all_orders }} {{ trans('board.th.orders_received') }}
                                </vc-donut>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <pending-files-component v-if="!loading_budget" v-bind:data="{ translations: translations, bossFlag: bossFlag }"></pending-files-component>
                </div>
                <div class="col-md-6">
                    <pending-statements-component v-if="!loading_budget" v-bind:data="{ translations: translations, bossFlag: bossFlag }"></pending-statements-component>
                </div>
            </div>
        </div>
    </section>
@endsection
@section('js')
    <script>
      new Vue({
        el: '#app',
        data: {
            update_menu:1,
            quote_date: '',
            icon: 'icon-filter-fire',
            options: {
                format: 'DD/MM/YYYY',
                useCurrent: true,
            },
            loadingModal: false,
            loading: true,
            loading_budget: true,
            dataURL: '{!! $dataURL !!}',
            dateRangeResume: {
                'startDate': '{!! $date['startDate'] !!}',
                'endDate': '{!! $date['endDate'] !!}'
            },
            timePicker24Hour: false,
            showWeekNumbers: false,
            singleDatePicker: true,
            startDate: moment().add('days', 2).format('Y-MM-DD'),
            minDate: moment().add('days', 2).format('Y-MM-DD'),
            locale_data: {
                direction: 'ltr',
                format: moment.localeData().postformat('ddd D MMM'),
                separator: ' - ',
                applyLabel: 'Guardar',
                cancelLabel: 'Cancelar',
                weekLabel: 'W',
                customRangeLabel: 'Rango de Fechas',
                daysOfWeek: moment.weekdaysMin(),
                monthNames: moment.monthsShort(),
                firstDay: moment.localeData().firstDayOfWeek()
            },
            orders: {
                'all_quotes': 0
            },
            sections: [],
            graph_section: [],
            translations: {
                label: {},
                btn: {}
            },
            bossFlag: "{!! $bossFlag !!}",
            regions: "{!! $regions !!}",
            dataSource_budget: { },
            page: 1,
        },
        created: function () {
            this.setTranslations()
            this.showDetailOrders(1, true)
        },
        mounted () {
            this.dataSource_budget = {
                "chart": {
                    "theme": "zune",
                    "caption": "AVANCE DE PRESUPUESTO",
                    "yAxisname": ""
                },
                "categories": [{
                    "category": [
                        {
                            "label": "VENTA"
                        },
                        {
                            "label": "BENEFICIO"
                        }
                    ]
                }],
                "dataset": []
            };

            console.log(FusionCharts)
            // FusionCharts.addEventListener('dataPlotClick', this.handler)
        },
        computed: {},
        methods: {
            handler: function () {
                let props = e.data
                console.log(props)
                console.log(this.items[props.dataIndex][props.datasetIndex])
            },
            setTranslations() {
                /*
                axios.get(baseURL+'translation/'+localStorage.getItem('lang')+'/slug/reports').then((data) => {
                    this.translations = data.data
                })
                 */
            },
            searchBudget: function () {
                this.loading_budget = true

                axios.post(
                    baseURL + 'search_budget', {
                        lang: this.lang,
                        region: this.regions,
                        area: ''
                    }
                )
                    .then((result) => {
                        let vm = this
                        vm.loading_budget = false

                        setTimeout(function () {

                            vm.dataSource_budget.dataset.push({
                                'data': [
                                    {
                                        'value': result.data.venta.ejecutado,
                                        'color': '#FBC73B',
                                        'valueFontColor': '#FFFFFF'
                                    },
                                    {
                                        'value': result.data.beneficio.ejecutado,
                                        'color': '#FBC73B',
                                        'valueFontColor': '#FFFFFF'
                                    }
                                ],
                                'color': '#FBC73B',
                                'seriesname': 'EJECUTADO'
                            });

                            vm.dataSource_budget.dataset.push({
                                'data': [
                                    {
                                        'value': result.data.venta.esperado,
                                        'color': '#FE812A',
                                        'valueFontColor': '#FFFFFF'
                                    },
                                    {
                                        'value': result.data.beneficio.esperado,
                                        'color': '#FE812A',
                                        'valueFontColor': '#FFFFFF'
                                    }
                                ],
                                'color': '#FE812A',
                                'seriesname': 'ESPERADO'
                            });

                            vm.dataSource_budget.dataset.push({
                                'data': [
                                    {
                                        'value': result.data.venta.presupuesto_anual,
                                        'color': '#DD0100',
                                        'valueFontColor': '#FFFFFF'
                                    },
                                    {
                                        'value': result.data.beneficio.presupuesto_anual,
                                        'color': '#DD0100',
                                        'valueFontColor': '#FFFFFF'
                                    }
                                ],
                                'color': '#DD0100',
                                'valueFontColor': '#FFFFFF',
                                'seriesname': 'PRESUPUESTO ANUAL'
                            });
                        }, 10)
                    })
                    .catch((e) => {
                        this.loading = false
                        console.log(e)
                    })
            },
            showDetailOrders: function (page, _return) {
                if(this.dateRangeResume == '')
                {
                    this.$toast.error('Seleccione un rango de fechas para poder filtrar los reportes', {
                        // override the global option
                        position: 'top-right'
                    })
                    return false
                }

                if(page == undefined)
                {
                    this.page = 1
                }
                else
                {
                    this.page = page
                }

                this.loading = true
                this.quantityOrders = 0
                this.all_stats = []
                this.sections = []

                axios.post(
                    baseURL + 'search_orders', {
                        lang: this.lang,
                        type: 'E',
                        user: '',
                        status: 'ALL',
                        dateRange: this.dateRangeResume,
                        team: '',
                        product: '',
                        limit: this.page
                    }
                )
                    .then((result) => {
                        let _quantity = result.data.quantity

                        if(this.page == 'show')
                        {
                            this.loading = false
                            this.graph_section = []
                            this.sections = []

                            axios.post(
                                baseURL + 'board/orders', {
                                    lang: this.lang
                                }
                            )
                                .then((result) => {
                                    let vm = this
                                    this.loading = false
                                    this.quantityOrders = result.data.quantity
                                    this.all_stats = result.data.all_stats
                                    this.sections = result.data.sections

                                    Object.entries(vm.sections).forEach(([key, value]) => {

                                        let porcentaje_meta = (value.limit - value.stats.percent_placed > 0) ? value.limit - value.stats.percent_placed : 0
                                        let cantidad_pendiente = (value.__orders > value.stats.orders_placed) ? value.__orders - value.stats.orders_placed : 0

                                        Vue.set(vm.graph_section, key, eval(
                                            "[" +
                                            "{ label: '" + value.stats.percent_placed + "% META REALIZADA (" + value.stats.orders_placed + ")', value: " +  value.stats.percent_placed + ", color: '#FBC73B' }," +
                                            "{ label: '" + (porcentaje_meta) + "% META PENDIENTE (" + (cantidad_pendiente) + ")', value: " +  (porcentaje_meta) + ", color: '#DD0100' }" +
                                            "]"
                                        ));
                                        // eval("vm.graph_sections.push({'" + key + "':  });");
                                        // eval("this.dataQuotes = [['Porcentaje en STELA', " + this.orders.percent_stela_quotes + "], ['Porcentaje de AURORA', " + this.orders.percent_aurora_quotes + "]]")
                                    });

                                    if(_return != undefined)
                                    {
                                        this.searchBudget()
                                    }
                                })
                                .catch((e) => {
                                    console.log(e)
                                    this.loading = false

                                    if(e.message == 'Unauthenticated.')
                                    {
                                        window.location.reload()
                                    }
                                })
                        }
                        else
                        {
                            if(_quantity == 0)
                            {
                                this.page = 'show'
                            }
                            else
                            {
                                this.page = this.page + 1
                            }

                            this.showDetailOrders(this.page, _return)
                        }
                    })
                    .catch((e) => {
                        this.loading = false
                        console.log(e)
                    })
            }
        }
      })
    </script>
@endsection
