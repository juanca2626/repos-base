@extends('layouts.app')
@section('content')
    <loading-component v-show="blockPage"></loading-component>
    <div class="shopping">
        <h3>
            <span class="icon-shopping-bag mr-2"></span> {{ trans('reservations.label.title_cart') }} <span
                class="tag-counter">(@{{ cart.quantity_items
                    }} {{ trans('reservations.label.products') }} )</span>
        </h3>
        <main class="cart-all">
            <div class="basket">
                {{--Servicios--}}
                <h4 class="subtitle" v-if="cart_detail.reservations_service.length > 0">
                    (@{{ cart.services.length }}) {{ trans('reservations.label.added_services') }}
                </h4>
                <div class="services_item" v-for="(service,index) in cart_detail.reservations_service">
                    <div class="blog-card overflow-card">
                        <div class="col-auto px-0">
                            <img :src="service.service_logo" alt="Image Service" class="photo"
                                 onerror="this.onerror=null;this.src='https://res.cloudinary.com/litomarketing/image/upload/c_scale,w_400/v1440093948/paragliding_in_Miraflores._123110314_dlyun3.jpg'">
                        </div>
                        <div class="col">
                            <div class="d-flex justify-content-between mb-3">
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
                                    <strong>(+ USD @{{ multiservice.total_amount }})</strong>
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
                            {{--------------------Fin Suplementos---------------------------------}}

                            <div class="d-flex align-items-center justify-content-between mt-5 mb-3">
                                <span class="mr-5"><span class="icon-users"></span> @{{ service.adult_num }} {{ trans('hotel.label.adults') }}</span>
                                <span class="mr-5" v-if="service.child_num > 0"><span
                                        class="icon-smile"></span> @{{ service.child_num }} {{ trans('hotel.label.child') }}</span>
                                <span class="price_"><span class="icon-dollar-sign1 mr-2"></span>@{{ service.total_amount }}</span>
                            </div>

                            <hr>
                            <div class="row">
                                <div class="col-12 d-flex justify-content-between">
                                    <div class="read-more">
                                        <a :href="'#notas-service'+index" data-toggle="collapse" role="button"
                                           aria-expanded="false"
                                           aria-controls="collapseExample2">
                                            <span
                                                class="icon-plus-circle mr-2"></span>{{trans('reservations.label.include_notes')}}
                                        </a>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="collapse" :id="'notas-service'+index">
                                        <textarea class="textarea-notas" v-model="service_comments[index]" cols="5"
                                                  rows="5"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                {{--End Servicios--}}

                {{--Hoteles--}}
                <h4 class="subtitle" v-if="cart_detail.reservations_hotel.length > 0">
                    (@{{ cart.hotels.length }}) {{ trans('reservations.label.hotels_added') }}
                </h4>
                <div class="hoteles_item" v-for="(hotel,index) in cart_detail.reservations_hotel">
                    <div class="blog-card">
                        <div class="meta">
                            <div class="photo">
                                <img :src="baseExternalURL+'images/'+hotel.hotel_logo" alt="Image Hotel" class="photo"
                                     onerror="this.src = baseURL + 'images/hotel-default.jpg'">
                            </div>
                            <ul class="details">
                            </ul>
                        </div>
                        <div class="description">
                            <div class="d-flex justify-content-between mb-2">
                                <h5 class="subtle d-flex align-items-start"><span class="icon-calendar-confirm mr-1"
                                                                                  style="font-size: 1.5rem;"></span>
                                    @{{ formatDate(hotel.check_in) }} <span class="icon-arrow-right"></span> @{{
                                    formatDate(hotel.check_out) }} </h5>
                            </div>
                            <h2 class="d-flex align-items-center">
                                @{{ hotel.hotel_name }}
                            </h2>
                            <div class="d-flex align-items-center justify-content-between">
                                <div class=""><span class="icon-bed-double mr-2"></span>
                                    @{{ Object.keys(hotel.reservations_hotel_rooms).length }}
                                    <span v-if="Object.keys(hotel.reservations_hotel_rooms).length === 1" class="text-lowercase">{{trans('reservations.label.room')}}</span>
                                    <span v-if="Object.keys(hotel.reservations_hotel_rooms).length > 1" class="text-lowercase">{{trans('reservations.label.rooms')}}</span>
                                </div>
                                <div class=""><span class="icon-users mr-2"></span>
                                    @{{ countTotalPeople(hotel) }}
                                    <span v-if="countTotalPeople(hotel) === 1" class="text-lowercase">{{trans('reservations.label.person')}}</span>
                                    <span v-if="countTotalPeople(hotel) > 1" class="text-lowercase">{{trans('reservations.label.people')}}</span>
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
                                        <span class="icon-users"></span> @{{ room.adult_num }} {{ trans('hotel.label.adults') }}
                                    </span>
                                    <span class="mr-5" v-if="room.child_num > 0">
                                        <span class="icon-smile"></span> @{{ room.child_num }} {{ trans('hotel.label.child') }}
                                    </span>
                                    <span class="price_">
                                        $ @{{ room.total_amount }}
                                    </span>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-12 d-flex justify-content-between">
                                    <div class="row read-more">
                                        <a :href="'#notas-'+index" data-toggle="collapse" role="button" style=" margin-left: 10px"
                                           aria-expanded="false" aria-controls="collapsenote">
                                            <span
                                                class="icon-plus-circle mr-2"></span>{{trans('reservations.label.include_notes')}}
                                        </a>
                                        <label style="float: left; margin-left: 10px" for="is_modification" @click="toggle_modification(hotel)">
                                            <input type="checkbox" name="is_modification" v-model="hotel_is_modification[hotel.hotel_id]"> Es una modificación
                                        </label>

                                        <label style="float: left; margin-left: 10px" class="alert alert-warning" v-if="hotel_is_modification[hotel.hotel_id]">
                                            Por favor llenar las notas y código de confirmación
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="collapse" :id="'notas-'+index">
                                            <textarea placeholder="Notas"
                                                      v-model="hotel_room_comments[hotel.hotel_id]" cols="5"
                                                      rows="5" class="textarea-notas"></textarea>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                {{--End Hoteles--}}
            </div>
            <aside>
                <div class="summary">
                    <div class="card card-cart">
                        <h2 class="title-card">
                            {{trans('reservations.label.my_products')}}
                        </h2>
                        <div>
                            <div class="d-flex justify-content-between mb-2" v-for="(service,index) in cart.services">
                                <div class="text-title">
                                    <span class="icon-check mr-1"></span> @{{ service.service_name }} (@{{
                                    service.service.quantity_adult + service.service.quantity_child }}
                                    <span v-if="(service.service.quantity_adult + service.service.quantity_child) === 1">{{trans('reservations.label.person')}}</span>
                                    <span v-if="(service.service.quantity_adult + service.service.quantity_child) > 1">{{trans('reservations.label.people')}}</span>)
                                    <span v-for="component in service.service.components">
                                        <br>
                                         + @{{ component.descriptions.name }}
                                    </span>
                                </div>
                                <div style="width: 90px;text-align: right;">USD $.@{{ service.total_service }}</div>
                            </div>
                            <div class="d-flex justify-content-between"
                                 v-for="(hotel,index) in cart_detail.reservations_hotel">
                                <div class="text-title"><span class="icon-check mr-1"></span>@{{ hotel.hotel_name }}
                                    (@{{ countTotalPeople(hotel) }}
                                    <span v-if="countTotalPeople(hotel) === 1">{{trans('reservations.label.person')}}</span>
                                    <span v-if="countTotalPeople(hotel) > 1">{{trans('reservations.label.people')}}</span>)
                                </div>
                                <div style="width: 90px;text-align: right;">USD $.@{{ hotel.total_amount }}</div>
                            </div>

                            <hr>
                            <div class="d-flex justify-content-between total">
                                <div>{{ trans('cart_view.label.total_to_pay') }}</div>
                                <div>USD $.@{{ cart.total_cart }}</div>
                            </div>
                        </div>
                        <hr>
                        <div class="mt-5">
                            <h4><strong>{{trans('reservations.label.file_information')}}</strong></h4>
                            <form>
                                <div class="" v-if="user_type_id == 1 ">
                                    <h4 class="col-sm-9">Excecutive</h4>
                                    <select class="custom-select" name="" id="executive_select"
                                            v-model="cart_detail.executive_id">
                                        <option :value="executive_id" v-for="(executive,executive_id) in executives">@{{
                                            executive}}
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group row">
                                    <label for="" class="col-5 col-form-label">File #</label>
                                    <div class="col-7">
                                        <input type="text" placeholder="File #" class="form-control"
                                               style="height: 30px;" id="file_code" v-model="cart_detail.file_code">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="" class="col-5 col-form-label">Reference #</label>
                                    <div class="col-7">
                                        <input type="text" class="form-control" placeholder="Reference #"
                                               style="height: 30px;" v-model="cart_detail.customer_name">
                                    </div>
                                </div>
                            </form>

                        </div>
                        <div class="mt-5">
                            <div class="personal-data-item">
                                <h4><strong>{{trans('reservations.label.passenger_data')}}</strong></h4>
                                <div class="mb-3 d-flex align-items-center">
                                    <div class="mr-5"><span
                                            class="icon-users mr-2"></span><span><b>@{{ totalHotelAdults }} </b> {{ trans('hotel.label.adults') }}</span>
                                    </div>
                                    <div class="mr-5"><span class="icon-smile mr-2"></span><span><b>@{{ totalHotelChilds }} </b> {{ trans('hotel.label.child') }}</span>
                                    </div>
                                    <b-button v-on:click="modalPassengers()" class="btn-lg">
                                        <i class="icon-edit"></i> {{trans('reservations.label.enter_data')}}
                                    </b-button>
                                </div>
                            </div>

                        </div>
                        <div class="my-3 mt-5">
                            <p class="alert alert-warning mb-3" v-if="error !== ''">@{{ error }}</p>
                            <button class="btn btn-primary btn-car" v-bind:disabled="error != ''" style="width: 100%"
                                    @click="pushReservation()">{{ trans('cart_view.label.reserve') }}</button>
                        </div>

                    </div>

                </div>
            </aside>
        </main>
    </div>
    <!------- Modal Datos pasajeros ------->
    <modal-passengers ref="modal_passengers"></modal-passengers>
@endsection
@section('js')
    <script>
        new Vue({
            el: '#app',
            data: {
                error: '',
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
                cart: {
                    hotels: [],
                    services: [],
                    total_cart: 0,
                    quantity_items: 0
                },
                countries: [],
                doctypes: [],
                cart_detail: {
                    total: 0,
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
                hotel_is_modification: [],
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
                this.getFiltersFile()
                this.updateMenu()
            },
            computed: {},
            methods: {
                updateMenu: function () {
                    this.$emit('updateMenu')
                },
                toggle_modification(hotel){
                    console.log(hotel)
                    console.log(this.hotel_is_modification[hotel.hotel_id] )
                    // this.hotel_is_modification[hotel.hotel_id] = !(this.hotel_is_modification[hotel.hotel_id])
                },
                getFiltersFile: function () {
                    axios.get(baseURL + 'filter_hotels_file')
                        .then((result) => {
                            if (result.data != null) {
                                this.cart_detail.file_code = result.data.file
                            }
                        }).catch((e) => {
                        console.log(e)
                    })
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

                            // + Prices of multiservices
                            result.data.cart.total_cart = parseFloat(result.data.cart.total_cart.replace(/[^\d\.\-eE+]/g, ''))
                            result.data.cart.services.forEach((s) => {
                                s.service.components.forEach((c) => {
                                    if (!(c.removed)) {
                                        s.total_service += c.total_amount
                                        result.data.cart.total_cart += c.total_amount
                                    }
                                })
                            })
                            // + Prices of multiservices

                            this.cart = result.data.cart
                            this.cart_total = result.data.total
                            this.cart_hotel_name = result.data.hotel_name

                            if (Object.keys(this.cart.hotels).length || Object.keys(this.cart.services).length) {
                                Object.entries(this.cart.cart_content).forEach(([key, resHotel]) => {

                                    if(resHotel.options.hotel_id != undefined)
                                    {
                                        let _notes = localStorage.getItem('notes_hotel_carrito_' + resHotel.options.hotel_id)
                                        let _is_modification = localStorage.getItem('is_modification_hotel_carrito_' + resHotel.options.hotel_id)

                                        Vue.set(this.hotel_room_comments, resHotel.options.hotel_id, ((_notes != undefined && _notes != '') ? _notes : ''))
                                        Vue.set(this.hotel_is_modification, resHotel.options.hotel_id, ((_is_modification != null && _is_modification != '' && _is_modification !== "false" ) ? 1 : 0))
                                    }
                                    else
                                    {
                                        Vue.set(this.hotel_room_comments, resHotel.options.hotel_id, '')
                                        Vue.set(this.hotel_is_modification, resHotel.options.hotel_id, '')
                                    }
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
                            this.getFiltersFile()
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
                distribute_subs (services) {
                    services.forEach((s) => {
                        s.supplements = []
                        s.multiservices = []
                    })
                    services.forEach((s) => {
                        if (s.type_service === 'multiservice') {
                            let code_split = s.parent_id.split('-')
                            let parent_service_id = parseInt(code_split[0])
                            let parent_date = code_split[1] + '-' + code_split[2] + '-' + code_split[3]
                            services.forEach((s_, s_k) => {
                                if (s_.service_id === parent_service_id && s_.date === parent_date) {
                                    services[s_k].multiservices.push(s)
                                }
                            })
                        }
                        if (s.type_service === 'supplement') {
                            let code_split = s.parent_id.split('-')
                            let parent_service_id = parseInt(code_split[0])
                            let parent_date = code_split[1] + '-' + code_split[2] + '-' + code_split[3]
                            services.forEach((s_, s_k) => {
                                if (s_.service_id === parent_service_id && s_.date === parent_date) {
                                    services[s_k].supplements.push(s)
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
                                result.data.data.reservations_service = this.distribute_subs(result.data.data.reservations_service)
                                this.cart_detail = result.data.data
                                if (this.cart_detail.file_code == null) {
                                    this.getFiltersFile()
                                }
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
                    console.log(passengers)
                    let passengers_data = []
                    for (let i = 0; i < passengers.length; i++) {
                        passengers_data.push({
                            'sequence_number': (passengers[i].nrosec && passengers[i].nrosec !== '') ? passengers[i].nrosec : i + 1,
                            'doctype_iso': (passengers[i].tipdoc == '-1') ? '' : passengers[i].tipdoc,
                            'document_number': (passengers[i].document_number) ? passengers[i].document_number : passengers[i].nrodoc,
                            'given_name': passengers[i].nombres,
                            'surname': passengers[i].apellidos,
                            'date_birth': (passengers[i].fecnac != '' && passengers[i].fecnac != null) ? moment(passengers[i].fecnac, 'DD/MM/YYYY').format('YYYY-MM-DD') : null,
                            'genre': (passengers[i].sexo == '-1') ? '' : passengers[i].sexo,
                            'type': passengers[i].tipo,
                            'dietary_restrictions': passengers[i].resali,
                            'medical_restrictions': passengers[i].resmed,
                            'email': passengers[i].correo,
                            'phone': passengers[i].celula,
                            'country_iso': (passengers[i].nacion == '-1') ? '' : passengers[i].nacion,
                            'city_iso': (passengers[i].city_ifx_iso) ? passengers[i].city_ifx_iso : passengers[i].ciunac,
                            'notes': passengers[i].observ,
                        })
                    }
                    return passengers_data
                },
                pushReservation: function () {
                    let _passengers = this.setFormatPassengers(this.$refs.modal_passengers.getPassengers())
                    let _passenger = (_passengers.length > 0) ? _passengers[0] : {}

                    if (this.cart_detail.file_code == '' || this.cart_detail.file_code == null) {
                        if (
                            (_passenger.surname == undefined || _passenger.surname == '') &&
                            (_passenger.given_name == undefined || _passenger.given_name == '')
                        ) {
                            alert('El nombre y apellido del pasajeros principal son obligatorios')
                            this.modalPassengers(true)
                        } else {
                            this.msg = 'Procesando su reserva por favor espere....'
                            this.blockPage = true
                            this.processReservation()
                        }
                    } else {
                        this.modalPassengers(false)
                        this.msg = 'Procesando su reserva por favor espere....'
                        this.blockPage = true
                    }
                },
                processReservation: function () {
                    this.error = this.$refs.modal_passengers.getError()
                    let _passengers = this.setFormatPassengers(this.$refs.modal_passengers.getPassengers())

                    if(this.error == '')
                    {
                        axios.post(
                            'services/hotels/reservation/add',
                            {
                                client_id: this.client_id,
                                executive_id: this.cart_detail.executive_id,
                                file_code: this.cart_detail.file_code,
                                reference: this.cart_detail.customer_name,
                                guests: _passengers,
                                reservations: this.getReservationDetail(),
                                reservations_services: this.getReservationService(),
                                entity: 'Cart',
                                object_id: null
                            }
                        ).then((result) => {
                            if (result.data.success) {
                                this.cart_detail.file_code = result.data.data.file_code
                                let vm = this
                                console.log(this.cart_detail.file_code)
                                console.log(parseInt(this.cart_detail.file_code, 10))
                                if (this.cart_detail.file_code == parseInt(this.cart_detail.file_code, 10)) {
                                    this.msg = 'Procesando pasajeros por favor espere....'
                                    this.$refs.modal_passengers.setNroFile(this.cart_detail.file_code)
                                    this.$refs.modal_passengers.savePassengers(false)
                                } else {
                                    this.destroyCartAndRedirect()
                                }
                            } else {
                                this.blockPage = false
                                this.show_message_error = true
                                this.message_error = result.data.error
                                this.$toast.error(this.message_error, {
                                    position: 'top-right'
                                })
                            }
                        }).catch((e) => {
                            this.blockPage = false
                            console.log(e)
                        })
                    }
                    else
                    {
                        this.blockPage = false
                    }
                },
                modalPassengers: function (modal) {
                    this.$refs.modal_passengers.modalPassengers((this.cart_detail.file_code != null && this.cart_detail.file_code != undefined && this.cart_detail.file_code != '') ? 'file' : 'session', this.cart_detail.file_code, this.totalPassengersNum, this.totalHotelAdults, this.totalHotelChilds, 0, modal)
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
                            this.totalHotelAdults += resHotelRoom.adult_num
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
                getIsModification: function (key) {
                    let response = this.hotel_is_modification[key]
                    response = (response || response == "true") ? 1 : 0
                    return response
                },
                getRoomComment: function (key) {
                    console.log('Key Room ----')
                    console.log(key)
                    console.log('---')
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
                                    guest_note: this.getRoomComment(val.options.hotel_id),
                                    is_modification: this.getIsModification(val.options.hotel_id),
                                    date_from: val.options.date_from,
                                    date_to: val.options.date_to,
                                    quantity_adults: val.options.room.quantity_adults,
                                    quantity_child: val.options.room.quantity_child,
                                    // quantity_infants : val.options.room.quantity_infants,
                                    // quantity_rooms : val.options.room.quantity_rooms,
                                    child_ages: val.options.ages_child,
                                }
                            } else {
                                dataRoom = {
                                    token_search: val.options.token_search,
                                    room_ident: key,
                                    hotel_id: val.options.hotel_id,
                                    best_option: false,
                                    rate_plan_room_id: val.options.rate_id,
                                    suplements: [],
                                    multiservices: [],
                                    guest_note: this.getRoomComment(val.options.hotel_id),
                                    is_modification: this.getIsModification(val.options.hotel_id),
                                    date_from: val.options.date_from,
                                    date_to: val.options.date_to,
                                    quantity_adults: val.options.quantity_adults,
                                    quantity_child: val.options.quantity_child,
                                    // quantity_infants : val.options.quantity_infants,
                                    // quantity_rooms : val.options.quantity_rooms,
                                    child_ages: val.options.ages_child,
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

                            $.each(val.options.search.components, function (key_multi, val_multi) {
                                service.multiservices.push({
                                    token_search: val_multi.token_search,
                                    service_ident: key_multi,
                                    service_id: val_multi.service_id,
                                    rate_plan_id: val_multi.rate.id,
                                    reservation_time: val.reservation_time,
                                    guest_note: '',
                                    date_from: val_multi.date_reserve,
                                    quantity_adults: val.options.search.quantity_adult,
                                    quantity_child: val.options.search.quantity_child,
                                    child_ages: []
                                })
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
                                multiservices: [],
                                date_from: val.options.date_from,
                                quantity_adults: val.options.search.quantity_adult,
                                quantity_child: val.options.search.quantity_child,
                                child_ages: []
                            }
                            $.each(val.options.search.supplements.supplements, function (key_sup, val_sup) {
                                service.supplements.push({
                                    token_search: val_sup.token_search,
                                    adults: val_sup.params.adults,
                                    child: val_sup.params.child,
                                    dates: val_sup.params.dates
                                })
                            })
                            $.each(val.options.search.components, function (key_multi, val_multi) {
                                if (!(val_multi.removed)) {
                                    service.multiservices.push({
                                        token_search: val_multi.token_search,
                                        service_ident: key_multi,
                                        service_id: val_multi.service_id,
                                        rate_plan_id: val_multi.rate.id,
                                        reservation_time: val.reservation_time,
                                        guest_note: '',
                                        date_from: val_multi.date_reserve,
                                        quantity_adults: val.options.search.quantity_adult,
                                        quantity_child: val.options.search.quantity_child,
                                        child_ages: []
                                    })
                                }
                            })
                            reservations.push(service)
                        }
                    })
                    return reservations
                },
                destroyCartAndRedirect: function () {
                    let vm = this
                    let redirect = baseURL + 'reservations/' + vm.cart_detail.file_code

                    axios.delete(baseURL + 'cart/content/delete')
                        .then((result) => {
                            window.location.href = redirect
                        }).catch((e) => {
                        console.log(e)
                    })
                },
                countTotalPeople: function (hotel) {
                    let total = 0
                    for (let key in hotel.reservations_hotel_rooms) {
                        let people = hotel.reservations_hotel_rooms[key].adult_num + hotel.reservations_hotel_rooms[key].child_num
                        total += people
                    }
                    return total
                },
            }
        })
    </script>
@endsection
