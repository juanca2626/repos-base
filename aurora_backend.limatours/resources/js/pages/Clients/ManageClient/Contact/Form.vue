<template>
    <div class="row mt-3">
        <div class="col-sm-12">
            <form @submit.prevent="validateBeforeSubmit">
                <div class="b-form-group form-group" style="display: none">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="name">Tipo Código</label>
                        <div class="col-sm-5">
                            <input :class="{'form-control':true }"
                                   id="type_code" name="type_code"
                                   type="text"
                                   autocomplete="off"
                                   v-model="form.type_code" v-validate="'required'" maxlength='1'>

                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('type_code')"/>
                                <span v-show="errors.has('type_code')">{{ errors.first('type_code') }}</span>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="name">{{ $t('global.name') }} completo</label>
                        <div class="col-sm-5">
                            <input :class="{'form-control':true }"
                                   id="name" name="name"
                                   type="text"
                                   autocomplete="off"
                                   v-model="form.name" v-validate="'required'">

                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('name')"/>
                                <span v-show="errors.has('name')">{{ errors.first('name') }}</span>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="name">Cargo</label>
                        <div class="col-sm-5">
                            <input :class="{'form-control':true }"
                                   id="surname" name="surname"
                                   type="text"
                                   autocomplete="off"
                                   v-model="form.surname" v-validate="'required'">

                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('surname')"/>
                                <span v-show="errors.has('surname')">{{ errors.first('surname') }}</span>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="email">{{
                                $t('clientsmanageclientseller.email')
                            }}</label>
                        <div class="col-sm-5">
                            <input :class="{'form-control':true }"
                                   data-vv-as="email" id="email"
                                   name="email"
                                   autocomplete="off" @change="removeAccents()"
                                   type="text" v-model="form.email" v-validate="'required|email'">
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('email')"/>
                                <span v-show="errors.has('email')">{{ errors.first('email') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="name">Teléfono</label>
                        <div class="col-sm-5">
                            <input :class="{'form-control':true }"
                                   id="phone" name="phone"
                                   type="text"
                                   autocomplete="off"
                                   v-model="form.phone" v-validate="'required'">

                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('phone')"/>
                                <span v-show="errors.has('phone')">{{ errors.first('phone') }}</span>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="name">Cumpleaños</label>
                        <div class="col-sm-5">
                            <date-picker
                                :config="datePickerToOptions"
                                id="birthday_date"
                                name="birthday_date"
                                placeholder="DD/MM/YYYY"
                                ref="datePickerTo"
                                v-model="form.birthday_date"
                            >
                            </date-picker>
                        </div>
                    </div>
                </div>

                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="name">Ver en Operaciones</label>
                        <div class="col-sm-5">
                            <c-switch :value="true" class="mx-1" color="success"
                                      v-model="form.see_in_operations"
                                      variant="pill">
                            </c-switch>
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
                    {{ $t('global.buttons.save') }}
                </button>
                <button @click="close" class="btn btn-danger" type="reset" v-if="!loading">
                    {{ $t('global.buttons.cancel') }}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import {API} from './../../../../api'
import VueBootstrapTypeahead from 'vue-bootstrap-typeahead'
import {Switch as cSwitch} from '@coreui/vue'
import 'vue2-dropzone/dist/vue2Dropzone.min.css'
import datePicker from 'vue-bootstrap-datetimepicker'
import vue2Dropzone from 'vue2-dropzone'

export default {
    props: ['form'],
    components: {
        VueBootstrapTypeahead,
        cSwitch,
        datePicker,
        vueDropzone: vue2Dropzone
    },
    data: () => {
        return {
            images: [],
            status: false,
            zip_code: '',
            id_image: '',
            url_image: '',
            clients: [],
            hotel: null,
            showError: false,
            invalidError: false,
            countError: 0,
            loading: false,
            datePickerToOptions: {
                format: 'DD/MM/YYYY',
                useCurrent: false,
                locale: localStorage.getItem('lang')
            },
        }
    },
    computed: {
        validError: function () {
            if (this.errors.has('name') == false && this.form.name != '') {
                this.invalidError = false
                this.countError += 1
                return true

            } else if (this.countError > 0) {
                this.invalidError = true
            }
            return false
        }
    },
    mounted: function () {
    },
    methods: {

        removeAccents() {
            this.form.email = this.form.email.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
        },
        close() {
            this.$emit('changeStatus', false)
        }
        ,
        validateBeforeSubmit() {
            if (this.form.period === null) {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('clientsmanageclientseller.markup'),
                    text: this.$t('clientsmanageclientseller.error.messages.select_period')
                })
                return false
            }
            this.$validator.validateAll().then((result) => {
                if (result) {
                    this.submit()
                } else {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.contacts'),
                        text: this.$t('clientsmanageclientseller.error.messages.information_complete')
                    })
                    this.loading = false
                }
            })
        },
        submit() {

            this.loading = true
            API({
                method: this.form.action,
                url: 'client_contacts' + (this.form.id !== null ? '/' + this.form.id : ''),
                data: this.form
            })
                .then((result) => {
                    console.log(result)
                    if (!(result.data.success)) {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('clientsmanageclientseller.title'),
                            text: this.$t('clientsmanageclientseller.error.messages.incorrect')
                        })

                        this.loading = false
                    } else {
                        this.loading = false

                        this.close()
                    }
                })
        }
        ,
    }
}
</script>

<style lang="stylus">

</style>
