@extends('layouts.app')
@section('content')
    @section('css')
        <style>
            .btn-sm {
                padding: 0.25rem 0.75rem;
                font-size: 13px;
                line-height: 1.5;
                width: 150px;
                height: 52px;
            }

            .modal-footer-buttons {
                display: flex;
                justify-content: center;
                margin-top: 1rem;
            }
        </style>
    @endsection
    <section class="packages__details">
        <div class="hero__primary">
        </div>
    </section>
    <section class="contenedor-quote packages__details my-5 container" v-show="client_id != ''">
        <loading-component v-show="loading2"></loading-component>
        <b-skeleton-wrapper :loading="loading_package">
            <template #loading>
                <b-row>
                    <b-col cols="10" class="mb-2">
                        <b-skeleton with="100%" height="20%"></b-skeleton>
                    </b-col>
                    <b-col cols="2" class="mb-2">
                        <b-skeleton with="100%" height="20%"></b-skeleton>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col cols="9" class="mb-2">
                        <b-skeleton with="100%" height="20%"></b-skeleton>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col cols="6" class="mb-2">
                        <b-skeleton with="100%" height="20%"></b-skeleton>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col cols="5" class="mb-5">
                        <b-skeleton with="100%" height="20%"></b-skeleton>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col cols="4" class="">
                        <b-skeleton-img height="350px"></b-skeleton-img>
                    </b-col>
                    <b-col cols="8" class="mb-2">
                        <b-skeleton class="mt-3" width="100%" height="5%"></b-skeleton>
                        <b-skeleton class="mt-3" width="100%" height="5%"></b-skeleton>
                        <b-skeleton class="mt-3" width="100%" height="5%"></b-skeleton>
                        <b-skeleton class="mt-3" width="100%" height="5%"></b-skeleton>
                        <b-skeleton class="mt-3" width="100%" height="5%"></b-skeleton>
                        <b-skeleton class="mt-5 mb-5" width="100%" height="5%"></b-skeleton>
                        <b-row>
                            <b-col cols="4" class="mb-2">
                                <b-skeleton class="mt-3" width="100%" height="5%"></b-skeleton>
                                <b-skeleton class="mt-3" width="100%" height="5%"></b-skeleton>
                                <b-skeleton class="mt-3" width="100%" height="5%"></b-skeleton>
                                <b-skeleton class="mt-3" width="100%" height="5%"></b-skeleton>
                                <b-skeleton class="mt-3" width="100%" height="5%"></b-skeleton>
                            </b-col>
                            <b-col cols="4" class="mb-2">
                                <b-skeleton class="mt-3" width="100%" height="5%"></b-skeleton>
                                <b-skeleton class="mt-3" width="100%" height="5%"></b-skeleton>
                                <b-skeleton class="mt-3" width="100%" height="5%"></b-skeleton>
                                <b-skeleton class="mt-3" width="100%" height="5%"></b-skeleton>
                                <b-skeleton class="mt-3" width="100%" height="5%"></b-skeleton>
                            </b-col>
                            <b-col cols="4" class="mb-2">
                                <b-skeleton class="mt-3" width="100%" height="5%"></b-skeleton>
                                <b-skeleton class="mt-3" width="100%" height="5%"></b-skeleton>
                                <b-skeleton class="mt-3" width="100%" height="5%"></b-skeleton>
                                <b-skeleton class="mt-3" width="100%" height="5%"></b-skeleton>
                                <b-skeleton class="mt-3" width="100%" height="5%"></b-skeleton>
                            </b-col>
                        </b-row>
                    </b-col>
                </b-row>
            </template>
            <div class="d-flex justify-content-between" v-if="package.id != null">
                <div>
                    <h1 class="package-title color-primary mb-5">1. @{{ translations.label.see_the_detail_your_booking
                        }}</h1>
                    <h2 class="package-title color-primary mb-4">@{{package.descriptions.name}} <span>@{{ package.nights + 1 }} @{{ translations.label.days }} / @{{ package.nights }} @{{ translations.label.nights }}</span>
                    </h2>
                    <p class="package-destination" style="font-weight: 400;">@{{ package.destinations.destinations |
                        formatDestinations}}</p>
                    <div class="tag clasic-vendidos" :style="'background-color:#' + package.tag.color +''">
                        @{{ package.tag.name }}
                    </div>
                </div>

            </div>
            <div class="d-flex content-details" v-if="package.id != null">
                <div class="mr-3">
                    <div>
                        <iframe class="rounded" frameborder="0"
                                style="border:0; height: 400px; opacity: 0.9;width: 320px"
                                :src="package.map_link"></iframe>
                    </div>
                    {{-- <article class="mt-4 mr-2" v-if="package.destinations.destinations.length > 0">
                        <h3 class="">@{{ translations.label.start_and_final_destination }}</h3>
                        <div class="destination d-flex mb-5">
                            <span>1. @{{ package.destinations.destinations[0].state }}</span>
                            <span>2. @{{ package.destinations.destinations[package.destinations.destinations.length - 1].state }}</span>
                        </div>
                        <h3 class="mb-4">@{{ translations.label.destinations }}</h3>
                        <div class="destination d-flex mb-5">
                            <div v-for="(destinations,index) in package.destinations.destinations" class="mr-5">
                                @{{ index + 1 }}. @{{ destinations.state }}
                            </div>
                        </div>
                    </article> --}}
                </div>
                <div class="" style="padding-left: 10%">
                    <div class="d-flex justify-content-between">
                        <div class="col-6 box-details">
                            <p class="text-uppercase font-weight-bold text-uppercase">@{{
                                translations.label.date_of_arrival_departure }}</p>
                            <p class="text-uppercase font-weight-bold text-uppercase">@{{
                                translations.label.number_passengers }}</p>
                            <p class="text-uppercase font-weight-bold text-uppercase">@{{ translations.label.rooms
                                }}</p>
                            <p class="text-uppercase font-weight-bold text-uppercase">@{{
                                translations.label.hotel_category }}</p>
                            <p class="text-uppercase font-weight-bold text-uppercase">@{{
                                translations.label.type_of_service }}</p>

                            <br>
                            <!-- inicio -->

                            <div class="mr-3">

                                <article class="mt-4 mr-2" v-if="package.destinations.destinations.length > 0">
                                    <h3 class="">@{{ translations.label.start_and_final_destination }}</h3>
                                    <div class="destination d-flex mb-5">
                                        <span>1. @{{ package.destinations.destinations[0].state }}</span>
                                        <span>2. @{{ package.destinations.destinations[package.destinations.destinations.length - 1].state }}</span>
                                    </div>
                                    <h3 class="mb-4">@{{ translations.label.destinations }}</h3>
                                    <div class="destination d-flex mb-5">
                                        <div v-for="(destinations,index) in package.destinations.destinations" class="mr-5">
                                            @{{ index + 1 }}. @{{ destinations.state }}
                                        </div>
                                    </div>
                                </article>
                            </div>

                            <!-- FIN  -->
                        </div>
                        <div class="col-5 box-details">
                            <p class="">@{{ range.start | formattedDate2 }} - @{{ range.end | formattedDate2 }}</p>
                            <p class="">@{{ quantity_persons.adults }} @{{ translations.label.adults }} @{{
                                quantity_persons.child_with_bed +
                                quantity_persons.child_without_bed }} @{{ translations.label.children }}</p>
                            <p class="">
                                <span v-if="rooms.quantity_sgl > 0"> @{{ rooms.quantity_sgl }} SGL</span>
                                <span v-if="rooms.quantity_dbl > 0"> @{{ rooms.quantity_dbl }} DBL</span>
                                <span v-if="rooms.quantity_tpl > 0"> @{{ rooms.quantity_tpl }} TPL</span>
                            </p>
                            <p class="">
                                @{{ package.rate.category.name }}
                            </p>
                            <p class="">
                                @{{ package.rate.type_service.name }}
                            </p>
                        </div>
                        <div class="box-details">
                            <div v-show="loading_hotel_search">
                                <div class="d-block mb-1 text-center text-danger">
                                    <img alt="loading" height="25px" src="/images/loading.svg"
                                         class="mx-auto d-block"/>
                                </div>
                                <div class="d-block mb-1 text-center text-danger">
                                    <img alt="loading" height="25px" src="/images/loading.svg"
                                         class="mx-auto d-block"/>
                                </div>
                                <div class="d-block mb-1 text-center text-danger">
                                    <img alt="loading" height="25px" src="/images/loading.svg"
                                         class="mx-auto d-block"/>
                                </div>
                                <div class="d-block mb-1 text-center text-danger">
                                    <img alt="loading" height="25px" src="/images/loading.svg"
                                         class="mx-auto d-block"/>
                                </div>
                                <div class="d-block mb-1 text-center text-danger">
                                    <img alt="loading" height="25px" src="/images/loading.svg"
                                         class="mx-auto d-block"/>
                                </div>
                            </div>
                            <div v-show="!loading_hotel_search">
                                <a class="d-block mb-3" @click="modalCalender = !modalCalender">@{{
                                    translations.label.edit }}</a>
                                <a class="d-block mb-3" @click="modalPax = !modalPax">@{{ translations.label.edit }}</a>
                                <a class="d-block mb-3" @click="modalRoom = !modalRoom">@{{ translations.label.edit
                                    }}</a>
                                <a class="d-block mb-3" @click="modalCategory = !modalCategory">@{{
                                    translations.label.edit }}</a>
                                <a class="d-block mb-3" @click="modalTypeServices = !modalTypeServices">@{{
                                    translations.label.edit }}</a>
                            </div>



                        </div>
                    </div>




                </div>
            </div>
            <div v-if="categorySelected !== 2 && package.cancellation_policy">
                <p class="color-primary my-5" v-if="package.cancellation_policy && !package.cancellation_policy.applies_cancellation_fees">
                    <i class="fas fa-exclamation-circle mr-1"></i>@{{
                    translations.label.canellation_without_cost_until }} @{{
                    package.cancellation_policy.last_day_cancel | formattedDateCancellation }}</p>
                <p class="color-primary my-5" v-if="package.cancellation_policy && package.cancellation_policy.applies_cancellation_fees">
                    <i class="fas fa-exclamation-circle mr-1"></i>@{{
                    translations.label.cancellation_fees_apply_on }} @{{
                    package.cancellation_policy.cancellation_fees}}%</p>
            </div>

            <div v-if="categorySelected == 2">
                <p class="color-primary my-5">
                    <i class="fas fa-exclamation-circle mr-1"></i>
                    @{{ translations.label.cancellation_hotels_luxury }}.
                </p>
            </div>
            <hr>

        </b-skeleton-wrapper>
    </section>
    <!-- inicio tabla -->
    <div class="contenedor-quote packages__details my-5 container"   v-show="client_id != ''">
        <div class=" d-flex justify-content-between my-5 content-details">
            <article class="program" style="width: 100%;height: 100%;">
                <div class="d-flex" style="width: 100%">
                    <h3 class="text-uppercase col-5 px-0 mb-3">@{{ translations.label.programme}}</h3>
                    <h3 class="text-uppercase col-3 px-0 mb-3">@{{ translations.label.hotel }}</h3>
                    <h3 class="text-uppercase col-1 px-0 mb-3">@{{ translations.label.status }}</h3>
                    <div class="" style="width: 100%;text-align: right;">
                        <span class="mr-4"><i class="uil uil-check-circle status-ok"></i> OK</span>
                        <span><i class="uil uil-exclamation-triangle status-rq"></i> RQ</span>
                    </div>
                </div>
                <div class="d-flex program-col" >
                    {{-- <div class="d-flex" id="NOThidingScrollBar" style="height: 400px;width: 100%"> --}}
                        <div class="content-list" style="width: 100%;height: 100%;padding-bottom: 10px;padding-right:0">
                            <div v-for="(itinerary,index_itinerary) in itinerary_hotels"
                                 style="background-color: #F6F6F6; border-radius: 0.5rem;" class="p-3 mb-2">
                                <div class="d-flex justify-content-between align-items-start mb-3"
                                     v-for="(hotel,index) in itinerary">
                                    <p class="txt-program mb-0 col-5 px-0 pr-2">
                                        <span
                                            class="day-pin">@{{ hotel.day | formattedDateProgram}} |</span>
                                        @{{ hotel.description_short }}
                                    </p>
                                    <p class="txt-program mb-0 col-3 px-0 pr-2"
                                       v-if="hotel.hotel.length > 0 && index === 0">
                                        <span class="day-pin font-weight-bold"
                                              style="color: #333333;text-transform: uppercase;">
                                            @{{ hotel.hotel[0].city.name }} |</span> @{{
                                        hotel.hotel[0].name }}
                                        <span style="display: none" >
                                            <br>
                                            replace :  @{{ hotel.hotel[0].replace }}
                                            <br>
                                            original :  @{{ hotel.hotel[0].original }}
                                        </span>
                                    </p>
                                    <div v-if="hotel.hotel.length > 0 && index === 0" class="col-1 px-0">
                                        <p class="status-rq mb-0 text-center"
                                           v-if="hotel.hotel[0].on_request === 1">
                                            <i class="uil uil-exclamation-triangle"></i>
                                        </p>
                                        <p class="status-ok mb-0 text-center"
                                           v-if="hotel.hotel[0].on_request === 0">
                                            <i class="uil uil-check-circle"></i>
                                        </p>
                                    </div>
                                    <div class="content-hotel mb-0 col-3 px-0">
                                        <div v-if="hotel.hotel.length > 0 && index === 0">
                                            <div class="text-center text-danger"
                                                 v-show="loading_hotel_search">
                                                <img alt="loading" height="35px" src="/images/loading.svg"
                                                     class="mx-auto d-block"/>
                                                @{{ translations.label.searching }}...
                                            </div>
                                            <v-select

                                                style="width: 100%"
                                                v-show="!loading_hotel_search"
                                                @input="hotel_to_change => updateHotel(hotel.hotel[0], hotel_to_change, 0)"
                                                class="form-control"
                                                :placeholder="translations.label.change_hotel"
                                                label="hotel_name"

                                                :options="hotelsList[index_itinerary]"

                                            >

                                            <template v-slot:option="status">
                                                <span v-if="status.on_request == 0">
                                                    <i class="uil uil-check-circle status-ok"></i> @{{status.hotel_name}} </span>
                                                <span v-else>
                                                    <i class="uil uil-exclamation-triangle status-rq"></i> @{{status.hotel_name}} </span>
                                            </template>
                                            </v-select>
                                        </div>
                                        <div style="width: 200px" v-else></div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    {{-- </div> --}}
                </div>
            </article>

        </div>
        <hr>
        {{--PRECIOS--}}

        <div class="contenedor-quote my-5 d-flex " style="letter-spacing: 0;justify-content: end">
            <div class="col-5 my-4" style="display: grid;justify-content: end">
                <div class="d-flex justify-content-start align-items-center mb-3"
                     v-for="service in services" v-show="!package_not_found">
                    <label class="checkbox-ui text-left" @click="addServiceToPackage(service)"
                           v-if="!checkIfAddedService(service)">
                        <i class="far fa-square"></i>
                        @{{ translations.label.i_want_pay }} -
                        @{{service.commercial_descriptions.name}}
                    </label>
                    <label class="checkbox-ui text-left" @click="removeServiceToPackage(service)"
                           v-if="checkIfAddedService(service)">
                        <i class="fa fa-check-square"></i>
                        @{{ translations.label.i_want_pay }} -
                        @{{service.commercial_descriptions.name}}
                    </label>
                </div>
                <div style="width: 200px">
                    <a class="font-weight-bold" href="/cancellation-policies" target="_blank"><span
                            class="icon-ac-info mr-1"></span>@{{ translations.label.cancellation_policies }}</a>
                </div>
            </div>

            <div v-if="package.country_id != 89 || holidays=='0'" class="col-5 content-pax" style="display: grid;">
                <div class="d-flex my-4">
                    <div class="text-total" v-show="!package_not_found">
                        <span class="d-block">@{{ quantity_persons.adults }} @{{ translations.label.adults_s }}</span>
                        <span class="d-block span-xs" v-if="rooms.quantity_sgl > 0">
                            @{{ translations.label.x_adult_simple_room }}
                        </span>
                        <span class="d-block span-xs" v-if="rooms.quantity_dbl > 0">
                            @{{ translations.label.x_adult_double_room }}
                        </span>
                        <span class="d-block span-xs" v-if="rooms.quantity_tpl > 0">
                            @{{ translations.label.x_adult_triple_room }}
                        </span>
                    </div>
                    <div v-show="!package_not_found">
                        <span class="d-block">
                            <i class="fas fa-spinner fa-spin" v-show="loading_package_filter"></i>
                            <span v-show="!loading_package_filter">
                                $ @{{ getPriceAmount(package.amounts.total_adults) }}
                            </span>
                        </span>
                        <span class="d-block span-xs" v-if="rooms.quantity_sgl > 0">
                            <i class="fas fa-spinner fa-spin" v-show="loading_package_filter"></i>
                            <span v-show="!loading_package_filter">$ @{{ getPriceAmount(package.amounts.price_per_adult.room_sgl) }}</span>
                        </span>
                        <span class="d-block span-xs" v-if="rooms.quantity_dbl > 0">
                            <i class="fas fa-spinner fa-spin" v-show="loading_package_filter"></i>
                            <span v-show="!loading_package_filter">$ @{{ getPriceAmount(package.amounts.price_per_adult.room_dbl) }}</span>
                        </span>
                        <span class="d-block span-xs" v-if="rooms.quantity_tpl > 0">
                            <i class="fas fa-spinner fa-spin" v-show="loading_package_filter"></i>
                            <span v-show="!loading_package_filter">$ @{{ getPriceAmount(package.amounts.price_per_adult.room_tpl) }}</span>
                        </span>
                    </div>
                </div>
                <div class="d-flex my-4" v-if="package.amounts.price_per_child.with_bed != 0">
                    <div class="text-total" v-show="!package_not_found">
                        <span class="d-block">@{{ package.quantity_child.with_bed }} @{{ translations.label.children_with_bed }}</span>
                        <span class="d-block span-xs">@{{ translations.label.x_child_with_bed }}</span>
                    </div>
                    <div v-show="!package_not_found">
                        <span class="d-block">
                            <i class="fas fa-spinner fa-spin" v-show="loading_package_filter"></i>
                            <span v-show="!loading_package_filter">$ @{{ getPriceAmount(package.amounts.total_children.with_bed) }}</span>
                        </span>
                        <span class="d-block span-xs">
                            <i class="fas fa-spinner fa-spin" v-show="loading_package_filter"></i>
                            <span v-show="!loading_package_filter">$ @{{ getPriceAmount(package.amounts.price_per_child.with_bed) }}</span>
                        </span>
                    </div>
                </div>
                <div class="d-flex pb-5 my-4 border-bottom"
                     v-if="package.amounts.price_per_child.without_bed != 0">
                    <div class="text-total" v-show="!package_not_found">
                        <span class="d-block">
                            @{{ package.quantity_child.without_bed }} @{{ translations.label.children_without_bed }}
                        </span>
                        <span class="d-block span-xs">
                            @{{ translations.label.x_child_without_bed }}
                        </span>
                    </div>
                    <div v-show="!package_not_found">
                        <span class="d-block">
                            <i class="fas fa-spinner fa-spin" v-show="loading_package_filter"></i>
                            <span v-show="!loading_package_filter">$ @{{ getPriceAmount(package.amounts.total_children.without_bed) }}</span>
                        </span>
                        <span class="d-block span-xs">
                            <i class="fas fa-spinner fa-spin" v-show="loading_package_filter"></i>
                            <span v-show="!loading_package_filter">$ @{{ getPriceAmount(package.amounts.price_per_child.without_bed) }}</span>
                        </span>
                    </div>
                </div>
              <div class="d-flex total-price" v-if="!package_not_found">
                <div class="text-total" style="line-height: 1;">
                   @{{ (userTypeIsClient && commission_status === 1) ? @json(__('global.label.with_commission')) : translations.label.total }}
                </div>
                <div style="width: 120px">
                    <i class="fas fa-spinner fa-spin" v-show="loading_package_filter"></i>
                    <span v-show="!loading_package_filter">
                    $ @{{ totalAmountWithCommission }}
                    </span>
                </div>
                </div>


            </div>
            <div v-else class="col-5 content-pax" style="display: grid;"></div>
            <div class="col-2 row">
                <div class="col-md-12 mb-2 " style="padding-right: 0px">
                    <span id="disabled-wrapper" class="d-flex" tabindex="0">
                        <button v-if="package.country_id != 89 || holidays=='0'" type="button" class="btn-primary float-right" @click="goToPassengers"
                                :disabled="!!loading_search_package || !!loading_hotel_search || package_not_found || isDisabledReservation"
                                style="width: 220px" id="reservation-disabled">
                            <i class="fas fa-spinner fa-spin"
                            v-show="loading_search_package || loading_hotel_search"></i>
                            @{{ translations.label.continue }}
                        </button>
                    </span>
                    <b-tooltip show target="disabled-wrapper" placement="topleft" v-if="isDisabledReservation">
                        <span style="font-size: 13px!important;">{{ trans('reservations.label.disabled') }}</span>
                    </b-tooltip>
                </div>
                <div class="col-md-12" v-if="package.allow_modify" style="padding-right: 0px">
                    <button type="button" class="btn-primary float-right" @click="edit()" v-if="user_type_id != '4'  "
                            :disabled="!!loading_search_package || !!loading_hotel_search || package_not_found"
                            style="width: 100%">
                        <i class="fas fa-spinner fa-spin"
                           v-show="loading_search_package || loading_hotel_search"></i>
                        {{trans('package.label.quote')}}

                    </button>
                    <button type="button" class="btn-primary float-right" @click="edit()" v-if="user_type_id == '4' "
                            :disabled="!!loading_search_package || !!loading_hotel_search || package_not_found"
                            style="width: 100%">
                        <i class="fas fa-spinner fa-spin"
                           v-show="loading_search_package || loading_hotel_search"></i>
                           {{trans('package.label.modify_package')}}
                    </button>

                </div>
            </div>
        </div>
    </div>
    <!-- fin tabla -->
    <div class="container mt-5 mb-5" v-if="client_id === ''">
        <div class="jumbotron bg-danger">
            <h2 class="text-center text-white">
                <i class="fas fa-exclamation-triangle"></i> @{{ translations.label.you_must_select_customer }}... <i
                    class="fas fa-hand-point-up"></i>
            </h2>
        </div>
    </div>

    <section class="packages__details my-5 container pb-5"
             v-if="package.id === null && !loading_package && client_id != ''">
        <div class="jumbotron">
            <h2 class="text-center"><i class="fas fa-sad-tear"></i> ¡@{{ translations.label.were_sorry_information_found
                }}.</h2>
        </div>
    </section>

    <div class="packages__details background-secondary pb-5" v-if="services.length > 0 && client_id != ''">
        <section class="container py-5">
            <div class="py-5">
                <h2 class="my-4 title-section">@{{ translations.label.contribute_sustainable_peru }}</h2>
            </div>
            <div class="d-flex content-details my-5" style="letter-spacing: 0;" v-for="(service,index) in services">
                <div class="mr-3">
                    <img v-if="service.galleries.length > 0" :src="service.galleries[0].url"
                         onerror="this.onerror=null;this.src='https://res.cloudinary.com/litomarketing/image/upload/c_scale,w_400/v1440093948/paragliding_in_Miraflores._123110314_dlyun3.jpg'"
                         class="rounded" alt="service" style="width: 450px;">
                    <img class="rounded"
                         src="https://res.cloudinary.com/litomarketing/image/upload/c_scale,w_400/v1440093948/paragliding_in_Miraflores._123110314_dlyun3.jpg"
                         alt="Image Service" v-else style="width: 450px;">
                </div>
                <div class="container">
                    <h2 class="mb-5" style="line-height: 24px;">@{{ service.commercial_descriptions.name
                        }}</h2>
                    <p class="mb-5">
                        @{{ service.commercial_descriptions.description }}
                    </p>
                    <form class="mt-4">
                        <div class="d-flex justify-content-start align-items-center mt-2 mb-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="">@{{ translations.label.number_passengers }}</label>
                                </div>
                                <div class="col-md-6 p-0">
                                    <div class="input-actions ">
                                        <vue-numeric-input v-model="service.quantity_adult"
                                                           @input="getService(service)"
                                                           :min="1"
                                                           name="qty_adult_service"
                                                           :precision="0" id="qty_adult_service"></vue-numeric-input>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-start align-items-center mt-2 mb-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <label>@{{ translations.label.amount_donate }}</label>
                                </div>
                                <div class="col-md-6 p-0">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="d-flex">
                                                <div class="content-donate">
                                                    <input type="text" class="form-control"
                                                           :value="service.total_amount" readonly>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 p-0">
                                            <button type="button" class="btn-primary ml-4"
                                                    v-if="!checkIfAddedService(service)"
                                                    style="height: 40px;line-height: 0;width: 100%;"
                                                    @click="addServiceToPackage(service)" :disabled="!!loading_service">
                                                @{{ translations.label.add }} <i class="fas fa-spinner fa-spin"
                                                                                 v-show="loading_service"></i>
                                            </button>
                                            <button type="button" class="btn-secondary ml-4"
                                                    v-if="checkIfAddedService(service,false)"
                                                    style="height: 40px;line-height: 0;width: 100%;"
                                                    @click="removeServiceToPackage(service)"
                                                    :disabled="!!loading_service">
                                                @{{ translations.label.cancel }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>


                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
    {{--    Modal fechas--}}
    <b-modal v-model="modalCalender" :no-close-on-backdrop="true" :no-close-on-esc="true" :hide-header-close="true"
             id="modalCalender" style="width: max-content!important;">
        <div style="width: max-content!important;">
            <div class="mb-5">
                <h2>@{{ translations.label.date_of_arrival_departure }}</h2>
            </div>
            <div class="mb-5">
                <div class="d-flex align-items-start justify-content-between mb-3"
                     v-if="package.fixed_outputs.length > 0">
                    <p class="mr-1 py-4" style="font-size: 15px">@{{ translations.label.fixed_departures }}:</p>
                    <div class="flex justify-center items-center datepicker-calender">
                        <v-date-picker v-model="range_to_change" is-required>
                            <template>
                                <span class="icon-ac-calendar1"></span>
                                <label for="">@{{ translations.label.check_in }}:</label>
                                <select name="select" class="select-fixed-outputs"
                                        @change="changeDateFixed(daySelected)" v-model="daySelected">
                                    <option :value="date.date" v-for="date in package.fixed_outputs"> @{{ date.date
                                        | formattedDate }}
                                    </option>
                                </select>
                                <label for="">@{{ translations.label.check_out }}:</label>
                                <input :value="range_to_change.end | formattedDate"
                                       class="ml-1"
                                />
                            </template>
                        </v-date-picker>
                    </div>
                </div>
                <v-date-picker class="flex justify-center items-center datepicker-calender__secondary"
                               v-model="range_to_change"
                               locale="es" v-if="package.fixed_outputs.length === 0">
                    <template>
                        <span class="icon-ac-calendar1"></span>
                        <label class="mr-1">@{{ translations.label.check_in }}:</label>
                        <input :value="range_to_change.start | formattedDate"
                               class="ml-1"/>
                        <span class="icon-ac-calendar1"></span>
                        <label class="mr-1">@{{ translations.label.check_out }}:</label>
                        <input :value="range_to_change.end | formattedDate"
                               class="ml-1"
                        />
                    </template>
                </v-date-picker>
            </div>
            <button type="button" @click="cambiar()" class="hide" ref="cambiarfecha"></button>
            <div class="mb-5">
                <v-date-picker :columns="$screens({ default: 1, lg: 2 })" v-model="range_to_change" color="red"
                               fillMode="outline" mode="date" mode="range_to_change" ref="calendar_view"
                               :value="range_to_change"
                               is-range locale="es" :min-date='min_date' @dayclick="onDayClick"
                               v-if="package.fixed_outputs.length === 0">
                </v-date-picker>
                <v-date-picker :columns="$screens({ default: 1, lg: 2 })" v-model="range_to_change" color="red"
                               fillMode="outline" mode="date" mode="range_to_change" style="pointerEvents:none;"
                               :value="range_to_change"
                               ref="calendarfixed"
                               is-range locale="es" :min-date='min_date'
                               v-if="package.fixed_outputs.length > 0">
                </v-date-picker>
            </div>
            <button class="btn-cancel mr-4" @click="closeModalDates">@{{ translations.label.cancel }}</button>
            <button class="btn-primary" @click="saveModalDates()">@{{ translations.label.save }}</button>
        </div>
    </b-modal>
    {{--    Modal pasajeros--}}
    <b-modal v-model="modalPax" :no-close-on-backdrop="true" :no-close-on-esc="true" :hide-header-close="true">
        <article class="content-pax">
            <div class="mb-5">
                <h2>@{{ translations.label.passengers }}</h2>
            </div>
            <div class="mb-4 border-bottom">
                <p class="mb-3"><strong>@{{ translations.label.adults }}</strong></p>
                <div class="d-flex align-items-center justify-content-between pb-4">
                    <span>@{{ translations.label.age }}: @{{package.adult_age_from}} @{{ translations.label.years_more }}</span>
                    <div class="input-actions">
                        <vue-numeric-input v-model="quantity_persons_change.adults_"
                                           @input="changeAdult"
                                           :min="1"
                                           name="qty_adult"
                                           :precision="0" id="qty_adult"></vue-numeric-input>
                    </div>
                </div>
            </div>
            <div class="mb-5">
                <p class="mb-3"><strong>@{{ translations.label.children }}:</strong></p>
                <div class="pb-4">
                    <div>
                        <span>@{{ translations.label.with_bed }}</span>
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <span class="span-xs">@{{ translations.label.of }} @{{ package.prices_children.with_bed.min_age }} @{{ translations.label.to }} @{{ package.prices_children.with_bed.max_age }} @{{ translations.label.years }}</span>
                            <div class="input-actions">
                                <vue-numeric-input v-model="quantity_persons_change.child_with_bed_"
                                                   @input="changeInput"
                                                   :min="0" name="qty_child_with_bed"
                                                   :precision="0" id="qty_child_with_bed"></vue-numeric-input>
                            </div>
                        </div>
                    </div>
                    <div>
                        <span>@{{ translations.label.without_bed }}</span>
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <span class="span-xs">@{{ translations.label.of }} @{{ package.prices_children.without_bed.min_age }} @{{ translations.label.to }} @{{ package.prices_children.without_bed.max_age }} @{{ translations.label.years }}</span>
                            <div class="input-actions">
                                <vue-numeric-input v-model="quantity_persons_change.child_without_bed_"
                                                   @input="changeInput"
                                                   :min="0" name="qty_child_without_bed"
                                                   :precision="0" id="qty_child_without_bed"></vue-numeric-input>
                            </div>
                        </div>
                    </div>
                    <span class="my-3 span-xs">*@{{ translations.label.toddlers_from }} @{{package.infant_age_allowed.min}} @{{ translations.label.months_to }} @{{package.infant_age_allowed.max}} @{{package.infant_age_allowed.year}} @{{translations.label.year_do_not_pay}}</span>
                </div>
            </div>

        </article>
        <button class="btn-cancel mr-4" @click="closeModalPax">@{{ translations.label.cancel }}</button>
        <button class="btn-primary" @click="saveModalPax">@{{ translations.label.save }}</button>
    </b-modal>
    {{--    Modal habitaciones --}}
    <b-modal v-model="modalRoom" :no-close-on-backdrop="true" :no-close-on-esc="true" :hide-header-close="true">
        <article class="content-pax">
            <div class="mb-5">
                <h2>@{{ translations.label.rooms }}</h2>
            </div>
            <div class="mb-5">
                <div class="mb-4">
                    <div class="row m-0">
                        <div class="col-4 text-center">
                            <strong>@{{ translations.label.adults }}</strong><br>
                            @{{ quantity_persons.adults }}
                        </div>
                        <div class="col-4 text-center">
                            <strong>@{{ translations.label.children }} @{{ translations.label.with_bed }}</strong><br>
                            @{{ quantity_persons.child_with_bed }}
                        </div>
                        <div class="col-4 text-center">
                            <strong>@{{ translations.label.children }} @{{ translations.label.without_bed
                                }}</strong><br>
                            @{{ quantity_persons.child_without_bed }}
                        </div>
                        <div class="col-12">
                            <hr>
                        </div>
                    </div>
                    <div class="alert alert-danger mt-3" v-if="validate_rooms_and_pax" style="font-size: 11px">
                        <i class="fas fa-info-circle"></i>
                        {{trans('package.validate.quantity_rooms')}}
                    </div>
                    <div class="d-flex align-items-center justify-content-between pb-4">
                        <span class="span-xs mr-4">@{{ translations.label.simple }}</span>
                        <div class="input-actions"
                             :class="{'validate_rooms':validate_rooms_and_pax && !disable_room_sgl}">
                            <vue-numeric-input v-model="rooms_to_change._quantity_sgl"
                                               :min="0"
                                               @input="validateQuantityRoomSgl"
                                               @change="validateQuantityRoomSgl"
                                               :disabled="disable_room_sgl"
                                               :precision="0" id="quantity_rooms_sgl"></vue-numeric-input>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between pb-4">
                        <span class="span-xs mr-4">@{{ translations.label.double }}</span>
                        <div class="input-actions"
                             :class="{'validate_rooms':validate_rooms_and_pax && !disable_room_dbl}">
                            <vue-numeric-input v-model="rooms_to_change._quantity_dbl"
                                               @input="validateQuantityRoomDbl"
                                               @change="validateQuantityRoomDbl"
                                               :min="0"
                                               :disabled="disable_room_dbl"
                                               :precision="0" id="quantity_rooms_dbl"></vue-numeric-input>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between pb-4">
                        <span class="span-xs mr-4">@{{ translations.label.triple }}</span>
                        <div class="input-actions"
                             :class="{'validate_rooms':validate_rooms_and_pax && !disable_room_tpl}">
                            <vue-numeric-input v-model="rooms_to_change._quantity_tpl"
                                               @input="validateQuantityRoomTpl"
                                               @change="validateQuantityRoomTpl"
                                               :min="0"
                                               :disabled="disable_room_tpl"
                                               :precision="0" id="quantity_rooms_tpl"></vue-numeric-input>
                        </div>
                    </div>
                    <div class="alert alert-danger" v-if="validate_rooms_and_pax && quantity_persons_change.child_with_bed_ > 0" style="font-size: 11px;">
                        <i class="fas fa-info-circle"></i>
                        {{trans('package.validate.quantity_child_rooms')}}
                    </div>
                    <div class="d-flex align-items-center justify-content-between pb-4"
                         v-for="(child,index_child) in quantity_persons.child_with_bed"
                         v-if="quantity_persons.child_with_bed > 0">
                        <span class="span-xs mr-4"
                              style="width: 110px">{{trans('package.label.assign_child')}} @{{ index_child + 1 }}</span>
                        <div class="input-actions">
                            <v-select class="form-control" v-model="childrenRoomSelected[index_child]"
                                      :options="rooms_children" label="name"
                                      @input="changeChildrenRoom(index_child,childrenRoomSelected[index_child])"
                                      :selectable="room => !room.disabled"
                                      :reduce="room => room.value">
                            </v-select>
                        </div>
                    </div>
                </div>
            </div>
        </article>
        <button class="btn-cancel mr-4" @click="closeModalRooms">@{{ translations.label.cancel }}</button>
        <button class="btn-primary" @click="saveModalRooms">@{{ translations.label.save }}</button>
    </b-modal>
    {{--    Modal categoria --}}
    <b-modal v-model="modalCategory" :no-close-on-backdrop="true" :no-close-on-esc="true" :hide-header-close="true">
        <article class="content-pax">
            <div class="mb-5">
                <h2>@{{ translations.label.hotel_category }}</h2>
            </div>
            <div class="mb-5">
                <b-form-group class="mb-2">
                    <b-form-radio v-model="categorySelected_change" class="mb-3" size="lg" name="some-radios"
                                  :value="category.id" v-for="category in package.categories">
                        <span class="span-xs mr-4">@{{ category.name }}</span>
                    </b-form-radio>
                </b-form-group>
            </div>
        </article>
        <button class="btn-cancel mr-4" @click="closeModalCategory">@{{ translations.label.cancel }}</button>
        <button class="btn-primary" @click="saveModalCategory">@{{ translations.label.save }}</button>
    </b-modal>
    {{--    Modal tipo de servicios --}}
    <b-modal v-model="modalTypeServices" :no-close-on-backdrop="true" :no-close-on-esc="true" :hide-header-close="true">
        <article class="content-pax">
            <div class="mb-5">
                <h2>@{{ translations.label.type_of_service }}</h2>
            </div>
            <div class="mb-5">
                <div class="mb-4">
                    <b-form-radio v-model="typeServiceSelected_change" class="mb-3" size="lg" name="some-radios"
                                  :value="type.id" v-for="type in package.type_services">
                        <span class="span-xs mr-4">@{{ type.name }}</span>
                    </b-form-radio>
                </div>
            </div>
        </article>
        <button class="btn-cancel mr-4" @click="closeModalTypeService">@{{ translations.label.cancel }}</button>
        <button class="btn-primary" @click="saveModalTypeService">@{{ translations.label.save }}</button>
    </b-modal>
    {{-- Modal para confirmacion de numero de file --}}
    <b-modal v-model="modalFileNumber" :no-close-on-backdrop="true" :no-close-on-esc="true" :hide-header-close="true">
        <template #modal-header="{ close }">
            <div class="w-100 position-relative">
                <button type="button" class="btn-close-modal" @click="closeModalFileNumber">
                    <i class="fas fa-times"></i>
                    <span>Cerrar</span>
                </button>
            </div>
        </template>
        <article class="content-pax">
            <div class="mb-5">
                <h2>@{{ translations.label.file_number_title }}</h2>
                <p class="mb-3">@{{ translations.label.file_number_message }}</p>

                <b-form-group>
                    <b-form-input v-model="fileNumber" :placeholder="translations.label.file_number_placeholder"></b-form-input>
                </b-form-group>
            </div>
        </article>
        <div class="modal-footer-buttons">
            <button class="btn-cancel btn-sm mr-3" @click="continueWithoutFileNumber">@{{ translations.label.continue_without }}</button>
            <button class="btn-primary btn-sm" @click="saveFileNumberAndContinue">@{{ translations.label.save_and_continue }}</button>
        </div>
    </b-modal>
    <section-write-us-component></section-write-us-component>
    {{-- MODAL ALERTA COTI--}}
    <div id="modalAlertaCotizacion" tabindex="1" role="dialog" class="modal show" ref="modalAlerta">
        <div role="document" class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <h4 class="text-center text-danger">
                        <div class="icon">
                            <i class="icon-alert-circle" v-if="!loading"></i>
                            <i class="spinner-grow" v-if="loading"></i>
                        </div>
                        <strong v-if="!loading">{{trans('quote.label.you_are_about_to_replace')}}!</strong>
                        <strong v-if="loading">{{trans('quote.label.loading')}}</strong>
                    </h4>
                    <p class="text-center" v-if="!loading"><strong>{{trans('quote.label.we_suggest_you_save')}}
                            .</strong></p>
                    <div class="group-btn" v-if="!loading">
                        <button type="button" @click="putQuote()" data-dismiss="modal" class="btn btn-secondary">
                            {{trans('quote.label.replace')}}
                        </button>
                        <button type="button" @click="goToQuotesFront()" data-dismiss="modal" class="btn btn-primary">
                            {{trans('quote.label.save_first')}}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('js')
    <script>
        new Vue({
            el: '#app',
            data: {
                modalFileNumber: false,
                fileNumber: '',
                client_id: '',
                loading: false,
                loading2: false,
                package_not_found: false,
                isActive: true,
                modalCalender: false,
                modalPax: false,
                modalRoom: false,
                modalCategory: false,
                modalTypeServices: false,
                range_to_change: {
                    start: null,
                    end: null
                },
                range: {
                    start: null,
                    end: null
                },
                min_date: moment().add(1, 'days').format('YYYY-MM-DD'),
                lang: 'en',
                hotelsList: [],
                quantity_persons_change: {
                    adults_: 2,
                    child_with_bed_: 0,
                    child_without_bed_: 0,
                    age_child: [
                        {
                            age: 1,
                        },
                    ],
                },
                quantity_persons: {
                    adults: 2,
                    child_with_bed: 0,
                    child_without_bed: 0,
                    age_child: [
                        {
                            age: 1,
                        },
                    ],
                },
                rooms_to_change: {
                    _quantity_sgl: 0,
                    _quantity_dbl: 0,
                    _quantity_child_dbl: 0,
                    _quantity_tpl: 0,
                    _quantity_child_tpl: 0,
                },
                rooms: {
                    quantity_sgl: 0,
                    quantity_dbl: 0,
                    quantity_child_dbl: 0,
                    quantity_tpl: 0,
                    quantity_child_tpl: 0,
                },
                type_service: 1,
                package_ids: [],
                itinerary_hotels: [],
                services: [],
                services_added_package: [],
                hotels_changed: [],
                package: {
                    id: null,
                    country_id: null,
                    adult_age_from: 0,
                    allow_infant: 0,
                    allow_child: 0,
                    nights: 0,
                    infant_age_allowed: {
                        min: 0,
                        max: 0
                    },
                    descriptions: {
                        name: '',
                        description: '',
                        itinerary: [],
                        itinerary_link: '',
                    },
                    destinations: {
                        destinations: [],
                        destinations_display: ''
                    },
                    tag: {
                        name: '',
                        color: ''
                    },
                    included_services: {
                        breakfast_days: [],
                        accommodation: [],
                        lunch_days: [],
                        dinner_days: [],
                        transport: false,
                        includes_flights: false,
                        include_guides_tickets: {
                            guides: true,
                            tickets: false,
                        },
                    },
                    fixed_outputs: [],
                    flights: [],
                    highlights: [],
                    type_services: [],
                    categories: [],
                    rate: {
                        category: {
                            name: ''
                        },
                        type_service: {
                            name: ''
                        }
                    },
                    prices_children: {
                        with_bed: {
                            price: 0,
                            min_age: 0,
                            max_age: 0,
                        },
                        without_bed: {
                            price: 0,
                            min_age: 0,
                            max_age: 0,
                        }
                    },
                    quantity_child: {
                        quantity_children: 0,
                        with_bed: 0,
                        without_bed: 0,
                    },
                    cancellation_policy: {
                        applies_cancellation_fees: false,
                        cancellation_fees: 0,
                        last_day_cancel: null,
                    },
                    children_age_allowed: {
                        with_bed: {
                            min_age: 0,
                            max_age: 0,
                        },
                        without_bed: {
                            min_age: 0,
                            max_age: 0,
                        }
                    },
                    allow_modify: false,
                    token_search: '',
                    amounts: {
                        total_amount: 0,
                        price_per_person: 0,
                        price_per_adult: {
                            room_sgl: 0,
                            room_dbl: 0,
                            room_tpl: 0,
                        },
                        total_adults: 0,
                        price_per_child: {
                            with_bed: 0,
                            without_bed: 0
                        },
                        total_children: {
                            with_bed: 0,
                            without_bed: 0
                        }
                    }
                },
                slideHighlight: 0,
                typeServiceSelected: '',
                typeServiceSelected_change: '',
                categorySelected: '',
                categorySelected_change: '',
                loading_package: true,
                show_package: false,
                days_package: 1,
                daySelected: null,
                disable_room_sgl: false,
                disable_room_dbl: false,
                disable_room_tpl: false,
                date_to_days: 0,
                loading_search_package: false,
                loading_hotel_search: false,
                loading_service: false,
                validate_rooms_and_pax: false,
                translations: {
                    label: {},
                    validations: {},
                    messages: {}
                },
                loading_package_filter: false,
                rooms_children: [
                    {
                        value: 'double',
                        name: 'Doble',
                        disabled: false
                    },
                    {
                        value: 'triple',
                        name: 'Triple',
                        disabled: false
                    }
                ],
                childrenRoomSelected: [],
                user_type_id: '',
                holidays : 0,
                commission_percentage: 0,
                commission_status: 0,
            },
            created: function () {
                this.min_date = new Date(moment(this.min_date))
                this.client_id = localStorage.getItem('client_id')
                this.user_type_id = localStorage.getItem('user_type_id')
                this.lang = localStorage.getItem('lang')
                this.$root.$on('changeMarkup', function () {
                    this.client_id = localStorage.getItem('client_id')
                    this.getPackagesDetails()
                    this.getServices()
                })
                if (localStorage.getItem('parameters_packages_details')) {
                    let parameters = JSON.parse(localStorage.getItem('parameters_packages_details'))
                    if (parameters.date) {
                        this.range.start = new Date(moment(parameters.date))
                        this.date_to_days = parameters.date_to_days
                        this.range.end = new Date(moment(parameters.date).add(parameters.date_to_days, 'days').format('YYYY-MM-DD'))
                        this.range_to_change.start = new Date(moment(parameters.date))
                        this.range_to_change.end = new Date(moment(parameters.date).add(parameters.date_to_days, 'days').format('YYYY-MM-DD'))
                        this.daySelected = parameters.date

                    }

                    if (parameters.quantity_persons) {
                        this.quantity_persons = parameters.quantity_persons
                        this.quantity_persons_change = {
                            adults_: parameters.quantity_persons.adults,
                            child_with_bed_: parameters.quantity_persons.child_with_bed,
                            child_without_bed_: parameters.quantity_persons.child_without_bed,
                            age_child: [
                                {
                                    age: 1,
                                },
                            ],
                        }
                    }

                    if (parameters.rooms) {
                        let rooms = parameters.rooms
                        this.rooms = rooms
                        this.rooms_to_change = {
                            _quantity_sgl: rooms.quantity_sgl,
                            _quantity_dbl: rooms.quantity_dbl,
                            _quantity_child_dbl: rooms.quantity_child_dbl,
                            _quantity_tpl: rooms.quantity_tpl,
                            _quantity_child_tpl: rooms.quantity_child_tpl,
                        }

                        if (parameters.quantity_persons.child_with_bed > 0) {
                            this.childrenRoomSelected = parameters.children_accommodation
                        }

                    }

                    if (parameters.type_service) {
                        this.typeServiceSelected = parameters.type_service
                        this.typeServiceSelected_change = parameters.type_service
                    }

                    if (parameters.category) {
                        this.categorySelected = parameters.category
                        this.categorySelected_change = parameters.category
                    }

                    if (parameters.package_ids) {
                        this.package_ids = parameters.package_ids
                    }
                } else {
                    window.location.href = '/packages'
                }
            },
            mounted () {
                this.$root.$emit('loadingPage', {typeBack: 2})
                localStorage.setItem('package_reservation', JSON.stringify({}))
                this.setTranslations()
                this.getPackagesDetails()
                this.getServices()
            },
            computed: {
                isDisabledReservation: function () {
                    const response = parseInt(localStorage.getItem('client_disable_reservation') ?? 0);
                    return (response === 1);
                },
                userTypeIsClient() {
                    return localStorage.getItem("user_type_id") === "4";
                },
                totalAmountWithCommission() {
                    // Solo calcula si el status es 1 y hay porcentaje
                    if (this.commission_status === 1 && this.commission_percentage > 0) {
                        const total = this.package.amounts.total_amount;
                        const commissionRate = this.commission_percentage / 100;
                        const withCommission = total * (1 + commissionRate);
                        // Redondear siempre hacia arriba al entero más cercano
                        return Math.ceil(withCommission);
                    }
                    return this.package.amounts.total_amount; // No mostrar si no aplica
                }
            },
            methods: {
                goBiosafetyProtocols () {
                    window.location.href = '/biosafety-protocols'
                },
                setTranslations () {
                    axios.get(baseURL + 'translation/' + localStorage.getItem('lang') + '/slug/package').then((data) => {
                        this.translations = data.data
                    })
                },
                validate_rooms () {
                    this.rooms_to_change._quantity_sgl = 0
                    this.rooms_to_change._quantity_dbl = 0
                    this.rooms_to_change._quantity_tpl = 0

                    let total_adults = parseInt(this.quantity_persons_change.adults_)
                    let total_children_bed = parseInt(this.quantity_persons_change.child_with_bed_)

                    if (total_children_bed === 0) {
                        this.rooms_to_change._quantity_child_dbl = 0
                        this.rooms_to_change._quantity_child_tpl = 0
                    }

                    if (total_adults === 1) {
                        this.rooms_to_change._quantity_sgl = 1
                        this.disable_room_sgl = false
                        this.disable_room_dbl = true
                        this.disable_room_tpl = true
                    } else if (total_adults === 2) {
                        this.rooms_to_change._quantity_dbl = 1
                        this.disable_room_sgl = false
                        this.disable_room_dbl = false
                        this.disable_room_tpl = true
                        if (total_children_bed > 0) {
                            this.disable_room_tpl = false
                        }

                    } else if (total_adults === 3) {
                        this.rooms_to_change._quantity_tpl = 1
                        this.disable_room_sgl = false
                        this.disable_room_dbl = false
                        this.disable_room_tpl = false
                    } else if (total_adults > 3) {
                        this.disable_room_sgl = false
                        this.disable_room_dbl = false
                        this.disable_room_tpl = false
                    }
                    this.resetChildrenAccommodation()
                    this.assignedRoomChild(total_adults, total_children_bed)
                },
                assignedRoomChild: function (total_adults, total_child) {
                    if (total_child > 0) {
                        if (total_adults % 2 === 0) {
                            if (total_adults === 2 && total_child === 1) {
                                this.rooms_to_change._quantity_sgl = 1
                            }
                            for (let i = 0; i < total_child; i++) {
                                this.changeChildrenRoom(i, 'double')
                            }
                        } else {
                            if (total_adults === 3 && total_child === 1) {
                                this.rooms_to_change._quantity_sgl = 1
                                this.rooms_to_change._quantity_tpl = 1
                            }
                            if (total_adults >= 3) {
                                for (let i = 0; i < total_child; i++) {
                                    this.changeChildrenRoom(i, 'triple')
                                }
                            }
                        }
                    }
                },
                resetChildrenAccommodation () {
                    for (let i = 0; i < this.childrenRoomSelected.length; i++) {
                        this.childrenRoomSelected[i] = ''
                    }
                },
                validateQuantityRoomSgl: function (event) {
                    this.rooms_to_change._quantity_sgl = event
                    this.validateRoomsAndPax()
                },
                validateQuantityRoomDbl: function (event) {
                    this.rooms_to_change._quantity_dbl = event
                    this.validateRoomsAndPax()
                },
                validateQuantityRoomChildDbl: function (event) {
                    this.rooms_to_change._quantity_child_dbl = event
                    this.validateRoomsAndPax()
                },
                validateQuantityRoomTpl: function (event) {
                    this.rooms_to_change._quantity_tpl = event
                    this.validateRoomsAndPax()
                },
                validateQuantityRoomChildTpl: function (event) {
                    this.rooms_to_change._quantity_child_tpl = event
                    this.validateRoomsAndPax()
                },
                validateRoomsAndPax: function () {
                    let validate = this.getQuantityPersonsRooms()
                    if (validate) {
                        this.validate_rooms_and_pax = false
                    } else {
                        this.validate_rooms_and_pax = true
                    }
                },
                getQuantityPersonsRooms: function () {
                    let validate = true
                    let adults = parseInt(this.quantity_persons_change.adults_)
                    let child = parseInt(this.quantity_persons_change.child_with_bed_)

                    console.log("Adultos", adults)
                    console.log("Niños", child)

                    if(child === 0){
                        this.rooms_to_change._quantity_child_dbl = 0
                        this.rooms_to_change._quantity_child_tpl = 0
                    }
                    let quantityPersonsRoomsSGL = parseInt(this.rooms_to_change._quantity_sgl)
                    let quantityPersonsRoomsDBL = parseInt(this.rooms_to_change._quantity_dbl) * 2
                    let quantityPersonsRoomsChildDBL = parseInt(this.rooms_to_change._quantity_child_dbl)
                    let quantityPersonsRoomsTPL = parseInt(this.rooms_to_change._quantity_tpl) * 3
                    let quantityPersonsRoomsChildTPL = parseInt(this.rooms_to_change._quantity_child_tpl)

                    let total_accommodation_adults = parseInt(quantityPersonsRoomsSGL) + parseInt(quantityPersonsRoomsDBL - quantityPersonsRoomsChildDBL) + parseInt(quantityPersonsRoomsTPL - quantityPersonsRoomsChildTPL)
                    let total_accommodation_child = parseInt(quantityPersonsRoomsChildDBL) + parseInt(quantityPersonsRoomsChildTPL)

                    if (total_accommodation_adults !== adults) {
                        validate = false
                    }

                    if (child > 0 && total_accommodation_child !== child) {
                        validate = false
                    }

                    if (child > 0 && quantityPersonsRoomsChildDBL > 0 && quantityPersonsRoomsDBL === 0) {
                        validate = false
                    }

                    if (child > 0 && quantityPersonsRoomsChildTPL > 0 && quantityPersonsRoomsTPL === 0) {
                        validate = false
                    }

                    console.log("Validación..", validate)

                    return validate
                },
                getPackage () {
                    if (this.validateQuantityRooms()) {
                        this.loading_search_package = true
                        this.loading_hotel_search = true
                        this.loading_package_filter = true
                        this.gtm = false;

                        if (localStorage.getItem("user_type_id") == 4) {
                            this.gtm = true;
                        }

                        let data = {
                            client_id: this.client_id,
                            lang: localStorage.getItem('lang'),
                            date: moment(this.range.start).format('YYYY-MM-DD'),
                            quantity_persons: this.quantity_persons,
                            category: this.categorySelected,
                            rooms: this.rooms,
                            type_service: this.typeServiceSelected,
                            package_ids: this.package_ids,
                            gtm: this.gtm
                        }
                        axios.post(
                            baseExternalURL + 'services/client/packages',
                            data,
                        ).then((result) => {
                            this.loading_search_package = false
                            this.loading_hotel_search = false
                            this.loading_package_filter = false
                            if (result.data.data.length > 0) {
                                this.package_not_found = false
                                this.package = result.data.data[0]
                                this.commission_percentage = result.data.commission;
                                this.commission_status = result.data.commission_status;
                                this.chargeEventGtm(this.package);
                                this.itinerary_hotels = this.package.itinerary_hotels
                                this.getHotelsToChange(this.itinerary_hotels)
                            } else {

                                this.$toast.error('Lo sentimos, no se encontro disponibilidad', {
                                    position: 'top-right'
                                })
                                this.package_not_found = true
                            }
                        }).catch((e) => {
                            this.loading_search_package = false
                            this.loading_hotel_search = false
                        })
                    }
                },
                checkForSpecialDates(range, year) {
                    // Definir los rangos de fechas especiales
                    const specialDates = [
                        { name: "Semana Santa", start: "03-29", end: "04-05" },
                        { name: "Inti Raymi en Cusco", start: "06-23", end: "06-25" },
                        { name: "Fiestas Patrias", start: "07-27", end: "07-30" },
                         { name: "EXPOMINA Lima", start: "09-09", end: "09-11" },
                        { name: 'Navidad y Año Nuevo', start: '12-23', end: '01-02' }
                    ];
                    // Verificar si el rango seleccionado coincide con alguna fecha especial
                    for (const specialDate of specialDates) {
                        const startDate = moment(`${year}-${specialDate.start}`);
                        const endDate = moment(`${year}-${specialDate.end}`);
                        // Ajustar para el caso de Navidad y Año Nuevo que cruza el año
                        if (specialDate.start === '12-23' && specialDate.end === '01-02') {
                            endDate.add(1, 'year');
                        }

                        // Si hay superposición de fechas, retornar 1
                        if (range.start <= endDate && range.end >= startDate) {
                            return 1;
                        }
                    }

                    // Si no hay coincidencia, retornar 0
                    return 0;
                },
                chargeEventGtm: function(package) {
                    if (localStorage.getItem("user_type_id") == 4) {
                        dataLayer.push({
                            "event": "add_to_cart",
                            "currency": "USD",
                            "value": package.amounts.total_amount,
                            "package_id": package.id,
                            "package_code": package.code,
                            "package_name": package.descriptions.name_gtm.toLocaleUpperCase("en"),
                            "items": package.services_gtm
                        });
                    }
                },
                getPackagesDetails () {
                    if (this.client_id) {
                        this.loading_package = true
                        this.show_package = false

                        const year = moment(this.range.start).format('YYYY');
                        this.holidays = this.checkForSpecialDates(this.range, year);

                        this.gtm = false;

                        if (localStorage.getItem("user_type_id") == 4) {
                            this.gtm = true;
                        }

                        let data = {
                            client_id: this.client_id,
                            lang: localStorage.getItem('lang'),
                            date: moment(this.range.start).format('YYYY-MM-DD'),
                            quantity_persons: this.quantity_persons,
                            type_service: this.typeServiceSelected,
                            category: this.categorySelected,
                            rooms: this.rooms,
                            package_ids: this.package_ids,
                            limit: 1,
                            gtm: this.gtm
                        }
                        axios.post(
                            baseExternalURL + 'services/client/packages',
                            data,
                        ).then((result) => {
                            this.loading_package = false
                            if (result.data.data.length > 0) {
                                console.log("Package founded..")
                                this.show_package = true
                                this.package = result.data.data[0]
                                this.commission_percentage = result.data.commission;
                                this.commission_status = result.data.commission_status;
                                this.chargeEventGtm(this.package);
                                this.categorySelected = this.package.rate.category.id
                                this.typeServiceSelected = this.package.rate.type_service.id
                                this.days_package = this.package.nights + 1
                                if (this.itinerary_hotels.length === 0) {
                                    let itinerary_hotels = this.package.itinerary_hotels
                                    itinerary_hotels.forEach(function (item) {
                                        item.forEach(function (hotel) {
                                            hotel.hotel.forEach(function (hotel) {
                                                hotel.change = false
                                            })
                                        })
                                    })
                                    this.itinerary_hotels = itinerary_hotels
                                    this.getHotelsToChange(this.package.itinerary_hotels)
                                }
                            }
                        }).catch((e) => {
                            this.loading_package = false
                        })
                    }
                },
                getServices () {
                    if (this.client_id) {
                        this.loading_service = true
                        let data = {
                            lang: localStorage.getItem('lang'),
                            client_id: this.client_id,
                            date: moment(this.range.start).format('YYYY-MM-DD'),
                            quantity_persons: {
                                adults: 1,
                                child: 0,
                                age_childs: [
                                    {
                                        age: 1,
                                    },
                                ],
                            },
                            compensation: true, // Porqué se usa true?
                            limit: 50
                        }
                        axios.post(
                            baseExternalURL + 'services/available',
                            data,
                        ).then((result) => {
                            this.loading_service = false
                            if (result.data.data.services.length > 0) {
                                this.services = result.data.data.services
                            }
                        }).catch((e) => {
                            this.loading_service = false
                        })
                    }
                },
                getService (service) {
                    if (this.client_id) {
                        this.loading_service = true
                        let data = {
                            lang: localStorage.getItem('lang'),
                            client_id: this.client_id,
                            date: moment(this.range.start).format('YYYY-MM-DD'),
                            quantity_persons: {
                                adults: service.quantity_adult,
                                child: 0,
                                age_childs: [
                                    {
                                        age: 1,
                                    },
                                ],
                            },
                            compensation: true,
                            services_id: [service.id],
                            limit: 50
                        }
                        axios.post(
                            baseExternalURL + 'services/available',
                            data,
                        ).then((result) => {
                            this.loading_service = false
                            if (result.data.data.services.length > 0) {
                                let service_length = this.services.length
                                for (let i = 0; i < service_length; i++) {
                                    if (this.services[i].id === service.id) {
                                        this.services[i] = result.data.data.services[0]
                                    }
                                }
                            }
                        }).catch((e) => {
                            this.loading_service = false
                        })
                    }
                },
                addServiceToPackage (service) {
                    this.services_added_package.push({
                        id: service.id,
                        name: service.commercial_descriptions.name,
                        quantity_adults: service.quantity_adult,
                        description: service.commercial_descriptions.description,
                        rate_plan_id: service.rate.id,
                        token_search: service.token_search
                    })
                },
                checkIfAddedService (service) {
                    let check = false
                    this.services_added_package.forEach(function (item) {
                        if (item.id === service.id) {
                            check = true
                        }
                    })
                    return check
                },
                removeServiceToPackage (service) {
                    let _index = null
                    this.services_added_package.forEach(function (item, index) {
                        if (item.id === service.id) {
                            _index = index
                        }
                    })
                    if (_index != null) {
                        this.services_added_package.splice(_index, 1)
                    }
                },
                async getHotelsToChange(itinerary_hotels) {
                    if (itinerary_hotels.length > 0) {

                        let itinerary_results = [];
                        let rooms_add = [];

                        if(this.rooms.quantity_sgl>0){
                            rooms_add.push({
                                occupation: 1,
                                cant: this.rooms.quantity_sgl
                            });
                        }

                        if(this.rooms.quantity_dbl>0){
                            rooms_add.push({
                                occupation: 2,
                                cant: this.rooms.quantity_dbl
                            });
                        }

                        if(this.rooms.quantity_tpl>0){
                            rooms_add.push({
                                occupation: 3,
                                cant: this.rooms.quantity_tpl
                            });
                        }

                        // console.log("quantity_sgl", this.rooms.quantity_sgl);
                        // console.log("quantity_dbl", this.rooms.quantity_dbl);
                        // console.log("quantity_tpl", this.rooms.quantity_tpl);
                        // console.log("itinerary_hotels", itinerary_hotels);
                        // console.log("rooms_add", rooms_add);

                        this.loading_hotel_search = true
                        let promises = [];
                        for (let i = 0; i < itinerary_hotels.length; i++) {

                            let itinerary = itinerary_hotels[i][0]; // el primer valor me trae la informacion que necesito
                            // console.log(itinerary);
                            if (itinerary.hotel && itinerary.hotel.length > 0) {
                                promises.push(
                                    axios.post('services/hotels/available/quote',{   // buscamos los hoteles disponibles para poder agregar la habitacion
                                        "date_from": itinerary.hotel[0].date_in,
                                        "date_to": itinerary.hotel[0].date_out,
                                        "client_id": this.client_id,
                                        "quantity_rooms": 1,
                                        quantity_persons_rooms:[  // para que nos envien todas las habitaciones existentes
                                        ],
                                        "typeclass_id": this.categorySelected,
                                        "destiny": {
                                            "code": itinerary.hotel[0].country.iso + "," + itinerary.hotel[0].state.iso + "," +  itinerary.hotel[0].city.id,
                                            "label": itinerary.hotel[0].country.name + "," + itinerary.hotel[0].state.name + "," +  itinerary.hotel[0].city.name,
                                        },
                                        "lang": localStorage.getItem('lang'),
                                        "set_markup": 0,
                                        'zero_rates' : true,
                                        'preferential' : true
                                    })
                                )

                                itinerary_results.push({
                                    'date_from': itinerary.hotel[0].date_in,
                                    'date_to': itinerary.hotel[0].date_out,
                                    'hotels' : [],
                                    'hotels_on_request' : [],
                                    'hotel_code': itinerary.hotel[0].code
                                });
                            }
                        }

                        console.log("itinerary results ", itinerary_results);

                        if(promises.length>0){
                            let result_promises = await Promise.all(promises)
                            // console.log(result_promises);

                            result_promises.forEach((result, index) => {
                                // console.log(result);
                                let hotel_transform = [];
                                // Si tienes hoteles relacionados al hotel principal
                                let hotels_disponibles = result.data.data[0].city.hotels.length > 0 ? result.data.data[0].city.hotels : []
                                console.log("Hoteles disponibles", hotels_disponibles);
                                let hotel_code = itinerary_results[index].hotel_code;
                                console.log("index de hoteles disponibilidad",hotel_code, index);
                                hotels_disponibles = hotels_disponibles.sort((a, b) => {
                                    if(a.id === hotel_code) return -1;
                                    if(b.id === hotel_code) return 1;
                                    return 0
                                })
                                console.log("Hoteles disponibles formateado", hotels_disponibles);

                                hotels_disponibles.forEach((hotels) => {
                                    // console.log(hotels);
                                    let rooms_filtrados = [];
                                    // Verifica si tiene cuartos asociados al hotel iterado
                                    let rooms_disponibles = hotels.rooms.length>0 ? hotels.rooms : []
                                    rooms_add.forEach(room_new => {
                                        for(let p = 0; p < rooms_disponibles.length; p++) {
                                            let room = rooms_disponibles[p];
                                            // [1,2,3].includes(room.room_type_id) &&
                                            if(room_new.occupation == room.occupation) {  // filtramos solo habitaciones de tipo standard
                                                // Verifica si existen tarifas asociadas al cuarto
                                                for (let i = 0; i < room.rates.length; i++) {
                                                    let rate = room.rates[i];
                                                    if(rate.rates_plans_type_id == 2){  // filtramos solo las tarifas regulares
                                                        // Cuando rateProviderMethod es 2, es porque viene de hyperguest pull, usa entonces total_amount, si no usa total
                                                        let price_adult = rate.rateProviderMethod == 2 ? rate.total_amount : rate.total;
                                                        rooms_filtrados.push({
                                                            'room_id' : room.room_id,
                                                            'room_name' : room.name,
                                                            'occupation' : room.occupation,
                                                            'on_request' : rate.onRequest,
                                                            'price_adult' :  price_adult,
                                                            'rate_plan_id' : rate.ratePlanId,
                                                            'rate_plan_room_id' :  rate.rateId,
                                                            'rate_provider': rate.rateProvider,
                                                            'hyperguest_pull': rate.rateProviderMethod ? 1 : 0,
                                                            'hote_id' : hotels.id,
                                                            'hotel_name': hotels.name,
                                                            'date_from': result.data.data[0].city.search_parameters.date_from
                                                        })
                                                        p = i = 999; // salimos de los 2 niveles
                                                    }
                                                }
                                            }
                                        }
                                    });

                                    console.log("room add", rooms_add);
                                    console.log("room filtrados", rooms_filtrados);

                                    if(rooms_filtrados.length > 0 && rooms_add.length == rooms_filtrados.length){
                                        $on_request = 0;
                                        rooms_filtrados.forEach(element => {
                                            if(element.on_request == "0"){  // a nivel de hotel las que no son cofirmadas es porque on_request = 0
                                                $on_request = 1; // no confirmada
                                            }
                                        });

                                        hotel_transform.push({
                                            'hotel_id' : hotels.id,
                                            'hotel_name' : hotels.name,
                                            'on_request' : $on_request,
                                            'price' : Math.min(...rooms_filtrados.map(item => item.price_adult)),
                                            'rooms' : rooms_filtrados
                                        });
                                    }
                                });

                                let hotel_confirmadas = hotel_transform.filter(element => element.on_request == 0);
                                let hotel_onRequest = hotel_transform.filter(element => element.on_request == 1);

                                hotel_confirmadas.sort((a,b) => a.price - b.price);
                                hotel_onRequest.sort((a,b) => a.price - b.price);

                                itinerary_results[index].hotels = hotel_confirmadas
                                itinerary_results[index].hotels_on_request = hotel_onRequest
                            });

                            for (let i = 0; i < itinerary_results.length; i++) {
                                this.hotelsList[i] = itinerary_results[i].hotels.concat(itinerary_results[i].hotels_on_request);
                                this.hotelsList[i].change = false
                            }
                            this.loading_hotel_search = false

                            let results = [];
                            console.log("itinerary_hotels", this.itinerary_hotels);
                            this.itinerary_hotels.forEach((element, index) => {

                                if(element[0].hotel && element[0].hotel.length>0){

                                    let hotel_code = element[0].hotel[0].code
                                    let on_request = element[0].hotel[0].on_request

                                    if(!Array.isArray(results[hotel_code])){
                                        results[hotel_code] = []
                                    }
                                    // console.log("index", index);
                                    // console.log("hotelsList", this.hotelsList);
                                    results[hotel_code].push({
                                        'item' : index,
                                        'rq' : on_request,
                                        'hotels' : this.hotelsList[index].filter((el) =>el.on_request == 0)
                                    })
                                }
                            });

                            results.forEach((element, index) => {
                                if(element.length > 1) {
                                    element.forEach(item => {
                                        if(item.rq == 1){
                                            if (item.hotels.length > 0) {
                                                let hotel_finded = null;
                                                let hotel_first = null;
                                                let existRoomWithHyperguest = false;

                                                item.hotels.forEach((hotel, pos) => {
                                                    if (pos == 0) {
                                                        hotel_first = hotel
                                                    }
                                                    // Busco si alguno de los hoteles coincide con el main
                                                    hotel_finded = this.itinerary_hotels[item.item][0].hotel.find((el) =>el.code == hotel.hotel_id)
                                                    // verifico que sea diferente de null, es decir trajo resultados
                                                    if (hotel_finded) {
                                                        // verifico si algun cuarto del hotel que coincide tiene HYPERGUEST
                                                        hotel.rooms.forEach(room => {
                                                            if (room.rate_provider == 'HYPERGUEST') {
                                                                existRoomWithHyperguest = true;
                                                            }
                                                        });
                                                        // Si existe cuarto con HYPERGUEST
                                                        if (existRoomWithHyperguest) {
                                                            // console.log('Hotel con HYPERGUEST');
                                                            this.itinerary_hotels[item.item][0].hotel[0].replace = hotel.hotel_name
                                                            this.updateHotel(this.itinerary_hotels[item.item][0].hotel[0], hotel, item.item)
                                                        } else {
                                                            // console.log('Hotel sin HYPERGUEST, toma primer hotel');
                                                            this.itinerary_hotels[item.item][0].hotel[0].replace = hotel_first.hotel_name
                                                            this.updateHotel(this.itinerary_hotels[item.item][0].hotel[0], hotel_first, item.item)
                                                        }
                                                    } else {
                                                        // Verificación extra que determina si ya encontro hotel con HYPERGUEST y no sobreescribirlo
                                                        if (!existRoomWithHyperguest) {
                                                            // console.log('No hay hotel igual, toma primer hotel');
                                                            this.itinerary_hotels[item.item][0].hotel[0].replace = hotel_first.hotel_name
                                                            this.updateHotel(this.itinerary_hotels[item.item][0].hotel[0], hotel_first, item.item)
                                                        }
                                                    }
                                                });
                                            }
                                        }
                                    });
                                } else {
                                    // Toma primer hotel al tener pocos hoteles
                                    const currentElement = element[0];
                                    const currentItineraryIndex = currentElement.item;
                                    const currentHotel = this.itinerary_hotels[currentItineraryIndex][0].hotel[0];

                                    if (currentElement.rq == 1 && currentElement.hotels.length > 0) {
                                        // Buscar si existe el hotel actual en la lista de hoteles disponibles
                                        const hotelSearch = currentElement.hotels.find(
                                            (hotel) => hotel.hotel_id == currentHotel.code
                                        );

                                        // Si se encuentra el hotel significa que viene de hyperguest pull, usa entonces ese hotel, si no usar el primer hotel disponible
                                        const selectedHotel = hotelSearch
                                            ? hotelSearch
                                            : this.hotelsList[currentItineraryIndex][0];

                                        currentHotel.replace = currentElement.hotels[0].hotel_name;
                                        this.updateHotel(currentHotel, selectedHotel, currentItineraryIndex);
                                    }
                                }
                                return;

                                // if(element.length>1){
                                //     let rq = false
                                //     let cero = false
                                //     element.forEach(element2 => {
                                //         if(element2.rq == 1){
                                //             rq = true
                                //         }
                                //         if(element2.hotels.length == 0 ){
                                //             cero = true;
                                //         }
                                //     });

                                //     if(rq == true && cero == false){
                                //         let hotel_seleccionado = '';

                                //         for(let i=0; i<=element[0].hotels.length; i++){
                                //             // console.log('length :' + element[0].hotels.length);
                                //             // console.log('hotels in pos i : ');
                                //             // console.log(element[0].hotels[i]);
                                //             let hotel_matrix = []
                                //             hotel_seleccionado = element[0].hotels[i].hotel_id
                                //             // console.log(hotel_seleccionado);
                                //             hotel_matrix.push(hotel_seleccionado)
                                //             // console.log(hotel_matrix);

                                //             for(let j=1; j<=20; j++){
                                //                 // console.log('post j : ');
                                //                 // console.log(element[j]);
                                //                 if(!element[j])break;
                                //                 let hotel_find = element[j].hotels.find((el) =>el.hotel_id == hotel_seleccionado)
                                //                 if(hotel_find){
                                //                     hotel_matrix.push(hotel_seleccionado)
                                //                 }
                                //             }

                                //             if(hotel_matrix.length>=element.length){
                                //                 // console.log(hotel_matrix);
                                //                 break;
                                //             }
                                //         }

                                //         element.forEach(element2 => {
                                //             // console.log('entro a reemplazar');
                                //             // console.log(element2);
                                //             // console.log(hotel_seleccionado);
                                //             let hotel_find = element2.hotels.find((el) =>el.hotel_id == hotel_seleccionado)
                                //             // console.log(hotel_find);
                                //             this.itinerary_hotels[element2.item][0].hotel[0].replace = hotel_find.hotel_name
                                //             // console.log(element[0].item);
                                //             this.updateHotel(this.itinerary_hotels[element2.item][0].hotel[0], this.hotelsList[element2.item].find((el) =>el.hotel_id == hotel_seleccionado), element2.item);
                                //         });
                                //     }
                                // } else {
                                //     if(element[0].rq == 1 && element[0].hotels.length>0){
                                //         this.itinerary_hotels[element[0].item][0].hotel[0].replace = element[0].hotels[0].hotel_name
                                //         this.updateHotel(this.itinerary_hotels[element[0].item][0].hotel[0], this.hotelsList[element[0].item][0], element[0].item );
                                //     }
                                // }
                            });
                            // console.log(results);

                            // this.itinerary_hotels.forEach((element, index) => {

                            //     if(this.itinerary_hotels[index][0].hotel && this.itinerary_hotels[index][0].hotel.length>0 && this.itinerary_hotels[index][0].hotel[0].on_request === 1){
                            //         if(this.hotelsList[index] && this.hotelsList[index].length>0 && this.hotelsList[index][0].on_request === 0 ){
                            //            this.updateHotel(this.itinerary_hotels[index][0].hotel[0], this.hotelsList[index][0] );
                            //         }
                            //     }
                            // });
                        }
                        else
                        {
                            this.loading_hotel_search = false
                        }
                    }
                },

                getHotelsToChange_bk: function (itinerary_hotels) {
                    if (itinerary_hotels.length > 0) {
                        let with_bed_min = this.package.children_age_allowed.with_bed.min_age
                        let with_bed_max = this.package.children_age_allowed.with_bed.max_age
                        let without_bed_min = this.package.children_age_allowed.without_bed.min_age
                        let without_bed_max = this.package.children_age_allowed.without_bed.max_age
                        let ranges_hotels = []
                        for (let i = 0; i < itinerary_hotels.length; i++) {
                            for (let h = 0; h < itinerary_hotels[i].length; h++) {
                                if (itinerary_hotels[i][h].hotel && itinerary_hotels[i][h].hotel.length > 0) {
                                    ranges_hotels.push({
                                        date_from: itinerary_hotels[i][h].hotel[0].date_in,
                                        date_to: itinerary_hotels[i][h].hotel[0].date_out,
                                        state_id: itinerary_hotels[i][h].hotel[0].state.id,
                                        city_id: itinerary_hotels[i][h].hotel[0].city.id,
                                    })
                                }
                                if (h === 0) {
                                    break
                                }
                            }
                        }
                        this.loading_hotel_search = true
                        let data_hotels = {
                            lang: localStorage.getItem('lang'),
                            client_id: this.client_id,
                            quantity_persons: {
                                adults: this.quantity_persons.adults,
                                child_with_bed: {
                                    min_age: 0,
                                    max_age: 0,
                                    quantity: 0,
                                },
                                child_without_bed: {
                                    min_age: 0,
                                    max_age: 0,
                                    quantity: 0,
                                }
                            },
                            room_occupation: {
                                sgl: this.rooms.quantity_sgl,
                                dbl: this.rooms.quantity_dbl,
                                tpl: this.rooms.quantity_tpl
                            },
                            dates_ranges: ranges_hotels,
                            category_id: this.categorySelected
                        }
                        data_hotels.quantity_persons.child_with_bed.min_age = with_bed_min
                        data_hotels.quantity_persons.child_with_bed.max_age = with_bed_max
                        data_hotels.quantity_persons.child_with_bed.quantity = this.quantity_persons.child_with_bed
                        data_hotels.quantity_persons.child_without_bed.min_age = without_bed_min
                        data_hotels.quantity_persons.child_without_bed.max_age = without_bed_max
                        data_hotels.quantity_persons.child_without_bed.quantity = this.quantity_persons.child_without_bed
                        axios.post(
                            baseExternalURL + 'services/hotels/list/packages',
                            data_hotels,
                        ).then((result) => {
                            if (result.data.length > 0) {
                                let hotels = result.data
                                for (let i = 0; i < hotels.length; i++) {
                                    if (hotels[i].hotels.length > 0) {
                                        this.hotelsList[i] = hotels[i].hotels
                                        this.hotelsList[i].change = false
                                    } else if (hotels[i].hotels_on_request.length > 0) {
                                        this.hotelsList[i] = hotels[i].hotels_on_request
                                        this.hotelsList[i].change = false
                                    }
                                }
                            }
                            this.loading_hotel_search = false

                        }).catch((e) => {
                            this.loading_hotel_search = false
                        })
                    }
                },
                onDayClick (day) {
                    this.daySelected = day.date
                    this.$nextTick(() => {
                        this.$refs.cambiarfecha.click()
                    })
                },
                changeAdult: function () {
                    this.validateRoomsAndPax()
                },
                changeInput: function () {
                    if (parseInt(this.quantity_persons.child_with_bed) === 0) {
                        this.rooms.quantity_child_dbl = 0
                        this.rooms.quantity_child_tpl = 0
                    }
                    this.validateRoomsAndPax()
                },
                cambiar () {
                    this.range_to_change = {
                        start: this.daySelected,
                        end: new Date(moment(this.daySelected).add(this.days_package, 'days').format('YYYY-MM-DD'))
                    }
                },
                changeDateFixed: async function (daySelected) {
                    this.range_to_change = {
                        start: new Date(moment(daySelected)),
                        end: new Date(moment(daySelected).add(this.days_package, 'days').format('YYYY-MM-DD'))
                    }
                    let date = moment(daySelected)
                    let month = date.format('M');
                    let year = date.format('YYYY');
                    const calendar = this.$refs.calendarfixed
                    await calendar.move({ month: parseInt(month), year: parseInt(year) })
                },
                closeModalDates () {
                    this.modalCalender = false
                    this.range_to_change = this.range
                },
                saveModalDates () {
                    this.modalCalender = false
                    this.range = this.range_to_change
                    this.getPackage()
                },
                closeModalPax () {
                    this.modalPax = false
                    this.quantity_persons_change = {
                        adults_: this.quantity_persons.adults,
                        child_with_bed_: this.quantity_persons.child_with_bed,
                        child_without_bed_: this.quantity_persons.child_without_bed,
                        age_child: [
                            {
                                age: 1,
                            },
                        ],
                    }
                },
                saveModalPax () {
                    this.modalPax = false
                    this.quantity_persons = {
                        adults: this.quantity_persons_change.adults_,
                        child_with_bed: this.quantity_persons_change.child_with_bed_,
                        child_without_bed: this.quantity_persons_change.child_without_bed_,
                        age_child: [
                            {
                                age: 1,
                            },
                        ],
                    }

                    this.validateRoomsAndPax()
                    if (this.validate_rooms_and_pax) {
                        this.modalRoom = true
                    }
                },
                closeModalRooms () {
                    if (this.validateQuantityRooms()) {
                        this.modalRoom = false
                        this.rooms_to_change = {
                            _quantity_sgl: this.rooms.quantity_sgl,
                            _quantity_dbl: this.rooms.quantity_dbl,
                            _quantity_child_dbl: this.rooms.quantity_child_dbl,
                            _quantity_tpl: this.rooms.quantity_tpl,
                            _quantity_child_tpl: this.rooms.quantity_child_tpl,
                        }
                    }
                },
                saveModalRooms () {
                    if (this.validateQuantityRooms()) {
                        this.modalRoom = false
                        this.rooms = {
                            quantity_sgl: this.rooms_to_change._quantity_sgl,
                            quantity_dbl: this.rooms_to_change._quantity_dbl,
                            quantity_child_dbl: this.rooms_to_change._quantity_child_dbl,
                            quantity_tpl: this.rooms_to_change._quantity_tpl,
                            quantity_child_tpl: this.rooms_to_change._quantity_child_tpl
                        }
                        this.getPackage()
                    }
                },
                closeModalCategory () {
                    this.modalCategory = false
                    this.categorySelected_change = this.categorySelected
                },
                saveModalCategory () {
                    this.modalCategory = false
                    this.categorySelected = this.categorySelected_change
                    this.getPackage()
                },
                closeModalTypeService () {
                    this.modalTypeServices = false
                    this.typeServiceSelected_change = this.typeServiceSelected
                },
                closeModalFileNumber () {
                    this.modalFileNumber = false
                },
                saveModalTypeService () {
                    this.modalTypeServices = false
                    this.typeServiceSelected = this.typeServiceSelected_change
                    this.getPackage()
                },
                validateQuantityRooms () {
                    let validate = this.getQuantityPersonsRooms()
                    if (validate) {
                        this.validate_rooms_and_pax = false
                    } else {
                        this.validate_rooms_and_pax = true
                    }
                    console.log("Validar Cantidad de Rooms:", validate)
                    return validate
                },
                goToPassengers () {
                    if(this.isDisabledReservation)
                    {
                        return false
                    }

                    let validate = this.getQuantityPersonsRooms()
                    if (validate) {
                        this.validate_rooms_and_pax = false
                        this.buildDataReservation()
                        /* if(localStorage.getItem('user_type_id') == '4'){
                            this.buildDataReservation()
                        }else {
                            this.modalFileNumber = true
                        } */
                    } else {
                        this.$toast.error(this.translations.validate.quantity_rooms, {
                            position: 'top-right'
                        })
                        this.modalRoom = true
                        this.validate_rooms_and_pax = true
                    }
                },
                continueWithoutFileNumber() {
                    this.modalFileNumber = false
                    this.fileNumber = '' // Limpiar el número si elige continuar sin él
                    this.buildDataReservation()
                },

                saveFileNumberAndContinue() {
                    this.modalFileNumber = false
                    this.buildDataReservation()
                },

                buildDataReservation () {
                    this.getHotelChanged()
                    let data = {
                        package_name: this.package.descriptions.name,
                        package_name_gtm: this.package.descriptions.name_gtm,
                        id: this.package.id,
                        code: this.package.code,
                        total_amount: this.package.amounts.total_amount,
                        client_id: this.client_id,
                        date: moment(this.range.start).format('YYYY-MM-DD'),
                        rooms: this.rooms,
                        hotels_changed: this.hotels_changed,
                        lang: this.lang,
                        quantity_persons: this.quantity_persons,
                        passengers: [],
                        flights: this.package.flights,
                        services_added_package: this.services_added_package,
                        reference: '',
                        entity: 'Package',
                        object_id: this.package.id,
                        type_class_id: this.categorySelected,
                        service_type_id: this.typeServiceSelected,
                        token_search: this.package.token_search,
                        services_gtm: this.package.services_gtm,
                        file_number: this.fileNumber
                    }
                    localStorage.setItem('package_reservation', JSON.stringify(data))
                    window.location = baseURL + 'client/passengers'
                },
                getHotelChanged () {
                    for (let i = 0; i < this.itinerary_hotels.length; i++) {
                        if (this.itinerary_hotels[i][0].hotel.length > 0 && this.itinerary_hotels[i][0].hotel[0].change) {
                            this.hotels_changed.push({
                                'hotel_id': this.itinerary_hotels[i][0].hotel[0].id,
                                'date_in': this.itinerary_hotels[i][0].hotel[0].date_in,
                                'date_out': this.itinerary_hotels[i][0].hotel[0].date_out,
                                'package_service_id': this.itinerary_hotels[i][0].hotel[0].package_service_id,
                                'rooms': this.itinerary_hotels[i][0].hotel[0].rooms,
                            })
                        }
                    }
                },
                updateHotel: function (hotel_package, hotel, index = 0) {
                    // console.log(hotel_package, hotel, index = 0);
                    hotel_package.name = hotel.hotel_name
                    hotel_package.on_request = hotel.on_request
                    // this.itinerary_hotels[index][0].hotel[0].name = hotel.hotel_name;
                    // this.itinerary_hotels[index][0].hotel[0].on_request = hotel.on_request;
                    // return false;

                    let rooms_sgl = []
                    let rooms_dbl = []
                    let rooms_tpl = []
                    let rooms = []
                    hotel.rooms.forEach(function (item) {
                        if (item.occupation === 1 && item.on_request === 0 && rooms_sgl.length === 0) {
                            rooms_sgl.push(item)
                            rooms.push(item)
                        } else if (item.occupation === 1 && rooms_sgl.length === 0) {
                            rooms_sgl.push(item)
                            rooms.push(item)
                        }
                        if (item.occupation === 2 && item.on_request === 0 && rooms_dbl.length === 0) {
                            rooms_dbl.push(item)
                            rooms.push(item)
                        } else if (item.occupation === 2 && rooms_dbl.length === 0) {
                            rooms_dbl.push(item)
                            rooms.push(item)
                        }
                        if (item.occupation === 3 && item.on_request === 0 && rooms_tpl.length === 0) {
                            rooms_tpl.push(item)
                            rooms.push(item)
                        } else if (item.occupation === 3 && rooms_tpl.length === 0) {
                            rooms_tpl.push(item)
                            rooms.push(item)
                        }
                    })
                    hotel_package.id = hotel.hotel_id
                    hotel_package.rooms = rooms
                    hotel_package.change = true
                    // this.itinerary_hotels[index][0].hotel[0].id = hotel.hotel_id;
                    // this.itinerary_hotels[index][0].hotel[0].rooms = rooms;
                    // this.itinerary_hotels[index][0].hotel[0].change = true;
                },
                modalAlertToggle () {
                    $('#modalAlertaCotizacion').modal()
                },
                edit () {
                    // console.log("aqui cotizacion");
                    let urlquoteA3 = window.a3BaseQuoteServerURL
                    // if(this.user_type_id == '4'){
                    //     urlquoteA3 = window.a3BaseQuoteServerURL
                    // }else{
                    //     urlquoteA3 = baseExternalURL
                    // }

                    this.loading2 = true
                    axios.get(urlquoteA3 + 'api/quote/existByUserStatus/2')
                        .then((result) => {
                            if (result.data.success) {
                                this.loading2 = false
                                this.modalAlertToggle()
                            } else {
                                this.putQuote()
                            }
                        }).catch((e) => {
                            this.loading2 = false
                        })
                },
                goToA3(){
                    document.location.href = window.a3BaseUrl + 'quotes'
                },
                async putQuote() {

                    this.loading = true
                    let urlquoteA3 = window.a3BaseQuoteServerURL
                    let package_token = '';

                    const { data } = await axios.post(baseExternalURL + 'services/client/cache-package-select',{package_token: this.package.token_search})
                    if(data.success){
                        package_token = data.packages_selected
                    }else{
                        this.$toast.error('Error: ' + data.message, {
                            position: 'top-right'
                        })
                        this.loading = false
                        return false;
                    }

                    this.getHotelChanged()
                    const request_data = {
                            name: this.package.descriptions.name,
                            status: 2,
                            date_in: moment(this.range.start).format('YYYY-MM-DD'),
                            client_id: this.client_id,
                            quantity_persons: this.quantity_persons,
                            type_class_id: this.categorySelected,
                            type_service: this.typeServiceSelected,
                            hotels_changed: this.hotels_changed,
                            rooms: this.rooms,
                            package_token: package_token
                    };

                    console.log("data a enviar", request_data);
                    axios.post(urlquoteA3 + 'api/package/copy/quote',
                        request_data
                    ).then(async (result) => {
                        if (result.data.success) {

                            let quote_id =  result.data.quote_id;
                            let resulRateHpPull = await this.getRateHpPull(quote_id, this.client_id);
                            if(resulRateHpPull.length >0 )
                            {
                                const promisesAvailability = this.fetchHotelAvailability(resulRateHpPull);

                                if (promisesAvailability.length > 0) {
                                    const promisesResult = await Promise.all(promisesAvailability);

                                    const promisesServiceHotel = await this.updateRateHyperguestPull(quote_id, promisesResult);

                                }
                            }

                            localStorage.setItem('request_pakage', 1)

                            if(localStorage.getItem('parameters_ota_generic') != null){
                                let parameters_ota_generic = JSON.parse(localStorage.getItem('parameters_ota_generic'))
                                parameters_ota_generic.quote_id = result.data.quote_id

                                localStorage.setItem('parameters_ota_generic', JSON.stringify(parameters_ota_generic))
                            }
                            this.$toast.success('Cotización en modo edición', {
                                // override the global option
                                position: 'top-right'
                            })
                            this.goToQuotesFront()


                        } else {
                            this.$toast.error('Error Interno', {
                                position: 'top-right'
                            })
                        }
                        this.loading = false
                    })
                    // .catch((e) => {
                    //     this.$toast.error('Error: ' + e, {
                    //         position: 'top-right'
                    //     })
                    //     this.loading = false
                    // })
                },
                goToQuotesFront () {

                    if(this.user_type_id == '4'){
                        window.location.href = window.a3BaseUrl + 'quotes'
                    }else{
                        window.location.href = '/packages/cotizacion'
                    }
                },
                changeChildrenRoom (index_child, room) {
                    this.childrenRoomSelected[index_child] = room
                    let children_room_dbl = 0
                    let children_room_tpl = 0
                    for (let i = 0; i < this.childrenRoomSelected.length; i++) {
                        if (this.childrenRoomSelected[i] === 'double') {
                            children_room_dbl++
                        }
                        if (this.childrenRoomSelected[i] === 'triple') {
                            children_room_tpl++
                        }
                    }
                    this.rooms_to_change._quantity_child_dbl = children_room_dbl
                    this.rooms_to_change._quantity_child_tpl = children_room_tpl
                    this.validateRoomsAndPax()
                },
                async getRateHpPull (quote_id, client_id) {
                    let urlquoteA3 = window.a3BaseQuoteServerURL

                    const response = await axios.get(urlquoteA3 + 'api/quote/' + quote_id + '/get-rate-hyperguest-pull', {
                        params: { client_id }
                    });

                    return response.data.result ?? [];
                },
                fetchHotelAvailability(params){
                    const promises = [];

                    for (let i=0; i<params.length; i++) {

                        let results = params[i];
                        promises.push(new Promise((resolve) => {
                            this.getHotelsAvailability(results.params).then((response) => {
                                resolve({
                                    hotel_id: results.hotel_id,
                                    quote_service_id: results.quote_service_id,
                                    passengers_quantity: results.passengers_quantity,
                                    service_rate_plan_room: results.service_rate_plan_room,
                                    availability: response.data[0].city.hotels
                                })
                            })
                        }))
                    }

                    return promises
                },
                async getHotelsAvailability(request){
                    const { data } =  await axios.post('services/hotels/available/quote', request)
                    return data

                },
                async updateRateHyperguestPull(quote_id, result){
                    const response = [];
                    for (const { hotel_id, quote_service_id, passengers_quantity, service_rate_plan_room , availability } of result) {

                        const roomsAvailable = availability.length > 0 ? availability[0].rooms : [];

                        let shouldBreak = false;
                        for (const room of roomsAvailable) {         
                            // console.log(quote_service_id + ' - ' +  hotel_id  + ' - ' + service_rate_plan_room.room_id + ' == ' +  room.room_id  )
                            if (service_rate_plan_room.room_id == room.room_id) {

                                for (const rate of room.rates) {
                                    // console.log(quote_service_id + ' - ' +  hotel_id  + ' - ' + service_rate_plan_room.room_id + ' == ' +  room.room_id + ' ' +  service_rate_plan_room.rate_plan_id + ' == ' + rate.ratePlanId)
                                    if (service_rate_plan_room.rate_plan_id == rate.ratePlanId) {

                                        let amount_days = rate.rate[0].amount_days;

                                        for (const amount of amount_days)
                                        {
                                            const base  = Number(amount.total_amount_base ?? 0);
                                            const total = Number(amount.total_amount ?? 0);
                                            const pax   = Number(passengers_quantity ?? 0);

                                            response.push({
                                                quote_service_id,
                                                date_service: amount.date,
                                                price_per_night_without_markup: base,
                                                price_per_night: total,
                                                price_adult_without_markup: base / pax,
                                                price_adult: total / pax,
                                                price_child_without_markup: amount.total_child_base ?? 0,
                                                price_child: amount.total_child ?? 0,
                                                price_teenagers_without_markup: 0,
                                                price_teenagers: 0
                                            });
                                        }

                                    }
                                }
                            }

                        }

                    }

                    let urlquoteA3 = window.a3BaseQuoteServerURL
                    let results = await axios.post(urlquoteA3 + 'api/quote/' + quote_id + '/update-rate-hyperguest-pull', {
                        data: response
                    })

                },
                getPriceAmount(price) {
                    // Eliminar comas y convertir a float
                    const numericPrice = parseFloat(String(price).replace(/,/g, '')) || 0;
                    // Solo calcula si el status es 1 y hay porcentaje
                    if (this.commission_status === 1 && this.commission_percentage > 0) {
                        const commissionRate = this.commission_percentage / 100;
                        const withCommission = numericPrice * (1 + commissionRate);
                        // Redondear siempre hacia arriba al entero más cercano
                        return Math.ceil(withCommission);
                    }

                    return price;
                }
            },
            filters: {
                formattedDateProgram: function (date) {
                    if (date != null) {
                        return moment(date).format('MMM D')
                    } else {
                        return ''
                    }
                },
                formattedDateCancellation: function (date) {
                    if (date != null) {
                        return moment(date).format('D MMMM')
                    } else {
                        return ''
                    }
                },
                formattedDate: function (date) {
                    return moment(date).format('MMM D, YYYY')
                },
                formattedDate2: function (date) {
                    return moment(date).format('D MMM YYYY')
                },
                formatDayArray (arr) {
                    var outStr = ''
                    if (arr.length === 1) {
                        outStr = arr[0]
                    } else if (arr.length === 2) {
                        //joins all with "and" but no commas
                        //example: "bob and sam"
                        outStr = arr.join(' and ')
                    } else if (arr.length > 2) {
                        //joins all with commas, but last one gets ", and" (oxford comma!)
                        //example: "bob, joe, and sam"
                        outStr = arr.slice(0, -1).join(', ') + ', y ' + arr.slice(-1)
                    }
                    return outStr
                },
                formatDestinations (arr) {
                    let array = []
                    arr.forEach(function (state) {
                        array.push(state.state)
                    })

                    var outStr = ''
                    if (array.length === 1) {
                        outStr = array[0]
                    } else if (array.length === 2) {
                        //joins all with "and" but no commas
                        //example: "bob and sam"
                        outStr = array.join(' & ')
                    } else if (array.length > 2) {
                        //joins all with commas, but last one gets ", and" (oxford comma!)
                        //example: "bob, joe, and sam"
                        outStr = array.slice(0, -1).join(', ') + ' & ' + array.slice(-1)
                    }
                    return outStr
                },
            }
        })
    </script>

    <style>
        /* Estilos para el botón de cerrar del modal */
        .btn-close-modal {
            position: absolute;
            top: 10px;
            right: 15px;
            background: rgba(0, 0, 0, 0.1);
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .btn-close-modal:hover {
            background: rgba(0, 0, 0, 0.2);
            transform: scale(1.1);
        }

        .btn-close-modal i {
            color: #666;
            font-size: 16px;
            margin-right: 5px;
        }

        .btn-close-modal span {
            color: #666;
            font-size: 12px;
            font-weight: 500;
        }

        .btn-close-modal:hover i,
        .btn-close-modal:hover span {
            color: white;
        }

        @media screen and (max-width: 1200px) {
            .contenedor-quote{
                max-width: none !important;
            }
        }

        @media screen and (max-width: 1140px) {
            .contenedor-quote{
                max-width: none !important;
                width: max-content;
                margin-left: 40px;
            }
        }

        @media screen and (max-width: 992px) {
            .contenedor-quote{
                max-width: none !important;
                width: max-content;
                margin-left: 40px;
            }

        }

        @media screen and (max-width: 768px) {
            .contenedor-quote{
                max-width: none !important;
                justify-content:center !important;
                margin-left: 40px;
            }
        }

        @media screen and (max-width: 576px) {
            .contenedor-quote{
                max-width: none !important;
                margin-left: 40px;
                justify-content:center !important;
            }
        }

    </style>
@endsection
