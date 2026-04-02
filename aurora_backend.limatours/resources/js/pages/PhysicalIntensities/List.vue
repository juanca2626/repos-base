<template>
    <table-client :columns="table.columns" :data="markets" :loading="loading" :options="tableOptions" id="dataTable"
                  theme="bootstrap4">
        <div class="table-actions" slot="actions" slot-scope="props">
            <menu-edit :id="props.row.id" :name="props.row.name" :options="menuOptions"
                       @remove="remove(props.row.id)"/>
        </div>
        <div class="table-name" slot="name" slot-scope="props">
            {{ props.row.translations[0].value }}
        </div>
        <div class="table-color" slot="color" slot-scope="props">
            <button type="button" class="btn" :style="{'background-color': '#'+props.row.color}">{{props.row.color}}
            </button>
            <!--            <span class="badge"></span>-->
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
    import BFormCheckbox from 'bootstrap-vue/es/components/form-checkbox/form-checkbox'

    export default {
        components: {
            'table-client': TableClient,
            'menu-edit': MenuEdit,
            BFormCheckbox
        },
        data: () => {
            return {
                loading: false,
                markets: [],
                table: {
                    columns: ['actions', 'id', 'name', 'color']
                }
            }
        },
        mounted () {
            this.fetchData()
        },
        computed: {
            menuOptions: function () {
                let options = []
                if (this.$can('update', 'physicalintensities')) {
                    options.push({
                        type: 'edit',
                        text: '',
                        link: 'physical_intensities/edit/',
                        icon: 'dot-circle',
                        callback: '',
                        type_action: 'link'
                    })
                }
                if (this.$can('delete', 'physicalintensities')) {
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
                        name: this.$i18n.t('global.name'),
                        color: 'Color',
                        actions: this.$i18n.t('global.table.actions')
                    },
                    sortable: ['id'],
                    filterable: ['id', 'name']
                }
            }
        },
        created () {
            this.$parent.$parent.$on('langChange', (payload) => {
                this.fetchData(payload.lang)
            })
        },
        methods: {
            checkboxChecked: function (market_status) {
                if (market_status) {
                    return 'true'
                } else {
                    return 'false'
                }
            },

            fetchData: function () {
                //this.loading = true
                API.get('physical_intensities?lang=' + localStorage.getItem('lang')).then((result) => {
                    //this.loading = false
                    if (result.data.success === true) {
                        this.markets = result.data.data
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Fetch Error',
                            text: result.data.message
                        })
                    }
                })
                    .catch((error) => {
                        this.loading = false
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: error.response.data.title,
                            text: error.response.data.message
                        })
                    })
            },
            remove (id) {
                API({
                    method: 'DELETE',
                    url: 'physical_intensities/' + id
                })
                    .then((result) => {
                        if(result.data.used === true){
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.physicalintensity'),
                                text: this.$t('physicalintensity.error.messages.used')
                            })
                        }else{
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.physicalintensity'),
                                text: this.$t('inclusions.error.messages.inclusion_delete')
                            })
                        }
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.physicalintensity'),
                        text: this.$t('markets.error.messages.connection_error')
                    })
                })
            }
        }
    }
</script>

<style lang="stylus">
</style>


