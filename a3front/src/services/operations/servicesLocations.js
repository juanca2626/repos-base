import axios from 'axios';

class ServicesLocations {
  constructor() {
    this.pathBack = `${window.url_providers}ms-providers`;
  }
  getDepartmentsPeru() {
    return new Promise((resolve, reject) => {
      axios
        .get(`${this.pathBack}/external-services/departments-peru`)
        .then((response) => {
          resolve(response.data.data);
        })
        .catch((error) => {
          console.log(error);
          reject(error);
        });
    });
  }
  getProvinces(department_id) {
    return new Promise((resolve, reject) => {
      axios
        .get(`${this.pathBack}/external-services/department/${department_id}/provinces`)
        .then((response) => {
          resolve(response.data.data);
        })
        .catch((error) => {
          console.log(error);
          reject(error);
        });
    });
  }
  getDistricts(province_id) {
    return new Promise((resolve, reject) => {
      axios
        .get(`${this.pathBack}/external-services/province/${province_id}/districts`)
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

export default ServicesLocations;
