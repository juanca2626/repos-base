@extends('layouts.app')
@section('content')
    <section class="page-central">
        <div class="container">
            <div class="row">
                <div id="_overlay"></div>

                <nav-central-bookings></nav-central-bookings>

                <div class="col-12 mb-5">
                    <form class="form">
                        <!--Filtros -->
                        <div class="" v-show="showFilters">
                            <div class="d-flex">
                                <div class="form-group mx-3 w-25">
                                    <label class="col-form-label">Fechas de:</label>
                                    <v-select class="form-control"
                                              :options="date_types"
                                              :value="date_type_id"
                                              autocomplete="true"
                                              data-vv-as="date_type"
                                              data-vv-name="date_type"
                                              :on-change="changeDateType()"
                                              name="date_type"
                                              v-model="dateTypeSelected">
                                    </v-select>
                                </div>
                                <div class="form-group mx-3 " v-show="toggleDate">
                                    <label class="col-form-label">Elige fecha</label>
                                    <div class="">
                                        <date-picker
                                                class="form-control"
                                                :config="datePickerOptions"
                                                id="date" autocomplete="off"
                                                name="date" ref="datePicker"
                                                v-model="date">
                                        </date-picker>
                                    </div>
                                </div>
                                <div class="form-group mx-3 " v-show="!toggleDate">
                                    <label class="col-form-label">Desde</label>
                                    <div class="">
                                        <date-picker
                                                :config="datePickerFromOptions"
                                                id="date_from"
                                                autocomplete="off"
                                                name="date_from" ref="datePickerFrom"
                                                v-model="date_from">
                                        </date-picker>
                                    </div>
                                </div>
                                <div class="form-group mx-3 " v-show="!toggleDate">
                                    <label class="col-form-label">Hasta</label>
                                    <div class="">
                                        <date-picker
                                                :config="datePickerToOptions"
                                                id="date_to"
                                                autocomplete="off"
                                                name="date_to" ref="datePickerTo"
                                                v-model="date_to">
                                        </date-picker>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex">
                                <div class="form-group mx-3 w-25">
                                    <label class="col-form-label">Estado:</label>
                                    <v-select :options="status"
                                              :value="status_id"
                                              autocomplete="true"
                                              data-vv-as="status"
                                              data-vv-name="status"
                                              name="status"
                                              v-model="statusSelected"
                                              class="form-control">
                                    </v-select>
                                </div>
                                <div class="form-group mx-3 w-25">
                                    <label class="col-form-label">Buscar por apellido del cliente:</label>
                                    <input :class="{'form-control':true }"
                                           id="filter_client_name" name="filter_client_name"
                                           type="text"
                                           ref="filter_client_name" v-model="filter_client_name">
                                </div>
                            </div>
                            <div class="text-right">
                                <button @click="willOnUpdate()" class="btn btn-primary w-25" type="button" :disabled="loading" style="margin-top: 32px;">
                                    <i :class="{'fa':true, 'fa-search':!loading, 'fa-spin fa-spinner':loading }"></i> Buscar
                                </button>
                            </div>

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
                                <th scope="col" class="text-muted">ID</th>
                                <th scope="col" class="text-muted cursor-pointer" @click="filterBy('made_date_time')">
                                    Fecha de Creación <i :class="{'fa':true,'fa-angle-down':filter_made_date_time,'fa-angle-up':!filter_made_date_time}"></i>
                                </th>
                                <th scope="col" class="text-muted cursor-pointer" @click="filterBy('start_date')">
                                    Inicio-Fin <i :class="{'fa':true,'fa-angle-down':filter_start_date,'fa-angle-up':!filter_start_date}"></i>
                                </th>
                                <th scope="col" class="text-muted cursor-pointer" @click="filterBy('lead_customer_name')">
                                    Cliente <i :class="{'fa':true,'fa-angle-down':filter_lead_customer_name,'fa-angle-up':!filter_lead_customer_name}"></i>
                                </th>
                                <th scope="col" class="text-muted">Cant.Pax</th>
                                <th scope="col" class="text-muted cursor-pointer" @click="filterBy('passenger')">
                                    Servicio <i :class="{'fa':true,'fa-angle-down':filter_booking_name,'fa-angle-up':!filter_booking_name}"></i>
                                </th>
                                <th scope="col" class="text-muted">Código</th>
                                <th scope="col" class="text-muted">Opciones</th>
                            </tr>
                            </thead>
                            <tbody class="text-center">
                            <tr v-for="booking in bookings">
                                <td class="td-body">@{{ booking.id}}</td>
                                <td class="td-body">@{{ booking.made_date_time | formatDate}}</td>
                                <td class="td-body">@{{ booking.start_date | formatDate}} - @{{booking.end_date | formatDate}}</td>
                                <td class="td-body">@{{ booking.quantity_pax}}</td>
                                <td class="td-body">@{{ booking.passenger}}</td>
                                <td class="td-body">@{{ booking.description}}</td>
                                <td class="td-body">
                                    <span class="btn-estado" v-if="booking.aurora_code!==null && booking.aurora_code!==''">
                                        @{{ booking.aurora_code }}
                                    </span>
                                    <span v-else>
                                        -
                                    </span>
                                </td>
                                <td>

{{--                                    <button @click="closeOthersPopovers(booking)" class="btn btn-info" :id="'pop' + booking.booking_id"--}}
{{--                                            type="button" title="Ver más información de la reserva">--}}
{{--                                        <i class="fa fa-list-alt"></i>--}}
{{--                                    </button>--}}

{{--                                    <!--                    :data-target="'pop' + booking.booking_id"-->--}}
{{--                                    <b-popover class="canSelectText" :show.sync="booking.popShow" :target="'pop' + booking.booking_id" title="Datos de Reserva" triggers="hover focus">--}}
{{--                                        <strong>Fecha y hora de creación:</strong>--}}
{{--                                        <br>@{{ booking.made_date_time | formatDate}}<br>--}}
{{--                                        <strong>Nombre:</strong> <br>@{{ booking.booking_name }}<br>--}}
{{--                                        <strong>Fechas:</strong><br>--}}
{{--                                        <strong>Inicio:</strong> @{{ booking.start_date}} | <strong>Fin:</strong> @{{ booking.end_date | formatDate}}<br>--}}
{{--                                    <!--                        <strong>Status:</strong> @{{ booking.status_text }}<br>-->--}}
{{--                                        <strong>Cliente:</strong> @{{ booking.lead_customer_name }} | <strong>Total clientes:</strong> @{{ booking.customer_count }}<br>--}}
{{--                                        <strong>Venta:</strong> @{{ booking.sale_currency }}--}}
{{--                                        <span v-if="parseInt( booking.booking_has_net_price ) === 1">--}}
{{--                                            @{{ booking.balance }}--}}
{{--                                        </span>--}}
{{--                                        <span v-else>--}}
{{--                                            @{{ booking.sales_revenue - booking.commission }}--}}
{{--                                        </span>--}}
{{--                                        <br>--}}
{{--                                        <span v-if="booking.cancel_reason != 0">--}}
{{--                                            <strong>Motivo de cancelación:</strong> @{{ booking.cancel_reason }}<br>--}}
{{--                                            <strong>Estado de cancelación:</strong> @{{ booking.cancel_text }}<br>--}}
{{--                                        </span>--}}
{{--                                        <!--                        <strong>Final check realizado:</strong>-->--}}
{{--                                        <!--                        <span v-if="booking.final_check == 0">No</span>-->--}}
{{--                                        <!--                        <span v-if="booking.final_check == 1">Si</span>-->--}}
{{--                                        <!--                        <br>-->--}}
{{--                                        <!--                        <strong>Comisión:</strong> <span v-html="booking.commission_display"></span><br>-->--}}
{{--                                        <!--                        <strong>Impuesto de la comisión:</strong> <span v-html="booking.commission_tax_display"></span><br>-->--}}
{{--                                        <br>--}}
{{--                                        <strong>Datos de Agente:</strong> <br>--}}
{{--                                        <strong>Referencia: </strong>@{{ booking.agent_ref }}<br>--}}
{{--                                    <!--                        <strong>Tipo:</strong> @{{ booking.agent_type }}<br>-->--}}
{{--                                        <strong>Nombre:</strong> @{{ booking.agent_name }}<br>--}}
{{--                                        <strong>Código:</strong> @{{ booking.agent_code }}<br>--}}
{{--                                        <br>--}}
{{--                                    <!--                        <strong>Status de pago:</strong> @{{ booking.payment_status_text }}<br>-->--}}
{{--                                        <!--                        <strong>Saldo adeudado por:</strong>-->--}}
{{--                                        <!--                        <span v-if="booking.balance_owed_by == 'C'">Cliente</span>-->--}}
{{--                                        <!--                        <span v-if="booking.balance_owed_by == 'A'">Agente</span>-->--}}
{{--                                        <!--                        <br>-->--}}
{{--                                        <!--                        <strong>Restante a pagar:</strong> <span v-html="booking.balance_display"></span><br>-->--}}

{{--                                    </b-popover>--}}

                                    <span class="btn-estado" v-if="booking.status">
                                        P
                                    </span>
                                    <span class="btn-estado" v-if="!(booking.status)">
                                        C
                                    </span>

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

            </div>
        </div>
    </section>
@endsection
@section('js')
    <script>

        new Vue({
        el: '#app',
        data: {
            loading:false,
            showFilters:true,
            showOptions:true,
            files:[],
            fileSelected:[],
            nrofile:null,
            urlbookings: '',
            date_type_id:"",
            date: '',
            status_id:'',
            filter_client_name:'',
            dateTypeSelected:{
                code : 0,
                label : "Creación",
                param : "made"
            },
            date_types:[
                {
                    code : 0,
                    label : "Creación",
                    param : "made"
                },
                {
                    code : 1,
                    label : "Inicio",
                    param : "start"
                },
                {
                    code : 2,
                    label : "Termino",
                    param : "end"
                },
                {
                    code : 3,
                    label : "Una específica",
                    param : "component_start_date"
                }
            ],
            statusSelected:{
                code : 1,
                label : "Pendientes",
                param : "1"
            },
            status:[
                {
                    code : 2,
                    label : "Todos",
                    param : ""
                },
                {
                    code : 1,
                    label : "Pendientes",
                    param : "1"
                },
                {
                    code : 0,
                    label : "Cerradas",
                    param : "0"
                }
            ],
            final_check:[
                {
                    code : 0,
                    label : "Todos",
                    param : ""
                },
                {
                    code : 1,
                    label : "Completados",
                    param : "1"
                },
                {
                    code : 2,
                    label : "Incompletos",
                    param : "0"
                }
            ],
            toggleDate : 0,
            datePickerOptions: {
                format: 'DD/MM/YYYY',
                useCurrent: false,
                locale: localStorage.getItem('lang')
            },
            datePickerFromOptions: {
                format: 'DD/MM/YYYY',
                useCurrent: false,
                locale: localStorage.getItem('lang')
            },
            datePickerToOptions: {
                format: 'DD/MM/YYYY',
                useCurrent: false,
                locale: localStorage.getItem('lang')
            },
            form:{
                service_id:null,
                booking_id:null
            },
            my_date_to: '',
            my_date_from: '',
            bookings : [],
            pageChosen : 1,
            limits : [5, 10, 15, 20, 25, 30],
            limit : 15,
            booking_pages : 0,
            filter_made_date_time : '',
            filter_start_date : '',
            filter_lead_customer_name : '',
            filter_agent_name : '',
            filter_booking_name : '',
            _filter:'',
            _order:'',
            view_pages: 15,
            show_pages: {},
            pages: 0,
            page: 0
        },
        created() {

        },
        mounted() {
            this.onUpdate()
        },
        computed: {
            date_from:{
                get : function(){
                    let date = new Date()
                    let week = 1000 * 60 * 60 * 24 * 7
                    let myDate = new Date(date.getTime() - week)

                    let day = myDate.getDate()
                    let month = myDate.getMonth() + 1
                    let year = myDate.getFullYear()

                    let myDateFormat
                    if(month < 10){
                        myDateFormat = `${day}/0${month}/${year}`
                    }else{
                        myDateFormat = `${day}/${month}/${year}`
                    }
                    this.my_date_from = myDateFormat
                    return myDateFormat
                },
                set : function(newDate){
                    this.my_date_from = newDate
                    return newDate
                }
            },
            date_to:{
                get : function(){
                    let date = new Date()
                    let day = date.getDate()
                    let month = date.getMonth() + 1
                    let year = date.getFullYear()

                    let today
                    if(month < 10){
                        today = `${day}/0${month}/${year}`
                    }else{
                        today = `${day}/${month}/${year}`
                    }
                    this.my_date_to = today
                    return today
                },
                set : function(newDate){
                    this.my_date_to = newDate
                    return newDate
                }
            }
        },
        methods: {
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
            filterBy(_filter){
                if( _filter == 'made_date_time' ){
                    this.filter_made_date_time = !(this.filter_made_date_time)
                    this._order = this.filter_made_date_time
                }
                if( _filter == 'start_date' ){
                    this.filter_start_date = !(this.filter_start_date)
                    this._order = this.filter_start_date
                }
                if( _filter == 'passenger' ){
                    this.filter_lead_customer_name = !(this.filter_lead_customer_name)
                    this._order = this.filter_lead_customer_name
                }
                if( _filter == 'description' ){
                    this.filter_booking_name = !(this.filter_booking_name)
                    this._order = this.filter_booking_name
                }
                this._filter = _filter
                this.onUpdate()
            },
            toggleStatus(id, status) {
                this.loading = true
                axios.post('/api/channel/tourcms/'+id+'/status', { status : status })
                    .then((result) => {
                        if(result.data.success){
                            this.onUpdate()
                        }
                        this.loading = false
                    }).catch(() => {
                    this.loading = false
                })
            },
            searchAgents(){
                // agents
                axios.get('/api/channel/tourcms/bookings/agents')
                    .then((result) => {
                        this.agents = result.data
                    }).catch((e) => {
                        console.log(e)
                    })
            },
            onSearch(search, loading) {
                loading(true)
                let data = { query : search }
                axios.post('/api/channel/tourcms/booking/files', data)
                    .then((result) => {
                        loading(false)
                        this.files = result.data
                    }).catch(() => {
                    loading(false)
                })
            },
            changeDateType(){
                if( this.dateTypeSelected.code == 3 ){
                    this.toggleDate = 1;
                } else {
                    this.toggleDate = 0;
                }
            },
            setPage(page){
                if( page < 1 || page > this.booking_pages ){
                    return;
                }
                this.pageChosen = page
                this.onUpdate("","")
            },
            willOnUpdate () {
                this.pageChosen = 1
                this.onUpdate()
            },
            onUpdate() {
                let myTypeDate = this.dateTypeSelected.param

                if( myTypeDate == 'component_start_date' && ( this.date == '' || this.date == null ) ){
                    this.$toast.error("La fecha no es correcta", {
                        position: 'top-right'
                    })
                } else {
                    this.loading = true
                    let myDateFrom = this.my_date_from
                    let myDateTo = this.my_date_to
                    this.urlbookings = 'api/central_bookings?token=' + localStorage.getItem('access_token') +
                        '&type_date=' + myTypeDate + '&date_from=' + myDateFrom + '&date_to=' + myDateTo +
                        '&date=' + this.date + '&active=' + this.statusSelected.param +
                        '&lead_customer_surname=' +  this.filter_client_name+
                        '&page='+this.pageChosen+'&limit='+this.limit+
                        '&filter='+this._filter+'&order='+this._order

                    axios.get(baseExternalURL + this.urlbookings)
                        .then((result) => {
                            console.log(result)
                            this.bookings = result.data.data
                            this.loading = false
                            this.booking_pages = Math.ceil(result.data.count / this.limit)
                            this.validatePagination()
                        }).catch((e) => {
                            console.log(e)
                            this.loading = false
                        })
                }
            },
            closeOthersPopovers: function () {
                this.$root.$emit('bv::hide::popover')
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
    .rowTitle {
        text-align: center;
        background: #e9ecef;
        border: solid 1px #8a9b9b;
        margin: 3px -6px;
        color: #8e8e8e;
        font-weight: 800;
    }

    .datepickerbutton{
        z-index: 1 !important;
    }
    .cursor-pointer{
        cursor: pointer;
    }
    .back_resalt{
        background: #fbff00;
    }
    </style>
@endsection
