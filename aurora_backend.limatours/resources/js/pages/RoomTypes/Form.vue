<template>
    <div class="row">
        <div class="col-sm-12">
            <form @submit.prevent="validateBeforeSubmit">
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="roomtype_name">{{ $t('roomtypes.roomtype_name')
                            }}</label>
                        <div class="col-sm-5">
                            <input :class="{'form-control':true, 'is-valid':validError, 'is-invalid':invalidError }"
                                   id="roomtype_name" name="roomtype_name"
                                   type="text"
                                   v-model="form.translations[currentLang].roomtype_name" v-validate="'required'">
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('roomtype_name')"/>
                                <span v-show="errors.has('roomtype_name')">{{ errors.first('roomtype_name') }}</span>
                            </div>
                        </div>
                        <select class="col-sm-1 form-control" id="lang" required size="0" v-model="currentLang">
                            <option v-bind:value="language.id" v-for="language in languages">
                                {{ language.iso }}
                            </option>
                        </select>
                    </div>
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="occupation">
                            Ocupación
                        </label>
                        <div class="col-sm-1">
                            <select class="form-control" id="occupation" required size="0"
                                    v-model="form.occupation">
                                <option v-bind:value="occupation" v-for="occupation in occupations">
                                    {{ occupation }}
                                </option>
                            </select>
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
                <router-link :to="{ name: 'RoomTypesList' }" v-if="!loading">
                    <button class="btn btn-danger" type="reset">
                        {{$t('global.buttons.cancel')}}
                    </button>
                </router-link>
            </div>
        </div>
    </div>
</template>

<script>
    import { API } from './../../api'

    export default {
        data: () => {
            return {
                languages: [],
                roomtype: null,
                showError: false,
                currentLang: '1',
                invalidError: false,
                countError: 0,
                loading: false,
                formAction: 'post',
                form: {
                    translations: {
                        '1': {
                            'id': '',
                            'roomtype_name': ''
                        }
                    },
                    occupation: 1
                },
                occupations: [1, 2, 3, 4, 5, 6],

            }
        },
        computed: {
            validError: function () {
                if (this.errors.has('roomtype_name') == false && this.form.translations[1].roomtype_name != '') {
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
            // this.cleanFields()
            API.get('/languages/')
                .then((result) => {
                    this.languages = result.data.data
                    this.currentLang = result.data.data[0].id

                    let form = {
                        translations: {}
                    }

                    let languages = this.languages

                    languages.forEach((value) => {
                        form.translations[value.id] = {
                            id: '',
                            roomtype_name: ''
                        }
                    })
                    if (this.$route.params.id !== undefined) {

                        API.get('/room_types/' + this.$route.params.id)
                            .then((result) => {
                                this.roomtype = result.data.data
                                this.formAction = 'put'

                                let arrayTranslations = this.roomtype[0].translations
                                this.form.occupation = this.roomtype[0].occupation

                                arrayTranslations.forEach((translation) => {
                                    form.translations[translation.language_id] = {
                                        id: translation.id,
                                        roomtype_name: translation.value
                                    }
                                })
                            }).catch(() => {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('roomtypes.error.messages.name'),
                                text: this.$t('roomtypes.error.messages.connection_error')
                            })
                        })
                    }

                    this.form = form
                }).catch(() => {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('roomtypes.error.messages.name'),
                    text: this.$t('roomtypes.error.messages.connection_error')
                })
            })
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
                            title: this.$t('global.modules.room_types'),
                            text: this.$t('roomtypes.error.messages.information_complete')
                        })

                        this.loading = false
                    }
                })
            },
            submit () {

                this.loading = true

                API({
                    method: this.formAction,
                    url: 'room_types/' + (this.$route.params.id !== undefined ? this.$route.params.id : ''),
                    data: this.form
                })
                    .then((result) => {
                        if (result.data.success === false) {

                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.room_types'),
                                text: this.$t('roomtypes.error.messages.roomtype_incorrect')
                            })

                            this.loading = false
                        } else {
                            this.$router.push('/room_types/list')
                        }
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('roomtypes.error.messages.name'),
                        text: this.$t('roomtypes.error.messages.connection_error')
                    })
                })
            }
        }
    }
</script>

<style lang="stylus">
</style>


