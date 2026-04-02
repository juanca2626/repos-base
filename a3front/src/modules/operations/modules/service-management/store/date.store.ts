// stores/dateStore.ts
import { defineStore } from 'pinia';
import dayjs, { Dayjs } from 'dayjs';
import customParseFormat from 'dayjs/plugin/customParseFormat';
import isSameOrBefore from 'dayjs/plugin/isSameOrBefore';
import 'dayjs/locale/es';

dayjs.extend(customParseFormat);
dayjs.extend(isSameOrBefore);
dayjs.locale('es');

interface DateState {
  date: Dayjs | null;
  time: Dayjs | null;
}

export const useDateStore = defineStore('dateStore', {
  state: (): DateState => ({
    date: null,
    time: null,
  }),

  getters: {
    formattedDate: (state): string => state.date?.format('DD/MM/YYYY') || '',
    formattedTime: (state): string => state.time?.format('HH:mm') || '',
    isValidDate: (state): boolean => {
      if (!state.date) return false;
      return dayjs().isSameOrBefore(state.date, 'day');
    },
  },

  actions: {
    setDate(date: Dayjs | null): void {
      this.date = date;
    },

    setTime(time: Dayjs | null): void {
      this.time = time;
    },

    initializeDateTime(): void {
      const now = dayjs();
      this.date = now;
      this.time = now;
    },

    disabledDate(current: Dayjs): boolean {
      return current && current.isBefore(dayjs().startOf('day'), 'day');
    },

    disabledHours: (selectedDate: Dayjs | null) => {
      if (!selectedDate || !dayjs().isSame(selectedDate, 'day')) {
        return [];
      }

      const currentHour = dayjs().hour();
      const disabled = Array.from({ length: currentHour }, (_, i) => i);
      return disabled;
    },

    disabledMinutes: (selectedDate: Dayjs | null, selectedHour: number) => {
      if (
        !selectedDate ||
        !dayjs().isSame(selectedDate, 'day') ||
        dayjs().hour() !== selectedHour
      ) {
        return [];
      }

      const currentMinute = dayjs().minute();
      const disabled = Array.from({ length: currentMinute }, (_, i) => i);
      return disabled;
    },
  },
});

export type DateStore = ReturnType<typeof useDateStore>;
