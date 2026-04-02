import { reactive, computed } from 'vue';
import { storeToRefs } from 'pinia';

import { useHolidayEditing } from './useHolidayEditing';
import { useHolidayCategories } from './useHolidayCategories';
import { useHolidayCalendar } from './useHolidayCalendar';

import { loadHolidayTariff } from '@/modules/negotiations/products/configuration/content/pricingPlans/application/loadHolidayTariff';
import { useConfigurationStore } from '@/modules/negotiations/products/configuration/stores/useConfigurationStore';
import { TariffType } from '@/modules/negotiations/products/configuration/enums/tariffType.enum';
import { resolvePricingTravelRange } from '@/modules/negotiations/products/configuration/content/pricingPlans/domain/travelRange/resolvePricingTravelRange';

export const useHolidayTariffs = (formState: any, ratePlanId: string | null) => {
  const configurationStore = useConfigurationStore();

  const { loadingHoliday } = storeToRefs(configurationStore);

  const uiState = reactive({
    selectedItemId: null as string | null,
    editingItemId: null as string | null,
    editMode: false,
  });

  const holidays = computed(() => formState.selectedHolidays ?? []);

  const years = computed(() => formState.years ?? []);

  const travelRange = computed(() => resolvePricingTravelRange(formState));

  const isFlatValid = () => {
    return !!formState.travelFrom && !!formState.travelTo;
  };

  const isPeriodsValid = () => {
    if (!formState.periods?.length) return false;

    return formState.periods.every((group: any) => {
      if (!group.periodType && !group.periodId) return false;

      if (!group.ranges?.length) return false;

      return group.ranges.every((range: any) => {
        return !!range.dateFrom && !!range.dateTo;
      });
    });
  };

  const isDisabled = computed(() => {
    if (formState.tariffType === TariffType.PERIODS) {
      return !isPeriodsValid();
    }

    return !isFlatValid();
  });

  const travelFromStr = computed(() => {
    if (!travelRange.value?.dateFrom) return '';
    return travelRange.value.dateFrom.format('YYYY-MM-DD');
  });

  const travelToStr = computed(() => {
    if (!travelRange.value?.dateTo) return '';
    return travelRange.value.dateTo.format('YYYY-MM-DD');
  });

  const selectedYear = computed({
    get: () => formState.selectedYear ?? null,
    set: (value) => {
      formState.selectedYear = value;
    },
  });

  const selectedCategory = computed(
    () => holidays.value.find((c: any) => (c.id ?? c.uuid) === formState.selectedCategoryId) || null
  );

  const selectedItem = computed(
    () =>
      selectedCategory.value?.dates.find((d: any) => d.externalId === uiState.selectedItemId) ||
      null
  );

  const editingItem = computed(() => {
    if (!uiState.editingItemId) return null;

    for (const category of holidays.value) {
      const found = category.dates.find((d: any) => d.externalId === uiState.editingItemId);

      if (found) return found;
    }

    return null;
  });

  const calendar = useHolidayCalendar({
    selectedYear,
    formState,
  });

  const editing = useHolidayEditing({
    uiState,
    editingItem,
    selectedItem,
  });

  const categories = useHolidayCategories({
    holidays,
    uiState,
    formState,
  });

  const handleIncludeHolidayTariffsChange = async () => {
    const result = await loadHolidayTariff(formState, ratePlanId);

    if (!result) return;

    loadingHoliday.value = result.loadingHoliday;

    formState.selectedHolidays = result.holidays;
    formState.years = result.years;
    formState.selectedYear = result.selectedYear;
    formState.selectedCategoryId = result.selectedCategoryId;
  };

  return {
    holidays,
    uiState,
    selectedYear,
    selectedItem,
    editingItem,
    years,
    isDisabled,
    travelFromStr,
    travelToStr,

    loadingHoliday,

    ...calendar,
    ...editing,
    ...categories,

    handleIncludeHolidayTariffsChange,
  };
};
