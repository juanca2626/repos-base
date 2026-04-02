<template>
    <div class="vld-parent">
        <loading :active.sync="loading" :can-cancel="false" color="#BD0D12"></loading>
        <div v-show="newForm">
            <div class="row col-12">
                <div class="col-6">
                </div>
                <div class="col-6">
                    <div class="col-6">
                        <label>Seleccionar todas las tarifas</label>
                        <div class="col-sm-12 p-0">
                            <c-switch :value="true" class="mx-1" color="success"
                                      v-model="all_service_rates"
                                      variant="pill">
                            </c-switch>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row col-12 mb-2">
                <div class="col-6">
                    <label class="col-form-label">Paquetes disponibles</label>
                    <div class="input-group">
                 <span class="input-group-append">
                    <button class="btn btn-outline-secondary button_icon" type="button">
                        <font-awesome-icon :icon="['fas', 'search']"/>
                    </button>
                 </span>
                        <input class="form-control" id="search_services" type="search" v-model="query" value="">
                    </div>
                    <ul class="style_list_ul" id="list_services" ref="list_services">
                        <draggable :list="services">
                            <li :class="{'style_list_li':true, 'item':true, 'selected':service.selected}"
                                :id="'service_'+index"
                                @click="selectService(service,index)" v-for="(service,index) in services">
                            <span class="style_span_li"><span class="alert alert-warning"
                                                              style="padding:0px !important">[{{ service.code}}]</span> {{ service.name}}</span>
                            </li>
                        </draggable>
                    </ul>
                </div>
                <div class="col-6">
                    <label class="col-form-label">
                        {{$t('clientsmanageclienthotel.available_hotels_rates') }}</label>
                    <ul class="style_list_ul" id="list_rates"
                        style="border-top:1px solid #ccc;max-height: 196px;height: 196px;">
                        <draggable :list="rates">
                            <li :class="{'style_list_li':true, 'item':true, 'selected':rate.selected}"
                                :id="'rate_'+index"
                                @click="selectRate(rate,index)" v-for="(rate,index) in rates">
                        <span
                            class="style_span_li">{{ rate.service_type}} - {{ rate.name}}</span>
                            </li>
                        </draggable>
                    </ul>
                </div>
            </div>
            <form @submit.prevent="validateBeforeSubmit">
                <div class="row col-12">
                    <div class="col-sm-2">
                        <label class="col-form-label">Desde</label>
                        <div class="input-group ">
                            <date-picker
                                :config="datePickerFromOptions"
                                @dp-change="setDateFrom"
                                id="date_from"
                                name="date_from" ref="datePickerFrom"
                                v-model="date_from"
                                data-vv-as="date_from"
                                data-vv-name="date_from"
                                v-validate="'required'"
                                autocomplete="off"
                            >
                            </date-picker>
                            <div class="input-group-append">
                                <button class="btn btn-secondary datepickerbutton" title="Toggle"
                                        type="button">
                                    <i class="far fa-calendar"></i>
                                </button>
                            </div>
                            <span class="invalid-feedback-select" v-show="errors.has('date_from')">
                            <span>{{ errors.first('date_from') }}</span>
                        </span>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <label class="col-form-label">Hasta</label>
                        <div class="input-group ">
                            <date-picker
                                :config="datePickerToOptions"
                                @dp-change="setDateTo"
                                id="date_to"
                                name="date_to" ref="datePickerTo"
                                v-model="date_to"
                                data-vv-as="date_to"
                                data-vv-name="date_to"
                                autocomplete="off"
                                v-validate="'required'"
                            >
                            </date-picker>
                            <div class="input-group-append">
                                <button class="btn btn-secondary datepickerbutton" title="Toggle"
                                        type="button">
                                    <i class="far fa-calendar"></i>
                                </button>
                            </div>
                            <span class="invalid-feedback-select" v-show="errors.has('date_to')">
                            <span>{{ errors.first('date_to') }}</span>
                        </span>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <label class="col-form-label">Oferta %</label>
                        <input :class="{'form-control':true }"
                               id="value_offer"
                               name="value_offer"
                               data-vv-as="value_offer"
                               data-vv-name="value_offer"
                               type="text"
                               v-model="value_offer"
                               v-validate="'required'">
                        <span class="invalid-feedback-select" v-show="errors.has('value_offer')">
                            <span>{{ errors.first('value_offer') }}</span>
                        </span>
                    </div>
                    <div class="col-sm-2">
                        <label>Es oferta?</label>
                        <div class="col-sm-12 p-0">
                            <c-switch :value="true" class="mx-1" color="success"
                                      v-model="is_offer"
                                      variant="pill">
                            </c-switch>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <label>.</label>
                        <div class="col-sm-12 p-0">
                            <button @click="validateBeforeSubmit()" class="form-control btn btn-success" type="button"
                                    v-if="!loading">
                                {{ $t('global.buttons.save') }}
                            </button>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <label>.</label>
                        <button @click="cancelForm()"
                                class="form-control btn btn-danger"
                                type="button" v-if="!loading">
                            {{ $t('global.buttons.cancel') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="row col-12 mb-2" v-show="!newForm">
            <button @click="addOffer()" class="btn btn-success" type="button"
                    v-if="$can('create', 'clientserviceoffer')">
                Nuevo
            </button>
        </div>
        <div v-show="!newForm">
            <table-server :columns="table.columns" :options="tableOptions" :url="urlOffer"
                          class="text-center"
                          ref="table">
                <div class="table-service-service" slot="package" slot-scope="props" style="font-size: 0.9em">
                    {{props.row.rate_plan.package.code}} - {{props.row.rate_plan.package.translations[0].name}}
                </div>
                <div class="table-service-rate" slot="rate" slot-scope="props" style="width: 150px;font-size: 0.9em">
                    {{props.row.rate_plan.id}} - [{{props.row.rate_plan.service_type.code}}]
                    {{props.row.rate_plan.name}}
                </div>
                <div class="table-service-dates" slot="dates" slot-scope="props" style="width: 155px;font-size: 0.9em">
                    {{props.row.date_from}} - {{props.row.date_to}}
                </div>
                <div class="table-service-value" slot="value" slot-scope="props" style="font-size: 0.9em">
                    {{props.row.value}} %
                </div>
                <div class="table-is_offer" slot="is_offer" slot-scope="props" style="font-size: 0.9em">
                    <b-form-checkbox
                        :checked="checkboxCheckedOffer(props.row.is_offer)"
                        :id="'checkboxoffer_'+props.row.id"
                        :name="'checkboxoffer_'+props.row.id"
                        @change="changeOffer(props.row.id,props.row.is_offer)"
                        switch>
                    </b-form-checkbox>
                </div>
                <div class="table-status" slot="status" slot-scope="props" style="font-size: 0.9em">
                    <b-form-checkbox
                        :checked="checkboxCheckedStatus(props.row.status)"
                        :id="'checkbox_'+props.row.id"
                        :name="'checkbox_'+props.row.id"
                        @change="changeState(props.row.id,props.row.status)"
                        switch>
                    </b-form-checkbox>
                </div>
                <div class="table-actions" slot="actions" slot-scope="props" style="padding: 5px;">
                    <b-dropdown class="mt-2 ml-2 mb-0" dropleft size="sm" v-if="props.row.event != 'restored'">
                        <template slot="button-content">
                            <font-awesome-icon :icon="['fas', 'bars']" class="ml-1 p-0"/>
                        </template>
                        <li @click="showModal(props.row.id,props.row.rate_plan.package.code)" class="nav-link m-0 p-0">
                            <b-dropdown-item-button class="m-0 p-0" v-if="$can('delete', 'clientpackageoffer')">
                                <font-awesome-icon :icon="['fas', 'edit']" class="m-0"/>
                                Eliminar
                            </b-dropdown-item-button>
                        </li>
                    </b-dropdown>
                </div>
            </table-server>
            <b-modal :title="offerName" centered ref="my-modal" size="sm">
                <p class="text-center">{{$t('global.message_delete')}}</p>
                <div slot="modal-footer">
                    <button @click="remove()" class="btn btn-success">{{$t('global.buttons.accept')}}</button>
                    <button @click="hideModal()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
                </div>
            </b-modal>
        </div>
    </div>
</template>
<script>
    import {API} from '../../../../../api';
    import draggable from 'vuedraggable';
    import TableServer from '../../../../../components/TableServer';
    import datePicker from 'vue-bootstrap-datetimepicker';
    import {Switch as cSwitch} from '@coreui/vue';
    import Loading from 'vue-loading-overlay';
    import 'vue-loading-overlay/dist/vue-loading.css';
    import BModal from 'bootstrap-vue/es/components/modal/modal';

    export default {
        components: {
            draggable,
            datePicker,
            cSwitch,
            Loading,
            BModal,
            'table-server': TableServer,
        },
        data() {
            return {
                urlOffer: '',
                date_from: '',
                date_to: '',
                offer: '',
                newForm: false,
                is_offer: true,
                all_services: false,
                all_service_rates: false,
                users: [],
                scroll_limit: 2900,
                services: [],
                page: 1,
                markup_rate: '',
                selectPeriod: '',
                selectRatePlan: '',
                nameSelectService: '',
                rates_selected: [],
                porcentage: '',
                markup: '',
                periods: [],
                clientRates: [],
                clientRatesSelected: [],
                limit: 100,
                count: 0,
                num_pages: 1,
                query: '',
                interval: null,
                services_selected: [],
                page_services_selected: 1,
                limit_services_selected: 100,
                count_services_selected: 0,
                num_pages_services_selected: 1,
                query_services_selected: '',
                scroll_limit_services_selected: 2900,
                interval_services_selected: null,
                value_offer: 1,
                loading: false,
                rates: [],
                table: {
                    columns: ['id', 'package', 'rate', 'dates', 'value', 'is_offer', 'status', 'actions'],
                },
                datePickerFromOptions: {
                    format: 'DD/MM/YYYY',
                    useCurrent: false,
                    locale: localStorage.getItem('lang'),
                },
                datePickerToOptions: {
                    format: 'DD/MM/YYYY',
                    useCurrent: false,
                    locale: localStorage.getItem('lang'),
                },
                offer_id: '',
                offerName: '',
            };
        },
        computed: {
            tableOptions: function() {
                return {
                    headings: {
                        id: 'ID',
                        package: 'Paquete',
                        rate: 'Tarifa',
                        dates: 'Fechas',
                        value: 'Oferta %',
                        is_offer: 'Oferta',
                        status: 'Estado',
                        actions: this.$i18n.t('global.table.actions'),
                    },
                    sortable: [],
                    filterable: [],
                    perPageValues: [],
                    responseAdapter({data}) {
                        return {
                            data: data.data,
                            count: data.count,
                        };
                    },
                    params: {
                        'client_id': this.$route.params.client_id,
                        //     'date_from': (this.form.date_from === '') ? '' : moment(this.form.date_from,'DD/MM/YYYY').format('YYYY-MM-DD'),
                        //     'date_to': (this.form.date_to === '') ? '' : moment(this.form.date_to,'DD/MM/YYYY').format('YYYY-MM-DD'),
                        //     'event':this.form.evento
                    },
                    requestFunction: function(data) {
                        let url = '/client/package/offers?token=' + window.localStorage.getItem('access_token') +
                            '&lang='
                            + localStorage.getItem('lang') + '&region_id=' + this.$route.params.region_id
                        return API.get(url, {
                            params: data,
                        }).catch(function(e) {
                            this.dispatch('error', e);
                        }.bind(this));

                    },
                };
            },
        },
        mounted: function() {
            this.init();
        },
        methods: {
            init: function() {
                this.getPeriods();
                this.getServicesSelected();
                let search_services = document.getElementById('search_services');
                let timeout_services;
                search_services.addEventListener('keydown', () => {
                    clearTimeout(timeout_services);
                    timeout_services = setTimeout(() => {
                        this.getServices();
                        clearTimeout(timeout_services);
                    }, 1000);
                });
                this.interval = setInterval(this.getScrollTop, 3000);
                this.interval_services_selected = setInterval(this.getScrollTopServicesSelected, 3000);
            },
            checkboxCheckedStatus: function(status) {
                if (status) {
                    return 'true';
                } else {
                    return 'false';
                }
            },
            checkboxCheckedOffer: function(status) {
                if (status) {
                    return 'true';
                } else {
                    return 'false';
                }
            },
            addOffer() {
                this.newForm = true;
            },
            cancelForm() {
                this.newForm = false;
            },
            setDateFrom(e) {
                if (e.date == false) {
                    this.date_from = '';
                } else {
                    this.$refs.datePickerTo.dp.minDate(e.date);
                }
            },
            hideModal() {
                this.$refs['my-modal'].hide();
            },
            showModal(id, offer) {
                this.offer_id = id;
                this.offerName = offer;
                this.$refs['my-modal'].show();
            },
            remove() {
                this.loading = true;
                API({
                    method: 'DELETE',
                    url: 'client/package/offer/' + this.offer_id,
                }).then((result) => {
                    this.loading = false;
                    if (result.data.success === true) {
                        this.$refs.table.$refs.tableserver.getData();
                        this.hideModal();
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Cliente',
                            text: this.$t('global.error.messages.service_delete'),
                        });
                    }
                }).catch(() => {
                    this.loading = false;
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Cliente',
                        text: this.$t('global.error.messages.connection_error'),
                    });
                });
            },
            setDateTo(e) {
                if (e.date == false) {
                    this.date_to = '';
                }
            },
            changeOffer: function(offer_id, status) {
                API({
                    method: 'put',
                    url: 'client/package/offer/' + offer_id + '/offer',
                    data: {status: status},
                }).then((result) => {
                    if (result.data.success === true) {
                        this.$refs.table.$refs.tableserver.getData();
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.services'),
                            text: this.$t('global.error.messages.information_error'),
                        });
                    }
                });
            },
            changeState: function(offer_id, status) {
                API({
                    method: 'put',
                    url: 'client/package/offer/' + offer_id + '/status',
                    data: {status: status},
                }).then((result) => {
                    if (result.data.success === true) {
                        this.$refs.table.$refs.tableserver.getData();
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.services'),
                            text: this.$t('global.error.messages.information_error'),
                        });
                    }
                });
            },
            searchPeriod: function() {
                this.getServices();
                this.getServicesSelected();
                this.searchMarkup();
                this.searchMarkupId();
            },
            searchMarkup: function() {
                let data = this.periods.find(period => period.text == this.selectPeriod);
                this.porcentage = data.porcen_service;
            },
            searchMarkupId: function() {
                let data = this.periods.find(period => period.text == this.selectPeriod);
                this.markupId = data.value;
            },
            selectMarkup: function() {
                let search_service = this.searchSelectService();
                if (search_service !== -1) {
                    let services = this.services[search_service];
                    if (services.markup) {
                        return services.markup;
                    } else {
                        return this.porcentage;
                    }
                }
            },
            showError: function(title, text, isLoading = true) {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: title,
                    text: text,
                });
                if (isLoading === true) {
                    this.loading = true;
                }
            },
            validateMarkup: function() {
                if (this.markup == '') {
                    this.showError(
                        this.$t('clientsmanageclienthotel.title'),
                        this.$t('clientsmanageclienthotel.error.messages.add_markup'),
                    );
                    return false;
                }
            },
            validatePeriod: function() {
                if (this.selectPeriod == '') {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('title'),
                        text: this.$t('error.messages.select_period'),
                    });
                    return false;
                }
            },
            selectService: function(service, index) {
                if (this.services[index].selected) {
                    this.rates = [];
                    this.all_service_rates = false;
                    this.$set(this.services[index], 'selected', false);
                } else {
                    this.all_service_rates = true;
                    // this.getClientRatePlans(service);
                    this.getClientRatePlanSelected(service);
                    this.nameSelectService = service.name;
                    this.setPropertySelectedInServices();
                    this.$set(this.services[index], 'selected', true);
                    // this.markup = service.markup;
                }
            },
            selectServiceServicesSelected: function(service, index) {
                if (this.services_selected[index].selected) {
                    this.$set(this.services_selected[index], 'selected', false);
                } else {
                    this.getClientRatePlans(service.service_id);
                    this.getClientRatePlanSelected(service.service_id);
                    this.nameSelectService = service.name;
                    this.setPropertySelectedInServicesSelected();
                    this.$set(this.services_selected[index], 'selected', true);
                    this.markup = service.markup;
                }
            },
            searchSelectService: function() {
                for (let i = 0; i < this.services.length; i++) {
                    if (this.services[i].selected) {
                        return i;
                        break;
                    }
                }
                return -1;
            },
            searchSelectServiceServicesSelected: function() {
                for (let i = 0; i < this.services_selected.length; i++) {
                    if (this.services_selected[i].selected) {
                        return i;
                        break;
                    }
                }
                return -1;
            },
            setPropertySelectedInServices: function() {
                for (let i = 0; i < this.services.length; i++) {
                    this.$set(this.services[i], 'selected', false);
                }
            },
            setPropertySelectedInServicesSelected: function() {
                for (let i = 0; i < this.services_selected.length; i++) {
                    this.$set(this.services_selected[i], 'selected', false);
                }
            },
            moveOneService: function() {
                if (this.loading === false) {
                    if (this.validatePeriod() != false) {
                        this.searchMarkup();

                        this.loading = true;
                        let search_service = this.searchSelectService();
                        if (search_service !== -1) {
                            API({
                                method: 'post',
                                url: 'client_services/store',
                                data: {
                                    data: this.services[search_service],
                                    period: this.selectPeriod,
                                    porcentage: this.porcentage,
                                },
                            }).then((result) => {
                                if (result.data.success === true) {

                                    this.$set(this.services[search_service], 'selected', false);
                                    this.services[search_service].service_client_id = result.data.service_client_id;
                                    this.services[search_service].markup = this.porcentage;
                                    this.services_selected.push(this.services[search_service]);
                                    this.services.splice(search_service, 1);
                                    this.loading = false;
                                }
                            }).catch(() => {
                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: this.$t('error.messages.name'),
                                    text: this.$t('error.messages.connection_error'),
                                });
                            });
                        } else {
                            if (this.services.length > 0) {
                                this.loading = true;
                                let element = this.services.shift();
                                API({
                                    method: 'post',
                                    url: 'client_services/store',
                                    data: {
                                        data: element,
                                        period: this.selectPeriod,
                                        porcentage: this.porcentage,
                                    },
                                }).then((result) => {
                                    if (result.data.success === true) {
                                        element.service_client_id = result.data.service_client_id;
                                        this.services_selected.push(element);

                                        this.loading = false;
                                    }
                                }).catch((e) => {
                                    this.$notify({
                                        group: 'main',
                                        type: 'error',
                                        title: this.$t('error.messages.name'),
                                        text: this.$t('error.messages.connection_error') + e,
                                    });
                                });
                            }
                        }

                    }
                } else {
                    console.log('Bloqueado accion');
                }
            },
            validateBeforeSubmit() {
                this.$validator.validateAll().then((result) => {
                    if (result) {
                        this.submit();
                        this.$validator.reset(); //solution
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Cliente',
                            text: this.$t('global.error.information_complete'),
                        });
                        this.loading = false;
                    }
                });
            },

            submit: function() {
                this.loading = true;
                let search_rate = this.searchSelectRate();
                let rate = [];
                console.log(search_rate)
                if (!this.all_services) {
                    if (search_rate !== -1) {
                        rate.push(this.rates[search_rate].id);
                    } else {
                        if (this.rates.length === 0) {
                            this.loading = false;
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Cliente',
                                text: 'No se encontraron tarifas para el servicio seleccionado',
                            });
                            return false;
                        } else {
                            this.rates.forEach(item => rate.push(item.id));
                        }
                    }
                }
                API({
                    method: 'post',
                    url: 'client/package/offer',
                    data: {
                        client_id: this.$route.params.client_id,
                        period: this.selectPeriod,
                        service_rate_id: rate,
                        date_from: (this.date_from === '') ? '' : moment(this.date_from, 'DD/MM/YYYY').
                            format('YYYY-MM-DD'),
                        date_to: (this.date_to === '') ? '' : moment(this.date_to, 'DD/MM/YYYY').format('YYYY-MM-DD'),
                        is_offer: this.is_offer,
                        value_offer: this.value_offer,
                        all_services: this.all_services,
                    },
                }).then((result) => {
                    if (result.data.success === true) {
                        this.$refs.table.$refs.tableserver.getData();
                        this.newForm = false;
                    } else if (result.data.success === false && result.data.message === 'RATE_RANGE_EXIST') {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Cliente',
                            text: 'La oferta ingresada para el paquete en el rango de fechas ya existe',
                        });
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Cliente',
                            text: this.$t('error.messages.connection_error'),
                        });
                    }
                    this.loading = false;
                }).catch(() => {
                    this.loading = false;
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Cliente',
                        text: this.$t('error.messages.connection_error'),
                    });
                });
            },
            addRatePlan: function() {
                if (this.loading === false) {
                    if (this.validatePeriod() != false) {
                        this.searchMarkup();
                        this.loading = true;
                        let search_service = this.searchSelectServiceServicesSelected();
                        if (search_service !== -1) {

                            API({
                                method: 'post',
                                url: 'service_client_rate_plans/store',
                                data: {
                                    client_id: this.$route.params.client_id,
                                    period: this.selectPeriod,
                                    service_rate_id: this.selectRatePlan,
                                },
                            }).then((result) => {
                                if (result.data.success === true) {
                                    this.getClientRatePlans(this.services_selected[search_service].service_id);
                                    this.getClientRatePlanSelected(this.services_selected[search_service].service_id);
                                    this.loading = false;
                                }
                            }).catch(() => {
                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: this.$t('error.messages.name'),
                                    text: this.$t('error.messages.connection_error'),
                                });
                            });
                        } else {
                            if (this.services_selected.length > 0) {
                                this.loading = true;
                                let element = this.services_selected.shift();
                                API({
                                    method: 'post',
                                    url: 'service_client_rate_plans/store',
                                    data: {
                                        client_id: this.$route.params.client_id,
                                        period: this.selectPeriod,
                                        porcentage: this.porcentage,
                                        service_rate_id: this.selectRatePlan,
                                    },
                                }).then((result) => {
                                    if (result.data.success === true) {
                                        this.getClientRatePlans(element.service_id);
                                        this.getClientRatePlanSelected(element.service_id);
                                        this.loading = false;
                                    }
                                }).catch((e) => {
                                    this.loading = false;
                                    this.$notify({
                                        group: 'main',
                                        type: 'error',
                                        title: this.$t('error.messages.name'),
                                        text: this.$t('error.messages.connection_error') + e,
                                    });
                                });
                            }
                        }
                    }
                } else {
                    console.log('Bloqueado accion');
                }
            },
            updateMarkupRate(id, data) {
                if (this.validatePeriod() != false) {

                    this.searchMarkup();

                    this.loading = true;
                    API({
                        method: 'put',
                        url: 'service_client_rate_plans/update',
                        data: {data: data},
                    }).then((result) => {
                        if (result.data.success === false) {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('modules.contacts'),
                                text: this.$t('error.messages.contact_incorrect'),
                            });
                            this.loading = false;
                        } else {
                            this.loading = false;
                            this.getClientRatePlans(data.service_rate.service_id);
                            this.getClientRatePlanSelected(data.service_rate.service_id);
                        }
                    }).catch(() => {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('error.messages.name'),
                            text: this.$t('error.messages.connection_error'),
                        });
                    });
                }
            },
            inverseOneService: function() {
                if (this.loading === false) {
                    if (this.validatePeriod() != false) {
                        this.searchMarkup();

                        this.loading = true;

                        let search_service = this.searchSelectServiceServicesSelected();
                        if (search_service !== -1) {

                            API({
                                method: 'post',
                                url: 'client_services/inverse',
                                data: {
                                    data: this.services_selected[search_service],
                                    period: this.selectPeriod,
                                },
                            }).then((result) => {
                                if (result.data.success === true) {
                                    this.getServices();
                                    this.getServicesSelected();
                                    this.clientRates = [];
                                    this.clientRatesSelected = [];
                                    this.markup = '';
                                    this.loading = false;
                                }
                            }).catch(() => {
                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: this.$t('error.messages.name'),
                                    text: this.$t('error.messages.connection_error'),
                                });
                            });
                        } else {
                            if (this.services_selected.length > 0) {
                                this.loading = true;
                                let element = this.services_selected.shift();
                                API({
                                    method: 'post',
                                    url: 'client_services/inverse',
                                    data: {
                                        data: element,
                                        period: this.selectPeriod,
                                    },
                                }).then((result) => {
                                    if (result.data.success === true) {
                                        this.services.push(element);
                                        this.markup = '';
                                        this.loading = false;
                                    }
                                }).catch((e) => {
                                    this.$notify({
                                        group: 'main',
                                        type: 'error',
                                        title: this.$t('error.messages.name'),
                                        text: this.$t('error.messages.connection_error') + e,
                                    });
                                });
                            }
                        }
                    }
                } else {
                    console.log('Bloqueado accion');
                }
            },
            moveAllServices: function() {
                if (this.loading === false) {
                    if (this.validatePeriod() !== false) {
                        this.searchMarkup();

                        this.loading = true;

                        if (this.services.length > 0) {
                            for (let i = 0; i < this.services.length; i++) {
                                this.$set(this.services[i], 'selected', false);
                                this.services_selected.push(this.services[i]);
                            }
                            this.services = [];

                            API({
                                method: 'post',
                                url: 'client_services/store/all',
                                data: {
                                    client_id: this.$route.params.client_id,
                                    period: this.selectPeriod,
                                    porcentage: this.porcentage,
                                },
                            }).then((result) => {
                                if (result.data.success === true) {
                                    this.getServicesSelected();
                                    this.markup = '';
                                    this.loading = false;
                                }
                            }).catch((e) => {
                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: this.$t('error.messages.name'),
                                    text: this.$t('error.messages.connection_error') + e,
                                });
                            });
                        }
                    } else {
                        console.log('Bloqueado accion');
                    }

                }
            },
            inverseAllServices: function() {
                if (this.loading === false) {
                    if (this.validatePeriod() != false) {
                        this.searchMarkup();

                        this.loading = true;
                        if (this.services_selected.length > 0) {
                            for (let i = 0; i < this.services_selected.length; i++) {

                                this.services.push(this.services_selected[i]);
                            }
                            this.services_selected = [];
                            API({
                                method: 'post',
                                url: 'client_services/inverse/all',
                                data: {
                                    client_id: this.$route.params.client_id,
                                    period: this.selectPeriod,
                                },
                            }).then((result) => {
                                if (result.data.success === true) {
                                    this.getServices();
                                    this.clientRates = [];
                                    this.clientRatesSelected = [];
                                    this.markup = '';
                                    this.loading = false;
                                }
                            }).catch((e) => {
                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: this.$t('error.messages.name'),
                                    text: this.$t('error.messages.connection_error') + e,
                                });
                            });
                        }
                    }
                } else {
                    console.log('Bloqueado accion');
                }
            },
            calculateNumPages: function(num_services, limit) {
                this.num_pages = Math.ceil(num_services / limit);
            },
            calculateNumPagesServicesSelected: function(num_services, limit) {
                this.num_pages_services_selected = Math.ceil(num_services / limit);
            },
            getScrollTop: function() {
                if(!this.$refs.list_services) return

                let scroll = this.$refs.list_services.scrollTop;

                console.log(scroll);
                if (scroll > this.scroll_limit) {
                    this.page += 1;
                    this.scroll_limit = 2900 * this.page;
                    if (this.page === this.num_pages) {
                        clearInterval(this.interval);
                        this.getServicesScroll();
                    } else {

                        this.getServicesScroll();
                    }

                }
            },
            getPeriods: function() {
                API.get('client_services/selectPeriod?lang=' + localStorage.getItem('lang') + '&client_id=' +
                    this.$route.params.client_id + '&region_id=' + this.$route.params.region_id).then((result) => {
                    this.periods = result.data.data;
                    this.selectPeriod = !result.data.data.length ? '' : result.data.data[0].text;
                    this.porcentage = !result.data.data.length ? '' : result.data.data[0].porcen_service;
                    this.getServices();
                    this.getServicesSelected();
                }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('error.messages.name'),
                        text: this.$t('error.messages.connection_error'),
                    });
                });
            },
            removeRatePlan: function(id, data) {
                if (id != null) {
                    API({
                        method: 'post',
                        url: 'service_client_rate_plans/delete',
                        data: {
                            id: id,
                        },
                    }).then((result) => {
                        if (result.data.success === true) {
                            this.getClientRatePlans(data.service_rate.service_id);
                            this.getClientRatePlanSelected(data.service_rate.service_id);
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('modules.contacts'),
                                text: this.$t('error.messages.contact_delete'),
                            });
                            this.loading = false;
                        }
                    }).catch((e) => {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('error.messages.name'),
                            text: this.$t('error.messages.connection_error') + e,
                        });
                    });
                }
            },
            getClientRatePlans: function(service_id) {
                API({
                    method: 'post',
                    url: 'service_client_rate_plans',
                    data: {
                        client_id: this.$route.params.client_id,
                        period: this.selectPeriod,
                        service_id: service_id,
                    },
                }).then((result) => {
                    this.rates_selected = result.data.data;
                    console.log(this.rates_selected);
                }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('error.messages.name'),
                        text: this.$t('error.messages.connection_error'),
                    });
                });
            },
            getClientRatePlanSelected: function(service) {
                this.rates = service.rates;
            },
            getServices: function() {
                API({
                    method: 'post',
                    url: 'client/packages',
                    data: {
                        page: 1,
                        limit: this.limit,
                        query: this.query,
                        client_id: this.$route.params.client_id,
                        region_id: this.$route.params.region_id
                    },
                }).then((result) => {
                    this.services = result.data.data;
                    this.count = result.data.count;
                    this.calculateNumPages(result.data.count, this.limit);
                    this.scroll_limit = 2900;
                    this.$refs.list_services.scrollTop = 0;

                }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('error.messages.name'),
                        text: this.$t('error.messages.connection_error'),
                    });
                });

            },
            getServicesScroll: function() {
                API({
                    method: 'post',
                    url: 'service/search/client',
                    data: {
                        page: this.page,
                        limit: this.limit,
                        query: this.query,
                        client_id: this.$route.params.client_id,
                        period: this.selectPeriod,
                    },
                }).then((result) => {
                    let services = result.data.data;
                    for (let i = 0; i < services.length; i++) {
                        this.services.push(services[i]);
                    }
                    if (this.page === 1) {
                        this.count = result.data.count;
                        this.calculateNumPages(result.data.count, this.limit);
                    }
                }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('error.messages.name'),
                        text: this.$t('error.messages.connection_error'),
                    });
                });

            },
            getServicesSelected: function() {
                API({
                    method: 'post',
                    url: 'client_services',
                    data: {
                        page: 1,
                        limit: this.limit_services_selected,
                        query: this.query_services_selected,
                        client_id: this.$route.params.client_id,
                        period: this.selectPeriod,
                    },
                }).then((result) => {
                    this.services_selected = result.data.data;
                    this.count_services_selected = result.data.count;
                    this.calculateNumPagesServicesSelected(result.data.count, this.limit_services_selected);
                    this.scroll_limit_services_selected = 2900;

                }).catch((e) => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('error.messages.name'),
                        text: this.$t('error.messages.connection_error') + e,
                    });
                });
            },
            getServicesScrollSelected: function() {
                API({
                    method: 'post',
                    url: 'client_services',
                    data: {
                        page: this.page_services_selected,
                        limit: this.limit_services_selected,
                        query: this.query_services_selected,
                        client_id: this.$route.params.client_id,
                        period: this.selectPeriod,
                    },
                }).then((result) => {
                    let services_selected = result.data.data;
                    for (let i = 0; i < services_selected.length; i++) {
                        this.services_selected.push(services_selected[i]);
                    }
                    if (this.page === 1) {
                        this.count = result.data.count;
                        this.calculateNumPagesServicesSelected(result.data.count, this.limit);
                    }
                }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('error.messages.name'),
                        text: this.$t('error.messages.connection_error'),
                    });
                });
            },
            setPropertySelectedInRates: function() {
                for (let i = 0; i < this.rates.length; i++) {
                    this.$set(this.rates[i], 'selected', false);
                }
            },
            selectRate: function(service, index) {
                if (this.rates[index].selected) {
                    this.all_service_rates = true;
                    this.$set(this.rates[index], 'selected', false);
                } else {
                    this.setPropertySelectedInRates();
                    this.all_service_rates = false;
                    this.$set(this.rates[index], 'selected', true);
                }
            },
            searchSelectRate: function() {
                for (let i = 0; i < this.rates.length; i++) {
                    if (this.rates[i].selected) {
                        return i;
                        break;
                    }
                }
                return -1;
            },
            validateMarkupRate: function() {
                if (this.markup_rate == '') {
                    this.showError(
                        this.$t('clientsmanageclienthotel.title'),
                        this.$t('clientsmanageclienthotel.error.messages.add_markup'),
                    );
                    return false;
                }
            },
            updateOneRate: function() {
                if (this.loading === false) {
                    if (this.validateMarkupRate() !== false) {
                        this.loading = true;
                        let search_service = this.searchSelectService();
                        let search_rate = this.searchSelectRate();
                        if (search_rate !== -1) {
                            API({
                                method: 'put',
                                url: 'service_client_rate_plans/update',
                                data: {
                                    rate_plan_id: this.rates[search_rate].id,
                                    client_id: this.$route.params.client_id,
                                    period: this.selectPeriod,
                                    markup: this.markup_rate,
                                },
                            }).then((result) => {
                                if (result.data.success === true) {
                                    let service = this.services[search_service];
                                    this.getClientRatePlans(service.service_id);
                                    this.getClientRatePlanSelected(service.service_id);
                                    this.markup_rate = '';
                                    this.loading = false;
                                }
                            }).catch(() => {
                                this.showError(
                                    this.$t('clientsmanageclienthotel.error.messages.name'),
                                    this.$t('clientsmanageclienthotel.error.messages.connection_error'),
                                );
                                this.loading = false;
                            });
                        } else {
                            this.loading = false;
                        }
                    }
                } else {
                    console.log('Bloqueado accion');
                }
            },
            searchSelectRateRatesSelected: function() {
                for (let i = 0; i < this.rates_selected.length; i++) {
                    if (this.rates_selected[i].selected) {
                        return i;
                        break;
                    }
                }
                return -1;
            },
            setPropertySelectedInRatesSelected: function() {
                for (let i = 0; i < this.rates_selected.length; i++) {
                    this.$set(this.rates_selected[i], 'selected', false);
                }
            },
            selectRateRatesSelected: function(hotel, index) {
                if (this.rates_selected[index].selected) {
                    this.$set(this.rates_selected[index], 'selected', false);
                } else {
                    this.setPropertySelectedInRatesSelected();
                    this.$set(this.rates_selected[index], 'selected', true);
                }
            },
            beforeDestroy() {
                clearInterval(this.interval);
                clearInterval(this.interval_services_selected);
            },
        },
    };
</script>

<style>
    body {
        -webkit-touch-callout: none;
        -webkit-user-select: none;
        -khtml-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }

    .style_list_ul {
        height: 160px;
        max-height: 160px;
        overflow-y: scroll;
        list-style-type: none;
        padding: 0px;
        margin-left: -1px;
        border-left: 1px solid #ccc;
        border-bottom: 1px solid #ccc;
    }

    .selected {
        background-color: #BD0D12;
        color: white;
    }

    .style_list_li {
        border-bottom: 1px solid #ccc;
        padding: 5px 5px 5px 5px;
        cursor: move;
    }

    .style_span_li {
        margin-left: 5px;
    }

    #search_services:focus {
        box-shadow: none;
        border-color: #ccc;
    }

    #search_services {
        border-top: 1px solid #ccc;
        border-right: 1px solid #ccc;
        border-bottom: 1px solid #ccc;
        border-bottom-right-radius: 0px;
        border-top-right-radius: 0.2rem;
    }

    .button_icon {
        background-color: #f0f3f5 !important;
        border-top-left-radius: 0.2rem;
        color: #000;
        cursor: default !important;
    }

    .button_icon:hover {
        box-shadow: none;
        background-color: #f0f3f5 !important;
    }

    .button_icon:focus {
        box-shadow: none;
        background-color: #f0f3f5 !important;
    }

    .button_icon:active {
        box-shadow: none;
        background-color: #f0f3f5 !important;
    }

    .mover_controls {
        padding: 10px;
        margin-bottom: 10px;
    }
</style>

<i18n src="./services.json"></i18n>
