import { defineStore } from 'pinia';
import { ref } from 'vue';
import { supportApi } from '@/modules/negotiations/api/negotiationsApi';
import type { ApiListResponse } from '@/modules/negotiations/interfaces/api-response.interface';
import type { SelectOption } from '@/modules/negotiations/supplier/interfaces';
import { handleError } from '@/modules/negotiations/api/responseApi';

export const useTypeUnitConfiguratorStore = defineStore('typeUnitConfiguratorStore', () => {
  const isLoading = ref<boolean>(false);
  const periodYears = ref<SelectOption[]>([]);

  const pageTabsKeys = {
    typeUnit: 'type-unit',
    typeUnitSetting: 'type-unit-setting',
  };

  const pageActiveTabKey = ref<string>(pageTabsKeys.typeUnit);

  const setPageActiveTabKey = (value: string): void => {
    pageActiveTabKey.value = value;
  };

  const fetchListPeriodYears = async () => {
    try {
      isLoading.value = true;

      const { data } = await supportApi.get<ApiListResponse<number[]>>(
        `unit-settings/list-period-years`
      );

      periodYears.value = data.data.map((item) => ({
        label: item.toString(),
        value: item,
      }));
    } catch (error: any) {
      console.error('Error fetching data:', error);
      handleError(error);
    } finally {
      isLoading.value = false;
    }
  };

  const loadPeriodYearsIfEmpty = async () => {
    if (periodYears.value.length !== 0 || isLoading.value) return;
    await fetchListPeriodYears();
  };

  return {
    isLoading,
    periodYears,
    pageActiveTabKey,
    pageTabsKeys,
    setPageActiveTabKey,
    fetchListPeriodYears,
    loadPeriodYearsIfEmpty,
  };
});
