import { defineStore } from 'pinia';
import { fetchClients, filterClients } from '@service/files';

import { createClientAdapter } from '@store/files/adapters';

export const useClientsStore = defineStore({
  id: 'clients',
  state: () => ({
    loading: false,
    clients: [],
  }),
  getters: {
    isLoading: (state) => state.loading,
    getClients: (state) => state.clients,
  },
  actions: {
    fetchAll(search = '') {
      this.loading = true;
      this.clients = [];
      return fetchClients(search)
        .then(({ data }) => {
          this.clients = data.data.all_clients.map((client) => createClientAdapter(client));
          this.loading = false;
        })
        .catch((error) => {
          console.log(error);
          this.loading = false;
        });
    },
    filterAll(search = '') {
      this.loading = true;
      this.clients = [];
      return filterClients(search)
        .then(({ data }) => {
          this.clients = data.data.map((client) => {
            return {
              code: client.client_code.toString(),
              value: client.code.toString(),
              label: client.label.toString(),
            };
          });
          this.loading = false;
        })
        .catch((error) => {
          console.log(error);
          this.loading = false;
        });
    },
  },
});
