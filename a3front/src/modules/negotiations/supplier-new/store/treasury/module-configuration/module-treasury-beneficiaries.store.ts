import { defineStore } from 'pinia';
import { ref } from 'vue';
import type { FormInstance } from 'ant-design-vue';

export const useModuleTreasuryBeneficiariesStore = defineStore(
  'moduleTreasuryBeneficiariesStore',
  () => {
    const initialFormData = ref<any>({
      type_document_id: undefined,
      document_number: undefined,
      deduction_percentage: 0,
      national_bank_account: undefined,
      main_name: undefined,
      main_email: undefined,
      emails: [
        {
          email: undefined,
        },
      ],
      accounts_national_bank_account_number: undefined,
      accounts_national_type_bank_accounts_id: undefined,
      accounts_foreign_bank_account_number: undefined,
      accounts_foreign_type_bank_accounts_id: undefined,
    });
    const formState = ref<any>({ ...initialFormData.value });
    const formRef = ref<FormInstance | null>(null);

    return { initialFormData, formState, formRef };
  }
);
