import dayjs from 'dayjs';
import type { ServiceType } from '@/modules/negotiations/products/configuration/types/service.type';

export type BasicSection = ReturnType<typeof createInitialBasicState>;

export function createInitialBasicState(_serviceType: ServiceType | null = null) {
  return {
    name: 'Tarifa',
    requiresBookingCode: false,
    bookingCode: '',
    tariffType: '',
    promotionName: '',
    tariffSegmentation: [],
    specificMarkets: [],
    specificClients: [],
    specificSeries: '',
    periods: [
      {
        periodId: '',
        periodType: '',
        periodName: '',
        ranges: [
          {
            dateFrom: null,
            dateTo: null,
          },
        ],
      },
    ],
    travelFrom: dayjs(),
    travelTo: null,
    modifyBookingPeriod: false,
    bookingFrom: null,
    bookingTo: null,
    differentiatedTariff: false,
    selectedDays: [],
    standardDays: ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'],
    includeHolidayTariffs: false,
    selectedHolidays: [],
    currencyBuy: 'USD',
    currencySell: 'USD',

    // parametros para el calendario
    years: [] as number[],
    selectedYear: null as number | null,
    selectedCategoryId: null as string | null,
  };
}
