<template>
    <div class="row">
        <div class="col-sm-12">
            <form @submit.prevent="validateBeforeSubmit">
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-1 col-form-label" for="code">Nombre</label>
                        <div class="col-sm-3">
                            <input :class="{'form-control':true }"
                                   id="name" name="name"
                                   type="text"
                                   v-model="form.name" v-validate="'required'">
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('name')"/>
                                <span v-show="errors.has('code')">{{ errors.first('name') }}</span>
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
                <router-link :to="{ name: 'ServiceTypesList' }" v-if="!loading">
                    <button class="btn btn-danger" type="reset">
                        {{ $t('global.buttons.cancel') }}
                    </button>
                </router-link>
            </div>
        </div>
    </div>
</template>

<script>
import {API} from './../../api'

export default {
    data: () => {
        return {
            languages: [],
            serviceTypes: null,
            showError: false,
            currentLang: '1',
            invalidError: false,
            countError: 0,
            loading: false,
            formAction: 'post',
            form: {
                name: ''
            }
        }
    },
    computed: {},
    mounted: function () {
        if (this.$route.params.id !== undefined) {
            API.get('/positions/' + this.$route.params.id)
                .then((result) => {
                    this.positions = result.data.data
                    this.formAction = 'put'
                    this.form.name = this.positions.name
                }).catch(() => {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: 'Cargo',
                    text: this.$t('servicetypes.error.messages.connection_error')
                })
            })
        }

    },
    methods: {
        validateBeforeSubmit() {
            this.$validator.validateAll().then((result) => {
                if (result) {
                    this.submit()
                } else {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: "Cargo",
                        text: this.$t('servicetypes.error.messages.information_complete')
                    })
                    this.loading = false
                }
            })
        },
        submit() {
            this.loading = true
            API({
                method: this.formAction,
                url: 'positions/' + (this.$route.params.id !== undefined ? this.$route.params.id : ''),
                data: this.form
            }).then((result) => {
                    if (result.data.success === false) {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: "Cargo",
                            text: this.$t('servicetypes.error.messages.information_error')
                        })
                        this.loading = false
                    } else {
                        this.$router.push('/positions/list')
                    }
                }).catch(() => {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('servicetypes.error.messages.name'),
                    text: this.$t('servicetypes.error.messages.connection_error')
                })
            })
        },
        remove() {
            this.loading = true
            API({
                method: 'DELETE',
                url: 'positions/' + (this.$route.params.id !== undefined ? this.$route.params.id : '')
            })
                .then((result) => {
                    if (result.data.success === true) {
                        this.$router.push('/positions/list')
                    } else {
                        if (result.data.used === true) {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: "Cargo",
                                text: "El cargo no puede ser eliminado, por que está siendo utilizado en registros"
                            })
                        }
                        this.loading = false
                    }
                }).catch(() => {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('servicetypes.error.messages.name'),
                    text: this.$t('servicetypes.error.messages.connection_error')
                })
            })
        }
    }
}
</script>

<style lang="stylus">
#container_country
    margin-bottom  15px
</style>
