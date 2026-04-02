<template>
    <div class="vld-parent">
        <loading :active.sync="loading" :can-cancel="false" color="#BD0D12"></loading>
        <div>
            <div class="col-12">
                <div class="row">
                    <div class="col-5 pull-right">
                        <div class="b-form-group form-group">
                            <div class="form-row">
                                <label class="col-sm-3 col-form-label" for="period">{{ $t('period') }}</label>
                                <div class="col-sm-8">
                                    <b-form-select v-model="selectPeriod" @change="searchPeriod" :options="periods"></b-form-select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>

            <div class="col-md-12 mt-2 mb-2">
                <hr>
            </div>

            <div class="row col-12 mb-2">
                <div class="col-5">
                    <label class="col-form-label">{{ $t('available_services') }}</label>
                    <div class="input-group">
                         <span class="input-group-append">
                            <button class="btn btn-outline-secondary button_icon" type="button">
                                <font-awesome-icon :icon="['fas', 'search']"/>
                            </button>
                         </span>
                        <input class="form-control" id="search_services" type="search" v-model="query" value="">
                    </div>
                    <ul class="style_list_ul" id="list_services" ref="servicesList">
                        <draggable :list="services">
                            <li :class="{'style_list_li':true, 'item':true, 'selected':service.selected}"
                                :id="'service_'+index"
                                @click="selectService(service,index)" v-for="(service,index) in services">
                            <span class="style_span_li"><span class="alert alert-warning"
                                                              style="padding:0px !important">[{{ service.aurora_code}}]</span> {{ service.name}}</span>
                            </li>
                        </draggable>
                    </ul>
                </div>
                <div class="col-md-7">
                    <div>
                        <b-tabs>
                            <b-tab title="Reserva" active>
                                <form @submit.prevent="validateBeforeSubmit">
                                    <div class="row col-12">
                                        <div class="col-sm-4">
                                        <label>{{ $t('services.reservation_from') }}</label>
                                        <div class="col-sm-12 p-0">
                                            <input :class="{'form-control':true }"
                                                   data-vv-as="reserve"
                                                   data-vv-name="reserve"
                                                   type="number"
                                                   v-model.number="reserve_from"
                                                   v-validate="'required'">
                                            <span class="invalid-feedback" v-show="errors.has('reserve')">
                                                <span>{{ errors.first('reserve') }}</span>
                                            </span>
                                        </div>
                                    </div>
                                        <div class="col-sm-4">
                                            <label>.</label>
                                            <div class="col-sm-12 p-0">
                                                <v-select :options="unitDurationsReserve"
                                                          :value="unitDurationReserve_id"
                                                          @input="unitDurationReserveChange"
                                                          autocomplete="true"
                                                          data-vv-as="unit duration"
                                                          data-vv-name="unitDurationReserve"
                                                          v-model="unitDurationsReserveSelected"
                                                          v-validate="'required'">
                                                </v-select>
                                                <span class="invalid-feedback-select"
                                                      v-show="errors.has('unitDurationReserve')">
                                            <span>{{ errors.first('unitDurationReserve') }}</span>
                                        </span>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                        <label>.</label>
                                        <div class="col-sm-12 p-0">
                                            <button @click="validateBeforeSubmit()" class="form-control btn btn-success"
                                                    type="button"
                                                    v-if="!loading">
                                                {{ $t('global.buttons.save') }}
                                            </button>
                                        </div>
                                    </div>
                                    </div>
                                </form>
                            </b-tab>
                        </b-tabs>
                    </div>
                </div>
            </div>

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
    import vSelect from 'vue-select'
    import 'vue-select/dist/vue-select.css'
    export default {
        components: {
            draggable,
            datePicker,
            cSwitch,
            Loading,
            vSelect,
            BModal,
            'table-server': TableServer,
        },
        data() {
            return {
                unitDurationsReserve: [
                    { 'code': 1, 'label': 'Horas' },
                    { 'code': 2, 'label': 'Días' }
                ],
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
                reserve_from: 0,
                num_pages: 1,
                query: '',
                interval: null,
                services_selected: [],
                unitDurationsReserveSelected: [],
                page_services_selected: 1,
                limit_services_selected: 100,
                count_services_selected: 0,
                num_pages_services_selected: 1,
                query_services_selected: '',
                scroll_limit_services_selected: 2900,
                interval_services_selected: null,
                value_offer: 1,
                unitDurationReserve_id: 2,
                loading: false,
                rates: [],
                table: {
                    columns: ['id', 'service', 'rate', 'dates', 'value', 'is_offer', 'status', 'actions'],
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
                service_id: '',
                offerName: '',
            };
        },
        computed: {
            tableOptions: function() {
                return {
                    headings: {
                        id: 'ID',
                        service: 'Servicio',
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
                        let url = '/client/service/offers?token=' + window.localStorage.getItem('access_token') +
                            '&lang='
                            + localStorage.getItem('lang');
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
            // this.interval = setInterval(this.getScrollTop, 3000);
            // this.interval_services_selected = setInterval(this.getScrollTopServicesSelected, 3000);
            this.unitDurationsReserveSelected= {
                code: 2,
                label: 'Días',
            }
        },
        methods: {
            unitDurationReserveChange: function (value) {
                this.unitDurationReserve = value
                if (this.unitDurationReserve != null) {
                    this.unitDurationReserve_id = this.unitDurationReserve.code
                } else {
                    this.unitDurationReserve_id = ''
                    this.unitDurationsReserveSelected = []
                }
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
                    url: 'client/service/offer/' + this.offer_id,
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
                    url: 'client/service/offer/' + offer_id + '/offer',
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
                    url: 'client/service/offer/' + offer_id + '/status',
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
                    this.service_id = service.service_id
                    this.getClientServiceSetting(service.service_id);
                    this.setPropertySelectedInServices();
                    this.$set(this.services[index], 'selected', true);
                    this.markup = service.markup;
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
                if(this.service_id == ''){
                    return false
                }
                this.loading = true;
                API({
                    method: 'post',
                    url: 'client/service/setting/reserve',
                    data: {
                        client_id: this.$route.params.client_id,
                        service_id: this.service_id,
                        reserve_from: this.reserve_from,
                        unit_duration: this.unitDurationReserve_id,
                    },
                }).then((result) => {
                    if (result.data.success === true) {
                        this.$notify({
                            group: 'main',
                            type: 'success',
                            title: 'Cliente',
                            text: 'Se guardo correctamente',
                        });
                    } else if (result.data.success === false && result.data.message === 'RATE_RANGE_EXIST') {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Cliente',
                            text: 'La oferta ingresada para el servicio en el rango de fechas ya existe',
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
            calculateNumPages: function(num_services, limit) {
                this.num_pages = Math.ceil(num_services / limit);
            },
            calculateNumPagesServicesSelected: function(num_services, limit) {
                this.num_pages_services_selected = Math.ceil(num_services / limit);
            },
            getScrollTop: function() {
                if (!this.$refs.servicesList) return;

                let scroll = this.$refs.servicesList.scrollTop;

                if (!scroll) {
                    // console.error('Elemento list_services_selected no encontrado');
                    return;
                }

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
                API.get('client_services/selectPeriod?lang=' + localStorage.getItem('lang') + '&client_id='
                + this.$route.params.client_id
                + '&region_id=' + this.$route.params.region_id
                )
                .then((result) => {
                    let periods = result.data.data;
                    let data_period = [];
                    periods.forEach((period) => {
                        data_period.push({
                            text: period.text + ' - ' + period.porcen_service + ' %',
                            value: period.text,
                        });
                    });

                    this.periods = data_period;

                    this.porcentage = !result.data.data.length ? '' : result.data.data[0].porcen_service;
                    var d = new Date();
                    var year = d.getFullYear();
                    if (result.data.data.length > 0) {
                        result.data.data.forEach((_year) => {
                            if (parseInt(_year.text) == year) {
                                this.selectPeriod = year
                            }
                        });
                    } else {
                        this.selectPeriod = year
                    }
                    this.loading = false
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

            },
            getClientServiceSetting: function(service_id) {
                this.loading = true
                API({
                    method: 'post',
                    url: 'client/service/setting',
                    data: {
                        client_id: this.$route.params.client_id,
                        service_id: service_id,
                        region_id: this.$route.params.region_id
                    },
                }).then((result) => {
                    this.loading = false
                    let setting = result.data.data;
                    if(setting.length > 0){
                        this.reserve_from = setting[0].reservation_from
                        if (setting[0].unit_duration_reserve === 1) {
                            this.unitDurationsReserveSelected = {
                                code: setting[0].unit_duration_reserve,
                                label: 'Horas',
                            }
                        } else if (setting[0].unit_duration_reserve === 2) {
                            this.unitDurationsReserveSelected = {
                                code: setting[0].unit_duration_reserve,
                                label: 'Días',
                            }
                        }
                    }else{
                        this.reserve_from = 0
                        this.unitDurationsReserveSelected = {
                            code: 2,
                            label: 'Días',
                        }
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
            getClientRatePlanSelected: function(service_id) {
                API({
                    method: 'post',
                    url: 'service_client_rate_plans/selected',
                    data: {
                        client_id: this.$route.params.client_id,
                        period: this.selectPeriod,
                        service_id: service_id,
                        region_id: this.$route.params.region_id
                    },
                }).then((result) => {
                    // this.clientRatesSelected = result.data.data
                    this.rates = result.data.data;

                }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('error.messages.name'),
                        text: this.$t('error.messages.connection_error'),
                    });
                });
            },
            getServices: function() {
                this.loading = true
                API({
                    method: 'post',
                    url: 'service/search/client',
                    data: {
                        page: 1,
                        limit: this.limit,
                        query: this.query,
                        client_id: this.$route.params.client_id,
                        period: this.selectPeriod,
                        region_id: this.$route.params.region_id
                    },
                }).then((result) => {
                    this.loading = false
                    this.services = result.data.data;
                    this.count = result.data.count;
                    this.calculateNumPages(result.data.count, this.limit);
                    this.scroll_limit = 2900;
                    document.getElementById('list_services').scrollTop = 0;
                }).catch(() => {
                    this.loading = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('error.messages.name'),
                        text: this.$t('error.messages.connection_error'),
                    });
                });

            },
            getServicesScroll: function() {
                this.loading = true
                API({
                    method: 'post',
                    url: 'service/search/client',
                    data: {
                        page: this.page,
                        limit: this.limit,
                        query: this.query,
                        client_id: this.$route.params.client_id,
                        period: this.selectPeriod,
                        region_id: this.$route.params.region_id
                    },
                }).then((result) => {
                    this.loading = false
                    let services = result.data.data;
                    for (let i = 0; i < services.length; i++) {
                        this.services.push(services[i]);
                    }
                    if (this.page === 1) {
                        this.count = result.data.count;
                        this.calculateNumPages(result.data.count, this.limit);
                    }
                }).catch(() => {
                    this.loading = false
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
                        region_id: this.$route.params.region_id
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
                        region_id: this.$route.params.region_id
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
            moveOneRate: function() {
                if (this.loading === false) {
                    if (this.validatePeriod() !== false) {
                        this.searchMarkup();
                        this.loading = true;
                        let search_rate = this.searchSelectRate();
                        if (search_rate !== -1) {
                            API({
                                method: 'post',
                                url: 'service_client_rate_plans/store',
                                data: {
                                    service_rate_id: this.rates[search_rate].id,
                                    client_id: this.$route.params.client_id,
                                    period: this.selectPeriod,
                                    porcentage: this.porcentage,
                                    region_id: this.$route.params.region_id
                                },
                            }).then((result) => {
                                if (result.data.success === true) {
                                    this.$set(this.rates[search_rate], 'selected', false);
                                    this.rates[search_rate].service_client_rate_plans_id = result.data.service_client_rate_plans_id;
                                    this.rates[search_rate].markup = this.porcentage;
                                    this.rates_selected.push(this.rates[search_rate]);
                                    this.rates.splice(search_rate, 1);
                                    console.log(this.rates);
                                    this.loading = false;
                                }
                            }).catch(() => {
                                this.showError(
                                    this.$t('clientsmanageclienthotel.error.messages.name'),
                                    this.$t('clientsmanageclienthotel.error.messages.connection_error'),
                                );
                            });
                        } else {
                            if (this.rates.length > 0) {
                                this.loading = true;
                                let element = this.rates.shift();
                                API({
                                    method: 'post',
                                    url: 'service_client_rate_plans/store',
                                    data: {
                                        data: element,
                                        rate_plan_id: element.id,
                                        client_id: this.$route.params.client_id,
                                        period: this.selectPeriod,
                                        porcentage: this.porcentage,
                                        region_id: this.$route.params.region_id
                                    },
                                }).then((result) => {
                                    if (result.data.success === true) {
                                        element.service_client_rate_plans_id = result.data.service_client_rate_plans_id;
                                        this.rates_selected.push(element);
                                        this.loading = false;
                                    }
                                }).catch((e) => {
                                    this.showError(
                                        this.$t('clientsmanageclienthotel.error.messages.name'),
                                        this.$t('clientsmanageclienthotel.error.messages.connection_error') + e,
                                    );
                                });
                            }
                        }

                    }
                } else {
                    console.log('Bloqueado accion');
                }
            },
            moveAllRates: function() {
                if (this.loading === false) {
                    if (this.validatePeriod() !== false) {
                        this.searchMarkup();
                        this.loading = true;
                        if (this.rates.length > 0) {
                            for (let i = 0; i < this.rates.length; i++) {
                                this.$set(this.rates[i], 'selected', false);
                                this.rates_selected.push(this.rates[i]);
                            }
                            this.rates = [];

                            let search_service = this.searchSelectService();
                            if (search_service !== -1) {
                                API({
                                    method: 'post',
                                    url: 'service_client_rate_plans/store/all',
                                    data: {
                                        client_id: this.$route.params.client_id,
                                        service_id: this.services[search_service].service_id,
                                        period: this.selectPeriod,
                                        porcentage: this.porcentage,
                                        region_id: this.$route.params.region_id
                                    },
                                }).then((result) => {
                                    if (result.data.success === true) {
                                        //his.getRatesSelected()
                                        this.markup = '';
                                        this.loading = false;
                                    }
                                }).catch((e) => {
                                    this.showError(
                                        this.$t('clientsmanageclienthotel.error.messages.name'),
                                        this.$t('clientsmanageclienthotel.error.messages.connection_error') + e,
                                    );
                                });
                            }
                        }
                    } else {
                        console.log('Bloqueado accion');
                    }

                }
            },
            inverseOneRate: function() {
                if (this.loading === false) {
                    if (this.validatePeriod() !== false) {
                        this.searchMarkup();

                        this.loading = true;
                        let search_service = this.searchSelectService();
                        let search_rate = this.searchSelectRateRatesSelected();
                        if (search_rate !== -1) {
                            API({
                                method: 'post',
                                url: 'service_client_rate_plans/inverse',
                                data: {
                                    rate_plan_id: this.rates_selected[search_rate].service_rate_id,
                                    client_id: this.$route.params.client_id,
                                    period: this.selectPeriod,
                                    region_id: this.$route.params.region_id
                                },
                            }).then((result) => {
                                if (result.data.success === true) {
                                    let service = this.services[search_service];
                                    this.getClientRatePlans(service.service_id);
                                    this.getClientRatePlanSelected(service.service_id);
                                    this.loading = false;
                                }
                            }).catch(() => {
                                this.showError(
                                    this.$t('clientsmanageclienthotel.error.messages.name'),
                                    this.$t('clientsmanageclienthotel.error.messages.connection_error'),
                                );
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
                                    this.showError(
                                        this.$t('clientsmanageclienthotel.error.messages.name'),
                                        this.$t('clientsmanageclienthotel.error.messages.connection_error') + e,
                                    );
                                });
                            }
                        }
                    }
                } else {
                    console.log('Bloqueado accion');
                }
            },
            inverseAllRates: function() {
                if (this.loading === false) {
                    if (this.validatePeriod() !== false) {
                        this.searchMarkup();
                        this.loading = true;
                        if (this.rates_selected.length > 0) {
                            this.rates_selected = [];
                            let search_service = this.searchSelectService();
                            let service = this.services[search_service];
                            API({
                                method: 'post',
                                url: 'service_client_rate_plans/inverse/all',
                                data: {
                                    client_id: this.$route.params.client_id,
                                    service_id: service.service_id,
                                    period: this.selectPeriod,
                                    region_id: this.$route.params.region_id
                                },
                            }).then((result) => {
                                if (result.data.success === true) {
                                    this.getClientRatePlanSelected(service.service_id);
                                    this.loading = false;
                                }
                            }).catch((e) => {
                                this.showError(
                                    this.$t('clientsmanageclienthotel.error.messages.name'),
                                    this.$t('clientsmanageclienthotel.error.messages.connection_error') + e,
                                );
                            });
                        }
                    }
                } else {
                    console.log('Bloqueado accion');
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
                                    region_id: this.$route.params.region_id
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
        height: 300px;
        max-height: 300px;
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
