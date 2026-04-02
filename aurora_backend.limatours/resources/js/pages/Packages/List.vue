<template>
    <div class="container-fluid">

        <div class="row col-12 no-margin">
            <div class="col-sm-3">
                <label class="col-form-label" for="search_packages">Filtros de búsqueda: </label>
                <input class="form-control" id="search_packages" type="search" v-model="query_packages"
                       value="" placeholder="Buscar por nombre o código">
            </div>
            <div class="col-sm-3">
                <label class="col-form-label">{{ $t('packages.search.label.interest_search') }}</label>
                <v-select
                    @input="handleGroupChange"
                    :options="groups"
                    v-model="filters.group"
                    :placeholder="this.$t('packages.search.messages.interest_search')"
                    autocomplete="true"></v-select>
            </div>
            <div class="col-sm-3">
                <label class="col-form-label">{{ $t('packages.search.label.categories_search') }}</label>
                <v-select
                    :options="tags"
                    v-model="filters.tag"
                    :placeholder="this.$t('packages.search.messages.categories_search')"
                    autocomplete="true"></v-select>
            </div>
            <div class="col-sm-3">
                <label class="col-form-label">{{ $t('packages.search.label.type_package_search') }}</label>
                <v-select
                    :options="type_packages"
                    v-model="filters.type_package"
                    :placeholder="this.$t('packages.search.messages.type_package_search')"
                    autocomplete="true"></v-select>
            </div>
        </div>

        <table-server :columns="table.columns" :options="tableOptions" :url="urlPackages" class="text-center"
                      ref="table" theme="bootstrap4">
            <div class="table-country" slot="country" slot-scope="props" style="font-size: 0.9em">
                {{props.row.country.translations[0].value}}
            </div>
            <div class="table-group" slot="group" slot-scope="props" style="font-size: 0.9em">
                <span v-if="props.row.tag !== null">{{ props.row.tag.tag_group.translations[0].value.toUpperCase() }}</span>
                <span v-else> - </span>
            </div>
            <div class="table-category" slot="category" slot-scope="props" style="font-size: 0.9em">
                <span v-if="props.row.tag !== null">{{props.row.tag.translations[0].value}}</span>
                <span v-else> - </span>
            </div>
            <div class="table-name text-left" slot="name" slot-scope="props" style="font-size: 0.9em">
                <font-awesome-icon :icon="['fas', 'heartbeat']" class="nav-icon"
                                   v-bind:style="{ color: props.row.physical_intensity.color}" />
                <span v-if="props.row.code">
                    [{{ props.row.code }}] -
                </span>
                <span v-if="!(props.row.code)">
                    <span v-if="props.row.extension=='1'">[E{{ props.row.id }}] - </span>
                    <span v-if="props.row.extension=='0'">[P{{ props.row.id }}] - </span>
                    <span v-if="props.row.extension=='2'">[P{{ props.row.id }}] - </span>
                </span>
                <span v-html="props.row.translations[0].name"></span>
            </div>
            <div class="table-allow_modify" slot="allow_modify" slot-scope="props" style="font-size: 0.9em;">
                <b-form-checkbox
                    :disabled="!props.row.allow_edit"
                    :checked="checkboxCheckedModify(props.row.allow_modify)"
                    :id="'checkbox_modify_'+props.row.id"
                    :name="'checkbox_modify_'+props.row.id"
                    @change="changeModify(props.row.id,props.row.allow_modify)"
                    switch>
                </b-form-checkbox>
            </div>
            <div class="table-status" slot="status" slot-scope="props" style="font-size: 0.9em;">
                <b-form-checkbox
                    :disabled="!props.row.allow_edit"
                    :checked="checkboxChecked(props.row.status)"
                    :id="'checkbox_'+props.row.id"
                    :name="'checkbox_'+props.row.id"
                    @change="changeState(props.row.id,props.row.status)"
                    switch>
                </b-form-checkbox>
            </div>
            <div class="table-status" slot="recommended" slot-scope="props" style="font-size: 0.9em;">
                <b-form-checkbox
                    :disabled="!props.row.allow_edit"
                    :checked="checkboxRecommended(props.row.recommended)"
                    :id="'checkbox_recommended_'+props.row.id"
                    :name="'checkbox_recommended_'+props.row.id"
                    @change="changeRecommended(props.row.id,props.row.recommended)"
                    switch>
                </b-form-checkbox>
            </div>
            <div class="table-status" slot="free_sale" slot-scope="props" style="font-size: 0.9em;">
                <b-form-checkbox
                    :disabled="!props.row.allow_edit"
                    :checked="checkboxFreeSale(props.row.free_sale)"
                    :id="'checkbox_free_sale_'+props.row.id"
                    :name="'checkbox_free_sale_'+props.row.id"
                    @change="changeFreeSale(props.row.id,props.row.free_sale)"
                    switch>
                </b-form-checkbox>
            </div>
            <div class="table-actions" slot="actions" slot-scope="props" style="padding: 5px;">
                <b-dropdown class="mt-2 ml-2 mb-0" dropleft size="sm" :disabled="!props.row.allow_edit" :title="(!props.row.allow_edit) ? 'No tiene permisos en este paquete': 'Acciones'">
                    <template slot="button-content">
                        <font-awesome-icon :icon="['fas', 'bars']" class="ml-1 p-0" />
                    </template>
                    <router-link :to="'/packages/edit/'+props.row.id"
                                 @click.native="getNamePackage(props.row.id,props.row.translations[0].name,props.row.extension)"
                                 class="nav-link m-0 p-0">
                        <b-dropdown-item-button class="m-0 p-0" v-if="$can('update', 'packages')">
                            <font-awesome-icon :icon="['fas', 'dot-circle']" class="m-0" />
                            {{$t('packages.general_data')}}
                        </b-dropdown-item-button>
                    </router-link>

                    <router-link :to="'/packages/'+props.row.id+'/manage_package'"
                                 @click.native="getNamePackage(props.row.id,props.row.translations[0].name,props.row.extension)"
                                 class="nav-link m-0 p-0">
                        <b-dropdown-item-button class="m-0 p-0">
                            <font-awesome-icon :icon="['fas', 'dot-circle']" class="m-0" />
                            {{$t('packages.manage_package')}}
                        </b-dropdown-item-button>
                    </router-link>
                    <router-link :to="'/packages/'+props.row.id+'/quotes'"
                                 @click.native="getNamePackage(props.row.id,props.row.translations[0].name,props.row.extension)"
                                 class="nav-link m-0 p-0"
                                 v-if="!props.row.is_processing_plan_rates">
                        <b-dropdown-item-button class="m-0 p-0">
                            <font-awesome-icon :icon="['fas', 'dot-circle']" class="m-0" />
                            {{$t('packages.quotes')}}
                        </b-dropdown-item-button>
                    </router-link>
                    <!--  v-if="$can('create', 'packages') && !props.row.is_processing_plan_rates" -->
                    <b-dropdown-item-button @click="showDuplicationModal(props.row)" class="m-0 p-0"
                            v-if="!props.row.is_processing_plan_rates">
                        <font-awesome-icon :icon="['fas', 'copy']" class="m-0" />
                        {{$t('global.buttons.duplicate')}}
                    </b-dropdown-item-button>
                    <b-dropdown-item-button @click="showModal(props.row)" class="m-0 p-0"
                                            v-if="$can('delete', 'packages') && !props.row.is_processing_plan_rates">
                        <font-awesome-icon :icon="['fas', 'trash']" class="m-0" />
                        {{$t('global.buttons.delete')}}
                    </b-dropdown-item-button>
                    <b-dropdown-item-button class="m-0 p-0"
                                            v-if="props.row.is_processing_plan_rates">
                        <i class="fas fa-spinner fa-spin m-0"></i>
                        {{$t('packages.processing_plan_rates')}}
                    </b-dropdown-item-button>
                </b-dropdown>
            </div>
        </table-server>
        <b-modal :title="packageName" centered ref="my-modal" size="sm">
            <p class="text-center">{{$t('global.message_delete')}}</p>

            <div slot="modal-footer">
                <button @click="remove()" class="btn btn-success">{{$t('global.buttons.accept')}}</button>
                <button @click="hideModal()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
            </div>
        </b-modal>
        <b-modal
            :title="packageName"
            centered
            ref="modal-duplicate"
            size="sm"
            :hide-header-close="loading"
            :no-close-on-backdrop="loading"
            :no-close-on-esc="loading"
        >
            <p class="text-center">{{$t('global.message_duplicate')}}. Puede tardar hasta 1 minuto</p>

            <div slot="modal-footer">
                <button @click="duplicate()" class="btn btn-success" :disabled="loading">
                    {{$t('global.buttons.accept')}}
                    <i class="fas fa-spinner fa-spin m-0" v-if="loading"></i>
                </button>
                <button @click="hideDuplicationModal()" class="btn btn-danger" :disabled="loading">{{$t('global.buttons.cancel')}}</button>
            </div>
        </b-modal>
    </div>

</template>

<script>
    import { API } from './../../api'
    import TableServer from '../../components/TableServer'
    import {BFormCheckbox, BModal, BDropdown, BDropdownItemButton} from 'bootstrap-vue'
    import vSelect from 'vue-select'

    export default {
        components: {
            BFormCheckbox,
            'table-server': TableServer,
            'b-dropdown': BDropdown,
            'b-dropdown-item-button': BDropdownItemButton,
            BModal,
            vSelect,
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
                urlPackages: '/api/packages?token=' + window.localStorage.getItem('access_token') +
                    '&lang=' + localStorage.getItem('lang'),
                table: {
                    columns: ['id', 'country', 'name', 'group', 'category', 'recommended','allow_modify','free_sale', 'status', 'actions'],
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
                },
                groups: [],
                tags: [],
                filters: {
                    group: '',
                    tag: '',
                    type_package: '',
                }
            }
        },
        mounted () {
            this.exclusive = this.$can('exclusive', 'packages')
            this.generals = this.$can('generals', 'packages')

            this.$i18n.locale = localStorage.getItem('lang')
            this.$root.$emit('updateTitlePackage', { tab: 1 })
            this.type_packages.push({
                code: 0,
                label: this.$i18n.t('packages.packages'),
            }, {
                code: 1,
                label: this.$i18n.t('packages.extension'),
            }, {
                code: 2,
                label: this.$i18n.t('packages.exclusive'),
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
            API.get('packages/groups?lang=' + localStorage.getItem('lang')).then(({data}) => {
                this.groups = data.data.map(group => ({
                    label: group.translations[0].value,
                    code: group.id,
                }));
            }).catch(console.error)
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
                        country: this.$i18n.t('global.types.country'),
                        name: this.$i18n.t('packages.package'),
                        group: this.$i18n.t('packages.interest'),
                        category: this.$i18n.t('packages.category'),
                        rate: this.$i18n.t('packages.rate'),
                        recommended: this.$i18n.t('package.package_recommended'),
                        allow_modify: this.$i18n.t('packages.allow_modify'),
                        free_sale: 'Free Sale',
                        status: this.$i18n.t('global.status'),
                        actions: this.$i18n.t('global.table.actions')
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
        watch: {
            filters: {
                handler: function() {
                    this.onUpdate();
                },
                deep: true,
            }
        },
        methods: {
            handleGroupChange(group) {
                this.filters.tag = null;
                this.updateTags(group);
            },
            updateTags(group) {
                API.get(`packages/groups/${group.code}/tags?lang=${localStorage.getItem('lang')}`).then(({data}) => {
                    this.tags = data.data.map(tag => ({
                        label: tag.translations[0]?.value,
                        code: tag.id,
                    }));
                })
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
            checkboxCheckedModify: function (package_allow_modify) {
                if (package_allow_modify) {
                    return 'true'
                } else {
                    return 'false'
                }
            },
            checkboxRecommended: function (package_recommended) {
                if (package_recommended) {
                    return 'true'
                } else {
                    return 'false'
                }
            },
            checkboxFreeSale: function (package_free_sale) {
                if (package_free_sale) {
                    return 'true'
                } else {
                    return 'false'
                }
            },
            checkboxChecked: function (status) {
                if (status) {
                    return 'true'
                } else {
                    return 'false'
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
            showDuplicationModal(p) {
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

                this.$refs['modal-duplicate'].show();
            },
            hideDuplicationModal() {
                this.$refs['modal-duplicate'].hide();
            },
            duplicate() {
                if (this.loading) {
                    return;
                }
                this.loading = true;
                API({
                    method: 'post',
                    url: `package/${this.package_id}/duplicate`
                })
                    .then((result) => {
                        if (result.data.success) {
                            this.onUpdate()

                            let interval;

                            interval = setInterval(async () => {
                                const isDoneDuplicating = await this.isDoneDuplicating(result.data.data.id);
                                if (!isDoneDuplicating) {
                                    return;
                                }

                                clearInterval(interval);
                                this.hideDuplicationModal();
                                this.$router.push({path: `packages/${result.data.data.id}/manage_package`});
                            }, 10000);
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Error',
                                text: this.$t('packages.error.messages.package_duplicate')
                            })
                        }
                    });
            },
            async isDoneDuplicating(packageId) {
                const res = await API({
                    method: 'get',
                    url: `package/${packageId}/duplication-info`,
                });
                console.log(res)
                if (!res.data.success) {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Error',
                        text: this.$t('packages.error.messages.package_duplicate')
                    })
                    return false;
                }

                return !res.data.data.is_processing_plan_rates;
            },
            changeState: function (package_id, status) {
                API({
                    method: 'put',
                    url: 'packages/' + package_id + '/status',
                    data: { status: status }
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.onUpdate()
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Error',
                                text: this.$t('packages.error.messages.information_error')
                            })
                        }
                    })
            },
            changeRecommended: function (package_id, recommended) {
                API({
                    method: 'put',
                    url: 'packages/' + package_id + '/recommended',
                    data: { recommended: recommended }
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.onUpdate()
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Error',
                                text: this.$t('packages.error.messages.information_error')
                            })
                        }
                    })
            },
            changeFreeSale: function (package_id, free_sale) {
                API({
                    method: 'put',
                    url: 'packages/' + package_id + '/free_sale',
                    data: { free_sale: free_sale }
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.onUpdate()
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Error',
                                text: this.$t('packages.error.messages.information_error')
                            })
                        }
                    })
            },
            changeModify: function (package_id, allow_modify) {
                API({
                    method: 'put',
                    url: 'packages/' + package_id + '/modify',
                    data: { allow_modify: allow_modify }
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.onUpdate()
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Error',
                                text: this.$t('packages.error.messages.information_error')
                            })
                        }
                    })
            },
            onUpdate (refreshTable = false) {
                this.urlPackages = '/api/packages?token=' + window.localStorage.getItem('access_token') + '&lang=' +
                    localStorage.getItem('lang') + '&queryCustom=' + this.query_packages +
                    '&group_id=' + (this.filters.group?.code ?? '') + '&tag_id=' + (this.filters.tag?.code ?? '') +
                    '&type_package=' + (this.filters.type_package?.code ?? '');

                // INFO: Se comentó porque la tabla se refresca automaticamente cuando cambía la url
                if (refreshTable) {
                    this.refreshTable();
                }
            },
            refreshTable() {
                this.$refs.table.$refs.tableserver.refresh()
            },
            remove () {
                API({
                    method: 'DELETE',
                    url: 'packages/' + this.package_id
                })
                    .then((result) => {
                        if (result.data.success) {
                            this.onUpdate(true)
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
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('packages.error.messages.name'),
                        text: this.$t('packages.error.messages.connection_error')
                    })
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
