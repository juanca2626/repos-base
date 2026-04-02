<template>
    <div class="vld-parent">
        <loading :active.sync="loading" :can-cancel="false" color="#BD0D12"></loading>
        <div class="row pb-2">
            <div class="col-md-5">
                <label>Highlights</label>
                <div class="col-sm-12 p-0">
                    <multiselect :clear-on-select="false"
                                 :close-on-select="false"
                                 :hide-selected="true"
                                 :searchable="true"
                                 :multiple="true"
                                 :options="highlights"
                                 placeholder="Seleccione..."
                                 :preserve-search="false"
                                 tag-placeholder="Seleccione..."
                                 :taggable="false"
                                 label="name"
                                 ref="multiselect"
                                 track-by="code"
                                 data-vv-as="highlights"
                                 data-vv-name="highlights"
                                 name="highlights"
                                 v-model="highlightsChoosed"
                                 v-validate="'required'">
                    </multiselect>
                    <span class="invalid-feedback-select" v-show="errors.has('highlights')">
                                <span>Campo requerido</span>
                            </span>
                </div>
            </div>
            <div class="col-md-2">
                <label>.</label>
                <button @click="validateBeforeSubmit" class="form-control btn btn-success"
                        type="submit" v-if="!loading">
                    <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                    {{ $t('global.buttons.save') }}
                </button>
            </div>
        </div>
        <table-server :columns="table.columns" :options="tableOptions" :url="urlIncludes"
                      class="text-center"
                      ref="table">
            <div class="table-day" slot="options" slot-scope="props"
                 style="font-size: 0.9em">
                <button class="btn btn-success" @click="upService(props.row.id,props.row.order)">
                    <font-awesome-icon :icon="['fas', 'angle-up']" class="m-0"/>
                </button>
                <button class="btn btn-danger" @click="downService(props.row.id,props.row.order)">
                    <font-awesome-icon :icon="['fas', 'angle-down']" class="m-0"/>
                </button>
            </div>
            <div class="table-includes" slot="highlights" slot-scope="props"
                 style="font-size: 0.9em;text-align: center">
                <b-card
                    no-body
                    :img-src="props.row.highlights.url"
                    img-alt="Image"
                    img-top
                    tag="article"
                    style="max-width: 8rem;"
                    class="mb-2">
                    <b-card-text>
                        {{props.row.highlights.translations[0].value}}
                    </b-card-text>
                </b-card>
            </div>
            <div class="table-actions" slot="actions" slot-scope="props" style="padding: 5px;">
                <b-dropdown class="mt-2 ml-2 mb-0" dropleft size="sm">
                    <template slot="button-content">
                        <font-awesome-icon :icon="['fas', 'bars']" class="ml-1 p-0"/>
                    </template>
                    <b-dropdown-item-button
                        @click="showModal(props.row.id,props.row.highlights.translations[0].value)"
                        class="m-0 p-0"
                        v-if="$can('delete', 'packagehighlights')">
                        <font-awesome-icon :icon="['fas', 'trash']" class="m-0"/>
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
                urlIncludes: '',
                highlights: [],
                highlightsChoosed: [],
                loading: false,
                scheduleIndex: 0,
                formAction: 'post',
                inclusionName: '',
                service_inclusions_id: '',
                days: [],
                daySelected: [],
                duration: 0,
                form: {
                    day: 1,
                    service_id: '',
                    inclusion_id: '',
                    include: true,
                    see_client: true
                },
                table: {
                    columns: ['options', 'highlights', 'actions'],
                },
            }
        },
        mounted () {
            API.get('/image_highlights?lang=' + localStorage.getItem('lang') + '&token=' + window.localStorage.getItem('access_token'))
                .then((result) => {
                    if (result.data.success === true) {
                        let highlights_data = result.data.data
                        let highlights = []
                        highlights_data.forEach(function (item) {
                            highlights.push({
                                'code': item.id,
                                'name': item.translations[0].value,
                            })
                        })
                        this.highlights = highlights
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Fetch Error',
                            text: result.data.message
                        })
                    }
                })
        },
        created () {
            this.form.service_id = this.$route.params.service_id
        },
        computed: {
            tableOptions: function () {
                return {
                    headings: {
                        options: 'Orden',
                        highlights: 'Highlights',
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
                        let url = '/packages/' + this.$route.params.package_id + '/highlights?token=' + window.localStorage.getItem('access_token') + '&lang=' + localStorage.getItem('lang')
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
            showModal (id, inclusion) {
                this.service_inclusions_id = id
                this.inclusionName = inclusion
                this.$refs['my-modal'].show()
            },
            hideModal () {
                this.$refs['my-modal'].hide()
            },
            validateBeforeSubmit: function () {
                this.$validator.validateAll().then((result) => {
                    if (result) {
                        this.submit()
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Paquete - Highlights',
                            text: this.$t('servicesmanageserviceincludes.error.messages.information_complete')
                        })
                        this.loading = false
                    }

                })
            },
            getIsdHighLights () {
                let ids = []
                this.highlightsChoosed.forEach(function (item) {
                    ids.push(item.code)
                })
                return ids
            },
            submit: function () {
                this.loading = true
                API({
                    method: 'POST',
                    url: 'packages/highlights',
                    data: {
                        'highlights': this.getIsdHighLights(),
                        'packages': [this.$route.params.package_id]
                    }
                })
                    .then((result) => {
                        this.loading = false
                        if (result.data.success === true) {
                            this.onUpdate()
                            this.highlightsChoosed = []
                            this.$notify({
                                group: 'main',
                                type: 'success',
                                title: 'Paquetes - Highlights',
                                text: this.$t('global.success.save')
                            })
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Paquetes - Highlights',
                                text: result.data.message
                            })

                        }
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Paquete - Highlights',
                        text: this.$t('servicesmanageserviceincludes.error.messages.connection_error')
                    })
                })
            },

            removeInclusion: function () {
                API({
                    method: 'DELETE',
                    url: 'packages/highlights/' + this.service_inclusions_id
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.onUpdate()
                            this.hideModal()
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Paquete - Highlights',
                                text: this.$t('servicesmanageserviceincludes.error.messages.service_delete')
                            })
                        }
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Paquete - Highlights',
                        text: this.$t('servicesmanageserviceincludes.error.messages.connection_error')
                    })
                })
            },
            onUpdate () {
                // this.urlIncludes = '/api/service/' + this.$route.params.service_id + '/inclusions?token=' + window.localStorage.getItem('access_token') + '&lang=' + localStorage.getItem('lang')
                this.$refs.table.$refs.tableserver.refresh()
                this.$forceUpdate()
                // window.location.reload()
            },
            upService: function (package_highlight_id, order) {
                //
                API({
                    method: 'put',
                    url: 'packages/highlights/up_order',
                    data: { package_highlight_id: package_highlight_id, order: order }
                })
                    .then((result) => {
                        this.onUpdate()
                    }).catch((e) => {
                    console.log(e)
                })
            },
            downService: function (package_highlight_id, order) {
                //
                API({
                    method: 'put',
                    url: 'packages/highlights/down_order',
                    data: { package_highlight_id: package_highlight_id, order: order }
                })
                    .then((result) => {
                        this.onUpdate()
                    }).catch((e) => {
                    console.log(e)
                })
            },

        }
    }
</script>

<style lang="stylus">
</style>


