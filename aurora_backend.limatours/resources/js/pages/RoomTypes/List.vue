<template>
    <table-client :columns="table.columns" :data="room_types" :loading="loading" :options="tableOptions" id="dataTable"
                  theme="bootstrap4">
        <div class="table-actions" slot="actions" slot-scope="props">
            <menu-edit :id="props.row.id" :name="props.row.translations[0].value" :options="menuOptions"
                       @remove="remove(props.row.id)"/>
        </div>
        <div class="table-translations" slot="translations" slot-scope="props">
            {{ props.row.translations[0].value }}
        </div>
        <div class="table-loading text-center" slot="loading">
            <img alt="loading" height="51px" src="/images/loading.svg"/>
        </div>
    </table-client>
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
        data: () => {
            return {
                loading: false,
                room_types: [],
                table: {
                    columns: ['actions', 'id', 'translations', 'occupation']
                },
            }
        },
        mounted () {
            this.fetchData(this.$i18n.locale)
        },
        computed: {
            menuOptions: function () {

                let options = []

                if (this.$can('update', 'roomtypes')) {
                    options.push({
                        type: 'edit',
                        text: '',
                        link: 'room_types/edit/',
                        icon: 'dot-circle',
                        callback: '',
                        type_action: 'link'
                    })
                }
                if (this.$can('delete', 'roomtypes')) {
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
                        translations: this.$i18n.t('hotelsmanagehotelrooms.room_type_id'),
                        occupation: 'Ocupación',
                        actions: this.$i18n.t('global.table.actions')
                    },
                    sortable: ['id'],
                    filterable: ['id', 'translations']
                }
            }
        },
        created () {
            this.$parent.$parent.$on('langChange', (payload) => {
                this.fetchData(payload.lang)
            })
        },
        methods: {
            fetchData: function (lang) {
                this.loading = true
                API.get('room_types/?lang=' + lang).then((result) => {
                    this.loading = false
                    if (result.data.success === true) {
                        this.room_types = result.data.data
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
            remove (id) {
                API({
                    method: 'DELETE',
                    url: 'room_types/' + id
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.fetchData(localStorage.getItem('lang'))
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.room_types'),
                                text: this.$t('roomtypes.error.messages.roomtype_delete')
                            })
                        }
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('roomtypes.error.messages.name'),
                        text: this.$t('roomtypes.error.messages.connection_error')
                    })
                })
            }
        }
    }
</script>

<style lang="stylus">
</style>


