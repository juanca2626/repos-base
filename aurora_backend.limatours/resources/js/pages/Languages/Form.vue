<template>
    <div class="row">
        <div class="col-sm-12">
            <form @submit.prevent="validateBeforeSubmit">
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="name">{{ $t('languages.language_name') }}</label>
                        <div class="col-sm-5">
                            <input :class="{'form-control':true, 'is-valid':validError, 'is-invalid':invalidError }"
                                   id="name" name="name"
                                   type="text"
                                   v-model="form.name" v-validate="'required'">
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('name')"/>
                                <span v-show="errors.has('name')">{{ errors.first('name') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="iso">ISO</label>
                        <div class="col-sm-5">
                            <input :class="{'form-control':true, 'is-valid':validError, 'is-invalid':invalidError }"
                                   id="iso" maxlength=3 name="iso"
                                   type="text"
                                   v-model="form.iso" v-validate="'required'">
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('iso')"/>
                                <span v-show="errors.has('iso')">{{ errors.first('iso') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="iso">{{ $t('languages.language_flag_image')
                            }}</label>
                        <div class="col-sm-5">
                            <vue-dropzone :options="dropzoneOptions"
                                          @vdropzone-removed-file='dropzoneRemoveFile'
                                          id="uploadFile"
                                          ref="uploadFile"
                            ></vue-dropzone>
                        </div>
                    </div>
                </div>

                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label">{{ $t('global.status') }}</label>
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
                <router-link :to="{ name: 'LanguagesList' }" v-if="!loading">
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
  import { Switch as cSwitch } from '@coreui/vue'
  import vue2Dropzone from 'vue2-dropzone'
  import 'vue2-dropzone/dist/vue2Dropzone.min.css'  
  export default {
    components: {
      vueDropzone: vue2Dropzone,
      cSwitch,
    },
    data: () => {
      return {
        status: false,
        loading: false,
        showError: false,
        formAction: 'post',
        invalidError: false,
        countError: 0,
        dropzoneOptions: {
          url: window.origin + '/api/languages/image',
          acceptedFiles: 'image/*',
          maxFiles: 1,
          thumbnailWidth: 150,
          maxFilesize: 1.5,
          addRemoveLinks: true,
          dictDefaultMessage: '<i class=\'fas fa-cloud-upload\'></i> Upload File',
          headers: { 'Authorization': 'Bearer ' + localStorage.getItem('access_token') }
        },
        form: {
          name: '',
          iso: ''
        }
      }
    },
    computed: {
      validError: function () {
        if ((this.errors.has('name') == false && this.form.name != '') &&
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
    mounted () {
      if (this.$route.params.id !== undefined) {
        API.get('/languages/' + this.$route.params.id)
          .then((result) => {
            this.form = result.data.data
            this.formAction = 'put'
            this.form.status = !!result.data.data.state

            if (result.data.image) {
              this.$refs.uploadFile.manuallyAddFile(result.data.image, '/images/' + result.data.image.name)
            }
          })
      }
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
              title: this.$t('global.modules.languages'),
              text: this.$t('languages.error.messages.information_complete')
            })

            this.loading = false
          }
        })
      },
      submit () {
        this.loading = true

        API({
          method: this.formAction,
          url: 'languages/' + (this.$route.params.id !== undefined ? this.$route.params.id : ''),
          data: this.form
        })
          .then((result) => {
            if (result.data.success === true) {
              this.$router.push('/languages/list')
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.languages'),
                text: this.$t('languages.error.messages.language_incorrect')
              })

              this.loading = false
            }
          })
      },
      dropzoneRemoveFile () {
        if (this.$route.params.id) {
          API.delete('/languages/image/' + this.$route.params.id)
            .then((result) => {
              if (result.data.success === false) {
                this.$notify({
                  group: 'main',
                  type: 'error',
                  title: this.$i18n.t('languages.title'),
                  text: result.data.message
                })
              }
            })
        }
      }
    }
  }
</script>

<style lang="stylus">
</style>
