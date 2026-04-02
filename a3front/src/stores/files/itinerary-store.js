import { defineStore } from 'pinia';
import {
  fetchItinerary,
  fetchQuotation,
  updateSchedule,
  updateDate,
  addFileItinerary,
  updateFileItinerary,
  addFileItineraryPublic,
  updateFileItineraryPublic,
} from '@service/files';

import { normalizeItineries } from '@store/files/adapters';

export const useItineraryStore = defineStore({
  id: 'itinerary',
  state: () => ({
    loading: false,
    quotation: [],
    itinerary: {
      isLoading: false,
    },
    newId: '',
  }),
  getters: {
    isLoading: (state) => state.loading,
    isLoadingAsync: (state) => state.loading_async,
    getItinerary: (state) => state.itinerary,
    getQuotation: (state) => state.quotation,
    getNewId: (state) => state.newId,
  },
  actions: {
    inited() {
      this.loading = true;
    },
    initedAsync() {
      this.loading_async = true;
    },
    finished() {
      this.loading_async = false;
    },
    removeItinerary() {
      this.itinerary = false;
    },
    getQuotationById({ itineraryId }) {
      this.quotation = [];
      this.loading = true;
      return fetchQuotation({ itineraryId })
        .then(({ data }) => {
          if (data.success) {
            this.quotation = data.data;
          }
          this.loading = false;
        })
        .catch((error) => {
          console.log(error);
          this.loading = false;
        });
    },
    async getById({ fileId, itineraryId }) {
      this.itinerary = {};
      await this.fetchItineraryWithRetry({ fileId, itineraryId });
    },
    groupServicesByDay(data) {
      const grouped = {};

      if (data.services) {
        data.services.forEach((service) => {
          const date = service.date_in;
          if (!grouped[date]) {
            grouped[date] = [];
          }
          grouped[date].push(service);
        });
      }

      return Object.keys(grouped).map((date) => ({
        date_in: date,
        services: grouped[date],
      }));
    },
    async fetchItineraryWithRetry({ fileId, itineraryId }, maxRetries = 3) {
      let retries = 0;

      while (retries < maxRetries) {
        try {
          const { data } = await fetchItinerary({ fileId, itineraryId });
          const newItinerary = normalizeItineries([data.data]);
          this.itinerary = newItinerary[0];
          this.itinerary.days = this.groupServicesByDay(this.itinerary);
          return; // Éxito, salimos
        } catch (error) {
          console.log(
            `Error al obtener itinerario ${itineraryId} (intento ${retries + 1}):`,
            error
          );

          if ([401].includes(error.code)) {
            removeCookiesCross();
            window.location.href = window.url_app;
            return;
          }

          if ([500, 404].includes(error.code)) {
            break; // No seguir intentando
          }

          retries++;
          await new Promise((res) => setTimeout(res, 500)); // Espera entre reintentos
        }
      }

      this.itinerary = {}; // Si todos fallan, limpiar
    },
    putUpdateSchedule(data) {
      this.loading = true;
      return updateSchedule(data)
        .then(() => {
          this.loading = false;
        })
        .catch((error) => {
          console.log(error);
          this.loading = false;
        });
    },
    putUpdateDate({ type = 'flight', fileId, itineraryId, serviceId = 0, date }) {
      this.loading = true;
      return updateDate({ type, fileId, itineraryId, serviceId, date })
        .then(() => {
          this.loading = false;
        })
        .catch((error) => {
          console.log(error);
          this.loading = false;
        });
    },
    add({ fileId, data }) {
      this.loading = true;
      return addFileItinerary({ fileId, data })
        .then(({ data }) => {
          console.log(data);
          console.log(data.id);
          console.log(data.data.id);
          this.newId = data.data.id;
          this.loading = false;
        })
        .catch((error) => {
          console.log(error);
          this.loading = false;
        });
    },
    update({ fileId, fileItineraryId, data }) {
      this.loading = true;
      return updateFileItinerary({ fileId, fileItineraryId, data })
        .then(({ data }) => {
          console.log(data);
          this.loading = false;
        })
        .catch((error) => {
          console.log(error);
          this.loading = false;
        });
    },

    addPublic({ fileId, data }) {
      this.loading = true;
      return addFileItineraryPublic({ fileId, data })
        .then(({ data }) => {
          console.log(data);
          console.log(data.id);
          console.log(data.data.id);
          this.newId = data.data.id;
          this.loading = false;
        })
        .catch((error) => {
          console.log(error);
          this.loading = false;
        });
    },
    updatePublic({ fileId, fileItineraryId, data }) {
      this.loading = true;
      return updateFileItineraryPublic({ fileId, fileItineraryId, data })
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
