<template>
    <div class="app flex-row align-items-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card-group">
                        <img alt="LimaTours" src="/images/logo_big.png" width="100%"/>
                        <div class="card pt-0 px-4 pb-4">
                            <div class="card-body">
                                <form><h1>{{ $t('login.title') }}</h1>
                                    <p class="text-muted">{{ $t('login.subTitle') }}</p>
                                    <div class="input-group mb-3" role="group">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">
                                                <font-awesome-icon icon="user"/>
                                            </div>
                                        </div>
                                        <input autocomplete="username email" class="form-control form-control"
                                               type="text"
                                               ref="code"
                                               v-bind:placeholder="$t('login.code')"
                                               v-model="form.code"
                                               v-on:keyup.enter="gotoPassField"></div>
                                    <div class="input-group mb-4" role="group">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">
                                                <font-awesome-icon icon="lock"/>
                                            </div>
                                        </div>
                                        <input autocomplete="current-password" class="form-control form-control"
                                               type="password"
                                               ref="password"
                                               v-bind:placeholder="$t('login.password')"
                                               v-model="form.password"
                                               v-on:keyup.enter="submit"></div>
                                    <div class="row">
                                        <div class="col-6">
                                            <button @click="submit" class="btn px-4 btn-primary" type="button"
                                                    v-if="!loading">{{$t('login.login')}}
                                            </button>
                                            <img :src="loadingimg" v-if="loading" width="40px"/>
                                        </div>
                                        <div class="text-right col-6">
                                            <router-link :to="{ name: 'reset-password' }">
                                                <button class="btn px-0 btn-link" type="button">
                                                    {{$t('login.didYouForgotPassword')}}
                                                </button>
                                            </router-link>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <!--                        <div class="card text-white bg-primary py-5 d-md-down-none" style="width: 44%;">-->
                        <!--                            <div class="card-body text-center">-->
                        <!--                                <div><h2>{{$t('login.register')}}</h2>-->
                        <!--                                    <p>{{$t('login.registerDescription')}}</p>-->
                        <!--                                    <button @click="toRegister" class="btn active mt-3 btn-primary" type="button">-->
                        <!--                                        {{$t('login.registerButton')}}-->
                        <!--                                    </button>-->
                        <!--                                </div>-->
                        <!--                            </div>-->
                        <!--                        </div>-->
                    </div>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-6 text-right">
                    <button @click="changeLang($t('global.language.language1Code'))" class="btn btn-link" type="button">
                        {{$t('global.language.language1')}}
                    </button>
                    <button @click="changeLang($t('global.language.language2Code'))" class="btn btn-link" type="button">
                        {{$t('global.language.language2')}}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import axios from 'axios'
    import { login, loginCross } from '../../auth'
    import { API } from './../../api'
    import Cookies from 'js-cookie';

    axios.baseURL = window.origin

    const loadingimg = require('../../../images/loading.svg')

    export default {
        data: () => {
            return {
                loadingimg: loadingimg,
                loading: false,
                form: {
                    code: '',
                    password: ''
                },
                TokenKey: 'token_limatour',
                UserKey: 'user_id_limatour',
                token: null
            }
        },
        async mounted () {
            this.token = Cookies.get(this.TokenKey);
            if(this.token) {
                await loginCross(this.token)
                this.$emit('loggedin')
                const permissions = await this.getPernmissions()
                this.$ability.update(permissions)
                if (this.$route.query.redirect) {
                    this.$router.push(this.$route.query.redirect.toString())
                } else {
                    this.$router.push('/')
                }
            }
        },
        methods: {
            gotoPassField: function () {
                this.$refs.password.focus()
            },
            submit: async function () {
                this.loading = true

                const result = await login(this.form.code, this.form.password);
                console.log("RESULT: ", result);

                this.loading = false

                if (result.success === true) {
                    this.$emit('loggedin')
                    this.$ability.update(result.permissions)

                    if (this.$route.query.redirect) {
                        this.$router.push(this.$route.query.redirect.toString())
                    } else {
                        this.$router.push('/')
                    }
                } else {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Login Error',
                        text: result?.data?.response?.data?.message ?? 'Email o Contraseña incorrectos'
                    })
                }
            },
            toRegister: function () {
                this.$router.push('register')
            },
            changeLang: function (lang) {
                console.log(lang)
                window.localStorage.setItem('lang', lang)

                this.$i18n.locale = lang

                this.$emit('langChange', { lang: lang })
            },
            async getPernmissions(){
                const { data } = await API({ method: 'get', url: 'user/permissions'})
                return data
            }
        }
    }
</script>
