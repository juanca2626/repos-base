 @extends('layouts.app')
@section('content')
    <section class="page-trains">
        <div class="container">
            <div id="_overlay"></div>

            <div class="motor-busqueda">
                <h2>{{ trans('train.label.reservation_train') }}</h2>
                <div class="form form-hotel">
                    <div class="form-row">
                        <div class="form-group train">
                            <select class="form-control" v-model="search_trains.train_type_id">
                                <option :value="t_type.id" v-for="t_type in train_types">@{{ t_type.name }}
                                </option>
                            </select>
                        </div>
                        <div class="form-group destino">
                            <v-select :options="rail_routes_select" v-model="search_trains.rail_route"
                                      class="form-control">
                            </v-select>
                        </div>
                        <div class="form-group fecha">
                            <date-range-picker
                                    :locale-data="locale_data"
                                    :timePicker24Hour="false"
                                    :showWeekNumbers="false"
                                    :ranges="false"
                                    :auto-apply="true"
                                    :start-date="startDate"
                                    :min-date="minDate"
                                    v-model="search_trains.dateRange">
                            </date-range-picker>
                        </div>

                        <div class="form-group habitacion dropdown">
                            <button class="form-control" id="dropdownHab" data-toggle="dropdown" aria-haspopup="true"
                                    aria-expanded="true">
                                <span><strong>@{{search_trains.quantity_guides}}</strong>{{ trans('train.label.guides') }}</span>
                                <span><strong>@{{search_trains.quantity_adults}}</strong>{{ trans('train.label.adults') }}</span>
                                <span><strong>@{{search_trains.quantity_children}}</strong>{{ trans('train.label.children') }}</span>
                            </button>

                                <input type="hidden"
                                       :value="search_trains.quantity_guides+' guías '+search_trains.quantity_adults+'adultos '+search_trains.quantity_child +'niños'"
                                >
                                <div aria-labelledby="dropdownHab"
                                     class="dropdown dropdown-menu container_quantity_persons_rooms width_child_1_container">

                                    <div class="container_quantity_persons_rooms_selects quantity-persons-rooms">

                                        <div class="form-group container_quantity_persons_rooms_select width_default_select">
                                            <label>{{ trans('train.label.guides') }}</label>
                                            <select
                                                    v-model="search_trains.quantity_guides"
                                                    class="form-control">
                                                <option v-for="num_guide in 4" :value="num_guide - 1">@{{ num_guide - 1 }}
                                                </option>
                                            </select>
                                        </div>
                                        <div class="form-group container_quantity_persons_rooms_select width_default_select">
                                            <label>{{ trans('train.label.adults') }}</label>
                                            <select
                                                    v-model="search_trains.quantity_adults"
                                                    class="form-control">
                                                <option v-for="num_adult in 20" :value="num_adult">@{{ num_adult }}
                                                </option>
                                            </select>
                                        </div>
                                        <div class="form-group container_quantity_persons_rooms_select width_default_select">
                                            <label>{{ trans('train.label.children') }}</label>
                                            <select
                                                    v-model="search_trains.quantity_children"
                                                    class="form-control">
                                                <option v-for="num_child in 10" :value="num_child - 1">@{{ num_child - 1 }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                        </div>

                    </div>
                </div>
                <div class="form-btn-action">
                    <div class="row">
                        <div class="col-sm-9">
                        </div>
                        <div class="col-sm-3">
                            <button class="btn btn-primary"
                                    @click="search()">{{ trans('train.label.search_trains') }}</button>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <div class="container-heigth">
                <div class="row col-lg-12 title-result">
                    <h3>{{ trans('hotel.label.your_results') }}</h3>
                </div>
                <div class="row col-lg-12">
                    <div class="tabsNav">
                        <ul class="clearfix">
                            <li>
                                <a :class="activeTabGoing"
                                   @click.prevent="setTab($event,'going')">
                                    IDA
                                </a>
                            </li>
                            <li>
                                <a :class="activeTabReturn"
                                   @click.prevent="setTab($event,'return')">
                                    RETORNO
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="hoteles-encontrados">
                        <span><b>10</b> Trenes encontrados para  <b>Machu Pichu - Cusco</b></span>
                        <span><b>@{{ getTrainStartDate() }}</b></span> <span><b>@{{ getTrainEndDate() }}</b></span>
                    </div>
                </div>
                <div class="row col-lg-12 filtros">
                    <div class="row col-lg-4">
                        <div class="col-lg-4 label">
                            <label>Ordenar por</label>
                        </div>
                        <div class="col-lg-8">
                            <select class="form-control" @change="">
                                <option value="2">Horas de salida(ascendente)</option>
                                <option value="3">2</option>
                                <option value="4">3</option>
                            </select>
                        </div>
                    </div>
                    <div class="row col-lg-4 filtros-font">
                        <div class="col lg-2 text-center text">
                            Filtros
                        </div>

                        <!--Filtro por Nombre de Hotel-->
                        <div class="col lg-2 text-center font">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="col lg-2 text-center font">
                            <i class="far fa-clock"></i>
                        </div>
                        <div class="col lg-2 text-center font">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                    </div>
                    <div class="row col-lg-4 vista">
                        <div class="col-lg-9 text">
                            Tipo de vista
                        </div>
                        <div class="row col-lg-3 content-vista">
                            <div id="filterView_0" class="col lg-2 font text-center active"
                                 @click="filterView(0,this)">
                                <i class="fas fa-list" style="font-size: x-large"></i>
                            </div>
                            <div id="filterView_1" class="col lg-2 font text-center" @click="filterView(1,this)">
                                <i class="fas fa-th-list" style="font-size: x-large"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row col-lg-12 one-view">
                    <div class="container">
                        <div class="row body-result" :class="{'train-blocked':train.template==null}"
                             v-show="(train.ID_VIAJE_SENTIDO=='1' && activeTabGoing=='active') ||
                                        (train.ID_VIAJE_SENTIDO=='2' && activeTabReturn=='active')"
                             v-for="train in train_templates">
                            <div class="col-2 d-block align-items-center">
                                <img src="/images/train_logo_IR.png" class="object-fit_cover">
                            </div>
                            <div class="col-3 align-self-center">
                                <h5 :class="{'select-yellow':train.ID_RUTA==search_trains.rail_route}" v-if="train.template==null">
                                    <b>@{{ train.NOM_RUTA }}
                                        <b-button v-b-popover.hover="{ variant: 'info',  content: 'Popover content' }"><i class="fas fa-info"></i></b-button>
                                    </b>
                                </h5>
                                <h5 :class="{'select-yellow':train.ID_RUTA==search_trains.rail_route}" v-if="train.template!=null"><b>@{{ train.template.train_rail_route.rail_route.name }}</b></h5>
                                <h5 v-if="train.template==null">@{{ train.NOM_SERVICIO }}</h5>
                                <h5 v-if="train.template!=null">@{{ train.template.train_train_class.train_class.name }}</h5>
                                <b>Salida:</b> @{{ train.HOR_SALIDA }} - <b>Llegada:</b>  @{{ train.HOR_LLEGADA }}
                            </div>
                            <div class="col-1 align-self-center formato-gris">
                                (Duración @{{ calculateHours(train.HOR_LLEGADA, train.HOR_SALIDA) }} hrs.)
                            </div>
                            <div class="col-1 align-self-center" v-for="rate in train.TARIFA_PRECIO">
                                <b>@{{ rate.NOM_PASAJERO_TIPO | capitalize }} </b><span>@{{ train.NOM_MONEDA_SIMBOLO }}@{{ rate.IMP_PRECIO }}</span>
                            </div>
                            <div class="col-1 align-self-center formato">
                                <span>usd</span><span class="price" >$ 170</span>
                                <div class="formato-gris tarifa"><b>Tarifa especial</b></div>
                            </div>
                            <div class="col-2 align-self-center">
                                <button class="btn btn-seleccionar">Seleccionar</button>
                            </div>
                        </div>

                    </div>
                </div>
                {{-- segunda vista--}}
                <div class="row col-lg-12 second-view">
                    <div class="container">
                        <div class="row body-result">
                            <div class="col-3 d-block align-items-center">
                                <img src="/images/actividad.png" class="image-cover">
                            </div>
                            <div class="col-5">
                                <div class="d-flex align-items-center">
                                    <h3><b>Expedition 33  </b></h3>
                                    <span>by</span>
                                    <img src="/images/perurail-logo.png" class="image-icon" alt="logo">
                                </div>
                                <div class="d-flex align-items-center mt-3">
                                    <p><b>Salida:</b> 222  -  <b>Llegada:</b> 222</p>
                                    <p class="formato-gris ml-4">
                                        (Duración 02 hrs.)
                                    </p>
                                </div>
                                <div class="d-flex align-items-center mt-5">
                                    <div class="mr-4">
                                        <a href=""><i class="icon-camera"></i>  Fotos</a>
                                    </div>
                                    <div class="mr-4">
                                        <a href="" class=""><i class="icon-alert-circle"></i>  Detalles</a>
                                    </div>
                                    <div class="box-icons">
                                        <i class="far fa-credit-card"></i>
                                        <i class="fas fa-utensils"></i>
                                        <i class="fas fa-cocktail"></i>
                                        <i class="fas fa-train"></i>
                                        <i class="fas fa-wheelchair"></i>
                                        <span>+3</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-2 align-self-center formato">
                                <div class="d-flex align-items-end"><p>usd</p> <h3 class="price" ><b>$ 170</b></h3></div>
                                <div class="d-flex formato-gris tarifa">
                                    Tarifa especial
                                    <div class="dropdown show mr-2">
                                        <a class="text-decoration-none" href="#" role="button" id="dropdownMenuLink4" data-toggle="dropdown"
                                           aria-haspopup="false" aria-expanded="false" style="border: none;">
                                            <i class="icon-alert-circle"></i>
                                        </a>
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuLink4"
                                             style="width: 240px; font-size: 14px; margin-left: -36px; margin-top: 18px;">
                                            <div style="width: 100%;margin: 0 auto;" class="">
                                                <div class="container" >
                                                    <b style="font-size: 15px;">Por pasajero</b>
                                                    <hr>
                                                    <div class="row text-center">
                                                        <div class="col-6">Adulto</div>
                                                        <div class="col-6">$150</div>
                                                    </div>
                                                    <div class="row text-center">
                                                        <div class="col-6">Adulto</div>
                                                        <div class="col-6">$150</div>
                                                    </div>

                                                    <div class="row text-right">
                                                        <div class="col-12 justify-content-end">
                                                            <b-button v-b-modal.modal-reg>Ver regulaciones</b-button>
                                                            <b-modal id="modal-reg" centered>
                                                                <h3><b>Tarifa especial</b></h3>
                                                                <h5><b>Regulaciones de tarifa</b></h5>
                                                                <p class="my-4">Contenido</p>
                                                                <template v-slot:modal-footer="{ ok, cancel, hide }">
                                                                    <b-button size="sm" variant="primary" @click="ok()">
                                                                        OK
                                                                    </b-button>
                                                                </template>
                                                            </b-modal>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="col-2 align-self-center">
                                <button class="btn btn-seleccionar">Seleccionar</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div></div>
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
            loadingModal:false,
            modalTitle:'',
            contentModal:'',
            translations:[],
            train_types:[],
            rail_routes_select:[],
            search_trains:{
                train_type_id:'',
                rail_route:'',
                dateRange:'',
                quantity_guides:0,
                quantity_adults:1,
                quantity_children:0
            },
            startDate: moment().add('days', 2).format('Y-MM-DD'),
            minDate: moment().add('days', 2).format('Y-MM-DD'),
            locale_data: {
                direction: 'ltr',
                format: moment.localeData().postformat('ddd D MMM'),
                separator: ' - ',
                applyLabel: '{{ trans("hotel.label.save") }}',
                cancelLabel: '{{ trans("hotel.label.cancel") }}',
                weekLabel: 'W',
                customRangeLabel: '{{ trans("hotel.label.date_range") }}',
                daysOfWeek: moment.weekdaysMin(),
                monthNames: moment.monthsShort(),
                firstDay: moment.localeData().firstDayOfWeek()
            },
            activeTabGoing:'active',
            activeTabReturn:'',
            train_templates:[],
        },
        created() {

        },
        mounted() {

            axios.get(baseURL + 'translation/' + localStorage.getItem('lang') + '/slug/train').then((data) => {
                this.translations = data.data
            })
            // Train Types
            axios.get(
                'api/train_types'
            )
                .then((result) => {
                    this.train_types = result.data
                    this.train_types.forEach( tt =>{
                        if( tt.abbreviation == 'RT' ){
                            this.search_trains.train_type_id = tt.id
                        }
                    } )
                }).catch((e) => {
                console.log(e)
            })

            axios.get('api/rail_routes?status=1')
                .then((result) => {
                    console.log(result)
                    let _routes = []
                    result.data.data.forEach( r=>{
                        _routes.push({
                            code : r.id,
                            label : r.name,
                        })
                    } )

                    this.rail_routes_select = _routes
                }).catch((e) => {
                console.log(e)
            })

        },
        methods: {
            getTrainStartDate: function () {
                return moment(this.search_trains.dateRange.startDate).lang(localStorage.getItem('lang')).format('ddd D MMM')
            },
            getTrainEndDate: function () {
                return moment(this.search_trains.dateRange.endDate).lang(localStorage.getItem('lang')).format('ddd D MMM')
            },
            setTab: function (event, mode) {
                event.preventDefault()
                if( mode === 'going' ){
                    this.activeTabGoing = 'active'
                    this.activeTabReturn = ''
                } else {
                    this.activeTabGoing = ''
                    this.activeTabReturn = 'active'
                }
                let train_type_id_ow = ''
                this.train_types.forEach( tt=>{
                    if( tt.abbreviation === 'OW' ){
                        train_type_id_ow = tt.id
                    }
                })
                if( this.search_trains.train_type_id == train_type_id_ow ){
                    this.search()
                }
            },
            search(){
                console.log(this.search_trains.train_type_id)
                if( this.search_trains.train_type_id == '' ||
                    ( this.search_trains.quantity_guides + this.search_trains.quantity_adults +
                        this.search_trains.quantity_children ) == 0
                    ){
                    this.$toast.warning(this.translations.message.incomplete_filters, {
                        position: 'top-right'
                    })
                    return
                }

                let new_route_id = 2
                if(this.activeTabGoing === 'active'){
                    new_route_id = 1
                }

                axios.get(
                    'api/channel/trains/search?train_type_id=' + this.search_trains.train_type_id +
                    '&rail_route_id='+ new_route_id +
                    '&user_id='+localStorage.getItem('user_id') +
                    '&client_id='+localStorage.getItem('client_id') +
                    '&date_from='+moment(this.search_trains.dateRange.startDate).format('DD/MM/Y') +
                    '&date_to='+moment(this.search_trains.dateRange.endDate).format('DD/MM/Y') +
                    '&guides='+this.search_trains.quantity_guides +
                    '&adults='+this.search_trains.quantity_adults +
                    '&children='+this.search_trains.quantity_children
                )
                    .then((result) => {
                        this.train_templates = result.data.incarail.success ? result.data.incarail.data : []
                        if( !result.data.success ){
                            this.$toast.danger( result.data.message , {
                                position: 'top-right'
                            })
                        }
                    }).catch((e) => {
                    console.log(e)
                })
            },
            setPage(page){
                if( page < 1 || page > this.booking_pages.length ){
                    return;
                }
                this.pageChosen = page
                this.onUpdate("","")
            },
            showModal () {
                this.$refs['my-modal'].show()
            },
            hideModal () {
                this.$refs['my-modal'].hide()
                this.$refs['my-modal-add-to-file'].hide()
            },
            calculateHours: function (h1, h2) {

                let _h2 = h2.split(':')
                let duration
                duration = moment(h1, "hh:mm").subtract(_h2[0], 'hours').format('hh:mm')
                duration = moment(duration, "hh:mm").subtract(_h2[1], 'minutes').format('hh:mm')
                return duration
            }
        },
        filters:{
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
    margin-top: 34px;
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
    .form-group.train::before{
        font-family: "Font Awesome 5 Free" !important;
        font-size: 20px;
        color: #BBBDBF;
        position: absolute;
        top: 42px;
        left: 30px;
        font-weight: 800; /*Importante, si no no sale*/
        content: "\f362";
    }
    .page-hotel .container .form-group.destino {
        width: 25% !important;
    }
    .filtros > .col-lg-4 > div.label.col-lg-4 {
        max-width: 27%;
    }
    .train-blocked{
        /*opacity: 0.5;*/
        /*background: #d3d3d37a;*/
    }
    .select-yellow{
            background: #feffa5;
    }

    </style>
@endsection
