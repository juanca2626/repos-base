<template>
    <div class="container" style="max-width: 100%;">
        <div class="row cost-table-container">
            <div class="col-12 text-left">
                <div class="row">
                    <div class="col-2">
                        <b-form-select :options="months" @change="fetchData" v-model="currentMonth"></b-form-select>
                    </div>
                    <div class="col-2">
                        <b-form-select :options="years" @change="fetchData" v-model="currentYear"></b-form-select>
                    </div>
                    <div class="col-2">
                        <b-form-select :options="rooms" @change="fetchData" v-model="currentRoom"></b-form-select>
                    </div>
                    <div class="col-2">
                        <b-form-select :options="rates" @change="fetchData" v-model="currentRate"></b-form-select>
                    </div>
                    <div class="col-2">
                        <b-form-select :options="channels" @change="fetchData" v-model="currentChannel"></b-form-select>
                    </div>
                </div>
                <br/>
            </div>


            <div class="col-12">
                <div class="vld-parent">
                    <loading :active.sync="loading" :can-cancel="false" color="#BD0D12"></loading>
                    <div class="row">
                        <div class="col-12 m-auto" id="cost-calendar">
                            <div class="header row text-center">
                                <div class="col-head">{{ $t('global.days.monday') }}</div>
                                <div class="col-head">{{ $t('global.days.tuesday') }}</div>
                                <div class="col-head">{{ $t('global.days.wednesday') }}</div>
                                <div class="col-head">{{ $t('global.days.thursday') }}</div>
                                <div class="col-head">{{ $t('global.days.friday') }}</div>
                                <div class="col-head">{{ $t('global.days.saturday') }}</div>
                                <div class="col-head">{{ $t('global.days.sunday') }}</div>
                            </div>
                            <div class="body row text-center">
                                <div class="col-body" v-for="day in days">
                                    <div class="date-block">{{ day.text }}</div>
                                    <div @click="showDetail(item)" class="rates-block" v-for="item in day.data"
                                         v-if="day.data">
                                        <div class="block">
                                        <span
                                            class="block-value"><strong>{{ item.ratePlan.name }} ({{
                                                item.channel.name
                                            }}): </strong></span>
                                            <span class="block-value">{{ item.room.name }}: US$ {{
                                                    item.room.value
                                                }}</span>
                                            <span class="block-value" v-for="rate_extra in item.rates"
                                                  v-if="rate_extra.category==='Extra'">
                                            + Extra US$ {{ rate_extra.price }}
                                        </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-4 offset-8 text-right">
                            <button @click="deleteAll" class="btn btn-secondary" type="button" v-if="!loading">
                                {{ $t('hotelsmanagehotelratesratescost.buttons.deleteVisible') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <b-modal :title="currentItem.ratePlan.name+': '+currentItem.room.name" id="modal-block-detail"
                 ref="modal-detail" size="lg"
                 v-if="currentItem">
            <div class="row">
                <div class="col-12">
                    <h5>Política: {{ currentItem.policy.name }}</h5>
                </div>
                <div class="col-auto">
                    <span><strong>Min AB Offset: </strong>{{ currentItem.policy.min_ab_offset }} </span>
                </div>
                <div class="col-auto">
                    <span><strong>Max AB Offset: </strong>{{ currentItem.policy.max_ab_offset }} </span>
                </div>
                <div class="col-auto">
                    <span><strong>Días mínimo: </strong>{{ currentItem.policy.min_length_stay }} </span>
                </div>
                <div class="col-auto">
                    <span><strong>Días máximo: </strong>{{ currentItem.policy.max_length_stay }} </span>
                </div>
                <div class="col-auto">
                    <span><strong>Ocupantes máximo: </strong>{{ currentItem.policy.max_occupancy }} </span>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-12">
                    <hr/>
                </div>
                <div class="col-12">
                    <h5>Meal: {{ currentItem.meal.name }}</h5>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-12">
                    <hr/>
                </div>
                <div class="col-12">
                    <div class="rooms-table row rooms-table-headers">
                        <div class="col-3 my-auto">
                            {{ $t('hotelsmanagehotelratesratescost.type') }}
                        </div>
                        <div class="col-3 my-auto">
                            {{ $t('hotelsmanagehotelratesratescost.category') }}
                        </div>
                        <div class="col-3 my-auto">
                            {{ $t('hotelsmanagehotelratesratescost.amount') }}
                        </div>
                        <div class="col-3 my-auto">
                            {{ $t('hotelsmanagehotelratesratescost.price') }} US$
                        </div>
                    </div>
                    <div class="rooms-table row" v-for="(rate, index) in currentItem.rates">
                        <div class="col-3 my-auto">
                            {{ rate.type }}
                        </div>
                        <div class="col-3 my-auto text-center">
                            {{ rate.category }}
                        </div>
                        <div class="col-3 my-auto text-center">
                            {{ rate.amount }}
                        </div>
                        <div class="col-3 my-auto text-right" v-if="currentItem.channel.id > 1">
                            {{ rate.price }}
                        </div>
                        <div class="col-3 my-auto text-right" v-if="currentItem.channel.id === 1">
                            <input :id="'room-'+index+'-price'"
                                   :name="'room-'+index+'-price'"
                                   class="form-control"
                                   step="0.01"
                                   type="number"
                                   v-model="currentItem.rates[index].price"
                                   v-validate="{ required: true }"/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-100" slot="modal-footer">
                <div class="row">
                    <div class="col-12">
                        <img src="/images/loading.svg" v-if="loading" width="40px"/>
                    </div>
                    <div class="col-6 text-left">
                        <button @click="deleteDetail" class="btn btn-secondary" type="button"
                                v-if="currentItem.channel.id === 1 && !loading">
                            {{ $t('global.buttons.delete') }}
                        </button>
                    </div>
                    <div class="col-6 text-right">
                        <button @click="submit" class="btn btn-success" type="button"
                                v-if="currentItem.channel.id === 1 && !loading">
                            <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                            {{ $t('global.buttons.submit') }}
                        </button>
                        <button @click="closeDetail" class="btn btn-danger" type="button" v-if="!loading">
                            {{ $t('global.buttons.cancel') }}
                        </button>
                    </div>
                </div>
            </div>
        </b-modal>
        <b-modal id="modal-block-delete-detail" ref="modal-delete-detail" title="Eliminar Tarifa" v-if="currentItem">
            <div class="row">
                <div class="col-auto">
                    {{ $t('hotelsmanagehotelratesratescost.delete.message') }}
                </div>
                <div class="col-auto">
                    <strong>{{ $t('hotelsmanagehotelratesratescost.rate') }}: </strong>{{ currentItem.ratePlan.name }}
                </div>
                <div class="col-auto">
                    <strong>{{ $t('hotelsmanagehotelratesratescost.room') }}: </strong>{{ currentItem.room.name }}
                </div>
                <div class="col-auto">
                    <strong>{{ $t('hotelsmanagehotelratesratescost.policy') }}: </strong>{{ currentItem.policy.name }}
                </div>
                <div class="col-auto">
                    <strong>{{ $t('hotelsmanagehotelratesratescost.meal') }}: </strong>{{ currentItem.meal.name }}
                </div>
            </div>
            <div class="w-100" slot="modal-footer">
                <div class="row">
                    <div class="col-12">
                        <img src="/images/loading.svg" v-if="loading" width="40px"/>
                    </div>
                    <div class="col-12 text-right">
                        <button @click="continueDeleteDetail" class="btn btn-success" type="button" v-if="!loading">
                            <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                            {{ $t('global.buttons.delete') }}
                        </button>
                        <button @click="closeDeleteDetail" class="btn btn-danger" type="button" v-if="!loading">
                            {{ $t('global.buttons.cancel') }}
                        </button>
                    </div>
                </div>
            </div>
        </b-modal>
        <b-modal id="modal-block-delete-rates" ref="modal-delete-rates" title="Eliminar Tarifas Visibles">
            <div class="row">
                <div class="col-auto">
                    {{ $t('hotelsmanagehotelratesratescost.delete.confirmation') }}
                </div>
                <div class="col-auto">
                    <strong>{{ $t('hotelsmanagehotelratesratescost.month') }}: </strong>{{ currentMonthName }}
                </div>
                <div class="col-auto">
                    <strong>{{ $t('hotelsmanagehotelratesratescost.year') }}: </strong>{{ currentYear }}
                </div>
                <div class="col-auto" v-if="currentRoom !== ''">
                    <strong>{{ $t('hotelsmanagehotelratesratescost.room') }}: </strong>{{ currentRoomName }}
                </div>
                <div class="col-auto" v-if="currentRate !== ''">
                    <strong>{{ $t('hotelsmanagehotelratesratescost.rate') }}: </strong>{{ currentRateName }}
                </div>
            </div>
            <div class="w-100" slot="modal-footer">
                <div class="row">
                    <div class="col-12">
                        <img src="/images/loading.svg" v-if="loading" width="40px"/>
                    </div>
                    <div class="col-12 text-right">
                        <button @click="continueDeleteAll" class="btn btn-success" type="button" v-if="!loading">
                            <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                            {{ $t('global.buttons.delete') }}
                        </button>
                        <button @click="closeDeleteAll" class="btn btn-danger" type="button" v-if="!loading">
                            {{ $t('global.buttons.cancel') }}
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
import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/vue-loading.css'

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
        Loading
    },
    data: () => {
        return {
            loading: false,
            currentMonth: 0,
            currentYear: 0,
            currentRoom: '',
            currentRate: '',
            currentChannel: '',
            days: [],
            rooms: [],
            rates: [],
            channels: [],
            currentItem: null
        }
    },
    mounted() {
        let currentDate = new Date()
        this.currentMonth = ('00' + (currentDate.getMonth() + 1)).slice(-2)
        this.currentYear = currentDate.getFullYear()

        this.fetchData(this.$i18n.locale)

        API.get('/rooms/selectBox?hotel_id=' + this.$route.params.hotel_id + '&lang=' + localStorage.getItem('lang'))
            .then((result) => {
                let rooms = [
                    {
                        value: '',
                        text: 'Todas las Habitaciones'
                    }
                ]
                result.data.data.forEach((room) => {
                    rooms.push({
                        value: room.id,
                        text: room.translations[0].value
                    })
                })

                this.rooms = rooms
            })

        API.get('/rates/cost/selectBox?hotel_id=' + this.$route.params.hotel_id + '&lang=' + localStorage.getItem('lang'))
            .then((result) => {
                let data = result.data.data

                data.unshift({value: '', text: 'Todas las Tarifas'})

                this.rates = data
            })

        API.get('/channels/selectHotelBox?lang=' + localStorage.getItem('lang'))
            .then((result) => {
                let data = result.data.data

                data.unshift({value: '', text: 'Todos los Canales'})

                this.channels = data
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
        },
        currentMonthName() {
            let months = JSON.parse(JSON.stringify(this.months))
            let month = this.currentMonth !== 0 ?
                months.find(month => month.value === this.currentMonth) : {text: ''}

            return month.text
        },
        currentRoomName() {
            let rooms = JSON.parse(JSON.stringify(this.rooms))
            let room = this.currentRoom !== '' ?
                rooms.find(room => room.value === this.currentRoom) : {text: ''}

            return room.text
        },
        currentRateName() {
            let rates = JSON.parse(JSON.stringify(this.rates))
            let rate = this.currentRate !== '' ?
                rates.find(rate => rate.value === this.currentRate) : {text: ''}

            return rate.text
        }
    },
    created() {
        this.$parent.$parent.$on('langChange', (payload) => {
            this.fetchData(payload.lang)
        })
    },
    methods: {
        fetchData: function () {
            this.loading = true
            API.get('rates/cost/'
                + this.$route.params.hotel_id + '/calendar?' +
                'date=' + this.currentMonth + '-' + this.currentYear +
                '&room=' + this.currentRoom +
                '&rate=' + this.currentRate +
                '&channel=' + this.currentChannel +
                '&lang=' + localStorage.getItem('lang'))
                .then((result) => {
                    let days = []
                    let currentDays = moment('01-' + this.currentMonth + '-' + this.currentYear, 'DD-MM-YYYY')
                    let resultData = result.data
                    this.loading = false
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
            console.log(item)
            this.currentItem = item
            setTimeout(() => {
                this.$refs['modal-detail'].show()
            }, 50)
        },
        deleteAll() {
            this.loading = false

            this.$refs['modal-delete-rates'].show()
        },
        deleteDetail() {
            this.loading = false

            this.$refs['modal-delete-detail'].show()
        },
        continueDeleteDetail() {
            API.delete('rates/cost/' + this.$route.params.hotel_id + '/calendar/' + this.currentItem.calendar.id)
                .then(() => {
                    this.fetchData()
                    this.closeDetail()

                    this.loading = false
                })
        },
        closeDeleteDetail() {
            this.loading = false

            this.$refs['modal-delete-detail'].hide()
        },
        closeDeleteAll() {
            this.loading = false

            this.$refs['modal-delete-rates'].hide()
        },
        continueDeleteAll() {
            this.loading = true
            API.delete('rates/cost/'
                + this.$route.params.hotel_id + '/calendar?date=' + this.currentMonth + '|' + this.currentYear + '|' +
                this.currentRoom + '|' + this.currentRate
                + '&lang=' + localStorage.getItem('lang'))
                .then((result) => {
                    this.fetchData()
                    this.closeDeleteAll()
                    this.loading = false
                })
        },
        closeDetail() {
            this.$refs['modal-detail'].hide()
            this.currentItem = null
        },
        submit() {
            this.loading = true
            API({
                method: 'put',
                url: 'rates/cost/' + this.$route.params.hotel_id + '/calendar',
                data: this.currentItem
            }).then((result) => {
                this.loading = false
                if (result.data.success === true) {
                    this.$refs['modal-detail'].hide()
                    this.fetchData()
                } else {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Fetch Error',
                        text: result.data.message
                    })
                }
            }).catch(() => {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('hotelsmanagehotelratesratescost.error.messages.name'),
                    text: this.$t('hotelsmanagehotelratesratescost.error.messages.connection_error')
                })
            })
        }
    }
}
</script>



