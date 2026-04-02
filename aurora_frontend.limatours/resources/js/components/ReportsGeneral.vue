<template>
    <div>
        <div class="container">
            <div class="tabs" style="padding-left:0px !important; padding-right:0px !important;">
                <ul class="nav nav-primary text-center">
                    <li class="nav-item" style="width:33.3% !important;">
                        <a v-bind:class="[(format == 1) ? 'active' : '', 'nav-link' ]" href="javascript:;" @click="toggleView(1)"><i class="icon-bar-chart"></i>Resultado de Files Concretados</a>
                    </li>
                    <li class="nav-item" style="width:33.3% !important;">
                        <a v-bind:class="[(format == 2) ? 'active' : '', 'nav-link' ]" href="javascript:;" @click="toggleView(2)"><i class="icon-bar-chart"></i>Cotizaciones Realizadas</a>
                    </li>
                    <li class="nav-item" style="width:33.3% !important;">
                        <a v-bind:class="[(format == 3) ? 'active' : '', 'nav-link' ]" href="javascript:;" @click="toggleView(3); searchPackages()"><i class="icon-bar-chart"></i>Cantidad de Files Abiertos</a>
                    </li>
                </ul>
                <hr>
            </div>
        </div>

        <div class="container" v-if="format == 1">
            <div class="form">
                <div class="form-row justify-content-between">
                    <div class="form-group col-md-3">
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
                    <div class="form-group col-md-4">
                        <div class="text-muted mt-3">
                            <span class="form-check form-check-inline">Filtrar en:</span>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="from" v-on:click="check = 'C'; product = 'TODOS'; executive = 'TODOS';" v-model="from" id="stela" value="E" />
                                <label class="form-check-label" for="stela">Stela</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="from" v-on:click="check = 'C'; product = 'TODOS'; executive = 'TODOS';" v-model="from" id="aurora" value="A">
                                <label class="form-check-label" for="aurora">Aurora</label>
                            </div>
                        </div>
                        <div class="text-muted mt-3">
                            <span class="form-check form-check-inline">Filtrar por:</span>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="check" v-model="check" id="customer" value="C" />
                                <label class="form-check-label" for="customer">Cliente</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="check" v-model="check" id="executive" value="E">
                                <label class="form-check-label" for="executive">Especialista</label>
                            </div>

                            <div class="form-check form-check-inline" v-if="from != 'E'">
                                <input class="form-check-input" type="radio" name="check" v-model="check" id="product" value="S">
                                <label class="form-check-label" for="product">Producto</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-md-2" v-bind:disabled="check != 'S'">
                        <label>
                            <strong>Producto</strong>
                        </label>
                        <b-form-select v-model="product" :disabled="check != 'S'" :reduce="products => products.value" :options="products" class="form-control ml-1">
                        </b-form-select>
                    </div>
                    <div class="form-group col-md-3" v-bind:disabled="check != 'E'">
                        <label>
                            <strong>Especialista</strong>
                        </label>
                        <v-select label="text" :disabled="check != 'E'" :reduce="executives => executives.value" :options="executives" v-model="executive" class="form-control"></v-select>
                    </div>
                    <div class="form-group col-md-2 reporte-boton" style="margin-top:15px;">
                        <button class="btn btn-primary" v-bind:disabled="loading" v-on:click="search()">
                            Buscar
                        </button>
                    </div>
                </div>
            </div>
            <div class="alert alert-warning mt-3" v-if="loading">Cargando..</div>
            <div class="alert alert-warning mt-3" v-if="quantityOne == 0 && !loading">No se encontró información disponible.</div>
            <fusioncharts v-if="quantityOne > 0 && !loading"
                        id="mychartcontainer"
                        chartid="vueChart"
                        width="100%"
                        height="500"
                        type="column2d"
                        class="mt-3"
                        :dataSource="dataSource">
                        Cargando..
            </fusioncharts>
        </div>

        <div class="container" v-if="format == 2">
            <div class="form">
                <div class="form-row justify-content-between">
                    <div class="form-group col-md-3">
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
                    <div class="form-group col-md-4">
                        <div class="text-muted mt-3">
                            <span class="form-check form-check-inline">Filtrar en:</span>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="from" v-on:click="check = 'C'; product = 'TODOS'; executive = 'TODOS';" v-model="from" id="stela" value="E" />
                                <label class="form-check-label" for="stela">Stela</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="from" v-on:click="check = 'C'; product = 'TODOS'; executive = 'TODOS';" v-model="from" id="aurora" value="A">
                                <label class="form-check-label" for="aurora">Aurora</label>
                            </div>
                        </div>
                        <div class="text-muted mt-3">
                            <span class="form-check form-check-inline">Filtrar por:</span>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="check" v-model="check" id="customer" value="C" />
                                <label class="form-check-label" for="customer">Cliente</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="check" v-model="check" id="executive" value="E">
                                <label class="form-check-label" for="executive">Especialista</label>
                            </div>

                            <div class="form-check form-check-inline" v-if="from != 'E'">
                                <input class="form-check-input" type="radio" name="check" v-model="check" id="product" value="S">
                                <label class="form-check-label" for="product">Producto</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-md-2" v-bind:disabled="check != 'S'">
                        <label>
                            <strong>Producto</strong>
                        </label>
                        <b-form-select v-model="product" :disabled="check != 'S'" :reduce="products => products.value" :options="products" class="form-control ml-1">
                        </b-form-select>
                    </div>
                    <div class="form-group col-md-3" v-bind:disabled="check != 'E'">
                        <label>
                            <strong>Especialista</strong>
                        </label>
                        <v-select label="text" :disabled="check != 'E'" :reduce="executives => executives.value" :options="executives" v-model="executive" class="form-control"></v-select>
                    </div>
                    <div class="form-group col-md-2 reporte-boton" style="margin-top:15px;">
                        <button class="btn btn-primary" v-bind:disabled="loading" v-on:click="search()">
                            Buscar
                        </button>
                    </div>
                </div>
            </div>
            <div class="alert alert-warning mt-3" v-if="loading">Cargando..</div>
            <div class="alert alert-warning mt-3" v-if="quantityTwo == 0 && !loading">No se encontró información disponible.</div>
            <fusioncharts v-if="quantityTwo > 0 && !loading"
                          id="mychartcontainer"
                          chartid="vueChart"
                          width="100%"
                          height="500"
                          type="column2d"
                          class="mt-3"
                          :dataSource="dataSource2">
                Cargando..
            </fusioncharts>
        </div>

        <div class="container" v-if="format == 3">
            <div class="form">
                <div class="form-row justify-content-between">
                    <div class="form-group col-md-3">
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
                    <div class="form-group col-md-2" v-bind:disabled="check != 'S'">
                        <label>
                            <strong>Producto</strong>
                        </label>
                        <b-form-select v-model="product" :disabled="check != 'S'" :reduce="products => products.value" :options="products" class="form-control ml-1">
                        </b-form-select>
                    </div>
                    <div class="form-group col-md-3" v-bind:disabled="check != 'E'">
                        <label>
                            <strong>Especialista</strong>
                        </label>
                        <v-select label="text" :disabled="check != 'E'" :reduce="executives => executives.value" :options="executives" v-model="executive" class="form-control"></v-select>
                    </div>

                    <div class="form-group col-md-4" v-bind:disabled="check != 'P'">
                        <label>
                            <strong>Paquete</strong>
                        </label>
                        <v-select label="text" :disabled="check != 'P'" @search="searchPackages" :reduce="packages => packages.value" :options="packages" v-model="package" class="form-control"></v-select>
                    </div>
                </div>
                <div class="form-row justify-content-between">
                    <!-- div class="form-group mx-4" v-if="quantityTeams > 0">
                        <label>
                            <strong>Equipo</strong>
                        </label>
                        <b-form-select v-model="team" :options="teams" v-on:change="searchExecutives()" class="form-control ml-1">
                        </b-form-select>
                    </div -->
                    <div class="form-group col-md-4">
                        <label>
                            <strong>Filtrar por</strong>
                        </label>
                        <div class="text-muted mt-3">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="check" v-on:click="product = 'TODOS'; executive = 'TODOS'; package = '';" v-model="check" id="customer" value="C" />
                                <label class="form-check-label" for="customer">Cliente</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="check" v-on:click="product = 'TODOS'; package = '';" v-model="check" id="executive" value="E">
                                <label class="form-check-label" for="executive">Especialista</label>
                            </div>

                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="check" v-on:click="executive = 'TODOS'; package = '';" v-model="check" id="product" value="S">
                                <label class="form-check-label" for="product">Producto</label>
                            </div>

                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="check" v-on:click="product = 'TODOS'; executive = 'TODOS';" v-model="check" id="package" value="P">
                                <label class="form-check-label" for="package">Paquete</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-md-2 reporte-boton" style="margin-top:9px;">
                        <button class="btn btn-primary" v-bind:disabled="loading" v-on:click="search()">
                            Buscar
                        </button>
                    </div>
                    <!-- div class="form-group mx-4 reporte-boton" style="margin-top:9px;">
                        <button class="btn btn-primary" v-bind:disabled="loading || quantity == 0" v-on:click="downloadExcel()">
                            Exportar Excel
                        </button>
                    </div -->
                </div>
            </div>
            <div class="alert alert-warning mt-3" v-if="loading">Cargando..</div>
            <div class="alert alert-warning mt-3" v-if="quantityThree == 0 && !loading">No se encontró información disponible.</div>
            <fusioncharts v-if="quantityThree > 0 && !loading"
                          id="mychartcontainer"
                          chartid="vueChart"
                          width="100%"
                          height="500"
                          type="column2d"
                          class="mt-3"
                          :dataSource="dataSource3">
                Cargando..
            </fusioncharts>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['options'],
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
                from: 'A',
                format: 1, // Mostrar el primer gráfico..
                quantityProducts: 0,
                products: [],
                product: 'TODOS',
                quantityExecutives: 0,
                executives: [],
                executive: 'TODOS',
                quantityPackages: 0,
                packages: [],
                package: '',
                customer: '',
                check: 'C',
                quantityOne: 0,
                quantityTwo: 0,
                quantityThree: 0,
                dataSource: { }, // Primer Gráfico..
                dataSource2: { }, // Segundo Gráfico..
                dataSource3: { },
            }
        },
        created: function () {
            this.searchProducts()
            this.searchExecutives()
            this.lang = localStorage.getItem('lang')

            if(this.options.dateRange != undefined && this.options.dateRange != '')
            {
                this.dateRange = this.options.dateRange
            }

            if(this.options.format != undefined && this.options.format != '')
            {
                this.format = this.options.format
            }

            this.search()
        },
        mounted: function() {
            this.dataSource = {
                "chart": {
                    "showLegend": 1,
                    "formatNumberScale": 0,
                    "caption": "Files Concretados",
                    "captionFontSize": "20",
                    "captionPadding": "15",
                    "baseFontSize": "11",
                    "canvasBorderAlpha": "0",
                    "showPlotBorder": "0",
                    "placevaluesInside": "1",
                    "valueFontColor": "#000000",
                    "captionFontBold": "0",
                    "bgColor": "#ffffff",
                    "divLineAlpha": "50",
                    "plotSpacePercent": "10",
                    "bgAlpha": "95",
                    "canvasBgAlpha": "0",
                    "outCnvBaseFontColor": "#000000",
                    "showValues": "0",
                    "baseFont": "Open Sans",
                    "paletteColors": "#000",
                    "theme": "zune",

                    // tool-tip customization
                    "toolTipBorderColor": "#FFFFFF",
                    "toolTipBorderThickness": "1",
                    "toolTipBorderRadius": "2",
                    "toolTipBgColor": "#F6F6F6",
                    "toolTipBgAlpha": "70",
                    "toolTipPadding": "12",
                    "toolTipSepChar": " - ",

                    // axis customization
                    "xAxisNameFontSize": "18",
                    "yAxisNameFontSize": "18",
                    "xAxisNamePadding": "10",
                    "yAxisNamePadding": "10",
                    "xAxisName": ((this.check == 'C') ? 'Cliente' : 'Especialista'),
                    "yAxisName": "Cantidad de Files",
                    "xAxisNameFontBold": "0",
                    "yAxisNameFontBold": "0",
                    "showXAxisLine": "1",
                    "xAxisLineColor": "#999999",

                },
                "data": []
            }

            this.dataSource2 = {
                "chart": {
                    "showLegend": 1,
                    "formatNumberScale": 0,
                    "caption": "Cotizaciones Realizadas",
                    "captionFontSize": "20",
                    "captionPadding": "15",
                    "baseFontSize": "11",
                    "canvasBorderAlpha": "0",
                    "showPlotBorder": "0",
                    "placevaluesInside": "1",
                    "valueFontColor": "#000000",
                    "captionFontBold": "0",
                    "bgColor": "#ffffff",
                    "divLineAlpha": "50",
                    "plotSpacePercent": "10",
                    "bgAlpha": "95",
                    "canvasBgAlpha": "0",
                    "outCnvBaseFontColor": "#000000",
                    "showValues": "0",
                    "baseFont": "Open Sans",
                    "paletteColors": "#000",
                    "theme": "zune",

                    // tool-tip customization
                    "toolTipBorderColor": "#FFFFFF",
                    "toolTipBorderThickness": "1",
                    "toolTipBorderRadius": "2",
                    "toolTipBgColor": "#F6F6F6",
                    "toolTipBgAlpha": "70",
                    "toolTipPadding": "12",
                    "toolTipSepChar": " - ",

                    // axis customization
                    "xAxisNameFontSize": "18",
                    "yAxisNameFontSize": "18",
                    "xAxisNamePadding": "10",
                    "yAxisNamePadding": "10",
                    "xAxisName": ((this.check == 'C') ? 'Cliente' : 'Especialista'),
                    "yAxisName": "Cantidad de Cotizaciones",
                    "xAxisNameFontBold": "0",
                    "yAxisNameFontBold": "0",
                    "showXAxisLine": "1",
                    "xAxisLineColor": "#999999",

                },
                "data": []
            }

            this.dataSource3 = {
                "chart": {
                    "showLegend": 1,
                    "formatNumberScale": 0,
                    "caption": "Files Abiertos",
                    "captionFontSize": "20",
                    "captionPadding": "15",
                    "baseFontSize": "11",
                    "canvasBorderAlpha": "0",
                    "showPlotBorder": "0",
                    "placevaluesInside": "1",
                    "valueFontColor": "#000000",
                    "captionFontBold": "0",
                    "bgColor": "#ffffff",
                    "divLineAlpha": "50",
                    "plotSpacePercent": "10",
                    "bgAlpha": "95",
                    "canvasBgAlpha": "0",
                    "outCnvBaseFontColor": "#000000",
                    "showValues": "0",
                    "baseFont": "Open Sans",
                    "paletteColors": "#000",
                    "theme": "zune",

                    // tool-tip customization
                    "toolTipBorderColor": "#FFFFFF",
                    "toolTipBorderThickness": "1",
                    "toolTipBorderRadius": "2",
                    "toolTipBgColor": "#F6F6F6",
                    "toolTipBgAlpha": "70",
                    "toolTipPadding": "12",
                    "toolTipSepChar": " - ",

                    // axis customization
                    "xAxisNameFontSize": "18",
                    "yAxisNameFontSize": "18",
                    "xAxisNamePadding": "10",
                    "yAxisNamePadding": "10",
                    "xAxisName": ((this.check == 'C') ? 'Cliente' : 'Especialista'),
                    "yAxisName": "Cantidad de Files",
                    "xAxisNameFontBold": "0",
                    "yAxisNameFontBold": "0",
                    "showXAxisLine": "1",
                    "xAxisLineColor": "#999999",

                },
                "data": []
            }

            // FusionCharts.addEventListener('dataPlotClick', this.handler)
        },
        computed: {

        },
        methods: {
            toggleView: function (_format) {
                this.format = _format

                this.product = 'TODOS'
                this.executive = 'TODOS'
                this.package = ''
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
            searchPackages: function (search, loading) {
                this.loading = true

                axios.post(
                    baseURL + 'board/all_packages', {
                        lang: this.lang,
                        filter: (search != undefined) ? search : ''
                    }
                )
                    .then((result) => {
                        this.loading = false
                        this.packages = result.data.packages
                        this.quantityPackages = result.data.quantity
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            search: function () {
                if(this.check == 'C')
                {
                    this.customer = localStorage.getItem('client_code')
                }

                if(this.dateRange == '')
                {
                    this.$toast.error('Seleccione un rango de fechas para filtrar los reportes', {
                        // override the global option
                        position: 'top-right'
                    })
                    return false
                }

                if(this.check == 'C' && (this.customer == '' || this.customer == null))
                {
                    this.$toast.error('Seleccione un cliente para filtrar los reportes', {
                        // override the global option
                        position: 'top-right'
                    })
                    return false
                }

                if(this.check == 'S' && (this.product == 'TODOS' || this.product == ''))
                {
                    this.$toast.error('Seleccione un producto para filtrar los reportes', {
                        // override the global option
                        position: 'top-right'
                    })
                    return false
                }

                if(this.format == 1)
                {
                    this.loading = true
                    this.dataSource.data = [];
                    this.dataSource.chart.paletteColors = ""
                    this.dataSource.chart.xAxisName = (this.check == 'C') ? 'Cliente' : 'Especialista'

                    axios.post(
                        baseURL + 'reports/dashboard/files_concretados', {
                            lang: this.lang,
                            filter: this.from,
                            type: this.check,
                            product: this.product,
                            user: (this.check == 'E') ? this.executive : this.customer,
                            dateRange: this.dateRange
                        }
                    )
                        .then((result) => {
                            this.loading = false
                            this.users = result.data.users
                            this.quantityOne = result.data.quantity

                            if(this.quantityOne > 0)
                            {
                                let vm = this

                                setTimeout(function() {
                                    $.each(vm.users, function(i, item) {
                                        let _cantidad = item.length
                                        vm.dataSource.data.push({'label': i, 'value': _cantidad})
                                    })
                                }, 10)
                            }
                        })
                        .catch((e) => {
                            console.log(e)
                        })
                }

                if(this.format == 2)
                {
                    this.loading = true
                    this.dataSource2.data = [];
                    this.dataSource2.chart.paletteColors = ""
                    this.dataSource2.chart.xAxisName = (this.check == 'C') ? 'Cliente' : 'Especialista'

                    axios.post(
                        baseURL + 'reports/dashboard/cotizaciones_realizadas', {
                            lang: this.lang,
                            filter: this.from,
                            type: this.check,
                            product: this.product,
                            user: (this.check == 'E') ? this.executive : this.customer,
                            dateRange: this.dateRange
                        }
                    )
                        .then((result) => {
                            this.loading = false
                            this.users = result.data.users
                            this.quantityTwo = result.data.quantity

                            if(this.quantityTwo > 0)
                            {
                                let vm = this

                                setTimeout(function() {
                                    $.each(vm.users, function(i, item) {
                                        let _cantidad = item.length
                                        vm.dataSource2.data.push({'label': i, 'value': _cantidad})
                                    })
                                }, 10)
                            }
                        })
                        .catch((e) => {
                            console.log(e)
                        })
                }

                if(this.format == 3)
                {
                    this.loading = true

                    this.dataSource3.data = [];
                    this.dataSource3.chart.paletteColors = ""
                    this.dataSource3.chart.xAxisName = (this.check == 'C') ? 'Cliente' : 'Especialista'

                    axios.post(
                        baseURL + 'reports/dashboard/report_files', {
                            lang: this.lang,
                            filter: this.from,
                            type: this.check,
                            product: this.product,
                            user: (this.check == 'E') ? this.executive : this.customer,
                            package: this.package,
                            dateRange: this.dateRange
                        }
                    )
                        .then((result) => {
                            this.loading = false
                            this.quantityThree = result.data.quantity
                            this.users = result.data.users

                            if(this.quantityThree > 0)
                            {
                                let vm = this

                                setTimeout(function() {
                                    $.each(vm.users, function(i, item) {
                                        let _cantidad = item.length
                                        vm.dataSource3.data.push({'label': i, 'value': _cantidad})
                                    })
                                }, 10)
                            }
                        })
                        .catch((e) => {
                            console.log(e)
                        })
                }
            }
        }
    }
</script>
