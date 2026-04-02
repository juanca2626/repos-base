import { computed, type Ref } from 'vue';
import dayjs from 'dayjs';
import 'dayjs/locale/es';

dayjs.locale('es');

interface UseHolidayCalendarProps {
  selectedYear: Ref<number>;
  formState: any;
}

export function useHolidayCalendar({ selectedYear, formState }: UseHolidayCalendarProps) {
  const allowedMonths = computed(() => {
    if (!formState.travelFrom || !formState.travelTo) return [];

    const start = dayjs(formState.travelFrom);
    const end = dayjs(formState.travelTo);

    const currentYear = selectedYear.value;

    let startMonth = 0;
    let endMonth = 11;

    if (start.year() === currentYear) {
      startMonth = start.month();
    }

    if (end.year() === currentYear) {
      endMonth = end.month();
    }

    const months = [];

    for (let i = startMonth; i <= endMonth; i++) {
      const date = dayjs().year(currentYear).month(i).date(1);

      months.push({
        monthIndex: i,
        name: date.format('MMMM'),
      });
    }

    return months;
  });

  const generateYearDates = (year: number) => {
    const dates: string[] = [];

    for (let month = 0; month < 12; month++) {
      const date = new Date(year, month, 1);

      while (date.getMonth() === month) {
        dates.push(date.toISOString().split('T')[0]);

        date.setDate(date.getDate() + 1);
      }
    }

    return dates;
  };

  return {
    allowedMonths,
    generateYearDates,
  };
}
