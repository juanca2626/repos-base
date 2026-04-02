<template>
    <div class="col-12">
        <b-card-text>
            <!-- router-link :to="getRouteGaleryNew()" class="btn" style="padding: 0; float: right; height: 45px;">
                <button class="btn btn-danger mb-4" type="button">{{$t('servicesmanageservicegallery.gallery_new')}}
                    <font-awesome-icon :icon="['fas', 'plus']" class="nav-icon"/>
                </button>
            </router-link -->
            <button @click="updateCloudinary()" :disabled="loading"
                class="btn btn-danger mb-3" type="button">
                Actualizar fotos con Cloudinary
            </button>

            <table class="col-12 VueTables__table table table-striped table-bordered table-hover text-center">
                <thead>
                <tr>
                    <th scope="col">{{$t('servicesmanageservicegallery.gallery_position')}}</th>
                    <th scope="col">{{$t('servicesmanageservicegallery.gallery_image')}}</th>
                    <!-- th scope="col">{{$t('servicesmanageservicegallery.state')}}</th>
                    <th scope="col">{{$t('global.table.actions')}}</th -->
                </tr>
                </thead>
                <tbody>
                <!-- draggable :list="images" @change="updatePositions" tag="tbody" -->
                    <tr :key="image.id" v-for="(image,index) in images">
                        <td class="text-center" scope="row">{{ image.position }}</td>
                        <td class="text-center">
                            <!-- img :src="'/images/galeries/'+image.url" alt="" height="45" v-if="!validURL(image.url)"
                                 width="45" -->
                            <img :src="image.url" alt="" height="45" width="45" />
                        </td>
                        <!-- td class="text-center">
                            <b-form-checkbox
                                    :checked="checkboxImageChecked(image.state)"
                                    :id="'checkbox_image_'+image.id"
                                    :name="'checkbox_image_'+image.id"
                                    @change="changeImageState(image.id,index)"
                                    switch>
                            </b-form-checkbox>
                        </td>
                        <td class="text-center">
                            <button @click="remove(image.id,index)" class="btn btn-danger" type="button">
                                <font-awesome-icon :icon="['fas', 'trash']"/>
                            </button>
                        </td -->
                    </tr>
                <!-- /draggable -->
                </tbody>
            </table>
        </b-card-text>
    </div>
</template>
<script>
  import { API } from '../../../../../api'
  import BCardText from 'bootstrap-vue/es/components/card/card-text'
  import draggable from 'vuedraggable'
  import BFormCheckbox from 'bootstrap-vue/es/components/form-checkbox/form-checkbox'

  export default {
    components: {
      BCardText,
      draggable,
      BFormCheckbox
    },
    data: () => {
      return {
        loading: false,
        images: []
      }
    },
    computed: {},
    created: function () {

    },
    mounted () {
      this.searchGalleries();
    },
    methods: {
        searchGalleries: function () {
            this.loading = true;
            this.images = [];
            API.get('/services/galleries/' + this.$route.params.service_id + '?lang=' + localStorage.getItem('lang'))
            .then((result) => {
                this.loading = false;
                this.images = result.data.data
            })
            .catch(() => {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('global.modules.gallery'),
                    text: this.$t('global.error.messages.connection_error')
                })
            })
        },
      updateCloudinary: function () {
        this.loading = true;
        API({
            method: 'GET',
            url: `services/${this.$route.params.service_id}`,
        })
          .then(({ data }) => {
            const aurora_code = data.data[0].aurora_code;

            API({
                method: 'GET',
                url: 'cloudinary',
                params: { folders: `['services/${aurora_code}']`, token: "KLu1zSv%" }
            })
            .then(() => {
                this.loading = false;
                this.searchGalleries();
            })
          })
          .catch(() => {
            this.loading = false;
          });
      },
      updatePositions: function () {
        this.images.forEach((value, key) => {
          this.images[key].position = key + 1
        })
        API({
          method: 'put',
          url: 'galeries/update/image/positions',
          data: { images: this.images }
        })
          .then((result) => {
            if (result.data.success === true) {

            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.gallery'),
                text: this.$t('global.error.messages.connection_error')
              })
            }
          })
      },
      checkboxImageChecked: function (image_state) {
        if (image_state) {
          return 'true'
        } else {
          return 'false'
        }
      },
      changeImageState: function (image_id, index) {
        if (this.images[index].state) {

          this.images[index].state = 0
        } else {
          this.images[index].state = 1
        }
        API({
          method: 'put',
          url: 'galeries/update/image/' + image_id + '/state',
          data: { state: this.images[index].state }
        })
          .then((result) => {
            if (result.data.success === true) {

            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.gallery'),
                text: this.$t('global.error.save')
              })
            }
          })
      },
      getRouteGaleryNew: function () {
        return '/services_new/' + this.$route.params.service_id + '/manage_service/gallery/add'
      },
      submit: function () {

      },
      remove: function (image_id, index) {
        API({
          method: 'DELETE',
          url: 'galeries/' + image_id
        })
          .then((result) => {
            if (result.data.success === true) {
              this.images.splice(index, 1)
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.gallery'),
                text: this.$t('global.error.delete')
              })

              this.loading = false
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('global.modules.gallery'),
            text: this.$t('global.error.messages.connection_error')
          })
        })
      },
      validURL: function (url, obligatory, ftp) {
        // Si no se especifica el paramatro "obligatory", interpretamos

        // que no es obligatorio

        if (obligatory == undefined)

          obligatory = 0

        // Si no se especifica el parametro "ftp", interpretamos que la

        // direccion no puede ser una direccion a un servidor ftp

        if (ftp == undefined)

          ftp = 0

        if (url == '' && obligatory == 0)

          return true

        if (ftp)

          var pattern = /^(http|https|ftp)\:\/\/[a-z0-9\.-]+\.[a-z]{2,4}/gi

        else

          var pattern = /^(http|https)\:\/\/[a-z0-9\.-]+\.[a-z]{2,4}/gi

        if (url.match(pattern))

          return true

        else

          return false
      }
    }
  }
</script>
<style scoped>
    .buttons {
        margin-top: 35px;
    }

    .btn-primary {
        background-color: #005ba5;
        color: white;
        border-color: #005ba5;
    }

    .btn-primary:hover {
        background-color: #0b4d75;
    }
</style>

