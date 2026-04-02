<template>
    <b-card-text>
        <b-nav class="fondo-nav tabs" tabs>
            <b-nav-item :active="changeTab" @click="selectTab(true)">
                <span :class="{'text-danger':changeTab}">{{ $t('servicesmanageservicegallery.gallery_image_load') }}</span>
            </b-nav-item>
            <b-nav-item :active="!changeTab" @click="selectTab(false)">
                <span :class="{'text-danger':!changeTab}">{{ $t('servicesmanageservicegallery.gallery_url_load') }}</span>
            </b-nav-item>
        </b-nav>
        <div class="tab-content" style="padding: 20px">
            <div v-show="changeTab">
                <h3 class="text-center">{{$t('servicesmanageservicegallery.gallery_add_images')}}</h3>
                <div class="form-row" style="margin-bottom: 5px;">
                    <div class="col-sm-12 text-center" id="images">
                        <vue-dropzone :options="dropzoneOptions" @vdropzone-removed-file='dropzoneRemoveFile'
                                      @vdropzone-success="dropzoneSuccess"
                                      id="uploadFile"
                                      ref="uploadFile"
                        ></vue-dropzone>
                    </div>
                </div>

            </div>
            <div style="margin-top: 10px" v-show="!changeTab">
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
                                                   style="margin-left: 5px;" />
                                <span>{{ errors.first('url_'+index) }}</span>
                            </div>
                        </div>
                        <div class="form-group col-sm-2" v-if="index === 0">
                            <button @click="addUrl()" class="btn btn-success" type="button">
                                <font-awesome-icon :icon="['fas', 'plus']" />
                            </button>
                        </div>
                        <div class="form-group col-sm-2" v-if="index !== 0">
                            <button @click="deleteUrl(index)" class="btn btn-danger" type="button">
                                <font-awesome-icon :icon="['fas', 'trash']" />
                            </button>
                        </div>
                    </div>
                </template>
            </div>
            <div class="col-sm-6 p-0">
                <div slot="footer">
                    <button @click="submit()" class="btn btn-success" type="button">
                        <font-awesome-icon :icon="['fas', 'dot-circle']" />
                        {{ $t('global.buttons.submit') }}
                    </button>
                    <router-link :to="getRouteGaleryList()" v-if="!loading">
                        <button class="btn btn-danger" type="reset">
                            {{ $t('global.buttons.cancel') }}
                        </button>
                    </router-link>
                </div>
            </div>
        </div>
    </b-card-text>
</template>
<script>
    import { API } from '../../../../api'
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
                    type: 'state',
                    object_id: '',
                    url: '',
                    slug: 'state_gallery',
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
                    maxFilesize: 1.5,
                    addRemoveLinks: true,
                    dictDefaultMessage: '<i class=\'fas fa-cloud-upload\'></i> Upload File',
                    headers: { 'Authorization': 'Bearer ' + localStorage.getItem('access_token') }
                }
            }
        },
        computed: {
            changeTab: function () {
                return this.showTab
            }
        },
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
                return '/states/' + this.$route.params.state_id + '/manage_state/gallery/list'
            },
            submit: function (object_id) {
                //  url: 'galeries/max/position',
                if (this.charged) {
                    API({
                        method: 'post',
                        url: 'state/gallery/max/position',
                        data: { object_id: this.$route.params.state_id, type: 'state' }
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
                                    type: 'state',
                                    object_id: this.$route.params.state_id,
                                    url: '',
                                    slug: 'state_gallery',
                                    position: this.position,
                                    state: true
                                }
                                console.log(form)
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
                                            title: this.$t('global.modules.gallery'),
                                            text: this.$t('global.error.save')
                                        })
                                    }
                                }).catch(() => {
                                    this.$notify({
                                        group: 'main',
                                        type: 'error',
                                        title: this.$t('global.modules.gallery'),
                                        text: this.$t('global.error.messages.connection_error')
                                    })
                                })
                            })
                            this.sendUrls(this.$route.params.service_id)
                            setTimeout(() => {
                                this.$router.push({ path: '/states/' + this.$route.params.state_id + '/manage_state/gallery/' })
                            }, 1000)
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.gallery'),
                                text: this.$t('global.error.messages.connection_error')
                            })
                        }
                    })

                } else {
                    this.sendUrls(this.$route.params.service_id)
                    setTimeout(() => {
                        this.$router.push({ path: '/states/' + this.$route.params.state_id + '/manage_state/gallery/' })
                    }, 1000)
                }
            },
            sendUrls: function (object_id) {
                if (this.urls[0].url !== '' || this.urls.length > 1) {
                    API({
                        method: 'post',
                        url: 'state/gallery/max/position',
                        data: { object_id: object_id, type: 'state' }
                    })
                    .then((result) => {
                        if (result.data.success === true) {
                            if (result.data.position != null) {
                                var position = result.data.position
                            } else {
                                var position = 0
                            }
                            this.urls.forEach((url, key) => {
                                position = position + 1
                                let form = {
                                    type: 'state',
                                    object_id: this.$route.params.state_id,
                                    url: url.url,
                                    slug: 'state_gallery',
                                    position: position,
                                    state: true
                                }

                                API({
                                    method: this.formAction,
                                    url: 'galeries/add/urls',
                                    data: form
                                })
                                .then((result) => {
                                    if (result.data.success === true) {
                                        this.urls = []

                                    } else {
                                        this.$notify({
                                            group: 'main',
                                            type: 'error',
                                            title: this.$t('global.modules.gallery'),
                                            text: this.$t('global.error.save')
                                        })
                                    }
                                }).catch(() => {
                                    this.$notify({
                                        group: 'main',
                                        type: 'error',
                                        title: this.$t('global.modules.gallery'),
                                        text: this.$t('global.error.messages.connection_error')
                                    })
                                })
                            })
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.gallery'),
                                text: this.$t('global.error.messages.connection_error')
                            })
                        }
                    })
                }
            },
            dropzoneRemoveFile () {
                if (this.$route.params.id) {
                    API.delete('/galeries/image/' + this.$route.params.id)
                    .then((result) => {
                        if (result.data.success === false) {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.gallery'),
                                text: this.$t('global.error.messages.connection_error')
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

