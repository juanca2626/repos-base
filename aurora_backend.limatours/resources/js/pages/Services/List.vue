<template>
    <div class="container-fluid">
        <div class="b-form-group form-group">
            <div class="row">
                <div class="col-md-12">
                    <a href="/translations/texts/export" target="_blank" class="btn btn-success"
                       v-if="$can('exporttexts', 'manageservices')">
                        <i class="fas fa-file-download"></i> Exportar textos
                    </a>
                    <fileupload id="file" class="inputfile" :target="'/translations/texts/import/?_token=' + csrf"
                                action="POST"
                                v-on:progress="progress" v-on:start="startUpload" v-on:finish="finishUpload"
                                accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
                    </fileupload>
                    <label for="file" v-if="!loadFile && $can('importtexts', 'manageservices')">
                        <i class="fas fa-file-upload"></i> Importar textos
                    </label>
                    <label for="file" v-if="loadFile"><i class="fas fa-spinner fa-spin"></i> Importar textos</label>

                    <button class="btn btn-info right" type="button" :disabled="loadFile"
                            v-if="$can('importequivalences', 'manageservices')"
                            @click="import_more_equivalences()">
                        <i class="fa fa-recycle nav-icon" :class="{'fa-spin':loadFile}"></i>
                        Importar más Equivalencias
                    </button>

                </div>
            </div>
            <div class="form-row">
                <div class="col-sm-3">
                    <label class="col-form-label">Destino</label>
                    <v-select :options="ubigeos"
                              @input="ubigeoChange"
                              :value="this.ubigeo_id"
                              v-model="ubigeoSelected"
                              :placeholder="this.$t('hotels.search.messages.hotel_ubigeo_search')"
                              autocomplete="true"></v-select>
                </div>
                <div class="col-sm-2">
                    <label class="col-form-label">{{ $t('services.category') }}</label>
                    <v-select :options="typeServices"
                              :value="type_service_id"
                              @input="typeServiceChange"
                              autocomplete="true"
                              data-vv-as="type service"
                              data-vv-name="type_service"
                              name="type_service"
                              v-model="typeServiceSelected"
                              :placeholder="this.$t('services.typeService')">
                    </v-select>
                </div>
                <div class="col-sm-2">
                    <label class="col-form-label">{{ $t('services.typeService') }}</label>
                    <v-select :options="categories"
                              :value="category_service_id"
                              @input="serviceCategoryChange"
                              autocomplete="true"
                              data-vv-as="category"
                              data-vv-name="category"
                              v-model="serviceCategorySelected"
                              :placeholder="this.$t('services.category')">
                    </v-select>
                </div>
                <div class="col-sm-2">
                    <label class="col-form-label">Estado</label>
                    <v-select :options="[{label: 'Todos' , code: ''},{label: this.$t('hotels.status.active') , code: '1'},
                                {label: this.$t('hotels.status.disable') , code: '0'}]"
                              :placeholder="this.$t('hotels.search.messages.hotel_status_search')"
                              @input="statusChange"
                              v-model="statusSelected"
                              :value="this.status_id"
                    ></v-select>
                </div>
                <div class="col-sm-2">
                    <label class="col-form-label">Nombre o codigo</label>
                    <input class="form-control" id="service_name" name="service_name"
                           :placeholder="this.$t('services.search.messages.service_aurora_name_search')"
                           type="text" v-model="service_name">
                </div>
                <div class="col-sm-1">
                    <img src="/images/loading.svg" v-if="loading" width="40px"
                         style="float: right; margin-top: 35px;"/>
                    <button @click="search" class="btn btn-success" type="submit" v-if="!loading"
                            style="float: right; margin-top: 35px;">
                        <font-awesome-icon :icon="['fas', 'search']"/>
                        Buscar
                    </button>
                </div>
            </div>
        </div>
        <table-server :columns="table.columns" :options="tableOptions" :url="urlServices" class="text-center"
                      ref="table">
            <div class="table-actions" slot="actions" slot-scope="props" style="padding: 5px;">
                <b-dropdown class="mt-2 ml-2 mb-0" dropright size="sm">
                    <template slot="button-content">
                        <font-awesome-icon :icon="['fas', 'bars']" class="ml-1 p-0"/>
                    </template>
                    <b-dropdown-item-button @click="see_rates(props.row)" class="m-0 p-0"
                                            v-if="rate_permission">
                        <font-awesome-icon :icon="['fas', 'bars']" class="m-0"/>
                        Ver Tarifas
                    </b-dropdown-item-button>
                    <b-dropdown-item-button @click="see_packages(props.row)" class="m-0 p-0" v-if="rate_permission">
                        <font-awesome-icon :icon="['fas', 'bars']" class="m-0"/>
                        Ver Paquetes
                    </b-dropdown-item-button>
                    <router-link :to="'/services_new/edit/'+props.row.id"
                                 @click.native="getNameService(props.row)" class="nav-link m-0 p-0">
                        <b-dropdown-item-button class="m-0 p-0" v-if="$can('update', 'services')">
                            <font-awesome-icon :icon="['fas', 'edit']" class="m-0"/>
                            {{ $t('global.buttons.edit') }}
                        </b-dropdown-item-button>
                    </router-link>
                    <li @click="toManageService(props.row)" class="nav-link m-0 p-0">
                        <b-dropdown-item-button class="m-0 p-0" v-if="$can('update', 'services')">
                            <font-awesome-icon :icon="['fas', 'edit']" class="m-0"/>
                            {{ $t('services.manage') }}
                        </b-dropdown-item-button>
                    </li>
                    <b-dropdown-item-button @click="will_remove(props.row)" class="m-0 p-0"
                                            v-if="$can('delete', 'services')">
                        <font-awesome-icon :icon="['fas', 'trash']" class="m-0"/>
                        {{ $t('global.buttons.delete') }}
                    </b-dropdown-item-button>
                </b-dropdown>
            </div>
            <div class="table-service_ubigeo" slot="destinations" slot-scope="props" style="font-size: 0.9em">
                {{ props.row.service_origin[0] ? props.row.service_origin[0].state.translations[0].value : '' }} <i
                class="fas fa-long-arrow-alt-right text-danger"></i>
                {{ props.row.service_destination[0] ? props.row.service_destination[0].state.translations[0].value : '' }}
            </div>
            <div class="table-name" slot="name" slot-scope="props" style="font-size: 0.9em">
                <router-link :to="'/services_new/edit/'+props.row.id"
                             v-if="props.row.service_translations[0].name && props.row.service_translations[0].name != '' && props.row.service_translations[0].name != null">
                    <i class="fas fa-link"></i> {{ props.row.service_translations[0].name }}
                </router-link>
                <router-link :to="'/services_new/edit/'+props.row.id" title="(Nombre Técnico)"
                             style="color: #a20ba2b8; font-weight: 600;" v-else>
                    <i class="fas fa-link"></i> {{ props.row.name }}
                </router-link>
                <br>
                <small class="badge service" v-if="props.row.type == 'service'"> Servicio</small>
                <small class="badge supplement" v-if="props.row.type == 'supplement'"> Suplemento</small>
            </div>
            <div class="table-name" slot="category" slot-scope="props" style="font-size: 0.9em">
                {{ props.row.service_sub_category.service_categories.translations[0].value }}
            </div>
            <div class="table-name" slot="type" slot-scope="props" style="font-size: 0.9em">
                {{ props.row.service_type.code }} - {{ props.row.service_type.translations[0].value }}
            </div>
            <div class="table-aurora_code" slot="aurora_code" slot-scope="props" style="font-size: 0.9em">
                {{ props.row.aurora_code }} - {{ props.row.equivalence_aurora }}
            </div>
            <div class="table-channel" slot="channel" slot-scope="props" style="font-size: 0.9em">
                <div v-for="channel in props.row.channels">
                    {{ channel.name }}
                </div>
            </div>
            <div class="table-status" slot="status" slot-scope="props" style="font-size: 0.9em">
                <div class="fake_change_status" @click="verify_uses(props.row)"></div>
                <b-form-checkbox
                    :checked="(props.row.status) ? 'true' : 'false'"
                    :id="'checkbox_'+props.row.id"
                    :name="'checkbox_'+props.row.id"
                    @change="changeState(props.row.id, props.row.status)"
                    switch>
                </b-form-checkbox>
            </div>
            <!--Barra de progreso-->
            <div class="table-advance" slot="progress" slot-scope="props" style="padding: 5px 25px;">
                <b-tooltip :target="'progress'+props.row.id" title="Tooltip content" placement="right"
                           v-if="props.row.progress_bar_value>0">
                    <ul style="list-style-type: none; padding: 0px;" class="text-left">
                        {{ $t('global.progress_bars_names.title') }}
                        <li v-for="progress_bar in progress_bars_names">
                            {{ $t('global.progress_bars_names.' + progress_bar.name) }}
                            <font-awesome-icon :icon="['fas', 'check']" class="ml-1 p-0" style="color: #4dbd74;"
                                               v-show="showIconProgressBar(progress_bar.name,props.row.progress_bars)"/>
                        </li>
                    </ul>
                </b-tooltip>
                <b-progress :id="'progress'+props.row.id" :max="max" style="background-color:#3c5e79">
                    <b-progress-bar :value="props.row.progress_bar_value"
                                    :variant="checkProgressBar(props.row.progress_bar_value)">
                        <div class="text-center">
                            {{ props.row.progress_bar_value }}%
                        </div>
                    </b-progress-bar>
                </b-progress>
            </div>

        </table-server>
        <b-modal :title="serviceName" centered ref="my-modal" size="sm">
            <p class="text-center">{{ $t('global.message_delete') }}</p>
            <div slot="modal-footer">
                <button @click="remove()" class="btn btn-success">{{ $t('global.buttons.accept') }}</button>
                <button @click="hideModal()" class="btn btn-danger">{{ $t('global.buttons.cancel') }}</button>
            </div>
        </b-modal>

        <b-modal title="Coincidencias del Servicio" ref="my-modal-uses" hide-footer size="lg">

            <h4>Se encontró que el servicio está siendo utilizado en los siguientes modulos.</h4>

            <div class="col-12">
                <div class="left">
                    <button v-if="!see_confirm" class="btn btn-success" :disabled="loading || tab_active===''"
                            type="button" @click="will_send_report()">
                        <span>Reportar</span>
                    </button>
                    <div class="alert alert-warning" v-if="see_message">
                        <i class="fa fa-info-circle"></i> El servicio ya fué reportado por favor espere las 48 hrs y
                        desactivado automáticamente.
                    </div>
                    <div class="alert alert-warning" v-if="see_confirm">
                        <i class="fa fa-info-circle"></i> Notificará a los usuarios correspondientes y el servicio se
                        desactivará automáticamente en 48 hrs.
                    </div>
                    <button v-if="see_confirm" class="btn btn-success" :disabled="loading || tab_active===''"
                            type="button" @click="send_report()">
                        <span>Confirmar</span>
                    </button>
                    <br>
                    <br>
                </div>
            </div>

            <div class="row col-12" style="padding-left: 29px; padding-bottom: 10px;">
                <div class="left" v-if="used_services.packages.length>0" style="margin-right: 2px;">
                    <button
                        :class="{'btn':true, 'btn-danger': tab_active!=='packages', 'btn-primary': tab_active==='packages'}"
                        @click="tab_active='packages'" :disabled="loading" type="button">
                        <span>({{ used_services.packages.length }}) Paquetes</span>
                    </button>
                </div>
                <div class="left" v-if="used_services.equivalences.length>0" style="margin-right: 2px;">
                    <button
                        :class="{'btn':true, 'btn-danger': tab_active!=='equivalences', 'btn-primary': tab_active==='equivalences'}"
                        @click="tab_active='equivalences'" :disabled="loading" type="button">
                        <span>({{ used_services.equivalences.length }}) Equivalencias Asociadas</span>
                    </button>
                </div>
                <div class="left" v-if="used_services.multiservices.length>0" style="margin-right: 2px;">
                    <button
                        :class="{'btn':true, 'btn-danger': tab_active!=='multiservices', 'btn-primary': tab_active==='multiservices'}"
                        @click="tab_active='multiservices'" :disabled="loading" type="button">
                        <span>({{ used_services.multiservices.length }}) Multiservicios</span>
                    </button>
                </div>
                <div class="left" v-if="used_services.multiservices_replaces.length>0" style="margin-right: 2px;">
                    <button
                        :class="{'btn':true, 'btn-danger': tab_active!=='multiservices_replaces', 'btn-primary': tab_active==='multiservices_replaces'}"
                        @click="tab_active='multiservices_replaces'" :disabled="loading" type="button">
                        <span>({{ used_services.multiservices_replaces.length }}) Multiservicios - Reemplazos</span>
                    </button>
                </div>
                <div class="left" v-if="used_services.supplements.length>0">
                    <button
                        :class="{'btn':true, 'btn-danger': tab_active!=='supplements', 'btn-primary': tab_active==='supplements'}"
                        @click="tab_active='supplements'" :disabled="loading" type="button">
                        <span>({{ used_services.supplements.length }}) Suplementos</span>
                    </button>
                </div>
            </div>

            <div class="col-12" v-if="tab_active==='packages'">
                <table-client :columns="table_used_packages.columns" :data="used_services.packages"
                              :options="table_options_used_packages" id="dataTable"
                              theme="bootstrap4">
                    <div class="table-supplement" slot="package" slot-scope="props">
                        <span v-if="props.row.code">
                                    {{ props.row.id }} - [{{ props.row.code }}] -
                                </span>
                        <span v-if="!(props.row.code)">
                                    <span v-if="props.row.extension=='1'">[E{{ props.row.id }}] - </span>
                                    <span v-if="props.row.extension=='0'">[P{{ props.row.id }}] - </span>
                                </span>
                        <span v-html="props.row.translations[0].name"></span>
                    </div>
                    <div class="table-supplement" slot="plan_rate" slot-scope="props">
                        [{{ props.row.plan_rates[0].service_type.abbreviation }}] - {{ props.row.plan_rates[0].name }}
                    </div>
                    <div class="table-supplement" slot="categories" slot-scope="props">
                        <div class="badge badge-primary bag-category mr-1" v-if="category.services_count>0"
                             v-for="category in props.row.plan_rates[0].plan_rate_categories">
                            {{ category.category.translations[0].value }} ({{ category.services_count }})
                        </div>
                    </div>
                    <div class="table-supplement" slot="period" slot-scope="props">
                        {{ props.row.plan_rates[0].date_from | formatDate }} - {{
                            props.row.plan_rates[0].date_to |
                                formatDate
                        }}
                    </div>
                </table-client>
            </div>
            <div class="col-12" v-if="tab_active==='equivalences'">
                <table-client :columns="table_used_equivalences.columns" :data="used_services.equivalences"
                              :options="table_options_used_equivalences" id="dataTable"
                              theme="bootstrap4">
                    <div class="table-supplement" slot="code" slot-scope="props">
                        <span>
                            {{ props.row.parent_service.id }} - [{{ props.row.parent_service.aurora_code }}]
                        </span>
                    </div>
                    <div class="table-supplement" slot="name" slot-scope="props">
                        <span v-html="props.row.parent_service.name"></span>
                    </div>
                </table-client>
            </div>
            <div class="col-12" v-if="tab_active==='multiservices'">
                <table-client :columns="table_used_multiservices.columns" :data="used_services.multiservices"
                              :options="table_options_used_multiservices" id="dataTable"
                              theme="bootstrap4">
                    <div class="table-supplement" slot="code" slot-scope="props">
                        <span>
                            {{
                                props.row.service_component.service.id
                            }} - [{{ props.row.service_component.service.aurora_code }}]
                        </span>
                    </div>
                    <div class="table-supplement" slot="name" slot-scope="props">
                        <span v-html="props.row.service_component.service.name"></span>
                    </div>
                </table-client>
            </div>
            <div class="col-12" v-if="tab_active==='multiservices_replaces'">
                <table-client :columns="table_used_multiservices_replaces.columns"
                              :data="used_services.multiservices_replaces"
                              :options="table_options_used_multiservices_replaces" id="dataTable"
                              theme="bootstrap4">
                    <div class="table-supplement" slot="multiservice" slot-scope="props">
                        <span>
                            {{ props.row.multiservice.service.id }} - [{{ props.row.multiservice.service.aurora_code }}] - <span
                            v-html="props.row.multiservice.service.name"></span>
                        </span>
                    </div>
                    <div class="table-supplement" slot="code" slot-scope="props">
                        <span>
                            {{
                                props.row.multiservice.service_component.service.id
                            }} - [{{ props.row.multiservice.service_component.service.aurora_code }}]
                        </span>
                    </div>
                    <div class="table-supplement" slot="name" slot-scope="props">
                        <span v-html="props.row.multiservice.service_component.service.name"></span>
                    </div>
                </table-client>
            </div>
            <div class="col-12" v-if="tab_active==='supplements'">
                <table-client :columns="table_used_supplements.columns" :data="used_services.supplements"
                              :options="table_options_used_supplements" id="dataTable"
                              theme="bootstrap4">
                    <div class="table-supplement" slot="code" slot-scope="props">
                        <span>
                            {{ props.row.parent_service.id }} - [{{ props.row.parent_service.aurora_code }}]
                        </span>
                    </div>
                    <div class="table-supplement" slot="name" slot-scope="props">
                        <span v-html="props.row.parent_service.name"></span>
                    </div>
                </table-client>
            </div>

        </b-modal>

        <b-modal title="Tarifas Costo" ref="my-modal-rates" hide-footer size="lg">
            <div class="vld-parent">
                <loading :active.sync="loading_rates" :can-cancel="false" color="#BD0D12"
                         :is-full-page="false"></loading>
                <h4>Lista de Tarifas Costo</h4>
                <table-client :columns="table_rates.columns" v-if="step==1"
                              :data="rates"
                              :options="table_options_rates"
                              id="dataTable"
                              theme="bootstrap4">
                    <div class="table-service_type_rate" slot="service_type_rate" slot-scope="props">
                        {{ props.row.service_type_rate.name }}
                    </div>
                    <div class="table-service_type_rate" slot="see" slot-scope="props">
                        <button @click="view_detail_rates(props.row)" class="btn btn-success" :disabled="loading">
                            <i class="fa fa-search" v-if="!loading"></i>
                            <i class="fa fa-spin fa-spinner" v-else></i>
                        </button>
                    </div>
                </table-client>
                <div v-else>
                    <div class="col-12">
                        <div class="form-row">
                            <label class="col-sm-1 col-form-label" for="period">{{
                                    $t('clientsmanageclienthotel.period')
                                }}</label>
                            <div class="col-sm-1.5">
                                <select @change="searchPeriod" ref="period" class="form-control" id="period"
                                        required
                                        size="0" v-model="selectPeriod">
                                    <option :value="year.value" v-for="year in years">
                                        {{ year.text }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-sm-1.5 right">
                                <button type="button" class="btn btn-success" @click="step=1">< Atrás</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-12" slot="range">
                        <table-client :columns="table_rates_detail.columns"
                                      :data="rates_plans"
                                      :loading="false"
                                      :options="table_rates_detail.options"
                                      id="rooms-table"
                                      ref="roomsTable"
                                      theme="bootstrap4">

                            <div class="table-range text-center" slot="range"
                                 slot-scope="props">
                                <i class="fa fa-check-circle" data-toggle="tooltip" data-placement="top"
                                   title="Con marge de protección" v-if="props.row.flag_migrate == 0"></i>
                                {{ props.row.pax_from }} - {{ props.row.pax_to }}
                            </div>
                            <div class="table-policy" slot="provider_policy"
                                 slot-scope="props">
                                {{ props.row.user.name }} / {{ props.row.policy.name }}
                            </div>
                            <div class="table-period" slot="period" style="width: 172px"
                                 slot-scope="props">
                                {{ props.row.date_from | formatDate }} - {{ props.row.date_to | formatDate }}
                            </div>
                            <div class="table-price_adult" slot="price_adult"
                                 slot-scope="props">
                                {{ props.row.price_adult }}
                            </div>
                            <div class="table-price_child" slot="price_child"
                                 slot-scope="props">
                                {{ props.row.price_child }}
                            </div>
                            <div class="table-price_infant" slot="price_infant"
                                 slot-scope="props">
                                {{ props.row.price_infant }}
                            </div>
                            <div class="table-price_guide" slot="price_guide"
                                 slot-scope="props">
                                {{ props.row.price_guide }}
                            </div>
                        </table-client>
                    </div>
                </div>
            </div>

        </b-modal>

        <b-modal title="Paquetes" ref="my-modal-packages" hide-footer size="lg">
            <div class="vld-parent">
                <loading :active.sync="loading_packages" :can-cancel="false" color="#BD0D12" :is-full-page="false" />
                <h4>Lista de Paquetes</h4>
                <table-server
                    :url="urlPackages"
                    :columns="table_packages.columns"
                    :data="packages"
                    :options="table_options_packages"
                    id="dataTable"
                    theme="bootstrap4">
                    <div class="table-name" slot="extension" slot-scope="props" style="font-size: 0.9em">
                        <small class="badge badge-success" v-if="props.row.extension === '0'"> Paquete Grande</small>
                        <small class="badge badge-info" v-if="props.row.extension === '1'"> Extensión</small>
                        <small class="badge badge-danger" v-if="props.row.extension === '2'"> Paquete Exclusivo</small>
                    </div>
                </table-server>
            </div>
        </b-modal>
    </div>
</template>

<script>
import { API } from './../../api'
import TableServer from '../../components/TableServer'
import TableClient from '../../components/TableClient'
import BFormCheckbox from 'bootstrap-vue/es/components/form-checkbox/form-checkbox'
import BDropDown from 'bootstrap-vue/es/components/dropdown/dropdown'
import BDropDownItemButton from 'bootstrap-vue/es/components/dropdown/dropdown-item-button'
import BModal from 'bootstrap-vue/es/components/modal/modal'
import Progress from 'bootstrap-vue/src/components/progress/progress'
import ProgressBar from 'bootstrap-vue/src/components/progress/progress-bar'
import Tooltip from 'bootstrap-vue/src/components/tooltip/tooltip'
import vSelect from 'vue-select'
import 'vue-select/dist/vue-select.css'
import FileUpload from 'vue-simple-upload/dist/FileUpload'
import datePicker from 'vue-bootstrap-datetimepicker'
import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/vue-loading.css'

export default {
    components: {
        datePicker,
        BFormCheckbox,
        'table-server': TableServer,
        'table-client': TableClient,
        'b-dropdown': BDropDown,
        'b-dropdown-item-button': BDropDownItemButton,
        BModal,
        'b-progress': Progress,
        'b-progress-bar': ProgressBar,
        'b-tooltip': Tooltip,
        vSelect,
        'fileupload': FileUpload,
        Loading
    },
    data: () => {
        return {
            urlPackages: null,
            max: 100,
            value: 100,
            categories: [],
            typeServices: [],
            type_service_id: '',
            category_service_id: '',
            typeServiceSelected: [],
            serviceCategorySelected: [],
            ubigeos: [],
            ubigeoSelected: [],
            hotelName: '',
            ubigeo_id: '',
            country_id: '',
            state_id: '',
            city_id: '',
            district_id: '',
            status: '',
            status_id: '',
            aurora_code_name: '',
            ubigeo: null,
            serviceName: '',
            services: [],
            loading: false,
            urlServices: '',
            service_name: '',
            fileImport: '',
            table: {
                columns: ['actions', 'id', 'destinations', 'name', 'category', 'type', 'aurora_code', 'status', 'progress'],
            },
            table_used_packages: {
                columns: ['package', 'plan_rate', 'categories', 'period']
            },
            table_used_equivalences: {
                columns: ['code', 'name']
            },
            table_used_multiservices: {
                columns: ['code', 'name']
            },
            table_used_multiservices_replaces: {
                columns: ['multiservice', 'code', 'name']
            },
            table_used_supplements: {
                columns: ['code', 'name']
            },
            table_rates: {
                columns: ['id', 'name', 'service_type_rate', 'see']
            },
            table_packages: {
                columns: ['code', 'name', 'extension']
            },
            table_rates_detail: {
                columns: ['range', 'period', 'provider_policy', 'price_adult', 'price_child', 'price_infant', 'price_guide'],
                options: {
                    headings: {
                        range: 'Rango',
                        period: 'Periodo',
                        provider_policy: 'Proveedor / Política',
                        price_adult: 'Adulto US$',
                        price_child: 'Niño US$',
                        price_infant: 'Infante US$',
                        price_guide: 'Guía US$'
                    },
                }
            },
            progress_bars_names: [
                { name: 'service_progress_details' },
                { name: 'service_progress_descriptions' },
                { name: 'service_progress_location' },
                { name: 'service_progress_experiences' },
                { name: 'service_progress_schedules' },
                { name: 'service_progress_operability' },
                { name: 'service_progress_gallery' },
                { name: 'service_progress_inclusions' },
                { name: 'service_progress_politics_cancellations' },
                { name: 'service_progress_availability' },
                { name: 'service_progress_rates' },
            ],
            statusSelected: [],
            csrf: '',
            loadFile: false,
            used_services: {
                service_id: '',
                service_name: '',
                packages: [],
                equivalences: [],
                multiservices: [],
                multiservices_replaces: [],
                supplements: [],
            },
            see_confirm: false,
            see_message: false,
            rate_permission: false,
            rates: [],
            packages: [],
            rates_plans: [],
            selectPeriod: '',
            step: 1,
            rate_plan_choose: '',
            tab_active: '',
            loading_rates: false,
            loading_packages: false,
            client_id: '',
        }
    },
    computed: {
        years () {
            let previousYear = moment().subtract(2, 'years').year()
            let currentYear = moment().add(5, 'years').year()

            let years = []

            do {
                years.push({ value: previousYear, text: previousYear })
                previousYear++
            } while (currentYear > previousYear)

            return years
        },
        menuOptions: function () {
            return [
                {
                    type: 'edit',
                    link: 'services_new/edit/',
                    icon: 'dot-circle',
                },
                {
                    type: 'delete',
                    link: 'services_new/edit/',
                    icon: 'times',
                },
            ]
        },
        tableOptions: function () {
            return {
                headings: {
                    id: 'ID',
                    destinations: this.$i18n.t('services.service_origin') + ' - ' +
                        this.$i18n.t('services.service_destination'),
                    name: this.$i18n.t('services.service_name'),
                    category: this.$i18n.t('services.service_category'),
                    type: this.$i18n.t('services.service_type'),
                    aurora_code: this.$i18n.t('services.service_code_aurora'),
                    status: this.$i18n.t('services.service_status'),
                    progress: this.$i18n.t('services.service_progress'),
                    actions: this.$i18n.t('global.table.actions')
                },
                sortable: [],
                filterable: [],
                perPageValues: [],
                responseAdapter ({ data }) {
                    return {
                        data: data.data,
                        count: data.count
                    }
                },
                params: {
                    'status': this.status_id,
                    'destiny': this.ubigeo_id,
                    'service_type': this.type_service_id,
                    'service_category': this.category_service_id,
                    'service_name': this.service_name
                },
                requestFunction: function (data) {
                    let url = '/services?token=' + window.localStorage.getItem('access_token') + '&lang='
                        + localStorage.getItem('lang')
                    return API.get(url, {
                        params: data
                    }).catch(function (e) {
                        this.dispatch('error', e)
                    }.bind(this))
                }
            }
        },
        table_options_used_packages: function () {
            return {
                headings: {
                    package: 'Paquete',
                    plan_rate: 'Plan Tarifario',
                    categories: 'Categorías',
                    period: 'Periodo'
                },
                sortable: [],
                filterable: []
            }
        },
        table_options_used_equivalences: function () {
            return {
                headings: {
                    code: 'Código',
                    name: 'Servicio'
                },
                sortable: [],
                filterable: []
            }
        },
        table_options_used_multiservices: function () {
            return {
                headings: {
                    code: 'Código',
                    name: 'Servicio'
                },
                sortable: [],
                filterable: []
            }
        },
        table_options_used_multiservices_replaces: function () {
            return {
                headings: {
                    multiservice: 'Multiservicio',
                    code: 'Código',
                    name: 'Servicio'
                },
                sortable: [],
                filterable: []
            }
        },
        table_options_used_supplements: function () {
            return {
                headings: {
                    code: 'Código',
                    name: 'Servicio'
                },
                sortable: [],
                filterable: []
            }
        },
        table_options_rates: function () {
            return {
                headings: {
                    id: 'ID',
                    name: this.$i18n.t('global.name'),
                    service_type_rate: this.$i18n.t('servicesmanageservicerates.service_type_rate'),
                    see: 'Opc.'
                },
                sortable: ['id'],
                filterable: ['name']
            }
        },
        table_options_packages: function () {
            return {
                headings: {
                    code: this.$i18n.t('global.code'),
                    name: this.$i18n.t('global.name'),
                    extension: this.$i18n.t('global.type'),
                },
                sortable: ['extension'],
                filterable: ['name']
            }
        }
    },
    created () {
        this.client_id = window.localStorage.getItem('client_id')
        this.csrf = window.Laravel.csrfToken
        this.$root.$emit('updateTitleServiceList', { tab: 1 })
        this.loadubigeo()
    },
    mounted () {
        this.$i18n.locale = localStorage.getItem('lang')
        //type services
        API.get('/service_categories/selectBox?lang=' + localStorage.getItem('lang'))
            .then((result) => {
                let categorias = result.data.data
                categorias.forEach((category) => {
                    this.typeServices.push({
                        label: category.translations[0].value,
                        code: category.translations[0].object_id
                    })
                })

            }).catch(() => {
            this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.services'),
                text: this.$t('global.error.messages.connection_error')
            })
        })
        //categorias
        API.get('/service_types/selectBox?lang=' + localStorage.getItem('lang'))
            .then((result) => {
                let serviceTypes = result.data.data
                serviceTypes.forEach((serviceTypes) => {
                    this.categories.push({
                        label: serviceTypes.code + ' - ' + serviceTypes.translations[0].value,
                        code: serviceTypes.translations[0].object_id
                    })
                })

            }).catch(() => {
            this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.services'),
                text: this.$t('global.error.messages.connection_error')
            })
        })
        //rate permission
        API.get('/permissions/name/Manage_services: Rates')
            .then((result) => {
                // if( result.data.data.length === 0 ){
                this.rate_permission = true
                // }
            }).catch(() => {
            this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.services'),
                text: this.$t('global.error.messages.connection_error')
            })
        })
        let currentDate = new Date()
        this.selectPeriod = currentDate.getFullYear()
    },
    methods: {
        see_packages (service) {
            const serviceId = service.id
            const accessToken = window.localStorage.getItem('access_token')
            const lang = localStorage.getItem('lang')
            this.urlPackages = '/api/services/packages?token=' + accessToken + '&lang=' + lang + '&service_id=' + serviceId
            this.$refs['my-modal-packages'].show()
        },
        import_more_equivalences () {
            this.loadFile = true

            API.post('services/equivalences/import')
                .then((result) => {

                    if (result.data.success) {
                        let count_ = result.data.data.length
                        let message_ = 'Ninguno por importar'
                        let type_message_ = 'warning'
                        if (count_ === 1) {
                            message_ = 'Se importó ' + count_ + ' equivalencia'
                            type_message_ = 'success'
                        }
                        if (count_ > 1) {
                            message_ = 'Se importaron ' + count_ + ' equivalencias'
                            type_message_ = 'success'
                        }
                        this.$notify({
                            group: 'main',
                            type: type_message_,
                            title: this.$t('global.modules.services'),
                            text: message_
                        })

                        this.onUpdate()

                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.services'),
                            text: 'Servicio no disponible, por favor contáctese con el administrador'
                        })
                    }

                    this.loadFile = false
                    // invocar la clase del otro vue
                }).catch(() => {
                this.loadFile = false
            })

        },
        searchPeriod: function () {
            this.view_detail_rates(this.rate_plan_choose)
        },
        view_detail_rates (rate_plan) {
            this.rate_plan_choose = rate_plan
            this.loading = true
            this.loading_rates = true

            API.get('/service/rates/' + rate_plan.id + '/plans/' + this.selectPeriod)
                .then((result) => {
                    this.rates_plans = result.data.data
                    this.loading = false
                    this.loading_rates = false
                    this.step = 2
                }).catch(() => {
                this.loading_rates = false
                this.loading = false
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('global.modules.services'),
                    text: this.$t('servicesmanageservicerates.error.messages.connection_error')
                })
            })

        },
        will_send_report () {

            if (this.tab_active === '') {
                console.log('ningun tab elegido')
                return
            }

            this.see_message = false
            this.loading = true

            API({
                method: 'get',
                url: 'deactivatable/entity?entity=App/Service&object_id=' + this.used_services.service_id
            })
                .then((result) => {
                    if (result.data.success) {
                        if (result.data.data.length > 0) {
                            this.see_message = true
                            let me = this
                            setTimeout(function () {
                                me.see_message = false
                            }, 5000)
                        } else {
                            this.see_confirm = true
                        }
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.services'),
                            text: this.$t('global.error.messages.information_error')
                        })
                    }
                    this.loading = false
                })

        },
        send_report () {

            this.loading = true

            let packages_ = []
            this.used_services.packages.forEach(p => {
                let obj = {}
                if (p.code) {
                    obj.package = p.id + ' - [' + p.code + '] - '
                } else {
                    if (p.extension === '1') {
                        obj.package = '[E' + p.id + '] - '
                    } else {
                        obj.package = '[P' + p.id + '] - '
                    }
                }
                obj.package += p.translations[0].name

                obj.plan_rate = '[' + p.plan_rates[0].service_type.abbreviation + '] - ' + p.plan_rates[0].name

                obj.categories = []
                p.plan_rates[0].plan_rate_categories.forEach(c => {
                    if (c.services_count > 0) {
                        obj.categories.push(c.category.translations[0].value + ' (' + c.services_count + ')')
                    }
                })

                obj.period = this.formatDate(p.plan_rates[0].date_from) + ' - ' + this.formatDate(p.plan_rates[0].date_to)

                packages_.push(obj)

            })

            let equivalences_ = []
            this.used_services.equivalences.forEach(e => {
                equivalences_.push({
                    id: e.id,
                    service: '[' + e.parent_service.aurora_code + '] - ' + e.parent_service.name,
                })
            })

            let multiservices_ = []
            this.used_services.multiservices.forEach(m => {
                multiservices_.push({
                    id: m.id,
                    service: '[' + m.service_component.service.aurora_code + '] - ' + m.service_component.service.name,
                })
            })

            let multiservices_replaces_ = []
            this.used_services.multiservices_replaces.forEach(m => {
                multiservices_replaces_.push({
                    id: m.id,
                    multiservice: '[' + m.multiservice.service.aurora_code + '] - ' + m.multiservice.service.name,
                    service: '[' + m.multiservice.service_component.service.aurora_code + '] - ' + m.multiservice.service_component.service.name,
                })
            })

            let supplements_ = []
            this.used_services.supplements.forEach(s => {
                supplements_.push({
                    id: s.id,
                    service: '[' + s.parent_service.aurora_code + '] - ' + s.parent_service.name,
                })
            })

            let data = {
                service_id: this.used_services.service_id,
                service_name: this.used_services.service_name,
                packages: packages_,
                equivalences: equivalences_,
                multiservices: multiservices_,
                multiservices_replaces: multiservices_replaces_,
                supplements: supplements_
            }

            API({
                method: 'post',
                url: 'services/' + data.service_id + '/uses/report',
                data: { data: data }
            })
                .then((result) => {
                    if (result.data.success) {
                        this.see_confirm = false
                        this.hideModal()
                        this.used_services.packages = []
                        this.used_services.service_id = ''
                        this.used_services.service_name = ''
                        this.$notify({
                            group: 'main',
                            type: 'success',
                            title: this.$t('global.modules.services'),
                            text: 'Enviado correctamente'
                        })
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.services'),
                            text: this.$t('global.error.messages.information_error')
                        })
                    }
                    this.loading = false
                })

        },
        see_rates (service) {
            this.service_id = service.id
            this.serviceName = service.name
            this.step = 1
            this.$refs['my-modal-rates'].show()
            this.rates = []
            this.rates_plans = []
            this.list_rates_costs(service)
        },
        will_remove (service) {
            this.service_id = service.id
            this.serviceName = service.name
            this.$refs['my-modal'].show()
            this.verify_uses(service)
        },
        hideModal () {
            this.$refs['my-modal'].hide()
            this.$refs['my-modal-uses'].hide()
            this.$refs['my-modal-rates'].hide()
        },
        list_rates_costs (service) {
            this.loading_rates = true
            API.get('service/rates/cost/' + service.id)
                .then((result) => {
                    this.loading_rates = false
                    if (result.data.success === true) {
                        this.rates = result.data.data
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Fetch Error',
                            text: result.data.message
                        })
                    }
                }).catch(() => {
                this.loading_rates = false
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('global.modules.services'),
                    text: this.$t('servicesmanageservicerates.error.messages.connection_error')
                })
            })
        },
        verify_uses (service) {

            if (service.status) {
                // Validar coincidencias
                API({
                    method: 'get',
                    url: 'services/' + service.id + '/uses'
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            if (result.data.packages.length === 0 &&
                                result.data.equivalences.length === 0 &&
                                result.data.multiservices.length === 0 &&
                                result.data.multiservices_replaces.length === 0 &&
                                result.data.supplements.length === 0
                            ) {
                                document.getElementById('checkbox_' + service.id).click()
                            } else {
                                this.$refs['my-modal'].hide()
                                this.$refs['my-modal-uses'].show()

                                this.used_services.packages = result.data.packages
                                this.used_services.equivalences = result.data.equivalences
                                this.used_services.multiservices = result.data.multiservices
                                this.used_services.multiservices_replaces = result.data.multiservices_replaces
                                this.used_services.supplements = result.data.supplements

                                if (this.used_services.packages.length > 0) {
                                    this.tab_active = 'packages'
                                } else if (this.used_services.equivalences.length > 0) {
                                    this.tab_active = 'equivalences'
                                } else if (this.used_services.multiservices.length > 0) {
                                    this.tab_active = 'multiservices'
                                } else if (this.used_services.multiservices_replaces.length > 0) {
                                    this.tab_active = 'multiservices_replaces'
                                } else if (this.used_services.supplements.length > 0) {
                                    this.tab_active = 'supplements'
                                } else {
                                    this.tab_active = ''
                                }

                                this.used_services.service_id = service.id
                                this.used_services.service_name = '[' + service.aurora_code + '] ' + service.name
                            }
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.services'),
                                text: this.$t('global.error.messages.information_error')
                            })
                        }
                    })
            } else { // Activando
                document.getElementById('checkbox_' + service.id).click()
            }
        },
        changeState: function (service_id, status) {
            API({
                method: 'put',
                url: 'services/' + service_id + '/status',
                data: { status: status }
            })
                .then((result) => {
                    if (result.data.success === true) {
                        this.onUpdate()

                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.services'),
                            text: this.$t('global.error.messages.information_error')
                        })
                    }
                })
        },
        remove () {
            API({
                method: 'DELETE',
                url: 'services/' + this.service_id
            })
                .then((result) => {
                    if (result.data.success === true) {
                        this.onUpdate()
                        this.hideModal()
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.services'),
                            text: this.$t('global.error.messages.service_delete')
                        })
                    }
                }).catch(() => {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('global.modules.services'),
                    text: this.$t('global.error.messages.connection_error')
                })
            })
        },
        onUpdate () {
            this.urlServices = '/api/services?token=' + window.localStorage.getItem('access_token') + '&lang=' +
                localStorage.getItem('lang')
            this.$refs.table.$refs.tableserver.refresh()
        },
        ubigeoChange: function (value) {
            this.ubigeo = value
            if (this.ubigeo != null) {
                if (this.ubigeo_id != this.ubigeo.code) {
                }
                this.ubigeo_id = this.ubigeo.code
            } else {
                this.ubigeo_id = ''
            }
        },
        statusChange: function (value) {
            this.status = value
            if (this.status != null) {
                this.status_id = this.status.code
            } else {
                this.status_id = ''
            }
        },
        checkProgressBar: function (value) {
            if (value >= 0 && value <= 30) {
                return 'danger'
            }
            if (value > 30 && value <= 70) {
                return 'warning'
            }
            if (value > 70 && value <= 100) {
                return 'success'
            }
        },
        showIconProgressBar: function (progress_bar_name, progress_bar_success) {
            for (let i = 0; i < progress_bar_success.length; i++) {
                if (progress_bar_success[i].slug === progress_bar_name) {
                    return true
                }
            }
            return false
        },
        serviceCategoryChange: function (value) {
            this.serviceType = value
            if (this.serviceType != null) {
                this.category_service_id = this.serviceType.code
            } else {
                this.category_service_id = ''
                this.serviceCategorySelected = []
            }
        },
        typeServiceChange: function (value) {
            this.category = value
            if (this.category != null) {
                this.type_service_id = this.category.code
            } else {
                this.type_service_id = ''
            }
        },
        loadubigeo () {
            API.get('/services/ubigeo/selectbox/destination/' + localStorage.getItem('lang'))
                .then((result) => {
                    let ubigeohotel = result.data.data
                    ubigeohotel.forEach((ubigeofor) => {
                        this.ubigeos.push({ label: ubigeofor.description, code: ubigeofor.id })
                    })
                }).catch((e) => {
                console.log(e)
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('global.modules.services'),
                    text: this.$t('global.error.messages.connection_error')
                })
            })
        },
        search () {
            this.loading = true
            this.$refs.table.$refs.tableserver.getData()
            this.loading = false
        },
        getNameService (data) {
            // localStorage.setItem("hotelnamemanage", name)
            this.$root.$emit('updateTitleService', { service_id: data.id })
        },
        toManageService: function (me) {
            this.$root.$emit('updateTitleService', { service_id: me.id })
            localStorage.serviceChoosed_type_code = me.service_type.code
            this.$router.push('/services_new/' + me.id + '/manage_service')
        },
        startUpload (e) {
            this.loadFile = true
            console.log(e)
        },
        finishUpload (e) {
            this.loadFile = false
            if (e.target.status === 500) {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('global.modules.services'),
                    text: this.$t('global.error.messages.connection_error')
                })
            }

            if (e.target.status === 200) {
                this.$notify({
                    group: 'main',
                    type: 'success',
                    title: this.$t('global.modules.services'),
                    text: 'La importación se realizo correctamente'
                })
            }

            console.log('-----finalizo')
            console.log(e)
        },
        progress (e) {
            console.log(e)
        },
        formatDate: function (_date) {
            if (_date == undefined) {
                // console.log('fecha no parseada: ' + _date)
                return
            }
            _date = _date.split('-')
            _date = _date[2] + '/' + _date[1] + '/' + _date[0]
            return _date
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
        first2Char: function (day) {
            return day.substr(0, 2)
        }
    }
}
</script>

<style lang="stylus">


/* style 2 */
.inputfile {
    width: 0.1px;
    height: 0.1px;
    opacity: 0;
    overflow: hidden;
    position: absolute;
    z-index: -1;
}

.inputfile + label {
    max-width: 80%;
    font-size: 0.875rem;
    font-weight: 400;
    text-overflow: ellipsis;
    white-space: nowrap;
    cursor: pointer;
    background-color: #bd0d12;
    padding: 0.375rem 0.75rem;
    border-radius: 0.25rem;
    line-height: 1.5;
    border: 1px solid transparent;
}

.inputfile + label {
    color: #fff;
}

.inputfile:focus + label,
.inputfile.has-focus + label,
.inputfile + label:hover {
    color: #722040;
}

.fake_change_status {
    width: 50px;
    height: 21px;
    position: absolute;
    z-index: 1;
}

</style>
