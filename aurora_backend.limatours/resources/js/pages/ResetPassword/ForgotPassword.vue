<template>
    <div class="app flex-row align-items-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card-group">
                        <img alt="LimaTours" src="/images/logo_big.png" width="100%"/>
                        <div class="card pt-0 px-4 pb-4">
                            <div class="card-body">
                                <h1>{{ $t('resetpassword.title') }}</h1>
                                <p class="text-muted">{{ $t('resetpassword.subTitle') }}</p>
                                <div class="input-group mb-3" role="group">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">
                                            <font-awesome-icon icon="user"/>
                                        </div>
                                    </div>
                                    <input autocomplete="off" class="form-control form-control"
                                           type="email"
                                           v-bind:placeholder="$t('resetpassword.email')"
                                           v-model="email">
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <button @click="submit" class="btn px-4 btn-primary" type="button"
                                                v-if="!loading">{{$t('resetpassword.send')}}
                                        </button>
                                        <img :src="loadingimg" v-if="loading" width="40px"/>
                                    </div>
                                    <div class="text-right col-6">
                                        <router-link :to="{ name: 'login' }">
                                            <button class="btn px-0 btn-link" type="button">
                                                {{$t('resetpassword.back')}}
                                            </button>
                                        </router-link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-6 text-right">
                    <button @click="changeLang($t('global.language.language1Code'))" class="btn btn-link"
                            type="button">
                        {{$t('global.language.language1')}}
                    </button>
                    |
                    <button @click="changeLang($t('global.language.language2Code'))" class="btn btn-link"
                            type="button">
                        {{$t('global.language.language2')}}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
<script>

  import { requestResetPassword } from '../../auth'

  const loadingimg = require('../../../images/loading.svg')

  export default {
    data () {
      return {
        loadingimg: loadingimg,
        loading: false,
        email: null,
        has_error: false,
        error: this.$i18n.t('resetpassword.error_email'),
        success: this.$i18n.t('resetpassword.success_email')
      }
    },
    methods: {
      submit: function () {
        this.loading = true
        requestResetPassword(this.email)
          .then((result) => {
            this.loading = false
            if (result.status != 500) {
              this.$notify({
                group: 'main',
                type: 'success',
                title: 'Login Error',
                text: this.success
              })
            } else {
              this.loading = false
              this.$notify({
                group: 'main',
                type: 'error',
                title: 'Login Error',
                text: this.error
              })
            }
          })
          .catch(() => {
            this.loading = false
            this.$notify({
              group: 'main',
              type: 'error',
              title: 'Login Error',
              text: this.error
            })
          })
      },
      changeLang: function (lang) {
        console.log(lang)
        window.localStorage.setItem('lang', lang)
        this.$i18n.locale = lang
        this.$emit('langChange', { lang: lang })
      }
    }
  }
</script>



