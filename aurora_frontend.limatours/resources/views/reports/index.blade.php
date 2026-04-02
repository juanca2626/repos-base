@extends('layouts.app')
@section('content')
<section class="page-reportes" v-if="is_reservation_modification === false">
    <loading-component v-show="blockPage"></loading-component>
    <div class="container">
        <div class="motor-busqueda">
            <h2>{{trans('reservations.label.reports')}}</h2>
            <div class="form reportes">
                <div class="form-row" style="align-items: end;">
                    <div class="col">
                        <label>{{trans('reservations.label.num_file')}}</label>
                        <input class="form-control" v-model="form.file_code" placeholder="Escriba N° file">
                    </div>
                    <div class="col">
                        <div class="form-group" style="width: 100%;">
                            <label>{{trans('reservations.label.date_range')}}</label>
                            <div class="form-control fecha">
                                <date-range-picker
                                    :locale-data="locale_data"
                                    :timePicker24Hour="false"
                                    :showWeekNumbers="false"
                                    :ranges="false"
                                    :auto-apply="true"
                                    v-model="form.date_range">
                                </date-range-picker>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto text-right">
                        <button class="btn btn-cancel"
                            @click="clearParameters">
                            <i class="fas fa-eraser"></i> {{trans('global.label.clear_filters')}}
                        </button>
                    </div>
                    <div class="col-auto text-right">
                        <button class="btn btn-primary"
                            @click="buscarReservas()"><i class="fa fa-search"></i> {{trans('global.label.search')}}</button>
                    </div>

                </div>
            </div>
        </div>
        <hr>
        <div class="row col-lg-12 title-result">
            <h3 class="color-title">{{trans('reservations.label.your_results')}}</h3>
        </div>
        <div class="d-flex justify-content-between align-items-center" style="float: right">
            {{-- <div class="tabsNav">--}}
            {{-- <ul class="clearfix">--}}
            {{-- <li><a class="active">{{trans('reservations.label.hotels')}}</a></li>--}}
            {{-- <li><a>{{trans('reservations.label.services')}}</a></li>--}}
            {{-- <li><a>{{trans('reservations.label.programs')}}</a></li>--}}
            {{-- </ul>--}}
            {{-- </div>--}}
            <div class="clasificacion d-flex justify-content-end">
                <div class="clasi_sinPenalidad"><i class="fa fa-circle"></i> {{trans('reservations.label.days_left_without_penalty')}}</div>
                <div class="clasi_conPenalidad"><i class="fa fa-circle"></i> {{trans('reservations.label.with_penalty')}}</div>
                <div class="clasi_sinConfirmar"><i class="fa fa-circle"></i> {{trans('reservations.label.unconfirmed')}}</div>
                <div class="clasi_cancelada"><i class="fa fa-circle"></i> {{trans('reservations.label.cancelled')}}</div>
            </div>
        </div>
        <div class="results">
            <div class="results-hotels" v-if="isEmpty(reservationsHotels) > 0">
                @include('reports.hotels')
            </div>
        </div>
    </div>
</section>

<section class="page-hotel page-reportes" v-else-if="is_reservation_modification === true">
    <loading-component v-show="blockPage"></loading-component>
    <div class="container">
        <div class="motor-busqueda">
            <div class="form-btn-action">
                <div class="row">
                    <div class="col-sm-9 align-self-center">
                        <h2>{{trans('reservations.label.editing_reservation')}}:</h2>
                    </div>
                    <br>
                    <div>{{trans('reservations.label.reservations_number')}}: @{{ edit_reservation.id }}</div>
                    <br>
                    <div>{{trans('reservations.label.hotel')}}: @{{ edit_reservation.hotel_name }}</div>
                    <br>
                    <div>{{trans('reservations.label.entry')}}:
                        @{{ dateFormatLatin(edit_reservation.check_in) }}
                        <span>@{{ edit_reservation.check_in_time }}</span>
                    </div>
                    <br>
                    <div>{{trans('reservations.label.exit')}}:
                        @{{ dateFormatLatin(edit_reservation.check_out) }}
                        <span>@{{ edit_reservation.check_out_time }}</span>
                    </div>
                    <br>
                    <div class="col-sm-3">
                        <button class="btn btn-primary"
                            @click="buscarReservas()">{{trans('reservations.label.cancel_modification')}}</button>
                    </div>
                </div>
            </div>
        </div>
        <hr>
        <div class="row col-lg-12">
            <div class="tabsNav">
                <ul class="clearfix">
                    <li><a :class="edit_reservation_action === 1 ?'active':''"
                            @click="setEditAction(1)">{{trans('reservations.label.remove_rooms')}}</a></li>
                    <li><a :class="edit_reservation_action === 2 ?'active':''"
                            @click="setEditAction(2)">{{trans('reservations.label.add_rooms')}}</a></li>
                    <li><a :class="edit_reservation_action === 3 ?'active':''"
                            @click="setEditAction(3)">{{trans('reservations.label.change_dates')}}</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="container" v-if="edit_reservation_action === 1">
        <div class="results">
            <div class="results-hotels">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{trans('reservations.label.room')}}</th>
                            <th>{{trans('reservations.label.rate_plan')}}</th>
                            <th>{{trans('reservations.label.channel')}}</th>
                            <th>{{trans('reservations.label.adults')}}</th>
                            <th>{{trans('reservations.label.children')}}</th>
                            <th>{{trans('reservations.label.price')}}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(resRoom,index_room) in edit_reservation.reservations_hotel_rooms" :key="index_room">
                            <td class="clasi_resCode">
                                <span v-if="resRoom.status === 0" class="clasi clasi_cancelada"></span>
                                <span v-else-if="resRoom.status === 3" class="clasi clasi_sinConfirmar"></span>
                                <span v-else-if="resRoom.status === 1 || resRoom.status === 2"
                                    class="clasi clasi_sinPenalidad"></span>
                                @{{ resRoom.id }}
                            </td>
                            <td class="clasi_resRoomName">
                                @{{ resRoom.room_name }} - @{{ resRoom.room_code }}
                            </td>
                            <td class="clasi_resRatePlanName">
                                @{{ resRoom.rate_plan_name }} - @{{ resRoom.rate_plan_code }}
                            </td>
                            <td class="clasi_resChannelName">
                                @{{ resRoom.channel_code }}
                            </td>
                            <td class="clasi_resAdultsNum">
                                @{{ resRoom.adult_num + resRoom.extra_num }}
                            </td>
                            <td class="clasi_resChildsNum">
                                @{{ resRoom.child_num }}
                            </td>
                            <td class="clasi_resRoomPrince">
                                @{{ resRoom.total_amount + resRoom.total_tax_and_services_amount }}
                            </td>
                            <td class="clasi_resRoomPrince">
                                <a class="clasi_botonCancelar"
                                    @click="cancelReservationRoom(edit_reservation.reservation.file_code, edit_reservation.id, resRoom.id)"
                                    href="javascript:void(0)">{{trans('reservations.label.delete')}}</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="container" v-else-if="edit_reservation_action === 2">
        <div class="motor-busqueda">
            <div class="form reportes">
                <div
                    v-for="(quantity_person_room, index_quantity_person_room) in edit_reservation_agregar_form.quantity_persons_rooms"
                    class="container_quantity_persons_rooms_selects quantity-persons-rooms">
                    <h4>
                        <span style="display: block; height: 26px; width: 100%;"
                            v-if="index_quantity_person_room === 0"></span>
                        {{ trans('hotel.label.room') }}
                        @{{ index_quantity_person_room + 1}}
                    </h4>
                    <div class="form-group">
                        <label v-if="index_quantity_person_room === 0">{{ trans('hotel.label.adults') }}</label>
                        <select v-model="quantity_person_room.adults"
                            class="form-control"
                            @change="calculateNumPersonsPerRooms()">
                            <option v-for="num_adults in 10" :value="num_adults">@{{ num_adults }}
                            </option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label v-if="index_quantity_person_room === 0">{{ trans('hotel.label.child') }}</label>
                        <select v-model="quantity_person_room.child"
                            @change="changeQuantityChild(quantity_person_room.child,index_quantity_person_room)"
                            class="form-control">
                            <option v-for="num_child in 6" :value="num_child - 1">@{{ num_child - 1 }}</option>
                        </select>
                    </div>
                    <div class="form-group" v-if="quantity_person_room.child >=1">
                        <label>{{ trans('hotel.label.age') }}</label>
                        <select v-for="(age_child_slot,index_age_child) in quantity_person_room.child"
                            v-model="quantity_person_room.ages_child[index_age_child].age" class="form-control">
                            <option v-for="age_child in 18" :value="age_child -1">@{{ age_child -1}}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-btn-action">
                <div class="row">
                    <div class="col-sm-9 align-self-center"></div>
                    <div class="col-sm-3 text-right">
                        <button class="btn btn-primary"
                            @click="buscarHabitacionAgregar()">{{trans('global.label.search')}} <i class="fa fa-search"></i></button>
                    </div>
                </div>
            </div>
        </div>
        <hr>
        <div class="container-heigth">
            <template v-if="edit_reservation_agregar_rooms_avail.hotels.length > 0">
                <div id="result-hotels" class="row-fluid col-lg-12 result-hotels">
                    <div class="row col-lg-12"
                        v-for="(hotel,index_hotel) in edit_reservation_agregar_rooms_avail.hotels">
                        <div class="row-fluid col-lg-12 list-rooms" :id="'container_rooms_hotel'+index_hotel">
                            <div class="content-list">
                                <div class="row col-lg-12">
                                    <div :id="'wrapper-personality-'+index_hotel"
                                        class="row col-lg-12 wrapper-personality" style="display:block">
                                        <div class="row col-lg-12" v-for="(room,index_room) in  hotel.rooms">
                                            <div class="row col-lg-12">
                                                <div class="col-lg-3">
                                                    <ul class="slides noPa"
                                                        style="margin: 0;width: 240px;height: 190px;"
                                                        v-if="room.gallery.length > 0">
                                                        <template v-for="(gallery,index_image) in room.gallery">
                                                            <input class="inputnoPa" type="radio"
                                                                :name="'radio-btn_'+index_hotel+'_'+index_room"
                                                                :id="'img-'+index_image+'_'+index_hotel+'_'+index_room"
                                                                :checked="index_image===0" />
                                                            <li class="slide-container">
                                                                <div class="slide"
                                                                    style="width: 240px;height: 130px;">
                                                                    <img :src="baseExternalURL+'images/'+gallery"
                                                                        style="border-radius: 2px;" />
                                                                </div>
                                                                <div class="nav">
                                                                    <label
                                                                        :for="'img-'+(room.gallery.length -1)+'_'+index_hotel+'_'+index_room"
                                                                        class="prev" v-if="index_image ===0"
                                                                        style="width:30%;height: 100%;line-height: normal; padding: 17% 0 0 0">&#x2039;</label>
                                                                    <label
                                                                        :for="'img-'+(index_image - 1)+'_'+index_hotel+'_'+index_room"
                                                                        class="prev"
                                                                        style="width:30%;height: 100%;line-height: normal; padding: 17% 0 0 0"
                                                                        v-else>&#x2039;</label>
                                                                    <label
                                                                        :for="'img-0'+'_'+index_hotel+'_'+index_room"
                                                                        class="next"
                                                                        v-if="index_image ===(room.gallery.length -1)"
                                                                        style="width:30%;height: 100%;line-height: normal; padding: 17% 0 0 0">&#x203a;</label>
                                                                    <label
                                                                        :for="'img-'+ (index_image + 1)+'_'+index_hotel+'_'+index_room"
                                                                        class="next"
                                                                        style="width:30%;height: 100%;line-height: normal; padding: 17% 0 0 0"
                                                                        v-else>&#x203a;</label>
                                                                </div>
                                                            </li>
                                                        </template>
                                                        <li class="nav-dots">
                                                            <label
                                                                :for="'img-'+index_image+'_'+index_hotel+'_'+index_room"
                                                                class="nav-dot"
                                                                :id="'img-dot-'+index_image+'_'+index_hotel+'_'+index_room"
                                                                v-for="(gallery,index_image) in room.gallery"></label>
                                                        </li>
                                                    </ul>
                                                    <ul class="slides  noPa" v-else>
                                                        <li class="default"><img src="/images/hotel-default.jpg">
                                                        </li>
                                                    </ul>
                                                </div>
                                                <div class="col-lg-9 content-personality">
                                                    <h3>@{{ room.name }}</h3>
                                                    <p>@{{ room.description }}</p>
                                                    <p>{{ trans('hotel.label.capacity') }} <img
                                                            src="/images/user.png" v-for="n in room.max_capacity">
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="row col-lg-12 list-personality"
                                                v-for="(rate,index_rate) in room.rates">
                                                <div class="row col-lg-12">
                                                    <div class="bg-danger" v-show="rate.show_message_error">
                                                        @{{ rate.message_error }}
                                                    </div>
                                                </div>
                                                <div class="row col-lg-3">
                                                    <div class="row col-lg-12">
                                                        <div class="row col-lg-2">
                                                            <button class="btn btn-success"
                                                                v-if="rate.onRequest ==1">OK
                                                            </button>
                                                            <button class="btn btn-danger"
                                                                v-if="rate.onRequest ==0">RQ
                                                            </button>
                                                        </div>

                                                        <div class="row col-lg-10">
                                                            <p><b>@{{ rate.name }}</b><br>
                                                                (@{{ rate.rateProvider }})<br>
                                                                <template v-if="rate.meal_id == 1">
                                                                    <img src="/images/tasa.png"
                                                                        :alt="rate.meal_name"
                                                                        :title="rate.meal_name">
                                                                    <img src="/images/tenedor.png"
                                                                        :alt="rate.meal_name"
                                                                        :title="rate.meal_name">
                                                                    <img src="/images/timbre.png"
                                                                        :alt="rate.meal_name"
                                                                        :title="rate.meal_name">
                                                                </template>
                                                                <template v-if="rate.meal_id == 2"
                                                                    :alt="rate.meal_name"
                                                                    :title="busqueda.rates[0].meal_name">
                                                                    <img src="/images/tasa.png"
                                                                        :alt="rate.meal_name"
                                                                        :title="rate.meal_name">
                                                                </template>
                                                                <template v-if="rate.meal_id == 3"
                                                                    :alt="rate.meal_name"
                                                                    :title="rate.meal_name">
                                                                    <img src="/images/tasa.png"
                                                                        :alt="rate.meal_name"
                                                                        :title="rate.meal_name">
                                                                    <img src="/images/tenedor.png"
                                                                        :alt="rate.meal_name"
                                                                        :title="rate.meal_name">
                                                                </template>
                                                            </p>
                                                        </div>

                                                        <div class="modal fade tarifaSeleccion"
                                                            :id="'tarifa_'+index_hotel+'_'+index_room+'_'+index_rate"
                                                            tabindex="-1">
                                                            <div class="modal-dialog" role="document">
                                                                <div class="modal-content">
                                                                    <a href="" aria-label="Close" class="close"
                                                                        data-dismiss="modal"
                                                                        @click.prevent="hideShowModalTarifa(index_hotel,index_room,index_rate)">
                                                                        {{ trans('hotel.label.Close') }}<i
                                                                            aria-hidden="true">&times;</i>
                                                                    </a>
                                                                    <div class="modal-body">
                                                                        <div v-for="rate_in in rate.rate">
                                                                            <div class="titleModalTarifa">@{{
                                                                                    room.name }}
                                                                                <span>@{{ rate.name }} </span>
                                                                                <span> (@{{ rate.rateProvider }})<br></span>
                                                                            </div>
                                                                            <table width="100%">
                                                                                <thead>
                                                                                    <tr>
                                                                                        <th>{{ trans('hotel.label.date') }}</th>
                                                                                        <th class="text-center">
                                                                                            {{ trans('hotel.label.adults') }}
                                                                                            (@{{
                                                                                            rate_in.quantity_adults_total
                                                                                            }})
                                                                                        </th>
                                                                                        <th class="text-center">
                                                                                            {{ trans('hotel.label.child') }}
                                                                                            (@{{
                                                                                            rate_in.quantity_child_total
                                                                                            }})
                                                                                        </th>
                                                                                        <th class="text-center">
                                                                                            Extra (@{{
                                                                                            rate_in.quantity_extras_total
                                                                                            }})
                                                                                        </th>
                                                                                        <th class="text-center no-border">
                                                                                            Subtotal
                                                                                        </th>
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody>
                                                                                    <tr v-for="amount_day in rate_in.amount_days">
                                                                                        <td>@{{amount_day.date }}</td>
                                                                                        <td class="text-center">
                                                                                            @{{amount_day.rate[0].total_adult }}
                                                                                        </td>
                                                                                        <td class="text-center">
                                                                                            @{{amount_day.rate[0].total_child }}
                                                                                        </td>
                                                                                        <td class="text-center">
                                                                                            @{{amount_day.rate[0].total_extra }}
                                                                                        </td>
                                                                                        <td class="text-center no-border">
                                                                                            @{{amount_day.rate[0].total_amount }}
                                                                                        </td>
                                                                                    </tr>
                                                                                </tbody>
                                                                            </table>

                                                                            <template
                                                                                v-if="!rate_in.supplements && rate.supplements.supplements.length > 0">

                                                                                <div class="titleModalTarifa"
                                                                                    style="margin-top: 24px;">
                                                                                    {{ trans('hotel.label.supplement')}}
                                                                                </div>
                                                                                <table width="100%">
                                                                                    <thead>
                                                                                        <tr>
                                                                                            <th class="text-center">{{ trans('hotel.label.supplement') }}</th>
                                                                                            <th class="text-center no-border">{{ trans('hotel.label.subtotal') }}</th>
                                                                                        </tr>
                                                                                    </thead>
                                                                                    <tbody>
                                                                                        <tr v-for="supplement in rate.supplements.supplements">
                                                                                            <td>@{{supplement.supplement }}
                                                                                            </td>
                                                                                            <td class="text-center no-border">
                                                                                                @{{supplement.total_amount }}
                                                                                            </td>
                                                                                        </tr>
                                                                                    </tbody>
                                                                                </table>
                                                                            </template>


                                                                            <template
                                                                                v-if="rate_in.supplements && rate_in.supplements.supplements.length > 0">

                                                                                <div class="titleModalTarifa"
                                                                                    style="margin-top: 24px;">
                                                                                    {{ trans('hotel.label.supplement')}}
                                                                                </div>
                                                                                <table width="100%">
                                                                                    <thead>
                                                                                        <tr>
                                                                                            <th class="text-center">{{ trans('hotel.label.supplement') }}</th>
                                                                                            <th class="text-center no-border">{{ trans('hotel.label.subtotal') }}</th>
                                                                                        </tr>
                                                                                    </thead>
                                                                                    <tbody>
                                                                                        <tr v-for="supplement in rate_in.supplements.supplements">
                                                                                            <td>@{{supplement.supplement }}
                                                                                            </td>
                                                                                            <td class="text-center no-border">
                                                                                                @{{supplement.total_amount }}
                                                                                            </td>
                                                                                        </tr>
                                                                                    </tbody>
                                                                                </table>
                                                                            </template>

                                                                            <table width="100%">
                                                                                <tr class="tarifa_total">
                                                                                    <td colspan="5">
                                                                                        Total {{ trans('hotel.label.subtotal') }}
                                                                                        $<b>@{{
                                                                                                parseFloat(rate_in.total_amount
                                                                                                -
                                                                                                rate_in.total_taxes_and_services).toFixed(2)
                                                                                                }}</b></td>
                                                                                </tr>
                                                                                <tr class="tarifa_total">
                                                                                    <td colspan="5">{{ trans('hotel.label.taxes') }}
                                                                                        / {{ trans('hotel.label.services') }}
                                                                                        $<b>@{{
                                                                                                parseFloat(rate_in.total_taxes_and_services).toFixed(2)
                                                                                                }}</b></td>
                                                                                </tr>
                                                                                <tr class="tarifa_total">
                                                                                    <td colspan="5">Total $<b>@{{
                                                                                                rate_in.total_amount
                                                                                                }}</b></td>
                                                                                </tr>
                                                                            </table>
                                                                            <br />
                                                                        </div>

                                                                        <table width="100%">
                                                                            <tr class="tarifa_total_title">
                                                                                <td colspan="5" align="justify">
                                                                                    <p><b>@{{
                                                                                                rate.political.rate.name
                                                                                                }}</b><br />
                                                                                        @{{
                                                                                            rate.political.rate.message
                                                                                            }}</p>
                                                                                </td>
                                                                            </tr>
                                                                            <tr class="tarifa_total_title">
                                                                                <td colspan="5" align="justify">
                                                                                    <p>
                                                                                        <b>{{ trans('hotel.label.cancellation_politics') }}</b><br />
                                                                                        @{{
                                                                                            rate.political.cancellation.name
                                                                                            }}
                                                                                    </p>
                                                                                </td>
                                                                            </tr>
                                                                        </table>

                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row col-lg-7">
                                                    <div class="col-lg-4">
                                                        <label
                                                            v-if="rate.onRequest ==1">{{ trans('hotel.label.rooms') }}
                                                            <br><span>@{{ rate.available }} {{ trans('hotel.label.available') }} </span></label>
                                                        <label v-if="rate.onRequest ==0"><span
                                                                style="margin-top: 10px!important;display: block;">{{ trans('hotel.label.without_inventory') }}</span></label>
                                                        <select class="form-control" v-model="rate.quantity_rates"
                                                            disabled>
                                                            <option :value="n" v-for="n in rate.available"
                                                                :selected="n===1">
                                                                @{{n}}
                                                            </option>
                                                        </select>
                                                    </div>
                                                    <template v-for="n in rate.quantity_rates">
                                                        <div class="col-lg-3 margin-bottom">
                                                            <label>{{ trans('hotel.label.adults') }}</label>
                                                            <select class="form-control"
                                                                v-model="rate.rate[n-1].quantity_adults"
                                                                disabled>
                                                                <option :value="n_pax"
                                                                    v-for="n_pax in room.max_adults">
                                                                    @{{ n_pax }}
                                                                </option>
                                                            </select>
                                                        </div>
                                                        <div class="col-lg-3 margin-bottom">
                                                            <label>{{ trans('hotel.label.child') }}</label>
                                                            <select class="form-control"
                                                                v-model="rate.rate[n-1].quantity_child"
                                                                disabled>
                                                                <option :value="0">0</option>
                                                                <option :value="n"
                                                                    v-for="n in room.max_child"></option>
                                                            </select>
                                                        </div>
                                                        <div class="col-lg-2 text-right margin-bottom">
                                                            <label>{{ trans('hotel.label.beds') }} <img
                                                                    src="/images/cama.png"></label>
                                                            <div class="price">$ <b>@{{rate.rate[n-1].total_amount }}</b></div>
                                                        </div>
                                                    </template>
                                                </div>
                                                <div class="col-lg-2 text-right">
                                                    <label>
                                                        <button class="btn-primary btnAddCart"
                                                            @click="proecessRoomAdd(hotel.id,rate)">
                                                            {{ trans('hotel.label.reserve') }}
                                                        </button>
                                                    </label>
                                                </div>
                                                <div class="row col-lg-12">
                                                    <p>
                                                        @{{ rate.political.cancellation.name }}<br>
                                                        <!--<a data-toggle="modal" data-target="tarifa_<?php //echo $index_hotel;
                                                                                                        ?>_@{{ index_room }}_@{{ index_rate }}">Ver Detalle</a>-->
                                                        <a @click="showModalTarifa(index_hotel,index_room,index_rate)"
                                                            class="btn-Link-Underline">{{ trans('hotel.label.see_detail') }}</a>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <div class="container results" v-else-if="edit_reservation_action === 3">
        <div class="motor-busqueda">
            <div class="form reportes">
                <div class="form-group">
                    <label>{{trans('reservations.label.check_in')}}
                        / {{trans('reservations.label.check_out')}}</label>
                    <div class="form-control fecha">
                        <date-range-picker
                            :locale-data="locale_data"
                            :timePicker24Hour="false"
                            :showWeekNumbers="false"
                            :ranges="false"
                            :auto-apply="true"
                            v-model="edit_reservation_cambio_fechas_form.date_range">
                        </date-range-picker>
                    </div>
                </div>
            </div>
            <div class="form-btn-action">
                <div class="row">
                    <div class="col-sm-9 align-self-center"></div>
                    <div class="col-sm-3">
                        <button class="btn btn-primary"
                            @click="buscarHabitacionesCambioFecha()">{{trans('global.label.search')}}</button>
                    </div>
                </div>
            </div>
        </div>
        <hr>
        <div class="container-heigth">
            <template v-if="edit_reservation_cambio_fechas_avail.hotels.length > 0">
                <div id="result-hotels" class="row-fluid col-lg-12 result-hotels">
                    <div class="row col-lg-12"
                        v-for="(hotel,index_hotel) in edit_reservation_cambio_fechas_avail.hotels">
                        <div class="row-fluid col-lg-12 list-rooms" :id="'container_rooms_hotel'+index_hotel">
                            <div class="content-list">
                                <div class="row col-lg-12">
                                    <div class="title-personality">
                                        <h4>{{ trans('hotel.label.custom_selection') }}</h4>
                                    </div>
                                    <div :id="'wrapper-personality-'+index_hotel"
                                        class="row col-lg-12 wrapper-personality" style="display:block">
                                        <div class="row col-lg-12" v-for="(room,index_room) in  hotel.rooms">
                                            <div class="row col-lg-12">
                                                <div class="col-lg-3">
                                                    <ul class="slides  noPa"
                                                        style="margin: 0;width: 240px;height: 190px;"
                                                        v-if="room.gallery.length > 0">
                                                        <template v-for="(gallery,index_image) in room.gallery">
                                                            <input type="radio"
                                                                :name="'radio-btn_'+index_hotel+'_'+index_room"
                                                                :id="'img-'+index_image+'_'+index_hotel+'_'+index_room"
                                                                :checked="index_image===0" />
                                                            <li class="slide-container">
                                                                <div class="slide"
                                                                    style="width: 240px;height: 130px;">
                                                                    <img :src="baseExternalURL+'images/'+gallery"
                                                                        style="border-radius: 2px;" />
                                                                </div>
                                                                <div class="nav">
                                                                    <label
                                                                        :for="'img-'+(room.gallery.length -1)+'_'+index_hotel+'_'+index_room"
                                                                        class="prev"
                                                                        v-if="index_image ===0"
                                                                        style="width:30%;height: 100%;line-height: normal; padding: 17% 0 0 0">&#x2039;</label>
                                                                    <label
                                                                        :for="'img-'+(index_image - 1)+'_'+index_hotel+'_'+index_room"
                                                                        class="prev"
                                                                        style="width:30%;height: 100%;line-height: normal; padding: 17% 0 0 0"
                                                                        v-else>&#x2039;</label>

                                                                    <label
                                                                        :for="'img-0'+'_'+index_hotel+'_'+index_room"
                                                                        class="next"
                                                                        v-if="index_image ===(room.gallery.length -1)"
                                                                        style="width:30%;height: 100%;line-height: normal; padding: 17% 0 0 0">&#x203a;</label>
                                                                    <label
                                                                        :for="'img-'+ (index_image + 1)+'_'+index_hotel+'_'+index_room"
                                                                        class="next"
                                                                        style="width:30%;height: 100%;line-height: normal; padding: 17% 0 0 0"
                                                                        v-else>&#x203a;</label>
                                                                </div>
                                                            </li>
                                                        </template>
                                                        <li class="nav-dots">
                                                            <label
                                                                :for="'img-'+index_image+'_'+index_hotel+'_'+index_room"
                                                                class="nav-dot"
                                                                :id="'img-dot-'+index_image+'_'+index_hotel+'_'+index_room"
                                                                v-for="(gallery,index_image) in room.gallery"></label>
                                                        </li>
                                                    </ul>
                                                    <ul class="slides" v-else>
                                                        <li class="default"><img src="/images/hotel-default.jpg">
                                                        </li>
                                                    </ul>
                                                </div>
                                                <div class="col-lg-9 content-personality">
                                                    <h3>@{{ room.name }}</h3>
                                                    <p>@{{ room.description }}</p>
                                                    <p>{{ trans('hotel.label.capacity') }} <img
                                                            src="/images/user.png" v-for="n in room.max_capacity">
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="row col-lg-12 list-personality"
                                                v-for="(rate,index_rate) in room.rates">
                                                <div class="row col-lg-12">
                                                    <div class="bg-danger" v-show="rate.show_message_error">
                                                        @{{ rate.message_error }}
                                                    </div>
                                                </div>
                                                <div class="row col-lg-3">
                                                    <div class="row col-lg-12">
                                                        <div class="row col-lg-2">
                                                            <button class="btn btn-success"
                                                                v-if="rate.onRequest ==1">OK
                                                            </button>
                                                            <button class="btn btn-danger"
                                                                v-if="rate.onRequest ==0">RQ
                                                            </button>
                                                        </div>
                                                        <div class="row col-lg-10">
                                                            <p><b>@{{ rate.name }}</b><br>
                                                                (@{{ rate.rateProvider }})<br>
                                                                <template v-if="rate.meal_id == 1">
                                                                    <img src="/images/tasa.png"
                                                                        :alt="rate.meal_name"
                                                                        :title="rate.meal_name">
                                                                    <img src="/images/tenedor.png"
                                                                        :alt="rate.meal_name"
                                                                        :title="rate.meal_name">
                                                                    <img src="/images/timbre.png"
                                                                        :alt="rate.meal_name"
                                                                        :title="rate.meal_name">
                                                                </template>
                                                                <template v-if="rate.meal_id == 2"
                                                                    :alt="rate.meal_name"
                                                                    :title="busqueda.rates[0].meal_name">
                                                                    <img src="/images/tasa.png"
                                                                        :alt="rate.meal_name"
                                                                        :title="rate.meal_name">
                                                                </template>
                                                                <template v-if="rate.meal_id == 3"
                                                                    :alt="rate.meal_name"
                                                                    :title="rate.meal_name">
                                                                    <img src="/images/tasa.png"
                                                                        :alt="rate.meal_name"
                                                                        :title="rate.meal_name">
                                                                    <img src="/images/tenedor.png"
                                                                        :alt="rate.meal_name"
                                                                        :title="rate.meal_name">
                                                                </template>
                                                            </p>
                                                        </div>

                                                        <div class="modal fade tarifaSeleccion"
                                                            :id="'tarifa_'+index_hotel+'_'+index_room+'_'+index_rate"
                                                            tabindex="-1">
                                                            <div class="modal-dialog" role="document">
                                                                <div class="modal-content">
                                                                    <a href="" aria-label="Close" class="close"
                                                                        data-dismiss="modal"
                                                                        @click.prevent="hideShowModalTarifa(index_hotel,index_room,index_rate)">
                                                                        {{ trans('hotel.label.Close') }}<i
                                                                            aria-hidden="true">&times;</i>
                                                                    </a>
                                                                    <div class="modal-body">
                                                                        <div v-for="rate_in in rate.rate">
                                                                            <div class="titleModalTarifa">@{{
                                                                                    room.name }}
                                                                                <span>@{{ rate.name }} </span>
                                                                                <span> (@{{ rate.rateProvider }})<br></span>
                                                                            </div>
                                                                            <table width="100%">
                                                                                <thead>
                                                                                    <tr>
                                                                                        <th>{{ trans('hotel.label.date') }}</th>
                                                                                        <th class="text-center">
                                                                                            {{ trans('hotel.label.adults') }}
                                                                                            (@{{
                                                                                            rate_in.quantity_adults_total
                                                                                            }})
                                                                                        </th>
                                                                                        <th class="text-center">
                                                                                            {{ trans('hotel.label.child') }}
                                                                                            (@{{
                                                                                            rate_in.quantity_child_total
                                                                                            }})
                                                                                        </th>
                                                                                        <th class="text-center">
                                                                                            Extra (@{{
                                                                                            rate_in.quantity_extras_total
                                                                                            }})
                                                                                        </th>
                                                                                        <th class="text-center no-border">
                                                                                            Subtotal
                                                                                        </th>
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody>
                                                                                    <tr v-for="amount_day in rate_in.amount_days">
                                                                                        <td>@{{amount_day.date }}</td>
                                                                                        <td class="text-center">
                                                                                            @{{amount_day.rate[0].total_adult }}
                                                                                        </td>
                                                                                        <td class="text-center">
                                                                                            @{{amount_day.rate[0].total_child }}
                                                                                        </td>
                                                                                        <td class="text-center">
                                                                                            @{{amount_day.rate[0].total_extra }}
                                                                                        </td>
                                                                                        <td class="text-center no-border">
                                                                                            @{{amount_day.rate[0].total_amount }}
                                                                                        </td>
                                                                                    </tr>
                                                                                </tbody>
                                                                            </table>

                                                                            <template
                                                                                v-if="!rate_in.supplements && rate.supplements.supplements.length > 0">

                                                                                <div class="titleModalTarifa"
                                                                                    style="margin-top: 24px;">
                                                                                    {{ trans('hotel.label.supplement')}}
                                                                                </div>
                                                                                <table width="100%">
                                                                                    <thead>
                                                                                        <tr>
                                                                                            <th class="text-center">{{ trans('hotel.label.supplement') }}</th>
                                                                                            <th class="text-center no-border">{{ trans('hotel.label.subtotal') }}</th>
                                                                                        </tr>
                                                                                    </thead>
                                                                                    <tbody>
                                                                                        <tr v-for="supplement in rate.supplements.supplements">
                                                                                            <td>@{{supplement.supplement }}
                                                                                            </td>
                                                                                            <td class="text-center no-border">
                                                                                                @{{supplement.total_amount }}
                                                                                            </td>
                                                                                        </tr>
                                                                                    </tbody>
                                                                                </table>
                                                                            </template>


                                                                            <template
                                                                                v-if="rate_in.supplements && rate_in.supplements.supplements.length > 0">

                                                                                <div class="titleModalTarifa"
                                                                                    style="margin-top: 24px;">
                                                                                    {{ trans('hotel.label.supplement')}}
                                                                                </div>
                                                                                <table width="100%">
                                                                                    <thead>
                                                                                        <tr>
                                                                                            <th class="text-center">{{ trans('hotel.label.supplement') }}</th>
                                                                                            <th class="text-center no-border">{{ trans('hotel.label.subtotal') }}</th>
                                                                                        </tr>
                                                                                    </thead>
                                                                                    <tbody>
                                                                                        <tr v-for="supplement in rate_in.supplements.supplements">
                                                                                            <td>@{{supplement.supplement }}
                                                                                            </td>
                                                                                            <td class="text-center no-border">
                                                                                                @{{supplement.total_amount }}
                                                                                            </td>
                                                                                        </tr>
                                                                                    </tbody>
                                                                                </table>
                                                                            </template>

                                                                            <table width="100%">
                                                                                <tr class="tarifa_total">
                                                                                    <td colspan="5">
                                                                                        Total {{ trans('hotel.label.subtotal') }}
                                                                                        $<b>@{{
                                                                                                parseFloat(rate_in.total_amount
                                                                                                -
                                                                                                rate_in.total_taxes_and_services).toFixed(2)
                                                                                                }}</b></td>
                                                                                </tr>
                                                                                <tr class="tarifa_total">
                                                                                    <td colspan="5">{{ trans('hotel.label.taxes') }}
                                                                                        / {{ trans('hotel.label.services') }}
                                                                                        $<b>@{{
                                                                                                parseFloat(rate_in.total_taxes_and_services).toFixed(2)
                                                                                                }}</b></td>
                                                                                </tr>
                                                                                <tr class="tarifa_total">
                                                                                    <td colspan="5">Total $<b>@{{
                                                                                                rate_in.total_amount
                                                                                                }}</b></td>
                                                                                </tr>
                                                                            </table>
                                                                            <br />
                                                                        </div>

                                                                        <table width="100%">
                                                                            <tr class="tarifa_total_title">
                                                                                <td colspan="5" align="justify">
                                                                                    <p><b>@{{
                                                                                                rate.political.rate.name
                                                                                                }}</b><br />
                                                                                        @{{
                                                                                            rate.political.rate.message
                                                                                            }}</p>
                                                                                </td>
                                                                            </tr>
                                                                            <tr class="tarifa_total_title">
                                                                                <td colspan="5" align="justify">
                                                                                    <p>
                                                                                        <b>{{ trans('hotel.label.cancellation_politics') }}</b><br />
                                                                                        @{{
                                                                                            rate.political.cancellation.name
                                                                                            }}
                                                                                    </p>
                                                                                </td>
                                                                            </tr>
                                                                        </table>

                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row col-lg-7">
                                                    <div class="col-lg-4">
                                                        <label
                                                            v-if="rate.onRequest ==1">{{ trans('hotel.label.rooms') }}
                                                            <br><span>@{{ rate.available }} {{ trans('hotel.label.available') }} </span></label>
                                                        <label v-if="rate.onRequest ==0"><span
                                                                style="margin-top: 10px!important;display: block;">{{ trans('hotel.label.without_inventory') }}</span></label>

                                                        <select class="form-control"
                                                            v-model="rate.quantity_rates"
                                                            disabled>
                                                            <option :value="n" v-for="n in rate.available"
                                                                :selected="n===1">
                                                                @{{n}}
                                                            </option>
                                                        </select>
                                                    </div>
                                                    <template v-for="n in rate.quantity_rates">
                                                        <div class="col-lg-3 margin-bottom">
                                                            <label>{{ trans('hotel.label.adults') }}</label>
                                                            <select class="form-control"
                                                                v-model="rate.rate[n-1].quantity_adults"
                                                                disabled>
                                                                <option :value="n_pax"
                                                                    v-for="n_pax in room.max_adults">
                                                                    @{{ n_pax }}
                                                                </option>
                                                            </select>
                                                        </div>
                                                        <div class="col-lg-3 margin-bottom">
                                                            <label>{{ trans('hotel.label.child') }}</label>
                                                            <select class="form-control"
                                                                v-model="rate.rate[n-1].quantity_child"
                                                                disabled>
                                                                <option :value="0">0</option>
                                                                <option :value="n"
                                                                    v-for="n in room.max_child"></option>
                                                            </select>
                                                        </div>
                                                        <div class="col-lg-2 text-right margin-bottom">
                                                            <label>{{ trans('hotel.label.beds') }} <img
                                                                    src="/images/cama.png"></label>
                                                            <div class="price">$ <b>@{{rate.rate[n-1].total_amount }}</b></div>
                                                        </div>
                                                    </template>
                                                </div>
                                                <div class="row col-lg-12">
                                                    <p>
                                                        @{{ rate.political.cancellation.name }}<br>
                                                        <!--<a data-toggle="modal" data-target="tarifa_<?php //echo $index_hotel;
                                                                                                        ?>_@{{ index_room }}_@{{ index_rate }}">Ver Detalle</a>-->
                                                        <a @click="showModalTarifa(index_hotel,index_room,index_rate)"
                                                            class="btn-Link-Underline">{{ trans('hotel.label.see_detail') }}</a>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-2 text-right">
                                            <label>
                                                <button class="btn-primary btnAddCart"
                                                    @click="proecessDateUpdate()">
                                                    {{ trans('hotel.label.reserve') }}
                                                </button>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>



        </div>
    </div>

</section>
{{-- Modal añadir codigo de confirmacion    --}}
<div id="modal-add-confirm" class="modal fade show modal-confirm" tabindex="-1" role="dialog" style="z-index: 98;"
    aria-labelledby="myLargeModalLabel" aria-hidden="true" ref="vuemodal">
    <div class="modal-dialog modal-md">
        <div class="py-5 my-5">
            <div>
                <button id="modal_detail_close" type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">X</span>
                </button>
            </div>
        </div>
        <div class="modal-content">
            <div class="modal-body" style="border-radius: 1rem!important;">
                <div class="service-modal-container">
                    <h2 class="modal-title text-left" style="font-size: 20px !important; margin-bottom: 1.5rem!important; color: #2E2E2E;"
                        id="itinerary">{{trans('reservations.label.num_confirmation')}}</h2>
                    <p class="text-left" style="color: #2E2E2E; font-size: 1.5rem;">{{trans('reservations.label.text_confirmation')}}</p>
                    <input type="text" class="form-control" v-model="nroconfirmation"
                        placeholder="{{trans('reservations.label.num_confirmation')}}">
                    <br>
                    <div class="d-flex justify-content-between">
                        <button class="btn-cancel mt-3" data-dismiss="modal" aria-label="Close">
                            {{trans('reservations.label.cancel')}}
                        </button>
                        <button class="btn-primary mt-3" @click="saveNroConfirm()">
                            {{trans('reservations.label.save')}}
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Cancelar reserva   --}}
<div class="modal fade show modal-cancel-reservation" tabindex="-1" role="dialog" style="z-index: 20!important"
    aria-labelledby="myLargeModalLabel" aria-hidden="true" ref="vuemodal">
    <div class="modal-dialog modal-md">
        <div class="py-5 my-5">
            <div>
                <button id="modal_cancel_close" type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">X</span>
                </button>
            </div>
        </div>
        <div class="modal-content">
            <div class="modal-body" style="border-radius: 1rem!important;">
                <div class="service-modal-container">
                    <h2 style="font-size: 20px !important; margin-bottom: 1.5rem!important; color: #2E2E2E;">Cancelar reserva</h2>
                    <p class="text-left py-4" style="color: #2E2E2E; font-size: 1.8rem;">Estás a un paso de eliminar la reserva. ¿Estás seguro?</p>
                    <p class="text-left" style="color: #2E2E2E; font-size: 1.5rem;">Ingresa un comentario para el proveedor:</p>
                    <textarea class="form-control px-3 py-2" :disabled="blockPage" v-model="message_provider" cols="30" rows="3" style="border: 1px solid #d6d6d6;"></textarea>
                    <br>

                    <label for="block_email_provider" @click="see_email_provider()">
                        <input type="checkbox" name="block_email_provider" v-model="block_email_provider"> No enviar email al hotel
                    </label>
                    <br>
                    <div class="d-flex justify-content-between">
                        <button class="btn-cancel mt-3" data-dismiss="modal" aria-label="Close">
                            {{trans('reservations.label.cancel')}}
                        </button>
                        <button class="btn-primary mt-3" @click="do_cancel()">
                            {{trans('reservations.label.yes_continue')}}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



@endsection

@section('css')
<style>
    .color-title {
        color: #eb5757 !important;
    }


    .v-select .vs--single .vs--searchable {
        padding: 0 !important;
        margin-top: 0 !important;
        padding-left: 0 !important;
    }

    .vs--searchable {
        padding: 0 !important;
        margin-top: 0 !important;
        padding-left: 0 !important;
    }

    .fa-ellipsis-v:before {
        color: #eb5757;
    }

    .label-join {
        padding: 0px;
        margin: 0;
        font-size: 10px
    }

    .canSelectText {
        user-select: text;
    }

    .page-hotel .container .result-hotels .row .list-rooms {
        background: rgba(0, 0, 0, 0.95);
        padding: 0;
        position: relative;
        top: 0;
        left: 0;
        right: 0;
        overflow: auto;
        bottom: 0;
        z-index: 21;
    }

    .container .tabsNav {
        margin-bottom: 0;
    }

    .page-hotel .container .result-hotels .row .list-rooms .wrapper-personality .list-personality select::-ms-expand {
        display: none;
    }

    .page-hotel .container .result-hotels .row .list-rooms .wrapper-personality .list-personality select {
        width: 68px;
        position: absolute;
        top: 0;
        right: 5px;
    }

    .hiddenRow {
        padding: 0 4px !important;
        background-color: #eeeeee;
        font-size: 13px;
    }

    .grid {
        display: grid;
        grid-template-areas: "head head" "menu main" "foot foot";
    }

    .a {
        grid-area: head;
        background: blue
    }

    .b {
        grid-area: menu;
        background: red
    }

    .c {
        grid-area: main;
        background: green
    }

    .d {
        grid-area: foot;
        background: orange
    }

    .container .tabsNav ul a {
        border: 1px solid #eb5757;
        line-height: 40px;
        color: #eb5757;
        font-weight: 600;
    }

    .container .tabsNav ul a.active,
    .container .tabsNav ul a:hover {
        color: #fff !important;
        background: #eb5757;
    }

    .dropdown-content a {
        cursor: pointer;
    }

    .alert-warning {
        color: #eb5757;
        background-color: #ffffff;
        border-color: #ffffff;
        font-weight: normal;
    }

    #cancel-room___BV_modal_body_ {
        border-radius: 1rem !important;
    }

    /* Bulk Cancellation Styles */
    .bulk-actions-bar {
        position: sticky;
        top: 0;
        z-index: 1;
        background: #f8f9fa;
        padding: 1rem;
        border-bottom: 2px solid #eb5757;
        margin-bottom: 1rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .reservation-checkbox {
        width: 20px;
        height: 20px;
        cursor: pointer;
        accent-color: #eb5757;
    }

    .reservation-checkbox:disabled {
        cursor: not-allowed;
        opacity: 0.5;
    }

    .reservation-row-selected {
        background-color: #fff3cd !important;
        border-left: 3px solid #eb5757;
    }

    .bulk-actions-bar .btn {
        font-size: 14px;
        padding: 8px 16px;
    }

    .bulk-actions-bar .btn-danger {
        background-color: #eb5757;
        border-color: #eb5757;
    }

    .bulk-actions-bar .btn-danger:hover {
        background-color: #d64545;
        border-color: #d64545;
    }

    .bulk-actions-bar .btn-secondary {
        background-color: #6c757d;
        border-color: #6c757d;
    }

    .bulk-actions-bar .btn-secondary:hover {
        background-color: #5a6268;
        border-color: #5a6268;
    }
</style>
@endsection

@section('js')
<script>
    new Vue({
        el: '#app',
        data: {
            msg: 'Por favor espere cargando ....',
            blockPage: false,
            update_menu: 1,
            form: {
                file_code: null,
                selected_excecutive: null,
                date_range: {
                    endDate: '',
                    startDate: '',
                }
            },
            nroconfirmation: '',
            is_reservation_modification: false,
            edit_reservation_index: 0,
            edit_reservation: {},
            edit_reservation_action: 1,
            edit_reservation_agregar_form: {
                quantity_persons: 1,
                quantity_adults: 1,
                quantity_child: 0,
                quantity_persons_rooms: [{
                    adults: 1,
                    child: 0,
                    ages_child: [{
                        child: 1,
                        age: 0
                    }]
                }],
                date_range: {
                    endDate: '',
                    startDate: '',
                }
            },
            edit_reservation_agregar_rooms_avail: {
                hotels: []
            },
            edit_reservation_cambio_fechas_form: {
                date_range: {
                    endDate: '',
                    startDate: '',
                }
            },
            edit_reservation_cambio_fechas_avail: {
                hotels: []
            },
            reservationsHotels: {},
            dateRange: {},
            locale_data: {
                direction: 'ltr',
                format: moment.localeData().postformat('ddd D MMM'),
                separator: ' - ',
                applyLabel: 'Guardar',
                cancelLabel: 'Cancelar',
                weekLabel: 'W',
                customRangeLabel: 'Rango de Fechas',
                daysOfWeek: moment.weekdaysMin(),
                monthNames: moment.monthsShort(),
                firstDay: moment.localeData().firstDayOfWeek()
            },
            baseExternalURL: window.baseExternalURL,
            file_code_: null,
            reservation_Hotel_code_: null,
            reservation_Hotel_room_code_: null,
            reservation_Hotel_room_by_channel: '',
            text_add_: "",
            message_provider: "",
            block_email_provider: 0,
            header_detail: {
                file_number: "",
                group_name: "",
                executive: "",
            },
            check_room_hyperguest: false,
            pages: 0,
            page: 0,
            show_pages: [],
            view_pages: 15,
            date_order: "asc",
            // Bulk cancellation properties
            selectedRooms: [],
            bulkCancellationResults: {
                success: [],
                failed: []
            },
            isBulkCancelling: false,
        },
        mounted() {
            // this.buscarReservas();
            let url__ = window.location.href
            // if(url__ === "http://aurora_frontend.test/reportes-reservas"){
            //     this.form.file_code = 308956
            //     this.buscarReservas()
            // }
        },
        computed: {},
        methods: {
            changeFilter: function(value) {
                if (value === 'date_order') {
                    this.date_order = (this.date_order === 'asc') ? 'desc' : 'asc';
                }
                this.getReservations();
            },
            clearParameters: function() {
                this.form.date_range = {};
                this.form.file_code = "";
            },
            validatePagination: function() {
                this.view_pages = 15

                for (let p = 0; p < this.pages; p++) {
                    this.show_pages[p] = false

                    if (this.page < this.view_pages && p > this.page) {
                        if (this.view_pages > 0) {
                            this.view_pages -= 1
                            this.show_pages[p] = true
                        }
                    } else {
                        if (this.page >= (this.pages - (this.view_pages) / 2)) {
                            if (p >= (this.pages - this.view_pages)) {
                                this.show_pages[p] = true
                            }
                        } else {
                            if (p >= parseFloat(this.page - parseFloat(this.view_pages / 2)) && p <= parseFloat(this.page + parseFloat(this.view_pages / 2))) {
                                this.show_pages[p] = true
                            }
                        }
                    }
                }

            },
            checkRoomHyperguest: function(channel_id) {
                if (this.check_room_hyperguest == false && channel_id == 6) {
                    this.check_room_hyperguest = true
                    return true
                } else {
                    return false
                }
            },
            checkChannelId: function(resRoom) {
                if (resRoom.channel_id == 6) {
                    return false
                } else {
                    return true
                }
            },
            see_email_provider() {
                this.block_email_provider = !(this.block_email_provider)
                console.log(this.block_email_provider)
            },
            go_to_hotels() {
                let a = document.createElement('a')
                a.target = '_blank'
                a.href = '/hotels';
                a.click()
            },
            showUrlHotel(resHotel) {

                console.log(resHotel)

                this.$toast.success("Generando redirección automática por favor espere un momento", {
                    position: 'top-right'
                })

                axios.post(baseExternalURL + 'api/search_passengers', {
                        file: resHotel.reservation.file_code,
                        type: 'file'
                    })
                    .then(response => {

                        let _passengers = response.data.passengers
                        let canadl_, canchd_
                        canadl_ = resHotel.reservations_hotel_rooms[0].adult_num + resHotel.reservations_hotel_rooms[0].extra_num
                        canchd_ = resHotel.reservations_hotel_rooms[0].child_num

                        axios.post(baseExternalURL + 'api/toggle_view_hotels', {
                                file: resHotel.reservation.file_code,
                                adults: canadl_,
                                child: canchd_,
                                fecini: resHotel.reservation.date_init,
                                fecfin: moment(resHotel.reservation.date_init).add(resHotel.nights, 'days').format('YYYY-MM-DD'),
                                hotel_code: resHotel.hotel_code,
                                passengers: _passengers,
                                typeclass_id: (resHotel.reservation.type_class_id) ? resHotel.reservation.type_class_id : "all"
                            })
                            .then(response => {

                                console.log(response.data)

                                axios.post(baseURL + 'toggle_view_hotels', {
                                        items: response.data.items
                                    })
                                    .then(response => {

                                        let a = document.createElement('a')
                                        a.target = '_blank'
                                        a.href = '/hotels?search=1';

                                        a.click()
                                    })
                                    .catch(error => {
                                        console.log(error)
                                    })
                            })
                            .catch(error => {
                                console.log(error)
                            })
                    })
                    .catch(e => {
                        console.log(e)
                    })
            },
            hideModal() {
                this.$refs['modal-will-cancel-reservation-room'].hide()
            },
            showModalTarifa: function(index_hotel, index_room, index_rate, tipo_tarifa) {
                let modalTarifa
                if (tipo_tarifa == 1) {
                    modalTarifa = document.getElementById('tarifaCombinacion_' + index_hotel + '_' + index_room + '_' + index_rate)
                } else {
                    modalTarifa = document.getElementById('tarifa_' + index_hotel + '_' + index_room + '_' + index_rate)
                }
                modalTarifa.classList.add('show')
                modalTarifa.style.display = 'block'
                modalTarifa.style.opacity = '1'
                modalTarifa.style.overflow = 'auto'
            },
            hideShowModalTarifa: function(index_hotel, index_room, index_rate, tipo_tarifa) {
                let modalTarifa
                if (tipo_tarifa == 1) {
                    modalTarifa = document.getElementById('tarifaCombinacion_' + index_hotel + '_' + index_room + '_' + index_rate)
                } else {
                    modalTarifa = document.getElementById('tarifa_' + index_hotel + '_' + index_room + '_' + index_rate)
                }
                modalTarifa.classList.remove('show')
                modalTarifa.style.opacity = '0'
                modalTarifa.style.display = 'none'
                modalTarifa.style.overflow = 'hidden'
            },
            calculateNumPersonsPerRooms: function() {
                let quantity_adults = 0
                let quantity_child = 0

                for (let i = 0; i < this.edit_reservation_agregar_form.quantity_persons_rooms.length; i++) {
                    quantity_adults += this.edit_reservation_agregar_form.quantity_persons_rooms[i].adults
                    quantity_child += this.edit_reservation_agregar_form.quantity_persons_rooms[i].child
                }

                this.edit_reservation_agregar_form.quantity_persons = quantity_adults + quantity_child
                this.edit_reservation_agregar_form.quantity_adults = quantity_adults
                this.edit_reservation_agregar_form.quantity_child = quantity_child
            },
            changeQuantityChild: function(childs_num, index_quantity_person_room) {
                // this.edit_reservation_agregar_form.quantity_persons_rooms[index_quantity_person_room].ages_child.splice(1, 4);

                for (let i = 1; i < childs_num; i++) {
                    this.edit_reservation_agregar_form.quantity_persons_rooms[index_quantity_person_room].ages_child.splice(
                        (i + 1), 0, {
                            child: i + 1,
                            age: 1
                        }
                    )
                }
            },
            setEditAction: function(action) {
                this.edit_reservation_action = action
            },
            isEmpty: function(data) {
                return Object.keys(data).length
            },
            dateFormatLatin: function(dateString) {
                return moment(dateString).format('DD/MM/Y')
            },
            getTimeDate(dateString) {
                return moment(dateString).format('HH:mm:ss')
            },
            hasPenalty(resHotel, resHotelRoom) {
                let hasPenalty = false
                console.log(resHotel)
                if (resHotel.cancel_at) {
                    hasPenalty = resHotel.reservations_hotel_rooms.length > 0 ? moment(resHotel.reservations_hotel_rooms[0].first_penalty_date).isSameOrBefore(resHotel.cancel_at) : false
                } else {
                    if (resHotel.status === 0) {
                        hasPenalty = resHotel.reservations_hotel_rooms.length > 0 ? moment(resHotel.reservations_hotel_rooms[0].first_penalty_date).isSameOrBefore(resHotel.creaeAt) : false
                    } else {
                        hasPenalty = resHotel.reservations_hotel_rooms.length > 0 ? moment(resHotel.reservations_hotel_rooms[0].first_penalty_date).isSameOrBefore(moment()) : false
                    }
                }
                // console.log('Penalidad: ' + hasPenalty)

                return hasPenalty
            },
            calculateStatusColorReservation(status) {
                // * 0: cancelada
                // * 1: activa
                // * 2: modificada
                // * 3: por confirmar
                // * 4: error
                // * 5: cancelada     con penalidad
                // * 6: activa        con penalidad
                // * 7: modificada    con penalidad
                // * 8: por confirmar con penalidad
                // * 9: con error     con penalidad
                if (status === 0) { // 0: cancelada
                    return 'clasi_cancelada'
                } else if (status === 1 || status === 2) { // 1: activa 2: modificada
                    return 'clasi_sinPenalidad'
                } else if (status === 3) { // 3: por confirmar
                    return 'clasi_posrConfirmar'
                } else if (status === 4) { // 4: error
                    return 'clasi_conError'
                } else if (status === 5) { // 6: activa con penalidad  7: modificada con penalidad
                    return 'clasi_canceladaConPenalidad'
                } else if (status === 6 || status === 7) { // 6: activa con penalidad  7: modificada con penalidad
                    return 'clasi_conPenalidad'
                } else if (status === 8) { // 5: por confirmar con penalidad
                    return 'clasi_porConfirmarConPenalidad'
                } else if (status === 9) { // cancelada con penalidad
                    return 'clasi_canceladaConPenalidad'
                } else {
                    return 'clasi_statusNoDefinido'
                }
            },
            buscarReservas: function() {
                this.is_reservation_modification = false
                this.edit_reservation = {}
                this.getReservations()
            },
            toggle_see_rooms(hotel, boolean_) {
                console.log(hotel);
                this.reservationsHotels.forEach((h) => {
                    h.toggle_ = false
                })
                if (boolean_) {
                    hotel.toggle_ = true
                }
            },
            changePage: function(_page) {
                this.page = _page
                this.getReservations()
            },
            getReservations: function() {
                this.msg = "{{trans('global.message.processing_the_request_please_wait')}}"
                this.blockPage = true

                let args = {
                    file_code: this.form.file_code,
                    selected_excecutive: this.form.selected_excecutive,
                    from_date: this.form.date_range.startDate,
                    to_date: this.form.date_range.endDate,
                    selected_client: localStorage.getItem('client_code'),
                    client_id: localStorage.getItem('client_id'),
                    option: 'booking_report',
                    user_type_id: localStorage.getItem('user_type_id'),
                    from_aurora: true,
                    page: this.page,
                    date_order: this.date_order,
                }

                this.edit_reservation_agregar_rooms_avail = {
                    hotels: []
                }
                this.edit_reservation_cambio_fechas_avail = {
                    hotels: []
                }

                axios.post('services/reservations/hotels/list', args)
                    .then((result) => {
                        this.blockPage = false
                        if (result.data.success) {

                            let file_number_ = ""
                            let files_n = 0
                            result.data.data.forEach((hotel, h) => {
                                hotel.toggle_ = false
                                if (file_number_ === "") {
                                    file_number_ = hotel.reservation.file_code
                                    files_n++
                                } else {
                                    if (hotel.reservation.file_code === file_number_) {
                                        files_n++
                                    }
                                }
                                let channel_reservation_codes_label = "";
                                let channel_reservation_codes_ = [];
                                let total_confirmed_1_2 = 0;
                                hotel.reservations_hotel_rooms.forEach((room, r) => {
                                    if (room.channel_reservation_code != '' && room.channel_reservation_code != "0") {
                                        if (channel_reservation_codes_[room.channel_reservation_code] == undefined) {
                                            channel_reservation_codes_[room.channel_reservation_code] = true;
                                            let sep_ = (channel_reservation_codes_label == "") ? "" : " / "
                                            channel_reservation_codes_label += sep_ + room.channel_reservation_code;
                                        }
                                    }
                                    if (room.status == 1 || room.status == 2) {
                                        total_confirmed_1_2++;
                                    }
                                });

                                if (hotel.status !== 3) {
                                    hotel.channel_reservation_codes_label = channel_reservation_codes_label;
                                    if (total_confirmed_1_2 == hotel.reservations_hotel_rooms.length) {
                                        hotel.status = 1;
                                    }
                                }
                            })
                            if (result.data.data.length > 0 && files_n === result.data.data.length) {
                                this.header_detail.file_number = result.data.data[0].reservation.file_code
                                this.header_detail.group_name = result.data.data[0].reservation.customer_name
                                this.header_detail.executive = result.data.data[0].reservation.executive_name
                            } else {
                                this.header_detail.file_number = ""
                                this.header_detail.group_name = ""
                                this.header_detail.executive = ""
                            }

                            this.reservationsHotels = result.data.data

                            this.pages = result.data.pages
                            this.validatePagination()
                        } else {
                            this.$toast.error(result.data.error, {
                                position: 'top-right'
                            })
                        }
                    }).catch((e) => {
                        console.log("Error en:", e)
                        this.blockPage = false
                        this.$toast.error(e, {
                            position: 'top-right'
                        })
                    })
            },
            // modificar resservas
            setReservation: function(reservation, resIndex) {
                this.reservationsHotels[resIndex] = reservation
                return reservation
            },
            setReservationEdit: function(reservation, resIndex) {
                this.edit_reservation = reservation
                this.edit_reservation_index = resIndex
            },
            getReservation: function(file_code, reservation_Hotel_code, resIndex) {
                this.msg = "{{trans('global.message.processing_the_request_please_wait')}}"
                this.blockPage = true

                let reservation = {}
                axios.post('services/reservations/hotels/list', {
                    file_code: file_code,
                    reservation_Hotel_code: reservation_Hotel_code,
                    selected_excecutive: this.form.selected_excecutive,
                    from_date: this.form.date_range.startDate,
                    to_date: this.form.date_range.endDate,
                    selected_client: localStorage.getItem('client_code'),
                    client_id: localStorage.getItem('client_id'),
                    option: 'booking_report',
                    user_type_id: localStorage.getItem('user_type_id'),
                    from_aurora: true,
                    date_order: this.date_order,
                }).then((result) => {
                    if (result.data.success) {
                        reservation = this.setReservation(result.data.data[0], resIndex)

                        this.setReservationEdit(reservation, resIndex)

                        this.blockPage = false
                    } else {
                        this.blockPage = false
                        // rate.show_message_error = true
                        // rate.message_error = result.data.error
                        this.$toast.error(result.data.error, {
                            position: 'top-right'
                        })
                    }
                }).catch((e) => {
                    this.blockPage = false
                    this.$toast.error(e, {
                        position: 'top-right'
                    })
                })
            },
            will_cancel_reservation_room: function(file_code, reservation_Hotel_code, reservation_Hotel_room_code) {

                this.file_code_ = file_code
                this.reservation_Hotel_code_ = reservation_Hotel_code
                this.reservation_Hotel_room_code_ = reservation_Hotel_room_code
                this.reservation_Hotel_room_by_channel = 1
                this.text_add_ = ''

                this.edit_reservation.reservations_hotel_rooms.forEach((r) => {
                    if (r.id === reservation_Hotel_room_code) {
                        if (r.stella_updated_at != null) {
                            this.text_add_ = 'El Sistema ' + `<strong>NO</strong>` + ' puede calcular automáticamente la penalidad de esta reserva, porque fue una reserva que bajó de Aurora a Stela con status RQ. Antes de anular, por favor ' + `<strong>consultar con el proveedor si hay penalidad.</strong>` + ' Y si lo hubiera, registrarla manualmente en el file. '
                        }
                    }
                })

                this.$refs['modal-will-cancel-reservation-room'].show()

            },
            will_cancel_reservation_room_by_channel: function(file_code, reservation_Hotel_code) {

                this.file_code_ = file_code
                this.reservation_Hotel_code_ = reservation_Hotel_code
                this.reservation_Hotel_room_code_ = ''
                this.reservation_Hotel_room_by_channel = 6


                this.$refs['modal-will-cancel-reservation-room'].show()

            },

            do_cancel_reservation_room() {
                this.blockPage = true
                axios.post('services/reservations/hotels/cancel-room', {
                    file_code: this.file_code_,
                    reservation_Hotel_code: this.reservation_Hotel_code_,
                    reservation_Hotel_room_code: this.reservation_Hotel_room_code_,
                    message_provider: this.message_provider,
                    block_email_provider: (this.block_email_provider) ? 1 : 0,
                    channel: this.reservation_Hotel_room_by_channel
                }).then((result) => {
                    if (result.data.success) {
                        this.$toast.success("Cancelado Correctamente", {
                            position: 'top-right'
                        })
                        // this.getReservation(this.file_code_, this.reservation_Hotel_code_, this.edit_reservation_index)
                        this.getReservations();
                    } else {
                        this.$toast.error(result.data.data.error[0], {
                            position: 'top-right'
                        })
                    }
                    this.message_provider = ""
                    this.blockPage = false
                    this.hideModal()
                }).catch((e) => {
                    this.$toast.error(e, {
                        position: 'top-right'
                    })
                    this.blockPage = false
                    this.hideModal()
                })
            },
            editReservation: function(resHotel, resIndex) {
                // resHotel.reservation.file_code,resHotel.id
                this.setReservationEdit(resHotel, resIndex)

                this.msg = "{{trans('global.message.processing_the_request_please_wait')}}"
                this.blockPage = true
                this.is_reservation_modification = true
                this.blockPage = false
            },
            editReservationCollapse: function(resHotel, resIndex) {
                // resHotel.reservation.file_code,resHotel.id
                this.setReservationEdit(resHotel, resIndex)

                // this.msg = 'Procesando la peticion por favor espere....'
                // this.blockPage = true
                // this.is_reservation_modification = true
                // this.blockPage = false
            },
            buscarHabitacionAgregar: function() {
                this.msg = "{{trans('global.message.looking_for_rooms_please_wait')}}"
                this.blockPage = true
                axios.post(
                    'services/reservations/hotels/modification', {
                        type: 'room_add',
                        reservation_hotel_code: this.edit_reservation.id,
                        adult_num: this.edit_reservation_agregar_form.quantity_persons_rooms[0].adults,
                        child_num: this.edit_reservation_agregar_form.quantity_persons_rooms[0].child,
                        ages_child: this.edit_reservation_agregar_form.quantity_persons_rooms[0].ages_child
                    }
                ).then((result) => {
                    if (result.data.success === true) {
                        this.edit_reservation_agregar_rooms_avail = result.data.data
                    }
                    this.blockPage = false
                }).catch((e) => {
                    //this.blockPage = false
                    console.log(e)
                })

            },
            buscarHabitacionesCambioFecha: function() {
                this.msg = "{{trans('global.message.looking_for_rooms_please_wait')}}"
                this.blockPage = true
                axios.post(
                    'services/reservations/hotels/modification', {
                        type: 'date_update',
                        reservation_hotel_code: this.edit_reservation.id,
                        check_in: this.edit_reservation_cambio_fechas_form.date_range.startDate,
                        check_out: this.edit_reservation_cambio_fechas_form.date_range.endDate,
                    }
                ).then((result) => {
                    if (result.data.success === true) {
                        this.edit_reservation_cambio_fechas_avail = result.data.data
                    }
                    this.blockPage = false
                }).catch((e) => {
                    //this.blockPage = false
                    console.log(e)
                })
            },
            proecessRoomAdd: function(hotel_id, rate) {
                let resData = {
                    client_id: this.edit_reservation.reservation.client_id,
                    executive_id: this.edit_reservation.reservation.executive_id,
                    file_code: this.edit_reservation.reservation.file_code,
                    reference: this.edit_reservation.reservation.customer_name,
                    guests: [{
                        given_name: this.edit_reservation.reservation.given_name,
                        surname: this.edit_reservation.reservation.surname,
                    }],
                    reservations: [{
                        reservation_hotel_code: this.edit_reservation.id,
                        token_search: this.edit_reservation_agregar_rooms_avail.token_search,
                        room_ident: 0,
                        hotel_id: hotel_id,
                        best_option: false,
                        rate_plan_room_id: rate.rateId,
                        suplements: [],
                        guest_note: '',
                        date_from: this.edit_reservation.check_in,
                        date_to: this.edit_reservation.check_out,
                        quantity_adults: rate.rate[0].quantity_adults,
                        quantity_child: rate.rate[0].quantity_child,
                        child_ages: []
                    }],
                }

                this.msg = "{{trans('global.message.saving_your_reservation_please_wait')}}"
                this.blockPage = true
                axios.post('services/reservations/hotels/add-room', resData).then((result) => {
                    if (result.data.success) {
                        this.getReservation(this.edit_reservation.reservation.file_code, this.edit_reservation.id, this.edit_reservation_index)
                        this.edit_reservation_action = 1
                    } else {
                        // rate.show_message_error = true
                        // rate.message_error = result.data.error
                        this.$toast.error(result.data.error, {
                            position: 'top-right'
                        })
                        this.blockPage = false
                    }
                }).catch((e) => {
                    this.blockPage = false
                    console.log(e)
                })
            },
            proecessDateUpdate: function() {
                this.msg = "{{trans('global.message.processing_the_request_please_wait')}}"
                this.blockPage = true

                let resData = {
                    client_id: this.edit_reservation.reservation.client_id,
                    executive_id: this.edit_reservation.reservation.executive_id,
                    file_code: this.edit_reservation.reservation.file_code,
                    reference: this.edit_reservation.reservation.customer_name,
                    guests: [{
                        given_name: this.edit_reservation.reservation.given_name,
                        surname: this.edit_reservation.reservation.surname,
                    }],
                    reservations: [],
                }

                Object.entries(this.edit_reservation.reservations_hotel_rooms).forEach(([key, rate]) => {
                    dataRoom = {
                        reservation_hotel_code: this.edit_reservation.id,
                        token_search: this.edit_reservation_cambio_fechas_avail.token_search,
                        room_ident: key,
                        hotel_id: this.edit_reservation.hotel_id,
                        best_option: false,
                        rate_plan_room_id: rate.rates_plans_room_id,
                        suplements: [],
                        guest_note: '',
                        date_from: this.edit_reservation_cambio_fechas_avail.search_parameters.date_from,
                        date_to: this.edit_reservation_cambio_fechas_avail.search_parameters.date_to,
                        quantity_adults: rate.adult_num + rate.extra_num,
                        quantity_child: rate.child_num,
                        child_ages: []
                    }

                    // $.each(rate.options.rate.supplements.supplements, function (key_sup, val_sup) {
                    //     if (val_sup.type_req_opt == 'optional') {
                    //         dataRoom.suplements.push({
                    //             supplement_id: val_sup.supplement_id,
                    //             amount_extra: val_sup.amount_extra,
                    //             calendars: val_sup.calendars,
                    //             date: val_sup.date,
                    //             key: val_sup.key,
                    //             options: val_sup.options,
                    //             show_calendar: val_sup.show_calendar,
                    //             supplement: val_sup.supplement,
                    //             supplement_dates_selected: val_sup.supplement_dates_selected,
                    //             total_amount: val_sup.total_amount,
                    //             totals: val_sup.totals,
                    //             type: val_sup.type,
                    //             type_req_opt: val_sup.type_req_opt
                    //         });
                    //         console.log(dataRoom.suplements);
                    //     }
                    // });

                    resData.reservations.push(dataRoom)
                })

                axios.post('services/reservations/hotels/dates-update', resData).then((result) => {
                    if (result.data.success) {
                        this.getReservation(this.edit_reservation.reservation.file_code, this.edit_reservation.id, this.edit_reservation_index)
                        this.edit_reservation_action = 1
                    } else {
                        // rate.show_message_error = true
                        // rate.message_error = result.data.error
                        this.$toast.error(result.data.error, {
                            position: 'top-right'
                        })
                        this.blockPage = false
                    }
                }).catch((e) => {
                    this.blockPage = false
                    console.log(e)
                })
            },
            do_cancel() {
                // this.blockPage = true
                // axios.post('services/reservations/hotels/cancel-room', {
                //     file_code: this.file_code_,
                //     reservation_Hotel_code: this.reservation_Hotel_code_,
                //     reservation_Hotel_room_code: this.reservation_Hotel_room_code_,
                //     message_provider: this.message_provider,
                // }).then((result) => {
                //     if (result.data.success) {
                //         this.$toast.success("Cancelado Correctamente", {
                //             position: 'top-right'
                //         })
                //         this.getReservation(this.file_code_, this.reservation_Hotel_code_, this.edit_reservation_index)
                //     } else {
                //         this.$toast.error(result.data.data.error[0], {
                //             position: 'top-right'
                //         })
                //     }
                //     this.message_provider = ""
                //     this.blockPage = false
                //     this.hideModal()
                // }).catch((e) => {
                //     this.$toast.error(e, {
                //         position: 'top-right'
                //     })
                //     this.blockPage = false
                //     this.hideModal()
                // })

                this.blockPage = true

                axios.post('services/reservations/hotels/cancellation', {
                    file_code: this.edit_reservation.reservation.file_code,
                    reservation_Hotel_code: this.edit_reservation.id,
                    message_provider: this.message_provider,
                    block_email_provider: (this.block_email_provider) ? 1 : 0,
                }).then((result) => {
                    if (result.data.success) {
                        this.$toast.success("Cancelado Correctamente", {
                            position: 'top-right'
                        })
                        // this.getReservation(this.edit_reservation.reservation.file_code, this.edit_reservation.id, this.edit_reservation_index)
                        this.getReservations()
                        this.message_provider = ""
                    } else {
                        this.$toast.error(result.data.error, {
                            position: 'top-right'
                        })
                        this.blockPage = false
                    }
                    this.closeModalCancel()
                }).catch((e) => {


                    let displayError = ''

                    if (e.response) {
                        displayError = e.response.data.message
                    } else {
                        displayError = e.message
                    }
                    this.$toast.error(displayError, {
                        position: 'top-right'
                    })
                    this.blockPage = false
                })
            },
            openModalCancelReservation: function(resHotel, resIndex) {
                this.nroconfirmation = ''
                this.setReservationEdit(resHotel, resIndex)
                $('.modal-cancel-reservation').modal()
            },
            openModalCodeConfirm: function(resHotel, resIndex) {
                this.nroconfirmation = ''
                this.setReservationEdit(resHotel, resIndex)
                $('.modal-confirm').modal()
            },
            closeModalDetail: function() {
                document.getElementById('modal_detail_close').click()
            },
            closeModalCancel: function() {
                document.getElementById('modal_cancel_close').click()
            },
            saveNroConfirm: function() {

                if (this.nroconfirmation === '') {
                    this.$toast.warning("{{trans('global.message.enter_the_confirmation_code')}}", {
                        position: 'top-right'
                    })
                    return
                }

                this.msg = "{{trans('global.message.processing_the_request_please_wait')}}"
                this.blockPage = true
                this.loading = true
                let room_codes = []
                let reservation_hotel_id = ''
                for (var i = 0; i < this.edit_reservation.reservations_hotel_rooms.length; i++) {
                    reservation_hotel_id = this.edit_reservation.reservations_hotel_rooms[i].reservations_hotel_id
                    room_codes.push(this.edit_reservation.reservations_hotel_rooms[i].room_code)
                }

                axios.post('services/hotels/reservation/add/confirmation', {
                    file_code: this.edit_reservation.reservation.file_code,
                    reservation_hotel_id: reservation_hotel_id,
                    room_codes: room_codes,
                    nroconfirmation: this.nroconfirmation
                }).then((result) => {
                    if (result.data.success) {
                        this.reservationsHotels[this.edit_reservation_index].reservations_hotel_rooms[0].channel_reservation_code = this.nroconfirmation
                        this.blockPage = false
                        this.loading = false
                        this.closeModalDetail()
                    } else {
                        this.blockPage = false
                        this.loading = false
                        this.$toast.error(result.data.error, {
                            position: 'top-right'
                        })
                    }
                }).catch((e) => {
                    this.blockPage = false
                    this.loading = false
                    this.$toast.error(e, {
                        position: 'top-right'
                    })
                })
            },
            checkIsFile: function(resHotel) {
                if (resHotel.reservation) {
                    if (Number.isInteger(parseInt(resHotel.reservation.file_code))) { //&& resHotel.reservations_hotel_rooms[0].channel_code === 'AURORA'
                        return true
                    } else {
                        return false
                    }
                } else {
                    console.log(resHotel)
                    return false
                }
            },
            // Bulk Cancellation Methods
            toggleRoomSelection(resRoom, resHotel) {
                const existingIndex = this.selectedRooms.findIndex(r => r.id === resRoom.id)
                if (existingIndex > -1) {
                    this.selectedRooms.splice(existingIndex, 1)
                } else {
                    // Store necessary data for cancellation
                    this.selectedRooms.push({
                        ...resRoom,
                        file_code: resHotel.reservation.file_code,
                        reservation_hotel_id: resHotel.id,
                        channel_id: resRoom.channel_id
                    })
                }
            },
            isRoomSelected(resRoom) {
                return this.selectedRooms.some(r => r.id === resRoom.id)
            },
            clearSelection() {
                this.selectedRooms = []
            },
            openBulkCancelModal() {
                if (this.selectedRooms.length === 0) {
                    this.$toast.warning('Por favor selecciona al menos una habitación', {
                        position: 'top-right'
                    })
                    return
                }
                this.$refs['modal-bulk-cancel'].show()
            },
            closeBulkCancelModal() {
                this.$refs['modal-bulk-cancel'].hide()
            },
            async processBulkCancellation() {
                this.isBulkCancelling = true
                this.blockPage = true
                this.bulkCancellationResults = {
                    success: [],
                    failed: []
                }

                // Split reservations into chunks of 7
                const BATCH_SIZE = 7
                const chunks = []
                for (let i = 0; i < this.selectedRooms.length; i += BATCH_SIZE) {
                    chunks.push(this.selectedRooms.slice(i, i + BATCH_SIZE))
                }

                // Process each chunk sequentially
                for (const chunk of chunks) {
                    const cancellationPromises = chunk.map(room => {
                        return axios.post('services/reservations/hotels/cancel-room', {
                                file_code: room.file_code,
                                reservation_Hotel_code: room.reservation_hotel_id,
                                reservation_Hotel_room_code: room.id,
                                message_provider: this.message_provider,
                                block_email_provider: this.block_email_provider ? 1 : 0,
                                channel: room.channel_id
                            })
                            .then(response => {
                                if (response.data.success) {
                                    this.bulkCancellationResults.success.push({
                                        id: room.id,
                                        hotel: room.room_name // Using room name for report
                                    })
                                } else {
                                    this.bulkCancellationResults.failed.push({
                                        id: room.id,
                                        hotel: room.room_name,
                                        error: response.data.error || 'Error desconocido'
                                    })
                                }
                            })
                            .catch(error => {
                                let errorMsg = 'Error de conexión'
                                if (error.response && error.response.data && error.response.data.message) {
                                    errorMsg = error.response.data.message
                                } else if (error.message) {
                                    errorMsg = error.message
                                }
                                this.bulkCancellationResults.failed.push({
                                    id: room.id,
                                    hotel: room.room_name,
                                    error: errorMsg
                                })
                            })
                    })

                    // Wait for current chunk to complete before processing next chunk
                    await Promise.allSettled(cancellationPromises)
                }

                this.showConsolidatedResults()

                this.selectedRooms = []
                this.message_provider = ''
                this.block_email_provider = 0
                this.getReservations()

                this.isBulkCancelling = false
                this.blockPage = false
                this.closeBulkCancelModal()
            },
            showConsolidatedResults() {
                const total = this.bulkCancellationResults.success.length +
                    this.bulkCancellationResults.failed.length
                const successCount = this.bulkCancellationResults.success.length
                const failedCount = this.bulkCancellationResults.failed.length

                if (failedCount === 0) {
                    this.$toast.success(
                        `✓ ${successCount} de ${total} reservas canceladas exitosamente`, {
                            position: 'top-right',
                            duration: 5000
                        }
                    )
                } else if (successCount === 0) {
                    this.$toast.error(
                        `✗ Error: No se pudo cancelar ninguna de las ${total} reservas`, {
                            position: 'top-right',
                            duration: 5000
                        }
                    )
                } else {
                    this.$toast.warning(
                        `⚠ ${successCount} canceladas, ${failedCount} fallaron de ${total} reservas`, {
                            position: 'top-right',
                            duration: 5000
                        }
                    )
                }

                if (failedCount > 0) {
                    const errorDetails = this.bulkCancellationResults.failed
                        .map(f => `• ${f.hotel}: ${f.error}`)
                        .join('\n')

                    console.error('Detalles de errores de cancelación masiva:', errorDetails)
                }
            },
        },
        filters: {
            formatDate: function(_date) {
                if (_date == undefined) {
                    // console.log('fecha no parseada: ' + _date)
                    return;
                }
                let secondPartDate = ''

                if (_date.length > 10) {
                    secondPartDate = _date.substr(10, _date.length)
                    _date = _date.substr(0, 10)
                }

                _date = _date.split('-')
                _date = _date[2] + '/' + _date[1] + '/' + _date[0]
                return _date + secondPartDate
            },
        }
    })
</script>

@endsection
