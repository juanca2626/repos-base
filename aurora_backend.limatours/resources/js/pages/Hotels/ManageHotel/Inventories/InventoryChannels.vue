<template>
    <div class="container-fluid tab-content" style="padding: 20px;">
        <div>
            <div class="my-3">
                <div class="row col-lg-12" style="padding:10px 0">
                    <div class="col-2">
                        <select @change="getInventoryByTab()" id="month" name="month" v-model="month"
                                class="form-control">
                            <option :value="month.id" v-for="month in months">{{
                                    $t('global.months.' + month.name)
                                }}
                            </option>
                        </select>
                    </div>
                    <div class="col-2">
                        <select @change="getInventoryByTab()" id="year" name="year" v-model="year" class="form-control">
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
                    <div class="row col-2" style="padding-right: 0;">
                        <div class="col-lg-4">
                            <label style="padding-top: 5px;">{{ $t('global.modules.rates') }}</label>
                        </div>
                        <div class="col-lg-8" style="padding-right: 0;">
                            <select @change="getInventoryByTab()" class="form-control" id="rate_plan_id"
                                    name="rate_plan_id"
                                    v-model="rate_plan_id">
                                <option value="">{{ $t('global.days.everyone') }}</option>
                                <option :value="rate_plan.id" v-for="rate_plan in rates_plans">{{
                                        rate_plan.name
                                    }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="my-3">
                <h6 class="font-weight-bold">Leyenda:</h6>
                <div class="d-flex flex-wrap">
                    <div class="d-flex align-items-center mr-4 mb-2">
                        <span class="d-inline-block rounded-circle"
                              style="width: 16px; height: 16px; background-color: #ffffff; border: 1px solid #ced4da;"></span>
                        <span class="ml-2 text-muted">Disponible</span>
                    </div>
                    <div class="d-flex align-items-center mr-4 mb-2">
                        <span class="d-inline-block rounded-circle"
                              style="width: 16px; height: 16px; background-color: #B38B00;"></span>
                        <span class="ml-2 text-muted">Fecha de Salida Cerrada</span>
                    </div>
                    <div class="d-flex align-items-center mr-4 mb-2">
                        <span class="d-inline-block rounded-circle"
                              style="width: 16px; height: 16px; background-color: #cb2027;"></span>
                        <span class="ml-2 text-muted">Bloqueado</span>
                    </div>
                </div>
            </div>

            <div class="table-responsive responsive-inventory" v-show="showTable">
                <table class="VueTables__table table table-bordered table-hover text-center">
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
                        <td
                            :id="inventory_index+'-'+day_index"
                            :class="{
                                   'row_selected': inventories[inventory_index].inventory[day_index].class_selected,
                                    'row_normal': inventories[inventory_index].inventory[day_index].class_normal,
                                    'row_departure_closed': inventories[inventory_index].inventory[day_index].class_departure_closed,
                                    'row_disabled': inventories[inventory_index].inventory[day_index].class_locked,
                                    'row_intermediate': inventories[inventory_index].inventory[day_index].class_intermediate
                                 }"
                            @click="selectCell(inventory_index, day_index)"
                            v-for="(day, day_index) in item.inventory"
                        >
                            <template v-if="inventories[inventory_index].inventory[day_index].id !== ''">
                                <b-popover
                                    :target="inventory_index+'-'+day_index"
                                    triggers="hover"
                                    placement="top"
                                    html
                                >
                                    <template #default>
                                        <div><strong>Bloqueado:</strong> {{inventories[inventory_index].inventory[day_index].class_locked ? 'Si' : 'No'}}</div>
                                        <div><strong>Fecha de Salida Cerrada:</strong> {{inventories[inventory_index].inventory[day_index].class_departure_closed ? 'Si' : 'No'}}</div>
                                    </template>
                                </b-popover>
                            </template>
                            <span v-if="inventories[inventory_index].inventory[day_index].inventory_num >= 0">
                                {{ inventories[inventory_index].inventory[day_index].inventory_num }}
                            </span>
                            <span v-if="inventories[inventory_index].inventory[day_index].inventory_num < 0">
                                <div style="height: 4px;width: 5px;background: red;border-radius: 4px;float: right;margin-top: -2px;"></div>
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
import {API} from '../../../../api'

export default {
    components: {},
    data: () => {
        return {
            days: 0,
            name: '',
            inventories: [],
            rooms: [],
            room_id: '',
            channel_id: '',
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
        }
    },
    mounted() {
        let f = new Date()

        this.month = f.getMonth() + 1

        this.year = f.getFullYear()

        this.getInventoryByTab()
        this.getRoomsByHotel()

    },
    computed: {},
    created() {
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
        getRatesPlansByChannels: function () {
            API({
                method: 'post',
                url: 'rates/plans/by/channels',
                data: {
                    hotel_id: this.$route.params.hotel_id,
                    channel_id: this.$route.params.channel_id,
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
            this.showHistory = false
            this.getInventory(this.allotment)
            this.getRatesPlansByChannels()
        },
        getInventory: function (allotment) {
            API({
                method: 'post',
                url: 'inventory/by/channels',
                data: {
                    month: this.month,
                    year: this.year,
                    room_id: this.room_id,
                    rate_plan_id: this.rate_plan_id,
                    hotel_id: this.$route.params.hotel_id,
                    lang: localStorage.getItem('lang'),
                    allotment: 0,
                    channel_id: this.$route.params.channel_id,
                },
            }).then((result) => {
                if (result.data.success === true) {
                    this.showTable = true
                    this.showMessage = false
                    this.showHistory = false
                    this.inventories = result.data.inventories
                    this.days = result.data.days

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
            }).catch(() => {

            })
        },
    },
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

.row_departure_closed {
    background-color: #B38B00;
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
