@extends('layouts.app')
@section('li_menu_new')
    <div class="cliente-menu" v-if="user_type_id == 3 ">
        <select class="custom-select" name="" id="cliente_select" v-model="client_id"
                @change="getDestiniesByClientId()">
            <option :value="client.client_id" v-for="client in clients">@{{ client.client.name}}</option>
        </select>
    </div>
    <button type="button" class="mailto btn btn-secondary"><i class="icon-mail"></i><span>1</span></button>
@endsection
@section('content')
    <div class="shopping">
        <b-skeleton-wrapper :loading="blockPage">
            <template #loading>
                <b-row>
                    <div class="col-md-8">
                        <b-row>
                            <b-col cols="4" class="mb-2">
                                <b-skeleton with="100%" height="20%"></b-skeleton>
                            </b-col>
                        </b-row>
                        <b-row class="mt-5">
                            <b-col cols="12" class="mb-2"></b-col>
                        </b-row>
                        <b-row>
                            <b-col cols="12" class="mb-2"></b-col>
                        </b-row>
                        <b-row>
                            <b-col cols="4" class="">
                                <b-skeleton-img height="225px"></b-skeleton-img>
                            </b-col>
                            <b-col cols="8" class="mb-2">
                                <b-skeleton class="mt-3" width="40%" height="5%"></b-skeleton>
                                <b-skeleton class="mt-3" width="100%" height="5%"></b-skeleton>
                                <b-skeleton class="mt-3" width="80%" height="5%"></b-skeleton>
                                <b-skeleton class="mt-3" width="100%" height="5%"></b-skeleton>
                                <b-skeleton class="mt-3" width="80%" height="5%"></b-skeleton>
                                <b-skeleton class="mt-3" width="100%" height="5%"></b-skeleton>
                                <b-skeleton class="mt-3" width="80%" height="5%"></b-skeleton>
                                <b-skeleton class="mt-3" width="100%" height="5%"></b-skeleton>
                            </b-col>
                        </b-row>
                    </div>
                    <div class="col-md-4">
                        <b-row class="mt-5">
                            <b-col cols="12" class="mb-2"></b-col>
                        </b-row>
                        <b-row>
                            <b-col cols="12" class="mb-2"></b-col>
                        </b-row>
                        <b-card>
                            <b-col cols="12" class="mb-2">
                                <b-skeleton class="mt-3" width="50%" height="5%"></b-skeleton>
                                <div class="row" style="width: auto;">
                                    <b-col cols="9">
                                        <b-skeleton class="mt-3" width="100%" height="5%"></b-skeleton>
                                    </b-col>
                                    <b-col cols="3">
                                        <b-skeleton class="mt-3" width="100%" height="5%"></b-skeleton>
                                    </b-col>
                                </div>
                                <div class="row" style="width: auto;">
                                    <b-col cols="9">
                                        <b-skeleton class="mt-3" width="100%" height="5%"></b-skeleton>
                                    </b-col>
                                    <b-col cols="3">
                                        <b-skeleton class="mt-3" width="100%" height="5%"></b-skeleton>
                                    </b-col>
                                </div>
                                <div class="row" style="width: auto;">
                                    <b-col cols="9">
                                        <b-skeleton class="mt-3" width="100%" height="5%"></b-skeleton>
                                    </b-col>
                                    <b-col cols="3">
                                        <b-skeleton class="mt-3" width="100%" height="5%"></b-skeleton>
                                    </b-col>
                                </div>
                                <div class="row" style="width: auto;">
                                    <b-col cols="9">
                                        <b-skeleton class="mt-3" width="100%" height="5%"></b-skeleton>
                                    </b-col>
                                    <b-col cols="3">
                                        <b-skeleton class="mt-3" width="100%" height="5%"></b-skeleton>
                                    </b-col>
                                </div>
                                <div class="row" style="width: auto;">
                                    <b-col cols="9">
                                        <b-skeleton class="mt-3" width="100%" height="5%"></b-skeleton>
                                    </b-col>
                                    <b-col cols="3">
                                        <b-skeleton class="mt-3" width="100%" height="5%"></b-skeleton>
                                    </b-col>
                                </div>
                                <div class="row" style="width: auto;">
                                    <hr style="width: 100%;color: black;">
                                </div>
                                <div class="row" style="width: auto;">
                                    <b-col cols="12">
                                        <b-skeleton type="button" width="100%" height="60px"></b-skeleton>
                                    </b-col>
                                </div>
                            </b-col>
                        </b-card>
                    </div>

                </b-row>

            </template>
        <h3>
            <span class="icon-shopping-bag mr-2"></span> {{trans('reservations.label.title_reservations')}}
        </h3>
        <main class="container-fluid bg-light p-4 my-5 cart-all">
            <div class="row m-0 my-3 align-items-start">
                <div class="col">
                    {{--Servicios--}}
                    <h4 class="subtitle my-0 text-uppercase" v-if="cart_detail.reservations_service.length > 0">
                        @{{ cart_detail.reservations_service.length }} {{trans('reservations.label.services')}}
                    </h4>
                    <div class="services_item" v-for="(service,index) in cart_detail.reservations_service">
                        <div class="blog-card">
                            <div class="col-auto pr-0 py-4" v-if="service.service_logo">
                                <img :src="service.service_logo" :alt="service.service_name"
                                    class="photo" style="width: 200px; height: 200px; object-fit: cover;"
                                    onerror="this.onerror=null;this.src='https://res.cloudinary.com/litomarketing/image/upload/c_scale,w_400/v1440093948/paragliding_in_Miraflores._123110314_dlyun3.jpg'">
                            </div>
                            <div class="col description p-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <h5 class="subtle"><span class="icon-calendar-confirm mr-1"
                                                            style="font-size: 1.5rem;"></span> @{{
                                        formatDate(service.date) }}</h5>
                                </div>
                                <h2 :title="'USD $' + service.total_amount_base">@{{ service.service_name }}
                                    <span class="cod">[@{{ service.service_code }}]</span>
                                    <span class="ok" v-if="service.on_request == 0">OK</span>
                                    <span class="rq" v-if="service.on_request == 1">RQ</span>
                                </h2>

                                {{--------------------Componentes-------------------------------------}}
                                <div class="multi-services" v-if="service.multiservices.length>0"
                                    v-for="(multiservice, c_i) in service.multiservices">

                                    <div class="d-block">
                                        <span class="text-muted mr-1">[@{{ multiservice.service_code }}]</span>
                                        <span v-html="multiservice.service_name"></span>
                                        <strong>(+ USD @{{ getPrice(multiservice.total_amount) }})</strong>
                                    </div>
                                </div>
                                {{--------------------Fin Componentes---------------------------------}}

                                {{--------------------Suplementos-------------------------------------}}
                                <div class="multi-services" v-if="service.supplements.length>0"
                                    v-for="(supplements, c_i) in service.supplements">

                                    <div class="d-block">
                                        <span class="text-muted mr-1">[@{{ supplements.service_code }}] </span>
                                        @{{ formatDate(supplements.date) }} | @{{ supplements.adult_num
                                        }} {{ trans('hotel.label.adults') }} | <span v-if="supplements.child_num > 0">@{{ supplements.child_num }} {{ trans('hotel.label.child') }} |</span><span
                                            v-html="supplements.service_name"></span>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="mr-5"><span class="icon-users"></span> @{{ service.adult_num }} {{ trans('hotel.label.adults') }}</span>
                                    <span class="mr-5" v-if="service.child_num > 0"><span
                                            class="icon-smile"></span> @{{ service.child_num }} {{ trans('hotel.label.child') }}</span>
                                    <span class="price_"><span class="icon-dollar-sign1 mr-2"></span>@{{ getPrice(service.total_amount) }}
                                        <span
                                            v-if="client && client.commission_status == 1 && parseFloat(client.commission) > 0 && user_type_id == 4"
                                            class="badge badge-warning ml-2">
                                            {{trans('global.label.with_commission') }}
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>

                    </div>
                    {{--End Servicios--}}

                    <div class="my-3" v-if="cart_detail.reservations_service.length > 0 && cart_detail.reservations_hotel.length > 0">
                    </div>

                    {{--Hoteles--}}
                    <h4 class="subtitle my-0 text-uppercase" v-if="cart_detail.reservations_hotel > 0">
                        @{{ cart_detail.reservations_hotel.length }} {{trans('reservations.label.hotels')}}
                    </h4>
                    <div class="hoteles_item" v-for="(hotel,index) in cart_detail.reservations_hotel">
                        <div class="blog-card">
                            <div class="col-auto py-4 pr-0">
                                <img :src="changeSizeImageHotel(hotel.hotel_logo)" :alt="hotel.hotel_name"
                                    onerror="this.src = baseURL + 'images/hotel-default.jpg'"
                                    class="photo" style="width: 200px; height: 200px; object-fit: cover;">
                                <ul class="details"></ul>
                            </div>
                            <div class="col description p-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <h5 class="subtle d-flex align-items-start"><span class="icon-calendar-confirm mr-1"
                                                                                    style="font-size: 1.5rem;"></span>
                                        @{{ formatDate(hotel.check_in) }} <span class="icon-arrow-right"></span> @{{
                                        formatDate(hotel.check_out) }} </h5>
                                </div>
                                <h2 class="d-flex align-items-center">
                                    @{{ hotel.hotel_name }}
                                    {{--                                <div class="star">--}}
                                    {{--                                    <img src="https://image.flaticon.com/icons/svg/291/291205.svg" v-for="n in parseInt(hotel.hotel.category)">--}}
                                    {{--                                </div>--}}
                                </h2>
                                {{--                            <p class="mt-3 mr-5"><span class="icon-add-supplement"></span>Suplementos: Desayuno,--}}
                                {{--                                gaseosa, entrada</p>--}}
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class=""><span class="icon-bed-double mr-2"></span>
                                        @{{ Object.keys(hotel.reservations_hotel_rooms).length }} Habitacion(es)
                                    </div>
                                    <div class=""><span class="icon-users mr-2"></span>
                                        @{{ totalPaxHotels(hotel) }} Personas
                                    </div>
                                </div>

                                <div class="mini-card" v-for="(room,index_room) in hotel.reservations_hotel_rooms">
                                    <h2 style="color: #4a90e2;">
                                        @{{ room.room_name }} <span class="text mr-2">@{{ room.rate_plan_name }}</span>
                                        <span class="ok" v-if="room.onRequest ==1">OK</span>
                                        <span class="rq" v-if="room.onRequest ==0">RQ</span>
                                    </h2>

                                    <div class="read-more">
                                        <span class="mr-5">
                                            <span class="icon-users"></span> @{{ room.adult_num  }} {{ trans('hotel.label.adults') }}
                                        </span>
                                        <span class="mr-5" v-if="room.child_num > 0">
                                            <span class="icon-smile"></span> @{{ room.child_num }} {{ trans('hotel.label.child') }}
                                        </span>
                                        <span class="price_">
                                            $ @{{ getPrice(room.total_amount) }}
                                            <span
                                                v-if="client && client.commission_status == 1 && parseFloat(client.commission) > 0 && user_type_id == 4"
                                                class="badge badge-warning ml-2">
                                                {{trans('global.label.with_commission') }}
                                            </span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{--End Hoteles--}}
                </div>
                <div class="col-auto">
                    <div class="summary">
                        <div class="card card-cart">
                            <h2 class="title-card">
                                {{trans('reservations.label.my_products')}}
                            </h2>
                            <div>
                                <div class="d-flex justify-content-between mb-2"
                                    v-for="(service,index) in cart_detail.reservations_service">
                                    <div class="text-title">
                                        <span class="icon-check mr-1"></span> @{{ service.service_name }}
                                        ( @{{ service.adult_num + service.child_num }} personas)
                                        <span v-for="multiservice in service.multiservices">
                                            <br>
                                            + @{{ multiservice.service_name }}
                                        </span>
                                    </div>
                                    <div>USD $.@{{ getPrice(service.total_amount) }}</div>
                                </div>
                                <div class="d-flex justify-content-between"
                                    v-for="(hotel,index) in cart_detail.reservations_hotel">
                                    <div class="text-title"><span class="icon-check mr-1"></span>@{{ hotel.hotel_name }}
                                        (@{{ totalPaxHotels(hotel) }} personas)
                                    </div>
                                    <div>USD $.@{{ getPrice(hotel.total_amount) }}</div>
                                </div>

                                <hr>
                                <div class="d-flex justify-content-between total">
                                    <div>{{ trans('cart_view.label.total_to_pay') }}</div>
                                    <div>
                                        <span
                                            v-if="client && client.commission_status == 1 && parseFloat(client.commission) > 0 && user_type_id == 4"
                                            class="badge badge-warning ml-2">
                                            {{trans('global.label.with_commission') }}
                                        </span>
                                        USD $.@{{ getPrice(total_ultimo_reservado) }}</div>
                                </div>
                            </div>
                            <hr>
                            <div class="mt-2">
                                <h4 class="mb-3"><strong>{{trans('reservations.label.file_information')}}</strong></h4>
                                <form>
                                    <div class="" v-if="user_type_id == 1 ">
                                        <h4 class="col-sm-9">Excecutive</h4>
                                        <select class="custom-select" name="" id="executive_select"
                                                v-model="cart_detail.executive_id" disabled>
                                            <option :value="executive_id" v-for="(executive,executive_id) in executives">@{{
                                                executive}}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="">
                                        <input placeholder="# File" type="text" id="customer_name" class="form-control font-weight-bold"
                                            v-model="cart_detail.file_code" disabled>
                                    </div>
                                    <div class="my-4">
                                        <span
                                            class="text-danger">{{ trans('reservations.label.your_transaction_message_confirmation') }}</span>
                                    </div>
                                    <div class="">
                                        <input placeholder="Reference " type="text" id="customer_name" class="form-control"
                                            v-model="cart_detail.customer_name" disabled>
                                    </div>
                                    <hr>
                                    <h4><strong>{{trans('reservations.label.passenger_data')}}</strong></h4>
                                    <div>
                                        <b>@{{ totalHotelAdults }}</b> {{trans('reservations.label.adults')}}<span></span>
                                        <b>@{{ totalHotelChilds }}</b> {{trans('reservations.label.children')}}
                                    </div>
                                    <hr>
                                    <small class="text-default help_message">
                                        <i class="fas fa-info-circle"></i> {{ trans('reservations.label.help_message_transaction_number') }}
                                        : <strong class="text-secondary">@{{ cart_detail.booking_code }}</strong>
                                    </small>
                                </form>

                            </div>

                        </div>

                    </div>
                </div>
            </div>
        </main>
        </b-skeleton-wrapper>
    </div>
@endsection
@section('css')
    <style>
        .daterangepicker {
            padding: 0 !important;
        }

        /* Estilos de Popup Bottom de Filtro por Precio*/
        .tooltip_cart {
            position: relative;
            display: inline-block;
        }

        .tooltip_cart .tooltip_cart_container {
            visibility: hidden;
            width: 280px;
            background-color: white;
            color: white;
            text-align: center;
            border-radius: 3px;
            padding: 5px 0;
            position: absolute;
            z-index: 1;
            top: 130%;
            left: -237%;
            margin-left: -60px;
            -webkit-box-shadow: 0px 0px 13px -2px rgba(0, 0, 0, 0.75);
            -moz-box-shadow: 0px 0px 13px -2px rgba(0, 0, 0, 0.75);
            box-shadow: 0px 0px 13px -2px rgba(0, 0, 0, 0.75);
        }

        .tooltip_cart .tooltip_cart_container::after {
            content: "";
            position: absolute;
            bottom: 100%;
            left: 46%;
            border-width: 10px;
            border-style: solid;
            border-color: transparent transparent white transparent;
        }

        .tooltip_cart:hover .tooltip_cart_container {
            visibility: visible;
        }

        /*end*/
        /*Estilos Range Slider*/
        .vue-slider-dot-handle {
            background-color: #8e0b07;

        }

        .vue-slider-process {
            background-color: #8e0b07;
        }

        /*End*/
        /* Estilos de Popup Bottom de Filtro por Precio*/
        .tooltip_filter_price {
            position: relative;
            display: inline-block;
        }

        .tooltip_filter_price .tooltip_filter_price_container {
            visibility: hidden;
            width: 250px;
            height: 190px;
            background-color: white;
            color: white;
            text-align: center;
            border-radius: 3px;
            padding: 5px 0;
            position: absolute;
            z-index: 1;
            top: 100%;
            left: 58%;
            margin-left: -60px;
            -webkit-box-shadow: 0px 0px 13px -2px rgba(0, 0, 0, 0.75);
            -moz-box-shadow: 0px 0px 13px -2px rgba(0, 0, 0, 0.75);
            box-shadow: 0px 0px 13px -2px rgba(0, 0, 0, 0.75);
        }

        .tooltip_filter_price .tooltip_filter_price_container::after {
            content: "";
            position: absolute;
            bottom: 100%;
            left: 20%;
            margin-left: -5px;
            border-width: 10px;
            border-style: solid;
            border-color: transparent transparent white transparent;
        }

        .tooltip_filter_price:hover .tooltip_filter_price_container {
            visibility: visible;
        }

        /*end*/
        /*Estilos de los tab de destinos buscados*/
        .tabsNav {
            margin-bottom: 35px;
        }

        .tabsNav li {
            list-style: none;
            float: left;
            margin-right: 25px;
        }

        .tabsNav li a {
            min-width: 90px;
            text-align: center;
            line-height: 27px;
            color: white;
            text-transform: uppercase;
            font-size: 12px;
            height: 27px;
            border-radius: 7px;
            display: block;
            background-color: #0056b3;
        }

        .tabsNav li a:hover,
        .tabsNav li a.active {
            background: #a11216;
            text-decoration: none;
            color: #fff;
            cursor: pointer;
        }

        /*end*/
        /*estilos de galeria*/
        .slides {
            padding: 0;
            width: 100%;
            height: 190px;
            display: block;
            margin: 0 auto;
            position: relative;
        }

        .slides * {
            user-select: none;
            -ms-user-select: none;
            -moz-user-select: none;
            -khtml-user-select: none;
            -webkit-user-select: none;
            -webkit-touch-callout: none;
        }

        .slides input {
            display: none;
        }

        .slide-container {
            display: block;
        }

        .slide {
            top: 0;
            opacity: 0;
            width: 100%;
            height: 190px;
            display: block;
            position: absolute;

            transform: scale(0);

            transition: all .7s ease-in-out;
        }

        .slide img {
            width: 100%;
            height: auto;
        }

        .nav label {
            width: 35%;
            height: 100%;
            display: none;
            position: absolute;

            opacity: 0;
            z-index: 9;
            cursor: pointer;

            transition: opacity .2s;

            color: #FFF;
            font-size: 50pt;
            text-align: center;
            line-height: 175px;
            font-family: "Varela Round", sans-serif;
            background-color: rgba(255, 255, 255, .3);
            text-shadow: 0 0 15px rgb(119, 119, 119);
        }

        .slide:hover + .nav label {
            opacity: 0.5;
        }

        .nav label:hover {
            opacity: 1;
        }

        .nav .next {
            right: 0;
        }

        input:checked + .slide-container .slide {
            opacity: 1;

            transform: scale(1);

            transition: opacity 1s ease-in-out;
        }

        input:checked + .slide-container .nav label {
            display: block;
        }

        .nav-dots {
            width: 100%;
            bottom: 9px;
            height: 11px;
            display: block;
            position: absolute;
            text-align: center;
        }

        .nav-dots .nav-dot {
            top: -5px;
            width: 11px;
            height: 11px;
            margin: 0 4px;
            position: relative;
            border-radius: 100%;
            display: inline-block;
            background-color: rgba(0, 0, 0, 0.6);
        }

        .nav-dots .nav-dot:hover {
            cursor: pointer;
            background-color: rgba(0, 0, 0, 0.8);
        }

        input#img-1:checked ~ .nav-dots label#img-dot-1,
        input#img-2:checked ~ .nav-dots label#img-dot-2,
        input#img-3:checked ~ .nav-dots label#img-dot-3,
        input#img-4:checked ~ .nav-dots label#img-dot-4,
        input#img-5:checked ~ .nav-dots label#img-dot-5,
        input#img-6:checked ~ .nav-dots label#img-dot-6 {
            background: rgba(0, 0, 0, 0.8);
        }

        /*end*/
    </style>
@endsection
@section('js')
    <script>
        new Vue({
            el: '#app',
            data: {
                msg: 'Por favor espere cargando ....',
                blockPage: true,
                update_menu: 1,
                user_type_id: localStorage.getItem('user_type_id'),
                baseExternalURL: window.baseExternalURL,
                baseURL: window.baseURL,
                client_id: 1,
                clients: [],
                cart: [],
                total_ultimo_reservado: 0,
                cart_detail: {
                    executive_id: '',
                    file_code: '',
                    customer_name: '',
                    given_name: '',
                    surname: '',
                    customer_country: '',
                    room_comments: [],
                    reservations_hotel: [],
                    reservations_service: []
                },
                totalHotelAdults: 0,
                totalHotelChilds: 0,
                hotel_room_comments: [],
                cart_total: [],
                cart_hotel_name: '',
                executives: {
                    '15': 'ejecutivo 01',
                    '22': 'ejecutivo 02',
                    '1': 'ejecutivo 03',
                    '44': 'ejecutivo 04',
                    '56': 'ejecutivo 05',
                },
                customer_country: '',
                countries: {
                    'PE': 'Peru',
                    'CH': 'Chile',
                    'AR': 'Argentina',
                },
                total_adult_passengers: [],
                total_child_passengers: [],
                client : {},
            },
            created: function () {
                this.client_id = localStorage.getItem('client_id')

                if (this.client_id) {
                    this.getClient();
                }
            },
            mounted () {
                if (this.user_type_id === 3) {
                    this.getClientsByExecutive()
                }

                this.getReservation()
            },
            computed: {},
            methods: {
                changeSizeImageHotel(url){
                    if (typeof url !== 'undefined' && url !== null) {
                        // Si la URL contiene 'hg-static.hyperguest.com', devolver la URL tal cual
                        if (url.includes('hg-static.hyperguest.com')) {
                            // Si la URL no tiene https, agregarlo
                            if (!url.startsWith('https://') && !url.startsWith('http://')) {
                                return 'https://' + url;
                            }
                            return url;
                        }

                        return this.baseExternalURL+'images/'+url;
                    } else {
                        return ""; // O devuelve un valor predeterminado, dependiendo de tus necesidades
                    }
                },

                roundLito: function (num) {
                    num = parseFloat(num)
                    num = (num).toFixed(2)

                    if (num != null) {
                        var res = String(num).split('.')
                        var nEntero = parseInt(res[0])
                        var nDecimal = 0
                        if (res.length > 1)
                            nDecimal = parseInt(res[1])

                        var newDecimal
                        if (nDecimal < 25) {
                            newDecimal = 0
                        } else if (nDecimal >= 25 && nDecimal < 75) {
                            newDecimal = 5
                        } else if (nDecimal >= 75) {
                            nEntero = nEntero + 1
                            newDecimal = 0
                        }

                        return parseFloat(String(nEntero) + '.' + String(newDecimal))
                    }
                },
                formatDate: function (starDate) {
                    return moment(starDate).format('ddd D MMM')
                },
                hasHotels: function () {
                    return Object.keys(this.cart_detail.reservations_hotel).length
                },
                hasServices: function () {
                    return Object.keys(this.cart_detail.reservations_service).length
                },
                totalPaxHotels: function (hotel) {
                    let total = 0
                    console.log(hotel)
                    Object.entries(hotel.reservations_hotel_rooms).forEach(([key, resHotelRoom]) => {
                        total += resHotelRoom.adult_num + resHotelRoom.child_num
                    })
                    return total

                },
                totalGuestHotels: function () {
                    Object.entries(this.cart_detail.reservations_hotel).forEach(([key, resHotel]) => {
                        this.totalHotelAdults = 0
                        this.totalHotelChilds = 0
                        Object.entries(resHotel.reservations_hotel_rooms).forEach(([key, resHotelRoom]) => {
                            this.totalHotelAdults += resHotelRoom.adult_num
                            this.totalHotelChilds += resHotelRoom.child_num
                            this.total_adult_passengers.push(resHotelRoom.adult_num)
                            this.total_child_passengers.push(resHotelRoom.child_num)
                        })
                    })

                    Object.entries(this.cart_detail.reservations_service).forEach(([key, resService]) => {
                        this.totalServicesAdults += resService.adult_num
                        this.totalServicesChilds += resService.child_num
                        this.total_adult_passengers.push(resService.adult_num)
                        this.total_child_passengers.push(resService.child_num)
                    })

                    var adult_max = Math.max(...this.total_adult_passengers)
                    var child_max = Math.max(...this.total_child_passengers)

                    this.totalHotelAdults = adult_max
                    this.totalHotelChilds = child_max

                },
                setGuestRoomNotes: function () {
                    Object.entries(this.cart_detail.reservations_hotel).forEach(([key, resHotel]) => {
                        Object.entries(resHotel.reservations_hotel_rooms).forEach(([key, resHotelRoom]) => {
                            this.hotel_room_comments[key] += resHotelRoom.guest_note
                        })
                    })
                },
                getClientsByExecutive: function () {
                    axios.get('api/clients/by/executive')
                        .then((result) => {
                            if (result.data.success === true) {
                                this.clients = result.data.clients
                            }
                        }).catch((e) => {
                    })
                },
                getReservation: function () {
                    axios.get(
                        'services/hotels/reservation/' + '{{ Request::route('file_code')}}',
                    )
                        .then((result) => {
                            if (result.data.success) {
                                result.data.data.reservations_service = this.distribute_subs(result.data.data.reservations_service)
                                this.cart_detail = result.data.data
                                this.total_ultimo_reservado = result.data.data.total
                                this.totalGuestHotels()
                                this.setGuestRoomNotes()

                                if (parseInt(this.user_type_id) === 4) {
                                    const storedItem = localStorage.getItem('purchase_gmt');
                                    if(storedItem)
                                    {
                                        let purchase_item = storedItem ? JSON.parse(storedItem) : {};

                                        purchase_item = {
                                            ...purchase_item,
                                            event: "purchase",
                                            package_name: null, // this.cart_detail.customer_name,
                                            transaction_id: this.cart_detail.file_code,
                                        };

                                        console.log(purchase_item);
                                        dataLayer.push(purchase_item);
                                    }
                                }
                            } else {
                                rate.show_message_error = true
                                rate.message_error = result.data.error
                            }
                            this.blockPage = false
                        }).catch((e) => {
                        this.blockPage = false
                        console.log(e)
                    })
                },
                distribute_subs (services) {
                    services.forEach((s) => {
                        s.supplements = []
                        s.multiservices = []
                    })
                    services.forEach((s) => {
                        if (s.type_service === 'multiservice' || s.type_service === 'supplement') {
                            services.forEach((s_, s_k) => {
                                if (s_.reservation_code === parseInt(s.parent_id)) {
                                    if (s.type_service === 'multiservice') {
                                        services[s_k].multiservices.push(s)
                                    } else { // supplement
                                        services[s_k].supplements.push(s)
                                    }
                                }
                            })
                        }
                    })
                    let new_services = []
                    services.forEach((s) => {
                        if (s.type_service !== 'supplement' && s.type_service !== 'multiservice') {
                            new_services.push(s)
                        }
                    })

                    // Agregar precios
                    new_services.forEach((s) => {
                        s.multiservices.forEach((m) => {
                            s.total_amount += m.total_amount
                        })
                        s.supplements.forEach((sup) => {
                            s.total_amount += sup.total_amount
                        })
                    })

                    return new_services
                },
                getCartDetails: function () {
                    this.blockPage = true
                    axios.post(
                        'services/hotels/reservation',
                        {
                            cart: this.cart.cart_content,
                            client_id: this.client_id,
                            executive_id: this.cart_detail.executive_id,
                            file_code: this.cart_detail.file_code,
                            customer_name: this.cart_detail.customer_name,
                            customer_country: this.cart_detail.customer_country,
                            room_comments: this.cart_detail.room_comments,
                        }
                    ).then((result) => {
                        if (result.data.success) {
                            this.cart_detail = result.data.data
                            this.total_ultimo_reservado = result.data.total_reserva
                            if (this.hasHotels()) {
                                this.totalGuestHotels()
                                this.setGuestRoomNotes()
                            }
                            this.blockPage = false
                        } else {
                            this.blockPage = false
                            this.show_message_error = true
                            this.message_error = result.data.error
                            this.$toast.error(result.data.error, {
                                position: 'top-right'
                            })
                        }
                    }).catch((e) => {
                        this.blockPage = false
                    })
                },
                getClient() {
                    axios.get(`${baseExternalURL}api/clients/${this.client_id}`)
                        .then((response) => {
                            this.client = response.data.data;
                        })
                        .catch((error) => {
                            console.error("Error al obtener el cliente:", error);
                        });
                },
                getPrice(price) {
                    console.log('Precio antes de comision: ' + price);
                    console.log('Tipo de usuario: ' + this.user_type_id);
                    console.log('Cliente comision status: ' + (this.client ? this.client.commission_status : 'N/A'));
                    console.log('Cliente comision: ' + (this.client ? this.client.commission : 'N/A'));
                    if (
                        this.client &&
                        this.client.commission_status == 1 &&
                        parseFloat(this.client.commission) > 0 &&
                        this.user_type_id == 4
                    ) {
                        let commissionRate = parseFloat(this.client.commission) / 100;
                        let priceWithCommission = price * (1 + commissionRate);
                        // Usar roundLito para redondear
                        let rounded = this.roundLito(priceWithCommission);
                        return rounded;
                    }

                    return this.roundLito(price);
                },
            }
        })
    </script>
@endsection
<style>
    .circle_red {
        color: #dc3545 !important;
    }
</style>
