<template>
    <div class="row">
        <div class="col-sm-12">
            <form @submit.prevent="validateBeforeSubmit">
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="currency_name">{{ $t('currencies.currency_name')
                            }}</label>
                        <div class="col-sm-5">
                            <input :class="{'form-control':true, 'is-valid':validError, 'is-invalid':invalidError }"
                                   id="currency_name" name="currency_name"
                                   type="text"
                                   v-model="form.translations[currentLang].currency_name" v-validate="'required'">
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('currency_name')" />
                                <span v-show="errors.has('currency_name')">{{ errors.first('currency_name') }}</span>
                            </div>
                        </div>
                        <select class="col-sm-1 form-control" id="lang" required size="0" v-model="currentLang">
                            <option v-bind:value="language.id" v-for="language in languages">
                                {{ language.iso }}
                            </option>
                        </select>
                    </div>
                </div>
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="symbol">{{ $t('currencies.symbol') }}</label>
                        <div class="col-sm-5">
                            <input class="form-control input" id="symbol" maxlength=5
                                   name="symbol"
                                   type="text"
                                   v-model="form.symbol" v-validate="'required'">
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('symbol')" />
                                <span v-show="errors.has('symbol')">{{ errors.first('symbol') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="iso">ISO</label>
                        <div class="col-sm-5">
                            <input class="form-control input" id="iso" maxlength=3 name="iso"
                                   type="text"
                                   v-model="form.iso" v-validate="'required'">
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('iso')" />
                                <span v-show="errors.has('iso')">{{ errors.first('iso') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="iso">T.C</label>
                        <div class="col-sm-5">
                            <input class="form-control input" id="tc" name="tc"
                                   @input="handleInput"
                                   type="text"
                                   v-model.number="form.exchange_rate" v-validate="'required'">
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('tc')" />
                                <span v-show="errors.has('tc')">{{ errors.first('tc') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-sm-6">
            <div slot="footer">
                <img src="/images/loading.svg" v-if="loading" width="40px" />
                <button @click="validateBeforeSubmit" class="btn btn-success" type="submit" v-if="!loading">
                    <font-awesome-icon :icon="['fas', 'dot-circle']" />
                    {{$t('global.buttons.submit')}}
                </button>
                <router-link :to="{ name: 'CurrenciesList' }" v-if="!loading">
                    <button class="btn btn-danger" type="reset">
                        {{$t('global.buttons.cancel')}}
                    </button>
                </router-link>
            </div>
        </div>
    </div>
</template>

<script>
  import { API } from './../../api'

  export default {
    data: () => {
      return {
        previousPrice:null,
        languages: [],
        currency: null,
        showError: false,
        currentLang: '1',
        invalidError: false,
        countError: 0,
        loading: false,
        formAction: 'post',
        form: {
          symbol: '',
          iso: '',
          exchange_rate: null,
          translations: {
            '1': {
              'id': '',
              'currency_name': ''
            }
          }
        }
      }
    },
    computed: {
      validError: function () {
        if ((this.errors.has('currency_name') == false && this.form.translations[1].currency_name != '') &&
          (this.errors.has('symbol') == false && this.form.symbol != '') &&
          (this.errors.has('iso') == false &&
            this.form.iso != '')) {
          this.invalidError = false
          this.countError += 1
          return true
        } else if (this.countError > 0) {
          this.invalidError = true
        }
        return false
      }
    },
    mounted: function () {
      // this.cleanFields()
      API.get('/languages/')
        .then((result) => {
          this.languages = result.data.data
          this.currentLang = result.data.data[0].id

          let languages = this.languages

          languages.forEach((value) => {
            this.form.translations[value.id] = {
              id: '',
              currency_name: ''
            }
          })

          if (this.$route.params.id !== undefined) {
            API.get('/currencies/' + this.$route.params.id)
              .then((result) => {
                this.formAction = 'put'
                let form = result.data.data
                let arrayTranslations = result.data.data.translations

                form.translations = {}

                arrayTranslations.forEach((translation) => {
                  form.translations[translation.language_id] = {
                    id: translation.id,
                    currency_name: translation.value
                  }
                })
                this.form = form
              })
          }
        })
    },
    methods: {
      handleInput (e) {
        let stringValue = e.target.value.toString()
        let regex = /^\d*(\.\d{1,2})?$/
        if(!stringValue.match(regex) && this.form.exchange_rate !== '') {
          this.form.exchange_rate = this.previousPrice
        }
        this.previousPrice = this.form.exchange_rate
      },
      validateBeforeSubmit () {
        this.$validator.validateAll().then((result) => {
          if (result) {

            this.submit()

          } else {
            this.$notify({
              group: 'main',
              type: 'error',
              title: this.$t('global.modules.currencies'),
              text: this.$t('currencies.error.messages.information_complete')
            })

            this.loading = false
          }
        })
      },
      submit () {

        this.loading = true

        API({
          method: this.formAction,
          url: 'currencies/' + (this.$route.params.id !== undefined ? this.$route.params.id : ''),
          data: this.form
        })
          .then((result) => {
            if (result.data.success === false) {

              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.currencies'),
                text: this.$t('currencies.error.messages.country_incorrect')
              })

              this.loading = false
            } else {
              this.$router.push('/currencies/list')
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('currencies.error.messages.name'),
            text: this.$t('currencies.error.messages.connection_error')
          })
        })
      }
    }
  }
</script>

<style lang="stylus">
</style>


