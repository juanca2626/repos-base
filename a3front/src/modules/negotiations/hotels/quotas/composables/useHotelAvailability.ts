import { ref, watch, nextTick, reactive, computed } from 'vue';
import moment from 'moment';
import hotelsApi from '@/modules/negotiations/hotels/api/hotelsApi';
import { useHotelAvailabilityFilterStore } from '@/modules/negotiations/hotels/quotas/store/hotel-availability-filter.store';
import { useHotelAvailabilityLoadingStore } from '@/modules/negotiations/hotels/quotas/store/hotel-availability-loading.store';
import { useHotelAvailabilityRoomsStore } from '@/modules/negotiations/hotels/quotas/store/hotel-availability-rooms.store';
import { useHotelAvailabilityChartStore } from '@/modules/negotiations/hotels/quotas/store/hotel-availability-chart.store';
import { useQuotasStore } from '@/modules/negotiations/hotels/quotas/store/quotas.store';
import { useHotelAvailabilityChart } from './useHotelAvailabilityChart';
import { useHotelAvailabilityCalendar } from './useHotelAvailabilityCalendar';
import type {
  FilterOption,
  DateRange,
  HotelAvailabilityFilters,
  DestinationsResponse,
  ChannelResponse,
  ChainResponse,
  StarsResponse,
  RoomTypeResponse,
  HotelsRoomsListResponse,
  HotelsRoomsListTotalsResponse,
  HotelRoomData,
} from '@/modules/negotiations/hotels/quotas/interfaces/quotas.interface';

// Variables compartidas fuera del composable para evitar múltiples peticiones
let connectionsLoadingPromise: Promise<FilterOption[]> | null = null;
let sharedConnections: FilterOption[] = [];
let connectionsLoaded = false;

let destinationLoadingPromise: Promise<FilterOption[]> | null = null;
let sharedDestinations: FilterOption[] = [];
let destinationLoaded = false;

let hotelChainsLoadingPromise: Promise<FilterOption[]> | null = null;
let sharedHotelChains: FilterOption[] = [];
let hotelChainsLoaded = false;

let rateTypesLoadingPromise: Promise<FilterOption[]> | null = null;
let sharedRateTypes: FilterOption[] = [];
let rateTypesLoaded = false;

let hotelCategoriesLoadingPromise: Promise<FilterOption[]> | null = null;
let sharedHotelCategories: FilterOption[] = [];
let hotelCategoriesLoaded = false;

let roomTypesLoadingPromise: Promise<FilterOption[]> | null = null;
let sharedRoomTypes: FilterOption[] = [];
let roomTypesLoaded = false;

let calendarLoadingPromise: Promise<any> | null = null;

// Variables compartidas para hotelsRoomsList (usando reactive para que sean observables)
const hotelsRoomsListShared = reactive<HotelRoomData[]>([]);
const hotelsRoomsPaginationShared = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
  from: 0,
  to: 0,
});
let calendarLoadingKey: string | null = null;

// Función para calcular las fechas iniciales
const getInitialDateRange = (): DateRange => {
  const today = moment();
  // Primer día del mes actual
  const dateFrom = today.clone().startOf('month').format('YYYY-MM-DD');
  // Último día del mes que está 6 meses después
  const dateTo = today.clone().add(6, 'months').endOf('month').format('YYYY-MM-DD');
  return {
    from: dateFrom,
    to: dateTo,
  };
};

// Variables compartidas para filters y availabilityData (para que sean compartidas entre todas las instancias)
const filtersShared = ref<HotelAvailabilityFilters>({
  country: null,
  city: null,
  destination: null,
  internalCategory: null,
  connection: null,
  dateRange: getInitialDateRange(),
});

const availabilityDataShared = ref<Record<string, string>>({});

// Flags compartidos para evitar múltiples búsquedas automáticas iniciales
let initialAutoSearchDone = false;
let initialAutoSearchInProgress = false;

export const useHotelAvailability = () => {
  // Usar las referencias compartidas
  const filters = filtersShared;
  const availabilityData = availabilityDataShared;

  // Usar el store para los totales (persistencia entre navegaciones)
  const roomsStore = useHotelAvailabilityRoomsStore();

  // Usar el store para las categorías internas
  const quotasStore = useQuotasStore();

  // Usar una referencia reactiva que se sincroniza con los destinos
  const destinations = ref<FilterOption[]>(sharedDestinations);
  const isLoadingDestinations = ref<boolean>(true);

  // Función para obtener los destinos desde el API
  const fetchDestinations = async (lang: string = 'es') => {
    const loadingStore = useHotelAvailabilityLoadingStore();

    // Si ya hay una petición en curso, esperar a que termine y sincronizar
    if (destinationLoadingPromise) {
      const result = await destinationLoadingPromise;
      destinations.value = result;
      isLoadingDestinations.value = false;
      return;
    }

    // Si ya se cargaron los países, sincronizar y retornar
    if (destinationLoaded && sharedDestinations.length > 0) {
      destinations.value = sharedDestinations;
      isLoadingDestinations.value = false;
      return;
    }

    isLoadingDestinations.value = true;
    loadingStore.startRequest('destinations');

    // Crear la promesa compartida
    destinationLoadingPromise = (async (): Promise<FilterOption[]> => {
      try {
        const response = await hotelsApi.get<{ success: boolean; data: DestinationsResponse[] }>(
          '/services/hotels/allotment/destinations',
          {
            params: { lang },
          }
        );

        // Transformar la respuesta al formato FilterOption
        const destinationsData = response.data.data || response.data;
        const destinationsArray = Array.isArray(destinationsData) ? destinationsData : [];

        const mappedDestinations = destinationsArray.map((item: DestinationsResponse) => ({
          label: item.label,
          code: item.code.toString(),
        }));

        // Almacenar en variable compartida
        sharedDestinations = mappedDestinations;
        destinationLoaded = true;
        return mappedDestinations;
      } catch (error) {
        console.error('Error fetching destinations:', error);
        // Mantener valores por defecto en caso de error
        const defaultDestinations: FilterOption[] = [];
        sharedDestinations = defaultDestinations;
        destinationLoaded = false;
        return defaultDestinations;
      } finally {
        isLoadingDestinations.value = false;
        loadingStore.endRequest('destinations');
        destinationLoadingPromise = null;
      }
    })();

    const result = await destinationLoadingPromise;
    destinations.value = result;
  };

  // Usar el store para las categorías internas
  const internalCategories = computed(() => quotasStore.internalCategories);
  const isLoadingCategories = computed(() => quotasStore.isLoadingCategories);
  const fetchInternalCategories = quotasStore.fetchInternalCategories;

  // Usar una referencia reactiva que se sincroniza con las conexiones compartidas
  const connections = ref<FilterOption[]>(sharedConnections);
  const isLoadingConnections = ref<boolean>(true);

  // Función para obtener las conexiones desde el API
  const fetchConnections = async () => {
    const loadingStore = useHotelAvailabilityLoadingStore();

    // Si ya hay una petición en curso, esperar a que termine y sincronizar
    if (connectionsLoadingPromise) {
      const result = await connectionsLoadingPromise;
      connections.value = result;
      isLoadingConnections.value = false;
      return;
    }

    // Si ya se cargaron las conexiones, sincronizar y retornar
    if (connectionsLoaded && sharedConnections.length > 0) {
      connections.value = sharedConnections;
      isLoadingConnections.value = false;
      return;
    }

    isLoadingConnections.value = true;
    loadingStore.startRequest('connections');

    // Crear la promesa compartida
    connectionsLoadingPromise = (async (): Promise<FilterOption[]> => {
      try {
        const response = await hotelsApi.get<{ success: boolean; data: ChannelResponse[] }>(
          '/api/channels/selected'
        );

        // Transformar la respuesta al formato FilterOption
        const connectionsData = response.data.data || response.data;
        const connectionsArray = Array.isArray(connectionsData) ? connectionsData : [];

        const mappedConnections = connectionsArray.map((item: ChannelResponse) => ({
          label: item.text,
          code: item.value.toString(),
        }));

        // Almacenar en variable compartida
        sharedConnections = mappedConnections;
        connectionsLoaded = true;
        return mappedConnections;
      } catch (error) {
        console.error('Error fetching connections:', error);
        // Mantener valores por defecto en caso de error
        const defaultConnections: FilterOption[] = [];
        sharedConnections = defaultConnections;
        connectionsLoaded = false;
        return defaultConnections;
      } finally {
        isLoadingConnections.value = false;
        loadingStore.endRequest('connections');
        connectionsLoadingPromise = null;
      }
    })();

    const result = await connectionsLoadingPromise;
    connections.value = result;
  };

  // Usar una referencia reactiva que se sincroniza con las cadenas de hoteles compartidas
  const hotelChains = ref<FilterOption[]>(sharedHotelChains);
  const isLoadingHotelChains = ref<boolean>(false);

  // Función para obtener las cadenas de hoteles desde el API
  const fetchHotelChains = async () => {
    const loadingStore = useHotelAvailabilityLoadingStore();

    // Si ya hay una petición en curso, esperar a que termine y sincronizar
    if (hotelChainsLoadingPromise) {
      const result = await hotelChainsLoadingPromise;
      hotelChains.value = result;
      return;
    }

    // Si ya se cargaron las cadenas, sincronizar y retornar
    if (hotelChainsLoaded && sharedHotelChains.length > 0) {
      hotelChains.value = sharedHotelChains;
      return;
    }

    isLoadingHotelChains.value = true;
    loadingStore.startRequest('hotelChains');

    // Crear la promesa compartida
    hotelChainsLoadingPromise = (async (): Promise<FilterOption[]> => {
      try {
        const response = await hotelsApi.get<{ success: boolean; data: ChainResponse[] }>(
          '/api/chain/selectbox'
        );

        // Transformar la respuesta al formato FilterOption
        const chainsData = response.data.data || response.data;
        const chainsArray = Array.isArray(chainsData) ? chainsData : [];

        const mappedChains = chainsArray.map((item: ChainResponse) => ({
          label: item.label,
          code: item.code.toString(),
        }));

        // Almacenar en variable compartida
        sharedHotelChains = mappedChains;
        hotelChainsLoaded = true;
        return mappedChains;
      } catch (error) {
        console.error('Error fetching hotel chains:', error);
        // Mantener valores por defecto en caso de error
        const defaultChains: FilterOption[] = [];
        sharedHotelChains = defaultChains;
        hotelChainsLoaded = false;
        return defaultChains;
      } finally {
        isLoadingHotelChains.value = false;
        loadingStore.endRequest('hotelChains');
        hotelChainsLoadingPromise = null;
      }
    })();

    const result = await hotelChainsLoadingPromise;
    hotelChains.value = result;
  };

  // Usar una referencia reactiva que se sincroniza con las categorías de hoteles compartidas
  const hotelCategories = ref<FilterOption[]>(sharedHotelCategories);
  const isLoadingHotelCategories = ref<boolean>(false);

  // Función para obtener las categorías de hoteles desde el API
  const fetchHotelCategories = async () => {
    const loadingStore = useHotelAvailabilityLoadingStore();

    // Si ya hay una petición en curso, activar loading y esperar a que termine
    if (hotelCategoriesLoadingPromise) {
      isLoadingHotelCategories.value = true;
      const result = await hotelCategoriesLoadingPromise;
      hotelCategories.value = result;
      isLoadingHotelCategories.value = false;
      return;
    }

    // Si ya se cargaron las categorías, sincronizar y retornar
    if (hotelCategoriesLoaded && sharedHotelCategories.length > 0) {
      hotelCategories.value = sharedHotelCategories;
      return;
    }

    isLoadingHotelCategories.value = true;
    loadingStore.startRequest('hotelCategories');

    // Crear la promesa compartida
    hotelCategoriesLoadingPromise = (async (): Promise<FilterOption[]> => {
      try {
        const response = await hotelsApi.get<{ success: boolean; data: StarsResponse[] }>(
          '/api/stars'
        );

        // Transformar la respuesta al formato FilterOption
        const categoriesData = response.data.data || response.data;
        const categoriesArray = Array.isArray(categoriesData) ? categoriesData : [];

        const mappedCategories = categoriesArray.map((item: StarsResponse, index: number) => ({
          label: item.description,
          code: item.id.toString() + '-' + index.toString(), // Usar índice para garantizar unicidad
          originalId: item.id.toString(), // Guardar el ID original para usar en las peticiones
        }));

        // Almacenar en variable compartida
        sharedHotelCategories = mappedCategories;
        hotelCategoriesLoaded = true;
        return mappedCategories;
      } catch (error) {
        console.error('Error fetching hotel categories:', error);
        // Mantener valores por defecto en caso de error
        const defaultCategories: FilterOption[] = [];
        sharedHotelCategories = defaultCategories;
        hotelCategoriesLoaded = false;
        return defaultCategories;
      } finally {
        isLoadingHotelCategories.value = false;
        loadingStore.endRequest('hotelCategories');
        hotelCategoriesLoadingPromise = null;
      }
    })();

    const result = await hotelCategoriesLoadingPromise;
    hotelCategories.value = result;
  };

  // Usar una referencia reactiva que se sincroniza con los tipos de tarifa compartidos
  const rateTypes = ref<FilterOption[]>(sharedRateTypes);
  const isLoadingRateTypes = ref<boolean>(false);

  // Función para obtener los tipos de tarifa desde el API
  const fetchRateTypes = async (lang: string = 'es') => {
    const loadingStore = useHotelAvailabilityLoadingStore();

    // Si ya hay una petición en curso, activar loading y esperar a que termine
    if (rateTypesLoadingPromise) {
      isLoadingRateTypes.value = true;
      const result = await rateTypesLoadingPromise;
      rateTypes.value = result;
      isLoadingRateTypes.value = false;
      return;
    }

    // Si ya se cargaron los tipos de tarifa, sincronizar y retornar
    if (rateTypesLoaded && sharedRateTypes.length > 0) {
      rateTypes.value = sharedRateTypes;
      return;
    }

    isLoadingRateTypes.value = true;
    loadingStore.startRequest('rateTypes');

    // Crear la promesa compartida
    rateTypesLoadingPromise = (async (): Promise<FilterOption[]> => {
      try {
        const response = await hotelsApi.get<{ success: boolean; data: ChannelResponse[] }>(
          '/api/ratesplanstypes/selectBox',
          {
            params: { lang },
          }
        );

        // Transformar la respuesta al formato FilterOption
        const rateTypesData = response.data.data || response.data;
        const rateTypesArray = Array.isArray(rateTypesData) ? rateTypesData : [];

        const mappedRateTypes = rateTypesArray.map((item: ChannelResponse) => ({
          label: item.text,
          code: item.value.toString(),
        }));

        // Almacenar en variable compartida
        sharedRateTypes = mappedRateTypes;
        rateTypesLoaded = true;
        return mappedRateTypes;
      } catch (error) {
        console.error('Error fetching rate types:', error);
        // Mantener valores por defecto en caso de error
        const defaultRateTypes: FilterOption[] = [];
        sharedRateTypes = defaultRateTypes;
        rateTypesLoaded = false;
        return defaultRateTypes;
      } finally {
        isLoadingRateTypes.value = false;
        loadingStore.endRequest('rateTypes');
        rateTypesLoadingPromise = null;
      }
    })();

    const result = await rateTypesLoadingPromise;
    rateTypes.value = result;
  };

  // Estado para resumen de disponibilidad
  const totalRooms = ref<string | number>(0);
  const bedType = ref<string | null>(null);

  // Usar el store para los totales (persistencia entre navegaciones)
  // Estos valores se sincronizan con el store para mantenerlos entre navegaciones
  const availableRooms = computed({
    get: () => roomsStore.availableRooms,
    set: (value) => roomsStore.setAvailableRooms(value),
  });
  const soldoutRooms = computed({
    get: () => roomsStore.soldoutRooms,
    set: (value) => roomsStore.setSoldoutRooms(value),
  });
  const blockedRooms = computed({
    get: () => roomsStore.blockedRooms,
    set: (value) => roomsStore.setBlockedRooms(value),
  });

  // Estado para vista y resultados
  const activeView = ref<'table' | 'calendar'>('table');

  // Usar el store para los totales de búsqueda (persistencia entre navegaciones)
  const totalHotels = computed({
    get: () => roomsStore.totalHotels,
    set: (value) => roomsStore.setTotalHotels(value),
  });
  const totalRoomsCount = computed({
    get: () => roomsStore.totalRoomsCount,
    set: (value) => roomsStore.setTotalRoomsCount(value),
  });

  // Estado para la lista de hoteles y habitaciones (compartido)
  const hotelsRoomsList = ref<HotelRoomData[]>(hotelsRoomsListShared);
  // Usar el store para el loading principal
  const loadingStore = useHotelAvailabilityLoadingStore();
  const isLoadingHotelsRooms = computed(() => loadingStore.isLoadingHotelsRooms);
  const isLoadingCalendar = ref<boolean>(false);
  const hotelsRoomsPagination = ref<{
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
  }>(hotelsRoomsPaginationShared);

  // Sincronizar los refs con el estado compartido reactivo cuando cambie
  watch(
    () => hotelsRoomsListShared,
    (newValue) => {
      hotelsRoomsList.value = [...newValue];
    },
    { deep: true, immediate: true }
  );

  watch(
    () => hotelsRoomsPaginationShared,
    (newValue) => {
      hotelsRoomsPagination.value = { ...newValue };
    },
    { deep: true, immediate: true }
  );

  // Sincronizar cambios locales hacia el estado compartido
  watch(
    hotelsRoomsList,
    (newValue) => {
      hotelsRoomsListShared.splice(0, hotelsRoomsListShared.length, ...newValue);
    },
    { deep: true }
  );

  watch(
    hotelsRoomsPagination,
    (newValue) => {
      Object.assign(hotelsRoomsPaginationShared, newValue);
    },
    { deep: true }
  );

  // Opciones de tipos de cama
  const bedTypes = ref<FilterOption[]>([]);

  // Estado para tipo de habitación
  const roomType = ref<string | null>(null);
  const roomTypes = ref<FilterOption[]>(sharedRoomTypes);
  const isLoadingRoomTypes = ref<boolean>(false);

  // Función para obtener los tipos de habitación desde el API
  const fetchRoomTypes = async () => {
    const loadingStore = useHotelAvailabilityLoadingStore();

    // Si ya hay una petición en curso, activar loading y esperar a que termine
    if (roomTypesLoadingPromise) {
      isLoadingRoomTypes.value = true;
      const result = await roomTypesLoadingPromise;
      roomTypes.value = result;
      isLoadingRoomTypes.value = false;
      return;
    }

    // Si ya se cargaron los tipos de habitación, sincronizar y retornar
    if (roomTypesLoaded && sharedRoomTypes.length > 0) {
      roomTypes.value = sharedRoomTypes;
      return;
    }

    isLoadingRoomTypes.value = true;
    loadingStore.startRequest('roomTypes');

    // Crear la promesa compartida
    roomTypesLoadingPromise = (async (): Promise<FilterOption[]> => {
      try {
        const response = await hotelsApi.get<{ success: boolean; data: RoomTypeResponse[] }>(
          '/api/room_types/allotments'
        );

        // Transformar la respuesta al formato FilterOption
        const roomTypesData = response.data.data || response.data;
        const roomTypesArray = Array.isArray(roomTypesData) ? roomTypesData : [];

        const mappedRoomTypes = roomTypesArray.map((item: RoomTypeResponse) => ({
          label: item.description,
          code: item.id.toString(),
          occupation: item.occupation,
        }));

        // Almacenar en variable compartida
        sharedRoomTypes = mappedRoomTypes;
        roomTypesLoaded = true;

        return mappedRoomTypes;
      } catch (error) {
        console.error('Error fetching room types:', error);
        // Mantener valores por defecto en caso de error
        const defaultRoomTypes: FilterOption[] = [];
        sharedRoomTypes = defaultRoomTypes;
        roomTypesLoaded = false;
        return defaultRoomTypes;
      } finally {
        isLoadingRoomTypes.value = false;
        loadingStore.endRequest('roomTypes');
        roomTypesLoadingPromise = null;
      }
    })();

    const result = await roomTypesLoadingPromise;
    roomTypes.value = result;
  };

  // Función para mapear el estado del inventario al formato del calendario
  const mapInventoryStatus = (status: string): string => {
    // Normalizar el status: convertir a mayúscula y eliminar espacios
    const normalizedStatus = (status || '').trim().toUpperCase();

    const statusMap: Record<string, string> = {
      B: 'blocked', // Bloqueado
      I: 'available', // Disponible
      A: 'soldout', // Agotado
    };

    const mapped = statusMap[normalizedStatus];

    return mapped || 'available';
  };

  // Función para obtener los datos del calendario
  const fetchCalendar = async (
    dateFrom?: string,
    dateTo?: string,
    skipLoadingStore: boolean = false
  ) => {
    const filterStore = useHotelAvailabilityFilterStore();

    // Usar los parámetros proporcionados o los del filtro
    const from = dateFrom || filters.value.dateRange.from;
    const to = dateTo || filters.value.dateRange.to;

    if (!from || !to) {
      console.error('❌ fetchCalendar: Las fechas son obligatorias', {
        from,
        to,
      });
      isLoadingCalendar.value = false;
      return;
    }

    // Construir los parámetros para GET
    const params: Record<string, any> = {
      destination: filters.value.destination?.code || '89,1610',
      date_from: from,
      date_to: to,
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

    // Crear una clave única basada en los parámetros para evitar llamadas duplicadas
    const requestKey = JSON.stringify(params);

    // Si ya hay una petición en curso con los mismos parámetros, esperar a que termine
    if (calendarLoadingPromise && calendarLoadingKey === requestKey) {
      await calendarLoadingPromise;
      // No cambiar isLoadingCalendar aquí porque la petición ya está en curso
      return;
    }

    // Crear la promesa compartida
    calendarLoadingKey = requestKey;
    isLoadingCalendar.value = true;
    const loadingStore = useHotelAvailabilityLoadingStore();
    // Solo registrar en el store si no se indica skipLoadingStore (para evitar activar loading general)
    if (!skipLoadingStore) {
      loadingStore.startRequest('calendar');
    }
    calendarLoadingPromise = (async () => {
      try {
        const response = await hotelsApi.get('/api/allotment/hotels/calendar', { params });

        if (response.data && response.data.success && response.data.data) {
          const newAvailabilityData: Record<string, string> = {};

          response.data.data.forEach((item: { date: string; inventory_status_day: string }) => {
            // Normalizar la fecha a formato YYYY-MM-DD para asegurar consistencia
            const normalizedDate = moment(item.date).format('YYYY-MM-DD');
            const mappedStatus = mapInventoryStatus(item.inventory_status_day);

            // Si ya existe un estado para esta fecha, verificar prioridad
            // Prioridad: blocked > soldout > available
            if (newAvailabilityData[normalizedDate]) {
              const existingStatus = newAvailabilityData[normalizedDate];
              const priority: Record<string, number> = {
                blocked: 3,
                soldout: 2,
                available: 1,
              };

              // Solo actualizar si el nuevo estado tiene mayor prioridad
              if ((priority[mappedStatus] || 0) > (priority[existingStatus] || 0)) {
                newAvailabilityData[normalizedDate] = mappedStatus;
                console.log('🔄 Status updated for date:', {
                  date: normalizedDate,
                  oldStatus: existingStatus,
                  newStatus: mappedStatus,
                  inventory_status_day: item.inventory_status_day,
                });
              }
            } else {
              newAvailabilityData[normalizedDate] = mappedStatus;
            }
          });

          // Actualizar availabilityData con los nuevos datos
          // NO limpiar los datos existentes para mantener el cache de meses ya consultados
          // Solo actualizar/agregar las fechas que vienen en la nueva respuesta
          Object.keys(newAvailabilityData).forEach((key) => {
            availabilityData.value[key] = newAvailabilityData[key];
          });
        } else {
          console.warn('⚠️ Calendar response format unexpected:', response.data);
        }

        return response.data;
      } catch (error) {
        console.error('❌ fetchCalendar: Error al obtener el calendario:', error);
        throw error;
      } finally {
        isLoadingCalendar.value = false;
        // Solo finalizar en el store si se registró inicialmente
        if (!skipLoadingStore) {
          loadingStore.endRequest('calendar');
        }
        calendarLoadingPromise = null;
        calendarLoadingKey = null;
      }
    })();

    return await calendarLoadingPromise;
  };

  // Función para obtener los totales de hoteles y habitaciones
  const fetchHotelsRoomsListTotals = async () => {
    const filterStore = useHotelAvailabilityFilterStore();

    // Validar que los campos obligatorios estén presentes
    if (!filters.value.destination?.code) {
      console.error('❌ fetchHotelsRoomsListTotals: El destination es obligatoria');
      return;
    }

    if (!filters.value.dateRange.from || !filters.value.dateRange.to) {
      console.error('❌ fetchHotelsRoomsListTotals: Las fechas son obligatorias');
      return;
    }

    loadingStore.startRequest('hotelsRoomsListTotals');

    // Construir los parámetros para GET (mismos que fetchHotelsRoomsList pero sin paginación)
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
      const response = await hotelsApi.get<HotelsRoomsListTotalsResponse>(
        '/api/allotment/hotels/hotels-rooms-list-totals',
        { params }
      );

      if (
        response.data &&
        response.data.success === 'true' &&
        response.data.data &&
        response.data.data.length > 0
      ) {
        const totals = response.data.data[0];
        // Guardar en el store para persistencia
        roomsStore.setSearchTotals({
          totalHotels: totals.total_hotels,
          totalRoomsCount: totals.total_rooms,
        });
      }

      return response.data;
    } catch (error) {
      console.error('❌ fetchHotelsRoomsListTotals: Error al obtener los totales:', error);
      throw error;
    } finally {
      loadingStore.endRequest('hotelsRoomsListTotals');
    }
  };

  // Función para obtener la lista de hoteles y habitaciones
  const fetchHotelsRoomsList = async (
    page: number = 1,
    order: string = 'preferente',
    dir: string = 'asc',
    skipLoading: boolean = false
  ) => {
    const filterStore = useHotelAvailabilityFilterStore();

    // Validar que los campos obligatorios estén presentes
    if (!filters.value.destination?.code) {
      console.error('❌ fetchHotelsRoomsList: El destination es obligatoria');
      return;
    }

    if (!filters.value.dateRange.from || !filters.value.dateRange.to) {
      console.error('❌ fetchHotelsRoomsList: Las fechas son obligatorias');
      return;
    }

    if (!skipLoading) {
      loadingStore.startRequest('hotelsRoomsList');
    }

    // Construir los parámetros para GET
    const params: Record<string, any> = {
      destination: filters.value.destination.code,
      date_from: filters.value.dateRange.from,
      date_to: filters.value.dateRange.to,
      page: page,
      order: order,
      dir: dir,
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

    // Agregar parámetro status cuando se ordene por preferente o status
    if (order === 'preferente') {
      params.status = 'preferente';
    } else if (order === 'status') {
      params.status = 'status';
    }

    try {
      const response = await hotelsApi.get<HotelsRoomsListResponse>(
        '/api/allotment/hotels/hotels-rooms-list',
        { params }
      );

      // La respuesta tiene la estructura: { current_page, data: [...], total, ... }
      const responseData = response.data as HotelsRoomsListResponse;

      if (responseData && responseData.data) {
        // Actualizar estado compartido reactivo (esto automáticamente actualizará todos los refs)
        hotelsRoomsListShared.splice(0, hotelsRoomsListShared.length, ...responseData.data);
        Object.assign(hotelsRoomsPaginationShared, {
          current_page: responseData.current_page,
          last_page: responseData.last_page,
          per_page: responseData.per_page,
          total: responseData.total,
          from: responseData.from,
          to: responseData.to,
        });
        // Actualizar totalHotels (este es el total de la página actual, no el total general)
        // El total general se obtiene del endpoint de totales
      }

      return response.data;
    } catch (error) {
      console.error('❌ fetchHotelsRoomsList: Error al obtener la lista de hoteles:', error);
      throw error;
    } finally {
      if (!skipLoading) {
        loadingStore.endRequest('hotelsRoomsList');
      }
    }
  };

  const search = async (force: boolean = false) => {
    // Evitar que la búsqueda automática (no forzada) se ejecute más de una vez,
    // incluso si varias instancias del composable disparan el watcher al mismo tiempo.
    if (!force) {
      if (initialAutoSearchDone || initialAutoSearchInProgress) {
        return;
      }
      initialAutoSearchInProgress = true;
    }

    // Limpiar todos los datos antes de realizar la búsqueda
    // Limpiar resumen de disponibilidad
    roomsStore.resetRoomsStats();
    // Limpiar totales de hoteles y habitaciones
    roomsStore.resetSearchTotals();
    // Limpiar datos de la tabla
    hotelsRoomsListShared.splice(0, hotelsRoomsListShared.length);
    // Limpiar datos del gráfico
    const chartStore = useHotelAvailabilityChartStore();
    chartStore.clearChartData();

    // Si hay fechas en el input de periodo, usarlas; si no, usar las fechas iniciales
    const dateFrom = filters.value.dateRange.from || getInitialDateRange().from;
    const dateTo = filters.value.dateRange.to || getInitialDateRange().to;

    filters.value.dateRange = {
      from: dateFrom,
      to: dateTo,
    };

    // Validar que exista destinos y rango de fechas antes de buscar, salvo que se fuerce
    if (
      !filters.value.destination?.code ||
      !filters.value.dateRange.from ||
      !filters.value.dateRange.to
    ) {
      if (!force) {
        console.warn(
          '🔎 search: Faltan filtros obligatorios (destination/fechas). Se omite búsqueda automática.'
        );
        return;
      }
    }

    // Resetear la paginación a la página 1 antes de buscar
    // Actualizar el estado compartido reactivo (los watchers actualizarán los refs)
    Object.assign(hotelsRoomsPaginationShared, {
      current_page: 1,
      last_page: 1,
      per_page: 10,
      total: 0,
      from: 0,
      to: 0,
    });

    // Llamar al nuevo endpoint de disponibilidad usando el composable del gráfico
    // Crear una nueva instancia del composable para la búsqueda
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

    // Ejecutar todos los endpoints en paralelo
    // Cada función registrará su propio estado de loading en el store
    try {
      await Promise.all([
        chartComposableInstance.fetchChartTotals(undefined, false),
        chartComposableInstance.fetchHotelAvailability(undefined, false),
        fetchHotelsRoomsList(1, 'preferente', 'asc', false),
        fetchHotelsRoomsListTotals(),
      ]);
    } catch (error) {
      // Si hay un error, limpiar todas las peticiones activas
      console.error('Error en la búsqueda:', error);
      loadingStore.clearAllRequests();
    } finally {
      // Marcar que la búsqueda automática ya se ejecutó al menos una vez
      if (!force) {
        initialAutoSearchDone = true;
        initialAutoSearchInProgress = false;
      }
    }
  };

  // Función para manejar el click en el botón Calendario
  const handleCalendarViewClick = async () => {
    // Cambiar la vista a calendario
    activeView.value = 'calendar';

    // Crear una instancia del composable del calendario y cargar los datos
    const calendarComposableInstance = useHotelAvailabilityCalendar(filters, hotelCategories);
    await calendarComposableInstance.fetchCalendarDetails(false);
  };

  // Watcher para seleccionar Destino por defecto cuando se carguen los destinos
  watch(
    () => destinations.value,
    async (newDestinations) => {
      if (newDestinations.length > 0 && !filters.value.destination) {
        const limaDestination = newDestinations.find(
          (destination) => destination.code === '89,1610'
        );
        if (limaDestination) {
          // Usar el objeto exacto del array para que vue-select lo reconozca
          filters.value.destination = limaDestination;
          await nextTick();
          // El watcher de filters.destination se encargará de cargar las ciudades
        }
      }
    },
    { immediate: true }
  );

  // Ejecutar búsqueda automática solo cuando ya exista ciudad y rango de fechas
  // (por ejemplo, después de que se seleccione por defecto Perú/Lima y se tenga periodo)
  watch(
    () => ({
      destinationCode: filters.value.destination?.code,
      from: filters.value.dateRange.from,
      to: filters.value.dateRange.to,
    }),
    (current, previous) => {
      const hasDestinationNow = !!current.destinationCode;
      const hadDestinationBefore = !!previous?.destinationCode;
      const hasDatesNow = !!current.from && !!current.to;

      // Solo disparar la primera vez que tengamos ciudad + fechas
      if (hasDestinationNow && hasDatesNow && !hadDestinationBefore) {
        search();
      }
    }
  );

  // Watcher para seleccionar automáticamente el roomType con occupation 2 cuando se cargan los tipos
  watch(
    () => roomTypes.value,
    (newRoomTypes) => {
      if (newRoomTypes && newRoomTypes.length > 0 && !roomType.value) {
        const defaultRoomType = newRoomTypes.find((rt) => {
          const occupation =
            typeof rt.occupation === 'number' ? rt.occupation : parseInt(String(rt.occupation), 10);
          return occupation === 2;
        });
        if (defaultRoomType) {
          nextTick().then(() => {
            roomType.value = defaultRoomType.code;
          });
        }
      }
    },
    { immediate: true }
  );

  // Función para limpiar los datos de disponibilidad
  const clearAvailabilityData = () => {
    availabilityData.value = {};
  };

  // Cargar categorías, conexiones, países, cadenas de hoteles, categorías de hoteles y tipos de tarifa (equivalente a created)
  // La búsqueda inicial se dispara con el watcher anterior cuando los filtros estén listos
  (async () => {
    await fetchDestinations();
    await fetchInternalCategories();
    fetchConnections();
    fetchHotelChains();
    fetchHotelCategories();
    fetchRateTypes();
    fetchRoomTypes();
  })();

  return {
    filters,
    availabilityData,
    destinations,
    isLoadingDestinations,
    internalCategories,
    isLoadingCategories,
    connections,
    isLoadingConnections,
    hotelChains,
    isLoadingHotelChains,
    hotelCategories,
    isLoadingHotelCategories,
    rateTypes,
    isLoadingRateTypes,
    totalRooms,
    bedType,
    bedTypes,
    roomType,
    roomTypes,
    isLoadingRoomTypes,
    availableRooms,
    soldoutRooms,
    blockedRooms,
    activeView,
    totalHotels,
    totalRoomsCount,
    fetchHotelsRoomsListTotals,
    hotelsRoomsList,
    isLoadingHotelsRooms,
    hotelsRoomsPagination,
    isLoadingCalendar,
    search,
    fetchHotelsRoomsList,
    fetchCalendar,
    fetchInternalCategories,
    fetchConnections,
    fetchHotelChains,
    fetchHotelCategories,
    fetchRateTypes,
    fetchRoomTypes,
    clearAvailabilityData,
    fetchDestinations,
    handleCalendarViewClick,
  };
};
