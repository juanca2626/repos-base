<template>
    <div class="row">
        <div class="col-sm-12 mb-0 d-flex align-items-center justify-content-between">
            <h2 class="mb-0">Rol: {{ role.name }}</h2>
        </div>
        <div class="col-md-12">
            <hr class="my-4 mb-0 mt-0" />
        </div>
        <div class="col-sm-6 mb-2 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <input v-model.trim="search" type="text" class="form-control form-control mr-2"
                       :placeholder="'Buscar permisos…'">
                <button class="btn btn-outline-dark mr-2" @click="expandAll">Expandir todo</button>
                <button class="btn btn-outline-dark" @click="collapseAll">Colapsar todo</button>
            </div>
        </div>

        <div class="col-sm-6 mb-2 text-right">
            <button class="btn btn-success ml-2" @click="changeState(true)">Marcar todo</button>
            <button class="btn btn-outline-secondary ml-2" @click="changeState(false)">Desmarcar  todo</button>
        </div>

        <div class="col-md-12">
            <hr class="my-4 mb-0 mt-0" />
        </div>
        <!-- Secciones por MÓDULO (colapsables) -->
        <div class="col-sm-12" v-for="section in filteredSections" :key="section.module">
            <div class="module-header" @click="toggleModule(section.module)">
                <div class="left">
                    <span class="chevron" :class="{open: isOpen(section.module)}">▸</span>
                    <strong>{{ section.module }}</strong>
                </div>
                <div class="right">
                    <small class="text-muted mr-3">
                        {{ selectedCount(section) }} / {{ totalCount(section) }} seleccionados
                    </small>
                    <button class="btn btn-sm btn-outline-primary"
                            @click.stop="toggleAllInModule(section, true)">Marcar todo</button>
                    <button class="btn btn-sm btn-outline-secondary ml-2"
                            @click.stop="toggleAllInModule(section, false)">Desmarcar</button>
                </div>
            </div>

            <transition name="collapse">
                <div v-show="isOpen(section.module)" class="module-body">
                    <div class="card-columns">
                        <!-- Tarjeta por GRUPO -->
                        <div class="card" v-for="group in filteredGroups(section)" :key="section.module + '::' + group.id">
                            <div class="card-header">
                                {{ group.description || group.name }}
                                <br>
                                <small class="text-muted">{{ group.name }}</small>
                            </div>
                            <div class="card-body">
                                <!-- ACCIONES -->
                                <div class="row align-items-center"
                                     v-for="action in filteredActions(group)" :key="action.id">
                                    <div class="col-8">
                                        {{ highlight(action.name) }}
                                        <small v-if="action.description" class="text-muted d-block">
                                            {{ action.description }}
                                        </small>
                                    </div>
                                    <div class="col-4 text-right">
                                        <c-switch class="mx-1" color="primary" variant="pill"
                                                  v-model="form.permissions[action.id]" />
                                    </div>
                                </div>
                            </div>
                        </div><!-- /card -->
                    </div><!-- /card-columns -->
                </div>
            </transition>

            <hr class="my-4" />
        </div>

        <div class="col-sm-6">
            <div slot="footer">
                <img src="/images/loading.svg" v-if="loading" width="40" />
                <button @click="submit" class="btn btn-success" type="submit" v-if="!loading">
                    <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                    {{$t('global.buttons.save')}}
                </button>
                <router-link :to="{ name: 'RolesList' }" v-if="!loading">
                    <button class="btn btn-danger" type="reset">
                        {{$t('global.buttons.cancel')}}
                    </button>
                </router-link>
            </div>
        </div>
    </div>
</template>

<script>
import { API } from './../../api'
import { Switch as cSwitch } from '@coreui/vue'

export default {
    components: { cSwitch },
    data: () => ({
        loading: false,
        permissions: [], // [{ module, permissions: [{ id, name, description, data: [...] }] }]
        role: { name: '' },
        form: {
            permissions: {} // { [id:number]: boolean }
        },
        openModules: {},  // { [moduleName]: boolean }
        search: ''
    }),
    mounted () {
        this.fetchData()
    },
    computed: {
        filteredSections () {
            // Si no hay búsqueda, retorna tal cual para performance
            if (!this.search) return this.permissions
            const q = this.search.toLowerCase()
            return this.permissions
                .map(section => {
                    // filtrar grupos/acciones dentro del módulo
                    const filteredGroups = (section.permissions || []).map(g => {
                        const actions = (g.data || []).filter(a =>
                            (a.name || '').toLowerCase().includes(q) ||
                            (g.name || '').toLowerCase().includes(q) ||
                            (g.description || '').toLowerCase().includes(q)
                        )
                        return { ...g, data: actions }
                    }).filter(g => g.data && g.data.length > 0 || (g.name || '').toLowerCase().includes(q))

                    if (filteredGroups.length === 0 &&
                        !section.module.toLowerCase().includes(q)) return null

                    return { module: section.module, permissions: filteredGroups.length ? filteredGroups : section.permissions }
                })
                .filter(Boolean)
        }
    },
    methods: {
        isOpen (moduleName) {
            return this.openModules[moduleName] !== false // por defecto abierto
        },
        toggleModule (moduleName) {
            this.$set(this.openModules, moduleName, !this.isOpen(moduleName))
        },
        expandAll () {
            const state = {}
            this.permissions.forEach(s => { state[s.module] = true })
            this.openModules = state
        },
        collapseAll () {
            const state = {}
            this.permissions.forEach(s => { state[s.module] = false })
            this.openModules = state
        },
        filteredGroups (section) {
            // cuando no hay búsqueda, devolvemos grupos completos
            if (!this.search) return section.permissions
            const q = this.search.toLowerCase()
            return (section.permissions || []).map(g => {
                const actions = (g.data || []).filter(a =>
                    (a.name || '').toLowerCase().includes(q) ||
                    (g.name || '').toLowerCase().includes(q) ||
                    (g.description || '').toLowerCase().includes(q)
                )
                return { ...g, data: actions }
            }).filter(g => g.data && g.data.length > 0 || (g.name || '').toLowerCase().includes(q))
        },
        filteredActions (group) {
            if (!this.search) return group.data || []
            const q = this.search.toLowerCase()
            return (group.data || []).filter(a =>
                (a.name || '').toLowerCase().includes(q) ||
                (a.description || '').toLowerCase().includes(q)
            )
        },
        selectedCount (section) {
            let count = 0
            this.filteredGroups(section).forEach(g => {
                this.filteredActions(g).forEach(a => { if (this.form.permissions[a.id]) count++ })
            })
            return count
        },
        totalCount (section) {
            let count = 0
            this.filteredGroups(section).forEach(g => {
                count += this.filteredActions(g).length
            })
            return count
        },
        toggleAllInModule (section, value) {
            this.filteredGroups(section).forEach(g => {
                this.filteredActions(g).forEach(a => {
                    this.$set(this.form.permissions, a.id, !!value)
                })
            })
        },
        changeState (value) {
            const v = !!value
            // opción A (rápida y reactiva con Vue 2):
            Object.keys(this.form.permissions).forEach(id => {
                this.$set(this.form.permissions, id, v)
            })
        },
        buildPermissionIndex (sections) {
            const index = {}
            sections.forEach(section => {
                (section.permissions || []).forEach(group => {
                    (group.data || []).forEach(action => {
                        index[action.id] = false // default
                    })
                })
            })
            return index
        },
        async fetchData () {
            try {
                // 1) Árbol
                const tree = await API.get('permissions/treeView')
                if (tree.data.success !== true) throw new Error(tree.data.message || 'Tree fetch error')
                this.permissions = tree.data.data || []

                // 2) Índice base con todos los IDs visibles del árbol (en false)
                const freshIndex = this.buildPermissionIndex(this.permissions)

                // 3) Rol
                const roleRes = await API.get('roles/' + this.$route.params.id)
                if (roleRes.data.success !== true) throw new Error(roleRes.data.message || 'Role fetch error')
                this.role = roleRes.data.data

                // 4) Permisos del rol (objeto { "1": true, "2": false, ... })
                const rp = await API.get('permissions/fromRole/' + this.$route.params.id)
                if (rp.data.success !== true) throw new Error(rp.data.message || 'Role perms fetch error')
                const remote = rp.data.data || {}

                // 5) Match por ID: sólo seteamos los IDs que existen en el árbol
                const merged = { ...freshIndex }
                Object.keys(remote).forEach(id => {
                    if (merged.hasOwnProperty(id)) merged[id] = !!remote[id]
                })
                this.form.permissions = merged
                console.log(this.form.permissions)

            } catch (err) {
                this.$notify({ group: 'main', type: 'error', title: 'Fetch Error', text: err.message || 'Error' })
            }
        },
        async submit () {
            this.loading = true
            try {
                const res = await API.post('roles/' + this.$route.params.id + '/permissions', this.form)
                if (res.data.success === true) {
                    this.$router.push('/roles/list')
                    this.$root.$emit('roles_permissions_update')
                } else {
                    throw new Error(res.data.message || 'Submit error')
                }
            } catch (e) {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: 'Fetch Error',
                    text: 'Error al procesar, intente de nuevo por favor'
                })
            } finally {
                this.loading = false
            }
        },
        // resalta coincidencia (simple): puedes mejorarlo si quieres
        highlight (text) {
            return text
        }
    }
}
</script>

<style>
.card-columns {
    column-count: 3;
    column-gap: 1rem;
}
@media (max-width: 1200px) {
    .card-columns {
        column-count: 2;
    }
}
@media (max-width: 768px) {
    .card-columns {
        column-count: 1;
    }
}

.btn-outline-dark{
    width: 200px;
}

.module-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: .75rem 1rem;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: .5rem;
    cursor: pointer;
    transition: background .2s ease;
}
.module-header:hover {
    background: #f1f3f5;
}
.module-header .left {
    display: flex;
    align-items: center;
}
.module-header .chevron {
    display: inline-block;
    transform: rotate(0deg);
    transition: transform .15s ease;
    margin-right: .5rem;
}
.module-header .chevron.open {
    transform: rotate(90deg);
}

.module-body {
    padding: 1rem 0 .5rem 0;
}

.collapse-enter-active,
.collapse-leave-active {
    transition: all .18s ease;
}
.collapse-enter,
.collapse-leave-to {
    opacity: 0;
    transform: translateY(-4px);
}
</style>
