<template>
    <div class="container-fluid">
        <table-server :columns="table.columns" :options="tableOptions" :url="urlCancellation" class="text-center"
                      ref="table_server">
            <div class="table-actions" slot="actions" slot-scope="props" style="padding: 5px;">
                <b-dropdown class="mt-2 ml-2 mb-0" dropright size="sm">
                    <template slot="button-content">
                        <font-awesome-icon :icon="['fas', 'bars']" class="ml-1 p-0"/>
                    </template>
                    <router-link :to="'/packages/cancellation_policies/edit/'+props.row.id" class="nav-link m-0 p-0">
                        <b-dropdown-item-button class="m-0 p-0" v-if="$can('update', 'packagecancellationpolicies')">
                            <font-awesome-icon :icon="['fas', 'edit']" class="m-0"/>
                            {{$t('global.buttons.edit')}}
                        </b-dropdown-item-button>
                    </router-link>
                    <b-dropdown-item-button @click="showModal(props.row.id,props.row)" class="m-0 p-0"
                                            v-if="$can('delete', 'packagecancellationpolicies')">
                        <font-awesome-icon :icon="['fas', 'trash']" class="m-0"/>
                        {{$t('global.buttons.delete')}}
                    </b-dropdown-item-button>
                </b-dropdown>
            </div>
            <div class="table-cancellation_fees" slot="cancellation_fees" slot-scope="props" style="font-size: 0.9em">
                <span class="badge badge-danger font-weight-bold" style="font-size: 15px">
                    {{props.row.cancellation_fees }} %
                </span>
            </div>
        </table-server>
        <b-modal :title="serviceName" centered ref="my-modal" size="sm">
            <p class="text-center">{{$t('global.message_delete')}}</p>
            <div slot="modal-footer">
                <button @click="remove()" class="btn btn-success">{{$t('global.buttons.accept')}}</button>
                <button @click="hideModal()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
            </div>
        </b-modal>
    </div>
</template>

<script>
    import { API } from './../../api'
    import TableServer from '../../components/TableServer'
    import BDropDown from 'bootstrap-vue/es/components/dropdown/dropdown'
    import BDropDownItemButton from 'bootstrap-vue/es/components/dropdown/dropdown-item-button'
    import BModal from 'bootstrap-vue/es/components/modal/modal'

    export default {
        components: {
            'table-server': TableServer,
            'b-dropdown': BDropDown,
            'b-dropdown-item-button': BDropDownItemButton,
            BModal
        },
        data: () => {
            return {
                table: {
                    columns: ['actions', 'pax_from', 'pax_to', 'day_from', 'day_to', 'cancellation_fees'],
                },
                urlCancellation: '',
                serviceName: '',
                package_highlight_id: '',
                id: '',
                loadFile: false,
                packages: []
            }
        },
        computed: {
            menuOptions: function () {
                return [
                    {
                        type: 'edit',
                        link: 'services_new/edit/',
                        icon: 'dot-circle',
                    },
                    {
                        type: 'delete',
                        link: 'services_new/edit/',
                        icon: 'times',
                    },
                ]
            },
            tableOptions: function () {
                return {
                    headings: {
                        id: 'ID',
                        pax_from: 'Pasajeros desde',
                        pax_to: 'Pasajeros hasta',
                        day_from: 'Días desde',
                        day_to: 'Días hasta',
                        cancellation_fees: 'Cargo de cancelación %',
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
                    requestFunction: function (data) {
                        let url = '/package/cancellation_policies?token=' + window.localStorage.getItem('access_token') + '&lang='
                            + localStorage.getItem('lang')
                        return API.get(url, {
                            params: data
                        }).catch(function (e) {
                            this.dispatch('error', e)
                        }.bind(this))
                    }
                }
            },
            tableOptionsPackage: function () {
                return {
                    headings: {
                        actions: 'Acciones',
                        name: 'Paquete',
                    },
                    sortable: [],
                    filterable: []
                }
            }
        },
        created () {
            this.csrf = window.Laravel.csrfToken
        },
        mounted () {
            this.$i18n.locale = localStorage.getItem('lang')
        },
        methods: {
            checkboxChecked: function (service_status) {
                if (service_status) {
                    return 'true'
                } else {
                    return 'false'
                }
            },
            showModal (service_id, service_name) {
                this.service_id = service_id
                this.$refs['my-modal'].show()
            },
            hideModal () {
                this.$refs['my-modal'].hide()
            },
            remove () {
                this.loading = true
                API({
                    method: 'DELETE',
                    url: 'package/cancellation_policies/' + this.service_id
                }).then((result) => {
                        this.loading = false
                        if (result.data.success === true) {
                            this.hideModal()
                            this.onUpdate()
                        }
                    }).catch(() => {
                    this.loading = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.packages'),
                        text: this.$t('inclusions.error.messages.connection_error')
                    })
                })
            },
            onUpdate () {
                this.urlCancellation = '/api/package/cancellation_policies?token=' + window.localStorage.getItem('access_token') + '&lang=' +
                    localStorage.getItem('lang')
                this.$refs.table.$refs.tableserver.refresh()
            },
        }
    }
</script>

<style lang="stylus">


    /* style 2 */
    .inputfile {
        width: 0.1px;
        height: 0.1px;
        opacity: 0;
        overflow: hidden;
        position: absolute;
        z-index: -1;
    }

    .inputfile + label {
        max-width: 80%;
        font-size: 0.875rem;
        font-weight: 400;
        text-overflow: ellipsis;
        white-space: nowrap;
        cursor: pointer;
        background-color: #bd0d12;
        padding: 0.375rem 0.75rem;
        border-radius: 0.25rem;
        line-height: 1.5;
        border: 1px solid transparent;
    }

    .inputfile + label {
        color: #fff;
    }

    .inputfile:focus + label,
    .inputfile.has-focus + label,
    .inputfile + label:hover {
        color: #722040;
    }

</style>
