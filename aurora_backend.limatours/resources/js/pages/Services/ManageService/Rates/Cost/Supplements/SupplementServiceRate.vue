<template>
    <div class="container-fluid">
        <div class="vld-parent">
            <loading :active.sync="loading" :can-cancel="false" color="#BD0D12"></loading>
            <div class="row mb-5">
                <div class="col-4">
                    <label for="">Suplemento</label>
                    <select name="" id="" v-model="supplement_selected" class="form-control">
                        <option value="">Seleccione Suplemento</option>
                        <option :value="supplement.id" v-for="supplement in suplements">
                            {{ supplement.supplement.translations[0].value }}
                        </option>
                    </select>
                </div>
                <div class="col-4">
                    <label for="">Tipo</label>
                    <select name="type" id="type" v-model="type" class="form-control"
                            v-bind:class="[type === 'optional' ? 'optional' : 'required']">
                        <option value="required" class="required">Obligatorio</option>
                        <option value="optional" class="optional">Opcional</option>
                    </select>
                </div>
                <div class="col-2">
                    <label for="">Cargo Extra</label>
                    <c-switch
                        class="mx-1"
                        color="primary"
                        v-model="amount_extra"
                        variant="pill"
                    />
                </div>
                <div class="col-2" style="align-items: center;justify-content: center;display: flex;">
                    <button class="btn btn-success" @click="addSupplement">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <table-client :columns="table.columns" :data="suplements_table" :options="tableOptions" id="dataTable"
                          theme="bootstrap4">
                <div class="table-actions" slot="actions" slot-scope="props">
                    <menu-edit :id="props.row.id" :custom_id="props.row.supplement_id" :options="menuOptions"
                               @remove="remove(props.row.id)"/>

                </div>
                <div class="table-supplement" slot="supplement" slot-scope="props">
                    {{ props.row.service_supplement.supplement.translations[0].value }}
                </div>
                <div class="table-supplement" slot="rate" slot-scope="props">
                    {{ props.row.service_supplement.supplement.per_person>0 ? 'por persona' : 'por habitacion' }}
                </div>
                <div class="table-supplement m-2" slot="type" slot-scope="props">
                    <select :name="'select_type_'+props.row.id" :id="'select_type_'+props.row.id" class="form-control"
                            v-model="props.row.type"
                            v-bind:class="[props.row.type === 'optional' ? 'optional' : 'required']"
                            @change="changeTypeRate(props.row)">
                        <option value="required" class="required">Obligatorio</option>
                        <option value="optional" class="optional">Opcional</option>
                    </select>
                </div>
                <div class="table-supplement" slot="amount_extra" slot-scope="props">
                    <b-form-checkbox
                        :checked="checkboxCheckedExtra(props.row.amount_extra)"
                        :id="'checkbox_amount_extra'+props.row.id"
                        :name="'checkbox_amount_extra'+props.row.id"
                        @change="changeExtra(props.row.id,props.row.amount_extra)"
                        switch>
                        {{ props.row.amount_extra? 'SI':'NO' }}
                    </b-form-checkbox>
                </div>
            </table-client>
        </div>
    </div>
</template>

<script>
    import { API } from './../../../../../../api'
    import TableClient from './../../../../../../components/TableClient'
    import MenuEdit from './../../../../../../components/MenuEdit'
    import CSwitch from '@coreui/vue/src/components/Switch/Switch'
    import Loading from 'vue-loading-overlay'
    import 'vue-loading-overlay/dist/vue-loading.css'

    export default {
        components: {
            'table-client': TableClient,
            'menu-edit': MenuEdit,
            CSwitch,
            Loading
        },
        data: () => {
            return {
                loading: false,
                suplements: [],
                suplements_table: [],
                supplement_selected: '',
                type: 'optional',
                amount_extra: true,
                table: {
                    columns: ['id', 'supplement', 'rate', 'type', 'amount_extra', 'actions']
                }
            }
        },
        mounted () {
            this.getSupplementsRate(this.$i18n.locale)
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
                        rate: 'Tarifario',
                        type: this.$i18n.t('supplements.type'),
                        amount_extra: this.$i18n.t('suplements.amount_extra'),
                        actions: this.$i18n.t('global.table.actions')
                    },
                    sortable: [],
                    filterable: []
                }
            }
        },
        created () {
            this.$parent.$parent.$on('langChange', (payload) => {
                this.getSupplementsRate(payload.lang)
            })
            this.getSupplements(localStorage.getItem('lang'))
            this.getSupplementsRate(localStorage.getItem('lang'))
        },
        methods: {
            getSupplements: function (lang) {
                let rate_plan_id = this.$route.params.rate_id
                API.get('service/rate/supplements?lang=' + lang + '&rate_plan_id=' + rate_plan_id).then((result) => {
                    this.suplements = result.data
                }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('suplements.error.messages.name'),
                        text: this.$t('suplements.error.messages.connection_error')
                    })
                })
            },
            getSupplementsRate: function (lang) {
                this.loading = true
                let rate_id = this.$route.params.rate_id
                API.get('service/rate/supplements/' + rate_id + '?lang=' + lang).then((result) => {
                    this.suplements_table = result.data
                    this.loading = false
                }).catch(() => {
                    this.loading = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('suplements.error.messages.name'),
                        text: this.$t('suplements.error.messages.connection_error')
                    })
                })
            },
            addSupplement: function () {
                this.loading = true
                if (this.supplement_selected !== '') {
                    API.post('service/rate/supplements', {
                        rate_id: this.$route.params.rate_id,
                        supplement_id: this.supplement_selected,
                        type: this.type,
                        amount_extra: this.amount_extra

                    }).then((result) => {
                        this.loading = false
                        this.supplement_selected = ''
                        this.getSupplementsRate(localStorage.getItem('lang'))
                        this.getSupplements(localStorage.getItem('lang'))
                        this.$notify({
                            group: 'main',
                            type: 'success',
                            title: this.$t('suplements.error.messages.name'),
                            text: result.data.message
                        })
                    }).catch(() => {
                        this.loading = false
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('suplements.error.messages.name'),
                            text: this.$t('suplements.error.messages.connection_error')
                        })
                    })
                }
            },
            remove (id) {
                this.loading = true
                API({
                    method: 'DELETE',
                    url: 'service/rate/supplements/' + id
                })
                    .then((result) => {
                        this.loading = false
                        this.supplement = ''
                        this.getSupplementsRate(localStorage.getItem('lang'))
                        this.getSupplements(localStorage.getItem('lang'), this.$route.params.rate_id)
                        this.$notify({
                            group: 'main',
                            type: 'success',
                            title: this.$t('suplements.error.messages.name'),
                            text: result.data.message
                        })
                    }).catch(() => {
                    this.loading = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('suplements.error.messages.name'),
                        text: this.$t('suplements.error.messages.connection_error')
                    })
                })
            },
            checkboxCheckedExtra: function (service_status) {
                if (service_status) {
                    return 'true'
                } else {
                    return 'false'
                }
            },
            changeExtra: function (id, status) {
                this.loading = true
                API({
                    method: 'put',
                    url: 'service/rate/supplements/' + id + '/extra_charge',
                    data: { extra: status }
                })
                    .then((result) => {
                        this.loading = false
                        if (result.data.success === true) {
                            this.getSupplementsRate(localStorage.getItem('lang'))
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
                        text: this.$t('servicesmanageserviceincludes.error.messages.connection_error')
                    })
                })
            },
            changeTypeRate: function (value) {
                this.loading = true
                API({
                    method: 'put',
                    url: 'service/rate/supplements/' + value.id + '/type',
                    data: { type: value.type }
                })
                    .then((result) => {
                        this.loading = false
                        if (result.data.success === true) {
                            this.getSupplementsRate(localStorage.getItem('lang'))
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
                        text: this.$t('servicesmanageserviceincludes.error.messages.connection_error')
                    })
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
