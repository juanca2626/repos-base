<template>
    <div class="card">
        <div class="container-fluid col-lg-12">
            <div class="row col-lg-12">
                <div class="col-lg-12">
                    <b-form-radio-group
                        v-model="selectedType"
                        :options="options"
                        class="mb-3"
                        value-field="item"
                        text-field="name"
                        disabled-field="notEnabled"
                    ></b-form-radio-group>
                </div>
            </div>
            <div class="row col-lg-12">
                <div class="col-lg-6">
                    <div class="col-lg-12">
                        <h4 class="text-center">Ciudades</h4>
                    </div>
                    <div class="col-lg-12 mb-3">
                        <input type="text" class="form-control" placeholder="Filtro por ciudad"
                               v-model="filter_city_name" @keyup.enter="getCitiesByName()">
                    </div>
                    <div class="col-lg-12">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                            <tr>
                                <th scope="col">Ciudad</th>
                                <th scope="col">Orden</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="city in cities.data">
                                <td>{{ city.translations[0].value }}</td>
                                <td><input class="form-control" type="text" v-model="city.hotel_order_rate.order">
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="row col-lg-12 pr-0 mb-3">
                        <div class="col-lg-6">
                            <button class="btn btn-success" v-bind:disabled="loading" v-if="cities.data.length > 0"
                                    @click="updateOrderCity()"><i class="fas fa-save"></i> Actualizar Orden
                            </button>
                        </div>
                    </div>
                    <div class="row col-lg-12 pr-0">
                        <div class="col-lg-6">
                            <button class="btn btn-success" v-bind:disabled="loading" v-if="cities.prev_page_url !=null"
                                    @click="getCitiesByPage(cities.prev_page_url)"><i class="fas fa-chevron-left"></i> Pagina anterior
                            </button>
                        </div>
                        <div class="col-lg-6 pr-0">
                            <button class="btn btn-success right" v-bind:disabled="loading" v-if="cities.next_page_url !=null"
                                    @click="getCitiesByPage(cities.next_page_url)">Pagina siguiente <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="col-lg-12">
                        <h4 class="text-center">Hoteles</h4>
                    </div>
                    <div class="row col-lg-12 mb-3">
                        <div class="col-lg-6">
                            <v-select :options="cities_vue_select"
                                      @input="filterByCity"
                                      v-model="citySelected">
                            </v-select>
                        </div>
                        <div class="col-lg-6">
                            <select name="" class="form-control" id="" v-model="subCategorySelected"
                                    @change="getServicesByCity(citySelected.code)">
                                <option :value="category.id" v-for="category in subCategoriesServices">
                                    {{ category.translations[0].value }}
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                            <tr>
                                <th>Codigo</th>
                                <th>Servicio</th>
                                <th>Orden</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(service, s) in orderedHotel">
                                <td>{{ service.channel[0].code }}</td>
                                <td style="text-align: left; padding-left: 10px;">
                                    <span :inner-html.prop="service.stars | render_stars"></span> {{ service.name }}
                                </td>
                                <td>
                                    <div v-if="selectedType === 'rate'">
                                        <input class="form-control" type="text"
                                               v-bind:value="(service.rate_order_new != undefined) ? service.rate_order_new : service.rate_order"
                                               v-on:change="setValue(service, 'rate_order_new', $event)">
                                    </div>
                                    <div v-else>
                                        <input class="form-control" type="text"
                                            v-bind:value="(service.order_new != undefined) ? service.rate_order_new : service.order"
                                            v-on:change="setValue(service, 'order_new', $event)">
                                    </div>

                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="row col-lg-12 pr-0 mb-3">
                        <div class="col-lg-6">
                            <button class="btn btn-success" v-bind:disabled="loading" v-if="orderedHotel.length > 0"
                                    @click="updateOrderService()"><i class="fas fa-save"></i> Actualizar Orden
                            </button>
                        </div>
                    </div>
                    <div class="row col-lg-12 pr-0">
                        <div class="col-lg-6">
                            <button class="btn btn-success" v-bind:disabled="loading" v-if="services.prev_page_url !=null"
                                    @click="getServicesByPage(services.prev_page_url)"><i class="fas fa-chevron-left"></i> Pagina anterior
                            </button>
                        </div>
                        <div class="col-lg-6 pr-0">
                            <button class="btn btn-success" v-bind:disabled="loading" v-if="services.next_page_url !=null"
                                    @click="getServicesByPage(services.next_page_url)">Pagina siguiente <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import { API } from './../../api'
    import vSelect from 'vue-select'
    import 'vue-select/dist/vue-select.css'

    export default {
        components: {
            vSelect
        },
        data () {
            return {
                cities: [],
                route_city_endpoint: '/cities/orders/hotels/rate?page=1',
                filter_city_name: '',
                services: [],
                route_service_endpoint: '/hotels/orders/rate?page=1',
                filter_service_code: '',
                citySelected: null,
                cities_vue_select: [],
                subCategorySelected: null,
                selectedType: 'rate',
                loading: false,
                options: [
                    { item: 'rate', name: 'Orden de tarifario' },
                    { item: 'hotel', name: 'Orden de hoteles' },
                ],
                subCategoriesServices: []
            }
        },
        computed: {
            orderedHotel: function () {
                let type = 'rate_order'
                if(this.selectedType == 'hotel'){
                    type = 'order'
                }
                return _.orderBy(this.services.data, type)
            }
        },
        mounted: function () {
            this.getCities()
            this.getCitiesBySelect()
            this.getSubCategoriesByService()
        },
        methods: {
            setValue: function (_service, _key, $event) {
                let vm = this
                vm.$set(_service, _key, $event.target.value)
            },
            filterByCity: function (city) {
                this.citySelected = city
                this.getServicesByCity(city.code)
            },
            getSubCategoriesByService: function () {
                API.get('/typeclass/selectbox?lang=' + localStorage.getItem('lang'))
                    .then((result) => {
                        this.subCategoriesServices = result.data.data
                    })
            },
            getCitiesBySelect: function () {
                API.get('/cities/peru/vue_select')
                    .then((result) => {
                        this.cities_vue_select = result.data
                    })
            },
            getCities: function () {
                this.loading = true
                API.get(this.route_city_endpoint)
                    .then((result) => {
                        this.loading = false
                        this.cities = result.data
                    })
            },
            updateOrderCity: function () {
                let vm = this
                vm.loading = true

                API.put('/cities/orders/hotels/rate/update', {
                    cities: this.cities.data
                })
                    .then((result) => {
                        vm.loading = false
                        vm.getCities()
                    })
            },
            getCitiesByName: function () {
                this.route_city_endpoint = ('http://aurora_backend.test' === window.origin) ? this.route_city_endpoint : this.route_city_endpoint.replace('http:', 'https:')
                if (this.filter_city_name != '') {
                    API.get(this.route_city_endpoint + '&filter_by_name=' + this.filter_city_name)
                        .then((result) => {
                            this.cities = result.data
                        })
                } else {
                    this.getCities()
                }
            },
            getCitiesByPage: function (route_city_endpoint) {
                this.loading = true
                let route_city_endpoint_new = ('http://aurora_backend.test' === window.origin) ? route_city_endpoint : route_city_endpoint.replace('http:', 'https:')
                API.get(route_city_endpoint_new)
                    .then((result) => {
                        this.loading = false
                        this.cities = result.data
                    })
            },
            getServices: function () {
                this.route_service_endpoint = ('http://aurora_backend.test' === window.origin) ? this.route_service_endpoint : this.route_service_endpoint.replace('http:', 'https:')
                API.get(this.route_service_endpoint)
                    .then((result) => {
                        this.services = result.data
                    })
            },
            updateOrderService: function () {
                let vm = this
                vm.loading = true

                API.put('/hotels/orders/rate/update', {
                    hotels: vm.orderedHotel,
                    type: vm.selectedType,
                })
                .then((result) => {
                    vm.loading = false
                    vm.getServicesByPage(vm.route_service_endpoint)
                })
            },
            getServicesByCity: function (city_id) {
                this.route_service_endpoint = ('http://aurora_backend.test' === window.origin) ? this.route_service_endpoint : this.route_service_endpoint.replace('http:', 'https:')
                if (city_id != null) {
                    if (this.subCategorySelected != null) {
                        API.get(this.route_service_endpoint + '&filter_city_id=' + city_id + '&filter_subcategory_id=' + this.subCategorySelected + '&type=' + this.selectedType)
                            .then((result) => {
                                let data = result.data
                                this.services =data
                            })

                    }
                }
            },
            getServicesByPage: function (route_service_endpoint) {
                let route_service_endpoint_new = ('http://aurora_backend.test' === window.origin) ? route_service_endpoint : route_service_endpoint.replace('http:', 'https:')
                if (this.citySelected != null) {
                    route_service_endpoint_new += '&filter_city_id=' + this.citySelected.code
                }
                if (this.subCategorySelected != null) {
                    route_service_endpoint_new += '&filter_subcategory_id=' + this.subCategorySelected
                }
                API.get(route_service_endpoint_new)
                    .then((result) => {
                        this.services = result.data
                    })
            }
        },
        filters: {
            render_stars (total) {

                let render = ''

                for (let i = 0; i < total; i++) {
                    render += '<i class="yellow_icon fa fa-star"></i>'
                }

                return render
            }
        }
    }
</script>

<style>
    .yellow_icon {
        color: #e5c900;
    }
</style>
