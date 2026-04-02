<template>
    <div class="row">
        <div class="col-sm-12">
            <form @submit.prevent="validateBeforeSubmit">
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="tagservices_name">{{ $t('tagservices.tagservices_name')
                            }}</label>
                        <div class="col-sm-10">
                            <input :class="{'form-control':true, 'is-valid':validError, 'is-invalid':invalidError }"
                                   id="tagservices_name" name="tagservices_name"
                                   type="text"
                                   v-model="form.translations_name[currentLang].tagservices_name" v-validate="'required'">
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('tagservices_name')"/>
                                <span v-show="errors.has('tagservices_name')">{{ errors.first('tagservices_name') }}</span>
                            </div>
                        </div>
                        <label class="col-sm-2 col-form-label" for="tagservices_description">{{ $t('tagservices.tagservices_description')}}</label>
                        <div class="col-sm-10">
                            <input :class="{'form-control':true, 'is-valid':validError, 'is-invalid':invalidError }"
                                   id="tagservices_description" name="tagservices_description"
                                   type="text"
                                   v-model="form.translations_description[currentLang].tagservices_description" v-validate="'required'">
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('tagservices_description')"/>
                                <span v-show="errors.has('tagservices_description')">{{ errors.first('tagservices_description') }}</span>
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
                    {{$t('global.buttons.submit')}}
                </button>
                <router-link :to="{ name: 'TagServicesList' }" v-if="!loading">
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
        languages: [],
        tagservices: null,
        showError: false,
        currentLang: '1',
        invalidError: false,
        countError: 0,
        loading: false,
        formAction: 'post',
        form: {
          translations_name: {
            '1': {
              'id': '',
              'tagservices_name': '',
            }
          },
            translations_description: {
            '1': {
              'id': '',
                'tagservices_description':'',
            }
          }
        }
      }
    },
    computed: {
      validError: function () {
        if (this.errors.has('tagservices_name') == false && this.form.translations_name[1].tagservices_name != '') {
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

          let form = {
            translations_name: {},
            translations_description: {},

          }

          let languages = this.languages

          languages.forEach((value) => {
            form.translations_name[value.id] = {
              id: '',
              tagservices_name: ''
            }
              form.translations_description[value.id] = {
                  id: '',
                  tagservices_description: ''
              }
          })
          if (this.$route.params.id !== undefined) {

            API.get('/tagservices/' + this.$route.params.id)
              .then((result) => {
                this.tagservices = result.data.data
                this.formAction = 'put'

                let arrayTranslations = this.tagservices[0].translations

                arrayTranslations.forEach((translation) => {
                    if (translation.slug == 'tagservices_name')
                    {
                        form.translations_name[translation.language_id] = {
                            id: translation.id,
                            tagservices_name: translation.value
                        }
                    }
                    if (translation.slug == 'tagservices_description')
                    {
                        form.translations_description[translation.language_id] = {
                            id: translation.id,
                            tagservices_description: translation.value
                        }
                    }

                })
              }).catch((e) => {
              console.log(e)
            })
          }

          this.form = form
        }).catch((e) => {
        console.log(e)
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
              title: this.$t('global.modules.tagservices'),
              text: this.$t('tagservices.error.messages.information_complete')
            })

            this.loading = false
          }
        })
      },
      submit () {

        this.loading = true

        API({
          method: this.formAction,
          url: 'tagservices/' + (this.$route.params.id !== undefined ? this.$route.params.id : ''),
          data: this.form
        })
          .then((result) => {
            if (result.data.success === false) {

              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.tagservices'),
                text: this.$t('tagservices.error.messages.tagservices_incorrect')
              })

              this.loading = false
            } else {
              this.$router.push('/tagservices/list')
            }
          }).catch((e) => {
          console.log(e)
        })
      }
    }
  }
</script>

<style lang="stylus">

</style>
