<template>
    <div class="container-fluid">
        <table-server :columns="table.columns" :options="tableOptions" :url="urlServiceCagories" class="text-center"
                      ref="table">
            <div class="table-name" slot="area" slot-scope="props" style="font-size: 0.9em">
                <h6 class="font-weight-bold">{{ props.row.name }}</h6>
            </div>
            <div class="table-name" slot="teams" slot-scope="props" style="font-size: 0.9em">
                <h5>
                    <b-badge pill variant="success" class="mr-2" v-for="team in props.row.teams" :key="team.id">
                        {{ team.name }}
                    </b-badge>
                </h5>

            </div>
            <div class="table-actions" slot="actions" slot-scope="props" style="padding: 5px;">
                <b-dropdown class="mt-2 ml-2 mb-0" dropleft size="sm">
                    <template slot="button-content">
                        <font-awesome-icon :icon="['fas', 'bars']" class="ml-1 p-0"/>
                    </template>
                    <router-link :to="'/departments/edit/'+props.row.id" class="nav-link m-0 p-0">
                        <b-dropdown-item-button class="m-0 p-0" v-if="$can('update', 'departments')">
                            <font-awesome-icon :icon="['fas', 'edit']" class="m-0"/>
                            {{ $t('global.buttons.edit') }}
                        </b-dropdown-item-button>
                    </router-link>
                    <router-link :to="'/departments/'+props.row.id +'/teams'" class="nav-link m-0 p-0">
                        <b-dropdown-item-button class="m-0 p-0" v-if="$can('read', 'departments')">
                            <font-awesome-icon :icon="['fas', 'bars']" class="m-0"/>
                            Ver Equipos
                        </b-dropdown-item-button>
                    </router-link>
                    <b-dropdown-item-button @click="showModal(props.row.id,props.row.name)"
                                            class="m-0 p-0"
                                            v-if="$can('delete', 'departments')">
                        <font-awesome-icon :icon="['fas', 'trash']" class="m-0"/>
                        {{ $t('global.buttons.delete') }}
                    </b-dropdown-item-button>
                </b-dropdown>
            </div>
        </table-server>
        <b-modal :title="serviceCategoryName" centered ref="my-modal" size="sm">
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
            serviceCategoryName: '',
            urlServiceCagories: '/api/departments?token=' + window.localStorage.getItem('access_token') + '&lang=' +
                localStorage.getItem('lang'),
            table: {
                columns: ['id', 'area', 'teams', 'actions'],
            }
        }
    },
    computed: {
        menuOptions: function () {
            return [
                {
                    type: 'edit',
                    link: 'departments/edit/',
                    icon: 'dot-circle',
                }
            ]
        },
        tableOptions: function () {
            return {
                headings: {
                    id: 'ID',
                    area: 'Area',
                    teams: 'Equipos',
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
        showModal(department_id, serviceCategory_name) {
            this.department_id = department_id
            this.serviceCategoryName = serviceCategory_name
            this.$refs['my-modal'].show()
        },
        hideModal() {
            this.$refs['my-modal'].hide()
        },
        remove() {
            API({
                method: 'DELETE',
                url: 'departments/' + this.department_id
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
                                title: "Areas",
                                text: "El Area no puede ser eliminado, por que está siendo utilizado por otros registros"
                            })
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: "Areas",
                                text: this.$t('servicecategories.error.messages.requirement_delete')
                            })
                        }
                    }
                }).catch(() => {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: "Areas",
                    text: this.$t('servicecategories.error.messages.connection_error')
                })
            })
        },
        onUpdate() {
            this.urlServiceCagories = '/api/departments?token=' + window.localStorage.getItem('access_token') + '&lang=' +
                localStorage.getItem('lang')
            this.$refs.table.$refs.tableserver.refresh()
        },
    }

}
</script>

<style lang="stylus">

</style>
