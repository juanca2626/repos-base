<template>
    <div class="container-fluid">
        <div class="row">
<!--            <a href="/translations/operativities/export" target="_blank" class="btn btn-success">-->
<!--                Exportar Operatividades</a>-->
        </div>
        <table-server :columns="table.columns" :options="tableOptions"
                      :url="urlOperability" class="text-center"
                      ref="table">
            <div class="table-name" slot="name" slot-scope="props" style="font-size: 0.9em">
                {{ props.row.translations[0].value }}
            </div>
            <div class="table-actions" slot="actions" slot-scope="props" style="padding: 5px;">
                <b-dropdown class="mt-2 ml-2 mb-0" dropleft size="sm">
                    <template slot="button-content">
                        <font-awesome-icon :icon="['fas', 'bars']" class="ml-1 p-0" />
                    </template>
                    <router-link :to="'/type_operability/edit/'+props.row.id" class="nav-link m-0 p-0">
                        <b-dropdown-item-button class="m-0 p-0" v-if="$can('update', 'servicetypeactivities')">
                            <font-awesome-icon :icon="['fas', 'edit']" class="m-0" />
                            {{$t('global.buttons.edit')}}
                        </b-dropdown-item-button>
                    </router-link>
                    <b-dropdown-item-button @click="showModal(props.row.id,props.row.translations[0].value)"
                                            class="m-0 p-0"
                                            v-if="$can('delete', 'servicetypeactivities')">
                        <font-awesome-icon :icon="['fas', 'trash']" class="m-0" />
                        {{$t('global.buttons.delete')}}
                    </b-dropdown-item-button>
                </b-dropdown>
            </div>
        </table-server>
        <b-modal :title="operabilityName" centered ref="my-modal" size="sm">
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
                loading: false,
                operabilityName: '',
                operations: [],
                urlOperability: '/api/type_operability?token=' + window.localStorage.getItem('access_token') + '&lang=' +
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
                        link: 'type_operability/edit/',
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
                    filterable: ['id', 'translations']
                }
            }
        },
        mounted () {
            this.getData()
            this.$i18n.locale = localStorage.getItem('lang')
        },
        methods: {
            getData: function () {
                this.loading = true
                API.get('/type_operability?token=' + window.localStorage.getItem('access_token') + '&lang=' + localStorage.getItem('lang'))
                    .then((result) => {
                        this.loading = false
                        this.operations = result.data.data
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
            showModal (operability_id, operability_name) {
                this.operability_id = operability_id
                this.operabilityName = operability_name
                this.$refs['my-modal'].show()
            },
            hideModal () {
                this.$refs['my-modal'].hide()
            },
            remove () {
                API({
                    method: 'DELETE',
                    url: 'type_operability/' + this.operability_id
                })
                    .then((result) => {
                        if (result.data.success === true && result.data.used === false) {
                            this.onUpdate()
                            this.hideModal()
                        } else {
                            this.hideModal()
                            if (result.data.used === true) {
                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: this.$t('global.modules.typeoperability'),
                                    text: this.$t('typeoperability.error.messages.used')
                                })
                            } else {
                                this.hideModal()
                                this.$notify({
                                    group: 'main',
                                    type: 'success',
                                    title: this.$t('global.modules.typeoperability'),
                                    text: this.$t('typeoperability.error.messages.requirement_delete')
                                })
                            }

                        }
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('typeoperability.error.messages.name'),
                        text: this.$t('typeoperability.error.messages.connection_error')
                    })
                })
            },
            onUpdate () {
                this.urlOperability = '/api/type_operability?token=' + window.localStorage.getItem('access_token') + '&lang=' +
                    localStorage.getItem('lang')
                this.$refs.table.$refs.tableserver.refresh()
            },
        }

    }
</script>

<style lang="stylus">

</style>
