<template>
    <div class="container-fluid">
        <div class="row">
            <div class="col-3 text-left" style="padding-top: 20px;">
                <span class="switch-label">Lista</span>
                <span class="two-way-switch">
                    <input id="switch" type="checkbox" v-model="showCalendar"/><label for="switch">Toggle</label>
                </span>
                <span class="switch-label">Calendario</span>
            </div>
<!--               <div class="col-3">-->
<!--                   <input type="text" class="form-control" placeholder="2020" v-model="year_from">-->
<!--               </div>-->
<!--            <div class="col-3">-->
<!--                <input type="text" class="form-control" placeholder="2021" v-model="year_to">-->
<!--            </div>-->
<!--            <div class="col-3">-->
<!--                <button class="btn btn-success" @click="validateFieldsDuplicate">Duplicar</button>-->
<!--            </div>-->
        </div>
        <div class="row" v-if="showCalendar">
            <Calendar></Calendar>
        </div>
        <div class="row" v-if="!showCalendar">

            <div class="row col-12">
                <div class="col-12">
                    <div class="row">
                        <div class="col-8 pull-right">
                            <div class="b-form-group form-group">
                                <div class="form-row">
                                    <label class="col-sm-1 col-form-label" for="period">{{ $t('clientsmanageclienthotel.period')
                                        }}</label>
                                    <div class="col-sm-1.5">
                                        <select @change="searchPeriod" ref="period" class="form-control" id="period" required
                                                size="0" v-model="selectPeriod">
                                            <option :value="year.value" v-for="year in years">
                                                {{ year.text}}
                                            </option>
                                        </select>
                                    </div>
                                    <label class="col-sm-1 col-form-label" for="period">{{ $t('clientsmanageclienthotel.market')
                                        }}</label>
                                    <div class="col-sm-4">
                                        <select @change="searchPeriod" ref="period" class="form-control" id="mark" required
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
                    <label class="col-sm-12 col-form-label" for="period">{{ $t('clientsmanageclienthotel.available_clients')
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
                            <li :class="{'style_list_li':true, 'item':true, 'selected':hotel.selected}" :id="'hotel_'+index"

                                @click="selectHotel(hotel,index)" v-for="(hotel,index) in hotels">
                                <span class="style_span_li">{{ hotel.name}} ({{ hotel.markup_no_defined ? 'no definido' : hotel.markup}}%)</span>
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
                        <button @click="inverseOneHotel()" class="btn btn-secondary mover_controls btn-block pr-3">
                            <font-awesome-icon :icon="['fas', 'angle-left']"/>
                        </button>
                    </div>
                </div>
                <div class="col-5">
                    <label class="col-sm-12 col-form-label" for="period">{{ $t('clientsmanageclienthotel.clients_locked')
                        }}</label>
                    <div class="input-group">
                <span class="input-group-append">
                    <button class="btn btn-outline-secondary button_icon" type="button">
                        <font-awesome-icon :icon="['fas', 'search']"/>
                    </button>
                </span>
                        <input class="form-control" id="search_hotels_selected" type="search" v-model="query_hotels_selected"
                               value="">
                    </div>
                    <ul class="style_list_ul" id="list_hotels_selected" style="background-color: #bd0d121c;">
                        <draggable :list="hotels_selected" class="list-group">
                            <li :class="{'style_list_li':true, 'item':true, 'selected':hotel.selected}"
                                @click="selectHotelHotelsSelected(hotel,index)" v-for="(hotel,index) in hotels_selected">
                                <span class="style_span_li">{{ hotel.name}}</span>
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
<!--                    <div style="margin-bottom: 10px;">-->
<!--                        <label class="col-sm-12 col-form-label" for="period" style="width: 27%;">{{ $t('clientsmanageclienthotel.available_hotels_rates')-->
<!--                            }}</label>-->
<!--                        <select class="form-control" style="width: 72%; display: inline;" v-model="aplicaTarifa" @change="aplicaTarifaChange">-->
<!--                            <option value="1">Aplicar cliente Seleccionado</option>-->
<!--                            <option value="2">Aplicar a todos los clientes</option>-->
<!--                        </select>-->
<!--                    </div>-->
                    <ul class="style_list_ul" id="list_rates" style="border-top:1px solid #ccc;max-height: 196px;height: 196px;background-color: #4dbd7429;">
                        <draggable :list="rates">
                            <li :class="{'style_list_li':true, 'item':true, 'selected':rate.selected}" :id="'rate_'+index"
                                @click="selectRate(rate,index)" v-for="(rate,index) in rates">
                                <span class="style_span_li">{{ rate.name}} {{ rate.markup ? '(' + rate.markup + ')%' : formatselectMarkup() }}</span>
                            </li>
                        </draggable>
                    </ul>
                </div>
                <div class="col-1 mt-4 mr-2">
<!--                    <div class="col-12">-->
<!--                        <button @click="moveOneRate()" class="btn btn-secondary mover_controls btn-block pr-3">-->
<!--                            <font-awesome-icon :icon="['fas', 'angle-right']"/>-->
<!--                        </button>-->
<!--                    </div>-->
<!--                    <div class="col-12">-->
<!--                        <button @click="moveAllRates()" class="btn btn-secondary mover_controls btn-block pr-3">-->
<!--                            <font-awesome-icon :icon="['fas', 'angle-double-right']"/>-->
<!--                        </button>-->
<!--                    </div>-->
<!--                    <div class="col-12">-->
<!--                        <button @click="inverseOneRate()" class="btn btn-secondary mover_controls btn-block pr-3">-->
<!--                            <font-awesome-icon :icon="['fas', 'angle-left']"/>-->
<!--                        </button>-->
<!--                    </div>-->
<!--                    <div class="col-12">-->
<!--                        <button @click="inverseAllRates()" class="btn btn-secondary mover_controls btn-block pr-3">-->
<!--                            <font-awesome-icon :icon="['fas', 'angle-double-left']"/>-->
<!--                        </button>-->
<!--                    </div>-->
                </div>
                <div class="col-5">
<!--                    <div style="margin-bottom: 10px;">-->
<!--                        <label class="col-sm-12 col-form-label" for="period" style="width: 27%;">{{ $t('clientsmanageclienthotel.hotels_rates_blocked')-->
<!--                            }}</label>-->
<!--                        <select class="form-control" style="width: 72%; display: inline;" v-model="aplicaTarifaBlocked" @change="aplicaTarifaBloquedChange">-->
<!--                            <option value="1">Aplicar cliente Seleccionado</option>-->
<!--                            <option value="2">Aplicar a todos los clientes</option>-->
<!--                        </select>-->
<!--                    </div>-->
                    <ul class="style_list_ul" id="list_rates_selected" style="border-top:1px solid #ccc;max-height: 196px;height: 196px;background-color: #bd0d121c;">
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

        <BlockUI :message="msg" v-show="blockPage">
            <i class="fa fa-cog fa-spin fa-3x fa-fw"></i>
        </BlockUI>
        <block-page></block-page>
    </div>
</template>
<script>
    import axios from 'axios'
    import { API } from './../../../../../../api'
    import draggable from 'vuedraggable'
    import TableClient from './../../../../../../components/TableClient'
    import BFormCheckbox from 'bootstrap-vue/es/components/form-checkbox/form-checkbox'
    import Calendar from './Calendar'
    import BlockPage from "../../../../../../components/BlockPage";

    export default {
        components: {
            draggable,
            'table-client': TableClient,
            BFormCheckbox,
            Calendar,
            BlockPage
        },
        data () {
            return {
                msg: 'Procesando',
                blockPage:false,
                aplicaTarifa: 1,
                aplicaTarifaBlocked:1,
                showCalendar: false,
                users: [],
                scroll_limit: 2900,
                hotels: [],
                rates: [],
                markets:[],
                page: 1,
                selectPeriod: '',
                selectMarkets:'',
                selectRatePlan: '',
                nameSelectHotel: '',
                porcentage: '',
                markupId:'',
                markup: '',
                markup_rate:'',
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
                query_rates_selected:'',
                scroll_limit_hotels_selected: 2900,
                interval_hotels_selected: null,
                loading: false,
                table: {
                    columns: ['name', 'markup', 'delete']
                },
                year_from:'',
                year_to:''
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
            years() {
                let previousYear = moment().subtract(2, 'years').year()
                let currentYear = moment().add(5, 'years').year()

                let years = []

                do {
                    years.push({value: previousYear, text: previousYear})
                    previousYear++
                } while (currentYear > previousYear)

                return years
            }
        },
        mounted: function () {
            let currentDate = new Date()
            this.selectPeriod = currentDate.getFullYear();

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
            // this.getClientRatePlanSelected(this.$route.params.hotel_id,'');
        },
        methods: {
            validateFieldsDuplicate:function(){
                if (this.year_from === '' || this.year_to ==='')
                {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Error',
                        text: 'Faltan datos por llenar'
                    })
                }else{
                    this.duplicate();
                }
            },
            duplicate:function(){
                this.$root.$emit('blockPage')
                API({
                    method: 'post',
                    url: 'duplicate/rates_and_clients/locked',
                    data: {
                       'year_from':this.year_from,
                        'year_to':this.year_to,
                        'hotel_id':this.$route.params.hotel_id
                    }
                })
                    .then((result) => {
                        this.$notify({
                            group: 'main',
                            type: 'success',
                            title: 'Clientes y Tarifas',
                            text: 'Clientes y Tarifas Clonadas'
                        })
                        this.$root.$emit('unlockPage')
                    }).catch((e) => {
                        console.log(e)
                    this.$root.$emit('unlockPage')
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Error',
                        text: 'Error duplicando Clientes y tarifas bloqueadas'
                    })
                })
            },
            aplicaTarifaChange: function(){
                if(this.aplicaTarifa == 1) {
                    let search_hotel = this.searchSelectHotel()
                    if (search_hotel !== -1) {
                        let hotel_id = this.hotels[search_hotel].hotel_id
                        let client_id = this.hotels[search_hotel].client_id
                        this.getClientRatePlanSelected(hotel_id, client_id);
                        this.getClientRatePlans(hotel_id,client_id)
                    }else{
                        this.getClientRatePlanSelected(this.$route.params.hotel_id, '');
                        this.rates_selected = [];
                    }
                }else{
                    // let search_hotel = this.searchSelectHotel();
                    // if (search_hotel !== -1) {
                    //     this.$set(this.hotels[search_hotel], 'selected', false)
                    // }
                    this.getClientRatePlanSelected(this.$route.params.hotel_id, '');
                    this.rates_selected = [];
                }
            },
            aplicaTarifaBloquedChange: function(){

                if(this.aplicaTarifaBlocked == 1) {
                    let search_hotel = this.searchSelectHotel()
                    if (search_hotel !== -1) {
                        let hotel_id = this.hotels[search_hotel].hotel_id
                        let client_id = this.hotels[search_hotel].client_id
                        this.getClientRatePlanSelected(hotel_id, client_id);
                        this.getClientRatePlans(hotel_id,client_id)
                    }else{
                        this.getClientRatePlanSelected(this.$route.params.hotel_id, '');
                        this.rates_selected = [];
                    }
                }else{
                    // let search_hotel = this.searchSelectHotel();
                    // if (search_hotel !== -1) {
                    //     this.$set(this.hotels[search_hotel], 'selected', false)
                    // }
                    this.getClientRatePlanSelectedBloquet(this.$route.params.hotel_id, '');
                    this.rates = [];
                }

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
                this.blockPage = true
                this.getClients()
                this.getClientsLocked()
                this.searchMarkup()
                this.searchMarkupId()


                // this.getClientRatePlanSelected(this.$route.params.hotel_id,'');
                // this.rates_selected = [];
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
                if (this.hotels[index].selected) {
                    this.$set(this.hotels[index], 'selected', false)
                } else {

                    this.getClientRatePlans(hotel.hotel_id,hotel.client_id)
                    this.getClientRatePlanSelected(hotel.hotel_id,hotel.client_id)
                    this.nameSelectHotel = hotel.name
                    this.setPropertySelectedInHotels()
                    this.$set(this.hotels[index], 'selected', true)
                    this.markup = hotel.markup
                    this.aplicaTarifa = 1;
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
                    // this.setPropertySelectedInRates()
                    this.$set(this.rates[index], 'selected', true)
                }
            },
            selectRateRatesSelected: function (hotel, index) {
                if (this.rates_selected[index].selected) {
                    this.$set(this.rates_selected[index], 'selected', false)
                } else {
                    //this.setPropertySelectedInRatesSelected()
                    this.$set(this.rates_selected[index], 'selected', true)
                }
            },
            searchSelectHotel: function () {
                console.log(this.hotels.length)
                for (let i = 0; i < this.hotels.length; i++) {
                    if (this.hotels[i].selected) {
                        return i
                        break
                    }
                }
                return -1
            },
            // searchSelectRate:function(){
            //     for (let i = 0; i < this.rates.length; i++) {
            //         if (this.rates[i].selected) {
            //             return i
            //             break
            //         }
            //     }
            //     return -1
            // },
            searchSelectRate:function(){
                let rateSelected = [];
                for (let i = 0; i < this.rates.length; i++) {
                    if (this.rates[i].selected) {
                        rateSelected.push(this.rates[i].id)
                    }
                }
                if(rateSelected.length == 0) {
                    return -1
                }else{
                    return rateSelected;
                }
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
            searchSelectRateRatesSelected: function (){
                let rateSelected = [];
                for (let i = 0; i < this.rates_selected.length; i++) {
                    if (this.rates_selected[i].selected) {
                        rateSelected.push(this.rates_selected[i].id)
                    }
                }

                if(rateSelected.length == 0) {
                    return -1
                }else{
                    return rateSelected;
                }
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
            selectMarkup: function (){
                let search_hotel = this.searchSelectHotel()
                if (search_hotel !== -1) {
                    let hotels =  this.hotels[search_hotel];
                    if(hotels.markup){
                        return hotels.markup;
                    }else{
                        return 0; //this.porcentage
                    }
                }else{
                    return -1;
                }
            },
            formatselectMarkup: function (){
                let markup = this.selectMarkup();
                if(markup != -1){
                    return '(' + markup + ')%';
                }else{
                    return '';
                }
            },
            moveOneHotel: function () {
                // if (this.loading === false) {
                    if (this.validatePeriod() != false) {
                        this.searchMarkup()

                        this.loading = true
                        let search_hotel = this.searchSelectHotel()
                        if (search_hotel !== -1) {
                            this.blockPage = true
                            API({
                                method: 'post',
                                url: 'client_hotels/store',
                                data: {
                                    data: this.hotels[search_hotel],
                                    period: this.selectPeriod,
                                    market:this.selectMarkets,
                                    porcentage: this.porcentage,
                                    query: this.query
                                }
                            })
                                .then((result) => {
                                    this.blockPage = false
                                    if (result.data.success === true) {

                                        this.$set(this.hotels[search_hotel], 'selected', false)
                                        this.hotels[search_hotel].hotel_client_id = result.data.hotel_client_id
                                        this.hotels[search_hotel].markup = this.porcentage
                                        this.hotels_selected.push(this.hotels[search_hotel])
                                        this.hotels.splice(search_hotel, 1)
                                        this.rates = [];
                                        this.rates_selected = [];
                                        this.loading = false
                                    }
                                }).catch(() => {
                                this.blockPage = false
                                this.showError(
                                    this.$t('clientsmanageclienthotel.error.messages.name'),
                                    this.$t('clientsmanageclienthotel.error.messages.connection_error')
                                )
                            })
                        } else {
                            if (this.hotels.length > 0) {
                                this.loading = true
                                let element = this.hotels.shift()
                                this.blockPage = true
                                API({
                                    method: 'post',
                                    url: 'client_hotels/store',
                                    data: {
                                        data: element,
                                        period: this.selectPeriod,
                                        market:this.selectMarkets,
                                        porcentage: this.porcentage,
                                        query: this.query
                                    }
                                })
                                    .then((result) => {

                                        this.blockPage = false
                                        if (result.data.success === true) {
                                            element.hotel_client_id = result.data.hotel_client_id
                                            this.hotels_selected.push(element)
                                            this.rates = [];
                                            this.rates_selected = [];
                                        }
                                    }).catch((e) => {
                                    this.blockPage = false
                                    this.showError(
                                        this.$t('clientsmanageclienthotel.error.messages.name'),
                                        this.$t('clientsmanageclienthotel.error.messages.connection_error') + e
                                    )
                                })
                            }
                        }

                    }
                // } else {
                //     console.log('Bloqueado accion')
                // }
            },
            moveOneRate: function () {
                if (this.validatePeriod() != false) {

                    this.searchMarkup()

                    this.loading = true
                    let search_hotel = this.searchSelectHotel()
                    console.log(search_hotel)
                    let search_rate = this.searchSelectRate()
                    if(this.aplicaTarifa == 1) {
                        if (search_hotel == -1) {
                            alert('Selecciona un cliente')
                            return false;
                        }
                    }

                    if(this.hotels.length == 0){
                        alert('No hay clientes para procesar')
                        return false;
                    }

                    if (search_rate !== -1 ) {

                        this.blockPage = true

                        let promises = [];



                        for (var i = 0; i < this.markets.length; i++) {


                            let marketSelected = '';
                            if(this.selectMarkets ){
                                if(this.selectMarkets != this.markets[i].value)continue;
                                marketSelected = this.selectMarkets;
                            }else{
                                marketSelected = this.markets[i].value;
                            }

                            promises.push(API({
                                            method: 'post',
                                            url: 'client_rate_plans/store',
                                            data: {
                                                rate_plan_id: search_rate,
                                                hotel_id: this.$route.params.hotel_id,
                                                client_id: this.aplicaTarifa == 1 ? this.hotels[search_hotel].client_id : '',
                                                period: this.selectPeriod,
                                                market: marketSelected,
                                                porcentage: this.porcentage,
                                                aplicaTarifa: this.aplicaTarifa,
                                                query: this.query
                                            }
                                        })
                            );

                        }

                        axios.all(promises).then((result) => {
                            console.log(promises);
                            if (search_hotel == -1) {
                                search_hotel = 0;
                                this.$set(this.hotels[search_hotel], 'selected', true)
                            }
                            this.getClientRatePlans(this.hotels[search_hotel].hotel_id, this.hotels[search_hotel].client_id)
                            this.getClientRatePlanSelected(this.hotels[search_hotel].hotel_id, this.hotels[search_hotel].client_id)
                            this.blockPage = false;
                        })

                    }else{
                        alert('Selecciona una tarifa')
                    }
                }

            },
            moveOneRateBK: function () {
                    if (this.validatePeriod() != false) {
                        this.searchMarkup()

                        this.loading = true
                        let search_hotel = this.searchSelectHotel()
                        let search_rate = this.searchSelectRate()
                        if(this.aplicaTarifa == 1) {
                            if (search_hotel == -1) {
                                alert('Selecciona un cliente')
                                return false;
                            }
                        }

                        if(this.hotels.length == 0){
                            alert('No hay clientes para procesar')
                            return false;
                        }

                        if (search_rate !== -1 ) {
                            this.blockPage = true
                            API({
                                method: 'post',
                                url: 'client_rate_plans/store',
                                data: {
                                    rate_plan_id: search_rate,
                                    hotel_id: this.$route.params.hotel_id,
                                    client_id: this.aplicaTarifa == 1 ? this.hotels[search_hotel].client_id : '',
                                    period: this.selectPeriod,
                                    market: this.selectMarkets,
                                    porcentage: this.porcentage,
                                    aplicaTarifa: this.aplicaTarifa,
                                    query: this.query
                                }
                            })
                                .then((result) => {
                                    this.blockPage = false
                                    if (result.data.success === true) {

                                        if (search_hotel == -1) {
                                            search_hotel = 0;
                                            this.$set(this.hotels[search_hotel], 'selected', true)
                                        }
                                        this.getClientRatePlans(this.hotels[search_hotel].hotel_id, this.hotels[search_hotel].client_id)
                                        this.getClientRatePlanSelected(this.hotels[search_hotel].hotel_id, this.hotels[search_hotel].client_id)


                                    }
                                }).catch((e) => {
                                    this.blockPage = false
                                    this.showError(
                                        this.$t('clientsmanageclienthotel.error.messages.name'),
                                        this.$t('clientsmanageclienthotel.error.messages.connection_error') + e
                                    )
                            })
                        }else{
                            alert('Selecciona una tarifa')
                        }
                    }

            },
            inverseOneHotel: function () {
                // if (this.loading === false) {
                    if (this.validatePeriod() != false) {
                        this.searchMarkup()

                        this.loading = true

                        let search_hotel = this.searchSelectHotelHotelsSelected()
                        if (search_hotel !== -1) {
                            this.blockPage = true
                            API({
                                method: 'post',
                                url: 'client_hotels/inverse',
                                data: {
                                    data: this.hotels_selected[search_hotel],
                                    period: this.selectPeriod,
                                    market:this.selectMarkets,
                                    query: this.query
                                }
                            })
                                .then((result) => {
                                    this.blockPage = false
                                    if (result.data.success === true) {
                                        this.getClients()
                                        this.getClientsLocked()
                                        this.clientRates = []
                                        this.clientRatesSelected = []
                                        this.markup = ''
                                    }
                                }).catch(() => {
                                this.blockPage = false
                                this.showError(
                                    this.$t('clientsmanageclienthotel.error.messages.name'),
                                    this.$t('clientsmanageclienthotel.error.messages.connection_error')
                                )
                            })
                        } else {
                            if (this.hotels_selected.length > 0) {
                                this.loading = true
                                let element = this.hotels_selected.shift()
                                this.blockPage = false
                                API({
                                    method: 'post',
                                    url: 'client_hotels/inverse',
                                    data: {
                                        data: element,
                                        period: this.selectPeriod,
                                        market:this.selectMarkets
                                    }
                                })
                                    .then((result) => {
                                        this.blockPage = false
                                        if (result.data.success === true) {
                                            this.hotels.push(element)
                                            this.markup = ''
                                        }
                                    }).catch((e) => {
                                    this.blockPage = false
                                    this.showError(
                                        this.$t('clientsmanageclienthotel.error.messages.name'),
                                        this.$t('clientsmanageclienthotel.error.messages.connection_error') + e
                                    )
                                })
                            }
                        }
                    }
                // } else {
                //     console.log('Bloqueado accion')
                // }
            },
            inverseOneRate: function () {

                    if (this.validatePeriod() != false) {
                        this.searchMarkup()

                        this.loading = true
                        let search_hotel = this.searchSelectHotel()
                        let search_rate = this.searchSelectRateRatesSelected()
                        console.log(search_hotel)

                        if(this.aplicaTarifaBlocked == 1) {
                            if (search_hotel == -1) {
                                alert('Selecciona un cliente')
                                return false;
                            }
                        }

                        if(this.hotels.length == 0){
                            alert('No hay clientes para procesar')
                            return false;
                        }

                        if (search_rate !== -1) {
                            this.blockPage = true
                            API({
                                method: 'post',
                                url: 'client_rate_plans/inverse',
                                data: {
                                    rate_plan_id: search_rate,
                                    client_id: this.aplicaTarifaBlocked == 1 ? this.hotels[search_hotel].client_id : '',
                                    hotel_id: this.$route.params.hotel_id,
                                    period: this.selectPeriod,
                                    market:this.selectMarkets,
                                    aplicaTarifa: this.aplicaTarifaBlocked,
                                    query: this.query
                                }
                            })
                                .then((result) => {

                                    this.blockPage = false
                                    if (result.data.success === true) {

                                        if (search_hotel == -1) {
                                            this.selectHotel(this.hotels[0] , 0);
                                            search_hotel = 0;
                                        }

                                        let hotel = this.hotels[search_hotel];

                                        this.getClientRatePlans(this.$route.params.hotel_id,hotel.client_id)
                                        this.getClientRatePlanSelected(this.$route.params.hotel_id,hotel.client_id)
                                        this.markup = ''
                                    }
                                }).catch((e) => {
                                    this.blockPage = false
                                    this.showError(
                                        this.$t('clientsmanageclienthotel.error.messages.name'),
                                        this.$t('clientsmanageclienthotel.error.messages.connection_error') + e
                                )
                            })
                        } else {
                            alert('Seleccione una tarifa')
                        }
                    }

            },
            moveAllHotels: function () {
                // if (this.loading === false) {
                    if (this.validatePeriod() != false) {
                        this.searchMarkup()

                        this.loading = true

                        if (this.hotels.length > 0) {
                            for (let i = 0; i < this.hotels.length; i++) {
                                this.$set(this.hotels[i], 'selected', false)
                                this.hotels_selected.push(this.hotels[i])
                            }
                            this.hotels = []
                            this.blockPage = true
                            API({
                                method: 'post',
                                url: 'client_hotels/store/clientAll',
                                data: {
                                    hotel_id: this.$route.params.hotel_id,
                                    period: this.selectPeriod,
                                    market:this.selectMarkets,
                                    porcentage: this.porcentage,
                                    query: this.query
                                }
                            })
                                .then((result) => {
                                    this.blockPage = false
                                    if (result.data.success === true) {
                                        this.getClientsLocked()
                                        this.markup = ''
                                        this.rates = [];
                                        this.rates_selected = [];
                                    }
                                }).catch((e) => {
                                this.blockPage = false
                                this.showError(
                                    this.$t('clientsmanageclienthotel.error.messages.name'),
                                    this.$t('clientsmanageclienthotel.error.messages.connection_error') + e
                                )
                            })
                        }
                    } else {
                        console.log('Bloqueado accion')
                    }

                // }
            },
            moveAllRates: function () {

                if (this.validatePeriod() != false) {
                    this.searchMarkup()

                    this.loading = true

                    if(this.hotels.length == 0){
                        alert('No hay clientes para procesar')
                        return false;
                    }

                    if (this.rates.length > 0) {

                        let search_hotel = this.searchSelectHotel()

                        if(this.aplicaTarifa == 1) {
                            if (search_hotel == -1) {
                                alert('Selecciona un cliente')
                                return false;
                            }
                        }

                        // this.msg = '';
                        this.blockPage = true

                        let promises = [];

                        for (var i = 0; i < this.markets.length; i++) {


                            let marketSelected = '';
                            if(this.selectMarkets ){
                                if(this.selectMarkets != this.markets[i].value)continue;
                                marketSelected = this.selectMarkets;
                            }else{
                                marketSelected = this.markets[i].value;
                            }

                            promises.push( API({
                                    method: 'post',
                                    url: 'client_rate_plans/store/all',
                                    data: {
                                        client_id: this.aplicaTarifa == 1 ? this.hotels[search_hotel].client_id : '',
                                        hotel_id: this.$route.params.hotel_id,
                                        period: this.selectPeriod,
                                        market:marketSelected,
                                        porcentage: this.porcentage,
                                        aplicaTarifa : this.aplicaTarifa,
                                        query: this.query
                                    }
                                }).then((result) => {
                                    // this.msg = this.msg + 'Mercado ' + this.markets[i].text + " procesado\n";
                                }).catch((e) => {

                                })
                            );

                        }

                        axios.all(promises).then((result) => {
                            console.log(promises);
                            if (search_hotel == -1) {
                                search_hotel = 0;
                                this.$set(this.hotels[search_hotel], 'selected', true)
                            }
                            this.getClientRatePlans(this.hotels[search_hotel].hotel_id, this.hotels[search_hotel].client_id)
                            this.getClientRatePlanSelected(this.hotels[search_hotel].hotel_id, this.hotels[search_hotel].client_id)
                            this.blockPage = false;
                            // this.msg = 'Procesando';
                        })

                    }
                }

            },
            moveAllRatesBK: function () {
                //if (this.loading === false) {
                    if (this.validatePeriod() != false) {
                        this.searchMarkup()

                        this.loading = true

                        if(this.hotels.length == 0){
                            alert('No hay clientes para procesar')
                            return false;
                        }

                        if (this.rates.length > 0) {

                            let search_hotel = this.searchSelectHotel()

                            if(this.aplicaTarifa == 1) {
                                if (search_hotel == -1) {
                                    alert('Selecciona un cliente')
                                    return false;
                                }
                            }
                            this.blockPage = true
                            API({
                                method: 'post',
                                url: 'client_rate_plans/store/all',
                                data: {
                                    client_id: this.aplicaTarifa == 1 ? this.hotels[search_hotel].client_id : '',
                                    hotel_id: this.$route.params.hotel_id,
                                    period: this.selectPeriod,
                                    market:this.selectMarkets,
                                    porcentage: this.porcentage,
                                    aplicaTarifa : this.aplicaTarifa,
                                    query: this.query
                                }
                            })
                                .then((result) => {
                                    this.blockPage = false
                                    if (result.data.success === true) {

                                        this.markup = ''
                                        if (search_hotel == -1) {
                                            search_hotel = 0;
                                            this.$set(this.hotels[search_hotel], 'selected', true)
                                        }
                                        this.getClientRatePlans(this.hotels[search_hotel].hotel_id, this.hotels[search_hotel].client_id)
                                        this.getClientRatePlanSelected(this.hotels[search_hotel].hotel_id, this.hotels[search_hotel].client_id)


                                    }
                                }).catch((e) => {
                                    this.blockPage = false
                                    this.showError(
                                    this.$t('clientsmanageclienthotel.error.messages.name'),
                                    this.$t('clientsmanageclienthotel.error.messages.connection_error') + e
                                )
                            })


                        }
                    }

                //}
            },
            inverseAllHotels: function () {
                // if (this.loading === false) {
                    if (this.validatePeriod() != false) {
                        this.searchMarkup()

                        this.loading = true
                        if (this.hotels_selected.length > 0) {
                            for (let i = 0; i < this.hotels_selected.length; i++) {

                                this.hotels.push(this.hotels_selected[i])
                            }
                            this.hotels_selected = []
                            this.blockPage = true
                            API({
                                method: 'post',
                                url: 'client_hotels/inverse/clientAll',
                                data: {
                                    hotel_id: this.$route.params.hotel_id,
                                    period: this.selectPeriod,
                                    market:this.selectMarkets,
                                    query: this.query
                                }
                            })
                                .then((result) => {
                                    this.blockPage = false
                                    if (result.data.success === true) {
                                        this.getClients()
                                        this.clientRates = []
                                        this.clientRatesSelected = []
                                        this.markup = ''
                                        // this.loading = false
                                    }
                                }).catch((e) => {
                                this.blockPage = false
                                this.showError(
                                    this.$t('clientsmanageclienthotel.error.messages.name'),
                                    this.$t('clientsmanageclienthotel.error.messages.connection_error') + e
                                )
                            })
                        }
                    }
                // } else {
                //     console.log('Bloqueado accion')
                // }
            },
            inverseAllRates: function () {
                // if (this.loading === false) {
                    if (this.validatePeriod() != false) {
                        this.searchMarkup()

                        if(this.hotels.length == 0){
                            alert('No hay clientes para procesar')
                            return false;
                        }

                        if (this.rates_selected.length > 0) {

                            this.rates_selected = []
                            let search_hotel = this.searchSelectHotel()
                            let hotel = this.hotels[search_hotel];


                            if(this.aplicaTarifaBlocked == 1) {
                                if (search_hotel == -1) {
                                    alert('Selecciona un cliente')
                                    return false;
                                }
                            }

                            this.blockPage = true
                            API({
                                method: 'post',
                                url: 'client_rate_plans/inverse/all',
                                data: {
                                    client_id: this.aplicaTarifaBlocked == 1 ? this.hotels[search_hotel].client_id : '',
                                    hotel_id:this.$route.params.hotel_id,
                                    period: this.selectPeriod,
                                    market:this.selectMarkets,
                                    hotel_id: this.$route.params.hotel_id,
                                    aplicaTarifa: this.aplicaTarifaBlocked,
                                    query: this.query
                                }
                            })
                                .then((result) => {
                                    this.blockPage = false
                                    if (result.data.success === true) {

                                        if (search_hotel == -1) {
                                            this.selectHotel(this.hotels[0] , 0);
                                            search_hotel = 0;
                                        }

                                        let hotel = this.hotels[search_hotel];

                                        this.getClientRatePlanSelected(this.$route.params.hotel_id,hotel.client_id)
                                        //this.clientRates = []
                                        //this.clientRatesSelected = []
                                        //this.markup = ''
                                        // this.loading = false
                                    }
                                }).catch((e) => {
                                    this.blockPage = false
                                    this.showError(
                                        this.$t('clientsmanageclienthotel.error.messages.name'),
                                        this.$t('clientsmanageclienthotel.error.messages.connection_error') + e
                                )
                            })
                        }
                    }
                // } else {
                //     console.log('Bloqueado accion')
                // }
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
            getClientRatePlans: function (hotel_id,client_id) {
                API({
                    method: 'post',
                    url: 'client_rate_plans',
                    data: {
                        client_id: client_id,
                        period: this.selectPeriod,
                        market:this.selectMarkets,
                        hotel_id: hotel_id
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
                        this.$t('clientsmanageclienthotel.error.messages.name'),
                        this.$t('clientsmanageclienthotel.error.messages.connection_error')
                    )
                })
            },
            getClientRatePlanSelected: function (hotel_id,client_id) {
                API({
                    method: 'post',
                    url: 'client_rate_plans/selected',
                    data: {
                        client_id: client_id,
                        period: this.selectPeriod,
                        market:this.selectMarkets,
                        hotel_id: hotel_id
                    }
                })
                    .then((result) => {
                        //this.clientRatesSelected = result.data.data
                        this.rates = result.data.data;

                    }).catch(() => {
                    this.showError(
                        this.$t('clientsmanageclienthotel.error.messages.name'),
                        this.$t('clientsmanageclienthotel.error.messages.connection_error')
                    )
                })
            },

            getClientRatePlanSelectedBloquet: function (hotel_id,client_id) {
                API({
                    method: 'post',
                    url: 'client_rate_plans/selected',
                    data: {
                        client_id: client_id,
                        period: this.selectPeriod,
                        market:this.selectMarkets,
                        hotel_id: hotel_id
                    }
                })
                    .then((result) => {
                        //this.clientRatesSelected = result.data.data
                        this.rates_selected = result.data.data;

                    }).catch(() => {
                    this.showError(
                        this.$t('clientsmanageclienthotel.error.messages.name'),
                        this.$t('clientsmanageclienthotel.error.messages.connection_error')
                    )
                })
            },

            getClients: function () {
                this.blockPage = true
                API({
                    method: 'post',
                    url: 'client/search/hotel',
                    data: {
                        page: 1,
                        limit: this.limit,
                        query: this.query,
                        hotel_id: this.$route.params.hotel_id,
                        period: this.selectPeriod,
                        market:this.selectMarkets
                    }
                })
                    .then((result) => {
                        this.hotels = result.data.data
                        this.count = result.data.count
                        this.calculateNumPages(result.data.count, this.limit)
                        this.scroll_limit = 2900
                        document.getElementById('list_hotels').scrollTop = 0

                        this.rates = [];
                        this.rates_selected = [];
                        if(this.hotels.length>0) {
                            let search_hotel = 0;
                            this.$set(this.hotels[search_hotel], 'selected', true)

                            this.getClientRatePlans(this.hotels[search_hotel].hotel_id, this.hotels[search_hotel].client_id)
                            this.getClientRatePlanSelected(this.hotels[search_hotel].hotel_id, this.hotels[search_hotel].client_id)
                        }

                        this.blockPage = false;

                    }).catch((e) => {
                        this.blockPage = false;
                        this.showError(
                            this.$t('clientsmanageclienthotel.error.messages.name'),
                            this.$t('clientsmanageclienthotel.error.messages.connection_error') + e
                    )
                })

            },
            getClientsScroll: function () {
                this.blockPage = true
                API({
                    method: 'post',
                    url: 'client/search/hotel',
                    data: {
                        page: this.page,
                        limit: this.limit,
                        query: this.query,
                        hotel_id: this.$route.params.hotel_id,
                        period: this.selectPeriod,
                        market:this.selectMarkets
                    }
                })
                    .then((result) => {
                        this.blockPage = false;
                        let hotels = result.data.data
                        for (let i = 0; i < hotels.length; i++) {
                            this.hotels.push(hotels[i])
                        }
                        if (this.page === 1) {
                            this.count = result.data.count
                            this.calculateNumPages(result.data.count, this.limit)
                        }
                    }).catch(() => {
                        this.blockPage = false;
                        this.showError(
                            this.$t('clientsmanageclienthotel.error.messages.name'),
                            this.$t('clientsmanageclienthotel.error.messages.connection_error')
                        )
                })

            },
            getClientsLocked: function () {
                API({
                    method: 'post',
                    url: 'client_hotels/clientLocked',
                    data: {
                        page: 1,
                        limit: this.limit_hotels_selected,
                        query: this.query_hotels_selected,
                        hotel_id: this.$route.params.hotel_id,
                        period: this.selectPeriod,
                        market:this.selectMarkets
                    }
                })
                    .then((result) => {
                        this.hotels_selected = result.data.data

                        this.count_hotels_selected = result.data.count
                        this.calculateNumPagesHotelsSelected(result.data.count, this.limit_hotels_selected)
                        this.scroll_limit_hotels_selected = 2900
                        document.getElementById('list_hotels_selected').scrollTop = 0

                    }).catch((e) => {
                    this.showError(
                        this.$t('clientsmanageclienthotel.error.messages.name'),
                        this.$t('clientsmanageclienthotel.error.messages.connection_error') + e
                    )
                })
            },
            getClientsScrollSelected: function () {
                API({
                    method: 'post',
                    url: 'client_hotels',
                    data: {
                        page: this.page_hotels_selected,
                        limit: this.limit_hotels_selected,
                        query: this.query_hotels_selected,
                        client_id: this.$route.params.client_id,
                        period: this.selectPeriod,
                        market:this.selectMarkets
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
                        this.$t('clientsmanageclienthotel.error.messages.name'),
                        this.$t('clientsmanageclienthotel.error.messages.connection_error')
                    )
                })
            },
            updateOneHotel: function () {
                if (this.loading === false) {
                    if (this.validateMarkup() != false) {
                        this.loading = true
                        let search_hotel = this.searchSelectHotel()
                        if (search_hotel !== -1) {

                            API({
                                method: 'put',
                                url: 'client_hotels/update',
                                data: {
                                    hotel_id: this.hotels[search_hotel].hotel_id,
                                    client_id: this.hotels[search_hotel].client_id,
                                    period: this.selectPeriod,
                                    market:this.selectMarkets,
                                    markup: this.markup,
                                    markupId:this.markupId
                                }
                            })
                                .then((result) => {
                                    if (result.data.success === true) {
                                        this.getClients()
                                        this.getClientsLocked()
                                        this.rates_selected = [];
                                        this.rates = [];
                                        this.markup = '';
                                        //this.clientRates = []
                                        //this.clientRatesSelected = []
                                        this.loading = false
                                    }
                                }).catch(() => {
                                this.showError(
                                    this.$t('clientsmanageclienthotel.error.messages.name'),
                                    this.$t('clientsmanageclienthotel.error.messages.connection_error')
                                )
                                this.loading = false
                            })
                        }else{

                            this.showError(
                                this.$t('clientsmanageclienthotel.error.messages.name'),
                                this.$t('clientsmanageclienthotel.error.messages.connection_error')
                            )
                            this.loading = false
                        }
                    }else{
                        this.loading = false;
                    }
                } else {
                    console.log('Bloqueado accion ' + this.loading)
                }
            },
            updateOneRate: function () {
                // if (this.loading === false) {
                    if (this.validateMarkupRate() != false) {
                        this.loading = true
                        let search_hotel = this.searchSelectHotel()
                        let search_rate = this.searchSelectRate()
                        if (search_rate !== -1) {

                            let selectedRateId = Array.isArray(search_rate) ? search_rate[0] : search_rate;
                            let selectedRate = this.rates.find(rate => rate.id === selectedRateId);

                            API({
                                method: 'put',
                                url: 'client_rate_plans/update',
                                data: {
                                    rate_plan_id: selectedRate?.id,
                                    client_id: this.hotels[search_hotel].client_id,
                                    period: this.selectPeriod,
                                    market:this.selectMarkets,
                                    markup: this.markup_rate
                                }
                            })
                                .then((result) => {
                                    if (result.data.success === true) {
                                        let hotel = this.hotels[search_hotel];
                                        this.getClientRatePlans(hotel.hotel_id,hotel.client_id)
                                        this.getClientRatePlanSelected(hotel.hotel_id,hotel.client_id)
                                        this.markup_rate = ''
                                        //this.clientRates = []
                                        //this.clientRatesSelected = []
                                        this.loading = false
                                    }
                                }).catch(() => {
                                this.showError(
                                    this.$t('clientsmanageclienthotel.error.messages.name'),
                                    this.$t('clientsmanageclienthotel.error.messages.connection_error')
                                )
                                this.loading = false;
                            })
                        } else {
                            this.loading = false;
                        }
                    }
                // } else {
                //     console.log('Bloqueado accion')
                // }
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


