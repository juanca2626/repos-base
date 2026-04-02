<template>
    <div class="vld-parent">
        <loading :active.sync="loading" :can-cancel="false" color="#BD0D12"></loading>
        <div class="container mt-2 ml-3">
        <div class="row">
            <div class="col-md-4">
                <p class="font-weight-bold">Lista de hoteles</p>
                <input class="form-control mb-1"
                       id="search" name="search"
                       placeholder="Buscar hotel"
                       type="text" v-model="search">
                <hr class="mb-2">
                <b-input-group class="mb-3" v-if="!add_rate">
                    <b-form-input v-model="form.discount" placeholder="Descuento %"
                                  id="discount" name="discount" v-validate="'required|decimal:2'"></b-form-input>
                    <b-input-group-append>
                        <b-button variant="success" @click="validateBeforeSubmit">Agregar</b-button>
                    </b-input-group-append>
                </b-input-group>
                <b-button-group v-if="add_rate" class="mb-3">
                    <b-button variant="success" @click="addRate">Agregar</b-button>
                    <b-button variant="danger" @click="cancelAddRate">Cancelar</b-button>
                </b-button-group>
                <b-alert show variant="info" v-if="add_rate">
                    Seleccione las tarifas que desea agregar
                </b-alert>

                <p class="bg-danger" style="margin-top: 3px;border-radius: 2px;" v-if="!add_rate">
                    <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                       style="margin-left: 5px;" v-show="errors.has('discount')"/>
                    <span v-show="errors.has('discount')">{{ errors.first('discount') }}</span>
                </p>
                <b-card no-body class="mb-1" v-for="(hotel,index_hotel) in filterByTerm" :key="index_hotel">
                    <b-card-header header-tag="header" class="p-1" role="tab">
                        <b-button block v-b-toggle="'accordion-'+index_hotel" variant="default"
                                  class="d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold">{{hotel.name}}</span>
                            <b-badge variant="primary" pill>
                                {{hotel.count_selected}}
                            </b-badge>
                        </b-button>
                    </b-card-header>
                    <b-collapse :id="'accordion-'+index_hotel" visible accordion="my-accordion" role="tabpanel">
                        <b-card-body>
                            <b-list-group>
                                <b-list-group-item
                                    v-for="rate_plan in hotel.rates_plans"
                                    :key="rate_plan.id"
                                    class="d-flex justify-content-between align-items-center" button
                                    @click="selectRatePlan(hotel,rate_plan)">
                                    {{rate_plan.name}}
                                    <b-badge variant="none" class="font-xl" pill>
                                        <i class="far fa-square" v-if="!rate_plan.check"></i>
                                        <i class="far fa-check-square" v-else></i>
                                    </b-badge>
                                </b-list-group-item>
                            </b-list-group>
                        </b-card-body>
                    </b-collapse>
                </b-card>
            </div>
            <div class="col-md-8">
                <p class="font-weight-bold">Lista de Multi-Propiedades</p>
                <b-card-group deck v-for="multi in multiProperties" :key="multi.id" class="mb-3" v-if="multi.view">
                    <b-card no-body>
                        <b-card-header header-tag="header" class="d-flex justify-content-between align-items-center p-2"
                                       role="tab">
                            Descuento: {{multi.discount}}%
                            <b-button-group pill v-if="!add_rate">
                                <b-button variant="success" @click="willAddRate(multi)">Agregar</b-button>
                                <b-button variant="danger" @click="remove(multi.id)">Eliminar</b-button>
                            </b-button-group>
                        </b-card-header>
                        <b-list-group v-for="(hotel,index_hotel_property) in multi.property_hotels" :key="index_hotel_property">
                            <b-list-group-item href="#">
                                <div class="font-weight-bold mb-3">
                                    {{hotel.hotel}}
                                    <b-button variant="danger" size="sm" pill @click="removeHotelAll(hotel.rates)"
                                              v-if="!add_rate" style="font-size: 8px !important;">
                                        <i class="fas fa-trash-alt"></i> Eliminar
                                    </b-button>
                                </div>
                                <b-list-group-item v-for="rate in hotel.rates" :key="rate.id"
                                                   class="d-flex justify-content-between align-items-center p-2">
                                    {{rate.rate.name}}
                                    <b-button variant="danger" size="sm" pill @click="removeHotelRate(rate)"
                                              v-if="!add_rate" style="font-size: 8px !important;">
                                        <i class="fas fa-trash-alt"></i> Eliminar
                                    </b-button>
                                </b-list-group-item>
                            </b-list-group-item>
                        </b-list-group>
                    </b-card>
                </b-card-group>
            </div>
        </div>
    </div>
    </div>
</template>

<script>
    import { API } from '../../../api'
    import VueBootstrapTypeahead from 'vue-bootstrap-typeahead'
    import { Switch as cSwitch } from '@coreui/vue'
    import TableClient from '../../../components/TableClient'
    import MenuEdit from '../../../components/MenuEdit'
    import Loading from 'vue-loading-overlay'
    import 'vue-loading-overlay/dist/vue-loading.css'
    export default {
        components: {
            'table-client': TableClient,
            'menu-edit': MenuEdit,
            VueBootstrapTypeahead,
            cSwitch,
            Loading
        },
        data: () => {
            return {
                languages: [],
                multiProperties: [],
                loading: false,
                add_rate: false,
                search: '',
                multi_property_id: '',
                form: {
                    rates: [],
                    discount: '',
                },
                quantities: [],
                hotels: [],
                table: {
                    columns: ['quantity', 'discount', 'delete']
                },
            }
        },
        computed: {
            filterByTerm () {
                return this.hotels.filter(hotel => {
                    return hotel.name.toLowerCase().includes(this.search)
                })
            }
        },
        mounted: function () {
            this.fetchData(this.$i18n.locale)
        },
        methods: {
            getSelectedRatesPlan () {
                let rates_plans = []
                this.hotels.forEach(function (hotel) {
                    if (hotel.count_selected > 0) {
                        hotel.rates_plans.forEach(function (rate) {
                            if (rate.check) {
                                rates_plans.push(rate.id)
                            }
                        })

                    }
                })

                return rates_plans
            },
            validateBeforeSubmit () {
                this.form.rates = this.getSelectedRatesPlan()
                if (this.form.rates.length === 0) {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Multi Propiedades',
                        text: 'Debe seleccionar al menos una tarifa de un hotel'
                    })
                    return false
                }
                this.$validator.validateAll().then((result) => {
                    if (result) {
                        this.submit()
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Multi Propiedades',
                            text: 'Debe completar la información del formulario'
                        })
                        this.loading = false
                    }
                })
            },
            fetchData: function (lang) {
                if (this.$route.params.id != null) {
                    API.get('/multiple_property/chains?lang=' + localStorage.getItem('lang') + '&id=' + this.$route.params.id)
                        .then((result) => {
                            let multiProperties = result.data.data
                            multiProperties.forEach(function (rate) {
                                rate.view = true
                            })
                            this.multiProperties = multiProperties
                        }).catch(() => {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Multi Propiedades',
                            text: this.$t('chains.error.messages.connection_error')
                        })
                    })

                    //select hotels
                    API.get('/chain/' + this.$route.params.id + '/hotels?lang=' + localStorage.getItem('lang'))
                        .then((result) => {
                            let hotels = result.data.data
                            hotels.forEach(function (hotel) {
                                hotel.count_selected = 0
                                hotel.rates_plans.forEach(function (rate) {
                                    rate.check = false
                                })
                            })
                            this.hotels = hotels
                            console.log(hotels)
                        }).catch(() => {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Multi Propiedades',
                            text: this.$t('chains.error.messages.connection_error')
                        })
                    })
                }
            },
            submit () {
                this.loading = true
                API({
                    method: 'post',
                    url: 'multiple_property/chains',
                    data: {
                        discount: this.form.discount,
                        rates: this.form.rates,
                        chain_id: this.$route.params.id
                    }
                })
                    .then((result) => {
                        if (result.data.success === false) {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Multi Propiedades',
                                text: this.$t('chains.error.messages.contact_incorrect')
                            })
                            this.loading = false
                        } else {
                            this.loading = false
                            this.fetchData(this.$i18n.locale)
                        }
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Multi Propiedades',
                        text: this.$t('chains.error.messages.connection_error')
                    })
                })
            },
            remove: function (id) {
                API({
                    method: 'DELETE',
                    url: 'multiple_property/chains/' + (id)
                })
                    .then((result) => {
                        console.log(result)
                        if (result.data.success === true) {
                            this.fetchData(this.$i18n.locale)
                            this.loading = false
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Multi Propiedades',
                                text: this.$t('chains.error.messages.chain_delete')
                            })

                            this.loading = false
                        }
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Multi Propiedades',
                        text: this.$t('chains.error.messages.connection_error')
                    })
                })
            },
            selectRatePlan: function (hotel, rate_plan) {
                if (rate_plan.check) {
                    rate_plan.check = false
                } else {
                    rate_plan.check = true
                }
                let count = 0
                hotel.rates_plans.forEach(function (rate) {
                    if (rate.check) {
                        count++
                    }
                })
                hotel.count_selected = count
            },
            willAddRate: function (multi) {
                this.multi_property_id = multi.id
                this.add_rate = true
                this.multiProperties.forEach(function (rate) {
                    rate.view = false
                })
                multi.view = true
            },
            cancelAddRate: function () {
                this.add_rate = false
                this.form.rates = []
                this.multi_property_id = ''
                this.multiProperties.forEach(function (rate) {
                    rate.view = true
                })
            },
            resetSelectedRates: function () {
                this.hotels.forEach(function (hotel) {
                    hotel.count_selected = 0
                    hotel.rates_plans.forEach(function (rate) {
                        rate.check = false
                    })
                })
            },
            removeHotelAll: function (hotel) {
                let rates = []
                hotel.forEach(function (rate) {
                    rates.push(rate.id)
                })
                this.loading = true
                API({
                    method: 'DELETE',
                    url: 'multiple_property/chains/property/rates',
                    data: {
                        rates: rates,
                    }
                })
                    .then((result) => {
                        if (result.data.success === false) {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Multi Propiedades',
                                text: this.$t('chains.error.messages.contact_incorrect')
                            })
                            this.loading = false
                        } else {
                            this.loading = false
                            this.fetchData(this.$i18n.locale)
                        }
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Multi Propiedades',
                        text: this.$t('chains.error.messages.connection_error')
                    })
                })
            },
            removeHotelRate: function (rate) {
                this.loading = true
                API({
                    method: 'DELETE',
                    url: 'multiple_property/chains/property/rate/' + rate.id
                })
                    .then((result) => {
                        this.loading = false
                        if (result.data.success === true) {
                            this.fetchData(this.$i18n.locale)
                            this.loading = false
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Multi Propiedades',
                                text: this.$t('chains.error.messages.chain_delete')
                            })

                            this.loading = false
                        }
                    }).catch(() => {
                    this.loading = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Multi Propiedades',
                        text: this.$t('chains.error.messages.connection_error')
                    })
                })
            },
            addRate: function () {
                this.form.rates = this.getSelectedRatesPlan()
                console.log(this.form.rates)
                if (this.form.rates.length === 0) {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Multi Propiedades',
                        text: 'Debe seleccionar al menos una tarifa de un hotel'
                    })
                    return false
                } else {
                    this.loading = true
                    API({
                        method: 'post',
                        url: 'multiple_property/chains/add/property',
                        data: {
                            multi_property_id: this.multi_property_id,
                            rates: this.form.rates,
                        }
                    })
                        .then((result) => {
                            if (result.data.success === false) {
                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: 'Multi Propiedades',
                                    text: result.data.error
                                })
                                this.loading = false
                            } else {
                                this.loading = false
                                this.search = ''
                                this.cancelAddRate()
                                this.resetSelectedRates()
                                this.fetchData(this.$i18n.locale)
                            }
                        }).catch(() => {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Multi Propiedades',
                            text: this.$t('chains.error.messages.connection_error')
                        })
                    })

                }
            },
        }
    }
</script>

<style lang="stylus">
    .s-color {
        color: red;
    }
</style>
