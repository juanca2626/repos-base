import Cookies from 'js-cookie';
import { jwtDecode as jwt_decode } from 'jwt-decode';

export function isAuthenticated() {
  const accessToken = getAccessToken();
  return !!accessToken && !isTokenExpired(accessToken);
}

export function isAuthenticatedCognito() {
  const accessToken = getAccessTokenCognito();
  return !!accessToken && !isTokenExpiredCognito(accessToken);
}

export function getUrlAuroraBack() {
  return window.url_back_a2;
}

export function getUrlAuroraQuoteBack() {
  return window.url_back_quote_a3;
}

export function getUrlAuroraFront() {
  return window.url_front_a2;
}

export function getAccessToken() {
  return Cookies.get(window.TOKEN_KEY);
}

export function setCookieLocal(key, value) {
  if (window.environment == 'local') {
    let domains = ['127.0.0.1', 'localhost'];
    domains.forEach((domain) => {
      Cookies.set(key, value, { domain: domain });
    });
  }
}

export function removeCookiesLocal() {
  let domains = ['127.0.0.1', 'localhost'];

  domains.forEach((domain) => {
    Cookies.remove(window.USER_CODE_KEY, { domain: domain });
    Cookies.remove(window.USER_TYPE, { domain: domain });
    Cookies.remove(window.USER_EMAIL, { domain: domain });
    Cookies.remove(window.USER_ID, { domain: domain });
    Cookies.remove(window.USER_NAME, { domain: domain });
    Cookies.remove(window.USER_CLIENT_ID, { domain: domain });
    Cookies.remove(window.USER_DEPARTMENT_ID, { domain: domain });
    Cookies.remove(window.USER_DEPARTMENT_NAME, { domain: domain });
    Cookies.remove(window.USER_DEPARTMENT_TEAM_ID, { domain: domain });
    Cookies.remove(window.USER_DEPARTMENT_TEAM_NAME, { domain: domain });
    Cookies.remove(window.USER_DISABLE_RESERVATION, { domain: domain });
    Cookies.remove(window.TOKEN_COGNITO_KEY, { domain: domain });
    Cookies.remove(window.TOKEN_KEY, { domain: domain });
    Cookies.remove(window.USER_ROLE_KEY, { domain: domain });
  });

  localStorage.removeItem('access_token');
  localStorage.removeItem('client_id');

  return true;
}

export function setAccessToken(value) {
  setCookieLocal(window.TOKEN_KEY, value);
  return Cookies.set(window.TOKEN_KEY, value, { domain: window.DOMAIN });
}

export function getAccessTokenCognito() {
  let token = Cookies.get(window.TOKEN_COGNITO_KEY);
  if (token == null) {
    token = localStorage.getItem('token_cognito');
  }
  return token ? token : null;
}

export function setAccessTokenCognito(value) {
  localStorage.setItem('token_cognito', value);
  localStorage.setItem('access_token', value);
  setCookieLocal(window.TOKEN_COGNITO_KEY, value);
  return Cookies.set(window.TOKEN_COGNITO_KEY, value, {
    domain: window.DOMAIN,
  });
}

export function setUserCode(value) {
  localStorage.setItem('user_code', value);
  setCookieLocal(window.USER_CODE_KEY, value);
  return Cookies.set(window.USER_CODE_KEY, value, { domain: window.DOMAIN });
}

export function getUserCode() {
  return Cookies.get(window.USER_CODE_KEY);
}

export function setUserEmail(value) {
  setCookieLocal(window.USER_EMAIL, value);
  return Cookies.set(window.USER_EMAIL, value, { domain: window.DOMAIN });
}

export function getUserEmail() {
  return Cookies.get(window.USER_EMAIL);
}

export function setUserId(value) {
  setCookieLocal(window.USER_ID, value);
  return Cookies.set(window.USER_ID, value, { domain: window.DOMAIN });
}

export function getUserId() {
  return Cookies.get(window.USER_ID);
}

export function setUserType(value) {
  localStorage.setItem('type', value);
  setCookieLocal(window.USER_TYPE, value);
  return Cookies.set(window.USER_TYPE, value, { domain: window.DOMAIN });
}

export function setUserContract(value) {
  localStorage.setItem('contract', value);
}

export function getUserType() {
  return Cookies.get(window.USER_TYPE);
}

export function hasPermission(permission, action) {
  let index = 0;
  let flag_permission = false;
  let enable = false;
  let permissions = JSON.parse(localStorage.getItem('permissions'));

  for (let i = 0; i < permissions.length; i++) {
    if (permissions[i].subject === permission) {
      flag_permission = true;
      index = i;
    }
  }
  if (flag_permission) {
    for (let a = 0; a < permissions[index].actions.length; a++) {
      if (permissions[index].actions[a] === action) {
        enable = true;
      }
    }
  }
  return enable;
}

export function setUserName(value) {
  setCookieLocal(window.USER_NAME, value);
  return Cookies.set(window.USER_NAME, value, { domain: window.DOMAIN });
}

export function getUserName() {
  return Cookies.get(window.USER_NAME);
}

export function setUserClientId(value) {
  setCookieLocal(window.USER_CLIENT_ID, value);
  localStorage.setItem('client_id', value);
  return Cookies.set(window.USER_CLIENT_ID, value, {
    domain: window.DOMAIN,
  });
}

export function setUserRole(value) {
  setCookieLocal(window.USER_ROLE_KEY, value);
  return Cookies.set(window.USER_ROLE_KEY, value, {
    domain: window.DOMAIN,
  });
}

export function getUserClientId() {
  return Cookies.get(window.USER_CLIENT_ID);
}

export function getUserRole() {
  return Cookies.get(window.USER_ROLE_KEY);
}

export function isAdmin() {
  const role = getUserRole() ?? '';
  return role.toLowerCase() === 'admin';
}

export function setUserDepartmentId(value) {
  setCookieLocal(window.USER_DEPARTMENT_ID, value);
  return Cookies.set(window.USER_DEPARTMENT_ID, value, {
    domain: window.DOMAIN,
  });
}

export function getUserDepartmentId() {
  return Cookies.get(window.USER_DEPARTMENT_ID);
}

export function setUserDepartmentName(value) {
  setCookieLocal(window.USER_DEPARTMENT_NAME, value);
  return Cookies.set(window.USER_DEPARTMENT_NAME, value, {
    domain: window.DOMAIN,
  });
}

export function getUserDepartmentName() {
  return Cookies.get(window.USER_DEPARTMENT_NAME);
}

export function setUserDepartmentTeamId(value) {
  setCookieLocal(window.USER_DEPARTMENT_TEAM_ID, value);
  return Cookies.set(window.USER_DEPARTMENT_TEAM_ID, value, {
    domain: window.DOMAIN,
  });
}

export function getUserDepartmentTeamId() {
  return Cookies.get(window.USER_DEPARTMENT_TEAM_ID);
}

export function setUserDepartmentTeamName(value) {
  setCookieLocal(window.USER_DEPARTMENT_TEAM_NAME, value);
  return Cookies.set(window.USER_DEPARTMENT_TEAM_NAME, value, {
    domain: window.DOMAIN,
  });
}

export function getUserDepartmentTeamName() {
  return Cookies.get(window.USER_DEPARTMENT_TEAM_NAME);
}

export function getUserDisableReservation() {
  return Cookies.get(window.USER_DISABLE_RESERVATION);
}

export function removeCookiesCross() {
  removeCookiesLocal();
  Cookies.remove(window.USER_CODE_KEY, { domain: window.DOMAIN });
  Cookies.remove(window.USER_TYPE, { domain: window.DOMAIN });
  Cookies.remove(window.USER_EMAIL, { domain: window.DOMAIN });
  Cookies.remove(window.USER_ID, { domain: window.DOMAIN });
  Cookies.remove(window.USER_NAME, { domain: window.DOMAIN });
  Cookies.remove(window.USER_CLIENT_ID, { domain: window.DOMAIN });
  Cookies.remove(window.USER_DEPARTMENT_ID, { domain: window.DOMAIN });
  Cookies.remove(window.USER_DEPARTMENT_NAME, { domain: window.DOMAIN });
  Cookies.remove(window.USER_DEPARTMENT_TEAM_ID, { domain: window.DOMAIN });
  Cookies.remove(window.USER_DEPARTMENT_TEAM_NAME, { domain: window.DOMAIN });
  Cookies.remove(window.USER_DISABLE_RESERVATION, { domain: window.DOMAIN });
  Cookies.remove(window.USER_ROLE_KEY, { domain: window.DOMAIN });
  Cookies.remove(window.TOKEN_COGNITO_KEY, { domain: window.DOMAIN });
  return Cookies.remove(window.TOKEN_KEY, { domain: window.DOMAIN });
}

function isTokenExpired(token) {
  const expirationDate = getTokenExpirationDate(token);
  return expirationDate < new Date();
}

function isTokenExpiredCognito(token) {
  const expirationDate = getTokenExpirationDate(token);
  return expirationDate < new Date();
}

function getTokenExpirationDate(encodedToken) {
  const token = jwt_decode(encodedToken);
  if (!token.exp) {
    return new Date();
  }

  const date = new Date(0);
  date.setUTCSeconds(token.exp);

  return date;
}

// OPE USER (TRP / GUI)
export const getUserInfo = () => {
  return {
    username: localStorage.getItem('user_code'),
    type: localStorage.getItem('type'),
    contract: localStorage.getItem('contract'),
  };
};
