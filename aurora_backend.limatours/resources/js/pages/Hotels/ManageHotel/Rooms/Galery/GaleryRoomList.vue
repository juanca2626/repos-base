<template>
    <b-card-text>
        <!-- router-link :to="getRouteGaleryNew()" class="nav-link">
            <button class="btn btn-primary" type="button">{{$t('global.buttons.add')}}</button>
        </router-link -->
        <button @click="updateCloudinary()"
            class="btn btn-danger mb-3" type="button">
            Actualizar fotos con Cloudinary
        </button>

        <table class="VueTables__table table table-striped table-bordered table-hover text-center">
            <thead>
            <tr>
                <th scope="col">{{$t('hotelsmanagehotelrooms.room_position')}}</th>
                <th scope="col">{{$t('hotelsmanagehotelrooms.room_image')}}</th>
                <!-- th scope="col">{{$t('hotelsmanagehotelrooms.state')}}</th>
                <th scope="col">{{$t('global.table.actions')}}</th -->
            </tr>
            </thead>
            <tbody>
            <!-- draggable :list="images" @change="updatePositions" tag="tbody" -->
                <tr :key="image.id" v-for="(image,index) in images">
                    <td class="text-center" scope="row">{{ image.position }}</td>
                    <td class="text-center">
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

            API.get('/rooms/' + this.$route.params.room_id + '?lang=' + localStorage.getItem('lang'))
            .then((result) => {
                this.room = result.data.data
                this.images = this.room.galeries
            })
            .catch(() => {

                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('hotelsmanagehotelrooms.error.messages.name'),
                    text: this.$t('hotelsmanagehotelrooms.error.messages.connection_error')
                })
            })
        },
        updateCloudinary: function () {
            this.loading = true;
            API({
                method: 'GET',
                url: `hotel/${this.$route.params.hotel_id}/configurations`,
            })
            .then(({ data }) => {
                const aurora_code = data.data.channel[0].code;

                API({
                    method: 'GET',
                    url: 'cloudinary',
                    params: { folders: `['hotels/${aurora_code}/rooms/${this.$route.params.room_id}']`, token: "KLu1zSv%" }
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
                title: this.$t('global.modules.rooms'),
                text: this.$t('hotelsmanagehotelrooms.error.messages.information_error')
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
                title: this.$t('global.modules.rooms'),
                text: this.$t('hotelsmanagehotelrooms.error.messages.information_error')
              })
            }
          })
      },
      getRouteGaleryNew: function () {

        return '/hotels/' + this.$route.params.hotel_id + '/manage_hotel/rooms/edit/' + this.$route.params.room_id +
          '/galery/add/edit'
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
                title: this.$t('global.modules.galeries'),
                text: this.$t('hotelsmanagehotelrooms.error.messages.galery_delete')
              })

              this.loading = false
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('hotelsmanagehotelrooms.error.messages.name'),
            text: this.$t('hotelsmanagehotelrooms.error.messages.connection_error')
          })
        })
      },
      validURL: function (url, obligatory, ftp) {
        let pattern

        // Si no se especifica el paramatro "obligatory", interpretamos
        // que no es obligatorio
        if (obligatory === undefined)
          obligatory = 0

        // Si no se especifica el parametro "ftp", interpretamos que la
        // direccion no puede ser una direccion a un servidor ftp
        if (ftp === undefined)
          ftp = 0

        if (url === '' && obligatory === 0)
          return true

        if (ftp)
          pattern = /^(http|https|ftp)\:\/\/[a-z0-9\.-]+\.[a-z]{2,4}/gi
        else
          pattern = /^(http|https)\:\/\/[a-z0-9\.-]+\.[a-z]{2,4}/gi

        return !!url.match(pattern)
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

