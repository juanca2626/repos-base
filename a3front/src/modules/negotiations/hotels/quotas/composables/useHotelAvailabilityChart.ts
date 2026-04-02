import { ref, computed } from 'vue';
import moment from 'moment';
import hotelsApi from '@/modules/negotiations/hotels/api/hotelsApi';
import { useHotelAvailabilityFilterStore } from '@/modules/negotiations/hotels/quotas/store/hotel-availability-filter.store';
import { useHotelAvailabilityLoadingStore } from '@/modules/negotiations/hotels/quotas/store/hotel-availability-loading.store';
import { useHotelAvailabilityChartStore } from '@/modules/negotiations/hotels/quotas/store/hotel-availability-chart.store';
import { useHotelAvailabilityRoomsStore } from '@/modules/negotiations/hotels/quotas/store/hotel-availability-rooms.store';
import type {
  FilterOption,
  HotelAvailabilityFilters,
  HotelAvailabilityChartResponse,
  HotelChartTotalsResponse,
} from '@/modules/negotiations/hotels/quotas/interfaces/quotas.interface';

/**
 * Composable para manejar la lógica del endpoint de disponibilidad de hoteles para el gráfico
 */
export const useHotelAvailabilityChart = (
  filters: { value: HotelAvailabilityFilters },
  roomType: { value: string | null },
  roomTypes: { value: FilterOption[] },
  hotelCategories: { value: FilterOption[] },
  totalRooms: { value: string | number },
  availableRooms: { value: string | number },
  soldoutRooms: { value: string | number },
  blockedRooms: { value: string | number }
) => {
  const filterStore = useHotelAvailabilityFilterStore();
  const loadingStore = useHotelAvailabilityLoadingStore();
  const chartStore = useHotelAvailabilityChartStore();
  const roomsStore = useHotelAvailabilityRoomsStore();
  const availabilityData = ref<HotelAvailabilityChartResponse | null>(null);
  const chartTotalsData = ref<HotelChartTotalsResponse | null>(null);
  const isLoading = ref<boolean>(false);
  const error = ref<string | null>(null);

  /**
   * Calcula el tipo (month o day) basándose en los días del rango de fechas
   * @param dateFrom - Fecha de inicio (YYYY-MM-DD)
   * @param dateTo - Fecha de fin (YYYY-MM-DD)
   * @returns 'month' si el rango es mayor a 31 días, 'day' si es menor o igual a 31 días
   */
  const calculateAvailabilityType = (dateFrom: string, dateTo: string): 'month' | 'day' => {
    if (!dateFrom || !dateTo) {
      return 'month'; // Por defecto month
    }

    const startDate = moment(dateFrom);
    const endDate = moment(dateTo);
    const daysDifference = endDate.diff(startDate, 'days') + 1; // +1 para incluir ambos días

    // Si el rango es mayor a 31 días, usar 'month', si no, usar 'day'
    return daysDifference > 31 ? 'month' : 'day';
  };

  /**
   * Obtiene el occupation del tipo de habitación seleccionado
   * @returns El valor de occupation (por defecto 2)
   */
  const getOccupationFromRoomType = (): number => {
    // Por defecto es 2
    if (!roomType.value) {
      return 2;
    }

    // Buscar el roomType seleccionado en la lista
    const selectedRoomType = roomTypes.value.find((rt) => rt.code === roomType.value);
    if (selectedRoomType && selectedRoomType.occupation !== undefined) {
      return selectedRoomType.occupation;
    }

    // Si no se encuentra o no tiene occupation, retornar 2 por defecto
    return 2;
  };

  /**
   * Obtiene los totales del gráfico desde el endpoint
   * @param occupationParam - Valor de occupation opcional (si no se proporciona, se obtiene del roomType)
   * @returns La respuesta del endpoint o null si hay error de validación
   */
  const fetchChartTotals = async (
    occupationParam?: number,
    skipLoading: boolean = false
  ): Promise<HotelChartTotalsResponse | null> => {
    // Validar que los campos obligatorios estén presentes
    if (!filters.value.destination?.code) {
      const errorMsg = '❌ fetchChartTotals: El destination es obligatoria';
      console.error(errorMsg);
      return null;
    }

    if (!filters.value.dateRange.from || !filters.value.dateRange.to) {
      const errorMsg = '❌ fetchChartTotals: Las fechas son obligatorias';
      console.error(errorMsg);
      return null;
    }

    if (!skipLoading) {
      loadingStore.startRequest('chartTotals');
    }

    try {
      // Calcular el tipo basándose en los días del rango
      const chartType = calculateAvailabilityType(
        filters.value.dateRange.from,
        filters.value.dateRange.to
      );

      // Obtener el occupation (si se pasa como parámetro, usarlo; si no, obtener del roomType)
      const occupation =
        occupationParam !== undefined ? occupationParam : getOccupationFromRoomType();

      // Construir los parámetros para GET (mismos que fetchHotelAvailability)
      const params: Record<string, any> = {
        destination: filters.value.destination.code,
        date_from: filters.value.dateRange.from,
        date_to: filters.value.dateRange.to,
        type: chartType,
        occupation: occupation,
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

      const response = await hotelsApi.get<HotelChartTotalsResponse>(
        '/api/allotment/hotels/chart-totals',
        { params }
      );

      chartTotalsData.value = response.data;

      // Actualizar los valores en el store (persistencia entre navegaciones)
      if (response.data && response.data.data && response.data.data.length > 0) {
        const totals = response.data.data[0];
        // Guardar los valores en el store directamente
        roomsStore.setRoomsStats({
          availableRooms: totals.available_rooms,
          soldoutRooms: totals.sold_out_rooms,
          blockedRooms: totals.blocked_rooms,
          totalRooms: totals.total_count,
        });

        // También actualizar los refs pasados como parámetros para compatibilidad
        availableRooms.value = totals.available_rooms;
        soldoutRooms.value = totals.sold_out_rooms;
        blockedRooms.value = totals.blocked_rooms;
        totalRooms.value = totals.total_count;
      }

      return response.data;
    } catch (err) {
      const errorMsg = '❌ fetchChartTotals: Error al obtener los totales del gráfico';
      console.error(errorMsg, err);
      throw err;
    } finally {
      if (!skipLoading) {
        loadingStore.endRequest('chartTotals');
      }
    }
  };

  /**
   * Obtiene la disponibilidad de hoteles desde el endpoint
   * @param occupationParam - Valor de occupation opcional (si no se proporciona, se obtiene del roomType)
   * @returns La respuesta del endpoint o null si hay error de validación
   */
  const fetchHotelAvailability = async (
    occupationParam?: number,
    skipLoading: boolean = false
  ): Promise<HotelAvailabilityChartResponse | null> => {
    isLoading.value = true;
    error.value = null;

    // Validar que los campos obligatorios estén presentes
    if (!filters.value.destination?.code) {
      const errorMsg = '❌ fetchHotelAvailability: El destination es obligatoria';
      console.error(errorMsg);
      error.value = errorMsg;
      isLoading.value = false;
      return null;
    }

    if (!filters.value.dateRange.from || !filters.value.dateRange.to) {
      const errorMsg = '❌ fetchHotelAvailability: Las fechas son obligatorias';
      console.error(errorMsg);
      error.value = errorMsg;
      isLoading.value = false;
      return null;
    }

    if (!skipLoading) {
      loadingStore.startRequest('chart');
    }

    try {
      // Calcular el tipo basándose en los días del rango
      const chartType = calculateAvailabilityType(
        filters.value.dateRange.from,
        filters.value.dateRange.to
      );

      // Obtener el occupation (si se pasa como parámetro, usarlo; si no, obtener del roomType)
      const occupation =
        occupationParam !== undefined ? occupationParam : getOccupationFromRoomType();

      // Construir los parámetros para GET (mismos que fetchHotelsRoomsList)
      const params: Record<string, any> = {
        destination: filters.value.destination.code,
        date_from: filters.value.dateRange.from,
        date_to: filters.value.dateRange.to,
        type: chartType,
        occupation: occupation,
      };

      // Agregar parámetros opcionales si están presentes (igual que fetchHotelsRoomsList)
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

      const response = await hotelsApi.get<HotelAvailabilityChartResponse>(
        '/api/allotment/hotels/chart',
        { params }
      );

      availabilityData.value = response.data;

      // Actualizar el store con los datos procesados del gráfico
      // (chartType ya fue calculado anteriormente)
      if (response.data && response.data.data) {
        const processedData = getProcessedChartData();
        const months = getMonthsFromData();
        const colors = getChartColors();
        const labels = getChartLabels();

        chartStore.setChartData(processedData);
        chartStore.setChartXLabels(months);
        chartStore.setChartColors(colors);
        chartStore.setChartLabels(labels);
        chartStore.setRawChartData(response.data.data);
        chartStore.setChartType(chartType);
      }

      return response.data;
    } catch (err) {
      const errorMsg = '❌ fetchHotelAvailability: Error al obtener la disponibilidad de hoteles';
      console.error(errorMsg, err);
      error.value = errorMsg;
      throw err;
    } finally {
      isLoading.value = false;
      if (!skipLoading) {
        loadingStore.endRequest('chart');
      }
    }
  };

  /**
   * Extrae los meses o días únicos de los datos según el tipo
   * @returns Array de meses formateados (ej: ["Ene", "Feb", ...]) o días formateados
   */
  const getMonthsFromData = (): string[] => {
    if (!availabilityData.value || !availabilityData.value.data) {
      return [];
    }

    // Determinar el tipo basándose en el formato de la fecha
    const firstItem = availabilityData.value.data[0];
    if (!firstItem || !firstItem.date) {
      return [];
    }

    // Si la fecha tiene formato "YYYY-MM-DD" es por día, si es "YYYY-MM" es por mes
    const isDayType = firstItem.date.includes('-') && firstItem.date.split('-').length === 3;

    if (isDayType) {
      // Por día: extraer días únicos
      const daysMap = new Map<string, moment.Moment>();
      availabilityData.value.data.forEach((item) => {
        if (item.date) {
          const dateMoment = moment(item.date, 'YYYY-MM-DD');
          const dayKey = dateMoment.format('YYYY-MM-DD');
          if (!daysMap.has(dayKey)) {
            daysMap.set(dayKey, dateMoment);
          }
        }
      });

      // Ordenar por fecha y formatear solo el día como "DD" (ej: "01", "26")
      return Array.from(daysMap.entries())
        .sort((a, b) => a[1].valueOf() - b[1].valueOf())
        .map(([_, dateMoment]) => dateMoment.format('DD'));
    } else {
      // Por mes: extraer meses únicos
      const monthsMap = new Map<string, moment.Moment>();
      availabilityData.value.data.forEach((item) => {
        if (item.date) {
          // El formato viene como "2025-12" (YYYY-MM)
          const dateMoment = moment(item.date, 'YYYY-MM');
          const monthKey = dateMoment.format('YYYY-MM'); // Clave para ordenar

          if (!monthsMap.has(monthKey)) {
            monthsMap.set(monthKey, dateMoment);
          }
        }
      });

      // Ordenar por fecha y extraer solo los meses
      return Array.from(monthsMap.entries())
        .sort((a, b) => a[1].valueOf() - b[1].valueOf())
        .map(([_, dateMoment]) => dateMoment.format('MMM')); // Solo el mes sin año
    }
  };

  /**
   * Procesa los datos del gráfico agrupándolos por mes o día según el tipo
   * @returns Array de arrays con los valores agrupados por typeclass_id y fecha
   */
  const getProcessedChartData = (): (number | null)[][] => {
    if (!availabilityData.value || !availabilityData.value.data) {
      return [];
    }

    // Obtener las fechas únicas ordenadas (meses o días)
    const dateLabels = getMonthsFromData();
    if (dateLabels.length === 0) {
      return [];
    }

    // Determinar el tipo basándose en el formato de la fecha
    const firstItem = availabilityData.value.data[0];
    const isDayType = firstItem.date.includes('-') && firstItem.date.split('-').length === 3;

    // Agrupar datos por typeclass_id y fecha
    // Si hay múltiples registros de la misma fecha y typeclass_id, sumar los valores
    const dataByTypeClass: Record<number, Record<string, number>> = {};

    availabilityData.value.data.forEach((item) => {
      const typeClassId = item.typeclass_id;
      if (!dataByTypeClass[typeClassId]) {
        dataByTypeClass[typeClassId] = {};
      }

      // Convertir la fecha según el tipo
      let dateKey: string;
      if (isDayType) {
        const dateMoment = moment(item.date, 'YYYY-MM-DD');
        dateKey = dateMoment.format('DD'); // "01", "26"
      } else {
        const dateMoment = moment(item.date, 'YYYY-MM');
        dateKey = dateMoment.format('MMM'); // "Nov"
      }

      // Usar score_del_periodo
      // Si hay múltiples registros de la misma fecha y typeclass_id, sumar los valores
      const currentValue = dataByTypeClass[typeClassId][dateKey] || 0;
      dataByTypeClass[typeClassId][dateKey] = currentValue + item.score_del_periodo;
    });
    // Convertir a array de arrays, una línea por typeclass_id
    // Ordenar por typeclass_id para mantener consistencia
    const result: (number | null)[][] = [];
    const sortedTypeClassIds = Object.keys(dataByTypeClass)
      .map((id) => parseInt(id))
      .sort((a, b) => a - b);

    sortedTypeClassIds.forEach((typeClassId) => {
      const series = dateLabels.map((dateLabel) => {
        return dataByTypeClass[typeClassId][dateLabel] ?? null;
      });
      result.push(series);
    });
    return result;
  };

  /**
   * Obtiene los colores de las líneas basados en typeclass_color
   * @returns Array de colores para cada línea del gráfico
   */
  const getChartColors = (): string[] => {
    if (!availabilityData.value || !availabilityData.value.data) {
      return ['#246337', '#A93030', '#BBADE5', '#1284ED', '#0F766E'];
    }

    // Obtener colores únicos por typeclass_id
    const colorsByTypeClass: Record<number, string> = {};
    availabilityData.value.data.forEach((item) => {
      if (!colorsByTypeClass[item.typeclass_id]) {
        colorsByTypeClass[item.typeclass_id] = item.typeclass_color;
      }
    });

    // Ordenar por typeclass_id y retornar los colores
    return Object.keys(colorsByTypeClass)
      .sort((a, b) => parseInt(a) - parseInt(b))
      .map((typeClassId) => colorsByTypeClass[parseInt(typeClassId)]);
  };

  /**
   * Obtiene los nombres de las categorías (typeclass_name) ordenados por typeclass_id
   * @returns Array de nombres para cada línea del gráfico
   */
  const getChartLabels = (): string[] => {
    if (!availabilityData.value || !availabilityData.value.data) {
      return [];
    }

    // Obtener nombres únicos por typeclass_id
    const labelsByTypeClass: Record<number, string> = {};
    availabilityData.value.data.forEach((item) => {
      if (!labelsByTypeClass[item.typeclass_id]) {
        labelsByTypeClass[item.typeclass_id] = item.typeclass_name;
      }
    });

    // Ordenar por typeclass_id y retornar los nombres
    return Object.keys(labelsByTypeClass)
      .sort((a, b) => parseInt(a) - parseInt(b))
      .map((typeClassId) => labelsByTypeClass[parseInt(typeClassId)]);
  };

  return {
    availabilityData: computed(() => availabilityData.value),
    chartTotalsData: computed(() => chartTotalsData.value),
    isLoading: computed(() => isLoading.value),
    error: computed(() => error.value),
    calculateAvailabilityType,
    getOccupationFromRoomType,
    fetchChartTotals,
    fetchHotelAvailability,
    getMonthsFromData,
    getProcessedChartData,
    getChartColors,
    getChartLabels,
  };
};
