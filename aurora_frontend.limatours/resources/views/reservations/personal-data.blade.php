@extends('layouts.app')
@section('content')
    <loading-component v-show="blockPage"></loading-component>
    <div class="shopping">
        <div class="container-fluid p-0">
            <div class="row">
                <div class="col">
                    <h3>
                        <span class="icon-shopping-bag mr-2"></span> {{ trans('reservations.label.title_cart') }}
                        <span class="tag-counter">
                            (@{{ cart.quantity_items }} {{ trans('reservations.label.products') }})
                        </span>
                    </h3>
                </div>
                <div class="col-auto">
                    <span>
                        <a href="/cart_details/view" class="btn btn-primary btn-car float-right">
                            {{ trans('cart_view.label.back') }}
                        </a>
                    </span>


                </div>
            </div>

        </div>

        <main class="container-fluid bg-light p-4 my-5 cart-all">
            <div class="row m-0 my-3 align-items-start">
                <div class="col">
                    <!-- DETAILS -->
                    <h4 class="subtitle my-0 text-uppercase" v-if="cart_detail.reservations_service.length > 0">
                        @{{ cart_detail.reservations_service.length }}
                        <span v-if="cart_detail.reservations_service.length == 1">{{trans('cart_view.label.service_added')}}</span>
                        <span v-else>{{trans('cart_view.label.services_added')}}</span>
                    </h4>
                    <div class="services_item" v-for="(service,index) in cart_detail.reservations_service">
                        <div class="blog-card overflow-card">
                            <div class="col-auto pr-0 py-4" v-if="service.service_logo">
                                <img :src="service.service_logo" :alt="service.service_name"
                                    class="photo" style="width: 200px; height: 200px; object-fit: cover;"
                                    onerror="this.onerror=null;this.src='https://res.cloudinary.com/litomarketing/image/upload/c_scale,w_400/v1440093948/paragliding_in_Miraflores._123110314_dlyun3.jpg'">
                            </div>
                            <div class="col description p-4">
                                <div class="d-flex justify-content-between mb-3">
                                    <h5 class="subtle">
                                        <span class="icon-calendar-confirm mr-1" style="font-size: 1.5rem;"></span>
                                        @{{ formatDate(service.date) }}
                                    </h5>
                                </div>
                                <h2>@{{ service.service_name }}
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
                                {{--------------------Fin Suplementos---------------------------------}}

                                <div class="d-flex align-items-center justify-content-between mt-5 mb-3">
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

                                <hr>
                                <div class="row">
                                    <div class="col-12 d-flex justify-content-between">
                                        <div class="row read-more" style="margin-left: 0px;">
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
                                                placeholder="{{ trans('global.label.include_notes') }}"
                                                rows="3"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    {{--End Servicios--}}

                    <div class="my-3" v-if="cart_detail.reservations_service.length > 0 && cart_detail.reservations_hotel.length > 0">
                    </div>

                    {{--Hoteles--}}
                    <h4 class="subtitle my-0 text-uppercase" v-if="cart_detail.reservations_hotel.length > 0">
                        @{{ cart_detail.reservations_hotel.length }}
                        <span v-if="cart_detail.reservations_hotel.length == 1">{{trans('cart_view.label.hotel_added')}}</span>
                        <span v-else>{{trans('cart_view.label.hotels_added')}}</span>
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
                                </h2>
                                <div class="d-flex align-items-center justify-content-between mb-3 mt-3">
                                    <div class=""><span class="icon-bed-double mr-2"></span>
                                        @{{ Object.keys(hotel.reservations_hotel_rooms).length }}
                                        <span v-if="Object.keys(hotel.reservations_hotel_rooms).length === 1"
                                            class="text-lowercase">{{trans('reservations.label.room')}}</span>
                                        <span v-if="Object.keys(hotel.reservations_hotel_rooms).length > 1"
                                            class="text-lowercase">{{trans('reservations.label.rooms')}}</span>
                                    </div>
                                    <div class=""><span class="icon-users mr-2"></span>
                                        @{{ countTotalPeople(hotel) }}
                                        <span v-if="countTotalPeople(hotel) === 1"
                                            class="text-lowercase">{{trans('reservations.label.person')}}</span>
                                        <span v-if="countTotalPeople(hotel) > 1"
                                            class="text-lowercase">{{trans('reservations.label.people')}}</span>
                                    </div>
                                </div>

                                <div class="mini-card" v-for="(room,index_room) in hotel.reservations_hotel_rooms">
                                    <h2 style="color: #4a90e2;">
                                        @{{ room.room_name }} <span class="text mr-2">@{{ room.rate_plan_name }}</span>
                                        <span class="ok" v-if="room.onRequest ==1">OK</span>
                                        <span class="rq" v-if="room.onRequest ==0">RQ</span>
                                    </h2>
                                    <div class="read-more mt-4 border-top pt-3">
                                        <span class="mr-5">
                                            <span class="icon-users"></span> @{{ room.adult_num }} {{ trans('hotel.label.adults') }}
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
                                <div class="row">
                                    <div class="col-12 d-flex justify-content-between">
                                        <div class="row read-more" style="margin-left: 0px;">
                                            <a :href="'#notas-'+index" data-toggle="collapse" role="button"
                                            style="float: left"
                                            aria-expanded="false" aria-controls="collapsenote">
                                                <span class="icon-plus-circle mr-2"></span>
                                                {{trans('reservations.label.include_notes')}}
                                            </a>
                                            <label style="float: left; margin-left: 50px"
                                                for="is_modification"
                                                @click="toggle_modification(hotel)">
                                                <input type="checkbox" style="display: inline-flex;"
                                                    id="is_modification"
                                                    name="is_modification"
                                                    v-model="hotel_is_modification[hotel.hotel_id]">
                                                {{trans('cart_view.label.it_is_a_modification')}}
                                            </label>

                                            <label style="float: left; margin-left: 10px" class="alert alert-warning"
                                                v-if="hotel_is_modification[hotel.hotel_id]">
                                                {{trans('cart_view.label.please_fill_in_the_notes_and_confirmation')}}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="collapse" :id="'notas-'+index">
                                                <textarea placeholder="{{trans('global.label.include_notes')}}"
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
                <div class="col-auto">
                    <div class="summary subtitle m-0">
                        <div class="card card-cart">
                            <h2 class="title-card">
                                {{trans('reservations.label.my_products')}}
                            </h2>
                            <div>
                                <div class="d-flex justify-content-between mb-2" v-for="(service,index) in cart.services">
                                    <div class="text-title">
                                        <span class="icon-check mr-1"></span> @{{ service.service_name }} (@{{
                                        service.service.quantity_adult + service.service.quantity_child }}
                                        <span
                                            v-if="(service.service.quantity_adult + service.service.quantity_child) === 1">{{trans('reservations.label.person')}}</span>
                                        <span
                                            v-if="(service.service.quantity_adult + service.service.quantity_child) > 1">{{trans('reservations.label.people')}}</span>)
                                        <span v-for="component in service.service.components">
                                            <br>
                                            + @{{ component.descriptions.name }}
                                        </span>
                                    </div>
                                    <div style="width: 90px;text-align: right;">USD $.@{{  getPrice(service.total_service) }}</div>
                                </div>
                                <div class="d-flex justify-content-between"
                                    v-for="(hotel,index) in cart_detail.reservations_hotel">
                                    <div class="text-title"><span class="icon-check mr-1"></span>@{{ hotel.hotel_name }}
                                        (@{{ countTotalPeople(hotel) }}
                                        <span
                                            v-if="countTotalPeople(hotel) === 1">{{trans('reservations.label.person')}}</span>
                                        <span
                                            v-if="countTotalPeople(hotel) > 1">{{trans('reservations.label.people')}}</span>)
                                    </div>
                                    <div style="width: 90px;text-align: right;">USD $.@{{ getPrice(hotel.total_amount) }}</div>
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
                                        USD $.@{{ getPrice(cart.total_cart) }}

                                    </div>
                                </div>
                                <div class="d-flex justify-content-between total" v-if="calculate_penality">
                                    <div>{{ trans('quote.label.cancellation_without_penalty') }}</div>
                                    {{-- <div>@{{ getFormatDate(reservation.min_date) }} @{{ reservation.min_date }} </div> --}}
                                    <div>@{{ getFormatDate(calculate_penality) }}</div>
                                </div>
                            </div>
                            <hr>
                            <template v-if="!isDisabledReservation">
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
                                                    style="height: 30px;" id="file_code"
                                                    v-bind:disabled="error != '' || cartIsNull"
                                                    v-model="cart_detail.file_code" maxlength="6"
                                                    @keydown="handleKeyDown($event)" @paste="handlePaste($event)">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="" class="col-5 col-form-label">Reference #</label>
                                            <div class="col-7">
                                                <input type="text" class="form-control" placeholder="Reference #"
                                                    v-bind:disabled="error != '' || cartIsNull"
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
                                                    class="icon-users mr-2"></span><span>
                                                    <b>@{{ totalHotelAdults }} </b> {{ trans('hotel.label.adults') }}</span>
                                            </div>
                                            <div class="mr-5" v-if="totalHotelChilds > 0">
                                                <span class="icon-smile mr-2"></span>
                                                <span><b>@{{ totalHotelChilds }} </b> {{ trans('hotel.label.child') }}</span>
                                            </div>
                                            <b-button v-on:click="modalPassengers(true)"
                                                    class="btn-lg" v-bind:disabled="error != '' || cartIsNull">
                                                <i class="icon-edit"></i> {{trans('reservations.label.enter_data')}}
                                            </b-button>
                                        </div>
                                    </div>

                                </div>
                            </template>
                            <div class="my-3 mt-5">
                                <p class="alert alert-warning mb-3" v-if="error !== ''">@{{ error }}</p>
                                <span id="disabled-wrapper" class="d-flex" tabindex="0">
                                    <button class="btn btn-primary btn-car" id="reservation-disabled"
                                        v-bind:disabled="error != '' || cartIsNull || loading_paxs || isDisabledReservation"
                                        style="width: 100%" @click="pushReservation()">
                                        {{ trans('cart_view.label.reserve') }}
                                    </button>
                                </span>
                                <b-tooltip show target="disabled-wrapper" placement="topleft" v-if="isDisabledReservation">
                                    <span style="font-size: 13px!important;">{{ trans('reservations.label.disabled') }}</span>
                                </b-tooltip>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </main>
    </div>
    <b-modal class="modal-central modal-content" size="md" id="modal_reservation" aria-hidden="true"
             ref="modal_reservation" v-if="reservation.file_code != ''" :no-close-on-backdrop="true"
             :no-close-on-esc="true" :hide-header-close="true">
        <div class="text-center mt-5">
            <h4 class="mb-5 size_title">
                <strong class="text-dark"> {{ trans('quote.label.tab_booking_details') }} </strong>
            </h4>
            <div>
                <label for="file_reference" class="col size_title mb-4 color-inf">
                    FILE: @{{ reservation.file_code }}
                </label>
            </div>
            <div>
                <label for="file_code" class="col-12 color-inf">
                    <i class="fas fa-user"></i> {{ trans('quote.label.name_pax') }}:
                    <span class="text-dark">@{{ reservation.customer_name }}</span>
                </label>
            </div>
            <div>
                <label for="file_code" class="col-12 py-3 color-inf">
                    <i class="fas fa-user-friends"></i>
                    {{ trans('quote.label.number_pax') }}:
                    <span class="text-dark">@{{ totalHotelAdults }} {{ trans('quote.label.adults') }}
                        <template v-if="totalHotelChilds > 0">
                            @{{ totalHotelChilds }} {{ trans('quote.label.child') }}
                        </template>
                    </span>
                </label>
            </div>
            <!-- div>
                <label for="file_reference" class="col-12 mb-5 color-inf">
                    <i class="fas fa-user-tag"></i>
                    {{ trans('quote.label.type_service') }}:
                    <span class="text-dark">@{{ getServiceType() }}</span>
                </label>
            </div -->
        </div>

        <div class="mb-2">
            <div class="row c-dark rounded justify-content-center mb-5 py-5 ml-0">
                <div>
                    <label class="col-12 m-0 color-inf">
                        {{ trans('quote.label.cancellation_without_penalty') }}:
                    </label>
                </div>
                <span class="text-dark">@{{ getFormatDate(reservation.min_date) }}</span>
                <div class="row px-4 my-3">
                    <div class="wrapper ml-4">
                        <input type="checkbox" v-on:change="saveReminder()" v-model="reminder.flag_send" id="check"
                               class="col-1"/>
                        <label for="check" class="col m-0 ml-2 p-0 text-dark2 size_paragraph">
                            {{ trans('quote.label.send_reminder_email') }}:
                        </label>
                    </div>
                    <div class="col-5 p-0">
                        <i v-on:click="changeDaysReminder('down')" class="text-dark3 fas fa-minus-circle"></i>
                        <input type="text" v-model="reminder.days" min="1" max="100"
                               value="1" class="col-3 p-0 text-center" v-on:change="saveReminder"/>
                        <i v-on:click="changeDaysReminder('up')" class="text-dark2 fas fa-plus-circle"></i>
                        <span class="size_paragraph">{{ trans('quote.label.days_before') }}</span>
                    </div>
                    <template v-if="reminder.flag_send">
                        <div class="row p-0 ml-4">
                            <input type="checkbox" id="check-mail" checked="checked" disabled class="col-auto"/>
                            <label for="check-mail" class="col m-0 ml-2 p-0 text-dark3 size_paragraph">
                                @{{ reservation.client.email }}
                            </label>
                        </div>
                        <div class="row p-0 ml-4">
                            <input type="checkbox" class="col-auto" v-model="reminder.flag_email"
                                   v-on:change="toggleEmailReminder()"/>
                            <input type="email" v-bind:disabled="!reminder.flag_email" v-model="reminder.email"
                                   maxlenght="100" v-on:change="saveReminder()"
                                   class="size-paragraph col m-1 p-1 border"/>
                        </div>
                        <small class="text-danger" v-if="email_error">{{ trans('quote.label.email_invalid') }}</small>
                    </template>
                </div>

                <div>
                    <span> {{ trans('quote.label.please_reconfirm_reservation') }}:
                        <a href="javascript:;" v-on:click="modalPassengers()">
                            <u class="blue-link">{{ trans('quote.label.passenger_data') }}</u>
                        </a>
                    </span>
                </div>
            </div>

            <div class="row mx-0 mb-4 rounded warning align-items-start">
                <i class="col-1 mt-4 fas fa-exclamation-triangle"></i>
                <div class="col justify-content-center py-4 ml-0">
                    <label class="m-0">
                        {{ trans('quote.label.no_code_booking') }}:
                        <span>@{{ reservation.booking_code }}</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="row m-0">
            <div class="col-md-12 p-0 mt-4 text-center">
                <button @click="closeModalReservation()" class="btn btn-primary mb-2">
                    {{trans('global.label.save')}}
                </button>
            </div>
        </div>
    </b-modal>
    <!------- Modal Datos pasajeros ------->
    <modal-passengers v-if="!blockPage" ref="modal_passengers"></modal-passengers>
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
                cartIsNull: false,
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
                reservation: {
                    file_code: '',
                },
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
                total_child_passengers: [],
                reminder: {
                    flag_send: false,
                    days: 1,
                    flag_email: false,
                    email: ''
                },
                email_error: false,
                loading_paxs: false,
                client : {},
            },
            created: function () {
                this.csrf_token = document.querySelector('meta[name=\'csrf-token\']').getAttribute('content')
            },
            mounted() {
                if (this.user_type_id === 3) {
                    this.getClientsByExecutive()
                }

                this.lang = localStorage.getItem('lang')
                this.client_id = localStorage.getItem('client_id')

                if (this.client_id) {
                    this.getClient();
                }

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
            computed: {
                isDisabledReservation: function () {
                    const response = parseInt(localStorage.getItem('client_disable_reservation') ?? 0);
                    return (response === 1);
                },
                calculate_penality: function () {
                    let date = ''
                    if (this.cart_detail.reservations_hotel && this.cart_detail.reservations_hotel.length > 0) {


                        let hotels_add_rooms = Object.entries(this.cart_detail.reservations_hotel[0].reservations_hotel_rooms)

                        for (const entry of hotels_add_rooms) {
                            if (entry[1] && entry[1].policies_cancellation.length > 0) {
                                date = entry[1].policies_cancellation[0].apply_date
                            }
                        }

                        if (date) {
                            date = date.split("-");
                            date = date[2] + '-' + date[1] + '-' + date[0];
                        }
                    }

                    return date;

                }
            },
            watch: {
                'cart_detail.file_code': async function (newVal) {
                    this.loading_paxs = true;
                    if(newVal && newVal.length === 6) {
                        await this.modalPassengers(false);
                    }
                    else
                    {
                        if(!newVal)
                        {
                            await this.modalPassengers(false);
                        }
                    }
                    this.loading_paxs = false;
                }
            },
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

                updateMenu: function () {
                    this.$emit('updateMenu')
                },
                toggle_modification(hotel) {
                    console.log(hotel)
                    console.log(this.hotel_is_modification[hotel.hotel_id])
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
                            if (result.data.cart.hotels.length === 0 && result.data.cart.services.length === 0) {
                                this.cartIsNull = true
                            } else {
                                this.cartIsNull = false
                            }
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

                                    if (resHotel.options.hotel_id != undefined) {
                                        let _notes = localStorage.getItem('notes_hotel_carrito_' + resHotel.options.hotel_id)
                                        let _is_modification = localStorage.getItem('is_modification_hotel_carrito_' + resHotel.options.hotel_id)

                                        Vue.set(this.hotel_room_comments, resHotel.options.hotel_id, ((_notes != undefined && _notes != '') ? _notes : ''))
                                        Vue.set(this.hotel_is_modification, resHotel.options.hotel_id, ((_is_modification != null && _is_modification != '' && _is_modification !== "false") ? 1 : 0))
                                    } else {
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

                            // DATALAYER CLIENT..
                            if(parseInt(this.user_type_id) === 4 && !this.cartIsNull) {
                                console.log("CART: ", this.cart.services, this.cart.hotels);

                                const items = [
                                    ...this.cart.services.map(service => ({
                                        item_id: service.service.id,
                                        item_sku: service.service.code,
                                        item_name: service.service.descriptions.name_gtm.toLocaleUpperCase("en"),
                                        price: parseFloat(service.service.total_amount),
                                        item_brand: service.service.origin.state_iso,
                                        item_category: 'service',
                                        item_category2: 'single_product',
                                        item_list_id: '',
                                        item_list_name: '',
                                    })),
                                    ...this.cart.hotels.map(hotel => ({
                                        item_id: hotel.hotel_id,
                                        item_sku: hotel.hotel.code,
                                        item_name: hotel.hotel_name.toLocaleUpperCase("en"),
                                        price: parseFloat(hotel.total_hotel),
                                        item_brand: hotel.hotel.state_iso,
                                        item_category: 'hotel',
                                        item_category2: 'single_product',
                                        item_list_id: '',
                                        item_list_name: '',
                                    }))
                                ];

                                const item_purchase = {
                                    event: "begin_checkout",
                                    funnel_type: "single-funnel",
                                    currency: "USD",
                                    value: parseFloat(this.cart.total_cart),
                                    package_id: null,
                                    package_name: null,
                                    items
                                };

                                localStorage.setItem('purchase_gmt', JSON.stringify(item_purchase));
                                dataLayer.push(item_purchase);
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
                distribute_subs(services) {
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
                            'phone': passengers[i].phone_code + passengers[i].celula,
                            'country_iso': (passengers[i].nacion == '-1') ? '' : passengers[i].nacion,
                            'city_iso': (passengers[i].city_ifx_iso) ? passengers[i].city_ifx_iso : passengers[i].ciunac,
                            'notes': passengers[i].observ,
                            'document_url': passengers[i].document_url,
                        })
                    }
                    return passengers_data
                },
                toggleEmailReminder: function () {
                    if (!this.reminder.flag_email) {
                        this.reminder.email = ''
                    }

                    this.saveReminder()
                },
                changeDaysReminder: function (_type) {

                    if (this.reminder.days > 0) {
                        if (_type == 'up' && this.reminder.days < 100) {
                            this.reminder.days += 1
                        }

                        if (_type == 'down' && this.reminder.days > 1) {
                            this.reminder.days -= 1
                        }
                    } else {
                        this.reminder.days = 0
                    }

                    if (this.reminder.flag_send) {
                        this.saveReminder()
                    }
                },
                saveReminder: function () {
                    let vm = this
                    vm.email_error = false

                    setTimeout(() => {
                        if (vm.reminder.flag_send) {
                            if (vm.reminder.email == '' || vm.reminder.email.match(/[^\s@]+@[^\s@]+\.[^\s@]+/gi)) {
                                axios.post('api/reservations/' + vm.reservation.id + '/reminders', {
                                    days_before: vm.reminder.days,
                                    email: vm.reservation.client.email,
                                    email_alt: vm.reminder.email,
                                    date: vm.reservation.min_date
                                })
                                    .then(result => {
                                        console.log(result.data)
                                    })
                                    .catch(error => {
                                        console.log(error)
                                    })
                            } else {
                                vm.email_error = true
                            }
                        } else {
                            axios.delete('api/reservations/' + vm.reservation.id + '/reminders')
                                .then(result => {
                                    console.log(result.data)
                                })
                                .catch(error => {
                                    console.log(error)
                                })
                        }
                    }, 10)
                },
                getFormatDate: function (_date) {
                    window.moment.locale(localStorage.getItem('lang'))
                    return window.moment(_date).format('LL')
                },
                closeModalReservation: async function () {
                    let vm = this
                    window.location.href = baseURL + 'reservations/' + vm.cart_detail.file_code
                },
                showModal: function (_modal) {
                    // this.searchStatements()
                    this.$refs[_modal].show()
                },
                pushReservation: async function () {
                    if(this.isDisabledReservation)
                    {
                        return false
                    }

                    this.error = this.$refs.modal_passengers.getError();
                    const _passengers = this.setFormatPassengers(this.$refs.modal_passengers.getPassengers());

                    if(this.error)
                    {
                        this.$toast.error(this.error, {
                            position: 'top-right'
                        });
                        return false;
                    }

                    if (_passengers.length === 0) {
                        this.$toast.error("{{ trans('quote.messages.empty_passenger') }}", {
                            position: 'top-right'
                        });

                        this.modalPassengers(true);
                        return false;
                    }

                    let _passenger = (_passengers.length > 0) ? _passengers[0] : null;

                    if (!this.cart_detail.file_code)
                    {
                        if (!_passenger || !_passenger.surname || !_passenger.given_name)
                        {
                            this.$toast.error("{{ trans('quote.messages.empty_passenger') }}", {
                                position: 'top-right'
                            });

                            this.modalPassengers(true);
                            return false;
                        }
                        else
                        {
                            this.msg = 'Procesando su reserva por favor espere....';
                            this.blockPage = true;
                            await this.processReservation();
                        }
                    } else {

                        this.msg = 'Procesando su reserva por favor espere....';
                        this.blockPage = true;
                        await this.processReservation();
                    }
                },
                processReservation: async function () {
                    let save_paxs = false;
                    this.error = this.$refs.modal_passengers.getError();
                    const _passengers = this.setFormatPassengers(this.$refs.modal_passengers.getPassengers());

                    if(this.error)
                    {
                        this.$toast.error(this.error, {
                            position: 'top-right'
                        });
                        return false;
                    }

                    if(this.cart_detail?.file_code)
                    {
                        save_paxs = true;
                    }

                    const params = {
                        client_id: this.client_id,
                        executive_id: this.cart_detail.executive_id,
                        file_code: this.cart_detail.file_code,
                        reference: this.cart_detail.customer_name,
                        guests: _passengers,
                        reservations: this.getReservationDetail(),
                        reservations_services: this.getReservationService(),
                        entity: 'Cart',
                        object_id: null
                    };

                    await axios.post(
                        'services/hotels/reservation/add', params
                    ).then(async (result) => {

                        if (result.data.success) {
                            this.cart_detail.file_code = result.data.data.file_code
                            this.reservation = result.data.data
                            this.destroyCartAndRedirect();
                        } else {
                            this.blockPage = false
                            this.show_message_error = true
                            const error = result.data.error.message;
                            // Si el mensaje es un arreglo, iterar sobre él y mostrar los mensajes
                            if (Array.isArray(error)) {
                                error.forEach((err) => {
                                    this.$toast.error(err, {
                                        position: 'top-right'
                                    });
                                });
                            } else {
                                // Si el mensaje es un string, mostrarlo directamente
                                this.$toast.error(error, {
                                    position: 'top-right'
                                });
                            }
                        }
                    }).catch((e) => {
                        this.blockPage = false
                        console.log(e)
                    });
                },
                modalPassengers: async function (modal) {
                    if(this.$refs.modal_passengers)
                    {
                        await this.$refs.modal_passengers.modalPassengers((this.cart_detail.file_code != null && this.cart_detail.file_code != undefined && this.cart_detail.file_code != '') ? 'file' : 'session', this.cart_detail.file_code, this.totalPassengersNum, this.totalHotelAdults, this.totalHotelChilds, 0, modal)
                    }
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
                    //return moment(starDate).format('ddd D MMM')
                    return moment(starDate).utcOffset(-5).format('ddd D MMM')
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
                                    rate_plan: {
                                        rate: val.options.rate,
                                    },
                                    suplements: [],
                                    guest_note: this.getRoomComment(val.options.hotel_id),
                                    is_modification: this.getIsModification(val.options.hotel_id),
                                    date_from: val.options.date_from,
                                    date_to: val.options.date_to,
                                    quantity_adults: val.options.room.quantity_adults,
                                    quantity_child: val.options.room.quantity_child,
                                    // quantity_infants : val.options.room.quantity_infants,
                                    // quantity_rooms : val.options.room.quantity_rooms,
                                    child_ages: val.options.rate.rate[0].ages_child,
                                }
                            } else {
                                dataRoom = {
                                    token_search: val.options.token_search,
                                    room_ident: key,
                                    hotel_id: val.options.hotel_id,
                                    best_option: false,
                                    rate_plan_room_id: val.options.rate_id,
                                    rate_plan: {
                                        rate: val.options.rate,
                                    },
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
                                    child_ages: val.options.rate.rate[0].ages_child,
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
                destroyCartAndRedirect: async function () {
                    try {

                        // Event purchase..
                        if (parseInt(this.user_type_id) === 4) {
                            const storedItem = localStorage.getItem('purchase_gmt');
                            if(storedItem)
                            {
                                let purchase_item = storedItem ? JSON.parse(storedItem) : {};

                                purchase_item = {
                                    ...purchase_item,
                                    event: "purchase",
                                    package_name: null, // this.cart_detail.customer_name,
                                    transaction_id: this.reservation.file_code,
                                };

                                console.log(purchase_item);
                                dataLayer.push(purchase_item);
                                localStorage.removeItem('purchase_gmt');
                            }
                        }

                        // Limpiar el carrito del servidor
                        // this.clearCart();
                        const result = await axios.delete(baseURL + 'cart/content/delete');
                        console.log("RESPONSE: ", result.data);

                        if(result.data.success)
                        {
                            this.blockPage = false
                            this.showModal('modal_reservation')
                        }
                        else
                        {
                            console.error('Error destroying cart:');
                            this.blockPage = false;
                            this.$toast.error('Error al procesar la reserva. Por favor, inténtelo de nuevo.', {
                                position: 'top-right'
                            });
                        }
                    } catch (error) {
                        console.error('Error destroying cart:', error);
                        this.blockPage = false;
                        this.$toast.error('Error al procesar la reserva. Por favor, inténtelo de nuevo.', {
                            position: 'top-right'
                        });
                    }
                },
                clearCart: function () {

                    try {
                        this.executeMenuClearCart();
                        // await this.executeMenuClearCart();
                    } catch (error) {
                        console.error('Error clearing cart:', error);
                        this.$toast.error('Error al limpiar el carrito. Por favor, inténtelo de nuevo.', {
                            position: 'top-right'
                        });
                    }
                },
                executeMenuClearCart: function () {
                    // Ejecutar clearCart() del MenuComponent usando $root.$emit
                    this.$root.$emit('executeMenuClearCart', false);
                },
                executeMenuClearCartDirect: function () {
                    // Acceso directo al componente del menú (alternativa)
                    if (this.$root.$children && this.$root.$children.length > 0) {
                        // Buscar el componente del menú
                        const menuComponent = this.$root.$children.find(child =>
                            child.$options.name === 'MenuComponent' ||
                            child.clearCart
                        );
                        if (menuComponent && menuComponent.clearCart) {
                            menuComponent.clearCart(false);
                        }
                    }
                },
                countTotalPeople: function (hotel) {
                    let total = 0
                    for (let key in hotel.reservations_hotel_rooms) {
                        let people = hotel.reservations_hotel_rooms[key].adult_num + hotel.reservations_hotel_rooms[key].child_num
                        total += people
                    }
                    return total
                },
                closeBloqued() {
                    this.blockPage = false;
                },
                handleKeyDown(event) {
                    const charCode = event.keyCode;
                    if (event.ctrlKey || event.metaKey) return;
                    if (charCode === 8 || charCode === 46) return;
                    if ((charCode >= 48 && charCode <= 57) ||
                        (charCode >= 96 && charCode <= 105)) {
                        return;
                    }
                    event.preventDefault();
                },
                handlePaste(event) {
                    const pastedText = event.clipboardData.getData('text/plain');
                    this.cart_detail.file_code = pastedText.replace(/[^\d]/g, '');
                    event.preventDefault();
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
@section('css')
    <style>

        .blue-info {
            cursor: pointer;
            color: #1b70a1;
        }

        .blue-link {
            color: #55A3FF;
        }

        .blue-link:hover {
            color: #005BC6;
        }

        .warning {
            background: #FFFBDB;
            color: #E4B804;
            border: solid .5px #FFCC00;
        }

        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type=number] {
            -moz-appearance: textfield;
        }

        .fa-minus-circle:hover, .fa-plus-circle:hover {
            color: #EB5757;
            cursor: pointer;
        }

        .wrapper {
            height: 18px;
            width: 199px;
            /* position: absolute; */
            margin: auto;
            display: flex;
            align-items: center;
            justify-content: space-around;
        }

        input[type="checkbox"] {
            appearance: none;
            -webkit-appearance: none;
            height: 15px;
            width: 15px;
            background-color: white;
            border-radius: 3px;
            border: solid 1px #C4C4C4;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        input[type="checkbox"]:after {
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            content: "\f00c";
            font-size: 10px;
            color: white;
            display: none;
        }

        input[type="checkbox"]:hover {
            background-color: #c7c6c6;
        }

        input[type="checkbox"]:checked {
            background-color: #EB5757;
            border: solid 1px #EB5757;
        }

        input[type="checkbox"]:checked:after {
            display: block;
        }

        input[type="checkbox"]:disabled {
            background-color: rgba(235, 87, 87, .5);
            border: none;
            color: red;
            cursor: none;
        }

        .size_title {
            font-size: 24px !important;
        }

        .size_paragraph {
            font-size: 12px !important;
        }

        .background-warning {
            background: #fffbdf;
        }

        .alert-warning {
            font-size: 13px !important;
            margin: 2rem !important;
        }

        .color-inf {
            color: #2E2B9E;
        }

        .text-dark {
            color: #4F4B4B;
        }

        .text-dark2 {
            color: #737373;
        }

        .text-dark3 {
            color: #BDBDBD;
        }

        .fa-times:hover {
            color: #4F4B4B;
        }

        .c-dark {
            background: #FAFAFA;
        }

    </style>
@endsection
