<template>
    <div class="vld-parent">
        <loading :active.sync="loading" :can-cancel="false" color="#BD0D12"></loading>

        <div class="col-12">
            <h3 class="text-center title-equivalence">
                Equivalencia Aurora. Código: {{ service_current.aurora_code }} | N°: {{ service_current.equivalence_aurora }}
            </h3>
        </div>

        <div class="col-12">
            <h4 class="title-equivalence">
                Composición de la Equivalencia:
            </h4>
        </div>

        <div class="col-12 text-center my-2">
            <div class="row mx-0 px-0">
                <div class="px-1">
                    <label class="btn btn-outline-success col-6 left" @click="choose_parent(service_parent)"
                           v-for="service_parent in services_parents" :for="'parent_'+service_parent.id">
                        <input type="radio" v-model="service_parent_id" :value="service_parent.id" class="btn-check" name="radio_parent" :id="'parent_'+service_parent.id"> {{ service_parent.master_service.code }} - {{ service_parent.master_service.description }}
                        <label class="right alert-warning" v-if="service_parent.components.length===0">Directo</label>
                        <label class="right alert-info" v-if="service_parent.components.length>0">Padre ({{ service_parent.components.length }})</label>
                    </label>
                    <label class="btn btn-outline-success col-12" v-if="services_parents.length===0">
                        <label class="right alert-info" >No tiene ningún servicio agregado.</label>
                    </label>
                </div>
            </div>
        </div>

        <div class="col-12" v-if="services_parents.length>0">
            <h4 class="title-equivalence" v-if="services_components.length===1 && services_components[0].type_service==='direct'">
                Servicio Directo: {{ service_parent_code }}
            </h4>
            <h4 class="title-equivalence" v-else>
                Componentes de Servicio: {{ service_parent_code }}
            </h4>
        </div>

        <div class="col-12 text-center my-2">
            <div class="row mx-0 px-0">
                <div class="col-6 px-1" v-for="service_component in services_components" v-if="service_component.type_service==='component'">
                    <label class="btn left" style="width: 100%;"
                           :class="{'btn-outline-info':service_component.type_service==='component', 'btn-outline-warning':service_component.type_service==='direct'}"
                           :for="'component_'+service_component.codsvs">
<!--                        <input type="radio" v-model="service_component_code" :value="service_component.codsvs" class="btn-check" name="radio_component" :id="'component_'+service_component.codsvs"> -->
                        {{ service_component.codsvs }} - {{ service_component.descri }}
                    </label>
                </div>

            </div>
        </div>

    </div>
</template>
<script>
    import {API, APISTELA} from '../../../../api'
    import BCardText from 'bootstrap-vue/es/components/card/card-text'
    import BFormCheckbox from 'bootstrap-vue/es/components/form-checkbox/form-checkbox'
    import TableClient from '../../../../components/TableClient'
    import vSelect from 'vue-select'
    import 'vue-select/dist/vue-select.css'
    import Multiselect from 'vue-multiselect'
    import Loading from 'vue-loading-overlay'
    import 'vue-loading-overlay/dist/vue-loading.css'
    import BModal from 'bootstrap-vue/es/components/modal/modal'
    import datePicker from 'vue-bootstrap-datetimepicker'
    import 'pc-bootstrap4-datetimepicker/build/css/bootstrap-datetimepicker.css'
    import moment from 'moment'

    export default {
        components: {
            BCardText,
            BFormCheckbox,
            TableClient,
            Multiselect,
            Loading,
            BModal,
            datePicker,
            vSelect
        },
        data: () => {
            return {
                hidden: 'display:true',
                viewForm: false,
                loading: false,
                loadingFrm: false,
                released: [],
                date_from: '',
                date_to: '',
                date_from_param: '',
                date_to_param: '',
                delete_id: '',
                rates_ids: [],
                type_discount: 'percentage',
                type_discount_param: 'percentage',
                value: 100,
                value_param: 100,
                every: 15,
                every_param: 15,
                roomSelected: [],
                service_current: [],
                services_parents: [],
                service_parent_code: '',
                service_parent_id: '',
                services_components: [],
                service_component_code: '',
            }
        },
        computed: {
            tableOptionsService: function () {
                return {
                    headings: {
                        id: 'ID',
                        rate: 'Tarifa',
                        options: 'Opciones'
                    },
                    uniqueKey: 'id',
                    filterable: [],
                }
            },
            years () {
                let previousYear = moment().subtract(2, 'years').year()
                let currentYear = moment().add(5, 'years').year()
                let years = []
                do {
                    years.push({ value: previousYear, text: previousYear })
                    previousYear++
                } while (currentYear > previousYear)
                return years
            }
        },
        created: function () {
        },
        mounted () {
            let currentDate = new Date()
            setTimeout(() => {
                this.hidden = 'display:none'
            }, 500)

            this.info_service()
        },
        methods: {
            choose_parent(service_parent){
                this.service_parent_code = service_parent.master_service.code
                this.services_components = []
                if( service_parent.components.length > 0 ){
                    this.services_components = service_parent.components
                    this.services_components.forEach((s_c)=>{
                        s_c.type_service = 'component'
                    })
                } else {
                    this.services_components.push({
                        type_service: 'direct',
                        codsvs: service_parent.master_service.code,
                        nroite: null,
                        clasvs: service_parent.master_service.classification,
                        diain: null,
                        diaout: null,
                        horin: null,
                        horout: null,
                        ciudes: service_parent.master_service.city_in_iso,
                        ciuhas: service_parent.master_service.city_out_iso,
                        descri: service_parent.master_service.description,
                    })
                }
                this.service_component_code = this.services_components[0].codsvs

            },
            info_service(){

                this.loading = true
                API.get('/services/' + this.$route.params.service_id )
                    .then((result) => {
                        this.service_current = result.data.data[0]

                        let data_ = {
                            with_components: true
                        }
                        API.post('/services/'+this.$route.params.service_id+'/equivalences', data_)
                            .then((result) => {
                                this.loading = false
                                this.services_parents = []
                                if( result.data.data.length > 0 ){
                                    this.services_parents = result.data.data
                                    this.service_parent_id = this.services_parents[0].id
                                    this.choose_parent(this.services_parents[0])
                                }
                            }).catch(() => {
                            this.loading = false
                        })

                    }).catch(() => {
                    this.loading = false
                })
            },
            setDateFrom_param(e) {
                this.date_to_param= ''
                this.$refs.datePickerTo.dp.minDate(e.date)
            },
            editRangeFrom: function () {
                let data = {
                    service_rate_released_id: this.service_rate_released_id,
                    date_from: moment(this.date_from_param, 'DD/MM/YYYY').format('YYYY-MM-DD'),
                    date_to: moment(this.date_to_param, 'DD/MM/YYYY').format('YYYY-MM-DD'),
                    type_discount: this.type_discount_param,
                    every: this.every_param,
                    value: this.value_param,
                }
                API({
                    method: 'POST',
                    url: 'service_released/range_released',
                    data: data
                })
                    .then((result) => {
                        this.loadingFrm = false
                        if (result.data.success === true) {
                            this.$notify({
                                group: 'main',
                                type: 'success',
                                title: 'Liberados',
                                text: 'Se guardo correctamente'
                            })
                            this.cancelModalAddParams()
                        } else {
                            if (result.data.error === 'RANGE_EXIST') {
                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: 'Liberados',
                                    text: 'Ya tienes registrado el rango de fechas para las tarifas seleccionadas'
                                })
                            }
                        }
                    }).catch(() => {
                    this.loadingFrm = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Liberados',
                        text: this.$t('servicesmanageserviceincludes.error.messages.connection_error')
                    })
                })
            },
            showModalDuplicate: function (id) {
                this.$refs['my-modal-duplicate'].show()
            },

        }, filters: {
            formatDateRange: function (_date) {
                if (_date == undefined) {
                    // console.log('fecha no parseada: ' + _date)
                    return
                }
                _date = _date.split('-')
                _date = _date[2] + '/' + _date[1] + '/' + _date[0]
                return _date
            }
        }
    }
</script>
<style scoped>
    .buttons {
        margin-top: 35px;
    }

    .btn-primary {
        background-color: #005ba5;
        color: white;
        border-color: #005ba5;
    }

    .btn-primary:hover {
        background-color: #0b4d75;
    }
    .title-equivalence{
        background: #d6e3e3;
        padding: 8px;
    }
</style>

