<template>
    <div class="tab-content container-fluid" style="padding: 20px;">
        <div v-show="!loading">
            <div class="row col-lg-12">
                <div class="col-2" v-show="getAllotment == 1">
                    <select @change="getInventoryByTab()" class="form-control" id="client_id" name="client_id"
                            v-model="client_id">
                        <option value="">Ninguno</option>
                        <option :value="client.client.id" v-for="client in clients">{{ client.client.name }}</option>
                    </select>
                </div>
            </div>
            <div class=" row col-12" style="padding:10px" v-show="showTable">
                <div class="col-2">
                    <select @change="getInventoryByTab()" class="form-control" id="month" name="month" v-model="month">
                        <option :value="month.id" v-for="month in months">{{
                                $t('global.months.' + month.name)
                            }}
                        </option>
                    </select>
                </div>
                <div class="col-2">
                    <select @change="getInventoryByTab()" class="form-control" id="year" name="year" v-model="year">
                        <option :value="year" v-for="year in years">{{ year }}</option>
                    </select>
                </div>
                <div class="row col-4">
                    <div class="col-lg-4">
                        <label style="padding-top: 5px;">{{ $t('global.modules.rooms') }}</label>
                    </div>
                    <div class="col-lg-8">
                        <select @change="getInventoryByTab()" class="form-control" id="room_id" name="room_id"
                                v-model="room_id">
                            <option value="">{{ $t('global.days.everyone') }}</option>
                            <option :value="room.id" v-for="room in rooms">{{ room.translations[0].value }}</option>
                        </select>
                    </div>
                </div>
                <div class="row col-4">
                    <div class="col-lg-4">
                        <label style="padding-top: 5px;">{{ $t('global.modules.rates') }}</label>
                    </div>
                    <div class="col-lg-8">
                        <select @change="getInventoryByTab()" class="form-control" id="rate_plan_id" name="rate_plan_id"
                                v-model="rate_plan_id">
                            <option value="">{{ $t('global.days.everyone') }}</option>
                            <option :value="rate_plan.id" v-for="rate_plan in rates_plans">{{ rate_plan.name }}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="table-responsive responsive-inventory" v-show="showTable">
                <table class="VueTables__table table table-bordered table-hover text-center">
                    <thead>
                    <tr>
                        <th>{{ $t('hotelsmanagehotelinventories.rate') }}</th>
                        <th style="font-size: 13px" v-show="client_id!=''">Allotment</th>
                        <th :key="index" v-for="index in days">
                            <p>
                                {{ showDay(index) }}<br>
                                {{ index }}
                            </p>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr :key="inventory_index" style="text-decoration: none; text-align: center"
                        v-for="(item, inventory_index) in inventories">
                        <td>
                            <div>
                                <font-awesome-icon :icon="['fas', 'bed']"/>
                                {{ item.rate_name }}
                            </div>
                            <span class="badge badge-primary mb-2">{{ item.bag_name }}</span>
                        </td>
                        <td v-show="client_id!=''">
                            <input :id="'num_days_blocked_'+item.rate_plan_rooms_id" type="text"
                                   v-model="num_days_blocked[item.rate_plan_rooms_id]"
                                   @keyup.enter="blockedDayByClient(item.rate_plan_rooms_id)" style="width:30px;">
                        </td>
                        <td :class="{'row_selected': inventories[inventory_index].inventory[day_index].class_selected,
                                 'row_normal': inventories[inventory_index].inventory[day_index].class_normal,
                                 'row_disabled': inventories[inventory_index].inventory[day_index].class_locked,
                                 'row_intermediate': inventories[inventory_index].inventory[day_index].class_intermediate
                                 }"
                            @click="editCell(inventory_index, day_index)"
                            @mouseover="selectRow(inventory_index, day_index)"
                            v-for="(day, day_index) in item.inventory">
                            <span v-if="inventories[inventory_index].inventory[day_index].inventory_num >= 0">
                                {{ inventories[inventory_index].inventory[day_index].inventory_num }}
                            </span>
                            <span v-if="inventories[inventory_index].inventory[day_index].inventory_num < 0">
                                <div
                                    style="height: 4px;width: 5px;background: red;border-radius: 4px;float: right;margin-top: -2px;"></div>
                                0
                            </span>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="form-inline col-sm-12" style="padding: 15px" v-show="showTable">
                <div class="form-row">
                    <input class="form-control col-sm-3" id="inventory_num" name="inventory_num" type="text"
                           v-model="inventory_num"
                           v-validate="'required|numeric'">
                    <label class="" for="inventory_num" style="margin-left: 5px">{{
                            $t('hotelsmanagehotelinventories.inventory')
                        }}</label>
                </div>
                <div class="bg-danger" style="border-radius: 2px;" v-show="errors.has('inventory_num')">
                    <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                       style="margin-left: 5px;"/>
                    <span>{{ errors.first('inventory_num') }}</span>
                </div>
            </div>
            <div class="row col-sm-12" style="padding: 10px;" v-show="showTable">
                <div class="col-sm-5">
                    <button @click="addInventory()" class="btn btn-success btn-block">{{
                            $t('hotelsmanagehotelinventories.add_inventory')
                        }}
                    </button>
                </div>
                <div class="col-sm-2">
                    <button @click="lockedDays()" class="btn btn-success btn-block">{{
                            $t('hotelsmanagehotelinventories.blocked_days')
                        }}
                    </button>
                </div>
                <div class="col-sm-5">
                    <button @click="enabledDays()" class="btn btn-success btn-block">{{
                            $t('hotelsmanagehotelinventories.enabled_days')
                        }}
                    </button>
                </div>
            </div>
            <div class="row col-sm-12" style="padding: 10px;" v-show="showTable">
                <div class="col-sm-6">
                    <router-link :to="getRouteAddInventoryByDateRange()"
                                 class="btn btn-success btn-block">
                        {{ $t('hotelsmanagehotelinventories.add_by_range_dates') }}
                    </router-link>
                </div>
                <div class="col-sm-6">
                    <router-link :to="getRouteBlockedInventoryByDateRange()"
                                 class="btn btn-success btn-block">
                        {{ $t('hotelsmanagehotelinventories.blocked_by_range_dates') }}
                    </router-link>
                </div>
            </div>
            <div class="container" v-show="showMessage">
                <h4 class="text-center">{{ $t('hotelsmanagehotelinventories.no_policies') }}</h4>
            </div>
        </div>
        <div class="table-loading text-center" v-show="loading">
            <img alt="loading" height="51px" src="/images/loading.svg"/>
        </div>
    </div>
</template>
<script>
import { API } from '../../../../api'
import moment from 'moment'

export default {
    components: {},
    data: () => {
        return {
            days: 0,
            rate_plan_id: '',
            room_id: '',
            client_id: '',
            clients: [],
            num_days_blocked: [],
            inventoriesSelected: [],
            classSelected: '',
            inventories: [],
            rooms: [],
            rates_plans: [],
            month: '1',
            loading: false,
            showTable: false,
            showMessage: false,
            inventory_num: null,
            ctrlActive: false,
            min_position_x: 0,
            min_position_y: 0,
            max_position_x: 0,
            max_position_y: 0,
            state_change: false,
            months: [
                {
                    id: '1',
                    name: 'january',
                },
                {
                    id: '2',
                    name: 'february',
                },
                {
                    id: '3',
                    name: 'march',
                },
                {
                    id: '4',
                    name: 'april',
                },
                {
                    id: '5',
                    name: 'may',
                },
                {
                    id: '6',
                    name: 'june',
                },
                {
                    id: '7',
                    name: 'july',
                },
                {
                    id: '8',
                    name: 'august',
                },
                {
                    id: '9',
                    name: 'september',
                },
                {
                    id: '10',
                    name: 'october',
                },
                {
                    id: '11',
                    name: 'november',
                },
                {
                    id: '12',
                    name: 'december',
                },

            ],
            year: '2019',
            years: ['2019', '2020', '2021', '2022', '2023', '2024', '2025', '2026'],
            msg: '',
        }
    },
    mounted () {
        let f = new Date()

        this.month = f.getMonth() + 1

        this.year = f.getFullYear()

        window.addEventListener('mousedown', function (e) {
            this.ctrlActive = true
            this.state_change = true
        }.bind(this))
        window.addEventListener('mouseup', function (e) {
            this.ctrlActive = false
            this.state_change = false
            this.min_position_x = 0
            this.min_position_y = 0
            this.max_position_x = 0
            this.max_position_y = 0
        }.bind(this))
        this.getInventoryByTab()
        this.getRoomsByHotel()
        this.getRatesPlansByHotel()
        if (localStorage.getItem('allotment') == 1) {
            this.getClientsByHotel()
        }
    },
    computed: {
        getAllotment: function () {
            return localStorage.getItem('allotment')
        },
    },
    created () {
    },
    methods: {
        cleanArrayNumDaysBlocked: function () {
            for (let i = 0; i < this.num_days_blocked.length; i++) {

                this.num_days_blocked[i] = ''

            }

        },
        blockedDaysByClient: function () {
            this.cleanArrayNumDaysBlocked()
            for (let j = 0; j < this.clients.length; j++) {
                if (this.clients[j].client_id == this.client_id) {
                    if (this.clients[j]['client']['allotments'].length > 0) {
                        for (let k = 0; k < this.clients[j]['client']['allotments'].length; k++) {
                            for (let i = 0; i < this.inventories.length; i++) {

                                if (this.clients[j]['client']['allotments'][k].rate_plan_rooms_id ==
                                    this.inventories[i].rate_plan_rooms_id) {

                                    this.num_days_blocked[this.clients[j]['client']['allotments'][k].rate_plan_rooms_id] = this.clients[j]['client']['allotments'][k].num_days_blocked

                                    let date_end = moment().add(this.clients[j]['client']['allotments'][k].num_days_blocked, 'days').format('YYYY-MM-DD')

                                    Object.keys(this.inventories[i].inventory).some((objKey) => {
                                        this.inventories[i].inventory[objKey].class_normal = false
                                        this.inventories[i].inventory[objKey].class_locked = true

                                        if (this.inventories[i].inventory[objKey].date == date_end) {
                                            return true
                                        }
                                    })
                                }
                            }
                        }
                    }
                }
            }
        },
        blockedDayByClient: function (rate_plan_rooms_id) {

            let num_days_blocked = document.getElementById('num_days_blocked_' + rate_plan_rooms_id).value

            API({
                method: 'put',
                url: 'allotments/update/num_days_blocked',
                data: {
                    rate_plan_rooms_id: rate_plan_rooms_id,
                    num_days_blocked: num_days_blocked,
                    client_id: this.client_id,
                },
            }).then((result) => {
                if (result.data.success === true) {
                    this.getClientsByHotel()
                    this.getInventoryByTab()
                } else {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.clients'),
                        text: this.$t('hotelsmanagehotelinventories.error.messages.information_error'),
                    })
                }
            }).catch((e) => {
                console.log(e)
            })
        },
        getClientsByHotel: function () {
            API({
                method: 'get',
                url: 'clients/by/hotel?hotel_id=' + this.$route.params.hotel_id + '&year=' + this.year,
            }).then((result) => {
                if (result.data.success === true) {
                    this.clients = result.data.clients
                } else {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.clients'),
                        text: this.$t('hotelsmanagehotelinventories.error.messages.information_error'),
                    })
                }
            }).catch((e) => {
                console.log(e)
            })
        },
        showDay: function (day) {
            let connector_month = ''
            if (this.month < 10) {
                connector_month = '0'
            }
            let connector_day = ''
            if (day < 10) {
                connector_day = '0'
            }
            let date = this.year + '/' + connector_month + this.month + '/' + connector_day + day
            let date_javascript = new Date(date)
            let day_javascript = date_javascript.getDay()
            if (day_javascript === 0) {
                return 'do'
            }
            if (day_javascript === 1) {
                return 'lu'
            }
            if (day_javascript === 2) {
                return 'ma'
            }
            if (day_javascript === 3) {
                return 'mi'
            }
            if (day_javascript === 4) {
                return 'ju'
            }
            if (day_javascript === 5) {
                return 'vi'
            }
            if (day_javascript === 6) {
                return 'sa'
            }
        },
        getRoomsByHotel: function () {
            API({
                method: 'post',
                url: 'rooms/by/hotel',
                data: {
                    hotel_id: this.$route.params.hotel_id,
                    lang: localStorage.getItem('lang'),
                },
            }).then((result) => {
                if (result.data.success === true) {
                    this.rooms = result.data.data
                } else {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.rooms'),
                        text: this.$t('hotelsmanagehotelinventories.error.messages.information_error'),
                    })
                }
            }).catch(() => {
                this.showTable = false
                this.showMessage = true
            })
        },
        getRatesPlansByHotel: function () {
            API({
                method: 'get',
                url: 'rates/plans/by/hotel',
                params: {
                    hotel_id: this.$route.params.hotel_id,
                },
            }).then((result) => {
                if (result.data.success === true) {
                    this.rates_plans = result.data.data
                } else {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.rooms'),
                        text: this.$t('hotelsmanagehotelinventories.error.messages.information_error'),
                    })
                }
            }).catch(() => {
                this.showTable = false
                this.showMessage = true
            })
        },
        getInventoryByTab: function () {
            if (this.$route.name === 'InventoryLayoutFreeSale') {

                this.getInventory(0)
            }
            if (this.$route.name === 'InventoryLayoutAllotments') {

                this.getInventory(1)
            }
        },
        getRouteAddInventoryByDateRange: function () {
            return '/hotels/' + this.$route.params.hotel_id + '/manage_hotel/add_inventory_by_date_range'
        },
        getRouteBlockedInventoryByDateRange: function () {
            return '/hotels/' + this.$route.params.hotel_id + '/manage_hotel/blocked_inventory_by_date_range'
        },
        storeInventory: function () {
            API({
                method: 'post',
                url: 'inventory/add',
                data: { hotel_id: this.$route.params.hotel_id, inventories_selected: this.inventoriesSelected },
            }).then((result) => {
                if (result.data.success === true) {
                    this.loading = false
                    this.inventoriesSelected = []
                    this.getInventoryByTab()
                } else {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.rooms'),
                        text: this.$t('hotelsmanagehotelinventories.error.messages.information_error'),
                    })
                }
            })
        },
        storeInventoryLockedDays: function () {
            API({
                method: 'post',
                url: 'inventory/locked/days',
                data: { inventories_selected: this.inventoriesSelected },
            }).then((result) => {
                if (result.data.success === true) {
                    this.loading = false
                    this.inventoriesSelected = []
                    this.getInventoryByTab()

                } else {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.rooms'),
                        text: this.$t('hotelsmanagehotelinventories.error.messages.information_error'),
                    })
                }
            })
        },
        storeInventoryEnabledDays: function () {
            API({
                method: 'post',
                url: 'inventory/enabled/days',
                data: { inventories_selected: this.inventoriesSelected },
            }).then((result) => {
                if (result.data.success === true) {
                    this.loading = false
                    this.inventoriesSelected = []
                    this.getInventoryByTab()
                } else {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.rooms'),
                        text: this.$t('hotelsmanagehotelinventories.error.messages.information_error'),
                    })
                }
            })
        },
        editCell: function (inventory_index, day_index) {
            this.clearSelection()
            this.inventories[inventory_index].inventory[day_index].selected = true
            this.inventories[inventory_index].inventory[day_index]['selected'] = true
            this.inventories[inventory_index].inventory[day_index]['class_selected'] = true

            if (this.inventories[inventory_index].inventory[day_index]['locked']) {
                this.inventories[inventory_index].inventory[day_index]['class_locked'] = false
                this.inventories[inventory_index].inventory[day_index]['class_intermediate'] = true
                this.inventories[inventory_index].inventory[day_index]['class_normal'] = false
                this.inventories[inventory_index].inventory[day_index]['class_selected'] = false
            } else {
                this.inventories[inventory_index].inventory[day_index]['class_normal'] = false
                this.inventories[inventory_index].inventory[day_index]['class_intermediate'] = false
                this.inventories[inventory_index].inventory[day_index]['class_locked'] = false
            }
        },
        getInventory: function (allotment) {

            this.blockPage = true
            API({
                method: 'post',
                url: 'inventory/hotel',
                data: {
                    month: this.month,
                    year: this.year,
                    room_id: this.room_id,
                    rate_plan_id: this.rate_plan_id,
                    hotel_id: this.$route.params.hotel_id,
                    lang: localStorage.getItem('lang'),
                    allotment: allotment,
                    client_id: this.client_id,
                },
            }).then((result) => {
                this.blockPage = false
                if (result.data.success === true) {
                    this.showTable = true
                    this.showMessage = false
                    this.inventories = result.data.inventories
                    this.days = result.data.days
                    if (result.data.inventories.length > 0 && localStorage.getItem('allotment') == 1) {
                        this.blockedDaysByClient()
                    }
                    if (result.data.inventories.length === 0) {
                        this.showTable = false
                        this.showMessage = true
                    }

                } else {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.rooms'),
                        text: this.$t('hotelsmanagehotelinventories.error.messages.information_error'),
                    })
                }
            }).catch((e) => {
                this.blockPage = false
                console.log(e)
            })
        },
        addInventory: function () {
            this.$validator.validateAll().then((result) => {
                if (result) {
                    this.loading = true
                    for (let i = 0; i < this.inventories.length; i++) {
                        Object.keys(this.inventories[i].inventory).forEach((objKey) => {
                            if (this.inventories[i].inventory[objKey].selected) {
                                this.inventories[i].inventory[objKey].inventory_num = this.inventory_num
                                this.inventoriesSelected.push(this.inventories[i].inventory[objKey])
                            }
                        })
                    }
                    this.storeInventory()

                } else {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.inventories'),
                        text: this.$t('hotelsmanagehotelinventories.error.messages.information_complete'),
                    })
                }
            })
        },
        lockedDays: function () {
            this.loading = true
            for (let i = 0; i < this.inventories.length; i++) {
                Object.keys(this.inventories[i].inventory).forEach((objKey) => {
                    if (this.inventories[i].inventory[objKey].selected) {

                        this.inventoriesSelected.push(this.inventories[i].inventory[objKey])

                    }
                })
            }
            this.storeInventoryLockedDays()
        },
        enabledDays: function () {
            this.loading = true
            for (let i = 0; i < this.inventories.length; i++) {
                Object.keys(this.inventories[i].inventory).forEach((objKey) => {
                    if (this.inventories[i].inventory[objKey].selected) {
                        this.inventoriesSelected.push(this.inventories[i].inventory[objKey])
                    }
                })
            }
            this.storeInventoryEnabledDays()
        },
        selectRow: function (inventory_index, day_index) {
            if (this.ctrlActive) {
                if (this.state_change) {
                    this.max_position_x = parseInt(inventory_index)
                    this.max_position_y = parseInt(day_index)
                    this.selectArea()
                } else {
                    this.min_position_x = parseInt(inventory_index)
                    this.min_position_y = parseInt(day_index)
                    this.max_position_x = parseInt(inventory_index)
                    this.max_position_y = parseInt(day_index)
                    this.state_change = true
                    this.selectArea()
                }
            } else {
                this.min_position_x = parseInt(inventory_index)
                this.min_position_y = parseInt(day_index)
                this.max_position_x = parseInt(inventory_index)
                this.max_position_y = parseInt(day_index)
            }
        },
        selectArea: function () {
            for (let i = this.min_position_x; i <= this.max_position_x; i++) {
                for (let j = this.min_position_y; j <= this.max_position_y; j++) {

                    if (this.inventories[i].inventory[j]['selected']) {

                        if (this.inventories[i].inventory[j]['locked']) {
                            this.inventories[i].inventory[j]['class_locked'] = false
                            this.inventories[i].inventory[j]['class_intermediate'] = true
                            this.inventories[i].inventory[j]['class_normal'] = false
                        } else {
                            this.inventories[i].inventory[j]['class_normal'] = false
                            this.inventories[i].inventory[j]['class_selected'] = true
                        }
                    } else {
                        this.inventories[i].inventory[j]['selected'] = true
                        this.inventories[i].inventory[j]['class_selected'] = true

                        if (this.inventories[i].inventory[j]['locked']) {
                            this.inventories[i].inventory[j]['class_locked'] = false
                            this.inventories[i].inventory[j]['class_intermediate'] = true
                            this.inventories[i].inventory[j]['class_normal'] = false
                            this.inventories[i].inventory[j]['class_selected'] = false
                        } else {
                            this.inventories[i].inventory[j]['class_normal'] = false
                            this.inventories[i].inventory[j]['class_intermediate'] = false
                            this.inventories[i].inventory[j]['class_locked'] = false
                        }
                    }
                }
            }
        },
        clearSelection: function () {
            for (let i = 0; i < this.inventories.length; i++) {
                Object.keys(this.inventories[i].inventory).forEach((objKey) => {
                    if (this.inventories[i].inventory[objKey].selected) {

                        this.inventories[i].inventory[objKey].selected = false
                        this.inventories[i].inventory[objKey].class_selected = false

                        if (this.inventories[i].inventory[objKey].locked) {
                            this.inventories[i].inventory[objKey].class_locked = true
                            this.inventories[i].inventory[objKey].class_normal = false
                            this.inventories[i].inventory[objKey].class_intermediate = false
                        } else {
                            this.inventories[i].inventory[objKey].class_locked = false
                            this.inventories[i].inventory[objKey].class_normal = true
                            this.inventories[i].inventory[objKey].class_intermediate = false
                        }
                    }
                })
            }
            this.min_position_x = 0
            this.min_position_y = 0
            this.max_position_x = 0
            this.max_position_y = 0
        },
    },
}
</script>

<style>
.row_selected {
    background-color: #4dbd74;
    color: #ffffff;
    border: white;
    width: 30px;

}

.row_disabled {
    background-color: #cb2027;
    color: #ffffff;
    border: white;
    width: 30px;
}

.row_normal {
    background-color: #ffffff;
    color: black;
    border: white;
    width: 30px;
}

.row_intermediate {
    background-color: #0b4d75;
    color: white;
    border: white;
    width: 30px;
}

.table td {
    padding: 0px;
}

body {
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    -khtml-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
}
</style>

