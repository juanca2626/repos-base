@extends('layouts.app')
@section('content')
    <div class="shopping">
        <div class="container-fluid p-0">
            <div class="row">
                <div class="col">
                    <h3>
                        <span class="icon-shopping-bag mr-2"></span> {{ trans('cart_view.label.title_cart') }}
                        <span class="tag-counter">(@{{ cart_content.quantity_items}} <span
                                v-if="cart_content.quantity_items == 1">{{trans('cart_view.label.product')}}</span>
                    <span v-else>{{trans('cart_view.label.products')}}</span>)</span>
                    </h3>
                </div>
                <div class="col-auto">
                <span>
                    <a href="javascript:void(0)" class="btn btn-primary btn-car float-right" @click="back_page()">
                        <i class="fas fa-arrow-circle-left"></i> {{ trans('cart_view.label.back') }}</a>
                </span>
                </div>
            </div>
        </div>
        <main class="container-fluid bg-light p-4 my-5 cart-all">
            <div class="row m-0 my-3 align-items-start">
                <div class="col">
                    {{--Servicios--}}
                    <h4 class="subtitle my-0 text-uppercase" v-if="cart_content.services.length > 0">
                        @{{ cart_content.services.length }}
                        <span v-if="cart_content.services.length == 1">{{trans('cart_view.label.service_added')}}</span>
                        <span v-else>{{trans('cart_view.label.services_added')}}</span>
                    </h4>
                    <div class="services_item" v-for="(service,index) in cart_content.services">
                        <div class="blog-card">
                            <div class="col-auto pr-0 py-4" v-if="service.service.galleries.length > 0">
                                <img
                                    :src="changeSizeImageService(service.service.galleries[0])"
                                    class="photo" style="width: 200px; height: 200px; object-fit: cover;"
                                    :alt="service.service_name" />
                            </div>
                            <div class="col description p-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <h5 class="subtle"><span class="icon-calendar-confirm mr-1"
                                                            style="font-size: 1.5rem;"></span> @{{
                                        formatDate(service.date_from) }}</h5>
                                    <a class="trash" href="#" @click="cancelRates(service.cart_items_id)"><span
                                            class="icon-trash-2"></span></a>
                                </div>
                                <h2>@{{ service.service_name }}
                                    <span class="cod">[@{{ service.service.code }}]</span>
                                    <span class="ok" v-if="service.service.on_request == 0">OK</span>
                                    <span class="rq" v-if="service.service.on_request == 1">RQ</span>
                                </h2>
                                <p class="map-pin">
                                    <span class="icon-map-pin-in mr-1"></span>@{{
                                    service.service.origin.origin_display }} <span class="icon-map-pin-out mr-1"></span>
                                    @{{
                                    service.service.destiny.destiny_display }} </p>
                                {{--------------------Suplementos obligatorios-------------------------------------}}
                                <p class="mt-3 mr-5 text-supplements"
                                v-if="service.service.supplements.supplements.length > 0">
                                <span v-for="supplement in service.service.supplements.supplements">
                                <span class="icon-plus"></span>{{trans('service.label.include')}}: @{{supplement.name}}
                                    <button v-if="supplement.type === 'optional'" class="btn btn-danger ml-2"
                                            @click="will_remove_supplement_opt(service,supplement)">
                                        <span class="icon-trash-2"></span>
                                    </button><br>
                                </span><br>
                                </p>
                                {{--------------------Fin Suplementos obligatorios-------------------------------------}}

                                {{--------------------Componentes-------------------------------------}}
                                <div class="multi-services" v-if="service.service.components.length>0"
                                    :class="{'multiservice-removed':(component.removed)}"
                                    v-for="(component, c_i) in service.service.components">

                                    <div class="d-block" v-if="!(component.show_replace)">
                                        <span class="text-muted mr-1">[@{{ component.code }}]</span>
                                        <span v-if="component.descriptions.name !== null"
                                            v-html="component.descriptions.name"></span>
                                        <span v-else v-html="component.name"></span> <strong>(+ USD @{{
                                            getPrice(component.price_per_person) }})</strong>

                                        <button class="btn-multi ml-2" v-if="component.descriptions.itinerary.length>0"
                                                @click='component.collapsed=!(component.collapsed)'>
                                            <span class="fa fa-angle-right"
                                                :class="{'rotate-90': (component.collapsed)}"></span>
                                        </button>

                                        <button class="btn-multi ml-3" @click='component.show_replace=true'
                                                v-if="component.substitutes.length>0">
                                            <span class="icon-repeat"></span>
                                        </button>
                                        <span v-if="!(component.lock)">
                                            <button class="btn-multi ml-2" v-if="!(component.will_remove)"
                                                    @click="will_remove_multiservice(component)">
                                                <span class="icon-trash-2"></span>
                                            </button>
                                            <button class="btn-multi ml-2" v-if="component.will_remove"
                                                    @click="remove_multiservice(service, c_i)">
                                                <span class="icon-trash-2 icon-trash-remove"></span>
                                            </button>
                                        </span>
                                        <span v-if="component.lock">
                                            <button class="btn-multi ml-2">
                                                <span class="fa fa-lock"></span>
                                            </button>
                                        </span>
                                    </div>
                                    <div class="d-block mb-2" v-if="(component.show_replace)">
                                        <div class="d-flex justify-content-between">
                                            <label class="ml-2 mt-2 text-muted">Seleccionar el servicio a
                                                reemplazar:</label>
                                            <div>
                                                <button class="btn-multi ml-4"
                                                        @click="replace_component(component, service, c_i)">
                                                    <span class="icon-save"></span></button>
                                                <button class="btn-multi ml-2" @click='component.show_replace=false'>
                                                    <span class="icon-x"></span></button>
                                            </div>
                                        </div>

                                        <v-select :options="component.substitutes"
                                                v-model="component.substitute_selected"
                                                label="label"
                                                placeholder="Seleccionar servicio"
                                                class="form-control ml-2">
                                            <template slot="option" slot-scope="option">
                                                @{{ option.label }}
                                            </template>
                                        </v-select>

                                    </div>
                                    <transition name="fade">
                                        <div class="collapse-body" v-if="component.collapsed">
                                            <p class="mt-3" v-for="itinerary in component.descriptions.itinerary">
                                                <strong>{{trans('global.label.day')}} @{{ parseInt( itinerary.day ) +
                                                    parseInt( component.after_days ) }}: </strong> @{{
                                                itinerary.description }}
                                            </p>
                                        </div>
                                    </transition>
                                </div>
                                {{--------------------Fin Componentes---------------------------------}}

                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <label data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                            class="m-0" style="cursor: pointer;">
                                            <span class="icon-clock font-clock btn-clock"></span>
                                        </label>
                                        <div @click.stop=""
                                            class="dropdown-menu dropdown-menu__cotizacion dropdown-menu-right"
                                            style="overflow-y: scroll; z-index: 100; left: 50px!important; top: 150px!important; padding: 20px!important; height: 120px; min-height: 120px; max-height: 180px;">
                                            <div class="dropdown-menu_body">
                                                <div class="col-md-12 p-0">
                                                    <table class="table table-bordered"
                                                        v-if="service.service.schedules.length>0">
                                                        <thead class="thead-light text-center">
                                                        <tr>
                                                            <th class="th-table"></th>
                                                            <th class="th-table">{{ trans('global.label.monday') }}</th>
                                                            <th class="th-table">{{ trans('global.label.tuesday') }}</th>
                                                            <th class="th-table">{{ trans('global.label.wednesday') }}</th>
                                                            <th class="th-table">{{ trans('global.label.thursday') }}</th>
                                                            <th class="th-table">{{ trans('global.label.Friday') }}</th>
                                                            <th class="th-table">{{ trans('global.label.saturday') }}</th>
                                                            <th class="th-table">{{ trans('global.label.sunday') }}</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        <tr v-for="(horaries,indexParent) in service.service.schedules"
                                                            :key="indexParent"
                                                            class="tr-choose"
                                                            @click="change_schedule(service, indexParent)">
                                                            <th class="text-center">
                                                                <div class="col-md-12 font-weight-bold">
                                                                    Horario<br>
                                                                    #@{{indexParent + 1}}
                                                                </div>
                                                            </th>
                                                            <td v-for="(horary,index) in horaries" :key="index"
                                                                :class="{'background-grays':!horary.day_choosed,
                                                                            'background-success':horary.day_choosed && horary.ini !== null,
                                                                            'background-warning':horary.day_choosed && horary.ini == null }">
                                                                <div v-if="horary.ini !== null">
                                                                    <div class="col-md-12 input-group-sm"
                                                                        style="padding:0;">
                                                                        @{{ horary.ini | format_hour }}
                                                                    </div>
                                                                    <div class="col-md-12 input-group-sm"
                                                                        style="padding:0;">
                                                                        @{{ horary.fin | format_hour }}
                                                                    </div>
                                                                </div>
                                                                <div v-else>
                                                                    <div class="text-center">
                                                                        <i class="fa fa-times text-danger"></i>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                    <span v-else class="alert alert-warning"> <i
                                                            class="fa fa-info-circle"></i> Ningún horario para mostrar</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-if="service.service.service_type.id === 2">
                                            <vue-timepicker ordersname="reservation_time_control"
                                                            @input="save_change_schedule(service.cart_items_id[0], service.service.reservation_time)"
                                                            v-model="service.service.reservation_time"
                                                            format="HH:mm"></vue-timepicker>
                                        </div>
                                        <div v-else>
                                            @{{ service.service.reservation_time | format_hour }}
                                        </div>
                                    </div>
                                    <span class="mr-5"><span class="icon-users"></span> @{{ service.service.quantity_adult }} {{ trans('hotel.label.adults') }}</span>
                                    <span class="mr-5" v-if="service.service.quantity_child > 0"><span
                                            class="icon-smile"></span> @{{ service.service.quantity_child }} {{ trans('hotel.label.child') }}</span>
                                    <span class="price_"><span class="icon-dollar-sign1 mr-2"></span>@{{ getPrice(service.total_service) }} <span
                                    v-if="client && client.commission_status == 1 && parseFloat(client.commission) > 0 && user_type_id == 4"
                                    class="badge badge-warning ml-2">
                                    {{trans('global.label.with_commission') }}
                                </span></span>



                                </div>

                                <hr v-if="service.service.supplements.optional_supplements.length > 0">

                                <div class="row" v-if="service.service.supplements.optional_supplements.length > 0">
                                    <div class="col-12 d-flex justify-content-between">
                                        <div class="read-more">
                                            <a :href="'#supplements_'+index" data-toggle="collapse" role="button"
                                            aria-expanded="false" aria-controls="collapseExample2">
                                                <span class="icon-plus-circle mr-2"></span>Añadir suplementos</a>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="collapse" :id="'supplements_'+index">
                                            <div class="card card-body">
                                                <div class="form-row align-items-center"
                                                    v-for="(supplement_optional,index_sup) in service.service.supplements.optional_supplements">
                                                    <div class="col-8">
                                                        <div class="col-12">
                                                            <input type="checkbox" class="form-check-input"
                                                                :id="'sup_'+index"
                                                                v-model="supplement_optional.selected"
                                                                @change="selectedSupplement(supplement_optional,index,index_sup)">
                                                            <label class="form-check-label" :for="'sup_'+index"
                                                                style="font-size: 12px;margin-left: 5px">
                                                                @{{supplement_optional.name}}</label>

                                                            <span v-for="day_charge in supplement_optional.days.charge"
                                                                class="mr-2 float-left"
                                                                style="font-size: 12px;margin-left: 5px">
                                                                <i class="fas fa-calendar-check ml-1"></i> @{{ day_charge.day }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="col-4">
                                                        <div class="col-md-12">
                                                            <div class="row">
                                                                <div class="col-md-12 p-0 mb-3">
                                                                    <span class="ml-5"> USD $ @{{ supplement_optional.rate.price_per_adult }} x
                                                                        <i class="fas fa-male"></i>
                                                                    </span>
                                                                </div>
                                                                <div class="col-md-12 p-0"
                                                                    v-if="supplement_optional.rate.price_per_child > 0">
                                                                    <span class="ml-5"> USD $ @{{ supplement_optional.rate.price_per_child }} x
                                                                        <i class="fas fa-child"></i>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-12" v-show="supplement_optional.selected">
                                                        <div class="row">
                                                            <div class="col-md-3">
                                                                <label class="ml-2 text-muted"
                                                                    style="font-size: 12px;margin-left: 5px"
                                                                    for=""> {{trans('service.label.for_how_many_adults')}}
                                                                    :</label>
                                                                <select class="form-control"
                                                                        @change="filterSupplement(supplement_optional,index,index_sup)"
                                                                        v-model="parameters_supplements[index][index_sup].adults">
                                                                    <option
                                                                        v-for="num_adults in service.quantity_adults"
                                                                        :value="num_adults"
                                                                        :disabled="supplement_optional.charge_all_pax">
                                                                        @{{ num_adults }}
                                                                    </option>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-3"
                                                                v-if="service.quantity_child > 0">
                                                                <label class="ml-2 text-muted"
                                                                    style="font-size: 12px;margin-left: 5px"
                                                                    for=""> {{trans('service.label.for_how_many_children')}}
                                                                    :</label>
                                                                <select class="form-control"
                                                                        @change="filterSupplement(supplement_optional,index)"
                                                                        v-model="parameters_supplements[index][index_sup].child">
                                                                    <option
                                                                        v-for="(num_child,index) in (service.quantity_child + 1)"
                                                                        :value="index"
                                                                        :disabled="supplement_optional.charge_all_pax">
                                                                        @{{
                                                                        index }}
                                                                    </option>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-6 date"
                                                                v-show="supplement_optional.days.charge.length > 0 || supplement_optional.days.not_charge.length > 0">
                                                                <label class="ml-2 text-muted"
                                                                    style="font-size: 12px;margin-left: 5px">
                                                                    {{trans('service.label.for_what_dates_do_you_want_it')}}
                                                                    :
                                                                </label>
                                                                <v-select multiple
                                                                        v-model="parameters_supplements[index][index_sup].dates"
                                                                        label="day"
                                                                        :reduce="supplement_optional => supplement_optional.day"
                                                                        @input="filterSupplement(supplement_optional,index,index_sup)"
                                                                        :options="supplement_optional.days.not_charge"
                                                                        :disabled="supplement_optional.days.not_charge.length === 0"
                                                                        placeholder="Selecciona las fechas"
                                                                        class="form-control"/>
                                                            </div>
                                                            <div class="col-md-2">
                                                                <button class="btn btn-danger ml-2"
                                                                        @click="will_add_supplement_opt(service,index,index_sup,supplement_optional)">
                                                                    <i class="fas fa-plus"></i> Agregar
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    {{--End Servicios--}}

                    <div class="my-3" v-if="cart_content.services.length > 0 && cart_content.hotels.length > 0">
                    </div>

                    {{--Hoteles--}}
                    <h4 class="subtitle my-0 text-uppercase" v-if="cart_content.hotels.length > 0">
                        @{{ cart_content.hotels.length }}
                        <span v-if="cart_content.hotels.length == 1">{{trans('cart_view.label.hotel_added')}}</span>
                        <span v-else>{{trans('cart_view.label.hotels_added')}}</span>
                    </h4>
                    <div class="hoteles_item" v-for="(hotel,index) in cart_content.hotels">
                        <div class="blog-card">
                            <div class="col-auto py-4 pr-0" v-if="hotel.hotel.galleries.length > 0">
                                <img
                                    :src="changeSizeImageHotel(hotel.hotel.galleries[0])"
                                    class="photo" style="width: 200px; height: 200px; object-fit: cover;"
                                    :alt="hotel.hotel.name" />
                            </div>
                            <div class="col description p-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <h5 class="subtle d-flex align-items-start">
                                        <span class="icon-calendar-confirm mr-1" style="font-size: 1.5rem;"></span>
                                        @{{ formatDate(hotel.date_from) }}
                                        <span class="icon-arrow-right"></span>
                                        @{{ formatDate(hotel.date_to) }}
                                    </h5>
                                    <div class="read-more ml-5">
                                        @include('cart_details.hotel_upselling')
                                    </div>
                                </div>
                                <h2 class="d-flex align-items-center">
                                    @{{ hotel.hotel.name }}
                                    <div class="star">
                                        <i class="fas fa-star" style="color: #f3e22b;"
                                        v-for="n in parseInt(hotel.hotel.category)"></i>
                                    </div>
                                </h2>
                                <div class="d-flex align-items-center justify-content-between mb-3 mt-3">
                                    <div class=""><span class="icon-bed-double mr-2"></span>
                                        @{{ hotel.rooms.length }} <span
                                            v-if="hotel.rooms.length == 1">{{trans('reservations.label.room')}}</span>
                                        <span v-else>{{trans('reservations.label.rooms')}}</span>
                                    </div>
                                    <div class=""><span class="icon-users mr-2"></span>
                                        @{{ countTotalPeople(hotel.rooms) }} {{trans('global.label.people')}}
                                    </div>
                                </div>
                                <div class="mini-card" v-for="(room,index_room) in hotel.rooms">
                                    <div class="text-right"><a class="trash" href="#"
                                                            @click="cancelRates(room.cart_items_id)"><span
                                                class="icon-trash-2"></span></a>
                                    </div>
                                    <h2 style="color: #4a90e2;">
                                        @{{ room.room_name }} <span class="text mr-2">@{{ room.rate.name }}</span>
                                        <span class="ok" v-if="room.rate.onRequest ==1">OK</span>
                                        <span class="rq" v-if="room.rate.onRequest ==0">RQ</span>
                                    </h2>
                                    <p style="font-size: 1.15rem;">@{{ room.rate.meal_name }}</p>
                                    <p style="font-size: 1.15rem;">@{{ room.rate.political.cancellation.name }}</p>

                                    <div class="read-more mt-4 border-top pt-3">
                                    <span class="mr-5">
                                        <span class="icon-users"></span> @{{ room.quantity_adults }} {{ trans('hotel.label.adults') }}
                                    </span>
                                        <span class="mr-5" v-if="room.quantity_child > 0">
                                        <span class="icon-smile"></span> @{{ room.quantity_child }} {{ trans('hotel.label.child') }}
                                    </span>
                                        <span class="price_">
                                        $ @{{ getPrice(parseFloat(room.total_room - room.rate.supplements.total_amount - room.rate.total_taxes_and_services).toFixed(2))}}
                                    </span>
                                     <span
                                    v-if="client && client.commission_status == 1 && parseFloat(client.commission) > 0 && user_type_id == 4"
                                    class="badge badge-warning ml-2">
                                    {{trans('global.label.with_commission') }}
                                </span>
                                    </div>

                                </div>
                                <div class="row">
                                    <div class="col-12 d-flex justify-content-between">
                                        <div class="row read-more" style="margin-left: 0px;">
                                            <a style="float: left" :href="'#notas-'+index" data-toggle="collapse"
                                            role="button"
                                            aria-expanded="false" aria-controls="collapsenote">
                                                <span class="icon-plus-circle mr-2"></span>
                                                {{trans('reservations.label.include_notes')}}
                                            </a>

                                            <label style="float: left; margin-left: 50px"
                                                for="is_modification"
                                                @click="toggle_modification(hotel)">
                                                <input type="checkbox" id="is_modification" name="is_modification"
                                                    v-model="hotel.is_modification">
                                                {{trans('cart_view.label.it_is_a_modification')}}
                                            </label>

                                            <label style="float: left" class="alert alert-warning"
                                                v-if="hotel.is_modification">
                                                {{trans('cart_view.label.please_fill_in_the_notes_and_confirmation')}}
                                            </label>

                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="collapse" :id="'notas-'+index">
                                            <textarea placeholder="{{trans('global.label.include_notes')}}"
                                                    v-on:change="setNotesHotel(hotel)"
                                                    v-model="hotel.notes_carrito" cols="5"
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
                    <div class="summary subtitle my-0">
                        <div class="card card-cart">
                            <h2 class="title-card">{{trans('cart_view.label.my_products')}}</h2>
                            <div>
                                <div class="d-flex justify-content-between mb-2"
                                    v-for="(service,index) in cart_content.services">
                                    <div class="text-title">
                                        <span class="icon-check mr-1"></span> @{{ service.service_name }} (@{{
                                        service.service.quantity_adult + service.service.quantity_child }} personas)

                                        <span v-for="component in service.service.components"
                                            :class="{'multiservice-removed':(component.removed)}">
                                            <br>
                                            + @{{ component.descriptions.name }}
                                        </span>
                                    </div>
                                    <div>USD $.@{{ getPrice(service.total_service) }}</div>
                                </div>
                                <div class="d-flex justify-content-between" v-for="(hotel,index) in cart_content.hotels">
                                    <div class="text-title"><span class="icon-check mr-1"></span>@{{ hotel.hotel.name }}
                                        (@{{ countTotalPeople(hotel.rooms) }} <span
                                            v-if="countTotalPeople(hotel.rooms) == 1">{{trans('global.label.person')}}</span>
                                        <span v-else>{{trans('global.label.people')}}</span>)
                                    </div>
                                    <div>USD $.@{{ getPrice(hotel.total_hotel) }}</div>

                                </div>

                                <hr>
                                <div class="d-flex justify-content-between total mt-4 mb-5">
                                    <div>{{ trans('cart_view.label.total_to_pay') }}</div>
                                    <div>
                                    <span
                                        v-if="client && client.commission_status == 1 && parseFloat(client.commission) > 0 && user_type_id == 4"
                                        class="badge badge-warning ml-2">
                                        {{trans('global.label.with_commission') }}
                                    </span>
                                        USD $.@{{ getPrice(cart_content.total_cart) }}
                                    </div>
                                </div>
                            </div>
                            <div class="my-3 cart d-flex" style="padding-left: 0px; gap: 10px;">
                                <a class="btn btn-primary w-50" href="javascript:void(0)" @click="clearCart()"
                                    v-bind:disabled="cart_content.quantity_items === 0">
                                    <i class="far fa-trash-alt"></i> {{ trans('global.label.clear_cart') }}
                                </a>
                                <span id="disabled-wrapper" class="d-block w-50" tabindex="0">
                                    <button type="button" class="btn btn-primary btn-car"
                                        style="width: 100%!important;" @click="goToBooking()"
                                        v-bind:disabled="cart_content.quantity_items === 0 || isDisabledReservation">
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
@endsection
@section('css')
    <style>
        .schedules-icon {
            margin-top: -63px;
            position: absolute;
            margin-left: -8px;
        }

        .schedules-hours {
            position: absolute;
            margin-top: 60px;
        }

        .background-grays {
            background: #f6f7f8;
            font-size: 13px !important;
        }

        .background-success {
            background: #d4fbff !important;
            color: #12bcc4;
            font-weight: 700;
            font-size: 12px;
        }

        .background-warning {
            background: #fffbdf;
        }

        .tr-choose:hover {
            opacity: 0.8;
            cursor: pointer;
        }

        .alert-warning {
            font-size: 13px !important;
            margin: 2rem !important;
        }

        .time-locked {
            background: #eee;
            padding: 0.5rem 1rem !important;
            color: #848080;
            font-size: 13.5px !important;
        }

        .danger-date {
            background-color: #eb5757 !important;
            padding: .8rem 1.5rem !important;
        }

        .font-clock {
            border-radius: 0.5rem;
            padding: 0.5rem;
            color: #04b5aa;
            background: #04b5aa29;
            border: 2px solid #c7e6e4;
        }

        .vue__time-picker input.display-time {
            border-radius: 0.5rem !important;
            padding: 0.2rem 2rem;
            height: 3.2rem !important;
        }

        .vue__time-picker .dropdown ul li:not([disabled]).active,
        .vue__time-picker .dropdown ul li:not([disabled]).active:hover {
            background: #d4fbff;
            color: #12bcc4;
        }

    </style>
@endsection

@section('js')
    <script>
        new Vue({
            el: '#app',
            data: {
                blockPage: false,
                loading_cart: false,
                msg: "{{ trans('hotel.label.please_loading') }}",
                cart_content: {
                    hotels: [],
                    services: [],
                    total_cart: 0,
                    quantity_items: 0
                },
                up_selling: [],
                parameters_supplements: [],
                baseURL: window.baseURL,
                baseExternalURL: window.baseExternalURL,
                block_change_schedule: false,
                is_modification: false,
                client : {},
                user_type_id: localStorage.getItem("user_type_id"),
            },
            created: function () {
                this.getCartContent()
                this.client_id = localStorage.getItem('client_id')
                if (this.client_id) {
                    this.getClient();
                }
            },
            mounted() {
            },
            computed: {
                isDisabledReservation: function () {
                    const response = parseInt(localStorage.getItem('client_disable_reservation') ?? 0);
                    return (response === 1);
                },
            },
            methods: {
                goToBooking() {
                    if(this.isDisabledReservation)
                    {
                        return false
                    }

                    if (this.cart_content.quantity_items > 0) {
                        window.location = baseURL + 'reservations/personal-data'
                    }
                },
                toggle_modification(hotel) {
                    hotel.is_modification = !(hotel.is_modification)
                    setTimeout(function () {
                        localStorage.setItem('is_modification_hotel_carrito_' + hotel.hotel_id, hotel.is_modification)
                    }, 100)
                },
                save_change_schedule(cart_item_id, hour_in) {
                    try {

                        if (hour_in && typeof hour_in === 'object') {
                            let mm_ = (hour_in.mm) ? hour_in.mm : '00'
                            hour_in = hour_in.HH + ':' + mm_
                        }

                        let data = {
                            cart_item_id: cart_item_id,
                            reservation_time: hour_in,
                        }

                        // GUARDAR EN CART
                        axios.put(
                            baseURL + 'cart/service/reservation_time', data).then((result) => {
                            if (result.data.success) {
                                this.cart_content.services.forEach((s) => {
                                    if (s.cart_items_id[0] === cart_item_id) {
                                        s.cart_items_id[0] = result.data.update.rowId
                                        s.service.reservation_time = hour_in
                                        s.service.schedules.forEach((sch_array) => {
                                            sch_array.forEach((sch) => {
                                                sch.day_choosed = false
                                            })

                                            if (s.weekday === 0) {
                                                if (sch_array[6].ini !== null) {
                                                    if (sch_array[6].ini.substr(0, 5) === hour_in.substr(0, 5)) {
                                                        sch_array[6].day_choosed = true
                                                    }
                                                }
                                            } else {
                                                if (sch_array[s.weekday - 1].ini !== null) {
                                                    if (sch_array[s.weekday - 1].ini.substr(0, 5) === hour_in.substr(0, 5)) {
                                                        sch_array[s.weekday - 1].day_choosed = true
                                                    }
                                                }
                                            }

                                        })
                                    }
                                })
                                // this.getCartContent()
                            }
                            this.block_change_schedule = false
                        }).catch((e) => {
                            console.log(e)
                            this.block_change_schedule = false
                        })
                    } catch (error) {
                        console.error('Error en la función save_change_schedule:', error);
                        this.block_change_schedule = false;
                    }
                },
                change_schedule(service, schedule_index) {

                    let hour_in_ = "00:00"

                    service.service.schedules[schedule_index].forEach((sch) => {
                        if (sch.code === service.weekday_name) {
                            hour_in_ = sch.ini
                        }
                    })

                    if (hour_in_ === service.service.reservation_time) {
                        console.log('ningún cambio por hacer')
                        return
                    }
                    if (this.block_change_schedule) {
                        console.log('ya en ejecución, espere por favor')
                        return
                    }
                    this.block_change_schedule = true

                    this.save_change_schedule(service.cart_items_id[0], hour_in_)

                },
                getNotesHotel: function (_hotel) {
                    let _notes = localStorage.getItem('notes_hotel_carrito_' + _hotel.hotel_id)
                    return ((_notes != null && _notes != '') ? _notes : '')
                },
                getIsModificationHotel: function (_hotel) {
                    let _is_modification = localStorage.getItem('is_modification_hotel_carrito_' + _hotel.hotel_id)
                    let response = ((_is_modification != null && _is_modification != '' && _is_modification !== "false") ? 1 : 0)
                    return response
                },
                setNotesHotel: function (_hotel) {
                    setTimeout(function () {
                        localStorage.setItem('notes_hotel_carrito_' + _hotel.hotel_id, _hotel.notes_carrito)
                    }, 100)
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
                replace_component(component, service, c_i) {
                    let data = {
                        client_id: this.client_id,
                        service_id: component.substitute_selected.service_id
                    }
                    axios.post('api/services/component/' + component.component_id + '/client', data).then((result) => {

                            component.show_replace = false
                            if (result.data.success) {
                                axios.put(
                                    baseURL + 'cart/multiservice/substitute',
                                    {
                                        component_index: c_i,
                                        substitute_service_id: component.substitute_selected.service_id,
                                        cart_item_id: service.cart_items_id[0],
                                    }).then((result) => {
                                    if (result.data.success) {
                                        this.getCartContent()
                                    }
                                }).catch((e) => {
                                    console.log(e)
                                })
                            } else {
                                console.log('error 1')
                            }
                        }
                    ).catch((e) => {
                        console.log(e)
                    })

                },
                remove_multiservice(service, c_i) {

                    axios.put(
                        baseURL + 'cart/multiservice/removed',
                        {
                            component_index: c_i,
                            cart_item_id: service.cart_items_id[0],
                            removed: !(service.service.components[c_i].removed)
                        }).then((result) => {
                        if (result.data.success) {
                            this.getCartContent()
                        }
                    }).catch((e) => {
                        console.log(e)
                    })
                },
                will_remove_multiservice(component) {
                    component.will_remove = true
                    setTimeout(() => {
                        component.will_remove = false
                    }, 5000)
                },
                showPriceAlternatives: function (hotel, price_my_selection) {
                    let price = 0
                    if (parseFloat(hotel.price) > parseFloat(price_my_selection)) {
                        price = (parseFloat(hotel.price).toFixed(2) - parseFloat(price_my_selection).toFixed(2)).toFixed(2)

                        price += ' +'
                    }
                    if (parseFloat(hotel.price) < parseFloat(price_my_selection)) {
                        price = (parseFloat(price_my_selection).toFixed(2) - parseFloat(hotel.price).toFixed(2)).toFixed(2)

                        price += ' -'
                    }
                    return price
                },
                showNameRooms: function (hotel) {
                    if (hotel.best_options.legth > 0) {
                        let name_rooms = ''
                        for (let i = 0; i < hotel.best_options.rooms.length; i++) {
                            if (i == 0) {

                                name_rooms += hotel.best_options.rooms[i].name + ' '
                            } else {
                                name_rooms += ' + ' + hotel.best_options.rooms[i].name + ' '
                            }
                        }
                        return name_rooms
                    }
                },
                addCartSupplementsOptionalSelected: function (cart_item_id, supplements_optional, quantity_adult, quantity_child) {
                    axios.post(baseURL + 'cart/update/item', {
                        quantity_adult: quantity_adult,
                        quantity_child: quantity_child,
                        cart_item_id: cart_item_id,
                        supplements_optional: supplements_optional
                    })
                        .then((result) => {
                            this.updateMenu()
                            this.getCartContent()

                        }).catch((e) => {
                        console.log(e)
                    })
                },
                transformDate: function (date) {
                    let array = date.split('/')
                    return '' + array[2] + '-' + array[1] + '-' + array[0]
                },
                addEnabledDate: function (date, supplement, index) {
                    delete supplement.options.daysOfWeekDisabled
                    supplement.options.enabledDates.push(date)
                    supplement.supplement_dates_selected.splice(index, 1)
                    supplement.key += 1
                },
                formatDate: function (starDate) {
                    return moment(starDate).format('ddd D MMM')
                },
                format_date: function (_date, charFrom, charTo, orientation) {
                    _date = _date.split(charFrom)
                    _date =
                        (orientation)
                            ? _date[2] + charTo + _date[1] + charTo + _date[0]
                            : _date[0] + charTo + _date[1] + charTo + _date[2]
                    return _date
                },
                updateMenu: function () {
                    this.$emit('updateMenu')
                },
                getCartContent: function () {
                    axios.get(baseURL + 'cart_details/service')
                        .then((result) => {
                            if (result.data.success) {
                                this.cart_content = []
                                result.data.cart.total_cart = parseFloat(result.data.cart.total_cart.replace(/[^\d\.\-eE+]/g, ""))
                                result.data.cart.services.forEach((s) => {
                                    // "2022-02-09"
                                    let date_in_ = this.format_date(s.date_from, '-', '/', 0)
                                    // 2022/04/13
                                    s.weekday = moment(date_in_).weekday()
                                    // console.log(weekday_) // 3 miercoles
                                    s.weekday_name = "sunday"
                                    switch (s.weekday) {
                                        case 1:
                                            s.weekday_name = "monday"
                                            break;
                                        case 2:
                                            s.weekday_name = "tuesday"
                                            break;
                                        case 3:
                                            s.weekday_name = "wednesday"
                                            break;
                                        case 4:
                                            s.weekday_name = "thursday"
                                            break;
                                        case 5:
                                            s.weekday_name = "friday"
                                            break;
                                        case 6:
                                            s.weekday_name = "saturday"
                                            break;
                                    }

                                    s.service.schedules = this.format_schedule_show(s.service.reservation_time, s.weekday, s.service.schedules)
                                    if (s.service.reservation_time === "" || s.service.reservation_time === null) {
                                        if (s.service.schedules && s.service.schedules.length > 0) {
                                            s.service.schedules[0].forEach((sch) => {
                                                if (sch.code === s.weekday_name) {
                                                    s.service.reservation_time = sch.ini
                                                    this.save_change_schedule(s.cart_items_id[0], sch.ini)
                                                }
                                            })
                                        }
                                    } else {
                                        this.save_change_schedule(s.cart_items_id[0], s.service.reservation_time)
                                    }

                                    s.service.components.forEach((c) => {
                                        c.show_replace = false
                                        c.will_remove = false
                                        c.collapsed = false
                                        c.substitute_selected = ''
                                        if (!(c.removed)) {
                                            s.total_service += c.total_amount
                                            result.data.cart.total_cart += c.total_amount
                                        }
                                    })
                                })

                                result.data.cart.hotels.forEach((s, k) => {
                                    result.data.cart.hotels[k].is_modification = this.getIsModificationHotel(s)
                                    result.data.cart.hotels[k].notes_carrito = this.getNotesHotel(s)
                                })

                                let cart = result.data.cart
                                let parameters_supplements = []
                                for (let s = 0; s < cart.services.length; s++) {
                                    parameters_supplements[s] = []
                                    if (cart.services[s].service.supplements.optional_supplements.length > 0) {
                                        for (let sp = 0; sp < cart.services[s].service.supplements.optional_supplements.length; sp++) {
                                            cart.services[s].service.supplements.optional_supplements[sp].selected = false
                                            parameters_supplements[s].push({
                                                adults: cart.services[s].service.supplements.optional_supplements[sp].params.adults,
                                                child: cart.services[s].service.supplements.optional_supplements[sp].params.child,
                                                dates: cart.services[s].service.supplements.optional_supplements[sp].params.dates,
                                                prices: {},
                                            })
                                        }
                                    } else {
                                        parameters_supplements[s] = [{
                                            adults: cart.services[s].quantity_adults,
                                            child: cart.services[s].quantity_child,
                                            dates: [],
                                            prices: {},
                                        }]
                                    }
                                }
                                this.parameters_supplements = parameters_supplements
                                this.cart_content = cart
                                console.log(this.cart_content)
                            }
                            this.blockPage = false
                        }).catch((e) => {
                        this.blockPage = false
                        console.log(e)
                    })
                },

                format_schedule_show: function (reservation_time, weekday_, schedules) {
                    let arrayNew = []
                    schedules.forEach((schedule, index) => {
                        // day_choosed
                        let ini = schedule.services_schedule_detail[0]
                        let fin = schedule.services_schedule_detail[1]

                        if (!(ini.monday === null && fin.monday === null &&
                            ini.tuesday === null && fin.tuesday === null &&
                            ini.wednesday === null && fin.wednesday === null &&
                            ini.thursday === null && fin.thursday === null &&
                            ini.friday === null && fin.friday === null &&
                            ini.saturday === null && fin.saturday === null &&
                            ini.sunday === null && fin.sunday === null)) {
                            arrayNew.push(
                                [
                                    {
                                        id_parent: ini.service_schedule_id,
                                        id_ini: ini.id,
                                        id_fin: fin.id,
                                        code: 'monday',
                                        ini: ini.monday,
                                        fin: fin.monday,
                                        day_choosed: (weekday_ === 1 && reservation_time === ini.monday)
                                    },
                                    {
                                        id_parent: ini.service_schedule_id,
                                        id_ini: ini.id,
                                        id_fin: fin.id,
                                        code: 'tuesday',
                                        ini: ini.tuesday,
                                        fin: fin.tuesday,
                                        day_choosed: (weekday_ === 2 && reservation_time === ini.tuesday)
                                    },
                                    {
                                        id_parent: ini.service_schedule_id,
                                        id_ini: ini.id,
                                        id_fin: fin.id,
                                        code: 'wednesday',
                                        ini: ini.wednesday,
                                        fin: fin.wednesday,
                                        day_choosed: (weekday_ === 3 && reservation_time === ini.wednesday)
                                    },
                                    {
                                        id_parent: ini.service_schedule_id,
                                        id_ini: ini.id,
                                        id_fin: fin.id,
                                        code: 'thursday',
                                        ini: ini.thursday,
                                        fin: fin.thursday,
                                        day_choosed: (weekday_ === 4 && reservation_time === ini.thursday)
                                    },
                                    {
                                        id_parent: ini.service_schedule_id,
                                        id_ini: ini.id,
                                        id_fin: fin.id,
                                        code: 'friday',
                                        ini: ini.friday,
                                        fin: fin.friday,
                                        day_choosed: (weekday_ === 5 && reservation_time === ini.friday)
                                    },
                                    {
                                        id_parent: ini.service_schedule_id,
                                        id_ini: ini.id,
                                        id_fin: fin.id,
                                        code: 'saturday',
                                        ini: ini.saturday,
                                        fin: fin.saturday,
                                        day_choosed: (weekday_ === 6 && reservation_time === ini.saturday)
                                    },
                                    {
                                        id_parent: ini.service_schedule_id,
                                        id_ini: ini.id,
                                        id_fin: fin.id,
                                        code: 'sunday',
                                        ini: ini.sunday,
                                        fin: fin.sunday,
                                        day_choosed: (weekday_ === 0 && reservation_time === ini.sunday)
                                    },
                                ]
                            )
                        }
                    })
                    return arrayNew
                },
                cancelRates: function (cart_items_id) {
                    this.blockPage = true
                    this.loading_cart = true
                    axios.post(
                        baseURL + 'cart/cancel/rates', {
                            cart_items_id: cart_items_id
                        }
                    )
                        .then((result) => {
                            this.loading_cart = false
                            if (result.data.success) {
                                this.$toast.success("{{ trans('cart_view.label.deleted_item') }}", {
                                    position: 'top-right',
                                })
                                this.updateMenu()
                                this.blockPage = false
                                this.getCartContent()
                            } else {
                                this.$toast.error(result.data.data, {
                                    position: 'top-right',
                                })
                                this.getCartContent()
                            }
                        }).catch((e) => {
                        this.blockPage = false
                        this.loading_cart = false
                        console.log(e)
                    })
                },
                showAlterna: function (index_hotel, token_search_frontend, token_search, hotel_id, option) {

                    let container_hotel = document.getElementById('container_hotel' + index_hotel)

                    if (option) {
                        this.blockPage = true
                        axios.post('services/hotels/up-selling', {
                            token_search_frontend: token_search_frontend,
                            hotel_id: hotel_id
                        }).then((result) => {
                            this.returList(token_search + '_' + hotel_id)
                            this.up_selling = result.data.data[0].city
                            this.blockPage = false
                            if (this.up_selling.hotels.length > 0) {
                                container_hotel.style.display = 'block'
                            } else {
                                alert("{{ trans('cart_view.label.hotels_found_apply_upgrade') }}")
                            }
                        }).catch((e) => {
                            this.blockPage = false
                            console.log(e)
                        })
                    } else {
                        container_hotel.style.display = 'none'
                    }
                },
                comparar: function (index, index_alt) {
                    // El elemento seleccionado en el carrito de compras
                    let mieleccion = document.getElementById(index)
                    let mieleccionRooms = mieleccion.getElementsByClassName('rooms-alternativa')[0]
                    // El elemendo alterno que reemplazara
                    let alternativa = document.getElementById(index_alt)
                    let roomsAlternativa = alternativa.getElementsByClassName('rooms-alternativa')[0]
                    // Los botones de seleccion del la alternativa
                    let btnComprar = alternativa.getElementsByClassName('btn-comprar')[0]
                    let btnCambiar = alternativa.getElementsByClassName('btn-cambiar')[0]
                    // Los controles del pop-up actual
                    //let contentBtn = mieleccion.parentElement.getElementsByClassName('contentBtn')[0];
                    let returList = mieleccion.parentElement.getElementsByClassName('returList')[0]
                    // Coleccion de elemendos alternativos
                    let alternativasClass = document.getElementsByClassName('row-alternativa')

                    // Se esconden todos los alternativos
                    for (var i = 0; i < alternativasClass.length; i++) {
                        alternativasClass[i].style.display = 'none' // depending on what you're doing
                    }

                    // El elemento seleccionado en el carrito de compras
                    mieleccion.style.display = 'block'
                    mieleccionRooms.style.display = 'block'
                    // El elemendo alterno que reemplazara
                    alternativa.style.display = 'block'
                    roomsAlternativa.style.display = 'block'
                    // Los botones de seleccion del elemento alterno
                    btnComprar.style.display = 'none'
                    btnCambiar.style.display = 'block'
                    // Los controles del pop-up actual
                    //contentBtn.style.display = "block";
                    returList.style.display = 'block'
                },
                cambiar: function (index, index_alt) {
                    // El elemento seleccionado en el carrito de compras
                    let mieleccion = document.getElementById(index)
                    // Los botones de seleccion en el carrito de compras
                    let btnEleccionMiEleccion = mieleccion.getElementsByClassName('btn-eleccion')[0]
                    let btnComprarEleccion = mieleccion.getElementsByClassName('btn-comprar')[0]
                    let btnCambiarEleccion = mieleccion.getElementsByClassName('btn-cambiar')[0]

                    let contentBtn = mieleccion.parentElement.getElementsByClassName('contentBtn')[0]

                    if (!index_alt) {
                        index_alt = mieleccion.getAttribute('alternative-selected')
                        let alternativa = document.getElementById(index_alt)
                        // Los botones de seleccion del la alternativa
                        let btnEleccion = alternativa.getElementsByClassName('btn-eleccion')[0]
                        let btnComprar = alternativa.getElementsByClassName('btn-comprar')[0]
                        let btnCambiar = alternativa.getElementsByClassName('btn-cambiar')[0]

                        mieleccion.classList.add('eleccion')
                        alternativa.classList.remove('eleccion')

                        btnEleccionMiEleccion.style.display = 'block'
                        btnComprarEleccion.style.display = 'none'
                        btnCambiarEleccion.style.display = 'none'

                        btnEleccion.style.display = 'none'
                        btnComprar.style.display = 'none'
                        btnCambiar.style.display = 'block'

                        contentBtn.style.display = 'none'

                        mieleccion.setAttribute('alternative-selected', '')
                    } else {
                        let alternativa = document.getElementById(index_alt)
                        // Los botones de seleccion del la alternativa
                        let btnEleccion = alternativa.getElementsByClassName('btn-eleccion')[0]
                        let btnComprar = alternativa.getElementsByClassName('btn-comprar')[0]
                        let btnCambiar = alternativa.getElementsByClassName('btn-cambiar')[0]

                        mieleccion.classList.remove('eleccion')
                        alternativa.classList.add('eleccion')

                        btnEleccionMiEleccion.style.display = 'none'
                        btnComprarEleccion.style.display = 'none'
                        btnCambiarEleccion.style.display = 'block'

                        btnEleccion.style.display = 'block'
                        btnComprar.style.display = 'none'
                        btnCambiar.style.display = 'none'

                        contentBtn.style.display = 'block'

                        mieleccion.setAttribute('alternative-selected', index_alt)
                    }
                },
                cambiar_eleccion: function (index, index_container_hotel) {
                    // El elemento seleccionado en el carrito de compras
                    let mieleccion = document.getElementById(index)
                    let cart_items_id = mieleccion.getAttribute('cart_items_id')
                    let index_alt = mieleccion.getAttribute('alternative-selected')

                    if (!index_alt) {
                        return false
                    }

                    let alternativa = document.getElementById(index_alt)
                    let upselling_index = alternativa.getAttribute('upselling-index')
                    let upselling_item = this.up_selling.hotels[upselling_index]

                    this.msg = "{{ trans('cart_view.label.label.updating_car') }}..."
                    this.blockPage = true
                    axios.post(baseURL + 'cart/content/change/item', {
                        token_search: this.up_selling.token_search,
                        token_search_frontend: this.up_selling.token_search_frontend,
                        date_from: this.up_selling.search_parameters.date_from,
                        date_to: this.up_selling.search_parameters.date_to,
                        cart_items_id: cart_items_id,
                        upselling_item: upselling_item
                    }).then((result) => {
                        this.msg = ''
                        let container_hotel = document.getElementById('container_hotel' + index_container_hotel)
                        container_hotel.style.display = 'none'
                        // this.returList(index);
                        this.updateMenu()
                        this.getCartContent()
                        this.blockPage = false
                    }).catch((e) => {
                        this.msg = ''
                        this.blockPage = false
                        console.log(e)
                    })
                },
                returList: function (index) {
                    let alternativasClass = document.getElementsByClassName('row-alternativa')
                    let mieleccionRoomsAlternativa = document.getElementsByClassName('rooms-alternativa')

                    let contentBtn = document.getElementsByClassName('contentBtn')
                    let returList = document.getElementsByClassName('returList')
                    let btnEleccion = document.getElementsByClassName('btn-eleccion')
                    let btnComprar = document.getElementsByClassName('btn-comprar')
                    let btnCambiar = document.getElementsByClassName('btn-cambiar')

                    for (var i = 0; i < alternativasClass.length; i++) {
                        alternativasClass[i].style.display = 'block'
                        alternativasClass[i].classList.remove('eleccion')
                    }
                    for (var i = 0; i < mieleccionRoomsAlternativa.length; i++) {
                        mieleccionRoomsAlternativa[i].style.display = 'none'
                    }
                    for (var i = 0; i < contentBtn.length; i++) {
                        contentBtn[i].style.display = 'none'
                        returList[i].style.display = 'none'
                        btnComprar[i].style.display = 'block'
                        btnCambiar[i].style.display = 'none'
                    }
                    for (var i = 0; i < btnComprar.length; i++) {
                        btnEleccion[i].style.display = 'none'
                        btnComprar[i].style.display = 'block'
                        btnCambiar[i].style.display = 'none'
                    }

                    // El elemento seleccionado en el carrito de compras
                    let mieleccion = document.getElementById(index)
                    let btnEleccionMiEleccion = mieleccion.getElementsByClassName('btn-eleccion')[0]
                    let btnComprarEleccion = mieleccion.getElementsByClassName('btn-comprar')[0]

                    btnEleccionMiEleccion.style.display = 'block'
                    btnComprarEleccion.style.display = 'none'
                    mieleccion.classList.add('eleccion')
                    mieleccion.setAttribute('alternative-selected', '')
                },
                countTotalPeople: function (rooms) {
                    let total = 0
                    rooms.forEach(function (valor, indice, array) {
                        let people = valor.quantity_adults + valor.quantity_child
                        total += people
                    })
                    return total
                },
                selectedSupplement: function (supplement, index_service, index_sup) {
                    if (supplement.selected) {
                        this.filterSupplement(supplement, index_service, index_sup)
                    }
                },
                validationsParameterSupplements: function (supplement_optional, index_service, index_supplement) {
                    let validate = true
                    if (supplement_optional.days.charge.length === 0) {
                        if (this.parameters_supplements[index_service][index_supplement].dates.length === 0) {
                            validate = false
                            this.$toast.warning('Debe seleccionar las fechas para el suplemento', {
                                position: 'top-right'
                            })
                        }
                    }
                    return validate
                },
                getOnlyDates: function (dates) {
                    let dates_array = []
                    for (let i = 0; i < dates.length; i++) {
                        dates_array.push(dates[i].day)
                    }
                    return dates_array
                },
                filterSupplement: function (supplement_optional, index_service, index_supplement) {
                    let validate = this.validationsParameterSupplements(supplement_optional, index_service, index_supplement)
                    if (validate) {
                        let dates_ = this.parameters_supplements[index_service][index_supplement].dates
                        // let filterDates = this.getOnlyDates(dates_)
                        axios.post(baseExternalURL + 'services/supplement/add',
                            {
                                adults: this.parameters_supplements[index_service][index_supplement].adults,
                                child: this.parameters_supplements[index_service][index_supplement].child,
                                dates: dates_,
                                service_id: this.cart_content.services[index_service].service_id,
                                supplement_id: supplement_optional.id,
                                token_search: this.cart_content.services[index_service].token_search,
                            },
                        ).then((result) => {
                            if (result.data.success) {
                                supplement_optional.rate.price_per_adult = result.data.data.price_per_adult
                                supplement_optional.rate.price_per_child = result.data.data.price_per_child
                                supplement_optional.rate.total_adult_amount = result.data.data.total_adult_amount
                                supplement_optional.rate.total_child_amount = result.data.data.total_child_amount
                                supplement_optional.rate.price_per_person = result.data.data.price_per_person
                                supplement_optional.rate.total_amount = result.data.data.total_amount
                                this.parameters_supplements[index_service][index_supplement].prices = result.data.data
                            } else {
                                this.$toast.warning('No se encontraron tarifas para el suplemento', {
                                    position: 'top-right'
                                })
                            }
                        }).catch((e) => {
                            console.log(e)
                        })
                    }
                },
                will_add_supplement_opt: function (service, index_service, index_supplement, supplement_optional) {
                    axios.post(baseURL + 'cart/services/supplement/add',
                        {
                            cart_item_id: service.cart_items_id[0],
                            token: supplement_optional.token_search,
                            adults: this.parameters_supplements[index_service][index_supplement].adults,
                            child: this.parameters_supplements[index_service][index_supplement].child,
                            dates: this.parameters_supplements[index_service][index_supplement].dates,
                            prices: this.parameters_supplements[index_service][index_supplement].prices
                        },
                    ).then((result) => {
                        if (result.data.success) {
                            this.getCartContent()
                        }
                    }).catch((e) => {
                        console.log(e)
                    })
                },
                will_remove_supplement_opt: function (service, supplement_optional) {
                    axios.post(baseURL + 'cart/services/supplement',
                        {
                            cart_item_id: service.cart_items_id[0],
                            token: supplement_optional.token_search
                        },
                    ).then((result) => {
                        this.getCartContent()
                    }).catch((e) => {
                        console.log(e)
                    })
                },
                back_page() {

                    let urlDefault = 'hotels';
                    if (localStorage.getItem('page_return')) {
                        urlDefault = localStorage.getItem('page_return')
                    }
                    document.location.href = '/' + urlDefault
                },
                clearCart() {
                    axios.post(
                        baseURL + 'cart/cancel/clear')
                        .then((result) => {
                            if (result.data.success) {
                                localStorage.setItem('search_params', '')
                                let page = document.head.querySelector("[name=route_name]").content;
                                if (['cart_details', 'reservations.personal_data'].includes(page)) {
                                    document.location.href = "/hotels"
                                }

                            }
                        }).catch((e) => {
                        console.log(e)
                    })
                },
                changeSizeImageService(url) {
                    return url.replace('/c_thumb,h_450,w_400/', '/c_scale,w_400/');
                },
                changeSizeImageHotel(url, ancho = 420) {
                    if (typeof url !== 'undefined' && url !== null) {
                        // Si la URL contiene 'hg-static.hyperguest.com', devolver la URL tal cual
                        if (url.includes('hg-static.hyperguest.com')) {
                            return url;
                        }
                         // Separa la URL en partes usando "/" como delimitador
                        let partes = url.split("/");

                         // Encuentra la posición donde comienza la parte que contiene la versión y el nombre del archivo
                        let indiceVersion = partes.findIndex(part => part.startsWith("v"));

                        partes.splice(indiceVersion, 0, "c_scale,w_" + ancho);

                        // Une las partes de la URL de nuevo
                        return partes.join("/");
                    } else {
                        return ""; // O devuelve un valor predeterminado, dependiendo de tus necesidades
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
            },
            filters: {
                format_hour: function (_hour) {
                    if (_hour == undefined || _hour == null) {
                        // console.log('fecha no parseada: ' + _date)
                        return
                    }
                    _hour = _hour.substr(0, 5)
                    return _hour
                },
            }
        })
    </script>

    <style>
        .icon-trash-remove {
            font-weight: 800;
            font-size: 14px;
        }

        .multiservice-removed {
            opacity: 0.5;
            text-decoration: line-through;
        }

        .schedules-icon {
            margin-top: -63px;
            position: absolute;
            margin-left: -8px;
        }

        .schedules-hours {
            position: absolute;
            margin-top: 50px;
        }

        .background-gray {
            background: #dedede;
        }

        .background-success {
            background: #d6ffdd;
        }

        .background-warning {
            background: #fffbdf;
        }

        .tr-choose:hover {
            opacity: 0.8;
            cursor: pointer;
        }
    </style>

@endsection
