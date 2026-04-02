import { defineStore } from 'pinia';
import { ref } from 'vue';
import type { FormInstance } from 'ant-design-vue';

export const useBasicInformationStore = defineStore('basicInformationStore', () => {
  const initialFormData = ref<any>({
    credit_days: undefined,
    credit_days_sunat: undefined,
    sunat_start_date: undefined,
  });
  const formState = ref<any>({ ...initialFormData.value });
  const formRef = ref<FormInstance | null>(null);

  return { initialFormData, formState, formRef };
});
