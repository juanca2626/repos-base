<template>
    <div class="container">
        <loading :active.sync="loading" :can-cancel="false" color="#BD0D12"></loading>
        <div class="vld-parent">
            <div class="col-md-6 p-0 mb-4">
                <label>Tarifas</label>
                <div class="col-md-12 p-0">
                    <select @change="changeRatePlan()" class="form-control" id="plan_rates" name="plan_rates"
                            v-model="planRateSelected">
                        <option :value="plan.code" v-for="plan in plan_rates">{{ plan.label }}</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <!-- Formulario asignar por rango de fechas -->
                <div class="col-md-2" v-if="showRangeDates">
                    <label>{{ $t('packagesquote.from') }}</label>
                    <div class="input-group mb-3">
                        <date-picker
                            :config="datePickerFromOptions"
                            @dp-change="setDateFrom"
                            ref="datePickerFrom"
                            id="dates_from"
                            name="dates_from"
                            data-vv-as="fecha"
                            data-vv-name="dates_from"
                            data-vv-scope="formProcessInventory"
                            v-model="form.dates_from" v-validate="'required'">
                        </date-picker>
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon3">
                                <i class="far fa-calendar"></i>
                            </span>
                        </div>
                        <span class="invalid-feedback-select" v-show="errors.has('formProcessInventory.dates_from')">
                                            <span>{{ errors.first('formProcessInventory.dates_from') }}</span>
                        </span>
                    </div>

                </div>
                <div class="col-md-2" v-if="showRangeDates">
                    <label>{{ $t('packagesquote.to') }}</label>
                    <div class="input-group mb-3">
                        <date-picker
                            :config="datePickerToOptions"
                            ref="datePickerTo"
                            id="dates_to"
                            name="dates_to"
                            data-vv-as="fecha"
                            data-vv-name="dates_to"
                            data-vv-scope="formProcessInventory"
                            v-model="form.dates_to" v-validate="'required'">
                        </date-picker>
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon1">
                                <i class="far fa-calendar"></i>
                            </span>
                        </div>
                        <span class="invalid-feedback-select" v-show="errors.has('formProcessInventory.dates_to')">
                            <span>{{ errors.first('formProcessInventory.dates_to') }}</span>
                        </span>
                    </div>

                </div>
                <div class="col-md-2" v-if="showRangeDates">
                    <label>{{ $t('packagesmanageavailability.avail') }}</label>
                    <div class="col-sm-12 p-0">
                        <input id="availability"
                               data-vv-as="disponibilidad"
                               data-vv-name="availability"
                               data-vv-scope="formProcessInventory"
                               name="availability"
                               type="text"
                               class="form-control"
                               v-model="form.availability" v-validate="'required|numeric'">
                        <span class="invalid-feedback" v-show="errors.has('formProcessInventory.availability')">
                                            <span>{{ errors.first('formProcessInventory.availability') }}</span>
                        </span>
                    </div>
                </div>
                <div class="col-md-2" v-if="showRangeDates">
                    <label>&nbsp;</label>
                    <div class="col-sm-12 p-0">
                        <button @click="processInventory()" class="btn btn-success btn-block">{{
                            $t('packagesmanageavailability.assign') }}
                        </button>
                    </div>
                </div>
                <div class="col-md-2" v-if="showRangeDates">
                    <label>&nbsp;</label>
                    <div class="col-sm-12 p-0">
                        <button @click="cancelRange()" class="btn btn-danger btn-block">{{ $t('global.buttons.cancel')
                            }}
                        </button>
                    </div>
                </div>
                <!-- Formulario asignar por seleccion de dias -->
                <div class="col-md-2" v-if="!showRangeDates && !showRangeDatesBlock">
                    <label>{{ $t('packagesmanageavailability.avail') }}</label>
                    <div class="col-sm-12 p-0">
                        <input id="inventory_num"
                               data-vv-as="disponibilidad"
                               data-vv-name="inventory_num"
                               data-vv-scope="formAddInventory"
                               name="inventory_num"
                               type="text"
                               class="form-control"
                               v-model="inventory_num" v-validate="'required|numeric'">
                        <span class="invalid-feedback" v-show="errors.has('formAddInventory.inventory_num')">
                                            <span>{{ errors.first('formAddInventory.inventory_num') }}</span>
                        </span>
                    </div>
                </div>
                <div class="col-md-2" v-if="!showRangeDates && !showRangeDatesBlock">
                    <label>&nbsp;</label>
                    <div class="col-sm-12 p-0">
                        <button @click="addInventory()" class="btn btn-success btn-block">{{
                            $t('packagesmanageavailability.assign') }}
                        </button>
                    </div>
                </div>
                <div class="col-md-3" v-if="!showRangeDates && !showRangeDatesBlock">
                    <label class="col-sm-12 p-0">&nbsp;</label>
                    <div class="btn-group" role="group" aria-label="Basic example">
                        <button type="button" @click="enabledDays()" class="btn btn-success">{{
                            $t('packagesmanageavailability.unblock') }}
                        </button>
                        <button type="button" @click="lockedDays()" class="btn btn-danger">{{
                            $t('packagesmanageavailability.block') }}
                        </button>
                    </div>
                </div>

                <!-- Formulario bloquear por rango de fechas -->
                <div class="col-md-2" v-if="showRangeDatesBlock">
                    <label>{{ $t('packagesquote.from') }}</label>
                    <div class="input-group mb-3">
                        <date-picker
                            :config="datePickerFromOptions"
                            @dp-change="setDateFrom"
                            ref="datePickerFrom"
                            id="dates_from_block"
                            name="dates_from_block"
                            data-vv-as="fecha"
                            data-vv-name="dates_from_block"
                            data-vv-scope="formProcessBlockInventory"
                            v-model="form.dates_from" v-validate="'required'">
                        </date-picker>
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon4">
                                <i class="far fa-calendar"></i>
                            </span>
                        </div>
                        <span class="invalid-feedback-select"
                              v-show="errors.has('formProcessBlockInventory.dates_from_block')">
                                            <span>{{ errors.first('formProcessBlockInventory.dates_from_block') }}</span>
                        </span>
                    </div>
                </div>
                <div class="col-md-2" v-if="showRangeDatesBlock">
                    <label>{{ $t('packagesquote.to') }}</label>
                    <div class="input-group mb-3">
                        <date-picker
                            :config="datePickerToOptions"
                            ref="datePickerTo"
                            id="dates_to_block"
                            name="dates_to_block"
                            data-vv-as="fecha"
                            data-vv-name="dates_to_block"
                            data-vv-scope="formProcessBlockInventory"
                            v-model="form.dates_to" v-validate="'required'">
                        </date-picker>
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon5">
                                <i class="far fa-calendar"></i>
                            </span>
                        </div>
                        <span class="invalid-feedback-select"
                              v-show="errors.has('formProcessBlockInventory.dates_to_block')">
                            <span>{{ errors.first('formProcessBlockInventory.dates_to_block') }}</span>
                        </span>
                    </div>

                </div>
                <div class="col-md-3" v-if="showRangeDatesBlock">
                    <label class="col-sm-12 p-0">&nbsp;</label>
                    <div class="btn-group" role="group" aria-label="Basic example">
                        <button type="button" @click="blockedInventory(0)" class="btn btn-success">{{
                            $t('packagesmanageavailability.unblock') }}
                        </button>
                        <button type="button" @click="blockedInventory(1)" class="btn btn-danger">{{
                            $t('packagesmanageavailability.block') }}
                        </button>
                    </div>
                </div>
                <div class="col-md-2" v-if="showRangeDatesBlock">
                    <label>&nbsp;</label>
                    <div class="col-sm-12 p-0">
                        <button @click="cancelRangeBlock()" class="btn btn-danger btn-block">{{
                            $t('global.buttons.cancel') }}
                        </button>
                    </div>
                </div>
                <!-- Opciones avanzadas -->
                <div class="col-md-5" v-if="!showRangeDates && !showRangeDatesBlock">
                    <label>&nbsp;</label>
                    <b-dropdown id="dropdown-1" dropleft size="sm" class="pull-right" variant="primary">
                        <template slot="button-content">
                            {{ $t('packagesmanageavailability.advancedoptions') }}
                        </template>
                        <b-dropdown-item @click="showDates">{{ $t('packagesmanageavailability.assignbydates') }}
                        </b-dropdown-item>
                        <b-dropdown-item @click="showDatesBlock">{{ $t('packagesmanageavailability.blockbydates') }}
                        </b-dropdown-item>
                    </b-dropdown>

                </div>
            </div>
            <div class="row">
                <div class="col-md-12 text-center" v-if="showRangeDates || showRangeDatesBlock">
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
                                           style="margin-left: 5px;" />
                        <span>{{ $t('packagesmanageavailability.error.daysincomplete') }}</span>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12" style="margin-top: 20px;">
                    <div class="calendar-service">
                        <ul class="calendar-header">
                            <li class="prev" @click="subtractMonth"><i class="fa fa-fw fa-chevron-left"></i></li>
                            <li class="next" @click="addMonth"><i class="fa fa-fw fa-chevron-right"></i></li>
                            <li>
                                <!--                                <select @change="getChangeMonth()" class="form-control input-group-sm" id="month" name="month" v-model="monthSelected">-->
                                <!--                                    <option :value="month.id" v-for="month in monthsList">{{ $t('months.'+month.name) }}</option>-->
                                <!--                                </select>-->
                                <h5>{{monthName + ' - ' + year}}</h5>
                            </li>
                        </ul>
                        <ul class="weekdays">
                            <li v-for="day in days">
                                <strong>{{day}}</strong>
                            </li>
                        </ul>
                        <ul class="days">
                            <li v-for="blank in firstDayOfMonth">&nbsp;</li>
                            <li :class="{'day_disabled':date.class_locked,
                                        'day_selected':date.class_selected,'day_intermediate':date.class_intermediate}"
                                v-for="(date, day_index) in inventories"
                                @click="editCell(day_index)"
                                @mouseover="selectRow(day_index)">
                                <div
                                    :class="{'active': date.day === initialDate && monthName === initialMonth && year === initialYear}">
                                    <strong>{{date.day}}</strong>
                                </div>
                                <div class="day-qty">
                                   <span class="badge badge-success"
                                         title="Disponible">{{date.inventory_num}}</span> / <span
                                    class="badge badge-info" title="Reserva">{{date.total_booking}}</span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
  import { API } from '../../../../api'
  import Loading from 'vue-loading-overlay'
  import 'vue-loading-overlay/dist/vue-loading.css'
  import datePicker from 'vue-bootstrap-datetimepicker'
  import 'pc-bootstrap4-datetimepicker/build/css/bootstrap-datetimepicker.css'
  import BFormCheckbox from 'bootstrap-vue/es/components/form-checkbox/form-checkbox'
  import moment from 'moment'
  import BDropDown from 'bootstrap-vue/es/components/dropdown/dropdown'
  import BDropDownItemButton from 'bootstrap-vue/es/components/dropdown/dropdown-item-button'
  import vSelect from 'vue-select'
  import 'vue-select/dist/vue-select.css'

  export default {
    components: {
      BDropDown,
      BDropDownItemButton,
      Loading,
      datePicker,
      BFormCheckbox,
      vSelect,
    },
    data: () => {
      return {
        package_plan_rate_id: '',
        inventories: [],
        inventoriesSelected: [],
        planRateSelected: [],
        checked_all_days: false,
        showRangeDates: false,
        showRangeDatesBlock: false,
        loading: false,
        scheduleIndex: 0,
        formAction: 'post',
        scheduleName: '',
        monthSelected: '1',
        today: moment(),
        dateContext: moment(),
        days: ['DO', 'LU', 'MA', 'MI', 'JU', 'VI', 'SA'],
        form: {
          locked: 0,
          availability: '',
          package_plan_rate_id: '',
          dates_from: '',
          dates_to: '',
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
        ctrlActive: false,
        max_position_x: 0,
        state_change: false,
        inventory_num: null,
        errorDay: false,
        errorRangeDates: false,
        month: '',
        monthsList: [
          {
            id: '1',
            name: 'january'
          },
          {
            id: '2',
            name: 'february'
          },
          {
            id: '3',
            name: 'march'
          },
          {
            id: '4',
            name: 'april'
          },
          {
            id: '5',
            name: 'may'
          },
          {
            id: '6',
            name: 'june'
          },
          {
            id: '7',
            name: 'july'
          },
          {
            id: '8',
            name: 'august'
          },
          {
            id: '9',
            name: 'september'
          },
          {
            id: '10',
            name: 'october'
          },
          {
            id: '11',
            name: 'november'
          },
          {
            id: '12',
            name: 'december'
          }
        ],
        plan_rates: []
      }
    },
    mounted () {
      let f = new Date()
      this.month = ((f.getMonth() + 1) < 10) ? '0' + (f.getMonth() + 1) : (f.getMonth() + 1)
      window.addEventListener('mousedown', function (e) {
        this.ctrlActive = true
        this.state_change = true
      }.bind(this))
      window.addEventListener('mouseup', function (e) {
        this.ctrlActive = false
        this.state_change = false
        this.max_position_x = 0
      }.bind(this))
      this.loadPlanRates()
    },
    computed: {
      year: function () {
        var t = this
        return t.dateContext.format('Y')
      },
      monthName: function () {
        var t = this
        let f = new Date()
        this.monthSelected = f.getMonth() + 1
        return t.dateContext.format('MMMM')
      },
      currentDate: function () {
        var t = this
        return t.dateContext.get('date')
      },
      firstDayOfMonth: function () {
        var t = this
        var firstDay = moment(t.dateContext).subtract((t.currentDate - 1), 'days')
        return firstDay.day()
      },
      initialDate: function () {
        var t = this
        return t.today.get('date')
      },
      initialMonth: function () {
        var t = this
        return t.today.format('MMMM')
      },
      initialYear: function () {
        var t = this
        return t.today.format('Y')
      },
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
      addMonth: function () {
        var t = this
        this.inventoriesSelected = []
        t.dateContext = moment(t.dateContext).add(1, 'month')
        this.month = ((t.dateContext.month() + 1) < 10) ? '0' + (t.dateContext.month() + 1) : (t.dateContext.month() + 1)
        console.log(this.month, this.year)
        this.getInventory(this.package_plan_rate_id)
      },
      subtractMonth: function () {
        var t = this
        this.inventoriesSelected = []
        t.dateContext = moment(t.dateContext).subtract(1, 'month')
        this.month = ((t.dateContext.month() + 1) < 10) ? '0' + (t.dateContext.month() + 1) : (t.dateContext.month() + 1)
        console.log(this.month, this.year)
        this.getInventory(this.package_plan_rate_id)
      },
      selectRow: function (day_index) {
        if (!this.showRangeDatesBlock && !this.showRangeDates) {
          if (this.ctrlActive) {
            if (this.state_change) {
              this.max_position_x = parseInt(day_index)
              this.selectArea()
            } else {
              this.max_position_x = parseInt(day_index)
              this.state_change = true
              this.selectArea()
            }
          } else {
            this.max_position_x = parseInt(day_index)
          }
        }
      },
      selectArea: function () {
        let i = this.max_position_x
        if (this.inventories[i]['selected']) {
          if (this.inventories[i]['locked']) {
            this.inventories[i]['class_locked'] = false
            this.inventories[i]['class_intermediate'] = true
          } else {
            this.inventories[i]['class_selected'] = true
          }
        } else {
          this.inventories[i]['selected'] = true
          this.inventories[i]['class_selected'] = true
          if (this.inventories[i]['locked']) {
            this.inventories[i]['class_locked'] = false
            this.inventories[i]['class_intermediate'] = true
            this.inventories[i]['class_selected'] = false
          } else {
            this.inventories[i]['class_intermediate'] = false
            this.inventories[i]['class_locked'] = false
          }
        }
      },
      editCell: function (day_index) {
        this.clearSelection()
        if (!this.showRangeDatesBlock && !this.showRangeDates) {
          this.inventories[day_index].selected = true
          this.inventories[day_index]['selected'] = true
          this.inventories[day_index]['class_selected'] = true
          if (this.inventories[day_index]['locked']) {
            this.inventories[day_index]['class_locked'] = false
            this.inventories[day_index]['class_intermediate'] = true
            this.inventories[day_index]['class_selected'] = false
          } else {
            this.inventories[day_index]['class_intermediate'] = false
            this.inventories[day_index]['class_locked'] = false
          }
        }

      },
      clearSelection: function () {
        for (let i = 0; i < this.inventories.length; i++) {
          if (this.inventories[i].selected) {
            this.inventories[i].selected = false
            this.inventories[i].class_selected = false
            if (this.inventories[i].locked) {
              this.inventories[i].class_locked = true
              this.inventories[i].class_normal = false
              this.inventories[i].class_intermediate = false
            } else {
              this.inventories[i].class_locked = false
              this.inventories[i].class_normal = true
              this.inventories[i].class_intermediate = false
            }
          }

        }
        this.max_position_x = 0
      },
      getInventory: function (package_plan_rate_id) {
        API({
          method: 'post',
          url: 'package/inventory',
          data: {
            month: this.month,
            year: this.year,
            package_plan_rate_id: package_plan_rate_id,
            lang: localStorage.getItem('lang')
          }
        }).then((result) => {
          if (result.data.success === true) {
            this.inventories = result.data.inventories
          } else {
            this.$notify({
              group: 'main',
              type: 'error',
              title: this.$t('global.global.modules.availability'),
              text: this.$t('global.error.messages.connection_error')
            })
          }
        }).catch((e) => {
          console.log(e)
        })
      },
      addInventory: function () {
        this.$validator.validateAll('formAddInventory').then((result) => {
          if (result) {
            for (let i = 0; i < this.inventories.length; i++) {
              if (this.inventories[i].selected) {
                this.inventories[i].inventory_num = this.inventory_num
                this.inventoriesSelected.push(this.inventories[i])
              }
            }
            if (this.inventoriesSelected.length === 0) {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.availability'),
                text: this.$t('global.error.information_complete')
              })
            } else {
              this.loading = true
              this.storeInventory()
            }
          } else {
            this.$notify({
              group: 'main',
              type: 'error',
              title: this.$t('global.modules.availability'),
              text: this.$t('global.error.information_complete')
            })
          }
        })
      },
      storeInventory: function () {
        API({
          method: 'post',
          url: 'package/inventory/add',
          data: { package_plan_rate_id: this.package_plan_rate_id, inventories_selected: this.inventoriesSelected }
        })
          .then((result) => {
            this.loading = false
            if (result.data.success === true) {
              this.inventoriesSelected = []
              this.getInventory(this.package_plan_rate_id)
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.availability'),
                text: this.$t('global.error.information_error')
              })
            }
          })
      },
      lockedDays: function () {
        for (let i = 0; i < this.inventories.length; i++) {
          if (this.inventories[i].selected) {
            this.inventoriesSelected.push(this.inventories[i])
          }
        }
        if (this.inventoriesSelected.length === 0) {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('global.modules.availability'),
            text: this.$t('global.error.information_complete')
          })
        } else {
          this.loading = true
          this.storeInventoryLockedDays()
        }

      },
      storeInventoryLockedDays: function () {
        API({
          method: 'post',
          url: 'package/inventory/locked/days',
          data: { inventories_selected: this.inventoriesSelected }
        })
          .then((result) => {
            this.loading = false
            if (result.data.success === true) {
              this.inventoriesSelected = []
              this.getInventory(this.package_plan_rate_id)
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.availability'),
                text: this.$t('global.error.information_complete')
              })
            }
          })
      },
      enabledDays: function () {
        for (let i = 0; i < this.inventories.length; i++) {
          if (this.inventories[i].selected) {
            this.inventoriesSelected.push(this.inventories[i])
          }
        }
        if (this.inventoriesSelected.length === 0) {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('global.modules.availability'),
            text: this.$t('global.error.information_complete')
          })
        } else {
          this.loading = true
          this.storeInventoryEnabledDays()
        }

      },
      storeInventoryEnabledDays: function () {
        API({
          method: 'post',
          url: 'package/inventory/enabled/days',
          data: { inventories_selected: this.inventoriesSelected }
        })
          .then((result) => {
            this.loading = false
            if (result.data.success === true) {
              this.inventoriesSelected = []
              this.getInventory(this.package_plan_rate_id)
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.availability'),
                text: this.$t('global.error.information_error')
              })
            }
          })
      },
      processInventory: function () {
        this.$validator.validateAll('formProcessInventory').then((result) => {
          console.log(result)
          if (result) {
            if (this.form.dates_from === '' || this.form.dates_to === '') {
              this.errorRangeDates = true
            } else {
              if (!this.validateSelectedDays()) {
                this.errorDay = true
              } else {
                this.loading = true
                this.form.package_plan_rate_id = this.package_plan_rate_id
                API({
                  method: 'post',
                  url: 'package/inventory/store/range/days',
                  data: this.form
                }).then((result) => {
                  this.loading = false
                  if (result.data.success === true) {
                    this.checked_all_days = false
                    this.form.availability = ''
                    this.unSelectAllDays()
                    this.getInventory(this.package_plan_rate_id)
                  } else {
                    this.$notify({
                      group: 'main',
                      type: 'error',
                      title: this.$t('global.modules.availability'),
                      text: this.$t('global.error.information_error')
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
            this.form.package_plan_rate_id = this.package_plan_rate_id
            API({
              method: 'post',
              url: 'package/inventory/blocked/range/days',
              data: this.form
            })
              .then((result) => {
                if (result.data.success === true) {
                  this.loading = false
                  this.checked_all_days = false
                  this.form.availability = ''
                  this.unSelectAllDays()
                  this.getInventory(this.package_plan_rate_id)
                } else {
                  this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('global.modules.availability'),
                    text: this.$t('global.error.information_error')
                  })
                }
              })

          }
        }
      },
      showDates: function () {
        this.showRangeDates = true
        this.clearSelection()
      },
      showDatesBlock: function () {
        this.showRangeDatesBlock = true
        this.clearSelection()
      },
      cancelRange: function () {
        this.showRangeDates = false
        this.form.availability = ''
        this.form.date_to = ''
        this.form.date_from = ''
      },
      cancelRangeBlock: function () {
        this.showRangeDatesBlock = false
        this.form.date_to = ''
        this.form.date_from = ''
      },
      loadPlanRates: function () {
        API.get('package/' + this.$route.params.package_id + '/plan_rates/selectBox')
          .then((result) => {
            let plans = result.data.data
            plans.forEach((plan) => {
              this.plan_rates.push({ label: plan.service_type.code +' - '+ plan.name, code: plan.id })
            })
            if (plans.length > 0) {
              this.package_plan_rate_id = plans[0].id
              this.planRateSelected = this.plan_rates[0].code
              this.getInventory(this.package_plan_rate_id)
            } else {

            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('global.modules.availability'),
            text: this.$t('global.error.information_error')
          })
        })
      },
      changeRatePlan: function () {
        this.package_plan_rate_id = this.planRateSelected
        this.getInventory(this.package_plan_rate_id)
        console.log(this.package_plan_rate_id)
      }
    }
  }
</script>

<style lang="stylus">
    .pull-right {
        float: right;
    }

    .calendar-header {
        list-style-type: none !important;
        padding: 20px 25px;
        width: 100%;
        background: #bd0d12;
        text-align: center;
        color: #fff;
        margin-bottom: 0px;
    }

    .calendar-header ul {
        margin: 0;
        padding: 0;
    }

    .calendar-header ul li {
        color: white;
        font-size: 20px;
        text-transform: uppercase;
        letter-spacing: 3px;
    }

    .calendar-header .prev {
        cursor: pointer;
        float: left;
        padding-top: 5px;
    }

    .calendar-header .next {
        float: right;
        padding-top: 5px;
        cursor: pointer;
    }

    .weekdays {
        position: relative;
        list-style: none;
        width: 100%;
        overflow: hidden;
        background-color: #ddd;
        margin 0px;
    }

    .weekdays li {
        display: inline-block;
        width: 13.6%;
        color: #666;
        text-align: center;
    }

    .days {
        position: relative;
        list-style: none;
        width: 100%;
        overflow: hidden;
        padding-bottom: 30px;
        background: #eee;
    }

    .days li {
        list-style-type: none;
        width: 13.6%;
        text-align: left;
        font-size: 12px;
        color: #777;
        min-height: 80px;
        padding: 5px;
        border: solid 1px #e1d4d4;
        float: left;
        display: block;
    }

    .days li div.active {
        width: 24px;
        background: #dd2b1e;
        color: #fff !important;
        text-align: center;
        border-radius: 26px;
        padding: 0px;
        font-weight: 600;
    }

    .days li:hover {
        color: #000 !important;
        font-weight: bold;
        cursor: pointer;
    }

    .day-qty {
        text-align right;
        padding-top 32px
    }

    .clear {
        clear: both;
    }

    .day_selected {
        background-color: #4dbd74;
        color: #ffffff !important;
    }

    .day_disabled {
        background-color: #f86c6b;
        color: #ffffff !important;
    }

    .day_normal {
        background-color: #777;
        color: #ffffff !important;
    }

    .day_intermediate {
        background-color: #dd9a08;
        color: #ffffff !important;
    }
</style>
