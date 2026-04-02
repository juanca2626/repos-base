<template>
    <div class="container-fluid">

        <div class="row buttonTabs">
            <button type="button" :class="{'btn btn-primary':true, 'type_off':typeList!='GENERAL'}" @click="changeTypeTrain(null, 'GENERAL')">
                General
            </button>
            <button v-for="t in train_companies"
                    type="button" :class="{'btn btn-info':true, 'type_off':typeList!=t.code}"  @click="changeTypeTrain(t.id,  t.code)" style="margin-left: 5px;">
                {{ t.name }}
            </button>
        </div>

        <div v-show="showAddGeneral">
            <div class="row col-12" v-show="typeList=='GENERAL'">

                <div class="row col-12" style="margin-bottom: 15px;">
                    Nombre:
                    <input class="form-control" type="text" v-model="rail_route_name"
                           value="" placeholder="Ingrese un nombre">
                </div>

                <button class="right btn btn-danger" type="button" @click="showAddGeneral=false;showListGeneral=true;rail_route_id=null">
                    <font-awesome-icon :icon="['fas', 'ban']"/>
                    {{$t('global.buttons.cancel')}}
                </button>
                <button class="right btn btn-success" type="button" @click="addOrEditRouteGeneral()" style="margin-left: 5px;">
                    <font-awesome-icon :icon="['fas', 'save']"/>
                    {{$t('global.buttons.save')}}
                </button>
            </div>
        </div>

        <div v-show="showAddRoute" style="margin-bottom: 20px">
            <div class="row col-12">

                <div class="row col-12" style="margin-bottom: 15px;">
                    <div class="col-2">
                        Empresa:
                        <select class="form-control" type="text" v-model="selectTrain">
                            <option :value="train_c.id" v-for="train_c in train_companies">{{ train_c.name }}</option>
                        </select>
                    </div>
                    <div class="col-3">
                        Ruta:
                        <select class="form-control" type="text" v-model="selectRailRoute">
                            <option :value="train.id" v-for="train in trains">{{train.id}} - {{ train.name }}</option>
                        </select>
                    </div>
                    <div class="col-3">
                        Nombre:
                        <input class="form-control" type="text" v-model="train_rail_route_name"
                               value="" placeholder="Ingrese un nombre">
                    </div>
                    <div class="col-2">
                        Abreviación:
                        <input class="form-control" type="text" v-model="train_rail_route_abbreviation">
                    </div>
                    <div class="col-2">
                        Código:
                        <input class="form-control" type="text" v-model="train_rail_route_code">
                    </div>
                </div>

                <button class="right btn btn-danger" type="button" @click="showAddRoute=false">
                    <font-awesome-icon :icon="['fas', 'ban']"/>
                    {{$t('global.buttons.cancel')}}
                </button>
                <button class="right btn btn-success" type="button" @click="addOrEditRoute()" style="margin-left: 5px;">
                    <font-awesome-icon :icon="['fas', 'save']"/>
                    {{$t('global.buttons.save')}}
                </button>
            </div>
        </div>

        <div v-show="showListGeneral">

            <div class="row col-12 no-margin" v-show="typeList=='GENERAL'">
                <div class="col-8">
                    <input class="form-control" id="search_trains" type="search" v-model="query_trains"
                           value="" placeholder="Buscar por nombre o código">
                </div>
                <div class="col-4 no-margin">
                    <button class="right btn btn-danger" type="button"
                            @click="showAddGeneral=true;showListGeneral=false;rail_route_id=null;rail_route_name='';showAddRoute=false">
                        <font-awesome-icon :icon="['fas', 'plus']"/>
                        {{$t('global.buttons.add')}}
                    </button>
                </div>
            </div>

            <div class="table-responsive" v-show="typeList=='GENERAL'">
                <table class="VueTables__table table table-striped table-bordered table-hover">
                    <thead>
                    <tr>
                        <th class="vueTable_column_id">
                            <span title="" class="VueTables__heading">Id</span>
                        </th>
                        <th class="vueTable_column_package">
                            <span title="" class="VueTables__heading">Ruta</span>
                        </th>
                        <th class="vueTable_column_status">
                            <span title="" class="VueTables__heading">Status</span>
                        </th>
                        <th class="vueTable_column_actions">
                            <span title="" class="VueTables__heading">Acciones</span>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-if="trains.length>0" class="trPadding" v-for="train in trains">
                        <td class="vueTable_column_id">
                            {{ train.id }}
                        </td>
                        <td class="vueTable_column_package">
                            {{ train.name }}
                        </td>
                        <td class="vueTable_column_status">
                            <div class="table-status" style="font-size: 0.9em; cursor: pointer;">
                                <font-awesome-icon v-if="!loadingIcons" :icon="['fas', 'check-circle']" @click="changeStatus(train)" :class="'fa-2x check_'+train.status"/>
                                <font-awesome-icon v-if="loadingIcons" :icon="['fas', 'check-circle']" :class="'el_disabled fa-2x check_'+train.status"/>
                            </div>
                        </td>
                        <td class="vueTable_column_actions">
                            <div class="table-actions">
                                <div>
                                    <b-dropdown>
                                        <template v-slot:button-content>
                                            <font-awesome-icon :icon="['fas', 'bars']" />
                                        </template>
                                        <li class="nav-link m-0 p-0"
                                            @click="showAddGeneral=true;showListGeneral=false;rail_route_id=train.id;rail_route_name=train.name">
                                            <b-dropdown-item-button class="m-0 p-0">
                                                <font-awesome-icon :icon="['fas', 'edit']" class="m-0" /> {{$t('global.buttons.edit')}}
                                            </b-dropdown-item-button>
                                        </li>
                                        <li class="nav-link m-0 p-0" @click="willShowAddRoute('', train.id,train.name)">
                                            <b-dropdown-item-button class="m-0 p-0">
                                                <font-awesome-icon :icon="['fas', 'edit']" class="m-0" />
                                                Agregar por Empresa
                                            </b-dropdown-item-button>
                                        </li>
                                        <b-dropdown-item-button @click="showModal(train.id,train.name)" class="m-0 p-0">
                                            <font-awesome-icon :icon="['fas', 'trash']" class="m-0" />
                                            {{$t('global.buttons.delete')}}
                                        </b-dropdown-item-button>
                                    </b-dropdown>
                                </div>
                            </div>
                        </td>
                    </tr>

                    <tr v-if="trains.length==0" class="trPadding">
                        <td colspan="4">
                            <center><img src="/images/loading.svg" v-if="loading" width="40px"/></center>
                            <center><span v-if="!loading">Ninguno por mostrar</span></center>
                        </td>
                    </tr>

                    </tbody>
                </table>
            </div>

            <div class="VuePagination row col-md-12 justify-content-center" v-show="typeList=='GENERAL'">
                <nav class="text-center">
                    <ul class="pagination VuePagination__pagination" style="">
                        <li :class="{'VuePagination__pagination-item':true, 'page-item':true, 'VuePagination__pagination-item-prev-chunk':true,
                            'disabled':(pageChosen==1 || loading)}" @click="setPage(pageChosen-1)">
                            <a href="javascript:void(0);" :disabled="(pageChosen==1 || loading)" class="page-link">&lt;</a>
                        </li>
                        <li v-for="page in train_pages" @click="setPage(page)"
                            :class="{'VuePagination__pagination-item':true,'page-item':true,'active':(page==pageChosen), 'disabled':loading }">
                            <a href="javascript:void(0)" class="page-link active" role="button">{{ page }}</a>
                        </li>
                        <li :class="{'page-item':true,'VuePagination__pagination-item':true,'VuePagination__pagination-item-next-chunk':true,
                            'disabled':(pageChosen==train_pages.length || loading)}" @click="setPage(pageChosen+1)">
                            <a href="javascript:void(0);" :disabled="(pageChosen==train_pages.length || loading)" class="page-link">&gt;</a>
                        </li>
                    </ul>
                </nav>
            </div>

        </div>
        <!---->

        <div class="row col-12 no-margin" v-show="typeList!='GENERAL'">
            <div class="col-8">
                <input class="form-control" id="search_train_routes" type="search" v-model="query_train_routes"
                       value="" placeholder="Buscar por nombre o código">
            </div>
            <div class="col-4 no-margin">
                <button class="right btn btn-danger" type="button"
                        @click="willShowAddRoute(_train_id,'','')">
                    <font-awesome-icon :icon="['fas', 'plus']"/>
                    {{$t('global.buttons.add')}}
                </button>
            </div>
        </div>

        <div class="table-responsive" v-show="typeList!='GENERAL'">
            <table class="VueTables__table table table-striped table-bordered table-hover">
                <thead>
                <tr>
                    <th class="vueTable_column_id">
                        <span title="" class="VueTables__heading">Id</span>
                    </th>
                    <th class="vueTable_column_code">
                        <span title="" class="VueTables__heading">Código</span>
                    </th>
                    <th class="vueTable_column_package">
                        <span title="" class="VueTables__heading">Ruta</span>
                    </th>
                    <th class="vueTable_column_abbreviation">
                        <span title="" class="VueTables__heading">Abrev.</span>
                    </th>
                    <th class="vueTable_column_actions">
                        <span title="" class="VueTables__heading">Acciones</span>
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr v-if="train_routes.length>0" class="trPadding" v-for="train_route in train_routes">
                    <td class="vueTable_column_id">
                        {{ train_route.id }}
                    </td>
                    <td class="vueTable_column_code">
                        {{ train_route.code }}
                    </td>
                    <td class="vueTable_column_package">
                        {{ train_route.name }}
                    </td>
                    <td class="vueTable_column_abbreviation">
                        {{ train_route.abbreviation }}
                    </td>
                    <td class="vueTable_column_actions">
                        <div class="table-actions">
                            <div>
                                <b-dropdown>
                                    <template v-slot:button-content>
                                        <font-awesome-icon :icon="['fas', 'bars']" />
                                    </template>
                                    <li class="nav-link m-0 p-0"
                                        @click="willShowEditRoute( train_route )">
                                        <b-dropdown-item-button class="m-0 p-0">
                                            <font-awesome-icon :icon="['fas', 'edit']" class="m-0" /> {{$t('global.buttons.edit')}}
                                        </b-dropdown-item-button>
                                    </li>
                                    <b-dropdown-item-button @click="showModal(train_route.id,train_route.name)" class="m-0 p-0">
                                        <font-awesome-icon :icon="['fas', 'trash']" class="m-0" />
                                        {{$t('global.buttons.delete')}}
                                    </b-dropdown-item-button>
                                </b-dropdown>
                            </div>
                        </div>
                    </td>
                </tr>

                <tr v-if="train_routes.length==0" class="trPadding">
                    <td colspan="5">
                        <center><img src="/images/loading.svg" v-if="loading" width="40px"/></center>
                        <center><span v-if="!loading">Ninguno por mostrar</span></center>
                    </td>
                </tr>

                </tbody>
            </table>
        </div>

        <div class="VuePagination row col-md-12 justify-content-center" v-show="typeList!='GENERAL'">
            <nav class="text-center">
                <ul class="pagination VuePagination__pagination" style="">
                    <li :class="{'VuePagination__pagination-item':true, 'page-item':true, 'VuePagination__pagination-item-prev-chunk':true,
                        'disabled':(pageChosenRoute==1 || loading)}" @click="setPageRoute(pageChosenRoute-1)">
                        <a href="javascript:void(0);" :disabled="(pageChosenRoute==1 || loading)" class="page-link">&lt;</a>
                    </li>
                    <li v-for="page in train_route_pages" @click="setPageRoute(page)"
                        :class="{'VuePagination__pagination-item':true,'page-item':true,'active':(page==pageChosenRoute), 'disabled':loading }">
                        <a href="javascript:void(0)" class="page-link active" role="button">{{ page }}</a>
                    </li>
                    <li :class="{'page-item':true,'VuePagination__pagination-item':true,'VuePagination__pagination-item-next-chunk':true,
                        'disabled':(pageChosenRoute==train_route_pages.length || loading)}" @click="setPageRoute(pageChosenRoute+1)">
                        <a href="javascript:void(0);" :disabled="(pageChosenRoute==train_route_pages.length || loading)" class="page-link">&gt;</a>
                    </li>
                </ul>
            </nav>
        </div>


        <b-modal :title="trainName" centered ref="my-modal" size="sm">
            <p class="text-center">¿Seguro que desea eliminar el elemento?</p>

            <div slot="modal-footer">
                <button @click="remove()" class="btn btn-success">{{$t('global.buttons.accept')}}</button>
                <button @click="hideModal()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
            </div>
        </b-modal>


        <block-page></block-page>
    </div>



</template>

<script>
    import { API } from './../../../api'
    import BFormCheckbox from 'bootstrap-vue/es/components/form-checkbox/form-checkbox'
    import BlockPage from '../../../components/BlockPage'
    import BModal from 'bootstrap-vue/es/components/modal/modal'

    export default {
        components: {
            BFormCheckbox,
            BlockPage,
            BModal
        },
        data: () => {
            return {
                loading: false,
                loadingIcons: false,
                ratesChoosed : [],
                train_companies: [],
                trains: [],
                train_routes: [],
                train_id: null,
                query_trains: '',
                query_train_routes: '',
                trainName: '',
                pageChosen : 1,
                limit : 10,
                train_pages : [],
                pageChosenRoute : 1,
                limitRoute : 10,
                train_route_pages : [],
                rail_route_id : null,
                _train_id : null,
                typeList:"GENERAL",
                showAddRoute:false,
                showAddGeneral:false,
                showListGeneral:true,
                rail_route_name:'',
                train_rail_route_id : null,
                train_rail_route_name : "",
                train_rail_route_code : "",
                train_rail_route_abbreviation : "",
                selectTrain : "",
                selectRailRoute : "",
            }
        },
        mounted () {

            this.$i18n.locale = localStorage.getItem('lang')
            this.$root.$emit('updateTitleTrain', { title: "Administrador de Rutas de Trenes" })

            let search_trains = document.getElementById('search_trains')
            let timeout_extensions
            search_trains.addEventListener('keydown', () => {
                clearTimeout(timeout_extensions)
                timeout_extensions = setTimeout(() => {
                    this.pageChosen = 1
                    this.onUpdate()
                    clearTimeout(timeout_extensions)
                }, 1000)
            })

            let search_train_routes = document.getElementById('search_train_routes')
            let timeout_route_trains
            search_train_routes.addEventListener('keydown', () => {
                clearTimeout(timeout_route_trains)
                timeout_route_trains = setTimeout(() => {
                    this.pageChosenRoute = 1
                    this.onUpdateByTrain()
                    clearTimeout(timeout_route_trains)
                }, 1000)
            })

            this.onUpdate()

            //trains
            API.get('/trains')
                .then((result) => {
                    this.train_companies = result.data.data
                }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('services.error.messages.name'),
                        text: this.$t('services.error.messages.connection_error')
                    })
            })

        },
        created () {
            localStorage.setItem("trainnamemanage", "")
        },
        computed: {
        },
        methods: {
            willShowEditRoute(train_rail_route){
                this.showAddRoute=true
                this.train_rail_route_id=train_rail_route.id
                this.train_rail_route_name=train_rail_route.name
                this.train_rail_route_code=train_rail_route.code
                this.train_rail_route_abbreviation=train_rail_route.abbreviation
                this.selectTrain = train_rail_route.train_id
                this.selectRailRoute = train_rail_route.rail_route_id
            },
            willShowAddRoute(train_id, rail_route_id, rail_route_name){
                this.showAddRoute=true
                this.train_rail_route_id=null
                this.train_rail_route_name=rail_route_name
                this.train_rail_route_code=''
                this.train_rail_route_abbreviation=''
                this.selectTrain = train_id
                this.selectRailRoute = rail_route_id
            },
            addOrEditRouteGeneral(){

                if( this.rail_route_name == '' ){
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Datos incompletos',
                        text: "Por favor ingrese un nombre a la ruta"
                    })
                    return
                }

                this.loading = true

                let _method = 'POST'
                let _url = 'rail_routes'

                if( this.rail_route_id != null ){
                    _method = 'PUT'
                    _url += '/' + this.rail_route_id
                }

                API({
                    method: _method,
                    url: _url,
                    data: { name : this.rail_route_name }
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.$notify({
                                group: 'main',
                                type: 'success',
                                title: "Modulo de Trenes",
                                text: "Guardado correctamente"
                            })
                            this.onUpdate()
                            this.rail_route_name = ''
                            this.showAddGeneral = false
                            this.showListGeneral = true
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: "Modulo de Trenes",
                                text: result.data.message
                            })
                        }
                        this.loading = true
                    }).catch(() => {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Error Modulo Trenes',
                            text: this.$t('trains.error.messages.connection_error')
                        })
                    })
            },
            addOrEditRoute(){

                if( this.selectTrain == '' ){
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Datos incompletos',
                        text: "Por favor seleccione una empresa de tren"
                    })
                    return
                }
                if( this.selectRailRoute == '' ){
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Datos incompletos',
                        text: "Por favor ingrese una ruta"
                    })
                    return
                }
                if( this.train_rail_route_abbreviation == '' ){
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Datos incompletos',
                        text: "Por favor ingrese una abreviación"
                    })
                    return
                }
                if( this.train_rail_route_code == '' ){
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Datos incompletos',
                        text: "Por favor ingrese un código"
                    })
                    return
                }
                if( this.train_rail_route_name == '' ){
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Datos incompletos',
                        text: "Por favor ingrese un nombre a la ruta"
                    })
                    return
                }

                this.loading = true

                let _method = 'POST'
                let _url = 'train_rail_routes'

                if( this.train_rail_route_id != null ){
                    _method = 'PUT'
                    _url += '/' + this.train_rail_route_id
                }

                API({
                    method: _method,
                    url: _url,
                    data: {
                        name : this.train_rail_route_name,
                        code : this.train_rail_route_code,
                        abbreviation : this.train_rail_route_abbreviation,
                        train_id : this.selectTrain,
                        rail_route_id : this.selectRailRoute
                    }
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.$notify({
                                group: 'main',
                                type: 'success',
                                title: "Modulo de Trenes",
                                text: "Guardado correctamente"
                            })
                            if( this.typeList != "GENERAL" ){
                                this.onUpdateByTrain()
                            }
                            this.showAddRoute = false
                            this.train_rail_route_id = null
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: "Modulo de Trenes",
                                text: result.data.message
                            })
                        }
                        this.loading = true
                    }).catch(() => {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Error Modulo Trenes',
                            text: this.$t('trains.error.messages.connection_error')
                        })
                    })
            },
            changeTypeTrain(_type_id, _type_code){
                this.showAddRoute = false
                this.typeList = _type_code
                if( _type_code == 'GENERAL' ){
                    this.onUpdate()
                } else {
                    this._train_id = _type_id
                    this.onUpdateByTrain()
                }
            },
            onUpdate () {

                this.loading = true
                this.trains = []

                API({
                    method: 'GET',
                    url: 'rail_routes?queryCustom='+this.query_trains +
                        '&page=' + this.pageChosen + '&limit=' + this.limit
                })
                    .then((result) => {
                        this.train_pages = []
                        for( let i=0; i<(result.data.count/this.limit); i++){
                            this.train_pages.push(i+1)
                        }

                        this.trains = result.data.data
                        this.loading = false

                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Error Modulo Trenes',
                        text: this.$t('trains.error.messages.connection_error')
                    })
                })
            },
            onUpdateByTrain () {

                this.loading = true
                this.train_routes = []

                API({
                    method: 'GET',
                    url: 'train_rail_routes/train/'+this._train_id +
                            '?queryCustom='+this.query_train_routes +
                            '&page=' + this.pageChosenRoute + '&limit=' + this.limitRoute
                })
                    .then((result) => {
                        this.train_route_pages = []
                        for( let i=0; i<(result.data.count/this.limitRoute); i++){
                            this.train_route_pages.push(i+1)
                        }

                        this.train_routes = result.data.data
                        this.loading = false

                    }).catch(() => {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Error Modulo Trenes',
                            text: this.$t('trains.error.messages.connection_error')
                        })
                    })
            },
            remove () {

                let _route = "train_rail_routes"
                if( this.typeList=='GENERAL' ){
                    _route = "rail_routes"
                }

                API({
                    method: 'DELETE',
                    url: _route + '/' + this.rail_route_id
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            if( this.typeList=='GENERAL' ){
                                this.onUpdate()
                            } else {
                                this.onUpdateByTrain()
                            }
                            this.hideModal()
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: "Modulo de Trenes",
                                text: result.data.message
                            })
                        }
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: "Modulo de Trenes",
                        text: this.$t('global.error.messages.connection_error')
                    })
                })
            },
            hideModal () {
                this.$refs['my-modal'].hide()
            },
            showModal (id, name) {
                this.rail_route_id = id
                this.trainName = name
                this.$refs['my-modal'].show()
            },
            setPage(page){
                if( page < 1 || page > this.train_pages.length ){
                    return;
                }
                this.pageChosen = page
                this.onUpdate()
            },
            setPageRoute(page){
                if( page < 1 || page > this.train_route_pages.length ){
                    return;
                }
                this.pageChosenRoute = page
                this.onUpdateByTrain()
            },
            changeStatus(me){
                this.loadingIcons = true

                API.put('/rail_route/'+me.id+'/status',{ status:!me.status })
                    .then((result) => {
                        if( result.data.success ){
                            me.status = !me.status
                        } else {
                            console.log(result)
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: "Modulo de Trenes",
                                text: this.$t('services.error.messages.connection_error')
                            })
                        }
                        this.loadingIcons = false
                    }).catch(() => {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: "Modulo de Trenes",
                            text: this.$t('services.error.messages.connection_error')
                        })
                })
            }
        },
        filters: {
            formatDate: function (_date) {
                if (_date == undefined) {
                    // console.log('fecha no parseada: ' + _date)
                    return
                }
                _date = _date.split('-')
                _date = _date[2] + '/' + _date[1] + '/' + _date[0]
                return _date
            }
        }
    }
</script>

<style lang="stylus">
    .table-actions {
        display: flex;
    }
    .trExtension, .trExtension > th, .trExtension > td {
        background-color: #e9eaff;
    }
    .trExtension:hover, .trExtension:hover > th, .trExtension:hover > td {
        background-color: #e2e3ff;
    }
    .VueTables__limit {
        display: none;
    }
    .no-margin{
        padding-left: 0;
        padding-bottom: 5px !important;
        padding-right: 0px;
    }
    .trPadding, .trPadding > th, .trPadding > td{
        padding: 10px !important;
    }
    .check_true, .check_1{
        color: #04bd12;
    }
    .el_disabled{
        opacity: 0.5;
    }
    .type_off{
        opacity: 0.5;
    }
    .buttonTabs{
        margin-bottom: 10px;
        padding-bottom: 10px !important;
        padding-left: 15px;
        border-bottom: solid 1px #a5c6c6;
    }


</style>
