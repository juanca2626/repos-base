<template>
    <div class="col-12">
        <b-card-text>
            <router-link :to="getRouteGaleryNew()" class="btn" style="padding: 0; float: right; height: 45px;">
                <button class="btn btn-danger mb-4" type="button">{{$t('packagesmanagepackagegallery.gallery_new')}}
                    <font-awesome-icon :icon="['fas', 'plus']" class="nav-icon"/>
                </button>
            </router-link>
            <table class="col-12 VueTables__table table table-striped table-bordered table-hover text-center">
                <thead>
                <tr>
                    <th scope="col">{{$t('packagesmanagepackagegallery.gallery_position')}}</th>
                    <th scope="col">{{$t('packagesmanagepackagegallery.gallery_image')}}</th>
                    <th scope="col">{{$t('global.table.actions')}}</th>
                </tr>
                </thead>
                <draggable :list="images" @change="updatePositions" tag="tbody">
                    <tr :key="image.id" v-for="(image,index) in images">
                        <td class="text-center" scope="row">{{ image.position }}</td>
                        <td class="text-center">
                            <img :src="image.url" alt="" height="45" width="45">
                        </td>
                        <td class="text-center">
                            <button @click="remove(image.id,index)" class="btn btn-danger" type="button">
                                <font-awesome-icon :icon="['fas', 'trash']"/>
                            </button>
                        </td>
                    </tr>
                </draggable>
            </table>
        </b-card-text>
    </div>
</template>
<script>
    import {API} from '../../../../../api'
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
                images: []

            }
        },
        computed: {},
        created: function () {

        },
        mounted() {

            this.listImages()

            this.$root.$on('listPackageImages', (payload) => {
                this.listImages()
            })
        },
        methods: {
            listImages() {
                API.get('/packages/galleries/' + this.$route.params.package_id + '?lang=' + localStorage.getItem('lang'))
                    .then((result) => {

                        result.data.data.forEach( i=>{
                            i.url = this.reformat_URL(i.url)
                        })

                        this.images = result.data.data

                    }).catch(() => {

                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('packagesmanagepackagegallery.error.messages.name'),
                        text: this.$t('packagesmanagepackagegallery.error.messages.connection_error')
                    })
                })
            },
            updatePositions: function () {
                this.images.forEach((value, key) => {

                    this.images[key].position = key + 1

                })
                API({
                    method: 'put',
                    url: 'galeries/update/image/positions',
                    data: {images: this.images}
                })
                    .then((result) => {
                        if (result.data.success === true) {

                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.packages'),
                                text: this.$t('packagesmanagepackagegallery.error.messages.information_error')
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
                    data: {state: this.images[index].state}
                })
                    .then((result) => {
                        if (result.data.success === true) {

                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.packages'),
                                text: this.$t('packagesmanagepackagegallery.error.messages.information_error')
                            })
                        }
                    })
            },
            getRouteGaleryNew: function () {

                return '/packages/' + this.$route.params.package_id + '/manage_package/package_gallery/packagegallery/add'
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
                                text: this.$t('packagesmanagepackagegallery.error.messages.galery_delete')
                            })

                            this.loading = false
                        }
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('packagesmanagepackagegallery.error.messages.name'),
                        text: this.$t('packagesmanagepackagegallery.error.messages.connection_error')
                    })
                })
            },
            reformat_URL: function (url) {

                let image_ = url
                let splitter_ = url.split('cloudinary')

                if (splitter_.length > 1) {
                    let splitter_http_ = url.split('http')
                    if (splitter_http_.length <= 1) {
                        image_ = 'https://' + url
                    }
                } else {
                    image_ = '/images/galeries/'+url
                }

                return image_
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

