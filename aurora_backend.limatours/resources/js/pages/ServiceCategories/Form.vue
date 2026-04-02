<template>
    <div class="row">
        <div class="col-sm-12">
            <form @submit.prevent="validateBeforeSubmit">
                <div class="b-form-group form-group">
                    <div class="form-row" id="container_country">
                        <label class="col-sm-2 col-form-label" for="servicecategory_name">{{
                            $t('servicecategories.service_category_name')
                            }}</label>
                        <div class="col-sm-4">
                            <input :class="{'form-control':true }"
                                   id="servicecategory_name" name="servicecategory_name"
                                   type="text"
                                   v-model="form.translations[currentLang].servicecategory_name" v-validate="'required'">
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('servicecategory_name')"/>
                                <span v-show="errors.has('servicecategory_name')">{{ errors.first('servicecategory_name') }}</span>
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
                <router-link :to="{ name: 'ServiceCategoryList' }" v-if="!loading">
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
        service_categories: null,
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
              'servicecategory_name': ''
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
              servicecategory_name: ''
            }
          })
          if (this.$route.params.id !== undefined) {

            API.get('/service_categories/' + this.$route.params.id)
              .then((result) => {
                this.service_categories = result.data.data
                this.formAction = 'put'

                let arrayTranslations = this.service_categories[0].translations

                arrayTranslations.forEach((translation) => {
                  form.translations[translation.language_id] = {
                    id: translation.id,
                    servicecategory_name: translation.value
                  }
                })
              }).catch(() => {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.servicecategories'),
                text: this.$t('servicecategories.error.messages.connection_error')
              })
            })
          }

          this.form = form
        }).catch(() => {
        this.$notify({
          group: 'main',
          type: 'error',
          title: this.$t('global.modules.servicecategories'),
          text: this.$t('servicecategories.error.messages.connection_error')
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
              title: this.$t('global.modules.servicecategories'),
              text: this.$t('servicecategories.error.messages.information_complete')
            })
            this.loading = false
          }
        })
      },
      submit () {
        this.loading = true
        API({
          method: this.formAction,
          url: 'service_categories/' + (this.$route.params.id !== undefined ? this.$route.params.id : ''),
          data: this.form
        })
          .then((result) => {
            if (result.data.success === false) {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.servicecategories'),
                text: this.$t('servicecategories.error.messages.information_error')
              })
              this.loading = false
            } else {
              this.$router.push('/type_service/list')
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('global.modules.servicecategories'),
            text: this.$t('servicecategories.error.messages.connection_error')
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
