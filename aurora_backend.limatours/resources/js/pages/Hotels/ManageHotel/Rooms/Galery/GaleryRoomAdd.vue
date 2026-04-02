<template>
    <b-card-text>
        <b-tabs content-class="mt-3">
            <b-tab title="Cargar Urls" active>
                <div>
                    <template v-for="(url, index) in urls">
                        <div class="form-row">
                            <label :for="index" class="col-sm-1 col-form-label">Url</label>
                            <div class="col-sm-4">
                                <input :class="{'form-control':true }"
                                       :id="'url_'+index" :name="'url_'+index"
                                       type="text"
                                       v-model="url.url" v-validate="'required|url'">
                                <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;"
                                     v-show="errors.has('url_'+index)">
                                    <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                       style="margin-left: 5px;"/>
                                    <span>{{ errors.first('url_'+index) }}</span>
                                </div>
                            </div>
                            <div class="form-group col-sm-2" v-if="index === 0">
                                <button @click="addUrl()" class="btn btn-success" type="button">
                                    <font-awesome-icon :icon="['fas', 'plus']"/>
                                </button>
                            </div>
                            <div class="form-group col-sm-2" v-if="index !== 0">
                                <button @click="deleteUrl(index)" class="btn btn-danger" type="button">
                                    <font-awesome-icon :icon="['fas', 'trash']"/>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
                <router-link :to="getRouteGaleryList()" v-if="this.$route.params.room_id !== undefined">
                    <button class="btn btn-danger" type="button" :disabled="loading">
                        <i class="fa fa-spin fa-spinner" v-if="loading"></i>
                        {{ $t('global.buttons.cancel') }}
                    </button>
                </router-link>
                <button class="btn btn-success" type="button" @click="sendUrls()" :disabled="loading">
                    <i class="fa fa-spin fa-spinner" v-if="loading"></i>
                    {{ $t('global.buttons.save') }}
                </button>
            </b-tab>
        </b-tabs>
    </b-card-text>
</template>
<script>
    import { API } from '../../../../../api'
    import BCardText from 'bootstrap-vue/es/components/card/card-text'
    import vue2Dropzone from 'vue2-dropzone'
    import 'vue2-dropzone/dist/vue2Dropzone.min.css'

    export default {
        components: {
            BCardText,
            vueDropzone: vue2Dropzone
        },
        data: () => {
            return {
                images_names: [],
                position: 0,
                showTab: true,
                urls: [
                    { url: '' }
                ],
                form: {
                    image: '',
                    type: 'room',
                    object_id: '',
                    url: '',
                    slug: '',
                    position: '',
                    state: true
                },
                charged: false,
                formAction: 'post',
                loading: false,
                dropzoneOptions: {
                    url: window.origin + '/api/galeries/image',
                    cursor: 'move',
                    acceptedFiles: 'image/*',
                    maxFiles: 10,
                    thumbnailWidth: 150,
                    maxFilesize: 1,
                    addRemoveLinks: true,
                    dictDefaultMessage: '<i class=\'fas fa-cloud-upload\'></i> Upload File',
                    headers: { 'Authorization': 'Bearer ' + localStorage.getItem('access_token') }
                }
            }
        },
        computed: {},
        created: function () {
        },
        mounted () {

        },
        methods: {
            addUrl: function () {
                this.urls.push({ url: '' })
            },
            deleteUrl: function (index) {
                this.urls.splice(index, 1)
            },
            selectTab: function (tab) {
                this.showTab = tab
            },
            dropzoneSuccess: function (file, response) {
                this.charged = true
                this.images_names.push(response.timestamp)
            },
            getRouteGaleryList: function () {
                if (this.$route.params.room_id !== undefined) {

                    return '/hotels/' + this.$route.params.hotel_id + '/manage_hotel/rooms/edit/' + this.$route.params.room_id +
                        '/galery/list'
                } else {
                    return '/hotels/' + this.$route.params.hotel_id + '/manage_hotel/rooms/add'
                }
            },
            submit: function (object_id) {

                if (this.charged) {
                    API({
                        method: 'post',
                        url: 'galeries/max/position',
                        data: { object_id: object_id, type: 'room' }
                    })
                        .then((result) => {
                            if (result.data.success === true) {
                                if (result.data.position != null) {

                                    this.position = result.data.position
                                }
                                this.images_names.forEach((image, key) => {
                                    this.position = this.position + 1
                                    let form = {
                                        image: image,
                                        type: 'room',
                                        object_id: object_id,
                                        url: '',
                                        slug: '',
                                        position: this.position,
                                        state: true
                                    }

                                    API({
                                        method: this.formAction,
                                        url: 'galeries/',
                                        data: form
                                    })
                                        .then((result) => {
                                            if (result.data.success === true) {

                                            } else {
                                                this.$notify({
                                                    group: 'main',
                                                    type: 'error',
                                                    title: this.$t('global.modules.galeries'),
                                                    text: this.$t('hotelsmanagehotelrooms.error.messages.galery_incorrect')
                                                })
                                            }
                                        }).catch(() => {
                                        this.$notify({
                                            group: 'main',
                                            type: 'error',
                                            title: this.$t('hotelsmanagehotelrooms.error.messages.name'),
                                            text: this.$t('hotelsmanagehotelrooms.error.messages.connection_error')
                                        })
                                    })
                                })
                                this.sendUrls(object_id)
                            } else {
                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: this.$t('global.modules.rooms'),
                                    text: this.$t('hotelsmanagehotelrooms.error.messages.information_error')
                                })
                            }
                        })

                } else {
                    this.sendUrls(object_id)
                    console.log('no tiene imagenes cargadas')
                }
            },
            sendUrls: function () {
                let object_id = this.$route.params.room_id
                if (this.urls[0].url !== '' || this.urls.length > 1) {
                    this.loading = true
                    API({
                        method: 'post',
                        url: 'galeries/max/position',
                        data: { object_id: object_id, type: 'room' }
                    })
                        .then((result) => {
                            if (result.data.success === true) {
                                if (result.data.position != null) {

                                    var position = result.data.position
                                }
                                this.urls.forEach((url, key) => {
                                    this.loading = true
                                    position = position + 1
                                    let form = {
                                        type: 'room',
                                        object_id: object_id,
                                        url: url.url,
                                        slug: '',
                                        position: position,
                                        state: true
                                    }

                                    API({
                                        method: this.formAction,
                                        url: 'galeries/add/urls',
                                        data: form
                                    })
                                        .then((result) => {
                                            this.loading = false
                                            if (result.data.success === true) {
                                                this.$notify({
                                                    group: 'main',
                                                    type: 'success',
                                                    title: this.$t('global.modules.galeries'),
                                                    text: 'Se guardo correctamente la URL # ' + (key + 1)
                                                })
                                                if (key === (this.urls.length - 1)){
                                                    this.$router.push(this.getRouteGaleryList())
                                                }
                                            } else {
                                                this.$notify({
                                                    group: 'main',
                                                    type: 'error',
                                                    title: this.$t('global.modules.galeries'),
                                                    text: this.$t('hotelsmanagehotelrooms.error.messages.galery_incorrect')
                                                })
                                            }
                                        }).catch(() => {
                                        this.loading = false

                                        this.$notify({
                                            group: 'main',
                                            type: 'error',
                                            title: this.$t('hotelsmanagehotelrooms.error.messages.name'),
                                            text: this.$t('hotelsmanagehotelrooms.error.messages.connection_error')
                                        })
                                    })
                                })
                            } else {
                                this.loading = false

                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: this.$t('global.modules.rooms'),
                                    text: this.$t('hotelsmanagehotelrooms.error.messages.information_error')
                                })
                            }
                        })
                }else{
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.rooms'),
                        text: 'Debe agregar al menos una URL'
                    })
                }
            },
            remove: function () {

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
                                    text: this.$t('hotelsmanagehotelrooms.error.messages.connection_error')
                                })
                            }
                        })
                }
            }
        }
    }
</script>
<style>
</style>

