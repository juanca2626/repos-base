import axios from 'axios';
import { getAccessToken } from '@/utils/auth';

class ServicesOperation {
  markets = [];
  clients = [];
  items = [];
  languages = [];

  constructor() {
    this.pathBack = `${window.url_back_ope}operating_guidelines`;
    this.pathBackA2 = `${window.url_back_a2}api`;
  }

  // return a promise of axios get with the markets
  getMarkets(lang = 'es') {
    return new Promise((resolve, reject) => {
      if (this.markets.length > 0) {
        resolve(this.markets);
        return;
      }
      axios
        .get(`${this.pathBack}/external_services/markets?lang=${lang}`, {
          headers: {
            'Content-Type': 'application/json',
          },
        })
        .then((response) => {
          // set mercado ref new values of response.data.data
          this.markets = response.data.data;
          resolve(this.markets);
        })
        .catch((error) => {
          console.log(error);
          reject(error);
        });
    });
  }

  // return a promise of axios get with the clients
  getClients(query = '', lang = 'es') {
    console.log(query);
    return new Promise((resolve, reject) => {
      axios
        .get(`${this.pathBack}/external_services/clients?lang=${lang}&queryCustom=${query}`, {
          headers: {
            'Content-Type': 'application/json',
          },
        })
        .then((response) => {
          // set clients ref new values of response.data.data
          this.clients = response.data.data;
          resolve(this.clients);
        })
        .catch((error) => {
          console.log(error);
          reject(error);
        });
    });
  }

  getItems(belongsTo = '') {
    return new Promise((resolve, reject) => {
      axios
        .get(`${this.pathBack}/items${belongsTo ? '/belongs_to/' + belongsTo : ''}`, {
          headers: {
            'Content-Type': 'application/json',
          },
        })
        .then((response) => {
          // set items ref new values of response.data.data
          this.items = response.data.data;
          resolve(this.items);
        })
        .catch((error) => {
          console.log(error);
          reject(error);
        });
    });
  }

  getLanguages() {
    return new Promise((resolve, reject) => {
      axios
        .get(`${this.pathBack}/external_services/languages`, {
          headers: {
            'Content-Type': 'application/json',
          },
        })
        .then((response) => {
          // set languages ref new values of response.data.data
          this.languages = response.data.data;
          resolve(response.data.data);
        })
        .catch((error) => {
          console.log(error);
          reject(error);
        });
    });
  }

  getGuidelinesByReference(reference) {
    return new Promise((resolve, reject) => {
      axios
        .get(`${this.pathBack}/guidelines/by_reference/${reference}`, {
          headers: {
            'Content-Type': 'application/json',
          },
        })
        .then((response) => {
          resolve(response.data.data);
        })
        .catch((error) => {
          console.log(error);
          reject(error);
        });
    });
  }

  createGuideline(guideline) {
    return new Promise((resolve, reject) => {
      axios
        .post(`${this.pathBack}/guidelines`, guideline, {
          headers: {
            'Content-Type': 'application/json',
          },
        })
        .then((response) => {
          resolve(response.data.data);
        })
        .catch((error) => {
          console.log(error);
          reject(error);
        });
    });
  }

  updateGuideline(guideline) {
    return new Promise((resolve, reject) => {
      axios
        .put(`${this.pathBack}/guidelines/${guideline.id}`, guideline, {
          headers: {
            'Content-Type': 'application/json',
          },
        })
        .then((response) => {
          resolve(response.data.data);
        })
        .catch((error) => {
          console.log(error);
          reject(error);
        });
    });
  }

  createGuidelineItems(guideline_items) {
    return new Promise((resolve, reject) => {
      axios
        .post(`${this.pathBack}/guideline_items`, guideline_items, {
          headers: {
            'Content-Type': 'application/json',
          },
        })
        .then((response) => {
          resolve(response.data.data);
        })
        .catch((error) => {
          console.log(error);
          reject(error);
        });
    });
  }

  getGuidelineItemsByGuideline(guideline) {
    return new Promise((resolve, reject) => {
      axios
        .get(`${this.pathBack}/guideline_items/by_guideline_id/${guideline}`, {
          headers: {
            'Content-Type': 'application/json',
          },
        })
        .then((response) => {
          resolve(response.data.data);
        })
        .catch((error) => {
          console.log(error);
          reject(error);
        });
    });
  }

  getCampus() {
    return new Promise((resolve, reject) => {
      axios
        .get(`${this.pathBack}/external_services/campus`, {
          headers: {
            'Content-Type': 'application/json',
          },
        })
        .then((response) => {
          resolve(response.data.data);
        })
        .catch((error) => {
          console.log(error);
          reject(error);
        });
    });
  }

  getEjecutives(query = '') {
    return new Promise((resolve, reject) => {
      axios
        .get(`${this.pathBack}/external_services/executives?queryCustom=${query}`, {
          headers: {
            'Content-Type': 'application/json',
          },
        })
        .then((response) => {
          resolve(response.data.data);
        })
        .catch((error) => {
          console.log(error);
          reject(error);
        });
    });
  }

  getTypeVehicles() {
    return new Promise((resolve, reject) => {
      axios
        .get(`${this.pathBack}/external_services/vehicles`, {
          headers: {
            'Content-Type': 'application/json',
          },
        })
        .then((response) => {
          resolve(response.data.data);
        })
        .catch((error) => {
          console.log(error);
          reject(error);
        });
    });
  }

  getUsers() {
    return new Promise((resolve, reject) => {
      axios
        .get(`${this.pathBack}/external_services/users`, {
          headers: {
            'Content-Type': 'application/json',
          },
        })
        .then((response) => {
          resolve(response.data.data);
        })
        .catch((error) => {
          console.log(error);
          reject(error);
        });
    });
  }

  createFileToGuidelineItem(data) {
    return new Promise((resolve, reject) => {
      axios
        .post(`${this.pathBack}/files`, data, {
          headers: {
            'Content-Type': 'multipart/form-data',
            Authorization: `Bearer ${this.token}`,
          },
        })
        .then((response) => {
          resolve(response.data.data);
        })
        .catch((error) => {
          console.log(error);
          reject(error);
        });
    });
  }

  deleteGuidelineItem(idsToDelete) {
    return new Promise((resolve, reject) => {
      axios
        .delete(`${this.pathBack}/guidelines/${idsToDelete.join(',')}`, {
          headers: {
            'Content-Type': 'application/json',
          },
        })
        .then((response) => {
          resolve(response.data.data);
        })
        .catch((error) => {
          console.log(error);
          reject(error);
        });
    });
  }

  uploadLogoClient(data) {
    return new Promise((resolve, reject) => {
      axios
        .post(`${this.pathBackA2}/upload/clientlogo`, data, {
          headers: {
            Authorization: this.bearerToken(),
            'Content-Type': 'multipart/form-data',
          },
        })
        .then((response) => {
          resolve(response.data.data);
        })
        .catch((error) => {
          console.log(error);
          reject(error);
        });
    });
  }

  bearerToken() {
    return `Bearer ${getAccessToken()}`;
  }
}

export default ServicesOperation;
