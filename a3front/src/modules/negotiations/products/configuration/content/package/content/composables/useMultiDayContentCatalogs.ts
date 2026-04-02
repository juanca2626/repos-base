import { computed } from 'vue';
import type { SelectOption } from '@/modules/negotiations/products/configuration/content/shared/interfaces/content';

export interface UseMultiDayContentCatalogsOptions {
  supplierActivities: any[];
}

export const useMultiDayContentCatalogs = (opts: UseMultiDayContentCatalogsOptions) => {
  const { supplierActivities } = opts;

  const activities = computed<SelectOption[]>(() => {
    const activitiesList = supplierActivities;
    if (!activitiesList || activitiesList.length === 0) {
      return [];
    }
    return activitiesList.map((activity) => ({
      label: activity.name,
      value: activity.code,
    }));
  });

  const filterOption = (input: string, option: any) => {
    return option.label.toLowerCase().indexOf(input.toLowerCase()) >= 0;
  };

  return {
    activities,
    filterOption,
  };
};
