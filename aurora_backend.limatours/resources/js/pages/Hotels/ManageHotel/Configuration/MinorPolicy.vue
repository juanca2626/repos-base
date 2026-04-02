<template>
    <div>
        <div class="row">
            <div class="col-sm-12">
                <form @submit>
                    <form class="row">
                        <div class="col-6">
                            <div class="col-12 mb-4 mt-2">
                                <b-form-checkbox id="checkbox-1"
                                                 name="checkbox-1"
                                                 unchecked-value="null" v-model="form.allows_child"
                                                 value="ok">
                                    {{$t('hotelsmanagehotelconfiguration.allows_child')}}
                                </b-form-checkbox>
                            </div>
                            <div class="col-12">
                                <div class="b-form-group form-group">
                                    <div class="form-row">
                                        <label class="col-sm-4 col-form-label"
                                               for="min_age_child">{{$t('hotelsmanagehotelconfiguration.min_child')}}</label>
                                        <!-- class="form-control input" -->
                                        <div class="col-sm-8">
                                            <input :class="{'form-control':true }"
                                                   id="min_age_child" name="min_age_child" placeholder="" type="text"
                                                   v-model="form.min_age_child" v-validate="'numeric'">
                                        </div>
                                        <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                            <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                               style="margin-left: 5px;"
                                                               v-show="errors.has('min_age_child')"/>
                                            <span
                                                v-show="errors.has('min_age_child')">{{ errors.first('min_age_child') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="b-form-group form-group">
                                    <div class="form-row">
                                        <label class="col-sm-4 col-form-label" for="max_child">{{$t('hotelsmanagehotelconfiguration.max_child')}}</label>
                                        <div class="col-sm-8">
                                            <input :class="{'form-control':true }"
                                                   id="max_age_child" name="max_age_child" placeholder="" type="text"
                                                   v-model="form.max_age_child" v-validate="'numeric'">
                                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                                   style="margin-left: 5px;"
                                                                   v-show="errors.has('max_age_child')"/>
                                                <span v-show="errors.has('max_age_child')">{{ errors.first('max_age_child') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="col-12 mb-4 mt-2">
                                <b-form-checkbox id="checkbox-2"
                                                 name="checkbox-2"
                                                 unchecked-value="null" v-model="form.allows_teenagers"
                                                 value="ok">
                                    {{$t('hotelsmanagehotelconfiguration.allows_teenagers')}}
                                </b-form-checkbox>
                            </div>
                            <div class="col-12">
                                <div class="b-form-group form-group">
                                    <div class="form-row">
                                        <label class="col-sm-4 col-form-label" for="min_age_teenagers">{{$t('hotelsmanagehotelconfiguration.min_teenagers')}}</label>
                                        <div class="col-sm-8">
                                            <input :class="{'form-control':true }"
                                                   id="min_age_teenagers" name="min_age_teenagers" placeholder=""
                                                   type="text" v-model="form.min_age_teenagers" v-validate="'numeric'">
                                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                                   style="margin-left: 5px;"
                                                                   v-show="errors.has('min_age_teenagers')"/>
                                                <span v-show="errors.has('min_age_teenagers')">{{ errors.first('min_age_teenagers') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="b-form-group form-group">
                                    <div class="form-row">
                                        <label class="col-sm-4 col-form-label"
                                               for="max_teenagers">{{$t('hotelsmanagehotelconfiguration.max_teenagers')}}</label>
                                        <div class="col-sm-8">
                                            <input :class="{'form-control':true }"
                                                   id="max_age_teenagers" name="max_age_teenagers" placeholder=""
                                                   type="text"
                                                   v-model="form.max_age_teenagers" v-validate="'numeric'">
                                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                                   style="margin-left: 5px;"
                                                                   v-show="errors.has('max_age_teenagers')"/>
                                                <span v-show="errors.has('max_age_teenagers')">{{ errors.first('max_age_teenagers') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </form>
            </div>
            <div class="col-sm-6">
                <div slot="footer">
                    <img src="/images/loading.svg" v-if="loading" width="40px"/>
                    <button @click="validateBeforeSubmit" class="btn btn-danger" type="submit" v-if="!loading">
                        <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                        {{$t('global.buttons.save')}}
                    </button>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 mb-3 mt-3">
                <p>Incluyentes</p>
                <hr style="border-top: 4px solid rgb(0 0 0 / 42%);">
            </div>
        </div>

        <div class="vld-parent">
            <loading :active.sync="loading" :can-cancel="false" color="#BD0D12"></loading>
            <div class="row">
                <div class="col-12">
                    <b-tabs fill>
                        <b-tab active>
                            <template #title>
                                Incluyente niños
                            </template>
                            <b-card-text>
                                <div class="row">
                                    <div class="col-md-5">
                                        <label>{{ $t('servicesmanageserviceincludes.includes') }}</label>
                                        <div class="col-sm-12 p-0">
                                            <v-select :options="includes"
                                                      :value="form_inclusion.include_id"
                                                      @input="includeChange"
                                                      autocomplete="true"
                                                      data-vv-as="include"
                                                      data-vv-name="include"
                                                      data-vv-scope="form-children"
                                                      name="include"
                                                      v-model="includeSelected"
                                                      v-validate="'required'">
                                            </v-select>
                                        </div>
                                    </div>
                                    <div class="col-sm-1">
                                        <label>{{ $t('servicesmanageserviceincludes.yes_no') }}</label>
                                        <div class="col-sm-12 p-0">
                                            <c-switch :value="true" class="mx-1" color="success"
                                                      v-model="form_inclusion.include"
                                                      data-vv-scope="form-children"
                                                      variant="pill">
                                            </c-switch>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <label>.</label>
                                        <img src="/images/loading.svg" v-if="loading" width="40px"/>
                                        <button @click="validateBeforeSubmitInclusion()"
                                                class="form-control btn btn-success"
                                                type="submit" v-if="!loading">
                                            <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                                            {{ $t('global.buttons.save') }}
                                        </button>
                                    </div>
                                    <div class="col-md-12 mt-5">
                                        <table-server :columns="table.columns" :options="tableOptions"
                                                      :url="urlIncludes"
                                                      class="text-center"
                                                      ref="table">
                                            <div class="table-day" slot="options" slot-scope="props"
                                                 style="font-size: 0.9em">
                                                <button class="btn btn-success"
                                                        @click="upService(props.row.id,props.row.order)">
                                                    <font-awesome-icon :icon="['fas', 'angle-up']" class="m-0"/>
                                                </button>
                                                <button class="btn btn-danger"
                                                        @click="downService(props.row.id,props.row.order)">
                                                    <font-awesome-icon :icon="['fas', 'angle-down']" class="m-0"/>
                                                </button>
                                            </div>
                                            <div class="table-day" slot="day" slot-scope="props"
                                                 style="font-size: 0.9em">
                                                {{props.row.day}}
                                            </div>
                                            <div class="table-includes" slot="includes" slot-scope="props"
                                                 style="font-size: 0.9em">
                                                {{props.row.inclusions.translations[0].value}}
                                            </div>
                                            <div class="table-option" slot="option"
                                                 slot-scope="props"
                                                 style="font-size: 0.9em">
                                                <b-form-checkbox
                                                    :key="'checkbox_'+props.row.id"
                                                    :checked="checkboxChecked(props.row.include)"
                                                    :id="'checkbox_'+props.row.id"
                                                    :name="'checkbox_'+props.row.id"
                                                    @change="changeState(props.row.id,props.row.include,'children')"
                                                    switch>
                                                </b-form-checkbox>
                                            </div>
                                            <div class="table-actions" slot="actions" slot-scope="props"
                                                 style="padding: 5px;">
                                                <b-dropdown class="mt-2 ml-2 mb-0" dropleft size="sm">
                                                    <template slot="button-content">
                                                        <font-awesome-icon :icon="['fas', 'bars']" class="ml-1 p-0"/>
                                                    </template>
                                                    <b-dropdown-item-button
                                                        @click="showModal(props.row.id,props.row.inclusions.translations[0].value)"
                                                        class="m-0 p-0"
                                                        v-if="$can('delete', 'hotels')">
                                                        <font-awesome-icon :icon="['fas', 'trash']" class="m-0"/>
                                                        {{$t('global.buttons.delete')}}
                                                    </b-dropdown-item-button>
                                                </b-dropdown>
                                            </div>
                                        </table-server>
                                    </div>
                                </div>
                            </b-card-text>
                        </b-tab>
                        <b-tab>
                            <template #title>
                                Incluyente infantes
                            </template>
                            <b-card-text>
                                <div class="row">
                                    <div class="col-md-5">
                                        <label>{{ $t('servicesmanageserviceincludes.includes') }}</label>
                                        <div class="col-sm-12 p-0">
                                            <v-select :options="includes"
                                                      :value="form_inclusion_infant.include_id"
                                                      @input="includeInfantChange"
                                                      autocomplete="true"
                                                      data-vv-as="include_infant"
                                                      data-vv-name="include_infant"
                                                      name="include"
                                                      data-vv-scope="form-infant"
                                                      v-model="includeInfantSelected"
                                                      v-validate="'required'">
                                            </v-select>
                                        </div>
                                    </div>
                                    <div class="col-sm-1">
                                        <label>{{ $t('servicesmanageserviceincludes.yes_no') }}</label>
                                        <div class="col-sm-12 p-0">
                                            <c-switch :value="true" class="mx-1" color="success"
                                                      v-model="form_inclusion_infant.include"
                                                      data-vv-scope="form-infant"
                                                      variant="pill">
                                            </c-switch>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <label>.</label>
                                        <img src="/images/loading.svg" v-if="loading" width="40px"/>
                                        <button @click="validateBeforeSubmitInclusionInfant()"
                                                class="form-control btn btn-success"
                                                type="submit" v-if="!loading">
                                            <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                                            {{ $t('global.buttons.save') }}
                                        </button>
                                    </div>
                                    <div class="col-md-12 mt-5">
                                        <table-server :columns="table.columns" :options="tableOptionsInfant"
                                                      :url="urlIncludesInfant"
                                                      class="text-center"
                                                      ref="tableInfant">
                                            <div class="table-day" slot="options" slot-scope="props"
                                                 style="font-size: 0.9em">
                                                <button class="btn btn-success"
                                                        @click="upServiceInfant(props.row.id,props.row.order)">
                                                    <font-awesome-icon :icon="['fas', 'angle-up']" class="m-0"/>
                                                </button>
                                                <button class="btn btn-danger"
                                                        @click="downServiceInfant(props.row.id,props.row.order)">
                                                    <font-awesome-icon :icon="['fas', 'angle-down']" class="m-0"/>
                                                </button>
                                            </div>
                                            <div class="table-day" slot="day" slot-scope="props"
                                                 style="font-size: 0.9em">
                                                {{props.row.day}}
                                            </div>
                                            <div class="table-includes" slot="includes" slot-scope="props"
                                                 style="font-size: 0.9em">
                                                {{props.row.inclusions.translations[0].value}}
                                            </div>
                                            <div class="table-option" slot="option"
                                                 slot-scope="props"
                                                 style="font-size: 0.9em">
                                                <b-form-checkbox
                                                    :key="'_checkbox_'+props.row.id"
                                                    :checked="checkboxChecked(props.row.include)"
                                                    :id="'checkbox_'+props.row.id"
                                                    :name="'checkbox_'+props.row.id"
                                                    @change="changeState(props.row.id,props.row.include,'infant')"
                                                    switch>
                                                </b-form-checkbox>
                                            </div>
                                            <div class="table-actions" slot="actions" slot-scope="props"
                                                 style="padding: 5px;">
                                                <b-dropdown class="mt-2 ml-2 mb-0" dropleft size="sm">
                                                    <template slot="button-content">
                                                        <font-awesome-icon :icon="['fas', 'bars']" class="ml-1 p-0"/>
                                                    </template>
                                                    <b-dropdown-item-button
                                                        @click="showModalInfant(props.row.id,props.row.inclusions.translations[0].value)"
                                                        class="m-0 p-0"
                                                        v-if="$can('delete', 'hotels')">
                                                        <font-awesome-icon :icon="['fas', 'trash']" class="m-0"/>
                                                        {{$t('global.buttons.delete')}}
                                                    </b-dropdown-item-button>
                                                </b-dropdown>
                                            </div>
                                        </table-server>
                                    </div>
                                </div>
                            </b-card-text>
                        </b-tab>
                    </b-tabs>
                </div>

            </div>
        </div>
        <b-modal :title="inclusionName" centered ref="my-modal" size="sm">
            <p class="text-center">{{$t('global.message_delete')}}</p>
            <div slot="modal-footer">
                <button @click="removeInclusion()" class="btn btn-success">{{$t('global.buttons.accept')}}</button>
                <button @click="hideModal()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
            </div>
        </b-modal>
        <b-modal :title="inclusionName" centered ref="my-modal-infant" size="sm">
            <p class="text-center">{{$t('global.message_delete')}}</p>
            <div slot="modal-footer">
                <button @click="removeInclusionInfant()" class="btn btn-success">{{$t('global.buttons.accept')}}
                </button>
                <button @click="hideModalInfant()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
            </div>
        </b-modal>

    </div>
</template>

<script>
    import { API } from './../../../../api'
    import VueBootstrapTypeahead from 'vue-bootstrap-typeahead'
    import { Switch as cSwitch } from '@coreui/vue'
    import vSelect from 'vue-select'
    import TableServer from '../../../../components/TableServer'
    import BModal from 'bootstrap-vue/es/components/modal/modal'
    import Loading from 'vue-loading-overlay'
    import 'vue-loading-overlay/dist/vue-loading.css'
    export default {
        components: {
            VueBootstrapTypeahead,
            vSelect,
            cSwitch,
            'table-server': TableServer,
            BModal,
            Loading
        },
        data: () => {
            return {
                languages: [],
                minor: [],
                includes: [],
                includeSelected: [],
                includeInfantSelected: [],
                loading: false,
                showError: false,
                invalidError: false,
                countError: 0,
                form: {
                    allows_child: null,
                    min_age_child: null,
                    max_age_child: null,
                    allows_teenagers: null,
                    max_age_teenagers: null,
                    min_age_teenagers: null,
                },
                form_inclusion: {
                    hotel_id: '',
                    inclusion_id: '',
                    include: true,
                },
                form_inclusion_infant: {
                    hotel_id: '',
                    inclusion_id: '',
                    include: true,
                },
                table: {
                    columns: ['options', 'includes', 'option', 'actions'],
                },
                urlIncludes: '',
                urlIncludesInfant: '',
                inclusionName: '',
                service_inclusions_id: '',
            }
        },
        computed: {
            tableOptions: function () {
                return {
                    headings: {
                        includes: this.$i18n.t('servicesmanageserviceincludes.includes'),
                        option: this.$i18n.t('servicesmanageserviceincludes.option'),
                        actions: this.$i18n.t('global.table.actions')
                    },
                    perPage: 30,
                    sortable: [],
                    filterable: [],
                    perPageValues: [],
                    responseAdapter ({ data }) {
                        return {
                            data: data.data,
                            count: data.count
                        }
                    },
                    params: {},
                    requestFunction: function (data) {
                        let url = '/hotel/' + this.$route.params.hotel_id + '/children/inclusions?token=' + window.localStorage.getItem('access_token') + '&lang=' + localStorage.getItem('lang')
                        return API.get(url, {
                            params: data
                        }).catch(function (e) {
                            this.dispatch('error', e)
                        }.bind(this))
                    }
                }
            },
            tableOptionsInfant: function () {
                return {
                    headings: {
                        includes: this.$i18n.t('servicesmanageserviceincludes.includes'),
                        option: this.$i18n.t('servicesmanageserviceincludes.option'),
                        actions: this.$i18n.t('global.table.actions')
                    },
                    perPage: 30,
                    sortable: [],
                    filterable: [],
                    perPageValues: [],
                    responseAdapter ({ data }) {
                        return {
                            data: data.data,
                            count: data.count
                        }
                    },
                    params: {},
                    requestFunction: function (data) {
                        let url = '/hotel/' + this.$route.params.hotel_id + '/infant/inclusions?token=' + window.localStorage.getItem('access_token') + '&lang=' + localStorage.getItem('lang')
                        return API.get(url, {
                            params: data
                        }).catch(function (e) {
                            this.dispatch('error', e)
                        }.bind(this))
                    }
                }
            },

        },
        mounted: function () {
            this.fetchData(this.$i18n.locale)
            //inclusions
            API.get('inclusions/selectBox?lang=' + localStorage.getItem('lang'))
                .then((result) => {
                    let inclusions = result.data.data
                    let label = ''
                    inclusions.forEach((inclusions) => {
                        label = (inclusions.translations.length > 0) ? inclusions.translations[0].value : ''
                        this.includes.push({
                            label: label,
                            code: inclusions.id
                        })
                    })

                }).catch((err) => {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('global.modules.inclusions'),
                    text: this.$t('global.error.messages.connection_error')
                })
            })
        },
        created () {
            this.form_inclusion.hotel_id = this.$route.params.hotel_id
            this.form_inclusion_infant.hotel_id = this.$route.params.hotel_id
            this.$parent.$parent.$on('langChange', (payload) => {
                this.fetchData(payload.lang)
            })
        },
        methods: {
            close () {
                this.$emit('changeStatus', false)
            },
            validateBeforeSubmit () {

                if (this.form == null) {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('hotelsmanagehotelconfiguration.hotel'),
                        text: this.$t('hotelsmanagehotelconfiguration.error.messages.hotel_incorrect')
                    })
                    return false
                }
                this.$validator.validateAll().then((result) => {
                    if (result) {

                        this.submit()

                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.contacts'),
                            text: this.$t('hotelsmanagehotelconfiguration.error.messages.information_complete')
                        })

                        this.loading = false
                    }
                })
            },
            fetchData: function (lang) {
                this.loading = true
                API.get('minor_policies/?lang=' + lang + '&id=' + this.$route.params.hotel_id).then((result) => {
                    this.loading = false
                    if (result.data.success === true) {
                        this.minor = result.data.data
                        if (this.minor[0].allows_child == 1) {
                            this.form.allows_child = 'ok'
                        }
                        if (this.minor[0].allows_teenagers == 1) {
                            this.form.allows_teenagers = 'ok'
                        }
                        this.form.min_age_child = this.minor[0].min_age_child
                        this.form.max_age_child = this.minor[0].max_age_child
                        this.form.max_age_teenagers = this.minor[0].max_age_teenagers
                        this.form.min_age_teenagers = this.minor[0].min_age_teenagers
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Fetch Error',
                            text: result.data.message
                        })
                    }
                })
                    .catch(() => {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Fetch Error',
                            text: 'Cannot load data'
                        })
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
            submit () {
                if ((this.form.allows_teenagers == 'ok' && this.form.max_age_teenagers == (this.form.min_age_child - 1)) || (this.form.allows_teenagers == null || this.form.allows_teenagers == 0)) {
                    API({
                        method: 'put',
                        url: 'minor_policies/' + (this.$route.params.hotel_id),
                        data: this.form
                    })
                        .then((result) => {
                            if (result.data.success === false) {
                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: this.$t('global.modules.minor_policy'),
                                    text: this.$t('hotelsmanagehotelconfiguration.error_insert')
                                })

                                this.loading = false
                            } else {
                                this.$notify({
                                    group: 'main',
                                    type: 'success',
                                    title: this.$t('global.modules.minor_policy'),
                                    text: this.$t('hotelsmanagehotelconfiguration.success_insert')
                                })
                            }
                        }).catch(() => {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('hotelsmanagehotelconfiguration.error.messages.name'),
                            text: this.$t('hotelsmanagehotelconfiguration.error.messages.connection_error')
                        })
                    })

                } else {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.minor_policy'),
                        text: 'La edad maxima de infante debe de ser igual a la edad minima de niño - 1'
                    })
                }

            },
            includeChange: function (value) {
                this.include = value
                if (this.include != null) {
                    this.form_inclusion.inclusion_id = this.include.code
                } else {
                    this.form_inclusion.inclusion_id = ''
                    this.includeSelected = []
                }
            },
            includeInfantChange: function (value) {
                this.include_infant = value
                if (this.include_infant != null) {
                    this.form_inclusion_infant.inclusion_id = this.include_infant.code
                } else {
                    this.form_inclusion_infant.inclusion_id = ''
                    this.includeInfantSelected = []
                }
            },
            validateBeforeSubmitInclusion: function () {
                this.$validator.validateAll('form-children').then((result) => {
                    if (result) {
                        this.submitInclusion()
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.inclusions'),
                            text: 'Debe de seleccionar una inclusion para el niño'
                        })
                    }
                })
            },
            validateBeforeSubmitInclusionInfant: function () {
                this.$validator.validateAll('form-infant').then((result) => {
                    if (result) {
                        this.submitInclusionInfant()
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.inclusions'),
                            text: 'Debe de seleccionar una inclusion para el infante'
                        })
                    }
                })
            },
            submitInclusion: function () {
                this.loading = true
                API({
                    method: 'POST',
                    url: 'hotel/children/inclusions',
                    data: {
                        hotel_id: this.form_inclusion.hotel_id,
                        inclusion_id: this.form_inclusion.inclusion_id,
                        include: (this.form_inclusion.include === false) ? 0 : 1,
                    },
                })
                    .then((result) => {
                        this.loading = false
                        if (result.data.success === true) {
                            this.onUpdate()
                            this.$notify({
                                group: 'main',
                                type: 'success',
                                title: this.$t('global.modules.inclusions'),
                                text: this.$t('servicesmanageserviceincludes.messages.successfully')
                            })
                        } else if (result.data.success === false) {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.inclusions'),
                                text: this.$t('servicesmanageserviceincludes.validation.duplicity_inclusion')
                            })
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.inclusions'),
                                text: this.$t('servicesmanageserviceincludes.error.messages.service_incorrect')
                            })
                        }
                    }).catch(() => {
                    this.loading = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.inclusions'),
                        text: this.$t('servicesmanageserviceincludes.error.messages.connection_error')
                    })
                })
            },
            submitInclusionInfant: function () {
                this.loading = true
                API({
                    method: 'POST',
                    url: 'hotel/infant/inclusions',
                    data: {
                        hotel_id: this.form_inclusion_infant.hotel_id,
                        inclusion_id: this.form_inclusion_infant.inclusion_id,
                        include: (this.form_inclusion_infant.include === false) ? 0 : 1,
                    },
                })
                    .then((result) => {
                        this.loading = false
                        if (result.data.success === true) {
                            this.onUpdateInfant()
                            this.$notify({
                                group: 'main',
                                type: 'success',
                                title: this.$t('global.modules.inclusions'),
                                text: this.$t('servicesmanageserviceincludes.messages.successfully')
                            })
                        } else if (result.data.success === false) {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.inclusions'),
                                text: this.$t('servicesmanageserviceincludes.validation.duplicity_inclusion')
                            })
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.inclusions'),
                                text: this.$t('servicesmanageserviceincludes.error.messages.service_incorrect')
                            })
                        }
                    }).catch(() => {
                    this.loading = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.inclusions'),
                        text: this.$t('servicesmanageserviceincludes.error.messages.connection_error')
                    })
                })
            },
            onUpdate () {
                this.urlIncludes = '/api/hotel/' + this.$route.params.hotel_id + '/children/inclusions?token=' + window.localStorage.getItem('access_token') + '&lang=' + localStorage.getItem('lang')
                this.$refs.table.$refs.tableserver.refresh()
                this.$forceUpdate()
            },
            onUpdateInfant () {
                this.urlIncludes = '/api/hotel/' + this.$route.params.hotel_id + '/infant/inclusions?token=' + window.localStorage.getItem('access_token') + '&lang=' + localStorage.getItem('lang')
                this.$refs.tableInfant.$refs.tableserver.refresh()
                this.$forceUpdate()
            },
            checkboxChecked: function (service_status) {
                if (service_status) {
                    return 'true'
                } else {
                    return 'false'
                }
            },
            upService: function (hotel_children_inclusion_id, order) {
                this.loading = true
                API({
                    method: 'put',
                    url: 'hotel/children/inclusion/up_order',
                    data: { hotel_children_inclusion_id: hotel_children_inclusion_id, order: order }
                })
                    .then((result) => {
                        this.loading = false
                        this.onUpdate()
                    }).catch((e) => {
                    console.log(e)
                })
            },
            upServiceInfant: function (hotel_children_inclusion_id, order) {
                this.loading = true
                API({
                    method: 'put',
                    url: 'hotel/infant/inclusion/up_order',
                    data: { hotel_children_inclusion_id: hotel_children_inclusion_id, order: order }
                })
                    .then((result) => {
                        this.loading = false
                        this.onUpdateInfant()
                    }).catch((e) => {
                    this.loading = false
                    console.log(e)
                })
            },
            downService: function (hotel_children_inclusion_id, order) {
                this.loading = true
                API({
                    method: 'put',
                    url: 'hotel/children/inclusion/down_order',
                    data: { hotel_children_inclusion_id: hotel_children_inclusion_id, order: order }
                })
                    .then((result) => {
                        this.loading = false
                        this.onUpdate()
                    }).catch((e) => {
                    this.loading = false
                    console.log(e)
                })
            },
            downServiceInfant: function (hotel_children_inclusion_id, order) {
                this.loading = true
                API({
                    method: 'put',
                    url: 'hotel/infant/inclusion/down_order',
                    data: { hotel_children_inclusion_id: hotel_children_inclusion_id, order: order }
                })
                    .then((result) => {
                        this.loading = false
                        this.onUpdateInfant()
                    }).catch((e) => {
                    this.loading = false
                    console.log(e)
                })
            },
            showModal (id, inclusion) {
                this.service_inclusions_id = id
                this.inclusionName = inclusion
                this.$refs['my-modal'].show()
            },
            showModalInfant (id, inclusion) {
                this.service_inclusions_id = id
                this.inclusionName = inclusion
                this.$refs['my-modal-infant'].show()
            },
            hideModal () {
                this.$refs['my-modal'].hide()
            },
            hideModalInfant () {
                this.$refs['my-modal-infant'].hide()
            },
            removeInclusion: function () {
                this.loading = true
                API({
                    method: 'DELETE',
                    url: 'hotel/children/inclusions/' + this.service_inclusions_id,
                })
                    .then((result) => {
                        this.loading = false
                        if (result.data.success === true) {
                            this.service_inclusions_id = ''
                            this.onUpdate()
                            this.hideModal()
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.services'),
                                text: this.$t('servicesmanageserviceincludes.error.messages.service_delete')
                            })
                        }
                    }).catch(() => {
                    this.loading = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.services'),
                        text: this.$t('servicesmanageserviceincludes.error.messages.connection_error')
                    })
                })
            },
            removeInclusionInfant: function () {
                this.loading = true
                API({
                    method: 'DELETE',
                    url: 'hotel/infant/inclusions/' + this.service_inclusions_id,
                })
                    .then((result) => {
                        this.loading = false
                        if (result.data.success === true) {
                            this.service_inclusions_id = ''
                            this.onUpdateInfant()
                            this.hideModalInfant()
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.services'),
                                text: this.$t('servicesmanageserviceincludes.error.messages.service_delete')
                            })
                        }
                    }).catch(() => {
                    this.loading = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.services'),
                        text: this.$t('servicesmanageserviceincludes.error.messages.connection_error')
                    })
                })
            },
            changeState: function (id, status, type) {
                this.loading = true
                API({
                    method: 'put',
                    url: 'hotel/' + type + '/inclusions/' + id + '/status',
                    data: { include: status }
                })
                    .then((result) => {
                        this.loading = false
                        if (result.data.success === true) {
                            if (type === 'children') {
                                this.onUpdate()
                            } else {
                                this.onUpdateInfant()
                            }
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.inclusions'),
                                text: this.$t('servicesmanageserviceincludes.error.messages.information_error')
                            })
                        }
                    }).catch(() => {
                    this.loading = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.inclusions'),
                        text: this.$t('servicesmanageserviceincludes.error.messages.connection_error')
                    })
                })
            },

        }
    }
</script>

<style lang="stylus">

</style>
