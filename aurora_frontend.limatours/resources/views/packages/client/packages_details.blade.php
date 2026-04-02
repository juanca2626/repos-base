@extends('layouts.app')
@section('content')
<section class="packages__details">
    <div class="hero__primary">
    </div>
</section>
<section class="packages__details my-5 container border-bottom" v-show="client_id != ''">
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
                <b-col cols="4" class="mb-2">
                    <b-skeleton-img height="350px"></b-skeleton-img>
                </b-col>
                <b-col cols="8" class="mb-2">
                    <b-skeleton class="mt-3" height="10%"></b-skeleton>
                    <b-skeleton class="mt-3" width="80%" height="5%"></b-skeleton>
                    <b-skeleton class="mt-3" width="70%" height="5%"></b-skeleton>
                    <b-skeleton class="mt-3" width="60%" height="5%"></b-skeleton>
                    <b-row>
                        <b-col cols="12" class="mb-4 mt-4">
                            <b-skeleton class="mt-3" width="50%" height="5%"></b-skeleton>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col cols="6" class="mb-2">
                            <b-skeleton class="mt-3" width="80%" height="5%"></b-skeleton>
                            <b-skeleton class="mt-3" width="70%" height="5%"></b-skeleton>
                        </b-col>
                        <b-col cols="6" class="mb-2">
                            <b-skeleton class="mt-3" width="80%" height="5%"></b-skeleton>
                            <b-skeleton class="mt-3" width="70%" height="5%"></b-skeleton>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col cols="6" class="mb-2">
                            <b-skeleton class="mt-3" width="80%" height="5%"></b-skeleton>
                            <b-skeleton class="mt-3" width="70%" height="5%"></b-skeleton>
                        </b-col>
                        <b-col cols="6" class="mb-2">
                            <b-skeleton class="mt-3" width="80%" height="5%"></b-skeleton>
                            <b-skeleton class="mt-3" width="70%" height="5%"></b-skeleton>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col cols="6" class="mb-2">
                            <b-skeleton class="mt-3" width="80%" height="5%"></b-skeleton>
                            <b-skeleton class="mt-3" width="70%" height="5%"></b-skeleton>
                        </b-col>
                        <b-col cols="6" class="mb-2">
                            <b-skeleton class="mt-3" width="80%" height="5%"></b-skeleton>
                            <b-skeleton class="mt-3" width="70%" height="5%"></b-skeleton>
                        </b-col>
                    </b-row>
                </b-col>
            </b-row>
            <b-row>
                <b-col cols="12" class="mb-3 mt-3"></b-col>
            </b-row>
            <b-row>
                <b-col cols="4" class="mb-5 mt-2">
                    <b-skeleton class="mt-3" width="60%" height="10%"></b-skeleton>
                    <b-skeleton class="mt-3" height="10%"></b-skeleton>
                    <b-skeleton class="mt-3" height="10%"></b-skeleton>
                    <b-skeleton class="mt-3" height="10%"></b-skeleton>
                </b-col>
                <b-col cols="4" class="mb-5 mt-2">
                    <b-skeleton class="mt-3" width="60%" height="10%"></b-skeleton>
                    <b-skeleton class="mt-3" height="10%"></b-skeleton>
                    <b-skeleton class="mt-3" height="10%"></b-skeleton>
                    <b-skeleton class="mt-3" height="10%"></b-skeleton>
                </b-col>
                <b-col cols="4" class="mb-5 mt-2">
                    <b-skeleton class="mt-3" width="60%" height="10%"></b-skeleton>
                    <b-skeleton class="mt-3" height="10%"></b-skeleton>
                    <b-skeleton class="mt-3" height="10%"></b-skeleton>
                    <b-skeleton class="mt-3" height="10%"></b-skeleton>
                </b-col>
            </b-row>
        </template>
        <div v-if="package.id != null">
            <div class="d-flex justify-content-between">
                <div>
                    <h1 class="text-center package-title">@{{package.descriptions.name}} <span>@{{ package.nights + 1 }} @{{ translations.label.days }} / @{{ package.nights }} @{{ translations.label.nights }}</span>
                    </h1>
                    <p class="package-destination">@{{ package.destinations.destinations | formatDestinations}}</p>
                    <div class="tag clasic-vendidos" :style="'background-color:#' + package.tag.color +''">
                        @{{ package.tag.name }}
                    </div>
                </div>

                <div>
                    <a data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                        class="acciones__item">
                        <span><i class="icon-download"></i>{{ trans('quote.label.download') }}</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu__opciones dropdown-menu-right" style="z-index: 100">
                        <div class="dropdown-menu_body">
                            <div class="showDownloadItinerary">
                                <form>
                                    <h5>{{ trans('quote.label.itinerary') }} <i class="icon-download"></i></h5>
                                    <hr>
                                    <template v-if="package.portada_link != null && package.portada != ''">
                                        <h5>{{ trans('quote.label.do_you_want_a_cover') }}:</h5>
                                        <div class="d-flex align-items-center">
                                            <label class="mx-3">
                                                <input :disabled="loading" @click.stop="" @click="setWithHeader()"
                                                    name="with_header_radio_header1" type="radio" :value="true"
                                                    v-model="download_options.withHeader"> {{ trans('quote.label.yes') }}
                                            </label>
                                            <label class="mx-3">
                                                <input :disabled="loading" @click.stop="" @click="setWithHeader()"
                                                    name="with_header_radio_header1" type="radio" :value="false"
                                                    v-model="download_options.withHeader"> {{ trans('quote.label.no') }}
                                            </label>
                                        </div>
                                    </template>
                                    <template v-if="download_options.withHeader">
                                        <h5>{{ trans('quote.label.do_you_want_a_client_logo') }}:</h5>
                                        <div class="d-flex align-items-center">
                                            <label class=" mx-3">
                                                <input :disabled="loading" @click.stop=""
                                                    name="with_header_radio1" type="radio"
                                                    @click="setWithHeader()"
                                                    :value="1" v-model="download_options.withClientLogo"> {{ trans('quote.label.yes') }}
                                            </label>
                                            <label class="mx-3">
                                                <input :disabled="loading" @click.stop=""
                                                    name="with_header_radio2" type="radio"
                                                    @click="setWithHeader()"
                                                    :value="2" v-model="download_options.withClientLogo">
                                                {{ trans('quote.label.no') }}
                                            </label>
                                            <label class=" mx-3">
                                                <input :disabled="loading" @click.stop=""
                                                    name="with_header_radio1" type="radio"
                                                    @click="setWithHeader()"
                                                    :value="3" v-model="download_options.withClientLogo"> {{ trans('quote.label.nothing') }}
                                            </label>
                                        </div>
                                    </template>
                                    <template v-if="download_options.imagePortada && download_options.withHeader">
                                        <div class="d-flex align-items-center" v-if="download_options.imagePortada"
                                            id="zoomImage">
                                            {{-- se cambia carpeta word por la de portada --}}
                                            <img class="showWithCover"
                                                :src="download_options.imagePortada"
                                                style="margin: 9px;width: 170px;height: auto;transition: transform .2s;cursor: pointer">
                                        </div>
                                        <div style="height: 170px; width: 170px" v-if="caja"></div>
                                    </template>
                                    <h5>{{ trans('quote.label.with_rates') }}:</h5>
                                    <div class="d-flex align-items-center">
                                        <label class="mx-3">
                                            <input :disabled="loading" @click.stop=""
                                                name="with_prices_radio1" type="radio"
                                                :value="true" v-model="download_options.withPrices"> {{ trans('quote.label.yes') }}
                                        </label>
                                        <label class="mx-3">
                                            <input :disabled="loading" @click.stop=""
                                                name="with_prices_radio2" type="radio"
                                                :value="false" v-model="download_options.withPrices">
                                            {{ trans('quote.label.no') }}
                                        </label>
                                    </div>
                                    <div class="d-flex my-3">
                                        <button :disabled="loading" class="btn btn-primary mx-1" type="button"
                                            @click.stop="showItineraryPackage()">
                                            <i class="fa fa-spin fa-spinner"
                                                v-if="loading"></i> {{ trans('quote.label.download') }}
                                        </button>
                                        <?php if (in_array(strtolower(auth()->user()->code), ['mlu', 'fgg', 'admin'])): ?>
                                            <button @click.stop="linkToWord()" :disabled="loading"
                                                class="btn btn-primary mx-1" type="button">
                                                Public Link
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="d-flex content-details">
                <div class="mr-5">
                    <iframe class="rounded" frameborder="0"
                        style="border:0; height: 400px; opacity: 0.9;width: 320px"
                        :src="package.map_link"></iframe>
                </div>
                <div>
                    <h2 class="mb-5 text-uppercase">@{{ translations.label.tour_overview }}</h2>
                    <p class="mb-3" v-html="package.descriptions.description"></p>
                    <h2 class="mt-5 text-uppercase">@{{ translations.label.included }}</h2>
                    <div class="d-flex inclusions">
                        <div>
                            <div class="inclusions-item item-r d-flex my-4">
                                <span class="icon-ac-Group"></span>
                                <div class="ml-3">
                                    <h4>@{{ translations.label.breakfast }}:</h4>
                                    <span>{{trans('package.label.includes_breakfast')}}</span>
                                </div>
                            </div>
                            <div class="inclusions-item item-r d-flex my-4">
                                <span class="icon-ac-comida-3"></span>
                                <div class="ml-3">
                                    <h4>@{{ translations.label.lunch }}:</h4>
                                    <span v-if="package.included_services.lunch_days.length > 0">
                                        <span
                                            v-if="package.included_services.lunch_days.length === 1">{{trans('package.label.the_day')}}</span>
                                        <span
                                            v-if="package.included_services.lunch_days.length > 1">{{trans('package.label.the_days')}}</span>
                                        @{{ package.included_services.lunch_days | formatDayArray }}
                                    </span>
                                    <span v-else>
                                        {{trans('package.label.mentioned_in_your_itinerary')}}.
                                    </span>
                                </div>
                            </div>
                            <div class="inclusions-item item-r d-flex my-4">
                                <span class="icon-ac-comida"></span>
                                <div class="ml-3">
                                    <h4>@{{ translations.label.dinner }}:</h4>
                                    <span v-if="package.included_services.dinner_days.length > 0">
                                        <span
                                            v-if="package.included_services.dinner_days.length === 1">{{trans('package.label.the_day')}}</span>
                                        <span
                                            v-if="package.included_services.dinner_days.length > 1">{{trans('package.label.the_days')}}</span>
                                        @{{ package.included_services.dinner_days | formatDayArray }}
                                    </span>
                                    <span v-else>
                                        {{trans('package.label.mentioned_in_your_itinerary')}}.
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="inclusions-item d-flex my-4">
                                <span class="icon-building"></span>
                                <div class="ml-3">
                                    <h4>@{{ translations.label.accommodation }}:</h4>
                                    <span v-for="(accommodation,index) in package.included_services.accommodation">
                                        @{{ accommodation.nights }} {{ trans('package.label.nights_in') }} @{{ accommodation.city }}
                                        <span v-if="index !== (package.included_services.accommodation.length - 1)">/</span>
                                    </span>
                                    <span v-if="package.included_services.accommodation.length === 0">
                                        {{trans('package.label.does_not_include')}}.
                                    </span>
                                </div>
                            </div>
                            <div class="inclusions-item d-flex my-4">
                                <span class="icon-ac-users"></span>
                                <div class="ml-3">
                                    <h4>@{{ translations.label.guide_and_entrances }}:</h4>
                                    <span v-if="package.included_services.include_guides_tickets.guides">
                                        @{{ translations.label.english_and_spanish_guide }}
                                    </span><br>
                                    <span v-if="package.included_services.include_guides_tickets.tickets">
                                        @{{ translations.label.entrances_to_all_destinations_mentiones }}
                                    </span>
                                </div>
                            </div>
                            <div class="inclusions-item d-flex my-4">
                                <span class="icon-ac-bus"></span>
                                <div class="ml-3">
                                    <h4>@{{ translations.label.transportation }}:</h4>
                                    <span v-if="package.included_services.transport">
                                        @{{ translations.label.transport_to_all_destinations_mentiones }}
                                    </span>
                                    <span v-else>
                                        {{trans('package.label.does_not_include')}}.
                                    </span>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
            <div class="d-flex justify-content-between my-5 content-details">
                <article class="mr-2" v-if="package.destinations.destinations.length > 0">
                    <h3 class="">@{{ translations.label.start_and_final_destination }}</h3>
                    <div class="destination d-flex mb-5">
                        <span>1. @{{ package.destinations.destinations[0].state }}</span>
                        <span>2. @{{ package.destinations.destinations[package.destinations.destinations.length - 1].state }}</span>
                    </div>
                    <h3 class="mb-4">@{{ translations.label.destinations }}</h3>
                    <div class="destination mb-5">
                        <p v-for="(destinations,index) in package.destinations.destinations">
                            @{{ index + 1 }}. @{{ destinations.state }}
                        </p>
                    </div>
                    <div class="my-4">
                        <span class="not-include">* @{{ translations.label.air_tickets_not_included }}</span>
                        <br>
                        <span class="not-include" v-if="package.id == 688 || package.id == 1449">* @{{ translations.label.kuelap_not_included }}</span>
                    </div>
                </article>
                <article class="program mx-1" v-if="package.descriptions.itinerary.length > 0">
                    <div class="program-col">
                        <h3 class="text-uppercase">@{{ translations.label.programme }}</h3>
                        <div class="content-list" id="NOThidingScrollBar">
                            <p v-for="itinerary in package.descriptions.itinerary">
                                <span class="day-pin">{{trans('package.label.day')}} @{{ itinerary.day }} |</span>
                                @{{ itinerary.description }}
                            </p>
                        </div>
                    </div>
                </article>
                <article class="pl-5" v-if="package.highlights.length > 0">
                    <h3 class="">@{{ translations.label.highlights }}</h3>
                    <div class="carousel">
                        <!-- Carousel start -->
                        <div>
                            <div class="card-images">
                                <b-carousel
                                    ref="carouselHighlights"
                                    id="carousel-1"
                                    no-animation
                                    v-model="slideHighlight">
                                    <b-carousel-slide v-for="highlight in package.highlights"
                                        :img-src="highlight.url.replace('litomarketing', 'litodti')"></b-carousel-slide>
                                </b-carousel>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span style="font-size: 11px;">@{{ package.highlights[slideHighlight].name }}</span>
                                <span class="prev" @click.prevent="prev()">
                                    <span class="icon-ac-arrow-left"></span>
                                </span>
                                <span class="next" @click.prevent="next()">
                                    <span class="icon-ac-arrow-right"></span>
                                </span>
                            </div>

                        </div>
                    </div>

                </article>
            </div>
        </div>
    </b-skeleton-wrapper>
</section>

<b-skeleton-wrapper :loading="loading_package" v-if="client_id != '' && package.id != null">
    <template #loading>
        <section class="packages__details my-5 container justify-content-between">
            <b-row>
                <b-col cols="12" class="mb-3 mt-3"></b-col>
            </b-row>
            <b-row>
                <b-col cols="6" class="mb-2">
                    <b-skeleton class="mt-3" width="60%" height="10%"></b-skeleton>
                    <b-skeleton class="mt-3" height="10%"></b-skeleton>
                    <b-skeleton class="mt-3" height="10%"></b-skeleton>
                    <b-skeleton class="mt-3" height="10%"></b-skeleton>
                    <b-skeleton class="mt-3" height="10%"></b-skeleton>
                    <b-skeleton class="mt-3" height="10%"></b-skeleton>
                </b-col>
                <b-col cols="6" class="mb-2">
                    <b-skeleton class="mt-3" width="60%" height="10%"></b-skeleton>
                    <b-skeleton class="mt-3" height="10%"></b-skeleton>
                    <b-skeleton class="mt-3" height="10%"></b-skeleton>
                    <b-skeleton class="mt-3" height="10%"></b-skeleton>
                    <b-skeleton class="mt-3" height="10%"></b-skeleton>
                    <b-skeleton class="mt-3" height="10%"></b-skeleton>
                </b-col>
            </b-row>
        </section>
    </template>
    <section class="packages__details my-5 container pt-5 d-flex justify-content-between">
        <article class="content-calendar mr-0" style="width: 50%">
            <b-overlay :show="loading_search_package" :opacity="0.42" rounded="lg">
                <div class="mb-5">
                    <h2>1. @{{ translations.label.day_arrival_departure }}</h2>
                </div>
                <div class="mb-5">
                    <div class="d-flex align-items-start justify-content-between mb-3"
                        v-if="package.fixed_outputs.length > 0">
                        <p class="mr-1 py-4" style="font-size: 15px">@{{ translations.label.fixed_departures }}:</p>
                        <div class="flex justify-center items-center datepicker-calender">
                            <v-date-picker v-model="range" is-required :min-date="min_date">
                                <template>
                                    <span class="icon-ac-calendar1"></span>
                                    <label for="">@{{ translations.label.check_in }}:</label>
                                    <select name="select" class="select-fixed-outputs"
                                        @change="changeDateFixed(daySelected)" v-model="daySelected">
                                        <option :value="date.date" v-for="date in package.fixed_outputs"> @{{
                                                date.date
                                                | formattedDate }}
                                        </option>
                                    </select>
                                    <label for="">@{{ translations.label.check_out }}:</label>
                                    <input :value="range.end | formattedDate"
                                        class="ml-1" />
                                </template>
                            </v-date-picker>
                        </div>
                    </div>
                    <v-date-picker class="flex justify-center items-center datepicker-calender__secondary"
                        v-model="range"
                        locale="es" :min-date="min_date" v-if="isPageLoaded && package.fixed_outputs.length === 0">
                        <template>
                            <span class="icon-ac-calendar1"></span>
                            <label class="mr-1">@{{ translations.label.check_in }}:</label>
                            <input :value="range.start | formattedDate"
                                class="ml-1" />
                            <span class="icon-ac-calendar1"></span>
                            <label class="mr-1">@{{ translations.label.check_out }}:</label>
                            <input :value="range.end | formattedDate"
                                class="ml-1" />
                        </template>
                    </v-date-picker>
                </div>
                <button type="button" @click="cambiar()" class="hide" ref="cambiarfecha"></button>
                <div class="mb-5">
                    <v-date-picker :columns="$screens({ default: 1, lg: 2 })" v-model="range" color="red"
                        fillMode="outline" mode="date" mode="range" ref="calendar_view"
                        :value="range"
                        is-range locale="es" :min-date='min_date' :max-date='max_date'
                        @dayclick="onDayClick"
                        v-if="package.fixed_outputs.length === 0">
                    </v-date-picker>
                    <v-date-picker :columns="$screens({ default: 1, lg: 2 })" v-model="range" color="red"
                        fillMode="outline" mode="date" mode="range" style="pointerEvents:none;"
                        :value="range"
                        ref="calendarfixed"
                        is-range locale="es" :min-date='min_date' :max-date='package.rate.date_to'
                        v-if="package.fixed_outputs.length > 0">
                    </v-date-picker>
                </div>
                <h2 v-if="package.cancellation_policy.last_day_cancel != '' && package.cancellation_policy.last_day_cancel != null">
                    {{ trans('quote.label.cancellation_without_penalty') }}: @{{
                        getFormatDate(package.cancellation_policy.last_day_cancel) }}
                </h2>
                <div v-if="message_date != ''" class="alert alert-danger" v-html="message_date"></div>
            </b-overlay>
        </article>
        <article class="content-pax ml-5 pl-2" style="width: 50%">
            <b-overlay :show="loading_search_package" :opacity="0.42" rounded="lg">
                <div class="mb-5">
                    <h2>2. @{{ translations.label.number_passengers }}</h2>
                </div>
                <div class="mb-4 border-bottom">
                    <p class="mb-3">@{{ translations.label.adults }}</p>
                    <div class="d-flex align-items-center justify-content-between pb-4">
                        <span v-if="package.adult_age_from > 0">@{{ translations.label.age }}: @{{package.adult_age_from}} @{{ translations.label.years_more }}</span>
                        <div class="input-actions">
                            <vue-numeric-input v-model="quantity_persons.adults"
                                @input="changeAdult"
                                :min="1"
                                :precision="0" id="qty_adult"></vue-numeric-input>
                        </div>
                    </div>
                </div>
                <div class="mb-4 border-bottom"
                    v-if="package.allow_child && package.prices_children.with_bed.price !== 0 || package.prices_children.without_bed.price !== 0">
                    <p class="mb-3"
                        v-if="package.prices_children.with_bed.price !== 0 && package.prices_children.without_bed.price !== 0">
                        @{{ translations.label.children }}:</p>
                    <div class="pb-4">
                        <div v-if="package.prices_children.with_bed.price !== 0">
                            <span>@{{ translations.label.with_bed }}</span>
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <span class="span-xs">@{{ translations.label.of }} @{{ package.prices_children.with_bed.min_age }} @{{ translations.label.to }} @{{ package.prices_children.with_bed.max_age }} @{{ translations.label.years }}</span>
                                <div class="input-actions">
                                    <vue-numeric-input v-model="quantity_persons.child_with_bed"
                                        @input="changeInput"
                                        :min="0"
                                        :precision="0" id="qty_child_with_bed"></vue-numeric-input>
                                </div>
                            </div>
                        </div>
                        <div v-if="package.prices_children.without_bed.price !== 0">
                            <span>@{{ translations.label.without_bed }}</span>
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <span class="span-xs">@{{ translations.label.of }} @{{ package.prices_children.without_bed.min_age }} @{{ translations.label.to }} @{{ package.prices_children.without_bed.max_age }} @{{ translations.label.years }}</span>
                                <div class="input-actions">
                                    <vue-numeric-input v-model="quantity_persons.child_without_bed"
                                        @input="changeInput"
                                        :min="0"
                                        :precision="0"
                                        id="qty_child_without_bed"></vue-numeric-input>
                                </div>
                            </div>
                        </div>
                        <span class="my-3 span-xs" v-if="package.allow_infant">*@{{ translations.label.toddlers_from }} @{{package.infant_age_allowed.min}} @{{ translations.label.months_to }} @{{package.infant_age_allowed.max}} @{{package.infant_age_allowed.year}} @{{translations.label.year_do_not_pay}}</span>
                    </div>
                </div>
                <div class="mb-5 d-flex justify-content-between align-items-start">
                    <div class="row">
                        <div class="col-md-12">
                            <h2>3. @{{ translations.label.rooms }}</h2>
                        </div>
                    </div>
                    <div class="mb-4">
                        <div class="alert alert-danger" v-if="validate_rooms_and_pax" style="font-size: 11px;">
                            <i class="fas fa-info-circle"></i>
                            {{trans('package.validate.quantity_rooms')}}
                        </div>
                        <div class="d-flex align-items-center justify-content-between pb-4">
                            <span class="span-xs mr-4">@{{ translations.label.simple }}</span>
                            <div class="input-actions"
                                :class="{'validate_rooms':validate_rooms_and_pax && !disable_room_sgl}">
                                <vue-numeric-input v-model="rooms.quantity_sgl"
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
                                <vue-numeric-input v-model="rooms.quantity_dbl"
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
                                <vue-numeric-input v-model="rooms.quantity_tpl"
                                    @input="validateQuantityRoomTpl"
                                    @change="validateQuantityRoomTpl"
                                    :min="0"
                                    :disabled="disable_room_tpl"
                                    :precision="0" id="quantity_rooms_tpl"></vue-numeric-input>
                            </div>
                        </div>
                        <div class="alert alert-danger"
                            v-if="validate_rooms_and_pax && package.prices_children.with_bed.price !== 0 && quantity_persons.child_with_bed > 0"
                            style="font-size: 11px;">
                            <i class="fas fa-info-circle"></i>
                            {{trans('package.validate.quantity_child_rooms')}}
                        </div>
                        <div class="d-flex align-items-center justify-content-between pb-4"
                            v-for="(child,index_child) in quantity_persons.child_with_bed"
                            v-if="package.prices_children.with_bed.price !== 0 && quantity_persons.child_with_bed > 0">
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
                <div class="mb-5 d-flex justify-content-between align-items-start">
                    <h2>4. @{{ translations.label.hotel_category }}</h2>
                    <div class="mb-4">
                        <v-select class="form-control" v-model="categorySelected"
                            :options="package.categories" label="name" @input="changeCategory"
                            :reduce="category => category.id">
                        </v-select>
                    </div>
                </div>
                <div class="mb-5 d-flex justify-content-between align-items-start border-bottom">
                    <h2>5. @{{ translations.label.type_of_service }}</h2>
                    <div class="mb-4 pb-4">
                        <v-select class="form-control" v-model="typeServiceSelected"
                            label="name"
                            :options="package.type_services"
                            @input="changeTypeService"
                            :reduce="type_service => type_service.id">
                        </v-select>
                    </div>
                </div>
                <div class="mb-5 d-flex justify-content-between align-items-start">
                    <div v-if="package.country_id != 89 || holidays=='0'">
                        <div class="d-flex my-4">
                            <div class="text-total" v-show="!package_not_found">
                                <span class="d-block">@{{ quantity_persons.adults }} @{{ translations.label.adults_s }}</span>
                                <span class="d-block span-xs" v-if="rooms.quantity_sgl > 0">@{{ translations.label.x_adult_simple_room }}</span>
                                <span class="d-block span-xs" v-if="rooms.quantity_dbl > 0">@{{ translations.label.x_adult_double_room }}</span>
                                <span class="d-block span-xs" v-if="rooms.quantity_tpl > 0">@{{ translations.label.x_adult_triple_room }}</span>
                            </div>
                            <div v-show="!package_not_found">
                                <span class="d-block">$ @{{ getPriceAmount(package.amounts.total_adults) }}</span>
                                <span class="d-block span-xs" v-if="rooms.quantity_sgl > 0">$ @{{ getPriceAmount(package.amounts.price_per_adult.room_sgl) }}</span>
                                <span class="d-block span-xs" v-if="rooms.quantity_dbl > 0">$ @{{ getPriceAmount(package.amounts.price_per_adult.room_dbl) }}</span>
                                <span class="d-block span-xs" v-if="rooms.quantity_tpl > 0">$ @{{ getPriceAmount(package.amounts.price_per_adult.room_tpl) }}</span>
                            </div>
                        </div>
                        <div class="d-flex my-4" v-if="package.amounts.price_per_child.with_bed != 0">
                            <div class="text-total" v-show="!package_not_found">
                                <span class="d-block">@{{ package.quantity_child.with_bed }} @{{ translations.label.children_with_bed }}</span>
                                <span class="d-block span-xs">@{{ translations.label.x_child_with_bed }}</span>
                            </div>
                            <div v-show="!package_not_found">
                                <span class="d-block">$ @{{ getPriceAmount(package.amounts.total_children.with_bed) }}</span>
                                <span
                                    class="d-block span-xs">$ @{{ getPriceAmount(package.amounts.price_per_child.with_bed) }}</span>
                            </div>
                        </div>
                        <div class="d-flex pb-5 my-4 border-bottom"
                            v-if="package.amounts.price_per_child.without_bed != 0">
                            <div class="text-total" v-show="!package_not_found">
                                <span
                                    class="d-block">@{{ package.quantity_child.without_bed }} @{{ translations.label.children_without_bed }}</span>
                                <span class="d-block span-xs">@{{ translations.label.x_child_without_bed }}</span>
                            </div>
                            <div v-show="!package_not_found">
                                <span class="d-block">$ @{{ getPriceAmount(package.amounts.total_children.without_bed) }}</span>
                                <span
                                    class="d-block span-xs">$ @{{ getPriceAmount(package.amounts.price_per_child.without_bed) }}</span>
                            </div>
                        </div>
                        <div class="d-flex total-price" v-if="!package_not_found">
                            <div class="text-total" style="line-height: 1;">
                                @{{ (userTypeIsClient && commission_status === 1) ? @json(__('global.label.with_commission')) : translations.label.total }}
                            </div>
                            <div>
                                $ @{{ userTypeIsClient ? totalAmountWithCommission : package.amounts.total_amount }}
                            </div>
                        </div>

                    </div>
                    <div v-else>
                        <div class="d-flex my-4">
                        </div>
                    </div>
                    <div class="d-block">
                        <template v-if="!message_date_bloqueo">
                            <button type="button" class="btn-primary" style="width: 175px;"
                                @click="goToReservationDetails"
                                :disabled="!!loading_search_package || package_not_found">
                                <i class="fas fa-spinner fa-spin" v-show="loading_search_package"></i>
                                @{{ translations.label.continue }}
                            </button>
                        </template>

                    </div>
                </div>
            </b-overlay>
        </article>
    </section>
</b-skeleton-wrapper>

<div class="container mt-5 mb-5" v-if="client_id === ''">
    <div class="jumbotron bg-danger">
        <h2 class="text-center text-white">
            <i class="fas fa-exclamation-triangle"></i> @{{ translations.label.you_must_select_customer }}... <i
                class="fas fa-hand-point-up"></i>
        </h2>
    </div>
</div>

<section class="packages__details my-5 container pb-5" v-if="package.id === null && !loading_package">
    <div class="jumbotron">
        <h2 class="text-center"><i class="fas fa-sad-tear"></i> ¡@{{ translations.label.were_sorry_information_found
                }}.</h2>
    </div>
</section>

<package-recommended></package-recommended>
<section-write-us-component></section-write-us-component>
@endsection

@section('js')
<script>
    new Vue({
        el: "#app",
        data: {
            client_id: "",
            range: {
                start: null,
                end: null
            },
            min_date: moment().add(1, "days").format("YYYY-MM-DD"),
            max_date: "",
            lang: "en",
            quantity_persons: {
                adults: 2,
                child_with_bed: 0,
                child_without_bed: 0,
                age_child: [{
                    age: 1
                }]
            },
            rooms: {
                quantity_sgl: 0,
                quantity_dbl: 0,
                quantity_child_dbl: 0,
                quantity_tpl: 0,
                quantity_child_tpl: 0
            },
            package_not_found: false,
            type_service: 1,
            package_ids: [],
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
                    name: "",
                    description: "",
                    itinerary: [],
                    itinerary_link: ""
                },
                destinations: {
                    destinations: [],
                    destinations_display: ""
                },
                tag: {
                    name: "",
                    color: ""
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
                        tickets: false
                    }
                },
                fixed_outputs: [],
                highlights: [],
                type_services: [],
                categories: [],
                prices_children: {
                    with_bed: {
                        price: 0,
                        min_age: 0,
                        max_age: 0
                    },
                    without_bed: {
                        price: 0,
                        min_age: 0,
                        max_age: 0
                    }
                },
                quantity_child: {
                    quantity_children: 0,
                    with_bed: 0,
                    without_bed: 0
                },
                amounts: {
                    total_amount: 0,
                    price_per_adult: {
                        room_sgl: 0,
                        room_dbl: 0,
                        room_tpl: 0
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
            translations: {
                label: {},
                validations: {},
                messages: {}
            },
            slideHighlight: 0,
            typeServiceSelected: "",
            categorySelected: "",
            childrenRoomSelected: [],
            loading_package: false,
            loading_search_package: false,
            show_package: false,
            days_package: 1,
            daySelected: null,
            disable_room_sgl: false,
            disable_room_dbl: false,
            disable_room_tpl: false,
            date_to_days: 0,
            validate_rooms_and_pax: false,
            rooms_children: [{
                    value: "double",
                    name: "Doble",
                    disabled: false
                },
                {
                    value: "triple",
                    name: "Triple",
                    disabled: false
                }
            ],
            children_accommodation: [],
            message_date: "",
            message_date_bloqueo: false,
            year_options: [{
                    text: 2023,
                    value: 2023
                },
                {
                    text: 2024,
                    value: 2024
                }
            ],
            loading: false,
            caja: false,
            urlPortada: "",
            download_options: {
                withHeader: false,
                select_itinerary_with_cover: "",
                withPrices: false,
                withClientLogo: 2,
                imagePortada: ""
            },
            holidays: 0,
            isPageLoaded: false,
            gtmSent: false,
            commission_percentage: 0,
            commission_status: 0,
        },
        created: function() {
            this.client_id = localStorage.getItem("client_id");
            this.lang = localStorage.getItem("lang");

            this.$root.$on("changeMarkup", function() {
                this.client_id = localStorage.getItem("client_id");
                this.getPackagesDetails();
            });

            if (localStorage.getItem("parameters_packages_details") || this.getCookie("parameters_packages_details")) {

                let parameters = "";

                if (localStorage.getItem("parameters_packages_details")) {
                    parameters = JSON.parse(localStorage.getItem("parameters_packages_details"));
                }

                if (this.getCookie("parameters_packages_details")) {
                    parameters = JSON.parse(this.getCookie("parameters_packages_details"));
                }

                if (parameters.date) {

                    let momentA = moment(parameters.date, "YYYY-MM-DD");
                    let momentB = moment(moment().format("YYYY-MM-DD"), "YYYY-MM-DD");
                    let date_used = "";
                    if (momentA >= momentB) {
                        date_used = parameters.date;
                    } else {
                        date_used = moment().format("YYYY-MM-DD");
                    }


                    this.range.start = new Date(moment(date_used));


                    this.date_to_days = parameters.date_to_days;
                    this.range.end = new Date(moment(date_used).add(parameters.date_to_days, "days").format("YYYY-MM-DD"));
                }

                if (parameters.quantity_persons) {
                    this.quantity_persons = parameters.quantity_persons;
                    this.validate_rooms();
                }
                if (parameters.type_service) {
                    this.type_service = parameters.type_service;
                }
                if (parameters.package_ids) {
                    this.package_ids = parameters.package_ids;
                }

                document.cookie = window.parametersPackagesDetails + "=;domain=" + window.domain + ";";

            } else {

                window.location.href = "/packages";
            }
        },
        mounted() {
            this.setTranslations();
            this.getPackagesDetails();
            setTimeout(() => {
                this.isPageLoaded = true;
            }, 0);
        },
        computed: {
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
            validUser: function() {
                let codes = localStorage.getItem("codes_download");

                if (codes == null || codes == "") {
                    codes = ["ESR", "COR", "MRS", "APA", "KBG", "JSV", "MLU", "MAA", "ADMIN"];
                } else {
                    codes = JSON.parse(codes);
                }

                let code = '{{ strtoupper(Auth::user()->code) }}';
                localStorage.setItem("codes_download", JSON.stringify(codes));

                return (codes.indexOf(code) > -1);
            },
            setWithHeader: function() {
                let vm = this;

                setTimeout(() => {
                    let idCliente = localStorage.getItem("client_id");
                    let destinies = vm.package.destinations.destinations_display;
                    let title = vm.package.descriptions.name;
                    let type_package = vm.package.descriptions.label;
                    let date_operations = vm.package.schedule_days;
                    vm.download_options.imagePortada = "";
                    vm.urlPortada = "";

                    if (vm.download_options.withHeader) {
                        if (vm.download_options.withClientLogo == 1) {
                            vm.loading = true;
                            vm.caja = true;

                            axios.post(window.a3BaseQuoteServerURL + "api/quote/imageCreatePackage", {
                                clienteId: idCliente,
                                portada: vm.package.portada_link,
                                title: title,
                                destinies: destinies,
                                type_package: type_package,
                                date_operations: date_operations,
                                estado: vm.download_options.withClientLogo,
                                lang: localStorage.getItem("lang"),
                                days: vm.date_to_days,
                                date_from: vm.package.rate.date_from,
                                date_to: vm.package.rate.date_to,
                                code: localStorage.getItem("code")
                            }).then((result) => {

                                vm.download_options.imagePortada = result.data.image;
                                vm.caja = false;
                                vm.loading = false;
                                vm.urlPortada = result.data.portada;
                            });

                            // vm.download_options.withHeader = true
                        } else if (vm.download_options.withClientLogo == 2) {
                            // vm.download_options.withHeader = true
                            vm.loading = true;
                            vm.caja = true;

                            axios.post(window.a3BaseQuoteServerURL + "api/quote/imageCreatePackage", {
                                clienteId: 15766,
                                portada: vm.package.portada_link,
                                title: title,
                                destinies: destinies,
                                type_package: type_package,
                                date_operations: date_operations,
                                estado: vm.download_options.withClientLogo,
                                lang: localStorage.getItem("lang"),
                                days: vm.date_to_days,
                                date_from: vm.package.rate.date_from,
                                date_to: vm.package.rate.date_to,
                                code: localStorage.getItem("code")
                            }).then((result) => {

                                vm.download_options.imagePortada = result.data.image;
                                vm.caja = false;
                                vm.loading = false;
                                vm.urlPortada = result.data.portada;

                            });
                        } else {
                            vm.download_options.select_itinerary_with_cover = "";
                            vm.download_options.withHeader = true;
                            vm.loading = true;
                            vm.caja = true;

                            axios.post(window.a3BaseQuoteServerURL + "api/quote/imageCreatePackage", {
                                clienteId: idCliente,
                                portada: vm.package.portada_link,
                                title: title,
                                destinies: destinies,
                                type_package: type_package,
                                date_operations: date_operations,
                                estado: vm.download_options.withClientLogo,
                                lang: localStorage.getItem("lang"),
                                days: vm.date_to_days,
                                date_from: vm.package.rate.date_from,
                                date_to: vm.package.rate.date_to,
                                code: localStorage.getItem("code")
                            }).then((result) => {
                                vm.download_options.imagePortada = result.data.image;
                                vm.caja = false;
                                vm.loading = false;
                                vm.urlPortada = result.data.portada;
                            });
                        }
                    }
                }, 100);
            },
            linkToWord: function() {
                var link = `${baseExternalURL}api/public_link/itinerary?client_id=${localStorage.getItem("client_id")}
                &lang=${localStorage.getItem("lang")}&package_id=${this.package_ids}&year=${localStorage.getItem("packages_year")}
                &portada=${(this.urlPortada != "") ? (this.download_options.imagePortada) : ""}&days=${this.date_to_days}
                &category=${this.categorySelected}&type_service=${this.typeServiceSelected}
                &use_prices=${(this.download_options.withPrices) ? 1 : 0}
                &with_client_logo=${this.download_options.withClientLogo}`;
                navigator.clipboard.writeText(link).then(() => {
                    this.$toast.success("El enlace fue copiado al portapapeles!", {
                        position: "top-right"
                    });
                }).catch(err => {
                    this.$toast.error(err + "!", {
                        position: "top-right"
                    });
                });
            },
            showItineraryPackage: function() {
                let validate = this.getQuantityPersonsRooms();
                if (validate) {
                    if (localStorage.getItem("user_type_id") == 4) {
                        dataLayer.push({
                            event: "download",
                            file_category: "itinerary_package"
                        });
                    }
                    this.validate_rooms_and_pax = false;

                    dataLayer.push({
                        "event": "view_item",
                        "currency": "USD",
                        "value": 1200,
                        "package_id": "LIMCUZ20",
                        "package_name": "Cuzco trip",
                        "items": []
                    });


                    let data = {
                        client_id: localStorage.getItem("client_id"),
                        lang: localStorage.getItem("lang"),
                        quantity_persons: this.quantity_persons,
                        category: this.categorySelected,
                        type_service: this.typeServiceSelected,
                        date_to_days: this.date_to_days,
                        package_ids: this.package_ids,
                        rooms: this.rooms,
                        children_accommodation: this.childrenRoomSelected,
                        use_header: this.download_options.withHeader,
                        client_logo: this.download_options.withClientLogo,
                        urlPortadaLogo: (this.urlPortada != "") ? (this.download_options.imagePortada) : "",
                        use_prices: this.download_options.withPrices,
                        package: this.package,
                        year: localStorage.getItem("packages_year")
                    };

                    let specialPackageIds = [1140, 1430, 1659, 1110, 1357];
                    if (specialPackageIds.includes(this.package.id) && this.package.descriptions.itinerary_link_current_year) {
                        window.open(this.package.descriptions.itinerary_link_current_year, "_blank");
                        return;
                    }

                    this.loading = true;
                    axios({
                        method: "POST",
                        url: baseExternalURL + "api/package/itinerary",
                        data: data,
                        responseType: "blob"
                    }).then((response) => {
                        this.loading = false;
                        var fileURL = window.URL.createObjectURL(new Blob([response.data]));
                        var fileLink = document.createElement("a");
                        fileLink.href = fileURL;
                        fileLink.setAttribute("download", this.package.descriptions.name + ".docx");
                        document.body.appendChild(fileLink);

                        fileLink.click();
                    }).catch((e) => {
                        this.loading = false;
                        console.log(e);
                    });
                } else {
                    this.validate_rooms_and_pax = true;
                }
            },
            getCookie(name) {
                const value = `; ${document.cookie}`;
                const parts = value.split(`; ${name}=`);
                if (parts.length === 2) return parts.pop().split(";").shift();
            },
            deleteCookie(name) {
                document.cookie = name + "=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;domain=" + window.domain + ";";
            },
            goModifyPackage() {
                window.location.href = "/package-modify";
            },
            getFormatDate: function(_date) {
                window.moment.locale(localStorage.getItem("lang"));
                return window.moment(_date).format("LL");
            },
            showMessagesDate: function() {
                this.message_date = "";
                this.message_date_bloqueo = false;

                if (localStorage.getItem("user_type_id") == 4) // cliente..
                {

                    this.message_date = "{{ trans('package.label.service_min_date_error') }}"; // <b>" + moment(this.package.min_date_reserve, 'YYYY-MM-DD').format('DD/MM/YYYY') + '</b>'

                    if (moment(this.range.start).format("YYYY-MM-DD") <= this.package.min_date_reserve) {
                        this.message_date_bloqueo = true;
                    }

                    // console.log(moment(this.range.start).format('YYYY-MM-DD'),this.package.min_date_reserve );
                } else {
                    if (moment(this.range.start).format("YYYY-MM-DD") <= this.package.min_date_reserve) {
                        this.message_date = "{{ trans('package.label.service_min_date_error') }}";
                    }

                }

                this.getPackage();
            },
            goBiosafetyProtocols() {
                window.location.href = "/biosafety-protocols";
            },
            setTranslations() {
                axios.get(baseURL + "translation/" + localStorage.getItem("lang") + "/slug/package").then((data) => {
                    this.translations = data.data;
                });
            },
            validate_rooms() {
                this.rooms.quantity_sgl = 0;
                this.rooms.quantity_dbl = 0;
                this.rooms.quantity_tpl = 0;
                let total_adults = parseInt(this.quantity_persons.adults);
                let total_children_bed = parseInt(this.quantity_persons.child_with_bed);
                if (total_children_bed === 0) {
                    this.rooms.quantity_child_dbl = 0;
                    this.rooms.quantity_child_tpl = 0;
                }
                if (total_adults === 1) {
                    this.rooms.quantity_sgl = 1;
                    this.disable_room_sgl = false;
                    this.disable_room_dbl = true;
                    this.disable_room_tpl = true;
                } else if (total_adults === 2) {
                    this.rooms.quantity_dbl = 1;
                    this.disable_room_sgl = false;
                    this.disable_room_dbl = false;
                    this.disable_room_tpl = true;
                    if (total_children_bed > 0) {
                        this.disable_room_tpl = false;
                    }
                } else if (total_adults === 3) {
                    this.rooms.quantity_tpl = 1;
                    this.disable_room_sgl = false;
                    this.disable_room_dbl = false;
                    this.disable_room_tpl = false;
                    // this.rooms_children[1].disabled = false
                } else if (total_adults > 3) {
                    this.disable_room_sgl = false;
                    this.disable_room_dbl = false;
                    this.disable_room_tpl = false;
                }
                this.resetChildrenAccommodation();
                this.assignedRoomChild(total_adults, total_children_bed);
            },
            assignedRoomChild: function(total_adults, total_child) {
                if (total_child > 0) {
                    if (total_adults % 2 === 0) {
                        if (total_adults === 2 && total_child === 1) {
                            this.rooms.quantity_sgl = 1;
                        }

                        for (let i = 0; i < total_child; i++) {
                            this.changeChildrenRoom(i, "double");
                        }
                    } else {
                        if (total_adults === 3 && total_child === 1) {
                            this.rooms.quantity_sgl = 1;
                            this.rooms.quantity_tpl = 1;
                        }
                        if (total_adults >= 3) {
                            for (let i = 0; i < total_child; i++) {
                                this.changeChildrenRoom(i, "triple");
                            }
                        }
                    }

                }
            },
            validateQuantityRoomSgl: function(event) {
                this.rooms.quantity_sgl = event;
                this.validateRoomsAndPax();
            },
            validateQuantityRoomChildDbl: function(event) {
                this.rooms.quantity_child_dbl = event;
                this.validateRoomsAndPax();
            },
            validateQuantityRoomDbl: function(event) {
                this.rooms.quantity_dbl = event;
                this.validateRoomsAndPax();
            },
            validateQuantityRoomTpl: function(event) {
                this.rooms.quantity_tpl = event;
                this.validateRoomsAndPax();
            },
            validateQuantityRoomChildTpl: function(event) {
                this.rooms.quantity_child_tpl = event;
                this.validateRoomsAndPax();
            },
            validateRoomsAndPax: function() {
                let validate = this.getQuantityPersonsRooms();
                if (validate) {
                    this.validate_rooms_and_pax = false;
                    this.getPackage();
                } else {
                    this.validate_rooms_and_pax = true;
                }
            },
            getQuantityPersonsRooms: function() {
                let validate = true;
                let adults = parseInt(this.quantity_persons.adults);
                let child = parseInt(this.quantity_persons.child_with_bed);
                let quantityPersonsRoomsSGL = parseInt(this.rooms.quantity_sgl);
                let quantityPersonsRoomsDBL = parseInt(this.rooms.quantity_dbl) * 2;
                let quantityPersonsRoomsChildDBL = parseInt(this.rooms.quantity_child_dbl);
                let quantityPersonsRoomsTPL = parseInt(this.rooms.quantity_tpl) * 3;
                let quantityPersonsRoomsChildTPL = parseInt(this.rooms.quantity_child_tpl);

                let total_accommodation_adults = parseInt(quantityPersonsRoomsSGL) + parseInt(quantityPersonsRoomsDBL - quantityPersonsRoomsChildDBL) + parseInt(quantityPersonsRoomsTPL - quantityPersonsRoomsChildTPL);
                let total_accommodation_child = parseInt(quantityPersonsRoomsChildDBL) + parseInt(quantityPersonsRoomsChildTPL);

                if (total_accommodation_adults !== adults) {
                    validate = false;
                }

                if (child > 0 && total_accommodation_child !== child) {
                    validate = false;
                }

                if (child > 0 && quantityPersonsRoomsChildDBL > 0 && quantityPersonsRoomsDBL === 0) {
                    validate = false;
                }

                if (child > 0 && quantityPersonsRoomsChildTPL > 0 && quantityPersonsRoomsTPL === 0) {
                    validate = false;
                }

                return validate;
            },
            prev() {
                this.$refs.carouselHighlights.prev();
            },
            next() {
                this.$refs.carouselHighlights.next();
            },
            changeInput: function() {
                this.validate_rooms();
                this.validateRoomsAndPax();
            },
            changeAdult: function() {
                this.validate_rooms();
                this.validateRoomsAndPax();
            },
            chargeEventGtm: function(package) {
                if (localStorage.getItem("user_type_id") == 4) {
                    dataLayer.push({
                        "event": "view_item",
                        "currency": "USD",
                        "value": package.amounts.total_amount,
                        "package_id": package.id,
                        "package_code": package.code,
                        "package_name": package.descriptions.name_gtm.toLocaleUpperCase("en"),
                        "items": package.services_gtm
                    });
                }
            },
            getPackagesDetails() {
                if (this.client_id) {
                    this.loading_package = true;
                    this.show_package = false;
                    this.gtm = false;

                    if (localStorage.getItem("user_type_id") == 4) {
                        this.gtm = true;
                    }

                    let data = {
                        client_id: this.client_id,
                        lang: localStorage.getItem("lang"),
                        date: moment(this.range.start).format("YYYY-MM-DD"),
                        quantity_persons: this.quantity_persons,
                        type_service: this.type_service,
                        rooms: this.rooms,
                        package_ids: this.package_ids,
                        limit: 1,
                        gtm: this.gtm
                    };

                    axios.post(
                        baseExternalURL + "services/client/packages",
                        data
                    ).then((result) => {
                        this.loading_package = false;
                        if (result.data.count > 0) {
                            this.show_package = true;
                            this.package = result.data.data[0];
                            this.categorySelected = this.package.rate.category.id;
                            this.typeServiceSelected = this.package.rate.type_service.id;
                            this.days_package = this.package.nights + 1;
                            if (this.package.fixed_outputs.length > 0) {
                                this.daySelected = this.package.fixed_outputs[0].date;
                                this.range.start = new Date(moment(this.package.fixed_outputs[0].date));
                                this.range.end = new Date(moment(this.package.fixed_outputs[0].date).add(this.days_package, "days").format("YYYY-MM-DD"));
                            }

                            if (!this.gtmSent) {
                                this.chargeEventGtm(this.package);
                                this.gtmSent = true;
                            }


                            this.max_date = new Date(moment(this.package.rate.date_to).add(1, "days").format("YYYY-MM-DD"));

                            // Usar el máximo entre hoy y la fecha mínima del paquete
                            let today = moment().add('days', 1).format("YYYY-MM-DD");
                            let packageMinDate = moment(this.package.rate.date_from).format("YYYY-MM-DD");
                            let minDateToUse = today > packageMinDate ? today : packageMinDate;
                            this.min_date = new Date(moment(minDateToUse));

                            this.showMessagesDate();
                        } else {
                            this.package.id = null;
                        }
                    }).catch((e) => {
                        this.loading_package = false;
                    });
                }
            },
            getPackage() {
                if (this.rooms.quantity_sgl === 0 && this.rooms.quantity_dbl === 0 && this.rooms.quantity_tpl === 0) {
                    this.validate_rooms_and_pax = true;
                    return;
                }

                this.loading_search_package = true;
                this.gtm = false;

                if (localStorage.getItem("user_type_id") == 4) {
                    this.gtm = true;
                }

                let data = {
                    client_id: this.client_id,
                    lang: localStorage.getItem("lang"),
                    date: moment(this.range.start).format("YYYY-MM-DD"),
                    quantity_persons: this.quantity_persons,
                    category: this.categorySelected,
                    rooms: this.rooms,
                    type_service: this.typeServiceSelected,
                    package_ids: this.package_ids,
                    gtm: this.gtm
                };
                const year = moment(this.range.start).format("YYYY");
                this.holidays = this.checkForSpecialDates(this.range, year);
                axios.post(
                    baseExternalURL + "services/client/packages",
                    data
                ).then((result) => {
                    this.loading_search_package = false;
                    if (result.data.count > 0) {
                        this.package_not_found = false;
                        this.package = result.data.data[0];
                        this.commission_percentage = result.data.commission;
                        this.commission_status = result.data.commission_status;
                        if (!this.gtmSent) {
                            this.chargeEventGtm(this.package);
                            this.gtmSent = true;
                        }
                        this.max_date = new Date(moment(this.package.rate.date_to).add(1, "days").format("YYYY-MM-DD"));

                        // Usar el máximo entre hoy y la fecha mínima del paquete
                        let today = moment().add('days', 1).format("YYYY-MM-DD");
                        let packageMinDate = moment(this.package.rate.date_from).format("YYYY-MM-DD");
                        let minDateToUse = today > packageMinDate ? today : packageMinDate;
                        this.min_date = new Date(moment(minDateToUse));

                    } else {
                        this.$toast.error("Lo sentimos, no se encontro disponibilidad", {
                            position: "top-right"
                        });
                        this.package_not_found = true;
                    }
                }).catch((e) => {
                    this.loading_search_package = false;
                });
            },
            changeCategory() {
                this.getPackage();
            },
            changeTypeService() {
                this.getPackage();
            },
            onDayClick(day) {
                this.daySelected = day.date;
                this.$nextTick(() => {
                    this.$refs.cambiarfecha.click();
                });
            },
            cambiar() {
                this.range = {
                    start: this.daySelected,
                    end: new Date(moment(this.daySelected).add(this.days_package, "days").format("YYYY-MM-DD"))
                };

                this.showMessagesDate();
            },
            changeDateFixed: async function(daySelected) {
                this.range = {
                    start: new Date(moment(daySelected)),
                    end: new Date(moment(daySelected).add(this.days_package, "days").format("YYYY-MM-DD"))
                };
                let date = moment(daySelected);
                let month = date.format("M");
                let year = date.format("YYYY");
                const calendar = this.$refs.calendarfixed;
                await calendar.move({
                    month: parseInt(month),
                    year: parseInt(year)
                });

                this.showMessagesDate();

            },
            checkForSpecialDates(range, year) {
                // Definir los rangos de fechas especiales
                const specialDates = [{
                        name: "Semana Santa",
                        start: "03-29",
                        end: "04-05"
                    },
                    {
                        name: "Inti Raymi en Cusco",
                        start: "06-23",
                        end: "06-25"
                    },
                    {
                        name: "Fiestas Patrias",
                        start: "07-27",
                        end: "07-30"
                    },
                    {
                        name: "EXPOMINA Lima",
                        start: "09-09",
                        end: "09-11"
                    },
                    {
                        name: "Navidad y Año Nuevo",
                        start: "12-23",
                        end: "01-02"
                    }
                ];
                // Verificar si el rango seleccionado coincide con alguna fecha especial
                for (const specialDate of specialDates) {
                    const startDate = moment(`${year}-${specialDate.start}`);
                    const endDate = moment(`${year}-${specialDate.end}`);
                    // Ajustar para el caso de Navidad y Año Nuevo que cruza el año
                    if (specialDate.start === "12-23" && specialDate.end === "01-02") {
                        endDate.add(1, "year");
                    }

                    // Si hay superposición de fechas, retornar 1
                    if (range.start <= endDate && range.end >= startDate) {
                        return 1;
                    }
                }

                // Si no hay coincidencia, retornar 0
                return 0;
            },
            goToReservationDetails() {
                let validate = this.getQuantityPersonsRooms();
                if (validate) {
                    this.validate_rooms_and_pax = false;
                    let data = {
                        client_id: localStorage.getItem("client_id"),
                        lang: localStorage.getItem("lang"),
                        date: moment(this.range.start).format("YYYY-MM-DD"),
                        quantity_persons: this.quantity_persons,
                        category: this.categorySelected,
                        type_service: this.typeServiceSelected,
                        date_to_days: this.date_to_days,
                        package_ids: this.package_ids,
                        rooms: this.rooms,
                        children_accommodation: this.childrenRoomSelected
                    };
                    localStorage.setItem("parameters_packages_details", JSON.stringify(data));
                    window.location = baseURL + "package/reservation/details";
                } else {
                    this.validate_rooms_and_pax = true;
                }
            },
            changeChildrenRoom(index_child, room) {
                this.childrenRoomSelected[index_child] = room;
                let children_room_dbl = 0;
                let children_room_tpl = 0;
                for (let i = 0; i < this.childrenRoomSelected.length; i++) {
                    if (this.childrenRoomSelected[i] === "double") {
                        children_room_dbl++;
                    }
                    if (this.childrenRoomSelected[i] === "triple") {
                        children_room_tpl++;
                    }
                }
                this.rooms.quantity_child_dbl = children_room_dbl;
                this.rooms.quantity_child_tpl = children_room_tpl;
                this.validateRoomsAndPax();
            },
            resetChildrenAccommodation() {
                for (let i = 0; i < this.childrenRoomSelected.length; i++) {
                    this.childrenRoomSelected[i] = "";
                }
            },
            getPriceAmount(price) {

                console.log('Precio antes de comision: ' + price);
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
            formattedDate: function(date) {
                return moment(date).format("MMM D, YYYY");
            },
            formatDayArray(arr) {
                var outStr = "";
                if (arr.length === 1) {
                    outStr = arr[0];
                } else if (arr.length === 2) {
                    //joins all with "and" but no commas
                    //example: "bob and sam"
                    outStr = arr.join(" and ");
                } else if (arr.length > 2) {
                    //joins all with commas, but last one gets ", and" (oxford comma!)
                    //example: "bob, joe, and sam"
                    outStr = arr.slice(0, -1).join(", ") + ", y " + arr.slice(-1);
                }
                return outStr;
            },
            formatDestinations(arr) {
                let array = [];
                arr.forEach(function(state) {
                    array.push(state.state);
                });

                var outStr = "";
                if (array.length === 1) {
                    outStr = array[0];
                } else if (array.length === 2) {
                    //joins all with "and" but no commas
                    //example: "bob and sam"
                    outStr = array.join(" & ");
                } else if (array.length > 2) {
                    //joins all with commas, but last one gets ", and" (oxford comma!)
                    //example: "bob, joe, and sam"
                    outStr = array.slice(0, -1).join(", ") + " & " + array.slice(-1);
                }
                return outStr;
            }
        }
    });
</script>
@endsection
