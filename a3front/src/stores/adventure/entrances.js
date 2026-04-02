import { defineStore } from 'pinia';
import {
  fetchEntrances,
  fetchService,
  saveEntrance,
  updateStatus,
  reserveEntrance,
  downloadEntrance,
  sendEntrance,
} from '@service/adventure';
import { createPaginationAdapter, createEntranceAdapter } from './adapters';

export const useEntrancesStore = defineStore({
  id: 'entrances',
  state: () => ({
    loading: false,
    entrance: {},
    entrances: [],
    error: '',
    states: [
      { label: 'Sin Reserva', value: 'NO_BOOKING' },
      { label: 'Pendiente de Pago', value: 'PENDING_PAYMENT' },
      { label: 'Recibos con Asientos (Contabilidad)', value: 'ACCOUNTING_CONFIRMED' },
      { label: 'Pagados (Tesorería)', value: 'PAID' },
      { label: 'Eliminados', value: 'CANCELED' },
    ],
    filters: {
      status: 'NO_BOOKING',
      term: '',
    },
    pagination: {},
  }),
  getters: {
    isLoading: (state) => state.loading,
    getEntrances: (state) => state.entrances,
    getError: (state) => state.error,
  },
  actions: {
    fetchAll() {
      this.loading = true;
      this.error = '';
      const params = {
        page: this.pagination.current,
        limit: this.pagination.pageSize,
        status: this.filters.status,
        term: this.filters.term,
      };
      this.entrances = [];
      return fetchEntrances(params)
        .then(({ data }) => {
          if (data.success) {
            this.entrances = (data.data ?? []).map((ticket) => createEntranceAdapter(ticket));
            this.pagination = createPaginationAdapter(data.meta);
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
      return fetchService(id, params)
        .then(({ data }) => {
          if (data.success) {
            this.entrace = data.data;
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
      const params = {};
      return saveEntrance(params)
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
    changeStatus(id, params) {
      this.loading = true;
      this.error = '';
      return updateStatus(id, params)
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
    reserve(id, params) {
      this.loading = true;
      this.error = '';
      return reserveEntrance(id, params)
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
    download(record) {
      this.error = '';
      return downloadEntrance(record._id)
        .then((response) => {
          if (response.success) {
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', `${record.opeFile}.xlsx`);
            document.body.appendChild(link);
            link.click();
            link.remove();
            window.URL.revokeObjectURL(url);
          }
        })
        .catch((err) => {
          this.error = err.message || 'Error al descargar el archivo';
        });
    },
    send(id, params) {
      this.loading = true;
      this.error = '';
      return sendEntrance(id, params)
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
