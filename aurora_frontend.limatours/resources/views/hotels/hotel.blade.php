@extends('layouts.app')
@section('content')
    <loading-component v-show="blockPage"></loading-component>
    <section class="page-hotel" v-if="this.client_id">
        <div class="container">
            <div class="motor-busqueda">
                <div class="d-flex align-items-center justify-content-between">
                    <h2 class="mb-3">{{ trans('hotel.label.reservation_hotel') }}</h2>
                    <div>
                        <button class="btn btn-secondary btn-lg" @click="cleanSearchParameters">
                            <i class="fas fa-eraser"></i> {{ trans('global.label.clear_filters') }}
                        </button>
                    </div>
                </div>
                <template v-for="(search_destiny,index_search_destiny) in search_destinies">
                    <div class="position-relative">
                        <div class="d-flex position-absolute h-100 w-100 align-items-center justify-content-center"
                            v-if="loading_form" style="z-index: 10; border-radius: 10px;">
                            <b class="text-black">Cargando..</b>
                            <!-- svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" fill="currentColor" class="bi bi-hourglass" viewBox="0 0 16 16">
                                                                                                                                    <path d="M2 1.5a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-1v1a4.5 4.5 0 0 1-2.557 4.06c-.29.139-.443.377-.443.59v.7c0 .213.154.451.443.59A4.5 4.5 0 0 1 12.5 13v1h1a.5.5 0 0 1 0 1h-11a.5.5 0 1 1 0-1h1v-1a4.5 4.5 0 0 1 2.557-4.06c.29-.139.443-.377.443-.59v-.7c0-.213-.154-.451-.443-.59A4.5 4.5 0 0 1 3.5 3V2h-1a.5.5 0 0 1-.5-.5m2.5.5v1a3.5 3.5 0 0 0 1.989 3.158c.533.256 1.011.791 1.011 1.491v.702c0 .7-.478 1.235-1.011 1.491A3.5 3.5 0 0 0 4.5 13v1h7v-1a3.5 3.5 0 0 0-1.989-3.158C8.978 9.586 8.5 9.052 8.5 8.351v-.702c0-.7.478-1.235 1.011-1.491A3.5 3.5 0 0 0 11.5 3V2z"/>
                                                                                                                                </svg -->
                            <div class="bg-light position-absolute h-100 w-100" style="opacity: .5;">
                            </div>
                        </div>
                        <div class="form form-hotel mb-5 position-relative" style="border: 1px dashed #ccc;">
                            <div class="form-row mb-0">
                                <div class="form-group nombre mb-3 col-auto">
                                    <v-select :options="destinations_countries_select"
                                        @input="change_destiny_cities(index_search_destiny)"
                                        v-model="search_destiny.destiny_country" class="form-control">
                                    </v-select>
                                </div>
                                <div class="form-group nombre mb-3 col-auto">
                                    <v-select :options="search_destiny.destinations_select"
                                        @input="change_destiny_districts(index_search_destiny)"
                                        v-model="search_destiny.destiny" class="form-control">
                                    </v-select>
                                </div>
                                <div class="form-group mb-3 col-3">
                                    <date-range-picker :locale-data="locale_data" :time-picker24-hour="timePicker24Hour"
                                        :show-week-numbers="showWeekNumbers" :ranges="false" :auto-apply="true"
                                        :min-date="minDateToday"
                                        v-model="search_destiny.dateRange">
                                    </date-range-picker>
                                </div>
                                <div class="form-group dropdown mb-3 col">
                                    <button class="form-control" id="dropdownHab" data-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="true">
                                        <span><strong>@{{ search_destiny.quantity_rooms }}</strong>
                                            {{ trans('hotel.label.rooms') }}</span>
                                        <span><strong>@{{ search_destiny.quantity_adults }}</strong>
                                            {{ trans('hotel.label.adults') }}</span>
                                        <span><strong>@{{ search_destiny.quantity_child }}</strong>
                                            {{ trans('hotel.label.child') }}</span>
                                    </button>
                                    <input type="hidden"
                                        :value="search_destiny.quantity_rooms + ' habitaciones ' + search_destiny
                                            .quantity_adults + 'adultos ' + search_destiny.quantity_child + 'niños'">
                                    <div aria-labelledby="dropdownHab" class="dropdown dropdown-menu"
                                        :id="'container_quantity_persons_rooms_' + index_search_destiny"
                                        :class="search_destiny.class_container_rooms">
                                        <div class="form-group num-hab" style="display: none;">
                                            <select v-model="search_destiny.quantity_rooms"
                                                @change="changeQuantityRooms($event,index_search_destiny)"
                                                class="form-control">
                                                <option v-for="num_rooms in 5" :value="num_rooms">
                                                    @{{ num_rooms }}
                                                </option>
                                            </select>
                                            <label>{{ trans('hotel.label.rooms') }}</label>
                                        </div>
                                        <hr style="display: none;">
                                        <div class="d-flex justify-content-end">
                                            <div
                                                class="container_quantity_persons_rooms_selects quantity-persons-rooms mb-0">
                                                <div class="form-group">
                                                    <label>{{ trans('hotel.label.adults') }}</label>
                                                </div>
                                                <div class="form-group">
                                                    <label>{{ trans('hotel.label.child') }}</label>
                                                </div>
                                                <template v-if="search_destiny.quantity_child >=1">
                                                    <div class="form-group" v-for="nc in search_destiny.quantity_child">
                                                        <label>{{ trans('hotel.label.age') }}</label>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                        <div v-for="(quantity_person_room, index_quantity_person_room) in search_destiny.quantity_persons_rooms"
                                            class="container_quantity_persons_rooms_selects quantity-persons-rooms">
                                            <h4>
                                                {{ trans('hotel.label.room') }}
                                                @{{ index_quantity_person_room + 1 }}
                                            </h4>
                                            <div class="form-group" :class="search_destiny.class_container_select">
                                                <select
                                                    v-model="search_destiny.quantity_persons_rooms[index_quantity_person_room].adults"
                                                    class="form-control"
                                                    @change="calculateNumPersonsPerRooms(index_search_destiny)">
                                                    <option v-for="num_adults in 4" :value="num_adults - 1">
                                                        @{{ num_adults - 1 }}
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="form-group" :class="search_destiny.class_container_select">
                                                <select
                                                    v-model="search_destiny.quantity_persons_rooms[index_quantity_person_room].child"
                                                    @change="changeQuantityChild($event,index_search_destiny,index_quantity_person_room)"
                                                    class="form-control">
                                                    <option v-for="num_child in 4" :value="num_child - 1">
                                                        @{{ num_child - 1 }}
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="form-group" :class="search_destiny.class_container_select"
                                                v-for="(age_child_slot,index_age_child) in search_destiny.quantity_persons_rooms[index_quantity_person_room].ages_child"
                                                v-if="search_destiny.quantity_persons_rooms[index_quantity_person_room].child >=1">
                                                <select
                                                    v-model="search_destiny.quantity_persons_rooms[index_quantity_person_room].ages_child[index_age_child].age"
                                                    class="form-control">
                                                    <option v-for="age_child in 17" :value="age_child">
                                                        @{{ age_child }}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group hotel mb-3">
                                    <select class="form-control" v-model="search_destiny.typeclass_id">
                                        <option value="all">{{ trans('hotel.label.all_categories') }}</option>
                                        <option :value="hotel.class_id" v-for="hotel in classes_hotel">
                                            @{{ hotel.class_name }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-row mb-0" v-if="search_destiny.destinations_additional_select">
                                <div class="col-auto mb-3"
                                    v-for="district in search_destiny.destinations_additional_select">
                                    <label class="tag-districts m-0">
                                        <input type="radio" :value="district.code"
                                            v-model="search_destiny.destiny_district"
                                            :name="'radio_destiny_' + index_search_destiny + '_' + district.parent_code">
                                        <small><b>@{{ district.label }}</b></small>
                                    </label>
                                </div>
                            </div>
                            <div class="form-row mb-3" style="align-items: center;">
                                <div class="form-group nombre col pr-2">
                                    <input type="text" class="form-control" v-model="search_destiny.hotels_search_code"
                                        placeholder="{{ trans('hotel.label.name') }}">
                                </div>

                                <div class="col-auto" v-if="index_search_destiny > 0">
                                    <a class="btn btn-secondary" @click.prevent="deleteSearchDestiny(index_search_destiny)"
                                        style="cursor: pointer; border-radius: 100%;">
                                        <i class="icon-minus"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
                <div class="d-flex align-items-center justify-content-between form-btn-action">
                    <div class="d-flex align-self-center">
                        <div class="modal fade" id="mod-historial" tabindex="-1" role="dialog"
                            aria-labelledby="mod-historial-label" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <a id="modal_search_close" class="close" data-dismiss="modal" aria-label="Close"
                                    href="">
                                    {{ trans('hotel.label.Close') }}<i aria-hidden="true">&times;</i>
                                </a>
                                <div class="modal-content">
                                    <div class="modal-body">
                                        <h3 class="modal-title" id="mod-historial-label">
                                            {{ trans('hotel.label.hotel_history') }}</h3>
                                        <p>{{ trans('hotel.label.descriptive_history') }}</p>
                                        <div class="box-busqueda"
                                            v-for="(search_destiny,index_search_destiny) in search_destinies_save">
                                            <div class="box-busqueda-row" v-for="destiny in search_destiny">
                                                <h4>@{{ (JSON.parse(destiny.destiny)).label }}</h4>
                                                <div class="itinerario-historial">
                                                    <span>@{{ formatDate(JSON.parse(destiny.date_range).startDate) }}</span>
                                                    <span>@{{ formatDate(JSON.parse(destiny.date_range).endDate) }}</span>
                                                    <span><strong>@{{ destiny.quantity_rooms }}
                                                            Hab.</strong><strong>@{{ destiny.quantity_adults }}
                                                            {{ trans('hotel.label.adults') }}</strong><strong>@{{ destiny.quantity_child }}
                                                            {{ trans('hotel.label.child') }}</strong></span>
                                                    <p>{{ trans('hotel.label.searched_the') }} @{{ getDateSearchDestiny(destiny.created_at) }}</p>
                                                </div>
                                            </div>
                                            <button class="btn-seleccionar"
                                                @click="getSearchDestiniesByTokenSearch(index_search_destiny)">
                                                {{ trans('hotel.label.repeat_search') }} <i
                                                    class="icon-rotate-ccw"></i></button>
                                        </div>
                                    </div>
                                    <div class="modal-footer text-center">
                                        <div class="col-lg-12">
                                            <a href="javascript:void(0)" tabindex="-1" aria-disabled="true"
                                                v-if="pagination.current_page > 1"
                                                @click.prevent="changePage(pagination.current_page - 1)">
                                                <i class="fa-angle-left fa"></i>
                                            </a>
                                            <a href="javascript:void(0)" @click.prevent="changePage(page)"
                                                v-for="page in pagesNumbers"
                                                :class="{ 'active': page == pagination.current_page }">@{{ page }}</a>
                                            <a href="javascript:void(0)" aria-label="Next"
                                                @click.prevent="changePage(pagination.current_page + 1)"
                                                v-if="pagination.current_page < pagination.last_page">
                                                <i class="fa-angle-right fa"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <a @click="addNewSearchDestiny()" class="agregar-destino">
                            <i class="icon-plus-circle"></i>
                            {{ trans('hotel.label.add_destiny') }}
                        </a>

                    </div>
                    <div class="d-flex">
                        <button class="btn btn-primary"
                            @click="searchDestinies()">{{ trans('hotel.label.search_hotels') }}</button>
                    </div>
                </div>
            </div>
            <hr>
            <div class="container-heigth">
                <div class="row col-lg-12 title-result" v-show="tabsDestinies">
                    <h3>{{ trans('hotel.label.your_results') }}</h3>
                </div>

                <div class="bg-white py-3" v-if="tabsDestinies" style="position:sticky; top: 0; z-index: 20;">
                    <div class="d-flex mb-3">
                        <div class="row">
                            <div class="col">
                                <div class="tabsNav mb-0">
                                    <ul class="clearfix m-0">
                                        <li v-for="(search_destiny,index_search_destiny) in search_destinies">
                                            <a :class="{ 'active': search_destiny.active }"
                                                @click.prevent="setDestinyId($event,index_search_destiny)">
                                                @{{ search_destiny.destiny_name }}
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="text-right" v-if="search_destinies[destiny_id]">
                                    <span><b>@{{ search_destinies[destiny_id].quantity_hotels }}</b> {{ trans('hotel.label.hotels_found_in') }}
                                        <b>@{{ search_destinies[destiny_id].destiny_name }}</b></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <template v-if="search_destinies[destiny_id]">
                        <div class="row col-lg-12 filtros mb-0"
                            v-if="search_destinies[destiny_id].hotels_original.length > 0">
                            <div class="row col-lg-4">
                                <div class="col-lg-4 label">
                                    <label>{{ trans('hotel.label.sort_by') }}</label>
                                </div>
                                <div class="col-lg-8">
                                    <select class="form-control" @change="sortHotels($event)">
                                        <option value="2">{{ trans('hotel.label.price_lowest_to_highest') }}</option>
                                        <option value="3">{{ trans('hotel.label.price_highest_to_lowest') }}</option>
                                        <option value="4">{{ trans('hotel.label.stars_lowest_to_highest') }}</option>
                                        <option value="5">{{ trans('hotel.label.stars_highest_to_lowest') }}</option>
                                        <option value="6">{{ trans('hotel.label.popularity') }}</option>
                                        <option value="7">{{ trans('hotel.label.favorite') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row col-lg-4 filtros-font">
                                <div class="col lg-2 text-center text">
                                    {{ trans('hotel.label.filters') }}
                                </div>
                                <!--Filtro por Precio-->
                                <div class="col lg-2 text-center tooltip_filter_price font">
                                    <div class="dropdown">
                                        <a href="#" role="button" id="dropdownMenuLink1" data-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="true" style="border: none;">
                                            <i class="fas fa-dollar-sign" style="font-size: x-large"></i>
                                        </a>
                                        <div id="dropdownMenuLink1Container" class="dropdown-menu"
                                            aria-labelledby="dropdownMenuLink1"
                                            style="width: 240px; font-size: 14px; margin-left: -36px; margin-top: 18px;">
                                            <div style="color: #000;">
                                                <div style="width: 100%;margin: 0 auto;">
                                                    <b style="font-size: 15px;">{{ trans('hotel.label.price') }}</b>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--Filtro por Estrellas-->
                                <div class="col lg-2 text-center font">
                                    <div class="dropdown show">
                                        <a href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown"
                                            aria-haspopup="false" aria-expanded="false" style="border: none;">
                                            <i class="fas fa-star" style="font-size: x-large"></i>
                                        </a>
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuLink"
                                            style="width: 200px; font-size: 14px;  margin-left: -36px; margin-top: 18px;">
                                            <div style="width: 100%;margin: 0 auto;">
                                                <b style="font-size: 15px;">{{ trans('hotel.label.stars') }}</b>
                                            </div>
                                            <div class="form-check">
                                                <input style="margin-top: 14px;" class="form-check-input" type="checkbox"
                                                    id="check_all_stars" v-model="check_all_stars"
                                                    @change="checkAllStars">
                                                <label class="form-check-label" for="check_all_stars"
                                                    style="padding-left: 7px;">
                                                    {{ trans('hotel.label.all_the_stars') }}
                                                </label>
                                            </div>
                                            <div class="form-check" v-for="star in stars">
                                                <input style="margin-top: 14px;" class="form-check-input" type="checkbox"
                                                    :id="'checkbox_' + star.star" v-model="star.status"
                                                    @change="filterByStars">
                                                <label class="form-check-label" :for="'checkbox_' + star.star"
                                                    style="padding-left: 7px;">
                                                    <i class="fas fa-star" v-for="icon in star.star"></i>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--Filtro por Clases de Hotel-->
                                <div class="col lg-2 text-center font">
                                    <div class="dropdown show">
                                        <a href="#" role="button" id="dropdownMenuLink2" data-toggle="dropdown"
                                            aria-haspopup="false" aria-expanded="false" style="border: none;">
                                            <i class="fas fa-tag" style="font-size: x-large"></i>
                                        </a>
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuLink2"
                                            style="width: 240px; font-size: 14px; margin-left: -36px; margin-top: 18px;">
                                            <div style="width: 100%;margin: 0 auto;">
                                                <b style="font-size: 15px;">{{ trans('hotel.label.type_hotel') }}</b>
                                            </div>
                                            <div class="form-check">
                                                <input style="margin-top: 14px;" class="form-check-input" type="checkbox"
                                                    id="check_all_class" v-model="check_all_class"
                                                    @change="checkAllClass">
                                                <label class="form-check-label" for="check_all_stars"
                                                    style="padding-left: 7px;">
                                                    {{ trans('hotel.label.all_accommodations') }}
                                                </label>
                                            </div>
                                            <div class="form-check"
                                                v-for="class_hotel in search_destinies[destiny_id].class">
                                                <input style="margin-top: 14px;" class="form-check-input" type="checkbox"
                                                    :id="'checkbox_' + class_hotel.class_name"
                                                    v-model="class_hotel.status" @change="filterByClassHotel">
                                                <label class="form-check-label"
                                                    :for="'checkbox_' + class_hotel.class_name"
                                                    style="padding-left: 7px;">
                                                    @{{ class_hotel.class_name }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--Filtro por Zonas de Hotel-->
                                <div class="col lg-2 text-center font">
                                    <div class="dropdown show">
                                        <a href="#" role="button" id="dropdownMenuLink3" data-toggle="dropdown"
                                            aria-haspopup="false" aria-expanded="false" style="border: none;">
                                            <i class="fas fa-map-marker-alt" style="font-size: x-large"></i>
                                        </a>
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuLink3"
                                            style="width: 240px; font-size: 14px; margin-left: -36px; margin-top: 18px;">
                                            <div style="width: 100%;margin: 0 auto;">
                                                <b style="font-size: 15px;">{{ trans('hotel.label.interest_site') }}</b>
                                            </div>
                                            <div class="form-check">
                                                <input style="margin-top: 14px;" class="form-check-input" type="checkbox"
                                                    id="check_all_places" v-model="check_all_zones"
                                                    @change="checkAllZones">
                                                <label class="form-check-label" for="check_all_places"
                                                    style="padding-left: 7px;">
                                                    {{ trans('hotel.label.all_the_places') }}
                                                </label>
                                            </div>
                                            <div class="form-check" v-for="zone in search_destinies[destiny_id].zones">
                                                <input class="form-check-input" type="checkbox"
                                                    :id="'checkbox_' + zone.zone_name" v-model="zone.status"
                                                    @change="filterByZoneHotel">
                                                <label class="form-check-label" :for="'checkbox_' + zone.zone_name"
                                                    style="padding-left: 7px;">
                                                    @{{ zone.zone_name }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--Filtro por Nombre de Hotel-->
                                <div class="col lg-2 text-center font">
                                    <div class="dropdown show">
                                        <a href="#" role="button" id="dropdownMenuLink4" data-toggle="dropdown"
                                            aria-haspopup="false" aria-expanded="false" style="border: none;">
                                            <i class="fas fa-search" style="font-size: x-large"></i>
                                        </a>
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuLink4"
                                            style="width: 240px; font-size: 14px; margin-left: -36px; margin-top: 18px;">
                                            <div style="width: 100%;margin: 0 auto;">
                                                <b style="font-size: 15px;">{{ trans('hotel.label.filter_by_name') }}</b>
                                            </div>
                                            <input
                                                style="width: 100%; height: 30px; line-height: 30px; padding: 0; border: 1px solid #E9E9E9; border-radius: 3px; padding:0 3px;"
                                                type="text" v-model="search_destinies[destiny_id].filter_by_name"
                                                class="form" @keyup.enter="filterByNameHotel">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <template v-if="search_destinies[destiny_id]">

                    <!-- template v-if="search_destinies[destiny_id].hotels.length === 0 && flag_search">
                                                                                                                            <div class="alert alert-warning text-center border d-flex flex-column align-items-center justify-content-center py-5">
                                                                                                                                <p class="text-muted mb-0">No se encontró hoteles con los filtros seleccionados.</p>
                                                                                                                            </div>
                                                                                                                        </template -->

                    <div id="result-hotels" class="row-fluid col-lg-12 result-hotels mt-5"
                        v-if="search_destinies[destiny_id].hotels.length > 0">
                        <div class="row col-lg-12" v-for="(hotel,index_hotel) in search_destinies[destiny_id].hotels">

                            <div class="row col-lg-12">
                                <div class="col-lg-4 gallery" :style="(hotel.flag_new == 1) ? 'padding-left:2%;' : ''">
                                    <div class="label-list" style="position:absolute; left:0;">
                                        <div class="category recomendado" style="float:left;" v-if="hotel.flag_new == 1">
                                            {{ trans('hotel.label.new') }}
                                        </div>
                                    </div>
                                    <div class="label-list">
                                        <div class="category" :style="'background-color:' + hotel.color_class + ' '">
                                            @{{ hotel.class }}
                                        </div>
                                        <div class="category recomendado" v-if="hotel.popularity > 0">
                                            <i class="icon icon-thumbs-up"></i>
                                            {{ trans('hotel.label.recommended') }}
                                        </div>
                                        <div v-if="hotel.favorite==1" class="category favorite">
                                            <i class="icon icon-heart" @click="addFavorite(hotel.id,hotel)"></i>
                                            <label for=""
                                                v-show="hotel.favorite==1">{{ trans('hotel.label.favorite') }}</label>
                                        </div>
                                        <div v-if="hotel.favorite==0" class="category favorite no-favorite">
                                            <i class="icon icon-heart" @click="addFavorite(hotel.id,hotel)"></i>
                                            <label for="" v-show="hotel.favorite==0">No
                                                {{ trans('hotel.label.favorite') }}</label>
                                        </div>
                                        <div class="category" :style="'background-color:#4BC910'"
                                            v-if="validateAvailableHotel(hotel)">OK
                                        </div>
                                        <div class="category" :style="'background-color:#DC3545'"
                                            v-if="!validateAvailableHotel(hotel)">RQ
                                        </div>
                                        <!--<div class="category star">
                                                                                                                                                <span class="icon-star" v-for="n in parseInt(hotel.category)"></span>
                                                                                                                                            </div>-->
                                    </div>

                                    <ul class="slides" v-if="hotel.galleries.length > 0">
                                        <template v-for="(gallery,index_image) in hotel.galleries">
                                            <input type="radio" :name="'radio-btn_' + index_hotel"
                                                :id="'img-' + index_image + '_' + index_hotel"
                                                :checked="index_image == 0" />
                                            <li :class="'slide-container slide-container_' + index_image">
                                                <div class="slide">
                                                    <img loading="lazy" :src="gallery" />
                                                </div>
                                                <div class="nav">
                                                    <label style="line-height: normal; padding: 17% 0 0 0"
                                                        :for="'img-' + (hotel.galleries.length - 1) + '_' + index_hotel"
                                                        class="prev" v-if="index_image ===0">&#x2039;</label>
                                                    <label style="line-height: normal; padding: 17% 0 0 0"
                                                        :for="'img-' + (index_image - 1) + '_' + index_hotel"
                                                        class="prev" v-else>&#x2039;</label>

                                                    <label style="line-height: normal; padding: 17% 0 0 0"
                                                        :for="'img-0' + '_' + index_hotel" class="next"
                                                        v-if="index_image ===(hotel.galleries.length -1)">&#x203a;</label>
                                                    <label style="line-height: normal; padding: 17% 0 0 0"
                                                        :for="'img-' + (index_image + 1) + '_' + index_hotel"
                                                        class="next" v-else>&#x203a;</label>
                                                </div>
                                            </li>
                                        </template>

                                        <li class="nav-dots">
                                            <label :for="'img-' + index_image + '_' + index_hotel" class="nav-dot"
                                                :id="'img-dot-' + index_image + '_' + index_hotel"
                                                v-for="(gallery,index_image) in hotel.galleries"></label>
                                        </li>
                                    </ul>
                                    <ul class="slides" v-else>
                                        <li class="default"><img loading="lazy" src="/images/hotel-default.jpg"></li>
                                    </ul>
                                </div>
                                <div class="col-lg-8 item-hotel">
                                    <div class="content-hotel">
                                        <div class="title"><b>@{{ hotel.name }}</b>
                                            <span class="icon-star" v-if="hotel.category > 0"
                                                v-for="(n, ni) in parseInt(hotel.category)"></span>
                                        </div>
                                        <div class="price">
                                            <p class="mb-3">{{ trans('hotel.label.since') }} <br>
                                                <span style="font-size: 12px"
                                                v-if="client && client.commission_status == 1 && parseFloat(client.commission) > 0 && user_type_id == 4"
                                                class="badge badge-warning ml-2">
                                                {{ trans('global.label.with_commission') }}
                                            </span>
                                            </p>

                                            <span v-if="hotel.flag_migrate == 1">$ <b>@{{ getPrice(hotel.price) }}</b></span>
                                            <span v-else style="color: green">$ <b>@{{ getPrice(hotel.price) }}</b></span>                                            <!-- Label cuando el precio lleva comisión -->

                                            <a href="/#/" @click.prevent="showRoomsHotel(index_hotel)" class="btn-seleccionar">
                                                {{ trans('hotel.label.select') }}
                                            </a>
                                        </div>

                                        <div class="details">
                                            <!--<a @click.prevent="showGoogleMaps(hotel.coordinates.latitude,hotel.coordinates.longitude,hotel.name,hotel.description)"
                                                                                                                                                    href="#">Ver ubicación</a> <span>A 30 min. del centro de la ciudad </span><a href="" class="more">ver más</a>-->
                                            <a @click.prevent="showGoogleMaps(hotel.coordinates.latitude,hotel.coordinates.longitude,hotel.name,hotel.category,hotel.price,hotel.address,hotel.galleries,index_hotel)"
                                                href="#">{{ trans('hotel.label.location') }}</a>
                                            {{--                                <span>A 30 min. del centro de la ciudad </span><a href="" class="more">ver más</a> --}}
                                        </div>
                                        <div :id="'description_' + index_hotel" class="description">
                                            <template>

                                                <span v-html="readMoreDescription(hotel.description)">
                                                </span>

                                                <a v-if="contarCadena(hotel.description) > 200"
                                                    :id="'leermas_' + index_hotel"
                                                    @click.prevent="leermas(index_hotel)">{{ trans('hotel.label.read_more') }}</a>

                                                <span :id="'last_' + index_hotel" class="last"
                                                    v-html="readMoreDescriptionComplete(hotel.description)">
                                                </span>

                                                <a class="hide" :id="'leermenos_' + index_hotel"
                                                    @click.prevent="leermenos(index_hotel)">{{ trans('hotel.label.read_less') }}</a>
                                            </template>

                                            <div class="services d-flex">
                                                <template v-for="gallery in hotel.amenities">
                                                    <div :data-tooltip="gallery.name">
                                                        <img :src="gallery.image" :alt="gallery.name"
                                                            v-if="gallery.image != '' " />
                                                    </div>
                                                </template>
                                                <div class="text" @click="openModalDetail('Remarks', hotel.notes)"
                                                    v-if="hotel.notes != null && hotel.notes != '' && user_type_id == 3">
                                                    <span class="icon-message-circle mr-2"
                                                        style="font-size: 1.6rem;"></span>
                                                    Remarks
                                                    <div class="icon ml-4">
                                                        <i class="fa fa-angle-right"></i>
                                                    </div>
                                                </div>
                                                <div class="text"
                                                    @click="openModalDetail('{{ trans('hotel.label.notes') }}', hotel.summary )"
                                                    v-if="hotel.summary != null && hotel.summary != ''">
                                                    <span class="icon-message-circle mr-2"
                                                        style="font-size: 1.6rem;"></span>
                                                    {{ trans('hotel.label.notes') }}
                                                    <div class="icon ml-4">
                                                        <i class="fa fa-angle-right"></i>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row-fluid col-lg-12 list-rooms" :id="'container_rooms_hotel' + index_hotel"
                                v-if="flag_hotel_room === index_hotel">

                                {{-- boton de carrito --}}

                                <div v-if="cart_quantity_items > 0" class="list-rooms-car cart">
                                    <!--<a href="/cart_details/view">
                                                                                                                                            <i class="icon-shopping-cart"></i>
                                                                                                                                            <span>@{{ cart_quantity_items }}</span>
                                                                                                                                        </a>-->
                                    <a id="dropdownMainCar" data-toggle="dropdown" aria-haspopup="true"
                                        aria-expanded="false">
                                        <i class="icon-shopping-cart"></i>
                                        <span class="count">@{{ cart.quantity_items }}</span>
                                    </a>

                                    <!--------- Carrito ------>

                                    <div class="dropdown dropdown-menu menu-cart" aria-labelledby="dropdownMainCar"
                                        v-if="cart.hotels.length > 0 || cart.services.length > 0">
                                        <h2>{{ trans('hotel.label.your_shopping_cart') }}</h2>
                                        <h3>{{ trans('hotel.label.you_have') }} @{{ (cart.hotels.length +
    cart.services.length) }}
                                            {{ trans('hotel.label.products_your_cart') }}</h3>
                                        <div class="shopping-cart">
                                            <div class="scroll-cart scrollbar-project">
                                                <div class="card-body">

                                                    <div :id="'hotel-content-shopping' + index"
                                                        class="hotel-content-shopping"
                                                        v-for="(hotel,index) in cart.hotels">

                                                        <div class="img-shopping">
                                                            <img loading="lazy" :src="hotel.hotel.galleries[0]"
                                                                alt="Image Hotel"
                                                                onerror="this.src = baseURL + 'images/hotel-default.jpg'">
                                                        </div>
                                                        <div class="content-shopping">
                                                            <span class="tipo">@{{ hotel.hotel.class }}</span>
                                                            <h3 class="text-left">
                                                                @{{ hotel.hotel_name }}
                                                                <span class="icon-star"></span>
                                                                <div class="price">$<b>@{{ roundLito(hotel.total_hotel) }}</b></div>
                                                            </h3>
                                                            <div class="date-shopping">
                                                                <i class="icon-calendar"></i>
                                                                <span>@{{ formatDate(hotel.date_from) }}</span>
                                                                <span>@{{ formatDate(hotel.date_to) }}</span>

                                                                <div class="total-rooms">

                                                                    <button type="button"
                                                                        class="collapsed btn btn-secondary">
                                                                        <span class="fa fa-circle"
                                                                            v-for="room in hotel.rooms"></span>
                                                                        @{{ hotel.rooms.length }} Hab
                                                                    </button>


                                                                </div>
                                                            </div>
                                                            <!--<b-collapse :id="'hotels-' + index">
                                                                                                                                                                    <b-card>
                                                                                                                                                                        <div class="car-room" v-for="room in hotel.rooms">
                                                                                                                                                                            <h5><span class="fa fa-circle"></span>@{{ room.room_name }}</h5>
                                                                                                                                                                            <div class="price">$ <b>@{{ room.total_room }}</b> <a class="remove" @click="cancelRoomsCart(room,hotel)"><i class="icon-trash-2"></i></a></div>
                                                                                                                                                                        </div>
                                                                                                                                                                    </b-card>
                                                                                                                                                                </b-collapse>-->
                                                        </div>

                                                    </div>
                                                    <div :id="'service-content-shopping' + index"
                                                        class="hotel-content-shopping"
                                                        v-for="(service,index) in cart.services">
                                                        <div class="img-shopping">
                                                            <img :src="service.service.galleries[0]" alt="Image Servicio"
                                                                onerror="this.src = baseURL + 'images/hotel-default.jpg'">
                                                        </div>
                                                        <div class="content-shopping">
                                                            <h3 class="text-left">
                                                                @{{ service.service_name }} -
                                                                [@{{service.service.code}}]
                                                                <div class="price">$<b>@{{
                                                                        getPrice(service.total_service) }}</b></div>
                                                            </h3>
                                                            <div class="date-shopping">
                                                                <i class="icon-calendar"></i>
                                                                <span>@{{ formatDate(service.date_from) }}</span><br>
                                                                <i class="icon-map-pin"></i>
                                                                <span> @{{ service.service.origin.country }},
                                                                    @{{ service.service.origin.state }}</span><span
                                                                    v-if="service.service.origin.city !== null">,
                                                                    @{{ service.service.origin.city }}</span><span
                                                                    v-if="service.service.origin.zone !== null">,
                                                                    @{{ service.service.origin.zone }}</span>
                                                                <i class="icon-arrow-right"></i>
                                                                <span> @{{ service.service.destiny.country }},
                                                                    @{{ service.service.destiny.state }}</span><span
                                                                    v-if="service.service.destiny.city !== null">,
                                                                    @{{ service.service.destiny.city }}</span><span
                                                                    v-if="service.service.destiny.zone !== null">,
                                                                    @{{ service.service.destiny.zone }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="no-gutters total">
                                                <h3>{{ trans('hotel.label.total_pay') }}</h3>
                                                <div class="price">USD <b>@{{ getPrice(cart.total_cart) }}</b></div>
                                            </div>
                                        </div>
                                        <a class="btn btn-primary btn-car" href="javascript:void(0)"
                                            @click="goCartDetails()">{{ trans('hotel.label.go_to_cart') }}</a>
                                    </div>
                                </div>
                                <div class="list-rooms-car-desac" v-else></div>

                                <div class="close-list-rooms">
                                    <a @click.prevent="flag_hotel_room = null"
                                        href="">{{ trans('hotel.label.Close') }} <i
                                            aria-hidden="true">&times;</i></a>
                                </div>

                                <div class="content-list">

                                    <div class="col-12">
                                        <h4>{{ trans('hotel.label.select_rooms') }}</h4>

                                        <div class="col-lg-12 seleccion">
                                            @{{ hotel.name }} <span class="two"></span>
                                            <img loading="lazy" src="/images/star.png" v-if="hotel.category > 0"
                                                v-for="(n, ni) in parseInt(hotel.category)" />
                                            <span></span>
                                            <!-- img loading="lazy" src="/images/calendar.png" -->
                                            {{--                                            <span class="two"></span> <b>@{{ getHotelStartDate() }}</b> --}}
                                            {{--                                            <span class="two"></span> <b>@{{ getHotelEndDate() }}</b> --}}
                                            <span></span>

                                            <img src="/images/luna.png">
                                            <span class="two"></span> <b>@{{ getHotelNights() }}</b>
                                            {{ trans('hotel.label.nights') }}
                                            <span></span>

                                            <img loading="lazy" src="/images/user-fil.png">
                                            <span class="two"></span> <b>@{{ search_destinies[destiny_id].quantity_rooms }}</b>
                                            {{ trans('hotel.label.rooms') }}
                                            <span class="two"></span> <b>@{{ search_destinies[destiny_id].quantity_adults }}</b>
                                            {{ trans('hotel.label.adults') }}
                                            <span class="two"></span> <b>@{{ search_destinies[destiny_id].quantity_child }}</b>
                                            {{ trans('hotel.label.child') }}
                                        </div>
                                        <h5 class="blue" v-if="hotel.best_options.total_rate_amount> 0">
                                            {{ trans('hotel.label.best_combination') }}
                                        </h5>
                                    </div>

                                    <div class="col-12 p-0"
                                        v-if="(hotel.notes != null && hotel.notes != '' && user_type_id == 3) || hotel.summary != null && hotel.summary != ''">
                                        <div class="accordion mb-4" role="tablist">
                                            <b-card no-body v-if="hotel.notes != null && hotel.notes != '' && user_type_id == 3">
                                                <b-card-header header-tag="div" class="p-0" role="tab">
                                                    <b-button block v-b-toggle.accordion-remarks variant="secondary">Remarks</b-button>
                                                </b-card-header>
                                                <b-collapse id="accordion-remarks" accordion="my-accordion" role="tabpanel">
                                                    <b-card-body>
                                                        <div class="d-block"
                                                            style="font-size: 13px; line-height: 16px;"
                                                            v-html="hotel.notes.replace(/\n/g, '<br>')">
                                                        </div>
                                                    </b-card-body>
                                                </b-collapse>
                                            </b-card>

                                            <b-card no-body v-if="hotel.summary != null && hotel.summary != ''">
                                                <b-card-header header-tag="div" class="p-0" role="tab">
                                                    <b-button block v-b-toggle.accordion-notes variant="secondary">{{ trans('hotel.label.notes') }}</b-button>
                                                </b-card-header>
                                                <b-collapse id="accordion-notes" accordion="my-accordion" role="tabpanel">
                                                    <b-card-body>
                                                        <div class="d-block"
                                                            style="font-size: 13px; line-height: 16px;"
                                                            v-html="hotel.summary.replace(/\n/g, '<br>')">
                                                        </div>
                                                    </b-card-body>
                                                </b-collapse>
                                            </b-card>
                                        </div>
                                    </div>

                                    <div class="col-lg-12 item-room"
                                         v-if="hotel.best_options.total_rate_amount">
                                        <div class="row col-lg-6"
                                            v-for="(busqueda,index_busqueda) in  hotel.best_options.rooms">
                                            <div class="row col-lg-12">
                                                <div class="col-lg-4">
                                                    <ul class="slides" v-if="busqueda.gallery.length > 0">
                                                        <template v-for="(gallery,index_image) in busqueda.gallery">
                                                            <input type="radio"
                                                                :name="'radio-btn_' + index_hotel + '_' + index_busqueda +
                                                                    'best'"
                                                                :id="'img-' + index_image + '_' + index_hotel + '_' +
                                                                    index_busqueda + 'best'"
                                                                :checked="index_image === 0" />
                                                            <li class="slide-container">
                                                                <div class="slide">
                                                                    <img loading="lazy" :src="gallery"
                                                                        style="border-radius: 2px;" />
                                                                </div>
                                                                <div class="nav">
                                                                    <label
                                                                        :for="'img-' + (busqueda.gallery.length - 1) + '_' +
                                                                        index_hotel + '_' + index_busqueda + 'best'"
                                                                        class="prev" v-if="index_image ===0"
                                                                        style="width:30%;height: 100%;line-height: normal; padding: 0">&#x2039;</label>
                                                                    <label
                                                                        :for="'img-' + (index_image - 1) + '_' + index_hotel +
                                                                            '_' + index_busqueda + 'best'"
                                                                        class="prev"
                                                                        style="width:30%;height: 100%;line-height: normal; padding: 0"
                                                                        v-else>&#x2039;</label>

                                                                    <label
                                                                        :for="'img-0' + '_' + index_hotel + '_' +
                                                                            index_busqueda + 'best'"
                                                                        class="next"
                                                                        v-if="index_image ===(busqueda.gallery.length -1)"
                                                                        style="width:30%;height: 100%;line-height: normal; padding: 0">&#x203a;</label>
                                                                    <label
                                                                        :for="'img-' + (index_image + 1) + '_' + index_hotel +
                                                                            '_' + index_busqueda + 'best'"
                                                                        class="next"
                                                                        style="width:30%;height: 100%;line-height: normal; padding: 0"
                                                                        v-else>&#x203a;</label>
                                                                </div>
                                                            </li>
                                                        </template>
                                                        <li class="nav-dots">
                                                            <label
                                                                :for="'img-' + index_image + '_' + index_hotel + '_' +
                                                                    index_busqueda + 'best'"
                                                                class="nav-dot"
                                                                :id="'img-dot-' + index_image + '_' + index_hotel + '_' +
                                                                    index_busqueda + 'best'"
                                                                v-for="(gallery,index_image) in busqueda.gallery"></label>
                                                        </li>
                                                    </ul>
                                                    <ul class="slides" v-else>
                                                        <li class="default"><img src="/images/hotel-default.jpg"></li>
                                                    </ul>
                                                </div>
                                                <div class="col-lg-8">
                                                    <div class="title-room">
                                                        @{{ busqueda.name }}
                                                    </div>
                                                    <div class="description-room">
                                                        <div>
                                                            <label><b>@{{ busqueda.quantity_adults }}</b>
                                                                {{ trans('hotel.label.adults') }}
                                                                <template
                                                                    v-if="busqueda.quantity_child >0"><b>@{{ busqueda.quantity_child }}</b>{{ trans('hotel.label.child') }}
                                                                </template>
                                                            </label>
                                                            <img src="/images/cama.png">
                                                            <template v-if="busqueda.rates[0].meal_id == 1">
                                                                <img src="/images/tasa.png"
                                                                    :alt="busqueda.rates[0].meal_name"
                                                                    :title="busqueda.rates[0].meal_name">
                                                                <img src="/images/tenedor.png"
                                                                    :alt="busqueda.rates[0].meal_name"
                                                                    :title="busqueda.rates[0].meal_name">
                                                                <img src="/images/timbre.png"
                                                                    :alt="busqueda.rates[0].meal_name"
                                                                    :title="busqueda.rates[0].meal_name">
                                                            </template>
                                                            <template v-if="busqueda.rates[0].meal_id == 2">
                                                                <img src="/images/tasa.png"
                                                                    :alt="busqueda.rates[0].meal_name"
                                                                    :title="busqueda.rates[0].meal_name">
                                                            </template>
                                                            <template v-if="busqueda.rates[0].meal_id == 3">
                                                                <img src="/images/tasa.png"
                                                                    :alt="busqueda.rates[0].meal_name"
                                                                    :title="busqueda.rates[0].meal_name">
                                                                <img src="/images/tenedor.png"
                                                                    :alt="busqueda.rates[0].meal_name"
                                                                    :title="busqueda.rates[0].meal_name">
                                                            </template>
                                                        </div>
                                                        <div>
                                                            {{--                                                            @{{ busqueda.rates[0].political.cancellation.name }} --}}
                                                        </div>
                                                        <div>
                                                            <a href="" class="ok">Ok</a>
                                                            <b class="politicy-name">@{{ busqueda.rates[0].name }}</b>
                                                            $<b>@{{ roundLito(busqueda.total_amount) }}</b>
                                                            <div class="row col-lg-12">
                                                                <div class="col-12">
                                                                    <a @click="showModalTarifa(index_hotel,index_busqueda,index_busqueda,1)"
                                                                        class="btn-Link-Underline">{{ trans('hotel.label.rate_details') }}</a>
                                                                </div>
                                                                <div class="col-12">
                                                                    <a @click="showModalPolicy(index_hotel,index_busqueda,index_busqueda,1)"
                                                                        class="btn-Link-Underline">{{ trans('hotel.label.policy_details') }}</a>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal fade tarifaSeleccion"
                                                    :id="'tarifaCombinacion_' + index_hotel + '_' + index_busqueda + '_' +
                                                        index_busqueda"
                                                    tabindex="-1">
                                                    <div class="modal-dialog" role="document">

                                                        <div class="modal-content">

                                                            <a href="" aria-label="Close" class="close"
                                                                data-dismiss="modal"
                                                                @click.prevent="hideShowModalTarifa(index_hotel,index_busqueda,index_busqueda,1)">
                                                                {{ trans('hotel.label.Close') }}<i
                                                                    aria-hidden="true">&times;</i>
                                                            </a>
                                                            <div class="modal-body">

                                                                <div v-for="rate in busqueda.rates">
                                                                    <div class="titleModalTarifa">
                                                                        @{{ busqueda.name }}
                                                                        <span>@{{ rate.name }}</span>
                                                                        <span
                                                                            v-if="rate.promotions_data.length>0">{{ trans('hotel.label.booking_window') }}:
                                                                            @{{ rate.promotions_data[0].promotion_from }} -
                                                                            @{{ rate.promotions_data[0].promotion_to }}</span>
                                                                        <span>(@{{ rate.rateProvider }})</span>
                                                                    </div>
                                                                    <table width="100%">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>{{ trans('hotel.label.date') }}</th>
                                                                                <th class="text-center">
                                                                                    {{ trans('hotel.label.adults') }}
                                                                                    (@{{ rate.rate.quantity_adults }})
                                                                                </th>
                                                                                <th class="text-center">
                                                                                    {{ trans('hotel.label.child') }}
                                                                                    (@{{ rate.rate.quantity_child }})
                                                                                </th>
                                                                                <th class="text-center">
                                                                                    Extra(@{{ rate.rate.quantity_extras }})
                                                                                </th>
                                                                                <th class="text-center no-border">
                                                                                    {{ trans('hotel.label.subtotal') }}
                                                                                </th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                        <tr v-for="amount_day in rate.rate.amount_days">
                                                                            <td>@{{amount_day.day }}</td>
                                                                            <td class="text-center">@{{
                                                                                getPrice(amount_day.total_adult) }}
                                                                            </td>
                                                                            <td class="text-center">@{{
                                                                                getPrice(amount_day.total_child) }}
                                                                            </td>
                                                                            <td class="text-center">@{{
                                                                                getPrice(amount_day.total_extra) }}
                                                                            </td>
                                                                            <td class="text-center no-border">
                                                                                @{{ getPrice(
                                                                                parseFloat(roundLito(amount_day.total_adult))
                                                                                +
                                                                                parseFloat(roundLito(amount_day.total_child))
                                                                                )
                                                                                }}
                                                                            </td>
                                                                        </tr>
                                                                        </tbody>
                                                                    </table>

                                                                    {{--                                                                    <template --}}
                                                                    {{--                                                                        v-if="rate.supplements.supplements.length > 0"> --}}

                                                                    {{--                                                                        <div class="titleModalTarifa" --}}
                                                                    {{--                                                                             style="margin-top: 24px;"> --}}
                                                                    {{--                                                                            {{ trans('hotel.label.supplement')}} --}}
                                                                    {{--                                                                        </div> --}}
                                                                    {{--                                                                        <table width="100%"> --}}
                                                                    {{--                                                                            <thead> --}}
                                                                    {{--                                                                            <tr> --}}
                                                                    {{--                                                                                <th class="text-center">{{ trans('hotel.label.supplement') }}</th> --}}
                                                                    {{--                                                                                <th class="text-center no-border">{{ trans('hotel.label.subtotal') }}</th> --}}
                                                                    {{--                                                                            </tr> --}}
                                                                    {{--                                                                            </thead> --}}
                                                                    {{--                                                                            <tbody> --}}
                                                                    {{--                                                                            <tr v-for="supplement in rate.supplements.supplements"> --}}
                                                                    {{--                                                                                <td>@{{ supplement.supplement }}</td> --}}
                                                                    {{--                                                                                <td class="text-center no-border"> --}}
                                                                    {{--                                                                                    @{{ roundLito(-- }}
                                                                    {{--                                                                                    supplement.total_amount) }} --}}
                                                                    {{--                                                                                </td> --}}
                                                                    {{--                                                                            </tr> --}}
                                                                    {{--                                                                            </tbody> --}}
                                                                    {{--                                                                        </table> --}}
                                                                    {{--                                                                    </template> --}}

                                                                    <table width="100%">
                                                                        <tr class="tarifa_total">
                                                                            <td colspan="5">{{ trans('hotel.label.subtotal') }}
                                                                                $<b>@{{ getPrice(parseFloat(rate.total
                                                                                    - rate.total_taxes_and_services))
                                                                                    }}</b></td>
                                                                        </tr>
                                                                        <tr class="tarifa_total">
                                                                            <td colspan="5">
                                                                                {{ trans('hotel.label.taxes') }}
                                                                                / {{ trans('hotel.label.services') }}
                                                                                $<b>@{{
                                                                                    getPrice(parseFloat(rate.total_taxes_and_services))
                                                                                    }}</b></td>
                                                                        </tr>
                                                                        <tr class="tarifa_total">
                                                                            <td colspan="5">Total $<b>@{{
                                                                                    getPrice(rate.total) }}</b></td>
                                                                        </tr>
                                                                    </table>
                                                                    <br />
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="modal fade tarifaSeleccion"
                                                    :id="'policyCombination_' + index_hotel + '_' + index_busqueda + '_' +
                                                        index_busqueda"
                                                    tabindex="-1">
                                                    <div class="modal-dialog" role="document">

                                                        <div class="modal-content">

                                                            <a href="" aria-label="Close" class="close"
                                                                data-dismiss="modal"
                                                                @click.prevent="hideShowModalPolicy(index_hotel,index_busqueda,index_busqueda,1)">
                                                                {{ trans('hotel.label.Close') }}<i
                                                                    aria-hidden="true">&times;</i>
                                                            </a>
                                                            <div class="modal-body"
                                                                style="padding: 2.5rem!important;border-radius: 1.5rem!important;">
                                                                <h3 class="policies-title mb-0"
                                                                    style="font-weight: bold !important;">
                                                                    {{ trans('hotel.label.policy_details') }}
                                                                </h3>
                                                                <div class="polices-modal">
                                                                    <p class="policies-title">
                                                                        <b>{{ trans('hotel.label.general_policy') }}</b>
                                                                    </p>
                                                                    <p class="polices-text">
                                                                        Check-in: @{{ hotel.checkIn }} Check out:
                                                                        @{{ hotel.checkOut }}</p>
                                                                    <p class="policies-title">
                                                                        <b>{{ trans('hotel.label.political_children') }}</b>
                                                                    </p>
                                                                    <p class="polices-text"
                                                                        v-if="hotel.political_children.child.allows_child == 1">
                                                                        <b>{{ trans('hotel.label.children') }}</b>
                                                                        {{ trans('hotel.label.from') }}
                                                                        @{{ hotel.political_children.child.min_age_child }}
                                                                        {{ trans('hotel.label.years') }}
                                                                        {{ trans('hotel.label.to') }}
                                                                        @{{ hotel.political_children.child.max_age_child }}
                                                                        {{ trans('hotel.label.years') }}
                                                                        - {{ trans('hotel.label.free_of_charge') }}
                                                                    </p>
                                                                    <p class="polices-text"
                                                                        v-if="hotel.political_children.child.allows_teenagers == 1">
                                                                        <b>{{ trans('hotel.label.infants') }}</b>
                                                                        @{{ hotel.political_children.infant.min_age_teenagers }}
                                                                        {{ trans('hotel.label.years') }}
                                                                        {{ trans('hotel.label.to') }}
                                                                        @{{ hotel.political_children.infant.max_age_teenagers }}
                                                                        {{ trans('hotel.label.years') }}
                                                                    </p>
                                                                    <p class="policies-text"
                                                                        v-if="busqueda.rates[0].supplements.supplements.length > 0">
                                                                        <b>{{ trans('hotel.label.additional_required') }}</b>
                                                                    </p>
                                                                    <ul
                                                                        v-if="busqueda.rates[0].supplements.supplements.length > 0">
                                                                        <li style="color: #EB5757;"
                                                                            v-for="(supplement) in busqueda.rates[0].supplements.supplements">
                                                                            @{{ supplement.supplement }}
                                                                        </li>
                                                                    </ul>
                                                                    <p class="polices-text">No Show:
                                                                        @{{ busqueda.rates[0].no_show }}</p>
                                                                    <p class="polices-text">Day Use:
                                                                        @{{ busqueda.rates[0].day_use }}</p>
                                                                    <p
                                                                        v-if="user_type_id == 3 && busqueda.rates[0].notes != undefined && busqueda.rates[0].notes != ''">
                                                                        <b>{{ trans('hotel.label.notes') }}</b>
                                                                    </p>
                                                                    <p class="polices-text"
                                                                        v-if="busqueda.rates[0].notes != undefined && busqueda.rates[0].notes != ''"
                                                                        style="white-space: pre-wrap;">
                                                                        <small>@{{ busqueda.rates[0].notes }}</small>
                                                                    </p>
                                                                    <p class="policies-title-sec mt-3">
                                                                        {{ trans('hotel.label.political_cancellation') }}
                                                                    </p>
                                                                    <div class="policies-text-sec date-cancel mt-3">
                                                                        @{{ busqueda.rates[0].political.cancellation.name }}
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="row best-price col-lg-12 text-right"
                                        v-if="hotel.best_options.total_rate_amount > 0">
                                        <div class="col-lg-10">
                                            <div class="col-lg-12">
                                                <div class="right">
                                                    <h6>$ <b>@{{ roundLito(hotel.best_options.total_rate_amount) }}</b></h6>
                                                </div>
                                                <div class="right">
                                                    {{ trans('hotel.label.the_best_price_available') }}
                                                    <p><b>@{{ hotel.best_options.quantity_rooms }}</b> {{ trans('hotel.label.rooms_for') }}
                                                        <b>@{{ hotel.best_options.quantity_adults }}</b>
                                                        {{ trans('hotel.label.adults') }}
                                                        <template v-if="hotel.best_options.quantity_child > 0"> y
                                                            @{{ hotel.best_options.quantity_child }} {{ trans('hotel.label.child') }}
                                                        </template>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-2">
                                            <button class="btn-primary" @click="addCartBestOption(index_hotel)"
                                                v-if="!hotel.best_option_taken">{{ trans('hotel.label.reserve') }}
                                            </button>
                                            <button class="btn-cancelar" @click="cancelBestOption(hotel)"
                                                v-else>{{ trans('hotel.label.cancel') }}
                                            </button>
                                        </div>
                                    </div>
                                    <div class="row col-lg-12">
                                        <div class="title-personality">
                                            <h4>{{ trans('hotel.label.custom_selection') }}</h4>
                                            <!--<select class="form-control">
                                                                                                                                                    <option value="">Mostrar todas las Hab.</option>
                                                                                                                                                </select>-->
                                            <label v-if="hotel.best_options.rooms && hotel.best_options.rooms.length>0">

                                                <div class="toggle">
                                                    <input type="checkbox" class="check-checkbox"
                                                        :id="'mytoggle-' + index_hotel" :value="true"
                                                        @click="seleccionPersonalizada(index_hotel)">
                                                    <label class="check-label" :for="'mytoggle-' + index_hotel">
                                                        <div id="background"></div>
                                                        <span class="face">
                                                            <span class="face-container">
                                                                <span class=" left"></span>
                                                                <span class=" right"></span>
                                                            </span>
                                                        </span>
                                                    </label>
                                                </div>
                                                {{ trans('hotel.label.see_all_rates') }}

                                            </label>
                                        </div>
                                        <div :id="'wrapper-personality-' + index_hotel"
                                            class="row col-lg-12 wrapper-personality"
                                            :style="(!hotel.best_options.rooms || hotel.best_options.rooms.length == 0) ?
                                            'display:block' : ''">
                                            <div class="row col-lg-12" v-for="(room,index_room) in  hotel.rooms">
                                                <div class="row col-lg-12">
                                                    <div class="col-lg-3">
                                                        <ul class="slides" style="margin: 0;width: 240px;height: 190px;"
                                                            v-if="room.gallery.length > 0">
                                                            <template v-for="(gallery,index_image) in room.gallery">
                                                                <input type="radio"
                                                                    :name="'radio-btn_' + index_hotel + '_' + index_room"
                                                                    :id="'img-' + index_image + '_' + index_hotel + '_' +
                                                                        index_room"
                                                                    :checked="index_image === 0" />
                                                                <li class="slide-container">
                                                                    <div class="slide"
                                                                        style="width: 240px;height: 130px;">
                                                                        <img :src="gallery"
                                                                            style="border-radius: 2px;" />
                                                                    </div>
                                                                    <div class="nav">
                                                                        <label
                                                                            :for="'img-' + (room.gallery.length - 1) + '_' +
                                                                            index_hotel + '_' + index_room"
                                                                            class="prev" v-if="index_image ===0"
                                                                            style="width:30%;height: 100%;line-height: normal; padding: 13% 0 0 0">&#x2039;</label>
                                                                        <label
                                                                            :for="'img-' + (index_image - 1) + '_' +
                                                                            index_hotel + '_' + index_room"
                                                                            class="prev"
                                                                            style="width:30%;height: 100%;line-height: normal; padding: 13% 0 0 0"
                                                                            v-else>&#x2039;</label>

                                                                        <label
                                                                            :for="'img-0' + '_' + index_hotel + '_' +
                                                                                index_room"
                                                                            class="next"
                                                                            v-if="index_image ===(room.gallery.length -1)"
                                                                            style="width:30%;height: 100%;line-height: normal; padding: 13% 0 0 0">&#x203a;</label>
                                                                        <label
                                                                            :for="'img-' + (index_image + 1) + '_' +
                                                                            index_hotel + '_' + index_room"
                                                                            class="next"
                                                                            style="width:30%;height: 100%;line-height: normal; padding: 13% 0 0 0"
                                                                            v-else>&#x203a;</label>
                                                                    </div>
                                                                </li>
                                                            </template>
                                                            <li class="nav-dots">
                                                                <label
                                                                    :for="'img-' + index_image + '_' + index_hotel + '_' +
                                                                        index_room"
                                                                    class="nav-dot"
                                                                    :id="'img-dot-' + index_image + '_' + index_hotel + '_' +
                                                                        index_room"
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
                                                        {{--                                                        <p>{{ trans('hotel.label.capacity') }} <img --}}
                                                        {{--                                                                src="/images/user.png" v-for="n in room.max_capacity"> --}}
                                                        {{--                                                        </p> --}}
                                                        {{--                                                        <h4 style="color: darkred" v-if="room.bed_additional == 1">{{ trans('hotel.label.bed_additional') }} </h4> --}}
                                                    </div>
                                                </div>
                                                <template v-for="(rate,index_rate) in room.rates">
                                                    <div class="row list-personality align-items-center">
                                                        <div class="col p-0">
                                                            <div class="row align-items-center mb-2">
                                                                <div class="pr-3">
                                                                    <button class="btn btn-success"
                                                                        v-if="rate.onRequest ==1">OK
                                                                    </button>
                                                                    <button class="btn btn-danger"
                                                                        v-if="rate.onRequest ==0">RQ
                                                                    </button>
                                                                </div>

                                                                <div class="row col-lg-10">
                                                                    <p><b>@{{ rate.name }}</b><br>
                                                                        (@{{ rate.rateProvider }}<?php if (Auth::user()->user_type_id == 3): ?> -
                                                                        @{{ rate.name_commercial }})<?php endif; ?><br>
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
                                                                    :id="'tarifa_' + index_hotel + '_' + index_room + '_' +
                                                                        index_rate"
                                                                    tabindex="-1">
                                                                    <div class="modal-dialog" role="document">
                                                                        <div class="modal-content">
                                                                            <a href="javascript:;" aria-label="Close"
                                                                                class="close" data-dismiss="modal"
                                                                                @click.prevent="hideShowModalTarifa(index_hotel,index_room,index_rate)">
                                                                                {{ trans('hotel.label.Close') }}<i
                                                                                    aria-hidden="true">&times;</i>
                                                                            </a>
                                                                            <div class="modal-body">
                                                                                <div v-for="rate_in in rate.rate">
                                                                                    <div class="titleModalTarifa">
                                                                                        @{{ room.name }}
                                                                                        <span>@{{ rate.name }}</span>
                                                                                        <span
                                                                                            v-if="rate.promotions_data.length>0">{{ trans('hotel.label.booking_window') }}:
                                                                                            @{{ rate.promotions_data[0].promotion_from }} -
                                                                                            @{{ rate.promotions_data[0].promotion_to }}</span>
                                                                                        <span>
                                                                                            (@{{ rate.rateProvider }})<br></span>
                                                                                    </div>
                                                                                    <table width="100%">
                                                                                        <thead>
                                                                                           <tr>
                                                                                                <th>
                                                                                                    <small>
                                                                                                    {{ trans('hotel.label.date') }}
                                                                                                    </small>
                                                                                                </th>
                                                                                                <th class="text-center">
                                                                                                    <small>{{ trans('hotel.label.adults') }}
                                                                                                    (@{{
                                                                                                    roundLito(rate_in.quantity_adults_total)
                                                                                                    }})
                                                                                                    </small>
                                                                                                </th>
                                                                                                <th class="text-center">
                                                                                                    <small>
                                                                                                    {{ trans('hotel.label.child') }}
                                                                                                    (@{{
                                                                                                    roundLito(rate_in.quantity_child_total)
                                                                                                    }})
                                                                                                    </small>
                                                                                                </th>
                                                                                                </th>
                                                                                                <th class="text-center no-border">
                                                                                                    <small>Subtotal</small>
                                                                                                </th>
                                                                                            </tr>
                                                                                        </thead>
                                                                                        <tbody>
                                                                                            <tr v-for="(amount_day, kd) in rate_in.amount_days">
                                                                                                <td>
                                                                                                    <div class="d-flex align-items-center justify-content-between">
                                                                                                        <div class="col p-0">
                                                                                                            <small><b>@{{amount_day.date }}</b></small>
                                                                                                        </div>
                                                                                                        <div class="col-auto p-0">

                                                                                                            <template v-if="rate.rateProviderMethod && rate.rateProviderMethod == 2">
                                                                                                                <button class="btn btn-success" type="button" data-toggle="tooltip" :title="`1 {{ trans('hotel.label.available') }}`">
                                                                                                                    ok
                                                                                                                </button>
                                                                                                            </template>
                                                                                                            <template v-else>
                                                                                                                <button class="btn btn-success" type="button" data-toggle="tooltip"
                                                                                                                    :title="`${rate.political.rate.example.inventories?.[kd]?.inventory_num} {{ trans('hotel.label.available') }}`"
                                                                                                                    v-if="rate.political.rate.example.inventories?.[kd]?.inventory_num >= search_destinies[destiny_id].quantity_rooms">
                                                                                                                    OK
                                                                                                                </button>
                                                                                                                <button class="btn btn-danger" type="button"v-else>
                                                                                                                    RQ
                                                                                                                </button>
                                                                                                            </template>

                                                                                                        </div>
                                                                                                    </div>
                                                                                                </td>
                                                                                                <td class="text-center">
                                                                                                    @{{
                                                                                                    (amount_day.rate && amount_day.rate[0]) ? getPrice(parseFloat(amount_day.rate[0].total_adult) + parseFloat(amount_day.rate[0].total_extra)) : 0
                                                                                                    }}
                                                                                                </td>
                                                                                                <td class="text-center">
                                                                                                    @{{
                                                                                                    (amount_day.rate && amount_day.rate[0]) ? getPrice(amount_day.rate[0].total_child) : 0
                                                                                                    }}
                                                                                                </td>
                                                                                                <td class="text-center no-border">
                                                                                                    @{{
                                                                                                    (amount_day.rate && amount_day.rate[0]) ? getPrice(parseFloat(amount_day.rate[0].total_adult) + parseFloat(amount_day.rate[0].total_child) + parseFloat(amount_day.rate[0].total_extra)) : 0
                                                                                                    }}
                                                                                                </td>
                                                                                            </tr>
                                                                                        </tbody>
                                                                                    </table>
                                                                                    <table width="100%">
                                                                                        <tr class="tarifa_total">
                                                                                            <td colspan="5">
                                                                                                Total
                                                                                                {{ trans('hotel.label.subtotal') }}
                                                                                                $<b>@{{ getPrice(parseFloat(rate_in.total_amount -
    rate_in.total_taxes_and_services)) }}</b>
                                                                                            </td>
                                                                                        </tr>
                                                                                        <tr class="tarifa_total">
                                                                                            <td colspan="5">
                                                                                                {{ trans('hotel.label.taxes') }}
                                                                                                /
                                                                                                {{ trans('hotel.label.services') }}
                                                                                                $<b>@{{ getPrice(parseFloat(rate_in.total_taxes_and_services)) }}</b>
                                                                                            </td>
                                                                                        </tr>
                                                                                        <tr class="tarifa_total">
                                                                                            <td colspan="5">Total
                                                                                                $<b>@{{ getPrice(rate_in.total_amount) }}</b>
                                                                                            </td>
                                                                                        </tr>
                                                                                    </table>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="modal fade tarifaSeleccion"
                                                                    :id="'policy_' + index_hotel + '_' + index_room + '_' +
                                                                        index_rate"
                                                                    tabindex="-1">
                                                                    <div class="modal-dialog" role="document">

                                                                        <div class="modal-content">
                                                                            <a href="" aria-label="Close"
                                                                                class="close" data-dismiss="modal"
                                                                                @click.prevent="hideShowModalPolicy(index_hotel,index_room,index_rate)">
                                                                                {{ trans('hotel.label.Close') }}<i
                                                                                    aria-hidden="true">&times;</i>
                                                                            </a>
                                                                            <div class="modal-body"
                                                                                style="padding: 2.5rem!important;border-radius: 1.5rem!important;">
                                                                                <h3 class="policies-title mb-0"
                                                                                    style="font-weight: bold !important;">
                                                                                    {{ trans('hotel.label.policy_details') }}
                                                                                </h3>
                                                                                <div class="polices-modal">
                                                                                    <template  v-if="rate.rateProvider === 'HYPERGUEST'">
                                                                                        <p class="policies-title">
                                                                                            <b>Taxes and Fees</b>
                                                                                        </p>
                                                                                        <p class="policies-text" v-if="rate.display_taxes">
                                                                                            @{{ rate.display_taxes.description }}
                                                                                        </p>
                                                                                    </template>
                                                                                    <p class="policies-text" v-if="rate.display_taxes">Total:
                                                                                        @{{ rate.display_taxes.amount }}
                                                                                        @{{ rate.display_taxes.currency }}
                                                                                    </p>
                                                                                    <p class="policies-title">
                                                                                        <b>{{ trans('hotel.label.general_policy') }}</b>
                                                                                    </p>
                                                                                    <p class="policies-text">Check-in:
                                                                                        @{{ hotel.checkIn }} Check out :
                                                                                        @{{ hotel.checkOut }}</p>
                                                                                    <p class="policies-title">
                                                                                        <b>{{ trans('hotel.label.political_children') }}</b>
                                                                                    </p>
                                                                                    <p class="policies-text"
                                                                                        v-if="hotel.political_children.child.allows_child == 1">
                                                                                        <b>{{ trans('hotel.label.children') }}</b>
                                                                                        @{{ hotel.political_children.child.min_age_child }}
                                                                                        {{ trans('hotel.label.years') }}
                                                                                        {{ trans('hotel.label.to') }}
                                                                                        @{{ hotel.political_children.child.max_age_child }}
                                                                                        {{ trans('hotel.label.years') }}
                                                                                        @{{ rate.political.no_show_apply ? rate.political.no_show_apply.political_child : '' }}
                                                                                    </p>
                                                                                    <p class="policies-text"
                                                                                        v-if="hotel.political_children.child.allows_teenagers == 1">
                                                                                        <b>{{ trans('hotel.label.infants') }}</b>
                                                                                        @{{ hotel.political_children.infant.min_age_teenagers }}
                                                                                        {{ trans('hotel.label.years') }}
                                                                                        {{ trans('hotel.label.to') }}
                                                                                        @{{ hotel.political_children.infant.max_age_teenagers }}
                                                                                        {{ trans('hotel.label.years') }}
                                                                                        @{{ rate.political.no_show_apply ? rate.political.no_show_apply.political_child : '' }}
                                                                                    </p>
                                                                                    <p class="policies-text"
                                                                                        v-if="rate.supplements.supplements.length > 0">
                                                                                        <b>{{ trans('hotel.label.additional_required') }}</b>
                                                                                    </p>
                                                                                    <ul
                                                                                        v-if="rate.supplements.supplements.length > 0">
                                                                                        <li style="color: #EB5757;"
                                                                                            v-for="(supplement) in rate.supplements.supplements">
                                                                                            @{{ supplement.supplement }}
                                                                                        </li>
                                                                                    </ul>
                                                                                    <p class="policies-text">No Show:
                                                                                        @{{ rate.no_show }}</p>
                                                                                    <p class="policies-text">Day Use:
                                                                                        @{{ rate.day_use }}</p>
                                                                                    <p
                                                                                        v-if="rate.notes != undefined && rate.notes != '' && user_type_id == 3">
                                                                                        <b>{{ trans('hotel.label.notes') }}</b>
                                                                                    </p>
                                                                                    <p class="policies-text"
                                                                                        v-if="rate.notes != undefined && rate.notes != '' && user_type_id == 3"
                                                                                        style="white-space: pre-wrap;">
                                                                                        @{{ rate.notes }}
                                                                                    </p>
                                                                                    <p class="policies-title-sec mt-3">
                                                                                        {{ trans('hotel.label.political_cancellation') }}
                                                                                    </p>
                                                                                    <div
                                                                                        class="policies-text-sec date-cancel mt-3">
                                                                                        @{{ rate.political.cancellation.name }}
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="d-block">
                                                                <div class="d-block">
                                                                    <a data-toggle="modal"
                                                                        :data-target="'tarifa_' + '_' + index_room + '_index_rate'">
                                                                        {{ trans('hotel.label.rate_details') }}
                                                                    </a>
                                                                    <a @click="showModalTarifa(index_hotel,index_room,index_rate)"
                                                                        class="btn-Link-Underline">{{ trans('hotel.label.see_detail') }}</a>
                                                                </div>
                                                                <div class="d-block">
                                                                    <a data-toggle="modal"
                                                                        :data-target="'policy_' + '_' + index_room + '_index_rate'">
                                                                        {{ trans('hotel.label.policy_details') }}
                                                                    </a>
                                                                    <a @click="showModalPolicy(index_hotel,index_room,index_rate)"
                                                                        class="btn-Link-Underline">{{ trans('hotel.label.see_detail') }}</a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-auto p-0"
                                                            :style="rate.taken ? 'pointer-events: none;' : ''">
                                                            <div class="row align-items-center">
                                                                <div class="col-auto" v-if="rate.rateProvider=='Amadeus'">
                                                                    <div class="row align-items-center">
                                                                        <label class="col-auto p-0"
                                                                            v-if="rate.onRequest == 1">
                                                                            <span
                                                                                class="d-block">{{ trans('hotel.label.rooms') }}</span>
                                                                            <span class="d-block">@{{ rate.available }}
                                                                                {{ trans('hotel.label.available') }}</span>
                                                                        </label>
                                                                        <label class="col-auto p-0"
                                                                            v-if="rate.onRequest == 0">
                                                                            <span class="d-block">
                                                                                {{ trans('hotel.label.without_inventory') }}
                                                                            </span>
                                                                        </label>
                                                                        <div class="col-auto">
                                                                            <div class="form-control position-relative">
                                                                                @{{ search_destinies[destiny_id].quantity_rooms }}
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <template v-if="rate.rateProvider=='Amadeus'">
                                                                    <div class="col-auto">
                                                                        <div class="row align-items-center">
                                                                            <label
                                                                                class="col-auto p-0">{{ trans('hotel.label.adults') }}</label>
                                                                            <div class="col-auto">
                                                                                <div
                                                                                    class="form-control position-relative">
                                                                                    @{{ search_destinies[destiny_id].quantity_adults }}
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-auto">
                                                                        <div class="row align-items-center">
                                                                            <label
                                                                                class="col-auto p-0">{{ trans('hotel.label.child') }}</label>
                                                                            <div class="col-auto">
                                                                                <div
                                                                                    class="form-control position-relative">
                                                                                    @{{ search_destinies[destiny_id].quantity_child }}
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-auto text-right">
                                                                        <div class="row align-items-center">
                                                                            <label
                                                                                class="col-auto p-0">{{ trans('hotel.label.beds') }}
                                                                                <img src="/images/cama.png"></label>
                                                                            <div class="price col-auto">
                                                                                $ <b>@{{ getPrice(
    rate.rate[0].total_amount) }}</b>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </template>
                                                                <div class="col-auto p-0"
                                                                    v-if="rate.rateProvider!='Amadeus'">
                                                                    <div class="row align-items-center">
                                                                        <label class="col-auto p-0"
                                                                            v-if="rate.onRequest == 1">
                                                                            <span
                                                                                class="d-block">{{ trans('hotel.label.rooms') }}</span>
                                                                            <span class="d-block">@{{ rate.available }}
                                                                                {{ trans('hotel.label.available') }}
                                                                            </span>
                                                                        </label>
                                                                        <label class="col-auto p-0"
                                                                            v-if="rate.onRequest == 0">
                                                                            <span class="d-block">
                                                                                {{ trans('hotel.label.without_inventory') }}
                                                                            </span>
                                                                        </label>
                                                                        <div class="col-auto">
                                                                            <select class="form-control position-relative"
                                                                                v-model="rate.quantity_rates"
                                                                                @change.stop="generateNewRate(hotel,room,rate)">
                                                                                <option :value="n"
                                                                                    v-for="n in rate.available"
                                                                                    :selected="n === 1">
                                                                                    @{{ n }}
                                                                                </option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-auto p-0">
                                                                    <div :class="['row align-items-center', n > 1 ? 'mt-3' : '', rate
                                                                        .quantity_rates > 1 ? 'bg-white pl-4 py-2' : ''
                                                                    ]"
                                                                        v-for="n in rate.quantity_rates"
                                                                        :style="(rate.quantity_rates > 1) ?
                                                                        `border-radius: 6px; border: 1px dashed #ddd;` :
                                                                        ``"
                                                                        v-if="rate.rateProvider!='Amadeus'">
                                                                        <div class="col-auto p-0">
                                                                            <div class="row align-items-center">
                                                                                <label
                                                                                    class="col-auto p-0">{{ trans('hotel.label.adults') }}</label>
                                                                                <label class="text-select col-auto"
                                                                                    style="font-size: 1.6rem;color:red; font-weight: bold;"
                                                                                    v-if=" (rate.rate[n-1].quantity_adults == search_destinies[destiny_id].quantity_persons_rooms[0].adults ) &&  (search_destinies[destiny_id].quantity_persons_rooms[0].adults > room.max_adults)">
                                                                                    @{{ search_destinies[destiny_id].quantity_persons_rooms[0].adults }}
                                                                                </label>
                                                                                <div class="col-auto">
                                                                                    <select
                                                                                        class="form-control position-relative"
                                                                                        v-model="rate.rate[n-1].quantity_adults"
                                                                                        @change.prevent="calculateSelectionTotalRate(hotel.id,room.room_id,rate.rateId,rate,n-1,rate.ratePlanId)">
                                                                                        <option :value="0">0
                                                                                        </option>
                                                                                        <option :value="n_pax"
                                                                                            v-for="n_pax in room.max_adults">
                                                                                            @{{ n_pax }}
                                                                                        </option>
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-auto p-0">
                                                                            <div class="row align-items-center">
                                                                                <label
                                                                                    class="col-auto p-0">{{ trans('hotel.label.child') }}</label>
                                                                                <label class="text-select col-auto"
                                                                                    style="font-size: 1.6rem;color:red; font-weight: bold;"
                                                                                    v-if=" (rate.rate[n-1].quantity_child == search_destinies[destiny_id].quantity_persons_rooms[0].child) && (search_destinies[destiny_id].quantity_persons_rooms[0].child > room.max_capacity)">
                                                                                    @{{ search_destinies[destiny_id].quantity_persons_rooms[0].child }}
                                                                                </label>

                                                                                <div class="col-auto">
                                                                                    <select
                                                                                        class="form-control position-relative"
                                                                                        v-model="rate.rate[n-1].quantity_child"
                                                                                        @change="calculateSelectionTotalRate(hotel.id,room.room_id,rate.rateId,rate,n-1,rate.ratePlanId)">
                                                                                        <option :value="0">0
                                                                                        </option>
                                                                                        <option :value="n"
                                                                                            v-for="n in room.max_capacity">
                                                                                            @{{ n }}
                                                                                        </option>
                                                                                    </select>
                                                                                </div>

                                                                                <template
                                                                                    v-if="rate.rate[n-1].ages_child.length>0">
                                                                                    <label
                                                                                        class="col-auto p-0">Edades</label>

                                                                                    <div class="col-auto">
                                                                                        <div class="row"
                                                                                            style="gap: 10px;">
                                                                                            <template
                                                                                                v-for="age_child in rate.rate[n-1].ages_child"
                                                                                                v-if="rate.rateProvider!='Amadeus'">
                                                                                                <select
                                                                                                    class="form-control position-relative"
                                                                                                    v-model="age_child.age"
                                                                                                    @change="calculateSelectionTotalRate(hotel.id,room.room_id,rate.rateId,rate,n-1,rate.ratePlanId)">
                                                                                                    <option
                                                                                                        :value="0">
                                                                                                        0</option>
                                                                                                    <option
                                                                                                        :value="nm"
                                                                                                        v-for="nm in 18">
                                                                                                        @{{ nm }}
                                                                                                    </option>
                                                                                                </select>
                                                                                            </template>
                                                                                        </div>
                                                                                    </div>
                                                                                </template>
                                                                            </div>
                                                                        </div>

                                                                        <div class="col-auto p-0 text-right">
                                                                            <div class="row align-items-center">
                                                                                <label
                                                                                    class="col-auto p-0">{{ trans('hotel.label.beds') }}
                                                                                    <img src="/images/cama.png"></label>
                                                                                <div class="price col-auto position-relative"
                                                                                    style="top: auto; right: auto;">
                                                                                    <span
                                                                                        v-if="rate.rate[n-1].total_amount>0">
                                                                                        <span
                                                                                            v-if="rate.rate[n-1].flag_migrate==1">
                                                                                            $
                                                                                            <b> @{{ getPrice(rate.rate[n - 1].total_amount) }}
                                                                                            </b>
                                                                                        </span>
                                                                                        <span v-else style="color: green">
                                                                                            $
                                                                                            <b> @{{ getPrice(rate.rate[n - 1].total_amount) }}
                                                                                            </b>
                                                                                        </span>
                                                                                    </span>
                                                                                    <span v-else
                                                                                        style="color:red;font-weight: bold">
                                                                                        <!-- svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-ban" viewBox="0 0 16 16">
                                                                                                                                                                                                <path d="M15 8a6.97 6.97 0 0 0-1.71-4.584l-9.874 9.875A7 7 0 0 0 15 8M2.71 12.584l9.874-9.875a7 7 0 0 0-9.874 9.874ZM16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0"/>
                                                                                                                                                                                            </svg -->
                                                                                        !! Error !!
                                                                                    </span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-2 text-right p-0">
                                                            <button class="btn-primary btnAddCart"
                                                                :disabled="rate.message_error !== '' || unlockRate(room,
                                                                    rate)"
                                                                @click="addCart(hotel.id,room.room_id,rate.rateId,rate,hotel.name,room.name,index_hotel,room)"
                                                                v-if="!rate.taken">
                                                                {{ trans('hotel.label.reserve') }}
                                                            </button>
                                                            <button class="btn btn-cancelar w-auto" style="height:52px;"
                                                                @click="cancelRates(rate)" v-else>
                                                                {{ trans('hotel.label.cancel') }}
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3" v-if="rate.show_message_error">
                                                        <div class="d-block">
                                                            <p class="font-weight-bold text-danger px-3"
                                                                v-show="rate.show_message_error">
                                                                *{{ trans('quote.label.observations') }}:
                                                            </p>
                                                            <p class="badge badge-danger danger-date mr-2 mb-2 mx-3"
                                                                style="font-size: 12px;" v-show="rate.show_message_error">
                                                                @{{ rate.message_error }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="result-hotels-maps" v-if="search_destinies[destiny_id].hotels.length >0">
                        <div id="result-hotels-maps-item"></div>
                        <div id="result-hotels-maps-view" class="hotel-mapa">
                            <div class="entra"></div>
                            <div v-if="search_destinies[destiny_id].hotels.length > 0">
                                <div :id="'boton_mapa_result' + index_hotel" class="botonMapa"
                                    v-for="(hotel,index_hotel) in search_destinies[destiny_id].hotels">
                                    <a href="/#/" @click.prevent="showRoomsHotel(index_hotel)"
                                        class="btn-primary ">{{ trans('hotel.label.select') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <b-modal ref="my-modal" hide-footer centered size="lg" class="text-center">
                        <img :src="baseExternalURL + 'images/' + image_modal" alt="">
                    </b-modal>
                    <div id="my-modal-google-maps" class="modal bs-example-modal-lg" tabindex="-1" role="dialog">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <a @click="hideModalGoogleMaps" aria-label="Close" class="close" data-dismiss="modal">
                                    {{ trans('hotel.label.Close') }}<i aria-hidden="true">&times;</i>
                                </a>
                                <div class="modal-body">
                                    <div id="showMap" style="width: 100%; height: 500px;"></div>

                                    <div class="hotel-mapa" id="app_mapa">
                                        <div class="entra"></div>

                                        <div v-if="search_destinies[destiny_id].hotels.length > 0">
                                            <div :id="'boton_mapa_' + index_hotel" class="botonMapa"
                                                v-for="(hotel,index_hotel) in search_destinies[destiny_id].hotels">
                                                <a href="/#/" @click.prevent="showRoomsHotel(index_hotel,1)"
                                                    class="btn-primary ">{{ trans('hotel.label.select') }}</a>
                                            </div>
                                        </div>

                                        <!--<img src="http://127.0.0.1:8000/images/e11a3747766285c35e7765e388182955.png">
                                                                                                                                            <div class="hotel-mapa-content">
                                                                                                                                                <div class="title-mapa">Hotel Ejemplo
                                                                                                                                                    <img src="/images/mapa.png">
                                                                                                                                                    <div><span class="fa-star-0 fa"></span><span class="fa-star fa"></span></div>
                                                                                                                                                </div>
                                                                                                                                                <p>Dirección:<br>
                                                                                                                                                Sta. Catalina Agosta 125, Cusco, Pe</p>
                                                                                                                                                <p>Teléfono:<br>
                                                                                                                                                +51 (84) 233-2334</p>
                                                                                                                                                <p><a href="">Detalles del hotel</a></p>
                                                                                                                                                <div class="price">
                                                                                                                                                    <p>Tarifa desde <span class="fas fa-dollar-sign"></span><b>42</b></p>
                                                                                                                                                    <a href="/#/" class="btn-primary">ver tarifa</a>
                                                                                                                                                </div>
                                                                                                                                            </div>-->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </template>
            </div>
        </div>
    </section>
    <section v-else>
        <div class="jumbotron bg-danger">
            <h2 class="text-center text-white"><i class="fas fa-exclamation-triangle"></i>
                {{ trans('package.label.you_must_select_customer') }}
                ... <i class="fas fa-hand-point-up"></i></h2>
        </div>
    </section>

    @include('hotels.modal')
@endsection
@section('css')
    <style>
        .tag-districts {
            margin-right: 10px;
            padding: 10px;
            background: #f2faff;
            border-radius: 5px;
            border: solid 1px #ebebeb;
        }

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

        /*end*/
        /*estilos de galeria*/
        .slides {
            padding: 0;
            width: 100%;
            height: 215px;
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
            width: 15%;
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

        .slide:hover+.nav label {
            opacity: 0.5;
        }

        .nav label:hover {
            opacity: 1;
        }

        .nav .next {
            right: 0;
        }

        input:checked+.slide-container .slide {
            opacity: 1;

            transform: scale(1);

            transition: opacity 1s ease-in-out;
        }

        input:checked+.slide-container .nav label {
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

        input#img-1:checked~.nav-dots label#img-dot-1,
        input#img-2:checked~.nav-dots label#img-dot-2,
        input#img-3:checked~.nav-dots label#img-dot-3,
        input#img-4:checked~.nav-dots label#img-dot-4,
        input#img-5:checked~.nav-dots label#img-dot-5,
        input#img-6:checked~.nav-dots label#img-dot-6 {
            background: rgba(0, 0, 0, 0.8);
        }

        /*end*/

        .content-hotel .description .services .text {
            display: flex;
            align-items: center;
            justify-content: start;
            padding: 2px 11px;
            min-width: auto;
            border: 1px solid #eee;
            margin-right: 0.5rem;
            /* margin-bottom: 1rem; */
            border-radius: 5px;
            font-weight: 600;
            font-size: 1.2rem;
            cursor: pointer;
            transition: background 0.2s ease-in;
            height: 28px;
            margin-top: -2px;
        }

        .content-hotel .description .services .text:hover {
            background: bisque;
        }
    </style>
@endsection
@section('js')
    <script type='text/javascript'
        src='https://maps.google.com/maps/api/js?language=es&key=AIzaSyAyJEO088PaVG6e2KSOOQrF5U4y_O580BU&callback=showGoogleMaps'>
    </script>
    <script type="text/javascript" src="{{ asset('js/gmaps.js') }}"></script>
    <script>
        new Vue({
            el: "#app",
            data: {
                flag_search: false,
                referer: {{ $refererEjecute }},
                timePicker24Hour: false,
                showWeekNumbers: false,
                singleDatePicker: true,
                msg: "{{ trans('hotel.label.please_loading') }}",
                user_type_id: localStorage.getItem("user_type_id"),
                blockPage: false,
                rate_calculate_error: false,
                rate_calculate_message: "",
                baseExternalURL: window.baseExternalURL,
                baseURL: window.baseURL,
                dateRange: "",
                client_id: null,
                clients: [],
                image_modal: "",
                hotels: [],
                search_destinies_save: [],
                pagination: {
                    total: 0,
                    per_page: 2,
                    from: 1,
                    to: 0,
                    last_page: 1,
                    current_page: 1
                },
                cart: {
                    cart_content: [],
                    hotels: [],
                    total_cart: 0.00,
                    quantity_items: 0
                },
                pagesNumbers: 1,
                offset: 4,
                tabsDestinies: false,
                destiny_id: 0,
                startDate: moment().add("days", 2).format("Y-MM-DD"),
                minDate: moment().add("days", 2).format("Y-MM-DD"),
                minDateToday: moment().add("days", 2).format("Y-MM-DD"),
                locale_data: {
                    direction: "ltr",
                    format: moment.localeData().postformat("ddd D MMM"),
                    separator: " - ",
                    applyLabel: '{{ trans('hotel.label.save') }}',
                    cancelLabel: '{{ trans('hotel.label.cancel') }}',
                    weekLabel: "W",
                    customRangeLabel: '{{ trans('hotel.label.date_range') }}',
                    daysOfWeek: moment.weekdaysMin(),
                    monthNames: moment.monthsShort(),
                    firstDay: moment.localeData().firstDayOfWeek()
                },
                tabDestinies: false,
                search_destinies: [{
                    loader: false,
                    token_search: "",
                    typeclass_id: "all",
                    hotels_search_code: "",
                    showContainerQuantityPersons: false,
                    class_container_rooms: "container_quantity_persons_rooms width_default_container",
                    class_container_select: "container_quantity_persons_rooms_select width_default_select",

                    destiny_country: {
                        code: 89,
                        label: "Perú"
                    },
                    destinations_select: [],
                    destiny: "",
                    destinations_additional_select: [],
                    destiny_district: "",

                    quantity_rooms: 1,
                    quantity_persons: 2,
                    quantity_adults: 2,
                    quantity_child: 0,
                    quantity_persons_rooms: [{
                        room: 1,
                        adults: 2,
                        child: 0,
                        ages_child: [{
                            child: 1,
                            age: 1
                        }]
                    }],
                    dateRange: "",
                    startDate: "",
                    endDate: "",
                    hotel_selected: "",
                    hotels: [],
                    active: true,
                    rangePrice: [0, 0],
                    max_price_search: 100,
                    hotels_original: [],
                    class: [],
                    zones: [],
                    filter_by_name: "",
                    quantity_hotels: 0
                }],
                destinations_countries_select: [],
                destinations_select_universe: [],
                destinations_additional_select_universe: [],

                check_all_stars: false,
                check_all_class: false,
                check_all_zones: false,
                stars: [{
                        star: 5,
                        status: false
                    },
                    {
                        star: 4,
                        status: false
                    },
                    {
                        star: 3,
                        status: false
                    },
                    {
                        star: 2,
                        status: false
                    },
                    {
                        star: 1,
                        status: false
                    }
                ],
                classes_hotel: [],
                cart_quantity_items: 0,
                routeExcel: "",
                service_year: "2020",
                modal: {
                    title: "",
                    details: ""
                },
                loading_form: false,
                flag_hotel_room: null,
                client : {}
            },
            created: function() {
                this.client_id = localStorage.getItem("client_id");
                console.log("client_id:" + this.client_id);
                if (this.client_id == null) {
                    window.location = this.baseURL + "home";
                }else {
                    this.getClient();
                }
                localStorage.setItem("reservation", false);

                this.$root.$on("cancelcartmodal", function(payload) {
                    this.cancelSelectionCartInModal(payload.room_id, payload.hotel);
                    this.getCartContent();
                });
                this.$root.$on("updatedestiniesandclass", async () => {
                    console.log("Buscando destinos..")

                    this.loading_form = true;

                    this.client_id = localStorage.getItem("client_id");
                    this.tabsDestinies = false;
                    this.clearDestinies();

                    await Promise.all([
                        this.getDestiniesByClientId(),
                        this.getClassHotelByClientId(),
                        this.getCartContent(),
                    ])

                    this.loading_form = false;
                });

                this.getCartContent();
            },
            mounted() {

                this.calculateNumPersonsPerRooms(this.destiny_id);
                this.getSearchDestinies();
                this.getFiltersFile();

                if (localStorage.getItem("search_params")) {
                    if (localStorage.getItem("search_params") !== "" && localStorage.getItem("search_params") !=
                        null && this.referer === 2) {

                        let params = JSON.parse(localStorage.getItem("search_params"));

                        if (params.module == "hotels") {

                            this.blockPage = true;

                            this.search_destinies[0].destiny = params.destiny.destiny;
                            this.search_destinies[0].destiny_country = params.destiny.destiny_country;
                            this.search_destinies[0].destiny_district = params.destiny.destiny_district;
                            this.search_destinies[0].typeclass_id = params.typeclass_id;
                            this.search_destinies[0].hotels_search_code = params.hotels_search_code;
                            this.search_destinies[0].dateRange = {
                                startDate: params.startDate,
                                endDate: params.endDate
                            };

                            this.search_destinies[0].quantity_adults = params.quantity_adults;
                            this.search_destinies[0].quantity_child = params.quantity_child;
                            this.search_destinies[0].quantity_persons = params.quantity_persons;
                            this.search_destinies[0]["quantity_rooms"] = params.quantity_rooms;
                            this.search_destinies[0]["quantity_persons_rooms"] = params.quantity_persons_rooms;

                            setTimeout(() => {
                                this.change_destiny_districts(0);
                                this.$forceUpdate();
                                this.search_destinies[0].destiny_district = params.destiny.destiny_district;
                                this.searchDestinies();
                            }, 2000);

                        }

                    }
                }
            },
            computed: {},
            methods: {
                unlockRate: function(room, rate) {
                    /*
                    if(room.min_adults >= rate.num_adult && room.max_adults <= rate.num_adult)
                    {
                        return true;
                    }
                    */

                    return false;
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
                formatPolicyText(text) {
                    if (!text) return text;
                    return text.replace(/USD\s?([\d,]+\.?\d*)/g, (match, p1) => {
                        let price = parseFloat(p1.replace(/,/g, ''));
                        let newPrice = this.getPrice(price);
                        return 'USD ' + newPrice.toFixed(2);
                    });
                },
                processHotelsPolicies(hotels) {
                    hotels.forEach(hotel => {
                        if (hotel.rooms) {
                            hotel.rooms.forEach(room => {
                                if (room.rates) {
                                    room.rates.forEach(rate => {
                                        if (rate.political && rate.political.cancellation && rate.political.cancellation.name) {
                                            rate.political.cancellation.name = this.formatPolicyText(rate.political.cancellation.name);
                                        }
                                    });
                                }
                            });
                        }
                        if (hotel.best_options && hotel.best_options.rooms) {
                            hotel.best_options.rooms.forEach(room => {
                                if (room.rates) {
                                    room.rates.forEach(rate => {
                                        if (rate.political && rate.political.cancellation && rate.political.cancellation.name) {
                                            rate.political.cancellation.name = this.formatPolicyText(rate.political.cancellation.name);
                                        }
                                    });
                                }
                            });
                        }
                    });
                },
                openModalDetail: function(title, details) {
                    this.modal.title = title;
                    this.modal.details = details;
                    $(".modal-servicios").modal();
                },
                closeModalDetail: function() {
                    document.getElementById("modal_detail_close").click();
                },
                change_destiny_districts(index_search_destiny) {
                    this.search_destinies[index_search_destiny].destinations_additional_select = [];
                    this.search_destinies[index_search_destiny].destiny_district = "";

                    this.destinations_additional_select_universe.forEach((d_u) => {
                        if (d_u.parent_code && d_u.parent_code == this.search_destinies[
                                index_search_destiny].destiny.code) {
                            this.search_destinies[index_search_destiny].destinations_additional_select.push(
                                d_u);
                        }
                    });
                },
                getFiltersFile: function() {
                    axios.get(baseURL + "filter_hotels_file")
                        .then((result) => {
                            let items = Object.entries(result.data);
                            if (items.length > 0) {
                                let d = result.data.destiny;
                                let code_split = d.code.split(",");
                                let label_split = d.label.split(",");

                                // this.destinations_countries_select.code = code_split[0]
                                // this.destinations_countries_select.label = label_split[0].trim()
                                //
                                // this.search_destiny.destinations_select.code =  code_split[1]
                                // this.search_destiny.destinations_select.label =  label_split[1].trim()

                                this.search_destinies[0].destiny = {
                                    code: code_split[1],
                                    label: label_split[1].trim()
                                };
                                this.search_destinies[0].dateRange = {
                                    startDate: result.data.date_from,
                                    endDate: result.data.date_to
                                };
                                this.search_destinies[0].quantity_persons_rooms = result.data
                                    .quantity_persons_rooms;
                                this.search_destinies[0].quantity_rooms = result.data.quantity_rooms;
                                this.search_destinies[0].quantity_adults = result.data.quantity_adults;
                                this.search_destinies[0].quantity_child = result.data.quantity_child;
                                this.search_destinies[0].quantity_persons = result.data.quantity_persons;
                                this.search_destinies[0].typeclass_id = result.data.typeclass_id;
                                this.search_destinies[0].hotels_search_code = result.data
                                    .hotels_search_code;
                                console.log("ola");
                                console.log(this.search_destinies[0].dateRange);
                                this.searchDestinies();
                            }
                        }).catch((e) => {
                            console.log(e);
                        });
                },
                cleanSearchParameters: function() {
                    this.search_destinies = [{
                        loader: false,
                        token_search: "",
                        typeclass_id: "all",
                        hotels_search_code: "",
                        showContainerQuantityPersons: false,
                        class_container_rooms: "container_quantity_persons_rooms width_default_container",
                        class_container_select: "container_quantity_persons_rooms_select width_default_select",
                        destiny: "",
                        quantity_rooms: 1,
                        quantity_persons: 2,
                        quantity_adults: 2,
                        quantity_child: 0,
                        quantity_persons_rooms: [{
                            room: 1,
                            adults: 2,
                            child: 0,
                            ages_child: [{
                                child: 1,
                                age: 1
                            }]
                        }],
                        dateRange: "",
                        hotel_selected: "",
                        hotels: [],
                        active: true,
                        rangePrice: [0, 0],
                        max_price_search: 100,
                        hotels_original: [],
                        class: [],
                        zones: [],
                        filter_by_name: "",
                        quantity_hotels: 0
                    }];
                    this.tabsDestinies = false;
                    localStorage.setItem("search_params", "");
                },
                getRouteExcelHotel: function() {
                    this.blockPage = true;
                    setTimeout(this.getRouteExcelExport, 50000);
                    axios.get(
                            baseExternalURL + "api/hotels/generate_array/" + this.service_year + "?lang=" +
                            localStorage.getItem("lang") + "&client_id=" + localStorage.getItem("client_id") +
                            "&user_id=" + localStorage.getItem("user_id") + "&user_type_id=" + localStorage
                            .getItem("user_type_id")
                        )
                        .then((result) => {

                        }).catch((e) => {
                            console.log(e);
                        });

                },
                getRouteExcelExport: function() {
                    this.blockPage = false;
                    var a = document.createElement("a");

                    a.target = "_blank";

                    a.href = baseExternalURL + "hotels/export/" + this.service_year + "?lang=" + localStorage
                        .getItem("lang") + "&client_id=" + localStorage.getItem("client_id") + "&user_id=" +
                        localStorage.getItem("user_id") + "&user_type_id=" + localStorage.getItem(
                            "user_type_id");

                    a.click();

                },
                roundLito: function(num) {
                    num = parseFloat(num);
                    num = (num).toFixed(2);

                    if (num != null) {
                        var res = String(num).split(".");
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

                        return parseFloat(String(nEntero) + "." + String(newDecimal));
                    }
                },
                validateAvailableHotel: function(hotel) {
                    for (let i = 0; i < hotel.rooms.length; i++) {
                        for (let j = 0; j < hotel.rooms[i].rates.length; j++) {
                            if (hotel.rooms[i].rates[j].onRequest == 1) {
                                return true;
                            }
                        }
                    }
                    return false;
                },
                addFavorite: function(hotel_id, hotel) {
                    axios.put(
                            baseExternalURL + "api/hotels/update/favorite", {

                                hotel_id: hotel_id
                            }
                        )
                        .then((result) => {
                            this.$toast.success(result.data.message, {
                                // override the global option
                                position: "top-right"

                            });
                            hotel.favorite = result.data.favorite;
                            this.$forceUpdate();
                        }).catch((e) => {
                            console.log(e);
                        });
                },
                seleccionPersonalizada: function(idHotel) {
                    let wrapperPersonality = document.getElementById("wrapper-personality-" + idHotel);
                    let mytoggle = document.getElementById("mytoggle-" + idHotel);
                    if (mytoggle.value > 0) {
                        mytoggle.value = 0;
                        wrapperPersonality.style.display = "none";
                    } else {
                        mytoggle.value = 1;
                        wrapperPersonality.style.display = "block";
                    }
                },
                filterView: function(view, x) {
                    let result_hotels = document.getElementById("result-hotels");
                    // if(!result_hotels){
                    //     return false
                    // }
                    let filterView = document.getElementById("filterView_" + view);
                    let result_hotels_maps = document.getElementById("result-hotels-maps");
                    //this.setAttribute('class', 'col lg-2 font text-center active');
                    $(".content-vista div").removeClass("active");
                    filterView.setAttribute("class", "col lg-2 font text-center active");

                    result_hotels.style.height = "auto";
                    result_hotels.style.overflow = "auto";
                    result_hotels_maps.style.display = "none";

                    if (view == 0) {
                        result_hotels.setAttribute("class", "row-fluid col-lg-12 result-hotels");
                    } else if (view == 1) {
                        result_hotels.setAttribute("class",
                            "row-fluid col-lg-12 result-hotels result-hotels-gallery");
                    } else {
                        result_hotels.style.height = "0px";
                        result_hotels.style.overflow = "hidden";
                        result_hotels_maps.style.display = "block";
                        // this.filterViewShowGoogleMaps('-33.91721', '151.22630')
                    }
                },
                formatDate: function(starDate) {
                    return moment(starDate).format("ddd D MMM");
                },
                getStringStars: function(stars) {
                    if (stars > 0) {
                        let string = "";
                        for (let i = 0; i < stars; i++) {
                            string += "<span class=\"fa-star fa\"></span>";
                        }
                        return string;
                    }
                },
                filterViewShowGoogleMaps: function(latitude, longitude, hotel_name, hotel_category, hotel_price,
                    hotel_address, hotel_gallery, index_hotel) {

                    let iconoMapa = baseURL + "images/icon-maps.png";
                    let iconoMapaActive = baseURL + "images/icon-maps-active.png";

                    /* let features = [
                         {
                             position: new google.maps.LatLng('-33.91539', '151.22820'),
                             index_hotel: '0',
                             precio: '$ 100',
                             imagen: baseURL+'images/1614e5e1031ae276a5a63378aa341d76.png',
                             html : '<img src="http://127.0.0.1:8000/images/1614e5e1031ae276a5a63378aa341d76.png"><div class="hotel-mapa-content"><div class="title-mapa">Hotel 1 <img src="http://127.0.0.1:8001/images/mapa.png"><div><span class="fa-star fa"></span><span class="fa-star fa"></span><span class="fa-star fa"></span></div></div><p>Dirección:<br>Santa anita mz u lote 33</p><div class="price"><p>Tarifa desde <span class="fas fa-dollar-sign"></span><b>22.00</b></p></div></div></div>',
                         }, {
                             position: new google.maps.LatLng('-33.91747', '151.22912'),
                             index_hotel: '1',
                             precio: '$ 60',
                             imagen: baseURL+'images/1614e5e1031ae276a5a63378aa341d76.png',
                             html : '<img src="http://127.0.0.1:8000/images/1614e5e1031ae276a5a63378aa341d76.png"><div class="hotel-mapa-content"><div class="title-mapa">Hotel 2 <img src="http://127.0.0.1:8001/images/mapa.png"><div><span class="fa-star fa"></span><span class="fa-star fa"></span><span class="fa-star fa"></span></div></div><p>Dirección:<br>Santa anita mz u lote 33</p><div class="price"><p>Tarifa desde <span class="fas fa-dollar-sign"></span><b>22.00</b></p></div></div></div>',
                         }, {
                             position: new google.maps.LatLng('-33.91910', '151.22820'),
                             index_hotel: '0',
                             precio: '$ 80',
                             imagen: baseURL+'images/1614e5e1031ae276a5a63378aa341d76.png',
                             html : '<img src="http://127.0.0.1:8000/images/1614e5e1031ae276a5a63378aa341d76.png"><div class="hotel-mapa-content"><div class="title-mapa">Hotel 3 <img src="http://127.0.0.1:8001/images/mapa.png"><div><span class="fa-star fa"></span><span class="fa-star fa"></span><span class="fa-star fa"></span></div></div><p>Dirección:<br>Santa anita mz u lote 33</p><div class="price"><p>Tarifa desde <span class="fas fa-dollar-sign"></span><b>22.00</b></p></div></div></div>',
                         }
                     ];*/
                    let features = [];
                    for (let i = 0; i < this.search_destinies[this.destiny_id].hotels.length; i++) {
                        let rutaImagen;
                        if (this.search_destinies[this.destiny_id].hotels[i].galleries[0] == undefined) {
                            rutaImagen = baseURL + "images/hotel-default.jpg";
                        } else {
                            rutaImagen = baseExternalURL + "images/" + this.search_destinies[this.destiny_id]
                                .hotels[i].galleries[0];
                        }

                        if (this.search_destinies[this.destiny_id].hotels[i].date_end_flag_new > moment()
                            .toDate()) {
                            this.search_destinies[this.destiny_id].hotels[i].flag_new = 0;
                        }

                        features.push({
                            position: new google.maps.LatLng(this.search_destinies[this.destiny_id]
                                .hotels[i].coordinates.latitude, this.search_destinies[this
                                    .destiny_id].hotels[i].coordinates.longitude),
                            index_hotel: i,
                            precio: "$ " + this.roundLito(this.search_destinies[this.destiny_id].hotels[
                                i].price),
                            imagen: rutaImagen,
                            html: "<img loading=\"lazy\" src=\"" + rutaImagen + "\"" +
                                "><div class=\"hotel-mapa-content\"><div class=\"title-mapa\">" + this
                                .search_destinies[this.destiny_id].hotels[i].name + "<img src=\"" +
                                baseURL + "images/mapa.png" + "\"" + "><div>" + this.getStringStars(this
                                    .search_destinies[this.destiny_id].hotels[i].category) +
                                '</div></div><p>{{ trans('hotel.label.direction') }}:<br>' + this
                                .search_destinies[this.destiny_id].hotels[i].address +
                                '</p><div class="price"><p>{{ trans('hotel.label.rate_from') }} <span class="fas fa-dollar-sign"></span><b>' +
                                this.roundLito(this.search_destinies[this.destiny_id].hotels[i].price) +
                                "</b></p></div></div></div>"
                        });
                    }
                    var markers = [];
                    map = new google.maps.Map(
                        document.getElementById("result-hotels-maps-item"), {
                            center: new google.maps.LatLng(this.search_destinies[this.destiny_id].hotels[0]
                                .coordinates.latitude, this.search_destinies[this.destiny_id].hotels[0]
                                .coordinates.longitude),
                            zoom: 14
                        });

                    for (let i = 0; i < features.length; i++) {
                        var marker = new google.maps.Marker({
                            position: features[i].position,
                            icon: iconoMapa,
                            labelClass: "label-mapa",
                            label: {
                                text: features[i].precio,
                                color: "#fff",
                                fontSize: "11px",
                                fontWeight: "bold"
                            },
                            map: map
                        });
                        markers.push(marker);

                        google.maps.event.addListener(marker, "click", (function(marker, i) {
                            return function() {
                                for (var j = 0; j < markers.length; j++) {
                                    markers[j].setIcon(iconoMapa);
                                }
                                this.setIcon(iconoMapaActive);
                                $(".botonMapa").css("display", "none");
                                $("#result-hotels-maps-view .entra").html(features[i].html);
                                let boton = document.getElementById("boton_mapa_result" +
                                    features[i].index_hotel);
                                boton.style.display = "block";
                            };
                        })(marker, i));
                    }

                },
                getCartContent: async function() {
                    axios.get(
                            baseURL + "cart"
                        )
                        .then((result) => {
                            if (result.data.success === true) {
                                this.cart = result.data.cart;
                                this.cart_quantity_items = result.data.cart.quantity_items;
                            }
                        }).catch((e) => {
                            console.log(e);
                        });
                },
                contarCadena: function(texto) {
                    let cadena = texto.trim();
                    cadena = cadena.length;
                    return cadena;
                },
                updateMenu: function() {
                    this.$emit("updateMenu");
                },
                readMoreDescription(descripcion) {
                    let showChar = 200;
                    if (descripcion.length > showChar) {
                        let first = descripcion.substr(0, showChar);
                        return first;
                    } else {
                        return descripcion;
                    }
                },
                readMoreDescriptionComplete(descripcion) {
                    let showChar = 200;
                    if (descripcion.length > showChar) {
                        let last = descripcion.substr(showChar, descripcion.length);
                        return last;
                    }
                },
                leermas: function(id) {
                    document.getElementById("last_" + id).style.display = "inline";
                    document.getElementById("leermas_" + id).style.display = "none";
                    document.getElementById("leermenos_" + id).style.display = "inline";
                },
                leermenos: function(id) {
                    document.getElementById("last_" + id).style.display = "none";
                    document.getElementById("leermas_" + id).style.display = "inline";
                    document.getElementById("leermenos_" + id).style.display = "none";
                },
                changePage(page) {
                    this.pagination.current_page = page;
                    this.getSearchDestinies();
                },
                pagesNumber: function() {
                    if (!this.pagination.to) {
                        return [];
                    }
                    let from = this.pagination.current_page - this.offset;
                    if (from < 1) {
                        from = 1;
                    }
                    let to = from + (this.offset * 2);
                    if (to >= this.pagination.last_page) {
                        to = this.pagination.last_page;
                    }
                    let pagesArray = [];
                    for (let page = from; page <= to; page++) {
                        pagesArray.push(page);
                    }
                    this.pagesNumbers = pagesArray;
                },
                getHotelStartDate: function() {
                    return moment(this.search_destinies[this.destiny_id].dateRange.startDate).lang(localStorage
                        .getItem("lang")).format("ddd D MMM");
                },
                getHotelEndDate: function() {
                    return moment(this.search_destinies[this.destiny_id].dateRange.endDate).lang(localStorage
                        .getItem("lang")).format("ddd D MMM");
                },
                getHotelNights: function() {
                    let start_date = moment(this.search_destinies[this.destiny_id].startDate);
                    let end_date = moment(this.search_destinies[this.destiny_id].endDate);

                    let num_nights = end_date.diff(start_date, "days");
                    return num_nights;
                },
                generateNewRate: function(hotel, room, rate) {
                    for (let i = 1; i < rate.quantity_rates; i++) {
                        let copy_rate = rate.rate.slice(0)[0];

                        rate.rate[i] = {
                            total_amount: copy_rate["total_amount"],
                            total_amount_adult: copy_rate["total_amount_adult"],
                            total_amount_child: copy_rate["total_amount_child"],
                            total_amount_extra: copy_rate["total_amount_extra"],
                            quantity_adults: copy_rate["quantity_adults"],
                            quantity_child: copy_rate["quantity_child"],
                            ages_child: copy_rate["ages_child"],
                            quantity_adults_total: copy_rate["quantity_adults_total"],
                            quantity_child_total: copy_rate["quantity_child_total"],
                            quantity_extras_total: copy_rate["quantity_extras_total"],
                            amount_days: copy_rate["amount_days"],
                            avgPrice: copy_rate["avgPrice"],
                            people_coverage: copy_rate["people_coverage"],
                            quantity_inventory_taken: copy_rate["quantity_inventory_taken"],
                            total_amount_infants: copy_rate["total_amount_infants"],
                            total_taxes_and_services: copy_rate["total_taxes_and_services"],
                            flag_migrate: copy_rate["flag_migrate"]
                        };
                    }

                    rate.rate.splice(rate.quantity_rates, rate.rate.length);

                    // this.calculateTotalRate(hotel.id,room.room_id,rate.rateId,rate,rate.rate.length-1,rate.ratePlanId);
                    this.calculateSelectionTotalRate(hotel.id, room.room_id, rate.rateId, rate, rate.rate
                        .length - 1, rate.ratePlanId);
                },
                checkNameHotel: function(hotel_name, hotel) {
                    if (hotel.name.toLowerCase().includes(hotel_name.toLowerCase())) {
                        return true;
                    }
                },
                filterByNameHotel: function() {
                    let hotels_new = this.search_destinies[this.destiny_id].hotels_original.filter(this
                        .checkNameHotel.bind(this, this.search_destinies[this.destiny_id].filter_by_name));

                    if (this.search_destinies[this.destiny_id].filter_by_name != "") {
                        this.search_destinies[this.destiny_id].hotels = hotels_new;
                    } else {
                        this.search_destinies[this.destiny_id].hotels = this.search_destinies[this.destiny_id]
                            .hotels_original;
                    }
                },
                filterByPrice: function() {

                    let precio_minimo = this.search_destinies[this.destiny_id].rangePrice[0];
                    let precio_maximo = this.search_destinies[this.destiny_id].rangePrice[1];

                    let hotels_new = this.search_destinies[this.destiny_id].hotels_original.filter(function(
                        hotel) {

                        return hotel.price >= precio_minimo && hotel.price <= precio_maximo;
                    });
                    this.search_destinies[this.destiny_id].hotels = hotels_new;

                    $("#dropdownMenuLink1Container").removeClass("show");
                    $("#dropdownMenuLink1Container").addClass("fade");
                },
                checkAllStars: function() {
                    for (let i = 0; i < this.stars.length; i++) {
                        if (this.check_all_stars) {
                            this.stars[i].status = true;
                        } else {
                            this.stars[i].status = false;
                        }
                    }
                    if (this.check_all_stars) {
                        this.search_destinies[this.destiny_id].hotels = this.search_destinies[this.destiny_id]
                            .hotels_original;
                    }
                },
                checkStarSelect: function(star, hotel) {
                    return hotel.category == star;
                },
                filterByStars: function() {
                    let hotels_new = [];
                    let check_status = false;
                    for (let i = 0; i < this.stars.length; i++) {
                        if (this.stars[i].status) {
                            check_status = true;
                            let hotel_new = this.search_destinies[this.destiny_id].hotels_original.filter(this
                                .checkStarSelect.bind(this, this.stars[i].star));

                            if (hotels_new.length == 0) {
                                hotels_new = hotel_new;
                            } else {

                                hotels_new = hotels_new.concat(hotel_new);
                            }
                        }
                    }
                    if (check_status) {
                        this.search_destinies[this.destiny_id].hotels = hotels_new;
                    } else {
                        this.search_destinies[this.destiny_id].hotels = this.search_destinies[this.destiny_id]
                            .hotels_original;
                    }
                },
                checkAllZones: function() {
                    for (let i = 0; i < this.search_destinies[this.destiny_id].zones.length; i++) {
                        if (this.check_all_zones) {
                            this.search_destinies[this.destiny_id].zones[i].status = true;
                        } else {
                            this.search_destinies[this.destiny_id].zones[i].status = false;
                        }
                    }
                    if (this.check_all_zones) {
                        this.search_destinies[this.destiny_id].hotels = this.search_destinies[this.destiny_id]
                            .hotels_original;
                    }
                },
                checkZonesSelect: function(zone_name, hotel) {
                    return hotel.zone == zone_name;
                },
                filterByZoneHotel: function() {
                    let hotels_new = [];
                    let check_status = false;
                    for (let i = 0; i < this.search_destinies[this.destiny_id].zones.length; i++) {
                        if (this.search_destinies[this.destiny_id].zones[i].status) {
                            check_status = true;
                            let hotel_new = this.search_destinies[this.destiny_id].hotels_original.filter(this
                                .checkZonesSelect.bind(this, this.search_destinies[this.destiny_id].zones[i]
                                    .zone_name));

                            if (hotels_new.length == 0) {
                                hotels_new = hotel_new;
                            } else {

                                hotels_new = hotels_new.concat(hotel_new);
                            }
                        }
                    }
                    if (check_status) {
                        this.search_destinies[this.destiny_id].hotels = hotels_new;
                    } else {
                        this.search_destinies[this.destiny_id].hotels = this.search_destinies[this.destiny_id]
                            .hotels_original;
                    }
                },
                checkAllClass: function() {
                    for (let i = 0; i < this.search_destinies[this.destiny_id].class.length; i++) {
                        if (this.check_all_class) {
                            this.search_destinies[this.destiny_id].class[i].status = true;
                        } else {
                            this.search_destinies[this.destiny_id].class[i].status = false;
                        }
                    }
                    if (this.check_all_class) {
                        this.search_destinies[this.destiny_id].hotels = this.search_destinies[this.destiny_id]
                            .hotels_original;
                    }
                },
                checkClassSelect: function(class_name, hotel) {
                    return hotel.class == class_name;
                },
                filterByClassHotel: function() {
                    let hotels_new = [];
                    let check_status = false;
                    for (let i = 0; i < this.search_destinies[this.destiny_id].class.length; i++) {
                        if (this.search_destinies[this.destiny_id].class[i].status) {
                            check_status = true;
                            let hotel_new = this.search_destinies[this.destiny_id].hotels_original.filter(this
                                .checkClassSelect.bind(this, this.search_destinies[this.destiny_id].class[i]
                                    .class_name));

                            if (hotels_new.length == 0) {
                                hotels_new = hotel_new;
                            } else {

                                hotels_new = hotels_new.concat(hotel_new);
                            }
                        }
                    }
                    if (check_status) {
                        this.search_destinies[this.destiny_id].hotels = hotels_new;
                    } else {
                        this.search_destinies[this.destiny_id].hotels = this.search_destinies[this.destiny_id]
                            .hotels_original;
                    }
                },
                calculateTotalRate: function(hotel_id, room_id, rate_id, rate, index_rate, rate_plan_id) {
                    this.blockPage = true;
                    axios.post(
                            baseExternalURL + "services/calculate/rate/total_amount", {
                                token_search: this.search_destinies[this.destiny_id].token_search,
                                hotel_id: hotel_id,
                                room_id: room_id,
                                rate_id: rate_id,
                                rate_plan_id: rate_plan_id,
                                date_from: this.search_destinies[this.destiny_id].startDate,
                                date_to: moment(this.search_destinies[this.destiny_id].endDate).subtract(1,
                                    "days").format("Y-MM-DD"),
                                client_id: this.client_id,
                                quantity_adults: rate.rate[index_rate].quantity_adults,
                                quantity_child: rate.rate[index_rate].quantity_child,
                                ages_child: this.search_destinies[this.destiny_id].quantity_persons_rooms[0]
                                    .ages_child
                            }
                        )
                        .then((result) => {
                            if (result.data.success) {
                                rate.show_message_error = false;
                                rate.message_error = "";
                                rate.rate[index_rate].amount_days = result.data.rate_plan_room.calendarys;
                                rate.rate[index_rate].quantity_adults_total = result.data.rate_plan_room
                                    .quantity_adults;
                                rate.rate[index_rate].quantity_child_total = result.data.rate_plan_room
                                    .quantity_child;
                                rate.rate[index_rate].quantity_extras_total = result.data.rate_plan_room
                                    .quantity_extras;
                                rate.rate[index_rate].total_amount = result.data.rate_plan_room
                                    .total_amount;
                                rate.rate[index_rate].total_taxes_and_services = result.data.rate_plan_room
                                    .total_taxes_and_services;
                                rate.rate[index_rate].amount_days = result.data.rate_plan_room.calendarys;
                                rate.rate[index_rate].supplements = result.data.rate_plan_room.supplements;

                                //rate.total = result.data.rate_plan_room.total_amount
                                //rate.total_taxes_and_services = result.data.rate_plan_room.total_taxes_and_services
                                //rate.supplements = result.data.rate_plan_room.supplements
                                this.$forceUpdate();
                                this.blockPage = false;
                                // console.log(rate.total);
                            } else {
                                rate.rate[index_rate].quantity_adults = 1;
                                rate.rate[index_rate].quantity_adults_total = 1;
                                rate.rate[index_rate].quantity_child = 0;
                                rate.rate[index_rate].quantity_child_total = 0;
                                rate.show_message_error = true;
                                this.$toast.error(result.data.error, {
                                    position: "top-right"
                                });
                                rate.message_error = result.data.error;
                                this.calculateTotalRate(hotel_id, room_id, rate_id, rate, index_rate,
                                    rate_plan_id);
                                this.blockPage = false;
                            }
                        }).catch((e) => {
                            console.log(e);
                        });
                },
                calculateSelectionTotalRate: function(hotel_id, room_id, rate_id, rate, index_rate, rate_plan_id) {
                    this.blockPage = true;

                    try {
                        var selected_rooms = [];
                        var total_passengers = 0;

                        Object.keys(rate.rate).forEach(function(key_rate) {
                            var val_rate = rate.rate[key_rate];
                            var current_ages_child = val_rate.ages_child || [];
                            var ages_child = [];

                            for (var i = 0; i < val_rate.quantity_child; i++) {
                                if (current_ages_child[i]) {
                                    current_ages_child[i].child = i + 1;
                                    ages_child.push(current_ages_child[i]);
                                } else {
                                    ages_child.push({
                                        child: i + 1,
                                        age: 0
                                    });
                                }
                            }

                            // Reactividad garantizada en Vue 2
                            this.$set(rate.rate[key_rate], 'ages_child', ages_child);

                            selected_rooms.push({
                                quantity_adults: val_rate.quantity_adults,
                                quantity_child: val_rate.quantity_child,
                                ages_child: ages_child
                            });

                            total_passengers += val_rate.quantity_adults + val_rate.quantity_child;
                        }.bind(this));

                        axios.post(baseExternalURL + "services/calculate/selection/rate/total_amount", {
                            token_search: this.search_destinies[this.destiny_id].token_search,
                            hotel_id: hotel_id,
                            room_id: room_id,
                            rate_id: rate_id,
                            ages_child: this.search_destinies[this.destiny_id].quantity_persons_rooms[0]
                                .ages_child,
                            rate_plan_id: rate_plan_id,
                            date_from: this.search_destinies[this.destiny_id].startDate,
                            date_to: this.search_destinies[this.destiny_id].endDate,
                            client_id: this.client_id,
                            rooms: selected_rooms,
                            destiny: {
                                code: this.search_destinies[0].destiny.parent_code + ',' + this
                                    .search_destinies[0].destiny.code,
                                label: this.search_destinies[0].destiny_country.label + ',' + this
                                    .search_destinies[0].destiny.label
                            },
                            typeclass_id: this.search_destinies[0].typeclass_id,
                            hotels_search_code: this.search_destinies[0].hotels_search_code,
                            quantity_rooms: this.search_destinies[0].quantity_rooms,
                            quantity_persons_rooms: this.search_destinies[0].quantity_persons_rooms,
                            lang: "en",
                            zero_rates: true
                        }).then(function(result) {
                            if (result.data.success) {
                                rate.show_message_error = false;
                                rate.message_error = "";
                                Object.keys(result.data.rate_plan_rooms).forEach(function(
                                    key_rate_plan_rooms) {
                                    var rate_plan_room = result.data.rate_plan_rooms[
                                        key_rate_plan_rooms];

                                    if (rate.rate && rate.rate[key_rate_plan_rooms]) {
                                        var r = rate.rate[key_rate_plan_rooms];

                                        /* TODO: ALEX CHRISTIAN */
                                        if ((rate_plan_room.channel_id && rate_plan_room
                                                .channel_type) && (rate_plan_room.channel_id ==
                                                6 && rate_plan_room.channel_type == 2)) {
                                            this.$set(rate, 'id', rate_plan_room.rateId);
                                            this.$set(rate, 'rateId', rate_plan_room.rateId);
                                            this.$set(rate, 'translations', rate_plan_room
                                                .translations);
                                            this.$set(r, 'rate_id', rate_plan_room.rateId);
                                            this.$set(r, 'rateId', rate_plan_room.rateId);
                                            this.$set(r, 'id', rate_plan_room.rateId);
                                            this.$set(r, 'translations', rate_plan_room
                                                .translations);
                                        }
                                        /* TODO: ALEX CHRISTIAN */

                                        this.$set(r, 'quantity_adults_total', rate_plan_room
                                            .quantity_adults);
                                        this.$set(r, 'quantity_child_total', rate_plan_room
                                            .quantity_child);
                                        this.$set(r, 'ages_child', rate_plan_room.ages_child);
                                        this.$set(r, 'quantity_extras_total', rate_plan_room
                                            .quantity_extras);
                                        this.$set(r, 'total_amount', rate_plan_room
                                            .total_amount);
                                        this.$set(r, 'total_taxes_and_services', rate_plan_room
                                            .total_taxes_and_services);
                                        this.$set(r, 'amount_days', rate_plan_room.calendarys);
                                        this.$set(r, 'supplements', rate_plan_room.supplements);
                                        this.$set(r, 'policy_cancellation', rate_plan_room
                                            .policy_cancellation);
                                        this.$set(r, 'policies_cancellation', rate_plan_room
                                            .policies_cancellation);
                                    }

                                    if (rate_plan_room.show_message_error) {
                                        rate.show_message_error = true;
                                        rate.message_error = rate_plan_room.message_error;
                                    }
                                }.bind(this));


                                this.$set(rate.political.cancellation, 'name',  this.formatPolicyText(result.data.political.cancellation.name));

                            } else {
                                rate.total_amount = 0;
                                if (rate.rate[0]) {
                                    this.$set(rate.rate[0], 'total_amount', 0);
                                }

                                rate.show_message_error = true;
                                this.$toast.error(result.data.error, {
                                    position: "top-right"
                                });
                                rate.message_error = result.data.error;
                            }
                        }.bind(this)).catch(function(error) {
                            console.error(error);
                        }).finally(function() {
                            this.blockPage = false;
                        }.bind(this));
                    } catch (error) {
                        console.error("calculateSelectionTotalRate:error", error);
                        this.blockPage = false;
                    }
                },
                addCart: function(hotel_id, room_id, rate_id, rate, hotel_name, room_name, index_hotel, room) {
                    if (rate.message_error !== "") {
                        return;
                    }

                    console.log("tarifa hab...", rate);

                    if (localStorage.getItem("user_type_id") == 4) {
                        const hotel = this.search_destinies[0].hotels[index_hotel];
                        dataLayer.push({
                            "event": "add_to_cart",
                            "currency": "USD",
                            "value": parseFloat(rate.total),
                            "package_id": null,
                            "package_name": null,
                            "items": [{
                                "item_id": hotel.id,
                                "item_sku": hotel.code,
                                "item_name": hotel.name.toLocaleUpperCase("en"),
                                "price": parseFloat(rate.total),
                                "item_brand": hotel.state_iso,
                                "item_category": "hotel",
                                "item_category2": "single_product",
                                "item_list_id": null,
                                "item_list_name": null
                            }]
                        });
                    }

                    this.blockPage = true;
                    localStorage.removeItem("notes_hotel_carrito_" + hotel_id);

                    axios.get(
                            baseExternalURL + "services/check/token_search/" + this.search_destinies[this
                                .destiny_id].token_search
                        )
                        .then((result) => {
                            if (result.data.success) {
                                axios.post(
                                        baseURL + "cart/add", {
                                            token_search: this.search_destinies[this.destiny_id]
                                                .token_search,
                                            token_search_frontend: this.search_destinies[this.destiny_id]
                                                .token_search_frontend,
                                            date_from: this.search_destinies[this.destiny_id].startDate,
                                            date_to: this.search_destinies[this.destiny_id].endDate,
                                            hotel_id: hotel_id,
                                            room_id: room_id,
                                            rate_id: rate_id,
                                            rates: rate.rate,
                                            hotel_name: hotel_name,
                                            room_name: room_name,
                                            search: this.search_destinies[this.destiny_id].hotels[
                                                index_hotel],
                                            room: room,
                                            rate: rate,
                                            ages_child: this.search_destinies[this.destiny_id]
                                                .quantity_persons_rooms[0].ages_child
                                        }
                                    )
                                    .then((result) => {
                                        if (result.data.success) {
                                            rate.taken = true;
                                            rate.disabled_buttons = true;
                                            rate.cart_items_id = result.data.cart_items_id;
                                            localStorage.setItem("reservation", true);
                                            this.updateMenu();
                                            this.blockPage = false;
                                            this.getCartContent();
                                        }
                                    }).catch((e) => {
                                        this.blockPage = false;
                                        console.log(e);
                                    });
                            } else {
                                rate.show_message_error = true;
                                rate.message_error = result.data.error;
                                this.blockPage = false;

                            }
                        }).catch((e) => {
                            console.log(e);
                        });
                },
                cancelSelectionCartInModal: function(room_id, hotel) {
                    for (let i = 0; i < this.search_destinies[this.destiny_id].hotels.length; i++) {
                        if (this.search_destinies[this.destiny_id].hotels[i].id == hotel.hotel_id) {
                            if (hotel.best_option) {
                                this.search_destinies[this.destiny_id].hotels[i].best_option_taken = false;
                            }
                            for (let j = 0; j < this.search_destinies[this.destiny_id].hotels[i].rooms
                                .length; j++) {
                                if (this.search_destinies[this.destiny_id].hotels[i].rooms[j].room_id ==
                                    room_id) {
                                    for (let k = 0; k < this.search_destinies[this.destiny_id].hotels[i].rooms[
                                            j].rates.length; k++) {
                                        this.search_destinies[this.destiny_id].hotels[i].rooms[j].rates[k]
                                            .taken = false;
                                        this.search_destinies[this.destiny_id].hotels[i].rooms[j].rates[k]
                                            .disabled_buttons = false;
                                    }
                                }
                            }
                        }
                    }
                },
                cancelRates: function(rate) {
                    this.blockPage = true;
                    axios.post(
                            baseURL + "cart/cancel/rates", {
                                cart_items_id: rate.cart_items_id
                            }
                        )
                        .then((result) => {
                            if (result.data.success) {
                                rate.taken = false;
                                rate.disabled_buttons = false;
                                this.updateMenu();
                                this.blockPage = false;
                                this.getCartContent();
                            }
                        }).catch((e) => {
                            this.blockPage = false;
                            console.log(e);
                        });
                },
                cancelBestOption: function(hotel) {
                    this.blockPage = true;
                    axios.post(
                            baseURL + "cart/cancel/rates", {
                                cart_items_id: hotel.best_option_cart_items_id
                            }
                        )
                        .then((result) => {
                            if (result.data.success) {
                                hotel.best_option_taken = false;
                                this.updateMenu();
                                this.blockPage = false;
                                this.getCartContent();
                            }
                        }).catch((e) => {
                            console.log(e);
                        });
                },
                destroyCart: function() {
                    axios.delete(baseURL + "cart/content/delete")
                        .then((result) => {

                            if (result.data.success) {
                                this.updateMenu();
                            }
                        }).catch((e) => {
                            console.log(e);
                        });
                },
                addCartBestOption: function(index_hotel) {

                    const config = {
                        headers: {
                            "content-type": "application/x-www-form-urlencoded",
                            "Accept": "application/json"
                        }
                    };

                    console.log(config);

                    localStorage.removeItem("notes_hotel_carrito_" + this.search_destinies[this.destiny_id]
                        .hotels[index_hotel].id);

                    this.blockPage = true;
                    axios.post(
                            baseURL + "cart/add_best_option", {
                                token_search: this.search_destinies[this.destiny_id].token_search,
                                token_search_frontend: this.search_destinies[this.destiny_id]
                                    .token_search_frontend,
                                date_from: this.search_destinies[this.destiny_id].startDate,
                                date_to: this.search_destinies[this.destiny_id].endDate,
                                index_hotel: index_hotel,
                                best_option: this.search_destinies[this.destiny_id].hotels[index_hotel],
                                search: this.search_destinies[this.destiny_id].hotels[index_hotel]
                            }
                        )
                        .then((result) => {
                            this.blockPage = false;
                            if (result.data.success === true) {
                                this.search_destinies[this.destiny_id].hotels[index_hotel][
                                    "best_option_taken"
                                ] = true;
                                this.search_destinies[this.destiny_id].hotels[index_hotel][
                                    "best_option_cart_items_id"
                                ] = result.data.cart_items_id;
                                localStorage.setItem("reservation", true);
                                this.updateMenu();
                                this.getCartContent();
                            } else {
                                console.log(result.data.msn);
                            }
                        }).catch((e) => {
                            this.blockPage = false;
                            console.log(e);
                        });
                },
                sortHotels: function(event) {
                    //Precio menor a mayor
                    if (event.target.value === "2") {
                        this.search_destinies[this.destiny_id].hotels.sort((hotela, hotelb) => hotela.price -
                            hotelb.price);
                    }
                    //Precio mayor a menor
                    if (event.target.value === "3") {
                        this.search_destinies[this.destiny_id].hotels.sort((hotela, hotelb) => hotelb.price -
                            hotela.price);
                    }
                    //Estrellas mayor a menor
                    if (event.target.value === "4") {
                        this.search_destinies[this.destiny_id].hotels.sort((hotela, hotelb) => hotelb.category -
                            hotela.category);
                    }
                    //Estrellas menor a mayor
                    if (event.target.value === "5") {
                        this.search_destinies[this.destiny_id].hotels.sort((hotela, hotelb) => hotela.category -
                            hotelb.category);
                    }
                    //Popularidad
                    if (event.target.value === "6") {
                        let hotel_new_populars = this.search_destinies[this.destiny_id].hotels_original.filter(
                            function(hotel) {
                                if (hotel.popularity != 0) {
                                    return true;
                                }
                            });
                        let hotel_new = this.search_destinies[this.destiny_id].hotels_original.filter(function(
                            hotel) {
                            if (hotel.popularity == 0) {
                                return true;
                            }
                        });
                        hotel_new_populars = hotel_new_populars.sort((hotela, hotelb) => hotela.popularity -
                            hotelb.popularity);

                        let hotels_new = hotel_new_populars.concat(hotel_new);
                        //console.log(hotels_new)
                        this.search_destinies[this.destiny_id].hotels = hotels_new;
                    }

                    //Favorite
                    if (event.target.value === "7") {
                        let hotel_new_favorite = this.search_destinies[this.destiny_id].hotels_original.filter(
                            function(hotel) {
                                if (hotel.favorite == 1) {
                                    return true;
                                }
                            });
                        let hotel_new = this.search_destinies[this.destiny_id].hotels_original.filter(function(
                            hotel) {
                            if (hotel.favorite == 0) {
                                return true;
                            }
                        });

                        // console.log(hotel_new_favorite);
                        // console.log(hotel_new);

                        hotel_new_favorities = hotel_new_favorite.sort((hotela, hotelb) => hotela.favorite -
                            hotelb.favorite);

                        let hotels_new = hotel_new_favorities.concat(hotel_new);
                        //console.log(hotels_new)
                        this.search_destinies[this.destiny_id].hotels = hotels_new;
                    }
                },

                orderByFavorityAndRecomender: function(index) {

                    let hotel_new_favorite = this.search_destinies[index].hotels_original.filter(function(
                        hotel) {
                        if (hotel.favorite == 1) {
                            return true;
                        }
                    });

                    let hotel_new_populars = this.search_destinies[index].hotels_original.filter(function(
                        hotel) {
                        if (hotel.popularity != 0 && hotel.favorite == 0) {
                            return true;
                        }
                    });

                    // hotel_new_populars.forEach((populars, i) => {
                    //
                    //     hotel_new_favorite.forEach((favorite, j) => {
                    //         if (populars.id == favorite.id) {
                    //             this.$delete(hotel_new_populars, i)
                    //         }
                    //     });
                    //
                    // });

                    let hotel_new_no_favorite_no_popularity = this.search_destinies[index].hotels_original
                        .filter(function(hotel) {
                            if (hotel.favorite == 0 && hotel.popularity == 0) {
                                return true;
                            }
                        });

                    let hotels_new_filtrado;

                    if (hotel_new_favorite.length == 0 && hotel_new_populars.length == 0) {
                        hotels_new_filtrado = hotel_new_no_favorite_no_popularity.sort((hotela, hotelb) =>
                            hotela.price - hotelb.price);
                    } else {

                        let hotel_new_favorite_sort = hotel_new_favorite.sort((hotela, hotelb) => hotela.price -
                            hotelb.price);

                        let hotel_new_populars_sort = hotel_new_populars.sort((hotela, hotelb) => hotela
                            .popularity - hotelb.popularity);

                        let hotel_new_no_favorite_no_popularity_sort = hotel_new_no_favorite_no_popularity.sort(
                            (hotela, hotelb) => hotela.price - hotelb.price);

                        hotels_new_filtrado = hotel_new_favorite_sort.concat(hotel_new_populars_sort,
                            hotel_new_no_favorite_no_popularity_sort);
                    }

                    this.search_destinies[index].hotels = hotels_new_filtrado;

                },
                getSearchDestinies: function() {
                    axios.post(
                            "api/search_destinies?page=" + this.pagination.current_page, {
                                client_id: this.client_id
                            }
                        )
                        .then((result) => {
                            this.search_destinies_save = result.data.data;
                            this.pagination = result.data.pagination;
                            this.pagesNumber();

                        }).catch((e) => {
                            console.log(e);
                        });
                },
                getDateSearchDestiny: function(date) {
                    return moment(date).format("L");
                },
                getSearchDestiniesByTokenSearch: function(token_search) {
                    for (let i = 0; i < this.search_destinies.length; i++) {
                        this.tabsDestinies = false;
                        this.search_destinies[i].destiny = "";
                        this.search_destinies[i].destiny_name = "";
                        this.search_destinies[i].hotels = [];
                    }

                    axios.post(
                            "api/search_destinies/by/token_search", {
                                client_id: this.client_id,
                                token_search: token_search
                            }
                        )
                        .then((result) => {
                            document.getElementById("modal_search_close").click();
                            let destinies = result.data;

                            for (let i = 0; i < destinies.length; i++) {
                                if (i === 0) {
                                    this.search_destinies[i].destiny = JSON.parse(destinies[i].destiny);
                                    this.search_destinies[i].startDate = destinies[i].startDate;
                                    this.search_destinies[i].endDate = destinies[i].endDate;
                                    this.search_destinies[i].quantity_adults = destinies[i].quantity_adults;
                                    this.search_destinies[i].quantity_child = destinies[i].quantity_child;
                                    this.search_destinies[i].quantity_persons_rooms = JSON.parse(destinies[
                                        i].quantity_persons_rooms);
                                    this.search_destinies[i].typeclass_id = destinies[i].typeclass_id;
                                    this.search_destinies[i].hotels_search_code = destinies[i]
                                        .hotels_search_code;

                                }
                                if (typeof(this.search_destinies[i]) == "undefined") {
                                    this.addNewSearchDestinySearchSave(i, destinies[i]);
                                } else {
                                    this.search_destinies[i].destiny = JSON.parse(destinies[i].destiny);
                                    this.search_destinies[i].startDate = destinies[i].startDate;
                                    this.search_destinies[i].endDate = destinies[i].endDate;
                                    this.search_destinies[i].quantity_adults = destinies[i].quantity_adults;
                                    this.search_destinies[i].quantity_child = destinies[i].quantity_child;
                                    this.search_destinies[i].quantity_persons_rooms = JSON.parse(destinies[
                                        i].quantity_persons_rooms);
                                    this.search_destinies[i].typeclass_id = destinies[i].typeclass_id;
                                    this.search_destinies[i].hotels_search_code = destinies[i]
                                        .hotels_search_code;
                                }
                            }

                        }).catch((e) => {
                            console.log(e);
                        });

                },
                addNewSearchDestinySearchSave: function(index, destiny) {
                    if (this.search_destinies.length < 5) {
                        this.search_destinies.splice(index, 0, {
                            token_search: "",
                            typeclass_id: destiny.typeclass_id,
                            hotels_search_code: destiny.hotels_search_code,
                            showContainerQuantityPersons: false,
                            class_container_rooms: "container_quantity_persons_rooms width_default_container",
                            class_container_select: "container_quantity_persons_rooms_select width_default_select",
                            destiny_name: "",
                            destiny: JSON.parse(destiny.destiny),
                            quantity_rooms: destiny.quantity_rooms,
                            quantity_persons: 2,
                            quantity_adults: destiny.quantity_adults,
                            quantity_child: destiny.quantity_child,
                            quantity_persons_rooms: JSON.parse(destiny.quantity_persons_rooms),
                            startDate: "",
                            endDate: "",
                            hotels: [],
                            hotel_selected: "",
                            active: false,
                            rangePrice: [0, 0],
                            max_price_search: 100,
                            hotels_original: [],
                            class: [],
                            zones: [],
                            filter_by_name: ""
                        });
                        $(".container_quantity_persons_rooms").click(function(e) {
                            e.stopPropagation();
                            $(this).show();
                        });

                        this.calculateNumPersonsPerRooms(this.search_destinies.length - 1);
                    }
                },
                searchDestinies: function() {
                    this.rate_calculate_error = false;
                    this.tabsDestinies = false;
                    this.flag_search = false;
                    let success = true;
                    for (let i = 0; i < this.search_destinies.length; i++) {
                        this.search_destinies[i]["loeader"] = false;
                        this.search_destinies[i]["active"] = false;
                        this.search_destinies[i].hotels = [];

                        if (this.search_destinies[i].destiny === "" || this.search_destinies[i].dateRange ===
                            "" ||
                            this.search_destinies[i].dateRange === null || this.search_destinies[i]
                            .quantity_persons <= 0) {
                            success = false;
                            this.$toast.error("{{ trans('hotel.label.data_to_Fill') }}", {
                                position: "top-right"
                            });
                            this.blockPage = false;
                        }

                        let sDate = new Date(this.search_destinies[i].dateRange.startDate);
                        let eDate = new Date(this.search_destinies[i].dateRange.endDate);

                        if (sDate !== undefined) {
                            const sDateDD = ((sDate.getDate() < 10) ? "0" : "") + sDate.getDate().toString();
                            const sDateMM = (((sDate.getMonth() + 1) < 10) ? "0" : "") + (sDate.getMonth() + 1)
                                .toString();
                            const sDateYY = sDate.getFullYear().toString();
                            this.search_destinies[i].startDate = sDateYY + "-" + sDateMM + "-" + sDateDD;

                            const eDateDD = ((eDate.getDate() < 10) ? "0" : "") + eDate.getDate().toString();
                            const eDateMM = (((eDate.getMonth() + 1) < 10) ? "0" : "") + (eDate.getMonth() + 1)
                                .toString();
                            const eDateYY = eDate.getFullYear().toString();
                            this.search_destinies[i].endDate = eDateYY + "-" + eDateMM + "-" + eDateDD;
                        }
                    }
                    if (success) {
                        this.flag_search = true;

                        let params = {
                            module: "hotels",
                            destiny: {
                                destiny: this.search_destinies[0].destiny,
                                destiny_country: this.search_destinies[0].destiny_country,
                                destiny_district: this.search_destinies[0].destiny_district
                            },
                            typeclass_id: this.search_destinies[0].typeclass_id,
                            hotels_search_code: this.search_destinies[0].hotels_search_code,
                            startDate: this.search_destinies[0].dateRange.startDate,
                            endDate: this.search_destinies[0].dateRange.endDate,
                            quantity_adults: this.search_destinies[0].quantity_adults,
                            quantity_child: this.search_destinies[0].quantity_child,
                            quantity_persons: this.search_destinies[0].quantity_persons,
                            quantity_rooms: this.search_destinies[0]["quantity_rooms"],
                            quantity_persons_rooms: this.search_destinies[0]["quantity_persons_rooms"]
                        };

                        localStorage.setItem("search_params", JSON.stringify(params));

                        this.blockPage = true;
                        for (let i = 0; i < this.search_destinies.length; i++) {
                            this.search_destinies[i]["destiny_name"] =
                                "{{ trans('hotel.label.loader') }} ....";
                            if (this.search_destinies[i].destiny !== "" && this.search_destinies[i]
                                .startDate !== "" && this.search_destinies[i].endDate !== "" && this
                                .search_destinies[i].quantity_persons > 0) {





                                axios.post(
                                        "services/hotels/available", {
                                            client_id: this.client_id,
                                            destiny: this.get_data_destiny(i),
                                            typeclass_id: this.search_destinies[i].typeclass_id,
                                            hotels_search_code: this.search_destinies[i].hotels_search_code,
                                            date_from: this.search_destinies[i].startDate,
                                            date_to: this.search_destinies[i].endDate,
                                            quantity_rooms: this.search_destinies[i]["quantity_rooms"],
                                            quantity_persons_rooms: this.search_destinies[i][
                                                "quantity_persons_rooms"
                                            ],
                                            lang: localStorage.getItem("lang"),
                                            zero_rates: true
                                        })
                                    .then((result) => {

                                        console.log(this.search_destinies);

                                        if (result.data.success === true) {

                                            this.search_destinies[i]["loeader"] = true;

                                            if (result.data.data[0]["city"]["hotels"].length > 0 && this
                                                .blockPage == true) {
                                                console.log(localStorage.getItem("user_type_id"));
                                                if (localStorage.getItem("user_type_id") == 4) {
                                                    console.log("dataLayer: se encontraron resultados");
                                                    dataLayer.push({
                                                        "event": "search",
                                                        "results": "true"
                                                    });
                                                }

                                                this.search_destinies[i]["active"] = true;
                                                this.destiny_id = i;
                                                this.blockPage = false;
                                            } else {

                                                if (localStorage.getItem("user_type_id") == 4) {
                                                    console.log("dataLayer: no se encontraron resultados");
                                                    dataLayer.push({
                                                        "event": "search",
                                                        "results": "false"
                                                    });
                                                }
                                                let loader = 0;
                                                for (let ix = 0; ix < this.search_destinies.length; ix++) {
                                                    if (this.search_destinies[ix]["loeader"] == true) {
                                                        loader = loader + 1;
                                                    }
                                                }

                                                if (loader == this.search_destinies.length) {
                                                    if (this.blockPage == true) {
                                                        this.search_destinies[0]["active"] = true;
                                                        this.destiny_id = 0;
                                                    }
                                                    this.blockPage = false;
                                                }

                                            }

                                            this.search_destinies[i]["destiny_name"] = this
                                                .search_destinies[i]["destiny"]["label"];
                                            this.tabsDestinies = true;
                                            this.search_destinies[i].token_search = result.data.data[0][
                                                "city"
                                            ]["token_search"];
                                            this.search_destinies[i].token_search_frontend = result.data
                                                .data[0]["city"]["token_search_frontend"];

                                            let hotels = result.data.data[0]["city"]["hotels"];
                                            this.processHotelsPolicies(hotels);

                                            this.search_destinies[i].hotels = result.data.data[0]["city"][
                                                "hotels"
                                            ].sort((hotela, hotelb) => hotela.price - hotelb.price);
                                            this.search_destinies[i].hotels = result.data.data[0]["city"][
                                                "hotels"
                                            ];
                                            this.search_destinies[i].hotels_original = result.data.data[0][
                                                "city"
                                            ]["hotels"];
                                            this.search_destinies[i].class = result.data.data[0]["city"][
                                                "class"
                                            ];
                                            this.search_destinies[i].zones = result.data.data[0]["city"][
                                                "zones"
                                            ];
                                            this.search_destinies[i].quantity_hotels = result.data.data[0][
                                                "city"
                                            ]["quantity_hotels"];
                                            this.search_destinies[i].rangePrice[1] = Math.ceil(parseFloat(
                                                result.data.data[0]["city"]["max_price_search"]));
                                            this.search_destinies[i].max_price_search = Math.ceil(
                                                parseFloat(result.data.data[0]["city"][
                                                    "max_price_search"
                                                ]));

                                            this.orderByFavorityAndRecomender(i);
                                            // this.filterView(0)
                                            if (this.$refs["rangePrice" + i]) {
                                                this.$refs["rangePrice" + i].setValue([0, Math.ceil(parseFloat(result.data.data[0]["city"]["max_price_search"]))]);
                                            }

                                        }
                                    }).catch((e) => {
                                        this.blockPage = false;
                                        console.log(e);
                                    });
                            } else {

                                this.$toast.error("{{ trans('hotel.label.data_to_Fill') }}", {
                                    position: "top-right"
                                });
                            }
                        }
                    }
                },
                get_data_destiny(i) {
                    // this.destiny / destiny_country / destiny_district
                    // {code: "PE,LIM,128", label: "Perú, Lima, Lima"} // http://prntscr.com/123oeip
                    /*
                    destinations_countries_select: [],
                    destinations_select_universe: [],
                    destinations_additional_select_universe: [],

                    in nodes:
                    destiny_country: { code: 89, label: "Perú" },
                    destinations_select: [],
                    destiny: '',
                    destinations_additional_select: [],
                    destiny_district: "",
                    * */
                    let data_destiny = "";
                    if (this.search_destinies[i].destiny !== "") {
                        let code_ = this.search_destinies[i].destiny_country.code + "," + this.search_destinies[i]
                            .destiny.code;
                        let label_ = this.search_destinies[i].destiny_country.label + "," + this.search_destinies[i]
                            .destiny.label;
                        if (this.search_destinies[i].destiny_district !== "") {
                            let destiny_district_label = "";
                            this.search_destinies[i].destinations_additional_select.forEach((d) => {
                                if (d.code === this.search_destinies[i].destiny_district) {
                                    destiny_district_label = d.label;
                                }
                            });

                            code_ += "," + this.search_destinies[i].destiny_district;
                            label_ += "," + destiny_district_label;
                        }
                        data_destiny = {
                            code: code_,
                            label: label_
                        };
                    }
                    return data_destiny;
                },
                setDestinyId: function(event, destiny_id) {
                    console.log(destiny_id);
                    event.preventDefault();
                    this.unSelectDestinies();
                    this.destiny_id = destiny_id;
                    this.search_destinies[destiny_id]["active"] = true;
                    if (this.search_destinies[destiny_id]["quantity_hotels"] > 0) {
                        // this.filterView(0)
                    }
                },
                unSelectDestinies: function() {
                    this.search_destinies.forEach(function(destiny, index_search_destiny) {
                        destiny.active = false;
                    });
                },
                getClassHotelByClientId: async function() {
                    await axios.get("api/client_hotels/classes?client_id=" + this.client_id + "&lang=" +
                            localStorage.getItem("lang"))
                        .then((result) => {
                            if (result.data.success) {
                                this.classes_hotel = result.data.data;
                            }
                        }).catch((e) => {
                            console.log(e);
                        });
                },
                clearDestinies: function() {
                    this.search_destinies = [];

                    this.search_destinies.push({
                        token_search: "",
                        typeclass_id: "all",
                        hotels_search_code: "",
                        quantity_adults: 0,
                        quantity_child: 0,
                        showContainerQuantityPersons: false,
                        class_container_rooms: "container_quantity_persons_rooms width_default_container",
                        class_container_select: "container_quantity_persons_rooms_select width_default_select",
                        destiny_name: "",
                        destiny_country: {
                            code: "PE",
                            label: "Perú"
                        },
                        destiny: "",
                        destiny_district: "",
                        quantity_rooms: 1,
                        quantity_persons: 2,
                        quantity_persons_rooms: [{
                            room: 1,
                            adults: 2,
                            child: 0,
                            ages_child: [{
                                child: 1,
                                age: 1
                            }]
                        }],
                        dateRange: "",
                        hotel_selected: "",
                        active: true,
                        hotels: [],
                        rangePrice: [0, 0],
                        max_price_search: 100,
                        hotels_original: [],
                        class: [],
                        zones: [],
                        filter_by_name: ""
                    });

                    this.destiny_id = 0;
                    this.calculateNumPersonsPerRooms(this.search_destinies.length - 1);
                },
                getDestiniesByClientId: async function() {
                    this.getClassHotelByClientId();
                    await axios.get("services/hotels/destinations?client_id=" + this.client_id)
                        .then((result) => {
                            this.search_destinies[this.destiny_id].hotels = [];
                            this.unzip_destination_hotels(result.data);
                        }).catch((e) => {
                            console.log(e);
                        });
                },
                unzip_destination_hotels(data) {
                    // {code: "89,1610,128", label: "Perú, Lima, Lima"}

                    // DESTINIES
                    this.destinations_countries_select = [];
                    let destinations_countries_select_ = [];
                    this.destinations_select_universe = [];
                    let destinations_select_ = [];
                    this.destinations_additional_select_universe = [];

                    data.forEach((d) => {
                        let code_split = d.code.split(",");
                        let label_split = d.label.split(",");

                        if (destinations_countries_select_[code_split[0]] === undefined) {
                            this.destinations_countries_select.push({
                                code: code_split[0],
                                label: label_split[0].trim()
                            });
                            destinations_countries_select_[code_split[0]] = true;
                        }

                        if (destinations_select_[code_split[1]] === undefined) {
                            this.destinations_select_universe.push({
                                code: code_split[1],
                                label: label_split[1].trim(),
                                parent_code: code_split[0]
                            });
                            destinations_select_[code_split[1]] = true;
                        }

                        let code_for_split = code_split[0] + "," + code_split[1];
                        let label_for_split = label_split[0] + "," + label_split[1];
                        let code_add_split = d.code.split(code_for_split);
                        let label_add_split = d.label.split(label_for_split);
                        if (code_add_split.length > 1) {
                            if (code_add_split[1].trim() !== "") {
                                this.destinations_additional_select_universe.push({
                                    code: code_add_split[1].substring(1),
                                    label: label_add_split[1].substring(1).trim(),
                                    parent_code: code_split[1]
                                });
                            }
                        }
                    });

                    this.change_destiny_cities(0);
                },
                change_destiny_cities(index_search_destiny) {
                    this.search_destinies[index_search_destiny].destinations_additional_select = [];
                    this.search_destinies[index_search_destiny].destinations_select = []; // destinies_select
                    // this.destiny = ""
                    this.destinations_select_universe.forEach((d_u) => {
                        console.log(d_u.parent_code);
                        if (d_u.parent_code == this.search_destinies[index_search_destiny].destiny_country
                            .code) {
                            this.search_destinies[index_search_destiny].destinations_select.push(d_u);
                        }
                    });
                    console.log(this.search_destinies);
                },
                addNewSearchDestiny: function() {
                    this.tabsDestinies = false;
                    for (let i = 0; i < this.search_destinies.length; i++) {
                        this.search_destinies[i].hotels = [];
                        this.search_destinies[i].hotels_original = [];
                    }

                    this.search_destinies.push({
                        token_search: "",
                        typeclass_id: "all",
                        hotels_search_code: "",
                        quantity_adults: 0,
                        quantity_child: 0,
                        showContainerQuantityPersons: false,
                        class_container_rooms: "container_quantity_persons_rooms width_default_container",
                        class_container_select: "container_quantity_persons_rooms_select width_default_select",
                        destiny_name: "",
                        destiny_country: this.search_destinies[this.search_destinies.length - 1]
                            .destiny_country,
                        destinations_select: this.search_destinies[this.search_destinies.length - 1]
                            .destinations_select,
                        destiny: this.search_destinies[this.search_destinies.length - 1].destiny,
                        destinations_additional_select: this.search_destinies[this.search_destinies
                            .length - 1].destinations_additional_select,
                        destiny_district: this.search_destinies[this.search_destinies.length - 1]
                            .destiny_district,
                        quantity_rooms: 1,
                        quantity_persons: 2,
                        quantity_persons_rooms: [{
                            room: 1,
                            adults: 2,
                            child: 0,
                            ages_child: [{
                                child: 1,
                                age: 1
                            }]
                        }],
                        dateRange: "",
                        hotels: [],
                        hotel_selected: "",
                        active: false,
                        rangePrice: [0, 0],
                        max_price_search: 100,
                        hotels_original: [],
                        class: [],
                        zones: [],
                        filter_by_name: "",
                        quantity_hotels: 0
                    });
                    this.calculateNumPersonsPerRooms(this.search_destinies.length - 1);

                },
                deleteSearchDestiny: function(index_search_destiny) {
                    this.$delete(this.search_destinies, index_search_destiny);
                    this.search_destinies[0]["active"] = true;
                    this.destiny_id = 0;
                },
                hiddenContainerQuantityPersons: function(index_search_destiny) {
                    this.search_destinies[index_search_destiny].showContainerQuantityPersons = false;
                },
                showContainerQuantityPersons: function(index_search_destiny) {
                    this.search_destinies[index_search_destiny].showContainerQuantityPersons = true;
                },
                changePropertyWidthContainerRooms: function(index_search_destiny) {
                    let num_child = 0;
                    for (let i = 0; i < this.search_destinies[index_search_destiny].quantity_persons_rooms
                        .length; i++) {
                        if (num_child < this.search_destinies[index_search_destiny].quantity_persons_rooms[i]
                            .child) {

                            num_child = this.search_destinies[index_search_destiny].quantity_persons_rooms[i]
                                .child;
                        }
                    }
                    if (num_child === 0) {
                        this.search_destinies[index_search_destiny].class_container_rooms =
                            "container_quantity_persons_rooms width_default_container";
                    }
                    if (num_child === 1) {

                        this.search_destinies[index_search_destiny].class_container_rooms =
                            "container_quantity_persons_rooms width_child_1_container";

                    }
                    if (num_child === 2) {

                        this.search_destinies[index_search_destiny].class_container_rooms =
                            "container_quantity_persons_rooms width_child_2_container";

                    }
                    if (num_child === 3) {

                        this.search_destinies[index_search_destiny].class_container_rooms =
                            "container_quantity_persons_rooms width_child_3_container";

                    }
                    if (num_child === 4) {

                        this.search_destinies[index_search_destiny].class_container_rooms =
                            "container_quantity_persons_rooms width_child_4_container";

                    }
                    if (num_child === 5) {

                        this.search_destinies[index_search_destiny].class_container_rooms =
                            "container_quantity_persons_rooms width_child_5_container";

                    }
                },
                changePropertyWidthContainerSelect: function(index_search_destiny) {
                    let num_child = 0;
                    for (let i = 0; i < this.search_destinies[index_search_destiny].quantity_persons_rooms
                        .length; i++) {
                        if (num_child < this.search_destinies[index_search_destiny].quantity_persons_rooms[i]
                            .child) {

                            num_child = this.search_destinies[index_search_destiny].quantity_persons_rooms[i]
                                .child;
                        }
                    }
                    if (num_child === 0) {

                        this.search_destinies[index_search_destiny].class_container_select =
                            "container_quantity_persons_rooms_select width_default_select";
                    }
                    if (num_child === 1) {

                        this.search_destinies[index_search_destiny].class_container_select =
                            "container_quantity_persons_rooms_select width_child_1_select";
                    }
                    if (num_child === 2) {

                        this.search_destinies[index_search_destiny].class_container_select =
                            "container_quantity_persons_rooms_select width_child_2_select";
                    }
                    if (num_child === 3) {

                        this.search_destinies[index_search_destiny].class_container_select =
                            "container_quantity_persons_rooms_select width_child_3_select";
                    }
                    if (num_child === 4) {

                        this.search_destinies[index_search_destiny].class_container_select =
                            "container_quantity_persons_rooms_select width_child_4_select";
                    }
                    if (num_child === 5) {

                        this.search_destinies[index_search_destiny].class_container_select =
                            "container_quantity_persons_rooms_select width_child_5_select";
                    }
                },
                changeQuantityChild: function(event, index_search_destiny, index_quantity_person_room) {
                    this.changePropertyWidthContainerRooms(index_search_destiny);
                    this.changePropertyWidthContainerSelect(index_search_destiny);
                    this.search_destinies[index_search_destiny].quantity_persons_rooms[
                        index_quantity_person_room].ages_child.splice(
                        1, 4);
                    for (let i = 1; i < event.target.value; i++) {
                        this.search_destinies[index_search_destiny].quantity_persons_rooms[
                            index_quantity_person_room].ages_child.splice(
                            (i + 1), 0, {
                                child: i + 1,
                                age: 1
                            });
                    }
                    this.calculateNumPersonsPerRooms(index_search_destiny);
                },
                changeQuantityRooms: function(event, index_search_destiny) {
                    if (this.search_destinies[index_search_destiny].quantity_rooms > 1) {
                        this.search_destinies[index_search_destiny].quantity_persons_rooms.splice(1, 4);
                        for (let i = 1; i < event.target.value; i++) {
                            this.search_destinies[index_search_destiny].quantity_persons_rooms.splice((i + 1),
                                0, {
                                    room: i + 1,
                                    adults: 1,
                                    child: 0,
                                    ages_child: [{
                                        child: 1,
                                        age: 1
                                    }]
                                });
                        }
                    } else {
                        this.search_destinies[index_search_destiny].quantity_persons_rooms.splice(1, 4);
                    }
                    this.calculateNumPersonsPerRooms(index_search_destiny);
                },
                hideModalGoogleMaps: function() {
                    let boton_mapa = document.getElementsByClassName("botonMapa");
                    for (var i = 0; i < boton_mapa.length; i++) {
                        boton_mapa[i].style.display = "none"; // depending on what you're doing
                    }
                    document.getElementById("my-modal-google-maps").style.display = "none";
                },
                showGoogleMaps: function(latitude, longitude, hotel_name, hotel_category, hotel_price,
                    hotel_address, hotel_gallery, index_hotel) {
                    hotel_price = this.roundLito(hotel_price);
                    // The location of point
                    let point = {
                        lat: parseFloat(latitude),
                        lng: parseFloat(longitude)
                    };
                    // The map, centered at point
                    let map = new google.maps.Map(document.getElementById("showMap"), {
                        zoom: 15,
                        center: point
                    });
                    // The marker, positioned at point
                    let iconoMapa = baseURL + "images/mapa.png";
                    let marker = new google.maps.Marker({
                        position: point,
                        map: map,
                        icon: iconoMapa
                    });

                    let html_estrella = "";
                    for (var i = 0; i < hotel_category; i++) {
                        html_estrella += "<span class=\"fa-star fa\"></span>";
                    }
                    let imagen = baseExternalURL + "images/" + hotel_gallery[0];
                    if (hotel_gallery[0] == undefined) {
                        imagen = baseURL + "images/hotel-default.jpg";
                    }

                    let html_mapa = "<img loading=\"lazy\" src=\"" + imagen +
                        "\"><div class=\"hotel-mapa-content\"><div class=\"title-mapa\">" + hotel_name +
                        " <img loading=\"lazy\" src=\"" + iconoMapa + "\"><div>" + html_estrella +
                        '</div></div><p>{{ trans('hotel.label.direction') }}:<br>' + hotel_address +
                        '</p><div class="price"><p>{{ trans('hotel.label.rate_from') }} <span class="fas fa-dollar-sign"></span><b>' +
                        hotel_price + "</b></p></div></div>";
                    $("#app_mapa  div.entra").html(html_mapa);

                    //this.indexMapa(index_hotel)
                    document.getElementById("my-modal-google-maps").style.display = "block";
                    let boton = document.getElementById("boton_mapa_" + index_hotel);
                    //alert(boton)
                    boton.style.display = "block";

                },
                showModalRooms: function(index_hotel, index_image, index_room) {
                    this.image_modal = this.search_destinies[this.destiny_id].hotels[index_hotel].hotel.rooms[
                        index_room].galeries[index_image].url;
                    this.$refs["my-modal"].show();
                },
                showModalRoomsBestOption: function(index_hotel, index_image, index_busqueda) {
                    this.image_modal = this.search_destinies[this.destiny_id].hotels[index_hotel][
                        "best_options"
                    ]["rooms"][index_busqueda]["room"]["gallery"][index_image];
                    this.$refs["my-modal"].show();
                },
                showModal: function(index_hotel, index_image) {
                    this.image_modal = this.search_destinies[this.destiny_id].hotels[index_hotel].hotel
                        .galeries[index_image];
                    this.$refs["my-modal"].show();
                    // this.$refs['my-modal'+index_hotel+index_image].show()
                },
                showModalTarifa: function(index_hotel, index_room, index_rate, tipo_tarifa) {
                    let modalTarifa;
                    if (tipo_tarifa == 1) {
                        modalTarifa = document.getElementById("tarifaCombinacion_" + index_hotel + "_" +
                            index_room + "_" + index_rate);
                    } else {
                        modalTarifa = document.getElementById("tarifa_" + index_hotel + "_" + index_room + "_" +
                            index_rate);
                    }
                    modalTarifa.classList.add("show");
                    modalTarifa.style.display = "block";
                    modalTarifa.style.opacity = "1";
                    modalTarifa.style.overflow = "auto";
                },
                showModalPolicy: function(index_hotel, index_room, index_rate, tipo_tarifa) {
                    let modalTarifa;
                    if (tipo_tarifa == 1) {
                        modalTarifa = document.getElementById("policyCombination_" + index_hotel + "_" +
                            index_room + "_" + index_rate);
                    } else {
                        modalTarifa = document.getElementById("policy_" + index_hotel + "_" + index_room + "_" +
                            index_rate);
                    }
                    modalTarifa.classList.add("show");
                    modalTarifa.style.display = "block";
                    modalTarifa.style.opacity = "1";
                    modalTarifa.style.overflow = "auto";
                },
                hideShowModalTarifa: function(index_hotel, index_room, index_rate, tipo_tarifa) {
                    let modalTarifa;
                    if (tipo_tarifa == 1) {
                        modalTarifa = document.getElementById("tarifaCombinacion_" + index_hotel + "_" +
                            index_room + "_" + index_rate);
                    } else {
                        modalTarifa = document.getElementById("tarifa_" + index_hotel + "_" + index_room + "_" +
                            index_rate);
                    }
                    modalTarifa.classList.remove("show");
                    modalTarifa.style.opacity = "0";
                    modalTarifa.style.display = "none";
                    modalTarifa.style.overflow = "hidden";
                },
                hideShowModalPolicy: function(index_hotel, index_room, index_rate, tipo_tarifa) {
                    let modalTarifa;
                    if (tipo_tarifa == 1) {
                        modalTarifa = document.getElementById("policyCombination_" + index_hotel + "_" +
                            index_room + "_" + index_rate);
                    } else {
                        modalTarifa = document.getElementById("policy_" + index_hotel + "_" + index_room + "_" +
                            index_rate);
                    }
                    modalTarifa.classList.remove("show");
                    modalTarifa.style.opacity = "0";
                    modalTarifa.style.display = "none";
                    modalTarifa.style.overflow = "hidden";
                },
                hideModal: function() {
                    this.$refs["my-modal"].hide();
                },
                calculateNumPersonsPerRooms: function(index_search_destiny) {
                    let quantity_adults = 0;
                    let quantity_child = 0;
                    for (let i = 0; i < this.search_destinies[index_search_destiny].quantity_persons_rooms
                        .length; i++) {
                        quantity_adults += this.search_destinies[index_search_destiny].quantity_persons_rooms[i]
                            .adults;
                        quantity_child += this.search_destinies[index_search_destiny].quantity_persons_rooms[i]
                            .child;
                    }

                    this.search_destinies[index_search_destiny].quantity_persons = quantity_adults +
                        quantity_child;
                    this.search_destinies[index_search_destiny].quantity_adults = quantity_adults;
                    this.search_destinies[index_search_destiny].quantity_child = quantity_child;
                },
                showRoomsHotel: function(index_hotel, mapa, mapa_view) {
                    this.flag_hotel_room = index_hotel;
                    // console.log("MOSTRANDO ROOMS..", index_hotel, mapa);
                    if (localStorage.getItem("user_type_id") == 4) {
                        const hotel = this.search_destinies[0].hotels[index_hotel];
                        dataLayer.push({
                            "event": "view_item",
                            "currency": "USD",
                            "value": parseFloat(hotel.price),
                            "package_id": null,
                            "package_name": null,
                            "items": [{
                                "item_id": hotel.id,
                                "item_sku": hotel.code,
                                "item_name": hotel.name.toLocaleUpperCase("en"),
                                "price": parseFloat(hotel.price),
                                "item_brand": hotel.state_iso,
                                "item_category": "hotel",
                                "item_category2": "single_product",
                                "item_list_id": null,
                                "item_list_name": null
                            }]
                        });
                    }

                    if (mapa == 1) {
                        document.getElementById("my-modal-google-maps").style.display = "none";
                        let boton_mapa = document.getElementById("boton_mapa_" + index_hotel);
                        boton_mapa.style.display = "none";
                    }
                    /*
                    let container_rooms_hotel = document.getElementById("container_rooms_hotel" + index_hotel);
                    if (container_rooms_hotel.style.display === "none") {
                        container_rooms_hotel.style.display = "block";
                    } else {
                        container_rooms_hotel.style.display = "none";
                    }
                    */
                    let wrapperPersonality = document.getElementById("wrapper-personality-" + index_hotel);
                    // let mytoggle = document.getElementById('mytoggle-' + index_hotel)
                    // mytoggle.value = 1
                    // if (mytoggle.value != null && mytoggle.value > 0) {
                    //     mytoggle.value = 0
                    //     wrapperPersonality.style.display = 'none'
                    // } else {
                    //     mytoggle.value = 1

                    if (wrapperPersonality) {
                        console.log("Wrapper: ", wrapperPersonality.style)
                        wrapperPersonality.style.display = "block";
                    }
                    // }
                },

                goCartDetails() {
                    if (document.head.querySelector("[name=route_name]")) {
                        let page = document.head.querySelector("[name=route_name]").content;
                        if (["services", "hotels"].includes(page)) {
                            localStorage.setItem("page_return", document.head.querySelector("[name=route_name]")
                                .content);

                        }
                        document.location.href = "/cart_details/view";
                    }

                }
            }
        });
    </script>
@endsection
