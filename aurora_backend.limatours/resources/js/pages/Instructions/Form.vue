<template>
    <div class="row">
        <div class="col-sm-12">
            <form @submit.prevent="validateBeforeSubmit">
                <div class="b-form-group form-group">
                    <div class="form-row" id="container_country">
                        <label class="col-sm-2 col-form-label" for="instruction_name">{{ $t('instruction.instruction_name')
                            }}</label>
                        <div class="col-sm-4">
                            <input :class="{'form-control':true }"
                                   id="instruction_name" name="instruction_name"
                                   type="text"
                                   v-model="form.translations[currentLang].instruction_name" v-validate="'required'">
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('instruction_name')" />
                                <span v-show="errors.has('instruction_name')">{{ errors.first('instruction_name') }}</span>
                            </div>
                        </div>
                        <select class="col-sm-1 form-control" id="lang" required size="0" v-model="currentLang">
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
                <router-link :to="{ name: 'InstructionsList' }" v-if="!loading">
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
                form: {
                    translations: {
                        '1': {
                            'id': '',
                            'instruction_name': ''
                        }
                    }
                }
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
                            instruction_name: ''
                        }
                    })
                    if (this.$route.params.id !== undefined) {

                        API.get('/instructions/' + this.$route.params.id)
                            .then((result) => {
                                this.inclusion = result.data.data
                                this.formAction = 'put'

                                let arrayTranslations = this.inclusion[0].translations

                                arrayTranslations.forEach((translation) => {
                                    form.translations[translation.language_id] = {
                                        id: translation.id,
                                        instruction_name: translation.value
                                    }
                                })
                            }).catch(() => {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.instruction'),
                                text: this.$t('inclusions.error.messages.connection_error')
                            })
                        })
                    }

                    this.form = form
                }).catch(() => {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('global.modules.instruction'),
                    text: this.$t('inclusions.error.messages.connection_error')
                })
            })
        },
        methods: {
            validateBeforeSubmitOpt () {
                this.$validator.validateAll().then((result) => {
                    if (result) {
                        this.optionSelect = false
                        this.$refs['my-modal-notify'].show()
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.instruction'),
                            text: this.$t('inclusions.error.messages.information_complete')
                        })
                        this.loading = false
                    }
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
                            title: this.$t('global.modules.instruction'),
                            text: this.$t('inclusions.error.messages.information_complete')
                        })
                        this.loading = false
                    }
                })
            },
            submit () {
                this.loading = true
                this.form.hasNotify = this.optionSelect
                this.form.emails = this.emailsService
                this.form.message = this.form_notify.message
                API({
                    method: this.formAction,
                    url: 'instructions' + (this.$route.params.id !== undefined ? '/'+ this.$route.params.id : ''),
                    data: this.form
                })
                    .then((result) => {
                        if (result.data.success === false) {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.instruction'),
                                text: this.$t('inclusions.error.messages.information_error')
                            })
                            this.loading = false
                        } else {
                            this.$router.push('/instructions/list')
                        }
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.instruction'),
                        text: this.$t('inclusions.error.messages.connection_error')
                    })
                })
            },
            remove () {
                this.loading = true
                API({
                    method: 'DELETE',
                    url: 'instructions/' + (this.$route.params.id !== undefined ? this.$route.params.id : '')
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.$router.push('/instructions/list')
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.instruction'),
                                text: this.$t('inclusions.error.messages.country_delete')
                            })

                            this.loading = false
                        }
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.instruction'),
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
                            title: this.$t('global.modules.instruction'),
                            text: 'Debe seleccionar al menos un email'
                        })
                    } else {
                        this.$refs['my-modal-notify'].hide()
                        this.submit()
                    }
                }
            },
            addEmail (newTag) {
                const tag = {
                    name: newTag,
                    email: newTag.substring(0, 2) + Math.floor((Math.random() * 10000000))
                }
                this.emailsService.push(tag)
            },
            hideModal () {
                this.optionSelect = false
                this.$refs['my-modal-notify'].hide()
            },
        }
    }
</script>

<style lang="stylus">
    #container_country
        margin-bottom 15px
</style>
<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>
