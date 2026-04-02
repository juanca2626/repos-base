<template>
    <div class="animated fadeIn">
        <div class="row">
            <div class="col-xs-12 col-lg-12">
                <template v-if="flag==false">
                    <button @click="create" class="btn btn-danger mb-4" type="reset">
                        {{ $t('hotelsmanagehotelconfiguration.new_policy') }}
                        <font-awesome-icon :icon="['fas', 'plus']" class="nav-icon"/>
                    </button>
                    <table-client :columns="table.columns" :data="rates" :loading="loading" :options="tableOptions"
                                  id="dataTable"
                                  theme="bootstrap4">
                        <div class="table-state" slot="status" slot-scope="props" style="font-size: 0.9em">
                            <b-form-checkbox
                                    :checked="checkboxChecked(props.row.status)"
                                    :id="'checkbox_'+props.row.id"
                                    :name="'checkbox_'+props.row.id"
                                    @change="changeStatus(props.row.id,props.row.status)"
                                    switch>
                            </b-form-checkbox>
                        </div>
                        <div class="table-actions" slot="actions" slot-scope="props">
                            <menu-edit :id="props.row.id" :name="props.row.name" :options="menuOptions"
                                       @edit="edit(props.row, props.row.id)"
                                       @remove="remove(props.row.id)"/>
                        </div>
                        <div class="table-loading text-center" slot="loading" slot-scope="props">
                            <img alt="loading" height="51px" src="/images/loading.svg"/>
                        </div>
                    </table-client>
                </template>
                <template v-else-if="flag=true">
                    <policy-rates-form :draft="draft" @changeStatus="close" @close="flag"/>
                </template>
            </div>
        </div>
    </div>
</template>

<script>

    import { API } from './../../../../api'
    import TableClient from './.././../../../components/TableClient'
    import MenuEdit from './../../../../components/MenuEdit'
    import PolicyRateForm from './PolicyRateForm'
    import BDropDown from 'bootstrap-vue/es/components/dropdown/dropdown'
    import BDropDownItemButton from 'bootstrap-vue/es/components/dropdown/dropdown-item-button'

    export default {
        components: {
            'table-client': TableClient,
            'menu-edit': MenuEdit,
            'policy-rates-form': PolicyRateForm,
            'b-dropdown': BDropDown,
            'b-dropdown-item-button': BDropDownItemButton
        },
        data: () => {
            return {
                loading: false,
                flag: false,
                rates: [],
                table: {
                    columns: ['actions', 'id', 'name', 'days_apply', 'status']
                },
                draft: {
                    id: null,
                    name: '',
                }
            }
        },
        mounted () {
            this.fetchData(this.$i18n.locale)
        },
        computed: {
            menuOptions: function () {

                let options = []

                options.push({
                    type: 'edit',
                    text: '',
                    link: 'meals/edit/',
                    icon: 'dot-circle',
                    callback: '',
                    type_action: 'editButton'
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
                        days_apply: this.$i18n.t('hotelsmanagehotelconfiguration.days'),
                        status: this.$i18n.t('global.status'),
                        actions: this.$i18n.t('global.table.actions')
                    },
                    sortable: ['id'],
                    filterable: []
                }
            }
        },
        created () {
            this.$parent.$parent.$on('langChange', (payload) => {
                this.fetchData(payload.lang)
            })
        },
        methods: {
            checkboxChecked: function (room_state) {
                if (room_state) {
                    return 'true'
                } else {
                    return 'false'
                }
            },
            changeStatus: function (id, status) {
                API({
                    method: 'put',
                    url: 'train_policy_rates/' + id + '/state',
                    data: { status: status }
                })
                .then((result) => {
                    if (result.data.success === true) {
                        this.fetchData(localStorage.getItem('lang'))
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.rooms'),
                            text: this.$t('hotelsmanagehotelconfiguration.error.messages.information_error')
                        })
                    }
                })
            },
            close (valor) {
                this.flag = valor
                this.fetchData(this.$i18n.locale)
            },
            edit: function (data, index) {
                this.draft = clone(data)
                this.draft.action = 'put'

                this.change()
            },
            create: function () {
                this.draft = {
                    id: null,
                    name: '',
                    action: 'post',
                    train_cancellation_policy_id: null,
                    description: '',
                    select_day: '',
                    days_apply: [{
                        all: false,
                        monday: false,
                        tuesday: false,
                        wednesday: false,
                        thursday: false,
                        friday: false,
                        saturday: false,
                        sunday: false
                    }],
                    train_template_id: this.$route.params.train_id,

                }
                this.change()
            },
            change: function () {
                this.flag = this.flag !== true
            },
            fetchData: function (lang) {
                this.loading = true
                API.get('train_policy_rates/?lang=' + lang + '&train_template_id=' + this.$route.params.train_id).then((result) => {
                    this.loading = false
                    if (result.data.success === true) {
                        this.rates = result.data.data
                        for (var i = this.rates.length - 1; i >= 0; i--) {
                            let data = this.rates[i].days_apply.split('|')
                            let value = []

                            for (let j = 0; j < data.length; j++) {

                                if (data[j] === 'all') {
                                    value.push(this.$i18n.t('hotelsmanagehotelconfiguration.all'))
                                    break
                                }
                                switch (data[j]) {
                                    case '1':
                                        value.push(this.$i18n.t('hotelsmanagehotelconfiguration.m'))
                                        break
                                    case '2':
                                        value.push(this.$i18n.t('hotelsmanagehotelconfiguration.tu'))
                                        break
                                    case '3':
                                        value.push(this.$i18n.t('hotelsmanagehotelconfiguration.w'))
                                        break
                                    case '4':
                                        value.push(this.$i18n.t('hotelsmanagehotelconfiguration.thu'))
                                        break
                                    case '5':
                                        value.push(this.$i18n.t('hotelsmanagehotelconfiguration.f'))
                                        break
                                    case '6':
                                        value.push(this.$i18n.t('hotelsmanagehotelconfiguration.sa'))
                                        break
                                    case '7':
                                        value.push(this.$i18n.t('hotelsmanagehotelconfiguration.su'))
                                        break
                                }
                            }
                            this.rates[i].days_apply = value.join('|')
                        }

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
                    url: 'train_policy_rates/' + id
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.fetchData(localStorage.getItem('lang'))
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.policy_rates'),
                                text: this.$t(result.data.message)
                            })
                        }
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('hotelsmanagehotelconfiguration.error.messages.name'),
                        text: this.$t('hotelsmanagehotelconfiguration.error.messages.connection_error')
                    })
                })
            }
        }
    }
</script>

<style lang="stylus">
    .marl {
        margin-left: 800px;
    }
</style>


