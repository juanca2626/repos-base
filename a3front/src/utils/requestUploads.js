import axios from 'axios';

const AMAZON_UPLOAD = import.meta.env.VITE_APP_URL_S3;

const headers = {
  'Content-Type': 'application/json',
};

export default axios.create({ headers, timeout: 35000, baseURL: AMAZON_UPLOAD });
