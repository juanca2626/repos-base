import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import dayjs from 'dayjs';

export const usePricingPlansStore = defineStore('pricingPlans', () => {
  const basicData = ref({
    name: 'Tarifa Estándar 2025',
    requiresBookingCode: false,
    bookingCode: '',
    tariffType: 'plana' as string | null,
    periods: [
      {
        periodType: null,
        ranges: [{ dateFrom: null, dateTo: null }],
      },
    ] as Array<{
      periodType: string | null;
      ranges: Array<{
        dateFrom: any;
        dateTo: any;
      }>;
    }>,
    promotionName: [] as string[],
    tariffSegmentation: [] as string[],
    specificMarkets: [] as string[],
    specificClients: [] as string[],
    specificSeries: [] as string[],
    travelFrom: dayjs('2025-01-01'),
    travelTo: dayjs('2025-12-31'),
    modifyBookingPeriod: false,
    bookingFrom: null,
    bookingTo: null,
    differentiatedTariff: true,
    selectedDays: ['L', 'M', 'X', 'J', 'V', 'S', 'D'] as string[],
    includeHolidayTariffs: true,
    selectedHolidays: [] as Array<{ name: string; date: string }>,
    currencyBuy: 'USD',
    currencySell: 'USD',
  });

  const taxAndStaff = ref({
    affectedByIGV: false,
    igvRecovery: false,
    servicePercentage: 0,
    additionalPercentage: false,
    additionalPercentages: [] as Array<{ name: string; percentage: number }>,
    additionalPercentageValue: 0,
    selectedStaff: [] as string[],
    staffTaxes: {} as Record<string, { igv: boolean; services: boolean; other: boolean }>,
  });

  const amounts = ref({
    selectedPeriod: 0,
    tariffStatus: null as string | null,
    associatedPolicies: [{ name: 'Política general', passengers: '1 - 99 pasajeros' }] as Array<{
      name: string;
      passengers: string;
    }>,
    tariffInputMode: 'unica',
    includeTaxes: false,
    adultoRate: null as number | null,
    ninoRate: null as number | null,
    ninoDiscount: false,
    ninoDiscountPercent: 0,
    infanteRate: null as number | null,
    infanteDiscount: false,
    infanteDiscountPercent: 0,
    rangosGeneralDiscount: false,
    rangosGeneralDiscountPercent: 0,
    rangos: [
      {
        maxPax: null as number | null,
        adultoRate: null as number | null,
        ninoRate: null as number | null,
        ninoDiscount: false,
        infanteRate: null as number | null,
        infanteDiscount: false,
        infanteDiscountPercent: 0,
      },
    ],
  });

  const amountsByPeriod = ref<Record<number, any>>({});

  const cupos = ref<
    Record<
      string,
      {
        mode: 'complete' | 'custom';
        cupoVal: number | null;
        releaseVal: number | null;
        dates: Record<string, boolean>;
      }
    >
  >({});

  const formattedPeriods = computed(() => {
    const periods: Array<{ id: string; name: string; dates: string }> = [];

    if (basicData.value.tariffType === 'periodos') {
      basicData.value.periods.forEach((group, groupIndex) => {
        group.ranges.forEach((range, rangeIndex) => {
          const start = range.dateFrom ? dayjs(range.dateFrom).format('DD MMM') : '?';
          const end = range.dateTo ? dayjs(range.dateTo).format('DD MMM') : '?';

          periods.push({
            id: `period_${groupIndex}_${rangeIndex}`,
            name: group.periodType || `Periodo ${groupIndex + 1}`,
            dates: `${start} - ${end}`,
          });
        });
      });
    } else {
      let start = '?';
      let end = '?';

      if (basicData.value.travelFrom) {
        start = dayjs(basicData.value.travelFrom).format('DD MMM');
      }

      if (basicData.value.travelTo) {
        end = dayjs(basicData.value.travelTo).format('DD MMM/YY');
      }

      periods.push({
        id: 'general',
        name: basicData.value.tariffType === 'promocional' ? 'Promocional' : 'Plana',
        dates: `${start} - ${end}`,
      });
    }

    if (basicData.value.differentiatedTariff && basicData.value.selectedDays.length > 0) {
      const weekendDays = ['S', 'D'];
      const hasWeekend = basicData.value.selectedDays.some((d) => weekendDays.includes(d));

      if (hasWeekend) {
        periods.push({
          id: 'diff_weekend',
          name: 'Fin de semana',
          dates: 'Sáb-Dom' + (basicData.value.selectedDays.length > 2 ? ' (+)' : ''),
        });
      } else {
        periods.push({
          id: 'diff_weekdays',
          name: 'Días de semana',
          dates: 'Lun-Vie',
        });
      }
    }

    if (basicData.value.includeHolidayTariffs && basicData.value.selectedHolidays.length > 0) {
      basicData.value.selectedHolidays.forEach((h, index) => {
        periods.push({
          id: `holiday_${index}`,
          name: h.name,
          dates: h.date,
        });
      });
    }

    return periods;
  });

  function saveAmounts(index: number) {
    const dataToSave = JSON.parse(JSON.stringify(amounts.value));
    delete (dataToSave as any).selectedPeriod;
    amountsByPeriod.value[index] = dataToSave;
  }

  function loadAmounts(index: number) {
    const saved = amountsByPeriod.value[index];

    if (saved) {
      Object.assign(amounts.value, saved);
    } else {
      amounts.value.tariffStatus = null;
      amounts.value.tariffInputMode = 'unica';
      amounts.value.includeTaxes = false;
      amounts.value.adultoRate = null;
      amounts.value.ninoRate = null;
      amounts.value.ninoDiscount = false;
      amounts.value.ninoDiscountPercent = 0;
      amounts.value.infanteRate = null;
      amounts.value.infanteDiscount = false;
      amounts.value.infanteDiscountPercent = 0;
      amounts.value.rangosGeneralDiscount = false;
      amounts.value.rangos = [
        {
          maxPax: null,
          adultoRate: null,
          ninoRate: null,
          ninoDiscount: false,
          infanteRate: null,
          infanteDiscount: false,
          infanteDiscountPercent: 0,
        },
      ];
    }

    amounts.value.selectedPeriod = index;
  }

  function resetStore() {
    amountsByPeriod.value = {};
  }

  return {
    basicData,
    taxAndStaff,
    amounts,
    amountsByPeriod,
    cupos,
    formattedPeriods,
    saveAmounts,
    loadAmounts,
    resetStore,
  };
});
