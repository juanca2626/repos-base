<template>
    <div class="row">

        <div class="col-xs-12 col-lg-12">
            <b-tabs>

                <b-tab :title="$t('packagesmanagepackagefixedoutputs.fixed')" active>

                    <form @submit.prevent="validateBeforeSubmit" data-vv-scope="form-fixed-output">
                        <div class="form-row">
                            <label class="col-sm-2 col-form-label">{{ $t('packagesmanagepackagefixedoutputs.date')
                                }}</label>
                            <div class="col-3">
                                <div class="form-group" id="input-group-1" role="group">
                                    <div class="col-sm-12 p-0">

                                        <date-picker
                                                :config="datePickerOptions"
                                                id="date"
                                                name="date" ref="datePicker"
                                                v-model="form.date" v-validate="'required'"
                                        >
                                        </date-picker>

                                        <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                            <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                               style="margin-left: 5px;"
                                                               v-show="errors.has('form.date')"/>
                                            <span v-show="errors.has('date')">{{ errors.first('date') }}</span>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <label class="col-sm-2 col-form-label">{{ $t('packagesmanagepackagefixedoutputs.room')
                                }}</label>
                            <div class="col-3">
                                <input class="form-control" id="room" max="10000" min="1" name="room"
                                       type="number" v-model.number="form.room"
                                       v-validate="'required'">
                                <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                    <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                       style="margin-left: 5px;"
                                                       v-show="errors.has('room')"/>
                                    <span v-show="errors.has('room')">{{ errors.first('room') }}</span>
                                </div>
                            </div>
                            <div class="col-2" style="text-align: right;">
                                <img src="/images/loading.svg" v-if="loading" width="40px"/>
                                <button @click="validateBeforeSubmit" class="btn btn-success" type="submit"
                                        v-if="!loading">
                                    <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                                    {{ $t('global.buttons.add') }}
                                </button>
                            </div>

                        </div>

                    </form>

                    <table-server :columns="table.columns" :options="tableOptions" :url="urlfixedoutputs"
                                  class="text-center" ref="table">

                        <div class="table-state" slot="state" slot-scope="props" style="font-size: 0.9em">
                            <b-form-checkbox
                                    :checked="checkboxChecked(props.row.state)"
                                    :id="'checkbox_'+props.row.id"
                                    :name="'checkbox_'+props.row.id"
                                    @change="changeState(props.row.id,props.row.state)"
                                    switch>
                            </b-form-checkbox>
                        </div>

                        <div class="table-actions" slot="actions" slot-scope="props" style="margin-top: 10px">
                            <button @click="showModal(props.row.id)" class="btn btn-danger" type="button"
                                    v-if="$can('delete', 'fixedoutputs')">
                                <font-awesome-icon :icon="['fas', 'trash']"/>
                            </button>
                        </div>
                    </table-server>

                </b-tab>

                <b-tab :title="$t('packagesmanagepackagefixedoutputs.ranges')">

                    <div class="form-row">
                        <table class="col-12 VueTables__table table table-striped table-bordered table-hover text-center schedules">
                            <thead>
                            <tr>
                                <th scope="col">{{ $t('packagesmanagepackagefixedoutputs.all') }}</th>
                                <th scope="col">{{ $t('packagesmanagepackagefixedoutputs.monday') }}</th>
                                <th scope="col">{{ $t('packagesmanagepackagefixedoutputs.tuesday') }}</th>
                                <th scope="col">{{ $t('packagesmanagepackagefixedoutputs.wednesday') }}</th>
                                <th scope="col">{{ $t('packagesmanagepackagefixedoutputs.thursday') }}</th>
                                <th scope="col">{{ $t('packagesmanagepackagefixedoutputs.friday') }}</th>
                                <th scope="col">{{ $t('packagesmanagepackagefixedoutputs.saturday') }}</th>
                                <th scope="col">{{ $t('packagesmanagepackagefixedoutputs.sunday') }}</th>
                            </tr>
                            </thead>
                            <tr>
                                <td class="text-center">
                                    <b-form-checkbox
                                            :id="'checkbox_all'"
                                            :name="'checkbox_all'"
                                            @change="changeSchedulesAll()"
                                            switch
                                            v-model="package.schedules.all">
                                    </b-form-checkbox>
                                </td>
                                <td class="text-center">
                                    <b-form-checkbox
                                            :checked="checkboxChecked(package.schedules.monday)"
                                            :id="'checkbox_monday'"
                                            :name="'checkbox_monday'"
                                            @change="changeSchedule(package.schedules.monday)"
                                            switch
                                            v-model="package.schedules.monday">
                                    </b-form-checkbox>
                                </td>
                                <td class="text-center">
                                    <b-form-checkbox
                                            :checked="checkboxChecked(package.schedules.tuesday)"
                                            :id="'checkbox_tuesday'"
                                            :name="'checkbox_tuesday'"
                                            @change="changeSchedule(package.schedules.tuesday)"
                                            switch
                                            v-model="package.schedules.tuesday">
                                    </b-form-checkbox>
                                </td>
                                <td class="text-center">
                                    <b-form-checkbox
                                            :checked="checkboxChecked(package.schedules.wednesday)"
                                            :id="'checkbox_wednesday'"
                                            :name="'checkbox_wednesday'"
                                            @change="changeSchedule(package.schedules.wednesday)"
                                            switch
                                            v-model="package.schedules.wednesday">
                                    </b-form-checkbox>
                                </td>
                                <td class="text-center">
                                    <b-form-checkbox
                                            :checked="checkboxChecked(package.schedules.thursday)"
                                            :id="'checkbox_thursday'"
                                            :name="'checkbox_thursday'"
                                            @change="changeSchedule(package.schedules.thursday)"
                                            switch
                                            v-model="package.schedules.thursday">
                                    </b-form-checkbox>
                                </td>
                                <td class="text-center">
                                    <b-form-checkbox
                                            :checked="checkboxChecked(package.schedules.friday)"
                                            :id="'checkbox_friday'"
                                            :name="'checkbox_friday'"
                                            @change="changeSchedule(package.schedules.friday)"
                                            switch
                                            v-model="package.schedules.friday">
                                    </b-form-checkbox>
                                </td>
                                <td class="text-center">
                                    <b-form-checkbox
                                            :checked="checkboxChecked(package.schedules.saturday)"
                                            :id="'checkbox_saturday'"
                                            :name="'checkbox_saturday'"
                                            @change="changeSchedule(package.schedules.saturday)"
                                            switch
                                            v-model="package.schedules.saturday">
                                    </b-form-checkbox>
                                </td>
                                <td class="text-center">
                                    <b-form-checkbox
                                            :checked="checkboxChecked(package.schedules.sunday)"
                                            :id="'checkbox_sunday'"
                                            :name="'checkbox_sunday'"
                                            @change="changeSchedule(package.schedules.sunday)"
                                            switch
                                            v-model="package.schedules.sunday">
                                    </b-form-checkbox>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <form @submit.prevent="validateBeforeSubmitSchedule" data-vv-scope="form-schedule">
                        <div class="form-row">
                            <div class="col-2">
                                <label class="col-sm-12 col-form-label">{{ $t('packagesmanagepackagefixedoutputs.from')
                                    }} - {{ $t('packagesmanagepackagefixedoutputs.to') }}</label>
                            </div>
                            <div class="col-2">
                                <div class="input-group col-12">
                                    <date-picker
                                            :config="datePickerFromOptions"
                                            @dp-change="setDateFrom"
                                            id="date_from"
                                            name="date_from" ref="datePickerFrom"
                                            v-model="date_from" v-validate="'required'"
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

                            <div class="col-2">
                                <div class="input-group col-12">
                                    <date-picker
                                            :config="datePickerToOptions"
                                            id="date_to"
                                            name="date_to" ref="datePickerTo"
                                            v-model="date_to" v-validate="'required'">
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

                            <div class="col-2">
                                <label class="col-sm-12 col-form-label">{{ $t('packagesmanagepackagefixedoutputs.room')
                                    }}</label>
                            </div>

                            <div class="col-2">
                                <input class="form-control" id="room_range" max="10000" min="1" name="room_range"
                                       type="number" v-model.number="room_range"
                                       v-validate="'required'">
                                <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                    <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                       style="margin-left: 5px;"
                                                       v-show="errors.has('room_range')"/>
                                    <span v-show="errors.has('room_range')">{{ errors.first('room_range') }}</span>
                                </div>
                            </div>

                            <div class="col-2" style="text-align: right;">
                                <img src="/images/loading.svg" v-if="loading" width="40px"/>
                                <button @click="validateBeforeSubmitSchedule" class="btn btn-success" type="submit"
                                        v-if="!loading">
                                    <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                                    {{ $t('global.buttons.add') }}
                                </button>
                            </div>

                        </div>

                    </form>

                    <table-client :columns="tableSchedules.columns" :data="schedules" :loading="loading"
                                  :options="tableOptionsSchedule" id="dataTableSchedules"
                                  style="margin-top: 30px;" theme="bootstrap4">

                        <div class="table-monday day" slot="monday" slot-scope="props">
                            <font-awesome-icon :icon="['fas', 'check-circle']" class="day-success"
                                               v-if="props.row.monday == '1'"/>
                            <font-awesome-icon :icon="['fas', 'times']" class="day-danger"
                                               v-if="props.row.monday == '0'"/>
                        </div>

                        <div class="table-tuesday day" slot="tuesday" slot-scope="props">
                            <font-awesome-icon :icon="['fas', 'check-circle']" class="day-success"
                                               v-if="props.row.tuesday == '1'"/>
                            <font-awesome-icon :icon="['fas', 'times']" class="day-danger"
                                               v-if="props.row.tuesday == '0'"/>
                        </div>

                        <div class="table-wednesday day" slot="wednesday" slot-scope="props">
                            <font-awesome-icon :icon="['fas', 'check-circle']" class="day-success"
                                               v-if="props.row.wednesday == '1'"/>
                            <font-awesome-icon :icon="['fas', 'times']" class="day-danger"
                                               v-if="props.row.wednesday == '0'"/>
                        </div>

                        <div class="table-thursday day" slot="thursday" slot-scope="props">
                            <font-awesome-icon :icon="['fas', 'check-circle']" class="day-success"
                                               v-if="props.row.thursday == '1'"/>
                            <font-awesome-icon :icon="['fas', 'times']" class="day-danger"
                                               v-if="props.row.thursday == '0'"/>
                        </div>

                        <div class="table-friday day" slot="friday" slot-scope="props">
                            <font-awesome-icon :icon="['fas', 'check-circle']" class="day-success"
                                               v-if="props.row.friday == '1'"/>
                            <font-awesome-icon :icon="['fas', 'times']" class="day-danger"
                                               v-if="props.row.friday == '0'"/>
                        </div>

                        <div class="table-saturday day" slot="saturday" slot-scope="props">
                            <font-awesome-icon :icon="['fas', 'check-circle']" class="day-success"
                                               v-if="props.row.saturday == '1'"/>
                            <font-awesome-icon :icon="['fas', 'times']" class="day-danger"
                                               v-if="props.row.saturday == '0'"/>
                        </div>

                        <div class="table-sunday day" slot="sunday" slot-scope="props">
                            <font-awesome-icon :icon="['fas', 'check-circle']" class="day-success"
                                               v-if="props.row.sunday == '1'"/>
                            <font-awesome-icon :icon="['fas', 'times']" class="day-danger"
                                               v-if="props.row.sunday == '0'"/>
                        </div>

                        <div class="table-state" slot="state" slot-scope="props" style="font-size: 0.9em">
                            <b-form-checkbox
                                    :checked="checkboxChecked(props.row.state)"
                                    :id="'checkbox_schedule_'+props.row.id"
                                    :name="'checkbox_schedule_'+props.row.id"
                                    @change="updateStatusSchedule(props.row.id,props.row.state)"
                                    switch>
                            </b-form-checkbox>
                        </div>

                        <div class="table-actions" slot="actions" slot-scope="props" style="margin-top: 10px">
                            <button @click="showModalSchedule(props.row.id)" class="btn btn-danger" type="button"
                                    v-if="$can('delete', 'packageschedules')">
                                <font-awesome-icon :icon="['fas', 'trash']"/>
                            </button>
                        </div>
                        <div class="table-loading text-center" slot="loading">
                            <img alt="loading" height="51px" src="/images/loading.svg"/>
                        </div>
                    </table-client>

                </b-tab>
            </b-tabs>
        </div>

        <b-modal :title="fixedOutputName" centered ref="my-modal" size="sm">
            <p class="text-center">{{$t('global.message_delete')}}</p>

            <div slot="modal-footer">
                <button @click="remove()" class="btn btn-success">{{$t('global.buttons.accept')}}</button>
                <button @click="hideModal()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
            </div>
        </b-modal>

        <b-modal :title="scheduleName" centered ref="my-modal-schedule" size="sm">
            <p class="text-center">{{$t('global.message_delete')}}</p>

            <div slot="modal-footer">
                <button @click="removeSchedule()" class="btn btn-success">{{$t('global.buttons.accept')}}</button>
                <button @click="hideModal()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
            </div>
        </b-modal>

    </div>
</template>
<script>
  import TableServer from '../../../../components/TableServer'
  import TableClient from './.././../../../components/TableClient'
  import { API } from '../../../../api'
  import datePicker from 'vue-bootstrap-datetimepicker'
  import BFormCheckbox from 'bootstrap-vue/es/components/form-checkbox/form-checkbox'
  import BDropDown from 'bootstrap-vue/es/components/dropdown/dropdown'
  import BDropDownItemButton from 'bootstrap-vue/es/components/dropdown/dropdown-item-button'
  import BModal from 'bootstrap-vue/es/components/modal/modal'
  import vSelect from 'vue-select'
  import 'vue-select/dist/vue-select.css'

  export default {
    components: {
      datePicker,
      'table-server': TableServer,
      'table-client': TableClient,
      BFormCheckbox,
      'b-dropdown': BDropDown,
      'b-dropdown-item-button': BDropDownItemButton,
      BModal,
      vSelect
    },
    data: () => {
      return {
        loading:false,
        room_range: '',
        date_from: '',
        date_to: '',
        datePickerOptions: {
          format: 'DD/MM/YYYY',
          useCurrent: false,
          locale: localStorage.getItem('lang')
        },
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
        schedules: [],
        package: {
          schedules: {
            monday: true,
            tuesday: true,
            wednesday: true,
            thursday: true,
            friday: true,
            saturday: true,
            sunday: true,
            all: true,
            id: ''
          }
        },
        fixed_output_id: '',
        schedule_id: '',
        fixedOutputName: '',
        scheduleName: '',
        urlfixedoutputs: '',
        table: {
          columns: ['id', 'date', 'room', 'state', 'actions']
        },
        tableSchedules: {
          columns: ['id', 'date_from', 'date_to', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday', 'room', 'state', 'actions']
        },
        form:{
          package_id:null,
          date: '',
          room: 0
        }
      }
    },
    mounted () {
      this.$i18n.locale = localStorage.getItem('lang')
      this.fetchData()
    },
    created () {
      this.form.package_id = this.$route.params.package_id
      this.urlfixedoutputs = '/api/package/' + this.$route.params.package_id + '/fixed_outputs?token=' +
        window.localStorage.getItem('access_token') + '&lang=' +
        localStorage.getItem('lang')
      this.$parent.$parent.$on('langChange', (payload) => {
        this.onUpdate()
      })
    },
    computed: {
      tableOptions: function () {
        return {
          headings: {
            id: 'ID',
            date: this.$i18n.t('packagesmanagepackagefixedoutputs.date'),
            room: this.$i18n.t('packagesmanagepackagefixedoutputs.room'),
            state: this.$i18n.t('global.state'),
            actions: this.$i18n.t('global.table.actions')
          },
          sortable: ['id'],
          filterable: false
        }
      },
      tableOptionsSchedule: function () {
        return {
          headings: {
            id: 'ID',
            date_from: this.$i18n.t('packagesmanagepackagefixedoutputs.from'),
            date_to: this.$i18n.t('packagesmanagepackagefixedoutputs.to'),
            monday: this.$i18n.t('packagesmanagepackagefixedoutputs.monday'),
            tuesday: this.$i18n.t('packagesmanagepackagefixedoutputs.tuesday'),
            wednesday: this.$i18n.t('packagesmanagepackagefixedoutputs.wednesday'),
            thursday: this.$i18n.t('packagesmanagepackagefixedoutputs.thursday'),
            friday: this.$i18n.t('packagesmanagepackagefixedoutputs.friday'),
            saturday: this.$i18n.t('packagesmanagepackagefixedoutputs.saturday'),
            sunday: this.$i18n.t('packagesmanagepackagefixedoutputs.sunday'),
            room: this.$i18n.t('packagesmanagepackagefixedoutputs.room'),
            state: this.$i18n.t('global.state'),
            actions: this.$i18n.t('global.table.actions')
          },
          sortable: ['id'],
          filterable: false
        }
      }
    },
    methods: {
      setDateFrom (e) {
        this.$refs.datePickerTo.dp.minDate(e.date)
      },
      updateStatusSchedule: function (schedule_id, state) {
        API({
          method: 'put',
          url: 'package/' + this.$route.params.package_id + '/schedules/' + schedule_id + '/state',
          data: { state: state }
        })
          .then((result) => {
            if (result.data.success === true) {
              console.log(result)
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: 'Error: ' + this.$t('packagesmanagepackagefixedoutputs.schedule'),
                text: this.$t('packagesmanagepackagefixedoutputs.error.messages.system')
              })
            }
          })
      },
      changeSchedulesAll: function () {
        this.package.schedules.monday = !(this.package.schedules.all)
        this.package.schedules.tuesday = !(this.package.schedules.all)
        this.package.schedules.wednesday = !(this.package.schedules.all)
        this.package.schedules.thursday = !(this.package.schedules.all)
        this.package.schedules.friday = !(this.package.schedules.all)
        this.package.schedules.saturday = !(this.package.schedules.all)
        this.package.schedules.sunday = !(this.package.schedules.all)
      },
      checkboxChecked: function (check) {

        if (!(isNaN(parseInt(check)))) {
          check = parseInt(check)
        }

        if (check) {
          return 'true'
        } else {
          return 'false'
        }
      },
      changeSchedule: function () {
        console.log(this.package.schedules)
        var self = this
        setTimeout(function () {
          let n = 0
          if (self.package.schedules.monday == false) { n++ }
          if (self.package.schedules.tuesday == false) { n++ }
          if (self.package.schedules.wednesday == false) { n++ }
          if (self.package.schedules.thursday == false) { n++ }
          if (self.package.schedules.friday == false) { n++ }
          if (self.package.schedules.saturday == false) { n++ }
          if (self.package.schedules.sunday == false) { n++ }
          self.package.schedules.all = (n == 0) ? true : false
        }, 300)
      },
      validateBeforeSubmit: function () {
        this.$validator.validateAll('form-fixed-output').then((result) => {
          if (result) {
            this.form.package_id = this.$route.params.package_id
            this.submit()

          } else {
            this.$notify({
              group: 'main',
              type: 'error',
              title: 'Error: ' + this.$t('packagesmanagepackagefixedoutputs.fixed_output'),
              text: this.$t('packagesmanagepackagefixedoutputs.error.messages.system')
            })

            this.loading = false
          }
        })
      },
      validateBeforeSubmitSchedule: function () {

        if (!(this.package.schedules.all)) {
          this.$notify({
            group: 'main',
            type: 'error',
            title: 'Error: ' + this.$t('packagesmanagepackagefixedoutputs.schedule'),
            text: this.$t('packagesmanagepackagefixedoutputs.error.messages.schedule_null')
          })

          this.loading = false
        }

        this.$validator.validateAll('form-schedule').then((result) => {
          if (result) {
            this.form.package_id = this.$route.params.package_id
            this.submitSchedule()

          } else {
            this.$notify({
              group: 'main',
              type: 'error',
              title: 'Error: ' + this.$t('packagesmanagepackagefixedoutputs.schedule'),
              text: this.$t('packagesmanagepackagefixedoutputs.error.messages.system')
            })

            this.loading = false
          }
        })
      },
      showModal (fixed_output_id) {
        this.fixed_output_id = fixed_output_id
        this.fixedOutputName = this.$t('packagesmanagepackagefixedoutputs.fixed_output') + ' n°: ' +
          this.fixed_output_id
        this.$refs['my-modal'].show()
      },
      showModalSchedule (schedule_id) {
        this.schedule_id = schedule_id
        this.scheduleName = this.$t('packagesmanagepackagefixedoutputs.schedule') + ' n°: ' + this.schedule_id
        this.$refs['my-modal-schedule'].show()
      },
      hideModal () {
        this.$refs['my-modal'].hide()
        this.$refs['my-modal-schedule'].hide()
      },
      changeState: function (fixed_output_id, state) {
        API({
          method: 'put',
          url: 'package/' + this.form.package_id + '/fixed_outputs/' + fixed_output_id + '/state',
          data: { state: state }
        })
          .then((result) => {
            if (result.data.success === true) {
              this.onUpdate()

            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: 'Error: ' + this.$t('packagesmanagepackagefixedoutputs.fixed_output'),
                text: this.$t('packagesmanagepackagefixedoutputs.error.messages.system')
              })
            }
          })
      },
      onUpdate () {
        this.urlfixedoutputs = '/api/package/' + this.$route.params.package_id + '/fixed_outputs?token=' +
          window.localStorage.getItem('access_token') + '&lang=' +
          localStorage.getItem('lang')
        this.$refs.table.$refs.tableserver.refresh()
      },
      remove: function () {

        API({
          method: 'DELETE',
          url: '/package/' + this.$route.params.package_id + '/fixed_outputs/' + this.fixed_output_id
        })
          .then((result) => {
            if (result.data.success === true) {
              this.onUpdate()
              this.hideModal()
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: 'Error: ' + this.$t('packagesmanagepackagefixedoutputs.fixed_output'),
                text: this.$t('packagesmanagepackagefixedoutputs.error.messages.system')
              })
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: 'Error: ' + this.$t('packagesmanagepackagefixedoutputs.fixed_output'),
            text: this.$t('packagesmanagepackagefixedoutputs.error.messages.connection_error')
          })
        })
      },
      removeSchedule: function () {

        API({
          method: 'DELETE',
          url: '/package/' + this.$route.params.package_id + '/schedules/' + this.schedule_id
        })
          .then((result) => {
            if (result.data.success === true) {
              this.fetchData()
              this.hideModal()
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: 'Error: ' + this.$t('packagesmanagepackagefixedoutputs.schedule'),
                text: this.$t('packagesmanagepackagefixedoutputs.error.messages.system')
              })
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: 'Error: ' + this.$t('packagesmanagepackagefixedoutputs.schedule'),
            text: this.$t('packagesmanagepackagefixedoutputs.error.messages.connection_error')
          })
        })
      },
      submit: function () {

        this.loading = true
        API({
          method: 'post',
          url: 'package/' + this.form.package_id + '/fixed_outputs',
          data: this.form
        })
          .then((result) => {
            if (result.data.success === true) {
              this.onUpdate()
              this.loading = false
              this.form.room = 0
              this.form.date = ''
              this.errors.items = []
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: 'Error: ' + this.$t('packagesmanagepackagefixedoutputs.fixed_output'),
                text: this.$t('packagesmanagepackagefixedoutputs.error.messages.system')
              })

              this.loading = false
            }
          })
      },
      submitSchedule: function () {

        this.loading = true
        let data = this.package.schedules
        data.room = this.room_range
        data.date_from = this.date_from
        data.date_to = this.date_to

        API({
          method: 'post',
          url: 'package/' + this.form.package_id + '/schedules',
          data: data
        })
          .then((result) => {
            if (result.data.success === true) {
              this.fetchData()
              this.loading = false
              this.room_range = ''
              this.date_from = ''
              this.date_to = ''
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: 'Error: ' + this.$t('packagesmanagepackagefixedoutputs.schedule'),
                text: this.$t('packagesmanagepackagefixedoutputs.error.messages.system')
              })

              this.loading = false
            }
          })
      },
      fetchData: function () {
        this.loading = true
        // Packages schedules
        API.get('/package/' + this.$route.params.package_id + '/schedules')
          .then((result) => {
            this.loading = false
            this.schedules = result.data.data
          })
          .catch(() => {
            this.$notify({
              group: 'main',
              type: 'error',
              title: 'Fetch Error',
              text: 'Cannot load data'
            })
          })

      }
    }
  }
</script>
<style>
    .custom-control-input:checked ~ .custom-control-label::before {
        border-color: #3ea662;
        background-color: #3a9d5d;
    }
    .v-select input{
        height: 25px;
    }

    .schedules td {
        height: 45px;
    }

    .day {
        margin-top: 10px;
        text-align: center
    }

    .day-danger {
        color: #8d0a0d;
    }

    .day-success {
        color: #3a9d5d;
    }
</style>


