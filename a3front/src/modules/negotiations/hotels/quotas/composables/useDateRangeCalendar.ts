import { ref, computed } from 'vue';
import moment from 'moment';
import { useHotelAvailability } from './useHotelAvailability';

// Variables compartidas para mantener la selección entre instancias
const selectedStartShared = ref<moment.Moment | null>(null);
const selectedEndShared = ref<moment.Moment | null>(null);
const currentMonthShared = ref(moment());
const loadedMonthsShared = ref<Set<string>>(new Set());

export const useDateRangeCalendar = () => {
  const { fetchCalendar, filters } = useHotelAvailability();

  // Estado del calendario (usando variables compartidas)
  const currentMonth = currentMonthShared;
  const selectedStart = selectedStartShared;
  const selectedEnd = selectedEndShared;
  const isLoadingCalendar = ref(false);

  // Cache de meses ya consultados (formato: 'YYYY-MM')
  const loadedMonths = loadedMonthsShared;

  // Segundo mes visible (mes siguiente)
  const secondMonth = computed(() => {
    return currentMonth.value.clone().add(1, 'month');
  });

  // Función para obtener la clave del mes (YYYY-MM)
  const getMonthKey = (month: moment.Moment): string => {
    return month.format('YYYY-MM');
  };

  // Función para consultar disponibilidad de los meses visibles
  const fetchAvailabilityForVisibleMonths = async () => {
    // Verificar que haya destination (ciudad) para poder cargar la disponibilidad
    if (!filters.value.destination?.code) {
      return;
    }

    const firstMonth = currentMonth.value.clone();
    const secondMonthValue = secondMonth.value;

    const monthsToLoad: string[] = [];

    // Verificar si el primer mes ya está cargado
    const firstMonthKey = getMonthKey(firstMonth);
    if (!loadedMonths.value.has(firstMonthKey)) {
      monthsToLoad.push(firstMonthKey);
    }

    // Verificar si el segundo mes ya está cargado
    const secondMonthKey = getMonthKey(secondMonthValue);
    if (!loadedMonths.value.has(secondMonthKey)) {
      monthsToLoad.push(secondMonthKey);
    }

    // Si no hay meses nuevos para cargar, retornar
    if (monthsToLoad.length === 0) {
      return;
    }

    isLoadingCalendar.value = true;

    try {
      // Calcular el rango de fechas para los meses a consultar
      const startDate = firstMonth.clone().startOf('month');
      const endDate = secondMonthValue.clone().endOf('month');

      // Pasar las fechas directamente a fetchCalendar sin modificar filters.dateRange
      // skipLoadingStore = true para evitar activar el loading general
      await fetchCalendar(
        startDate.format('YYYY-MM-DD'),
        endDate.format('YYYY-MM-DD'),
        true // skipLoadingStore
      );

      // Marcar los meses como cargados
      monthsToLoad.forEach((monthKey) => {
        loadedMonths.value.add(monthKey);
      });
    } catch (error) {
      console.error('Error al cargar disponibilidad del calendario:', error);
    } finally {
      isLoadingCalendar.value = false;
    }
  };

  // Función para navegar al mes anterior
  const previousMonth = async () => {
    currentMonth.value = currentMonth.value.clone().subtract(1, 'month');
    // Consultar disponibilidad del nuevo mes si no está cargado
    await fetchAvailabilityForVisibleMonths();
  };

  // Función para navegar al mes siguiente
  const nextMonth = async () => {
    currentMonth.value = currentMonth.value.clone().add(1, 'month');
    // Consultar disponibilidad del nuevo mes si no está cargado
    await fetchAvailabilityForVisibleMonths();
  };

  // Función para obtener los días de un mes
  const getDaysForMonth = (month: moment.Moment) => {
    const days: Array<{ date: string; day: number; isCurrentMonth: boolean; isToday: boolean }> =
      [];
    const startOfMonth = month.clone().startOf('month');
    const endOfMonth = month.clone().endOf('month');

    // Obtener el día de la semana del primer día del mes (0 = domingo, 1 = lunes, etc.)
    const firstDayOfWeek = startOfMonth.day(); // 0 = domingo, 1 = lunes, etc.

    // Agregar espacios vacíos al inicio para alinear el primer día con el día de la semana correcto
    for (let i = 0; i < firstDayOfWeek; i++) {
      days.push({
        date: '',
        day: 0,
        isCurrentMonth: false,
        isToday: false,
      });
    }

    // Solo incluir días del mes actual
    let currentDate = startOfMonth.clone();
    while (currentDate.isSameOrBefore(endOfMonth, 'day')) {
      days.push({
        date: currentDate.format('YYYY-MM-DD'),
        day: currentDate.date(),
        isCurrentMonth: true,
        isToday: currentDate.isSame(moment(), 'day'),
      });
      currentDate.add(1, 'day');
    }
    return days;
  };

  // Función para verificar si una fecha está en el rango seleccionado
  const isInRange = (dateMoment: moment.Moment): boolean => {
    if (!selectedStart.value || !selectedEnd.value) return false;
    return dateMoment.isBetween(selectedStart.value, selectedEnd.value, 'day', '[]');
  };

  // Función para resetear la posición del calendario (volver al mes actual y limpiar cache)
  const resetCalendarPosition = () => {
    currentMonth.value = moment(); // Volver al mes actual
    loadedMonths.value.clear(); // Limpiar el cache de meses cargados
  };

  return {
    // Estado
    currentMonth,
    secondMonth,
    selectedStart,
    selectedEnd,
    isLoadingCalendar,
    loadedMonths,

    // Funciones
    fetchAvailabilityForVisibleMonths,
    previousMonth,
    nextMonth,
    getDaysForMonth,
    isInRange,
    getMonthKey,
    resetCalendarPosition,
  };
};
