<template>
    <div class="col-12">
        <div class="">
            <div class="col-12 text-left" style="padding-top: 20px;" v-show="0">
                <span class="switch-label">Lista</span>
                <span class="two-way-switch">
            <input id="switch" type="checkbox" v-model="showCalendar"/><label for="switch">Toggle</label>
        </span>
                <span class="switch-label">Calendario</span>
            </div>
            <div class="col-12" v-if="!showCalendar">
                <div class="row">
                    <div class="offset-4 col-8 text-right">
                        <router-link :to="'/services_new/'+$route.params.service_id+'/manage_service/rates/cost/add'">
                            <button class="btn btn-primary" type="button" :disabled="loading">
                                <i class="fas fa-plus"></i> {{$t('global.buttons.add')}}
                            </button>
                        </router-link>
                        <button class="btn btn-primary" @click="showModalDuplicateRate" :disabled="loading_duplicate">
                            <i class="far fa-copy"></i> Duplicar Tarifas
                        </button>
                        <button class="btn btn-primary" type="button" :disabled="loading_duplicate" @click="updateRatesInPackages()" v-if="!hasClient">
                            <i class="fas fa-sync-alt"></i> Actualizar en paquetes
                        </button>
                    </div>
                    <div class="col-12">
                        <table-client :columns="table.columns"
                                      :data="rates"
                                      :options="tableOptions"
                                      id="dataTable"
                                      theme="bootstrap4">
                            <div class="table-service_type_rate" slot="service_type_rate" slot-scope="props">
                                {{ props.row.service_type_rate.name}}
                            </div>
                            <div class="table-actions" slot="actions" slot-scope="props">
                                <menu-edit :id="props.row.id" :options="options" @remove="remove(props.row.id)"/>
                            </div>
                        </table-client>
                    </div>
                </div>
            </div>
            <div v-else>
                <Calendar></Calendar>
            </div>
            <b-modal :title="'Duplicar Tarifas'" centered ref="my-modal-duplicate-rates" size="sm"
                     :no-close-on-backdrop=true
                     :no-close-on-esc=true>
                <div>
                    <div class="row text-left" style="padding-top: 20px;" v-if="!showCalendar">
                        <div class="col-12 mb-4">
                            <multiselect :clear-on-select="false"
                                         :close-on-select="false"
                                         :multiple="true"
                                         :options="rates"
                                         placeholder="Elegir Tarifas"
                                         :preserve-search="true"
                                         :taggable="true"
                                         v-model="rates_selected"
                                         label="name"
                                         ref="multiselect"
                                         track-by="id">
                            </multiselect>
                        </div>
                        <div class="col-12 mb-4">
                            <select ref="period" class="form-control" id="year_from" required size="0"
                                    v-model="year_from">
                                <option :value="year.value" v-for="year in years">
                                    {{ year.text}}
                                </option>
                            </select>
                        </div>
                        <div class="col-12 mb-4">
                            <select ref="period" class="form-control" id="year_to" required size="0" v-model="year_to">
                                <option :value="year.value" v-for="year in years">
                                    {{ year.text}}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
                <div slot="modal-footer">
                    <button class="btn btn-success" @click="duplicate" :disabled="loading_duplicate">
                        <i class="fa fa-spin fa-spinner" v-if="loading_duplicate"></i> {{$t('global.buttons.accept')}}
                    </button>
                    <button @click="hideModal()" class="btn btn-danger" :disabled="loading_duplicate">
                        {{$t('global.buttons.cancel')}}
                    </button>
                </div>
            </b-modal>
        </div>
    </div>
</template>

<script>
    import { API } from './../../../../../api'
    import TableClient from './../../../../../components/TableClient'
    import MenuEdit from './../../../../../components/MenuEdit'
    import Calendar from './Calendar'
    import Multiselect from 'vue-multiselect'
    import moment from 'moment'

    export default {
        components: {
            Calendar,
            'table-client': TableClient,
            'menu-edit': MenuEdit,
            Multiselect
        },
        data: () => {
            return {
                rates: [],
                rates_selected: [],
                currentRate: '',
                showCalendar: false,
                loading_duplicate: false,
                table: {
                    columns: ['id', 'name', 'service_type_rate', 'actions']
                },
                year_from: moment().format('YYYY'),
                year_to: moment().add(1, 'year').format('YYYY'),
                loading: false,
                hasClient: false
            }
        },
        mounted () {
            this.fetchData()
        },
        computed: {
            years () {
                let previousYear = moment().subtract(2, 'years').year()
                let currentYear = moment().add(5, 'years').year()

                let years = []

                do {
                    years.push({ value: previousYear, text: previousYear })
                    previousYear++
                } while (currentYear > previousYear)

                return years
            },
            options: function () {
                let options = []

                options.push({
                    type: 'edit',
                    text: 'Editar',
                    link: 'services_new/' + this.$route.params.service_id + '/manage_service/rates/cost/edit/',
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
                        service_type_rate: this.$i18n.t('servicesmanageservicerates.service_type_rate'),
                        actions: this.$i18n.t('global.table.actions')
                    },
                    sortable: ['id'],
                    filterable: ['name']
                }
            }
        },
        created () {
            this.hasClient = !!(window.localStorage.getItem('client_id') && window.localStorage.getItem('client_id') !== '')
            this.$parent.$parent.$on('langChange', () => {
                this.fetchData()
            })
        },
        methods: {
            updateRatesInPackages () {
                this.$root.$emit('blockPage', { message: 'Preparando actualización ...' })
                this.loading = true
                API.put('service/' + this.$route.params.service_id + '/packages/rates')
                    .then((result) => {
                        if (result.data.success) {
                            if (result.data.total === 0) {
                                this.$notify({
                                    group: 'main',
                                    type: 'warning',
                                    title: 'Tarifas',
                                    text: 'Ninguna Cotización de Paquetes contiene este servicio'
                                })
                            } else {
                                this.$notify({
                                    group: 'main',
                                    type: 'success',
                                    title: 'Tarifas',
                                    text: result.data.total + ' Cotizaciones de Paquetes Actualizadas correctamente'
                                })
                            }
                            this.$root.$emit('unlockPage')
                            this.loading = false
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Tarifas',
                                text: 'Error interno'
                            })
                            this.loading = false
                            this.$root.$emit('unlockPage')
                        }
                    }).catch((e) => {
                    console.log(e)
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('hotelsmanagehotelratesratescost.error.messages.name'),
                        text: this.$t('hotelsmanagehotelratesratescost.error.messages.connection_error')
                    })
                    this.$root.$emit('unlockPage')
                    this.loading = false
                })

            },
            duplicate () {
                if (this.year_from === '' || this.year_to === '' || this.rates_selected.length == 0) {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Error',
                        text: 'Faltan datos por llenar'
                    })
                } else {
                   this.loading_duplicate = true
                    let data = {
                        rate_plans: this.rates_selected,
                        year_from: this.year_from,
                        year_to: this.year_to,
                    }
                    API.post('service/rates/cost/duplicate', data)
                        .then((result) => {
                            this.loading_duplicate = false
                            if (result.data.success) {
                                this.hideModal()
                                if (result.data.alerts == '') {
                                    this.$notify({
                                        group: 'main',
                                        type: 'success',
                                        title: 'Tarifas',
                                        text: 'Duplicado correctamente'
                                    })
                                } else {
                                    this.$notify({
                                        group: 'main',
                                        type: 'warning',
                                        title: 'Tarifas',
                                        text: result.data.alerts
                                    })
                                }
                            } else {

                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: 'Tarifas',
                                    text: 'Error interno'
                                })
                            }
                        }).catch((e) => {
                        console.log(e)
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('hotelsmanagehotelratesratescost.error.messages.name'),
                            text: this.$t('hotelsmanagehotelratesratescost.error.messages.connection_error')
                        })
                        this.loading_duplicate = false
                    })
                }
            },
            fetchData: function () {
                API.get('service/rates/cost/' + this.$route.params.service_id)
                    .then((result) => {
                        if (result.data.success === true) {
                            this.rates = result.data.data
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
                API.delete('service/rates/cost/' + this.$route.params.service_id + '/' + id)
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
            },
            hideModal() {
                this.$refs['my-modal-duplicate-rates'].hide()
            },
            showModalDuplicateRate() {
                this.$refs['my-modal-duplicate-rates'].show()
            },
        }
    }
</script>

<style lang="stylus">
</style>


