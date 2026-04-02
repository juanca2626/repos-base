import axios from 'axios';
import Cookies from 'js-cookie';

const TOKEN_COGNITO_KEY = import.meta.env.VITE_TOKEN_KEY_COGNITO_LIMATOUR;
const DYNAMO_URL = import.meta.env.VITE_APP_AMAZON_DYNAMO_URL;
const accessTokenCognito = Cookies.get(TOKEN_COGNITO_KEY);

const headers = {
  'Content-Type': 'application/json',
  Authorization: `Bearer ${accessTokenCognito}`,
};

export default axios.create({ headers, timeout: 35000, baseURL: DYNAMO_URL });
