<template>
    <div class="row">
        <div class="col-sm-12">
            <form @submit.prevent="validateBeforeSubmit">
                <div class="b-form-group form-group">
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
                                {{ language.iso }}
                            </option>
                        </select>
                    </div>
                </div>
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="folder">Carpeta Cloudinary</label>
                        <div class="col-sm-3">
                            <select class="form-control" id="folder" required size="0" v-model="form.folder"
                                    name="folder" v-validate="'required'">
                                <option v-bind:value="folder.path" v-for="folder in folders">
                                    {{ folder.name }}
                                </option>
                            </select>
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('folder')"/>
                                <span v-show="errors.has('folder')">{{ errors.first('folder') }}</span>
                            </div>
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
                <router-link :to="{ name: 'PhotosList' }" v-if="!loading">
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

    export default {
        components: {
            BModal,
            Multiselect
        },
        data: () => {
            return {
                languages: [],
                folders: [],
                destination: null,
                showError: false,
                currentLang: '1',
                invalidError: false,
                countError: 0,
                loading: false,
                optionSelect: false,
                formAction: 'post',
                form: {
                    folder: '',
                    translations: {
                        '1': {
                            'id': '',
                            'destiny_name': ''
                        }
                    }
                }
            }
        },
        computed: {},
        mounted: function () {
            API.get('/folders/multimedia').then((result) => {
                let folders = result.data.folders
                folders.forEach((folder) => {
                    this.folders.push({
                        name: folder.name,
                        path: folder.path
                    })
                })
            })
            API.get('/languages/')
                .then((result) => {
                    this.languages = result.data.data
                    this.currentLang = result.data.data[0].id
                    let form = this.form
                    let languages = this.languages
                    languages.forEach((value) => {
                        form.translations[value.id] = {
                            id: '',
                            destiny_name: ''
                        }
                    })
                    if (this.$route.params.id !== undefined) {

                        API.get('/multimedia/' + this.$route.params.id)
                            .then((result) => {
                                this.destination = result.data.data
                                this.formAction = 'put'

                                let arrayTranslations = this.destination[0].translations
                                this.form.folder = this.destination[0].folder

                                arrayTranslations.forEach((translation) => {
                                    form.translations[translation.language_id] = {
                                        id: translation.id,
                                        destiny_name: translation.value
                                    }
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

                    this.form = form
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
            validateBeforeSubmit () {
                this.$validator.validateAll().then((result) => {
                    if (result) {
                        this.submit()
                    } else {
                        this.hideModal()
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
                API({
                    method: this.formAction,
                    url: 'multimedia/' + (this.$route.params.id !== undefined ? this.$route.params.id : ''),
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
                            this.$router.push('/photos/list')
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
        }
    }
</script>

<style lang="stylus">
    #container_country
        margin-bottom 15px
</style>
<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>
