import { defineStore } from 'pinia';
import { ref } from 'vue';
import type { FormInstance } from 'ant-design-vue';
import { useSupplierResourceService } from '@/modules/negotiations/supplier-new/service/supplier-resources.service';

export const useSunatInformationStore = defineStore('sunatInformationStore', () => {
  const initialFormData = ref<any>({
    tax_rates_id: undefined,
    iva_options_id: undefined,
    last_billing_date: undefined,
  });
  const formState = ref<any>({ ...initialFormData.value });
  const formRef = ref<FormInstance | null>(null);
  const taxRates = ref<[]>([]);
  const ivaOptions = ref<[]>([]);

  const { fetchSunatInformation } = useSupplierResourceService;

  const loadSunatInformation = async () => {
    const { data } = await fetchSunatInformation();
    return data;
  };

  return { initialFormData, formState, formRef, taxRates, ivaOptions, loadSunatInformation };
});
