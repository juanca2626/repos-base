<template>
    <div class="vld-parent">
        <loading :active.sync="loading" :can-cancel="false" color="#BD0D12"></loading>
        <div class="row pb-2">
            <div class="col-md-5">
                <label>{{ $t('service.featured') }}</label>
                <div class="col-sm-12 p-0">
                    <v-select :options="includes"
                              :value="form.info_important_service_id"
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
<!--            <div class="col-sm-1">-->
<!--                <label>{{ $t('servicesmanageserviceincludes.yes_no') }}</label>-->
<!--                <div class="col-sm-12 p-0">-->
<!--                    <c-switch :value="true" class="mx-1" color="success"-->
<!--                              v-model="form.include"-->
<!--                              variant="pill">-->
<!--                    </c-switch>-->
<!--                </div>-->
<!--            </div>-->
<!--            <div class="col-sm-2">-->
<!--                <label>{{ $t('servicesmanageserviceincludes.yes_no_client') }}</label>-->
<!--                <div class="col-sm-12 p-0">-->
<!--                    <c-switch :value="true" class="mx-1" color="success"-->
<!--                              v-model="form.see_client"-->
<!--                              variant="pill">-->
<!--                    </c-switch>-->
<!--                </div>-->
<!--            </div>-->
            <div class="col-md-2">
                <label>.</label>
                <button @click="validateBeforeSubmit" class="form-control btn btn-success"
                        type="submit" v-if="!loading">
                    <font-awesome-icon :icon="['fas', 'dot-circle']" />
                    {{ $t('global.buttons.save') }}
                </button>
            </div>
        </div>
        <table-server :columns="table.columns" :options="tableOptions" :url="urlIncludes"
                      class="text-center"
                      ref="table">
            <div class="table-day" slot="day" slot-scope="props"
                 style="font-size: 0.9em">
                {{props.row.day}}
            </div>
            <div class="table-includes" slot="featured" slot-scope="props"
                 style="font-size: 0.9em">
                {{props.row.featured.translations[0].value}}
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
                        @click="showModal(props.row.id,props.row.featured.translations[0].value)"
                        class="m-0 p-0"
                        v-if="$can('delete', 'informationimportantservices')">
                        <font-awesome-icon :icon="['fas', 'trash']" class="m-0" />
                        {{$t('global.buttons.delete')}}
                    </b-dropdown-item-button>
                </b-dropdown>
            </div>
        </table-server>
        <b-modal :title="inclusionName" centered ref="my-modal" size="sm">
            <p class="text-center">{{$t('global.message_delete')}}</p>
            <div slot="modal-footer">
                <button @click="removeInclusion()" class="btn btn-success">{{$t('global.buttons.accept')}}</button>
                <button @click="hideModal()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
            </div>
        </b-modal>
<!--        <b-modal title="Notificaciones" centered ref="my-modal-notify" size="md">-->
<!--            <div class="row" v-show="!optionSelect">-->
<!--                <div class="col-md-12">-->
<!--                    <p class="text-center">¿Desea enviar una notificación de los cambios realizados?</p>-->
<!--                </div>-->
<!--            </div>-->
<!--            <div class="form-group row" v-show="optionSelect">-->
<!--                <div class="col-sm-12">-->
<!--                    <label for="name_service">Emails</label>-->
<!--                    <multiselect :clear-on-select="false"-->
<!--                                 :close-on-select="false"-->
<!--                                 :hide-selected="true"-->
<!--                                 :multiple="true"-->
<!--                                 :options="emails"-->
<!--                                 placeholder="Emails"-->
<!--                                 :preserve-search="true"-->
<!--                                 tag-placeholder="Emails"-->
<!--                                 :taggable="true"-->
<!--                                 @tag="addEmail"-->
<!--                                 label="name"-->
<!--                                 ref="multiselect"-->
<!--                                 track-by="email"-->
<!--                                 v-model="emailsService">-->
<!--                    </multiselect>-->
<!--                </div>-->
<!--                <div class="col-md-12">-->
<!--                    <label for="name_service">Mensaje</label>-->
<!--                    <textarea :class="{'form-control':true }"-->
<!--                              id="message" name="descripction"-->
<!--                              rows="3"-->
<!--                              v-model="form_notify.message"></textarea>-->
<!--                </div>-->
<!--            </div>-->
<!--            <template slot="modal-footer">-->
<!--                <div class="row" v-show="!optionSelect">-->
<!--                    <div class="col-md-12">-->
<!--                        <button @click="optionSelection(1)" class="btn btn-success" type="reset" v-if="!loading">-->
<!--                            Si, enviar y guardar-->
<!--                        </button>-->
<!--                        <button @click="optionSelection(2)" class="btn btn-secondary" type="reset" v-if="!loading">-->
<!--                            No, solo guardar-->
<!--                        </button>-->
<!--                    </div>-->
<!--                </div>-->
<!--                <div class="row" v-show="optionSelect">-->
<!--                    <div class="col-md-12">-->
<!--                        <button @click="optionSelection(3)" class="btn btn-success" type="reset" v-if="!loading">-->
<!--                            Enviar y guardar-->
<!--                        </button>-->
<!--                        <button @click="hideModal()" class="btn btn-danger" type="reset" v-if="!loading">-->
<!--                            {{$t('global.buttons.cancel')}}-->
<!--                        </button>-->
<!--                    </div>-->
<!--                </div>-->
<!--            </template>-->
<!--        </b-modal>-->
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
                optionSelect: false,
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
                days: [],
                daySelected: [],
                duration: 0,
                form: {
                    // day: 1,
                    service_id: '',
                    info_important_service_id: '',
                    // include: true,
                    // see_client: true
                },
                table: {
                    columns: ['featured', 'actions'],
                },
            }
        },
        mounted () {
            // API.get('users/notification/service').then((result) => {
            //     let emails = result.data.data
            //     emails.forEach((email) => {
            //         this.emails.push({
            //             name: email.name + ' <' + email.email + '>',
            //             email: email.email
            //         })
            //     })
            // })
            // API.get('service/' + this.$route.params.service_id + '/configuration')
            //     .then((result) => {
            //         let duration = parseInt(result.data.data.duration)
            //         let type_duration = parseInt(result.data.data.unit_duration_id)
            //         if (type_duration == 2) { // si el tipo de duracion es 2 (dias)
            //             this.getDays(duration)
            //         } else {
            //             this.getDays(1)
            //         }
            //         this.daySelected.push({
            //             'code': 1,
            //             'label': 'Día 1'
            //         })
            //     }).catch(() => {
            //     this.$notify({
            //         group: 'main',
            //         type: 'error',
            //         title: this.$t('global.modules.inclusions'),
            //         text: this.$t('global.error.messages.connection_error')
            //     })
            // })
            //featured
            API.get('featured/selectBox?lang=' + localStorage.getItem('lang'))
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
                    title: this.$t('global.modules.featured'),
                    text: this.$t('global.error.messages.connection_error')
                })
            })
        },
        created () {
            this.form.service_id = this.$route.params.service_id
        },
        computed: {
            tableOptions: function () {
                return {
                    headings: {
                        featured: this.$i18n.t('global.modules.featured'),
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
                        let url = '/service/' + this.$route.params.service_id + '/featured?token=' + window.localStorage.getItem('access_token') + '&lang=' + localStorage.getItem('lang')
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
                    this.form.info_important_service_id = this.include.code
                } else {
                    this.form.info_important_service_id = ''
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
            validateBeforeSubmitOpt: function () {
                this.$validator.validateAll().then((result) => {
                    if (result) {
                        this.optionSelect = false
                        this.$refs['my-modal-notify'].show()
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.featured'),
                            text: this.$t('servicesmanageserviceincludes.error.messages.information_complete')
                        })
                        this.loading = false
                    }

                })
            },
            validateBeforeSubmit: function () {
                this.$validator.validateAll().then((result) => {
                    if (result) {
                        this.submit()
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.featured'),
                            text: this.$t('servicesmanageserviceincludes.error.messages.information_complete')
                        })
                        this.loading = false
                    }

                })
            },
            submit: function () {
                this.loading = true
                // this.form.include = (this.form.include === false ) ? 0 : 1
                // this.form.hasNotify = this.optionSelect
                // this.form.emails = this.emailsService
                // this.form.message = this.form_notify.message
                API({
                    method: 'POST',
                    url: 'service/featured',
                    data: this.form
                })
                    .then((result) => {
                        this.loading = false
                        if (result.data.success === true) {
                            this.onUpdate()
                            this.$notify({
                                group: 'main',
                                type: 'success',
                                title: this.$t('global.modules.featured'),
                                text: this.$t('servicesmanageserviceincludes.messages.successfully')
                            })
                        } else if (result.data.success === false) {
                            this.$notify({
                                group: 'main',
                                type: 'warning',
                                title: this.$t('global.modules.featured'),
                                text: this.$t('servicesmanageserviceincludes.validation.duplicity_inclusion')
                            })
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.featured'),
                                text: this.$t('servicesmanageserviceincludes.error.messages.service_incorrect')
                            })
                        }
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.featured'),
                        text: this.$t('servicesmanageserviceincludes.error.messages.connection_error')
                    })
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
                    url: 'service/inclusions/' + id + '/status',
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
                    url: 'service/inclusions/' + id + '/see_client',
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
                    url: 'service/featured/' + this.service_inclusions_id,
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.hideModal()
                            this.onUpdate()

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
                this.urlIncludes = '/api/service/' + this.$route.params.service_id + '/featured?token=' + window.localStorage.getItem('access_token') + '&lang=' + localStorage.getItem('lang')
                this.$refs.table.$refs.tableserver.refresh()
            },
            getDays: function (duration) {
                for (let i = 1; i <= duration; i++) {
                    this.days.push({
                        'code': i,
                        'label': 'Día ' + i,
                    })
                }
            },
            dayChange: function (value) {
                let day = value
                if (day != null) {
                    this.form.day = (day.code === '') ? 1 : day.code
                } else {
                    this.form.day = ''
                    this.daySelected = []
                }
                // this.findOperability(day.code)
            },
            optionSelection: function (option, form) {
                if (option === 1) {
                    this.optionSelect = true
                } else if (option === 2) {
                    this.$refs['my-modal-notify'].hide()
                    this.submit()
                } else {
                    if (this.emailsService.length === 0) {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.services'),
                            text: 'Debe seleccionar al menos un email'
                        })
                    } else {
                        if (this.optionSelect) {
                            this.$refs['my-modal-notify'].hide()
                            this.submit()
                        }

                    }
                }
            },
            // hideModal () {
            //     this.optionSelect = false
            //     this.$refs['my-modal-notify'].hide()
            // },
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


