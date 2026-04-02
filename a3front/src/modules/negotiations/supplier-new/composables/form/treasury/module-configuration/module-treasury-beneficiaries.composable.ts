import type { Rule } from 'ant-design-vue/es/form';
import { storeToRefs } from 'pinia';
import { computed, watch } from 'vue';
import { useSupplierGlobalComposable } from '@/modules/negotiations/supplier-new/composables/form/supplier-global.composable';
import { handleError, handleCompleteResponse } from '@/modules/negotiations/api/responseApi';
import { useSupplierService } from '@/modules/negotiations/supplier-new/service/supplier.service';
import { FormComponentEnum } from '@/modules/negotiations/supplier-new/enums/form-component.enum';
import { useModuleTreasuryBeneficiariesStore } from '@/modules/negotiations/supplier-new/store/treasury/module-configuration/module-treasury-beneficiaries.store';
import { useModuleTreasuryBankInformationComponenComposable } from '@/modules/negotiations/supplier-new/composables/form/treasury/module-configuration/module-treasury-bank-information.composable';

export function useModuleTreasuryBeneficiariesComposable() {
  const {
    supplierBankInformationId,
    supplierBankInformation,
    subClassificationSupplierId,
    showFormComponent,
    getShowFormComponent,
    getIsEditFormComponent,
    getLoadingFormComponent,
    getLoadingButtonComponent,
    getDisabledComponent,
    getSavedFormComponent,
    handleShowFormSpecific,
    handleIsEditFormSpecific,
    handleLoadingFormSpecific,
    handleLoadingButtonSpecific,
    handleDisabledSpecific,
    handleSavedFormSpecific,
  } = useSupplierGlobalComposable();

  const { typeDocument } = useModuleTreasuryBankInformationComponenComposable();

  const { updateSupplierBankInformation, createSupplierBankInformation } = useSupplierService;

  const moduleTreasuryBeneficiariesStore = useModuleTreasuryBeneficiariesStore();

  const { formState, initialFormData, formRef } = storeToRefs(moduleTreasuryBeneficiariesStore);

  const formRules: Record<string, Rule[]> = {
    bank_id: [{ required: true, message: 'Selecciona un banco', trigger: 'change' }],
    document_number: [
      { required: true, message: 'El número de documento es obligatorio', trigger: 'blur' },
    ],
    deduction_percentage: [
      { required: true, message: 'El porcentaje de deducción es obligatorio', trigger: 'blur' },
    ],
    national_bank_account: [
      { required: true, message: 'Selecciona una cuenta bancaria nacional', trigger: 'change' },
    ],
    main_name: [{ required: true, message: 'El nombre principal es obligatorio', trigger: 'blur' }],
    main_email: [
      {
        required: true,
        message: 'El correo electrónico principal es obligatorio',
        trigger: 'blur',
      },
      { type: 'email', message: 'El correo electrónico no es válido', trigger: 'blur' },
    ],
  };

  const getListItem = computed(() => {
    const formatFieldValue = (fieldKey: string, fieldValue: number) => {
      const sourceList: Record<string, any[]> = {
        type_document_id: typeDocument.value,
      };

      const matchedItem = sourceList[fieldKey]?.find((item: any) => item.id === fieldValue) || {};
      return matchedItem.name || '';
    };

    const fields = [
      { key: 'type_document_id', label: 'Tipo de documento:', format: formatFieldValue },
      { key: 'document_number', label: 'Número de documento:' },
      { key: 'deduction_percentage', label: 'Porcentaje de deducción:' },
      { key: 'national_bank_account', label: 'Cuenta bancaria nacional:' },
      { key: 'main_name', label: 'Nombre principal:' },
      { key: 'main_email', label: 'Correo principal:' },
    ];

    return fields.map(({ key, label, format }) => ({
      title: label,
      value: format ? format(key, formState.value[key]) : formState.value[key],
    }));
  });

  const getIsFormValid = computed(() => {
    const requiredFields = [
      'type_document_id',
      'document_number',
      'deduction_percentage',
      'national_bank_account',
      'main_name',
      'main_email',
    ];
    return requiredFields.every((key) => formState.value[key]);
  });

  const getRequestData = () => {
    const {
      type_document_id,
      document_number,
      deduction_percentage,
      national_bank_account,
      main_name,
      main_email,
      emails,
      accounts_national_bank_account_number,
      accounts_national_type_bank_accounts_id,
      accounts_foreign_bank_account_number,
      accounts_foreign_type_bank_accounts_id,
    } = formState.value;

    return {
      type_document_id,
      document_number,
      deduction_percentage,
      national_bank_account,
      main_name,
      main_email,
      accounts: [
        {
          type_bank_accounts_id: accounts_national_type_bank_accounts_id,
          type: 'NATIONAL',
          bank_account_number: accounts_national_bank_account_number,
        },
        {
          type_bank_accounts_id: accounts_foreign_type_bank_accounts_id,
          type: 'FOREIGN',
          bank_account_number: accounts_foreign_bank_account_number,
        },
      ],
      sub_classification_supplier_id: subClassificationSupplierId.value,
      emails: emails.map((e: any) => e.email),
    };
  };

  const handleClose = () => {
    const isValid = getIsFormValid.value;
    const isSaved = getSavedFormComponent.value(FormComponentEnum.MODULE_TREASURY_BENEFICIARIES);

    if (isValid && isSaved) {
      handleIsEditFormSpecific(FormComponentEnum.MODULE_TREASURY_BENEFICIARIES, true);
      return;
    }

    formState.value = { ...initialFormData.value };
    handleShowFormSpecific(FormComponentEnum.MODULE_TREASURY_BENEFICIARIES, false);
  };

  const handleSaveForm = async () => {
    const request = getRequestData();

    try {
      handleLoadingFormSpecific(FormComponentEnum.MODULE_TREASURY_BENEFICIARIES, true);
      handleLoadingButtonSpecific(FormComponentEnum.MODULE_TREASURY_BENEFICIARIES, true);

      const { data } = supplierBankInformationId.value
        ? await updateSupplierBankInformation(supplierBankInformationId.value, request)
        : await createSupplierBankInformation(request);

      if (!supplierBankInformationId.value) {
        supplierBankInformationId.value = data.id;
      }

      handleCompleteResponse(data);

      handleIsEditFormSpecific(FormComponentEnum.MODULE_TREASURY_BENEFICIARIES, true);
      handleSavedFormSpecific(FormComponentEnum.MODULE_TREASURY_BENEFICIARIES, true);
      handleDisabledSpecific(FormComponentEnum.MODULE_ACCOUNTING_SUNAT_INFORMATION, false);
    } catch (error: any) {
      handleError(error);
      console.log('⛔ Error save: ', error.message);
    } finally {
      handleLoadingFormSpecific(FormComponentEnum.MODULE_TREASURY_BENEFICIARIES, false);
      handleLoadingButtonSpecific(FormComponentEnum.MODULE_TREASURY_BENEFICIARIES, false);
    }
  };

  const handleSave = async () => {
    try {
      await formRef.value?.validate();
      await handleSaveForm();
    } catch (error: any) {
      console.log('⛔ Validation error: ', error.message);
    }
  };

  const handleShowForm = () => {
    handleIsEditFormSpecific(FormComponentEnum.MODULE_TREASURY_BENEFICIARIES, false);
  };

  const handleAddEmails = () => {
    formState.value.emails.push({
      email: undefined,
    });
  };

  const handleRemoveEmails = (index: number) => {
    formState.value.emails.splice(index, 1);
  };

  const fetchModuleBeneficiaries = () => {
    if (supplierBankInformation.value && supplierBankInformation.value?.type_document?.id) {
      const getValue = (source: any, path: string, defaultValue: any = null) =>
        path.split('.').reduce((acc, key) => acc?.[key], source) || defaultValue;

      const data = supplierBankInformation.value;

      formState.value = {
        type_document_id: getValue(data, 'type_document.id'),
        document_number: getValue(data, 'document_number', ''),
        deduction_percentage: getValue(data, 'deduction_percentage', 0),
        national_bank_account: getValue(data, 'national_bank_account', ''),
        main_name: getValue(data, 'main_name', ''),
        main_email: getValue(data, 'main_email', ''),
        accounts_national_bank_account_number: getValue(
          data,
          'bank_accounts.0.bank_account_number',
          ''
        ),
        accounts_national_type_bank_accounts_id: getValue(
          data,
          'bank_accounts.0.type_bank_account.id'
        ),
        accounts_foreign_bank_account_number: getValue(
          data,
          'bank_accounts.1.bank_account_number',
          ''
        ),
        accounts_foreign_type_bank_accounts_id: getValue(
          data,
          'bank_accounts.1.type_bank_account.id'
        ),
        sub_classification_supplier_id: subClassificationSupplierId.value || null,
        emails: getValue(data, 'beneficiary_emails', []).map((e: any) => ({
          email: e.email,
        })),
      };

      handleDisabledSpecific(FormComponentEnum.MODULE_TREASURY_BENEFICIARIES, false);
      handleIsEditFormSpecific(FormComponentEnum.MODULE_TREASURY_BENEFICIARIES, true);
      handleSavedFormSpecific(FormComponentEnum.MODULE_TREASURY_BENEFICIARIES, true);
      handleShowFormSpecific(FormComponentEnum.MODULE_TREASURY_BENEFICIARIES, true);
    } else {
      handleDisabledSpecific(FormComponentEnum.MODULE_TREASURY_BENEFICIARIES, true);
      if (supplierBankInformation.value.id) {
        handleDisabledSpecific(FormComponentEnum.MODULE_TREASURY_BENEFICIARIES, false);
      }
      handleIsEditFormSpecific(FormComponentEnum.MODULE_TREASURY_BENEFICIARIES, false);
      handleSavedFormSpecific(FormComponentEnum.MODULE_TREASURY_BENEFICIARIES, false);
      handleShowFormSpecific(FormComponentEnum.MODULE_TREASURY_BENEFICIARIES, false);
    }
  };

  watch(
    () => supplierBankInformation.value,
    () => {
      fetchModuleBeneficiaries();
    },
    { deep: true, immediate: true }
  );

  return {
    formState,
    formRef,
    formRules,
    showFormComponent,
    typeDocument,
    getShowFormComponent,
    getIsEditFormComponent,
    getLoadingFormComponent,
    getLoadingButtonComponent,
    getDisabledComponent,
    getListItem,
    getIsFormValid,
    handleClose,
    handleSave,
    handleShowForm,
    handleDisabledSpecific,
    handleAddEmails,
    handleRemoveEmails,
  };
}
