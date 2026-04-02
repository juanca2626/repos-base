<template>
    <div class="row-fluid col-12" style="margin-top: 10px;">
        <table class="table">
            <thead>
            <th v-for="table in tableOptions">
                {{ table }}
            </th>
            </thead>
            <tbody>
            <tr v-for="room in rooms">
                <td>
                    <b-dropdown class="mt-2 ml-2 mb-0" dropright size="sm">
                        <template slot="button-content">
                            <font-awesome-icon :icon="['fas', 'bars']" class="ml-1 p-0"/>
                        </template>

                        <router-link :to="'/hotels/edit/'+room.id" class="nav-link m-0 p-0">
                            <router-link :to="'/hotels/'+room.hotel_id+'/manage_hotel/rooms/edit/'+room.id"
                                         class="nav-link m-0 p-0">
                                <b-dropdown-item-button class="m-0 p-0" v-if="$can('read', 'rooms')">
                                    <font-awesome-icon :icon="['fas', 'dot-circle']" class="m-0"/>
                                    {{$t('global.buttons.edit')}}
                                </b-dropdown-item-button>
                            </router-link>
                        </router-link>
                        <b-dropdown-item-button @click="showModal(room)"
                                                class="m-0 p-0"
                                                v-if="$can('delete', 'rooms')">
                            <font-awesome-icon :icon="['fas', 'trash']" class="m-0"/>
                            {{$t('global.buttons.delete')}}
                        </b-dropdown-item-button>
                    </b-dropdown>
                </td>
                <td>
                    {{ room.translations[0].value }}
                </td>
                <td>
                    <input style="max-width: 80px;" class="form-control" type="number" min="1" step="1"
                           title="Presione Enter para actualizar cambios"
                           v-model="room.order" @keypress="update_order(room)" :disabled="loading">
                </td>
                <td>
                    <b-form-checkbox
                        :checked="checkboxChecked(room.inventory)"
                        :id="'checkbox_inventory_'+room.id"
                        :name="'checkbox_inventory_'+room.id"
                        :key="updateChecks"
                        @change="updateInventory(room.id,room.inventory)"
                        switch
                        :disabled="room.blocked"
                        >
                    </b-form-checkbox>
                </td>
                <td>
                    <b-form-checkbox
                        :checked="checkboxChecked(room.see_in_rates)"
                        :id="'checkbox_see_'+room.id"
                        :name="'checkbox_see_'+room.id"
                        :key="updateChecks"
                        @change="updateSeeInRates(room.id,room.see_in_rates)"
                        switch
                        :disabled="room.blocked"
                        >
                    </b-form-checkbox>
                </td>
                <td>
                    <b-form-checkbox
                        :checked="checkboxChecked(room.state)"
                        :id="'checkbox_'+room.id"
                        :name="'checkbox_'+room.id"
                        :key="updateChecks"
                        @change="changeState(room)"
                        switch>
                    </b-form-checkbox>
                </td>
                <td>
                    <b-tooltip :target="'progress'+room.id" title="Tooltip content" placement="right"
                               v-if="room.progress_bar_value>0">
                        <ul style="list-style-type: none; padding: 0px;" class="text-left">
                            {{ $t('global.progress_bars_names.title') }}
                            <li v-for="progress_bar in progress_bars_names">
                                {{ $t('global.progress_bars_names.'+progress_bar.name) }}
                                <font-awesome-icon :icon="['fas', 'check']" class="ml-1 p-0" style="color: #4dbd74;"
                                                   v-show="showIconProgressBar(progress_bar.name,room.progress_bars)"/>
                            </li>
                        </ul>
                    </b-tooltip>
                    <b-progress :id="'progress'+room.id" :max="max" style="background-color:#3c5e79">
                        <b-progress-bar :value="room.progress_bar_value"
                                        :variant="checkProgressBar(room.progress_bar_value)">
                            <div class="text-center">
                                {{ room.progress_bar_value}}%
                            </div>
                        </b-progress-bar>
                    </b-progress>
                </td>
            </tr>
            </tbody>
        </table>
        <!--        <pagination :align="'center'" :data="pagination" :limit="3" @pagination-change-page="getRooms"></pagination>-->
        <b-modal :title="roomName" centered ref="my-modal" size="sm">
            <p class="text-center">{{$t('global.message_delete')}}</p>

            <div slot="modal-footer">
                <button @click="remove(room)" class="btn btn-success">{{$t('global.buttons.accept')}}</button>
                <button @click="hideModal()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
            </div>
        </b-modal>


        <b-modal title="Coincidencias de la Habitación" ref="my-modal-uses" hide-footer size="lg">

            <h4>Se encontró que la habitación está siendo utilizada en los siguientes paquetes.</h4>

            <div class="left">
                <button v-if="!see_confirm" class="btn btn-success" :disabled="loading" type="button"
                        @click="will_send_report()">
                    <span>Reportar</span>
                </button>
                <div class="alert alert-warning" v-if="see_message">
                    <i class="fa fa-info-circle"></i> La habitación ya fué reportada por favor espere las 48 hrs y será
                    desactivada automáticamente.
                </div>
                <div class="alert alert-warning" v-if="see_confirm">
                    <i class="fa fa-info-circle"></i> Notificará a los usuarios correspondientes y la habitación se
                    desactivará automáticamente en 48 hrs.
                </div>
                <button v-if="see_confirm" class="btn btn-success" :disabled="loading" type="button"
                        @click="send_report()">
                    <span>Confirmar</span>
                </button>
                <br>
                <br>
            </div>

            <table-client :columns="table_used_packages.columns" :data="used_rooms.packages"
                          :options="table_options_used_packages" id="dataTable"
                          theme="bootstrap4">
                <div class="table-supplement" slot="package" slot-scope="props">
                    <span v-if="props.row.code">
                                {{ props.row.id }} - [{{ props.row.code }}] -
                            </span>
                    <span v-if="!(props.row.code)">
                                <span v-if="props.row.extension=='1'">[E{{ props.row.id }}] - </span>
                                <span v-if="props.row.extension=='0'">[P{{ props.row.id }}] - </span>
                            </span>
                    <span v-html="props.row.translations[0].name"></span>
                </div>
                <div class="table-supplement" slot="plan_rate" slot-scope="props">
                    [{{ props.row.plan_rates[0].service_type.abbreviation}}] - {{props.row.plan_rates[0].name}}
                </div>
                <div class="table-supplement" slot="categories" slot-scope="props">
                    <div class="badge badge-primary bag-category mr-1" v-if="category.services.length>0"
                         v-for="category in props.row.plan_rates[0].plan_rate_categories">
                        {{category.category.translations[0].value}} ({{ category.services.length }})
                    </div>
                </div>
                <div class="table-supplement" slot="period" slot-scope="props">
                    {{props.row.plan_rates[0].date_from | formatDate}} - {{props.row.plan_rates[0].date_to |
                    formatDate}}
                </div>
            </table-client>

        </b-modal>

    </div>
</template>
<script>
    import TableServer from '../../../../components/TableServer'
    import TableClient from '../../../../components/TableClient'
    import { API } from '../../../../api'
    import BFormCheckbox from 'bootstrap-vue/es/components/form-checkbox/form-checkbox'
    import BModal from 'bootstrap-vue/es/components/modal/modal'
    import Progress from 'bootstrap-vue/src/components/progress/progress'
    import ProgressBar from 'bootstrap-vue/src/components/progress/progress-bar'
    import Tooltip from 'bootstrap-vue/src/components/tooltip/tooltip'
    import datePicker from 'vue-bootstrap-datetimepicker'

    export default {
        components: {
            datePicker,
            'table-server': TableServer,
            'table-client': TableClient,
            BFormCheckbox,
            BModal,
            'b-progress': Progress,
            'b-progress-bar': ProgressBar,
            'b-tooltip': Tooltip
        },
        data: () => {
            return {
                roomName: '',
                room_id: '',
                rooms: [],
                max: 100,
                limit: 10,
                page: 1,
                updateChecks: 1,
                changeLanguage: false,
                progress_bars_names: [
                    { name: 'room_progress_descriptions' },
                    { name: 'room_progress_channels' },
                    { name: 'room_progress_gallery' },
                    { name: 'hotel_progress_rooms_details' },
                    { name: 'room_progress_inventories' }
                ],
                pagination: {},
                table: {
                    columns: ['actions', 'name', 'order', 'state', 'advance']
                },
                loading: false,
                table_used_packages: {
                    columns: ['package', 'plan_rate', 'categories', 'period']
                },
                used_rooms: {
                    room_id: '',
                    room_name: '',
                    packages: []
                },
                see_message: false,
                see_confirm: false,
                room_choose: '',
            }
        },
        mounted () {
            this.$i18n.locale = localStorage.getItem('lang')
        },
        async created () {
            this.$parent.$parent.$on('langChange', (payload) => {
                this.getRooms(this.page)
                this.changeLanguage = true
            })
            if (!this.changeLanguage) {
                this.getRooms(this.page)
            }
        },
        computed: {
            tableOptions: function () {
                return {
                    actions: this.$i18n.t('global.table.actions'),
                    name: this.$i18n.t('hotelsmanagehotelrooms.room_name'),
                    order: 'Orden',
                    inventory: 'Inventory',
                    see_in_rates: 'Tarifas',
                    state: this.$i18n.t('global.status'),
                    advance: this.$i18n.t('hotelsmanagehotelrooms.advance')
                }
            },
            table_options_used_packages: function () {
                return {
                    headings: {
                        package: 'Paquete',
                        plan_rate: 'Plan Tarifario',
                        categories: 'Categorías',
                        period: 'Periodo'
                    },
                    sortable: [],
                    filterable: []
                }
            },
        },
        methods: {
            will_send_report () {
                this.see_message = false
                this.loading = true

                API({
                    method: 'get',
                    url: 'deactivatable/entity?entity=App/Room&object_id=' + this.used_rooms.room_id
                })
                    .then((result) => {
                        if (result.data.success) {
                            if (result.data.data.length > 0) {
                                this.see_message = true
                                let me = this
                                setTimeout(function () {
                                    me.see_message = false
                                }, 5000)
                            } else {
                                this.see_confirm = true
                            }
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.rooms'),
                                text: this.$t('global.error.messages.information_error')
                            })
                        }
                        this.loading = false
                    })
            },
            send_report () {

                this.loading = true

                let packages_ = []

                this.used_rooms.packages.forEach(p => {
                    let obj = {}
                    if (p.code) {
                        obj.package = p.id + ' - [' + p.code + '] - '
                    } else {
                        if (p.extension === '1') {
                            obj.package = '[E' + p.id + '] - '
                        } else {
                            obj.package = '[P' + p.id + '] - '
                        }
                    }
                    obj.package += p.translations[0].name

                    obj.plan_rate = '[' + p.plan_rates[0].service_type.abbreviation + '] - ' + p.plan_rates[0].name

                    obj.categories = []
                    p.plan_rates[0].plan_rate_categories.forEach(c => {
                        if (c.services.length > 0) {
                            obj.categories.push(c.category.translations[0].value + ' (' + c.services.length + ')')
                        }
                    })

                    obj.period = this.formatDate(p.plan_rates[0].date_from) + ' - ' + this.formatDate(p.plan_rates[0].date_to)

                    packages_.push(obj)

                })

                let data = {
                    room_id: this.used_rooms.room_id,
                    room_name: this.used_rooms.room_name,
                    packages: packages_
                }

                API({
                    method: 'post',
                    url: 'rooms/' + data.room_id + '/uses/report',
                    data: { data: data }
                })
                    .then((result) => {
                        if (result.data.success) {
                            this.see_confirm = false
                            this.hideModal()
                            this.used_rooms.packages = []
                            this.used_rooms.room_id = ''
                            this.used_rooms.room_name = ''
                            this.$notify({
                                group: 'main',
                                type: 'success',
                                title: this.$t('global.modules.rooms'),
                                text: 'Enviado correctamente'
                            })
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.rooms'),
                                text: this.$t('global.error.messages.information_error')
                            })
                        }
                        this.loading = false
                    })

            },
            update_order (room) {
                let me = this
                setTimeout(function () {
                    me.loading = true
                    API({
                        method: 'put',
                        url: 'rooms/' + room.id + '/order',
                        data: { order: parseInt(room.order) }
                    })
                        .then((result) => {
                            if (result.data.success === true) {
                                me.getRooms(me.page)
                            } else {
                                me.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: me.$t('global.modules.rooms'),
                                    text: me.$t('hotelsmanagehotelrooms.error.messages.information_error')
                                })
                                me.loading = false
                            }
                        })
                }, 250)

            },
            showIconProgressBar: function (progress_bar_name, progress_bar_success) {

                for (let i = 0; i < progress_bar_success.length; i++) {
                    if (progress_bar_success[i].slug === progress_bar_name) {
                        return true
                    }
                }
                return false
            },
            checkProgressBar: function (value) {
                if (value >= 0 && value <= 30) {
                    return 'danger'
                }
                if (value > 30 && value <= 70) {
                    return 'warning'
                }
                if (value > 70 && value <= 100) {
                    return 'success'
                }
            },
            showModal (room) {
                this.room_choose = room
                this.room_id = room.id
                this.roomName = room.translations[0].value
                this.$refs['my-modal'].show()
            },
            hideModal () {
                this.room_choose = ''
                this.$refs['my-modal'].hide()
                this.$refs['my-modal-uses'].hide()
            },
            checkboxChecked: function (room_state) {
                if (room_state) {
                    return 'true'
                } else {
                    return 'false'
                }
            },
            updateSeeInRates: function (room_id, see_in_rates) {
                API({
                    method: 'put',
                    url: 'rooms/update/' + room_id + '/see_in_rates',
                    data: { see_in_rates: see_in_rates }
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.getRooms(this.page)

                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.rooms'),
                                text: this.$t('hotelsmanagehotelrooms.error.messages.information_error')
                            })
                        }
                    })
            },
            changeState: function (room) {
                API({
                    method: 'put',
                    url: 'rooms/update/' + room.id + '/state',
                    data: { state: room.state }
                })
                    .then((result) => {
                        if (result.data.success !== true) {
                            this.$refs['my-modal-uses'].show()
                            this.used_rooms.packages = result.data.uses
                            this.used_rooms.room_id = room.id
                            this.used_rooms.room_name = '[' + room.id + '] ' + room.translations[0].value
                        }
                        this.getRooms(this.page)
                    }).catch((e) => {
                    console.log(e)
                    this.getRooms(this.page)
                })
            },
            updateInventory: function (room_id, check_inventory) {
                API({
                    method: 'put',
                    url: 'update/check_inventory/room',
                    data: { room_id: room_id, check_inventory: check_inventory }
                })
                    .then((result) => {

                        this.getRooms(this.page)

                    }).catch((e) => {
                    console.log(e)
                })
            },
            getRooms: function (page = 1) {
                this.page = page
                API({
                    method: 'GET',
                    url: 'hotel/' + this.$route.params.hotel_id + '/rooms?token=' +
                        window.localStorage.getItem('access_token') + '&lang=' +
                        localStorage.getItem('lang') + '&page=' + page + '&limit=' + this.limit
                })
                    .then((result) => {
                        this.loading = false

                        // if (result.data) {
                        //     result.data = result.data.map(room => {
                        //         const hasHyperguestChannel = room.channels && room.channels.some(channel =>
                        //             channel.name === "HYPERGUEST" &&
                        //             channel.pivot.state === 1 &&
                        //             channel.pivot.type === "2"
                        //         );

                        //         return {
                        //             ...room,
                        //             blocked: !!hasHyperguestChannel
                        //         };
                        //     });
                        // }
                        this.rooms = result.data
                        this.pagination = result.data
                        this.updateChecks += 1
                        this.$forceUpdate()
                    }).catch(() => {
                    this.loading = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('hotelsmanagehotelrooms.error.messages.name'),
                        text: this.$t('hotelsmanagehotelrooms.error.messages.connection_error')
                    })
                })
            },
            remove: function () {

                let room = this.room_choose

                API({
                    method: 'DELETE',
                    url: 'rooms/' + this.room_id
                })
                    .then((result) => {

                        this.$refs['my-modal'].hide()

                        if (result.data.success !== true) {
                            this.$refs['my-modal-uses'].show()
                            this.used_rooms.packages = result.data.uses
                            this.used_rooms.room_id = room.id
                            this.used_rooms.room_name = '[' + room.id + '] ' + room.translations[0].value
                        }
                        this.getRooms(this.page)
                    }).catch(() => {
                    this.hideModal()
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('hotelsmanagehotelrooms.error.messages.name'),
                        text: this.$t('hotelsmanagehotelrooms.error.messages.connection_error')
                    })
                })
            },
            formatDate: function (_date) {
                if (_date == undefined) {
                    // console.log('fecha no parseada: ' + _date)
                    return
                }
                _date = _date.split('-')
                _date = _date[2] + '/' + _date[1] + '/' + _date[0]
                return _date
            },

        },

        filters: {
            formatDate: function (_date) {
                if (_date == undefined) {
                    // console.log('fecha no parseada: ' + _date)
                    return
                }
                _date = _date.split('-')
                _date = _date[2] + '/' + _date[1] + '/' + _date[0]
                return _date
            }
        }
    }
</script>
<style>
    .custom-control-input:checked ~ .custom-control-label::before {
        border-color: #3ea662;
        background-color: #3a9d5d;
    }
</style>


