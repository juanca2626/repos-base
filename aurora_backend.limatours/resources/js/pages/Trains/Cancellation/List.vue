<template>
    <div class="animated fadeIn">
        <div class="row">
            <div class="col-xs-12 col-lg-12" v-if="flag==false">
                <button @click="create" class="btn btn-danger mb-4" type="reset">
                    {{ $t('hotelsmanagehotelconfiguration.new_policy') }}
                    <font-awesome-icon :icon="['fas', 'plus']" class="nav-icon"/>
                </button>
                <table-client :columns="table.columns" :data="cancellations" :loading="loading" :options="tableOptions"
                              id="dataTable"
                              theme="bootstrap4">
                    <div class="table-state" slot="status" slot-scope="props" style="font-size: 0.9em">
                        <b-form-checkbox
                                :checked="checkboxChecked(props.row.status)"
                                :id="'checkbox_'+props.row.id"
                                :name="'checkbox_'+props.row.id"
                                @change="changeState(props.row.id,props.row.status)"
                                switch>
                        </b-form-checkbox>
                    </div>
                    <div class="table-actions" slot="actions" slot-scope="props">
                        <menu-edit :id="props.row.id" :name="props.row.name" :options="menuOptions"
                                   @edit="edit(props.row, props.row.id)"
                                   @remove="remove(props.row.id)"/>
                    </div>
                    <div class="table-loading text-center" slot="loading" slot-scope="props">
                        <img alt="loading" height="51px" src="/images/loading.svg"/>
                    </div>
                </table-client>
            </div>

            <div class="col-xs-12 col-lg-12" v-else-if="flag=true">
                <cancellation-form :form="draft" @changeStatus="close" @close="flag"/>
            </div>

        </div>
    </div>
</template>

<script>

    import { API } from './../../../api'
    import TableClient from './.././../../components/TableClient'
    import MenuEdit from './../../../components/MenuEdit'
    import CancellationForm from './CancellationForm'
    import BDropDown from 'bootstrap-vue/es/components/dropdown/dropdown'
    import BDropDownItemButton from 'bootstrap-vue/es/components/dropdown/dropdown-item-button'

    export default {
        components: {
            'table-client': TableClient,
            'menu-edit': MenuEdit,
            'cancellation-form': CancellationForm,
            'b-dropdown': BDropDown,
            'b-dropdown-item-button': BDropDownItemButton
        },
        data: () => {
            return {
                loading: false,
                flag: false,
                cancellations: [],
                table: {
                    columns: ['actions', 'id', 'name', 'status']
                },
                draft: {
                    id: null,
                    name: '',
                },
            }
        },
        mounted () {

            this.$i18n.locale = localStorage.getItem('lang')
            this.$root.$emit('updateTitleTrain', { title: "Políticas de Cancelación de Trenes" })

            this.fetchData(this.$i18n.locale)
        },
        computed: {
            menuOptions: function () {

                let options = []

                options.push({
                    type: 'edit',
                    text: '',
                    link: '',
                    icon: 'dot-circle',
                    callback: '',
                    type_action: 'editButton'
                })
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
                        name: this.$i18n.t('global.name'),
                        status: this.$i18n.t('global.status'),
                        actions: this.$i18n.t('global.table.actions')
                    },
                    sortable: ['id'],
                    filterable: []
                }
            }
        },
        created () {
            this.$parent.$parent.$on('langChange', (payload) => {
                this.fetchData(payload.lang)
            })
        },
        methods: {
            changeState: function (id, status) {
                API({
                    method: 'put',
                    url: 'train_cancellation_policies/' + id + '/state',
                    data: { status: status }
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.fetchData(localStorage.getItem('lang'))
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.policy_cancellation'),
                                text: this.$t('hotelsmanagehotelconfiguration.error.messages.policy_delete')
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
            edit: function (data, index) {
                this.draft = clone(data)
                this.draft.action = 'put'
                this.change()
            },
            create: function () {
                this.draft = {
                    id: null,
                    name: '',
                    action: 'post',
                    count: 0,
                }
                this.change()
            },
            change: function () {
                if (this.flag === true) {
                    this.flag = false
                } else {
                    this.flag = true
                }
            },
            fetchData: function (lang) {
                this.loading = true
                API.get('train_cancellation_policies')
                    .then((result) => {
                        this.loading = false
                        if (result.data.success === true) {
                            this.cancellations = result.data.data
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
            },
            remove (id) {
                API({
                    method: 'DELETE',
                    url: 'train_cancellation_policies/' + id
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.fetchData(localStorage.getItem('lang'))
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.policy_cancellation'),
                                text: result.data.message
                            })
                        }
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: "Modulo de Trenes",
                        text: this.$t('hotelsmanagehotelconfiguration.error.messages.connection_error')
                    })
                })
            }
        }
    }
</script>

<style lang="stylus">
    .marl {
        margin-left: 800px;
    }
</style>


