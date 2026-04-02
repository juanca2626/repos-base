import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import {
  createLockApi,
  deleteLocksApi,
  fetchBlockingReasons,
  fetchHeadquarters,
  fetchLocksByMonth,
  fetchLocksByProvidersAndSelectedDaysApi,
  fetchProviders,
  updateLockApi,
} from '@/modules/operations/modules/blackout-calendar/api/blackoutCalendarApi';
import { formatDate } from '@operations/modules/blackout-calendar/utils/dateUtils';
import type {
  BlockingReasonsResponse,
  Headquarter,
  LocationsResponse,
  MonthInfo,
  ProviderContractType,
  ProviderProfileType,
  ProvidersResponse,
} from '../interfaces';
import { useSelectedDaysStore } from './selectedDays.store';
import type { Dayjs } from 'dayjs';

export const useBlockCalendarStore = defineStore('blockCalendar', () => {
  const modalData = ref();
  const locksByMonth = ref([]);
  const locations = ref<LocationsResponse['data']>([]);
  const blockingReasons = ref<BlockingReasonsResponse['data']>([]);
  const providers = ref<ProvidersResponse['data']>([]);
  const locksByProvidersAndSelectedDates = ref([]);
  const daysInMonth = ref<number>(0);

  const monthInfo = ref<MonthInfo>({
    month: 0,
    year: 0,
    daysInMonth: 0,
    firstDayOfMonth: {
      dayNumber: 0,
      dayName: '',
    },
  });

  const getLocations = async () => {
    try {
      const response = await fetchHeadquarters();
      locations.value = response.data;
    } catch (error) {
      console.error('🚀 ~ getLocations ~ error:', error);
    }
  };

  const getBlockingReasons = async () => {
    try {
      const response = await fetchBlockingReasons();
      blockingReasons.value = response?.data;
    } catch (error) {
      console.error('🚀 ~ getBlockingReasons ~ error:', error);
      // Implementar una notificación de error aquí si es necesario
    }
  };

  const getLocksByMonth = async (payload: {
    monthYear: Dayjs;
    contractProvider: ProviderContractType;
    profileProvider: ProviderProfileType;
    headquarter: Headquarter;
    searchTerm: string;
  }) => {
    try {
      const month = payload.monthYear.format('MM');
      const year = payload.monthYear.format('YYYY');
      const { contractProvider, profileProvider, headquarter, searchTerm } = payload;

      const response = await fetchLocksByMonth({
        month,
        year,
        contractProvider: contractProvider.iso,
        profileProvider: profileProvider.iso,
        headquarter: headquarter.code,
        searchTerm,
      });
      console.log('🚀 ~ useBlockCalendarStore ~ response:', response);
      locksByMonth.value = response.data;
    } catch (error) {
      console.error('🚀 ~ getLocksByMonth ~ error:', error);
    }
  };

  const getProviders = async (searchText: string) => {
    try {
      const response = await fetchProviders(searchText);
      providers.value = response.data;
    } catch (error) {
      console.error('🚀 ~ getProviders ~ error:', error);
      // Implementar una notificación de error aquí si es necesario
    }
  };

  const createLock = async (lockData: any) => {
    try {
      const response = await createLockApi(lockData);
      console.log('🚀 ~ createLock ~ response:', response);
      // if (response) {
      //   notification.success({
      //     message: 'Proceso de creación de bloqueo(s)',
      //     description: 'El bloqueo(s) se ha(n) creado(s) exitosamente.',
      //     duration: 3, // Duración en segundos
      //   });
      // }
      return response;
    } catch (error) {
      console.error(error);
      throw error;
    }
  };

  const updateLock = async (lockId: string, updateLockData: any) => {
    try {
      const response = await updateLockApi(lockId, updateLockData);
      return response; // Return the created lock data
    } catch (error) {
      throw error;
    }
  };

  const deleteLocks = async (locks: any) => {
    try {
      const response = await deleteLocksApi(locks);
      return response; // Return the created lock data
    } catch (error) {
      throw error;
    }
  };

  const fetchLocksByProvidersAndSelectedDays = async (payload) => {
    try {
      const response = await fetchLocksByProvidersAndSelectedDaysApi(payload);
      locksByProvidersAndSelectedDates.value = response.data;
    } catch (error) {
      throw error;
    }
  };

  const getDaysInMonth = (month: number, year: number) => {
    const date = new Date(year, month - 1, 0);
    return (daysInMonth.value = date.getDate());
  };

  const getDayNames = (date: Date): string => {
    const daysOfWeek = ['Dom.', 'Lun.', 'Mar.', 'Mie.', 'Jue.', 'Vie.', 'Sab.'];
    return daysOfWeek[date.getDay()];
  };

  const getMonthInfo = (month: number, year: number): MonthInfo => {
    const firstDayOfMonth = new Date(year, month - 1, 1); // Restar 1 al mes aquí
    const daysInMonth = new Date(year, month, 0).getDate();
    monthInfo.value = {
      month,
      year,
      daysInMonth,
      firstDayOfMonth: {
        dayNumber: firstDayOfMonth.getDate(),
        dayName: getDayNames(firstDayOfMonth),
      },
    };
    return monthInfo.value;
  };

  const setModalData = (provider, lock) => {
    modalData.value = { provider, lock };
  };

  const handleClickDayCalendar = async (item, day) => {
    const selectedDaysStore = useSelectedDaysStore();
    const { provider, locks } = item;
    const formatDay = formatDate(day);
    const { _id: providerId } = provider;

    if (!(formatDay in locks)) return;

    const tempSelectedDays = JSON.parse(JSON.stringify(selectedDaysStore.selectedDays));
    updateSelectedDays(tempSelectedDays, providerId, formatDay, locks);

    if (tempSelectedDays.length > 0) {
      try {
        await fetchLocksByProvidersAndSelectedDays(tempSelectedDays);
        selectedDaysStore.updateSelectedDays(tempSelectedDays);
        updateLocksByMonth(providerId, formatDay);
      } catch (error) {
        console.error('Error fetching locks by providers and selected days:', error);
      }
    }
  };

  const updateSelectedDays = (selectedDays, providerId, formatDay, locks) => {
    const selectedDay = selectedDays.find((day) => day.provider_id === providerId);
    console.log(locks);
    if (selectedDay) {
      if (!selectedDay.days.includes(formatDay)) {
        selectedDay.days.push(formatDay);
      } else {
        selectedDay.days = selectedDay.days.filter((day) => day !== formatDay);
        if (selectedDay.days.length === 0) {
          locksByProvidersAndSelectedDates.value = locksByProvidersAndSelectedDates.value.filter(
            (day) => day.provider._id !== providerId
          );
        }
      }
    } else {
      selectedDays.push({
        provider_id: providerId,
        days: [formatDay],
      });
    }
  };

  const updateLocksByMonth = (providerId, formatDay) => {
    const providerLocks = locksByMonth.value.find((lock) => lock.provider._id === providerId);
    if (providerLocks) {
      const lock = providerLocks.locks[formatDay];
      if (lock) {
        lock.selected = !lock.selected;
      }
    }
  };

  return {
    // Properties
    modalData,
    locations,
    locksByMonth,
    blockingReasons,
    providers,
    locksByProvidersAndSelectedDates,
    daysInMonth,
    monthInfo,

    // Getters
    locationsList: computed(() => [...locations.value]),
    locksByMonthList: computed(() => [...locksByMonth.value]),
    blockingReasonsList: computed(() => [...blockingReasons.value]),
    providersList: computed(() => [...providers.value]),

    // Actions
    setModalData,
    getLocations,
    getLocksByMonth,
    getBlockingReasons,
    getProviders,
    createLock,
    updateLock,
    deleteLocks,
    fetchLocksByProvidersAndSelectedDays,
    getDaysInMonth,
    getMonthInfo,
    getDayNames,
    handleClickDayCalendar,
    // Llama a deselectAllDays pasando locksByMonth y locksByProvidersAndSelectedDates como argumentos
    deselectAllDays: () => {
      const selectedDaysStore = useSelectedDaysStore();
      selectedDaysStore.deselectAllDays(locksByMonth.value, locksByProvidersAndSelectedDates);
    },
  };
});
