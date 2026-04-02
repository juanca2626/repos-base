<template>
    <div class="col-12">
        <div class="">
            <div class="col-12">
                <div class="row">
                    <div class="offset-10  col-2 text-right">
                        <router-link :to="'/trains/'+$route.params.train_id+'/manage_train/rates/cost/add'"
                                     class="nav-link">
                            <button class="btn btn-primary" type="button">+ {{$t('global.buttons.add')}}</button>
                        </router-link>
                    </div>
                    <div class="col-12">
                        <table-client :columns="table.columns"
                                      :data="train_rates"
                                      :options="tableOptions"
                                      theme="bootstrap4">
                            <div class="table-actions" slot="actions" slot-scope="props">
                                <menu-edit :id="props.row.id" :options="options" @remove="remove(props.row.id)"/>
                            </div>
                        </table-client>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import { API } from './../../../../../api'
    import TableClient from './../../../../../components/TableClient'
    import MenuEdit from './../../../../../components/MenuEdit'

    export default {
        components: {
            'table-client': TableClient,
            'menu-edit': MenuEdit
        },
        data: () => {
            return {
                train_rates: [],
                currentRate: '',
                table: {
                    columns: ['id', 'name', 'actions']
                },
            }
        },
        mounted () {
            this.fetchData()
        },
        computed: {
            options: function () {
                let options = []

                options.push({
                    type: 'edit',
                    text: 'Edit',
                    link: 'trains/' + this.$route.params.train_id + '/manage_train/rates/cost/edit/',
                    icon: 'dot-circle',
                    callback: '',
                    type_action: 'link'
                })

                options.push({
                    type: 'delete',
                    text: '',
                    link: '',
                    icon: 'trash',
                    type_action: 'button',
                    callback_delete: 'remove'
                })

                return options
            },
            tableOptions: function () {
                return {
                    headings: {
                        id: 'ID',
                        name: this.$i18n.t('global.name'),
                        actions: this.$i18n.t('global.table.actions')
                    },
                    sortable: ['id'],
                    filterable: ['name']
                }
            }
        },
        created () {
            this.$parent.$parent.$on('langChange', () => {
                this.fetchData()
            })
        },
        methods: {
            fetchData: function () {
                API.get('train_template/' + this.$route.params.train_id + '/rates')
                    .then((result) => {
                        if (result.data.success === true) {
                            this.train_rates = result.data.data
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Fetch Error',
                                text: result.data.message
                            })
                        }
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.services'),
                        text: this.$t('servicesmanageservicerates.error.messages.connection_error')
                    })
                })
            },
            remove (id) {
                this.loading = true
                API.delete('train_template/rate/' + id)
                    .then((result) => {
                        if (result.data.success === true) {
                            this.fetchData()
                        } else {
                            if (result.data.used === true) {
                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: this.$t('global.modules.services'),
                                    text: this.$t('servicerate.error.messages.used')
                                })
                            } else {
                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: this.$t('global.modules.services'),
                                    text: this.$t('servicecategories.error.messages.requirement_delete')
                                })
                            }
                        }

                        this.loading = false
                    })
            }
        }
    }
</script>

<style lang="stylus">

</style>


