<template>
    <div class="vld-parent">
        <loading :active.sync="loading" :can-cancel="false" color="#BD0D12"></loading>
        <div class="row pb-2">
                <div class="col-md-7">
                    <label>{{ $t('servicesmanageserviceincludes.includes') }}</label>
                    <div class="col-sm-12 p-0">
                        <v-select :options="includes"
                                  :value="form.include_id"
                                  @input="includeChange"
                                  autocomplete="true"
                                  data-vv-as="include"
                                  data-vv-name="include"
                                  name="include"
                                  v-model="includeSelected"
                                  v-validate="'required'">
                        </v-select>
                        <span class="invalid-feedback-select" v-show="errors.has('include')">
                                    <span>{{ $t('global.error.required') }}</span>
                                </span>
                    </div>
                </div>
                <div class="col-sm-1">
                    <label>{{ $t('servicesmanageserviceincludes.yes_no') }}</label>
                    <div class="col-sm-12 p-0">
                        <c-switch :value="true" class="mx-1" color="success"
                                  v-model="form.include"
                                  variant="pill">
                        </c-switch>
                    </div>
                </div>
                <div class="col-sm-2">
                    <label>{{ $t('servicesmanageserviceincludes.yes_no_client') }}</label>
                    <div class="col-sm-12 p-0">
                        <c-switch :value="true" class="mx-1" color="success"
                                  v-model="form.see_client"
                                  variant="pill">
                        </c-switch>
                    </div>
                </div>
                <div class="col-md-2">
                    <label>.</label>
                    <button @click="doSubmit" class="form-control btn btn-success"
                            type="button" v-if="!loading">
                        <font-awesome-icon :icon="['fas', 'dot-circle']" />
                        {{ $t('global.buttons.save') }}
                    </button>
                </div>
        </div>
        <table-server :columns="table.columns" :options="tableOptions" :url="urlIncludes"
                      class="text-center"
                      ref="table">
            <div class="table-includes" slot="includes" slot-scope="props"
                 style="font-size: 0.9em">
                {{props.row.inclusions.translations[0].value}}
            </div>
            <div class="table-option" slot="option"
                 slot-scope="props"
                 style="font-size: 0.9em">
                <b-form-checkbox
                    :checked="checkboxChecked(props.row.include)"
                    :id="'checkbox_'+props.row.id"
                    :name="'checkbox_'+props.row.id"
                    @change="changeState(props.row.id,props.row.include)"
                    switch>
                </b-form-checkbox>
            </div>
            <div class="table-option" slot="see_client"
                 slot-scope="props"
                 style="font-size: 0.9em">
                <b-form-checkbox
                    :checked="checkboxCheckedSeeClient(props.row.see_client)"
                    :id="'checkbox_see_client'+props.row.id"
                    :name="'checkbox_see_client'+props.row.id"
                    @change="changeStateSeeClient(props.row.id,props.row.see_client)"
                    switch>
                </b-form-checkbox>
            </div>
            <div class="table-actions" slot="actions" slot-scope="props" style="padding: 5px;">
                <b-dropdown class="mt-2 ml-2 mb-0" dropleft size="sm">
                    <template slot="button-content">
                        <font-awesome-icon :icon="['fas', 'bars']" class="ml-1 p-0" />
                    </template>
                    <b-dropdown-item-button
                        @click="showModal(props.row.id,props.row.inclusions.translations[0].value)"
                        class="m-0 p-0"
                        v-if="$can('delete', 'services')">
                        <font-awesome-icon :icon="['fas', 'trash']" class="m-0" />
                        {{$t('global.buttons.delete')}}
                    </b-dropdown-item-button>
                </b-dropdown>
            </div>
        </table-server>
    </div>

</template>

<script>
    import { API } from './../../../../api'
    import TableServer from '../../../../components/TableServer'
    import Loading from 'vue-loading-overlay'
    import 'vue-loading-overlay/dist/vue-loading.css'
    import BModal from 'bootstrap-vue/es/components/modal/modal'
    import vSelect from 'vue-select'
    import 'vue-select/dist/vue-select.css'
    import { Switch as cSwitch } from '@coreui/vue'
    import Multiselect from 'vue-multiselect'

    export default {
        components: {
            cSwitch,
            Loading,
            BModal,
            vSelect,
            Multiselect,
            'table-server': TableServer,

        },
        data: () => {
            return {
                emailsService: [],
                emails: [],
                form_notify: {
                    emails: [],
                    message: ''
                },
                urlIncludes: '',
                includes: [],
                includeSelected: [],
                loading: false,
                scheduleIndex: 0,
                formAction: 'post',
                inclusionName: '',
                service_inclusions_id: '',
                duration: 0,
                form: {
                    train_id: '',
                    inclusion_id: '',
                    include: true,
                    see_client: true
                },
                table: {
                    columns: ['includes', 'option', 'see_client', 'actions'],
                },
            }
        },
        mounted () {
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
                // console.log(err)
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('global.modules.inclusions'),
                    text: this.$t('global.error.messages.connection_error')
                })
            })
        },
        created () {
            this.form.train_id = this.$route.params.train_id
        },
        computed: {
            tableOptions: function () {
                return {
                    headings: {
                        includes: this.$i18n.t('servicesmanageserviceincludes.includes'),
                        option: this.$i18n.t('servicesmanageserviceincludes.option'),
                        see_client: this.$i18n.t('servicesmanageserviceincludes.yes_no_client'),
                        actions: this.$i18n.t('global.table.actions')
                    },
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
                        let url = '/train/' + this.$route.params.train_id + '/inclusions?token=' + window.localStorage.getItem('access_token') + '&lang=' + localStorage.getItem('lang')
                        return API.get(url, {
                            params: data
                        }).catch(function (e) {
                            this.dispatch('error', e)
                        }.bind(this))
                    }
                }
            },
        },
        methods: {
            includeChange: function (value) {
                this.include = value
                if (this.include != null) {
                    this.form.inclusion_id = this.include.code
                } else {
                    this.form.inclusion_id = ''
                    this.includeSelected = []
                }
            },
            showModal (id, inclusion) {
                this.service_inclusions_id = id
                this.inclusionName = inclusion
                this.$refs['my-modal'].show()
            },
            hideModal () {
                this.$refs['my-modal'].hide()
            },
            doSubmit() {
                this.$validator.validateAll().then((result) => {
                    if (result) {
                        this.loading = true
                        this.form.include = (this.form.include) ? 1 : 0
                        this.form.train_id = parseInt(this.form.train_id)
                        API.post('train/'+this.form.train_id+'/inclusions', this.form)
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
                                        type: 'success',
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
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.inclusions'),
                                text: this.$t('servicesmanageserviceincludes.error.messages.connection_error')
                            })
                        })
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.inclusions'),
                            text: this.$t('servicesmanageserviceincludes.error.messages.information_complete')
                        })
                        this.loading = false
                    }

                })
            },
            checkboxChecked: function (service_status) {
                if (service_status) {
                    return 'true'
                } else {
                    return 'false'
                }
            },
            checkboxCheckedSeeClient: function (service_status) {
                if (service_status) {
                    return 'true'
                } else {
                    return 'false'
                }
            },
            changeState: function (id, status) {
                API({
                    method: 'put',
                    url: 'train/inclusions/' + id + '/status',
                    data: { include: status }
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.onUpdate()
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.inclusions'),
                                text: this.$t('servicesmanageserviceincludes.error.messages.information_error')
                            })
                        }
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.inclusions'),
                        text: this.$t('servicesmanageserviceincludes.error.messages.connection_error')
                    })
                })
            },
            changeStateSeeClient: function (id, status) {
                API({
                    method: 'put',
                    url: 'train/inclusions/' + id + '/see_client',
                    data: { see_client: status }
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.onUpdate()
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.inclusions'),
                                text: this.$t('servicesmanageserviceincludes.error.messages.information_error')
                            })
                        }
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.inclusions'),
                        text: this.$t('servicesmanageserviceincludes.error.messages.connection_error')
                    })
                })
            },
            removeInclusion: function () {
                API({
                    method: 'DELETE',
                    url: 'train/inclusions/' + this.service_inclusions_id,
                })
                    .then((result) => {
                        if (result.data.success === true) {
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
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.services'),
                        text: this.$t('servicesmanageserviceincludes.error.messages.connection_error')
                    })
                })
            },
            onUpdate () {
                this.urlIncludes = '/api/train/' + this.$route.params.train_id + '/inclusions?token=' + window.localStorage.getItem('access_token') + '&lang=' + localStorage.getItem('lang')
                this.$refs.table.$refs.tableserver.refresh()
            },
            addEmail (newTag) {
                const tag = {
                    name: newTag,
                    email: newTag.substring(0, 2) + Math.floor((Math.random() * 10000000))
                }
                this.emailsService.push(tag)
            },
            removeEmail: function (index) {
                this.form_notify.emails.splice(index, 1)
            },

        }
    }
</script>

<style lang="stylus">
</style>


