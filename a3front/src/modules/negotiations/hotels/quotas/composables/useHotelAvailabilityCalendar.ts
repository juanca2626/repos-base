import { ref, computed } from 'vue';
import moment from 'moment';
import hotelsApi from '@/modules/negotiations/hotels/api/hotelsApi';
import { useHotelAvailabilityCalendarStore } from '@/modules/negotiations/hotels/quotas/store/hotel-availability-calendar.store';
import { useHotelAvailabilityLoadingStore } from '@/modules/negotiations/hotels/quotas/store/hotel-availability-loading.store';
import { useHotelAvailabilityFilterStore } from '@/modules/negotiations/hotels/quotas/store/hotel-availability-filter.store';
import type {
  CalendarDetailsResponse,
  HotelAvailabilityFilters,
  FilterOption,
} from '@/modules/negotiations/hotels/quotas/interfaces/quotas.interface';

// Configurar moment en español
moment.locale('es');

export interface DayAvailability {
  date: string; // YYYY-MM-DD
  agotados: number;
  disponibles: number;
  bloqueados: number;
}

export interface CalendarDay {
  key: string;
  date: string;
  day: number;
  isCurrentMonth: boolean;
}

export const useHotelAvailabilityCalendar = (
  filters?: { value: HotelAvailabilityFilters },
  hotelCategories?: { value: FilterOption[] }
) => {
  const calendarStore = useHotelAvailabilityCalendarStore();
  const loadingStore = useHotelAvailabilityLoadingStore();
  const filterStore = useHotelAvailabilityFilterStore();

  const currentDate = ref(moment());
  const selectedDate = ref<string | null>(null);
  const weekdays = ['Lun', 'Mar', 'Miér', 'Jue', 'Vie', 'Sáb', 'Dom'];

  const monthName = computed(() => {
    const formatted = currentDate.value.format('MMMM YYYY');
    return formatted.charAt(0).toUpperCase() + formatted.slice(1);
  });

  const calendarDays = computed((): CalendarDay[] => {
    const startOfMonth = currentDate.value.clone().startOf('month');
    const endOfMonth = currentDate.value.clone().endOf('month');

    let startDate = startOfMonth.clone();
    const dayOfWeek = startDate.day(); // 0 = domingo, 1 = lunes, etc.
    const daysToSubtract = dayOfWeek === 0 ? 6 : dayOfWeek - 1;
    startDate = startDate.subtract(daysToSubtract, 'days');

    let endDate = endOfMonth.clone();
    const endDayOfWeek = endDate.day();
    const daysToAdd = endDayOfWeek === 0 ? 0 : 7 - endDayOfWeek;
    endDate = endDate.add(daysToAdd, 'days');

    const days: CalendarDay[] = [];
    const current = startDate.clone();

    while (current.isSameOrBefore(endDate, 'day')) {
      const dateStr = current.format('YYYY-MM-DD');
      days.push({
        key: dateStr,
        date: dateStr,
        day: current.date(),
        isCurrentMonth: current.isSame(currentDate.value, 'month'),
      });
      current.add(1, 'day');
    }

    return days;
  });

  const previousMonth = async () => {
    currentDate.value = currentDate.value.clone().subtract(1, 'month');
    // Recargar datos del nuevo mes
    if (filters?.value) {
      await fetchCalendarDetails();
    }
  };

  const nextMonth = async () => {
    currentDate.value = currentDate.value.clone().add(1, 'month');
    // Recargar datos del nuevo mes
    if (filters?.value) {
      await fetchCalendarDetails();
    }
  };

  const selectDay = (date: string) => {
    const dateMoment = moment(date);
    if (dateMoment.isSame(currentDate.value, 'month')) {
      selectedDate.value = date;
    }
  };

  const isSelected = (date: string): boolean => {
    return selectedDate.value === date;
  };

  const getDayData = (date: string): DayAvailability | undefined => {
    const dayData = calendarStore.getDayData(date);
    if (!dayData) return undefined;

    return {
      date: dayData.date,
      agotados: dayData.totals.sold_out_hotels,
      disponibles: dayData.totals.available_hotels,
      bloqueados: dayData.totals.blocked_hotels,
    };
  };

  const fetchCalendarDetails = async (skipLoading: boolean = false) => {
    if (!filters?.value) {
      console.error('❌ fetchCalendarDetails: filters no está disponible');
      return;
    }

    // Obtener el mes actual (primer y último día del mes)
    const startOfMonth = currentDate.value.clone().startOf('month');
    const endOfMonth = currentDate.value.clone().endOf('month');

    // Construir los parámetros para GET (mismos que otras endpoints)
    const params: Record<string, any> = {
      destination: filters.value.destination?.code || '89,1610',
      date_from: startOfMonth.format('YYYY-MM-DD'),
      date_to: endOfMonth.format('YYYY-MM-DD'),
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
      filterStore.filterState.hotelCategories.length > 0 &&
      hotelCategories?.value
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

    if (!skipLoading) {
      loadingStore.startRequest('calendarDetails');
    }

    try {
      const response = await hotelsApi.get<CalendarDetailsResponse>(
        '/api/allotment/hotels/calendar-details',
        { params }
      );

      if (response.data && response.data.success && response.data.data) {
        calendarStore.setCalendarData(response.data.data);
      }
    } catch (error) {
      console.error('❌ Error al cargar calendar-details:', error);
    } finally {
      if (!skipLoading) {
        loadingStore.endRequest('calendarDetails');
      }
    }
  };

  const clearSelection = () => {
    selectedDate.value = null;
  };

  const resetToCurrentMonth = () => {
    currentDate.value = moment();
  };

  return {
    currentDate,
    selectedDate,
    weekdays,

    monthName,
    calendarDays,

    previousMonth,
    nextMonth,
    selectDay,
    isSelected,
    getDayData,
    fetchCalendarDetails,
    clearSelection,
    resetToCurrentMonth,
  };
};
