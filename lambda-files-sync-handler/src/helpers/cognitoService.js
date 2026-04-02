import axios from 'axios';
import jwt from 'jsonwebtoken';

class CognitoService {
    constructor() {
      // Variables de entorno para client ID, client secret y token URL
      this.clientId = process.env.CLIENT_APP_ID;
      this.clientSecret = process.env.CLIENT_APP_SECRET;
      this.cognitoTokenUrl = process.env.USER_POOL_URL;
    }
  
    // Método para obtener el token
    async getAccessToken() {
      const tokenData = new URLSearchParams();
      tokenData.append('grant_type', 'client_credentials');
      tokenData.append('client_id', this.clientId);
      tokenData.append('client_secret', this.clientSecret);

      console.log("CLIENT ID: ", this.clientId);
      console.log("CLIENTE SECRET: ", this.clientSecret);
      console.log("COGNITO TOKEN URL: ", this.cognitoTokenUrl);
  
      try {
        const response = await axios.post(`${this.cognitoTokenUrl}/oauth2/token`, tokenData, {
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
        });
  
        return response.data.access_token;
      } catch (error) {
        console.log("ERROR AL OBTENER EL TOKEN: ", error);
        return ''
      }
    }

    async getID(access_token) {
      return 1;
      /*
      const urlAuth = `${process.env.BACKEND_URL_AUTH_COGNITO}auth/me`;
      console.log("auth: ", urlAuth);
      console.log("access token: ", access_token);
      try {
        const response = await axios({
          method: 'get',
          url: urlAuth,
          headers: {
            Authorization: `Bearer ${access_token}`,
            'Content-Type': 'application/json',
          }
        });

        console.log("RESPONSE COGNITO: ", JSON.stringify(response));
  
        return response.data.id;
      } catch (error) {
        console.log("ERROR AL OBTENER EL TOKEN ID: ", error);
        return ''
      }
      */
    }
}

export default CognitoService;