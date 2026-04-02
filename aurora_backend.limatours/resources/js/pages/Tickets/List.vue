<template>
    <div class="container-fluid">
        <table-server :columns="table.columns" :options="tableOptions" :url="urlUnits" class="text-center"
                      ref="table">
            <div class="table-id" slot="id" slot-scope="props" style="font-size: 0.9em">
                {{ props.row.id }}
            </div>
            <div class="table-file_code" slot="file_code" slot-scope="props" style="font-size: 0.9em">
                {{ props.row.file_code }}
                <!--                <button type="button" class="btn btn-primary">{{ props.row.file_code }}-->
                <!--                    <i class="fas fa-eye"></i>-->
                <!--                </button>-->
            </div>
            <div class="table-service_or_hotel" slot="service_or_hotel" slot-scope="props" style="font-size: 0.9em">
               <span v-if="props.row.object_id !== '' && props.row.type == 'service'">
                   {{ props.row.service.aurora_code }} - Fecha: {{ props.row.date_service }}
               </span>
            </div>

            <div class="table-client" slot="client" slot-scope="props" style="font-size: 0.9em">
                {{ props.row.client.code }} - {{ props.row.client.name }}
            </div>

            <div class="table-action" slot="action" slot-scope="props" style="font-size: 20px">
                <span class="badge badge-primary" v-if="props.row.action === 'cancellation'">Cancelacíon - {{ props.row.type }}</span>
            </div>

            <div class="table-status" slot="status" slot-scope="props" style="font-size: 20px">
                <b-dropdown id="dropdown-right" right :text="(props.row.status == '0') ? 'Pendiente' : 'Cerrado'"
                            :variant="(props.row.status == '0') ? 'primary' : 'success'" class="m-2">
                    <b-dropdown-item href="#" @click="changeStatus(props.row.id,props.row.status)">
                        <span v-if="props.row.status == '0'">Cerrar</span>
                        <span v-else>Pendiente</span>
                    </b-dropdown-item>
                </b-dropdown>
            </div>

        </table-server>
        <b-modal :title="unitName" centered ref="my-modal" size="sm">
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
    import BFormCheckbox from 'bootstrap-vue/es/components/form-checkbox/form-checkbox'
    import BDropDown from 'bootstrap-vue/es/components/dropdown/dropdown'
    import BDropDownItemButton from 'bootstrap-vue/es/components/dropdown/dropdown-item-button'
    import BModal from 'bootstrap-vue/es/components/modal/modal'

    export default {
        components: {
            BFormCheckbox,
            'table-server': TableServer,
            'b-dropdown': BDropDown,
            'b-dropdown-item-button': BDropDownItemButton,
            BModal
        },
        data: () => {
            return {
                unitName: '',
                units: [],
                urlUnits: '/api/ticket?token=' + window.localStorage.getItem('access_token') + '&lang=' +
                    localStorage.getItem('lang'),
                table: {
                    columns: ['id', 'file_code', 'client', 'service_or_hotel', 'origin', 'action', 'status'],
                }
            }
        },
        computed: {
            menuOptions: function () {
                return [
                    {
                        type: 'edit',
                        link: 'units/edit/',
                        icon: 'dot-circle',
                    }
                ]
            },
            tableOptions: function () {
                return {
                    headings: {
                        id: 'ID',
                        file_code: 'File',
                        service_or_hotel: 'Servicio',
                        client: 'Cliente',
                        origin: 'Origen',
                        action: 'Accíon',
                        status: this.$i18n.t('services.service_status'),
                    },
                    sortable: ['id'],
                    filterable: ['id']
                }
            }
        },
        mounted () {
            this.$i18n.locale = localStorage.getItem('lang')
        },
        methods: {
            showModal (unit_id, unit_name) {
                this.unit_id = unit_id
                this.unitName = unit_name
                this.$refs['my-modal'].show()
            },
            hideModal () {
                this.$refs['my-modal'].hide()
            },
            changeStatus: function (id, status) {
                API({
                    method: 'put',
                    url: 'ticket/' + id + '/status',
                    data: { status: status }
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.onUpdate()
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.tickets'),
                                text: this.$t('global.error.messages.information_error')
                            })
                        }
                    })
            },
            remove () {
                API({
                    method: 'DELETE',
                    url: 'ticket/' + this.unit_id
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.onUpdate()
                            this.hideModal()
                        } else {
                            this.hideModal()
                            if (result.data.used === true) {
                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: this.$t('global.modules.units'),
                                    text: this.$t('units.error.messages.used')
                                })
                            } else {
                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: this.$t('global.modules.units'),
                                    text: this.$t('units.error.messages.unit_delete')
                                })
                            }

                        }
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('units.error.messages.name'),
                        text: this.$t('units.error.messages.connection_error')
                    })
                })
            },
            onUpdate () {
                this.urlUnits = '/api/ticket?token=' + window.localStorage.getItem('access_token') + '&lang=' +
                    localStorage.getItem('lang')
                this.$refs.table.$refs.tableserver.refresh()
            },
        }

    }
</script>

<style lang="stylus">
    .dropdown-toggle:after {
        display: inline-block;
        margin-left: .255em;
        vertical-align: .255em;
        content: "";
        border-top: .3em solid;
        border-right: .3em solid transparent;
        border-bottom: 0;
        border-left: .3em solid transparent;
    }
</style>
