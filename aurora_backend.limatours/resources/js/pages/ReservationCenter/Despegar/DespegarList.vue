<template>
    <div class="container-fluid">
        <div class="container-fluid vld-parent">
            <loading :active.sync="loading" :can-cancel="false" color="#BD0D12"></loading>

            <div class="row justify-content-end">
                <div class="col-sm-12" style="padding: 0; margin: 0">
                    <button @click="showOptions=!showOptions" class="btn btn-info" type="button"style="line-height: 0px;">
                        Opciones
                        <font-awesome-icon :icon="['fas', 'angle-up']" v-show="showOptions"/>
                        <font-awesome-icon :icon="['fas', 'angle-down']" v-show="!showOptions"/>
                    </button>
                </div>
            </div>
            <div class="row card justify-content-end" v-show="showOptions">
                <div class="col-sm-12">
                    <div class="b-form-group form-group" style="margin-top: 13px;">
                        <div class="form-row">

                            <div class="col-2">
                                <button class="btn btn-success" @click="newFile(0)" type="button" style="line-height: 0px;" :disabled="!(componentsChoosed.length)">
                                    <font-awesome-icon :icon="['fas', 'circle']"/> Nuevo File ({{ componentsChoosedCount }})
                                </button>
                            </div>
                            <div class="col-2">
                                <button class="btn btn-success" @click="addFile()" type="button" style="line-height: 0px;" :disabled="!(componentsChoosed.length)">
                                    <font-awesome-icon :icon="['fas', 'plus']"/> Agregar File({{ componentsChoosedCount }})
                                </button>
                            </div>
                            <div class="col-2">
                                <button v-if="componentsChoosed.length > 0" class="btn btn-info" @click="viewServicesChoosed" type="button" style="line-height: 0px;">
                                    <font-awesome-icon :icon="['fas', 'bars']"/> {{ componentsChoosed.length }} Seleccionados
                                </button>
                            </div>
                            <div class="col-6">
                                <button class="btn btn-info right" @click="homologate()" type="button" style="line-height: 0px;">
                                    <font-awesome-icon :icon="['fas', 'edit']"/> Homologar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row justify-content-end" style="margin-top: 10px;">
                <div class="col-sm-12" style="padding: 0; margin: 0">
                    <button @click="showFilters=!showFilters" class="btn btn-info" type="button"style="line-height: 0px;">
                        Filtros
                        <font-awesome-icon :icon="['fas', 'angle-up']" v-show="showFilters"/>
                        <font-awesome-icon :icon="['fas', 'angle-down']" v-show="!showFilters"/>
                    </button>
                </div>
            </div>
            <div class="row card justify-content-end" v-show="showFilters">

                <div class="col-sm-12">
                    <div class="b-form-group form-group">
                        <div class="form-row">

                            <div class="col-7" style="margin-top: 15px;">
                                <input class="form-control" id="search_custom" type="search" v-model="query_custom"
                                       value="" placeholder="Buscar por nombre, email, código o fecha de inicio">
                            </div>

                            <div class="col-2">
                                <button @click="onUpdate()" class="btn btn-danger btn-search" type="button" :disabled="loading">
                                    <font-awesome-icon :icon="['fas', 'search']"/> Buscar
                                </button>
                            </div>
                            <div class="col-3">
                                <button @click="viewClosed()" class="btn btn-danger btn-search"
                                        type="button" v-if="!loading"
                                        style="float: right; margin-right: 5px;">
                                    <font-awesome-icon v-if="checkViewClosed" :icon="['fas', 'check-square']" />
                                    <font-awesome-icon v-if="!checkViewClosed" :icon="['fas', 'square']" />
                                    Incluir cerrados
                                </button>
                            </div>

                        </div>
                        <div class="clearfix"></div>
                    </div>

                </div>

                <div class="clearfix"></div>
            </div>


            <table-server :columns="table.columns" style="margin-top: 10px" :options="tableOptions" @loaded="onLoaded"
                          :url="urlbookings" class="text-center canSelectText" ref="table">

                <div class="table-actions text-center" slot="info" slot-scope="props" style="padding: 5px">

                    <!--                    :data-id="'pop' + props.row.id"-->
                    <button @click="closeOthersPopovers(props.row)" class="btn btn-info" :id="'pop' + props.row.id"
                            type="button" title="Ver más información de la reserva">
                        <font-awesome-icon :icon="['fas', 'list-alt']"/>
                    </button>

                    <!--                    :data-target="'pop' + props.row.id"-->
                    <b-popover class="canSelectText" :show.sync="props.row.popShow" :target="'pop' + props.row.id" title="Datos de Reserva" triggers="hover focus">
                        <strong>Código de Despegar.com:</strong>
                        <br>{{ props.row.header.code}}<br>
                        <strong>Ofrecido por:</strong> <br>{{ props.row.header.email }}<br>
                        <strong>Fecha de recepción al sistema:</strong><br>
                        {{ props.row.header.created_at}}
                        <strong>Detalle:</strong>
                        <br>{{ props.row.detail}}<br>
                        <strong>Descripción:</strong>
                        <br>{{ props.row.description}}<br>
                        <strong>Modalidad:</strong>
                        <br>{{ props.row.modality}}<br>

                    </b-popover>

                </div>
                <div class="table-date_start" slot="date_start" slot-scope="props" style="font-size: 0.9em">
                    {{props.row.date_from | formatDate}} | {{props.row.date_to | formatDate}}
                </div>
                <div class="table-paxs" slot="paxs" slot-scope="props" style="font-size: 0.9em">
                    <span v-if="props.row.adults>0">{{props.row.adults}} <strong>ADL</strong></span>
                    <span v-if="props.row.children>0">{{props.row.children}} <strong>+ CHD</strong></span>
                    <span v-if="props.row.infants>0">{{props.row.infants}} <strong>+ INF</strong></span>
                </div>
                <div class="table-type" slot="type" slot-scope="props" style="font-size: 0.9em">
                    <span v-if="props.row.type == 'X'">Excursión</span>
                    <span v-if="props.row.type == 'P'">Paquete</span>
                    <span v-if="props.row.type == 'T'">Traslado</span>
                </div>

                <div class="table-accion" slot="actions" slot-scope="props" style="font-size: 0.9em">
                    <button  disabled="true" style="float: left;" class="btn-sm btn-success" v-if="props.row.reserves.length>0" title="Nro.File">
                        {{ props.row.reserves[ props.row.reserves.length - 1 ].file.file_number }}
                    </button>
                    <button class="btn-sm btn-warning" @click="toggleStatus(props.row.id, props.row.status)" v-if="props.row.status" title="PENDIENTE, (Click para cerrar reserva)">
                        P
                    </button>
                    <button class="btn-sm btn-danger" @click="toggleStatus(props.row.id, props.row.status)" v-if="!(props.row.status)" title="CERRADO, (Click para re-abrir reserva)">
                        C
                    </button>
                </div>

                <div class="table-choose" slot="choose" slot-scope="props" style="font-size: 0.9em; cursor: pointer;">
                    <font-awesome-icon :icon="['fas', 'check-circle']" @click="changeChoosed(props.row)" :class="'fa-2x check_'+props.row.choosed"/>
                </div>

            </table-server>

            <b-modal title="Homologar" ref="my-modal-homologate" size="lg">

                <div class="col-md-12 text-center" v-show="loadingModal">
                    <img src="/images/loading.svg" alt="loading"/>
                </div>

                <div class="col-sm-12 canSelectText" v-show="!loadingModal">

                    <div class="row col-12" style="padding-bottom: 15px;">
                        <button type="button" class="right btn btn-danger" @click="viewFormHomologation=!(viewFormHomologation)">
                            <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon> Agregar
                        </button>
                    </div>
                    <div class="row col-12" style="padding-bottom: 15px;" v-show="viewFormHomologation">
                        <div class="col-3">
                            <select type="text" class="form-control" v-model="formHomologation.service_type">
                                <option value="X" selected>Excursión</option>
                                <option value="T">Traslado</option>
                            </select>
                        </div>
                        <div class="col-4">
                            <input type="text" placeholder="Descripción" class="form-control" v-model="formHomologation.description"/>
                        </div>
                        <div class="col-3">
                            <input type="text" placeholder="Código de aurora" class="form-control" v-model="formHomologation.internal_code"/>
                        </div>
                        <div class="col-2">
                            <button type="button" class="btn btn-success" :disabled="loadingModal" @click="saveHomologation()">
                                OK
                            </button>
                        </div>
                    </div>

                    <div>
                        <table class="col-12 VueTables__table table table-striped table-bordered table-hover text-center">
                            <thead>
                            <tr>
                                <th scope="col"><font-awesome-icon :icon="['fas','trash']"></font-awesome-icon></th>
                                <th scope="col">Tipo</th>
                                <th scope="col">Descripción</th>
                                <th scope="col">Código</th>
                            </tr>
                            </thead>
                            <tr v-for="h in homologations">
                                <td class="text-center">
                                    <font-awesome-icon class="cursor-pointer" v-if="!h.trashActive" :icon="['fas','trash']" @click="h.trashActive=true"></font-awesome-icon>
                                    <font-awesome-icon class="cursor-pointer" v-if="h.trashActive" :icon="['fas','trash']" @click="removeHomologation(h)" style="color: red"></font-awesome-icon>
                                </td>
                                <td class="text-center" scope="row">
                                    <span v-if="h.service_type=='X'">Excursión</span>
                                    <span v-if="h.service_type=='T'">Traslado</span>
                                </td>
                                <td class="text-center">{{ h.description }}</td>
                                <td class="text-center">{{ h.internal_code }}</td>
                            </tr>
                        </table>
                    </div>

                </div>

                <div slot="modal-footer">
                    <button @click="hideModal()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
                </div>
            </b-modal>

            <b-modal :title="modalTitle" ref="my-modal" size="lg">

                <div class="col-md-12 text-center" v-show="loadingModal">
                    <img src="/images/loading.svg" alt="loading"/>
                </div>

                <div class="col-sm-12 canSelectText" v-show="!loadingModal">

                    <div>
                        <div :class="'row' + (key%2)" v-show="showComponents" style="margin-bottom: 10px;"
                             v-for="(component, key) in componentsChoosed" >

                            <button class="btn btn-danger" @click="deleteServiceChoosed(key, component)" style="float: right;">
                                <font-awesome-icon :icon="['fas', 'trash']"/>
                            </button>

                            <div class="rooms-table row canSelectText rowTitle rowTitleChoosed-N" >
                                <div class="col-12 my-auto">
                                    <strong >
                                        NUEVO
                                    </strong> |
                                    <span v-if="component.type == 'X'">Excursión</span>
                                    <span v-if="component.type == 'P'">Paquete</span>
                                    <span v-if="component.type == 'T'">Traslado</span>
                                </div>
                            </div>

                            <div class="rooms-table row canSelectText">
                                <div class="col-12 my-auto">
                                    <h2>{{ component.name }}</h2>
                                </div>
                            </div>
                            <div class="rooms-table row canSelectText">
                                <div class="col-3 my-auto">
                                    <strong>Código: </strong>{{ component.code }}
                                </div>
                                <div class="col-4 my-auto">
                                    <strong>Fecha de Creación: </strong>{{component.created_at | formatDate}}
                                </div>
                                <div class="col-5 my-auto">
                                    <strong>Fecha de Inicio/Fin: </strong>{{component.date_from | formatDate}} | {{component.date_to | formatDate}}
                                </div>
                            </div>

                            <div class="rooms-table row canSelectText">
                                <div class="col-3 my-auto">
                                    <span v-if="component.adults>0">{{component.adults}} <strong>ADL</strong></span>
                                    <span v-if="component.children>0">{{component.children}} <strong>+ CHD</strong></span>
                                    <span v-if="component.infants>0">{{component.infants}} <strong>+ INF</strong></span>
                                </div>
                                <div class="col-4 my-auto">
                                    <strong>Detalle: </strong>{{ component.detail }}
                                </div>
                                <div class="col-5 my-auto">
                                    <strong>Pasajero: </strong>{{component.passenger}}
                                </div>
                            </div>

                            <div class="rooms-table row canSelectText">
                                <div class="col-12 my-auto">
                                    <strong>Descripción: </strong>{{ component.description }}
                                </div>
                            </div>

                            <div class="rooms-table row canSelectText">
                                <div class="col-12 my-auto">
                                    <strong>Modalidad: </strong>{{component.modality}}
                                </div>
                            </div>

                        </div>
                    </div>

                </div>

                <div slot="modal-footer">

                    <button class="btn btn-success" @click="newFile(0)" type="button"
                            :disabled="!(componentsChoosed.length)">
                        <font-awesome-icon :icon="['fas', 'circle']"/> Nuevo File
                    </button>
                    <button class="btn btn-success" @click="addFile()" type="button"
                            :disabled="!(componentsChoosed.length)">
                        <font-awesome-icon :icon="['fas', 'plus']"/> Agregar a un File
                    </button>

                    <button @click="hideModal()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
                </div>
            </b-modal>

            <b-modal title="Agregar a un File" centered ref="my-modal-add-to-file" size="md">
                <p class="text-center">Seleccione el file:</p>

                <v-select :options="files"
                          :value="nrofile"
                          label="name" :filterable="false" @search="onSearch"
                          placeholder="Buscar por pasajero o Nº de file"
                          v-model="fileSelected" name="file" id="file" style="height: 35px;">
                    <template slot="option" slot-scope="option">
                        <div class="d-center">
                            {{ option.NROREF }} - {{ option.NOMBRE }}
                        </div>
                    </template>
                    <template slot="selected-option" slot-scope="option">
                        <div class="selected d-center">
                            {{ option.NROREF }} - {{ option.NOMBRE }}
                        </div>
                    </template>
                </v-select>

                <div slot="modal-footer">
                    <button class="btn btn-success" @click="newFile(1)" type="button">
                        <font-awesome-icon :icon="['fas', 'circle']"/> Agregar File
                    </button>
                    <button @click="hideModal()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
                </div>
            </b-modal>

        </div>
    </div>
</template>
<script>
    import TableServer from '../../../components/TableServer'
    import { API } from '../../../api'
    import BFormCheckbox from 'bootstrap-vue/es/components/form-checkbox/form-checkbox'
    import BDropDown from 'bootstrap-vue/es/components/dropdown/dropdown'
    import BPopover from 'bootstrap-vue/es/components/popover/popover'
    import BDropDownItemButton from 'bootstrap-vue/es/components/dropdown/dropdown-item-button'
    import BModal from 'bootstrap-vue/es/components/modal/modal'
    import vSelect from 'vue-select'
    import 'vue-select/dist/vue-select.css'
    import datePicker from 'vue-bootstrap-datetimepicker'
    import Loading from 'vue-loading-overlay'
    import Vue from 'vue'
    import { Event } from 'vue-tables-2'
    Vue.use(Event)

    let componentsChoosed = []

    export default {
        components: {
            'table-server': TableServer,
            'b-popover': BPopover,
            BFormCheckbox,
            'b-dropdown': BDropDown,
            'b-dropdown-item-button': BDropDownItemButton,
            BModal,
            vSelect,
            datePicker,
            Loading
        },
        data: () => {
            return {
                loading:false,
                viewFormHomologation:false,
                query_custom:'',
                loadingModal:false,
                showFilters:true,
                showOptions:true,
                showCustomers:false,
                showComponents:false,
                homologations:[],
                files:[],
                fileSelected:[],
                nrofile:null,
                modalTitle:'',
                contentModal:'',
                bookingId:'',
                urlbookings: '',
                date_type_id:"",
                status_id:'',
                final_check_id:'',
                checkboxs:[],
                componentsChoosed:[],
                componentsChoosedCount:0,
                checkViewClosed:0,
                table: {
                    columns: ["info","code","name","date_start","paxs","passenger","type", 'actions',"choose"]
                },
                form:{
                    service_id:null,
                    id:null
                },
                viewBooking : {
                    customers : {
                        customer : ''
                    },
                    components : {
                        component : []
                    }
                },
                tmpBookings : [],
                formHomologation : {
                    service_type : 'X',
                    description : '',
                    internal_code : ''
                }
            }
        },
        mounted () {
            this.$i18n.locale = localStorage.getItem('lang')

            let search_custom = document.getElementById('search_custom')
            let timeout_search
            search_custom.addEventListener('keydown', () => {
                clearTimeout(timeout_search)
                timeout_search = setTimeout(() => {
                    this.onUpdate()
                    clearTimeout(timeout_search)
                }, 1000)
            })

            if (localStorage.getItem('servicesDespegar')) {
                try {
                    componentsChoosed = JSON.parse(localStorage.getItem('servicesDespegar'))
                    this.componentsChoosed = componentsChoosed
                    this.componentsChoosedCount = componentsChoosed.length
                } catch(e) {
                    localStorage.removeItem('servicesDespegar')
                }
            }

            this.searchHomologations()

        },
        created () {

            this.urlbookings = '/api/channel/despegar/list?filterBy='+this.checkViewClosed + '&queryCustom='+this.query_custom

            this.$parent.$parent.$on('langChange', (payload) => {
                this.onUpdate()
            })
            Event.$on('vue-tables.loaded', function (data) {
                // console.log('My event has been triggered', data)
                this.loading = false
                // console.log(this.loading)
            })
        },
        computed: {
            tableOptions: function () {
                return {
                    headings: {
                        info: 'Info',
                        code: 'Código',
                        name: 'Nombre',
                        date_start: 'Fecha de inicio',
                        paxs: 'Paxs',
                        passenger: 'Pasajero',
                        type: 'Tipo',
                        actions: this.$i18n.t('global.table.actions'),
                        choose: "Elegir"
                    },
                    sortable: ['id'],
                    filterable: false,
                    responseAdapter: function(data){
                        data.data.forEach( _data=>{
                            let c=0
                            componentsChoosed.forEach( rate=>{
                                if( _data.id == rate.id ){
                                    _data.choosed = true
                                    c++
                                }
                            } )
                            if(c==0){
                                _data.choosed = false
                            }
                        } )
                        return data
                    }
                }
            }
        },
        methods: {
            removeHomologation(h) {
                API.delete('/channel/despegar/homologation/'+h.id)
                    .then((result) => {
                        if(result.data.success){
                            this.$notify({
                                group: 'main',
                                type: 'success',
                                title: "Homologación Despegar",
                                text: 'Eliminado correctamente'
                            })
                            this.searchHomologations()
                        }
                        this.loadingModal = false
                    }).catch(() => {
                    this.loadingModal = false
                })
            },
            saveHomologation(){

                if( this.formHomologation.description == '' ||
                    this.formHomologation.internal_code == '' ||
                    this.formHomologation.service_type == '' ){
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: "Error",
                        text: 'Formulario incompleto'
                    })
                    return
                }

                API.post('/channel/despegar/homologations', this.formHomologation)
                    .then((result) => {
                        if(result.data.success){
                            this.$notify({
                                group: 'main',
                                type: 'success',
                                title: "Homologación Despegar",
                                text: 'Guardado correctamente'
                            })
                            this.searchHomologations()
                        }
                        this.loadingModal = false
                    }).catch(() => {
                    this.loadingModal = false
                })
            },
            searchHomologations(){
                this.loadingModal = true
                API.get('/channel/despegar/homologations')
                    .then((result) => {
                        if(result.data.success){
                            result.data.data.forEach( h =>{
                                h.trashActive = false
                            } )
                            this.homologations = result.data.data
                        }
                        this.loadingModal = false
                    }).catch(() => {
                        this.loadingModal = false
                })
            },
            homologate(){
                this.$refs['my-modal-homologate'].show()
            },
            viewClosed(){
                this.checkViewClosed = (this.checkViewClosed==0) ? 1 : 0
                this.onUpdate()
            },
            onSearch(search, loading) {
                loading(true)
                API.post('/channel/tourcms/booking/files', { query : search })
                    .then((result) => {
                        loading(false)
                        this.files = result.data
                    }).catch(() => {
                    loading(false)
                })
            },
            toggleStatus(id, status) {
                this.loading = true
                API.post('/channel/despegar/'+id+'/status', { status : status })
                    .then((result) => {
                        if(result.data.success){
                            this.onUpdate()
                        }
                        this.loading = false
                    }).catch(() => {
                    this.loading = false
                })
            },
            saveLocalStorage(){
                this.componentsChoosed = componentsChoosed
                const parsed = JSON.stringify(componentsChoosed);
                this.componentsChoosedCount = componentsChoosed.length
                localStorage.setItem('servicesDespegar', parsed);
                this.onUpdate()
            },
            onLoaded ($event) {
                console.log('My event caught in global event bus', $event)
            },
            setDateFrom (e) {
                this.$refs.datePickerTo.dp.minDate(e.date)
            },
            onUpdate () {
                this.loading = true
                this.urlbookings = '/api/channel/despegar/list?filterBy='+this.checkViewClosed + '&queryCustom='+this.query_custom

                this.$refs.table.$refs.tableserver.refresh()
                this.loading = false

            },
            viewServicesChoosed() {

                this.modalTitle = 'Servicios Elegidos para crear/agregar file'
                this.loadingModal = true
                this.showModal()
                this.loadingModal = false
                this.showComponents = false
                this.showComponents = true
            },
            closeOthersPopovers: function () {
                this.$root.$emit('bv::hide::popover')
            },
            showModal () {
                this.$refs['my-modal'].show()
            },
            hideModal () {
                this.$refs['my-modal'].hide()
                this.$refs['my-modal-add-to-file'].hide()
                this.$refs['my-modal-homologate'].hide()
            },
            changeChoosed(me){
                let count_r = 0
                componentsChoosed.forEach( (rate,k) =>{
                    if( me.id == rate.id ){
                        count_r++
                        componentsChoosed.splice(k,1)
                    }
                } )
                if( !count_r ){
                    componentsChoosed.push(me)
                }

                document.getElementsByClassName('VuePagination__pagination-item active')[0].click()
                this.saveLocalStorage()
            },

            newFile : function(typeCreation){

                let nrofile=''
                let services = []
                let err=0

                if( typeCreation == 1 ){
                    if(this.fileSelected.NROREF == undefined) {
                        err++
                    } else {
                        nrofile=this.fileSelected.NROREF
                    }
                }

                if( err==0 ){
                    componentsChoosed.forEach( comp =>{
                        services.push({
                            id : comp.id,
                            start_date : comp.date_from,
                            end_date : comp.date_to,
                            paxdes : comp.passenger,
                            total : 0,
                            canadu : comp.adults,
                            canchd : comp.children,
                            caninf : comp.infants,
                        })

                    } )

                    if( err == 0 ){

                        this.loading = true
                        this.loadingModal = true
                        let data = {
                            services : services,
                            nrofile : nrofile
                        }
                        API({
                            method: 'post',
                            url: '/channel/despegar/reserve/',
                            data: data
                        })
                            .then((result) => {
                                console.log(result)
                                this.loadingModal = false
                                this.loading = false

                                if( result.data.success ){
                                    this.$notify({
                                        group: 'main',
                                        type: 'success',
                                        title: "Satisfactorio",
                                        text: 'Reserva generada con el N°: '+ result.data.detail
                                    })
                                    componentsChoosed = []
                                    this.componentsChoosed = []
                                    this.componentsChoosedCount = 0
                                    this.onUpdate()
                                    localStorage.setItem('servicesDespegar', '')
                                    this.hideModal()
                                } else {
                                    this.$notify({
                                        group: 'main',
                                        type: 'error',
                                        title: 'Error en el ws',
                                        text: result.data.detail
                                    })
                                }

                            }).catch((e) => {
                                console.log(e)
                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: 'Error al generar la reserva',
                                    text: this.$t('error.messages.connection_error')
                                })
                        })
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Error al generar la reserva',
                            text: 'No se encontró el código de servicio en uno o más servicios'
                        })
                    }
                } else {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Error al agregar a un File',
                        text: 'Por favor seleccione el file a agregar'
                    })
                }
            },
            addFile(){
                this.$refs['my-modal'].hide()
                this.$refs['my-modal-add-to-file'].show()
            },
            deleteServiceChoosed : function(key, component){
                this.checkboxs['check_'+component.component_id] = false
                componentsChoosed.splice(key,1)
                this.saveLocalStorage()
            },
            getSizeObj : function(obj) {
                var size = 0, key;
                for (key in obj) {
                    if (obj.hasOwnProperty(key)) size++;
                }
                return size;
            }
        },
        filters: {
            formatDate: function (_date) {
                if( _date == undefined ){
                    // console.log('fecha no parseada: ' + _date)
                    return;
                }
                let secondPartDate = ''

                if( _date.length > 10 ){
                    secondPartDate = _date.substr(10, _date.length )
                    _date = _date.substr(0,10)
                }

                _date = _date.split('-')
                _date = _date[2] + '/' + _date[1] + '/' + _date[0]
                return _date + secondPartDate
            },
            capitalize: function (value) {
                if (!value) return ''
                value = value.toString().toLowerCase()
                return value.charAt(0).toUpperCase() + value.slice(1)
            }
        }
    }
</script>
<style>
    .custom-control-input:checked ~ .custom-control-label::before {
        border-color: #3ea662;
        background-color: #3a9d5d;
    }
    .v-select input{
        height: 25px;
    }
    .btn-search{
        margin-top: 19px;
        float: right;
    }
    .modal-backdrop {
        background-color: #00000052;
    }
    .canSelectText{
        user-select: text;
    }
    .row0{
        background: #e3fcff;
        padding: 5px;
        border-radius: 9px;
        line-height: 16px;
        color: #615555;
        border: solid 1px #8a9b9b;
        font-size: 11px;
    }
    .row1{
        background: #ffeeff;
        padding: 5px;
        border-radius: 9px;
        line-height: 16px;
        color: #615555;
        border: solid 1px #8a9b9b;
        font-size: 11px;
    }
    .rooms-table{
        padding: 5px;
    }
    .rowTitle{
        text-align: center;
        background: white;
        border: solid 1px #8a9b9b;
        margin: 3px -6px;
    }
    .rowTitleChoosed-N{
        background: #ddffd2;
    }
    .rowTitleChoosed-C{
        background: #ffba66;
    }
    .cursor-pointer{
        cursor: pointer;
    }
</style>
