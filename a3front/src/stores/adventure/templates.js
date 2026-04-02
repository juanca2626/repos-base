import moment from 'moment';
import { defineStore } from 'pinia';
import {
  fetchTemplates,
  fetchTemplate,
  fetchTemplateServices,
  fetchTemplateCashService,
  saveTemplate,
  updateTemplate,
  deleteTemplate,
  cloneTemplate,
  saveTemplateService,
  updateTemplateService,
  deleteTemplateService,
  updateServiceProviders,
  fetchTemplateServicesGrouped,
  fetchTemplateBreakpoints,
} from '@service/adventure';
import {
  createTemplateAdapter,
  createPaginationAdapter,
  createTemplateServiceAdapter,
  createBreakpointAdapter,
} from './adapters';

export const useTemplatesStore = defineStore({
  id: 'templates',
  state: () => ({
    loading: false,
    template: {
      durationDays: 1,
      durationType: 'fullDay',
      serviceCode: '',
      name: '',
      startDate: '',
      endDate: '',
      percentOpe: 0,
      paxs: 1,
      description: '',
      itinerary: '',
      restrictions: '',
      type: 'private',
      images: [],
    },
    templateClone: {
      name: '',
    },
    templates: [],
    pagination: {},
    services: [],
    cash: [],
    extraCash: [],
    error: '',
    types: [
      { label: 'Todos', value: 'all' },
      { label: 'Privado', value: 'private' },
      { label: 'Compartido', value: 'shared' },
    ],
    serviceTypes: [
      { label: 'Costo por Persona', value: 'costPerPerson' },
      { label: 'Tarifa por Día', value: 'ratePerDay' },
      { label: 'Rango', value: 'range' },
    ],
    durations: [
      { label: 'Todos', value: 'all' },
      { label: 'Full-Days', value: 'fullDay' },
      { label: 'Días / Noches', value: 'multiDay' },
    ],
    filtersCash: {
      quantity: 3,
    },
    filters: {
      type: 'all',
      duration: 'all',
      term: '',
    },
    newService: {
      templateId: '',
      categoryId: '',
      name: '',
      type: 'costPerPerson',
      costPerPerson: 0,
      ratePerDay: 0,
      provider: '',
      paymentType: 'credit',
      currency: 'USD',
      position: 0,
      isExtra: false,
      isProgrammable: false,
      isTicket: false,
      multiProviders: false,
      user: '',
      allDays: false,
      days: [1],
      pricing: [
        {
          pax: 0,
          value: 0,
        },
      ],
      locked: false,
      breakpoints: [],
    },
  }),
  getters: {
    isLoading: (state) => state.loading,
    getTemplates: (state) => state.templates,
    getPagination: (state) => state.pagination,
    getServices: (state) => state.services,
    getCash: (state) => state.cash,
    getExtraCash: (state) => state.extraCash,
    getTypes: (state) => state.types,
    getDurations: (state) => state.durations,
    getError: (state) => state.error,
    getBreakpoints: (state) => state.breakpoints,
  },
  actions: {
    fetchAll() {
      this.loading = true;
      this.error = '';
      const params = {
        page: this.pagination.current,
        limit: this.pagination.pageSize,
        type: this.filters.type,
        duration: this.filters.duration,
        name: this.filters.term,
      };
      return fetchTemplates(params)
        .then(({ data }) => {
          if (data.success) {
            this.templates = data.data.map((template) => createTemplateAdapter(template));
            this.pagination = createPaginationAdapter(data.meta);
          }
          this.loading = false;
        })
        .catch((err) => {
          this.error = err.data.message;
          this.loading = false;
        });
    },
    fetchOne(id) {
      this.template = {};
      this.loading = true;
      this.error = '';
      return fetchTemplate(id)
        .then(({ data }) => {
          if (data.success) {
            this.template = createTemplateAdapter(data.data);
          }
          this.loading = false;
        })
        .catch((err) => {
          this.error = err.data.message;
          this.loading = false;
        });
    },
    fetchServices(type, id) {
      this.loading = true;
      this.error = '';
      this.services = [];
      return fetchTemplateServices(type, id)
        .then(({ data }) => {
          if (data.success) {
            this.services = (data.data ?? []).map((service) =>
              createTemplateServiceAdapter(service)
            );
          }
          this.loading = false;
        })
        .catch((err) => {
          this.error = err.data.message;
          this.loading = false;
        });
    },
    fetchServicesGrouped(id) {
      this.loading = true;
      this.error = '';
      this.services = [];
      return fetchTemplateServicesGrouped(id)
        .then(({ data }) => {
          if (data.success) {
            this.services = data.data;
            /*
            this.services = (data.data || []).map((service) =>
              createTemplateServiceAdapter(service)
            );
            */
          }
          this.loading = false;
        })
        .catch((err) => {
          this.error = err.data.message;
          this.loading = false;
        });
    },
    fetchBreakpoints() {
      this.loading = true;
      this.error = '';
      this.breakpoints = [];
      return fetchTemplateBreakpoints(this.template._id)
        .then(({ data }) => {
          if (data.success) {
            this.breakpoints = Object.values(data.data.summary.costs).map((breakpoint, index) =>
              createBreakpointAdapter(breakpoint, index)
            );
          }
          this.loading = false;
        })
        .catch((err) => {
          this.error = err.data.message;
          this.loading = false;
        });
    },
    costsAsObject(totalPorPaxCosts) {
      return totalPorPaxCosts.reduce((acc, cost, index) => {
        const key = (index + 1).toString();
        const value = cost || cost === 0 ? cost.toString() : '0';
        acc[key] = value;
        return acc;
      }, {});
    },
    totalSumOfTargetKey(data, targetKey) {
      const targetIndex = Number(targetKey);

      return data.reduce((accumulator, currentArray) => {
        const costValue = currentArray[targetIndex];

        if (costValue !== undefined && !isNaN(Number(costValue))) {
          return accumulator + Number(costValue);
        }

        return accumulator;
      }, 0);
    },
    fetchCashService(id) {
      this.loading = true;
      this.error = '';
      this.cash = [];
      return fetchTemplateCashService(id, this.filtersCash.quantity)
        .then(({ data }) => {
          if (data.success) {
            this.cash = data.data?.services || [];
            this.extraCash = data.data?.extraServices || [];

            const summaryCosts = data.data?.summary?.costs || {};

            if (summaryCosts) {
              const summaryCostsArray = Object.values(summaryCosts);

              const totalPorPaxCosts = summaryCostsArray.map((cost) => cost.costPerPax);
              const totalWithOpeCosts = summaryCostsArray.map((cost) => cost.totalWithOpe);

              this.cash.push({
                name: '<b class="d-block text-right">TOTAL POR PAX</b>',
                costs: this.costsAsObject(totalPorPaxCosts),
              });

              this.cash.push({
                name: `<b class="d-block text-right">+${parseFloat(data.data.summary.percentOpe).toFixed(2)}% OPE</b>`,
                costs: this.costsAsObject(totalWithOpeCosts),
              });

              if (this.extraCash.length > 0) {
                const summaryExtraCosts = data.data?.extraServices.map((item) => {
                  return Object.values(item.costs);
                });

                console.log(summaryExtraCosts);

                const subtotalExtraCosts = [];
                const totalExtraCosts = [];

                for (let index = 0; index < totalWithOpeCosts.length; index++) {
                  console.log(summaryExtraCosts[index]);
                  const total = this.totalSumOfTargetKey(summaryExtraCosts, index);

                  subtotalExtraCosts.push(total);
                  totalExtraCosts.push(total + totalWithOpeCosts[index]);
                }

                this.extraCash.push({
                  name: '<b class="d-block text-right">SUBTOTAL</b><small class="d-block text-right">SERVICIOS ADICIONALES</small>',
                  costs: this.costsAsObject(subtotalExtraCosts),
                });

                this.extraCash.push({
                  name: '<b class="d-block text-right">TOTAL</b>',
                  costs: this.costsAsObject(totalExtraCosts),
                });
              }
            }
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
        type: this.template.type,
        durationType: this.template.durationType,
        durationDays: this.template.durationDays,
        serviceCode: this.template.serviceCode,
        name: this.template.name,
        effectiveStartDate: moment(this.template.startDate).format('DD/MM/YYYY'),
        effectiveEndDate: moment(this.template.endDate).format('DD/MM/YYYY'),
        percentOpe: this.template.percentOpe,
        paxs: this.template.paxs,
        description: this.template.description,
        itinerary: this.template.itinerary,
        restrictions: this.template.restrictions,
        images: this.template.images,
      };
      return saveTemplate(params)
        .then(({ data }) => {
          if (data.success) {
            this.template = data.data;
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
        type: this.template.type,
        durationType: this.template.durationType,
        durationDays: this.template.durationDays,
        serviceCode: this.template.serviceCode,
        name: this.template.name,
        effectiveStartDate: moment(this.template.startDate).format(
          'ddd MMM DD YYYY HH:mm:ss [GMT]ZZ'
        ),
        effectiveEndDate: moment(this.template.endDate).format('ddd MMM DD YYYY HH:mm:ss [GMT]ZZ'),
        percentOpe: this.template.percentOpe,
        paxs: this.template.paxs,
        description: this.template.description,
        itinerary: this.template.itinerary,
        restrictions: this.template.restrictions,
        images: this.template.images,
      };
      return updateTemplate(id, params)
        .then(({ data }) => {
          if (data.success) {
            this.template = createTemplateAdapter(data.data);
          }
          this.loading = false;
        })
        .catch((err) => {
          this.error = err.data.message;
          this.loading = false;
        });
    },
    saveBreakpoint(breakpoint) {
      this.loading = true;
      this.error = '';
      const params = {
        breakEvenPoint: {
          pax: breakpoint.pax,
          totalCost: breakpoint.cost,
        },
      };
      return updateTemplate(this.template._id, params)
        .then(({ data }) => {
          if (data.success) {
            this.template = data.data;
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
      return deleteTemplate(id)
        .then(({ data }) => {
          if (data.success) {
            this.template = data.data;
          }
          this.loading = false;
        })
        .catch((err) => {
          this.error = err.data.message;
          this.loading = false;
        });
    },
    clone() {
      this.loading = true;
      this.error = '';
      const params = {
        name: this.templateClone.name,
      };
      return cloneTemplate(this.templateClone.id, params)
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
    saveService() {
      this.loading = true;
      this.error = '';
      const params = {
        templateId: this.newService.templateId,
        categoryId: this.newService.categoryId,
        name: this.newService.name,
        type: this.newService.type,
        providers: this.newService.providers,
        paymentType: this.newService.paymentType,
        currency: this.newService.currency,
        position: this.newService.position,
        pricing: this.newService.pricing,
        isProgrammable: this.newService.isProgrammable,
        isTicket: this.newService.isTicket,
        multiProviders: this.newService.multiProviders,
        user: this.newService.user || 'USR001',
        days: this.newService.days,
      };
      return saveTemplateService(params)
        .then(({ data }) => {
          if (!data.success) {
            this.error = data.message;
          }
          this.loading = false;
        })
        .catch((err) => {
          this.error = err.data.message?.[0] ?? err.data.message;
          this.loading = false;
        });
    },
    updateService(id) {
      this.loading = true;
      this.error = '';
      const params = {
        templateId: this.newService.templateId,
        categoryId: this.newService.categoryId,
        name: this.newService.name,
        type: this.newService.type,
        providers: this.newService.providers,
        paymentType: this.newService.paymentType,
        currency: this.newService.currency,
        position: this.newService.position,
        pricing: this.newService.pricing,
        isProgrammable: this.newService.isProgrammable,
        isTicket: this.newService.isTicket,
        multiProviders: this.newService.multiProviders,
        user: this.newService.user || 'USR001',
        days: this.newService.days,
      };
      return updateTemplateService(id, params)
        .then(({ data }) => {
          if (!data.success) {
            this.error = data.message;
          }
          this.loading = false;
        })
        .catch((err) => {
          this.error = err.data.message?.[0] ?? err.data.message;
          this.loading = false;
        });
    },
    deleteService(id) {
      this.loading = true;
      this.error = '';
      return deleteTemplateService(id)
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
    updateProviders(id) {
      this.loading = true;
      this.error = '';
      const params = {
        providerScaling: this.newService.providerScaling.map((item) => {
          return {
            pax: item.pax,
            providers: item.providers,
          };
        }),
      };
      return updateServiceProviders(id, params)
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
