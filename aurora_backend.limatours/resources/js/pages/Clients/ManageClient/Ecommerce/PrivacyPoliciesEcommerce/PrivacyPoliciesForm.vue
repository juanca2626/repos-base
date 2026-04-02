<template>
    <div class="row">
        <div class="col-sm-12">
            <form @submit.prevent="validateBeforeSubmit">
                <div class="b-form-group form-group">
                    <div class="form-row" id="container_country">
                        <label class="col-sm-12 col-form-label" for="ecommerce_privacy_policy_title">Título</label>
                        <div class="col-sm-10">
                            <input :class="{'form-control':true }"
                                   id="ecommerce_privacy_policy_title" name="ecommerce_privacy_policy_title"
                                   type="text"
                                   v-model="form.translations[currentLang].ecommerce_privacy_policy_title"
                                   v-validate="'required'">
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;"
                                                   v-show="errors.has('ecommerce_privacy_policy_title')"/>
                                <span v-show="errors.has('ecommerce_privacy_policy_title')">{{ errors.first('ecommerce_privacy_policy_title') }}</span>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <select class="form-control" id="lang" required size="0" v-model="currentLang">
                                <option v-bind:value="language.id" v-for="language in languages">
                                    {{ language.iso }}
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <label class="col-sm-12 col-form-label" for="ecommerce_privacy_policy_content">Contenido</label>
                        <div class="col-sm-10">
                            <vue-editor
                                v-model="form.translations_content[currentLang].ecommerce_privacy_policy_content"
                                v-validate="'required'"
                                :editorToolbar="customToolbar"
                                id="ecommerce_privacy_policy_content" name="ecommerce_privacy_policy_content">

                            </vue-editor>
                            <!--                            <textarea :class="{'form-control':true }"-->
                            <!--                                      id="ecommerce_privacy_policy_content" name="ecommerce_privacy_policy_content"-->
                            <!--                                      rows="5"-->
                            <!--                                      v-model="form.translations_content[currentLang].ecommerce_privacy_policy_content" v-validate="'required'"></textarea>-->
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;"
                                                   v-show="errors.has('ecommerce_privacy_policy_content')"/>
                                <span v-show="errors.has('ecommerce_privacy_policy_content')">{{ errors.first('ecommerce_privacy_policy_content') }}</span>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <select class="form-control" id="lang_" required size="0" v-model="currentLang">
                                <option v-bind:value="language.id" v-for="language in languages">
                                    {{ language.iso }}
                                </option>
                            </select>
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
                <router-link :to="{ name: 'PrivacyPoliciesListEcommerce' }" v-if="!loading">
                    <button class="btn btn-danger" type="reset">
                        {{ $t('global.buttons.cancel') }}
                    </button>
                </router-link>
            </div>
        </div>
    </div>
</template>

<script>
    import { API } from './../../../../../api'
    import BModal from 'bootstrap-vue/es/components/modal/modal'
    import Multiselect from 'vue-multiselect'
    import { VueEditor } from 'vue2-editor'

    export default {
        components: {
            BModal,
            Multiselect,
            VueEditor,
        },
        data: () => {
            return {
                categories: [],
                languages: [],
                inclusion: null,
                showError: false,
                currentLang: '1',
                invalidError: false,
                countError: 0,
                loading: false,
                optionSelect: false,
                formAction: 'post',
                customToolbar: [[{ header: [false, 1, 2, 3, 4, 5, 6] }], ['bold', 'italic', 'underline'], [{ list: 'ordered' }, { list: 'bullet' }], [{ align: '' }, { align: 'center' }, { align: 'right' }, { align: 'justify' }], [{ color: [] }, { background: [] }], ['clean']],
                form_notify: {
                    message: ''
                },
                form: {
                    client_id: '',
                    translations: {
                        '1': {
                            'id': '',
                            'ecommerce_privacy_policy_title': ''
                        }
                    },
                    translations_content: {
                        '1': {
                            'id': '',
                            'ecommerce_privacy_policy_content': ''
                        }
                    }
                }
            }
        },
        computed: {},
        mounted: function () {
            this.form.client_id = this.$route.params.client_id
            API.get('/languages/')
                .then((result) => {
                    this.languages = result.data.data
                    this.currentLang = result.data.data[0].id
                    let form = this.form
                    let languages = this.languages
                    languages.forEach((value) => {
                        form.translations[value.id] = {
                            id: '',
                            ecommerce_privacy_policy_title: ''
                        }
                        form.translations_content[value.id] = {
                            id: '',
                            ecommerce_privacy_policy_content: ''
                        }
                    })
                    if (this.$route.params.id !== undefined) {

                        API.get('/client/' + this.$route.params.client_id + '/ecommerce/privacy_policies/' + this.$route.params.id)
                            .then((result) => {
                                this.inclusion = result.data.data
                                this.formAction = 'put'
                                let arrayTranslations = this.inclusion[0].translations_title
                                arrayTranslations.forEach((translation) => {
                                    form.translations[translation.language_id] = {
                                        id: translation.id,
                                        ecommerce_privacy_policy_title: translation.value
                                    }
                                })
                                let arrayTranslationsAnswer = this.inclusion[0].translations_privacy_policy
                                arrayTranslationsAnswer.forEach((translation) => {
                                    form.translations_content[translation.language_id] = {
                                        id: translation.id,
                                        ecommerce_privacy_policy_content: translation.value
                                    }
                                })
                            }).catch(() => {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Ecommerce - Politicas de privacidad',
                                text: this.$t('inclusions.error.messages.connection_error')
                            })
                        })
                    }

                    this.form = form
                }).catch(() => {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: 'Ecommerce - Politicas de privacidad',
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
                            title: 'Ecommerce - Politicas de privacidad',
                            text: this.$t('inclusions.error.messages.information_complete')
                        })
                        this.loading = false
                    }
                })
            },
            submit () {
                API({
                    method: this.formAction,
                    url: 'client/ecommerce/privacy_policies/' + (this.$route.params.id !== undefined ? this.$route.params.id : ''),
                    data: this.form
                })
                    .then((result) => {
                        if (result.data.success === false) {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Ecommerce - Politicas de privacidad',
                                text: this.$t('inclusions.error.messages.information_error')
                            })
                            this.loading = false
                        } else {
                            this.$router.push({name :'PrivacyPoliciesListEcommerce'})
                        }
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Ecommerce - Politicas de privacidad',
                        text: this.$t('inclusions.error.messages.connection_error')
                    })
                })
            },

        }
    }
</script>

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>
