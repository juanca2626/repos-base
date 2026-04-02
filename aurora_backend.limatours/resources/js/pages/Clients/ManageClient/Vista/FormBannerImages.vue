<template>
    <div class="vld-parent">
        <loading :active.sync="loading" :can-cancel="false" color="#BD0D12"></loading>
        <div class="row mt-3">
            <div class="col-sm-12">
                <div class="form-group row">
                    <div class="col-md-12 mb-2">
                        <div class="divider-text mb-2">
                            <strong>Datos Generales</strong>
                        </div>
                        <hr>
                    </div>
                    <div class="col-sm-3">
                        <label for="name">Nombre de la web</label>
                        <div class="col-sm-12 p-0">
                            <input :class="{'form-control':true }"
                                   data-vv-as="name"
                                   data-vv-name="name"
                                   id="name"
                                   name="name"
                                   type="text"
                                   v-model="form.name"
                                   v-validate="'required'">
                            <span class="invalid-feedback" v-show="errors.has('name')">
                                            <span>{{ errors.first('name') }}</span>
                                        </span>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <label for="email_contac">Email contacto</label>
                        <div class="col-sm-12 p-0">
                            <input :class="{'form-control':true }"
                                   data-vv-as="email_contac"
                                   data-vv-name="email_contac"
                                   id="email_contac"
                                   name="email_contac"
                                   type="text"
                                   v-model="form.email"
                                   v-validate="'required|email'">
                            <span class="invalid-feedback" v-show="errors.has('email_contac')">
                                            <span>{{ errors.first('email_contac') }}</span>
                                        </span>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <label for="phone">Telefono Contacto</label>
                        <div class="col-sm-12 p-0">
                            <input :class="{'form-control':true }"
                                   data-vv-as="phone"
                                   data-vv-name="phone"
                                   id="phone"
                                   name="phone"
                                   type="text"
                                   v-model="form.phone"
                                   v-validate="'required'">
                            <span class="invalid-feedback" v-show="errors.has('phone')">
                                            <span>{{ errors.first('phone') }}</span>
                                        </span>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <label for="direction">Dirección</label>
                        <div class="col-sm-12 p-0">
                            <input :class="{'form-control':true }"
                                   data-vv-as="direction"
                                   data-vv-name="direction"
                                   id="direction"
                                   name="direction"
                                   type="text"
                                   v-model="form.direction"
                                   v-validate="'required'">
                            <span class="invalid-feedback" v-show="errors.has('direction')">
                                            <span>{{ errors.first('direction') }}</span>
                                        </span>
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-md-12 mb-2">
                        <div class="divider-text mb-2">
                            <strong>Redes sociales</strong>
                        </div>
                        <hr>
                    </div>
                    <div class="col-sm-3">
                        <label for="facebook">Facebook (URL)</label>
                        <div class="col-sm-12 p-0">
                            <input :class="{'form-control':true }"
                                   data-vv-as="facebook"
                                   data-vv-name="facebook"
                                   id="facebook"
                                   name="facebook"
                                   type="text"
                                   v-model="form.facebook"
                                   v-validate="'url'">
                            <span class="invalid-feedback" v-show="errors.has('facebook')">
                                            <span>{{ errors.first('facebook') }}</span>
                                        </span>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <label for="facebook">Instagram (URL)</label>
                        <div class="col-sm-12 p-0">
                            <input :class="{'form-control':true }"
                                   data-vv-as="instagram"
                                   data-vv-name="instagram"
                                   id="instagram"
                                   name="instagram"
                                   type="text"
                                   v-model="form.instagram"
                                   v-validate="'url'">
                            <span class="invalid-feedback" v-show="errors.has('instagram')">
                                            <span>{{ errors.first('instagram') }}</span>
                                        </span>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <label for="facebook">Twitter (URL)</label>
                        <div class="col-sm-12 p-0">
                            <input :class="{'form-control':true }"
                                   data-vv-as="twitter"
                                   data-vv-name="twitter"
                                   id="twitter"
                                   name="twitter"
                                   type="text"
                                   v-model="form.twitter"
                                   v-validate="'url'">
                            <span class="invalid-feedback" v-show="errors.has('twitter')">
                                            <span>{{ errors.first('twitter') }}</span>
                                        </span>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <label for="facebook">Whatsapp (URL)</label>
                        <div class="col-sm-12 p-0">
                            <input :class="{'form-control':true }"
                                   data-vv-as="whatsapp"
                                   data-vv-name="whatsapp"
                                   id="whatsapp"
                                   name="whatsapp"
                                   type="text"
                                   v-model="form.whatsapp"
                                   v-validate="'url'">
                            <span class="invalid-feedback" v-show="errors.has('whatsapp')">
                                            <span>{{ errors.first('whatsapp') }}</span>
                                        </span>
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-md-12">
                        <button type="button" class="btn btn-success" @click="submit">Guardar</button>
                        <button type="button" class="btn btn-danger" @click="cancel">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
  import { API } from './../../../../api'
  import VueBootstrapTypeahead from 'vue-bootstrap-typeahead'
  import { Switch as cSwitch } from '@coreui/vue'
  import vSelect from 'vue-select'
  import 'vue-select/dist/vue-select.css'
  import Loading from 'vue-loading-overlay'
  import 'vue-loading-overlay/dist/vue-loading.css'

  export default {
    components: {
      VueBootstrapTypeahead,
      cSwitch,
      Loading,
      vSelect
    },
    data: () => {
      return {
        loading: false,
        images: [],
        languages: [],
        types: [
          { code: 'b2b', label: 'B2B' },
          { code: 'b2c', label: 'B2C' },
        ],
        types_banner: [
          { code: 'images_upload', label: 'Subir images' },
          { code: 'images_url', label: 'Ingresar Url' },
          { code: 'video_url', label: 'Video Url' },
        ],
        form: {
          name: '',
          direction: '',
          email: '',
          phone: '',
          facebook: '',
          instagram: '',
          twitter: '',
          whatsapp: '',
        },
        action: 'post',
      }
    },
    computed: {},
    mounted () {
      //Modificar
      if (this.$route.params.vista_id !== undefined) {
        API.get('/vista/' + this.$route.params.vista_id)
          .then((result) => {
            this.action = 'put'
            this.form.name = result.data.data.name
            this.form.email = result.data.data.email
            this.form.direction = result.data.data.direction
            this.form.phone = result.data.data.phone
            this.form.facebook = result.data.data.facebook
            this.form.instagram = result.data.data.instagram
            this.form.twitter = result.data.data.twitter
            this.form.whatsapp = result.data.data.whatsapp
          })
      }
    },
    methods: {
      submit () {
        this.loading = true
        API({
          method: this.action,
          url: 'vista/' + (this.$route.params.vista_id !== undefined ? this.$route.params.vista_id : ''),
          data: this.form
        })
          .then((result) => {
            if (result.data.success === true) {
              this.$notify({
                group: 'main',
                type: 'success',
                title: 'Vista',
                text: this.$t('global.success.save')
              })
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: 'Vista',
                text: result.data.message
              })
            }
            this.loading = false
          }).catch(() => {
          this.loading = false
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('clientsmanageclientmarkup.error.messages.name'),
            text: this.$t('clientsmanageclientmarkup.error.messages.connection_error')
          })
        })
      },
      cancel: function () {
        this.$router.push({ path: '/clients/' + this.$route.params.client_id + '/manage_client/vista' })
      }
    }
  }
</script>

<style lang="stylus">

</style>
