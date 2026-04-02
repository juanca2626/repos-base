<template>
    <div class="vld-parent">
        <loading :active.sync="loading" :can-cancel="false" color="#BD0D12"></loading>
        <div class="container-fluid">

            <div class="b-form-group form-group">
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
                <div class="row">
                    <div class="col-md-12 mt-2 mb-2">
                        <hr>
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
                                  autocomplete="true"
                                  :disabled="loading"
                        ></v-select>
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
                        <label class="col-form-label">Experiencias</label>
                        <v-select :options="experiences"
                                  :value="experience_id"
                                  @input="experienceChange"
                                  autocomplete="true"
                                  data-vv-as="experiences"
                                  data-vv-name="experiences"
                                  name="type_service"
                                  v-model="experienceSelected"
                                  placeholder="Experiencias">
                        </v-select>
                    </div>
                    <div class="col-sm-3">
                        <div>
                            <label for="range-1">Valoración</label>
                            <div class="text-center">
                                <b-form-input id="range-1" v-model="rated" type="range" min="0" max="10"
                                              step="0.5"></b-form-input>
                                <span class="font-weight-bold">{{ rated }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <img src="/images/loading.svg" v-if="loading" width="40px"
                             style="float: right; margin-top: 35px;"/>
                        <button @click="saveFilter()" class="btn btn-success" type="submit" v-if="!loading"
                                style="float: right; margin-top: 35px;">
                            <font-awesome-icon :icon="['fas', 'save']"/>
                            Guardar
                        </button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 mt-2 mb-2">
                        <hr>
                    </div>
                </div>
            </div>
            <div class="b-form-group form-group">
                <div class="form-row">
                    <div class="col-sm-4">
                        <label class="col-form-label">Buscar por: Nombre o codigo</label>
                        <input class="form-control" id="service_name" name="service_name"
                               :placeholder="this.$t('services.search.messages.service_aurora_name_search')"
                               type="text" v-model="service_name">
                    </div>
                    <div class="col-sm-2">
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
                <div class="table-service" slot="service" slot-scope="props" style="font-size: 0.9em">
                    {{ props.row.aurora_code }} - {{ props.row.name }}
                </div>
                <div class="table-rated" slot="rated" slot-scope="props" style="font-size: 0.9em">
                    <div class="text-center">
                        <b-form-input id="range-1" v-model="props.row.rated" type="range" min="0" max="10" step="0.5"
                                      @change="changeRated(props.row)"></b-form-input>
                        <span class="font-weight-bold">{{ props.row.rated }}</span>
                    </div>
                </div>
            </table-server>
        </div>
    </div>
</template>
<script>
    import {API, APISERVICE} from '../../../../../api';
    import TableServer from '../../../../../components/TableServer';
    import Loading from 'vue-loading-overlay';
    import 'vue-loading-overlay/dist/vue-loading.css';
    import vSelect from 'vue-select';
    import 'vue-select/dist/vue-select.css';

    export default {
        components: {
            Loading,
            vSelect,
            'table-server': TableServer,
        },
        data() {
            return {
                selectPeriod: '',
                period: '',
                urlOffer: '',
                date_from: '',
                date_to: '',
                offer: '',
                rated: 0,
                categories: [],
                typeServices: [],
                experiences: [],
                type_service_id: '',
                experience_id: '',
                category_service_id: '',
                typeServiceSelected: [],
                serviceCategorySelected: [],
                experienceSelected: [],
                ubigeos: [],
                ubigeoSelected: [],
                periods: [],
                loading: false,
                ubigeo_id: '',
                service_name: '',
                urlServices: '',
                table: {
                    columns: ['service', 'rated'],
                },

            };
        },

        computed: {
            tableOptions: function() {
                return {
                    headings: {
                        id: 'ID',
                        service: 'Servicio',
                        rated: 'Valoración',
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
                        'query': this.service_name,
                        'period': this.selectPeriod,
                        'limit': 10
                    },
                    requestFunction: function(data) {
                        console.log(data);
                        let url = 'service/search/client/rated?token=' + window.localStorage.getItem('access_token') +
                            '&lang=' + localStorage.getItem('lang') + '&region_id=' + this.$route.params.region_id;
                        return API.get(url, {
                            params: data,
                        }).catch(function(e) {
                            this.dispatch('error', e);
                        }.bind(this));

                    },
                };
            },
        },
        created () {
            this.getPeriods();
            this.loadubigeo();
        },
        mounted: function() {
            API.get('/service_categories/selectBox?lang=' + localStorage.getItem('lang') + '&region_id=' + this.$route.params.region_id)
            .then((result) => {
                let categorias = result.data.data;
                categorias.forEach((category) => {
                    this.typeServices.push({
                        label: category.translations[0].value,
                        code: category.translations[0].object_id,
                    });
                });

            }).catch(() => {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('global.modules.services'),
                    text: this.$t('global.error.messages.connection_error'),
                });
            });
            API.get('/experiences/selectBox?lang=' + localStorage.getItem('lang') + '&region_id=' + this.$route.params.region_id)
            .then((result) => {
                let experiences = result.data.data;
                experiences.forEach((experience) => {
                    this.experiences.push({
                        label: experience.translations[0].value,
                        code: experience.translations[0].object_id,
                    });
                });

            }).catch(() => {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('global.modules.services'),
                    text: this.$t('global.error.messages.connection_error'),
                });
            });
        },
        methods: {
            getPeriods: function() {
                this.loading = true
                API.get('client_services/selectPeriod?lang='
                    + localStorage.getItem('lang') +
                    '&client_id=' + this.$route.params.client_id
                    + '&region_id=' + this.$route.params.region_id
                )
                .then((result) => {

                    let periods = result.data.data;
                    periods.forEach((period) => {
                        this.periods.push({
                            text: period.text + ' - ' + period.porcen_service + ' %',
                            value: period.text,
                        });
                    });

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
            loadubigeo() {
                this.loading = true
                APISERVICE.get('destination_services/?client_id=' + this.$route.params.client_id + '&region_id='+ this.$route.params.region_id)
                .then((result) => {
                    let ubigeohotel = result.data.data.destinations;
                    this.ubigeos = [];
                    ubigeohotel.forEach((ubigeofor) => {
                        this.ubigeos.push({label: ubigeofor.label, code: ubigeofor.code});
                    });
                    this.loading = false
                }).catch((e) => {
                    this.loading = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('clients.title'),
                        text: this.$t('global.error.messages.connection_error'),
                    });
                });
            },
            ubigeoChange: function(value) {
                this.ubigeo = value;
                if (this.ubigeo != null) {
                    if (this.ubigeo_id != this.ubigeo.code) {
                    }
                    this.ubigeo_id = this.ubigeo.code;
                } else {
                    this.ubigeo_id = '';
                }
            },
            typeServiceChange: function(value) {
                this.category = value;
                if (this.category != null) {
                    this.type_service_id = this.category.code;
                } else {
                    this.type_service_id = '';
                }
            },
            experienceChange: function(value) {
                this.experience = value;
                if (this.experience != null) {
                    this.experience_id = this.experience.code;
                } else {
                    this.experience_id = '';
                }
            },
            search() {
                this.loading = true;
                this.$refs.table.$refs.tableserver.getData();
                this.loading = false;
            },
            searchPeriod() {
                this.$refs.table.$refs.tableserver.getData();
            },
            saveFilter() {
                if(this.rated === 0){
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('clients.title'),
                        text: 'La valoración debe ser mayor a 0',
                    });
                }else{
                    this.loading = true
                    API({
                        method: 'post',
                        url: 'client/service/store/filter',
                        data: {
                            'client_id':  this.$route.params.client_id,
                            'destination':this.ubigeo_id,
                            'categories':this.type_service_id,
                            'experiences': this.experience_id,
                            'period': this.selectPeriod,
                            'rated': this.rated,
                            'region_id' : this.$route.params.region_id
                        },
                    }).then((result) => {
                        this.loading = false
                        if (result.data.success === false) {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('clients.title'),
                                text: this.$t('global.error.save')
                            })
                        }else{
                            this.$notify({
                                group: 'main',
                                type: 'success',
                                title: this.$t('clients.title'),
                                text: this.$t('global.success.save')
                            })
                            this.search();
                        }
                    }).catch(() => {
                        this.loading = false
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('clients.title'),
                            text: this.$t('error.messages.connection_error'),
                        });
                    });
                }
            },
            changeRated(row) {
                this.loading = true
                API({
                    method: 'post',
                    url: 'client/service/rated',
                    data: {
                        'client_id': row.client_id,
                        'service_id': row.service_id,
                        'rated': row.rated,
                        'period': this.selectPeriod,
                        'region_id' : this.$route.params.region_id
                    },
                }).then((result) => {
                    this.loading = false
                    this.search();
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
