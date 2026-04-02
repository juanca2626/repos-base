<template>
    <div class="vld-parent">
        <loading :active.sync="loading" :can-cancel="false" color="#BD0D12"></loading>
        <div class="row">
            <div class="col-xs-12 col-lg-12">
                <template v-if="flag==false">
                    <form @submit.prevent="validateBeforeSubmitConfig">
                        <div class="form-row">
                            <div class="col-sm-2">
                                <c-switch :value="true" class="mx-1" color="success"
                                          v-model="formConfig.allow_guide"
                                          variant="pill">
                                </c-switch>
                                <label>{{ $t('servicesmanageservicepolitics.it_allows') }} {{
                                    $t('servicesmanageservicepolitics.guide') }}</label>
                            </div>
                            <div class="col-sm-2">
                                <c-switch :value="true" class="mx-1" color="success"
                                          v-model="formConfig.allow_child"
                                          variant="pill">
                                </c-switch>
                                <label>{{ $t('servicesmanageservicepolitics.it_allows') }} {{
                                    $t('servicesmanageservicepolitics.children') }}</label>
                            </div>
<!--                            <div class="col-sm-2" v-if="!hasClient">-->
<!--                                <label>{{ $t('servicesmanageservicepolitics.limit_confirmation') }}</label>-->
<!--                            </div>-->
<!--                            <div class="col-sm-1" v-if="!hasClient">-->
<!--                                <select name="unit_duration_limit_confirmation" id="unit_duration_limit_confirmation"-->
<!--                                        v-model="formConfig.unit_duration_limit_confirmation"-->
<!--                                        class="form-control">-->
<!--                                    <option value="1">Horas</option>-->
<!--                                    <option value="2">Días</option>-->
<!--                                </select>-->
<!--                            </div>-->
<!--                            <div class="col-sm-2" v-if="!hasClient">-->
<!--                                <input class="form-control" id="limit_confirmation_hours" max="10000" min="0"-->
<!--                                       name="limit_confirmation_hours"-->
<!--                                       type="number" v-model.number="formConfig.limit_confirmation_hours">-->
<!--                            </div>-->

                            <div class="col-sm-2" style="text-align: right">
                                <img src="/images/loading.svg" v-if="loading" width="40px"/>
                                <button @click="validateBeforeSubmitConfig" class="btn btn-success" type="submit"
                                        v-if="!loading">
                                    <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                                    {{ $t('global.buttons.submit') }}
                                </button>
                            </div>
                        </div>
                        <div class="form-row">


<!--                            <div class="col-sm-3">-->
<!--                                <c-switch :value="true" class="mx-1" color="success"-->
<!--                                          v-model="formConfig.allow_infant"-->
<!--                                          variant="pill">-->
<!--                                </c-switch>-->
<!--                                <label>{{ $t('servicesmanageservicepolitics.it_allows') }} {{-->
<!--                                    $t('servicesmanageservicepolitics.infants') }}</label>-->
<!--                            </div>-->
                        </div>
                    </form>

                    <div class="col-sm-12" v-if="formConfig.allow_child">
                        <hr class="mb-3 mt-3">
                        <form @submit.prevent="validateBeforeSubmitChild">
                            <div class="form-row" style="margin-bottom: 10px;">

                                <div class="col-sm-2" v-if="formConfig.allow_child">
                                    <label>{{ $t('servicesmanageservicepolitics.age_minimum') }} (Infante)</label>
                                    <input class="form-control" id="infant_min_age" max="10000" min="0"
                                           name="infant_min_age"
                                           type="number" v-model.number="formConfig.infant_min_age">
                                </div>

                                <div class="col-sm-2" v-if="formConfig.allow_child">
                                    <label>{{ $t('servicesmanageservicepolitics.age_maximum') }} (Infante)</label>
                                    <input class="form-control" id="infant_max_age" max="10000" min="0"
                                           name="infant_max_age"
                                           type="number" v-model.number="formConfig.infant_max_age">
                                </div>

                                <div class="col-sm-2">
                                    <label>{{ $t('servicesmanageservicepolitics.age_minimum') }} ({{
                                        $t('servicesmanageservicepolitics.children') }})</label>
                                    <input class="form-control" id="child_min_age" max="10000" min="0"
                                           name="child_min_age"
                                           type="number" v-model.number="formConfig.child_min_age"
                                           v-validate="'required'">

                                    <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                        <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                           style="margin-left: 5px;"
                                                           v-show="errors.has('child_min_age')"/>
                                        <span
                                            v-show="errors.has('child_min_age')">{{ errors.first('child_min_age') }}</span>
                                    </div>
                                </div>

                                <div class="col-sm-2">
                                    <label>{{ $t('servicesmanageservicepolitics.age_maximum') }} ({{
                                        $t('servicesmanageservicepolitics.children') }})</label>
                                    <input class="form-control" id="child_max_age" max="10000" min="1"
                                           name="child_max_age"
                                           type="number" v-model.number="formConfig.child_max_age"
                                           v-validate="'required'">

                                    <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                        <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                           style="margin-left: 5px;"
                                                           v-show="errors.has('child_max_age')"/>
                                        <span
                                            v-show="errors.has('child_max_age')">{{ errors.first('child_max_age') }}</span>
                                    </div>
                                </div>



<!--                                <div class="col-sm-2" style="margin-top: 28px;">-->
<!--                                    <img src="/images/loading.svg" v-if="loading" width="40px"/>-->
<!--                                    <button @click="validateBeforeSubmitChild" class="btn btn-success" type="submit"-->
<!--                                            v-if="!loading">-->
<!--                                        <font-awesome-icon :icon="['fas', 'dot-circle']"/>-->
<!--                                        {{ $t('global.buttons.submit') }}-->
<!--                                    </button>-->
<!--                                </div>-->
                            </div>
                        </form>
                    </div>
<!--                    <table-client :columns="table.columns" :data="children" :loading="loading"-->
<!--                                  :options="tableOptions" id="dataTable"-->
<!--                                  theme="bootstrap4" v-if="formConfig.allow_child">-->
<!--                        <div class="table-state" slot="status" slot-scope="props" style="font-size: 0.9em">-->
<!--                            <b-form-checkbox-->
<!--                                :checked="checkboxChecked(props.row.status)"-->
<!--                                :id="'checkbox_'+props.row.id"-->
<!--                                :name="'checkbox_'+props.row.id"-->
<!--                                @change="changeStateChild(props.row.id,props.row.status)"-->
<!--                                switch>-->
<!--                            </b-form-checkbox>-->
<!--                        </div>-->
<!--                        <div class="table-actions" slot="actions" slot-scope="props" style="margin-top: 10px">-->
<!--                            <button @click="remove(props.row,props.row.id)" class="btn btn-danger" type="button"-->
<!--                                    v-if="$can('delete', 'packagechildren')">-->
<!--                                <font-awesome-icon :icon="['fas', 'trash']"/>-->
<!--                            </button>-->
<!--                        </div>-->
<!--                        <div class="table-loading text-center" slot="loading">-->
<!--                            <img alt="loading" height="51px" src="/images/loading.svg"/>-->
<!--                        </div>-->
<!--                    </table-client>-->
                </template>
                <template v-else>
                    <user :form="draft" @changeStatus="close" @close="flag"/>
                </template>
            </div>
        </div>
    </div>
</template>

<script>
    import { API } from './../../../../api'
    import TableClient from './.././../../../components/TableClient'
    import MenuEdit from './../../../../components/MenuEdit'
    import BDropDown from 'bootstrap-vue/es/components/dropdown/dropdown'
    import BDropDownItemButton from 'bootstrap-vue/es/components/dropdown/dropdown-item-button'
    import { Switch as cSwitch } from '@coreui/vue'
    import Loading from 'vue-loading-overlay'
    import 'vue-loading-overlay/dist/vue-loading.css'

    export default {
        components: {
            'table-client': TableClient,
            'menu-edit': MenuEdit,
            'b-dropdown': BDropDown,
            'b-dropdown-item-button': BDropDownItemButton,
            cSwitch,
            Loading
        },
        data: () => {
            return {
                loading: false,
                flag: false,
                children: [],
                id: null,
                currentIndex: null,
                showEdit: false,
                formTax: {
                    igv: 0,
                    use_igv: 0,
                    service_id: ''
                },
                formConfig: {
                    limit_confirmation_hours: 0,
                    unit_duration_limit_confirmation: 0,
                    allow_guide: '',
                    allow_child: '',
                    allow_infant: '',
                    child_min_age: '',
                    child_max_age: '',
                    infant_min_age: '',
                    infant_max_age: '',
                    service_id: ''
                },
                table: {
                    columns: ['id', 'min_age', 'max_age', 'status', 'actions']
                },
                hasClient : false

            }
        },
        mounted () {
            this.fetchData()
        },
        computed: {
            tableOptions: function () {
                return {
                    headings: {
                        id: 'ID',
                        min_age: this.$i18n.t('servicesmanageservicepolitics.age_minimum'),
                        max_age: this.$i18n.t('servicesmanageservicepolitics.age_maximum'),
                        status: this.$i18n.t('global.status'),
                        actions: this.$i18n.t('global.table.actions')
                    },
                    sortable: ['id'],
                    filterable: false
                }
            }
        },
        created () {
            this.hasClient = !!(window.localStorage.getItem('client_id') && window.localStorage.getItem('client_id') !== '')
            this.$parent.$parent.$on('langChange', (payload) => {
                this.fetchData(payload.lang)
            })
        },
        methods: {
            validateBeforeSubmitConfig: function () {
                let child_min_age = this.formConfig.child_min_age
                let child_max_age = this.formConfig.child_max_age
                let error = 0

                this.children.forEach(function (value, key) {
                    if ((child_min_age >= value.min_age && child_min_age <= value.max_age) ||
                        (child_max_age >= value.min_age && child_max_age <= value.max_age)) {
                        error++
                    }
                })

                if (error > 0) {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('servicesmanageservicepolitics.configuration'),
                        text: this.$t('servicesmanageservicepolitics.error.messages.range_children_invalid')
                    })
                    return
                }

                if (this.formConfig.allow_child &&
                    (this.formConfig.infant_min_age < 0 || this.formConfig.infant_max_age <= 0)) {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('servicesmanageservicepolitics.configuration'),
                        text: this.$t('servicesmanageservicepolitics.error.messages.information_complete')
                    })
                    return false
                }

                if (this.formConfig.allow_child &&
                    (this.formConfig.child_min_age <= this.formConfig.infant_max_age)) {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'La edad minima del niño no puede ser igual o menor a la edad maxima del infante',
                        text: this.$t('servicesmanageservicepolitics.error.messages.information_complete')
                    })
                    return false
                }

                if (this.formConfig.allow_child &&
                    (this.formConfig.child_min_age > this.formConfig.child_max_age)) {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'La edad minima del niño no puede mayor a la edad maxima',
                        text: this.$t('servicesmanageservicepolitics.error.messages.information_complete')
                    })
                    return false
                }

                if (this.formConfig.allow_child &&
                    (this.formConfig.infant_min_age > this.formConfig.infant_max_age)) {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'La edad minima del infante no puede mayor a la edad maxima',
                        text: this.$t('servicesmanageservicepolitics.error.messages.information_complete')
                    })
                    return false
                }

                this.$validator.validateAll().then((result) => {
                    if (result) {
                        this.formConfig.service_id = this.$route.params.service_id

                        this.submit(this.formConfig)

                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('servicesmanageservicepolitics.configuration'),
                            text: this.$t('servicesmanageservicepolitics.error.messages.information_complete')
                        })

                        this.loading = false
                    }
                })
            },
            validateBeforeSubmitChild: function () {



                this.$validator.validateAll().then((result) => {
                    if (result) {
                        this.formConfig.service_id = this.$route.params.service_id
                        this.submitNewChild()

                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('servicesmanageservicepolitics.configuration'),
                            text: this.$t('global.error.information_complete')
                        })

                        this.loading = false
                    }
                })
            },
            submit: function (data) {
                this.loading = true
                API({
                    method: 'PUT',
                    url: 'service/' + data.service_id + '/configurations',
                    data: data
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.$notify({
                                group: 'main',
                                type: 'success',
                                title: this.$t('servicesmanageservicepolitics.configuration'),
                                text: this.$t('global.success.save')
                            })
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('servicesmanageservicepolitics.configuration'),
                                text: this.$t('global.error.save')
                            })
                        }
                        this.loading = false
                    })
            },
            submitNewChild: function () {
                this.loading = true
                API({
                    method: 'POST',
                    url: 'service/' + this.formConfig.service_id + '/children',
                    data: this.formConfig
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.$notify({
                                group: 'main',
                                type: 'success',
                                title: this.$t('servicesmanageservicepolitics.configuration'),
                                text: this.$t('global.success.save')
                            })
                            this.fetchData()
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('servicesmanageservicepolitics.configuration'),
                                text: this.$t('global.error.save')
                            })
                        }
                        this.loading = false
                    })
            },
            changeStateChild: function (child_id, status) {
                API({
                    method: 'put',
                    url: 'service/' + this.$route.params.service_id + '/children/' + child_id + '/status',
                    data: { status: status }
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.fetchData(localStorage.getItem('lang'))
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('servicesmanageservicepolitics.configuration'),
                                text: this.$t('global.error.messages.connection_error')
                            })
                        }
                    })
            },
            checkboxChecked: function (room_state) {
                if (room_state) {
                    return 'true'
                } else {
                    return 'false'
                }
            },
            close (valor) {
                this.flag = valor
                this.fetchData(this.$i18n.locale)
            },
            remove: function (data, index) {
                API({
                    method: 'delete',
                    url: 'service/' + this.$route.params.service_id + '/children/' + index
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.fetchData(localStorage.getItem('lang'))
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('servicesmanageservicepolitics.configuration'),
                                text: this.$t('global.error.delete')
                            })
                        }
                    })
            },
            statusB: function () {
                this.flag = false
            },
            fetchData: function () {
                this.loading = true
                // Services
                API.get('/services/' + this.$route.params.service_id)
                    .then((result) => {
                        this.loading = false
                        this.formConfig.limit_confirmation_hours = result.data.data[0].limit_confirm_hours
                        this.formConfig.unit_duration_limit_confirmation = result.data.data[0].unit_duration_limit_confirmation
                        this.formConfig.infant_max_age = result.data.data[0].infant_max_age
                        this.formConfig.infant_min_age = result.data.data[0].infant_min_age
                        this.formConfig.allow_guide =
                            (result.data.data[0].allow_guide !== null && parseInt(result.data.data[0].allow_guide) === 1)
                        this.formConfig.allow_child =
                            (result.data.data[0].allow_child != null && parseInt(result.data.data[0].allow_child) === 1)
                        this.formConfig.allow_infant =
                            (result.data.data[0].allow_infant != null && parseInt(result.data.data[0].allow_infant) === 1)
                    })
                    .catch(() => {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.error.messages.connection_error'),
                            text: this.$t('servicesmanageservicepolitics.configuration'),
                        })
                    })

                // Children
                API.get('/service/' + this.$route.params.service_id + '/children')
                    .then((result) => {
                        this.loading = false
                        if(result.data.data.length > 0){
                            this.formConfig.child_min_age = result.data.data[0].min_age
                            this.formConfig.child_max_age = result.data.data[0].max_age
                        }else{
                            this.formConfig.child_min_age = 0
                            this.formConfig.child_max_age = 0
                        }
                        // this.children = result.data.data
                    })
                    .catch(() => {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Fetch Error',
                            text: 'Cannot load data'
                        })
                    })
            }
        }
    }
</script>

<style lang="stylus">
</style>


