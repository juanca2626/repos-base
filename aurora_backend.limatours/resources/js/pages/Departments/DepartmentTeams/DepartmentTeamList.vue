<template>
    <div class="container-fluid">
        <table-client :columns="table.columns" :data="teams"
                      :options="tableOptions" id="dataTable"
                      theme="bootstrap4">
            <div class="table-name" slot="name" slot-scope="props" style="font-size: 0.9em">
                {{ props.row.name }}
            </div>
            <div class="table-actions" slot="actions" slot-scope="props" style="padding: 5px;">
                <b-dropdown class="mt-2 ml-2 mb-0" dropleft size="sm">
                    <template slot="button-content">
                        <font-awesome-icon :icon="['fas', 'bars']" class="ml-1 p-0"/>
                    </template>
                    <router-link :to="'/'+ department_id +'/teams/edit/'+props.row.id"
                                 class="nav-link m-0 p-0">
                        <b-dropdown-item-button class="m-0 p-0" v-if="$can('update', 'departments')">
                            <font-awesome-icon :icon="['fas', 'edit']" class="m-0"/>
                            {{ $t('global.buttons.edit') }}
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
        </table-client>
        <b-modal :title="teamName" centered ref="my-modal" size="sm">
            <p class="text-center">{{ $t('global.message_delete') }}</p>
            <div slot="modal-footer">
                <button @click="remove()" class="btn btn-success">{{ $t('global.buttons.accept') }}</button>
                <button @click="hideModal()" class="btn btn-danger">{{ $t('global.buttons.cancel') }}</button>
            </div>
        </b-modal>
    </div>
</template>

<script>
import {API} from '../../../api'
import TableClient from '../../../components/TableClient'
import BFormCheckbox from 'bootstrap-vue/es/components/form-checkbox/form-checkbox'
import BDropDown from 'bootstrap-vue/es/components/dropdown/dropdown'
import BDropDownItemButton from 'bootstrap-vue/es/components/dropdown/dropdown-item-button'
import BModal from 'bootstrap-vue/es/components/modal/modal'

export default {
    components: {
        BFormCheckbox,
        'table-client': TableClient,
        'b-dropdown': BDropDown,
        'b-dropdown-item-button': BDropDownItemButton,
        BModal
    },
    data: () => {
        return {
            teams: [],
            teamName: '',
            urlTeams: '',
            department_id: '',
            table: {
                columns: ['id', 'name', 'actions'],
            }
        }
    },
    created() {
        this.getTeams()
    },
    computed: {
        tableOptions: function () {
            return {
                headings: {
                    id: 'ID',
                    name: this.$i18n.t('global.name'),
                    subtypes: this.$i18n.t('servicecategories.name_subtypes'),
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
        getTeams: function () {
            this.department_id = this.$route.params.department_id
            API.get('/departments/' + this.$route.params.department_id + '/teams?token=' + window.localStorage.getItem('access_token') + '&lang=' + localStorage.getItem('lang')).then((result) => {
                if (result.data.success === true) {
                    this.teams = result.data.data
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
        showModal(department_team_id, name) {
            this.department_team_id = department_team_id
            this.teamName = name
            this.$refs['my-modal'].show()
        },
        hideModal() {
            this.$refs['my-modal'].hide()
        },
        remove() {
            API({
                method: 'DELETE',
                url: 'department_team/' + this.department_team_id
            }).then((result) => {
                if (result.data.success === true) {
                    this.hideModal()
                    this.getTeams()
                } else {
                    this.hideModal()
                    if (result.data.used === true) {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: "Equipo",
                            text: "El Equipo no puede ser eliminado, por que está siendo utilizado por otros registros"
                        })
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: "Equipo",
                            text: this.$t('servicecategories.error.messages.requirement_delete')
                        })
                    }
                }
            }).catch(() => {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: "Equipo",
                    text: this.$t('servicecategories.error.messages.connection_error')
                })
            })
        }
    }

}
</script>

<style lang="stylus">

</style>
