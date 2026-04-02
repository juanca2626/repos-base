<template>
    <div class="container-fluid">
        <div class="row-fluid col-12" v-show="!loading">
            <div class="row col-12">
                <div class="row col-12">
                    <div class="col-8 text-center">
                        <label>{{ $t('hotelsmanagehotelinventories.range_by_dates') }}</label>
                    </div>
                    <div class="col-4 text-center" v-if="checkRouteButtonProcess()">
                        <label>{{ $t('hotelsmanagehotelinventories.availability') }}</label>
                    </div>
                </div>
                <div class="col-4">
                    <div class="input-group col-12">
                        <date-picker
                                :config="datePickerFromOptions"
                                @dp-change="setDateFrom"
                                ref="datePickerFrom"
                                v-model="form.dates_from"
                        >
                        </date-picker>
                        <div class="input-group-append">
                            <button class="btn btn-secondary datepickerbutton" title="Toggle"
                                    type="button">
                                <i class="far fa-calendar"></i>
                            </button>
                        </div>
                    </div>
                    <div class="bg-danger" style="border-radius: 2px;" v-show="showErrorRangeDates">
                        <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                           style="margin-left: 5px;"/>
                        <span>{{ $t('hotelsmanagehotelinventories.error.messages.dates_incomplete') }}</span>
                    </div>
                </div>
                <div class="col-4">
                    <div class="input-group col-12">
                        <date-picker
                                :config="datePickerToOptions"
                                ref="datePickerTo"
                                v-model="form.dates_to">
                        </date-picker>
                        <div class="input-group-append">
                            <button class="btn btn-secondary datepickerbutton" title="Toggle"
                                    type="button">
                                <i class="far fa-calendar"></i>
                            </button>
                        </div>
                    </div>
                    <div class="bg-danger" style="border-radius: 2px;" v-show="showErrorRangeDates">
                        <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                           style="margin-left: 5px;"/>
                        <span>{{ $t('hotelsmanagehotelinventories.error.messages.dates_incomplete') }}</span>
                    </div>
                </div>
                <div class="col-4" v-if="checkRouteButtonProcess()">
                    <input id="availability" name="availability" type="text" class="form-control"
                           v-model="form.availability" v-validate="'required|numeric'">
                    <div class="bg-danger" style="border-radius: 2px;" v-show="errors.has('availability')">
                        <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                           style="margin-left: 5px;"/>
                        <span>{{ errors.first('availability') }}</span>
                    </div>
                </div>
            </div>
            <div class="row col-12" style="margin-top: 20px">
                <table class="table table-bordered">
                    <thead class="text-center">
                    <th>{{ $t('global.days.everyone') }}</th>
                    <th>{{ $t('global.days.monday') }}</th>
                    <th>{{ $t('global.days.tuesday') }}</th>
                    <th>{{ $t('global.days.wednesday') }}</th>
                    <th>{{ $t('global.days.thursday') }}</th>
                    <th>{{ $t('global.days.friday') }}</th>
                    <th>{{ $t('global.days.saturday') }}</th>
                    <th>{{ $t('global.days.sunday') }}</th>
                    </thead>
                    <tbody>
                    <tr class="text-center">
                        <td style="cursor:pointer; padding: 5px">
                            <b-form-checkbox @click="selectAllDays()"
                                    :checked="checked_all_days"
                                    :id="'checkbox_day_all'"
                                    :name="'checkbox_day_all'"
                                    @change="selectAllDays()"
                                    switch
                                    v-model="checked_all_days">
                            </b-form-checkbox>
                        </td>
                        <td style="cursor:pointer; padding: 5px;"
                            v-for="(day, index) in form.days">
                            <b-form-checkbox :checked="day.selected"
                                             :id="'checkbox_day_'+day.day"
                                             :name="'checkbox_day_'+day.day"
                                             @click="checkDay(index)"
                                             switch
                                             v-model="form.days[index].selected">
                            </b-form-checkbox>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <div class="bg-danger text-center col-12" style="border-radius: 2px;" v-show="showErrorDays">
                    <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                       style="margin-left: 5px;"/>
                    <span>{{ $t('hotelsmanagehotelinventories.error.messages.days_incomplete') }}</span>
                </div>
            </div>
            <div class="row col-12" style="margin-top: 20px">
                <div class="col-6">
                    <ul class="style_inventory_ul">
                        <li :class="{'style_inventory_li':true,'style_inventory_li_selected':rooms[index].selected }"
                            @click="selectRoom(index)" v-for="(room, index) in rooms">{{ room.room_name}}
                        </li>
                    </ul>
                </div>
                <div class="col-6">
                    <ul class="style_inventory_ul">
                        <li :class="{'style_inventory_li':true,'style_inventory_li_selected':rates[index].selected }"
                            @click="selectRates(index)" v-for="(rate, index) in rates"
                            v-show="rooms[rate.room_id].selected">{{ rate.rate_name }}
                        </li>
                    </ul>
                </div>
            </div>
            <div class="row col-12" style="margin-top: 20px">
                <div class="col-2" v-if="checkRouteButtonProcess()">
                    <button @click="processInventory(1)" class="btn btn-success btn-block">{{
                        $t('hotelsmanagehotelinventories.process') }}
                    </button>
                </div>
                <div class="col-4" v-if="checkRouteButtonBlocked()">
                    <button @click="blockedInventory(0)" class="btn btn-success btn-block">{{
                        $t('hotelsmanagehotelinventories.enabled') }}
                    </button>
                </div>
                <div class="col-4" v-if="checkRouteButtonBlocked()">
                    <button @click="blockedInventory(1)" class="btn btn-dark btn-block">{{
                        $t('hotelsmanagehotelinventories.blocked') }}
                    </button>
                </div>
                <div class="col-8" v-if="checkRouteButtonProcess()">
                    <button @click="processInventory(2)" class="btn btn-success btn-block">{{
                        $t('hotelsmanagehotelinventories.process_with_add_others') }}
                    </button>
                </div>
                <div class="col-2">
                    <button @click="checkRouteButtonCancel()" class="btn btn-danger btn-block">{{
                        $t('global.buttons.cancel')
                        }}
                    </button>
                </div>
            </div>
        </div>
        <div class="table-loading text-center" v-show="loading">
            <img alt="loading" height="51px" src="/images/loading.svg"/>
        </div>
    </div>
</template>
<script>
  import { API } from '../../../../api'
  import datePicker from 'vue-bootstrap-datetimepicker'
  import 'pc-bootstrap4-datetimepicker/build/css/bootstrap-datetimepicker.css'
  import BFormCheckbox from 'bootstrap-vue/es/components/form-checkbox/form-checkbox'

  export default {
    components: {
      datePicker,
      BFormCheckbox
    },
    data: () => {
      return {
        checked_all_days: false,
        rooms: [],
        rates: [],
        errorRangeDates: false,
        errorDay: false,
        loading: false,
        form: {
          locked: 0,
          availability: '',
          dates_from: '',
          dates_to: '',
          rate_selected: null,
          days: [
            { day: 1, selected: false },
            { day: 2, selected: false },
            { day: 3, selected: false },
            { day: 4, selected: false },
            { day: 5, selected: false },
            { day: 6, selected: false },
            { day: 0, selected: false },
          ]
        },
        datePickerFromOptions: {
          format: 'DD/MM/YYYY',
          useCurrent: false,
          locale: localStorage.getItem('lang'),
          widgetPositioning: { 'vertical': 'bottom' }
        },
        datePickerToOptions: {
          format: 'DD/MM/YYYY',
          useCurrent: false,
          locale: localStorage.getItem('lang'),
          widgetPositioning: { 'vertical': 'bottom' }
        },
      }
    },
    computed: {
      showErrorRangeDates: function () {
        if ((this.form.dates_from === '' || this.form.dates_to === '') && this.errorRangeDates === true) {
          return true
        } else {
          return false
        }
      },
      showErrorDays: function () {

        let selected_day = this.validateSelectedDays()

        if (selected_day) {
          this.errorDay = false
        }
        if (selected_day === false && this.errorDay === true) {
          return true
        } else {
          return false
        }
      }

    },
    mounted: function () {
      this.form.hotel_id = this.$route.params.hotel_id
      this.getRoomWithRates()

    },
    methods: {
      validateSelectedDays: function () {
        let valid_day = false
        this.form.days.forEach(function (day) {

          if (day.selected === true) {
            valid_day = true
          }
        })
        return valid_day
      },
      checkRouteButtonProcess: function () {
        if (this.$route.name === 'InventoryByDateRange') {
          return true
        } else {
          return false

        }
      },
      checkRouteButtonCancel: function () {
        if (localStorage.getItem('allotment') == 0) {
          this.$router.push('/hotels/' + this.$route.params.hotel_id + '/manage_hotel/inventories/free_sale')
        } else {
          this.$router.push('/hotels/' + this.$route.params.hotel_id + '/manage_hotel/inventories/allotments')

        }
      },
      checkRouteButtonBlocked: function () {
        if (this.$route.name === 'BlockedInventoryByDateRange') {
          return true
        } else {
          return false
        }
      },
      getRoomWithRates: function () {
        API({
          method: 'post',
          url: 'rooms/with/rates',
          data: {
            hotel_id: this.$route.params.hotel_id,
            lang: localStorage.getItem('lang'),
            allotment: localStorage.getItem('allotment')
          }
        })
          .then((result) => {
            if (result.data.success === true) {

              this.rooms = result.data.rooms_array
              this.rates = result.data.rates_array
              this.getSelectRate()

            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.rooms'),
                text: this.$t('hotelsmanagehotelinventories.error.messages.information_error')
              })
            }
          })
      },
      selectRoom: function (index) {
        this.unSelectRoom()
        this.rooms[index]['selected'] = true
        this.unSelectRates()
        this.$set(this.form, 'rate_selected', this.rates[this.rooms[index]['rate_index']])
        this.rates[this.rooms[index]['rate_index']]['selected'] = true
      },
      selectRates: function (index) {
        this.unSelectRates()
        this.rates[index]['selected'] = true
        this.$set(this.form, 'rate_selected', this.rates[index])
        this.form.rate_selected = this.rates[index]
      },
      getSelectRate: function () {
        let ids_rate_keys = (Object.keys(this.rates))
        for (var i = 0; i < ids_rate_keys.length; i++) {
          if (this.rates[ids_rate_keys[i]]['selected'] === true) {
            this.$set(this.form, 'rate_selected', this.rates[ids_rate_keys[i]])
            this.form.rate_selected = this.rates[ids_rate_keys[i]]
          }
        }
      },
      unSelectRoom: function () {
        let ids_room_keys = (Object.keys(this.rooms))
        for (var i = 0; i < ids_room_keys.length; i++) {
          this.rooms[ids_room_keys[i]]['selected'] = false
        }
      },
      unSelectRates: function () {
        let ids_rate_keys = (Object.keys(this.rates))
        for (var i = 0; i < ids_rate_keys.length; i++) {
          this.rates[ids_rate_keys[i]]['selected'] = false
        }
      },
      checkDay: function (index) {
        if (this.form.days[index].selected) {
          this.form.days[index].selected = false
        } else {
          this.form.days[index].selected = true
        }
      },
      selectAllDays: function () {
        if (this.checked_all_days) {
          this.checked_all_days = false
          this.form.days.forEach(function (day) {
            day.selected = false
          })
        } else {
          this.checked_all_days = true
          this.form.days.forEach(function (day) {
            day.selected = true
          })
        }
      },
      unSelectAllDays: function () {
        this.form.days.forEach(function (day) {
          day.selected = false
        })
      },
      setDateFrom (e) {
        this.$refs.datePickerTo.dp.minDate(e.date)
      },
      processInventory: function (option) {
        this.$validator.validateAll().then((result) => {
          if (result) {

            if (this.form.dates_from === '' || this.form.dates_to === '') {
              this.errorRangeDates = true
            } else {

              if (!this.validateSelectedDays()) {
                this.errorDay = true
              } else {
                this.loading = true
                API({
                  method: 'post',
                  url: 'inventory/process/range/days',
                  data: this.form
                })
                  .then((result) => {
                    if (result.data.success === true) {
                      this.loading = false
                      if (option === 1) {
                        if (localStorage.getItem('allotment') == 0) {
                          this.$router.push('/hotels/' + this.$route.params.hotel_id + '/manage_hotel/inventories/free_sale')
                        } else {

                          this.$router.push('/hotels/' + this.$route.params.hotel_id + '/manage_hotel/inventories/allotments')

                        }
                      } else {
                        this.checked_all_days = false
                        this.form.availability = ''
                        this.unSelectAllDays()
                      }

                    } else {
                      this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.inventories'),
                        text: this.$t('hotelsmanagehotelinventories.error.messages.information_error')
                      })
                    }
                  })

              }

            }
          }

        })
      },
      blockedInventory: function (locked) {
        this.form.locked = locked

        if (this.form.dates_from === '' || this.form.dates_to === '') {
          this.errorRangeDates = true
        } else {
          if (!this.validateSelectedDays()) {
            this.errorDay = true
          } else {
            this.loading = true
            API({
              method: 'post',
              url: 'inventory/blocked/range/days',
              data: this.form
            })
              .then((result) => {
                if (result.data.success === true) {
                  this.loading = false

                  if (localStorage.getItem('allotment') == 0) {

                    this.$router.push('/hotels/' + this.$route.params.hotel_id + '/manage_hotel/inventories/free_sale')
                  } else {
                    this.$router.push('/hotels/' + this.$route.params.hotel_id + '/manage_hotel/inventories/allotments')

                  }

                } else {
                  this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('global.modules.inventories'),
                    text: this.$t('hotelsmanagehotelinventories.error.messages.information_error')
                  })
                }
              })

          }
        }
      },

    }
  }
</script>

<style>
    .style_inventory_li:hover {
        background-color: #005ba5;
        color: white;
        cursor: pointer;

    }

    .style_inventory_li {
        padding: 8px;
    }

    .style_inventory_li_selected {
        background-color: #005ba5 !important;
        color: white;
    }

    .style_inventory_ul {
        border: 1px solid #c8ced3;
        padding: 0;
        list-style: none;
        list-style-type: none;

    }
</style>

