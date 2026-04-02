import { defineStore } from 'pinia';
import { ref } from 'vue';
import type { FormInstance } from 'ant-design-vue';
import { useSupplierResourceService } from '@/modules/negotiations/supplier-new/service/supplier-resources.service';
import { useSupplierService } from '@/modules/negotiations/supplier-new/service/supplier.service';

export const useModuleTreasuryBankInformationStore = defineStore(
  'moduleTreasuryBankInformationStore',
  () => {
    const initialFormData = ref<any>({
      bank_id: undefined,
      branch_office: undefined,
      address: undefined,
      country_id: undefined,
      state_id: undefined,
      number_aba: undefined,
      accounts_national_bank_account_number: undefined,
      accounts_national_type_bank_accounts_id: undefined,
      accounts_foreign_bank_account_number: undefined,
      accounts_foreign_type_bank_accounts_id: undefined,
    });
    const formState = ref<any>({ ...initialFormData.value });
    const formRef = ref<FormInstance | null>(null);
    const countries = ref<any>([]);
    const states = ref<any>([]);
    const typeBankAccount = ref<any>([]);
    const bank = ref<any>([]);
    const typeDocument = ref<any>([]);
    const stateDisabled = ref<boolean>(true);

    const { fetchModuleBankInformationLocationsResource, fetchModuleBankInformationResource } =
      useSupplierResourceService;

    const { showSupplierBankInformationModule } = useSupplierService;

    return {
      initialFormData,
      formState,
      formRef,
      countries,
      states,
      typeBankAccount,
      bank,
      typeDocument,
      stateDisabled,
      fetchModuleBankInformationLocationsResource,
      fetchModuleBankInformationResource,
      showSupplierBankInformationModule,
    };
  }
);
