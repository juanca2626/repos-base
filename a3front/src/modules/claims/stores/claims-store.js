import { defineStore } from 'pinia';
import {
  fetchClaims,
  saveClaim,
  deleteClaim as deleteClaimService,
} from '@service/claims/index.js';

export const useClaimsStore = defineStore({
  id: 'claims',
  state: () => ({
    claims: [],
    loading: false,
    isListing: false,
  }),
  getters: {
    isLoading: (state) => state.loading,
  },
  actions: {
    async fetchClaims(filters = {}) {
      this.loading = true;
      try {
        const { data } = await fetchClaims(filters);
        this.claims = data; // Ajustar según la estructura de la API
      } catch (error) {
        console.error('Error al obtener reclamos:', error);
      } finally {
        this.loading = false;
      }
    },

    async saveClaim(claim) {
      try {
        const updatedClaim = await saveClaim(claim);
        if (updatedClaim) {
          await this.fetchClaims(); // Recargar la lista de reclamos después de guardar
        }
      } catch (error) {
        console.error('Error al guardar reclamo:', error);
      }
    },
    async deleteClaim(nroFile) {
      try {
        await deleteClaimService(nroFile); // Llama al servicio de eliminación
        await this.fetchClaims();
      } catch (error) {
        console.error('Error al eliminar reclamo:', error);
      }
    },
  },
});
