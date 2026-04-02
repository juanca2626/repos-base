<template>
    <div class="container">
        <div class="row">
            <h2>{{$t('hotelsmanagehotelratesratescost.rate')}}: {{form.name}}</h2>
        </div>
        <div class="row cost-table-container">
            <div class="container">
                <div class="row">
                    <div class="b-form-group form-group col-12">
                        <div class="form-row">
                            <label class="col-sm-2 col-form-label" for="type">Rate Plan</label>
                            <div class="col-sm-10">
                                <v-select
                                        id="rate_plan_from"
                                        ref="rate_plan_from"
                                        :options="rates"
                                        :reduce="rate_plan => rate_plan.id"
                                        autocomplete="true"
                                        label="name"
                                        code="id"
                                        name="rate_plan_from"
                                        v-model="form.rate_plan_from"
                                        data-vv-name="rate_plan_from"
                                        v-validate="'required'">
                                    <template slot="rate_plan" slot-scope="rate_plan">{{ rate_plan.name }}</template>
                                </v-select>
                                <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;"
                                     v-show="errors.has('rate_plan_from')">
                                    <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                       style="margin-left: 5px;"/>
                                    <span>{{ $t('hotelsmanagehotelratesratescost.error.required') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="b-form-group form-group col-6">
                        <div class="form-row">
                            <label class="col-sm-2 col-form-label" for="name">
                                {{ $t('hotelsmanagehotelratesratescost.name')}}</label>
                            <div class="col-sm-10">
                                <input id="name"
                                       ref="name"
                                       :class="{'form-control':true, 'is-valid':errors.has('name'), 'is-invalid':errors.has('name') }"
                                       type="text"
                                       name="name"
                                       v-model="form.name"
                                       data-vv-name="name"
                                       v-validate="'required'">
                                <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;"
                                     v-show="errors.has('name')">
                                    <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                       style="margin-left: 5px;"/>
                                    <span>{{ $t('hotelsmanagehotelratesratescost.error.required') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="b-form-group form-group col-6">
                        <div class="form-row">
                            <label class="col-sm-2 col-form-label" for="type">Tipo de Tarifa</label>
                            <div class="col-sm-10">
                                <v-select
                                        id="type"
                                        ref="rates_plans_type_id"
                                        :options="rateTypes"
                                        :reduce="rateType => rateType.value"
                                        autocomplete="true"
                                        label="text"
                                        code="value"
                                        name="rates_plans_type_id"
                                        data-vv-name="rates_plans_type_id"
                                        v-model="form.rates_plans_type_id" v-validate="'required'">
                                    <template slot="rateType" slot-scope="rateType">{{ rateType.text }}</template>
                                </v-select>
                                <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;"
                                     v-show="errors.has('rates_plans_type_id')">
                                    <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                       style="margin-left: 5px;"/>
                                    <span>{{ $t('hotelsmanagehotelratesratescost.error.required') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="b-form-group form-group col-6">
                        <div class="form-row">
                            <label class="col-sm-2 col-form-label" for="code">
                                {{ $t('hotelsmanagehotelratesratescost.code')}}
                            </label>
                            <div class="col-sm-10">
                                <input id="code"
                                       ref="code"
                                       class="form-control"
                                       type="text"
                                       name="code"
                                       v-model="form.code">
                            </div>
                        </div>
                    </div>
                    <div class="b-form-group form-group col-6">
                        <div class="form-row" style="margin-bottom: 5px;">
                            <label class="col-sm-2 col-form-label" for="meals">{{
                                $t('hotelsmanagehotelratesratescost.meal_name') }}</label>
                            <div class="col-sm-10">
                                <v-select id="meals"
                                          ref="meal_id"
                                          :options="meals"
                                          :reduce="meal => meal.id"
                                          autocomplete="true"
                                          label="text"
                                          code="id"
                                          name="meal_id"
                                          v-model="form.meal_id"
                                          data-vv-name="meal_id"
                                          v-validate="'required'">
                                    <template slot="meal" slot-scope="meal">{{ meal.text }}</template>
                                </v-select>
                                <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;"
                                     v-show="errors.has('meal_id')">
                                    <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                       style="margin-left: 5px;"/>
                                    <span>{{ $t('hotelsmanagehotelratesratescost.error.required') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--<div class="row">-->
                    <!--<div class="b-form-group form-group col-12">-->
                        <!--<div class="form-row" style="margin-bottom: 5px;">-->
                            <!--<label class="col-sm-2 col-form-label" for="policy">{{-->
                                <!--$t('hotelsmanagehotelratesratescost.policy_name') }}</label>-->
                            <!--<div class="col-sm-10">-->
                                <!--<v-select id="policy"-->
                                          <!--ref="policies"-->
                                          <!--:options="policies"-->
                                          <!--@input="setPolicy"-->
                                          <!--autocomplete="true"-->
                                          <!--label="value"-->
                                          <!--name="policy_id"-->
                                          <!--v-model="policy"-->
                                          <!--data-vv-name="policy_id"-->
                                          <!--v-validate="'required'">-->
                                <!--</v-select>-->
                                <!--<div class="bg-danger" style="margin-top: 3px;border-radius: 2px;"-->
                                     <!--v-show="errors.has('policy_id')">-->
                                    <!--<font-awesome-icon :icon="['fas', 'exclamation-circle']"-->
                                                       <!--style="margin-left: 5px;"/>-->
                                    <!--<span>{{ $t('hotelsmanagehotelratesratescost.error.required') }}</span>-->
                                <!--</div>-->
                            <!--</div>-->
                        <!--</div>-->
                    <!--</div>-->
                <!--</div>-->
                <!--<div class="row">-->
                    <!--<div class="col-12">-->
                        <!--<table class="table table-days">-->
                            <!--<thead>-->
                            <!--<tr>-->
                                <!--<th>{{$t('global.days.all')}}</th>-->
                                <!--<th>{{$t('global.days.monday')}}</th>-->
                                <!--<th>{{$t('global.days.tuesday')}}</th>-->
                                <!--<th>{{$t('global.days.wednesday')}}</th>-->
                                <!--<th>{{$t('global.days.thursday')}}</th>-->
                                <!--<th>{{$t('global.days.friday')}}</th>-->
                                <!--<th>{{$t('global.days.saturday')}}</th>-->
                                <!--<th>{{$t('global.days.sunday')}}</th>-->
                            <!--</tr>-->
                            <!--</thead>-->
                            <!--<tbody>-->
                            <!--<tr>-->
                                <!--<td>-->
                                    <!--<font-awesome-icon-->
                                            <!--:icon="['fas', 'check-circle']"-->
                                            <!--class="success fa-2x"-->
                                            <!--v-if="policyDays.all"/>-->
                                    <!--<font-awesome-icon-->
                                            <!--:icon="['fas', 'times-circle']"-->
                                            <!--class="danger fa-2x"-->
                                            <!--v-if="!policyDays.all"/>-->
                                <!--</td>-->
                                <!--<td>-->
                                    <!--<font-awesome-icon-->
                                            <!--:icon="['fas', 'check-circle']"-->
                                            <!--class="success fa-2x"-->
                                            <!--v-if="policyDays.all || policyDays[1]"/>-->
                                    <!--<font-awesome-icon-->
                                            <!--:icon="['fas', 'times-circle']"-->
                                            <!--class="danger fa-2x"-->
                                            <!--v-if="!policyDays[1] && !policyDays.all"/>-->
                                <!--</td>-->
                                <!--<td>-->
                                    <!--<font-awesome-icon-->
                                            <!--:icon="['fas', 'check-circle']"-->
                                            <!--class="success fa-2x"-->
                                            <!--v-if="policyDays.all || policyDays[2]"/>-->
                                    <!--<font-awesome-icon-->
                                            <!--:icon="['fas', 'times-circle']"-->
                                            <!--class="danger fa-2x"-->
                                            <!--v-if="!policyDays[2] && !policyDays.all"/>-->
                                <!--</td>-->
                                <!--<td>-->
                                    <!--<font-awesome-icon-->
                                            <!--:icon="['fas', 'check-circle']"-->
                                            <!--class="success fa-2x"-->
                                            <!--v-if="policyDays.all || policyDays[3]"/>-->
                                    <!--<font-awesome-icon-->
                                            <!--:icon="['fas', 'times-circle']"-->
                                            <!--class="danger fa-2x"-->
                                            <!--v-if="!policyDays[3] && !policyDays.all"/>-->
                                <!--</td>-->
                                <!--<td>-->
                                    <!--<font-awesome-icon-->
                                            <!--:icon="['fas', 'check-circle']"-->
                                            <!--class="success fa-2x"-->
                                            <!--v-if="policyDays.all || policyDays[4]"/>-->
                                    <!--<font-awesome-icon-->
                                            <!--:icon="['fas', 'times-circle']"-->
                                            <!--class="danger fa-2x"-->
                                            <!--v-if="!policyDays[4] && !policyDays.all"/>-->
                                <!--</td>-->
                                <!--<td>-->
                                    <!--<font-awesome-icon-->
                                            <!--:icon="['fas', 'check-circle']"-->
                                            <!--class="success fa-2x"-->
                                            <!--v-if="policyDays.all || policyDays[5]"/>-->
                                    <!--<font-awesome-icon-->
                                            <!--:icon="['fas', 'times-circle']"-->
                                            <!--class="danger fa-2x"-->
                                            <!--v-if="!policyDays[5] && !policyDays.all"/>-->
                                <!--</td>-->
                                <!--<td>-->
                                    <!--<font-awesome-icon-->
                                            <!--:icon="['fas', 'check-circle']"-->
                                            <!--class="success fa-2x"-->
                                            <!--v-if="policyDays.all || policyDays[6]"/>-->
                                    <!--<font-awesome-icon-->
                                            <!--:icon="['fas', 'times-circle']"-->
                                            <!--class="danger fa-2x"-->
                                            <!--v-if="!policyDays[6] && !policyDays.all"/>-->
                                <!--</td>-->
                                <!--<td>-->
                                    <!--<font-awesome-icon-->
                                            <!--:icon="['fas', 'check-circle']"-->
                                            <!--class="success fa-2x"-->
                                            <!--v-if="policyDays.all || policyDays[7]"/>-->
                                    <!--<font-awesome-icon-->
                                            <!--:icon="['fas', 'times-circle']"-->
                                            <!--class="danger fa-2x"-->
                                            <!--v-if="!policyDays[7] && !policyDays.all"/>-->
                                <!--</td>-->
                            <!--</tr>-->
                            <!--</tbody>-->
                        <!--</table>-->
                    <!--</div>-->
                <!--</div>-->
                <h3>Seleccion de habitaciones</h3>
                <div class="row">
                    <div class="col-12">
                        <table class="table table-striped table-bordered table-hover">
                            <thead>
                            <tr>
                                <th>Habitaciones Procedencia</th>
                                <th>Habitaciones Destino</th>
                                <th>Politica</th>
                                <th>
                                    <button @click="addRoom()" class="btn btn-success" type="button" v-if="!loading">
                                        <font-awesome-icon :icon="['fas', 'plus']"/>
                                    </button></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(room,index) in rooms_selected">
                                <td>
                                    <v-select autocomplete="true" code="id" label="name"
                                              :options="rooms_from"
                                              v-model="rooms_selected[index].room_from">
                                    </v-select>
                                </td>
                                <td>
                                    <v-select autocomplete="true" code="id" label="name"
                                              :options="rooms_to"
                                              v-model="rooms_selected[index].room_to"></v-select>
                                </td>
                                <td>
                                    <v-select id="policy"
                                              ref="policies"
                                              :options="policies"
                                              autocomplete="true"
                                              label="value"
                                              name="policy_id"
                                              v-model="rooms_selected[index].policy">
                                    </v-select>
                                </td>
                                <td>
                                    <button @click="deteteRoom(index)" class="btn btn-danger" type="button"
                                            v-if="!loading">
                                        <font-awesome-icon :icon="['fas', 'trash']"/>
                                        <!--{{$t('global.buttons.submit')}}-->
                                    </button>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="row text-right mt-3">
                <div class="col-12">
                    <img src="/images/loading.svg" v-if="loading" width="40px"/>
                    <button @click="submit()" class="btn btn-success" type="button" v-if="!loading">
                        <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                        {{$t('global.buttons.submit')}}
                    </button>
                    <router-link to="../" v-if="!loading">
                        <button class="btn btn-danger" type="reset">
                            {{$t('global.buttons.cancel')}}
                        </button>
                    </router-link>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
  import { API } from './../../../../../../api'
  import VueBootstrapTypeahead from 'vue-bootstrap-typeahead'
  import BFormGroup from 'bootstrap-vue/es/components/form-group/form-group'
  import datePicker from 'vue-bootstrap-datetimepicker'
  import 'pc-bootstrap4-datetimepicker/build/css/bootstrap-datetimepicker.css'
  import BFormSelect from 'bootstrap-vue/src/components/form-select/form-select'
  import Progress from 'bootstrap-vue/src/components/progress/progress'
  import CSwitch from '@coreui/vue/src/components/Switch/Switch'
  import TableClient from './../../../../../../components/TableClient'
  import moment from 'moment'
  import vSelect from 'vue-select'
  import 'vue-select/dist/vue-select.css'
  import draggable from 'vuedraggable'

  export default {
    components: {
      'table-client': TableClient,
      VueBootstrapTypeahead,
      'b-form-group': BFormGroup,
      datePicker,
      BFormSelect,
      'b-progress': Progress,
      CSwitch,
      vSelect,
      draggable,
    },
    data: () => {
      return {
        hotelID: '',
        ratePlanID: '',
        loading: false,
        languages: [],
        showError: false,
        currentLang: 1,
        invalidError: false,
        countError: 0,
        policies: [],
        policy: null,
        policyDays: {
          all: false,
          1: false,
          2: false,
          3: false,
          4: false,
          5: false,
          6: false,
          7: false
        },
        rates: [],
        meals: [],
        meal: null,
        rooms: [],
        rooms_selected: [],
        rooms_from: [],
        rooms_to: [],
        query_rooms_from: '',
        query_rooms_to: '',
        rateTypes: [],
        formAction: 'post',
        table: {
          columns: ['room', 'period', 'policy', 'adult', 'child', 'infant', 'extra'],
          options: {
            headings: {
              room: 'Habitacion',
              period: 'Periodo',
              policy: 'Política',
              adult: 'Adulto US$',
              kid: 'Niño US$',
              infant: 'Infante US$',
              extra: 'Extra US$'
            },
            sortable: [],
            filterable: false
          }
        },
        form: {
          id: '',
          rate_plan_from: '',
          name: '',
          code: '',
          meal_id: '',
          rates_plans_type_id: '',
          translations: {
            '1': {
              'id': '',
              'commercial_name': ''
            }
          },
          allotment: false,
          taxes: false,
          services: false,
          timeshares: false,
          promotions: false,
          promotionsData: [
            {
              from: moment().format('DD/MM/YYYY'),
              to: moment().format('DD/MM/YYYY')
            }]
        }
      }
    },
    mounted: function () {
      this.hotelID = parseInt(this.$route.params.hotel_id)

      API.get('/languages/')
        .then((result) => {
          this.languages = result.data.data
          this.currentLang = result.data.data[0].id
        }).catch(() => {
        this.$notify({
          group: 'main',
          type: 'error',
          title: this.$t('hotelsmanagehotelratesratescost.error.messages.name'),
          text: this.$t('hotelsmanagehotelratesratescost.error.messages.connection_error')
        })
      })

      API.get('/meals/selectBox?lang=' + localStorage.getItem('lang'))
        .then((result) => {
          this.meals = result.data.data
        }).catch(() => {
        this.$notify({
          group: 'main',
          type: 'error',
          title: this.$t('hotelsmanagehotelratesratescost.error.messages.name'),
          text: this.$t('hotelsmanagehotelratesratescost.error.messages.connection_error')
        })
      })

      API.get('/ratesplanstypes/selectBox?lang=' + localStorage.getItem('lang'))
        .then((result) => {
          this.rateTypes = result.data.data
        }).catch(() => {
        this.$notify({
          group: 'main',
          type: 'error',
          title: this.$t('hotelsmanagehotelratesratescost.error.messages.name'),
          text: this.$t('hotelsmanagehotelratesratescost.error.messages.connection_error')
        })
      })

      API.get('/rooms/selectBox?hotel_id=' + this.hotelID + '&lang=' + localStorage.getItem('lang'))
        .then((result) => {

          let rooms_from = []
          let rooms_to = []
          let rooms = []

          result.data.data.forEach((room) => {
            if (room.state === 1) {

              rooms_from.push({
                id: parseInt(room.id),
                name: room.translations[0].value,
                selected: false,
              })

              rooms_to.push({
                id: parseInt(room.id),
                name: room.translations[0].value,
                selected: false,
              })

              rooms.push({
                id: parseInt(room.id),
                name: room.translations[0].value,
                selected: false,
              })
            }
          })

          this.rooms_from = rooms_from
          this.rooms_to = rooms_to
          this.rooms = rooms

        }).catch(() => {
        this.$notify({
          group: 'main',
          type: 'error',
          title: this.$i18n.t('hotelsmanagehotelratesratescost.error.messages.name'),
          text: this.$i18n.t('hotelsmanagehotelratesratescost.error.messages.connection_error')
        })
      })

      API.get('/policies_rates/selectBox?hotel_id=' + this.hotelID)
        .then((result) => {
          let policies = []
          result.data.data.forEach((item) => {
            policies.push({
              id: item.id,
              value: item.name,
              days_apply: item.days_apply
            })
          })

          this.policies = policies
        }).catch(() => {
        this.$notify({
          group: 'main',
          type: 'error',
          title: this.$t('hotelsmanagehotelratesratescost.error.messages.name'),
          text: this.$t('hotelsmanagehotelratesratescost.error.messages.connection_error')
        })
      })

      API.get('rates/cost/' + this.hotelID + '/?lang=' + localStorage.getItem('lang'))
        .then((result) => {
          if (result.data.success === true) {
            this.rates = result.data.data
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
          title: this.$t('hotelsmanagehotelratesratescost.error.messages.name'),
          text: this.$t('hotelsmanagehotelratesratescost.error.messages.connection_error')
        })
      })
    },
    methods: {
      addRoom () {
        this.rooms_selected.push({
          room_from: '',
          room_to: '',
          policy: ''
        })
      },
      deteteRoom (index) {
        this.rooms_selected.splice(index, 1)
      },
      setPolicy (event) {
        if (event) {
          this.policy = event
          let days = event.days_apply.split('|')

          Object.keys(this.policyDays).forEach((key) => {
            this.policyDays[key] = days.indexOf(key) > -1
          })

          this.form.policy_id = event.id
        } else {
          this.form.policy_id = null
          this.policy = null
          this.policyDays = {
            all: false,
            1: false,
            2: false,
            3: false,
            4: false,
            5: false,
            6: false,
            7: false
          }
        }
      },
      submit () {
        this.$validator.validateAll().then(isValid => {
          if (isValid) {
            this.loading = true

            API({
              method: this.formAction,
              url: 'rates/merge/' + this.$route.params.hotel_id,
              data: {
                id: this.form.id,
                rate_plan_from: this.form.rate_plan_from,
                name: this.form.name,
                code: this.form.code,
                rates_plans_type_id: this.form.rates_plans_type_id,
                meal_id: this.form.meal_id,
                // policy_id: this.form.policy_id,
                rooms: this.rooms_selected
              }
            }).then((result) => {
              if (result.data.success === false) {
                this.$notify({
                  group: 'main',
                  type: 'error',
                  title: this.$t('hotelsmanagehotelratesratescost.error.messages.name'),
                  text: this.$t('hotelsmanagehotelratesratescost.error.messages.connection_error')
                })
                this.loading = false
              } else {
                this.$router.push('/hotels/' + this.$route.params.hotel_id + '/manage_hotel/rates/rates/cost')
                // this.form.id = result.data.rate_plan_id;
                // this.loading = false
              }
            })
              .catch(() => {
                this.$notify({
                  group: 'main',
                  type: 'error',
                  title: this.$t('hotelsmanagehotelratesratescost.error.messages.name'),
                  text: this.$t('hotelsmanagehotelratesratescost.error.messages.connection_error')
                })
                this.loading = false
              })
          } else {
            this.$notify({
              group: 'main',
              type: 'error',
              title: this.$i18n.t('global.modules.ratescost'),
              text: this.$i18n.t('hotelsmanagehotelratesratescost.error.messages.information_complete')
            })

            this.loading = false
          }
        })
      }
    }
  }
</script>

<style lang="stylus">
    .with-border
        border 1px solid #e4e7ea

    .table-days
        margin-bottom 0

        th
            text-align center
            background-color #e4e7ea

        td
            text-align center
            padding 5px 0

            .success
                color #28a745

            .danger
                color #dc3545

    .rooms-table-headers
        text-align center
        background-color #e4e7ea

    input[type="number"]::-webkit-outer-spin-button,
    input[type="number"]::-webkit-inner-spin-button
        -webkit-appearance none
        margin 0

    input[type="number"]
        -moz-appearance textfield

    .small-title
        background #2F353A
        text-align center
        color #FFFFFF
        font-weight 700
        font-size 14px
        padding 0.75rem

    .table-options
        .col-2
            padding 0.75rem
            text-align center

    .rooms-table
        input[type=number]
            padding-right 0 !important
            background none !important

</style>

