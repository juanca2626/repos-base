@extends('layouts.app')
@section('content')
    <section class="packages__details">
        <div class="hero__primary">
        </div>
    </section>
    <section class="packages__details mt-5 container pb-5">
        <b-skeleton-wrapper :loading="loading_package">
            <template #loading>
                <b-row>
                    <b-col cols="8" class="mb-2">
                        <b-skeleton width="100%" height="20%" class="mb-3"></b-skeleton>
                        <b-skeleton width="60%" height="20%" class="mb-3"></b-skeleton>
                        <b-skeleton width="60%" height="20%" class="mb-5"></b-skeleton>
                        <b-row>
                            <b-col cols="6">
                                <b-skeleton width="100%" height="20%" class="mb-3"></b-skeleton>
                                <b-skeleton-img width="100%" height="250px"></b-skeleton-img>
                            </b-col>
                            <b-col cols="6">
                                <b-skeleton width="100%" height="20%" class="mb-3"></b-skeleton>
                                <b-skeleton width="90%" height="20%" class="mb-3"></b-skeleton>
                                <b-skeleton width="80%" height="20%" class="mb-3"></b-skeleton>
                                <b-skeleton width="70%" height="20%" class="mb-3"></b-skeleton>
                                <b-skeleton width="60%" height="20%" class="mb-3"></b-skeleton>
                                <b-skeleton width="50%" height="20%" class="mb-3"></b-skeleton>
                                <b-skeleton width="50%" height="20%" class="mb-3"></b-skeleton>
                                <b-skeleton width="50%" height="20%" class="mb-3"></b-skeleton>
                                <b-skeleton width="50%" height="20%" class="mb-3"></b-skeleton>
                                <b-skeleton width="50%" height="20%" class="mb-3"></b-skeleton>
                            </b-col>
                        </b-row>
                    </b-col>
                    <b-col cols="4" class="mb-2">
                        <b-skeleton-img></b-skeleton-img>
                        <b-skeleton width="100%" height="20%" class="mb-3"></b-skeleton>
                        <b-skeleton width="100%" height="20%" class="mb-3"></b-skeleton>
                        <b-skeleton width="100%" height="20%" class="mb-3"></b-skeleton>
                        <b-skeleton width="100%" height="20%" class="mb-3"></b-skeleton>
                        <b-skeleton width="100%" height="20%" class="mb-3"></b-skeleton>
                        <b-skeleton width="100%" height="20%" class="mb-3"></b-skeleton>
                        <b-skeleton width="100%" height="20%" class="mb-3"></b-skeleton>
                    </b-col>
                </b-row>
            </template>
            <h1 class="package-title color-primary mb-5" v-if="reservation.reservations_package.length > 0 && reservation_status == 'OK'">
                3. ¡@{{ translations.label.your_booking_is_confirmed }}!
            </h1>
            <h1 class="package-title color-primary mb-5" v-if="reservation.reservations_package.length > 0 && reservation_status == 'RQ'">
                3. ¡@{{ translations.label.your_reservation_being_worked }}!
            </h1>
            <div class="d-flex justify-content-between" v-if="reservation.reservations_package.length > 0">
                <div style="width: 65%">
                    <p v-if="reservation.reservations_passenger.length > 0">
                        @{{reservation.reservations_passenger[0].name}} @{{reservation.reservations_passenger[0].surnames}}
                    </p>
                    <p>@{{ translations.label.booking_number }}: @{{ file_code }}</p>
                    <div>
                        <div class="d-flex my-5" style="letter-spacing: 0;">
                            <div class="mr-3" v-if="reservation.reservations_package.length > 0">
                                <img v-if="reservation.reservations_package[0].package.galleries.length > 0"
                                     :src="reservation.reservations_package[0].package.galleries[0].url | buildURL"
                                     onerror="this.onerror=null;this.src='https://res.cloudinary.com/litomarketing/image/upload/c_scale,w_400/v1440093948/paragliding_in_Miraflores._123110314_dlyun3.jpg'"
                                     class="rounded" alt="service" style="width: 300px;">
                                <img class="rounded"
                                     src="https://res.cloudinary.com/litomarketing/image/upload/c_scale,w_400/v1440093948/paragliding_in_Miraflores._123110314_dlyun3.jpg"
                                     alt="Image Service" v-else style="width: 300px;">
                            </div>
                            <div v-if="reservation.reservations_package.length > 0">
                                <p class="color-primary font-weight-bold mb-5">
                                    @{{ reservation.reservations_package[0].package.translations[0].tradename }} @{{
                                    reservation.reservations_package[0].package.nights + 1 }} @{{ translations.label.days }} / @{{
                                    reservation.reservations_package[0].package.nights }} @{{ translations.label.nights }} </p>
                                <p><span class="icon-ac-calendar1 mr-1"></span> @{{ reservation.date_init |
                                    formattedDate}}
                                    - @{{ reservation.date_end | formattedDate}}
                                </p>
                                <p>
                                    <span class="icon-ac-ninos mr-1"></span>
                                    <span>@{{ reservation.reservations_package[0].quantity_adults }} @{{ translations.label.adults }}</span>
                                    <span>@{{ reservation.reservations_package[0].quantity_child_with_bed +  reservation.reservations_package[0].quantity_child_without_bed }} @{{ translations.label.children }}</span>
                                </p>
                                <p v-if="reservation.reservations_package[0].quantity_sgl > 0">
                                    <span class="icon-ac-seat_individual mr-1"></span> @{{
                                    reservation.reservations_package[0].quantity_sgl }} SGL
                                </p>
                                <p v-if="reservation.reservations_package[0].quantity_dbl > 0">
                                    <span class="icon-ac-seat_individual mr-1"></span> @{{
                                    reservation.reservations_package[0].quantity_dbl }} DBL
                                </p>
                                <p v-if="reservation.reservations_package[0].quantity_tpl > 0">
                                    <span class="icon-ac-seat_individual mr-1"></span> @{{
                                    reservation.reservations_package[0].quantity_tpl }} TPL
                                </p>
                                <p v-if="reservation.reservations_package[0].type_class.translations.length > 0">
                                    <span class="icon-ac-location_city"></span> @{{
                                    reservation.reservations_package[0].type_class.translations[0].value }}
                                </p>
                                <p v-if="reservation.reservations_package[0].service_type.translations.length > 0">
                                    <span class="icon-ac-directions_run"></span> @{{
                                    reservation.reservations_package[0].service_type.translations[0].value }}
                                </p>
                            </div>
                        </div>
                        <div class="d-flex my-5" style="letter-spacing: 0;"
                             v-for="service in reservation.reservations_service">
                            <div class="mr-3">
                                <img v-if="service.service.galleries.length > 0" :src="service.service.galleries[0].url"
                                     onerror="this.onerror=null;this.src='https://res.cloudinary.com/litomarketing/image/upload/c_scale,w_400/v1440093948/paragliding_in_Miraflores._123110314_dlyun3.jpg'"
                                     class="rounded" alt="service" style="width: 300px;">
                                <img class="rounded"
                                     src="https://res.cloudinary.com/litomarketing/image/upload/c_scale,w_400/v1440093948/paragliding_in_Miraflores._123110314_dlyun3.jpg"
                                     alt="Image Service" v-else style="width: 300px;">
                            </div>
                            <div>
                                <p class="color-primary font-weight-bold mb-5">@{{
                                    service.service.service_translations[0].name }}</p>
                                <a href="https://www.masiyourtravelpartner.com/carbo_certification/" target="_blank"
                                   class="btn-outline"><span class="icon-ac-download mr-2"></span> Descargar
                                    certificado
                                </a>
                            </div>
                        </div>
                        <div>
                            <a href="/consulta_files" target="_blank" class="btn btn-primary">@{{ translations.label.see_my_bookings }}</a>
                        </div>

                    </div>
                </div>
                <div style="width: 32%" v-if="reservation.reservations_package.length > 0">
                    <div class="details-booking">
                        <div class="d-flex justify-content-between">
                            <p class="p-booking justify-content-start">@{{
                                reservation.reservations_package[0].package.translations[0].tradename }}</p>
                            <p class="justify-content-end"><strong>$ @{{ reservation.total_amount }}</strong>
                            </p>
                        </div>
                        <div class="d-flex justify-content-between"
                             v-if="reservation.reservations_package[0].quantity_sgl > 0">
                            <p class="p-booking justify-content-start">
                                @{{ translations.label.x_adult_simple_room }}
                            </p>
                            <p class="justify-content-end">$ @{{ reservation.reservations_package[0].price_per_adult_sgl
                                }}</p>
                        </div>
                        <div class="d-flex justify-content-between"
                             v-if="reservation.reservations_package[0].quantity_dbl > 0">
                            <p class="p-booking justify-content-start">
                                @{{ translations.label.x_adult_double_room }}
                            </p>
                            <p class="justify-content-end">$ @{{ reservation.reservations_package[0].price_per_adult_dbl
                                }}</p>
                        </div>

                        <div class="d-flex justify-content-between"
                             v-if="reservation.reservations_package[0].quantity_tpl > 0">
                            <p class="p-booking justify-content-start">
                                @{{ translations.label.x_adult_triple_room }}
                            </p>
                            <p class="justify-content-end">$ @{{ reservation.reservations_package[0].price_per_adult_tpl
                                }}</p>
                        </div>
                        <div class="d-flex justify-content-between"
                             v-if="reservation.reservations_package[0].price_per_child_with_bed > 0">
                            <p class="p-booking justify-content-start">
                                @{{ reservation.reservations_package[0].quantity_child_with_bed }} @{{ translations.label.children_with_bed }}
                            </p>
                            <p class="justify-content-end">$ @{{
                                reservation.reservations_package[0].price_per_child_with_bed }}</p>
                        </div>
                        <div class="d-flex justify-content-between"
                             v-if="reservation.reservations_package[0].price_per_child_without_bed > 0">
                            <p class="p-booking justify-content-start">@{{
                                reservation.reservations_package[0].quantity_child_without_bed }} @{{ translations.label.children_without_bed }}</p>
                            <p class="justify-content-end">$ @{{
                                reservation.reservations_package[0].price_per_child_without_bed }}</p>
                        </div>
                        <div class="d-flex justify-content-between mt-4"
                             v-if="reservation.reservations_service.length > 0"
                             v-for="service in reservation.reservations_service">
                            <p class="p-booking justify-content-start mr-2">@{{
                                service.service.service_translations[0].name }}</p>
                            <p class="justify-content-end" style="width: 100px"><strong>$ @{{ service.total_amount
                                    }}</strong></p>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <p class="p-booking justify-content-start">@{{ translations.label.total }}</p>
                            <p class="justify-content-end"><strong>$ @{{ reservation.total_amount }}</strong></p>
                        </div>
                        <div class="d-flex justify-content-between">
                            <p class="justify-content-start"><strong>@{{ translations.label.total_amount_due }}</strong></p>
                            <p class="justify-content-end"><strong>USD $ @{{ reservation.total_amount }}</strong></p>
                        </div>

                    </div>
                    <div class="mt-4">
                        <p class="p-booking">@{{ translations.label.send_email_booking }}</p>
                        <span
                            class="p-booking color-primary mb-5">@{{ translations.label.if_you_want_send_another_email }}:</span>
                    </div>
                    <validation-observer v-slot="{ handleSubmit }">
                        <form class="pb-5" @submit.prevent="handleSubmit(sendEmail)">
                            <validation-provider rules="required|email" v-slot="{ errors }" slim>
                                <div class="form-control my-4" :class="{ 'border-red': errors[0]}">
                                    <input class="send" type="email" v-model="email" placeholder="abcd@gmail.com">
                                    <button type="submit" class="btn btn-danger"
                                            style="background-color: #EB5757 !important;">
                                        <i class="fas fa-paper-plane" style="color: #ffffff !important;"></i>
                                    </button>
                                </div>
                            </validation-provider>
                        </form>
                    </validation-observer>
                    <!-- div class="details-booking color-primary d-flex">
                        <i class="fas fa-exclamation-circle mr-5 text-xl-center"></i>
                        <p class="p-booking">
                            @{{ translations.label.contact_technical_support }}: @{{ reservation.booking_code }}
                        </p>
                    </div -->
                </div>

            </div>
        </b-skeleton-wrapper>
    </section>

    <section class="packages__details mt-5 container pb-5" v-if="reservation.reservations_package.length === 0 && !loading_package">
        <div class="jumbotron">
            <h2 class="text-center"><i class="fas fa-sad-tear"></i> {{trans('package.label.no_file_information_found')}}:
                @{{ file_code }}</h2>
        </div>
    </section>

    <template v-if="reservation.total_amount > 0">
        <div class="mb-2">
            <div class="row c-dark rounded justify-content-center mb-5 py-5 ml-0">
                <div class="container">
                    <div>
                        <label class="col-12 m-0 color-inf">
                            {{ trans('quote.label.cancellation_without_penalty') }}:
                            <span class="text-dark">@{{ getFormatDate(JSON.parse(reservation.reservations_package[0].cancellation_policy).last_day_cancel) }}</span>
                        </label>
                    </div>
                    <div class="row px-4 my-3">
                        <div class="d-flex mx-4">
                            <input type="checkbox" v-on:change="saveReminder()" v-model="reminder.flag_send" id="check" class="col-1" />
                            <label for="check" class="col m-0 ml-2 p-0 text-dark2 size_paragraph">
                                {{ trans('quote.label.send_reminder_email') }}:
                            </label>
                        </div>
                        <div class="col-5 p-0">
                            <i v-on:click="changeDaysReminder('down')" class="text-dark3 fas fa-minus-circle"></i>
                            <input type="text" v-model="reminder.days" min="1" max="100"
                                value="1" class="col-3 p-0 text-center" v-on:change="saveReminder" />
                            <i v-on:click="changeDaysReminder('up')" class="text-dark2 fas fa-plus-circle"></i>
                            <span class="size_paragraph">{{ trans('quote.label.days_before') }}</span>
                        </div>
                        <template v-if="reminder.flag_send">
                            <div class="row p-0 ml-4">
                                <input type="checkbox" id="check-mail" checked="checked" disabled class="col-auto" />
                                <label for="check-mail" class="col m-0 ml-2 p-0 text-dark3 size_paragraph">
                                    @{{ reservation.client.email }}
                                </label>
                            </div>
                            <div class="row p-0 ml-4">
                                <input type="checkbox" class="col-auto" v-model="reminder.flag_email"
                                    v-on:change="toggleEmailReminder()" />
                                <input type="email" v-bind:disabled="!reminder.flag_email" v-model="reminder.email"
                                    maxlenght="100" v-on:change="saveReminder()" class="size-paragraph col m-1 p-1 border" />
                            </div>
                            <small class="text-danger" v-if="email_error">{{ trans('quote.label.email_invalid') }}</small>
                        </template>
                    </div>

                    <div>
                        <span class="col-12 m-0"> {{ trans('quote.label.please_reconfirm_reservation') }}:
                            <a href="javascript:;" v-on:click="modalPassengers()">
                                <u class="blue-link">{{ trans('quote.label.passenger_data') }}</u>
                            </a>
                        </span>
                    </div>

                    <div class="row mx-0 my-4 rounded warning align-items-start">
                        <i class="col-auto mt-4 fas fa-exclamation-triangle"></i>
                        <div class="col justify-content-center py-4 ml-0">
                            <small class="m-0">
                                {{ trans('quote.label.no_code_booking') }}:
                                <span>@{{ reservation.booking_code }}</span>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>

    {{--Masi--}}
    <section class="packages__details my-5 container pb-5">
        <div class="d-flex justify-content-between align-items-center pt-0">
            <div class="">
                <h1 class="mb-5 text-center">{{ trans('masi.label.activate_masi') }}</h1>
                <img class="" src="../../images/masi-partner.png" alt="masi-partner" width="500"/>
            </div>
            <div class="pl-5">
                <h2 class="mb-5">{{ trans('masi.label.with_mais_you_can') }}</h2>
                <ol class="pl-4">
                    <li class="mt-4">
                        {{ trans('masi.label.masi_consult_services') }}.
                    </li>
                    <li class="mt-4">
                        {{ trans('masi.label.masi_know_the_temperature_of_destination') }}.
                    </li>
                    <li class="mt-4">
                        {{ trans('masi.label.masi_know_itinerary') }}.
                    </li>
                    <li class="mt-4">
                        {{ trans('masi.label.masi_have_chatbot') }}.
                    </li>
                    <li class="mt-4">
                        {{ trans('masi.label.masi_available_24_7') }}.
                    </li>
                </ol>
            </div>


        </div>
    </section>
    {{--Recomendaciones--}}
    <div class="" style="background:#F5F5F5;">
        <section class="container policies py-5 border-bottom">
            <h2 class="title-section my-5">{{ trans('travel_recommendations.label.recommendations_for_your_trip') }}</h2>
            <div class="d-flex py-5">
                <div class="col pl-0 pr-4">
                    <h3 class="">{{ trans('travel_recommendations.label.it_is_recommended') }}</h3>
                    <p class="">
                        {{ trans('travel_recommendations.label.it_is_recommended_text') }}
                    </p>
                </div>
                <div class="col pr-0 pl-4">
                    <h3 class="">{{ trans('travel_recommendations.label.not_recommended') }}</h3>
                    <p class="">
                        {{ trans('travel_recommendations.label.not_recommended_text') }}.
                    </p>
                </div>
            </div>
            <h4 class="my-5">{{ trans('travel_recommendations.label.recommendations_luggage_transfer') }}</h4>
            <div class="d-flex py-5">
                <div class="pr-4">
                    <h3 class="condition_title mt-0">{{ trans('travel_recommendations.label.train_to_machu_picchu') }}</h3>
                    <p class="">
                        {{ trans('travel_recommendations.label.train_to_machu_picchu_text') }}.
                    </p>
                    <table>
                        <thead>
                        <tr>
                            <th scope="col">{{ trans('travel_recommendations.label.quantity') }}</th>
                            <th scope="col">{{ trans('travel_recommendations.label.weight') }}</th>
                            <th scope="col">{{ trans('travel_recommendations.label.size') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td data-label="Cantidad">1 {{ trans('travel_recommendations.label.bag_or_backpack') }}</td>
                            <td data-label="Peso">5kg / 11lb</td>
                            <td data-label="Tamaño">62’’ / 157 cm</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="pl-4">
                    <h3 class="condition_title mt-0">{{ trans('travel_recommendations.label.by_bus_within_the_country') }}</h3>
                    <p class="">
                        {{ trans('travel_recommendations.label.by_bus_within_the_country_text') }}.
                    </p>
                    <table>
                        <thead>
                        <tr>
                            <th scope="col">{{ trans('travel_recommendations.label.quantity') }}</th>
                            <th scope="col">{{ trans('travel_recommendations.label.weight') }}</th>
                            <th scope="col">{{ trans('travel_recommendations.label.size') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td data-label="Cantidad">{{ trans('travel_recommendations.label.hold_baggage') }}</td>
                            <td data-label="Peso">20 kg</td>
                            <td data-label="Tamaño">-</td>
                        </tr>
                        <tr>
                            <td data-label="Cantidad">{{ trans('travel_recommendations.label.hand_baggage') }}</td>
                            <td data-label="Peso">5 kg</td>
                            <td data-label="Tamaño">-</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="packages__details py-5 container border-bottom">
            <div class="container px-0">
                <div class="d-flex justify-content-between align-items-center pt-0">
                    <div class="aurora__description pr-5">
                        <h2 class="title-section mb-5">{{ trans('travel_recommendations.label.entrance_to_peru') }}</h2>
                        <p>{{ trans('travel_recommendations.label.entrance_to_peru_text') }}.</p>
                    </div>
                    <div>
                        <img class="" src="../../images/aeropuerto.png" alt="aeropuerto" width="500"/>
                    </div>

                </div>
            </div>
        </section>
        <section class="packages__details py-5 container">
            <div class="container">
                <div class="aurora__video d-flex justify-content-between align-items-center pt-0">
                    <video controls crossorigin playsinline
                           poster="https://res.cloudinary.com/litomarketing/image/upload/v1619539204/Thumbnails/Gu%C3%ADa%20esencial%20de%20viaje%20a%20Per%C3%BA/Gui%CC%81a_de_viaje_ESP.jpg"
                           v-if="lang === 'es'" id="es">
                        <!-- Video files -->
                        <source
                            src="https://res.cloudinary.com/litomarketing/video/upload/v1527182682/vimeo/Your%20Ultimate%20Guide%20to%20Peru/Gui%CC%81a_esencial_de_Viaje_al_Peru%CC%81-_720.mp4"
                            type="video/mp4" size="720">
                    </video>
                    <video controls crossorigin playsinline
                           poster="https://res.cloudinary.com/litomarketing/image/upload/v1619539204/Thumbnails/Gu%C3%ADa%20esencial%20de%20viaje%20a%20Per%C3%BA/Gui%CC%81a_de_viaje_ENG.jpg"
                           v-else id="en">
                        <!-- Video files -->
                        <source
                            src="https://res.cloudinary.com/litomarketing/video/upload/v1527182674/vimeo/Your%20Ultimate%20Guide%20to%20Peru/Your_Ultimate_Guide_to_Peru_-_720.mp4"
                            type="video/mp4" size="720">
                    </video>
                    <div class="aurora__description mr-3">
                        <h2 class="title-section mb-5">{{ trans('travel_recommendations.label.essential_peru_travel_guide') }}</h2>
                        <p>{{ trans('travel_recommendations.label.essential_peru_travel_guide_text') }}.</p>
                        <a class="font-weight-bold" target="_blank" href="/biosafety-protocols"><span
                                class="icon-ac-alert-circle mr-1"></span>{{ trans('travel_recommendations.label.review_our_biosafety_protocols') }}</a>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <!------- Modal Datos pasajeros ------->
    <modal-passengers ref="modal_passengers"></modal-passengers>
    <section-write-us-component></section-write-us-component>
@endsection
@section('js')
    <script>
        new Vue({
            el: '#app',
            data: {
                lang: 'en',
                loading_package: true,
                loading_send: false,
                reservation_status: 'OK',
                file_code: 0,
                email: '',
                reservation: {
                    booking_code: '',
                    file_code: '',
                    date_init: '',
                    date_end: '',
                    reservations_package: [],
                    reservations_service: [],
                    reservations_passenger: [],
                    total_amount: 0,
                    total_amount_package: 0,
                },
                translations: {
                    label: {},
                    validations: {},
                    messages: {}
                },
                reminder: {
                    flag_send: false,
                    days: 1,
                    flag_email: false,
                    email: ''
                },
                email_error: false,
            },
            created: function () {
                this.client_id = localStorage.getItem('client_id')
                this.lang = localStorage.getItem('lang')
                this.file_code = "{{ $file_code }}"
            },
            async mounted () {
                await this.setTranslations();
                await this.getReservation();
            },
            computed: {},
            methods: {
                modalPassengers: function () {
                    this.$refs.modal_passengers.modalPassengers('file', this.reservation.file_code, 0, 0, 0, 0, true)
                },
                getFormatDate: function (_date) {
                    window.moment.locale(localStorage.getItem('lang'))
                    return window.moment(_date).format('LL')
                },
	            goBiosafetyProtocols () {
		            window.location.href = '/biosafety-protocols'
	            },
                async setTranslations () {
                    try {
                        const response = await axios.get(
                            `${baseURL}translation/${localStorage.getItem('lang')}/slug/package`
                        )

                        this.translations = response.data;
                    } catch (error) {
                        console.error('Error al obtener traducciones:', error)
                    }
                },
                toggleEmailReminder: function () {
                    if(!this.reminder.flag_email)
                    {
                        this.reminder.email = ''
                    }

                    this.saveReminder()
                },
                changeDaysReminder: function (_type) {

                    if(this.reminder.days > 0)
                    {
                        if(_type == 'up' && this.reminder.days < 100)
                        {
                            this.reminder.days += 1
                        }

                        if(_type == 'down' && this.reminder.days > 1)
                        {
                            this.reminder.days -= 1
                        }
                    }
                    else
                    {
                        this.reminder.days = 0
                    }

                    if(this.reminder.flag_send)
                    {
                        this.saveReminder()
                    }
                },
                saveReminder: function () {
                    let vm = this
                    vm.email_error = false

                    setTimeout(() => {
                        if(vm.reminder.flag_send)
                        {
                            if(vm.reminder.email == '' || vm.reminder.email.match(/[^\s@]+@[^\s@]+\.[^\s@]+/gi))
                            {
                                axios.post('api/reservations/' + vm.reservation.id + '/reminders', {
                                    days_before: vm.reminder.days,
                                    email: vm.reservation.client.email,
                                    email_alt: vm.reminder.email,
                                    date: JSON.parse(vm.reservation.reservations_package[0].cancellation_policy).last_day_cancel
                                })
                                    .then(result => {
                                        console.log(result.data)
                                    })
                                    .catch(error => {
                                        console.log(error)
                                    })
                            }
                            else
                            {
                                vm.email_error = true
                            }
                        }
                        else
                        {
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
                async getReservation () {
                    this.loading_package = true
                    try {
                        const result = await axios.get(
                            `${baseExternalURL}services/client/reservation/${this.file_code}/package?lang=${this.lang}`
                        )

                        this.loading_package = false

                        if (result.data.success) {
                            this.reservation = result.data.data
                            this.reservation_status = result.data.on_request

                            // 👤 Validación para user_type_id = 4
                            if (localStorage.getItem("user_type_id") == 4) {
                                const storedItem = localStorage.getItem('package_reservation')
                                if (storedItem) {
                                    this.data_reservation = JSON.parse(storedItem)
                                    dataLayer.push({
                                        event: "purchase",
                                        funnel_type: "package-funnel",
                                        transaction_id: this.file_code,
                                        currency: "USD",
                                        value: this.data_reservation.total_amount,
                                        package_id: this.data_reservation.id,
                                        package_code: this.data_reservation.code,
                                        package_name: this.data_reservation.package_name_gtm,
                                        items: this.data_reservation.services_gtm
                                    })
                                }
                            }

                            // 📦 Cálculos de totales
                            if (this.reservation.reservations_package.length > 0) {
                                let pkg = this.reservation.reservations_package[0]

                                let days = pkg.package.nights + 1
                                this.reservation.date_end = moment(this.reservation.date_init)
                                    .add(days, 'days')
                                    .format('YYYY-MM-DD')

                                let total_adult_sgl = pkg.price_per_adult_sgl * pkg.quantity_sgl
                                let total_adult_dbl = pkg.price_per_adult_dbl * (2 * pkg.quantity_dbl)
                                let total_adult_tpl = pkg.price_per_adult_tpl * (3 * pkg.quantity_tpl)
                                let total_child_with_bed = pkg.quantity_child_with_bed * pkg.price_per_child_with_bed
                                let total_child_without_bed = pkg.quantity_child_without_bed * pkg.price_per_child_without_bed

                                this.reservation.total_amount_package =
                                    total_adult_sgl +
                                    total_adult_dbl +
                                    total_adult_tpl +
                                    total_child_with_bed +
                                    total_child_without_bed
                            }
                        }
                    } catch (error) {
                        console.error("Error al obtener la reserva:", error)
                        this.loading_package = false
                    }
                },

                sendEmail () {
                    this.loading_send = true
                    let data = {
                        file_code: this.file_code,
                        email: this.email
                    }
                    axios.post(
                        baseExternalURL + 'services/client/reservation/send',
                        data,
                    ).then((result) => {
                        this.loading_send = false
                    }).catch((e) => {
                        this.loading_send = false
                        console.log(e)
                    })
                }
            },
            filters: {
                formattedDate: function (date) {
                    if (date != '') {
                        return moment(date).format('D MMM YYYY')
                    } else {
                        return ''
                    }
                },
                buildURL: function (url) {
                    let termino = 'cloudinary'
                    let posicion = url.indexOf(termino)
                    let urlBase = ''
                    if (posicion !== -1) {
                        urlBase = url
                    } else {
                        urlBase = baseExternalURL + 'images/' + url
                    }
                    console.log(urlBase)
                    return urlBase
                }
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

    .warning{
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

    .fa-minus-circle:hover, .fa-plus-circle:hover{
        color: #EB5757;
        cursor: pointer;
    }
    .wrapper{
        height: 18px;
        width: 199px;
        /* position: absolute; */
        margin: auto;
        display: flex;
        align-items: center;
            justify-content: space-around;
    }
    input[type="checkbox"]{
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
    input[type="checkbox"]:after{
        font-family: "Font Awesome 5 Free";
        font-weight: 900;
        content: "\f00c";
        font-size: 10px;
        color: white;
        display: none;
    }
    input[type="checkbox"]:hover{
        background-color: #c7c6c6;
    }
    input[type="checkbox"]:checked{
        background-color: #EB5757;
        border: solid 1px #EB5757;
    }
    input[type="checkbox"]:checked:after{
        display: block;
    }
    input[type="checkbox"]:disabled{
        background-color: rgba(235, 87, 87, .5);
        border: none;
        color: red;
        cursor: none;
    }
    .size_title{
        font-size: 24px !important;
    }
    .size_paragraph{
        font-size: 12px !important;
    }
    .background-warning {
        background: #fffbdf;
    }
    .alert-warning {
        font-size: 13px !important;
        margin: 2rem !important;
    }
    .color-inf{
        color: #2E2B9E;
    }
    .text-dark{
        color: #4F4B4B;
    }
    .text-dark2{
        color: #737373;
    }
    .text-dark3{
        color: #BDBDBD;
    }
    .fa-times:hover{
        color: #4F4B4B;
    }
    .c-dark{
        background: #FAFAFA;
    }

    </style>
@endsection
