<template>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2">
                <div class="form-row">
                    <label class="col-md-12" for="year_from">Año a duplicar:</label>
                    <div class="col-sm-12">
                        <select name="year_from" id="year_from" v-model="year_from" class="form-control">
                            <option :value="year" v-for="year in periods">
                                {{ year }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-row">
                    <label class="col-md-12" for="year_to">Aplicar en el año:</label>
                    <div class="col-sm-12">
                        <select name="year_to" id="year_to" v-model="year_to" class="form-control">
                            <option :value="year.value" v-for="year in years">
                                {{ year.text}}
                            </option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <label class="col-md-12" for="year_to">.</label>
                <button class="btn btn-danger" type="button" :disabled="loading" @click="duplicate">Duplicar</button>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <h4>Suplemento: {{ supplement_name }}</h4>
            </div>
        </div>
        <div class="col-lg-12" v-if="supplement_per_person === 1" style="padding-bottom: 25px;">
            <div class="form-row">
                <div class="col-md-4">
                    <label class="col-md-12" for="fecha">Periodo de Fecha</label>
                    <div class="col-md-12">
                        <date-range-picker
                            id="dateRangePerPerson" name="date_range"
                            :locale-data="locale_data"
                            :timePicker24Hour="false"
                            :showWeekNumbers="false"
                            :ranges="false"
                            :auto-apply="true"
                            v-validate="'required'"
                            v-model="dateRangePerPerson">
                        </date-range-picker>
                        <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                            <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                               style="margin-left: 5px;"
                                               v-show="errors.has('date_range')"/>
                            <span v-show="errors.has('date_range')">Ingresar fechas</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-5">
                <div class="col-lg-12" v-for="(option,index_option) in options_per_person">
                    <div class="form-row">
                        <div class="col-md-3">
                            <label :for="'price_per_person_'+index_option" class="col-md-12">Precio</label>
                            <div class="col-md-12">
                                <input type="text" :id="'price_per_person_'+index_option" class="form-control"
                                       :name="'price_per_person_'+index_option"
                                       v-model="option.price_per_person" v-validate="'required|decimal|min_value:1'">
                                <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                    <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                       style="margin-left: 5px;"
                                                       v-show="errors.has('price_per_person_'+index_option)"/>
                                    <span v-show="errors.has('price_per_person_'+index_option)">
                                        requerido, valor minimo 1
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="min_age" class="col-md-12">Min Age</label>
                            <div class="col-md-12">
                                <input type="text" id="min_age" class="form-control"
                                       v-model="option.min_age">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="max_age" class="col-md-12">Max Age</label>
                            <div class="col-md-12">
                                <input type="text" id="max_age" class="form-control"
                                       v-model="option.max_age">
                            </div>
                        </div>

                        <div class="col-lg-3 mt-4">
                            <button class="btn btn-success" @click="addNewOptionPerPerson" v-show="index_option === 0">
                                <i class="fas fa-plus"></i>
                            </button>
                            <button class="btn btn-danger" @click="deleteOptionPerPerson(index_option)"
                                    v-show="index_option!==0">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row col-lg-12">
                <button class="btn btn-success" @click="validateBeforeSubmit()">
                    {{ $i18n.t('global.buttons.save') }}
                </button>
                <a :href="'/#/services_new/'+$route.params.service_id+'/manage_service/service_supplements'"
                   class="btn btn-primary"
                   style="margin-left:10px">{{
                    $i18n.t('global.buttons.back') }}</a>
            </div>
        </div>
        <div class="col-lg-12" v-if="supplement_per_room === 1" style="padding-bottom: 25px;">
            <div v-for="(option,index_option) in options_per_room">
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="name">Periodo de Fecha</label>
                        <div class="col-sm-5">
                            <date-range-picker
                                :locale-data="locale_data"
                                :timePicker24Hour="false"
                                :showWeekNumbers="false"
                                :ranges="false"
                                :auto-apply="true"
                                v-model="option.dateRange">
                            </date-range-picker>
                        </div>
                    </div>
                </div>

                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="name">Precio por Habitacion</label>
                        <div class="col-sm-5">
                            <input type="text" id="price_per_room" class="form-control col-lg-4"
                                   v-model="option.price_per_room">
                        </div>
                    </div>
                </div>
            </div>

            <div class="row col-lg-12">
                <button class="btn btn-success" @click="saveOptionsPerRoom">
                    {{ $i18n.t('global.buttons.save') }}
                </button>
                <a :href="'/#/services_new/'+$route.params.service_id+'/manage_service/service_supplements'"
                   class="btn btn-primary"
                   style="margin-left:10px">{{
                    $i18n.t('global.buttons.back') }}</a>
            </div>
        </div>

        <div class="col-lg-12 tabla_supple" v-if="supplement_per_person === 1">
            <div class="col-12">
                <div class="form-row">
                    <label class="col-sm-1 col-form-label" for="period">{{
                        $t('clientsmanageclienthotel.period') }}</label>
                    <div class="col-sm-1.5">
                        <select @change="searchPeriod" ref="period" class="form-control" id="period"
                                required
                                size="0" v-model="selectPeriod">
                            <option :value="year" v-for="year in periods">
                                {{ year}}
                            </option>
                        </select>
                    </div>
                </div>
            </div>
            <table-client :columns="table_person.columns" :data="suplements_table" :options="tableOptions_person"
                          id="dataTable"
                          theme="bootstrap4">

                <template slot="min_age" slot-scope="props">
                    <input type="text" v-model="props.row.min_age" v-if="props.row.accion == true">
                    <template v-else>{{ props.row.min_age }}</template>
                </template>

                <template slot="max_age" slot-scope="props">
                    <input type="text" v-model="props.row.max_age" v-if="props.row.accion == true">
                    <template v-else>{{ props.row.max_age }}</template>
                </template>

                <template slot="price_per_person" slot-scope="props">
                    <input type="text" v-model="props.row.price" v-if="props.row.accion == true">
                    <template v-else>{{ props.row.price }}</template>
                </template>

                <template slot="edit" slot-scope="props">
                    <button class="btn btn-success" @click="editSupp(props )" v-if="props.row.accion == false">
                        Editar
                    </button>
                    <template v-else>
                        <button class="btn btn-success" @click="saveSupp(props , 1)">Guardar</button>
                        <button class="btn btn-primary" @click="editSupp(props)">Cancelar</button>
                    </template>
                </template>

                <template slot="delete" slot-scope="props">
                    <button class="btn btn-primary" @click="deleteSupp(props , 1)">
                        Eliminar
                    </button>
                </template>

            </table-client>

        </div>
        <!--        <div class="col-lg-12 tabla_supple" v-if="supplement_per_room === 1">-->

        <!--            <table-client :columns="table_room.columns" :data="suplements_table" :options="tableOptions_room"-->
        <!--                          id="dataTable"-->
        <!--                          theme="bootstrap4">-->

        <!--                <template slot="price_per_room" slot-scope="props">-->
        <!--                    <input type="text" v-model="props.row.price_per_room" v-if="props.row.accion == true">-->
        <!--                    <template v-else>{{ props.row.price_per_room }}</template>-->
        <!--                </template>-->

        <!--                <template slot="edit" slot-scope="props">-->
        <!--                    <button class="btn btn-success" @click="editSupp(props)" v-if="props.row.accion == false">edit-->
        <!--                    </button>-->
        <!--                    <template v-else>-->
        <!--                        <button class="btn btn-success" @click="editSupp(props)">cancel</button>-->
        <!--                        <button class="btn btn-success" @click="saveSupp(props , 2)">save</button>-->
        <!--                    </template>-->
        <!--                </template>-->

        <!--                <template slot="delete" slot-scope="props">-->
        <!--                    <button class="btn btn-primary" @click="deleteSupp(props , 2)">delete</button>-->
        <!--                </template>-->


        <!--            </table-client>-->

        <!--        </div>-->
        <BlockUI :message="msg" v-show="blockPage">
            <i class="fa fa-cog fa-spin fa-3x fa-fw"></i>
        </BlockUI>
    </div>
</template>

<script>
    import { API } from './../../../../../api'
    import TableClient from './.././../../../../components/TableClient'
    import MenuEdit from './../../../../../components/MenuEdit'
    import moment from 'moment'

    export default {
        components: {
            'table-client': TableClient,
            'menu-edit': MenuEdit
        },
        data: () => {
            return {
                msg: 'Procesando',
                blockPage: false,
                loading: false,
                periods: [],
                suplements_table: [],
                supplement_name: '',
                year_from: 0,
                year_to: 0,
                supplement_per_room: 0,
                supplement_per_person: 0,
                options_per_room: [
                    {
                        price_per_room: 0,
                        dateRange: []
                    }
                ],
                dateRangePerPerson: [],
                options_per_person: [
                    {
                        price_per_person: 1,
                        min_age: 0,
                        max_age: 0
                    }
                ],
                locale_data: {
                    direction: 'ltr',
                    format: moment.localeData().postformat('ddd D MMM'),
                    separator: ' - ',
                    applyLabel: 'Guardar',
                    cancelLabel: 'Cancelar',
                    weekLabel: 'W',
                    customRangeLabel: 'Rango de Fechas',
                    daysOfWeek: moment.weekdaysMin(),
                    monthNames: moment.monthsShort(),
                    firstDay: moment.localeData().firstDayOfWeek()
                },
                selectPeriod: '',
                table_room: {
                    columns: ['id', 'from', 'to', 'price_per_room', 'edit', 'delete']
                },
                table_person: {
                    columns: ['id', 'from', 'to', 'min_age', 'max_age', 'price_per_person', 'edit', 'delete']
                }
            }
        },
        created () {
            this.getSupplement()
        },
        mounted () {
            let currentDate = new Date()
            this.selectPeriod = currentDate.getFullYear()
            this.getPeriods()
            this.fetchData(this.$i18n.locale)
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
            menuOptions: function () {

                let options = []
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
            tableOptions_person: function () {
                return {
                    headings: {
                        id: 'ID',
                        from: this.$i18n.t('supplements.from'),
                        to: this.$i18n.t('supplements.to'),
                        min_age: this.$i18n.t('supplements.min_age'),
                        max_age: this.$i18n.t('supplements.max_age'),
                        price_per_person: this.$i18n.t('supplements.price'),
                        actions: this.$i18n.t('global.table.actions')
                    },
                    sortable: [],
                    filterable: []
                }
            },
            tableOptions_room: function () {
                return {
                    headings: {
                        id: 'ID',
                        from: this.$i18n.t('supplements.from'),
                        to: this.$i18n.t('supplements.to'),
                        price_per_room: this.$i18n.t('supplements.price_per_room'),
                        created_at: this.$i18n.t('supplements.created_at'),
                        actions: this.$i18n.t('global.table.actions')
                    },
                    sortable: [],
                    filterable: []
                }
            }

        },
        methods: {
            editSupp: function (props) {

                this.suplements_table[props.index - 1].accion = !props.row.accion

            },
            validateBeforeSubmit: function () {
                this.$validator.validateAll().then((result) => {
                    console.log(result)
                    if (result) {
                        this.saveOptionsPerPerson()
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Suplementos',
                            text: this.$t('inclusions.error.messages.information_complete')
                        })
                        this.loading = false
                    }
                })
            },
            saveSupp: function (props, tipo) {

                props.row.tipo = tipo
                this.blockPage = true
                API.put('supplements/service/options', props.row).then((result) => {
                    if (result.data.success === true) {
                        this.fetchData(this.$i18n.locale)
                    }
                }).catch(() => {
                    this.blockPage = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('suplements.error.messages.name'),
                        text: this.$t('suplements.error.messages.connection_error')
                    })
                })

            },
            deleteSupp: function (props, tipo) {

                props.row.tipo = tipo
                this.blockPage = true
                // API('suplements/hotel/delete/per_room', {id:props.row.id} ).then((result) => {

                API({
                    method: 'DELETE',
                    url: 'supplements/service/options/' + props.row.id
                }).then((result) => {

                    if (result.data.success === true) {
                        this.fetchData(this.$i18n.locale)
                    }
                }).catch(() => {
                    this.blockPage = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('suplements.error.messages.name'),
                        text: this.$t('suplements.error.messages.connection_error')
                    })
                })
            },

            clearOptions: function () {
                this.options_per_room = [{
                    price_per_room: 0,
                    dateRange: []
                }]
            },
            clearOptionsPerPerson: function () {
                this.dateRangePerPerson = []
                this.options_per_person = [{
                    price_per_room: 0,
                    min_age: 0,
                    max_age: 0
                }]
            },
            deleteOptionPerPerson: function (index_option) {
                if (index_option !== 0) {
                    this.options_per_person.splice(index_option, 1)
                }
            },
            addNewOptionPerPerson: function () {
                this.options_per_person.push({
                    price_per_person: 0,
                    min_age: 0,
                    max_age: 0
                })
            },
            getPeriods: function () {
                API.get('supplements/services/options/periods?service_supplement_id=' + this.$route.params.supplement_id).then((result) => {
                    if (result.data.success === true) {
                        let periods = []
                        let data = result.data.data
                        data.forEach(function (number) {
                            periods.push(number.year)
                        })
                        this.periods = periods
                        console.log(periods)
                    }
                }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('suplements.error.messages.name'),
                        text: this.$t('suplements.error.messages.connection_error')
                    })
                })
            },
            getSupplement: function () {
                API.get('supplements/service/' + this.$route.params.supplement_id + '?lang=' + localStorage.getItem('lang')).then((result) => {
                    if (result.data.success === true) {
                        this.supplement_name = result.data.data.translations[0].value
                        this.supplement_per_person = result.data.data.per_person
                    }
                }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('suplements.error.messages.name'),
                        text: this.$t('suplements.error.messages.connection_error')
                    })
                })
            },
            saveOptionsPerRoom: function () {

                this.blockPage = true
                API.post('suplements/hotel/add/per_room', {
                    service_id: this.$route.params.service_id,
                    supplement_id: this.$route.params.supplement_id,
                    options: this.options_per_room
                }).then((result) => {

                    this.blockPage = false
                    if (result.data.success === true) {
                        this.clearOptions()
                        this.$notify({
                            group: 'main',
                            type: 'success',
                            title: 'Success',
                            text: result.data.message
                        })
                        this.fetchData(this.$i18n.locale)
                    }
                }).catch(() => {
                    this.blockPage = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('suplements.error.messages.name'),
                        text: this.$t('suplements.error.messages.connection_error')
                    })
                })
            },
            saveOptionsPerPerson: function () {
                API.post('supplements/service/options', {
                    service_supplement_id: this.$route.params.supplement_id,
                    date_range: this.dateRangePerPerson,
                    options: this.options_per_person
                }).then((result) => {
                    if (result.data.success === true) {
                        this.clearOptionsPerPerson()
                        this.$notify({
                            group: 'main',
                            type: 'success',
                            title: 'Success',
                            text: result.data.message
                        })
                        this.fetchData(this.$i18n.locale)
                    }
                }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('suplements.error.messages.name'),
                        text: this.$t('suplements.error.messages.connection_error')
                    })
                })
            },
            searchPeriod: function () {
                this.fetchData()
            },
            fetchData: function (lang) {
                let supplement_id = this.$route.params.supplement_id
                API.get('supplements/services/options?service_supplement_id=' + supplement_id + '&year=' + this.selectPeriod).then((result) => {
                    this.blockPage = false
                    if (result.data.success === true) {
                        this.suplements_table = result.data.data
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Fetch Error',
                            text: result.data.message
                        })
                    }
                }).catch(() => {
                    this.blockPage = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('suplements.error.messages.name'),
                        text: this.$t('suplements.error.messages.connection_error')
                    })
                })
            },
            duplicate () {
                if (this.year_from === '' || this.year_to === '') {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Error',
                        text: 'Faltan datos por llenar'
                    })
                } else {
                    this.$root.$emit('blockPage', { message: 'Cargando..' })
                    let data = {
                        service_supplement_id: this.$route.params.supplement_id,
                        year_from: this.year_from,
                        year_to: this.year_to,
                    }
                    API.post('supplements/services/options/period/duplicate', data)
                        .then((result) => {
                            this.$root.$emit('unlockPage')
                            if (result.data.success) {
                                if (result.data.alerts == '') {
                                    this.getPeriods()
                                    this.$notify({
                                        group: 'main',
                                        type: 'success',
                                        title: this.$t('global.modules.services'),
                                        text: 'Duplicado correctamente'
                                    })
                                } else {
                                    this.$notify({
                                        group: 'main',
                                        type: 'warning',
                                        title: this.$t('global.modules.services'),
                                        text: result.data.alerts
                                    })
                                }
                            } else {

                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: this.$t('global.modules.services'),
                                    text: 'Error interno'
                                })
                            }
                        }).catch((e) => {
                        console.log(e)
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.services'),
                            text: this.$t('servicesmanageservicerates.error.messages.connection_error')
                        })
                        this.$root.$emit('unlockPage')
                    })
                }
            },

        }
    }
</script>

<style>
    .tabla_supple td {
        height: 46px !important;
    }
</style>
