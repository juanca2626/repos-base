<template>
    <div class="vld-parent">
        <loading :active.sync="loading" :can-cancel="false" color="#BD0D12"></loading>
        <div class="row">
            <div class="col-sm-12">
                <form @submit.prevent="validateBeforeSubmit">
                    <div class="b-form-group form-group">
                        <div class="btn-group btn-group-toggle">
                            <button type="button"
                                    v-on:click="currentLang = language.id"
                                    v-bind:key="l"
                                    v-bind:class="['btn btn-secondary', (currentLang == language.id) ? 'active' : '']"
                                    v-for="(language, l) in languages">
                                {{ language.iso.toUpperCase() }}
                            </button>
                        </div>
                    </div>
                    <template v-for="(language, l) in languages">
                        <div class="b-form-group form-group" v-if="currentLang == language.id">
                            <div class="card" style="border:1px solid #ddd;">
                                <header class="card-header">
                                    IDIOMA: <b>{{ language.name.toUpperCase() }}</b>
                                </header>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label v-bind:for="'name-' + language.iso">Destino</label>
                                        <input type="text" class="form-control"
                                            v-model="form.translations[language.id].destiny_name"
                                            v-validate="'required'"
                                            v-bind:id="'name-' + language.iso" />
                                    </div>
                                    <div class="form-group">
                                        <label v-bind:for="'description-' + language.iso">Descripción</label>
                                        <vue-editor
                                            v-model="form.translations_content[language.id].destiny_description"
                                            v-validate="'required'"
                                            :editorToolbar="customToolbar"
                                            v-bind:id="'description-' + language.iso"
                                            v-bind:name="'description-' + language.iso">
                                        </vue-editor>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- div class="b-form-group form-group">
                        <div class="form-row" id="container_country">
                            <label class="col-sm-2 col-form-label" for="destiny_name">Nombre del destino</label>
                            <div class="col-sm-3">
                                <input :class="{'form-control':true }"
                                       id="destiny_name" name="destiny_name"
                                       type="text"
                                       v-model="form.translations[currentLang].destiny_name" v-validate="'required'">
                                <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                    <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                       style="margin-left: 5px;" v-show="errors.has('destiny_name')"/>
                                    <span v-show="errors.has('destiny_name')">{{ errors.first('destiny_name') }}</span>
                                </div>
                            </div>
                            <select class="col-sm-1 form-control" id="lang" required size="0" v-model="currentLang">
                                <option v-bind:value="language.id" v-for="language in languages">
                                    {{ language.iso.toUpperCase() }}
                                </option>
                            </select>
                        </div>
                    </div -->
                    <div class="b-form-group form-group" v-if="showPreviewImageEdit">
                        <img :src="form.url" class="img-thumbnail" style="width: 250px">
                        <button type="button" class="btn btn-danger" @click="changeImage">X</button>
                    </div>
                    <div class="b-form-group form-group" v-if="!showPreviewImageEdit">
                        <b-form-group>
                            <b-form-radio-group
                                id="radio-group-1"
                                v-model="selectedOption"
                                :options="options_radio"
                                name="radio-options"
                            ></b-form-radio-group>
                        </b-form-group>
                    </div>
                    <div class="b-form-group form-group" v-if="selectedOption == 'selected' && !showPreviewImageEdit">
                        <div class="form-row">
                            <label class="col-sm-3 col-form-label">Imagen Cloudinary (carpeta
                                highlights)</label>
                            <div class="col-sm-6">
                                <multiselect
                                    :options="images_cloudinary"
                                    :placeholder="'Seleccione una imagen'"
                                    label="name"
                                    :show-labels="false"
                                    ref="multiselect"
                                    track-by="name"
                                    :option-height="100"
                                    v-model="imagesCloudinarySelected"
                                    id="image"
                                    v-validate="'required'">
                                    <template slot="singleLabel" slot-scope="props">
                                        <img class="option__image" width="140px" :src="props.option.image">
                                        <span class="option__desc">
                                    <span class="option__title">{{ props.option.name }}</span></span>
                                    </template>
                                    <template slot="option" slot-scope="props">
                                        <img class="option__image" :src="props.option.image"
                                             width="140px">
                                        <div class="option__desc">
                                            <span class="option__title">{{ props.option.name }}</span>
                                        </div>
                                    </template>
                                </multiselect>
                                <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                    <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                       style="margin-left: 5px;" v-show="errors.has('image')"/>
                                    <span v-show="errors.has('folder')">{{ errors.first('image') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="b-form-group form-group" v-if="selectedOption == 'upload' && !showPreviewImageEdit" style="position:relative">
                        <vue-dropzone :options="dropzoneOptions" @vdropzone-removed-file='dropzoneRemoveFile'
                                      v-on:vdropzone-sending="formsAdd"
                                      :useCustomSlot=true
                                      @vdropzone-success="dropzoneSuccess"
                                      id="uploadFile"
                                      ref="myDropzone">
                            <div class="dropzone-custom-content">
                                <h3 class="dropzone-custom-title">Arrastra y suelta para subir contenido!</h3>
                                <div class="subtitle">...o haga clic para seleccionar un archivo de su computadora</div>
                            </div>
                        </vue-dropzone>
                    </div>
                </form>
            </div>
            <div class="col-sm-6">
                <div slot="footer">
                    <img src="/images/loading.svg" v-if="loading" width="40px"/>
                    <button @click="validateBeforeSubmit" class="btn btn-success" type="submit" v-if="!loading"
                            v-show="selectedOption === 'selected'">
                        <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                        {{ $t('global.buttons.submit') }}
                    </button>
                    <button @click="submitUpload" class="btn btn-success" type="submit" v-if="!loading"
                            v-show="selectedOption === 'upload'">
                        <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                        {{ $t('global.buttons.submit') }}
                    </button>
                    <router-link :to="{ name: 'PackageHighLightsList' }" v-if="!loading">
                        <button class="btn btn-danger" type="reset">
                            {{ $t('global.buttons.cancel') }}
                        </button>
                    </router-link>
                </div>
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
    import Loading from 'vue-loading-overlay'
    import 'vue-loading-overlay/dist/vue-loading.css'
    import { VueEditor } from 'vue2-editor'

    export default {
        components: {
            BModal,
            Multiselect,
            vueDropzone: vue2Dropzone,
            Loading,
            VueEditor,
        },
        data: () => {
            return {
                languages: [],
                images_cloudinary: [],
                imagesCloudinarySelected: [],
                destination: null,
                showPreviewImageEdit: false,
                showError: false,
                currentLang: 1,
                invalidError: false,
                countError: 0,
                loading: false,
                optionSelect: false,
                formAction: 'post',
                selectedOption: 'upload',
                form: {
                    url: '',
                    translations: {
                        '1': {
                            'id': '',
                            'destiny_name': ''
                        }
                    },
                    translations_content: {
                        '1': {
                            'id': '',
                            'destiny_description': ''
                        }
                    }
                },
                options_radio: [
                    { text: 'Cargar una imagen', value: 'upload' },
                    { text: 'Seleccionar una imagen', value: 'selected' },
                ],
                dropzoneOptions: {
                    url: window.origin + '/api/highlights/upload',
                    cursor: 'move',
                    acceptedFiles: 'image/*',
                    maxFiles: 1,
                    thumbnailWidth: 150,
                    maxFilesize: 1.5,
                    addRemoveLinks: true,
                    autoProcessQueue: false,
                    headers: { 'Authorization': 'Bearer ' + localStorage.getItem('access_token') }
                },
                customToolbar: [[{ header: [false, 1, 2, 3, 4, 5, 6] }], ['bold', 'italic', 'underline'], [{ list: 'ordered' }, { list: 'bullet' }], [{ align: '' }, { align: 'center' }, { align: 'right' }, { align: 'justify' }], [{ color: [] }, { background: [] }], ['clean']],
            }
        },
        computed: {},
        mounted: function () {
            API.get('/folder/highlights').then((result) => {
                const images = Array.isArray(result.data.data) ? result.data.data : []
                images.forEach((image) => {
                    this.images_cloudinary.push({
                        name: image.filename,
                        image: image.resizes.low,
                        path: image.secure_url
                    })
                })
            })
            API.get('/languages/')
                .then((result) => {
                    let vm = this
                    this.languages = result.data.data
                    this.currentLang = result.data.data[0].id

                    this.languages.forEach((value) => {
                        console.log("Language:" , value)
                        vm.$set(vm.form.translations, value.id, {
                            id: '',
                            destiny_name: '',
                        })

                        vm.$set(vm.form.translations_content, value.id, {
                            id: '',
                            destiny_description: '',
                        })
                    })
                    if (this.$route.params.id !== undefined) {

                        API.get('/image_highlights/' + this.$route.params.id)
                            .then((result) => {
                                this.destination = result.data.data
                                this.formAction = 'put'
                                this.showPreviewImageEdit = true
                                this.selectedOption = 'selected'
                                let arrayTranslations = this.destination[0].translations
                                let arrayTranslationsContent = this.destination[0].translations_content
                                this.form.url = this.destination[0].resizes.low

                                arrayTranslations.forEach((translation) => {
                                    vm.$set(vm.form.translations, translation.language_id, {
                                        id: translation.id,
                                        destiny_name: translation.value,
                                    })
                                })

                                arrayTranslationsContent.forEach((translation) => {
                                    vm.$set(vm.form.translations_content, translation.language_id, {
                                        id: translation.id,
                                        destiny_description: translation.value,
                                    })
                                })
                            }).catch(() => {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('inclusions.error.messages.name'),
                                text: this.$t('inclusions.error.messages.connection_error')
                            })
                        })
                    }

                    //this.form = form
                }).catch(() => {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('inclusions.error.messages.name'),
                    text: this.$t('inclusions.error.messages.connection_error')
                })
            })
        },
        methods: {
            formsAdd (file, xhr, formData) {
                formData.append('id', (this.$route.params.id !== undefined ? this.$route.params.id : ''))
                formData.append('url', this.form.url)
                formData.append('translations', JSON.stringify(this.form.translations))
            },
            dropzoneSuccess: function (file, response) {
                this.loading = false
                this.$router.push({ name: 'PackageHighLightsList' })
            },
            validateBeforeSubmit () {
                this.$validator.validateAll().then((result) => {
                    if (result) {
                        this.submit()
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.photos'),
                            text: this.$t('inclusions.error.messages.information_complete')
                        })
                        this.loading = false
                    }
                })
            },
            submitUpload () {
                this.$validator.validateAll().then((result) => {
                    if (result) {
                        this.loading = true
                        this.$refs.myDropzone.processQueue()
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.photos'),
                            text: this.$t('inclusions.error.messages.information_complete')
                        })
                        this.loading = false
                    }
                })
            },
            submit () {
                this.loading = true
                this.form.url = this.imagesCloudinarySelected.path
                API({
                    method: this.formAction,
                    url: 'image_highlights/' + (this.$route.params.id !== undefined ? this.$route.params.id : ''),
                    data: this.form
                })
                    .then((result) => {
                        if (result.data.success === false) {
                            if (result.data.error.length > 0 && result.data.error[0] === 'validation.unique') {
                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: this.$t('global.modules.photos'),
                                    text: 'La carpeta de cloudinary ya se encuentra registrado.'
                                })
                            } else {
                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: this.$t('global.modules.photos'),
                                    text: this.$t('inclusions.error.messages.information_error')
                                })
                            }
                            this.loading = false
                        } else {
                            this.$router.push({ name: 'PackageHighLightsList' })
                        }
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('inclusions.error.messages.name'),
                        text: this.$t('inclusions.error.messages.connection_error')
                    })
                })
            },
            remove () {
                this.loading = true
                API({
                    method: 'DELETE',
                    url: 'multimedia/' + (this.$route.params.id !== undefined ? this.$route.params.id : '')
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.$router.push('/photos/list')
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.photos'),
                                text: this.$t('inclusions.error.messages.country_delete')
                            })

                            this.loading = false
                        }
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('inclusions.error.messages.name'),
                        text: this.$t('inclusions.error.messages.connection_error')
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
                                    title: this.$t('global.modules.gallery'),
                                    text: this.$t('global.error.messages.connection_error')
                                })
                            }
                        })
                }
            },
            addImagesCloudinary (newTag) {
                const tag = {
                    name: newTag,
                    code: newTag.substring(0, 2) + Math.floor((Math.random() * 10000000))
                }
                this.imagesCloudinarySelected.push(tag)
            },
            changeImage:function () {
                this.showPreviewImageEdit = false
            }

        }
    }
</script>

<style lang="stylus">
    #container_country {
        margin-bottom 15px
    }

    .option__desc, .option__image {
        display: inline-block;
        vertical-align: middle;
    }

    .dropzone-custom-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
    }

    .dropzone-custom-title {
        margin-top: 0;
        color: #00b782;
    }

    .subtitle {
        color: #314b5f;
    }
</style>
<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>
