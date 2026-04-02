<template>
    <div class="container" style="max-width: 100%;">
        <div class="row cost-table-container">
            <div class="col-12 text-left">
                <div class="row">
                    <div class="col-2">
                        <b-form-select :options="months" @change="fetchData" v-model="currentMonth"></b-form-select>
                    </div>
                    <div class="col-2">
                        <b-form-select :options="years" @change="fetchDataYear" v-model="currentYear"></b-form-select>
                    </div>
                    <div class="col-8">
                        <v-select
                            :options="clients"
                            :reduce="client => client.value"
                            @input="fetchData"
                            autocomplete="true"
                            id="clients"
                            label="text"
                            v-model="currentClient">
                        </v-select>
                    </div>
                    <div class="col-4 mt-4" v-if="currentClient !== ''">
                        <v-select
                            :options="rooms"
                            :reduce="room => room.value"
                            @input="fetchData"
                            autocomplete="true"
                            id="rooms"
                            label="text"
                            placeholder="Habitación"
                            v-model="currentRoom">
                        </v-select>
                    </div>
                    <div class="col-4 mt-4" v-if="currentClient !== ''">
                        <v-select
                            :options="rates"
                            :reduce="rate => rate.value"
                            @input="fetchData"
                            autocomplete="true"
                            id="rates"
                            label="text"
                            placeholder="Tarifa"
                            v-model="currentRate">
                        </v-select>
                    </div>
                    <div class="col-4 mt-4" v-if="currentClient !== ''">
                        <v-select
                            :options="channels"
                            :reduce="channel => channel.value"
                            @input="fetchData"
                            autocomplete="true"
                            id="channels"
                            label="text"
                            placeholder="Canal"
                            v-model="currentChannel">
                        </v-select>
                    </div>
                </div>
                <br/>
            </div>
            <div class="col-12" v-if="currentClient !== ''">
                <div class="row">
                    <div class="col-12  m-auto" id="cost-calendar">
                        <div class="header row text-center">
                            <div class="col-head">{{$t('global.days.monday')}}</div>
                            <div class="col-head">{{$t('global.days.tuesday')}}</div>
                            <div class="col-head">{{$t('global.days.wednesday')}}</div>
                            <div class="col-head">{{$t('global.days.thursday')}}</div>
                            <div class="col-head">{{$t('global.days.friday')}}</div>
                            <div class="col-head">{{$t('global.days.saturday')}}</div>
                            <div class="col-head">{{$t('global.days.sunday')}}</div>
                        </div>
                        <div class="body row text-center">
                            <div class="col-body" v-for="day in days">
                                <div class="date-block">{{day.text}}</div>
                                <div @click="showDetail(item)" class="rates-block" v-for="item in day.data"
                                     v-if="day.data">
                                    <div class="block">
                                        <span class="block-value"><strong>{{item.ratePlan.name}} ({{item.channel.name}}): </strong></span>
                                        <span class="block-value">{{item.room.name}}: US$ {{parseFloat(item.room.value).toFixed(2)}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <b-modal :title="currentItem.ratePlan.name+': '+currentItem.room.name" id="modal-block-detail"
                 ref="modal-detail" size="lg" v-if="currentItem">
            <div class="row">
                <div class="col-12">
                    <h5>Política: {{currentItem.policy.name}}</h5>
                </div>
                <div class="col-auto">
                    <span><strong>Min AB Offset: </strong>{{currentItem.policy.min_ab_offset}} </span>
                </div>
                <div class="col-auto">
                    <span><strong>Max AB Offset: </strong>{{currentItem.policy.max_ab_offset}} </span>
                </div>
                <div class="col-auto">
                    <span><strong>Días mínimo: </strong>{{currentItem.policy.min_length_stay}} </span>
                </div>
                <div class="col-auto">
                    <span><strong>Días máximo: </strong>{{currentItem.policy.max_length_stay}} </span>
                </div>
                <div class="col-auto">
                    <span><strong>Ocupantes máximo: </strong>{{currentItem.policy.max_occupancy}} </span>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-12">
                    <hr/>
                </div>
                <div class="col-12">
                    <h5>Meal: {{currentItem.meal.name}}</h5>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-12">
                    <hr/>
                </div>
                <div class="col-12">
                    <div class="rooms-table row rooms-table-headers">
                        <div class="col-3 my-auto">
                            {{$t('type')}}
                        </div>
                        <div class="col-3 my-auto">
                            {{$t('category')}}
                        </div>
                        <div class="col-3 my-auto">
                            {{$t('amount')}}
                        </div>
                        <div class="col-3 my-auto">
                            {{$t('price')}} US$
                        </div>
                    </div>
                    <div class="rooms-table row" v-for="(rate, index) in currentItem.rates">
                        <div class="col-3 my-auto">
                            {{rate.type}}
                        </div>
                        <div class="col-3 my-auto text-center">
                            {{rate.category}}
                        </div>
                        <div class="col-3 my-auto text-center">
                            {{rate.amount}}
                        </div>
                        <div class="col-3 my-auto text-right">
                            {{rate.price}}
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-100" slot="modal-footer">
                <div class="row">
                    <div class="offset-6 col-6 text-right">
                        <button @click="closeDetail" class="btn btn-danger" type="button" v-if="!loading">
                            {{$t('global.buttons.cancel')}}
                        </button>
                    </div>
                </div>
            </div>
        </b-modal>
    </div>
</template>

<script>
    import {API} from './../../../../../../api'
    import TableClient from './../../../../../../components/TableClient'
    import MenuEdit from './../../../../../../components/MenuEdit'
    import moment from 'moment/moment'
    import BFormSelect from 'bootstrap-vue/src/components/form-select/form-select'
    import BModal from 'bootstrap-vue/src/components/modal/modal'
    import vSelect from 'vue-select'
    import 'vue-select/dist/vue-select.css'

    const times = x => f => {
        if (x > 0) {
            f()
            times(x - 1)(f)
        }
    }

    export default {
        components: {
            'table-client': TableClient,
            'menu-edit': MenuEdit,
            BFormSelect,
            BModal,
            vSelect
        },
        data: () => {
            return {
                loading: false,
                currentMonth: 0,
                currentYear: 0,
                currentRoom: '',
                currentClient: '',
                currentRate: '',
                currentChannel: '',
                days: [],
                rooms: [],
                clients: [],
                rates: [],
                channels: [],
                currentItem: null
            }
        },
        mounted() {
            let currentDate = new Date()
            this.currentMonth = ('00' + (currentDate.getMonth() + 1)).slice(-2)
            this.currentYear = currentDate.getFullYear()

            API.get('/channels/selectHotelBox?lang=' + localStorage.getItem('lang'))
                .then((result) => {
                    let data = result.data.data

                    data.unshift({value: '', text: ''})

                    this.channels = data
                })

            this.getListClients();

            API.get('/clients/selectBox?hotel_id=' + this.$route.params.hotel_id + '&lang=' + localStorage.getItem('lang'))
                .then((result) => {
                    let data = result.data.data

                    data.unshift({value: '', text: ''})

                    this.clients = data
                })

            API.get('/rooms/selectBox?hotel_id=' + this.$route.params.hotel_id + '&lang=' + localStorage.getItem('lang'))
                .then((result) => {
                    let data = [{value: '', text: 'Todas las habitaciones'}]

                    result.data.data.forEach((item) => {
                        data.push({
                            value: item.id,
                            text: item.translations[0].value
                        })
                    })

                    this.rooms = data
                })

            API.get('/rates/plans/by/hotel?hotel_id=' + this.$route.params.hotel_id + '&lang=' + localStorage.getItem('lang'))
                .then((result) => {
                    let data = [{value: '', text: 'Todos los planes'}]

                    result.data.data.forEach((item) => {
                        data.push({
                            value: item.id,
                            text: item.name
                        })
                    })

                    this.rates = data
                })
        },
        computed: {
            months() {
                return [
                    {value: '01', text: this.$i18n.t('global.months.january')},
                    {value: '02', text: this.$i18n.t('global.months.february')},
                    {value: '03', text: this.$i18n.t('global.months.march')},
                    {value: '04', text: this.$i18n.t('global.months.april')},
                    {value: '05', text: this.$i18n.t('global.months.may')},
                    {value: '06', text: this.$i18n.t('global.months.june')},
                    {value: '07', text: this.$i18n.t('global.months.july')},
                    {value: '08', text: this.$i18n.t('global.months.august')},
                    {value: '09', text: this.$i18n.t('global.months.september')},
                    {value: '10', text: this.$i18n.t('global.months.october')},
                    {value: '11', text: this.$i18n.t('global.months.november')},
                    {value: '12', text: this.$i18n.t('global.months.december')}
                ]
            },
            years() {
                let previousYear = moment().subtract(5, 'years').year()
                let currentYear = moment().add(5, 'years').year()

                let years = []

                do {
                    years.push({value: previousYear, text: previousYear})
                    previousYear++
                } while (currentYear > previousYear)

                return years
            }
        },
        created() {
            this.$parent.$parent.$on('langChange', (payload) => {
                this.fetchData(payload.lang)
            })
        },
        methods: {
            getListClients: function () {
                API.get('/clients/selectBox?hotel_id=' + this.$route.params.hotel_id + '&lang=' + localStorage.getItem('lang') + '&period=' + this.currentYear)
                    .then((result) => {
                        let data = result.data.data

                        data.unshift({value: '', text: ''})

                        this.clients = data
                    })
            },
            fetchDataYear: function () {
                this.getListClients();
                this.currentClient = '';
            },
            fetchData: function () {
                this.days = []
                if (this.currentClient === '') {
                    return
                }
                API.get('rates/sale/'
                    + this.$route.params.hotel_id + '/calendar?date=' + this.currentMonth + '-' + this.currentYear +
                    '&client=' + this.currentClient +
                    '&rate=' + this.currentRate +
                    '&room=' + this.currentRoom +
                    '&channel=' + this.currentChannel +
                    '&lang=' + localStorage.getItem('lang'))
                    .then((result) => {
                        let days = []
                        let currentDays = moment('01-' + this.currentMonth + '-' + this.currentYear, 'DD-MM-YYYY')
                        let resultData = result.data

                        let day = currentDays.day() === 0 ? 6 : currentDays.day() - 1

                        times(day)(() => {
                            days.push({value: 0, text: ''})
                        })

                        let howManyDays = currentDays.daysInMonth()
                        let count = 1

                        times(howManyDays)(() => {
                            if (resultData.data.hasOwnProperty(
                                this.currentYear + '-' + this.currentMonth + '-' + ('00' + count).slice(-2))) {
                                days.push({
                                    value: count,
                                    text: count,
                                    data: resultData.data[this.currentYear + '-' + this.currentMonth + '-' + ('00' + count).slice(-2)]
                                })
                            } else {
                                days.push({value: count, text: count})
                            }

                            count++
                        })

                        this.days = days
                    })
            },
            showDetail(item) {
                this.currentItem = item
                setTimeout(() => {
                    this.$refs['modal-detail'].show()
                }, 50)
            },
            closeDetail() {
                this.currentItem = null

                this.$refs['modal-detail'].hide()
            },
        }
    }
</script>

<style lang="stylus">
    .cost-table-container
        position relative

    #cost-calendar
        .col-head
            background #2f353a
            color #fff
            border-width 0 !important
            font-size 14px
            font-weight 700
            padding 0.75rem
            width 14% !important
            min-width 14% !important
            max-width 14% !important

        .col-body
            border 1px solid #C8CED3
            font-size 14px
            padding 0
            width 14% !important
            min-width 14% !important
            max-width 14% !important
            min-height 45px

            .date-block
                background #F9FBFC
                font-weight 700
                text-align right
                padding-right 10px
                font-size 10px

            .rates-block
                text-align left
                border 1px solid #F9FBFC
                padding 0.75rem

                .block
                    font-size 12px

                    .block-hotel
                        font-weight bold

                &:hover
                    background-color #C8CED3
                    cursor pointer

    .rooms-table-headers
        text-align center
        background-color #e4e7ea

    input[type="number"]::-webkit-outer-spin-button,
    input[type="number"]::-webkit-inner-spin-button
        -webkit-appearance none
        margin 0


    input[type="number"]
        -moz-appearance textfield

    .rooms-table
        input[type=number]
            padding-right 0 !important
            background none !important

</style>


