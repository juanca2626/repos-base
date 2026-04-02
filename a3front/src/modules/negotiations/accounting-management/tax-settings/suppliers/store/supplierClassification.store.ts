import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import type { Datum } from '@/modules/negotiations/accounting-management/tax-settings/suppliers/interfaces/supplier-tax-show-response.interface';
import { supportApi } from '@/modules/negotiations/api/negotiationsApi';

export const supplierClassificationStore = defineStore('supplier', () => {
  const data = ref<Datum[]>([]);
  const isLoading = ref(false);
  const activeKey = ref<number | null>(null);
  const nameClassification = ref<string | null>(null);

  const fetchData = async (id: string) => {
    isLoading.value = true;
    try {
      const response = await supportApi.get(`taxes-suppliers/${id}/classifications`);
      data.value = response.data.data;
      activeKey.value = data.value[0]?.id ?? null;
      nameClassification.value = getNameClassification(activeKey.value);
    } catch (error) {
      console.error('Error fetching supplier data:', error);
    } finally {
      isLoading.value = false;
    }
  };

  const setActiveKey = (key: number) => {
    activeKey.value = key;
    nameClassification.value = getNameClassification(key);
  };

  const getNameClassification = (id: number) => {
    const foundClassification = data.value.find((classification) => classification.id === id);
    return foundClassification ? foundClassification.name : '';
  };

  const activeItem = computed(() => data.value.find((item) => item.id === activeKey.value) ?? null);

  return {
    data,
    isLoading,
    activeKey,
    activeItem,
    nameClassification,
    fetchData,
    setActiveKey,
  };
});
