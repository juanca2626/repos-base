<template>
    <div class="row">
        <div class="col-sm-12">
            <form @submit.prevent="validateBeforeSubmit">
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="name">{{ $t('global.name') }}</label>
                        <div class="col-sm-5">
                            <input :class="{'form-control':true, 'is-valid':validError, 'is-invalid':invalidError }"
                                   id="name" name="name"
                                   type="text"
                                   v-model="form.name" v-validate="'required'">

                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('name')"/>
                                <span v-show="errors.has('name')">{{ errors.first('name') }}</span>
                            </div>

                        </div>

                        <div class="col-sm-5">
                            <img src="/images/loading.svg" class="right" v-if="loading" width="40px"/>
                            <button @click="getSubscription()" class="btn btn-danger right" type="button"
                                    v-if="!loading"
                                    :disabled="stateSubscription">
                                <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                                <span v-if="!(stateSubscription)">Suscribirse</span>
                                <span v-if="stateSubscription">Suscrito</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="email">{{ $t('profile.email') }}</label>
                        <div class="col-sm-5">
                            <input :class="{'form-control':true, 'is-valid':validError, 'is-invalid':invalidError }"
                                   data-vv-as="email" id="email"
                                   name="email"
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
                        <label class="col-sm-2 col-form-label" for="password">{{ $t('profile.password') }}</label>
                        <div class="col-sm-5">
                            <input :class="{'form-control':true, 'is-valid':validError, 'is-invalid':invalidError }"
                                   id="password" name="password" placeholder="Password"
                                   ref="password" type="password" v-model="form.password"
                                   v-validate="'min:6|max:35'">
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('password')"/>
                                <span v-show="errors.has('password')">{{ errors.first('password') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="confirm_password">{{ $t('profile.confirm_password')
                            }}</label>
                        <div class="col-sm-5">
                            <input :class="{'form-control':true, 'is-valid':validError, 'is-invalid':invalidError }"
                                   data-vv-as="password" id="confirm_password"
                                   name="confirm_password" placeholder="Confirm password"
                                   type="password" v-model="form.confirm_password"
                                   v-validate="'confirmed:password'">

                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('confirm_password')"/>
                                <span v-show="errors.has('confirm_password')">{{ errors.first('confirm_password') }}</span>
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
                    {{$t('global.buttons.submit')}}
                </button>
                <router-link :to="'/'" v-if="!loading">
                    <button class="btn btn-danger" type="reset">
                        {{$t('global.buttons.cancel')}}
                    </button>
                </router-link>
            </div>
        </div>

        <div class="col-12" style="display: none">
            <table>
                <tr>
                    <td></td>
                    <td colspan="2">
                        <input type="text" v-model="myTitle">
                    </td>
                </tr>
                <tr>
                    <td>
                        <p>MI TOKEN:</p>
                        <section class="subscription-details js-subscription-details">
                            <code class="js-subscription-json">
                                <textarea name="" v-model="area1" cols="30" rows="10"></textarea>
                            </code>
                        </section>
                    </td>
                    <td>
                        <p>PARA:</p>
                        <section class="subscription-details js-subscription-details">
                            <code class="js-subscription-json">
                                <textarea name="" v-model="para" cols="30" rows="10"></textarea>
                            </code>
                        </section>
                    </td>
                    <td><p>MENSAJE:</p>
                        <section class="subscription-details js-subscription-details">
                            <code class="js-subscription-json">
                                <textarea name="" v-model="messageText" cols="30" rows="10"></textarea> <label>{{
                                messageEtiquet }}</label>
                            </code>
                        </section>
                    </td>
                </tr>
            </table>
            <center>
                <button @click="sendMessageTest" class="btn btn-success" :disabled="disabledBtnSend" type="button">
                    Enviar
                </button>
            </center>
        </div>

    </div>
</template>

<script>
  import { API } from './../../api'
  import VueBootstrapTypeahead from 'vue-bootstrap-typeahead'
  import { getUser } from './../../auth'

  export default {
    components: {
      VueBootstrapTypeahead
    },
    data: () => {
      return {
        area1: '',
        para: 'dFR6fGULEZI:APA91bFXps1fKLHefyxvWE2uDKeY8S9paCKZzd4y_boKne5BeM4xAe4XlIvbPmCDXb3fXMRx4TckKXINyovnCLY-3sYCkK8vyoussbHzFLV2_q_4sIY9mwOBjfUvpWSGyiJ54gW7RFML',
        myTitle: 'Test Push Notification',
        messageText: '',
        messageEtiquet: '',
        languages: [],
        user: null,
        showError: false,
        currentLang: '1',
        invalidError: false,
        disabledBtnSend: false,
        countError: 0,
        loading: false,
        stateSubscription: false,
        form: {
          id: '',
          name: '',
          email: '',
          password: '',
          confirm_password: ''
        }
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
      },
      validError: function () {
        if (this.errors.has('email') == false && this.form.email != '') {
          this.invalidError = false
          this.countError += 1
          return true

        } else if (this.countError > 0) {
          this.invalidError = true
        }
        return false
      }

    },
    mounted () {

      if ('serviceWorker' in navigator) {
        this.registerPush()
        this.verifySetPush()
      } else {
        console.log('El navegador no es compatible con serviceWorker')
      }

      this.listTokens()
      // PUSH NOTIFICATION

      getUser().then(result => {
        this.user = result.data
        this.form.id = this.user.id
        this.form.name = this.user.name
        this.form.email = this.user.email
      })

    },
    methods: {
      registerPush () {
        ////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////
        // PUSH NOTIFICATION
        var config = {
          apiKey: 'AIzaSyByFvuZUxj1dOajExlm7BAnJdqksXsqri4',
          authDomain: 'firebase-limatours.firebaseapp.com',
          databaseURL: 'https://firebase-limatours.firebaseio.com',
          projectId: 'firebase-limatours',
          storageBucket: 'firebase-limatours.appspot.com',
          messagingSenderId: '541786854376'
        }

        if (!firebase.apps.length) {

          firebase.initializeApp(config)

          // REGISTRAR EL WORKER
          navigator.serviceWorker.register(window.origin + '/sw.js').then(registro => {
            firebase.messaging().useServiceWorker(registro)
          })
            .catch(error => {
              console.log(error)
            })
          // REGISTRAR EL WORKER

          const messaging = firebase.messaging()

          // Registrar credenciales web
          messaging.usePublicVapidKey(
            'BPqRLq6SsAsJTUUNwfyyd94RvLBbIf6vZp078YDiLri4sF9OSlh7bLvqw1RnusZLnxJLH30-pk2BdLPTZCDjpMc'
          )

          //  Recibir las notificaciones cuando el usuario esta foreground
          messaging.onMessage(playload => {
            console.log('ola aqui se recibe la data')
            M.toast(
              'Ya tenemos un nuevo post: ' + playload.notification.title,
              6000
            )
          })
        }

        // PUSH NOTIFICATION
        ////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////
      },
      verifySetPush () {
        this.loading = true
        const messaging = firebase.messaging()
        messaging.getToken()
          .then(token => {
            if (token != null && token != '') {
              this.area1 = token
              this.stateSubscription = true

              const db = firebase.firestore()
              db.settings({ timestampsInSnapshots: true })
              db.collection('tokens').doc(token).set({
                token: token
              }).catch(error => {
                console.error(error)
              })
              this.loading = false
            } else {
              this.stateSubscription = false
              console.log('No registrado')
              console.log(this.stateSubscription)
              this.loading = false
            }
          })
      },
      sendMessageTest () {

        this.disabledBtnSend = true

        let myData = {
          'to': this.para,
          'notification': {
            'title': this.myTitle,
            'body': this.messageText,
            'image': window.origin + '/images/viaje.jpg',
            'icon': window.origin + '/images/favicon.png',
            'click_action': 'http://google.com'
          }
        }

        API({
          method: 'POST',
          url: 'https://fcm.googleapis.com/fcm/send',
          headers: {
            Authorization: 'key=' + 'AAAAfiUDU-g:APA91bGI-fdhLGFb9PrvB0OSVUOV2RzmFoKIPSL10df9U7u5J-K8t4hk-kPq1ZQRlGOENFBoGOHfRoELTVR--h1j4FD_O1eejbE5-TP7S9SM2TNtSbJdebrA-qgxxqFi9qfkQoT8pUPH'
          },
          contentType: 'application/json',
          dataType: 'json',
          data: myData
        }).then(response => {
          this.disabledBtnSend = false
          console.log(response.data)
          this.messageEtiquet = response.data
        }).catch((e) => {
          this.disabledBtnSend = false
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('profile.error.messages.name'),
            text: e
          })
        })

      },
      getSubscription () {
        const messaging = firebase.messaging()
        messaging.requestPermission()
          .then(() => {
            return messaging.getToken()
          }).then(token => {
          console.log('token', token)
          if (token != null) {
            this.area1 = token
            API({
              method: 'POST',
              url: 'user/notification/token',
              data: { token: token }
            })
              .then((result) => {
                if (result.data.success) {

                  this.$notify({
                    group: 'main',
                    type: 'success',
                    title: this.$t('global.modules.users'),
                    text: 'Token registrado!'
                  })

                  this.stateSubscription = true
                  console.log(this.stateSubscription)
                  this.loading = false
                }
              }).catch((e) => {
              console.log(e)
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('profile.error.messages.name'),
                text: this.$t('profile.error.messages.connection_error')
              })
            })
          }

          const db = firebase.firestore()
          db.settings({ timestampsInSnapshots: true })
          db.collection('tokens').doc(token).set({
            token: token
          }).catch(error => {
            console.log('ErroR')
            this.stateSubscription = false
            console.error(error)
          })
        })
      },
      listTokens () {
        API({
          method: 'GET',
          url: 'user/notification/token'
        })
          .then((result) => {

          }).catch((e) => {
          console.log(e)
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('profile.error.messages.name'),
            text: this.$t('profile.error.messages.connection_error')
          })
        })
      },
      validateBeforeSubmit () {
        this.$validator.validateAll().then((result) => {
          if (result) {
            this.submit()
          } else {
            this.$notify({
              group: 'main',
              type: 'error',
              title: this.$t('global.modules.users'),
              text: this.$t('profile.error.messages.information_complete')
            })
            this.loading = false
          }
        })
      },
      submit () {

        this.loading = true
        //console.log(this.form)
        API({
          method: 'PUT',
          url: 'users/' + (this.form.id),
          data: this.form
        })
          .then((result) => {
            if (result.data.success === false) {

              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.users'),
                text: this.$t('profile.error.messages.profile_incorrect')
              })

              this.loading = false
            } else {
              this.loading = false
              this.$notify({
                group: 'main',
                type: 'success',
                title: this.$t('global.modules.users'),
                text: this.$t('profile.error.messages.precess_exit')
              })
              this.$router.push('profile')
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('profile.error.messages.name'),
            text: this.$t('profile.error.messages.connection_error')
          })
        })
      }
    }
  }
</script>

<style lang="stylus">
    .right {
        float: right;
    }
</style>
