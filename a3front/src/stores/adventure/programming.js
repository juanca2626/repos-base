import { defineStore } from 'pinia';
import {
  fetchProgramming,
  deactivateProgramming,
  resetProgramming,
  saveProgramming,
  updateProgramming,
  sendOrder,
} from '@service/adventure';
import { createProgrammingAdapter } from './adapters';

export const useProgrammingStore = defineStore({
  id: 'programming',
  state: () => ({
    loading: false,
    programming: {},
    programmings: [],
    pagination: {},
    error: '',
    states: [
      { label: 'Todos', value: 'ALL' },
      { label: 'Sin Programar', value: 'UNPROGRAMMED' },
      { label: 'Programados', value: 'PROGRAMMED' },
      { label: 'Con orden de servicio / Sin monitorear', value: 'WITH_ORDER' },
      { label: 'Monitoreados', value: 'MONITORED' },
    ],
    types: [
      { label: 'Todos', value: 'ALL' },
      { label: 'Fecha', value: 'DATE' },
      { label: 'File', value: 'FILE' },
      { label: 'Pax', value: 'PAX' },
    ],
    filters: {
      state: 'ALL',
      type: 'ALL',
      date_from: '',
      date_to: '',
      term: '',
    },
  }),
  getters: {
    isLoading: (state) => state.loading,
    getProgramming: (state) => state.programming,
    getProgrammings: (state) => state.programmings,
    getPagination: (state) => state.pagination,
    getStates: (state) => state.states,
    getTypes: (state) => state.types,
    getError: (state) => state.error,
  },
  actions: {
    fetchAll() {
      this.loading = true;
      this.error = '';
      let params = {
        page: this.pagination.current,
        limit: this.pagination.pageSize,
        status: this.filters.state,
      };

      if (this.filters.type === 'DATE') {
        if (this.filters.date_from) {
          params = {
            ...params,
            startDate: this.filters.date_from,
          };
        }

        if (this.filters.date_to) {
          params = {
            ...params,
            endDate: this.filters.date_to,
          };
        }
      }

      if (this.filters.type === 'FILE') {
        params = {
          ...params,
          opeFile: this.filters.term,
        };
      }

      if (this.filters.type === 'PAX') {
        params = {
          ...params,
          pax: this.filters.term,
        };
      }

      return fetchProgramming(params)
        .then(({ data }) => {
          if (data.success) {
            this.programmings = (data.data ?? []).map((programming) =>
              createProgrammingAdapter(programming)
            );
          }
          this.loading = false;
        })
        .catch((err) => {
          this.error = err.data.message;
          this.loading = false;
        });
    },
    deactivate(id) {
      this.loading = true;
      this.error = '';
      return deactivateProgramming(id)
        .then(({ data }) => {
          if (!data.success) {
            this.error = data.message;
          }
          this.loading = false;
        })
        .catch((err) => {
          this.error = err.data.message;
          this.loading = false;
        });
    },
    reset(id) {
      this.loading = true;
      this.error = '';
      return resetProgramming(id)
        .then(({ data }) => {
          if (!data.success) {
            this.error = data.message;
          }
          this.loading = false;
        })
        .catch((err) => {
          this.error = err.data.message;
          this.loading = false;
        });
    },
    send(id) {
      this.loading = true;
      this.error = '';
      return sendOrder(id)
        .then(({ data }) => {
          if (!data.success) {
            this.error = data.message;
          }
          this.loading = false;
        })
        .catch((err) => {
          this.error = err.data.message;
          this.loading = false;
        });
    },
    save(params) {
      this.loading = true;
      this.error = '';
      return saveProgramming(params)
        .then(({ data }) => {
          if (!data.success) {
            this.error = data.message;
          }
          this.loading = false;
        })
        .catch((err) => {
          this.error = err.data.message;
          this.loading = false;
        });
    },
    update(params) {
      this.loading = true;
      this.error = '';
      return updateProgramming(this.programming._id, params)
        .then(({ data }) => {
          if (!data.success) {
            this.error = data.message;
          }
          this.loading = false;
        })
        .catch((err) => {
          this.error = err.data.message;
          this.loading = false;
        });
    },
  },
});
