// store/useTransportConfiguratorFilterStore.ts
import { defineStore } from 'pinia';
import { ref, reactive } from 'vue';
import dayjs from 'dayjs';

export const useTransportConfiguratorSettingFilterStore = defineStore('settingFilterStore', () => {
  const keyAllSelectTransfer = -1;

  // Estado reactivo para los filtros
  const filterSettings = reactive({
    date_from: null as string | null,
    date_to: null as string | null,
    type_transfer: null as string | null,
    is_trunk: false,
    is_representative: false,
    is_trunk_representative: false,
    guides: false,
    transfer_id: keyAllSelectTransfer as number,
  });

  const rangePresets = ref<{ label: string; value: [dayjs.Dayjs, dayjs.Dayjs] }[]>([]);

  // Métodos para actualizar los filtros
  const setFilterRangeDates = (dates: [dayjs.Dayjs, dayjs.Dayjs] | null) => {
    if (dates) {
      filterSettings.date_from = dayjs(dates[0]).format('YYYY-MM-DD');
      filterSettings.date_to = dayjs(dates[1]).format('YYYY-MM-DD');
    } else {
      filterSettings.date_from = null;
      filterSettings.date_to = null;
    }
  };

  const clearFilters = () => {
    filterSettings.date_from = null;
    filterSettings.date_to = null;
    filterSettings.type_transfer = null;
    filterSettings.is_trunk = false;
    filterSettings.is_representative = false;
    filterSettings.is_trunk_representative = false;
    filterSettings.guides = false;
    filterSettings.transfer_id = keyAllSelectTransfer;
  };

  // Método para establecer presets de rangos de fechas
  const setRangePresets = (details: Array<{ validityPeriod: string | null }>) => {
    const seenRanges = new Set<string>();

    const newRangePresets = details
      .map((detail) => {
        if (!detail.validityPeriod) return null;

        const [startDateStr, endDateStr] = detail.validityPeriod.split(' - ');

        const startDate = dayjs(startDateStr, 'DD/MM/YYYY');
        const endDate = dayjs(endDateStr, 'DD/MM/YYYY');

        const rangeKey = `${startDateStr}-${endDateStr}`;
        if (!seenRanges.has(rangeKey)) {
          seenRanges.add(rangeKey);
          return { label: rangeKey, value: [startDate, endDate] };
        }
        return null;
      })
      .filter((preset) => preset !== null);

    rangePresets.value = newRangePresets as { label: string; value: [dayjs.Dayjs, dayjs.Dayjs] }[];
  };

  return {
    filterSettings,
    rangePresets,
    keyAllSelectTransfer,
    setFilterRangeDates,
    clearFilters,
    setRangePresets,
  };
});
