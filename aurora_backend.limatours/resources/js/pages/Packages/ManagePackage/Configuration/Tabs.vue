<template>
    <div class="row">
        <div class="col-xs-12 col-lg-12">
            <b-tabs>
                <b-tab :title="$t('packagesmanagepackageconfiguration.taxes')" @click="statusB" active>
                    <template v-if="flag==false">

                        <div class="form-row col-md-12 taxRow" v-for="tax in taxes">
                            <div class="col-sm-3">
                                <label>{{ tax.name }}</label>
                            </div>
                            <div class="col-sm-3">
                                <label>{{ tax.value }}(%)</label>
                            </div>

                            <div class="col-sm-3" style="text-align: right;">
                                <label>{{ $t('packagesmanagepackageconfiguration.apply') }}</label>
                            </div>
                            <div class="col-sm-3">
                                <c-switch :value="true" class="mx-1" color="success"
                                          v-model="tax.used" @change="updateTax(tax)"
                                          variant="pill">
                                </c-switch>
                            </div>

                        </div>

                    </template>
                </b-tab>
                <b-tab :title="$t('packagesmanagepackageconfiguration.configuration')" @click="statusB">

                    <template v-if="flag==false">

                        <form @submit.prevent="validateBeforeSubmitConfig" class="mb-3">
                            <div class="form-row">
                                <div class="col-sm-3">
                                    <c-switch :value="true" class="mx-1" color="success"
                                              v-model="formConfig.allow_guide"
                                              variant="pill">
                                    </c-switch>
                                    <label>{{ $t('packagesmanagepackageconfiguration.it_allows') }} {{
                                        $t('packagesmanagepackageconfiguration.guide') }}</label>
                                </div>

                                <div class="col-sm-3">
                                    <label>{{ $t('packagesmanagepackageconfiguration.limit_confirmation') }}</label>
                                </div>
                                <div class="col-sm-2">
                                    <input class="form-control" id="limit_confirmation_hours" max="10000" min="0"
                                           name="limit_confirmation_hours"
                                           data-vv-scope="form-config"
                                           type="number" v-model.number="formConfig.limit_confirmation_hours">
                                </div>

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
                                <div class="col-sm-3">
                                    <c-switch :value="true" class="mx-1" color="success"
                                              v-model="formConfig.allow_child"
                                              variant="pill">
                                    </c-switch>
                                    <label>{{ $t('packagesmanagepackageconfiguration.it_allows') }} {{
                                        $t('packagesmanagepackageconfiguration.children') }}</label>
                                </div>

                                <div class="col-sm-3">
                                    <c-switch :value="true" class="mx-1" color="success"
                                              v-model="formConfig.allow_infant"
                                              variant="pill">
                                    </c-switch>
                                    <label>{{ $t('packagesmanagepackageconfiguration.it_allows') }} {{
                                        $t('packagesmanagepackageconfiguration.infants') }}</label>
                                </div>


                                <div class="col-sm-2" v-if="formConfig.allow_infant">
                                    <label>{{ $t('packagesmanagepackageconfiguration.age_minimum') }} ({{
                                        $t('packagesmanagepackageconfiguration.infants') }})</label>
                                    <input class="form-control" id="infant_min_age" max="10000" min="0"
                                           name="infant_min_age"
                                           data-vv-scope="form-config"
                                           type="number" v-model.number="formConfig.infant_min_age">
                                </div>
                                <div class="col-sm-2" v-if="formConfig.allow_infant">
                                    <label>{{ $t('packagesmanagepackageconfiguration.age_maximum') }} ({{
                                        $t('packagesmanagepackageconfiguration.infants') }})</label>
                                    <input class="form-control" id="infant_max_age" max="10" min="0"
                                           name="infant_max_age"
                                           data-vv-scope="form-config"
                                           type="number" v-model.number="formConfig.infant_max_age">
                                </div>
                                <div class="col-sm-2" v-if="formConfig.allow_infant">
                                    <label>%</label>
                                    <input class="form-control" id="infant_discount_rate" max="100" min="1"
                                           name="infant_discount_rate"
                                           data-vv-scope="form-config"
                                           type="number" v-model.number="formConfig.infant_discount_rate">
                                </div>
                            </div>
                        </form>

                        <div class="col-sm-12" v-if="formConfig.allow_child">

                            <div class="col-md-12">
                                <hr>
                            </div>

                            <form @submit.prevent="validateBeforeSubmitChild" class="mt-3">
                                <div class="form-row" style="margin-bottom: 10px;">
                                    <div class="col-sm-3">
                                        <label>{{ $t('packagesmanagepackageconfiguration.age_minimum') }} ({{
                                            $t('packagesmanagepackageconfiguration.children') }})</label>
                                        <input class="form-control" id="child_min_age" max="10000" min="0"
                                               name="child_min_age"
                                               type="number" v-model.number="formNewChild.child_min_age"
                                               v-validate="'required'">

                                        <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                            <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                               style="margin-left: 5px;"
                                                               v-show="errors.has('child_min_age')"/>
                                            <span v-show="errors.has('child_min_age')">{{ errors.first('child_min_age') }}</span>
                                        </div>
                                    </div>

                                    <div class="col-sm-3">
                                        <label>{{ $t('packagesmanagepackageconfiguration.age_maximum') }} ({{
                                            $t('packagesmanagepackageconfiguration.children') }})</label>
                                        <input class="form-control" id="child_max_age" max="10000" min="1"
                                               name="child_max_age"
                                               type="number" v-model.number="formNewChild.child_max_age"
                                               v-validate="'required'">

                                        <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                            <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                               style="margin-left: 5px;"
                                                               v-show="errors.has('child_max_age')"/>
                                            <span v-show="errors.has('child_max_age')">{{ errors.first('child_max_age') }}</span>
                                        </div>
                                    </div>

                                    <div class="col-sm-3">
                                        <label>%</label>
                                        <input class="form-control" id="child_percentage" max="10000" min="1"
                                               name="child_percentage"
                                               type="number" v-model.number="formNewChild.percentage"
                                               v-validate="'required'">

                                        <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                            <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                               style="margin-left: 5px;"
                                                               v-show="errors.has('child_percentage')"/>
                                            <span v-show="errors.has('child_percentage')">{{ errors.first('child_percentage') }}</span>
                                        </div>
                                    </div>

                                    <div class="col-sm-3">
                                        <label>Con Cama / Sin Cama</label>
                                        <br>
                                        <c-switch :value="true" class="mx-1" color="success"
                                                  v-model="formNewChild.has_bed"
                                                  variant="pill">
                                        </c-switch>
                                    </div>

                                    <div class="col-sm-4" style="margin-top: 28px;">
                                        <img src="/images/loading.svg" v-if="loading" width="40px"/>
                                        <button @click="validateBeforeSubmitChild" class="btn btn-success" type="submit"
                                                v-if="!loading">
                                            <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                                            {{ $t('global.buttons.submit') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <table-client :columns="table.columns" :data="children" :loading="loading"
                                      :options="tableOptions" id="dataTable"
                                      theme="bootstrap4" v-if="formConfig.allow_child">
                            <div class="table-state" slot="status" slot-scope="props" style="font-size: 0.9em">
                                <b-form-checkbox
                                    :checked="checkboxChecked(props.row.status)"
                                    :id="'checkbox_'+props.row.id"
                                    :name="'checkbox_'+props.row.id"
                                    @change="changeStateChild(props.row.id,props.row.status)"
                                    switch>
                                </b-form-checkbox>
                            </div>
                            <div class="table-has_bed" slot="has_bed" slot-scope="props" style="font-size: 0.9em">
                                <h4>
                                    <span class="badge badge-primary" v-if="props.row.has_bed"> Con cama</span>
                                    <span class="badge badge-secondary" v-if="!props.row.has_bed"> Sin cama</span>
                                </h4>
                            </div>
                            <div class="table-has_bed" slot="percentage" slot-scope="props" style="font-size: 0.9em">
                                <input class="form-control" id="_percentage" max="100" min="0"
                                       name="_percentage" @keyup.enter="changePercentage(props.row.id,props.row.percentage)"
                                       type="number" v-model.number="props.row.percentage">
                            </div>
                            <div class="table-actions" slot="actions" slot-scope="props" style="margin-top: 10px">
                                <button @click="remove(props.row,props.row.id)" class="btn btn-danger" type="button"
                                        v-if="$can('delete', 'packagechildren')">
                                    <font-awesome-icon :icon="['fas', 'trash']"/>
                                </button>
                            </div>
                            <div class="table-loading text-center" slot="loading">
                                <img alt="loading" height="51px" src="/images/loading.svg"/>
                            </div>
                        </table-client>
                    </template>
                    <template v-else>
                        <user :form="draft" @changeStatus="close" @close="flag"/>
                    </template>
                </b-tab>
            </b-tabs>
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

    export default {
        components: {
            'table-client': TableClient,
            'menu-edit': MenuEdit,
            'b-dropdown': BDropDown,
            'b-dropdown-item-button': BDropDownItemButton,
            cSwitch
        },
        data: () => {
            return {
                loading: false,
                flag: false,
                children: [],
                taxes: [],
                id: null,
                currentIndex: null,
                showEdit: false,
                formConfig: {
                    limit_confirmation_hours: 0,
                    allow_guide: '',
                    allow_child: '',
                    allow_infant: '',
                    infant_min_age: '',
                    infant_max_age: '',
                    infant_discount_rate: '',
                    package_id: ''
                },
                formNewChild: {
                    child_min_age: 0,
                    child_max_age: 0,
                    percentage: 0,
                    package_id: '',
                    has_bed: true
                },
                table: {
                    columns: ['id', 'min_age', 'max_age', 'percentage', 'has_bed', 'status', 'actions']
                }
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
                        min_age: this.$i18n.t('packagesmanagepackageconfiguration.age_minimum'),
                        max_age: this.$i18n.t('packagesmanagepackageconfiguration.age_maximum'),
                        percentage: '%',
                        has_bed: 'Con cama / sin cama',
                        status: this.$i18n.t('global.status'),
                        actions: this.$i18n.t('global.table.actions')
                    },
                    sortable: ['id'],
                    filterable: false
                }
            }
        },
        created () {
            this.$parent.$parent.$on('langChange', (payload) => {
                this.fetchData(payload.lang)
            })
        },
        methods: {
            validateBeforeSubmitConfig: function () {

                if (this.formConfig.allow_infant &&
                    (this.formConfig.infant_min_age < 0 || this.formConfig.infant_max_age < 0)) {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('packagesmanagepackageconfiguration.configuration'),
                        text: this.$t('packagesmanagepackageconfiguration.error.messages.information_complete')
                    })
                    return false
                }

                this.$validator.validateAll('form-config').then((result) => {
                    if (result) {
                        this.formConfig.package_id = this.$route.params.package_id

                        this.submit(this.formConfig)

                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.packages'),
                            text: this.$t('packagesmanagepackageconfiguration.error.messages.information_complete')
                        })

                        this.loading = false
                    }
                })
            },
            validateBeforeSubmitChild: function () {

                let child_min_age = this.formNewChild.child_min_age
                let child_max_age = this.formNewChild.child_max_age
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
                        title: this.$t('global.modules.packages'),
                        text: this.$t('packagesmanagepackageconfiguration.error.messages.range_children_invalid')
                    })
                    return
                }

                this.$validator.validateAll().then((result) => {
                    if (result) {
                        this.formNewChild.package_id = this.$route.params.package_id
                        this.submitNewChild()

                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.packages'),
                            text: this.$t('packagesmanagepackageconfiguration.error.messages.information_complete')
                        })

                        this.loading = false
                    }
                })
            },
            submit: function (data) {
                this.loading = true
                API({
                    method: 'PUT',
                    url: 'packages/' + data.package_id + '/configurations',
                    data: data
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.$notify({
                                group: 'main',
                                type: 'success',
                                title: this.$t('global.modules.taxes'),
                                text: this.$t('packagesmanagepackageconfiguration.success')
                            })
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.taxes'),
                                text: this.$t('packagesmanagepackageconfiguration.error.messages.system')
                            })
                        }
                        this.loading = false
                    })
            },
            submitNewChild: function () {
                this.loading = true
                API({
                    method: 'POST',
                    url: 'package/' + this.formNewChild.package_id + '/children',
                    data: this.formNewChild
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.$notify({
                                group: 'main',
                                type: 'success',
                                title: this.$t('global.modules.taxes'),
                                text: this.$t('packagesmanagepackageconfiguration.success')
                            })
                            this.fetchData()
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.taxes'),
                                text: result.data.data
                            })
                        }
                        this.loading = false
                    })
            },
            changeStateChild: function (child_id, status) {
                API({
                    method: 'put',
                    url: 'package/' + this.$route.params.package_id + '/children/' + child_id + '/status',
                    data: { status: status }
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.fetchData(localStorage.getItem('lang'))
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.rooms'),
                                text: this.$t('packagesmanagepackageconfiguration.error.messages.system')
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
                    url: 'package/' + this.$route.params.package_id + '/children/' + index
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.fetchData(localStorage.getItem('lang'))
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.rooms'),
                                text: this.$t('packagesmanagepackageconfiguration.error.messages.system')
                            })
                        }
                    })
            },
            statusB: function () {
                this.flag = false
            },
            updateTax: function (tax) {
                let pack_id = this.$route.params.package_id
                API({
                    method: 'post',
                    url: 'package/' + pack_id + '/taxes',
                    data: { tax_id: tax.id, used: tax.used }
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.fetchData(localStorage.getItem('lang'))
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.taxes'),
                                text: this.$t('packagesmanagepackageconfiguration.error.messages.system')
                            })
                        }
                    })
            },
            fetchData: function () {
                this.loading = true
                // Packages
                API.get('/packages/' + this.$route.params.package_id)
                    .then((result) => {

                        this.taxes = result.data.data.country.taxes

                        this.loading = false
                        this.formConfig.limit_confirmation_hours = result.data.data.limit_confirmation_hours
                        this.formConfig.infant_max_age = result.data.data.infant_max_age
                        this.formConfig.infant_min_age = result.data.data.infant_min_age
                        this.formConfig.infant_discount_rate = result.data.data.infant_discount_rate
                        this.formConfig.allow_guide =
                            (result.data.data.allow_guide != null && parseInt(result.data.data.allow_guide) == 1) ? true : false
                        this.formConfig.allow_child =
                            (result.data.data.allow_child != null && parseInt(result.data.data.allow_child) == 1) ? true : false
                        this.formConfig.allow_infant =
                            (result.data.data.allow_infant != null && parseInt(result.data.data.allow_infant) == 1) ? true : false
                    })
                    .catch(() => {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Fetch Error',
                            text: 'Cannot load data'
                        })
                    })

                // Children
                API.get('/package/' + this.$route.params.package_id + '/children')
                    .then((result) => {
                        this.loading = false
                        this.children = result.data.data
                    })
                    .catch(() => {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Fetch Error',
                            text: 'Cannot load data'
                        })
                    })
            },
            changePercentage: function (id,percentage) {
                console.log(id)
                API({
                    method: 'put',
                    url: 'package/children/' + id + '/percentage',
                    data: { percentage: percentage }
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.fetchData(localStorage.getItem('lang'))
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.rooms'),
                                text: this.$t('packagesmanagepackageconfiguration.error.messages.system')
                            })
                        }
                    })
            }
        }
    }
</script>

<style lang="stylus">

    .taxRow
        border dashed 1px #979797
        padding-top 10px

</style>


