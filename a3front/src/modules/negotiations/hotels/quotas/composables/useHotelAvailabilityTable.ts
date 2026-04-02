import { ref, computed, watch } from 'vue';
import moment from 'moment';
import { useHotelAvailability } from './useHotelAvailability';
import type {
  HotelRoomData,
  HotelRoomDetail,
  HotelRoomPeriod,
  HotelRoomDay,
} from '@/modules/negotiations/hotels/quotas/interfaces/quotas.interface';

export const useHotelAvailabilityTable = () => {
  // Obtener datos del composable compartido
  const {
    hotelsRoomsList,
    hotelsRoomsPagination,
    fetchHotelsRoomsList,
    filters,
    isLoadingHotelsRooms,
  } = useHotelAvailability();

  // Estado de UI específico de la tabla
  const connectionSortIcon = ref<'short-wide' | 'wide-short'>('short-wide');
  const preferenteSortIcon = ref<'short-wide' | 'wide-short'>('wide-short');
  const statusSortIcon = ref<'short-wide' | 'wide-short'>('short-wide');
  const activeKeys = ref<string[]>([]);
  const hasSearched = ref(false);
  const currentPeriodIndexByHotel = ref<Record<number, number>>({});

  // Estado para mantener el ordenamiento actual
  const currentOrderField = ref<string>('preferente');
  const currentOrderDir = ref<'asc' | 'desc'>('asc');

  // Observar cuando se completa la carga para marcar que se ha buscado
  watch(
    () => isLoadingHotelsRooms.value,
    (isLoading, wasLoading) => {
      if (wasLoading && !isLoading) {
        hasSearched.value = true;
      }
    }
  );

  // Función para transformar los datos del API al formato de la tabla
  const transformHotelData = (hotelData: HotelRoomData) => {
    // Transformar las habitaciones
    const transformedRooms = hotelData.details.map((detail: HotelRoomDetail, _index: number) => {
      // Obtener todos los días de todos los períodos
      const allDays: Record<string, number | null> = {};
      const blockedDays: Record<string, boolean> = {};
      const lockedDays: Record<string, number> = {}; // Almacenar el valor de locked para cada día
      const highlightedDays: number[] = [];

      detail.days.forEach((period: HotelRoomPeriod) => {
        period.days.forEach((day: HotelRoomDay) => {
          // Validar y convertir inventory_num a número válido o null
          let inventoryNum: number | null;
          if (typeof day.inventory_num === 'number' && !isNaN(day.inventory_num)) {
            inventoryNum = day.inventory_num;
          } else if (typeof day.inventory_num === 'string') {
            const parsed = parseInt(day.inventory_num, 10);
            inventoryNum = !isNaN(parsed) ? parsed : null;
          } else {
            // Si es null, undefined o cualquier otro tipo inválido, usar null
            inventoryNum = null;
          }

          // Normalizar la fecha a formato YYYY-MM-DD para asegurar consistencia
          const normalizedDate = moment(day.date).format('YYYY-MM-DD');
          // Usar la fecha normalizada como clave y almacenar el valor validado (puede ser null)
          allDays[normalizedDate] = inventoryNum;

          // Almacenar información de bloqueo usando el campo locked
          if (day.locked !== undefined && day.locked !== null) {
            blockedDays[normalizedDate] = day.locked === 1;
            // Almacenar el valor numérico de locked para poder verificar locked === 0
            lockedDays[normalizedDate] = day.locked;
          }

          // Si está bloqueado (locked === 1), agregarlo a highlightedDays usando el día del mes
          if (day.locked === 1) {
            const date = moment(day.date);
            const dayNumber = parseInt(date.format('DD'));
            if (!highlightedDays.includes(dayNumber)) {
              highlightedDays.push(dayNumber);
            }
          }
        });
      });

      return {
        id: detail.room_id,
        type: detail.room_name,
        occupancy: detail.occupancy,
        rate: detail.rate_name,
        price: detail.price ?? null, // Precio de la tarifa
        channel_mark: detail.channel_mark,
        rates_plans_type_id: detail.rates_plans_type_id, // ID del tipo de plan de tarifa
        availability: allDays,
        blockedDays, // Días bloqueados (locked === 1)
        lockedDays, // Valores de locked para cada día
        highlightedDays,
      };
    });

    // Determinar la conexión basada en channel_mark
    let connection = 'both';
    if (hotelData.channel_mark === 'hyperguest') {
      connection = 'hyperguest';
    } else if (hotelData.channel_mark === 'aurora') {
      connection = 'aurora';
    }

    return {
      id: hotelData.hotel_id,
      connection,
      preferente: hotelData.preferente === 1,
      hotelName: hotelData.name,
      category: hotelData.category,
      chain: hotelData.chain,
      status: hotelData.inventory_status,
      status_porcent: hotelData.inventory_status_porcent,
      release: '15 días', // TODO: Obtener del API si está disponible
      quota: `${hotelData.cupos} dobles`,
      hasError: false, // TODO: Determinar si hay errores
      rooms: transformedRooms,
      header: hotelData.header,
    };
  };

  // Obtener los períodos disponibles para un hotel específico desde su header
  const getAvailablePeriods = (hotelId: number): string[] => {
    const hotel = tableData.value.find((h) => h.id === hotelId);
    if (!hotel || !hotel.header || hotel.header.length === 0) {
      return [moment().format('MMMM YYYY')];
    }

    // Convertir los períodos del header (formato "YYYY-MM") a formato "MMMM YYYY"
    return hotel.header.map((headerItem) => {
      const periodMoment = moment(headerItem.period, 'YYYY-MM');
      return periodMoment.format('MMMM YYYY');
    });
  };

  // Resetear todos los índices de período cuando cambie el rango de fechas
  watch(
    () => [filters.value.dateRange.from, filters.value.dateRange.to],
    () => {
      currentPeriodIndexByHotel.value = {};
    }
  );

  // Función para obtener el índice del período de un hotel específico
  const getPeriodIndex = (hotelId: number): number => {
    return currentPeriodIndexByHotel.value[hotelId] || 0;
  };

  // Función para establecer el índice del período de un hotel específico
  const setPeriodIndex = (hotelId: number, index: number) => {
    currentPeriodIndexByHotel.value[hotelId] = index;
  };

  // Generar días del mes basado en el período actual seleccionado para un hotel específico
  const getCurrentMonth = (hotelId: number): string => {
    const periods = getAvailablePeriods(hotelId);
    const periodIndex = getPeriodIndex(hotelId);
    if (periods.length > 0 && periodIndex < periods.length) {
      return periods[periodIndex];
    }
    return periods[0] || moment().format('MMMM YYYY');
  };

  const getMonthDays = (hotelId: number): string[] => {
    const hotel = tableData.value.find((h) => h.id === hotelId);
    if (!hotel || !hotel.header || hotel.header.length === 0) {
      // Si no hay header, usar el mes actual
      const days: string[] = [];
      const daysInMonth = moment().daysInMonth();
      for (let i = 1; i <= daysInMonth; i++) {
        days.push(i.toString().padStart(2, '0'));
      }
      return days;
    }

    // Obtener el índice del período actual para este hotel
    const periodIndex = getPeriodIndex(hotelId);
    const headerPeriod = hotel.header[periodIndex] || hotel.header[0];

    if (!headerPeriod || !headerPeriod.days || headerPeriod.days.length === 0) {
      return [];
    }

    // Obtener el período en formato "YYYY-MM" del header
    const periodStr = headerPeriod.period; // "2025-12"
    const periodMoment = moment(periodStr, 'YYYY-MM');

    // Construir las fechas completas usando los días del header
    const days: string[] = [];
    headerPeriod.days.forEach((dayStr: string) => {
      // dayStr viene como "15", "16", etc.
      const dayNumber = parseInt(dayStr, 10);
      const fullDate = periodMoment.clone().date(dayNumber);
      days.push(fullDate.format('YYYY-MM-DD'));
    });

    return days;
  };

  // Funciones para navegar entre períodos para un hotel específico
  const previousPeriod = (hotelId: number) => {
    const currentIndex = getPeriodIndex(hotelId);
    if (currentIndex > 0) {
      setPeriodIndex(hotelId, currentIndex - 1);
    }
  };

  const nextPeriod = (hotelId: number) => {
    const currentIndex = getPeriodIndex(hotelId);
    const periods = getAvailablePeriods(hotelId);
    if (currentIndex < periods.length - 1) {
      setPeriodIndex(hotelId, currentIndex + 1);
    }
  };

  // Paginación usando directamente los datos del API
  const pagination = computed(() => {
    const paginationData = hotelsRoomsPagination.value;
    return {
      current: paginationData?.current_page || 1,
      pageSize: paginationData?.per_page || 10,
      total: paginationData?.total || 0,
      showSizeChanger: true,
      showTotal: (total: number) => `Total ${total} registros`,
    };
  });

  // Usar directamente los datos transformados (ya vienen paginados del API)
  const tableData = computed(() => {
    if (!hotelsRoomsList.value || hotelsRoomsList.value.length === 0) {
      return [];
    }
    return hotelsRoomsList.value.map(transformHotelData);
  });

  const handlePaginationChange = async (page: number, _pageSize: number) => {
    // Llamar al API con la nueva página manteniendo el ordenamiento actual
    await fetchHotelsRoomsList(page, currentOrderField.value, currentOrderDir.value);
    // Marcar que se ha buscado
    hasSearched.value = true;
  };

  const toggleConnectionSortIcon = async () => {
    // Reiniciar los iconos de Preferente y Estado a su estado inicial
    preferenteSortIcon.value = 'short-wide';
    statusSortIcon.value = 'short-wide';

    // Cambiar el icono
    connectionSortIcon.value =
      connectionSortIcon.value === 'short-wide' ? 'wide-short' : 'short-wide';

    // Determinar la dirección según el icono actual
    // short-wide = asc, wide-short = desc
    const dir = connectionSortIcon.value === 'short-wide' ? 'desc' : 'asc';

    // Actualizar el estado del ordenamiento actual
    currentOrderField.value = 'channel';
    currentOrderDir.value = dir;

    // Obtener la página actual
    const currentPage = hotelsRoomsPagination.value.current_page || 1;

    // Llamar a fetchHotelsRoomsList con order='channel' y la dirección correspondiente
    await fetchHotelsRoomsList(currentPage, 'channel', dir);
  };

  const togglePreferenteSortIcon = async () => {
    // Reiniciar los iconos de Conexion y Estado a su estado inicial
    connectionSortIcon.value = 'short-wide';
    statusSortIcon.value = 'short-wide';

    // Cambiar el icono
    preferenteSortIcon.value =
      preferenteSortIcon.value === 'short-wide' ? 'wide-short' : 'short-wide';

    // Determinar la dirección según el icono actual
    // short-wide = asc, wide-short = desc
    const dir = preferenteSortIcon.value === 'short-wide' ? 'desc' : 'asc';

    // Actualizar el estado del ordenamiento actual
    currentOrderField.value = 'preferente';
    currentOrderDir.value = dir;

    // Obtener la página actual
    const currentPage = hotelsRoomsPagination.value.current_page || 1;

    // Llamar a fetchHotelsRoomsList con order='preferente' y la dirección correspondiente
    await fetchHotelsRoomsList(currentPage, 'preferente', dir);
  };

  const toggleStatusSortIcon = async () => {
    // Reiniciar los iconos de Conexion y Preferente a su estado inicial
    connectionSortIcon.value = 'short-wide';
    preferenteSortIcon.value = 'short-wide';
    // Cambiar el icono
    statusSortIcon.value = statusSortIcon.value === 'short-wide' ? 'wide-short' : 'short-wide';

    // Determinar la dirección según el icono actual
    // short-wide = asc, wide-short = desc
    const dir = statusSortIcon.value === 'short-wide' ? 'desc' : 'asc';

    // Actualizar el estado del ordenamiento actual
    currentOrderField.value = 'status';
    currentOrderDir.value = dir;

    // Obtener la página actual
    const currentPage = hotelsRoomsPagination.value.current_page || 1;

    // Llamar a fetchHotelsRoomsList con order='status' y la dirección correspondiente
    await fetchHotelsRoomsList(currentPage, 'status', dir);
  };

  const toggleCollapse = (hotelId: number) => {
    const idString = String(hotelId);
    const index = activeKeys.value.indexOf(idString);
    if (index > -1) {
      activeKeys.value.splice(index, 1);
    } else {
      activeKeys.value.push(idString);
    }
  };

  const getStatusClass = (status: string) => {
    switch (status) {
      case 'Disponible':
        return 'disponible';
      case 'Mínima':
        return 'minima';
      default:
        return 'agotado';
    }
  };

  const getStatusConfig = (status: string) => {
    switch (status) {
      case 'Disponible':
        return {
          icon: 'check',
          backgroundColor: '#DFFFE9',
          textColor: '#246337',
        };
      case 'Mínima':
        return {
          icon: 'circle-minus',
          backgroundColor: '#FFFBDB',
          textColor: '#E4B804',
        };
      default:
        return {
          icon: 'warning',
          backgroundColor: '#FFE1E1',
          textColor: '#BD0D12',
        };
    }
  };

  // Función para obtener la disponibilidad de un día específico
  const getAvailabilityForDay = (availability: Record<string, number | null>, dayStr: string) => {
    if (!availability || !dayStr) {
      return '';
    }

    // dayStr viene en formato 'YYYY-MM-DD' de getMonthDays
    // Asegurarse de que la fecha esté en el formato correcto
    const dateKey = moment(dayStr).format('YYYY-MM-DD');

    // Buscar en availability usando la clave formateada
    const value = availability[dateKey];

    // Si no se encuentra, intentar buscar con la fecha original (por si acaso hay diferencias de formato)
    if (value === undefined && availability[dayStr] !== undefined) {
      return availability[dayStr] ?? '-';
    }

    return value ?? '-';
  };

  // Función para determinar el color de la línea indicadora basándose en los valores de disponibilidad
  const getRoomIndicatorColor = (
    availability: Record<string, number | null>,
    lockedDays?: Record<string, number>
  ): string | null => {
    if (!availability || Object.keys(availability).length === 0) {
      return null;
    }

    // Filtrar solo los días donde locked === 0
    let filteredAvailability: Record<string, number | null> = {};

    if (lockedDays) {
      // Solo incluir días donde locked === 0
      Object.keys(availability).forEach((dateKey) => {
        if (lockedDays[dateKey] === 0) {
          filteredAvailability[dateKey] = availability[dateKey];
        }
      });
    } else {
      // Si no hay lockedDays, usar toda la disponibilidad
      filteredAvailability = availability;
    }

    // Si no hay días con locked === 0, retornar null
    if (Object.keys(filteredAvailability).length === 0) {
      return null;
    }

    // Convertir todos los valores filtrados a números y filtrar valores inválidos (null, undefined, NaN)
    const values = Object.values(filteredAvailability)
      .map((val) => {
        // Si es null o undefined, retornar null
        if (val === null || val === undefined) {
          return null;
        }
        // Convertir a número si es string o mantener como número
        const num = typeof val === 'string' ? parseInt(val, 10) : val;
        // Retornar solo si es un número válido (no NaN)
        return !isNaN(num) ? num : null;
      })
      .filter((val): val is number => val !== null);

    // Si no hay valores válidos, retornar null
    if (values.length === 0) {
      return null;
    }

    // Verificar si existe algún valor 0 (no puede existir zero según el requerimiento)
    const hasZero = values.some((val) => val === 0);
    // Verificar si contiene valor 1
    const hasOne = values.some((val) => val === 1);
    // Verificar si contiene valor 2
    const hasTwo = values.some((val) => val === 2);

    // Si existe algún valor 0, retorna rojo
    if (hasZero) {
      return '#A71216';
    }

    // Si contiene valores 1 o 2, retorna amarillo
    if (hasOne || hasTwo) {
      return '#E4B804';
    }

    // Si no cumple ninguna condición, retorna null
    return null;
  };

  return {
    // Estado de UI
    connectionSortIcon,
    preferenteSortIcon,
    statusSortIcon,
    activeKeys,
    hasSearched,

    // Datos del composable compartido
    hotelsRoomsList,
    hotelsRoomsPagination,
    fetchHotelsRoomsList,
    filters,
    isLoadingHotelsRooms,

    // Computed
    pagination,
    tableData,

    // Funciones
    handlePaginationChange,
    toggleConnectionSortIcon,
    togglePreferenteSortIcon,
    toggleStatusSortIcon,
    toggleCollapse,
    getStatusClass,
    getStatusConfig,
    getAvailabilityForDay,
    getRoomIndicatorColor,
    getCurrentMonth,
    getMonthDays,
    getAvailablePeriods,
    previousPeriod,
    nextPeriod,
    getPeriodIndex,
  };
};
