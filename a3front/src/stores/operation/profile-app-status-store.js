import { defineStore } from 'pinia';

export const useProfileAppStatusStore = defineStore({
  id: 'profileAppStatus',
  state: () => ({
    //uploading: false,
    saving: false,
    loading: false,
  }),
  actions: {
    setSaving(value) {
      this.saving = value;
    },
    setLoading(value) {
      this.loading = value;
    },
  },
});
