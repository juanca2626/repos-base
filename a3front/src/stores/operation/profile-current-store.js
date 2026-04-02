import { defineStore } from 'pinia';
import ServicesProviders from '@service/operations/servicesProviders';

export const useProfileCurrentStore = defineStore({
  id: 'profileCurrent',
  state: () => ({
    profile: {
      general: {},
      personal: {},
      contacts: [],
      cv: [],
      observation: '',
      photo_url: '',
    },
    loading: false,
    saving: false,
    editable: false,
    photoFile: null,
    servicesProviders: new ServicesProviders(),
  }),
  actions: {
    async getProfile(id) {
      if (this.profile.general.id !== id) {
        this.profile = await this.servicesProviders.getProfileInfo(id);
        this.setProfile(this.profile);
      }
      return this.profile;
    },
    clearProfile() {
      this.profile = {
        general: {},
        personal: {},
        contacts: [],
        cv: [],
        observation: '',
        photo_url: '',
      };
    },
    setProfile(profile) {
      this.profile = profile;
    },
    setPhotoFile(file) {
      this.photoFile = file;
    },
    setPhotoUrl(url) {
      this.profile.photo_url = url;
    },
    setObservation(observation) {
      this.profile.observation = observation;
    },
    setStatusActive() {
      this.profile.personal.profile_status = 1; //profile_status: 1 = active
      this.profile.personal.status = { id: 1, name: 'active' };
    },
    setStatusInactive() {
      this.profile.personal.profile_status = 2; //profile_status: 0 = inactive
      this.profile.personal.status = { id: 2, name: 'inactive' };
    },
    getCode() {
      if (this.profile.general) {
        return this.profile.general.code;
      }
      return '';
    },
    getName() {
      if (this.profile.general.id) {
        return `${this.profile.personal?.first_name ?? ''} ${
          this.profile.personal?.second_name ?? ''
        } ${this.profile.personal?.surname ?? ''} ${this.profile.personal?.second_surname ?? ''}`;
      }
      return '';
    },
    getPhotoUrl() {
      if (this.profile.general.id) {
        return this.profile.photo_url;
      }
      return '';
    },
    getProfilesTypes() {
      if (this.profile.general.id) {
        return this.profile.general.profiles;
      }
      return [];
    },
    getProfileStatusName() {
      if (this.profile.general.id) {
        return this.profile.personal.status.name;
      }
      return '';
    },
    getContacts() {
      if (this.profile.general.id) {
        return this.profile.contacts;
      }
      return [];
    },
    getObservation() {
      return this.profile.observation ?? null;
    },
    getPercentageCompleteProfile() {
      const totalFields = 16;
      let fieldsCompleted = 0;
      const idProfileTypeGuide = 1; // id profile type guide in database
      if (this.profile.general.id) {
        if (this.profile.general.profiles.includes(idProfileTypeGuide)) {
          if (this.profile.general.card) {
            fieldsCompleted++;
          }
          if (this.profile.general.card_expiration) {
            fieldsCompleted++;
          }
        } else {
          fieldsCompleted += 2;
        }
        if (this.profile.personal.first_name) {
          fieldsCompleted++;
        }
        if (this.profile.personal.surname) {
          fieldsCompleted += 1;
        }
        if (this.profile.personal.birthday) {
          fieldsCompleted += 1;
        }
        if (this.profile.personal.address) {
          fieldsCompleted += 1;
        }
        if (this.profile.personal.department_id) {
          fieldsCompleted += 1;
        }
        if (this.profile.personal.province_id) {
          fieldsCompleted += 1;
        }
        if (this.profile.personal.district_id) {
          fieldsCompleted += 1;
        }
        if (this.profile.general.campus_id) {
          fieldsCompleted += 1;
        }
        if (this.profile.general.campus_iso) {
          fieldsCompleted += 1;
        }
        if (this.profile.personal.profile_status) {
          fieldsCompleted += 1;
        }
        if (this.profile.personal.gender) {
          fieldsCompleted += 1;
        }
        if (this.profile.observation) {
          fieldsCompleted -= 1;
        }
        if (this.profile.photo_url) {
          fieldsCompleted += 1;
        }
        if (this.profile.personal.document_type_id) {
          fieldsCompleted += 1;
        }
        if (this.profile.personal.document_id) {
          fieldsCompleted += 1;
        }
      }
      const percentage = (fieldsCompleted * 100) / totalFields;
      return Math.round(percentage);
    },
  },
});
