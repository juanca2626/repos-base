<template>
    <div class="row">
        <div class="col-sm-12">
            <form @submit.prevent="validateBeforeSubmit">
                <div class="b-form-group form-group">
                    <div class="form-row" id="container_country">
                        <label class="col-sm-2 col-form-label text-right" for="unitduration_name">{{
                            $t('unitdurations.unit_name')
                            }}</label>
                        <div class="col-sm-4">
                            <input :class="{'form-control':true }"
                                   id="unitduration_name" name="unitduration_name"
                                   type="text"
                                   v-model="form.translations[currentLang].unitduration_name" v-validate="'required'">
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('unit_name')"/>
                                <span v-show="errors.has('unitduration_name')">{{ errors.first('unitduration_name') }}</span>
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
                    {{ $t('global.buttons.submit') }}
                </button>
                <router-link :to="{ name: 'UnitDurationsList' }" v-if="!loading">
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
        languages: [],
        unitDuration: null,
        showError: false,
        currentLang: '1',
        invalidError: false,
        countError: 0,
        loading: false,
        formAction: 'post',
        form: {
          translations: {
            '1': {
              'id': '',
              'unitduration_name': ''
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
              unitduration_name: ''
            }
          })
          if (this.$route.params.id !== undefined) {

            API.get('/unitdurations/' + this.$route.params.id)
              .then((result) => {
                this.unitDuration = result.data.data
                this.formAction = 'put'

                let arrayTranslations = this.unitDuration[0].translations

                arrayTranslations.forEach((translation) => {
                  form.translations[translation.language_id] = {
                    id: translation.id,
                    unitduration_name: translation.value
                  }
                })
              }).catch(() => {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('unitdurations.error.messages.name'),
                text: this.$t('unitdurations.error.messages.connection_error')
              })
            })
          }

          this.form = form
        }).catch(() => {
        this.$notify({
          group: 'main',
          type: 'error',
          title: this.$t('unitdurations.error.messages.name'),
          text: this.$t('unitdurations.error.messages.connection_error')
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
              title: this.$t('global.modules.unitdurations'),
              text: this.$t('unitdurations.error.messages.information_complete')
            })
            this.loading = false
          }
        })
      },
      submit () {
        this.loading = true
        API({
          method: this.formAction,
          url: 'unitdurations/' + (this.$route.params.id !== undefined ? this.$route.params.id : ''),
          data: this.form
        })
          .then((result) => {
            if (result.data.success === false) {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.unitdurations'),
                text: this.$t('unitdurations.error.messages.information_error')
              })
              this.loading = false
            } else {
              this.$router.push('/unitdurations/list')
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('unitdurations.error.messages.name'),
            text: this.$t('unitdurations.error.messages.connection_error')
          })
        })
      },
      remove () {
        this.loading = true
        API({
          method: 'DELETE',
          url: 'unitdurations/' + (this.$route.params.id !== undefined ? this.$route.params.id : '')
        })
          .then((result) => {
            if (result.data.success === true) {
              this.$router.push('/unitdurations/list')
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.unitdurations'),
                text: this.$t('unitdurations.error.messages.unitduration_delete')
              })

              this.loading = false
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('unitdurations.error.messages.name'),
            text: this.$t('unitdurations.error.messages.connection_error')
          })
        })
      }
    }
  }
</script>

<style lang="stylus">
    #container_country
        margin-bottom 15px
</style>
