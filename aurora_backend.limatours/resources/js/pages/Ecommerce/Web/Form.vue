<template>
    <div>
        <div v-if="form.client_id != ''">
            <div class="vld-parent">
                <loading :active.sync="loading" :can-cancel="false" color="#BD0D12"></loading>
                <div class="row mt-3">
                    <div class="col-sm-12">
                        <div class="form-group row">
                            <div class="col-md-12 mb-2">
                                <div class="divider-text mb-2">
                                    <strong>Datos Generales</strong>
                                </div>
                                <hr>
                            </div>
                            <div class="col-sm-3">
                                <label for="email_contac">Correo (venta)</label>
                                <div class="col-sm-12 p-0">
                                    <input :class="{'form-control':true }"
                                           data-vv-as="email_contac"
                                           data-vv-name="email_contac"
                                           id="email_contac"
                                           name="email_contac"
                                           type="text"
                                           v-model="form.email"
                                           v-validate="'email'">
                                    <span class="invalid-feedback" v-show="errors.has('email_contac')">
                                            <span>{{ errors.first('email_contac') }}</span>
                                        </span>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <label for="email_contac">Correo (Preguntas)</label>
                                <div class="col-sm-12 p-0">
                                    <input :class="{'form-control':true }"
                                           data-vv-as="email_questions"
                                           data-vv-name="email_questions"
                                           id="email_questions"
                                           name="email_questions"
                                           type="text"
                                           v-model="form.email_questions"
                                           v-validate="'email'">
                                    <span class="invalid-feedback" v-show="errors.has('email_questions')">
                                            <span>{{ errors.first('email_questions') }}</span>
                                        </span>
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <label for="phone">Telefono</label>
                                <div class="col-sm-12 p-0">
                                    <input :class="{'form-control':true }"
                                           data-vv-as="phone"
                                           data-vv-name="phone"
                                           id="phone"
                                           name="phone"
                                           type="text"
                                           v-model="form.phone">
                                    <span class="invalid-feedback" v-show="errors.has('phone')">
                                            <span>{{ errors.first('phone') }}</span>
                                        </span>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <label for="direction">Dirección</label>
                                <div class="col-sm-12 p-0">
                                    <input :class="{'form-control':true }"
                                           data-vv-as="direction"
                                           data-vv-name="direction"
                                           id="direction"
                                           name="direction"
                                           type="text"
                                           v-model="form.direction">
                                    <span class="invalid-feedback" v-show="errors.has('direction')">
                                            <span>{{ errors.first('direction') }}</span>
                                        </span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-12 mb-2">
                                <div class="divider-text mb-2">
                                    <strong>Redes sociales</strong>
                                </div>
                                <hr>
                            </div>
                            <div class="col-sm-3">
                                <label for="facebook">Facebook (URL)</label>
                                <div class="col-sm-12 p-0">
                                    <input :class="{'form-control':true }"
                                           data-vv-as="facebook"
                                           data-vv-name="facebook"
                                           id="facebook"
                                           name="facebook"
                                           type="text"
                                           v-model="form.facebook"
                                           v-validate="'url'">
                                    <span class="invalid-feedback" v-show="errors.has('facebook')">
                                            <span>{{ errors.first('facebook') }}</span>
                                        </span>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <label for="facebook">Instagram (URL)</label>
                                <div class="col-sm-12 p-0">
                                    <input :class="{'form-control':true }"
                                           data-vv-as="instagram"
                                           data-vv-name="instagram"
                                           id="instagram"
                                           name="instagram"
                                           type="text"
                                           v-model="form.instagram"
                                           v-validate="'url'">
                                    <span class="invalid-feedback" v-show="errors.has('instagram')">
                                            <span>{{ errors.first('instagram') }}</span>
                                        </span>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <label for="facebook">Linkedin (URL)</label>
                                <div class="col-sm-12 p-0">
                                    <input :class="{'form-control':true }"
                                           data-vv-as="linkedin"
                                           data-vv-name="linkedin"
                                           id="linkedin"
                                           name="linkedin"
                                           type="text"
                                           v-model="form.linkedin"
                                           v-validate="'url'">
                                    <span class="invalid-feedback" v-show="errors.has('linkedin')">
                                            <span>{{ errors.first('linkedin') }}</span>
                                        </span>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <label for="facebook">Twitter (URL)</label>
                                <div class="col-sm-12 p-0">
                                    <input :class="{'form-control':true }"
                                           data-vv-as="twitter"
                                           data-vv-name="twitter"
                                           id="twitter"
                                           name="twitter"
                                           type="text"
                                           v-model="form.twitter"
                                           v-validate="'url'">
                                    <span class="invalid-feedback" v-show="errors.has('twitter')">
                                            <span>{{ errors.first('twitter') }}</span>
                                        </span>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <label for="facebook">Whatsapp</label>
                                <div class="col-sm-12 p-0">
                                    <input :class="{'form-control':true }"
                                           data-vv-as="whatsapp"
                                           data-vv-name="whatsapp"
                                           id="whatsapp"
                                           name="whatsapp"
                                           type="text"
                                           v-model="form.whatsapp">
                                    <span class="invalid-feedback" v-show="errors.has('whatsapp')">
                                            <span>{{ errors.first('whatsapp') }}</span>
                                        </span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-12">
                                <button type="button" class="btn btn-success" @click="submit">Guardar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-else>
            <h2 class="text-center">
                <i class="fas fa-user-lock"></i> Su cuenta debe tener un cliente asignado
            </h2>
        </div>
    </div>
</template>

<script>
    import { API } from './../../../api'
    import VueBootstrapTypeahead from 'vue-bootstrap-typeahead'
    import { Switch as cSwitch } from '@coreui/vue'
    import vSelect from 'vue-select'
    import 'vue-select/dist/vue-select.css'
    import Loading from 'vue-loading-overlay'
    import 'vue-loading-overlay/dist/vue-loading.css'

    export default {
        components: {
            VueBootstrapTypeahead,
            cSwitch,
            Loading,
            vSelect
        },
        data: () => {
            return {
                loading: false,
                images: [],
                languages: [],
                form: {
                    client_id: '',
                    client_ecommerce_id: '',
                    direction: '',
                    email: '',
                    email_questions: '',
                    phone: '',
                    facebook: '',
                    instagram: '',
                    linkedin: '',
                    twitter: '',
                    whatsapp: '',
                },
                action: 'post',
            }
        },
        created () {
            this.form.client_id = window.localStorage.getItem('client_id')
        },
        mounted () {
            //Modificar
            API.get('/client/' + this.form.client_id + '/ecommerce')
                .then((result) => {
                    if (result.data.data.length > 0) {
                        this.action = 'put'
                        let data = result.data.data[0]
                        this.form.client_ecommerce_id = data.id
                        this.form.email = data.email
                        this.form.email_questions = data.email_questions
                        this.form.direction = data.direction
                        this.form.phone = data.phone
                        this.form.facebook = data.facebook
                        this.form.instagram = data.instagram
                        this.form.linkedin = data.linkedin
                        this.form.twitter = data.twitter
                        this.form.whatsapp = data.whatsapp
                    } else {
                        this.form.client_ecommerce_id = ''
                        this.action = 'post'
                    }
                })

        },
        methods: {
            submit () {
                this.loading = true
                API({
                    method: this.action,
                    url: 'client/ecommerce/' + (this.form.client_ecommerce_id !== '' ? this.form.client_ecommerce_id : ''),
                    data: this.form
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.$notify({
                                group: 'main',
                                type: 'success',
                                title: 'Ecommerce',
                                text: this.$t('global.success.save')
                            })
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Vista',
                                text: result.data.message
                            })
                        }
                        this.loading = false
                    }).catch(() => {
                    this.loading = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('Ecommerce'),
                        text: this.$t('clientsmanageclientmarkup.error.messages.connection_error')
                    })
                })
            },
            cancel: function () {
                this.$router.push({ path: '/clients/' + this.form.client_id + '/manage_client/ecommerce/management' })
            }
        }
    }
</script>

<style lang="stylus">

</style>
