import { ref } from 'vue';
import { shouldConfirmHolidayReset } from '../shared/shouldConfirmHolidayReset';
import { resetBasicStateAfterTariffTypeChange } from '../state/princingPlanStateActions';
import { loadHolidayTariff } from '@/modules/negotiations/products/configuration/content/pricingPlans/application/loadHolidayTariff';

export function useBasicTariffGuards(state: any) {
  const pendingChange = ref<null | (() => void)>(null);
  const showConfirmModal = ref(false);

  function requestTariffTypeChange(newValue: any) {
    const applyChange = () => {
      state.tariffType = newValue;
      resetBasicStateAfterTariffTypeChange(state);
    };

    if (shouldConfirmHolidayReset(state)) {
      pendingChange.value = applyChange;
      showConfirmModal.value = true;
      return;
    }

    applyChange();
  }

  function requestTravelDatesChange(payload: { from: any; to: any }) {
    if (
      state.travelFrom?.isSame(payload.from, 'day') &&
      state.travelTo?.isSame(payload.to, 'day')
    ) {
      return;
    }

    const applyChange = async () => {
      state.travelFrom = payload.from;
      state.travelTo = payload.to;
      state.selectedHolidays = [];

      if (payload.from && payload.to) {
        const result = await loadHolidayTariff(state, null);

        if (result) {
          state.selectedHolidays = result.holidays;
          state.years = result.years;
          state.selectedYear = result.selectedYear;
          state.selectedCategoryId = result.selectedCategoryId;
        }
      } else {
        state.selectedHolidays = [];
        state.years = [];
        state.selectedYear = null;
        state.selectedCategoryId = null;
      }
    };

    if (shouldConfirmHolidayReset(state)) {
      pendingChange.value = applyChange;
      showConfirmModal.value = true;
      return;
    }

    applyChange();
  }

  function confirmChange() {
    pendingChange.value?.();
    pendingChange.value = null;
    showConfirmModal.value = false;
  }

  function cancelChange() {
    pendingChange.value = null;
    showConfirmModal.value = false;
  }

  return {
    showConfirmModal,
    requestTariffTypeChange,
    requestTravelDatesChange,
    confirmChange,
    cancelChange,
  };
}
