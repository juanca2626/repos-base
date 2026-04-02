import axios from 'axios';
import Cookies from 'js-cookie';

window.TOKEN_KEY = import.meta.env.VITE_TOKEN_KEY_COGNITO_LIMATOUR;
window.API_NODE_TWO = import.meta.env.VITE_APP_BACKEND + '/api/v1/commercial/ifx';

const accessTokenCognito = Cookies.get(window.TOKEN_KEY);

const headers = {
  'Content-Type': 'application/json',
  Authorization: `Bearer ${accessTokenCognito}`,
};

const instance = axios.create({
  headers,
  timeout: 35000,
  baseURL: window.API_NODE_TWO,
});

export default instance;
