<template>
  <div class="row mt-3">
    <div class="col-sm-12">
      <form @submit.prevent="validateBeforeSubmit">

        <div class="b-form-group form-group">
          <div class="form-row">
            <label class="col-sm-2 col-form-label">
              <input type="checkbox" v-model="use_import"> Importar de Contactos
            </label>
            <div class="col-sm-5">
              <select :disabled="!(use_import)" class="form-control" id="select_contacts" required
                      size="0" v-model="contact" @change="set_data_contact(contact)">
                <option :value="c.id" v-for="c in contacts">
                  {{ c.name }}
                </option>
              </select>
            </div>
          </div>
        </div>

        <div class="b-form-group form-group">
          <div class="form-row">
            <label class="col-sm-2 col-form-label" for="name">{{ $t('global.name') }}</label>
            <div class="col-sm-5">
              <input :class="{'form-control':true }" :disabled="use_import"
                     id="name" name="name"
                     type="text"
                     autocomplete="off"
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
            <label class="col-sm-2 col-form-label" for="email">{{
                $t('clientsmanageclientseller.email')
              }}</label>
            <div class="col-sm-5">
              <input :class="{'form-control':true }" :disabled="use_import"
                     data-vv-as="email" id="email"
                     name="email"
                     autocomplete="off"
                     type="text" v-model="form.email" v-validate="'required|email'">
              <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                   style="margin-left: 5px;" v-show="errors.has('email')"/>
                <span v-show="errors.has('email')">{{ errors.first('email') }}</span>
              </div>
            </div>
          </div>
        </div>
        <div class="b-form-group form-group">
          <div class="form-row">
            <label class="col-sm-2 col-form-label" for="uploadFile">{{
                $t('clientsmanageclientseller.photo')
              }}</label>
            <div class="col-sm-5">
              <vue-dropzone :options="dropzoneOptions"
                            @vdropzone-removed-file="dropzoneRemoveFile"
                            @vdropzone-success="dropzoneSuccess"
                            id="uploadFile"
                            ref="uploadFile"
              ></vue-dropzone>
            </div>
          </div>
        </div>
        <div class="b-form-group form-group">
          <div class="form-row">
            <label class="col-sm-4 col-form-label">Auto - {{
                $t('clientsmanageclientseller.password')
              }}</label>
            <div class="col-sm-5">
              <c-switch class="mx-1" color="primary"
                        v-model="form.auto_password"
                        variant="pill">
              </c-switch>
            </div>
          </div>
        </div>
        <div class="b-form-group form-group" v-if="form.auto_password != 1">
          <div class="form-row">
            <label class="col-sm-2 col-form-label" for="password">{{
                $t('clientsmanageclientseller.password')
              }}</label>
            <div class="col-sm-5">
              <input :class="{'form-control':true }"
                     autocomplete="off" id="password" name="password"
                     placeholder="Password" ref="password" type="password" v-model="form.password"
                     v-validate="'min:6|max:35'">
              <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                   style="margin-left: 5px;" v-show="errors.has('password')"/>
                <span v-show="errors.has('password')">{{ errors.first('password') }}</span>
              </div>
            </div>
          </div>
        </div>
        <div class="b-form-group form-group" v-if="form.auto_password != 1">
          <div class="form-row">
            <label class="col-sm-2 col-form-label" for="confirm_password">{{
                $t('clientsmanageclientseller.confirm_password')
              }}</label>
            <div class="col-sm-5">
              <input :class="{'form-control':true }"
                     autocomplete="off" data-vv-as="password"
                     id="confirm_password" name="confirm_password" placeholder="Confirm password"
                     type="password" v-model="form.confirm_password"
                     v-validate="'confirmed:password'">

              <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                   style="margin-left: 5px;" v-show="errors.has('confirm_password')"/>
                <span v-show="errors.has('confirm_password')">{{ errors.first('confirm_password') }}</span>
              </div>
            </div>
          </div>
        </div>
        <div class="b-form-group form-group" v-if="$can('read', 'assignsellerrol')">
          <div class="form-row">
            <label class="col-sm-2 col-form-label">{{ $t('users.rol') }}</label>
            <div class="col-sm-5">
              <select class="form-control" id="role_id" required size="0" v-model="role_id">
                <option value=""></option>
                <option :value="rol.value" v-for="rol in roles">
                  {{ rol.value }} - {{ rol.text }}
                </option>
              </select>
            </div>
          </div>
        </div>
        <div class="b-form-group form-group">
          <div class="form-row">
            <label class="col-sm-4 col-form-label">{{ $t('global.status') }}</label>
            <div class="col-sm-5">
              <c-switch class="mx-1" color="primary"
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
          {{ $t('global.buttons.save') }}
        </button>
        <button @click="close" class="btn btn-danger" type="reset" v-if="!loading">
          {{ $t('global.buttons.cancel') }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { API } from './../../../../api'
import VueBootstrapTypeahead from 'vue-bootstrap-typeahead'
import { Switch as cSwitch } from '@coreui/vue'
import 'vue2-dropzone/dist/vue2Dropzone.min.css'
import vue2Dropzone from 'vue2-dropzone'

export default {
  props: ['form'],
  components: {
    VueBootstrapTypeahead,
    cSwitch,
    vueDropzone: vue2Dropzone,
  },
  data: () => {
    return {
      images: [],
      roles: [],
      status: false,
      charged: false,
      zip_code: '',
      id_image: '',
      url_image: '',
      languages: [],
      clients: [],
      hotel: null,
      role_id: null,
      showError: false,
      currentLang: '1',
      invalidError: false,
      countError: 0,
      loading: false,
      dropzoneOptions: {
        url: window.origin + '/api/galeries/image',
        cursor: 'move',
        acceptedFiles: 'image/*',
        maxFiles: 1,
        thumbnailWidth: 200,
        maxFilesize: 1.5,
        addRemoveLinks: true,
        dictDefaultMessage: '<i class=\'fas fa-cloud-upload\'></i> Upload File',
        headers: { 'Authorization': 'Bearer ' + localStorage.getItem('access_token') },
      },
      use_import: false,
      contact: '',
      contacts: [],
    }
  },
  computed: {
    validError: function () {
      if (this.errors.has('name') == false && this.form.name != '') {
        this.invalidError = false
        this.countError += 1
        return true

      } else if (this.countError > 0) {
        this.invalidError = true
      }
      return false
    },
  },
  mounted: function () {

    API.get('/roles/selectBox').then((result) => {
      this.roles = result.data.data
      API.get('/languages/').then((result) => {
        this.languages = result.data.data
        this.currentLang = result.data.data[0].id
        this.form.lang = localStorage.getItem('lang')

        let form = {}

        if (this.form.id !== null) {

          API.get('/sellers/' + this.form.id + '?lang=' + localStorage.getItem('lang')).then((result) => {

            form.seller = result.data.data
            this.form.action = 'put'
            // this.form.code = form.seller.code
            this.form.name = form.seller.name
            this.form.email = form.seller.email
            this.form.auto_password = form.seller.auto_password == '1' ? true : false
            this.form.status = form.seller.status == '1' ? true : false
            this.role_id = form.seller.role_id
            console.log(this.role_id)
            //image logo
            if (form.seller.galeries.length > 0) {
              this.form.images = form.seller.galeries[0]
              this.url_image = form.seller.galeries[0].url
              this.id_image = form.seller.galeries[0].id

              let imageObj = {
                name: 'galeries/' + this.url_image + '?' + Date.now(),
                size: 1176,
                type: 'image/png',
              }
              if (this.$refs.uploadFile != undefined) {
                this.$refs.uploadFile.manuallyAddFile(imageObj,
                    '/images/galeries/' + this.url_image + '?' + Date.now())
              }
            }
          })
        }

      }).catch(() => {
        this.$notify({
          group: 'main',
          type: 'error',
          title: this.$t('clientsmanageclientseller.error.messages.name'),
          text: this.$t('clientsmanageclientseller.error.messages.connection_error'),
        })
      })
    })
    this.get_contacts()
  },
  methods: {
    set_data_contact (contact_id) {
      this.contacts.forEach((c) => {
        if (contact_id === c.id) {
          this.form.name = c.name + ' ' + c.surname
          this.form.email = c.email
          return
        }
      })
    },
    get_contacts () {
      API.get('client_contacts?client_id=' + this.$route.params.client_id).then((result) => {
        this.loading = false
        if (result.data.success === true) {
          this.contacts = result.data.data
        } else {
          this.$notify({
            group: 'main',
            type: 'error',
            title: 'Fetch Error',
            text: result.data.message,
          })
        }
      }).catch(() => {
        this.$notify({
          group: 'main',
          type: 'error',
          title: 'Fetch Error',
          text: 'Cannot load data',
        })
      })
    },
    dropzoneSuccess: function (file, response) {
      this.charged = true
      this.images = []
      this.images.push(response.timestamp)
    },
    close () {
      this.$emit('changeStatus', false)
    },
    validateBeforeSubmit () {
      if (this.form.period === null) {
        this.$notify({
          group: 'main',
          type: 'error',
          title: this.$t('clientsmanageclientseller.markup'),
          text: this.$t('clientsmanageclientseller.error.messages.select_period'),
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
            title: this.$t('global.modules.contacts'),
            text: this.$t('clientsmanageclientseller.error.messages.information_complete'),
          })
          this.loading = false
        }
      })
    },
    //delete image
    dropzoneRemoveFile () {
      if (this.id_image != '') {
        API.delete('/seller/logo/image/' + this.id_image).then((result) => {
          if (result.data.success === false) {
            this.$notify({
              group: 'main',
              type: 'error',
              title: this.$t('clientsmanageclientseller.hotel_name'),
              text: this.$t('clientsmanageclientseller.error.messages.connection_error'),
            })
          }
        })
        this.id_image = ''
      }
    },
    submit () {

      //status
      if (this.form.action == 'put') {
        this.form.status_seller = (this.form.status_seller == false ? 0 : 1)
      }
      this.form.role = this.role_id
      this.loading = true
      API({
        method: this.form.action,
        url: 'sellers' + (this.form.id !== null ? '/' + this.form.id : ''),
        data: this.form,
      }).then((result) => {
        if (result.data.success === false) {
          if (result.data.error != '') {
            if (result.data.error == 'email validation.unique') {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('clientsmanageclientseller.title'),
                text: 'El email ingresado ya se encuentra en uso, ingrese uno nuevo.',
              })
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('clientsmanageclientseller.title'),
                text: result.data.error,
              })
            }
          } else {
            this.$notify({
              group: 'main',
              type: 'error',
              title: this.$t('clientsmanageclientseller.title'),
              text: this.$t('clientsmanageclientseller.error.messages.incorrect'),
            })
          }

          this.loading = false
        } else {
          if (this.charged == true) {
            this.images.forEach((image, key) => {
              this.position = 0
              let formImagen = {
                image: image,
                type: 'client',
                object_id: result.data.object_id,
                url: '',
                slug: 'client_logo',
                position: this.position,
                state: true,
              }

              API({
                method: 'put',
                url: 'client/gallery/logo/',
                data: formImagen,
              }).then((result) => {
                if (result.data.success === false) {
                  this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('global.name'),
                    text: this.$t('clientsmanageclientseller.error.messages.gallery_incorrect'),
                  })
                  this.loading = false
                }
              })
            })
          } else {
            this.charged = false
            this.id_image = ''
          }
          this.close()
        }
      })
    },
  },
}
</script>

<style lang="stylus">

</style>
