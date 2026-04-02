<template>

    <div class="container-fluid">
        <table-server :columns="table.columns" :options="tableOptions" :url="urlServiceTypes" class="text-center"
                      ref="table">
            <div class="table-name" slot="name" slot-scope="props" style="font-size: 0.9em">
                {{ props.row.name }}
            </div>
            <div class="table-actions" slot="actions" slot-scope="props" style="padding: 5px;">
                <b-dropdown class="mt-2 ml-2 mb-0" dropleft size="sm">
                    <template slot="button-content">
                        <font-awesome-icon :icon="['fas', 'bars']" class="ml-1 p-0"/>
                    </template>
                    <router-link :to="'/positions/edit/'+props.row.id" class="nav-link m-0 p-0">
                        <b-dropdown-item-button class="m-0 p-0" v-if="$can('update', 'positions')">
                            <font-awesome-icon :icon="['fas', 'edit']" class="m-0"/>
                            {{ $t('global.buttons.edit') }}
                        </b-dropdown-item-button>
                    </router-link>
                    <b-dropdown-item-button @click="showModal(props.row.id,props.row.name)"
                                            class="m-0 p-0"
                                            v-if="$can('delete', 'positions')">
                        <font-awesome-icon :icon="['fas', 'trash']" class="m-0"/>
                        {{ $t('global.buttons.delete') }}
                    </b-dropdown-item-button>
                </b-dropdown>
            </div>
        </table-server>
        <b-modal :title="positionsName" centered ref="my-modal" size="sm">
            <p class="text-center">{{ $t('global.message_delete') }}</p>

            <div slot="modal-footer">
                <button @click="remove()" class="btn btn-success">{{ $t('global.buttons.accept') }}</button>
                <button @click="hideModal()" class="btn btn-danger">{{ $t('global.buttons.cancel') }}</button>
            </div>
        </b-modal>
    </div>
</template>

<script>
import {API} from './../../api'
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
            positionsName: '',
            urlServiceTypes: '/api/positions?token=' + window.localStorage.getItem('access_token') + '&lang=' +
                localStorage.getItem('lang'),
            table: {
                columns: ['id', 'name', 'actions'],
            }
        }
    },
    computed: {
        menuOptions: function () {
            return [
                {
                    type: 'edit',
                    link: 'positions/edit/',
                    icon: 'dot-circle',
                }
            ]
        },
        tableOptions: function () {
            return {
                headings: {
                    id: 'ID',
                    name: this.$i18n.t('global.name'),
                    actions: this.$i18n.t('global.table.actions')
                },
                sortable: ['id'],
                filterable: ['id']
            }
        }
    },
    mounted() {
        this.$i18n.locale = localStorage.getItem('lang')
    },
    methods: {
        showModal(position_id, serviceTypes_name) {
            this.position_id = position_id
            this.positionsName = serviceTypes_name
            this.$refs['my-modal'].show()
        },
        hideModal() {
            this.$refs['my-modal'].hide()
        },
        remove() {
            API({
                method: 'DELETE',
                url: 'positions/' + this.position_id
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
                                title: "Cargos",
                                text: "El cargo no puede ser eliminado, por que está siendo utilizado por otros registros"
                            })
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: "Cargos",
                                text: "El cargo ha sido eliminado"
                            })
                        }
                    }
                }).catch(() => {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('servicetypes.error.messages.name'),
                    text: this.$t('servicetypes.error.messages.connection_error')
                })
            })
        },
        onUpdate() {
            this.urlServiceTypes = '/api/positions?token=' + window.localStorage.getItem('access_token') + '&lang=' +
                localStorage.getItem('lang')
            this.$refs.table.$refs.tableserver.refresh()
        },
    }

}
</script>

<style lang="stylus">

</style>
