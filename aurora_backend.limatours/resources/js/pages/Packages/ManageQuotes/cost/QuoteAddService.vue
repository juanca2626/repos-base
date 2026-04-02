<template>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <label v-if="!loading">Agregar a:</label>
            </div>
            <div class="col-md-12">
                <button v-for="(category,category_index) in categories_plan" @click="checkCategory(category_index)"
                        class="btn btn-danger" type="submit" v-if="!loading"
                        style="float: left; margin-right: 5px;">
                    <font-awesome-icon v-if="category.check" :icon="['fas', 'check-square']"/>
                    <font-awesome-icon v-if="!category.check" :icon="['fas', 'square']"/>
                    {{category.name}}
                </button>
            </div>
        </div>
        <div class="b-form-group form-group bottom0">
            <div class="form-row">
                <div class="col-sm-3">
                    <label class="col-form-label">Destino</label>
                    <v-select :options="ubigeos"
                              @input="ubigeoChange"
                              :value="this.ubigeo_id"
                              v-model="ubigeoSelected"
                              :placeholder="this.$t('hotels.search.messages.hotel_ubigeo_search')"
                              autocomplete="true"></v-select>
                </div>
                <div class="col-2">
                    <div class="col-sm-2"><label class="col-form-label">Fecha</label></div>
                    <div class="input-group col-12">
                        <date-picker
                            :config="datePickerFromOptions"
                            id="date_from"
                            autocomplete="off"
                            name="date_from"
                            v-model="date_from" v-validate="'required'">
                        </date-picker>

                        <div class="input-group-append">
                            <button class="btn btn-secondary datepickerbutton" title="Toggle"
                                    type="button">
                                <i class="far fa-calendar"></i>
                            </button>
                        </div>
                    </div>
                    <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                        <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                           style="margin-left: 5px;"
                                           v-show="errors.has('date_from')"/>
                        <span v-show="errors.has('date_from')">{{ errors.first('date_from') }}</span>
                    </div>
                </div>
                <div class="col-sm-3">
                    <label class="col-form-label">{{ $t('services.category') }}</label>
                    <v-select :options="typeServices"
                              :value="type_service_id"
                              @input="typeServiceChange"
                              autocomplete="true"
                              data-vv-as="type service"
                              data-vv-name="type_service"
                              name="type_service"
                              v-model="typeServiceSelected"
                              :placeholder="this.$t('services.typeService')">
                    </v-select>
                </div>
                <div class="col-sm-4">
                    <label class="col-form-label">{{ $t('services.typeService') }}</label>
                    <v-select :options="categories"
                              :value="category_service_id"
                              @input="serviceCategoryChange"
                              autocomplete="true"
                              data-vv-as="category"
                              data-vv-name="category"
                              v-model="serviceCategorySelected"
                              :placeholder="this.$t('services.category')">
                    </v-select>
                </div>
            </div>
            <div class="form-row">
                <div class="col-sm-5">
                    <label class="col-form-label">Busqueda por nombre de servicio</label>
                    <input class="form-control" id="service_name" name="service_name" placeholder="Nombre de servicio"
                           type="text" v-model="service_name">
                </div>
                <div class="col-md-1">
                    <img src="/images/loading.svg" v-if="loading" width="40px"
                         style="float: right; margin-top: 35px;"/>
                    <button @click="search" class="btn btn-success" type="submit" v-if="!loading"
                            style="float: right; margin-top: 35px;">
                        <font-awesome-icon :icon="['fas', 'search']"/>
                        Buscar
                    </button>
                </div>
            </div>
            <div class="mt-4">
                <div class="vld-parent">
                    <loading :active.sync="loading" :can-cancel="false" color="#BD0D12"></loading>
                    <table-server :columns="table.columns" :options="tableOptions" :url="urlServices"
                                  @loaded="loading"
                                  class="text-center" ref="table">
                        <div class="table-service_ubigeo" slot="destinations" slot-scope="props"
                             style="font-size: 0.9em;padding:12px">
                            {{props.row.service_origin[0].state.translations[0].value}} -
                            {{props.row.service_destination[0].state.translations[0].value}}
                        </div>
                        <div class="table-name" slot="name" slot-scope="props" style="font-size: 0.9em">
                            {{ props.row.name }}
                        </div>
                        <div class="table-aurora_code" slot="aurora_code" slot-scope="props" style="font-size: 0.9em">
                            {{ props.row.aurora_code }}
                        </div>
                        <div class="table-name" slot="type" slot-scope="props" style="font-size: 0.9em">
                            {{ props.row.service_type.translations[0].value }}
                        </div>
                        <div class="table-name" slot="category" slot-scope="props" style="font-size: 0.9em">
                            {{ props.row.service_sub_category.service_categories.translations[0].value }}
                        </div>
                        <div class="table-category" slot="experiences" slot-scope="props"
                             style="font-size: 0.9em;padding: 10px">
                            <div v-for="exp in props.row.experience">
                                <span class="badge badge-primary bag-category mr-1">{{exp.translations[0].value}}</span><br>
                            </div>
                        </div>
                        <div class="table-category" slot="rates" slot-scope="props"
                             style="font-size: 0.9em;width: 240px">
                            <v-select :options="props.row.service_rate"
                                      track-by="id"
                                      label="name"
                                      :value="rateSelected"
                                      @input="addRate(props.index)"
                                      placeholder="Seleccione una tarifa"
                                      name="rate"
                                      autocomplete="true"
                                      :preselect-first="true"
                                      key="id"
                                      v-model="serviceByRates[props.index]">
                            </v-select>
                        </div>
                        <div class="table-actions" slot="actions" slot-scope="props"
                             style="padding: 5px;display: block;margin: auto;">
                            <button @click="addService(props.index,props.row)" class="btn btn-success btn-sm"
                                    v-if="!props.row.added"
                                    type="button">
                                <font-awesome-icon :icon="['fas', 'plus']"/>
                            </button>
                            <button @click="deleteService(props.row)" class="btn btn-danger btn-sm"
                                    v-if="props.row.added"
                                    type="button">
                                <font-awesome-icon :icon="['fas', 'minus']"/>
                            </button>
                        </div>
                    </table-server>
                </div>
            </div>

            <div class="col-12" style="padding-left: 0;">
                <button @click="back()" class="btn btn-success" type="button">
                    <font-awesome-icon :icon="['fas', 'angle-left']"
                                       style="margin-left: 5px;"/>
                    Atrás
                </button>
            </div>
        </div>

    </div>
</template>

<script>
    import { API } from '../../../../api'
    import TableServer from './../../../../components/TableServer'
    import { Switch as cSwitch } from '@coreui/vue'
    import BTab from 'bootstrap-vue/es/components/tabs/tab'
    import BInputNumber from 'bootstrap-vue/es/components/form-input/form-input'
    import BTabs from 'bootstrap-vue/es/components/tabs/tabs'
    import VueBootstrapTypeahead from 'vue-bootstrap-typeahead'
    import vSelect from 'vue-select'
    import 'vue-select/dist/vue-select.css'
    import datePicker from 'vue-bootstrap-datetimepicker'
    import 'pc-bootstrap4-datetimepicker/build/css/bootstrap-datetimepicker.css'
    import Multiselect from 'vue-multiselect'
    import Loading from 'vue-loading-overlay'

    export default {
        components: {
            'table-server': TableServer,
            BTabs,
            BTab,
            cSwitch,
            VueBootstrapTypeahead,
            BInputNumber,
            vSelect,
            datePicker,
            Multiselect,
            Loading
        },
        data: () => {
            return {
                loading: false,
                rateSelected: [],
                services: [],
                categories: [],
                typeServices: [],
                tmpubigeos: [],
                ubigeos: [],
                experiences: [],
                experiencesService: [],
                ubigeo: null,
                ubigeoSelected: [],
                typeServiceSelected: [],
                serviceCategorySelected: [],
                type_service_id: '',
                category_service_id: '',
                serviceByRates: [{
                    id: ''
                }],
                plan_rate_id: '',
                ubigeo_id: '',
                date_from: '',
                service_name: '',
                experiencesFormat: [],
                datePickerFromOptions: {
                    format: 'DD/MM/YYYY',
                    useCurrent: false,
                    locale: localStorage.getItem('lang')
                },
                table: {
                    columns: ['destinations', 'name', 'aurora_code', 'type', 'category', 'experiences', 'rates', 'actions'],
                },
                categories_plan: [],
                urlServices: '',
            }
        },
        computed: {
            tableOptions: function () {
                return {
                    headings: {
                        destinations: this.$i18n.t('services.service_origin') + ' - ' +
                            this.$i18n.t('services.service_destination'),
                        name: this.$i18n.t('services.service_name'),
                        aurora_code: this.$i18n.t('services.service_code_aurora'),
                        type: this.$i18n.t('services.service_type'),
                        category: this.$i18n.t('services.service_category'),
                        experiences: this.$i18n.t('services.experiences'),
                        rates: this.$i18n.t('services.rates'),
                        actions: this.$i18n.t('global.table.actions')
                    },
                    sortable: [],
                    filterable: [],
                    perPageValues: [10, 25, 50, 100],
                    perPage: 10,
                    responseAdapter ({ data }) {
                        data.data.forEach((item) => {
                            item.added = false
                            item.object_ids = ''
                        })
                        return {
                            data: data.data,
                            count: data.count
                        }
                    },
                    params: {
                        'experiences': this.experiencesFormat,
                        'destiny': this.ubigeo,
                        'service_category': this.type_service_id,
                        'service_type': this.category_service_id,
                        'service_name': this.service_name,
                        'date_from': this.convertDate(this.date_from, '/', '-'),
                    },
                    requestFunction: function (data) {
                        return API.post('services/search', data).catch(function (e) {
                            this.dispatch('error', e)
                        }.bind(this))
                    }
                }
            },
        },
        mounted: function () {
            this.$i18n.locale = localStorage.getItem('lang')
            //type services
            API.get('/service_categories/selectBox?lang=' + localStorage.getItem('lang'))
                .then((result) => {
                    let categorias = result.data.data
                    categorias.forEach((category) => {
                        this.typeServices.push({
                            label: category.translations[0].value,
                            code: category.translations[0].object_id
                        })
                    })

                }).catch(() => {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('global.modules.package'),
                    text: this.$t('global.error.messages.connection_error')
                })
            })

            //categorias
            API.get('/service_types/selectBox?lang=' + localStorage.getItem('lang'))
                .then((result) => {
                    let serviceTypes = result.data.data
                    serviceTypes.forEach((serviceTypes) => {
                        this.categories.push({
                            label: serviceTypes.code + ' - ' + serviceTypes.translations[0].value,
                            code: serviceTypes.translations[0].object_id
                        })
                    })

                }).catch(() => {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('global.modules.services'),
                    text: this.$t('global.error.messages.connection_error')
                })
            })
        },
        created () {
            this.$root.$on('plan_rates_categories', (payload) => {
                console.log(payload)
                this.categories_plan.push(payload)
            })
            this.plan_rate_id = this.$route.params.package_plan_rate_id
            this.category_id = this.$route.params.category_id
            this.loadubigeo()
            this.getCategories()
        },
        methods: {
            getCategories: function () {
                API({
                    method: 'get',
                    url: '/package/plan_rates/' + this.$route.params.package_plan_rate_id + '?lang=' + localStorage.getItem('lang')
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            let categories = result.data.data.plan_rate_categories
                            let arrayCatego = []
                            for (let i = 0; i < categories.length; i++) {
                                arrayCatego.push({
                                    'id': categories[i].id,
                                    'check': true,
                                    'name': categories[i].category.translations[0].value,
                                })
                            }
                            this.categories_plan = arrayCatego
                        }
                    }).catch((e) => {
                })
            },
            serviceCategoryChange: function (value) {
                this.serviceType = value
                if (this.serviceType != null) {
                    this.category_service_id = this.serviceType.code
                } else {
                    this.category_service_id = ''
                    this.serviceCategorySelected = []
                }
            },
            typeServiceChange: function (value) {
                this.category = value
                if (this.category != null) {
                    this.type_service_id = this.category.code
                } else {
                    this.type_service_id = ''
                }
            },
            addExperiences (newTag) {
                const tag = {
                    name: newTag,
                    code: newTag.substring(0, 2) + Math.floor((Math.random() * 10000000))
                }
                this.experiencesService.push(tag)
            },
            checkboxChecked: function (status) {
                return !!status
            },
            setDateFrom (e) {
                // this.$refs.datePickerTo.dp.minDate(e.date)
            },
            loadubigeo () {
                API.get('/services/ubigeo/selectbox/destination/' + localStorage.getItem('lang'))
                    .then((result) => {
                        let ubigeohotel = result.data.data
                        ubigeohotel.forEach((ubigeofor) => {
                            this.ubigeos.push({ label: ubigeofor.description, code: ubigeofor.id })
                        })
                    }).catch((e) => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('hotels.error.messages.name'),
                        text: this.$t('hotels.error.messages.connection_error')
                    })
                })
            },
            ubigeoChange: function (value) {
                this.ubigeo = value
                if (this.ubigeo != null) {
                    if (this.ubigeo_id != this.ubigeo.code) {
                    }
                    this.ubigeo_id = this.ubigeo.code
                } else {
                    this.ubigeo_id = ''
                }
            },
            getUnique (arr, comp) {
                //store the comparison  values in array
                const unique = arr.map(e => e[comp]).// store the keys of the unique objects
                    map((e, i, final) => final.indexOf(e) === i && i)
                    // eliminate the dead keys & return unique objects
                    .filter((e) => arr[e]).map(e => arr[e])
                return unique
            },
            convertDate: function (_date, charFrom, charTo) {
                if (_date) {
                    _date = _date.split(charFrom)
                    _date = _date[2] + charTo + _date[1] + charTo + _date[0]
                } else {
                    _date = ''
                }
                return _date
            },
            search () {
                this.loading = true
                let varExperiences = this.experiencesService
                varExperiences.forEach((experience) => {
                    this.experiencesFormat.push(experience.code)
                })
                if (this.service_name == '') {
                    if (!(this.ubigeo)) {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Paquetes - Cotizador',
                            text: 'Debe seleccionar una ciudad de destino'
                        })
                        this.loading = false
                        return false
                    }
                }
                if (this.date_from == '') {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Paquetes - Cotizador',
                        text: 'Debe ingresar una fecha de inicio'
                    })
                    this.loading = false
                    return false
                }

                this.$refs.table.$refs.tableserver.refresh()
                this.loading = false

            },
            deleteService: function (data) {
                this.loading = true
                API({
                    method: 'DELETE',
                    url: 'package/package_plan_rate_category/service/rates',
                    data: {
                        'object_ids': data.object_ids
                    }
                })
                    .then((result) => {
                        this.loading = false
                        if (result.data.success === true) {
                            service.added = false
                            service.object_ids = ''
                            this.updateRates()
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Paquetes - Cotizador',
                                text: this.$t('global.error.messages.information_error')
                            })
                        }
                    }).catch((e) => {
                    this.loading = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Paquetes - Cotizador',
                        text: this.$t('global.error.messages.information_error')
                    })
                })
            },
            updateRates () {
                API.get(window.origin + '/prices?category_id=' + this.$route.params.category_id).then((result) => {
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
                    this.loadingUpdate = 1
                    console.log(e)
                })
            },
            addService: function (index, service) {
                if (this.serviceByRates[index] && this.serviceByRates[index] !== '') {
                    let categoriesInsert = []
                    let check = false
                    for (let i = 0; i < this.categories_plan.length; i++) {
                        if (this.categories_plan[i].check) {
                            check = true
                            categoriesInsert.push({ 'id': this.categories_plan[i].id })
                        }
                    }
                    if (check) {
                        const codes = JSON.parse(localStorage.getItem('services_selected') ?? '[]')

                        let data = {
                            'package_plan_rate_id': this.plan_rate_id,
                            'date_in': this.convertDate(this.date_from, '/', '-'),
                            'service_id': service.id,
                            'categories': categoriesInsert,
                            'service_rates_id': this.serviceByRates[index].id,
                            'remove_codes': codes,
                        }
                        this.loading = true
                        API({
                            method: 'post',
                            url: 'package/package_plan_rate_category/service/rate',
                            data: data
                        })
                            .then((result) => {
                                if (result.data.success === true) {
                                    service.added = true
                                    service.object_ids = result.data.object_ids
                                    this.loading = false
                                    this.updateRates()
                                    this.updateDestinations()
                                    localStorage.removeItem('services_selected')
                                } else {
                                    service.added = false
                                    this.loading = false
                                    this.$notify({
                                        group: 'main',
                                        type: 'error',
                                        title: 'Paquetes - Cotizador',
                                        text: this.$t('global.error.messages.information_error')
                                    })
                                }
                            }).catch((e) => {
                            console.log(e)
                            this.loading = false
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Paquetes - Cotizador',
                                text: this.$t('global.error.messages.information_error')
                            })
                        })
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Paquetes - Cotizador',
                            text: 'Debe seleccionar una categoria'
                        })
                    }
                } else {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Paquetes - Cotizador',
                        text: 'Debe seleccionar una tarifa'
                    })
                }
            },
            back: function () {
                this.$router.push('/packages/' + this.$route.params.package_id + '/quotes/cost/' +
                    this.plan_rate_id + '/category/' + this.category_id)
            },
            addRate: function (index) {
                console.log(this.serviceByRates[index])
            },
            checkCategory: function (index) {
                this.categories_plan[index].check = !this.categories_plan[index].check
            },
            updateDestinations () {
                API.get(window.origin + '/destinations/update?package_id=' + this.$route.params.package_id).then((result) => {
                    console.log('Destinos actualizados')
                }).catch((e) => {
                    console.log(e)
                })
            },
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

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>
