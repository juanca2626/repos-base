<template>
    <div class="row">
        <div class="col-sm-12">
            <form @submit.prevent="validateBeforeSubmit">
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="frequentquestion_name">Categorias</label>
                        <div class="col-sm-7">
                            <select class="form-control" id="categories" name="categories" required size="0" v-model="form.category_id" v-validate="'required'">
                                <option v-bind:value="category.id" v-for="category in categories">
                                    {{ category.translations[0].value }}
                                </option>
                            </select>
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('categories')" />
                                <span v-show="errors.has('categories')">{{ errors.first('categories') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="form-row" id="container_country">
                        <label class="col-sm-2 col-form-label" for="frequentquestion_name">Pregunta</label>
                        <div class="col-sm-7">
                            <input :class="{'form-control':true }"
                                   id="frequentquestion_name" name="frequentquestion_name"
                                   type="text"
                                   v-model="form.translations[currentLang].frequentquestion_name" v-validate="'required'">
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('frequentquestion_name')" />
                                <span v-show="errors.has('frequentquestion_name')">{{ errors.first('frequentquestion_name') }}</span>
                            </div>
                        </div>
                        <select class="col-sm-1 form-control" id="lang" required size="0" v-model="currentLang">
                            <option v-bind:value="language.id" v-for="language in languages">
                                {{ language.iso }}
                            </option>
                        </select>
                    </div>
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="frequentquestion_answer">Respuesta</label>
                        <div class="col-sm-7">
                            <textarea :class="{'form-control':true }"
                                   id="frequentquestion_answer" name="frequentquestion_answer"
                                   rows="5"
                                      v-model="form.translations_answer[currentLang].frequentquestion_answer" v-validate="'required'"></textarea>
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('frequentquestion_answer')" />
                                <span v-show="errors.has('frequentquestion_answer')">{{ errors.first('frequentquestion_name') }}</span>
                            </div>
                        </div>
                        <select class="col-sm-1 form-control" id="lang_" required size="0" v-model="currentLang">
                            <option v-bind:value="language.id" v-for="language in languages">
                                {{ language.iso }}
                            </option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-sm-6">
            <div slot="footer">
                <img src="/images/loading.svg" v-if="loading" width="40px" />
                <button @click="validateBeforeSubmit" class="btn btn-success" type="submit" v-if="!loading">
                    <font-awesome-icon :icon="['fas', 'dot-circle']" />
                    {{ $t('global.buttons.submit') }}
                </button>
                <router-link :to="{ name: 'FrequentQuestionsList' }" v-if="!loading">
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
            Multiselect,
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

                form_notify: {
                    message: ''
                },
                form: {
                    category_id: '',
                    translations: {
                        '1': {
                            'id': '',
                            'frequentquestion_name': ''
                        }
                    },
                    translations_answer: {
                        '1': {
                            'id': '',
                            'frequentquestion_answer': ''
                        }
                    }
                }
            }
        },
        computed: {},
        mounted: function () {
            this.question_categories()
            API.get('/languages/')
                .then((result) => {
                    this.languages = result.data.data
                    this.currentLang = result.data.data[0].id
                    let form = this.form
                    let languages = this.languages
                    languages.forEach((value) => {
                        form.translations[value.id] = {
                            id: '',
                            frequentquestion_name: ''
                        }
                        form.translations_answer[value.id] = {
                            id: '',
                            frequentquestion_answer: ''
                        }
                    })
                    if (this.$route.params.id !== undefined) {

                        API.get('/frequent_questions/' + this.$route.params.id)
                            .then((result) => {
                                this.inclusion = result.data.data
                                this.formAction = 'put'
                                this.form.category_id = this.inclusion[0].category.id
                                let arrayTranslations = this.inclusion[0].translations_question
                                arrayTranslations.forEach((translation) => {
                                    form.translations[translation.language_id] = {
                                        id: translation.id,
                                        frequentquestion_name: translation.value
                                    }
                                })
                                let arrayTranslationsAnswer = this.inclusion[0].translations_answer
                                arrayTranslationsAnswer.forEach((translation) => {
                                    form.translations_answer[translation.language_id] = {
                                        id: translation.id,
                                        frequentquestion_answer: translation.value
                                    }
                                })
                            }).catch(() => {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.frequentquestions'),
                                text: this.$t('inclusions.error.messages.connection_error')
                            })
                        })
                    }

                    this.form = form
                }).catch(() => {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('global.modules.frequentquestions'),
                    text: this.$t('inclusions.error.messages.connection_error')
                })
            })
        },
        methods: {
            question_categories: function () {
                //this.loading = true
                API.get('question_categories/selectBox?lang=' + localStorage.getItem('lang')).then((result) => {
                    //this.loading = false
                    if (result.data.success === true) {
                        this.categories = result.data.data
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Fetch Error',
                            text: result.data.message
                        })
                    }
                })
                    .catch((error) => {
                        this.loading = false
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: error.response.data.title,
                            text: error.response.data.message
                        })
                    })
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
                            title: this.$t('global.modules.frequentquestions'),
                            text: this.$t('inclusions.error.messages.information_complete')
                        })
                        this.loading = false
                    }
                })
            },
            submit () {
                API({
                    method: this.formAction,
                    url: 'frequent_questions/' + (this.$route.params.id !== undefined ? this.$route.params.id : ''),
                    data: this.form
                })
                    .then((result) => {
                        if (result.data.success === false) {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.frequentquestions'),
                                text: this.$t('inclusions.error.messages.information_error')
                            })
                            this.loading = false
                        } else {
                            this.$router.push('/frequent_questions/list')
                        }
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.frequentquestions'),
                        text: this.$t('inclusions.error.messages.connection_error')
                    })
                })
            },
            remove () {
                this.loading = true
                API({
                    method: 'DELETE',
                    url: 'physical_intensities/' + (this.$route.params.id !== undefined ? this.$route.params.id : '')
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.$router.push('/frequent_questions/list')
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.frequentquestions'),
                                text: this.$t('inclusions.error.messages.country_delete')
                            })

                            this.loading = false
                        }
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.frequentquestions'),
                        text: this.$t('inclusions.error.messages.connection_error')
                    })
                })
            },
        }
    }
</script>

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>
