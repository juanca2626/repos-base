import { defineStore } from 'pinia';
import quotesA3Api from '@/quotes/api/quotesA3Api';

export const useItineraryDownloadStore = defineStore('itineraryDownloadStore', {
  state: () => ({
    loading: false,
  }),
  getters: {
    isLoading: (state) => state.loading,
  },
  actions: {
    setLoading(loading: boolean) {
      this.loading = loading;
    },
    async setComboPortada({ cover, language, logoWidth, clientId, clientName, nameDocument }) {
      this.loading = true;
      try {
        const response = await quotesA3Api.get('api/quote/imageCreate', {
          params: {
            clienteId: clientId,
            portada: cover,
            portadaName: nameDocument,
            estado: logoWidth,
            refPax: clientName,
            lang: language,
            nameCliente: clientName,
          },
        });

        // Verificar si la respuesta es válida
        if (response.status === 200 && response.data?.image) {
          return response; // Retorna la respuesta completa si es válida
        } else {
          console.warn('Respuesta inválida de la API:', response);
          return null; // Retorna null si no es válida
        }
      } catch (error) {
        console.error('Error en setComboPortada:', error);
        throw error; // Propaga el error para manejarlo en el componente
      } finally {
        this.loading = false;
      }
    },
  },
});
