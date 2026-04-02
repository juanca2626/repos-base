<template>
    <div class="row">
        <div class="col-sm-12">
            <form @submit.prevent="validateBeforeSubmit">
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="amenity_name">{{ $t('amenities.amenity_name')
                            }}</label>
                        <div class="col-sm-5">
                            <input :class="{'form-control':true, 'is-valid':validError, 'is-invalid':invalidError }"
                                   id="amenity_name" name="amenity_name"
                                   type="text"
                                   v-model="form.translations[currentLang].amenity_name" v-validate="'required'">

                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('amenity_name')"/>
                                <span v-show="errors.has('amenity_name')">{{ errors.first('amenity_name') }}</span>
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
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label">{{ $t('amenities.status.title') }}</label>
                        <div class="col-sm-5">
                            <c-switch :value="true" class="mx-1" color="success"
                                      v-model="form.status"
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
                    {{$t('global.buttons.submit')}}
                </button>
                <button @click="CancelForm" class="btn btn-danger" type="reset" v-if="!loading">
                    {{$t('global.buttons.cancel')}}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
  import { API } from './../../api'
  import { Switch as cSwitch } from '@coreui/vue'
  import vue2Dropzone from 'vue2-dropzone'
  import 'vue2-dropzone/dist/vue2Dropzone.min.css'

  export default {
    components: {
      cSwitch,
      vueDropzone: vue2Dropzone
    },
    data: () => {
      return {
        images: [],
        languages: [],
        amenity: null,
        showError: false,
        currentLang: '1',
        invalidError: false,
        countError: 0,
        loading: false,
        formAction: 'post',
        id_image: '',
        url_image: '',
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
        form: {
          status: false,
          translations: {
            '1': {
              'id': '',
              'amenity_name': ''
            }
          }
        }
      }
    },
    computed: {
      validError: function () {
        if (this.errors.has('amenity_name') === false && this.form.translations[1].amenity_name !== '') {
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
              amenity_name: ''
            }
          })
          if (this.$route.params.id !== undefined) {

            API.get('/amenities/' + this.$route.params.id)
              .then((result) => {
                this.formAction = 'put'
                this.amenity = result.data.data
                this.form.status = (this.amenity[0].status === 1)
                let arrayTranslations = this.amenity[0].translations

                arrayTranslations.forEach((translation) => {
                  this.form.translations[translation.language_id] = {
                    id: translation.id,
                    amenity_name: translation.value
                  }
                })

                if (this.amenity[0].galeries.length > 0) {
                  this.images = this.amenity[0].galeries[0]
                  this.url_image = this.amenity[0].galeries[0].url
                  this.id_image = this.amenity[0].galeries[0].id

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

              })
          }

        })
    },
    methods: {
      dropzoneSuccess: function (file, response) {
        this.charged = true
        this.images = []
        this.images.push(response.timestamp)
      },
      CancelForm () {
        this.id_image = ''
        this.$router.push('/amenities/list')
      },
      validateBeforeSubmit () {
        this.$validator.validateAll().then((result) => {
          if (result) {

            this.submit()

          } else {
            this.$notify({
              group: 'main',
              type: 'error',
              title: this.$t('global.modules.amenities'),
              text: this.$t('amenities.error.messages.information_complete')
            })

            this.loading = false
          }
        })
      },
      submit () {

        this.loading = true
        if (this.formAction !== 'put') {
          this.form.status = (this.form.status === false ? 0 : 1)
        }

        API({
          method: this.formAction,
          url: 'amenities/' + (this.$route.params.id !== undefined ? this.$route.params.id : ''),
          data: this.form
        })
          .then((result) => {
            if (result.data.success === false) {

              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.amenities'),
                text: this.$t('amenities.error.messages.amenity_incorrect')
              })

              this.loading = false
            } else {
              if (this.charged === true) {
                this.images.forEach((image, key) => {
                  this.position = 1
                  let formImagen = {
                    image: image,
                    type: 'amenity',
                    object_id: result.data.object_id,
                    url: '',
                    slug: 'amenity',
                    position: this.position,
                    state: true
                  }

                  API({
                    method: 'put',
                    url: 'amenities/gallery',
                    data: formImagen
                  }).then((result) => {
                    if (result.data.success === false) {
                      this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.amenities'),
                        text: this.$t('amenities.error.messages.gallery_incorrect')
                      })
                      this.loading = false
                    }
                  })
                })
              } else {
                this.charged = false
                this.id_image = ''
              }
            }

            this.$router.push('/amenities/list')
          })
      },
      dropzoneRemoveFile () {
        if (this.id_image !== '') {
          API.delete('/amenities/image/' + this.id_image)
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
</style>

