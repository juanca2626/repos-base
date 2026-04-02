import { defineStore } from 'pinia';
import {
  fetchEffective,
  updateEffectiveStatus,
  updateEffectiveStatusItem,
  save,
  validate,
} from '@service/adventure';
import { createEffectiveAdapter, createPaginationAdapter } from './adapters';

export const useEffectiveStore = defineStore({
  id: 'effective',
  state: () => ({
    loading: false,
    cash: {},
    effective: [],
    error: '',
    filters: {
      status: 'ALL',
      term: '',
    },
    rucs: [],
    pagination: {},
    states: [
      { value: 'ALL', label: 'Todos' },
      { value: 'DELIVERED', label: 'Sin Rendir' },
      { value: 'ACCOUNTED', label: 'Rendidos - Sin validación' },
      { value: 'VALIDATED', label: 'Rendidos - Validados' },
      { value: 'LIQUIDATED', label: 'Liquidados' },
    ],
  }),
  getters: {
    isLoading: (state) => state.loading,
    getEffective: (state) => state.effective,
    getCash: (state) => state.cash,
  },
  actions: {
    fetchAll() {
      this.loading = true;
      this.error = '';
      this.effective = [];

      const params = {
        status: this.filters.status,
        search: this.filters.term,
        page: this.pagination.current,
        limit: this.pagination.pageSize,
      };
      return fetchEffective(params)
        .then(({ data }) => {
          if (data.success) {
            this.effective = (data.data ?? []).map((effective) =>
              createEffectiveAdapter(effective)
            );
            this.pagination = createPaginationAdapter(data.meta);
          }
          this.loading = false;
        })
        .catch((err) => {
          console.log(err);
          this.error = err.data.message;
          this.loading = false;
        });
    },
    updateStatusItem(payload) {
      this.loading = true;
      return updateEffectiveStatusItem(this.cash._id, payload)
        .then(({ data }) => {
          console.log('DATA: ', data);
          this.loading = false;
        })
        .catch((err) => {
          this.loading = false;
          this.error = err.data.message;
        });
    },
    updateStatus(item_id, report_id, type) {
      this.loading = true;
      this.error = '';
      return updateEffectiveStatus(this.cash._id, item_id, report_id, type)
        .then(({ data }) => {
          console.log('DATA: ', data);
          this.loading = false;
        })
        .catch((err) => {
          console.log(err);
          this.loading = false;
          this.error = err.data.message;
        });
    },
    saveDocuments(effective_id, item_id, payload) {
      this.loading = true;
      this.error = '';
      return save(effective_id, item_id, payload)
        .then(({ data }) => {
          if (data.success) {
            const newCash = {
              ...this.cash,
              ...data.data,
            };

            this.cash = createEffectiveAdapter(newCash);
          }
          this.loading = false;
        })
        .catch((err) => {
          console.log(err);
          this.loading = false;
          this.error = err.data.message;
        });
    },
    validateRuc(ruc) {
      this.loading = true;
      this.rucs = [];
      return validate(ruc)
        .then(({ data }) => {
          console.log('DATA: ', data);
          this.loading = false;
          if (data.success) {
            this.rucs = data.data.map((ruc) => {
              return {
                value: ruc.cuit,
                label: ruc.razon,
              };
            });
          }
        })
        .catch((err) => {
          console.log('ERROR', err);
          this.loading = false;
          this.error = err.data.message;
        });
    },
  },
});
