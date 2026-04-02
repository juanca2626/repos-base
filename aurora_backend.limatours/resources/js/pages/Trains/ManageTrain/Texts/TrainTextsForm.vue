<template>
    <div class="row">

        <div class="col-md-12">

            <b-tabs>
                <b-tab :key="trans.language.id" :title="trans.language.name" ref="tabtrans"
                       v-for="trans in translations">
                    <form @submit.prevent="validateBeforeSubmit( trans.language.id )">

                        <div class="form-row">
                            <label for="description">{{ $t('packagesmanagepackagetexts.description') }}</label>
                            <div class="col-sm-12 p-0">
                                <textarea :class="{'form-control':true }"
                                          id="description" name="description"
                                          rows="5"
                                          v-model="form.translations[trans.language.id].value"></textarea>
                            </div>
                        </div>

                        <div class="form-row" style="margin-top: 20px">
                            <div slot="footer">
                                <img src="/images/loading.svg" v-if="loading" width="40px"/>
                                <button @click="validateBeforeSubmit(trans.language.id)" class="btn btn-success"
                                        type="submit" v-if="!loading">
                                    <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                                    {{ $t('global.buttons.submit') }}
                                </button>
                                <button @click="cancelForm" class="btn btn-danger" type="reset" v-if="!loading">
                                    {{$t('global.buttons.cancel')}}
                                </button>
                            </div>
                        </div>

                    </form>
                </b-tab>
            </b-tabs>

        </div>

    </div>
</template>

<script>
    import { API } from '../../../../api'
    import { Switch as cSwitch } from '@coreui/vue'
    import BTab from 'bootstrap-vue/es/components/tabs/tab'
    import BInputNumber from 'bootstrap-vue/es/components/form-input/form-input'
    import BTabs from 'bootstrap-vue/es/components/tabs/tabs'
    import VueBootstrapTypeahead from 'vue-bootstrap-typeahead'
    import BFormCheckbox from 'bootstrap-vue/es/components/form-checkbox/form-checkbox'
    import BFormCheckboxGroup from 'bootstrap-vue/es/components/form-checkbox/form-checkbox-group'

    export default {
        components: {
            BTabs,
            BTab,
            cSwitch,
            VueBootstrapTypeahead,
            BFormCheckbox,
            BFormCheckboxGroup,
            BInputNumber
        },
        data: () => {
            return {
                loading: false,
                translations: [],
                form: {
                    train_id: '',
                    translations: []
                },
            }
        },
        mounted: function () {

            this.$i18n.locale = localStorage.getItem('lang')

            API.get('/train_template/' + this.form.train_id + '/translations').then((result) => {
                this.translations = result.data.data
                let _data = result.data.data

                _data.forEach((value) => {
                    this.form.translations[value.language_id] = value
                    this.form.translations[value.language_id].value = (value.value) ? value.value.trim() : ''
                })

            }).catch(() => {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('packagesmanagepackagetexts.error.messages.name'),
                    text: this.$t('packagesmanagepackagetexts.error.messages.connection_error')
                })
            })

            localStorage.setItem('trainnamemanage', this.form.train_id)

        },
        created: function () {
            this.form.train_id = this.$route.params.train_id
        },
        methods: {
            cancelForm () {
                this.$router.push({ path: '/trains' })
            },
            validateBeforeSubmit: function (lang_id) {
                this.$validator.validateAll().then((result) => {
                    if (result) {
                        this.form.train_id = this.$route.params.train_id
                        this.submit(lang_id)

                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Trenes',
                            text: this.$t('packagesmanagepackagetexts.error.messages.information_complete')
                        })

                        this.loading = false
                    }
                })
            },
            submit: function (lang_id) {
                API({
                    method: 'put',
                    url: 'train_template/' + this.form.train_id + '/translations/' + lang_id,
                    data: this.form.translations[lang_id]
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.$notify({
                                group: 'main',
                                type: 'success',
                                title: this.$t('global.modules.package_texts'),
                                text: this.$t('packagesmanagepackagetexts.messages.successfully')
                            })
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.package_texts'),
                                text: this.$t('packagesmanagepackagetexts.messages.information_error')
                            })

                            this.loading = false
                        }
                    })
            }
        }
    }
</script>

<style lang="stylus">
</style>

