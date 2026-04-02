<template>
    <div class="row">
        <div class="col-sm-12">
            <form @submit.prevent="validateBeforeSubmit">
                <div class="b-form-group form-group">
                    <div class="form-row" id="container_country">
                        <label class="col-sm-2 col-form-label" for="questioncategory_name">Categoria</label>
                        <div class="col-sm-4">
                            <input :class="{'form-control':true }"
                                   id="questioncategory_name" name="questioncategory_name"
                                   type="text"
                                   v-model="form.translations[currentLang].questioncategory_name"
                                   v-validate="'required'">
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;"
                                                   v-show="errors.has('questioncategory_name')"/>
                                <span v-show="errors.has('questioncategory_name')">{{ errors.first('questioncategory_name') }}</span>
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
                        <label class="col-sm-2 col-form-label">Icono</label>
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
            </form>
        </div>
        <div class="col-sm-6">
            <div slot="footer">
                <img src="/images/loading.svg" v-if="loading" width="40px"/>
                <button @click="validateBeforeSubmit" class="btn btn-success" type="submit" v-if="!loading">
                    <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                    {{ $t('global.buttons.submit') }}
                </button>
                <router-link :to="{ name: 'QuestionCategoriesList' }" v-if="!loading">
                    <button class="btn btn-danger" type="reset">
                        {{ $t('global.buttons.cancel') }}
                    </button>
                </router-link>
            </div>
        </div>
    </div>
</template>

<script>
    import { API } from './../../api'
    import BModal from 'bootstrap-vue/es/components/modal/modal'
    import Multiselect from 'vue-multiselect'
    import vue2Dropzone from 'vue2-dropzone'
    import 'vue2-dropzone/dist/vue2Dropzone.min.css'

    export default {
        components: {
            BModal,
            Multiselect,
            vueDropzone: vue2Dropzone
        },
        data: () => {
            return {
                languages: [],
                emailsService: [],
                emails: [],
                inclusion: null,
                showError: false,
                currentLang: '1',
                invalidError: false,
                countError: 0,
                loading: false,
                optionSelect: false,
                formAction: 'post',
                form_notify: {
                    message: ''
                },
                images: [],
                form: {
                    translations: {
                        '1': {
                            'id': '',
                            'questioncategory_name': ''
                        }
                    }
                },
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
            }
        },
        computed: {},
        mounted: function () {
            API.get('/languages/')
                .then((result) => {
                    this.languages = result.data.data
                    this.currentLang = result.data.data[0].id
                    let form = this.form
                    let languages = this.languages
                    languages.forEach((value) => {
                        form.translations[value.id] = {
                            id: '',
                            questioncategory_name: ''
                        }
                    })
                    if (this.$route.params.id !== undefined) {

                        API.get('/question_categories/' + this.$route.params.id)
                            .then((result) => {
                                this.inclusion = result.data.data
                                this.formAction = 'put'
                                let arrayTranslations = this.inclusion[0].translations
                                arrayTranslations.forEach((translation) => {
                                    form.translations[translation.language_id] = {
                                        id: translation.id,
                                        questioncategory_name: translation.value
                                    }
                                })

                                if (this.inclusion[0].galleries.length > 0) {
                                    this.images = this.inclusion[0].galleries[0]
                                    this.url_image = this.inclusion[0].galleries[0].url
                                    this.id_image = this.inclusion[0].galleries[0].id

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
                            }).catch(() => {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.question_category'),
                                text: this.$t('inclusions.error.messages.connection_error')
                            })
                        })
                    }

                    this.form = form
                }).catch(() => {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('global.modules.question_category'),
                    text: this.$t('inclusions.error.messages.connection_error')
                })
            })
        },
        methods: {
            dropzoneSuccess: function (file, response) {
                this.charged = true
                this.images = []
                this.images.push(response.timestamp)
            },
            dropzoneRemoveFile () {
                if (this.id_image !== '') {
                    API.delete('/question_categories/image/' + this.id_image)
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
            validateBeforeSubmit () {
                this.$validator.validateAll().then((result) => {
                    if (result) {
                        this.submit()
                    } else {
                        this.hideModal()
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.question_category'),
                            text: this.$t('inclusions.error.messages.information_complete')
                        })
                        this.loading = false
                    }
                })
            },
            submit () {
                this.loading = true
                API({
                    method: this.formAction,
                    url: 'question_categories/' + (this.$route.params.id !== undefined ? this.$route.params.id : ''),
                    data: this.form
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            if (this.charged === true) {
                                this.images.forEach((image, key) => {
                                    this.position = 1
                                    let formImagen = {
                                        image: image,
                                        type: 'question_category',
                                        object_id: result.data.object_id,
                                        url: '',
                                        slug: 'question_category',
                                        position: this.position,
                                        state: true
                                    }

                                    API({
                                        method: 'put',
                                        url: 'question_categories/gallery',
                                        data: formImagen
                                    }).then((result) => {
                                        if (result.data.success === false) {
                                            this.$notify({
                                                group: 'main',
                                                type: 'error',
                                                title: this.$t('global.modules.question_category'),
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
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.question_category'),
                                text: this.$t('inclusions.error.messages.information_error')
                            })
                        }
                        this.$router.push('/question_categories/list')
                        this.loading = false
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.question_category'),
                        text: this.$t('inclusions.error.messages.connection_error')
                    })
                })
            },
            optionSelection: function (option) {
                if (option === 1) {
                    this.optionSelect = true
                } else if (option === 2) {
                    this.$refs['my-modal-notify'].hide()
                    this.validateBeforeSubmit()
                    this.$validator.reset() //solution
                } else {
                    if (this.emailsService.length === 0) {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.question_category'),
                            text: 'Debe seleccionar al menos un email'
                        })
                    } else {
                        this.$refs['my-modal-notify'].hide()
                        this.submit()
                    }
                }
            },
        }
    }
</script>

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>
