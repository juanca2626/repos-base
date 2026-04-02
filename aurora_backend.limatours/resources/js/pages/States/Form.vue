<template>
    <div class="row">
        <div class="col-sm-12">
            <form @submit.prevent="validateBeforeSubmit">
                <div class="b-form-group form-group">
                    <div class="form-row" style="margin-bottom: 5px;">
                        <label class="col-sm-2 col-form-label" for="state_name">{{ $t('states.country_name') }}</label>
                        <div class="col-sm-5">
                            <vue-bootstrap-typeahead
                                :data="countries"
                                :serializer="item => item.translations[0].value"
                                @hit="country = $event"
                                ref="countryTypeahead"
                                v-model="countrySearch"

                            />
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;" v-show="errorCountry">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']" style="margin-left: 5px;"/>
                                <span>{{ $t('states.error.required') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="state_name">{{ $t('states.state_name') }}</label>
                        <div class="col-sm-5">
                            <input :class="{'form-control':true, 'is-valid':validError, 'is-invalid':invalidError }"
                                   id="state_name" name="state_name"
                                   type="text"
                                   v-model="form.translations[currentLang].state_name" v-validate="'required'">
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;"
                                 v-show="errors.has('state_name')">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']" style="margin-left: 5px;"/>
                                <span>{{ errors.first('state_name') }}</span>
                            </div>
                        </div>
                        <select class="col-sm-1 form-control" id="lang" required size="0" v-model="currentLang">
                            <option v-bind:value="language.id" v-for="language in languages">
                                {{ language.iso }}
                            </option>
                        </select>
                    </div>
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label">ISO</label>
                        <div class="col-sm-5">
                            <input class="form-control"
                                   id="iso" name="iso"
                                   type="text"
                                   v-model="form.iso">
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-sm-6">
            <div slot="footer">
                <img src="/images/loading.svg" v-if="loading" width="40px"/>
                <button @click="validateBeforeSubmit()" class="btn btn-success" type="submit" v-if="!loading">
                    <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                    {{ $t('global.buttons.submit') }}
                </button>
                <router-link :to="{ name: 'StatesList' }" v-if="!loading">
                    <button class="btn btn-danger" type="reset">
                        {{ $t('global.buttons.cancel') }}
                    </button>
                </router-link>
            </div>
        </div>
    </div>
</template>

<script>
import {API} from './../../api'
import VueBootstrapTypeahead from 'vue-bootstrap-typeahead'

export default {
    components: {
        VueBootstrapTypeahead
    },
    data: () => {
        return {
            loading: false,
            formAction: 'post',
            countries: [],
            country: null,
            countrySearch: '',
            state: null,
            languages: [],
            currentLang: '1',
            invalidError: false,
            countError: 0,
            form: {
                country_id: null,
                iso: '',
                translations: {
                    '1': {
                        'id': '',
                        'state_name': ''
                    }
                }
            }
        }
    },
    mounted() {

        API.get('/languages/')
            .then((result) => {
                this.languages = result.data.data
                this.currentLang = result.data.data[0].id

                let form = {
                    translations: {}
                }

                let languages = this.languages

                languages.forEach((value) => {
                    form.translations[value.id] = {
                        id: '',
                        state_name: ''
                    }
                })

                if (this.$route.params.id !== undefined) {
                    API.get('/states/' + this.$route.params.id + '?lang=' + localStorage.getItem('lang'))
                        .then((result) => {
                            this.country = null
                            this.state = result.data.data
                            this.form.country_id = this.state[0].country_id
                            this.form.iso = this.state[0].iso
                            this.$refs.countryTypeahead.inputValue = this.state[0].country.translations[0].value
                            this.formAction = 'put'
                            let arrayTranslations = this.state[0].translations

                            arrayTranslations.forEach((translation) => {
                                form.translations[translation.language_id] = {
                                    id: translation.id,
                                    state_name: translation.value
                                }
                            })
                        }).catch(() => {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('states.error.messages.name'),
                            text: this.$t('states.error.messages.connection_error')
                        })
                    })
                }
                this.form = form
            }).catch(() => {
            this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('states.error.messages.name'),
                text: this.$t('states.error.messages.connection_error')
            })
        })
        API.get('/country/selectbox?lang=' + localStorage.getItem('lang'))
            .then((result) => {
                this.countries = result.data.data
            }).catch(() => {
            this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('states.error.messages.name'),
                text: this.$t('states.error.messages.connection_error')
            })
        })
    },
    computed: {
        errorCountry: function () {
            if (this.countrySearch == '' && this.form.country_id == '') {
                return true
            }
            if (this.countrySearch == null && this.form.country_id == '') {
                return false
            }
            if (this.country != null) {
                if (this.countrySearch != this.country.translations[0].value) {
                    this.country = null
                }
            } else {
                return false
            }
        },
        validError: function () {
            if (this.errors.has('state_name') == false && this.form.translations[1].state_name != '') {
                this.invalidError = false
                this.countError += 1
                return true
            } else {
                if (this.countError > 0) {
                    this.invalidError = true
                }
                return false
            }
        }
    },
    methods: {
        validateBeforeSubmit() {
            if ((this.country == null && this.formAction == 'post')) {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('states.country_name'),
                    text: this.$t('states.error.messages.country_incorrect')
                })
                return false
            }
            if ((this.country == null && this.formAction == 'put' && this.form.country_id != '' && this.countrySearch !=
                '')) {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('states.country_name'),
                    text: this.$t('states.error.messages.country_incorrect')
                })
                return false
            }
            this.$validator.validateAll().then((result) => {
                if (result) {
                    this.submit()
                } else {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.states'),
                        text: this.$t('states.error.messages.information_complete')
                    })

                    this.loading = false
                }
            })
        },
        submit() {
            if ((this.country == null) && this.formAction == 'put' && this.form.country_id != '') {

            } else {
                this.form.country_id = this.country.id
            }

            this.loading = true
            console.log('guardando...')
            console.log(this.formAction)
            API({
                method: this.formAction,
                url: 'states/' + (this.$route.params.id !== undefined ? this.$route.params.id : ''),
                data: this.form
            })
                .then((result) => {
                    if (result.data.success === true) {
                        this.$router.push('/states/list')
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.states'),
                            text: this.$t('states.error.messages.information_error')
                        })

                        this.loading = false
                    }
                }).catch(() => {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('states.error.messages.name'),
                    text: this.$t('states.error.messages.connection_error')
                })
            })
        }
    }
}
</script>

<style lang="stylus">

</style>

