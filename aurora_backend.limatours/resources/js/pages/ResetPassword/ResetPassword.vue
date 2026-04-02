<template>
    <div class="app flex-row align-items-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card-group">
                        <img alt="LimaTours" src="/images/logo_big.png" width="100%"/>
                        <div class="card pt-0 px-4 pb-4">
                            <div class="card-body">
                                <form @submit.prevent="validateBeforeSubmit">
                                    <h1>{{ $t('resetpassword.Nueva clave') }}</h1>
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
                                    <div class="input-group mb-3" role="group">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">
                                                <font-awesome-icon icon="key"/>
                                            </div>
                                        </div>
                                        <input :class="{'form-control':true,  'is-valid':validError, 'is-invalid':invalidError }"
                                               id="password" name="password" placeholder="Password"
                                               ref="password" type="password" v-model="password"
                                               v-validate="'min:6|max:35'">
                                        <div class="bg-danger " style="border-radius: 2px; margin-left:3px;">
                                            <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                               style="margin-left: 5px;"
                                                               v-show="errors.has('password')"/>
                                            <span v-show="errors.has('password')">{{ errors.first('password') }}</span>
                                        </div>
                                    </div>
                                    <div class="input-group mb-3" role="group">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">
                                                <font-awesome-icon icon="key"/>
                                            </div>
                                        </div>
                                        <input :class="{'form-control':true, 'is-valid':validError, 'is-invalid':invalidError }"
                                               data-vv-as="password" id="confirm_password"
                                               name="confirm_password" placeholder="Confirm password"
                                               type="password" v-model="confirm_password"
                                               v-validate="'confirmed:password'">

                                        <div class="bg-danger col-10"
                                             style="margin-top: 3px; border-radius: 2px; margin-left:1px;">
                                            <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                               style="margin-left: 0px;"
                                                               v-show="errors.has('confirm_password')"/>
                                            <span v-show="errors.has('confirm_password')">{{ errors.first('confirm_password') }}</span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <button @click="submit" class="btn px-4 btn-primary" type="button"
                                                    v-if="!loading">{{$t('resetpassword.send')}}
                                            </button>
                                            <img :src="loadingimg" v-if="loading" width="40px"/>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-6 text-right">
                    <button @click="changeLang($t('global.language.language1'))" class="btn btn-link"
                            type="button">
                        {{$t('global.language.language1')}}
                    </button>
                    |
                    <button @click="changeLang($t('global.language.language2'))" class="btn btn-link"
                            type="button">
                        {{$t('global.language.language2')}}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
  import { resetPassword } from '../../auth'

  const loadingimg = require('../../../images/loading.svg')

  export default {
    data () {
      return {
        loadingimg: loadingimg,
        loading: false,
        token: null,
        email: null,
        password: null,
        confirm_password: null,
        error: this.$i18n.t('resetpassword.error_password'),
        success: this.$i18n.t('resetpassword.success_password'),
        has_error: false
      }
    },
    computed: {
      validError: function () {
        if (this.errors.has('email') == false && this.email != '') {
          this.invalidError = false
          this.countError += 1
          return true

        } else if (this.countError > 0) {
          this.invalidError = true
        }
        return false
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
              title: this.$t('global.modules.users'),
              text: this.$t('resetpassword.error.messages.information_complete')
            })
            this.loading = false
          }
        })
      },

      submit: function () {
        this.loading = true
        resetPassword(this.email, this.$route.params.token, this.password, this.confir_password)
          .then((result) => {
            this.loading = false
            if (result.status != 500) {
              this.$notify({
                group: 'main',
                type: 'success',
                title: 'Login Error',
                text: this.success
              })
              this.$router.push({ name: 'login' })
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

  // resetPassword() {
  //             this.$http.post("/api/reset/password/", {
  //                 token: this.$route.params.token,
  //                 email: this.email,
  //                 password: this.password,
  //                 password_confirmation: this.password_confirmation
  //             })
  //             .then(result => {
  //                 console.log(result.data);
  //                 this.$router.push({name: 'login'})
  //             }, error => {
  //                 console.error(error);
  //             });
  //         },
  //
</script>

