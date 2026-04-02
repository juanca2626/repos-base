<template>
    <div class="row col-12">
        <div class="col-12">
            <div class="row">
                <div class="col-5 pull-right">
                    <div class="b-form-group form-group">
                        <div class="form-row">
                            <label class="col-sm-3 col-form-label" for="period">{{ $t('clientsmanageclienthotel.period')
                                }}</label>
                            <div class="col-sm-8">
                                <select @change="searchPeriod" ref="period" class="form-control" id="period" required
                                        size="0" v-model="selectPeriod">
                                    <option :value="period.text" v-for="period in periods">
                                        {{ period.text}} - {{period.porcen_hotel}} %
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
            <label class="col-sm-12 col-form-label" for="period">{{ $t('clientsmanageclienthotel.available_hotels')
                }}</label>
            <div class="input-group">
                 <span class="input-group-append">
                    <button class="btn btn-outline-secondary button_icon" type="button">
                        <font-awesome-icon :icon="['fas', 'search']"/>
                    </button>
                 </span>
                <input class="form-control" id="search_hotels" type="search" v-model="query" value="">
            </div>
            <ul class="style_list_ul" id="list_hotels" ref="list_hotels" style="background-color: #4dbd7429;">
                <draggable :list="hotels">
                    <li :class="{'style_list_li':true, 'item':true, 'selected':hotel.selected}" :id="'hotel_'+index"

                        @click="selectHotel(hotel,index)" v-for="(hotel,index) in hotels">
                        <span class="style_span_li">{{ hotel.name}} ({{ hotel.markup ? hotel.markup : porcentage}}%)</span>
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
            <label class="col-sm-12 col-form-label" for="period">{{ $t('clientsmanageclienthotel.hotels_added') }}</label>
            <div class="input-group">
                <span class="input-group-append">
                    <button class="btn btn-outline-secondary button_icon" type="button">
                        <font-awesome-icon :icon="['fas', 'search']"/>
                    </button>
                </span>
                <input class="form-control" id="search_hotels_selected" type="search" v-model="query_hotels_selected"
                       value="">
            </div>
            <ul class="style_list_ul" id="list_hotels_selected" ref="list_hotels_selected" style="background-color: #bd0d121c;">
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
            <label class="col-sm-12 col-form-label" for="period">
                {{ $t('clientsmanageclienthotel.available_hotels_rates') }}
            </label>

            <ul class="style_list_ul" id="list_rates" style="border-top:1px solid #ccc;max-height: 196px;height: 196px;background-color: #4dbd7429;">
                <draggable :list="rates">
                    <li :class="{'style_list_li':true, 'item':true, 'selected':rate.selected}" :id="'rate_'+index"
                        @click="selectRate(rate,index)" v-for="(rate,index) in rates">
                        <span class="style_span_li">{{ rate.name}} ({{ rate.markup ? rate.markup : selectMarkup()}}%)</span>
                    </li>
                </draggable>
            </ul>
        </div>
        <div class="col-1 mt-4 mr-2">
<!--            <div class="col-12">-->
<!--                <button @click="moveOneRate()" class="btn btn-secondary mover_controls btn-block pr-3">-->
<!--                    <font-awesome-icon :icon="['fas', 'angle-right']"/>-->
<!--                </button>-->
<!--            </div>-->
<!--            <div class="col-12">-->
<!--                <button @click="inverseOneRate()" class="btn btn-secondary mover_controls btn-block pr-3">-->
<!--                    <font-awesome-icon :icon="['fas', 'angle-left']"/>-->
<!--                </button>-->
<!--            </div>-->
        </div>
        <div class="col-5">
            <label class="col-sm-12 col-form-label" for="period">{{ $t('clientsmanageclienthotel.hotels_rates_blocked')
                }}</label>

            <ul class="style_list_ul" id="list_rates_selected" style="border-top:1px solid #ccc;max-height: 196px;height: 196px;background-color: #bd0d121c;">
                <draggable :list="rates_selected" class="list-group">
                    <li :class="{'style_list_li':true, 'item':true, 'selected':rate.selected}"
                        @click="selectRateRatesSelected(rate,index)" v-for="(rate,index) in rates_selected">
                        <span class="style_span_li">{{ rate.name}}</span>
                    </li>
                </draggable>
            </ul>
        </div>

<!--        <div class="col-10">-->
<!--            <div class="row">-->
<!--                <div class="col-5 pull-left">-->
<!--                    <div class="b-form-group form-group">-->
<!--                        <div class="form-row">-->
<!--                            <label class="col-sm-6 col-form-label" for="markup">{{-->
<!--                                $t('clientsmanageclienthotel.personal_markup') }}</label>-->
<!--                            <div class="col-sm-6">-->
<!--                                <input :class="{'form-control':true }"-->
<!--                                       id="markup_rate" name="markup_rate"-->
<!--                                       type="text"-->
<!--                                       ref="auroraCodeName" v-model="markup_rate"-->
<!--                                >-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                </div>-->
<!--                <div class="col-5">-->
<!--                    <button @click="updateOneRate" class="btn btn-success mb-4" type="reset">-->
<!--                        {{ $t('clientsmanageclienthotel.update') }}-->
<!--                    </button>-->
<!--                </div>-->
<!--                <div class="clearfix"></div>-->
<!--            </div>-->
<!--        </div>-->


    </div>
</template>
<script>
  import { API } from './../../../../api'
  import draggable from 'vuedraggable'
  import TableClient from './.././../../../components/TableClient'

  export default {
    components: {
      draggable,
      'table-client': TableClient,
    },
    data () {
      return {
        users: [],
        scroll_limit: 2900,
        hotels: [],
        rates: [],
        page: 1,
        selectPeriod: '',
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
        }
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
      }
    },
    mounted: function () {
      this.init();
    },
    methods: {
      init: function (){
        this.getPeriods()

        let search_hotels = document.getElementById('search_hotels')
        let timeout_hotels
        search_hotels.addEventListener('keydown', () => {
          clearTimeout(timeout_hotels)
          timeout_hotels = setTimeout(() => {
            this.getHotels()
            clearTimeout(timeout_hotels)
          }, 1000)
        })

        let search_hotels_selected = document.getElementById('search_hotels_selected')
        let timeout_hotels_selected
        search_hotels_selected.addEventListener('keydown', () => {
          clearTimeout(timeout_hotels_selected)
          timeout_hotels_selected = setTimeout(() => {
            this.getHotelsSelected()
            clearTimeout(timeout_hotels_selected)
          }, 1000)
        })

        this.interval = setInterval(this.getScrollTop, 3000)
        this.interval_hotels_selected = setInterval(this.getScrollTopHotelsSelected, 3000)

      },
      showError: function (title, text, isLoading = true) {
        this.$notify({
          group: 'main',
          type: 'error',
          title: title,
          text: text,
          duration: 10000
        })
        if (isLoading === true) {
          this.loading = true
        }
      },
      searchPeriod: function () {
        this.getHotels()
        this.getHotelsSelected()
        this.searchMarkup()
        this.searchMarkupId()
      },
      searchMarkup: function () {
        let data = this.periods.find(period => period.text == this.selectPeriod)
        this.porcentage = data.porcen_hotel
      },
      searchMarkupId: function () {
        let data = this.periods.find(period => period.text == this.selectPeriod)
        this.markupId = data.value
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

          this.getClientRatePlans(hotel.hotel_id)
          this.getClientRatePlanSelected(hotel.hotel_id)
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
      searchSelectRate:function(){
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
      searchSelectRateRatesSelected: function (){
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
      selectMarkup: function (){
          let search_hotel = this.searchSelectHotel()
          if (search_hotel !== -1) {
             let hotels =  this.hotels[search_hotel];
             if(hotels.markup){
                return hotels.markup;
             }else{
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
                url: 'client_hotels/store',
                data: {
                  data: this.hotels[search_hotel],
                  period: this.selectPeriod,
                  porcentage: this.porcentage,
                  region_id: this.$route.params.region_id
                }
              })
                .then((result) => {
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
                this.showError(
                  this.$t('clientsmanageclienthotel.error.messages.name'),
                  this.$t('clientsmanageclienthotel.error.messages.connection_error')
                )
              })
            } else {
              if (this.hotels.length > 0) {
                this.loading = true
                let element = this.hotels.shift()
                API({
                  method: 'post',
                  url: 'client_hotels/store',
                  data: {
                    data: element,
                    period: this.selectPeriod,
                    porcentage: this.porcentage
                  }
                })
                  .then((result) => {
                    if (result.data.success === true) {
                      element.hotel_client_id = result.data.hotel_client_id
                      this.hotels_selected.push(element)
                      this.rates = [];
                      this.rates_selected = [];
                      this.loading = false
                    }
                  }).catch((e) => {
                  this.showError(
                    this.$t('clientsmanageclienthotel.error.messages.name'),
                    this.$t('clientsmanageclienthotel.error.messages.connection_error') + e
                  )
                  console.log(e);
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
            let search_rate = this.searchSelectRate()
            if (search_rate !== -1) {
              API({
                method: 'post',
                url: 'client_rate_plans/store',
                data: {
                  rate_plan_id: [this.rates[search_rate].id],
                  client_id: this.$route.params.client_id,
                  period: this.selectPeriod,
                  aplicaTarifa: 1,
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
                  this.$t('clientsmanageclienthotel.error.messages.name'),
                  this.$t('clientsmanageclienthotel.error.messages.connection_error')
                )
              })
            } else {
              if (this.rates.length > 0) {
                this.loading = true
                let element = this.rates.shift()
                API({
                  method: 'post',
                  url: 'client_rate_plans/store',
                  data: {
                    data: element,
                    rate_plan_id: element.id,
                    client_id: this.$route.params.client_id,
                    period: this.selectPeriod,
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
                    this.$t('clientsmanageclienthotel.error.messages.name'),
                    this.$t('clientsmanageclienthotel.error.messages.connection_error') + e
                  )
                  console.log(e);
                })
              }
            }

          }
        } else {
          console.log('Bloqueado accion')
        }
      },
      inverseOneHotel: function () {
        if (this.loading === false) {
          if (this.validatePeriod() != false) {
            this.searchMarkup()

            this.loading = true

            let search_hotel = this.searchSelectHotelHotelsSelected()
            if (search_hotel !== -1) {

              API({
                method: 'post',
                url: 'client_hotels/inverse',
                data: {
                  data: this.hotels_selected[search_hotel],
                  period: this.selectPeriod
                }
              })
                .then((result) => {
                  if (result.data.success === true) {
                    this.getHotels()
                    this.getHotelsSelected()
                    this.clientRates = []
                    this.clientRatesSelected = []
                    this.markup = ''
                    this.loading = false
                  }
                }).catch(() => {
                this.showError(
                  this.$t('clientsmanageclienthotel.error.messages.name'),
                  this.$t('clientsmanageclienthotel.error.messages.connection_error')
                )
              })
            } else {
              if (this.hotels_selected.length > 0) {
                this.loading = true
                let element = this.hotels_selected.shift()
                API({
                  method: 'post',
                  url: 'client_hotels/inverse',
                  data: {
                    data: element,
                    period: this.selectPeriod
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
                    this.$t('clientsmanageclienthotel.error.messages.name'),
                    this.$t('clientsmanageclienthotel.error.messages.connection_error') + e
                  )
                  console.log(e)
                })
              }
            }
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
            if (search_rate !== -1) {

              API({
                method: 'post',
                url: 'client_rate_plans/inverse',
                data: {
                  rate_plan_id: this.rates_selected[search_rate].id,
                  client_id: this.$route.params.client_id,
                  period: this.selectPeriod,
                  aplicaTarifa: 1,
                }
              })
                .then((result) => {
                  if (result.data.success === true) {
                    let hotel = this.hotels[search_hotel];
                    this.getClientRatePlans(hotel.hotel_id)
                    this.getClientRatePlanSelected(hotel.hotel_id)
                    //this.clientRates = []
                    //this.clientRatesSelected = []
                    //this.markup = ''
                    this.loading = false
                  }
                }).catch(() => {
                this.showError(
                  this.$t('clientsmanageclienthotel.error.messages.name'),
                  this.$t('clientsmanageclienthotel.error.messages.connection_error')
                )
              })
            } else {
              if (this.hotels_selected.length > 0) {
                this.loading = true
                let element = this.hotels_selected.shift()
                API({
                  method: 'post',
                  url: 'client_hotels/inverse',
                  data: {
                    data: element,
                    period: this.selectPeriod
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
                    this.$t('clientsmanageclienthotel.error.messages.name'),
                    this.$t('clientsmanageclienthotel.error.messages.connection_error') + e
                  )
                  console.log(e)
                })
              }
            }
          }
        } else {
          console.log('Bloqueado accion')
        }
      },
      moveAllHotels: function () {
        if (this.loading === false) {
          if (this.validatePeriod() != false) {
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
                url: 'client_hotels/store/all',
                data: {
                  client_id: this.$route.params.client_id,
                  period: this.selectPeriod,
                  porcentage: this.porcentage
                }
              })
                .then((result) => {
                  if (result.data.success === true) {
                    this.getHotelsSelected()
                    this.markup = ''
                    this.rates = [];
                    this.rates_selected = [];
                    this.loading = false
                  }
                }).catch((e) => {
                this.showError(
                  this.$t('clientsmanageclienthotel.error.messages.name'),
                  this.$t('clientsmanageclienthotel.error.messages.connection_error') + e
                )
                console.log(e);
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
                  url: 'client_rate_plans/store/all',
                  data: {
                    client_id: this.$route.params.client_id,
                    hotel_id: this.hotels[search_hotel].hotel_id,
                    period: this.selectPeriod,
                    porcentage: this.porcentage,
                    aplicaTarifa: 1
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
                    this.$t('clientsmanageclienthotel.error.messages.name'),
                    this.$t('clientsmanageclienthotel.error.messages.connection_error') + e
                  )
                  console.log(e)
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
                url: 'client_hotels/inverse/all',
                data: {
                  client_id: this.$route.params.client_id,
                  period: this.selectPeriod
                }
              })
                .then((result) => {
                  if (result.data.success === true) {
                    this.getHotels()
                    this.clientRates = []
                    this.clientRatesSelected = []
                    this.markup = ''
                    this.loading = false
                  }
                }).catch((e) => {
                this.showError(
                  this.$t('clientsmanageclienthotel.error.messages.name'),
                  this.$t('clientsmanageclienthotel.error.messages.connection_error') + e
                )
                console.log(e)
              })
            }
          }
        } else {
          console.log('Bloqueado accion')
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
              let hotel = this.hotels[search_hotel];
              API({
                method: 'post',
                url: 'client_rate_plans/inverse/all',
                data: {
                  client_id: this.$route.params.client_id,
                  hotel_id:hotel.hotel_id,
                  aplicaTarifa: 1,
                  period: this.selectPeriod
                }
              })
                .then((result) => {
                  if (result.data.success === true) {
                    this.getClientRatePlanSelected(hotel.hotel_id)
                    //this.clientRates = []
                    //this.clientRatesSelected = []
                    //this.markup = ''
                    this.loading = false
                  }
                }).catch((e) => {
                this.showError(
                  this.$t('clientsmanageclienthotel.error.messages.name'),
                  this.$t('clientsmanageclienthotel.error.messages.connection_error') + e
                )
                console.log(e)
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
        if (!this.$refs.list_hotels) return;

        let scroll = this.$refs.list_hotels.scrollTop
        if (scroll > this.scroll_limit) {
          this.page += 1
          this.scroll_limit = 2900 * this.page
          if (this.page === this.num_pages) {
            clearInterval(this.interval)
            this.getHotelsScroll()
          } else {

            this.getHotelsScroll()
          }

        }
      },
      getScrollTopHotelsSelected: function () {
        if (!this.$refs.list_hotels_selected) return;

        let scroll = this.$refs.list_hotels_selected.scrollTop
        if (scroll > this.scroll_limit_hotels_selected) {
          this.page_hotels_selected += 1
          this.scroll_limit_hotels_selected = 2900 * this.page_hotels_selected
          if (this.page_hotels_selected === this.num_pages_hotels_selected) {
            clearInterval(this.interval_hotels_selected)
            this.getHotelsScrollSelected()
          } else {

            this.getHotelsScrollSelected()
          }

        }
      },
      getPeriods: function () {
          var currentTime = new Date();
          var year = currentTime.getFullYear()
         
          API.get('client_hotels/selectPeriod?lang=' + localStorage.getItem('lang') + '&client_id=' + this.$route.params.client_id + '&region_id=' + this.$route.params.region_id)
          .then((result) => {
            this.periods = result.data.data
             this.selectPeriod = '';
              if(result.data.data.length >0){
                  for (var i = 0; i < result.data.data.length; i++) {                     
                      if (Number(result.data.data[i].text)  == year){          
                          this.selectPeriod = result.data.data[i].text;
                          this.porcentage = result.data.data[i].porcen_hotel;
                      }
                  }
              }
            // this.selectPeriod = !result.data.data.length ? '' : result.data.data[0].text
            // this.porcentage = !result.data.data.length ? '' : result.data.data[0].porcen_hotel
            this.getHotels()
            this.getHotelsSelected()
          }).catch(() => {
          this.showError(
            this.$t('clientsmanageclienthotel.error.messages.name'),
            this.$t('clientsmanageclienthotel.error.messages.connection_error')
          )
        })
      },
      getClientRatePlans: function (hotel_id) {
        API({
          method: 'post',
          url: 'client_rate_plans',
          data: {
            client_id: this.$route.params.client_id,
            period: this.selectPeriod,
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
      getClientRatePlanSelected: function (hotel_id) {
        API({
          method: 'post',
          url: 'client_rate_plans/selected',
          data: {
            client_id: this.$route.params.client_id,
            period: this.selectPeriod,
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
      getHotels: function () {
        API({
          method: 'post',
          url: 'hotel/search/client',
          data: {
            page: 1,
            limit: this.limit,
            query: this.query,
            client_id: this.$route.params.client_id,
            period: this.selectPeriod,
            region_id: this.$route.params.region_id
          }
        })
          .then((result) => {
            this.hotels = result.data.data
            this.count = result.data.count
            this.calculateNumPages(result.data.count, this.limit)
            this.scroll_limit = 2900
            this.$refs.list_hotels.scrollTop = 0

          }).catch(() => {
          this.showError(
            this.$t('clientsmanageclienthotel.error.messages.name'),
            this.$t('clientsmanageclienthotel.error.messages.connection_error')
          )
        })

      },
      getHotelsScroll: function () {

        API({
          method: 'post',
          url: 'hotel/search/client',
          data: {
            page: this.page,
            limit: this.limit,
            query: this.query,
            client_id: this.$route.params.client_id,
            period: this.selectPeriod
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
            this.$t('clientsmanageclienthotel.error.messages.name'),
            this.$t('clientsmanageclienthotel.error.messages.connection_error')
          )
        })

      },
      getHotelsSelected: function () {
        API({
          method: 'post',
          url: 'client_hotels',
          data: {
            page: 1,
            limit: this.limit_hotels_selected,
            query: this.query_hotels_selected,
            client_id: this.$route.params.client_id,
            period: this.selectPeriod,
            region_id: this.$route.params.region_id
          }
        })
          .then((result) => {
            this.hotels_selected = result?.data?.data || {}
            this.count_hotels_selected = result.data.count || 0
            this.calculateNumPagesHotelsSelected(this.count_hotels_selected, this.limit_hotels_selected)
            this.scroll_limit_hotels_selected = 2900
            this.$nextTick(() => {
                if (this.$refs.list_hotels_selected) {
                    this.$refs.list_hotels_selected.scrollTop = 0
                }
            })

          }).catch((e) => {
          this.showError(
            this.$t('clientsmanageclienthotel.error.messages.name'),
            this.$t('clientsmanageclienthotel.error.messages.connection_error') + e
          )
          console.log(e);
        })
      },
      getHotelsScrollSelected: function () {
        API({
          method: 'post',
          url: 'client_hotels',
          data: {
            page: this.page_hotels_selected,
            limit: this.limit_hotels_selected,
            query: this.query_hotels_selected,
            client_id: this.$route.params.client_id,
            period: this.selectPeriod
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
                  client_id: this.$route.params.client_id,
                  period: this.selectPeriod,
                  markup: this.markup,
                  markupId:this.markupId
                }
              })
                .then((result) => {
                  if (result.data.success === true) {
                    this.getHotels()
                    this.getHotelsSelected()
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
        if (this.loading === false) {
          if (this.validateMarkupRate() != false) {
            this.loading = true
            let search_hotel = this.searchSelectHotel()
            let search_rate = this.searchSelectRate()
            if (search_rate !== -1) {

              API({
                method: 'put',
                url: 'client_rate_plans/update',
                data: {
                  rate_plan_id: this.rates[search_rate].id,
                  client_id: this.$route.params.client_id,
                  period: this.selectPeriod,
                  markup: this.markup_rate
                }
              })
                .then((result) => {
                  if (result.data.success === true) {
                    let hotel = this.hotels[search_hotel];
                    this.getClientRatePlans(hotel.hotel_id)
                    this.getClientRatePlanSelected(hotel.hotel_id)
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

