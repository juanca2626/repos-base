import { defineStore } from 'pinia';
export const useProfilesStore = defineStore({
  id: 'profiles',
  state: () => ({
    profiles: [],
  }),
  actions: {
    setProfiles(langs) {
      this.profiles = langs;
    },
  },
});
