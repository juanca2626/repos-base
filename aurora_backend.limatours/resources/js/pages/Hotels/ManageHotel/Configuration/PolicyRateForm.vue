<template>
    <div class="row">
        <div class="col-sm-12">
            <form @submit.prevent="validateBeforeSubmit">
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="name">{{
                            $t('hotelsmanagehotelconfiguration.new_policy') }}</label>
                        <div class="col-sm-8">
                            <input :class="{'form-control':true }"
                                   id="name" name="name"
                                   type="text"
                                   v-model="form.name" v-validate="'required'">
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('name')"/>
                                <span v-show="errors.has('name')">{{ errors.first('name') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="policy_cancellation_id">
                            {{ $t('hotelsmanagehotelconfiguration.nom_cancellation') }}
                        </label>
                        <div class="col-sm-8">
                            <v-select :options="policyCancellations"
                                      autocomplete="true"
                                      v-model="form.policies_cancelation"
                                      multiple>
                            </v-select>
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;"
                                 v-show="errorPolicyCancellation">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']" style="margin-left: 5px;"/>
                                <span>{{ $t('hotelsmanagehotelconfiguration.error.required') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="min_length_stay">{{
                            $t('hotelsmanagehotelconfiguration.nro_nigth_min') }}</label>
                        <div class="col-sm-2">
                            <input :class="{'form-control':true }"
                                   id="min_length_stay" name="min_length_stay"
                                   type="text"
                                   v-model="form.min_length_stay" v-validate="'required|numeric'">
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('min_length_stay')"/>
                                <span v-show="errors.has('min_length_stay')">{{ errors.first('min_length_stay') }}</span>
                            </div>
                        </div>
                        <label class="col-sm-2 col-form-label" for="max_length_stay">{{
                            $t('hotelsmanagehotelconfiguration.nro_nigth_max') }}</label>
                        <div class="col-sm-2">
                            <input :class="{'form-control':true }"
                                   id="max_length_stay" name="max_length_stay"
                                   type="text"
                                   v-model="form.max_length_stay" v-validate="'required|numeric'">
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('max_length_stay')"/>
                                <span v-show="errors.has('max_length_stay')">{{ errors.first('max_length_stay') }}</span>
                            </div>
                        </div>
<!--                        <label class="col-sm-2 col-form-label" for="max_occupancy">{{-->
<!--                            $t('hotelsmanagehotelconfiguration.max_person') }}</label>-->
<!--                        <div class="col-sm-2">-->
<!--                            <input :class="{'form-control':true }"-->
<!--                                   id="max_occupancy" name="max_occupancy"-->
<!--                                   type="text"-->
<!--                                   v-model="form.max_occupancy" v-validate="'required|numeric'">-->
<!--                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">-->
<!--                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"-->
<!--                                                   style="margin-left: 5px;" v-show="errors.has('max_occupancy')"/>-->
<!--                                <span v-show="errors.has('max_occupancy')">{{ errors.first('max_occupancy') }}</span>-->
<!--                            </div>-->
<!--                        </div>-->
                    </div>
                </div>
                <table-client :columns="table.columns" :data="days" :options="tableOptions" id="dataTable"
                              theme="bootstrap4">
                    <div class="table-state" slot="all" slot-scope="props" style="font-size: 0.9em">
                        <b-form-checkbox
                                :checked="checkboxChecked(props.row.all)"
                                @change="changeState('all',props.row.all)" id="checkbox-all"
                                name="checkbox-all"
                                switch>
                        </b-form-checkbox>
                    </div>
                    <div class="table-state" slot="monday" slot-scope="props" style="font-size: 0.9em">
                        <b-form-checkbox
                                :checked="checkboxChecked(props.row.monday)"
                                @change="changeState('monday',props.row.monday)" id="checkbox-monday"
                                name="checkbox-monday"
                                switch>
                        </b-form-checkbox>
                    </div>
                    <div class="table-state" slot="tuesday" slot-scope="props" style="font-size: 0.9em">
                        <b-form-checkbox
                                :checked="checkboxChecked(props.row.tuesday)"
                                @change="changeState('tuesday',props.row.tuesday)" id="checkbox-tuesday"
                                name="checkbox-tuesday"
                                switch>
                        </b-form-checkbox>
                    </div>
                    <div class="table-state" slot="wednesday" slot-scope="props" style="font-size: 0.9em">
                        <b-form-checkbox
                                :checked="checkboxChecked(props.row.wednesday)"
                                @change="changeState('wednesday',props.row.wednesday)" id="checkbox-wednesday"
                                name="checkbox-wednesday"
                                switch>
                        </b-form-checkbox>
                    </div>
                    <div class="table-state" slot="thursday" slot-scope="props" style="font-size: 0.9em">
                        <b-form-checkbox
                                :checked="checkboxChecked(props.row.thursday)"
                                @change="changeState('thursday',props.row.thursday)" id="checkbox-thursday"
                                name="checkbox-thursday"
                                switch>
                        </b-form-checkbox>
                    </div>
                    <div class="table-state" slot="friday" slot-scope="props" style="font-size: 0.9em">
                        <b-form-checkbox
                                :checked="checkboxChecked(props.row.friday)"
                                @change="changeState('friday',props.row.friday)" id="checkbox-friday"
                                name="checkbox-friday"
                                switch>
                        </b-form-checkbox>
                    </div>
                    <div class="table-state" slot="saturday" slot-scope="props" style="font-size: 0.9em">
                        <b-form-checkbox
                                :checked="checkboxChecked(props.row.saturday)"
                                @change="changeState('saturday',props.row.saturday)" id="checkbox-saturday"
                                name="'checkbox-saturday"
                                switch>
                        </b-form-checkbox>
                    </div>
                    <div class="table-state" slot="sunday" slot-scope="props" style="font-size: 0.9em">
                        <b-form-checkbox
                                :checked="checkboxChecked(props.row.sunday)"
                                @change="changeState('sunday',props.row.sunday)" id="checkbox-sunday"
                                name="checkbox-sunday"
                                switch>
                        </b-form-checkbox>
                    </div>
                </table-client>
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
                                    v-model="form.translDesc[currentLang].policy_description"
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
            </form>
        </div>
        <div class="col-sm-6">
            <div slot="footer">
                <img src="/images/loading.svg" v-if="loading" width="40px"/>
                <button @click="validateBeforeSubmit" class="btn btn-success" type="submit" v-if="!loading">
                    <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                    {{$t('global.buttons.submit')}}
                </button>
                <button @click="close" class="btn btn-danger" type="reset" v-if="!loading">
                    {{$t('global.buttons.cancel')}}
                </button>
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
  import BFormSelect from 'bootstrap-vue/es/components/form-select/form-select'
  import vSelect from 'vue-select'

  export default {
    props: ['draft'],
    components: {
      'table-client': TableClient,
      'menu-edit': MenuEdit,
      VueBootstrapTypeahead,
      cSwitch,
      BFormSelect,
      vSelect,
    },
    data: () => {
      return {

        policyRate: [],
        policyCancellations: [],
        policyCancellation: null,
        policyCancellationSelected: [],
        policyCancellationSearch: '',
        invalidErrorPolicyCancellation: false,
        currentLang: '1',
        languages: [],
        contact: null,
        penalties: [],
        parameters: [],
        days: [{
          all: false,
          monday: false,
          tuesday: false,
          wednesday: false,
          thursday: false,
          friday: false,
          saturday: false,
          sunday: false
        }],
        form: {
          action: 'post',
          id: null,
          name: '',
          min_length_stay: null,
          max_length_stay: null,
          policies_cancelation: [],
          description: '',
          select_day: '',
          days_apply: [{
            all: false,
            monday: false,
            tuesday: false,
            wednesday: false,
            thursday: false,
            friday: false,
            saturday: false,
            sunday: false
          }],
          max_occupancy: 3,
          translDesc: {
            '1': {
              'id': '',
              'policy_description': ''
            }
          }
        },
        showError: false,
        invalidError: false,
        countError: 0,
        loading: false,
        table: {
          columns: ['all', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']
        },
      }
    },
    computed: {
      tableOptions: function () {
        return {
          headings: {
            all: this.$i18n.t('hotelsmanagehotelconfiguration.all'),
            monday: this.$i18n.t('hotelsmanagehotelconfiguration.monday'),
            tuesday: this.$i18n.t('hotelsmanagehotelconfiguration.tuesday'),
            wednesday: this.$i18n.t('hotelsmanagehotelconfiguration.wednesday'),
            thursday: this.$i18n.t('hotelsmanagehotelconfiguration.thursday'),
            friday: this.$i18n.t('hotelsmanagehotelconfiguration.friday'),
            saturday: this.$i18n.t('hotelsmanagehotelconfiguration.saturday'),
            sunday: this.$i18n.t('hotelsmanagehotelconfiguration.sunday')
          },
          filterable: []
        }
      },
      errorPolicyCancellation: function () {
        if (this.form.policy_cancellation_id == '') {
          if (this.invalidErrorPolicyCancellation == false) {
            this.invalidErrorPolicyCancellation = true
            return false
          } else {
            return true
          }
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

        return false
      }
    },
    mounted: function () {
      API.get('/languages/')
        .then((result) => {
          this.languages = result.data.data
          this.currentLang = result.data.data[0].id
          //this.form = this.draft

          //console.log(this.draft);

          this.form.id = this.draft.id
          this.form.hotel_id = this.$route.params.hotel_id,

            this.languages.forEach((value) => {
              this.form.translDesc[value.id] = {
                id: '',
                policy_description: ''
              }
            })

          if (this.form.id != null) {

            API.get('/policies_rates/' + this.form.id)
              .then((result) => {

                this.form.action = 'put'
                this.policyRate = result.data.data
                this.form.name = this.policyRate[0].name
                this.form.min_length_stay = this.policyRate[0].min_length_stay
                this.form.max_length_stay = this.policyRate[0].max_length_stay
                this.form.policies_cancelation = this.policyRate[0].policies_cancelation
                // this.form.max_occupancy = this.policyRate[0].max_occupancy
                this.form.description = this.policyRate[0].description
                this.form.status = !!this.policyRate[0].status

                let arrayTranslations = this.policyRate[0].translations

                arrayTranslations.forEach((translation) => {
                  this.form.translDesc[translation.language_id] = {
                    id: translation.id,
                    policy_description: translation.value
                  }
                })

                let data = this.policyRate[0].days_apply.split('|')

                for (let i = data.length - 1; i >= 0; i--) {
                  if (data[i] === 'all') {
                    this.days[0].all = true
                    this.days[0].monday = true
                    this.days[0].tuesday = true
                    this.days[0].wednesday = true
                    this.days[0].thursday = true
                    this.days[0].friday = true
                    this.days[0].saturday = true
                    this.days[0].sunday = true
                    break
                  }

                  switch (data[i]) {
                    case '1':
                      this.days[0].monday = true
                      break
                    case '2':
                      this.days[0].tuesday = true
                      break
                    case '3':
                      this.days[0].wednesday = true
                      break
                    case '4':
                      this.days[0].thursday = true
                      break
                    case '5':
                      this.days[0].friday = true
                      break
                    case '6':
                      this.days[0].saturday = true
                      break
                    case '7':
                      this.days[0].sunday = true
                      break
                  }
                }
              }).catch((e) => {
                  console.log(e)
            })
          }

          // this.form = form
        }).catch((e) => {
            console.log(e)

      })

      //penalties
      API.get('/policies_cancelations/selectBox?lang=' + localStorage.getItem('lang') + '&hotel_id=' + this.$route.params.hotel_id)
        .then((result) => {
          //console.log("politica cancelation result")
          //console.log("politica cancelation result")
          //console.log(result)
          let policy = result.data.data
          policy.forEach((policy) => {
            this.policyCancellations.push({
              label: policy.text,
              code: policy.value
            })
          })
        }).catch((e) => {
            console.log(e)
        this.$notify({
          group: 'main',
          type: 'error',
          title: this.$t('hotelsmanagehotelconfiguration.error.messages.name'),
          text: this.$t('hotelsmanagehotelconfiguration.error.messages.connection_error')
        })
      })
    },
    methods: {
      checkboxChecked: function (room_state) {
        return !!room_state
      },
      changeState: function (day, status) {
        if (day === 'all') {
          if (status === false) {
            this.days[0].all = true
            this.days[0].monday = true
            this.days[0].tuesday = true
            this.days[0].wednesday = true
            this.days[0].thursday = true
            this.days[0].friday = true
            this.days[0].saturday = true
            this.days[0].sunday = true
          } else if (status === true) {
            this.days[0].all = false
            this.days[0].monday = false
            this.days[0].tuesday = false
            this.days[0].wednesday = false
            this.days[0].thursday = false
            this.days[0].friday = false
            this.days[0].saturday = false
            this.days[0].sunday = false
          }
        } else {
          this.days[0][day] = !status
          this.days[0].all = false
        }
      },
      changes: function () {
        return status !== true
      },
      addDays: function () {
        let value = []
        let keys = Object.keys(this.days[0])
        for (let i = 0; i < keys.length; i++) {
          if (this.days[0][keys[i]] === true) {
            if (keys[i] === 'all') {
              value.push('all')
              break
            } else {
              value.push(i)
            }
          }
        }
        this.form.select_day = value.join('|')
      },
      close () {
        this.$emit('changeStatus', false)
      },
      validateBeforeSubmit () {

        this.addDays()

        if (this.form.select_day === '') {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('hotelsmanagehotelconfiguration.policy_rate'),
            text: this.$t('hotelsmanagehotelconfiguration.error.messages.select_day')
          })
          return false
        }

        if ((this.form.policies_cancelation.length == 0 && this.form.action == 'post')) {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('hotelsmanagehotelconfiguration.policy_rate'),
            text: this.$t('hotelsmanagehotelconfiguration.error.messages.select_policy_incorrect')
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
              title: this.$t('hotelsmanagehotelconfiguration.policy_rate'),
              text: this.$t('hotelsmanagehotelconfiguration.error.messages.information_incomplete_policy_tarifa')
            })

            this.loading = false
          }
        })
      },
      submit () {
        this.loading = true

        API({
          method: this.form.action,
          url: 'policies_rates/' + (this.form.id !== null ? this.form.id : ''),
          data: this.form
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
          }).catch((e) => {
              console.log(e)
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('hotelsmanagehotelconfiguration.error.messages.name'),
            text: this.$t('hotelsmanagehotelconfiguration.error.messages.connection_error')
          })
        })
      }
    }
  }
</script>

<style lang="stylus">

</style>
<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>
