@extends('layouts.app')
@section('content')
    <section class="package_modify container-fluid" style="height: 500px;">
        <b-skeleton-wrapper :loading="loading_general">
            <template #loading>
                <b-row>
                    <b-col cols="12" class="mt-3 mb-4">
                        <b-skeleton class="mt-3" width="15%" height="5%"></b-skeleton>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col cols="10" class="mt-3 mb-4">
                        <b-skeleton class="mt-3" width="25%" height="5%"></b-skeleton>
                    </b-col>
                    <b-col cols="2" class="mt-3 mb-4">
                        <b-skeleton class="mt-3 float-right" width="30%" height="5%"></b-skeleton>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col cols="3" class="mt-3 mb-4">
                        <b-skeleton class="mt-3" width="100%" height="20px"></b-skeleton>
                    </b-col>
                    <b-col cols="3" class="mt-3 mb-4">
                        <b-skeleton class="mt-3" width="100%" height="20px"></b-skeleton>
                    </b-col>
                    <b-col cols="3" class="mt-3 mb-4">
                        <b-skeleton class="mt-3" width="100%" height="20px"></b-skeleton>
                    </b-col>
                    <b-col cols="3" class="mt-3 mb-4">
                        <b-skeleton class="mt-3" width="100%" height="20px"></b-skeleton>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col cols="3" class="mt-3 mb-4">
                        <b-skeleton class="mt-3" width="100%" height="75px"></b-skeleton>
                    </b-col>
                    <b-col cols="3" class="mt-3 mb-4">
                        <b-skeleton class="mt-3" width="100%" height="75px"></b-skeleton>
                    </b-col>
                    <b-col cols="3" class="mt-3 mb-4">
                        <b-skeleton class="mt-3" width="100%" height="75px"></b-skeleton>
                    </b-col>
                    <b-col cols="3" class="mt-3 mb-4">
                        <b-skeleton class="mt-3" width="100%" height="75px"></b-skeleton>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col cols="3" class="mt-3 mb-4">
                        <b-skeleton type="input"></b-skeleton>
                    </b-col>
                    <b-col cols="3" class="mt-3 mb-4">
                        <b-skeleton type="input"></b-skeleton>
                    </b-col>
                    <b-col cols="3" class="mt-3 mb-4">
                        <b-skeleton type="input"></b-skeleton>
                    </b-col>
                    <b-col cols="3" class="mt-3 mb-4">
                        <b-skeleton type="input"></b-skeleton>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col cols="3" class="mt-3 mb-4">
                        <b-skeleton type="input"></b-skeleton>
                    </b-col>
                    <b-col cols="3" class="mt-3 mb-4">
                        <b-skeleton type="input"></b-skeleton>
                    </b-col>
                    <b-col cols="3" class="mt-3 mb-4">
                        <b-skeleton type="input"></b-skeleton>
                    </b-col>
                    <b-col cols="3" class="mt-3 mb-4">
                        <b-skeleton type="input"></b-skeleton>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col cols="3" class="mt-3 mb-4">
                        <b-skeleton type="input"></b-skeleton>
                    </b-col>
                    <b-col cols="3" class="mt-3 mb-4">
                        <b-skeleton type="input"></b-skeleton>
                    </b-col>
                    <b-col cols="3" class="mt-3 mb-4">
                        <b-skeleton type="input"></b-skeleton>
                    </b-col>
                    <b-col cols="3" class="mt-3 mb-4">
                        <b-skeleton type="input"></b-skeleton>
                    </b-col>
                </b-row>
            </template>
            <h4 class="py-5 text-danger font-weight-bold">Modifica tu paquete</h4>
            <div class="d-flex">
                <h2>@{{ quote_name }} <span v-if="nights > 0" class="text-danger">@{{ nights + 1}} días / @{{ nights }} noches</span>
                </h2>
                <div class="d-flex">

                </div>
            </div>
            <div class="detail-reservation d-flex justify-content-between">
                <div class="box-content">
                    <p>
                        <strong>Días:</strong> @{{ quote_date_start }} <span
                            v-if="nights > 0"> - @{{ quote_date_end }}</span>
                    </p>
                </div>
                <div class="box-content d-flex">
                    <p class="mr-2"><strong>{{ trans('quote.label.adults') }}:</strong> @{{ quantity_persons.adults }}
                    </p>
                    <p><strong>{{ trans('quote.label.child') }}:</strong> @{{ quantity_persons.child }} </p>
                </div>
                <div class="box-content">
                    <p>
                        <strong>Habitaciones:</strong>
                        <span v-if="control_service_selected_general.single > 0"> SGL @{{ control_service_selected_general.single }}</span>
                        <span v-if="control_service_selected_general.double > 0"> DBL @{{ control_service_selected_general.double }}</span>
                        <span v-if="control_service_selected_general.triple > 0"> TPL @{{ control_service_selected_general.triple }}</span>
                    </p>
                </div>
                <div class="box-content">
                    <p>
                        <strong>Categoría del hotel:</strong>
                        <span
                            v-for="qCateg in quote_open.categories">@{{ qCateg.type_class.translations[0].value }}</span>
                    </p>
                </div>
                <div class="box-content">
                    <p><strong>Servicio:</strong> @{{quote_service_type_name}} </p>
                </div>
            </div>
            <div class="package_modify-search">
                <div class="tabs">
                    <div class="tab">
                        <input type="radio" name="css-tabs" id="tab-1" checked class="tab-switch">
                        <label for="tab-1" class="tab-label tab-experience d-block" @click="setGroupTab(1)">
                            <span class="icon-ac-directions_run"></span>
                            <p>Experiencias</p>
                        </label>
                        <div class="tab-content">
                            <form class="form-modify d-flex justify-content-between align-items-center">
                                <div class="mx-2 my-3">
                                    <label for="add_service_date_experience">Dia de inicio:</label>
                                    <date-picker class="date-search" v-model="add_service_date" :config="optionsR"
                                                 name="add_service_date_experience"
                                                 id="add_service_date_experience"></date-picker>
                                </div>
                                <div class="mx-2 my-3">
                                    <label for="">Ciudad:</label>
                                    <div class="input-group input-modify">
                                        <v-select :options="destinations_select"
                                                  v-model="destiny_service"
                                                  placeholder="{{trans('service.label.select_destination')}}"
                                                  class="form-control"></v-select>
                                    </div>
                                </div>
                                <div class="mx-2 my-3">
                                    <label for="">Pasajeros:</label>
                                    <div class="dropdown">
                                        <a class="nav-link link-icon dropdown-toggle" href="#" role="button"
                                           id="dropdownPaxExp"
                                           data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <div class="d-flex justify-content-between">
                                            <span class="text-left">
                                                <span class="mr-2"><i class="uil uil-user-md mr-1"></i> {{trans('service.label.adults')}} @{{service_quantity_persons.adults}} </span>
                                                <span><i class="uil uil-kid mr-1"></i> {{trans('service.label.children')}} @{{service_quantity_persons.child}}</span>
                                            </span>
                                                <i class="fas fa-sort-down ml-3"></i>
                                            </div>
                                        </a>
                                        <div class="dropdown-menu" aria-labelledby="dropdownPaxExp">
                                            <div class="p-3">
                                                <div>
                                                    <div class="d-flex justify-content-between mb-3">
                                                        <div clas="">
                                                            <h5>{{trans('service.label.adults')}}</h5>
                                                            <vue-numeric-input v-model="service_quantity_persons.adults"
                                                                               :min="1"
                                                                               controls-type="updown"
                                                                               :precision="0" id="adult_exp">
                                                            </vue-numeric-input>
                                                        </div>
                                                        <div>
                                                            <h5>{{trans('service.label.children')}}</h5>
                                                            <vue-numeric-input v-model="service_quantity_persons.child"
                                                                               @input="changeQuantityServiceChild"
                                                                               @change="changeQuantityServiceChild"
                                                                               :min="0"
                                                                               controls-type="updown"
                                                                               :precision="0" id="child_exp">
                                                            </vue-numeric-input>
                                                        </div>
                                                    </div>
                                                    <h5 v-if="service_quantity_persons.child >=1">{{trans('service.label.age')}}</h5>
                                                    <div class="d-flex">
                                                        <div class="form-group mr-2"
                                                             v-if="service_quantity_persons.child >=1"
                                                             v-for="(age_child_slot,index_age_child) in service_quantity_persons.age_childs">
                                                            <select class="form-control"
                                                                    v-model="service_quantity_persons.age_childs[index_age_child].age">
                                                                <option v-for="age_child in 11" :value="age_child">@{{
                                                                    age_child }}
                                                                </option>
                                                            </select>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mx-2 my-3">
                                    <label for="">Tipo de experiencia:</label>
                                    <div class="dropdown">
                                        <a class="nav-link link-icon dropdown-toggle" href="#" role="button"
                                           id="dropdownMenuInterests"
                                           data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

                                            <div class="d-flex justify-content-between">
                                            <span class="text-left" v-if="selectedExperiencesLabel === ''">
                                                Elige una opción
                                            </span>
                                                <span class="text-left" v-else>
                                                <span v-if="selectedExperiences.length > 1">
                                                 @{{ selectedExperiences.length }} seleccionados
                                                </span>
                                                <span v-else>
                                                @{{ selectedExperiencesLabel }}
                                                </span>
                                            </span>
                                            </div>
                                        </a>
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuInterests">
                                            <div class="d-flex align-items-center dropdown-menu__option"
                                                 v-for="experience in experiences">
                                                <label class="checkbox-ui" @click="selectExperience(experience)">
                                                    <i :class="{'fa fa-check-square' : experience.active, 'far fa-square':!experience.active}"></i>
                                                    @{{ experience.label }}

                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <div class="mx-2 my-3">
                                        <label for="">Tipo de servicio:</label>
                                        <div class="input-group input-modify" style="width: 200px;">
                                            <select name="tab_service_type_id" v-model="tab_service_type_id"
                                                    class="form-control servicio">
                                                <option :value="service_type.id" v-for="service_type in service_types">
                                                    @{{ service_type.translations[0].value }} - @{{ service_type.code }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mx-2 my-3">
                                        <label for="">Duración:</label>
                                        <div class="input-group input-modify" style="width: 240px;">
                                            <div class="form-control duration d-flex">
                                                <select name="tab_service_type_id" v-model="unit_duration"
                                                        class="text_duration">
                                                    <option value="0">Elige la duración</option>
                                                    <option value="5">Minutos</option>
                                                    <option value="1">Horas</option>
                                                    <option value="2">Días</option>
                                                </select>
                                                <input type="number" id="duration_experience" name="duration_experience"
                                                       placeholder="1" class="time_duration" min="1"
                                                       v-model="service_duration">
                                            </div>

                                        </div>
                                    </div>
                                    <div class="mx-2 my-3">
                                        <label for="">Filtro por palabras:</label>
                                        <div class="input-group input-filter">
                                            <input type="text" placeholder="Escribe algo" class="form-control"
                                                   v-model="filter_service_experiences">
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end align-items-center">
                                    <div class="content-btn">
                                        <button type="button" class="btn__clear" @click="cleanFilters()"
                                                :disabled="!!loading_services">
                                            <i class="fas fa-spinner fa-spin" v-if="loading_services"></i>
                                            <span class="icon-ac-x-circle" v-else></span>
                                        </button>
                                        <button type="button" class="btn__search" @click="searchServices()"
                                                :disabled="!!loading_services">
                                            <i class="fas fa-spinner fa-spin" v-if="loading_services"></i>
                                            <span class="icon-search" v-else></span>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="tab">
                        <input type="radio" name="css-tabs" id="tab-2" class="tab-switch">
                        <label for="tab-2" class="tab-label tab-extension d-block" @click="setGroupTab(2)">
                            <i class="uil uil-map-pin-alt"></i>
                            <p>Extensión</p></label>
                        <div class="tab-content">
                            <form class="form-modify d-flex justify-content-between align-items-center">
                                <div class="mx-2 my-3">
                                    <label for="add_extensions_date">Dia de inicio:</label>
                                    <date-picker class="form-control" v-model="add_extensions_date" :config="optionsR"
                                                 name="add_extensions_date"
                                                 id="add_extensions_date"></date-picker>
                                </div>
                                <div class="mx-2 my-3">
                                    <label for="">Duración:</label>
                                    <div class="input-group input-modify" style="width: 240px;">
                                        <div class="form-control duration d-flex">
                                            <select name="tab_service_type_id" v-model="unit_duration"
                                                    class="text_duration">
                                                <option value="0">Elige la duración</option>
                                                <option value="5">Minutos</option>
                                                <option value="1">Horas</option>
                                                <option value="2">Días</option>
                                            </select>
                                            <input type="number" id="duration_experience" name="duration_experience"
                                                   placeholder="1" class="time_duration" min="1"
                                                   v-model="service_duration">
                                        </div>
                                    </div>
                                </div>
                                <div class="mx-2 my-3">
                                    <label for="">Ciudad:</label>
                                    <div class="input-group input-modify">
                                        <v-select :options="destinations_select"
                                                  v-model="destiny_service"
                                                  placeholder="{{trans('service.label.select_destination')}}"
                                                  class="form-control"></v-select>
                                    </div>
                                </div>
                                <div class="mx-2 my-3">
                                    <label for="">Tipo de servicio:</label>
                                    <div class="input-group input-modify">
                                        <select name="tab_service_type_id" v-model="tab_service_type_id"
                                                class="form-control servicio">
                                            <option :value="service_type.id" v-for="service_type in service_types">
                                                @{{ service_type.translations[0].value }} - @{{ service_type.code }}
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end align-items-center">
                                    <div class="mx-2 my-3">
                                        <label for="">Pasajeros:</label>
                                        <div class="dropdown">
                                            <a class="nav-link link-icon dropdown-toggle" href="#" role="button"
                                               id="dropdownPaxExp"
                                               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <div class="d-flex justify-content-between">
                                            <span class="text-left">
                                                <span class="mr-2"><i class="uil uil-user-md mr-1"></i> {{trans('service.label.adults')}} @{{service_quantity_persons.adults}} </span>
                                                <span><i class="uil uil-kid mr-1"></i> {{trans('service.label.children')}} @{{service_quantity_persons.child}}</span>
                                            </span>
                                                    <i class="fas fa-sort-down ml-3"></i>
                                                </div>
                                            </a>
                                            <div class="dropdown-menu" aria-labelledby="dropdownPaxExp">
                                                <div class="p-3">
                                                    <div>
                                                        <div class="d-flex justify-content-between mb-3">
                                                            <div clas="">
                                                                <h5>{{trans('service.label.adults')}}</h5>
                                                                <vue-numeric-input
                                                                    v-model="service_quantity_persons.adults"
                                                                    :min="1"
                                                                    controls-type="updown"
                                                                    :precision="0" id="adult_exp">
                                                                </vue-numeric-input>
                                                            </div>
                                                            <div>
                                                                <h5>{{trans('service.label.children')}}</h5>
                                                                <vue-numeric-input
                                                                    v-model="service_quantity_persons.child"
                                                                    @input="changeQuantityServiceChild"
                                                                    @change="changeQuantityServiceChild"
                                                                    :min="0"
                                                                    controls-type="updown"
                                                                    :precision="0" id="child_exp">
                                                                </vue-numeric-input>
                                                            </div>
                                                        </div>
                                                        <h5 v-if="service_quantity_persons.child >=1">{{trans('service.label.age')}}</h5>
                                                        <div class="d-flex">
                                                            <div class="form-group mr-2"
                                                                 v-if="service_quantity_persons.child >=1"
                                                                 v-for="(age_child_slot,index_age_child) in service_quantity_persons.age_childs">
                                                                <select class="form-control"
                                                                        v-model="service_quantity_persons.age_childs[index_age_child].age">
                                                                    <option v-for="age_child in 11" :value="age_child">
                                                                        @{{
                                                                        age_child }}
                                                                    </option>
                                                                </select>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mx-2 my-3">
                                        <label for="">Filtro por palabras:</label>
                                        <div class="input-group input-filter">
                                            <input type="text" placeholder="Escribe algo" class="form-control"
                                                   v-model="filter_extensions">
                                        </div>
                                    </div>

                                </div>
                                <div class="d-flex justify-content-end align-items-center">
                                    <div class="content-btn">
                                        <button type="button" class="btn__clear" @click="cleanFilters()">
                                            <span class="icon-ac-x-circle"></span>
                                        </button>
                                        <button type="button" class="btn__search" @click="searchServices()">
                                            <span class="icon-search"></span>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="tab">
                        <input type="radio" name="css-tabs" id="tab-3" class="tab-switch">
                        <label for="tab-3" class="tab-label tab-hotel d-block" @click="setGroupTab(3)">
                            <i class="uil uil-building"></i>
                            <p>Hoteles</p></label>
                        <div class="tab-content">
                            <form class="form-modify d-flex justify-content-between align-items-center">
                                <div class="mx-2 my-3" style="width: 150px;">
                                    <label for="">Check in:</label>
                                    <date-picker class="form-control" v-model="add_hotel_date" :config="optionsR"
                                                 name="add_hotel_date"
                                                 id="add_hotel_date"></date-picker>
                                </div>
                                <div class="mx-2 my-3" style="width: 150px;">
                                    <label for="">Check out:</label>
                                    <date-picker class="form-control" v-model="add_hotel_date" :config="optionsR"
                                                 @dp-show="showDatePickerQuote"
                                                 :key="updateDatePickerQuote"></date-picker>
                                </div>
                                <div class="mx-2 my-3">
                                    <label for="">Destino:</label>
                                    <div class="input-group input-modify">
                                        <v-select :options="destinations_select"
                                                  v-model="destiny_service"
                                                  placeholder="{{trans('service.label.select_destination')}}"
                                                  class="form-control"></v-select>
                                    </div>
                                </div>
                                {{----}}
                                <div class="mx-2 my-3">
                                    <label for="">Filtro por palabras:</label>
                                    <div class="input-group input-filter">
                                        <input type="text" placeholder="Escribe algo" class="form-control">
                                    </div>
                                </div>

                                <div class="mx-2 my-3">
                                    <label for="">Pasajeros:</label>
                                    <div class="dropdown">
                                        <a class="nav-link link-icon dropdown-toggle" href="#" role="button"
                                           id="dropdownPax"
                                           data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <div class="d-flex justify-content-between">
                                            <span class="text-left">
                                                <span class="mr-2"><i class="uil uil-user-md mr-1"></i> Adulto 2 </span>
                                                <span><i class="uil uil-kid mr-1"></i> Niño 3</span>
                                            </span>
                                                <i class="fas fa-sort-down ml-3"></i>
                                            </div>
                                        </a>
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuInterests">
                                            <div class="p-3">
                                                <div>
                                                    <div class="d-flex justify-content-between mb-3">
                                                        <div class="">
                                                            <label for="">Adultos</label>
                                                            <input type="number" class="form-control mx-2"
                                                                   style="width:  100px; border-radius: 5px;">
                                                        </div>
                                                        <div>
                                                            <label for="">Niños</label>
                                                            <input type="number" class="form-control mx-2"
                                                                   style="width:  100px; border-radius: 5px;">
                                                        </div>
                                                    </div>
                                                    <h5> Edades</h5>
                                                    <div class="d-flex">
                                                        <input type="number" class="form-control mx-2">
                                                        <input type="number" class="form-control mx-2">
                                                        <input type="number" class="form-control mx-2">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mx-2 my-3">
                                    <label for="">Habitaciones:</label>
                                    <div class="input-group">
                                        <div class="d-flex form-control">
                                            <input type="number" class="mx-2 input-hab" placeholder="SGL">
                                            <input type="number" class="mx-2 input-hab" placeholder="DBL">
                                            <input type="number" class="mx-2 input-hab" placeholder="TPL">

                                        </div>
                                        <span class="dropdown--arrow"></span>
                                    </div>

                                </div>
                                <div class="rating">
                                    <input type="radio" name="rating" id="rating-5">
                                    <label for="rating-5"></label>
                                    <input type="radio" name="rating" id="rating-4">
                                    <label for="rating-4"></label>
                                    <input type="radio" name="rating" id="rating-3">
                                    <label for="rating-3"></label>
                                    <input type="radio" name="rating" id="rating-2">
                                    <label for="rating-2"></label>
                                    <input type="radio" name="rating" id="rating-1">
                                    <label for="rating-1"></label>
                                </div>

                                <div class="d-flex justify-content-end align-items-center">
                                    <div class="content-btn">
                                        <button type="button" class="btn__clear" @click="cleanFilters()">
                                            <span class="icon-ac-x-circle"></span>
                                        </button>
                                        <button type="button" class="btn__search" @click="searchServices()">
                                            <span class="icon-search"></span>
                                        </button>
                                    </div>
                                </div>


                            </form>
                        </div>
                    </div>
                    <div class="tab">
                        <input type="radio" name="css-tabs" id="tab-4" class="tab-switch">
                        <label for="tab-4" class="tab-label tab-food d-block" @click="setGroupTab(4)">
                            <span class="icon-ac-comida-3"></span>
                            <p>Comidas</p>
                        </label>
                        <div class="tab-content">
                            <form class="form-modify d-flex justify-content-between align-items-center">
                                <div class="mx-2 my-3">
                                    <label for="">Fecha:</label>
                                    <date-picker class="form-control" v-model="add_service_date" :config="optionsR"
                                                 name="add_service_date_food"
                                                 id="add_service_date_food"
                                    ></date-picker>
                                </div>
                                <div class="mx-2 my-3">
                                    <label for="">Ciudad:</label>
                                    <div class="input-group input-modify">
                                        <v-select :options="destinations_select"
                                                  v-model="destiny_service"
                                                  placeholder="{{trans('service.label.select_destination')}}"
                                                  class="form-control"></v-select>
                                    </div>
                                </div>
                                <div class="mx-2 my-3">
                                    <label for="">Pasajeros:</label>
                                    <div class="dropdown">
                                        <a class="nav-link link-icon dropdown-toggle" href="#" role="button"
                                           id="dropdownPaxFood"
                                           data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <div class="d-flex justify-content-between">
                                            <span class="text-left">
                                                <span class="mr-2"><i class="uil uil-user-md mr-1"></i> {{trans('service.label.adults')}} @{{service_quantity_persons.adults}} </span>
                                                <span><i class="uil uil-kid mr-1"></i> {{trans('service.label.children')}} @{{service_quantity_persons.child}}</span>
                                            </span>
                                                <i class="fas fa-sort-down ml-3"></i>
                                            </div>
                                        </a>
                                        <div class="dropdown-menu" aria-labelledby="dropdownPaxFood">
                                            <div class="p-3">
                                                <div>
                                                    <div class="d-flex justify-content-between mb-3">
                                                        <div clas="">
                                                            <label for="">{{trans('service.label.adults')}}</label>
                                                            <vue-numeric-input v-model="service_quantity_persons.adults"
                                                                               :min="1"
                                                                               controls-type="updown"
                                                                               name="adult_food"
                                                                               :precision="0" id="adult_food">
                                                            </vue-numeric-input>
                                                        </div>
                                                        <div>
                                                            <label
                                                                for="">{{trans('service.label.children')}}</label><br>
                                                            <vue-numeric-input v-model="service_quantity_persons.child"
                                                                               @input="changeQuantityServiceChild"
                                                                               @change="changeQuantityServiceChild"
                                                                               :min="0"
                                                                               controls-type="updown"
                                                                               :precision="0" id="child_food"
                                                                               name="child_food">
                                                            </vue-numeric-input>
                                                        </div>
                                                    </div>
                                                    <h5 v-if="service_quantity_persons.child >=1">{{trans('service.label.age')}}</h5>
                                                    <div class="d-flex">
                                                        <div class="form-group mr-2"
                                                             v-if="service_quantity_persons.child >=1"
                                                             v-for="(age_child_slot,index_age_child) in service_quantity_persons.age_childs">
                                                            <select class="form-control"
                                                                    v-model="service_quantity_persons.age_childs[index_age_child].age">
                                                                <option v-for="age_child in 11" :value="age_child">@{{
                                                                    age_child }}
                                                                </option>
                                                            </select>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mx-2 my-3">
                                    <label for="tab_category_id">Tipo de comida:</label>
                                    <div class="input-group input-modify">
                                        <div class="input-group input-modify">
                                            <select name="tab_category_id" v-model="tab_category_id"
                                                    class="form-control servicio">
                                                <option :value="food_category.id"
                                                        v-for="food_category in food_categories">
                                                    @{{ food_category.label }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="mx-2 my-3">
                                    <label for="">Filtro por palabras:</label>
                                    <div class="input-group input-filter">
                                        <input type="text" placeholder="Escribe algo" class="form-control"
                                               v-model="filter_service_foods">
                                    </div>
                                </div>

                                <div class="range-price">
                                    <label for="">Rango de precios:</label>
                                    <vue-slider v-model="ranges_price"
                                                :enable-cross="false"
                                                :marks="rangePriceServices"
                                                :tooltip="'always'"
                                                :interval="0.5"
                                                :min="min_price_food"
                                                :max="max_price_food"></vue-slider>
                                </div>
                                <div class="d-flex justify-content-end align-items-center">
                                    <div class="content-btn">
                                        <button type="button" class="btn__clear" @click="cleanFilters()"
                                                :disabled="!!loading_services">
                                            <i class="fas fa-spinner fa-spin" v-if="loading_services"></i>
                                            <span class="icon-ac-x-circle" v-else></span>
                                        </button>
                                        <button type="button" class="btn__search" @click="searchServices()"
                                                :disabled="!!loading_services">
                                            <i class="fas fa-spinner fa-spin" v-if="loading_services"></i>
                                            <span class="icon-search" v-else></span>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="tab">
                        <input type="radio" name="css-tabs" id="tab-5" class="tab-switch">
                        <label for="tab-5" class="tab-label tab-transfers" @click="setGroupTab(5)">
                            <span class="icon-ac-bus"></span>
                            <p>Traslados</p>
                        </label>
                        <div class="tab-content">
                            <form class="form-modify d-flex justify-content-between align-items-center">
                                <div class="mx-2 my-3">
                                    <label for="">Fecha:</label>
                                    <date-picker class="form-control" v-model="add_service_date" :config="optionsR"
                                                 name="add_service_date_translations"
                                                 id="add_service_date_translations"></date-picker>
                                </div>
                                <div class="mx-2 my-3">
                                    <label for="">Origen:</label>
                                    <div class="input-group input-modify">
                                        <v-select :options="origins_select"
                                                  v-model="origin_service"
                                                  placeholder="{{trans('service.label.select_origin')}}"
                                                  class="form-control"></v-select>
                                    </div>
                                </div>
                                <div class="mx-2 my-3">
                                    <label for="">Destino:</label>
                                    <div class="input-group input-modify">
                                        <v-select :options="destinations_select"
                                                  v-model="destiny_service"
                                                  placeholder="{{trans('service.label.select_destination')}}"
                                                  class="form-control"></v-select>
                                    </div>
                                </div>
                                <div class="mx-2 my-3">
                                    <label for="">Tipo de servicio:</label>
                                    <div class="input-group input-modify">
                                        <select name="tab_service_type_id" v-model="tab_service_type_id"
                                                class="form-control servicio">
                                            <option :value="service_type.id" v-for="service_type in service_types">
                                                @{{ service_type.translations[0].value }} - @{{ service_type.code }}
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mx-2 my-3">
                                    <label for="">Pasajeros:</label>
                                    <div class="dropdown">
                                        <a class="nav-link link-icon dropdown-toggle" href="#" role="button"
                                           id="dropdownPaxTras"
                                           data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <div class="d-flex justify-content-between">
                                            <span class="text-left">
                                                <span class="mr-2"><i class="uil uil-user-md mr-1"></i> {{trans('service.label.adults')}} @{{service_quantity_persons.adults}} </span>
                                                <span><i class="uil uil-kid mr-1"></i> {{trans('service.label.children')}} @{{service_quantity_persons.child}}</span>
                                            </span>
                                                <i class="fas fa-sort-down ml-3"></i>
                                            </div>
                                        </a>
                                        <div class="dropdown-menu" aria-labelledby="dropdownPaxTras">
                                            <div class="p-3">
                                                <div>
                                                    <div class="d-flex justify-content-between mb-3">
                                                        <div clas="">
                                                            <label for="">{{trans('service.label.adults')}}</label>
                                                            <vue-numeric-input v-model="service_quantity_persons.adults"
                                                                               :min="1"
                                                                               controls-type="updown"
                                                                               name="adult_food"
                                                                               :precision="0" id="adult_tras"
                                                                               name="adult_tras">
                                                            </vue-numeric-input>
                                                        </div>
                                                        <div>
                                                            <label
                                                                for="">{{trans('service.label.children')}}</label><br>
                                                            <vue-numeric-input v-model="service_quantity_persons.child"
                                                                               @input="changeQuantityServiceChild"
                                                                               @change="changeQuantityServiceChild"
                                                                               :min="0"
                                                                               controls-type="updown"
                                                                               :precision="0" id="child_tras"
                                                                               name="child_tras">
                                                            </vue-numeric-input>
                                                        </div>
                                                    </div>
                                                    <h5 v-if="service_quantity_persons.child >=1">{{trans('service.label.age')}}</h5>
                                                    <div class="d-flex">
                                                        <div class="form-group mr-2"
                                                             v-if="service_quantity_persons.child >=1"
                                                             v-for="(age_child_slot,index_age_child) in service_quantity_persons.age_childs">
                                                            <select class="form-control"
                                                                    v-model="service_quantity_persons.age_childs[index_age_child].age">
                                                                <option v-for="age_child in 11" :value="age_child">@{{
                                                                    age_child }}
                                                                </option>
                                                            </select>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mx-2 my-3">
                                    <label for="">Filtro por palabras:</label>
                                    <div class="input-group input-filter">
                                        <input type="text" placeholder="Escribe algo" class="form-control"
                                               v-model="filter_service_transfers">
                                    </div>

                                </div>
                                <div class="mx-2 my-3">
                                    <div class="d-flex justify-content-start align-items-center mt-5">
                                        <label class="checkbox-ui">
                                            {{--                                        <i class="far fa-square mr-2"></i> --}}
                                            <i class="fa fa-check-square mr-2"></i>Incluir trasladista
                                        </label>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end align-items-center">
                                    <div class="content-btn">
                                        <button type="button" class="btn__clear" @click="cleanFilters()"
                                                :disabled="!!loading_services">
                                            <i class="fas fa-spinner fa-spin" v-if="loading_services"></i>
                                            <span class="icon-ac-x-circle" v-else></span>
                                        </button>
                                        <button type="button" class="btn__search" @click="searchServices()"
                                                :disabled="!!loading_services">
                                            <i class="fas fa-spinner fa-spin" v-if="loading_services"></i>
                                            <span class="icon-search" v-else></span>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </b-skeleton-wrapper>
    </section>
    <section class="package_modify container-fluid">
        <b-skeleton-wrapper :loading="loading_general">
            <template #loading>
                <b-row>
                    <b-col cols="2" class="mt-3 mb-4">
                        <b-skeleton type="input"></b-skeleton>
                    </b-col>
                    <b-col cols="2" class="mt-3 mb-4">
                        <b-skeleton type="input"></b-skeleton>
                    </b-col>
                    <b-col cols="2" class="mt-3 mb-4">
                        <b-skeleton type="input"></b-skeleton>
                    </b-col>
                    <b-col cols="2" class="mt-3 mb-4">
                        <b-skeleton type="input"></b-skeleton>
                    </b-col>
                    <b-col cols="2" class="mt-3 mb-4">
                        <b-skeleton type="input"></b-skeleton>
                    </b-col>
                    <b-col cols="2" class="mt-3 mb-4">
                        <b-skeleton type="input"></b-skeleton>
                    </b-col>
                </b-row>
            </template>
            <div class="" style="width: 100%;">
                <div class="detail-reservation d-flex justify-content-between">
                    <button class=" btn-outline mx-2" @click="modalDelete = !modalDelete">
                        <span class="icon-ac-trash-2"></span>
                        Eliminar cotización
                    </button>
                    <button class="btn-outline mx-2" @click="modalSave = !modalSave">
                        <span class="icon-ac-save"></span>
                        Guardar
                    </button>
                    <button class=" btn-outline mx-2">
                        <span class="icon-ac-save"></span>
                        Guardar como
                    </button>
                    <button class="btn-outline mx-2">
                        <span class="icon-ac-save"></span>
                        Elimar servicios
                    </button>
                    <button class=" btn-outline mx-2">
                        <span class="icon-ac-book"></span>
                        Marcar como opcional
                    </button>
                    <button class=" btn-outline mx-2">
                        <span class="icon-ac-download"></span>
                        Descargar
                    </button>


                </div>
            </div>
        </b-skeleton-wrapper>
    </section>
    <div class="package_modify container-fluid" v-if="search_services">
        <div class="row" style="width: 100%;">
            <div class="col-sm-8"></div>
            <div class="col-sm-4 container-cards">
                <b-skeleton-wrapper :loading="loading_services">
                    <template #loading>
                        <b-row style="width: auto !important;">
                            <b-col cols="6" class="mt-3 mb-4">
                                <b-skeleton class="mt-3" width="100%" height="5%"></b-skeleton>
                            </b-col>
                            <b-col cols="6" class="mt-3 mb-4">
                                <b-skeleton class="mt-3" width="100%" height="5%"></b-skeleton>
                            </b-col>
                        </b-row>
                        <b-row style="width: auto !important;">
                            <b-col cols="12" class="mb-4">
                                <b-skeleton class="mt-3" width="60%" height="5%"></b-skeleton>
                            </b-col>
                            <b-col cols="12" class="mt-2">
                                <b-skeleton-img height="170px"></b-skeleton-img>
                            </b-col>
                            <b-col cols="12" class="mt-2">
                                <b-skeleton class="mt-3" width="100%" height="5%"></b-skeleton>
                                <b-skeleton class="mt-3" width="80%" height="5%"></b-skeleton>
                                <b-skeleton class="mt-3" width="60%" height="5%"></b-skeleton>
                            </b-col>
                        </b-row>
                        <b-row>
                            <b-col cols="12" class="mt-2 mb-2"></b-col>
                        </b-row>
                        <b-row style="width: auto !important;">
                            <b-col cols="6" class="mt-2 mb-4">
                                <b-skeleton width="100%" height="5%"></b-skeleton>
                            </b-col>
                            <b-col cols="6" class="mt-2 mb-4">
                                <b-skeleton width="100%" height="5%"></b-skeleton>
                            </b-col>
                        </b-row>
                    </template>
                    <div class="d-flex justify-content-between align-items-center mb-5">
                        <h2>Resultados</h2>
                        <div class="form-modify">
                            <b-dropdown size="sm" variant="link" toggle-class="text-decoration-none" no-caret
                                        style="width: 220px;border: none !important;">
                                <template #button-content>
                                    <p class="text-danger text-right">
                                        <span v-if="sort_by_code === 1">Precio: Mayor a menor</span>
                                        <span v-if="sort_by_code === 2">Precio: Menor a mayor</span>
                                        <span v-if="sort_by_code === 3">Nombre</span>
                                        <i class="fas fa-arrow-down"></i>
                                    </p>
                                </template>
                                <b-dropdown-item href="#" @click="filtersResult(1)" v-show="sort_by_code !== 1">Precio:
                                    Mayor a menor
                                </b-dropdown-item>
                                <b-dropdown-item href="#" @click="filtersResult(2)" v-show="sort_by_code !== 2">Precio:
                                    Menor a mayor
                                </b-dropdown-item>
                                <b-dropdown-item href="#" @click="filtersResult(3)" v-show="sort_by_code !== 3">Nombre
                                </b-dropdown-item>
                            </b-dropdown>
                        </div>
                    </div>
                    <div class="content-cards p-0" id="NOThidingScrollBar">

                        <div class="grid" style="height: 1000px;">
                            <div class="grid__item" v-for="service in services" style="display: contents;">
                                <div class="card p-3" style="height: auto !important;">
                                    <h2 class="modify-cards">
                                        @{{ service.commercial_descriptions.name }} <small>[@{{ service.code }}]</small>
                                    </h2>
                                    <div>
                                        <img v-if="service.galleries.length > 0" :src="service.galleries[0].url"
                                             onerror="this.onerror=null;this.src='https://res.cloudinary.com/litomarketing/image/upload/c_scale,w_400/v1440093948/paragliding_in_Miraflores._123110314_dlyun3.jpg'"
                                             class="card__img" alt="service">
                                        <img class="card__img"
                                             src="https://res.cloudinary.com/litomarketing/image/upload/c_scale,w_400/v1440093948/paragliding_in_Miraflores._123110314_dlyun3.jpg"
                                             alt="Image Service" v-else>
                                    </div>
                                    <div class="card__content">
                                        <div class="card__ubi"><span class="icon-map-pin mr-1"></span>
                                            @{{ service.destiny.destiny_display }}
                                        </div>
                                        <p class="card__text" style="height: auto !important;">
                                            @{{ service.commercial_descriptions.description }}
                                            <a href="/" class="ml-2">Mas detalles</a>
                                        </p>
                                        <div class="card__price d-flex justify-content-between">
                                            <button class="btn btn-primary" @click="">Agregar</button>
                                            <div class="text-right price">
                                                <div class="price__value">
                                                    <span class="icon-dollar-sign"></span> @{{ service.price_per_person
                                                    }}
                                                </div>
                                                <span class="price__text">Por pasajero</span>
                                            </div>
                                        </div>
                                        <hr>
                                    </div>
                                </div>
                            </div>
                            <infinite-loading @infinite="infiniteHandler" :infinite-scroll-disabled="loading_general" v-if="search_services">
                                <div slot="no-more">
                                    {{trans('service.label.no_more_data')}}
                                </div>
                            </infinite-loading>
                        </div>
                    </div>
                </b-skeleton-wrapper>
            </div>
        </div>
    </div>

@endsection
@section('css')
    <style>
        .backdrop-banners {
            display: none !important;
        }
    </style>
@endsection
@section('js')
    <script>

        new Vue({
            el: '#app',
            data: {
                loading_general: false,
                loading_services: false,
                search_services: false,
                blockPage: false,
                selectedDay: null,
                translations: {
                    label: {},
                    validations: {},
                    messages: {}
                },
                service_categories: [],
                experiences: [],
                type_class_id: null,
                service_types: [],
                categories: [],
                quote_open: '',
                use_discount: false,
                discount: 0,
                discount_detail: '',
                discount_user_permission: '',
                optionsR: {
                    format: 'DD/MM/YYYY',
                    useCurrent: false,
                    defaultDate: moment().add(1, 'days').format('YYYY-MM-DD'),
                    minDate: moment().add(1, 'days').format('YYYY-MM-DD'),
                },
                quote_date: '',
                quote_code: '',
                add_service_date: moment().add(1, 'days').format('DD/MM/YYYY'),
                quote_service_type_name: '',
                add_extensions_date: '',
                add_hotel_date: '',
                updateDatePickerQuote: 1,
                operation: 'ranges',
                quote_name: '',
                markup: 0,
                nights: 0,
                quote_date_start: '',
                quote_date_end: '',
                modalDelete: false,
                modalSave: false,
                quantity_persons: {
                    adults: 0,
                    child: 0,
                    ages_child: []
                },
                service_quantity_persons: {
                    adults: 2,
                    child: 0,
                    age_childs: [{
                        age: 1,
                    },]
                },
                control_service_selected_general: {
                    single: 0,
                    double: 0,
                    triple: 0
                },
                service_type_id: 1,
                tab_service_type_id: 1,
                destinations_select: [],
                origins_select: [],
                origin_service: '',
                destiny_service: '',
                filter_service_transfers: '',
                filter_service_foods: '',
                filter_service_experiences: '',
                filter_extensions: '',
                service_limit: 10,
                service_page: 0,
                services: [],
                selectedExperiencesLabel: '',
                selectedExperiences: [],
                service_durations: [],
                unit_duration: 0,
                service_duration: '',
                quantity_services: 0,
                service_group_id: 1,
                sortByService: 'price',
                orderByService: 'desc',
                tabOn: 1,
                sort_by_code: 1,
                food_categories: [],
                tab_category_id: '',
                ranges_price: [],
                rangePriceServices: [0, 0],
                max_price_food: 0,
                min_price_food: 0
            },

            mounted () {
                this.$root.$emit('loadingPage', { typeBack: 2 })
                this.setTranslations()
                this.$root.$on('changeMarkup', (payload) => {
                    this.putMarkup()
                })

                this.loading_general = true
                this.blockPage = true

                this.getCategories()
                this.getServiceCategories()
                this.getServicesTypes()
                this.getDestinations()
                this.getExperiences()
                this.getRangesPriceServicesFood()
                this.getServiceFoodCategories()
                this.searchQuoteOpen('')
            },
            computed: {},
            methods: {
                setTranslations () {
                    axios.get(baseURL + 'translation/' + localStorage.getItem('lang') + '/slug/quote').then((data) => {
                        this.translations = data.data
                    })
                },
                putMarkup () {
                    axios.get('api/markup/byClient/' + localStorage.getItem('client_id'))
                        .then(response => {
                            if (response.data.success) {
                                this.markup = parseFloat(response.data.data.hotel)
                                this.updateMarkup(2)
                            }
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
                getServiceCategories: function () {
                    axios.get('api/service_categories/selectBox?lang=' + localStorage.getItem('lang')).then(response => {
                        this.service_categories = response.data.data
                    }).catch(error => {
                        console.log(error)
                    })
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
                getDestinations: function () { // Busqueda de destinos por cliente
                    this.blockPage = true
                    axios.get('services/destination_services?client_id=' + localStorage.getItem('client_id')).then((result) => {
                        this.blockPage = false
                        if (result.data.success) {
                            this.destinations_select = result.data.data.destinations
                            this.origins_select = result.data.data.origins
                        }
                    }).catch((e) => {
                        this.blockPage = false
                        console.log(e)
                    })
                },
                getExperiences: function () {
                    axios.get(baseExternalURL + 'services/experiences?lang=' + localStorage.getItem('lang')).then((result) => {
                        if (result.data.success) {
                            var data = result.data.data
                            for (let i = 0; i < data.length; i++) {
                                this.experiences.push({
                                    'label': data[i].name,
                                    'code': data[i].id,
                                    'active': false
                                })
                            }
                        }
                    }).catch((e) => {
                        console.log(e)
                    })
                },
                getRangesPriceServicesFood () {
                    let data = {
                        lang: localStorage.getItem('lang'),
                        client_id: localStorage.getItem('client_id'),
                        date: (this.add_service_date === '') ? moment().format('YYYY-MM-DD') : moment(this.add_service_date, 'DD/MM/YYYY').format('YYYY-MM-DD'),
                        quantity_persons: this.service_quantity_persons,
                        group: 2
                    }
                    axios.post('services/price_ranges/services', data).then(response => {
                        if (response.data.success) {
                            this.min_price_food = response.data.data.price_per_person.min
                            this.max_price_food = response.data.data.price_per_person.max
                            this.rangePriceServices = [response.data.data.price_per_person.min, response.data.data.price_per_person.max]
                            this.ranges_price = [response.data.data.price_per_person.min, response.data.data.price_per_person.max]

                        }
                    })
                },
                searchQuoteOpen (category_id) {
                    this.loading_general = true
                    axios.get('api/quote/byUserStatus/2?lang=' + localStorage.getItem('lang')).then(response => {
                        if (response.data.length > 0) {
                            this.quote_id = response.data[0].id
                            this.quote_open = response.data[0]
                            this.quote_open.withHeader = true
                            this.quote_open.withClientLogo = false

                            this.quote_date = moment(this.quote_open.date_in).format('DD/MM/Y')
                            this.quote_date_start = moment(this.quote_open.date_in).format('MMM DD')
                            if (this.quote_open.nights > 0) {
                                let days = this.quote_open.nights
                                this.quote_date_end = moment(this.quote_open.date_in).add(days, 'days').format('MMM DD')
                            }
                            this.operation = response.data[0].operation
                            this.quote_name = this.quote_open.name
                            this.nights = this.quote_open.nights
                            this.markup = response.data[0].markup
                            this.add_hotel_date = moment(this.quote_open.date_in).format('DD/MM/Y')
                            // this.add_service_date = moment(this.quote_open.date_in).format('DD/MM/Y')
                            this.add_extensions_date = moment(this.quote_open.date_in).format('DD/MM/Y')
                            this.quote_service_type_id = this.quote_open.service_type_id

                            this.service_types.forEach(s_t => {
                                if (s_t.id === this.quote_service_type_id) {
                                    this.quote_service_type_name = s_t.translations[0].value
                                }
                            })

                            if (this.operation === 'passengers') {
                                this.passengers = response.data[0].passengers
                                if (response.data[0].people.length > 0) {
                                    this.quantity_persons.adults = response.data[0].people[0].adults
                                    this.quantity_persons.child = response.data[0].people[0].child
                                } else {
                                    this.quantity_persons.adults = 0
                                    this.quantity_persons.child = 0
                                }
                            }

                            let occupation_done = false
                            this.quote_open.categories.forEach((_c, _k) => {
                                if (_c.services.length > 0 && !occupation_done) {
                                    _c.services.forEach(_s => {
                                        if (this.quantity_persons.adults === 0 && this.quantity_persons.child === 0) {
                                            this.quantity_persons.adults = _s.adult
                                            this.quantity_persons.child = _s.child
                                        }
                                        if (_s.type === 'hotel') {

                                            this.control_service_selected_general.single = _s.single
                                            this.control_service_selected_general.double = _s.double
                                            this.control_service_selected_general.triple = _s.triple
                                            occupation_done = true
                                        }
                                    })
                                }
                            })
                            this.service_quantity_persons.adults = this.quantity_persons.adults
                            this.service_quantity_persons.child = this.quantity_persons.child
                            this.service_quantity_persons.age_childs = this.quantity_persons.ages_child

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
                            this.quote_service_type_name = ''
                        }

                        // this.set_service_selected_generals()

                        if (this.add_service_date == '') {
                            this.add_service_date = moment(this.quote_open.date_in).format('DD/MM/Y')
                        }
                        if (this.add_hotel_date == '') {
                            this.add_hotel_date = moment(this.quote_open.date_in).format('DD/MM/Y')
                        }
                        this.loading_general = false
                        this.blockPage = false
                        this.no_reload = false

                        // if (this.service_selected.single != '' && this.service_selected.double != '') {
                        // this.checkExistsPassengerService()
                        // }

                        if (localStorage.getItem('client_id') == '') {
                            document.getElementById('_overlay').style.display = ''
                            this.$toast.success(this.translations.validations.rq_client, {
                                position: 'top-right'
                            })
                        } else {
                            // document.getElementById('_overlay').style.display = 'none'
                        }

                        if (this.update_passengers_first_time) {
                            this.update_passengers_first_time = false
                            this.generatePassenger()
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
                        this.loading_general = false
                        this.blockPage = false
                        // document.getElementById('_overlay').style.display = 'none'
                    })

                },
                showDatePickerQuote: function () {
                    // Vue.set(this.quote_open, 'disabled', false)
                },
                formatDate: function (_date, charFrom, charTo, orientation) {
                    _date = _date.split(charFrom)
                    _date =
                        (orientation)
                            ? _date[2] + charTo + _date[1] + charTo + _date[0]
                            : _date[0] + charTo + _date[1] + charTo + _date[2]
                    return _date
                },
                getFiltersServices () {
                    let data = {}
                    let date = (this.add_service_date === '') ? moment().format('YYYY-MM-DD') : moment(this.add_service_date, 'DD/MM/YYYY').format('YYYY-MM-DD')
                    //Traslados
                    if (this.service_group_id === 3) {
                        data = {
                            lang: localStorage.getItem('lang'),
                            client_id: localStorage.getItem('client_id'),
                            date: date,
                            origin: this.origin_service,
                            destiny: this.destiny_service,
                            quantity_persons: this.service_quantity_persons,
                            type: [this.tab_service_type_id],
                            limit: this.service_limit,
                            page: this.service_page,
                            group: this.service_group_id,
                            sort_by: this.sortByService,
                            order_by: this.orderByService,
                            filter: this.filter_service_transfers,
                        }
                    }
                    //Comidas
                    if (this.service_group_id === 2) {
                        data = {
                            lang: localStorage.getItem('lang'),
                            client_id: localStorage.getItem('client_id'),
                            destiny: this.destiny_service,
                            date: date,
                            quantity_persons: this.service_quantity_persons,
                            limit: this.service_limit,
                            page: this.service_page,
                            group: this.service_group_id,
                            sort_by: this.sortByService,
                            order_by: this.orderByService,
                            filter: this.filter_service_foods,
                            ranges_price: this.ranges_price,
                        }
                        if (this.tab_category_id !== '') {
                            data.category = [this.tab_category_id]
                        }
                    }
                    //Experiencias
                    if (this.service_group_id === 1) {
                        data = {
                            lang: localStorage.getItem('lang'),
                            client_id: localStorage.getItem('client_id'),
                            destiny: this.destiny_service,
                            date: date,
                            quantity_persons: this.service_quantity_persons,
                            type: [this.tab_service_type_id],
                            experience: this.selectedExperiences,
                            limit: this.service_limit,
                            page: this.service_page,
                            group: this.service_group_id,
                            sort_by: this.sortByService,
                            order_by: this.orderByService,
                            filter: this.filter_service_experiences,
                        }
                        if ((this.service_duration !== '' && this.service_duration > 0) && this.unit_duration != 0) {
                            data.duration = this.service_duration
                            data.unit_duration = this.unit_duration
                        }
                    }

                    return data
                },
                searchServices () {
                    this.loading_services = true
                    this.search_services = true
                    this.service_page = 1
                    this.moreServices = []
                    axios.post('services/available', this.getFiltersServices()).then(response => {
                        response.data.data.services.forEach((s) => {
                            s.total_price_per_person = s.price_per_person
                            s.components.forEach((c) => {
                                c.show_replace = false
                                c.will_remove = false
                                c.removed = false
                                c.collapsed = false
                                c.substitute_selected = ''
                                s.total_price_per_person += c.price_per_person

                                c.repeated_total = 0
                                if (c.repeated === undefined) {
                                    s.components.forEach((cc) => {
                                        if (cc.service_id === c.service_id) {
                                            if (c.repeated_total === 0) {
                                                cc.repeated = false
                                            } else {
                                                cc.repeated = true
                                            }
                                            c.repeated_total++
                                        }
                                    })
                                }

                            })
                            s.total_price_per_person += s.supplements.total_price_per_person

                        })
                        this.services = response.data.data.services
                        this.quantity_services = response.data.data.quantity_services
                        this.loading_services = false
                    }).catch(error => {
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right'
                        })
                        this.loading_services = false
                    })
                },
                infiniteHandler: function ($state) {
                    this.service_page = this.service_page + 1
                    axios.post(
                        'services/available',
                        this.getFiltersServices(),
                    ).then((result) => {
                        if (result.data.success === true) {
                            result.data.data.services.forEach((s) => {
                                s.total_price_per_person = s.price_per_person
                                s.components.forEach((c) => {
                                    c.show_replace = false
                                    c.will_remove = false
                                    c.collapsed = false
                                    c.substitute_selected = ''
                                    s.total_price_per_person += c.price_per_person
                                })
                            })

                            this.loadMore($state, result)
                        } else {
                            let _result = {
                                data: {
                                    data: {
                                        services: []
                                    }
                                }
                            }
                            this.loadMore($state, _result)
                            this.$toast.warning(result.data.message, {
                                position: 'top-right'
                            })
                        }
                    }).catch((e) => {
                        console.log(e)
                    })

                },
                loadMore: function ($state, response) {
                    let data = response.data.data.services
                    if (data.length) {
                        if (this.quantity_services === data.length) {
                            $state.complete()
                        } else {
                            data.forEach(item => {
                                this.services.push(item)
                            })
                            $state.loaded()
                        }
                    } else {
                        $state.complete()
                    }
                },
                setGroupTab: function (tab) {
                    this.tabOn = tab
                    if (tab === 1) {
                        this.service_group_id = 1
                    }
                    if (tab === 2) {

                    }

                    if (tab === 3) {

                    }

                    if (tab === 4) {
                        this.service_group_id = 2
                    }

                    if (tab === 5) {
                        this.service_group_id = 3
                    }

                },
                getServiceFoodCategories: function () {
                    axios.get(baseExternalURL + 'services/categories?lang=' + localStorage.getItem('lang') + '&category_id=10').then((result) => {
                            if (result.data.success) {
                                let data = result.data.data
                                let categories = []
                                for (let c = 0; c < data.length; c++) {
                                    if (data[c].sub_category) {
                                        for (let i = 0; i < data[c].sub_category.length; i++) {
                                            categories.push({
                                                'label': data[c].sub_category[i].name,
                                                'id': data[c].sub_category[i].id,

                                            })
                                        }
                                    }
                                }
                                this.food_categories = categories
                            }
                        },
                    ).catch((e) => {
                        console.log(e)
                    })
                },
                selectExperience (experience) {
                    let experiencesCodes = []
                    let experiencesNames = []
                    experience.active = !(experience.active)
                    for (let i = 0; i < this.experiences.length; i++) {
                        if (this.experiences[i].active) {
                            experiencesCodes.push(this.experiences[i].code)
                            experiencesNames.push(this.experiences[i].label)
                        }
                    }
                    this.selectedExperiencesLabel = experiencesNames.join(', ')
                    this.selectedExperiences = experiencesCodes
                },
                isPositiveInteger: function (n) {
                    return n == '0' || ((n | 0) > 0 && n % 1 == 0)
                },
                changeQuantityServiceChild: function (event) {
                    if (!this.isPositiveInteger(event)) {
                        event = 0
                        this.service_quantity_persons.child = event
                    }
                    this.service_quantity_persons.child = parseInt(event)
                    this.service_quantity_persons.age_childs = [{
                        age: 1
                    }]
                    for (let i = 1; i < this.service_quantity_persons.child; i++) {
                        this.service_quantity_persons.age_childs.splice((i + 1), 0, { age: 1 })
                    }

                },
                filtersResult: function (sort_by) {
                    this.sort_by_code = sort_by
                    if (sort_by === 1) { //Todo: Filtro de precio de mayor a menor
                        this.sortByService = 'price'
                        this.orderByService = 'desc'
                    }
                    if (sort_by === 2) { //Todo: Filtro de precio de menor a mayor
                        this.sortByService = 'price'
                        this.orderByService = 'asc'
                    }

                    if (sort_by === 3) { //Todo: Ordenar por nombre
                        this.sortByService = 'name'
                        this.orderByService = 'asc'
                    }

                    if (this.tabOn === 1 || this.tabOn === 4 || this.tabOn === 5) {
                        this.searchServices()
                    }
                }
            },

        })
    </script>
@endsection
