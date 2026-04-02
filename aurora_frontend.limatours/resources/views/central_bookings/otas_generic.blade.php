@extends('layouts.app')
@section('content')
    <section class="page-central">
        <div class="container">
            <div class="row">
                <div id="_overlay"></div>

                <nav-central-bookings></nav-central-bookings>
                <div class="col-12 mb-5">
                    <form class="form">
                        <!-- Opciones -->
                        <div class="mt-4 mb-3 d-flex justify-content-around" v-show="showOptions">
                            <div class="">
                                <button class="btn btn-success E" @click="newFile(0)" type="button" style="line-height: 0px;" :disabled="!(componentsChoosed.length)">
                                    <i class="fa fa-circle mr-2"></i> Nuevo File (@{{ componentsChoosedCount }})
                                </button>
                            </div>
                            <div class="">
                                <button class="btn btn-success" @click="addFile()" type="button" style="line-height: 0px;" :disabled="!(componentsChoosed.length)">
                                    <i class="fa fa-plus mr-2"></i> Agregar File (@{{ componentsChoosedCount }})
                                </button>
                            </div>
                            <div class="" v-if="componentsChoosed.length > 0">
                                <button class="btn btn-info" @click="viewServicesChoosed" type="button" style="line-height: 0px;">
                                    <i class="fa fa-bars mr-2"></i> @{{ componentsChoosed.length }} Seleccionados
                                </button>
                            </div>
                        </div>
                        <hr>
                        <!-- Filtros -->
                        <div class="" v-show="showFilters">
                            <div class="d-flex my-5">
                                <div class="form-group mx-3 col">
                                    <input class="form-control" id="search_custom" type="search" v-model="query_custom"
                                           value="" placeholder="Buscar por nombre, email, código o fecha de inicio">
                                </div>
                                <div class="form-group mx-3 col">
                                    <select class="form-control" id="sel1"  v-model="ota">
                                        <option value="">Seleccione OTA</option>
                                        <option v-for="ota in otas" :value="ota.id">@{{ ota.name  }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-inline">
                                <button @click="viewClosed()" class="btn"
                                        type="button" :disabled="loading">
                                    <i class="fa fa-check mr-2" v-show="checkViewClosed"></i>
                                    <i class="fa fa-square mr-2" v-show="!checkViewClosed"></i>
                                    <span style="color: #890005;font-weight: 600;font-size: 11px;"> Incluir cerrados </span>
                                </button>
                            </div>
                            <div class="form-inline">
                                <button @click="viewReservesPassed()" class="btn"
                                        type="button" :disabled="loading">
                                    <i class="fa fa-check mr-2" v-show="checkViewPassed"></i>
                                    <i class="fa fa-square mr-2" v-show="!checkViewPassed"></i>
                                    <span style="color: #890005;font-weight: 600;font-size: 11px;"> Incluir Reservas Pasadas </span>
                                </button>
                            </div>
                            <div class="form-inline">
                                <button @click="viewMultiDays()" class="btn"
                                        type="button" :disabled="loading">
                                    <i class="fa fa-check mr-2" v-show="checkViewMultiDays"></i>
                                    <i class="fa fa-square mr-2" v-show="!checkViewMultiDays"></i>
                                    <span style="color: #890005;font-weight: 600;font-size: 11px;"> Mostrar Solo Reservas Multi Days </span>
                                </button>
                            </div>
                            <div class="text-right">
                                <button @click="onUpdate()" class="btn btn-primary w-25" type="button" :disabled="loading">
                                    <i class="fa fa-search mr-2"></i> Buscar
                                </button>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </form>
                </div>

                <div class="col-12 my-5" style="margin-top: 20px;">
                    <div class="right">
                        <select class="form-control-lg" v-model="limit" @change="onUpdate()">
                            <option :value="l" v-for="l in limits">@{{ l }}</option>
                        </select> Registros.
                    </div>
                    <div class="table-responsive-sm">
                        <table class="table text-center canSelectText">
                            <thead>
                            <tr>
                                <th scope="col" class="text-muted cursor-pointer">
                                    OTA
                                </th>
                                <th scope="col" class="text-muted cursor-pointer">
                                    Booking ID
                                </th>
                                <th scope="col" class="text-muted cursor-pointer">
                                    Fecha de inicio
                                </th>
                                <th scope="col" class="text-muted cursor-pointer">
                                    Paxs
                                </th>
                                <th scope="col" class="text-muted cursor-pointer">
                                    Pasajero
                                </th>
                                <th scope="col" class="text-muted cursor-pointer">
                                    Email
                                </th>
                                <th scope="col" class="text-muted cursor-pointer">
                                    Estado
                                </th>
                                <th scope="col" class="text-muted cursor-pointer" >
                                    Código Aurora
                                </th>
                                <th scope="col" class="text-muted cursor-pointer">
                                    Tipo
                                </th>
                                <th scope="col" class="text-muted">Acciones</th>
                                <th scope="col" class="text-muted">Elegir</th>
                                <th scope="col" class="text-muted">N°File</th>
                            </tr>
                            </thead>
                            <tbody class="text-center">
                            <tr v-for="booking in bookings">
                                <td class="td-body">@{{ booking.header.ota.name}}</td>
                                <td class="td-body">@{{ booking.code}}</td>
                                <td class="td-body">@{{ booking.date_start | formatDate}}</td>
                                <td class="td-body">@{{ booking.paxs}}</td>
                                <td class="td-body">@{{ booking.passenger}}</td>
                                <td class="td-body">@{{ booking.passenger_email}}</td>
                                <td class="td-body">
                                    <template v-if="flag_status != booking.id">
                                        <span class="btn-estado btn" v-on:click="flag_status = booking.id"
                                            v-if="booking.booking_state=='N'">
                                            Nuevo
                                        </span>
                                        <span class="btn-estado btn" v-on:click="flag_status = booking.id"
                                            v-if="booking.booking_state=='C'">
                                            Cancelado
                                        </span>
                                    </template>
                                    <template v-if="flag_status == booking.id">
                                        <v-select class="form-control px-3 p-0" style="height:auto; width:150px;" v-model="flag_booking_status"
                                            :options="booking_status" label="name" v-on:input="saveBookingStatus(booking.id)">
                                        </v-select>
                                    </template>
                                </td>
                                <td class="td-body">
                                    <span class="btn-estado" v-if="(booking.package_id!==null && booking.package_id!=0) || booking.service!==null">
                                       <template v-if="(booking.reserves.length>0 && booking.package != null) || booking.service!==null || !validationBookingDate(booking)">
                                           @{{ booking.aurora_code }}
                                       </template>
                                       <a id="move_package" href="#" @click="movePackageDetails(booking)" target="_blank" v-else>@{{ booking.aurora_code }} <br>Ir al paquete</a>
                                    </span>
                                    <span class="btn-estado-danger btn-estado" style="background-color: #ea8181 !important;"
                                          title="Asegúrese que el código de este servicio esté creado en el backend" v-else>
                                       @{{ booking.aurora_code }}
                                    </span>
                                </td>
                                <td class="td-body">
                                    <span v-if="booking.type == 'X'">Excursión</span>
                                    <span v-if="booking.type == 'P'">Paquete</span>
                                    <span v-if="booking.type == 'T'">Traslado</span>
                                    - @{{booking.ticket_type }}
                                </td>
                                <td>
                                    <button class="btn-sm btn-info" @click="toggleStatus(booking.id, booking.status)" v-if="booking.status" title="PENDIENTE, (Click para cerrar reserva)">
                                        P
                                    </button>
                                    <button class="btn-sm btn-info" @click="toggleStatus(booking.id, booking.status)" v-if="!(booking.status)" title="CERRADO, (Click para re-abrir reserva)">
                                        C
                                    </button>
                                </td>
                                <td class="td-body">
                                    <i @click="changeChoosed(booking)" :class="'fa fa-check-circle fa-2x check_' + booking.choosed" v-if="booking.package_id == null || booking.package_id == 0"></i>
                                </td>
                                <td class="td-body">
                                    <label disabled="true" style="float: left;" class="btn btn-xs btn-nrofile" v-if="booking.reserves.length>0" title="Nro.File">
                                        @{{ booking.reserves[ booking.reserves.length - 1 ].file.file_number }}
                                    </label>
                                    <span v-else>-</span>
                                </td>
                            </tr>
                            </tbody>


                        </table>

                        <nav aria-label="page navigation" v-if="booking_pages>1">
                            <ul class="pagination d-flex justify-content-center">
                                <li :class="{'page-item':true,'disabled':(pageChosen==1)}"
                                    @click="setPage(1)">
                                    <a class="page-link" href="javascript:;">Primero</a>
                                </li>

                                <li :class="{'page-item':true,'disabled':(pageChosen==1)}"
                                    @click="setPage(pageChosen-1)">
                                    <a class="page-link" href="javascript:;">Anterior</a>
                                </li>

                                <li v-for="(page, p) in booking_pages" @click="setPage(page)"
                                    v-if="show_pages[p]"
                                    :class="{'page-item':true,'active':(page==pageChosen) }">
                                    <a class="page-link" href="javascript:;">@{{ page }}</a>
                                </li>

                                <li :class="{'page-item':true,'disabled':(pageChosen==booking_pages)}"
                                    @click="setPage(pageChosen+1)">
                                    <a class="page-link" href="javascript:;">Siguiente</a>
                                </li>

                                <li :class="{'page-item':true,'disabled':(pageChosen==booking_pages)}"
                                    @click="setPage(booking_pages)">
                                    <a class="page-link" href="javascript:;">Último</a>
                                </li>
                            </ul>
                        </nav>

                        <div class="tbl--cotizacion__content" v-if="bookings.length==0">
                            <div class="row no-gutters align-items-center">
                                <div class="col px-12 text-center">
                                    Ninguna reserva para mostrar
                                </div>
                            </div>
                        </div>

                    </div>
                </div>


                <b-modal :title="modalTitle" ref="my-modal" size="lg" class="modal-central">

                    <div class="col-md-12 text-center" v-show="loadingModal">
                        <i class="fa fa-spinner fa-spin"></i>
                    </div>

                    <div class="col-sm-12 canSelectText" v-show="!loadingModal">
                        <div>
                            <div :class="'row' + (key%2)" v-show="showComponents" style="margin-bottom: 30px;"
                                 v-for="(component, key) in componentsChoosed" >

                                <button class="btn btn-danger" @click="deleteServiceChoosed(key, component)" style="float: right;">
                                    <i class="far fa-trash-alt"></i>
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
                                        - @{{component.ticket_type }}
                                    </div>
                                </div>

                                <div class="rooms-table row canSelectText">
                                    <div class="col-2 my-auto">
                                        <strong>Código: </strong>@{{ component.code }}
                                    </div>
                                    <div class="col-6 my-auto">
                                        <strong>Fecha de Creación: </strong>@{{component.created_at | formatDate}}
                                    </div>
                                    <div class="col-4 my-auto">
                                        <strong>Fecha de Inicio: </strong>@{{component.date_start | formatDate}}
                                    </div>
                                </div>

                                <div class="rooms-table row canSelectText">
                                    <div class="col-2 my-auto">
                                        <strong>Paxs: </strong>@{{ component.paxs }}
                                    </div>
                                    <div class="col-6 my-auto">
                                        <strong>Pasajero: </strong>@{{component.passenger}}
                                    </div>
                                    <div class="col-4 my-auto">
                                        <strong>email: </strong>@{{component.passenger_email}}
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-5">
                        <button class="btn btn-success" @click="newFile(0)" type="button"
                                :disabled="!(componentsChoosed.length)">
                            <i class="fa fa-circle mr-2"></i> Nuevo File
                        </button>
                        <button class="btn btn-success" @click="addFile()" type="button"
                                :disabled="!(componentsChoosed.length)">
                            <i class="fa fa-plus mr-2"></i> Agregar a un File
                        </button>

                        <button @click="hideModal()" class="btn btn-cancelar w-25">Cancelar</button>
                    </div>

                    <div slot="modal-footer">

                    </div>
                </b-modal>

                <b-modal title="Agregar a un File" centered ref="my-modal-add-to-file" size="md" class="modal-central">
                    <h1> <i class="far fa-calendar-plus mr-2"></i>Agregar a un File</h1>
                    <hr>
                    <p class="">Seleccione el file:</p>

                    <v-select class="form-control mb-4"
                              :options="files"
                              :value="nrofile"
                              label="name" :filterable="false" @search="onSearch"
                              placeholder="Buscar por pasajero o Nº de file"
                              v-model="fileSelected" name="file" id="file" style="padding-top: 5px;">
                        <template slot="option" slot-scope="option">
                            <div class="d-center">
                                @{{ option.NROREF }} - @{{ option.NOMBRE }}
                            </div>
                        </template>
                        <template slot="selected-option" slot-scope="option">
                            <div class="selected d-center">
                                @{{ option.NROREF }} - @{{ option.NOMBRE }}
                            </div>
                        </template>
                    </v-select>
                    <div class="d-flex justify-content-between align-items-center mt-5">
                        <button class="btn btn-success" @click="newFile(1)" type="button">
                            <i class="fa fa-plus mr-2"></i> Agregar File
                        </button>
                        <button @click="hideModal()" class="btn btn-cancelar w-25">Cancelar</button>
                    </div>

                    <div slot="modal-footer">

                    </div>
                </b-modal>

            </div>
        </div>
    </section>
@endsection
@section('js')
    <script>
        let componentsChoosed = []
        new Vue({
            el: '#app',
            data: {
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
                checkViewPassed:false,
                checkViewMultiDays:false,
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
                bookings : [],
                booking_pages : 0,
                pageChosen : 1,
                limits : [5, 10, 15, 20, 25, 30],
                limit : 15,
                filter_code:'',
                filter_date_start:'',
                filter_paxs:'',
                filter_passenger:'',
                filter_passenger_email:'',
                filter_booking_state:'',
                filter_booking_type:'',
                filter_aurora_code:'',
                _filter:'',
                _order:'',
                ota:"",
                otas:[],
                view_pages: 15,
                show_pages: {},
                pages: 0,
                page: 0,
                booking_status: [
                    { code: 'N', name: 'Nuevo' },
                    { code: 'C', name: 'Cancelado'},
                ],
                flag_booking_status: {},
                flag_status: false,
            },
            created: function () {
            },
            mounted() {
                if (localStorage.getItem('servicesExpedia')) {
                    try {
                        componentsChoosed = JSON.parse(localStorage.getItem('servicesExpedia'))
                        this.componentsChoosed = componentsChoosed
                        this.componentsChoosedCount = componentsChoosed.length
                    } catch(e) {
                        localStorage.removeItem('servicesExpedia')
                    }
                }
                this.onUpdate()
                this.getOtas()
            },
            computed: {
            },
            methods: {
                saveBookingStatus: function (booking_id) {
                    console.log(this.flag_booking_status)

                    this.loading = true
                    axios.post('/api/generic_otas/'+booking_id+'/status_external', this.flag_booking_status)
                        .then((result) => {
                            if(result.data.success){
                                this.flag_status=''
                                this.onUpdate()
                            }
                            this.loading = false
                        }).catch(() => {
                        this.loading = false
                    })
                },
                validatePagination: function () {
                    this.view_pages = 15
                    this.pages = this.booking_pages
                    this.page = this.pageChosen

                    for(let p=0;p<this.pages;p++)
                    {
                        this.show_pages[p] = false
                    
                        if(this.page < 5)
                        {
                            if(this.view_pages > 0)
                            {
                                this.view_pages -= 1
                                this.show_pages[p] = true
                            }
                        }
                        else
                        {
                            if(this.page >= (this.pages - (this.view_pages) / 2))
                            {
                                if(p >= (this.pages - this.view_pages))
                                {
                                    this.show_pages[p] = true
                                }
                            }
                            else
                            {
                                if(p >= parseFloat(this.page - parseFloat(this.view_pages / 2)) && p <= parseFloat(this.page + parseFloat(this.view_pages / 2)))
                                {
                                    this.show_pages[p] = true
                                }
                            }
                        }
                    }
                },
                validationBookingDate:function (booking){
                    console.log(moment(booking.date_start).format('YYYY-MM-DD'))
                    console.log(moment().format('YYYY-MM-DD'))
                    if(moment(booking.date_start).format('YYYY-MM-DD') >= moment().format('YYYY-MM-DD') ){
                        return true
                    }
                    return false
                },
                getOtas:function (){
                        axios.get('api/otas').then((response)=>{

                            this.otas = response.data
                        }).catch((e)=>{
                            console.log(e)
                        })
                },
                movePackageDetails:function(reservation){

                    var fecha1 = moment(reservation.date_start);
                    var fecha2 = moment(reservation.date_end);


                    let persons = {
                            adults: reservation.paxs,
                            child_with_bed: 0,
                            child_without_bed: 0,
                            age_child: [
                                {
                                    age: 1,
                                },
                            ],
                        }
                    let data = {
                        lang: localStorage.getItem('lang'),
                        date: reservation.date_start,
                        quantity_persons: persons,
                        type_service: 1,
                        date_to_days:fecha2.diff(fecha1, 'days') + 1,
                        package_ids: [reservation.package_id],

                    }
                    let data_ota_generic ={
                        generic_service_id:reservation.id,
                        passenger:{
                            name:reservation.passenger,
                            email:reservation.email
                        },
                        quote_id:null
                    }
                    localStorage.setItem('parameters_ota_generic', JSON.stringify(data_ota_generic))
                    localStorage.setItem('parameters_packages_details', JSON.stringify(data))
                    let a = document.getElementById('move_package')
                    a.href = baseURL + 'package-details'

                },
                filterBy(_filter){
                    if( _filter == 'code' ){
                        this.filter_code = !(this.filter_code)
                        this._order = this.filter_code
                    }
                    if( _filter == 'date_start' ){
                        this.filter_date_start = !(this.filter_date_start)
                        this._order = this.filter_date_start
                    }
                    if( _filter == 'paxs' ){
                        this.filter_paxs = !(this.filter_paxs)
                        this._order = this.filter_paxs
                    }
                    if( _filter == 'passenger' ){
                        this.filter_passenger = !(this.filter_passenger)
                        this._order = this.filter_passenger
                    }
                    if( _filter == 'passenger_email' ){
                        this.filter_passenger_email = !(this.filter_passenger_email)
                        this._order = this.filter_passenger_email
                    }
                    if( _filter == 'booking_state' ){
                        this.filter_booking_state = !(this.filter_booking_state)
                        this._order = this.filter_booking_state
                    }
                    if( _filter == 'booking_type' ){
                        this.filter_booking_type = !(this.filter_booking_state)
                        this._order = this.filter_booking_state
                    }
                    if( _filter == 'aurora_code' ){
                        this.filter_aurora_code = !(this.filter_aurora_code)
                        this._order = this.filter_aurora_code
                    }
                    this._filter = _filter
                    this.onUpdate()
                },
                setPage(page){
                    if( page < 1 || page > this.booking_pages ){
                        return;
                    }
                    this.pageChosen = page
                    this.onUpdate()
                },
                viewClosed(){
                    this.checkViewClosed = (this.checkViewClosed==0) ? 1 : 0
                    this.onUpdate()
                },
                viewReservesPassed:function(){
                    this.checkViewPassed = !this.checkViewPassed
                    this.onUpdate()
                },
                viewMultiDays:function(){
                    this.checkViewMultiDays = !this.checkViewMultiDays
                    this.onUpdate()
                },
                onSearch(search, loading) {
                    loading(true)
                    axios.post('/api/channel/tourcms/booking/files', { query : search })
                        .then((result) => {
                            loading(false)
                            this.files = result.data
                        }).catch(() => {
                        loading(false)
                    })
                },
                toggleStatus(id, status) {
                    this.loading = true
                    axios.post('/api/generic_otas/'+id+'/status', { status : status })
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
                onUpdate () {

                    this.loading = true
                    this.urlbookings = 'api/generic_otas/list?filterBy='+this.checkViewClosed +
                        '&queryCustom='+this.query_custom+'&page='+this.pageChosen+'&limit='+this.limit+
                        '&filter='+this._filter+'&order='+this._order+'&ota='+this.ota+'&reserve_passed='+this.checkViewPassed+'&multi_days='+this.checkViewMultiDays

                    axios.get(baseExternalURL + this.urlbookings)
                        .then((result) => {
                            console.log(result)
                            result.data.data.forEach( _data=>{
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
                            this.bookings = result.data.data
                            this.loading = false
                            this.booking_pages = Math.ceil(result.data.count / this.limit)
                            this.validatePagination()
                        }).catch((e) => {
                        console.log(e)
                        this.loading = false
                    })

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

                    if($('.page-item.active').length > 0)
                    {
                        document.getElementsByClassName('page-item active')[0].click()
                    }
                    
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

                            if( ( comp.booking_code === '' || comp.booking_code === null ) && comp.service === null ){
                                err++
                            }

                            services.push({
                                id : comp.id,
                                type : comp.type,
                                code : (comp.booking_code === '' || comp.booking_code === null ) ? comp.aurora_code : comp.booking_code,
                                quote : (comp.booking_code === '' || comp.booking_code === null ) ? "BREDOW" : "EXPEDI",
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
                                nrofile : nrofile,
                                code: localStorage.getItem('client_code')
                            }
                            axios({
                                method: 'post',
                                url: '/api/generic_otas/reserve',
                                data: data
                            })
                                .then((result) => {
                                    console.log(result)
                                    this.loadingModal = false
                                    this.loading = false
                                    if( result.data.success ){
                                        this.$toast.success('Reserva generada con el N°: '+ result.data.detail, {
                                            position: 'top-right'
                                        })
                                        componentsChoosed = []
                                        this.componentsChoosed = []
                                        this.componentsChoosedCount = 0
                                        this.onUpdate()
                                        localStorage.setItem('servicesExpedia', '')
                                        this.hideModal()
                                    } else {
                                        this.$toast.error('Error en el ws '+result.data.detail, {
                                            position: 'top-right'
                                        })
                                    }

                                }).catch((e) => {
                                console.log(e)

                                this.$toast.error('Error al generar la reserva', {
                                    position: 'top-right'
                                })
                            })
                        } else {
                            this.$toast.error('No se encontró el código de servicio en uno o más servicios', {
                                position: 'top-right'
                            })
                        }
                    } else {

                        this.$toast.error('Por favor seleccione el file a agregar', {
                            position: 'top-right'
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
            filters:{
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
        })
    </script>
@endsection
@section('css')
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
        }
        .right, .btn-search{
            float: right;
        }
        .modal-backdrop {
            background-color: #00000052;
        }
        .canSelectText{
            user-select: text;
        }
        .rooms-table{
            padding: 5px;
        }
        .rowTitle{
            text-align: center;
            font-weight: 800;
            border: solid 1px #8a9b9b;
            margin: 3px;
            border-radius: 6px;
            margin-right: 35px;
        }
        .rowTitleChoosed-N{
            background: #343a40;
            color: #ffffff;
        }
        .rowTitleChoosed-C{
            background: #ffba66;
        }
        .datepickerbutton{
            z-index: 1 !important;
        }
        .check_true{
            color: #04bd12;
        }
        .cursor-pointer{
            cursor: pointer;
        }

        .check_false:hover, .check_true:hover{
            opacity: 0.7;
            cursor: pointer;
        }
        .btn-nrofile{
            float: left;
            background: #ffe8e8;
            margin-top: 5px;
            color: #b24343;
        }
        .btn-estado-danger{
            color: white !important;
        }
    </style>
@endsection
