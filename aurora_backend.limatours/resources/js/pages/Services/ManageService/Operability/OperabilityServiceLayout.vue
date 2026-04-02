<template>
    <div class="vld-parent">
        <loading :active.sync="loading" :can-cancel="false" color="#BD0D12"></loading>
        <div class="box mb-3">
            <div v-for="(operation, o) in service_operations" :class="{'col-md-12':true, 'tr-checked':(operation.id)===operation_radio}" style="padding: 5px;">
                <div class="form-group row">
                    <div class="col-md-2">
                        <label>
                            <input style="margin-top: 34px;" type="radio" @click="find_operability(operation)" name="operation_radio" v-model="operation_radio" :value="operation.id"> Horario {{ o+1 }}
                        </label>
                    </div>
                    <div class="col-sm-2">
                        <label>{{ $t('servicesmanageserviceoperability.day') }}</label>
                        <div class="col-sm-12 p-0">
                            <v-select :options="days"
                                      @input="dayChange(operation)"
                                      :value="operation.data.day"
                                      autocomplete="true"
                                      data-vv-name="day"
                                      data-vv-as="day"
                                      data-vv-scope="form-1"
                                      v-validate="'required'"
                                      v-model="operation.daySelected">
                            </v-select>
                            <span v-show="errors.has('form-1.day')" class="invalid-feedback-select">
                                <span>{{ errors.first('form-1.day') }}</span>
                            </span>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <label>{{ $t('servicesmanageserviceoperability.departure_time') }}</label>
                        <div class="col-sm-12 p-0">
                            <input :class="{'form-control':true }"
                                   type="time"
                                   data-vv-name="start_time"
                                   data-vv-as="start_time"
                                   data-vv-scope="form-1"
                                   v-model="operation.start_time"
                                   v-validate="'required'">
                            <span v-show="errors.has('form-1.departure')" class="invalid-feedback">
                                <span>{{ errors.first('form-1.start_time') }}</span>
                            </span>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <label>{{ $t('servicesmanageserviceoperability.available_shifts') }}</label>
                        <div class="col-sm-12 p-0">
                            {{ operation.shifts_available }}
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <label>SSHH en ruta</label>
                        <div class="col-sm-12 p-0">
                            <c-switch :value="true" class="mx-1" color="success"
                                      v-model="operation.sshh_available"
                                      variant="pill">
                            </c-switch>
                        </div>
                    </div>
                    <div class="col-sm-1">
                        <label style="color: white">.</label>
                        <button @click="validateBeforeSubmitOpeNew(operation)" class="form-control btn btn-success"
                                type="submit" :disabled="loading || !((operation.id)===operation_radio)" v-if="!hasClient">
                            <font-awesome-icon :icon="['fas', 'save']" />
                        </button>
                        <button @click="validateBeforeSubmitOpe(operation)" class="form-control btn btn-success"
                                type="submit" :disabled="loading || !((operation.id)===operation_radio)" v-if="hasClient">
                            <font-awesome-icon :icon="['fas', 'save']" />
                        </button>
                    </div>
                </div>

            </div>

            <div class="col-md-12" v-if="service_operations.length===0">
                <div class="alert alert-warning">
                    <i class="fa fa-info-circle"></i> Por favor agregar los turnos desde la pestaña de "Horarios", si este servicio ya tenía operatividades asignadas se verán reflejadas en el primer turno que agregue.
                </div>
            </div>

<!--            <div class="col-md-12">-->
<!--                <div class="form-group row">-->
<!--                    <div class="col-sm-2">-->
<!--                        <label>{{ $t('servicesmanageserviceoperability.day') }}</label>-->
<!--                        <div class="col-sm-12 p-0">-->
<!--                            <v-select :options="days"-->
<!--                                      @input="dayChange"-->
<!--                                      :value="formOperavility.day"-->
<!--                                      autocomplete="true"-->
<!--                                      data-vv-name="day"-->
<!--                                      data-vv-as="day"-->
<!--                                      data-vv-scope="form-1"-->
<!--                                      v-validate="'required'"-->
<!--                                      v-model="daySelected">-->
<!--                            </v-select>-->
<!--                            <span v-show="errors.has('form-1.day')" class="invalid-feedback-select">-->
<!--                                <span>{{ errors.first('form-1.day') }}</span>-->
<!--                            </span>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                    <div class="col-sm-2">-->
<!--                        <label>{{ $t('servicesmanageserviceoperability.departure_time') }}</label>-->
<!--                        <div class="col-sm-12 p-0">-->
<!--                            <input :class="{'form-control':true }"-->
<!--                                   id="start_time" name="start_time"-->
<!--                                   type="time"-->
<!--                                   data-vv-name="start_time"-->
<!--                                   data-vv-as="start_time"-->
<!--                                   data-vv-scope="form-1"-->
<!--                                   v-model="formOperavility.start_time"-->
<!--                                   v-validate="'required'">-->
<!--                            <span v-show="errors.has('form-1.departure')" class="invalid-feedback">-->
<!--                                            <span>{{ errors.first('form-1.start_time') }}</span>-->
<!--                                        </span>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                    <div class="col-sm-2">-->
<!--                        <label>{{ $t('servicesmanageserviceoperability.available_shifts') }}</label>-->
<!--                        <div class="col-sm-12 p-0">-->
<!--                            <v-select :options="turns"-->
<!--                                      @input="turnChange"-->
<!--                                      :value="formOperavility.shifts_available"-->
<!--                                      autocomplete="true"-->
<!--                                      data-vv-name="turn"-->
<!--                                      data-vv-as="turn"-->
<!--                                      data-vv-scope="form-1"-->
<!--                                      v-validate="'required'"-->
<!--                                      v-model="turnSelected">-->
<!--                            </v-select>-->
<!--                            <span v-show="errors.has('form-1.turn')" class="invalid-feedback-select">-->
<!--                                <span>{{ errors.first('form-1.turn') }}</span>-->
<!--                            </span>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                    <div class="col-sm-3">-->
<!--                        <label>{{ $t('servicesmanageserviceoperability.availability_sshh') }}</label>-->
<!--                        <div class="col-sm-12 p-0">-->
<!--                            <c-switch :value="true" class="mx-1" color="success"-->
<!--                                      v-model="formOperavility.sshh_available"-->
<!--                                      variant="pill">-->
<!--                            </c-switch>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                    <div class="col-md-2">-->
<!--                        <label>.</label>-->
<!--                        <button @click="validateBeforeSubmitOpeNew" class="form-control btn btn-success"-->
<!--                                type="submit" v-if="!loading">-->
<!--                            <font-awesome-icon :icon="['fas', 'dot-circle']" />-->
<!--                            {{ $t('global.buttons.save') }}-->
<!--                        </button>-->
<!--                    </div>-->
<!--                </div>-->

<!--            </div>-->

        </div>

        <div class="col-sm-12 p-0">
            <b-tabs>
                <b-tab :title="this.$i18n.t('servicesmanageserviceoperability.operativity_detail')">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group row">
                                        <div class="col-sm-5">
                                            <div v-show="viewFormDetail">
                                                <label>{{ $t('servicesmanageserviceoperability.type_operativity')
                                                    }}</label>
                                                <div class="col-sm-12 p-0">
                                                    <v-select :options="operativities"
                                                              id="operativity"
                                                              :disabled="disableInput"
                                                              :value="formDetailOperavility.activity_id"
                                                              @input="operativityChange"
                                                              autocomplete="true"
                                                              data-vv-as="operativity"
                                                              data-vv-name="operativity"
                                                              data-vv-scope="form-2"
                                                              name="operativity"
                                                              v-model="operativitySelected"
                                                              v-validate="'required'">
                                                    </v-select>
                                                    <span class="invalid-feedback-select"
                                                          v-show="errors.has('form-2.operativity')">
                                                    <span>{{ errors.first('form-2.operativity') }}</span>
                                                </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-2">
                                            <div v-show="viewFormDetail">
                                                <label>Horarios</label>
                                                <div class="col-sm-12 p-0">
                                                    <v-select multiple
                                                              :options="service_operations"
                                                              id="schedules"
                                                              :disabled="disableInput"
                                                              :value="formDetailOperavility.schedules"
                                                              autocomplete="true"
                                                              data-vv-as="schedule"
                                                              data-vv-name="schedule"
                                                              data-vv-scope="form-2"
                                                              name="operativity"
                                                              label="name"
                                                              v-model="scheduleSelected"
                                                              v-validate="'required'">
                                                    </v-select>
                                                    <span class="invalid-feedback-select"
                                                          v-show="errors.has('form-2.schedule')">
                                                    <span>{{ errors.first('form-2.schedule') }}</span>
                                                </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-2">
                                            <div v-show="viewFormDetail">
                                                <label>{{ $t('servicesmanageserviceoperability.minutes') }}</label>
                                                <div class="col-sm-12 p-0">
                                                    <input :class="{'form-control':true }"
                                                           id="minutes" name="minutes"
                                                           :disabled="disableInput"
                                                           type="text"
                                                           data-vv-name="minute"
                                                           data-vv-as="minute"
                                                           data-vv-scope="form-2"
                                                           v-model="formDetailOperavility.minutes"
                                                           v-validate="'required|numeric|min_value:1'">
                                                    <span v-show="errors.has('form-2.minute')" class="invalid-feedback">
                                                <span>{{ errors.first('form-2.minute') }}</span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <div v-show="viewFormDetail">
                                                <label style="color: white">.</label>
                                                <button @click="validateBeforeSubmitDeNew"
                                                        :disabled="disableInput"
                                                        class="form-control btn btn-success"
                                                        type="submit" v-if="!loading && !hasClient">
                                                    <font-awesome-icon :icon="['fas', 'save']" />
                                                </button>
                                                <button @click="validateBeforeSubmitDe"
                                                        :disabled="disableInput"
                                                        class="form-control btn btn-success"
                                                        type="submit" v-if="!loading && hasClient">
                                                    <font-awesome-icon :icon="['fas', 'save']" />
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-md-2" v-if="!viewFormDetail">
                                            <label style="color: white">.</label>
                                            <button @click="showFormDetail()"
                                                    :disabled="disableInput"
                                                    class="form-control btn btn-danger"
                                                    type="submit" v-if="!loading">
                                                <font-awesome-icon :icon="['fas', 'plus']" />
                                                {{ $t('global.buttons.add') }}
                                            </button>
                                        </div>
                                        <div class="col-md-1" v-if="viewFormDetail">
                                            <label style="color: white">.</label>
                                            <button @click="cancelFormDetail()"
                                                    class="form-control btn btn-danger"
                                                    type="submit" v-if="!loading">
                                                <font-awesome-icon :icon="['fas', 'times']" />
                                            </button>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <table-client :columns="table.columns" :options="tableOptions"
                                          :data="operations" :loading="loading"
                                          class="text-center"
                                          ref="table">
                                <div class="table-day" slot="id" slot-scope="props"
                                     style="font-size: 0.9em">
                                    {{props.row.id}}
                                </div>
                                <div class="table-day" slot="day" slot-scope="props"
                                     style="font-size: 0.9em">
                                    {{props.row.service_operations.day}}
                                </div>
                                <div class="table-service_operativity" slot="operativity" slot-scope="props"
                                     style="font-size: 0.9em">
                                    {{props.row.operativity_start}} - {{props.row.operativity_end}}
                                </div>
                                <div class="table-service_type_operativity" slot="type_operativity"
                                     slot-scope="props"
                                     style="font-size: 0.9em">
                                    <router-link :to="'/type_operability/edit/'+props.row.service_type_activities.id" target="_blank">
                                        <i class="fas fa-link"></i> {{props.row.service_type_activities.translations[0].value}}
                                    </router-link>
                                </div>
                                <div class="table-service_minutes" slot="minutes" slot-scope="props"
                                     style="font-size: 0.9em">
                                    {{props.row.minutes}}
                                </div>
                                <div class="table-actions" slot="actions" slot-scope="props" style="padding: 5px;">
                                    <b-dropdown class="mt-2 ml-2 mb-0" dropleft size="sm">
                                        <template slot="button-content">
                                            <font-awesome-icon :icon="['fas', 'bars']" class="ml-1 p-0" />
                                        </template>
                                        <b-dropdown-item-button
                                            @click="willEdit(props.row)"
                                            class="m-0 p-0"
                                            :disabled="disableInput"
                                            v-if="$can('delete', 'services')">
                                            <font-awesome-icon :icon="['fas', 'edit']" class="m-0" />
                                            {{$t('global.buttons.edit')}}
                                        </b-dropdown-item-button>
                                        <b-dropdown-item-button
                                            @click="showModal(props.row.id,props.row.service_type_activities.translations[0].value)"
                                            class="m-0 p-0"
                                            :disabled="disableInput"
                                            v-if="$can('delete', 'services')">
                                            <font-awesome-icon :icon="['fas', 'trash']" class="m-0" />
                                            {{$t('global.buttons.delete')}}
                                        </b-dropdown-item-button>
                                    </b-dropdown>
                                </div>
                            </table-client>
                        </div>
                    </div>
                </b-tab>
            </b-tabs>
        </div>

        <b-modal :title="typeOperabilityName" centered ref="my-modal" size="sm">
            <p class="text-center">{{$t('global.message_delete')}}</p>
            <div slot="modal-footer">
                <button @click="removeActivity()" class="btn btn-success">{{$t('global.buttons.accept')}}</button>
                <button @click="hideModal()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
            </div>
        </b-modal>
        <b-modal title="Notificaciones" centered ref="my-modal-notify" size="md">
            <div class="row" v-show="!optionSelect">
                <div class="col-md-12">
                    <p class="text-center">¿Desea enviar una notificación de los cambios realizados?</p>
                </div>
            </div>
            <div class="form-group row" v-show="optionSelect">
                <div class="col-sm-12">
                    <label for="name_service">Emails</label>
                    <multiselect :clear-on-select="false"
                                 :close-on-select="false"
                                 :hide-selected="true"
                                 :multiple="true"
                                 :options="emails"
                                 placeholder="Emails"
                                 :preserve-search="true"
                                 tag-placeholder="Emails"
                                 :taggable="true"
                                 @tag="addEmail"
                                 label="name"
                                 ref="multiselect"
                                 track-by="email"
                                 v-model="emailsService">
                    </multiselect>
                </div>
                <div class="col-md-12">
                    <label for="name_service">Mensaje</label>
                    <textarea :class="{'form-control':true }"
                              id="message" name="descripction"
                              rows="3"
                              v-model="form_notify.message"></textarea>
                </div>
            </div>
            <template slot="modal-footer">
                <div class="row" v-show="!optionSelect">
                    <div class="col-md-12">
                        <button @click="optionSelection(1)" class="btn btn-success" type="reset" v-if="!loading">
                            Si, enviar y guardar
                        </button>
                        <button @click="optionSelection(2)" class="btn btn-secondary" type="reset" v-if="!loading">
                            No, solo guardar
                        </button>
                    </div>
                </div>
                <div class="row" v-show="optionSelect">
                    <div class="col-md-12">
                        <button @click="optionSelection(3)" class="btn btn-success" type="reset" v-if="!loading">
                            Enviar y guardar
                        </button>
                        <button @click="hideModal()" class="btn btn-danger" type="reset" v-if="!loading">
                            {{$t('global.buttons.cancel')}}
                        </button>
                    </div>
                </div>
            </template>
        </b-modal>
    </div>
</template>

<script>
    import { API } from './../../../../api'
    import TableClient from '../../../../components/TableClient'
    import Loading from 'vue-loading-overlay'
    import 'vue-loading-overlay/dist/vue-loading.css'
    import BModal from 'bootstrap-vue/es/components/modal/modal'
    import vSelect from 'vue-select'
    import 'vue-select/dist/vue-select.css'
    import { Switch as cSwitch } from '@coreui/vue'
    import BTab from 'bootstrap-vue/es/components/tabs/tab'
    import BTabs from 'bootstrap-vue/es/components/tabs/tabs'
    import Multiselect from 'vue-multiselect'

    export default {
        components: {
            BTabs,
            BTab,
            cSwitch,
            Loading,
            BModal,
            vSelect,
            Multiselect,
            'table-client': TableClient,
        },
        data: () => {
            return {
                loading: false,
                optionSelect: false,
                emailsService: [],
                emails: [],
                form_notify: {
                    emails: [],
                    message: ''
                },
                sendForm: 1,
                formAction: 'post',
                days: [],
                operations: [],
                operation_radio:"",
                typeOperabilityName: '',
                turns: [
                    { code: 'AM', label: 'AM' },
                    { code: 'PM', label: 'PM' },
                    { code: 'AM/PM', label: 'AM/PM' },
                ],
                service_operations:[],
                formOperavility: {
                    service_id: '',
                    id: '',
                    day: '',
                    start_time: '',
                    shifts_available: '',
                    sshh_available: false,
                    hasNotify: false,
                    emails: '',
                    message: '',
                },
                formDetailOperavility: {
                    id: '',
                    service_operation_id: '',
                    activity_id: '',
                    minutes: '',
                    hasNotify: false,
                    emails: '',
                    message: '',
                },
                urlOperability: '',
                turnSelected: [],
                daySelected: [],
                operativitySelected: [],
                operativities: [],
                disableInput: true,
                viewFormDetail: false,
                table: {
                    columns: ['id', 'day', 'operativity', 'type_operativity', 'minutes', 'actions'],
                },
                formActionDoperavility: 'post',
                service_operation_id_selected:null,
                operation_selected:null,
                scheduleSelected:[],
                hasClient: false,
            }
        },
        mounted () {
            API.get('users/notification/service').then((result) => {
                let emails = result.data.data
                emails.forEach((email) => {
                    this.emails.push({
                        name: email.name + ' <' + email.email + '>',
                        email: email.email
                    })
                })
            })
            //operativity
            API.get('type_operability/selectBox?lang=' + localStorage.getItem('lang'))
                .then((result) => {
                    let operativities = result.data.data
                    operativities.forEach((operativities) => {
                        this.operativities.push({
                            label: operativities.translations[0].value,
                            code: operativities.translations[0].object_id
                        })
                    })
                }).catch(() => {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('global.error.messages.name'),
                    text: this.$t('global.error.messages.connection_error')
                })
            })
        },
        computed: {
            tableOptions: function () {
                return {
                    headings: {
                        id: 'ID',
                        day: this.$i18n.t('servicesmanageserviceoperability.day'),
                        operativity: this.$i18n.t('global.modules.services'),
                        type_operativity: this.$i18n.t('servicesmanageserviceoperability.type_operativity'),
                        minutes: this.$i18n.t('servicesmanageserviceoperability.minutes'),
                        actions: this.$i18n.t('global.table.actions')
                    },
                    sortable: [],
                    filterable: ['id', 'service_type_activities.translations'],
                    perPageValues: [],
                }
            }
        },
        created () {
            this.getOperativity()
            this.formOperavility.service_id = this.$route.params.service_id
            this.hasClient = !!(window.localStorage.getItem('client_id') && window.localStorage.getItem('client_id') !== '')
        },
        methods: {
            getData: function () {
                this.loading = true
                API.get('/service_operation_activity?token=' + window.localStorage.getItem('access_token') +
                    '&lang=' + localStorage.getItem('lang') + '&id=' + this.service_operation_id_selected)
                    .then((result) => {
                        this.loading = false
                        this.operations = result.data.data
                    })
                    .catch(() => {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Fetch Error',
                            text: 'Cannot load data'
                        })
                    })
            },
            getDays: function (duration) {
                for (let i = 1; i <= duration; i++) {
                    this.days.push({
                        'code': i,
                        'label': 'Día ' + i,
                    })
                }
            },
            getOperativity: function () {
                //operativity
                this.loading = true
                API.get('service_operation/' + this.$route.params.service_id)
                    .then((result) => {
                        this.loading = false
                        // this.serviceOperation = result.data.data.service_operations
                        let duration = parseInt(result.data.data.duration)
                        this.getDays(duration)
                        result.data.data.service_operations.forEach((data, i)=>{
                            if( i===0 ){
                                this.scheduleSelected.push(data)
                            }
                            data.name = "Horario " + (i + 1)
                            data.daySelected = {
                                'code': 1,
                                'label': 'Día 1'
                            }
                            data.day = 1
                            data.start_time = "00.00"
                            data.sshh_available = false
                            data.shifts_available = false
                            if( data.data.length > 0 ){
                                data.start_time = data.data[0].start_time
                                data.shifts_available = data.data[0].shifts_available
                            }
                            data.data.forEach((d)=>{
                                d.start_time = d.start_time.substring(0, 5)
                                d.sshh_available = !!(d.sshh_available)
                            })
                        })

                        this.service_operations = result.data.data.service_operations
                        this.formOperavility = this.service_operations[0].data[0] //
                        this.operation_radio = this.service_operations[0].id
                        this.service_operation_id_selected = this.service_operations[0].data[0].id
                        this.getData()
                        this.disableInput = false

                    }).catch(() => {
                    this.loading = false
                })
            },
            showModal (id, name) {
                this.servive_type_activity_id = id
                this.typeOperabilityName = name
                this.$refs['my-modal'].show()
            },
            hideModal () {
                this.optionSelect = false
                this.$refs['my-modal-notify'].hide()
                this.$refs['my-modal'].hide()
            },
            // turnChange: function (value) {
            //     this.turn = value
            //     if (this.turn != null) {
            //         this.formOperavility.shifts_available = this.turn.code
            //     } else {
            //         this.formOperavility.shifts_available = ''
            //         this.turnSelected = []
            //     }
            // },
            find_operability: function (turn_operation) {
                this.scheduleSelected = []
                this.scheduleSelected.push(turn_operation)
                this.operation_radio = turn_operation.id
                turn_operation.day = turn_operation.daySelected.code
                console.log(turn_operation)
                let flag = 0
                for (let i = 0; i < turn_operation.data.length; i++) {
                    if (turn_operation.data[i].day === turn_operation.daySelected.code) {
                        flag = 1 // pone el primero del dia
                        this.service_operation_id_selected = turn_operation.data[i].id
                        turn_operation.start_time = turn_operation.data[i].start_time.substring(0, 5)
                        turn_operation.sshh_available = turn_operation.data[i].sshh_available
                        turn_operation.shifts_available = turn_operation.data[i].shifts_available
                        this.disableInput = false
                        this.cancelFormDetail()
                        this.getData()
                        break
                    }
                }
                if (flag === 0) {
                    this.service_operation_id_selected = null
                    this.disableInput = true
                    this.operations = []
                }
            },
            dayChange: function (turn_operation) {

                if (turn_operation.data.day !== null) {
                    this.find_operability(turn_operation)
                } else {
                    turn_operation.daySelected = []
                }
            },
            operativityChange: function (value) {
                this.operativity = value
                if (this.operativity != null) {
                    this.formDetailOperavility.activity_id = this.operativity.code
                } else {
                    this.formDetailOperavility.activity_id = ''
                    this.operativitySelected = []
                }
            },
            validateBeforeSubmitOpeNew: function (operation) {
                this.operation_selected = operation
                this.optionSelect = false
                this.$refs['my-modal-notify'].show()
                this.sendForm = 1
            },
            validateBeforeSubmitOpe: function (operation) {
                this.$validator.validateAll('form-1').then((result) => {
                    if (result) {
                        this.operation_selected = operation
                        this.submitOperavility()
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.services'),
                            text: this.$t('global.error.information_complete')
                        })
                        this.loading = false
                    }

                })
            },
            submitOperavility: function () {

                // console.log(this.operation_selected)
                // return

                let method_ = "post"
                let id_ = null
                let flag = 0
                for (let i = 0; i < this.operation_selected.data.length; i++) {
                    if (this.operation_selected.data[i].day === this.operation_selected.daySelected.code) {
                        method_ = "put"
                        id_ = this.operation_selected.data[i].id
                        flag = 1 // pone el primero del dia
                        break
                    }
                }

                this.loading = true

                let data = {}
                data.id = id_
                data.service_schedule_id = this.operation_selected.id
                data.service_id = this.operation_selected.service_id
                data.hasNotify = this.optionSelect
                data.emails = this.emailsService
                data.message = this.form_notify.message
                data.start_time = moment(this.operation_selected.start_time, 'HH:mm').format('HH:mm:ss')
                data.shifts_available = moment(this.operation_selected.start_time, 'HH:mm').format('A')
                data.sshh_available = this.operation_selected.sshh_available
                data.day = this.operation_selected.day
                API({
                    method: method_,
                    url: 'service_operation' + (id_ !== null ? '/'+id_ : ''),
                    data: data
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
                        this.service_operations.forEach((s_o)=>{
                            if( s_o.id === this.operation_selected.id ){
                                if( id_ === null ){
                                    s_o.data.push(result.data.data.operation)
                                } else {
                                    s_o.data.forEach((d)=>{
                                        if(d.id === id_){
                                            d.start_time = data.start_time.substring(0, 5)
                                            d.shifts_available = data.shifts_available
                                            d.sshh_available = data.sshh_available
                                            d.day = data.day
                                        }
                                    })
                                }
                            }
                        })
                        this.find_operability(this.operation_selected)

                        this.disableInput = false

                        this.$notify({
                            group: 'main',
                            type: 'success',
                            title: this.$t('global.modules.services'),
                            text: this.$t('global.success.save')
                        })

                    }
                })
            },
            validateBeforeSubmitDeNew: function () {
                this.$validator.validateAll('form-2').then((result) => {
                    if (result) {
                        this.optionSelect = false
                        this.$refs['my-modal-notify'].show()
                        this.sendForm = 2
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.services'),
                            text: this.$t('global.error.information_error')
                        })
                        this.loading = false
                    }

                })
            },
            validateBeforeSubmitDe: function () {
                this.$validator.validateAll('form-2').then((result) => {
                    if (result) {
                        this.sendForm = 2
                        this.submitDetailOperavility()

                        // this.$validator.reset() //solution
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.services'),
                            text: this.$t('global.error.information_error')
                        })
                        this.loading = false
                    }

                })
            },
            submitDetailOperavility: function () {

                console.log( this.scheduleSelected )

                let day_
                let flag_1 = 1
                this.service_operations.forEach((so)=>{
                    so.data.forEach((data)=>{
                        if(flag_1 && data.id === this.service_operation_id_selected){
                            day_ = data.day
                            flag_1 = 0
                        }
                    })
                })

                let service_operation_ids = []
                this.scheduleSelected.forEach((s)=>{
                    let flag_2 = 1
                    s.data.forEach((data_)=>{
                        if( flag_2 && data_.day === day_ ){
                            service_operation_ids.push(data_.id)
                            flag_2 = 0
                        }
                    })
                })

                this.loading = true
                this.formDetailOperavility.service_operation_id = service_operation_ids // this.service_operation_id_selected
                this.formDetailOperavility.hasNotify = this.optionSelect
                this.formDetailOperavility.emails = this.emailsService
                this.formDetailOperavility.message = this.form_notify.message
                API({
                    method: this.formActionDoperavility,
                    url: 'service_operation_activity' + (this.formDetailOperavility.id !== '' ? '/'+this.formDetailOperavility.id : ''),
                    data: this.formDetailOperavility
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
                        this.cancelFormDetail()
                        this.getData()
                    }
                })
            },
            onUpdate () {
                this.urlOperability = '/api/service_operation_activity?token=' +
                    window.localStorage.getItem('access_token') + '&lang=' + localStorage.getItem('lang') +
                    '&id=' + this.service_operation_id_selected
                this.$refs.table.$refs.tableserver.refresh()
            },
            removeActivity: function () {
                API({
                    method: 'DELETE',
                    url: 'service_operation_activity/' + this.servive_type_activity_id
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.getData()
                            this.hideModal()
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.services'),
                                text: this.$t('global.success.delete')
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
            cancelFormDetail: function () {
                this.operativitySelected = []
                this.formDetailOperavility.minutes = ''
                this.formDetailOperavility.id = ''
                this.viewFormDetail = false
                this.formActionDoperavility = 'post'
            },
            showFormDetail: function () {
                this.viewFormDetail = true
                this.formActionDoperavility = 'post'
            },
            willEdit: function (data) {
                console.log(data.service_type_activity_id)
                this.formActionDoperavility = 'put'
                this.operativitySelected = []
                this.disableInput = false
                this.viewFormDetail = true
                this.operativitySelected.push({
                    code: data.service_type_activity_id,
                    label: data.service_type_activities.translations[0].value,
                })
                this.formDetailOperavility.id = data.id
                this.formDetailOperavility.activity_id = data.service_type_activity_id
                this.formDetailOperavility.minutes = data.minutes
            },
            optionSelection: function (option, form) {
                if (option === 1) {
                    this.optionSelect = true
                } else if (option === 2) {
                    this.$refs['my-modal-notify'].hide()
                    if (this.sendForm === 1) {
                        this.submitOperavility()
                    } else {
                        this.submitDetailOperavility()
                    }
                } else {
                    if (this.emailsService.length === 0) {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.services'),
                            text: 'Debe seleccionar al menos un email'
                        })
                    } else {
                        if (this.optionSelect && this.sendForm === 1) {
                            this.$refs['my-modal-notify'].hide()
                            this.submitOperavility()
                        }
                        if (this.optionSelect && this.sendForm === 2) {
                            this.$refs['my-modal-notify'].hide()
                            this.submitDetailOperavility()
                        }
                    }
                }
            },
            addEmail (newTag) {
                const tag = {
                    name: newTag,
                    email: newTag.substring(0, 2) + Math.floor((Math.random() * 10000000))
                }
                this.emailsService.push(tag)
            },
            removeEmail: function (index) {
                this.form_notify.emails.splice(index, 1)
            },
        }
    }
</script>

<style lang="stylus">
    table {
        width: 100%;
        position: relative;
    }

    .tr-checked{
        background-color: #c0e4ff;
    }

    .overlay-loader {
        position: absolute;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: white;
        opacity: 0.8;
        filter: blur(5px);
    }

    .clip-loader {
        position: absolute;
        left: 50%;
        right: 50%;
        bottom: 60%;
        top: 40%;
    }
</style>


