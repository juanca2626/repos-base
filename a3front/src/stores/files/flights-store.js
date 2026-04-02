import { defineStore } from 'pinia';
import {
  addFlightItem,
  removeFlightItem,
  removeSubFlightItem,
  storeSubFlightItem,
  updateSubFlightItem,
  getAirlines,
  updateFlightItem,
  getFlightInformation,
  updateCityIso,
  validationFlight,
} from '@service/files';
import { notification } from 'ant-design-vue';

// import { createItineraryAdapter } from '@store/files/adapters'

export const useFlightStore = defineStore({
  id: 'flight',
  state: () => ({
    loading: false,
    flight: [],
    airlines: [],
  }),
  getters: {
    isLoading: (state) => state.loading,
    getFlight: (state) => state.flight,
    listAirlines: (state) => state.airlines,
  },
  actions: {
    updateSub({ fileId, fileItineraryId, itemId, data }) {
      this.loading = true;
      return updateSubFlightItem({ fileId, fileItineraryId, itemId, data })
        .then(() => {
          this.loading = false;
          /*
          notification.success({
            message: 'Éxito',
            description: 'Vuelo actualizado correctamente',
          });
          */
        })
        .catch((error) => {
          notification.warning({
            message: 'Error',
            description: error.errors,
          });
          this.loading = false;
        });
    },
    storeSub({ fileId, fileItineraryId, data }) {
      this.loading = true;
      return storeSubFlightItem({ fileId, fileItineraryId, data })
        .then(() => {
          this.loading = false;
          /*
          notification.success({
            message: 'Éxito',
            description: 'Vuelo agregado correctamente',
          });
          */
        })
        .catch((error) => {
          notification.warning({
            message: 'Error',
            description: error.errors,
          });
          console.log(error);
          this.loading = false;
        });
    },
    removeSub({ fileId, fileItineraryId, itemId }) {
      this.loading = true;
      return removeSubFlightItem({ fileId, fileItineraryId, itemId })
        .then(() => {
          this.loading = false;
          /*
          notification.success({
            message: 'Éxito',
            description: 'Vuelo eliminado correctamente',
          });
          */
        })
        .catch((error) => {
          console.log(error);
          this.loading = false;
        });
    },
    remove({ fileId, fileItineraryId }) {
      this.loading = true;
      return removeFlightItem({ fileId, fileItineraryId })
        .then(() => {
          this.loading = false;
          /*
          notification.success({
            message: 'Éxito',
            description: 'Vuelo eliminado correctamente',
          });
          */
        })
        .catch((error) => {
          console.log(error);
          this.loading = false;
        });
    },
    add({ fileId, fileItineraryId, data }) {
      this.loading = true;
      return addFlightItem({ fileId, fileItineraryId, data })
        .then(() => {
          this.loading = false;
          /*
          notification.success({
            message: 'Éxito',
            description: 'Vuelo agregado correctamente',
          });
          */
        })
        .catch((error) => {
          console.log(error);
          this.loading = false;
        });
    },
    async getListAirlines({ query }) {
      this.loading = true;
      this.airlines = [];

      return getAirlines({ query })
        .then(({ data }) => {
          if (data.success) {
            this.airlines = data.data.map((row) => ({
              label: row.razon,
              value: row.codigo,
            }));
          }
          this.loading = false;
        })
        .catch((error) => {
          console.log(error);
          this.loading = false;
        });
    },
    async updateFlight({ file_id, flight_id, data }) {
      this.loading = true;

      return updateFlightItem({ file_id, flight_id, data })
        .then(() => {
          this.loading = false;
        })
        .catch((error) => {
          console.log(error);
          this.loading = false;
        });
    },
    async flightInformation({ flight_number }) {
      this.loading = true;
      return getFlightInformation({ flight_number })
        .then(({ data }) => {
          this.loading = false;
          return data.data;
        })
        .catch((error) => {
          console.log(error);
          this.loading = false;
        });
    },
    async updateCityIso({ fileId, fileItineraryId, data }) {
      return updateCityIso({ fileId, fileItineraryId, data })
        .then(({ data }) => {
          console.log(data);
        })
        .catch((error) => {
          console.log(error);
        });
    },
    validationFlight({ data }) {
      this.loading = true;
      return validationFlight({ data })
        .then(({ data }) => {
          this.loading = false;
          return data.data;
        })
        .catch((error) => {
          console.log(error);
          this.loading = false;
        });
    },
  },
});
