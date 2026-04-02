<template>
    <div class="container">
        <loading :active.sync="loading" :can-cancel="false" color="#BD0D12"></loading>
        <div class="vld-parent">
<!--            <div class="row">-->
<!--                <div class="col-md-4 pb-2">-->
<!--                    <label>Afecto a horario</label>-->
<!--                    <b-form-checkbox-->
<!--                        :checked="checkboxChecked(affected_schedule)"-->
<!--                        style="float:left !important"-->
<!--                        id="checkbox_status"-->
<!--                        name="checkbox_status"-->
<!--                        @change="changeState(affected_schedule)"-->
<!--                        switch>-->
<!--                    </b-form-checkbox>-->

<!--                </div>-->
<!--            </div>-->
            <div class="row">
                <div class="col-md-12 p-0">
                    <table class="table table-bordered">
                        <thead class="thead-light text-center">
                        <tr>
                            <th></th>
                            <th>{{ $t('global.days.everyone') }}</th>
                            <th>{{ $t('global.days.monday') }}</th>
                            <th>{{ $t('global.days.tuesday') }}</th>
                            <th>{{ $t('global.days.wednesday') }}</th>
                            <th>{{ $t('global.days.thursday') }}</th>
                            <th>{{ $t('global.days.friday') }}</th>
                            <th>{{ $t('global.days.saturday') }}</th>
                            <th>{{ $t('global.days.sunday') }}</th>
                            <th>
                                <button @click="addRowSchedule()" class="btn btn-sm btn-warning"
                                        :disabled="!affected_schedule"
                                        type="button">
                                    <font-awesome-icon :icon="['fas', 'plus']" />
                                    {{ $t('global.buttons.add') }}
                                </button>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(horaries,indexParent) in form.schedule" :key="indexParent">
                            <th class="text-center">
                                <div class="col-md-12 font-weight-bold">
                                    {{$t('servicesmanageservicescheduleservice.schedule')}}<br>
                                    {{indexParent + 1}} <br>
                                    <input v-if="flag_featured == 1" type="radio" name="featured[]" :value="1" v-model="featured[indexParent]" @change="updateFeatured(indexParent)">
                                </div>
                            </th>
                            <td>
                                <div class="">
                                    <div class="col-md-12 input-group-sm">
                                        <input :class="{'form-control':true }"
                                               type="time"
                                               :disabled="!affected_schedule"
                                               v-on:keyup="setScheduleRow(indexParent)"
                                               v-model="form.scheduleAll[indexParent].ini">
                                    </div>
                                    <div class="col-md-12 input-group-sm">
                                        <input :class="{'form-control':true }"
                                               v-on:keyup="setScheduleRow(indexParent)"
                                               type="time"
                                               :disabled="!affected_schedule"
                                               v-model="form.scheduleAll[indexParent].fin">
                                    </div>
                                </div>
                            </td>
                            <td v-for="(horary,index) in horaries" :key="index">
                                <div class="">
                                    <div class="col-md-12 input-group-sm">
                                        <input :class="{'form-control':true }"
                                               type="time"
                                               :disabled="!affected_schedule"
                                               v-model="horary.ini">
                                    </div>
                                    <div class="col-md-12 input-group-sm">
                                        <input :class="{'form-control':true }"
                                               type="time"
                                               :disabled="!affected_schedule"
                                               v-model="horary.fin">
                                    </div>
                                </div>
                            </td>

                            <th class="text-center">
                                <button @click="saveSchedule(indexParent)" class="btn btn-sm btn-success mb-sm-1"
                                        :disabled="!affected_schedule"
                                        type="button">
                                    <font-awesome-icon :icon="['fas', 'plus']" />
                                    {{ $t('global.buttons.submit') }}
                                </button>
                                <button @click="showModal(indexParent)"
                                        :disabled="!affected_schedule"
                                        class="btn btn-sm btn-danger"
                                        type="button">
                                    <font-awesome-icon :icon="['fas', 'trash']" />
                                    {{ $t('global.buttons.delete') }}
                                </button>
                            </th>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <b-modal :title="scheduleName" centered ref="my-modal" size="sm">
            <p class="text-center">{{$t('global.message_delete')}}</p>
            <div slot="modal-footer">
                <button @click="removeSchedule()" class="btn btn-success">{{$t('global.buttons.accept')}}</button>
                <button @click="hideModal()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
            </div>
        </b-modal>
    </div>
</template>

<script>
  import { API } from './../../../../api'
  import Loading from 'vue-loading-overlay'
  import 'vue-loading-overlay/dist/vue-loading.css'
  import BModal from 'bootstrap-vue/es/components/modal/modal'
  import BFormCheckbox from 'bootstrap-vue/es/components/form-checkbox/form-checkbox'

  export default {
    components: {
      Loading,
      BModal,
      BFormCheckbox
    },
    data: () => {
      return {
        loading: false,
        scheduleIndex: 0,
        formAction: 'post',
        affected_schedule: true,
        scheduleName: '',
        form: {
          scheduleAll: [{ ini: '', fin: '' }],
          schedule: [],
        },
        featured: [],
        flag_featured: 0,
        schedule_ids : []
      }
    },
    computed: {},
    created () {
      this.getSchedulesService()
    },
    methods: {
      checkboxChecked: function (service_status) {
        if (service_status) {
          return 'true'
        } else {
          return 'false'
        }
      },
      getSchedulesService: function () {
        //schedules
        this.form.schedule = []
        this.form.scheduleAll = []
        API.get('/service_schedules?id=' + this.$route.params.service_id).then((result) => {
          let schedules = result.data.data
          let affected_schedule = result.data.affected_schedule
          // if (affected_schedule === 1) {
          //   this.affected_schedule = true
          // } else {
          //   this.affected_schedule = false
          // }
          if (schedules.length > 0) {
            this.form.schedule = this.formatScheduleShow(schedules)            
            this.flag_featured = result.data.flag_featured
          } else {
            this.addRowSchedule()
          }
        }).catch((e) => {
            console.log('las', e)
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('global.modules.services'),
            text: this.$t('global.error.messages.connection_error')
          })
        })
      },
      formatScheduleShow: function (schedules) {
        let arrayNew = []
        this.featured = []
        schedules.forEach((schedule, index) => {

          let ini = schedule.services_schedule_detail[0]
          let fin = schedule.services_schedule_detail[1]
          arrayNew.push(
            [
              {
                id_parent: ini.service_schedule_id,
                id_ini: ini.id,
                id_fin: fin.id,
                code: 'monday',
                ini: ini.monday,
                fin: fin.monday
              },
              {
                id_parent: ini.service_schedule_id,
                id_ini: ini.id,
                id_fin: fin.id,
                code: 'tuesday',
                ini: ini.tuesday,
                fin: fin.tuesday
              },
              {
                id_parent: ini.service_schedule_id,
                id_ini: ini.id,
                id_fin: fin.id,
                code: 'wednesday',
                ini: ini.wednesday,
                fin: fin.wednesday
              },
              {
                id_parent: ini.service_schedule_id,
                id_ini: ini.id,
                id_fin: fin.id,
                code: 'thursday',
                ini: ini.thursday,
                fin: fin.thursday
              },
              {
                id_parent: ini.service_schedule_id,
                id_ini: ini.id,
                id_fin: fin.id,
                code: 'friday',
                ini: ini.friday,
                fin: fin.friday
              },
              {
                id_parent: ini.service_schedule_id,
                id_ini: ini.id,
                id_fin: fin.id,
                code: 'saturday',
                ini: ini.saturday,
                fin: fin.saturday
              },
              {
                id_parent: ini.service_schedule_id,
                id_ini: ini.id,
                id_fin: fin.id,
                code: 'sunday',
                ini: ini.sunday,
                fin: fin.sunday
              },
            ]
          )
          this.form.scheduleAll.push(
            [{ ini: '', fin: '' }]
          )

          this.featured.push(schedule.featured)
          this.schedule_ids.push(schedule.id)
        })
        return arrayNew
      },
      addRowSchedule: function () {
        this.form.schedule.push(
          [
            { id_parent: '', id_ini: '', id_fin: '', code: 'monday', ini: '', fin: '' },
            { id_parent: '', id_ini: '', id_fin: '', code: 'tuesday', ini: '', fin: '' },
            { id_parent: '', id_ini: '', id_fin: '', code: 'wednesday', ini: '', fin: '' },
            { id_parent: '', id_ini: '', id_fin: '', code: 'thursday', ini: '', fin: '' },
            { id_parent: '', id_ini: '', id_fin: '', code: 'friday', ini: '', fin: '' },
            { id_parent: '', id_ini: '', id_fin: '', code: 'saturday', ini: '', fin: '' },
            { id_parent: '', id_ini: '', id_fin: '', code: 'sunday', ini: '', fin: '' },
          ]
        )
        this.form.scheduleAll.push(
          [{ ini: '', fin: '' }]
        )
      },
      removeRowSchedule: function (index) {
        this.form.schedule.splice(index, 1)
      },
      setScheduleRow: function (index) {
        this.form.schedule[index].forEach((schedule) => {
          schedule.ini = this.form.scheduleAll[index].ini
          schedule.fin = this.form.scheduleAll[index].fin
        })
      },
      formatScheduleInsert: function (schedules, type) {
        let arrayNew
        if (schedules[0].id_ini === '') {
          this.formAction = 'post'
          arrayNew = {
            service_id: this.$route.params.service_id,
            type: (type === 1) ? 'I' : 'F',
            monday: (type === 1) ? schedules[0].ini : schedules[0].fin,
            tuesday: (type === 1) ? schedules[1].ini : schedules[1].fin,
            wednesday: (type === 1) ? schedules[2].ini : schedules[2].fin,
            thursday: (type === 1) ? schedules[3].ini : schedules[3].fin,
            friday: (type === 1) ? schedules[4].ini : schedules[4].fin,
            saturday: (type === 1) ? schedules[5].ini : schedules[5].fin,
            sunday: (type === 1) ? schedules[6].ini : schedules[6].fin,
          }
        } else {
          this.formAction = 'put'
          arrayNew = {
            id: (type === 1) ? schedules[0].id_ini : schedules[0].id_fin,
            type: (type === 1) ? 'I' : 'F',
            monday: (type === 1) ? schedules[0].ini : schedules[0].fin,
            tuesday: (type === 1) ? schedules[1].ini : schedules[1].fin,
            wednesday: (type === 1) ? schedules[2].ini : schedules[2].fin,
            thursday: (type === 1) ? schedules[3].ini : schedules[3].fin,
            friday: (type === 1) ? schedules[4].ini : schedules[4].fin,
            saturday: (type === 1) ? schedules[5].ini : schedules[5].fin,
            sunday: (type === 1) ? schedules[6].ini : schedules[6].fin,
          }
        }
        return arrayNew
      },
      saveSchedule: function (index) {
        this.loading = true
        let sheduleTo = this.formatScheduleInsert(this.form.schedule[index], 1)
        let sheduleFrom = this.formatScheduleInsert(this.form.schedule[index], 2)
        API({
          method: this.formAction,
          url: 'service_schedules' + (this.formAction === 'put' ? '/'+this.$route.params.service_id : ''),
          data: {
            schedule_to: sheduleTo,
            schedule_from: sheduleFrom,
            service_id: this.$route.params.service_id,
            featured: this.featured[index],
            schedule_id: this.schedule_ids[index]
          }
        }).then((result) => {
          this.loading = false
          if (result.data.success === false) {
            this.$notify({
              group: 'main',
              type: 'error',
              title: this.$t('global.modules.services'),
              text: this.$t('global.error.save')
            })
          } else {
            this.$notify({
              group: 'main',
              type: 'success',
              title: this.$t('global.modules.services'),
              text: this.$t('global.success.save')
            })
            this.getSchedulesService()
          }
        }).catch(() => {
          this.loading = false
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('global.modules.services'),
            text: this.$t('global.error.messages.connection_error')
          })
        })
      },
      showModal (index) {
        this.scheduleName = this.$t('servicesmanageservicescheduleservice.schedule') + ' ' + (index + 1)
        this.scheduleIndex = index
        this.$refs['my-modal'].show()
      },
      hideModal () {
        this.$refs['my-modal'].hide()
      },
      getScheduleIdRemove: function (index) {
        return this.form.schedule[index][0].id_parent
      },
      removeSchedule: function () {
        let scheduleId = this.getScheduleIdRemove(this.scheduleIndex)
        this.hideModal()
        if (scheduleId === '') {
          this.removeRowSchedule(this.scheduleIndex)
        } else {
          this.loading = true
          API({
            method: 'DELETE',
            url: 'service_schedules/' + scheduleId + '/' + this.$route.params.service_id
          }).then((result) => {
            this.loading = false
            if (result.data.success === true) {
              this.getSchedulesService()
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.services'),
                text: this.$t('global.error.delete')
              })
            }
          }).catch(() => {
            this.$notify({
              group: 'main',
              type: 'error',
              title: this.$t('global.modules.services'),
              text: this.$t('global.error.messages.connection_error')
            })
          })
        }
      },
      changeState: function (affected_schedule) {

        this.affected_schedule = !affected_schedule
        API({
          method: 'put',
          url: 'service/' + this.$route.params.service_id + '/schedule',
          data: { affected_schedule: this.affected_schedule }
        })
          .then((result) => {
            if (result.data.success === true) {
              this.$notify({
                group: 'main',
                type: 'success',
                title: this.$t('global.modules.services'),
                text: this.$t('global.success.save')
              })
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.services'),
                text: this.$t('global.error.messages.connection_error')
              })
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('global.modules.services'),
            text: this.$t('global.error.messages.connection_error')
          })
        })
      },
      updateFeatured(indexToUpdate) {
        // Obtener el valor seleccionado actual
        const selectedValue = this.featured[indexToUpdate];
        // Recorrer todos los elementos y ponerlos a 0 excepto el seleccionado
        for (let i = 0; i < this.featured.length; i++) {
          if (i !== indexToUpdate) {
            this.featured[i] = 0; // Poner a 0 todos menos el seleccionado
          }
        }
        // Restaurar el valor seleccionado
        this.featured[indexToUpdate] = selectedValue;
      }
    }
  }
</script>

<style lang="stylus">
</style>


