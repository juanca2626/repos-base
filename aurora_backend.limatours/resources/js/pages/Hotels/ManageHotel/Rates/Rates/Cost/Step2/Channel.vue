<template>
    <div class="col-12">

        <div class="row mt-3">
            <div class="col-6">
                <div class="row">
                    <label class="col-sm-4 col-form-label" for="policy_cancellation_id">
                        {{ $t('hotelsmanagehotelconfiguration.nom_cancellation') }}
                    </label>
                    <div class="col-sm-8">
                        <v-select :options="policyCancellations"
                                autocomplete="true"
                                v-model="form.policies_cancelation"
                                multiple>
                        </v-select>
                    </div>
                </div>
            </div>
            <div class="col-2">
                <label class="col-form-label">Rango de Fechas</label>
            </div>
            <div class="input-group col-4">
                <div class="row">
                    <div class="input-group col-6">
                        <date-picker
                            :config="datePickerFromOptions"
                            @dp-change="setDateFrom"
                            id="date_from"
                            name="date_from"
                            placeholder="inicio: DD/MM/YYYY"
                            ref="datePickerFrom"
                            v-model="form.date_from"
                            v-validate="{ required: true }"
                        >
                        </date-picker>
                        <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                            <font-awesome-icon
                                :icon="['fas', 'exclamation-circle']"
                                style="margin-left: 5px;"
                                v-show="errors.has('date_from')"/>
                            <span v-show="errors.has('date_from')">{{ errors.first('date_from') }}</span>
                        </div>
                    </div>
                    <div class="input-group col-6">
                        <date-picker
                            :config="datePickerToOptions"
                            id="date_to"
                            name="date_to"
                            placeholder="fin: DD/MM/YYYY"
                            ref="datePickerTo"
                            v-model="form.date_to"
                            v-validate="{ required: true }"
                        >
                        </date-picker>
                        <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                            <font-awesome-icon
                                :icon="['fas', 'exclamation-circle']"
                                style="margin-left: 5px;"
                                v-show="errors.has('date_to')"/>
                            <span v-show="errors.has('date_to')">{{ errors.first('date_to') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3 add">
            <div class="col-12">
                <div class="rooms-table row rooms-table-headers">
                    <div class="col-6 my-auto">
                        {{ $t('hotelsmanagehotelratesratescost.room') }}
                    </div>
                    <div class="col-2">
                        {{ $t('hotelsmanagehotelratesratescost.child') }} US$
                    </div>
                    <div class="col-2">
                        {{ $t('hotelsmanagehotelratesratescost.infant') }} US$
                    </div>
                    <div class="col-2">
                        {{ $t('hotelsmanagehotelratesratescost.add') }}
                    </div>
                </div>
                <div :key="room.id" class="rooms-table row" v-for="(room, index) in form.rooms">
                    <div class="col-6 my-auto">
                        {{ room.name }}
                    </div>
                    <div class="col-2">
                        <input
                            id="channel_child_price"
                            name="channel_child_price"
                            class="form-control"
                            step="0.01"
                            type="number"
                            v-model="room.channel_child_price" @keyup="setPrice(index)"
                        />
                    </div>
                    <div class="col-2">
                        <input
                            id="channel_infant_price"
                            name="channel_infant_price"
                            class="form-control"
                            step="0.01"
                            type="number"
                            v-model="room.channel_infant_price" @keyup="setPrice(index)"
                        />
                    </div>
                    <div class="col-2">
                        <c-switch
                            :uncheckedValue="false"
                            :value="true"
                            class="mx-1"
                            color="primary"
                            v-model="form.rooms[index].selected"
                            variant="pill"
                        />
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div :class="{'col-8': !loading, 'col-12': loading}">
                <img src="/images/loading.svg" v-if="loading" width="40px"/>
                <button @click="submit" class="btn btn-success" type="button" v-if="!loading">
                    <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                    Guardar
                </button>
                <router-link to="../" v-if="!loading">
                    <button class="btn btn-danger" type="button">
                        {{ $t('global.buttons.cancel') }}
                    </button>
                </router-link>
            </div> 
        </div>

        <div class="row">
            <div class="col-2">
                <div class="b-form-group form-group">
                    <div class="input-group">
                        <div class="form-row">
                            <label class="col-form-label" for="period">{{
                                $t('clientsmanageclienthotel.period') }}</label>
                            <div class="col-sm-12">
                                <select @change="getDateRanges" ref="period" class="form-control" id="period"
                                        required
                                        size="0" v-model="selectPeriod">
                                    <option :value="year.value" v-for="year in years">
                                        {{ year.text}}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
                <table class="VueTables__table table table-striped table-bordered table-hover">
                    <thead class="thead-dark">
                    <th scope="col" style="padding: 10px">Opción</th>
                    <th scope="col" style="padding: 10px">Habitación</th>
                    <th scope="col" style="padding: 10px">Periodo</th>
                    <th scope="col" style="padding: 10px">Niño US$</th>
                    <th scope="col" style="padding: 10px">Infante US$</th>
                    <th></th>
                    </thead>
                    <tbody>
                    <tr v-for="price in prices">
                        <td style="width: 90px">
                            <button type="button" class="btn btn-success btn-sm" @click="editPrice(price.group, price.date_from, price.date_to)">
                                <i class="fas fa-edit"></i> Editar
                            </button>
                        </td>
                        <td>
                        <span style="padding: 5px">
                            <!-- <i v-if="date_range.flag_migrate === 0" class="fa fa-check-circle"></i> -->
                            {{price.rate_plan_room.room.id}} - {{ price.rate_plan_room.room.translations[0].value }}
                        </span>
                        </td>
                        <td style="padding: 5px;width: 180px">
                            {{ getDateFormat(price.date_from) }} <i class="fas fa-angle-right"></i> {{getDateFormat(price.date_to) }}
                        </td>
                        <td style="padding: 5px">{{ price.price_child }}</td>
                        <td style="padding: 5px">{{ price.price_infant }}</td>
                        <td style="padding: 5px">
                         <button type="button" @click="deletePrice(price.id)" title="Eliminar" class="btn btn-sm btn-danger" style="cursor: pointer">
                             <font-awesome-icon :icon="['fas', 'trash']" class="m-0"/>
                         </button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>


    </div>
</template>
<script>
import { API } from './../../../../../../../api'
import datePicker from 'vue-bootstrap-datetimepicker'
import CSwitch from '@coreui/vue/src/components/Switch/Switch'
import BFormSelect from 'bootstrap-vue/es/components/form-select/form-select'
import vSelect from 'vue-select'

export default {
    components: {
        CSwitch,
        BFormSelect,
        vSelect,
        datePicker
    },
    props: {
        hotelID: Number,
        ratePlanID: Number,
        formAction: String,
        options: Object,
        channelID: Number
    },
    data: () => {
        return {
            save: {
                max: 0,
                counter: 0
            },
            rooms: [],
            currentRooms: [],
            loading: false,
            policyCancellations: [],
            policyCancellation: null,
            policyCancellationSelected: [],
            policyCancellationSearch: '',
            selectPeriod: '',
            form: {
                channel_id: '',
                policies_cancelation: '',
                channel_price_child: 0.00,
                channel_price_infant: 0.00,
                rooms: [],
                date_from: '',
                date_to: '',
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
            prices: []
        }
    },
    computed: {
        years () {
                let previousYear = moment().subtract(2, 'years').year()
                let currentYear = moment().add(5, 'years').year()

                let years = []

                do {
                    years.push({ value: previousYear, text: previousYear })
                    previousYear++
                } while (currentYear > previousYear)

                return years
            }
    },
    mounted () {
        let currentDate = new Date()
        this.form.channel_id = this.channelID
        this.selectPeriod = currentDate.getFullYear()

        API.get('/policies_cancelations/selectBox?lang=' + localStorage.getItem('lang') + '&hotel_id=' + this.hotelID)
            .then((result) => {
                let policy = result.data.data

                policy.forEach((policy) => {
                    this.policyCancellations.push({
                        label: policy.text,
                        code: policy.value
                    })
                })
            }).catch(() => {
            this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('hotelsmanagehotelconfiguration.error.messages.name'),
                text: this.$t('hotelsmanagehotelconfiguration.error.messages.connection_error')
            })
        })

        this.getDateRanges()

    },
    methods: {
        getDateFormat: function (date) {
            return moment(date).format('L')
        },
        editPrice: async function (_group, date_from, date_to) {
            let vm = this
            let prices = []

            this.prices.forEach((item, i) => {
                if(item.group == _group)
                {
                    prices.push(item)
                }
            })

            this.form.rooms.forEach((item, i) => {
                vm.$set(item, 'channel_child_price', 0)
                vm.$set(item, 'channel_infant_price', 0)
                vm.$set(item, 'price_id', 0)

                prices.forEach((price, p) => {
                    if(price.rate_plan_room.room_id == item.id)
                    {
                        vm.$set(item, 'channel_child_price', price.price_child)
                        vm.$set(item, 'channel_infant_price', price.price_infant)
                        vm.$set(item, 'price_id', price.id)

                        /*
                        item.channel_child_price = price.price_child
                        item.channel_infant_price = price.price_infant
                        item.price_id = price.id
                        */
                    }
                })


            })

            // this.form.rooms = new_rooms
            this.form.date_from = moment(date_from).format('DD/MM/YYYY')
            this.form.date_to = moment(date_to).format('DD/MM/YYYY')

            console.log(this.form)
        },
        deletePrice: function (_price) {
            console.log(_price)

            API.delete('rates/cost/' + this.hotelID + '/' + this.ratePlanID + '/' + _price)
                .then((result) => {
                    if(result.data)
                    {
                        this.getDateRanges()
                    }
                    else
                    {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$i18n.t('hotelsmanagehotelratesratescost.error.messages.name'),
                            text: this.$i18n.t('hotelsmanagehotelratesratescost.error.messages.connection_error')
                        })        
                    }
                }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$i18n.t('hotelsmanagehotelratesratescost.error.messages.name'),
                        text: this.$i18n.t('hotelsmanagehotelratesratescost.error.messages.connection_error')
                    })
                })
        },
        getDateRanges: function () {
            this.prices = []

            API.get('rates/cost/' + this.hotelID + '/' + this.ratePlanID + '/channels' +
                '/?lang=' + localStorage.getItem('lang') + '&channel=' + this.channelID + '&year=' + this.selectPeriod)
                .then((result) => {
                    this.form.rooms = result.data.data
                    this.prices = result.data.prices

                    if (result.data.police.length > 0) {
                        this.form.policies_cancelation = result.data.police[0].policies_cancelation
                    }

                }).catch(() => {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$i18n.t('hotelsmanagehotelratesratescost.error.messages.name'),
                    text: this.$i18n.t('hotelsmanagehotelratesratescost.error.messages.connection_error')
                })
            })
        },
        submit () {
            this.loading = true
            API({
                method: this.formAction,
                url: 'rates/cost/' + this.hotelID + '/' + this.ratePlanID + '/channels',
                data: this.form
            }).then((result) => {
                this.loading = false    
                this.getDateRanges()

                if (result.data.success) {
                    this.form.date_from = ''
                    this.form.date_to = ''

                    this.$notify({
                        group: 'main',
                        type: 'success',
                        title: this.$i18n.t('hotelsmanagehotelratesratescost.channel'),
                        text: this.$i18n.t('hotelsmanagehotelratesratescost.messages.saveok')
                    })
                } else {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$i18n.t('hotelsmanagehotelratesratescost.channel'),
                        text: this.$i18n.t('hotelsmanagehotelratesratescost.messages.savefailed')
                    })
                }
            })
                .catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$i18n.t('hotelsmanagehotelratesratescost.error.messages.name'),
                        text: this.$i18n.t('hotelsmanagehotelratesratescost.error.messages.connection_error')
                    })
                })
        },
        emptyForm () {

        },
        setDateFrom (e) {
            this.$refs.datePickerTo.dp.minDate(e.date)
        },
        setPrice: function (index) {
            if (isNaN(this.form.rooms[index].channel_price_child) || this.form.rooms[index].channel_price_child === '') {
                this.form.rooms[index].channel_price_child = 0.00
            }
            if (isNaN(this.form.rooms[index].channel_price_infant) || this.form.rooms[index].channel_price_infant === '') {
                this.form.rooms[index].channel_price_infant = 0.00
            }
        },
    }
}
</script>

<style lang="stylus"></style>


