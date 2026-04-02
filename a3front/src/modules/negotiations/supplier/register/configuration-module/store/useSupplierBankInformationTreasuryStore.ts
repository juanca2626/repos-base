import { reactive, ref } from 'vue';
import { defineStore } from 'pinia';

export const useSupplierBankInformationTreasuryStore = defineStore(
  'useSupplierBankInformationTreasuryStore',
  () => {
    const initialFormBankInformationTreasury: any = {
      sub_classification_supplier_id: undefined,
      bank_id: undefined,
      branch_office: '',
      address: '',
      country_id: undefined,
      state_id: undefined,
      number_aba: '',
      accounts_national_type_bank_accounts_id: undefined,
      accounts_national_bank_account_number: '',
      accounts_foreign_type_bank_accounts_id: undefined,
      accounts_foreign_bank_account_number: '',
      type_document_id: undefined,
      document_number: '',
      deduction_percentage: undefined,
      national_bank_account: undefined,
      main_name: '',
      main_email: '',
    };

    const initialEmails: any = [''];

    const formBankInformationTreasury = reactive<any>(
      structuredClone(initialFormBankInformationTreasury)
    );
    const emails = ref(structuredClone(initialEmails));

    const rules = reactive({
      bank_id: [{ required: true, message: 'Selecciona un banco', trigger: 'change' }],
      branch_office: [{ required: true, message: 'La sucursal es obligatoria', trigger: 'blur' }],
      address: [{ required: true, message: 'La dirección es obligatoria', trigger: 'blur' }],
      country_id: [{ required: true, message: 'Selecciona un país', trigger: 'change' }],
      state_id: [{ required: true, message: 'Selecciona una ciudad', trigger: 'change' }],
      number_aba: [{ required: true, message: 'El número ABA es obligatorio', trigger: 'blur' }],
      accounts_national_type_bank_accounts_id: [
        {
          required: true,
          message: 'Selecciona un tipo de cuenta bancaria nacional',
          trigger: 'change',
        },
      ],
      accounts_national_bank_account_number: [
        {
          required: true,
          message: 'El número de cuenta bancaria nacional es obligatorio',
          trigger: 'blur',
        },
      ],
      accounts_foreign_type_bank_accounts_id: [
        {
          required: true,
          message: 'Selecciona un tipo de cuenta bancaria extranjera',
          trigger: 'change',
        },
      ],
      accounts_foreign_bank_account_number: [
        {
          required: true,
          message: 'El número de cuenta bancaria extranjera es obligatorio',
          trigger: 'blur',
        },
      ],
      type_document_id: [
        { required: true, message: 'Selecciona un tipo de documento', trigger: 'change' },
      ],
      document_number: [
        { required: true, message: 'El número de documento es obligatorio', trigger: 'blur' },
      ],
      deduction_percentage: [
        { required: true, message: 'El porcentaje de deducción es obligatorio', trigger: 'blur' },
      ],
      national_bank_account: [
        { required: true, message: 'Selecciona una cuenta bancaria nacional', trigger: 'change' },
      ],
      main_name: [
        { required: true, message: 'El nombre principal es obligatorio', trigger: 'blur' },
      ],
      main_email: [
        {
          required: true,
          message: 'El correo electrónico principal es obligatorio',
          trigger: 'blur',
        },
        { type: 'email', message: 'El correo electrónico no es válido', trigger: 'blur' },
      ],
    });

    const resetFormBankInformationTreasury = () => {
      Object.assign(
        formBankInformationTreasury,
        structuredClone(initialFormBankInformationTreasury)
      );
    };

    const isLoadingForm = ref<boolean>(false);
    const setIsLoadingForm = (value: boolean) => {
      isLoadingForm.value = value;
    };

    const setAddEmails = () => {
      emails.value.push('');
    };

    const setRemoveEmails = (index: number) => {
      emails.value.splice(index, 1);
    };

    const isLoadingButton = ref<boolean>(false);
    const setIsLoadingButton = (value: any) => {
      isLoadingButton.value = value;
    };

    return {
      formBankInformationTreasury,
      resetFormBankInformationTreasury,
      rules,
      isLoadingForm,
      setIsLoadingForm,
      setAddEmails,
      setRemoveEmails,
      emails,
      initialEmails,
      isLoadingButton,
      setIsLoadingButton,
    };
  }
);
