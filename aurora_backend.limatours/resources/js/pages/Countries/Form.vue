<template>
    <div class="row">
        <div class="col-sm-12">
            <form @submit.prevent="validateBeforeSubmit">
                <div class="b-form-group form-group">
                    <div class="form-row" id="container_country">
                        <label class="col-sm-1 col-form-label" for="country_name">{{ $t('countries.country_name')
                            }}</label>
                        <div class="col-sm-4">
                            <input :class="{'form-control':true }"
                                   id="country_name" name="country_name"
                                   type="text"
                                   v-model="form.translations[currentLang].country_name" v-validate="'required'">
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('country_name')"/>
                                <span v-show="errors.has('country_name')">{{ errors.first('country_name') }}</span>
                            </div>
                        </div>
                        <select class="col-sm-1 form-control" id="lang" required size="0" v-model="currentLang">
                            <option v-bind:value="language.id" v-for="language in languages">
                                {{ language.iso }}
                            </option>
                        </select>
                    </div>
                    <div class="form-row">
                        <label class="col-sm-1 col-form-label">ISO</label>
                        <div class="col-sm-4">
                            <input :class="{'form-control':true }"
                                   id="iso" name="iso"
                                   type="text"
                                   v-model="form.iso">
                        </div>
                    </div>
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="local_tax">{{ $t('countries.local_tax') }}</label>
                        <div class="col-sm-2">
                            <c-switch class="mx-1" color="success" id="local_tax"
                                      v-model="form.local_tax"
                                      variant="pill">
                            </c-switch>
                        </div>
                        <label class="col-sm-2 col-form-label" for="foreign_tax">{{ $t('countries.foreign_tax')
                            }}</label>
                        <div class="col-sm-4">
                            <c-switch class="mx-1" color="success" id="foreign_tax"
                                      v-model="form.foreign_tax"
                                      variant="pill">
                            </c-switch>
                        </div>
                    </div>
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="local_service">{{ $t('countries.local_service')
                            }}</label>
                        <div class="col-sm-2">
                            <c-switch class="mx-1" color="success" id="local_service"
                                      v-model="form.local_service"
                                      variant="pill">
                            </c-switch>
                        </div>
                        <label class="col-sm-2 col-form-label" for="foreign_service">{{ $t('countries.foreign_service')
                            }}</label>
                        <div class="col-sm-5">
                            <c-switch class="mx-1" color="success" id="foreign_service"
                                      v-model="form.foreign_service"
                                      variant="pill">
                            </c-switch>
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
                <router-link :to="{ name: 'CountriesList' }" v-if="!loading">
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
  import { Switch as cSwitch } from '@coreui/vue'

  export default {
    components: {
      cSwitch
    },
    data: () => {
      return {
        languages: [],
        country: null,
        showError: false,
        currentLang: '1',
        invalidError: false,
        countError: 0,
        loading: false,
        formAction: 'post',
        form: {
          iso: '',
          local_tax: true,
          local_service: true,
          foreign_tax: true,
          foreign_service: true,
          translations: {
            '1': {
              'id': '',
              'country_name': ''
            }
          }
        }
      }
    },
    computed: {},
    mounted: function () {
      API.get('/languages/')
        .then((result) => {
          this.languages = result.data.data
          this.currentLang = result.data.data[0].id

          let form = this.form

          let languages = this.languages

          languages.forEach((value) => {
            form.translations[value.id] = {
              id: '',
              country_name: ''
            }
          })
          if (this.$route.params.id !== undefined) {

            API.get('/countries/' + this.$route.params.id)
              .then((result) => {
                this.country = result.data.data
                this.form.iso = this.country[0].iso
                this.form.local_tax = this.country[0].local_tax ? true : false
                this.form.local_service = this.country[0].local_service ? true : false
                this.form.foreign_tax = this.country[0].foreign_tax ? true : false
                this.form.foreign_service = this.country[0].foreign_service ? true : false
                this.formAction = 'put'

                let arrayTranslations = this.country[0].translations

                arrayTranslations.forEach((translation) => {
                  form.translations[translation.language_id] = {
                    id: translation.id,
                    country_name: translation.value
                  }
                })
              }).catch(() => {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('countries.error.messages.name'),
                text: this.$t('countries.error.messages.connection_error')
              })
            })
          }

          this.form = form
        }).catch(() => {
        this.$notify({
          group: 'main',
          type: 'error',
          title: this.$t('countries.error.messages.name'),
          text: this.$t('countries.error.messages.connection_error')
        })
      })
    },
    methods: {
      validateBeforeSubmit () {
        this.$validator.validateAll().then((result) => {
          if (result) {

            this.submit()

          } else {
            this.$notify({
              group: 'main',
              type: 'error',
              title: this.$t('global.modules.countries'),
              text: this.$t('countries.error.messages.information_complete')
            })

            this.loading = false
          }
        })
      },
      submit () {

        this.loading = true

        API({
          method: this.formAction,
          url: 'countries/' + (this.$route.params.id !== undefined ? this.$route.params.id : ''),
          data: this.form
        })
          .then((result) => {
            if (result.data.success === false) {

              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.countries'),
                text: this.$t('countries.error.messages.country_incorrect')
              })
              this.loading = false
            } else {
              this.$router.push('/countries/list')
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('countries.error.messages.name'),
            text: this.$t('countries.error.messages.connection_error')
          })
        })
      },
    }
  }
</script>

<style lang="stylus">
    #container_country
        margin-bottom 15px
</style>
