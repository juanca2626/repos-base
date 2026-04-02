import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useHotelAvailabilityRoomsStore = defineStore('hotelAvailabilityRoomsStore', () => {
  const availableRooms = ref<string | number>(0);
  const soldoutRooms = ref<string | number>(0);
  const blockedRooms = ref<string | number>(0);
  const totalRooms = ref<string | number>(0);

  // Totales de búsqueda (hoteles y habitaciones encontrados)
  const totalHotels = ref<number>(0);
  const totalRoomsCount = ref<number>(0);

  const setAvailableRooms = (value: string | number) => {
    availableRooms.value = value;
  };

  const setSoldoutRooms = (value: string | number) => {
    soldoutRooms.value = value;
  };

  const setBlockedRooms = (value: string | number) => {
    blockedRooms.value = value;
  };

  const setTotalRooms = (value: string | number) => {
    totalRooms.value = value;
  };

  const setRoomsStats = (stats: {
    availableRooms: string | number;
    soldoutRooms: string | number;
    blockedRooms: string | number;
    totalRooms: string | number;
  }) => {
    availableRooms.value = stats.availableRooms;
    soldoutRooms.value = stats.soldoutRooms;
    blockedRooms.value = stats.blockedRooms;
    totalRooms.value = stats.totalRooms;
  };

  const setTotalHotels = (value: number) => {
    totalHotels.value = value;
  };

  const setTotalRoomsCount = (value: number) => {
    totalRoomsCount.value = value;
  };

  const setSearchTotals = (totals: { totalHotels: number; totalRoomsCount: number }) => {
    totalHotels.value = totals.totalHotels;
    totalRoomsCount.value = totals.totalRoomsCount;
  };

  const resetRoomsStats = () => {
    availableRooms.value = 0;
    soldoutRooms.value = 0;
    blockedRooms.value = 0;
    totalRooms.value = 0;
  };

  const resetSearchTotals = () => {
    totalHotels.value = 0;
    totalRoomsCount.value = 0;
  };

  const resetAll = () => {
    resetRoomsStats();
    resetSearchTotals();
  };

  return {
    availableRooms,
    soldoutRooms,
    blockedRooms,
    totalRooms,
    totalHotels,
    totalRoomsCount,
    setAvailableRooms,
    setSoldoutRooms,
    setBlockedRooms,
    setTotalRooms,
    setRoomsStats,
    setTotalHotels,
    setTotalRoomsCount,
    setSearchTotals,
    resetRoomsStats,
    resetSearchTotals,
    resetAll,
  };
});
