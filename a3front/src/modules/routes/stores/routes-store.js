import { defineStore } from 'pinia';
import {
  fetchRoutes,
  saveRoute,
  deleteRoute as deleteRouteService,
} from '@service/routes/index.js';

export const useRoutesStore = defineStore({
  id: 'routes',
  state: () => ({
    routes: [],
    loading: false,
    isListing: false,
  }),
  getters: {
    isLoading: (state) => state.loading,
  },
  actions: {
    async fetchRoutes(filters = {}) {
      this.loading = true;
      try {
        const { data } = await fetchRoutes(filters);
        this.routes = data; // Ajustar según la estructura de la API
      } catch (error) {
        console.error('Error al obtener rutas:', error);
      } finally {
        this.loading = false;
      }
    },

    async saveRoute(route) {
      try {
        const updatedRoute = await saveRoute(route);
        if (updatedRoute) {
          await this.fetchRoutes(); // Recargar la lista de rutas después de guardar
        }
      } catch (error) {
        console.error('Error al guardar ruta:', error);
      }
    },
    async deleteRoute(nroFile) {
      try {
        await deleteRouteService(nroFile); // Llama al servicio de eliminación
        await this.fetchRoutes();
      } catch (error) {
        console.error('Error al eliminar ruta:', error);
      }
    },
  },
});
