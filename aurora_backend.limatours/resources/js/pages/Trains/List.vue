<template>
    <div class="container-fluid">

        <div class="row col-12 no-margin">
            <div class="col-8">
                <input class="form-control" id="search_trains" type="search" v-model="query_trains"
                       value="" placeholder="Buscar por nombre o código">
            </div>
            <div class="col-2">
                <select data-vv-as="train_companies" class="form-control"
                        data-vv-name="train_companies"
                        name="train_companies"
                        @change="onUpdate()"
                        v-model="trainCompaniesSelected">
                    <option value="all" selected>Todos</option>
                    <option :value="t.id" v-for="t in train_companies">{{ t.name }}</option>
                </select>
            </div>

            <div class="col-2">
                <button type="button" class="btn btn-danger" @click="importService()" v-show="showImport">Importar</button>
            </div>
        </div>

        <div class="row col-12 no-margin" v-show="showImport">

            <div class="col-8">
            </div>

            <div class="col-2">
                <input type="text" class="form-control right" placeholder="skip" v-model="importSkip">
            </div>
            <div class="col-2">
                <input type="text" class="form-control right" placeholder="limit" v-model="importLimit">
            </div>

        </div>

        <div class="table-responsive">
            <table class="VueTables__table table table-striped table-bordered table-hover">
                <thead>
                <tr>
                    <th class="vueTable_column_id">
                        <span title="" class="VueTables__heading">Id</span>
                    </th>
                    <th class="vueTable_column_period">
                        <span title="" class="VueTables__heading">Compañía</span>
                    </th>
                    <th class="vueTable_column_package">
                        <span title="" class="VueTables__heading">Ruta: Desde - Hasta</span>
                    </th>
                    <th class="vueTable_column_name">
                        <span title="" class="VueTables__heading">Clase</span>
                    </th>
                    <th class="vueTable_column_categories">
                        <span title="" class="VueTables__heading">Código</span>
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
                    <td class="vueTable_column_period">
                        {{ train.train_rail_route.train.name }}
                    </td>
                    <td class="vueTable_column_package">
                        {{ train.train_rail_route.name }}
                    </td>
                    <td class="vueTable_column_name">
                        <div class="table-category" style="font-size: 0.9em;">
                            {{ train.train_train_class.name }}
                        </div>
                    </td>
                    <td class="vueTable_column_categories">
                        <div class="table-category" style="font-size: 0.9em;">
                            {{ train.aurora_code }}
                        </div>
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
                                    <router-link :to="'/trains/edit/'+train.id">
                                        <b-dropdown-item-button class="m-0 p-0" v-if="$can('update', 'trains')">
                                            <font-awesome-icon :icon="['fas', 'edit']" class="m-0" /> {{$t('global.buttons.edit')}}
                                        </b-dropdown-item-button>
                                    </router-link>
                                    <li @click="toManage(train)" class="nav-link m-0 p-0">
                                        <b-dropdown-item-button class="m-0 p-0" v-if="$can('update', 'trains')">
                                            <font-awesome-icon :icon="['fas', 'edit']" class="m-0" />
                                            {{$t('services.manage')}}
                                        </b-dropdown-item-button>
                                    </li>
                                    <b-dropdown-item-button @click="showModal(train.id,train.name)" class="m-0 p-0"
                                                            v-if="$can('delete', 'trains')">
                                        <font-awesome-icon :icon="['fas', 'trash']" class="m-0" />
                                        {{$t('global.buttons.delete')}}
                                    </b-dropdown-item-button>
                                </b-dropdown>
                            </div>
                        </div>
                    </td>
                </tr>

                <tr v-if="trains.length==0" class="trPadding">
                    <td colspan="7">
                        <center><img src="/images/loading.svg" v-if="loading" width="40px"/></center>
                        <center><span v-if="!loading">Ninguno por mostrar</span></center>
                    </td>
                </tr>

                </tbody>
            </table>
        </div>

        <div class="VuePagination row col-md-12 justify-content-center">
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


        <b-modal :title="trainName" centered ref="my-modal" size="sm">
            <p class="text-center">{{$t('global.message_delete')}}</p>

            <div slot="modal-footer">
                <button @click="remove()" class="btn btn-success">{{$t('global.buttons.accept')}}</button>
                <button @click="hideModal()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
            </div>
        </b-modal>


        <block-page></block-page>
    </div>



</template>

<script>
    import { API } from './../../api'
    import TableServer from '../../components/TableServer'
    import BFormCheckbox from 'bootstrap-vue/es/components/form-checkbox/form-checkbox'
    import BlockPage from '../../components/BlockPage'
    import BModal from 'bootstrap-vue/es/components/modal/modal'

    export default {
        components: {
            BFormCheckbox,
            'table-server': TableServer,
            BlockPage,
            BModal
        },
        data: () => {
            return {
                loading: false,
                loadingIcons: false,
                ratesChoosed : [],
                train_companies: [],
                trainCompaniesSelected: "all",
                trains: [],
                train_id: null,
                query_trains: '',
                trainName: '',
                pageChosen : 1,
                limit : 10,
                train_pages : [],
                train_template_id : null,
                showImport : false,
                importLimit : 50,
                importSkip : 0,
            }
        },
        mounted () {

            this.$i18n.locale = localStorage.getItem('lang')
            this.$root.$emit('updateTitleTrain', { tab: 1 })

            let search_trains = document.getElementById('search_trains')
            let timeout_extensions
            search_trains.addEventListener('keydown', () => {
                clearTimeout(timeout_extensions)
                timeout_extensions = setTimeout(() => {
                    this.pageChosen = 1

                    console.log( this.query_trains )
                    if( this.query_trains === '[7]' && !(this.showImport) ){
                        console.log('olas ?')
                        this.showImport = true
                        this.query_trains = ""
                        this.onUpdate()
                    } else{
                        if( !this.showImport ) {
                            this.onUpdate()
                        }
                    }
                    clearTimeout(timeout_extensions)
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
            importService(){
                API({
                    method: 'GET',
                    url: 'train_template/import/service/' + this.query_trains+'?skip='+this.importSkip+'&limit='+this.importLimit
                })
                    .then((result) => {
                        console.log(result.data)
                        this.query_trains = ''
                        this.onUpdate()
                    }).catch(() => {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: "Modulo de Trenes",
                            text: this.$t('global.error.messages.connection_error')
                        })
                })
            },
            remove () {
                API({
                    method: 'DELETE',
                    url: 'train_templates/' + this.train_template_id
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.onUpdate()
                            this.hideModal()
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: "Modulo de Trenes",
                                text: this.$t('global.error.messages.service_delete')
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
            showModal (train_template_id, train_template_name) {
                this.train_template_id = train_template_id
                this.trainName = train_template_name
                this.$refs['my-modal'].show()
            },
            toManage: function (me) {
                this.$root.$emit('updateTitleTrain', { train_id: me.id })
                this.$router.push('/trains/' + me.id + '/manage_train')
            },
            setPage(page){
                if( page < 1 || page > this.train_pages.length ){
                    return;
                }
                this.pageChosen = page
                this.onUpdate()
            },
            changeStatus(me){

                if( me.train_rail_route.train.name === "BASIC" ){
                    this.$notify({
                        group: 'main',
                        type: 'warning',
                        title: "Modulo de Trenes",
                        text: "Primero debe editar la configuración BASIC"
                    })
                    return
                }

                this.loadingIcons = true

                API.put('/train_template/'+me.id+'/status',{status:!me.status})
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
            },
            onUpdate () {

                this.loading = true
                this.trains = []

                API({
                    method: 'GET',
                    url: 'train_templates?queryCustom='+this.query_trains +
                        '&page=' + this.pageChosen + '&limit=' + this.limit +
                        '&byCompany=' + this.trainCompaniesSelected
                })
                    .then((result) => {
                        this.train_pages = []
                        for( let i=0; i<(result.data.count/this.limit); i++){
                            this.train_pages.push(i+1)
                        }

                        result.data.data.forEach( d=>{
                            d.swap_train_id = d.train_rail_route.train_id
                            d.swap_train_train_class_id = d.train_train_class_id
                            d.swap_train_rail_route_id = d.train_rail_route_id
                        } )
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
</style>
