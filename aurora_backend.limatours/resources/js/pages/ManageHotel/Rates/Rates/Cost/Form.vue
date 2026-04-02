<template>
    <div class="container">
        <div class="row cost-table-container">
            <form @submit.prevent="validateBeforeSubmit" class="container">
                <div class="row">
                    <div class="b-form-group form-group col-6">
                        <div class="form-row">
                            <label class="col-sm-2 col-form-label" for="name">{{ $t('name') }}</label>
                            <div class="col-sm-10">
                                <input :class="{'form-control':true, 'is-valid':errors.has('name'), 'is-invalid':errors.has('name') }"
                                       data-vv-validate-on="none"
                                       id="name"
                                       name="name"
                                       type="text"
                                       v-model="form.name"
                                       v-validate="'required'">
                                <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                    <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                       style="margin-left: 5px;" v-show="errors.has('name')"/>
                                    <span v-show="errors.has('name')">{{ errors.first('name') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="b-form-group form-group col-6">
                        <div class="form-row">
                            <label class="col-sm-4 col-form-label" for="commercial_name">
                                {{ $t('commercial_name')}}
                            </label>
                            <div class="col-sm-6">
                                <input :class="{'form-control':true, 'is-valid':errors.has('commercial_name'), 'is-invalid':errors.has('commercial_name') }"
                                       data-vv-validate-on="none"
                                       id="commercial_name"
                                       name="commercial_name"
                                       type="text"
                                       v-model="form.translations[currentLang].commercial_name"
                                       v-validate="'required'">
                                <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                    <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                       style="margin-left: 5px;"
                                                       v-show="errors.has('commercial_name')"/>
                                    <span v-show="errors.has('commercial_name')">{{ errors.first('commercial_name') }}</span>
                                </div>
                            </div>
                            <select class="col-sm-2 form-control" id="lang" required size="0" v-model="currentLang">
                                <option v-bind:value="language.id" v-for="language in languages">
                                    {{ language.iso }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="b-form-group form-group col-6">
                        <div class="form-row" style="margin-bottom: 5px;">
                            <label class="col-sm-2 col-form-label" for="policy">{{ $t('policy_name') }}</label>
                            <div class="col-sm-10">
                                <vue-bootstrap-typeahead
                                        :data="policies"
                                        :maxMatches="10"
                                        :minMatchingChars="0"
                                        :serializer="item => item.value"
                                        @hit="setPolicy"
                                        id="policy"
                                        ref="policyTypeahead"
                                        v-model="policySearch"
                                />
                            </div>
                        </div>
                    </div>
                    <div class="b-form-group form-group col-6">
                        <div class="form-row" style="margin-bottom: 5px;">
                            <label class="col-sm-4 col-form-label" for="meals">{{ $t('meal_name') }}</label>
                            <div class="col-sm-8">
                                <vue-bootstrap-typeahead
                                        :data="meals"
                                        :maxMatches="10"
                                        :minMatchingChars="0"
                                        :serializer="item => item.translations[0].value"
                                        @hit="setMeal"
                                        id="meals"
                                        ref="mealTypeahead"
                                        v-model="mealSearch"
                                />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <h3>Plan Tarifario</h3>
                    </div>
                </div>
                <div class="row">
                    <div class="b-form-group form-group col-2">
                        <label class="col-sm-12 col-form-label" for="dates">{{ $t('date_range')
                            }}</label>
                    </div>
                    <div class="b-form-group form-group col-10">
                        <div class="row">
                            <div class="input-group col-3">
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
                            <div class="input-group col-3">
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
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <table class="table table-days">
                            <thead>
                            <tr>
                                <th>{{$t('days.all')}}</th>
                                <th>{{$t('days.monday')}}</th>
                                <th>{{$t('days.tuesday')}}</th>
                                <th>{{$t('days.wednesday')}}</th>
                                <th>{{$t('days.thursday')}}</th>
                                <th>{{$t('days.friday')}}</th>
                                <th>{{$t('days.saturday')}}</th>
                                <th>{{$t('days.sunday')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>
                                    <font-awesome-icon
                                            :icon="['fas', 'check-circle']"
                                            class="success fa-2x"
                                            v-if="policyDays.all"/>
                                    <font-awesome-icon
                                            :icon="['fas', 'times-circle']"
                                            class="danger fa-2x"
                                            v-if="!policyDays.all"/>
                                </td>
                                <td>
                                    <font-awesome-icon
                                            :icon="['fas', 'check-circle']"
                                            class="success fa-2x"
                                            v-if="policyDays.all || policyDays[1]"/>
                                    <font-awesome-icon
                                            :icon="['fas', 'times-circle']"
                                            class="danger fa-2x"
                                            v-if="!policyDays[1] && !policyDays.all"/>
                                </td>
                                <td>
                                    <font-awesome-icon
                                            :icon="['fas', 'check-circle']"
                                            class="success fa-2x"
                                            v-if="policyDays.all || policyDays[2]"/>
                                    <font-awesome-icon
                                            :icon="['fas', 'times-circle']"
                                            class="danger fa-2x"
                                            v-if="!policyDays[2] && !policyDays.all"/>
                                </td>
                                <td>
                                    <font-awesome-icon
                                            :icon="['fas', 'check-circle']"
                                            class="success fa-2x"
                                            v-if="policyDays.all || policyDays[3]"/>
                                    <font-awesome-icon
                                            :icon="['fas', 'times-circle']"
                                            class="danger fa-2x"
                                            v-if="!policyDays[3] && !policyDays.all"/>
                                </td>
                                <td>
                                    <font-awesome-icon
                                            :icon="['fas', 'check-circle']"
                                            class="success fa-2x"
                                            v-if="policyDays.all || policyDays[4]"/>
                                    <font-awesome-icon
                                            :icon="['fas', 'times-circle']"
                                            class="danger fa-2x"
                                            v-if="!policyDays[4] && !policyDays.all"/>
                                </td>
                                <td>
                                    <font-awesome-icon
                                            :icon="['fas', 'check-circle']"
                                            class="success fa-2x"
                                            v-if="policyDays.all || policyDays[5]"/>
                                    <font-awesome-icon
                                            :icon="['fas', 'times-circle']"
                                            class="danger fa-2x"
                                            v-if="!policyDays[5] && !policyDays.all"/>
                                </td>
                                <td>
                                    <font-awesome-icon
                                            :icon="['fas', 'check-circle']"
                                            class="success fa-2x"
                                            v-if="policyDays.all || policyDays[6]"/>
                                    <font-awesome-icon
                                            :icon="['fas', 'times-circle']"
                                            class="danger fa-2x"
                                            v-if="!policyDays[6] && !policyDays.all"/>
                                </td>
                                <td>
                                    <font-awesome-icon
                                            :icon="['fas', 'check-circle']"
                                            class="success fa-2x"
                                            v-if="policyDays.all || policyDays[7]"/>
                                    <font-awesome-icon
                                            :icon="['fas', 'times-circle']"
                                            class="danger fa-2x"
                                            v-if="!policyDays[7] && !policyDays.all"/>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row mt-3" v-if="rooms">
                    <div class="col-12">
                        <div class="rooms-table row rooms-table-headers">
                            <div class="col-8 my-auto">
                                {{$t('room')}} US$
                            </div>
                            <div class="col-1">
                                {{$t('adult')}} US$
                            </div>
                            <div class="col-1">
                                {{$t('child')}} US$
                            </div>
                            <div class="col-1">
                                {{$t('infant')}} US$
                            </div>
                            <div class="col-1">
                                {{$t('extra')}} US$
                            </div>
                        </div>
                        <div :key="room.id" class="rooms-table row" v-for="room in rooms">
                            <div class="col-8 my-auto">
                                {{room.translations[0].value}}
                            </div>
                            <div class="col-1">
                                <input :id="'room-'+room.id+'-adult'"
                                       :name="'room-'+room.id+'-adult'"
                                       class="form-control"
                                       step="0.01"
                                       type="number"
                                       v-model="form.rooms[room.id].adult"/>
                            </div>
                            <div class="col-1">
                                <input :id="'room-'+room.id+'-adult'"
                                       :name="'room-'+room.id+'-child'"
                                       class="form-control"
                                       step="0.01"
                                       type="number"
                                       v-model="form.rooms[room.id].child"/>
                            </div>
                            <div class="col-1">
                                <input :id="'room-'+room.id+'-adult'"
                                       :name="'room-'+room.id+'-infant'"
                                       class="form-control"
                                       step="0.01"
                                       type="number"
                                       v-model="form.rooms[room.id].infant"/>
                            </div>
                            <div class="col-1">
                                <input :id="'room-'+room.id+'-adult'"
                                       :name="'room-'+room.id+'-extra'"
                                       class="form-control"
                                       step="0.01"
                                       type="number"
                                       v-model="form.rooms[room.id].extra"/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row text-right mt-3">
                    <div class="col-12">
                        <img src="/images/loading.svg" v-if="loading" width="40px"/>
                        <button class="btn btn-success" type="submit" v-if="!loading">
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
            </form>
        </div>
    </div>
</template>

<script>
  import { API } from './../../../../../api'
  import VueBootstrapTypeahead from 'vue-bootstrap-typeahead'
  import BFormGroup from 'bootstrap-vue/es/components/form-group/form-group'
  import datePicker from 'vue-bootstrap-datetimepicker'
  import 'pc-bootstrap4-datetimepicker/build/css/bootstrap-datetimepicker.css'

  export default {
    components: {
      VueBootstrapTypeahead,
      'b-form-group': BFormGroup,
      datePicker
    },
    data: () => {
      return {
        loading: false,
        languages: [],
        showError: false,
        currentLang: '1',
        invalidError: false,
        countError: 0,
        policies: [
          {
            'id': 1,
            'value': 'Toda la semana',
            'days': 'all'
          }, {
            'id': 2,
            'value': 'Fin de semana',
            'days': '6|7'
          }, {
            'id': 3,
            'value': 'Lunes a Viernes',
            'days': '1|2|3|4|5'
          }
        ],
        policy: null,
        policySearch: null,
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
        meals: [],
        meal: null,
        mealSearch: null,
        datePickerFromOptions: {
          format: 'DD/MM/YYYY',
          useCurrent: false,
          locale: localStorage.getItem('lang')
        },
        datePickerToOptions: {
          format: 'DD/MM/YYYY',
          useCurrent: false,
          locale: localStorage.getItem('lang')
        },
        rooms: [],
        formAction: 'post',
        form: {
          name: '',
          policy_id: '',
          meal_id: '',
          dates_from: '',
          dates_to: '',
          rooms: [],
          translations: {
            '1': {
              'id': '',
              'commercial_name': ''
            }
          }
        }
      }
    },
    mounted: function () {
      API.get('/languages/')
        .then((result) => {
          this.languages = result.data.data
          this.currentLang = result.data.data[0].id

          let form = {
            translations: {}
          }

          let languages = this.languages

          languages.forEach((value) => {
            form.translations[value.id] = {
              id: '',
              commercial_name: ''
            }
          })
          if (this.$route.params.id !== undefined) {
            API.get('/rates/' + this.$route.params.id)
              .then((result) => {
                this.rates = result.data.data
                this.formAction = 'put'

                let arrayTranslations = this.rates[0].translations

                arrayTranslations.forEach((translation) => {
                  form.translations[translation.language_id] = {
                    id: translation.id,
                    commercial_name: translation.value
                  }
                })
              }).catch(() => {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('error.messages.name'),
                text: this.$t('error.messages.connection_error')
              })
            })
          }

          this.form = { ...this.form, ...form }
        }).catch(() => {
        this.$notify({
          group: 'main',
          type: 'error',
          title: this.$t('error.messages.name'),
          text: this.$t('error.messages.connection_error')
        })
      })

      API.get('/meals/selectBox?lang=' + localStorage.getItem('lang'))
        .then((result) => {
          this.meals = result.data.data
        }).catch(() => {
        this.$notify({
          group: 'main',
          type: 'error',
          title: this.$t('error.messages.name'),
          text: this.$t('error.messages.connection_error')
        })
      })

      API.get('/rooms/selectBox?lang=' + localStorage.getItem('lang'))
        .then((result) => {

          let rooms = {}
          let roomTmp = {}
          let id
          result.data.data.forEach((room) => {
            id = parseInt(room.id)
            roomTmp = {}
            roomTmp.id = parseInt(room.id)
            roomTmp.translations = room.translations
            roomTmp.adult = 0
            roomTmp.child = 0
            roomTmp.infant = 0
            roomTmp.extra = 0

            rooms[id] = roomTmp

          })

          this.form.rooms = rooms

          this.rooms = result.data.data
        }).catch(() => {
        this.$notify({
          group: 'main',
          type: 'error',
          title: this.$t('error.messages.name'),
          text: this.$t('error.messages.connection_error')
        })
      })
    },
    methods: {
      setPolicy (event) {
        let days = event.days.split('|')

        Object.keys(this.policyDays).forEach((key) => {
          this.policyDays[key] = days.indexOf(key) > -1
        })

        this.form.policy_id = event.id
      },
      setMeal (event) {
        this.form.meal_id = event.id
      },
      setDateFrom (e) {
        this.$refs.datePickerTo.dp.minDate(e.date)
      },
      validateBeforeSubmit () {
        this.$validator.validateAll().then(isValid => {
          if (isValid) {
            this.submit()
          } else {
            this.$notify({
              group: 'main',
              type: 'error',
              title: this.$t('global.modules.ratescost'),
              text: this.$t('error.messages.information_complete')
            })

            this.loading = false
          }
        })
      },
      submit () {
        API({
          method: this.formAction,
          url: 'rates/cost/' + (this.$route.params.id !== undefined ? this.$route.params.id : ''),
          data: this.form
        })
          .then((result) => {
            if (result.data.success === false) {

              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.ratescost'),
                text: this.$t('error.messages.facility_incorrect')
              })

              this.loading = false
            } else {
              this.$router.push('/manage_hotels/rates/rates/cost')
            }
          })
          .catch((result) => {
            this.loading = false
            this.$notify({
              group: 'main',
              type: 'error',
              title: this.$t('error.messages.name'),
              text: this.$t('error.messages.connection_error')
            })
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

</style>

