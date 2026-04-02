<template>
    <div class="row">
        <div class="col-sm-12">
            <form @submit.prevent="validateBeforeSubmit">
                <div class="b-form-group form-group">
                    <div class="form-row" id="container_country">
                        <label class="col-sm-1 col-form-label" for="classification_name">{{
                            $t('classifications.classification_name')
                            }}</label>
                        <div class="col-sm-4">
                            <input :class="{'form-control':true }"
                                   id="classification_name" name="classification_name"
                                   type="text"
                                   v-model="form.translations[currentLang].classification_name" v-validate="'required'">
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;"
                                                   v-show="errors.has('classification_name')"/>
                                <span v-show="errors.has('classification_name')">{{ errors.first('classification_name') }}</span>
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
                        <label class="col-sm-2 col-form-label" for="iso">{{ $t('amenities.amenity_image') }}</label>
                        <div class="col-sm-5">
                            <vue-dropzone :options="dropzoneOptions"
                                          @vdropzone-removed-file='dropzoneRemoveFile'
                                          @vdropzone-success="dropzoneSuccess"
                                          id="uploadFile"
                                          ref="uploadFile"
                            ></vue-dropzone>
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
                <router-link :to="{ name: 'ClassificationsList' }" v-if="!loading">
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
  import vue2Dropzone from 'vue2-dropzone'
  import 'vue2-dropzone/dist/vue2Dropzone.min.css'

  export default {
    components: {
      vueDropzone: vue2Dropzone
    },
    data: () => {
      return {
        images: [],
        languages: [],
        classification: null,
        showError: false,
        currentLang: '1',
        invalidError: false,
        countError: 0,
        loading: false,
        formAction: 'post',
        dropzoneOptions: {
          url: window.origin + '/api/galeries/image',
          cursor: 'move',
          acceptedFiles: 'image/*',
          maxFiles: 1,
          thumbnailWidth: 200,
          maxFilesize: 1.5,
          addRemoveLinks: true,
          dictDefaultMessage: '<i class=\'fas fa-cloud-upload\'></i> Upload File',
          headers: { 'Authorization': 'Bearer ' + localStorage.getItem('access_token') }
        },
        id_image: '',
        url_image: '',
        form: {
          translations: {
            '1': {
              'id': '',
              'classification_name': ''
            }
          }
        }
      }
    },
    computed: {
      validError: function () {
        if (this.errors.has('classification_name') === false && this.form.translations[1].classification_name !== '') {
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
      API.get('/languages/')
        .then((result) => {
          this.languages = result.data.data
          this.currentLang = result.data.data[0].id
          let form = this.form
          let languages = this.languages
          languages.forEach((value) => {
            form.translations[value.id] = {
              id: '',
              classification_name: ''
            }
          })
          if (this.$route.params.id !== undefined) {

            API.get('/classifications/' + this.$route.params.id)
              .then((result) => {
                this.classification = result.data.data
                this.formAction = 'put'

                let arrayTranslations = this.classification[0].translations

                arrayTranslations.forEach((translation) => {
                  form.translations[translation.language_id] = {
                    id: translation.id,
                    classification_name: translation.value
                  }
                })

                if (this.classification[0].galeries.length > 0) {
                  this.images = this.classification[0].galeries[0]
                  this.url_image = this.classification[0].galeries[0].url
                  this.id_image = this.classification[0].galeries[0].id

                  let imageObj = {
                    name: 'galeries/' + this.url_image + '?' + Date.now(),
                    size: 1176,
                    type: 'image/png'
                  }
                  if (this.$refs.uploadFile !== undefined) {
                    this.$refs.uploadFile.manuallyAddFile(imageObj,
                      '/images/galeries/' + this.url_image + '?' + Date.now())
                  }
                }
              }).catch(() => {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('classifications.error.messages.name'),
                text: this.$t('classifications.error.messages.connection_error')
              })
            })
          }

          this.form = form
        }).catch(() => {
        this.$notify({
          group: 'main',
          type: 'error',
          title: this.$t('classifications.error.messages.name'),
          text: this.$t('classifications.error.messages.connection_error')
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
              title: this.$t('global.modules.classifications'),
              text: this.$t('classifications.error.messages.information_complete')
            })
            this.loading = false
          }
        })
      },
      submit () {
        this.loading = true
        API({
          method: this.formAction,
          url: 'classifications/' + (this.$route.params.id !== undefined ? this.$route.params.id : ''),
          data: this.form
        })
          .then((result) => {
            if (result.data.success === false) {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.classifications'),
                text: this.$t('classifications.error.messages.information_error')
              })
              this.loading = false
            } else {
              if (this.charged === true) {
                this.images.forEach((image, key) => {
                  this.position = 1
                  let formImagen = {
                    image: image,
                    type: 'classification',
                    object_id: result.data.object_id,
                    url: '',
                    slug: 'classification',
                    position: this.position,
                    state: true
                  }

                  API({
                    method: 'put',
                    url: 'classification/gallery',
                    data: formImagen
                  }).then((result) => {
                    if (result.data.success === false) {
                      this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.classifications'),
                        text: this.$t('classifications.error.messages.gallery_incorrect')
                      })
                      this.loading = false
                    }
                  })
                })
              }else{
                this.charged = false
                this.id_image = ''
              }
              this.$router.push('/classifications/list')
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('classifications.error.messages.name'),
            text: this.$t('classifications.error.messages.connection_error')
          })
        })
      },
      remove () {
        this.loading = true
        API({
          method: 'DELETE',
          url: 'classifications/' + (this.$route.params.id !== undefined ? this.$route.params.id : '')
        })
          .then((result) => {
            if (result.data.success === true) {
              this.$router.push('/classifications/list')
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.classifications'),
                text: this.$t('classifications.error.messages.country_delete')
              })

              this.loading = false
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('classifications.error.messages.name'),
            text: this.$t('classifications.error.messages.connection_error')
          })
        })
      },
      dropzoneSuccess: function (file, response) {
        this.charged = true
        this.images = []
        this.images.push(response.timestamp)
      },
      dropzoneRemoveFile () {
        if (this.id_image !== '') {
          API.delete('/classification/image/' + this.id_image)
            .then((result) => {
              if (result.data.success === false) {
                this.$notify({
                  group: 'main',
                  type: 'error',
                  title: this.$t('global.modules.messages.name'),
                  text: this.$t('amenities.error.messages.connection_error')
                })
              }
            })
          this.id_image = ''
        }
      },
    }
  }
</script>

<style lang="stylus">
    #container_country
        margin-bottom 15px
</style>
