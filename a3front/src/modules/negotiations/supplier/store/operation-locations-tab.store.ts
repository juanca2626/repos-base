import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import type {
  ApiResponse,
  TransformedData,
} from '@/modules/negotiations/supplier/interfaces/operation-locations-response.interface';
import { supplierApi } from '@/modules/negotiations/api/negotiationsApi';

export const useOperationLocationsTab = defineStore('useOperationLocationsTab', () => {
  const data = ref<TransformedData[]>([]);
  const isLoading = ref(false);
  const activeKey = ref<string | null>(null);

  const transformData = (apiData: ApiResponse[]): TransformedData[] => {
    return apiData.map((item) => {
      const ids = [item.country.id, item.state.id, item.city?.id, item.zone?.id]
        .filter((id) => id !== undefined && id !== null)
        .join(',');

      return {
        ids,
        country_id: item.country.id,
        state_id: item.state.id,
        city_id: item.city?.id ?? null,
        zone_id: item.zone?.id ?? null,
        display_name: generateDisplayName(item),
      };
    });
  };

  const generateDisplayName = (item: ApiResponse): string => {
    const descriptions = [item.state.name, item.city?.name, item.zone?.name].filter(Boolean);

    return descriptions.join(', ').replace('.', '');
  };

  const fetchData = async (id: number) => {
    isLoading.value = true;
    try {
      const response = await supplierApi.get(
        `supplier/operation-locations?supplierSubClassificationId=${id}`
      );
      data.value = transformData(response.data.data);
      activeKey.value = data.value[0]?.ids ?? null;
    } catch (error) {
      console.error('Error fetching supplier data:', error);
    } finally {
      isLoading.value = false;
    }
  };

  const setActiveKey = (key: string) => {
    activeKey.value = key;
  };

  const activeItem = computed(
    () => data.value.find((item) => item.ids === activeKey.value) ?? null
  );

  return {
    data,
    isLoading,
    activeKey,
    activeItem,
    fetchData,
    setActiveKey,
  };
});
