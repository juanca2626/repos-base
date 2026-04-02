<template>
    <div class="container-fluid">
        <div class="col-lg-12">
            <h4>Suplemento: {{ supplement_name }}</h4>
        </div>
        <div class="col-lg-12" v-if="supplement_per_person === 1" style="padding-bottom: 25px;">

            <div class="row col-lg-12">
                <div class="row col-lg-11">

                    <div class="form-group row col-lg-11">
                        <label class="col-lg-3 col-form-label" for="fecha">Periodo de Fecha</label>
                        <date-range-picker
                            id="fecha"
                            :locale-data="locale_data"
                            :timePicker24Hour="false"
                            :showWeekNumbers="false"
                            :ranges="false"
                            :auto-apply="true"
                            v-model="dateRangePerPerson">
                        </date-range-picker>
                    </div>
                </div>
            </div>

            <div class="row col-lg-12" v-for="(option,index_option) in options_per_person">
                <div class="row col-lg-11">
                    <div class="form-group row col-lg-4">
                        <label for="price_per_person" class="col-lg-8 col-form-label">Precio por Persona</label>
                        <input type="text" id="price_per_person" class="form-control col-lg-4"
                               v-model="option.price_per_person">
                    </div>
                    <div class="form-group row col-lg-4">
                        <label for="min_age" class="col-lg-8 col-form-label">Min Age</label>
                        <input type="text" id="min_age" class="form-control col-lg-4"
                               v-model="option.min_age">
                    </div>
                    <div class="form-group row col-lg-4">
                        <label for="min_age" class="col-lg-8 col-form-label">Max Age</label>
                        <input type="text" id="max_age" class="form-control col-lg-4"
                               v-model="option.max_age">
                    </div>
                </div>
                <div class="col-lg-1">
                    <button class="btn btn-success" @click="addNewOptionPerPerson" v-show="index_option === 0">
                        <i class="fas fa-plus"></i>
                    </button>
                    <button class="btn btn-danger" @click="deleteOptionPerPerson(index_option)"
                            v-show="index_option!==0">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>

            <div class="row col-lg-12">
                <button class="btn btn-success" @click="saveOptionsPerPerson">{{ $i18n.t('global.buttons.save') }}
                </button>
                <a :href="'/#/hotels/'+$route.params.hotel_id+'/manage_hotel/supplements_hotel'" class="btn btn-primary"
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
                        <label class="col-sm-2 col-form-label" for="price_per_room">Precio por Habitacion</label>
                        <div class="col-sm-5">
                            <input type="text" id="price_per_room" class="form-control col-lg-4"
                                   v-model="option.price_per_room">
                        </div>
                    </div>
                </div>
            </div>

            <div class="row col-lg-12">
                <button class="btn btn-success" @click="saveOptionsPerRoom">{{ $i18n.t('global.buttons.save') }}
                </button>

                <a :href="'/#/hotels/'+$route.params.hotel_id+'/manage_hotel/supplements_hotel'" class="btn btn-primary"
                   style="margin-left:10px">{{
                    $i18n.t('global.buttons.back') }}</a>
            </div>
        </div>

        <div class="col-lg-12 tabla_supple" v-if="supplement_per_person === 1">

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
                    <input type="text" v-model="props.row.price_per_person" v-if="props.row.accion == true">
                    <template v-else>{{ props.row.price_per_person }}</template>
                </template>

                <template slot="edit" slot-scope="props">
                    <button class="btn btn-success" @click="editSupp(props )" v-if="props.row.accion == false">edit
                    </button>
                    <template v-else>
                        <button class="btn btn-success" @click="editSupp(props)">cancel</button>
                        <button class="btn btn-success" @click="saveSupp(props , 1)">save</button>
                    </template>
                </template>

                <template slot="delete" slot-scope="props">
                    <button class="btn btn-primary" @click="deleteSupp(props , 1)">delete</button>
                </template>

            </table-client>

        </div>
        <div class="col-lg-12 tabla_supple" v-if="supplement_per_room === 1">

            <table-client :columns="table_room.columns" :data="suplements_table" :options="tableOptions_room"
                          id="dataTable"
                          theme="bootstrap4">

                <template slot="price_per_room" slot-scope="props">
                    <input type="text" v-model="props.row.price_per_room" v-if="props.row.accion == true">
                    <template v-else>{{ props.row.price_per_room }}</template>
                </template>

                <template slot="edit" slot-scope="props">
                    <button class="btn btn-success" @click="editSupp(props)" v-if="props.row.accion == false">edit
                    </button>
                    <template v-else>
                        <button class="btn btn-success" @click="editSupp(props)">cancel</button>
                        <button class="btn btn-success" @click="saveSupp(props , 2)">save</button>
                    </template>
                </template>

                <template slot="delete" slot-scope="props">
                    <button class="btn btn-primary" @click="deleteSupp(props , 2)">delete</button>
                </template>


            </table-client>

        </div>
        <BlockUI :message="msg" v-show="blockPage">
            <i class="fa fa-cog fa-spin fa-3x fa-fw"></i>
        </BlockUI>
    </div>
</template>

<script>
    import { API } from './../../../../../api'
    import TableClient from './.././../../../../components/TableClient'
    import MenuEdit from './../../../../../components/MenuEdit'

    export default {
        components: {
            'table-client': TableClient,
            'menu-edit': MenuEdit
        },
        data: () => {
            return {
                msg: 'Procesando',
                blockPage: false,
                suplements_table: [],
                supplement_name: '',
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
                        price_per_person: 0,
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
                table_room: {
                    columns: ['id', 'from', 'to', 'price_per_room', 'created_at', 'edit', 'delete']
                },
                table_person: {
                    columns: ['id', 'from', 'to', 'min_age', 'max_age', 'price_per_person', 'created_at', 'edit', 'delete']
                }
            }
        },
        created () {
            this.getSupplement()
        },
        mounted () {
            this.fetchData(this.$i18n.locale)
        },
        computed: {
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
                        from: this.$i18n.t('suplements.from'),
                        to: this.$i18n.t('suplements.to'),
                        min_age: this.$i18n.t('suplements.min_age'),
                        max_age: this.$i18n.t('suplements.max_age'),
                        price_per_person: this.$i18n.t('suplements.price_per_person'),
                        created_at: this.$i18n.t('suplements.created_at'),
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
                        from: this.$i18n.t('suplements.from'),
                        to: this.$i18n.t('suplements.to'),
                        price_per_room: this.$i18n.t('suplements.price_per_room'),
                        created_at: this.$i18n.t('suplements.created_at'),
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
            saveSupp: function (props, tipo) {

                props.row.tipo = tipo
                this.blockPage = true
                API.post('suplements/hotel/update/per_room', props.row).then((result) => {
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
                    url: 'suplements/hotel/delete/per_room',
                    data: {
                        id: props.row.id,
                        tipo: tipo
                    }
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
            getSupplement: function () {
                API.get('suplements/' + this.$route.params.supplement_id + '?lang=' + localStorage.getItem('lang')).then((result) => {
                    if (result.data.success === true) {
                        this.supplement_name = result.data.data.translations[0].value
                        this.supplement_per_room = result.data.data.per_room
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
                    hotel_id: this.$route.params.hotel_id,
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
                API.post('suplements/hotel/add/per_person', {
                    hotel_id: this.$route.params.hotel_id,
                    supplement_id: this.$route.params.supplement_id,
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
            fetchData: function (lang) {
                let hotel_id = this.$route.params.hotel_id
                let supplement_id = this.$route.params.supplement_id

                API.get('suplements/hotel/table_options?lang=' + lang + '&hotel_id=' + hotel_id + '&supplement_id=' + supplement_id).then((result) => {

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
            }
        }
    }
</script>

<style>
    .tabla_supple td {
        height: 46px !important;
    }
</style>
