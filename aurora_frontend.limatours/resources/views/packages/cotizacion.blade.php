@extends('layouts.app')

@section('content')

<section class="page-cotizacion">
    <loading-component v-show="loading"></loading-component>
    <div class="container-fluid" style="padding: 0 10rem;">
        <div class="col-md-12 pr-0 pl-0" v-if="editing_quote">
            <b-alert show variant="warning" v-if="editing_quote_user != null">
                <h3 class="alert-heading mb-4">
                    <i class="fas fa-book-reader"></i> {{ trans('quote.label.reading_mode') }}
                </h3>
                <p>
                    {{ trans('quote.label.edit_user') }}
                    <span
                        class="font-weight-bold">@{{editing_quote_user.name}}</span> {{ trans('quote.label.edit_user_quote') }}
                </p>
                <hr>
                <small class="mb-0">
                    {{ trans('quote.label.info_duplicate_user_quote') }}
                </small>
            </b-alert>
        </div>
        <div class="row ml-0">
            <div id="_overlay"></div>
            <div class="col-6 titulo">
                <h2 v-if="quote_open.id_original===''">{{ trans('quote.label.new_quote') }}</h2>
                <h2 v-if="quote_open.id_original!==''">{{ trans('quote.label.draft_of_the_quotation') }} N° @{{ quote_open.id_original }}</h2>
                <h5 v-if="quote_open!=''"><i class="fa fa-eraser"></i> N° @{{ quote_id }}</h5>
            </div>
            <div class="col-6 titulo" v-if="has_file && !(quote_open.file_id > 0)">
                <h2 class="file_quote">File #: @{{ file.file_code }}</h2>
            </div>

            <div class="col-6 titulo" v-if="quote_open.file_id">
                <h2 class="file_quote">
                    File #: @{{ quote_open.file_number }}
                    <span v-if="quote_open.reservation != ''">
                        <br>
                        Cliente: @{{ quote_open.reservation.client_code }}
                    </span>
                </h2>
            </div>

            <!-- <div class="col-6 d-flex justify-content-end" v-if="quote_id === null">
                    <div class="switch-container">
                        <label class="switch">
                            <input type="checkbox" v-model="switchValue" @change="onSwitchChange">
                            <span class="slider round"></span>
                        </label>
                        <span class="switch-label">@{{ switchValue ? 'Deshacer Cotización LATAM' : 'Cotización LATAM' }}</span>
                    </div>
                </div> -->

            <div class="col-12" v-if="client_file_incorrect && quote_open.file_id && quote_open.reservation != ''">
                <h4>
                    <span class="alert alert-warning" style="display: block">
                        <i class="fa fa-info-circle"></i> Esta cotización sólo puede ser reservada con el mismo cliente <b>@{{ quote_open.reservation.client_code }}</b>, por favor seleccione el cliente correcto o genere una copia de la cotización original para trabajar con otro cliente.
                    </span>
                </h4>
            </div>

            <div class="col-12 cotizacion-crear">
                <div class="form">
                    <div class="form-row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <i class="icon icon-folder"></i>
                                <input type="text" class="form-control" v-model="quote_name"
                                    placeholder="" @keyup.enter="updateNameQuote">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group"><i class="icon icon-calendar"></i>
                                <date-picker class="form-control" v-model="quote_date" :config="optionsR"
                                    @dp-show="showDatePickerQuote"
                                    @dp-change="updateDateInQuote"
                                    :key="updateDatePickerQuote"
                                    :disabled="isQuoteBlocked"></date-picker>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <select type="text" class="form-control" name="quote_service_type_id"
                                    style="padding-left: 10px"
                                    v-model="quote_service_type_id" :disabled="isQuoteBlocked">
                                    <option value="" disabled>{{ trans('quote.label.type_services') }}</option>
                                    <option :value="service_type.id" v-for="service_type in service_types">
                                        @{{ service_type.translations[0].value }} - @{{ service_type.code }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 form-group cotizacion-crear--pasajeros"
                            :style="quote_open.operation == 'ranges' ? 'pointer-events: none;width: 252px' : 'width: 252px' ">
                            <i class="icon icon-user"></i>
                            <button id="dropdownPax" data-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false" class="form-control"
                                :style="quote_open.operation == 'ranges' ? 'background-color: #f3f2f2;' : '' "
                                :disabled="isQuoteBlocked">
                                <span class="text">
                                    <strong class="num">@{{ quantity_persons.adults }}</strong>
                                    {{ trans('quote.label.adults') }}
                                </span>
                                <span class="text">
                                    <strong class="num">@{{ quantity_persons.child }}</strong>
                                    {{ trans('quote.label.child') }}
                                </span>
                                <i class="fas fa-info-circle faa-flash	animated text-danger"
                                    v-if="!validateAgeChild"
                                    style="font-size: 18px;position: absolute;top: 11px;"></i>
                            </button>
                            <div aria-labelledby="dropdownPax" class="dropdown dropdown-menu" style="z-index: 100"
                                x-placement="bottom-start">
                                <div class="container-dropdown">
                                    <div class="form-group">
                                        <label>{{ trans('quote.label.adults') }}</label>
                                        <select class="form-control" v-model="quantity_persons.adults"
                                            @change="generatePassengerInit(true)">
                                            <option :value="index" v-for="(i, index) in 41">@{{ index }}</option>
                                        </select>
                                    </div>
                                    <div class="form-group" v-if="quantity_persons.adults > 0">
                                        <label>{{ trans('quote.label.child') }}</label>
                                        <select class="form-control" v-model="quantity_persons.child"
                                            @change="generatePassengerInit(true)">
                                            <option :value="index" v-for="(i, index) in 11">@{{ index }}</option>
                                        </select>
                                    </div>

                                    <div class="form-group" style="width:100%;" v-if="quantity_persons.child > 0">
                                        <div class="form-inline mt-2 mb-2" v-for="(age, a) in age_child">
                                            <div class="form-group" style="width:auto;">
                                                <label v-bind:class="(age.age > 0) ? '' : 'text-danger'">
                                                    {{ trans('quote.label.child') }} # @{{ (a + 1) }}
                                                </label>
                                            </div>
                                            <div class="form-group mb-2" style="width:auto;">
                                                <select
                                                    v-bind:class="['form-control', (age.age > 0) ? '' : 'is-invalid']"
                                                    v-model="age.age"
                                                    @change="updateAgeChild(age)">
                                                    <option value="0">0</option>
                                                    <option value="1">1</option>
                                                    <option value="2">2</option>
                                                    <option value="3">3</option>
                                                    <option value="4">4</option>
                                                    <option value="5">5</option>
                                                    <option value="6">6</option>
                                                    <option value="7">7</option>
                                                    <option value="8">8</option>
                                                    <option value="9">9</option>
                                                    <option value="10">10</option>
                                                    <option value="11">11</option>
                                                    <option value="12">12</option>
                                                    <option value="13">13</option>
                                                    <option value="14">14</option>
                                                    <option value="15">15</option>
                                                    <option value="16">16</option>
                                                    <option value="17">17</option>
                                                </select>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-row mt-5">
                        <div class="col-md-2 cotizacion-editar" v-if="quote_id!=null">
                            <div style="margin-bottom: 0px; padding: 0px; margin-left: 10px;margin-top: 5px">
                                <div class="btn btn-link" id="dropdown_rango">
                                    <a href="#" id="dropdownRango" data-toggle="dropdown" aria-haspopup="true"
                                        aria-expanded="false" class="link" :disabled="isQuoteBlocked"
                                        @click.prevent="isQuoteBlocked ? $event.preventDefault() : null"
                                        :style="isQuoteBlocked ? 'pointer-events: none; opacity: 0.6;' : ''">
                                        <span class="text">
                                            <i class="fas fa-sort-numeric-down"></i> {{ trans('quote.label.ranges') }}
                                        </span>

                                        <small v-if="quote_open != '' && quantity_persons.adults == 0 && ranges">(@{{
                                                ranges.length }})</small>

                                    </a>
                                    <div class="input-group-prepend" style="float: right;"
                                        v-if="quote_open != '' &&  quantity_persons.adults == 0">
                                        <button title="Clear Range" class="btn btn-lg btn-danger ml-4"
                                            style="z-index: 0;" @click="cancela_ranger()" :disabled="isQuoteBlocked"><i
                                                class="fas fa-times"></i></button>
                                    </div>
                                    <div aria-labelledby="dropdownRango" class="dropdown dropdown-menu"
                                        style="z-index: 100"
                                        x-placement="bottom-start"
                                        v-if="!isQuoteBlocked">
                                        <div class="container-dropdown">
                                            <div class="form-group"
                                                style="min-height: 200px; overflow-y: scroll; width: 100%; max-height: 400px">
                                                <label>{{ trans('quote.label.select_ranges') }}</label>
                                                <table class="table justify-content-center">
                                                    <thead>
                                                        <tr>
                                                            <th scope="col" class="col-ini">#</th>
                                                            <th scope="col"
                                                                class="col">{{ trans('quote.label.from') }}</th>
                                                            <th scope="col"
                                                                class="col">{{ trans('quote.label.to') }}</th>
                                                            <th scope="col"
                                                                class="col-fin">{{ trans('quote.label.actions') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr v-for="(range,index_range) in ranges">
                                                            <th scope="row" class="th">@{{ index_range + 1 }}</th>
                                                            <td nowrap class="td">
                                                                <input type="text" class="form-control start"
                                                                    v-model="range.from"
                                                                    @keyup.enter="updateRange(range)"
                                                                    :disabled="isQuoteBlocked" />
                                                            </td>
                                                            <td nowrap class="td">
                                                                <input type="text" class="form-control end"
                                                                    v-model="range.to"
                                                                    @keyup.enter="updateRange(range)"
                                                                    :disabled="isQuoteBlocked" />
                                                            </td>
                                                            <td nowrap class="td icon-rank text-center">
                                                                <a class="" title="" v-if="!isQuoteBlocked">
                                                                    <i class="icon-plus-square"
                                                                        @click.stop="createRange"></i>
                                                                </a>
                                                                <a class="" title="" v-if="index_range > 0 && !isQuoteBlocked">
                                                                    <i class="far fa-minus-square"
                                                                        @click.stop="deleteRange(index_range)"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>

                                                <button type="button" :disabled="loading || isQuoteBlocked"
                                                    class="btn btn-success btn-update-all-ranges"
                                                    @click="update_all_ranges()">
                                                    <i class="fa fa-save"></i> {{ trans('quote.label.save') }}
                                                </button>

                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 cotizacion-editar"
                            v-if="(quantity_persons.adults > 0 && !has_file && !client_file_incorrect) && quote_id!=null">
                            <div class="btn btn-link cotizacion-editar--lista">
                                <a
                                    href="#"
                                    v-on:click="isQuoteBlocked ? null : modalPassengers(quote_id, passengers.length)"
                                    class="link a-filtros"
                                    :disabled="isQuoteBlocked"
                                    :style="isQuoteBlocked ? 'pointer-events: none; opacity: 0.6;' : ''">
                                    <i class="icon icon-user-check"></i><span
                                        class="text">{{ trans('quote.label.passengers_list') }}</span>
                                    <small>(@{{
                                            passengers.length }})
                                    </small>
                                </a>
                            </div>
                        </div>
                        <div class="col-md-2 cotizacion-editar">
                            <div class="btn btn-link cotizacion-editar--categorias">
                                <a
                                    href="#"
                                    id="dropdownCategoria"
                                    data-toggle="dropdown"
                                    aria-haspopup="true"
                                    aria-expanded="false"
                                    class="link a-filtros"
                                    :disabled="isQuoteBlocked"
                                    @click.prevent="isQuoteBlocked ? $event.preventDefault() : null"
                                    :style="isQuoteBlocked ? 'pointer-events: none; opacity: 0.6;' : ''">
                                    <i class="icon icon-tag"></i><span
                                        class="text">{{ trans('quote.label.categories') }}</span>
                                    <small>(@{{ categories_selected.length }}/@{{ categories.length }})</small>
                                </a>
                                <div aria-labelledby="dropdownCategoria" class="dropdown dropdown-menu"
                                    style="z-index: 100"
                                    x-placement="bottom-start"
                                    v-if="!isQuoteBlocked">
                                    <div class="container-dropdown px-4" style="overflow-y: scroll;">
                                        <div class="form-group row">
                                            <div style="height: 200px">
                                                <label>{{ trans('quote.label.select_categories') }}</label>
                                                <label class="form-check col-6"
                                                    v-for="category in categories">
                                                    <input style="margin-top: 14px; margin-right: 5px;"
                                                        class="form-check-input"
                                                        type="checkbox"
                                                        v-model="category.checked"
                                                        @change="createOrDeleteCategory(category)"
                                                        :disabled="isQuoteBlocked">
                                                    @{{ category.translations[0].value }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 cotizacion-editar">
                            <div class="btn btn-link cotizacion-editar--notas ml-5">
                                <a
                                    href="#"
                                    class="link a-filtros"
                                    data-toggle="modal"
                                    data-target="#modal-notas"
                                    v-show="quote_id!=null"
                                    :disabled="isQuoteBlocked"
                                    :style="isQuoteBlocked ? 'pointer-events: none; opacity: 0.6;' : ''"
                                    @click.prevent="isQuoteBlocked ? null : null">
                                    <i class="icon icon-message-square"></i>
                                    <span class="text">
                                        {{ trans('quote.label.notes') }}
                                    </span>
                                    <small>(@{{ notes.length }})
                                    </small>
                                </a>
                            </div>
                        </div>
                        <div class="col-md-4 cotizacion-editar">
                            <div class="btn btn-link cotizacion-editar--notas ml-5" v-if="quote_id != null">
                                <a href="#" class="link a-filtros"
                                    @click.prevent="isQuoteBlocked ? null : show_occupation_modal()"
                                    :disabled="isQuoteBlocked"
                                    :style="isQuoteBlocked ? 'pointer-events: none; opacity: 0.6;' : ''">
                                    <i class="icon icon-bed-simple"></i>
                                    <span class="text">{{ trans('quote.label.occupation') }}:</span>
                                    <strong style="display: contents; color:initial;"
                                        :class="['span-accomodation', {'text-danger':!( (quantity_persons.adults + quantity_persons.child) === ( parseInt(service_selected_general.single) + (parseInt(service_selected_general.double)*2)+(parseInt(service_selected_general.triple) * 3)) )}]">
                                        SGL:@{{ service_selected_general.single }} - DBL:@{{
                                            service_selected_general.double }} - TPL:@{{
                                            service_selected_general.triple }}
                                    </strong>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div v-if="isQuoteBlocked" class="cotizacion-editar--markup-container">
                            <div class="cotizacion-editar--markup">
                                <div class="btn btn-link--markup">
                                    <a href="#"
                                        class="link"
                                        @click.prevent="showMarkupList = !showMarkupList"
                                        :disabled="!quote_open || !quote_open.categories || quote_open.categories.length === 0"
                                        :style="(!quote_open || !quote_open.categories || quote_open.categories.length === 0) ? 'pointer-events: none; opacity: 0.6;' : ''">
                                        <i class="icon-tag"></i>
                                        <span class="text">{{ trans('quote.label.markup') }} por País:</span>
                                        <small>(@{{ markupByCountry.length }})</small>
                                    </a>
                                </div>
                            </div>
                            <div v-if="showMarkupList && markupByCountry.length > 0" class="markup-list-modal">
                                <div class="markup-list-content">
                                    <div class="markup-list-header">
                                        <span>Markup por País</span>
                                        <button type="button" class="markup-list-close" @click="showMarkupList = false" aria-label="Close">
                                            <span>&times;</span>
                                        </button>
                                    </div>
                                    <ul class="markup-list-items">
                                        <li v-for="item in markupByCountry" :key="item.country" class="markup-list-item">
                                            <span class="markup-list-country">@{{ item.country }}</span>
                                            <span class="markup-list-value">@{{ item.markup }}%</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div v-if="showMarkupList && markupByCountry.length === 0" class="markup-list-empty">
                                No hay información de markup disponible.
                            </div>
                        </div>
                        <div v-else class="col-md-2">
                            <div v-if="quote_id!=null">
                                <div class="form-group">
                                    <label class="markup mr-3">{{ trans('quote.label.markup') }}

                                    </label>
                                    <div class="input-group mb-3">
                                        <input
                                            class="form-control"
                                            type="number"
                                            min="0"
                                            max="100"
                                            step="0.01"
                                            v-model="markup"
                                            :placeholder="markup_readonly + ' %'"
                                            @keyup.enter="updateMarkup(2)" style="padding-left: 10px;"
                                            :disabled="isQuoteBlocked">
                                        <div class="input-group-prepend">
                                            <button
                                                class="btn btn-lg btn-danger mr-1"
                                                @click="updateMarkup(2)"
                                                title="{{ trans('quote.label.save_markup') }}"
                                                style="z-index: 0;"

                                                :disabled="!permissions.updatemarkup || isQuoteBlocked">
                                                <i class="far fa-save"></i>
                                            </button>

                                            <!-- <button class="btn btn-lg btn-danger" @click="updateMarkup(3)"
                                                    :disabled="!permissions.updatemarkup"
                                                    title="{{ trans('quote.label.restore_markup') }}"
                                                    style="z-index: 0;">
                                                <i class="fas fa-sync"></i>
                                                </button> -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group"
                                v-if="quote_id!=null">
                                <label
                                    class="markup mr-4">{{ trans('quote.label.link_the_reservation_with_an_order') }}</label>
                                <input class="form-control" type="number" min="1" max="1000000" step="1"
                                    v-model="new_order_related" v-bind:disabled="readonly"
                                    style="padding-left: 10px;">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="markup mr-4" v-show="quote_id != null">
                                    {{ trans('quote.label.estimated_travel_date') }}
                                </label>
                                <div v-show="quote_id != null">
                                    <i class="icon icon-calendar" style="top: 30px !important;"></i>
                                    <date-picker
                                        class="form-control"
                                        v-model="quote_date_estimated"
                                        :config="optionsR"
                                        :disabled="isQuoteBlocked"></date-picker>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group float-right">
                                <button class="btn btn-crear btn-primary" @click="saveQuote()" :disabled="loading || isQuoteBlocked"
                                    v-if="quote_id == null">
                                    <span v-if="!(loading)">
                                        <strong v-if="quote_open==''">{{ trans('quote.label.create') }}</strong>
                                    </span>
                                    <span v-if="loading"><i class="fa fa-spinner fa-spin"></i></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <b-overlay no-center :show="has_file || editing_quote || client_file_incorrect" :opacity="0.42" rounded="sm" z-index="1"
                    no-wrap>
                    <template #overlay>
                        <i class="fas fa-lock position-absolute text-danger"
                            style="top: 3%; right: 23px;font-size: 20px;"></i>
                    </template>
                </b-overlay>
            </div>

            <div class="col-12">
                <div class="form-row d-flex justify-content-between align-items-center">
                    <div v-if="quantity_persons.adults > 0 && has_file">
                        <a
                            href="#"
                            v-on:click="modalPassengers(quote_id, passengers.length)"
                            class="link a-filtros"
                            style="color: black!important;"
                            :disabled="isQuoteBlocked">
                            <i class="icon icon-user-check"></i>
                            <span class="text text-dark">{{ trans('quote.label.passengers_list') }}</span>
                            <small>(@{{ passengers.length }})</small>
                        </a>
                    </div>
                    <b-overlay no-center :show="editing_quote" :opacity="0.42" rounded="sm" z-index="1" no-wrap>
                        <template #overlay>
                            <i class="fas fa-lock position-absolute text-danger"
                                style="top: 3%; right: 23px;font-size: 20px;"></i>
                        </template>
                    </b-overlay>
                </div>
            </div>
            <div class="col-12">
                <div class="col-12 cotizacion-cotizar line-bottom" v-if="quote_open!=''">
                    @include('quotes.buttons')
                    <b-overlay no-center :show="editing_quote" :opacity="0.42" rounded="sm" z-index="1" no-wrap>
                        <template #overlay>
                            <i class="fas fa-lock position-absolute text-danger"
                                style="top: 3%; right: 23px;font-size: 20px;"></i>
                        </template>
                    </b-overlay>
                </div>
            </div>
            <div class="col-12 d-flex justify-content-center">
                <div class="box-filters mr-3" v-if="quote_open.operation == 'ranges'">
                    <div>
                        <div class="col-12 cotizacion-rangos" title="{{ trans('quote.label.selected_ranges') }}"
                            v-if="quantity_persons.adults == 0" style="margin-bottom: 0px;">
                            <span class="rango back-gray" v-for="(range,index_range) in ranges">
                                @{{ range.from }} - @{{ range.to }} <i class="fas fa-times-circle"
                                    @click="deleteRange(index_range)"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="box-filters ml-3">
                    <div style="display: none"
                        class="col-12 cotizacion-categorias d-flex justify-content-between align-items-center"
                        v-if="quote_open!=''" style="padding-top: 50px;">
                        <div>
                            <div>
                                <button :class="'btn btn-tab categoria ' + qCateg.tabActive" type="button"
                                    @click="toggleTabCategory(qCateg)"
                                    v-for="qCateg in quote_open.categories">
                                    @{{ qCateg.type_class.translations[0].value }}
                                </button>
                            </div>
                        </div>
                        <b-overlay no-center :show="has_file || editing_quote || client_file_incorrect || isQuoteBlocked" :opacity="0.42" rounded="sm"
                            z-index="1" no-wrap>
                            <template #overlay>
                                <i class="fas fa-lock position-absolute text-danger"
                                    style="top: 0px; right: 0px;font-size: 20px;"></i>
                            </template>
                        </b-overlay>
                    </div>
                    <div style="display: none"
                        class="col-12 cotizacion-categorias d-flex justify-content-between align-items-center"
                        v-if="quote_open==''" style="padding-top: 50px;">
                        <div>
                            <div class="col-12 cotizacion-rangos text-center"
                                title="{{ trans('quote.label.selected_categories') }}">
                                <span v-if="category.checked" class="rango rango-cate back-red"
                                    v-for="category in categories">
                                    @{{ category.translations[0].value }}
                                </span>
                            </div>
                        </div>
                        <b-overlay no-center :show="editing_quote || isQuoteBlocked" :opacity="0.42" rounded="sm" z-index="1" no-wrap>
                            <template #overlay>
                                <i class="fas fa-lock position-absolute text-danger"
                                    style="top: 0px; right: 0px;font-size: 20px;"></i>
                            </template>
                        </b-overlay>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div>
                    <div style="top: 25px;">
                        <div>
                            <div class="cotizacion-incluir d-flex justify-content-between"
                                v-if="quote_open != ''">
                                <div class="d-flex align-items-center justify-content-start">
                                    <template v-if="quote_open.categories.length > 1">
                                        <label v-if="!has_file && !client_file_incorrect">{{ trans('quote.label.copy_from_category')}}:</label>
                                        <select name="" id="" v-model="categoryForCopy" class="form-control ml-1"
                                            v-if="!has_file && !client_file_incorrect"
                                            style="width: 170px;">
                                            <option v-for="category in quote_open.categories"
                                                v-if="category.tabActive===''" :value="category.id">
                                                @{{ category.type_class.translations[0].value }} (@{{
                                                    category.services.length }})
                                            </option>
                                        </select>
                                        <button type="button" class="btn btn-success ml-1" v-if="!has_file && !client_file_incorrect"
                                            @click="willCopyCategory()">
                                            {{trans('global.label.do')}}
                                        </button>
                                        <div v-if="has_file || client_file_incorrect">
                                            <b-form-checkbox v-model="hiddenLocked" name="check-button" size="lg"
                                                @change="changeLocked(hiddenLocked)" switch>
                                                <span v-if="hiddenLocked">{{trans('quote.label.view_blocked')}}</span>
                                                <span v-if="!hiddenLocked">{{trans('quote.label.hide_locked')}}</span>
                                            </b-form-checkbox>
                                        </div>
                                    </template>
                                </div>

                                <div class="d-flex align-items-center justify-content-end">
                                    <button class="btn btn-secondary"
                                        @click="isQuoteBlocked ? null : showModalHotel('')"
                                        :disabled="isQuoteBlocked">
                                        + {{ trans('quote.label.hotel') }}
                                    </button>
                                    <button class="btn btn-secondary" data-toggle="modal"
                                        data-target="#modal-servicios"
                                        @click="isQuoteBlocked ? null : showCategories"
                                        :disabled="isQuoteBlocked">
                                        + {{ trans('quote.label.service') }}
                                    </button>
                                    <button class="btn btn-secondary" data-toggle="modal"
                                        data-target="#modal_extensions"
                                        @click="isQuoteBlocked ? null : showModalExtension"
                                        :disabled="isQuoteBlocked">
                                        + {{ trans('quote.label.extension') }}
                                    </button>
                                    <button class="btn btn-secondary"
                                        @click="isQuoteBlocked ? null : showModalFlight('')"
                                        data-toggle="modal"
                                        data-target="#modal-flight"
                                        :disabled="isQuoteBlocked">
                                        + {{ trans('quote.label.flight') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <b-overlay no-center :show="editing_quote || isQuoteBlocked" :opacity="0.42" rounded="sm" z-index="1" no-wrap>
                        <template #overlay>
                            <i class="fas fa-lock position-absolute text-danger"
                                style="top: 0px; right: 0px;font-size: 20px;"></i>
                        </template>
                    </b-overlay>
                </div>
            </div>
            <div style="display: none"
                class="col-10 cotizacion-categorias d-flex justify-content-between align-items-center"
                v-if="quote_open!=''" style="padding-top: 50px;">
                <div class="box-icons d-flex align-items-center justify-content-between">
                    <div class="d-flex">
                        <div class="mx-3">
                            <a data-toggle="modal" class="acciones__item">
                                <span>
                                    <i class="icon icon-plus-circle"></i>
                                    <span class="font-weight-normal"
                                        style="color: #000000">{{ trans('global.icon.add_and_replace') }}</span>
                                </span>
                            </a>
                        </div>
                    </div>
                    <div class="d-flex">
                        <div class="mx-3">
                            <a data-toggle="modal" class="acciones__item">
                                <span>
                                    <i class="icon icon-edit"></i> <span class="font-weight-normal"
                                        style="color: #000000">{{ trans('global.icon.edit') }}</span>
                                </span>
                            </a>
                        </div>
                    </div>
                    <div class="d-flex">
                        <div class="mx-3">
                            <a data-toggle="modal" class="acciones__item">
                                <span>
                                    <i class="icon icon-user-check"></i> <span class="font-weight-normal"
                                        style="color: #000000">{{ trans('global.icon.assign_list') }}</span>
                                </span>
                            </a>
                        </div>
                    </div>
                    <div class="d-flex">
                        <div class="mx-3">
                            <a data-toggle="modal" class="acciones__item">
                                <span>
                                    <i class="icon icon-trash"></i> <span class="font-weight-normal"
                                        style="color: #000000">{{ trans('global.icon.delete') }}</span>
                                </span>
                            </a>
                        </div>
                    </div>
                    <div class="d-flex">
                        <div class="mx-3">
                            <a data-toggle="modal" class="acciones__item">
                                <span>
                                    <i class="icon icon-book"></i> <span class="font-weight-normal"
                                        style="color: #000000">{{ trans('global.icon.optional') }}</span>
                                </span>
                            </a>
                        </div>
                    </div>
                    <div class="d-flex">
                        <div class="mx-3">
                            <a data-toggle="modal" class="acciones__item">
                                <span>
                                    <i class="icon icon-bed-simple icon_green"></i> <span class="font-weight-normal"
                                        style="color: #000000">{{ trans('global.icon.accommodation') }}</span>
                                </span>
                            </a>
                        </div>
                    </div>
                    <div class="d-flex">
                        <div class="mx-3">
                            <a data-toggle="modal" class="acciones__item">
                                <span>
                                    <i class="far fa-trash-alt"
                                        title="{{trans('global.icon.delete_all')}}"></i> <span
                                        class="font-weight-normal"
                                        style="color: #000000">{{ trans('global.icon.delete_all') }}</span>
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div style="display: none"
                class="col-2 cotizacion-categorias d-flex justify-content-between align-items-center"
                v-if="quote_open!=''" style="padding-top: 50px;">
                <div class="box-close d-flex align-items-center justify-content-between">
                    <div class="d-flex">
                        <div class="mx-3">
                            <a class="acciones__item"
                                @click.prevent="isQuoteBlocked ? null : openModalClose()"
                                :style="isQuoteBlocked ? 'pointer-events: none; opacity: 0.6; cursor: not-allowed;' : ''">
                                <span><i class="far fa-window-close"></i> {{ trans('quote.label.close') }}</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Lista de Resultados -->
            <div class="col-12 cotizacion-listado p-0 m-0" v-if="quote_open!=''"
                v-for="(qCateg,indexCateg) in quote_open.categories"
                v-show="qCateg.tabActive">
                <div class="leyenda">
                    <span class="leyenda-fecha">{{ trans('quote.label.date') }} @{{ qCateg.id }}</span>
                    <span class="leyenda-descripcion">{{ trans('quote.label.description') }}</span>
                    <span class="leyenda-tipo">{{ trans('quote.label.type') }}</span>
                    <span v-if="quote_open.operation != 'ranges'"
                        class="leyenda-detalle">{{ trans('quote.label.detail') }}</span>
                    <span v-if="quote_open.operation != 'ranges'"
                        class="leyenda-precio">{{ trans('quote.label.price_per_person') }}</span>
                    <span class="leyenda-acomodacion">{{ trans('quote.label.occupation') }}</span>
                </div>
                <div class="row cotizaciones-listado mx-0">
                    <div class="">
                        <div class="tbl--cotizacion">
                            <div class="tbl--cotizacion__header">
                                <div class="row no-gutters align-items-center">
                                    <div class="icon-eliminar cursor-pointer"
                                        :style="isQuoteBlocked ? 'pointer-events: none; opacity: 0.6; cursor: not-allowed;' : ''">
                                        <i class="far fa-trash-alt" title="{{trans('global.icon.delete_all')}}"
                                            @click="showModalDeleteServices"></i>
                                    </div>
                                    <div class="col px-3">
                                        <div
                                            :class="{ 'tbl--cotizacion__fecha':true, 'ancho270' : quote_open.operation == 'ranges' }"
                                            style="width: 150px;">
                                            <h4 class="tbl--cotizacion__title">
                                                {{ trans('quote.label.date_from') }}
                                            </h4>
                                            <span class="ml-2">
                                                {{ trans('quote.label.date_to') }}
                                                <a class="mx-2" @click="show_dateIn = !show_dateIn">
                                                    <i class="icon-eye" v-if="show_dateIn"></i>
                                                    <i class="icon-eye-off" v-else></i>
                                                </a>
                                            </span>

                                        </div>
                                    </div>
                                    <div class="col px-3">
                                        <div
                                            :class="{ 'tbl--cotizacion__noche':true, 'ancho0' : quote_open.operation == 'ranges' }">
                                            <h4 class="tbl--cotizacion__title">{{ trans('quote.label.night') }}</h4>
                                        </div>
                                    </div>
                                    <div class="col px-2">
                                        <div
                                            :class="{ 'prod-estado':true, 'ancho120' : quote_open.operation == 'ranges' }">
                                            <span class="d-flex align-items-center mx-3"><span
                                                    class="estado estado-rq mr-1"></span> RQ </span>
                                            <span class="d-flex align-items-center mx-3"><span
                                                    class="estado estado-ok mr-1"></span> OK </span>
                                        </div>
                                    </div>
                                    <div class="col px-3">
                                        <div
                                            :class="{ 'tbl--cotizacion__descripcion':true, 'ancho400' : quote_open.operation == 'ranges' }">
                                            <h4 class="tbl--cotizacion__title">{{ trans('quote.label.description') }}</h4>
                                        </div>
                                    </div>
                                    <div class="col px-3" v-if="quote_open.operation != 'ranges'"
                                        style="    width: 340px;">
                                        <div class="tbl--cotizacion__detalle">
                                            <h4 class="tbl--cotizacion__title">{{ trans('quote.label.detail') }}</h4>
                                        </div>
                                    </div>
                                    <div class="col px-3" v-if="quote_open.operation != 'ranges'">
                                        <div class="tbl--cotizacion__editar">
                                            <h4 class="tbl--cotizacion__title"></h4>
                                        </div>
                                    </div>
                                    <div class="col px-3" v-if="quote_open.operation != 'ranges'">
                                        <div class="tbl--cotizacion__precio">
                                            <h4 class="tbl--cotizacion__title">{{ trans('quote.label.price_per_person') }}</h4>
                                        </div>
                                    </div>
                                    <div class="col-sm-auto px-3">
                                        <div
                                            :class="{ 'tbl--cotizacion__acomodacion':true, 'ancho75' : quote_open.operation == 'ranges' }">
                                            <h4 class="tbl--cotizacion__title">{{ trans('quote.label.occupation') }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 draggable" v-if="qCateg.services.length>0">
                        <draggable v-model="qCateg.services" tag="ul" handle=".handle" @start="drag=true"
                            @end="drag=false" @update="checkMoveService(qCateg.services)"
                            :disabled="isQuoteBlocked">
                            <transition-group>
                                <li :class="{ 'producto': true, 'producto-agrupado': service.extension_id !=null,
                                            'back_warning_icon' : service.type=='hotel' && verify_type_rooms(service),
                                             'back_warning_amount' : service.type=='hotel' && ( service.amount && service.amount.error_in_nights ),
                                              'service_validation_errors' : service.validations.length > 0,
                                              'grouped_class': service.type==='group_header',
                                              'grouped_class_row': service.grouped_type==='row'}"
                                    v-for="(service,index_service) in qCateg.services"
                                    :key="service.id"
                                    v-if="service.parent_service_id ==null"
                                    :style="validateService(service)"
                                    v-show="( (!hiddenLocked && service.locked) || (!service.locked) && service.grouped_show==true || service.type=='group_header' )  && (service.total_accommodations > 0)">

                                    <div v-if="service.alert != undefined && service.alert != ''"
                                        style="position: absolute;display: flex;top: 0px;left: 0px;font-size: 13px;">
                                        <small class="alert alert-danger m-0" style="padding: 4px !important;">@{{
                                                service.alert }}</small>
                                    </div>
                                    <div class="row m-0">
                                        <div class="d-flex align-items-center"
                                            :class="{ 'prod-acciones':true, 'porc10' : quote_open.operation == 'ranges' }">

                                            <i class="icon icon-drag handle"
                                                v-if="service.type !='group_header'"
                                                :style="isQuoteBlocked ? 'pointer-events: none; opacity: 0.6; cursor: not-allowed;' : ''"></i>
                                            <input style="" class="form-check-input" type="checkbox"
                                                v-model="service.selected"
                                                @change="addServiceDelete(service)"
                                                :disabled="isQuoteBlocked">
                                            <a data-toggle="modal" href="#modal_extensions"
                                                v-if=" service.extension_id!=null"
                                                :style="isQuoteBlocked ? 'pointer-events: none; opacity: 0.6; cursor: not-allowed;' : ''">
                                                <i class="icon icon-plus-square"
                                                    @click="isQuoteBlocked ? null : selectReplaceExtension(service)"></i>
                                            </a>
                                            <a data-toggle="modal" href="#modal-servicios"
                                                :class="{'iconServiceInExtension':service.extension_id!=null}"
                                                v-if="service.type == 'service'"
                                                @click="isQuoteBlocked ? null : openModalService(qCateg, service)"
                                                :style="isQuoteBlocked ? 'pointer-events: none; opacity: 0.6; cursor: not-allowed;' : ''">
                                                <i class="icon icon-plus-circle"
                                                    title="{{trans('global.icon.add_and_replace')}}"></i>
                                            </a>

                                            <a @click="isQuoteBlocked ? null : showModalHotel(service)"
                                                :style="'z-index: 1;' + (service.type==='group_header' ? 'margin-left: 17px;' : '') + (isQuoteBlocked ? 'pointer-events: none; opacity: 0.6; cursor: not-allowed;' : '')"
                                                v-if="service.type == 'group_header' && service.extension_id==null">
                                                <i class="icon icon-plus-circle"
                                                    title="{{trans('global.icon.add_and_replace')}}"></i>
                                            </a>
                                            <a href="javascript:;" :style="'z-index: 1;' + (isQuoteBlocked ? 'pointer-events: none; opacity: 0.6; cursor: not-allowed;' : '')"
                                                @click="isQuoteBlocked ? null : willRemoveService(qCateg, service)">
                                                <i class="icon icon-trash"
                                                    title="{{trans('global.icon.delete')}}"></i>
                                            </a>
                                            <span v-if="service.type == 'service' || service.type == 'group_header'"
                                                :style="'cursor: pointer;z-index: 1;' + (isQuoteBlocked ? 'pointer-events: none; opacity: 0.6; cursor: not-allowed;' : '')"
                                                @click="isQuoteBlocked ? null : updateOptional(service,indexCateg)">
                                                <i class="icon icon-book" style="color: #890005"
                                                    title="{{trans('global.icon.optional')}}"> </i>
                                            </span>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-center"
                                            :class="{ 'prod-fecha':true, 'porc20' : quote_open.operation == 'ranges' }"
                                            style="width: 130px;">

                                            <a data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                                v-if="service.type=='service'"
                                                class="acciones__item schedules-icon"
                                                :style="isQuoteBlocked ? 'pointer-events: none; opacity: 0.6; cursor: not-allowed;' : ''">
                                                <span
                                                    class="producto-acomodacion--cambiar btn btn-icon btn-time-sucess"
                                                    title="Horarios"
                                                    @click="isQuoteBlocked ? null : selectServiceSelected(service,index_service)"
                                                    v-if="service.service.schedules.length>0">

                                                    <span v-for="(schedules_, sch_i) in service.service.schedules"
                                                        v-if="service.schedule_id !== null && service.schedule_id !== ''">
                                                        <span v-for="schedule_ in schedules_"
                                                            v-if='schedule_.id_parent == service.schedule_id && schedule_.day_choosed'
                                                            style="">
                                                            <i class="icon fa fa-calendar-times text-danger "
                                                                v-if="schedule_.ini==null && service.hour_in==null"></i>
                                                            <i class="icon fa fa-calendar-check" v-else></i>
                                                            N° @{{sch_i+1}}
                                                        </span>
                                                    </span>
                                                </span>

                                                <span class="producto-acomodacion--cambiar btn btn-icon btn-time"
                                                    v-else>
                                                    <span>
                                                        <i class="icon fa fa-calendar-times text-danger"
                                                            v-if="service.hour_in==null"></i>
                                                        <i class="icon fa fa-calendar-check" v-else></i>
                                                    </span>
                                                </span>
                                            </a>
                                            <div @click.stop="" v-if="service.type=='service' && !isQuoteBlocked"
                                                class="dropdown-menu dropdown-menu__cotizacion dropdown-menu-right"
                                                style="overflow-y: scroll; z-index: 100; max-height: 300px!important; min-width: 180px!important; left: 50px!important;">
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
                                                                            @{{indexParent + 1}}
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
                                                        <span v-else class="alert alert-warning-quote"> <i
                                                                class="fa fa-info-circle"></i> Ningún horario para mostrar</span>
                                                    </div>

                                                </div>
                                            </div>

                                            <div style="padding-top: 3rem;">

                                                <date-picker class="date" v-model="service.date_in"
                                                    :config="optionsR" @dp-show="showDatePickerService(service)"
                                                    @dp-change="updateDateInService(service,index_service)"
                                                    :disabled="isQuoteBlocked"
                                                    v-if="service.type==='group_header'  || service.type=='service' || service.type=='flight' ">
                                                </date-picker>
                                                <span
                                                    v-if="service.type!=='group_header' && service.type=='hotel'   "
                                                    class="form-control date" style="padding: 6px">@{{ service.date_in }}</span>

                                                <div style="display: block;">

                                                    <input type="text" class="date-disabled"
                                                        v-model="service.date_out"
                                                        v-if="service.type =='hotel'"
                                                        v-show="show_dateIn"
                                                        disabled>
                                                </div>
                                            </div>


                                            {{-- HORA DE INICIO --}}
                                            <div v-if="service.type=='service'"
                                                class="acciones__item schedules-hours" style="font-size: 10px;">
                                                <span v-if="service.service.service_type_id == 2"
                                                    class="schedule-hour">
                                                    <input type="time" v-model="service.hour_in"
                                                        @input="change_hour_in(service)"
                                                        :disabled="isQuoteBlocked">
                                                </span>
                                                <span v-else class="">
                                                    <select v-if="service.service.schedules.length > 0"
                                                        class="form-control"
                                                        @change="onScheduleChange($event, service)"
                                                        :disabled="isQuoteBlocked">
                                                        <option v-for="(horary, index) in getSortedSchedules(service)"
                                                            :key="index"
                                                            :selected="horary[getDayIndex(service.date_in)].day_choosed"
                                                            :value="horary[getDayIndex(service.date_in)].ini">
                                                            @{{ horary[getDayIndex(service.date_in)].ini | format_hour }}
                                                        </option>
                                                    </select>
                                                </span>
                                            </div>

                                        </div>
                                        <div class="d-flex align-items-center justify-content-center prod-fecha"
                                            :class="{ 'prod-max':true, 'porc8' : quote_open.operation == 'ranges' }"
                                            style="width: 60px;">
                                            <select class="select-pax " v-model="service.nights"
                                                v-if="service.type==='group_header'" style="margin-top: -6px;"
                                                @change="updateNightsService(service)"
                                                :disabled="isQuoteBlocked"
                                                v-if="service.type =='hotel' || service.type=='group_header'">
                                                <option value="1">1</option>
                                                <option value="2">2</option>
                                                <option value="3">3</option>
                                                <option value="4">4</option>
                                                <option value="5">5</option>
                                                <option value="6">6</option>
                                                <option value="7">7</option>
                                                <option value="8">8</option>
                                                <option value="9">9</option>
                                                <option value="10">10</option>
                                            </select>

                                            <span v-if="service.type =='hotel' && service.type!=='group_header'"
                                                class="form-control date"
                                                style="padding: 6px; height: 28px; margin-top: -4px;">@{{ service.nights }}</span>

                                            <span
                                                v-if="service.type !='hotel' &&  service.type!='group_header'">-</span>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-center"
                                            :class="{ 'prod-estado':true, 'porc10' : quote_open.operation == 'ranges' }"
                                            style="width: 40px;">
                                            <span class="estado estado-rq"
                                                v-if="service.type == 'hotel' && service.on_request == 1"></span>
                                            <span class="estado estado-ok"
                                                v-if="service.type == 'hotel' && service.on_request == 0"></span>
                                            <span class="estado estado-ok"
                                                v-if="service.type == 'service' && service.service_rate!=null && service.on_request == 0"></span>
                                            <span class="estado estado-rq"
                                                v-if="service.type == 'service' && service.service_rate!=null && service.on_request == 1"></span>
                                            <i class="estado fa fa-times"
                                                v-if="service.type == 'service' && service.service_rate==null"></i>
                                        </div>
                                        <div class="prod-descripcion mx-4"
                                            :class="{ 'prod-descripcion':true, 'porc35' : quote_open.operation == 'ranges' }">

                                            <div v-if="service.new_extension && service.type != 'hotel' " class="text-left">
                                                <p class="mb-2"><i>Extensión</i></p>
                                                <span class="texto">
                                                    <b>[@{{ service.new_extension.code }}]</b> @{{ service.new_extension.translations[0].name }}
                                                </span>
                                            </div>

                                            <div class="d-flex">
                                                <span class="id mr-3" :title="service.code_flight"
                                                    v-if="service.type =='flight'">[{{ trans('quote.label.flight') }} -
                                                    <strong
                                                        v-if="service.code_flight == 'AEC' || service.code_flight == 'AECFLT'">
                                                        {{ trans('flights.label.national') }}
                                                    </strong>
                                                    <strong
                                                        v-if="service.code_flight == 'AEI' || service.code_flight == 'AEIFLT'">
                                                        {{ trans('flights.label.international') }}
                                                    </strong>]
                                                </span>
                                                <span class="id mr-3" v-if="service.type =='service'">
                                                    <div style="display: column; align-items: center;">
                                                        <span style="font-weight: bold; color: #2F353A; font-size: 14px; margin-right: 8px;" v-if="isMultiRegionQuote">LATAM</span>
                                                        <span>
                                                            [@{{ service.service.aurora_code }}]
                                                        </span>
                                                    </div>
                                                </span>
                                                <span class="id mr-3" v-if="(service.type =='hotel' || service.type =='group_header' ) && service.hotel.channel.length>0">
                                                    <div style="display: column; align-items: center;">
                                                        <span style="font-weight: bold; color: #2F353A; font-size: 14px; margin-right: 8px;" v-if="service.type=='group_header' && isMultiRegionQuote">LATAM</span>
                                                        <div>
                                                            <span v-if="service.hotel.channel.some(c => c.channel_id == 1)">
                                                                [@{{ service.hotel.channel.find(c => c.channel_id == 1).code }}]
                                                            </span>
                                                            <span v-else>
                                                                [No ingresado]
                                                            </span>
                                                        </div>
                                                    </div>
                                                </span>

                                                <span class="texto" v-if="service.type == 'flight'">
                                                    <span v-if="service.origin != '' && service.origin != null">
                                                        {{ trans('quote.label.origin') }}: @{{ service.origin }}
                                                    </span>
                                                    <span
                                                        v-if="service.origin != '' && service.origin != null && service.destiny != '' && service.destiny != null"> / </span>
                                                    <span v-if="service.destiny != '' && service.destiny != null">
                                                        {{ trans('quote.label.destiny') }}: @{{ service.destiny }}
                                                    </span>
                                                </span>
                                                <span class="texto" v-if="service.type =='service'">
                                                    <div class="latam-badges-row" style="justify-content: flex-start; margin-bottom: 8px;" v-if="isMultiRegionQuote">
                                                        <span class="latam-country-badge-card">@{{ service.service.service_origin[0].country.translations[0].value }}</span>
                                                        <span class="latam-mkp-badge">MKP @{{ service.markup_regionalization }}%</span>
                                                    </div>
                                                    @{{ service.service.service_translations[0].name }}
                                                    <b v-if="service.service.service_type.code==='SIM' || service.service.service_type.code==='PC'">
                                                        - [@{{ service.service.service_type.translations[0].value }}].
                                                    </b>
                                                    <p v-if="service.type==='service' && (service.service.deleted_at == '' || service.service.deleted_at == null)">
                                                        <a href="javascript:void(0);"
                                                            @click="isQuoteBlocked ? null : openModalDetail(service.service.id,'inclusions',service.date_out, service.adult, service.child)"
                                                            :style="isQuoteBlocked ? 'pointer-events: none; opacity: 0.6; cursor: not-allowed;' : ''">
                                                            {{trans('service.label.includes_not_include')}}
                                                        </a>
                                                        <a href="javascript:void(0);"
                                                            @click="isQuoteBlocked ? null : openModalDetail(service.service.id,'itinerary',service.date_out, service.adult, service.child)"
                                                            :style="isQuoteBlocked ? 'pointer-events: none; opacity: 0.6; cursor: not-allowed;' : ''">
                                                            {{trans('service.label.itinerary')}}
                                                        </a> <br>
                                                        <a href="javascript:void(0);"
                                                            @click="isQuoteBlocked ? null : openModalDetail(service.service.id,'schedule',service.date_out, service.adult, service.child)"
                                                            :style="isQuoteBlocked ? 'pointer-events: none; opacity: 0.6; cursor: not-allowed;' : ''">
                                                            {{trans('service.label.schedules_restrictions')}}
                                                        </a><br>
                                                        <a data-toggle="modal" href="#modal-real-notes"
                                                            v-if="service.service.service_translations[0].summary != null && service.service.service_translations[0].summary != ''"
                                                            @click="isQuoteBlocked ? null : (service_real_notes=service.service.service_translations[0].summary)"
                                                            :style="isQuoteBlocked ? 'pointer-events: none; opacity: 0.6; cursor: not-allowed;' : ''">
                                                            {{trans('service.label.summary')}}
                                                        </a><br>
                                                        <a data-toggle="modal" href="#modal-notes"
                                                            v-if="service.service.notes != null && service.service.notes != ''   && user_type_id == 3 "
                                                            @click="isQuoteBlocked ? null : (service_notes=service.service.notes)"
                                                            :style="isQuoteBlocked ? 'pointer-events: none; opacity: 0.6; cursor: not-allowed;' : ''">
                                                            Remarks
                                                        </a>
                                                    </p>
                                                </span>

                                                <span class="texto"
                                                    v-if="service.type =='hotel' || service.type =='group_header'">
                                                    <div class="latam-badges-row" style="justify-content: flex-start; margin-bottom: 8px;" v-if="service.type=='group_header' && isMultiRegionQuote">
                                                        <span class="latam-country-badge-card">@{{ service.hotel.country.translations[0].value }}</span>
                                                        <span class="latam-mkp-badge">MKP @{{ service.markup_regionalization }}%</span>
                                                    </div>
                                                    @{{ service.hotel.name }}

                                                    <span v-for="(service_room, srKey) in service.service_rooms"
                                                        v-if="srKey < 3 && service.service_rooms.length >= 1">
                                                        <br>
                                                        <span class="strong-message-sub"
                                                            v-if="service_room.error_rates && service_room.error_rates!==''">
                                                            @{{ service_room.error_rates }}
                                                        </span>

                                                        <span v-if="service.type =='hotel'">
                                                            <span
                                                                v-if="service_room.rate_plan_room != null && service_room.rate_plan_room.room.translations[0]">

                                                                <i class="fa fa-bed icon_green"></i>
                                                                <span v-if="service_room.rate_plan_room.room.translations.find(t => t.slug === 'room_name')">
                                                                    @{{ service_room.rate_plan_room.room.translations.find(t => t.slug === 'room_name').value }}
                                                                </span>

                                                                <a style="margin-left: 6px; margin-top: 3px; font-size: 9.5px;cursor:pointer;"
                                                                    class="a-plan-room"
                                                                    @click="isQuoteBlocked ? null : editPlanRooms(service)"
                                                                    :style="isQuoteBlocked ? 'pointer-events: none; opacity: 0.6; cursor: not-allowed;' : ''">
                                                                    {{ trans('quote.label.change') }}
                                                                </a>
                                                                <br />
                                                                <span v-if="service_room.rate_plan_room.rate_plan">@{{ service_room.rate_plan_room.rate_plan.name }} (@{{ service_room.rate_plan_room.rate_plan.meal.translations[0].value  }})</span>
                                                                <button title="Eliminar" type="button"
                                                                    class="btn btn-sm btn-danger ml-2 mb-2"
                                                                    @click="isQuoteBlocked ? null : deleteRoomHotel(service_room.id)"
                                                                    style="border-radius: 10px;"
                                                                    :disabled="isQuoteBlocked"
                                                                    v-if="service.service_rooms.length > 1">
                                                                    <i class="fas fa-trash-alt"></i>
                                                                </button>
                                                            </span>

                                                        </span>

                                                        <span v-if="service.type == 'group_header'">
                                                            <span
                                                                v-if="service_room.rate_plan_room != null && service_room.rate_plan_room.room.translations[0]">
                                                                <span
                                                                    style="margin-left: 6px; margin-top: 3px; font-size: 9.5px;"
                                                                    class="a-plan-room">
                                                                    <i class="fa fa-ban icon_default"
                                                                        v-if="(service_room.rate_plan_room.room.room_type.occupation == 1 && control_service_selected_general.single == 0) || (service_room.rate_plan_room.room.room_type.occupation == 2 && control_service_selected_general.double == 0) || (service_room.rate_plan_room.room.room_type.occupation == 3 && control_service_selected_general.triple == 0)"></i>
                                                                    <i class="fa fa-bed icon_green" v-else></i>
                                                                    <span v-if="service_room.rate_plan_room.room.translations.find(t => t.slug === 'room_name')">
                                                                        @{{ service_room.rate_plan_room.room.translations.find(t => t.slug === 'room_name').value }}
                                                                    </span>
                                                                </span>
                                                            </span>
                                                        </span>

                                                    </span>

                                                    <span v-for="(service_rooms_hyperguest, srKey) in service.service_rooms_hyperguest"
                                                        v-if="srKey < 3 && service.service_rooms_hyperguest.length >= 1">
                                                        <br>
                                                        <span class="strong-message-sub"
                                                            v-if="service_rooms_hyperguest.error_rates && service_rooms_hyperguest.error_rates!==''">
                                                            @{{ service_room.error_rates }}
                                                        </span>

                                                        <span v-if="service.type =='hotel'">
                                                            <span
                                                                v-if="service_rooms_hyperguest.rate_plan_id != null && service_rooms_hyperguest.room.translations[0]">

                                                                <i class="fa fa-bed icon_green"></i>
                                                                <span v-if="service_rooms_hyperguest.room.translations.find(t => t.slug === 'room_name')">
                                                                    @{{ service_rooms_hyperguest.room.translations.find(t => t.slug === 'room_name').value }}
                                                                </span>

                                                                <a style="margin-left: 6px; margin-top: 3px; font-size: 9.5px;cursor:pointer;"
                                                                    class="a-plan-room"
                                                                    @click="isQuoteBlocked ? null : editPlanRooms(service)"
                                                                    :style="isQuoteBlocked ? 'pointer-events: none; opacity: 0.6; cursor: not-allowed;' : ''">
                                                                    {{ trans('quote.label.change') }}
                                                                </a>
                                                                <br />
                                                                <span v-if="service_rooms_hyperguest.rate_plan">@{{ service_rooms_hyperguest.rate_plan.name }} / HYPERGUEST (@{{ service_rooms_hyperguest.rate_plan.meal.translations[0].value  }})</span>
                                                                <button title="Eliminar" type="button"
                                                                    class="btn btn-sm btn-danger ml-2 mb-2"
                                                                    @click="isQuoteBlocked ? null : deleteRoomHotel(service_room.id)"
                                                                    style="border-radius: 10px;"
                                                                    :disabled="isQuoteBlocked"
                                                                    v-if="service.service_rooms.length > 1">
                                                                    <i class="fas fa-trash-alt"></i>
                                                                </button>
                                                            </span>

                                                        </span>

                                                        <span v-if="service.type == 'group_header'">
                                                            <span
                                                                v-if="service_rooms_hyperguest.rate_plan != null && service_rooms_hyperguest.room.translations[0]">
                                                                <span
                                                                    style="margin-left: 6px; margin-top: 3px; font-size: 9.5px;"
                                                                    class="a-plan-room">
                                                                    <i class="fa fa-ban icon_default"
                                                                        v-if="(service_rooms_hyperguest.room.room_type.occupation == 1 && control_service_selected_general.single == 0) || (service_rooms_hyperguest.room.room_type.occupation == 2 && control_service_selected_general.double == 0) || (service_rooms_hyperguest.room.room_type.occupation == 3 && control_service_selected_general.triple == 0)"></i>
                                                                    <i class="fa fa-bed icon_green" v-else></i>
                                                                    <span v-if="service_rooms_hyperguest.room.translations.find(t => t.slug === 'room_name')">
                                                                        @{{ service_rooms_hyperguest.room.translations.find(t => t.slug === 'room_name').value }}
                                                                    </span>
                                                                </span>
                                                            </span>
                                                        </span>

                                                    </span>


                                                    <div v-if="service.type =='group_header'">
                                                        <button type="button"
                                                            style="padding: 0.25rem 0.5rem !important;font-size: 0.875rem !important;"
                                                            class="btn btn-sm btn-success ml-2 mb-2"
                                                            @click="isQuoteBlocked ? null : editPlanRooms(service)"
                                                            :disabled="isQuoteBlocked">
                                                            <i class="fas fa-plus-circle"></i> {{ trans('global.label.add') }}
                                                        </button>
                                                    </div>
                                                    <template v-if="!service.hyperguest_pull">
                                                        <span
                                                            v-if="service.service_rooms && service.service_rooms.length > 3"> ({{ trans('global.label.and') }} @{{ service.service_rooms.length - 3 }} {{ trans('global.label.more') }}) </span>
                                                    </template>
                                                    <template v-else>
                                                        <span
                                                            v-if="service.service_rooms_hyperguest && service.service_rooms_hyperguest.length > 3"> ({{ trans('global.label.and') }} @{{ service.service_rooms_hyperguest.length - 3 }} {{ trans('global.label.more') }}) </span>
                                                    </template>
                                                    <br /><br />
                                                    <a v-if="service.type == 'hotel'" href="javascript:;"
                                                        @click="isQuoteBlocked ? null : openModalNotesHotel(service)"
                                                        :style="isQuoteBlocked ? 'pointer-events: none; opacity: 0.6; cursor: not-allowed;' : ''">
                                                        {{ trans('quote.label.notes_hotel') }}
                                                    </a>
                                                </span>

                                                <template v-if="!service.hyperguest_pull">
                                                    <a style="margin-left: 15px; margin-top: 3px;cursor:pointer;"
                                                        v-if="service.type == 'hotel' && service.service_rooms.length == 0"
                                                        @click="isQuoteBlocked ? null : editPlanRooms(service)"
                                                        :style="isQuoteBlocked ? 'pointer-events: none; opacity: 0.6; cursor: not-allowed;' : ''">
                                                        <i class="fa fa-bed"></i>
                                                    </a>
                                                    <a style="margin-left: 15px; margin-top: 3px;cursor:pointer;"
                                                        v-if="service.type == 'group_header' && service.service_rooms.length == 0"
                                                        @click="isQuoteBlocked ? null : editPlanRooms(service)"
                                                        :style="isQuoteBlocked ? 'pointer-events: none; opacity: 0.6; cursor: not-allowed;' : ''">
                                                        <i class="fa fa-bed"></i>
                                                    </a>
                                                </template>
                                                <template v-else>
                                                    <a style="margin-left: 15px; margin-top: 3px;cursor:pointer;"
                                                        v-if="service.type == 'hotel' && service.service_rooms_hyperguest.length == 0"
                                                        @click="isQuoteBlocked ? null : editPlanRooms(service)"
                                                        :style="isQuoteBlocked ? 'pointer-events: none; opacity: 0.6; cursor: not-allowed;' : ''">
                                                        <i class="fa fa-bed"></i>
                                                    </a>
                                                    <a style="margin-left: 15px; margin-top: 3px;cursor:pointer;"
                                                        v-if="service.type == 'group_header' && service.service_rooms_hyperguest.length == 0"
                                                        @click="isQuoteBlocked ? null : editPlanRooms(service)"
                                                        :style="isQuoteBlocked ? 'pointer-events: none; opacity: 0.6; cursor: not-allowed;' : ''">
                                                        <i class="fa fa-bed"></i>
                                                    </a>
                                                </template>

                                            </div>
                                        </div>

                                        <div class="prod-detalle d-flex align-items-center justify-content-center"
                                            v-if="quote_open.operation =='passengers' && service.type !='group_header'">
                                            <div v-show="!service.showQuantityPassengers"
                                                class="justify-content-around" style="display:flex;">

                                                <div v-if="isEditingPrice" class="align-items-center d-inline-flex">
                                                    <span>@{{ service.adult }} {{ trans('quote.label.adults') }}</span>
                                                    <span>@{{ service.child }} {{ trans('quote.label.child') }}</span>
                                                </div>

                                            </div>
                                            <div class="form-group" v-show="service.showQuantityPassengers"
                                                style="margin: 0px !important;">
                                                <label>{{ trans('quote.label.adults') }}</label>
                                                <select class="form-control" v-model="service.adult"
                                                    style="width: 55px !important;"
                                                    @change="updatePassengerService(service)"
                                                    :disabled="isQuoteBlocked">
                                                    <option value="0">
                                                        0
                                                    </option>
                                                    <option :value="adult" v-for="adult in quantity_persons.adults">
                                                        @{{adult}}
                                                    </option>

                                                </select>
                                            </div>
                                            <div class="form-group"
                                                v-if="service.showQuantityPassengers">
                                                <label>{{ trans('quote.label.child') }}</label>
                                                <select class="form-control" v-model="service.child"
                                                    @change="updatePassengerService(service)"
                                                    style="width: 55px !important;"
                                                    :disabled="isQuoteBlocked">
                                                    <option :value="child - 1"
                                                        v-for="child in (quantity_persons.child + 1)">
                                                        @{{child - 1}}
                                                    </option>
                                                </select>
                                            </div>

                                        </div>

                                        <div class="prod-editar d-flex align-items-center justify-content-center"
                                            v-if="quote_open.operation =='passengers' && service.type !='group_header'">
                                            <span class="btn btn-icon producto-editar--boton"
                                                title="{{ trans('quote.label.edit') }}"
                                                @click="isQuoteBlocked ? null : showSelectQuantityPassengersService(service)"
                                                v-if="!service.showQuantityPassengers"
                                                :style="isQuoteBlocked ? 'pointer-events: none; opacity: 0.6; cursor: not-allowed;' : ''">
                                                <i class="icon icon-edit"></i>
                                            </span>
                                            <span class="btn btn-icon producto-editar--boton"
                                                title="{{ trans('quote.label.save') }}"
                                                @click="isQuoteBlocked ? null : showSelectQuantityPassengersService2(service)"
                                                v-if="service.showQuantityPassengers"
                                                :style="isQuoteBlocked ? 'pointer-events: none; opacity: 0.6; cursor: not-allowed;' : ''">
                                                <i class="icon icon-save"></i>
                                            </span>
                                        </div>

                                        <div
                                            class="prod-precio d-flex align-items-center justify-content-center"
                                            v-if="service.amount.length>0 && quote_open.operation =='passengers' && service.type !='group_header'">
                                            {{-- ---------- --}}

                                            <span class="producto-precio--num" v-if="service.type == 'hotel'">

                                                <template v-if="service.validations.length > 0">
                                                    <span style="color: red"> !! Error !! </span>
                                                </template>
                                                <template v-else> <!-- JCCCCCCC -->
                                                    <!-- Muestra precio o mensaje según condiciones -->
                                                    <span :style="(service.flag_migrate == 0 && !service.price_dynamic) ? 'color: green' : (service.price_dynamic ? 'color: red' : '')">
                                                        <template v-if="!service.price_dynamic || !service.showQuantityPassengers">
                                                            USD @{{ (service.adult + service.child) > 0 ? (service.import_amount?.price_ADL ?? 0) : 0 }}
                                                        </template>
                                                    </span>

                                                    <!-- Controles de precio dinámico -->
                                                    <div v-if="service.price_dynamic == 1">
                                                        <span v-if="(service.import_amount?.price_ADL ?? 0) == 0" style="color: red; display: block;">
                                                            TARIFA DINÁMICA
                                                        </span>
                                                        <template v-if="service.showQuantityPassengers">
                                                            <div class="form-group">
                                                                <label>Price ADL</label>
                                                                <input type="number" step="0.01" v-model="price_dynamic_amount" class="form-control" @focus="isEditingPrice = false"
                                                                    @blur="isEditingPrice = false"
                                                                    :disabled="isQuoteBlocked">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Markup</label>
                                                                <input type="number" step="0.01" v-model="price_dynamic_markup" class="form-control"
                                                                    :disabled="isQuoteBlocked">
                                                            </div>
                                                        </template>
                                                    </div>


                                                    <a href="javascript:void(0)" role="button"
                                                        data-toggle="dropdown" aria-haspopup="false"
                                                        aria-expanded="false" style="border: none;">
                                                        <i class="fa fa-question-circle"
                                                            style="font-size: initial;    color: green;"></i>
                                                    </a>

                                                    <div @click.stop=""
                                                        class="modal-dialog dropdown-menu dropdown-menu__cotizacion dropdown-menu-right"
                                                        style="overflow-y: scroll; z-index: 100;"
                                                        v-if="service.import_amount">
                                                        <div class="dropdown-menu_body modal-body"
                                                            style="padding: 0px 10px 0px 10px !important;font-size: 12px!important;">
                                                            <div class="">

                                                                <div class="titleModalTarifa"
                                                                    style="font-weight: normal;font-size: 14px !important;">
                                                                    @{{ service.hotel.name }}
                                                                    <br />


                                                                    <template v-if="!service.hyperguest_pull">
                                                                        <span>@{{ (service.service_rooms && service.service_rooms.length>0 ) ? service.service_rooms[0].rate_plan_room.room.translations[0].value : '' }}</span>
                                                                        <br />
                                                                        <span>Rate : @{{ (service.service_rooms && service.service_rooms.length>0 ) ? service.service_rooms[0].rate_plan_room.rate_plan.name + ' ('  + service.service_rooms[0].rate_plan_room.rate_plan.meal.translations[0].value + ')' : '' }}</span>
                                                                    </template>

                                                                    <template v-else>
                                                                        <span>@{{ (service.service_rooms_hyperguest && service.service_rooms_hyperguest.length>0 ) ? service.service_rooms_hyperguest[0].room.translations[0].value : '' }}</span>
                                                                        <br />
                                                                        <span>Rate : @{{ (service.service_rooms_hyperguest && service.service_rooms_hyperguest.length>0 ) ? service.service_rooms_hyperguest[0].rate_plan.name + ' / HYPERGUEST  ('  + service.service_rooms_hyperguest[0].rate_plan.meal.translations[0].value + ')' : '' }}</span>
                                                                    </template>

                                                                    <br />



                                                                </div>
                                                                <table width="100%"
                                                                    style="margin-bottom: 10px;font-weight: normal;">
                                                                    <thead>
                                                                        <tr>
                                                                            <th class="text-center">{{ trans('hotel.label.date') }}</th>
                                                                            <th class="text-center">{{ trans('hotel.label.adults') }}(@{{ service.import_amount.adult }})</th>
                                                                            <th class="text-center">{{ trans('hotel.label.child') }}(@{{ service.import_amount.child }})</th>
                                                                            <th class="text-center no-border">{{ trans('hotel.label.subtotal') }}</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <tr v-for="deta in service.import_amount.deta">
                                                                            <td class="text-center">@{{ deta.date }}</td>
                                                                            <td class="text-center"> @{{ deta.adult }}</td>
                                                                            <td class="text-center"> @{{ deta.child }}</td>
                                                                            <td class="text-center no-border">@{{ deta.subTotal }}</td>
                                                                        </tr>
                                                                        {{-- <tr v-if="service.alerta_change_children_ages" >
                                                                                    <td :colspan="(service.adult + service.child ) + 1" style="font-size: 10px!important; color: red;">
                                                                                        (*) Hubo un cambio automático en la tarifa de niños, se cambió por la tarifa del adulto porque la edad del niño seleccionado es mayor al máximo configurado en el servicio
                                                                                    </td>
                                                                                </tr> --}}
                                                                    </tbody>
                                                                </table>
                                                                <table width="100%">
                                                                    <tbody>
                                                                        <tr class="tarifa_total">
                                                                            <td colspan="5"
                                                                                style="text-align: right;font-weight: normal;">{{ trans('hotel.label.subtotal') }} $ <b> @{{ service.import_amount.subtotal }}</b></td>
                                                                        </tr>
                                                                        <tr class="tarifa_total">
                                                                            <td colspan="5"
                                                                                style="text-align: right;font-weight: normal;">{{ trans('hotel.label.taxes') }} / {{ trans('hotel.label.services') }} $ <b>@{{ service.import_amount.taxes }}</b></td>
                                                                        </tr>
                                                                        <tr class="tarifa_total">
                                                                            <td colspan="5"
                                                                                style="text-align: right;font-weight: normal;">Total $ <b>@{{ service.import_amount.total }}</b></td>
                                                                        </tr>
                                                                        {{-- <tr class="tarifa_total">
                                                                                    <td colspan="5" style="text-align: right;font-weight: normal;">{{trans('service.label.cost_per_passenger')}} $ <b>@{{ service.import_amount.price_per_person }}</b></td>
                                                                        </tr> --}}
                                                                    </tbody>
                                                                </table>

                                                            </div>

                                                        </div>
                                                    </div>
                                                    <br />
                                                    <span
                                                        style="font-weight: normal;font-size: 12px;">ADL</span>

                                            </span>
                                            </template>

                                            </span>
                                            <span class="producto-precio--num" v-else>
                                                <span :style="(service.flag_migrate == 0 && !service.price_dynamic) ? 'color: green' : (service.price_dynamic ? 'color: red' : '')">
                                                    <template v-if="!service.price_dynamic || !service.showQuantityPassengers">
                                                        USD @{{ (service.adult + service.child) > 0 ? (service.import?.price_ADL ?? 0) : 0 }}
                                                    </template>
                                                </span>

                                                <!-- Controles de precio dinámico -->
                                                <div v-if="service.price_dynamic == 1">
                                                    <span v-if="(service.import?.price_ADL ?? 0) == 0" style="color: red; display: block;">
                                                        TARIFA DINÁMICA
                                                    </span>
                                                    <template v-if="service.showQuantityPassengers">
                                                        <div class="form-group">
                                                            <label>Price ADL</label>
                                                            <input type="number" step="0.01" v-model="price_dynamic_amount" class="form-control" @focus="isEditingPrice = false"
                                                                @blur="isEditingPrice = false">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Markup</label>
                                                            <input type="number" step="0.01" v-model="price_dynamic_markup" class="form-control">
                                                        </div>
                                                    </template>
                                                </div>

                                                {{-- Tarifa Detalle --}}

                                                <a href="javascript:void(0)" role="button" data-toggle="dropdown"
                                                    aria-haspopup="false" aria-expanded="false"
                                                    style="border: none;">
                                                    <i class="fa fa-question-circle"
                                                        style="font-size: initial;    color: green;"></i>
                                                </a>

                                                <div @click.stop=""
                                                    class="modal-dialog dropdown-menu dropdown-menu__cotizacion dropdown-menu-right"
                                                    style="overflow-y: scroll; z-index: 100;">
                                                    <div class="dropdown-menu_body modal-body"
                                                        style="padding: 0px 10px 0px 10px !important;font-size: 12px!important;">
                                                        <div class="">

                                                            <div class="titleModalTarifa"
                                                                style="font-weight: normal;font-size: 14px !important;">
                                                                @{{ service.service.service_translations.length>0 ? service.service.service_translations[0].name : '' }}
                                                                {{-- <span>Rate : @{{ service_detail_selected.rate.name }}
                                            </span> --}}
                                            <br />
                                            <span> @{{ service.date_in }} <br></span>
                                        </div>
                                        <table width="100%"
                                            style="margin-bottom: 10px;font-weight: normal;">
                                            <thead>
                                                <tr>
                                                    <th class="text-center">{{ trans('hotel.label.adults') }}(@{{ service.adult }})</th>
                                                    {{-- <template v-if="service.import.import_childres && service.import.import_childres.length>0">
                                                                                <th class="text-center" v-for="children in service.import.import_childres" >Children(@{{ children.age }} a)</th>
                                                    </template> --}}
                                                    {{-- <template v-if="service.import.import_childres.length == 0">
                                                                                <th class="text-center">Children(0)</th>
                                                                            </template> --}}
                                                    <th class="text-center">{{ trans('hotel.label.child') }}(@{{ service.child }})</th>
                                                    <th class="text-center no-border">{{ trans('hotel.label.subtotal') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="text-center">@{{ service.import.total_amount_adult }}</td>
                                                    {{-- <template v-if="service.import.import_childres && service.import.import_childres.length>0">
                                                                                <td class="text-center" v-for="children in service.import.import_childres"  > @{{ children.price }} </td>
                                                    </template>
                                                    <template v-if="service.import.import_childres.length == 0">
                                                        <td class="text-center"> 0.00 </td>
                                                    </template> --}}
                                                    <td class="text-center"> @{{ service.import.total_amount_child }}</td>
                                                    <td class="text-center no-border">@{{ service.import.sub_total }}</td>
                                                </tr>
                                                <tr v-if="service.alerta_change_children_ages">
                                                    <td :colspan="(service.adult + service.child ) + 1"
                                                        style="font-size: 10px!important; color: red;">
                                                        (*) Hubo un cambio automático en la tarifa de niños, se cambió por la tarifa del adulto porque la edad del niño seleccionado es mayor al máximo configurado en el servicio
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <table width="100%">
                                            <tbody>
                                                <tr class="tarifa_total">
                                                    <td colspan="5"
                                                        style="text-align: right;font-weight: normal;">{{ trans('hotel.label.subtotal') }} $ <b> @{{ service.import.sub_total }}</b></td>
                                                </tr>
                                                <tr class="tarifa_total">
                                                    <td colspan="5"
                                                        style="text-align: right;font-weight: normal;">{{ trans('hotel.label.taxes') }} / {{ trans('hotel.label.services') }} $ <b>@{{ service.import.total_taxes }}</b></td>
                                                </tr>
                                                <tr class="tarifa_total">
                                                    <td colspan="5"
                                                        style="text-align: right;font-weight: normal;">Total $ <b>@{{ service.import.total_amount }}</b></td>
                                                </tr>
                                                {{-- <tr class="tarifa_total">
                                                                            <td colspan="5" style="text-align: right;font-weight: normal;">{{trans('service.label.cost_per_passenger')}} $ <b>@{{ service.import.price_per_person }}</b></td>
                                                </tr> --}}
                                            </tbody>
                                        </table>

                                    </div>

                    </div>
                </div>
                <br />
                <span style="font-weight: normal;font-size: 12px;">ADL</span>
                </span>


            </div>
            {{-- <div--}}
            {{-- class="prod-precio d-flex align-items-center justify-content-center"--}}
            {{-- v-if="service.amount !=null && quote_open.operation =='passengers' && service.child >0">--}}
            {{-- <span class="producto-precio--num"><small>CHD </small> @{{ service.amount.price_child  | roundLito }}</span>--}}
            {{-- </div>--}}
            <div
                class="prod-acomodacion d-flex align-items-center justify-content-center dropdown-group"
                v-if="quote_open.operation =='passengers'  && service.type !='group_header'"
                id="dropdown_passenger">
                <a data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                    class="acciones__item">
                    <span class="producto-acomodacion--cambiar btn btn-icon"
                        title="{{ trans('quote.label.assign_list') }}"
                        @click="selectServiceSelected(service,index_service)">
                        <i class="icon icon-user-switch"
                            v-show="service.adult != quantity_persons.adults || service.child != quantity_persons.child"></i>
                        <i class="icon icon-user-check"
                            v-show="service.adult == quantity_persons.adults  && service.child == quantity_persons.child"></i>
                        {{ trans('quote.label.assign_list') }}
                    </span>
                </a>
                <div @click.stop=""
                    class="dropdown-menu dropdown-menu__cotizacion dropdown-menu-right"
                    style="overflow-y: scroll; z-index: 100;">
                    <div class="dropdown-menu_body">
                        <div class="d-flex align-items-center"
                            v-for="(passenger,index_passenger) in service.passengers_front">
                            <label class="mx-2">
                                <input type="checkbox" v-model="passenger.checked"
                                    @change="willSavePassengerService(service, passenger)"
                                    :disabled="isQuoteBlocked">
                                <span
                                    v-if="!(!!passenger.first_name && !!passenger.last_name)">
                                    <span v-if="passenger.type == 'ADL'">{{ trans('quote.label.adults') }} @{{ passenger.index }}</span>
                                    <span v-if="passenger.type == 'CHD'">{{ trans('quote.label.child') }} @{{ passenger.index }} @{{ passenger.age_child ? '(' + passenger.age_child.age + ') a' : '' }} </span>
                                    <span v-if="!(!!passenger.type)">{{ trans('quote.label.adults') }} @{{ passenger.index }}</span>
                                </span>
                                <span v-else>
                                    @{{ passenger.first_name }} @{{ passenger.last_name }}
                                </span>
                            </label>
                        </div>
                        <button class="btn btn-success" @click="savePassengerService" :disabled="isQuoteBlocked"><i
                                class="icon icon-save"></i> {{ trans('quote.label.save') }}
                        </button>
                    </div>
                </div>
            </div>

            {{-- LA CAMITA  v-if="service.type=='hotel'"--}}
            {{-- <img class="ico-error" src="{{ asset('/images/status-error.png') }}" alt="">--}}
            <div v-if="service.type=='group_header'">
                <div class="d-flex justify-content-center col-12">
                    <div class="form text-center col-4 mr-2"
                        :class="{'accommodation-enabled' : service.single > 0, 'accommodation-disabled' : service.single == 0}">
                        <label for="" class="font-weight-bold">SGL</label><br>
                        <input class="mx-1 inputs-ocupation" type="number"
                            @input="updateOccupationHotel" min="0"
                            max="30"
                            step="1"
                            :disabled="service.simple_enabled || isQuoteBlocked"
                            v-model="service.single">
                    </div>
                    <div class="form text-center col-4 mr-2"
                        :class="{'accommodation-enabled' : service.double > 0, 'accommodation-disabled' : service.double == 0}">
                        <label for="" class="font-weight-bold">DBL</label><br>
                        <input class="mx-1 inputs-ocupation" type="number"
                            @input="updateOccupationHotel" min="0"
                            max="30"
                            step="1"
                            :disabled="service.double_enabled || isQuoteBlocked"
                            v-model="service.double">
                    </div>
                    <div class="form text-center col-4 mr-2"
                        :class="{'accommodation-enabled' : service.triple > 0, 'accommodation-disabled' : service.triple == 0}">
                        <label for="" class="font-weight-bold">TPL</label><br>
                        <input class="mx-1 inputs-ocupation" type="number"
                            @input="updateOccupationHotel" min="0"
                            max="30"
                            step="1"
                            :disabled="service.triple_enabled || isQuoteBlocked"
                            v-model="service.triple">
                    </div>
                </div>

                {{-- <div class="d-flex justify-content-center col-12"--}}
                {{-- style="padding-top: 0px !important;"--}}
                {{-- v-if="service.child > 0">--}}
                {{-- <div class="form col-4">--}}
                {{-- <label for="">DBL (CHD)</label><br>--}}
                {{-- <input class="mx-1 inputs-ocupation" type="number"--}}
                {{-- @input="updateOccupationHotel" min="0"--}}
                {{-- max="30"--}}
                {{-- step="1"--}}
                {{-- :disabled="service.double_child_enabled"--}}
                {{-- v-model="service.double_child">--}}
                {{-- </div>--}}
                {{-- <div class="form col-4">--}}
                {{-- <label for="">TPL (CHD)</label><br>--}}
                {{-- <input class="mx-1 inputs-ocupation" type="number"--}}
                {{-- @input="updateOccupationHotel" min="0"--}}
                {{-- max="30"--}}
                {{-- step="1"--}}
                {{-- :disabled="service.triple_child_enabled"--}}
                {{-- v-model="service.triple_child">--}}
                {{-- </div>--}}
                {{-- </div>--}}

                <div class="row justify-content-center col-12 distribution"
                    style="font-size: 12px;"
                    v-if="quote_open.operation!=='ranges'">
                    Distribuir: <strong style="margin: 0 5px;"> @{{
                                                        quantity_persons.adults
                                                        }} </strong> {{ trans('quote.label.adults') }} +
                    <strong style="margin: 0 5px;"> @{{ quantity_persons.child
                                                        }} </strong> {{ trans('quote.label.child') }}
                </div>

            </div>
            {{-- /fin de la camita--}}

            <button type="button" class="btn btn-primary mx-3 d-flex align-items-center"
                @click="grouped_toggle(service)"
                style="position: absolute;right: 7px;padding: 7px;top: 24%;font-size: 19px;height: 97px;"
                v-if="service.type == 'group_header' && !service.locked">
                <i class="fas fa-angle-up animated faa-bounce"
                    v-if="service.grouped_show"></i>
                <i class="fas fa-angle-down animated faa-bounce"
                    v-if="!service.grouped_show"></i>
            </button>

            {{-- <div class="mx-3 d-flex align-items-center"
                                                 style="position: absolute;right: -10px;background: #eee;padding: 7px;top: 0px;"
                                                 v-if="service.type == 'hotel'">
                                                <a href="javascript:;" @click="showModalHotelPromotion(service)">
                                                    <i class="fa fa-fire"></i>
                                                </a>
                                            </div> --}}

            <div class="content_services_group prod-group"
                v-for="(service_extension,index_service_extension) in qCateg.services"
                v-if="service.id == service_extension.parent_service_id">
                <!--<hr class="line_vertical">-->
                <div class="prod-acciones align-items-center">

                    <a href="javascript:;" style="z-index: 1;"
                        @click="isQuoteBlocked ? null : willRemoveService(qCateg, service_extension)"
                        v-if="!loading"
                        :style="isQuoteBlocked ? 'pointer-events: none; opacity: 0.6; cursor: not-allowed;' : ''">
                        <i class="icon icon-trash"
                            title="{{trans('global.icon.delete')}}"></i>
                    </a>
                    <a href="javascript:;" v-if="loading" style="color: #919595;">
                        <i class="icon icon-trash"
                            title="{{trans('global.icon.delete')}}"></i>
                    </a>

                    <a data-toggle="modal" href="#modal-servicios"
                        v-if="service_extension.type == 'service'"
                        @click="isQuoteBlocked ? null : openModalService(qCateg, service_extension)"
                        :style="isQuoteBlocked ? 'pointer-events: none; opacity: 0.6; cursor: not-allowed;' : ''">
                        <i class="icon icon-plus-circle"
                            title="{{trans('global.icon.add_and_replace')}}"></i>
                    </a>

                    <a
                        @click="isQuoteBlocked ? null : showModalHotel(service_extension)"
                        v-if="service_extension.type == 'hotel'"
                        :style="isQuoteBlocked ? 'pointer-events: none; opacity: 0.6; cursor: not-allowed;' : ''">
                        <i class="icon icon-plus-circle"
                            title="{{trans('global.icon.add_and_replace')}}"></i>
                    </a>
                </div>
                <div class="prod-fecha">
                    <span>
                        <date-picker class="date" v-model="service_extension.date_in"
                            :config="optionsR"
                            @dp-show="showDatePickerService(service_extension)"
                            @dp-change="updateDateInService(service_extension,index_service)"
                            :disabled="isQuoteBlocked"></date-picker>
                    </span>

                    <span>
                        <input type="text" class="date-disabled"
                            v-model="service_extension.date_out"
                            v-if="service_extension.type =='hotel'"
                            v-show="show_dateIn"
                            disabled>
                    </span>
                    {{-- <span class="fecha">@{{ service_extension.date_in }}</span>--}}
                </div>
                <div class="prod-estado">
                    <span class="estado estado-rq"
                        v-if="service_extension.type == 'hotel' && service_extension.on_request == 1"></span>
                    <span class="estado estado-ok"
                        v-if="service_extension.type == 'hotel' && service_extension.on_request == 0"></span>
                    <span class="estado estado-ok"
                        v-if="service_extension.type == 'service' && service_extension.service_rate!=null && service_extension.on_request == 0"></span>
                    <span class="estado estado-rq"
                        v-if="service_extension.type == 'service' && service_extension.service_rate!=null && service_extension.on_request == 1"></span>
                    <i class="estado fa fa-times"
                        v-if="service_extension.type == 'service' && service_extension.service_rate==null"></i>

                </div>
                <div class="prod-descripcion ml-5 mr-2">
                    <span class="id mr-3" v-if="service_extension.type =='service'">[@{{ service_extension.service.aurora_code }}]</span>
                    <span class="texto" v-if="service_extension.type =='service'">@{{ service_extension.service.name }}</span>

                    <span class="id mr-3" v-if="service_extension.type =='hotel'">[@{{ service_extension.hotel.channel[0].code }}]</span>
                    <span class="texto" v-if="service_extension.type =='hotel'">
                        @{{ service_extension.hotel.name }}
                        <span
                            v-for="(service_room, srKey) in service_extension.service_rooms"
                            v-if="srKey < 3 && service_extension.service_rooms.length >= 1">
                            <br>
                            <span
                                v-if="service_room.rate_plan_room != null && service_room.rate_plan_room.room.translations[0]">
                                <a style="margin-left: 6px; margin-top: 3px; font-size: 9.5px;cursor:pointer;"
                                    v-if="service_extension.type == 'hotel'"
                                    @click="isQuoteBlocked ? null : editPlanRooms(service_extension)"
                                    :style="isQuoteBlocked ? 'pointer-events: none; opacity: 0.6; cursor: not-allowed;' : ''">
                                    <i class="fa fa-bed icon_green"></i>
                                    @{{ service_room.rate_plan_room.room.translations[0].value }}
                                </a>
                            </span>
                        </span>
                        <span v-if="service_extension.service_rooms.length > 3"> ({{ trans('global.label.and') }} @{{ service_extension.service_rooms.length - 3 }} {{ trans('global.label.more') }}) </span>
                    </span>

                    <a style="margin-left: 15px; margin-top: 3px;cursor:pointer;"
                        v-if="service_extension.type == 'hotel' && service_extension.service_rooms.length==0"
                        @click="isQuoteBlocked ? null : editPlanRooms(service_extension)"
                        :style="isQuoteBlocked ? 'pointer-events: none; opacity: 0.6; cursor: not-allowed;' : ''">
                        <i class="fa fa-bed"></i>
                    </a>

                    <span class="id mr-3" :title="service_extension.code_flight"
                        v-if="service_extension.type =='flight'">[{{ trans('quote.label.flight') }} -
                        <strong
                            v-if="service_extension.code_flight == 'AEC' || service_extension.code_flight == 'AECFLT'">
                            {{ trans('flights.label.national') }}
                        </strong>
                        <strong
                            v-if="service_extension.code_flight == 'AEI' || service_extension.code_flight == 'AEIFLT'">
                            {{ trans('flights.label.international') }}
                        </strong>]
                    </span>

                </div>
                <div class="prod-detalle mx-1 justify-content-center"
                    v-if="quote_open.operation =='passengers'">
                    <div class=""
                        v-show="!service_extension.showQuantityPassengers">
                        @{{ service_extension.adult
                                                        }} {{ trans('quote.label.adults') }}
                        <div class="" v-if="service_extension.adult >0">@{{
                                                            service_extension.child
                                                            }} {{ trans('quote.label.child') }}
                        </div>
                    </div>
                    <div class="form-group"
                        v-show="service_extension.showQuantityPassengers">
                        <label>{{ trans('quote.label.adults') }}</label>
                        <select class="form-control" v-model="service.adult"
                            @change="updatePassengerService(service)"
                            :disabled="isQuoteBlocked">
                            <option value="0">0</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                            <option value="7">7</option>
                            <option value="8">8</option>
                            <option value="9">9</option>
                            <option value="10">10</option>
                        </select>
                    </div>
                    <div class="form-group"
                        v-if="service_extension.adult > 0 && service_extension.showQuantityPassengers">
                        <label>{{ trans('quote.label.child') }}</label>
                        <select class="form-control"
                            v-model="service_extension.child"
                            @change="updatePassengerService(service_extension)"
                            :disabled="isQuoteBlocked">
                            <option value="0">0</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                        </select>
                    </div>
                </div>
                {{-- <div class="prod-editar text-center"
                                                     v-if="quote_open.operation =='passengers'">
                                                        <span class="btn btn-icon producto-editar--boton"
                                                              title="{{ trans('quote.label.edit') }}"
                @click="showSelectQuantityPassengersService(service_extension)"
                v-if="!service_extension.showQuantityPassengers">
                <i class="icon icon-edit"></i>
                </span>
                <span class="btn btn-icon producto-editar--boton"
                    title="{{ trans('quote.label.save') }}"
                    @click="showSelectQuantityPassengersService(service_extension)"
                    v-if="service_extension.showQuantityPassengers">
                    <i class="icon icon-save"></i>
                </span>
            </div> --}}
            <div class="prod-precio mx-3 justify-content-center"
                v-if="service_extension.amount !=null && quote_open.operation =='passengers'">
                <span class="producto-precio--num"><small>USD. </small>@{{ service_extension.amount.amount }}</span>
            </div>
            <div class="prod-acomodacion mx-2 dropdown-group"
                v-if="quote_open.operation =='passengers'">
                <a data-toggle="dropdown" aria-haspopup="true"
                    aria-expanded="false"
                    class="acciones__item">
                    <span class="producto-acomodacion--cambiar btn btn-icon"
                        title="{{ trans('quote.label.assign_list') }}"
                        @click="selectServiceSelected(service,index_service)">
                        <i class="icon icon-user-switch"
                            v-show="service.adult != quantity_persons.adults || service.child != quantity_persons.child"></i>
                        <i class="icon icon-user-check"
                            v-show="service.adult == quantity_persons.adults  && service.child == quantity_persons.child"></i>
                    </span>
                </a>
                <div @click.stop=""
                    class="dropdown-menu dropdown-menu__cotizacion dropdown-menu-right"
                    style="overflow-y: scroll; z-index: 100;">
                    <div class="dropdown-menu_body">
                        <div class="d-flex align-items-center"
                            v-for="(passenger,index_passenger) in service.passengers_front">
                            <label class="mx-2">
                                <input type="checkbox"
                                    v-model="passenger.checked"
                                    @change="willSavePassengerService(service, passenger)"
                                    :disabled="isQuoteBlocked">
                                <span
                                    v-if="!(!!passenger.first_name && !!passenger.last_name)">
                                    <span v-if="passenger.type == 'ADL'">{{ trans('quote.label.adults') }} @{{ passenger.index }}</span>
                                    <span v-if="passenger.type == 'CHD'">{{ trans('quote.label.child') }} @{{ passenger.index }}</span>
                                    <span v-if="!(!!passenger.type)">{{ trans('quote.label.adults') }} @{{ passenger.index }}</span>
                                </span>
                                <span v-else>
                                    @{{ passenger.checked }} @{{ passenger.first_name }}
                                    @{{ passenger.last_name }}
                                </span>
                            </label>
                        </div>
                        <button class="btn btn-success"
                            @click="savePassengerService"
                            :disabled="isQuoteBlocked">
                            <i class="icon icon-save"></i> {{ trans('quote.label.save') }}
                        </button>
                    </div>
                </div>
            </div>
            {{-- <div class="prod-acomodacion dropdown-group"--}}
            {{-- v-if="service.type=='hotel'">--}}
            {{-- <a data-toggle="dropdown" aria-haspopup="true"--}}
            {{-- aria-expanded="false"--}}
            {{-- class="acciones__item">--}}
            {{-- <span class="producto-acomodacion--cambiar btn btn-icon"--}}
            {{-- title="{{ trans('quote.label.modify_hotel_occupancy') }}"--}}
            {{-- @click="setServiceHotelSelected(service)">--}}
            {{-- <i :class="{'icon icon-bed-simple':true,--}}
            {{-- 'icon_green':((service.single+service.double+service.triple)>0),--}}
            {{-- 'icon_red':((service.single+service.double+service.triple)==0)}"--}}
            {{-- title="{{trans('global.icon.accommodation')}}"></i>--}}
            {{-- </span>--}}
            {{-- </a>--}}
            {{-- <div--}}
            {{-- class="dropdown-menu dropdown-menu__cotizacion dropdown-menu-right"--}}
            {{-- @click.stop="" style="overflow-y: scroll; z-index: 100;"--}}
            {{-- v-if="service_selected.single !='' && service_selected.double !='' && service_selected.type=='hotel'">--}}
            {{-- <div class="dropdown-menu_body container">--}}

            {{-- <div--}}
            {{-- class="row justify-content-center col-12 alert-accomodation">--}}
            {{-- <div class="alert alert-warning-quote"><i--}}
            {{-- class="fa fa-info-circle"></i> La--}}
            {{-- acomodación no--}}
            {{-- coincide con las tarifas asignadas.--}}
            {{-- </div>--}}
            {{-- </div>--}}
            {{-- <div class="d-flex justify-content-center col-12">--}}
            {{-- <div class="form col-4">--}}
            {{-- <label for="">SGL</label>--}}
            {{-- <input class="mx-1" type="number" min="0"--}}
            {{-- max="30"--}}
            {{-- step="1" @input="updateOccupationHotel"--}}
            {{-- v-model="service_selected.single">--}}
            {{-- </div>--}}
            {{-- <div class="form col-4">--}}
            {{-- <label for="">DBL</label>--}}
            {{-- <input class="mx-1" type="number" min="0"--}}
            {{-- max="30"--}}
            {{-- step="1" @input="updateOccupationHotel"--}}
            {{-- v-model="service_selected.double">--}}
            {{-- </div>--}}
            {{-- <div class="form col-4">--}}
            {{-- <label for="">TPL</label>--}}
            {{-- <input class="mx-1" type="number" min="0"--}}
            {{-- max="30"--}}
            {{-- step="1" @input="updateOccupationHotel"--}}
            {{-- v-model="service_selected.triple">--}}
            {{-- </div>--}}
            {{-- </div>--}}
            {{-- </div>--}}
            {{-- </div>--}}
            {{-- </div>--}}
        </div>
    </div>
    <div class="row"
        v-if="service.validations.length > 0">
        <div class="col-md-12 p-3">
            <hr>
            <h5 class="font-weight-bold text-danger px-3" v-if="!service.is_file">
                *{{trans('quote.label.observations')}}:
            </h5>
            <div v-for="validation in service.validations.slice().reverse()" class="badge badge-warning warning-date mr-2 mb-2 mx-3" style="color: #856404;
    background-color: #fff3cd;
    border-color: #ffeeba;">
                <template v-if="!service.is_file || (service.is_file && service.file_status)">
                    <i class="fas fa-exclamation-circle animated faa-flash"></i>
                    <span v-if="validation.range !== ''">
                        {{trans('quote.label.ranges')}} @{{ validation.range }} <i
                            class="fas fa-long-arrow-alt-right"></i>
                    </span> @{{ validation.error }}
                </template>
            </div>
        </div>
    </div>
    <div class="row"
        v-if="service.is_file && !service.file_status">
        <div class="col-md-12 p-3">
            <div class="badge badge-danger danger-date mr-2 mb-2 mx-3">
                <i class="fas fa-exclamation-circle animated faa-flash"></i>
                Servicio Cancelado
                @{{ service.file_amount_cost >0 ? 'con penalidad' : 'sin penalidad'  }}
                <span></span>
            </div>
        </div>
    </div>
    <div
        v-if="(quote_open.operation == 'passengers' && quote_open.people[0].child>0) && ((service.type=='hotel' || service.type =='group_header') && (service.hotel.allows_child==1 || service.hotel.allows_teenagers)) "
        style="width: 100%;font-size: 13px;text-align: right;">
        <small class="alert alert-info m-0" style="padding: 4px !important;">

            <i class="fa fa-info-circle"></i>

            {{trans('quote.label.age_provider')}}

            <template v-if="service.hotel.allows_teenagers">
                {{trans('quote.label.age_teenagers_between_provider')}} @{{
                                                    service.hotel.min_age_teenagers }} {{trans('quote.label.and')}} @{{
                                                    service.hotel.max_age_teenagers
                                                    }} {{trans('quote.label.years_old')}}
            </template>

            <template v-if="service.hotel.allows_child">
                {{trans('quote.label.and')}}

                {{trans('quote.label.age_child_between_provider')}} @{{
                                                    service.hotel.min_age_child }} {{trans('quote.label.and')}} @{{
                                                    service.hotel.max_age_child }} {{trans('quote.label.years_old')}}
            </template>

        </small>
    </div>
    <div
        v-if="service.type=='service' && service.service.allow_child==1 && quantity_persons.child>0"
        style="width: 100%;font-size: 13px;text-align: right;">
        <small class="alert alert-info m-0" style="padding: 4px !important;">
            <i class="fa fa-info-circle"></i>

            {{trans('quote.label.age_provider')}}
            {{trans('quote.label.age_teenagers_between_provider')}} @{{
                                                service.service.infant_min_age }} {{trans('quote.label.and')}} @{{
                                                service.service.infant_max_age }} {{trans('quote.label.years_old')}}
            <template
                v-if="service.service.children_ages && service.service.children_ages.length>0 ">
                {{trans('quote.label.age_child_between_provider')}} @{{
                                                    service.service.children_ages[0].min_age
                                                    }} {{trans('quote.label.and')}} @{{
                                                    service.service.children_ages[0].max_age
                                                    }} {{trans('quote.label.years_old')}}
            </template>
        </small>
    </div>

    <b-overlay no-center :show="service.locked || client_file_incorrect" :opacity="0.42" z-index="1" gi
        rounded="sm" no-wrap>
        <template #overlay>
            <i class="fas fa-lock position-absolute text-danger"
                style="top: 43%; left: 14px;font-size: 20px;"></i>
        </template>
    </b-overlay>
    </li>
    </transition-group>
    </draggable>
    <b-overlay no-center :show="editing_quote" :opacity="0.42" rounded="sm" z-index="1" no-wrap>
        <template #overlay>
            <i class="fas fa-lock position-absolute text-danger"
                style="top: 0px; right: 0px;font-size: 20px;"></i>
        </template>
    </b-overlay>
    </div>

    <div class="col-12 text-center" style="background: #f6f6f6; padding: 22px;"
        v-if="qCateg.services.length==0">
        {{ trans('quote.label.no_service_added') }}
    </div>
    </div>
    </div>
    <!-- End Lista de Resultados -->
    <div class="col-12 cotizacion-incluir d-flex align-items-center justify-content-end"
        v-if="quote_open!=''">
        <div class="text-center">
            <button class="btn btn-secondary"
                @click="isQuoteBlocked ? null : showModalFlight('')"
                data-toggle="modal"
                data-target="#modal-flight"
                :disabled="isQuoteBlocked">
                + {{ trans('quote.label.flight') }}
            </button>
            {{-- <button class="btn btn-secondary" data-toggle="modal" data-target="#modal_extensions"
                                @click="showModalExtension">+ {{ trans('quote.label.extension') }}
            </button> --}}
            <button class="btn btn-secondary" data-toggle="modal" data-target="#modal-servicios"
                @click="isQuoteBlocked ? null : showCategories"
                :disabled="isQuoteBlocked">
                + {{ trans('quote.label.service') }}
            </button>
            <button class="btn btn-secondary"
                @click="isQuoteBlocked ? null : showModalHotel('')"
                :disabled="isQuoteBlocked">
                + {{ trans('quote.label.hotel') }}
            </button>
        </div>
        <b-overlay no-center :show="editing_quote || isQuoteBlocked" :opacity="0.42" rounded="sm" z-index="1" no-wrap>
            <template #overlay>
                <i class="fas fa-lock position-absolute text-danger"
                    style="top: 0px; right: 0px;font-size: 20px;"></i>
            </template>
        </b-overlay>
    </div>
    <div class="col-12 cotizacion-cotizar" v-if="quote_open!=''">
        @include('quotes.buttons')
        <b-overlay no-center :show="editing_quote" :opacity="0.42" rounded="sm" z-index="1" no-wrap>
            <template #overlay>
                <i class="fas fa-lock position-absolute text-danger"
                    style="top: 0px; right: 0px;font-size: 20px;"></i>
            </template>
        </b-overlay>
    </div>
    </div>
    </div>
    <footer-component></footer-component>
</section>
{{-- modales del formulario          --}}
{{-- modal Lista de pasajeros de un servicio         --}}
<modal-passengers ref="modal_passengers" multi-region="isQuoteBlocked"></modal-passengers>

{{-- modal Ocupacion de Hotel General         --}}
<b-modal centered id="modal_occupation_hotel" ref="modal_occupation_hotel" :no-close-on-backdrop="true"
    :no-close-on-esc="true" :hide-header-close="true">
    <h1>
        <i class="icon icon-bed-simple mr-2"
            title="{{trans('global.icon.accommodation')}}"></i> {{ trans('quote.label.hotel_general_occupation') }}
    </h1>
    <hr>
    <div class="d-flex justify-content-between mb-5">
        <div class="form-xs">
            <label for="">SGL</label>

            <input type="number" min="0" max="30" step="1" class="form-control"
                v-model="control_service_selected_general.single" v-if="quote_open.operation == 'passengers'">
            <input type="number" min="0" max="1" step="1" class="form-control"
                v-model="control_service_selected_general.single" v-else>
        </div>
        <div class="form-xs">
            <label for="">DBL</label>
            <input type="number" min="0" max="30" step="1" class="form-control"
                v-model="control_service_selected_general.double" v-if="quote_open.operation == 'passengers'">
            <input type="number" min="0" max="1" step="1" class="form-control"
                v-model="control_service_selected_general.double" v-else>
        </div>
        <div class="form-xs">
            <label for="">TPL</label>
            <input type="number" min="0" max="30" step="1" class="form-control"
                v-model="control_service_selected_general.triple" v-if="quote_open.operation == 'passengers'">
            <input type="number" min="0" max="1" step="1" class="form-control"
                v-model="control_service_selected_general.triple" v-else>
        </div>
    </div>
    <hr>
    <div class="my-3 d-flex">
        Distribuir: <strong style="margin: 0 5px;"> @{{ quantity_persons.adults
                }} </strong> {{ trans('quote.label.adults') }} + <strong style="margin: 0 5px;"> @{{
                quantity_persons.child }} </strong> {{ trans('quote.label.child') }}
    </div>

    <div class="my-3 d-flex">
        <button class="btn btn-primary mr-2"
            @click="validateDistribution">
            <i class="fa fa-spin fa-spinner" v-if="loading_occupation"></i> {{ trans('global.icon.accommodation') }}
        </button>
        <button class="btn btn-cancelar ml-2" style="height: 52px;" :disabled="loading_occupation"
            @click="closeModalOccupationHotel">{{ trans('quote.label.cancel') }}</button>
    </div>


    <div slot="modal-footer">

    </div>

</b-modal>

<b-modal centered id="modal_occupation_hotel_pax" ref="modal_occupation_hotel_pax" size="lg"
    :no-close-on-backdrop="true" :no-close-on-esc="true" :hide-header-close="true">

    <h1>
        <i class="icon icon-bed-simple mr-2"
            title="{{trans('global.icon.accommodation')}}"></i> {{ trans('global.icon.accommodation') }}
    </h1>
    <hr>
    <div class="d-flex justify-content-between mb-5">


        <table width="100%">
            <thead>
                <tr>
                    <th style="width: 16%">{{ trans('quote.label.type_room') }}</th>
                    <th class="text-center">{{ trans('quote.label.distribution') }}</th>
                </tr>
            </thead>
            <tbody class="multiselec">

                <tr v-for="distribution in distribution_passengers">
                    <td class="font-weight-bold:text-align:center">@{{ distribution.type_room_name }}</td>
                    <td class="text-center">
                        <multiselect :clear-on-select="false"
                            :close-on-select="false"
                            :hide-selected="true"
                            :searchable="false"
                            :multiple="true"
                            :options="select_passengers"
                            :placeholder="translations.label.search_users"
                            :preserve-search="false"
                            :tag-placeholder="translations.label.select_users"
                            :taggable="false"
                            label="label"
                            ref="multiselect"
                            track-by="code"
                            v-model="distribution.passengers"
                            :loading="loading"
                            :internal-search="false"
                            :show-no-results="false">
                        </multiselect>

                    </td>
                </tr>

            </tbody>
        </table>


    </div>
    <hr>

    <div class="my-3 d-flex">
        <button class="btn btn-primary mr-2" style="width: 150px;"
            @click="updateOccupationHotelGeneralPax">
            <i class="fa fa-spin fa-spinner" v-if="loading_occupation"></i> {{ trans('quote.label.save') }}
        </button>
        <button class="btn btn-cancelar ml-2" style="height: 52px;width: 150px;" :disabled="loading_occupation"
            @click="closeModalOccupationHotelPax">{{ trans('quote.label.cancel') }}</button>
    </div>


    <div slot="modal-footer">

    </div>


</b-modal>

{{-- End modal Ocupacion de Hotel        --}}

{{-- modal-notas           --}}
<div class="modal fade" id="modal-notas" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="d-block" style="margin:-20px;">
                    <div class="mb-5">
                        <h2><i class="icon-file-text"></i>{{ trans('quote.label.notes') }}</h2>
                    </div>
                    <div class="container box-content">

                        <div class="mt-5 ml-4">
                            <form class=" mt-3">
                                <div class="form-group">
                                    <label><strong>{{ trans('quote.label.new_note') }}</strong></label>
                                    <textarea rows="4" class="form-control"
                                        placeholder=""
                                        v-model="note_comment"></textarea>
                                </div>
                                <div class="form-group d-flex align-items-center justify-content-end">
                                    {{-- <p class="text-muted m-2">Julio 10,2019 - 16:22hrs</p>--}}
                                    <button class="btn btn-danger btn-lg m-2" @click="createNote" type="button">
                                        {{ trans('quote.label.save_note') }}
                                    </button>
                                    {{-- <b-button v-on:click="" class="btn btn-inverse btn-lg m-2">Regresar--}}
                                    {{-- </b-button>--}}
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="content-comments">
                        <div class="comment-box p-4 mb-4" v-for="(note,index_note) in notes">
                            <!-- Avatar -->
                            <div class="commet-avatar">
                                <img v-if="note.user_photo != '' && note.user_photo != null"
                                    :src="'/images/users/'+note.user_photo" width="40px"
                                    alt="" />
                                <img v-else width="40px"
                                    :src="baseURLPhoto + 'images/anonimo.jpg'"
                                    alt="" />
                            </div>
                            <!-- Contenedor del Comentario -->
                            <div class="box">
                                <div class="commet-head mb-1">
                                    <h4>@{{ note.user_name }}</h4>
                                    <div class="d-flex justify-content-start">
                                        <span
                                            class="text-muted mr-4">@{{ note.created_at | formattedDateNotes}}hrs.</span>
                                        <a class="text-muted" href="#" v-if="note.user_id == user.id"
                                            @click="showEditNote(note)">{{ trans('quote.label.edit_note') }}</a>
                                    </div>
                                </div>
                                <div class="comment-content">
                                    <div v-if="!note.edit">@{{ note.comment }}</div>
                                    <div class="form-group" v-if="note.edit">
                                        <textarea rows="4" class="form-control"
                                            v-model="note.comment"></textarea>
                                    </div>
                                    <button class="btn btn-success" @click="editNote(note)" v-if="note.edit">
                                        {{ trans('quote.label.save_note') }}
                                    </button>
                                </div>
                            </div>
                            <!-- Responder Comentario -->
                            <hr class="ml-10">
                            <div class="commet-response p-3">
                                <div class="response ml-10">
                                    <i class="icon-corner-down-right" id="ico"></i>
                                    <a href="#" @click="viewFormResponse(note)"
                                        id="res">{{ trans('quote.label.reply_note') }}</a>
                                </div>
                                <div class="ml-10">
                                    <form class=" mt-3" v-show="note.create_response">
                                        <div class="form-group">
                                            <textarea rows="4" class="form-control"
                                                placeholder=""
                                                v-model="note_response"></textarea>
                                        </div>
                                        <div class="form-group d-flex align-items-center justify-content-end">
                                            <button class="btn btn-danger btn-lg m-2"
                                                @click="createResponse(index_note)">
                                                {{ trans('quote.label.save_note') }}
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <template v-for="response in note.responses">
                                <hr class="ml-10">
                                <div class="commet-response p-3">
                                    <div class="commet-icon">
                                        <i class="icon-corner-down-right"></i>
                                    </div>
                                    <!-- Avatar -->
                                    <div class="commet-avatar">
                                        <img v-if="response.user_photo != '' && response.user_photo != null"
                                            :src="'/images/users/'+response.user_photo" width="40px"
                                            alt="" />
                                        <img v-else width="40px"
                                            :src="baseURLPhoto + 'images/anonimo.jpg'"
                                            alt="" />
                                    </div>
                                    <!-- Contenedor del Comentario -->
                                    <div class="box">
                                        <div class="commet-head mb-1">
                                            <h4>@{{ response.user_name }}</h4>
                                            <div class="d-flex justify-content-start">
                                                <span class="text-muted mr-4">@{{ response.created_at | formattedDateNotes}}hrs.</span>
                                                <a class="text-muted" href="#"
                                                    v-if="response.user_id == user.id"
                                                    @click="showEditNote(response)">{{ trans('quote.label.edit_note') }}</a>
                                            </div>
                                        </div>
                                        <div class="comment-content">
                                            <div v-if="!response.edit">@{{ response.comment }}</div>
                                            <div class="form-group" v-if="response.edit">
                                                <textarea rows="4" class="form-control"
                                                    v-model="response.comment"></textarea>
                                            </div>
                                            <button class="btn btn-success" @click="editNote(response)"
                                                v-if="response.edit">{{ trans('quote.label.save_note') }}
                                            </button>
                                        </div>
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
{{-- End modal-notas         --}}

{{-- modal-rango           --}}
<div class="modal fade" id="modal-rank" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <table class="table justify-content-center">
                    <thead>
                        <tr>
                            <th scope="col" class="col-ini">#</th>
                            <th scope="col" class="col">{{ trans('quote.label.from') }}</th>
                            <th scope="col" class="col">{{ trans('quote.label.to') }}</th>
                            <th scope="col" class="col">{{ trans('quote.label.single') }}</th>
                            <th scope="col" class="col">{{ trans('quote.label.double') }}</th>
                            <th scope="col" class="col">{{ trans('quote.label.triple') }}</th>
                            <th scope="col" class="col-fin">{{ trans('quote.label.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th scope="row" class="th">1</th>
                            <td nowrap class="td"><input type="text" class="form-control" placeholder="1" /></td>
                            <td nowrap class="td"><input type="text" class="form-control" placeholder="1" /></td>
                            <td nowrap class="td"><input type="text" class="form-control" placeholder="208.50" />
                            </td>
                            <td nowrap class="td"><input type="text" class="form-control" placeholder="0.00" /></td>
                            <td nowrap class="td"><input type="text" class="form-control" placeholder="0.00" /></td>
                            <td nowrap class="td icon-modal"><a class="" title=""><i
                                        class="icon-plus-square"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" class="th">2</th>
                            <td nowrap class="td"><input type="text" class="form-control" placeholder="1" /></td>
                            <td nowrap class="td"><input type="text" class="form-control" placeholder="1" /></td>
                            <td nowrap class="td"><input type="text" class="form-control" placeholder="208.50" />
                            </td>
                            <td nowrap class="td"><input type="text" class="form-control" placeholder="0.00" /></td>
                            <td nowrap class="td"><input type="text" class="form-control" placeholder="0.00" /></td>
                            <td nowrap class="td icon-modal"><a class="" title=""><i
                                        class="icon-plus-square"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" class="th">3</th>
                            <td nowrap class="td"><input type="text" class="form-control" placeholder="1" /></td>
                            <td nowrap class="td"><input type="text" class="form-control" placeholder="1" /></td>
                            <td nowrap class="td"><input type="text" class="form-control" placeholder="208.50" />
                            </td>
                            <td nowrap class="td"><input type="text" class="form-control" placeholder="0.00" /></td>
                            <td nowrap class="td"><input type="text" class="form-control" placeholder="0.00" /></td>
                            <td nowrap class="td icon-modal"><a class="" title=""><i
                                        class="icon-plus-square"></i></a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
{{-- End modal-rango         --}}

{{-- modales generales          --}}
{{-- modal-hotel           --}}
<b-modal class="modal-central" id="modal-hotel" ref="modal-hotel" size="lg">

    <div class="mb-2">
        <h3><i class="icon-grid mr-2"></i>{{ trans('quote.label.add_hotels') }}</h3>
    </div>
    <hr>
    <div class="mt-4" style="text-align: right;overflow: hidden;">
        <button :class="'btn categoria check_' + qCateg.checkAddHotel"
            v-for="qCateg in quote_open.categories"
            @click="toggleCategoryCheckAddHotel(qCateg)">
            <i class="far fa-square" v-if="!(qCateg.checkAddHotel)"></i>
            <i class="fa fa-check-square" v-if="qCateg.checkAddHotel"></i>
            @{{ qCateg.type_class.translations[0].value }}
        </button>
    </div>

    <template v-if="!check_promotion">
        <div class="form-content">
            <form class="form">
                <div class="form-row d-flex justify-content-between align-items-center">
                    <div class="form-group ">
                        <label for="">{{ trans('quote.label.country') }}:</label>
                        <v-select :options="destinationsModalHotel_countries_select"
                            @input="change_hotel_destiny_cities()"
                            v-model="destinationsModalHotel_country" placeholder=""
                            class="form-control destino"></v-select>
                    </div>
                    <div class="form-group ">
                        <label for="">{{ trans('quote.label.destiny') }}:</label>
                        <v-select :options="destinationsModalHotel_select"
                            @input="change_hotel_destiny_districts()"
                            v-model="destinationsModalHotel" placeholder=""
                            class="form-control destino"></v-select>
                    </div>
                    <div class="form-group ">
                        <label v-for="district in destinationsModalHotel_additional_select"
                            class="tag-districts">
                            <input type="radio" :value="district.code" v-model="destinationsModalHotel_district"
                                :name="'radio_hotel_destiny_'+district.parent_code">
                            @{{ district.label }}
                        </label>
                    </div>
                </div>
                <div class="form-row form-group d-flex justify-content-between align-items-center">
                    <div class="form-group">
                        <label for="">{{ trans('quote.label.category') }}:</label>
                        <select class="form-control categoria"
                            v-model="categoryModalHotel">
                            <option value=""
                                disabled>{{ trans('quote.label.select_category') }}</option>
                            <option value="all">{{ trans('hotel.label.all_categories') }}</option>
                            <option v-for="qCateg in categories"
                                :value="qCateg.id">
                                @{{ qCateg.translations[0].value }}
                            </option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="">{{ trans('quote.label.date') }}:</label>
                        <date-picker class="date form-control" v-model="add_hotel_date"
                            :config="optionsR"></date-picker>
                    </div>
                    <div class="form-group">
                        <label for="">{{ trans('quote.label.nights') }}:</label>
                        <input type="number" class="form-control noches" min="1" step="1"
                            v-model="nightsModalHotel">
                    </div>
                </div>
                <div class="form-row form-group d-flex justify-content-between align-items-center">
                    <div class="d-flex">
                        <input type="text" class="form-control filter" id="add_hotel_words"
                            placeholder="{{ trans('quote.label.filter_by_word') }}..."
                            v-model="add_hotel_words" @input="willFilterTextH" :disabled="loading">

                        <b-popover class="canSelectText" target="add_hotel_words" triggers="hover">
                            {{ trans('quote.messages.you_can_multiple_search') }}
                        </b-popover>
                        <div class="form-group cotizacion-crear--boton ml-5 mt-3">
                            <a class="clean" type="button" @click="cleanHotelFilters()"
                                :disabled="loading">
                                <i class="fas fa-magic mr-2"></i>
                                {{ trans('quote.label.clean') }}
                            </a>
                        </div>
                    </div>

                    <div class="form-group cotizacion-crear--boton">
                        <button class="btn btn-primary" type="button" @click="searchHotels"
                            :disabled="loading">
                            <i class="fa fa-spin fa-spinner" v-if="loading"></i>
                            <i class="icon-search" v-if="!loading"></i>
                        </button>
                    </div>
                </div>
                <div>
                    <div class="form-group mt-2">
                        <label class=" col-md-10 col-md-offset-2 checkbox-inline checkbox"
                            style="left: -22px;"
                            v-show="wordsH.length >= 2 && wordsH[1] != ''">
                            <input type="checkbox" class="edit"
                                v-model="allWordsH"> {{ trans('quote.messages.results_containing_the') }}
                            @{{wordsH.length}} {{ trans('quote.label.words') }}.
                        </label>
                    </div>
                </div>
            </form>
        </div>
        <hr>
    </template>
    <div class="table-responsive table-results mt-5">
        <div v-if="moreHotels.length == 0 && !loadingHotel" class=" none-result text-center">
            <i class="icon-cloud-off"></i> {{ trans('quote.label.no_results') }}.
        </div>

        <div v-if="moreHotels.length == 0 && loadingHotel" class=" none-result text-center">
            <i class="fa fa-spinner fa-spin"></i> {{ trans('quote.label.loading') }}
        </div>

        <h4 v-if="moreHotels.length>0 && hotelForReplace!=''" style="margin-top: 0px;">
            <label>
                <input type="checkbox" v-model="check_replace_hotel" disabled>
                {{ trans('quote.label.replace_hotel') }}:
                <span v-if="hotelForReplace.hotel.channel.length>0">
                    [@{{ hotelForReplace.hotel.channel[0].code }}] -
                </span> @{{ hotelForReplace.hotel.name }}
            </label>
        </h4>

        <div v-if="moreHotels.length>0">

            {{--#fffee5--}}

            <div :class="'col-12 row_' + (hkey%2)" v-for="(hotel, hkey) in moreHotels"
                v-if="hotel.countRates>0">

                <div
                    v-if="hotelForReplace!='' && hotelForReplace.hotel.id == hotel.id && check_replace_hotel">
                    <div class="accordion">
                        <h5 style="padding: 7px 7px 0;" class=" d-flex justify-content-between">
                            <strong>
                                <i class="fa fa-hotel"></i>
                                <span v-if="hotelForReplace.hotel.channel.length>0">
                                    [@{{ hotelForReplace.hotel.channel[0].code }}] -
                                </span> @{{ hotelForReplace.hotel.name }} -
                                [{{ trans('quote.label.this_hotel_not_replace') }}]
                            </strong>
                        </h5>
                    </div>
                </div>
                <div v-else>
                    <div class="accordion" @click="showContentHotel(hotel)">
                        <h5 style="padding: 7px 7px 0;" class=" d-flex justify-content-between">
                            <strong>
                                <i class="fa fa-hotel mr-2"></i>
                                {{-- <span v-if="hotel.channel.length>0">--}}
                                {{-- [@{{ hotel.channel[0].code }}] ---}}
                                {{-- </span>--}}
                                @{{ hotel.name }}
                            </strong>

                            <div
                                v-if="(quote_open.people > 0 && quote_open.people.length > 0 && quote_open.people[0].child>0) && hotel.political_children && ((hotel.political_children.child && hotel.political_children.child.allows_child==1) || (hotel.political_children.infant && hotel.political_children.infant.allows_teenagers)) "
                                style="position: absolute;display: flex;top: 34px;left: 41px;font-size: 10px;font-weight: bold;">


                                {{trans('quote.label.age_provider')}}

                                <template v-if="hotel.political_children && hotel.political_children.infant && hotel.political_children.infant.allows_teenagers">
                                    {{trans('quote.label.age_teenagers_between_provider')}} @{{
                                        hotel.political_children.infant.min_age_teenagers
                                        }} {{trans('quote.label.and')}} @{{
                                        hotel.political_children.infant.max_age_teenagers
                                        }} {{trans('quote.label.years_old')}}
                                </template>

                                <template v-if="hotel.political_children && hotel.political_children.child && hotel.political_children.child.allows_child">
                                    {{trans('quote.label.and')}}

                                    {{trans('quote.label.age_child_between_provider')}} @{{
                                        hotel.political_children.child.min_age_child }} {{trans('quote.label.and')}} @{{
                                        hotel.political_children.child.max_age_child
                                        }} {{trans('quote.label.years_old')}}
                                </template>


                            </div>

                            <i v-if="!(hotel.viewContent) && hotel.rooms.length>0"
                                class="far fa-plus-square right"
                                style="font-size: 18px; color: #a71b20;"></i>
                            <i v-if="hotel.viewContent && hotel.rooms.length>0"
                                class="far fa-minus-square right"
                                style="font-size: 18px; color: #a71b20;"></i>
                        </h5>
                    </div>
                </div>

                <div
                    v-if="hotelForReplace!='' && hotelForReplace.hotel.id == hotel.id && check_replace_hotel"></div>
                <div class="col-12" v-show="hotel.viewContent" v-else>
                    <hr>

                    <div class="col-12 p-0"
                        v-if="(hotel.notes != null && hotel.notes != '' && user_type_id == 3) || hotel.summary != null && hotel.summary != ''">
                        <div class="accordion my-4" role="tablist">
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

                    {{--
                        <div v-if="hotel.notes != null && hotel.notes != '' && user_type_id == 3">
                            <h4 class="blue" style="cursor: pointer;" id="notes">Remarks</h4>
                            <b-popover
                                target="notes"
                                title="Remarks"
                                trigger="click">
                                <div
                                    class="d-block my-2" v-html="hotel.notes.replace(/\n/g, '<br>')"
                                    style="font-size: 13px; line-height: 16px;">
                                </div>
                            </b-popover>
                        </div>
                        <div v-if="hotel.summary != null && hotel.summary != ''">
                            <h4 class="blue" style="cursor:pointer;" id="summary">{{ trans('hotel.label.notes') }}</h4>
                    <b-popover
                        target="summary"
                        title="{{ trans('hotel.label.notes') }}"
                        trigger="click">
                        <div
                            class="d-block my-2" v-html="hotel.summary.replace(/\n/g, '<br>')"
                            style="font-size: 13px; line-height: 16px;">
                        </div>
                    </b-popover>
                </div>
                --}}

                <div v-for="(room, rkey) in hotel.rooms" v-if="room.rates.length>0 && room.countCalendars>0">
                    {{-- &&
                                  ( ( room.occupation===1 && service_selected_general.single>0 ) ||
                                    ( room.occupation===2 && service_selected_general.double>0 ) ||
                                    ( room.occupation===3 && service_selected_general.triple>0 ) )   --}}


                    <div class="rooms-table row canSelectText">
                        <div class="col-4 my-auto">
                            <strong>{{ trans('quote.label.name') }}: </strong>@{{
                                    room.name }}<br>

                        </div>
                        <div class="col-8 my-auto">
                            <div v-for="(rate, raKey) in room.rates"
                                :class="'col-12 rateRow rateChoosed_' + checkboxs[ hotel.id + '_' + rate.rateId ]"
                                v-if="(rate.rate[0].amount_days.length > 0) && (rate.promotions_data.length>0 ? (getPromotionsData(rate.promotions_data ,1,hotel) && getPromotionsData(rate.promotions_data ,2,hotel)) :true  ) ">
                                {{-- --}}
                                <label style="display: block;"
                                    :for="'checkbox_' + hotel.id + '_' + rate.rateId">
                                    <strong>
                                        <span class="room-ok" v-if="rate.onRequest== 1">
                                            <i class="fa fa-check-circle green"></i>
                                        </span>
                                        <span class="room-rq" v-if="rate.onRequest!= 1">
                                            <i class="fa fa-times-circle red"></i>
                                        </span>
                                        <span v-if="rate.name!=null">
                                            <i @click="closeOthersPopovers(rate)"
                                                class="fa fa-info-circle blue-info"
                                                :id="'pop' + rate.rateId"></i>

                                            <b-popover class="canSelectText" :show.sync="rate.popShow"
                                                :target="'pop' + rate.rateId"
                                                title="{{ trans('hotel.label.policy_details') }}"
                                                triggers="hover focus">
                                                <div>
                                                    <table width="100%">
                                                        <tr class="tarifa_total_title">
                                                            <td colspan="5" align="justify">
                                                                <p class="mb-0">
                                                                    <b>{{ trans('hotel.label.general_policy') }}</b>
                                                                </p>
                                                                <p class="mb-0">
                                                                    Check-in: @{{ hotel.checkIn }} Check out : @{{ hotel.checkOut }}
                                                                </p>
                                                                <p class="mb-0" v-if="hotel.political_children"><b>{{ trans('hotel.label.political_children') }}</b>
                                                                </p>
                                                                <p class="mb-0"
                                                                    v-if="hotel.political_children && hotel.political_children.child && hotel.political_children.child.allows_child == 1">
                                                                    <b>{{ trans('hotel.label.children') }}</b>
                                                                    @{{
                                                                                hotel.political_children.child.min_age_child
                                                                                }} {{ trans('hotel.label.years') }} {{ trans('hotel.label.to') }} @{{
                                                                                hotel.political_children.child.max_age_child
                                                                                }} {{ trans('hotel.label.years') }} @{{
                                                                                rate.political && rate.political.no_show_apply ? rate.political.no_show_apply.political_child : ''
                                                                                }}
                                                                </p>
                                                                <p class="mb-0"
                                                                    v-if="hotel.political_children && hotel.political_children.infant && hotel.political_children.infant.allows_teenagers == 1">
                                                                    <b>{{ trans('hotel.label.infants') }}</b>
                                                                    @{{
                                                                                hotel.political_children.infant.min_age_teenagers
                                                                                }} {{ trans('hotel.label.years') }} {{ trans('hotel.label.to') }} @{{
                                                                                hotel.political_children.infant.max_age_teenagers
                                                                                }} {{ trans('hotel.label.years') }} @{{
                                                                                rate.political && rate.political.no_show_apply ? rate.political.no_show_apply.political_child : ''
                                                                                }}
                                                                </p>
                                                                <p class="policies-text mb-0"
                                                                    v-if="rate.supplements.supplements.length > 0">
                                                                    <b>{{ trans('hotel.label.additional_required') }}</b>
                                                                </p>
                                                                <ul style="list-style-type:circle"
                                                                    v-if="rate.supplements.supplements.length > 0">
                                                                    <li class="ml-5"
                                                                        v-for="(supplement) in rate.supplements.supplements">
                                                                        @{{ supplement.supplement }}
                                                                    </li>
                                                                </ul>
                                                                <p class="mb-0">
                                                                    No Show: @{{ rate.no_show }}
                                                                </p>
                                                                <p class="mb-0">
                                                                    Day Use: @{{ rate.day_use }}
                                                                </p>
                                                                <p v-if="rate.notes != undefined && rate.notes != '' && user_type_id == 3"
                                                                    class="mb-0">
                                                                    <b>{{ trans('hotel.label.notes') }}</b>
                                                                </p>
                                                                <p class="mb-0"
                                                                    v-if="rate.notes != undefined && rate.notes != '' && user_type_id == 3"
                                                                    style="white-space: pre-wrap;">
                                                                    @{{ rate.notes }}
                                                                </p>
                                                                <p class="policies-title-sec mb-0">
                                                                    <b>{{ trans('hotel.label.political_cancellation') }}</b>
                                                                </p>
                                                                <p>@{{ rate.political.cancellation.name }}</p>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </b-popover>

                                            @{{ rate.name }} <?php if (Auth::user()->user_type_id == 3): ?>(@{{ rate.name_commercial }})<?php endif; ?>:
                                        </span>
                                        <span v-if="rate.promotions_data.length>0">({{ trans('hotel.label.booking_window') }}: @{{ getPromotionsData(rate.promotions_data ,1,hotel)}} - @{{ getPromotionsData(rate.promotions_data ,2,hotel) }})</span>
                                        <span v-if="rate.name==null">---</span>
                                    </strong>
                                    <input type="checkbox" style="float: right;"
                                        :id="'checkbox_' + hotel.id + '_' + rate.rateId"
                                        :name="'checkbox_' + hotel.id + '_' + rate.rateId"
                                        v-model="checkboxs[ hotel.id + '_' + rate.rateId]"
                                        :disabled="loading">
                                </label>
                                <div style="margin-left: 30px;">
                                    @{{ rate.rate[0].amount_days[0].date | formatDate }}
                                    <strong :class="{ 'room-rq': rate.price_dynamic == 1 }">$ <span v-if="rate.rate[0].amount_days[0]">
                                            @{{ rate.price_dynamic == 1 ? 0 : rate.rate[0].amount_days[0].total_amount }} </span>
                                    </strong>
                                    <span v-if="rate.rate[0].amount_days.length>1">
                                        <a href="javascript:;" v-show="!(rate.showAllRates)"
                                            @click="toggleViewRates(rate)">
                                            <i class="fa fa-plus"></i></a>
                                        <a href="javascript:;" v-show="rate.showAllRates"
                                            @click="toggleViewRates(rate)"><i
                                                class="fa fa-minus"></i></a>
                                    </span>
                                </div>
                                <div style="margin-left: 30px;"
                                    v-for="( calendar, cKey) in rate.rate[0].amount_days"
                                    v-show="rate.showAllRates">
                                    <span v-if="cKey > 0">
                                        @{{ calendar.date | formatDate }}
                                        <strong>$ <span v-if="calendar.rate[0]">
                                                @{{ calendar.total_amount }} </span>
                                        </strong>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr v-if="(rkey + 1 ) < hotel.rooms.length">
                </div>
            </div>
        </div>

    </div>

    </div>

    <div class="form-group cotizacion-crear--boton justify-content-center"
        v-if="moreHotels.length>0">
        <button class="btn btn-primary" type="button" @click="addHotel()" :disabled="loading">
            <i class="fa fa-spin fa-spinner" v-if="loading"></i>
            <span v-if="!loading" v-show="!check_replace_hotel">
                <i class="icon-plus"></i>
                {{ trans('quote.label.add') }}
            </span>
            <span v-if="!loading" v-show="check_replace_hotel">
                <i class="icon-edit-3"></i>
                {{ trans('quote.label.replace') }}
            </span>
        </button>
    </div>

    <div slot="modal-footer">

    </div>
</b-modal>

{{-- modal-vuelos --}}
<div class="modal fade modal-flight" id="modal-flight" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-label="Close" class="close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <h3><i class="icon-grid mr-2"></i>{{ trans('quote.label.add_flights') }}</h3>
                </div>
                <hr>

                <div class="mt-4" style="text-align: right;overflow: hidden;" v-if="!editService">
                    <button :class="'btn categoria check_' + qCateg.checkAddService"
                        v-for="qCateg in quote_open.categories"
                        @click="toggleCategoryCheckAddService(qCateg)">
                        <i class="far fa-square" v-if="!(qCateg.checkAddService)"></i>
                        <i class="fa fa-check-square" v-if="qCateg.checkAddService"></i>
                        @{{ qCateg.type_class.translations[0].value }}
                    </button>
                </div>
                <div class="form-content">
                    <form class="form">
                        <div class="form-row form-inline mb-2">
                            <div class="form-group form-check">
                                <input type="radio" class="form-check-input" v-on:change="resetDestinations()"
                                    v-model="flight_type"
                                    name="flight_type" value="0" id="nacional" />
                                <label class="form-check-label"
                                    for="nacional">{{ trans('flights.label.national') }}</label>
                            </div>
                            <div class="form-group form-check">
                                <input type="radio" class="form-check-input" v-on:change="resetDestinations()"
                                    v-model="flight_type"
                                    name="flight_type" value="1" id="internacional" />
                                <label class="form-check-label"
                                    for="internacional">{{ trans('flights.label.international') }}</label>
                            </div>
                        </div>
                        <div class="form-row d-flex justify-content-between align-items-end">
                            <div class="form-group">
                                <label for="">{{ trans('quote.label.origin') }}:</label>
                                <v-select class="form-control destino"
                                    :options="destinations_flights_origin"
                                    :value="codciu"
                                    label="codciu" :filterable="false" @search="searchDestinationsOrigin"
                                    placeholder="{{ trans('quote.label.localte_a_origin') }}"
                                    v-model="originModalFlight" style="padding-top: 5px;">
                                    <template slot="option" slot-scope="option">
                                        <div class="d-center">
                                            @{{ option.ciudad }}, @{{ option.pais }}
                                        </div>
                                    </template>
                                    <template slot="selected-option" slot-scope="option">
                                        <div class="selected d-center">
                                            @{{ option.ciudad }} - @{{ option.pais }}
                                        </div>
                                    </template>
                                </v-select>
                            </div>
                            <div class="form-group">
                                <label for="">{{ trans('quote.label.destiny') }}:</label>
                                <v-select class="form-control destino"
                                    :options="destinations_flights_destiny"
                                    :value="codciu"
                                    label="codciu" :filterable="false" @search="searchDestinationsDestiny"
                                    placeholder="{{ trans('quote.label.locate_a_destination') }}"
                                    v-model="destinationsModalFlight" style="padding-top: 5px;">
                                    <template slot="option" slot-scope="option">
                                        <div class="d-center">
                                            @{{ option.ciudad }}, @{{ option.pais }}
                                        </div>
                                    </template>
                                    <template slot="selected-option" slot-scope="option">
                                        <div class="selected d-center">
                                            @{{ option.ciudad }} - @{{ option.pais }}
                                        </div>
                                    </template>
                                </v-select>
                            </div>
                            <div class="form-group">
                                <label>{{ trans('quote.label.date') }}:</label>
                                <date-picker class="date form-control" v-model="add_flight_date"
                                    :config="optionsR"></date-picker>
                            </div>
                            <div class="form-group cotizacion-crear--boton">
                                <button class="btn btn-primary" type="button" @click="addFlight()"
                                    :disabled="loading">
                                    <i class="fa fa-spin fa-spinner" v-if="loading"></i>
                                    <span v-if="!loading" v-show="!check_replace_hotel">
                                        <i class="icon-plus"></i>
                                        {{ trans('quote.label.add') }}
                                    </span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- modal-servicios         --}}
<div class="modal fade modal-servicios" id="modal-servicios" aria-hidden="true" v-on:click="validModal()">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-label="Close" class="close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <ul class="list-group scrollbar-outer mt-4" v-if="similar_services.length>0 && editService">
                    <li class="list-group-item" v-for="similar_category in similar_services">
                        <h4 class="m-0">@{{ similar_category.category_name }}</h4>
                        <label class="lbl-similar" v-for="similar_service in similar_category.services">
                            <input type="checkbox" v-model="similar_service.check"> @{{ similar_service.date }}
                            - @{{ similar_service.code }}
                        </label>
                    </li>
                </ul>
                <div class="mb-2">
                    <h3><i class="icon-grid mr-2"></i>{{ trans('quote.label.add_services') }}</h3>
                </div>
                <hr>
                <div class="mt-4" style="text-align: right;overflow: hidden;" v-if="!editService">
                    <button :class="'btn categoria check_' + qCateg.checkAddService"
                        v-for="qCateg in quote_open.categories"
                        @click="toggleCategoryCheckAddService(qCateg)">
                        <i class="far fa-square" v-if="!(qCateg.checkAddService)"></i>
                        <i class="fa fa-check-square" v-if="qCateg.checkAddService"></i>
                        @{{ qCateg.type_class.translations[0].value }}
                    </button>
                </div>

                <div class="form-content">
                    <form class="form">

                        <div class="form-row d-flex justify-content-between align-items-end">
                            <div class="form-group ">
                                <label for="">{{ trans('quote.label.country') }}:</label>
                                <v-select :options="originModalService_countries_select"
                                    @input="change_service_origin_cities()"
                                    v-model="originModalService_country" placeholder=""
                                    class="form-control destino"></v-select>
                            </div>
                            <div class="form-group ">
                                <label for="">{{ trans('quote.label.origin') }}:</label>
                                <v-select :options="originModalService_select"
                                    @input="change_service_origin_districts()"
                                    v-model="originModalService"
                                    placeholder="{{ trans('quote.label.localte_a_origin') }}"
                                    class="form-control destino"></v-select>
                            </div>
                            <div class="form-group ">
                                <label v-for="district in originModalService_additional_select"
                                    class="tag-districts">
                                    <input type="radio" :value="district.code"
                                        v-model="originModalService_district"
                                        :name="'radio_service_origin_'+district.parent_code">
                                    @{{ district.label }}
                                </label>
                            </div>
                        </div>

                        <div class="form-row d-flex justify-content-between align-items-end">
                            <div class="form-group ">
                                <label for="">{{ trans('quote.label.country') }}:</label>
                                <v-select :options="destinationsModalService_countries_select"
                                    @input="change_service_destiny_cities()"
                                    v-model="destinationsModalService_country" placeholder=""
                                    class="form-control destino"></v-select>
                            </div>
                            <div class="form-group ">
                                <label for="">{{ trans('quote.label.destiny') }}:</label>
                                <v-select :options="destinationsModalService_select"
                                    @input="change_service_destiny_districts()"
                                    v-model="destinationsModalService"
                                    placeholder="{{ trans('quote.label.locate_a_destination') }}"
                                    class="form-control destino"></v-select>
                            </div>
                            <div class="form-group ">
                                <label v-for="district in destinationsModalService_additional_select"
                                    class="tag-districts">
                                    <input type="radio" :value="district.code"
                                        v-model="destinationsModalService_district"
                                        :name="'radio_service_destiny_'+district.parent_code">
                                    @{{ district.label }}
                                </label>
                            </div>
                        </div>

                        <div class="form-row d-flex justify-content-between align-items-end">
                            <div class="form-group">
                                <label>{{ trans('quote.label.date') }}:</label>
                                <date-picker class="date form-control" v-model="add_service_date"
                                    :config="optionsR"></date-picker>
                            </div>
                            <div class="form-group">
                                <label>{{ trans('quote.label.category') }}:</label>
                                <select class="form-control categoria"
                                    v-model="categoryModalService" id="_categoryModalService">
                                    <option value="" selected>{{ trans('quote.label.all') }}</option>
                                    <option v-for="qCateg in service_categories" :value="qCateg.id">
                                        @{{ qCateg.translations[0].value }}
                                    </option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>{{ trans('quote.label.type_service') }}:</label>
                                <select type="text" class="form-control tipo-servicio"
                                    name="modal_service_type_id" v-model="modal_service_type_id"
                                    id="_modal_service_type_id">
                                    <option value="" selected>{{ trans('quote.label.all') }}</option>
                                    <option :value="service_type.id" v-for="service_type in service_types">
                                        @{{ service_type.translations[0].value }} - @{{ service_type.code }}
                                    </option>
                                </select>
                            </div>
                            <div class="form-group mt-2">
                                <label for="">{{ trans('quote.label.filter_by_word') }}:</label>
                                <input type="text" class="form-control filter2" id="palabra"
                                    placeholder="{{ trans('quote.label.filter_by_word') }}..."
                                    v-model="add_service_words" @input="willFilterTextS">
                                <b-popover class="canSelectText" target="palabra" triggers="hover">
                                    {{ trans('quote.messages.you_can_multiple_search') }}
                                </b-popover>
                            </div>
                            <div class="form-group mt-2">
                                <label>{{ trans('quote.label.number_of_adults') }}:</label>
                                <select type="text" class="form-control tipo-servicio"
                                    name="modal_service_number_of_guests" v-model="modal_service_number_of_guests"
                                    id="_modal_service_number_of_guests">
                                    <option :value="i" v-for="i in 20">
                                        @{{ i }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <div class="form-group mt-0">
                                <label class="checkbox-inline checkbox"
                                    v-show="wordsS.length >= 2 && wordsS[1] != ''">
                                    <input type="checkbox" class="edit"
                                        v-model="allWordsS"> {{ trans('quote.messages.results_containing_the') }}
                                    @{{wordsS.length}} {{ trans('quote.label.words') }}.
                                </label>
                            </div>
                        </div>
                        <div class="form-row d-flex justify-content-between align-items-end">
                            <div class="form-group cotizacion-crear--boton mt-2">
                                <a class="clean" type="button" @click="cleanServiceFilters()"
                                    :disabled="loading">
                                    <i class="fas fa-magic mr-2"></i>
                                    {{ trans('quote.label.clean') }}
                                </a>
                            </div>
                            <div class="form-group cotizacion-crear--boton">
                                <button class="btn btn-primary" type="button" @click="searchServices(true)"
                                    :disabled="loading">
                                    <i class="fa fa-spin fa-spinner" v-if="loading"></i>
                                    <i class="icon-search" v-if="!loading"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <hr>
                <div class="table-responsive table-results">
                    <div v-if="moreServices.length==0" class=" none-result text-center">
                        <i class="icon-cloud-off"></i> {{ trans('quote.label.no_results') }}.
                    </div>

                    <table class="table" v-if="moreServices.length>0">
                        <thead>
                            <tr>
                                <th scope="col" class="">{{ trans('quote.label.rate_code') }}</th>
                                <th scope="col" class="">{{ trans('quote.label.rate_description') }}</th>
                                <th scope="col" class="" style="width: 80px;">{{ trans('quote.label.rate') }}</th>
                                <th scope="col" class="">{{ trans('quote.label.rate_action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="mS in moreServices">
                                <td>@{{ mS.aurora_code }}</td>
                                <td>
                                    @{{ mS.service_translations[0].name }}
                                    <b v-if="mS.service_type.code==='SIM' || mS.service_type.code==='PC'">
                                        - [@{{ mS.service_type.translations[0].value }}].
                                    </b>
                                    <div>
                                        <ul class="d-flex justify-content-around">
                                            <li class="icon-results">
                                                <i class="fas fa-tasks mr-1"></i>
                                                <a class="link-tablecursor-pointer" href="javascript:;"
                                                    @click="openModalDetail(mS.id,'inclusions',add_service_date,1,0)">
                                                    {{trans('service.label.includes_not_include')}}
                                                </a>
                                            </li>
                                            <li class="icon-results">
                                                <i class="fas fa-list mr-1"></i>
                                                <a class="link-tablecursor-pointer" href="javascript:;"
                                                    @click="openModalDetail(mS.id,'itinerary',add_service_date,1,0)">
                                                    {{trans('service.label.itinerary')}}
                                                </a>
                                            </li>
                                            <li class="icon-results">
                                                <i class="far fa-clock mr-1"></i>
                                                <a class="link-table cursor-pointer" href="javascript:;"
                                                    @click="openModalDetail(mS.id,'schedule',add_service_date,1,0)">
                                                    {{trans('service.label.schedules_restrictions')}}
                                                </a>
                                            </li>
                                            <li class="icon-results"
                                                v-if="mS.notes != null && mS.notes != ''   && user_type_id == 3">
                                                <a class="mr-1 link-table cursor-pointer"
                                                    data-toggle="modal" href="#modal-notes"
                                                    @click="service_notes=mS.notes">
                                                    Remarks
                                                </a>
                                            </li>
                                        </ul>
                                    </div>

                                </td>
                                <td>
                                    <label v-for="(sR, sR_k) in mS.service_rate">
                                        <input type="radio" :name="'r_'+mS.id+'_'+sR.id" :value="sR.id"
                                            v-model="mS.rateChoosed"> @{{ sR.name }}
                                        <span v-for="s_r_plan in sR.service_rate_plans"
                                            v-if="(quantity_persons.adults + quantity_persons.child) >= s_r_plan.pax_from &&
                                                    (quantity_persons.adults + quantity_persons.child) <= s_r_plan.pax_to">
                                            <span :style="sR.price_dynamic == 1 ? 'color:red' : ''">
                                                $@{{ sR.price_dynamic == 1 ? 0 : s_r_plan.price_adult_label }}
                                            </span>
                                        </span>
                                        <span class="badge badge-success" v-if="sR.inventory_count > 0">OK</span>
                                        <span class="badge badge-danger" v-if="sR.inventory_count == 0">RQ</span>
                                        <br v-if="sR_k>0">
                                    </label>

                                </td>
                                <td>
                                    <div class="d-flex justify-content-center">
                                        <button class="btn btn-primary mx-2" type="button" @click="addService(mS)"
                                            title="Agregar"
                                            :disabled="loading">
                                            <i class="icon-plus"></i>
                                        </button>
                                        <button v-if="editService" class="btn btn-primary mx-2" type="button"
                                            title="Reemplazar"
                                            @click="replaceService(mS)"
                                            :disabled="loading">
                                            <i class="fas fa-exchange-alt"></i>
                                        </button>
                                    </div>

                                </td>
                            </tr>
                        </tbody>

                    </table>
                    <hr>
                    <nav aria-label="page navigation" v-if="moreServices.length>0">
                        <ul class="pagination">
                            <li :class="{'page-item':true,'disabled':(modal_services_pageChosen==1)}"
                                @click="modal_services_setPage(modal_services_pageChosen-1)">
                                <a class="page-link" href="#">Anterior</a>
                            </li>

                            <li v-for="page in modal_services_pages" @click="modal_services_setPage(page)"
                                :class="{'page-item':true,'active':(page==modal_services_pageChosen) }">
                                <a class="page-link" href="javascript:;">@{{ page }}</a>
                            </li>

                            <li :class="{'page-item':true,'disabled':(modal_services_pageChosen==modal_services_pages.length)}"
                                @click="modal_services_setPage(modal_services_pageChosen+1)">
                                <a class="page-link" href="#">Siguiente</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- End modal-servicios       --}}

{{-- modal de Extensiones      --}}
<div class="modal fade modal-extensiones" id="modal_extensions" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-label="Close" class="close"
                    id="close_modal_extension" @click="closeModalExtension">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <h3><i class="icon-grid mr-2"></i>{{ trans('quote.label.add_extensions') }}</h3>
                <hr>

                <div class="row align-items-center mt-3">
                    <div class="col text-left">
                        <div class="d-flex align-items-center">
                            <input type="checkbox" id="package_extension" class="mr-2" v-model="package_extension"
                                :true-value="1"
                                :false-value="0">
                            <label for="package_extension" class="mb-0">{{ trans('package.label.packages') }}</label>
                        </div>
                    </div>
                    <div class="col text-right">
                        <button :class="'btn categoria check_' + qCateg.checkAddExtension"
                            v-for="qCateg in quote_open.categories"
                            @click="toggleCategoryCheckAddExtension(qCateg)">
                            <i class="far fa-square" v-if="!(qCateg.checkAddExtension)"></i>
                            <i class="fa fa-check-square" v-if="qCateg.checkAddExtension"></i>
                            @{{ qCateg.type_class.translations[0].value }}
                        </button>
                    </div>
                </div>

                <div class="form-content">
                    <form class="form form-extensions">
                        <div class="form-row d-flex justify-content-between align-items-end">
                            <div class="form-group date">
                                <label for="">{{ trans('quote.label.date') }}:</label>
                                <date-picker class="date form-control" v-model="add_extensions_date"
                                    :config="optionsR"></date-picker>
                            </div>
                            <div class="form-group">
                                <select name="service_type_id" v-model="service_type_id"
                                    class="form-control servicio">
                                    <option :value="service_type.id" v-for="service_type in service_types">
                                        @{{ service_type.translations[0].value }} - @{{ service_type.code }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row d-flex justify-content-between align-items-end">
                            <div class="form-group mt-2">
                                <label for="">{{ trans('quote.label.filter_by_word') }}:</label>
                                <input type="text" class="form-control filter"
                                    placeholder="{{ trans('quote.label.filter_by_word') }}..."
                                    v-model="add_extension_words">
                                {{-- <input type="text" class="form-control filter" id="extension_word" placeholder="{{ trans('quote.label.filter_by_word') }}..."--}}
                                {{-- v-model="add_extension_words" @input="willFilterTextE">--}}
                            </div>


                            <div class="form-group btn-extensions">
                                <a class="clean" type="button" @click="cleanExtensionFilters()"
                                    :disabled="loading_extension">
                                    <i class="fas fa-magic mr-2"></i>
                                    {{ trans('quote.label.clean') }}
                                </a>
                            </div>
                            <div class="form-group btn-extensions">
                                <button class="btn btn-primary" type="button" @click="getExtensions"
                                    :disabled="loading_extension">
                                    <i class="fa fa-spin fa-spinner" v-if="loading_extension"></i>
                                    <i class="icon-search" v-if="!loading_extension"></i>
                                </button>
                            </div>

                        </div>
                        <hr v-if="extensions.length>0">
                        <div class="form-row d-flex justify-content-between align-items-end mx-3"
                            v-if="extensions.length>0">
                            <div class="dropdown filtro-group">
                                <a class="filter-extensiones" href="#" role="button" id="dropdownMenuLink2"
                                    data-toggle="dropdown"
                                    aria-haspopup="false" aria-expanded="false">
                                    <i class="btn-icon fas fa-tag mr-2"></i> {{trans('package.label.experiences')}}
                                </a>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuLink2"
                                    style="top: 20px;">
                                    {{-- <div class="subtitulo">{{trans('package.label.experiences')}}
                                </div> --}}

                                <div class="form-group">
                                    <label class="form-check form-check-all">
                                        <input class="form-check-input" type="checkbox"
                                            v-model="check_status_all" @change="filterByCategoryAll"
                                            checked>
                                        <span>{{trans('package.label.all_the_experiences')}}</span>
                                    </label>
                                    <label class="form-check"
                                        v-for="(category,index_category) in filter_by_category">
                                        <input class="form-check-input" type="checkbox" :id="index_category"
                                            v-model="category.status" @change="filterByCategory">
                                        <span>@{{ category.tag_name }} <small>(@{{ category.count }})</small></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <!--Filtro por Zonas de Hotel-->
                        <div class="dropdown filtro-group">
                            <a class="filter-extensiones" href="#" role="button" id="dropdownMenuLink3"
                                data-toggle="dropdown"
                                aria-haspopup="false" aria-expanded="false"
                                title="{{trans('package.label.destinations')}}">
                                <i class="btn-icon fas fa-map-marker-alt mr-2"></i>
                                {{trans('package.label.destinations')}}
                            </a>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuLink3">
                                {{-- <div class="subtitulo">{{trans('package.label.destinations')}}
                            </div> --}}
                            <div class="form-group">
                                <label class="form-check form-check-all">
                                    <input class="form-check-input" type="checkbox"
                                        id="all_destinations"
                                        @change="filterByDestinyAll()" checked>
                                    <span>{{trans('package.label.all_destinations')}}</span>
                                </label>
                                <label class="form-check"
                                    v-for="(destiny,index_destiny) in filter_by_destiny">
                                    <input class="form-check-input" type="checkbox" :id="index_destiny"
                                        v-model="destiny.status"
                                        @change="filterByDestiny(index_destiny)">
                                    <span>@{{ destiny.name }} <small>(@{{ destiny.count }})</small></span>
                                </label>
                            </div>
                        </div>
                </div>
                {{-- <!--Filtro por Duracion-->--}}
                <div class="dropdown filtro-group">
                    <a class="filter-extensiones" href="#" role="button" id="dropdownMenuLink1"
                        data-toggle="dropdown" aria-haspopup="false"
                        aria-expanded="true" title="{{trans('package.label.duration')}}">
                        <i class="btn-icon fas fa-moon mr-2"></i>
                        {{trans('package.label.duration')}}
                    </a>
                    <div id="dropdownMenuLink1Container" class="dropdown-menu"
                        aria-labelledby="dropdownMenuLink1">
                        {{-- <div class="subtitulo">{{trans('package.label.duration')}}
                    </div> --}}
                    <div class="form-group">
                        <label class="form-check form-check-all">
                            <input class="form-check-input" type="checkbox"
                                id="all_itineraries" @change="filterByNightsAll()" checked>
                            <span>{{trans('package.label.all_itineraries')}}</span>
                        </label>
                        <div v-for="(option,index_nights) in filter_by_nights">
                            <label class="form-check"
                                v-show="option.option == 1 && option.count > 0">
                                <input class="form-check-input" type="checkbox"
                                    v-model="option.status"
                                    @change="filterByNights(index_nights)"
                                    :id="index_nights">
                                <span>{{trans('package.label.up_to_3_nights')}} <small>(@{{ option.count }})</small></span>
                            </label>
                            <label class="form-check"
                                v-show="option.option == 2 && option.count > 0">
                                <input class="form-check-input" type="checkbox"
                                    v-model="option.status"
                                    @change="filterByNights(index_nights)"
                                    :id="index_nights">
                                <span>{{trans('package.label.4_to_6_nights')}} <small>(@{{ option.count }})</small></span>
                            </label>
                            <label class="form-check"
                                v-show="option.option == 3 && option.count > 0">
                                <input class="form-check-input" type="checkbox"
                                    v-model="option.status"
                                    @change="filterByNights(index_nights)"
                                    :id="index_nights">
                                <span>{{trans('package.label.7_to_10_nights')}} <small>(@{{ option.count }})</small></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </form>
    </div>
    <hr>
    <div class="row align-items-start mt-2">
        <div class="col-4" v-for="(extension,index) in extensions">
            <div class="card-carousel--card">
                <div class="image">
                    <img v-if="extension.galleries.length > 0" :src="extension.galleries[0].url"
                        class="object-fit_cover" />
                    <img class="object-fit_cover"
                        src="https://res.cloudinary.com/litodti/image/upload/v1722636098/packages/632/gallery/Uros_02.png"
                        v-else>
                    <div class="details">
                        <div class="tag" v-if="extension.recommended == 1"><i
                                class="icon-thumbs-up"></i>
                            {{ trans('quote.label.recommended') }}
                        </div>
                        <div class="container">
                            <div class="row info">
                                <div class="col-12">
                                    @{{ extension.nights + 1 }}D/@{{ extension.nights }}N
                                    <div class="city">
                                        @{{ extension.translations[0].name }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    class="card-carousel--card--footer d-flex align-items-center justify-content-between"
                    v-if="extension.destinations">
                    <div class="d-flex">
                        <i class="icon-map-pin"></i> @{{ extension.destinations }}
                    </div>
                </div>
                <div>
                    <div class="card card-body">
                        <p class="text-left">
                            <strong>{{ trans('quote.label.select_category') }}:</strong>
                        </p>
                        <div v-if="extension.plan_rates[0] && extension.plan_rates[0].plan_rate_categories">
                            <div v-for="categ in extension.plan_rates[0].plan_rate_categories">
                                <div class="form-check">
                                    <input type="radio" v-model="type_class_ids[index]"
                                        @change="extensionSelected(extension.id,categ.type_class_id)"
                                        :value="extension.id +'_'+categ.type_class_id"
                                        :id="extension.id +'_'+ categ.type_class_id"
                                        :name="extension.id">
                                    <label class="form-check-label" for="defaultCheck1">
                                        @{{ categ.category.translations[0].value }}
                                    </label>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive table-results mt-3">
        <div v-if="extensions.length == 0 && !loading_extension" class=" none-result text-center">
            <i class="icon-cloud-off"></i> {{ trans('quote.label.no_results') }}.
        </div>
        <div v-if="loading_extension" class=" none-result text-center">
            <i class="fa fa-spin fa-spinner"></i> {{ trans('quote.label.loading') }}
        </div>

        <div class="row m-5 justify-content-center" v-if="extensions.length>0">
            <div class="text-center">
                <button class="btn btn-primary" @click="addExtension" v-if="extension_replace ==null"
                    style="width: 250px;" :disabled="loading">
                    <i class="fa fa-spin fa-spinner" v-show="loading"></i>
                    <span v-show="!loading">{{ trans('quote.label.add') }}</span>

                </button>

                {{-- <button class="btn btn-primary" @click="replaceExtension"
                                        v-if="extension_replace != null && type_class_id != null">{{ trans('quote.label.replace') }}
                </button> --}}
                {{-- <button class="btn btn-danger" @click="closeModalExtension">@{{ extension_type_class_replace }} / @{{ extension_selected }} - @{{ type_class_id }}</button>--}}
            </div>

        </div>
    </div>
</div>
</div>
</div>
</div>
{{-- End modal de Extensiones      --}}

{{-- modal-edit-plan-rooms          --}}
<b-modal class="modal fade modal_cotizar" id="modal-edit-plan-rooms" ref="modal-edit-plan-rooms" size="lg"
    aria-hidden="true" :no-close-on-backdrop="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div>
                <div class="mb-2">
                    <h3><i class="icon-grid mr-2"></i> @{{ title_rates_hotel }}</h3>
                </div>
                <hr>

                <div v-if="message_edit_plan_rooms!=''" class="alert alert-warning-quote">
                    @{{ message_edit_plan_rooms }}
                </div>

                <div v-if="hotelSwapRates.length==0 && !loadingModal" class=" none-result text-center">.
                    <i class="icon-cloud-off"></i> {{ trans('quote.label.no_results_rates_hotel') }}.
                </div>

                <div v-if="loadingModal" class=" none-result text-center">.
                    <i class="fa fa-spinner fa-spin fa-2x"></i>
                </div>

                <div :class="'col-12 row_' + (rkey%2)" v-for="(room, rkey) in hotelSwapRates.rooms"
                    v-if="room.rates.length > 0 && room.countCalendars>0 && !loadingModal &&
                              ( ( room.occupation===1 && hotel_swap_single>0 ) ||
                                ( room.occupation===2 && hotel_swap_double>0 ) ||
                                ( room.occupation===3 && hotel_swap_triple>0 ) )">

                    <div class="rooms-table row canSelectText">
                        <div class="col-6 my-auto">
                            <strong>{{ trans('quote.label.name') }}: </strong>@{{ room.name }}<br>
                        </div>
                        <div class="col-6 my-auto">
                            <div v-for="(rate, raKey) in room.rates"
                                :class="'col-12 rateRow rateChoosed_' + edit_checkboxs[ '_' + rate.rateId ]"
                                v-if="rate.rate[0].amount_days.length > 0">

                                <label style="display: block;" :for="'checkbox_' + rkey + '_' + raKey">
                                    <strong>
                                        <i @click="closeOthersPopovers(rate)"
                                            class="fa fa-info-circle blue-info" :id="'pop' + rate.rateId"></i>

                                        <b-popover class="canSelectText" :show.sync="rate.popShow"
                                            :target="'pop' + rate.rateId"
                                            title="{{ trans('hotel.label.policy_details') }}"
                                            triggers="hover focus">
                                            <div>
                                                <table width="100%">
                                                    <tr class="tarifa_total_title">
                                                        <td colspan="5" align="justify">
                                                            <p>
                                                                <b>@{{ rate.political.rate ? rate.political.rate.name : '' }}</b>
                                                            </p>
                                                            <p>
                                                                Check-in: @{{ hotelSwapRates.checkIn }} Check
                                                                out : @{{ hotelSwapRates.checkOut }}
                                                            </p>
                                                            <p v-if="hotelSwapRates.political_children">
                                                                <b>{{ trans('hotel.label.political_children') }}</b>
                                                            </p>
                                                            <p v-if="hotelSwapRates.political_children && hotelSwapRates.political_children.child && hotelSwapRates.political_children.child.allows_child == 1">
                                                                <b>{{ trans('hotel.label.children') }}</b>
                                                                @{{
                                                                    hotelSwapRates.political_children.child.min_age_child
                                                                    }} {{ trans('hotel.label.years') }} {{ trans('hotel.label.to') }}
                                                                @{{
                                                                    hotelSwapRates.political_children.child.max_age_child
                                                                    }} {{ trans('hotel.label.years') }} @{{
                                                                    rate.political && rate.political.no_show_apply ? rate.political.no_show_apply.political_child : ''
                                                                    }}
                                                            </p>
                                                            <p v-if="hotelSwapRates.political_children && hotelSwapRates.political_children.infant && hotelSwapRates.political_children.infant.allows_teenagers == 1">
                                                                <b>{{ trans('hotel.label.infants') }}</b>
                                                                @{{
                                                                    hotelSwapRates.political_children.infant.min_age_teenagers
                                                                    }} {{ trans('hotel.label.years') }} {{ trans('hotel.label.to') }}
                                                                @{{
                                                                    hotelSwapRates.political_children.infant.max_age_teenagers
                                                                    }} {{ trans('hotel.label.years') }} @{{
                                                                    rate.political && rate.political.no_show_apply ? rate.political.no_show_apply.political_child : ''
                                                                    }}
                                                            </p>
                                                            <p class="policies-text"
                                                                v-if="rate.supplements.supplements.length > 0">
                                                                <b>{{ trans('hotel.label.additional_required') }}</b>
                                                            </p>
                                                            <ul v-if="rate.supplements.supplements.length > 0">
                                                                <li style="color: #EB5757;"
                                                                    v-for="(supplement) in rate.supplements.supplements">
                                                                    @{{ supplement.supplement }}
                                                                </li>
                                                            </ul>
                                                            <p>
                                                                No Show: @{{ rate.no_show }}
                                                            </p>
                                                            <p>
                                                                Day Use: @{{ rate.day_use }}
                                                            </p>
                                                            <p v-if="rate.notes != undefined && rate.notes != '' && user_type_id == 3">
                                                                <b>{{ trans('hotel.label.notes') }}</b>
                                                            </p>
                                                            <p v-if="rate.notes != undefined && rate.notes != '' && user_type_id == 3"
                                                                style="white-space: pre-wrap;">
                                                                @{{ rate.notes }}
                                                            </p>
                                                            <p class="policies-title-sec">
                                                                <b>{{ trans('hotel.label.political_cancellation') }}</b>
                                                            </p>
                                                            <p>@{{ rate.political.cancellation.name }}</p>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </b-popover>

                                        <i class="fa fa-dot-circle"></i> @{{ rate.name }}:
                                    </strong>
                                    <span v-if="rate.promotions_data.length>0">({{ trans('hotel.label.booking_window') }} : @{{ getPromotionsData(rate.promotions_data ,1)}} - @{{ getPromotionsData(rate.promotions_data ,2) }})</span>

                                    <!-- :disabled="loading || (url_hotel_choose === 'addFromHeader' && edit_checkboxs[ '_' + rate.rateId])" -->
                                    <b-form-checkbox style="float: right;"

                                        :id="'edit_checkboxs_' + rkey + '_' + raKey"
                                        :name="'edit_checkboxs_' + rkey + '_' + raKey"
                                        v-model="edit_checkboxs[ '_' + rate.rateId]"
                                        @change="chooseEditRoom( rate.rateId )">
                                    </b-form-checkbox>
                                    {{-- <a data-toggle="modal" href="#modal_politicas_cancelacion"--}}
                                    {{-- @click="setCancellationPolicies(rate.political.rate.message,rate.political.cancellation.name)">Ver--}}
                                    {{-- Politicas de Cancelacion</a>--}}
                                    <br>
                                    (@{{ rate.rateProvider }} - <?php if (Auth::user()->user_type_id == 3): ?>@{{
                                        rate.name_commercial }}<?php endif; ?>)
                                </label>

                                <div style="margin-left: 30px;">
                                    <span class="room-ok" v-if="rate.onRequest == 1">
                                        <i class="fa fa-check-circle"></i>
                                    </span>
                                    <span class="room-rq" v-if="rate.onRequest != 1">
                                        <i class="fa fa-times-circle"></i>
                                    </span>
                                    @{{ rate.rate[0].amount_days[0].date | formatDate }}
                                    <strong :class="{ 'room-rq': rate.price_dynamic == 1 }">
                                        $ <span v-if="rate.rate[0].amount_days[0]">
                                            @{{ rate.price_dynamic == 1 ? 0 : rate.rate[0].amount_days[0].total_amount }}
                                        </span>
                                    </strong>
                                    <span v-if="rate.rate[0].amount_days.length>1">
                                        <a href="javascript:;" v-show="!(rate.showAllRates)"
                                            @click="toggleViewRates(rate)">
                                            <i class="fa fa-plus"></i>
                                        </a>
                                        <a href="javascript:;" v-show="rate.showAllRates"
                                            @click="toggleViewRates(rate)">
                                            <i class="fa fa-minus"></i>
                                        </a>
                                    </span>
                                </div>
                                <div style="margin-left: 30px;"
                                    v-for="( calendar, cKey) in rate.rate[0].amount_days"
                                    v-show="rate.showAllRates">
                                    <span v-if="cKey > 0">
                                        @{{ calendar.date | formatDate }}
                                        <strong>$ <span
                                                v-if="calendar.rate[0]"> @{{ calendar.total_amount }} </span></strong>

                                    </span>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>
</b-modal>
{{-- End modal-edit-plan-rooms        --}}

{{-- modal Notes        --}}
<div class="modal fade" id="modal-notes" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-label="Close" class="close"
                    id="close-modal-notes"
                    @click="close_modal_notes">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">

                <div class="col-md-12">
                    <h2 class="modal-title">Remarks</h2>
                    <hr>
                    <div class="service-seleccion" v-if="service_notes != null && service_notes != ''">
                        <div style="white-space: pre-wrap;" v-html="service_notes"></div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<b-modal class="modal fade" id="modal_create_notes_hotel" ref="modal_create_notes_hotel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div>
                <div class="mb-2">
                    <h3>{{ trans('quote.label.notes_hotel') }}</h3>
                </div>
                <hr>
                <div class="mt-3">
                    <textarea type="text" class="form-control" rows="5" v-model="service_active.notes">
                        </textarea>
                </div>
                <div class="d-flex">
                    <button class="btn btn-cancelar col-6 mt-5 mr-1"
                        style="height: 52px !important;"
                        :disabled="loading_create_note"
                        @click="closeModalNotesHotel">
                        {{ trans('quote.label.cancel') }}
                    </button>
                    <button class="btn btn-primary col-6 mt-5"
                        :disabled="loading_create_note"
                        @click="saveNoteHotel">
                        <i class="fa fa-spin fa-spinner" v-if="loading_create_note"></i>
                        {{ trans('quote.label.save') }}
                    </button>
                </div>

            </div>
        </div>
    </div>
</b-modal>
{{-- End modal Notes       --}}

{{-- modal Real Notes        --}}
<div class="modal fade" id="modal-real-notes" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-label="Close" class="close"
                    id="close-modal-real-notes"
                    @click="close_modal_real_notes">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">

                <div class="col-md-12">
                    <h2 class="modal-title">{{trans('service.label.summary')}}</h2>
                    <hr>
                    <div class="service-seleccion"
                        v-if="service_real_notes != null && service_real_notes != ''">
                        <p v-html="service_real_notes"></p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
{{-- End modal Notes       --}}

{{-- modal Guardar Como         --}}
<b-modal class="modal fade" id="modal_guardar_como" ref="modal_guardar_como" aria-hidden="true"
    :no-close-on-backdrop="true" :no-close-on-esc="true" :hide-header-close="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div>
                <div class="mb-2">
                    <h3>{{ trans('quote.label.new_name_of_quote') }}</h3>
                </div>
                <hr>
                <div class="mt-3">
                    <label for="new_name_quote">{{ trans('quote.label.enter_the_new_name') }}:</label>
                    <input type="text" class="form-control" v-model="new_name_quote" />
                </div>
                <div class="d-flex">
                    <button class="btn btn-cancelar col-6 mt-5 mr-1"
                        style="height: 52px !important;"
                        :disabled="loading_save_as"
                        @click="closeModalSaveAs">
                        {{ trans('quote.label.cancel') }}
                    </button>
                    <button class="btn btn-primary col-6 mt-5"
                        :disabled="loading_save_as"
                        @click="saveAsQuote">
                        <i class="fa fa-spin fa-spinner" v-if="loading_save_as"></i>
                        {{ trans('quote.label.save') }}
                    </button>
                </div>

            </div>
        </div>
    </div>
</b-modal>
{{-- End modal Guardar Como       --}}


{{-- modal de Reserva       --}}
<b-modal class="modal-central modal-content" size="md" id="modal_reservation" aria-hidden="true"
    ref="modal_reservation" :no-close-on-backdrop="true" :no-close-on-esc="true" :hide-header-close="true">
    <div class="text-center mt-5">
        <h4 class="mb-5 size_title">
            <strong class="text-dark"> @{{ translations.label.tab_booking_details }} </strong>
        </h4>
        <div>
            <label for="file_reference" class="col size_title mb-4 color-inf">
                FILE: @{{ codeFile }}
            </label>
            <div class="mb-5" v-if="user_type_id == 3">
                <label for="orderNumber" class="col m-0 text-dark"> @{{ translations.label.order_number }}:
                    <span class="ml-2 color-inf">@{{ new_order_related }}</span>
                </label>
            </div>
        </div>
        <div>
            <label for="file_code" class="col-12 color-inf">
                <i class="fas fa-user"></i> @{{ translations.label.name_pax }}: <span class="text-dark">@{{ file.file_reference }}</span>
            </label>
        </div>
        <div>
            <label for="file_code" class="col-12 py-3 color-inf">
                <i class="fas fa-user-friends"></i>
                @{{ translations.label.number_pax }}:
                <span class="text-dark">@{{ quantity_persons.adults}} {{ trans('quote.label.adults') }}
                    <template v-if="quantity_persons.child > 0">
                        @{{ quantity_persons.child }} {{ trans('quote.label.child') }}
                    </template>
                </span>
            </label>
        </div>
        <div>
            <label for="file_reference" class="col-12 mb-5 color-inf">
                <i class="fas fa-user-tag"></i>
                @{{ translations.label.type_service }}:
                <span class="text-dark">@{{ getServiceType() }}</span>
            </label>
        </div>
    </div>


    <div class="mb-2" v-if="statements.type != undefined && statements.type == 'success'">
        <div class="row c-dark rounded justify-content-center mb-5 py-5 ml-0">
            <div>
                <label class="col-12 m-0 color-inf">
                    @{{ translations.label.cancellation_without_penalty }}:
                </label>
            </div>
            <span class="text-dark">@{{ getFormatDate(statements.min_date_cancellation) }}</span>
            <div class="row px-4 my-3">
                <div class="wrapper ml-4">
                    <input type="checkbox" v-on:change="saveReminder()" v-model="reminder.flag_send" id="check"
                        class="col-1" />
                    <label for="check" class="col m-0 ml-2 p-0 text-dark2 size_paragraph">
                        @{{ translations.label.send_reminder_email }}:
                    </label>
                </div>
                <div class="col-5 p-0">
                    <i v-on:click="changeDaysReminder('down')" class="text-dark3 fas fa-minus-circle"></i>
                    <input type="text" v-model="reminder.days" min="1" max="100"
                        value="1" class="col-3 p-0 text-center" v-on:change="saveReminder" />
                    <i v-on:click="changeDaysReminder('up')" class="text-dark2 fas fa-plus-circle"></i>
                    <span class="size_paragraph">@{{ translations.label.days_before }}</span>
                </div>
                <template v-if="reminder.flag_send">
                    <div class="row p-0 ml-4">
                        <input type="checkbox" id="check-mail" checked="checked" disabled class="col-auto" />
                        <label for="check-mail" class="col m-0 ml-2 p-0 text-dark3 size_paragraph">
                            @{{ statements.client.email }}
                        </label>
                    </div>
                    <div class="row p-0 ml-4">
                        <input type="checkbox" class="col-auto" v-model="reminder.flag_email"
                            v-on:change="toggleEmailReminder()" />
                        <input type="email" v-bind:disabled="!reminder.flag_email" v-model="reminder.email"
                            maxlenght="100" v-on:change="saveReminder()"
                            class="size-paragraph col m-1 p-1 border" />
                    </div>
                    <small class="text-danger" v-if="email_error">{{ trans('quote.label.email_invalid') }}</small>
                </template>
            </div>

            <div>
                <span> @{{ translations.label.please_reconfirm_reservation }}:
                    <a href="javascript:;" v-on:click="modalPassengers(quote_id, passengers.length)">
                        <u class="blue-link">@{{ translations.label.passenger_data }}</u>
                    </a>
                </span>
            </div>
        </div>

        <div class="row mx-0 mb-4 rounded warning align-items-start">
            <i class="col-1 mt-4 fas fa-exclamation-triangle"></i>
            <div class="col justify-content-center py-4 ml-0">
                <label class="m-0">
                    Si no recibiste la confirmación de tu reserva, puedes contactarte con el área de soport técnico
                    con el código:
                    <span>@{{ booking_code }}</span>
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
{{-- End modal de Reserva     --}}

{{-- End modal Editar       --}}
<div class="cotizacion-modals">
    <div class="modal fade modal-extensiones" id="modalEditar" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <h3 id="myLargeModalLabel" class="modal-title">
                        <strong>{{ trans('quote.label.edit_service') }}</strong>
                    </h3>
                    <center>...</center>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade modal-extensiones" id="modalVerDetalle" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <h3 id="myLargeModalLabel" class="modal-title">
                        <strong>{{ trans('quote.label.details_service') }}</strong>
                    </h3>
                    <center>...</center>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- modal Cerrar         --}}
<b-modal class="modalfade modal_cotizar" id="modal_close" aria-hidden="true" ref="modal_close"
    :no-close-on-backdrop="true" :no-close-on-esc="true">
    <template v-slot:modal-header="{close}">
        <div class="text-right w-100 align-self-center">
            <button type="button" class="align-items-center d-inline-flex" style="color: #fff" @click="close">
                {{ trans('hotel.label.Close') }} &nbsp;
                <span style="font-size: 2.5rem">&times;</span>
            </button>
        </div>
    </template>
    <div class="row m-0">
        <div class="col-12 mt-4">
            <h1 class="text-center">
                <i class="icon-alert-circle"></i>
            </h1>
            <h4 class="text-center font-weight-bold text-primary">
                ¿ {{ trans('quote.label.surely_you_want_to_close_without_saving_changes_to') }} "@{{
                    quote_open.name }}"?
            </h4>
            <div class="group-btn mt-5 text-center">
                <button class="btn btn-outline-danger mr-1" v-if="quote_id != null"
                    @click="discardChanges" :disabled="force_fully_destroy_loading"
                    style="padding: 0 30px;height: 52px;font-style: normal;font-weight: 500; font-size: 17px;">
                    <i class="fa fa-spin fa-spinner"
                        v-if="force_fully_destroy_loading"></i> {{ trans('quote.label.discard_changes') }}
                </button>
                <button class="btn btn-primary" v-if="quote_id != null"
                    :disabled="force_fully_destroy_loading"
                    @click="saveAndDiscardQuote">{{ trans('quote.label.save') }}</button>
            </div>
        </div>
    </div>
</b-modal>
{{-- End modal Cerrar       --}}
{{-- modal Cotizar         --}}
<b-modal class="modal fade modal_cotizar" id="modal_cotizar" ref="modal_cotizar" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div>
                <h4>@{{ translations.label.services_hotels_on_request }}</h4>
                <div v-for="qCateg in quote_open.categories">
                    <ul v-for="(service,index_service) in qCateg.services" v-if="qCateg.id ==categoryActive.id">
                        <li v-if="service.on_request == 1 && service.type=='service' && service.locked == false">
                            - [@{{ service.service.aurora_code }}] @{{
                                service.service.service_translations[0].name }}
                        </li>
                        <li v-if="service.on_request == 1 && service.locked == false && service.type==='group_header'">
                            - [@{{ service.hotel.channel[0].code }}] @{{ service.hotel.name }}
                        </li>
                    </ul>
                </div>
                <template v-if="!blockPage">
                    <div class="box-table" v-for="category in categoryPassengers" style="margin: 5px;"
                        v-if="categoryActive.id == category.id && quote_open.operation == 'passengers'">
                        <div class="table-responsive justify-content-center">
                            <h2 class="">{{ trans('quote.label.amount_table') }}</h2>
                            <hr>
                            <table class="table text-center table-results mt-4">
                                <thead>
                                    <th>{{ trans('quote.label.passenger') }}</th>
                                    <template v-if="category.flags.multiple_passengers">
                                        <th>{{ trans('quote.label.amount') }}</th>
                                    </template>
                                    <template v-else>
                                        <th>{{ trans('quote.label.amount') }}
                                            <template v-if="quantity_persons.child > 0">- ADL</template>
                                        </th>
                                        <th v-if="quantity_persons.child > 0">{{ trans('quote.label.amount') }} -
                                            CHD
                                        </th>
                                    </template>
                                </thead>
                                <tbody>
                                    <tr v-for="(passenger, key) in category.passengers">
                                        <td>@{{ passenger.passenger_name }}</td>
                                        <template v-if="category.flags.multiple_passengers">
                                            <template v-if="key < quantity_persons.adults">
                                                <td v-show="!use_discount || !permissions.adddiscount">@{{
                                                    parseFloat(passenger.amount_adult).toFixed(2) }}
                                                </td>
                                                <td v-show="use_discount && permissions.adddiscount">@{{
                                                    parseFloat(passenger.amount_adult - ( passenger.amount_adult *
                                                    (discount/100)
                                                    )).toFixed(2) }}
                                                </td>
                                            </template>
                                            <template
                                                v-if="(key - quantity_persons.adults) < quantity_persons.child && key >= quantity_persons.adults">
                                                <td v-show="!use_discount || !permissions.adddiscount">@{{
                                                    parseFloat(passenger.amount_child).toFixed(2) }}
                                                </td>
                                                <td v-show="use_discount && permissions.adddiscount">@{{
                                                    parseFloat(passenger.amount_child - ( passenger.amount_child *
                                                    (discount/100)
                                                    )).toFixed(2) }}
                                                </td>
                                            </template>
                                        </template>
                                        <template v-else>
                                            <td v-show="!use_discount || !permissions.adddiscount">@{{
                                                parseFloat(passenger.amount_adult).toFixed(2) }}
                                            </td>
                                            <td v-show="!use_discount || !permissions.adddiscount"
                                                v-if="quantity_persons.child > 0">@{{
                                                parseFloat(passenger.amount_child).toFixed(2) }}
                                            </td>
                                            <td v-show="use_discount && permissions.adddiscount">@{{
                                                parseFloat(passenger.amount_adult - ( passenger.amount_adult *
                                                (discount/100)
                                                )).toFixed(2) }} - ADL
                                            </td>
                                            <td v-show="use_discount && permissions.adddiscount"
                                                v-if="quantity_persons.child > 0">@{{
                                                parseFloat(passenger.amount_child - ( passenger.amount_child *
                                                (discount/100)
                                                )).toFixed(2) }} - CHD
                                            </td>
                                        </template>
                                    </tr>
                                    <tr v-show="use_discount && permissions.adddiscount">
                                        <td>
                                            <input class="form-control text-center" type="text"
                                                v-model="discount_detail"
                                                placeholder="{{ trans('quote.label.reason_for_discount') }}"
                                                @input="updateDiscount">
                                        </td>
                                        <td><span class="percent">%</span>
                                            <input class="form-control text-center" type="number" min="0" max="80"
                                                v-model="discount" @input="updateDiscount">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <template v-if="category.passengers_optional.length > 0">
                                <h2 class="">{{ trans('quote.label.optionals') }}</h2>
                                <hr>
                                <table class="table text-center table-results mt-4">
                                    <thead>
                                        <th>{{ trans('quote.label.passenger') }}</th>
                                        <template v-if="category.flags.multiple_passengers">
                                            <th>{{ trans('quote.label.amount') }}</th>
                                        </template>
                                        <template v-else>
                                            <th>{{ trans('quote.label.amount') }}
                                                <template v-if="quantity_persons.child > 0">- ADL</template>
                                            </th>
                                            <th v-if="quantity_persons.child > 0">{{ trans('quote.label.amount') }}
                                                - CHD
                                            </th>
                                        </template>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(passenger, key) in category.passengers_optional">
                                            <td>@{{ passenger.passenger_name }}</td>
                                            <template v-if="category.flags.multiple_passengers">
                                                <template v-if="key < quantity_persons.adults">
                                                    <td v-show="!use_discount || !permissions.adddiscount">@{{
                                                        parseFloat(passenger.amount_adult).toFixed(2) }}
                                                    </td>
                                                    <td v-show="use_discount && permissions.adddiscount">@{{
                                                        parseFloat(passenger.amount_adult - ( passenger.amount_adult
                                                        * (discount/100)
                                                        )).toFixed(2) }}
                                                    </td>
                                                </template>
                                                <template
                                                    v-if="(key - quantity_persons.adults) < quantity_persons.child && key >= quantity_persons.adults">
                                                    <td v-show="!use_discount || !permissions.adddiscount">@{{
                                                        parseFloat(passenger.amount_child).toFixed(2) }}
                                                    </td>
                                                    <td v-show="use_discount && permissions.adddiscount">@{{
                                                        parseFloat(passenger.amount_child - ( passenger.amount_child
                                                        * (discount/100)
                                                        )).toFixed(2) }}
                                                    </td>
                                                </template>
                                            </template>
                                            <template v-else>
                                                <td v-show="!use_discount || !permissions.adddiscount">@{{
                                                    parseFloat(passenger.amount_adult).toFixed(2) }}
                                                </td>
                                                <td v-show="!use_discount || !permissions.adddiscount"
                                                    v-if="quantity_persons.child > 0">@{{
                                                    parseFloat(passenger.amount_child).toFixed(2) }}
                                                </td>
                                                <td v-show="use_discount && permissions.adddiscount">@{{
                                                    parseFloat(passenger.amount_adult - ( passenger.amount_adult *
                                                    (discount/100)
                                                    )).toFixed(2) }} - ADL
                                                </td>
                                                <td v-show="use_discount && permissions.adddiscount"
                                                    v-if="quantity_persons.child > 0">@{{
                                                    parseFloat(passenger.amount_child - ( passenger.amount_child *
                                                    (discount/100)
                                                    )).toFixed(2) }} - CHD
                                                </td>
                                            </template>
                                        </tr>
                                        <tr v-show="use_discount && permissions.adddiscount">
                                            <td>
                                                <input class="form-control text-center" type="text"
                                                    v-model="discount_detail"
                                                    placeholder="{{ trans('quote.label.reason_for_discount') }}"
                                                    @input="updateDiscount">
                                            </td>
                                            <td><span class="percent">%</span>
                                                <input class="form-control text-center" type="number" min="0"
                                                    max="80"
                                                    v-model="discount" @input="updateDiscount">
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </template>
                            <label v-if="permissions.adddiscount">
                                <input type="checkbox" v-model="use_discount" @change="updateDiscount">
                                {{ trans('quote.label.add_percentage_discount') }}.
                            </label>
                            <label v-if="permissions.adddiscount && use_discount && discount>3"
                                style="margin-left: 20px;">
                                {{ trans('quote.label.request_permission_from')  }}:
                                <input class="form-control text-center" @input="updateDiscount" type="text"
                                    v-model="discount_user_permission"
                                    :placeholder="'KAM (' + translations.label.rate_code + ')'">
                            </label>
                        </div>
                    </div>
                    <div class="box-table" v-for="category in categoryRanges" style="margin: 5px;"
                        v-if="categoryActive.id == category.id && quote_open.operation == 'ranges'">
                        <div class="table-responsive justify-content-center">
                            <h2>{{ trans('quote.label.amount_table') }}</h2>
                            <table class="table text-center">
                                <thead>
                                    <th style="color: #fff !important;">{{ trans('quote.label.range') }}</th>
                                    <th style="color: #fff !important;">{{ trans('quote.label.total_amount') }}</th>
                                </thead>
                                <tbody>
                                    <tr v-for="range in category.ranges">
                                        <td>@{{ range.from }} - @{{ range.to }}</td>
                                        <td v-show="!use_discount || !permissions.adddiscount">@{{
                                            parseFloat(range.amount).toFixed(2) }}
                                        </td>
                                        <td v-show="use_discount && permissions.adddiscount">@{{
                                            parseFloat(range.amount
                                            - ( range.amount * (discount/100) )).toFixed(2) }}
                                        </td>
                                    </tr>
                                    <tr v-show="use_discount && permissions.adddiscount">
                                        <td>
                                            <input class="form-control text-center" type="text"
                                                v-model="discount_detail"
                                                placeholder="{{ trans('quote.label.reason_for_discount') }}"
                                                @input="updateDiscount">
                                        </td>
                                        <td><span class="percent">%</span>
                                            <input class="form-control text-center" type="number" min="0" max="80"
                                                v-model="discount" @input="updateDiscount">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <h2>{{ trans('quote.label.optionals') }}</h2>
                            <table class="table text-center">
                                <thead>
                                    <th style="color: #fff !important;">{{ trans('quote.label.range') }}</th>
                                    <th style="color: #fff !important;">{{ trans('quote.label.total_amount') }}</th>
                                </thead>
                                <tbody>
                                    <tr v-for="range in category.ranges_optional">
                                        <td>@{{ range.from }} - @{{ range.to }}</td>
                                        <td v-show="!use_discount || !permissions.adddiscount">@{{
                                            parseFloat(range.amount).toFixed(2) }}
                                        </td>
                                        <td v-show="use_discount && permissions.adddiscount">@{{
                                            parseFloat(range.amount
                                            - ( range.amount * (discount/100) )).toFixed(2) }}
                                        </td>
                                    </tr>
                                    <tr v-show="use_discount && permissions.adddiscount">
                                        <td>
                                            <input class="form-control text-center" type="text"
                                                v-model="discount_detail"
                                                placeholder="{{ trans('quote.label.reason_for_discount') }}"
                                                @input="updateDiscount">
                                        </td>
                                        <td><span class="percent">%</span>
                                            <input class="form-control text-center" type="number" min="0" max="80"
                                                v-model="discount" @input="updateDiscount">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <label v-if="permissions.adddiscount">
                                <input type="checkbox" v-model="use_discount" @change="updateDiscount">
                                {{ trans('quote.label.add_percentage_discount') }}.
                            </label>
                            <label v-if="permissions.adddiscount && use_discount && discount>3"
                                style="margin-left: 20px;">
                                {{ trans('quote.label.request_permission_from')  }}:
                                <input class="form-control text-center" v-model="discount_user_permission"
                                    @input="updateDiscount" type="text"
                                    :placeholder="'KAM (' + translations.label.rate_code + ')'">
                            </label>
                        </div>
                    </div>
                </template>
                <div class="group-btn mt-5" v-if="quote_id != null">
                    <button @click="willGoExport()" class="btn btn-primary"
                        :disabled="(use_discount && discount>3 && discount_user_permission=='') || loading">
                        {{ trans('quote.label.export') }} <i class="fa fa-spin fa-spinner" v-show="loading"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</b-modal>
{{-- End modal Cotizar       --}}
{{-- modal Politicas de cancelacion         --}}
<div class="modal fade modal_cotizar" id="modal_politicas_cancelacion" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-label="Close" class="close"
                    id="close_modal_politicas_cancelacion">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <b>Politica de Cancelacion</b>
                <p>@{{ policies_cancellation }}</p>
                <b>Politicas Generales</b>
                <p>@{{ general_politics }}</p>

            </div>
        </div>
    </div>
</div>
{{-- End modal Politicas de cancelacion       --}}
{{-- modal Realizar         --}}
<b-modal ref="my-modal-confirm" hide-footer centered size="md" class="modal-central modal-content"
    id="modal_realizar">
    <div class="text-center">
        <h4 class="text-center mb-5" style="font-size: 18px;">
            <div class="icon mb-3">
                <i class="icon-alert-circle" style="font-size: 40px;"></i>
            </div>
            <strong>{{ trans('quote.messages.category_already_has_services') }}</strong>
        </h4>
        <div class="d-flex justify-content-between">
            <button @click="hideModal()" class="btn btn-cancelar mr-1">{{trans('global.label.cancel')}}</button>
            <button @click="copyCategory()" class="btn btn-primary">{{trans('global.label.do')}}</button>
        </div>
    </div>
</b-modal>

{{-- end Realizar         --}}
{{-- modal Reservar         --}}
<b-modal ref="modal_reserve" hide-footer centered size="md" class="modal-central modal-content"
    id="modal_reserve" v-if="quote_id != null" :no-close-on-backdrop="true" :no-close-on-esc="true"
    :hide-header-close="true">
    <div class="reservation">
        <b-tabs>
            <b-tab active v-if="has_file">
                <template #title>
                    @{{ translations.label.tab_file_details }}
                </template>
                <div class="col-md-12 mt-5 text-center text-dark">
                    <div class="color-inf">
                        <label for="file_code" class="col-12 col-form-label size_title">
                            FILE
                            <strong>@{{ file.file_code }}</strong>
                        </label>
                    </div>
                    <div>
                        <label for="file_code" class="col-12">
                            {{trans('quote.label.client')}}:
                            <strong class="color-inf">
                                @{{ file.client.code }} - @{{ file.client.name }}
                            </strong>
                        </label>
                    </div>
                    <div v-if="new_order_related != '' && new_order_related != null">
                        <label for="file_reference" class="col-12">
                            Order number:
                            <strong class="color-inf">@{{ new_order_related }}</strong>
                            <!-- {{trans('package.label.file_reference')}}  # <strong>@{{ file.file_reference
                                }}</strong> -->
                        </label>
                    </div>
                    <div>
                        <label for="file_code" class="col-12 mt-5 color-inf">
                            <i class="fas fa-user"></i></i> @{{ translations.label.name_pax }}:
                            <span class="text-dark">@{{ file.file_reference }}</span>
                        </label>
                    </div>
                    <div>
                        <label for="file_code" class="col-12 py-2 color-inf">
                            <i class="fas fa-user-friends"></i> @{{ translations.label.number_pax }}:
                            <span class="text-dark">
                                @{{ quantity_persons.adults}} {{ trans('quote.label.adults') }}
                                <template v-if="quantity_persons.child > 0">
                                    @{{ quantity_persons.child }} {{ trans('quote.label.child') }}
                                </template>
                            </span>
                        </label>
                    </div>
                    <div>
                        <label for="file_reference" class="col-12 mb-5 color-inf">
                            <i class="fas fa-user-tag"></i> @{{ translations.label.type_service }}:
                            <span class="text-dark">@{{ getServiceType() }}</span>
                        </label>
                    </div>
                </div>
            </b-tab>
            <b-tab>
                <template #title>
                    @{{ translations.label.booking_details }}
                </template>
                <div class="text-center mt-5">
                    <div>
                        <label for="file_reference" class="col-12 size_title mb-5 text-dark2">
                            @{{ translations.label.quote }}: <span
                                class="color-inf">@{{ quote_open.id_original }}</span>
                        </label>
                        <template v-if="user_type_id == 3 && statements.client.code != undefined">
                            <label for="file_code" class="col-12 mb-3">
                                {{trans('quote.label.client')}}:
                                <strong class="color-inf">
                                    @{{ statements.client.code }} - @{{ statements.client.name }}
                                </strong>
                            </label>
                        </template>
                        <div class="row mb-5" v-if="user_type_id == 3">
                            <label for="orderNumber" class="col m-0 p-0 text-dark"> @{{
                                    translations.label.order_number }}:</label>
                            <input type="text" v-bind:disabled="readonly || has_file"
                                placeholder="12345" class="col-7 form-control" v-model="new_order_related" />
                        </div>
                    </div>
                    <span>@{{ translations.label.message_confirm_reservation }}:</span>
                    <div class="mb-2">
                        <div class="row c-dark rounded justify-content-center my-4 py-5 ml-0">
                            <div class="border-right">
                                <div class="mb-2">
                                    <label class="col-12 color-inf">
                                        <i class="fas fa-plane-arrival"></i> @{{ translations.label.arrival_date }}:
                                    </label>
                                </div>
                                <span class="text-dark">
                                    @{{ getDateIn() }}
                                </span>
                            </div>
                            <div>
                                <div class="mb-2">
                                    <label class="col-12 color-inf">
                                        <i class="fas fa-plane-departure"></i> @{{ translations.label.departure_date
                                            }}:
                                    </label>
                                </div>
                                <span class="text-dark">
                                    @{{ getFormatDate(statements.quote.date_out) }}
                                </span>
                            </div>
                            <div class="mt-4 row">
                                <label class="col-12 color-inf">
                                    <div class="border-top pt-4 px-3">
                                        <i class="fas fa-bed"></i> @{{ translations.label.total_stay }}:
                                        <span class="text-dark mt-3">@{{ statements.quote.nights }} @{{ translations.label.nights }}</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label for="file_code" class="col-12 color-inf mt-2">
                            <i class="fas fa-user-friends"></i> @{{ translations.label.number_pax }}:
                            <span class="text-dark">
                                @{{ quantity_persons.adults}} {{ trans('quote.label.adults') }}
                                <template v-if="quantity_persons.child > 0">
                                    @{{ quantity_persons.child }} {{ trans('quote.label.child') }}
                                </template>
                            </span>
                        </label>
                    </div>

                    <div class="row">
                        <label for="file_code" class="col-12 py-3 color-inf">
                            <i class="fas fa-building"></i> @{{ translations.label.arrangement }}:
                            <span class="text-dark">
                                <template v-if="quote_open.accommodation.single > 0">
                                    @{{ quote_open.accommodation.single }} SGL
                                </template>
                                <template v-if="quote_open.accommodation.double > 0">
                                    @{{ quote_open.accommodation.double }} DBL
                                </template>
                                <template v-if="quote_open.accommodation.triple > 0">
                                    @{{ quote_open.accommodation.triple }} TPL
                                </template>
                            </span>
                        </label>
                    </div>
                    <div class="row">
                        <label for="file_reference" class="col-12  color-inf">
                            <i class="fas fa-user-tag"></i> @{{ translations.label.type_service }}:
                            <span class="text-dark">@{{ getServiceType() }}</span>
                        </label>
                    </div>
                    <div>
                        <div class="c-dark rounded justify-content-center mt-4 mb-5 py-4">
                            <div>
                                <label class="col-5 p-0 m-0 text-dark">
                                    @{{ translations.label.estimated_amount }}:
                                </label>
                                <strong class="color-inf">
                                    <template v-if="statements.total > 0">$ @{{ statements.total | roundLito }}
                                    </template>
                                    <template v-else>...</template>
                                </strong>
                            </div>
                        </div>
                    </div>
                    <div class="row" v-if="rq_hotels.length > 0">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th scope="col">Hotel</th>
                                    <th scope="col" width="15">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in rq_hotels">
                                    <td>
                                        <strong>[@{{ item.code }}]</strong> @{{ item.name }}
                                    </td>
                                    <td>
                                        <span class="badge badge-danger">RQ</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </b-tab>
        </b-tabs>
    </div>
    <div class="row m-0">
        <div class="col-md-5 col-md-5 p-0 mr-5 ml-4">
            <button @click="hideModalReserve()" style="height: 52px !important;"
                class="btn btn-cancelar mt-4 mb-2"
                :disabled="loading_reserve">
                {{trans('global.label.cancel')}}
            </button>
        </div>
        <div class="col-md-5 p-0 mt-4">
            <button @click="willReserveQuote()" class="btn btn-primary mb-2" style="width: 100%;"
                :disabled="loading_reserve">
                <span v-if="loading_reserve">
                    <i class="fa fa-spin fa-spinner"></i> {{trans('global.label.processing_your_booking')}}...
                </span>
                <span v-if="!loading_reserve">
                    {{trans('global.label.do')}}
                </span>
            </button>
        </div>
    </div>
</b-modal>
{{-- end Realizar         --}}
@include('services.modal')


<b-modal class="modal" hide-footer ref="modalWillRemoveService" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div>
                <ul class="list-group scrollbar-outer mt-4" v-if="similar_services.length>0">
                    <li class="list-group-item" v-for="similar_category in similar_services">
                        <h4>@{{ similar_category.category_name }}</h4>
                        <label class="lbl-similar" v-for="similar_service in similar_category.services">
                            <input type="checkbox" v-model="similar_service.check"> @{{ similar_service.date }}
                            - @{{ similar_service.code }}
                        </label>
                    </li>
                </ul>
                <h4 class="text-center">
                    <div class="icon">
                        <i class="icon-alert-circle" v-if="!loading"></i>
                        <i class="spinner-grow" v-if="loading"></i>
                    </div>
                    <strong v-if="!loading">@{{ translations.label.one_step_away_eliminating_service }}: "
                        <span v-if="serviceChoosen.type=='hotel' || serviceChoosen.type=='group_header'">@{{ serviceChoosen.hotel.name }}</span>
                        <span v-if="serviceChoosen.type=='service'">@{{ serviceChoosen.service.service_translations[0].name }}</span>
                        "</strong>
                    <strong v-if="loading">@{{ translations.label.loading }}</strong>
                </h4>
                <p class="text-center" v-if="!loading"><strong>@{{ translations.label.are_you_sure }}</strong>
                </p>
                <div class="row justify-content-center mt-3" v-if="!loading">
                    <button type="button" class="btn-seleccionar btn-acciones col-5 mx-3"
                        @click="closeModalWillRemoveService">
                        @{{ translations.label.cancel }}
                    </button>
                    <button type="button" @click="deleteService(serviceChoosen)"
                        class="btn-seleccionar btn-acciones col-5 mx-3">@{{ translations.label.yes_continue
                            }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</b-modal>

<b-modal class="modal" hide-footer ref="removeAllService" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div>
                <h4 class="text-center">
                    <strong>@{{ translations.label.delete_all_service_selected }}</strong>
                </h4>
                <p class="text-center" v-if="!loading"><strong>@{{ translations.label.are_you_sure }}</strong>
                </p>
                <div class="row justify-content-center mt-3" v-if="!loading">
                    <button type="button" class="btn-seleccionar btn-acciones col-5 mx-3"
                        @click="closeModalremoveAllService">
                        @{{ translations.label.cancel }}
                    </button>
                    <button type="button" @click="deleteServices()"
                        class="btn-seleccionar btn-acciones col-5 mx-3">@{{ translations.label.yes_continue
                            }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</b-modal>


<b-modal class="modal-central modal-content" id="modal_reservation_errors" aria-hidden="true"
    ref="modal_reservation_errors" :no-close-on-backdrop="true" :no-close-on-esc="true"
    :hide-header-close="true">
    <h1 class="mb-3">
        <i class="fas fa-exclamation-triangle"></i> {{ trans('reservations.label.reservation') }}
    </h1>

    <p class="alert alert-danger text-justify p-3">
        {{trans('quote.label.sorry_your_operation_could_not_carried_out')}}
    </p>
    <div v-if="errors_reservations.services.length > 0">
        <h4 class="font-weight-bold mt-3" style="color:#000000 !important">
            {{trans('quote.label.services')}}:
        </h4>
        <div v-for="service in errors_reservations.services">
            <p style="font-weight: bold !important;color:#000000 !important">
                <i class="fas fa-times-circle text-danger"></i> @{{ service.service_code }}
            </p>
            <ul>
                <li class="text-danger" v-for="error in service.errors">@{{ error.error }}</li>
            </ul>
        </div>
    </div>
    <div v-if="errors_reservations.hotels.length > 0">
        <h4 class="font-weight-bold mt-3" style="color:#000000 !important">
            {{trans('quote.label.hotels')}}:
        </h4>
        <div v-for="hotel in errors_reservations.hotels">
            <p style="font-weight: bold !important;color:#000000 !important">
                <i class="fas fa-times-circle text-danger"></i> @{{ hotel.hotel_code }}
            </p>
            <ul>
                <li class="text-danger" v-for="error in hotel.errors">@{{ error.error }}</li>
            </ul>
        </div>
    </div>
    <hr>
    <button @click="closeModalReservationErrors()" style="height: 50px !important;"
        class="btn btn-cancelar"
        :disabled="loading_reserve">@{{ translations.label.close }}
    </button>
</b-modal>

{{-- modal paquete id      --}}
<b-modal class="modal fade" id="modal_package_created" ref="modal_package_created" aria-hidden="true"
    :no-close-on-backdrop="true" :no-close-on-esc="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div>
                <div class="mb-2">
                    <h3 class="text-center">
                        <i class="fas fa-check-circle text-success"></i> Su paquete ha sido creado
                        satisfactoriamente
                    </h3>
                </div>
                <hr>
                <div class="mt-5">
                    <h4 class="text-center font-weight-bold text-dark" v-if="package_create.id != null">
                        El ID de su paquete es : @{{ package_create.id }}
                    </h4>
                </div>
                <div class="text-center">
                    <button class="btn btn-cancelar col-12 mt-5 mr-1"
                        style="height: 52px !important;"
                        @click="closeModalPackageCreated">
                        <span v-if="loading"><i class="fa fa-spinner fa-spin"></i></span>
                        {{ trans('quote.label.close') }}
                    </button>
                </div>

            </div>
        </div>
    </div>
</b-modal>

<b-modal class="modal" hide-footer ref="confirmConvertModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div>
                <h4 class="text-center">
                    <strong>¿{{ trans('quote.label.convert_to_package') }}?</strong>
                </h4>
                <p class="text-center" v-if="!loading"><strong>@{{ translations.label.are_you_sure }}</strong>
                </p>
                <div class="row justify-content-center mt-3" v-if="!loading">
                    <button type="button" class="btn-seleccionar btn-acciones col-5 mx-3"
                        @click="$refs.confirmConvertModal.hide()">
                        @{{ translations.label.cancel }}
                    </button>
                    <button type="button" @click="convertToPackage()"
                        class="btn-seleccionar btn-acciones col-5 mx-3">@{{ translations.label.yes_continue
                            }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</b-modal>

<!-- Modal Habilitar Cotización LATAM -->
<b-modal centered id="latamModal" ref="latamModal" size="lg" :no-close-on-backdrop="true" :no-close-on-esc="true" :hide-header-close="true">
    <template v-slot:modal-header="{close}">
        <button type="button" class="close ml-auto" @click="closeLatamModal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </template>
    <div class="modal-content">
        <div class="w-100 d-flex align-items-center gap-10">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <g clip-path="url(#clip0_10850_1385)">
                    <path d="M24 12C24 18.6281 18.6281 24 12 24C5.37188 24 0 18.6281 0 12C0 5.37188 5.37188 0 12 0C18.6281 0 24 5.37188 24 12ZM2.70516 9.00469L3.14391 9.81562C3.5325 10.4953 4.17141 10.9969 4.92656 11.2125L7.59844 11.9859C8.44688 12.2156 9 12.9516 9 13.7906V15.6609C9 16.1766 9.29062 16.6453 9.75 16.8328C10.2094 17.1047 10.5 17.5734 10.5 18.0891V19.9172C10.5 20.6484 11.1984 21.1734 11.9016 20.9719C12.6609 20.7563 13.2422 20.1141 13.4344 19.3922L13.5656 18.8672C13.7625 18.075 14.2781 17.3953 14.9859 16.9875L15.3656 16.7719C16.0687 16.3734 16.5 15.6281 16.5 14.8172V14.4328C16.5 13.8328 16.2609 13.2609 15.8391 12.8391L15.6609 12.6609C15.2391 12.2391 14.6625 11.9578 14.0672 11.9578H12.0047C11.5266 11.9578 11.0109 11.8641 10.5562 11.6063L8.93906 10.6781C8.7375 10.5656 8.58281 10.3781 8.5125 10.1578C8.3625 9.70781 8.56406 9.22031 8.98594 9.00469L9.26719 8.86875C9.57656 8.71406 9.93281 8.68594 10.2234 8.79844L11.3531 9.15937C11.7328 9.28594 12.1547 9.14062 12.3797 8.80781C12.6 8.475 12.5766 8.03906 12.3234 7.73438L11.6859 6.975C11.2172 6.4125 11.2219 5.59219 11.7 5.03906L12.4359 4.1775C12.8484 3.69609 12.9141 3.0075 12.6 2.45719L12.4875 2.26219C12.2859 2.25422 12.1641 2.25 12 2.25C7.64531 2.25 3.95625 5.10469 2.70516 9.00469ZM20.5125 7.24219L19.3125 7.725C18.5766 8.02031 18.1969 8.83594 18.4453 9.59063L19.2375 11.9672C19.4016 12.4547 19.8 12.825 20.2969 12.9516L21.6656 13.2891C21.7219 12.8672 21.75 12.4359 21.75 12C21.75 10.275 21.3 8.65312 20.5125 7.24219Z" fill="#BD0D12" />
                </g>
                <defs>
                    <clipPath id="clip0_10850_1385">
                        <rect width="24" height="24" fill="white" />
                    </clipPath>
                </defs>
            </svg>
            <h5 class="modal-title mb-0">
                Habilitar cotización Latam
            </h5>
        </div>
        <hr class="hr">
        <!-- Mensaje informativo -->
        <div class="alert alert-info latam-info-box" v-if="showInfoMessage">
            <div class="d-flex align-items-center">
                <i class="fa fa-info-circle latam-info-icon"></i>
                <span class="latam-info-text">
                    Selecciona dos o más cotizaciones. Así generarás un documento único con los montos totales, listo para compartir con tu cliente.
                </span>
                <button type="button" class="btn-close-info" @click="dismissInfo">
                    <i class="fa fa-times"></i>
                </button>
            </div>
        </div>

        <!-- Sección de consolidación -->
        <div class="latam-consolidation-section">
            <div class="latam-section-title">
                <h6>Selecciona para consolidar</h6>
                <hr class="latam-hr">
            </div>
            <div class="form-group">
                <label class="latam-label">Número de cotización</label>
                <div class="latam-search-container" @click.stop>
                    <div class="latam-search-input-wrapper">
                        <input
                            type="text"
                            class="latam-search-input"
                            v-model="searchQuery"
                            @focus="showDropdown = true"
                            @blur="handleInputBlur"
                            @input="filterQuotes"
                            placeholder="Buscar cotización...">
                        <div class="latam-search-icons">
                            <i class="fa fa-times latam-clear-icon"
                                v-if="searchQuery"
                                @click="clearSearch"></i>
                            <i class="fa fa-chevron-down latam-arrow-icon"
                                @click="toggleDropdown"></i>
                        </div>
                    </div>

                    <!-- Dropdown con resultados -->
                    <div class="latam-dropdown-list" v-show="showDropdown">
                        <!-- Loading indicator -->
                        <div class="latam-loading" v-if="searchLoading">
                            <div class="latam-spinner"></div>
                            <span>Buscando cotizaciones...</span>
                        </div>

                        <!-- Resultados -->
                        <div v-else>
                            <div class="latam-dropdown-item"
                                v-for="quote in filteredQuotes"
                                :key="quote.id"
                                @click="toggleQuoteSelection(quote)">
                                <input type="checkbox"
                                    :checked="isQuoteSelected(quote.id)"
                                    @change="toggleQuoteSelection(quote)">
                                <span class="latam-quote-number">@{{ quote.number }}</span>
                                <span class="latam-country-badge" :class="'badge-' + (quote.country || 'sin-pais').toLowerCase()">
                                    @{{ quote.country || 'País no encontrado' }}
                                </span>
                            </div>
                            <div class="latam-no-results" v-if="filteredQuotes.length === 0 && !searchLoading">
                                No se encontraron cotizaciones
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cotizaciones seleccionadas -->
            <div class="latam-selected-quotes" v-if="selectedQuotes.length > 0">
                <div class="latam-quote-card" v-for="quote in selectedQuotes" :key="quote.id">
                    <button class="latam-remove-quote" @click="removeQuote(quote.id)">
                        <i class="fa fa-times"></i>
                    </button>

                    <div class="latam-card-content">
                        <!-- Columna izquierda: Número y Usuario -->
                        <div class="latam-card-left">
                            <div class="latam-quote-number-card">N° @{{ quote.number }}</div>
                            <div class="latam-user-info">
                                <svg width="15" height="16" viewBox="0 0 15 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <g clip-path="url(#clip0_11244_1134)">
                                        <path d="M11.7857 4.25C11.7857 6.32129 9.86719 8 7.5 8C5.13281 8 3.21429 6.32129 3.21429 4.25C3.21429 2.179 5.13281 0.5 7.5 0.5C9.86719 0.5 11.7857 2.179 11.7857 4.25ZM7.00112 11.0234L5.89286 9.40625H9.10714L7.99888 11.0234L9.11384 14.6533L10.4364 9.93066C13.0212 10.2822 15 12.2393 15 14.6006C15 15.0957 14.5379 15.5 13.9721 15.5H1.02857C0.460379 15.5 0 15.0957 0 14.6006C0 12.2393 1.97846 10.2822 4.56362 9.93066L5.88616 14.6533L7.00112 11.0234Z" fill="#2F353A" />
                                    </g>
                                    <defs>
                                        <clipPath id="clip0_11244_1134">
                                            <rect width="15" height="15" fill="white" transform="translate(0 0.5)" />
                                        </clipPath>
                                    </defs>
                                </svg>
                                @{{ quote.user_name }}
                            </div>
                        </div>

                        <!-- Columna central: Fechas -->
                        <div class="latam-card-center">
                            <div class="latam-start-date">
                                <svg width="12" height="13" viewBox="0 0 11 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <g clip-path="url(#clip0_11244_1232)">
                                        <path d="M3.7316 1.5H6.94589V0.5625C6.94589 0.251953 7.18473 0 7.4816 0C7.77848 0 8.01732 0.251953 8.01732 0.5625V1.5H8.91017C9.69812 1.5 10.3387 2.17148 10.3387 3V10.5C10.3387 11.3273 9.69812 12 8.91017 12H1.76732C0.978254 12 0.338745 11.3273 0.338745 10.5V3C0.338745 2.17148 0.978254 1.5 1.76732 1.5H2.66017V0.5625C2.66017 0.251953 2.89901 0 3.19589 0C3.49276 0 3.7316 0.251953 3.7316 0.5625V1.5ZM1.41017 10.5C1.41017 10.7063 1.57 10.875 1.76732 10.875H8.91017C9.1066 10.875 9.26732 10.7063 9.26732 10.5V4.5H1.41017V10.5Z" fill="#7E8285" />
                                    </g>
                                    <defs>
                                        <clipPath id="clip0_11244_1232">
                                            <rect width="10" height="12" fill="white" transform="translate(0.338745)" />
                                        </clipPath>
                                    </defs>
                                </svg>
                                <span class="latam-start-date-text">Inicio: @{{ quote.date_in }} </span>
                                <span class="latam-duration">(@{{ quote.nights }} días)</span>
                            </div>
                            <div class="latam-created-date">
                                Creada: @{{ quote.created_at }}
                            </div>
                            <div class="latam-expiry-date">
                                (@{{ quote.expiration_date }})
                            </div>
                        </div>

                        <!-- Columna derecha: País, MKP y Total -->
                        <div class="latam-card-center">
                            <div class="latam-badges-row">
                                <span class="latam-country-badge-card" :class="'badge-' + (quote.country || 'sin-pais').toLowerCase()">
                                    @{{ quote.country || 'País no encontrado' }}
                                </span>
                                <span class="latam-mkp-badge">
                                    MKP @{{ quote.markup }}%
                                </span>
                            </div>
                            <div class="latam-total">
                                Total: <span class="latam-total-amount">@{{ quote.total }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-100 d-flex justify-content-end">
            <button type="button" class="btn btn-primary latam-consolidate-btn" :disabled="!canConsolidate" @click="consolidateQuotes">
                <svg width="20" height="21" viewBox="0 0 20 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M14.1667 18V11.3333H5.83333V18M5.83333 3V7.16667H12.5M15.8333 18H4.16667C3.72464 18 3.30072 17.8244 2.98816 17.5118C2.67559 17.1993 2.5 16.7754 2.5 16.3333V4.66667C2.5 4.22464 2.67559 3.80072 2.98816 3.48816C3.30072 3.17559 3.72464 3 4.16667 3H13.3333L17.5 7.16667V16.3333C17.5 16.7754 17.3244 17.1993 17.0118 17.5118C16.6993 17.8244 16.2754 18 15.8333 18Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>

                Consolidar
            </button>
        </div>
    </div>
</b-modal>

</div>

@endsection
@section('css')
<style>
    .multiselec input {
        padding: 2px !important;
        font-size: 12px !important;
    }

    .grouped_class {
        background: #ffe1a56e !important;
    }

    .grouped_class_row {
        background: #fff2d870 !important;
        margin-left: 29px;
    }

    .accommodation-enabled {
        background-color: #ffda0b4d;
    }

    .accommodation-disabled {
        background-color: #e9e9e9;
    }

    .cotizacion-crear--pasajeros .text {
        padding-right: 5px !important;
    }

    .page-cotizacion .cotizaciones-listado .draggable ul .producto {
        position: relative;
    }

    .page-cotizacion .cotizaciones-listado .draggable ul .producto>.row.m-0 {
        width: 100%;
        flex: 0 0 100%;
    }

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

    .btn-update-all-ranges {
        float: right;
        padding: 4px 8px !important;
        width: auto !important;
    }

    .span-accommodation {
        color: #383838;
        font-weight: 800;
        margin-top: -18px;
        margin-left: 18px;
        display: block;
        border-radius: 4px;
    }

    .tag-districts {
        margin-right: 10px;
        padding: 10px;
        background: #f2faff;
        border-radius: 5px;
        border: solid 1px #ebebeb;
    }

    .lbl-similar {
        padding: 7px;
        width: 100%;
    }

    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    input[type=number] {
        -moz-appearance: textfield;
    }

    .fa-minus-circle:hover,
    .fa-plus-circle:hover {
        color: #EB5757;
        cursor: pointer;
    }

    .cotizacion-editar--markup-container {
        position: relative;
        display: flex;
    }

    .cotizacion-editar--markup-container {
        display: flex;
        align-items: center;

        width: 200px;
        max-width: 250px;
    }

    .cotizacion-editar--markup {
        font-size: 1.2rem;
        font-weight: 600;
        color: #EB5757;
    }


    .btn-link--markup {
        color: #ffffff;
        font-size: 1.2rem;
        display: block;
        text-align: center;
        width: 140px;
        /* margin: 0 auto; */
        white-space: nowrap;
        padding-left: 15px;
    }

    .markup-list-modal {
        position: absolute;
        top: 100%;
        left: 0;
        margin-top: 8px;
        z-index: 1000;
    }

    .markup-list-content {
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        min-width: 300px;
        max-width: 500px;
        overflow: hidden;
    }

    .markup-list-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 16px;
        border-bottom: 1px solid #e9ecef;
        font-size: 14px;
        font-weight: 600;
        color: #495057;
    }

    .markup-list-close {
        background: none;
        border: none;
        font-size: 24px;
        line-height: 1;
        color: #6c757d;
        cursor: pointer;
        padding: 0;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: color 0.2s;
    }

    .markup-list-close:hover {
        color: #212529;
    }

    .markup-list-items {
        list-style: none;
        margin: 0;
        padding: 0;
        max-height: 400px;
        overflow-y: auto;
    }

    .markup-list-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 16px;
        border-bottom: 1px solid #f8f9fa;
        transition: background-color 0.2s;
    }

    .markup-list-item:last-child {
        border-bottom: none;
    }

    .markup-list-item:hover {
        background-color: #f8f9fa;
    }

    .markup-list-country {
        font-size: 14px;
        color: #212529;
        font-weight: 500;
    }

    .markup-list-value {
        font-size: 13px;
        color: #007bff;
        font-weight: 600;
        padding: 4px 10px;
        background-color: #e7f3ff;
        border-radius: 12px;
    }

    .markup-list-empty {
        position: absolute;
        top: 100%;
        left: 0;
        margin-top: 8px;
        padding: 12px 16px;
        background: #fff3cd;
        border: 1px solid #ffc107;
        border-radius: 8px;
        color: #856404;
        font-size: 14px;
        min-width: 300px;
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
        /* display: flex; */
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
        text-align: center;
        margin-top: -1.1px;
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
        background-color: #e9ecef;
        border: solid 1px #C4C4C4;
    }

    input[type="checkbox"]:disabled:checked {
        background-color: rgba(235, 87, 87, .5);
        border: none;
        color: red;
        cursor: none;
    }

    #_overlay {
        position: fixed;
        /* Sit on top of the page content */
        width: 100%;
        /* Full width (cover the whole page) */
        height: 100%;
        /* Full height (cover the whole page) */
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.5);
        /* Black background with opacity */
        z-index: 2;
        /* Specify a stack order in case you're using a different order for other elements */
        cursor: pointer;
        /* Add a pointer on hover */
    }

    .bootstrap-datetimepicker-widget table td.active,
    .bootstrap-datetimepicker-widget table td.active:hover {
        background-color: #A71B20;
        color: #ffffff;
        text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);
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

    .check_true,
    .check_false,
    .check_undefined {
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
        background: #eceef0;
        border: solid 2px #e9ecef;
        box-shadow: 2px 2px grey;
    }

    .rateRow {
        border-radius: 5px;
        padding: 6px 10px;
    }

    .btn-acciones {
        font-size: 18px;
        height: 35px;
        font-weight: normal;
        line-height: 0;
        width: 50px;
        border: solid 1px;
    }

    .icon_green {
        color: #26bc00 !important;
    }

    .icon_default {
        color: #9b9b9b !important;
    }

    .icon_red {
        color: #A71B20 !important;
    }

    .iconServiceInExtension {
        margin-left: 13px;
        cursor: pointer;
        z-index: 10;
    }

    .percent {
        position: fixed;
        margin-left: 6px;
        margin-top: 10px;
    }

    .ancho0 {
        width: 0px !important;
    }

    .ancho75 {
        width: 75px !important;
    }

    .ancho120 {
        width: 120px !important;
    }

    .ancho270 {
        width: 270px !important;
    }

    .ancho400 {
        width: 400px !important;
    }

    .porc8 {
        width: 3% !important;
    }

    .porc10 {
        width: 10% !important;
    }

    .porc20 {
        width: 20% !important;
    }

    .porc35 {
        width: 35% !important;
    }

    .select-pax {
        display: inline;
        padding: .5rem;
        border: 1px solid #E9E9E9;
        border-radius: 3px;
        font-size: 12px;
        outline: none;
        width: 48px;
    }

    .alert-accomodation {
        display: none;
    }

    .back_warning,
    .back_warning,
    .back_warning select {
        background-color: #fff3de !important;
    }

    .back_warning_icon,
    .back_warning_icon input,
    .back_warning_icon select,
    .back_warning_amount,
    .back_warning_amount input {
        background-color: #ffe2d1 !important;
    }

    .back_warning_icon .ico-error,
    .back_warning_icon .alert-accomodation {
        display: block;
    }

    .back_warning_icon .a-plan-room,
    .back_warning_amount .a-plan-room,
    .back_warning_amount .producto-precio--num,
    .back_warning_amount .select-pax {
        background: #ffd400;
    }

    .ico-error {
        position: absolute;
        margin-top: -48px;
        margin-left: -6px;
        display: none;
    }

    .distribution {
        margin-left: 0px;
    }

    .back_warning .distribution {
        background: #fff3de;
    }

    .strong-message {
        background: red;
        color: white;
        font-size: 13px;
        position: absolute;
        padding: 0 5px;
        left: 0px;
        top: 0px;
        border-radius: 0px 0px 10px 0px;
    }

    .strong-message-sub {
        background: red;
        color: white;
        font-size: 9px;
        padding: 1px 2px;
    }

    .size_title {
        font-size: 24px !important;
    }

    .size_paragraph {
        font-size: 12px !important;
    }

    .service_validation_errors {
        box-shadow: inset 1px 0px 3px 3px #ffd1d1;
    }

    @-webkit-keyframes pulse_error {
        0% {
            -webkit-box-shadow: 0 0 0 0 rgba(221, 43, 30, 0.69);
        }

        70% {
            -webkit-box-shadow: 0 0 0 10px rgba(204, 169, 44, 0);
        }

        100% {
            -webkit-box-shadow: 0 0 0 0 rgba(204, 169, 44, 0);
        }
    }

    .file_quote {
        float: right;
        background-color: #890005;
        padding: 2px 12px 2px 12px;
        color: white !important;
        border-radius: 14px;
        font-size: 16px !important;
    }

    .modal-backdrop {
        display: none !important;
    }

    .schedules-icon {
        height: 35px;
        margin-top: -90px;
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
        background: #d4fbff;
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

    .alert-warning-quote {
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

    .inputs-ocupation {
        background-color: transparent !important;
        text-align: center !important;
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

    .room-ok {
        color: #04B5AA;
    }

    .room-rq {
        color: #CE3B4D;
    }

    .my-tooltip {
        font-size: 14px !important;
    }

    .zooImge {
        transform: scale(2.5);
        position: relative;
        justify-content: center;
    }

    .estiloX {
        position: absolute;
        float: right;
        top: 1%;
        right: 5%;
        cursor: pointer;

        background: black;
        width: 12px;
        height: 12px;
        text-align: center;
        border-radius: 18px;
        font-size: 8px;
        color: antiquewhite;
    }

    .page-cotizacion .cotizaciones-listado .draggable ul .producto .prod-acciones .form-check-input {
        position: relative;
        margin: 0 1px;
    }

    .page-cotizacion .cotizaciones-listado .draggable ul .producto .prod-acciones {
        width: 13.5%;
        white-space: nowrap;
        padding: 0px 5px 0 0;
    }

    /* Switch Styles */
    .switch-container {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .switch {
        position: relative;
        display: inline-block;
        width: 45px;
        height: 25px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        -webkit-transition: .4s;
        transition: .4s;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 19px;
        width: 19px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        -webkit-transition: .4s;
        transition: .4s;
    }

    input:checked+.slider {
        background-color: #A71B20;
    }

    input:focus+.slider {
        box-shadow: 0 0 1px #A71B20;
    }

    input:checked+.slider:before {
        -webkit-transform: translateX(20px);
        -ms-transform: translateX(20px);
        transform: translateX(20px);
    }

    .slider.round {
        border-radius: 25px;
    }

    .slider.round:before {
        border-radius: 50%;
    }

    .switch-label {
        font-weight: bold;
        color: #333;
    }

    /* Modal LATAM Styles */
    .latam-icon {
        color: #A71B20;
        margin-right: 10px;
        font-size: 18px;
    }

    .latam-info-box {
        background-color: #f8f9ff;
        border: 1px solid #d1d9ff;
        border-radius: 8px;
        padding: 15px;
        margin-top: 10px;
        margin-bottom: 10px;
    }

    .latam-info-icon {
        color: #6c5ce7;
        margin-right: 10px;
        font-size: 16px;
    }

    .latam-info-text {
        flex: 1;
        color: #333;
        font-size: 14px;
        line-height: 1.4;
    }

    .btn-close-info {
        background: none;
        border: none;
        color: #999;
        font-size: 14px;
        padding: 0;
        margin-left: 10px;
        cursor: pointer;
    }

    .btn-close-info:hover {
        color: #666;
    }

    .latam-consolidation-section {
        margin-top: 5px;
    }

    .latam-section-title {
        margin-bottom: 15px;
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 10px;
    }

    .latam-section-title h6 {
        color: #2F353A;
        font-weight: 600;
        font-size: 16px;
        margin: 0;
        white-space: nowrap;
    }

    .latam-hr {
        flex: 1;
        margin: 0;
        border: 1px solid #18181B0F;
        height: 0;
    }

    .latam-label {
        color: #7E8285;
        font-weight: 500;
        margin-bottom: 8px;
        display: block;
        font-size: 14px;
    }

    .latam-dropdown {
        border: 1px solid #ddd;
        border-radius: 6px;
        padding: 10px 12px;
        font-size: 14px;
    }

    .latam-dropdown:focus {
        border-color: #A71B20;
        box-shadow: 0 0 0 0.2rem rgba(167, 27, 32, 0.25);
    }

    /* Estilos para el componente de búsqueda */
    .latam-search-container {
        position: relative;
        width: 35%;
    }

    .latam-search-input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .latam-search-input {
        width: 100%;
        border: 1px solid #ddd;
        border-radius: 6px;
        padding: 10px 40px 10px 12px;
        font-size: 14px;
        outline: none;
        transition: border-color 0.3s ease;
    }

    .latam-search-input:focus {
        border-color: #A71B20;
        box-shadow: 0 0 0 0.2rem rgba(167, 27, 32, 0.25);
    }

    .latam-search-icons {
        position: absolute;
        right: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .latam-clear-icon,
    .latam-arrow-icon {
        cursor: pointer;
        color: #666;
        font-size: 14px;
        transition: color 0.3s ease;
    }

    .latam-clear-icon:hover,
    .latam-arrow-icon:hover {
        color: #A71B20;
    }

    .latam-dropdown-list {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #ddd;
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 1000;
        max-height: 200px;
        overflow-y: auto;
    }

    .latam-dropdown-item {
        display: flex;
        align-items: center;
        padding: 10px 12px;
        cursor: pointer;
        /* border-bottom: 1px solid #f0f0f0; */
        transition: background-color 0.2s ease;
    }

    .latam-dropdown-item:hover {
        background-color: #f8f9fa;
    }

    .latam-dropdown-item:last-child {
        border-bottom: none;
    }

    .latam-dropdown-item input[type="checkbox"] {
        margin-right: 10px;
        cursor: pointer;
    }

    .latam-quote-number {
        font-weight: 500;
        color: #333;
        margin-right: 10px;
        width: 20%;
    }

    .latam-country-badge {
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 500;
        color: #2F353A;
        border: 1px solid #E7E7E7;
        width: 90px;
        height: 24.52px;
        border-radius: 4px;
        text-align: center;
    }

    /* Estilos específicos de países eliminados - ahora todos usan el estilo minimalista */

    .latam-no-results {
        padding: 15px 12px;
        text-align: center;
        color: #666;
        font-style: italic;
    }

    /* Loading indicator styles */
    .latam-loading {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px 12px;
        gap: 10px;
        color: #666;
        font-size: 14px;
    }

    .latam-spinner {
        width: 20px;
        height: 20px;
        border: 2px solid #f3f3f3;
        border-top: 2px solid #A71B20;
        border-radius: 50%;
        animation: latam-spin 1s linear infinite;
    }

    @keyframes latam-spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    /* Estilos para las tarjetas de cotizaciones seleccionadas */
    .latam-selected-quotes {
        margin-top: 20px;
    }

    .latam-quote-card {
        background: #FFFFFF;
        border: 1px solid #E5E7EB;
        border-radius: 8px;
        padding: 15px 20px;
        margin-bottom: 12px;
        position: relative;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .latam-remove-quote {
        position: absolute;
        top: 10px;
        right: 10px;
        background: none;
        border: none;
        color: #9CA3AF;
        cursor: pointer;
        font-size: 18px;
        padding: 5px;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        transition: all 0.2s ease;
    }

    .latam-remove-quote:hover {
        background: #FEE2E2;
        color: #DC2626;
    }

    .latam-card-content {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        width: 90%;
    }

    .latam-card-left {
        flex: 0 0 25%;
        margin-left: 20px;
    }

    .latam-quote-number-card {
        font-weight: 600;
        font-size: 16px;
        color: #2F353A;
        margin-bottom: 10px;
    }

    .latam-user-info {
        display: flex;
        align-items: center;
        color: #828282;
        font-size: 13px;
        gap: 10px;
    }

    .latam-user-icon {
        margin-right: 6px;
        font-size: 11px;
    }

    .latam-card-center {
        padding: 0 15px;
        display: flex;
        flex-direction: column;
        text-align: center;
    }

    .latam-start-date {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 4px;
    }

    .latam-start-date svg {
        flex-shrink: 0;
        vertical-align: middle;
        margin-top: -1px;
        /* Ajuste fino para alineación perfecta */
    }

    .latam-start-date-text {
        color: #7E8285;
        font-size: 14px;
        font-weight: 600;
        line-height: 1;
    }

    .latam-duration {
        color: #7E8285;
        font-size: 11px;
        font-weight: 400;
        line-height: 1;
    }

    .latam-date-icon {
        margin-right: 6px;
        font-size: 11px;
        color: #7E8285;
    }

    .latam-created-date {
        color: #828282;
        font-size: 12px;
        margin-bottom: 4px;
        font-weight: 400;
    }

    .latam-expiry-date {
        color: #828282;
        font-size: 10px;
        font-weight: 400;
    }

    .latam-card-right {
        flex: 0 0 30%;
        text-align: right;
    }

    .latam-badges-row {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 10px;
        flex-wrap: wrap;
    }

    .latam-country-badge-card {
        background-color: white;
        border: 1px solid #E7E7E7;
        color: #2F353A;
        font-weight: 500;
        font-size: 14px;
        width: 90px;
        height: 24.52px;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: -4px;
    }

    .latam-mkp-badge {
        padding: 6px 4px;
        border-radius: 5px;
        font-size: 14px;
        font-weight: 500;
        background-color: #FFE1E1;
        color: #BD0D12;
        height: 29px;
        width: 83.54px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .latam-total {
        color: #BABCBD;
        font-size: 12px;
        font-weight: 400;
        margin-top: 4px;
    }

    .latam-total-amount {
        font-weight: 600;
        color: #2F353A;
        font-size: 16px;
    }

    .latam-consolidate-btn {
        background-color: #BD0D12;
        border-color: #BD0D12;
        font-weight: 500;
        border-radius: 6px;
        color: white;
        height: 45px;
        padding: 0px 20px;
        line-height: 2;
    }

    .latam-consolidate-btn:hover:not(:disabled) {
        background-color: #BDBDBD;
        border-color: #BDBDBD;
    }

    .latam-consolidate-btn:disabled {
        background-color: #BDBDBD;
        border-color: #BDBDBD;
        color: #E4E5E6;
        cursor: not-allowed;
    }

    .latam-consolidate-btn i {
        margin-right: 8px;
    }

    .modal-title {
        font-weight: 600;
        font-size: 20px;
        color: #18181B;
    }

    .gap-10 {
        gap: 10px;
        padding: 10px 0;
    }

    .hr {
        margin: 10px 0;
        border: 1px solid #18181B0F;
        text-align: center;
    }

    .page-cotizacion li.producto>.row {
        width: 100%;
    }

    input::placeholder {
        color: #101010;
        font-size: 14px;
        font-weight: bold;
        opacity: 1;
    }
</style>
@endsection
@section('js')
<script src="https://unpkg.com/vue-multiselect@2.1.0"></script>
<link rel="stylesheet" href="https://unpkg.com/vue-multiselect@2.1.0/dist/vue-multiselect.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/js-cookie/3.0.1/js.cookie.min.js"
    integrity="sha512-wT7uPE7tOP6w4o28u1DN775jYjHQApdBnib5Pho4RB0Pgd9y7eSkAV1BTqQydupYDB9GBhTcQQzyNMPMV3cAew=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    // import Multiselect from 'vue-multiselect';
    // import Multiselect from '@vueform/multiselect'

    new Vue({
        el: '#app',
        components: {
            Multiselect: window.VueMultiselect.default
        },
        data: {
            grouped_code_selected: '',
            show_dateIn: false,
            blockPage: false,
            user: null,
            quote_id: null,
            extensions: [],
            loading: false,
            loadingModal: false,
            quote_open: '',
            quote_name: '',
            quote_date: '',
            quote_date_estimated: '',
            drag: false,
            editService: false,
            baseExternalURL: window.baseExternalURL,
            checkMoveServiceUpdated: false,
            switchValue: false,
            showLatamModal: false,
            selectedQuote: '',
            showInfoMessage: true,
            searchQuery: '',
            showDropdown: false,
            selectedQuotes: [],
            allQuotes: [],
            filteredQuotes: [],
            searchLoading: false,
            searchTimeout: null,
            showQuantityPassengers: false,
            categoryPassengers: [],
            categoryRanges: [],
            new_name_quote: '',
            packages_search_category: [],
            service_selected: {
                single: '',
                double: '',
                double_child: '',
                triple: '',
                triple_child: '',
                experiences: [],
                restrictions: [],
                descriptions: {}
            },
            service_detail_selected: {
                experiences: [],
                restrictions: [],
                descriptions: {},
                inclusions_front: []
            },
            service_selected_general: {
                single: 0,
                double: 0,
                double_child: 0,
                triple: 0,
                triple_child: 0
            },
            control_service_selected_general: {
                single: 0,
                double: 0,
                double_child: 0,
                triple: 0,
                triple_child: 0
            },
            service_old: '',
            categoryModalHotel: 'all',
            categoryModalService: '',

            originModalService: '',
            originModalService_select: [],
            originModalService_select_universe: [],
            originModalService_countries_select: [],
            originModalService_country: {
                code: 89,
                label: 'Perú'
            },
            originModalService_additional_select_universe: [],
            originModalService_additional_select: [],
            originModalService_district: '',

            destinationsModalService: '',
            destinationsModalService_select: [],
            destinationsModalService_select_universe: [],
            destinationsModalService_countries_select: [],
            destinationsModalService_country: {
                code: 89,
                label: 'Perú'
            },
            destinationsModalService_additional_select_universe: [],
            destinationsModalService_additional_select: [],
            destinationsModalService_district: '',

            destinationsModalHotel: '',
            destinationsModalHotel_select: [],
            destinationsModalHotel_select_universe: [],
            destinationsModalHotel_countries_select: [],
            destinationsModalHotel_country: {
                code: 'PE',
                label: 'Perú'
            },
            destinationsModalHotel_additional_select_universe: [],
            destinationsModalHotel_additional_select: [],
            destinationsModalHotel_district: '',

            nightsModalHotel: 1,
            _module: '/packages/cotizacion',
            add_service_date: '',
            add_service_words: '',
            add_extension_words: '',
            add_hotel_words: '',
            add_hotel_date: '',
            package_selected: [],
            categories: [],
            categories_selected: [],
            services_deleted: [],
            moreServices: [],
            moreHotels: [],
            checkboxs: [],
            edit_checkboxs: [],
            categoryActive: '',
            modePassenger: 1,
            repeatPassenger: 0,
            ranges: [{
                from: 1,
                to: 1,
                simple: 0,
                double: 0,
                triple: 0
            }],
            quantity_persons: {
                adults: 0,
                child: 0,
                ages_child: []
            },
            operation: 'ranges',
            passengers: [],
            note_comment: '',
            notes: [],
            note_response: '',
            checkedAllCategories: false,
            options: {
                format: 'ddd Do MMM YYYY',
                useCurrent: false,
                locale: 'es',
                minDate: moment().format('YYYY-MM-DD')
            },
            optionsR: {
                format: 'DD/MM/YYYY',
                useCurrent: false,
            },
            showFormResponse: false,
            currentOffset: 0,
            windowSize: 3,
            paginationFactor: 270,
            items: [{
                    name: 'Lima, Paracas y Nazca',
                    imageNum: 'foto0',
                    tag: ''
                },
                {
                    name: 'Cusco',
                    imageNum: 'foto1',
                    tag: ''
                },
                {
                    name: 'Arequipa',
                    imageNum: 'foto2',
                    tag: ''
                },
                {
                    name: 'Lima',
                    imageNum: 'foto3',
                    tag: ''
                },
                {
                    name: 'Puno',
                    imageNum: 'foto4',
                    tag: ''
                },
                {
                    name: 'Burma Superstar',
                    imageNum: 'foto5',
                    tag: ''
                },
                {
                    name: 'Salt and Straw',
                    imageNum: 'foto6',
                    tag: ''
                },
                {
                    name: 'Milano',
                    imageNum: 'foto7',
                    tag: ''
                },
                {
                    name: 'Tsing Tao',
                    imageNum: 'foto8',
                    tag: ''
                },
            ],
            add_extensions_date: moment().format('L'),
            extension_selected: null,
            service_selected_id: null,
            service_type_id: 1,
            type_class_id: null,
            type_class_ids: [],
            extension_replace: null,
            extension_type_class_replace: null,
            updateDatePickerQuote: 1,
            service_types: [],
            quote_service_type_id: '',
            modal_service_type_id: '',
            modal_service_number_of_guests: 0,
            permissions: {
                converttopackage: false,
                adddiscount: false,
                updatemarkup: false
            },
            markup: 0,
            markup_readonly: 0,
            withDiscard: false,
            service_categories: [],
            hotelSwapRates: [],
            quote_service_id_choosed: '',
            title_rates_hotel: 'Tarifas de Hotel',
            discount_detail: '',
            discount: 0,
            use_discount: false,
            hotelForReplace: '',
            check_replace_hotel: false,
            mnjNoTie: '',
            useOrdersShow: false,
            orderAuto: false,
            numPedAuto: '',
            relaciOrder: false,
            query_ordersPend: '',
            ordersPend: [],
            loaderOrders: false,
            btnRelaciOrder: false,
            codeFile: 99999,
            booking_code: 0,
            r_order: '',
            translations: {
                label: {},
                validations: {},
                messages: {}
            },
            allWordsS: true,
            allWordsH: true,
            wordsS: [],
            wordsH: [],
            view: 'itinerary',
            loading_reserve: false,
            loadingHotel: false,
            modal_services_pageChosen: 1,
            modal_services_limit: 10,
            modal_services_pages: [],
            categoryForCopy: '',
            category_packages: [],
            filter_by_category: [],
            filter_by_destiny: [],
            packages_original: [],
            check_status_all: true,
            filter_by_nights: [{
                    option: 1,
                    count: 0,
                    status: false
                },
                {
                    option: 2,
                    count: 0,
                    status: false
                },
                {
                    option: 3,
                    count: 0,
                    status: false
                }
            ],
            no_reload: false,
            new_order_related: '',
            readonly: false,
            general_politics: '',
            policies_cancellation: '',
            refPax: '',
            language_for_download: 'es',
            select_itinerary_with_cover: 'amazonas',
            select_itinerary_with_client_logo: '',
            serviceChoosen: '',
            discount_user_permission: '',
            timeoutSaveQuote: null,
            age_child: [],
            originModalFlight: '',
            destinationsModalFlight: '',
            add_flight_date: '',
            flight_type: 0,
            destinations_flights_origin: [],
            destinations_flights_destiny: [],
            codciu: '',
            update_passengers_first_time: true,
            message_edit_plan_rooms: '',
            service_notes: '',
            service_real_notes: '',
            similar_services: [],
            hotel_swap_single: 0,
            hotel_swap_double: 0,
            hotel_swap_triple: 0,
            services_optionals: {},
            check_promotion: false,
            baseURLPhoto: window.baseURL,
            errors_reservations: {
                hotels: [],
                services: []
            },
            loading_occupation: false,
            file: {
                file_code: '',
                file_reference: '',
                client: {
                    id: '',
                    code: '',
                    name: ''
                }
            },
            client_file_incorrect: false,
            has_file: false,
            editing_quote: false,
            editing_quote_user: null,
            hiddenLocked: false,
            force_fully_destroy_loading: false,
            loading_extension: false,
            loading_save_as: false,
            package_create: {
                id: null
            },
            service_active: {},
            loading_create_note: false,
            skeleton_file: {},
            show_details_file: false,
            date_from_promotion: '',
            language_id: 0,
            block_change_schedule: false,
            validateAgeChild: true,
            clicks_send_booking: 0,
            user_type_id: 0,
            passengers_service: {
                service_id: null,
                passengers: []
            },
            groups_for_delete: [],
            url_hotel_choose: 'replace',
            statements: {
                quote: {},
                client: {}
            },
            reminder: {
                flag_send: false,
                days: 1,
                flag_email: false,
                email: ''
            },
            reservationId: 0,
            email_error: false,
            distribution_passengers: [],
            idCliente: '',
            imagePortada: '',
            urlPortada: '',
            portadaName: '',
            caja: true,
            iconoX: false,
            updateImage: false,
            textoCliente: '',
            rq_hotels: [],
            package_extension: '',
            price_dynamic_amount: 0,
            price_dynamic_markup: 0,
            isEditingPrice: true,
            executeMounted: true,
            showMarkupList: false
        },

        async created() {
            this.user_type_id = localStorage.getItem('user_type_id')
            this.getPackagesSelected()
            this.getUser()
            this.initializeModalServiceNumberOfGuests();
            let modal_edit_new_quote_id = localStorage.getItem('modal_edit_new_quote_id') ? localStorage.getItem('modal_edit_new_quote_id') : 0
            console.log(modal_edit_new_quote_id);
            if (modal_edit_new_quote_id > 0) {
                this.executeMounted = false;
                await this.executeUpdateRateHpPull(localStorage.getItem('client_id'), [], modal_edit_new_quote_id);
                localStorage.setItem("modal_edit_new_quote_id", 0);
                this.iniciarDOM();

            }

        },
        async mounted() {
            if (this.executeMounted == true) {
                this.iniciarDOM();
            }
        },
        beforeDestroy() {
            // Remover el event listener al destruir el componente
            window.removeEventListener('storage', this.handleStorageChange);
            document.removeEventListener('click', this.closeDropdownOnOutsideClick);
        },
        destroyed: function() {
            clearInterval(this.timeoutSaveQuote)
        },
        computed: {
            isQuoteBlocked() {
                // Validar que quote_open sea un objeto válido antes de acceder a sus propiedades
                if (!this.quote_open || typeof this.quote_open !== 'object' || this.quote_open === null) {
                    return false;
                }
                // se reemplaza por el nuevo campo
                return (this.quote_open_is_multiregion == 1);
            },
            isMultiRegionSwitch() {

                if (this.quote_open.file_id === null) {
                    console.log("aquiiiiiiiiiiiiiiiiiiiiiii debia verser", this.quote_open.file_id);
                    console.log(
                        this.quote_open.file_id,
                        typeof this.quote_open.file_id
                    );
                    return true;
                }

                return false;
            },
            isMultiRegionQuote() {
                if (!this.quote_open) {
                    return true;
                }
                return (this.quote_open_is_multiregion == 1);
            },
            canConsolidate() {
                // Debe haber al menos 2 cotizaciones seleccionadas
                if (this.selectedQuotes.length < 1) {
                    return false;
                }
                // Debe haber al menos una cotización de Perú
                // const hasPeruQuote = this.selectedQuotes.some(quote => quote.country_iso === 'PE');
                // return hasPeruQuote;
                return true;
            },
            isDisabledReservation: function() {
                const response = parseInt(localStorage.getItem('client_disable_reservation') ?? 0);
                return (response === 1);
            },
            atEndOfList() {
                return this.currentOffset <= (this.paginationFactor * -1) * (this.items.length - this.windowSize)
            },
            atHeadOfList() {
                return this.currentOffset === 0
            },
            filterOrdersPend() {
                return this.ordersPend.filter(order => {
                    return order.NOMPAX.toLowerCase().includes(this.query_ordersPend.toLowerCase())
                })
            },
            select_passengers() {

                let passenger_select = [];
                let countAdul = 0;
                let countChild = 0;
                let name = '';

                this.passengers.forEach(passenger => {

                    name = '';
                    if (passenger.first_name || passenger.last_name) {
                        name = passenger.first_name + ' ' + passenger.last_name
                    } else {

                        if (passenger.type == 'ADL') {
                            countAdul++;
                            name = 'Adult ' + countAdul;
                        } else {
                            countChild++;
                            name = 'Child ' + countChild;
                        }
                    }

                    passenger_select.push({
                        code: passenger.id,
                        label: name
                    })
                })

                return passenger_select;
            },
            markupByCountry() {
                if (!this.quote_open || !this.quote_open.categories) {
                    return [];
                }

                const markupMap = new Map();

                this.quote_open.categories.forEach(category => {
                    if (category.services && category.services.length > 0) {
                        category.services.forEach(service => {
                            let countryName = '';
                            let markup = service.markup_regionalization || 0;

                            // Obtener país según el tipo de servicio
                            if (service.type === 'service' && service.service && service.service.service_origin && service.service.service_origin.length > 0) {
                                if (service.service.service_origin[0].country && service.service.service_origin[0].country.translations && service.service.service_origin[0].country.translations.length > 0) {
                                    countryName = service.service.service_origin[0].country.translations[0].value;
                                }
                            } else if ((service.type === 'hotel' || service.type === 'group_header') && service.hotel && service.hotel.country) {
                                if (service.hotel.country.translations && service.hotel.country.translations.length > 0) {
                                    countryName = service.hotel.country.translations[0].value;
                                }
                            }

                            // Si hay país y markup, agregar al mapa (si ya existe, mantener el primero o el que tenga markup)
                            if (countryName && countryName !== '') {
                                if (!markupMap.has(countryName) || markup > 0) {
                                    markupMap.set(countryName, markup);
                                }
                            }
                        });
                    }
                });

                // Convertir el Map a un array de objetos
                return Array.from(markupMap.entries()).map(([country, markup]) => ({
                    country: country,
                    markup: markup
                }));
            },
        },
        methods: {
            onSwitchChange() {
                console.log('Switch changed to:', this.switchValue);
                if (!this.switchValue) {
                    this.unmergeQuotes();
                } else {
                    // Switch desactivado - cerrar modal si está abierto
                    this.showLatamModal = true;
                    this.$refs.latamModal.show();
                }
            },

            async consolidateQuotes() {
                console.log(this.quote_open);
                if (!this.quote_open) {
                    await this.saveQuote(false);
                }
                await this.mergeQuotes();
            },

            async mergeQuotes() {
                this.loading = true;
                let client_id = localStorage.getItem('client_id');
                let selectedIds = this.selectedQuotes.map(quote => quote.id);
                let response = await axios.post(window.a3BaseQuoteServerURL + 'api/quote/merge', {
                    quotes: selectedIds
                });

                console.log("merge quotes ", response.data);
                // return false;

                if (response.data.success) {
                    // await this.replaceQuoteInFront(response.data.quote_new_id, client_id);
                    await this.putQuote(response.data.quote_new_id, client_id)
                } else {
                    this.loading = false;
                    let errors = response.data.errors;
                    // Si el mensaje es un arreglo, iterar sobre él y mostrar los mensajes
                    if (Array.isArray(errors)) {
                        errors.forEach((err) => {
                            this.$toast.error(err, {
                                position: 'top-right'
                            });
                        });
                    } else {
                        // Si el mensaje es un string, mostrarlo directamente
                        this.$toast.error(errors || 'Error al fusionar cotizaciones', {
                            position: 'top-right'
                        });
                    }
                }
            },

            async unmergeQuotes() {
                this.loading = true;
                let principal_quote_id = this.quote_open.id;
                let client_id = localStorage.getItem('client_id');

                let response = await axios.post(window.a3BaseQuoteServerURL + 'api/quote/' + principal_quote_id + '/unmerge', {
                    quote_id: principal_quote_id
                });

                console.log("unmerge quotes ", response.data);

                if (response.data.success) {
                    await this.replaceQuoteInFront(response.data.quote_father_id, client_id);
                } else {
                    this.$toast.error('Error al deshacer fusión de cotizaciones', {
                        position: 'top-right'
                    });
                }
            },

            async replaceQuoteInFront(quoteId, client_id) {
                let response = await axios.post(window.a3BaseQuoteServerURL + 'api/quote/' + quoteId + '/replaceQuoteInFront', {
                    client_id: client_id
                });

                console.log("Reemplazar quote in front", response);

                if (response.data.success) {
                    window.location.reload();
                } else {
                    this.$toast.error('Error al reemplazar cotización', {
                        position: 'top-right'
                    });
                }
            },

            closeLatamModal() {
                this.showLatamModal = false;
                this.switchValue = false;
                this.$refs.latamModal.hide();
            },

            removeQuote(quoteId) {
                const index = this.selectedQuotes.findIndex(q => q.id === quoteId);
                if (index > -1) {
                    this.selectedQuotes.splice(index, 1);
                }
            },

            dismissInfo() {
                this.showInfoMessage = false;
            },

            // Métodos para el componente de búsqueda
            filterQuotes() {
                // Limpiar timeout anterior
                if (this.searchTimeout) {
                    clearTimeout(this.searchTimeout);
                }

                // Si no hay query, mostrar todas las cotizaciones (excluyendo la original)
                if (this.searchQuery.trim() === '') {
                    // this.filteredQuotes = this.allQuotes.filter(q => q.id !== this.quote_open.id_original);
                    this.filteredQuotes = this.allQuotes;
                    this.searchLoading = false;
                    return;
                }

                // Mostrar loading
                this.searchLoading = true;

                // Debounce: esperar 500ms antes de hacer la búsqueda
                this.searchTimeout = setTimeout(async () => {
                    try {
                        let response = await axios.get(window.a3BaseQuoteServerURL + 'api/quote/searchById', {
                            params: {
                                'page': 1,
                                'limit': 15,
                                'filterBy': 'all',
                                'queryCustom': this.searchQuery.trim(),
                                'destinations': "",
                                'market': '',
                                'client': '',
                                'executive': '',
                                'original_quote_id': this.quote_open.id_original
                            }
                        });

                        if (response.data.success) {
                            // Excluir la cotización original del listado
                            // this.filteredQuotes = response.data.data.filter(q => q.id !== this.quote_open.id_original);
                            this.filteredQuotes = response.data.data;
                        } else {
                            this.filteredQuotes = [];
                        }
                    } catch (error) {
                        console.error('Error en la búsqueda:', error);
                        this.filteredQuotes = [];
                    } finally {
                        this.searchLoading = false;
                    }
                }, 500);
            },

            clearSearch() {
                // Limpiar timeout si existe
                if (this.searchTimeout) {
                    clearTimeout(this.searchTimeout);
                }

                this.searchQuery = '';
                this.filteredQuotes = this.allQuotes;
                this.searchLoading = false;
            },

            toggleDropdown() {
                this.showDropdown = !this.showDropdown;
                if (this.showDropdown && this.filteredQuotes.length === 0) {
                    this.filteredQuotes = this.allQuotes;
                }
            },

            async toggleQuoteSelection(quote) {
                const index = this.selectedQuotes.findIndex(q => q.id === quote.id);
                if (index > -1) {
                    this.selectedQuotes.splice(index, 1);
                } else {
                    this.loading = true
                    await axios.post(window.a3BaseQuoteServerURL + 'api/quote/' + quote.id + '/statements', {
                        client_id: localStorage.getItem('client_id'),
                        type_class_id: quote.type_class_id
                    }).then(response => {
                        quote.total = response.data.total
                        this.selectedQuotes.push(quote);
                        this.searchQuery = ''
                        this.loading = false
                    })

                }
            },

            isQuoteSelected(quoteId) {
                return this.selectedQuotes.some(q => q.id === quoteId);
            },

            // Inicializar cotizaciones filtradas
            initFilteredQuotes() {
                this.filteredQuotes = this.allQuotes;
            },

            closeDropdownOnOutsideClick(event) {
                if (!event.target.closest('.latam-search-container')) {
                    this.showDropdown = false;
                }
            },

            handleInputBlur() {
                // Usar setTimeout para permitir que los clics en los items del dropdown funcionen
                setTimeout(() => {
                    if (!document.activeElement.closest('.latam-search-container')) {
                        this.showDropdown = false;
                    }
                }, 200);
            },

            handleStorageChange(event) {
                if (event.key === 'client_code') {
                    window.location.reload()
                    // this.validate_client_file(event.newValue);
                }
            },
            validate_client_file() {
                let client_select = localStorage.getItem('client_code')
                console.log("No toy disponible")
                console.log(client_select)
                console.log(this.quote_open.reservation)
                if (this.quote_open?.reservation?.client_code) {
                    console.log(this.quote_open?.reservation?.client_code)
                }

                if (this.quote_open != '' && this.quote_open?.reservation?.client_code &&
                    client_select != this.quote_open?.reservation?.client_code) {
                    this.client_file_incorrect = true
                    console.log("bloquea")
                } else {
                    this.client_file_incorrect = false
                    console.log("NO block")
                }

            },

            getSortedSchedules(service) {
                return [...service.service.schedules].sort((a, b) => {
                    const dayIndex = this.getDayIndex(service.date_in);
                    const timeA = a[dayIndex]?.ini;
                    const timeB = b[dayIndex]?.ini;

                    if (!timeA || !timeB) return 0;

                    return new Date(timeA) - new Date(timeB);
                });
            },
            onScheduleChange(event, service) {
                const selectedHourValue = event.target.value; // Obtiene el valor seleccionado
                const selectedIndex = service.service.schedules.findIndex(horary =>
                    horary[this.getDayIndex(service.date_in)].ini === selectedHourValue
                );
                if (selectedIndex !== -1) {
                    this.change_schedule(service, selectedIndex); // Llama a la función con el índice encontrado
                }
            },
            getDayIndex(date) {
                const dayOfWeek = moment(date, 'DD/MM/YYYY').day(); // 0 (Sunday) - 6 (Saturday)
                const daysMap = {
                    1: 0, // Monday
                    2: 1, // Tuesday
                    3: 2, // Wednesday
                    4: 3, // Thursday
                    5: 4, // Friday
                    6: 5, // Saturday
                    0: 6 // Sunday
                };
                return daysMap[dayOfWeek];
            },
            initializeModalServiceNumberOfGuests() {
                let adults = 2;

                if (this.operation === "passengers") {
                    adults = this.quantity_persons.adults;
                } else if (this.ranges) {
                    adults = this.ranges[0] ? this.ranges[0].from : adults;
                }

                this.modal_service_number_of_guests = adults;
            },
            reducirImage() {
                if (this.iconoX == true) {
                    this.iconoX = false
                    let zoom = document.getElementById('zoomImage');
                    zoom.classList.remove('zooImge');
                }

            },

            zoomImage() {
                this.iconoX = true
                let zoom = document.getElementById('zoomImage');
                zoom.classList.add('zooImge');

            },

            clickDropDown(e) {

                e.stopPropagation();

            },
            downloadDropdown() {
                /*
                const requiredMarkets = [4, 19, 20];
                const markets = this.quote_open && Array.isArray(this.quote_open.markets)
                ? this.quote_open.markets
                : [];
                const allMarkets = requiredMarkets.every(num => markets.includes(num));
                */
                if (!this.new_order_related) {
                    this.$toast.error('Por favor ingrese el número de orden', {
                        position: 'top-right'
                    });
                    return;
                } else {
                    this.backMiniMenu();
                    this.updateImage = false;
                    this.quote_open.withClientLogo = 3;
                    this.quote_open.withHeader = true;
                    this.reducirImage()
                }

            },
            grouped_toggle(service) {
                let show = service.grouped_show ? false : true;
                this.quote_open.categories.forEach(c => {
                    c.services.forEach(s => {
                        if (service.grouped_code === s.grouped_code) {
                            s.grouped_show = show
                        }
                    })
                })
            },
            change_hour_in(quote_service) {

                let data = {
                    hour_in: quote_service.hour_in,
                }
                axios.put(window.a3BaseQuoteServerURL + 'api/quote/services/' + quote_service.id + '/hour_in', data).then(response => {
                    if (response.data.success) {
                        this.$toast.success('Actualizado Correctamente', {
                            position: 'top-right'
                        })

                    } else {
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right'
                        })
                    }
                }).catch(error => {
                    this.$toast.error(this.translations.messages.internal_error, {
                        position: 'top-right'
                    })
                    // console.log(error)
                })
            },
            change_schedule(quote_service, schedule_index) {
                let id_parent_ = quote_service.service.schedules[schedule_index][0].id_parent
                if (id_parent_ === quote_service.schedule_id) {
                    // console.log('ningún cambio por hacer')
                    return
                }
                if (this.block_change_schedule) {
                    // console.log('ya en ejecución, espere por favor')
                    return
                }
                this.block_change_schedule = true

                let data = {
                    schedule_id: id_parent_,
                }
                axios.put(window.a3BaseQuoteServerURL + 'api/quote/services/' + quote_service.id + '/schedule', data).then(response => {
                    if (response.data.success) {
                        this.$toast.success('Actualizado Correctamente', {
                            position: 'top-right'
                        })
                        // day_choosed
                        quote_service.schedule_id = id_parent_

                        let day_code_ = ''
                        quote_service.service.schedules.forEach((schls) => {
                            schls.forEach((sch) => {
                                if (sch.day_choosed) {
                                    sch.day_choosed = false
                                    day_code_ = sch.code
                                }
                            })
                        })
                        // el nuevo elegido
                        quote_service.service.schedules.forEach((schls) => {
                            schls.forEach((sch) => {
                                if (sch.id_parent == id_parent_ && sch.code === day_code_) {
                                    sch.day_choosed = true
                                    quote_service.hour_in = sch.ini
                                }
                            })
                        })

                    } else {
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right'
                        })
                    }
                    // this.searchQuoteOpen(this.categoryActive.id)
                    this.block_change_schedule = false
                }).catch(error => {
                    this.$toast.error(this.translations.messages.internal_error, {
                        position: 'top-right'
                    })
                    // console.log(error)
                    this.block_change_schedule = false
                })

            },
            getPromotionsData: function(promotions_data, option) {
                // var date_from = this.date_from_promotion
                var date_from = moment().format('Y-MM-DD')
                // console.log(date_from, this.date_from_promotion);

                for (let i = 0; i < promotions_data.length; i++) {
                    if (option == 1) {
                        if (promotions_data[i].promotion_from <= date_from && promotions_data[i].promotion_to >= date_from) {
                            return promotions_data[i].promotion_from
                        }
                    }
                    if (option == 2) {
                        if (promotions_data[i].promotion_from <= date_from && promotions_data[i].promotion_to >= date_from) {
                            return promotions_data[i].promotion_to
                        }
                    }
                }
            },
            showDetailsFile: function() {
                this.show_details_file = !this.show_details_file
            },
            validModal: function() {
                setTimeout(function() {
                    $('body').addClass('modal-open')
                }, 10)
            },
            closeOthersPopovers: function() {
                this.$root.$emit('bv::hide::popover')
            },
            async update_all_ranges() {

                if (!this.quote_id) {
                    setTimeout(() => {
                        let el = document.getElementById('dropdown_rango')
                        el.click()
                    }, 10)

                    return false;
                }

                this.loading = true

                // await this.ranges.forEach((r,index) => {
                //      this.updateRange(r,index)
                // })

                await axios.post(window.a3BaseQuoteServerURL + 'api/quote/ranges/save', {
                    quote_id: this.quote_id,
                    ranges: this.ranges
                }).then(response => {
                    this.ranges = response.data.data
                })


                if (this.quote_open.operation === 'passengers') {

                    this.quantity_persons.adults = 0
                    await this.generatePassenger(true, true);

                    this.control_service_selected_general.single = 1;
                    this.control_service_selected_general.double = 1;
                    this.control_service_selected_general.triple = 1;
                    await this.updateOccupationHotelGeneral();

                } else {
                    await this.searchQuoteOpen(this.categoryActive.id)
                }

                this.loading = false

                this.$toast.success(this.translations.messages.saved_correctly, {
                    position: 'top-right'
                })

                setTimeout(() => {
                    let el = document.getElementById('dropdown_rango')
                    el.click()
                }, 10)


            },
            cancela_ranger() {

                this.$dialog.confirm(this.translations.label.cancel_ranges, {
                        okText: this.translations.label.confirm_yes,
                        cancelText: this.translations.label.confirm_no,
                    })
                    .then(dialog => {
                        this.quantity_persons.adults = 2
                        this.generatePassenger(true);
                    })
                    .catch(() => {
                        this.blockPage = false
                    })

            },
            unzip_destination_hotels(data) {
                // DESTINIES
                this.destinationsModalHotel_countries_select = []
                let destinations_countries_select_ = []
                this.destinationsModalHotel_select_universe = []
                let destinations_select_ = []
                this.destinationsModalHotel_additional_select_universe = []

                data.forEach((d) => {
                    let code_split = d.code.split(',')
                    let label_split = d.label.split(',')

                    if (destinations_countries_select_[code_split[0]] === undefined) {
                        this.destinationsModalHotel_countries_select.push({
                            code: code_split[0],
                            label: label_split[0].trim()
                        })
                        destinations_countries_select_[code_split[0]] = true
                    }

                    if (destinations_select_[code_split[1]] === undefined) {
                        this.destinationsModalHotel_select_universe.push({
                            code: code_split[1],
                            label: (label_split[1]) ? label_split[1].trim() : '',
                            parent_code: code_split[0]
                        })
                        destinations_select_[code_split[1]] = true
                    }

                    let code_for_split = code_split[0] + ',' + code_split[1]
                    let label_for_split = label_split[0] + ',' + label_split[1]
                    let code_add_split = d.code.split(code_for_split)
                    let label_add_split = d.label.split(label_for_split)
                    if (code_add_split.length > 1) {
                        if (code_add_split[1].trim() !== '') {
                            this.destinationsModalHotel_additional_select_universe.push({
                                code: code_add_split[1].substring(1),
                                label: label_add_split[1].substring(1).trim(),
                                parent_code: code_split[1]
                            })
                        }
                    }
                })
                this.change_hotel_destiny_cities()
            },
            change_hotel_destiny_cities() {
                this.destinationsModalHotel_additional_select = []
                this.destinationsModalHotel_select = []
                this.destinationsModalHotel = ''
                this.destinationsModalHotel_select_universe.forEach((d_u) => {
                    if (d_u.parent_code == this.destinationsModalHotel_country.code) {
                        this.destinationsModalHotel_select.push(d_u)
                    }
                })
            },
            change_hotel_destiny_districts() {
                this.destinationsModalHotel_additional_select = []
                this.destinationsModalHotel_district = ''
                this.destinationsModalHotel_additional_select_universe.forEach((o_u) => {
                    if (o_u.parent_code == this.destinationsModalHotel.code) {
                        this.destinationsModalHotel_additional_select.push(o_u)
                    }
                })
            },
            unzip_origin_services(data) {
                this.originModalService_countries_select = []
                let destinations_countries_select_ = []
                this.originModalService_select_universe = []
                let destinations_select_ = []
                this.originModalService_additional_select_universe = []
                data.forEach((d) => {
                    let code_split = d.code.split(',')
                    let label_split = d.label.split(',')

                    if (destinations_countries_select_[code_split[0]] === undefined) {
                        this.originModalService_countries_select.push({
                            code: code_split[0],
                            label: label_split[0].trim()
                        })
                        destinations_countries_select_[code_split[0]] = true
                    }

                    if (destinations_select_[code_split[1]] === undefined) {
                        this.originModalService_select_universe.push({
                            code: code_split[1],
                            label: (label_split[1]) ? label_split[1].trim() : '',
                            parent_code: code_split[0]
                        })
                        destinations_select_[code_split[1]] = true
                    }

                    let code_for_split = code_split[0] + ',' + code_split[1]
                    let label_for_split = label_split[0] + ',' + label_split[1]
                    let code_add_split = d.code.split(code_for_split)
                    let label_add_split = d.label.split(label_for_split)
                    if (code_add_split.length > 1) {
                        if (code_add_split[1].trim() !== '') {
                            this.originModalService_additional_select_universe.push({
                                code: code_add_split[1].substring(1),
                                label: label_add_split[1].substring(1).trim(),
                                parent_code: code_split[1]
                            })
                        }
                    }
                })
                this.change_service_origin_cities()
            },
            change_service_origin_cities() {
                this.originModalService_additional_select = []
                this.originModalService_select = []
                this.originModalService = ''
                this.originModalService_select_universe.forEach((d_u) => {
                    if (d_u.parent_code == this.originModalService_country.code) {
                        this.originModalService_select.push(d_u)
                    }
                })
            },
            change_service_origin_districts() {
                this.originModalService_additional_select = []
                this.originModalService_district = ''
                this.originModalService_additional_select_universe.forEach((o_u) => {
                    if (o_u.parent_code == this.originModalService.code && o_u.code.trim() !== ',') {
                        this.originModalService_additional_select.push(o_u)
                    }
                })
            },
            unzip_destiny_services(data) {
                this.destinationsModalService_countries_select = []
                let destinations_countries_select_ = []
                this.destinationsModalService_select_universe = []
                let destinations_select_ = []
                this.destinationsModalService_additional_select_universe = []

                data.forEach((d) => {
                    let code_split = d.code.split(',')
                    let label_split = d.label.split(',')

                    if (destinations_countries_select_[code_split[0]] === undefined) {
                        this.destinationsModalService_countries_select.push({
                            code: code_split[0],
                            label: label_split[0].trim()
                        })
                        destinations_countries_select_[code_split[0]] = true
                    }

                    if (destinations_select_[code_split[1]] === undefined) {
                        this.destinationsModalService_select_universe.push({
                            code: code_split[1],
                            label: (label_split[1]) ? label_split[1].trim() : '',
                            parent_code: code_split[0]
                        })
                        destinations_select_[code_split[1]] = true
                    }

                    let code_for_split = code_split[0] + ',' + code_split[1]
                    let label_for_split = label_split[0] + ',' + label_split[1]
                    let code_add_split = d.code.split(code_for_split)
                    let label_add_split = d.label.split(label_for_split)
                    if (code_add_split.length > 1) {
                        if (code_add_split[1].trim() !== '') {
                            this.destinationsModalService_additional_select_universe.push({
                                code: code_add_split[1].substring(1),
                                label: label_add_split[1].substring(1).trim(),
                                parent_code: code_split[1]
                            })
                        }
                    }
                })
                this.change_service_destiny_cities()
            },
            change_service_destiny_cities() {
                this.destinationsModalService_additional_select = []
                this.destinationsModalService_select = []
                this.destinationsModalService = ''
                this.destinationsModalService_select_universe.forEach((d_u) => {
                    if (d_u.parent_code == this.destinationsModalService_country.code) {
                        this.destinationsModalService_select.push(d_u)
                    }
                })
            },
            change_service_destiny_districts() {
                this.destinationsModalService_additional_select = []
                this.destinationsModalService_district = ''
                this.destinationsModalService_additional_select_universe.forEach((o_u) => {
                    if (o_u.parent_code == this.destinationsModalService.code && o_u.code.trim() !== ',') {
                        this.destinationsModalService_additional_select.push(o_u)
                    }
                })
            },
            /**
             * @returns {boolean} | true === con errores activados
             */
            verify_itinerary_errors() {

                let have_errors = 0

                this.quote_open.categories.forEach(c => {
                    if (c.tabActive == 'active') {
                        c.services.forEach(s => {
                            // console.log(s.validations.length, !s.locked, s.total_accommodations)
                            if (s.validations.length > 0 && !s.locked && s.total_accommodations > 0) {
                                let have_validation_true = 0
                                s.validations.forEach((v) => {
                                    if (v.validation) {
                                        have_validation_true++
                                    }
                                })
                                if (have_validation_true > 0) {
                                    have_errors++
                                }
                            }
                        })
                    }
                })
                // console.log(have_errors)
                if (have_errors > 0 && this.quote_open.operation === 'passengers') {
                    return true
                }

                if (this.quote_open.operation === 'ranges') {
                    return false
                }

                this.quote_open.categories.forEach(c => {
                    c.services.forEach(s => {
                        if (s.type === 'hotel' && !s.locked && s.total_accommodations > 0) {
                            if (this.verify_type_rooms(s) === true) {
                                have_errors++
                            }
                            // if (s.single == 0 && s.double == 0 && s.triple == 0) {
                            //     have_errors++
                            // }
                        }
                    })
                })
                return (have_errors > 0) ? true : false
            },
            /**
             * @param hotel
             * @returns {boolean} | true === con errores activados
             */
            verify_type_rooms(hotel) {
                if (this.quote_open.operation === 'ranges' || hotel.hyperguest_pull == '1') {
                    return false
                }

                if (hotel.single > 0) {
                    let validate_sgl = false
                    hotel.service_rooms.forEach(s_r => {
                        if (s_r.rate_plan_room != null && s_r.rate_plan_room.room.room_type.occupation === 1) {
                            validate_sgl = true
                        }
                    })
                    if (!validate_sgl) {
                        return true
                    }
                }
                if (hotel.double > 0) {
                    let validate_dbl = false
                    hotel.service_rooms.forEach(s_r => {
                        if (s_r.rate_plan_room != null && s_r.rate_plan_room.room.room_type.occupation === 2) {
                            validate_dbl = true
                        }
                    })
                    if (!validate_dbl) {
                        return true
                    }
                }
                if (hotel.triple > 0) {
                    let validate_tpl = false
                    hotel.service_rooms.forEach(s_r => {
                        if (s_r.rate_plan_room != null && s_r.rate_plan_room.room.room_type.occupation === 3) {
                            validate_tpl = true
                        }
                    })
                    if (!validate_tpl) {
                        return true
                    }
                }

                return false
            },
            show_occupation_modal() {

                this.control_service_selected_general.single = this.service_selected_general.single;
                this.control_service_selected_general.double = this.service_selected_general.double;
                this.control_service_selected_general.triple = this.service_selected_general.triple;
                this.$refs['modal_occupation_hotel'].show()
            },
            show_hotel_modal() {
                this.$refs['modal-hotel'].show()
            },
            setWithClientLogo: function(quote_open) {
                this.quote_open.withClientLogo = false

            },
            setWithHeader: function(quote_open) {

                if (this.quote_open.withClientLogo == 1) {
                    this.imagePortada = ''
                    this.loading = true
                    this.caja = true
                    this.idCliente = localStorage.getItem('client_id')
                    this.select_itinerary_with_client_logo = this.select_itinerary_with_cover;

                    axios.get(window.a3BaseQuoteServerURL + 'api/quote/imageCreate', {
                        params: {
                            clienteId: this.idCliente,
                            portada: this.select_itinerary_with_client_logo,
                            //portadaName:quote_open.name,
                            portadaName: this.quote_name,
                            estado: quote_open.withClientLogo,
                            refPax: this.refPax,
                            lang: localStorage.getItem('lang'),
                            nameCliente: this.refPax,
                        }
                    }).then((result) => {

                        this.imagePortada = window.a3BaseQuoteServerURL + result.data.image + '.jpg'
                        this.caja = false
                        this.loading = false
                        this.urlPortada = result.data.image + '.jpg';
                    });

                    this.quote_open.withHeader = true


                } else if (this.quote_open.withClientLogo == 2) {

                    this.quote_open.withHeader = true
                    this.loading = true
                    this.caja = true
                    this.imagePortada = '';
                    this.idCliente = localStorage.getItem('client_id')

                    axios.get(window.a3BaseQuoteServerURL + 'api/quote/imageCreate', {
                        params: {
                            clienteId: this.idCliente,
                            portada: this.select_itinerary_with_cover,
                            portadaName: quote_open.name,
                            estado: quote_open.withClientLogo,
                            refPax: this.refPax,
                            lang: localStorage.getItem('lang'),
                            nameCliente: this.refPax,
                        }
                    }).then((result) => {

                        this.imagePortada = window.a3BaseQuoteServerURL + result.data.image + '.jpg'
                        this.caja = false
                        this.loading = false
                        this.urlPortada = result.data.image + '.jpg';

                    });

                } else {
                    this.imagePortada = ''
                    this.select_itinerary_with_client_logo = ""
                    this.quote_open.withHeader = true

                    this.loading = true
                    this.caja = true
                    this.idCliente = localStorage.getItem('client_id')

                    axios.get(window.a3BaseQuoteServerURL + 'api/quote/imageCreate', {
                        params: {
                            clienteId: this.idCliente,
                            portada: this.select_itinerary_with_cover,
                            portadaName: quote_open.name,
                            estado: quote_open.withClientLogo,
                            refPax: this.refPax,
                            lang: localStorage.getItem('lang'),
                            nameCliente: this.refPax,
                        }
                    }).then((result) => {

                        this.imagePortada = window.a3BaseQuoteServerURL + result.data.image + '.jpg'
                        this.caja = false
                        this.loading = false
                        this.urlPortada = result.data.image + '.jpg';

                    });

                }

                if ((this.refPax.trim()).toUpperCase() !== (localStorage.getItem('client_name').trim()).toUpperCase()) {

                    this.updateImage = true
                    this.textoCliente = this.refPax

                }
            },
            updateOptional: function(service, indexCateg) {

                this.loading = true
                let data = {
                    optional: service.optional,
                    quote_service_id: service.type == 'service' ? service.id : this.quote_open.categories[indexCateg].services.filter((item) => (item.group == service.group && item.type == 'hotel')).map(ele => ele.id)
                }

                axios.put(window.a3BaseQuoteServerURL + 'api/quote/optional', data).then(response => {
                    if (service.optional == 1) {
                        if (service.type == 'service') {
                            this.$set(service, 'optional', 0)

                        } else {
                            this.quote_open.categories[indexCateg].services.forEach((item, i) => {
                                if (item.group == service.group) {
                                    this.quote_open.categories[indexCateg].services[i].optional = 0;
                                }
                            })
                        }

                    } else {
                        if (service.type == 'service') {
                            this.$set(service, 'optional', 1)
                        } else {
                            this.quote_open.categories[indexCateg].services.forEach((item, i) => {
                                if (item.group == service.group) {
                                    this.quote_open.categories[indexCateg].services[i].optional = 1;
                                }
                            })
                        }
                    }
                    // this.searchQuoteOpen(this.categoryActive.id)
                    this.loading = false
                }).catch(error => {
                    this.$toast.error(this.translations.messages.internal_error, {
                        position: 'top-right'
                    })
                    // console.log(error)
                    this.loading = false
                })
            },
            validateService: function(service) {
                if (service.optional == 1) {
                    return 'background-color:#ccc!important'
                } else {
                    return ''
                }
                this.$forceUpdate()
            },
            updateAgeChild: function(age) {
                this.loading = true
                let data = {
                    age_child: age
                }
                axios.put(window.a3BaseQuoteServerURL + 'api/quote/age_child', data).then(response => {
                    this.searchQuoteOpen(this.categoryActive.id)
                    this.loading = false
                }).catch(error => {
                    this.$toast.error(this.translations.messages.internal_error, {
                        position: 'top-right'
                    })
                    // console.log(error)
                    this.loading = false
                })
            },
            sendMailForPermission() {
                this.loading = true
                let data = {
                    user_code: this.discount_user_permission
                }
                axios.post(window.a3BaseQuoteServerURL + 'api/quote/' + this.quote_open.id + '/discountPermissionMail', data)
                    .then(response => {
                        // console.log(response)
                        if (response.data.success) {
                            this.loading = false
                            this.goExport()
                        } else {
                            // Por favor asegúrese que el código de usuario exista o contacte con su administrador
                            this.$toast.error(this.translations.validations.please_make_sure_the_user_code_exists, {
                                position: 'top-right'
                            })
                            this.loading = false
                        }
                    }).catch(error => {
                        // console.log(error)
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right'
                        })
                    })
            },
            willGoExport() {
                if (this.use_discount && this.discount > 3) {
                    this.sendMailForPermission()
                } else {
                    this.goExport()
                }
            },
            goExport() {

                if (this.verify_itinerary_errors()) {
                    this.$toast.warning(this.translations.label.observations_validation_text, {
                        position: 'top-right'
                    })
                    return
                }

                if (this.quote_open.operation === 'passengers') {
                    this.blockPage = true
                    this.loading = true
                    axios.put(window.a3BaseQuoteServerURL + 'api/quote/me', {
                        quote_id: this.quote_id,
                        client_id: localStorage.getItem('client_id'),
                        category_id: this.categoryActive.id

                    }).then(response => {
                        this.getRouteExport()
                        this.blockPage = false
                        this.loading = false
                    }).catch(error => {
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right'
                        })
                        this.loading = false
                        this.blockPage = false
                    })
                } else {
                    this.getRouteExport()
                }

            },
            willRemoveService(category, service) {
                this.$refs['modalWillRemoveService'].show()
                this.similar_services = []

                // if (service.type !== 'group_header') {
                //     this.quote_open.categories.forEach((c) => {

                //         this.similar_services.push({
                //             category_id: c.id,
                //             category_name: c.type_class.translations[0].value,
                //             services: []
                //         })

                //         c.services.forEach((s) => {
                //             if (s.type === service.type && s.id !== service.id) {
                //                 if (s.type === 'flight') {
                //                     if (s.code_flight === service.code_flight) {
                //                         this.similar_services[this.similar_services.length - 1]
                //                             .services.push({
                //                             id: s.id,
                //                             extension_id: s.extension_id,
                //                             quote_category_id: s.quote_category_id,
                //                             code: (s.code_flight !== null) ? s.code_flight : '-',
                //                             name: 'Flight',
                //                             date: s.date_in,
                //                             date_in: s.date_in,
                //                             type: s.type,
                //                             object_id: s.object_id,
                //                             check: (s.date_in === service.date_in),
                //                         })
                //                     }
                //                 }
                //                 if (s.type === 'service') {
                //                     if (s.service.aurora_code === service.service.aurora_code) {
                //                         this.similar_services[this.similar_services.length - 1]
                //                             .services.push({
                //                             id: s.id,
                //                             extension_id: s.extension_id,
                //                             quote_category_id: s.quote_category_id,
                //                             code: s.service.aurora_code,
                //                             name: s.service.name,
                //                             date: s.date_in,
                //                             date_in: s.date_in,
                //                             type: s.type,
                //                             object_id: s.object_id,
                //                             check: (s.date_in === service.date_in)
                //                         })
                //                     }
                //                 }
                //                 if (s.type === 'hotel') {
                //                     if (s.hotel.channel[0].code === service.hotel.channel[0].code) {
                //                         this.similar_services[this.similar_services.length - 1]
                //                             .services.push({
                //                             id: s.id,
                //                             extension_id: s.extension_id,
                //                             quote_category_id: s.quote_category_id,
                //                             code: s.hotel.channel[0].code,
                //                             name: s.hotel.name,
                //                             date: s.date_in,
                //                             date_in: s.date_in,
                //                             type: s.type,
                //                             object_id: s.object_id,
                //                             check: (s.date_in === service.date_in)
                //                         })
                //                     }
                //                 }
                //             }
                //         })

                //         if (this.similar_services[this.similar_services.length - 1].services.length === 0) {
                //             this.similar_services.splice(this.similar_services.length - 1, 1)
                //         }

                //     })
                // }
                // console.log(this.similar_services)

                this.serviceChoosen = service
            },
            async changeWithCover(quote_open) {
                let _toggle = (this.quote_open.withHeader) ? 'block' : 'none'
                $('.showWithCover').css('display', _toggle)
                if (!this.quote_open.withHeader) {
                    this.quote_open.withClientLogo = 4
                    this.imagePortada = "";
                    this.urlPortada = '';
                    this.select_itinerary_with_cover = ''
                } else {

                    this.loading = true
                    this.select_itinerary_with_cover = 'amazonas'
                    this.caja = true;
                    this.imagePortada = '';
                    this.idCliente = localStorage.getItem('client_id')
                    await axios.get(window.a3BaseQuoteServerURL + 'api/quote/imageCreate', {
                        params: {
                            clienteId: this.idCliente,
                            portada: this.select_itinerary_with_cover,
                            portadaName: quote_open.name,
                            estado: 3,
                            refPax: this.refPax,
                            lang: localStorage.getItem('lang'),
                            nameCliente: this.refPax,
                        }
                    }).then((result) => {

                        this.imagePortada = window.a3BaseQuoteServerURL + result.data.image + '.jpg'
                        this.caja = false;
                        this.loading = false
                        this.urlPortada = result.data.image + '.jpg';

                    });

                    this.quote_open.withClientLogo = 3
                }

                if ((this.refPax.trim()).toUpperCase() !== (localStorage.getItem('client_name').trim()).toUpperCase()) {

                    this.updateImage = true
                    this.textoCliente = this.refPax
                }

                this.$forceUpdate()
            },
            async setComboPortada(quote_open) {

                if (this.quote_open.withClientLogo == 3) {
                    this.caja = true;
                    this.loading = true
                    this.imagePortada = '';
                    this.idCliente = localStorage.getItem('client_id')

                    await axios.get(window.a3BaseQuoteServerURL + 'api/quote/imageCreate', {
                        params: {
                            clienteId: this.idCliente,
                            portada: this.select_itinerary_with_cover,
                            portadaName: quote_open.name,
                            estado: quote_open.withClientLogo,
                            refPax: this.refPax,
                            lang: localStorage.getItem('lang'),
                            nameCliente: this.refPax,
                        }
                    }).then((result) => {

                        this.imagePortada = window.a3BaseQuoteServerURL + result.data.image + '.jpg'
                        this.loading = false
                        this.urlPortada = result.data.image + '.jpg';
                        this.caja = false;
                    });

                } else if (quote_open.withClientLogo == 1) {
                    this.caja = true;
                    this.loading = true
                    this.imagePortada = '';
                    this.idCliente = localStorage.getItem('client_id')
                    this.select_itinerary_with_client_logo = this.select_itinerary_with_cover;

                    await axios.get(window.a3BaseQuoteServerURL + 'api/quote/imageCreate', {
                        params: {
                            clienteId: this.idCliente,
                            portada: this.select_itinerary_with_client_logo,
                            portadaName: quote_open.name,
                            estado: quote_open.withClientLogo,
                            refPax: this.refPax,
                            lang: localStorage.getItem('lang'),
                            nameCliente: this.refPax,
                        }
                    }).then((result) => {

                        this.imagePortada = window.a3BaseQuoteServerURL + result.data.image + '.jpg'
                        this.loading = false
                        this.urlPortada = result.data.image + '.jpg';
                        this.caja = false;

                    });
                } else if (quote_open.withClientLogo == 2) {
                    this.caja = true;
                    this.loading = true
                    this.imagePortada = '';
                    this.idCliente = localStorage.getItem('client_id')
                    this.select_itinerary_with_client_logo = this.select_itinerary_with_cover;

                    await axios.get(window.a3BaseQuoteServerURL + 'api/quote/imageCreate', {
                        params: {
                            clienteId: this.idCliente,
                            portada: this.select_itinerary_with_client_logo,
                            portadaName: quote_open.name,
                            estado: quote_open.withClientLogo,
                            refPax: this.refPax,
                            lang: localStorage.getItem('lang'),
                            nameCliente: this.refPax,
                        }
                    }).then((result) => {

                        this.imagePortada = window.a3BaseQuoteServerURL + result.data.image + '.jpg'
                        this.loading = false
                        this.urlPortada = result.data.image + '.jpg';
                        this.caja = false;
                    });

                }

                if ((this.refPax.trim()).toUpperCase() !== (localStorage.getItem('client_name').trim()).toUpperCase()) {

                    this.updateImage = true
                    this.textoCliente = this.refPax
                }

            },

            roundLito: function(num) {

                if (num && num != '' && num != undefined && num != null) {
                    num = parseFloat(num)
                    num = (num).toFixed(2)

                    if (num != null) {
                        var res = String(num).split('.')
                        var nEntero = parseInt(res[0])
                        var nDecimal = 0
                        if (res.length > 1)
                            nDecimal = parseInt(res[1])

                        var newDecimal
                        if (nDecimal <= 10) {
                            newDecimal = 0
                        } else if (nDecimal > 10 && nDecimal <= 50) {
                            newDecimal = 5
                        } else {
                            nEntero = nEntero + 1
                            newDecimal = 0
                        }

                        return parseFloat(String(nEntero) + '.' + String(newDecimal))
                    }
                } else {
                    return '...'
                }
            },
            downloadSkeleton(quote) {

                if (this.refPax == '') {
                    this.$toast.error(this.translations.validations.rq_pax_reference, {
                        position: 'top-right'
                    })
                    return
                }

                if (quote.radioCategories == '') {
                    this.$toast.error(this.translations.validations.rq_category, {
                        position: 'top-right'
                    })
                    return
                }

                let client_id = localStorage.getItem('client_id')
                if (!client_id) {
                    this.$toast.error(this.translations.validations.rq_client, {
                        position: 'top-right'
                    })
                    return
                }
                this.loading = true
                // PASAR CLIENTE, BOLEAN DE ENCABEZADO
                axios({
                        method: 'GET',
                        url: window.a3BaseQuoteServerURL + 'api/quote/' + quote.id + '/category/' + quote.radioCategories + '/skeleton?lang=' + this.language_for_download +
                            '&client_id=' + client_id + '&use_header=' + quote.withHeader + '&refPax=' + this.refPax,
                        responseType: 'blob',
                    })
                    .then((response) => {

                        // console.log(response.data)

                        this.loading = false
                        var fileURL = window.URL.createObjectURL(new Blob([response.data]))
                        var fileLink = document.createElement('a')
                        fileLink.href = fileURL
                        fileLink.setAttribute('download', 'Skeleton - ' + quote.name + '.docx')
                        document.body.appendChild(fileLink)

                        fileLink.click()

                        this.backMiniMenu()

                    }).catch(() => {
                        this.loading = false
                        this.enabledBtnExcel = false
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right'
                        })
                    })
            },


            async downloadItinerary(quote) {

                if (this.refPax == '') {
                    this.$toast.error(this.translations.validations.rq_pax_reference, {
                        position: 'top-right'
                    })
                    return
                }

                if (quote.radioCategories == '') {
                    this.$toast.error(this.translations.validations.rq_category, {
                        position: 'top-right'
                    })
                    return
                }

                let client_id = localStorage.getItem('client_id')
                if (!client_id) {
                    this.$toast.error(this.translations.validations.rq_client, {
                        position: 'top-right'
                    })
                    return
                }

                if (quote.withHeader == '' && quote.withClientLogo == '') {
                    this.$toast.error(this.translations.validations.rq_check_portada, {
                        position: 'top-right'
                    })
                    return
                }


                // PASAR CLIENTE, BOLEAN DE ENCABEZADO
                if (this.urlPortada != '') {

                    // if((this.refPax.trim()).toUpperCase()  !== (localStorage.getItem('client_name').trim()).toUpperCase() ){
                    //     await this.setComboPortada(quote)
                    //     this.loading = true

                    // }

                    if (((this.refPax.trim()).toUpperCase() !== (localStorage.getItem('client_name').trim()).toUpperCase() && this.updateImage == false) ||
                        (this.refPax.trim()).toUpperCase() !== (this.textoCliente.trim()).toUpperCase() && this.updateImage == true) {
                        await this.setComboPortada(quote)
                        this.loading = true
                    }


                    this.loading = true

                    await axios({
                        method: 'GET',
                        url: window.a3BaseQuoteServerURL + 'api/quote/' + quote.id + '/category/' + quote.radioCategories + '/itinerary?lang=' + this.language_for_download +
                            '&client_id=' + client_id + '&use_header=' + quote.withHeader + '&cover=' + this.select_itinerary_with_cover + '&refPax=' + this.refPax +
                            '&client_logo=' + quote.withClientLogo +
                            '&cover_client_logo=' + 'cliente-' + this.select_itinerary_with_client_logo +
                            '&urlPortadaLogo=' + this.urlPortada,
                        responseType: 'blob',
                    }).then((response) => {

                        this.loading = false
                        var fileURL = window.URL.createObjectURL(new Blob([response.data]))
                        var fileLink = document.createElement('a')
                        fileLink.href = fileURL
                        fileLink.setAttribute('download', 'Itinerary - ' + quote.name + '.docx')
                        document.body.appendChild(fileLink)

                        fileLink.click()

                        this.backMiniMenu()

                    }).catch(() => {

                        this.loading = false
                        this.enabledBtnExcel = false
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right'
                        })
                    })


                } else {
                    this.loading = true
                    // if((this.refPax.trim()).toUpperCase()  !== (localStorage.getItem('client_name').trim()).toUpperCase() ){
                    //     this.setComboPortada(quote)
                    //     this.loading = true

                    // }
                    if (((this.refPax.trim()).toUpperCase() !== (localStorage.getItem('client_name').trim()).toUpperCase() && this.updateImage == false) ||
                        (this.refPax.trim()).toUpperCase() !== (this.textoCliente.trim()).toUpperCase() && this.updateImage == true) {
                        await this.setComboPortada(quote)
                        this.loading = true
                    }

                    await axios({
                        method: 'GET',
                        url: window.a3BaseQuoteServerURL + 'api/quote/' + quote.id + '/category/' + quote.radioCategories + '/itinerary?lang=' + this.language_for_download +
                            '&client_id=' + client_id + '&use_header=' + quote.withHeader + '&cover=' + this.select_itinerary_with_cover + '&refPax=' + this.refPax +
                            '&client_logo=' + quote.withClientLogo,
                        responseType: 'blob',
                    }).then((response) => {

                        this.loading = false
                        var fileURL = window.URL.createObjectURL(new Blob([response.data]))
                        var fileLink = document.createElement('a')
                        fileLink.href = fileURL
                        fileLink.setAttribute('download', 'Itinerary - ' + quote.name + '.docx')
                        document.body.appendChild(fileLink)

                        fileLink.click()

                        this.backMiniMenu()

                    }).catch((e) => {
                        console.log(e.message)
                        this.loading = false
                        this.enabledBtnExcel = false
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right'
                        })
                    })

                }

            },
            backMiniMenu() {
                $('.showDownloadSkeleton').css('display', 'none')
                $('.showDownloadItinerary').css('display', 'none')
                $('.miniMenu').css('display', 'block')
            },
            willDownloadSkeleton() {

                if (this.verify_itinerary_errors()) {
                    this.$toast.warning(this.translations.label.observations_validation_text, {
                        position: 'top-right'
                    })
                    return
                }

                if (this.quote_open.categories.length == 0) {
                    this.$toast.error(this.translations.validations.rq_category, {
                        position: 'top-right'
                    })
                    return
                }

                this.refPax = this.quote_open.name
                $('.showDownloadSkeleton').css('display', 'block')
                $('.showDownloadItinerary').css('display', 'none')
                $('.miniMenu').css('display', 'none')
            },
            willDownloadItinerary(quote_open) {
                this.refPax = localStorage.getItem('client_name')

                this.iconoX = false
                this.changeWithCover(this.quote_open)

                if (this.verify_itinerary_errors()) {
                    this.$toast.warning(this.translations.label.observations_validation_text, {
                        position: 'top-right'
                    })

                    return
                }

                if (this.quote_open.categories.length == 0) {
                    this.$toast.error(this.translations.validations.rq_category, {
                        position: 'top-right'
                    })

                    return
                }

                $('.showDownloadSkeleton').css('display', 'none')
                $('.showDownloadItinerary').css('display', 'block')
                $('.miniMenu').css('display', 'none')
            },
            setCancellationPolicies: function(general_policies, policies_cancellation) {
                this.general_politics = general_policies
                this.policies_cancellation = policies_cancellation
            },
            async relaciOrderV2() {

                if (this.new_order_related > 0) {

                    const quoteId = this.quote_open.id;

                    const flights = this.quote_open.categories[0].services.filter((service) => service.entity === 'flight') ?? [];

                    console.log("SERVICIOS: ", this.quote_open.categories[0].services);

                    const params = {
                        "access_token": localStorage.getItem('access_token'),
                        "quote": {
                            "id_original": this.quote_open.id_original,
                            "order_related": this.quote_open.order_related,
                            "order_position": this.quote_open.order_position
                        },
                        "order_number": this.new_order_related,
                        "order_position": this.quote_open.order_position,
                        "params": {
                            "number": this.quote_open.id_original,
                            "date": this.quote_open.created_at,
                            "name": this.quote_open.name,
                            "departure": flights?.[0]?.departure ?? 'LIM',
                            "executive": "{{ auth()->user()->code }}",
                            "prices": {
                                "estimated": this.quote_open.estimated_price,
                                "final": this.quote_open.file?.amount_total ?? 0,
                                "moneda": "USD"
                            },
                            "travel_date": {
                                "date": this.quote_open.estimated_travel_date,
                                "date_tca": this.quote_open.file?.travel_date ?? null
                            },
                            "file": {
                                "number": this.quote_open.file.file_code ?? null,
                                "date": this.quote_open.file.created_at ?? null
                            }
                        }
                    }

                    <?php
                    $environment = 'prod';
                    if (config('app.env') === 'testing') {
                        $environment = 'qa';
                    }
                    if (config('app.env') === 'development' || config('app.env') === 'local') {
                        $environment = 'dev';
                    }
                    ?>

                    await window.amazonAxios.post(`${window.amazonURL}sqs/publish`, {
                            queueName: `sqs-orders-sync-quotation-{{ $environment }}`, // Cola dónde será dirigido el mensaje
                            metadata: {
                                origin: 'A2-Front', // De qué servicio / sistema es enviado el mensaje
                                destination: 'orders-sync', // A qué servicio se dirige el mensaje
                                user: "{{ auth()->user()->code }}", // Usuario que envía el mensaje
                                service: 'quotes', // De qué servicio / sistema es enviado el mensaje
                                notify: ['lsv@limatours.com.pe'],
                            },
                            payload: [
                                params
                            ],
                        })
                        .then((response) => {
                            console.log("DATA: ", response);
                        })
                        .catch((error) => {
                            console.log("ERROR: ", error);
                            this.$toast.error(this.translations.messages.internal_error, {
                                position: 'top-right'
                            })
                        });
                } else {
                    this.$root.$emit('updateMenu')
                    this.$root.$emit('reloadQuotes')
                    this.searchQuoteOpen(this.categoryActive.id)
                }
            },
            cleanHotelFilters() {
                this.change_hotel_destiny_cities()
                this.categoryModalHotel = 'all'
                this.add_hotel_date = moment(this.quote_open.date_in).format('DD/MM/Y')
                this.nightsModalHotel = 1
                this.add_hotel_words = ''
                this.moreHotels = []
            },
            cleanServiceFilters() {
                this.change_service_destiny_cities()
                this.change_service_origin_cities()
                this.add_service_date = moment(this.quote_open.date_in).format('DD/MM/Y')
                this.add_service_words = ''
                this.categoryModalService = ''
                this.modal_service_type_id = ''
                this.moreServices = []
            },
            cleanExtensionFilters() {
                this.add_extensions_date = moment(this.quote_open.date_in).format('DD/MM/Y')
                this.service_type_id = ''
                this.type_class_id = ''
                this.add_extension_words = ''
                this.extensions = []
            },
            hideModal() {
                this.$refs['my-modal-confirm'].hide()
            },
            hideModalReserve: function() {
                this.$refs['modal_reserve'].hide()
            },
            willCopyCategory() {
                // VALIDAR SI ESTA VACIO
                if (this.categoryForCopy == '') {
                    this.$toast.warning(this.translations.label.select_category, {
                        position: 'top-right'
                    })
                    return
                }
                // VALIDAR SI ES EL MISMO
                if (this.categoryActive.id == this.categoryForCopy) {
                    this.$toast.warning(this.translations.validations.same_category, {
                        position: 'top-right'
                    })
                    return
                }
                // console.log(this.categoryActive)
                // console.log(this.quote_open)
                // VALIDAR SI YA TIENE SERVICIOS
                if (this.categoryActive.services.length == 0) {
                    this.copyCategory()
                } else {
                    this.$refs['my-modal-confirm'].show()
                }
            },
            copyCategory() {
                this.loading = true
                axios({
                    method: 'POST',
                    url: window.a3BaseQuoteServerURL + 'api/quote/categories/copy',
                    data: {
                        quote_category_id_from: this.categoryForCopy,
                        quote_category_id_to: this.categoryActive.id
                    }
                }).then((result) => {
                    this.loading = false
                    if (result.data.success === true) {
                        this.searchQuoteOpen(this.categoryActive.id)
                        this.loading = false
                        this.hideModal()
                    } else {
                        if (result.data.success === false && result.data.type == 0) {
                            this.$toast.error(this.translations.messages.no_services, {
                                position: 'top-right'
                            })
                        } else {
                            this.$toast.error(this.translations.messages.internal_error, {
                                position: 'top-right'
                            })
                        }
                    }
                }).catch(error => {
                    this.$toast.error(this.translations.messages.internal_error, {
                        position: 'top-right'
                    })
                    this.loading = false
                })
            },
            openModalDetail: function(service_id, view, date_out, adult, child) {
                let total_pax = parseInt(adult) + parseInt(child)
                let _date = this.formatDate(date_out, '/', '-', 1)
                this.loading = true
                axios.get('api/service/' + service_id + '/moreDetails?lang=' +
                    localStorage.getItem('lang') +
                    '&date_out=' + _date +
                    '&total_pax=' + total_pax +
                    '&client_id=' + localStorage.getItem('client_id')
                ).then(response => {
                    // console.log(response.data)
                    this.loading = false
                    this.view = view
                    this.service_detail_selected = response.data
                    $('#modal-detail-servicios').modal()
                })

            },
            willFilterTextH() {
                this.wordsH = this.add_hotel_words.split(',')
            },
            willFilterTextS() {
                this.wordsS = this.add_service_words.split(',')
            },
            setTranslations() {
                axios.get(baseURL + 'translation/' + localStorage.getItem('lang') + '/slug/quote').then((data) => {
                    this.translations = data.data
                })
            },
            updateDiscount() {
                let data = {
                    discount: (this.use_discount) ? this.discount : 0,
                    discount_detail: (this.use_discount) ? this.discount_detail : '',
                    discount_user_permission: (this.discount_user_permission) ? this.discount_user_permission : '',
                }
                axios.post(window.a3BaseQuoteServerURL + 'api/quote/' + this.quote_open.id + '/discount', data)
                    .then(response => {
                        if (response.data.success) {
                            this.quote_open.discount = data.discount
                            this.quote_open.discount_detail = data.discount_detail
                            this.quote_open.discount_user_permission = data.discount_user_permission
                        }
                    }).catch(error => {})
            },
            putMarkup() {
                //Todo si la cotizacion tiene un file ya no se puede actualizar el markup
                if (!this.has_file) {
                    axios.get('api/markup/byClient/' + localStorage.getItem('client_id'))
                        .then(response => {
                            // console.log(response)
                            if (response.data.success) {
                                this.markup = parseFloat(response.data.data.hotel)
                                this.updateMarkup(2)
                            }
                        }).catch(error => {
                            // console.log(error)
                        })
                }
            },
            async chooseEditRoom(_rateId) {
                this.loading = true
                let me = this
                // setTimeout(function () {
                // if (me.edit_checkboxs['_' + _rateId]) {
                //     // Validando que elija como máximo uno de cada tipo de habitación
                //     let total_sgl = 0
                //     let total_dbl = 0
                //     let total_tpl = 0
                //     me.hotelSwapRates.rooms.forEach(r => {
                //         r.rates.forEach(r_p => {
                //             if (me.edit_checkboxs['_' + r_p.rateId]) {
                //                 if (r.occupation == 1) {
                //                     total_sgl++
                //                 }
                //                 if (r.occupation == 2) {
                //                     total_dbl++
                //                 }
                //                 if (r.occupation == 3) {
                //                     total_tpl++
                //                 }
                //             }
                //         })
                //     })
                //     if (total_sgl > 1 || total_dbl > 1 || total_tpl > 1) {
                //         me.$toast.warning(me.translations.messages.only_one_rate_per_room, {
                //             position: 'top-right'
                //         })
                //         me.edit_checkboxs['_' + _rateId] = false
                //         me.loading = false
                //         return
                //     }
                //     // Validando que elija como máximo uno de cada tipo de habitación
                // }
                let _rate_plan_room_ids = []
                let _rate_plan_rooms_choose = []
                me.hotelSwapRates.rooms.forEach(s_rooms => {
                    s_rooms.rates.forEach(r_plan => {
                        if (me.edit_checkboxs['_' + r_plan.rateId]) {
                            if (r_plan.rateId != _rateId) {
                                _rate_plan_rooms_choose.push({
                                    rate_plan_room_id: _rateId,
                                    rate_plan_id: r_plan.ratePlanId,
                                    rateProviderMethod: r_plan.rateProviderMethod ? r_plan.rateProviderMethod : 0,
                                    hyperguest_pull: r_plan.rateProviderMethod ? r_plan.rateProviderMethod : 0,
                                    room_id: s_rooms.room_id,
                                    choose: false,
                                    occupation: s_rooms.occupation,
                                    on_request: r_plan.onRequest
                                })
                            }
                            // _rate_plan_room_ids.push(r_plan.rateId)
                        }
                    })
                })
                me.hotelSwapRates.rooms.forEach(s_rooms => {
                    s_rooms.rates.forEach(r_plan => {
                        if (me.edit_checkboxs['_' + r_plan.rateId]) {
                            if (r_plan.rateId == _rateId) {
                                _rate_plan_rooms_choose.push({
                                    rate_plan_room_id: _rateId,
                                    rate_plan_id: r_plan.ratePlanId,
                                    rateProviderMethod: r_plan.rateProviderMethod ? r_plan.rateProviderMethod : 0,
                                    hyperguest_pull: r_plan.rateProviderMethod ? r_plan.rateProviderMethod : 0,
                                    room_id: s_rooms.room_id,
                                    choose: true,
                                    occupation: s_rooms.occupation,
                                    on_request: r_plan.onRequest
                                })
                            }
                            // _rate_plan_room_ids.push(r_plan.rateId)
                        }
                    })

                })

                let data = {
                    quote_id: this.quote_open.id,
                    quote_service_id: me.quote_service_id_choosed,
                    rate_plan_room_ids: [],
                    lang: localStorage.getItem('lang'),
                    rate_plan_rooms_choose: _rate_plan_rooms_choose,
                    client_id: localStorage.getItem('client_id')
                }
                axios.post(window.a3BaseQuoteServerURL + 'api/quote/service/' + me.quote_service_id_choosed + '/rooms/' + me.url_hotel_choose, data)
                    .then(async (result) => {


                        // this.updatePeople();
                        await this.executeUpdateRateHpPull(localStorage.getItem('client_id'), [me.quote_service_id_choosed]);
                        this.searchQuoteOpen(this.categoryActive.id, '', this.grouped_code_selected)

                        // if (result.data.success) {
                        //     me.quote_open.categories.forEach(q => {
                        //         q.services.forEach(s => {
                        //             if (s.id == me.quote_service_id_choosed) {
                        //                 s.service_rooms = result.data.service_rooms
                        //
                        //                 s.alert = ''
                        //                 let _hotel = s.hotel
                        //
                        //                 if (this.quantity_persons.child > 0) {
                        //                     if (_hotel.min_age_child >= 0 && _hotel.max_age_child >= 0) {
                        //                         if (_hotel.allows_child == 1) {
                        //                             s.alert = this.translations.label.age_child_between + ' ' + _hotel.min_age_child + ' ' + this.translations.label.and + ' ' + _hotel.max_age_child + ' ' + this.translations.label.years_old
                        //                         } else {
                        //                             s.alert = this.translations.label.not_accept_children
                        //                         }
                        //                     } else {
                        //                         s.alert = this.translations.label.not_accept_children
                        //                     }
                        //                 }
                        //             }
                        //         })
                        //     })
                        // }
                        me.loading = false

                    }).catch((e) => {
                        // console.log(e)
                        me.loading = false
                    })

                // }, 300)
                this.closeModalPlanRooms()
                // this.searchQuoteOpen(this.categoryActive.id)

            },
            async editPlanRooms(me) {


                this.grouped_code_selected = me.grouped_code


                this.openModalPlanRooms()
                let adult = 1
                // if (this.quote_open.operation === 'passengers') {
                //     adult = me.adult
                // }
                this.loadingModal = true
                this.hotelSwapRates = []
                this.message_edit_plan_rooms = ''
                this.quote_service_id_choosed = me.id
                this.url_hotel_choose = 'replace'
                if (me.type == 'group_header') {
                    this.quote_service_id_choosed = me.group_quote_service_id
                    this.url_hotel_choose = 'addFromHeader'
                }
                // this.hotel_swap_single = me.single
                // this.hotel_swap_double = me.double
                // this.hotel_swap_triple = me.triple
                this.hotel_swap_single = 1
                this.hotel_swap_double = 1
                this.hotel_swap_triple = 1

                let data = {
                    'hotels_id': [me.object_id],
                    'date_from': this.formatDate(me.date_in, '/', '-', 1),
                    'date_to': this.formatDate(me.date_out, '/', '-', 1),
                    'client_id': localStorage.getItem('client_id'),
                    'quantity_rooms': (me.single + me.double + me.triple),
                    // 'quantity_persons_rooms': [{
                    //     'adults': adult,
                    //     'child': 0,
                    //     'ages_child': [
                    //         {
                    //             'child': 1,
                    //             'age': 0
                    //         }
                    //     ]
                    // }],

                    quantity_persons_rooms: [],

                    'type_classes': [me.hotel.typeclass_id],
                    'destiny': {
                        'code': me.hotel.country.iso + ',' +
                            (me.hotel.state.iso ? me.hotel.state.iso : ''),
                        'label': me.hotel.country.translations[0].value + ',' +
                            me.hotel.state.translations[0].value
                    },
                    'lang': localStorage.getItem('lang'),
                    'set_markup': (this.markup != '' || this.markup != null) ? this.markup : 0,
                    'zero_rates': true
                }

                data.inventory_quote = this.quote_id_original

                // return
                await axios.post('services/hotels/available/quote', data)
                    // axios.post('services/hotels/available', data)
                    .then((result) => {
                        if (result.data.success) {
                            result.data.data = result.data.data[0].city.hotels[0]
                            // Reordenar
                            if (me.service_rooms.length > 0 && result.data.data != undefined) {
                                let indexs_first = []
                                let _result_data_rooms = []
                                // Primero poner en un nuevo array solo las que no tenemos seleccionadas
                                result.data.data.rooms.forEach((rate, i) => {

                                    // rate.popShow = false

                                    let _verified = 0
                                    for (let r_p_r = 0; r_p_r < rate.rates.length; r_p_r++) {
                                        me.service_rooms.forEach(s_r => {
                                            if (s_r.rate_plan_room_id == rate.rates[r_p_r].ratePlanId) {
                                                _verified++
                                            }
                                        })
                                    }

                                    if (_verified > 0) {
                                        indexs_first.push(i)
                                    } else {
                                        _result_data_rooms.push(rate)
                                    }

                                })
                                indexs_first.forEach((index) => {
                                    _result_data_rooms.unshift(result.data.data.rooms[index])
                                })

                                result.data.data.rooms = _result_data_rooms
                            }

                            this.hotelSwapRates = (result.data.data != undefined) ? result.data.data : []
                            // for show rates
                            if (result.data.data != undefined) {
                                for (let r = 0; r < this.hotelSwapRates.rooms.length; r++) {
                                    this.hotelSwapRates.rooms[r].countCalendars = 0
                                    for (let r_p_r = 0; r_p_r < this.hotelSwapRates.rooms[r].rates.length; r_p_r++) {
                                        this.hotelSwapRates.rooms[r].rates[r_p_r].showAllRates = 1
                                        // if (typeof (this.hotelSwapRates.rooms[r].rates[r_p_r].showAllRates) === 'undefined') {
                                        //     this.hotelSwapRates.rooms[r].rates[r_p_r].showAllRates = 0
                                        // }
                                        for (let r_p_r_c = 0; r_p_r_c < this.hotelSwapRates.rooms[r].rates[r_p_r].rate[0].amount_days.length; r_p_r_c++) {
                                            this.hotelSwapRates.rooms[r].countCalendars++
                                        }
                                    }
                                }
                            }

                            // Se desactivo esto porque podemos agregar la misma habitacion
                            this.edit_checkboxs = []
                            me.service_rooms.forEach(s_rooms => {
                                // this.edit_checkboxs['_' + s_rooms.rate_plan_room_id] = true
                            })

                            this.title_rates_hotel = this.translations.label.hotel_rates + ': [' + me.hotel.channel[0].code + '] - ' + me.hotel.name

                            if (me.amount && me.amount.error_in_nights) {
                                this.message_edit_plan_rooms = this.translations.label.please_choose_different_rate_base_validate
                            }
                            // console.log(this.edit_checkboxs);
                            console.log(me, this.hotelSwapRates);
                        } else {
                            this.$toast.error(result.data.data, {
                                position: 'top-right'
                            })
                        }
                        console.log("paso todo");
                        this.loadingModal = false
                    }).catch((e) => {
                        // console.log(e)
                    })
            },
            saveAndDiscardQuote() {
                this.editing_quote = false
                this.withDiscard = true
                this.saveQuote()
            },
            convertToPackage() {
                let validate_has_services = this.validateHasServices()
                if (validate_has_services) {
                    this.$toast.warning(this.translations.label.no_added_services_reserve, {
                        position: 'top-right'
                    })
                    return false
                }

                if (this.verify_itinerary_errors()) {
                    this.$toast.warning(this.translations.label.observations_validation_text, {
                        position: 'top-right'
                    })
                } else {
                    this.loading = true
                    let data = {
                        markup: this.markup,
                        client_id: localStorage.getItem('client_id')
                    }
                    axios.post(window.a3BaseQuoteServerURL + 'api/quote/' + this.quote_open.id + '/convertToPackage', data)
                        .then((result) => {
                            if (result.data.success) {
                                this.package_create.id = result.data.package_id
                                this.openModalPackageCreated()
                                this.$toast.success(this.translations.messages.new_package_generated, {
                                    position: 'top-right'
                                })
                            }
                            this.loading = false
                        }).catch((e) => {
                            // console.log(e)
                            this.loading = false
                        })
                }

                this.$refs.confirmConvertModal.hide();

            },
            getServicesTypes: function() {
                axios.get(baseExternalURL + 'api/service_types/selectBox?lang=' + localStorage.getItem('lang'))
                    .then((result) => {
                        this.service_types = []
                        result.data.data.forEach(s_t => {
                            if (s_t.code != 'NA') {
                                this.service_types.push(s_t)
                            }
                        })
                    }).catch((e) => {
                        // console.log(e)
                    })
            },
            getRouteExport: function() {
                let client_id = localStorage.getItem('client_id')
                if (this.quote_open.operation === 'ranges') {
                    let link = window.a3BaseQuoteServerURL + 'quote/' + this.quote_open.id + '/export/ranges?lang=' + localStorage.getItem('lang') + '&client_id=' + client_id + '&user_id=' + localStorage.getItem('user_id') + '&user_type_id=' + localStorage.getItem('user_type_id')
                    let a = document.createElement('a')
                    a.target = '_blank'
                    a.href = link
                    a.click()
                }
                if (this.quote_open.operation === 'passengers') {
                    axios.get(window.a3BaseQuoteServerURL + 'api/quote/' + this.quote_open.id + '/check/services_amounts?lang=' + localStorage.getItem('lang')).then(response => {
                        if (response.data.success) {
                            let link = window.a3BaseQuoteServerURL + 'quote/' + this.quote_open.id + '/export/passengers?lang=' + localStorage.getItem('lang') + '&client_id=' + client_id + '&user_id=' + localStorage.getItem('user_id') + '&user_type_id=' + localStorage.getItem('user_type_id')
                            let a = document.createElement('a')
                            a.target = '_blank'
                            a.href = link
                            a.click()
                        } else {
                            this.$toast.error(response.data.message, {
                                position: 'top-right'
                            })
                        }
                    }).catch(error => {
                        // console.log(error)
                    })
                }


            },
            moveCarousel(direction) {
                // Find a more elegant way to express the :style. consider using props to make it truly generic
                if (direction === 1 && !this.atEndOfList) {
                    this.currentOffset -= this.paginationFactor
                } else if (direction === -1 && !this.atHeadOfList) {
                    this.currentOffset += this.paginationFactor
                }
            },
            getServiceCategories: function() {
                axios.get('api/service_categories/selectBox?lang=' + localStorage.getItem('lang')).then(response => {
                    this.service_categories = response.data.data
                }).catch(error => {
                    // console.log(error)
                })
            },
            getCategories: async function() {
                try {
                    const response = await axios.get('api/typeclass/quotes/selectbox?lang=' + localStorage.getItem('lang') + '&type=2')
                    const categories = response.data.data ?? {}
                    this.categories = Array.isArray(categories) ? categories : Object.values(categories)
                    this.type_class_id = this.categories[0].id
                } catch (error) {
                    // console.log(error)
                }
            },
            toggleViewRates(rate) {
                this.loading = true
                rate.showAllRates = !(rate.showAllRates)
                this.loading = false
            },
            showContentHotel(hotel) {
                this.loading = true
                this.moreHotels.forEach(h => {
                    if (hotel.id == h.id) {
                        h.selected = true
                        h.viewContent = !(h.viewContent)
                    } else {
                        if (h.selected == false) {

                            h.viewContent = false
                        }
                    }
                })

                this.loading = false
            },
            showModalHotelPromotion(hotel) {

                console.log("Click aqui");
                this.check_promotion = true

                if (this.service_selected_general.single == 0 &&
                    this.service_selected_general.double == 0 &&
                    this.service_selected_general.triple == 0) {
                    this.$toast.warning(this.translations.label.you_must_enter_the_accommodation, {
                        position: 'top-right'
                    })
                    this.show_occupation_modal()
                    return
                }

                // console.log(hotel)

                this.show_hotel_modal()

                this.loading = true
                this.quote_open.categories.forEach(_c => {
                    if (_c.tabActive == 'active') {
                        _c.checkAddHotel = true
                        // this.categoryModalHotel = _c.type_class_id
                        this.categoryModalHotel = 'all'
                    } else {
                        _c.checkAddHotel = false
                    }
                })

                if (hotel != '') {
                    this.hotelForReplace = hotel
                    this.add_hotel_date = hotel.date_in
                    this.nightsModalHotel = hotel.nights

                    // Establecer el país primero
                    if (hotel.hotel && hotel.hotel.country) {
                        this.destinationsModalHotel_country = this.destinationsModalHotel_countries_select.find(c => c.code == hotel.hotel.country.iso)
                        if (this.destinationsModalHotel_country) {
                            // Cargar las ciudades del país seleccionado
                            this.change_hotel_destiny_cities()

                            // Esperar un momento para que se carguen las ciudades antes de buscar el destino
                            this.$nextTick(() => {
                                // Buscar el destino (state o city)
                                let destinationFound = false
                                if (hotel.hotel.state && hotel.hotel.state.iso) {
                                    this.destinationsModalHotel_select.forEach(d => {
                                        if (d.code == hotel.hotel.state.iso && d.parent_code == hotel.hotel.country.iso) {
                                            this.destinationsModalHotel = d
                                            destinationFound = true
                                            this.change_hotel_destiny_districts()
                                            return
                                        }
                                    })
                                }

                                // Si no se encontró por state, buscar por city
                                if (!destinationFound && hotel.hotel.city && hotel.hotel.city.iso) {
                                    this.destinationsModalHotel_select.forEach(d => {
                                        if (d.code == hotel.hotel.city.iso && d.parent_code == hotel.hotel.country.iso) {
                                            this.destinationsModalHotel = d
                                            this.change_hotel_destiny_districts()
                                            return
                                        }
                                    })
                                }

                                // Si aún no se encontró, intentar con city.id
                                if (!this.destinationsModalHotel && hotel.hotel.city && hotel.hotel.city.id) {
                                    // Buscar en el universo completo
                                    this.destinationsModalHotel_select_universe.forEach(d => {
                                        if (d.parent_code == hotel.hotel.country.iso) {
                                            // Verificar si el código contiene el city.id
                                            if (d.code == hotel.hotel.city.id || d.code == hotel.hotel.city.iso) {
                                                this.destinationsModalHotel = d
                                                this.change_hotel_destiny_districts()
                                                return
                                            }
                                        }
                                    })
                                }

                                // Si aún no se encontró, usar el formato de objeto como fallback
                                if (!this.destinationsModalHotel && hotel.hotel.country && hotel.hotel.state && hotel.hotel.city) {
                                    this.destinationsModalHotel = {
                                        code: hotel.hotel.country.iso + ',' + hotel.hotel.state.iso + ',' + hotel.hotel.city.id,
                                        label: hotel.hotel.country.translations[0].value + ',' + hotel.hotel.state.translations[0].value + ',' + hotel.hotel.city.translations[0].value
                                    }
                                }
                            })
                        }
                    }

                    this.check_replace_hotel = true
                    this.searchHotels('1')
                    // AL MOMENTO DE GUARDAR QUE REMUEVA DICHO HOTEL
                } else {
                    this.hotelForReplace = ''
                    this.check_replace_hotel = false
                    this.moreHotels = []
                    this.destinationsModalHotel = ''
                }

                this.loading = false
            },
            showModalHotel(hotel) {
                this.check_promotion = false

                // if (this.service_selected_general.single == 0 &&
                //     this.service_selected_general.double == 0 &&
                //     this.service_selected_general.triple == 0) {
                //     this.$toast.warning(this.translations.label.you_must_enter_the_accommodation, {
                //         position: 'top-right'
                //     })
                //     this.show_occupation_modal()
                //     return
                // }

                this.show_hotel_modal()

                // this.loading = true
                this.quote_open.categories.forEach(_c => {
                    if (_c.tabActive == 'active') {
                        _c.checkAddHotel = true
                        // this.categoryModalHotel = _c.type_class_id
                        this.categoryModalHotel = 'all'
                    } else {
                        _c.checkAddHotel = false
                    }
                })

                if (hotel != '') {
                    this.hotelForReplace = hotel
                    this.add_hotel_date = hotel.date_in
                    this.nightsModalHotel = hotel.nights

                    // Establecer el país primero
                    if (hotel.hotel && hotel.hotel.country) {
                        this.destinationsModalHotel_country = this.destinationsModalHotel_countries_select.find(c => c.code == hotel.hotel.country.iso)
                        if (this.destinationsModalHotel_country) {
                            // Cargar las ciudades del país seleccionado
                            this.change_hotel_destiny_cities()

                            // Esperar un momento para que se carguen las ciudades antes de buscar el destino
                            this.$nextTick(() => {
                                // Buscar el destino (state o city)
                                let destinationFound = false
                                if (hotel.hotel.state && hotel.hotel.state.iso) {
                                    this.destinationsModalHotel_select.forEach(d => {
                                        if (d.code == hotel.hotel.state.iso && d.parent_code == hotel.hotel.country.iso) {
                                            this.destinationsModalHotel = d
                                            destinationFound = true
                                            this.change_hotel_destiny_districts()
                                            return
                                        }
                                    })
                                }

                                // Si no se encontró por state, buscar por city
                                if (!destinationFound && hotel.hotel.city && hotel.hotel.city.iso) {
                                    this.destinationsModalHotel_select.forEach(d => {
                                        if (d.code == hotel.hotel.city.iso && d.parent_code == hotel.hotel.country.iso) {
                                            this.destinationsModalHotel = d
                                            this.change_hotel_destiny_districts()
                                            return
                                        }
                                    })
                                }

                                // Si aún no se encontró, intentar con city.id
                                if (!this.destinationsModalHotel && hotel.hotel.city && hotel.hotel.city.id) {
                                    // Buscar en el universo completo
                                    this.destinationsModalHotel_select_universe.forEach(d => {
                                        if (d.parent_code == hotel.hotel.country.iso) {
                                            // Verificar si el código contiene el city.id
                                            if (d.code == hotel.hotel.city.id || d.code == hotel.hotel.city.iso) {
                                                this.destinationsModalHotel = d
                                                this.change_hotel_destiny_districts()
                                                return
                                            }
                                        }
                                    })
                                }
                            })
                        }
                    }

                    this.check_replace_hotel = true
                    if (this.hotelForReplace.hotel) {
                        this.categoryModalHotel = this.hotelForReplace.hotel.typeclass_id
                    }
                    this.searchHotels()
                    // AL MOMENTO DE GUARDAR QUE REMUEVA DICHO HOTEL
                } else {
                    this.hotelForReplace = ''
                    this.check_replace_hotel = false
                    this.moreHotels = []
                    this.destinationsModalHotel = ''
                }

                // this.loading = false
            },
            showModalExtension() {
                this.loading = true
                this.quote_open.categories.forEach(_c => {
                    if (_c.tabActive == 'active') {
                        _c.checkAddExtension = true
                    } else {
                        _c.checkAddExtension = false
                    }
                })
                for (var i = 0; i < this.type_class_ids.length; i++) {
                    $('#' + this.type_class_ids[i]).prop('checked', false)
                }
                this.extension_selected = null
                this.extension_replace = null
                this.service_selected_id = null
                this.type_class_id = null
                this.getExtensions()
                this.loading = false
            },
            showModalFlight: function() {
                // this.searchDestinations('', '')
            },
            resetDestinations: function() {
                this.destinations_flights_destiny = []
                this.destinations_flights_origin = []
            },
            searchDestinationsOrigin: function(search, loading) {
                this.loading = true
                loading(true)

                axios.get(baseExpressURL + 'api/v1/flights/origins?type=' + this.flight_type + '&term=' + search.toUpperCase())
                    .then(response => {
                        loading(false)
                        this.destinations_flights_origin = response.data.data
                        // console.log(response.data)
                        this.loading = false
                    })
                    .catch(error => {
                        loading(false)
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right'
                        })
                        // console.log(error)
                        this.loading = false
                    })
            },
            searchDestinationsDestiny: function(search, loading) {
                this.loading = true
                loading(true)

                axios.get(baseExpressURL + 'api/v1/flights/origins?type=' + this.flight_type + '&term=' + search.toUpperCase())
                    .then(response => {
                        loading(false)
                        this.destinations_flights_destiny = response.data.data
                        // console.log(response.data)
                        this.loading = false
                    })
                    .catch(error => {
                        loading(false)
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right'
                        })
                        console.log(error)
                        this.loading = false
                    })
            },
            addFlight: function() {

                let _categories = []
                this.quote_open.categories.forEach(c => {
                    if (c.checkAddService) {
                        _categories.push(c.id)
                    }
                })

                if (_categories.length == 0) {
                    this.$toast.warning(this.translations.validations.rq_category, {
                        position: 'top-right'
                    })
                    return
                }

                if (this.add_flight_date == undefined || this.add_flight_date == '') {
                    this.$toast.warning(this.translations.label.enter_date_to_continue, {
                        position: 'top-right'
                    })
                    return
                }

                if (this.originModalFlight.codciu == undefined && this.destinationsModalFlight.codciu == undefined) {
                    this.$toast.warning(this.translations.label.enter_an_origin_or_destination_to_continue, {
                        position: 'top-right'
                    })
                    return
                }

                let _date = this.formatDate(this.add_flight_date, '/', '-', 1)

                let data = {
                    type: 'flight',
                    categories: _categories,
                    type_flight: this.flight_type,
                    date_in: _date,
                    adult: this.quantity_persons.adults,
                    child: this.quantity_persons.child,
                    client_id: localStorage.getItem('client_id'),
                    origin: this.originModalFlight.codciu,
                    destination: this.destinationsModalFlight.codciu
                }

                // console.log(data)

                this.loading = true
                axios.post(window.a3BaseQuoteServerURL + 'api/quote/' + this.quote_open.id + '/categories/flight', data).then((response) => {

                    if (response.data.success) {
                        this.flight_type = 0
                        this.add_flight_date = ''
                        this.originModalFlight = {}
                        this.destinationsModalFlight = {}

                        $('#modal-flight').modal('hide')

                        this.$toast.success(this.translations.messages.successfully_added, {
                            position: 'top-right'
                        })
                        this.searchQuoteOpen(this.categoryActive.id)
                    } else {
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right'
                        })
                    }

                    // response.data.quote_open_categories.forEach(_c => {
                    //     this.quote_open.categories.forEach(c => {
                    //         if (_c.id == c.id) {
                    //             this.quote_open.categories.services = response.data.quote_open_categories.services
                    //         }
                    //     })
                    // })
                    this.loading = false

                }).catch(error => {
                    this.$toast.error(this.translations.messages.internal_error, {
                        position: 'top-right'
                    })
                    // console.log(error)
                    this.loading = false
                })
            },
            toggleCategoryCheckAddExtension(me) {
                this.loading = true
                me.checkAddExtension = !(me.checkAddExtension)
                this.loading = false
            },
            toggleCategoryCheckAddHotel(me) {
                this.loading = true
                me.checkAddHotel = !(me.checkAddHotel)
                this.loading = false
            },
            toggleCategoryCheckAddService(me) {
                this.loading = true
                me.checkAddService = !(me.checkAddService)
                this.loading = false
            },
            addService(me) {

                let _categories = []
                this.quote_open.categories.forEach(c => {
                    if (c.checkAddService) {
                        _categories.push(c.id)
                    }
                })

                if (_categories.length == 0) {
                    this.$toast.warning(this.translations.validations.rq_category, {
                        position: 'top-right'
                    })
                    return
                }

                let _service_rate_id = me.rateChoosed

                if (_service_rate_id == '') {
                    this.$toast.warning(this.translations.validations.rq_rates, {
                        position: 'top-right'
                    })
                    return
                }

                let _date = this.formatDate(this.add_service_date, '/', '-', 1)

                let data = {
                    type: 'service',
                    categories: _categories,
                    object_id: me.id,
                    service_code: me.aurora_code,
                    date_in: _date,
                    date_out: _date,
                    service_rate_ids: [_service_rate_id],
                    adult: this.quantity_persons.adults,
                    child: this.quantity_persons.child,
                    single: (this.service_selected_general.single != null) ? this.service_selected_general.single : 0,
                    double: (this.service_selected_general.double != null) ? this.service_selected_general.double : 0,
                    triple: (this.service_selected_general.triple != null) ? this.service_selected_general.triple : 0,
                    client_id: localStorage.getItem('client_id'),
                    extension_parent_id: ''
                }

                if (this.editService) {
                    if ((this.service_old.extension_id != null && this.service_old.extension_id != '') ||
                        (this.service_old.parent_service_id != null && this.service_old.parent_service_id != '')) {

                        if (this.service_old.extension_id != null && this.service_old.extension_id != '') {
                            data.extension_parent_id = this.service_old.id
                        } else {
                            data.extension_parent_id = this.service_old.parent_service_id
                        }
                    }
                }
                this.saveService(data)

            },
            async addHotel() {
                this.loading = true
                let _categories = []
                this.quote_open.categories.forEach(c => {
                    if (c.checkAddHotel) {
                        _categories.push(c.id)
                    }
                })

                if (_categories.length == 0) {
                    this.$toast.warning(this.translations.validations.rq_category, {
                        position: 'top-right'
                    })
                    this.loading = false
                    return
                }

                let _date_from = this.formatDate(this.add_hotel_date, '/', '-', 1)
                let _date_to =
                    moment(this.formatDate(this.add_hotel_date, '/', '.', 0), 'DD-MM-YYYY').add('days', this.nightsModalHotel)

                let _extension_parent_id = ''
                if (this.hotelForReplace != '') {
                    if ((this.hotelForReplace.extension_id != null && this.hotelForReplace.extension_id != '') ||
                        (this.hotelForReplace.parent_service_id != null && this.hotelForReplace.parent_service_id != '')) {

                        if (this.hotelForReplace.extension_id != null && this.hotelForReplace.extension_id != '') {
                            _extension_parent_id = this.hotelForReplace.id
                        } else {
                            _extension_parent_id = this.hotelForReplace.parent_service_id
                        }
                    }
                }

                // Validando que elija como máximo uno de cada tipo de habitación
                let _rooms_error = 0
                this.moreHotels.forEach(h => {
                    let total_sgl = 0
                    let total_dbl = 0
                    let total_tpl = 0
                    h.rooms.forEach(r => {
                        r.rates.forEach(r_p => {
                            if (this.checkboxs[h.id + '_' + r_p.rateId]) {
                                if (r.occupation == 1) {
                                    total_sgl++
                                }
                                if (r.occupation == 2) {
                                    total_dbl++
                                }
                                if (r.occupation == 3) {
                                    total_tpl++
                                }
                                if (total_sgl > 1 || total_dbl > 1 || total_tpl > 1) {
                                    // console.log(total_sgl + ' | ' + total_dbl + ' | ' + total_tpl)
                                    _rooms_error++
                                }
                            }
                        })
                    })
                })
                if (_rooms_error > 0) {
                    this.$toast.warning(this.translations.messages.only_one_rate_per_room, {
                        position: 'top-right'
                    })
                    this.loading = false
                    return
                }
                // Validando que elija como máximo uno de cada tipo de habitación
                let isChannelHyperguestPull = false;
                this.moreHotels.forEach(async (h) => {
                    let on_request = ''
                    let service_rate_ids = []
                    let service_rooms_rate_plans_ids = []
                    h.rooms.forEach(r => {
                        r.rates.forEach(r_p => {
                            if (this.checkboxs[h.id + '_' + r_p.rateId]) {
                                on_request = r_p.onRequest
                                service_rate_ids.push(r_p.rateId)
                                service_rooms_rate_plans_ids.push({
                                    'room': r.room_id,
                                    'rate': r_p.ratePlanId
                                })

                                if (r_p.rateProvider == 'HYPERGUEST' && r_p.rateProviderMethod == 2) {
                                    isChannelHyperguestPull = true;
                                }


                            }
                        })
                    })

                    if (service_rate_ids.length > 0) {

                        if (isChannelHyperguestPull) {
                            service_rate_ids = [];
                        } else {
                            service_rooms_rate_plans_ids = [];
                        }

                        let file_itinerary_id = this.relationFile3(this.hotelForReplace);
                        //console.log(h)
                        let data = {
                            quote_id: this.quote_open.id,
                            type: 'hotel',
                            categories: _categories,
                            object_id: h.id,
                            service_code: h.code,
                            date_in: _date_from,
                            date_out: moment(_date_to, 'YYYY-MM-DD').format('YYYY-MM-DD'),
                            service_rate_ids: service_rate_ids,
                            service_rooms_rate_plans_ids: service_rooms_rate_plans_ids,
                            on_request: (on_request == 1) ? 0 : 1,
                            adult: this.quantity_persons.adults,
                            child: this.quantity_persons.child,
                            client_id: localStorage.getItem('client_id'),
                            single: this.service_selected_general.single,
                            double: this.service_selected_general.double,
                            triple: this.service_selected_general.triple,
                            extension_parent_id: _extension_parent_id,
                            file_itinerary_id: file_itinerary_id
                        }

                        /*
                        await axios.post(window.baseExternalURL + 'services/remove_inventory', {
                            service_rate_ids: service_rate_ids,
                        }).then((result) => {
                            console.log("Inventory: ", result.data);
                        });
                        */

                        await this.saveService(data)
                    }
                })

                if (this.check_promotion) {
                    this.$refs['modal-hotel'].hide()
                } else {
                    if (this.hotelForReplace != '') {
                        if (this.check_replace_hotel) {
                            this.deleteService(this.hotelForReplace)
                            this.hotelForReplace = ''
                        }
                    }

                    //this.searchHotels()
                }
            },
            async saveService(data) {
                this.loading = true
                axios.post(window.a3BaseQuoteServerURL + 'api/quote/' + this.quote_open.id + '/categories/service', data).then(async response => {
                    // console.log(response)
                    if (response.data.success) {

                        this.$toast.success(this.translations.messages.successfully_added, {
                            position: 'top-right'
                        })

                        if (data.type == 'hotel') {
                            let quote_service_ids = response.data.new_service_created
                            await this.executeUpdateRateHpPull(localStorage.getItem('client_id'), quote_service_ids);
                        }

                        await this.searchQuoteOpen(this.categoryActive.id, true)
                        // this.quoteMe()  // comentado x JC
                        // if (this.quote_id != null) {
                        //     this.updatePeople()
                        // }
                    } else {
                        // client_markup
                        let message_ = 'Error'
                        if (response.data.type === 'client_markup') {
                            message_ = this.translations.label.client_does_not_markup
                        }
                        this.$toast.warning(message_, {
                            position: 'top-right'
                        })
                    }
                    this.loading = false

                }).catch(error => {
                    if (error.response.status == 404) {
                        this.$toast.error(error.response.data.error, {
                            position: 'top-right'
                        })
                    }
                    if (error.response.status == 500) {
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right'
                        })
                    }
                    this.loading = false
                })
            },
            relationFile3(service) {

                let services_ = []

                if (service.type === 'group_header') {
                    this.quote_open.categories.forEach(_c => {
                        if (_c.id == service.quote_category_id) {
                            _c.services.forEach(_s => {
                                if (_s.grouped_code == service.grouped_code && _s.grouped_type == 'row') {
                                    if (!services_.includes(_s.file_itinerary_id)) {
                                        services_.push(_s.file_itinerary_id)
                                    }
                                }
                            })
                        }
                    })
                } else {
                    services_.push(service.file_itinerary_id)
                    if (this.similar_services.length > 0) {
                        this.similar_services.forEach((similar_categ) => {
                            similar_categ.services.forEach((similar_service) => {
                                if (similar_service.check) {
                                    if (!services_.includes(similar_service.file_itinerary_id)) {
                                        services_.push(similar_service.file_itinerary_id)
                                    }
                                }
                            })
                        })
                    }
                }

                if (services_.length > 0) {
                    return services_[0];
                }

                return '';

            },
            modal_services_setPage(page) {
                if (page < 1 || page > this.modal_services_pages.length) {
                    return
                }
                this.modal_services_pageChosen = page
                this.searchServices(false)
            },
            roundLito: function(num) {
                num = parseFloat(num)
                num = (num).toFixed(2)

                if (num != null) {
                    var res = String(num).split('.')
                    var nEntero = parseInt(res[0])
                    var nDecimal = 0
                    if (res.length > 1)
                        nDecimal = parseInt(res[1])

                    var newDecimal
                    if (nDecimal <= 10) {
                        newDecimal = 0
                    } else if (nDecimal > 10 && nDecimal <= 50) {
                        newDecimal = 5
                    } else if (nDecimal > 50) {
                        nEntero = nEntero + 1
                        newDecimal = 0
                    }

                    return parseFloat(String(nEntero) + '.' + String(newDecimal))
                }
            },
            searchServices(searchFisrt) {
                console.log("Click aqui 2");
                this.loading = true
                this.moreServices = []
                if (searchFisrt) {
                    this.modal_services_pageChosen = 1
                }

                let children = 0

                if (this.operation === 'passengers') {
                    children = this.quantity_persons.child
                }

                let data = {
                    date_from: this.formatDate(this.add_service_date, '/', '-', 1),
                    service_name: this.add_service_words,
                    allWords: (this.allWordsS) ? 1 : 0,
                    origin: this.get_data_service_origin(),
                    destiny: this.get_data_service_destiny(),
                    service_type: this.modal_service_type_id,
                    service_category: this.categoryModalService !== "" ? [this.categoryModalService] : null,
                    limit: this.modal_services_limit,
                    page: this.modal_services_pageChosen,
                    lang: localStorage.getItem('lang'),
                    client_id: localStorage.getItem('client_id'),
                    adults: this.modal_service_number_of_guests,
                    children: children,
                }

                // console.log(this.markup)

                axios.post('api/services/search', data).then(response => {

                    response.data.data.forEach((data) => {
                        data.service_rate.forEach((service_rate) => {
                            service_rate.service_rate_plans.forEach((service_rate_plan) => {
                                let markup_service = parseFloat(service_rate_plan.markup)
                                if (service_rate_plan.markup == null) {
                                    markup_service = this.markup
                                }
                                service_rate_plan.price_adult_label =
                                    this.roundLito(parseFloat(service_rate_plan.price_adult_without_markup) * (1 + (markup_service / 100)))
                            })
                        })
                    })

                    this.moreServices = response.data.data
                    //console.log(this.moreServices)
                    this.moreServices.forEach(mS => {
                        mS.rateChoosed = ''
                        if (mS.service_rate.length > 0) {
                            mS.rateChoosed = mS.service_rate[0].id
                        }
                    })

                    this.modal_services_pages = []
                    for (let i = 0; i < (response.data.count / this.modal_services_limit); i++) {
                        this.modal_services_pages.push(i + 1)
                    }

                    this.loading = false

                }).catch(error => {
                    this.$toast.error(this.translations.messages.internal_error, {
                        position: 'top-right'
                    })
                    // console.log(error)
                    this.loading = false
                })
            },

            get_data_service_origin() {
                // this.destiny / destiny_country / destiny_district
                // {code: "89,1610,128", label: "Perú, Lima, Lima"} // http://prntscr.com/123oeip
                let data_origin = ''
                if (this.originModalService !== '') {
                    let code_ = this.originModalService_country.code + ',' + this.originModalService.code
                    let label_ = this.originModalService_country.label + ',' + this.originModalService.label
                    if (this.originModalService_district !== '') {
                        let origin_district_label = ''
                        this.originModalService_additional_select.forEach((d) => {
                            if (d.code === this.originModalService_district) {
                                origin_district_label = d.label
                            }
                        })

                        code_ += ',' + this.originModalService_district
                        label_ += ',' + origin_district_label
                    }
                    data_origin = {
                        code: code_,
                        label: label_
                    }
                }
                return data_origin
            },
            get_data_service_destiny() {
                // this.destiny / destiny_country / destiny_district
                // {code: "89,1610,128", label: "Perú, Lima, Lima"} // http://prntscr.com/123oeip
                let data_destiny = ''
                if (this.destinationsModalService !== '') {
                    let code_ = this.destinationsModalService_country.code + ',' + this.destinationsModalService.code
                    let label_ = this.destinationsModalService_country.label + ',' + this.destinationsModalService.label
                    if (this.destinationsModalService_district !== '') {
                        let destiny_district_label = ''
                        this.destinationsModalService_additional_select.forEach((d) => {
                            if (d.code === this.destinationsModalService_district) {
                                destiny_district_label = d.label
                            }
                        })

                        code_ += ',' + this.destinationsModalService_district
                        label_ += ',' + destiny_district_label
                    }
                    data_destiny = {
                        code: code_,
                        label: label_
                    }
                }
                return data_destiny
            },
            async searchHotels(_promotion) {

                if (this.destinationsModalHotel == '') {
                    this.$toast.warning(this.translations.label.locate_a_destination, {
                        position: 'top-right'
                    })
                    return
                }

                if (this.categoryModalHotel == '') {
                    this.$toast.warning(this.translations.label.select_category, {
                        position: 'top-right'
                    })
                    return
                }

                this.loadingHotel = true
                this.moreHotels = []
                this.quantity_persons.ages_child = this.age_child
                let _date_to = moment(this.formatDate(this.add_hotel_date, '/', '.', 0), 'DD-MM-YYYY').add('days', this.nightsModalHotel)
                let data = {}
                if (this.operation === 'passengers') {
                    data = {
                        'date_from': this.formatDate(this.add_hotel_date, '/', '-', 1),
                        'date_to': moment(_date_to).format('YYYY-MM-DD'),
                        'client_id': localStorage.getItem('client_id'),
                        // 'quantity_persons_rooms': [this.quantity_persons],
                        // 'quantity_persons_rooms': [
                        //     {
                        //         adults: 1,
                        //         ages_child: [],
                        //         child: 0
                        //     }
                        // ],
                        quantity_persons_rooms: [],
                        'lang': localStorage.getItem('lang') ?? 'en',
                        'quantity_rooms': 1,
                        'set_markup': (this.markup != '' || this.markup != null) ? this.markup : 0,
                        'zero_rates': true
                    }
                } else {
                    data = {
                        'date_from': this.formatDate(this.add_hotel_date, '/', '-', 1),
                        'date_to': moment(_date_to).format('YYYY-MM-DD'),
                        'client_id': localStorage.getItem('client_id'),
                        // 'quantity_persons_rooms': [
                        //     {
                        //         adults: 1,
                        //         ages_child: [],
                        //         child: 0
                        //     }
                        // ],
                        quantity_persons_rooms: [],
                        'lang': localStorage.getItem('lang') ?? 'en',
                        'quantity_rooms': 1,
                        'set_markup': (this.markup != '' || this.markup != null) ? this.markup : 0,
                        'zero_rates': true
                    }
                }

                data.hotels_search_code = this.add_hotel_words
                data.allWords = this.allWordsH
                data.type_classes = [this.categoryModalHotel]
                data.typeclass_id = this.categoryModalHotel

                if (_promotion === '1') {
                    data.promotional_rate = 1
                    data.destiny = this.destinationsModalHotel
                } else {
                    data.destiny = this.get_data_hotel_destiny()
                }

                data.inventory_quote = this.quote_id_original

                this.loading = true
                await axios.post('services/hotels/available/quote', data).then(response => {
                    this.loadingHotel = false
                    this.loading = false
                    response.data.data[0].city.hotels.forEach(h => {
                        h.viewContent = false
                        h.selected = false
                        h.countRates = 0
                        // for show rates
                        for (let r = 0; r < h.rooms.length; r++) {
                            h.rooms[r].countCalendars = 0

                            let room = h.rooms[r]

                            if ((_promotion === '1' && ((room.occupation === 1 && this.service_selected_general.single > 0) ||
                                    (room.occupation === 2 && this.service_selected_general.double > 0) ||
                                    (room.occupation === 3 && this.service_selected_general.triple > 0))) || _promotion !== '1') {
                                for (let r_p_r = 0; r_p_r < h.rooms[r].rates.length; r_p_r++) {
                                    if (typeof(h.rooms[r].rates[r_p_r].showAllRates) === 'undefined') {
                                        h.rooms[r].rates[r_p_r].showAllRates = 0
                                    }
                                    h.countRates++

                                    for (let r_p_r_c = 0; r_p_r_c < h.rooms[r].rates[r_p_r].rate[0].amount_days.length; r_p_r_c++) {
                                        h.rooms[r].countCalendars++
                                    }
                                }
                            }
                        }
                        // for show rates
                    })
                    this.moreHotels = response.data.data[0].city.hotels
                    this.date_from_promotion = response.data.data[0].city.search_parameters.date_from

                }).catch(error => {
                    this.$toast.error(this.translations.messages.internal_error, {
                        position: 'top-right'
                    })
                    this.loading = false
                    this.loadingHotel = false
                })
            },
            get_data_hotel_destiny() {
                // this.destiny / destiny_country / destiny_district
                // {code: "89,1610,128", label: "Perú, Lima, Lima"} // http://prntscr.com/123oeip
                let data_destiny = ''
                if (this.destinationsModalHotel !== '') {
                    let code_ = this.destinationsModalHotel_country.code + ',' + this.destinationsModalHotel.code
                    let label_ = this.destinationsModalHotel_country.label + ',' + this.destinationsModalHotel.label
                    if (this.destinationsModalHotel_district !== '') {
                        let destiny_district_label = ''
                        this.destinationsModalHotel_additional_select.forEach((d) => {
                            if (d.code === this.destinationsModalHotel_district) {
                                destiny_district_label = d.label
                            }
                        })

                        code_ += ',' + this.destinationsModalHotel_district
                        label_ += ',' + destiny_district_label
                    }
                    data_destiny = {
                        code: code_,
                        label: label_
                    }
                }
                return data_destiny
            },
            async searchQuoteOpen(category_id, not_update, grouped_code = null) {
                this.loading = true
                this.blockPage = true
                this.quote_open_is_multiregion = null;
                this.switchValue = false;
                await axios.get(window.a3BaseQuoteServerURL + 'api/quote/byUserStatus/2?lang=' + localStorage.getItem('lang') + '&client_id=' + localStorage.getItem('client_id')).then(response => {

                    this.ranges = [{
                        from: 1,
                        to: 1,
                        simple: 0,
                        double: 0,
                        triple: 0
                    }]

                    if (response.data.length > 0) {
                        this.language_for_download = localStorage.getItem('lang')
                        this.quote_id = response.data[0].id
                        this.quote_open = response.data[0]
                        this.quote_open.withHeader = true //activado
                        this.quote_open_is_multiregion = response.data[0].is_multiregion
                        this.refPax = localStorage.getItem('client_name'),
                            this.quote_open.withClientLogo = 3 //de vacio a 3
                        this.quote_open.radioCategories =
                            (this.quote_open.categories.length > 0) ?
                            this.quote_open.categories[0].id : ''

                        if (this.quote_open_is_multiregion) {
                            // AQUI EL SWITCH LATAM
                            this.switchValue = true;
                        } else {
                            this.switchValue = false;
                        }
                        if (this.quote_open.discount != null && this.quote_open.discount > 0) {
                            this.use_discount = true
                            this.discount = this.quote_open.discount
                            this.discount_detail = this.quote_open.discount_detail
                            this.discount_user_permission =
                                (this.quote_open.discount_user_permission) ?
                                this.quote_open.discount_user_permission :
                                ''
                        }

                        if (this.quote_open.file.file_code !== null) {
                            this.file.file_code = this.quote_open.file.file_code
                            this.file.file_reference = this.quote_open.file.file_reference
                            this.file.client = this.quote_open.file.client
                            this.has_file = true
                            category_id = this.quote_open.file.type_class_id
                        } else {
                            this.has_file = false
                            this.file = {
                                file_code: '',
                                file_reference: '',
                                client: {
                                    id: '',
                                    code: '',
                                    name: ''
                                }
                            }

                            // Validación del tipo de usuario..
                            // console.log("Usuario logeado:", this.user_type_id)

                            if (this.user_type_id != 3) {
                                this.file.file_reference = this.quote_open.name
                                localStorage.setItem('file_reference', this.file.file_reference)
                            }
                        }

                        if (this.quote_open.file_id > 0) {
                            this.has_file = false
                            this.file.file_code = this.quote_open.file_number
                            this.file.file_reference = this.quote_open.reservation.customer_name
                            this.file.client.code = this.quote_open.reservation.client_code
                            this.file.client.name = ''
                            this.file.client.id = this.quote_open.reservation.client_id
                            category_id = this.quote_open.reservation.type_class_id
                        }

                        this.editing_quote = response.data[0].editing_quote_user.editing
                        this.editing_quote_user = response.data[0].editing_quote_user.user
                        this.operation = response.data[0].operation
                        this.new_order_related = response.data[0].order_related
                        if (this.new_order_related > 0 && !response.data[0].name.toLowerCase().includes("copia")) {
                            this.readonly = true;
                        } else {
                            this.readonly = false
                        }

                        if (this.operation === 'ranges' && response.data[0].ranges.length > 0) {
                            this.ranges = response.data[0].ranges
                        }

                        if (this.operation === 'passengers') {
                            this.passengers = response.data[0].passengers
                            if (response.data[0].people.length > 0) {
                                this.quantity_persons.adults = response.data[0].people[0].adults
                                this.quantity_persons.child = response.data[0].people[0].child

                                if (this.passengers.length == 0) {
                                    this.updatePeople(true)
                                }
                            } else {
                                this.quantity_persons.adults = 0
                                this.quantity_persons.child = 0
                            }
                        }
                        this.notes = response.data[0].notes
                        this.age_child = (this.quantity_persons.child > 0) ? response.data[0].age_child : []
                        this.validateChilds()
                        this.quote_date = this.formatDate(this.quote_open.date_in, '-', '/', 1)
                        this.quote_date_estimated = (this.quote_open.estimated_travel_date != '' && this.quote_open.estimated_travel_date != null) ? this.formatDate(this.quote_open.estimated_travel_date, '-', '/', 1) : ''

                        if (this.quote_date_estimated === '') {
                            this.quote_date_estimated = this.quote_date
                        }
                        //Todo Logica para colocar la acomodacion
                        this.service_selected_general.single = this.quote_open.accommodation.single
                        this.service_selected_general.double = this.quote_open.accommodation.double
                        this.service_selected_general.double_child = this.quote_open.accommodation.double_child
                        this.service_selected_general.triple = this.quote_open.accommodation.triple
                        this.service_selected_general.triple_child = this.quote_open.accommodation.triple_child

                        this.control_service_selected_general.single = this.quote_open.accommodation.single
                        this.control_service_selected_general.double = this.quote_open.accommodation.double
                        this.control_service_selected_general.double_child = this.quote_open.accommodation.double_child
                        this.control_service_selected_general.triple = this.quote_open.accommodation.triple
                        this.control_service_selected_general.triple_child = this.quote_open.accommodation.triple_child

                        this.quote_name = this.quote_open.name
                        this.markup = response.data[0].markup
                        this.markup_readonly = response.data[0].markup_readonly
                        this.price_dynamic_markup = response.data[0].markup

                        if (not_update == undefined) {
                            this.add_hotel_date = moment(this.quote_open.date_in).format('DD/MM/Y')
                            this.add_service_date = moment(this.quote_open.date_in).format('DD/MM/Y')
                            this.add_extensions_date = moment(this.quote_open.date_in).format('DD/MM/Y')
                        }

                        this.activeTabCategory(category_id)
                        this.quote_service_type_id = this.quote_open.service_type_id

                        let occupation_done = false
                        this.categories_selected = []

                        console.log("CATEGORIES: ", this.quote_open.categories);

                        this.quote_open.categories.forEach((_c, _k) => {
                            // _c.tabActive = (_k == 0) ? 'active' : ''

                            _c.checkAddService = false
                            _c.checkAddHotel = false
                            _c.checkAddExtension = false

                            if (_c.tabActive == 'active') {
                                _c.checkAddService = true
                                _c.checkAddHotel = true
                                _c.checkAddExtension = true
                            }

                            let category = this.categories.find(c => c.id === _c.type_class_id);

                            if (category) {
                                category.checked = true;

                                // Evitar duplicados
                                if (!this.categories_selected.some(c => c.id === category.id)) {
                                    this.categories_selected.push(category);
                                }
                            }

                            /*
                            this.categories.forEach(c => {
                                if (c.id == _c.type_class_id) {
                                    c.checked = true
                                    this.categories_selected.push(c)
                                }
                            })
                            */

                            // Validate Rates && Rooms
                            _c.services.forEach(_s => {

                                /*
                                if(this.has_file) {
                                    _s.locked = true;
                                }
                                */

                                if (_s.type === 'group_header') {
                                    _s.simple_enabled = true
                                    _s.double_enabled = true
                                    _s.double_child_enabled = true
                                    _s.triple_enabled = true
                                    _s.triple_child_enabled = true
                                }
                                if (_s.type === 'hotel') {
                                    _s.simple_enabled = true
                                    _s.double_enabled = true
                                    _s.double_child_enabled = true
                                    _s.triple_enabled = true
                                    _s.triple_child_enabled = true

                                    //Todo Si la ocupacion es una habitacion simple
                                    if (_s.service_rooms.length > 0 && _s.service_rooms[0].rate_plan_room.room.room_type.occupation === 1) {
                                        _s.simple_enabled = false
                                    }

                                    //Todo Si la ocupacion es una habitacion doble
                                    if (_s.service_rooms.length > 0 && _s.service_rooms[0].rate_plan_room.room.room_type.occupation === 2) {
                                        _s.double_enabled = false
                                    }

                                    //Todo Si la ocupacion es una habitacion triple
                                    if (_s.service_rooms.length > 0 && _s.service_rooms[0].rate_plan_room.room.room_type.occupation === 3) {
                                        _s.triple_enabled = false
                                    }

                                    //Todo Si la ocupacion es una habitacion triple
                                    if (_s.child > 0 && _s.double_child > 0) {
                                        _s.double_child_enabled = false
                                    }

                                    if (_s.child > 0 && _s.triple_child > 0) {
                                        _s.triple_child_enabled = false
                                    }

                                }

                                if (_s.type === 'service') {

                                    // "13/04/2022" .weekday()
                                    // console.log(_s.date_in)
                                    // let date_in_ = moment(_s.date_in).format('ddd D MMM')
                                    let date_in_ = this.formatDate(_s.date_in, '/', '/', 1)
                                    // console.log(date_in_)
                                    // 2022/04/13
                                    let weekday_ = moment(date_in_).weekday()
                                    // console.log(weekday_) // 3 miercoles

                                    // schedule_id
                                    if (_s.service.schedules.length > 0) {
                                        if (_s.schedule_id === null || _s.schedule_id == '') {
                                            let schedule_id_found_featured = 0
                                            _s.service.schedules.forEach((schedule, index) => {
                                                if (schedule.featured == 1) {
                                                    schedule_id_found_featured++
                                                    _s.schedule_id = _s.service.schedules[index].id
                                                }
                                            })
                                            if (schedule_id_found_featured === 0) {
                                                _s.schedule_id = _s.service.schedules[0].id
                                            }

                                        } else {
                                            let schedule_id_found = 0
                                            _s.service.schedules.forEach((schedule) => {
                                                if (schedule.id === _s.schedule_id) {
                                                    schedule_id_found++
                                                }
                                            })
                                            if (schedule_id_found === 0) {
                                                _s.schedule_id = _s.service.schedules[0].id
                                            }
                                        }
                                    }
                                    _s.service.schedules = this.format_schedule_show(_s.schedule_id, weekday_, _s.service.schedules)
                                }
                            })

                        })

                        if (not_update == undefined) {
                            this.quote_open.id_original = ''
                            this.quote_open.logs.forEach(log => {
                                if (log.type == 'editing_quote') {
                                    this.quote_open.id_original = log.object_id

                                    let vm = this
                                    setTimeout(function() {
                                        vm.modalPassengers(vm.quote_id, vm.passengers.length, false)
                                    }, 10)
                                }
                            })
                        }

                        // if(response.data[0].updated_at == null || response.data[0].updated_at == '')
                        // {
                        //     this.updateOccupationHotelGeneral()
                        // }
                    } else {

                        this.quote_id = null
                        this.quote_open = ''
                        this.operation = 'ranges'
                        this.passengers = []
                        this.quantity_persons.adults = 0
                        this.quantity_persons.child = 0

                        this.notes = []
                        this.quote_date = ''
                        this.quote_name = ''
                        this.quote_service_type_id = ''
                    }

                    this.set_service_selected_generals()

                    if (this.add_service_date == '') {
                        this.add_service_date = moment(this.quote_open.date_in).format('DD/MM/Y')
                    }
                    if (this.add_hotel_date == '') {
                        this.add_hotel_date = moment(this.quote_open.date_in).format('DD/MM/Y')
                    }


                    if (this.service_selected.single != '' && this.service_selected.double != '') {
                        this.checkExistsPassengerService()
                    }

                    if (localStorage.getItem('client_id') == '') {
                        document.getElementById('_overlay').style.display = ''
                        this.$toast.success(this.translations.validations.rq_client, {
                            position: 'top-right'
                        })
                    } else {
                        document.getElementById('_overlay').style.display = 'none'
                    }

                    if (this.update_passengers_first_time) {
                        this.update_passengers_first_time = false
                        this.generatePassenger(false)
                        if (localStorage.getItem('user_type_id') == 4) {
                            this.updateMarkup(1)
                        }
                    }

                    // this.quoteMe()

                    if (grouped_code != null) {
                        let show = true;
                        this.quote_open.categories.forEach(c => {
                            c.services.forEach(s => {
                                if (grouped_code === s.grouped_code) {
                                    s.grouped_show = show
                                }
                            })
                        })
                    }

                    this.no_reload = false

                    setTimeout(() => {
                        if (localStorage.getItem('request_pakage') != 1) {
                            this.loading = false
                            this.blockPage = false
                        }
                        this.validate_client_file()
                    }, 100)


                }).catch(error => {
                    const apiMessage = error && error.response && error.response.data && error.response.data.message
                        ? error.response.data.message
                        : this.translations.messages.internal_error
                    this.$toast.error(apiMessage, {
                        position: 'top-right'
                    })
                    console.log(error)
                    this.loading = false
                    this.blockPage = false
                    document.getElementById('_overlay').style.display = 'none'
                })

            },
            format_schedule_show: function(schedule_id, weekday_, schedules) {
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
                            [{
                                    id_parent: ini.service_schedule_id,
                                    id_ini: ini.id,
                                    id_fin: fin.id,
                                    code: 'monday',
                                    ini: ini.monday,
                                    fin: fin.monday,
                                    day_choosed: (weekday_ === 1 && schedule_id === schedule.id)
                                },
                                {
                                    id_parent: ini.service_schedule_id,
                                    id_ini: ini.id,
                                    id_fin: fin.id,
                                    code: 'tuesday',
                                    ini: ini.tuesday,
                                    fin: fin.tuesday,
                                    day_choosed: (weekday_ === 2 && schedule_id === schedule.id)
                                },
                                {
                                    id_parent: ini.service_schedule_id,
                                    id_ini: ini.id,
                                    id_fin: fin.id,
                                    code: 'wednesday',
                                    ini: ini.wednesday,
                                    fin: fin.wednesday,
                                    day_choosed: (weekday_ === 3 && schedule_id === schedule.id)
                                },
                                {
                                    id_parent: ini.service_schedule_id,
                                    id_ini: ini.id,
                                    id_fin: fin.id,
                                    code: 'thursday',
                                    ini: ini.thursday,
                                    fin: fin.thursday,
                                    day_choosed: (weekday_ === 4 && schedule_id === schedule.id)
                                },
                                {
                                    id_parent: ini.service_schedule_id,
                                    id_ini: ini.id,
                                    id_fin: fin.id,
                                    code: 'friday',
                                    ini: ini.friday,
                                    fin: fin.friday,
                                    day_choosed: (weekday_ === 5 && schedule_id === schedule.id)
                                },
                                {
                                    id_parent: ini.service_schedule_id,
                                    id_ini: ini.id,
                                    id_fin: fin.id,
                                    code: 'saturday',
                                    ini: ini.saturday,
                                    fin: fin.saturday,
                                    day_choosed: (weekday_ === 6 && schedule_id === schedule.id)
                                },
                                {
                                    id_parent: ini.service_schedule_id,
                                    id_ini: ini.id,
                                    id_fin: fin.id,
                                    code: 'sunday',
                                    ini: ini.sunday,
                                    fin: fin.sunday,
                                    day_choosed: (weekday_ === 0 && schedule_id === schedule.id)
                                },
                            ]
                        )
                    }
                })
                // console.log(arrayNew)
                return arrayNew
            },

            set_service_selected_generals() {

                if (this.quote_open.length > 0) {
                    this.quote_open.categories.forEach((c) => {
                        if (c.tabActive === 'active' &&
                            (this.service_selected_general.single === 0 &&
                                this.service_selected_general.double === 0 &&
                                this.service_selected_general.triple === 0)) {
                            c.services.forEach((s) => {
                                if (s.type === 'hotel') {
                                    this.service_selected_general.single = s.single
                                    this.service_selected_general.double = s.double
                                    this.service_selected_general.double_child = s.double_child
                                    this.service_selected_general.triple = s.triple
                                    this.service_selected_general.triple_child = s.triple_child

                                    this.control_service_selected_general.single = s.single
                                    this.control_service_selected_general.double = s.double
                                    this.control_service_selected_general.double_child = s.double_child
                                    this.control_service_selected_general.triple = s.triple
                                    this.control_service_selected_general.triple_child = s.triple_child
                                }
                            })
                        }
                    })
                }

                if (this.service_selected_general.single === 0 &&
                    this.service_selected_general.double === 0 &&
                    this.service_selected_general.triple === 0 &&
                    localStorage.getItem('service_selected_general_single') !== null) {
                    // buscar en local storage
                    this.service_selected_general.single = parseInt(localStorage.getItem('service_selected_general_single'))
                    this.service_selected_general.double = parseInt(localStorage.getItem('service_selected_general_double'))
                    this.service_selected_general.triple = parseInt(localStorage.getItem('service_selected_general_triple'))

                    this.control_service_selected_general.single = parseInt(localStorage.getItem('service_selected_general_single'))
                    this.control_service_selected_general.double = parseInt(localStorage.getItem('service_selected_general_double'))
                    this.control_service_selected_general.triple = parseInt(localStorage.getItem('service_selected_general_triple'))
                }

            },
            async saveQuotePrev() {

                const requiredMarkets = [4, 19, 20];
                const markets = this.quote_open && Array.isArray(this.quote_open.markets) ?
                    this.quote_open.markets : [];
                const allMarkets = requiredMarkets.every(num => markets.includes(num));
                if (!allMarkets && !this.new_order_related) {
                    this.$toast.error('Por favor ingrese el número de orden', {
                        position: 'top-right'
                    });
                    return;
                }

                let vm = this
                const response = await vm.validateChilds()

                if (response) {
                    this.saveQuote()
                }
            },
            async saveQuote(recalculo = true) {
                let vm = this

                if (this.quote_service_type_id == '') {
                    this.$toast.warning(this.translations.validations.rq_type_of_services, {
                        position: 'top-right'
                    })
                    return
                }
                if (this.quote_name == '') {
                    this.$toast.warning(this.translations.validations.rq_name_quote, {
                        position: 'top-right'
                    })
                    return
                }

                if (this.quote_date == '') {
                    this.$toast.warning(this.translations.validations.rq_date_start, {
                        position: 'top-right'
                    })
                    return
                }

                let category_ids = []
                this.categories.forEach(_c => {
                    if (_c.checked) {
                        category_ids.push(_c.id)
                    }
                })

                if (category_ids.length == 0) {
                    this.$toast.warning(this.translations.validations.rq_category, {
                        position: 'top-right'
                    })
                    return
                }

                this.loading = true

                let _date_quote = (this.quote_date_estimated != undefined && this.quote_date_estimated != '') ? this.quote_date_estimated : this.quote_date

                let data = {}
                if (this.quantity_persons.adults > 0) {
                    data = {
                        name: this.quote_name,
                        date: this.formatDate(this.quote_date, '/', '-', 1),
                        categories: category_ids,
                        service_type_id: this.quote_service_type_id,
                        passengers: this.passengers,
                        people: this.quantity_persons,
                        notes: this.notes,
                        client_id: localStorage.getItem('client_id'),
                        operation: 'passengers',
                        date_estimated: this.formatDate(_date_quote, '/', '-', 1)
                    }
                } else {
                    data = {
                        name: this.quote_name,
                        date: this.formatDate(this.quote_date, '/', '-', 1),
                        categories: category_ids,
                        service_type_id: this.quote_service_type_id,
                        ranges: this.ranges,
                        notes: this.notes,
                        client_id: localStorage.getItem('client_id'),
                        operation: 'ranges',
                        date_estimated: this.formatDate(_date_quote, '/', '-', 1)
                    }
                }

                if (this.quote_open == '') {
                    await axios.post(window.a3BaseQuoteServerURL + 'api/quotes', data).then(response => {
                        if (recalculo == true) {
                            this.quote_id = response.data.quote_open.id
                            this.quote_open = response.data.quote_open
                            this.ranges = response.data.quote_open.ranges
                            this.notes = response.data.quote_open.notes
                            this.$root.$emit('reloadQuotes')
                            this.$root.$emit('updateMenu')
                            this.searchQuoteOpen(this.quote_open.categories[0].id)
                            this.loading = false
                            this.quoteMeOnly(false)
                        }
                    }).catch(error => {
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right'
                        })
                        // console.log(error)
                        this.loading = false
                    })
                } else {
                    await axios.put(window.a3BaseQuoteServerURL + 'api/quotes/' + this.quote_open.id, data).then(async response => {
                        if (response.data.success) {
                            this.$toast.success(this.translations.messages.saved_correctly, {
                                position: 'top-right'
                            })

                            if (recalculo == true) {

                                let new_orden = this.new_order_related
                                await this.quoteMeOnly(this.withDiscard)

                                setTimeout(() => {
                                    vm.new_order_related = new_orden
                                    vm.relaciOrderV2()

                                }, 1200)
                            }

                        } else {
                            this.$toast.error(this.translations.messages.internal_error, {
                                position: 'top-right'
                            })
                        }
                        this.loading = false
                    }).catch(error => {
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right'
                        })
                        // console.log(error)
                        this.loading = false
                    })
                }
            },
            getTodayFormat: function() {
                ahora = new Date()

                dia = ahora.getDate()
                anoActual = ahora.getFullYear()
                mesActual = ahora.getMonth() + 1
                mesActual = (mesActual <= 9) ? '0' + mesActual : mesActual
                diaActual = (dia <= 9) ? '0' + dia : dia
                inicio = diaActual + '/' + mesActual + '/' + anoActual
                return inicio
            },
            formatDate: function(_date, charFrom, charTo, orientation) {
                _date = _date.split(charFrom)
                _date =
                    (orientation) ?
                    _date[2] + charTo + _date[1] + charTo + _date[0] :
                    _date[0] + charTo + _date[1] + charTo + _date[2]
                return _date
            },
            toggleAllCategories() {
                this.categories.forEach(c => {
                    c.checked = this.checkedAllCategories
                })
                if (this.checkedAllCategories) {
                    this.categories_selected = this.categories
                } else {
                    this.categories_selected = []
                }
            },
            toggleCategory(cate) {
                cate.checked = cate.checked

                this.categories_selected = []
                this.categories.forEach(c => {
                    if (c.checked) {
                        this.categories_selected.push(c)
                    }
                })

            },
            toggleTabCategory(category) {
                this.loading = true
                this.quote_open.categories.forEach(c => {
                    if (c.id == category.id) {
                        c.tabActive = 'active'
                    } else {
                        c.tabActive = ''
                    }
                })
                this.categoryActive = category
                this.loading = false
            },
            activeTabCategory(category_id) {
                let activated = false
                this.quote_open.categories.forEach(c => {
                    if (c.type_class_id == category_id) {
                        c.tabActive = 'active'
                        this.categoryActive = c
                        activated = true
                    } else {
                        c.tabActive = ''
                    }
                })

                if (category_id == '' && this.quote_open.categories.length > 0) {
                    this.quote_open.categories[0].tabActive = 'active'
                    this.categoryActive = this.quote_open.categories[0]
                }

                if (category_id != '' && activated == false && this.quote_open.categories.length > 0) {
                    this.quote_open.categories.forEach(c => {
                        if (c.id == category_id) {
                            c.tabActive = 'active'
                            this.categoryActive = c
                            activated = true
                        } else {
                            c.tabActive = ''
                        }
                    })
                }

            },
            getPackagesSelected: function() {
                axios.get(baseExternalURL + 'api/packages/selected')
                    .then((result) => {
                        this.package_selected = result.data
                    })
            },
            viewFormResponse: function(note) {
                document.getElementById('ico').className = 'response-disable'
                document.getElementById('res').className = 'response-disable'

                Vue.set(note, 'create_response', true)

            },
            createRange: function() {

                // console.log(this.ranges)

                let to = this.ranges[this.ranges.length - 1].to
                this.ranges.push({
                    id: null,
                    from: parseInt(to) + 1,
                    to: parseInt(to) + 1,
                    simple: 0,
                    double: 0,
                    triple: 0
                })
                if (this.quote_id != null) {
                    // axios.post('api/quote/ranges', {
                    //     quote_id: this.quote_id,
                    //     from: parseInt(to) + 1,
                    //     to: parseInt(to) + 1,
                    // }).then(response => {
                    //     this.ranges[this.ranges.length - 1].id = response.data.range_id
                    //     // console.log(this.ranges)
                    // }).catch(error => {
                    //     this.$toast.error(this.translations.messages.internal_error, {
                    //         position: 'top-right'
                    //     })
                    //     // console.log(error)
                    // })
                }
            },
            async updateRange(range, index) {
                if (range.id != null) {
                    await axios.patch(window.a3BaseQuoteServerURL + 'api/quote/ranges/' + range.id, range).then(response => {

                    }).catch(error => {
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right'
                        })
                        // console.log(error)
                    })
                } else {

                    await axios.post(window.a3BaseQuoteServerURL + 'api/quote/ranges', {
                        quote_id: this.quote_id,
                        from: parseInt(range.from),
                        to: parseInt(range.to),
                    }).then(response => {
                        this.ranges[index].id = response.data.range_id
                    }).catch(error => {
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right'
                        })
                    })
                }
            },
            deleteRange: function(index_range) {

                // if (this.ranges[index_range].id !== null) {
                //     axios.delete('api/quote/ranges/' + this.ranges[index_range].id).then(response => {

                //     }).catch(error => {
                //         this.$toast.error(this.translations.messages.internal_error, {
                //             position: 'top-right'
                //         })
                //         // console.log(error)
                //     })
                // }
                this.ranges.splice(index_range, 1)

                if (this.ranges.length === 0) {
                    this.ranges.push({
                        id: null,
                        from: 1,
                        to: 1,
                        simple: 0,
                        double: 0,
                        triple: 0
                    })
                    // axios.post('api/quote/ranges', {
                    //     quote_id: this.quote_id,
                    //     from: 1,
                    //     to: 1,
                    // }).then(response => {
                    //     Vue.set(this.ranges[0], 'id', response.data.range_id)

                    // }).catch(error => {
                    //     this.$toast.error(this.translations.messages.internal_error, {
                    //         position: 'top-right'
                    //     })
                    //     // console.log(error)
                    // })
                }
            },
            getUser: function() {
                axios.get('api/user').then(response => {
                    this.user = response.data
                }).catch(error => {
                    this.$toast.error(this.translations.messages.internal_error, {
                        position: 'top-right'
                    })
                    // console.log(error)
                })
            },
            createNote: function() {
                if (this.note_comment != null && this.note_comment !== '') {
                    this.notes.push({
                        id: null,
                        comment: this.note_comment,
                        status: 1,
                        quote_id: this.quote_id,
                        user_name: this.user.name,
                        user_id: this.user.id,
                        responses: [],
                        edit: false
                    })
                    if (this.quote_id != null) {
                        axios.post(window.a3BaseQuoteServerURL + 'api/quote/notes', {
                            comment: this.note_comment,
                            status: 1,
                            quote_id: this.quote_id,
                            user_id: this.user.id
                        }).then(response => {
                            Vue.set(this.notes[this.notes.length - 1], 'id', response.data.note_id)
                        }).catch(error => {
                            this.$toast.error(this.translations.messages.internal_error, {
                                position: 'top-right'
                            })
                            // console.log(error)
                        })
                    }
                    this.note_comment = ''
                }
            },
            editNote: function(note) {
                if (note.id != null) {
                    if (note.comment != null && note.comment !== '') {
                        axios.patch(window.a3BaseQuoteServerURL + 'api/quote/notes/' + note.id, note).then(response => {
                            Vue.set(note, 'edit', false)
                        }).catch(error => {
                            this.$toast.error(this.translations.messages.internal_error, {
                                position: 'top-right'
                            })
                            // console.log(error)
                        })
                    }
                } else {
                    Vue.set(note, 'edit', false)
                }
            },
            showEditNote: function(note) {
                Vue.set(note, 'edit', true)
            },
            createResponse: function(index_note) {
                if (this.note_response !== '') {
                    this.notes[index_note].responses.push({
                        id: null,
                        parent_note_id: this.notes[index_note].id,
                        comment: this.note_response,
                        status: 1,
                        quote_id: this.quote_id,
                        user_name: this.user.name,
                        user_id: this.user.id,
                        edit: false
                    })

                    if (this.notes[index_note].id != null) {
                        axios.post(window.a3BaseQuoteServerURL + 'api/quote/notes/responses', {
                            parent_note_id: this.notes[index_note].id,
                            comment: this.note_response,
                            status: 1,
                            quote_id: this.quote_id,
                            user_id: this.user.id
                        }).then(response => {

                        }).catch(error => {
                            this.$toast.error(this.translations.messages.internal_error, {
                                position: 'top-right'
                            })
                            // console.log(error)
                        })
                    }
                    this.note_response = ''
                    Vue.set(this.notes[index_note], 'create_response', false)
                }
            },
            async generatePassenger(update_people, call) {
                // console.log(this.quantity_persons)
                // console.log(this.passengers)
                // console.log(typeof this.passengers)
                // console.log(this.quote_id)

                if (!(this.passengers.length > 0)) {
                    // console.log('reseteo de paxs..')
                    this.passengers = []
                }
                if (parseInt(this.quantity_persons.adults) === 0) {
                    this.quantity_persons.child = 0
                    this.operation = 'ranges'
                } else {
                    this.operation = 'passengers'
                    let count_adults = this.countPassengers('ADL')

                    let count_childrens = this.countPassengers('CHD')
                    if (parseInt(this.quantity_persons.adults) > count_adults) {
                        let adults_new = parseInt(this.quantity_persons.adults) - count_adults
                        for (let i = 0; i < adults_new; i++) {
                            this.passengers.push({
                                id: null,
                                first_name: '',
                                last_name: '',
                                gender: '',
                                birthday: '',
                                doctype_iso: '',
                                document_number: '',
                                country_iso: '',
                                email: '',
                                phone: '',
                                notes: '',
                                type: 'ADL',
                            })
                        }
                    }

                    if (parseInt(this.quantity_persons.child) > count_childrens) {
                        let childrens_new = parseInt(this.quantity_persons.child) - count_childrens
                        for (let i = 0; i < childrens_new; i++) {
                            this.passengers.push({
                                id: null,
                                first_name: '',
                                last_name: '',
                                gender: '',
                                birthday: '',
                                doctype_iso: '',
                                document_number: '',
                                country_iso: '',
                                email: '',
                                phone: '',
                                notes: '',
                                type: 'CHD',
                            })
                        }
                    }

                    if (parseInt(this.quantity_persons.adults) < count_adults) {
                        let adults_deleted = count_adults - parseInt(this.quantity_persons.adults)
                        for (let k = this.passengers.length; k < 0; k--) {
                            if (adults_deleted > 0) {
                                if (this.passengers.type == 'ADL') {
                                    if (this.passengers[k].id != null) {
                                        this.deletePassenger(this.passengers[k].id)
                                    }
                                    this.passengers.splice(k, 1)
                                    adults_deleted--
                                }
                            }
                        }
                    }

                    if (parseInt(this.quantity_persons.child) < count_childrens) {
                        let childrens_deleted = count_childrens - parseInt(this.quantity_persons.child)
                        for (let l = this.passengers.length; l < 0; l--) {
                            if (childrens_deleted > 0) {
                                if (this.passengers.type == 'CHD') {
                                    if (this.passengers[l].id != null) {
                                        this.deletePassenger(this.passengers[l].id)
                                    }
                                    this.passengers.splice(l, 1)
                                    childrens_deleted--
                                }
                            }
                        }
                    }
                }
                if (this.quote_id != null && update_people) {
                    await this.updatePeople(call)
                }
            },
            countPassengers: function(type) {
                let count_adults = 0
                let count_childrens = 0
                if (type == 'ADL') {
                    for (let i = 0; i < this.passengers.length; i++) {
                        if (this.passengers[i].type == 'ADL') {
                            count_adults++
                        }
                    }
                    return count_adults
                }
                if (type == 'CHD') {
                    for (let i = 0; i < this.passengers.length; i++) {
                        if (this.passengers[i].type == 'CHD') {
                            count_childrens++
                        }
                    }
                    return count_childrens
                }
            },
            saveOrUpdatePassengers: function() {
                axios.post(window.a3BaseQuoteServerURL + 'api/quote/passengers', {
                    passengers: this.passengers,
                    quote_id: this.quote_id,
                }).then(response => {
                    this.passengers = response.data
                }).catch(error => {
                    this.$toast.error(this.translations.messages.internal_error, {
                        position: 'top-right'
                    })
                    // console.log(error)
                })
            },
            deletePassenger: function(passenger_id) {
                axios.delete(window.a3BaseQuoteServerURL + 'api/quote/passengers/' + passenger_id).then(response => {}).catch(error => {
                    this.$toast.error(this.translations.messages.internal_error, {
                        position: 'top-right'
                    })
                    // console.log(error)
                })
            },
            async updatePeople(_update) {
                this.loading = true
                await axios.put(window.a3BaseQuoteServerURL + 'api/quote/people', {
                    people: this.quantity_persons,
                    passengers: this.passengers,
                    quote_id: this.quote_id,
                    client_id: localStorage.getItem('client_id')
                }).then(response => {
                    this.passengers = response.data

                    if (_update == undefined) {
                        this.searchQuoteOpen(this.categoryActive.id)
                    }
                }).catch(error => {
                    this.$toast.error(this.translations.messages.internal_error, {
                        position: 'top-right'
                    })
                    // console.log(error)
                    this.loading = false
                })
            },
            copyFirstPassengerData: function() {
                axios.put(window.a3BaseQuoteServerURL + 'api/quote/copy_first_passenger_data', {
                    passenger: this.passengers[0],
                    quote_id: this.quote_id
                }).then(response => {
                    this.passengers = response.data
                }).catch(error => {
                    this.$toast.error(this.translations.messages.internal_error, {
                        position: 'top-right'
                    })
                    // console.log(error)
                })
            },
            openModalService: function(category, service) {

                this.similar_services = []

                this.quote_open.categories.forEach((c) => {

                    this.similar_services.push({
                        category_id: c.id,
                        category_name: c.type_class.translations[0].value,
                        services: []
                    })

                    c.services.forEach((s) => {
                        if (s.type === service.type && s.id !== service.id) {
                            if (s.type === 'flight') {
                                if (s.code_flight === service.code_flight) {
                                    this.similar_services[this.similar_services.length - 1]
                                        .services.push({
                                            id: s.id,
                                            extension_id: s.extension_id,
                                            quote_category_id: s.quote_category_id,
                                            code: (s.code_flight !== null) ? s.code_flight : '-',
                                            name: 'Flight',
                                            date: s.date_in,
                                            date_in: s.date_in,
                                            type: s.type,
                                            object_id: s.object_id,
                                            check: (s.date_in === service.date_in),
                                        })
                                }
                            }
                            if (s.type === 'service') {
                                if (s.service.aurora_code === service.service.aurora_code) {
                                    this.similar_services[this.similar_services.length - 1]
                                        .services.push({
                                            id: s.id,
                                            extension_id: s.extension_id,
                                            quote_category_id: s.quote_category_id,
                                            code: s.service.aurora_code,
                                            name: s.service.name,
                                            date: s.date_in,
                                            date_in: s.date_in,
                                            type: s.type,
                                            object_id: s.object_id,
                                            check: (s.date_in === service.date_in)
                                        })
                                }
                            }
                            if (s.type === 'hotel') {
                                if (s.hotel.channel[0].code === service.hotel.channel[0].code) {
                                    this.similar_services[this.similar_services.length - 1]
                                        .services.push({
                                            id: s.id,
                                            extension_id: s.extension_id,
                                            quote_category_id: s.quote_category_id,
                                            code: s.hotel.channel[0].code,
                                            name: s.hotel.name,
                                            date: s.date_in,
                                            date_in: s.date_in,
                                            type: s.type,
                                            object_id: s.object_id,
                                            check: (s.date_in === service.date_in)
                                        })
                                }
                            }
                        }
                    })

                    if (this.similar_services[this.similar_services.length - 1].services.length === 0) {
                        this.similar_services.splice(this.similar_services.length - 1, 1)
                    }

                })

                // console.log(service)

                this.editService = true
                this.add_service_date = service.date_in
                this.service_old = service

                // Destin
                if (service.service.service_destination[0].state_id !== null) {
                    this.destinationsModalService_select.forEach(d => {
                        if (d.parent_code == service.service.service_destination[0].country_id &&
                            d.code == service.service.service_destination[0].state_id) {
                            this.destinationsModalService = d
                            this.change_service_destiny_districts()
                        }
                    })
                }

                // Origin
                if (service.service.service_origin[0].state_id !== null) {
                    this.originModalService_select.forEach(o => {
                        if (o.code == service.service.service_origin[0].state_id &&
                            o.parent_code == service.service.service_origin[0].country_id) {
                            this.originModalService = o
                            this.change_service_origin_districts()
                        }
                    })
                }

                this.searchServices(true)
            },
            replaceService(me) {
                let _service_rate_id = me.rateChoosed

                if (_service_rate_id == '' || _service_rate_id == null) {
                    this.$toast.warning(this.translations.validations.rq_rates, {
                        position: 'top-right'
                    })
                    return
                }

                let _date = this.formatDate(this.add_service_date, '/', '-', 1)

                let services_ = []
                services_.push(this.service_old)

                if (this.similar_services.length > 0) {
                    this.similar_services.forEach((similar_categ) => {
                        similar_categ.services.forEach((similar_service) => {
                            if (similar_service.check) {
                                services_.push(similar_service)
                            }
                        })
                    })
                }

                let data = {
                    type: 'service',
                    quote_service_id_old: this.service_old.id,
                    service_rate_id_old: this.service_old.service_rate.id,
                    object_id: me.id,
                    quote_id: this.quote_id,
                    date_in: _date,
                    date_out: _date,
                    service_rate_ids: [_service_rate_id],
                    services: services_
                }

                axios.put(window.a3BaseQuoteServerURL + 'api/quote/replace/service', data).then(response => {
                    if (response.data.success) {
                        this.$toast.success(this.translations.messages.service_superseded, {
                            position: 'top-right'
                        })
                        this.searchQuoteOpen(this.categoryActive.id)
                    } else {
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right'
                        })
                        // console.log(response.data)
                    }
                }).catch(error => {
                    this.$toast.error(this.translations.messages.internal_error, {
                        position: 'top-right'
                    })
                    // console.log(error)
                    this.loading = false
                })
            },
            showCategories: function() {
                this.editService = false
            },
            deleteService(service) {

                this.loading = true

                let services_ = []

                if (service.type === 'group_header') {
                    this.quote_open.categories.forEach(_c => {
                        if (_c.id == service.quote_category_id) {
                            _c.services.forEach(_s => {
                                if (_s.grouped_code == service.grouped_code && _s.grouped_type == 'row') {
                                    services_.push(_s)
                                }
                            })
                        }
                    })
                } else {
                    services_.push(service)
                    if (this.similar_services.length > 0) {
                        this.similar_services.forEach((similar_categ) => {
                            similar_categ.services.forEach((similar_service) => {
                                if (similar_service.check) {
                                    services_.push(similar_service)
                                }
                            })
                        })
                    }
                }
                axios.post(window.a3BaseQuoteServerURL + 'api/quotes/' + this.quote_id + '/services', {
                        services: services_
                    })
                    .then(response => {
                        this.closeModalWillRemoveService()
                        this.searchQuoteOpen(this.categoryActive.id)
                        // this.quoteMe()
                        // this.loading = false
                        this.$toast.success(this.translations.label.services_removed_successfully, {
                            position: 'top-right'
                        })
                    }).catch(error => {
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right'
                        })
                        this.loading = false
                    })
            },
            deleteServiceConfirm: function(service) {
                var r = confirm(this.translations.label.are_you_sure_you_to_delete)
                if (r == true) {
                    this.deleteService(service)
                }
            },
            addServiceFromCheck(service) {

                this.services_deleted.push(service)
                if (service.extension_id != null && service.extension_id != '') {
                    this.quote_open.categories.forEach(_c => {
                        if (_c.id == service.quote_category_id) {
                            _c.services.forEach(_s => {
                                if (_s.parent_service_id == service.id) {
                                    // console.log(_s)
                                    this.services_deleted.push(_s)
                                }
                            })
                        }
                    })
                }

            },
            addServiceDelete: function(service) {
                if (service.type == 'group_header') {
                    if (this.groups_for_delete[service.grouped_code] === undefined) {
                        this.groups_for_delete[service.grouped_code] = true
                    } else {
                        this.groups_for_delete[service.grouped_code] = !this.groups_for_delete[service.grouped_code]
                    }

                    this.quote_open.categories.forEach((_c, cat) => {
                        if (_c.id == service.quote_category_id) {
                            _c.services.forEach((_s, ser) => {
                                if (_s.grouped_code == service.grouped_code && _s.grouped_type == 'row') {
                                    if (this.groups_for_delete[service.grouped_code]) {
                                        this.services_deleted.push(_s)
                                        this.quote_open.categories[cat].services[ser].selected = true
                                    } else {
                                        for (let i = 0; i < this.services_deleted.length; i++) {
                                            if (this.services_deleted[i].id == _s.id) {
                                                this.services_deleted.splice(i, 1)
                                                this.quote_open.categories[cat].services[ser].selected = false
                                            }
                                        }
                                    }
                                }
                            })
                        }
                    })

                } else {

                    if (this.services_deleted.length === 0) {
                        this.addServiceFromCheck(service)
                    } else {
                        let checkExistService = false
                        for (let i = 0; i < this.services_deleted.length; i++) {
                            if (this.services_deleted[i].id == service.id) {
                                checkExistService = true
                                this.services_deleted.splice(i, 1)

                                if (service.extension_id != null && service.extension_id != '') {

                                    let id_services_deleted = []
                                    this.quote_open.categories.forEach(_c => {
                                        if (_c.id == service.quote_category_id) {
                                            _c.services.forEach(_s => {
                                                if (_s.parent_service_id == service.id) {
                                                    id_services_deleted[_s.id] = true
                                                }
                                            })
                                        }
                                    })
                                    for (let ii = 0; ii < this.services_deleted.length; ii++) {
                                        if (id_services_deleted[this.services_deleted[ii].id]) {
                                            this.services_deleted.splice(ii, 1)
                                        }
                                    }
                                }

                            }
                        }
                        if (!checkExistService) {
                            this.addServiceFromCheck(service)
                        }
                    }
                    this.checkHeader(service);
                }
                // console.log(this.services_deleted,this.groups_for_delete)
            },
            checkHeader(service) {
                let categoriaSelected = null;
                let serviceSeleted = null;
                let selectedRow = false;
                if (service.grouped_code) {
                    this.quote_open.categories.forEach((_c, cat) => {
                        if (_c.id == service.quote_category_id) {

                            _c.services.forEach((_s, ser) => {

                                if (_s.grouped_code == service.grouped_code) {

                                    if (_s.type == 'group_header') {
                                        categoriaSelected = cat;
                                        serviceSeleted = ser;
                                    } else {
                                        if (_s.selected == true) {
                                            selectedRow = true
                                        }
                                    }
                                }

                            })
                        }
                    })

                    if (selectedRow == false) {
                        this.quote_open.categories[categoriaSelected].services[serviceSeleted].selected = false
                        this.groups_for_delete[service.grouped_code] = false
                    } else {
                        this.quote_open.categories[categoriaSelected].services[serviceSeleted].selected = true
                        this.groups_for_delete[service.grouped_code] = true
                    }
                }
            },
            showModalDeleteServices: function() {
                if (this.services_deleted.length > 0) {

                    this.$refs['removeAllService'].show()
                } else {
                    this.$toast.error(this.translations.label.validate_selected, {
                        position: 'top-right'
                    })
                }
            },
            closeModalremoveAllService: function() {
                this.$refs['removeAllService'].hide()
            },
            deleteServices: function() {
                this.$refs['removeAllService'].hide()
                if (this.services_deleted.length > 0) {

                    this.loading = true
                    this.blockPage = true
                    axios.post(window.a3BaseQuoteServerURL + 'api/quotes/' + this.quote_id + '/services', {
                            services: this.services_deleted
                        })
                        .then(response => {
                            this.services_deleted = [];
                            this.searchQuoteOpen(this.categoryActive.id)
                        }).catch(error => {
                            this.$toast.error(this.translations.messages.internal_error, {
                                position: 'top-right'
                            })
                            this.loading = false
                            this.blockPage = true
                        })
                } else {
                    this.$toast.error(this.translations.label.validate_selected, {
                        position: 'top-right'
                    })
                }
            },
            showDatePickerQuote: function() {
                Vue.set(this.quote_open, 'disabled', false)
            },
            showDatePickerService: function(service) {
                Vue.set(service, 'disabled', false)
            },
            async updateDateInService(service, index_service) {
                let r = false;
                let days = 0;

                if (service.hasOwnProperty('disabled')) {
                    try {
                        await this.$dialog.confirm("¿Modificar las fechas de los siguientes servicios?", {
                            okText: 'Sí',
                            cancelText: 'No, mantener las fechas',
                            reverse: false
                        });
                        r = true;
                    } catch (e) {
                        r = false;
                        console.log('Cancelado por el usuario');
                    }
                    const date1 = moment(service.date_in, 'DD/MM/YYYY');
                    const date2 = moment(service.date_in_format, 'YYYY-MM-DD');
                    days = date1.diff(date2, 'days');
                }

                this.loading = true
                // this.blockPage = true
                let _break = 0
                if (service.parent_service_id != null && service.parent_service_id != '') {
                    this.quote_open.categories.forEach(_c => {
                        if (_c.id == service.quote_category_id) {
                            _c.services.forEach(_s => {
                                if (_break == 0) {
                                    if (_s.id == service.parent_service_id ||
                                        (_s.parent_service_id == service.parent_service_id &&
                                            _s.id != service.id)) {
                                        index_service++
                                    }
                                    if (_s.id == service.id) {
                                        _break++
                                    }
                                }
                            })
                        }
                    })
                    // console.log(index_service)
                }
                if (service.hasOwnProperty('disabled')) {

                    let quote_service_ids = []
                    if (service.type == 'group_header') {
                        this.quote_open.categories.forEach(c => {
                            c.services.forEach(s => {
                                if (service.grouped_code === s.grouped_code && s.type != 'group_header') {
                                    quote_service_ids.push(s.id)
                                }
                            })
                        })
                    } else {
                        quote_service_ids = [service.id]
                    }

                    if (r) {
                        quote_service_ids = [
                            ...this.quote_open.categories.find((category) => category.id === this.categoryActive.id).services.filter((service, index) => index >= index_service && service.type != 'group_header').map((service) => service.id)
                        ];
                    }

                    quote_service_ids = [...new Set(quote_service_ids)];

                    axios.put(window.a3BaseQuoteServerURL + 'api/quote/update/date_in/services', {
                        index_service: index_service,
                        quote_service_ids: quote_service_ids,
                        date_in: this.formatDate(service.date_in, '/', '-', 1),
                        client_id: localStorage.getItem('client_id'),
                        quote_id: this.quote_id,
                        move_services: r ? 1 : 0,
                        days: days,
                    }).then(async (response) => {
                        this.no_reload = true
                        await this.executeUpdateRateHpPull(localStorage.getItem('client_id'), quote_service_ids);
                        this.searchQuoteOpen(this.categoryActive.id)

                    }).catch(error => {
                        if (error.response.status === 400) {
                            this.$toast.warning(error.response.data.error, {
                                position: 'top-right'
                            })
                        } else {
                            this.$toast.error(this.translations.messages.internal_error, {
                                position: 'top-right'
                            })
                        }

                        this.loading = false
                    })
                }
            },
            async updateDateInQuote() {
                if (this.quote_open.hasOwnProperty('disabled')) {
                    if (this.quote_id != null) {
                        let r = confirm(this.translations.messages.confirm_rescheduled_dates)
                        if (r === true) {
                            this.loading = true
                            axios.put(window.a3BaseQuoteServerURL + 'api/quote/update/date_in', {
                                lang: localStorage.getItem('lang'),
                                quote_id: this.quote_id,
                                date_in: this.formatDate(this.quote_date, '/', '-', 1)
                            }).then(async response => {
                                await this.executeUpdateRateHpPull(localStorage.getItem('client_id'));
                                this.searchQuoteOpen(this.categoryActive.id)
                            }).catch(error => {
                                this.$toast.error(this.translations.messages.internal_error + ': ' + error.response.data, {
                                    position: 'top-right'
                                })
                                this.quote_date = this.formatDate(this.quote_open.date_in, '-', '/', 1)
                                this.loading = false
                            })
                        } else {
                            this.quote_date = this.formatDate(this.quote_open.date_in, '-', '/', 1)
                            this.updateDatePickerQuote += 1
                        }
                    }
                }
            },
            updateNameQuote: function() {
                if (this.quote_id != null) {
                    axios.put(window.a3BaseQuoteServerURL + 'api/quote/update/name', {
                        quote_id: this.quote_id,
                        name: this.quote_name
                    }).then(response => {
                        this.$toast.success(this.translations.messages.updated_successfully, {
                            position: 'top-right'
                        })
                    }).catch(error => {
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right'
                        })
                        // console.log(error)
                        this.loading = false
                    })
                }
            },
            discardChanges: function() {
                this.force_fully_destroy_loading = true
                axios.delete(window.a3BaseQuoteServerURL + 'api/quote/' + this.quote_id + '/forcefullyDestroy')
                    .then(response => {
                        this.force_fully_destroy_loading = false
                        if (response.data.success) {
                            this.categories.forEach(c => {
                                c.checked = false
                            })
                            this.categories_selected = []
                            this.file.file_code = ''
                            this.file.file_reference = ''
                            this.quote_date_estimated = ''
                            this.has_file = false
                            this.editing_quote = false
                            this.searchQuoteOpen(this.categoryActive.id)
                            this.closeModalClose()
                            this.$root.$emit('reloadQuotes')
                        } else {
                            this.$toast.error(this.translations.messages.internal_error, {
                                position: 'top-right'
                            })
                        }
                    }).catch(error => {
                        this.force_fully_destroy_loading = false
                        this.$toast.error(this.translations.messages.internal_error + '. (' + error + ')', {
                            position: 'top-right'
                        })
                        // console.log(error)
                    })
            },
            createOrDeleteCategory: function(category) {
                if (this.quote_id != null) {
                    let data = {
                        quote_id: this.quote_id,
                        category_id: category.id,
                        operation: null
                    }
                    let deleteServer = false
                    if (category.checked) {
                        data.operation = 'new'
                    } else {
                        for (let i = 0; i < this.quote_open.categories.length; i++) {
                            if (category.id === this.quote_open.categories[i].type_class_id) {
                                data.operation = 'delete'
                                deleteServer = true
                            }
                        }
                    }
                    if (data.operation === 'new' || (data.operation === 'delete' && deleteServer)) {
                        axios.post(window.a3BaseQuoteServerURL + 'api/quote/create_or_delete/category', data)
                            .then(response => {

                                this.searchQuoteOpen(this.categoryActive.id)

                            })
                            .catch(error => {
                                this.$toast.error(this.translations.messages.internal_error, {
                                    position: 'top-right'
                                })
                                // console.log(error)
                                this.loading = false
                            })
                    }
                }
            },
            async updatePassengerService(service) {
                this.loading = true
                this.blockPage = true
                axios.post(window.a3BaseQuoteServerURL + 'api/quote/update/services/passengers', {
                    service_id: service.id,
                    adult: service.adult,
                    child: service.child,
                    client_id: localStorage.getItem('client_id'),
                    quote_id: this.quote_id
                }).then(async response => {
                    await this.executeUpdateRateHpPull(localStorage.getItem('client_id'), [service.id]);
                    this.searchQuoteOpen(this.categoryActive.id, '', service.grouped_code)
                }).catch(error => {
                    this.$toast.error(this.translations.messages.internal_error, {
                        position: 'top-right'
                    })
                    // console.log(error)
                    this.loading = false
                })

            },
            checkMoveService: function(services) {
                if (!this.no_reload) {
                    axios.post(window.a3BaseQuoteServerURL + 'api/quote/update_order_and_date/services', {
                        services: services,
                        quote_id: this.quote_id,
                        client_id: localStorage.getItem('client_id')
                    }).then(response => {
                        this.searchQuoteOpen(this.categoryActive.id)
                        // this.quoteMe()
                    }).catch(error => {
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right'
                        })
                        // console.log(error)
                        this.loading = false
                    })
                }
            },
            showSelectQuantityPassengersService: function(service) {
                (service.showQuantityPassengers) ? Vue.set(service, 'showQuantityPassengers', false): Vue.set(service, 'showQuantityPassengers', true)
            },
            showSelectQuantityPassengersService2: function(service) {
                (service.showQuantityPassengers) ? Vue.set(service, 'showQuantityPassengers', false): Vue
                    .set(service, 'showQuantityPassengers', true)
                this.isEditingPrice = true;
                console.log(service)

                if (service.type === 'service' && service.price_dynamic === 1) {
                    if (this.price_dynamic_amount > 0) {
                        axios.post('api/quote-dynamic-price', {
                            quote_id: this.quote_id,
                            quote_service_id: service.id,
                            object_id: service.service.id,
                            client_id: localStorage.getItem('client_id'),
                            type: service.service.type,
                            price_adl: this.price_dynamic_amount,
                            markup: this.price_dynamic_markup
                        }).then(response => {

                            const newPrice = parseFloat(this.price_dynamic_amount) + (parseFloat(this.price_dynamic_amount) * (parseFloat(this.price_dynamic_markup) / 100));
                            Vue.set(service.import, 'price_ADL', newPrice);

                            if (response.data.success) {
                                this.$toast.success(response.data.message, {
                                    position: 'top-right'
                                });

                            }
                        }).catch(error => {
                            if (error.response && error.response.status === 422 && error.response.data.errors) {
                                // Si hay errores de validación en la respuesta, mostramos el primer mensaje de error de validación
                                const errors = error.response.data.errors;
                                const firstError = Object.values(errors)[0][0];
                                this.$toast.error(firstError, {
                                    position: 'top-right'
                                });
                            } else {
                                this.$toast.error('Ocurrió un error al enviar la solicitud', {
                                    position: 'top-right'
                                });
                            }

                        });

                    }
                }

                if (service.type === 'hotel' && service.price_dynamic === 1) {
                    if (this.price_dynamic_amount > 0) {
                        axios.post('api/quote-dynamic-price', {
                            quote_id: this.quote_id,
                            quote_service_id: service.id,
                            object_id: service.hotel.id,
                            client_id: localStorage.getItem('client_id'),
                            type: service.type,
                            price_adl: this.price_dynamic_amount,
                            markup: this.price_dynamic_markup
                        }).then(response => {

                            const newPrice = parseFloat(this.price_dynamic_amount) + (parseFloat(this.price_dynamic_amount) * (parseFloat(this.price_dynamic_markup) / 100));
                            Vue.set(service.import_amount, 'price_ADL', newPrice);

                            // Recalcula solo el subTotal de cada fila,
                            // copiando explícitamente los demás campos (date y child):
                            const updatedDeta = service.import_amount.deta.map(d => ({
                                date: d.date, // copia la fecha
                                child: d.child, // copia la cantidad de niños
                                adult: service.import_amount.adult * newPrice,
                                subTotal: service.import_amount.adult * newPrice
                            }));

                            Vue.set(service.import_amount, 'deta', updatedDeta);

                            // Recalcula el subtotal general (suma de todos los subTotals)
                            const newSubtotal = updatedDeta.reduce((sum, d) => sum + d.subTotal, 0);
                            Vue.set(service.import_amount, 'subtotal', newSubtotal);

                            // Finalmente recalcula el total (subtotal + taxes)
                            Vue.set(service.import_amount, 'total', newSubtotal + parseFloat(service.import_amount.taxes));

                            if (response.data.success) {
                                this.$toast.success(response.data.message, {
                                    position: 'top-right'
                                });

                            }
                        }).catch(error => {
                            if (error.response && error.response.status === 422 && error.response.data.errors) {
                                // Si hay errores de validación en la respuesta, mostramos el primer mensaje de error de validación
                                const errors = error.response.data.errors;
                                const firstError = Object.values(errors)[0][0];
                                this.$toast.error(firstError, {
                                    position: 'top-right'
                                });
                            } else {
                                this.$toast.error('Ocurrió un error al enviar la solicitud', {
                                    position: 'top-right'
                                });
                            }

                        });

                    }

                }
            },
            close_modal_notes: function() {
                this.service_notes = ''
                document.getElementById('close-modal-notes').click()
            },
            close_modal_real_notes: function() {
                this.service_real_notes = ''
                document.getElementById('close-modal-real-notes').click()
            },
            closeModalSaveAs: function() {
                this.new_name_quote = ''
                this.$refs['modal_guardar_como'].hide()
            },
            closeModalPassengersService: function() {
                document.getElementById('close_modal_passengers_service').click()
            },
            closeModalQuoteMe: function() {
                this.$refs['modal_cotizar'].hide()
            },
            saveAsQuote: function() {
                if (this.new_name_quote.trim() === '') {
                    this.$toast.error(this.translations.validations.rq_name_quote, {
                        position: 'top-right'
                    })
                    return
                }
                this.loading_save_as = true
                axios.post(window.a3BaseQuoteServerURL + 'api/quote/save_as', {
                    quote_id: this.quote_id,
                    new_name_quote: this.new_name_quote
                }).then(response => {
                    this.loading_save_as = false
                    if (response.data.success) {
                        this.$toast.success(this.translations.label.new_quote + ' ' + this.translations.messages.saved_correctly, {
                            position: 'top-right'
                        })
                        this.closeModalSaveAs()
                        this.new_name_quote = ''
                        this.searchQuoteOpen(this.categoryActive.id)
                        this.$root.$emit('updateMenu')
                        this.$root.$emit('reloadQuotes')
                    } else {
                        // console.log(response)
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right'
                        })
                    }
                }).catch(error => {
                    this.$toast.error(this.translations.messages.internal_error, {
                        position: 'top-right'
                    })
                    this.loading_save_as = false
                })
            },
            selectServiceSelected: function(service) {
                this.service_selected = service
                this.checkExistsPassengerService()
            },
            willSavePassengerService(service, passenger) {
                this.service_selected = service
                if (this.passengers_service.service_id == service.id) {
                    let passenger_exist = false
                    for (let i = 0; i < this.passengers_service.passengers.length; i++) {
                        if (this.passengers_service.passengers[i].id == passenger.id) {
                            this.passengers_service.passengers[i].checked = passenger.checked
                            passenger_exist = true
                        }
                    }
                    if (!passenger_exist) {
                        this.passengers_service.passengers.push(passenger)
                    }
                } else {
                    this.passengers_service.service_id = service.id
                    this.passengers_service.passengers = []
                    this.passengers_service.passengers.push(passenger)
                }
            },
            async savePassengerService() {

                setTimeout(() => {
                    let el = document.getElementById('dropdown_passenger')
                    el.click()
                }, 10)

                let grouped_code = this.service_selected.grouped_code
                // console.log(grouped_code);
                this.loading = true
                this.blockPage = true
                axios.post(window.a3BaseQuoteServerURL + 'api/quote/service/passenger', {
                    passengers: this.passengers_service.passengers,
                    service_id: this.service_selected.id,
                    quote_id: this.quote_id
                }).then(async response => {
                    await this.executeUpdateRateHpPull(localStorage.getItem('client_id'), [this.service_selected.id]);
                    this.service_selected = response.data.service
                    this.searchQuoteOpen(this.categoryActive.id, '', grouped_code)
                    this.passengers_service.service_id = null
                    this.passengers_service.passengers = []

                }).catch(error => {
                    this.$toast.error(this.translations.messages.internal_error, {
                        position: 'top-right'
                    })
                })
            },
            checkExistsPassengerService: function() {
                for (let k = 0; k < this.passengers.length; k++) {
                    this.$set(this.passengers[k], 'checked', false)
                }
                if (this.service_selected.single != '' && this.service_selected.double != '') {
                    if (this.service_selected.passengers.length > 0) {
                        for (let j = 0; j < this.passengers.length; j++) {
                            for (let i = 0; i < this.service_selected.passengers.length; i++) {
                                if (this.service_selected.passengers[i].quote_passenger_id == this.passengers[j].id) {
                                    this.$set(this.passengers[j], 'checked', true)
                                }
                            }
                        }
                    }
                }
            },
            closeModalOccupationHotel: function() {
                this.$refs['modal_occupation_hotel'].hide()
            },
            updateOccupationHotel: function() {
                axios.put(window.a3BaseQuoteServerURL + 'api/quote/service/occupation_hotel', {
                    service_id: this.service_selected.id,
                    simple: this.service_selected.single,
                    double: this.service_selected.double,
                    double_child: this.service_selected.double_child,
                    triple: this.service_selected.triple,
                    triple_child: this.service_selected.triple_child,
                    client_id: localStorage.getItem('client_id'),
                    quote_id: this.quote_id
                }).then(response => {
                    this.searchQuoteOpen(this.categoryActive.id)
                    this.$toast.success(response.data, {
                        position: 'top-right'
                    })
                }).catch(error => {
                    this.$toast.error(this.translations.messages.internal_error, {
                        position: 'top-right'
                    })
                    // console.log(error)
                    this.loading = false
                })
            },
            validateOccupationHotelGeneral: function() {
                let total_pax = parseInt(this.quantity_persons.adults) + parseInt(this.quantity_persons.child)
                let total_single = parseInt(this.control_service_selected_general.single)
                let total_double = parseInt(this.control_service_selected_general.double * 2)
                let total_triple = parseInt(this.control_service_selected_general.triple * 3)
                let total_occupation_hotel = total_single + total_double + total_triple
                if (total_occupation_hotel != total_pax && this.quote_open.operation == 'passengers') {
                    return false
                }
                return true;
            },
            async validateDistribution() {

                if (this.quote_open.operation == 'passengers') {

                    this.loading = true

                    let single = this.control_service_selected_general.single > 0 ? this.control_service_selected_general.single : 0;
                    let double = this.control_service_selected_general.double > 0 ? this.control_service_selected_general.double : 0;
                    let triple = this.control_service_selected_general.triple > 0 ? this.control_service_selected_general.triple : 0;

                    let response = await axios.get(window.a3BaseQuoteServerURL + 'api/quote/service/occupation_paseengers_hotel', {
                        params: {
                            single: single,
                            double: double,
                            triple: triple,
                            adults: this.quantity_persons.adults,
                            child: this.quantity_persons.child,
                            quote_id: this.quote_id,
                            client_id: localStorage.getItem('client_id'),
                        }
                    });

                    this.distribution_passengers = response.data.quoteDistributions
                    // console.log(response.data.quoteAccommodationPassengers);
                    this.loading = false
                    this.$refs['modal_occupation_hotel_pax'].show()

                } else {
                    this.updateOccupationHotelGeneral();
                }


            },
            async updateOccupationHotelGeneralPax() {
                this.loading = true
                let response = await axios.post(window.a3BaseQuoteServerURL + 'api/quote/service/occupation_paseengers_hotel', {
                    distribution_passengers: this.distribution_passengers,
                    quote_id: this.quote_id
                });
                if (response.data.error) {

                    this.$toast.error(response.data.error, {
                        position: 'top-right'
                    })


                    this.loading = false
                    return false;
                }

                await this.updateOccupationHotelGeneral();

                this.$refs['modal_occupation_hotel_pax'].hide()
            },

            async updateOccupationHotelGeneral() {

                this.quantity_persons.ages_child = this.age_child
                this.loading_occupation = true
                // if (this.validateOccupationHotelGeneral()) {
                let response = await axios.put(window.a3BaseQuoteServerURL + 'api/quote/service/occupation_hotel/general', {
                    simple: this.control_service_selected_general.single,
                    double: this.control_service_selected_general.double,
                    triple: this.control_service_selected_general.triple,
                    double_child: this.control_service_selected_general.double_child,
                    triple_child: this.control_service_selected_general.triple_child,
                    client_id: localStorage.getItem('client_id'),
                    lang: localStorage.getItem('lang') ?? 'en',
                    quote_id: this.quote_id
                });


                localStorage.setItem('service_selected_general_single', this.control_service_selected_general.single)
                localStorage.setItem('service_selected_general_double', this.control_service_selected_general.double)
                localStorage.setItem('service_selected_general_double_child', this.control_service_selected_general.double_child)
                localStorage.setItem('service_selected_general_triple', this.control_service_selected_general.triple)
                localStorage.setItem('service_selected_general_triple_child', this.control_service_selected_general.triple_child)

                this.service_selected_general.single = this.control_service_selected_general.single
                this.service_selected_general.double = this.control_service_selected_general.double
                this.service_selected_general.triple = this.control_service_selected_general.triple
                this.service_selected_general.double_child = this.control_service_selected_general.double_child
                this.service_selected_general.triple_child = this.control_service_selected_general.triple_child

                let procesado = [];
                let promises = [];
                let hotels_add_rooms = Object.entries(response.data.hotels_add_rooms)

                for (const entry of hotels_add_rooms) {

                    let rooms_add = entry[1];
                    let hotel = rooms_add[0];

                    procesado.push(rooms_add);
                    promises.push(
                        axios.post('services/hotels/available/quote', { // buscamos los hoteles disponibles para poder agregar la habitacion
                            "hotels_id": [
                                hotel.hotel_id
                            ],
                            "date_from": hotel.date_in,
                            "date_to": hotel.date_out,
                            "client_id": localStorage.getItem('client_id'),
                            "quantity_rooms": 1,
                            // "quantity_persons_rooms": [
                            //     {
                            //         "adults": hotel.occupation,
                            //         "child": 0,
                            //         "ages_child": [
                            //             {
                            //                 "child": 1,
                            //                 "age": 0
                            //             }
                            //         ]
                            //     }
                            // ],
                            quantity_persons_rooms: [],
                            "typeclass_id": hotel.typeclass_id,
                            "destiny": {
                                "code": hotel.destiny_code,
                                "label": hotel.destiny_label
                            },
                            "lang": localStorage.getItem('lang'),
                            "set_markup": (this.markup != '' || this.markup != null) ? this.markup : 0,
                            'zero_rates': true
                        })
                    )

                }

                if (promises.length > 0) {

                    let result = await Promise.all(promises)


                    let promisesInternal = [];
                    for (let r = 0; r < result.length; r++) {

                        let response2 = result[r];
                        let rooms_add = procesado[r];
                        const hotelList = response2?.data?.data?.[0]?.city?.hotels;
                        let rooms_disponibles = Array.isArray(hotelList) && hotelList.length > 0 ?
                            hotelList[0].rooms : [];

                        rooms_add.forEach(room_new => {

                            for (let p = 0; p < rooms_disponibles.length; p++) {
                                let room = rooms_disponibles[p];
                                if ([1, 2, 3].includes(room.room_type_id) && room_new.occupation == room.occupation) { // filtramos solo habitaciones de tipo standard
                                    // if(room_new.occupation == room.occupation){  // no hacemos filtro por tipo de habitacion solo por ocupacion

                                    for (let i = 0; i < room.rates.length; i++) {
                                        let rate = room.rates[i];
                                        if (rate.rates_plans_type_id == 2) { // filtramos solo las tarifas regulares


                                            // promisesInternal.push({
                                            //     "quote_id": this.quote_id,
                                            //     "quote_service_id": room_new.quote_service_id,
                                            //     "rate_plan_room_ids": [],
                                            //     "lang": localStorage.getItem('lang'),
                                            //     "rate_plan_rooms_choose": [
                                            //         {
                                            //             "rate_plan_room_id": rate.rateId,
                                            //             "choose": true,
                                            //             "occupation": room_new.occupation,
                                            //             "on_request": 1
                                            //         }
                                            //     ],
                                            //     "cant" : room_new.cant
                                            // })

                                            promisesInternal.push(
                                                axios.post(window.a3BaseQuoteServerURL + 'api/quote/service/' + room_new.quote_service_id + '/rooms/addFromHeader', {
                                                    "quote_id": this.quote_id,
                                                    "quote_service_id": room_new.quote_service_id,
                                                    "rate_plan_room_ids": [],
                                                    "lang": localStorage.getItem('lang'),
                                                    "rate_plan_rooms_choose": [{
                                                        "rate_plan_room_id": rate.rateId,
                                                        "hyperguest_pull": rate.rateProviderMethod ? rate.rateProviderMethod : 0,
                                                        "rate_plan_id": rate.ratePlanId,
                                                        "room_id": room.room_id,
                                                        "choose": true,
                                                        "occupation": room_new.occupation,
                                                        "on_request": 1
                                                    }],
                                                    "cant": room_new.cant,
                                                    "quote_service": room_new.quote_service ? room_new.quote_service : ''
                                                })
                                            )
                                            p = i = 999; // salimos de los 2 niveles
                                        }
                                    }

                                }
                            }


                        })
                    }
                    // console.log(promisesInternal);

                    if (promisesInternal.length > 0) {

                        await Promise.all(promisesInternal)

                        this.loading_occupation = false

                        if (this.quote_open.operation == 'passengers') {
                            await this.updatePeople(false);
                            await this.executeUpdateRateHpPull(localStorage.getItem('client_id'));
                            await this.searchQuoteOpen(this.categoryActive.id)
                        } else {
                            await this.searchQuoteOpen(this.categoryActive.id)
                        }

                        //    this.searchQuoteOpen(this.categoryActive.id)
                        this.$toast.success(response.data.message, {
                            position: 'top-right'
                        })

                        this.closeModalOccupationHotel()


                    } else {

                        if (this.quote_open.operation == 'passengers') {
                            await this.updatePeople(false);
                            await this.executeUpdateRateHpPull(localStorage.getItem('client_id'));
                            await this.searchQuoteOpen(this.categoryActive.id)
                        } else {
                            await this.searchQuoteOpen(this.categoryActive.id)
                        }
                        this.loading_occupation = false
                        this.closeModalOccupationHotel()
                    }


                } else {

                    if (this.quote_open.operation == 'passengers') {
                        await this.updatePeople(false);
                        await this.executeUpdateRateHpPull(localStorage.getItem('client_id'));
                        await this.searchQuoteOpen(this.categoryActive.id)
                    } else {
                        await this.searchQuoteOpen(this.categoryActive.id)
                    }
                    this.loading_occupation = false
                    this.closeModalOccupationHotel()
                }


            },

            closeModalOccupationHotelPax() {
                this.$refs['modal_occupation_hotel_pax'].hide()
            },
            setServiceHotelSelected: function(service) {
                this.service_selected = service
            },
            async updateNightsService(service) {

                this.loading = true
                this.blockPage = true

                let quote_service_ids = []

                if (service.type == 'group_header') {
                    this.quote_open.categories.forEach(c => {
                        c.services.forEach(s => {
                            if (service.grouped_code === s.grouped_code && s.type != 'group_header') {
                                quote_service_ids.push(s.id)
                            }
                        })
                    })
                } else {
                    quote_service_ids = [service.id]
                }

                axios.put(window.a3BaseQuoteServerURL + 'api/quote/nights/service', {
                    quote_service_ids: quote_service_ids,
                    nights: service.nights,
                    client_id: localStorage.getItem('client_id'),
                    quote_id: this.quote_id
                }).then(async response => {
                    // console.log(service.grouped_code);
                    await this.executeUpdateRateHpPull(localStorage.getItem('client_id'), quote_service_ids);
                    this.searchQuoteOpen(this.categoryActive.id, '', service.grouped_code)
                    this.$toast.success(this.translations.messages.saved_correctly, {
                        position: 'top-right'
                    })
                }).catch(error => {
                    this.$toast.error(this.translations.messages.internal_error, {
                        position: 'top-right'
                    })
                    // console.log(error)
                    this.loading = false
                })
            },
            async validateAvailableHotels() {
                this.loading = true
                let process_quote_service = [];
                let promises = [];
                for (let i = 0; i < this.quote_open.categories.length; i++) {
                    for (let j = 0; j < this.quote_open.categories[i].services.length; j++) {

                        if (this.quote_open.categories[i].services[j].type === 'hotel' && !this.quote_open.categories[i].services[j].locked) {

                            let data = {}
                            let rate_plan_room_id = (this.quote_open.categories[i].services[j].service_rooms && this.quote_open.categories[i].services[j].service_rooms.length > 0) ? this.quote_open.categories[i].services[j].service_rooms[0].rate_plan_room_id : null
                            if (rate_plan_room_id === null) {
                                continue;
                            }

                            if (this.quote_open.operation == 'passengers') {
                                data = {
                                    'hotels_id': [this.quote_open.categories[i].services[j].object_id],
                                    'rate_plan_room_search': [rate_plan_room_id],
                                    'date_from': this.formatDate(this.quote_open.categories[i].services[j].date_in, '/', '-', 1),
                                    'date_to': this.formatDate(this.quote_open.categories[i].services[j].date_out, '/', '-', 1),
                                    'client_id': localStorage.getItem('client_id'),
                                    'quantity_rooms': 1,
                                    // 'quantity_persons_rooms': [{
                                    //     'adults': this.quote_open.categories[i].services[j].adult,
                                    //     'child': this.quote_open.categories[i].services[j].child,
                                    //     'ages_child': this.age_child
                                    // }],
                                    quantity_persons_rooms: [{
                                        'adults': this.quote_open.categories[i].services[j].adult,
                                        'child': 0,
                                        'ages_child': this.age_child
                                    }],
                                    'typeclass_id': this.quote_open.categories[i].services[j].hotel.typeclass_id,
                                    'destiny': {
                                        'code': this.quote_open.categories[i].services[j].hotel.country.iso + ',' +
                                            this.quote_open.categories[i].services[j].hotel.state.iso,
                                        'label': this.quote_open.categories[i].services[j].hotel.country.translations[0].value + ',' +
                                            this.quote_open.categories[i].services[j].hotel.state.translations[0].value
                                    },
                                    'set_markup': (this.markup != '' || this.markup != null) ? this.markup : 0,
                                    'zero_rates': true,
                                    'lang': localStorage.getItem('lang') ?? 'en',
                                }
                            } else {
                                data = {
                                    'hotels_id': [this.quote_open.categories[i].services[j].object_id],
                                    'rate_plan_room_search': [rate_plan_room_id],
                                    'date_from': this.formatDate(this.quote_open.categories[i].services[j].date_in, '/', '-', 1),
                                    'date_to': this.formatDate(this.quote_open.categories[i].services[j].date_out, '/', '-', 1),
                                    'client_id': localStorage.getItem('client_id'),
                                    'quantity_rooms': 1,
                                    // 'quantity_persons_rooms': [{
                                    //     'adults': 1,
                                    //     'child': 0,
                                    //     'ages_child': this.age_child
                                    // }],
                                    quantity_persons_rooms: [{
                                        'adults': this.quote_open.categories[i].services[j].adult,
                                        'child': 0,
                                        'ages_child': this.age_child
                                    }],
                                    'typeclass_id': this.quote_open.categories[i].services[j].hotel.typeclass_id,
                                    'destiny': {
                                        'code': this.quote_open.categories[i].services[j].hotel.country.iso + ',' +
                                            this.quote_open.categories[i].services[j].hotel.state.iso,
                                        'label': this.quote_open.categories[i].services[j].hotel.country.translations[0].value + ',' +
                                            this.quote_open.categories[i].services[j].hotel.state.translations[0].value
                                    },
                                    'set_markup': (this.markup != '' || this.markup != null) ? this.markup : 0,
                                    'zero_rates': true,
                                    'lang': localStorage.getItem('lang') ?? 'en',
                                }
                            }

                            data.inventory_quote = this.quote_id_original

                            // axios.post('services/hotels/available/quote', data)
                            //     .then((result) => {
                            //         // on_request = 1; ok = 0
                            //         if (result.data.success) {
                            //             if (result.data.data[0].city.hotels.length == 0) {
                            //                 this.$set(this.quote_open.categories[i].services[j], 'on_request', 1)
                            //                 let service = {
                            //                     quote_service_id: this.quote_open.categories[i].services[j].id,
                            //                     on_request: 1,
                            //                 }
                            //                 axios.post('api/quote/categories/update/on_request', service)
                            //                     .then((result) => {
                            //                         // console.log(result.data)
                            //                     })
                            //             } else {
                            //                 let on_request_count = 0
                            //                 for (let k = 0; k < result.data.data[0].city.hotels[0].rooms.length; k++) {

                            //                     if (result.data.data[0].city.hotels[0].rooms[k].rates[0].onRequest == 0) {
                            //                         on_request_count++
                            //                     }

                            //                 }
                            //                 let service = {
                            //                     quote_service_id: this.quote_open.categories[i].services[j].id,
                            //                     on_request: (on_request_count > 0) ? 1 : 0,
                            //                 }
                            //                 axios.post('api/quote/categories/update/on_request', service)
                            //                 .then((result) => {
                            //                     // console.log(result.data)
                            //                 })

                            //             }
                            //         }
                            // })

                            promises.push(axios.post('services/hotels/available/quote', data))

                            process_quote_service.push(this.quote_open.categories[i].services[j].id);
                        }
                    }
                }

                if (promises.length > 0) {

                    let result_promises = await Promise.all(promises)
                    let updateServices = []
                    result_promises.forEach((result, index) => {

                        if (result.data.success) {
                            if (result.data.data[0].city.hotels.length == 0) {
                                // this.$set(this.quote_open.categories[i].services[j], 'on_request', 1)
                                let service = {
                                    quote_service_id: process_quote_service[index],
                                    on_request: 1,
                                }
                                updateServices.push(service);
                                // axios.post('api/quote/categories/update/on_request', service)
                                //     .then((result) => {
                                //         // console.log(result.data)
                                //     })
                            } else {
                                let on_request_count = 0
                                for (let k = 0; k < result.data.data[0].city.hotels[0].rooms.length; k++) {

                                    if (result.data.data[0].city.hotels[0].rooms[k].rates[0].onRequest == 0) {
                                        on_request_count++
                                    }

                                }
                                let service = {
                                    quote_service_id: process_quote_service[index],
                                    on_request: (on_request_count > 0) ? 1 : 0,
                                }
                                updateServices.push(service);
                                // axios.post('api/quote/categories/update/on_request', service)
                                // .then((result) => {
                                //     // console.log(result.data)
                                // })

                            }
                        }

                    })

                    await axios.post(window.a3BaseQuoteServerURL + 'api/quote/categories/update/on_request_multiple', {
                        services_update: updateServices
                    })


                }

                this.loading = false
            },
            async validateChilds() {
                let response = true
                this.age_child.forEach((age, a) => {
                    if (age.age == 0 && response == true) {
                        response = false
                        this.$toast.warning('Por favor primero coloque la edad de los niños.', {
                            position: 'top-right'
                        })
                    }
                })

                if (this.quantity_persons.child > 0 && this.age_child.length === 0) {
                    response = false
                    this.$toast.warning('Por favor primero coloque la edad de los niños.', {
                        position: 'top-right'
                    })
                }
                this.validateAgeChild = response
                return response
            },
            async quoteMePrev() {

                const requiredMarkets = [4, 19, 20];
                const markets = this.quote_open && Array.isArray(this.quote_open.markets) ?
                    this.quote_open.markets : [];
                const allMarkets = requiredMarkets.every(num => markets.includes(num));
                if (!allMarkets && !this.new_order_related) {
                    this.$toast.error('Por favor ingrese el número de orden', {
                        position: 'top-right'
                    });
                    return;
                }

                let validate_has_dynamic_prices = this.validateHasDynamicPrices()

                if (validate_has_dynamic_prices) {
                    this.$toast.warning('No se puede cotizar con precios dinámicos en 0', {
                        position: 'top-right'
                    })
                    return false
                }
                let validate_has_services = this.validateHasServices()
                if (validate_has_services) {
                    this.$toast.warning(this.translations.label.no_added_services_reserve, {
                        position: 'top-right'
                    })
                    return false
                }
                this.$refs['modal_cotizar'].show()
                let vm = this
                let response = await this.validateChilds()

                if (response) {
                    // console.log('Ejecutando cotización..')
                    this.quoteMe()
                } else {
                    // console.log('No se puede ejecutar el modal..')
                    setTimeout(function() {
                        vm.closeModalQuoteMe()
                    }, 1200)
                }
            },
            async quoteMe(_open) {

                if (this.verify_itinerary_errors()) {
                    this.$toast.warning(this.translations.label.observations_validation_text, {
                        position: 'top-right'
                    })
                    let xthis = this
                    setTimeout(function() {
                        xthis.closeModalQuoteMe()
                    }, 1200)
                    return
                }

                await this.validateAvailableHotels()
                this.saveQuote()
                // let error_rooms = 0


                // el metodo saveQuote  hace todo este proceso que sigue.

                // this.blockPage = true
                // axios.put('api/quote/me', {
                //     quote_id: this.quote_id,
                //     client_id: localStorage.getItem('client_id'),
                //     category_id: this.categoryActive.id

                // }).then(response => {
                //     if (this.quote_open.operation === 'passengers') {
                //         this.categoryPassengers = response.data.categories
                //     }
                //     if (this.quote_open.operation === 'ranges') {
                //         this.categoryRanges = response.data.categories
                //     }
                //     this.searchQuoteOpen(this.categoryActive.id)
                //     this.$toast.success(response.data.message, {
                //         position: 'top-right'
                //     })
                //     this.blockPage = false
                // }).catch(error => {
                //     this.$toast.error(this.translations.messages.internal_error, {
                //         position: 'top-right'
                //     })
                //     // console.log(error)
                //     this.loading = false
                //     this.blockPage = false
                // })

            },
            async quoteMeOnly(close = false) {
                if (this.verify_itinerary_errors()) {
                    this.$toast.warning(this.translations.label.observations_validation_text, {
                        position: 'top-right'
                    })
                    let xthis = this
                    setTimeout(function() {
                        xthis.closeModalQuoteMe()
                    }, 1200)
                    return
                }
                this.blockPage = true
                try {
                    const response = await axios.put(window.a3BaseQuoteServerURL + 'api/quote/me', {
                        quote_id: this.quote_id,
                        client_id: localStorage.getItem('client_id'),
                        category_id: this.categoryActive.id
                    })

                    if (this.quote_open.operation === 'passengers') {
                        this.categoryPassengers = response.data.categories
                    }
                    if (this.quote_open.operation === 'ranges') {
                        this.categoryRanges = response.data.categories
                    }
                    if (close) {
                        this.withDiscard = false
                        this.discardChanges()
                        this.closeModalClose()
                    } else {
                        await this.searchQuoteOpen(this.categoryActive.id)
                        this.$toast.success(response.data.message, {
                            position: 'top-right'
                        })
                    }
                    this.blockPage = false
                } catch (error) {
                    this.$toast.error(this.translations.messages.internal_error, {
                        position: 'top-right'
                    })
                    // console.log(error)
                    this.loading = false
                    this.blockPage = false
                }
            },
            closeModalExtension: function() {
                document.getElementById('close_modal_extension').click()
            },
            getExtensions: function() {
                if (this.service_type_id == '') {
                    this.$toast.warning(this.translations.validations.rq_type_of_services, {
                        position: 'top-right'
                    })
                    return
                }

                if (this.type_class_id == '') {
                    this.$toast.warning(this.translations.label.select_category, {
                        position: 'top-right'
                    })
                    return
                }
                this.extensions = []
                this.loading_extension = true
                axios.post(window.a3BaseQuoteServerURL + 'api/quote/extensions', {
                    type_class_id: this.type_class_id,
                    date: this.formatDate(this.add_extensions_date, '/', '-', 1),
                    type_service: this.service_type_id,
                    lang: localStorage.getItem('lang'),
                    filter: this.add_extension_words,
                    package: this.package_extension
                }).then(response => {
                    this.loading_extension = false
                    this.extensions = response.data
                    this.packages_original = response.data
                    this.getCategoryPackages()
                    this.getDestinationsPackages()
                }).catch(error => {
                    this.$toast.error(this.translations.messages.internal_error, {
                        position: 'top-right'
                    })
                    this.loading_extension = false
                })
            },
            filterByDestinyAll: function() {
                for (let i = 0; i < this.filter_by_destiny.length; i++) {
                    if (this.filter_by_destiny[i].status) {
                        this.filter_by_destiny[i].status = false
                    }
                }
                this.extensions = this.packages_original
                if (this.packages_search_category.length > 0) {
                    this.extensions = this.packages_search_category
                }

            },
            filterByDestiny: function(index_destiny) {
                this.unCheckAllDestiny()
                let packages_new = []
                let check_status = false
                for (let i = 0; i < this.filter_by_destiny.length; i++) {
                    if (this.filter_by_destiny[i].status && index_destiny === i) {
                        check_status = true
                        let package_new = this.filterDestiny(this.filter_by_destiny[i].state_id)
                        packages_new = package_new
                    } else {
                        this.filter_by_destiny[i].status = false
                    }
                }
                if (check_status) {
                    this.extensions = packages_new
                } else {
                    this.extensions = this.packages_original
                }
            },
            filterDestiny: function(state_id) {
                let array_package = []
                let packages_original = this.packages_original
                if (this.packages_search_category.length > 0) {
                    packages_original = this.packages_search_category
                }
                for (let i = 0; i < packages_original.length; i++) {
                    for (let d = 0; d < packages_original[i].package_destinations.length; d++) {
                        if (state_id === packages_original[i].package_destinations[d].state.id) {
                            array_package.push(packages_original[i])
                        }
                    }
                }
                return array_package
            },
            getCategoryPackages: function() {
                axios.get(baseExternalURL + 'api/tags/selectBox?lang=' + localStorage.getItem('lang'))
                    .then((result) => {
                        this.category_packages = result.data.data
                        this.generateFilterByCategory()
                        this.generateFilterByNights()
                    }).catch((e) => {
                        //console.log(e)
                    })
            },

            unCheckAllNights: function() {
                $('#all_itineraries').prop('checked', true)
                for (let i = 0; i < this.filter_by_nights.length; i++) {
                    if (this.filter_by_nights[i].status) {
                        $('#all_itineraries').prop('checked', false)
                    }
                }
            },
            filterByNightsAll: function() {
                for (let i = 0; i < this.filter_by_nights.length; i++) {
                    if (this.filter_by_nights[i].status) {
                        this.filter_by_nights[i].status = false
                    }
                }
                this.extensions = this.packages_original
                if (this.packages_search_category.length > 0) {
                    this.extensions = this.packages_search_category
                }

            },
            filterByNights: function(index_nights) {
                this.unCheckAllNights()
                let packages_news = []
                let packages_original = this.packages_original
                if (this.packages_search_category.length > 0) {
                    packages_original = this.packages_search_category
                }
                let check_status = false
                for (let i = 0; i < this.filter_by_nights.length; i++) {
                    if (this.filter_by_nights[i].option == 1 && this.filter_by_nights[i].status && index_nights == i) {
                        check_status = true
                        let packages_new_3 = packages_original.filter(function(package) {
                            return package.nights >= 1 && package.nights <= 3
                        })
                        if (packages_news.length === 0) {
                            packages_news = packages_new_3
                        } else {
                            packages_news = packages_news.concat(packages_new_3)
                        }
                    }

                    if (this.filter_by_nights[i].option == 2 && this.filter_by_nights[i].status && index_nights == i) {
                        check_status = true
                        let packages_new_4_6 = packages_original.filter(function(package) {
                            return package.nights >= 4 && package.nights <= 6
                        })
                        if (packages_news.length === 0) {
                            packages_news = packages_new_4_6
                        } else {
                            packages_news = packages_news.concat(packages_new_4_6)
                        }
                    }

                    if (this.filter_by_nights[i].option == 3 && this.filter_by_nights[i].status && index_nights == i) {
                        check_status = true
                        let packages_new_7_10 = packages_original.filter(function(package) {
                            return package.nights >= 7 && package.nights <= 10
                        })
                        if (packages_news.length === 0) {
                            packages_news = packages_new_7_10
                        } else {
                            packages_news = packages_news.concat(packages_new_7_10)
                        }
                    }

                }

                for (let i = 0; i < this.filter_by_nights.length; i++) {
                    if (this.filter_by_nights[i].status && index_nights != i) {
                        this.filter_by_nights[i].status = false
                    }
                }

                if (check_status) {
                    this.extensions = packages_news
                } else {
                    this.extensions = this.packages_original
                }
            },
            generateFilterByNights: function() {
                let packages_original = this.packages_original
                // this.filter_by_nights = []
                if (this.extensions.length > 0) {
                    packages_original = this.extensions
                }

                let packages_news = []
                for (let i = 0; i < this.filter_by_nights.length; i++) {

                    if (this.filter_by_nights[i].option == 1) {
                        let packages_new_3 = packages_original.filter(function(package) {

                            return package.nights >= 1 && package.nights <= 3

                        })
                        this.$set(this.filter_by_nights[i], 'count', packages_new_3.length)
                        if (packages_news.length === 0) {
                            packages_news = packages_new_3
                        } else {
                            packages_news = packages_news.concat(packages_new_3)
                        }
                    }
                    if (this.filter_by_nights[i].option == 2) {
                        let packages_new_4_6 = packages_original.filter(function(package) {

                            return package.nights >= 4 && package.nights <= 6
                        })
                        this.$set(this.filter_by_nights[i], 'count', packages_new_4_6.length)
                        if (packages_news.length === 0) {
                            packages_news = packages_new_4_6
                        } else {
                            packages_news = packages_news.concat(packages_new_4_6)
                        }
                    }
                    if (this.filter_by_nights[i].option == 3) {
                        let packages_new_7_10 = packages_original.filter(function(package) {

                            return package.nights >= 7 && package.nights <= 10
                        })
                        this.$set(this.filter_by_nights[i], 'count', packages_new_7_10.length)
                        if (packages_news.length === 0) {
                            packages_news = packages_new_7_10
                        } else {
                            packages_news = packages_news.concat(packages_new_7_10)
                        }
                    }
                }

                //this.extensiones = packages_news
            },
            generateFilterByCategory: function() {
                this.filter_by_category = []
                for (let i = 0; i < this.category_packages.length; i++) {
                    let packages = this.packages_original.filter(this.checkCategoryId.bind(this, this.category_packages[i].id))
                    if (packages.length > 0) {
                        this.filter_by_category.push({
                            tag_id: this.category_packages[i].id,
                            tag_name: this.category_packages[i].translations[0].value,
                            count: packages.length,
                            status: false
                        })
                    }
                }
            },
            getDestinationsPackages: function() {
                let packages_original = this.packages_original
                this.filter_by_destiny = []
                if (this.extensions.length > 0) {
                    packages_original = this.extensions
                }
                let filter_by_destiny = []
                for (let i = 0; i < packages_original.length; i++) {
                    for (let d = 0; d < packages_original[i].package_destinations.length; d++) {
                        filter_by_destiny.push({
                            state_id: packages_original[i].package_destinations[d].state_id,
                            name: packages_original[i].package_destinations[d].state.translations[0].value,
                            count: 0,
                            status: false
                        })
                    }
                }

                for (let i = 0; i < filter_by_destiny.length; i++) {
                    let packages = filter_by_destiny.filter(this.checkDestinyId.bind(this, filter_by_destiny[i].state_id))
                    if (packages.length > 0) {
                        this.filter_by_destiny.push({
                            state_id: filter_by_destiny[i].state_id,
                            name: filter_by_destiny[i].name,
                            count: packages.length,
                            status: false
                        })
                    }
                }
                this.filter_by_destiny = this.removeDuplicates(this.filter_by_destiny, 'state_id')

            },
            removeDuplicates: function(originalArray, prop) {
                var newArray = []
                var lookupObject = {}

                for (var i in originalArray) {
                    lookupObject[originalArray[i][prop]] = originalArray[i]
                }
                for (i in lookupObject) {
                    newArray.push(lookupObject[i])
                }
                return newArray
            },
            filterByCategory: function() {
                let packages_new = []
                let check_status = false
                for (let i = 0; i < this.filter_by_category.length; i++) {
                    if (this.filter_by_category[i].status) {
                        check_status = true
                        let package_new = this.packages_original.filter(this.checkCategoryId.bind(this, this.filter_by_category[i].tag_id))
                        if (packages_new.length === 0) {
                            packages_new = package_new
                        } else {
                            packages_new = packages_new.concat(package_new)
                        }
                    }
                }
                // console.log(check_status)
                // console.log(packages_new)
                if (check_status) {
                    this.extensions = packages_new
                    this.packages_search_category = packages_new
                } else {
                    this.extensions = this.packages_original
                    this.packages_search_category = []
                }
            },
            unCheckAllDestiny: function() {
                $('#all_destinations').prop('checked', true)
                for (let i = 0; i < this.filter_by_destiny.length; i++) {
                    if (this.filter_by_destiny[i].status) {
                        $('#all_destinations').prop('checked', false)
                    }
                }
            },
            filterByCategoryAll: function() {

                for (let i = 0; i < this.filter_by_category.length; i++) {
                    this.filter_by_category[i].status = this.check_status_all
                }
                this.extensions = this.packages_original
                this.packages_search_category = []
                this.getDestinationsPackages()
                this.generateFilterByNights()
            },
            checkCategoryId: function(category_id, package) {
                if (package.tag_id === category_id) {
                    return true
                }
            },
            checkDestinyId: function(state, package_destination) {
                if (package_destination.state_id === state) {
                    return true
                }
            },
            addExtension: function() {
                this.loading = true;
                if (this.quote_open.operation === 'passengers') {
                    if (this.quantity_persons.adults == 0) {
                        this.$toast.warning(this.translations.validations.rq_adults, {
                            position: 'top-right'
                        })
                        this.loading = false;
                        return
                    }
                }

                let _categories = []
                this.quote_open.categories.forEach(c => {
                    if (c.checkAddExtension) {
                        _categories.push(c.id)
                    }
                })

                if (_categories.length == 0) {
                    this.$toast.warning(this.translations.validations.rq_category, {
                        position: 'top-right'
                    })
                    this.loading = false;
                    return
                }
                axios.post(window.a3BaseQuoteServerURL + 'api/quote_client/extension', {
                        extension_id: this.extension_selected,
                        service_type_id: this.service_type_id,
                        type_class_id: this.type_class_id,
                        quote_id: this.quote_id,
                        category_ids: _categories,
                        extension_date: this.formatDate(this.add_extensions_date, '/', '-', 1)
                    }).then(response => {
                        return this.validateDistribution();
                    })
                    .then(() => {
                        return this.updateOccupationHotelGeneralPax();
                    })
                    .then(() => {
                        this.$toast.success(this.translations.messages.successfully_added, {
                            position: 'top-right'
                        })
                        this.closeModalExtension()
                        this.searchQuoteOpen(this.categoryActive.id)
                        this.loading = false
                        this.$forceUpdate();
                    })
                    .catch(error => {
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right'
                        })
                        // console.log(error)
                        this.loading = false
                    })
            },
            selectReplaceExtension: function(service) {
                // this.type_class_id = service.package_extension.plan_rates[0].plan_rate_categories[0].type_class_id
                for (var i = 0; i < this.type_class_ids.length; i++) {
                    $('#' + this.type_class_ids[i]).prop('checked', false)
                }
                this.type_class_id = null
                this.add_extensions_date = service.date_in
                this.service_type_id = this.quote_open.service_type_id
                this.extension_selected = service.extension_id
                this.extension_replace = service.extension_id
                this.extension_type_class_replace = service.extension_id + '_' + service.category.type_class_id
                this.service_selected_id = service.id
                // console.log(service)
                this.getExtensions()
            },
            // replaceExtension: function () {
            //     let _categories = []
            //     this.quote_open.categories.forEach(c => {
            //         if (c.checkAddExtension) {
            //             _categories.push(c.id)
            //         }
            //     })

            //     if (_categories.length == 0) {
            //         this.$toast.warning(this.translations.validations.category, {
            //             position: 'top-right'
            //         })
            //         return
            //     }
            //     // if (this.extension_type_class_replace !== (this.extension_selected +'_'+ this.type_class_id)) {
            //     axios.post('api/quote/extension/replace', {
            //         quote_service_id: this.service_selected_id,
            //         extension_replace: this.extension_replace,
            //         extension_id: this.extension_selected,
            //         service_type_id: this.service_type_id,
            //         type_class_id: this.type_class_id,
            //         quote_id: this.quote_id,
            //         category_ids: _categories,
            //         extension_date: this.formatDate(this.add_extensions_date, '/', '-', 1)
            //     }).then(response => {
            //         this.$toast.success(this.translations.extension_replaced_or_successfully_added, {
            //             position: 'top-right'
            //         })
            //         this.searchQuoteOpen(this.categoryActive.id)
            //     }).catch(error => {
            //         this.$toast.error(this.translations.messages.internal_error, {
            //             position: 'top-right'
            //         })
            //         // console.log(error)
            //         this.loading = false
            //     })
            //     // }

            // },
            async updateMarkup(option) {
                this.loading = true
                if (this.quote_id != null || this.quote_id != '') {
                    axios.put(window.a3BaseQuoteServerURL + 'api/update/quote/markup', {
                        quote_id: this.quote_id,
                        markup: this.markup,
                        client_id: localStorage.getItem('client_id'),
                        option: option,
                        user_id: localStorage.getItem('user_id'),
                        user_type_id: localStorage.getItem('user_type_id')

                    }).then(async (response) => {
                        this.$toast.success(this.translations.label.markup + ' ' + this.translations.messages.updated_successfully, {
                            position: 'top-right'
                        })
                        this.markup = response.data.markup

                        await this.executeUpdateRateHpPull(localStorage.getItem('client_id'));

                        if (this.quote_id != null) {
                            await this.updatePeople()
                        }



                        this.loading = false
                    })
                }

            },
            async confirmReservePrev() {
                console.log("FILE_ID: ", this.quote_open.file_id);
                console.log("FILE: ", this.file);
                console.log("HAS FILE: ", this.has_file);

                const response = await this.validateChilds()

                if (response) {
                    // this.confirmReserve()
                    this.getRqHotels()

                    if (this.verify_itinerary_errors()) {
                        this.$toast.warning(this.translations.label.observations_validation_text, {
                            position: 'top-right'
                        })
                        return
                    }

                    this.blockPage = true
                    this.loading = true
                    await axios.put(window.a3BaseQuoteServerURL + 'api/quote/me', {
                        quote_id: this.quote_id,
                        client_id: localStorage.getItem('client_id'),
                        category_id: this.categoryActive.id

                    })
                    await this.saveQuote(false);
                    this.blockPage = true
                    this.loading = true
                    await this.searchStatements()

                    this.$refs['modal_reserve'].show()
                    this.blockPage = false
                    this.loading = false

                }

            },
            getRqHotels: function() {
                //Add hotels rq
                try {
                    axios.get(window.baseExternalURL + 'api/quote/rq_hotels', {
                        params: {
                            quote_category_id: this.categoryActive.id,
                        }
                    }).then((result) => {
                        this.rq_hotels = result.data
                    });

                } catch (error) {
                    console.error("Error fetching rq_hotels:", error); // Manejo del error
                }
            },
            confirmReserve: function() {
                if (this.verify_itinerary_errors()) {
                    this.$toast.warning(this.translations.label.observations_validation_text, {
                        position: 'top-right'
                    })
                    return
                }
                // this.validateAvailableHotels()  // Esta linea se comento porque hacia validaciones de mas antes de reservar
                this.searchStatements()
                this.$refs['modal_reserve'].show()
            },
            willReserveQuote: function() {
                let validate_client = this.validateClientWithFile()
                let validate_has_services = this.validateHasServices()
                if (validate_client) {
                    this.$toast.warning(this.translations.label.selected_different_client, {
                        position: 'top-right'
                    })
                    return false
                }

                if (validate_has_services) {
                    this.$toast.warning(this.translations.label.no_added_services_reserve, {
                        position: 'top-right'
                    })
                    return false
                }

                let vm = this
                vm.$refs.modal_passengers.validatePassengersQuote()
            },
            reserveQuote: function() {
                // this.clicks_send_booking++
                // if (this.clicks_send_booking === 1) {
                this.loading_reserve = true
                let category_ids = []
                this.categories.forEach(_c => {
                    if (_c.checked) {
                        category_ids.push(_c.id)
                    }
                })

                let _date_quote = (this.quote_date_estimated != undefined && this.quote_date_estimated != '') ? this.quote_date_estimated : this.quote_date
                let data_quote = {
                    name: this.quote_name,
                    date: this.formatDate(this.quote_date, '/', '-', 1),
                    categories: category_ids,
                    service_type_id: this.quote_service_type_id,
                    people: this.quantity_persons,
                    notes: this.notes,
                    client_id: localStorage.getItem('client_id'),
                    operation: 'passengers',
                    date_estimated: this.formatDate(_date_quote, '/', '-', 1)
                }
                //Todo guardamos la cotizacion
                axios.put(window.a3BaseQuoteServerURL + 'api/quotes/' + this.quote_open.id, data_quote).then(response => {
                    if (response.data.success) {

                        if (localStorage.getItem('file_reference') != null && localStorage.getItem('file_reference') != '') {
                            this.file.file_reference = localStorage.getItem('file_reference')
                            localStorage.removeItem('file_reference')
                        }

                        this.$toast.success(this.translations.messages.saved_correctly, {
                            position: 'top-right'
                        })
                        let data = {
                            client_id: localStorage.getItem('client_id'),
                            lang: localStorage.getItem('lang'),
                            quote_id: this.quote_id,
                            quote_id_original: this.quote_open.id_original,
                            reference: this.file.file_reference,
                            file_code: this.file.file_code,
                            quote_category_id: this.categoryActive.id,
                            services_optionals: this.getServiceOptionals(this.services_optionals)
                        }
                        let reservation_type_class_id = null

                        this.quote_open.categories.forEach(_c => {
                            if (_c.tabActive === 'active') {
                                reservation_type_class_id = _c.type_class_id
                            }
                        })
                        this.quoteMeOnly(false)
                        //Todo Se crea trama para la reserva, donde validara los servicios y hoteles
                        axios.post(baseExternalURL + 'services/reservations/quote', data).then((result) => {
                            if (result.data.success) {
                                result.data.response.entity = 'Quote'
                                result.data.response.object_id = this.quote_open.id_original
                                result.data.response.type_class_id = reservation_type_class_id
                                result.data.response.reference = this.file.file_reference

                                //Todo Enviamos a reservar la cotizacion
                                axios.post(baseExternalURL + 'services/hotels/reservation/add', result.data.response).then((result) => {
                                    this.clicks_send_booking = 0
                                    if (result.data.success) {

                                        if (localStorage.getItem('parameters_ota_generic') != null) {

                                            let parameters_ota_generic = JSON.parse(localStorage.getItem('parameters_ota_generic'))
                                            // if (this.quote_id == parameters_ota_generic.quote_id){
                                            let data_reservation = {
                                                generic_service_id: parameters_ota_generic.generic_service_id,
                                                file_code: result.data.data.file_code
                                            }
                                            axios.post(baseExternalURL + 'api/generic_otas/reservation/package', data_reservation).then((result2) => {
                                                localStorage.removeItem('parameters_ota_generic')
                                            })
                                            // }
                                        }
                                        this.reservationId = result.data.data.id
                                        this.codeFile = result.data.data.file_code
                                        this.booking_code = result.data.data.booking_code
                                        this.hideModalReserve()
                                        this.openModalReservation()
                                        this.$toast.success(this.translations.reservation_made, {
                                            position: 'top-right'
                                        })
                                        this.verifyTieOrder()
                                        this.searchQuoteOpen('')
                                        this.searchDetailsFile(this.quote_open.id_original)
                                    } else {
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

                                    this.loading = false
                                    this.loading_reserve = false
                                }).catch((e) => {
                                    // this.$toast.error('Error: ' + e, {
                                    //     position: 'top-right'
                                    // })
                                    // this.loading = false
                                    // window.location.reload()

                                    let message = 'Ha ocurrido un error de sistema. Por favor, repórtelo al equipo de TI.';

                                    //NO hubo respuesta del backend (servidor caído, timeout, CORS, red)
                                    if (!e.response) {

                                        // Opcional: diferenciar causas técnicas
                                        if (e.code === 'ECONNABORTED') {
                                            message = 'Tiempo de espera agotado. El servidor no responde.';
                                        } else if (e.message === 'Network Error') {
                                            message = 'Error de red. No se pudo conectar con el servidor.';
                                        }

                                    }
                                    // Hubo respuesta del backend
                                    else if (e.response) {
                                        message =
                                            e.response.data?.message ||
                                            `Error del servidor (${e.response.status})`;
                                    } else if (e.request) {
                                        message = 'No se pudo conectar con el servidor.';
                                    }

                                    this.$toast.error(message, {
                                        position: 'top-right'
                                    });

                                    this.loading = false;
                                    this.loading_reserve = false
                                    // Log técnico (solo para desarrolladores)
                                    console.error('Error crítico Axios:', e);

                                })
                            } else {
                                if ((result.data.errors) && (result.data.errors.hotels.length > 0 || result.data.errors.services.length > 0)) {
                                    this.$refs['modal_reserve'].hide()
                                    this.openModalReservationErrors()
                                    this.errors_reservations = result.data.errors
                                } else {
                                    if (result.data.error) {
                                        this.$toast.error(result.data.error, {
                                            position: 'top-right'
                                        })
                                    } else {
                                        this.$toast.error('Error: Algo paso por favor volver a intenerlo', {
                                            position: 'top-right'
                                        })
                                    }

                                }
                                this.clicks_send_booking = 0
                                this.loading = false
                                this.loading_reserve = false
                            }
                        }).catch((e) => {
                            this.$toast.error('Error: ' + e, {
                                position: 'top-right'
                            })
                            this.clicks_send_booking = 0
                            this.loading = false
                            this.loading_reserve = false
                        })

                    } else {
                        this.clicks_send_booking = 0
                        this.loading_reserve = false
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right'
                        })
                    }
                    this.loading = false
                }).catch(error => {
                    this.$toast.error(this.translations.messages.internal_error, {
                        position: 'top-right'
                    })
                    this.clicks_send_booking = 0
                    this.loading_reserve = false
                    this.loading = false
                })
                // }

            },
            verifyTieOrder() {
                /*
                let nroplaTemp = this.quote_open.order_related
                if (nroplaTemp == '' || nroplaTemp == null || nroplaTemp == 0) {
                    this.mnjNoTie = this.translations.messages.not_automatically_linked + ': ' +
                        this.translations.messages.no_quotation_was_found
                    this.useOrdersShow = true

                    this.orderAuto = false
                    this.btnRelaciOrder = true
                    this.searchOrdersByClient()
                } else {

                    let data = {
                        nropla: nroplaTemp,
                        nroref: this.codeFile,
                        codcli: localStorage.getItem('client_code')
                    }
                    axios.post(baseExternalURL + 'api/quote/order/verifyTie', data).then((returns) => {
                        this.useOrdersShow = true
                        if (returns.data.data.nroped != '') {
                            this.orderAuto = true
                            this.numPedAuto = returns.nroped
                        } else {
                            if (returns.data.data.descri != '') {
                                this.mnjNoTie = this.translations.messages.not_automatically_linked + ': ' + returns.descri
                            }
                            this.orderAuto = false
                            this.btnRelaciOrder = true
                            this.searchOrdersByClient()
                        }
                    })
                }
                */
            },
            searchOrdersByClient() {

                this.ordersPend = []
                this.loaderOrders = true

                axios.get(baseURL + 'orders/client/' + localStorage.getItem('client_code'))
                    .then((returns) => {
                        this.ordersPend = returns.data.data
                        this.loaderOrders = false
                    }).catch(e => {
                        this.useOrdersShow = false
                        // console.log(e)
                    })
            },
            storeRelaciOrder() {

                if (this.ordersPend.length == 0 || (this.ordersPend[this.r_order] == undefined)) {
                    this.$toast.warning(this.translations.messages.no_order_selected, {
                        position: 'top-right'
                    })
                    return
                }

                this.loading = true

                let data = {
                    nroref: this.codeFile,
                    nroped: this.ordersPend[this.r_order].NROPED,
                    nroord: this.ordersPend[this.r_order].NROORD
                }
                axios.post(baseExternalURL + 'api/order/relate/reservation', $.param(data)).then((returns) => {
                    if (returns.data.success) {
                        this.$toast.success(this.translations.messages.related_correctly, {
                            position: 'top-right'
                        })
                        this.btnRelaciOrder = false
                    }
                    // console.log(returns)
                    this.loading = false
                })
            },
            extensionSelected(extension_id, type_class_id) {
                this.extension_selected = extension_id
                this.type_class_id = type_class_id
                // console.log(this.type_class_id)
                let radioCheck = extension_id + '_' + type_class_id
                for (var i = 0; i < this.type_class_ids.length; i++) {
                    if (this.type_class_ids[i] !== radioCheck) {
                        $('#' + this.type_class_ids[i]).prop('checked', false)
                    }
                }

            },
            openModalReservation: function() {
                this.searchStatements()
                this.$refs['modal_reservation'].show()
            },
            closeModalReservation: function() {
                this.$refs['modal_reservation'].hide()
            },

            modalPassengers: function(file, paxs, modal_) {
                if (this.has_file) {
                    localStorage.setItem('save_quote_file', file)
                    this.$refs.modal_passengers.modalPassengers('file', this.file.file_code, paxs, this.quantity_persons.adults, this.quantity_persons.child, 0, modal_)
                } else {
                    this.$refs.modal_passengers.modalPassengers('quote', file, paxs, this.quantity_persons.adults, this.quantity_persons.child, 0, modal_)
                }
            },
            openModalReservationErrors: function() {
                this.$refs['modal_reservation_errors'].show()
            },
            closeModalReservationErrors: function() {
                this.$refs['modal_reservation_errors'].hide()
            },
            debounce(method, timer) {
                if (this.$_debounceTimer !== null) {
                    clearTimeout(this.$_debounceTimer)
                }
                this.$_debounceTimer = setTimeout(() => {
                    method()
                }, timer)
            },
            changeLocked: function(hiddenLocked) {
                this.hiddenLocked = hiddenLocked
            },
            openModalClose: function() {
                this.$refs['modal_close'].show()
            },
            closeModalClose: function() {
                this.$refs['modal_close'].hide()
            },
            validateClientWithFile: function() {
                let validate = false
                if (this.has_file) {
                    if (this.file.client.id != localStorage.getItem('client_id')) {
                        validate = true
                    }
                }
                return validate
            },
            validateHasServices: function() {
                let have_service = 0
                this.quote_open.categories.forEach(c => {
                    if (c.tabActive === 'active') {
                        c.services.forEach(s => {
                            if (!s.locked) {
                                have_service++
                            }
                        })
                    }
                })
                return (have_service == 0) ? true : false
            },
            validateHasDynamicPrices: function() {
                let have_service = 0
                this.quote_open.categories.forEach(c => {
                    c.services.forEach(s => {
                        if (s.type == 'service') {
                            if (s.import && s.import.price_ADL === 0) {
                                have_service++;
                            }
                        }

                        if (s.type == 'hotel') {
                            if (s.import_amount && s.import_amount.price_ADL === 0) {
                                have_service++;
                            }
                        }
                    })

                })
                return (have_service > 0) ? true : false
            },
            openModalSaveAs: function() {
                this.$refs['modal_guardar_como'].show()
            },
            openModalPlanRooms: function() {
                this.$refs['modal-edit-plan-rooms'].show()
            },
            closeModalPlanRooms: function() {
                this.$refs['modal-edit-plan-rooms'].hide()
            },
            closeModalWillRemoveService: function() {
                this.$refs['modalWillRemoveService'].hide()
            },
            openModalPackageCreated: function() {
                this.$refs['modal_package_created'].show()
            },
            closeModalPackageCreated: function() {
                this.package_create = {
                    id: null
                }
                this.$refs['modal_package_created'].hide()
            },
            openModalNotesHotel: function(service) {
                // console.log(service)
                this.service_active = service
                this.$refs['modal_create_notes_hotel'].show()
            },
            closeModalNotesHotel: function() {
                this.$refs['modal_create_notes_hotel'].hide()
            },
            saveNoteHotel: function() {
                let data_quote = {
                    hotel: this.service_active
                }
                //Todo guardamos la cotizacion
                axios.put(window.a3BaseQuoteServerURL + 'api/quotes/update_notes/' + this.quote_open.id, data_quote)
                    .then(response => {
                        this.$toast.success(this.translations.messages.saved_correctly, {
                            position: 'top-right'
                        })
                        this.$refs['modal_create_notes_hotel'].hide()
                    })
                    .catch(response => {
                        // console.log(response)
                    })
            },
            searchDetailsFile: function(quote_id) {
                axios.post(baseExternalURL + 'api/files/quote', {
                    quote_id: quote_id
                }).then((result) => {
                    if (result.data.type == 'success') {

                        if (result.data.reservation != null) {
                            let lang = localStorage.getItem('lang').toLowerCase()

                            let languages = ['es', 'en', 'pt', 'it']
                            this.language_id = languages.indexOf(lang)

                            this.skeleton_file.services = result.data.reservation.reservations_service
                            this.skeleton_file.hotels = result.data.reservation.reservations_hotel
                            this.skeleton_file.flights = result.data.reservation.reservations_flight

                            // console.log(this.skeleton_file)
                            // console.log(this.language_id)
                        }
                    } else {
                        this.$toast.error(result.data.message, {
                            position: 'top-right'
                        })
                    }

                    this.loading = false
                    this.loading_reserve = false
                }).catch((e) => {
                    this.$toast.error('Error: ' + e, {
                        position: 'top-right'
                    })
                    this.loading = false
                    this.loading_reserve = false
                })
            },
            getServiceOptionals: function(service_optionals) {
                let service = []
                this.categoryActive.services.forEach(function(item_service, index) {
                    for (var [index_opt, value] of Object.entries(service_optionals)) {
                        if (index == index_opt && value == true) {
                            service.push({
                                object_id: item_service.object_id,
                                date_in: item_service.date_in,
                                date_out: item_service.date_out
                            })
                        }
                    }
                })
                return service
            },
            deleteRoomHotel: function(service_room_id) {
                this.loading = true
                axios.delete(window.a3BaseQuoteServerURL + 'api/quote/service/rooms/' + service_room_id + '/rate_plan_room')
                    .then(response => {
                        this.loading = false
                        if (response.data.success) {
                            this.searchQuoteOpen(this.categoryActive.id)
                            this.$toast.success(this.translations.messages.successfully_removed, {
                                position: 'top-right'
                            })
                        }
                    }).catch(error => {
                        this.loading = false
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right'
                        })
                        // console.log(error)
                    })
            },
            async searchStatementsGo() {
                let _quote = this.quote_open.id_original
                let _client = localStorage.getItem('client_id')

                await axios.post(window.a3BaseQuoteServerURL + 'api/quote/' + _quote + '/statements', {
                        type_class_id: this.categoryActive.type_class_id,
                        client_id: _client,
                    })
                    .then(result => {
                        if (result.data.type == 'success') {
                            Vue.set(this, 'statements', result.data)
                        }
                    })
                    .catch(error => {
                        // console.log(error)
                    })
            },
            async searchStatements() {

                if (this.user_type_id == 3) {
                    if (this.passengers.length == 0) {
                        this.passengers = this.$refs.modal_passengers.getPassengers()
                    }

                    if (this.passengers.length > 0) {
                        if (
                            this.passengers[0].first_name == '' || this.passengers[0].first_name == null ||
                            this.passengers[0].last_name == '' || this.passengers[0].last_name == null ||
                            (
                                this.passengers[0].first_name != null && (this.passengers[0].first_name.indexOf('PASAJERO') > -1 || this.passengers[0].first_name.indexOf('pasajero') > -1)
                            ) ||
                            (
                                this.passengers[0].last_name != null && (this.passengers[0].last_name.indexOf('PASAJERO') > -1 || this.passengers[0].last_name.indexOf('pasajero') > -1)
                            )
                        ) {
                            this.$toast.error(this.translations.messages.empty_passenger, {
                                position: 'top-right'
                            })
                            this.modalPassengers(this.quote_id, this.passengers.length)

                            let vm = this
                            setTimeout(() => {
                                vm.hideModalReserve()
                            }, 10)
                        } else {
                            localStorage.setItem('file_reference', this.passengers[0].first_name + ' ' + this.passengers[0].last_name)
                            await this.searchStatementsGo()
                        }
                    } else {
                        this.$toast.error(this.translations.messages.empty_passenger, {
                            position: 'top-right'
                        })
                        this.modalPassengers(this.quote_id, this.passengers.length)

                        let vm = this
                        setTimeout(() => {
                            vm.hideModalReserve()
                        }, 10)
                    }
                } else {
                    await this.searchStatementsGo()
                }
            },
            getServiceType: function() {
                let _service_type = '-'

                this.service_types.forEach((item, i) => {
                    if (item.id == this.quote_open.service_type_id) {
                        _service_type = item.translations[0].value
                    }
                })

                return _service_type
            },
            getDateIn: function() {
                window.moment.locale(localStorage.getItem('lang'))
                return window.moment(this.quote_open.date_in).format('LL')
            },
            getDateOut: function() {
                window.moment.locale(localStorage.getItem('lang'))
                return window.moment(this.quote_open.date_in).add(this.quote_open.nights, 'days').format('LL')
            },
            getFormatDate: function(_date) {
                if (_date != undefined) {
                    window.moment.locale(localStorage.getItem('lang'))
                    return window.moment(_date).format('LL')
                }

            },
            toggleEmailReminder: function() {
                if (!this.reminder.flag_email) {
                    this.reminder.email = ''
                }

                this.saveReminder()
            },
            changeDaysReminder: function(_type) {

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
            saveReminder: function() {
                let vm = this
                vm.email_error = false

                setTimeout(() => {
                    if (vm.reminder.flag_send) {

                        if (vm.reminder.email == '' || vm.reminder.email.match(/[^\s@]+@[^\s@]+\.[^\s@]+/gi)) {
                            axios.post('api/reservations/' + vm.reservationId + '/reminders', {
                                    days_before: vm.reminder.days,
                                    email: vm.statements.client.email,
                                    email_alt: vm.reminder.email,
                                    date: vm.statements.min_date_cancellation
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
                        axios.delete('api/reservations/' + vm.reservationId + '/reminders')
                            .then(result => {
                                console.log(result.data)
                            })
                            .catch(error => {
                                console.log(error)
                            })
                    }
                }, 10)
            },
            async refreshAssignsHotel() {
                if (this.categoryActive) {
                    if (this.categoryActive.services.length > 0) {
                        this.categoryActive.services.forEach((element, index) => {
                            // console.log(element.id, element.type, element?.group_quote_service_id);

                            // verifico si el cuarto es de tipo grupo, es decir que contenga hoteles
                            if (element.type == 'group_header') {
                                // obtengo los hoteles que estan dentro de este hotel cabecera excepto el hotel cabecera
                                hotel_finded = this.categoryActive.services.filter((x, y) => (x.group == element.group) && (y != index))

                                // itero los hoteles encontrados
                                hotel_finded.forEach(hotel => {
                                    // almaceno variables para enviar en la petición
                                    let grouped_code = hotel.group_code;
                                    let quote_service_id_choosed = hotel.id;
                                    let room_id = null;
                                    let verifyValidation = false;

                                    // verifico que contenga errores
                                    if (hotel.validations.length > 0) {
                                        // verifico que contenga el campo verify
                                        hotel.validations.forEach(validation => {
                                            if (validation?.verify) {
                                                verifyValidation = true;
                                            }
                                        });
                                    }

                                    // si contiene verify es un error de tipo rate desactivado
                                    if (verifyValidation) {
                                        // obtengo el id del cuarto
                                        room_id = hotel.service_rooms[0].rate_plan_room.room_id

                                        let data = {
                                            'hotels_id': [hotel.object_id],
                                            'date_from': this.formatDate(hotel.date_in, '/', '-', 1),
                                            'date_to': this.formatDate(hotel.date_out, '/', '-', 1),
                                            'client_id': localStorage.getItem('client_id'),
                                            'quantity_rooms': (hotel.single + hotel.double + hotel.triple),
                                            'quantity_persons_rooms': [],
                                            'typeclass_id': hotel.hotel.typeclass_id,
                                            'destiny': {
                                                'code': hotel.hotel.country.iso + ',' +
                                                    hotel.hotel.state.iso,
                                                'label': hotel.hotel.country.translations[0].value + ',' +
                                                    hotel.hotel.state.translations[0].value
                                            },
                                            'lang': localStorage.getItem('lang'),
                                            'set_markup': (this.markup != '' || this.markup != null) ? this.markup : 0,
                                            'zero_rates': true
                                        }

                                        this.loading = true
                                        axios.post('services/hotels/available/quote', data)
                                            .then((result) => {
                                                let rooms = []
                                                // console.log(result.data.data[0].city.hotels[0].rooms);
                                                // Verifico que tenga hoteles
                                                if (result.data.data[0].city.hotels.length > 0) {
                                                    rooms = result.data.data[0].city.hotels[0].rooms
                                                }
                                                let rate_plan_rooms_choose = []
                                                let response = null;

                                                // Si tiene hoteles que itere para realizar el reemplazo
                                                if (rooms.length > 0) {
                                                    rooms.forEach(room => {
                                                        // verifico que el cuarto del listado sea igual al cuarto que debemos cambiar
                                                        if (room.room_id == room_id) {
                                                            // itero los rates para buscar cual de ellos es HYPERGUEST
                                                            room.rates.forEach(rate => {
                                                                // Si el rate seleccionado es "HYPERGUEST"
                                                                if (rate.rateProvider == "HYPERGUEST") {
                                                                    rate_plan_rooms_choose.push({
                                                                        rate_plan_room_id: rate.rateId,
                                                                        choose: true,
                                                                        occupation: room.occupation,
                                                                        on_request: rate.onRequest
                                                                    })

                                                                    response = {
                                                                        quote_id: this.quote_open.id,
                                                                        quote_service_id: quote_service_id_choosed,
                                                                        rate_plan_room_ids: [],
                                                                        lang: localStorage.getItem('lang'),
                                                                        rate_plan_rooms_choose: rate_plan_rooms_choose,
                                                                        client_id: localStorage.getItem('client_id')
                                                                    }
                                                                }
                                                            });
                                                        }
                                                    });
                                                }

                                                setTimeout(() => {
                                                    if (rate_plan_rooms_choose.length > 0) {
                                                        axios.post(window.a3BaseQuoteServerURL + 'api/quote/service/' + quote_service_id_choosed + '/rooms/replace', response)
                                                            .then(async (result) => {
                                                                await this.executeUpdateRateHpPull(localStorage.getItem('client_id'), [quote_service_id_choosed]);
                                                                this.searchQuoteOpen(this.categoryActive.id, '', grouped_code)
                                                            }).catch((e) => {
                                                                console.log(e)
                                                            }).finally(() => {
                                                                this.loading = true
                                                            })
                                                    } else {
                                                        this.loading = false
                                                    }
                                                }, 150);
                                            })
                                    }
                                });
                            }
                        });
                    }
                }
            },
            goto_file() {
                document.location.href = window.a3BaseUrl + 'files';
            },
            setAgeChild(child) {
                const childAges = [];
                if (child > 0) {
                    for (let i = 0; i < child; i++) {
                        childAges.push({
                            'age': 1,
                            'id': null,
                            'quote_id': null
                        })
                    }
                }
                return childAges;
            },
            validate_ocupation(generatedDistribution) {
                let validate = true;
                generatedDistribution.forEach(element => {
                    if ((parseInt(element.occupation) < parseInt(element.passengers.length)) || (parseInt(element.occupation) > parseInt(element.passengers.length))) {
                        validate = false
                    }
                });

                if (validate == false) {
                    return false;
                }
                return true;
            },
            passengerNews(passengerOptins) {
                let passengerNews = []
                passengerOptins.forEach((value, key) => {
                    if (this.isNumber(value.id) === false) {
                        value.id = null
                    }
                    passengerNews.push(value);
                });

                if (this.quote_open.passengers.length > passengerNews.length) {
                    passengerNews = this.quote_open.passengers
                }
                return passengerNews;
            },
            isNumber(value) {
                return typeof value === "number" && !Number.isNaN(value);
            },
            async updatePeopleClone(form) {
                return await axios.put(window.a3BaseQuoteServerURL + 'api/quote/people', form);
            },
            async setAccommodation(single, double, triple, adults, child) {

                const {
                    data
                } = await axios.get(window.a3BaseQuoteServerURL + 'api/quote/service/occupation_paseengers_hotel_client', {
                    params: {
                        single: single,
                        double: double,
                        triple: triple,
                        adults: adults,
                        child: child,
                        quote_id: this.quote_open.id,
                    }
                });

                return data;
            },

            async getQuoteAccommodation(single, double, triple, adults, child) {

                const {
                    data
                } = await axios.get(window.a3BaseQuoteServerURL + 'api/quote/service/occupation_paseengers_hotel', {
                    params: {
                        single: single,
                        double: double,
                        triple: triple,
                        adults: adults,
                        child: child,
                        quote_id: this.quote_open.id,
                    }
                })

                return data.quoteDistributions

            },
            async updateOccupationDistribution(form) {
                await axios.post(window.a3BaseQuoteServerURL + 'api/quote/service/occupation_paseengers_hotel', form)
            },
            async updateServiceHotelsRoomTypeQuantity(occupationHotel) {
                const {
                    data
                } = await axios.put(window.a3BaseQuoteServerURL + 'api/quote/service/occupation_hotel/general', occupationHotel)
                return data
            },
            async addQuoteServiceHotelAddRooms(quote_service_id, request) {
                const {
                    data
                } = await axios.post(window.a3BaseQuoteServerURL + `api/quote/service/${quote_service_id}/rooms/addFromHeader`, request)
                return data
            },
            async storeServiceHotel(quote_id, result, lang) {
                const responsePromise = [];
                for (const {
                        rooms_add,
                        availability
                    }
                    of result) {

                    const roomsAvailable = availability.length > 0 ? availability[0].rooms : [];
                    for (const room_new of rooms_add) {

                        let shouldBreak = false;
                        for (const room of roomsAvailable) {
                            if ([1, 2, 3].includes(room.room_type_id) && room_new.occupation == room.occupation) {
                                for (const rate of room.rates) {
                                    if (rate.rates_plans_type_id == 2) {
                                        responsePromise.push(new Promise((resolve) => {
                                            this.addQuoteServiceHotelAddRooms(
                                                room_new.quote_service_id, {
                                                    quote_id: quote_id,
                                                    quote_service_id: room_new.quote_service_id,
                                                    rate_plan_room_ids: [],
                                                    lang: lang,
                                                    rate_plan_rooms_choose: [{
                                                        rate_plan_room_id: rate.rateId,
                                                        rate_plan_id: rate.ratePlanId,
                                                        room_id: room_new.room_id,

                                                        choose: true,
                                                        occupation: room_new.occupation,
                                                        on_request: 1,
                                                    }, ],
                                                    cant: room_new.cant,
                                                    quote_service: room_new.quote_service ? room_new.quote_service : ''
                                                }).then((response) => {
                                                resolve({
                                                    rooms_add: 1,
                                                    availability: response.data
                                                })
                                            });
                                        }))
                                        shouldBreak = true
                                        break;
                                    }
                                }
                            }

                            if (shouldBreak) {
                                break;
                            }
                        }
                    }
                }

                if (responsePromise.length > 0) {
                    await Promise.all(responsePromise);
                }

            },
            async updateQuoteAccommodation(distributionPassengers, single, double, triple) { // , updatePeopleHow = 1

                await this.updateOccupationDistribution({
                    distribution_passengers: distributionPassengers,
                    quote_id: this.quote_open.id
                })

                const response = await this.updateServiceHotelsRoomTypeQuantity({
                    simple: single,
                    double: double,
                    triple: triple,
                    double_child: 0,
                    triple_child: 0,
                    quote_id: this.quote_open.id
                });

                const promisesAvailability = this.fetchHotelAvailability(response.hotels_add_rooms, localStorage.getItem('lang'));

                if (promisesAvailability.length > 0) {
                    const promisesResult = await Promise.all(promisesAvailability);

                    const promisesServiceHotel = await this.storeServiceHotel(this.quote_open.id, promisesResult, localStorage.getItem('lang'));


                }

                // if (this.quote_open.operation == 'passengers' && updatePeopleHow == 1) {
                //     await this.updatePeople({
                //         quote_id: this.quote_open.id,
                //         passengers: this.quote_open.passengers,
                //         people: {
                //             adults: this.quote_open.people[0].adults,
                //             child:this.quote_open.people[0].child,
                //             ages_child: this.quote_open.age_child
                //         },
                //     });
                // }

            },
            async getHotelsAvailability(request) {
                const {
                    data
                } = await axios.post('services/hotels/available/quote', request)
                return data

            },
            // localStorage.getItem('client_id')
            fetchHotelAvailability(hotelsAddRooms, lang) {
                const promises = [];

                for (const entry of Object.entries(hotelsAddRooms)) {
                    const roomsAdd = entry[1];
                    const hotel = entry[1][0];
                    promises.push(new Promise((resolve) => {
                        this.getHotelsAvailability({ // buscamos los hoteles disponibles para poder agregar la habitacion
                            hotels_id: [
                                hotel.hotel_id
                            ],
                            client_id: localStorage.getItem('client_id'),
                            date_from: hotel.date_in,
                            date_to: hotel.date_out,
                            quantity_rooms: 1,
                            quantity_persons_rooms: [],
                            typeclass_id: hotel.typeclass_id,
                            destiny: {
                                code: hotel.destiny_code,
                                label: hotel.destiny_label
                            },
                            lang: lang,
                            set_markup: 0,
                            zero_rates: true
                        }).then((response) => {
                            resolve({
                                rooms_add: roomsAdd,
                                availability: response.data[0].city.hotels
                            })
                        })
                    }))
                }

                return promises
            },
            async validate_clone_file() {

                const clone_file_id = this.quote_open.clone_file_id
                const clone_executed = this.quote_open.clone_executed
                if (!clone_file_id) {
                    return false;
                }

                if (clone_executed) {
                    return false;
                }

                setTimeout(() => {
                    this.loading = true
                    this.blockPage = true
                }, 100)


                const clone_parameters = this.quote_open.clone_parameters
                const single = clone_parameters.accommodation_sgl
                const double = clone_parameters.accommodation_dbl
                const triple = clone_parameters.accommodation_tpl
                const adults = clone_parameters.adults
                const child = parseInt(clone_parameters.children)
                const childAges = this.setAgeChild(child)

                if (this.quote_open.accommodation.single != single || this.quote_open.accommodation.double != double || this.quote_open.accommodation.triple != triple || this.quote_open.people[0].adults != adults || this.quote_open.people[0].child != child) {
                    const result = await this.setAccommodation(single, double, triple, adults, child)
                    const generatedDistribution = result.quoteDistributions
                    const passengerOptins = result.passengers

                    if (this.validate_ocupation(generatedDistribution) == false) {
                        return false;
                    }
                    const passengerNews = this.passengerNews(passengerOptins)

                    await this.updatePassengersAccommodations(passengerNews, adults, child, childAges, single, double, triple)

                    await this.searchQuoteOpen('')

                    setTimeout(() => {
                        this.loading = true
                        this.blockPage = true
                    }, 100)
                }

                if (this.quote_open.date_in != clone_parameters.date_init) {
                    await this.updateDate(clone_parameters.date_init)
                }

                await this.updateClient(clone_parameters.client_id)

                setTimeout(() => {
                    this.loading = false
                    this.blockPage = false
                }, 100)

            },
            async updateDate(date_init) {

                await axios.put(window.a3BaseQuoteServerURL + 'api/quote/update/date_in', {
                    lang: localStorage.getItem('lang'),
                    quote_id: this.quote_id,
                    date_in: date_init
                })

                // await this.searchQuoteOpen('')
                setTimeout(() => {
                    this.loading = true
                    this.blockPage = true
                }, 100)

            },
            async updateClient(client_id) {


                setTimeout(() => {
                    this.loading = true
                    this.blockPage = true
                }, 100)

                const client_id_actual = localStorage.getItem('client_id');
                const result_client = await axios.get(`api/markup/byClient/${client_id}`)
                this.markup = parseFloat(result_client.data.data.hotel)


                localStorage.setItem('client_id', result_client.data.data.client.id)
                localStorage.setItem('client_code', result_client.data.data.client.code)
                localStorage.setItem('client_name', result_client.data.data.client.name)
                localStorage.setItem('client_allow_direct_passenger_create', result_client.data.data.client.allow_direct_passenger_creation)

                await axios.put(window.a3BaseQuoteServerURL + 'api/update/quote/markup', {
                    quote_id: this.quote_id,
                    markup: this.markup,
                    client_id: localStorage.getItem('client_id'),
                    option: 2,
                    user_id: localStorage.getItem('user_id'),
                    user_type_id: localStorage.getItem('user_type_id')

                })
                await this.saveFinCloneFile()

                if (client_id != client_id_actual) {
                    window.location.reload()
                } else {
                    await this.searchQuoteOpen('')
                }


            },
            async saveFinCloneFile(lang_id) {
                await axios.put(window.a3BaseQuoteServerURL + `api/quote/finish-clone-file/${this.quote_id}`, {
                    language_id: lang_id
                })
            },
            async updatePassengersAccommodations(passengers, adults = 2, child = 0, ages_child, simple = 0, double = 1, triple = 0) {

                const passengerUpdates = await this.updatePeopleClone({
                    quote_id: this.quote_open.id,
                    passengers: passengers,
                    people: {
                        adults: adults,
                        child: child,
                        ages_child: ages_child,
                    },
                })

                const generatedDistribution = await this.getQuoteAccommodation(simple, double, triple, adults, child)
                await this.updateQuoteAccommodation(generatedDistribution, simple, double, triple); //, 0

                await this.updatePeopleClone({
                    quote_id: this.quote_open.id,
                    passengers: passengerUpdates.data,
                    people: {
                        adults: adults,
                        child: child,
                        ages_child: ages_child,
                    },
                })
            },
            async executeUpdateRateHpPull(client_id, quote_service_ids = [], new_quote_id) {

                let quote_id = new_quote_id ? new_quote_id : this.quote_id;

                let resulRateHpPull = await this.getRateHpPull(quote_id, client_id, quote_service_ids);
                if (resulRateHpPull.length > 0) {
                    const promisesAvailability = this.fetchHotelAvailability2(resulRateHpPull);

                    if (promisesAvailability.length > 0) {
                        const promisesResult = await Promise.all(promisesAvailability);

                        const promisesServiceHotel = await this.updateRateHyperguestPull(quote_id, promisesResult);

                    }
                }
            },
            async getRateHpPull(quote_id, client_id, quote_service_ids = []) {
                let urlquoteA3 = window.a3BaseQuoteServerURL

                const response = await axios.get(urlquoteA3 + 'api/quote/' + quote_id + '/get-rate-hyperguest-pull', {
                    params: {
                        client_id: client_id,
                        quote_service_ids: quote_service_ids
                    }
                });

                return response.data.result ?? [];
            },
            fetchHotelAvailability2(params) {
                const promises = [];

                for (let i = 0; i < params.length; i++) {

                    let results = params[i];
                    promises.push(new Promise((resolve) => {
                        this.getHotelsAvailability(results.params).then((response) => {
                            resolve({
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
            async updateRateHyperguestPull(quote_id, result) {
                const response = [];
                for (const {
                        quote_service_id,
                        passengers_quantity,
                        service_rate_plan_room,
                        availability
                    }
                    of result) {

                    const roomsAvailable = availability.length > 0 ? availability[0].rooms : [];

                    let shouldBreak = false;
                    for (const room of roomsAvailable) {
                        // console.log(quote_service_id + ' - ' + + service_rate_plan_room.room_id + ' == ' +  room.room_id  )
                        if (service_rate_plan_room.room_id == room.room_id) {

                            for (const rate of room.rates) {
                                // console.log(quote_service_id + ' - ' + service_rate_plan_room.room_id + ' == ' +  room.room_id + ' ' +  service_rate_plan_room.rate_plan_id + ' == ' + rate.ratePlanId)
                                if (service_rate_plan_room.rate_plan_id == rate.ratePlanId) {

                                    let amount_days = rate.rate[0].amount_days;

                                    if (amount_days.length > 0) {
                                        for (const amount of amount_days) {
                                            const base = Number(amount.total_amount_base ?? 0);
                                            const total = Number(amount.total_amount ?? 0);
                                            const pax = Number(passengers_quantity ?? 0);

                                            response.push({
                                                quote_service_delete: 0,
                                                quote_service_id: quote_service_id,
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
                                            shouldBreak = true
                                        }
                                    }

                                }
                            }
                        }

                    }

                    if (shouldBreak == false) {
                        response.push({
                            quote_service_delete: 1,
                            quote_service_id: quote_service_id
                        });
                    }

                }

                let urlquoteA3 = window.a3BaseQuoteServerURL
                let results = await axios.post(urlquoteA3 + 'api/quote/' + quote_id + '/update-rate-hyperguest-pull', {
                    data: response
                })

            },
            async iniciarDOM() {
                this.$root.$emit('loadingPage', {
                    typeBack: 2
                })
                // let me = this
                // this.timeoutSaveQuote = setInterval(function(){ me.saveQuote() }, 300000);
                this.setTranslations()

                this.$root.$on('changeMarkup', (payload) => {
                    this.putMarkup()
                })

                this.loading = true
                this.blockPage = true

                await Promise.all([
                    this.getCategories(),
                    this.getServiceCategories(),
                    this.getServicesTypes()
                ]);

                let a3_client_code = Cookies.get('a3_client_code');
                if (a3_client_code != null && a3_client_code != '') {
                    Cookies.remove('a3_client_code');
                    localStorage.setItem('client_code', a3_client_code);
                }

                let a3_client_id = Cookies.get('a3_client_id');
                if (a3_client_id != null && a3_client_id != '') {
                    Cookies.remove('a3_client_id');
                    localStorage.setItem('client_id', a3_client_id);
                }

                await this.searchQuoteOpen('')
                await this.validate_clone_file()

                if (localStorage.getItem('request_pakage') != null && localStorage.getItem('request_pakage') == 1) {
                    await this.validateAvailableHotels()
                    localStorage.setItem('request_pakage', 0)
                    this.searchQuoteOpen('')
                }

                // Permissions
                axios.get(baseURL + 'quotes/permissions')
                    .then(response => {
                        this.permissions = response.data
                        if (!(this.permissions.adddiscount)) {
                            this.use_discount = false
                            this.discount = 0
                        }

                    }).catch(error => {
                        // console.log(error)
                    })

                // Destinations Hotel
                axios.get('services/hotels/quotes/destinations?lang=' + localStorage.getItem('lang'))
                    .then(response => {

                        this.unzip_destination_hotels(response.data)

                    }).catch(error => {
                        // console.log(error)
                    })

                // Origin Services
                axios.get('api/services/ubigeo/selectbox/originFormat/' + localStorage.getItem('lang'))
                    .then(response => {

                        let _originModalService_select = []
                        response.data.data.forEach(u => {
                            _originModalService_select.push({
                                code: u.id,
                                label: u.description
                            })
                        })
                        this.unzip_origin_services(_originModalService_select)

                    }).catch(error => {
                        // console.log(error)
                    })
                // Destinations Services
                axios.get('api/services/ubigeo/selectbox/destination/' + localStorage.getItem('lang'))
                    .then(response => {

                        let _destinationsModalService_select = []
                        response.data.data.forEach(u => {
                            _destinationsModalService_select.push({
                                code: u.id,
                                label: u.description
                            })
                        })
                        this.unzip_destiny_services(_destinationsModalService_select)

                    }).catch(error => {
                        // console.log(error)
                    })

                // setTimeout(() => {
                //     this.refreshAssignsHotel();
                // }, 100);

                setTimeout(() => {
                    window.addEventListener('storage', this.handleStorageChange)
                    this.validate_client_file()
                }, 1000);

            },
            async putQuote(quote_id, client_id) {
                axios.post(window.a3BaseQuoteServerURL + 'api/quote/' + quote_id + '/copy/quote', {
                    status: 2,
                    client_id: client_id,
                }).then(async (result) => {
                    if (result.data.success) {
                        this.$toast.success(this.translations.messages.quote_in_edit_mode, {
                            // override the global option
                            position: 'top-right',
                        });
                        window.location.reload();
                    } else {
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right',
                        });
                    }
                }).catch((e) => {
                    this.$toast.error('Error: ' + e, {
                        position: 'top-right',
                    });
                    this.loading = false;
                });
            },
            async generatePassengerInit() {
                let urlquoteA3 = window.a3BaseQuoteServerURL
                await axios.post(urlquoteA3 + 'api/quote/' + this.quote_id + '/clear-rate-hyperguest-pull', {})
                await this.generatePassenger(true);

            }
        },
        watch: {
            operation: function(value) {
                this.initializeModalServiceNumberOfGuests();
            },
            quantity_persons: {
                handler: function() {
                    this.initializeModalServiceNumberOfGuests();
                },
                deep: true,
            },
            ranges: {
                handler: function() {
                    this.initializeModalServiceNumberOfGuests();
                },
                deep: true,
            },
            showDropdown(newVal) {
                if (newVal) {
                    // Agregar listener cuando se muestra el dropdown
                    this.$nextTick(() => {
                        document.addEventListener('click', this.closeDropdownOnOutsideClick);
                    });
                } else {
                    // Remover listener cuando se oculta el dropdown
                    document.removeEventListener('click', this.closeDropdownOnOutsideClick);
                }
            },
            new_order_related(newVal, oldVal) {
                const quoteId = this.quote_open.id;
            }
        },
        filters: {
            format_hour: function(_hour) {
                if (_hour == undefined || _hour == null) {
                    // console.log('fecha no parseada: ' + _date)
                    return
                }
                _hour = _hour.substr(0, 5)
                return _hour
            },
            formatDate: function(_date) {
                if (_date == undefined) {
                    // console.log('fecha no parseada: ' + _date)
                    return
                }
                _date = _date.split('-')
                _date = _date[2] + '/' + _date[1] + '/' + _date[0]
                return _date
            },
            formattedDateNotes: function(date) {
                return moment(date).format('MMM D, YYYY HH:mm')
            },

            formatPrice: function(price) {
                return parseFloat(price).toFixed(2)
            },
            roundLito: function(num) {
                num = parseFloat(num)
                num = (num).toFixed(2)

                if (num != null) {
                    var res = String(num).split('.')
                    var nEntero = parseInt(res[0])
                    var nDecimal = 0
                    if (res.length > 1)
                        nDecimal = parseInt(res[1])

                    var newDecimal
                    if (nDecimal <= 10) {
                        newDecimal = 0
                    } else if (nDecimal > 10 && nDecimal <= 50) {
                        newDecimal = 5
                    } else {
                        nEntero = nEntero + 1
                        newDecimal = 0
                    }

                    return parseFloat(String(nEntero) + '.' + String(newDecimal))
                }

            },
        }


    })
</script>

@endsection
