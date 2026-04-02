<template>
    <div class="row">
        <div class="col-sm-12">
            <form @submit.prevent="validateBeforeSubmit">
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <div class="col-2">
                            <label class="col-sm-12 col-form-label">Seleccione uno o varios usuarios</label>
                        </div>
                        <div class="col-10">
                            <multiselect :clear-on-select="false"
                                         :close-on-select="false"
                                         :hide-selected="true"
                                         :searchable="true"
                                         :multiple="true"
                                         :options="executives"
                                         placeholder="Usuarios"
                                         :preserve-search="false"
                                         tag-placeholder="Usuarios"
                                         :taggable="false"
                                         @tag="addExecutive"
                                         label="name"
                                         ref="multiselect"
                                         track-by="code"
                                         data-vv-as="usuario"
                                         data-vv-name="usuario"
                                         name="usuario"
                                         v-model="form.executivesSelected"
                                         v-validate="'required'">
                            </multiselect>
                            <span class="invalid-feedback-select" v-show="errors.has('usuario')">
                                <span>Campo requerido</span>
                            </span>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-2">
                            <label class="col-sm-12 col-form-label">Seleccione uno o varios paquetes</label>
                        </div>
                        <div class="col-10">
                            <multiselect :clear-on-select="false"
                                         :close-on-select="false"
                                         :hide-selected="true"
                                         :searchable="true"
                                         :multiple="true"
                                         :options="packages"
                                         placeholder="Paquetes"
                                         :preserve-search="false"
                                         tag-placeholder="Paquetes"
                                         :taggable="false"
                                         @tag="addPackage"
                                         label="name"
                                         ref="multiselect"
                                         track-by="code"
                                         data-vv-as="package"
                                         data-vv-name="package"
                                         name="package"
                                         v-model="form.packagesSelected"
                                         v-validate="'required'">
                            </multiselect>
                            <span class="invalid-feedback-select" v-show="errors.has('package')">
                                <span>Campo requerido</span>
                            </span>
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
                    {{$t('global.buttons.submit')}}
                </button>
                <button @click="CancelForm" class="btn btn-danger" type="reset" v-if="!loading">
                    {{$t('global.buttons.cancel')}}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
    import { API } from './../../../api'
    import BFormGroup from 'bootstrap-vue/es/components/form-group/form-group'
    import BFormRadio from 'bootstrap-vue/es/components/form-radio/form-radio'
    import BFormRadioGroup from 'bootstrap-vue/es/components/form-radio/form-radio-group'
    import Multiselect from 'vue-multiselect'
    import vSelect from 'vue-select'
    import 'vue-select/dist/vue-select.css'
    import { Switch as cSwitch } from '@coreui/vue'

    export default {
        components: {
            BFormGroup,
            BFormRadio,
            BFormRadioGroup,
            vSelect,
            cSwitch,
            Multiselect
        },
        data: () => {
            return {
                loading: false,
                isLoading: false,
                formAction: 'post',
                executives: [],
                packages: [],
                searchUserType: '',
                target: '',
                form: {
                    executivesSelected: [],
                    packagesSelected: []
                }
            }
        },
        mounted () {
            this.fetchDataUser()
            this.fetchDataPackage()
        },
        methods: {
            fetchDataUser: function () {
                API.get('users?search=' + this.target + '&typeUser=' + this.searchUserType)
                    .then((result) => {
                        if (result.data.success === true) {
                            let arrayAuxExperience = result.data.data
                            let argDataE = []
                            let e = 0
                            arrayAuxExperience.forEach((user) => {
                                argDataE[e] = {
                                    code: user.id,
                                    name: user.code + ' - ' + user.name
                                }
                                e++
                            })
                            this.executives = argDataE
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Fetch Error',
                                text: result.data.message
                            })
                        }
                    })
            },
            fetchDataPackage: function () {
                API.get('packages?token=' + window.localStorage.getItem('access_token') + '&lang=' +
                    localStorage.getItem('lang') + '&filterBy=2' +'&limit=3000' +
                    '&filter_exclusive=true&filter_generals=true')
                    .then((result) => {
                        if (result.data.success === true) {
                            let arrayAuxExperience = result.data.data
                            let argDataE = []
                            let e = 0
                            arrayAuxExperience.forEach((item) => {
                                argDataE[e] = {
                                    code: item.id,
                                    name: item.id + ' - ' + item.translations[0].name
                                }
                                e++
                            })
                            this.packages = argDataE
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Fetch Error',
                                text: result.data.message
                            })
                        }
                    })
            },
            addExecutive (newTag) {
                const tag = {
                    name: newTag,
                    code: newTag.substring(0, 2) + Math.floor((Math.random() * 10000000))
                }
                this.form.executivesSelected.push(tag)
            },
            addPackage (newTag) {
                const tag = {
                    name: newTag,
                    code: newTag.substring(0, 2) + Math.floor((Math.random() * 10000000))
                }
                this.form.packagesSelected.push(tag)
            },
            CancelForm () {
                this.id_image = ''
                this.$router.push({ path: '/packages/permissions' })
            },
            validateBeforeSubmit () {
                this.$validator.validateAll().then((result) => {
                    if (result) {
                        this.submit()
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.packages'),
                            text: this.$t('packages.error.messages.information_complete')
                        })
                        this.loading = false
                    }
                })
            },
            submit () {

                this.loading = true

                API({
                    method: this.formAction,
                    url: 'package/permissions/',
                    data: {
                        'users' : this.form.executivesSelected,
                        'packages' : this.form.packagesSelected
                    }
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.$router.push('/packages/permissions')
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Paquetes',
                                text: result.data.message
                            })

                            this.loading = false
                        }
                    })
            },
            asyncFindExecutives: function (query) {
                console.log(query)
            }
        },
        filters: {
            capitalize: function (value) {
                if (!value) return ''
                value = value.toString().toLowerCase()
                return value.charAt(0).toUpperCase() + value.slice(1)
            }
        }
    }
</script>

<style lang="stylus">

</style>
