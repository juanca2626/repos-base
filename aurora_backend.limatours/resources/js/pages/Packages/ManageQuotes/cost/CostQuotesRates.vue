<template>

    <div style="padding: 10px">
        <div class="vld-parent">
            <loading :active.sync="loading" :can-cancel="false" color="#BD0D12"></loading>
            <div class="row">
                <div class="col-md-12 text-right">
                    <img src="/images/loading.svg" v-if="loadingUpdate" width="40px"/>
                    <button @click="updateRates" class="btn btn-success" type="submit" v-if="!loadingUpdate">
                        {{$t('global.buttons.update')}}
                    </button>
                </div>
                <div class="col-md-12">
                    <b-tabs>
                        <b-tab title="Compartido" active v-if="showSIM">
                            <div class="row">
                                <div class="col-md-12">
                                    <table class="table table-bordered">
                                        <thead class="thead-light text-center">
                                        <tr>
                                            <th></th>
                                            <th>{{ $t('packagesmanagepackagerates.from') }}</th>
                                            <th>{{ $t('packagesmanagepackagerates.to') }}</th>
                                            <th v-if="showPricesCostShared">Simple</th>
                                            <th v-if="showPricesCostShared">Doble</th>
                                            <th v-if="showPricesCostShared">Triple</th>
                                            <th>{{ $t('global.table.actions') }}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="(rate,index) in form.ratesShared" :key="`contact-${index+1}`">
                                            <th class="text-center">
                                                <div class="col-md-12 font-weight-bold">
                                                    {{index + 1}}
                                                </div>
                                            </th>
                                            <td>
                                                <div class="">
                                                    <div class="col-md-12 input-group-sm">
                                                        <input :class="{'form-control':true }"
                                                               type="text"
                                                               v-model="rate[0].pax_from"
                                                               disabled>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="col-md-12 input-group-sm">
                                                    <input :class="{'form-control':true }"
                                                           type="text"
                                                           data-vv-as="pax_to"
                                                           :name="`pax_to${index}`"
                                                           v-validate="'required'"
                                                           data-vv-scope="formShared"
                                                           v-model="rate[0].pax_to"
                                                           :disabled="lastRowShared != index">
                                                    <span class="invalid-feedback-select"
                                                          v-show="errors.has(`pax_to${index}`)">
                                                            <span>{{ errors.first(`pax_to${index}`) }}</span>
                                                        </span>
                                                </div>
                                            </td>
                                            <td v-if="showPricesCostShared">
                                                <div class="">
                                                    <div class="">
                                                        <div class="col-md-12 input-group-sm">
                                                            <input :class="{'form-control':true }"
                                                                   type="text"
                                                                   data-vv-as="simple"
                                                                   :name="`simple_${index}`"
                                                                   v-validate="'required|decimal:2'"
                                                                   data-vv-scope="formShared"
                                                                   v-model="rate[0].simple">
                                                            <span class="invalid-feedback-select"
                                                                  v-show="errors.has(`simple_${index}`)">
                                                            <span>{{ errors.first(`simple_${index}`) }}</span>
                                                        </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td v-if="showPricesCostShared">
                                                <div class="">
                                                    <div class="col-md-12 input-group-sm">
                                                        <input :class="{'form-control':true }"
                                                               type="text"
                                                               data-vv-as="double"
                                                               :name="`double_${index}`"
                                                               v-validate="'required|decimal:2'"
                                                               data-vv-scope="formShared"
                                                               v-model="rate[0].double">
                                                        <span class="invalid-feedback-select"
                                                              v-show="errors.has(`double_${index}`)">
                                                            <span>{{ errors.first(`double_${index}`) }}</span>
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td v-if="showPricesCostShared">
                                                <div class="">
                                                    <div class="col-md-12 input-group-sm">
                                                        <input :class="{'form-control':true }"
                                                               type="text"
                                                               data-vv-as="triple"
                                                               :name="`triple_${index}`"
                                                               v-validate="'required|decimal:2'"
                                                               data-vv-scope="formShared"
                                                               v-model="rate[0].triple">
                                                        <span class="invalid-feedback-select"
                                                              v-show="errors.has(`triple_${index}`)">
                                                            <span>{{ errors.first(`triple_${index}`) }}</span>
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div>
                                                    <input type="number" v-model="quantityRow" min="1"
                                                           v-if="index === lastRowShared"
                                                           style="width:50px; border:1px solid #373737; padding:3px;" />
                                                    <button @click="validateBeforeAdd(1)"
                                                            class="btn btn-sm btn-success"
                                                            v-if="index === lastRowShared"
                                                            type="button">
                                                        <font-awesome-icon :icon="['fas', 'plus']"/>
                                                    </button>
                                                    <button @click="removeRowByType(index, 1)"
                                                            class="btn btn-sm btn-danger"
                                                            v-if="index === lastRowShared && index !== 0"
                                                            type="button">
                                                        <font-awesome-icon :icon="['fas', 'minus']"/>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>

                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-md-12">
                                    <button @click="validateBeforeSave(1)"
                                            class="btn btn-sm btn-success pull-right"
                                            type="button">
                                        <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                                        {{$t('global.buttons.submit')}}
                                    </button>
                                </div>
                            </div>
                        </b-tab>
                        <b-tab title="Privado" active v-if="showPC">
                            <div class="row">
                                <div class="col-md-12">
                                    <table class="table table-bordered">
                                        <thead class="thead-light text-center">
                                        <tr>
                                            <th>#</th>
                                            <th>{{ $t('packagesmanagepackagerates.from') }}</th>
                                            <th>{{ $t('packagesmanagepackagerates.to') }}</th>
                                            <th v-if="showPricesCost">Simple</th>
                                            <th v-if="showPricesCost">Doble</th>
                                            <th v-if="showPricesCost">Triple</th>
                                            <th>{{ $t('global.table.actions') }}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="(rate,index) in form.ratesPrivate" :key="`contact-${index+1}`">
                                            <th class="text-center">
                                                <div class="col-md-12 font-weight-bold">
                                                    {{index + 1}}
                                                </div>
                                            </th>
                                            <td>
                                                <div class="">
                                                    <div class="col-md-12 input-group-sm">
                                                        <input :class="{'form-control':true }"
                                                               type="text"
                                                               v-model="rate[0].pax_from"
                                                               disabled>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="">
                                                    <div class="col-md-12 input-group-sm">
                                                        <input :class="{'form-control':true }"
                                                               type="text"
                                                               data-vv-as="pax_to"
                                                               :name="`pax_to${index}`"
                                                               v-validate="'required'"
                                                               data-vv-scope="formPrivate"
                                                               v-model="rate[0].pax_to"
                                                               :disabled="lastRowPrivate != index">
                                                        <span class="invalid-feedback-select"
                                                              v-show="errors.has(`pax_to${index}`)">
                                                            <span>{{ errors.first(`pax_to${index}`) }}</span>
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td v-if="showPricesCost">
                                                <div class="">
                                                    <div class="col-md-12 input-group-sm">
                                                        <input :class="{'form-control':true }"
                                                               type="text"
                                                               data-vv-as="simple"
                                                               :name="`simple_${index}`"
                                                               v-validate="'required|decimal:2'"
                                                               data-vv-scope="formPrivate"
                                                               v-model="rate[0].simple">
                                                        <span class="invalid-feedback-select"
                                                              v-show="errors.has(`simple_${index}`)">
                                                            <span>{{ errors.first(`simple_${index}`) }}</span>
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td v-if="showPricesCost">
                                                <div class="">
                                                    <div class="col-md-12 input-group-sm">
                                                        <input :class="{'form-control':true }"
                                                               type="text"
                                                               data-vv-as="double"
                                                               :name="`double_${index}`"
                                                               v-validate="'required|decimal:2'"
                                                               data-vv-scope="formPrivate"
                                                               v-model="rate[0].double">
                                                        <span class="invalid-feedback-select"
                                                              v-show="errors.has(`double_${index}`)">
                                                            <span>{{ errors.first(`double_${index}`) }}</span>
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td v-if="showPricesCost">
                                                <div class="">
                                                    <div class="col-md-12 input-group-sm">
                                                        <input :class="{'form-control':true }"
                                                               type="text"
                                                               data-vv-as="triple"
                                                               :name="`triple_${index}`"
                                                               v-validate="'required|decimal:2'"
                                                               data-vv-scope="formPrivate"
                                                               v-model="rate[0].triple">
                                                        <span class="invalid-feedback-select"
                                                              v-show="errors.has(`triple_${index}`)">
                                                            <span>{{ errors.first(`triple_${index}`) }}</span>
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div>
                                                    <input type="number" v-model="quantityRow" min="1"
                                                           v-if="index === lastRowPrivate"
                                                           style="width:50px; border:1px solid #373737; padding:3px;" />
                                                    <button @click="validateBeforeAdd(2)"
                                                            v-if="index === lastRowPrivate"
                                                            class="btn btn-sm btn-success"
                                                            type="button">
                                                        <font-awesome-icon :icon="['fas', 'plus']"/>
                                                    </button>
                                                    <button @click="removeRowByType(index,2)"
                                                            class="btn btn-sm btn-danger"
                                                            v-if="index === lastRowPrivate && index !== 0"
                                                            type="button">
                                                        <font-awesome-icon :icon="['fas', 'minus']"/>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-md-12">
                                    <button @click="validateBeforeSave(2)"
                                            class="btn btn-sm btn-success pull-right"
                                            type="button">
                                        <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                                        {{$t('global.buttons.submit')}}
                                    </button>
                                </div>
                            </div>
                        </b-tab>
                    </b-tabs>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import { API } from './../../../../api'
    import TableClient from './.././../../../components/TableClient'
    import Loading from 'vue-loading-overlay'

    export default {
        name: 'CostRates',
        components: {
            'table-client': TableClient,
            Loading
        },
        data: () => {
            return {
                quantityRow: 1,
                showSIM: false,
                showPC: false,
                loadingUpdate: false,
                showPricesCost: false,
                showPricesCostShared: false,
                category_id: '',
                loading: false,
                form: {
                    ratesPrivate: [],
                    ratesShared: []
                },
                table: {
                    columns: ['pax_from', 'pax_to', 'simple', 'double', 'triple', 'actions']
                },
                price_child_with_bed: 0,
                price_child_without_bed: 0,
            }
        },
        computed: {
            lastRowPrivate () {
                return Object.keys(this.form.ratesPrivate).length - 1
            },
            lastRowShared () {
                return Object.keys(this.form.ratesShared).length - 1
            }
        },
        created () {
            this.category_id = this.$route.params.category_id
            this.$root.$on('updateCategory', (payload) => {
                this.category_id = payload.categoryId
                this.getRatesByType()
            })
            this.getRatesByType()
        },
        mounted: function () {
            API.get('package/plan_rates/' + this.$route.params.package_plan_rate_id + '?lang=' + localStorage.getItem('lang'))
                .then((result) => {
                    let rate_plan = result.data.data
                    if (rate_plan.service_type.code == 'SIM') {
                        this.showSIM = true
                        this.showPC = false
                    } else {
                        this.showSIM = false
                        this.showPC = true
                    }
                }).catch(() => {
                this.loading = false
            })
        },
        methods: {
            updateRates () {
                this.loadingUpdate = 1
                API.get(window.origin + '/prices?category_id=' + this.category_id).then((result) => {
                    this.getRatesByType()
                    this.$notify({
                        group: 'main',
                        type: 'success',
                        title: this.$t('global.modules.package'),
                        text: this.$t('packages.rates_updated')
                    })
                    console.log('Tarifas actualizadas')
                    this.loadingUpdate = 0
                    console.log(result)
                }).catch((e) => {
                    console.log(e)
                })
            },
            getRatesByType: function () {
                this.loading = true
                this.form.ratesPrivate = []
                this.form.ratesShared = []
                API.get('/package/package_dynamic_rates/' + this.category_id).then((result) => {
                    this.loading = false
                    let rates = result.data.data
                    if (rates.private && rates.private.length > 0) {
                        this.showPricesCost = true
                        this.form.ratesPrivate = this.formatRatesShowByType(rates.private)
                        rates.private.forEach((rate, index) => {
                            if (rate.pax_to === 2 && rate.pax_from === 2) {
                                this.price_child_with_bed = rate.child_with_bed
                                this.price_child_without_bed = rate.child_without_bed
                            }
                        })
                    } else {
                        this.showPricesCost = false
                        this.addRowRatesByType(2)
                    }
                    if (rates.shared && rates.shared.length > 0) {
                        this.showPricesCostShared = true
                        this.form.ratesShared = this.formatRatesShowByType(rates.shared)
                        rates.shared.forEach((rate, index) => {
                            if (rate.pax_to === 2 && rate.pax_from === 2) {
                                this.price_child_with_bed = rate.child_with_bed
                                this.price_child_without_bed = rate.child_without_bed
                            }
                        })
                    } else {
                        this.showPricesCostShared = false
                        this.addRowRatesByType(1)
                    }
                }).catch(() => {
                    this.loading = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.package'),
                        text: this.$t('global.error.messages.connection_error')
                    })
                })
            },
            addRowRatesByType: function (type) {
                let from = 1
                let to = 1

              for(let i=0; i<this.quantityRow; i++)
              {
                if (type === 2) { //Privado
                  if (this.form.ratesPrivate.length > 0) {
                    let ultimo = this.form.ratesPrivate[this.form.ratesPrivate.length - 1]
                    from = parseInt(ultimo[0].pax_to) + 1
                    to = from
                  }
                  this.form.ratesPrivate.push(
                    [
                      { id: '', pax_from: from, pax_to: to, simple: 0, double: 0, triple: 0 },
                    ]
                  )
                } else { // Compartido
                  if (this.form.ratesShared.length > 0) {
                    let ultimo = this.form.ratesShared[this.form.ratesShared.length - 1]
                    from = parseInt(ultimo[0].pax_to) + 1
                    to = from
                  }
                  this.form.ratesShared.push(
                    [
                      { id: '', pax_from: from, pax_to: to, simple: 0, double: 0, triple: 0 },
                    ]
                  )
                }
              }
            },
            formatRatesShowByType: function (rates) {
                let arrayNew = []
                rates.forEach((rate, index) => {
                    arrayNew.push(
                        [
                            {
                                id: rate.id,
                                pax_from: rate.pax_from,
                                pax_to: rate.pax_to,
                                simple: rate.simple,
                                double: rate.double,
                                triple: rate.triple,
                            },
                        ]
                    )
                })
                return arrayNew
            },
            validateBeforeAdd: function (type) {
                let form = (type === 1) ? 'formShared' : 'formPrivate'
                this.$validator.validateAll(form).then((result) => {
                    if (result) {
                        this.addRowRatesByType(type)
                        this.$validator.reset() //solution
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.package'),
                            text: this.$t('global.error.information_complete')
                        })
                        this.loading = false
                    }

                })
            },
            removeRowByType: function (index, type) {
              let _continue = true

              for(let i=0; i<this.quantityRow; i++)
              {
                if(_continue)
                {
                  if (type === 2) {
                    if (this.form.ratesPrivate[index][0].id === '') {
                      this.form.ratesPrivate.splice(index, 1)
                    } else {
                      this.removeRateByType(this.form.ratesPrivate[index][0].id)
                    }

                    if (this.form.ratesPrivate.length == 1)
                    {
                      _continue = false
                    }
                  } else {
                    if (this.form.ratesShared[index][0].id === '') {
                      this.form.ratesShared.splice(index, 1)
                    } else {
                      this.removeRateByType(this.form.ratesShared[index][0].id)
                    }

                    if (this.form.ratesShared.length == 1)
                    {
                      _continue = false
                    }
                  }
                  index -= 1;
                }
              }
            },
            validateBeforeSave: function (type) {
                let form = (type === 1) ? 'formShared' : 'formPrivate'
                this.$validator.validateAll(form).then((result) => {
                    if (result) {
                        this.submitByType(type)
                        this.$validator.reset() //solution
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.package'),
                            text: this.$t('global.error.information_complete')
                        })
                        this.loading = false
                    }
                })
            },
            submitByType: function (type) {
                let dataRate = (type === 1) ? this.form.ratesShared : this.form.ratesPrivate
                this.loading = true
                API({
                    method: 'post',
                    url: 'package/package_dynamic_rates',
                    data: {
                        'package_plan_rate_category_id': this.category_id,
                        'service_type_id': type,
                        'rates': dataRate
                    }
                }).then((result) => {
                    this.loading = false
                    if (result.data.success === false) {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.package'),
                            text: this.$t('global.error.save')
                        })
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'success',
                            title: this.$t('global.modules.package'),
                            text: this.$t('global.success.save')
                        })
                        // this.updateRates()
                        this.getRatesByType()

                    }
                })
            },
            removeRateByType: function (id) {
                this.loading = true
                API({
                    method: 'delete',
                    url: 'package/package_dynamic_rates/' + id,
                }).then((result) => {
                    this.loading = false
                    if (result.data.success === false) {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.package'),
                            text: this.$t('global.error.save')
                        })
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'success',
                            title: this.$t('global.modules.services'),
                            text: this.$t('global.success.save')
                        })
                        this.getRatesByType()
                    }
                })
            }
        }

    }
</script>

<style scoped>

</style>
