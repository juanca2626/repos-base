window._ = require('lodash');
import Cookies from 'js-cookie';
/**
 * We'll load jQuery and the Bootstrap jQuery plugin which provides support
 * for JavaScript based Bootstrap features such as modals and tabs. This
 * code may be modified to fit the specific needs of your application.
 */

try {
    window.Popper = require('popper.js').default;
    window.$ = window.jQuery = require('jquery');

    require('bootstrap');
} catch (e) { }

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

//configuracion keys y dominio para cross auth
window.domain = process.env.MIX_DOMAIN
window.tokenKey = process.env.MIX_TOKEN_KEY_LIMATOUR
window.tokenKeyCognito = 'token_cognito_limatour'
window.url_s3 = process.env.MIX_TOKEN_KEY_URL_S3
window.userKey = process.env.MIX_USER_KEY_LIMATOUR
window.userType = process.env.MIX_USER_TYPE_LIMATOUR
window.userName = process.env.MIX_USER_NAME_LIMATOUR
window.userClientId = process.env.MIX_USER_CLIENT_ID_LIMATOUR
window.userDisableReservation = process.env.MIX_USER_DISABLE_RESERVATION_LIMATOUR
window.parametersPackagesDetails = process.env.MIX_PARAMETERS_PACKAGES_DETAILS

const axios = require('axios');

let csrfTokenElement = document.head.querySelector('meta[name="csrf-token"]');
let csrfToken = csrfTokenElement ? csrfTokenElement.content : null;

window.axios = axios.create({
    baseURL: process.env.MIX_BASE_EXTERNAL_URL,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'Authorization': 'Bearer ' + Cookies.get(window.tokenKey),
        'X-CSRF-TOKEN': csrfToken
    }
});

window.amazonURL = process.env.MIX_APP_AMAZON_SQS_URL;
window.amazonAxios = axios.create({
    baseURL: window.amazonURL,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
});

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from 'laravel-echo'

// window.Pusher = require('pusher-js');

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: process.env.MIX_PUSHER_APP_KEY,
//     cluster: process.env.MIX_PUSHER_APP_CLUSTER,
//     encrypted: true
// });
