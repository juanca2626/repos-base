<template>
    <div class="row">
        <div class="col-sm-12">
            <form @submit.prevent="validateBeforeSubmit">
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label">{{ $t('packagesmanagepackagerates.stela_code') }}</label>
                        <div class="col-sm-2">
                            <input :class="{'form-control':true }"
                                   id="stela_code" name="stela_code"
                                   type="text"
                                   v-model="form.stela_code" v-validate="'required'">
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;"
                                                   v-show="errors.has('stela_code')"/>
                                <span v-show="errors.has('stela_code')">{{ errors.first('stela_code') }}</span>
                            </div>
                        </div>

                        <div class="col-2">
                            <label class="col-sm-12 col-form-label">{{ $t('packagesmanagepackagerates.from') }} - {{
                                $t('packagesmanagepackagerates.to') }}</label>
                        </div>
                        <div class="col-3">
                            <div class="input-group col-12">
                                <date-picker
                                        :config="datePickerFromOptions"
                                        @dp-change="setDateFrom"
                                        id="date_from"
                                        name="date_from" ref="datePickerFrom"
                                        v-model="form.date_from" v-validate="'required'"
                                >
                                </date-picker>

                                <div class="input-group-append">
                                    <button class="btn btn-secondary datepickerbutton" title="Toggle"
                                            type="button">
                                        <i class="far fa-calendar"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;"
                                                   v-show="errors.has('date_from')"/>
                                <span v-show="errors.has('date_from')">{{ errors.first('date_from') }}</span>
                            </div>
                        </div>

                        <div class="col-3">
                            <div class="input-group col-12">
                                <date-picker
                                        :config="datePickerToOptions"
                                        id="date_to"
                                        name="date_to" ref="datePickerTo"
                                        v-model="form.date_to" v-validate="'required'">
                                </date-picker>
                                <div class="input-group-append">
                                    <button class="btn btn-secondary datepickerbutton" title="Toggle"
                                            type="button">
                                        <i class="far fa-calendar"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;"
                                                   v-show="errors.has('date_to')"/>
                                <span v-show="errors.has('date_to')">{{ errors.first('date_to') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-2 col-form-label">{{ $t('packagesmanagepackagerates.title') }}</label>

                        <div class="col-2">
                            <label class="col-form-label">SGL</label>
                            <input class="form-control" id="simple" max="10000" min="1" name="simple" step="0.01"
                                   type="number" v-model.number="form.simple"
                                   v-validate="'required'">
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;"
                                                   v-show="errors.has('simple')"/>
                                <span v-show="errors.has('simple')">{{ errors.first('simple') }}</span>
                            </div>
                        </div>
                        <div class="col-2">
                            <label class="col-form-label">DBL</label>
                            <input class="form-control" id="double" max="10000" min="1" name="double" step="0.01"
                                   type="number" v-model.number="form.double"
                                   v-validate="'required'">
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;"
                                                   v-show="errors.has('double')"/>
                                <span v-show="errors.has('double')">{{ errors.first('double') }}</span>
                            </div>
                        </div>
                        <div class="col-2">
                            <label class="col-form-label">TPL</label>
                            <input class="form-control" id="triple" max="10000" min="0" step="0.01" type="number"
                                   v-model.number="form.triple">
                        </div>
                        <div class="col-2" v-if="showChildren">
                            <label class="col-form-label">CHD</label>
                            <input class="form-control" id="boy" max="10000" min="0" step="0.01" type="number"
                                   v-model.number="form.boy">
                        </div>
                        <div class="col-2" v-if="showInfants">
                            <label class="col-form-label">INF</label>
                            <input class="form-control" id="infant" max="10000" min="0" step="0.01" type="number"
                                   v-model.number="form.infant">
                        </div>

                    </div>
                </div>
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-2 col-form-label" for="service_type">{{ $t('packagesmanagepackagerates.type')
                            }}</label>
                        <div class="col-4">
                            <select class="form-control" id="service_type" name="service_type" size="0"
                                    v-model="form.service_type" v-validate="'required'">
                                <option value=""></option>
                                <option :value="service_type.id" v-for="service_type in service_types">
                                    {{ service_type.translations[0].value }}
                                </option>
                            </select>
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('service_type')"/>
                                <span v-show="errors.has('service_type')">{{ errors.first('service_type') }}</span>
                            </div>
                        </div>

                        <label class="col-2 col-form-label" for="type_class">{{ $t('packagesmanagepackagerates.class')
                            }}</label>
                        <div class="col-4">
                            <select class="form-control" id="type_class" name="type_class" size="0"
                                    v-model="form.type_class" v-validate="'required'">
                                <option value=""></option>
                                <option :value="type_class.id" v-for="type_class in type_classes">
                                    {{ type_class.translations[0].value | capitalize }}
                                </option>
                            </select>
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('type_class')"/>
                                <span v-show="errors.has('type_class')">{{ errors.first('type_class') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

            </form>
        </div>
        <div class="col-sm-6">
            <div slot="footer">
                <img src="/images/loading.svg" v-if="loading" width="40px"/>
                <button @click="validateBeforeSubmit" class="btn btn-success" type="submit" v-if="!loading">
                    <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                    {{ $t('global.buttons.submit') }}
                </button>
                <router-link :to="getRouteratesList()" v-if="!loading">
                    <button class="btn btn-danger" type="reset">
                        {{ $t('global.buttons.cancel') }}
                    </button>
                </router-link>
            </div>
        </div>
    </div>
</template>

<script>
  import { API } from '../../../../api'
  import datePicker from 'vue-bootstrap-datetimepicker'
  import 'pc-bootstrap4-datetimepicker/build/css/bootstrap-datetimepicker.css'
  import BFormGroup from 'bootstrap-vue/es/components/form-group/form-group'

  export default {
    components: {
      datePicker,
      BFormGroup
    },
    data: () => {
      return {
        type_classes: [],
        service_types: [],
        rate: null,
        loading: false,
        showChildren:false,
        showInfants:false,
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
        date_from: '',
        date_to: '',
        currentLangName: '1',
        currentLangDescription: '1',
        formAction: 'post',
        form: {
          stela_code: '',
          date_from: '',
          date_to: '',
          simple: 0,
          double: 0,
          triple: 0,
          boy: 0,
          infant: 0,
          type_class: '',
          service_type: '',
          package_id: null
        }
      }
    },
    computed: {},
    created: function () {
      this.form.package_id = this.$route.params.package_id
    },
    mounted () {
      if (this.$route.params.rate_id !== undefined) {
        API.get('/package/' + this.form.package_id + '/rates/' + this.$route.params.rate_id)
          .then((result) => {
            this.form.stela_code = result.data.data.reference_number
            this.form.date_from = this.formatDate(result.data.data.date_from)
            this.form.date_to = this.formatDate(result.data.data.date_to)
            this.form.type_class = result.data.data.type_class_id
            this.form.service_type = result.data.data.service_type_id
            this.form.simple = result.data.data.simple
            this.form.double = result.data.data.double
            this.form.triple = result.data.data.triple
            this.form.boy = result.data.data.boy
            this.form.infant = result.data.data.infant
            this.formAction = 'put'
          })
      }
      // API.get('/services/types/selectBox?lang='+localStorage.getItem('lang'))
      //   .then((result) => {
      this.service_types = [
        { id: 1, code: 'SIC', translations: [{ value: 'SIC' }] },
        { id: 2, code: 'PC', translations: [{ value: 'PC' }] },
        { id: 3, code: 'NA', translations: [{ value: 'NA' }] },
      ]
      // })
      API.get('/typeclass/selectbox?lang=' + localStorage.getItem('lang'))
        .then((result) => {
          this.type_classes = result.data.data
        })

      API.get('/packages/' + this.form.package_id)
        .then((result) => {
          this.showInfants = parseInt( result.data.data.allow_infant )
          this.showChildren = parseInt( result.data.data.allow_child )
        })
    },
    methods: {
      formatDate: function (_date) {
        _date = _date.split('-')
        _date = _date[2] + '/' + _date[1] + '/' + _date[0]
        return _date
      },
      getRouteratesList: function () {

        return '/packages/' + this.$route.params.package_id + '/manage_package/package_rates'

      },
      validateBeforeSubmit: function () {
        this.$validator.validateAll().then((result) => {
          if (result) {
            this.form.package_id = this.$route.params.package_id
            this.submit()

          } else {
            this.$notify({
              group: 'main',
              type: 'error',
              title: this.$t('global.modules.packages'),
              text: this.$t('packagesmanagepackagerates.error.messages.information_complete')
            })

            this.loading = false
          }
        })
      },
      setDateFrom (e) {
        this.$refs.datePickerTo.dp.minDate(e.date)
      },
      submit: function () {
        this.loading = true
        API({
          method: this.formAction,
          url: 'package/' + this.form.package_id + '/rates/' +
            (this.$route.params.rate_id !== undefined ? this.$route.params.rate_id : ''),
          data: this.form
        })
          .then((result) => {
            if (result.data.success === true) {
              this.$router.push('/packages/' + this.$route.params.package_id + '/manage_package/package_rates')
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.package_rates'),
                text: this.$t('packagesmanagepackagerates.error.messages.information_error')
              })

              this.loading = false
            }
          })
      }
    },
    filters: {
      capitalize: function (value) {
        if (!value) return ''
        value = value.toString().toLowerCase()
        return value.charAt(0).toUpperCase() + value.slice(1)
      }
    }
  }
</script>

<style>
    .custom-control-input:checked ~ .custom-control-label::before {
        border-color: #3ea662;
        background-color: #3a9d5d;
    }

    .form-row {
        margin-bottom: 5px;
    }

    .container_errors {
        margin-top: 3px;
        border-radius: 2px;
    }

    .margin_icon_error {
        margin-left: 5px;
    }

    .container_channel_code {
        margin-bottom: 10px;
    }
</style>

