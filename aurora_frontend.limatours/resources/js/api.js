import axios from 'axios';
import Cookies from 'js-cookie';

export const API = axios.create();

API.interceptors.request.use((config) => {
    let token = Cookies.get(window.tokenKey);

    token = (token === null || token === 'undefined') ? false : token;

    console.log("Token de permiso: " + token);

    if (token === false) {
        window.location = '/#/login?redirect=%2F' + window.location.hash.substr(2);
    } else {
        config.headers.Authorization = `Bearer ${token}`;
        config.headers['X-CSRF-TOKEN'] = window.csrfToken;
        // CORS middleware
    }

    return config;
}, (error) => {
    return Promise.reject(error);
});

// Add a 401 response interceptor
API.interceptors.response.use((response) => {
    return response;
}, (error) => {
    console.log("Error API: " + error);
    if (error.response.status === 401) {
        if (window.location.hash.indexOf('login') === -1) {
            window.location = '/#/login?redirect=%2F' + window.location.hash.substr(2);
        }
    } else {
        return Promise.reject(error);
    }
});
