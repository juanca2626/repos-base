@extends('layouts.app')
@section('content')
    <section class="page-services" v-if="this.client_id">
        <loading-component v-show="blockPage"></loading-component>
        <div class="container">
            <div class="motor-busqueda">
                <h2>{{ trans('service.reservation_of_service') }}</h2>
                <div class="form">

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            {{--                            <div v-if="user_type_id == 3">--}}
                            {{--                                <div class="form-check form-check-inline">--}}
                            {{--                                    <input type="radio" value="2020" class="form-check-input" v-model="service_year">--}}
                            {{--                                    <label class="form-check-label" for="uno">2020</label>--}}
                            {{--                                </div>--}}
                            {{--                                <div class="form-check form-check-inline">--}}
                            {{--                                    <input class="form-check-input" type="radio" value="2021" v-model="service_year">--}}
                            {{--                                    <label class="form-check-label" for="Dos">2021</label>--}}
                            {{--                                </div>--}}
                            {{--                                <button @click="getRouteExcelServiceYear()"--}}
                            {{--                                        class="btn btn-success">{{trans('global.label.download_excel')}}</button>--}}
                            {{--                            </div>--}}
                        </div>
                        <div class="col-md-6 mb-4 text-right">
                            <button class="btn btn-danger"
                                    @click="clearFilters">
                                <i class="fas fa-eraser"></i> {{trans('global.label.clear_filters')}}
                            </button>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group fecha">
                            <v-select :options="origins_countries_select" @input="change_origin_cities()"
                                      v-model="origin_country"
                                      placeholder="{{trans('service.label.select_origin')}}"
                                      class="form-control"></v-select>
                        </div>
                        <div class="form-group destino">
                            <v-select :options="origins_select" @input="change_origin_districts()"
                                      v-model="origin"
                                      placeholder="{{trans('service.label.select_origin')}}"
                                      class="form-control"></v-select>
                        </div>
                        <div class="form-group tags">
                            <label v-for="district in origins_additional_select" class="tag-districts">
                                <input type="radio" :value="district.code" v-model="origin_district"
                                       :name="'radio_origin_'+district.parent_code">
                                @{{ district.label }}
                            </label>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group fecha">
                            <v-select :options="destinations_countries_select" @input="change_destiny_cities()"
                                      v-model="destiny_country"
                                      placeholder="{{trans('service.label.select_destination')}}"
                                      class="form-control"></v-select>
                        </div>
                        <div class="form-group destino">
                            <v-select :options="destinations_select" @input="change_destiny_districts()"
                                      v-model="destiny"
                                      placeholder="{{trans('service.label.select_destination')}}"
                                      class="form-control">
                            </v-select>
                        </div>
                        <div class="form-group tags">
                            <label v-for="district in destinations_additional_select" class="tag-districts">
                                <input type="radio" :value="district.code" v-model="destiny_district"
                                       :name="'radio_destiny_'+district.parent_code">
                                @{{ district.label }}
                            </label>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group fecha">
                            <date-picker :name="'picker'"
                                         v-model="date"
                                         :config="options"></date-picker>
                        </div>
                        <div class="form-group pasajeros dropdown">
                            <button class="form-control" id="dropdownHab" data-toggle="dropdown" aria-haspopup="true"
                                    aria-expanded="true">
                                <span><strong>@{{ adults }}</strong>{{trans('service.label.adults')}}</span>
                                <span><strong>@{{ child }}</strong>{{trans('service.label.children')}}</span>
                            </button>
                            <div aria-labelledby="dropdownHab" class="dropdown dropdown-menu"
                                 :class="class_container_rooms">
                                <div class="container_quantity_persons_rooms_selects quantity-persons-rooms">
                                    <div class="form-group"
                                         :class="class_container_select">
                                        <label>{{trans('service.label.adults')}}</label>
                                        <select class="form-control" v-model="adults"
                                                @change="changeQuantityAdult($event)">
                                            <option v-for="num_adults in 40" :value="num_adults">@{{ num_adults }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="form-group"
                                         :class="class_container_select">
                                        <label>{{trans('service.label.children')}}</label>
                                        <select class="form-control" v-model="child"
                                                @change="changeQuantityChild($event)">
                                            <option v-for="num_child in 6" :value="num_child - 1">@{{ num_child - 1 }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="form-group"
                                         :class="class_container_select"
                                         v-for="(age_child_slot,index_age_child) in quantity_persons.age_childs"
                                         v-if="child >=1">
                                        <label>Edad</label>
                                        <select class="form-control"
                                                v-model="quantity_persons.age_childs[index_age_child].age">
                                            <option v-for="age_child in 17" :value="age_child">@{{ age_child }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group estados">
                            <v-select :options="service_types"
                                      :clearable="false"
                                      label="label"
                                      v-model="serviceTypeSelected"
                                      :reduce="servicetype => servicetype.code"
                                      @input="setServiceTypeSelected"
                                      placeholder="Eliga un estado"
                                      class="form-control">
                                <template slot="option" slot-scope="option">
                                    @{{ option.label }}
                                </template>
                            </v-select>
                        </div>
                        <div class="form-group tipos dropdown">
                            <button class="form-control" id="dropdownTipo" data-toggle="dropdown" aria-haspopup="true"
                                    aria-expanded="true">
                                 <span v-if="check_all_categories">
                                     {{trans('service.label.all_types')}}
                                 </span>
                                <span v-else>
                                    @{{ label_select_category | truncate(20,'...') }}
                                </span>
                            </button>
                            <div aria-labelledby="dropdownTipo" class="dropdown dropdown-menu"
                                 style="width: 420px; font-size: 14px;  margin-left: -36px; margin-top: 18px;">
                                <div style="overflow-y: scroll;">
                                    <div class="container_quantity_persons_rooms_selects quantity-persons-rooms"
                                         style="height: 170px">
                                        <div class="row col-md-12 ml-0">
                                            <div class="col-md-12 mb-3">
                                                <div class="form-check">
                                                    <input style="margin-top: 14px;" class="form-check-input"
                                                           type="checkbox"
                                                           id="check_all_categories"
                                                           v-model="check_all_categories" @change="checkAllCategories">
                                                    <label class="form-check-label" for="check_all_categories"
                                                           style="padding-left: 7px;">
                                                        {{trans('service.label.all_types')}}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row col-md-12 ml-0">
                                            <div class="col-md-6 mb-3" v-for="category in categories">
                                                <h4><strong>@{{ category.category }}</strong></h4>
                                                <div class="form-check" v-for="subcategory in category.sub_category">
                                                    <input style="margin-top: 14px;" class="form-check-input"
                                                           v-model="subcategory.status"
                                                           :id="'checkbox_'+subcategory.name"
                                                           type="checkbox" @change="checkSubCategories">
                                                    <label class="form-check-label" :for="'checkbox_'+subcategory.name"
                                                           style="padding-left: 7px;">
                                                        @{{ subcategory.name }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group experiencias dropdown">
                            <button class="form-control" id="dropdownExpenience" data-toggle="dropdown"
                                    aria-haspopup="true"
                                    aria-expanded="true">
                                <span v-if="check_all_experiences">
                                      {{trans('service.label.all_the_experiences')}}
                                 </span>
                                <span v-else>
                                    @{{ label_select_experiences | truncate(15,'...') }}
                                </span>
                            </button>
                            <div aria-labelledby="dropdownExpenience" class="dropdown dropdown-menu"
                                 style="width: 283px; font-size: 14px; margin-top: 18px;">
                                <div style="overflow-y: scroll;">
                                    <div class="container_quantity_persons_rooms_selects quantity-persons-rooms "
                                         style="height: 170px;">
                                        <div class="row col-md-12 pr-0">
                                            <div class="col-md-12 mb-3">
                                                <div class="form-check">
                                                    <input style="margin-top: 14px;" class="form-check-input"
                                                           type="checkbox"
                                                           id="check_all_experiences"
                                                           v-model="check_all_experiences"
                                                           @change="checkAllExperiences">
                                                    <label class="form-check-label" for="check_all_experiences"
                                                           style="padding-left: 7px;">
                                                        <span>{{trans('service.label.all_the_experiences')}}</span>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-12 mb-3">
                                                <div class="form-check" v-for="experience in experiences">
                                                    <input style="margin-top: 14px;" class="form-check-input"
                                                           v-model="experience.status"
                                                           :id="'checkbox_'+experience.code"
                                                           type="checkbox" @change="checkExperiences">
                                                    <label class="form-check-label" :for="'checkbox_'+experience.code"
                                                           style="padding-left: 7px;">
                                                        @{{ experience.label }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group clasificaciones">
                            <v-select :options="classifications"
                                      label="label"
                                      v-model="classificationSelected"
                                      :reduce="classification => classification.code"
                                      @input="setClassificationSelected"
                                      placeholder="Elija una clasificación"
                                      class="form-control">
                                <template slot="option" slot-scope="option">
                                    @{{ option.label }}
                                </template>
                            </v-select>
                        </div>
                        <div class="form-group tags">
                            <input type="text"
                                   placeholder="{{trans('service.label.use_keywords_example')}}"
                                   v-model="filter"
                                   name="filter" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="form-btn-action">
                    <div class="row">
                        <div class="col-sm-9 align-self-center">
                            <div class="modal fade" id="mod-historial" tabindex="-1" role="dialog"
                                 aria-labelledby="mod-historial-label" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <a id="modal_search_close" class="close" data-dismiss="modal"
                                       aria-label="Close" href="">
                                        {{ trans('hotel.label.Close') }}<i aria-hidden="true">&times;</i>
                                    </a>
                                    <div class="modal-content">
                                        <div class="modal-body">
                                            <h3 class="modal-title"
                                                id="mod-historial-label">{{trans('service.label.service_history')}}</h3>
                                            <p>
                                                {{trans('service.label.select_one_searches')}}
                                            </p>
                                            <div class="box-busqueda"
                                                 v-for="(search_destiny,index_search_destiny) in search_destinies_save">
                                                <div class="box-busqueda-row" v-for="destiny in search_destiny">
                                                    <h4>
                                                        <span v-if="destiny.origin !== 'null'">@{{ (JSON.parse(destiny.origin)).label }} </span>
                                                        <i v-if="destiny.origin !== 'null' && destiny.destiny !== 'null'"
                                                           class="fas fa-long-arrow-alt-right text-danger"></i>
                                                        <span v-if="destiny.destiny !== 'null'">@{{ (JSON.parse(destiny.destiny)).label }}</span>
                                                    </h4>
                                                    <div class="itinerario-historial">
                                                        <span>@{{ formatDate(destiny.date) }}</span>
                                                        <span>
                                                            <strong>@{{ destiny.quantity_adults }} {{ trans('hotel.label.adults') }}</strong>
                                                            <strong>@{{ destiny.quantity_child}} {{ trans('hotel.label.child') }}</strong>
                                                        </span>
                                                        <p>{{ trans('hotel.label.searched_the') }} @{{
                                                            getDateSearchDestiny(destiny.created_at)
                                                            }}</p>
                                                    </div>
                                                </div>
                                                <button class="btn-seleccionar"
                                                        @click="getSearchDestiniesByTokenSearch(search_destiny)">
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
                                                   :class="{'active': page == pagination.current_page}">@{{ page }}</a>
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
                        </div>
                        <div class="col-sm-3">
                            <button class="btn btn-primary"
                                    @click="search_services(true)">{{trans('service.label.search_services')}}</button>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row col-lg-12 title-result" v-show="tabsDestinies">
                <h3 class="mb-4">{{trans('service.label.your_results')}}</h3>
            </div>
            <div class="results mb-5" v-show="tabsDestinies">
                <div class="row col-lg-12">
                    <div class="d-flex flex-row">
                        <span class="mr-5"><strong>@{{ quantity_services }}</strong> {{trans('service.label.services_found')}}</span>
                        <h4 class="mr-5">
                            <strong v-if="search_destinies.origin !== '' && search_destinies.destiny !== ''">
                                @{{ search_destinies.origin }} <i class="fas fa-long-arrow-alt-right"></i>
                                @{{ search_destinies.destiny }}
                            </strong>
                        </h4>
                        <span class="mr-5"><b>@{{ getServiceDate() }}</b></span>
                        <span class="mr-5"><strong>@{{ search_destinies.quantity_persons_search }}</strong> {{trans('service.label.people')}} </span>
                    </div>
                </div>
            </div>
            <div class="row col-lg-12 filtros" v-if="services.length > 0">
                <div class="row col-lg-4">
                    <div class="col-lg-4 label">
                        <label class="m-0 mr-4 text-nowrap">{{trans('service.label.sort_by')}}</label>
                    </div>
                    <div class="col-lg-8">
                        <select class="form-control" @change="sortServices($event)">
                            <option value="1"
                                    selected>{{trans('service.label.sort_by_price_lowest_to_highest')}}</option>
                            <option value="2">{{trans('service.label.sort_by_price_higher_to_lower')}}</option>
                        </select>
                    </div>
                </div>
                <div class="row col-lg-4 filtros-font">
                    <div class="col lg-2 text-center text">
                        {{trans('service.label.filters')}}
                    </div>
                    <!--Filtro por Precio-->
                    {{--                    <div class="col lg-2 text-center tooltip_filter_price font">--}}
                    {{--                        <div class="dropdown">--}}
                    {{--                            <a href="#" role="button" id="dropdownMenuLink1" data-toggle="dropdown"--}}
                    {{--                               aria-haspopup="true" aria-expanded="true" style="border: none;">--}}
                    {{--                                <i class="fas fa-dollar-sign" style="font-size: x-large"></i>--}}
                    {{--                            </a>--}}
                    {{--                            <div id="dropdownMenuLink1Container" class="dropdown-menu"--}}
                    {{--                                 aria-labelledby="dropdownMenuLink1"--}}
                    {{--                                 style="width: 240px; font-size: 14px; margin-left: -36px; margin-top: 18px;">--}}
                    {{--                                <div style="color: #000;">--}}
                    {{--                                    <div style="width: 100%;margin: 0 auto;">--}}
                    {{--                                        <b style="font-size: 15px;">Precio</b>--}}
                    {{--                                    </div>--}}
                    {{--                                    <div style="width: 100%;margin: 0 auto;">--}}
                    {{--                                        <label class="text-left" style="width: 48%; margin: 0;">--}}
                    {{--                                            Min--}}
                    {{--                                            <bdi style="color: #8e0b07;">$.@{{--}}
                    {{--                                                min_price--}}
                    {{--                                                }}--}}
                    {{--                                            </bdi>--}}
                    {{--                                        </label>--}}
                    {{--                                        <label class="text-right" style="width: 48%;  margin: 0;">--}}
                    {{--                                            Max--}}
                    {{--                                            <bdi style="color: #8e0b07;">$.@{{--}}
                    {{--                                                max_price--}}
                    {{--                                                }}--}}
                    {{--                                            </bdi>--}}
                    {{--                                        </label>--}}
                    {{--                                    </div>--}}
                    {{--                                    <div style="width: 100%; margin: 0 auto;">--}}
                    {{--                                        <vue-slider v-model="rangePrice"--}}
                    {{--                                                    :enable-cross="false" :min="0"--}}
                    {{--                                                    :max="max_price"></vue-slider>--}}
                    {{--                                    </div>--}}
                    {{--                                    <div style="width: 60px; margin: 20px  0 0 0    ;">--}}
                    {{--                                        <button class="btn-primary" @click="filterByPrice()"--}}
                    {{--                                                style="font-size: 12px;">--}}
                    {{--                                            Aplicar--}}
                    {{--                                        </button>--}}
                    {{--                                    </div>--}}
                    {{--                                </div>--}}
                    {{--                            </div>--}}
                    {{--                        </div>--}}
                    {{--                    </div>--}}

                    <!--Filtro por Estados-->
                    <div class="col lg-2 text-center font">
                        <div class="dropdown show">
                            <a href="#" role="button" id="dropdownTypeService" data-toggle="dropdown"
                               aria-haspopup="false" aria-expanded="false" style="border: none;">
                                <i class="icon-unlock" style="font-size: x-large"></i>
                            </a>
                            <div aria-labelledby="dropdownTypeService" class="dropdown dropdown-menu"
                                 style="width: 283px; font-size: 14px; margin-top: 18px;">
                                <div class="container_quantity_persons_rooms_selects quantity-persons-rooms">
                                    <div class="row col-md-12">
                                        <div class="col-md-12 mb-3">
                                            <div class="form-check">
                                                <input style="margin-top: 14px;" class="form-check-input"
                                                       type="checkbox"
                                                       id="check_all_type_services"
                                                       v-model="check_all_type_services"
                                                       @change="checkAllTypeServiceFilter">
                                                <label class="form-check-label" for="check_all_experiences"
                                                       style="padding-left: 7px;">
                                                    <span>{{trans('service.label.all_the_states')}}</span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <div class="form-check" v-for="types in service_typesFilter">
                                                <input style="margin-top: 14px;" class="form-check-input"
                                                       v-model="types.status"
                                                       :id="'checkbox_'+types.code"
                                                       type="checkbox" @change="checkTypeServiceFilter">
                                                <label class="form-check-label" :for="'checkbox_'+types.code"
                                                       style="padding-left: 7px;">
                                                    @{{ types.label }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--Filtro por Tipos-->
                    <div class="col lg-2 text-center font">
                        <div class="dropdown show">
                            <a href="#" role="button" id="dropdownTiposFilter" data-toggle="dropdown"
                               aria-haspopup="false" aria-expanded="false" style="border: none;">
                                <i class="icon-archive"></i>
                            </a>
                            <div aria-labelledby="dropdownTiposFilter" class="dropdown dropdown-menu"
                                 style="width: 420px; font-size: 14px;  margin-left: -36px; margin-top: 18px;">
                                <div class="container_quantity_persons_rooms_selects quantity-persons-rooms">
                                    <div class="row col-md-12 ml-0">
                                        <div class="col-md-12 mb-3">
                                            <div class="form-check">
                                                <input style="margin-top: 14px;" class="form-check-input"
                                                       type="checkbox"
                                                       id="check_all_categories"
                                                       v-model="check_all_categories"
                                                       @change="checkAllCategoriesFilter">
                                                <label class="form-check-label" for="check_all_categories"
                                                       style="padding-left: 7px;">
                                                    {{trans('service.label.all_types')}}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row col-md-12 ml-0">
                                        <div class="col-md-6 mb-3" v-for="category_filter in categoriesFilter">
                                            <h4><strong>@{{ category_filter.category }}</strong></h4>
                                            <div class="form-check"
                                                 v-for="subcategory_filter in category_filter.sub_category">
                                                <input style="margin-top: 14px;" class="form-check-input"
                                                       v-model="subcategory_filter.status"
                                                       :id="'checkboxFilter_'+subcategory_filter.name"
                                                       type="checkbox" @change="checkSubCategoriesFilter">
                                                <label class="form-check-label"
                                                       :for="'checkboxFilter_'+subcategory_filter.name"
                                                       style="padding-left: 7px;">
                                                    @{{ subcategory_filter.name }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--Filtro por Experiencias-->
                    <div class="col lg-2 text-center font">
                        <div class="dropdown show">
                            <a href="#" role="button" id="dropdownExpenience" data-toggle="dropdown"
                               aria-haspopup="false" aria-expanded="false" style="border: none;">
                                <i class="icon-tag"></i>
                            </a>
                            <div aria-labelledby="dropdownExpenience" class="dropdown dropdown-menu"
                                 style="width: 283px; font-size: 14px; margin-top: 18px;">
                                <div class="container_quantity_persons_rooms_selects quantity-persons-rooms">
                                    <div class="row col-md-12">
                                        <div class="col-md-12 mb-3">
                                            <div class="form-check">
                                                <input style="margin-top: 14px;" class="form-check-input"
                                                       type="checkbox"
                                                       id="check_all_experiences"
                                                       v-model="check_all_experiences"
                                                       @change="checkAllExperiencesFilter">
                                                <label class="form-check-label" for="check_all_experiences"
                                                       style="padding-left: 7px;">
                                                    <span>{{trans('service.label.all_the_experiences')}}</span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <div class="form-check" v-for="experience in experiencesFilter">
                                                <input style="margin-top: 14px;" class="form-check-input"
                                                       v-model="experience.status"
                                                       :id="'checkbox_'+experience.code"
                                                       type="checkbox" @change="checkExperiencesFilter">
                                                <label class="form-check-label" :for="'checkbox_'+experience.code"
                                                       style="padding-left: 7px;">
                                                    @{{ experience.label }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--Filtro por Clasificaciones-->
                    <div class="col lg-2 text-center font">
                        <div class="dropdown show">
                            <a href="#" role="button" id="dropdownClassificationFilter" data-toggle="dropdown"
                               aria-haspopup="false" aria-expanded="false" style="border: none;">
                                <i class="icon-paperclip"></i>
                            </a>
                            <div aria-labelledby="dropdownClassificationFilter" class="dropdown dropdown-menu"
                                 style="width: 283px; font-size: 14px; margin-top: 18px;">
                                <div class="container_quantity_persons_rooms_selects quantity-persons-rooms">
                                    <div class="row col-md-12">
                                        <div class="col-md-12 mb-3">
                                            <div class="form-check">
                                                <input style="margin-top: 14px;" class="form-check-input"
                                                       type="checkbox"
                                                       id="check_all_classifications"
                                                       v-model="check_all_classifications"
                                                       @change="checkAllClassificationsFilter">
                                                <label class="form-check-label" for="check_all_classifications"
                                                       style="padding-left: 7px;">
                                                    <span>{{trans('service.label.all_the_classifications')}}</span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <div class="form-check" v-for="classification in classificationsFilter">
                                                <input style="margin-top: 14px;" class="form-check-input"
                                                       v-model="classification.status"
                                                       :id="'checkbox_'+classification.code"
                                                       type="checkbox" @change="checkClassificationsFilter">
                                                <label class="form-check-label" :for="'checkbox_'+classification.code"
                                                       style="padding-left: 7px;">
                                                    @{{ classification.label }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--Filtro por Nombre o descripción-->
                    <div class="col lg-2 text-center font">
                        <div class="dropdown show">
                            <a href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown"
                               aria-haspopup="false" aria-expanded="false" style="border: none;">
                                <i class="icon-hash"></i>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuLink"
                                 style="width: 300px; font-size: 14px;  margin-left: -36px; margin-top: 18px;">
                                <div style="width: 100%;margin: 0 auto;">
                                    <b style="font-size: 15px;">Palabras claves</b>
                                </div>
                                <div class="form-group">
                                    <input type=""
                                           placeholder="{{trans('service.label.use_keywords_example')}}"
                                           v-model="filterFilter"
                                           @keyup.enter="filterByName"
                                           name="filter_filter" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row col-lg-4 vista">
                    <div class="col-lg-9 text text-right">
                        {{trans('service.label.type_of_view')}}
                    </div>
                    <div class="row col-lg-3 content-vista">
                        <div id="filterView_0" class="col lg-2 font text-center active" @click="filterView(0,this)">
                            <i class="fas fa-th-list" style="font-size: x-large"></i>
                        </div>
                        <div id="filterView_1" class="col lg-2 font text-center" @click="filterView(1,this)">
                            <i class="fas fa-list" style="font-size: x-large"></i>
                        </div>
                    </div>
                </div>
            </div>
            <!--Resultados en Vista 1    -->

            <div id="result-services" class="row-fluid col-lg-12 result-services list-filters"
                 v-if="services.length > 0" v-show="viewMode">
                <div class="card-services mb-5" v-for="service in services">
                    <span class="onsale-section"
                          v-if="service.service_type.code == 'PC' || service.service_type.code == 'SIM'">
                        <span class="onsale">
                            <i class="icon-lock" v-if="service.service_type.code == 'PC'"></i>
                            <i class="icon-unlock" v-if="service.service_type.code == 'SIM'"></i>
                            <span v-html="service.service_type.name"></span>
                        </span>
                    </span>
                    <div class="destino pt-4 ml-4">
                        <span class="">
                            <img :src="service.classification.image">
                            <strong>@{{ service.origin.state }} <span class="icon-arrow-right"
                                                                      style="color: #ce3b4d;"></span>
                                @{{ service.destiny.state }}
                            </strong>
                        </span>
                    </div>
                    <h4>
                        <a :title="'Precio por persona USD ' + service.price_per_person" class="tooltip-price">
                            <strong v-if="service.descriptions.name !== null && service.descriptions.name !== ''"
                                    v-html="service.descriptions.name"></strong>
                            <strong v-else v-html="service.name"></strong>
                            <span class="text-muted ml-3" style="font-size: 1.3rem;">[@{{ service.code }}]</span>
                            <span class="badge badge-success" v-if="service.on_request == 0"><span
                                    class="icon-smile mr-1"></span>OK</span>
                            <span class="badge badge-danger" v-if="service.on_request == 1"><span
                                    class="icon-meh mr-1"></span> RQ</span>
                        </a>
                    </h4>

                    <div class="box-tag">
                        <div class="tag" class="tag_history" v-for="experience in service.experiences"
                             :style="'background-color:' + experience.color +' '">
                            @{{ experience.name }}
                        </div>
                    </div>
                    <div class="d-flex pb-5">
                        <div class="card-services__image" v-if="service.galleries.length > 0">
                            <img :src="service.galleries[0]"
                                 class="object-fit_cover backup_picture_service" alt="service">
                        </div>
                        <div class="card-services__description">
                            <div>
                                <div class="multi-services" v-if="service.components.length>0 && !(component.repeated)"
                                     v-for="(component, c_i) in service.components">

                                    <div class="d-block" v-if="!(component.show_replace)">
                                        <div class="text-right" style="margin-top: -20px;">
                                            <button class="btn-multi ml-2"
                                                    v-if="component.descriptions.itinerary.length>0"
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
                                        <span class="text-muted mr-1">[@{{ component.code }}]</span>
                                        <span v-if="component.descriptions.name !== null"
                                              v-html="component.descriptions.name"></span>
                                        <span v-else v-html="component.name"></span>
                                        <strong class="ml-3">(+ USD @{{ component.price_per_person }}<span
                                                v-if="component.repeated_total>1"> x @{{ component.repeated_total }} {{trans('global.label.day')}}s</span>)
                                        </strong>
                                    </div>
                                    <div class="d-block mb-2" v-if="(component.show_replace)">
                                        <div class="d-flex justify-content-between">
                                            <label class="ml-2 mt-2 text-muted">Seleccionar el servicio a
                                                reemplazar:</label>
                                            <div>
                                                <button class="btn-multi ml-4" @click="replace_component(component)">
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
                                            <p class="mt-3" v-for="itinerary in component.descriptions.itinerary"
                                               v-if="!(component.repeated)">
                                                <strong>{{trans('global.label.day')}}
                                                    @{{ parseInt( itinerary.day ) + parseInt( component.after_days ) }}
                                                    <strong v-for="component_ in service.components"
                                                            v-if="(component_.repeated) && component_.service_id === component.service_id">
                                                        - {{trans('global.label.day')}}
                                                        @{{ parseInt( itinerary.day ) + parseInt( component_.after_days
                                                        ) }}
                                                    </strong>
                                                    : </strong>
                                                @{{ itinerary.description }}
                                            </p>
                                        </div>
                                    </transition>
                                </div>
                            </div>

                            <div class="card-services__description__cta mt-5">
                                <div class="text">
                                    <span class="icon-clock mr-2" style="font-size: 1.6rem;"></span>
                                    {{trans('service.label.duration')}} @{{ service.duration }} @{{
                                    service.unit_of_duration }}.
                                </div>
                                <div class="text" v-if="service.inclusions.length > 0"
                                     @click="openModalDetail(service,'inclusions')">
                                    <span class="icon-check-square mr-2" style="font-size: 1.6rem;"></span>
                                    {{trans('service.label.includes_not_include')}}
                                    <div class="icon ml-4">
                                        <i class="fa fa-angle-right"></i>
                                    </div>
                                </div>
                                <div class="text" @click="openModalDetail(service,'itinerary')">
                                    <i class="icon-clipboard mr-2" style="font-size: 1.6rem;"></i>
                                    {{trans('service.label.itinerary')}}
                                    <div class="icon ml-4">
                                        <i class="fa fa-angle-right"></i>
                                    </div>
                                </div>
                                <div class="text" @click="openModalDetail(service,'schedule')">
                                    <i class="icon-calendar mr-2" style="font-size: 1.6rem;"></i>
                                    {{trans('service.label.schedules_restrictions')}}
                                    <div class="icon ml-4">
                                        <i class="fa fa-angle-right"></i>
                                    </div>
                                </div>
                                <div class="text"
                                     v-if="service.descriptions.summary != null && service.descriptions.summary != '' && user_type_id == 3"
                                     @click="openModalDetail(service,'real_notes')">
                                    <span class="icon-message-circle mr-2" style="font-size: 1.6rem;"></span>
                                    {{trans('service.label.summary')}}
                                    <div class="icon ml-4">
                                        <i class="fa fa-angle-right"></i>
                                    </div>
                                </div>
                                <div class="text"
                                     v-if="service.commercial_descriptions.summary != null && service.commercial_descriptions.summary != '' && user_type_id == 4"
                                     @click="openModalDetail(service,'real_notes_commercial')">
                                    <span class="icon-message-circle mr-2" style="font-size: 1.6rem;"></span>
                                    {{trans('service.label.summary')}}
                                    <div class="icon ml-4">
                                        <i class="fa fa-angle-right"></i>
                                    </div>
                                </div>
                                <div class="text" v-if="service.notes != null && user_type_id == 3"
                                     @click="openModalDetail(service,'notes')">
                                    <span class="icon-message-circle mr-2" style="font-size: 1.6rem;"></span>
                                    Remarks
                                    <div class="icon ml-4">
                                        <i class="fa fa-angle-right"></i>
                                    </div>
                                </div>
                                <div class="text" v-if="service.rate.rate_plans[0].political != null && user_type_id == 3"
                                     @click="openModalDetailPolicy(service, 'political')">
                                    <span class="icon-message-circle mr-2" style="font-size: 1.6rem;"></span>
                                    {{trans('service.label.policies')}}
                                    <div class="icon ml-4">
                                        <i class="fa fa-angle-right"></i>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="card-services__price text-center">
                            <div class="d-block mb-5">
                                <div v-if="service.rate.rate_plans[0]">
                                <span
                                    v-bind:class="['text-min', (service.rate.rate_plans[0].flag_migrate === 0) ? 'text-success' : '']">
                                    USD
                                </span>
                                <span
                                    v-bind:class="['text-max', (service.rate.rate_plans[0].flag_migrate === 0) ? 'text-success' : '']">
                                    $
                                </span>
                                <span
                                    v-bind:class="['text-max', (service.rate.rate_plans[0].flag_migrate === 0) ? 'text-success' : '']">
                                    <strong>@{{ getPrice(service.price_per_adult) }}</strong>
                                </span>

                                <!-- Label cuando el precio lleva comisión -->
                                <span
                                    v-if="client && client.commission_status == 1 && parseFloat(client.commission) > 0 && user_type_id == 4"
                                    class="badge badge-warning ml-2">
                                    {{trans('global.label.with_commission') }}
                                </span>
                            </div>

                                <div class="text-min">
                                    <span>{{trans('service.label.cost_per_passenger')}}</span>
                                    <a href="javascript:void(0)" role="button" @click="openModalDetail(service,'price')"
                                       aria-haspopup="false" aria-expanded="false" style="border: none;">
                                        <i class="fa fa-question-circle" style="font-size: x-large; color: green;"></i>
                                    </a>
                                </div>
                                <div class="col-lg-12 p-0 mb-4">
                                    <span class="text-supplements">
                                        <div v-for="supplement in service.supplements.supplements">
                                            <span class="icon-plus"></span> {{trans('service.label.include')}}: @{{supplement.name}}
                                        </div>
                                    </span>
                                </div>
                            </div>
                            <div class="d-block">
                                <button class="btn btn-seleccionar" @click="willAddCart(service)" v-if="!service.taken">
                                    {{trans('service.label.to_select')}}
                                </button>
                                <button class="btn btn-cancelar" @click="deleteCartItem(service)" v-if="service.taken">
                                    {{trans('service.label.cancel')}}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--Resultados en Vista 2 -->
            <div id="result-services-list result-services" class="row-fluid col-lg-12 result-services"
                 v-if="services.length > 0" v-show="!viewMode">
                <div class="card-services mb-5" v-for="service in services">
                    <div class="d-flex ">
                        <div class="card-services__image" v-if="service.galleries.length > 0">
                            <img :src="service.galleries[0]"
                                 class="object-fit_cover" alt="service">
                        </div>
                        <div class="card-services__description">
                            <h4 :title="'Precio por persona USD ' + service.price_per_person">
                                <strong v-if="service.descriptions.name !== null && service.descriptions.name !== ''"
                                        v-html="service.descriptions.name"></strong>
                                <strong v-else v-html="service.name"></strong>
                                <span class="text-muted ml-3" style="font-size: 1.3rem;">[@{{ service.code }}]</span>
                                <span class="badge badge-success" v-if="service.on_request == 0"><span
                                        class="icon-smile mr-1"></span>OK</span>
                                <span class="badge badge-danger" v-if="service.on_request == 1"><span
                                        class="icon-meh mr-1"></span> RQ</span>
                            </h4>
                            <div>
                                <div class="filters">
                                    <ul class=" d-flex justify-content-start">
                                        <li class="ml-0">
                                            <i class="icon-lock" v-if="service.service_type.code == 'PC'"></i>
                                            <i class="icon-unlock" v-if="service.service_type.code == 'SIM'"></i>
                                            <span v-html="service.service_type.name"></span>
                                        </li>
                                        <li>
                                            <i class="icon-clock"></i> <span>{{trans('service.label.duration')}} @{{ service.duration }} @{{ service.unit_of_duration }}.</span>
                                        </li>
                                        <li class="experience">
                                        <span class="mr-2" v-for="experience in service.experiences">
                                            <i class="fas fa-circle" :style="'color:' + experience.color +' '"></i> @{{ experience.name }}
                                        </span>
                                        </li>
                                    </ul>
                                </div>

                                <div class="multi-services" v-if="service.components.length>0 && !(component.repeated)"
                                     v-for="(component, c_i) in service.components">

                                    <div class="d-block" v-if="!(component.show_replace)">
                                        <span class="text-muted mr-1">[@{{ component.code }}]</span>
                                        <span v-if="component.descriptions.name !== null"
                                              v-html="component.descriptions.name"></span>
                                        <span v-else v-html="component.name"></span> <strong class="ml-3">(+ USD @{{
                                            component.price_per_person }}<span v-if="component.repeated_total>1"> x @{{ component.repeated_total }} {{trans('global.label.day')}}s</span>)
                                        </strong>

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
                                                <button class="btn-multi ml-4" @click="replace_component(component)">
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
                                            <p class="mt-3" v-for="itinerary in component.descriptions.itinerary"
                                               v-if="!(component.repeated)">
                                                <strong>{{trans('global.label.day')}}
                                                    @{{ parseInt( itinerary.day ) + parseInt( component.after_days ) }}
                                                    <strong v-for="component_ in service.components"
                                                            v-if="(component_.repeated) && component_.service_id === component.service_id">
                                                        - {{trans('global.label.day')}}
                                                        @{{ parseInt( itinerary.day ) + parseInt( component_.after_days
                                                        ) }}
                                                    </strong>
                                                    : </strong>
                                                @{{ itinerary.description }}
                                            </p>
                                        </div>
                                    </transition>
                                </div>
                                <div class="mt-5">
                                    <ul class="d-flex justify-content-start">
                                        <li class="ml-0">
                                            <img :src="service.classification.image">
                                            <a href="#">{{trans('service.label.details')}} </a>
                                        </li>
                                        <li>
                                            <i class="icon-clipboard"></i>
                                            <a href="#"
                                               @click="openModalDetail(service,'itinerary')">{{trans('service.label.itinerary')}}</a>
                                        </li>
                                        <li>
                                            <i class="icon-calendar"></i>
                                            <a href="#"
                                               @click="openModalDetail(service,'schedule')">{{trans('service.label.schedules_restrictions')}}</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                        </div>
                        <div class="card-services__price text-center mt-5">
                            <div class="d-block mb-5">
                                <div class="" v-if="service.rate.rate_plans[0]">
                                    <span
                                        v-bind:class="['text-min', (service.rate.rate_plans[0].flag_migrate === 0) ? 'text-success' : '']">USD </span>
                                    <span
                                        v-bind:class="['text-max', (service.rate.rate_plans[0].flag_migrate === 0) ? 'text-success' : '']">$</span>
                                    <span
                                        v-bind:class="['text-max', (service.rate.rate_plans[0].flag_migrate === 0) ? 'text-success' : '']">
                                        <strong>@{{ service.total_price_per_person }}</strong>
                                    </span>
                                </div>
                                <div class="text-min">
                                    <span>{{trans('service.label.cost_per_passenger')}}</span>
                                </div>
                            </div>
                            <div class="d-block">
                                <button class="btn btn-seleccionar" @click="willAddCart(service)" v-if="!service.taken">
                                    {{trans('service.label.to_select')}}
                                </button>
                                <button class="btn btn-cancelar" @click="deleteCartItem(service)" v-if="service.taken">
                                    {{trans('service.label.cancel')}}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- @include('services.icon_carrito') --}}

            <infinite-loading v-if="page > 0 && notSearch" @infinite="infiniteHandler">
            </infinite-loading>
            <span slot="no-more" v-else>
                    <center>{{trans('service.label.no_more_data')}}</center>
            </span>
        </div>
    </section>

    <section v-else>
        <div class="jumbotron bg-danger">
            <h2 class="text-center text-white"><i class="fas fa-exclamation-triangle"></i>
                {{ trans('package.label.you_must_select_customer')  }}
                ... <i class="fas fa-hand-point-up"></i></h2>
        </div>
    </section>

    @include('services.modal')
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

        /*Estilos Range Slider*/
        .vue-slider-dot-handle {
            background-color: #8e0b07;
        }

        .vue-slider-process {
            background-color: #8e0b07;
        }

        .vue-slider-dot-tooltip-inner {
            min-width: 35px;
            border-color: #db3453;
            background-color: #dc3545;
        }

        .vue-slider-dot-handle-focus {
            box-shadow: 0.5px 0.5px 2px 1px rgba(0, 0, 0, 0.32);
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
        /*estilos de galeria*/
        .slides {
            top: 0;
            width: 100%;
            height: 100px;
            display: block;
            position: absolute;
        }

        .icon-trash-remove {
            font-weight: 800;
            font-size: 14px;
        }

        .modal-dialog .modal-body p {
            color: inherit !important;
        }
    </style>
@endsection
@section('js')
    <script>
        Vue.filter("currency", function(value) {
            return "$" + value.toFixed(2);
        });
        new Vue({
            el: "#app",
            data: {
                referer: {{ $refererEjecute }},
                user_type_id: localStorage.getItem("user_type_id"),
                viewMode: true,
                reservation_time_control: {
                    HH: "00",
                    mm: "00"
                },
                reservation_time: "",
                page: 0,
                baseExternalURL: window.baseExternalURL,
                baseURL: window.baseURL,
                label_select_category: "",
                label_select_experiences: "",
                check_all_categories: true,
                check_all_experiences: true,
                check_all_type_services: true,
                check_all_classifications: true,
                tabsDestinies: false,
                blockPage: false,
                loadingModal: false,
                update_menu: 1,
                origins_select: [],
                origins_select_universe: [],
                origin: "",
                origins_countries_select: [],
                origin_country: {
                    code: 89,
                    label: "Perú"
                },
                origins_additional_select_universe: [],
                origins_additional_select: [],
                origin_district: "",
                destinations_select_universe: [],
                destinations_select: [],
                destiny: "",
                destinations_countries_select: [],
                destiny_country: {
                    code: 89,
                    label: "Perú"
                },
                destinations_additional_select_universe: [],
                destinations_additional_select: [],
                destiny_district: "",
                quantity_persons: {
                    adults: 2,
                    child: 0,
                    age_childs: [
                        {
                            age: 1
                        }
                    ]
                },
                service_type_id: "all",
                service_types: [],
                service_typesFilter: [],
                options: {
                    format: "DD/MM/YYYY",
                    useCurrent: false,
                    defaultDate: moment().add(1, "days").format("YYYY-MM-DD"),
                    minDate: moment().add(1, "days").format("YYYY-MM-DD")
                },
                category_ids: [],
                experience_ids: [],
                classification_ids: [],
                experience_id: "all",
                classification_id: "all",
                view: "itinerary",
                categories: [],
                categoriesFilter: [],
                experiences: [],
                experiencesFilter: [],
                classifications: [],
                classificationsFilter: [],
                services_original: [],
                services: [],
                filter: "",
                filterFilter: "",
                service_detail_selected: {
                    descriptions: {
                        name: "",
                        name_commercial: "",
                        description: "",
                        summary: "",
                        itinerary: [],
                        supplements: []
                    },
                    experiences: [],
                    restrictions: []
                },
                search_destinies: [
                    {
                        token_search: "",
                        token_search_frontend: "",
                        type: "all",
                        category: "all",
                        classification: "all",
                        experience: "all",
                        filter_by_name: "",
                        destiny: "",
                        origin: "",
                        quantity_persons_search: 2,
                        date: "",
                        quantity_adults: 2,
                        quantity_child: 0,
                        quantity_persons: {
                            adults: 2,
                            child: 0,
                            age_childs: [
                                {
                                    age: 1
                                }
                            ]
                        }

                    }
                ],
                experienceSelected: [
                    {
                        "label": '{{trans("service.label.all_the_experiences")}}',
                        "code": "all"
                    }],
                serviceTypeSelected: [
                    {
                        "label": '{{trans("service.label.all_the_states")}}',
                        "code": "all"
                    }],
                classificationSelected: [
                    {
                        "label": '{{trans("service.label.all_the_classifications")}}',
                        "code": "all"
                    }],
                date: moment().add(1, "days").format("DD/MM/YYYY"),
                quantity_services: 0,
                min_price: 0,
                max_price: 0,
                rangePrice: 0,
                class_container_rooms: "container_quantity_persons_rooms width_default_container",
                class_container_select: "container_quantity_persons_rooms_select width_default_select",
                adults: 2,
                num_adults: 2,
                child: 0,
                num_child: 0,
                pagination: {
                    total: 0,
                    per_page: 2,
                    from: 1,
                    to: 0,
                    last_page: 1,
                    current_page: 1
                },
                pagesNumbers: 1,
                offset: 4,
                cart_quantity_items: 0,
                cart: {
                    cart_content: [],
                    hotels: [],
                    services: [],
                    total_cart: 0.00,
                    quantity_items: 0
                },
                search_advanced_save: [],
                search_destinies_save: [],
                busy: false,
                notSearch: false,
                limit: 50,
                service_year: 2020,
                parameters_supplements: [],
                client : {}
            },
            created: function() {
                // this.getCartContent()
                this.client_id = localStorage.getItem("client_id");
                if (this.client_id == null) {
                    window.location = this.baseURL + "home";
                }else {
                    this.getClient();
                }
                localStorage.setItem("reservation", false);
                this.$root.$on("cancelcartmodal", function(payload) {
                    // this.cancelSelectionCartInModal(payload.room_id, payload.hotel);
                    // this.getCartContent()
                });
                this.$root.$on("updatedestiniesandclass", function() {
                    this.client_id = localStorage.getItem("client_id");
                    this.tabsDestinies = false;
                    this.clearOriginsAndDestinies();
                    this.getDestinations();
                    this.getServicesTypes();
                    this.getExperiences();
                    this.getClassifications();
                    this.getCategories();
                    // this.search_services(true)
                });

            },
            mounted() {
                this.getCartContent();

                this.$root.$on("getCartContentMenu", () => {
                    this.getCartContent();
                });

                if (localStorage.getItem("search_params")) {
                    if (localStorage.getItem("search_params") !== "" && localStorage.getItem("search_params") != null && this.referer === 2) {

                        let params = JSON.parse(localStorage.getItem("search_params"));

                        if (params.module == "service") {

                            this.blockPage = true;
                            // console.log(params);
                            this.origin = params.destiny_ini.origin;
                            this.origin_country = params.destiny_ini.origin_country;
                            this.origin_district = params.destiny_ini.origin_district;

                            this.destiny = params.destiny_fin.destiny;
                            this.destiny_country = params.destiny_fin.destiny_country;
                            this.destiny_district = params.destiny_fin.destiny_district;

                            this.date = params.date;


                            this.quantity_persons = params.quantity_persons;
                            this.filter = params.filter;

                            setTimeout(() => {
                                this.origin = params.destiny_ini.origin;
                                this.destiny = params.destiny_fin.destiny;
                                this.change_origin_districts();
                                this.change_destiny_districts();
                                this.origin_district = params.destiny_ini.origin_district;
                                this.destiny_district = params.destiny_fin.destiny_district;

                                // this.service_type_id = params.service_type_id

                                this.serviceTypeSelected = params.serviceTypeSelected;
                                this.service_type_id = params.service_type_id;

                                this.category_ids = params.category_ids;
                                this.check_all_categories = params.check_all_categories;
                                this.label_select_category = params.label_select_category;
                                for (let i = 0; i < this.categories.length; i++) {
                                    if (this.categories[i].sub_category) {
                                        for (let s = 0; s < this.categories[i].sub_category.length; s++) {
                                            if (this.check_all_categories == true) {
                                                this.categories[i].sub_category[s].status = true;
                                            } else {
                                                if (this.category_ids.includes(this.categories[i].sub_category[s].id)) {
                                                    this.categories[i].sub_category[s].status = true;
                                                }
                                            }
                                        }
                                    }
                                }


                                this.experience_ids = params.experience_ids;
                                this.check_all_experiences = params.check_all_experiences;
                                this.label_select_experiences = params.label_select_experiences;

                                for (let i = 0; i < this.experiences.length; i++) {

                                    if (this.check_all_experiences == true) {
                                        this.experiences[i].status = true;
                                    } else {
                                        if (this.experience_ids.includes(this.experiences[i].code)) {
                                            this.experiences[i].status = true;
                                        }
                                    }

                                }


                                this.classificationSelected = params.classificationSelected;
                                this.classification_ids = params.classification_ids;
                                this.check_all_classifications = params.check_all_classifications;

                                this.search_services(true);

                            }, 3000);

                        }

                    }
                }

            },
            methods: {
                getClient() {
                    axios.get(`${baseExternalURL}api/clients/${this.client_id}`)
                        .then((response) => {
                            console.log("Cliente:", response.data);
                            this.client = response.data.data;
                        })
                        .catch((error) => {
                            console.error("Error al obtener el cliente:", error);
                        });
                },
                getPrice(price) {
                        // Debug: revisar valores que llegan
                        console.log("Commission status:", this.client ? this.client.commission_status : null);
                        console.log("Commission:", this.client ? this.client.commission : null);
                        console.log("User type:", this.user_type_id);

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

                        return price;
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
                remove_multiservice(service, c_i) {
                    service.total_price_per_person -= service.components[c_i].price_per_person;
                    service.components.splice(c_i, 1);
                },
                will_remove_multiservice(component) {
                    component.will_remove = true;
                    setTimeout(() => {
                        component.will_remove = false;
                    }, 5000);
                },
                replace_component(component) {
                    let data = {
                        client_id: this.client_id,
                        service_id: component.substitute_selected.service_id
                    };
                    axios.post("api/services/component/" + component.component_id + "/client", data).then((result) => {

                            component.show_replace = false;
                            if (result.data.success) {
                                this.search_services(true);
                            }
                        }
                    ).catch((e) => {
                        console.log(e);
                    });

                },
                selectedSupplement: function(supplement, index) {
                    if (supplement.selected) {
                        this.filterSupplement(supplement, index);
                    }
                },
                totalSupplement: function() {
                    var total = 0;
                    this.service_detail_selected.supplements.optional_supplements.forEach(function(s) {
                        if (s.selected) {
                            total += s.rate.total_amount;
                        }
                    });
                    return total;
                },
                totalServiceSupplement: function() {
                    let total_supplement = this.service_detail_selected.total_amount;
                    return total_supplement += this.totalSupplement();
                },
                clearFilters: function() {
                    this.change_origin_cities();
                    this.change_destiny_cities();
                    this.filter = "";
                    this.label_select_experiences = "";
                    this.num_adults = 2;
                    this.adults = 2;
                    this.num_child = 0;
                    this.child = 0;
                    this.quantity_persons.adults = 2;
                    this.quantity_persons.child = 0;
                    this.quantity_persons.age_childs = [
                        {
                            age: 1
                        }
                    ];
                    this.service_type_id = "all";
                    this.experience_id = "all";
                    this.classification_ids = "all";
                    this.experienceSelected = [
                        {
                            "label": '{{trans("service.label.all_the_experiences")}}',
                            "code": "all"
                        }];
                    this.serviceTypeSelected = [
                        {
                            "label": '{{trans("service.label.all_the_states")}}',
                            "code": "all"
                        }];
                    this.classificationSelected = [
                        {
                            "label": '{{trans("service.label.all_the_classifications")}}',
                            "code": "all"
                        }];

                    this.check_all_experiences = true;
                    this.check_all_classifications = true;
                    this.check_all_categories = true;
                    this.checkAllCategories();
                    this.checkAllExperiences();
                    localStorage.setItem("search_params", "");

                },
                getRouteExcelServiceYear: function() {
                    var a = document.createElement("a");

                    a.target = "_blank";

                    a.href = baseExternalURL + "services/" + this.service_year + "/export?lang=" +
                        localStorage.getItem("lang") + "&client_id=" + localStorage.getItem("client_id") + "&user_id=" +
                        localStorage.getItem("user_id") + "&user_type_id=" + localStorage.getItem("user_type_id");

                    a.click();
                },
                sortServices: function(event) {
                    //precio menor
                    if (event.target.value === "1") {
                        this.services.sort(
                            (packagea, packageb) => packagea.price_per_person - packageb.price_per_person);
                    }
                    //precio mayor
                    if (event.target.value === "2") {
                        this.services.sort(
                            (packagea, packageb) => packageb.price_per_person - packagea.price_per_person);
                    }
                },
                setClassificationSelected: function(value) {
                    if (value === "all") {
                        this.check_all_classifications = true;
                        this.classification_ids = value;
                    } else {
                        this.check_all_classifications = false;
                        this.classification_ids = [value];
                    }

                },
                setExperienceSelected: function(value) {
                    this.experience_id = value;
                },
                setServiceTypeSelected: function(value) {
                    if (value === "all") {
                        this.service_type_id = value;
                    } else {
                        this.service_type_id = [value];
                    }
                },
                getCategories: function() {
                    axios.get(baseExternalURL + "services/categories?lang=" + localStorage.getItem("lang")).then((result) => {
                            if (result.data.success) {
                                let data = result.data.data;
                                for (let c = 0; c < data.length; c++) {
                                    if (data[c].sub_category) {
                                        for (let i = 0; i < data[c].sub_category.length; i++) {
                                            data[c].sub_category[i].status = false;
                                        }
                                    }
                                }
                                this.categories = data;
                                this.categoriesFilter = data;
                            }
                        }
                    ).catch((e) => {
                        console.log(e);
                    });
                },
                getClassifications: function() {
                    axios.get(baseExternalURL + "services/classifications?lang=" + localStorage.getItem("lang")).then((result) => {
                        if (result.data.success) {
                            this.classifications.push({
                                "label": '{{trans("service.label.all_the_classifications")}}',
                                "code": "all"
                            });
                            var data = result.data.data;
                            for (let i = 0; i < data.length; i++) {
                                this.classifications.push({
                                    "label": data[i].name,
                                    "code": data[i].id
                                });
                            }
                            for (let i = 0; i < data.length; i++) {
                                this.classificationsFilter.push({
                                    "label": data[i].name,
                                    "code": data[i].id
                                });
                            }
                        }
                    }).catch((e) => {
                        console.log(e);
                    });
                },
                getServicesTypes: function() {
                    axios.get(baseExternalURL + "services/service_types?lang=" + localStorage.getItem("lang")).then((result) => {
                        if (result.data.success) {
                            this.service_types.push({
                                "label": '{{trans("service.label.all_the_states")}}',
                                "code": "all"
                            });
                            var data = result.data.data;
                            for (let i = 0; i < data.length; i++) {
                                this.service_types.push({
                                    "label": data[i].name,
                                    "code": data[i].id
                                });
                                this.service_typesFilter.push({
                                    "label": data[i].name,
                                    "code": data[i].id
                                });
                            }
                        }
                    }).catch((e) => {
                        console.log(e);
                    });
                },
                getExperiences: function() {
                    axios.get(baseExternalURL + "services/experiences?lang=" + localStorage.getItem("lang")).then((result) => {
                        if (result.data.success) {
                            var data = result.data.data;
                            for (let i = 0; i < data.length; i++) {
                                this.experiences.push({
                                    "label": data[i].name,
                                    "code": data[i].id
                                });
                                this.experiencesFilter.push({
                                    "label": data[i].name,
                                    "code": data[i].id
                                });
                            }
                        }
                    }).catch((e) => {
                        console.log(e);
                    });
                },
                getDestinations: function() { // Busqueda de destinos por cliente
                    this.blockPage = true;
                    axios.get("services/destination_services?client_id=" + this.client_id).then((result) => {
                        this.blockPage = false;
                        if (result.data.success) {
                            this.unzip_destination_services(result.data.data);
                        }
                    }).catch((e) => {
                        this.blockPage = false;
                        console.log(e);
                    });
                },
                unzip_destination_services(data) {
                    // {code: "89,1610,128", label: "Perú, Lima, Lima"}

                    // ORIGINS
                    this.origins_countries_select = [];
                    let origins_countries_select_ = [];
                    this.origins_select_universe = [];
                    let origins_select_ = [];
                    this.origins_additional_select_universe = [];

                    data.origins.forEach((d) => {
                        let code_split = d.code.split(",");
                        let label_split = d.label.split(",");

                        if (origins_countries_select_[code_split[0]] === undefined) {
                            this.origins_countries_select.push({
                                code: code_split[0],
                                label: label_split[0].trim()
                            });
                            origins_countries_select_[code_split[0]] = true;
                        }

                        if (origins_select_[code_split[1]] === undefined) {
                            this.origins_select_universe.push({
                                code: code_split[1],
                                label: label_split[1].trim(),
                                parent_code: code_split[0]
                            });
                            origins_select_[code_split[1]] = true;
                        }

                        let code_for_split = code_split[0] + "," + code_split[1];
                        let label_for_split = label_split[0] + "," + label_split[1];
                        let code_add_split = d.code.split(code_for_split);
                        let label_add_split = d.label.split(label_for_split);
                        if (code_add_split.length > 1) {
                            if (code_add_split[1].trim() !== "") {
                                this.origins_additional_select_universe.push({
                                    code: code_add_split[1].substring(1),
                                    label: label_add_split[1].substring(1).trim(),
                                    parent_code: code_split[1]
                                });
                            }
                        }
                    });

                    // DESTINIES
                    this.destinations_countries_select = [];
                    let destinations_countries_select_ = [];
                    this.destinations_select_universe = [];
                    let destinations_select_ = [];
                    this.destinations_additional_select_universe = [];

                    data.destinations.forEach((d) => {
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

                    this.change_origin_cities();
                    this.change_destiny_cities();
                },
                change_origin_cities() {
                    this.origins_additional_select = [];
                    this.origins_select = [];
                    this.origin = "";
                    this.origins_select_universe.forEach((o_u) => {
                        if (o_u.parent_code == this.origin_country.code) {
                            this.origins_select.push(o_u);
                        }
                    });
                },
                change_destiny_cities() {
                    this.destinations_additional_select = [];
                    this.destinations_select = [];
                    this.destiny = "";
                    this.destinations_select_universe.forEach((d_u) => {
                        if (d_u.parent_code == this.destiny_country.code) {
                            this.destinations_select.push(d_u);
                        }
                    });
                },
                change_origin_districts() {
                    this.origins_additional_select = [];
                    this.origin_district = "";
                    this.origins_additional_select_universe.forEach((o_u) => {
                        if (o_u.parent_code == this.origin.code) {
                            this.origins_additional_select.push(o_u);
                        }
                    });
                },
                change_destiny_districts() {
                    this.destinations_additional_select = [];
                    this.destiny_district = "";
                    this.destinations_additional_select_universe.forEach((d_u) => {
                        if (d_u.parent_code == this.destiny.code) {
                            this.destinations_additional_select.push(d_u);
                        }
                    });
                },
                checkAllCategories: function() {
                    this.label_select_category = "";
                    for (let i = 0; i < this.categories.length; i++) {
                        if (this.categories[i].sub_category) {
                            for (let s = 0; s < this.categories[i].sub_category.length; s++) {
                                if (this.check_all_categories) {
                                    this.categories[i].sub_category[s].status = true;
                                } else {
                                    this.categories[i].sub_category[s].status = false;
                                }
                            }
                        }
                    }
                },
                checkAllCategoriesFilter: function() {
                    for (let i = 0; i < this.categoriesFilter.length; i++) {
                        if (this.categoriesFilter[i].sub_category) {
                            for (let s = 0; s < this.categoriesFilter[i].sub_category.length; s++) {
                                if (this.check_all_categories) {
                                    this.categoriesFilter[i].sub_category[s].status = true;
                                } else {
                                    this.categoriesFilter[i].sub_category[s].status = false;
                                }
                            }
                        }
                    }
                    this.search_services(false);
                },
                checkAllExperiences: function() {
                    this.label_select_experiences = "";
                    for (let i = 0; i < this.experiences.length; i++) {
                        if (this.check_all_experiences) {
                            this.experiences[i].status = true;
                        } else {
                            this.experiences[i].status = false;
                        }
                    }
                },
                checkAllExperiencesFilter: function() {
                    for (let i = 0; i < this.experiencesFilter.length; i++) {
                        if (this.check_all_experiences) {
                            this.experiencesFilter[i].status = true;
                        } else {
                            this.experiencesFilter[i].status = false;
                        }
                    }
                    this.search_services(false);
                },
                checkExperiencesFilter: function() {
                    let checkAll = true;
                    let checkExperiences = [];
                    for (let i = 0; i < this.experiencesFilter.length; i++) {
                        if (!this.experiencesFilter[i].status) {
                            checkAll = false;
                        } else {
                            checkExperiences.push(this.experiencesFilter[i].code);
                        }
                    }
                    this.experience_ids = checkExperiences;
                    this.check_all_experiences = checkAll;
                    this.search_services(false);
                },
                checkAllTypeServiceFilter: function() {
                    for (let i = 0; i < this.service_typesFilter.length; i++) {
                        if (this.check_all_type_services) {
                            this.service_typesFilter[i].status = true;
                        } else {
                            this.service_typesFilter[i].status = false;
                        }
                    }
                    this.search_services(false);
                },
                checkTypeServiceFilter: function() {
                    let checkAll = true;
                    let checkTypeServices = [];
                    for (let i = 0; i < this.service_typesFilter.length; i++) {
                        if (!this.service_typesFilter[i].status) {
                            checkAll = false;
                        } else {
                            checkTypeServices.push(this.service_typesFilter[i].code);
                        }
                    }
                    this.service_type_id = checkTypeServices;
                    this.check_all_type_services = checkAll;
                    this.search_services(false);
                },
                checkExperiences: function() {
                    let checkAll = true;
                    let checkExperiences = [];
                    let checkExperiencesName = [];
                    for (let i = 0; i < this.experiences.length; i++) {
                        if (!this.experiences[i].status) {
                            checkAll = false;
                        } else {
                            checkExperiences.push(this.experiences[i].code);
                            checkExperiencesName.push(this.experiences[i].label);
                        }
                    }
                    this.label_select_experiences = checkExperiencesName.join(", ");
                    this.experience_ids = checkExperiences;
                    this.check_all_experiences = checkAll;
                },
                checkSubCategories: function() {
                    let checkAll = true;
                    let checkCategories = [];
                    let checkCategoriesName = [];
                    for (let i = 0; i < this.categories.length; i++) {
                        if (this.categories[i].sub_category) {
                            for (let s = 0; s < this.categories[i].sub_category.length; s++) {
                                if (!this.categories[i].sub_category[s].status) {
                                    checkAll = false;
                                } else {
                                    checkCategories.push(this.categories[i].sub_category[s].id);
                                    checkCategoriesName.push(this.categories[i].sub_category[s].name);
                                }
                            }
                        }
                    }

                    this.label_select_category = checkCategoriesName.join(", ");
                    this.category_ids = checkCategories;
                    this.check_all_categories = checkAll;
                },
                checkSubCategoriesFilter: function() {
                    let checkAll = true;
                    let checkCategories = [];
                    for (let i = 0; i < this.categoriesFilter.length; i++) {
                        if (this.categoriesFilter[i].sub_category) {
                            for (let s = 0; s < this.categoriesFilter[i].sub_category.length; s++) {
                                if (!this.categoriesFilter[i].sub_category[s].status) {
                                    checkAll = false;
                                } else {
                                    checkCategories.push(this.categoriesFilter[i].sub_category[s].id);
                                }
                            }
                        }
                    }
                    this.category_ids = checkCategories;
                    this.check_all_categories = checkAll;
                    this.search_services(false);
                },
                checkAllClassificationsFilter: function() {
                    for (let i = 0; i < this.classificationsFilter.length; i++) {
                        if (this.check_all_classifications) {
                            this.classificationsFilter[i].status = true;
                        } else {
                            this.classificationsFilter[i].status = false;
                        }
                    }
                    this.search_services(false);
                },
                checkClassificationsFilter: function() {
                    let checkAll = true;
                    let checkClassifications = [];
                    for (let i = 0; i < this.classificationsFilter.length; i++) {
                        if (!this.classificationsFilter[i].status) {
                            checkAll = false;
                        } else {
                            checkClassifications.push(this.classificationsFilter[i].code);
                        }
                    }
                    this.classification_ids = checkClassifications;
                    this.check_all_classifications = checkAll;
                    this.search_services(false);
                },
                clearOriginsAndDestinies: function() {
                    this.origins_select = [];
                    this.destinations_select = [];
                },
                getServiceDate: function() {
                    return moment(this.search_destinies.date).lang(localStorage.getItem("lang")).format("ddd D MMM");
                },
                changeQuantityAdult: function(event) {
                    this.quantity_persons.adults = parseInt(event.target.value);
                },
                changeQuantityChild: function(event) {
                    this.quantity_persons.child = parseInt(event.target.value);
                    this.quantity_persons.age_childs.splice(1, 4);
                    for (let i = 1; i < event.target.value; i++) {
                        this.quantity_persons.age_childs.splice((i + 1), 0, { age: 1 });
                    }
                },
                get_data_origin() {
                    // this.destiny / destiny_country / destiny_district
                    // {code: "89,1610,128", label: "Perú, Lima, Lima"} // http://prntscr.com/123oeip
                    let data_origin = "";
                    if (this.origin !== "") {
                        let code_ = this.origin_country.code + "," + this.origin.code;
                        let label_ = this.origin_country.label + "," + this.origin.label;
                        if (this.origin_district !== "") {
                            let origin_district_label = "";
                            this.origins_additional_select.forEach((d) => {
                                if (d.code === this.origin_district) {
                                    origin_district_label = d.label;
                                }
                            });

                            code_ += "," + this.origin_district;
                            label_ += "," + origin_district_label;
                        }
                        data_origin = {
                            code: code_,
                            label: label_
                        };
                    }else{
                        let code_ = '89'
                        let label_ = 'Perú';

                        if(this.origin_country !== ""){
                            code_ = this.origin_country.code;
                            label_ = this.origin_country.label;
                        }

                        data_origin = {
                            code: code_,
                            label: label_
                        }
                    }

                    return data_origin;
                },
                get_data_destiny() {
                    // this.destiny / destiny_country / destiny_district
                    // {code: "89,1610,128", label: "Perú, Lima, Lima"} // http://prntscr.com/123oeip
                    let data_destiny = "";
                    if (this.destiny !== "") {
                        let code_ = this.destiny_country.code + "," + this.destiny.code;
                        let label_ = this.destiny_country.label + "," + this.destiny.label;
                        if (this.destiny_district !== "") {
                            let destiny_district_label = "";
                            this.destinations_additional_select.forEach((d) => {
                                if (d.code === this.destiny_district) {
                                    destiny_district_label = d.label;
                                }
                            });

                            code_ += "," + this.destiny_district;
                            label_ += "," + destiny_district_label;
                        }
                        data_destiny = {
                            code: code_,
                            label: label_
                        };
                    }else{
                        let code_ = "89";
                        let label_ = "Perú";

                        if(this.destiny_country.code !== ""){
                            code_ = this.destiny_country.code;
                            label_ = this.destiny_country.label;
                        }

                        data_destiny = {
                            code: code_,
                            label: label_
                        }
                    }

                    return data_destiny;
                },
                search_services: function(blockPage) {

                    this.notSearch = true;
                    this.page = 1;
                    this.blockPage = blockPage;

                    let params = {
                        module: "service",
                        destiny_ini: {
                            origin: this.origin,
                            origin_country: this.origin_country,
                            origin_district: this.origin_district
                        },
                        destiny_fin: {
                            destiny_country: this.destiny_country,
                            destiny: this.destiny,
                            destiny_district: this.destiny_district
                        },
                        date: this.date,
                        service_type_id: this.service_type_id,
                        serviceTypeSelected: this.serviceTypeSelected,
                        category_ids: this.category_ids,
                        check_all_categories: this.check_all_categories,
                        label_select_category: this.label_select_category,


                        experience_ids: this.experience_ids,
                        check_all_experiences: this.check_all_experiences,
                        label_select_experiences: this.label_select_experiences,

                        classificationSelected: this.classificationSelected,
                        classification_ids: this.classification_ids,
                        check_all_classifications: this.check_all_classifications,
                        // quantity_adults: this.search_destinies[0].quantity_adults,
                        // quantity_child: this.search_destinies[0].quantity_child,
                        quantity_persons: this.quantity_persons,
                        filter: this.filter
                    };

                    localStorage.setItem("search_params", JSON.stringify(params));


                    let data = {
                        lang: localStorage.getItem("lang"),
                        client_id: this.client_id,
                        origin: this.get_data_origin(),
                        destiny: this.get_data_destiny(),
                        date: (this.date === "") ? moment().format("YYYY-MM-DD") : moment(this.date, "DD/MM/YYYY").format("YYYY-MM-DD"),
                        quantity_persons: this.quantity_persons,
                        type: this.service_type_id,
                        category: (this.check_all_categories) ? "all" : this.category_ids,
                        experience: (this.check_all_experiences) ? "all" : this.experience_ids,
                        classification: (this.check_all_classifications) ? "all" : this.classification_ids,
                        filter: this.filter,
                        limit: this.limit,
                        page: this.page
                    };
                    axios.post(
                        "services/available",
                        data
                    ).then((result) => {
                        if (result.data.success === true) {
                            this.busy = true;

                            if (localStorage.getItem("user_type_id") == 4) {
                                const hasResults = result.data.data.services.length > 0;
                                dataLayer.push({
                                    event: "search",
                                    results: hasResults ? "true" : "false"
                                });

                                let servicesSearch = [];
                                result.data.data.services.forEach((s) => {
                                    servicesSearch.push({
                                        "item_id": s.id,
                                        "item_sku": s.code,
                                        "item_name": s.descriptions.name_gtm.toLocaleUpperCase("en"),
                                        "price": s.price_per_person,
                                        "item_brand": s.origin.state_iso,
                                        "item_category": "service",
                                        "item_category2": "single_product",
                                        "item_list_id": null,
                                        "item_list_name": null
                                    });
                                });

                                dataLayer.push({
                                    "event": "view_item",
                                    "currency": "USD",
                                    "value": null,
                                    "package_id": null,
                                    "package_name": null,
                                    "items": servicesSearch
                                });
                            }


                            result.data.data.services.forEach((s) => {
                                s.total_price_per_person = s.price_per_person;
                                s.components.forEach((c) => {
                                    c.show_replace = false;
                                    c.will_remove = false;
                                    c.removed = false;
                                    c.collapsed = false;
                                    c.substitute_selected = "";
                                    s.total_price_per_person += c.price_per_person;

                                    c.repeated_total = 0;
                                    if (c.repeated === undefined) {
                                        s.components.forEach((cc) => {
                                            if (cc.service_id === c.service_id) {
                                                if (c.repeated_total === 0) {
                                                    cc.repeated = false;
                                                } else {
                                                    cc.repeated = true;
                                                }
                                                c.repeated_total++;
                                            }
                                        });
                                    }

                                });
                                s.total_price_per_person += s.supplements.total_price_per_person;

                            });

                            //ordeno de precio menor a mayor
                            result.data.data.services.sort(
                                (servicea, serviceb) => servicea.price_per_person - serviceb.price_per_person);

                            this.services_original = result.data.data.services;
                            this.services = result.data.data.services;
                            this.min_price = result.data.data.min_price_search;
                            this.max_price = result.data.data.max_price_search;
                            this.quantity_services = result.data.data.quantity_services;
                            this.search_destinies.token_search = result.data.data.token_search;
                            this.search_destinies.destiny = (result.data.data.search_parameters.destiny.label != "null")
                                ? result.data.data.search_parameters.destiny.label
                                : "";
                            this.search_destinies.origin = (result.data.data.search_parameters.origin.label != "null")
                                ? result.data.data.search_parameters.origin.label
                                : "";
                            this.search_destinies.date = result.data.data.search_parameters.date;
                            let quantity_adults = result.data.data.search_parameters.quantity_persons.adults;
                            let quantity_child = result.data.data.search_parameters.quantity_persons.child;
                            this.search_destinies.quantity_persons_search = quantity_adults + quantity_child;

                        } else {
                            this.services_original = [];
                            this.services = [];
                            this.quantity_services = 0;
                            this.min_price = 0;
                            this.max_price = 0;
                            this.$toast.warning(result.message, {
                                position: "top-right"
                            });
                        }
                        this.tabsDestinies = true;
                        this.blockPage = false;
                        this.notSearch = false;
                    }).catch((e) => {
                        this.notSearch = false;
                        this.blockPage = false;
                        console.log(e);
                    });

                },
                filterView: function(view, x) {
                    let filterView = document.getElementById("filterView_" + view);
                    $(".content-vista div").removeClass("active");
                    filterView.setAttribute("class", "col lg-2 font text-center active");
                    this.viewMode = !this.viewMode;
                },
                getIncluions: function(inclusions, type) {
                    if (inclusions.length > 0) {
                        let inclusion = [];
                        if (type === 1 && inclusions[0].include) {
                            inclusion = inclusions[0].include;
                        } else if (type === 0 && inclusions[0].no_include) {
                            inclusion = inclusions[0].no_include;
                        }
                        var inclusion_concat = [];
                        inclusion.forEach(function(element) {
                            inclusion_concat.push(element.name);
                        });
                        return inclusion_concat.join(",");
                    } else {
                        return "";
                    }
                },
                openModalDetail: function(service, view) {
                    this.view = view;
                    this.service_detail_selected = service;
                    this.service_detail_selected.real_notes = service.descriptions.summary;
                    this.service_detail_selected.real_notes_commercial = service.commercial_descriptions.summary;
                    $(".modal-servicios").modal();
                },
                openModalDetailPolicy: function(service, view) {
                    this.view = view;
                    this.service_detail_selected = service;
                    $(".modal-servicios").modal();
                },
                closeModalDetail: function() {
                    document.getElementById("modal_detail_close").click();
                },
                infiniteHandler: function($state) {

                    if (this.page > 0 && !this.notSearch) {
                        this.notSearch = true;
                        this.page = this.page + 1;
                        let data = {
                            lang: localStorage.getItem("lang"),
                            client_id: this.client_id,
                            origin: this.get_data_origin(),
                            destiny: this.get_data_destiny(),
                            date: (this.date === "") ? moment().format("YYYY-MM-DD") : moment(this.date, "DD/MM/YYYY").format("YYYY-MM-DD"),
                            quantity_persons: this.quantity_persons,
                            type: this.service_type_id,
                            category: (this.check_all_categories) ? "all" : this.category_ids,
                            experience: (this.check_all_experiences) ? "all" : this.experience_ids,
                            classification: this.classification_id,
                            filter: this.filter,
                            limit: this.limit,
                            page: this.page
                        };
                        axios.post(
                            "services/available",
                            data
                        ).then((result) => {
                            if (result.data.success === true) {
                                result.data.data.services.forEach((s) => {
                                    s.total_price_per_person = s.price_per_person;
                                    s.components.forEach((c) => {
                                        c.show_replace = false;
                                        c.will_remove = false;
                                        c.collapsed = false;
                                        c.substitute_selected = "";
                                        s.total_price_per_person += c.price_per_person;
                                    });
                                });

                                this.next_page = result.data.data.quantity_services;
                                this.search_destinies.token_search = result.data.data.token_search;
                                this.search_destinies.token_search = result.data.data.token_search;
                                this.search_destinies.destiny = (result.data.data.search_parameters.destiny.label != "null")
                                    ? result.data.data.search_parameters.destiny.label
                                    : "";
                                this.search_destinies.origin = (result.data.data.search_parameters.origin.label != "null")
                                    ? result.data.data.search_parameters.origin.label
                                    : "";
                                this.search_destinies.date = result.data.data.search_parameters.date;
                                let quantity_adults = result.data.data.search_parameters.quantity_persons.adults;
                                let quantity_child = result.data.data.search_parameters.quantity_persons.child;
                                this.search_destinies.quantity_persons_search = quantity_adults + quantity_child;
                                this.loadMore($state, result);

                            } else {
                                let _result = {
                                    data: {
                                        data: {
                                            services: []
                                        }
                                    }
                                };
                                this.loadMore($state, _result);
                                this.$toast.warning(result.data.message, {
                                    position: "top-right"
                                });
                            }

                            this.notSearch = false;
                            //ordeno de precio menor a mayor
                            // this.services.sort((servicea, serviceb) => servicea.price_per_person - serviceb.price_per_person)
                        }).catch((e) => {
                            this.notSearch = false;
                            console.log(e);
                        });
                    }
                },
                loadMore: function($state, response) {
                    let data = response.data.data.services;
                    if (data.length) {
                        if (this.quantity_services === data.length) {
                            $state.complete();
                        } else {
                            data.forEach(item => {
                                this.services.push(item);
                            });
                            $state.loaded();
                        }
                    } else {
                        $state.complete();
                    }
                },
                filterByName: function() {
                    this.filter = this.filterFilter;
                    this.search_services(false);
                },
                changePage(page) {
                    this.pagination.current_page = page;
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
                formatDate: function(starDate) {
                    return moment(starDate).lang(localStorage.getItem("lang")).format("ddd D MMM");
                },
                getDateSearchDestiny: function(date) {
                    return moment(date).lang(localStorage.getItem("lang")).format("L");
                },
                getCartContent: function() {
                    axios.get(
                        baseURL + "cart"
                    ).then((result) => {
                        if (result.data.success === true) {

                            // + Prices of multiservices
                            result.data.cart.total_cart = parseFloat(result.data.cart.total_cart.replace(/[^\d\.\-eE+]/g, ""));
                            result.data.cart.services.forEach((s) => {
                                s.service.components.forEach((c) => {
                                    s.total_service += c.total_amount;
                                    result.data.cart.total_cart += c.total_amount;
                                });
                                // console.log(s.service.supplements)
                                // s.total_service += s.service.supplements.total_amount
                                // result.data.cart.total_cart += s.service.supplements.total_amount
                                this.services.forEach((item, i) => {
                                    Vue.set(item, "taken", false);
                                    if (item.id == s.service_id) {
                                        Vue.set(item, "taken", true);
                                    }
                                });
                            });
                            // + Prices of multiservices

                            this.cart = result.data.cart;
                            this.cart_quantity_items = result.data.cart.quantity_items;
                        }
                    }).catch((e) => {
                        console.log(e);
                    });
                },
                updateMenu: function() {
                    this.$emit("updateMenu");
                },
                deleteCartItem: function(service) {
                    this.blockPage = true;
                    axios.delete(baseURL + "cart/" + service.cart_items_id).then((result) => {
                        if (result.data.success) {
                            this.updateMenu();
                            service.taken = false;
                            service.cart_items_id = "";
                            this.getCartContent();
                        }
                        this.blockPage = false;
                    }).catch((e) => {
                        console.log(e);
                    });
                },
                willAddCart: function(service) {
                    if (service.supplements.optional_supplements.length > 0) {
                        let parameters_supplements = [];
                        for (let e = 0; e < service.supplements.optional_supplements.length; e++) {
                            service.supplements.optional_supplements[e].selected = false;
                            var dates = [];
                            if (service.supplements.optional_supplements[e].days.not_charge.length === 0) {
                                dates = service.supplements.optional_supplements[e].days.charge;
                            }
                            if (service.supplements.optional_supplements[e].days.charge.length === 0 && service.supplements.optional_supplements[e].days.not_charge.length > 0) {
                                dates = service.supplements.optional_supplements[e].days.not_charge;
                            }
                            parameters_supplements[e] = {
                                adults: service.quantity_adult,
                                child: service.quantity_child,
                                dates: dates
                            };
                        }

                        this.parameters_supplements = parameters_supplements;
                        console.log(this.parameters_supplements);
                        this.openModalDetail(service, "addSupplementsService");
                    } else {
                        this.addCart(service);
                        if (localStorage.getItem("user_type_id") == 4) {
                            dataLayer.push({
                                "event": "add_to_cart",
                                "currency": "USD",
                                "value": service.price_per_person,
                                "package_id": null,
                                "package_name": null,
                                "items": [
                                    {
                                        "item_id": service.id,
                                        "item_sku": service.code,
                                        "item_name": service.descriptions.name_gtm.toLocaleUpperCase("en"),
                                        "price": service.price_per_person,
                                        "item_brand": service.origin.state_iso,
                                        "item_category": "service",
                                        "item_category2": "single_product",
                                        "item_list_id": null,
                                        "item_list_name": null
                                    }
                                ]
                            });
                        }
                    }
                },
                omitir: function(service) {
                    this.reservation_time = "00:00";
                    this.closeModalDetail();
                    this.addCart(service);
                },
                getSupplementSelected: function(service) {
                    let supplement_selected = [];
                    if (service.supplements.optional_supplements.length > 0) {
                        for (let e = 0; e < service.supplements.optional_supplements.length; e++) {
                            if (service.supplements.optional_supplements[e].selected) {
                                supplement_selected.push({
                                    token_search: service.supplements.optional_supplements[e].token_search,
                                    adults: this.parameters_supplements[e].adults,
                                    child: this.parameters_supplements[e].child,
                                    dates: this.getOnlyDates(this.parameters_supplements[e].dates)
                                });
                            }
                        }
                    }
                    return supplement_selected;
                },
                validationsAllParameterSupplements: function(service) {
                    let validate = true;
                    if (service.supplements.optional_supplements.length > 0) {
                        for (let e = 0; e < service.supplements.optional_supplements.length; e++) {
                            if (service.supplements.optional_supplements[e].selected) {
                                if (service.supplements.optional_supplements[e].days.charge.length === 0 && this.parameters_supplements[e].dates.length === 0) {
                                    validate = false;
                                    this.$toast.warning("Debe seleccionar al menos una fecha para los suplementos seleccionados", {
                                        position: "top-right"
                                    });
                                }
                            }
                        }
                    }
                    return validate;
                },
                addCart: function(service) {
                    // this.reservation_time = service.reservation_time
                    let supplement_selected = this.getSupplementSelected(service);
                    if (supplement_selected.length > 0) {
                        let validate_ = this.validationsAllParameterSupplements(service);
                        if (!validate_) {
                            return false;
                        }
                    }
                    this.blockPage = true;
                    axios.get(
                        baseExternalURL + "services/check/token_search/" + this.search_destinies.token_search
                    ).then((result) => {
                        if (result.data.success) {
                            axios.post(
                                baseURL + "cart/add", {
                                    product_type: "service",
                                    token_search: service.token_search,
                                    date_from: (this.date === "") ? moment().format("YYYY-MM-DD") : moment(this.date,
                                        "DD/MM/YYYY").format("YYYY-MM-DD"),
                                    service_id: service.id,
                                    rate_id: service.rate.id,
                                    rate: service.rate,
                                    service_supplements: supplement_selected,
                                    reservation_time: this.reservation_time,
                                    service_name: service.descriptions.name,
                                    search: service,
                                    room: this.quantity_persons
                                }
                            ).then((result) => {
                                if (result.data.success) {
                                    if (supplement_selected.length > 0) {
                                        this.closeModalDetail();
                                    }
                                    service.taken = true;
                                    service.cart_items_id = result.data.cart_items_id;
                                    localStorage.setItem("reservation", true);
                                    this.updateMenu();
                                    this.blockPage = false;
                                    this.getCartContent();
                                }
                            }).catch((error) => {
                                this.blockPage = false;
                                // 👇 Captura del error 422 (validación)
                                if (error.response && error.response.status === 422) {
                                    let message = error.response.data.message || "Ocurrió un error de validación.";
                                    this.$toast.warning(message, {
                                        position: "top-right"
                                    });
                                } else {
                                    console.error(error);
                                    this.$toast.error("Ocurrió un error inesperado.", {
                                        position: "top-right"
                                    });
                                }
                            });
                        }
                    }).catch((e) => {
                        console.log(e);
                        this.blockPage = false;
                    });
                },
                groupByCollection: function(collection, property) {
                    var i = 0, val, index,
                        values = [], result = [];
                    for (; i < collection.length; i++) {
                        val = collection[i][property];
                        index = values.indexOf(val);
                        if (index > -1)
                            result[index].push(collection[i]);
                        else {
                            values.push(val);
                            result.push([collection[i]]);
                        }
                    }
                    return result;
                },
                validationsParameterSupplements: function(supplement_optional, index) {
                    let validate = true;
                    if (supplement_optional.days.charge.length === 0) {
                        if (this.parameters_supplements[index].dates.length === 0) {
                            validate = false;
                            this.$toast.warning("Debe seleccionar las fechas para el suplemento", {
                                position: "top-right"
                            });
                        }
                    }
                    return validate;
                },
                getOnlyDates: function(dates) {
                    let dates_array = [];
                    for (let i = 0; i < dates.length; i++) {
                        dates_array.push(dates[i].day);
                    }
                    return dates_array;
                },
                filterSupplement: function(supplement_optional, index) {
                    let validate = this.validationsParameterSupplements(supplement_optional, index);
                    if (validate) {
                        let dates_ = this.parameters_supplements[index].dates;
                        let filterDates = this.getOnlyDates(dates_);
                        axios.post("services/supplement/add",
                            {
                                adults: this.parameters_supplements[index].adults,
                                child: this.parameters_supplements[index].child,
                                dates: filterDates,
                                service_id: this.service_detail_selected.id,
                                supplement_id: supplement_optional.id,
                                token_search: this.service_detail_selected.token_search
                            }
                        ).then((result) => {
                            if (result.data.success) {
                                supplement_optional.rate.price_per_adult = result.data.data.price_per_adult;
                                supplement_optional.rate.price_per_child = result.data.data.price_per_child;
                                supplement_optional.rate.total_adult_amount = result.data.data.total_adult_amount;
                                supplement_optional.rate.total_child_amount = result.data.data.total_child_amount;
                                supplement_optional.rate.price_per_person = result.data.data.price_per_person;
                                supplement_optional.rate.total_amount = result.data.data.total_amount;
                                this.totalSupplement();
                                this.totalServiceSupplement();
                            } else {
                                this.$toast.warning("No se encontraron tarifas para el suplemento", {
                                    position: "top-right"
                                });
                            }
                        }).catch((e) => {
                            console.log(e);
                        });
                    }
                }
            }
        });
    </script>
@endsection
