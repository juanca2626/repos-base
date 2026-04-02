import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

// Tipos de peticiones que se rastrean
export type LoadingRequestType =
  | 'destinations'
  | 'countries'
  | 'cities'
  | 'categories'
  | 'connections'
  | 'hotelChains'
  | 'hotelCategories'
  | 'rateTypes'
  | 'roomTypes'
  | 'calendar'
  | 'calendarDetails'
  | 'chart'
  | 'chartTotals'
  | 'hotelsRoomsList'
  | 'hotelsRoomsListTotals';

export const useHotelAvailabilityLoadingStore = defineStore('hotelAvailabilityLoadingStore', () => {
  // Sistema de rastreo de peticiones activas
  const activeRequests = ref<Set<LoadingRequestType>>(new Set());

  // Función para iniciar una petición
  const startRequest = (requestType: LoadingRequestType) => {
    activeRequests.value.add(requestType);
  };

  // Función para finalizar una petición
  const endRequest = (requestType: LoadingRequestType) => {
    activeRequests.value.delete(requestType);
  };

  // Función para finalizar todas las peticiones (útil en caso de error)
  const clearAllRequests = () => {
    activeRequests.value.clear();
  };

  // Computed que indica si hay peticiones activas
  const hasActiveRequests = computed(() => {
    return activeRequests.value.size > 0;
  });

  // Computed que indica si una petición específica está activa
  const isRequestActive = (requestType: LoadingRequestType) => {
    return activeRequests.value.has(requestType);
  };

  // Mantener compatibilidad con código existente
  const isLoadingHotelsRooms = computed(() => {
    return (
      activeRequests.value.has('hotelsRoomsList') ||
      activeRequests.value.has('hotelsRoomsListTotals')
    );
  });

  const isLoadingChartTotals = computed(() => {
    return activeRequests.value.has('chartTotals');
  });

  const setLoadingHotelsRooms = (loading: boolean) => {
    if (loading) {
      activeRequests.value.add('hotelsRoomsList');
    } else {
      activeRequests.value.delete('hotelsRoomsList');
      activeRequests.value.delete('hotelsRoomsListTotals');
    }
  };

  const showLoading = () => {
    // Este método ya no se usa directamente, pero se mantiene para compatibilidad
    // El loading se controla mediante startRequest/endRequest
  };

  const hideLoading = () => {
    // Este método ya no se usa directamente, pero se mantiene para compatibilidad
    // El loading se controla mediante startRequest/endRequest
  };

  const setLoadingChartTotals = (loading: boolean) => {
    if (loading) {
      activeRequests.value.add('chartTotals');
    } else {
      activeRequests.value.delete('chartTotals');
    }
  };

  return {
    // Nuevo sistema
    activeRequests,
    startRequest,
    endRequest,
    clearAllRequests,
    hasActiveRequests,
    isRequestActive,
    // Compatibilidad
    isLoadingHotelsRooms,
    isLoadingChartTotals,
    setLoadingHotelsRooms,
    showLoading,
    hideLoading,
    setLoadingChartTotals,
  };
});
