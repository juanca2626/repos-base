<template>
    <div class="container-fluid">
        <div class="vld-parent">
            <loading :active.sync="loading" :can-cancel="false" color="#BD0D12"></loading>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group row">
                        <div class="col-sm-4">
                            <label>{{ $t('packagesquote.namerate') }}</label>
                            <div class="col-sm-12 p-0">
                                <input :class="{'form-control':true }"
                                       data-vv-as="name"
                                       data-vv-name="name"
                                       type="text"
                                       v-model="form.name"
                                       v-validate="'required'">
                                <span class="invalid-feedback" v-show="errors.has('name')">
                                <span>{{ errors.first('name') }}</span>
                            </span>
                            </div>
                        </div>

                        <div class="col-sm-2">
                            <label>Tipo</label>
                            <select class="form-control" v-model="form.service_type_id">
                                <option :value="services_type.id" v-for="services_type in services_types">{{
                                    services_type.code }}
                                </option>
                            </select>
                        </div>

                        <div class="col-sm-3">
                            <label>{{ $t('packagesquote.validitydate') }} - {{ $t('packagesquote.from') }}</label>
                            <div class="input-group">
                                <date-picker
                                    :config="datePickerFromOptions"
                                    @dp-change="setDateFrom"
                                    ref="datePickerFrom"
                                    id="dates_from"
                                    name="dates_from"
                                    autocomplete="off"
                                    data-vv-as="fecha"
                                    data-vv-name="dates_from"
                                    v-model="form.date_from" v-validate="'required'">
                                </date-picker>
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="basic-addon3">
                                        <i class="far fa-calendar"></i>
                                    </span>
                                </div>
                                <span class="invalid-feedback-select"
                                      v-show="errors.has('dates_from')">
                                    <span>{{ errors.first('dates_from') }}</span>
                                </span>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <label>{{ $t('packagesquote.to') }}</label>
                            <div class="input-group mb-3">
                                <date-picker
                                    :config="datePickerToOptions"
                                    ref="datePickerTo"
                                    id="dates_to"
                                    name="dates_to"
                                    autocomplete="off"
                                    data-vv-as="fecha"
                                    data-vv-name="dates_to"
                                    v-model="form.date_to" v-validate="'required'">
                                </date-picker>
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="basic-addon1">
                                        <i class="far fa-calendar"></i>
                                    </span>
                                </div>
                                <span class="invalid-feedback-select"
                                      v-show="errors.has('dates_to')">
                                    <span>{{ errors.first('dates_to') }}</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group row">

                        <div class="col-sm-6">
                            <label>{{ $t('packagesquote.selectcategory') }}</label>
                            <v-select :options="categories"
                                      id="categories"
                                      :value="category_id"
                                      @input="categoryChange"
                                      autocomplete="true"
                                      data-vv-as="categories"
                                      data-vv-name="categories"
                                      name="categories"
                                      v-model="categorySelected">
                            </v-select>
                            <span class="invalid-feedback-select" v-show="errors.has('categories')">
                                            <span>{{ errors.first('categories') }}</span>
                                        </span>
                        </div>
                        <div class="col-sm-2">
                            <button @click="addCategory" class="form-control btn btn-success" type="submit"
                                    style="margin-top: 28px;"
                                    v-if="!loading">
                                <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                                {{$t('packagesmanagepackagecustomers.buttons.add')}}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <table-client :columns="table.columns" :data="categoriesService" :loading="loading"
                                  :options="tableOptions" id="dataTable"
                                  theme="bootstrap4">
                        <div class="table-state" slot="name" slot-scope="props" style="font-size: 0.9em;padding: 10px">
                            {{props.row.name}}
                        </div>
                        <div class="table-actions" slot="actions" slot-scope="props" style="padding: 10px">
                            <button @click="showModal(props.row.id,props.row.count,props.row.name)"
                                    class="btn btn-danger"
                                    type="button"
                                    v-if="$can('delete', 'packagechildren')">
                                <font-awesome-icon :icon="['fas', 'trash']"/>
                            </button>
                        </div>
                        <div class="table-loading text-center" slot="loading">
                            <img alt="loading" height="51px" src="/images/loading.svg"/>
                        </div>
                    </table-client>
                </div>
            </div>
            <div class="row mt-3 pl-0">
                <div class="col-sm-6 pl-0">
                    <div slot="footer">
                        <img src="/images/loading.svg" v-if="loading" width="40px"/>
                        <button @click="validateBeforeSubmit" class="btn btn-success" type="submit" v-if="!loading">
                            <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                            {{$t('global.buttons.submit')}}
                        </button>
                        <button @click="CancelForm" class="btn btn-danger" type="reset" v-if="!loading">
                            {{$t('global.buttons.cancel')}}
                        </button>
                    </div>
                </div>
            </div>
            <b-modal :title="categoryName" centered ref="my-modal" size="sm">
                <p class="text-center">{{$t('global.message_delete')}}</p>
                <div slot="modal-footer">
                    <button @click="removeCategory()" class="btn btn-success">{{$t('global.buttons.accept')}}</button>
                    <button @click="hideModal()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
                </div>
            </b-modal>
        </div>
    </div>
</template>

<script>
    import { API } from './../../../../api'
    import { Switch as cSwitch } from '@coreui/vue'
    import TableClient from './.././../../../components/TableClient'
    import vSelect from 'vue-select'
    import 'vue-select/dist/vue-select.css'
    import Loading from 'vue-loading-overlay'
    import 'vue-loading-overlay/dist/vue-loading.css'
    import datePicker from 'vue-bootstrap-datetimepicker'
    import 'pc-bootstrap4-datetimepicker/build/css/bootstrap-datetimepicker.css'
    import moment from 'moment'
    import BModal from 'bootstrap-vue/es/components/modal/modal'

    export default {
        name: 'FormCostAdd',
        components: {
            'table-client': TableClient,
            cSwitch,
            vSelect,
            datePicker,
            BModal,
            Loading
        },
        data: () => {
            return {
                categoryName: '',
                category_id: '',
                categories: [],
                categorySelected: [],
                categoriesService: [],
                formAction: 'post',
                loading: false,
                services_types: [],
                form: {
                    id: '',
                    package_id: '',
                    name: '',
                    date_from: '',
                    date_to: '',
                    service_type_id: null
                },
                formDelete: {
                    id: null,
                    count: ''
                },
                datePickerFromOptions: {
                    format: 'DD/MM/YYYY',
                    useCurrent: false,
                    locale: localStorage.getItem('lang'),
                    widgetPositioning: { 'vertical': 'bottom' }
                },
                datePickerToOptions: {
                    format: 'DD/MM/YYYY',
                    useCurrent: false,
                    locale: localStorage.getItem('lang'),
                    widgetPositioning: { 'vertical': 'bottom' }
                },
                table: {
                    columns: ['name', 'actions']
                }
            }
        },
        computed: {
            tableOptions: function () {
                return {
                    headings: {
                        name: this.$i18n.t('packagesquote.categories'),
                        actions: this.$i18n.t('global.table.actions')
                    },
                    sortable: [],
                    filterable: [],
                    perPageValues: []
                }
            }
        },
        mounted: function () {
            this.getServicesTypes()
            this.$root.$emit('updateTitlePackage')
            this.form.package_id = this.$route.params.package_id
            //categories
            API.get('/typeclass/selectbox?lang=' + localStorage.getItem('lang'))
                .then((result) => {
                    this.loading = true
                    let categories = result.data.data
                    categories.forEach((category) => {
                        this.categories.push({
                            label: category.translations[0].value,
                            code: category.translations[0].object_id
                        })
                    })
                    //Modificar
                    let form = {}
                    if (this.$route.params.package_plan_rate_id !== undefined) {
                        API.get('package/plan_rates/' + this.$route.params.package_plan_rate_id + '?lang=' + localStorage.getItem('lang'))
                            .then((result) => {
                                form.package_plan = result.data.data
                                console.log(form.package_plan)
                                this.formAction = 'put'
                                this.form.name = form.package_plan.name
                                this.form.date_from = moment(form.package_plan.date_from).format('DD/MM/YYYY')
                                this.form.date_to = moment(form.package_plan.date_to).format('DD/MM/YYYY')
                                this.form.service_type_id = form.package_plan.service_type_id
                                this.loading = false
                                // this.categoriesService = form.package_plan.plan_rate_categories
                                let arraytranslCategories = form.package_plan.plan_rate_categories
                                let i = 0
                                let c = 1
                                arraytranslCategories.forEach((categories) => {
                                    this.categoriesService[i] = {
                                        id: categories.id,
                                        count: c,
                                        code: categories.category.translations[0].object_id,
                                        name: categories.category.translations[0].value
                                    }
                                    i++
                                    c++
                                })
                            }).catch(() => {
                            this.loading = false
                        })
                    } else {
                        this.loading = false
                    }
                }).catch(() => {
                this.loading = false
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('global.modules.services'),
                    text: this.$t('global.error.messages.connection_error')
                })
            })
        },
        methods: {
            getServicesTypes: function () {
                API.get('service_types')
                    .then((result) => {

                        this.services_types = result.data.data
                    })
            },
            categoryChange: function (value) {
                this.category = value
                if (this.category != null) {
                    this.category_id = this.category.code
                } else {
                    this.category_id = ''
                    this.categorySelected = []
                }
            },
            setDateFrom (e) {
                this.$refs.datePickerTo.dp.minDate(e.date)
            },
            validateBeforeSubmit () {
                this.$validator.validateAll().then((result) => {
                    if (result) {
                        this.submit()
                        this.$validator.reset() //solution
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.packages'),
                            text: this.$t('global.error.information_complete')
                        })
                        this.loading = false
                    }

                })
            },
            CancelForm () {
                this.$router.push({ name: 'PackageCostQuotes' })
            },
            submit () {
                //categories
                let varCategories = this.categoriesService
                if (varCategories.length === 0) {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.packages'),
                        text: this.$t('global.error.information_complete')
                    })
                } else {
                    let categories = []
                    varCategories.forEach((category) => {
                        categories.push({
                            'id': category.id,
                            'type_class_id': category.code
                        })
                    })
                    this.form.categories = categories
                    this.loading = true
                    API({
                        method: this.formAction,
                        url: 'package/plan_rates/' + (this.$route.params.package_plan_rate_id !== undefined ? this.$route.params.package_plan_rate_id : ''),
                        data: this.form
                    }).then((result) => {
                        this.loading = false
                        if (result.data.success === false) {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.packages'),
                                text: this.$t('global.error.save')
                            })

                        } else {
                            this.$router.push({
                                path: '/packages/' + this.$route.params.package_id + '/quotes/cost/' + result.data.package_plan_rate_id +
                                    '/category/' + result.data.package_plan_rate_category_id
                            })
                        }
                    }).catch(() => {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.packages'),
                            text: this.$t('global.error.messages.connection_error')
                        })
                    })
                }

            },
            addCategory: function () {
                let categories = this.categoriesService
                let flag = false
                categories.forEach((category) => {
                    console.log(category.code, this.categorySelected.code)
                    if (category.code == this.categorySelected.code) {
                        flag = true
                    }
                })
                console.log(flag)
                if (!flag) {
                    if (this.categorySelected.code == undefined) {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.packages'),
                            text: this.$t('packagesquote.selectcategory')
                        })
                    } else {
                        this.categoriesService.push({
                            id: null,
                            code: this.categorySelected.code,
                            name: this.categorySelected.label,
                        })
                    }
                } else {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.packages'),
                        text: this.$t('packagesquote.selectcategory')
                    })
                }
            },
            removeCategory: function () {

                if (this.categoriesService.length <= 1) {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.packages'),
                        text: 'Debe dejar al menos una categoría en la lista'
                    })
                } else {

                    if (this.formDelete.id != null) {
                        API({
                            method: 'DELETE',
                            url: 'package/package_plan_rate_category/' + (this.formDelete.id)
                        }).then((result) => {
                            if (result.data.success === true) {
                                this.removeItem(this.formDelete.count)
                                this.hideModal()
                            } else {
                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: this.$t('global.modules.packages'),
                                    text: this.$t('global.error.delete')
                                })
                                this.loading = false
                            }
                        }).catch(() => {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.packages'),
                                text: this.$t('global.error.delete')
                            })
                        })
                    } else {
                        this.removeItem(this.formDelete.count)
                    }
                }
            },
            removeItem: function (count) {
                let index = this.categoriesService.findIndex(parameter => parameter.count === count)
                this.categoriesService.splice(index, 1)
                this.formDelete.id = null
                this.formDelete.count = ''
                this.hideModal()
            },
            showModal (id, count, name) {
                this.formDelete.id = id
                this.formDelete.count = count
                this.categoryName = name
                this.$refs['my-modal'].show()
            },
            hideModal () {
                this.$refs['my-modal'].hide()
            },
        }
    }
</script>

<style lang="stylus">
    .invalid-feedback-select
        width: 100%;
        margin-top: 0.25rem;
        font-size: 80%;
        color: #f86c6b;

</style>
