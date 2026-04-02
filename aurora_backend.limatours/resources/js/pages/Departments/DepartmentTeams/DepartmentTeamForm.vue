<template>
    <div class="row">
        <div class="col-sm-12">
            <form @submit.prevent="validateBeforeSubmit">
                <div class="b-form-group form-group">
                    <div class="form-row" id="container_country">
                        <label class="col-sm-2 col-form-label" for="servicesubcategory_name">Nombre del equipo:</label>
                        <div class="col-sm-4">
                            <input :class="{'form-control':true }"
                                   id="name" name="name"
                                   type="text"
                                   v-model="form.name" v-validate="'required'">
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('name')"/>
                                <span v-show="errors.has('name')">{{ errors.first('name') }}</span>
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
                <router-link :to="{ name: 'DepartmentTeamList' }" v-if="!loading">
                    <button class="btn btn-danger" type="reset">
                        {{ $t('global.buttons.cancel') }}
                    </button>
                </router-link>
            </div>
        </div>
    </div>
</template>

<script>
import {API} from './../../../api'

export default {
    data: () => {
        return {
            languages: [],
            service_categories: null,
            showError: false,
            currentLang: '1',
            invalidError: false,
            countError: 0,
            loading: false,
            formAction: 'post',
            form: {
                department_id: '',
                name: '',
            }
        }
    },
    mounted: function () {
        this.form.department_id = this.$route.params.department_id
        if (this.$route.params.id !== undefined) {
            API.get('/department_team/' + this.$route.params.id)
                .then((result) => {
                    this.team = result.data.data
                    this.formAction = 'put'
                    this.form.department_id = this.team.department_id
                    this.form.name = this.team.name
                }).catch(() => {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: "Equipo",
                    text: this.$t('servicecategories.error.messages.connection_error')
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
                        title: "Equipo",
                        text: this.$t('servicecategories.error.messages.information_complete')
                    })
                    this.loading = false
                }
            })
        },
        submit() {
            this.loading = true
            API({
                method: this.formAction,
                url: 'department_team/' + (this.$route.params.id !== undefined ? this.$route.params.id : ''),
                data: this.form
            })
                .then((result) => {
                    if (result.data.success === false) {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: "Equipo",
                            text: this.$t('servicecategories.error.messages.information_error')
                        })
                        this.loading = false
                    } else {
                        this.$router.push({name: 'DepartmentTeamList'})
                    }
                }).catch(() => {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: "Equipo",
                    text: this.$t('servicecategories.error.messages.connection_error')
                })
            })
        },
    }
}
</script>

