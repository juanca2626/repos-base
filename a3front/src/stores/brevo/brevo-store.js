import { defineStore } from 'pinia';
// import { handleError } from '@/utils/errorHandling';

import { searchData, sendNotification } from '@service/brevo';

// import { notification } from 'ant-design-vue';

export const useBrevoStore = defineStore({
  id: 'brevo',
  state: () => ({
    // loading
    loading: false,
    notifications: [],
  }),
  getters: {
    isLoaded: (state) => state.loaded,
    isLoading: (state) => state.loading,
    getNotifications: (state) =>
      state.notifications.slice().sort((a, b) => new Date(b.date) - new Date(a.date)),
  },
  actions: {
    inited() {
      this.loading = true;
    },
    finished() {
      this.loading = false;
    },
    clearNotifications() {
      this.notifications = [];
    },
    searchNotifications(params) {
      this.loading = true;
      return searchData(params)
        .then(({ data }) => {
          this.notifications = data;
          this.loading = false;
        })
        .catch((error) => {
          console.log(error);
          this.loading = false;
        });
    },
    putNotification(params) {
      this.loading = true;
      return sendNotification(params)
        .then(({ data }) => {
          console.log(data);
          this.loading = false;
        })
        .catch((error) => {
          console.log(error);
          this.loading = false;
        });
    },
  },
});
