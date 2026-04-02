@extends('layouts.app')
@section('content')
    <section class="page-hotel">
        <loading-component v-show="loadSearch"></loading-component>
        {{--Inicio Filtro de Busqueda--}}
        <div class="container">
            <div class="motor-busqueda">
                <div class="form">
                    <div class="form-row">
                        <div class="form-group fecha">
                            <select name="fecha_exclusive" v-show="hasFixedOutputs"
                                    class="form-control">
                                <option :value="fixed_output.date" v-for="fixed_output in fixed_outputs">
                                    @{{ fixed_output.date }}
                                </option>
                            </select>
                            <date-picker :name="'picker'" v-show="!hasFixedOutputs"
                                         v-model="date"
                                         :config="options"></date-picker>

                        </div>
                        <div class="form-group pasajeros dropdown">
                            <button class="form-control" id="dropdownHab" data-toggle="dropdown" aria-haspopup="true"
                                    aria-expanded="true">
                                <span><strong>@{{ adults }}</strong>{{trans('global.label.adults')}}</span>
{{--                                <span><strong>@{{ child }}</strong>{{trans('global.label.children')}}</span>--}}
                            </button>
                            <div aria-labelledby="dropdownHab" class="dropdown dropdown-menu"
                                 :class="class_container_rooms">
                                <div class="container_quantity_persons_rooms_selects quantity-persons-rooms">
                                    <div class="form-group"
                                         :class="class_container_select">
                                        <label>{{trans('global.label.adults')}}</label>
                                        <select class="form-control" v-model="adults"
                                                @change="changeQuantityAdult($event)">
                                            <option v-for="num_adults in 10" :value="num_adults">@{{ num_adults }}
                                            </option>
                                        </select>
                                    </div>
{{--                                    <div class="form-group"--}}
{{--                                         :class="class_container_select">--}}
{{--                                        <label>{{trans('global.label.children')}}</label>--}}
{{--                                        <select class="form-control" v-model="child"--}}
{{--                                                @change="changeQuantityChild($event)">--}}
{{--                                            <option v-for="num_child in 6" :value="num_child - 1">@{{ num_child - 1 }}--}}
{{--                                            </option>--}}
{{--                                        </select>--}}
{{--                                    </div>--}}
{{--                                    <div class="form-group"--}}
{{--                                         :class="class_container_select"--}}
{{--                                         v-for="(age_child_slot,index_age_child) in quantity_persons[0].age_childs"--}}
{{--                                         v-if="child >=1">--}}
{{--                                        <label>{{trans('global.label.age')}}</label>--}}
{{--                                        <select class="form-control"--}}
{{--                                                v-model="quantity_persons[0].age_childs[index_age_child].age">--}}
{{--                                            <option v-for="age_child in 17" :value="age_child">@{{ age_child }}</option>--}}
{{--                                        </select>--}}
{{--                                    </div>--}}
                                </div>
                            </div>
                        </div>
                        <div class="form-group fecha">
                            <select name="service_type_id" @change="changeServiceType" v-model="service_type_id"
                                    class="form-control">
                                <option :value="service_type.id" v-for="service_type in service_types">
                                    @{{ service_type.translations[0].value }} - @{{ service_type.code }}
                                </option>
                            </select>
                        </div>
                        <div class="form-group hotel">
                            <select name="class_hotel" @change="changeTypeClass" v-model="typeclass_id"
                                    class="form-control">
                                <option :value="hotel.class_id" v-for="hotel in classes_hotel">
                                    @{{ hotel.class_name }}
                                </option>
                            </select>
                        </div>
                        <div class="col-sm-1.5">
                            <button class="btn btn-primary"
                                    @click="getPackages()">{{trans('global.label.search')}}</button>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
        </div>
        {{--Fin Filtro de Busqueda--}}
        <div class="container">
            <div class="row">
                <section class="col-12 titulo">
                    <h2>{{trans('package.label.selected_programs')}}</h2>
                </section>
                <section class="col-12 programa-seleccionado" v-if="package_selected[1] != null">
                    <div class="col-12 d-flex flex-wrap" data-fx="true">
                        <!-- programa 1 -->
                        <div class="container-programa">
                            <div class="programa">
                                <div class="programa-detalle">
                                    <div class="bloque-info">
                                        <div class="duracion">@{{ package_selected[1].nights + 1 }} D/ @{{
                                            package_selected[1].nights }} N
                                        </div>
                                        <div class="nombre">@{{ package_selected[1].translations[0].name }}</div>
                                        <div class="ruta">@{{ package_selected[1].destinations }}</div>
                                        <div class="clasificacion">
                                            <!-- Incluir para Tooltip: data-toggle="tooltip" title="NOMBRE" -->
                                            <span class="tipo" style="--tipo-color:#4A90E2">@{{ package_selected[1].tag.translations[0].value }}</span>
                                            <span class="tipo" style="--tipo-color:#BBBDBF">EXCLUSIVO</span>
                                        </div>
                                    </div>
                                    <div class="bloque-detalle accordion">
                                        <ul class="nav nav-pills nav-fill" role="tablist">
                                            <li class="nav-item" v-if="package_selected[0] != null">
                                                <a class="nav-link" href="#det1" data-toggle="tab" role="tab"
                                                   id="tab-1">
                                                    <p
                                                        v-html="package_selected[0].translations[0].name">
                                                    </p>
                                                </a>
                                            </li>
                                            <li class="nav-item" v-if="package_selected[1] != null">
                                                <a class="nav-link active" href="#det2" data-toggle="tab" role="tab"
                                                   id="tab-2"
                                                   v-if="package_selected[1].translations[0].name != null">
                                                    <p v-html="package_selected[1].translations[0].name"></p>
                                                </a>
                                            </li>
                                            <li class="nav-item" v-if="package_selected[2]!= null">
                                                <a class="nav-link" href="#det3" data-toggle="tab" role="tab"
                                                   id="tab-3">
                                                    <p v-html="package_selected[2].translations[0].name"></p>
                                                </a>
                                            </li>
                                        </ul>
                                        <div class="descripciones tab-content" role="tablist">
                                            <div class="descripcion collapse" id="det1" role="tabpanel"
                                                 aria-labelledby="tab-1" v-if="package_selected[0] != null">
                                                <p v-html="package_selected[0].translations[0].description "></p></div>
                                            <div class="descripcion collapse active" id="det2" role="tabpanel"
                                                 aria-labelledby="tab-2" v-if="package_selected[1] != null">
                                                <p v-html="package_selected[1].translations[0].description"></p>
                                            </div>
                                            <div class="descripcion collapse" id="det3" role="tabpanel"
                                                 aria-labelledby="tab-3" v-if=" package_selected[2] != null">
                                                <p v-html="package_selected[2].translations[0].description"></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="programa-foto">
                                    <img :src="package_selected[1].galleries[0]" alt="Image Package"
                                         onerror="this.onerror=null;this.src='https://res.cloudinary.com/litomarketing/image/upload/c_scale,w_400/v1440093948/paragliding_in_Miraflores._123110314_dlyun3.jpg'"
                                         v-if="package_selected[1].galleries.length > 0">
                                    <img :src="package_selected[1].image_link" alt="Image Package"
                                         v-else-if="package_selected[1].image_link && package_selected[1].image_link != '' && package_selected[1].image_link != null">
                                    <img
                                        src="https://res.cloudinary.com/litomarketing/image/upload/c_scale,w_800/v1440093948/paragliding_in_Miraflores._123110314_dlyun3.jpg"
                                        alt="Image Package" v-else>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <section class="col-12 programa-acciones">
                    <div class="filtros">
                        <div class="enlaces">
                            <a href="#" class="link" @click="openModal()"><i class="icon icon-link"></i>
                                <span>{{trans('package.label.extensions')}}</span></a>
                            <a href="#" class="link" @click="openModalRegulacion()"><i class="icon icon-info"></i>
                                <span>{{trans('package.label.regulations')}}</span></a>
                            <a v-if="hasPermission('mfpackages','rates')" href="#" @click="willExport" class="link"><i
                                    class="icon icon-download"></i>
                                <span>{{trans('package.label.tariff')}}</span></a>
                            <a v-if="package_selected[1] != null &&
                                package_selected[1].translations[0].itinerary_link != null &&
                                package_selected[1].translations[0].itinerary_link != ''"
                               :href="package_selected[1].translations[0].itinerary_link" class="link" download>
                                <i class="icon icon-download"></i> <span>{{trans('service.label.itinerary')}}</span>
                            </a>
                        </div>
                        <div class="p-2 d-inline-flex align-items-center ml-auto botones">
                            <h2 v-if="package_selected[1] != null" class="mb-0">
                                $ @{{ roundLito(price_total) }}
                            </h2>
                            <small class="mr-2 ml-2">x</small>
                            <i class="far fa-user fa-1x"></i>
                        </div>
                        <div class="botones">
                            <a href="#" class="btn btn-primary btn-slim"
                               v-on:click="modalPassengers()">{{ trans('quote.label.passengers_list') }} (@{{
                                (quantity_persons[0].adults + quantity_persons[0].child_with_bed + quantity_persons[0].child_without_bed) }})
                            </a>
                            <a href="#" class="btn btn-primary btn-slim"
                               @click="modalQuantityRooms">{{trans('package.label.reserve')}}</a>
                            <a href="#" class="btn btn-primary btn-slim" v-if="allow_modify"
                               @click="edit()">{{trans('package.label.quote')}}</a>
                        </div>
                    </div>
                </section>
                <section class="col-12 programa-detalles">
                    <div class="itinerario">
                        <h3 style="margin-bottom: 15px">{{trans('package.label.tinerary_day_by_day')}}</h3>
                        <!-- paquete 1 -->
                        <div class="col px-2 mb-3">
                            <div class="prod-estado" style="display: flex">
                                <span>
                                    <span class="estado estado-rq mr-1"></span> RQ
                                </span>
                                <span class="d-flex align-items-center mx-3">
                                    <span class="estado estado-ok mr-1"></span> OK
                                </span>
                            </div>
                        </div>
                        <div class="paquete" v-if="package_selected[0] != null">
                            <h4 v-html="package_selected[0].translations[0].name">
                                <span class="tipo" style="--tipo-color:#E9E9E9;"></span>
                            </h4>
                            <div class="dia" v-for="(itinerary,index) in itinerary[0]">
                                <span class="dia-num">{{trans('global.label.day')}} @{{ itinerary.day }}</span>
                                <span class="destino">
                                    <strong>@{{ itinerary.destinations }}</strong>
                                </span>
                                <p class="nota" v-for="descrip in itinerary.description">
                                    @{{ descrip }}<br>
                                </p>
                                <p class="hotel" v-for="hotel in itinerary.hotel">
                                    <i class="fas fa-hotel"></i> @{{ hotel.name }}<br>
                                </p>
                            </div>
                        </div>
                        <!-- paquete 2 -->
                        <div class="paquete" v-if="package_selected[1] != null">
                            <h4 v-html="package_selected[1].translations[0].name">
                                <span class="tipo" style="--tipo-color:#E9E9E9;"></span>
                            </h4>
                            <div class="dia" v-for="(itinerary,index) in itinerary[1]">
                                <span class="dia-num">{{trans('global.label.day')}} @{{ itinerary.day }}</span>
                                <span class="destino">
                                    <strong>@{{ itinerary.destinations }}</strong>
                                </span>
                                <p class="nota" v-for="descrip in itinerary.description" v-if="descrip != ''">
                                    @{{ descrip }}<br>
                                </p>
                                <p class="hotel" v-for="hotel in itinerary.hotel">
                                    <i class="fas fa-building" style="margin-right: 8px;"></i> @{{ hotel.name }}
                                    <span class="estado estado-ok" v-if="hotel.on_request === 0"></span>
                                    <span class="estado estado-rq" v-if="hotel.on_request === 1"></span>
                                </p>
                            </div>
                        </div>

                        <!-- paquete 3 -->
                        <div class="paquete" v-if="package_selected[2] != null">
                            <h4 v-html="package_selected[2].translations[0].name">
                                <span class="tipo" style="--tipo-color:#E9E9E9;"></span>
                            </h4>
                            <div class="dia" v-for="(itinerary,index) in itinerary[2]">
                                <span class="dia-num">{{trans('global.label.day')}} @{{ itinerary.day }}</span>
                                <span class="destino">
                                    <strong>@{{ itinerary.destinations }}</strong>
                                </span>
                                <p class="nota" v-for="descrip in itinerary.description">
                                    @{{ descrip }}<br>
                                </p>
                            </div>
                        </div>

                        <div class="table-package" v-if="package_selected[1 ] != null && showChangeHotel">
                            <table>
                                <thead>
                                <tr>
                                    <th colspan="4" style="padding: 0 1rem;">Reservación de paquetes</th>
                                </tr>
                                <tr class="text-center">
                                    <th>Hotel</th>
                                    <th>Fechas</th>
                                    {{--                                    <th>Status</th>--}}
                                    <th>Option</th>
                                </tr>
                                </thead>
                                <tbody class="text-center">
                                <tr v-for="service in package_selected[1].services" v-if="service.type == 'hotel'"
                                    v-bind:class="{ 'isSelected': service.selected  }">
                                    <td data-th="cod">@{{ service.hotel.name }}</td>
                                    <td data-th="Name">@{{ service.date_in | formatDate }} <i
                                            class="fas fa-long-arrow-alt-right"></i> @{{ service.date_out | formatDate
                                        }}
                                    </td>
                                    {{--                                    <td data-th="Status">--}}
                                    {{--                                        <div v-if="service.hotel.on_request === 0">--}}
                                    {{--                                            <span class="estado estado-ok"></span> OK--}}
                                    {{--                                        </div>--}}
                                    {{--                                        <div v-if="service.hotel.on_request === 1">--}}
                                    {{--                                            <span class="estado estado-rq"></span> RQ--}}
                                    {{--                                        </div>--}}
                                    {{--                                    </td>--}}
                                    <td data-th="action">
                                        <a href="#" @click="showModalHotel(service)">
                                            <span class="icon-repeat"></span> Cambiar
                                        </a>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                            <div v-show="showCardChangeHotel">
                                <div class="card card-body">
                                    <h4 class="title-card font-weight-bold mb-0">{{trans('package.label.change_hotel')}}</h4>
                                    <hr>
                                    <div class="form-content">
                                        <div class="table-responsive table-results">
                                            <div class="text-center" v-if="loadSearchHotel">
                                                <b-spinner variant="primary" label="Spinning"></b-spinner>
                                            </div>
                                            <div v-if="moreHotels.length==0 && !loadSearchHotel"
                                                 class=" none-result text-center"><i
                                                    class="icon-cloud-off"></i> {{trans('package.label.not_results')}}.
                                            </div>

                                            <div v-if="moreHotels.length>0 && !loadSearchHotel">
                                                <div :class="'col-12 row_' + (hkey%2)"
                                                     v-for="(hotel, hkey) in moreHotels">
                                                    <div>
                                                        <div class="accordion" @click="showContentHotel(hotel)">
                                                            <h5 style="padding: 7px 7px 0;"
                                                                class=" d-flex justify-content-between">
                                                                <label :for="'checkbox_' + hotel.id + '_'">
                                                                    <i class="fa fa-hotel"></i>
                                                                    <span v-if="hotel.hotel.channel.length>0">
                                                                            [@{{ hotel.hotel.channel[0].code }}] -
                                                                            </span>
                                                                    @{{ hotel.hotel.name }}

                                                                    <input type="radio"
                                                                           style="float: right;position: absolute;right: 25px;"
                                                                           :id="'checkbox_' + hotel.id + '_'"
                                                                           :name="'checkbox_hotel'"
                                                                           v-model="checkboxs[ hotel.id + '_']"
                                                                           :disabled="loading">
                                                                </label>
                                                            </h5>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>

                                        </div>

                                        <div class="form-group cotizacion-crear--boton" v-if="moreHotels.length>0">
                                            <button class="btn btn-primary" type="button" @click="addHotel()"
                                                    :disabled="loading || loadSearchHotel">
                                                <i class="fa fa-spin fa-spinner" v-if="loading || loadSearchHotel"></i>
                                                <span v-if="!loading || !loadSearchHotel"> Cambiar</span>
                                            </button>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mapa" v-if="package_selected[1] != null">
                        <h3>{{trans('package.label.program_route')}}</h3>
                        <div class="row">
                            <div class="col-md-9" v-if="package_selected[1].map_link != null">
                                <iframe class="full img-thumbnail" frameborder="0"
                                        style="border:0; height: 400px; opacity: 0.9;width: 100%"
                                        :src="package_selected[1].map_link"></iframe>
                            </div>
                            <div class="col-md-3">
                                <p class="font-weight-bold">{{trans('package.label.destinations')}}</p>
                                <ol type="1">
                                    <li v-for="destiny in package_selected[1].package_destinations">
                                        @{{ destiny.state.translations[0].value }}
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </section>
    @include('packages.modal')

    <modal-passengers ref="modal_passengers"></modal-passengers>

    <div id="modalRegulacion" class="modal fade show modal-regulacion" tabindex="-1" role="dialog"
         aria-labelledby="myLargeModalLabel" aria-hidden="true" v-if="package_selected[1] !== null"
         ref="vuemodalRegulacion">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h2 class="modal-title" id="myLargeModalLabel">{{trans('package.label.regulations')}}</h2>
                    <hr>
                    <p class="mt-5" v-html="package_selected[1].translations[0].inclusion"></p>
                </div>
            </div>
        </div>
    </div>
    {{-- MODAL ALERTA COTI--}}
    <div id="modalAlertaCotizacion" tabindex="1" role="dialog" class="modal show" ref="modalAlerta">
        <div role="document" class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <h4 class="text-center">
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
    {{-- MODAL Seleccion de Distribucion de habitaciones para Reservar--}}
    <div id="modalQuantityRooms" tabindex="1" role="dialog" class="modal show" ref="modalQuantityRooms">
        <div role="document" class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                            id="close_modal_quantity_rooms">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row col-lg-12" style="margin-bottom: 10px">
                        <h3>Por favor distribuya la cantidad de adultos elegida (@{{ adults }}), en habitaciones:</h3>
                    </div>
                    <div class="row col-lg-12">
                        <div class="col-lg-4">
                            <label for="">SGL</label>
                            <input type="text" class="form-control" v-model="quantity_rooms_sgl"
                                   @keyup="validateQuantityRooms">
                        </div>
                        <div class="col-lg-4">
                            <label for="">DBL</label>
                            <input type="text" class="form-control" v-model="quantity_rooms_dbl"
                                   @keyup="validateQuantityRooms">
                        </div>
                        <div class="col-lg-4" style="margin-bottom: 10px">
                            <label for="">TPL</label>
                            <input type="text" class="form-control" v-model="quantity_rooms_tpl"
                                   @keyup="validateQuantityRooms">
                        </div>
                        <div class="col-lg-12">
                            <input type="text" class="form-control" v-model="reference"
                                   placeholder="{{trans('package.label.file_reference')}}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 text-center" style="margin-top: 10px">
                            <button class="btn btn-success mt-2"
                                    @click="packageReservation">{{trans('package.label.reserve')}}</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>


@endsection
@section('css')
    <style>
        .isSelected {
            background-color: #dee2e6;
        }

        .cursor-pointer {
            cursor: pointer;
        }

        .radio_true {
            color: #a71c1c;
        }

        .tr_radio_true {
            background-color: #bfdaff5e;
        }

        .check_true {
            background-color: #A71B20 !important;
            color: #FFF !important;
        }

        .check_true, .check_false {
            font-size: 15px;
            float: right;
            margin-right: 5px;
            margin-bottom: 5px;
        }

        .row_0 {
            background: #ffffff;
            padding: 10px;
            border-radius: 4px;
            line-height: 22px;
            color: #615555;
            border: solid 1px #8a9b9b;
            font-size: 13px;
            margin-bottom: 10px !important;
        }

        .row_1 {
            background: #ffffff;
            padding: 10px;
            border-radius: 4px;
            line-height: 22px;
            color: #615555;
            border: solid 1px #8a9b9b;
            font-size: 13px;
            margin-bottom: 10px !important;
        }

        .rateChoosed_true {
            background: #deffe2;
            border: solid 1px #39573b;
        }

        .rateRow {
            border-radius: 3px;
            padding: 4px 0 4px 6px;
        }
    </style>
@endsection
@section('js')
    <script>
        new Vue({
            el: '#app',
            data: {
                showModal: false,
                allow_modify: 0,
                loading: false,
                hotelToChange: [],
                hotelSelected: {},
                moreHotels: [],
                checkboxs: [],
                hasFixedOutputs: false,
                showCardChangeHotel: false,
                showChangeHotel: false,
                fixed_outputs: [],
                service_rate_ids: [],
                package_selected: [
                    null,
                    null,
                    null
                ],
                options: {
                    format: 'DD/MM/YYYY',
                    useCurrent: false,
                    defaultDate: moment().add(1, 'days').format('YYYY-MM-DD'),
                    minDate: moment().add(1, 'days').format('YYYY-MM-DD'),
                },
                date: '',
                date1: '',
                adults: 2,
                num_adults: 2,
                child: 0,
                num_child: 0,
                quantity_adults: 2,
                quantity_child: 0,
                quantity_persons: [{
                    adults: 2,
                    child_with_bed: 0,
                    child_without_bed: 0,
                    age_childs: [
                        {
                            age: 1
                        }
                    ]
                }],
                quantity_rooms_sgl: 0,
                quantity_rooms_dbl: 0,
                quantity_rooms_tpl: 0,
                package_plan_rate_category_id: 0,
                mouseDrag: false,
                service_types: [],
                classes_hotel: [],
                itinerary: [
                    null,
                    null,
                    null
                ],
                typeclass_id: 'all',
                extensions: [],
                class_container_rooms: 'container_quantity_persons_rooms width_default_container',
                class_container_select: 'container_quantity_persons_rooms_select width_default_select',
                service_type_id: 1,
                loadSearch: false,
                loadSearchHotel: false,
                price_total: 0,
                reference: '',
                package_id: '',
                permissions: [],
            },
            created: function () {
                this.getExtensions()
                if (localStorage.getItem('permissions')) {
                    if (localStorage.getItem('permissions') !== '' && localStorage.getItem('permissions') != null) {
                        this.permissions = JSON.parse(localStorage.getItem('permissions'))
                    }
                }
            },
            mounted () {
                let $state = null
                // this.getServicesTypes()
                this.getPackagesSelected()
                // JQUERY + VUE: DRAG
                this.$nextTick(() => {
                    //this.mensaje =
                    // this.checkVue("$nextTick");

                    var $draggable = $('.owl-carousel .item'),
                        $active1, $active2, $extension

                    $('body')
                        .on('dragstart', '.owl-carousel .item', function (event) {
                            $extension = $(this)
                            // this.checkVue("dragstart");
                        })
                        .on('drop', '.block-extension', function (event) {
                            var $hermano = $(this).siblings('.block-extension')
                            // revisar si ya ha sido asignado y reubicarlo (bloque).
                            if ($extension.attr('data-extension') === $hermano.attr('data-extension')) {
                                $('.item[data-extension]').removeClass('selected')
                                $hermano.empty()
                                    .attr('data-extension', '0')
                                    .html('<div class=\'extension\'></div>')
                            }
                            // revisar si no tiene nada asignado (bloque) y desactivar anterior (lista)
                            else if ($(this).attr('data-extension') !== '0') {
                                $('.item[data-extension=\'' + $(this).attr('data-extension') + '\']').removeClass('selected')
                            }
                            // copiar (bloque) + seleccionar (lista)
                            $(this)
                                .html($extension.html())
                                .attr('data-extension', $extension.attr('data-extension'))
                            $('.item.extension[data-extension=\'' + $(this).attr('data-extension') + '\']').addClass('selected')
                            // detener drag
                            event.preventDefault()
                            $(this).removeClass('drop-able')
                            // this.checkVue("drop");
                        })
                        .on('dragover', '.block-extension', function (event) {
                            event.preventDefault()
                            $(this).addClass('drop-able')
                            // this.checkVue("dragover");
                        })
                        .on('dragenter', '.block-extension', function (event) {
                            $(this).addClass('drop-able')
                            // this.checkVue("dragenter");
                        })
                        .on('dragleave', '.block-extension', function (event) {
                            $(this).removeClass('dragleave')
                        })
                        .on('click', '.block-extension', function () {
                            // revisar si tiene extension + remover extension
                            console.log($(this).attr('data-extension'))
                            if ($(this).attr('data-extension') !== '0') {
                                // des-seleccionar (lista)
                                $('.item.extension[data-extension=\'' + $(this).attr('data-extension') + '\']').removeClass('selected')
                                // vaciar (bloque)
                                $(this).empty().attr('data-extension', '0').html('<div class=\'extension\'></div>')
                            }
                            $(this).removeClass('click')
                        })

                    $('.modal').on('hidden.bs.modal', function (event) {
                        $('.owl-carousel').trigger('destroy.owl.carousel')
                        $(this).removeClass('destroy carousel on modal.close')
                    })
                })
                this.restore_filters_from_storage()
            },
            computed: {},
            methods: {
                modalPassengers: function () {
                    this.$refs.modal_passengers.modalPassengers('session', '', (this.quantity_persons[0].adults + this.quantity_persons[0].child_with_bed + this.quantity_persons[0].child_without_bed + 0), this.quantity_persons[0].adults, (this.quantity_persons[0].child_with_bed + this.quantity_persons[0].child_without_bed), 0)
                },
                hasPermission: function (permission, action) {
                    let index = 0
                    let flag_permission = false
                    let enable = false
                    for (let i = 0; i < this.permissions.length; i++) {
                        if (this.permissions[i].subject === permission) {
                            flag_permission = true
                            index = i
                        }
                    }
                    if (flag_permission) {
                        for (let a = 0; a < this.permissions[index].actions.length; a++) {
                            if (this.permissions[index].actions[a] === action) {
                                enable = true
                            }
                        }
                    }
                    return enable
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
                getClassHotelByClientId: function (plan_rate_id) {
                    axios.get('api/package/categories/' + plan_rate_id + '?lang=' + localStorage.getItem('lang'))
                        .then((result) => {
                            if (result.data.success) {
                                this.classes_hotel = result.data.data
                            }
                        }).catch((e) => {
                        console.log(e)
                    })
                },
                getTypeServiceByClientId: function (package_id) {
                    axios.get('api/package/type_service/' + package_id + '?lang=' + localStorage.getItem('lang'))
                        .then((result) => {
                            if (result.data.success) {
                                this.service_types = result.data.data
                            }
                        }).catch((e) => {
                        console.log(e)
                    })
                },
                changeFormatDate: function (fixed_outputs) {
                    console.log(fixed_outputs)
                    for (let i = 0; i < fixed_outputs.length; i++) {
                        fixed_outputs[i].date = moment(fixed_outputs[i].date, 'YYYY-MM-DD').format('DD/MM/YYYY')
                    }
                    return fixed_outputs
                },
                getPackagesSelected: function () {
                    this.loadSearch = true
                    axios.get(baseExternalURL + 'api/packages/selected?lang=' + localStorage.getItem('lang'))
                        .then((result) => {
                            this.loadSearch = false
                            if (result.data.package && result.data.package.length > 0) {
                                this.price_total = 0
                                this.package_selected = [null, null, null]
                                this.package_selected[0] = (result.data.package[0] !== null) ? result.data.package[0] : null
                                this.package_selected[1] = (result.data.package[1] !== null) ? result.data.package[1] : null
                                this.package_selected[2] = (result.data.package[2] !== null) ? result.data.package[2] : null
                                if (this.package_selected[1] !== null) {
                                    this.allow_modify = this.package_selected[1].allow_modify
                                    this.reference = this.package_selected[1].translations[0].name
                                    let services = this.package_selected[1].services
                                    services.forEach(function (valor, indice, array) {
                                        valor.selected = false
                                    })
                                    this.package_selected[1].services = services
                                }
                                let date = result.data.params.date
                                console.log(date)
                                this.date = moment(date, 'YYYY-MM-DD').format('DD/MM/YYYY')
                                this.options.date = moment(date, 'YYYY-MM-DD').format('DD/MM/YYYY')
                                this.options.defaultDate = moment(date, 'YYYY-MM-DD').format('DD/MM/YYYY')
                                this.options.minDate = moment().add(1, 'days').format('DD/MM/YYYY')
                                // console.log(moment().format('DD/MM/YYYY') < moment(date, 'YYYY-MM-DD').format())
                                // if (moment().format() > this.options.date) {
                                //     this.options.minDate = moment(this.package_selected[1].plan_rates[0].date_from, 'YYYY-MM-DD').format('DD/MM/YYYY')
                                // } else {
                                //     this.options.minDate = moment().format('DD/MM/YYYY')
                                // }
                                //
                                this.options.maxDate = moment(this.package_selected[1].plan_rates[0].date_to, 'YYYY-MM-DD').format('DD/MM/YYYY')

                                if (this.package_selected[1].extension == '2' && this.package_selected[1].fixed_outputs.length > 0) {
                                    this.hasFixedOutputs = true
                                    this.fixed_outputs = this.changeFormatDate(this.package_selected[1].fixed_outputs)
                                    this.date = moment(this.fixed_outputs[0].date, 'DD/MM/YYYY').format('DD/MM/YYYY')
                                } else if (this.package_selected[1].extension == '2' && this.package_selected[1].fixed_outputs.length == 0) {
                                    this.hasFixedOutputs = false
                                }
                                this.getItinerary()
                                this.service_type_id = result.data.params.service_type
                                this.date = moment(result.data.params.date, 'YYYY-MM-DD').format('DD/MM/YYYY')
                                this.quantity_persons = result.data.params.quantity_persons
                                this.adults = result.data.params.quantity_persons[0].adults
                                this.child = result.data.params.quantity_persons[0].child
                                this.typeclass_id = this.package_selected[1].plan_rates[0].plan_rate_categories[0].type_class_id
                                this.package_plan_rate_category_id = this.package_selected[1].plan_rates[0].plan_rate_categories[0].id
                                if (this.package_selected[1].plan_rates[0].plan_rate_categories[0].optionals_count > 0) {
                                    this.showChangeHotel = true
                                } else {
                                    this.showChangeHotel = false
                                }
                                this.getClassHotelByClientId(this.package_selected[1].plan_rates[0].id)
                                this.getTypeServiceByClientId(this.package_selected[1].id)
                                this.price_total = this.getPriceTotal()
                            } else {
                                window.location = baseURL + 'packages/'
                            }

                        })
                },
                getItinerary: function () {
                    var itinerary_extension1 =
                        (this.package_selected[0] !== null && this.package_selected[0] !== undefined)
                            ? this.package_selected[0].itinerary : []
                    var itinerary_extension2 =
                        (this.package_selected[2] !== null && this.package_selected[0] !== undefined)
                            ? this.package_selected[2].itinerary : []
                    var itinerary_package =
                        (this.package_selected[1] !== null && this.package_selected[1] !== undefined)
                            ? this.package_selected[1].itinerary : []
                    var dayCount = 1
                    //EXTENSION 1
                    if (this.package_selected[0] !== null) {
                        for (let i = 0; i < itinerary_extension1.length; i++) {
                            itinerary_extension1[i].day = dayCount
                            dayCount++
                        }

                        this.itinerary[0] = itinerary_extension1
                    }

                    console.log(itinerary_package)
                    // console.log( itinerary_package.length )
                    console.log(this.package_selected)

                    // PAQUETE
                    if (this.package_selected[1] !== null && itinerary_package !== undefined) {
                        for (let c = 0; c < itinerary_package.length; c++) {
                            itinerary_package[c].day = dayCount
                            dayCount++
                        }
                        this.itinerary[1] = itinerary_package
                    }

                    //EXTENSION 1
                    if (this.package_selected[2] !== null) {
                        for (let i = 0; i < itinerary_extension2.length; i++) {
                            itinerary_extension2[i].day = dayCount
                            dayCount++
                        }
                        this.itinerary[2] = itinerary_extension2
                    }
                },
                setHotelStatus: function () {
                    var itinerary_extension1 = (this.package_selected[0] !== null) ? this.package_selected[0].itinerary : []
                    var itinerary_extension2 = (this.package_selected[2] !== null) ? this.package_selected[2].itinerary : []
                    var itinerary_package = (this.package_selected[1] !== null) ? this.package_selected[1].itinerary : []
                    // if (this.package_selected[1] !== null) {
                    //
                    //   this.itinerary[1] = itinerary_package
                    // }
                    console.log(this.itinerary)
                },
                getPriceTotal: function () {
                    let price_extension_1 = (this.package_selected[0] != null) ? this.package_selected[0].price : 0
                    let price_package = (this.package_selected[1] != null) ? this.package_selected[1].price : 0
                    let price_extension_2 = (this.package_selected[2] != null) ? this.package_selected[2].price : 0
                    let total = (price_extension_1 + price_package + price_extension_2)
                    let price_total = Number(Math.round(total + 'e2') + 'e-2') // 1.01
                    return price_total
                },
                getExtensions: function () {
                    axios.post(baseExternalURL + 'api/extensions', {
                        lang: localStorage.getItem('lang'),
                        client_id: localStorage.getItem('client_id'),
                        date: (this.date === '') ? moment().format('YYYY-MM-DD') : moment(this.date, 'DD/MM/YYYY').format('YYYY-MM-DD'),
                        quantity_persons: this.quantity_persons,
                        service_type: this.service_type_id,
                        type_class: this.typeclass_id
                    }).then((result) => {
                        this.extensions = result.data
                    }).catch((e) => {
                        console.log(e)
                    })
                },
                openModal: function () {
                    $('#modalExtension').modal()
                    this.initCarousel()
                    this.$forceUpdate()
                },
                openModalRegulacion: function () {
                    $('#modalRegulacion').modal()
                },
                dragClick: function () {

                },
                savePackagesSelected: function (onlySave) {
                    if ($('#ext1').attr('data-extension') !== '0') {
                        let extension_id = $('#ext1').attr('data-extension')
                        for (let i = 0; i < this.extensions.length; i++) {
                            if (this.extensions[i]['id'] == parseInt(extension_id)) {
                                this.package_selected[0] = this.extensions[i]
                            }
                        }
                    } else {
                        this.package_selected[0] = null
                    }
                    if ($('#ext2').attr('data-extension') !== '0') {
                        let extension_id = $('#ext2').attr('data-extension')
                        for (let i = 0; i < this.extensions.length; i++) {
                            if (this.extensions[i]['id'] == parseInt(extension_id)) {
                                this.package_selected[2] = this.extensions[i]
                            }
                        }
                    } else {
                        this.package_selected[2] = null
                    }

                    axios.post(baseExternalURL + 'api/packages/selected', {
                        packages_selected: this.package_selected,
                        params: {
                            date: (this.date === '') ? moment().format('YYYY-MM-DD') : moment(this.date, 'DD/MM/YYYY').format('YYYY-MM-DD'),
                            quantity_persons: this.quantity_persons,
                            service_type: this.service_type_id,
                            type_class: this.typeclass_id
                        },
                        lang: localStorage.getItem('lang'),
                    }).then((result) => {
                        if (onlySave) {
                            window.location = baseURL + 'packages/details'
                        }
                    })
                },
                savePackagesChangeFilter: function () {
                    axios.post(baseExternalURL + 'api/packages/selected', {
                        packages_selected: this.package_selected,
                        params: {
                            date: (this.date === '') ? moment().format('YYYY-MM-DD') : moment(this.date, 'DD/MM/YYYY').format('YYYY-MM-DD'),
                            quantity_persons: this.quantity_persons,
                            service_type: this.service_type_id,
                            type_class: this.typeclass_id
                        }
                    }).then((result) => {
                        console.log(result.data)
                    })
                }
                ,
                getPackages: function () {
                    this.loadSearch = true
                    axios.post(baseExternalURL + 'api/packages/active', {
                        lang: localStorage.getItem('lang'),
                        client_id: localStorage.getItem('client_id'),
                        date: (this.date === '') ? moment().format('YYYY-MM-DD') : moment(this.date, 'DD/MM/YYYY').format('YYYY-MM-DD'),
                        quantity_persons: this.quantity_persons,
                        service_type: this.service_type_id,
                        type_class: this.typeclass_id,
                        package_id: [this.package_selected[1].id],
                        type_package: [this.package_selected[1].extension],
                    })
                        .then((result) => {
                            if (result.data.length > 0) {
                                this.package_selected[1] = result.data[0]
                                this.getItinerary()
                                this.savePackagesChangeFilter()
                                this.price_total = this.getPriceTotal()
                                this.packages_original = result.data
                                localStorage.setItem('m_package_date', this.date)
                                localStorage.setItem('m_package_quantity_persons', JSON.stringify(this.quantity_persons))
                                localStorage.setItem('m_package_service_type_id', this.service_type_id)
                                localStorage.setItem('m_package_typeclass_id', this.typeclass_id)
                            } else {
                                this.$toast.warning('No disponible', {
                                    position: 'top-right'
                                })
                                this.restore_filters_from_storage()
                            }
                            this.loadSearch = false
                        }).catch((e) => {
                        this.loadSearch = false
                        console.log(e)
                    })
                },
                restore_filters_from_storage () {
                    this.date = localStorage.getItem('m_package_date')
                    this.quantity_persons = JSON.parse(localStorage.getItem('m_package_quantity_persons'))
                    this.adults = this.quantity_persons[0].adults
                    this.child = this.quantity_persons[0].child
                    this.service_type_id = localStorage.getItem('m_package_service_type_id')
                    this.typeclass_id = localStorage.getItem('m_package_typeclass_id')
                },
                changeDate: function () {
                    // this.getPackages()
                    console.log('se ejecuto changeDate')
                },
                changeQuantityAdult: function (event) {
                    this.quantity_persons[0].adults = parseInt(event.target.value)
                    // this.getPackages()
                },
                changeQuantityChild: function (event) {
                    this.quantity_persons[0].child = parseInt(event.target.value)
                    this.quantity_persons[0].age_childs.splice(1, 4)
                    for (let i = 1; i < event.target.value; i++) {
                        this.quantity_persons[0].age_childs.splice((i + 1), 0, { age: 1 })
                    }
                    // this.getPackages()
                },
                changeServiceType: function (value) {
                    // this.getPackages()
                },
                changeTypeClass: function (value) {
                    // this.getPackages()
                },
                getServicesTypes: function () {
                    axios.get(baseExternalURL + 'api/service_types/selectBox?lang=' + localStorage.getItem('lang'))
                        .then((result) => {
                            let services_types = []
                            for (let i = 0; i < result.data.data.length; i++) {
                                if (result.data.data[i].code !== 'NA') {
                                    services_types.push(
                                        {
                                            id: result.data.data[i].id,
                                            code: result.data.data[i].code,
                                            abbreviation: result.data.data[i].abbreviation,
                                            translations: result.data.data[i].translations,
                                        }
                                    )
                                }
                            }
                            this.service_types = services_types
                        }).catch((e) => {
                        console.log(e)
                    })
                },
                willExport () {
                    let plan_rate_id = this.package_selected[1].plan_rates[0].id
                    let dateYear = moment(this.date, 'DD/MM/YYYY').year()
                    let title = (this.package_selected[1].translations[0].name !== '') ? this.package_selected[1].translations[0].name : 'TARIFAS'
                    axios.get(baseExternalURL + 'api/package/plan_rates/' + plan_rate_id + '/excel/' + this.service_type_id + '?lang=' + localStorage.getItem('lang') + '&title=' + title + '&client_id=' + localStorage.getItem('client_id') + '&year=' + dateYear,
                        {
                            responseType: 'blob',
                        }).then((response) => {
                        var fileURL = window.URL.createObjectURL(new Blob([response.data]))
                        var fileLink = document.createElement('a')
                        fileLink.href = fileURL
                        fileLink.setAttribute('download', title + '.xlsx')
                        document.body.appendChild(fileLink)
                        fileLink.click()
                    }).catch(() => {
                        this.$toast.error('Ocurrió un error interno', {
                            position: 'top-right'
                        })
                    })
                },
                formatDate: function (_date, charFrom, charTo, orientation) {
                    _date = _date.split(charFrom)
                    _date =
                        (orientation)
                            ? _date[2] + charTo + _date[1] + charTo + _date[0]
                            : _date[0] + charTo + _date[1] + charTo + _date[2]
                    return _date
                },
                showModalHotel: function (service) {
                    this.package_selected[1].services.forEach(function (valor, indice, array) {
                        valor.selected = false
                    })
                    service.selected = true
                    this.showCardChangeHotel = true
                    this.hotelToChange = service
                    this.searchHotel()
                },
                searchHotel: function () {
                    this.loadSearchHotel = true
                    axios.get('api/package/package_plan_rate_category_optional/' + this.package_plan_rate_category_id).then(response => {
                        this.moreHotels = response.data.data
                        this.loadSearchHotel = false
                    }).catch(error => {
                        this.$toast.error('Ocurrió un error interno', {
                            position: 'top-right'
                        })
                        console.log(error)
                        this.loadSearchHotel = false
                    })
                },
                showContentHotel (hotel) {
                    this.loading = true
                    this.hotelSelected = hotel
                    // this.moreHotels.forEach(h => {
                    //     if (hotel.id == h.hotel.id) {
                    //         h.viewContent = !(h.viewContent)
                    //     } else {
                    //         h.viewContent = false
                    //     }
                    // })
                    this.loading = false
                },
                addHotel () {
                    let service_rate_ids = []
                    if (this.hotelSelected.hasOwnProperty('service_rooms')) {
                        this.hotelSelected.service_rooms.forEach(h => {
                            service_rate_ids.push(h.rate_plan_room_id)
                        })
                    }

                    if (service_rate_ids.length > 0) {
                        let data = {
                            service_rooms: service_rate_ids,
                            package_service_id: this.hotelToChange.id,
                            hotel_id: this.hotelSelected.object_id,
                            date: (this.date === '') ? moment().format('YYYY-MM-DD') : moment(this.date, 'DD/MM/YYYY').format('YYYY-MM-DD')
                        }
                        this.changeHotelService(data)
                    } else {
                        this.$toast.warning('Debe seleccionar un hotel', {
                            position: 'top-right'
                        })
                    }
                },
                changeHotelService: function (data) {
                    this.loading = true
                    axios.post('api/packages/selected/change', data).then(response => {
                        this.showCardChangeHotel = false
                        this.$toast.success('Agregado correctamente', {
                            position: 'top-right'
                        })
                        this.getPackagesSelected()
                        $('#modal-hotel').hide()
                        this.loading = false
                    }).catch(error => {
                        this.$toast.error('Ocurrió un error interno', {
                            position: 'top-right'
                        })
                        console.log(error)
                        this.showCardChangeHotel = false
                        this.loading = false
                    })
                },
                initCarousel () {
                    $('.owl-carousel').owlCarousel({
                        margin: 10,
                        nav: true,
                        navText: ['', ''],
                        dots: false,
                        loop: false,
                        mouseDrag: false,
                        //touchDrag: false,
                        responsive: {
                            0: {
                                items: 1
                            },
                            320: {
                                items: 2
                            },
                            480: {
                                items: 3
                            },
                            768: {
                                items: 4
                            },
                            1024: {
                                items: 4
                            }
                        }
                    })
                    //console.log("Creando Carrusel!")
                },
                modalAlertToggle () {
                    $('#modalAlertaCotizacion').modal()
                },
                modalQuantityRooms () {
                    $('#modalQuantityRooms').modal()
                },
                edit () {
                    this.modalAlertToggle()
                    this.loading = true
                    axios.get(baseExternalURL + 'api/quote/existByUserStatus/2')
                        .then((result) => {
                            if (result.data.success) {
                                this.loading = false
                            } else {
                                this.putQuote()
                            }
                        }).catch((e) => {
                        console.log(e)
                        this.loading = false
                    })
                },
                putQuote () {
                    this.loading = true
                    axios.post(baseExternalURL + 'api/package/copy/quote',
                        {
                            name: this.package_selected[1].translations[0].name,
                            status: 2,
                            date_in: (this.date === '') ? moment().format('YYYY-MM-DD') : moment(this.date, 'DD/MM/YYYY').format('YYYY-MM-DD'),
                            client_id: localStorage.getItem('client_id')
                        }
                    ).then((result) => {
                        if (result.data.success) {
                            this.$toast.success('Cotización en modo edición', {
                                // override the global option
                                position: 'top-right'
                            })
                            this.goToQuotesFront()
                        } else {
                            console.log(result.data)
                            this.$toast.error('Error Interno', {
                                position: 'top-right'
                            })
                        }
                        this.loading = false
                    }).catch((e) => {
                        this.$toast.error('Error: ' + e, {
                            position: 'top-right'
                        })
                        this.loading = false
                    })
                },
                goToQuotesFront () {
                    window.location.href = '/packages/cotizacion'
                },
                replaceQuote () {
                    // this.loading = true
                    // axios.post(baseExternalURL + 'api/quote/' + this.quoteChoosen.id + '/replaceQuoteInFront', { client_id: localStorage.getItem('client_id') })
                    //   .then((result) => {
                    //     if (result.data.success) {
                    //       this.$toast.success('Cotización en modo edición', {
                    //         position: 'top-right'
                    //       })
                    //       this.goToQuotesFront()
                    //     } else {
                    //       this.$toast.error('Error Interno', {
                    //         position: 'top-right'
                    //       })
                    //       this.loading = false
                    //     }
                    //   }).catch((e) => {
                    //
                    //   this.$toast.error('Error: ' + e, {
                    //     // override the global option
                    //     position: 'top-right'
                    //   })
                    //   this.loading = false
                    // })
                },
                packageReservation: function () {
                    document.getElementById('close_modal_quantity_rooms').click()
                    this.$emit('blockPage', { 'message': 'Un momento por favor, estamos procesando su reserva' })
                    let data = {
                        package_name: this.package_selected[1].translations[0].name,
                        client_id: localStorage.getItem('client_id'),
                        quantity_rooms_sgl: this.quantity_rooms_sgl,
                        quantity_rooms_dbl: this.quantity_rooms_dbl,
                        quantity_rooms_tpl: this.quantity_rooms_tpl,
                        lang: localStorage.getItem('lang'),
                        passengers: this.$refs.modal_passengers.passengers,
                        reference: this.reference,
                        entity: 'Package',
                        object_id: this.package_selected[1].id
                    }
                    let totalPersons = this.getQuantityPersonsRooms()
                    if (totalPersons > 0) {
                        axios.post(baseExternalURL + 'services/reservations/package', data
                        ).then((result) => {
                            axios.post(baseExternalURL + 'services/hotels/reservation/add', result.data
                            ).then((result) => {
                                console.log(result.data.data)
                                let reserve_details = {
                                    name_package: result.data.data.customer_name,
                                    image_package: '',
                                    date_reserve: moment(this.date, 'DD/MM/YYYY').format('YYYY-MM-DD'),
                                    quantity_adults: this.adults,
                                    quantity_child: this.child,
                                    total: result.data.data.total,
                                    booking_code: result.data.data.booking_code,
                                    reference: this.reference
                                }
                                axios.post(baseExternalURL + 'api/packages/save/reserve_details', reserve_details).then((result) => {
                                    location.href = '/packages/details/reserve'
                                }).catch((e) => {
                                    this.$emit('unlockPage')
                                    this.$toast.error('Error: ' + e, {
                                        position: 'top-right'
                                    })
                                    this.loading = false
                                })
                                // this.$toast.success('Reserva Realizada ', {
                                //     position: 'top-right'
                                // })
                                this.$emit('unlockPage')
                            }).catch((e) => {
                                this.$emit('unlockPage')
                                this.$toast.error('Error: ' + e, {
                                    position: 'top-right'
                                })
                                this.loading = false
                            })
                        }).catch((e) => {
                            this.$emit('unlockPage')
                            this.$toast.error('Error: ' + e, {
                                position: 'top-right'
                            })
                            this.loading = false
                        })
                    } else {
                        this.$toast.error('La cantidad de habitaciones debe ser mayor a 0', {
                            position: 'top-right'
                        })
                        this.$emit('unlockPage')
                    }

                },
                validateQuantityRooms: function () {
                    let totalPersons = this.getQuantityPersonsRooms()
                    if (totalPersons > this.adults) {
                        this.$toast.success('Cantidad de Habitaciones no Permitida', {
                            // override the global option
                            position: 'top-right'
                        })

                        this.quantity_rooms_sgl = 0
                        this.quantity_rooms_dbl = 0
                        this.quantity_rooms_tpl = 0
                    }
                },
                getQuantityPersonsRooms: function () {
                    let quantityPersonsRoomsSGL = this.quantity_rooms_sgl
                    let quantityPersonsRoomsDBL = this.quantity_rooms_dbl * 2
                    let quantityPersonsRoomsTPL = this.quantity_rooms_tpl * 3

                    let totalPersons = parseInt(quantityPersonsRoomsSGL) + parseInt(quantityPersonsRoomsDBL) + parseInt(quantityPersonsRoomsTPL)

                    return totalPersons
                }
            },
            filters: {
                formatDate: function (_date) {
                    if (_date == undefined) {
                        // console.log('fecha no parseada: ' + _date)
                        return
                    }
                    _date = _date.split('-')
                    _date = _date[2] + '/' + _date[1] + '/' + _date[0]
                    return _date
                },

                formatPrice: function (price) {
                    return parseFloat(price).toFixed(2)
                }
            }
        })
    </script>
    <script>
        jQuery(document).ready(function () {

            //------------------------------------------------------
            // LAYOUT: SCROLLBARS
            //------------------------------------------------------

            $('.scrollbar-outer, .scrollbar-dynamic').scrollbar()

            //------------------------------------------------------
            // LAYOUT: TOOLTIP (?) // Conflicto BS vs JQuery UI
            //------------------------------------------------------

            $('[data-toggle="tooltip"]').tooltip()

            // //------------------------------------------------------
            // // FILTROS: CHECKBOX
            // //------------------------------------------------------
            //
            //alert("demo");

            var $checkbox = $('.filtros .form-check'),
                $chk

            var $aplicados = $('.filtros-aplicados > div')
            $aplicadosBtn = $('.filtros-aplicados aplicado')

            $checkbox.on('click', function (event) {
                $chk = $(this).find('input')
                $dropdown = $(this).parents('.dropdown')

                $aplicados.slideDown()
                if ($(this).hasClass('form-check-all')) {
                    //console.log("filtro: todos!");
                    $(this).siblings().find('input').prop('checked', false)
                    $(this).closest('.dropdown').find('.btn-icon').removeClass('active')

                } else {
                    //console.log("filtro: uno!");
                    $(this).siblings('.form-check-all').find('input').prop('checked', false)
                    $(this).closest('.dropdown').find('.btn-icon').addClass('active')
                    $('.filtros-aplicados').addClass('d-flex').removeClass('d-none')

                    if ($chk.is(':checked')) {
                        var str = $chk.siblings('span').text()
                        str = str.substring(0, str.length - 3)
                        filtroAplicar($chk.attr('id'), str)
                    } else {
                        $('.aplicado[data-id=\'' + $chk.attr('id') + '\']').remove()
                    }

                }
                checkAll(this)
            })

            function filtroAplicar (id, txt) {
                $aplicados.append('<span class="aplicado" data-id=' + id + '>' + txt + '</span>')
            }

            function checkAll (obj) {
                var $drop = $(obj).parents('.dropdown')
                var $dropChecked = $drop.find('input')

                if ($drop.find('input:checked').length == 0) {
                    $drop.find('.form-check-all input').prop('checked', true)
                    $drop.find('.btn-icon').removeClass('active')

                    $dropChecked.each(function () {
                        $('aplicado[data-id=\'' + $(this).attr('id') + '\']').remove()
                    })
                }
            }

            function checkGroup (id) {
                var $drop = $('#' + id).closest('.dropdown')
                var $dropChecked = $drop.find('input')

                if ($drop.find('input:checked').length == 0) {
                    $drop.find('.form-check-all input').prop('checked', true)
                    $drop.find('.btn-icon').removeClass('active')
                }
            }

            $(document).on('click', '.aplicado', function (event) {
                var id = $(this).attr('data-id')
                $('#' + id).prop('checked', false)
                $(this).remove()
                checkGroup(id)
            })

            //------------------------------------------------------
            // FILTROS: SEARCHBOX
            //------------------------------------------------------

            var $searchBlock = $('.dropdown-search'),
                $searchField = $('.dropdown-search .form-control'),
                $searchButton = $('.dropdown-search .btn-icon'),
                animTime = 500,
                timer //timeout

            function searchReset () {
                $searchBlock.removeClass('show').css('width', '240px')
                $searchButton.css({
                    'z-index': '-1',
                    'opacity': '0'
                })
            }

            $searchBlock.bind('click', function (event) {
                //console.log("Objeto: "+ $(event.target).attr("class"));
                if ($(event.target).children.length) {
                    if ($(event.target).is($searchField) && $searchBlock.css('width') != '400px') {
                        //console.log("search: animacion!");
                        $searchBlock.animate({ 'width': '400px' }, animTime)
                        $searchButton.css({ 'z-index': '1' }).animate({ 'opacity': '1' }, animTime)
                    }
                    if ($(event.target).is($searchButton)) {
                        //console.log("search: boton!");
                        searchReset()
                    }
                } else {
                    //console.log("search: reset!")
                    searchReset()
                }
            })

            //------------------------------------------------------
            // LAYOUT: EFFECTS / TRANSITIONS
            //------------------------------------------------------

            var $FX

            function stopFX ($obj) {
                //console.log("FX: detenidos!");
                $FX = $obj
                $FX.addClass('no-transition')
            }

            function restoreFX () {
                //console.log("FX: restaurados!");
                $FX.removeClass('no-transition')
            }

            //------------------------------------------------------
            // FILTROS: CAMBIAR VISTA
            //------------------------------------------------------

            var $btnVista = $('.vista[data-vista=\'normal\']'),
                $resultados = $('.resultado-programas .resultados')

            function vistas () {
                //console.log("vista: iniciando cambio!");
                stopFX($('.programa > div'))
                cambiarVista(function () {
                    setTimeout(restoreFX, 3)
                })
            }

            function cambiarVista (callback) {
                //console.log("vista: cambiando!");
                $resultados.attr('data-vista', $btnVista.attr('data-vista'))
                callback()
            }

            $('.vista').on('click', function (event) {
                $('.vista').removeClass('active')
                $(this).addClass('active')
                $btnVista = $(this)
                vistas()
                //demoLoader("Cambiando Vista", loadTime * 2, cambiarVista);
                //demoLoader("Cambiando Vista a " + $(this).attr("data-vista"), loadTime * 5, cambiarVista);
            })

            //------------------------------------------------------
            // DEMO -- LAYOUT: BACKDROPS (FUNCIONES)
            //------------------------------------------------------

            var $backdrop = $('.backdrop-banners'),
                loadTime = 250

            $('.navbar .dropdown').on('show.bs.dropdown', function () {
                if (!$('.spinner').is(':visible')) {
                    //console.log("menu: visible!");
                    showBackdrop()
                }
            })

            function clearTimer () {
                clearTimeout(timer)
            }

            function showBackdrop () {
                //console.log("backdrop: on!")
                if (!$('.spinner').is(':visible')) {
                    clearTimer()
                }
                $backdrop.css('display', 'block').animate({ 'opacity': '1' }, loadTime / 2)
            }

            function hideBackdrop () {
                //console.log("backdrop / loader: off!")
                $backdrop.animate({ 'opacity': '0' }, loadTime, function () {
                    $('#spinner-loader').remove()
                    $backdrop.css('display', 'none')
                    fixMenu()
                })
            }

            function showLoader (txt) {
                //console.log("loader: on!")
                if (txt === null) {
                    txt === ''
                }
                clearTimer()
                $('#spinner-loader').remove()
                $backdrop.css('display', 'block').animate({ 'opacity': '1' }, loadTime, function () {
                    $backdrop.prepend('<div id="spinner-loader"><div class="spinner"><span class="logo"></span></div><div class="spinner-text">' + txt + '<small>Por favor espere.</small></div></div>')
                })
            }

            function demoLoader (txt, time, efx) {
                showLoader(txt)
                timer = setTimeout(hideBackdrop, time)
                if (efx !== undefined) {
                    setTimeout(eval(efx), parseFloat(time) + parseFloat(loadTime))
                }
            }

            function hideMenu () {
                $('.dropdown-menu.show').prev().dropdown('toggle')
            }

            //------------------------------------------------------
            // DEMO -- NAVBAR: MENU / FIX
            //------------------------------------------------------

            function fixMenu () {
                $('.dropdown-menu').attr('style', '')
            }

            $('.dropdown').on('hidden.bs.dropdown', function (event) {
                fixMenu()
            })

            //------------------------------------------------------
            // DEMO -- NAVBAR: MENU / HIDE
            //------------------------------------------------------

            // --- Ocultar cuando no es boton (navbar)

            var $listener = $('.ctaction')

            $(document).on('click', function (event) {
                //console.log($(event.target).attr("class"));
                if ($listener !== $(event.target) && !$listener.find($(event.target)).length) {
                    if (!$('.spinner').is(':visible')) {
                        //console.log("backdrop: oculto!");
                        hideBackdrop()
                    }
                }
            })

            // --- Ocultar cuando se presiona enlace de un menu (navbar)

            $('.dropdown-menu a').on('click', function (target) {
                hideMenu()
                demoLoader('Cargando Módulo', loadTime * 10)
            })

            //------------------------------------------------------
            // DEMO -- NAVBAR: SEARCH
            //------------------------------------------------------

            var $searchDisplay = $('#searchDisplay'),
                $searchGroup = $('.search-group > div'),
                $searchIcon = $('.search-group button'),
                $searchInput = $('.search-group .form-control')

            function setFocus () {
                $searchInput.focus()
                showBackdrop()
            }

            $searchDisplay.on('click', function () {
                //console.log("navbar search: focus 1!");
                setTimeout(setFocus, 250)
            })

            $searchIcon.on('click', function () {
                //console.log("navbar search: focus 2!");
                setFocus()
            })

            $searchInput.on('blur', function () {
                //console.log("navbar search: focus 2!");
                $searchGroup.hide()
            })

            //------------------------------------------------------
            // DEMO -- NAVBAR: CLIENTES
            //------------------------------------------------------

            var $btnCliente = $('.cliente-menu.custom-combo')

            $btnCliente.on('click', function (event) {
                //console.log("cliente: cambiando!");
                if ($(event.target).is('.select-items > div')) {
                    demoLoader('Cambiando Cliente', loadTime * 20)
                }
            })

            //------------------------------------------------------
            // DEMO -- NAVBAR: IDIOMAS
            //------------------------------------------------------

            var $btnIdiomas = $('.select_lang.custom-combo')

            $btnIdiomas.on('click', function (event) {
                //console.log("idioma: cambiando!");
                if ($(event.target).is('.select-items > div')) {
                    demoLoader('Cambiando Idioma', loadTime * 20)
                }
            })

            //------------------------------------------------------
            // DEMO -- NAVBAR: CARRITO
            //------------------------------------------------------

            var $btnCarrito = $('.dropdown.cart button')

            $btnCarrito.on('click', function (event) {
                //console.log("carrito: visible!");
                if ($(event.target).is('a')) {
                    demoLoader('Cargando Carrito', loadTime * 10)
                }
            })

            //------------------------------------------------------
            // DEMO -- NAVBAR: PERFIL
            //------------------------------------------------------

            var $btnPerfil = $('.dropdown.user-menu button')

            $btnPerfil.on('click', function (event) {
                //console.log("perfil: visible!");
                if ($(event.target).is('a')) {
                    demoLoader('Cargando Carrito', loadTime * 10)
                }
            })

            //------------------------------------------------------
            // DEMO -- PROGRAMAS: BTN SELECCIONAR
            //------------------------------------------------------

            //$('.modal-extensiones').modal({ backdrop: 'static' });
            // var $btnSeleccionar = $('.btn-primary')
            //
            // $btnSeleccionar.on('click', function () {
            //   //console.log("loader: cargando!");
            //   //demoLoader("Cargando Programa", loadTime * 10);
            //   $(this).closest('.programa').addClass('active')
            //   $('.modal-extensiones').modal({ backdrop: 'static' })
            // })
            //
            // $('.modal-extensiones').on('hide.bs.modal', function (e) {
            //   $('.programa.active').removeClass('active')
            // })

            //------------------------------------------------------
            // DEMO -- PROGRAMAS: BTN DESCARGAR
            //------------------------------------------------------

            var $btnDescargar = $('.btn-descargar')

            $btnDescargar.on('click', function () {
                //console.log("loader: cargando!");
                callNotify($(this))
            })

            //------------------------------------------------------
            // LAYOUT: NOTIFY (!) // Personalizado
            //------------------------------------------------------

            function callNotify ($obj) {
                console.log('------')
                console.log('objeto: ' + $obj.attr('class'))
                console.log('data-title: ' + $obj.attr('data-title'))
                var titulo = $obj.attr('data-title'),
                    icono = $obj.attr('data-icon'),
                    mensaje = $obj.attr('data-info')

                if (titulo === undefined) {
                    titulo = $obj.text()
                }
                if (icono === undefined) {
                    icono = 'fas fa-download'
                }
                if (mensaje === undefined) {
                    mensaje = ''
                }

                console.log('titulo: ' + titulo)
                console.log('------')

                $.notify({
                    title: '<strong>' + titulo + '</strong>',
                    icon: icono,
                    message: mensaje,
                }, {
                    type: 'info',
                    animate: {
                        enter: 'animated fadeInUp',
                        exit: 'animated fadeOutRight'
                    },
                    placement: {
                        from: 'bottom',
                        align: 'left'
                    },
                    offset: 20,
                    spacing: 10,
                    z_index: 1031,
                })
            }

            $('div[data-notify=\'container\'] .close').on('click', function () {
                $(this).closest('div[data-notify=\'container\']').remove()
            })

        })

    </script>

@endsection
