<template>
    <div class="container-fluid">

        <div class="form-row">
            <!--            <label class="col-2 col-form-label right">{{ $t('package.copy_from_category') }}</label>-->
            <!--            <div class="col-4 right">-->

            <!--                <v-select :options="categoriesWithServices"-->
            <!--                          :value="categoriesWithServicesId"-->
            <!--                          autocomplete="true"-->
            <!--                          data-vv-as="categories_services"-->
            <!--                          data-vv-name="categories_services"-->
            <!--                          name="categories_services"-->
            <!--                          @input="categoriesServicesChange"-->
            <!--                          v-model="categoriesServicesSelected">-->
            <!--                </v-select>-->

            <!--            </div>-->
            <!--            <div class="col-2 right">-->
            <!--                <button @click="willCopyCategory()" class="btn btn-success">{{$t('global.buttons.accept')}}</button>-->
            <!--            </div>-->
            <div class="col-2 right">
                <!--                <span class="legend trService"/> Servicio <br>-->
                <span class="legend trHotel1"/> Hotel <br>
<!--                <span v-show="legendHotelSecondary"><span class="legend trHotel"/> Hotel (No incluido al calculo de tarifas)</span>-->
            </div>
        </div>

        <table-client :columns="table.columns" :data="servicesAndHotels" :loading="loading"
                      :options="tableOptions" id="dataTable"
                      theme="bootstrap4">
            <div class="table-date" slot="date" slot-scope="props" style="font-size: 0.9em;padding: 10px">
                {{props.row.date_in | formatDate }}<span v-if="props.row.type=='hotel'"> - {{props.row.date_out | formatDate }}</span>
            </div>
            <div class="table-code" slot="code" slot-scope="props" style="font-size: 0.9em;padding: 10px">
                <span v-if="props.row.type=='hotel'">{{props.row.hotel.channel[0].code }}</span>
                <span v-if="props.row.type=='service'">{{props.row.service.aurora_code }}
                            <font-awesome-icon v-if="props.row.service.service_equiv_association_count > 0"
                                               class="text-danger change"
                                               @click="willChangeCategoryService(props.row.id,props.row.service.id)"
                                               :icon="['fas', 'exchange-alt']"/>
                    </span>
            </div>
            <div class="table-description" slot="description" slot-scope="props" style="font-size: 0.9em;padding: 10px">
                <span v-if="props.row.type=='hotel'"><font-awesome-icon :icon="['fas', 'bed']"/> {{props.row.hotel.name }}</span>
                <span v-if="props.row.type=='service'"><font-awesome-icon :icon="['fas', 'bars']"/> {{props.row.service.name }}</span>
            </div>
            <div class="table-rate_plan center" slot="rate_plan" slot-scope="props"
                 style="font-size: 0.9em; min-width: 330px;text-align: center;">

                <div class="col-12" v-if="props.row.type=='hotel'">
                    <div class="col-10 left" style="line-height: 28px;" v-if="props.row.service_rooms.length >= 1">
                        <span v-for="(service_room, srKey) in props.row.service_rooms" v-if="srKey < 3">
                            <span v-if="service_room.rate_plan_room.room.room_type.translations[0]">
                                {{ service_room.rate_plan_room.room.room_type.translations[0].value }}
                            </span>
                            
                            <span
                                v-if="service_room.rate_plan_room.first_rate.length>0 && service_room.rate_plan_room.room.room_type.occupation == 1">

                                <!-- (${{ service_room.rate_plan_room.first_rate[0].price_adult | formatPrice }}) -->
                                (${{ rateProcess(service_room.rate_plan_room.first_rate, 1, service_room.rate_plan_room.channel_id) | formatPrice }})
                         
                                <span @click="deleteServiceRoomHotel(service_room)" style="cursor:pointer;"><font-awesome-icon
                                    :icon="['fas', 'trash']"/></span><br>
                            </span>
                            <span
                                v-if="service_room.rate_plan_room.first_rate.length>0 && service_room.rate_plan_room.room.room_type.occupation == 2">

                                <!-- (${{(service_room.rate_plan_room.first_rate[0].price_adult / 2) | formatPrice }}) -->
                                (${{ rateProcess(service_room.rate_plan_room.first_rate, 2, service_room.rate_plan_room.channel_id) | formatPrice }})

                                 <span @click="deleteServiceRoomHotel(service_room)" style="cursor:pointer;"><font-awesome-icon
                                     :icon="['fas', 'trash']"/></span><br>
                            </span>
                            <span
                                v-if="service_room.rate_plan_room.first_rate.length>0 && service_room.rate_plan_room.room.room_type.occupation == 3">

                                <!-- (${{(service_room.rate_plan_room.first_rate[0].price_adult/3) | formatPrice }}) -->
                                (${{ rateProcess(service_room.rate_plan_room.first_rate, 3, service_room.rate_plan_room.channel_id) | formatPrice }})

                                <span v-if="parseFloat(service_room.rate_plan_room.first_rate[0].price_extra) > 0"> + (${{(service_room.rate_plan_room.first_rate[0].price_extra/3) | formatPrice }})</span>
                                 <span @click="deleteServiceRoomHotel(service_room)" style="cursor:pointer;"><font-awesome-icon
                                     :icon="['fas', 'trash']"/></span><br>
                            </span>
                            <span v-if="service_room.rate_plan_room.first_rate.length==0">
                                ($-)<span @click="deleteServiceRoomHotel(service_room)" style="cursor:pointer;"><font-awesome-icon
                                :icon="['fas', 'trash']"/></span><br>
                            </span>
                        </span>
                        <span v-if="props.row.service_rooms.length > 3"> (y {{ props.row.service_rooms.length - 3 }} más) </span>
                    </div>
                    <div class="col-10 left btn-warning" style="line-height: 25px; padding: 4px 10px;"
                         v-if="props.row.service_rooms.length == 0">
                        <font-awesome-icon :icon="['fas', 'exclamation-circle']"/>
                        Por asignar
                    </div>
                    <div class="col-2 right" v-if="props.row.service_rooms.length > 0"
                         :class="'paddingByCount paddingByCount'+props.row.service_rooms.length">
                        <button @click="viewHotelRates(props.row)" style="float: left;"
                                class="btn btn-sm btn-info"
                                type="button">
                            <font-awesome-icon :icon="['fas', 'money-bill']"/>
                        </button>
                    </div>
                </div>

                <div class="col-12" style="padding: 10px;" v-if="props.row.type=='service'">

                    <div class="col-10 left" v-if="props.row.service_rates.length == 0">
                        <select class="form-control mr-0 ml-0" @change="saveRateServiceSelected($event,props.row)"
                                :name="props.row.id" :key="props.row.id"
                                v-model="props.row.service_rate_id_selected =''">
                            <option value="">Seleccione Tarifa</option>
                            <option :value="rate.id" :disabled="rate.price_from == ''"
                                    v-for="rate in props.row.service.service_rate">{{ rate.name }}
                                <span v-if="rate.price_from != ''">
                                    | (${{ rate.price_from }} x {{ rate.price_from_pax }})
                                </span>

                            </option>
                        </select>
                    </div>

                    <div class="col-10 left" v-if="props.row.service_rates.length > 0">
                        <select class="form-control mr-0 ml-0" @change="saveRateServiceSelected($event,props.row)"
                                :name="props.row.id" :key="props.row.id"
                                v-model="props.row.service_rate_id_selected = props.row.service_rates[0].service_rate_id">
                            <option value="">Seleccione Tarifa</option>
                            <option :value="rate.id" :disabled="rate.price_from == ''"
                                    v-for="rate in props.row.service.service_rate">
                                {{ rate.name }}
                                <span v-if="rate.price_from != ''">
                                    | (${{ rate.price_from }} x {{ rate.price_from_pax }})
                                </span>
                            </option>
                        </select>
                    </div>

                    <div class="col-2 right" style="padding-top: 5px;" v-if="props.row.service_rates.length > 0">
                        <button @click="viewRatesServices(props.row)" style="float: left;"
                                class="btn btn-sm btn-info"
                                type="button">
                            <font-awesome-icon :icon="['fas', 'money-bill']"/>
                        </button>
                    </div>

                </div>

            </div>
            <div class="table-order" slot="order" slot-scope="props" style="font-size: 0.9em;padding: 10px">
                <button @click="orderServices(props.row, -1)"
                        class="btn btn-sm btn-info" style="margin-bottom: 5px;"
                        type="button" :disabled="props.row.disabled_order_up">
                    <font-awesome-icon :icon="['fas', 'sort-up']"/>
                </button>
                <button @click="orderServices(props.row, 1)"
                        class="btn btn-sm btn-info"
                        type="button" :disabled="props.row.disabled_order_down">
                    <font-awesome-icon :icon="['fas', 'sort-down']"/>
                </button>
            </div>
            <div class="table-actions" slot="actions" slot-scope="props" style="padding: 10px">


                <!--                <button v-if="props.row.calculation_included" @click="calculationIncluded(props.row.id, 0)"-->
                <!--                        style="margin-top: 13px; margin-right: 5px"-->
                <!--                        class="btn btn-sm btn-success" title="No incluir markup en el cálculo tarifario"-->
                <!--                        type="button">-->
                <!--                    <font-awesome-icon :icon="['fas', 'money-bill']"/>-->
                <!--                </button>-->

                <!--                <button v-if="!(props.row.calculation_included)" @click="calculationIncluded(props.row.id, 1)"-->
                <!--                        style="margin-top: 13px; margin-right: 5px; background-color: #ffc107"-->
                <!--                        class="btn btn-sm btn-danger" title="Incluir markup en el cálculo tarifario"-->
                <!--                        type="button">-->
                <!--                    <font-awesome-icon :icon="['fas', 'money-bill']"/>-->
                <!--                </button>-->

                <button @click="showModal(props.row.id)" style="margin-top: 13px;"
                        class="btn btn-sm btn-danger"
                        type="button">
                    <font-awesome-icon :icon="['fas', 'trash']"/>
                </button>

            </div>
            <div class="table-loading text-center" slot="loading">
                <img alt="loading" height="51px" src="/images/loading.svg"/>
            </div>
        </table-client>

        <b-modal :title="title_rates_plan" centered ref="my-modal-rates-services" size="lg">
            <div class="row">
                <div class="col-12">
                    <table-client :columns="table.columnsRates"
                                  :data="data_rates_plans"
                                  :loading="false"
                                  :options="tableRatesOptions"
                                  id="rooms-table"
                                  ref="roomsTable"
                                  theme="bootstrap4">

                        <div class="table-range text-center" slot="range"
                             slot-scope="props">
                            {{ props.row.pax_from }} - {{ props.row.pax_to }}
                        </div>
                        <div class="table-period" slot="period"
                             slot-scope="props">
                            {{ props.row.date_from | formatDate }} - {{ props.row.date_to | formatDate }}
                        </div>
                        <div class="table-price_adult" slot="price_adult"
                             slot-scope="props">
                            {{ props.row.price_adult }}
                        </div>
                        <div class="table-price_child" slot="price_child"
                             slot-scope="props">
                            {{ props.row.price_child }}
                        </div>
                        <div class="table-price_infant" slot="price_infant"
                             slot-scope="props">
                            {{ props.row.price_infant }}
                        </div>
                        <div class="table-price_guide" slot="price_guide"
                             slot-scope="props">
                            {{ props.row.price_guide }}
                        </div>
                    </table-client>
                </div>
            </div>
            <div slot="modal-footer">
                <button @click="hideModal()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
            </div>
        </b-modal>

        <b-modal :title="modalName" centered ref="my-modal" size="sm">
            <p class="text-center">{{$t('global.message_delete')}}</p>

            <div slot="modal-footer">
                <button @click="remove()" class="btn btn-success">{{$t('global.buttons.accept')}}</button>
                <button @click="hideModal()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
            </div>
        </b-modal>

        <b-modal title="Copiar servicios" centered ref="my-modal-confirm" size="sm">
            <p class="text-center">La categoría actual ya tiene servicios/hoteles, desea reemplazarlos?</p>

            <div slot="modal-footer">
                <button @click="copyCategory()" class="btn btn-success">{{$t('global.buttons.accept')}}</button>
                <button @click="hideModal()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
            </div>
        </b-modal>

        <b-modal :title="title_rates_hotel" centered ref="my-modal-select-hotel-rates" size="lg">

            <div v-for="(room, rkey) in hotelSwapRates.rooms" v-if="room.rates_plan_room.length > 0">

                <div class="rooms-table row canSelectText">
                    <div class="col-4 my-auto">
                        <strong>Nombre: </strong>{{ room.translations[0].value }}<br>
                        <strong>Descripción: </strong>{{ room.translations[1].value }}
                    </div>
                    <div class="col-8 my-auto">
                        <div v-for="(rate, raKey) in room.rates_plan_room"
                             :class="'col-12 rateRow rateChoosed_' + checkboxs[ '_' + rate.id ]"
                             v-if="rate.calendarys.length > 0">

                            <label style="display: block;" :for="'checkbox_' + rkey + '_' + raKey">
                                <strong>
                                    <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                                    {{ rate.rate_plan.name }}:</strong>
                                <!--                                <b-form-checkbox style="float: right;"-->
                                <!--                                                 :id="'checkbox_' + rkey + '_' + raKey"-->
                                <!--                                                 :name="'checkbox_' + rkey + '_' + raKey"-->
                                <!--                                                 v-model="checkboxs[ '_' + rate.id]"-->
                                <!--                                                 @change="chooseRoom(catKey, rate, hotel.id, rate.id)">-->
                                <!--                                </b-form-checkbox>-->
                            </label>

                            <div style="margin-left: 30px;">
                                <span v-if="rate.calendarys[0].status == 1"><font-awesome-icon
                                    :icon="['fas', 'check-circle']"/></span>
                                <span v-if="rate.calendarys[0].status != 1"><font-awesome-icon
                                    :icon="['fas', 'times-circle']"/></span>
                                {{ rate.calendarys[0].date | formatDate }}
                                <strong>$ <span v-if="rate.calendarys[0].rate[0]"> {{ rate.calendarys[0].rate[0].price_adult | formatPrice }} </span></strong>
                                <span v-if="rate.calendarys.length>1">
                                    <a href="javascript:;" v-show="!(rate.showAllRates)" @click="toggleViewRates(rate)"><font-awesome-icon
                                        :icon="['fas', 'plus']"/></a>
                                    <a href="javascript:;" v-show="rate.showAllRates" @click="toggleViewRates(rate)"><font-awesome-icon
                                        :icon="['fas', 'minus']"/></a>
                                </span>
                            </div>
                            <div style="margin-left: 30px;" v-for="( calendar, cKey) in rate.calendarys"
                                 v-show="rate.showAllRates">
                                <span v-if="cKey > 0">
                                    <span v-if="calendar.status == 1"><font-awesome-icon
                                        :icon="['fas', 'check-circle']"/></span>
                                    <span v-if="calendar.status != 1"><font-awesome-icon
                                        :icon="['fas', 'times-circle']"/></span>
                                    {{ calendar.date | formatDate }}
                                    <strong>$ <span v-if="calendar.rate[0]"> {{ calendar.rate[0].price_adult | formatPrice }} </span></strong>
                                </span>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div slot="modal-footer">
                <button @click="hideModal()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
            </div>
        </b-modal>

        <b-modal title="Elija un servicio:" centered ref="my-modal-service-category" size="lg">
            <table class="table table-bordered">
                <thead class="thead-light text-center">
                <tr>
                    <th>Servicio</th>
                    <th>Tarifa</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(equivalence,indexParent) in equivalences" :key="indexParent">
                    <th class="text-center">
                        <div class="col-md-12 font-weight-normal">
                            [{{equivalence.service.aurora_code}} - {{equivalence.service.equivalence_aurora}}] -
                            {{equivalence.service.name}}
                        </div>
                    </th>
                    <th class="text-center" style="width: 250px">
                        <v-select :options="equivalence.service.service_rate"
                                  track-by="id"
                                  label="name"
                                  :value="rateSelected"
                                  @input="addRate"
                                  placeholder="Seleccione una tarifa"
                                  name="rate"
                                  autocomplete="true"
                                  key="id"
                                  v-model="serviceByRates[indexParent]">
                        </v-select>
                    </th>
                    <th class="text-center">
                        <button @click="changeCategoryService(package_service_id,equivalence.service_equivalence_id)"
                                class="btn btn-sm btn-success mb-sm-1"
                                type="button">
                            <font-awesome-icon :icon="['fas', 'exchange-alt']"/>
                        </button>
                    </th>
                </tr>
                </tbody>
            </table>
        </b-modal>
        <block-page></block-page>
    </div>
</template>

<script>
    import { API } from './../../../../api'
    import TableClient from './.././../../../components/TableClient'
    import Loading from 'vue-loading-overlay'
    import BModal from 'bootstrap-vue/es/components/modal/modal'
    import vSelect from 'vue-select'
    import 'vue-select/dist/vue-select.css'
    import BlockPage from '../../../../components/BlockPage'

    export default {
        name: 'CostRates',
        components: {
            'table-client': TableClient,
            BModal,
            Loading,
            vSelect,
            BlockPage
        },
        data: () => {
            return {
                loading: false,
                checkboxs: [],
                package_service_id: '',
                serviceByRates: [{
                    id: ''
                }],
                rateSelected: '',
                title_rates_plan: '',
                title_rates_hotel: '',
                data_rates_plans: [],
                tmpServDates: [],
                equivalences: [],
                hotelSwapRates: [],
                modalName: '',
                categoriesWithServices: [],
                categoriesWithServicesId: '',
                servicesAndHotels: [],
                categoriesServicesSelected: [],
                legendHotelSecondary: 0,
                table: {
                    columns: ['order', 'date', 'code', 'description', 'rate_plan', 'actions'],
                    columnsRates: ['range', 'period', 'price_adult', 'price_child', 'price_infant', 'price_guide']
                },
            }
        },
        computed: {
            tableOptions: function () {
                return {
                    headings: {
                        order: 'Orden',
                        date: 'Fecha',
                        code: 'Código',
                        description: 'Descripción',
                        rate_plan: 'Plan Tarifario',
                        actions: this.$i18n.t('global.table.actions')
                    },
                    sortable: [],
                    filterable: [],
                    perPageValues: [],
                    rowClassCallback: function (item) {
                        if (!item) return
                        if (item.type == 'service' && item.service_rates.length == 0) {
                            return 'alert trServiceEmptyRate'
                        } else if (item.type == 'service' && item.service_rates.length > 0) {
                            if (item.service.service_rate[0].price_from == '') {
                                return 'alert trServiceEmptyRate'
                            }
                        } else if (item.type == 'service') {
                            return 'trService'
                        } else {
                            return 'trHotel ' + 'trHotel' + item.firstHotel
                        }
                    }
                }
            },
            tableRatesOptions: function () {
                return {
                    headings: {
                        range: 'Rango',
                        period: 'Periodo',
                        price_adult: 'Adulto US$',
                        price_child: 'Niño US$',
                        price_infant: 'Infante US$',
                        price_guide: 'Guía US$'
                    },
                    sortable: [],
                    filterable: false,
                    perPageValues: []
                }
            }
        },
        mounted: function () {
            this.category_id = this.$route.params.category_id
            this.$root.$on('updateCategory', (payload) => {
                this.category_id = payload.categoryId
                this.search()
            })
            this.search()
            this.searchCategoriesWithCountServices()

        },
        methods: {
            rateProcess(rates, occupation, channel){
                if(channel == '1'){
                    return rates[0].price_adult / occupation;
                }else{
                    let rateValue = 0;
                    rates.forEach(rate => {
                        if(rate.num_adult == occupation){
                            rateValue = rate.price_adult
                        } 
                    })
                    if(rateValue == 0){
                        rateValue = rates[0].price_total>0 ? rates[0].price_total: rates[0].price_adult ;
                    }
                    return rateValue / occupation;
                }
            },            
            deleteServiceRoomHotel: function (service_room) {
                API({
                    method: 'DELETE',
                    data: { service_room_id: service_room.id },
                    url: '/package/service/optional/service_room'
                }).then((response) => {
                    console.log('service room eliminado')
                    this.search()
                }).catch((e) => {
                    console.log(e)
                })
            },
            exportExcel () {
                this.loading = true

                let _name_category = ''
                this.categoriesWithServices.forEach(c => {
                    if (c.code == this.$route.params.category_id) {
                        _name_category = c.label
                    }
                })

                let title = localStorage.getItem('packagenamemanage') + ' - ' + _name_category

                API({
                    method: 'GET',
                    url: 'package/package_plan_rate_category/' + this.category_id + '/export/passengers' + '?lang=' +
                        localStorage.getItem('lang') + '&quantity_pax=2',
                    responseType: 'blob',
                })
                    .then((response) => {

                        console.log(response)

                        var fileURL = window.URL.createObjectURL(new Blob([response.data]))
                        var fileLink = document.createElement('a')
                        fileLink.href = fileURL
                        fileLink.setAttribute('download', 'TARIFAS - ' + title + '.xlsx')
                        document.body.appendChild(fileLink)
                        fileLink.click()

                        this.loading = false

                    }).catch(() => {

                    this.loading = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.package'),
                        text: this.$t('global.error.messages.connection_error')
                    })

                })
            },
            reCalculateMarkupRateSale: function () {
                this.$root.$emit('blockPage', { message: 'Actualizando tablas tarifarias de venta Por favor espere' })
                API({
                    method: 'get',
                    url: 'package/rates/sales/markup/recalculate?package_plan_rate_id=' + this.$route.params.package_plan_rate_id
                })
                    .then((result) => {
                        this.search()
                        this.$root.$emit('unlockPage')
                        this.$notify({
                            group: 'main',
                            type: 'success',
                            title: 'Tablas Tarifarias de venta',
                            text: result.data.message
                        })
                    }).catch((error) => {
                    this.$root.$emit('unlockPage')
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Error en actualizacion de tablas Tarifarias de venta',
                        text: ''
                    })
                })
            },
            calculationIncluded (id, _value) {
                API({
                    method: 'POST',
                    url: 'package/service/calculation_included',
                    data: {
                        package_service_id: id,
                        _value: _value
                    }
                })
                    .then((result) => {
                        this.search()
                        this.$notify({
                            group: 'main',
                            type: 'success',
                            title: this.$t('global.modules.package'),
                            text: 'Guardado Correctamente'
                        })
                    }).catch((e) => {
                    console.log(e)
                })
            },
            viewRatesServices (me) {
                let find_service_rate_id = me.service_rates[0].service_rate_id
                this.title_rates_plan = 'Tarifas | ' + me.service.name + '<b>(' + me.service.aurora_code + ')</b>'
                me.service.service_rate.forEach(r => {
                    if (r.id == find_service_rate_id) {
                        this.data_rates_plans = r.service_rate_plans
                        this.$refs['my-modal-rates-services'].show()
                        return
                    }
                })
            },
            saveRateServiceSelected: function (event, service) {
                if (event.target.value != '') {
                    if (service.service_rates.length == 0) {
                        API({
                            method: 'POST',
                            url: 'package/service/rate/selected',
                            data: {
                                service_id: service.id,
                                service_rate_id: event.target.value,
                            }
                        })
                            .then((result) => {
                                this.search()
                                this.$notify({
                                    group: 'main',
                                    type: 'success',
                                    title: this.$t('global.modules.package'),
                                    text: result.data.message
                                })
                            }).catch((e) => {
                            console.log(e)
                        })
                    }
                    if (service.service_rates.length > 0) {
                        API({
                            method: 'POST',
                            url: 'package/service/rate/selected',
                            data: {
                                service_id: service.id,
                                service_rate_id: event.target.value,
                                package_service_rate_id: service.service_rates[0].id
                            }
                        })
                            .then((result) => {
                                this.search()
                                this.$notify({
                                    group: 'main',
                                    type: 'success',
                                    title: this.$t('global.modules.package'),
                                    text: result.data.message
                                })
                            }).catch((e) => {
                            console.log(e)
                        })
                    }

                } else {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.package'),
                        text: 'Debe Seleccionar una Tarifa'
                    })
                }

            },
            viewHotelRates (me) {
                console.log(me)
                this.hotelSwapRates = []
                let data = {
                    'hotel_id': me.object_id,
                    'date_from': me.date_in,
                    'date_to': me.date_out,
                    'admin': 1
                }
                API({
                    method: 'POST',
                    url: window.origin + '/services/hotel/services',
                    data: data
                })
                    .then((result) => {
                        this.hotelSwapRates = result.data.data
                        // for show rates
                        for (let r = 0; r < this.hotelSwapRates.rooms.length; r++) {
                            for (let r_p_r = 0; r_p_r < this.hotelSwapRates.rooms[r].rates_plan_room.length; r_p_r++) {
                                if (typeof (this.hotelSwapRates.rooms[r].rates_plan_room[r_p_r].showAllRates) === 'undefined') {
                                    this.hotelSwapRates.rooms[r].rates_plan_room[r_p_r].showAllRates = 0
                                }
                            }
                        }

                        this.checkboxs = []
                        me.service_rooms.forEach(s_rooms => {
                            this.checkboxs['_' + s_rooms.rate_plan_room_id] = true
                        })

                        this.title_rates_hotel = 'Tarifas de Hotel: [' + me.hotel.channel[0].code + '] - ' + me.hotel.name
                        this.$refs['my-modal-select-hotel-rates'].show()

                    }).catch((e) => {
                    console.log(e)
                })
            },
            willCopyCategory () {
                // VALIDAR SI ESTA VACIO
                if (this.categoriesServicesSelected.code == undefined) {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.package'),
                        text: this.$t('packagesquote.selectcategory')
                    })
                    return
                }
                // VALIDAR SI YA TIENE SERVICIOS
                if (this.servicesAndHotels.length == 0) {
                    this.copyCategory()
                } else {
                    this.$refs['my-modal-confirm'].show()
                }
            },
            copyCategory () {
                this.loading = true
                API({
                    method: 'POST',
                    url: 'package/plan_rates/categories/copy',
                    data: {
                        plan_rate_category_id_from: this.categoriesServicesSelected.code,
                        plan_rate_category_id_to: this.$route.params.category_id
                    }
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            // this.reCalculateMarkupRateSale()
                            this.loading = false
                            this.hideModal()
                        } else {

                            this.loading = false
                            if (result.data.success === false && result.data.type == 0) {

                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: this.$t('global.modules.package'),
                                    text: 'La categoría que seleccionó no tiene servicios/hoteles'
                                })
                            } else {
                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: this.$t('global.modules.package'),
                                    text: this.$t('global.error.messages.connection_error')
                                })
                            }
                        }
                    })
            },
            categoriesServicesChange () {
                this.categoriesWithServicesId = this.categoriesServicesSelected.code
            },
            searchCategoriesWithCountServices () {
                API({
                    method: 'get',
                    url: '/package/plan_rates/' + this.$route.params.package_plan_rate_id +
                        '?lang=' + localStorage.getItem('lang')
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            let categories = result.data.data.plan_rate_categories
                            for (let i = 0; i < categories.length; i++) {
                                // if (this.$route.params.category_id != categories[i].id) {
                                this.categoriesWithServices.push({
                                    code: categories[i].id,
                                    label: categories[i].category.translations[0].value,
                                })
                                // }
                            }
                        }
                    }).catch((e) => {
                    console.log(e)
                })
            },
            showModal (package_service_id) {
                this.package_service_id = package_service_id
                this.modalName = 'Servicio n°: ' + this.package_service_id
                this.$refs['my-modal'].show()
            },
            hideModal () {
                this.$refs['my-modal'].hide()
                this.$refs['my-modal-confirm'].hide()
                this.$refs['my-modal-service-category'].hide()
                this.$refs['my-modal-select-hotel-rates'].hide()
                this.$refs['my-modal-rates-services'].hide()
            },
            search: function () {
                this.loading = true
                this.servicesAndHotels = []

                API.get('/package/package_plan_rate_category_optional/' + this.category_id).then((result) => {
                    this.loading = false
                    this.servicesAndHotels = result.data.data

                    this.tmpServDates = []
                    this.tmpServDatesNormal = []

                    this.servicesAndHotels.forEach(serv => {
                        if (!(this.tmpServDates[serv.date_in])) {
                            this.tmpServDates[serv.date_in] = []
                            this.tmpServDatesNormal.push(serv.date_in)
                            // BLOCK UP
                            let n = 0
                            this.servicesAndHotels.forEach((servJoinDate, key) => {
                                if (servJoinDate.date_in == serv.date_in) {
                                    this.tmpServDates[serv.date_in].push(key)
                                    if (n == 0) {
                                        servJoinDate.disabled_order_up = true
                                    }
                                    n++
                                    servJoinDate.order = n
                                }
                            })
                            // BLOCK DOWN
                            if (n == this.tmpServDates[serv.date_in].length) {
                                this.servicesAndHotels[this.tmpServDates[serv.date_in][this.tmpServDates[serv.date_in].length - 1]]
                                    .disabled_order_down = true
                            }
                        }
                    })

                    this.tmpServDatesNormal.forEach(arrayDatesNormal => {
                        let nByDate = 0
                        this.tmpServDates[arrayDatesNormal].forEach(keyServsHots => {

                            if (this.servicesAndHotels[keyServsHots].type == 'hotel' && nByDate > 0) {
                                this.legendHotelSecondary = 1
                            }

                            if (this.servicesAndHotels[keyServsHots].type == 'hotel' && nByDate == 0) {
                                this.servicesAndHotels[keyServsHots].firstHotel = 1
                                nByDate++
                            }
                        })
                    })

                }).catch(() => {
                    this.loading = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.package'),
                        text: this.$t('global.error.messages.connection_error')
                    })
                })
            },
            updateDestinations () {
                API.get(window.origin + '/destinations/update?package_id=' + this.$route.params.package_id).then((result) => {
                    console.log('Destinos actualizados')
                }).catch((e) => {
                    console.log(e)
                })
            },
            remove: function () {
                API.delete('/package/service/optional/' + this.package_service_id).then((result) => {
                    if (result.data.success === true) {
                        // this.reCalculateMarkupRateSale()
                        this.search()
                        this.hideModal()
                        // this.updateRates()
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Error al eliminar ',
                            text: 'Por favor inténtelo más tarde'
                        })
                    }
                }).catch(() => {
                    this.loading = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.package'),
                        text: this.$t('global.error.messages.connection_error')
                    })
                })
            },
            orderServices: function (servPick, nDirectionOrder) {

                let data = {
                    newOrders: []
                }

                this.tmpServDates[servPick.date_in].forEach(servDate => {
                    if (this.servicesAndHotels[servDate].id == servPick.id) {
                        // UP -1
                        data.newOrders.push(
                            {
                                id: this.servicesAndHotels[servDate].id,
                                order: this.servicesAndHotels[servDate + nDirectionOrder].order
                            },
                            {
                                id: this.servicesAndHotels[servDate + nDirectionOrder].id,
                                order: this.servicesAndHotels[servDate].order
                            })
                    }
                })

                API.post('/package/service/optional/orders', data).then((result) => {
                    if (result.data.success === true) {
                        this.search()
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Error al reordenar ',
                            text: 'Por favor inténtelo más tarde'
                        })
                    }
                }).catch(() => {
                    this.loading = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.package'),
                        text: this.$t('global.error.messages.connection_error')
                    })
                })

            },
            willChangeCategoryService: function (id, service_id) {
                this.equivalences = []
                this.rateSelected = ''
                this.serviceByRates = [{
                    id: ''
                }]
                this.package_service_id = id
                API.get('service/' + service_id + '/equivalence_associations').then((result) => {
                    if (result.data.success === true) {
                        this.equivalences = result.data.data
                    }
                }).catch(() => {
                    this.loading = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.package'),
                        text: this.$t('global.error.messages.connection_error')
                    })
                })
                this.$refs['my-modal-service-category'].show()
            },
            changeCategoryService: function (package_service_id, service_id) {
                if (this.rateSelected === '') {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.package'),
                        text: 'Debe seleccionar una tarifa'
                    })
                } else {
                    let data = {
                        'id': package_service_id,
                        'service_id': service_id,
                        'service_rate_id': this.rateSelected,
                    }
                    API.post('/package/service/change_service', data).then((result) => {
                        if (result.data.success === true) {
                            this.$refs['my-modal-service-category'].hide()
                            this.search()
                            // this.updateRates()
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Error al cambiar el servicio',
                                text: 'Por favor inténtelo más tarde'
                            })
                        }
                    }).catch(() => {
                        this.loading = false
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.package'),
                            text: this.$t('global.error.messages.connection_error')
                        })
                    })
                }
            },
            updateRates () {
                API.get(window.origin + '/prices?category_id=' + this.$route.params.category_id).then((result) => {
                    // this.reCalculateMarkupRateSale()
                    this.$notify({
                        group: 'main',
                        type: 'success',
                        title: this.$t('global.modules.package'),
                        text: this.$t('packages.rates_updated')
                    })
                    console.log('Tarifas actualizadas')
                    this.loadingUpdate = 0
                    console.log(result)
                }).catch((e) => {
                    console.log(e)
                })
            },
            addRate: function (rateSelected) {
                this.rateSelected = rateSelected.id
            },
            toggleViewRates (rate) {
                this.loading = true
                rate.showAllRates = !(rate.showAllRates)
                this.loading = false
            }
        },
        filters: {
            formatDate: function (_date) {
                _date = _date.split('-')
                _date = _date[2] + '/' + _date[1] + '/' + _date[0]
                return _date
            },

            formatPrice: function (price) {
                return parseFloat(price).toFixed(2)
            }
        }

    }
</script>

<style>
    .legend {
        height: 15px;
        width: 15px;
        display: block;
        float: left;
        margin: 2px;
    }

    .container-fluid {
        margin-top: 20px;
    }

    .trService, .trService > th, .trService > td {
        background-color: #dcfeff;
    }

    .trHotel, .trHotel > th, .trHotel > td {
        background-color: #edece7;
    }

    .trService:hover, .trService:hover > th, .trService:hover > td {
        background-color: #c5fcff;
    }

    .trHotel1, .trHotel1 > th, .trHotel1 > td {
        background-color: #fffbd1;
    }

    .change {
        cursor: pointer;
    }

    .left {
        float: left;
    }

    .right {
        float: right;
    }

    .paddingByCount {
        padding-top: 49px;
        padding-left: 18px;
    }

    .paddingByCount1 {
        padding-top: 13px !important;
    }

    .paddingByCount2 {
        padding-top: 21px !important;
    }

    .paddingByCount3 {
        padding-top: 34px !important;
    }

    .rateChoosed_true {
        background: #deffe2;
        border: solid 1px #39573b;
    }

    .rateRow {
        border-radius: 3px;
        padding: 4px 0 4px 6px;
    }

    .trServiceEmptyRate {
        color: #813838;
        background-color: #fee2e1 !important;
        border-color: #fdd6d6 !important;
    }

</style>
