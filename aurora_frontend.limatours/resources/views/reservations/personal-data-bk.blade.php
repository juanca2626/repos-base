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
    <section class="page-reservation">
        <loading-component v-show="blockPage"></loading-component>
        <div class="container">
            <div class="motor-busqueda">
                <h2>{{ trans('reservations.label.title_cart') }}</h2>
                <h3>{{ trans('cart_view.label.you_have') }} @{{ cart.quantity_items
                    }} {{ trans('cart_view.label.products_your_shopping_cart') }}</h3>
                <div class="content-data cart cart-view cart-reservations" v-if="hasHotels() > 0 || hasServices() > 0 ">
                    <div class="shopping-cart">
                        <div class="hotel-content-shopping"
                             v-for="(hotel,index_hotel) in cart_detail.reservations_hotel">
                            <div class="img-shopping">
                                <img :src="baseExternalURL+'images/'+hotel.hotel_logo" alt="Image Hotel"
                                     onerror="this.src = baseURL + 'images/hotel-default.jpg'">
                            </div>
                            <div class="content-shopping">
                                {{--                                <span class="tipo">Turista</span>--}}
                                <h3 class="text-left">@{{ hotel.hotel_name }}
                                    {{--<span v-for="n in parseFloat(cart_detail.hotels[hotel.hotel_id].stars, 10) " class="icon-star"></span>--}}
                                </h3>
                                <div class="date-shopping">
                                    <i class="icon-calendar"></i>
                                    <span>@{{ formatDate(hotel.check_in) }}</span> <span>@{{ formatDate(hotel.check_out) }}</span>
                                </div>
                            </div>
                            <div class="car-room" v-for="(room,roomIndet) in hotel.reservations_hotel_rooms">
                                <h5>
                                    <span class="fa fa-circle" v-bind:class="{circle_red : room.onRequest ==0}"></span>
                                    @{{ room.room_name }} -
                                    <b> @{{ room.rate_plan_name }}</b>
                                    <button class="btn btn-success" v-if="room.onRequest ==1"
                                            style="border-radius: 20px;">OK
                                    </button>
                                    <button class="btn btn-danger" v-if="room.onRequest ==0"
                                            style="border-radius: 20px;">RQ
                                    </button>
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
                                <b-button v-b-toggle="'notas-'+index_hotel">
                                    <i class="icon-edit"></i>{{trans('reservations.label.include_notes')}}
                                </b-button>
                                <div class="price">Total: <span></span> $<b>@{{ roundLito(hotel.total_amount) }}</b>
                                </div>

                                <b-collapse :id="'notas-'+index_hotel">
                                    <b-card>
                                        <textarea placeholder="Notas"
                                                  v-model="hotel_room_comments[getFirstRoomKey(index_hotel)]"></textarea>
                                    </b-card>
                                </b-collapse>
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
                                <h3 class="text-left">@{{ service.service_name }}</h3>
                                <div class="date-shopping">
                                    <i class="icon-calendar"></i>
                                    <span>@{{ formatDate(service.date) }}</span>

                                    <span><b>@{{ service.adult_num }}</b> {{ trans('hotel.label.adults') }}</span>
                                    <span><b>@{{ service.child_num }}</b> {{ trans('hotel.label.child') }}</span>
                                    <div class="price">
                                        $ <b>@{{ service.total_amount }}</b>
                                        {{--<i class="icon-chevron-down"></i>--}}
                                    </div>
                                    {{--                                    <span style="font-size: 12px">@{{ service.rate.rate_plans[0].political.cancellation.details.penalties[0].message }}</span>--}}
                                </div>
                                <div class="car-room" style="padding: 0px;">
                                    <div class="suplementos" v-for="supplement in service.supplements">
                                        <p>
                                            <span class="fa fa-circle"></span>
                                            <b>Suplemento: @{{ supplement.supplement_name }}</b>
                                            <span class="price">
                                            $ <b>@{{ supplement.total_amount }}</b>
                                        </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="notas">
                                <b-button v-b-toggle="'notas-service'+index_service">
                                    <i class="icon-edit"></i>{{ trans('reservations.label.include_notes') }}
                                </b-button>
                                <div class="price">Total: <span></span> $<b>@{{ service.total_amount +
                                        service.total_service_supplement_selected }}</b>
                                </div>

                                <b-collapse :id="'notas-service'+index_service">
                                    <b-card>
                                        <textarea placeholder="Notas"
                                                  v-model="service_comments[index_service]"></textarea>
                                    </b-card>
                                </b-collapse>
                            </div>
                        </div>
                        <div class="no-gutters total">
                            <h3>{{ trans('cart_view.label.total_to_pay') }} <span
                                    style="float: right;">USD $ <b>@{{ roundLito(cart_detail.total) }}</b></span>
                            </h3>
                        </div>
                        <button class="btn btn-primary btn-car"
                                @click="pushReservation()">{{ trans('cart_view.label.reserve') }}</button>
                    </div>
                    <div class="personal-data">
                        <div class="personal-data-item">
                            <h4>{{trans('reservations.label.file_information')}}</h4>
                            <div class="" v-if="user_type_id == 1 ">
                                <h4 class="col-sm-9">Excecutive</h4>
                                <select class="custom-select" name="" id="executive_select"
                                        v-model="cart_detail.executive_id">
                                    <option :value="executive_id" v-for="(executive,executive_id) in executives">@{{
                                        executive}}
                                    </option>
                                </select>
                            </div>
                            <div class="">
                                <input placeholder="File #" type="text" id="file_code" v-model="cart_detail.file_code">
                            </div>
                            <div class="">
                                <input placeholder="Reference #" type="text" id="reference"
                                       v-model="cart_detail.customer_name">
                            </div>
                        </div>
                        <div class="personal-data-item">
                            <h4>{{trans('reservations.label.passenger_data')}}</h4>
                            <div class="mb-3">
                                <b>@{{ totalHotelAdults }}</b> {{ trans('hotel.label.adults') }} <span></span>
                                <b>@{{ totalHotelChilds }}</b> {{ trans('hotel.label.child') }}
                                <br>
                            </div>
                            <b-button v-on:click="modalPassengers()" class="btn-lg">
                                <i class="icon-edit"></i> {{trans('reservations.label.enter_data')}}
                            </b-button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!------- Modal Datos pasajeros ------->
    <modal-passengers ref="modal_passengers"></modal-passengers>
    <!------- End Modal PAX PASAJEROS ------->
@endsection

@section('js')
    <script>
        new Vue({
            el: '#app',
            data: {
                msg: 'Por favor espere cargando ....',
                csrf_token: '',
                route_name: '',
                blockPage: true,
                update_menu: 1,
                user_type_id: localStorage.getItem('user_type_id'),
                baseExternalURL: window.baseExternalURL,
                baseURL: window.baseURL,
                client_id: 1,
                clients: [],
                cart: [],
                countries: [],
                doctypes: [],
                cart_detail: {
                    executive_id: '',
                    customer_name: '',
                    reference: '',
                    given_name: '',
                    surname: '',
                    file_code: '',
                    customer_country: '',
                    room_comments: [],
                    reservations_hotel: [],
                    reservations_service: []
                },
                totalPassengersNum: 0,
                loadingModal: false,
                totalHotelAdults: 0,
                totalHotelChilds: 0,
                totalServiceAdults: 0,
                totalServiceChilds: 0,
                hotel_room_comments: [],
                service_comments: [],
                cart_total: [],
                cart_hotel_name: '',
                customer_country: '',
                contents_atemps: 0,
                car_contents_atemps: 0,
                car_details_atemps: 0,
                dialog_message: {
                    title: 'Load Problem',
                    body: 'There is a problem load the interface, try again?',
                },
                total_adult_passengers: [],
                total_child_passengers: []
            },
            created: function () {
                this.csrf_token = document.querySelector('meta[name=\'csrf-token\']').getAttribute('content')
            },
            mounted () {
                if (this.user_type_id === 3) {
                    this.getClientsByExecutive()
                }

                this.lang = localStorage.getItem('lang')
                this.client_id = localStorage.getItem('client_id')

                if (this.client_id == null) {
                    axios.delete(baseURL + 'cart/content/delete')
                        .then((result) => {
                            window.location = this.baseURL + 'home'
                        }).catch((e) => {
                        console.log(e)
                    })
                } else {
                    this.getDashboardData()
                }
            },
            computed: {},
            methods: {
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
                confirmReloadInteface: function () {

                },
                getDashboardData: function () {
                    this.blockPage = true
                    axios.get(
                        baseURL + 'contents', {
                            lang: this.lang,
                            executive: this.executive,
                            bossFlag: this.bossFlagSearch
                        }
                    )
                        .then((result) => {
                            this.countries = result.data.countries
                            this.doctypes = result.data.doctypes
                            this.getCartContent()
                            // this.getCartDetails();
                        })
                        .catch((e) => {
                            console.log(e)
                            if (this.contents_atemps < 5) {
                                this.contents_atemps++
                                this.getDashboardData()
                            } else {
                                this.$dialog.confirm(this.dialog_message)
                                    .then(dialog => {
                                        this.contents_atemps = 0
                                        this.getDashboardData()
                                    })
                                    .catch(() => {
                                        this.blockPage = false
                                    })
                            }
                        })
                },
                getCartContent: function () {
                    axios.get(
                        baseURL + 'cart'
                    ).then((result) => {
                        if (result.data.success === true) {
                            this.cart = result.data.cart
                            console.log(result.data)
                            this.cart_total = result.data.total
                            this.cart_hotel_name = result.data.hotel_name

                            if (Object.keys(this.cart.hotels).length || Object.keys(this.cart.services).length) {
                                console.log('carro cargado')

                                Object.entries(this.cart.cart_content).forEach(([key, resHotel]) => {
                                    this.hotel_room_comments[key] = ''
                                })

                                this.getCartDetails()
                            } else {
                                if (this.car_contents_atemps < 5) {
                                    this.car_contents_atemps++
                                    this.getCartContent()
                                } else {
                                    this.$dialog.confirm(this.dialog_message)
                                        .then(dialog => {
                                            this.car_contents_atemps = 0
                                            this.getCartContent()
                                        })
                                        .catch(() => {
                                            this.blockPage = false
                                        })
                                }
                            }
                        }
                    }).catch((e) => {
                        console.log(e)
                        this.$dialog.confirm(this.dialog_message)
                            .then(dialog => {
                                this.car_contents_atemps = 0
                                this.getCartContent()
                            })
                            .catch(() => {
                                this.blockPage = false
                            })
                    })
                },
                getCartDetails: function () {
                    if (Object.keys(this.cart.hotels).length || Object.keys(this.cart.services).length) {
                        this.blockPage = true
                        axios.post(
                            'services/hotels/reservation',
                            {
                                client_id: this.client_id,
                                executive_id: this.cart_detail.executive_id,
                                reservations: this.getReservationDetail(),
                                reservations_services: this.getReservationService(),
                            }
                        ).then((result) => {
                            if (result.data.success) {
                                this.cart_detail = result.data.data
                                this.totalGuestHotels()
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
                    } else {
                        if (this.car_details_atemps < 5) {
                            this.car_details_atemps++
                            this.getCartDetails()
                        } else {
                            this.$dialog.confirm(this.dialog_message)
                                .then(dialog => {
                                    this.car_details_atemps = 0
                                    this.getCartDetails()
                                })
                                .catch(() => {
                                    this.blockPage = false
                                })
                        }
                    }
                },
                setFormatPassengers: function (passengers) {
                    let passengers_data = []
                    for (let i = 0; i < passengers.length; i++) {
                        passengers_data.push({
                            'doctype_iso': (passengers[i].tipdoc == '-1') ? null : passengers[i].tipdoc,
                            'document_number': passengers[i].document_number,
                            'given_name': passengers[i].nombres,
                            'surname': passengers[i].apellidos,
                            'date_birth': (passengers[i].fecnac != '') ? moment(passengers[i].fecnac, 'DD/MM/YYYY').format('YYYY-MM-DD') : moment().format('YYYY-MM-DD'),
                            'genre': (passengers[i].sexo == '-1') ? null : passengers[i].sexo,
                            'type': passengers[i].tipo,
                            'dietary_restrictions': passengers[i].resali,
                            'medical_restrictions': passengers[i].resmed,
                            'email': passengers[i].correo,
                            'phone': passengers[i].celula,
                            'country_iso': (passengers[i].nacion == '-1') ? null : passengers[i].nacion,
                            'notes': passengers[i].observ,
                        })
                    }
                    return passengers_data
                },
                pushReservation: function () {
                    if (
                        (this.$refs.modal_passengers.passengers.length != this.totalPassengersNum ||
                            (
                                (this.$refs.modal_passengers.passengers[0].nombres == undefined || this.$refs.modal_passengers.passengers[0].nombres == '') &&
                                (this.$refs.modal_passengers.passengers[0].apellidos == undefined || this.$refs.modal_passengers.passengers[0].apellidos == '')
                            )
                        )) {
                        alert('El nombre y apellido del pasajeros principal son obligatorios')
                        this.modalPassengers()
                    } else {
                        this.msg = 'Procesando su reserva por favor espere....'
                        this.blockPage = true
                        let guests = this.setFormatPassengers(this.$refs.modal_passengers.passengers)
                        axios.post(
                            'services/hotels/reservation/add',
                            {
                                client_id: this.client_id,
                                executive_id: this.cart_detail.executive_id,
                                file_code: this.cart_detail.file_code,
                                reference: this.cart_detail.customer_name,
                                guests: guests,
                                reservations: this.getReservationDetail(),
                                reservations_services: this.getReservationService(),
                            }
                        ).then((result) => {
                            if (result.data.success) {
                                console.log(result.data.data.file_code)

                                this.cart_detail.file_code = result.data.data.file_code
                                let vm = this

                                if (this.cart_detail.file_code == parseInt(this.cart_detail.file_code, 10)) {
                                    this.msg = 'Procesando pasajeros por favor espere....'
                                    this.$refs.modal_passengers.setNroFile(this.cart_detail.file_code)
                                    this.$refs.modal_passengers.savePassengers()

                                    setTimeout(function () {
                                        vm.destroyCartAndRedirect(baseURL + 'reservations/' + vm.cart_detail.file_code)
                                    }, 100)
                                } else {
                                    this.destroyCartAndRedirect(baseURL + 'reservations/' + this.cart_detail.file_code)
                                }
                            } else {
                                this.blockPage = false
                                this.show_message_error = true
                                this.message_error = result.data.error
                            }
                        }).catch((e) => {
                            this.blockPage = false
                            console.log(e)
                        })
                    }
                },
                modalPassengers: function () {
                    this.$refs.modal_passengers.modalPassengers('session', this.cart_detail.file_code, this.totalPassengersNum, this.totalHotelAdults, this.totalHotelChilds, 0)
                },
                getFirstRoomKey: function (index_hotel) {
                    let rooms = []
                    Object.entries(this.cart_detail.reservations_hotel[index_hotel].reservations_hotel_rooms).forEach(([key, resHotel]) => {
                        rooms.push(key)
                    })
                    return rooms[0]
                },
                getFirstServiceKey: function (index_service) {
                    let services = []
                    Object.entries(this.cart_detail.reservations_service[index_service]).forEach(([key, resService]) => {
                        services.push(key)
                    })
                    return services[0]
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
                            this.total_adult_passengers.push(this.totalHotelAdults)
                            this.total_child_passengers.push(this.totalHotelChilds)
                        })
                    })

                    Object.entries(this.cart_detail.reservations_service).forEach(([key, resService]) => {
                        this.totalServicesAdults += resService.adult_num
                        this.totalServicesChilds += resService.child_num
                        this.total_adult_passengers.push(resService.adult_num)
                        this.total_child_passengers.push(resService.child_num)
                    })
                    console.log(this.total_adult_passengers, this.total_child_passengers)
                    var adult_max = Math.max(...this.total_adult_passengers)
                    var child_max = Math.max(...this.total_child_passengers)

                    this.totalHotelAdults = adult_max
                    this.totalHotelChilds = child_max

                    this.totalPassengersNum = this.totalHotelAdults + this.totalHotelChilds + 0
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
                getRoomComment: function (key) {
                    return this.hotel_room_comments[key]
                },
                getServiceComment: function (key) {
                    return this.service_comments[key]
                },
                getReservationDetail: function () {
                    const reservations = []
                    Object.entries(this.cart.cart_content).forEach(([key, val]) => {
                        if (val.options.product_type === 'hotel') {
                            if (val.options.best_option === true) {
                                dataRoom = {
                                    token_search: val.options.token_search,
                                    room_ident: key,
                                    hotel_id: val.options.hotel_id,
                                    best_option: true,
                                    rate_plan_room_id: val.options.rate_id,
                                    suplements: [],
                                    guest_note: this.getRoomComment(key),
                                    date_from: val.options.date_from,
                                    date_to: val.options.date_to,
                                    quantity_adults: val.options.room.quantity_adults,
                                    quantity_child: val.options.room.quantity_child,
                                    // quantity_infants : val.options.room.quantity_infants,
                                    // quantity_rooms : val.options.room.quantity_rooms,
                                    child_ages: [
                                        // {"age": "8",},
                                    ]
                                }
                            } else {
                                dataRoom = {
                                    token_search: val.options.token_search,
                                    room_ident: key,
                                    hotel_id: val.options.hotel_id,
                                    best_option: false,
                                    rate_plan_room_id: val.options.rate_id,
                                    suplements: [],
                                    guest_note: this.getRoomComment(key),
                                    date_from: val.options.date_from,
                                    date_to: val.options.date_to,
                                    quantity_adults: val.options.quantity_adults,
                                    quantity_child: val.options.quantity_child,
                                    // quantity_infants : val.options.quantity_infants,
                                    // quantity_rooms : val.options.quantity_rooms,
                                    child_ages: [
                                        // {"age": "8",},
                                    ]
                                }
                            }

                            $.each(val.options.rate.supplements.supplements, function (key_sup, val_sup) {
                                if (val_sup.type_req_opt == 'optional') {
                                    dataRoom.suplements.push({
                                        supplement_id: val_sup.supplement_id,
                                        amount_extra: val_sup.amount_extra,
                                        calendars: val_sup.calendars,
                                        date: val_sup.date,
                                        key: val_sup.key,
                                        options: val_sup.options,
                                        show_calendar: val_sup.show_calendar,
                                        supplement: val_sup.supplement,
                                        supplement_dates_selected: val_sup.supplement_dates_selected,
                                        total_amount: val_sup.total_amount,
                                        totals: val_sup.totals,
                                        type: val_sup.type,
                                        type_req_opt: val_sup.type_req_opt
                                    })
                                }
                            })

                            reservations.push(dataRoom)
                        }
                    })

                    return reservations
                },
                getReservationService: function () {
                    const reservations = []
                    Object.entries(this.cart.cart_content).forEach(([key, val]) => {
                        if (val.options.product_type === 'service') {
                            service = {
                                token_search: val.options.token_search,
                                service_ident: key,
                                service_id: val.options.service_id,
                                rate_plan_id: val.options.rate_id,
                                reservation_time: val.options.reservation_time,
                                guest_note: this.getServiceComment(key),
                                supplements: [],
                                date_from: val.options.date_from,
                                quantity_adults: val.options.search.quantity_adult,
                                quantity_child: val.options.search.quantity_child,
                                child_ages: []
                            }

                            // $.each(val.options.rate.supplements.supplements, function (key_sup, val_sup) {
                            //     if (val_sup.type_req_opt == 'optional') {
                            //         service.supplements.push(val_sup)
                            //     }
                            // })
                            reservations.push(service)
                        }
                    })
                    return reservations
                },
                destroyCartAndRedirect: function (redirect) {
                    axios.delete(baseURL + 'cart/content/delete')
                        .then((result) => {
                            window.location.href = redirect
                        }).catch((e) => {
                        console.log(e)
                    })
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
