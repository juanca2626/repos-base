<template>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 text-left" style="padding-top: 20px;">
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
            <div class="row col-lg-12" style="padding-bottom: 0" v-show="!loading">
                <div class="col-lg-4">
                    <label>Rates</label>
                </div>
                <div class="row col-lg-8">
                    <div class="col-lg-4">
                        <label>Clients</label>
                    </div>
                    <div class="row col-lg-8 text-right">
                        <div class="col-lg-8">
                            <label style="padding: 0.4rem">Periodo</label>
                        </div>
                        <div class="col-lg-4">
                            <select @change="updateRatesAndClients" class="form-control" v-model="period">
                                <option value="2019">2019</option>
                                <option value="2020">2020</option>
                                <option value="2021">2021</option>
                                <option value="2022">2022</option>
                                <option value="2023">2023</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row col-lg-12" v-show="!loading">
                <div class="col-lg-4">
                    <ul class="style_list_ul_rates_sale">
                        <li :id="'rate_li_'+index_rate" @click="selectRate(index_rate,rate.rate_plan_id)"
                            class="style_list_li_rates_sale"
                            v-for="(rate,index_rate) in rates">
                            <span class="style_span_li_rates_sale">{{ rate.name }}</span>
                        </li>
                    </ul>
                </div>
                <div class="col-lg-8">
                    <table class="table table-bordered table-active">
                        <thead class="text-center">
                        <th>
                            <input @change="selectAllClients" type="checkbox" v-model="select_all">
                        </th>
                        <th>Client</th>
                        <th>Markup</th>
                        </thead>
                        <tbody class="text-center">
                        <tr v-for="(hotel_client,index_hotel_client) in hotel_clients">
                            <td>
                                <input type="checkbox" v-model="hotel_client.selected">
                            </td>
                            <td>{{ hotel_client.name }}</td>
                            <td>
                                <input class="text-center" type="text"
                                       v-model="hotel_clients[index_hotel_client].markup" @keypress="onlyNumbersFloat($event)">
                            </td>
                        </tr>
                        </tbody>
                    </table>

                </div>
            </div>
            <div class="row col-lg-12" v-show="!loading">
                <div class="col-lg-4"></div>
                <div class="col-lg-8">
                    <button @click="storeRatePlanClients" class="btn btn-success">{{ $t('global.buttons.save') }}
                    </button>
                </div>
            </div>
            <div class="table-loading text-center" v-show="loading">
                <img alt="loading" height="51px" src="/images/loading.svg"/>
            </div>
        </div>
    </div>
</template>
<script>
  import { API } from './../../../../../../api'
  import BFormCheckbox from 'bootstrap-vue/es/components/form-checkbox/form-checkbox'
  import Calendar from './Calendar'

  export default {
    components: {
      BFormCheckbox,
      Calendar
    },
    data () {
      return {
        showCalendar: false,
        period: '2019',
        rates: [],
        select_all: false,
        hotel_clients: [],
        rate_plan_id_selected: null,
        rate_index_selected: null,
        loading: false
      }
    },
    computed: {},
    mounted: function () {
      this.getRatesByHotel()
      this.getClientsHotel()
    },
    methods: {
      onlyNumbersFloat:function(event){
        if (event.keyCode === 46){
          return true
        }
        if (event.keyCode < 48 || event.keyCode > 57) {
          event.preventDefault()
        }

      },
      updateRatesAndClients:function(){
        this.unSelectRates()
        this.getRatesByHotel()
        this.getClientsHotel()
      },
      getRatesByHotel: function () {
        this.loading = true
        API({
          method: 'post',
          url: 'rates/by/hotel',
          data: {
            period: this.period,
            hotel_id: this.$route.params.hotel_id
          }
        })
          .then((result) => {
            this.rates = result.data.data
            this.loading = false
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('error.messages.name'),
            text: this.$t('error.messages.connection_error')
          })
        })

      },
      getClientsHotel: function () {
        this.loading = true
        API({
          method: 'post',
          url: 'client_hotels/by/period',
          data: {
            period: this.period,
            hotel_id: this.$route.params.hotel_id
          }
        })
          .then((result) => {
            this.hotel_clients = result.data.data
            if (this.rate_index_selected !== null && (this.rates[this.rate_index_selected]['clients_rate_plan'].length > 0)) {
              for (let i = 0; i < this.rates[this.rate_index_selected]["clients_rate_plan"].length; i++) {
                for (let j = 0; j < this.hotel_clients.length; j++) {
                  if (this.rates[this.rate_index_selected]['clients_rate_plan'][i]['client_id'] === this.hotel_clients[j]['id']) {
                    this.hotel_clients[j]['client_rate_plan_id'] = this.rates[this.rate_index_selected]['clients_rate_plan'][i]['id']
                    this.hotel_clients[j]['markup'] = this.rates[this.rate_index_selected]['clients_rate_plan'][i]['markup']
                    this.hotel_clients[j]['selected'] = true
                  }
                }
              }
            }
            this.loading = false
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('error.messages.name'),
            text: this.$t('error.messages.connection_error')
          })
        })
      },
      selectRate: function (index_rate, rate_plan_id) {
        this.unSelectRates()
        document.getElementById('rate_li_' + index_rate).classList.add('selected_rates_sale')
        this.rate_index_selected = index_rate
        this.rate_plan_id_selected = rate_plan_id
        this.getClientsHotel()
      },
      unSelectRates:function () {
        this.rate_index_selected = null
        this.rate_plan_id_selected = null
        let rates = document.getElementsByClassName('style_list_li_rates_sale')

        for (let i = 0; i < rates.length; i++) {
          if (rates[i].classList.contains('selected_rates_sale')) {

            rates[i].classList.remove('selected_rates_sale')
          }
        }
      },
      selectAllClients: function () {
        for (let i = 0; i < this.hotel_clients.length; i++) {
          this.hotel_clients[i].selected = this.select_all
        }
      },
      storeRatePlanClients: function () {
        if (this.rate_plan_id_selected !== null) {
          if (this.loading === false) {
            this.loading = true
            API({
              method: 'post',
              url: 'rates/plans/store/clients/',
              data: {
                hotel_clients: this.hotel_clients,
                rate_plan_id: this.rate_plan_id_selected,
                period: this.period
              }
            })
              .then((result) => {
                if (result.data.success) {
                  this.getRatesByHotel()
                  this.getClientsHotel()
                }
              }).catch(() => {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('error.messages.name'),
                text: this.$t('error.messages.connection_error')
              })
            })
          }
        } else {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('error.messages.name'),
            text: 'Debe tener seleccionada al menos 1 tarifa'
          })
        }
      }
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

    .style_list_ul_rates_sale {
        list-style-type: none;
        padding: 0px;
        margin-left: -1px;
        border-left: 1px solid #ccc;
        border-right: 1px solid #ccc;
        border-top: 1px solid #ccc;
    }

    .selected_rates_sale {
        background-color: #005ba5;
        color: white;
    }

    .style_list_li_rates_sale {
        border-bottom: 1px solid #ccc;
        padding: 5px 5px 5px 5px;
    }

    .style_span_li_rates_sale {
        margin-left: 5px;
    }

</style>