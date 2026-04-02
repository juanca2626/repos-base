<template>
    <div class="vld-parent">
        <loading :active.sync="loading" :can-cancel="false" color="#BD0D12"></loading>
        <div class="row justify-content-md-center">
            <div class="col-md-8">
                <table class="table table-bordered">
                    <thead class="thead-light text-center">
                    <tr>
                        <th>{{ $t('packagesmanagepackagerates.from') }}</th>
                        <th>{{ $t('packagesmanagepackagerates.to') }}</th>
                        <th>{{ $t('servicesmanageservicerates.adult') }}</th>
                        <th>Acción</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr :key="pax.id+'_'+index" v-for="(pax, index) in currentPaxs">
                        <td style="padding: 8px">
                            <div class="">
                                <input :id="'pax-'+pax.id+'-pax_from'"
                                       :name="'pax-'+pax.id+'-pax_from'"
                                       class="form-control"
                                       step="1"
                                       min="1"
                                       v-model="currentPaxs[index].pax_from"
                                       type="number"/>
                            </div>
                        </td>
                        <td style="padding: 8px">
                            <div>
                                <input :id="'pax-'+pax.id+'-pax_to'"
                                       :name="'pax-'+pax.id+'-pax_to'"
                                       class="form-control"
                                       step="1"
                                       min="1"
                                       v-model="currentPaxs[index].pax_to"
                                       type="number"/>
                            </div>
                        </td>
                        <td style="padding: 8px">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="basic-addon1">US$</span>
                                </div>
                                <input :id="'pax-'+pax.id+'-adult'"
                                       :name="'pax-'+pax.id+'-adult'"
                                       class="form-control"
                                       step="0.01"
                                       type="number"
                                       aria-describedby="basic-addon1"
                                       v-model="currentPaxs[index].price_adult"/>
                            </div>
                        </td>
                        <td style="padding: 8px">
                            <font-awesome-icon :icon="['fas', 'times-circle']"
                                               @click="removePax(index)"
                                               v-if="index !== 0 || pax.id != null"
                                               style="cursor: pointer;"
                                               class="icon-danger fa-2x"/>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px">
                            <input disabled
                                   class="form-control"
                                   step="1"
                                   min="1"
                                   type="number" style="background: #e1e1e1 !important; "/>
                        </td>
                        <td style="padding: 8px">
                            <input disabled
                                   class="form-control"
                                   step="1"
                                   min="1"
                                   type="number" style="background: #e1e1e1 !important; "/>
                        </td>
                        <td style="padding: 8px">
                            <input disabled
                                   class="form-control"
                                   step="1"
                                   min="1"
                                   type="number" style="background: #e1e1e1 !important; "/>
                        </td>
                        <td style="padding: 8px">
                            <font-awesome-icon
                                :icon="['fas', 'plus']"
                                @click="addPax()"
                                style="cursor: pointer;"
                                class="icon-success fa-2x"/>
                        </td>
                    </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4">
                                <button @click="submit()" class="btn btn-success" type="button">
                                    <font-awesome-icon :icon="['fas', 'save']"/>
                                    {{$t('global.buttons.submit')}}
                                </button>
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="row text-left my-3">
            <div class="col-12">

            </div>
        </div>


    </div>
</template>
<script>
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
    import { API } from '../../../../api'

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
                currentPaxs: [],
                loading: false,
            }
        },
        computed: {},
        created: function () {
        },
        mounted () {
            this.getRatesOts()
        },
        methods: {
            getRatesOts: function () {
                this.loading = true
                API.get('service/rates_ots/' + this.$route.params.service_id)
                    .then((result) => {
                        this.loading = false
                        if (result.data.data.length > 0) {
                            this.currentPaxs = result.data.data
                        } else {
                            this.currentPaxs = []
                            this.setInputs()
                        }
                    }).catch(() => {
                    this.loading = false
                })
            },
            addPax: function () {
                let prevPaxTo = 0
                let prevPaxFrom = 0
                let prevAdult = 0
                if (this.currentPaxs.length > 0) {
                    let prevPax = this.currentPaxs[this.currentPaxs.length - 1]
                    prevPaxTo = parseInt(prevPax.pax_to)
                    prevPaxFrom = parseInt(prevPax.pax_from)
                    prevAdult = parseFloat(prevPax.price_adult)
                }
                this.currentPaxs.push({
                    id: null,
                    pax_from: prevPaxTo + 1,
                    pax_to: (prevPaxTo + 1) + ((prevPaxTo) - prevPaxFrom),
                    price_adult: 0
                })
            },
            setInputs: function () {
                let nInputs
                nInputs = 1
                for (var n = 0; n < nInputs; n++) {
                    this.currentPaxs.push({
                        id: null,
                        pax_from: n + 1,
                        pax_to: n + 1,
                        price_adult: 0
                    })
                }
            },
            removePax: function (i) {
                if (this.currentPaxs[i].id === null) {
                    let tempPaxs = []
                    this.currentPaxs.forEach((value, index) => {
                        if (index != i) {
                            tempPaxs.push(value)
                        }
                    })
                    this.currentPaxs = tempPaxs
                } else {
                    this.loading = true
                    API({
                        method: 'DELETE',
                        url: 'service/rates_ots/' + this.currentPaxs[i].id
                    }).then((result) => {
                        this.loading = false
                        if (result.data.success === true) {
                            this.getRatesOts()
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Tarifas OTS',
                                text: this.$t('global.error.delete')
                            })
                        }
                    }).catch(() => {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Tarifas OTS',
                            text: this.$t('global.error.messages.connection_error')
                        })
                    })
                }

            },
            submit: function () {
                let data = {
                    service_id: this.$route.params.service_id,
                    rates: this.currentPaxs,
                }
                this.loading = true
                API.post('service/rates_ots', data)
                    .then((result) => {
                        this.loading = false
                        if (result.data.success) {
                            this.$notify({
                                group: 'main',
                                type: 'success',
                                title: 'Tarifas OTS',
                                text: 'Se Guardo Satisfactoriamente'
                            })
                            this.getRatesOts()
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Tarifas OTS',
                                text: 'No se pudo guardar. Por favor vuelva a intentarlo'
                            })
                        }
                    }).catch((e) => {
                    console.log(e)
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Tarifas OTS',
                        text: this.$t('hotelsmanagehotelratesratescost.error.messages.connection_error')
                    })
                    this.loading = false
                })
            }
        }, filters: {}
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

    .title-equivalence {
        background: #d6e3e3;
        padding: 8px;
    }
</style>

