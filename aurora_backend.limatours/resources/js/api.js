import axios from 'axios'
import {removeToken, clearAccessToken} from './auth'

export const APICLOUDINARY = axios.create({
    timeout: 360000
})

export const API = axios.create({
    baseURL: `${window.origin}/api`,
    timeout: 360000,
    headers: {'Content-Type': 'application/json', 'Accept': 'application/json'}
})

export const APISERVICE = axios.create({
    baseURL: `${window.origin}/services`,
    timeout: 360000,
    headers: {'Content-Type': 'application/json', 'Accept': 'application/json'}
})

export const APISTELA = axios.create({
    baseURL: process.env.API_STELLA_URL,
    timeout: 360000,
    headers: {'Content-Type': 'application/json', 'Accept': 'application/json'}
})

export const APIFILES = axios.create({
    baseURL: process.env.MIX_FILES_MS_URL,
    timeout: 360000,
    headers: {'Content-Type': 'application/json', 'Accept': 'application/json'}
})

API.interceptors.request.use((request) => {
    let token = window.localStorage.getItem('access_token')

    token = (token === null || token === 'undefined') ? false : token

    if (token === false) {
        window.location = '/#/login?redirect=%2F' + window.hash.substr(2)
    } else {
        request.headers.common['Authorization'] = 'Bearer ' + token
    }

    return request
})

// Add a 401 response interceptor
API.interceptors.response.use((response) => {
    return response
}, (error) => {
    if (error.response.status === 401) {
        if (window.location.hash.indexOf('login') === -1) {
            window.location = '/#/login?redirect=%2F' + window.location.hash.substr(2)
        }
        removeToken();
        clearAccessToken();
    } else {
        return Promise.reject(error)
    }
})

APISERVICE.interceptors.request.use((request) => {
    let token = window.localStorage.getItem('access_token')

    token = (token === null || token === 'undefined') ? false : token

    if (token === false) {
        window.location = '/#/login?redirect=%2F' + window.hash.substr(2)
    } else {
        request.headers.common['Authorization'] = 'Bearer ' + token
    }

    return request
})

// Add a 401 response interceptor
APISERVICE.interceptors.response.use((response) => {
    return response
}, (error) => {
    if (error.response.status === 401) {
        if (window.location.hash.indexOf('login') === -1) {
            window.location = '/#/login?redirect=%2F' + window.location.hash.substr(2)
        }
    } else {
        return Promise.reject(error)
    }
})


APISTELA.interceptors.request.use((request) => {
    let token = window.localStorage.getItem('access_token')

    token = (token === null || token === 'undefined') ? false : token

    if (token === false) {
        window.location = '/#/login?redirect=%2F' + window.hash.substr(2)
    } else {
        request.headers.common['Authorization'] = 'Bearer ' + token
    }

    return request
})

// Add a 401 response interceptor
APISTELA.interceptors.response.use((response) => {
    return response
}, (error) => {
    if (error.response.status === 401) {
        if (window.location.hash.indexOf('login') === -1) {
            window.location = '/#/login?redirect=%2F' + window.location.hash.substr(2)
        }
    } else {
        return Promise.reject(error)
    }
})


APIFILES.interceptors.request.use((request) => {
    let token = window.localStorage.getItem('access_token')

    token = (token === null || token === 'undefined') ? false : token

    if (token === false) {
        window.location = '/#/login?redirect=%2F' + window.hash.substr(2)
    } else {
        request.headers.common['Authorization'] = 'Bearer ' + token
    }

    return request
})

// Add a 401 response interceptor
APIFILES.interceptors.response.use((response) => {
    return response
}, (error) => {
    if (error.response.status === 401) {
        if (window.location.hash.indexOf('login') === -1) {
            window.location = '/#/login?redirect=%2F' + window.location.hash.substr(2)
        }
    } else {
        return Promise.reject(error)
    }
})
