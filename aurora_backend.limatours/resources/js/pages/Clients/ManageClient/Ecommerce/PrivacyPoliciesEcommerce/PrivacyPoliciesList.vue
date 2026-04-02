<template>
    <div class="vld-parent">
        <loading :active.sync="loading" :can-cancel="false" color="#BD0D12"></loading>
        <div>

            <div class="row mt-2">
                <div class="col-md-12">
                    <div class="card-header-actions">
                        <router-link :to="{ name: 'PrivacyPoliciesAddEcommerce' }">
                            <font-awesome-icon :icon="['fas', 'plus']" class="nav-icon"/>
                            {{ $t('global.buttons.add') }}
                        </router-link>
                    </div>
                </div>
                <table-client :columns="table.columns" :data="data_questions" :loading="loading" :options="tableOptions"
                              id="dataTable"
                              theme="bootstrap4">
                    <div class="table-actions" slot="actions" slot-scope="props">
                        <menu-edit :id="props.row.id"
                                   :name="(props.row.translations_title.length > 0) ? props.row.translations_title[0].value : ''"
                                   :options="menuOptions"
                                   @remove="remove(props.row.id)"/>
                    </div>
                    <div class="table-question" slot="politic" slot-scope="props">
                        <p class="font-weight-bold mb-0">
                            <span v-if="props.row.translations_title.length > 0">
                                {{ props.row.translations_title[0].value }}
                            </span>
                        </p>
                    </div>
                    <div slot="child_row" slot-scope="props" class="m-2">
                        <div class="ql-editor" v-if="props.row.translations_privacy_policy.length > 0">
                            <div v-html="props.row.translations_privacy_policy[0].value"></div>
                        </div>
                    </div>


                    <div class="table-loading text-center" slot="loading">
                        <img alt="loading" height="51px" src="/images/loading.svg"/>
                    </div>
                </table-client>
            </div>
        </div>

    </div>
</template>
<script>
    import { API } from '../../../../../api'
    import Loading from 'vue-loading-overlay'
    import 'vue-loading-overlay/dist/vue-loading.css'
    import vSelect from 'vue-select'
    import 'vue-select/dist/vue-select.css'
    import TableClient from '../../../../../components/TableClient'
    import MenuEdit from '../../../../../components/MenuEdit'
    import draggable from 'vuedraggable'

    export default {
        components: {
            Loading,
            vSelect,
            draggable,
            'menu-edit': MenuEdit,
            'table-client': TableClient,
        },
        data () {
            return {
                loading: false,
                question_id: '',
                data_questions: [],
                questions: [],
                questionsSelected: [],
                table: {
                    columns: ['politic', 'actions']
                }
            }
        },
        mounted: function () {
            this.fetchData()
        },
        computed: {
            menuOptions: function () {
                let options = []
                if (this.$can('update', 'clientecommerceprivacypolicies')) {
                    options.push({
                        type: 'edit',
                        text: '',
                        link: 'clients/' + this.$route.params.client_id + '/manage_client/regions/'+ this.$route.params.region_id +'/ecommerce/privacy_policies/edit/',
                        icon: 'dot-circle',
                        callback: '',
                        type_action: 'link'
                    })
                }
                if (this.$can('delete', 'clientecommerceprivacypolicies')) {
                    options.push({
                        type: 'delete',
                        text: '',
                        link: '',
                        icon: 'trash',
                        type_action: 'button',
                        callback_delete: 'remove'
                    })
                }
                return options
            },
            tableOptions: function () {
                return {
                    headings: {
                        id: 'ID',
                        politic: 'Política de privacidad',
                        actions: this.$i18n.t('global.table.actions')
                    },
                    sortable: ['id'],
                    filterable: ['id']
                }
            }
        },

        created () {
            this.$parent.$parent.$on('langChange', (payload) => {
                this.fetchData(payload.lang)
            })
        },
        methods: {
            validateBeforeSubmit: function () {
                this.$validator.validateAll().then((result) => {
                    if (result) {
                        this.submit()
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Ecommerce - Politicas de privacidad',
                            text: this.$t('packages.error.messages.information_complete')
                        })
                        this.loading = false
                    }
                })
            },
            submit () {

                this.loading = true

                API({
                    method: 'post',
                    url: 'client/ecommerce/privacy_policies',
                    data: {
                        client_id: this.$route.params.client_id,
                        questions_ids: this.questionsSelected,
                    }
                })
                    .then((result) => {
                        this.loading = false
                        if (result.data.success === true) {
                            this.fetchData()
                            this.$notify({
                                group: 'main',
                                type: 'success',
                                title: 'Ecommerce - Politicas de privacidad',
                                text: 'Guardado correctamente'
                            })
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Ecommerce - Politicas de privacidad',
                                text: result.data.message
                            })

                        }
                    })
            },
            fetchData: function () {
                this.loading = true
                API.get('client/' + this.$route.params.client_id + '/ecommerce/privacy_policies?lang=' + localStorage.getItem('lang')).then((result) => {
                    this.loading = false
                    if (result.data.success === true) {
                        this.data_questions = result.data.data
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Fetch Error',
                            text: result.data.message
                        })
                    }
                }).catch((error) => {
                    this.loading = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: error.response.data.title,
                        text: error.response.data.message
                    })
                })
            },
            remove (id) {
                API({
                    method: 'DELETE',
                    url: 'client/' + this.$route.params.client_id + '/ecommerce/privacy_policies/' + id
                })
                    .then((result) => {
                        if (result.data.success === false) {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Ecommerce - Politicas de privacidad',
                                text: result.data.message
                            })
                        } else {
                            this.fetchData()
                            this.$notify({
                                group: 'main',
                                type: 'success',
                                title: 'Ecommerce - Politicas de privacidad',
                                text: 'Eliminado correctamente'
                            })
                        }
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Ecommerce - Politicas de privacidad',
                        text: this.$t('inclusions.error.messages.connection_error')
                    })
                })
            }
        },

    }
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


</style>

<i18n src="./services.json"></i18n>
