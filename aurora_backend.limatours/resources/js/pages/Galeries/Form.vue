<template>
    <div class="row">
        <div class="col-sm-12">
            <form @submit.prevent="validateBeforeSubmit">
                <div class="b-form-group form-group">
                    <div class="form-row" style="margin-bottom: 5px;">
                        <label class="col-sm-2 col-form-label" for="type">{{ $t('galeries.type') }}</label>
                        <div class="col-sm-5">
                            <select class="col-sm-3 form-control" id="type" required size="0" v-model="form.type">
                                <option selected value="hotel">{{ $t('global.types.hotel') }}</option>
                                <option value="room">{{ $t('global.types.room') }}</option>
                                <option value="client">{{ $t('global.types.client') }}</option>
                            </select>
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;"
                                 v-show="errors.has('type')">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;"/>
                                <span>{{ errors.first('type') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="form-row" style="margin-bottom: 5px;">
                        <label class="col-sm-2 col-form-label" for="url">{{ $t('galeries.object_id') }}</label>
                        <div class="col-sm-5">
                            <input :class="{'form-control':true}"
                                   id="object_id" name="object_id"
                                   type="text"
                                   v-model="form.object_id" v-validate="'required|numeric'">

                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;"
                                 v-show="errors.has('object_id')">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;"/>
                                <span>{{ errors.first('object_id') }}</span>
                            </div>

                        </div>
                    </div>
                    <div class="form-row" style="margin-bottom: 5px;">
                        <label class="col-sm-2 col-form-label" for="url">{{ $t('galeries.url') }}</label>
                        <div class="col-sm-5">
                            <input :class="{'form-control':true}"
                                   id="url" name="url"
                                   type="text"
                                   v-model="form.url" v-validate="'url'">

                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;"
                                 v-show="errors.has('url')">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;"/>
                                <span>{{ errors.first('url') }}</span>
                            </div>

                        </div>
                    </div>
                    <div class="form-row" style="margin-bottom: 5px;">
                        <label class="col-sm-2 col-form-label" for="slug">{{ $t('galeries.slug') }}</label>
                        <div class="col-sm-5">
                            <input :class="{'form-control':true}"
                                   id="slug" name="slug"
                                   type="text"
                                   v-model="form.slug">
                        </div>
                    </div>
                    <div class="form-row" style="margin-bottom: 5px;">
                        <label class="col-sm-2 col-form-label" for="url">{{ $t('galeries.position') }}</label>
                        <div class="col-sm-5">
                            <input :class="{'form-control':true}"
                                   id="position" name="position"
                                   type="text"
                                   v-model="form.position" v-validate="'required|numeric'">

                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;"
                                 v-show="errors.has('position')">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;"/>
                                <span>{{ errors.first('position') }}</span>
                            </div>

                        </div>
                    </div>
                    <div class="form-row" style="margin-bottom: 5px;">
                        <label class="col-sm-2 col-form-label" for="iso">{{ $t('galeries.image') }}</label>
                        <div class="col-sm-5">
                            <vue-dropzone :options="dropzoneOptions"
                                          @vdropzone-removed-file='dropzoneRemoveFile'
                                          id="uploadFile"
                                          ref="uploadFile"
                            ></vue-dropzone>
                        </div>
                    </div>
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="state">{{ $t('galeries.state') }}</label>
                        <div class="col-sm-5">
                            <c-switch :value="true" class="mx-1" color="success"
                                      v-model="form.state"
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
                <router-link :to="{ name: 'GaleriesList' }" v-if="!loading">
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
      cSwitch,
      vueDropzone: vue2Dropzone
    },
    data: () => {
      return {

        loading: false,
        formAction: 'post',
        dropzoneOptions: {
          url: window.origin + '/api/galeries/image',
          acceptedFiles: 'image/*',
          maxFiles: 1,
          thumbnailWidth: 150,
          maxFilesize: 1.5,
          addRemoveLinks: true,
          dictDefaultMessage: '<i class=\'fas fa-cloud-upload\'></i> Upload File',
          headers: { 'Authorization': 'Bearer ' + localStorage.getItem('access_token') }
        },
        galery: null,
        form: {
          type: 'hotel',
          object_id: '',
          url: '',
          slug: '',
          position: '',
          state: true
        }
      }
    },
    computed: {},
    mounted: function () {
      // this.cleanFields()

      if (this.$route.params.id !== undefined) {

        API.get('/galeries/' + this.$route.params.id)
          .then((result) => {
            this.galery = result.data.data
            this.form.type = this.galery.type
            this.form.object_id = this.galery.object_id
            this.form.url = this.galery.url
            this.form.slug = this.galery.slug
            this.form.position = this.galery.position
            this.form.state = this.galery.state ? true : false
            this.formAction = 'put'

            if (result.data.image) {
              this.$refs.uploadFile.manuallyAddFile(result.data.image, '/images/' + result.data.image.name)
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('galeries.error.messages.name'),
            text: this.$t('galeries.error.messages.connection_error')
          })
        })
      }

      //this.form = form
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
              title: this.$t('global.modules.galeries'),
              text: this.$t('galeries.error.messages.information_complete')
            })

            this.loading = false
          }
        })
      },
      submit () {

        this.loading = true

        API({
          method: this.formAction,
          url: 'galeries/' + (this.$route.params.id !== undefined ? this.$route.params.id : ''),
          data: this.form
        })
          .then((result) => {
            if (result.data.success === false) {

              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.galeries'),
                text: this.$t('galeries.error.messages.galery_incorrect')
              })

              this.loading = false
            } else {
              this.$router.push('/galeries/list')
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('galeries.error.messages.name'),
            text: this.$t('galeries.error.messages.connection_error')
          })
        })
      },
      remove () {
        this.loading = true

        API({
          method: 'DELETE',
          url: 'galeries/' + (this.$route.params.id !== undefined ? this.$route.params.id : '')
        })
          .then((result) => {
            if (result.data.success === true) {
              this.$router.push('/galeries/list')
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.galeries'),
                text: this.$t('galeries.error.messages.galery_delete')
              })

              this.loading = false
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('galeries.error.messages.name'),
            text: this.$t('galeries.error.messages.connection_error')
          })
        })
      },
      dropzoneRemoveFile () {
        if (this.$route.params.id) {
          API.delete('/galeries/image/' + this.$route.params.id)
            .then((result) => {
              if (result.data.success === false) {
                this.$notify({
                  group: 'main',
                  type: 'error',
                  title: this.$t('global.modules.galeries'),
                  text: this.$t('galeries.error.messages.connection_error')
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

