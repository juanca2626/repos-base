<template>
    <div class="row mt-4">
        <div class="col-xs-12 col-lg-12">
            <template v-if="flag==false">
                <div class="row">
                    <div class="col-5">
                        <button @click="create" class="btn btn-danger mb-4" type="reset">
                            Nuevo Contacto
                            <font-awesome-icon :icon="['fas', 'plus']" class="nav-icon"/>
                        </button>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <table-client :columns="table.columns" :data="contacts" :loading="loading"
                              :options="tableOptions" id="dataTable"
                              theme="bootstrap4">

                    <div class="table-actions" slot="name" slot-scope="props">
                        {{ props.row.name }}
                    </div>

                    <div class="table-actions" slot="surname" slot-scope="props">
                        {{ props.row.surname }}
                    </div>

                    <div class="table-status" slot="see_in_operations" slot-scope="props">
                        <b-form-checkbox
                            :checked="(props.row.see_in_operations) ? 'true' : 'false'"
                            :id="'checkbox_'+props.row.id"
                            :name="'checkbox_'+props.row.id"
                            @change="changeState(props.row.id,props.row.see_in_operations)"
                            switch>
                        </b-form-checkbox>
                    </div>

                    <div class="table-actions" slot="actions" slot-scope="props">
                        <menu-edit :id="props.row.id" :name="'Contact'" :options="menuOptions"
                                   @edit="edit(props.row, props.row.id)"
                                   @remove="remove(props.row.id)"/>
                    </div>
                    <div class="table-loading text-center" slot="loading" slot-scope="props">
                        <img alt="loading" height="51px" src="/images/loading.svg"/>
                    </div>
                </table-client>

            </template>
            <template v-else-if="flag=true">
                <markup-form :form="draft" @changeStatus="close" @close="flag"/>
            </template>
        </div>
    </div>
</template>

<script>
    import {API} from './../../../../api'
    import Form from './Form'
    import TableClient from './.././../../../components/TableClient'
    import MenuEdit from './../../../../components/MenuEdit'
    import {Switch as cSwitch} from '@coreui/vue'
    import BFormCheckbox from "bootstrap-vue/es/components/form-checkbox/form-checkbox";

    export default {
        components: {
            BFormCheckbox,
            'table-client': TableClient,
            'menu-edit': MenuEdit,
            'markup-form': Form,
            cSwitch
        },
        watch: {
            target() {
                this.fetchData()
            }
        },
        data: () => {
            return {
                loading: false,
                flag: false,
                contacts: [],
                draft: {
                    id: null,
                    type_code: null,
                    order: null,
                    email: null,
                    name: null,
                    surname: null,
                    phone: null,
                    birthday: null,
                    birthday_date: null,
                    client_id: null,
                    see_in_operations: 0,
                    action: ''
                },
                id: null,
                currentIndex: null,
                showEdit: false,
                table: {
                    columns: ['id', 'name', 'surname', 'email', 'phone', 'birthday_date','see_in_operations', 'actions']
                }
            }
        },
        mounted() {
            this.fetchData()
        },
        computed: {
            menuOptions: function () {

                let options = []

                if (this.$can('update', 'clientcontacts')) {
                    options.push({
                        type: 'edit',
                        text: '',
                        link: '',
                        icon: 'dot-circle',
                        callback: '',
                        type_action: 'editButton'
                    })
                }
                if (this.$can('delete', 'clientcontacts')) {
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
                        name: "Nombre Completo",
                        surname: "Cargo",
                        email: "Email",
                        phone: "Teléfono",
                        birthday_date: "Cumpleaños",
                        see_in_operations: "Ver en operaciones",
                        actions: this.$i18n.t('global.table.actions')
                    },
                    sortable: ['id'],
                    filterable: []
                }
            }
        },
        created() {
        },
        methods: {
            changeState: function (client_id, status) {
                API({
                    method: 'put',
                    url: 'client_contacts/' + client_id + '/see_in_operations',
                    data: {status: status}
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.onUpdate()
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: "Contactos de Clientes",
                                text: this.$t('hotels.error.messages.information_error')
                            })
                        }
                    })
            },
            onUpdate() {
                // console.log(this.$refs)
                // this.$refs.table.$refs.tableclient.refresh()
            },
            search: function () {
                this.fetchData()
            },
            checkboxChecked: function (state) {
                return !!state
            },
            close(valor) {
                this.flag = valor
                this.fetchData()
            },
            edit: function (data, index) {
                this.draft = clone(data)
                console.log(this.draft,data)
                this.draft.see_in_operations = data.see_in_operations === 1
                this.draft.birthday_date = this.formatDate(this.draft.birthday_date)
                this.draft.action = 'put'
                this.change()
            },
            formatDate: function (_date) {
                if (_date == undefined) {
                    // console.log('fecha no parseada: ' + _date)
                    return
                }
                _date = _date.split('-')
                _date = _date[2] + '/' + _date[1] + '/' + _date[0]
                return _date
            },
            create: function () {
                this.draft = {
                    id: null,
                    type_code: "C",
                    order: null,
                    email: null,
                    name: null,
                    surname: null,
                    phone: null,
                    birthday: null,
                    birthday_date: null,
                    see_in_operations: 0,
                    client_id: this.$route.params.client_id,
                    action: 'post'
                }
                this.change()
            },
            change: function () {
                if (this.flag === true) {
                    this.flag = false
                } else {
                    this.flag = true
                }
            },
            statusB: function () {
                this.flag = false
            },
            fetchData: function () {
                this.loading = true

                API.get('client_contacts?client_id=' + this.$route.params.client_id ).then((result) => {
                    this.loading = false
                    if (result.data.success === true) {
                        this.contacts = result.data.data
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
            remove(id) {
                API({
                    method: 'DELETE',
                    url: 'client_contacts/' + id
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.fetchData()
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: "Contactos de Clientes",
                                text: this.$t('clientsmanageclientseller.error.messages.seller_delete')
                            })
                        }
                    }).catch(() => {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('clientsmanageclientseller.error.messages.name'),
                            text: this.$t('clientsmanageclientseller.error.messages.connection_error')
                        })
                })
            }
        }
    }
</script>

<style lang="stylus">
</style>


