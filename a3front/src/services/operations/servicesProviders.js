import axios from 'axios';

class ServicesProviders {
  constructor() {
    this.pathBack = `${window.url_providers}ms-providers`;
  }

  createUserProfile(data) {
    return new Promise((resolve, reject) => {
      axios
        .post(`${this.pathBack}/providers`, data, {
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

  createUserProfileOPE(data) {
    return new Promise((resolve, reject) => {
      axios
        .post(`${this.pathBack}/providers/create-ope`, data, {
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

  updateUserProfile(id, data) {
    return new Promise((resolve, reject) => {
      axios
        .put(`${this.pathBack}/providers/${id}`, data, {
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

  listProfiles({ iso, page, code, name, status, profile, language }) {
    return new Promise((resolve, reject) => {
      axios
        .get(`${this.pathBack}/providers/${iso}/filter/by`, {
          params: {
            page,
            perPage: 10,
            code,
            name,
            profile,
            status,
            language,
          },
        })
        .then((response) => {
          resolve(response.data);
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

  getProfiles() {
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

  getStats() {
    return new Promise((resolve, reject) => {
      axios
        .get(`${this.pathBack}/providers/counts/guides`)
        .then((response) => {
          resolve(response.data.data);
        })
        .catch((error) => {
          console.log(error);
          reject(error);
        });
    });
  }

  getProfileInfo(code) {
    return new Promise((resolve, reject) => {
      axios
        .get(`${this.pathBack}/providers/${code}`)
        .then((response) => {
          resolve(response.data.data);
        })
        .catch((error) => {
          console.log(error);
          reject(error);
        });
    });
  }

  uploadFile(file) {
    return new Promise((resolve, reject) => {
      axios
        .post(`${this.pathBack}/files`, file, {
          headers: {
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
  activateProfile(code) {
    return new Promise((resolve, reject) => {
      axios
        .get(`${this.pathBack}/providers/active/${code}`)
        .then((response) => {
          resolve(response.data.data);
        })
        .catch((error) => {
          console.log(error);
          reject(error);
        });
    });
  }
  deactivateProfile(code) {
    return new Promise((resolve, reject) => {
      axios
        .get(`${this.pathBack}/providers/inactive/${code}`)
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

export default ServicesProviders;
