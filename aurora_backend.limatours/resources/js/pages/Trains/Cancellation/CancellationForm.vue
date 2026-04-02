<template>
    <div class="container mt-2 ml-3">
        <div class="row">
            <div class="col-12">
                <form @submit.prevent="validateBeforeSubmit" class="row">
                    <div class="col-12 b-form-group form-group">
                        <div class="form-row">
                            <label class="col-form-label" for="name">{{ $t('global.name') }}</label>
                            <div class="col-sm-6">
                                <input :class="{'form-control':true }"
                                       id="name" name="name" autocomplete="off"
                                       type="text"
                                       v-model="form.name" v-validate="'required'">
                                <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                    <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                       style="margin-left: 5px;" v-show="errors.has('name')"/>
                                    <span v-show="errors.has('name')">{{ errors.first('name') }}</span>
                                </div>
                            </div>

                            <label class="col-form-label" for="min_num">PAX {{$t('hotelsmanagehotelconfiguration.min_num')}}</label>
                            <div class="col-sm-1">
                                <input :class="{'form-control':true }"
                                       id="min_num" name="min_num"
                                       type="number" minlength="1" maxlength="3" min="1" max="998"
                                       v-model.number="form.min_num"
                                       v-validate="'required:numeric|min_value:1|max_value:998'">
                                <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                    <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                       style="margin-left: 5px;"
                                                       v-show="errors.has('min_num')"/>
                                    <span v-show="errors.has('min_num')">{{ errors.first('min_num') }}</span>
                                </div>
                            </div>

                            <label class="col-form-label" for="max_num">PAX {{$t('hotelsmanagehotelconfiguration.max_num')}}</label>
                            <div class="col-sm-1">
                                <input :class="{'form-control':true }"
                                       id="max_num" name="max_num"
                                       type="number" minlength="1" maxlength="3" :min="(form.min_num + 1)" max="999"
                                       v-model.number="form.max_num"
                                       v-validate="'required:numeric|min_value:'+ (form.min_num + 1) +'|max_value:999'">
                                <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                    <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                       style="margin-left: 5px;"
                                                       v-show="errors.has('max_num')"/>
                                    <span v-show="errors.has('max_num')">{{ errors.first('max_num') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="b-form-group form-group">
                        <div class="form-row">
                            <label class="col-form-label"
                                   for="min_day">{{$t('hotelsmanagehotelconfiguration.since')}}</label>
                            <div class="col-sm-1">
                                <input :class="{'form-control':true }"
                                       id="min_day" name="min_day"
                                       type="text"
                                       v-model.number="param.min_day"
                                       v-validate="'numeric|min:0|max:100'">
                                <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                    <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                       style="margin-left: 5px;" v-show="errors.has('min_day')"/>
                                    <span v-show="errors.has('min_day')">{{ errors.first('min_day') }}</span>
                                </div>
                            </div>

                            <label class="col-form-label" for="max_day">{{$t('hotelsmanagehotelconfiguration.day_until')}}</label>
                            <div class="col-sm-1">
                                <input :class="{'form-control':true }" id="max_day" name="max_day" placeholder=""
                                       type="text"
                                       v-model.number="param.max_day"
                                       v-validate="'numeric|min:0|max:100'">
                                <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                    <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                       style="margin-left: 5px;" v-show="errors.has('max_day')"/>
                                    <span v-show="errors.has('max_day')">{{ errors.first('max_day') }}</span>
                                </div>
                            </div>

                            <label class="col-form-label" for="penalty">{{$t('hotelsmanagehotelconfiguration.days_before')}}</label>
                            <label class="col-form-label" for="penalty">{{$t('hotelsmanagehotelconfiguration.penality')}}</label>
                            <div class="col-sm-3">
                                <v-select id="penalty" ref="penalty"
                                          :options="penalties" autocomplete="true"
                                          v-model="param.penalty_id"
                                          :reduce="penalty => penalty.value"
                                          label="text" code="value" required>
                                    <template slot="penaltiy" slot-scope="penaltiy">{{ $t(penaltiy.text) }}</template>
                                </v-select>
                            </div>
                            <div class="col-sm-2" v-if="param.penalty_id == 2 || param.penalty_id == 1">
                                <input :class="{'form-control':true }"
                                       id="amount" name="amount" type="text"
                                       v-model.number="param.amount"
                                       v-validate.immediate="'decimal:'+ (param.penalty_id == 2 ? '2':'') +'|required_if:penalty,1,2'">
                                <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                    <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                       style="margin-left: 5px;" v-show="errors.has('amount')"/>
                                    <span v-show="errors.has('amount')">{{ errors.first('amount') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="b-form-group form-group">
                        <div class="form-row">
                            <label class="col-sm-6 col-form-label">{{ $t('hotelsmanagehotelconfiguration.payment_taxes')
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
                                $t('hotelsmanagehotelconfiguration.payment_services') }}</label>
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
                            <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                            {{$t('hotelsmanagehotelconfiguration.add')}}
                        </button>
                    </div>

                    <div class="form-row" style="width: 100%;">
                        <div class="col-12 ml-0 pl-0">
                            <table-client :columns="table.columns" :data="parameters" :options="tableOptions"
                                          id="dataTable"
                                          theme="bootstrap4">
                                <template slot="penalty_id" slot-scope="props">
                                    <div v-if="props.row.penalty_id == 1">{{$t('hotelsmanagehotelconfiguration.nigth')}}
                                    </div>
                                    <div v-else-if="props.row.penalty_id == 2">
                                        {{$t('hotelsmanagehotelconfiguration.percentage')}}
                                    </div>
                                    <div v-else>{{$t('hotelsmanagehotelconfiguration.total_reservation')}}</div>
                                </template>
                                <template slot="tax" slot-scope="props">
                                    <c-switch class="mx-1 mt-1" color="primary" :id="'checkbox-'+props.row.count"
                                              :name="'checkbox-'+props.row.count"
                                              v-model="props.row.tax"
                                              @change="changeState(props)"
                                              variant="pill">
                                    </c-switch>
                                </template>
                                <template slot="service" slot-scope="props">
                                    <c-switch class="mx-1 mt-1" color="primary" :id="'checkbox-'+props.row.count"
                                              :name="'checkbox-'+props.row.count"
                                              v-model="props.row.service"
                                              @change="changeState(props)"
                                              variant="pill">
                                    </c-switch>
                                </template>
                                <template slot="delete" slot-scope="props">
                                    <button @click="removeParam(props.row.id, props.row.count)" class="btn"
                                            type="submit"
                                            v-if="!loading">
                                        <font-awesome-icon :icon="['fas', 'times']"/>
                                    </button>
                                </template>
                            </table-client>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-sm-6">
                <div slot="footer">
                    <img src="/images/loading.svg" v-if="loading" width="40px"/>
                    <button @click="validateBeforeSubmit" class="btn btn-success" type="submit" v-if="!loading">
                        <font-awesome-icon :icon="['fas', 'dot-circle']"/>
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
  import { API } from './../../../api'
  import VueBootstrapTypeahead from 'vue-bootstrap-typeahead'
  import { Switch as cSwitch } from '@coreui/vue'
  import TableClient from './.././../../components/TableClient'
  import MenuEdit from './../../../components/MenuEdit'
  import vSelect from 'vue-select'

  export default {
    props: ['form'],
    components: {
      'table-client': TableClient,
      'menu-edit': MenuEdit,
      VueBootstrapTypeahead,
      cSwitch,
      vSelect,
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
          max_day: null,
          min_day: null,
          amount: null,
          min_num: null,
          max_num: null,
          penalty_id: null,
          service: false,
          tax: false,
          count: 0,
          details: ''
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
            details: this.$i18n.t('hotelsmanagehotelconfiguration.details'),
            penalty_id: this.$i18n.t('hotelsmanagehotelconfiguration.type_penality'),
            amount: this.$i18n.t('hotelsmanagehotelconfiguration.amount'),
            tax: this.$i18n.t('hotelsmanagehotelconfiguration.tax'),
            service: this.$i18n.t('hotelsmanagehotelconfiguration.service'),
            delete: this.$i18n.t('hotelsmanagehotelconfiguration.delete')
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
      if (this.form.id != null) {
        API.get('/train_cancellation_policies/parameters?lang=' + localStorage.getItem('lang') + '&id=' + this.form.id)
          .then((result) => {
            this.parameters = result.data.data
            for (var i = this.parameters.length - 1; i >= 0; i--) {
              this.parameters[i].id = this.parameters[i].id
              this.parameters[i].count = (i + 1)
              this.parameters[i].service = !!this.parameters[i].service
              this.parameters[i].tax = !!this.parameters[i].tax
              this.parameters[i].details = this.$t('hotelsmanagehotelconfiguration.since') + ' ' +
                this.parameters[i].min_day + ' ' + this.$t('hotelsmanagehotelconfiguration.day_until') + ' ' +
                this.parameters[i].max_day + ' ' + this.$t('hotelsmanagehotelconfiguration.days_before')
            }

          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('hotelsmanagehotelconfiguration.error.messages.name'),
            text: this.$t('hotelsmanagehotelconfiguration.error.messages.connection_error')
          })
        })
      }

      //penalties
      API.get('/service/penalties/selectBox?lang=' + localStorage.getItem('lang'))
        .then((result) => {
          this.penalties = result.data.data
        }).catch(() => {
        this.$notify({
          group: 'main',
          type: 'error',
          title: this.$t('hotelsmanagehotelconfiguration.error.messages.name'),
          text: this.$t('hotelsmanagehotelconfiguration.error.messages.connection_error')
        })
      })
    },
    methods: {

        changeState: function (props) {


            for (var i = 0 ; i<= this.parameters.length - 1; i++) {


                if(i == (props.index - 1)){

                    if (props.column == "tax") {
                        this.parameters[i].tax = props.row.tax;
                    }else{
                        this.parameters[i].service = props.row.service;
                    }
                }

            }

        },

      validateAmount: function () {
        if (this.param.penalty_id == 1 || this.param.penalty_id == 2) {
          return this.flagAmount = true
        }
        return this.flagAmount = false
      },
      validateAdd () {
        if (this.param.min_day == null || this.param.max_day == null || this.param.penalty_id == null) {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('hotelsmanagehotelconfiguration.cancellation_policy'),
            text: this.$t('hotelsmanagehotelconfiguration.error.messages.information_incomplete_parameters')
          })
          return false
        }

        if (this.param.penalty_id != 3 && this.param.amount == null) {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('hotelsmanagehotelconfiguration.cancellation_policy'),
            text: this.$t('hotelsmanagehotelconfiguration.error.messages.information_incomplete_amount')
          })
          return false
        }

        this.add()
      },
      add: function () {
        this.param.count = (this.parameters.length + 1)
        this.param.details = this.$t('hotelsmanagehotelconfiguration.since') + ' ' + this.param.min_day + ' ' +
          this.$t('hotelsmanagehotelconfiguration.day_until') + ' ' + this.param.max_day + ' ' +
          this.$t('hotelsmanagehotelconfiguration.days_before')
        this.parameters.push(this.param)
        this.clear()
      },
      removeItem: function (count) {
        let index = this.parameters.findIndex(parameter => parameter.count == count)
        console.log(index)
        this.parameters.splice(index, 1)
      },
      clear: function () {
        this.param = {
          max_day: null,
          min_day: null,
          min_num: null,
          max_num: null,
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
            title: this.$t('hotelsmanagehotelconfiguration.cancellation_policy'),
            text: this.$t('hotelsmanagehotelconfiguration.error.messages.information_complete')
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
              text: this.$t('hotelsmanagehotelconfiguration.error.messages.information_complete')
            })

            this.loading = false
          }
        })
      },
      submit () {

        this.loading = true
        API({
          method: this.form.action,
          url: 'train_cancellation_policies' + (this.form.id !== null ? '/' + this.form.id : ''),
          data: { parameters: this.parameters, form: this.form }
        })
          .then((result) => {
            if (result.data.success === false) {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.contacts'),
                text: this.$t('hotelsmanagehotelconfiguration.error.messages.contact_incorrect')
              })
              this.loading = false
            } else {
              this.close()
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('hotelsmanagehotelconfiguration.error.messages.name'),
            text: this.$t('hotelsmanagehotelconfiguration.error.messages.connection_error')
          })
        })
      },
      removeParam (id, count) {
        if (id != null) {
          API({
            method: 'DELETE',
            url: 'train_cancellation_policies/parameter/' + (id)
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
                  text: this.$t('hotelsmanagehotelconfiguration.error.messages.contact_delete')
                })

                this.loading = false
              }
            }).catch(() => {
            this.$notify({
              group: 'main',
              type: 'error',
              title: this.$t('hotelsmanagehotelconfiguration.error.messages.name'),
              text: this.$t('hotelsmanagehotelconfiguration.error.messages.connection_error')
            })
          })
        } else {
          this.removeItem(count)
        }
      }
    }
  }
</script>

<style lang="stylus">
    .s-color {
        color: red;
    }
</style>
