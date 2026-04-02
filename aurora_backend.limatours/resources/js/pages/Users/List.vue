<template>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="text-success mr-3 d-inline-block">
                    <i class="fas fa-user-circle fa-2x"></i>
                    Usuarios activos <strong>({{ users_on_count }})</strong></div>
                <div class="text-secondary d-inline-block">
                    <i class="far fa-user-circle fa-2x"></i>
                    Usuarios inactivos <strong>({{ users_off_count }})</strong></div>
                <div class="text-secondary d-inline-block ml-2">
                    <button type="button" class="btn btn-success btn-sm" @click="fetchData" :disabled="loading">
                        <i class="fa fa-spin fa-spinner" v-if="loading"></i>
                        <i class="fas fa-sync-alt" v-else></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-row">
                    <label class="col-4 col-form-label" for="status">Usuarios activos:</label>
                    <div class="col-8">
                        <select v-model="user_active" name="status" id="status" class="form-control"
                                @change="fetchData">
                            <option value="">Todos</option>
                            <option value="1">Activos</option>
                            <option value="0">Inactivos</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-row">
                    <label class="col-2 col-form-label" for="status">Rol:</label>
                    <div class="col-10">
                        <select polaceholder="Elija un rol" class="form-control" id="rol" size="0" v-model="role" @change="fetchData">
                            <option value="">Todos</option>
                            <option value="bdm"><strong>* BDM</strong></option>
                            <option value="kam"><strong>* KAM</strong></option>
                            <option :value="rol.value" v-for="rol in roles">
                                {{ rol.text }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-row">
                    <label class="col-6 col-form-label" for="searchStatus">{{
                            $t('users.search.messages.user_code_name_search')
                        }}</label>
                    <div class="col-6">
                        <input :class="{'form-control':true }"
                               id="target" name="target"
                               type="text"
                               ref="auroraCodeName" v-model="target">

                    </div>
                </div>
            </div>
        </div>
        <table-client :columns="table.columns" :data="users" :loading="loading" :options="tableOptions" id="dataTable"
                      theme="bootstrap4">
            <div class="table-actions" slot="actions" slot-scope="props">
                <menu-edit :id="props.row.id" :name="props.row.name" :options="menuOptions"
                           @remove="remove(props.row.id)"/>
                <div class="ml-2 mt-1">
                    <i class="fas fa-user-circle text-success fa-2x" v-if="props.row.session_active"></i>
                    <i class="far fa-user-circle text-secondary fa-2x" v-else></i>
                </div>
            </div>
            <div class="table-types" slot="name" slot-scope="props" style="padding: 10px">
                {{ props.row.name }}<br>
                <div v-if="props.row.employee != null">
                    <div v-if="props.row.employee.team != null">
                        <span class="badge badge-success">
                            Area / Equipo:  {{ props.row.employee.team.department.name }} / {{ props.row.employee.team.name }}
                        </span>
                    </div>
                    <div v-if="props.row.employee.position != null">
                        <span class="badge badge-warning">
                            Cargo:  {{ props.row.employee.position.name }}
                        </span>
                    </div>
                </div>

            </div>
            <div class="table-roles" slot="roles" slot-scope="props">
                <span v-if="props.row.roles.length > 0">{{ props.row.roles[0].name }}</span>
                <span v-else> - </span>
            </div>
            <div class="table-types" slot="type" slot-scope="props">
                {{ props.row.user_type.description }}<br>
                <div v-if="props.row.employee != null">
                    <div v-if="props.row.employee.is_kam">
                        <span class="badge badge-info">
                            KAM
                        </span>
                    </div>
                    <div v-if="props.row.employee.is_bdm">
                        <span class="badge badge-info">
                            BDM
                        </span>
                    </div>
                </div>
            </div>
            <div class="table-loading text-center" slot="loading">
                <img alt="loading" height="51px" src="/images/loading.svg"/>
            </div>

            <div class="table-state" slot="status" slot-scope="props" style="font-size: 0.9em">
                <b-form-checkbox
                    :checked="checkboxChecked(props.row.status)"
                    :id="'checkbox_'+props.row.id"
                    :name="'checkbox_'+props.row.id"
                    @change="changeState(props.row.id,props.row.status)"
                    switch>
                </b-form-checkbox>
            </div>

            <div class="table-state" slot="locked_at" slot-scope="props" style="font-size: 0.9em">
                <template v-if="props.row.locked_at">
                    <b-form-checkbox
                        :checked="checkboxChecked(props.row.locked_at)"
                        :id="'checkbox_locked_'+props.row.id"
                        :name="'checkbox_locked_'+props.row.id"
                        @change="unlock(props.row.id)"
                        switch>
                    </b-form-checkbox>
                </template>
            </div>


        </table-client>
    </div>
</template>

<script>
import {API} from './../../api'
import TableClient from './../../components/TableClient'
import MenuEdit from './../../components/MenuEdit'

export default {
    components: {
        'table-client': TableClient,
        'menu-edit': MenuEdit
    },
    watch: {
        target() {
            this.fetchData()
        }
    },
    data: () => {
        return {
            loading: false,
            users: [],
            searchUserType: '',
            target: '',
            table: {
                columns: ['actions', 'id', 'code', 'name', 'email', 'roles', 'type', 'status', 'locked_at'],
            },
            userTypes: [],
            users_on_count: 0,
            users_off_count: 0,
            user_active: '',
            roles: [],
            role: '',
        }
    },
    computed: {
        menuOptions: function () {

            let options = []

            if (this.$can('update', 'users')) {
                options.push({
                    type: 'edit',
                    text: '',
                    link: 'users/edit/',
                    icon: 'dot-circle',
                    callback: '',
                    type_action: 'link'
                });
            }
            if (this.$can('delete', 'users')) {
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
                    code: 'Code',
                    name: this.$i18n.t('global.name'),
                    email: this.$i18n.t('users.mail'),
                    roles: this.$i18n.t('users.roles'),
                    type: this.$i18n.t('users.user_type'),
                    actions: this.$i18n.t('users.actions'),
                    status: this.$i18n.t('global.status'),
                },
                // 'id'
                sortable: [],
                filterable: [],
                perPageValues: []
                // 'id', 'name'
            }
        }
    },
    mounted() {
        this.$i18n.locale = localStorage.getItem('lang')
        this.fetchData()
        this.getUserTypes()

        API.get('/roles/selectBox')
            .then((result) => {
                this.roles = result.data.data
            })

    },
    methods: {
        search: function () {
            this.fetchData()
        },
        checkboxChecked: function (status) {
            return !!status
        },
        changeState: function (user_id, status) {
            API({
                method: 'put',
                url: 'user/' + user_id + '/status',
                data: {status: status}
            })
                .then((result) => {
                    if (result.data.success === true) {
                        this.fetchData()
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.clients'),
                            text: this.$t('clients.error.messages.information_error')
                        })
                    }
                })
        },
        unlock(id) {
            console.log("UNLOCK: ", id);

            API({
                method: 'POST',
                url: 'users/unlock/' + id
            })
                .then((result) => {
                    if (result.data.success === true) {
                        this.fetchData()
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$i18n.t('users.title'),
                            text: result.data.message
                        })

                    }
                })
                .catch((e) => {

                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$i18n.t('users.title'),
                        text: e.data.message
                    })
                })
        },
        remove(id) {
            API({
                method: 'DELETE',
                url: 'users/' + id
            })
                .then((result) => {
                    if (result.data.success === true) {
                        this.fetchData()
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$i18n.t('users.title'),
                            text: result.data.message
                        })

                    }
                })
                .catch((e) => {

                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$i18n.t('users.title'),
                        text: e.data.message
                    })
                })
        },
        getUserTypes: function () {
            API.get('/usertypes/selectBox')
                .then((result) => {
                    this.userTypes = result.data.data
                })
        },
        fetchData: function () {
            this.loading = true
            API.get('users?search=' + this.target + '&typeUser=' + this.searchUserType
                + '&user_actives=' + this.user_active + '&role=' + this.role )
                .then((result) => {
                    this.loading = false
                    if (result.data.success === true) {
                        this.users = result.data.data
                        this.users_on_count = result.data.users_on_count
                        this.users_off_count = result.data.users_off_count
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Fetch Error',
                            text: result.data.message
                        })
                    }
                })
        }

    }
}
</script>


