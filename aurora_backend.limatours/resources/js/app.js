import Vue from 'vue'
import VueRouter from 'vue-router'
import VueResource from 'vue-resource'
import i18n from './i18n'
import { ClientTable, ServerTable } from 'vue-tables-2'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import VueBreadcrumbs from 'vue-2-breadcrumbs'
import VeeValidate from 'vee-validate'
import validationMessages from 'vee-validate/dist/locale/en'
import validationMessagesEs from 'vee-validate/dist/locale/es'
import BootstrapVue from 'bootstrap-vue'

import { abilitiesPlugin } from '@casl/vue'
import Notifications from 'vue-notification'
import Nav from 'bootstrap-vue/es/components/nav'
import Tabs from 'bootstrap-vue/es/components/tabs'
import Card from 'bootstrap-vue/es/components/card'
import FormCheckbox from 'bootstrap-vue/es/components/form-checkbox'
import FormInput from 'bootstrap-vue/es/components/form-input'

import VueBootstrapTypeahead from 'vue-bootstrap-typeahead'
// import Charts from 'fusioncharts/fusioncharts.charts'
import VueFusionCharts from 'vue-fusioncharts'
import FusionCharts from 'fusioncharts'
// import Charts from 'fusioncharts/fusioncharts.charts'
// import Column2D from 'fusioncharts/fusioncharts.charts'
import FusionTheme from 'fusioncharts/themes/fusioncharts.theme.fusion'
// import FusionTheme from 'fusioncharts/themes/fusioncharts.theme.fusion'
import './icons'

import routes from './routes'
import App from './components/App'

import DateRangePicker from 'vue2-daterange-picker'
import 'vue2-daterange-picker/dist/vue2-daterange-picker.css'
import VueClipboard from 'vue-clipboard2'
// vue block ui
import BlockUI from 'vue-blockui'
import Charts from 'fusioncharts/fusioncharts.charts'

window.moment = require('moment')
window.moment.locale('es')

window.domain = process.env.MIX_DOMAIN
window.tokenKey = process.env.MIX_TOKEN_KEY_LIMATOUR
window.userKey = process.env.MIX_USER_KEY_LIMATOUR


Vue.component('date-range-picker', DateRangePicker)
// Charts(FusionCharts)
// FusionTheme(FusionCharts)
Vue.component('date-range-picker', DateRangePicker)
Vue.component('vue-bootstrap-typeahead', VueBootstrapTypeahead)
// Vue.component('pagination', require('laravel-vue-pagination'))
Vue.use(FormInput)
Vue.use(FormCheckbox)
Vue.use(Card)
Vue.use(Tabs)
Vue.use(Nav)
Vue.use(abilitiesPlugin)
Vue.use(VueRouter)
Vue.use(VueResource)
Vue.use(ClientTable, {}, false, 'bootstrap4')
Vue.use(ServerTable, {}, false, 'bootstrap4')
Vue.use(VueBreadcrumbs)
Vue.use(Notifications)
Vue.use(VueFusionCharts, FusionCharts, Charts, FusionTheme)
Vue.use(BootstrapVue)
Vue.use(VueClipboard)
Vue.use(VeeValidate, {
    locale: 'es',
    dictionary: {
        en: validationMessages,
        es: validationMessagesEs
    },
    classes: true,
    classNames: {
        valid: 'is-valid',
        invalid: 'is-invalid'
    },
    fieldsBagName: 'formFields'
})

Vue.component('font-awesome-icon', FontAwesomeIcon)
Vue.use(BlockUI)
Vue.component('block-page', require('./components/BlockPage.vue').default)

Vue.http.interceptors.push((request, next) => {
    request.headers.set('X-CSRF-TOKEN', window.Laravel.csrfToken)
    next()
})

window.clone = function (obj) {
    return JSON.parse(JSON.stringify(obj))
}

const router = new VueRouter({
    routes: routes
})

Vue.filter('formatDate', function (value) {
    if (value) {
        return moment(String(value)).format('DD/MM/YYYY hh:mm')
    }
})

window.isLowerCase = str => str === str.toLowerCase()

Vue.config.warnHandler = function (msg, vm, trace) {
  if (msg.includes('.native modifier for v-on')) {
    return
  }
  console.warn(msg, trace)
}

new Vue({
    router,
    i18n,
    render: h => h(App)
}).$mount('#app')
//
// // PUSH NOTIFICATION
// var config = {
//   apiKey: "AIzaSyByFvuZUxj1dOajExlm7BAnJdqksXsqri4",
//   authDomain: "firebase-limatours.firebaseapp.com",
//   databaseURL: "https://firebase-limatours.firebaseio.com",
//   projectId: "firebase-limatours",
//   storageBucket: "firebase-limatours.appspot.com",
//   messagingSenderId: "541786854376"
// }
// if (!firebase.apps.length) {
//   firebase.initializeApp(config)
//   // REGISTRAR EL WORKER
//   navigator.serviceWorker.register(window.origin + '/sw.js').then(registro => {
//     firebase.messaging().useServiceWorker(registro)
//   })
//     .catch(error => {
//       console.log(error)
//     })
//   // REGISTRAR EL WORKER
//   const messaging = firebase.messaging()
//   // Registrar credenciales web
//   messaging.usePublicVapidKey(
//     'BPqRLq6SsAsJTUUNwfyyd94RvLBbIf6vZp078YDiLri4sF9OSlh7bLvqw1RnusZLnxJLH30-pk2BdLPTZCDjpMc'
//   )
//
//   // Recibir las notificaciones cuando el usuario esta foreground
//   messaging.onMessage(playload => {
//     Materialize.toast(
//       'Ya tenemos un nuevo post: ' + playload.data.titulo,
//       6000
//     )
//   })
// }
// // PUSH NOTIFICATION
