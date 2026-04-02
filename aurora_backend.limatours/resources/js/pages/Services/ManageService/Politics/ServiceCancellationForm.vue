<template>
    <div class="container mt-2 ml-3">
        <div class="row">
            <div class="col-12">
                <form @submit.prevent="validateBeforeSubmit" class="row">
                    <div class="row">
                        <div class="col-md-4">
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
                        <div class="col-md-8">
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
                                                               style="margin-left: 5px;" v-show="errors.has('description')"/>
                                            <span v-show="errors.has('description')">{{ errors.first('description') }}</span>
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
                                        size="0" v-model="param.penalty_id">
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
                    <div class="col-6">
                        <button @click="validateAdd" class="btn btn-success mb-2 ml-5 mt-2" type="button">
                            <font-awesome-icon :icon="['fas', 'dot-circle']" />
                            {{$t('global.buttons.add')}}
                        </button>
                    </div>
                    <div class="col-12 ml-0 pl-0">
                        <table-client :columns="table.columns" :data="parameters" :options="tableOptions" id="dataTable"
                                      theme="bootstrap4">
                            <template slot="penalty_id" slot-scope="props">
                                <div v-if="props.row.penalty_id === 1"> {{$t('servicesmanageservicepolitics.amount')}}
                                </div>
                                <div v-else-if="props.row.penalty_id === 2">
                                    {{$t('servicesmanageservicepolitics.percentage')}}
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
  import { API } from './../../../../api'
  import VueBootstrapTypeahead from 'vue-bootstrap-typeahead'
  import { Switch as cSwitch } from '@coreui/vue'
  import TableClient from './.././../../../components/TableClient'
  import MenuEdit from './../../../../components/MenuEdit'

  export default {
    props: ['form'],
    components: {
      'table-client': TableClient,
      'menu-edit': MenuEdit,
      VueBootstrapTypeahead,
      cSwitch,
    },
    data: () => {
      return {
        languages: [],
        flagAmount: false,
        contact: null,
        penalties: [],
        parameters: [],
        param: {
          id: null,
          max_hour: null,
          min_hour: null,
          unit_duration: 1,
          amount: 0,
          penalty_id: null,
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
          columns: ['details', 'penalty_id', 'amount', 'tax', 'service', 'delete']
        },
      }
    },
    computed: {
      tableOptions: function () {
        return {
          headings: {
            id: 'ID',
            details: this.$i18n.t('servicesmanageservicepolitics.details'),
            penalty_id: this.$i18n.t('servicesmanageservicepolitics.type_penality'),
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
                this.parameters = result.data.data
                for (var i = this.parameters.length - 1; i >= 0; i--) {
                  this.parameters[i].id = this.parameters[i].id
                  this.parameters[i].count = (i + 1)
                  this.parameters[i].service = !!this.parameters[i].service
                  this.parameters[i].tax = !!this.parameters[i].tax
                  this.parameters[i].unit_duration = this.parameters[i].unit_duration
                  this.parameters[i].details = this.$t('servicesmanageservicepolitics.since') + ' ' +
                    this.parameters[i].min_hour + ' ' + this.$t('servicesmanageservicepolitics.hour_until') + ' ' +
                    this.parameters[i].max_hour + ' ' + this.$t('servicesmanageservicepolitics.hours_before')
                }

              }).catch(() => {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('servicesmanageservicepolitics.error.messages.name'),
                text: this.$t('servicesmanageservicepolitics.error.messages.connection_error')
              })
            })
          }

        }).catch(() => {
        this.$notify({
          group: 'main',
          type: 'error',
          title: this.$t('hotelsmanagehotelconfiguration.error.messages.name'),
          text: this.$t('hotelsmanagehotelconfiguration.error.messages.connection_error')
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
          title: this.$t('servicesmanageservicepolitics.error.messages.name'),
          text: this.$t('servicesmanageservicepolitics.error.messages.connection_error')
        })
      })
    },
    methods: {
      validateAmount: function () {
        if (this.param.penalty_id == 1 || this.param.penalty_id == 2) {
          return this.flagAmount = true
        }
        return this.flagAmount = false
      },
      validateAdd () {
        if (this.param.min_hour == null || this.param.max_hour == null || this.param.penalty_id == null) {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('servicesmanageservicepolitics.cancellation_policy'),
            text: this.$t('servicesmanageservicepolitics.error.messages.information_incomplete_parameters')
          })
          return false
        }

        if (this.param.penalty_id != 3 && this.param.amount == null) {
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
        this.param.details = this.$t('servicesmanageservicepolitics.since') + ' ' + this.param.min_hour + ' ' +
          this.$t('servicesmanageservicepolitics.hour_until') + ' ' + this.param.max_hour + ' ' +
          this.$t('servicesmanageservicepolitics.hours_before')
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
          penalty_id: null,
          service: false,
          tax: false,
        }
      },
      close () {
        this.$emit('changeStatus', false)
      },
      validateBeforeSubmit () {
        if (this.form.name == null || this.parameters.length <= 0) {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('servicesmanageservicepolitics.cancellation_policy'),
            text: this.$t('servicesmanageservicepolitics.error.messages.information_complete')
          })
          return false
        }
        this.$validator.validateAll().then((result) => {
          if (result) {

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
        this.loading = true
        API({
          method: this.form.action,
          url: 'service/cancellations_policies/' + (this.form.id !== null ? this.form.id : ''),
          data: { parameters: this.parameters, form: this.form, translDesc: this.translDesc}
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
              // this.close()
              this.loading = false
            }
          }).catch(() => {
          this.loading = false
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('servicesmanageservicepolitics.error.messages.name'),
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
              title: this.$t('servicesmanageservicepolitics.error.messages.name'),
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
      changeStateService: function (index, status) {
        console.log(index, status)
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
