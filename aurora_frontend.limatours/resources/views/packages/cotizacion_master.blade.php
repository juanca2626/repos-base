@extends('layouts.app')
@section('content')

    <section class="page-cotizacion">
        <loading-component v-show="loading"></loading-component>
        <div class="container-fluid" style="padding: 0 10rem;">
            <div class="row ml-0">
                <div id="_overlay"></div>
                <div class="col-6 titulo">
                    <h2 v-if="quote_open.id_original==''">{{ trans('quote.label.new_quote') }}</h2>
                    <h2 v-if="quote_open.id_original!=''">{{ trans('quote.label.draft_of_the_quotation') }} N° @{{
                        quote_open.id_original }}</h2>
                    <h5 v-if="quote_open!=''"><i class="fa fa-eraser"></i> N° @{{ quote_id }}</h5>
                </div>
                <div class="col-6 titulo" v-if="has_file">
                    <h2 class="file_quote">File #: @{{ file.file_code }}</h2>
                </div>

                <div class="col-12 cotizacion-crear">
                    <div class="form">
                        <div class="form-row">
                            <div class="col-md-4">
                                <div class="form-group"><i class="icon icon-folder"></i>
                                    <input type="text" class="form-control" v-model="quote_name"
                                           placeholder="" @keyup.enter="updateNameQuote">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group"><i class="icon icon-calendar"></i>
                                    <date-picker class="form-control" v-model="quote_date" :config="optionsR"
                                                 @dp-show="showDatePickerQuote"
                                                 @dp-change="updateDateInQuote"
                                                 :key="updateDatePickerQuote"></date-picker>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <select type="text" class="form-control select-service" name="quote_service_type_id"
                                            v-model="quote_service_type_id">
                                        <option value="" disabled>{{ trans('quote.label.type_services') }}</option>
                                        <option :value="service_type.id" v-for="service_type in service_types">
                                            @{{ service_type.translations[0].value }} - @{{ service_type.code }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group cotizacion-crear--pasajeros" style="width: 252px">
                                <i class="icon icon-user"></i>
                                <button id="dropdownPax" data-toggle="dropdown" aria-haspopup="true"
                                        aria-expanded="false" class="form-control">
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
                                                    @change="generatePassenger(true)">
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
                                            </select>
                                        </div>
                                        <div class="form-group" v-if="quantity_persons.adults > 0">
                                            <label>{{ trans('quote.label.child') }}</label>
                                            <select class="form-control" v-model="quantity_persons.child"
                                                    @change="generatePassenger(true)">
                                                <option value="0">0</option>
                                                <option value="1">1</option>
                                                <option value="2">2</option>
                                                <option value="3">3</option>
                                                <option value="4">4</option>
                                                <option value="5">5</option>
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
                        <div class="form-row d-flex justify-content-between align-items-center">
                            <div class="form-group mx-4 mt-4" style="left: -60px;">
                                <div class="col-12 cotizacion-editar" style="margin-bottom: 0px;">
                                    <div class="btn-group align-items-center">
                                        <div class="btn btn-link cotizacion-editar--lista"
                                             v-if="quantity_persons.adults > 0 && !has_file">
                                            <a href="#" v-on:click="modalPassengers(quote_id, passengers.length)"
                                               class="link a-filtros">
                                                <i class="icon icon-user-check"></i><span
                                                    class="text">{{ trans('quote.label.passengers_list') }}</span>
                                                <small>(@{{
                                                    passengers.length }})
                                                </small>
                                            </a>
                                        </div>
                                        <div class="btn btn-link cotizacion-editar--categorias">
                                            <a href="#" id="dropdownCategoria" data-toggle="dropdown"
                                               aria-haspopup="true"
                                               aria-expanded="false" class="link a-filtros">
                                                <i class="icon icon-tag"></i><span
                                                    class="text">{{ trans('quote.label.categories') }}</span>
                                                <small>(@{{ categories_selected.length }}/@{{ categories.length
                                                    }})
                                                </small>
                                            </a>
                                            <div aria-labelledby="dropdownCategoria" class="dropdown dropdown-menu"
                                                 style="z-index: 100"
                                                 x-placement="bottom-start">
                                                <div class="container-dropdown px-4" style="overflow-y: scroll;">
                                                    <div class="form-group row">
                                                        <div style="height: 200px">
                                                            <label>{{ trans('quote.label.select_categories') }}</label>
                                                            {{--                                                            <label class="form-check col-12">--}}
                                                            {{--                                                                <input style="margin-top: 14px;margin-right: 5px;"--}}
                                                            {{--                                                                       class="form-check-input"--}}
                                                            {{--                                                                       type="checkbox"--}}
                                                            {{--                                                                       @change="toggleAllCategories()"--}}
                                                            {{--                                                                       v-model="checkedAllCategories">--}}
                                                            {{--                                                                {{ trans('quote.label.all_categories') }}--}}
                                                            {{--                                                            </label>--}}
                                                            <label class="form-check col-6"
                                                                   v-for="category in categories">
                                                                <input style="margin-top: 14px; margin-right: 5px;"
                                                                       class="form-check-input"
                                                                       type="checkbox"
                                                                       v-model="category.checked"
                                                                       @change="createOrDeleteCategory(category)">
                                                                @{{ category.translations[0].value }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="btn btn-link cotizacion-editar--rangos"
                                             v-if="quantity_persons.adults == 0" id="dropdown_rango">
                                            <a href="#" id="dropdownRango" data-toggle="dropdown" aria-haspopup="true"
                                               aria-expanded="false" class="link a-filtros">
                                                <i class="icon icon-maximize-2"></i><span
                                                    class="text">{{ trans('quote.label.ranges') }}</span>
                                                <small>(@{{ ranges.length }})
                                                </small>
                                            </a>
                                            <div aria-labelledby="dropdownRango" class="dropdown dropdown-menu"
                                                 style="z-index: 100"
                                                 x-placement="bottom-start">
                                                <div class="container-dropdown">
                                                    <div class="form-group"
                                                         style="max-height: 300px; overflow-y: scroll; width: 100%;">
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
                                                                           @keyup.enter="updateRange(range)"/>
                                                                </td>
                                                                <td nowrap class="td">
                                                                    <input type="text" class="form-control end"
                                                                           v-model="range.to"
                                                                           @keyup.enter="updateRange(range)"/>
                                                                </td>
                                                                <td nowrap class="td icon-rank text-center">
                                                                    <a class="" title="">
                                                                        <i class="icon-plus-square"
                                                                           @click.stop="createRange"></i>
                                                                    </a>
                                                                    <a class="" title="" v-if="index_range > 0">
                                                                        <i class="far fa-minus-square"
                                                                           @click.stop="deleteRange(index_range)"></i>
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                            </tbody>
                                                        </table>

                                                        <button type="button" :disabled="loading"
                                                                class="btn btn-success btn-update-all-ranges"
                                                                @click="update_all_ranges()">
                                                            <i class="fa fa-save"></i> {{ trans('quote.label.save') }}
                                                        </button>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="btn btn-link cotizacion-editar--notas ml-5">
                                            <a href="#" class="link a-filtros" data-toggle="modal"
                                               data-target="#modal-notas">
                                                <i class="icon icon-message-square"></i>
                                                <span class="text">
                                                    {{ trans('quote.label.notes') }}
                                                </span>
                                                <small>(@{{ notes.length }})
                                                </small>
                                            </a>
                                        </div>
                                        <div class="btn btn-link cotizacion-editar--notas ml-5" v-if="quote_id != null">

                                            <a href="#" class="link a-filtros" @click="show_occupation_modal()">
                                                <i class="icon icon-bed-simple"></i>
                                                <span class="text">{{ trans('quote.label.occupation') }}:</span>
                                                <strong style="display: contents; color:initial;"
                                                        :class="['span-accomodation', {'text-danger':!( (quantity_persons.adults + quantity_persons.child) === ( parseInt(service_selected_general.single) + (parseInt(service_selected_general.double)*2)+(parseInt(service_selected_general.triple) * 3)) )}]">
                                                    SGL:@{{ service_selected_general.single }} - DBL:@{{
                                                    service_selected_general.double }} - TPL:@{{
                                                    service_selected_general.triple }}
                                                </strong><br>
                                                {{--                                                <strong>--}}
                                                {{--                                                    DBL(CHD): @{{--}}
                                                {{--                                                    service_selected_general.double_child }} - TPL(CHD): @{{--}}
                                                {{--                                                    service_selected_general.triple_child }}--}}
                                                {{--                                                </strong>--}}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center my-5"
                                 v-if="quote_id!=null && permissions.updatemarkup" style="">
                                <div class="form-group">
                                    <label class="markup mr-3">{{ trans('quote.label.markup') }}</label>
                                    <div class="input-group mb-3">
                                        <input class="form-control" type="number" min="0" max="100" step="0.01"
                                               v-model="markup"
                                               @keyup.enter="updateMarkup(2)" style="padding-left: 10px;">
                                        <div class="input-group-prepend">
                                            <button class="btn btn-lg btn-danger mr-1" @click="updateMarkup(2)"
                                                    title="{{ trans('quote.label.save_markup') }}"
                                                    style="z-index: 0;">
                                                <i class="far fa-save"></i>
                                            </button>

                                            <button class="btn btn-lg btn-danger" @click="updateMarkup(3)"
                                                    title="{{ trans('quote.label.restore_markup') }}"
                                                    style="z-index: 0;">
                                                <i class="fas fa-sync"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group d-flex justify-content-start align-items-center my-5 ml-2"
                                 v-if="quote_id!=null" style="">

                                <label
                                    class="markup mr-4">{{ trans('quote.label.link_the_reservation_with_an_order') }}</label>
                                <input class="form-control" type="number" min="1" max="1000000" step="1"
                                       v-model="new_order_related" v-bind:disabled="readonly"
                                       style="padding-left: 10px; width: 120px;">
                            </div>
                            <div class="form-group d-flex justify-content-start align-items-center my-5 ml-2" style="">
                                <label class="markup mr-4">{{ trans('quote.label.estimated_travel_date') }}</label>
                                <div class="form-group"><i class="icon icon-calendar"></i>
                                    <date-picker class="form-control" v-model="quote_date_estimated" :config="optionsR"
                                                 style="width: 150px;"></date-picker>
                                </div>
                            </div>
                            <div class="form-group mx-4 mt-4">
                                <button class="btn btn-crear btn-primary" @click="saveQuote()" :disabled="loading"
                                        v-if="quote_id == null">
                                    <span v-if="!(loading)">
                                        <strong v-if="quote_open==''">{{ trans('quote.label.create') }}</strong>
                                    </span>
                                    <span v-if="loading"><i class="fa fa-spinner fa-spin"></i></span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <b-overlay no-center :show="has_file" :opacity="0.42" rounded="sm" z-index="1" no-wrap>
                        <template #overlay>
                            <i class="fas fa-lock position-absolute text-danger"
                               style="top: 3%; right: 23px;font-size: 20px;"></i>
                        </template>
                    </b-overlay>
                </div>

                <div class="col-12">
                    <div class="form-row d-flex justify-content-between align-items-center">
                        <div v-if="quantity_persons.adults > 0 && has_file">
                            <a href="#" v-on:click="modalPassengers(quote_id, passengers.length)"
                               class="link a-filtros" style="color: black;!important;">
                                <i class="icon icon-user-check"></i>
                                <span class="text text-dark">{{ trans('quote.label.passengers_list') }}</span>
                                <small>(@{{ passengers.length }})</small>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="col-12 cotizacion-cotizar line-bottom" v-if="quote_open!=''">
                        @include('quotes.buttons')
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
                            <b-overlay no-center :show="has_file" :opacity="0.42" rounded="sm" z-index="1" no-wrap>
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
                                        <label v-if="!has_file">{{ trans('quote.label.copy_from_category')}}:</label>
                                        <select name="" id="" v-model="categoryForCopy" class="form-control ml-1"
                                                v-if="!has_file"
                                                style="width: 170px;">
                                            <option v-for="category in quote_open.categories"
                                                    v-if="category.tabActive===''" :value="category.id">
                                                @{{ category.type_class.translations[0].value }} (@{{
                                                category.services.length }})
                                            </option>
                                        </select>
                                        <button type="button" class="btn btn-success ml-1" v-if="!has_file"
                                                @click="willCopyCategory()">
                                            {{trans('global.label.do')}}
                                        </button>
                                        <div v-if="has_file">
                                            <b-form-checkbox v-model="hiddenLocked" name="check-button" size="lg"
                                                             @change="changeLocked(hiddenLocked)" switch>
                                                <span v-if="hiddenLocked">{{trans('quote.label.view_blocked')}}</span>
                                                <span v-if="!hiddenLocked">{{trans('quote.label.hide_locked')}}</span>
                                            </b-form-checkbox>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center justify-content-end">
                                        <button class="btn btn-secondary" @click="showModalHotel('')">
                                            + {{ trans('quote.label.hotel') }}
                                        </button>
                                        <button class="btn btn-secondary" data-toggle="modal"
                                                data-target="#modal-servicios"
                                                @click="showCategories">+ {{ trans('quote.label.service') }}
                                        </button>
                                        <button class="btn btn-secondary" data-toggle="modal"
                                                data-target="#modal_extensions"
                                                @click="showModalExtension">+ {{ trans('quote.label.extension') }}
                                        </button>
                                        <button class="btn btn-secondary" @click="showModalFlight('')"
                                                data-toggle="modal"
                                                data-target="#modal-flight">+ {{ trans('quote.label.flight') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
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
                                <a class="acciones__item" @click="openModalClose()">
                                    <span><i class="far fa-window-close"></i> {{ trans('quote.label.close') }}</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Lista de Resultados -->
                <div class="col-12 cotizacion-listado p-0 m-0" v-if="quote_open!=''"
                     v-for="qCateg in quote_open.categories"
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
                                        <div class="icon-eliminar cursor-pointer">
                                            <i class="far fa-trash-alt" title="{{trans('global.icon.delete_all')}}"
                                               @click="deleteServices"></i>
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
                                       @end="drag=false" @update="checkMoveService(qCateg.services)">
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
                                                 :class="{ 'prod-acciones':true, 'porc10' : quote_open.operation == 'ranges' }"
                                                 style="width: 100px;">
                                                <i class="icon icon-drag handle"
                                                   v-if="service.type !='group_header'"></i>
                                                <input style="" class="form-check-input" type="checkbox"
                                                       @change="addServiceDelete(service)">
                                                <a data-toggle="modal" href="#modal_extensions"
                                                   v-if=" service.extension_id!=null">
                                                    <i class="icon icon-plus-square"
                                                       @click="selectReplaceExtension(service)"></i>
                                                </a>
                                                <a data-toggle="modal" href="#modal-servicios"
                                                   :class="{'iconServiceInExtension':service.extension_id!=null}"
                                                   v-if="service.type == 'service'"
                                                   @click="openModalService(qCateg, service)">
                                                    <i class="icon icon-plus-circle"
                                                       title="{{trans('global.icon.add_and_replace')}}"></i>
                                                </a>

                                                <a @click="showModalHotel(service)"
                                                   v-if="service.type =='hotel' && service.extension_id==null">
                                                    <i class="icon icon-plus-circle"
                                                       title="{{trans('global.icon.add_and_replace')}}"></i>
                                                </a>
                                                <a href="javascript:;" style="z-index: 1;"
                                                   @click="willRemoveService(qCateg, service)">
                                                    <i class="icon icon-trash"
                                                       title="{{trans('global.icon.delete')}}"></i>
                                                </a>
                                                <span v-if="service.type != 'flight' && service.type != 'group_header'"
                                                      style="cursor: pointer;z-index: 1;"
                                                      @click="updateOptional(service)">
                                                    <i class="icon icon-book" style="color: #890005"
                                                       title="{{trans('global.icon.optional')}}"> </i>
                                                </span>
                                            </div>
                                            <div class="d-flex align-items-center justify-content-center"
                                                 :class="{ 'prod-fecha':true, 'porc20' : quote_open.operation == 'ranges' }"
                                                 style="width: 130px;">

                                                <a data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                                   v-if="service.type=='service'"
                                                   class="acciones__item schedules-icon">
                                                    <span
                                                        class="producto-acomodacion--cambiar btn btn-icon btn-time-sucess"
                                                        title="Horarios"
                                                        @click="selectServiceSelected(service,index_service)"
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
                                                <div @click.stop="" v-if="service.type=='service'"
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
                                                            <span v-else class="alert alert-warning"> <i
                                                                    class="fa fa-info-circle"></i> Ningún horario para mostrar</span>
                                                        </div>

                                                    </div>
                                                </div>

                                                <div style="padding-top: 3rem;">
                                                    <date-picker class="date" v-model="service.date_in"
                                                                 :config="optionsR"
                                                                 @dp-show="showDatePickerService(service)"
                                                                 @dp-change="updateDateInService(service,index_service)">
                                                    </date-picker>

                                                    <div style="display: block;">
                                                        <input type="text" class="date-disabled"
                                                               v-model="service.date_out"
                                                               v-if="service.type =='hotel'"
                                                               v-show="show_dateIn"
                                                               disabled>
                                                    </div>
                                                </div>


                                                {{--                                                HORA DE INICIO --}}
                                                <div v-if="service.type=='service'"
                                                     class="acciones__item schedules-hours" style="font-size: 10px;">
                                                    <span v-if="service.service.service_type_id == 2"
                                                          class="schedule-hour">
                                                        <input type="time" v-model="service.hour_in"
                                                               @input="change_hour_in(service)">
                                                    </span>
                                                    <span v-else class="time-locked">
                                                        <i class="fa fa-clock"></i> @{{ service.hour_in | format_hour }}
                                                    </span>
                                                </div>

                                            </div>
                                            <div class="mx-3 d-flex align-items-center justify-content-center"
                                                 :class="{ 'prod-max':true, 'porc8' : quote_open.operation == 'ranges' }"
                                                 style="width: 60px;">
                                                <select class="select-pax " v-model="service.nights"
                                                        @change="updateNightsService(service)"
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
                                            <div class="prod-descripcion mx-4 d-flex"
                                                 :class="{ 'prod-descripcion':true, 'porc35' : quote_open.operation == 'ranges' }">
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
                                                    [@{{ service.service.aurora_code }}]
                                                </span>
                                                <span class="id mr-3"
                                                      v-if="(service.type =='hotel' || service.type =='group_header' ) && service.hotel.channel.length>0">
                                                    [@{{ service.hotel.channel[0].code }}]
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
                                                    @{{ service.service.service_translations[0].name }}
                                                    <b v-if="service.service.service_type.code==='SIM' || service.service.service_type.code==='PC'">
                                                         - [@{{ service.service.service_type.translations[0].value }}].
                                                    </b>
                                                    <p v-if="service.type==='service' && (service.service.deleted_at == '' || service.service.deleted_at == null)">
                                                        <a href="#"
                                                           @click="openModalDetail(service.service.id,'inclusions',service.date_out, service.adult, service.child)">
                                                            {{trans('service.label.includes_not_include')}}
                                                        </a>
                                                        <a href="#"
                                                           @click="openModalDetail(service.service.id,'itinerary',service.date_out, service.adult, service.child)">
                                                            {{trans('service.label.itinerary')}}
                                                        </a> <br>
                                                        <a href="#"
                                                           @click="openModalDetail(service.service.id,'schedule',service.date_out, service.adult, service.child)">
                                                            {{trans('service.label.schedules_restrictions')}}
                                                        </a><br>
                                                        <a data-toggle="modal" href="#modal-real-notes"
                                                           v-if="service.service.service_translations[0].summary != null && service.service.service_translations[0].summary != ''"
                                                           @click="service_real_notes=service.service.service_translations[0].summary">
                                                            {{trans('service.label.summary')}}
                                                        </a><br>
                                                        <a data-toggle="modal" href="#modal-notes"
                                                           v-if="service.service.notes != null && service.service.notes != ''"
                                                           @click="service_notes=service.service.notes">
                                                            Remarks
                                                        </a>
                                                    </p>
                                                </span>

                                                <span class="texto"
                                                      v-if="service.type =='hotel' || service.type =='group_header'">
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
                                                                <a style="margin-left: 6px; margin-top: 3px; font-size: 9.5px;cursor:pointer;"
                                                                   class="a-plan-room" @click="editPlanRooms(service)">
                                                                    <i class="fa fa-bed icon_green"></i>
                                                                    @{{ service_room.rate_plan_room.room.translations[0].value }}
                                                                </a>
                                                                <button title="Eliminar" type="button"
                                                                        class="btn btn-sm btn-danger ml-2 mb-2"
                                                                        @click="deleteRoomHotel(service_room.id)"
                                                                        style="border-radius: 10px;"
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
                                                                    <i class="fa fa-bed icon_green"></i>
                                                                    @{{ service_room.rate_plan_room.room.translations[0].value }}
                                                                </span>
                                                            </span>
                                                        </span>

                                                    </span>

                                                    <div v-if="service.type =='group_header'">
                                                        <button type="button"
                                                                style="padding: 0.25rem 0.5rem !important;font-size: 0.875rem !important;"
                                                                class="btn btn-sm btn-success ml-2 mb-2"
                                                                @click="editPlanRooms(service)">
                                                                    <i class="fas fa-plus-circle"></i> {{ trans('global.label.add') }}
                                                                </button>
                                                    </div>

                                                    <span v-if="service.service_rooms.length > 3"> ({{ trans('global.label.and') }} @{{ service.service_rooms.length - 3 }} {{ trans('global.label.more') }}) </span>

                                                    <br/><br/>
                                                    <a v-if="service.type == 'hotel'" href="javascript:;"
                                                       @click="openModalNotesHotel(service)">
                                                        {{ trans('quote.label.notes_hotel') }}
                                                    </a>
                                                </span>

                                                <a style="margin-left: 15px; margin-top: 3px;cursor:pointer;"
                                                   v-if="service.type == 'hotel' && service.service_rooms.length == 0"
                                                   @click="editPlanRooms(service)">
                                                    <i class="fa fa-bed"></i>
                                                </a>
                                                <a style="margin-left: 15px; margin-top: 3px;cursor:pointer;"
                                                   v-if="service.type == 'group_header' && service.service_rooms.length == 0"
                                                   @click="editPlanRooms(service)">
                                                    <i class="fa fa-bed"></i>
                                                </a>

                                            </div>

                                            <div class="prod-detalle d-flex align-items-center justify-content-center"
                                                 v-if="quote_open.operation =='passengers' && service.type !='group_header'">
                                                <div v-show="!service.showQuantityPassengers"
                                                     class="justify-content-around" style="display:flex;">
                                                    <span>@{{ service.adult }} {{ trans('quote.label.adults') }}</span>
                                                    <span
                                                        v-if="service.adult >0">@{{ service.child }} {{ trans('quote.label.child') }}</span>
                                                </div>
                                                <div class="form-group" v-show="service.showQuantityPassengers"
                                                     style="margin: 0px !important;">
                                                    <label>{{ trans('quote.label.adults') }}</label>
                                                    <select class="form-control" v-model="service.adult"
                                                            style="width: 50px !important;"
                                                            @change="updatePassengerService(service)">
                                                        <option value="0">
                                                            0
                                                        </option>
                                                        <option :value="adult" v-for="adult in quantity_persons.adults">
                                                            @{{adult}}
                                                        </option>

                                                    </select>
                                                </div>
                                                <div class="form-group"
                                                     v-if="service.adult > 0 && service.showQuantityPassengers">
                                                    <label>{{ trans('quote.label.child') }}</label>
                                                    <select class="form-control" v-model="service.child"
                                                            @change="updatePassengerService(service)">
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
                                                      @click="showSelectQuantityPassengersService(service)"
                                                      v-if="!service.showQuantityPassengers">
                                                    <i class="icon icon-edit"></i>
                                                </span>
                                                <span class="btn btn-icon producto-editar--boton"
                                                      title="{{ trans('quote.label.save') }}"
                                                      @click="showSelectQuantityPassengersService(service)"
                                                      v-if="service.showQuantityPassengers">
                                                    <i class="icon icon-save"></i>
                                                </span>
                                            </div>

                                            <div
                                                class="prod-precio d-flex align-items-center justify-content-center"
                                                v-if="service.amount.length>0 && quote_open.operation =='passengers' && service.type !='group_header'">
                                                <span class="producto-precio--num"><small>ADL </small> @{{ service.amount[0].price_adult | roundLito }}</span>
                                            </div>
                                            {{--                                            <div--}}
                                            {{--                                                class="prod-precio d-flex align-items-center justify-content-center"--}}
                                            {{--                                                v-if="service.amount !=null && quote_open.operation =='passengers' && service.child >0">--}}
                                            {{--                                                <span class="producto-precio--num"><small>CHD </small> @{{ service.amount.price_child  | roundLito }}</span>--}}
                                            {{--                                            </div>--}}
                                            <div
                                                class="prod-acomodacion d-flex align-items-center justify-content-center dropdown-group"
                                                v-if="quote_open.operation =='passengers'  && service.type !='group_header'">
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
                                                                       @change="willSavePassengerService(service, passenger)">
                                                                <span
                                                                    v-if="!(!!passenger.first_name && !!passenger.last_name)">
                                                                    <span v-if="passenger.type == 'ADL'">{{ trans('quote.label.adults') }} @{{ passenger.index }}</span>
                                                                    <span v-if="passenger.type == 'CHD'">{{ trans('quote.label.child') }} @{{  passenger.index }}</span>
                                                                    <span v-if="!(!!passenger.type)">{{ trans('quote.label.adults') }} @{{  passenger.index }}</span>
                                                                </span>
                                                                <span v-else>
                                                                    @{{ passenger.first_name }} @{{ passenger.last_name }}
                                                                </span>
                                                            </label>
                                                        </div>
                                                        <button class="btn btn-success" @click="savePassengerService"><i
                                                                class="icon icon-save"></i> {{ trans('quote.label.save') }}
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            {{--                                        LA CAMITA  v-if="service.type=='hotel'"--}}
                                            {{--                                            <img class="ico-error" src="{{ asset('/images/status-error.png') }}" alt="">--}}
                                            <div v-if="service.type=='group_header'">
                                                <div class="d-flex justify-content-center col-12">
                                                    <div class="form text-center col-4 mr-2"
                                                         :class="{'accommodation-enabled' : service.single > 0, 'accommodation-disabled' : service.single == 0}">
                                                        <label for="" class="font-weight-bold">SGL</label><br>
                                                        <input class="mx-1 inputs-ocupation" type="number"
                                                               @input="updateOccupationHotel" min="0"
                                                               max="30"
                                                               step="1"
                                                               :disabled="service.simple_enabled"
                                                               v-model="service.single">
                                                    </div>
                                                    <div class="form text-center col-4 mr-2"
                                                         :class="{'accommodation-enabled' : service.double > 0, 'accommodation-disabled' : service.double == 0}">
                                                        <label for="" class="font-weight-bold">DBL</label><br>
                                                        <input class="mx-1 inputs-ocupation" type="number"
                                                               @input="updateOccupationHotel" min="0"
                                                               max="30"
                                                               step="1"
                                                               :disabled="service.double_enabled"
                                                               v-model="service.double">
                                                    </div>
                                                    <div class="form text-center col-4 mr-2"
                                                         :class="{'accommodation-enabled' : service.triple > 0, 'accommodation-disabled' : service.triple == 0}">
                                                        <label for="" class="font-weight-bold">TPL</label><br>
                                                        <input class="mx-1 inputs-ocupation" type="number"
                                                               @input="updateOccupationHotel" min="0"
                                                               max="30"
                                                               step="1"
                                                               :disabled="service.triple_enabled"
                                                               v-model="service.triple">
                                                    </div>
                                                </div>

                                                {{--                                                <div class="d-flex justify-content-center col-12"--}}
                                                {{--                                                     style="padding-top: 0px !important;"--}}
                                                {{--                                                     v-if="service.child > 0">--}}
                                                {{--                                                    <div class="form col-4">--}}
                                                {{--                                                        <label for="">DBL (CHD)</label><br>--}}
                                                {{--                                                        <input class="mx-1 inputs-ocupation" type="number"--}}
                                                {{--                                                               @input="updateOccupationHotel" min="0"--}}
                                                {{--                                                               max="30"--}}
                                                {{--                                                               step="1"--}}
                                                {{--                                                               :disabled="service.double_child_enabled"--}}
                                                {{--                                                               v-model="service.double_child">--}}
                                                {{--                                                    </div>--}}
                                                {{--                                                    <div class="form col-4">--}}
                                                {{--                                                        <label for="">TPL (CHD)</label><br>--}}
                                                {{--                                                        <input class="mx-1 inputs-ocupation" type="number"--}}
                                                {{--                                                               @input="updateOccupationHotel" min="0"--}}
                                                {{--                                                               max="30"--}}
                                                {{--                                                               step="1"--}}
                                                {{--                                                               :disabled="service.triple_child_enabled"--}}
                                                {{--                                                               v-model="service.triple_child">--}}
                                                {{--                                                    </div>--}}
                                                {{--                                                </div>--}}

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

                                            <div class="mx-3 d-flex align-items-center"
                                                 style="position: absolute;right: -10px;background: #eee;padding: 7px;top: 0px;"
                                                 v-if="service.type == 'hotel'">
                                                <a href="javascript:;" @click="showModalHotelPromotion(service)">
                                                    <i class="fa fa-fire"></i>
                                                </a>
                                            </div>

                                            <div class="content_services_group prod-group"
                                                 v-for="(service_extension,index_service_extension) in qCateg.services"
                                                 v-if="service.id == service_extension.parent_service_id">
                                                <!--<hr class="line_vertical">-->
                                                <div class="prod-acciones align-items-center">

                                                    <a href="javascript:;" style="z-index: 1;"
                                                       @click="willRemoveService(qCateg, service_extension)"
                                                       v-if="!loading">
                                                        <i class="icon icon-trash"
                                                           title="{{trans('global.icon.delete')}}"></i>
                                                    </a>
                                                    <a href="javascript:;" v-if="loading" style="color: #919595;">
                                                        <i class="icon icon-trash"
                                                           title="{{trans('global.icon.delete')}}"></i>
                                                    </a>

                                                    <a data-toggle="modal" href="#modal-servicios"
                                                       v-if="service_extension.type == 'service'"
                                                       @click="openModalService(qCateg, service_extension)">
                                                        <i class="icon icon-plus-circle"
                                                           title="{{trans('global.icon.add_and_replace')}}"></i>
                                                    </a>

                                                    <a
                                                        @click="showModalHotel(service_extension)"
                                                        v-if="service_extension.type == 'hotel'">
                                                        <i class="icon icon-plus-circle"
                                                           title="{{trans('global.icon.add_and_replace')}}"></i>
                                                    </a>
                                                </div>
                                                <div class="prod-fecha">
                                                    <span>
                                                        <date-picker class="date" v-model="service_extension.date_in"
                                                                     :config="optionsR"
                                                                     @dp-show="showDatePickerService(service_extension)"
                                                                     @dp-change="updateDateInService(service_extension,index_service)"></date-picker>
                                                    </span>

                                                    <span>
                                                        <input type="text" class="date-disabled"
                                                               v-model="service_extension.date_out"
                                                               v-if="service_extension.type =='hotel'"
                                                               v-show="show_dateIn"
                                                               disabled>
                                                    </span>
                                                    {{--                                                <span class="fecha">@{{ service_extension.date_in }}</span>--}}
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
                                                                   @click="editPlanRooms(service_extension)">
                                                                    <i class="fa fa-bed icon_green"></i>
                                                                 @{{ service_room.rate_plan_room.room.translations[0].value }}
                                                                </a>
                                                            </span>
                                                        </span>
                                                        <span v-if="service_extension.service_rooms.length > 3"> ({{ trans('global.label.and') }} @{{ service_extension.service_rooms.length - 3 }} {{ trans('global.label.more') }}) </span>
                                                    </span>

                                                    <a style="margin-left: 15px; margin-top: 3px;cursor:pointer;"
                                                       v-if="service_extension.type == 'hotel' && service_extension.service_rooms.length==0"
                                                       @click="editPlanRooms(service_extension)">
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
                                                                @change="updatePassengerService(service)">
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
                                                                @change="updatePassengerService(service_extension)">
                                                            <option value="0">0</option>
                                                            <option value="1">1</option>
                                                            <option value="2">2</option>
                                                            <option value="3">3</option>
                                                            <option value="4">4</option>
                                                            <option value="5">5</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="prod-editar text-center"
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
                                                </div>
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
                                                                           @change="willSavePassengerService(service, passenger)">
                                                                    <span
                                                                        v-if="!(!!passenger.first_name && !!passenger.last_name)">
                                                                        <span v-if="passenger.type == 'ADL'">{{ trans('quote.label.adults') }} @{{ passenger.index }}</span>
                                                                        <span v-if="passenger.type == 'CHD'">{{ trans('quote.label.child') }} @{{ passenger.index }}</span>
                                                                        <span v-if="!(!!passenger.type)">{{ trans('quote.label.adults') }} @{{  passenger.index }}</span>
                                                                    </span>
                                                                    <span v-else>
                                                                        @{{ passenger.checked }} @{{ passenger.first_name }}
                                                                        @{{ passenger.last_name }}
                                                                    </span>
                                                                </label>
                                                            </div>
                                                            <button class="btn btn-success"
                                                                    @click="savePassengerService">
                                                                <i class="icon icon-save"></i> {{ trans('quote.label.save') }}
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                {{--                                                <div class="prod-acomodacion dropdown-group"--}}
                                                {{--                                                     v-if="service.type=='hotel'">--}}
                                                {{--                                                    <a data-toggle="dropdown" aria-haspopup="true"--}}
                                                {{--                                                       aria-expanded="false"--}}
                                                {{--                                                       class="acciones__item">--}}
                                                {{--                                                   <span class="producto-acomodacion--cambiar btn btn-icon"--}}
                                                {{--                                                         title="{{ trans('quote.label.modify_hotel_occupancy') }}"--}}
                                                {{--                                                         @click="setServiceHotelSelected(service)">--}}
                                                {{--                                                       <i :class="{'icon icon-bed-simple':true,--}}
                                                {{--                                                            'icon_green':((service.single+service.double+service.triple)>0),--}}
                                                {{--                                                            'icon_red':((service.single+service.double+service.triple)==0)}"--}}
                                                {{--                                                          title="{{trans('global.icon.accommodation')}}"></i>--}}
                                                {{--                                                   </span>--}}
                                                {{--                                                    </a>--}}
                                                {{--                                                    <div--}}
                                                {{--                                                        class="dropdown-menu dropdown-menu__cotizacion dropdown-menu-right"--}}
                                                {{--                                                        @click.stop="" style="overflow-y: scroll; z-index: 100;"--}}
                                                {{--                                                        v-if="service_selected.single !='' && service_selected.double !='' && service_selected.type=='hotel'">--}}
                                                {{--                                                        <div class="dropdown-menu_body container">--}}

                                                {{--                                                            <div--}}
                                                {{--                                                                class="row justify-content-center col-12 alert-accomodation">--}}
                                                {{--                                                                <div class="alert alert-warning"><i--}}
                                                {{--                                                                        class="fa fa-info-circle"></i> La--}}
                                                {{--                                                                    acomodación no--}}
                                                {{--                                                                    coincide con las tarifas asignadas.--}}
                                                {{--                                                                </div>--}}
                                                {{--                                                            </div>--}}
                                                {{--                                                            <div class="d-flex justify-content-center col-12">--}}
                                                {{--                                                                <div class="form col-4">--}}
                                                {{--                                                                    <label for="">SGL</label>--}}
                                                {{--                                                                    <input class="mx-1" type="number" min="0"--}}
                                                {{--                                                                           max="30"--}}
                                                {{--                                                                           step="1" @input="updateOccupationHotel"--}}
                                                {{--                                                                           v-model="service_selected.single">--}}
                                                {{--                                                                </div>--}}
                                                {{--                                                                <div class="form col-4">--}}
                                                {{--                                                                    <label for="">DBL</label>--}}
                                                {{--                                                                    <input class="mx-1" type="number" min="0"--}}
                                                {{--                                                                           max="30"--}}
                                                {{--                                                                           step="1" @input="updateOccupationHotel"--}}
                                                {{--                                                                           v-model="service_selected.double">--}}
                                                {{--                                                                </div>--}}
                                                {{--                                                                <div class="form col-4">--}}
                                                {{--                                                                    <label for="">TPL</label>--}}
                                                {{--                                                                    <input class="mx-1" type="number" min="0"--}}
                                                {{--                                                                           max="30"--}}
                                                {{--                                                                           step="1" @input="updateOccupationHotel"--}}
                                                {{--                                                                           v-model="service_selected.triple">--}}
                                                {{--                                                                </div>--}}
                                                {{--                                                            </div>--}}
                                                {{--                                                        </div>--}}
                                                {{--                                                    </div>--}}
                                                {{--                                                </div>--}}
                                            </div>
                                        </div>
                                        <div class="row"
                                             v-if="service.validations.length > 0">
                                            <div class="col-md-12 p-3">
                                                <hr>
                                                <h5 class="font-weight-bold text-danger px-3">
                                                    *{{trans('quote.label.observations')}}:
                                                </h5>
                                                <div v-for="validation in service.validations"
                                                     class="badge badge-danger danger-date mr-2 mb-2 mx-3">
                                                    <i class="fas fa-exclamation-circle animated faa-flash"></i>
                                                    <span v-if="validation.range !== ''">
                                                        {{trans('quote.label.ranges')}} @{{ validation.range }} <i
                                                            class="fas fa-long-arrow-alt-right"></i>
                                                    </span> @{{ validation.error }}
                                                </div>
                                            </div>
                                        </div>
                                        <div
                                            v-if="(service.type=='hotel' || service.type =='group_header') && service.hotel.allows_child==1 && quantity_persons.child>0"
                                            style="position: absolute;display: flex;bottom: 0px;right: 0px;font-size: 13px;">
                                            <small class="alert alert-info m-0" style="padding: 4px !important;">
                                                <i class="fa fa-info-circle"></i> {{trans('quote.label.age_child_between_provider')}}
                                                @{{ service.hotel.min_age_child }} {{trans('quote.label.and')}} @{{
                                                service.hotel.max_age_child }} {{trans('quote.label.years_old')}}
                                            </small>
                                        </div>
                                        <div
                                            v-if="service.type=='service' && service.service.allow_child==1 && quantity_persons.child>0"
                                            style="position: absolute;display: flex;bottom: 0px;right: 0px;font-size: 13px;">
                                            <small class="alert alert-info m-0" style="padding: 4px !important;">
                                                <i class="fa fa-info-circle"></i> {{trans('quote.label.age_child_between_provider')}}
                                                @{{
                                                service.service.children_ages[0].min_age }} {{trans('quote.label.and')}}
                                                @{{ service.service.children_ages[0].max_age
                                                }} {{trans('quote.label.years_old')}}
                                            </small>
                                        </div>

                                        <b-overlay no-center :show="service.locked" :opacity="0.42" z-index="1" gi
                                                   rounded="sm" no-wrap>
                                            <template #overlay>
                                                <i class="fas fa-lock position-absolute text-danger"
                                                   style="top: 43%; left: 14px;font-size: 20px;"></i>
                                            </template>
                                        </b-overlay>
                                    </li>
                                </transition-group>
                            </draggable>
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
                        <button class="btn btn-secondary" @click="showModalFlight('')" data-toggle="modal"
                                data-target="#modal-flight">+ {{ trans('quote.label.flight') }}
                        </button>
                        <button class="btn btn-secondary" data-toggle="modal" data-target="#modal_extensions"
                                @click="showModalExtension">+ {{ trans('quote.label.extension') }}
                        </button>
                        <button class="btn btn-secondary" data-toggle="modal" data-target="#modal-servicios"
                                @click="showCategories">+ {{ trans('quote.label.service') }}
                        </button>
                        <button class="btn btn-secondary" @click="showModalHotel('')">+ {{ trans('quote.label.hotel') }}
                        </button>
                    </div>
                </div>
                <div class="col-12 cotizacion-cotizar" v-if="quote_open!=''">
                    @include('quotes.buttons')
                </div>
            </div>
        </div>
    </section>
    {{--          modales del formulario          --}}
    {{--        modal Lista de pasajeros de un servicio         --}}
    <modal-passengers ref="modal_passengers"></modal-passengers>

    {{--        modal Ocupacion de Hotel General         --}}
    <b-modal centered id="modal_occupation_hotel" ref="modal_occupation_hotel">
        <h1>
            <i class="icon icon-bed-simple mr-2"
               title="{{trans('global.icon.accommodation')}}"></i> {{ trans('quote.label.hotel_general_occupation') }}
        </h1>
        <hr>
        <div class="d-flex justify-content-between mb-5">
            <div class="form-xs">
                <label for="">SGL</label>
                <input type="number" min="0" max="30" step="1" class="form-control"
                       v-model="control_service_selected_general.single">
            </div>
            <div class="form-xs">
                <label for="">DBL</label>
                <input type="number" min="0" max="30" step="1" class="form-control"
                       v-model="control_service_selected_general.double">
            </div>
            <div class="form-xs">
                <label for="">TPL</label>
                <input type="number" min="0" max="30" step="1" class="form-control"
                       v-model="control_service_selected_general.triple">
            </div>
        </div>
        <hr>
        {{--        <div class="d-flex justify-content-between mb-5" v-if="quantity_persons.child > 0">--}}
        {{--            <div class="form-xs">--}}
        {{--                <label for="">CHD - DBL</label>--}}
        {{--                <input type="number" min="0" max="30" step="1" class="form-control"--}}
        {{--                       v-model="control_service_selected_general.double_child">--}}
        {{--            </div>--}}
        {{--            <div class="form-xs">--}}
        {{--                <label for="">CHD - TPL</label>--}}
        {{--                <input type="number" min="0" max="30" step="1" class="form-control"--}}
        {{--                       v-model="control_service_selected_general.triple_child">--}}
        {{--            </div>--}}
        {{--        </div>--}}

        <div class="my-3 d-flex">
            Distribuir: <strong style="margin: 0 5px;"> @{{ quantity_persons.adults
                }} </strong> {{ trans('quote.label.adults') }} + <strong style="margin: 0 5px;"> @{{
                quantity_persons.child }} </strong> {{ trans('quote.label.child') }}
        </div>

        <div class="my-3 d-flex">
            <button class="btn btn-primary mr-2"
                    @click="updateOccupationHotelGeneral">
                {{--                 :disabled="!( (quantity_persons.adults + quantity_persons.child)
                === ( parseInt(control_service_selected_general.single)
                + (parseInt(control_service_selected_general.double)*2)+(parseInt(control_service_selected_general.triple) * 3)))
                && !((quantity_persons.adults + quantity_persons.child) == 0) || loading_occupation"--}}
                <i class="fa fa-spin fa-spinner" v-if="loading_occupation"></i> {{ trans('quote.label.save') }}
            </button>
            <button class="btn btn-cancelar ml-2" style="height: 52px;" :disabled="loading_occupation"
                    @click="closeModalOccupationHotel">{{ trans('quote.label.cancel') }}</button>
        </div>


        <div slot="modal-footer">

        </div>

    </b-modal>
    {{--      End modal Ocupacion de Hotel        --}}

    {{--          modal-notas           --}}
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
                                        {{--                                            <p class="text-muted m-2">Julio 10,2019 - 16:22hrs</p>--}}
                                        <button class="btn btn-danger btn-lg m-2" @click="createNote" type="button">
                                            {{ trans('quote.label.save_note') }}
                                        </button>
                                        {{--                                            <b-button v-on:click="" class="btn btn-inverse btn-lg m-2">Regresar--}}
                                        {{--                                            </b-button>--}}
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
                                         alt=""/>
                                    <img v-else width="40px"
                                         :src="baseURLPhoto + 'images/anonimo.jpg'"
                                         alt=""/>
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
                                                 alt=""/>
                                            <img v-else width="40px"
                                                 :src="baseURLPhoto + 'images/anonimo.jpg'"
                                                 alt=""/>
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
    {{--        End modal-notas         --}}

    {{--          modal-rango           --}}
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
                            <td nowrap class="td"><input type="text" class="form-control" placeholder="1"/></td>
                            <td nowrap class="td"><input type="text" class="form-control" placeholder="1"/></td>
                            <td nowrap class="td"><input type="text" class="form-control" placeholder="208.50"/>
                            </td>
                            <td nowrap class="td"><input type="text" class="form-control" placeholder="0.00"/></td>
                            <td nowrap class="td"><input type="text" class="form-control" placeholder="0.00"/></td>
                            <td nowrap class="td icon-modal"><a class="" title=""><i
                                        class="icon-plus-square"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" class="th">2</th>
                            <td nowrap class="td"><input type="text" class="form-control" placeholder="1"/></td>
                            <td nowrap class="td"><input type="text" class="form-control" placeholder="1"/></td>
                            <td nowrap class="td"><input type="text" class="form-control" placeholder="208.50"/>
                            </td>
                            <td nowrap class="td"><input type="text" class="form-control" placeholder="0.00"/></td>
                            <td nowrap class="td"><input type="text" class="form-control" placeholder="0.00"/></td>
                            <td nowrap class="td icon-modal"><a class="" title=""><i
                                        class="icon-plus-square"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" class="th">3</th>
                            <td nowrap class="td"><input type="text" class="form-control" placeholder="1"/></td>
                            <td nowrap class="td"><input type="text" class="form-control" placeholder="1"/></td>
                            <td nowrap class="td"><input type="text" class="form-control" placeholder="208.50"/>
                            </td>
                            <td nowrap class="td"><input type="text" class="form-control" placeholder="0.00"/></td>
                            <td nowrap class="td"><input type="text" class="form-control" placeholder="0.00"/></td>
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
    {{--        End modal-rango         --}}

    {{--          modales generales          --}}
    {{--          modal-hotel           --}}
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
                            <div class="form-group cotizacion-crear--boton ml-5 mt-4">
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
                                    {{--                                                    <span v-if="hotel.channel.length>0">--}}
                                    {{--                                                        [@{{ hotel.channel[0].code }}] ---}}
                                    {{--                                                        </span>--}}
                                    @{{ hotel.name }}
                                </strong>
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
                        <div v-for="(room, rkey) in hotel.rooms"
                             v-if="room.rates.length>0 && room.countCalendars>0">
                            {{-- &&
                                  ( ( room.occupation===1 && service_selected_general.single>0 ) ||
                                    ( room.occupation===2 && service_selected_general.double>0 ) ||
                                    ( room.occupation===3 && service_selected_general.triple>0 ) )   --}}
                            <div class="rooms-table row canSelectText">
                                <div class="col-4 my-auto">
                                    <strong>{{ trans('quote.label.name') }}: </strong>@{{
                                    room.name }}<br>
                                    <strong>{{ trans('quote.label.description') }}: </strong>@{{
                                    room.room_type }}
                                </div>
                                <div class="col-8 my-auto">
                                    <div v-for="(rate, raKey) in room.rates"
                                         :class="'col-12 rateRow rateChoosed_' + checkboxs[ hotel.id + '_' + rate.rateId ]"
                                         v-if="rate.rate[0].amount_days.length > 0">
                                        {{--    --}}
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
                                                                            <p class="mb-0"><b>{{ trans('hotel.label.political_children') }}</b>
                                                                            </p>
                                                                            <p class="mb-0"
                                                                               v-if="hotel.political_children.child.allows_child == 1">
                                                                                <b>{{ trans('hotel.label.children') }}</b>
                                                                                @{{
                                                                                hotel.political_children.child.min_age_child
                                                                                }} {{ trans('hotel.label.years') }} {{ trans('hotel.label.to') }} @{{
                                                                                hotel.political_children.child.max_age_child
                                                                                }} {{ trans('hotel.label.years') }} @{{
                                                                                rate.political.no_show_apply.political_child
                                                                                }}
                                                                            </p>
                                                                            <p class="mb-0"
                                                                               v-if="hotel.political_children.child.allows_teenagers == 1">
                                                                                <b>{{ trans('hotel.label.infants') }}</b>
                                                                                @{{
                                                                                hotel.political_children.infant.min_age_teenagers
                                                                                }} {{ trans('hotel.label.years') }} {{ trans('hotel.label.to') }} @{{
                                                                                hotel.political_children.infant.max_age_teenagers
                                                                                }} {{ trans('hotel.label.years') }} @{{
                                                                                rate.political.no_show_apply.political_child
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
                                                                            <p>@{{  rate.political.cancellation.name }}</p>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </div>
                                                        </b-popover>

                                                        @{{ rate.name }} <?php if(Auth::user()->user_type_id == 3): ?>(@{{ rate.name_commercial }})<?php endif; ?>:
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
                                            <strong>$ <span v-if="rate.rate[0].amount_days[0]">
                                                                            @{{ rate.rate[0].amount_days[0].total_amount }} </span>
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
                                           name="flight_type" value="0" id="nacional"/>
                                    <label class="form-check-label"
                                           for="nacional">{{ trans('flights.label.national') }}</label>
                                </div>
                                <div class="form-group form-check">
                                    <input type="radio" class="form-check-input" v-on:change="resetDestinations()"
                                           v-model="flight_type"
                                           name="flight_type" value="1" id="internacional"/>
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

    {{--        modal-servicios         --}}
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
                                            <li class="icon-results" v-if="mS.notes != null && mS.notes != ''">
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
                                              v-if="(quantity_persons.adults+quantity_persons.child) >= s_r_plan.pax_from && (quantity_persons.adults+quantity_persons.child) <= s_r_plan.pax_to">
                                                $@{{ s_r_plan.price_adult_label }}
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
    {{--      End modal-servicios       --}}

    {{--        modal de Extensiones      --}}
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
                    <div class="mt-4" style="text-align: right;overflow: hidden;">
                        <button :class="'btn categoria check_' + qCateg.checkAddExtension"
                                v-for="qCateg in quote_open.categories"
                                @click="toggleCategoryCheckAddExtension(qCateg)">
                            <i class="far fa-square" v-if="!(qCateg.checkAddExtension)"></i>
                            <i class="fa fa-check-square" v-if="qCateg.checkAddExtension"></i>
                            @{{ qCateg.type_class.translations[0].value }}
                        </button>
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
                                    {{--                                        <input type="text" class="form-control filter" id="extension_word" placeholder="{{ trans('quote.label.filter_by_word') }}..."--}}
                                    {{--                                               v-model="add_extension_words" @input="willFilterTextE">--}}
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
                                        {{-- <div class="subtitulo">{{trans('package.label.experiences')}}</div> --}}

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
                                        {{-- <div class="subtitulo">{{trans('package.label.destinations')}}</div> --}}
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
                                        {{-- <div class="subtitulo">{{trans('package.label.duration')}}</div> --}}
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
                                    <img v-if="extension.image_link" :src="extension.image_link"
                                         class="object-fit_cover"/>
                                    <img class="object-fit_cover"
                                         src="https://res.cloudinary.com/litomarketing/image/upload/c_scale,w_400/v1440093948/paragliding_in_Miraflores._123110314_dlyun3.jpg"
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
                                        style="width: 250px;">{{ trans('quote.label.add') }}
                                </button>
                                <button class="btn btn-primary" @click="replaceExtension"
                                        v-if="extension_replace != null && type_class_id != null">{{ trans('quote.label.replace') }}
                                </button>
                                {{--                                <button class="btn btn-danger" @click="closeModalExtension">@{{ extension_type_class_replace }} / @{{ extension_selected }} - @{{ type_class_id }}</button>--}}
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{--        End modal de Extensiones      --}}

    {{--          modal-edit-plan-rooms          --}}
    <b-modal class="modal fade modal_cotizar" id="modal-edit-plan-rooms" ref="modal-edit-plan-rooms" size="lg"
             aria-hidden="true" :no-close-on-backdrop="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div>
                    <div class="mb-2">
                        <h3><i class="icon-grid mr-2"></i> @{{ title_rates_hotel }}</h3>
                    </div>
                    <hr>

                    <div v-if="message_edit_plan_rooms!=''" class="alert alert-warning">
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
                                <strong>{{ trans('quote.label.description') }}: </strong>@{{ room.room_type }}
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
                                                                    <b>@{{ rate.political.rate.name }}</b>
                                                                </p>
                                                                <p>
                                                                    Check-in: @{{ hotelSwapRates.checkIn }} Check
                                                                    out : @{{ hotelSwapRates.checkOut }}
                                                                </p>
                                                                <p>
                                                                    <b>{{ trans('hotel.label.political_children') }}</b>
                                                                </p>
                                                                <p v-if="hotelSwapRates.political_children.child.allows_child == 1">
                                                                    <b>{{ trans('hotel.label.children') }}</b>
                                                                    @{{
                                                                    hotelSwapRates.political_children.child.min_age_child
                                                                    }} {{ trans('hotel.label.years') }} {{ trans('hotel.label.to') }}
                                                                    @{{
                                                                    hotelSwapRates.political_children.child.max_age_child
                                                                    }} {{ trans('hotel.label.years') }} @{{
                                                                    rate.political.no_show_apply.political_child
                                                                    }}
                                                                </p>
                                                                <p v-if="hotelSwapRates.political_children.child.allows_teenagers == 1">
                                                                    <b>{{ trans('hotel.label.infants') }}</b>
                                                                    @{{
                                                                    hotelSwapRates.political_children.infant.min_age_teenagers
                                                                    }} {{ trans('hotel.label.years') }} {{ trans('hotel.label.to') }}
                                                                    @{{
                                                                    hotelSwapRates.political_children.infant.max_age_teenagers
                                                                    }} {{ trans('hotel.label.years') }} @{{
                                                                    rate.political.no_show_apply.political_child
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
                                        <b-form-checkbox style="float: right;"
                                                         :disabled="loading || (url_hotel_choose === 'addFromHeader' && edit_checkboxs[ '_' + rate.rateId])"
                                                         :id="'edit_checkboxs_' + rkey + '_' + raKey"
                                                         :name="'edit_checkboxs_' + rkey + '_' + raKey"
                                                         v-model="edit_checkboxs[ '_' + rate.rateId]"
                                                         @change="chooseEditRoom( rate.rateId )">
                                        </b-form-checkbox>
                                        {{--                                            <a data-toggle="modal" href="#modal_politicas_cancelacion"--}}
                                        {{--                                               @click="setCancellationPolicies(rate.political.rate.message,rate.political.cancellation.name)">Ver--}}
                                        {{--                                                Politicas de Cancelacion</a>--}}
                                        <br>
                                        (@{{ rate.rateProvider }} - <?php if(Auth::user()->user_type_id == 3): ?>@{{
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
                                        <strong>$ <span v-if="rate.rate[0].amount_days[0]"> @{{ rate.rate[0].amount_days[0].total_amount }} </span></strong>
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
    {{--        End modal-edit-plan-rooms        --}}

    {{--        modal Notes        --}}
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
    {{--      End modal Notes       --}}

    {{--        modal Real Notes        --}}
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
    {{--      End modal Notes       --}}

    {{--        modal Guardar Como         --}}
    <b-modal class="modal fade" id="modal_guardar_como" ref="modal_guardar_como" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div>
                    <div class="mb-2">
                        <h3>{{ trans('quote.label.new_name_of_quote') }}</h3>
                    </div>
                    <hr>
                    <div class="mt-3">
                        <label for="new_name_quote">{{ trans('quote.label.enter_the_new_name') }}:</label>
                        <input type="text" class="form-control" v-model="new_name_quote"/>
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
    {{--      End modal Guardar Como       --}}


    {{--        modal de Reserva       --}}
    <b-modal class="modal-central modal-content" size="lg" id="modal_reservation" aria-hidden="true"
             ref="modal_reservation" :no-close-on-backdrop="true" :no-close-on-esc="true" :hide-header-close="true">
        <div class="text-center mt-5">
            <h4 class="mb-5 size_title">
                <strong class="text-dark">BOOKING DETAILS</strong>
            </h4>
            <div>
                <label for="file_reference" class="col size_title mb-4 color-inf">
                FILE: 350020
                </label>

                <div class="mb-5">
                    <label for="orderNumber" class="col m-0 text-dark">Número de pedido:
                        <span class="ml-2 color-inf">11355</span>
                    </label>
                </div>
            </div>

            <div>
                <label for="file_code" class="col-12 color-inf">
                    <i class="fas fa-user-tag"></i> Nombre de pax:
                    <span class="text-dark">Brooks x2</span>
                </label>
            </div>

            <div>
                <label for="file_code" class="col-12 py-3 color-inf">
                    <i class="fas fa-user-friends"></i> Número de pax:
                    <span class="text-dark">2 adultos</span>
                </label>
            </div>

            <div>
                <label for="file_reference" class="col-12 mb-5 color-inf">
                    <i class="fas fa-user-cog"></i> Tipo de servicio:
                    <span class="text-dark">Privado</span>
                </label>
            </div>
        </div>

        <div class="mb-2">
            <div class="row c-dark rounded justify-content-center mb-5 py-5 ml-0">
                <div>
                    <label class="col-12 m-0 color-inf">
                        Cancelación sin penalidad:
                        <span class="text-dark">15 de julio 2023</span>
                    </label>
                </div>
            </div>

            <div class="row px-4 my-3">
                <div class="wrapper ml-4">
                    <input type="checkbox" id="check" class="col-1" />
                    <label for="check" class="col m-0 ml-2 p-0 text-dark2 size_paragraph">
                        Enviar recordatorio vía email:
                    </label>
                </div>
                <div class="col-5 p-0">
                    <i class="text-dark3 fas fa-minus-circle"></i>
                    <input type="number" min="1" max="100" value="1" class="col-3 p-0 text-center"/>
                    <i class="text-dark2 fas fa-plus-circle"></i>
                    <span class="size_paragraph">días antes</span>
                </div>
                <div class="wrapper ml-4">
                    <input type="checkbox" id="check-mail" disabled class="col-1" />
                    <label for="check-mail" class="col m-0 ml-2 p-0 text-dark3 size_paragraph">
                        predeterminado@lito.com
                    </label>
                </div>
                    <input type="checkbox"/>
                    <input type="email" class="col" />
                </div>
                <div>
                    <span>Por favor reconfirma tu reserva:
                        <a href="">
                            <u class="blue-link">Datos de pax</u>
                        </a>
                    </span>
                </div>
            </div>

            <div class="row mx-0 mb-4 rounded warning align-items-start">
                <i class="col-1 mt-4 fas fa-exclamation-triangle"></i>
                <div class="col justify-content-center py-4 ml-0">
                    <label class="m-0">
                        Si no recibiste la confirmación de tu reserva, puedes contactarte con el área de soport técnico con el código:
                        <span>IT-5262E7</span>
                    </label>
                    <i class="col-1 mt-4 mr-2 text-dark3 fas fa-times"></i>
                </div>
            </div>
        </div>
    </b-modal>
    {{--      End modal de Reserva     --}}


    {{--      End modal Editar       --}}
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
                            <strong>{{ trans('quote.label.edit_service') }}</strong></h3>
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
                            <strong>{{ trans('quote.label.details_service') }}</strong></h3>
                        <center>...</center>
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{--        modal Cerrar         --}}
    <b-modal class="modalfade modal_cotizar" id="modal_close" aria-hidden="true" ref="modal_close"
             :no-close-on-backdrop="true" :no-close-on-esc="true">
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
    {{--      End modal Cerrar       --}}
    {{--        modal Cotizar         --}}
    <b-modal class="modal fade modal_cotizar" id="modal_cotizar" ref="modal_cotizar" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div>
                    <h4>@{{ translations.label.services_hotels_on_request }}</h4>
                    <div v-for="qCateg in quote_open.categories">
                        <ul v-for="(service,index_service) in qCateg.services" v-if="qCateg.id ==categoryActive.id">
                            <li v-if="service.on_request == 1 && service.type=='service' && service.locked == false">
                                - @{{ service.service.service_translations[0].name }}
                            </li>
                            <li v-if="service.on_request == 1 && service.type=='hotel' && service.locked == false">
                                - @{{ service.hotel.name }}
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
    {{--      End modal Cotizar       --}}
    {{--        modal Politicas de cancelacion         --}}
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
    {{--      End modal Politicas de cancelacion       --}}
    {{--        modal Realizar         --}}
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

    {{--        end Realizar         --}}
    {{--        modal Reservar         --}}
    <b-modal ref="modal_reserve" hide-footer centered size="md" class="modal-central modal-content"
             id="modal_reserve" :no-close-on-backdrop="true" :no-close-on-esc="true" :hide-header-close="true">
        <div class="reservation">
            <b-tabs>
                <b-tab active v-if="file.file_code">
                    <template #title>
                        Pre - Booking Details
                        <!-- {{trans('reservations.label.file_information')}} -->
                    </template>
                    <div class="col-md-12 mt-5 text-center text-dark">
                        <div class="row color-inf">
                            <label for="file_code" class="col-12 col-form-label size_title">
                                FILE
                                <strong>@{{ file.file_code }}</strong>
                            </label>
                        </div>
                        <div class="row">
                            <label for="file_code" class="col-12">
                                {{trans('quote.label.client')}}:
                                <strong class="color-inf">
                                    @{{ file.client.code }} - @{{ file.client.name }}
                                </strong>
                            </label>
                        </div>
                        <div class="row">
                            <label for="file_reference" class="col-12">
                                Order number:
                                <strong class="color-inf">11355</strong>
                                <!-- {{trans('package.label.file_reference')}}  # <strong>@{{ file.file_reference
                                    }}</strong> -->
                            </label>
                        </div>
                        <div class="row">
                            <label for="file_code" class="col-12 mt-5 color-inf">
                                <i class="fas fa-user-tag"></i> Nombre de pax:
                                <span class="text-dark">Brooks x2</span>
                            </label>
                        </div>
                        <div class="row">
                            <label for="file_code" class="col-12  color-inf">
                                <i class="fas fa-user-friends"></i> Número de pax:
                                <span class="text-dark">
                                    @{{ quantity_persons.adults}} {{ trans('quote.label.adults') }}
                                    @{{ quantity_persons.child }} {{ trans('quote.label.child') }}
                                </span>
                            </label>
                        </div>
                        <div class="row">
                            <label for="file_reference" class="col-12  color-inf">
                                <i class="fas fa-user-cog"></i> Tipo de servicio:
                                <span class="text-dark">@{{ getServiceType() }}</span>
                            </label>
                        </div>
                    </div>
                </b-tab>
                <b-tab>
                    <template #title>
                        PRE-BOOKING DETAILS
                    </template>
                    <div class="text-center mt-5">
                        <h4 class="mb-5 size_title">
                            <strong class="text-dark">PRE-BOOKING DETAILS</strong>
                        </h4>
                        <div>
                            <label for="file_reference" class="col-12 size_title mb-5 text-dark2">
                                COTIZACIÓN: <span class="color-inf">@{{ quote_open.id_original }}</span>
                            </label>
                            <div class="row mb-5">
                                <label for="orderNumber" class="col-6 m-0 text-dark">Número de pedido:</label>
                                <input type="text" name="orderNumber" placeholder="12345" class="col-6 form-control" />
                            </div>
                        </div>
                        <span>Por favor revise que la información es correcta para completar la reserva:</span>
                        <div class="mb-2">
                            <div class="row c-dark rounded justify-content-center my-4 py-5 ml-0">
                                <div class="border-right">
                                    <div class="mb-2">
                                        <label class="col-12 color-inf">
                                            <i class="fas fa-plane-arrival"></i> Fecha de llegada:
                                        </label>
                                    </div>
                                    <span class="text-dark">
                                        @{{ getDateIn() }}
                                    </span>
                                </div>
                                <div>
                                    <div class="mb-2">
                                        <label class="col-12 color-inf">
                                            <i class="fas fa-plane-departure"></i> Fecha de salida:
                                        </label>
                                    </div>
                                    <span class="text-dark">
                                        @{{ getDateOut() }}
                                    </span>
                                </div>
                                <div class="mt-4 row">
                                    <label class="col-12 color-inf">
                                        <div class="border-top pt-4 px-3">
                                            <i class="fas fa-bed"></i> Estadía total:
                                            <span class="text-dark mt-3">@{{ quote_open.nights }} noches</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <label for="file_code" class="col-12 color-inf mt-2">
                                <i class="fas fa-user-friends"></i> Número de pax:
                                <span class="text-dark">
                                    @{{ quantity_persons.adults}} {{ trans('quote.label.adults') }}
                                    @{{ quantity_persons.child }} {{ trans('quote.label.child') }}
                                </span>
                            </label>
                        </div>

                        <div class="row">
                            <label for="file_code" class="col-12 py-3 color-inf">
                                <i class="fas fa-building"></i> Acomodo:
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
                                <i class="fas fa-user-cog"></i> Tipo de servicios:
                                <span class="text-dark">@{{ getServiceType() }}</span>
                            </label>
                        </div>
                    </div>
                    <div>
                        <div class="c-dark rounded justify-content-center mt-4 mb-5 py-4">
                            <div>
                                <label class="col-5 p-0 m-0 text-dark">
                                Monto estimado:
                                </label>
                                <strong class="color-inf">
                                    <template v-if="statements.total > 0">$ @{{ statements.total | roundLito }}</template>
                                    <template v-else>...</template>
                                </strong>
                            </div>
                        </div>
                    </div>
                    <!-- <template #title>
                        @{{ translations.label.services_hotels_on_request }}
                    </template>
                    <div class="text-center mt-5">
                        <h4>@{{ translations.label.services_hotels_on_request }}</h4>
                        <div v-for="qCateg in quote_open.categories">
                            <ul v-for="(service,index_service) in qCateg.services"
                                v-if="qCateg.id ==categoryActive.id">
                                <template v-if="service.optional !== 1">
                                    <li v-if="service.on_request == 1 && service.type=='service'">
                                        @{{ service.service.service_translations[0].name }}
                                    </li>
                                    <li v-if="service.on_request == 1 && service.type=='hotel'">
                                        @{{ service.hotel.name }}
                                    </li>
                                </template>
                            </ul>
                        </div>

                        <div v-for="qCateg in quote_open.categories" v-if="qCateg.id ==categoryActive.id">
                            <h4 class="">{{ trans('quote.label.optionals') }}</h4>
                            <div>
                                <ul>
                                    <li v-for="(service, s) in qCateg.services" v-if="service.optional === 1"
                                        style="text-align:left;">
                                        <input type="checkbox" v-model="services_optionals[s]" value="1"/>
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
                                    @{{ service.service.service_translations[0].name }}
                                </span>
                                        <span class="id mr-3"
                                        v-if="service.type =='hotel' && service.hotel.channel.length>0">
                                    @{{ service.hotel.name }}
                                </span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div> -->
                </b-tab>
            </b-tabs>
        </div>
        <div class="row m-0">
            <div class="col-md-5 col-md-5 p-0 mr-5 ml-4">
                <button @click="hideModalReserve()" style="height: 52px !important;"
                    class="btn btn-cancelar mt-4 mb-2"
                    :disabled="loading_reserve">{{trans('global.label.cancel')}}</button>
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

    {{--        end Realizar         --}}
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

    {{--        modal paquete id      --}}
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

    </div>

@endsection
@section('css')
    <style>
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

        .blue-info {
            cursor: pointer;
            color: #1b70a1;
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

        #_overlay {
            position: fixed; /* Sit on top of the page content */
            width: 100%; /* Full width (cover the whole page) */
            height: 100%; /* Full height (cover the whole page) */
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5); /* Black background with opacity */
            z-index: 2; /* Specify a stack order in case you're using a different order for other elements */
            cursor: pointer; /* Add a pointer on hover */
        }

        .bootstrap-datetimepicker-widget table td.active, .bootstrap-datetimepicker-widget table td.active:hover {
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

        .check_true, .check_false, .check_undefined {
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
            width: 8% !important;
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

        .back_warning, .back_warning, .back_warning select {
            background-color: #fff3de !important;
        }

        .back_warning_icon, .back_warning_icon input, .back_warning_icon select,
        .back_warning_amount, .back_warning_amount input {
            background-color: #ffe2d1 !important;
        }

        .back_warning_icon .ico-error, .back_warning_icon .alert-accomodation {
            display: block;
        }

        .back_warning_icon .a-plan-room,
        .back_warning_amount .a-plan-room, .back_warning_amount .producto-precio--num, .back_warning_amount .select-pax {
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

        .inputs-ocupation {
            background-color: none !important;
            text-align: center !important;
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


    </style>
@endsection
@section('js')
    <script>

        let id = 3
        new Vue({
            el: '#app',
            data: {
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
                ranges: [
                    {
                        from: 1,
                        to: 1,
                        simple: 0,
                        double: 0,
                        triple: 0
                    }
                ],
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
                items: [
                    { name: 'Lima, Paracas y Nazca', imageNum: 'foto0', tag: '' },
                    { name: 'Cusco', imageNum: 'foto1', tag: '' },
                    { name: 'Arequipa', imageNum: 'foto2', tag: '' },
                    { name: 'Lima', imageNum: 'foto3', tag: '' },
                    { name: 'Puno', imageNum: 'foto4', tag: '' },
                    { name: 'Burma Superstar', imageNum: 'foto5', tag: '' },
                    { name: 'Salt and Straw', imageNum: 'foto6', tag: '' },
                    { name: 'Milano', imageNum: 'foto7', tag: '' },
                    { name: 'Tsing Tao', imageNum: 'foto8', tag: '' },
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
                permissions: {
                    converttopackage: false,
                    adddiscount: false,
                    updatemarkup: false
                },
                markup: 0,
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
                filter_by_nights: [
                    {
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
                select_itinerary_with_cover: '0000',
                select_itinerary_with_client_logo: '0000',
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
                has_file: false,
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
                url_hotel_choose: 'replace'
            },

            created: function () {
                this.user_type_id = localStorage.getItem('user_type_id')
                this.getPackagesSelected()
                this.getUser()
            },
            mounted () {
                this.$root.$emit('loadingPage', { typeBack: 2 })
                // let me = this
                // this.timeoutSaveQuote = setInterval(function(){ me.saveQuote() }, 300000);
                this.setTranslations()

                this.$root.$on('changeMarkup', (payload) => {
                    this.putMarkup()
                })

                this.loading = true
                this.blockPage = true

                this.getCategories()
                this.getServiceCategories()
                this.getServicesTypes()
                this.searchQuoteOpen('')

                // Permissions
                axios.get(baseURL + 'quotes/permissions')
                    .then(response => {
                        this.permissions = response.data
                        if (!(this.permissions.adddiscount)) {
                            this.use_discount = false
                            this.discount = 0
                        }

                    }).catch(error => {
                    console.log(error)
                })

                // Destinations Hotel
                axios.get('services/hotels/quotes/destinations?lang=' + localStorage.getItem('lang'))
                    .then(response => {

                        this.unzip_destination_hotels(response.data)

                    }).catch(error => {
                    console.log(error)
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
                    console.log(error)
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
                    console.log(error)
                })

            },
            destroyed: function () {
                clearInterval(this.timeoutSaveQuote)
            },
            computed: {
                atEndOfList () {
                    return this.currentOffset <= (this.paginationFactor * -1) * (this.items.length - this.windowSize)
                },
                atHeadOfList () {
                    return this.currentOffset === 0
                },
                filterOrdersPend () {
                    return this.ordersPend.filter(order => {
                        return order.NOMPAX.toLowerCase().includes(this.query_ordersPend.toLowerCase())
                    })
                }
            },
            methods: {
                grouped_toggle (service) {
                    this.quote_open.categories.forEach(c => {
                        c.services.forEach(s => {
                            if (service.grouped_code === s.grouped_code) {
                                s.grouped_show = !(service.grouped_show)
                            }
                        })
                    })
                },
                change_hour_in (quote_service) {

                    let data = {
                        hour_in: quote_service.hour_in,
                    }
                    axios.put('api/quote/services/' + quote_service.id + '/hour_in', data).then(response => {
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
                        console.log(error)
                    })
                },
                change_schedule (quote_service, schedule_index) {

                    let id_parent_ = quote_service.service.schedules[schedule_index][0].id_parent
                    if (id_parent_ === quote_service.schedule_id) {
                        console.log('ningún cambio por hacer')
                        return
                    }
                    if (this.block_change_schedule) {
                        console.log('ya en ejecución, espere por favor')
                        return
                    }
                    this.block_change_schedule = true

                    let data = {
                        schedule_id: id_parent_,
                    }
                    axios.put('api/quote/services/' + quote_service.id + '/schedule', data).then(response => {
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
                        console.log(error)
                        this.block_change_schedule = false
                    })

                },
                getPromotionsData: function (promotions_data, option) {
                    var date_from = this.date_from_promotion
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
                showDetailsFile: function () {
                    this.show_details_file = !this.show_details_file
                },
                validModal: function () {
                    setTimeout(function () {
                        $('body').addClass('modal-open')
                    }, 10)
                },
                closeOthersPopovers: function () {
                    this.$root.$emit('bv::hide::popover')
                },
                update_all_ranges () {

                    this.loading = true

                    this.ranges.forEach((r) => {
                        this.updateRange(r)
                    })

                    this.loading = false

                    this.$toast.success(this.translations.messages.saved_correctly, {
                        position: 'top-right'
                    })

                    // let el = document.getElementById('dropdown_rango')
                    // el.click()

                },
                unzip_destination_hotels (data) {
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
                change_hotel_destiny_cities () {
                    this.destinationsModalHotel_additional_select = []
                    this.destinationsModalHotel_select = []
                    this.destinationsModalHotel = ''
                    this.destinationsModalHotel_select_universe.forEach((d_u) => {
                        if (d_u.parent_code == this.destinationsModalHotel_country.code) {
                            this.destinationsModalHotel_select.push(d_u)
                        }
                    })
                },
                change_hotel_destiny_districts () {
                    this.destinationsModalHotel_additional_select = []
                    this.destinationsModalHotel_district = ''
                    this.destinationsModalHotel_additional_select_universe.forEach((o_u) => {
                        if (o_u.parent_code == this.destinationsModalHotel.code) {
                            this.destinationsModalHotel_additional_select.push(o_u)
                        }
                    })
                },
                unzip_origin_services (data) {
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
                change_service_origin_cities () {
                    this.originModalService_additional_select = []
                    this.originModalService_select = []
                    this.originModalService = ''
                    this.originModalService_select_universe.forEach((d_u) => {
                        if (d_u.parent_code == this.originModalService_country.code) {
                            this.originModalService_select.push(d_u)
                        }
                    })
                },
                change_service_origin_districts () {
                    this.originModalService_additional_select = []
                    this.originModalService_district = ''
                    this.originModalService_additional_select_universe.forEach((o_u) => {
                        if (o_u.parent_code == this.originModalService.code && o_u.code.trim() !== ',') {
                            this.originModalService_additional_select.push(o_u)
                        }
                    })
                },
                unzip_destiny_services (data) {
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
                change_service_destiny_cities () {
                    this.destinationsModalService_additional_select = []
                    this.destinationsModalService_select = []
                    this.destinationsModalService = ''
                    this.destinationsModalService_select_universe.forEach((d_u) => {
                        if (d_u.parent_code == this.destinationsModalService_country.code) {
                            this.destinationsModalService_select.push(d_u)
                        }
                    })
                },
                change_service_destiny_districts () {
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
                verify_itinerary_errors () {

                    let have_errors = 0

                    this.quote_open.categories.forEach(c => {
                        if (c.tabActive == 'active') {
                            c.services.forEach(s => {
                                console.log(s.validations.length, !s.locked, s.total_accommodations)
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
                    console.log(have_errors)
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
                verify_type_rooms (hotel) {
                    if (this.quote_open.operation === 'ranges') {
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
                show_occupation_modal () {
                    this.$refs['modal_occupation_hotel'].show()
                },
                show_hotel_modal () {
                    this.$refs['modal-hotel'].show()
                },
                setWithClientLogo: function (quote_open) {
                    this.quote_open.withClientLogo = false
                },
                setWithHeader: function (quote_open) {
                    if (this.quote_open.withClientLogo) {

                        this.quote_open.withHeader = false
                    } else {
                        this.quote_open.withHeader = true
                    }
                },
                updateOptional: function (service) {
                    this.loading = true
                    let data = {
                        quote_service_id: service.id,
                    }
                    axios.put('api/quote/optional', data).then(response => {
                        if (service.optional == 1) {
                            this.$set(service, 'optional', 0)
                        } else {
                            this.$set(service, 'optional', 1)
                        }
                        // this.searchQuoteOpen(this.categoryActive.id)
                        this.loading = false
                    }).catch(error => {
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right'
                        })
                        console.log(error)
                        this.loading = false
                    })
                },
                validateService: function (service) {
                    if (service.optional == 1) {
                        return 'background-color:#999999'
                    } else {
                        return ''
                    }
                    this.$forceUpdate()
                },
                updateAgeChild: function (age) {
                    this.loading = true
                    let data = {
                        age_child: age
                    }
                    axios.put('api/quote/age_child', data).then(response => {
                        this.searchQuoteOpen(this.categoryActive.id)
                        this.loading = false
                    }).catch(error => {
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right'
                        })
                        console.log(error)
                        this.loading = false
                    })
                },
                sendMailForPermission () {
                    this.loading = true
                    let data = {
                        user_code: this.discount_user_permission
                    }
                    axios.post('api/quote/' + this.quote_open.id + '/discountPermissionMail', data)
                        .then(response => {
                            console.log(response)
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
                        console.log(error)
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right'
                        })
                    })
                },
                willGoExport () {
                    if (this.use_discount && this.discount > 3) {
                        this.sendMailForPermission()
                    } else {
                        this.goExport()
                    }
                },
                goExport () {

                    if (this.verify_itinerary_errors()) {
                        this.$toast.warning(this.translations.label.observations_validation_text, {
                            position: 'top-right'
                        })
                        return
                    }

                    let link = this.getRouteExport()
                    let a = document.createElement('a')
                    a.target = '_blank'
                    a.href = link
                    a.click()
                },
                willRemoveService (category, service) {
                    this.$refs['modalWillRemoveService'].show()
                    this.similar_services = []

                    if (service.type !== 'group_header') {
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
                    }
                    console.log(this.similar_services)

                    this.serviceChoosen = service
                },
                changeWithCover (quote_open) {
                    let _toggle = (this.quote_open.withHeader) ? 'block' : 'none'
                    $('.showWithCover').css('display', _toggle)
                    if (!this.quote_open.withHeader) {
                        this.quote_open.withClientLogo = true
                        // this.$set(quote_open, 'withClientLogo', true);
                    } else {
                        // this.$set(quote_open, 'withClientLogo', false);
                        this.quote_open.withClientLogo = false
                    }
                    this.$forceUpdate()
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
                downloadSkeleton (quote) {

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
                        url: 'api/quote/' + quote.id + '/category/' + quote.radioCategories + '/skeleton?lang=' + this.language_for_download +
                            '&client_id=' + client_id + '&use_header=' + quote.withHeader + '&refPax=' + this.refPax,
                        responseType: 'blob',
                    })
                        .then((response) => {

                            console.log(response.data)

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
                downloadItinerary (quote) {

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
                        url: 'api/quote/' + quote.id + '/category/' + quote.radioCategories + '/itinerary?lang=' + this.language_for_download +
                            '&client_id=' + client_id + '&use_header=' + quote.withHeader + '&cover=' + this.select_itinerary_with_cover + '&refPax=' + this.refPax + '&client_logo=' + quote.withClientLogo + '&cover_client_logo=' + this.select_itinerary_with_client_logo,
                        responseType: 'blob',
                    })
                        .then((response) => {

                            // console.log(response.data)

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
                },
                backMiniMenu () {
                    $('.showDownloadSkeleton').css('display', 'none')
                    $('.showDownloadItinerary').css('display', 'none')
                    $('.miniMenu').css('display', 'block')
                },
                willDownloadSkeleton () {
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
                willDownloadItinerary () {
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
                    $('.showDownloadSkeleton').css('display', 'none')
                    $('.showDownloadItinerary').css('display', 'block')
                    $('.miniMenu').css('display', 'none')
                },
                setCancellationPolicies: function (general_policies, policies_cancellation) {
                    this.general_politics = general_policies
                    this.policies_cancellation = policies_cancellation
                },
                relaciOrderV2 () {

                    if (this.new_order_related > 0) {
                        let data = {
                            nropla_v2: this.quote_open.id,
                            lastNroped: this.quote_open.order_related,
                            lastNroord: this.quote_open.order_position,
                            nroped: this.new_order_related,
                            nroord: '',
                            mode: 1
                        }

                        axios.post(baseExternalURL + 'api/quote/order/relate', data)
                            .then((returns) => {
                                console.log(returns)

                                this.$root.$emit('updateMenu')
                                this.$root.$emit('reloadQuotes')
                                this.searchQuoteOpen(this.categoryActive.id)
                            })
                    } else {
                        this.$root.$emit('updateMenu')
                        this.$root.$emit('reloadQuotes')
                        this.searchQuoteOpen(this.categoryActive.id)
                    }
                },
                cleanHotelFilters () {
                    this.change_hotel_destiny_cities()
                    this.categoryModalHotel = 'all'
                    this.add_hotel_date = moment(this.quote_open.date_in).format('DD/MM/Y')
                    this.nightsModalHotel = 1
                    this.add_hotel_words = ''
                    this.moreHotels = []
                },
                cleanServiceFilters () {
                    this.change_service_destiny_cities()
                    this.change_service_origin_cities()
                    this.add_service_date = moment(this.quote_open.date_in).format('DD/MM/Y')
                    this.add_service_words = ''
                    this.categoryModalService = ''
                    this.modal_service_type_id = ''
                    this.moreServices = []
                },
                cleanExtensionFilters () {
                    this.add_extensions_date = moment(this.quote_open.date_in).format('DD/MM/Y')
                    this.service_type_id = ''
                    this.type_class_id = ''
                    this.add_extension_words = ''
                    this.extensions = []
                },
                hideModal () {
                    this.$refs['my-modal-confirm'].hide()
                },
                hideModalReserve: function () {
                    this.$refs['modal_reserve'].hide()
                },
                willCopyCategory () {
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
                    console.log(this.categoryActive)
                    console.log(this.quote_open)
                    // VALIDAR SI YA TIENE SERVICIOS
                    if (this.categoryActive.services.length == 0) {
                        this.copyCategory()
                    } else {
                        this.$refs['my-modal-confirm'].show()
                    }
                },
                copyCategory () {
                    this.loading = true
                    axios({
                        method: 'POST',
                        url: 'api/quote/categories/copy',
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
                openModalDetail: function (service_id, view, date_out, adult, child) {
                    let total_pax = parseInt(adult) + parseInt(child)
                    let _date = this.formatDate(date_out, '/', '-', 1)

                    axios.get('api/service/' + service_id + '/moreDetails?lang='
                        + localStorage.getItem('lang')
                        + '&date_out=' + _date
                        + '&total_pax=' + total_pax
                        + '&client_id=' + localStorage.getItem('client_id')
                    ).then(response => {
                        console.log(response.data)
                        this.view = view
                        this.service_detail_selected = response.data
                        $('#modal-detail-servicios').modal()
                    })

                },
                willFilterTextH () {
                    this.wordsH = this.add_hotel_words.split(',')
                },
                willFilterTextS () {
                    this.wordsS = this.add_service_words.split(',')
                },
                setTranslations () {
                    axios.get(baseURL + 'translation/' + localStorage.getItem('lang') + '/slug/quote').then((data) => {
                        this.translations = data.data
                    })
                },
                updateDiscount () {
                    let data = {
                        discount: (this.use_discount) ? this.discount : 0,
                        discount_detail: (this.use_discount) ? this.discount_detail : '',
                        discount_user_permission: (this.discount_user_permission) ? this.discount_user_permission : '',
                    }
                    axios.post('api/quote/' + this.quote_open.id + '/discount', data)
                        .then(response => {
                            console.log(response)
                            if (response.data.success) {
                                this.quote_open.discount = data.discount
                                this.quote_open.discount_detail = data.discount_detail
                                this.quote_open.discount_user_permission = data.discount_user_permission
                            }
                        }).catch(error => {
                        console.log(error)
                    })
                },
                putMarkup () {
                    //Todo si la cotizacion tiene un file ya no se puede actualizar el markup
                    if (!this.has_file) {
                        axios.get('api/markup/byClient/' + localStorage.getItem('client_id'))
                            .then(response => {
                                console.log(response)
                                if (response.data.success) {
                                    this.markup = parseFloat(response.data.data.hotel)
                                    this.updateMarkup(2)
                                }
                            }).catch(error => {
                            console.log(error)
                        })
                    }
                },
                chooseEditRoom (_rateId) {
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
                        rate_plan_rooms_choose: _rate_plan_rooms_choose
                    }
                    axios.post('api/quote/service/' + me.quote_service_id_choosed + '/rooms/' + me.url_hotel_choose, data)
                        .then((result) => {
                            this.searchQuoteOpen(this.categoryActive.id)
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
                        console.log(e)
                        me.loading = false
                    })

                    // }, 300)
                    this.closeModalPlanRooms()
                    this.searchQuoteOpen(this.categoryActive.id)

                },
                editPlanRooms (me) {
                    this.openModalPlanRooms()
                    let adult = 1
                    if (this.quote_open.operation === 'passengers') {
                        adult = me.adult
                    }
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
                        'quantity_persons_rooms': [{
                            'adults': adult,
                            'child': 0,
                            'ages_child': [
                                {
                                    'child': 1,
                                    'age': 0
                                }
                            ]
                        }],
                        'typeclass_id': me.hotel.typeclass_id,
                        'destiny': {
                            'code':
                                me.hotel.country.iso + ',' +
                                me.hotel.state.iso,
                            'label':
                                me.hotel.country.translations[0].value + ',' +
                                me.hotel.state.translations[0].value
                        },
                        'lang': localStorage.getItem('lang'),
                        'set_markup': (this.markup != '' || this.markup != null) ? this.markup : 0
                    }

                    // return
                    axios.post('services/hotels/available/quote', data)
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

                                this.edit_checkboxs = []
                                me.service_rooms.forEach(s_rooms => {
                                    this.edit_checkboxs['_' + s_rooms.rate_plan_room_id] = true
                                })

                                this.title_rates_hotel = this.translations.label.hotel_rates + ': [' + me.hotel.channel[0].code + '] - ' + me.hotel.name

                                if (me.amount && me.amount.error_in_nights) {
                                    this.message_edit_plan_rooms = this.translations.label.please_choose_different_rate_base_validate
                                }

                            } else {
                                this.$toast.error(result.data.data, {
                                    position: 'top-right'
                                })
                            }

                            this.loadingModal = false
                        }).catch((e) => {
                        console.log(e)
                    })
                },
                saveAndDiscardQuote () {
                    this.withDiscard = true
                    this.saveQuote()
                },
                convertToPackage () {
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
                        axios.post(baseExternalURL + 'api/quote/' + this.quote_open.id + '/convertToPackage', data)
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
                            console.log(e)
                            this.loading = false
                        })
                    }
                },
                getServicesTypes: function () {
                    axios.get(baseExternalURL + 'api/service_types/selectBox?lang=' + localStorage.getItem('lang'))
                        .then((result) => {
                            this.service_types = []
                            result.data.data.forEach(s_t => {
                                if (s_t.code != 'NA') {
                                    this.service_types.push(s_t)
                                }
                            })
                        }).catch((e) => {
                        console.log(e)
                    })
                },
                getRouteExport: function () {
                    let client_id = localStorage.getItem('client_id')
                    if (this.quote_open.operation === 'ranges') {
                        return baseExternalURL + 'quote/' + this.quote_open.id + '/export/ranges?lang=' + localStorage.getItem('lang') + '&client_id=' + client_id + '&user_id=' + localStorage.getItem('user_id') + '&user_type_id=' + localStorage.getItem('user_type_id')
                    }
                    if (this.quote_open.operation === 'passengers') {
                        return baseExternalURL + 'quote/' + this.quote_open.id + '/export/passengers?lang=' + localStorage.getItem('lang') + '&client_id=' + client_id + '&user_id=' + localStorage.getItem('user_id') + '&user_type_id=' + localStorage.getItem('user_type_id')
                    }
                },
                moveCarousel (direction) {
                    // Find a more elegant way to express the :style. consider using props to make it truly generic
                    if (direction === 1 && !this.atEndOfList) {
                        this.currentOffset -= this.paginationFactor
                    } else if (direction === -1 && !this.atHeadOfList) {
                        this.currentOffset += this.paginationFactor
                    }
                },
                getServiceCategories: function () {
                    axios.get('api/service_categories/selectBox?lang=' + localStorage.getItem('lang')).then(response => {
                        this.service_categories = response.data.data
                    }).catch(error => {
                        console.log(error)
                    })
                },
                getCategories: function () {
                    axios.get('api/typeclass/selectbox?lang=' + localStorage.getItem('lang') + '&type=2').then(response => {

                        this.categories = response.data.data

                        this.type_class_id = this.categories[0].id

                    }).catch(error => {
                        console.log(error)
                    })
                },
                toggleViewRates (rate) {
                    this.loading = true
                    rate.showAllRates = !(rate.showAllRates)
                    this.loading = false
                },
                showContentHotel (hotel) {
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
                showModalHotelPromotion (hotel) {
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

                    console.log(hotel)

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

                        /*
                        this.destinationsModalHotel_select.forEach(d => {
                            if (d.code == hotel.hotel.city.iso && d.parent_code == hotel.hotel.country.iso ) {
                                this.destinationsModalHotel = d
                                this.change_hotel_destiny_districts()
                            }
                        })

                        if(this.destinationsModalHotel == '')
                        {

                        }
                        */

                        this.destinationsModalHotel = {
                            code: hotel.hotel.country.iso + ',' + hotel.hotel.state.iso + ',' + hotel.hotel.city.id,
                            label: hotel.hotel.country.translations[0].value + ',' + hotel.hotel.state.translations[0].value + ',' + hotel.hotel.city.translations[0].value
                        }

                        console.log(this.destinationsModalHotel)

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
                showModalHotel (hotel) {
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

                        this.destinationsModalHotel_select.forEach(d => {
                            if ((d.code == hotel.hotel.state.iso || d.code == hotel.hotel.city.iso) && d.parent_code == hotel.hotel.country.iso) {
                                this.destinationsModalHotel = d
                                this.change_hotel_destiny_districts()
                            }
                        })

                        this.check_replace_hotel = true
                        this.searchHotels()
                        // AL MOMENTO DE GUARDAR QUE REMUEVA DICHO HOTEL
                    } else {
                        this.hotelForReplace = ''
                        this.check_replace_hotel = false
                        this.moreHotels = []
                        this.destinationsModalHotel = ''
                    }

                    this.loading = false
                },
                showModalExtension () {
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
                showModalFlight: function () {
                    // this.searchDestinations('', '')
                },
                resetDestinations: function () {
                    this.destinations_flights_destiny = []
                    this.destinations_flights_origin = []
                },
                searchDestinationsOrigin: function (search, loading) {
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
                            console.log(error)
                            this.loading = false
                        })
                },
                searchDestinationsDestiny: function (search, loading) {
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
                addFlight: function () {

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

                    console.log(data)

                    this.loading = true
                    axios.post('api/quote/' + this.quote_open.id + '/categories/flight', data).then((response) => {

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
                        console.log(error)
                        this.loading = false
                    })
                },
                toggleCategoryCheckAddExtension (me) {
                    this.loading = true
                    me.checkAddExtension = !(me.checkAddExtension)
                    this.loading = false
                },
                toggleCategoryCheckAddHotel (me) {
                    this.loading = true
                    me.checkAddHotel = !(me.checkAddHotel)
                    this.loading = false
                },
                toggleCategoryCheckAddService (me) {
                    this.loading = true
                    me.checkAddService = !(me.checkAddService)
                    this.loading = false
                },
                addService (me) {

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
                addHotel () {
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
                                        console.log(total_sgl + ' | ' + total_dbl + ' | ' + total_tpl)
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

                    this.moreHotels.forEach(h => {
                        let on_request = ''
                        let service_rate_ids = []

                        h.rooms.forEach(r => {
                            r.rates.forEach(r_p => {
                                if (this.checkboxs[h.id + '_' + r_p.rateId]) {
                                    on_request = r_p.onRequest
                                    service_rate_ids.push(r_p.rateId)
                                }
                            })
                        })

                        if (service_rate_ids.length > 0) {
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
                                on_request: (on_request == 1) ? 0 : 1,
                                adult: this.quantity_persons.adults,
                                child: this.quantity_persons.child,
                                client_id: localStorage.getItem('client_id'),
                                single: this.service_selected_general.single,
                                double: this.service_selected_general.double,
                                triple: this.service_selected_general.triple,
                                extension_parent_id: _extension_parent_id
                            }

                            this.saveService(data)
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
                saveService (data) {
                    this.loading = true
                    axios.post('api/quote/' + this.quote_open.id + '/categories/service', data).then(response => {
                        console.log(response)
                        if (response.data.success) {
                            this.$toast.success(this.translations.messages.successfully_added, {
                                position: 'top-right'
                            })
                            this.searchQuoteOpen(this.categoryActive.id, true)
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
                modal_services_setPage (page) {
                    if (page < 1 || page > this.modal_services_pages.length) {
                        return
                    }
                    this.modal_services_pageChosen = page
                    this.searchServices(false)
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
                searchServices (searchFisrt) {
                    this.loading = true
                    this.moreServices = []
                    if (searchFisrt) {
                        this.modal_services_pageChosen = 1
                    }
                    let adults = 2
                    let children = 0
                    if (this.operation === 'passengers') {
                        adults = this.quantity_persons.adults
                        children = this.quantity_persons.child
                    }
                    let data = {
                        date_from: this.formatDate(this.add_service_date, '/', '-', 1),
                        service_name: this.add_service_words,
                        allWords: (this.allWordsS) ? 1 : 0,
                        origin: this.get_data_service_origin(),
                        destiny: this.get_data_service_destiny(),
                        service_type: this.modal_service_type_id,
                        service_category: this.categoryModalService,
                        limit: this.modal_services_limit,
                        page: this.modal_services_pageChosen,
                        lang: localStorage.getItem('lang'),
                        client_id: localStorage.getItem('client_id'),
                        adults: adults,
                        children: children,
                    }

                    console.log(this.markup)

                    axios.post('api/services/search', data).then(response => {

                        response.data.data.forEach((data) => {
                            data.service_rate.forEach((service_rate) => {
                                service_rate.service_rate_plans.forEach((service_rate_plan) => {
                                    service_rate_plan.price_adult_label =
                                        this.roundLito(parseFloat(service_rate_plan.price_adult_without_markup) * (1 + (this.markup / 100)))
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
                        console.log(error)
                        this.loading = false
                    })
                },

                get_data_service_origin () {
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
                get_data_service_destiny () {
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
                searchHotels (_promotion) {

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
                            'quantity_persons_rooms': [
                                {
                                    adults: 1,
                                    ages_child: [],
                                    child: 0
                                }
                            ],
                            'quantity_rooms': 1,
                            'set_markup': (this.markup != '' || this.markup != null) ? this.markup : 0
                        }
                    } else {
                        data = {
                            'date_from': this.formatDate(this.add_hotel_date, '/', '-', 1),
                            'date_to': moment(_date_to).format('YYYY-MM-DD'),
                            'client_id': localStorage.getItem('client_id'),
                            'quantity_persons_rooms': [
                                {
                                    adults: 1,
                                    ages_child: [],
                                    child: 0
                                }
                            ],
                            'quantity_rooms': 1,
                            'set_markup': (this.markup != '' || this.markup != null) ? this.markup : 0
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

                    axios.post('services/hotels/available/quote', data).then(response => {
                        this.loadingHotel = false
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
                                        if (typeof (h.rooms[r].rates[r_p_r].showAllRates) === 'undefined') {
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
                        this.loadingHotel = false
                    })
                },
                get_data_hotel_destiny () {
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
                searchQuoteOpen (category_id, not_update) {
                    this.loading = true
                    axios.get('api/quote/byUserStatus/2?lang=' + localStorage.getItem('lang') + '&client_id=' + localStorage.getItem('client_id')).then(response => {
                        if (response.data.length > 0) {
                            this.quote_id = response.data[0].id
                            this.quote_open = response.data[0]
                            this.quote_open.withHeader = true
                            this.quote_open.withClientLogo = false
                            this.quote_open.radioCategories =
                                (this.quote_open.categories.length > 0)
                                    ? this.quote_open.categories[0].id : ''
                            if (this.quote_open.discount != null && this.quote_open.discount > 0) {
                                this.use_discount = true
                                this.discount = this.quote_open.discount
                                this.discount_detail = this.quote_open.discount_detail
                                this.discount_user_permission =
                                    (this.quote_open.discount_user_permission)
                                        ? this.quote_open.discount_user_permission
                                        : ''
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
                            }

                            this.operation = response.data[0].operation
                            this.new_order_related = response.data[0].order_related
                            if (this.new_order_related > 0) {
                                this.readonly = true
                            } else {
                                this.readonly = false
                            }
                            if (this.operation === 'ranges') {
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

                            if (not_update == undefined) {
                                this.add_hotel_date = moment(this.quote_open.date_in).format('DD/MM/Y')
                                this.add_service_date = moment(this.quote_open.date_in).format('DD/MM/Y')
                                this.add_extensions_date = moment(this.quote_open.date_in).format('DD/MM/Y')
                            }

                            this.activeTabCategory(category_id)

                            this.quote_service_type_id = this.quote_open.service_type_id

                            let occupation_done = false
                            this.categories_selected = []

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

                                this.categories.forEach(c => {
                                    if (c.id == _c.type_class_id) {
                                        c.checked = true
                                        this.categories_selected.push(c)
                                    }
                                })

                                // Validate Rates && Rooms
                                _c.services.forEach(_s => {

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
                                                _s.schedule_id = _s.service.schedules[0].id
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
                                        setTimeout(function () {
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
                            this.quote_open = []
                            this.operation = 'ranges'
                            this.ranges = [
                                {
                                    from: 1,
                                    to: 1,
                                    simple: 0,
                                    double: 0,
                                    triple: 0
                                }
                            ]
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
                        this.loading = false
                        this.blockPage = false
                        this.no_reload = false

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
                format_schedule_show: function (schedule_id, weekday_, schedules) {
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

                set_service_selected_generals () {

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
                async saveQuotePrev () {
                    let vm = this
                    const response = await vm.validateChilds()

                    if (response) {
                        this.saveQuote()
                    }
                },
                saveQuote: function () {

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
                        axios.post('api/quotes', data).then(response => {
                            this.quote_id = response.data.quote_open.id
                            this.quote_open = response.data.quote_open
                            this.ranges = response.data.quote_open.ranges
                            this.notes = response.data.quote_open.notes
                            this.$root.$emit('reloadQuotes')
                            this.$root.$emit('updateMenu')
                            this.searchQuoteOpen(this.quote_open.categories[0].id)
                            this.loading = false
                            this.quoteMeOnly(false)
                        }).catch(error => {
                            this.$toast.error(this.translations.messages.internal_error, {
                                position: 'top-right'
                            })
                            console.log(error)
                            this.loading = false
                        })
                    } else {
                        axios.put('api/quotes/' + this.quote_open.id, data).then(response => {
                            if (response.data.success) {
                                this.$toast.success(this.translations.messages.saved_correctly, {
                                    position: 'top-right'
                                })
                                this.relaciOrderV2()
                                this.quoteMeOnly(false)
                            } else {
                                this.$toast.error(this.translations.messages.internal_error, {
                                    position: 'top-right'
                                })
                            }
                            this.loading = false
                            if (this.withDiscard) {
                                this.withDiscard = false
                                this.discardChanges()
                                this.closeModalClose()
                            }
                        }).catch(error => {
                            this.$toast.error(this.translations.messages.internal_error, {
                                position: 'top-right'
                            })
                            console.log(error)
                            this.loading = false
                        })
                    }
                },
                getTodayFormat: function () {
                    ahora = new Date()

                    dia = ahora.getDate()
                    anoActual = ahora.getFullYear()
                    mesActual = ahora.getMonth() + 1
                    mesActual = (mesActual <= 9) ? '0' + mesActual : mesActual
                    diaActual = (dia <= 9) ? '0' + dia : dia
                    inicio = diaActual + '/' + mesActual + '/' + anoActual
                    return inicio
                },
                formatDate: function (_date, charFrom, charTo, orientation) {
                    _date = _date.split(charFrom)
                    _date =
                        (orientation)
                            ? _date[2] + charTo + _date[1] + charTo + _date[0]
                            : _date[0] + charTo + _date[1] + charTo + _date[2]
                    return _date
                },
                toggleAllCategories () {
                    this.categories.forEach(c => {
                        c.checked = this.checkedAllCategories
                    })
                    if (this.checkedAllCategories) {
                        this.categories_selected = this.categories
                    } else {
                        this.categories_selected = []
                    }
                },
                toggleCategory (cate) {
                    cate.checked = cate.checked

                    this.categories_selected = []
                    this.categories.forEach(c => {
                        if (c.checked) {
                            this.categories_selected.push(c)
                        }
                    })

                },
                toggleTabCategory (category) {
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
                activeTabCategory (category_id) {
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
                getPackagesSelected: function () {
                    axios.get(baseExternalURL + 'api/packages/selected')
                        .then((result) => {
                            this.package_selected = result.data
                        })
                },
                viewFormResponse: function (note) {
                    document.getElementById('ico').className = 'response-disable'
                    document.getElementById('res').className = 'response-disable'

                    Vue.set(note, 'create_response', true)

                },
                createRange: function () {

                    console.log(this.ranges)

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
                        axios.post('api/quote/ranges', {
                            quote_id: this.quote_id,
                            from: parseInt(to) + 1,
                            to: parseInt(to) + 1,
                        }).then(response => {
                            this.ranges[this.ranges.length - 1].id = response.data.range_id
                            console.log(this.ranges)
                        }).catch(error => {
                            this.$toast.error(this.translations.messages.internal_error, {
                                position: 'top-right'
                            })
                            console.log(error)
                        })
                    }
                },
                updateRange: function (range) {
                    if (range.id != null) {
                        axios.patch('api/quote/ranges/' + range.id, range).then(response => {

                        }).catch(error => {
                            this.$toast.error(this.translations.messages.internal_error, {
                                position: 'top-right'
                            })
                            console.log(error)
                        })
                    }
                },
                deleteRange: function (index_range) {

                    if (this.ranges[index_range].id !== null) {
                        axios.delete('api/quote/ranges/' + this.ranges[index_range].id).then(response => {

                        }).catch(error => {
                            this.$toast.error(this.translations.messages.internal_error, {
                                position: 'top-right'
                            })
                            console.log(error)
                        })
                    }
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
                        axios.post('api/quote/ranges', {
                            quote_id: this.quote_id,
                            from: 1,
                            to: 1,
                        }).then(response => {
                            Vue.set(this.ranges[0], 'id', response.data.range_id)

                        }).catch(error => {
                            this.$toast.error(this.translations.messages.internal_error, {
                                position: 'top-right'
                            })
                            console.log(error)
                        })
                    }
                },
                getUser: function () {
                    axios.get('api/user').then(response => {
                        this.user = response.data
                    }).catch(error => {
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right'
                        })
                        console.log(error)
                    })
                },
                createNote: function () {
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
                            axios.post('api/quote/notes', {
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
                                console.log(error)
                            })
                        }
                        this.note_comment = ''
                    }
                },
                editNote: function (note) {
                    if (note.id != null) {
                        if (note.comment != null && note.comment !== '') {
                            axios.patch('api/quote/notes/' + note.id, note).then(response => {
                                Vue.set(note, 'edit', false)
                            }).catch(error => {
                                this.$toast.error(this.translations.messages.internal_error, {
                                    position: 'top-right'
                                })
                                console.log(error)
                            })
                        }
                    } else {
                        Vue.set(note, 'edit', false)
                    }
                },
                showEditNote: function (note) {
                    Vue.set(note, 'edit', true)
                },
                createResponse: function (index_note) {
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
                            axios.post('api/quote/notes/responses', {
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
                                console.log(error)
                            })
                        }
                        this.note_response = ''
                        Vue.set(this.notes[index_note], 'create_response', false)
                    }
                },
                generatePassenger: function (update_people) {
                    console.log(this.quantity_persons)
                    console.log(this.passengers)
                    console.log(typeof this.passengers)
                    console.log(this.quote_id)

                    if (!(this.passengers.length > 0)) {
                        console.log('reseteo de paxs..')
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
                        this.updatePeople()
                    }
                },
                countPassengers: function (type) {
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
                saveOrUpdatePassengers: function () {
                    axios.post('api/quote/passengers', {
                        passengers: this.passengers,
                        quote_id: this.quote_id,
                    }).then(response => {
                        this.passengers = response.data
                    }).catch(error => {
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right'
                        })
                        console.log(error)
                    })
                },
                deletePassenger: function (passenger_id) {
                    axios.delete('api/quote/passengers/' + passenger_id).then(response => {
                    }).catch(error => {
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right'
                        })
                        console.log(error)
                    })
                },
                updatePeople: function (_update) {
                    this.loading = true
                    axios.put('api/quote/people', {
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
                        console.log(error)
                        this.loading = false
                    })
                },
                copyFirstPassengerData: function () {
                    axios.put('api/quote/copy_first_passenger_data', {
                        passenger: this.passengers[0],
                        quote_id: this.quote_id
                    }).then(response => {
                        this.passengers = response.data
                    }).catch(error => {
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right'
                        })
                        console.log(error)
                    })
                },
                openModalService: function (category, service) {

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

                    console.log(service)

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
                replaceService (me) {
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

                    axios.put('api/quote/replace/service', data).then(response => {
                        if (response.data.success) {
                            this.$toast.success(this.translations.messages.service_superseded, {
                                position: 'top-right'
                            })
                            this.searchQuoteOpen(this.categoryActive.id)
                        } else {
                            this.$toast.error(this.translations.messages.internal_error, {
                                position: 'top-right'
                            })
                            console.log(response.data)
                        }
                    }).catch(error => {
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right'
                        })
                        console.log(error)
                        this.loading = false
                    })
                },
                showCategories: function () {
                    this.editService = false
                },
                deleteService (service) {

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
                    axios.post('api/quotes/' + this.quote_id + '/services', { services: services_ })
                        .then(response => {
                            this.closeModalWillRemoveService()
                            this.searchQuoteOpen(this.categoryActive.id)
                            this.loading = false
                            this.$toast.success(this.translations.label.services_removed_successfully, {
                                position: 'top-right'
                            })
                        }).catch(error => {
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right'
                        })
                        console.log(error)
                        this.loading = false
                    })
                },
                deleteServiceConfirm: function (service) {
                    var r = confirm(this.translations.label.are_you_sure_you_to_delete)
                    if (r == true) {
                        this.deleteService(service)
                    }
                },
                addServiceFromCheck (service) {

                    this.services_deleted.push(service)
                    if (service.extension_id != null && service.extension_id != '') {
                        this.quote_open.categories.forEach(_c => {
                            if (_c.id == service.quote_category_id) {
                                _c.services.forEach(_s => {
                                    if (_s.parent_service_id == service.id) {
                                        console.log(_s)
                                        this.services_deleted.push(_s)
                                    }
                                })
                            }
                        })
                    }

                },
                addServiceDelete: function (service) {
                    if (service.type == 'group_header') {
                        if (this.groups_for_delete[service.grouped_code] === undefined) {
                            this.groups_for_delete[service.grouped_code] = true
                        } else {
                            this.groups_for_delete[service.grouped_code] = !this.groups_for_delete[service.grouped_code]
                        }

                        this.quote_open.categories.forEach(_c => {
                            if (_c.id == service.quote_category_id) {
                                _c.services.forEach(_s => {
                                    if (_s.grouped_code == service.grouped_code && _s.grouped_type == 'row') {
                                        if (this.groups_for_delete[service.grouped_code]) {
                                            this.services_deleted.push(_s)
                                        } else {
                                            for (let i = 0; i < this.services_deleted.length; i++) {
                                                if (this.services_deleted[i].id == _s.id) {
                                                    this.services_deleted.splice(i, 1)
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
                    }
                    console.log(this.services_deleted)
                },
                deleteServices: function () {
                    if (this.services_deleted.length > 0) {
                        axios.post('api/quotes/' + this.quote_id + '/services', { services: this.services_deleted })
                            .then(response => {
                                this.searchQuoteOpen(this.categoryActive.id)
                            }).catch(error => {
                            this.$toast.error(this.translations.messages.internal_error, {
                                position: 'top-right'
                            })
                            console.log(error)
                            this.loading = false
                        })
                    }
                },
                showDatePickerQuote: function () {
                    Vue.set(this.quote_open, 'disabled', false)
                },
                showDatePickerService: function (service) {
                    Vue.set(service, 'disabled', false)
                },
                updateDateInService: function (service, index_service) {
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
                        console.log(index_service)
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
                        axios.put('api/quote/update/date_in/services', {
                            index_service: index_service,
                            quote_service_ids: quote_service_ids,
                            date_in: this.formatDate(service.date_in, '/', '-', 1),
                            client_id: localStorage.getItem('client_id'),
                            quote_id: this.quote_id
                        }).then(response => {
                            this.no_reload = true
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
                updateDateInQuote: function () {
                    if (this.quote_open.hasOwnProperty('disabled')) {
                        if (this.quote_id != null) {
                            let r = confirm(this.translations.messages.confirm_rescheduled_dates)
                            if (r === true) {
                                this.loading = true
                                axios.put('api/quote/update/date_in', {
                                    lang: localStorage.getItem('lang'),
                                    quote_id: this.quote_id,
                                    date_in: this.formatDate(this.quote_date, '/', '-', 1)
                                }).then(response => {
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
                updateNameQuote: function () {
                    if (this.quote_id != null) {
                        axios.put('api/quote/update/name', {
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
                            console.log(error)
                            this.loading = false
                        })
                    }
                },
                discardChanges: function () {
                    this.force_fully_destroy_loading = true
                    axios.delete('api/quote/' + this.quote_id + '/forcefullyDestroy')
                        .then(response => {
                            this.force_fully_destroy_loading = false
                            if (response.data.success) {
                                this.categories.forEach(c => {
                                    c.checked = false
                                })
                                this.categories_selected = []
                                this.file.file_code = ''
                                this.file.file_reference = ''
                                this.has_file = false
                                this.searchQuoteOpen(this.categoryActive.id)
                                this.closeModalClose()
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
                        console.log(error)
                    })
                },
                createOrDeleteCategory: function (category) {
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
                            axios.post('api/quote/create_or_delete/category', data)
                                .then(response => {

                                    this.searchQuoteOpen(this.categoryActive.id)

                                })
                                .catch(error => {
                                    this.$toast.error(this.translations.messages.internal_error, {
                                        position: 'top-right'
                                    })
                                    console.log(error)
                                    this.loading = false
                                })
                        }
                    }
                },
                updatePassengerService: function (service) {
                    axios.post('api/quote/update/services/passengers', {
                        service_id: service.id,
                        adult: service.adult,
                        child: service.child,
                        client_id: localStorage.getItem('client_id'),
                        quote_id: this.quote_id
                    }).then(response => {
                        this.searchQuoteOpen(this.categoryActive.id)
                    }).catch(error => {
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right'
                        })
                        console.log(error)
                        this.loading = false
                    })

                },
                checkMoveService: function (services) {
                    if (!this.no_reload) {
                        axios.post('api/quote/update_order_and_date/services', {
                            services: services,
                            quote_id: this.quote_id,
                            client_id: localStorage.getItem('client_id')
                        }).then(response => {
                            this.searchQuoteOpen(this.categoryActive.id)
                            this.quoteMe()
                        }).catch(error => {
                            this.$toast.error(this.translations.messages.internal_error, {
                                position: 'top-right'
                            })
                            console.log(error)
                            this.loading = false
                        })
                    }
                },
                showSelectQuantityPassengersService: function (service) {
                    (service.showQuantityPassengers) ? Vue.set(service, 'showQuantityPassengers', false) : Vue.set(service, 'showQuantityPassengers', true)
                },
                close_modal_notes: function () {
                    this.service_notes = ''
                    document.getElementById('close-modal-notes').click()
                },
                close_modal_real_notes: function () {
                    this.service_real_notes = ''
                    document.getElementById('close-modal-real-notes').click()
                },
                closeModalSaveAs: function () {
                    this.new_name_quote = ''
                    this.$refs['modal_guardar_como'].hide()
                },
                closeModalPassengersService: function () {
                    document.getElementById('close_modal_passengers_service').click()
                },
                closeModalQuoteMe: function () {
                    this.$refs['modal_cotizar'].hide()
                },
                saveAsQuote: function () {
                    if (this.new_name_quote.trim() === '') {
                        this.$toast.error(this.translations.validations.rq_name_quote, {
                            position: 'top-right'
                        })
                        return
                    }
                    this.loading_save_as = true
                    axios.post('api/quote/save_as', {
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
                        } else {
                            console.log(response)
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
                selectServiceSelected: function (service) {
                    this.service_selected = service
                    this.checkExistsPassengerService()
                },
                willSavePassengerService (service, passenger) {
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
                savePassengerService: function () {
                    axios.post('api/quote/service/passenger', {
                        passengers: this.passengers_service.passengers,
                        service_id: this.service_selected.id,
                        quote_id: this.quote_id
                    }).then(response => {
                        this.service_selected = response.data.service
                        this.searchQuoteOpen(this.categoryActive.id)
                        this.passengers_service.service_id = null
                        this.passengers_service.passengers = []
                    }).catch(error => {
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right'
                        })
                    })
                },
                checkExistsPassengerService: function () {
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
                closeModalOccupationHotel: function () {
                    this.$refs['modal_occupation_hotel'].hide()
                },
                updateOccupationHotel: function () {
                    axios.put('api/quote/service/occupation_hotel', {
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
                        console.log(error)
                        this.loading = false
                    })
                },
                updateOccupationHotelGeneral: function () {
                    this.loading_occupation = true
                    if ((this.quantity_persons.adults + this.quantity_persons.child) > (this.control_service_selected_general.single +
                        (this.control_service_selected_general.double * 2) +
                        (this.control_service_selected_general.triple * 3) +
                        this.control_service_selected_general.double_child +
                        this.control_service_selected_general.triple_child)) {
                        this.$toast.error('la cantidad de pasajeros no debe de ser mayor a la ocupacion general generada', {
                            position: 'top-right'
                        })
                        this.loading_occupation = false
                        return true
                    }
                    axios.put('api/quote/service/occupation_hotel/general', {
                        simple: this.control_service_selected_general.single,
                        double: this.control_service_selected_general.double,
                        triple: this.control_service_selected_general.triple,
                        double_child: this.control_service_selected_general.double_child,
                        triple_child: this.control_service_selected_general.triple_child,
                        client_id: localStorage.getItem('client_id'),
                        quote_id: this.quote_id
                    }).then(response => {
                        this.loading_occupation = false
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

                        this.searchQuoteOpen(this.categoryActive.id)
                        this.$toast.success(response.data, {
                            position: 'top-right'
                        })
                        this.closeModalOccupationHotel()
                    }).catch(error => {
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right'
                        })
                        this.loading_occupation = false
                    })
                },
                setServiceHotelSelected: function (service) {
                    this.service_selected = service
                },
                updateNightsService: function (service) {

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

                    axios.put('api/quote/nights/service', {
                        quote_service_ids: quote_service_ids,
                        nights: service.nights,
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
                        console.log(error)
                        this.loading = false
                    })
                },
                validateAvailableHotels: function () {
                    this.blockPage = true
                    for (let i = 0; i < this.quote_open.categories.length; i++) {
                        for (let j = 0; j < this.quote_open.categories[i].services.length; j++) {
                            if (this.quote_open.categories[i].services[j].type === 'hotel' && !this.quote_open.categories[i].services[j].locked) {
                                let data = {}
                                if (this.quote_open.operation == 'passengers') {
                                    data = {
                                        'hotels_id': [this.quote_open.categories[i].services[j].object_id],
                                        'date_from': this.formatDate(this.quote_open.categories[i].services[j].date_in, '/', '-', 1),
                                        'date_to': this.formatDate(this.quote_open.categories[i].services[j].date_out, '/', '-', 1),
                                        'client_id': localStorage.getItem('client_id'),
                                        'quantity_rooms': 1,
                                        'quantity_persons_rooms': [{
                                            'adults': this.quote_open.categories[i].services[j].adult,
                                            'child': this.quote_open.categories[i].services[j].child,
                                            'ages_child': this.age_child
                                        }],
                                        'typeclass_id': this.quote_open.categories[i].services[j].hotel.typeclass_id,
                                        'destiny': {
                                            'code':
                                                this.quote_open.categories[i].services[j].hotel.country.iso + ',' +
                                                this.quote_open.categories[i].services[j].hotel.state.iso,
                                            'label':
                                                this.quote_open.categories[i].services[j].hotel.country.translations[0].value + ',' +
                                                this.quote_open.categories[i].services[j].hotel.state.translations[0].value
                                        },
                                        'set_markup': (this.markup != '' || this.markup != null) ? this.markup : 0

                                    }
                                } else {
                                    data = {
                                        'hotels_id': [this.quote_open.categories[i].services[j].object_id],
                                        'date_from': this.formatDate(this.quote_open.categories[i].services[j].date_in, '/', '-', 1),
                                        'date_to': this.formatDate(this.quote_open.categories[i].services[j].date_out, '/', '-', 1),
                                        'client_id': localStorage.getItem('client_id'),
                                        'quantity_rooms': 1,
                                        'quantity_persons_rooms': [{
                                            'adults': 1,
                                            'child': 0,
                                            'ages_child': this.age_child
                                        }],
                                        'typeclass_id': this.quote_open.categories[i].services[j].hotel.typeclass_id,
                                        'destiny': {
                                            'code':
                                                this.quote_open.categories[i].services[j].hotel.country.iso + ',' +
                                                this.quote_open.categories[i].services[j].hotel.state.iso,
                                            'label':
                                                this.quote_open.categories[i].services[j].hotel.country.translations[0].value + ',' +
                                                this.quote_open.categories[i].services[j].hotel.state.translations[0].value
                                        },
                                        'set_markup': (this.markup != '' || this.markup != null) ? this.markup : 0

                                    }
                                }

                                axios.post('services/hotels/available/quote', data)
                                    .then((result) => {
                                        // on_request = 1; ok = 0
                                        if (result.data.success) {
                                            if (result.data.data[0].city.hotels.length == 0) {
                                                this.$set(this.quote_open.categories[i].services[j], 'on_request', 1)
                                                let service = {
                                                    quote_service_id: this.quote_open.categories[i].services[j].id,
                                                    on_request: 1,
                                                }
                                                axios.post('api/quote/categories/update/on_request', service)
                                                    .then((result) => {
                                                        console.log(result.data)
                                                    })
                                            } else {
                                                let on_request_count = 0
                                                for (let k = 0; k < result.data.data[0].city.hotels[0].rooms.length; k++) {
                                                    for (let m = 0; m < result.data.data[0].city.hotels[0].rooms[k].rates.length; m++) {
                                                        for (let l = 0; l < this.quote_open.categories[i].services[j].service_rooms.length; l++) {
                                                            if (result.data.data[0].city.hotels[0].rooms[k].rates[m].rateId == this.quote_open.categories[i].services[j].service_rooms[l].rate_plan_room_id) {
                                                                if (result.data.data[0].city.hotels[0].rooms[k].occupation == 1 && this.quote_open.categories[i].services[j].single > 0) {
                                                                    if (result.data.data[0].city.hotels[0].rooms[k].rates[m].onRequest == 0) {
                                                                        on_request_count++
                                                                    }
                                                                }
                                                                if (result.data.data[0].city.hotels[0].rooms[k].occupation == 2 && this.quote_open.categories[i].services[j].double > 0) {
                                                                    if (result.data.data[0].city.hotels[0].rooms[k].rates[m].onRequest == 0) {
                                                                        on_request_count++
                                                                    }
                                                                }
                                                                if (result.data.data[0].city.hotels[0].rooms[k].occupation == 3 && this.quote_open.categories[i].services[j].triple > 0) {
                                                                    if (result.data.data[0].city.hotels[0].rooms[k].rates[m].onRequest == 0) {
                                                                        on_request_count++
                                                                    }
                                                                }
                                                            }
                                                        }

                                                    }
                                                }
                                                let service = {
                                                    quote_service_id: this.quote_open.categories[i].services[j].id,
                                                    on_request: (on_request_count > 0) ? 1 : 0,
                                                }
                                                axios.post('api/quote/categories/update/on_request', service)
                                                    .then((result) => {
                                                        console.log(result.data)
                                                    })
                                                // for (let k = 0; k < result.data.data[0].city.hotels[0].rooms.length; k++) {
                                                //     for (let m = 0; m < result.data.data[0].city.hotels[0].rooms[k].rates.length; m++) {
                                                //         for (let l = 0; l < this.quote_open.categories[i].services[j].service_rooms.length; l++) {
                                                //             if (result.data.data[0].city.hotels[0].rooms[k].rates[m].rateId == this.quote_open.categories[i].services[j].service_rooms[l].rate_plan_room_id) {
                                                //                 this.$set(this.quote_open.categories[i].services[j], 'on_request', (result.data.data[0].city.hotels[0].rooms[k].rates[m].onRequest == 1) ? 0 : 1)
                                                //                 let service = {
                                                //                     quote_service_id: this.quote_open.categories[i].services[j].id,
                                                //                     on_request: (result.data.data[0].city.hotels[0].rooms[k].rates[m].onRequest == 1) ? 0 : 1,
                                                //                 }
                                                //                 axios.post('api/quote/categories/update/on_request', service)
                                                //                     .then((result) => {
                                                //                         console.log(result.data)
                                                //                     })
                                                //
                                                //             }
                                                //         }
                                                //     }
                                                //
                                                // }
                                            }
                                        }
                                    })
                            }
                        }
                    }
                    this.blockPage = false
                },
                async validateChilds () {
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
                async quoteMePrev () {
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
                        console.log('Ejecutando cotización..')
                        this.quoteMe()
                    } else {
                        console.log('No se puede ejecutar el modal..')
                        setTimeout(function () {
                            vm.closeModalQuoteMe()
                        }, 1200)
                    }
                },
                quoteMe: function () {

                    if (this.verify_itinerary_errors()) {
                        this.$toast.warning(this.translations.label.observations_validation_text, {
                            position: 'top-right'
                        })
                        let xthis = this
                        setTimeout(function () {
                            xthis.closeModalQuoteMe()
                        }, 1200)
                        return
                    }

                    this.validateAvailableHotels()
                    this.saveQuote()
                    let error_rooms = 0

                    // this.quote_open.categories.forEach(c => {
                    //     if (c.tabActive == 'active') {
                    //         c.services.forEach(s => {
                    //             if (s.type == 'hotel' && !s.locked) {
                    //                 if ((s.single + s.double + s.triple) == 0) {
                    //                     error_rooms++
                    //                 }
                    //             }
                    //         })
                    //     }
                    // })

                    // if (error_rooms > 0) {
                    //     let xthis = this
                    //     setTimeout(function () {
                    //         xthis.closeModalQuoteMe()
                    //     }, 1200)
                    //     this.$toast.error(this.translations.messages.please_fill_the_rooms, {
                    //         position: 'top-right'
                    //     })
                    //     return
                    // }

                    this.blockPage = true
                    axios.put('api/quote/me', {
                        quote_id: this.quote_id,
                        client_id: localStorage.getItem('client_id'),
                        category_id: this.categoryActive.id

                    }).then(response => {
                        if (this.quote_open.operation === 'passengers') {
                            this.categoryPassengers = response.data.categories
                        }
                        if (this.quote_open.operation === 'ranges') {
                            this.categoryRanges = response.data.categories
                        }
                        this.searchQuoteOpen(this.categoryActive.id)
                        this.$toast.success(response.data.message, {
                            position: 'top-right'
                        })
                        this.blockPage = false
                    }).catch(error => {
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right'
                        })
                        console.log(error)
                        this.loading = false
                        this.blockPage = false
                    })
                },
                quoteMeOnly: function () {
                    if (this.verify_itinerary_errors()) {
                        this.$toast.warning(this.translations.label.observations_validation_text, {
                            position: 'top-right'
                        })
                        let xthis = this
                        setTimeout(function () {
                            xthis.closeModalQuoteMe()
                        }, 1200)
                        return
                    }
                    let error_rooms = 0

                    // this.quote_open.categories.forEach(c => {
                    //     if (c.tabActive == 'active') {
                    //         c.services.forEach(s => {
                    //             if (s.type == 'hotel' && !s.locked) {
                    //                 if ((s.single + s.double + s.triple) == 0) {
                    //                     error_rooms++
                    //                 }
                    //             }
                    //         })
                    //     }
                    // })
                    //
                    // if (error_rooms > 0) {
                    //     let xthis = this
                    //     setTimeout(function () {
                    //         xthis.closeModalQuoteMe()
                    //     }, 1200)
                    //     this.$toast.error(this.translations.messages.please_fill_the_rooms, {
                    //         position: 'top-right'
                    //     })
                    //     return
                    // }

                    this.blockPage = true
                    axios.put('api/quote/me', {
                        quote_id: this.quote_id,
                        client_id: localStorage.getItem('client_id'),
                        category_id: this.categoryActive.id

                    }).then(response => {
                        if (this.quote_open.operation === 'passengers') {
                            this.categoryPassengers = response.data.categories
                        }
                        if (this.quote_open.operation === 'ranges') {
                            this.categoryRanges = response.data.categories
                        }
                        this.searchQuoteOpen(this.categoryActive.id)
                        this.$toast.success(response.data.message, {
                            position: 'top-right'
                        })
                        this.blockPage = false
                    }).catch(error => {
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right'
                        })
                        console.log(error)
                        this.loading = false
                        this.blockPage = false
                    })
                },
                closeModalExtension: function () {
                    document.getElementById('close_modal_extension').click()
                },
                getExtensions: function () {
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
                    axios.post('api/quote/extensions', {
                        type_class_id: this.type_class_id,
                        date: this.formatDate(this.add_extensions_date, '/', '-', 1),
                        type_service: this.service_type_id,
                        lang: localStorage.getItem('lang'),
                        filter: this.add_extension_words
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
                filterByDestinyAll: function () {
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
                filterByDestiny: function (index_destiny) {
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
                filterDestiny: function (state_id) {
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
                getCategoryPackages: function () {
                    axios.get(baseExternalURL + 'api/tags/selectBox?lang=' + localStorage.getItem('lang'))
                        .then((result) => {
                            this.category_packages = result.data.data
                            this.generateFilterByCategory()
                            this.generateFilterByNights()
                        }).catch((e) => {
                        console.log(e)
                    })
                },

                unCheckAllNights: function () {
                    $('#all_itineraries').prop('checked', true)
                    for (let i = 0; i < this.filter_by_nights.length; i++) {
                        if (this.filter_by_nights[i].status) {
                            $('#all_itineraries').prop('checked', false)
                        }
                    }
                },
                filterByNightsAll: function () {
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
                filterByNights: function (index_nights) {
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
                            let packages_new_3 = packages_original.filter(function (package) {
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
                            let packages_new_4_6 = packages_original.filter(function (package) {
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
                            let packages_new_7_10 = packages_original.filter(function (package) {
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
                generateFilterByNights: function () {
                    let packages_original = this.packages_original
                    // this.filter_by_nights = []
                    if (this.extensions.length > 0) {
                        packages_original = this.extensions
                    }

                    let packages_news = []
                    for (let i = 0; i < this.filter_by_nights.length; i++) {

                        if (this.filter_by_nights[i].option == 1) {
                            let packages_new_3 = packages_original.filter(function (package) {

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
                            let packages_new_4_6 = packages_original.filter(function (package) {

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
                            let packages_new_7_10 = packages_original.filter(function (package) {

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
                generateFilterByCategory: function () {
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
                getDestinationsPackages: function () {
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
                removeDuplicates: function (originalArray, prop) {
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
                filterByCategory: function () {
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
                    console.log(check_status)
                    console.log(packages_new)
                    if (check_status) {
                        this.extensions = packages_new
                        this.packages_search_category = packages_new
                    } else {
                        this.extensions = this.packages_original
                        this.packages_search_category = []
                    }
                },
                unCheckAllDestiny: function () {
                    $('#all_destinations').prop('checked', true)
                    for (let i = 0; i < this.filter_by_destiny.length; i++) {
                        if (this.filter_by_destiny[i].status) {
                            $('#all_destinations').prop('checked', false)
                        }
                    }
                },
                filterByCategoryAll: function () {

                    for (let i = 0; i < this.filter_by_category.length; i++) {
                        this.filter_by_category[i].status = this.check_status_all
                    }
                    this.extensions = this.packages_original
                    this.packages_search_category = []
                    this.getDestinationsPackages()
                    this.generateFilterByNights()
                },
                checkCategoryId: function (category_id, package) {
                    if (package.tag_id === category_id) {
                        return true
                    }
                },
                checkDestinyId: function (state, package_destination) {
                    if (package_destination.state_id === state) {
                        return true
                    }
                },
                addExtension: function () {
                    if (this.quantity_persons.adults == 0) {
                        this.$toast.warning(this.translations.validations.rq_adults, {
                            position: 'top-right'
                        })
                        return
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
                        return
                    }
                    axios.post('api/quote/extension/add', {
                        extension_id: this.extension_selected,
                        service_type_id: this.service_type_id,
                        type_class_id: this.type_class_id,
                        quote_id: this.quote_id,
                        category_ids: _categories,
                        extension_date: this.formatDate(this.add_extensions_date, '/', '-', 1)
                    }).then(response => {
                        this.$toast.success(this.translations.messages.successfully_added, {
                            position: 'top-right'
                        })
                        this.closeModalExtension()
                        this.searchQuoteOpen(this.categoryActive.id)
                    }).catch(error => {
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right'
                        })
                        console.log(error)
                        this.loading = false
                    })
                },
                selectReplaceExtension: function (service) {
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
                    console.log(service)
                    this.getExtensions()
                },
                replaceExtension: function () {
                    let _categories = []
                    this.quote_open.categories.forEach(c => {
                        if (c.checkAddExtension) {
                            _categories.push(c.id)
                        }
                    })

                    if (_categories.length == 0) {
                        this.$toast.warning(this.translations.validations.category, {
                            position: 'top-right'
                        })
                        return
                    }
                    // if (this.extension_type_class_replace !== (this.extension_selected +'_'+ this.type_class_id)) {
                    axios.post('api/quote/extension/replace', {
                        quote_service_id: this.service_selected_id,
                        extension_replace: this.extension_replace,
                        extension_id: this.extension_selected,
                        service_type_id: this.service_type_id,
                        type_class_id: this.type_class_id,
                        quote_id: this.quote_id,
                        category_ids: _categories,
                        extension_date: this.formatDate(this.add_extensions_date, '/', '-', 1)
                    }).then(response => {
                        this.$toast.success(this.translations.extension_replaced_or_successfully_added, {
                            position: 'top-right'
                        })
                        this.searchQuoteOpen(this.categoryActive.id)
                    }).catch(error => {
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right'
                        })
                        console.log(error)
                        this.loading = false
                    })
                    // }

                },
                updateMarkup: function (option) {
                    this.loading = true
                    if (this.quote_id != null || this.quote_id != '') {
                        axios.put(window.a3BaseQuoteServerURL +   'api/update/quote/markup', {
                            quote_id: this.quote_id,
                            markup: this.markup,
                            client_id: localStorage.getItem('client_id'),
                            option: option,
                            user_id: localStorage.getItem('user_id'),
                            user_type_id: localStorage.getItem('user_type_id')

                        }).then((response) => {
                            this.$toast.success(this.translations.label.markup + ' ' + this.translations.messages.updated_successfully, {
                                position: 'top-right'
                            })
                            this.markup = response.data.markup

                            if (this.quote_id != null) {
                                this.updatePeople()
                            }

                            this.loading = false
                        })
                    }

                },
                async confirmReservePrev () {
                    const response = await this.validateChilds()

                    if (response) {
                        this.confirmReserve()
                    }
                },
                confirmReserve: function () {
                    if (this.verify_itinerary_errors()) {
                        this.$toast.warning(this.translations.label.observations_validation_text, {
                            position: 'top-right'
                        })
                        return
                    }

                    this.validateAvailableHotels()
                    this.$refs['modal_reserve'].show()
                },
                willReserveQuote: function () {
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
                reserveQuote: function () {
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
                    axios.put('api/quotes/' + this.quote_open.id, data_quote).then(response => {
                        if (response.data.success) {
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
                            axios.post(baseExternalURL + 'services/reservations/quote', data
                            ).then((result) => {
                                if (result.data.success) {
                                    result.data.response.entity = 'Quote'
                                    result.data.response.object_id = this.quote_open.id_original
                                    result.data.response.type_class_id = reservation_type_class_id
                                    //Todo Enviamos a reservar la cotizacion
                                    axios.post(baseExternalURL + 'services/hotels/reservation/add', result.data.response).then((result) => {
                                        this.clicks_send_booking = 0
                                        if (result.data.success) {
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

                                        this.loading = false
                                        this.loading_reserve = false
                                    }).catch((e) => {
                                        this.$toast.error('Error: ' + e, {
                                            position: 'top-right'
                                        })
                                        this.loading = false
                                        window.location.reload()
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
                verifyTieOrder () {
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
                searchOrdersByClient () {

                    this.ordersPend = []
                    this.loaderOrders = true

                    axios.get(baseURL + 'orders/client/' + localStorage.getItem('client_code'))
                        .then((returns) => {
                            this.ordersPend = returns.data.data
                            this.loaderOrders = false
                        }).catch(e => {
                        this.useOrdersShow = false
                        console.log(e)
                    })
                },
                storeRelaciOrder () {

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
                        console.log(returns)
                        this.loading = false
                    })
                },
                extensionSelected (extension_id, type_class_id) {
                    this.extension_selected = extension_id
                    this.type_class_id = type_class_id
                    console.log(this.type_class_id)
                    let radioCheck = extension_id + '_' + type_class_id
                    for (var i = 0; i < this.type_class_ids.length; i++) {
                        if (this.type_class_ids[i] !== radioCheck) {
                            $('#' + this.type_class_ids[i]).prop('checked', false)
                        }
                    }

                },
                openModalReservation: function () {
                    this.$refs['modal_reservation'].show()
                },
                closeModalReservation: function () {
                    this.$refs['modal_reservation'].hide()
                },

                modalPassengers: function (file, paxs, modal_) {
                    if (this.has_file) {
                        localStorage.setItem('save_quote_file', file)
                        this.$refs.modal_passengers.modalPassengers('file', this.file.file_code, paxs, this.quantity_persons.adults, this.quantity_persons.child, 0, modal_)
                    } else {
                        this.$refs.modal_passengers.modalPassengers('quote', file, paxs, this.quantity_persons.adults, this.quantity_persons.child, 0, modal_)
                    }
                },
                openModalReservationErrors: function () {
                    this.$refs['modal_reservation_errors'].show()
                },
                closeModalReservationErrors: function () {
                    this.$refs['modal_reservation_errors'].hide()
                },
                debounce (method, timer) {
                    if (this.$_debounceTimer !== null) {
                        clearTimeout(this.$_debounceTimer)
                    }
                    this.$_debounceTimer = setTimeout(() => {
                        method()
                    }, timer)
                },
                changeLocked: function (hiddenLocked) {
                    this.hiddenLocked = hiddenLocked
                },
                openModalClose: function () {
                    this.$refs['modal_close'].show()
                },
                closeModalClose: function () {
                    this.$refs['modal_close'].hide()
                },
                validateClientWithFile: function () {
                    let validate = false
                    if (this.has_file) {
                        if (this.file.client.id != localStorage.getItem('client_id')) {
                            validate = true
                        }
                    }
                    return validate
                },
                validateHasServices: function () {
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
                openModalSaveAs: function () {
                    this.$refs['modal_guardar_como'].show()
                },
                openModalPlanRooms: function () {
                    this.$refs['modal-edit-plan-rooms'].show()
                },
                closeModalPlanRooms: function () {
                    this.$refs['modal-edit-plan-rooms'].hide()
                },
                closeModalWillRemoveService: function () {
                    this.$refs['modalWillRemoveService'].hide()
                },
                openModalPackageCreated: function () {
                    this.$refs['modal_package_created'].show()
                },
                closeModalPackageCreated: function () {
                    this.package_create = {
                        id: null
                    }
                    this.$refs['modal_package_created'].hide()
                },
                openModalNotesHotel: function (service) {
                    console.log(service)
                    this.service_active = service
                    this.$refs['modal_create_notes_hotel'].show()
                },
                closeModalNotesHotel: function () {
                    this.$refs['modal_create_notes_hotel'].hide()
                },
                saveNoteHotel: function () {
                    let data_quote = {
                        hotel: this.service_active
                    }
                    //Todo guardamos la cotizacion
                    axios.put('api/quotes/update_notes/' + this.quote_open.id, data_quote)
                        .then(response => {
                            this.$toast.success(this.translations.messages.saved_correctly, {
                                position: 'top-right'
                            })
                            this.$refs['modal_create_notes_hotel'].hide()
                        })
                        .catch(response => {
                            console.log(response)
                        })
                },
                searchDetailsFile: function (quote_id) {
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

                                console.log(this.skeleton_file)
                                console.log(this.language_id)
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
                getServiceOptionals: function (service_optionals) {
                    let service = []
                    this.categoryActive.services.forEach(function (item_service, index) {
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
                deleteRoomHotel: function (service_room_id) {
                    this.loading = true
                    axios.delete('api/quote/service/rooms/' + service_room_id + '/rate_plan_room')
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
                        console.log(error)
                    })

                }
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
                formatDate: function (_date) {
                    if (_date == undefined) {
                        // console.log('fecha no parseada: ' + _date)
                        return
                    }
                    _date = _date.split('-')
                    _date = _date[2] + '/' + _date[1] + '/' + _date[0]
                    return _date
                },
                formattedDateNotes: function (date) {
                    return moment(date).format('MMM D, YYYY HH:mm')
                },

                formatPrice: function (price) {
                    return parseFloat(price).toFixed(2)
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
