import { defineStore } from 'pinia';
import { reactive, ref } from 'vue';
import type { FormInstance } from 'ant-design-vue';
import type { GeneralInformationForm } from '@/modules/negotiations/supplier-new/interfaces/supplier-registration';
import { SupplierStatusEnum } from '@/modules/negotiations/suppliers/enums/supplier-status.enum';

export const useGeneralInformationStore = defineStore('generalInformationStore', () => {
  const initialFormData: GeneralInformationForm = {
    code: '',
    businessName: '',
    companyName: '',
    rucNumber: '',
    dniNumber: null,
    chainId: undefined,
    authorizedManagement: false,
    status: SupplierStatusEnum.IN_REVIEW,
    reason_state: null,
  };

  const formState = reactive<GeneralInformationForm>({ ...initialFormData });

  const formRef = ref<FormInstance | null>(null);

  const resetFormState = () => {
    Object.assign(formState, { ...initialFormData });
  };

  return {
    initialFormData,
    formState,
    formRef,
    resetFormState,
  };
});
