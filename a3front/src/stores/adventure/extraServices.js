import { defineStore } from 'pinia';
import {
  fetchExtraServices,
  fetchExtraService,
  saveExtraService,
  updateExtraService,
  deleteExtraService,
  saveTemplateExtraService,
  saveDepartureExtraService,
} from '@service/adventure';
import { createServiceAdapter } from './adapters';

export const useExtraServicesStore = defineStore({
  id: 'extraServices',
  state: () => ({
    loading: false,
    extraService: {
      service: null,
      paxs: 0,
      days: [],
    },
    filters: {
      type: '',
      term: '',
    },
    types: [
      { label: 'Todos', value: '' },
      { label: 'Costo por Persona', value: 'costPerPerson' },
      { label: 'Tarifa por Día', value: 'ratePerDay' },
      { label: 'Rangos', value: 'range' },
    ],
    extraServices: [],
    error: '',
    pagination: {},
  }),
  getters: {
    isLoading: (state) => state.loading,
    getExtraServices: (state) => state.extraServices,
    getError: (state) => state.error,
  },
  actions: {
    fetchAll() {
      this.loading = true;
      this.error = '';
      const params = {
        name: this.filters.term,
      };

      if (this.filters.type) {
        params.type = this.filters.type;
      }

      return fetchExtraServices(params)
        .then(({ data }) => {
          if (data.success) {
            this.extraServices = (data.data ?? []).map((service) => createServiceAdapter(service));
            // this.pagination = createPaginationAdapter(data.data.pagination);
          }
          this.loading = false;
        })
        .catch((err) => {
          this.error = err.data.message;
          this.loading = false;
        });
    },
    findService(id, params = {}) {
      this.loading = true;
      this.error = '';
      return fetchExtraService(id, params)
        .then(({ data }) => {
          if (data.success) {
            this.extraService = createServiceAdapter(data.data);
          }
          this.loading = false;
        })
        .catch((err) => {
          this.error = err.data.message;
          this.loading = false;
        });
    },
    save() {
      this.loading = true;
      this.error = '';
      const params = {
        ...this.extraService,
      };
      return saveExtraService(params)
        .then(({ data }) => {
          if (!data.success) {
            this.error = data.message || 'Ocurrió un error inesperado.';
          }
          this.loading = false;
        })
        .catch((err) => {
          this.error = err.data.message;
          this.loading = false;
        });
    },
    update(id) {
      this.loading = true;
      this.error = '';
      const params = {
        ...this.extraService,
      };
      return updateExtraService(id, params)
        .then(({ data }) => {
          if (!data.success) {
            this.error = data.message || 'Ocurrió un error inesperado.';
          }
          this.loading = false;
        })
        .catch((err) => {
          this.error = err.data.message;
          this.loading = false;
        });
    },
    remove(id) {
      this.loading = true;
      this.error = '';
      return deleteExtraService(id)
        .then(({ data }) => {
          if (!data.success) {
            this.error = data.message || 'Ocurrió un error inesperado.';
          }
          this.loading = false;
        })
        .catch((err) => {
          this.error = err.data.message;
          this.loading = false;
        });
    },
    saveTemplateService(template_id) {
      this.loading = true;
      this.error = '';
      const params = {
        pax: this.extraService.paxs,
        days: this.extraService.days,
      };
      return saveTemplateExtraService(template_id, this.extraService.service, params)
        .then(({ data }) => {
          if (!data.success) {
            this.error = data.message || 'Ocurrió un error inesperado.';
          }
          this.loading = false;
        })
        .catch((err) => {
          this.error = err.data.message;
          this.loading = false;
        });
    },
    saveDepartureService(departure_id) {
      this.loading = true;
      this.error = '';
      const params = {
        paxQuantity: this.extraService.paxs,
        days: this.extraService.days,
      };
      return saveDepartureExtraService(departure_id, this.extraService.service, params)
        .then(({ data }) => {
          if (!data.success) {
            this.error = data.message || 'Ocurrió un error inesperado.';
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
