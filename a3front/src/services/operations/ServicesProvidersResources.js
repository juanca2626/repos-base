import axios from 'axios';

export default class ServicesProvidersResources {
  constructor() {
    this.pathBack = `${window.url_providers}ms-providers`;
  }

  getLanguages() {
    return new Promise((resolve, reject) => {
      axios
        .get(`${this.pathBack}/external-services/languages`)
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
        .get(`${this.pathBack}/external-services/campus`)
        .then((response) => {
          resolve(response.data.data);
        })
        .catch((error) => {
          console.log(error);
          reject(error);
        });
    });
  }

  getProfileTypes() {
    return new Promise((resolve, reject) => {
      axios
        .get(`${this.pathBack}/provider-type-profiles/1`)
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
        .get(`${this.pathBack}/provider-specialties`)
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
