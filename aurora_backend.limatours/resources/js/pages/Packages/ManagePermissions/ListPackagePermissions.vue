<template>
    <div class="container-fluid">

        <div class="row col-12 no-margin">
            <div
                :class="{ 'col-8' : ( ( generals && exclusive ) || ( !generals && !exclusive ) ), 'col-10' : ( ( !generals && exclusive ) || ( generals && !exclusive ) ) }">
                <label for="search_packages">Filtros de búsqueda: </label>
                <input class="form-control" id="search_packages" type="search" v-model="query_packages"
                       value="" placeholder="Buscar por nombre o código">
            </div>
        </div>

        <table-server :columns="table.columns" :options="tableOptions" :url="urlPackages" class="text-center"
                      ref="table" theme="bootstrap4">
            <div class="table-name text-left" slot="name" slot-scope="props" style="font-size: 0.9em">
                <font-awesome-icon :icon="['fas', 'heartbeat']" class="nav-icon"
                                   v-bind:style="{ color: props.row.physical_intensity.color}"/>
                <span v-if="props.row.code">
                    [{{ props.row.code }}] -
                </span>
                <span v-if="!(props.row.code)">
                    <span v-if="props.row.extension=='1'">[E{{ props.row.id }}] - </span>
                    <span v-if="props.row.extension=='0'">[P{{ props.row.id }}] - </span>
                    <span v-if="props.row.extension=='2'">[P{{ props.row.id }}] - </span>
                </span>
                <span v-html="getTranslationName(props.row)"></span>
            </div>
            <div class="table-name text-left" slot="users" slot-scope="props" style="font-size: 0.9em">
                <div class="btn-group btn-group-sm mr-1" role="group"
                     v-for="permission in (props.row.permissions || [])"
                     :key="permission.id || ('perm-' + Math.random())">
                    <button type="button" class="btn btn-secondary">
                        {{ (permission && permission.user && permission.user.name) ? permission.user.name : '—' }}
                    </button>
                    <button type="button" class="btn btn-primary" @click="remove(permission.id)" :disabled="!permission || !permission.id">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </div>
        </table-server>
        <!--        <b-modal :title="packageName" centered ref="my-modal" size="sm">-->
        <!--            <p class="text-center">{{$t('global.message_delete')}}</p>-->

        <!--            <div slot="modal-footer">-->
        <!--                <button @click="remove()" class="btn btn-success">{{$t('global.buttons.accept')}}</button>-->
        <!--                <button @click="hideModal()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>-->
        <!--            </div>-->
        <!--        </b-modal>-->
    </div>

</template>

<script>
import { API } from './../../../api'
import TableServer from '../../../components/TableServer'
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
            loading: false,
            packages: [],
            type_packages: [],
            package_id: null,
            filter_by: 2,
            query_packages: '',
            physical_intensity_color: 'FFFFFF',
            packageName: '',
            urlPackages: '/api/package/permissions?token=' + window.localStorage.getItem('access_token') + '&lang=' +
                localStorage.getItem('lang') + '&filterBy=2' +
                '&filter_exclusive=true&filter_generals=true',
            table: {
                columns: ['id', 'name', 'users'],
            },
            options: [
                {
                    type: 'edit',
                    link: 'packages/edit/',
                    icon: 'dot-circle'
                }
            ],
            generals: false,
            exclusive: false,
            g_exclusive: {
                name: 'Exclusivas',
                check: true
            },
            g_generals: {
                name: 'Generales',
                check: true
            }
        }
    },
    mounted () {
        this.exclusive = this.$can('exclusive', 'packages')
        this.generals = this.$can('generals', 'packages')

        this.$i18n.locale = localStorage.getItem('lang')
        this.$root.$emit('updateTitlePackage', { tab: 1 })
        this.type_packages.push({
            name: this.$i18n.t('packages.packages'),
            check: true,
            _class: ''
        }, {
            name: this.$i18n.t('packages.extensions'),
            check: true,
            _class: 'trExtension'
        })

        let search_packages = document.getElementById('search_packages')
        let timeout_extensions
        search_packages.addEventListener('keydown', () => {
            clearTimeout(timeout_extensions)
            timeout_extensions = setTimeout(() => {
                this.onUpdate()
                clearTimeout(timeout_extensions)
            }, 1000)
        })

    },
    created () {
        this.$parent.$parent.$on('langChange', (payload) => {
        })
        localStorage.setItem('packagenamemanage', '')
    },
    computed: {
        tableOptions: function () {
            return {
                headings: {
                    id: 'ID',
                    name: this.$i18n.t('packages.package'),
                    users: 'Usuarios',
                },
                sortable: ['id'],
                filterable: false,
                rowClassCallback: function (item) {
                    if (!item) return
                    if (parseInt(item.extension) === 1) {
                        return 'trExtension'
                    }
                    if (parseInt(item.extension) === 2) {
                        return 'trExclusive'
                    }
                }
            }
        }
    },
    methods: {
        getTranslationName (row) {
            const t0 = row && row.translations && row.translations[0]
            return (t0 && t0.name) ? t0.name : ''
        },
        filterByGroup (me) {
            me.check = !(me.check)
            if (!(this.g_exclusive.check) && !(this.g_generals.check)) {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: 'Error',
                    text: 'Debe elegir al menos un grupo'
                })
                me.check = !(me.check)
                return
            }
            this.onUpdate()
        },
        viewBy (index) {
            this.type_packages[index].check = !(this.type_packages[index].check)

            if (this.type_packages[0].check && this.type_packages[1].check) {
                this.filter_by = 2
                this.onUpdate()
            } else {
                this.filter_by = -1
                this.type_packages.forEach((t_p, t_p_key) => {
                    if (t_p.check) {
                        this.filter_by = t_p_key
                    }
                })

                if (this.filter_by == -1) {
                    this.type_packages[index].check = !(this.type_packages[index].check)
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Error',
                        text: this.$t('packages.error.messages.select_type_package')
                    })
                } else {
                    this.onUpdate()
                }
            }

        },
        showModal (p) {
            this.package_id = p.id
            if (p.code) {
                this.packageName = p.code
            } else {
                let pre_name = 'P'
                if (p.extension == '1') {
                    pre_name = 'E'
                }
                this.packageName = pre_name + p.id
            }
            this.packageName = 'Paquete: ' + this.packageName
            this.$refs['my-modal'].show()
        },
        hideModal () {
            this.$refs['my-modal'].hide()
        },
        onUpdate () {
            this.urlPackages = '/api/package/permissions?token=' + window.localStorage.getItem('access_token') + '&lang=' +
                localStorage.getItem('lang') + '&filterBy=' + this.filter_by + '&queryCustom=' + this.query_packages +
                '&filter_exclusive=' + this.g_exclusive.check + '&filter_generals=' + this.g_generals.check
            this.$refs.table.$refs.tableserver.refresh()
        },
        remove (id) {
            API({
                method: 'DELETE',
                url: 'package/permissions/' + id
            }).then((result) => {
                console.log(result.data.success)
                if (result.data.success == true) {
                    this.onUpdate()
                    this.hideModal()
                } else {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Error',
                        text: this.$t('packages.error.messages.package_delete')
                    })
                }
            }).catch(() => {

            })
        },
        getNamePackage (code, name, extension) {
            var nameComplete = ''
            if (name !== null) {
                nameComplete = code + ' - ' + name
            } else {
                nameComplete = code
            }
            localStorage.setItem('packagenamemanage', nameComplete)
            localStorage.setItem('package_extension', extension)
            this.$root.$emit('updateTitlePackage', { tab: 1 })
        },
    }
}
</script>

<style lang="stylus">
.table-actions {
    display: flex;
}

.trExtension, .trExtension > th, .trExtension > td {
    background-color: #e9eaff;
}

.trExtension:hover, .trExtension:hover > th, .trExtension:hover > td {
    background-color: #e2e3ff;
}

.trExclusive, .trExclusive > th, .trExclusive > td {
    background-color: #fff9e2;
}

.trExclusive:hover, .trExclusive:hover > th, .trExclusive:hover > td {
    background-color: #fff9e2;
}

.VueTables__limit {
    display: none;
}

.no-margin {
    padding-left: 0;
    padding-bottom: 5px !important;
    padding-right: 0px;
}
</style>
