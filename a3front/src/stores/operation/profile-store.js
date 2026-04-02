import { defineStore } from 'pinia';
import ServicesProviders from '@/services/operations/servicesProviders';

export const useProfileStore = defineStore({
  id: 'profile',
  state: () => ({
    profile: {},
    skills: [],
    profiles: [],
    guides: [],
    escorts: [],
    transfers: [],
    servicesProviders: new ServicesProviders(),
  }),
  actions: {
    async createProfile(profile) {
      return await this.servicesProviders.createUserProfile(profile);
    },
    async createProfileOPE(profile) {
      return await this.servicesProviders.createUserProfileOPE(profile);
    },
    async updateProfile(id, profile) {
      return await this.servicesProviders.updateUserProfile(id, profile);
    },
    async listProfiles({ iso, page, code, name, status, profile, language }) {
      return await this.servicesProviders.listProfiles({
        iso,
        page,
        code,
        name,
        status,
        profile,
        language,
      });
    },
    async getProviderDetail(code) {
      return await this.servicesProviders.getProfileInfo(code);
    },
    async uploadFile(file) {
      return await this.servicesProviders.uploadFile(file);
    },
    async getSkills() {
      const res = await this.servicesProviders.getSpecialties();
      this.skills = res;
      return res;
    },
    async getProfiles() {
      const res = await this.servicesProviders.getProfiles();
      this.profiles = res;
      return res;
    },
    async getStats() {
      const res = await this.servicesProviders.getStats();
      this.stats = res;
      return res;
    },
    async activate(code) {
      return await this.servicesProviders.activateProfile(code);
    },
    async deactivate(code) {
      return await this.servicesProviders.deactivateProfile(code);
    },
    setGuides(guides) {
      this.guides = guides;
    },
    setEscorts(escorts) {
      this.escorts = escorts;
    },
    setTransfers(transfers) {
      this.transfers = transfers;
    },
  },
  getters: {
    profileInfo: () => {
      return this.profile;
    },
  },
});
