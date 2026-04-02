<template>
    <div class="row">
        <div class="col-sm-12">
            <form @submit.prevent="validateBeforeSubmit">
                <div class="b-form-group form-group">
                    <div class="form-row" style="margin-bottom: 5px;">
                        <label class="col-sm-2 col-form-label" for="city_name">{{ $t('cities.state_name') }}</label>
                        <div class="col-sm-5">
                            <vue-bootstrap-typeahead
                                    :data="states"
                                    :serializer="item => item.translations[0].value +' / '+item.country.translations[0].value"
                                    @hit="state = $event"
                                    ref="stateTypeahead"
                                    v-model="stateSearch"
                                    :key="updateSelect"

                            />
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;" v-show="errorState">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']" style="margin-left: 5px;"/>
                                <span>{{ $t('cities.error.required') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="city_name">{{ $t('cities.city_name') }}</label>
                        <div class="col-sm-5">
                            <input :class="{'form-control':true, 'is-valid':validError, 'is-invalid':invalidError }"
                                   id="city_name" name="city_name"
                                   type="text"
                                   v-model="form.translations[currentLang].city_name" v-validate="'required'">
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;"
                                 v-show="errors.has('city_name')">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']" style="margin-left: 5px;"/>
                                <span>{{ errors.first('city_name') }}</span>
                            </div>
                        </div>
                        <select class="col-sm-1 form-control" id="lang" required size="0" v-model="currentLang">
                            <option v-bind:value="language.id" v-for="language in languages">
                                {{ language.iso }}
                            </option>
                        </select>
                    </div>
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="city_name">{{ $t('cities.city_iso') }}</label>
                        <div class="col-sm-5">
                            <input :class="{'form-control':true }"
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
                <button @click="validateBeforeSubmit" class="btn btn-success" type="submit" v-if="!loading">
                    <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                    {{ $t('global.buttons.submit') }}
                </button>
                <router-link :to="{ name: 'CitiesList' }" v-if="!loading">
                    <button class="btn btn-danger" type="reset">
                        {{ $t('global.buttons.cancel') }}
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
        loading: false,
        formAction: 'post',
        states: [],
        state: null,
        stateSearch: '',
        updateSelect:1,
        city: null,
        languages: [],
        currentLang: '1',
        invalidError: false,
        countError: 0,
        form: {
          state_id: null,
          iso:'',
          translations: {
            '1': {
              'id': '',
              'city_name': ''
            }
          }
        }
      }
    },
    mounted () {
      API.get('/languages')
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
              city_name: ''
            }
          })

          if (this.$route.params.id !== undefined) {
            API.get('/cities/' + this.$route.params.id + '?lang=' + localStorage.getItem('lang'))
              .then((result) => {
                this.city = result.data.data
                this.state = null
                this.$refs.stateTypeahead.inputValue = this.city[0].state.translations[0].value
                this.form.state_id = this.city[0].state_id
                this.form.iso = this.city[0].iso
                this.formAction = 'put'
                let arrayTranslations = this.city[0].translations

                arrayTranslations.forEach((translation) => {
                  form.translations[translation.language_id] = {
                    id: translation.id,
                    city_name: translation.value
                  }
                })
              }).catch(() => {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('cities.error.messages.name'),
                text: this.$t('cities.error.messages.connection_error')
              })
            })
          }
          this.form = form
        }).catch(() => {
        this.$notify({
          group: 'main',
          type: 'error',
          title: this.$t('cities.error.messages.name'),
          text: this.$t('cities.error.messages.connection_error')
        })
      })
      API.get('/state/selectbox?lang=' + localStorage.getItem('lang'))
        .then((result) => {
          this.states = result.data.data
          this.updateSelect+=1
        }).catch(() => {
        this.$notify({
          group: 'main',
          type: 'error',
          title: this.$t('cities.error.messages.name'),
          text: this.$t('cities.error.messages.connection_error')
        })
      })
    },
    computed: {
      errorState: function () {
        if (this.stateSearch == '' && this.form.state_id == '') {
          return true
        }
        if (this.stateSearch == null && this.form.state_id == '') {
          return false
        }
        if (this.country != null) {
          if (this.stateSearch != this.state.translations[0].value) {
            this.state = null
          }
        } else {
          return false
        }
      },
      validError: function () {
        if (this.errors.has('city_name') == false && this.form.translations[1].city_name != '') {
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
      validateBeforeSubmit () {
        if ((this.state == null && this.formAction == 'post')) {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('cities.state_name'),
            text: this.$t('cities.error.messages.state_incorrect')
          })
          return false
        }
        if ((this.state == null && this.formAction == 'put' && this.form.state_id != '' && this.stateSearch != '')) {

          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('cities.state_name'),
            text: this.$t('cities.error.messages.state_incorrect')
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
              title: this.$t('global.modules.cities'),
              text: this.$t('cities.error.messages.information_complete')
            })

            this.loading = false
          }
        })
      },
      submit () {
        if ((this.state == null) && this.formAction == 'put' && this.form.state_id != '') {

        } else {
          this.form.state_id = this.state.id
        }
        this.loading = true

        API({
          method: this.formAction,
          url: 'cities/' + (this.$route.params.id !== undefined ? this.$route.params.id : ''),
          data: this.form
        })
          .then((result) => {
            if (result.data.success === true) {
              this.$router.push('/cities/list')
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.cities'),
                text: this.$t('cities.error.messages.information_error')
              })

              this.loading = false
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('cities.error.messages.name'),
            text: this.$t('cities.error.messages.connection_error')
          })
        })
      }
    }
  }
</script>

<style lang="stylus">

</style>
