import decode from 'jwt-decode'
import axios from 'axios'
import Router from 'vue-router'
import Cookies from 'js-cookie'

const router = new Router({
  mode: 'history',
})

export function login (code, password) {
  return axios.post('/api/login', {
    code: code,
    password: password,
  }).then(response => {
    if (response.status === 200) {
      window.localStorage.setItem('access_token', response.data.access_token)
      window.localStorage.setItem('allotment', 0)
      setLogged(response.data.access_token)
      setUserKey(response.data.user_id)
      return { success: true, permissions: response.data.permissions }
    } else {
      return { success: false, data: response }
    }
  }).catch((error) => {
    return { success: false, data: error };
  })
}

export async function loginCross (token) {
  window.localStorage.setItem('access_token', token)
  window.localStorage.setItem('allotment', 0)
  return { success: true }
}

export function requestResetPassword (email) {
  return axios.post('/api/reset-password', {
    email: email, lang: window.localStorage.getItem('lang'),
  }).then(response => {
    return response.data
  }, error => {
    console.error(error)
  })
}

export function resetPassword (email, token, password, confirPassword) {
  return axios.post('/api/reset/password/', {
    email: email,
    token: token,
    password: password,
    confirPassword: confirPassword,
  }).then(response => {
    return response.data
  }, error => {
    console.error(error)
  })
}

export function logout () {
  clearAccessToken()
  router.push('/login')
}

export function requireAuth (to, from, next) {
  if (!isLoggedIn()) {
    next({
      path: '/login',
      query: { redirect: to.fullPath },
    })
  } else {
    next()
  }
}

export function isLoggedIn () {
  const accessToken = getAccessToken()
  return !!accessToken && !isTokenExpired(accessToken)
}

export function getAccessToken () {
  return Cookies.get(window.tokenKey) //window.localStorage.getItem('access_token')
}

function getTokenExpirationDate (encodedToken) {
  const token = decode(encodedToken)
  if (!token.exp) { return null }

  const date = new Date(0)
  date.setUTCSeconds(token.exp)

  return date
}

function isTokenExpired (token) {
  const expirationDate = getTokenExpirationDate(token)
  return expirationDate < new Date()
}

export function clearAccessToken () {
  window.localStorage.removeItem('access_token')
  window.localStorage.removeItem('client_id')
  window.localStorage.removeItem('user_permissions')
}

export function getUser () {
  return axios.get('/api/me',
    { headers: { 'Authorization': 'Bearer ' + getAccessToken() } }).
    then(response => {
      if (response.status === 200) {
        return { success: true, data: response.data }
      } else {
        logout()
      }
    })
}

axios.interceptors.response.use(function (response) {
  return response
}
,
function (error) {
      if (error.response.status === 401) {
        if (window.location.hash.indexOf('login') === -1) {
          logout()
          window.location = '/#/login?redirect=%2F' + window.location.hash.substr(2)
        }
      }
      return Promise.reject(error)
    }
)

export function setLogged (value) {
  return Cookies.set(window.tokenKey, value, { domain: window.domain })
}

export function setUserKey (value) {
  return Cookies.set(window.userKey, value, { domain: window.domain })
}

export function removeToken () {
  Cookies.remove(window.userKey, { domain: window.domain })
  return Cookies.remove(window.tokenKey, { domain: window.domain })
}
