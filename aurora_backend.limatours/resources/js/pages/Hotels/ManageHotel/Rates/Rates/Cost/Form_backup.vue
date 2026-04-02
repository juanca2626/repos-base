<template>
    <div class="container">
        <div class="row">
            <h2>{{$t('hotelsmanagehotelratesratescost.rate')}}: {{form.name}}</h2>
        </div>
        <div class="row cost-table-container">

            <div class="container" v-if="step === 1">
                <div class="row table-options mt-4">
                    <div class="col-2 small-title">
                        <span class="">Tipo de Tarifa</span>
                    </div>
                    <div class="col-1 small-title">
                        <span class="">Allotment</span>
                    </div>
                    <div class="col-1 small-title">
                        <span class="">Tarifario</span>
                    </div>
                    <div class="col-2 small-title">
                        <span class="">Impuestos</span>
                    </div>
                    <div class="col-2 small-title">
                        <span class="">Servicios</span>
                    </div>
                    <div class="col-2 small-title">
                        <span class="">Multipropiedad</span>
                    </div>
                    <div class="col-2 small-title">
                        <span class="">Promoción</span>
                    </div>
                    <div class="col-2">
                        <b-form-select
                            :options="rateTypes"
                            :state="validateState('ratesTypes')"
                            ref="ratesTypes"
                            v-model="form.type"
                            v-validate="{ required: true }">
                        </b-form-select>
                    </div>
                    <div class="col-1" style="padding: 0.75rem;">
                        <c-switch
                            :uncheckedValue="false"
                            :value="true"
                            class="mx-1"
                            color="primary"
                            v-model="form.allotment"
                            variant="pill"
                        />
                    </div>
                    <div class="col-1" style="padding: 0.75rem;">
                        <c-switch
                            :uncheckedValue="false"
                            :value="true"
                            class="mx-1"
                            color="primary"
                            v-model="form.rate"
                            variant="pill"
                        />
                    </div>
                    <div class="col-2">
                        <c-switch
                            :uncheckedValue="false"
                            :value="true"
                            class="mx-1"
                            color="primary"
                            v-model="form.taxes"
                            variant="pill"
                        />
                    </div>
                    <div class="col-2">
                        <c-switch
                            :uncheckedValue="false"
                            :value="true"
                            class="mx-1"
                            color="primary"
                            v-model="form.services"
                            variant="pill"
                        />
                    </div>
                    <div class="col-2">
                        <c-switch
                            :uncheckedValue="false"
                            :value="true"
                            class="mx-1"
                            color="primary"
                            v-model="form.timeshares"
                            variant="pill"
                        />
                    </div>
                    <div class="col-2">
                        <c-switch
                            :uncheckedValue="false"
                            :value="true"
                            class="mx-1"
                            color="primary"
                            v-model="form.promotions"
                            variant="pill"
                        />
                    </div>
                </div>

                <div class="row">
                    <div class="b-form-group form-group col-4">
                        <div class="form-row">
                            <label class="col-form-label" for="name">{{ $t('global.name') }}</label>
                            <input
                                :class="{'form-control':true, 'is-valid':errors.has('name'), 'is-invalid':errors.has('name') }"
                                data-vv-validate-on="none"
                                id="name"
                                name="name"
                                type="text"
                                v-model="form.name"
                                v-validate="'required'">
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('name')"/>
                                <span v-show="errors.has('name')">{{ errors.first('name') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="b-form-group form-group col-2">
                        <div class="form-row">
                            <label class="col-form-label" for="code">
                                {{ $t('hotelsmanagehotelratesratescost.code')}}
                            </label>
                            <input :class="{'form-control':true, 'is-valid':errors.has('name'), 'is-invalid':errors.has('name') }"
                                   data-vv-validate-on="none"
                                   id="code"
                                   name="code"
                                   type="text"
                                   v-validate="'required'"
                                   v-model="form.code">
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('code')"/>
                                <span v-show="errors.has('name')">{{ errors.first('code') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="b-form-group form-group col-6">
                        <div class="form-row">
                            <label class="col-12 col-form-label" for="meals">
                                {{ $t('hotelsmanagehotelratesratescost.meal_name') }}
                            </label>
                            <v-select
                                class="col-12"
                                :options="meals"
                                :resetOnOptionsChange="true"
                                :selectOnTab="true"
                                autocomplete="true"
                                id="meals"
                                ref="mealTypeahead"
                                label="text"
                                v-model="form.meal">
                            </v-select>
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;"
                                 v-show="customErrors.meals">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']" style="margin-left: 5px;"/>
                                <span>{{ $t('hotelsmanagehotelratesratescost.error.required') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <b-tabs class="mb-5">
                    <b-tab :key="language.id" :title="language.name" ref="tabLanguage" @click="set_language(language.id)"
                           v-for="language in languages">
                        <div class="row">
                            <div class="col-sm-12 b-form-group form-group">
                                <div class="form-row">
                                    <div class="col-sm-6">
                                        <label for="commercial_name">
                                            {{ $t('hotelsmanagehotelratesratescost.commercial_name')}}
                                        </label>
                                        <input
                                            :class="{'form-control':true, 'is-valid':errors.has('commercial_name'), 'is-invalid':errors.has('commercial_name') }"
                                            data-vv-validate-on="none"
                                            id="commercial_name"
                                            name="commercial_name"
                                            type="text"
                                            v-model="form.translations[currentLang].commercial_name"
                                            v-validate="'required'">
                                        <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                            <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                               style="margin-left: 5px;"
                                                               v-show="errors.has('commercial_name')"/>
                                            <span
                                                v-show="errors.has('commercial_name')">{{ errors.first('commercial_name') }}</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="no_show">No Show</label>
                                            <input type="text" id="no_show" v-model="form.translations_no_show[currentLang].no_show"
                                                   class="form-control"/>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label for="day_use">Day Use</label>
                                        <textarea name="" id="day_use" cols="15" rows="5"
                                                  v-model="form.translations_day_use[currentLang].day_use"
                                                  class="form-control"></textarea>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="notes">Notas</label>
                                            <textarea name="" id="notes" cols="15" rows="5"
                                                      v-model="form.translations_notes[currentLang].notes"
                                                      class="form-control"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </b-tab>
                </b-tabs>


                <h3>Asociar (Opcional): </h3>
                <div class="row">
                    <div class="b-form-group form-group col-4">
                        <label for="">Países</label>
                        <v-select multiple
                                  placeholder="Países"
                                  :options="countries"
                                  id="countries"
                                  autocomplete="true"
                                  data-vv-as="country"
                                  data-vv-name="country"
                                  name="country"
                                  label="name"
                                  v-model="form.countries">
                        </v-select>
                    </div>

                    <div class="b-form-group form-group col-4">
                        <label for="">Regiones</label>
                        <v-select multiple
                                  placeholder="Regiones"
                                  :options="regions"
                                  id="regions"
                                  autocomplete="true"
                                  data-vv-as="region"
                                  data-vv-name="region"
                                  name="region"
                                  label="text"
                                  v-model="form.regions">
                        </v-select>
                    </div>

                    <div class="b-form-group form-group col-4">
                        <label for="">Clientes</label>
                        <v-select multiple
                                  placeholder="Clientes"
                                  :options="clients"
                                  id="clients"
                                  autocomplete="true"
                                  data-vv-as="client"
                                  data-vv-name="client"
                                  @search="get_clients"
                                  name="client"
                                  label="label"
                                  v-model="form.clients">
                        </v-select>
                    </div>

                </div>

                <div class="row mt-4" v-if="form.promotions">
                    <div class="col-12">
                        <hr/>
                    </div>
                    <div class="col-12">
                        <h2>Promociones: (Booking Window)</h2>
                    </div>
                    <div class="col-12">
                        <div :key="index" class="row mt-2" v-for="(promotion, index) in form.promotionsData">
                            <div class="col-1 my-auto">
                                Desde:
                            </div>
                            <div class="col-3">
                                <date-picker
                                    :config="datePickerOptions"
                                    :id="'promotion-'+index+'-from'"
                                    :name="'promotion-'+index+'-from'"
                                    placeholder="inicio: DD/MM/YYYY"
                                    :ref="'promotion-'+index+'-from'"
                                    v-model="form.promotionsData[index].from"
                                >
                                </date-picker>
                            </div>
                            <div class="col-1 my-auto">
                                Hasta:
                            </div>
                            <div class="col-3">
                                <date-picker
                                    :config="datePickerOptions"
                                    :id="'promotion-'+index+'-to'"
                                    :name="'promotion-'+index+'-to'"
                                    placeholder="inicio: DD/MM/YYYY"
                                    :ref="'promotion-'+index+'-to'"
                                    v-model="form.promotionsData[index].to"
                                >
                                </date-picker>
                            </div>
                            <div class="col-1">
                                <button @click="addPromotion" class="btn btn-primary">
                                    +
                                </button>
                            </div>
                            <div class="col-1" v-if="index > 0">
                                <button @click="removePromotion(index)" class="btn btn-primary">
                                    -
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-4" v-if="form.promotions">
                    <div class="col-12">
                        <hr/>
                    </div>
                </div>
                <div class="row text-right mt-3">
                    <div class="col-12">
                        <img src="/images/loading.svg" v-if="loading" width="40px"/>
                        <button @click="submit(true)" class="btn btn-success" type="button" v-if="!loading">
                            <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                            {{$t('global.buttons.submit')}}
                        </button>
                        <router-link to="../" v-if="!loading">
                            <button class="btn btn-danger" type="reset">
                                {{$t('global.buttons.cancel')}}
                            </button>
                        </router-link>
                    </div>
                </div>
            </div>
            <LayoutStep2
                :formAction="formAction"
                :hotelID="hotelID"
                :options="optionsStepTwo"
                :ratePlanID="ratePlanID"
                v-if="step === 2"
            ></LayoutStep2>
        </div>
        <div class="row mt-4">
            <div class="col-12">
                <hr/>
            </div>
            <div class="col-12 text-right">
                <button @click="returnHome()" class="btn btn-secondary" type="button" v-if="step === 2">
                    {{$t('hotelsmanagehotelratesratescost.buttons.goback')}}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
    import { API } from './../../../../../../api'
    import VueBootstrapTypeahead from 'vue-bootstrap-typeahead'
    import BFormGroup from 'bootstrap-vue/es/components/form-group/form-group'
    import datePicker from 'vue-bootstrap-datetimepicker'
    import 'pc-bootstrap4-datetimepicker/build/css/bootstrap-datetimepicker.css'
    import BFormSelect from 'bootstrap-vue/src/components/form-select/form-select'
    import Progress from 'bootstrap-vue/src/components/progress/progress'
    import CSwitch from '@coreui/vue/src/components/Switch/Switch'
    import TableClient from './../../../../../../components/TableClient'
    import moment from 'moment'
    import vSelect from 'vue-select'
    import 'vue-select/dist/vue-select.css'
    import LayoutStep2 from './Step2/Layout'

    export default {
        components: {
            'table-client': TableClient,
            VueBootstrapTypeahead,
            'b-form-group': BFormGroup,
            datePicker,
            BFormSelect,
            'b-progress': Progress,
            CSwitch,
            vSelect,
            LayoutStep2
        },
        data: () => {
            return {
                countries: [],
                regions: [],
                clients: [],
                hotelID: '',
                ratePlanID: '',
                optionsStepTwo: {
                    // rooms: [],
                    // historyData: [],
                    ratePlanName: '',
                    promotions: false
                },
                loading: false,
                languages: [],
                showError: false,
                currentLang: 1,
                invalidError: false,
                countError: 0,
                policies: [],
                policy: null,
                policySearch: null,
                policyDays: {
                    all: false,
                    1: false,
                    2: false,
                    3: false,
                    4: false,
                    5: false,
                    6: false,
                    7: false
                },
                meals: [],
                meal: null,
                mealSearch: null,
                datePickerFromOptions: {
                    format: 'DD/MM/YYYY',
                    useCurrent: false,
                    locale: localStorage.getItem('lang')
                },
                datePickerToOptions: {
                    format: 'DD/MM/YYYY',
                    useCurrent: false,
                    locale: localStorage.getItem('lang')
                },
                rooms: [],
                rateTypes: [],
                formAction: 'post',
                step: 1,
                currentRooms: [],
                defaultRooms: [],
                table: {
                    columns: ['room', 'period', 'policy', 'adult', 'child', 'infant', 'extra'],
                    options: {
                        headings: {
                            room: 'Habitacion',
                            period: 'Periodo',
                            policy: 'Política',
                            adult: 'Adulto US$',
                            kid: 'Niño US$',
                            infant: 'Infante US$',
                            extra: 'Extra US$'
                        },
                        sortable: [],
                        filterable: false
                    }
                },
                customErrors: {
                    meals: false,
                    policy: false
                },
                currentRoomIndex: null,
                save: {
                    counter: 0,
                    max: 100
                },
                currentTime: 0,
                historyData: [],
                historySearch: null,
                historySet: null,
                datePickerOptions: {
                    format: 'DD/MM/YYYY',
                    useCurrent: false,
                    locale: localStorage.getItem('lang')
                },
                form: {
                    name: '',
                    code: '',
                    meal_id: '',
                    type: '',
                    translations: {
                        '1': {
                            'id': '',
                            'commercial_name': ''
                        }
                    },
                    translations_no_show: {
                        '1': {
                            'id': '',
                            'no_show': ''
                        }
                    },
                    translations_notes: {
                        '1': {
                            'id': '',
                            'notes': ''
                        }
                    },
                    translations_day_use: {
                        '1': {
                            'id': '',
                            'day_use': ''
                        }
                    },
                    allotment: false,
                    rate: false,
                    taxes: false,
                    services: false,
                    timeshares: false,
                    promotions: false,
                    promotionsData: [
                        {
                            from: moment().format('DD/MM/YYYY'),
                            to: moment().format('DD/MM/YYYY')
                        }],
                    no_show: '',
                    notas: '',
                    day_use: ''
                },
                formTwo: {
                    data: [],
                    policy_id: ''
                },
                language_choose: "",
            }
        },
        mounted: function () {
            this.hotelID = parseInt(this.$route.params.hotel_id)
            API.get('/languages/')
                .then((result) => {
                    this.languages = result.data.data
                    this.currentLang = result.data.data[0].id

                    let form = {
                        translations: {},
                        translations_no_show: {},
                        translations_notes: {},
                        translations_day_use: {}
                    }

                    let languages = this.languages

                    languages.forEach((value) => {
                        if (value.id == 1) {
                            form.translations_no_show[value.id] = {
                                id: '',
                                no_show: '100% + impuestos de ley'
                            }
                            form.translations_notes[value.id] = {
                                id: '',
                                notes: ''
                            }
                            form.translations_day_use[value.id] = {
                                id: '',
                                day_use: 'Por favor contacte a su especialista para mas detalles'
                            }
                        }
                        if (value.id == 2) {
                            form.translations_no_show[value.id] = {
                                id: '',
                                no_show: '100% fee + 18% Tax'
                            }
                            form.translations_notes[value.id] = {
                                id: '',
                                notes: ''
                            }
                            form.translations_day_use[value.id] = {
                                id: '',
                                day_use: 'Please contact your specialist for more details'
                            }
                        }
                        if (value.id == 3) {
                            form.translations_no_show[value.id] = {
                                id: '',
                                no_show: '100% + taxa de 18% de imposto'
                            }
                            form.translations_notes[value.id] = {
                                id: '',
                                notes: ''
                            }
                            form.translations_day_use[value.id] = {
                                id: '',
                                day_use: 'Entre em contato com seu especialista para obter mais detalhes'
                            }
                        }
                        form.translations[value.id] = {
                            id: '',
                            commercial_name: ''
                        }

                    })
                    if (this.$route.params.rate_id !== undefined) {
                        this.ratePlanID = this.$route.params.rate_id
                        API.get('rates/cost/' + this.$route.params.hotel_id + '/' + this.$route.params.rate_id + '/?lang=' +
                            localStorage.getItem('lang'))
                            .then((result) => {
                                this.formAction = 'put'
                                let data = result.data.data

                                this.form = {
                                    name: data.name,
                                    code: data.code,
                                    meal_id: data.meal.id,
                                    meal: data.meal,
                                    type: data.type,
                                    translations: data.translations,
                                    translations_no_show: data.translations_no_show,
                                    translations_day_use: data.translations_day_use,
                                    translations_notes: {},
                                    rate: data.rate,
                                    allotment: data.allotment,
                                    taxes: data.taxes,
                                    services: data.services,
                                    timeshares: data.timeshares,
                                    promotions: data.promotions,
                                    promotionsData: [],

                                    countries: data.association_countries,
                                    regions: data.association_regions,
                                    clients: data.association_clients,
                                }

                                if (data.translations_notes.length == 0) {
                                    this.languages.forEach((value) => {
                                        this.form.translations_notes[value.id] = {
                                            id: '',
                                            notes: ''
                                        }
                                    })

                                    console.log(this.form.translations_notes)
                                } else {
                                    this.form.translations_notes = data.translations_notes
                                }

                                if (data.promotionsData.length === 0) {
                                    this.form.promotionsData = [{
                                        from: moment().format('DD/MM/YYYY'),
                                        to: moment().format('DD/MM/YYYY')
                                    }]
                                } else {

                                    data.promotionsData.forEach((promotion, index) => {
                                        let temp = {
                                            from: moment(promotion.promotion_from).format('DD/MM/YYYY'),
                                            to: moment(promotion.promotion_to).format('DD/MM/YYYY')
                                        }

                                        this.$set(this.form.promotionsData, index, temp)
                                    })
                                }

                                this.step = 1

                                // this.form.meal = data.meal
                            })
                            .catch(() => {
                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: this.$t('hotelsmanagehotelratesratescost.error.messages.name'),
                                    text: this.$t('hotelsmanagehotelratesratescost.error.messages.connection_error')
                                })
                            })
                    }

                    this.form = { ...this.form, ...form }

                    this.currentTime = moment().unix()
                }).catch(() => {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('hotelsmanagehotelratesratescost.error.messages.name'),
                    text: this.$t('hotelsmanagehotelratesratescost.error.messages.connection_error')
                })
            })

            API.get('/meals/selectBox?lang=' + localStorage.getItem('lang'))
                .then((result) => {
                    this.meals = result.data.data
                }).catch(() => {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('hotelsmanagehotelratesratescost.error.messages.name'),
                    text: this.$t('hotelsmanagehotelratesratescost.error.messages.connection_error')
                })
            })

            API.get('/ratesplanstypes/selectBox?lang=' + localStorage.getItem('lang'))
                .then((result) => {
                    this.rateTypes = result.data.data
                }).catch(() => {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('hotelsmanagehotelratesratescost.error.messages.name'),
                    text: this.$t('hotelsmanagehotelratesratescost.error.messages.connection_error')
                })
            })
            this.get_countries()
            this.get_regions()
        },
        methods: {
            get_clients (search, loading) {
                loading(true)
                API.get('clients/selectBox/by/name?query=' + search)
                    .then((result) => {
                        loading(false)
                        let clients_ = []
                        result.data.data.forEach((c) => {
                            clients_.push({
                                label: c.name,
                                code: c.id
                            })
                        })
                        this.clients = clients_
                    }).catch(() => {
                    loading(false)
                })
            },
            get_regions () {
                API({
                    method: 'GET',
                    url: 'markets/selectbox?lang=' + localStorage.getItem('lang')
                }).then((result) => {
                    this.regions = result.data.data
                    this.loading = false
                })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            get_countries () {
                API({
                    method: 'GET',
                    url: 'country/selectbox?lang=' + localStorage.getItem('lang')
                }).then((result) => {
                    result.data.data.forEach((c) => {
                        c.name = '[' + c.iso + '] - ' + c.translations[0].value
                    })
                    this.countries = result.data.data
                    this.loading = false
                })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            validateState (ref) {
                // if (this.fields[ref] && (this.fields[ref].dirty || this.fields[ref].validated)) {
                //   return !this.errors.has(ref)
                // }
                // return null
            },
            submit (shallContinue) {
                this.$validator.validateAll().then(isValid => {
                    /* if (this.form.meal_id === '' || this.form.meal_id == null) {
                       this.customErrors.meals = true
                       return false
                     }
                     this.customErrors.meals = false*/

                    if (isValid) {
                        //this.loading = true
                        API({
                            method: this.formAction,
                            url: 'rates/cost/' + this.$route.params.hotel_id +
                                (this.$route.params.rate_id !== undefined ? '/' + this.$route.params.rate_id : ''),
                            data: this.form
                        }).then((result) => {
                            this.ratePlanID = parseInt(result.data.rate_plan)

                            if (shallContinue) {
                                this.optionsStepTwo.promotions = this.form.promotions
                                this.step = 2
                            } else {
                                this.$router.push('/hotels/' + this.$route.params.hotel_id + '/manage_hotel/rates/rates/cost')
                            }

                            if (this.formAction === 'put') {
                                this.optionsStepTwo = {
                                    promotions: this.form.promotions
                                }
                            }

                            this.loading = false
                        })
                            .catch((e) => {
                                console.log(e)
                            })
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$i18n.t('global.modules.ratescost'),
                            text: this.$i18n.t('hotelsmanagehotelratesratescost.error.messages.information_complete')
                        })

                        this.loading = false
                    }
                })
            },
            returnHome () {
                this.$router.push('/hotels/' + this.hotelID + '/manage_hotel/rates/rates/cost')
            },
            addPromotion () {
                this.form.promotionsData.push({
                    from: moment().format('DD/MM/YYYY'),
                    to: moment().format('DD/MM/YYYY')
                })
            },
            removePromotion (index) {
                let promotionsData = this.form.promotionsData
                promotionsData.splice(index, 1)
                this.form.promotionsData = promotionsData
            },
            set_language (currentLang) {
                this.currentLang = currentLang
            },
        }
    }
</script>

<style lang="stylus">
    .with-border
        border 1px solid #e4e7ea

    .table-days
        margin-bottom 0

        th
            text-align center
            background-color #e4e7ea

        td
            text-align center
            padding 5px 0

            .success
                color #28a745

            .danger
                color #dc3545

    .rooms-table-headers
        text-align center
        background-color #e4e7ea

    input[type="number"]::-webkit-outer-spin-button,
    input[type="number"]::-webkit-inner-spin-button
        -webkit-appearance none
        margin 0

    input[type="number"]
        -moz-appearance textfield

    .small-title
        background #2F353A
        text-align center
        color #FFFFFF
        font-weight 700
        font-size 14px
        padding 0.75rem

    .table-options
        .col-2
            padding 0.75rem
            text-align center

    .rooms-table
        input[type=number]
            padding-right 0 !important
            background none !important

</style>

