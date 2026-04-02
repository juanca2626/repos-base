import VueResource from 'vue-resource'

/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

window.Vue = require('vue')

import VCalendar from 'v-calendar';
import _ from 'lodash'
window._ = _
Vue.config.productionTip = false

// Use v-calendar & v-date-picker components
Vue.use(VCalendar, {
    // Use <vc-calendar /> instead of <v-calendar />
    // ...other defaults
});

Vue.use(VueResource)

//Moment JS
window.moment = require('moment')
window.moment.locale('es')

//Vue Select
import vSelect from 'vue-select'
//Vue DatePicker
import datePicker from 'vue-bootstrap-datetimepicker'
import 'pc-bootstrap4-datetimepicker/build/css/bootstrap-datetimepicker.css'

Vue.use(datePicker)

import Multiselect from 'vue-multiselect'

Vue.use(Multiselect)

import { Fancybox, Carousel, Panzoom } from "@fancyapps/ui"
import "@fancyapps/ui/dist/fancybox.css"
Vue.component(Fancybox)
Vue.component(Carousel)
Vue.component(Panzoom)


//Vue Range Date Picker
import DateRangePicker from 'vue2-daterange-picker'
import 'vue2-daterange-picker/dist/vue2-daterange-picker.css'
//Bootstrap Vue
import BootstrapVue from 'bootstrap-vue/dist/bootstrap-vue.esm'
//Vue Range Slider
import VueSlider from 'vue-slider-component'
import 'vue-slider-component/theme/default.css'
//Vue Toast Notification
import VueToast from 'vue-toast-notification'
import 'vue-toast-notification/dist/index.css'
//Vue BlockUI
import BlockUI from 'vue-blockui'
//Vue Calendar
// import VueCal from 'vue-cal'
// import 'vue-cal/dist/i18n/es.js'
// import 'vue-cal/dist/vuecal.css'
// Vue Dialog
import 'vuejs-dialog/dist/vuejs-dialog.min.css'
import VuejsDialog from 'vuejs-dialog'
// include the default style
// Vue draggable
import draggable from 'vuedraggable'
import Donut from 'vue-css-donut-chart'
import 'vue-css-donut-chart/dist/vcdonut.css'
import Chartkick from 'vue-chartkick'
import Chart from 'chart.js'

import { BSkeleton } from 'bootstrap-vue'
import VueLoading from 'vue-loading-overlay';
// VueLoading.config.color = 'red'
import 'vue-loading-overlay/dist/vue-loading.css';

import VueNumericInput from 'vue-numeric-input';


Vue.use(Chartkick.use(Chart))
Vue.component('block-page', require('./components/BlockPage.vue').default)
//Vue table 2
import { ServerTable, ClientTable, Event } from 'vue-tables-2'

Vue.use(ClientTable, {}, false, 'bootstrap4')
Vue.use(ServerTable, {}, false, 'bootstrap4')

import { BFormCheckbox } from 'bootstrap-vue'
import { BModal } from 'bootstrap-vue'
Vue.component('b-form-checkbox', BFormCheckbox)
Vue.component('b-modal', BModal)
Vue.component('b-skeleton', BSkeleton)
Vue.use(VueLoading, { color: '#EB5757' });
Vue.component('loading', VueLoading)


// Vue.use(ClientTable, [options = {}], [useVuex = false], [theme = 'bootstrap4'], [template = 'default']);
// Vue.use(ServerTable, [options = {}], [useVuex = false], [theme = 'bootstrap4'], [template = 'default']);
import VueTimepicker from 'vue2-timepicker'
import 'vue2-timepicker/dist/VueTimepicker.css'

// NOT WORK..
import VueFusionCharts from 'vue-fusioncharts';
import FusionCharts from 'fusioncharts';
import Charts from 'fusioncharts/fusioncharts.charts';
//import the theme
import FusionTheme from 'fusioncharts/themes/fusioncharts.theme.fusion'
// register VueFusionCharts component
Vue.use(VueFusionCharts, FusionCharts, Charts, FusionTheme)
// -----------------------------------------------------------------------

import 'vue-ads-table-tree/dist/vue-ads-table-tree.css'

import { localize, ValidationProvider, ValidationObserver } from 'vee-validate/dist/vee-validate.full'

import { BCalendar } from 'bootstrap-vue'

Vue.component('b-calendar', BCalendar)

// Register it globally
// main.js or any entry file.
Vue.component('ValidationProvider', ValidationProvider)
Vue.component('ValidationObserver', ValidationObserver)

import es from 'vee-validate/dist/locale/es.json'
import en from 'vee-validate/dist/locale/en.json'
import pt from 'vee-validate/dist/locale/pt_PT.json'

localize({
    es,
    en,
    pt
})

/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap')

window.Vue = require('vue')

Vue.use(VueResource)

import JsonExcel from "vue-json-excel";
Vue.component("downloadExcel", JsonExcel);

//Moment JS
window.moment = require('moment')
window.moment.locale('es')
Vue.use(datePicker)

Vue.use(Multiselect)

Vue.use(VueToast, {
    // One of the options
    duration: 9000
})

Vue.use(BlockUI)
Vue.use(VuejsDialog)

Vue.use(draggable)

Vue.use(Donut)

Vue.use(Chartkick.use(Chart))

Vue.use(VueNumericInput)

window.baseExternalURL = process.env.MIX_BASE_EXTERNAL_URL
window.baseMasiExternalURL = process.env.MIX_MASI_EXTERNAL_URL
window.baseURL = process.env.MIX_BASE_URL
window.a3BaseUrl = process.env.MIX_A3_BASE_URL
window.a3BaseQuoteServerURL = process.env.MIX_A3_BASE_QUOTE_SERVER_URL
window.baseSocketURL = process.env.MIX_BASE_SOCKET_URL
window.baseExpressURL = process.env.MIX_BASE_EXPRESS_WS_URL
window.baseFilesOnedbURL = process.env.MIX_API_FILES_ONEDB_MS

window.csrfToken = document.querySelector('meta[name=\'csrf-token\']').getAttribute('content')

Vue.http.interceptors.push((request, next) => {
    let token = Cookies.get(window.tokenKey);
    token = (token === null || token === 'undefined') ? false : token;
    request.headers.Authorization = `Bearer ${token}`;
    request.headers.set('X-CSRF-TOKEN', window.csrfToken)
    next()
})

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

import Raphael from 'raphael/raphael'
global.Raphael = Raphael

import { DonutChart, BarChart, LineChart, AreaChart } from 'vue-morris'
Vue.component('donut-chart', DonutChart)
Vue.component('bar-chart', BarChart)
Vue.component('line-chart', LineChart)
Vue.component('area-chart', AreaChart)

// const files = require.context('./', true, /\.vue$/i);
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default));
Vue.component('v-select', vSelect)
Vue.component('date-range-picker', DateRangePicker)
Vue.component('vue-ads-table-tree', require('vue-ads-table-tree').default)
Vue.component('v-select', vSelect)
Vue.component('date-range-picker', DateRangePicker)
Vue.component('VueSlider', VueSlider)
Vue.component('menu-component', require('./components/MenuComponent.vue').default)
Vue.component('footer-component', require('./components/FooterComponent.vue').default)
Vue.component('modal-quotes', require('./components/quotes/ModalQuotes.vue').default)
Vue.component('nav-central-bookings', require('./components/central_bookings/Nav.vue').default)
Vue.component('modal-imports', require('./components/quotes/ModalImports.vue').default)
Vue.component('modal-orders', require('./components/quotes/ModalOrders.vue').default)
Vue.component('modal-passengers', require('./components/ModalPassengers.vue').default)
Vue.component('file-single-upload', require('./components/FileSingleUpload.vue').default)
Vue.component('accommodation-passengers', require('./components/AccommodationPassengers.vue').default)
Vue.component('modal-flights', require('./components/ModalFlights.vue').default)
Vue.component('loading-component', require('./components/LoadingComponent.vue').default)
Vue.component('reports-general', require('./components/ReportsGeneral.vue').default)
Vue.component('area-component', require('./components/orders/AreaComponent.vue').default)
Vue.component('customer-component', require('./components/orders/CustomerComponent.vue').default)
Vue.component('order-list', require('./components/orders/ListComponent.vue').default)
Vue.component('order-excel', require('./components/orders/ExcelComponent.vue').default)
// Modales Orders..
Vue.component('order-email-modal', require('./components/orders/modals/EmailComponent.vue').default)
Vue.component('order-tracing-modal', require('./components/orders/modals/TracingComponent.vue').default)
Vue.component('order-tags-modal', require('./components/orders/modals/TagsComponent.vue').default)
Vue.component('order-reassign-modal', require('./components/orders/modals/ReassignComponent.vue').default)
Vue.component('order-update-modal', require('./components/orders/modals/UpdateComponent.vue').default)
Vue.component('order-update-response-modal', require('./components/orders/modals/UpdateResponseComponent.vue').default)
Vue.component('order-update-obs-modal', require('./components/orders/modals/UpdateObsComponent.vue').default)
// Customer Card..
Vue.component('customer-card', require('./components/customers/CustomerCardComponent.vue').default)
// Stats
Vue.component('stats-list', require('./components/stats/ListComponent.vue').default)
Vue.component('stats-login', require('./components/stats/LoginLogsComponent.vue').default)
// Cosig
Vue.component('cosig-access-link-component', require('./components/cosig/AccessLinkComponent.vue').default)
Vue.component('cosig-files-component', require('./components/cosig/FilesComponent.vue').default)
//--------------------------------------AURORA CLIENT--------------------------------------
Vue.component('menu-client-component', require('./components/MenuClientComponent.vue').default)
Vue.component('aurora-you-can-component', require('./components/home/AuroraYouCanComponent.vue').default)
Vue.component('section-write-us-component', require('./components/home/SectionWriteUsComponent.vue').default)
//PAQUETES
Vue.component('package-recommended', require('./components/packages/PackagesRecommended.vue').default)
Vue.component('package-best-seller', require('./components/packages/PackagesBestSeller.vue').default)
//MODALES
Vue.component('modal-calender-component', require('./components/modals_clients/ModalCalenderComponent.vue').default)



//--------------------------------------END AURORA CLIENT--------------------------------------

// Reminders..
Vue.component('reminders-component', require('./components/reminders/MainComponent.vue').default)
// -------------------------------------------------------------------------------------------------------------------

// Users TOM..
Vue.component('users-tom', require('./components/users/UsersTOMComponent.vue').default)
// Modales..
Vue.component('user-add-modal', require('./components/users/modals/AddComponent.vue').default)
Vue.component('user-update-modal', require('./components/users/modals/UpdateComponent.vue').default)
Vue.component('user-vacations-modal', require('./components/users/modals/VacationsComponent.vue').default)
Vue.component('user-customers-modal', require('./components/users/modals/CustomersComponent.vue').default)
Vue.component('user-countries-modal', require('./components/users/modals/CountriesComponent.vue').default)
// -------------------------------------------------------------------------------------------------------------------
Vue.component('ranking-component', require('./components/orders/RankingComponent.vue').default)
Vue.component('response-time-component', require('./components/orders/ResponseTimeComponent.vue').default)
Vue.component('unspecified-orders-component', require('./components/orders/UnspecifiedOrdersComponent.vue').default)
Vue.component('executive-report-component', require('./components/orders/ExecutiveReportComponent.vue').default)
Vue.component('cuadre-files-component', require('./components/files/CuadreFilesComponent.vue').default)
Vue.component('pending-files-component', require('./components/files/PendingFilesComponent.vue').default)
Vue.component('pending-statements-component', require('./components/files/PendingStatementsComponent.vue').default)
Vue.component('productivity-component', require('./components/files/ProductivityComponent.vue').default)
Vue.component('profile-component', require('./components/account/ProfileComponent.vue').default)
Vue.component('password-component', require('./components/account/PasswordComponent.vue').default)

// PROTOTIPO FILE
// Vue.component('files-dashboard-component', require('./components/files/FilesDashboardComponent.vue').default)

// MASI
Vue.component('masi-logs-component', require('./components/masi/LogsComponent.vue').default)
Vue.component('masi-statistics-component', require('./components/masi/StatisticsComponent.vue').default)

Vue.component('InfiniteLoading', require('vue-infinite-loading'))
Vue.component('VueTimepicker', VueTimepicker)
Vue.use(require('vue-moment'))
Vue.use(BootstrapVue)
// Vue.component('vue-cal', VueCal)
Vue.component('draggable', draggable)

// MULTIMEDIA
Vue.component('multimedia-photos-component', require('./components/multimedia/PhotosComponent.vue').default)


import moment from 'moment'

Vue.filter('formDate', function (value) {
    if (value && value != '') {
        return moment(String(value)).format('DD/MM/YYYY')
    }
})

Vue.filter('formHour', function (value) {
    if (value && value != '') {
        return moment(value).format('HH:mm')
    }
})

var filter = function (text, length, clamp) {
    clamp = clamp || '...'
    var node = document.createElement('div')
    node.innerHTML = text
    var content = node.textContent
    return content.length > length ? content.slice(0, length) + clamp : content
}

Vue.filter('truncate', filter)

Vue.filter('formatPrice', function (value) {
    value = parseFloat(value)
    let val = (value / 1).toFixed(2)
    return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')
})

Vue.filter('str_limit', function (value, size) {
    if (!value) return ''
    value = value.toString()

    if (value.length <= size) {
        return value
    }
    return value.substr(0, size) + ' ...'
})

// import Clipboard from 'v-clipboard'
import VueClipboard from 'vue-clipboard2'
import Cookies from "js-cookie";

VueClipboard.config.autoSetContainer = true // add this line
Vue.use(VueClipboard)

//import jsPDF from 'jspdf'
//Vue.use(jsPDF)
//var jsPDF = require('jspdf')
/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

// const app = new Vue({
//     el: '#app',
// });
//functions basicas
$(document).ready(function () {

    //active hover nav dropdown menu
    $('.nav-secondary .nav-link').hover(function (e) {
        if (e.type === 'mouseenter') {
            $(this).parent().parent().parent().find('.nav-item-title').addClass('activeHover')
        } else {
            $(this).parent().parent().parent().find('.nav-item-title').removeClass('activeHover')
        }
    })

    //Show inut search
    $('#searchDisplay').on('click', function () {
        var inputHide = $('.navbar .form-inline .form-group')
        inputHide.animate({ 'width': 'toggle' }, 200)
    })

    function stopPropagation() {
        $('.container_quantity_persons_rooms, .dropdown-menu ').click(function (e) {
            e.stopPropagation()
            $(this).show()
        })
    }

    stopPropagation()

    $('.agregar-destino').click(function (e) {
        setTimeout(function () {
            stopPropagation()
        }, 100)
    })

    $('.busqueda-avanzada').click(function (e) {
        setTimeout(function () {
            stopPropagation()
        }, 100)
    })

    $('.btn.btn-primary').click(function (e) {
        stopPropagationSearch()
    })

    function stopPropagationSearch() {
        if ($('.filtros').length < 1) {
            setTimeout(function () {
                stopPropagationSearch()
            }, 5)
        } else {
            $('.dropdown-menu ').click(function (e) {
                e.stopPropagation()
                $(this).show()
            })
        }
    }

    $('.scrollbar-outer, .scrollbar-dynamic').scrollbar()

    //------------------------------------------------------
    // LAYOUT: TOOLTIP (?) // Conflicto BS vs JQuery UI
    //------------------------------------------------------

    //$('[data-toggle="tooltip"]').tooltip();
    $(function () {
        $(document).tooltip()

        $(document).on('click', '.btn', function () {
            if ($(document).tooltip().length > 0) {
                $(document).tooltip('destroy')
            }
        })
    })

    //------------------------------------------------------
    // LAYOUT: TOOLTIP (?) // Conflicto BS vs JQuery UI
    //------------------------------------------------------

    $('.btn-cotizacion').on('click', function () {
        $('#modalCotizaciones').modal({
            backdrop: true
        })
    })
    $('.btn-alert').on('click', function () {
        $('#modalAlerta').modal({
            backdrop: true
        })
    })
})

Vue.directive('click-outside', {
    bind: function (el, binding, vnode) {
        console.log('sss')
        el.clickOutsideEvent = function (event) {
            // here I check that click was outside the el and his childrens
            if (!(el == event.target || el.contains(event.target))) {
                // and if it did, call method provided in attribute value
                vnode.context[binding.expression](event);
            }
        };
        document.body.addEventListener('click', el.clickOutsideEvent)
    },
    unbind: function (el) {
        document.body.removeEventListener('click', el.clickOutsideEvent)
    },
});

// NUEVAS ESTADISTICAS DE PEDIDOS -- NO QUITAR..

window.groupBy = async (orders, field) => {
    let response = {}

    orders.forEach((item, i) => {

        let _field = String(item[field]).trim();

        if (response[_field] == undefined) {
            Vue.set(response, _field, {
                index: _field,
                orders: [],
                stats: {}
            })
        }

        response[_field]['orders'].push(item)
    })

    Object.entries(response).forEach(async (value, key) => {
        response[value[0]]['stats'] = await window.allStatsOrders(value[1]['orders'])
    })

    return response
}

window.allStatsOrders = async (orders) => {
    const pedidosSet = new Set();
    const filesSet = new Set();
    const quotesSet = new Set();
    const cotizacionesMap = new Map();
    const montoEstimadoPorPedido = new Map();

    let cantidadCotizaciones = 0;
    let cantidadRespondidasATiempo = 0;
    let cantidadPedidos = 0;
    let cantidadPedidosConcretados = 0;
    let cantidadFilesConcretados = 0;
    let montoEstimado = 0;
    let montoEstimadoConcretado = 0;
    let cantidadCotisAurora = 0;
    let cantidadCotisStela = 0;

    // Consolidar cotizaciones únicas y contar pedidos
    for (const value of orders) {
        const key = [value.nroped, value.nroord, value.nroref, value.nrofile]
            .map((v) => String(v).trim())
            .join('_');
        const nroped = String(`${value.nroped}`).trim();

        if (!cotizacionesMap.has(key)) {
            cotizacionesMap.set(key, value);
        }

        if (!pedidosSet.has(nroped)) {
            pedidosSet.add(nroped);
            cantidadPedidos += 1;
        }
    }

    for (const [, value] of cotizacionesMap.entries()) {
        const nroref = String(value.nroref || '').trim();
        const chkpro = value.chkpro || 0;

        // if (nroref !== '' || chkpro > 0) {
        if (nroref && !quotesSet.has(nroref)) {
            quotesSet.add(nroref);
            const codsecRaw = parseInt(String(value.codsec).trim());
            const codsec = String(codsecRaw).length > 1
                ? parseInt(String(codsecRaw).charAt(2))
                : codsecRaw;

            const tiemposPorSector = [0, 12, 72, 0, 48, 120];
            const limiteHoras = tiemposPorSector[codsec] || 0;
            const horas = parseFloat(value.horas || 0);

            if (limiteHoras > 0 && horas <= limiteHoras) {
                cantidadRespondidasATiempo += 1;
            }

            if (value.nroref_identi === 'A') cantidadCotisStela += 1;
            if (value.nroref_identi === 'B') cantidadCotisAurora += 1;

            cantidadCotizaciones += 1;

            const priceEstimated = parseFloat(
                (value.price_estimated || '').toString().replace(',', '')
            ) || 0;

            const nroped = String(`${value.nroped}`).trim();
            if (!montoEstimadoPorPedido.has(nroped)) {
                montoEstimadoPorPedido.set(nroped, []);
            }

            montoEstimadoPorPedido.get(nroped).push(priceEstimated);
        }

        const nrofile = value.nrofile;

        if (nrofile && !filesSet.has(nrofile)) {
            filesSet.add(nrofile);

            const priceEnd = parseFloat(
                (value.price_end || '').toString().replace(',', '')
            ) || 0;

            cantidadPedidosConcretados += 1;
            cantidadFilesConcretados += 1;
            montoEstimadoConcretado += priceEnd;
        }
    }

    // Calcular promedios de monto estimado
    for (const montos of montoEstimadoPorPedido.values()) {
        const subtotal = montos.reduce((acc, val) => acc + val, 0);
        montoEstimado += subtotal / montos.length;
    }

    const porcentajeRespondidas = cantidadCotizaciones > 0
        ? Math.round((cantidadRespondidasATiempo * 100) / cantidadCotizaciones)
        : 0;

    const porcentajeConcrecion = cantidadPedidos > 0
        ? Math.round((cantidadPedidosConcretados * 100) / cantidadPedidos)
        : 0;

    const ratioTrabajo = cantidadPedidosConcretados > 0
        ? (cantidadCotizaciones / cantidadPedidosConcretados).toFixed(2)
        : 0;

    const ratioRecotizacion = cantidadPedidos > 0
        ? (cantidadCotizaciones / cantidadPedidos).toFixed(2)
        : 0;

    const percentStela = cantidadCotizaciones > 0
        ? ((cantidadCotisStela / cantidadCotizaciones) * 100).toFixed(2)
        : 0;

    const percentAurora = cantidadCotizaciones > 0
        ? ((cantidadCotisAurora / cantidadCotizaciones) * 100).toFixed(2)
        : 0;

    return {
        all_quotes: cantidadCotizaciones,
        quotes_ok: cantidadRespondidasATiempo,
        work_rate: ratioTrabajo,
        work_rate_orders: ratioRecotizacion,
        all_orders: cantidadPedidos,
        mount_all_orders: montoEstimado.toFixed(2),
        orders_placed: cantidadPedidosConcretados,
        files_placed: cantidadFilesConcretados,
        mount_orders_placed: montoEstimadoConcretado.toFixed(2),
        percent_placed: porcentajeConcrecion,
        time_response: porcentajeRespondidas,
        stela_quotes: cantidadCotisStela,
        aurora_quotes: cantidadCotisAurora,
        percent_stela_quotes: percentStela,
        percent_aurora_quotes: percentAurora
    };
};

window.getMonth = async (month) => {
    let months = ['ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO', 'JULIO', 'AGOSTO', 'SETIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE'];
    return months[month];
}

window.rankingOrders = async (orders_totales) => {
    let meses = [];

    orders_totales.forEach(async (value, key) => {
        let month = parseInt(moment(String(value['fecrec']), 'YYYY-MM-DD').get('month'))

        if (meses[month] == undefined) {
            Vue.set(meses, month, [])
        }

        meses[month].push(value)
    })

    // Response..
    let responseMonths = [];
    let allStats = await window.allStatsOrders(orders_totales);

    // Agrupando por especialista..
    let responseExecutives = await window.groupBy(orders_totales, 'codusu');

    // Agrupando por cliente..
    let responseCustomers = await window.groupBy(orders_totales, 'codigo');

    // -- FIN DEL ACUMULADO..
    let response = {
        quantity: orders_totales.length,
        customers: responseCustomers,
        executives: responseExecutives,
        all: allStats
    };

    meses.forEach(async (item, i) => {
        let mes = await window.getMonth(i);
        // Response..
        let allStats = await window.allStatsOrders(item);

        // Agrupando por especialista..
        let responseExecutives = await window.groupBy(item, 'codusu');

        // Agrupando por cliente..
        let responseCustomers = await window.groupBy(item, 'codigo');

        responseMonths.push({
            month: mes,
            quantity: item.length,
            customers: responseCustomers,
            executives: responseExecutives,
            all: allStats
        })
    });

    let response_general = { ranking: responseMonths, acumulado: response, meses: meses }
    console.log(response_general);

    return response_general;
}

window.getData = async function (_data) {
    let response = []

    Object.entries(_data).forEach((item, i) => {
        response.push(item[1])
    })

    return response
}
