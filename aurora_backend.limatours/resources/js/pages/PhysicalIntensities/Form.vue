<template>
    <div class="row">
        <div class="col-sm-12">
            <form @submit.prevent="validateBeforeSubmit">
                <div class="b-form-group form-group">
                    <div class="form-row" id="container_country">
                        <label class="col-sm-2 col-form-label" for="physicalintensity_name">{{ $t('package.package_physical_intensity')
                            }}</label>
                        <div class="col-sm-4">
                            <input :class="{'form-control':true }"
                                   id="physicalintensity_name" name="physicalintensity_name"
                                   type="text"
                                   v-model="form.translations[currentLang].physicalintensity_name" v-validate="'required'">
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('physicalintensity_name')" />
                                <span v-show="errors.has('physicalintensity_name')">{{ errors.first('physicalintensity_name') }}</span>
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
                    <sketch-picker v-model="color" name="color"  @input="updateValueColor"/>
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
                <router-link :to="{ name: 'InclusionsList' }" v-if="!loading">
                    <button class="btn btn-danger" type="reset">
                        {{ $t('global.buttons.cancel') }}
                    </button>
                </router-link>
            </div>
        </div>
<!--        <b-modal title="Notificaciones" centered ref="my-modal-notify" size="md">-->
<!--            <div class="row" v-show="!optionSelect">-->
<!--                <div class="col-md-12">-->
<!--                    <p class="text-center">¿Desea enviar una notificación de los cambios realizados?</p>-->
<!--                </div>-->
<!--            </div>-->
<!--            <div class="form-group row" v-show="optionSelect">-->
<!--                <div class="col-sm-12">-->
<!--                    <label for="name_service">Emails</label>-->
<!--                    <multiselect :clear-on-select="false"-->
<!--                                 :close-on-select="false"-->
<!--                                 :hide-selected="true"-->
<!--                                 :multiple="true"-->
<!--                                 :options="emails"-->
<!--                                 placeholder="Emails"-->
<!--                                 :preserve-search="true"-->
<!--                                 tag-placeholder="Emails"-->
<!--                                 :taggable="true"-->
<!--                                 @tag="addEmail"-->
<!--                                 label="name"-->
<!--                                 ref="multiselect"-->
<!--                                 track-by="email"-->
<!--                                 v-model="emailsService">-->
<!--                    </multiselect>-->
<!--                </div>-->
<!--                <div class="col-md-12">-->
<!--                    <label for="name_service">Mensaje</label>-->
<!--                    <textarea :class="{'form-control':true }"-->
<!--                              id="message" name="descripction"-->
<!--                              rows="3"-->
<!--                              v-model="form_notify.message"></textarea>-->
<!--                </div>-->
<!--            </div>-->
<!--            <template slot="modal-footer">-->
<!--                <div class="row" v-show="!optionSelect">-->
<!--                    <div class="col-md-12">-->
<!--                        <button @click="optionSelection(1)" class="btn btn-success" type="reset" v-if="!loading">-->
<!--                            Si, enviar y guardar-->
<!--                        </button>-->
<!--                        <button @click="optionSelection(2)" class="btn btn-secondary" type="reset" v-if="!loading">-->
<!--                            No, solo guardar-->
<!--                        </button>-->
<!--                    </div>-->
<!--                </div>-->
<!--                <div class="row" v-show="optionSelect">-->
<!--                    <div class="col-md-12">-->
<!--                        <button @click="optionSelection(3)" class="btn btn-success" type="reset" v-if="!loading">-->
<!--                            Enviar y guardar-->
<!--                        </button>-->
<!--                        <button @click="hideModal()" class="btn btn-danger" type="reset" v-if="!loading">-->
<!--                            {{$t('global.buttons.cancel')}}-->
<!--                        </button>-->
<!--                    </div>-->
<!--                </div>-->
<!--            </template>-->
<!--        </b-modal>-->
    </div>
</template>

<script>
    import { API } from './../../api'
    import BModal from 'bootstrap-vue/es/components/modal/modal'
    import Multiselect from 'vue-multiselect'
    import { Sketch } from 'vue-color'

    export default {
        components: {
            BModal,
            Multiselect,
            'sketch-picker': Sketch
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
                color: '#EA5656',
                form_notify: {
                    message: ''
                },
                form: {
                    translations: {
                        '1': {
                            'id': '',
                            'physicalintensity_name': ''
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
                            physicalintensity_name: ''
                        }
                    })
                    if (this.$route.params.id !== undefined) {

                        API.get('/physical_intensities/' + this.$route.params.id)
                            .then((result) => {
                                this.inclusion = result.data.data
                                this.formAction = 'put'
                                this.color = '#'+this.inclusion[0].color
                                let arrayTranslations = this.inclusion[0].translations

                                arrayTranslations.forEach((translation) => {
                                    form.translations[translation.language_id] = {
                                        id: translation.id,
                                        physicalintensity_name: translation.value
                                    }
                                })
                            }).catch(() => {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.physicalintensity'),
                                text: this.$t('inclusions.error.messages.connection_error')
                            })
                        })
                    }

                    this.form = form
                }).catch(() => {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('global.modules.physicalintensity'),
                    text: this.$t('inclusions.error.messages.connection_error')
                })
            })
        },
        methods: {
            updateValueColor () {
                // console.log(this.color)
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
                            title: this.$t('global.modules.physicalintensity'),
                            text: this.$t('inclusions.error.messages.information_complete')
                        })
                        this.loading = false
                    }
                })
            },
            submit () {
                this.loading = true
                let color = (typeof this.color.hex === 'undefined') ? this.color : this.color.hex
                this.form.color = color.substr(1)
                // this.form.hasNotify = this.optionSelect
                // this.form.emails = this.emailsService
                // this.form.message = this.form_notify.message
                API({
                    method: this.formAction,
                    url: 'physical_intensities/' + (this.$route.params.id !== undefined ? this.$route.params.id : ''),
                    data: this.form
                })
                    .then((result) => {
                        if (result.data.success === false) {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.physicalintensity'),
                                text: this.$t('inclusions.error.messages.information_error')
                            })
                            this.loading = false
                        } else {
                            this.$router.push('/physical_intensities/list')
                        }
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.physicalintensity'),
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
                            this.$router.push('/physical_intensities/list')
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.physicalintensity'),
                                text: this.$t('inclusions.error.messages.country_delete')
                            })

                            this.loading = false
                        }
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.physicalintensity'),
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
                            title: this.$t('global.modules.physicalintensity'),
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
            // hideModal () {
            //     this.optionSelect = false
            //     this.$refs['my-modal-notify'].hide()
            // },
        }
    }
</script>

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>
