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
        {{--        <loading-component v-show="blockPage"></loading-component>--}}
        <div class="container">
            <div class="motor-busqueda">
                <h2>{{ trans('reservations.label.title_cart') }}</h2>
                <div class="content-data cart cart-view">
                    <div class="shopping-cart">
                        <div class="hotel-content-shopping">
                            <div class="img-shopping">
                                <img src="/images/demo/paquetes/foto4.jpg" alt="Image Package">
                            </div>
                            <div class="content-shopping">
                                <h3 class="text-left">@{{ name_package }}</h3>
                                <div class="date-shopping">
                                    <i class="icon-calendar"></i>
                                    <span>@{{ formatDate(date_reserve) }}</span>

                                    <span><b>@{{ quantity_adults }}</b> adultos</span>
                                    <span><b>@{{ quantity_child }}</b> niños</span>

                                </div>
                            </div>
                            <div class="notas">
                                <div class="price">Total: <span></span> $<b>@{{ roundLito(total) }}</b></div>
                            </div>
                        </div>
                        <div class="no-gutters total">
                            <h3>Total a pagar <span
                                    style="float: right;">USD $ <b>@{{ roundLito(total) }}</b></span></h3>
                        </div>
                    </div>
                    <div class="personal-data">
                        <div class="personal-data-item">
                            <div class="my-4">
                                <span class="text-primary font-">{{ trans('reservations.label.your_transaction_message_confirmation') }}</span>
                            </div>
                            <div class="">
                                <input placeholder="Reference" type="text" :value="reference" readonly="true">
                            </div>
                            <hr>
                            <small class="text-default help_message">
                                <i class="fas fa-info-circle"></i> {{ trans('reservations.label.help_message_transaction_number') }}
                                : @{{ file_number }}
                            </small>
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
                show_passengers_modal: false,
                show_passenger_save: false,
                detailPassengers: [],
                modePassenger: 1,
                repeatPassenger: 0,
                passengers: [],
                totalPassengers: [],
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
                executives: {
                    '15': 'ejecutivo 01',
                    '22': 'ejecutivo 02',
                    '1': 'ejecutivo 03',
                    '44': 'ejecutivo 04',
                    '56': 'ejecutivo 05',
                },
                customer_country: '',
                // countries: {
                //     'PE': 'Peru',
                //     'CH': 'Chile',
                //     'AR': 'Argentina',
                // }
                contents_atemps: 0,
                car_contents_atemps: 0,
                car_details_atemps: 0,
                dialog_message: {
                    title: 'Load Problem',
                    body: 'There is a problem load the interface, try again?',
                },
                total_adult_passengers: [],
                total_child_passengers: [],
                //reserve Details
                name_package: '',
                date_reserve: '',
                image_package: '',
                quantity_adults: 0,
                quantity_child: 0,
                file_number: '',
                reference: '',
                total: 0

            },
            created: function () {

            },
            mounted () {
                this.getPackageReserveDetails()
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
                getPackageReserveDetails: function () {
                    axios.get(baseExternalURL + 'api/packages/save/reserve_details')
                        .then((result) => {
                            this.name_package = result.data.name_package
                            this.date_reserve = result.data.date_reserve
                            this.image_package = result.data.image_package
                            this.quantity_adults = result.data.quantity_adults
                            this.quantity_child = result.data.quantity_child
                            this.totalPassengersNum = this.quantity_adults + this.quantity_child
                            this.file_number = result.data.booking_code
                            this.reference = result.data.reference
                            this.total = result.data.total
                        }).catch((e) => {

                    })
                },
                getDashboardData: function () {
                    this.blockPage = true
                    axios.get(
                        baseExternalURL + 'contents', {
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

                            // + Prices of multiservices
                            result.data.cart.total_cart = parseFloat( result.data.cart.total_cart )
                            result.data.cart.services.forEach( (s)=>{
                                s.service.components.forEach( (c)=>{
                                    s.total_service += c.total_amount
                                    result.data.cart.total_cart += c.total_amount
                                })
                                s.total_service = this.roundLito( s.total_service )
                            })
                            // + Prices of multiservices

                            this.cart = result.data.cart
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
                pushReservation: function () {
                    if ((!this.passengers[0].nombres || !this.passengers[0].apellidos) && !this.cart_detail.file_code) {
                        alert('El nombre y apellido del pasajeros principal son abligatorios')
                        this.showModal('show_passengers_modal')
                    } else {
                        this.msg = 'Procesando su reserva por favor espere....'
                        this.blockPage = true
                        axios.post(
                            'services/hotels/reservation/add',
                            {
                                client_id: this.client_id,
                                executive_id: this.cart_detail.executive_id,
                                file_code: this.cart_detail.file_code,
                                reference: this.cart_detail.customer_name,
                                guests: [
                                    {
                                        given_name: this.passengers[0].nombres,
                                        surname: this.passengers[0].apellidos,
                                    }
                                ],
                                reservations: this.getReservationDetail(),
                                reservations_services: this.getReservationService(),
                            }
                        ).then((result) => {
                            if (result.data.success) {
                                this.cart_detail.file_code = result.data.data.file_code

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
                                rate.show_message_error = true
                                rate.message_error = result.data.error
                            }
                        }).catch((e) => {
                            this.blockPage = false
                            console.log(e)
                        })
                    }
                },
                modalPassengers: function () {
                    this.$refs.modal_passengers.modalPassengers(this.file_number, this.totalPassengersNum, this.quantity_adults, this.quantity_child, 0)
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
                    this.passengers = []
                    this.repeatPassenger = 1
                    this.modePassenger = 1
                    this.totalPassengers = []

                    this.detailPassengers.canadl = 0
                    this.detailPassengers.canchd = 0
                    this.detailPassengers.caninf = 0

                    Object.entries(this.cart_detail.reservations_hotel).forEach(([key, resHotel]) => {
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

                    var adult_max = Math.max(...this.total_adult_passengers)
                    var child_max = Math.max(...this.total_child_passengers)

                    this.totalHotelAdults = adult_max
                    this.totalHotelChilds = child_max

                    this.detailPassengers.canadl = this.totalHotelAdults
                    this.detailPassengers.canchd = this.totalHotelChilds
                    this.detailPassengers.caninf = 0

                    for (i = 1; i <= this.detailPassengers.canadl; i++) {
                        this.passengers.push({})
                        this.fillPassenger(i - 1, 'ADL')
                        this.totalPassengers.push(i)
                    }

                    if (this.detailPassengers.canchd > 0) {
                        for (i = (this.detailPassengers.canadl + 1); i <= (this.detailPassengers.canadl + this.detailPassengers.canchd); i++) {
                            this.passengers.push({})
                            this.fillPassenger(i - 1, 'CHD')
                            this.totalPassengers.push(i)
                        }
                    }

                    if (this.detailPassengers.caninf > 0) {
                        for (i = (this.detailPassengers.canadl + this.detailPassengers.canchd + 1); i <= (this.detailPassengers.canadl + this.detailPassengers.canchd + this.detailPassengers.caninf); i++) {
                            this.passengers.push({})
                            this.fillPassenger(i - 1, 'INF')
                            this.totalPassengers.push(i)
                        }
                    }
                    this.totalPassengersNum = this.passengers.length
                },
                fillPassenger: function (passenger_ind, _tipo) {
                    eval('this.passengers[' + passenger_ind + '].tipo = \'' + _tipo + '\'')
                    eval('this.passengers[' + passenger_ind + '].nrosec = 0')
                    eval('this.passengers[' + passenger_ind + '].nroref = ' + this.cart_detail.file_code)
                    eval('this.passengers[' + passenger_ind + '].nombres = \'\'')
                    eval('this.passengers[' + passenger_ind + '].apellidos = \'\'')
                    eval('this.passengers[' + passenger_ind + '].sexo = \'\'')
                    eval('this.passengers[' + passenger_ind + '].fecnac = \'\'')
                    eval('this.passengers[' + passenger_ind + '].tipdoc = \'\'')
                    eval('this.passengers[' + passenger_ind + '].nrodoc = \'\'')
                    eval('this.passengers[' + passenger_ind + '].nacion = \'\'')
                    eval('this.passengers[' + passenger_ind + '].correo = \'\'')
                    eval('this.passengers[' + passenger_ind + '].celula = \'\'')
                    eval('this.passengers[' + passenger_ind + '].observ = \'\'')
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
                                    rate_plan_id: val.options.rate_id,
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
                                date_from: val.options.date_from,
                                quantity_adults: val.options.search.quantity_adult,
                                quantity_child: val.options.search.quantity_child,
                                child_ages: []
                            }
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
