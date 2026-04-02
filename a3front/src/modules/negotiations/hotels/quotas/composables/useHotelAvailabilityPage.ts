import { computed, ref, watch } from 'vue';
import { message } from 'ant-design-vue';
import { useHotelAvailabilityChart } from '@/modules/negotiations/hotels/quotas/composables/useHotelAvailabilityChart';
import { useHotelAvailabilityChartStore } from '@/modules/negotiations/hotels/quotas/store/hotel-availability-chart.store';
import { useHotelAvailabilityLoadingStore } from '@/modules/negotiations/hotels/quotas/store/hotel-availability-loading.store';
import { useHotelAvailabilityFilterStore } from '@/modules/negotiations/hotels/quotas/store/hotel-availability-filter.store';
import hotelsApi from '@/modules/negotiations/hotels/api/hotelsApi';
import type {
  HotelAvailabilityFilters,
  FilterOption,
} from '@/modules/negotiations/hotels/quotas/interfaces/quotas.interface';

export const useHotelAvailabilityPage = (
  filters: { value: HotelAvailabilityFilters },
  search: (force: boolean) => Promise<void>,
  isLoadingCategories: { value: boolean },
  isLoadingConnections: { value: boolean },
  isLoadingHotelChains: { value: boolean },
  isLoadingHotelCategories: { value: boolean },
  isLoadingRateTypes: { value: boolean },
  isLoadingRoomTypes: { value: boolean },
  isLoadingCalendar: { value: boolean },
  roomType: { value: string | null },
  roomTypes: { value: FilterOption[] },
  hotelCategories: { value: FilterOption[] },
  totalRooms: { value: string | number },
  availableRooms: { value: string | number },
  soldoutRooms: { value: string | number },
  blockedRooms: { value: string | number },
  activeView: { value: 'table' | 'calendar' }
) => {
  // Obtener el store del gráfico
  const chartStore = useHotelAvailabilityChartStore();

  // Obtener el store de loading
  const loadingStore = useHotelAvailabilityLoadingStore();

  // Obtener el store de filtros
  const filterStore = useHotelAvailabilityFilterStore();

  // Computed para obtener los datos procesados del gráfico desde el store
  const chartData = computed(() => chartStore.chartData);

  // Computed para obtener los labels del eje X (meses) desde el store
  const chartXLabels = computed(() => chartStore.chartXLabels);

  // Computed para obtener los colores del gráfico desde el store
  const chartColors = computed(() => chartStore.chartColors);

  // Computed para determinar si es la carga inicial (combos y filtros)
  const isInitialLoading = computed(() => {
    return (
      isLoadingCategories.value ||
      isLoadingConnections.value ||
      isLoadingHotelChains.value ||
      isLoadingHotelCategories.value ||
      isLoadingRateTypes.value ||
      isLoadingRoomTypes.value ||
      isLoadingCalendar.value
    );
  });

  // Computed para combinar todos los estados de loading
  // Ahora usamos el sistema centralizado del loadingStore
  const isLoadingRoomTypeChange = ref(false);
  const isLoading = computed(() => {
    return loadingStore.hasActiveRequests || isLoadingRoomTypeChange.value;
  });

  // Computed para validar campos requeridos
  const isDestinationRequired = computed(() => !filters.value.destination);
  const isPeriodRequired = computed(
    () => !filters.value.dateRange.from || !filters.value.dateRange.to
  );

  // Función wrapper para forzar la búsqueda cuando el usuario hace clic en el botón
  const handleSearch = async () => {
    // Validar campos requeridos
    const missingFields: string[] = [];

    if (!filters.value.destination) {
      missingFields.push('Ciudad');
    }

    if (!filters.value.dateRange.from || !filters.value.dateRange.to) {
      missingFields.push('Período');
    }

    // Si faltan campos requeridos, mostrar mensaje de error
    if (missingFields.length > 0) {
      const fieldsText = missingFields.join(', ');
      message.error(`Por favor complete los siguientes campos requeridos: ${fieldsText}`);
      return;
    }

    // Establecer la vista en tabla antes de ejecutar la búsqueda
    activeView.value = 'table';

    // Si todos los campos están completos, ejecutar la búsqueda
    // calendar-details se ejecuta en paralelo dentro de search()
    await search(true); // force = true para permitir búsquedas manuales
  };

  // Watcher para ejecutar búsqueda cuando se aplican filtros
  watch(
    () => filterStore.applyFilters,
    (newValue) => {
      if (newValue) {
        // Ejecutar la búsqueda
        handleSearch();
        // Resetear el flag después de ejecutar la búsqueda
        filterStore.resetApplyFilters();
      }
    }
  );

  // Estado local para el loading del botón de descarga
  const isDownloading = ref(false);

  const handleDownload = async () => {
    // Validar que haya filtros necesarios
    if (
      !filters.value.destination?.code ||
      !filters.value.dateRange.from ||
      !filters.value.dateRange.to
    ) {
      return;
    }

    // Construir los parámetros para GET (igual que fetchHotelsRoomsList)
    const params: Record<string, any> = {
      destination: filters.value.destination.code,
      date_from: filters.value.dateRange.from,
      date_to: filters.value.dateRange.to,
    };

    // Agregar parámetros opcionales si están presentes
    if (filters.value.connection?.code) {
      params.channel_id = parseInt(filters.value.connection.code);
    }

    if (filters.value.internalCategory?.code) {
      params.type_classes_id = parseInt(filters.value.internalCategory.code);
    }

    if (filterStore.filterState.hotelChain) {
      params.chain_id = parseInt(filterStore.filterState.hotelChain);
    }

    if (
      filterStore.filterState.hotelCategories &&
      filterStore.filterState.hotelCategories.length > 0
    ) {
      // Convertir todos los códigos seleccionados a arrays de IDs
      params.stars = filterStore.filterState.hotelCategories.map((selectedCode) => {
        const selectedCategory = hotelCategories.value.find((cat) => cat.code === selectedCode);
        if (selectedCategory && selectedCategory.originalId) {
          return parseInt(selectedCategory.originalId);
        } else {
          // Fallback: intentar parsear directamente si no tiene originalId
          return parseInt(selectedCode.split('-')[0]);
        }
      });
    }

    if (filterStore.filterState.rateTypes && filterStore.filterState.rateTypes.length > 0) {
      params.rates_plans_type_id = filterStore.filterState.rateTypes.map((rateType) =>
        parseInt(rateType)
      );
    }

    try {
      isDownloading.value = true;

      // Hacer la petición con responseType: 'blob' para recibir el XLSX
      const response = await hotelsApi.get('/api/allotment/hotels/export', {
        params,
        responseType: 'blob',
      });

      // Crear el blob con el tipo XLSX
      const blob = new Blob([response.data], {
        type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      });
      const url = window.URL.createObjectURL(blob);

      // Crear un elemento <a> temporal para descargar
      const a = document.createElement('a');
      a.href = url;

      // Generar nombre de archivo con fecha
      const dateFrom = filters.value.dateRange.from || '';
      const dateTo = filters.value.dateRange.to || '';
      const filename = `disponibilidad-hoteles-${dateFrom}-${dateTo}.xlsx`;
      a.download = filename;

      // Agregar al DOM, hacer click y remover
      document.body.appendChild(a);
      a.click();

      // Limpiar después de un breve delay
      setTimeout(() => {
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
      }, 100);
    } catch (error) {
      console.error('Error al descargar el archivo XLSX:', error);
    } finally {
      isDownloading.value = false;
    }
  };

  // Toggle de visibilidad de línea en el gráfico
  const toggleLine = (lineIndex: number) => {
    chartStore.toggleLineVisibility(lineIndex);
  };

  // Manejar el cambio del tipo de habitación
  const handleRoomTypeChange = async () => {
    // Validar que haya filtros necesarios antes de ejecutar
    if (
      !filters.value.destination?.code ||
      !filters.value.dateRange.from ||
      !filters.value.dateRange.to
    ) {
      return;
    }

    // Activar loading
    isLoadingRoomTypeChange.value = true;

    // Obtener el occupation del roomType seleccionado
    let occupation = 2; // Por defecto
    if (roomType.value) {
      const selectedRoomType = roomTypes.value.find((rt) => rt.code === roomType.value);
      if (selectedRoomType && selectedRoomType.occupation !== undefined) {
        occupation = selectedRoomType.occupation;
      }
    }

    // Crear instancia del composable del gráfico
    const chartComposableInstance = useHotelAvailabilityChart(
      filters,
      roomType,
      roomTypes,
      hotelCategories,
      totalRooms,
      availableRooms,
      soldoutRooms,
      blockedRooms
    );

    // Ejecutar ambos endpoints en paralelo con el occupation
    try {
      await Promise.all([
        chartComposableInstance.fetchChartTotals(occupation),
        chartComposableInstance.fetchHotelAvailability(occupation),
      ]);
    } catch (error) {
      console.error('Error al actualizar el gráfico:', error);
    } finally {
      // Desactivar loading después de que ambas consultas terminen
      isLoadingRoomTypeChange.value = false;
    }
  };

  // Computed para obtener los labels del gráfico desde el store
  const chartLabels = computed(() => chartStore.chartLabels);

  // Función helper para verificar si una línea está visible
  const isLineVisible = (index: number) => {
    return chartStore.isLineVisible(index);
  };

  // No cargar automáticamente al montar, solo cuando se ejecute handleSearch
  // El calendario se carga cuando el usuario hace una búsqueda o cambia de mes

  return {
    chartStore,
    loadingStore,
    filterStore,
    chartData,
    chartXLabels,
    chartColors,
    chartLabels,
    isInitialLoading,
    isLoading,
    isLoadingRoomTypeChange,
    isDestinationRequired,
    isPeriodRequired,
    handleSearch,
    isDownloading,
    handleDownload,
    toggleLine,
    handleRoomTypeChange,
    isLineVisible,
  };
};
