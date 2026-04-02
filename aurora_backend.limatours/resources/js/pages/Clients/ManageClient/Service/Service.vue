<template>
    <div class="row col-12">
        <div class="col-12">
            <div class="row">
                <div class="col-5 pull-right">
                    <div class="b-form-group form-group">
                        <div class="form-row">
                            <label class="col-sm-3 col-form-label" for="period">{{ $t('period') }}</label>
                            <div class="col-sm-8">
                                <select @change="searchPeriod" ref="period" class="form-control" id="period" required
                                        size="0" v-model="selectPeriod">
                                    <option :value="period.text" v-for="period in periods">
                                        {{ period.text}} - {{period.porcen_service}} %
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
        <div class="col-md-12 mt-2 mb-2">
            <hr>
        </div>
        <div class="form-row col-12">
            <div class="col-sm-6">
                <label class="col-form-label"><i class="fas fa-filter"></i> Filtro: Bloquear todos menos los destinos
                    de:</label>
                <multiselect :clear-on-select="false"
                             :close-on-select="false"
                             :hide-selected="true"
                             :multiple="true"
                             :options="ubigeos"
                             :placeholder="this.$t('hotels.search.messages.hotel_ubigeo_search')"
                             :preserve-search="true"
                             :tag-placeholder="this.$t('hotels.search.messages.hotel_ubigeo_search')"
                             :taggable="true"
                             @tag="addUbigeos"
                             label="name"
                             ref="multiselect"
                             track-by="code"
                             v-model="ubigeoSelected">
                </multiselect>
            </div>
            <div class="col-sm-2">
                <img src="/images/loading.svg" v-if="loading" width="40px"
                     style="float: right; margin-top: 35px;"/>
                <button @click="saveFilter()" class="btn btn-success" type="submit" v-if="!loading"
                        style="float: left; margin-top: 35px;">
                    <font-awesome-icon :icon="['fas', 'save']"/>
                    Guardar
                </button>
            </div>
        </div>
        <div class="col-md-12 mt-2 mb-2">
            <hr>
        </div>
        <div class="col-5">
            <label class="col-sm-12 col-form-label" for="period">{{ $t('available_services') }}</label>
            <div class="input-group">
                 <span class="input-group-append">
                    <button class="btn btn-outline-secondary button_icon" type="button">
                        <font-awesome-icon :icon="['fas', 'search']"/>
                    </button>
                 </span>
                <input class="form-control" id="search_services" type="search" v-model="query" value="">
            </div>
            <ul class="style_list_ul" id="list_services" ref="servicesList" style="background-color: #4dbd7429;">
                <draggable :list="services">
                    <li :class="{'style_list_li':true, 'item':true, 'selected':service.selected}" :id="'service_'+index"
                        @click="selectService(service,index)" v-for="(service,index) in services">
                        <span class="style_span_li">
                            <span class="alert alert-warning"
                                  style="padding:0px !important">[{{ service.aurora_code}}]</span>
                            {{ service.name}} ({{ service.markup ? (service.markup == '0'  ? ' no definido ': service.markup) : ((porcentage == "" || porcentage == 0) ? ' no definido ': porcentage )}}%)
                        </span>
                        <button type="button" class="btn btn-success btn-xs" @click="blockService(service,index)">
                            Bloquear <i class="fas fa-arrow-right"></i>
                        </button>
                    </li>
                </draggable>
            </ul>
        </div>
        <div class="col-1 mt-4 mr-2">
            <div class="col-12">
                <button @click="moveOneService()" class="btn btn-secondary mover_controls btn-block pr-3">
                    <font-awesome-icon :icon="['fas', 'angle-right']"/>
                </button>
            </div>
            <div class="col-12">
                <button @click="inverseOneService()" class="btn btn-secondary mover_controls btn-block pr-3">
                    <font-awesome-icon :icon="['fas', 'angle-left']"/>
                </button>
            </div>
        </div>
        <div class="col-5">
            <label class="col-sm-12 col-form-label" for="period">{{ $t('service.service_blocked') }}</label>
            <div class="input-group">
                <span class="input-group-append">
                    <button class="btn btn-outline-secondary button_icon" type="button">
                        <font-awesome-icon :icon="['fas', 'search']"/>
                    </button>
                </span>
                <input class="form-control" id="search_services_selected" type="search"
                       v-model="query_services_selected"
                       value="">
            </div>
            <ul class="style_list_ul" id="list_services_selected" ref="servicesListSelected" style="background-color: #bd0d121c;">
                <draggable :list="services_selected" class="list-group">
                    <li :class="{'style_list_li':true, 'item':true, 'selected':service.selected}"
                        @click="selectServiceServicesSelected(service,index)"
                        v-for="(service,index) in services_selected">
                        <span class="style_span_li">
                            <span class="alert alert-warning"
                                  style="padding:0px !important">[{{ service.aurora_code}}]</span> {{ service.name}}
                        </span>
                        <button type="button" class="btn btn-success btn-xs" @click="inverseService(service,index)">
                            <i class="fas fa-arrow-left"></i> Desbloquear
                        </button>
                    </li>
                </draggable>
            </ul>
        </div>
        <div class="col-10">
            <div class="row">
                <div class="col-5 pull-left">
                    <div class="b-form-group form-group">
                        <div class="form-row">
                            <label class="col-sm-6 col-form-label" for="markup">{{ $t('personal_markup') }}</label>
                            <div class="col-sm-6">
                                <input :class="{'form-control':true }"
                                       id="markup" name="markup"
                                       type="text"
                                       ref="auroraCodeName" v-model="markup"
                                >
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <button @click="updateOneService" class="btn btn-success mb-4" type="reset">
                        {{ $t('update') }}
                    </button>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>

        <!-- Bloque de tarifas asociadas a los servicios -->

        <div class="col-5">
            <label class="col-sm-12 col-form-label" for="period">{{
                $t('clientsmanageclienthotel.available_hotels_rates')
                }}</label>

            <ul class="style_list_ul" id="list_rates"
                style="border-top:1px solid #ccc;max-height: 196px;height: 196px;background-color: #4dbd7429;">
                <draggable :list="rates">
                    <li :class="{'style_list_li':true, 'item':true, 'selected':rate.selected}" :id="'rate_'+index"
                        @click="selectRate(rate,index)" v-for="(rate,index) in rates">
                        <span
                            class="style_span_li">{{ rate.name}} ({{ rate.markup ? (rate.markup == "0" ? ' no definido ': rate.markup) : selectMarkup() == "0" ?  ' no definido ' : ((selectMarkup() == "0" || selectMarkup() == "") ? ' no definido ': selectMarkup()) }}%)</span>
                    </li>
                </draggable>
            </ul>
        </div>
        <div class="col-1 mt-4 mr-2">
<!--            <div class="col-12">-->
<!--                <button @click="moveOneRate()" class="btn btn-secondary mover_controls btn-block pr-3">-->
<!--                    <font-awesome-icon :icon="['fas', 'angle-right']"/>-->
<!--                </button>-->
<!--            </div>-->
<!--            <div class="col-12">-->
<!--                <button @click="moveAllRates()" class="btn btn-secondary mover_controls btn-block pr-3">-->
<!--                    <font-awesome-icon :icon="['fas', 'angle-double-right']"/>-->
<!--                </button>-->
<!--            </div>-->
<!--            <div class="col-12">-->
<!--                <button @click="inverseOneRate()" class="btn btn-secondary mover_controls btn-block pr-3">-->
<!--                    <font-awesome-icon :icon="['fas', 'angle-left']"/>-->
<!--                </button>-->
<!--            </div>-->
<!--            <div class="col-12">-->
<!--                <button @click="inverseAllRates()" class="btn btn-secondary mover_controls btn-block pr-3">-->
<!--                    <font-awesome-icon :icon="['fas', 'angle-double-left']"/>-->
<!--                </button>-->
<!--            </div>-->
        </div>
        <div class="col-5">
            <label class="col-sm-12 col-form-label" for="period">{{ $t('clientsmanageclienthotel.hotels_rates_blocked')
                }}</label>
            <ul class="style_list_ul" id="list_rates_selected"
                style="border-top:1px solid #ccc;max-height: 196px;height: 196px;background-color: #bd0d121c;">
                <draggable :list="rates_selected" class="list-group">
                    <li :class="{'style_list_li':true, 'item':true, 'selected':rate.selected}"
                        @click="selectRateRatesSelected(rate,index)" v-for="(rate,index) in rates_selected">
                        <span class="style_span_li">{{ rate.name}}</span>
                    </li>
                </draggable>
            </ul>
        </div>
<!--        <div class="col-10">-->
<!--            <div class="row">-->
<!--                <div class="col-5 pull-left">-->
<!--                    <div class="b-form-group form-group">-->
<!--                        <div class="form-row">-->
<!--                            <label class="col-sm-6 col-form-label" for="markup">{{-->
<!--                                $t('clientsmanageclienthotel.personal_markup') }}</label>-->
<!--                            <div class="col-sm-6">-->
<!--                                <input :class="{'form-control':true }"-->
<!--                                       id="markup_rate" name="markup_rate"-->
<!--                                       type="text"-->
<!--                                       ref="auroraCodeName" v-model="markup_rate"-->
<!--                                >-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                </div>-->
<!--                <div class="col-5">-->
<!--                    <button @click="updateOneRate" class="btn btn-success mb-4" type="reset">-->
<!--                        {{ $t('clientsmanageclienthotel.update') }}-->
<!--                    </button>-->
<!--                </div>-->
<!--                <div class="clearfix"></div>-->
<!--            </div>-->
<!--        </div>-->

    </div>
</template>
<script>
    import { API, APISERVICE } from './../../../../api'
    import draggable from 'vuedraggable'
    import TableClient from './.././../../../components/TableClient'
    import Multiselect from 'vue-multiselect'
    import vSelect from 'vue-select'
    import 'vue-select/dist/vue-select.css'

    export default {
        components: {
            draggable,
            vSelect,
            Multiselect,
            'table-client': TableClient,
        },
        data () {
            return {
                ubigeos: [],
                ubigeoSelected: [],
                users: [],
                scroll_limit: 2900,
                services: [],
                page: 1,
                ubigeo_id: '',
                markup_rate: '',
                selectPeriod: '',
                selectRatePlan: '',
                nameSelectService: '',
                rates_selected: [],
                porcentage: '',
                markup: '',
                periods: [],
                clientRates: [],
                clientRatesSelected: [],
                limit: 100,
                count: 0,
                num_pages: 1,
                query: '',
                interval: null,
                services_selected: [],
                page_services_selected: 1,
                limit_services_selected: 100,
                count_services_selected: 0,
                num_pages_services_selected: 1,
                query_services_selected: '',
                scroll_limit_services_selected: 2900,
                interval_services_selected: null,
                loading: false,
                rates: [],
                table: {
                    columns: ['name', 'markup', 'delete'],
                },
            }
        },
        computed: {
            tableOptions: function () {
                return {
                    headings: {
                        id: 'ID',
                        name: this.$i18n.t('associated_fees'),
                        markup: this.$i18n.t('markup'),
                        delete: this.$i18n.t(''),
                    },
                    sortable: ['id'],
                    filterable: [],
                }
            },
        },
        mounted: function () {
            this.init();
        },
        methods: {
            init: function(){
                this.getPeriods()
                this.getServicesSelected()
                this.loadubigeo()
                let search_services = document.getElementById('search_services')
                let timeout_services
                search_services.addEventListener('keydown', () => {
                    clearTimeout(timeout_services)
                    timeout_services = setTimeout(() => {
                        this.getServices()
                        clearTimeout(timeout_services)
                    }, 1000)
                })

                let search_services_selected = document.getElementById('search_services_selected')
                let timeout_services_selected
                search_services_selected.addEventListener('keydown', () => {
                    clearTimeout(timeout_services_selected)
                    timeout_services_selected = setTimeout(() => {
                        this.getServicesSelected()
                        clearTimeout(timeout_services_selected)
                    }, 1000)
                })

                this.interval = setInterval(this.getScrollTop, 3000)
                this.interval_services_selected = setInterval(this.getScrollTopServicesSelected, 3000)
            },
            searchPeriod: function () {
                this.getServices()
                this.getServicesSelected()
                this.searchMarkup()
                this.searchMarkupId()
            },
            searchMarkup: function () {
                let data = this.periods.find(period => period.text == this.selectPeriod)
                this.porcentage = data.porcen_service
            },
            searchMarkupId: function () {
                let data = this.periods.find(period => period.text == this.selectPeriod)
                this.markupId = data.value
            },
            selectMarkup: function () {
                let search_service = this.searchSelectService()
                if (search_service !== -1) {
                    let services = this.services[search_service]
                    if (services.markup) {
                        return services.markup
                    } else {
                        return this.porcentage
                    }
                }
            },
            showError: function (title, text, isLoading = true) {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: title,
                    text: text,
                })
                if (isLoading === true) {
                    this.loading = true
                }
            },
            validateMarkup: function () {
                if (this.markup == '') {
                    this.showError(
                        this.$t('clientsmanageclienthotel.title'),
                        this.$t('clientsmanageclienthotel.error.messages.add_markup'),
                    )
                    return false
                }
            },
            validatePeriod: function () {
                if (this.selectPeriod == '') {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('title'),
                        text: this.$t('error.messages.select_period'),
                    })
                    return false
                }
            },
            selectService: function (service, index) {
                if (this.services[index].selected) {
                    this.$set(this.services[index], 'selected', false)
                } else {
                    this.getClientRatePlans(service.service_id)
                    this.getClientRatePlanSelected(service.service_id)
                    this.nameSelectService = service.name
                    this.setPropertySelectedInServices()
                    this.$set(this.services[index], 'selected', true)
                    this.markup = service.markup
                }
            },
            selectServiceServicesSelected: function (service, index) {
                if (this.services_selected[index].selected) {
                    this.$set(this.services_selected[index], 'selected', false)
                } else {
                    this.getClientRatePlans(service.service_id)
                    this.getClientRatePlanSelected(service.service_id)
                    this.nameSelectService = service.name
                    this.setPropertySelectedInServicesSelected()
                    this.$set(this.services_selected[index], 'selected', true)
                    this.markup = service.markup
                }
            },
            searchSelectService: function () {
                for (let i = 0; i < this.services.length; i++) {
                    if (this.services[i].selected) {
                        return i
                        break
                    }
                }
                return -1
            },
            searchSelectServiceServicesSelected: function () {
                for (let i = 0; i < this.services_selected.length; i++) {
                    if (this.services_selected[i].selected) {
                        return i
                        break
                    }
                }
                return -1
            },
            setPropertySelectedInServices: function () {
                for (let i = 0; i < this.services.length; i++) {
                    this.$set(this.services[i], 'selected', false)
                }
            },
            setPropertySelectedInServicesSelected: function () {
                for (let i = 0; i < this.services_selected.length; i++) {
                    this.$set(this.services_selected[i], 'selected', false)
                }
            },
            moveOneService: function () {
                if (this.loading === false) {
                    if (this.validatePeriod() != false) {
                        this.searchMarkup()

                        this.loading = true
                        let search_service = this.searchSelectService()
                        if (search_service !== -1) {
                            API({
                                method: 'post',
                                url: 'client_services/store',
                                data: {
                                    data: this.services[search_service],
                                    period: this.selectPeriod,
                                    porcentage: this.porcentage,
                                    region_id: this.$route.params.region_id
                                },
                            }).then((result) => {
                                if (result.data.success === true) {

                                    this.$set(this.services[search_service], 'selected', false)
                                    this.services[search_service].service_client_id = result.data.service_client_id
                                    this.services[search_service].markup = this.porcentage
                                    this.services_selected.push(this.services[search_service])
                                    this.services.splice(search_service, 1)
                                    this.loading = false
                                }
                            }).catch(() => {
                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: this.$t('error.messages.name'),
                                    text: this.$t('error.messages.connection_error'),
                                })
                            })
                        } else {
                            if (this.services.length > 0) {
                                this.loading = true
                                let element = this.services.shift()
                                API({
                                    method: 'post',
                                    url: 'client_services/store',
                                    data: {
                                        data: element,
                                        period: this.selectPeriod,
                                        porcentage: this.porcentage,
                                        region_id: this.$route.params.region_id
                                    },
                                }).then((result) => {
                                    if (result.data.success === true) {
                                        element.service_client_id = result.data.service_client_id
                                        this.services_selected.push(element)

                                        this.loading = false
                                    }
                                }).catch((e) => {
                                    this.$notify({
                                        group: 'main',
                                        type: 'error',
                                        title: this.$t('error.messages.name'),
                                        text: this.$t('error.messages.connection_error') + e,
                                    })
                                })
                            }
                        }

                    }
                } else {
                    // console.log('Bloqueado accion');
                }
            },
            validateBeforeSubmit () {
                if (this.selectRatePlan == '') {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('title'),
                        text: this.$t('error.messages.select_rate'),
                    })
                    return false
                }
                this.addRatePlan()
            },
            updateOneService: function () {
                if (this.loading == false) {
                    // console.log(this.validateMarkup());
                    if (this.validateMarkup() != false) {
                        this.loading = true
                        let search_service = this.searchSelectService()
                        if (search_service !== -1) {
                            API({
                                method: 'put',
                                url: 'client_services/update',
                                data: {
                                    service_id: this.services[search_service].service_id,
                                    client_id: this.$route.params.client_id,
                                    period: this.selectPeriod,
                                    markup: this.markup,
                                    markupId: this.markupId,
                                    region_id: this.$route.params.region_id
                                },
                            }).then((result) => {
                                if (result.data.success === true) {
                                    this.getServices()
                                    this.getServicesSelected()
                                    this.clientRates = []
                                    this.clientRatesSelected = []
                                    this.loading = false
                                }
                            }).catch(() => {
                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: this.$t('error.messages.name'),
                                    text: this.$t('error.messages.connection_error'),
                                })
                            })
                        } else {
                            this.showError(
                                this.$t('clientsmanageclienthotel.error.messages.name'),
                                this.$t('clientsmanageclienthotel.error.messages.connection_error'),
                            )
                            this.loading = false
                        }
                    } else {
                        this.loading = false
                    }
                } else {
                    // console.log('Bloqueado accion ' + this.loading);
                }
            },
            addRatePlan: function () {
                if (this.loading === false) {
                    if (this.validatePeriod() != false) {
                        this.searchMarkup()
                        this.loading = true
                        let search_service = this.searchSelectServiceServicesSelected()
                        if (search_service !== -1) {

                            API({
                                method: 'post',
                                url: 'service_client_rate_plans/store',
                                data: {
                                    client_id: this.$route.params.client_id,
                                    period: this.selectPeriod,
                                    service_rate_id: this.selectRatePlan,
                                    region_id: this.$route.params.region_id
                                },
                            }).then((result) => {
                                if (result.data.success === true) {
                                    this.getClientRatePlans(this.services_selected[search_service].service_id)
                                    this.getClientRatePlanSelected(this.services_selected[search_service].service_id)
                                    this.loading = false
                                }
                            }).catch(() => {
                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: this.$t('error.messages.name'),
                                    text: this.$t('error.messages.connection_error'),
                                })
                            })
                        } else {
                            if (this.services_selected.length > 0) {
                                this.loading = true
                                let element = this.services_selected.shift()
                                API({
                                    method: 'post',
                                    url: 'service_client_rate_plans/store',
                                    data: {
                                        client_id: this.$route.params.client_id,
                                        period: this.selectPeriod,
                                        porcentage: this.porcentage,
                                        service_rate_id: this.selectRatePlan,
                                        region_id: this.$route.params.region_id
                                    },
                                }).then((result) => {
                                    if (result.data.success === true) {
                                        this.getClientRatePlans(element.service_id)
                                        this.getClientRatePlanSelected(element.service_id)
                                        this.loading = false
                                    }
                                }).catch((e) => {
                                    this.$notify({
                                        group: 'main',
                                        type: 'error',
                                        title: this.$t('error.messages.name'),
                                        text: this.$t('error.messages.connection_error') + e,
                                    })
                                })
                            }
                        }
                    }
                } else {
                    // console.log('Bloqueado accion');
                }
            },
            updateMarkupRate (id, data) {
                if (this.validatePeriod() != false) {

                    this.searchMarkup()

                    this.loading = true
                    API({
                        method: 'put',
                        url: 'service_client_rate_plans/update',
                        data: { data: data },
                    }).then((result) => {
                        if (result.data.success === false) {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('modules.contacts'),
                                text: this.$t('error.messages.contact_incorrect'),
                            })
                            this.loading = false
                        } else {
                            this.loading = false
                            this.getClientRatePlans(data.service_rate.service_id)
                            this.getClientRatePlanSelected(data.service_rate.service_id)
                        }
                    }).catch(() => {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('error.messages.name'),
                            text: this.$t('error.messages.connection_error'),
                        })
                    })
                }
            },
            inverseOneService: function () {
                if (this.loading === false) {
                    if (this.validatePeriod() != false) {
                        this.searchMarkup()

                        this.loading = true

                        let search_service = this.searchSelectServiceServicesSelected()
                        if (search_service !== -1) {

                            API({
                                method: 'post',
                                url: 'client_services/inverse',
                                data: {
                                    data: this.services_selected[search_service],
                                    period: this.selectPeriod,
                                },
                            }).then((result) => {
                                if (result.data.success === true) {
                                    this.getServices()
                                    this.getServicesSelected()
                                    this.clientRates = []
                                    this.clientRatesSelected = []
                                    this.markup = ''
                                    this.loading = false
                                }
                            }).catch(() => {
                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: this.$t('error.messages.name'),
                                    text: this.$t('error.messages.connection_error'),
                                })
                            })
                        } else {
                            if (this.services_selected.length > 0) {
                                this.loading = true
                                let element = this.services_selected.shift()
                                API({
                                    method: 'post',
                                    url: 'client_services/inverse',
                                    data: {
                                        data: element,
                                        period: this.selectPeriod,
                                    },
                                }).then((result) => {
                                    if (result.data.success === true) {
                                        this.services.push(element)
                                        this.markup = ''
                                        this.loading = false
                                    }
                                }).catch((e) => {
                                    this.$notify({
                                        group: 'main',
                                        type: 'error',
                                        title: this.$t('error.messages.name'),
                                        text: this.$t('error.messages.connection_error') + e,
                                    })
                                })
                            }
                        }
                    }
                } else {
                    // console.log('Bloqueado accion');
                }
            },
            moveAllServices: function () {
                if (this.loading === false) {
                    if (this.validatePeriod() !== false) {
                        this.searchMarkup()

                        this.loading = true

                        if (this.services.length > 0) {
                            for (let i = 0; i < this.services.length; i++) {
                                this.$set(this.services[i], 'selected', false)
                                this.services_selected.push(this.services[i])
                            }
                            this.services = []

                            API({
                                method: 'post',
                                url: 'client_services/store/all',
                                data: {
                                    client_id: this.$route.params.client_id,
                                    period: this.selectPeriod,
                                    porcentage: this.porcentage,
                                },
                            }).then((result) => {
                                if (result.data.success === true) {
                                    this.getServicesSelected()
                                    this.markup = ''
                                    this.loading = false
                                }
                            }).catch((e) => {
                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: this.$t('error.messages.name'),
                                    text: this.$t('error.messages.connection_error') + e,
                                })
                            })
                        }
                    } else {
                        // console.log('Bloqueado accion');
                    }

                }
            },
            inverseAllServices: function () {
                if (this.loading === false) {
                    if (this.validatePeriod() != false) {
                        this.searchMarkup()

                        this.loading = true
                        if (this.services_selected.length > 0) {
                            for (let i = 0; i < this.services_selected.length; i++) {

                                this.services.push(this.services_selected[i])
                            }
                            this.services_selected = []
                            API({
                                method: 'post',
                                url: 'client_services/inverse/all',
                                data: {
                                    client_id: this.$route.params.client_id,
                                    period: this.selectPeriod,
                                },
                            }).then((result) => {
                                if (result.data.success === true) {
                                    this.getServices()
                                    this.clientRates = []
                                    this.clientRatesSelected = []
                                    this.markup = ''
                                    this.loading = false
                                }
                            }).catch((e) => {
                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: this.$t('error.messages.name'),
                                    text: this.$t('error.messages.connection_error') + e,
                                })
                            })
                        }
                    }
                } else {
                    // console.log('Bloqueado accion');
                }
            },
            calculateNumPages: function (num_services, limit) {
                this.num_pages = Math.ceil(num_services / limit)
            },
            calculateNumPagesServicesSelected: function (num_services, limit) {
                this.num_pages_services_selected = Math.ceil(num_services / limit)
            },
            getScrollTop: function () {
                if (!this.$refs.servicesList) return;

                let scroll = this.$refs.servicesList.scrollTop;

                if (!scroll) {
                    // console.error('Elemento list_services_selected no encontrado');
                    return;
                }

                if (scroll > this.scroll_limit) {
                    this.page += 1
                    this.scroll_limit = 2900 * this.page
                    if (this.page === this.num_pages) {
                        clearInterval(this.interval)
                        this.getServicesScroll()
                    } else {

                        this.getServicesScroll()
                    }

                }
            },
            getScrollTopServicesSelected: function () {
                if (!this.$refs.servicesListSelected) return;

                let scroll = this.$refs.servicesListSelected.scrollTop;

                if (!scroll) {
                    // console.error('Elemento list_services_selected no encontrado');
                    return;
                }

                if (scroll > this.scroll_limit_services_selected) {
                    this.page_services_selected += 1
                    this.scroll_limit_services_selected = 2900 * this.page_services_selected
                    if (this.page_services_selected === this.num_pages_services_selected) {
                        clearInterval(this.interval_services_selected)
                        this.getServicesScrollSelected()
                    } else {

                        this.getServicesScrollSelected()
                    }

                }
            },
            getPeriods: function () {
                var currentTime = new Date()
                var year = currentTime.getFullYear()

                API.get('client_services/selectPeriod?lang=' + localStorage.getItem('lang') + '&client_id=' +
                    this.$route.params.client_id+'&region_id='+this.$route.params.region_id)
                .then((result) => {
                    this.periods = result.data.data
                    if (result.data.data.length > 0) {
                        for (var i = 0; i < result.data.data.length; i++) {
                            if (result.data.data[i].text == year) {
                                this.selectPeriod = result.data.data[i].text;
                                this.porcentage = result.data.data[i].porcen_service;
                            }
                        }
                    }
                    // this.selectPeriod = !result.data.data.length ? '' : result.data.data[0].text;
                    if(this.porcentage == ''){
                        this.porcentage = !result.data.data.length ? '' : result.data.data[0].porcen_service
                    }
                    this.getServices()
                    this.getServicesSelected()
                }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('error.messages.name'),
                        text: this.$t('error.messages.connection_error'),
                    })
                })
            },
            removeRatePlan: function (id, data) {
                if (id != null) {
                    API({
                        method: 'post',
                        url: 'service_client_rate_plans/delete',
                        data: {
                            id: id,
                        },
                    }).then((result) => {
                        if (result.data.success === true) {
                            this.getClientRatePlans(data.service_rate.service_id)
                            this.getClientRatePlanSelected(data.service_rate.service_id)
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('modules.contacts'),
                                text: this.$t('error.messages.contact_delete'),
                            })
                            this.loading = false
                        }
                    }).catch((e) => {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('error.messages.name'),
                            text: this.$t('error.messages.connection_error') + e,
                        })
                    })
                }
            },
            getClientRatePlans: function (service_id) {
                API({
                    method: 'post',
                    url: 'service_client_rate_plans',
                    data: {
                        client_id: this.$route.params.client_id,
                        period: this.selectPeriod,
                        service_id: service_id,
                    },
                }).then((result) => {
                    this.rates_selected = result.data.data
                    // console.log(this.rates_selected);
                }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('error.messages.name'),
                        text: this.$t('error.messages.connection_error'),
                    })
                })
            },
            getClientRatePlanSelected: function (service_id) {
                API({
                    method: 'post',
                    url: 'service_client_rate_plans/selected',
                    data: {
                        client_id: this.$route.params.client_id,
                        period: this.selectPeriod,
                        service_id: service_id,
                    },
                }).then((result) => {
                    // this.clientRatesSelected = result.data.data
                    this.rates = result.data.data

                }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('error.messages.name'),
                        text: this.$t('error.messages.connection_error'),
                    })
                })
            },
            getServices: function () {
                API({
                    method: 'post',
                    url: 'service/search/client',
                    data: {
                        page: 1,
                        limit: this.limit,
                        query: this.query,
                        client_id: this.$route.params.client_id,
                        period: this.selectPeriod,
                        region_id: this.$route.params.region_id
                    },
                }).then((result) => {
                    this.services = result.data.data
                    this.count = result.data.count
                    this.calculateNumPages(result.data.count, this.limit)
                    this.scroll_limit = 2900
                    document.getElementById('list_services').scrollTop = 0

                }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('error.messages.name'),
                        text: this.$t('error.messages.connection_error'),
                    })
                })

            },
            getServicesScroll: function () {

                API({
                    method: 'post',
                    url: 'service/search/client',
                    data: {
                        page: this.page,
                        limit: this.limit,
                        query: this.query,
                        client_id: this.$route.params.client_id,
                        period: this.selectPeriod,
                        region_id: this.$route.params.region_id
                    },
                }).then((result) => {
                    let services = result.data.data
                    for (let i = 0; i < services.length; i++) {
                        this.services.push(services[i])
                    }
                    if (this.page === 1) {
                        this.count = result.data.count
                        this.calculateNumPages(result.data.count, this.limit)
                    }
                }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('error.messages.name'),
                        text: this.$t('error.messages.connection_error'),
                    })
                })

            },
            getServicesSelected: function () {
                API({
                    method: 'post',
                    url: 'client_services',
                    data: {
                        page: 1,
                        limit: this.limit_services_selected,
                        query: this.query_services_selected,
                        client_id: this.$route.params.client_id,
                        period: this.selectPeriod,
                        region_id: this.$route.params.region_id
                    },
                }).then((result) => {
                    this.services_selected = result.data.data

                    this.count_services_selected = result.data.count
                    this.calculateNumPagesServicesSelected(result.data.count, this.limit_services_selected)
                    this.scroll_limit_services_selected = 2900
                    document.getElementById('list_services_selected').scrollTop = 0

                }).catch((e) => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('error.messages.name'),
                        text: this.$t('error.messages.connection_error') + e,
                    })
                })
            },
            getServicesScrollSelected: function () {
                API({
                    method: 'post',
                    url: 'client_services',
                    data: {
                        page: this.page_services_selected,
                        limit: this.limit_services_selected,
                        query: this.query_services_selected,
                        client_id: this.$route.params.client_id,
                        period: this.selectPeriod,
                        region_id: this.$route.params.region_id
                    },
                }).then((result) => {
                    let services_selected = result.data.data
                    for (let i = 0; i < services_selected.length; i++) {
                        this.services_selected.push(services_selected[i])
                    }
                    if (this.page === 1) {
                        this.count = result.data.count
                        this.calculateNumPagesServicesSelected(result.data.count, this.limit)
                    }
                }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('error.messages.name'),
                        text: this.$t('error.messages.connection_error'),
                    })
                })
            },
            setPropertySelectedInRates: function () {
                for (let i = 0; i < this.rates.length; i++) {
                    this.$set(this.rates[i], 'selected', false)
                }
            },
            selectRate: function (service, index) {
                if (this.rates[index].selected) {
                    this.$set(this.rates[index], 'selected', false)
                } else {
                    this.setPropertySelectedInRates()
                    this.$set(this.rates[index], 'selected', true)
                }
            },
            moveOneRate: function () {
                if (this.loading === false) {
                    if (this.validatePeriod() !== false) {
                        this.searchMarkup()
                        this.loading = true
                        let search_rate = this.searchSelectRate()
                        if (search_rate !== -1) {
                            API({
                                method: 'post',
                                url: 'service_client_rate_plans/store',
                                data: {
                                    service_rate_id: this.rates[search_rate].id,
                                    client_id: this.$route.params.client_id,
                                    period: this.selectPeriod,
                                    porcentage: this.porcentage,
                                    region_id: this.$route.params.region_id
                                },
                            }).then((result) => {
                                if (result.data.success === true) {
                                    this.$set(this.rates[search_rate], 'selected', false)
                                    this.rates[search_rate].service_client_rate_plans_id = result.data.service_client_rate_plans_id
                                    this.rates[search_rate].markup = this.porcentage
                                    this.rates_selected.push(this.rates[search_rate])
                                    this.rates.splice(search_rate, 1)
                                    // console.log(this.rates);
                                    this.loading = false
                                }
                            }).catch(() => {
                                this.showError(
                                    this.$t('clientsmanageclienthotel.error.messages.name'),
                                    this.$t('clientsmanageclienthotel.error.messages.connection_error'),
                                )
                            })
                        } else {
                            if (this.rates.length > 0) {
                                this.loading = true
                                let element = this.rates.shift()
                                API({
                                    method: 'post',
                                    url: 'service_client_rate_plans/store',
                                    data: {
                                        data: element,
                                        rate_plan_id: element.id,
                                        client_id: this.$route.params.client_id,
                                        period: this.selectPeriod,
                                        porcentage: this.porcentage,
                                        region_id: this.$route.params.region_id
                                    },
                                }).then((result) => {
                                    if (result.data.success === true) {
                                        element.service_client_rate_plans_id = result.data.service_client_rate_plans_id
                                        this.rates_selected.push(element)
                                        this.loading = false
                                    }
                                }).catch((e) => {
                                    this.showError(
                                        this.$t('clientsmanageclienthotel.error.messages.name'),
                                        this.$t('clientsmanageclienthotel.error.messages.connection_error') + e,
                                    )
                                })
                            }
                        }

                    }
                } else {
                    // console.log('Bloqueado accion');
                }
            },
            moveAllRates: function () {
                if (this.loading === false) {
                    if (this.validatePeriod() !== false) {
                        this.searchMarkup()
                        this.loading = true
                        if (this.rates.length > 0) {
                            for (let i = 0; i < this.rates.length; i++) {
                                this.$set(this.rates[i], 'selected', false)
                                this.rates_selected.push(this.rates[i])
                            }
                            this.rates = []

                            let search_service = this.searchSelectService()
                            if (search_service !== -1) {
                                API({
                                    method: 'post',
                                    url: 'service_client_rate_plans/store/all',
                                    data: {
                                        client_id: this.$route.params.client_id,
                                        service_id: this.services[search_service].service_id,
                                        period: this.selectPeriod,
                                        porcentage: this.porcentage,
                                        region_id: this.$route.params.region_id
                                    },
                                }).then((result) => {
                                    if (result.data.success === true) {
                                        //his.getRatesSelected()
                                        this.markup = ''
                                        this.loading = false
                                    }
                                }).catch((e) => {
                                    this.showError(
                                        this.$t('clientsmanageclienthotel.error.messages.name'),
                                        this.$t('clientsmanageclienthotel.error.messages.connection_error') + e,
                                    )
                                })
                            }
                        }
                    } else {
                        // console.log('Bloqueado accion');
                    }

                }
            },
            inverseOneRate: function () {
                if (this.loading === false) {
                    if (this.validatePeriod() !== false) {
                        this.searchMarkup()

                        this.loading = true
                        let search_service = this.searchSelectService()
                        let search_rate = this.searchSelectRateRatesSelected()
                        // console.log(this.rates_selected);
                        if (search_rate !== -1) {
                            API({
                                method: 'post',
                                url: 'service_client_rate_plans/inverse',
                                data: {
                                    rate_plan_id: this.rates_selected[search_rate].id,
                                    client_id: this.$route.params.client_id,
                                    period: this.selectPeriod,
                                    region_id: this.$route.params.region_id
                                },
                            }).then((result) => {
                                if (result.data.success === true) {
                                    let service = this.services[search_service]
                                    this.getClientRatePlans(service.service_id)
                                    this.getClientRatePlanSelected(service.service_id)
                                    this.loading = false
                                }
                            }).catch(() => {
                                this.showError(
                                    this.$t('clientsmanageclienthotel.error.messages.name'),
                                    this.$t('clientsmanageclienthotel.error.messages.connection_error'),
                                )
                            })
                        } else {
                            if (this.services_selected.length > 0) {
                                this.loading = true
                                let element = this.services_selected.shift()
                                API({
                                    method: 'post',
                                    url: 'client_services/inverse',
                                    data: {
                                        data: element,
                                        period: this.selectPeriod,
                                        region_id: this.$route.params.region_id
                                    },
                                }).then((result) => {
                                    if (result.data.success === true) {
                                        this.services.push(element)
                                        this.markup = ''
                                        this.loading = false
                                    }
                                }).catch((e) => {
                                    this.showError(
                                        this.$t('clientsmanageclienthotel.error.messages.name'),
                                        this.$t('clientsmanageclienthotel.error.messages.connection_error') + e,
                                    )
                                })
                            }
                        }
                    }
                } else {
                    // console.log('Bloqueado accion');
                }
            },
            inverseAllRates: function () {
                if (this.loading === false) {
                    if (this.validatePeriod() !== false) {
                        this.searchMarkup()
                        this.loading = true
                        if (this.rates_selected.length > 0) {
                            this.rates_selected = []
                            let search_service = this.searchSelectService()
                            let service = this.services[search_service]
                            API({
                                method: 'post',
                                url: 'service_client_rate_plans/inverse/all',
                                data: {
                                    client_id: this.$route.params.client_id,
                                    service_id: service.service_id,
                                    period: this.selectPeriod,
                                    region_id: this.$route.params.region_id
                                },
                            }).then((result) => {
                                if (result.data.success === true) {
                                    this.getClientRatePlanSelected(service.service_id)
                                    this.loading = false
                                }
                            }).catch((e) => {
                                this.showError(
                                    this.$t('clientsmanageclienthotel.error.messages.name'),
                                    this.$t('clientsmanageclienthotel.error.messages.connection_error') + e,
                                )
                            })
                        }
                    }
                } else {
                    // console.log('Bloqueado accion');
                }
            },
            searchSelectRate: function () {
                for (let i = 0; i < this.rates.length; i++) {
                    if (this.rates[i].selected) {
                        return i
                        break
                    }
                }
                return -1
            },
            validateMarkupRate: function () {
                if (this.markup_rate == '') {
                    this.showError(
                        this.$t('clientsmanageclienthotel.title'),
                        this.$t('clientsmanageclienthotel.error.messages.add_markup'),
                    )
                    return false
                }
            },
            updateOneRate: function () {
                if (this.loading === false) {
                    if (this.validateMarkupRate() !== false) {
                        this.loading = true
                        let search_service = this.searchSelectService()
                        let search_rate = this.searchSelectRate()
                        if (search_rate !== -1) {
                            API({
                                method: 'put',
                                url: 'service_client_rate_plans/update',
                                data: {
                                    rate_plan_id: this.rates[search_rate].id,
                                    client_id: this.$route.params.client_id,
                                    period: this.selectPeriod,
                                    markup: this.markup_rate,
                                    region_id: this.$route.params.region_id
                                },
                            }).then((result) => {
                                if (result.data.success === true) {
                                    let service = this.services[search_service]
                                    this.getClientRatePlans(service.service_id)
                                    this.getClientRatePlanSelected(service.service_id)
                                    this.markup_rate = ''
                                    this.loading = false
                                }
                            }).catch(() => {
                                this.showError(
                                    this.$t('clientsmanageclienthotel.error.messages.name'),
                                    this.$t('clientsmanageclienthotel.error.messages.connection_error'),
                                )
                                this.loading = false
                            })
                        } else {
                            this.loading = false
                        }
                    }
                } else {
                    // console.log('Bloqueado accion');
                }
            },
            searchSelectRateRatesSelected: function () {
                for (let i = 0; i < this.rates_selected.length; i++) {
                    if (this.rates_selected[i].selected) {
                        return i
                        break
                    }
                }
                return -1
            },
            setPropertySelectedInRatesSelected: function () {
                for (let i = 0; i < this.rates_selected.length; i++) {
                    this.$set(this.rates_selected[i], 'selected', false)
                }
            },
            selectRateRatesSelected: function (hotel, index) {
                if (this.rates_selected[index].selected) {
                    this.$set(this.rates_selected[index], 'selected', false)
                } else {
                    this.setPropertySelectedInRatesSelected()
                    this.$set(this.rates_selected[index], 'selected', true)
                }

            },
            beforeDestroy () {
                clearInterval(this.interval)
                clearInterval(this.interval_services_selected)
            },
            loadubigeo () {
                this.loading = true
                this.ubigeos = []
                APISERVICE.get('destination_services/?client_id=' + this.$route.params.client_id + '&with_country=1' + '&region_id=' + this.$route.params.region_id)
                .then((result) => {
                    let ubigeohotel = result.data.data.destinations
                    this.ubigeos = [];
                    ubigeohotel.forEach((ubigeofor) => {
                        this.ubigeos.push({ name: ubigeofor.label, code: ubigeofor.code })
                    })
                    this.loading = false
                }).catch((e) => {
                    this.loading = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('clients.title'),
                        text: this.$t('global.error.messages.connection_error'),
                    })
                })
            },
            addUbigeos (newTag) {
                const tag = {
                    name: newTag,
                    code: newTag.substring(0, 2) + Math.floor((Math.random() * 10000000))
                }
                this.ubigeoSelected.push(tag)
            },
            saveFilter () {
                if (this.ubigeoSelected.length === 0) {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Cliente - Servicios',
                        text: 'Debe seleccionar al menos una ciudad',
                    })
                } else {
                    this.loading = true
                    API({
                        method: 'post',
                        url: 'client_services/store/service/filter',
                        data: {
                            client_id: this.$route.params.client_id,
                            filter: this.ubigeoSelected,
                            period: this.selectPeriod,
                            region_id: this.$route.params.region_id
                        },
                    }).then((result) => {
                        this.loading = false
                        if (result.data.success === true) {
                            this.loadubigeo()
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('clientsmanageclienthotel.error.messages.name'),
                                text: result.data.message,
                            })
                        }
                    }).catch((e) => {
                        this.loading = false
                        this.showError(
                            this.$t('clientsmanageclienthotel.error.messages.name'),
                            this.$t('clientsmanageclienthotel.error.messages.connection_error') + e,
                        )
                    })
                }

            },
            inverseService (service, index) {
                this.selectServiceServicesSelected(service, index)
                this.inverseOneRate()
            },
            blockService (service, index) {
                this.selectService(service, index)
                this.moveOneService()
            }

        },
    }
</script>

<style>
    body {
        -webkit-touch-callout: none;
        -webkit-user-select: none;
        -khtml-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }

    .style_list_ul {
        height: 160px;
        max-height: 160px;
        overflow-y: scroll;
        list-style-type: none;
        padding: 0px;
        margin-left: -1px;
        border-left: 1px solid #ccc;
        border-bottom: 1px solid #ccc;
    }

    .selected {
        background-color: #005ba5;
        color: white;
    }

    .style_list_li {
        border-bottom: 1px solid #ccc;
        padding: 5px 5px 5px 5px;
        cursor: move;
    }

    .style_span_li {
        margin-left: 5px;
    }

    #search_services:focus {
        box-shadow: none;
        border-color: #ccc;
    }

    #search_services {
        border-top: 1px solid #ccc;
        border-right: 1px solid #ccc;
        border-bottom: 1px solid #ccc;
        border-bottom-right-radius: 0px;
        border-top-right-radius: 0.2rem;
    }

    .button_icon {
        background-color: #f0f3f5 !important;
        border-top-left-radius: 0.2rem;
        color: #000;
        cursor: default !important;
    }

    .button_icon:hover {
        box-shadow: none;
        background-color: #f0f3f5 !important;
    }

    .button_icon:focus {
        box-shadow: none;
        background-color: #f0f3f5 !important;
    }

    .button_icon:active {
        box-shadow: none;
        background-color: #f0f3f5 !important;
    }

    .mover_controls {
        padding: 10px;
        margin-bottom: 10px;
    }

    .btn-group-xs > .btn, .btn-xs {
        padding: .25rem .4rem;
        font-size: 9px;
        line-height: .5;
        border-radius: .2rem;
    }
</style>

<i18n src="./services.json"></i18n>
