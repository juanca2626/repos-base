<template>
    <div class="container-fluid">
        <div class="vld-parent">
            <loading :active.sync="loading" :can-cancel="false" color="#BD0D12"></loading>
            <form @submit.prevent="validateBeforeSubmit">
                <div class="row">
                    <div class="col-sm-6 mb-2">
                        <label>Servicio - {{ $t('suplements.suplement_name') }}</label>
                        <div class="col-sm-12 p-0">
                            <v-select :options="services"
                                      :value="form.object_id"
                                      label="name" :filterable="false" @search="onSearch"
                                      :placeholder="$t('servicesmanageservicecomponents.filter')"
                                      v-validate="'required'"
                                      v-model="serviceSelected" name="supplement" id="supplement">
                                <template slot="option" slot-scope="option">
                                    <div class="d-center">
                                        <span style="background-color: #FFF0A2;color: #5F2902;"> [{{ option.aurora_code }} - {{ option.equivalence_aurora }} - {{ option.service_type.translations[0].value }}]</span>
                                        {{ option.name }}
                                    </div>
                                </template>
                                <template slot="selected-option" slot-scope="option">
                                    <div class="selected d-center">
                                        <span style="background-color: #FFF0A2;color: #5F2902;"> [{{ option.aurora_code }} - {{ option.equivalence_aurora }} - {{ option.service_type.translations[0].value }}]</span>
                                        {{ option.name }}
                                    </div>
                                </template>
                            </v-select>
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;"
                                                   v-show="errors.has('supplement')"/>
                                <span v-show="errors.has('supplement')">{{ errors.first('supplement') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3 mb-2">
                        <label>Tipo</label>
                        <div class="col-sm-12 p-0">
                            <select name="type" id="type" v-model="form.type" class="form-control"
                                    v-bind:class="[form.type === 'optional' ? 'optional' : 'required']">
                                <option value="required" class="required">Obligatorio</option>
                                <option value="optional" class="optional">Opcional</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-3 mb-2" v-show="showDaysToCharge">
                        <label>Días a cobrar</label>
                        <div class="col-sm-12 p-0">
                            <div class="form-check form-check-inline" v-for="(day,index) in quantityDaysToCharge">
                                <input type="checkbox"
                                       class="form-check-input"
                                       :id="'check_day'+index"
                                       v-model="day.checked">
                                <label class="form-check-label" :for="'check_day'+index">Día {{day.day}}</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3 mb-2" v-show="form.type == 'optional'">
                        <label>Aplicar el cobro a todos de forma obligatoria?</label>
                        <div class="col-sm-12 p-0">
                            <c-switch :value="true" class="mx-1" color="success"
                                      v-model="form.charge_all_pax"
                                      variant="pill">
                            </c-switch>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-3 mb-5">
                        <img src="/images/loading.svg" v-if="loading" width="40px"/>
                        <div class="col-sm-12 p-0">
                            <button class="btn btn-success" @click="validateBeforeSubmit" v-if="!loading">
                                <i class="fas fa-plus"></i> {{ $t('global.buttons.save') }}
                            </button>
                        </div>
                    </div>
                </div>
            </form>
            <div class="row">
                <table-client :columns="table.columns" :data="suplements_table" :options="tableOptions" id="dataTable"
                              theme="bootstrap4">

                    <div class="table-actions" slot="actions" slot-scope="props">
                        <menu-edit :id="props.row.id" :custom_id="props.row.id" :options="menuOptions"
                                   @remove="remove(props.row.id)"/>

                    </div>
                    <div class="table-supplement" slot="supplement" slot-scope="props">
                        {{props.row.supplements.id}} - <span style="background-color: #FFF0A2;color: #5F2902;">[{{ props.row.supplements.aurora_code }}]</span>
                        - <router-link :to="'/services_new/edit/'+props.row.object_id">{{props.row.supplements.name}}</router-link>
                    </div>
                    <div class="table-supplement" slot="days_to_charge" slot-scope="props">
                        <div class="form-check form-check-inline" v-for="(day,index) in props.row.days_to_charge">
                            <input type="checkbox"
                                   class="form-check-input"
                                   :id="'check_day'+index"
                                   @change="changeStatusDayCharge(props.row)"
                                   v-model="day.charge">
                            <label class="form-check-label" :for="'check_day'+index">Día {{day.day}}</label>
                        </div>
                    </div>
                    <div class="table-supplement" slot="charge_all_pax" slot-scope="props">
                        <b-form-checkbox
                            :checked="(props.row.charge_all_pax) ? 'true' : 'false'"
                            :id="'checkbox_'+props.row.id"
                            :name="'checkbox_'+props.row.id"
                            @change="changeState(props.row.id,props.row.charge_all_pax)"
                            :disabled="props.row.type == 'required'"
                            switch>
                        </b-form-checkbox>
                    </div>
                    <div class="table-supplement m-2" slot="type" slot-scope="props">
                        <select :name="'select_type_'+props.row.id" :id="'select_type_'+props.row.id"
                                class="form-control"
                                v-model="props.row.type"
                                v-bind:class="[props.row.type === 'optional' ? 'optional' : 'required']"
                                @change="changeTypeRate(props.row)">
                            <option value="required" class="required">Obligatorio</option>
                            <option value="optional" class="optional">Opcional</option>
                        </select>
                    </div>

                </table-client>
            </div>
        </div>
    </div>
</template>

<script>
    import { API } from './../../../../../api'
    import TableClient from './.././../../../../components/TableClient'
    import MenuEdit from './../../../../../components/MenuEdit'
    import vSelect from 'vue-select'
    import 'vue-select/dist/vue-select.css'
    import Loading from 'vue-loading-overlay'
    import { Switch as cSwitch } from '@coreui/vue'

    export default {
        components: {
            'table-client': TableClient,
            'menu-edit': MenuEdit,
            vSelect,
            Loading,
            cSwitch
        },
        data: () => {
            return {
                loading: false,
                showDaysToCharge: false,
                quantityDaysToCharge: [],
                services: [],
                serviceSelected: [],
                suplements: [],
                suplements_table: [],
                service_configuration: {},
                supplement: '',
                form: {
                    type: 'required',
                    service_id: null,
                    object_id: null,
                    days_to_charge: [],
                    charge_all_pax: false,
                },
                chargeAllPaxs: true,
                table: {
                    columns: ['id', 'supplement', 'type', 'days_to_charge', 'charge_all_pax', 'actions']
                }
            }
        },
        mounted () {
            this.fetchData(this.$i18n.locale)
            this.service_configuration = JSON.parse(window.localStorage.getItem('service_configuration'))
            if (this.service_configuration.unit_duration_id === 2) {
                for (let i = 0; i < this.service_configuration.duration; i++) {
                    this.quantityDaysToCharge.push({
                        day: i + 1,
                        checked: true
                    })
                }
                this.showDaysToCharge = true
            }
        },
        computed: {
            menuOptions: function () {
                let options = []
                options.push({
                    type: 'delete',
                    text: '',
                    link: '',
                    icon: 'trash',
                    type_action: 'button',
                    callback_delete: 'remove'
                })
                return options
            },
            tableOptions: function () {
                return {
                    headings: {
                        id: 'ID',
                        supplement: this.$i18n.t('suplements.suplement_name'),
                        type: 'Tipo',
                        days_to_charge: 'Días a cobrar',
                        charge_all_pax: 'Aplicar el cobro a todos',
                        actions: this.$i18n.t('global.table.actions')
                    },
                    sortable: [],
                    filterable: []
                }
            }
        },
        created () {
            this.$parent.$parent.$on('langChange', (payload) => {
                this.fetchData(payload.lang)
            })
            this.getSupplements()
        },
        methods: {
            getSupplements: function () {
                API.get('/services/selectBox?query=&supplement=1')
                    .then((result) => {
                        this.services = result.data.data
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Suplementos',
                        text: this.$t('suplements.error.messages.connection_error')
                    })
                })
            },
            onSearch (search, loading) {
                loading(true)
                API.get('/services/selectBox?query=' + search + '&supplement=1')
                    .then((result) => {
                        loading(false)
                        this.services = result.data.data
                    }).catch(() => {
                    loading(false)
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Suplementos',
                        text: this.$t('suplements.error.messages.connection_error')
                    })
                })
            },
            fetchData: function (lang) {
                this.loading = true
                let service_id = this.$route.params.service_id
                API.get('service/' + service_id + '/supplements?lang=' + lang).then((result) => {
                    this.loading = false
                    if (result.data.success === true) {
                        this.suplements_table = result.data.data
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Fetch Error',
                            text: result.data.message
                        })
                    }
                }).catch(() => {
                    this.loading = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Suplementos',
                        text: this.$t('suplements.error.messages.connection_error')
                    })
                })
            },
            validateBeforeSubmit: function () {
                this.$validator.validateAll().then((result) => {
                    if (result) {
                        this.form.object_id = this.serviceSelected.id
                        this.form.service_id = this.$route.params.service_id
                        let days = []
                        this.quantityDaysToCharge.forEach(function (item) {
                            days.push({ day: item.day, charge: item.checked })
                        })
                        this.form.days_to_charge = days
                        this.addSupplement()
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Suplementos',
                            text: 'Debe seleccionar un suplemento'
                        })

                        this.loading = false
                    }
                })
            },
            addSupplement: function () {
                this.loading = true
                API.post('service/supplements', this.form
                ).then((result) => {
                    if (result.data.success) {
                        this.serviceSelected = []
                        this.fetchData(localStorage.getItem('lang'))
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Suplementos',
                            text: result.data.error
                        })
                    }
                    this.loading = false

                }).catch(() => {
                    this.loading = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Suplementos',
                        text: this.$t('suplements.error.messages.connection_error')
                    })
                })

            },
            remove (id) {
                this.loading = true
                API({
                    method: 'DELETE',
                    url: 'service/supplements/',
                    data: {
                        id: id,
                    }
                })
                    .then((result) => {
                        this.loading = false
                        if (result.data.success === true) {
                            this.supplement = ''
                            this.fetchData(localStorage.getItem('lang'))
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.suplements'),
                                text: this.$t('suplements.error.messages.suplement_delete')
                            })

                        }
                    }).catch(() => {
                    this.loading = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Suplementos',
                        text: this.$t('suplements.error.messages.connection_error')
                    })
                })
            },
            changeTypeRate: function (value) {
                this.loading = true
                API({
                    method: 'put',
                    url: 'service/supplements/' + value.id + '/type',
                    data: { type: value.type }
                })
                    .then((result) => {
                        this.loading = false
                        if (result.data.success === true) {
                            this.fetchData(localStorage.getItem('lang'))
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('service.supplements'),
                                text: this.$t('servicesmanageserviceincludes.error.messages.information_error')
                            })
                        }
                    }).catch(() => {
                    this.loading = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('service.supplements'),
                        text: this.$t('global.error.messages.information_error')
                    })
                })
            },
            changeState: function (supplement_id, charge_all_pax) {
                API({
                    method: 'put',
                    url: 'service/supplements/' + supplement_id + '/charge_all_pax',
                    data: { charge_all_pax: charge_all_pax }
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.fetchData(localStorage.getItem('lang'))
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('service.supplements'),
                                text: this.$t('global.error.messages.information_error')
                            })
                        }
                    })
            },
            changeStatusDayCharge: function(supplement){
                API({
                    method: 'put',
                    url: 'service/supplements/' + supplement.id + '/days_to_charge',
                    data: { days_to_charge: supplement.days_to_charge }
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.fetchData(localStorage.getItem('lang'))
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('service.supplements'),
                                text: this.$t('global.error.messages.information_error')
                            })
                        }
                    })
            }
        }
    }
</script>

<style scoped>
    .optional {
        background: #fcedd8de !important;
    }

    .required {
        background: #f86c6b6b !important;
    }
</style>
