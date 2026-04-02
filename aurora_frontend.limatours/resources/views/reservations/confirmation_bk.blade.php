    @extends('layouts.app')
@section('li_menu_new')
    <div class="cliente-menu" v-if="user_type_id == 3 ">
        <select class="custom-select" name="" id="cliente_select" v-model="client_id" @change="getDestiniesByClientId()">
            <option :value="client.client_id" v-for="client in clients">@{{ client.client.name}}</option>
        </select>
    </div>
    <button type="button" class="mailto btn btn-secondary"><i class="icon-mail"></i><span>1</span></button>
@endsection
@section('content')
    <section class="page-reservation">
        <loading-component v-show="blockPage"></loading-component>
        <div class="container">
            <div class="motor-busqueda">
                <h2>Resumen de compra</h2>
                <h3>Tienes @{{ cart.quantity_items }} productos en tu carrito de compras</h3>
                <div class="content-data cart cart-view">
                    <div class="shopping-cart" v-if="hasHotels() > 0 || hasServices() > 0">
                        <div class="hotel-content-shopping"
                             v-for="(hotel,index_hotel) in cart_detail.reservations_hotel">
                            <div class="img-shopping">
                                <img :src="hotel.hotel_logo" alt="Image Hotel"
                                     onerror="this.src = baseURL + 'images/hotel-default.jpg'">
                            </div>
                            <div class="content-shopping">
                                {{--<span class="tipo">[Cuz111] - Turista</span>--}}
                                <h3 class="text-left">@{{ hotel.hotel_name }}
                                    {{--<span v-for="n in parseFloat(cart_detail.hotels[hotel.hotel_id].stars, 10) " class="icon-star"></span>--}}
                                </h3>
                                <div class="date-shopping">
                                    <i class="icon-calendar"></i> <span>@{{ formatDate(hotel.check_in) }}</span> <span>@{{ formatDate(hotel.check_out) }}</span>
                                </div>
                            </div>
                            <div class="car-room" v-for="(room,roomIndet) in hotel.reservations_hotel_rooms">
                                <h5>
                                    <span class="fa fa-circle" v-bind:class="{circle_red : room.onRequest ==0}"></span>
                                    @{{ room.room_name }} -
                                    <b> @{{ room.rate_plan_name }}</b>
                                    <button class="btn btn-success" v-if="room.onRequest ==1" style="border-radius: 20px;">OK</button>
                                    <button class="btn btn-danger" v-if="room.onRequest ==0" style="border-radius: 20px;">RQ</button>
                                </h5>
                                <div class="price">
                                    $ <b>@{{ roundLito(room.total_amount) }}</b>
                                    {{--<i class="icon-chevron-down"></i>--}}
                                </div>
                                <div class="cantidad">
                                    <p><b>@{{ room.adult_num + room.extra_num }}</b> adultos</p>
                                    <p><b>@{{ room.child_num }}</b> niños</p>
                                </div>
                                <div class="servicios">
                                    <p>@{{ room.meal_name }}</p>
                                    <p v-for="penalty in room.policies_cancellation">@{{ penalty.message }}</p>
                                </div>

                                <div class="suplementos" v-for="supplement_room in room.supplements">
                                    <p>
                                        <span class="fa fa-circle"></span>
                                        <b>Suplemento: @{{ supplement_room.supplement }}</b>
                                        <b v-for="supplement_date in supplement_room.calendaries"> | @{{
                                            formatDate(supplement_date.date) }} </b>|
                                        <span class="price">
                                            $ <b>@{{ roundLito(supplement_room.total_amount) }}</b>
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <div class="notas">
                                <div class="price">Total<span></span> $<b>@{{ roundLito(hotel.total_amount) }}</b></div>
                            </div>
                        </div>
                        <div class="hotel-content-shopping"
                             v-for="(service,index_service) in cart_detail.reservations_service">
                            <div class="img-shopping">
                                <img :src="service.service_logo" alt="Image Service"
                                     onerror="this.src = baseURL + 'images/hotel-default.jpg'">
                            </div>
                            <div class="content-shopping">
                                <span class="tipo">@{{ service.service_code }}</span>
                                <span class="badge badge-success" v-if="service.on_request == 0">OK</span>
                                <span class="badge badge-danger" v-if="service.on_request == 1">RQ</span>
                                <h3 class="text-left">@{{ service.service_name }}
                                </h3>
                                <div class="date-shopping">
                                    <i class="icon-calendar"></i>
                                    <span>@{{ formatDate(service.date) }}</span>
                                    <span><b>@{{ service.adult_num }}</b> adultos</span>
                                    <span><b>@{{ service.child_num }}</b> niños</span>
                                </div>
                            </div>
                            <div class="notas">
                                <div class="price">Total: <span></span> $<b>@{{ roundLito(service.total_amount) }}</b></div>
                            </div>
                        </div>
                        <div class="no-gutters total">
{{--                            <h3>Subtotal <span--}}
{{--                                    style="float: right;">USD $ <b>@{{ cart_detail.total_hotels_subs }}</b></span></h3>--}}
{{--                            <h3>Impuestos y Servicios<span style="float: right;">USD $ <b>@{{ cart_detail.total_tax_and_services_amount }}</b></span>--}}
{{--                            </h3>--}}
                            {{-- <h3>Total a pagar <span style="float: right;">USD $ <b>@{{ roundLito(cart_detail.total) }}</b></span></h3> --}}

                            <h3>Total a pagar <span style="float: right;">USD $ <b>@{{ roundLito(total_ultimo_reservado) }}</b></span></h3>



                        </div>
                    </div>

                    <div class="personal-data" v-if="hasHotels() > 0 || hasServices() > 0">
                        <div class="personal-data-item">
                            <h4>Información del File</h4>
                            <div class="" v-if="user_type_id == 1 ">
                                <h4 class="col-sm-9">Excecutive</h4>
                                <select class="custom-select" name="" id="executive_select"
                                        v-model="cart_detail.executive_id" disabled>
                                    <option :value="executive_id" v-for="(executive,executive_id) in executives">@{{
                                        executive}}
                                    </option>
                                </select>
                            </div>
                            <div class="my-4">
                                <span class="text-danger">{{ trans('reservations.label.your_transaction_message_confirmation') }}</span>
                            </div>
                            <div class="">
                                <input placeholder="Reference " type="text" id="customer_name"
                                       v-model="cart_detail.customer_name" disabled>
                            </div>
                            <hr>
                            <h4>Datos de Pasajeros</h4>
                            <div>
                                <b>@{{ totalHotelAdults }}</b> adulto(s) <span></span>
                                <b>@{{ totalHotelChilds }}</b> niño(s)
                            </div>
                            <hr>
                            <small class="text-default help_message">
                                <i class="fas fa-info-circle"></i> {{ trans('reservations.label.help_message_transaction_number') }}
                                : @{{ cart_detail.file_code }}
                            </small>
                        </div>
{{--                        <div class="personal-data-item">--}}

{{--                        </div>--}}
                    </div>
                </div>
            </div>
        </div>
    </section>
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
          total_ultimo_reservado:0,
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
          total_child_passengers: []
        },
        created: function () {
        },
        mounted () {
          if (this.user_type_id === 3) {
            this.getClientsByExecutive()
          }

          this.getReservation()
        },
        computed: {},
        methods: {
            roundLito: function (num) {
                num = parseFloat(num)
                num = ( num ).toFixed(2);

                if (num != null) {
                    var res = String(num).split('.');
                    var nEntero = parseInt(res[0]);
                    var nDecimal = 0;
                    if (res.length > 1)
                        nDecimal = parseInt(res[1]);

                    var newDecimal;
                    if (nDecimal < 25) {
                        newDecimal = 0;
                    } else if (nDecimal >= 25 && nDecimal < 75) {
                        newDecimal = 5;
                    } else if (nDecimal >= 75) {
                        nEntero = nEntero + 1;
                        newDecimal = 0;
                    }

                    return parseFloat( String(nEntero) + '.' + String(newDecimal) );
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
          totalGuestHotels: function () {
            Object.entries(this.cart_detail.reservations_hotel).forEach(([key, resHotel]) => {
              this.totalHotelAdults = 0
              this.totalHotelChilds = 0
              Object.entries(resHotel.reservations_hotel_rooms).forEach(([key, resHotelRoom]) => {
                this.totalHotelAdults += resHotelRoom.adult_num + resHotelRoom.extra_num
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
                  this.client_id = this.clients[0].client_id
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
                  this.cart_detail = result.data.data
                  this.total_ultimo_reservado = result.data.data.total;
                  this.totalGuestHotels()
                  this.setGuestRoomNotes()
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
                this.total_ultimo_reservado = result.data.total_reserva;
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
          }
        }
      })
    </script>
@endsection
<style>
    .circle_red{
        color: #dc3545!important;
    }
</style>
