<template>
    <div class="container mt-2 ml-3">
        <div class="row">
            <div class="col-12">
                <form @submit.prevent="validateBeforeSubmit" class="row">
                    <div class="row">
                        <div class="b-form-group form-group col-6">
                            <div class="form-row" style="margin-bottom: 5px;">
                                <label class="col-sm-2 col-form-label" for="provider">{{
                                    $t('servicesmanageservicerates.provider') }}</label>

                                <div class="col-sm-10">
                                    <v-select :options="providers"
                                              :value="form.user_id"
                                              :filterable="false" @search="onSearchProvider"
                                              autocomplete="true"
                                              data-vv-as="provider"
                                              data-vv-name="provider"
                                              id="provider"
                                              name="provider"
                                              v-model="providerSelected"
                                              v-validate="'required'">
                                        <template slot="option" slot-scope="option">
                                            <div class="d-center">
                                                {{ option.name }}
                                            </div>
                                        </template>
                                        <template slot="selected-option" slot-scope="option">
                                            <div class="selected d-center">
                                                {{ option.name }}
                                            </div>
                                        </template>
                                    </v-select>
                                    <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                        <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                           style="margin-left: 5px;"
                                                           v-show="errors.has('provider')" />
                                        <span
                                            v-show="errors.has('description')">{{ errors.first('provider') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="b-form-group form-group col-md-6">
                            <div class="b-form-group form-group">
                                <div class="form-row">
                                    <label class="col-sm-3 col-form-label" for="name">{{ $t('global.name') }}</label>
                                    <div class="col-sm-9">
                                        <input :class="{'form-control':true }"
                                               id="name" name="name"
                                               type="text"
                                               v-model="form.name" v-validate="'required'">
                                        <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                            <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                               style="margin-left: 5px;" v-show="errors.has('name')" />
                                            <span v-show="errors.has('name')">{{ errors.first('name') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="b-form-group form-group ml-3">
                            <label class="col-form-label mr-3"
                                   for="type_fit">{{$t('hotelsmanagehotelconfiguration.types_fit')}}</label>
                        </div>
                        <label class="col-form-label" for="min_num">{{$t('hotelsmanagehotelconfiguration.min_num')}}</label>
                        <div class="col-sm-1">
                            <input :class="{'form-control':true }"
                                   id="min_num" name="min_num"
                                   type="number" minlength="1" maxlength="3" min="1" max="998"
                                   v-model.number="form.min_num"
                                   v-validate="'numeric|min_value:1|max_value:998'">
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;"
                                                   v-show="errors.has('min_num')" />
                                <span v-show="errors.has('min_num')">{{ errors.first('min_num') }}</span>
                            </div>
                        </div>
                        <label class="col-form-label" for="max_num">{{$t('hotelsmanagehotelconfiguration.max_num')}}</label>
                        <div  class="col-sm-1">
                            <input :class="{'form-control':true }"
                                   id="max_num" name="max_num"
                                   type="number" minlength="1" maxlength="3" :min="(form.min_num + 1)" max="999"
                                   v-model.number="form.max_num"
                                   v-validate="'numeric|min_value:'+ (form.min_num + 1) +'|max_value:999'">
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;"
                                                   v-show="errors.has('max_num')"/>
                                <span v-show="errors.has('max_num')">{{ errors.first('max_num') }}</span>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="b-form-group form-group">
                                <div class="form-row">
                                    <label class="col-sm-2 col-form-label" for="description">
                                        {{ $t('hotelsmanagehotelconfiguration.description') }}
                                    </label>
                                    <div class="col-sm-8">
                          <textarea :class="{'form-control':true }"
                                    cols="100" id="description"
                                    name="description"
                                    rows="5" type="text"
                                    v-model="translDesc[currentLang].policy_description"
                                    v-validate="'required'">
                            </textarea>
                                        <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                            <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                               style="margin-left: 5px;"
                                                               v-show="errors.has('description')" />
                                            <span
                                                v-show="errors.has('description')">{{ errors.first('description') }}</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-2">
                                        <select class="form-control" id="lang" required size="0"
                                                v-model="currentLang">
                                            <option v-bind:value="language.id" v-for="language in languages">
                                                {{ language.iso }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="b-form-group form-group">
                        <div class="form-row">
                            <label class="col-form-label"
                                   for="min_hours">{{$t('servicesmanageservicepolitics.since')}}</label>
                            <div class="col-sm-1">
                                <input :class="{'form-control':true }"
                                       id="min_hour" name="min_hour"
                                       type="text"
                                       v-model="param.min_hour" v-validate="'numeric|min:0|max:100'">

                                <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                    <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                       style="margin-left: 5px;" v-show="errors.has('min_hour')" />
                                    <span v-show="errors.has('min_hour')">{{ errors.first('min_hour') }}</span>
                                </div>
                            </div>
                            <div class="col-sm-1">
                                <select name="duration" id="duration" v-model="param.unit_duration"
                                        class="form-control">
                                    <option value="1">Horas</option>
                                    <option value="2">Días</option>
                                </select>
                            </div>
                            <label class="col-form-label" for="min_hours">{{$t('servicesmanageservicepolitics.hour_until')}}</label>

                            <div class="col-sm-1">
                                <input :class="{'form-control':true }" id="max_hour" name="max_hour" placeholder=""
                                       type="text" v-model="param.max_hour" v-validate="'numeric|min:0|max:100'">
                                <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                    <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                       style="margin-left: 5px;" v-show="errors.has('max_hour')" />
                                    <span v-show="errors.has('max_hour')">{{ errors.first('max_hour') }}</span>
                                </div>
                            </div>
                            <label class="col-form-label" for="penalty">{{$t('servicesmanageservicepolitics.hours_before')}}</label>
                            <div class="col-sm-3">
                                <select @click="validateAmount" ref="penalty" class="form-control" id="penalty" required
                                        size="0" v-model="param.service_penalty_id">
                                    <option disabled value="">{{ $t('servicesmanageservicepolitics.select_penality')
                                        }}
                                    </option>
                                    <option :value="penalty.value" v-for="penalty in penalties">
                                        {{ $t('servicesmanageservicepolitics.'+penalty.text) }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-sm-2">
                                <input :class="{'form-control':true }"
                                       id="amount" name="amount"
                                       type="text"
                                       v-model="param.amount"
                                       v-validate.immediate="'decimal:2|required_if:penalty,1,2'">
                                <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                    <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                       style="margin-left: 5px;" v-show="errors.has('amount')" />
                                    <span v-show="errors.has('amount')">{{ errors.first('amount') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="b-form-group form-group">
                        <div class="form-row">
                            <label class="col-sm-6 col-form-label">{{ $t('servicesmanageservicepolitics.payment_taxes')
                                }}</label>
                            <div class="col-sm-2">
                                <c-switch class="mx-1 mt-2" color="primary" id="tax-10"
                                          name="tax-10"
                                          v-model="param.tax"
                                          variant="pill">
                                </c-switch>
                            </div>
                        </div>
                    </div>
                    <div class="b-form-group form-group">
                        <div class="form-row">
                            <label class="col-sm-6 col-form-label">{{
                                $t('servicesmanageservicepolitics.payment_services') }}</label>
                            <div class="col-sm-3">
                                <c-switch class="mx-1 mt-2" color="primary" id="service-10"
                                          name="service-10"
                                          v-model="param.service"
                                          variant="pill">
                                </c-switch>
                            </div>
                        </div>
                    </div>

                    <button @click="validateAdd" class="btn btn-success mb-2 ml-5 mt-2" type="button">
                        <font-awesome-icon :icon="['fas', 'dot-circle']" />
                        {{$t('global.buttons.add')}}
                    </button>
                    <div class="col-12 ml-0 pl-0">
                        <table-client :columns="table.columns" :data="parameters" :options="tableOptions" id="dataTable"
                                      theme="bootstrap4">
                            <template slot="service_penalty_id" slot-scope="props">
                                <div v-if="props.row.service_penalty_id === 1">
                                    {{$t('servicesmanageservicepolitics.amount')}}
                                </div>
                                <div v-else-if="props.row.service_penalty_id === 2">
                                    {{$t('servicesmanageservicepolitics.percentage')}}
                                </div>
                                <div v-else-if="props.row.service_penalty_id === 3">
                                    {{$t('servicesmanageservicepolitics.pax')}}
                                </div>
                            </template>
                            <template slot="tax" slot-scope="props">
                                <c-switch class="mx-1 mt-1" color="primary" :id="'checkbox-'+props.row.count"
                                          :name="'checkbox-'+props.row.count"
                                          v-model="props.row.tax"
                                          @change="changeStateTax(props.index,props.row.tax)"
                                          variant="pill">
                                </c-switch>
                            </template>
                            <template slot="service" slot-scope="props">
                                <c-switch class="mx-1 mt-1" color="primary" :id="'checkbox-'+props.row.count"
                                          :name="'checkbox-'+props.row.count"
                                          v-model="props.row.service"
                                          @change="changeStateService(props.index,props.row.service)"
                                          variant="pill">
                                </c-switch>
                            </template>
                            <template slot="delete" slot-scope="props">
                                <button @click="removeParam(props.row.id, props.row.count)" class="btn" type="submit"
                                        v-if="!loading">
                                    <font-awesome-icon :icon="['fas', 'times']" />
                                </button>
                            </template>
                        </table-client>
                    </div>

                </form>
            </div>
            <div class="col-sm-6">
                <div slot="footer">
                    <img src="/images/loading.svg" v-if="loading" width="40px" />
                    <button @click="validateBeforeSubmit" class="btn btn-success" type="submit" v-if="!loading">
                        <font-awesome-icon :icon="['fas', 'dot-circle']" />
                        {{$t('global.buttons.save')}}
                    </button>
                    <button v-if="!loading" @click="close" class="btn btn-danger" type="reset">
                        {{$t('global.buttons.cancel')}}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
  import { API } from './../../api'
  import VueBootstrapTypeahead from 'vue-bootstrap-typeahead'
  import { Switch as cSwitch } from '@coreui/vue'
  import TableClient from './.././../components/TableClient'
  import MenuEdit from './../../components/MenuEdit'
  import vSelect from 'vue-select'
  import 'vue-select/dist/vue-select.css'

  export default {
    components: {
      'table-client': TableClient,
      'menu-edit': MenuEdit,
      VueBootstrapTypeahead,
      vSelect,
      cSwitch,
    },
    data: () => {
      return {
        form: {
          id: null,
          name: '',
          user_id: '',
          min_num: '',
          max_num: '',
          action: 'post',
        },
        providers: [],
        languages: [],
        flagAmount: false,
        contact: null,
        penalties: [],
        parameters: [],
        providerSelected: null,
        policy: [],
        param: {
          id: null,
          max_hour: null,
          min_hour: null,
          unit_duration: 1,
          amount: 0,
          service_penalty_id: null,
          service: false,
          tax: false,
          count: 0,
          details: ''
        },
        translDesc: {
          '1': {
            'id': '',
            'policy_description': ''
          }
        },
        showError: false,
        currentLang: '1',
        invalidError: false,
        countError: 0,
        invalidErrorMin: false,
        countErrorMin: 0,
        invalidErrorMax: false,
        countErrorMax: 0,
        invalidErrorA: false,
        countErrorA: 0,
        loading: false,
        table: {
          columns: ['details', 'service_penalty_id', 'amount', 'tax', 'service', 'delete']
        },
      }
    },
    computed: {
      tableOptions: function () {
        return {
          headings: {
            id: 'ID',
            details: this.$i18n.t('servicesmanageservicepolitics.details'),
            service_penalty_id: this.$i18n.t('servicesmanageservicepolitics.type_penality'),
            amount: this.$i18n.t('servicesmanageservicepolitics.amount'),
            tax: this.$i18n.t('servicesmanageservicepolitics.tax'),
            service: this.$i18n.t('servicesmanageservicepolitics.service'),
            delete: this.$i18n.t('servicesmanageservicepolitics.delete')
          },
          sortable: ['id'],
          filterable: []
        }
      },
      validError: function () {
        if (this.errors.has('name') === false && this.form.name !== '') {
          this.invalidError = false
          this.countError += 1
          return true

        } else if (this.countError > 0) {
          this.invalidError = true
        }

        if (this.errors.has('min_age') === false && this.form.min_age !== '') {
          this.invalidErrorMin = false
          this.countErrorMin += 1
          return true

        } else if (this.countError > 0) {
          this.invalidError = true
        }

        if (this.errors.has('max_age') === false && this.form.max_age !== '') {
          this.invalidErrorMin = false
          this.countErrorMin += 1
          return true

        } else if (this.countError > 0) {
          this.invalidError = true
        }

        if (this.errors.has('amount') === false && this.form.amount !== '') {
          this.invalidErrorA = false
          this.countErrorA += 1
          return true

        } else if (this.countError > 0) {
          this.invalidError = true
        }

        return false
      }
    },
    mounted: function () {
      this.form.id = this.$route.params.id !== undefined ? this.$route.params.id : null
      this.form.action = this.$route.params.id !== undefined ? 'put' : 'post'
      console.log(this.form)
      //parameters
      API.get('/languages/')
        .then((result) => {
          this.languages = result.data.data
          this.currentLang = result.data.data[0].id

          this.languages.forEach((value) => {
            this.translDesc[value.id] = {
              id: '',
              policy_description: ''
            }
          })

          if (this.form.id != null) {
            API.get('service/cancellations_policies/parameters?lang=' + localStorage.getItem('lang') + '&id=' + this.form.id)
              .then((result) => {
                this.parameters = result.data.data.parameters
                this.policy = result.data.data.policy[0]
                this.form.name = this.policy.name
                this.form.min_num = this.policy.min_num
                this.form.max_num = this.policy.max_num
                // this.translDesc = this.policy.name
                this.policy.translations.forEach((translation) => {
                  this.translDesc[translation.language_id] = {
                    id: translation.id,
                    policy_description: translation.value
                  }
                })

                this.providerSelected = {
                  id:  this.policy.provider.id,
                  name: this.policy.provider.name
                }

                for (var i = this.parameters.length - 1; i >= 0; i--) {
                  this.parameters[i].id = this.parameters[i].id
                  this.parameters[i].count = (i + 1)
                  this.parameters[i].service = !!this.parameters[i].service
                  this.parameters[i].tax = !!this.parameters[i].tax
                  this.parameters[i].unit_duration = this.parameters[i].unit_duration
                  let duration_label = this.$t('packagesmanagepackageconfiguration.hours').toLowerCase()
                  if (this.parameters[i].unit_duration == 2) {
                    duration_label = this.$t('hotelsmanagehotelconfiguration.days').toLowerCase()
                  }
                  this.parameters[i].details = this.$t('servicesmanageservicepolitics.since') + ' ' +
                    this.parameters[i].min_hour + ' ' + duration_label + ', ' + this.$t('servicesmanageservicepolitics.hour_until') + ' ' +
                    this.parameters[i].max_hour + ' ' + duration_label + ' ' + this.$t('servicesmanageservicepolitics.hours_before')
                }

              }).catch(() => {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('servicesmanageservicepolitics.cancellation_policy'),
                text: this.$t('servicesmanageservicepolitics.error.messages.connection_error')
              })
            })
          }

        }).catch(() => {
        this.$notify({
          group: 'main',
          type: 'error',
          title: this.$t('servicesmanageservicepolitics.cancellation_policy'),
          text: this.$t('servicesmanageservicepolitics.error.messages.connection_error')
        })
      })

      //penalties
      API.get('service/penalties/selectBox?lang=' + localStorage.getItem('lang'))
        .then((result) => {
          this.penalties = result.data.data
        }).catch(() => {
        this.$notify({
          group: 'main',
          type: 'error',
          title: this.$t('servicesmanageservicepolitics.cancellation_policy'),
          text: this.$t('servicesmanageservicepolitics.error.messages.connection_error')
        })
      })
    },
    methods: {
      validateAmount: function () {
        if (this.param.service_penalty_id == 1 || this.param.service_penalty_id == 2) {
          return this.flagAmount = true
        }
        return this.flagAmount = false
      },
      validateAdd () {
        if (this.param.min_hour == null || this.param.max_hour == null || this.param.service_penalty_id == null) {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('servicesmanageservicepolitics.cancellation_policy'),
            text: this.$t('servicesmanageservicepolitics.error.messages.information_incomplete_parameters')
          })
          return false
        }

        if (this.param.service_penalty_id != 3 && this.param.amount == null) {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('servicesmanageservicepolitics.cancellation_policy'),
            text: this.$t('servicesmanageservicepolitics.error.messages.information_incomplete_amount')
          })
          return false
        }

        this.add()
      },
      add: function () {
        this.param.count = (this.parameters.length + 1)
        let duration_label = this.$t('packagesmanagepackageconfiguration.hours').toLowerCase()
        if (this.param.unit_duration == 2) {
          duration_label = this.$t('hotelsmanagehotelconfiguration.days').toLowerCase()
        }
        console.log(this.param.unit_duration )
        this.param.details = this.$t('servicesmanageservicepolitics.since') + ' ' +
          this.param.min_hour + ' ' + duration_label + ', ' + this.$t('servicesmanageservicepolitics.hour_until') + ' ' +
          this.param.max_hour + ' ' + duration_label + ' ' + this.$t('servicesmanageservicepolitics.hours_before')
        this.parameters.push(this.param)
        this.clear()
      },
      removeItem: function (count) {
        let index = this.parameters.findIndex(parameter => parameter.count === count)
        console.log(index)
        this.parameters.splice(index, 1)
      },
      clear: function () {
        this.param = {
          max_hour: null,
          min_hour: null,
          unit_duration: 1,
          details: '',
          amount: null,
          service_penalty_id: null,
          service: false,
          tax: false,
        }
      },
      close () {
        this.$router.push({ path: '/cancellation_policies' })
      },
      validateBeforeSubmit () {
        if (this.form.name == null || this.parameters.length <= 0) {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('servicesmanageservicepolitics.cancellation_policy'),
            text: 'Ingrese el nombre de la politica y los parametros'
          })
          return false
        }
        if (this.providerSelected == null) {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('servicesmanageservicepolitics.cancellation_policy'),
            text: 'Seleccione a un proveedor'
          })
          return false
        }
        this.$validator.validateAll().then((result) => {
          if (result) {
            this.form.user_id = this.providerSelected.id
            this.submit()

          } else {
            this.$notify({
              group: 'main',
              type: 'error',
              title: this.$t('global.modules.contacts'),
              text: this.$t('servicesmanageservicepolitics.error.messages.information_complete')
            })

            this.loading = false
          }
        })
      },
      submit () {

        API({
          method: this.form.action,
          url: 'service/cancellations_policies/' + (this.form.id !== null ? this.form.id : ''),
          data: { parameters: this.parameters, form: this.form, translDesc: this.translDesc }
        })
          .then((result) => {
            if (result.data.success === false) {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.contacts'),
                text: this.$t('servicesmanageservicepolitics.error.messages.contact_incorrect')
              })
              this.loading = false
            } else {
              this.close()
              this.loading = false
            }
          }).catch(() => {
          this.loading = false
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('servicesmanageservicepolitics.cancellation_policy'),
            text: this.$t('servicesmanageservicepolitics.error.messages.connection_error')
          })
        })
      },
      removeParam (id, count) {
        if (id != null) {
          API({
            method: 'DELETE',
            url: 'service/cancellations_policies/parameters/' + (id)
          })
            .then((result) => {
              console.log(result)
              if (result.data.success === true) {
                this.removeItem(count)
              } else {
                this.$notify({
                  group: 'main',
                  type: 'error',
                  title: this.$t('global.modules.contacts'),
                  text: this.$t('servicesmanageservicepolitics.error.messages.contact_delete')
                })

                this.loading = false
              }
            }).catch(() => {
            this.$notify({
              group: 'main',
              type: 'error',
              title: this.$t('servicesmanageservicepolitics.cancellation_policy'),
              text: this.$t('servicesmanageservicepolitics.error.messages.connection_error')
            })
          })
        } else {
          this.removeItem(count)
        }
      },
      changeStateTax: function (index, status) {
        this.parameters[(index - 1)].tax = status
      },
      onSearchProvider (search, loading) {
        loading(true)
        API.get('/providers/selectBox?query=' + search)
          .then((result) => {
            loading(false)
            this.providers = result.data.data
          }).catch(() => {
          loading(false)
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('global.modules.services'),
            text: this.$t('servicesmanageservicerates.error.messages.system')
          })
        })
      },
      changeStateService: function (index, status) {
        this.parameters[(index - 1)].service = status
      }
    }
  }
</script>

<style lang="stylus">
    .s-color {
        color: red;
    }
</style>
