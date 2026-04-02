<template>
    <div class="container">
        <div class="row cost-table-container">
            <div class="col-2 offset-10 text-right">
                <router-link :to="'/hotels/'+$route.params.hotel_id+'/manage_hotel/rates/cost/add'"
                             class="nav-link"
                             v-if="$can('create', 'rates')">
                    <button class="btn btn-primary" type="button">+ {{$t('global.button.add')}}</button>
                </router-link>
            </div>
            <div class="col-12 text-left">
                <div class="row">
                    <div class="col-2">
                        <b-form-select :options="months" @change="fetchData" v-model="currentMonth"></b-form-select>
                    </div>
                    <div class="col-2">
                        <b-form-select :options="years" @change="fetchData" v-model="currentYear"></b-form-select>
                    </div>
                    <div class="col-2">
                        <b-form-select :options="rooms" @change="fetchData" v-model="currentRoom"></b-form-select>
                    </div>
                    <div class="col-2">
                        <b-form-select :options="rates" @change="fetchData" v-model="currentRate"></b-form-select>
                    </div>
                </div>
                <br/>
            </div>
            <div class="col-12">
                <div class="row">
                    <div class="col-12 calendar m-auto">
                        <div class="header row text-center">
                            <div class="col-head">{{$t('global.day.monday')}}</div>
                            <div class="col-head">{{$t('global.days.tuesday')}}</div>
                            <div class="col-head">{{$t('global.days.wednesday')}}</div>
                            <div class="col-head">{{$t('global.days.thursday')}}</div>
                            <div class="col-head">{{$t('global.days.friday')}}</div>
                            <div class="col-head">{{$t('global.days.saturday')}}</div>
                            <div class="col-head">{{$t('global.days.sunday')}}</div>
                        </div>
                        <div class="body row text-center">
                            <div class="col-body" v-for="day in days">
                                <div class="date-block">{{day.text}}</div>
                                <div @click="showDetail(item)" class="rates-block" v-for="item in day.data"
                                     v-if="day.data">
                                    <div class="block">
                                        <span class="block-value"><strong>{{item.rates_plans_rooms.rate_plan.name}}: </strong></span>
                                        <span class="block-value">{{item.rates_plans_rooms.room.translations[0].value}}: US$ {{parseFloat(item.rate.price_adult)}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-4">
                    <div class="col-4 offset-8 text-right">
                        <button @click="deleteAll" class="btn btn-secondary" type="button" v-if="!loading">
                            {{$t('global.button.deleteVisible')}}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <b-modal :title="currentItem.rates_plans_rooms.rate_plan.name" id="modal-block-detail" ref="modal-detail"
                 size="lg">
            <div style="margin-top: -17px">
                <div class="rooms-table row rooms-table-headers">
                    <div class="col-4 my-auto">
                        {{$t('servicesmanageservicerates.room')}}
                    </div>
                    <div class="col-2 my-auto">
                        {{$t('servicesmanageservicerates.policy_name')}}
                    </div>
                    <div class="col-2 my-auto">
                        {{$t('servicesmanageservicerates.meal_name')}}
                    </div>
                    <div class="col-1 px-2">
                        {{$t('servicesmanageservicerates.adult')}} US$
                    </div>
                    <div class="col-1 px-2">
                        {{$t('servicesmanageservicerates.child')}} US$
                    </div>
                    <div class="col-1 px-2">
                        {{$t('servicesmanageservicerates.infant')}} US$
                    </div>
                    <div class="col-1 px-2">
                        {{$t('servicesmanageservicerates.extra')}} US$
                    </div>
                </div>
                <div class="rooms-table row">
                    <div class="col-4 my-auto">
                        {{currentItem.rates_plans_rooms.room.translations[0].value}}
                    </div>
                    <div class="col-2 my-auto">
                        {{currentItem.policies_rates.name}}
                    </div>
                    <div class="col-2 my-auto">
                        {{currentItem.rates_plans_rooms.rate_plan.meal.translations[0].value}}
                    </div>
                    <div class="col-1 px-2">
                        <input :id="'room-adult'"
                               :name="'room-adult'"
                               class="form-control"
                               step="0.01"
                               type="number"
                               v-model="currentItem.rate.price_adult"
                               v-validate="{ required: true }"/>
                    </div>
                    <div class="col-1 px-2">
                        <input :id="'room-child'"
                               :name="'room-child'"
                               class="form-control"
                               step="0.01"
                               type="number"
                               v-model="currentItem.rate.price_child"
                               v-validate="{ required: true }"/>
                    </div>
                    <div class="col-1 px-2">
                        <input :id="'room-infant'"
                               :name="'room-infant'"
                               class="form-control"
                               step="0.01"
                               type="number"
                               v-model="currentItem.rate.price_infant"
                               v-validate="{ required: true }"/>
                    </div>
                    <div class="col-1 px-2">
                        <input :id="'room-extra'"
                               :name="'room-extra'"
                               class="form-control"
                               step="0.01"
                               type="number"
                               v-model="currentItem.rate.price_extra"
                               v-validate="{ required: true }"/>
                    </div>
                </div>
            </div>
            <div class="w-100" slot="modal-footer">
                <div class="row">
                    <div class="col-12">
                        <img src="/images/loading.svg" v-if="loading" width="40px"/>
                    </div>
                    <div class="col-6 text-left">
                        <button @click="deleteDetail" class="btn btn-secondary" type="button" v-if="!loading">
                            {{$t('global.buttons.delete')}}
                        </button>
                    </div>
                    <div class="col-6 text-right">
                        <button @click="submit" class="btn btn-success" type="button" v-if="!loading">
                            <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                            {{$t('global.buttons.submit')}}
                        </button>
                        <button @click="closeDetail" class="btn btn-danger" type="button" v-if="!loading">
                            {{$t('global.buttons.cancel')}}
                        </button>
                    </div>
                </div>
            </div>
        </b-modal>
        <b-modal id="modal-block-delete-rates" ref="modal-delete-rates" title="Eliminar Tarifas Visibles">
            <div class="row">
                <div class="col-auto">
                    {{$t('servicesmanageservicerates.delete.confirmation')}}
                </div>
                <div class="col-auto">
                    <strong>{{$t('servicesmanageservicerates.month')}}: </strong>{{currentMonthName}}
                </div>
                <div class="col-auto">
                    <strong>{{$t('servicesmanageservicerates.year')}}: </strong>{{currentYear}}
                </div>
                <div class="col-auto" v-if="currentRoom !== ''">
                    <strong>{{$t('servicesmanageservicerates.room')}}: </strong>{{currentRoomName}}
                </div>
                <div class="col-auto" v-if="currentRate !== ''">
                    <strong>{{$t('servicesmanageservicerates.rate')}}: </strong>{{currentRateName}}
                </div>
            </div>
            <div class="w-100" slot="modal-footer">
                <div class="row">
                    <div class="col-12">
                        <img src="/images/loading.svg" v-if="loading" width="40px"/>
                    </div>
                    <div class="col-12 text-right">
                        <button @click="continueDeleteAll" class="btn btn-success" type="button" v-if="!loading">
                            <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                            {{$t('global.buttons.delete')}}
                        </button>
                        <button @click="closeDeleteAll" class="btn btn-danger" type="button" v-if="!loading">
                            {{$t('global.buttons.cancel')}}
                        </button>
                    </div>
                </div>
            </div>
        </b-modal>
    </div>
</template>

<script>
  import { API } from './../../../../../api'
  import TableClient from './../../../../../components/TableClient'
  import MenuEdit from './../../../../../components/MenuEdit'
  import moment from 'moment/moment'
  import BFormSelect from 'bootstrap-vue/src/components/form-select/form-select'
  import BModal from 'bootstrap-vue/src/components/modal/modal'

  const times = x => f => {
    if (x > 0) {
      f()
      times(x - 1)(f)
    }
  }

  export default {
    components: {
      'table-client': TableClient,
      'menu-edit': MenuEdit,
      BFormSelect,
      BModal
    },
    data: () => {
      return {
        loading: false,
        currentMonth: 0,
        currentYear: 0,
        currentRoom: '',
        currentRate: '',
        days: [],
        rooms: [],
        rates: [],
        currentItem: {
          rates_plans_rooms: {
            room: {
              translations: [
                {
                  value: ''
                }
              ]
            },
            rate_plan: {
              name: '',
              meal: {
                translations: [
                  {
                    value: ''
                  }
                ]
              }
            }
          },
          policies_rates: {
            name: ''
          },
          rate: {
            price_adult: 0,
            price_child: 0,
            price_infant: 0,
            price_extra: 0
          }
        }
      }
    },
    mounted () {
      let currentDate = new Date()
      this.currentMonth = ('00' + (currentDate.getMonth() + 1)).slice(-2)
      this.currentYear = currentDate.getFullYear()

      this.fetchData(this.$i18n.locale)

      API.get('/rooms/selectBox?hotel_id=' + this.$route.params.hotel_id + '&lang=' + localStorage.getItem('lang'))
        .then((result) => {
          let rooms = [
            {
              value: '',
              text: ''
            }
          ]
          result.data.data.forEach((room) => {
            rooms.push({
              value: room.id,
              text: room.translations[0].value
            })
          })

          this.rooms = rooms
        })

      API.get('/rates/cost/selectBox?hotel_id=' + this.$route.params.hotel_id + '&lang=' + localStorage.getItem('lang'))
        .then((result) => {
          let data = result.data.data

          data.unshift({ value: '', text: '' })

          this.rates = data
        })
    },
    computed: {
      months () {
        return [
          { value: '01', text: this.$i18n.t('global.months.january') },
          { value: '02', text: this.$i18n.t('global.months.february') },
          { value: '03', text: this.$i18n.t('global.months.march') },
          { value: '04', text: this.$i18n.t('global.months.april') },
          { value: '05', text: this.$i18n.t('global.months.may') },
          { value: '06', text: this.$i18n.t('global.months.june') },
          { value: '07', text: this.$i18n.t('global.months.july') },
          { value: '08', text: this.$i18n.t('global.months.august') },
          { value: '09', text: this.$i18n.t('global.months.september') },
          { value: '10', text: this.$i18n.t('global.months.october') },
          { value: '11', text: this.$i18n.t('global.months.november') },
          { value: '12', text: this.$i18n.t('global.months.december') }
        ]
      },
      years () {
        let previousYear = moment().subtract(5, 'years').year()
        let currentYear = moment().add(5, 'years').year()

        let years = []

        do {
          years.push({ value: previousYear, text: previousYear })
          previousYear++
        } while (currentYear > previousYear)

        return years
      },
      currentMonthName () {
        let months = JSON.parse(JSON.stringify(this.months))
        let month = this.currentMonth !== 0 ?
          months.find(month => month.value === this.currentMonth) : { text: '' }

        return month.text
      },
      currentRoomName () {
        let rooms = JSON.parse(JSON.stringify(this.rooms))
        let room = this.currentRoom !== '' ?
          rooms.find(room => room.value === this.currentRoom) : { text: '' }

        return room.text
      },
      currentRateName () {
        let rates = JSON.parse(JSON.stringify(this.rates))
        let rate = this.currentRate !== '' ?
          rates.find(rate => rate.value === this.currentRate) : { text: '' }

        return rate.text
      }
    },
    created () {
      this.$parent.$parent.$on('langChange', (payload) => {
        this.fetchData(payload.lang)
      })
    },
    methods: {
      fetchData: function () {
        API.get('rates/cost/'
          + this.$route.params.hotel_id + '/calendar?date=' + this.currentMonth + '|' + this.currentYear + '|' +
          this.currentRoom + '|' + this.currentRate
          + '&lang=' + localStorage.getItem('lang'))
          .then((result) => {
            let days = []
            let currentDays = moment('01-' + this.currentMonth + '-' + this.currentYear, 'DD-MM-YYYY')
            let resultData = result.data

            let day = currentDays.day() === 0 ? 6 : currentDays.day() - 1

            times(day)(() => {
              days.push({ value: 0, text: '' })
            })

            let howManyDays = currentDays.daysInMonth()
            let count = 1

            times(howManyDays)(() => {
              if (resultData.data.hasOwnProperty(
                this.currentYear + '-' + this.currentMonth + '-' + ('00' + count).slice(-2))) {
                days.push({
                  value: count,
                  text: count,
                  data: resultData.data[this.currentYear + '-' + this.currentMonth + '-' + ('00' + count).slice(-2)]
                })
              } else {
                days.push({ value: count, text: count })
              }

              count++
            })

            this.days = days
          })
      },
      showDetail (item) {
        this.currentItem = item
        this.$refs['modal-detail'].show()
      },
      deleteAll () {
        this.loading = false

        this.$refs['modal-delete-rates'].show()
      },
      deleteDetail () {
        API.delete('rates/cost/' + this.$route.params.hotel_id + '/calendar/' + this.currentItem.id)
          .then(() => {
            this.fetchData()
            this.closeDetail()

            this.loading = false
          })
      },
      closeDeleteAll () {
        this.loading = false

        this.$refs['modal-delete-rates'].hide()
      },
      continueDeleteAll () {
        this.loading = true
        API.delete('rates/cost/'
          + this.$route.params.hotel_id + '/calendar?date=' + this.currentMonth + '|' + this.currentYear + '|' +
          this.currentRoom + '|' + this.currentRate
          + '&lang=' + localStorage.getItem('lang'))
          .then((result) => {
            this.fetchData()
            this.closeDeleteAll()
            this.loading = false
          })
      },
      closeDetail () {
        this.currentItem = {
          rates_plans_rooms: {
            room: {
              translations: [
                {
                  value: ''
                }
              ]
            },
            rate_plan: {
              name: '',
              meal: {
                translations: [
                  {
                    value: ''
                  }
                ]
              }
            }
          },
          policies_rates: {
            name: ''
          },
          rate: {
            price_adult: 0,
            price_child: 0,
            price_infant: 0,
            price_extra: 0
          }
        }

        this.$refs['modal-detail'].hide()
      },
      submit () {
        this.loading = true
        API({
          method: 'put',
          url: 'rates/cost/' + this.$route.params.hotel_id + '/calendar',
          data: this.currentItem
        }).then((result) => {
          this.loading = false
          if (result.data.success === true) {
            this.$refs['modal-detail'].hide()
            this.fetchData()
          } else {
            this.$notify({
              group: 'main',
              type: 'error',
              title: 'Fetch Error',
              text: result.data.message
            })
          }
        }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('servicesmanageservicerates.error.messages.name'),
            text: this.$t('servicesmanageservicerates.error.messages.connection_error')
          })
        })
      }
    }
  }
</script>

<style lang="stylus">
    .cost-table-container
        position relative

    .calendar
        max-width 910px !important
        min-width 910px !important
        width 910px !important

        .col-head
            background #2f353a
            color #fff
            border-width 0 !important
            font-size 14px
            font-weight 700
            padding 0.75rem
            width 130px !important
            min-width 130px !important
            max-width 130px !important

        .col-body
            border 1px solid #C8CED3
            font-size 14px
            padding 0
            width 130px !important
            min-width 130px !important
            max-width 130px !important
            min-height 45px

            .date-block
                background #F9FBFC
                font-weight 700
                text-align right
                padding-right 10px
                font-size 10px

            .rates-block
                text-align left
                border 1px solid #F9FBFC
                padding 0.75rem

                .block
                    font-size 12px

                    .block-hotel
                        font-weight bold

                &:hover
                    background-color #C8CED3
                    cursor pointer

    .rooms-table-headers
        text-align center
        background-color #e4e7ea

    input[type="number"]::-webkit-outer-spin-button,
    input[type="number"]::-webkit-inner-spin-button
        -webkit-appearance none
        margin 0


    input[type="number"]
        -moz-appearance textfield

    .rooms-table
        input[type=number]
            padding-right 0 !important
            background none !important

</style>

