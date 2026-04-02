<template>
    <div class="vld-parent">
        <loading :active.sync="loading" :can-cancel="false" color="#BD0D12"></loading>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-3">
                    <select ref="period" class="form-control" id="year_from" required size="0" v-model="year_from">
                        <option :value="year.value" v-for="year in years">
                            {{ year.text}}
                        </option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select ref="period" class="form-control" id="year_to" required size="0" v-model="year_to">
                        <option :value="year.value" v-for="year in years">
                            {{ year.text}}
                        </option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-success" @click="duplicate">Duplicar</button>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-3 text-left" style="padding-top: 20px;">
                    <span class="switch-label">Lista</span>
                    <span class="two-way-switch">
                    <input id="switch" type="checkbox" v-model="showCalendar"/><label for="switch">Toggle</label>
                </span>
                    <span class="switch-label">Calendario</span>
                </div>
            </div>
            <div class="row" v-if="showCalendar">
                <Calendar></Calendar>
            </div>
            <div class="row" v-if="!showCalendar">

                <div class="row col-12">
                    <div class="col-12">
                        <div class="row">
                            <div class="col-6 pull-right">
                                <div class="b-form-group form-group">
                                    <div class="form-row">
                                        <label class="col-sm-2 col-form-label" for="period">{{
                                            $t('clientsmanageclienthotel.period') }}</label>
                                        <div class="col-sm-1.5">
                                            <select @change="searchPeriod" ref="period" class="form-control" id="period"
                                                    required
                                                    size="0" v-model="selectPeriod">
                                                <option :value="year.value" v-for="year in years">
                                                    {{ year.text}}
                                                </option>
                                            </select>
                                        </div>
                                        <label class="col-sm-2 col-form-label text-right" for="period">
                                            {{ $t('clientsmanageclienthotel.market') }}
                                        </label>
                                        <div class="col-sm-5">
                                            <select @change="searchPeriod" ref="period" class="form-control" id="mark"
                                                    required
                                                    size="0" v-model="selectMarkets">
                                                <option value="">Todos</option>
                                                <option :value="market.value" v-for="market in markets">
                                                    {{ market.text}}
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                    <div class="col-5">
                        <label class="col-sm-12 col-form-label" for="period">{{
                            $t('clientsmanageclienthotel.available_clients')
                            }}</label>
                        <div class="input-group">
                 <span class="input-group-append">
                    <button class="btn btn-outline-secondary button_icon" type="button">
                        <font-awesome-icon :icon="['fas', 'search']"/>
                    </button>
                 </span>
                            <input class="form-control" id="search_hotels" type="search" v-model="query" value="">
                        </div>
                        <ul class="style_list_ul" id="list_hotels" style="background-color: #4dbd7429;">
                            <draggable :list="hotels">
                                <li :class="{'style_list_li':true, 'item':true, 'selected':hotel.selected}"
                                    :id="'hotel_'+index"
                                    @click="selectHotel(hotel,index)" v-for="(hotel,index) in hotels">
                                <span class="style_span_li">
                                    {{ hotel.name}} ({{ hotel.markup ? (hotel.markup == "0" ? " no definido " : hotel.markup) : ((porcentage == "0" || porcentage == "") ? ' no definido ': porcentage)}}%)
                                </span>
                                </li>
                            </draggable>
                        </ul>
                    </div>
                    <div class="col-1 mt-4 mr-2">
                        <div class="col-12">
                            <button @click="moveOneHotel()" class="btn btn-secondary mover_controls btn-block pr-3">
                                <font-awesome-icon :icon="['fas', 'angle-right']"/>
                            </button>
                        </div>
                        <div class="col-12">
                            <button @click="moveAllHotels()" class="btn btn-secondary mover_controls btn-block pr-3">
                                <font-awesome-icon :icon="['fas', 'angle-double-right']"/>
                            </button>
                        </div>
                        <div class="col-12">
                            <button @click="inverseOneHotel()" class="btn btn-secondary mover_controls btn-block pr-3">
                                <font-awesome-icon :icon="['fas', 'angle-left']"/>
                            </button>
                        </div>
                        <div class="col-12">
                            <button @click="inverseAllHotels()" class="btn btn-secondary mover_controls btn-block pr-3">
                                <font-awesome-icon :icon="['fas', 'angle-double-left']"/>
                            </button>
                        </div>
                    </div>
                    <div class="col-5">
                        <label class="col-sm-12 col-form-label" for="period">{{
                            $t('clientsmanageclienthotel.clients_locked')
                            }}</label>
                        <div class="input-group">
                <span class="input-group-append">
                    <button class="btn btn-outline-secondary button_icon" type="button">
                        <font-awesome-icon :icon="['fas', 'search']"/>
                    </button>
                </span>
                            <input class="form-control" id="search_hotels_selected" type="search"
                                   v-model="query_hotels_selected"
                                   value="">
                        </div>
                        <ul class="style_list_ul" id="list_hotels_selected" style="background-color: #bd0d121c;">
                            <draggable :list="hotels_selected" class="list-group">
                                <li :class="{'style_list_li':true, 'item':true, 'selected':hotel.selected}"
                                    @click="selectHotelHotelsSelected(hotel,index)"
                                    v-for="(hotel,index) in hotels_selected">
                                    <span class="style_span_li">{{ hotel.name}}</span>
                                </li>
                            </draggable>
                        </ul>
                    </div>
                    <div class="col-10">
                        <div class="row">
                            <div class="col-md-4 pb-2">
                                <label>Aplicar a todos:</label>
                                <b-form-checkbox
                                    :checked="checkboxChecked(apply_all)"
                                    style="float:left !important"
                                    id="checkbox_status"
                                    name="checkbox_status"
                                    @change="changeState(apply_all)"
                                    switch>
                                </b-form-checkbox>

                            </div>
                        </div>
                        <div class="row">
                            <div class="col-5 pull-left">
                                <div class="b-form-group form-group">
                                    <div class="form-row">
                                        <label class="col-sm-6 col-form-label" for="markup">{{
                                            $t('clientsmanageclienthotel.personal_markup') }}</label>
                                        <div class="col-sm-6">
                                            <input :class="{'form-control':true }"
                                                   id="markup" name="markup"
                                                   type="text"
                                                   ref="auroraCodeName" v-model="markup"
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-5">
                                <button @click="updateOneHotel" class="btn btn-success mb-4" type="reset">
                                    {{ $t('clientsmanageclienthotel.update') }}
                                </button>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>


                    <!-- Bloque de tarifas asociadas a los hoteles -->
                    <div class="col-5">
                        <label class="col-sm-12 col-form-label" for="period">{{
                            $t('clientsmanageclienthotel.available_hotels_rates')
                            }}</label>

                        <ul class="style_list_ul" id="list_rates"
                            style="border-top:1px solid #ccc;max-height: 196px;height: 196px;background-color: #4dbd7429;">
                            <draggable :list="rates">
                                <li :class="{'style_list_li':true, 'item':true, 'selected':rate.selected}"
                                    :id="'rate_'+index"
                                    @click="selectRate(rate,index)" v-for="(rate,index) in rates">
                                    <span class="style_span_li">{{ rate.name }} ({{ rate.markup ? (rate.markup == "0" ? ' no definido ': rate.markup) : (selectMarkup() == "0" ? ' no definido ' : selectMarkup()) }}%)</span>
                                </li>
                            </draggable>
                        </ul>
                    </div>
                    <div class="col-1 mt-4 mr-2">
                        <div class="col-12">
                            <button @click="moveOneRate()" class="btn btn-secondary mover_controls btn-block pr-3">
                                <font-awesome-icon :icon="['fas', 'angle-right']"/>
                            </button>
                        </div>
                        <div class="col-12">
                            <button @click="moveAllRates()" class="btn btn-secondary mover_controls btn-block pr-3">
                                <font-awesome-icon :icon="['fas', 'angle-double-right']"/>
                            </button>
                        </div>
                        <div class="col-12">
                            <button @click="inverseOneRate()" class="btn btn-secondary mover_controls btn-block pr-3">
                                <font-awesome-icon :icon="['fas', 'angle-left']"/>
                            </button>
                        </div>
                        <div class="col-12">
                            <button @click="inverseAllRates()" class="btn btn-secondary mover_controls btn-block pr-3">
                                <font-awesome-icon :icon="['fas', 'angle-double-left']"/>
                            </button>
                        </div>
                    </div>
                    <div class="col-5">
                        <label class="col-sm-12 col-form-label" for="period">{{
                            $t('clientsmanageclienthotel.hotels_rates_blocked')
                            }}</label>

                        <ul class="style_list_ul" id="list_rates_selected"
                            style="border-top:1px solid #ccc;max-height: 196px;height: 196px;background-color: #bd0d121c;">
                            <draggable :list="rates_selected" class="list-group">
                                <li :class="{'style_list_li':true, 'item':true, 'selected':rate.selected}"
                                    @click="selectRateRatesSelected(rate,index)" v-for="(rate,index) in rates_selected">
                                    <span class="style_span_li">{{ rate.name}}</span>
                                </li>
                            </draggable>
                        </ul>
                    </div>

                    <div class="col-10">
                        <div class="row">
                            <div class="col-5 pull-left">
                                <div class="b-form-group form-group">
                                    <div class="form-row">
                                        <label class="col-sm-6 col-form-label" for="markup">{{
                                            $t('clientsmanageclienthotel.personal_markup') }}</label>
                                        <div class="col-sm-6">
                                            <input :class="{'form-control':true }"
                                                   id="markup_rate" name="markup_rate"
                                                   type="text"
                                                   ref="auroraCodeName" v-model="markup_rate"
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-5">
                                <button @click="updateOneRate" class="btn btn-success mb-4" type="reset">
                                    {{ $t('clientsmanageclienthotel.update') }}
                                </button>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

</template>
<script>
    import { API } from './../../../../../api'
    import draggable from 'vuedraggable'
    import TableClient from './../../../../../components/TableClient'
    import BFormCheckbox from 'bootstrap-vue/es/components/form-checkbox/form-checkbox'
    import Calendar from './Calendar'
    import Loading from 'vue-loading-overlay'
    import 'vue-loading-overlay/dist/vue-loading.css'

    export default {
        components: {
            draggable,
            'table-client': TableClient,
            BFormCheckbox,
            Loading,
            Calendar
        },
        data () {
            return {
                showCalendar: false,
                users: [],
                scroll_limit: 2900,
                hotels: [],
                rates: [],
                markets: [],
                page: 1,
                selectPeriod: '',
                selectMarkets: '',
                selectRatePlan: '',
                nameSelectHotel: '',
                porcentage: '',
                markupId: '',
                markup: '',
                markup_rate: '',
                periods: [],
                clientRates: [],
                clientRatesSelected: [],
                limit: 100,
                count: 0,
                num_pages: 1,
                query: '',
                query_rates: '',
                interval: null,
                hotels_selected: [],
                rates_selected: [],
                page_hotels_selected: 1,
                limit_hotels_selected: 100,
                count_hotels_selected: 0,
                num_pages_hotels_selected: 1,
                query_hotels_selected: '',
                query_rates_selected: '',
                scroll_limit_hotels_selected: 2900,
                interval_hotels_selected: null,
                loading: false,
                apply_all: false,
                table: {
                    columns: ['name', 'markup', 'delete']
                },
                year_from: moment().format('YYYY'),
                year_to: moment().add(1, 'year').format('YYYY'),
            }
        },
        computed: {
            tableOptions: function () {
                return {
                    headings: {
                        id: 'ID',
                        name: this.$i18n.t('clientsmanageclienthotel.associated_fees'),
                        markup: this.$i18n.t('clientsmanageclienthotel.personal_markup'),
                        delete: this.$i18n.t('global.buttons.delete')
                    },
                    sortable: ['id'],
                    filterable: []
                }
            },
            years () {
                let previousYear = moment().subtract(2, 'years').year()
                let currentYear = moment().add(5, 'years').year()

                let years = []

                do {
                    years.push({ value: previousYear, text: previousYear })
                    previousYear++
                } while (currentYear > previousYear)

                return years
            }
        },
        mounted: function () {
            let currentDate = new Date()
            this.selectPeriod = currentDate.getFullYear()

            //markets
            API.get('/markets/selectbox?lang=' + localStorage.getItem('lang'))
                .then((result) => {

                    this.markets = result.data.data

                }).catch(() => {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('clients.error.messages.name'),
                    text: this.$t('clients.error.messages.connection_error')
                })
            })

            let search_hotels = document.getElementById('search_hotels')
            let timeout_hotels
            search_hotels.addEventListener('keydown', () => {
                clearTimeout(timeout_hotels)
                timeout_hotels = setTimeout(() => {
                    this.getClients()
                    clearTimeout(timeout_hotels)
                }, 1000)
            })

            let search_hotels_selected = document.getElementById('search_hotels_selected')
            let timeout_hotels_selected
            search_hotels_selected.addEventListener('keydown', () => {
                clearTimeout(timeout_hotels_selected)
                timeout_hotels_selected = setTimeout(() => {
                    this.getClientsLocked()
                    clearTimeout(timeout_hotels_selected)
                }, 1000)
            })

            this.interval = setInterval(this.getScrollTop, 3000)
            this.interval_hotels_selected = setInterval(this.getScrollTopHotelsSelected, 3000)

            this.getClients()
            this.getClientsLocked()

        },
        methods: {
            duplicate () {
                if (this.year_from === '' || this.year_to === '') {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Error',
                        text: 'Faltan datos por llenar'
                    })
                } else {
                    this.$root.$emit('blockPage', { message: 'Cargando..' })
                    let data = {
                        year_from: this.year_from,
                        year_to: this.year_to,
                    }
                    this.loading = true
                    API.post('service/' + this.$route.params.service_id + '/rates/sale/duplicate', data)
                        .then((result) => {
                            this.$root.$emit('unlockPage')
                            this.loading = false
                            if (result.data.success) {
                                this.$notify({
                                    group: 'main',
                                    type: 'success',
                                    title: 'Tarifas',
                                    text: 'Se duplico de manera exitosa.'
                                })
                                this.getClientsLocked()
                            } else {
                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: 'Tarifas',
                                    text: 'Error interno'
                                })
                            }
                        }).catch((e) => {
                        console.log(e)
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('hotelsmanagehotelratesratescost.error.messages.name'),
                            text: this.$t('hotelsmanagehotelratesratescost.error.messages.connection_error')
                        })
                        this.loading = false
                        this.$root.$emit('unlockPage')
                    })
                }
            },
            checkboxChecked: function (apply_all) {
                if (apply_all) {
                    return 'true'
                } else {
                    return 'false'
                }
            },
            changeState: function (apply_all) {
                this.apply_all = !apply_all
            },
            showError: function (title, text, isLoading = true) {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: title,
                    text: text
                })
                if (isLoading === true) {
                    this.loading = true
                }
            },
            searchPeriod: function () {
                this.getClients()
                this.getClientsLocked()
                this.searchMarkup()
                this.searchMarkupId()
            },
            searchMarkup: function () {
                // let data = this.periods.find(period => period.text == this.selectPeriod)
                // this.porcentage = data.porcen_hotel
            },
            searchMarkupId: function () {
                // let data = this.periods.find(period => period.text == this.selectPeriod)
                // this.markupId = data.value
            },
            validateMarkup: function () {
                if (this.markup == '') {
                    this.showError(
                        this.$t('clientsmanageclienthotel.title'),
                        this.$t('clientsmanageclienthotel.error.messages.add_markup')
                    )
                    return false
                }
            },
            validateMarkupRate: function () {
                if (this.markup_rate == '') {
                    this.showError(
                        this.$t('clientsmanageclienthotel.title'),
                        this.$t('clientsmanageclienthotel.error.messages.add_markup')
                    )
                    return false
                }
            },
            validatePeriod: function () {
                if (this.selectPeriod == '') {
                    this.showError(
                        this.$t('clientsmanageclienthotel.title'),
                        this.$t('clientsmanageclienthotel.error.messages.select_period')
                    )
                    return false
                }
            },
            selectHotel: function (hotel, index) {
                this.apply_all = false
                if (this.hotels[index].selected) {
                    this.$set(this.hotels[index], 'selected', false)
                } else {

                    this.getClientRatePlans(hotel.service_id, hotel.client_id)
                    this.getClientRatePlanSelected(hotel.service_id, hotel.client_id)
                    this.nameSelectHotel = hotel.name
                    this.setPropertySelectedInHotels()
                    this.$set(this.hotels[index], 'selected', true)
                    this.markup = hotel.markup
                }
            },
            selectHotelHotelsSelected: function (hotel, index) {
                if (this.hotels_selected[index].selected) {
                    this.$set(this.hotels_selected[index], 'selected', false)
                } else {
                    this.setPropertySelectedInHotelsSelected()
                    this.$set(this.hotels_selected[index], 'selected', true)
                }
            },
            selectRate: function (hotel, index) {
                if (this.rates[index].selected) {
                    this.$set(this.rates[index], 'selected', false)
                } else {
                    this.setPropertySelectedInRates()
                    this.$set(this.rates[index], 'selected', true)
                }
            },
            selectRateRatesSelected: function (hotel, index) {
                if (this.rates_selected[index].selected) {
                    this.$set(this.rates_selected[index], 'selected', false)
                } else {
                    this.setPropertySelectedInRatesSelected()
                    this.$set(this.rates_selected[index], 'selected', true)
                }
            },
            searchSelectHotel: function () {
                for (let i = 0; i < this.hotels.length; i++) {
                    if (this.hotels[i].selected) {
                        return i
                        break
                    }
                }
                return -1
            },
            searchSelectRate: function () {
                for (let i = 0; i < this.rates.length; i++) {
                    if (this.rates[i].selected) {
                        return i
                        break
                    }
                }
                return -1
            },

            searchSelectHotelHotelsSelected: function () {
                for (let i = 0; i < this.hotels_selected.length; i++) {
                    if (this.hotels_selected[i].selected) {
                        return i
                        break
                    }
                }
                return -1
            },
            searchSelectRateRatesSelected: function () {
                for (let i = 0; i < this.rates_selected.length; i++) {
                    if (this.rates_selected[i].selected) {
                        return i
                        break
                    }
                }
                return -1
            },
            setPropertySelectedInHotels: function () {
                for (let i = 0; i < this.hotels.length; i++) {
                    this.$set(this.hotels[i], 'selected', false)
                }
            },
            setPropertySelectedInHotelsSelected: function () {
                for (let i = 0; i < this.hotels_selected.length; i++) {
                    this.$set(this.hotels_selected[i], 'selected', false)
                }
            },
            setPropertySelectedInRates: function () {
                for (let i = 0; i < this.rates.length; i++) {
                    this.$set(this.rates[i], 'selected', false)
                }
            },
            setPropertySelectedInRatesSelected: function () {
                for (let i = 0; i < this.rates_selected.length; i++) {
                    this.$set(this.rates_selected[i], 'selected', false)
                }
            },
            selectMarkup: function () {
                let search_hotel = this.searchSelectHotel()
                if (search_hotel !== -1) {
                    let hotels = this.hotels[search_hotel]
                    if (hotels.markup) {
                        return hotels.markup === 0;
                    } else {
                        return this.porcentage
                    }
                }
            },
            moveOneHotel: function () {
                if (this.loading === false) {
                    if (this.validatePeriod() != false) {
                        this.searchMarkup()

                        this.loading = true
                        let search_hotel = this.searchSelectHotel()
                        if (search_hotel !== -1) {
                            API({
                                method: 'post',
                                url: 'client_services/store',
                                data: {
                                    data: this.hotels[search_hotel],
                                    period: this.selectPeriod,
                                    market: this.selectMarkets,
                                    porcentage: this.porcentage
                                }
                            })
                                .then((result) => {
                                    if (result.data.success === true) {

                                        this.$set(this.hotels[search_hotel], 'selected', false)
                                        this.hotels[search_hotel].service_client_id = result.data.service_client_id
                                        this.hotels[search_hotel].markup = this.porcentage
                                        this.hotels_selected.push(this.hotels[search_hotel])
                                        this.hotels.splice(search_hotel, 1)
                                        this.rates = []
                                        this.rates_selected = []
                                        this.loading = false
                                    }
                                }).catch(() => {
                                this.showError(
                                    this.$t('global.modules.services'),
                                    this.$t('global.error.messages.connection_error')
                                )
                            })
                        } else {
                            if (this.hotels.length > 0) {
                                this.loading = true
                                let element = this.hotels.shift()
                                API({
                                    method: 'post',
                                    url: 'client_services/store',
                                    data: {
                                        data: element,
                                        period: this.selectPeriod,
                                        market: this.selectMarkets,
                                        porcentage: this.porcentage
                                    }
                                })
                                    .then((result) => {
                                        if (result.data.success === true) {
                                            element.service_client_id = result.data.service_client_id
                                            this.hotels_selected.push(element)
                                            this.rates = []
                                            this.rates_selected = []
                                            this.loading = false
                                        }
                                    }).catch((e) => {
                                    this.showError(
                                        this.$t('global.modules.services'),
                                        this.$t('global.error.messages.connection_error') + e
                                    )
                                })
                            }
                        }

                    }
                } else {
                    console.log('Bloqueado accion')
                }
            },
            moveOneRate: function () {

                if (this.loading === false) {
                    if (this.validatePeriod() != false) {
                        this.searchMarkup()

                        this.loading = true
                        let search_hotel = this.searchSelectHotel()
                        let search_rate = this.searchSelectRate()
                        if (search_rate !== -1) {
                            API({
                                method: 'post',
                                url: 'service_client_rate_plans/store',
                                data: {
                                    service_rate_id: this.rates[search_rate].id,
                                    client_id: this.hotels[search_hotel].client_id,
                                    period: this.selectPeriod,
                                    market: this.selectMarkets,
                                    porcentage: this.porcentage
                                }
                            })
                                .then((result) => {
                                    if (result.data.success === true) {

                                        this.$set(this.rates[search_rate], 'selected', false)
                                        this.rates[search_rate].client_rate_plans_id = result.data.client_rate_plans_id
                                        this.rates[search_rate].markup = this.porcentage
                                        this.rates_selected.push(this.rates[search_rate])
                                        this.rates.splice(search_rate, 1)
                                        this.loading = false
                                    }
                                }).catch(() => {
                                this.showError(
                                    this.$t('global.modules.services'),
                                    this.$t('global.error.messages.connection_error')
                                )
                                this.loading = false
                            })
                        } else {
                            if (this.rates.length > 0) {
                                this.loading = true
                                let element = this.rates.shift()
                                let search_hotel = this.searchSelectHotel()
                                API({
                                    method: 'post',
                                    url: 'service_client_rate_plans/store',
                                    data: {
                                        data: element,
                                        client_id: this.hotels[search_hotel].client_id,
                                        service_rate_id: element.id,
                                        service_id: this.$route.params.service_id,
                                        period: this.selectPeriod,
                                        market: this.selectMarkets,
                                        porcentage: this.porcentage

                                    }
                                })
                                    .then((result) => {
                                        if (result.data.success === true) {
                                            element.client_rate_plans_id = result.data.client_rate_plans_id
                                            this.rates_selected.push(element)

                                            this.loading = false
                                        }
                                    }).catch((e) => {
                                    this.showError(
                                        this.$t('global.modules.services'),
                                        this.$t('global.error.messages.connection_error') + e
                                    )
                                    this.loading = false
                                })
                            }
                        }

                    } else {

                    }
                } else {
                    console.log('Bloqueado accion')
                }
            },
            inverseOneHotel: function () {

                if (this.loading === false) {
                    if (this.validatePeriod() != false) {
                        this.searchMarkup()
                        let search_hotel = this.searchSelectHotelHotelsSelected()
                        this.loading = true
                        console.log(search_hotel)
                        if (search_hotel !== -1) {

                            API({
                                method: 'post',
                                url: 'client_services/inverse',
                                data: {
                                    data: this.hotels_selected[search_hotel],
                                    period: this.selectPeriod,
                                    market: this.selectMarkets,
                                }
                            })
                                .then((result) => {
                                    if (result.data.success === true) {
                                        this.getClients()
                                        this.getClientsLocked()
                                        this.clientRates = []
                                        this.clientRatesSelected = []
                                        this.markup = ''
                                        this.loading = false
                                    }
                                }).catch(() => {
                                this.showError(
                                    this.$t('global.modules.services'),
                                    this.$t('global.error.messages.connection_error')
                                )
                                this.loading = false
                            })
                        } else {
                            if (this.hotels_selected.length > 0) {
                                this.loading = true
                                let element = this.hotels_selected.shift()
                                console.log(element)
                                API({
                                    method: 'post',
                                    url: 'client_services/inverse',
                                    data: {
                                        data: element,
                                        period: this.selectPeriod,
                                        market: this.selectMarkets
                                    }
                                })
                                    .then((result) => {
                                        if (result.data.success === true) {
                                            this.hotels.push(element)
                                            this.markup = ''
                                            this.loading = false
                                        }
                                    }).catch((e) => {
                                    this.showError(
                                        this.$t('global.modules.services'),
                                        this.$t('global.error.messages.connection_error') + e
                                    )
                                    this.loading = false
                                })
                            }
                        }
                    } else {
                        this.showError(
                            this.$t('global.modules.services'),
                            'Seleccione un item'
                        )
                        this.loading = false
                    }
                } else {
                    console.log('Bloqueado accion')
                }
            },
            inverseOneRate: function () {
                if (this.loading === false) {
                    if (this.validatePeriod() != false) {
                        this.searchMarkup()

                        this.loading = true
                        let search_hotel = this.searchSelectHotel()
                        let search_rate = this.searchSelectRateRatesSelected()
                        console.log(search_rate)
                        if (search_rate !== -1) {

                            API({
                                method: 'post',
                                url: 'service_client_rate_plans/inverse',
                                data: {
                                    rate_plan_id: this.rates_selected[search_rate].id,
                                    client_id: this.hotels[search_hotel].client_id,
                                    period: this.selectPeriod,
                                    market: this.selectMarkets
                                }
                            })
                                .then((result) => {
                                    if (result.data.success === true) {
                                        let hotel = this.hotels[search_hotel]
                                        this.getClientRatePlans(hotel.service_id, hotel.client_id)
                                        this.getClientRatePlanSelected(hotel.service_id, hotel.client_id)
                                        //this.clientRates = []
                                        //this.clientRatesSelected = []
                                        //this.markup = ''
                                        this.loading = false
                                    }
                                }).catch(() => {
                                this.showError(
                                    this.$t('global.modules.services'),
                                    this.$t('global.error.messages.connection_error')
                                )
                                this.loading = false
                            })
                        } else {
                            console.log(this.hotels_selected)
                            if (this.hotels_selected.length > 0) {
                                this.loading = true
                                let element = this.hotels_selected.shift()
                                API({
                                    method: 'post',
                                    url: 'client_services/inverse',
                                    data: {
                                        data: element,
                                        period: this.selectPeriod,
                                        market: this.selectMarkets
                                    }
                                })
                                    .then((result) => {
                                        if (result.data.success === true) {
                                            this.hotels.push(element)
                                            this.markup = ''
                                            this.loading = false
                                        }
                                    }).catch((e) => {
                                    this.showError(
                                        this.$t('global.modules.services'),
                                        this.$t('global.error.messages.connection_error') + e
                                    )
                                    this.loading = false
                                })
                            } else {
                                this.loading = false
                                console.log('no se a selecionado ningun servicio')
                            }
                        }
                    }
                } else {
                    console.log('Bloqueado accion')
                }
            },
            moveAllHotels: function () {
                if (this.loading === false) {
                    if (this.validatePeriod() !== false) {
                        this.searchMarkup()

                        this.loading = true

                        if (this.hotels.length > 0) {
                            for (let i = 0; i < this.hotels.length; i++) {
                                this.$set(this.hotels[i], 'selected', false)
                                this.hotels_selected.push(this.hotels[i])
                            }
                            this.hotels = []

                            API({
                                method: 'post',
                                url: 'client_services/store/clientAll',
                                data: {
                                    service_id: this.$route.params.service_id,
                                    period: this.selectPeriod,
                                    market: this.selectMarkets,
                                    porcentage: this.porcentage
                                }
                            })
                                .then((result) => {
                                    if (result.data.success === true) {
                                        this.getClientsLocked()
                                        this.markup = ''
                                        this.rates = []
                                        this.rates_selected = []
                                        this.loading = false
                                    }
                                }).catch((e) => {
                                this.showError(
                                    this.$t('global.modules.services'),
                                    this.$t('global.error.messages.connection_error') + e
                                )
                                this.loading = false
                            })
                        }
                    } else {
                        console.log('Bloqueado accion')
                    }

                }
            },
            moveAllRates: function () {
                if (this.loading === false) {
                    if (this.validatePeriod() != false) {
                        this.searchMarkup()

                        this.loading = true

                        if (this.rates.length > 0) {
                            for (let i = 0; i < this.rates.length; i++) {
                                this.$set(this.rates[i], 'selected', false)
                                this.rates_selected.push(this.rates[i])
                            }
                            this.rates = []

                            let search_hotel = this.searchSelectHotel()
                            if (search_hotel !== -1) {

                                API({
                                    method: 'post',
                                    url: 'service_client_rate_plans/store/all',
                                    data: {
                                        client_id: this.hotels[search_hotel].client_id,
                                        service_id: this.hotels[search_hotel].service_id,
                                        period: this.selectPeriod,
                                        market: this.selectMarkets,
                                        porcentage: this.porcentage
                                    }
                                })
                                    .then((result) => {
                                        if (result.data.success === true) {
                                            //his.getRatesSelected()
                                            this.markup = ''
                                            this.loading = false
                                        }
                                    }).catch((e) => {
                                    this.showError(
                                        this.$t('global.modules.services'),
                                        this.$t('global.error.messages.connection_error') + e
                                    )
                                    this.loading = false
                                })
                            }
                        }
                    } else {
                        console.log('Bloqueado accion')
                    }

                }
            },
            inverseAllHotels: function () {
                if (this.loading === false) {
                    if (this.validatePeriod() != false) {
                        this.searchMarkup()

                        this.loading = true
                        if (this.hotels_selected.length > 0) {
                            for (let i = 0; i < this.hotels_selected.length; i++) {

                                this.hotels.push(this.hotels_selected[i])
                            }
                            this.hotels_selected = []
                            API({
                                method: 'post',
                                url: 'client_services/inverse/clientAll',
                                data: {
                                    service_id: this.$route.params.service_id,
                                    period: this.selectPeriod,
                                    market: this.selectMarkets
                                }
                            })
                                .then((result) => {
                                    if (result.data.success === true) {
                                        this.getClients()
                                        this.clientRates = []
                                        this.clientRatesSelected = []
                                        this.markup = ''
                                        this.loading = false
                                    }
                                }).catch((e) => {
                                this.showError(
                                    this.$t('global.modules.services'),
                                    this.$t('global.error.messages.connection_error') + e
                                )
                                this.loading = false
                            })
                        }
                    }
                } else {
                    console.log('Bloqueado accion.')
                }
            },
            inverseAllRates: function () {
                if (this.loading === false) {
                    if (this.validatePeriod() != false) {
                        this.searchMarkup()

                        this.loading = true
                        if (this.rates_selected.length > 0) {

                            this.rates_selected = []
                            let search_hotel = this.searchSelectHotel()
                            let hotel = this.hotels[search_hotel]
                            API({
                                method: 'post',
                                url: 'service_client_rate_plans/inverse/all',
                                data: {
                                    client_id: hotel.client_id,
                                    service_id: hotel.service_id,
                                    period: this.selectPeriod,
                                    market: this.selectMarkets
                                }
                            })
                                .then((result) => {
                                    if (result.data.success === true) {
                                        this.getClientRatePlanSelected(hotel.service_id, hotel.client_id)
                                        //this.clientRates = []
                                        //this.clientRatesSelected = []
                                        //this.markup = ''
                                        this.loading = false
                                    }
                                }).catch((e) => {
                                this.showError(
                                    this.$t('global.modules.services'),
                                    this.$t('global.error.messages.connection_error') + e
                                )
                                this.loading = false
                            })
                        }
                    }
                } else {
                    console.log('Bloqueado accion')
                }
            },
            calculateNumPages: function (num_hotels, limit) {
                this.num_pages = Math.ceil(num_hotels / limit)
            },
            calculateNumPagesHotelsSelected: function (num_hotels, limit) {
                this.num_pages_hotels_selected = Math.ceil(num_hotels / limit)
            },
            getScrollTop: function () {
                let scroll = document.getElementById('list_hotels').scrollTop
                if (scroll > this.scroll_limit) {
                    this.page += 1
                    this.scroll_limit = 2900 * this.page
                    if (this.page === this.num_pages) {
                        clearInterval(this.interval)
                        this.getClientsScroll()
                    } else {

                        this.getClientsScroll()
                    }

                }
            },
            getScrollTopHotelsSelected: function () {
                let scroll = document.getElementById('list_hotels_selected').scrollTop
                if (scroll > this.scroll_limit_hotels_selected) {
                    this.page_hotels_selected += 1
                    this.scroll_limit_hotels_selected = 2900 * this.page_hotels_selected
                    if (this.page_hotels_selected === this.num_pages_hotels_selected) {
                        clearInterval(this.interval_hotels_selected)
                        this.getClientsScrollSelected()
                    } else {

                        this.getClientsScrollSelected()
                    }

                }
            },
            getClientRatePlans: function (service_id, client_id) {
                API({
                    method: 'post',
                    url: 'service_client_rate_plans',
                    data: {
                        client_id: client_id,
                        period: this.selectPeriod,
                        market: this.selectMarkets,
                        service_id: service_id
                    }
                })
                    .then((result) => {
                        this.rates_selected = result.data.data
                        /*
                        this.clientRates = result.data.data
                        for (var i = this.clientRates.length - 1; i >= 0; i--) {
                          this.clientRates[i].name = this.clientRates[i].rate_plan.name
                        }
                        */

                    }).catch(() => {
                    this.showError(
                        this.$t('global.modules.services'),
                        this.$t('global.error.messages.connection_error')
                    )
                    this.loading = false
                })
            },
            getClientRatePlanSelected: function (service_id, client_id) {
                API({
                    method: 'post',
                    url: 'service_client_rate_plans/selected',
                    data: {
                        client_id: client_id,
                        period: this.selectPeriod,
                        market: this.selectMarkets,
                        service_id: service_id
                    }
                })
                    .then((result) => {
                        //this.clientRatesSelected = result.data.data
                        this.rates = result.data.data

                    }).catch(() => {
                    this.showError(
                        this.$t('global.modules.services'),
                        this.$t('global.error.messages.connection_error')
                    )
                    this.loading = false
                })
            },
            getClients: function () {
                this.loading = true
                API({
                    method: 'post',
                    url: 'client/search/service',
                    data: {
                        page: 1,
                        limit: this.limit,
                        query: this.query,
                        service_id: this.$route.params.service_id,
                        period: this.selectPeriod,
                        market: this.selectMarkets
                    }
                })
                    .then((result) => {
                        this.loading = false
                        this.hotels = result.data.data
                        this.count = result.data.count
                        this.calculateNumPages(result.data.count, this.limit)
                        this.scroll_limit = 2900
                        document.getElementById('list_hotels').scrollTop = 0

                    }).catch(() => {
                    this.showError(
                        this.$t('global.modules.services'),
                        this.$t('global.error.messages.connection_error')
                    )
                    this.loading = false
                })

            },
            getClientsScroll: function () {

                API({
                    method: 'post',
                    url: 'client/search/service',
                    data: {
                        page: this.page,
                        limit: this.limit,
                        query: this.query,
                        service_id: this.$route.params.service_id,
                        period: this.selectPeriod,
                        market: this.selectMarkets
                    }
                })
                    .then((result) => {
                        let hotels = result.data.data
                        for (let i = 0; i < hotels.length; i++) {
                            this.hotels.push(hotels[i])
                        }
                        if (this.page === 1) {
                            this.count = result.data.count
                            this.calculateNumPages(result.data.count, this.limit)
                        }
                    }).catch(() => {
                    this.showError(
                        this.$t('global.modules.services'),
                        this.$t('global.error.messages.connection_error')
                    )
                    this.loading = false
                })

            },
            getClientsLocked: function () {
                this.loading = true
                API({
                    method: 'post',
                    url: 'client_services/clientLocked',
                    data: {
                        page: 1,
                        limit: this.limit_hotels_selected,
                        query: this.query_hotels_selected,
                        service_id: this.$route.params.service_id,
                        period: this.selectPeriod,
                        market: this.selectMarkets
                    }
                })
                    .then((result) => {
                        this.loading = false
                        this.hotels_selected = result.data.data

                        this.count_hotels_selected = result.data.count
                        this.calculateNumPagesHotelsSelected(result.data.count, this.limit_hotels_selected)
                        this.scroll_limit_hotels_selected = 2900
                        document.getElementById('list_hotels_selected').scrollTop = 0

                    }).catch((e) => {
                    this.showError(
                        this.$t('global.modules.services'),
                        this.$t('global.error.messages.connection_error') + e
                    )
                    this.loading = false
                })
            },
            getClientsScrollSelected: function () {
                API({
                    method: 'post',
                    url: 'client_services',
                    data: {
                        page: this.page_hotels_selected,
                        limit: this.limit_hotels_selected,
                        query: this.query_hotels_selected,
                        client_id: this.$route.params.client_id,
                        period: this.selectPeriod,
                        market: this.selectMarkets
                    }
                })
                    .then((result) => {
                        let hotels_selected = result.data.data
                        for (let i = 0; i < hotels_selected.length; i++) {
                            this.hotels_selected.push(hotels_selected[i])
                        }
                        if (this.page === 1) {
                            this.count = result.data.count
                            this.calculateNumPagesHotelsSelected(result.data.count, this.limit)
                        }
                    }).catch(() => {
                    this.showError(
                        this.$t('global.modules.services'),
                        this.$t('global.error.messages.connection_error')
                    )
                    this.loading = false
                })
            },
            updateOneHotel: function () {
                if (this.loading === false) {
                    if (this.validateMarkup() != false) {
                        this.loading = true
                        let search_hotel = this.searchSelectHotel()
                        let url_update = 'client_services/update'
                        if (this.apply_all) {
                            url_update = 'client_services/update/all'
                        }
                        if (search_hotel !== -1 || (this.apply_all)) {
                            API({
                                method: 'put',
                                url: url_update,
                                data: {
                                    service_id: this.$route.params.service_id,
                                    client_id: (search_hotel !== -1 && !this.apply_all) ? this.hotels[search_hotel].client_id : '',
                                    period: this.selectPeriod,
                                    market: this.selectMarkets,
                                    markup: this.markup,
                                    markupId: this.markupId
                                }
                            })
                                .then((result) => {
                                    if (result.data.success === true) {
                                        this.getClients()
                                        this.getClientsLocked()
                                        this.rates_selected = []
                                        this.rates = []
                                        this.markup = ''
                                        //this.clientRates = []
                                        //this.clientRatesSelected = []
                                        this.loading = false
                                    }
                                }).catch(() => {
                                this.showError(
                                    this.$t('global.modules.services'),
                                    this.$t('global.error.messages.connection_error')
                                )
                                this.loading = false
                            })
                        } else {
                            this.showError(
                                this.$t('global.modules.services'),
                                'Seleccione un cliente disponible'
                            )
                            this.loading = false
                        }
                    } else {
                        this.loading = false
                    }
                } else {
                    console.log('Bloqueado accion ' + this.loading)
                }
            },
            updateOneRate: function () {
                if (this.loading === false) {
                    if (this.validateMarkupRate() != false) {
                        this.loading = true
                        let search_hotel = this.searchSelectHotel()
                        let search_rate = this.searchSelectRate()
                        if (search_rate !== -1) {

                            API({
                                method: 'put',
                                url: 'service_client_rate_plans/update',
                                data: {
                                    rate_plan_id: this.rates[search_rate].id,
                                    client_id: this.hotels[search_hotel].client_id,
                                    period: this.selectPeriod,
                                    market: this.selectMarkets,
                                    markup: this.markup_rate
                                }
                            })
                                .then((result) => {
                                    if (result.data.success === true) {
                                        let hotel = this.hotels[search_hotel]
                                        this.getClientRatePlans(hotel.service_id, hotel.client_id)
                                        this.getClientRatePlanSelected(hotel.service_id, hotel.client_id)
                                        this.markup_rate = ''
                                        //this.clientRates = []
                                        //this.clientRatesSelected = []
                                        this.loading = false
                                    }
                                }).catch(() => {
                                this.showError(
                                    this.$t('global.modules.services'),
                                    this.$t('global.error.messages.connection_error')
                                )
                                this.loading = false
                            })
                        } else {
                            this.loading = false
                        }
                    }
                } else {
                    console.log('Bloqueado accion')
                }
            }
        },
        beforeDestroy () {

            clearInterval(this.interval)
            clearInterval(this.interval_hotels_selected)

        }
    }
</script>

<style>
    body {
        -webkit-touch-callout: none;
        -webkit-user-select: none;
        -khtml-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }

    .style_list_ul {
        height: 160px;
        max-height: 160px;
        overflow-y: scroll;
        list-style-type: none;
        padding: 0px;
        margin-left: -1px;
        border-left: 1px solid #ccc;
        border-bottom: 1px solid #ccc;
    }

    .selected {
        background-color: #005ba5;
        color: white;
    }

    .style_list_li {
        border-bottom: 1px solid #ccc;
        padding: 5px 5px 5px 5px;
        cursor: move;
    }

    .style_span_li {
        margin-left: 5px;
        font-size: 11px;
    }

    #search_hotels:focus {
        box-shadow: none;
        border-color: #ccc;
    }

    #search_hotels {
        border-top: 1px solid #ccc;
        border-right: 1px solid #ccc;
        border-bottom: 1px solid #ccc;
        border-bottom-right-radius: 0px;
        border-top-right-radius: 0.2rem;
    }

    .button_icon {
        background-color: #f0f3f5 !important;
        border-top-left-radius: 0.2rem;
        color: #000;
        cursor: default !important;
    }

    .button_icon:hover {
        box-shadow: none;
        background-color: #f0f3f5 !important;
    }

    .button_icon:focus {
        box-shadow: none;
        background-color: #f0f3f5 !important;
    }

    .button_icon:active {
        box-shadow: none;
        background-color: #f0f3f5 !important;
    }

    .mover_controls {
        padding: 10px;
        margin-bottom: 10px;
    }
</style>


