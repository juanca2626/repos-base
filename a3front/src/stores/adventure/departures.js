import moment from 'moment';
import { defineStore } from 'pinia';
import {
  fetchDepartures,
  exportDepartures,
  fetchCalendarDepartures,
  fetchDeparture,
  saveDeparture,
  updateDeparture,
  deleteDeparture,
  deactivateDeparture,
  fetchPaxsByFile,
  fetchGuides,
  savePaxsToDeparture,
  deletePaxToDeparture,
  fetchCashServices,
  generateCashServices,
  fetchDepartureServices,
  fetchDepartureTemplateServices,
  closeFile,
  deleteCloseProcess,
  hideService,
  closeService,
  updateRealCost,
  updateTemplateServiceProviders,
  resendNotification,
  updateCash,
} from '@service/adventure';
import {
  createPaginationAdapter,
  createDepartureAdapter,
  createPaxAdapter,
  createGuideAdapter,
  createCashAdapter,
  createCalendarDepartureAdapter,
  createServiceAdapter,
  createDepartureTemplateServiceAdapter,
} from './adapters';

export const useDeparturesStore = defineStore({
  id: 'departures',
  state: () => ({
    loading: false,
    departure: {
      _id: '',
      template_id: '',
      date: '',
      showAlert: false,
      closedAt: null,
    },
    departures: [],
    error: '',
    types: [
      { label: 'Por Fecha', value: 'date' },
      { label: 'Por File', value: 'file' },
    ],
    file_types: [
      { label: 'Todos', value: '' },
      { label: 'Cobro al Crédito', value: 'credit' },
      { label: 'Cobro al Contado', value: 'cash' },
    ],
    file_options: [
      { label: 'Ocultar', value: 'hide' },
      { label: 'Visualizar', value: 'show' },
    ],
    file_filters: {
      type: '',
      option: 'show',
    },
    filters: {
      type: 'date',
      date_from: moment().format('YYYY-MM-DD'),
      date_to: moment().add(1, 'month').format('YYYY-MM-DD'),
      file: '',
      term: '',
    },
    status: [
      { label: 'REQUERIDO', value: 'REQUESTED' },
      { label: 'APROBADO (CONTABILIDAD)', value: 'APPROVED' },
      { label: 'CAJA CHICA (CONTABILIDAD)', value: 'PETTY_CASH' },
      { label: 'EFECTIVO ENTREGADO (SIN RENDIR)', value: 'DELIVERED' },
      { label: 'RENDIDO', value: 'ACCOUNTED' },
      { label: 'RENDICIÓN VALIDADA', value: 'VALIDATED' },
      { label: 'RECHAZADO', value: 'REJECTED' },
      { label: 'ANULADO', value: 'CANCELLED' },
    ],
    pagination: {},
    paxsByFile: [],
    guides: [],
    cash: {},
    cashExtra: {},
    services: [],
    template_extra_services: [],
    template_services: [],
    template_summary: {},
  }),
  getters: {
    isLoading: (state) => state.loading,
    getDepartures: (state) => state.departures,
    getCalendarDepartures: (state) => state.calendarDepartures,
    getError: (state) => state.error,
    getPaxsByFile: (state) => state.paxsByFile,
    getGuides: (state) => state.guides,
    // Cash..
    getCash: (state) => state.cash,
    getCashExtra: (state) => state.cashExtra,
    getTotalCash: (state) => state.totalCash,
    getTotalCashExtra: (state) => state.totalCashExtra,
    getTypeCash: (state) => state.typeCash,
    getTypeCashExtra: (state) => state.typeCashExtra,
    getServices: (state) => state.services,
    getTemplateServices: (state) => state.template_services,
    getTemplateSummary: (state) => state.template_summary,
  },
  actions: {
    clearFilters() {
      this.pagination = {
        current: 1,
        pageSize: 10,
      };
      this.filters = {
        type: 'date',
        date_from: moment().format('YYYY-MM-DD'),
        date_to: moment().add(1, 'month').format('YYYY-MM-DD'),
        file: '',
        term: '',
      };
    },
    getStatus(status) {
      return (
        this.status.filter((state) => state.value === status)?.[0]?.label || 'Estado desconocido'
      );
    },
    fetchCalendarAll(year, month) {
      this.loading = true;
      this.error = '';
      const params = {
        year,
        month,
      };
      return fetchCalendarDepartures(params)
        .then(({ data }) => {
          if (data.success) {
            this.calendarDepartures = (data.data ?? []).map((departure) =>
              createCalendarDepartureAdapter(departure)
            );
          }
          this.loading = false;
        })
        .catch((err) => {
          this.error = err.data.message;
          this.loading = false;
        });
    },
    fetchAll() {
      this.loading = true;
      this.error = '';
      const params = {
        page: this.pagination.current,
        limit: this.pagination.pageSize,
        type: this.filters.type,
        opeFile: this.filters.file,
        // term: this.filters.term,
      };

      if (this.filters.type === 'date') {
        params.startDate = this.filters.date_from;
        params.endDate = this.filters.date_to;
      }

      return fetchDepartures(params)
        .then(({ data }) => {
          if (data.success) {
            this.departures = (data.data ?? []).map((departure) =>
              createDepartureAdapter(departure)
            );
            this.pagination = createPaginationAdapter(data.meta);
          }
          this.loading = false;
        })
        .catch((err) => {
          this.error = err.data.message;
          this.loading = false;
        });
    },
    exportAll() {
      this.loading = true;
      this.error = '';
      const params = {
        type: this.filters.type,
        opeFile: this.filters.file,
      };

      if (this.filters.type === 'date') {
        params.startDate = this.filters.date_from;
        params.endDate = this.filters.date_to;
      }

      return exportDepartures(params)
        .then((response) => {
          // Importante: El binario suele venir en 'response.data'
          const blob = new Blob([response.data], {
            type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
          });

          // Creamos un link temporal en el DOM
          const url = window.URL.createObjectURL(blob);
          const link = document.createElement('a');
          link.href = url;

          // Nombre del archivo (puedes dinamizarlo)
          link.setAttribute('download', `reporte-salidas-${Date.now()}.xlsx`);

          document.body.appendChild(link);
          link.click();

          // Limpieza
          link.remove();
          window.URL.revokeObjectURL(url);

          this.loading = false;
        })
        .catch((err) => {
          // Ojo: Si la respuesta es un blob, el error a veces viene encapsulado
          this.error = err.response?.data?.message || 'Error al exportar';
          this.loading = false;
        });
    },
    fetchTemplateServices() {
      this.loading = true;
      this.error = '';
      this.template_extra_services = [];
      this.template_services = [];
      this.template_summary = {
        exchangeRate: 0,
        totalCalculatedCostPackage: 0,
        totalCalculatedCostPerPax: 0,
        totalRealCostPackage: 0,
      };
      return fetchDepartureTemplateServices(this.departure._id)
        .then(({ data }) => {
          if (data.success) {
            this.template_extra_services = (data.data.extraServices ?? []).map((extraService) =>
              createDepartureTemplateServiceAdapter(extraService)
            );
            this.template_services = (data.data.services ?? []).map((service) =>
              createDepartureTemplateServiceAdapter(service)
            );
            this.template_summary = data.data.summary;
          }
          this.loading = false;
        })
        .catch((err) => {
          this.error = err.data.message;
          this.loading = false;
        });
    },
    fetchServices() {
      this.loading = true;
      this.error = '';
      this.services = [];
      return fetchDepartureServices(this.departure._id)
        .then(({ data }) => {
          if (data.success) {
            this.services = (data.data ?? []).map((service) => createServiceAdapter(service));
          }
          this.loading = false;
        })
        .catch((err) => {
          console.log('ERROR:', err);
          this.error = err.data.message;
          this.loading = false;
        });
    },
    fetchDeparture(id) {
      this.loading = true;
      this.error = '';
      this.departure = {};
      return fetchDeparture(id)
        .then(({ data }) => {
          if (data.success) {
            this.departure = createDepartureAdapter(data.data);
          }
          this.loading = false;
        })
        .catch((err) => {
          this.error = err.data.message;
          this.loading = false;
        });
    },
    fetchPaxsByFile(file) {
      this.loading = true;
      this.error = '';
      this.paxsByFile = [];
      return fetchPaxsByFile(file)
        .then(({ data }) => {
          if (data.success) {
            this.paxsByFile = data.data.map((pax) => {
              pax._id = `${file}-${pax.documentNumber}-${pax.id || pax.nrosec}`;
              return createPaxAdapter(file, pax);
            });
          }
          this.loading = false;
        })
        .catch((err) => {
          this.error = err.data.message;
          this.loading = false;
        });
    },
    fetchGuides(search, types = '') {
      this.loading = true;
      this.error = '';
      this.guides = [];
      const params = {
        term: search,
      };
      return fetchGuides(params, types)
        .then(({ data }) => {
          if (data.success) {
            this.guides = data.data.map((guide) => createGuideAdapter(guide));
          }
          this.loading = false;
        })
        .catch((err) => {
          this.error = err.data.message;
          this.loading = false;
        });
    },
    fetchCashDeparture() {
      this.loading = true;
      this.error = '';
      this.cash = {};
      const params = {
        type: 'regular',
      };
      return fetchCashServices(this.departure._id, params)
        .then(({ data }) => {
          if (data.success) {
            const cashGeneral = data.data;
            const items = (cashGeneral.items ?? []).map((cash) => createCashAdapter(cash));
            this.cash = {
              ...cashGeneral,
              items,
            };
          }
          this.loading = false;
        })
        .catch((err) => {
          this.error = err.data.message;
          this.loading = false;
        });
    },
    fetchCashExtraDeparture() {
      this.loading = true;
      this.error = '';
      this.cashExtra = {};
      const params = {
        type: 'extra',
      };
      return fetchCashServices(this.departure._id, params)
        .then(({ data }) => {
          if (data.success) {
            const cashGeneral = data.data;
            const items = (cashGeneral.items ?? []).map((cash) => createCashAdapter(cash));
            this.cashExtra = {
              ...cashGeneral,
              items,
            };
          }
          this.loading = false;
        })
        .catch((err) => {
          this.error = err.data.message;
          this.loading = false;
        });
    },
    generateCashDeparture(type) {
      this.loading = true;
      this.error = '';
      return generateCashServices(this.departure._id, type)
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
    savePaxsDeparture(params) {
      this.loading = true;
      this.error = '';
      return savePaxsToDeparture(this.departure._id, params)
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
    deletePaxDeparture(params) {
      this.loading = true;
      this.error = '';
      return deletePaxToDeparture(this.departure._id, params)
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
    saveGuideDeparture() {
      this.loading = true;
      this.error = '';
      const params = {
        guideCode: this.departure.guideCode,
      };
      return updateDeparture(this.departure._id, params)
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
    save() {
      this.loading = true;
      this.error = '';
      const params = {
        templateId: this.departure.template_id,
        startDate: this.departure.date,
      };
      return saveDeparture(params)
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
    update(id) {
      this.loading = true;
      this.error = '';
      const params = {
        guideCode: this.departure.guide_code,
      };
      return updateDeparture(id, params)
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
    remove(id) {
      this.loading = true;
      this.error = '';
      return deleteDeparture(id)
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
    deactivate(id) {
      this.loading = true;
      this.error = '';
      return deactivateDeparture(id)
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
    closeFileDeparture() {
      this.loading = true;
      this.error = '';
      const payload = {
        closedAt: moment(this.departure.closedAt, 'DD/MM/YYYY').format('YYYY-MM-DD'),
      };
      return closeFile(this.departure._id, payload)
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
    deleteCloseProcessDeparture() {
      this.loading = true;
      this.error = '';
      const payload = {
        closedAt: moment().format('YYYY-MM-DD'),
      };
      return deleteCloseProcess(this.departure._id, payload)
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
    hideServiceDeparture(id, isHidden = false) {
      this.loading = true;
      this.error = '';
      const payload = {
        isHidden,
      };
      return hideService(id, payload)
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
    closeServiceDeparture(id, isAccounted) {
      this.loading = true;
      this.error = '';
      const payload = {
        isAccounted,
      };
      return closeService(id, payload)
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
    updateRealCostDeparture(id, realCost) {
      this.loading = true;
      this.error = '';
      const payload = {
        realCost,
      };
      return updateRealCost(id, payload)
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
    updateTemplateServiceProvidersDeparture(id, providers) {
      this.loading = true;
      this.error = '';
      const payload = {
        providers,
      };
      return updateTemplateServiceProviders(id, payload)
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
    updateCashStatus(id, status) {
      this.loading = true;
      this.error = '';
      return updateCash(id, status)
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
    resendNotificationCash(id, status) {
      this.loading = true;
      this.error = '';
      return resendNotification(id, status)
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
