<template>
    <div class="row">
        <div class="col-sm-12">
            <form @submit.prevent="validateBeforeSubmit">
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="typeclass_name">{{ $t('typesclass.typeclass_name')
                            }}</label>
                        <div class="col-sm-5">
                            <input :class="{'form-control':true, 'is-valid':validError, 'is-invalid':invalidError }"
                                   id="typeclass_name" name="typeclass_name"
                                   type="text"
                                   v-model="form.translations[currentLang].typeclass_name" v-validate="'required'">
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('typeclass_name')"/>
                                <span v-show="errors.has('typeclass_name')">{{ errors.first('typeclass_name') }}</span>
                            </div>
                        </div>
                        <select class="col-sm-1 form-control" id="lang" required size="0" v-model="currentLang">
                            <option v-bind:value="language.id" v-for="language in languages">
                                {{ language.iso }}
                            </option>
                        </select>
                    </div>

                    <div class="form-row" v-if="isEditable">
                        <label class="col-sm-2 col-form-label" for="typeclass_order">Order</label>
                        <div class="col-sm-5">
                            <input :class="{'form-control':true, 'is-valid':validError, 'is-invalid':invalidError }"
                                id="typeclass_order" name="typeclass_order"
                                type="number"
                                v-model="form.order" v-validate="'required'">
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
                <router-link :to="{ name: 'TypesClassList' }" v-if="!loading">
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
        typeclass: null,
        showError: false,
        currentLang: '1',
        invalidError: false,
        countError: 0,
        loading: false,
        formAction: 'post',
        form: {
          order: '',
          translations: {
            '1': {
              'id': '',
              'typeclass_name': ''
            }
          }
        }
      }
    },
    computed: {
      validError: function () {
        if (this.errors.has('typeclass_name') == false && this.form.translations[1].typeclass_name != '') {
          this.invalidError = false
          this.countError += 1
          return true

        } else if (this.countError > 0) {
          this.invalidError = true
        }
        return false
      },
      isEditable: function () {
        if(this.$route.params.id)
        {
            return true;
        }

        return false;
      }
    },
    mounted: function () {
      // this.cleanFields()
      API.get('/languages/')
        .then((result) => {
          this.languages = result.data.data
          this.currentLang = result.data.data[0].id

          let form = {
            order: '',
            translations: {}
          }

          let languages = this.languages

          languages.forEach((value) => {
            form.translations[value.id] = {
              id: '',
              typeclass_name: ''
            }
          })
          if (this.$route.params.id !== undefined) {

            API.get('/typesclass/' + this.$route.params.id)
              .then((result) => {
                this.typeclass = result.data.data
                form.order = this.typeclass[0].order
                this.formAction = 'put'

                let arrayTranslations = this.typeclass[0].translations

                arrayTranslations.forEach((translation) => {
                  form.translations[translation.language_id] = {
                    id: translation.id,
                    typeclass_name: translation.value
                  }
                })
              }).catch(() => {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('typesclass.error.messages.name'),
                text: this.$t('typesclass.error.messages.connection_error')
              })
            })
          }

          this.form = form
        }).catch(() => {
        this.$notify({
          group: 'main',
          type: 'error',
          title: this.$t('typesclass.error.messages.name'),
          text: this.$t('typesclass.error.messages.connection_error')
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
              title: this.$t('global.modules.typesclass'),
              text: this.$t('typesclass.error.messages.information_complete')
            })

            this.loading = false
          }
        })
      },
      submit () {

        this.loading = true

        API({
          method: this.formAction,
          url: 'typesclass/' + (this.$route.params.id !== undefined ? this.$route.params.id : ''),
          data: this.form
        })
          .then((result) => {
            if (result.data.success === false) {

              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.typesclass'),
                text: this.$t('typesclass.error.messages.typeclass_incorrect')
              })

              this.loading = false
            } else {
              this.$router.push('/typesclass/list')
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('typesclass.error.messages.name'),
            text: this.$t('typesclass.error.messages.connection_error')
          })
        })
      }
    }
  }
</script>

<style lang="stylus">

</style>
