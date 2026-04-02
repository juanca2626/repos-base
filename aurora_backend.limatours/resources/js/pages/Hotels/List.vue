<template>
    <div class="container-fluid">
        <div class="b-form-group form-group">
            <div class="form-row">
                <div class="col-12 col-md-6 col-lg-2" v-if="usersValidate">
                    <v-select :options="chains"
                              @input="chainChange"
                              :value="this.chain_id"
                              v-model="chainSelected"
                              :placeholder="this.$t('hotels.search.messages.hotel_chain_search')"
                              autocomplete="true">
                    </v-select>
                </div>
                <div class="col-12 col-md-6 col-lg-2" v-if="usersValidate">
                    <v-select :options="ubigeos"
                              @input="ubigeoChange"
                              :value="this.ubigeo_id"
                              v-model="ubigeoSelected"
                              :placeholder="this.$t('hotels.search.messages.hotel_ubigeo_search')"
                              autocomplete="true"></v-select>
                </div>
                <div class="col-12 col-md-6 col-lg-2" v-if="usersValidate">
                    <v-select :options="classes_hotel"
                              @input="classHotelChange"
                              :value="this.typeclass_id"
                              v-model="classHotelSelected"
                              :placeholder="'Clase de hotel'"
                              autocomplete="true"></v-select>
                </div>
                <div class="col-12 col-md-6 col-lg-1" v-if="usersValidate">
                    <v-select :options="[{label: this.$t('hotels.status.active') , code: '1'},
                                {label: this.$t('hotels.status.disable') , code: '0'}]"
                              :placeholder="this.$t('hotels.search.messages.hotel_status_search')"
                              @input="statusChange"
                              v-model="statusSelected"
                              :value="this.status_id"
                    ></v-select>
                </div>
                <div class="col-12 col-md-6 col-lg-2" v-if="usersValidate">
                    <v-select
                        :options="optionsChannels"
                        placeholder="Channels"
                        @input="channelChange"
                        v-model="channelSelected"
                        :value="this.channel_id"
                    >
                    </v-select>
                </div>
                <div class="col-12 col-md-6 col-lg-2">
                    <input :class="{'form-control':true }"
                           id="aurora_code_name" name="aurora_code_name"
                           type="text"
                           ref="auroraCodeName"
                           :placeholder="this.$t('hotels.search.messages.hotel_aurora_name_search')">
                </div>
                <div class="col-12 col-md-6 col-lg-1">
                    <button @click="submit()" class="btn btn-secondary btn-block" type="submit">
                        <font-awesome-icon :icon="['fas', 'search']"/>
                    </button>
                </div>
            </div>
        </div>

        <table-server :columns="table.columns" :options="tableOptions" class="text-center" ref="table"
                      :key="updateTable">

            <div class="table-hotel_ubigeo" slot="hotel_ubigeo" slot-scope="props" style="font-size: 0.9em">
                {{props.row.city.state.country.translations[0].value}} - {{props.row.city.state.translations[0].value}}
                - {{props.row.city.translations[0].value}}
            </div>
            <div class="table-name" slot="name" slot-scope="props" style="font-size: 0.9em">
                {{ props.row.name }}
            </div>
            <div class="table-aurora_code" slot="aurora_code" slot-scope="props" style="font-size: 0.9em">
                <div v-for="channel in props.row.channels">
                    <div v-show="channel.id ===1">
                        {{ channel.pivot.code }}
                    </div>
                </div>
            </div>
            <div class="table-channel" slot="channel" slot-scope="props" style="font-size: 0.9em">
                <div v-for="channel in props.row.channels">
                    <div v-show="channel.id !==1">
                        {{ channel.name }} {{ channel.pivot.type == '2' ? 'PULL' : (channel.pivot.type == '1' ? 'PUSH' : '') }}
                    </div>
                </div>
            </div>
            <div class="table-status" slot="status" slot-scope="props" style="font-size: 0.9em">
                <div class="fake_change_status" @click="verify_uses(props.row)"></div>
                <b-form-checkbox
                        :checked="(props.row.status) ? 'true' : 'false'"
                        :id="'checkbox_'+props.row.id"
                        :name="'checkbox_'+props.row.id"
                        @change="changeState(props.row.id,props.row.status)"
                        switch>
                </b-form-checkbox>
            </div>
            <div class="table-actions" slot="actions" slot-scope="props" style="padding: 5px;">
                <b-dropdown class="mt-2 ml-2 mb-0" dropright size="sm">
                    <template slot="button-content">
                        <font-awesome-icon :icon="['fas', 'bars']" class="ml-1 p-0"/>
                    </template>
                    <router-link :to="'/hotels/edit/'+props.row.id" class="nav-link m-0 p-0"
                                 @click.native="getNameHotel(props.row)">
                        <b-dropdown-item-button class="m-0 p-0" v-if="$can('update', 'hotels')">
                            <font-awesome-icon :icon="['fas', 'dot-circle']" class="m-0"/>
                            {{$t('global.buttons.edit')}}
                        </b-dropdown-item-button>
                    </router-link>
                    <li @click="toManageHotel(props.row)" class="nav-link m-0 p-0">
                        <b-dropdown-item-button class="m-0 p-0">
                            <font-awesome-icon :icon="['fas', 'dot-circle']" class="m-0"/>
                            {{$t('hotels.manage_hotel')}}
                        </b-dropdown-item-button>
                    </li>
                    <b-dropdown-item-button @click="notify_new_hotel(props.row)" class="m-0 p-0"
                                            v-if="$can('notifynew', 'hotels')">
                        <font-awesome-icon :icon="['fas', 'dot-circle']" class="m-0"/>
                        {{$t('hotels.notify_new_hotel')}}
                    </b-dropdown-item-button>
                    <b-dropdown-item-button @click="will_remove(props.row)" class="m-0 p-0"
                                            v-if="$can('delete', 'hotels')">
                        <font-awesome-icon :icon="['fas', 'trash']" class="m-0"/>
                        {{$t('global.buttons.delete')}}
                    </b-dropdown-item-button>
                </b-dropdown>
            </div>
            <div class="table-advance" slot="advance" slot-scope="props" style="padding: 5px 25px;">
                <b-tooltip :target="'progress'+props.row.id" title="Tooltip content" placement="right"
                           v-if="props.row.progress_bar_value>0">
                    <ul style="list-style-type: none; padding: 0px;" class="text-left">
                        {{ $t('global.progress_bars_names.title') }}
                        <li v-for="progress_bar in progress_bars_names">
                            {{ $t('global.progress_bars_names.'+progress_bar.name) }}
                            <font-awesome-icon :icon="['fas', 'check']" class="ml-1 p-0" style="color: #4dbd74;"
                                               v-show="showIconProgressBar(progress_bar.name,props.row.progress_bars)"/>
                        </li>
                    </ul>
                </b-tooltip>
                <b-progress :id="'progress'+props.row.id" :max="max" style="background-color:#3c5e79">
                    <b-progress-bar :value="props.row.progress_bar_value"
                                    :variant="checkProgressBar(props.row.progress_bar_value)">
                        <div class="text-center">
                            {{ props.row.progress_bar_value}}%
                        </div>
                    </b-progress-bar>
                </b-progress>
            </div>
        </table-server>
        <b-modal :title="hotelName" centered ref="my-modal" size="sm">
            <p class="text-center">{{$t('global.message_delete')}}</p>

            <div slot="modal-footer">
                <button @click="remove()" class="btn btn-success">{{$t('global.buttons.accept')}}</button>
                <button @click="hideModal()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
            </div>
        </b-modal>


        <b-modal title="Coincidencias del Hotel" ref="my-modal-uses" hide-footer size="lg">

            <h4>Se encontró que el hotel está siendo utilizado en los siguientes paquetes.</h4>

            <div class="left">
                <button v-if="!see_confirm" class="btn btn-success" :disabled="loading" type="button" @click="will_send_report()">
                    <span>Reportar</span>
                </button>
                <div class="alert alert-warning" v-if="see_message">
                    <i class="fa fa-info-circle"></i> El hotel ya fué reportado por favor espere las 48 hrs y será desactivado automáticamente.
                </div>
                <div class="alert alert-warning" v-if="see_confirm">
                    <i class="fa fa-info-circle"></i> Notificará a los usuarios correspondientes y el hotel se desactivará automáticamente en 48 hrs.
                </div>
                <button v-if="see_confirm" class="btn btn-success" :disabled="loading" type="button" @click="send_report()">
                    <span>Confirmar</span>
                </button>
                <br>
                <br>
            </div>

            <table-client :columns="table_used_packages.columns" :data="used_hotels.packages"
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
                    [{{ props.row.plan_rates[0].service_type.abbreviation}}] - {{props.row.plan_rates[0].name}}
                </div>
                <div class="table-supplement" slot="categories" slot-scope="props">
                    <div class="badge badge-primary bag-category mr-1" v-if="category.services_count>0" v-for="category in props.row.plan_rates[0].plan_rate_categories">
                        {{category.category.translations[0].value}} ({{ category.services_count }})
                    </div>
                </div>
                <div class="table-supplement" slot="period" slot-scope="props">
                    {{props.row.plan_rates[0].date_from | formatDate}} - {{props.row.plan_rates[0].date_to | formatDate}}
                </div>
            </table-client>

        </b-modal>

        <b-modal title="Importar Hoteles de Hyperguest" centered ref="my-modal-import-hotels" size="xl" :busy="importing">
            <div style="position: relative;">
                <!-- Overlay de bloqueo durante la importación -->
                <div v-if="importing" class="modal-import-overlay">
                    <div class="modal-import-spinner">
                        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                            <span class="sr-only">Importando...</span>
                        </div>
                        <p class="mt-3">Importando hoteles...</p>
                    </div>
                </div>
                <div class="row" :class="{ 'modal-content-disabled': importing }">
                <!-- Búsqueda por país -->
                <div class="col-12 mb-3">
                    <div class="form-row align-items-end">
                        <div class="col-12 col-md-4">
                            <label for="country-select">Buscar por país:</label>
                            <v-select
                                id="country-select"
                                :options="countries"
                                @input="countryChange"
                                v-model="selectedCountry"
                                :placeholder="'Seleccionar país...'"
                                autocomplete="true">
                            </v-select>
                        </div>
                        <div class="col-12 col-md-2">
                            <button @click="searchHotelsToImport()" class="btn btn-primary btn-block" type="button" :disabled="loadingImport">
                                <font-awesome-icon :icon="['fas', 'search']"/>
                                Buscar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Filtro local (solo aparece después de la búsqueda) -->
                <div class="col-12 mb-3" v-if="allHotels.length > 0">
                    <div class="form-row">
                        <div class="col-12 col-md-8">
                            <input
                                id="hotel-name-search"
                                class="form-control"
                                type="text"
                                v-model="hotelNameSearch"
                                placeholder="Buscar por nombre"
                                @input="filterLocalResults()">
                        </div>
                        <div class="col-12 col-md-4">
                            <select
                                id="hotel-status-filter"
                                class="form-control"
                                v-model="selectedStatusFilter"
                                @change="filterLocalResults()">
                                <option value="">Todos los status</option>
                                <option v-for="status in availableStatuses" :key="status" :value="status">
                                    {{ status }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 text-right">
                    <div class="mb-2" v-if="allHotels.length > 0">
                        <span class="badge badge-info">
                            Total encontrados: {{ allHotels.length }}
                        </span>
                    </div>
                </div>
                <!-- Loading indicator -->
                <div class="col-12 text-center py-4" v-if="loadingImport">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Cargando...</span>
                    </div>
                    <p class="mt-2">Cargando...</p>
                </div>

                <div class="col-12" v-if="(filteredHotels.length > 0 || allHotels.length > 0) && !loadingImport">
                    <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                        <table class="table table-striped table-hover import-hotels-table">
                            <thead class="thead-light" style="position: sticky; top: 0; z-index: 10;">
                                <tr>
                                    <th width="50">
                                        <input
                                            type="checkbox"
                                            @change="toggleSelectAll($event)"
                                            :checked="allSelected">
                                    </th>
                                    <th>Nombre</th>
                                    <th>País</th>
                                    <th>Ciudad</th>
                                    <th>Región</th>
                                    <th>Cadena</th>
                                    <th>Código</th>
                                    <th>Status</th>
                                    <th>Coincidencias</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="hotel in paginatedHotels" :key="hotel.id || hotel.property_id">
                                    <td>
                                        <input
                                            type="checkbox"
                                            :value="hotel.id || hotel.property_id"
                                            v-model="selectedHotels"
                                            @change="updateSelectedCount()"
                                            :disabled="hotel.status && hotel.status !== 'Approved'"
                                            :title="hotel.status && hotel.status !== 'Approved' ? 'Hotel no aprobado. Status: ' + hotel.status : ''">
                                    </td>
                                    <td>{{ hotel.name }}</td>
                                    <td>{{ hotel.country || '-' }}</td>
                                    <td>{{ hotel.city || '-' }}</td>
                                    <td>{{ hotel.region || '-' }}</td>
                                    <td>{{ hotel.chain_name || '-' }}</td>
                                    <td>{{ hotel.id || '-' }}</td>
                                    <td class="text-center">
                                        <span v-if="hotel.status === 'Approved'" class="badge badge-success">
                                            {{ hotel.status }}
                                        </span>
                                        <span v-else-if="hotel.status" class="badge badge-warning">
                                            {{ hotel.status }}
                                        </span>
                                        <span v-else class="badge badge-secondary">
                                            N/A
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div v-if="hotel.matches && hotel.matches.length > 0" class="custom-matches-dropdown">
                                            <button
                                                type="button"
                                                class="btn btn-info btn-sm matches-dropdown-btn"
                                                @click.stop="toggleMatchesDropdown(hotel.id || hotel.property_id)">
                                                {{ hotel.matches.length }} coincidencia(s)
                                                <font-awesome-icon :icon="['fas', isMatchesDropdownOpen(hotel.id || hotel.property_id) ? 'angle-up' : 'angle-down']" class="ml-1"/>
                                            </button>
                                            <div
                                                v-if="isMatchesDropdownOpen(hotel.id || hotel.property_id)"
                                                class="matches-dropdown-menu"
                                                @click.stop>
                                                <div
                                                    v-for="match in hotel.matches"
                                                    :key="match.id"
                                                    class="match-item"
                                                    @click.stop>
                                                    <label class="match-item-label">
                                                        <input
                                                            type="checkbox"
                                                            :checked="isMatchSelected(hotel.id || hotel.property_id, match.id)"
                                                            @change="handleMatchCheckboxChange(hotel.id || hotel.property_id, match.id, $event)"
                                                            :disabled="hotel.status && hotel.status !== 'Approved'"
                                                            class="match-checkbox">
                                                        <div class="match-item-content">
                                                            <div>
                                                                <small class="text-muted"><strong>ID:</strong> {{ match.id }}</small>
                                                            </div>
                                                            <div class="mt-1">
                                                                <small class="text-muted"><strong>Hotel:</strong> {{ match.name }}</small>
                                                            </div>
                                                            <div v-if="match.channels && match.channels.length > 0" class="mt-1">
                                                                <small class="text-muted">
                                                                    <strong>Canales:</strong>
                                                                    <span v-for="(channel, idx) in match.channels" :key="idx">
                                                                        {{ channel }}<span v-if="idx < match.channels.length - 1">, </span>
                                                                    </span>
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <span v-else class="badge badge-secondary">
                                            Sin coincidencias
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- Paginación -->
                    <div class="row mt-3" v-if="totalPages > 1">
                        <div class="col-12">
                            <nav>
                                <ul class="pagination justify-content-center">
                                    <li class="page-item" :class="{ disabled: currentPage === 1 }">
                                        <a class="page-link" href="#" @click.prevent="goToPage(currentPage - 1)">Anterior</a>
                                    </li>
                                    <template v-for="(page, index) in visiblePages">
                                        <li
                                            v-if="page !== '...'"
                                            class="page-item"
                                            :key="'page-' + page"
                                            :class="{ active: page === currentPage }">
                                            <a class="page-link" href="#" @click.prevent="goToPage(page)">{{ page }}</a>
                                        </li>
                                        <li
                                            v-else
                                            class="page-item disabled"
                                            :key="'ellipsis-' + index">
                                            <span class="page-link">...</span>
                                        </li>
                                    </template>
                                    <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                                        <a class="page-link" href="#" @click.prevent="goToPage(currentPage + 1)">Siguiente</a>
                                    </li>
                                </ul>
                            </nav>
                            <div class="text-center">
                                <small>
                                    Mostrando {{ startIndex + 1 }} - {{ endIndex }} de {{ filteredHotels.length }} resultados
                                    (Página {{ currentPage }} de {{ totalPages }})
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12" v-if="filteredHotels.length === 0 && allHotels.length > 0">
                    <div class="alert alert-warning">
                        No se encontraron hoteles que coincidan con el filtro de búsqueda.
                    </div>
                </div>
                <div class="col-12" v-if="allHotels.length === 0 && searchPerformed">
                    <div class="alert alert-info">
                        No se encontraron hoteles con los criterios de búsqueda especificados.
                    </div>
                </div>
                </div>
            </div>

            <div slot="modal-footer">
                <span v-if="selectedHotels.length > 0" class="mr-3">
                    {{ selectedHotels.length }} hotel(es) seleccionado(s)
                </span>
                <button
                    @click="importSelectedHotels()"
                    class="btn btn-success"
                    type="button"
                    :disabled="selectedHotels.length === 0 || importing">
                    Importar
                </button>
                <button @click="hideImportModal()" class="btn btn-secondary" :disabled="importing">{{$t('global.buttons.cancel')}}</button>
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
    import BDropDownItem from 'bootstrap-vue/es/components/dropdown/dropdown-item'
    import BDropDownItemButton from 'bootstrap-vue/es/components/dropdown/dropdown-item-button'
    import BModal from 'bootstrap-vue/es/components/modal/modal'
    import Progress from 'bootstrap-vue/src/components/progress/progress'
    import ProgressBar from 'bootstrap-vue/src/components/progress/progress-bar'
    import Tooltip from 'bootstrap-vue/src/components/tooltip/tooltip'
    import vSelect from 'vue-select'
    import 'vue-select/dist/vue-select.css'
    import datePicker from 'vue-bootstrap-datetimepicker'

    export default {
        components: {
            datePicker,
            BFormCheckbox,
            'table-server': TableServer,
            'table-client': TableClient,
            'b-dropdown': BDropDown,
            'b-dropdown-item': BDropDownItem,
            'b-dropdown-item-button': BDropDownItemButton,
            BModal,
            'b-progress': Progress,
            'b-progress-bar': ProgressBar,
            'b-tooltip': Tooltip,
            vSelect,
        },
        data: () => {
            return {
                max: 100,
                value: 100,
                loading: false,
                usersValidate: false,
                hotels: [],
                tmpubigeos: [],
                ubigeos: [],
                chains: [],
                updateTable: 1,
                ubigeoSelected: [],
                chainSelected: [],
                statusSelected: [],
                progress_bars_names: [
                    {name: 'hotel_progress_details'},
                    {name: 'hotel_progress_location'},
                    {name: 'hotel_progress_amenities'},
                    {name: 'hotel_progress_channels'},
                    {name: 'hotel_progress_inventories'},
                    {name: 'hotel_progress_gallery'},
                    {name: 'hotel_progress_logo'},
                    {name: 'hotel_progress_rooms_create'},
                    {name: 'hotel_progress_rooms_descriptions'},
                ],
                hotel_id: null,
                ubigeo: null,
                hotelName: '',
                chain_id: '',
                ubigeo_id: '',
                country_id: '',
                state_id: '',
                city_id: '',
                district_id: '',
                status: '',
                status_id: '',
                aurora_code_name: '',
                table: {
                    columns: ['actions', 'hotel_ubigeo', 'name', 'aurora_code', 'channel', 'status', 'advance']
                },
                typeclass_id: '',
                classHotelSelected: [],
                classes_hotel: [],
                channelSelected: [],
                channel_id: '',
                channel_type: '',
                optionsChannels: [],
                table_used_packages: {
                    columns: ['package' , 'plan_rate', 'categories', 'period']
                },
                used_hotels: {
                    hotel_id : "",
                    hotel_name : "",
                    packages : []
                },
                see_confirm : false,
                see_message : false,
                // Import hotels modal data
                countries: [],
                selectedCountry: null,
                hotelNameSearch: '',
                selectedStatusFilter: 'Approved', // Status seleccionado por defecto
                allHotels: [], // Todos los hoteles obtenidos de Hyperguest
                filteredHotels: [], // Hoteles filtrados localmente
                searchResultsCount: 0,
                loadingImport: false,
                searchPerformed: false,
                selectedHotels: [], // IDs de hoteles seleccionados
                // Asociaciones de coincidencias: { 'hyperguestHotelId-dbHotelId': true }
                matchAssociations: {},
                // Control de dropdowns abiertos: { hyperguestHotelId: true }
                openMatchesDropdowns: {},
                // Paginación
                currentPage: 1,
                itemsPerPage: 10,
                importing: false,
            }
        },
        mounted() {
            this.$i18n.locale = localStorage.getItem('lang')
        },
        beforeDestroy() {
            // Remover listener al destruir el componente
            document.removeEventListener('click', this.closeAllMatchesDropdowns)
        },
        created() {
            this.filter();
            this.$root.$emit('updateTitleHotelList', { tab: 1 })
            this.$parent.$parent.$on('langChange', (payload) => {
                this.onUpdate()
            })
            // Escuchar evento para abrir el modal desde Layout
            this.$root.$on('openImportHotelsModal', () => {
                // Usar $nextTick para asegurar que el componente esté completamente montado
                this.$nextTick(() => {
                    this.showImportModal()
                })
            })
            localStorage.setItem("hotelchain", "")
            localStorage.setItem("hotelcountry", "")
            localStorage.setItem("hotelstate", "")
            localStorage.setItem("hotelcity", "")
            localStorage.setItem("hoteldistrict", "")
            localStorage.setItem("hotelstatus", "")
            localStorage.setItem("hotelauroraname", "")
            localStorage.setItem("hotelnamemanage", "")
            localStorage.setItem("typeclass_id", "")
            localStorage.setItem("channel_id", "")
            localStorage.setItem("channel_type", "")
            this.loadChain()
            this.loadubigeo()
            this.loadClassHotel()
            this.loadChannels()
            this.loadCountries()
        },
        computed: {
            // Obtener todos los status únicos de los hoteles
            availableStatuses() {
                const statuses = new Set()
                this.allHotels.forEach(hotel => {
                    if (hotel.status) {
                        statuses.add(hotel.status)
                    }
                })
                return Array.from(statuses).sort()
            },
            tableOptions: function () {
                return {
                    headings: {
                        actions: this.$i18n.t('global.table.actions'),
                        // id: 'ID',
                        hotel_ubigeo: this.$i18n.t('hotels.hotel_ubigeo'),
                        name: this.$i18n.t('hotels.hotel_name'),
                        aurora_code: this.$i18n.t('hotels.hotel_auroracode'),
                        channel: this.$i18n.t('hotels.hotel_channel'),
                        state: this.$i18n.t('hotels.hotel_status'),
                        advance: this.$i18n.t('hotels.advance')
                    },
                    sortable: [],
                    filterable: [],
                    perPageValues: [],
                    requestFunction: function (data) {

                        return API.get('/hotels?' +
                            'chain=' + localStorage.getItem("hotelchain") +
                            '&country=' + localStorage.getItem("hotelcountry") +
                            '&state=' + localStorage.getItem("hotelstate") +
                            '&city=' + localStorage.getItem("hotelcity") +
                            '&district=' + localStorage.getItem("hoteldistrict") +
                            '&status=' + localStorage.getItem("hotelstatus") +
                            '&typeclass_id=' + localStorage.getItem("typeclass_id") +
                            '&channel_id=' + localStorage.getItem("channel_id") +
                            '&channel_type=' + localStorage.getItem("channel_type") +
                            '&aurora_name=' + localStorage.getItem("hotelauroraname") +
                            '&lang=' + localStorage.getItem('lang'), {
                            params: data
                        })
                            .then((result) => {

                                return result.data
                            }).catch(() => {
                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: this.$t('hotels.error.messages.name'),
                                    text: this.$t('hotels.error.messages.connection_error')
                                })
                            })
                    },
                    responseAdapter: (response) => {
                        return response;
                    },
                    requestKeys: {}
                }
            },

            table_options_used_packages: function () {
                return {
                    headings: {
                        package: "Paquete",
                        plan_rate: "Plan Tarifario",
                        categories: "Categorías",
                        period: "Periodo"
                    },
                    sortable: [],
                    filterable: []
                }
            },
            // Computed para paginación
            totalPages: function () {
                return Math.ceil(this.filteredHotels.length / this.itemsPerPage)
            },
            paginatedHotels: function () {
                const start = (this.currentPage - 1) * this.itemsPerPage
                const end = start + this.itemsPerPage
                return this.filteredHotels.slice(start, end)
            },
            startIndex: function () {
                return (this.currentPage - 1) * this.itemsPerPage
            },
            endIndex: function () {
                const end = this.startIndex + this.itemsPerPage
                return end > this.filteredHotels.length ? this.filteredHotels.length : end
            },
            visiblePages: function () {
                const pages = []
                const total = this.totalPages
                const current = this.currentPage

                if (total <= 7) {
                    // Mostrar todas las páginas si son 7 o menos
                    for (let i = 1; i <= total; i++) {
                        pages.push(i)
                    }
                } else {
                    // Mostrar páginas con elipsis
                    if (current <= 3) {
                        for (let i = 1; i <= 5; i++) {
                            pages.push(i)
                        }
                        pages.push('...')
                        pages.push(total)
                    } else if (current >= total - 2) {
                        pages.push(1)
                        pages.push('...')
                        for (let i = total - 4; i <= total; i++) {
                            pages.push(i)
                        }
                    } else {
                        pages.push(1)
                        pages.push('...')
                        for (let i = current - 1; i <= current + 1; i++) {
                            pages.push(i)
                        }
                        pages.push('...')
                        pages.push(total)
                    }
                }
                return pages
            },
            allSelected: function () {
                // Solo considerar hoteles aprobados para la selección
                const approvedHotels = this.paginatedHotels.filter(hotel =>
                    !hotel.status || hotel.status === 'Approved'
                )
                if (approvedHotels.length === 0) return false
                return approvedHotels.every(hotel => {
                    const id = hotel.id || hotel.property_id
                    return this.selectedHotels.includes(id)
                })
            },
        },
        mounted: function () {
            this.$root.$emit('updateTitleUpdateList', { tab: 1 })
        },
        methods: {
            getNameHotel (data) {
                // localStorage.setItem("hotelnamemanage", name)
                this.$root.$emit('updateTitleHotel', { hotel_id: data.id })
            },
            will_send_report(){
                this.see_message = false
                this.loading = true

                API({
                    method: 'get',
                    url: 'deactivatable/entity?entity=App/Hotel&object_id='+ this.used_hotels.hotel_id
                })
                    .then((result) => {
                        if (result.data.success) {
                            if( result.data.data.length > 0 ){
                                this.see_message = true
                                let me = this
                                setTimeout(function(){
                                    me.see_message = false
                                }, 5000)
                            } else{
                                this.see_confirm = true
                            }
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.hotels'),
                                text: this.$t('global.error.messages.information_error')
                            })
                        }
                        this.loading = false
                    })
            },
            send_report(){

                this.loading = true

                let packages_ = []

                this.used_hotels.packages.forEach( p=>{
                    let obj = {}
                    if( p.code ){
                        obj.package = p.id + ' - [' + p.code + '] - '
                    } else{
                        if( p.extension==='1' ){
                            obj.package = '[E' + p.id + '] - '
                        } else {
                            obj.package = '[P' + p.id + '] - '
                        }
                    }
                    obj.package += p.translations[0].name

                    obj.plan_rate = '[' + p.plan_rates[0].service_type.abbreviation + '] - ' + p.plan_rates[0].name

                    obj.categories = []
                    p.plan_rates[0].plan_rate_categories.forEach( c=>{
                        if( c.services_count > 0 ){
                            obj.categories.push( c.category.translations[0].value + ' (' + c.services_count + ')' )
                        }
                    })

                    obj.period = this.formatDate(p.plan_rates[0].date_from) + ' - ' + this.formatDate(p.plan_rates[0].date_to)

                    packages_.push(obj)

                })

                let data = {
                    hotel_id : this.used_hotels.hotel_id,
                    hotel_name : this.used_hotels.hotel_name,
                    packages : packages_
                }

                API({
                    method: 'post',
                    url: 'hotels/' + data.hotel_id + '/uses/report',
                    data : { data : data }
                })
                    .then((result) => {
                        if (result.data.success) {
                            this.see_confirm = false
                            this.hideModal();
                            this.used_hotels.packages = []
                            this.used_hotels.hotel_id = ""
                            this.used_hotels.hotel_name = ""
                            this.$notify({
                                group: 'main',
                                type: 'success',
                                title: this.$t('global.modules.hotels'),
                                text: "Enviado correctamente"
                            })
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.hotels'),
                                text: this.$t('global.error.messages.information_error')
                            })
                        }
                        this.loading = false
                    })

            },
            verify_uses(hotel) {

                if (hotel.status) {
                    // Validar coincidencias
                    API({
                        method: 'get',
                        url: 'hotels/' + hotel.id + '/uses'
                    })
                        .then((result) => {
                            if (result.data.success === true) {
                                if (result.data.packages.length === 0) {
                                    document.getElementById('checkbox_' + hotel.id).click()
                                } else {
                                    this.$refs['my-modal'].hide();
                                    this.$refs['my-modal-uses'].show();
                                    this.used_hotels.packages = result.data.packages
                                    this.used_hotels.hotel_id = hotel.id
                                    this.used_hotels.hotel_name = '[' + hotel.channels[0].pivot.code + '] ' + hotel.name
                                }
                            } else {
                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: this.$t('global.modules.hotels'),
                                    text: this.$t('global.error.messages.information_error')
                                })
                            }
                        })
                } else { // Activando
                    document.getElementById('checkbox_' + hotel.id).click()
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
            will_remove(hotel) {
                this.hotel_id = hotel.id
                this.hotelName = hotel.name
                this.$refs['my-modal'].show()
                this.verify_uses(hotel)
            },
          notify_new_hotel(hotel) {
            this.hotel_id = hotel.id
            this.hotelName = hotel.name
            console.log(hotel)
            API({
              method: 'post',
              url: 'hotels/notify_new_hotel/' + hotel.id,
              data: {hotel: hotel}
            })
              .then((result) => {
                if (result.data.success === true) {
                  this.$notify({
                    group: 'main',
                    type: 'success',
                    title: this.$t('global.modules.hotels'),
                    text: 'Enviado Correctamente'
                  })
                } else {
                  this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('global.modules.hotels'),
                    text: this.$t('hotels.error.messages.information_error')
                  })
                }
              })
            // this.$refs['my-modal'].show()
          },
            hideModal() {
                this.$refs['my-modal'].hide()
                this.$refs['my-modal-uses'].hide()
            },
            // getNameHotel(name) {
            //     localStorage.setItem("hotelnamemanage", name)
            //     this.$root.$emit('updateTitle', {tab: 1})
            // },
            changeState: function (hotel_id, status) {
                API({
                    method: 'put',
                    url: 'hotels/' + hotel_id + '/status',
                    data: {status: status}
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.onUpdate()
                            this.updateTable += 1
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.hotels'),
                                text: this.$t('hotels.error.messages.information_error')
                            })
                        }
                    })
            },
            onUpdate() {
                this.$refs.table.$refs.tableserver.refresh()
            },
            remove() {
                API({
                    method: 'DELETE',
                    url: 'hotels/' + this.hotel_id
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.onUpdate()
                            this.hideModal()
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.hotels'),
                                text: this.$t('hotels.error.messages.hotel_delete')
                            })
                        }
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('hotels.error.messages.name'),
                        text: this.$t('hotels.error.messages.connection_error')
                    })
                })
            },
            filter() {
                API.get('hotels/filter')
                    .then((result) => {
                        this.usersValidate = !!result.data.access_bloqued;
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('hotels.error.messages.name'),
                        text: this.$t('hotels.error.messages.connection_error')
                    })
                })
            },
            loadChain() {
                API.get('chain/selectbox')
                    .then((result) => {
                        this.chains = result.data.data
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('hotels.error.messages.name'),
                        text: this.$t('hotels.error.messages.connection_error')
                    })
                })
            },
            loadubigeo() {
                API.get('/hotel/ubigeo/selectbox/' + localStorage.getItem('lang'))
                    .then((result) => {
                        let ubigeohotel = result.data.data
                        ubigeohotel.forEach((ubigeofor) => {
                            let dist_id = ' '
                            let dist_name = ' '
                            if (ubigeofor.district_id != null) {
                                dist_id = ubigeofor.district_id
                                dist_name = ubigeofor.district.translations[0].value
                            }

                            let cit_id = ubigeofor.city.translations[0].object_id
                            let cit_name = ubigeofor.city.translations[0].value

                            let stat_id = ubigeofor.city.state.translations[0].object_id
                            let stat_name = ubigeofor.city.state.translations[0].value

                            let countr_id = ubigeofor.city.state.country.translations[0].object_id
                            let countr_name = ubigeofor.city.state.country.translations[0].value


                            let code_ubigeo = countr_id + '_' + stat_id + '_' + cit_id + '_' + dist_id
                            let label_ubigeo = countr_name + '/' + stat_name + '/' + cit_name + '/' + dist_name

                            this.tmpubigeos.push({label: label_ubigeo, code: code_ubigeo})

                        })

                        this.ubigeos = this.getUnique(this.tmpubigeos, 'code')

                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('hotels.error.messages.name'),
                        text: this.$t('hotels.error.messages.connection_error')
                    })
                })
            },
            loadClassHotel: function () {
                API.get('type_classes_hotel/selectbox2?lang=' + localStorage.getItem('lang'))
                    .then((result) => {
                        this.classes_hotel = result.data.data
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('hotels.error.messages.name'),
                        text: this.$t('hotels.error.messages.connection_error')
                    })
                })
            },
            loadChannels: function () {
                API.get('channels?lang=' + localStorage.getItem('lang'))
                    .then((result) => {
                        if (result.data.success === true) {
                            this.optionsChannels = [];
                            result.data.data.forEach(channel => {
                                if (channel.name === 'HYPERGUEST') {
                                    // Agregar HYPERGUEST PUSH (tipo 1)
                                    this.optionsChannels.push({
                                        label: 'HYPERGUEST PUSH',
                                        value: channel.id,
                                        code: channel.code,
                                        type: '1'
                                    });
                                    // Agregar HYPERGUEST PULL (tipo 2)
                                    this.optionsChannels.push({
                                        label: 'HYPERGUEST PULL',
                                        value: channel.id,
                                        code: channel.code,
                                        type: '2'
                                    });
                                } else {
                                    // Agregar los demás canales normalmente
                                    this.optionsChannels.push({
                                        label: channel.name,
                                        value: channel.id,
                                        code: channel.code
                                    });
                                }
                            });
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('hotels.error.messages.name'),
                                text: this.$t('hotels.error.messages.connection_error')
                            });
                            this.optionsChannels = [];
                        }
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('hotels.error.messages.name'),
                        text: this.$t('hotels.error.messages.connection_error')
                    })
                })
            },
            classHotelChange: function (value) {
                this.class_hotel = value
                if (this.class_hotel != null) {
                    this.typeclass_id = this.class_hotel.code
                } else {
                    this.typeclass_id = ''
                }
            },
            chainChange: function (value) {
                this.chain = value
                if (this.chain != null) {
                    this.chain_id = this.chain.code
                } else {
                    this.chain_id = ''
                }
            },
            channelChange: function (value) {
                this.channel = value
                if (this.channel != null) {
                    this.channel_id = this.channel.value
                    this.channel_type = this.channel.type || ''
                } else {
                    this.channel_id = ''
                    this.channel_type = ''
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
            getUnique(arr, comp) {
                //store the comparison  values in array
                const unique = arr.map(e => e[comp]).// store the keys of the unique objects
                map((e, i, final) => final.indexOf(e) === i && i)
                // eliminate the dead keys & return unique objects
                    .filter((e) => arr[e]).map(e => arr[e]);
                return unique
            },
            submit() {

                let codigoubigeo = this.ubigeo_id || ''
                let arraycod = codigoubigeo.split('_')

                this.country_id = arraycod[0] || ''
                this.state_id = arraycod[1] || ''
                this.city_id = arraycod[2] || ''
                this.district_id = arraycod[3] || ''

                localStorage.setItem("hotelauroraname", document.getElementById('aurora_code_name').value.trim())
                localStorage.setItem("hotelchain", this.chain_id || '')
                localStorage.setItem("hotelcountry", this.country_id)
                localStorage.setItem("hotelstate", this.state_id)
                localStorage.setItem("hotelcity", this.city_id)
                localStorage.setItem("hoteldistrict", this.district_id)
                localStorage.setItem("hotelstatus", this.status_id || '')
                localStorage.setItem("typeclass_id", this.typeclass_id || '')
                localStorage.setItem("channel_id", this.channel_id || '')
                localStorage.setItem("channel_type", this.channel_type || '')

                this.onUpdate()


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
            toManageHotel: function (me) {
                this.$root.$emit('updateTitleHotel', { hotel_id: me.id })
                console.log(me)
                this.$router.push('/hotels/'+me.id+'/manage_hotel')
            },
            showImportModal() {
                // Verificar permisos antes de abrir el modal
                if (!this.$can('create', 'hotelsimporthyperguest')) {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.hotels'),
                        text: 'No tiene permisos para importar hoteles'
                    })
                    return
                }

                // Verificar que el modal esté disponible antes de intentar abrirlo
                if (this.$refs['my-modal-import-hotels']) {
                    this.$refs['my-modal-import-hotels'].show()
                    if (this.countries.length === 0) {
                        this.loadCountries()
                    }
                    // Agregar listener para cerrar dropdowns al hacer clic fuera
                    this.$nextTick(() => {
                        document.addEventListener('click', this.closeAllMatchesDropdowns)
                    })
                } else {
                    // Si el modal no está disponible, esperar un poco y reintentar
                    this.$nextTick(() => {
                        if (this.$refs['my-modal-import-hotels']) {
                            this.$refs['my-modal-import-hotels'].show()
                            if (this.countries.length === 0) {
                                this.loadCountries()
                            }
                            // Agregar listener para cerrar dropdowns al hacer clic fuera
                            this.$nextTick(() => {
                                document.addEventListener('click', this.closeAllMatchesDropdowns)
                            })
                        }
                    })
                }
            },
            hideImportModal() {
                if (this.$refs['my-modal-import-hotels']) {
                    this.$refs['my-modal-import-hotels'].hide()
                }
                this.hotelNameSearch = ''
                this.selectedCountry = null
                this.allHotels = []
                this.filteredHotels = []
                this.searchResultsCount = 0
                this.searchPerformed = false
                this.selectedHotels = []
                this.currentPage = 1
                this.importing = false
                // Cerrar todos los dropdowns y limpiar asociaciones
                this.openMatchesDropdowns = {}
                this.matchAssociations = {}
                // Remover listener
                document.removeEventListener('click', this.closeAllMatchesDropdowns)
            },
            loadCountries() {
                API.get('countries?lang=' + localStorage.getItem('lang'))
                    .then((result) => {
                        if (result.data.success === true) {
                            this.countries = []
                            result.data.data.forEach(country => {
                                if (country.translations && country.translations.length > 0) {
                                    this.countries.push({
                                        label: country.translations[0].value,
                                        code: country.id,
                                        iso: country.iso,
                                        value: country.id
                                    })
                                }
                            })
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('hotels.error.messages.name'),
                                text: this.$t('hotels.error.messages.connection_error')
                            })
                        }
                    }).catch(() => {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('hotels.error.messages.name'),
                            text: this.$t('hotels.error.messages.connection_error')
                        })
                    })
            },
            countryChange: function (value) {
                this.selectedCountry = value
            },
            searchHotelsToImport() {
                // Validar que se haya seleccionado un país
                if (!this.selectedCountry || !this.selectedCountry.iso) {
                    this.$notify({
                        group: 'main',
                        type: 'warning',
                        title: this.$t('global.modules.hotels'),
                        text: 'Por favor, seleccione un país para realizar la búsqueda'
                    })
                    return
                }

                this.loadingImport = true
                this.searchPerformed = false
                this.allHotels = []
                this.filteredHotels = []
                this.selectedHotels = []
                this.currentPage = 1
                this.hotelNameSearch = '' // Reset local search
                this.selectedStatusFilter = 'Approved' // Reset status filter to Approved

                const params = {
                    country_code: this.selectedCountry.iso,
                    lang: localStorage.getItem('lang') || 'es'
                }

                API({
                    method: 'post',
                    url: 'hotels/search-import',
                    data: params
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.allHotels = result.data.data || []
                            this.searchResultsCount = result.data.count || this.allHotels.length
                            this.filterLocalResults()
                            this.searchPerformed = true
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.hotels'),
                                text: result.data.message || this.$t('hotels.error.messages.information_error')
                            })
                            this.searchPerformed = true
                        }
                        this.loadingImport = false
                    })
                    .catch(() => {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('hotels.error.messages.name'),
                            text: this.$t('hotels.error.messages.connection_error')
                        })
                        this.loadingImport = false
                        this.searchPerformed = true
                    })
            },
            filterLocalResults() {
                // Filtrar localmente en el array ya obtenido
                let filtered = [...this.allHotels]

                // Filtrar por nombre si hay búsqueda
                if (this.hotelNameSearch && this.hotelNameSearch.trim() !== '') {
                    const searchTerm = this.hotelNameSearch.toLowerCase().trim()
                    filtered = filtered.filter(hotel => {
                        const name = (hotel.name || '').toLowerCase()
                        const country = (hotel.country_name || hotel.country || '').toLowerCase()
                        const city = (hotel.city_name || hotel.city || '').toLowerCase()
                        const address = (hotel.address || '').toLowerCase()

                        return name.includes(searchTerm) ||
                               country.includes(searchTerm) ||
                               city.includes(searchTerm) ||
                               address.includes(searchTerm)
                    })
                }

                // Filtrar por status si hay uno seleccionado
                if (this.selectedStatusFilter && this.selectedStatusFilter !== '') {
                    filtered = filtered.filter(hotel => {
                        return hotel.status === this.selectedStatusFilter
                    })
                }

                this.filteredHotels = filtered
                this.currentPage = 1 // Reset to first page when filtering
            },
            toggleSelectAll(event) {
                if (event.target.checked) {
                    // Solo seleccionar hoteles aprobados
                    const approvedHotels = this.paginatedHotels.filter(hotel =>
                        !hotel.status || hotel.status === 'Approved'
                    )
                    const approvedIds = approvedHotels.map(hotel => hotel.id || hotel.property_id)
                    // Agregar los IDs aprobados sin duplicar
                    approvedIds.forEach(id => {
                        if (!this.selectedHotels.includes(id)) {
                            this.selectedHotels.push(id)
                        }
                    })
                } else {
                    // Deseleccionar solo los de la página actual
                    const pageIds = this.paginatedHotels.map(hotel => hotel.id || hotel.property_id)
                    this.selectedHotels = this.selectedHotels.filter(id => !pageIds.includes(id))
                }
                this.updateSelectedCount()
            },
            updateSelectedCount() {
                // Método para actualizar contador si es necesario
            },
            importSelectedHotels() {
                if (!this.$can('create', 'hotelsimporthyperguest')) {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.hotels'),
                        text: 'No tiene permisos para importar hoteles'
                    })
                    return
                }

                if (this.selectedHotels.length === 0) {
                    return
                }

                this.importing = true

                // Obtener los datos completos de los hoteles seleccionados
                const hotelsToImport = this.allHotels.filter(hotel => {
                    const hotelId = hotel.id || hotel.property_id
                    return this.selectedHotels.includes(hotelId)
                })

                const hotelsData = hotelsToImport.map(hotel => {
                    const hotelId = hotel.id || hotel.property_id

                    // Obtener todas las asociaciones seleccionadas para este hotel de Hyperguest
                    const associatedHotels = []
                    Object.keys(this.matchAssociations).forEach(key => {
                        const [hyperguestId, dbHotelId] = key.split('-')
                        if (hyperguestId == hotelId && this.matchAssociations[key]) {
                            associatedHotels.push(parseInt(dbHotelId))
                        }
                    })

                    return {
                        property_id: hotelId,
                        name: hotel.name || '',
                        country: hotel.country || null,
                        city: hotel.city || null,
                        region: hotel.region || null,
                        chain_name: hotel.chain_name || null,
                        associated_hotels: associatedHotels, // IDs de hoteles de BD seleccionados para asociar
                        data: hotel // Guardar todos los datos del hotel
                    }
                });

                // console.log(hotelsData);

                API({
                    method: 'post',
                    url: 'hotels/import-from-hyperguest',
                    data: {
                        hotels: hotelsData
                    }
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.$notify({
                                group: 'main',
                                type: 'success',
                                title: this.$t('global.modules.hotels'),
                                text: `${this.selectedHotels.length} hotel(es) agregado(s) a la cola de importación correctamente`
                            })

                            // Ejecutar el endpoint import-batches después de guardar la importación
                            this.$root.$emit('refreshImportBatches')

                            this.hideImportModal()
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.hotels'),
                                text: result.data.message || 'Error al agregar hoteles a la cola de importación'
                            })
                        }
                        this.importing = false
                    })
                    .catch(() => {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('hotels.error.messages.name'),
                            text: this.$t('hotels.error.messages.connection_error')
                        })
                        this.importing = false
                    })
            },
            // Métodos de paginación
            goToPage(page) {
                if (page >= 1 && page <= this.totalPages) {
                    this.currentPage = page
                }
            },
            // Métodos para manejar asociaciones de coincidencias
            isMatchSelected(hyperguestHotelId, dbHotelId) {
                if (!hyperguestHotelId || !dbHotelId) {
                    return false
                }
                const associationKey = `${hyperguestHotelId}-${dbHotelId}`
                return !!this.matchAssociations[associationKey]
            },
            handleMatchCheckboxChange(hyperguestHotelId, dbHotelId, event) {
                event.stopPropagation()
                const isChecked = event.target.checked
                this.toggleMatchAssociation(hyperguestHotelId, dbHotelId, isChecked)
            },
            toggleMatchAssociation(hyperguestHotelId, dbHotelId, isChecked) {
                const associationKey = `${hyperguestHotelId}-${dbHotelId}`

                if (isChecked) {
                    // Deseleccionar todos los demás matches de este hotel de Hyperguest
                    Object.keys(this.matchAssociations).forEach(key => {
                        const [hyperguestId] = key.split('-')
                        if (hyperguestId == hyperguestHotelId && key !== associationKey) {
                            this.$delete(this.matchAssociations, key)
                        }
                    })

                    // Agregar la nueva asociación (solo una permitida)
                    this.$set(this.matchAssociations, associationKey, true)
                    // Seleccionar automáticamente el hotel principal si no está seleccionado
                    if (!this.selectedHotels.includes(hyperguestHotelId)) {
                        this.selectedHotels.push(hyperguestHotelId)
                    }
                } else {
                    // Remover asociación
                    this.$delete(this.matchAssociations, associationKey)
                    // Verificar si hay otros matches seleccionados para este hotel
                    const hasOtherMatches = this.hasSelectedMatches(hyperguestHotelId)
                    // Si no hay más matches seleccionados, deseleccionar el hotel principal
                    if (!hasOtherMatches) {
                        const index = this.selectedHotels.indexOf(hyperguestHotelId)
                        if (index > -1) {
                            this.selectedHotels.splice(index, 1)
                        }
                    }
                }
            },
            // Verificar si hay matches seleccionados para un hotel de Hyperguest
            hasSelectedMatches(hyperguestHotelId) {
                return Object.keys(this.matchAssociations).some(key => {
                    const [hyperguestId] = key.split('-')
                    return hyperguestId == hyperguestHotelId && this.matchAssociations[key]
                })
            },
            // Métodos para controlar dropdowns
            toggleMatchesDropdown(hyperguestHotelId) {
                const key = String(hyperguestHotelId)
                const isOpen = !!this.openMatchesDropdowns[key]

                // Cerrar todos los demás dropdowns antes de abrir uno nuevo
                this.openMatchesDropdowns = {}

                // Si el dropdown que se está intentando abrir estaba cerrado, abrirlo
                if (!isOpen) {
                    this.$set(this.openMatchesDropdowns, key, true)
                }
            },
            isMatchesDropdownOpen(hyperguestHotelId) {
                const key = String(hyperguestHotelId)
                return !!this.openMatchesDropdowns[key]
            },
            closeAllMatchesDropdowns(event) {
                // Cerrar todos los dropdowns si se hace clic fuera
                if (!event || !event.target) return

                const clickedInside = event.target.closest('.custom-matches-dropdown')
                if (!clickedInside) {
                    this.openMatchesDropdowns = {}
                }
            },

        },

        filters:{
            formatDate: function (_date) {
                if (_date == undefined) {
                    // console.log('fecha no parseada: ' + _date)
                    return
                }
                _date = _date.split('-')
                _date = _date[2] + '/' + _date[1] + '/' + _date[0]
                return _date
            }
        }

    }
</script>


<style>
    .progress-bar {
        color: white;
        -webkit-border-radius: 0.25rem;
        -moz-border-radius: 0.25rem;
        border-radius: 0.25rem;
    }
    .fake_change_status{
        width: 100px;
        height: 21px;
        position: absolute;
        z-index: 1;
    }
    .import-hotels-table tbody tr td {
        padding: 12px 8px;
    }
    .import-hotels-table thead th {
        padding: 12px 8px;
    }
    /* Custom matches dropdown styles */
    .custom-matches-dropdown {
        position: relative;
        display: inline-block;
    }
    .matches-dropdown-btn {
        cursor: pointer;
        white-space: nowrap;
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    .matches-dropdown-menu {
        position: absolute;
        top: calc(100% + 2px);
        right: 0;
        z-index: 1050;
        min-width: 350px;
        max-width: 500px;
        max-height: 200px;
        overflow-y: auto;
        overflow-x: hidden;
        background-color: #fff;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        padding: 0;
    }
    .match-item {
        padding: 0;
        border-bottom: 1px solid #e9ecef;
    }
    .match-item:first-child {
        border-top-left-radius: 0.25rem;
        border-top-right-radius: 0.25rem;
    }
    .match-item:last-child {
        border-bottom: none;
        border-bottom-left-radius: 0.25rem;
        border-bottom-right-radius: 0.25rem;
    }
    .match-item-label {
        display: flex;
        align-items: flex-start;
        padding: 10px 15px;
        margin: 0;
        cursor: pointer;
        width: 100%;
        transition: background-color 0.15s ease-in-out;
        user-select: none;
    }
    .match-item-label:hover {
        background-color: #f8f9fa;
    }
    .match-checkbox {
        margin-right: 12px;
        margin-top: 4px;
        cursor: pointer;
        flex-shrink: 0;
        width: 18px;
        height: 18px;
        accent-color: #17a2b8;
    }
    .match-checkbox:disabled {
        cursor: not-allowed;
        opacity: 0.5;
    }
    .match-item-content {
        flex: 1;
        white-space: normal;
        line-height: 1.4;
        text-align: left;
    }
    .match-item-content small {
        display: block;
        font-size: 0.875rem;
        color: #6c757d;
        text-align: left;
    }
    .match-item-content div {
        text-align: left;
    }
    .match-item-content div:last-child {
        color: #212529;
        font-weight: 400;
        text-align: left;
    }
    /* Estilos para bloqueo del modal durante importación */
    .modal-import-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(255, 255, 255, 0.9);
        z-index: 1050;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.25rem;
    }
    .modal-import-spinner {
        text-align: center;
    }
    .modal-content-disabled {
        pointer-events: none;
        opacity: 0.6;
    }
</style>

