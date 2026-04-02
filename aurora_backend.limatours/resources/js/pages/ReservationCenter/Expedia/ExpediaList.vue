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
                                    <font-awesome-icon :icon="['fas', 'plus']"/> Agregar File ({{ componentsChoosedCount }})
                                </button>
                            </div>
                            <div class="col-2" v-if="componentsChoosed.length > 0">
                                <button class="btn btn-info" @click="viewServicesChoosed" type="button" style="line-height: 0px;">
                                    <font-awesome-icon :icon="['fas', 'bars']"/> {{ componentsChoosed.length }} Seleccionados
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

                    </b-popover>

                </div>
                <div class="table-date_start" slot="date_start" slot-scope="props" style="font-size: 0.9em">
                    {{props.row.date_start | formatDate}}
                </div>
                <div class="table-created_at" slot="created_at" slot-scope="props" style="font-size: 0.9em">
                    {{props.row.created_at | formatDate}}
                </div>
                <div class="table-type" slot="type" slot-scope="props" style="font-size: 0.9em">
                    <span v-if="props.row.type == 'X'">Excursión</span>
                    <span v-if="props.row.type == 'P'">Paquete</span>
                    <span v-if="props.row.type == 'T'">Traslado</span>
                     - {{props.row.ticket_type }}
                </div>

                <div class="table-booking_state" slot="booking_state" slot-scope="props" style="font-size: 0.9em">
                    <span class="btn-info" v-if="props.row.booking_state=='N'" style="padding: 5px 10px;">
                        Nuevo
                    </span>
                    <span class="btn-warning" v-if="props.row.booking_state=='C'" style="padding: 5px 10px;">
                        Cancelado
                    </span>
                </div>

                <div class="table-accion" slot="actions" slot-scope="props" style="font-size: 0.9em">
                    <button disabled="true" style="float: left;" class="btn-sm btn-success" v-if="props.row.reserves.length>0" title="Nro.File">
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

                            <div :class="'rooms-table row canSelectText rowTitle rowTitleChoosed-'+component.booking_state" >
                                <div class="col-12 my-auto">
                                    <strong v-if="component.booking_state=='N'" >
                                        NUEVO
                                    </strong>
                                    <strong v-if="component.booking_state=='C'" >
                                        CANCELADO
                                    </strong> |
                                    <span v-if="component.type == 'X'">Excursión</span>
                                    <span v-if="component.type == 'P'">Paquete</span>
                                    <span v-if="component.type == 'T'">Traslado</span>
                                    - {{component.ticket_type }}
                                </div>
                            </div>

                            <div class="rooms-table row canSelectText">
                                <div class="col-2 my-auto">
                                    <strong>Código: </strong>{{ component.code }}
                                </div>
                                <div class="col-6 my-auto">
                                    <strong>Fecha de Creación: </strong>{{component.created_at | formatDate}}
                                </div>
                                <div class="col-4 my-auto">
                                    <strong>Fecha de Inicio: </strong>{{component.date_start | formatDate}}
                                </div>
                            </div>

                            <div class="rooms-table row canSelectText">
                                <div class="col-2 my-auto">
                                    <strong>Paxs: </strong>{{ component.paxs }}
                                </div>
                                <div class="col-6 my-auto">
                                    <strong>Pasajero: </strong>{{component.passenger}}
                                </div>
                                <div class="col-4 my-auto">
                                    <strong>email: </strong>{{component.passenger_email}}
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
        query_custom:'',
        loadingModal:false,
        showFilters:true,
        showOptions:true,
        showCustomers:false,
        showComponents:false,
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
          columns: ["info","code","date_start","paxs","passenger","passenger_email","booking_state","type", 'actions',"choose"]
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
        tmpBookings : []
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

      if (localStorage.getItem('servicesExpedia')) {
        try {
          componentsChoosed = JSON.parse(localStorage.getItem('servicesExpedia'))
          this.componentsChoosed = componentsChoosed
            this.componentsChoosedCount = componentsChoosed.length
        } catch(e) {
          localStorage.removeItem('servicesExpedia')
        }
      }
    },
    created () {

      this.urlbookings = '/api/channel/expedia/list?filterBy='+this.checkViewClosed + '&queryCustom='+this.query_custom

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
            date_start: 'Fecha de inicio',
            paxs: 'Paxs',
            passenger: 'Pasajero',
            passenger_email: 'Email',
            booking_state: 'Estado',
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
        API.post('/channel/expedia/'+id+'/status', { status : status })
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
        localStorage.setItem('servicesExpedia', parsed);
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
        this.urlbookings = '/api/channel/expedia/list?filterBy='+this.checkViewClosed + '&queryCustom='+this.query_custom

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
                  type : comp.type,
                  code : comp.booking_code,
                  start_date : comp.date_start,
                  end_date : comp.date_start,
                  paxdes : comp.passenger,
                  total : 0,
                  canadu : comp.paxs,
                  canchd : 0,
                  caninf : 0,
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
                  url: '/channel/expedia/reserve/',
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
                          localStorage.setItem('servicesExpedia', '')
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
</style>
