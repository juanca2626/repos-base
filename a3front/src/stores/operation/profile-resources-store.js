import { defineStore } from 'pinia';
import ServicesProvidersResources from '@service/operations/ServicesProvidersResources';

export const useProfileResourcesStore = defineStore({
  id: 'profile-resources',
  state: () => ({
    campus: [],
    languages: [],
    profileTypes: [],
    specialties: [],
    servicesProvidersResources: new ServicesProvidersResources(),
  }),
  actions: {
    async getResources() {
      const [campus, languages, profileTypes, specialties] = await Promise.all([
        this.servicesProvidersResources.getCampus(),
        this.servicesProvidersResources.getLanguages(),
        this.servicesProvidersResources.getProfileTypes(),
        this.servicesProvidersResources.getSpecialties(),
      ]);
      this.campus = campus;
      this.languages = languages;
      this.profileTypes = profileTypes;
      this.specialties = specialties;
    },
    async getCampus() {
      const res = await this.servicesProvidersResources.getCampus();
      this.campus = res;
      return res;
    },
    async getLanguages() {
      const res = await this.servicesProvidersResources.getLanguages();
      this.languages = res;
      return res;
    },
    async getProfileTypes() {
      const res = await this.servicesProvidersResources.getProfileTypes();
      this.profileTypes = res;
      return res;
    },
    async getSpecialties() {
      const res = await this.servicesProvidersResources.getSpecialties();
      this.specialties = res;
      return res;
    },
    setLanguages(languages) {
      this.languages = languages;
    },
    setCampus(campus) {
      this.campus = campus;
    },
  },
});
