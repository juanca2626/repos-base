<template>
    <div class="vld-parent">
        <loading :active.sync="loading" :can-cancel="false" color="#BD0D12"></loading>
        <div class="row">
            <div class="col-sm-12">
                <form @submit.prevent="validateBeforeSubmit">
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="pax_from">Pasajeros desde</label>
                                <input :class="{'form-control':true }"
                                       id="pax_from" name="pax_from"
                                       type="number"
                                       v-model="form.pax_from" v-validate="'required'">
                                <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                    <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                       style="margin-left: 5px;" v-show="errors.has('pax_from')"/>
                                    <span v-show="errors.has('pax_from')">{{ errors.first('pax_from') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="pax_to">Pasajeros hasta</label>
                                <input :class="{'form-control':true }"
                                       id="pax_to" name="pax_to"
                                       type="number"
                                       v-model="form.pax_to" v-validate="'required'">
                                <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                    <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                       style="margin-left: 5px;" v-show="errors.has('pax_to')"/>
                                    <span v-show="errors.has('pax_to')">{{ errors.first('pax_from') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="day_from">Desde el día</label>
                                <input :class="{'form-control':true }"
                                       id="day_from" name="day_from"
                                       type="number"
                                       v-model="form.day_from" v-validate="'required'">
                                <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                    <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                       style="margin-left: 5px;" v-show="errors.has('day_from')"/>
                                    <span v-show="errors.has('day_from')">{{ errors.first('pax_from') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="day_to">Hasta el día</label>
                                <input :class="{'form-control':true }"
                                       id="day_to" name="day_to"
                                       type="number"
                                       v-model="form.day_to" v-validate="'required'">
                                <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                    <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                       style="margin-left: 5px;" v-show="errors.has('day_to')"/>
                                    <span v-show="errors.has('day_to')">{{ errors.first('day_to') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="cancellation_fees">Gasto de cancelación %</label>
                                <input :class="{'form-control':true }"
                                       id="cancellation_fees" name="cancellation_fees"
                                       type="number"
                                       v-model="form.cancellation_fees" v-validate="'required'">
                                <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                    <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                       style="margin-left: 5px;"
                                                       v-show="errors.has('cancellation_fees')"/>
                                    <span v-show="errors.has('cancellation_fees')">{{ errors.first('cancellation_fees') }}</span>
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
                    <router-link :to="{ name: 'PackageCancellationPoliciesList' }" v-if="!loading">
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
    import Loading from 'vue-loading-overlay'
    import 'vue-loading-overlay/dist/vue-loading.css'

    export default {
        components: {
            BModal,
            Loading
        },
        data: () => {
            return {
                languages: [],
                form: {
                    pax_from: 1,
                    pax_to: 1,
                    day_from: 1,
                    day_to: 1,
                    cancellation_fees: 1,
                },
                loading: false,
                formAction: 'post',

            }
        },
        computed: {},
        mounted: function () {
            if (this.$route.params.id !== undefined) {
                this.loading = true
                this.formAction = 'put'
                API.get('package/cancellation_policies/' + this.$route.params.id)
                    .then((result) => {
                        this.loading = false
                        let form = result.data.data
                        this.form.pax_from = form.pax_from
                        this.form.pax_to = form.pax_to
                        this.form.day_from = form.day_from
                        this.form.day_to = form.day_to
                        this.form.cancellation_fees = form.cancellation_fees
                    }).catch(() => {
                    this.loading = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('inclusions.error.messages.name'),
                        text: this.$t('inclusions.error.messages.connection_error')
                    })
                })
            }
        },
        methods: {
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
            submit () {
                this.loading = true
                API({
                    method: this.formAction,
                    url: 'package/cancellation_policies/' + (this.$route.params.id !== undefined ? this.$route.params.id : ''),
                    data: this.form
                }).then((result) => {
                    this.loading = false
                    if (result.data.success === false) {
                        if (result.data.error === 'DUPLICATE_PAX') {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.photos'),
                                text: 'El rango de pasajeros ingresado ya existe, por favor ingrese otro.'
                            })
                        }
                    } else {
                        this.$router.push({ name: 'PackageCancellationPoliciesList' })
                    }
                }).catch(() => {
                    this.loading = false
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
