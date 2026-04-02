import axios from 'axios';
import Cookies from 'js-cookie';

const TOKEN_KEY = import.meta.env.VITE_TOKEN_KEY_LIMATOUR;
const url_back_quote_a3 = import.meta.env.VITE_APP_BACKEND_QUOTE_A3_URL;
const accessToken = Cookies.get(TOKEN_KEY);

const headers = {
  'Content-Type': 'application/json',
  Authorization: `Bearer ${accessToken}`,
};

export default axios.create({ headers, timeout: 35000, baseURL: url_back_quote_a3 });
