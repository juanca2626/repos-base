import { defineStore } from 'pinia';
import { ref } from 'vue';
import type { FormInstance } from 'ant-design-vue';
import { useSupplierResourceService } from '@/modules/negotiations/supplier-new/service/supplier-resources.service';

export const useModuleSunatInformationStore = defineStore('moduleSunatInformationStore', () => {
  const initialFormData = ref<any>({
    types_tax_documents_id: undefined,
    city_id: undefined,
  });
  const formState = ref<any>({ ...initialFormData.value });
  const formRef = ref<FormInstance | null>(null);
  const typeTaxDocument = ref<any>([]);
  const cities = ref<any>([]);

  const { fetchModuleSunatInformation } = useSupplierResourceService;

  const loadSunatInformation = async () => {
    const { data } = await fetchModuleSunatInformation();
    return data;
  };

  return { initialFormData, formState, formRef, typeTaxDocument, cities, loadSunatInformation };
});
