@extends('layouts.app')
@section('content')
    <section class="contact">
        <div class="hero__primary">
            <div class="container">
                <p class="text-left">¡@{{ translations.label.dont_hesitate_to_contact_us }}!</p>
            </div>
        </div>
    </section>
    <section class="contact pb-5">
        <div class="container">
            <validation-observer v-slot="{ handleSubmit }">
                <div class="vld-parent">
                    <loading :active.sync="loading" :is-full-page="false" :can-cancel="false" color="#EB5757"></loading>
                    <form class="pb-5" @submit.prevent="handleSubmit(onSubmit)">
                        <h3 class="sub-title text-left">@{{ translations.label.your_information }}</h3>
                        <div>
                            <div class="d-flex justify-content-between align-items-center mt-5">
                                <label for=""
                                       style="width: 400px;text-align: initial;">@{{ translations.label.name }}</label>
                                <validation-provider rules="required" v-slot="{ errors }" slim>
                                    <div style="width: 100%">
                                        <input type="text" :placeholder="translations.label.name" name="name" v-model="form.user_name"
                                               :class="{ 'border-red': errors[0]}">
                                    </div>
                                </validation-provider>
                                <label for=""
                                       style="width: 400px;text-align: initial; margin-left: 2rem;">@{{ translations.label.last_name }}</label>
                                <validation-provider rules="required" v-slot="{ errors }" slim>
                                    <div style="width: 100%">
                                        <input type="text" :placeholder="translations.label.last_name" name="surname"
                                               v-model="form.user_surname" :class="{ 'border-red': errors[0]}">
                                    </div>
                                </validation-provider>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-5">
                                <label for=""
                                       style="width: 400px;text-align: initial;">@{{ translations.label.company }}</label>
                                <validation-provider rules="required" v-slot="{ errors }" slim>
                                    <div style="width: 100%">
                                        <input type="text" :placeholder="translations.label.company" name="company"
                                               v-model="form.user_company" :class="{ 'border-red': errors[0]}">
                                    </div>
                                </validation-provider>
                                <label for="" style="width: 400px;text-align: initial; margin-left: 2rem;">@{{ translations.label.email }}</label>
                                <validation-provider rules="required|email" v-slot="{ errors }" slim>
                                    <div style="width: 100%">
                                        <input type="email" id="input-email" :placeholder="translations.label.email"
                                               name="user_email"
                                               v-model="form.user_email" :class="{ 'border-red': errors[0]}">
                                    </div>
                                </validation-provider>
                            </div>
                            <sub>Opcional</sub>
                            <div class="d-flex justify-content-start align-items-center mt-5" style="width: 555px;">
                                <label for=""
                                       style="width: 195px;text-align: initial;">@{{ translations.label.phone_number }}</label>
                                <input type="number" :placeholder="translations.label.phone_number" v-model="form.user_phone">

                            </div>
                        </div>
                        <h3 class="sub-title text-left">@{{ translations.label.your_message }}</h3>
                        <div class="">
                            <div class="d-flex justify-content-between align-items-center mt-5">
                                <label for="" style="width: 400px;text-align: initial;">@{{ translations.label.for }}</label>
                                <validation-provider rules="required|email" v-slot="{ errors }" slim>
                                    <div style="width: 100%">

                                        <select :placeholder="translations.label.for" :class="{ 'border-red': errors[0]}"
                                                v-model="form.executive_email">
                                            <option :value="executive.email" v-for="executive in executives">@{{
                                                executive.email }}
                                            </option>
                                        </select>
                                    </div>
                                </validation-provider>
                                <label for=""
                                       style="width: 400px;text-align: initial; margin-left: 2rem;">@{{ translations.label.subject }}</label>
                                <validation-provider rules="required" v-slot="{ errors }" slim>
                                    <div style="width: 100%">
                                        <input type="text" :placeholder="translations.label.subject" v-model="form.subject"
                                               :class="{ 'border-red': errors[0]}">
                                    </div>
                                </validation-provider>
                            </div>
                            <div class="d-flex justify-content-start align-items-center mt-5">
                                <label for=""
                                       style="width: 160px;text-align: initial;">@{{ translations.label.message }}</label>
                                <validation-provider rules="required" v-slot="{ errors }" slim>
                                    <div style="width: 100%">
                            <textarea name="message" type="text" id="input-message" :placeholder="translations.label.message"
                                      v-model="form.message" :class="{ 'border-red': errors[0]}"></textarea>
                                    </div>
                                </validation-provider>
                            </div>

                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="d-flex justify-content-start align-items-center mt-5">
                                    <label class="checkbox-ui" @click="form.privacy_policy=!form.privacy_policy"
                                           id="check_privacy_policy">
                                        <i :class="{'fa fa-check-square' : form.privacy_policy, 'far fa-square':!form.privacy_policy}"></i>
                                        @{{ translations.label.i_have_read_and_accept_the }} <a href="#">@{{ translations.label.privacy_policy }}.</a>
                                    </label>
                                </div>
                                <div class="d-flex justify-content-start align-items-center mt-5">
                                    <label class="checkbox-ui" @click="form.data_treatment=!form.data_treatment"
                                           id="check_data_treatment">
                                        <i :class="{'fa fa-check-square' : form.data_treatment, 'far fa-square':!form.data_treatment}"></i>
                                        @{{ translations.label.i_authorize_the_processing_of_my_data }}
                                    </label>
                                </div>

                            </div>
                            <button type="submit" class="btn-primary">Enviar</button>
                        </div>

                    </form>
                </div>
            </validation-observer>
        </div>
    </section>
@endsection
@section('css')
@endsection
@section('js')
    <script>
        new Vue({
            el: '#app',
            data: {
                form: {
                    user_name: '',
                    user_surname: '',
                    user_company: '',
                    user_email: '',
                    user_phone: '',
                    executive_email: '',
                    subject: '',
                    message: '',
                    privacy_policy: false,
                    data_treatment: false,
                },
                translations: {
                    label: {},
                    validations: {},
                    messages: {}
                },
                executives: [],
                loading: false,
                client_id: '',
            },
            created: function () {
                let fullname = localStorage.getItem('name').split(' ')
                if (fullname.length > 0) {
                    this.form.user_name = fullname[0]
                    this.form.user_surname = fullname[1]
                }
                this.form.user_company = localStorage.getItem('client_name')
                this.form.user_email = localStorage.getItem('user_email')
                this.client_id = localStorage.getItem('client_id')
            },
            mounted: function () {
                this.setTranslations()
                this.getExecutives()
            },
            computed: {},
            methods: {
	            goBiosafetyProtocols () {
		            window.location.href = '/biosafety-protocols'
	            },
                setTranslations () {
                    axios.get(baseURL + 'translation/' + localStorage.getItem('lang') + '/slug/contact_us').then((data) => {
                        this.translations = data.data
                    })
                },
                onSubmit () {
                    if (!this.form.privacy_policy) {
                        this.$toast.warning('Debe aceptar las política de privacidad', {
                            position: 'top-right'
                        })
                        return
                    }
                    if (!this.form.data_treatment) {
                        this.$toast.warning('Debe autorizar el tratamiento de mis datos', {
                            position: 'top-right'
                        })
                        return
                    }
                    this.loading = true
                    axios.post(
                        baseExternalURL + 'api/aurora/contact_us',
                        this.form,
                    ).then((result) => {
                        this.loading = false
                        if (result.data.success) {
                            this.resetForm()
                            this.$toast.success('Su mensaje fue enviado', {
                                position: 'top-right'
                            })
                        } else {
                            this.$toast.error('Algo paso, por favor vuelva a intentarlo', {
                                position: 'top-right'
                            })
                        }
                    }).catch((e) => {
                        this.loading = false
                        console.log(e)
                    })
                },
                getExecutives () {
                    axios.get(
                        baseExternalURL + 'api/aurora/executives/' + this.client_id
                    ).then((result) => {
                        this.executives = result.data.data
                    }).catch((e) => {
                        console.log(e)
                    })
                },
                resetForm () {
                    this.form.user_company = ''
                    this.form.user_phone = ''
                    this.form.executive_email = ''
                    this.form.subject = ''
                    this.form.message = ''
                    this.form.privacy_policy = false
                    this.form.data_treatment = false
                }
            }
        })
    </script>
@endsection
