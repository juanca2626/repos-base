<template>
    <div class="row">
        <div class="col-sm-12">
            <form @submit.prevent="validateBeforeSubmit">
                <div class="b-form-group form-group">
                    <div class="form-row" style="margin-bottom: 5px;">
                        <label class="col-sm-2 col-form-label" for="zone_name">{{ $t('zones.city_name') }}</label>
                        <div class="col-sm-10">
                            <vue-bootstrap-typeahead
                                    :data="cities"
                                    :serializer="item => item.translations[0].value +' -> '+item.state.translations[0].value +' / '+ item.state.country.translations[0].value"
                                    @hit="city = $event"
                                    ref="cityTypeahead"
                                    v-model="citySearch"
                            />
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;" v-show="errorCity">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']" style="margin-left: 5px;"/>
                                <span>{{ $t('zones.error.required') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="zone_name">{{ $t('zones.zone_name') }}</label>
                        <div class="col-sm-5">
                            <input :class="{'form-control':true, 'is-valid':validError, 'is-invalid':invalidError }"
                                   id="zone_name" name="zone_name"
                                   type="text"
                                   v-model="form.translations[currentLang].zone_name" v-validate="'required'">
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;"
                                 v-show="errors.has('zone_name')">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']" style="margin-left: 5px;"/>
                                <span>{{ errors.first('zone_name') }}</span>
                            </div>
                        </div>
                        <select class="col-sm-1 form-control" id="lang" required size="0" v-model="currentLang">
                            <option v-bind:value="language.id" v-for="language in languages">
                                {{ language.iso }}
                            </option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-sm-6">
            <div slot="footer">
                <img src="/images/loading.svg" v-if="loading" width="40px"/>
                <button @click="validateBeforeSubmit" class="btn btn-success" type="submit" v-if="!loading">
                    <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                    {{ $t('global.buttons.submit')}}
                </button>
                <router-link :to="{ name: 'ZonesList' }" v-if="!loading">
                    <button class="btn btn-danger" type="reset">
                        {{ $t('global.buttons.cancel')}}
                    </button>
                </router-link>
            </div>
        </div>
    </div>
</template>

<script>
  import { API } from './../../api'
  import VueBootstrapTypeahead from 'vue-bootstrap-typeahead'

  export default {
    components: {
      VueBootstrapTypeahead
    },
    data: () => {
      return {
        loading: false,
        formAction: 'post',
        cities: [],
        city: null,
        citySearch: '',
        zone: null,
        languages: [],
        currentLang: '1',
        invalidError: false,
        countError: 0,
        form: {
          city_id: null,
          translations: {
            '1': {
              'id': '',
              'zone_name': ''
            }
          }
        }
      }
    },
    mounted () {
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
              zone_name: ''
            }
          })

          if (this.$route.params.id !== undefined) {
            API.get('/zones/' + this.$route.params.id + '?lang=' + localStorage.getItem('lang'))
              .then((result) => {
                this.city = null
                this.zone = result.data.data
                this.form.city_id = this.zone[0].city_id
                this.$refs.cityTypeahead.inputValue = this.zone[0].city.translations[0].value + ' -> ' +
                  this.zone[0].city.state.translations[0].value + ' / ' +
                  this.zone[0].city.state.country.translations[0].value
                this.formAction = 'put'
                let arrayTranslations = this.zone[0].translations

                arrayTranslations.forEach((translation) => {
                  form.translations[translation.language_id] = {
                    id: translation.id,
                    zone_name: translation.value
                  }
                })
              }).catch(() => {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('zones.error.messages.name'),
                text: this.$t('zones.error.messages.connection_error')
              })
            })
          }
          this.form = form
        }).catch(() => {
        this.$notify({
          group: 'main',
          type: 'error',
          title: this.$t('zones.error.messages.name'),
          text: this.$t('zones.error.messages.connection_error')
        })
      })
      API.get('/city/selectbox?lang=' + localStorage.getItem('lang'))
        .then((result) => {
          this.cities = result.data.data
        }).catch(() => {
        this.$notify({
          group: 'main',
          type: 'error',
          title: this.$t('zones.error.messages.name'),
          text: this.$t('zones.error.messages.connection_error')
        })
      })
    },
    computed: {
      errorCity: function () {
        if (this.citySearch == '' && this.form.city_id == '') {
          return true
        }
        if (this.citySearch == null && this.form.city_id == '') {
          return false
        }
        if (this.city != null) {
          if (this.citySearch != this.city.translations[0].value + ' -> ' + this.city.state.translations[0].value +
            ' / ' + this.city.state.country.translations[0].value) {
            this.city = null
          }
        } else {
          return false
        }
      },
      validError: function () {
        if (this.errors.has('zone_name') == false && this.form.translations[1].zone_name != '') {
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
        if ((this.city == null && this.formAction == 'post')) {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('zones.city_name'),
            text: this.$t('zones.error.messages.city_incorrect')
          })
          return false
        }
        if ((this.city == null && this.formAction == 'put' && this.form.city_id != '' && this.citySearch != '')) {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('zones.city_name'),
            text: this.$t('zones.error.messages.city_incorrect')
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
              text: this.$t('zones.error.messages.information_complete')
            })

            this.loading = false
          }
        })
      },
      submit () {
        if ((this.city == null) && this.formAction == 'put' && this.form.city_id != '') {

        } else {
          this.form.city_id = this.city.id
        }
        this.loading = true

        API({
          method: this.formAction,
          url: 'zones/' + (this.$route.params.id !== undefined ? this.$route.params.id : ''),
          data: this.form
        })
          .then((result) => {
            if (result.data.success === true) {
              this.$router.push('/zones/list')
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.zones'),
                text: this.$t('zones.error.messages.information_error')
              })

              this.loading = false
            }
          })
      },
      remove () {
        this.loading = true

        API({
          method: 'DELETE',
          url: 'zones/' + (this.$route.params.id !== undefined ? this.$route.params.id : '')
        })
          .then((result) => {
            if (result.data.success === true) {
              this.$router.push('/zones/list')
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.zones'),
                text: this.$t('zones.error.messages.zone_delete')
              })

              this.loading = false
            }
          })
      }
    }
  }
</script>

<style lang="stylus">

</style>
