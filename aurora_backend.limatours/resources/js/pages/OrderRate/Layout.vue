<template>
   <div class="container-fluid col-lg-12">
       <div class="row col-lg-12">
           <div class="col-lg-6">
               <div class="col-lg-12">
                   <h4 class="text-center">Ciudades</h4>
               </div>
               <div class="col-lg-12">
                   <input type="text" placeholder="Filtro por ciudad" v-model="filter_city_name"  @keyup.enter="getCitiesByName()">
               </div>
               <div class="col-lg-12">
                   <table class="table-bordered">
                       <thead>
                        <tr>
                            <td>Ciudad</td>
                            <td>Orden</td>
                        </tr>
                       </thead>
                       <tbody>
                            <tr v-for="city in cities.data">
                                <td>{{ city.translations[0].value }}</td>
                                <td><input class="form-control" type="text" v-model="city.order_rate.order" @keyup.enter="updateOrderCity(city.order_rate.id,city.order_rate.order)"></td>
                            </tr>
                       </tbody>
                   </table>
               </div>
               <div class="row col-lg-12">
                   <div class="col-lg-6">
                       <button class="btn btn-success" v-if="cities.prev_page_url !=null" @click="getCitiesByPage(cities.prev_page_url)">Pagina anterior</button>
                   </div>
                   <div class="col-lg-6">
                       <button class="btn btn-success" v-if="cities.next_page_url !=null" @click="getCitiesByPage(cities.next_page_url)">Pagina siguiente</button>
                   </div>
               </div>
           </div>
           <div class="col-lg-6">
               <div class="col-lg-12">
                   <h4 class="text-center">Servicios</h4>
               </div>
               <div class="row col-lg-12">
                   <div class="col-lg-6">
                       <v-select :options="cities_vue_select"
                                 @input="filterByCity"
                                 v-model="citySelected">
                       </v-select>
                   </div>
                   <div class="col-lg-6">
                       <select name="" id="" v-model="subCategorySelected" @change="getServicesByCity(citySelected.code)">
                           <template v-for="category in subCategoriesServices">
                               <option :value="subcategory.id" v-for="subcategory in category.service_sub_category">{{ category.translations[0].value }} {{ subcategory.translations[0].value }}</option>
                           </template>
                       </select>
                   </div>
               </div>
               <div class="col-lg-12">
                   <table class="table-bordered">
                       <thead>
                       <tr>
                           <td>Codigo</td>
                           <td>Servicio</td>
                           <td>Orden</td>
                       </tr>
                       </thead>
                       <tbody>
                       <tr v-for="service in services.data">
                           <td>{{ service.aurora_code }}</td>
                           <td>{{ service.name }}</td>
                           <td><input class="form-control" type="text" v-model="service.rate_order" @keyup.enter="updateOrderService(service.rate_order,service.id,service.service_sub_category_id)"></td>
                       </tr>
                       </tbody>
                   </table>
               </div>
               <div class="row col-lg-12">
                   <div class="col-lg-6">
                       <button class="btn btn-success" v-if="services.prev_page_url !=null" @click="getServicesByPage(services.prev_page_url)">Pagina anterior</button>
                   </div>
                   <div class="col-lg-6">
                       <button class="btn btn-success" v-if="services.next_page_url !=null" @click="getServicesByPage(services.next_page_url)">Pagina siguiente</button>
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
                cities:[],
                route_city_endpoint:'/cities/orders/services/rate?page=1',
                filter_city_name:'',
                services:[],
                route_service_endpoint:'/services/orders/rate?page=1',
                filter_service_code:'',
                citySelected:null,
                cities_vue_select:[],
                subCategorySelected:null,
                subCategoriesServices:[]
            }
        },
        computed: {

        },
        mounted: function () {
            this.getCities()
            this.getCitiesBySelect()
            this.getSubCategoriesByService()
        },
        methods: {
            filterByCity:function(city){
                this.citySelected = city
                this.getServicesByCity(city.code)
            },
            getSubCategoriesByService:function(){
                API.get('/service_sub_categories')
                    .then((result)=>{
                        this.subCategoriesServices = result.data
                    })
            },
            getCitiesBySelect:function(){
                API.get('/cities/peru/vue_select')
                    .then((result)=>{
                        this.cities_vue_select = result.data
                })
            },
            getCities:function(){
                API.get(this.route_city_endpoint)
                    .then((result) => {
                        this.cities = result.data
                    })
            },
            updateOrderCity:function(order_rate_id,order)
            {
                API.put('/cities/orders/services/rate/update',{ order_city_id:order_rate_id,order:order})
                    .then((result) => {
                        this.getCities()
                    })
            },
            getCitiesByName:function()
            {
               this.route_city_endpoint =  this.route_city_endpoint.replace('http','https')
                if (this.filter_city_name!='')
                {
                    API.get(this.route_city_endpoint+'&filter_by_name='+this.filter_city_name)
                        .then((result) => {
                            this.cities = result.data
                        })
                }else{
                    this.getCities()
                }
            },
            getCitiesByPage:function (route_city_endpoint) {
                let  route_city_endpoint_new = route_city_endpoint.replace('http','https')
                API.get(route_city_endpoint_new)
                    .then((result) => {
                        this.cities = result.data
                    })
            },
            getServices:function(){
               this.route_service_endpoint = this.route_service_endpoint.replace('http','https')
                API.get(this.route_service_endpoint)
                    .then((result) => {
                        this.services = result.data
                    })
            },
            updateOrderService:function(order,service_id,service_sub_category_id)
            {
                API.put('/services/orders/rate/update',{ service_id:service_id,order:order,service_sub_category_id:service_sub_category_id,city_id:this.citySelected.code})
                    .then((result) => {
                        this.getServicesByPage(this.route_service_endpoint)
                    })
            },
            getServicesByCity:function(city_id){
                this.route_service_endpoint = this.route_service_endpoint.replace('http','https')
                if (city_id!=null)
                {
                    if (this.subCategorySelected !=null)
                    {
                        API.get(this.route_service_endpoint+'&filter_city_id='+city_id+'&filter_subcategory_id='+this.subCategorySelected)
                            .then((result) => {
                                this.services = result.data
                            })

                    }
                }
            },
            getServicesByPage:function (route_service_endpoint) {
                let route_service_endpoint_new = route_service_endpoint.replace('http','https')
                if(this.citySelected !=null)
                {
                    route_service_endpoint_new+='&filter_city_id='+this.citySelected.code
                }
                if (this.subCategorySelected !=null)
                {
                    route_service_endpoint_new+='&filter_subcategory_id='+this.subCategorySelected
                }
                API.get(route_service_endpoint_new)
                    .then((result) => {
                        this.services = result.data
                    })
            }
        }
    }
</script>

<style scoped>

</style>
