import axios from 'axios';

export default class ServicesOperationsResources {
  constructor() {
    this.pathBack = `${window.url_back_ope}operating_guidelines`;
  }

  // return a promise of axios get with the markets
  getMarkets(lang = 'es') {
    return new Promise((resolve, reject) => {
      axios
        .get(`${this.pathBack}/external_services/markets?lang=${lang}`, {
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

  // return a promise of axios get with the clients
  getClients(query = '', lang = 'es') {
    return new Promise((resolve, reject) => {
      axios
        .get(`${this.pathBack}/external_services/clients?lang=${lang}&queryCustom=${query}`, {
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

  getLanguages() {
    return new Promise((resolve, reject) => {
      axios
        .get(`${this.pathBack}/external_services/languages`, {
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

  getItems() {
    return new Promise((resolve, reject) => {
      axios
        .get(`${this.pathBack}/items`, {
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

  getSubItems(id) {
    return new Promise((resolve, reject) => {
      axios
        .get(`${this.pathBack}/items/belongs_to/${id}`, {
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

  getGuides() {
    return new Promise((resolve, reject) => {
      axios ///profile=1 is id of the profile type guide
        .get(`${this.pathBack}/external_services/providers/by?profile=1&perPage=100`, {
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

  getTransfers() {
    return new Promise((resolve, reject) => {
      axios ///profile=2 is id of the profile type transfer
        .get(`${this.pathBack}/external_services/providers/by?profile=2&perPage=100`, {
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

  getEscorts() {
    return new Promise((resolve, reject) => {
      axios ///profile=3 is id of the profile type escort
        .get(`${this.pathBack}/external_services/providers/by?profile=3&perPage=100`, {
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

  getSpecialties() {
    return new Promise((resolve, reject) => {
      axios
        .get(`${this.pathBack}/external_services/specialties`, {
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
}
