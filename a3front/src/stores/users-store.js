import { defineStore } from 'pinia';
import { useStorage } from '@vueuse/core';

export const useUserStore = defineStore({
  id: 'users',
  state: () => ({
    users: [],
  }),
  actions: {
    setUsers(users) {
      this.users = users;
    },
    setRoutes(records) {
      useStorage('mesas', records);
    },
  },
  getters: {
    routes: () => {
      return [1, 3, 4];
    },
  },
});
