import { computed } from 'vue';
import { capitalize } from 'vue';
import { useSupportResourcesStore } from '@/modules/negotiations/products/configuration/stores/useSupportResourcesStore';
import { storeToRefs } from 'pinia';
import { nanoid } from 'nanoid';
import dayjs from '@/modules/negotiations/products/configuration/utils/dayjs';

export function usePeriodTariffComposable(formState: any) {
  const supportResourcesStore = useSupportResourcesStore();

  const { operationalSeasons } = storeToRefs(supportResourcesStore);

  const periodTypeOptions = computed(() => {
    return operationalSeasons.value.map((season) => ({
      label: `Temporada ${capitalize(season.name)}`,
      value: season.id,
    }));
  });

  const generateId = () => {
    return nanoid();
  };

  const addPeriodGroup = () => {
    const id = generateId();
    formState.periods.push({
      id,
      periodType: null,
      ranges: [
        {
          dateFrom: null,
          dateTo: null,
        },
      ],
    });
  };

  const removePeriodGroup = (groupIndex: number) => {
    formState.periods.splice(groupIndex, 1);
  };

  const addRange = (group: any) => {
    const lastRange = group.ranges.slice(-1)[0];

    let nextDate = null;

    if (lastRange?.dateTo) {
      nextDate = dayjs(lastRange.dateTo).add(1, 'day');
    }

    group.ranges.push({
      dateFrom: nextDate,
      dateTo: null,
    });
  };

  const removeRange = (group: any, rangeIndex: number) => {
    group.ranges.splice(rangeIndex, 1);
  };

  const getMinDateForGroup = (groupIndex: number) => {
    if (groupIndex === 0) return dayjs().startOf('day');

    const prevGroup = formState.periods[groupIndex - 1];
    const lastRange = prevGroup?.ranges?.slice(-1)[0];

    if (!lastRange?.dateTo) return dayjs().startOf('day');

    return dayjs(lastRange.dateTo).add(1, 'day');
  };

  const getMinDateForRange = (groupIndex: number, rangeIndex: number) => {
    if (rangeIndex === 0) {
      return getMinDateForGroup(groupIndex);
    }

    const prevRange = formState.periods[groupIndex].ranges[rangeIndex - 1];

    if (!prevRange?.dateTo) {
      return getMinDateForGroup(groupIndex);
    }

    return dayjs(prevRange.dateTo).add(1, 'day');
  };

  const isDateUsed = (date: any, currentGroupIndex: number, currentRangeIndex: number) => {
    return formState.periods.some((group: any, gIndex: number) => {
      return group.ranges.some((range: any, rIndex: number) => {
        if (gIndex === currentGroupIndex && rIndex === currentRangeIndex) return false;
        if (!range.dateFrom || !range.dateTo) return false;

        return dayjs(date).isBetween(range.dateFrom, range.dateTo, 'day', '[]');
      });
    });
  };

  const getDisabledDate =
    (groupIndex: number, rangeIndex: number, field: 'from' | 'to') => (current: any) => {
      if (!current) return false;

      const minDate = getMinDateForRange(groupIndex, rangeIndex);

      if (current.isBefore(minDate, 'day')) return true;

      if (isDateUsed(current, groupIndex, rangeIndex)) return true;

      const range = formState.periods[groupIndex].ranges[rangeIndex];

      if (field === 'to' && range?.dateFrom) {
        if (current.isBefore(dayjs(range.dateFrom), 'day')) return true;
      }

      return false;
    };

  return {
    periodTypeOptions,
    generateId,
    addPeriodGroup,
    removePeriodGroup,
    addRange,
    removeRange,
    getDisabledDate,
  };
}
