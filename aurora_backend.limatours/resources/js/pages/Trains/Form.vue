<template>
    <div class="row">
        <div class="col-sm-12">
            <form @submit.prevent="validateBeforeSubmit">
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <div class="col-4">
                            <label>Operador</label>
                            <div class="col-sm-12 p-0">
                                <select data-vv-as="train" class="form-control"
                                        data-vv-name="train"
                                        name="train"
                                        @change="setRoutesAndClasses()"
                                        v-model="trainSelected"
                                        v-validate="'required'">
                                    <option :value="t.id" v-for="t in trains">{{ t.name }}</option>
                                </select>
                                <span class="invalid-feedback-select" v-show="errors.has('train')">
                                    <span>{{ errors.first('train') }}</span>
                                </span>
                            </div>
                        </div>
                        <div class="col-4">
                            <label>Ruta</label>
                            <div class="col-sm-12 p-0">
                                <select data-vv-as="train_route" class="form-control"
                                        data-vv-name="train_route" :disabled="!(train_routes.length)"
                                        name="train_route"
                                        v-model="trainRouteSelected"
                                        v-validate="'required'">
                                    <option :value="r.id" v-for="r in train_routes">{{ r.name }}</option>
                                </select>
                                <span class="invalid-feedback-select" v-show="errors.has('train_route')">
                                    <span>{{ errors.first('train_route') }}</span>
                                </span>
                            </div>
                        </div>
                        <div class="col-4">
                            <label>Clase</label>
                            <div class="col-sm-12 p-0">
                                <select data-vv-as="train_class" class="form-control"
                                        data-vv-name="train_class" :disabled="!(train_classes.length)"
                                        name="train_class"
                                        v-model="trainClassSelected"
                                        v-validate="'required'">
                                    <option :value="c.id" v-for="c in train_classes">{{ c.name }}</option>
                                </select>
                                <span class="invalid-feedback-select" v-show="errors.has('train_class')">
                                    <span>{{ errors.first('train_class') }}</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-form-group form-group">
                    <div class="form-row">

                        <div class="col-sm-2">
                            <label>{{ $t('services.country') }}</label>
                            <div class="col-sm-12 p-0">
                                <v-select :options="countries"
                                          :value="form.country_id"
                                          @input="countryChange"
                                          autocomplete="true"
                                          data-vv-as="country"
                                          data-vv-name="country"
                                          name="country"
                                          v-model="countrySelected"
                                          v-validate="'required'">
                                </v-select>
                                <span class="invalid-feedback-select" v-show="errors.has('country')">
                                <span>{{ errors.first('country') }}</span>
                            </span>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <label>{{ $t('services.state') }}</label>
                            <div class="col-sm-12 p-0">
                                <v-select :options="states"
                                          :value="form.state_id"
                                          @input="stateChange"
                                          autocomplete="true"
                                          data-vv-as="state"
                                          data-vv-name="state"
                                          v-model="stateSelected"
                                          v-validate="'required'">
                                </v-select>
                                <span class="invalid-feedback-select" v-show="errors.has('state')">
                                    <span>{{ errors.first('state') }}</span>
                                </span>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <label>{{ $t('services.city') }}</label>
                            <div class="col-sm-12 p-0">
                                <v-select :options="cities"
                                          :value="form.city_id"
                                          @input="cityChange"
                                          autocomplete="true"
                                          data-vv-as="city"
                                          data-vv-name="city"
                                          v-model="citySelected">
                                </v-select>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <label>{{ $t('global.types.district') }}</label>
                            <div class="col-sm-12 p-0">
                                <v-select :options="districts"
                                          :value="form.district_id"
                                          @input="districtChange"
                                          autocomplete="true"
                                          data-vv-as="district"
                                          data-vv-name="district"
                                          v-model="districtSelected">
                                </v-select>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <label>{{ $t('services.zone') }}</label>
                            <div class="col-sm-12 p-0">
                                <v-select :options="zones"
                                          :value="form.zone_id"
                                          @input="zoneChange"
                                          autocomplete="true"
                                          data-vv-as="zone"
                                          data-vv-name="zone"
                                          v-model="zoneSelected">
                                </v-select>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="b-form-group form-group">
                    <div class="form-row">

                        <label class="col-2 col-form-label">{{ $t('services.service_code_aurora') }}</label>

                        <div class="col-2">
                            <input class="form-control" type="text" v-model="form.aurora_code">
                        </div>

                        <label class="col-2 col-form-label">{{ $t('services.equivalence_aurora') }}</label>

                        <div class="col-2">
                            <input class="form-control" type="text" v-model="form.equivalence_aurora">
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-sm-6">
            <div slot="footer">
                <img src="/images/loading.svg" v-if="loading" width="40px"/>
                <button @click="validateBeforeSubmit" class="btn btn-success" type="submit" v-if="!loading">
                    <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                    {{$t('global.buttons.submit')}}
                </button>
                <button @click="cancelForm" class="btn btn-danger" type="reset" v-if="!loading">
                    {{$t('global.buttons.cancel')}}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
    import { API } from './../../api'
    import BFormGroup from 'bootstrap-vue/es/components/form-group/form-group'
    import BFormRadio from 'bootstrap-vue/es/components/form-radio/form-radio'
    import BFormRadioGroup from 'bootstrap-vue/es/components/form-radio/form-radio-group'
    import Multiselect from 'vue-multiselect'
    import vSelect from 'vue-select'
    import 'vue-select/dist/vue-select.css'
    import { Switch as cSwitch } from '@coreui/vue'

    export default {
        components: {
            BFormGroup,
            BFormRadio,
            BFormRadioGroup,
            vSelect,
            cSwitch,
            Multiselect
        },
        data: () => {
            return {
                loading: false,
                formAction: 'post',
                trains: [],
                trainSelected: '',
                states: [],
                cities: [],
                districts: [],
                zones: [],
                countries: [],
                countrySelected: [],
                zoneSelected: [],
                districtSelected: [],
                citySelected: [],
                stateSelected: [],
                train_routes: [],
                trainRouteSelected: '',
                train_classes: [],
                trainClassSelected: '',
                form: {
                    country_id: '',
                    state_id: '',
                    district_id: '',
                    city_id: '',
                    zone_id: '',
                    aurora_code: "",
                    equivalence_aurora: "",
                    train_rail_route_id: "",
                    train_train_class_id: ""
                }
            }
        },
        mounted () {

            //trains
            API.get('/trains')
                .then((result) => {
                    this.trains = result.data.data

                }).catch(() => {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('services.error.messages.name'),
                    text: this.$t('services.error.messages.connection_error')
                })
            })

            //countries
            API.get('/country/selectbox?lang=' + localStorage.getItem('lang'))
                .then((result) => {
                    let c = result.data.data
                    c.forEach((country) => {
                        this.countries.push({
                            label: country.translations[0].value,
                            code: country.translations[0].object_id
                        })
                        if( this.$route.params.id === undefined && country.translations[0].object_id === 89 ){
                            this.countrySelected.push({
                                code : country.translations[0].object_id,
                                label : country.translations[0].value
                            })
                            this.loadState(89)
                        }
                    })

                }).catch(() => {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('services.error.messages.name'),
                    text: this.$t('services.error.messages.connection_error')
                })
            })

            if (this.$route.params.id !== undefined) {
                API.get('/train_templates/' + this.$route.params.id )
                    .then((result) => {
                        this.form = result.data.data
                        this.form.aurora_code = result.data.data.aurora_code
                        this.form.equivalence_aurora = result.data.data.equivalence_aurora
                        this.trainSelected = result.data.data.train_rail_route.train_id
                        this.setRoutesAndClasses()
                        this.countrySelected.push({
                            code: result.data.data.country_id,
                            label: result.data.data.country.translations[0].value
                        })
                        this.stateSelected.push({
                            code: result.data.data.state_id,
                            label: result.data.data.state.translations[0].value
                        })
                        this.loadState(result.data.data.country_id)
                        this.citySelected.push({
                            code: result.data.data.city_id,
                            label: result.data.data.city.translations[0].value
                        })
                        this.loadCity(result.data.data.state_id)
                        this.zoneSelected.push({
                            code: result.data.data.zone_id,
                            label: result.data.data.zone.translations[0].value
                        })
                        this.loadZone(result.data.data.city_id)
                        this.districtSelected.push({
                            code: result.data.data.district_id,
                            label: result.data.data.district.translations[0].value
                        })
                        this.loadDistrict(result.data.data.city_id)
                        this.formAction = 'put'
                    })
            }
        },
        methods: {
            countryChange (value) {
                this.country = value
                if (this.country != null) {
                    if (this.form.country_id !== this.country.code) {
                        this.form.state_id = ''
                        this.form.city_id = ''
                        this.form.zone_id = ''
                        this.zoneSelected = []
                        this.citySelected = []
                        this.stateSelected = []
                    }
                    this.form.country_id = this.country.code
                    this.loadState(this.country.code)
                } else {
                    this.form.state_id = ''
                    this.form.city_id = ''
                    this.form.zone_id = ''
                    this.zoneSelected = []
                    this.citySelected = []
                    this.stateSelected = []
                }
            },
            stateChange: function (value) {
                this.state = value
                if (this.state != null) {
                    if (this.form.state_id !== this.state.code) {
                        this.form.city_id = ''
                        this.form.zone_id = ''
                        this.zoneSelected = []
                        this.citySelected = []
                    }
                    this.form.state_id = this.state.code
                    this.loadCity(this.state.code)
                } else {
                    this.form.city_id = ''
                    this.form.zone_id = ''
                    this.zoneSelected = []
                    this.citySelected = []
                }
            },
            cityChange: function (value) {
                this.city = value
                if (this.city != null) {
                    if (this.form.city_id !== this.city.code) {
                        this.form.zone_id = ''
                        this.zoneSelected = []
                    }
                    this.form.city_id = this.city.code
                    if (this.city.code > 0) {
                        this.loadZone(this.city.code)
                        this.loadDistrict(this.city.code)
                    }
                } else {
                    this.form.city_id = ''
                    this.zoneSelected = []
                }
            },
            zoneChange: function (value) {
                this.zone = value
                if (this.zone !== null) {
                    this.form.zone_id = this.zone.code
                } else {
                    this.form.zone_id = ''
                    this.zoneSelected = []
                }
            },
            districtChange: function (value) {
                this.district = value
                if (this.district !== null) {
                    this.form.district_id = this.district.code
                } else {
                    this.form.district_id = ''
                    this.districtSelected = []
                }
            },
            loadState (codecountry) {
                if (codecountry > 0) {
                    this.states = []
                    this.cities = []
                    this.zones = []
                    this.districts = []

                    API.get('/state/getstates/' + codecountry + '/' + localStorage.getItem('lang'))
                        .then((result) => {
                            let departamentos = result.data.data
                            departamentos.forEach((state) => {
                                this.states.push({
                                    label: state.translations[0].value,
                                    code: state.translations[0].object_id
                                })
                                if( this.$route.params.id === undefined && state.translations[0].object_id === 1603 ){
                                    this.stateSelected.push({
                                        code: state.translations[0].object_id,
                                        label: state.translations[0].value,
                                    })
                                    this.loadCity(state.translations[0].object_id)
                                }
                            })
                        }).catch(() => {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.services'),
                            text: this.$t('global.error.messages.connection_error')
                        })
                    })
                }
            },
            loadCity (codestate) {
                if (codestate > 0) {

                    this.cities = []
                    this.zones = []
                    this.districts = []
                    API.get('/city/getcities/' + codestate + '/' + localStorage.getItem('lang'))
                        .then((result) => {
                            let cuidades = result.data.data
                            cuidades.forEach((city) => {
                                this.cities.push({
                                    label: city.translations[0].value,
                                    code: city.translations[0].object_id
                                })
                            })
                        }).catch(() => {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.services'),
                            text: this.$t('global.error.messages.connection_error')
                        })
                    })
                }
            },
            loadZone (codecity) {
                this.zones = []
                //this.form.zone_id=''
                API.get('/zone/getzones/' + codecity + '/' + localStorage.getItem('lang'))
                    .then((result) => {
                        let zonas = result.data.data
                        zonas.forEach((zone) => {
                            this.zones.push({
                                label: zone.translations[0].value,
                                code: zone.translations[0].object_id
                            })
                        })

                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.services'),
                        text: this.$t('global.error.messages.connection_error')
                    })
                })
            },
            loadDistrict (codecity) {
                this.districts = []
                API.get('/district/getdistricts/' + codecity + '/' + localStorage.getItem('lang'))
                    .then((result) => {
                        let districts = result.data.data
                        districts.forEach((zone) => {
                            this.districts.push({
                                label: zone.translations[0].value,
                                code: zone.translations[0].object_id
                            })
                        })

                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.services'),
                        text: this.$t('global.error.messages.connection_error')
                    })
                })
            },
            setRoutesAndClasses () {

                //train routes
                API.get('/train/' + this.trainSelected + '/routes')
                    .then((result) => {
                        this.train_routes = result.data.data
                        if (result.data.data.length == 0) {
                            this.trainRouteSelected = ''
                        } else {
                            if( this.form.train_rail_route_id != '' ){
                                this.trainRouteSelected = this.form.train_rail_route_id
                            }
                        }
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('services.error.messages.name'),
                        text: this.$t('services.error.messages.connection_error')
                    })
                })
                //train classes
                API.get('/train/' + this.trainSelected + '/classes')
                    .then((result) => {
                        this.train_classes = result.data.data
                        if (result.data.data.length == 0) {
                            this.trainClassSelected = ''
                        } else {
                            if( this.form.train_train_class_id != '' ){
                                this.trainClassSelected = this.form.train_train_class_id
                            }
                        }
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('services.error.messages.name'),
                        text: this.$t('services.error.messages.connection_error')
                    })
                })
            },
            cancelForm () {
                this.$router.push({ path: '/trains/list' })
            },
            validateBeforeSubmit () {

                if( this.form.aurora_code == '' ){
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Trenes',
                        text: this.$t('packages.error.messages.information_complete')
                    })
                    this.loading = false
                    return
                }

                this.$validator.validateAll().then((result) => {
                    if (result) {
                        this.submit()

                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Trenes',
                            text: this.$t('packages.error.messages.information_complete')
                        })
                        this.loading = false
                    }
                })
            },
            submit () {

                this.loading = true

                this.form.train_rail_route_id = this.trainRouteSelected
                this.form.train_train_class_id = this.trainClassSelected

                if(this.formAction == 'post'){
                    API.post(
                        'train_templates',
                        this.form
                    )
                        .then((result) => {
                            if (result.data.success === true) {
                                this.$router.push('/trains/' + result.data.object_id + '/manage_train')
                            } else {
                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: 'Trains',
                                    text: result.data.message
                                })

                                this.loading = false
                            }
                        })

                } else {

                    API.put(
                        'train_templates/' + this.$route.params.id,
                        this.form
                    )
                        .then((result) => {
                            if (result.data.success === true) {
                                this.$router.push('/trains/list')
                            } else {
                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: 'Trains',
                                    text: result.data.message
                                })

                                this.loading = false
                            }
                        })

                }
            }
        },
        filters: {
            capitalize: function (value) {
                if (!value) return ''
                value = value.toString().toLowerCase()
                return value.charAt(0).toUpperCase() + value.slice(1)
            }
        }
    }
</script>

<style lang="stylus">

</style>
