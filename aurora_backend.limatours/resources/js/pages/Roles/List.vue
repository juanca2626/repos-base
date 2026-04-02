<template>
    <div>
        <!-- Toolbar superior -->
        <div class="mb-3 d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Roles</h4>
            <div>
                <button class="btn btn-success" @click="exportAllUsers" :disabled="exporting">
                    <span v-if="!exporting">Exportar roles con usuarios</span>
                    <span v-else>Generando...</span>
                </button>
                <button class="btn btn-success" @click="exportAll" :disabled="exporting">
                    <span v-if="!exporting">Exportar roles con permisos</span>
                    <span v-else>Generando...</span>
                </button>
            </div>
        </div>

        <table-client
            :columns="table.columns"
            :data="roles"
            :loading="loading"
            :options="tableOptions"
            id="dataTable"
            theme="bootstrap4"
        >
            <div class="table-actions" slot="actions" slot-scope="props">
                <menu-edit
                    :id="props.row.id"
                    :name="props.row.name"
                    :options="menuOptions"
                    @remove="remove(props.row.id)"
                    @export-role="exportRole(props.row)"
                />
            </div>

            <div class="table-state" slot="status" slot-scope="props" style="font-size: 0.9em">
                <b-form-checkbox
                    :checked="checkboxChecked(props.row.status)"
                    :id="'checkbox_'+props.row.id"
                    :name="'checkbox_'+props.row.id"
                    @change="changeState(props.row.id,props.row.status)"
                    switch
                />
            </div>
        </table-client>
    </div>
</template>


<script>
import { API } from './../../api'
import TableClient from './../../components/TableClient'
import MenuEdit from './../../components/MenuEdit'

export default {
    components: {
        'table-client': TableClient,
        'menu-edit': MenuEdit
    },
    data: () => ({
        loading: false,
        exporting: false,
        roles: [],
        table: { columns: ['actions', 'id', 'name', 'slug', 'status'] }
    }),
    computed: {
        menuOptions () {
            const options = []
            if (this.$can('update', 'roles')) {
                options.push({ type: 'edit', text: '', link: 'roles/edit/', icon: 'dot-circle', type_action: 'link' })
            }
            if (this.$can('delete', 'roles')) {
                options.push({
                    type: 'delete',
                    text: '',
                    link: '',
                    icon: 'trash',
                    type_action: 'button',
                    callback_delete: 'remove'
                })
            }
            if (this.$can('read', 'permissions')) {
                options.push({
                    type: '',
                    text: this.$i18n.t('roles.permissions.title'),
                    link: 'roles/addPermissions/',
                    icon: 'plus',
                    type_action: 'link'
                })
            }
            // puedes quitar este si ya no lo usas
            if (this.$can('read', 'permissions')) {
                options.push({
                    type: '',
                    text: 'Exportar usuarios',
                    link: 'roles/getUsers/',
                    icon: 'dot-circle',
                    type_action: 'link'
                })
            }

            if (this.$can('read', 'permissions')) {
                options.push({
                    type: '',
                    text: 'Exportar permisos',
                    icon: 'file-excel',       // usa el icon que tengas disponible
                    type_action: 'button',
                    callback: 'export-role',  // nombre del evento que emitirá MenuEdit
                    confirm: false            // <<< SIN modal, dispara directo
                })
            }
            return options
        },
        tableOptions () {
            return {
                headings: {
                    id: 'ID',
                    name: this.$i18n.t('global.name'),
                    slug: 'Slug',
                    description: this.$i18n.t('roles.description'),
                    level: 'Level',
                    actions: this.$i18n.t('global.table.actions')
                },
                sortable: ['id', 'name'],
                filterable: ['id', 'name'],
            }
        }
    },
    mounted () {
        this.fetchData()
    },
    methods: {
        checkboxChecked (status) { return !!status },
        changeState (user_id, status) {
            API({ method: 'put', url: 'role/' + user_id + '/status', data: { status } })
                .then((result) => {
                    if (result.data.success === true) this.fetchData()
                    else this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.clients'),
                        text: this.$t('clients.error.messages.information_error')
                    })
                })
        },
        fetchData () {
            API.get('roles').then((result) => {
                if (result.data.success === true) this.roles = result.data.data
                else this.$notify({ group: 'main', type: 'error', title: 'Fetch Error', text: result.data.message })
            })
        },
        remove (id) {
            API({ method: 'DELETE', url: 'roles/' + id })
                .then((result) => {
                    if (result.data.success === true) this.fetchData()
                    else this.$notify({ group: 'main', type: 'error', title: 'Roles', text: result.data.message })
                })
                .catch((result) => {
                    this.$notify({ group: 'main', type: 'error', title: 'Roles', text: result.data.message })
                })
        },

        // ---- Exportar Excel ----
        async exportAll () {
            try {
                this.exporting = true
                // Opción A: tu API responde con { success, url }
                let res = await API.get('reports/roles-permissions')
                if (res && res.data && res.data.success && res.data.url) {
                    window.open(res.data.url, '_blank')
                    return
                }
                // Opción B: tu API devuelve el archivo (stream)
                res = await API({ url: 'reports/roles-permissions', method: 'GET', responseType: 'blob' })
                this.downloadBlob(res.data, `roles_permissions_${this.timestamp()}.xlsx`)
            } catch (e) {
                this.$notify({ group: 'main', type: 'error', title: 'Export', text: 'No se pudo generar el Excel' })
            } finally {
                this.exporting = false
            }
        },

        async exportAllUsers () {
            try {
                this.exporting = true
                // Descarga directa (stream)
                const res = await API({ url: 'reports/roles-users', method: 'GET', responseType: 'blob' })
                this.downloadBlob(res.data, `roles_users_${this.timestamp()}.xlsx`)
            } catch (e) {
                this.$notify({ group: 'main', type: 'error', title: 'Export', text: 'No se pudo generar el Excel (usuarios por rol)' })
            } finally {
                this.exporting = false
            }
        },

        async exportRole (row) {
            try {
                this.exporting = true
                const endpoint = `reports/roles-permissions/${row.id}`

                // A) API devuelve { success, url }
                let res = await API.get(endpoint)
                if (res && res.data && res.data.success && res.data.url) {
                    window.open(res.data.url, '_blank')
                    return
                }
                // B) Stream
                res = await API({ url: endpoint, method: 'GET', responseType: 'blob' })
                this.downloadBlob(res.data, `role_${row.slug || row.id}_${this.timestamp()}.xlsx`)
            } catch (e) {
                this.$notify({ group: 'main', type: 'error', title: 'Export', text: 'No se pudo generar el Excel del rol' })
            } finally {
                this.exporting = false
            }
        },


        downloadBlob (blob, filename) {
            const url = window.URL.createObjectURL(new Blob([blob]))
            const link = document.createElement('a')
            link.href = url
            link.setAttribute('download', filename)
            document.body.appendChild(link)
            link.click()
            link.remove()
            window.URL.revokeObjectURL(url)
        },

        timestamp () {
            const d = new Date()
            const pad = n => (n < 10 ? `0${n}` : n)
            return `${d.getFullYear()}${pad(d.getMonth() + 1)}${pad(d.getDate())}_${pad(d.getHours())}${pad(d.getMinutes())}${pad(d.getSeconds())}`
        }
    }
}
</script>


<style>
.table-actions {
    display: flex;
    align-items: center;
}

.table-actions .btn + .btn {
    margin-left: .5rem;
}
</style>

