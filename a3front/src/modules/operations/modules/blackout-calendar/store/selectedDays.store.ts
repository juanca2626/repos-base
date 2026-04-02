import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useSelectedDaysStore = defineStore('selectedDays', () => {
  const selectedDays = ref<any[]>([]);

  const updateSelectedDays = (newSelectedDays) => {
    selectedDays.value = newSelectedDays;
  };

  const deselectAllDays = (locksByMonth, locksByProvidersAndSelectedDates) => {
    selectedDays.value.forEach((v) => {
      const provider_id = v.provider_id;
      const providerLocks = locksByMonth.find((v1) => v1.provider._id === provider_id);
      if (providerLocks) {
        const { locks } = providerLocks;
        for (const date in locks) {
          if (locks[date].selected) {
            locks[date].selected = false;
          }
        }
      }
    });
    selectedDays.value = [];
    locksByProvidersAndSelectedDates.value = [];
  };

  return {
    // state
    selectedDays,
    // actions
    updateSelectedDays,
    deselectAllDays,
  };
});
