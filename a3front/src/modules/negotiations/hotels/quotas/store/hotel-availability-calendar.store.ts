import { defineStore } from 'pinia';
import { ref } from 'vue';
import type { CalendarDayData } from '@/modules/negotiations/hotels/quotas/interfaces/quotas.interface';

export const useHotelAvailabilityCalendarStore = defineStore(
  'hotelAvailabilityCalendarStore',
  () => {
    const calendarData = ref<CalendarDayData[]>([]);

    const setCalendarData = (data: CalendarDayData[]) => {
      calendarData.value = data;
    };

    const getDayData = (date: string): CalendarDayData | undefined => {
      return calendarData.value.find((item) => item.date === date);
    };

    const getDayTotals = (date: string) => {
      const dayData = getDayData(date);
      return dayData?.totals || null;
    };

    const getDayDetailsByState = (date: string, state: 'bloqueado' | 'disponible' | 'agotado') => {
      const dayData = getDayData(date);
      if (!dayData) return [];
      return dayData.details.filter((detail) => detail.state_group === state);
    };

    const clearCalendarData = () => {
      calendarData.value = [];
    };

    return {
      calendarData,
      setCalendarData,
      getDayData,
      getDayTotals,
      getDayDetailsByState,
      clearCalendarData,
    };
  }
);
