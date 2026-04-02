<template>
    <div class="container-fluid tab-content" style="padding: 20px;">
        <div>
            <div class=" row col-12" style="padding:10px">
                <div class="col-2">
                    <select @change="getInventoryByTab()" id="month" name="month" v-model="month" class="form-control">
                        <option :value="month.id" v-for="month in months">{{ $t('global.months.'+month.name) }}</option>
                    </select>
                </div>
                <div class="col-2">
                    <select @change="getInventoryByTab()" id="year" name="year" v-model="year" class="form-control">
                        <option :value="year" v-for="year in years">{{ year }}</option>
                    </select>
                </div>
                <div class="col-2">
                    <select @change="getInventoryByTab()" id="allotment" name="allotment" v-model="allotment"
                            class="form-control">
                        <option value="0" selected>FreeSale</option>
                        <option value="1" selected>Allotments / Groups</option>
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
                <div class="row col-2" style="padding-right: 0;">
                    <div class="col-lg-4">
                        <label style="padding-top: 5px;">{{ $t('global.modules.rates') }}</label>
                    </div>
                    <div class="col-lg-8" style="padding-right: 0;">
                        <select @change="getInventoryByTab()" class="form-control" id="rate_plan_id" name="rate_plan_id"
                                v-model="rate_plan_id">
                            <option value="">{{ $t('global.days.everyone') }}</option>
                            <option :value="rate_plan.id" v-for="rate_plan in rates_plans">{{ rate_plan.name }}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="table-responsive responsive-inventory" v-show="showTable">
                <table class="VueTables__table table table-striped table-bordered table-hover text-center">
                    <thead>
                    <tr>
                        <th>{{ $t('hotelsmanagehotelinventories.rate') }}</th>
                        <th :key="index" v-for="index in days">
                            <p>
                                {{ showDay(index) }}<br>
                                {{ index }}
                            </p>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr :key="inventory_index" v-for="(item, inventory_index) in inventories"
                        style="text-decoration: none; text-align: center">
                        <td>{{ item.rate_name }}</td>
                        <td :class="{'row_selected': inventories[inventory_index].inventory[day_index].class_selected,
                                 'row_normal': inventories[inventory_index].inventory[day_index].class_normal,
                                 'row_disabled': inventories[inventory_index].inventory[day_index].class_locked,
                                 'row_intermediate': inventories[inventory_index].inventory[day_index].class_intermediate
                                 }"
                            @click="selectCell(inventory_index, day_index)"
                            v-for="(day, day_index) in item.inventory">
                            <span v-if="inventories[inventory_index].inventory[day_index].inventory_num >= 0">
                                {{inventories[inventory_index].inventory[day_index].inventory_num}}
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
            <div class="container" v-show="showMessage">
                <h4 class="text-center">{{ $t('hotelsmanagehotelinventories.no_policies') }}</h4>
            </div>

            <div class="container" v-show="showHistory">
                <h3 class="text-center">Historial {{ name }}</h3>

                <table class="VueTables__table table table-striped table-bordered table-hover text-center">
                    <thead>
                    <tr>
                        <th>Fecha y Hora de Movimiento</th>
                        <th>Descripción de Movimiento</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="message in history_messages">
                        <td>
                            {{ message.created_at }}
                        </td>
                        <td>
                            {{ message.description }}
                        </td>
                    </tr>
                    </tbody>
                </table>

                <ul class="style_list_ul_history">

                </ul>
            </div>
        </div>
    </div>
</template>
<script>
    import { API } from '../../../../api'

    export default {
        components: {},
        data: () => {
            return {
                days: 0,
                name: '',
                inventories: [],
                rooms: [],
                room_id: '',
                rates_plans: [],
                rate_plan_id: '',
                month: '1',
                showTable: false,
                showMessage: false,
                showHistory: false,
                allotment: 0,
                history_messages: [],
                months: [
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
                    },

                ],
                year: '2019',
                years: []
            }
        },
        mounted () {
            let f = new Date()

            this.month = f.getMonth() + 1

            this.year = f.getFullYear()

            this.getInventoryByTab()
            this.getRoomsByHotel()
            this.getRatesPlansByHotel()

            this.years.push( this.year - 2 )
            this.years.push( this.year - 1 )
            this.years.push( this.year )
            this.years.push( this.year + 1 )
            this.years.push( this.year + 2 )

        },
        computed: {},
        created () {
        },
        methods: {
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
                    }
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.rooms = result.data.data
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.rooms'),
                                text: this.$t('hotelsmanagehotelinventories.error.messages.information_error')
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
                        hotel_id: this.$route.params.hotel_id
                    }
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.rates_plans = result.data.data
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.rooms'),
                                text: this.$t('hotelsmanagehotelinventories.error.messages.information_error')
                            })
                        }
                    }).catch(() => {
                    this.showTable = false
                    this.showMessage = true
                })
            },
            getInventoryByTab: function () {
                this.showHistory = false
                this.getInventory(this.allotment)
            },
            getInventory: function (allotment) {
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
                        allotment: allotment
                    }
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.showTable = true
                            this.showMessage = false
                            this.showHistory = false
                            this.inventories = result.data.inventories
                            this.days = result.data.days
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.rooms'),
                                text: this.$t('hotelsmanagehotelinventories.error.messages.information_error')
                            })
                        }
                    }).catch(() => {
                    this.showTable = false
                    this.showMessage = true
                })
            },
            selectCell: function (inventory_index, day_index) {
                if (this.inventories[inventory_index].inventory[day_index].selected) {
                    this.inventories[inventory_index].inventory[day_index].selected = false
                    this.inventories[inventory_index].inventory[day_index]['selected'] = false
                    this.inventories[inventory_index].inventory[day_index]['class_selected'] = false

                    if (this.inventories[inventory_index].inventory[day_index]['locked']) {
                        this.inventories[inventory_index].inventory[day_index]['class_locked'] = true
                        this.inventories[inventory_index].inventory[day_index]['class_intermediate'] = false
                        this.inventories[inventory_index].inventory[day_index]['class_normal'] = false
                        this.inventories[inventory_index].inventory[day_index]['class_selected'] = false
                    } else {
                        this.inventories[inventory_index].inventory[day_index]['class_normal'] = true
                        this.inventories[inventory_index].inventory[day_index]['class_intermediate'] = false
                        this.inventories[inventory_index].inventory[day_index]['class_locked'] = false
                    }

                } else {
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
                    this.name = this.inventories[inventory_index]['rate_name']
                    API({
                        method: 'post',
                        url: 'inventory/history',
                        data: {
                            inventory_id: this.inventories[inventory_index].inventory[day_index]['id'],
                            bag_room_id: this.inventories[inventory_index].inventory[day_index]['bag_room_id'],
                            rate_plan_rooms_id: this.inventories[inventory_index].inventory[day_index]['rate_plan_rooms_id']
                        }
                    })
                        .then((result) => {
                            if (result.data.success === true) {
                                this.showHistory = true
                                this.history_messages = result.data.data

                            } else {
                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: this.$t('global.modules.inventories'),
                                    text: this.$t('hotelsmanagehotelinventories.error.messages.information_error')
                                })
                            }
                        }).catch((e) => {
                        console.log(e)
                    })
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
            }
        }
    }
</script>

<style>
    .style_list_ul_history {
        list-style-type: none;
        padding: 0px;
        margin-left: -1px;
        border-right: 1px solid #ccc;
        border-left: 1px solid #ccc;
        border-top: 1px solid #ccc;
        border-bottom: 1px solid #ccc;
    }

    .style_list_li_history {
        border-bottom: 1px solid #ccc;
        padding: 5px 5px 5px 5px;
    }

    .style_span_li_history {
        margin-left: 5px;
    }

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
