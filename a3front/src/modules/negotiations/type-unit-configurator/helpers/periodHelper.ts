import dayjs from 'dayjs';
import type {
  PeriodDateRange,
  SelectOption,
} from '@/modules/negotiations/type-unit-configurator/interfaces';

export const currentYear = dayjs().year();

export const buildPeriodYears = (
  yearsToAdd: number = 3,
  startYear: number = currentYear
): SelectOption[] => {
  return Array.from({ length: yearsToAdd }, (_, i) => {
    const year = startYear + i;

    return { label: year.toString(), value: year };
  });
};

export const buildPeriodDateRange = (periodYear: number): PeriodDateRange => {
  return {
    dateFrom: dayjs().year(periodYear).startOf('year').format('YYYY-MM-DD'),
    dateTo: dayjs().year(periodYear).endOf('year').format('YYYY-MM-DD'),
  };
};
