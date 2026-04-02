import { capitalize } from 'vue';
import type { EnginePeriod } from '../types/period.types';
import type { BasicSection } from '../state/createInitialBasicState';
import dayjs from 'dayjs';

export function mapBasicToPeriods(basic: BasicSection): EnginePeriod[] {
  const result: EnginePeriod[] = [];
  if (!basic) return result;

  /* PLANA / PROMOCIONAL / ESPECIFICA */

  if (['FLAT', 'PROMOTIONAL', 'SPECIFIC'].includes(basic.tariffType)) {
    const from = dayjs(basic.travelFrom).format('YYYY-MM-DD');
    const to = dayjs(basic.travelTo).format('YYYY-MM-DD');

    result.push({
      id: `range-${from}-${to}`,
      name: capitalize(basic.tariffType ?? ''),
      type: 'range',
      rangeFrom: from,
      rangeTo: to,
    });
  }

  /* PERIODOS */

  if (basic.tariffType === 'PERIODS') {
    basic.periods?.forEach((period, index) => {
      const range = period.ranges?.[0];
      if (!range) return;
      const from = dayjs(range.dateFrom).format('YYYY-MM-DD');
      const to = dayjs(range.dateTo).format('YYYY-MM-DD');
      result.push({
        id: `range-${from}-${to}`,
        name: capitalize(period.periodType ?? `Periodo ${index + 1}`),
        type: 'range',
        rangeFrom: from ?? '',
        rangeTo: to ?? '',
      });
    });
  }

  /* DIAS */

  if (basic.differentiatedTariff && basic.selectedDays?.length) {
    const key = basic.selectedDays.join('-');
    result.push({
      id: `days-${key}`,
      name: 'Personalizado',
      type: 'days',
      days: basic.selectedDays,
    });
  }

  /* FESTIVOS */

  if (basic.includeHolidayTariffs) {
    basic.selectedHolidays?.forEach((holiday, index) => {
      result.push({
        id: `holiday-${holiday}`,
        name: `Festivo ${index + 1}`,
        type: 'dayOfMonth',
        date: holiday,
      });
    });
  }

  return result;
}
