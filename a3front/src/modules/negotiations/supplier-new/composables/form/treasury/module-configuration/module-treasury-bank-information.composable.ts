import type { Rule } from 'ant-design-vue/es/form';
import { storeToRefs } from 'pinia';
import { computed, onMounted, watch } from 'vue';
import { useSupplierGlobalComposable } from '@/modules/negotiations/supplier-new/composables/form/supplier-global.composable';
import { handleError, handleCompleteResponse } from '@/modules/negotiations/api/responseApi';
import { useSupplierService } from '@/modules/negotiations/supplier-new/service/supplier.service';
import { FormComponentEnum } from '@/modules/negotiations/supplier-new/enums/form-component.enum';
import { useModuleTreasuryBankInformationStore } from '@/modules/negotiations/supplier-new/store/treasury/module-configuration/module-treasury-bank-information.store';
import slugify from 'slugify';

export function useModuleTreasuryBankInformationComponenComposable() {
  const { subClassificationSupplierId } = useSupplierGlobalComposable();

  const {
    supplierId,
    supplierBankInformationId,
    supplierBankInformation,
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

  const {
    updateSupplierBankInformation,
    createSupplierBankInformation,
    showSupplierBankInformationModule,
  } = useSupplierService;

  const moduleTreasuryBankInformationStore = useModuleTreasuryBankInformationStore();

  const {
    formState,
    initialFormData,
    formRef,
    countries,
    states,
    typeBankAccount,
    bank,
    typeDocument,
    stateDisabled,
  } = storeToRefs(moduleTreasuryBankInformationStore);

  const { fetchModuleBankInformationLocationsResource, fetchModuleBankInformationResource } =
    moduleTreasuryBankInformationStore;

  const formRules: Record<string, Rule[]> = {
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
  };

  const getListItem = computed(() => {
    const formatFieldValue = (fieldKey: string, fieldValue: number) => {
      const sourceList: Record<string, any[]> = {
        bank_id: bank.value,
        country_id: countries.value,
        state_id: states.value,
        accounts_national_type_bank_accounts_id: typeBankAccount.value,
        accounts_foreign_type_bank_accounts_id: typeBankAccount.value,
      };

      const matchedItem = sourceList[fieldKey]?.find((item: any) => item.id === fieldValue) || {};
      return matchedItem.name || '';
    };

    const fields = [
      { key: 'bank_id', label: 'Banco:', format: formatFieldValue },
      { key: 'branch_office', label: 'Sucursal:' },
      { key: 'address', label: 'Dirección:' },
      { key: 'country_id', label: 'País:', format: formatFieldValue },
      { key: 'state_id', label: 'Departamento/Estado:', format: formatFieldValue },
      { key: 'number_aba', label: 'Número ABA:' },
      { key: 'accounts_national_bank_account_number', label: 'Cuenta nacional:' },
      {
        key: 'accounts_national_type_bank_accounts_id',
        label: 'Tipo cuenta nacional:',
        format: formatFieldValue,
      },
      { key: 'accounts_foreign_bank_account_number', label: 'Cuenta extranjera:' },
      {
        key: 'accounts_foreign_type_bank_accounts_id',
        label: 'Tipo cuenta extranjera:',
        format: formatFieldValue,
      },
    ];

    return fields.map(({ key, label, format }) => ({
      title: label,
      value: format ? format(key, formState.value[key]) : formState.value[key],
    }));
  });

  const getIsFormValid = computed(() => {
    const requiredFields = [
      'bank_id',
      'branch_office',
      'address',
      'country_id',
      'state_id',
      'number_aba',
      'accounts_national_type_bank_accounts_id',
      'accounts_national_bank_account_number',
      'accounts_foreign_type_bank_accounts_id',
      'accounts_foreign_bank_account_number',
    ];
    return requiredFields.every((key) => formState.value[key]);
  });

  const getRequestData = () => {
    const {
      bank_id,
      branch_office,
      address,
      country_id,
      state_id,
      number_aba,
      accounts_national_bank_account_number,
      accounts_national_type_bank_accounts_id,
      accounts_foreign_bank_account_number,
      accounts_foreign_type_bank_accounts_id,
    } = formState.value;

    return {
      bank_id,
      branch_office,
      address,
      country_id,
      state_id,
      number_aba,
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
    };
  };

  const handleClose = () => {
    const isValid = getIsFormValid.value;
    const isSaved = getSavedFormComponent.value(FormComponentEnum.MODULE_TREASURY_BANK_INFORMATION);

    if (isValid && isSaved) {
      handleIsEditFormSpecific(FormComponentEnum.MODULE_TREASURY_BANK_INFORMATION, true);
      return;
    }

    formState.value = { ...initialFormData.value };
    handleShowFormSpecific(FormComponentEnum.MODULE_TREASURY_BANK_INFORMATION, false);
  };

  const handleSaveForm = async () => {
    const request = getRequestData();

    try {
      handleLoadingFormSpecific(FormComponentEnum.MODULE_TREASURY_BANK_INFORMATION, true);
      handleLoadingButtonSpecific(FormComponentEnum.MODULE_TREASURY_BANK_INFORMATION, true);

      const { data } = supplierBankInformationId.value
        ? await updateSupplierBankInformation(supplierBankInformationId.value, request)
        : await createSupplierBankInformation(request);

      if (!supplierBankInformationId.value) {
        supplierBankInformationId.value = data.id;
      }

      handleCompleteResponse(data);

      setBankInformation(data);

      handleIsEditFormSpecific(FormComponentEnum.MODULE_TREASURY_BENEFICIARIES, true);
      handleSavedFormSpecific(FormComponentEnum.MODULE_TREASURY_BENEFICIARIES, true);
      handleDisabledSpecific(FormComponentEnum.MODULE_TREASURY_BENEFICIARIES, false);

      handleIsEditFormSpecific(FormComponentEnum.MODULE_TREASURY_BANK_INFORMATION, true);
    } catch (error: any) {
      handleError(error);
      console.log('⛔ Error save: ', error.message);
    } finally {
      handleLoadingFormSpecific(FormComponentEnum.MODULE_TREASURY_BANK_INFORMATION, false);
      handleLoadingButtonSpecific(FormComponentEnum.MODULE_TREASURY_BANK_INFORMATION, false);
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
    handleIsEditFormSpecific(FormComponentEnum.MODULE_TREASURY_BANK_INFORMATION, false);
  };

  const loadModuleBankInformationLocationsResource = async () => {
    const countryId = supplierBankInformation.value.country_id || undefined;

    const { data } = await fetchModuleBankInformationLocationsResource(countryId);
    countries.value = data.countries;

    if (countryId) {
      states.value = data.states;
      stateDisabled.value = true;
    }
  };

  const loadModuleBankInformationStatesResource = async () => {
    const countryId = formState.value.country_id || undefined;
    const { data } = await fetchModuleBankInformationLocationsResource(countryId, ['states']);
    states.value = data.states;

    stateDisabled.value = data.states.length <= 0;
  };

  const loadModuleBankInformationResource = async () => {
    const { data } = await fetchModuleBankInformationResource();
    typeBankAccount.value = data.typeBankAccount;
    bank.value = data.bank;
    typeDocument.value = data.typeDocument;
  };

  const setBankInformation = (data: any) => {
    if (data?.id) {
      supplierBankInformationId.value = data.id;
      supplierBankInformation.value = data;
      const getValue = (source: any, path: string, defaultValue: any = null) =>
        path.split('.').reduce((acc, key) => acc?.[key], source) || defaultValue;

      formState.value = {
        bank_id: getValue(data, 'bank.id'),
        branch_office: getValue(data, 'branch_office', ''),
        address: getValue(data, 'address', ''),
        country_id: getValue(data, 'country.id'),
        state_id: getValue(data, 'state.id'),
        number_aba: getValue(data, 'number_aba', ''),
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
        emails: getValue(supplierBankInformation.value, 'beneficiary_emails', []).map(
          (e: any) => e.email
        ),
      };

      handleDisabledSpecific(FormComponentEnum.MODULE_TREASURY_BANK_INFORMATION, false);
      handleIsEditFormSpecific(FormComponentEnum.MODULE_TREASURY_BANK_INFORMATION, true);
      handleSavedFormSpecific(FormComponentEnum.MODULE_TREASURY_BANK_INFORMATION, true);
      handleShowFormSpecific(FormComponentEnum.MODULE_TREASURY_BANK_INFORMATION, true);
    } else {
      handleDisabledSpecific(FormComponentEnum.MODULE_TREASURY_BANK_INFORMATION, true);
      if (supplierId.value) {
        handleDisabledSpecific(FormComponentEnum.MODULE_TREASURY_BANK_INFORMATION, false);
      }
      handleIsEditFormSpecific(FormComponentEnum.MODULE_TREASURY_BANK_INFORMATION, false);
      handleSavedFormSpecific(FormComponentEnum.MODULE_TREASURY_BANK_INFORMATION, false);
      handleShowFormSpecific(FormComponentEnum.MODULE_TREASURY_BANK_INFORMATION, false);
    }
  };

  const fetchModuleBankInformation = async () => {
    const { data } = await showSupplierBankInformationModule({
      sub_classification_supplier_id: subClassificationSupplierId.value,
    });

    setBankInformation(data);
  };

  const filterOption = (search: string, option: any) => {
    return slugify(option.name, { lower: true, replacement: '-', trim: true }).includes(
      slugify(search, { lower: true, replacement: '-', trim: true })
    );
  };

  onMounted(async () => {
    await Promise.all([
      fetchModuleBankInformation(),
      loadModuleBankInformationLocationsResource(),
      loadModuleBankInformationResource(),
    ]);
  });

  watch(
    () => formState.value.country_id,
    async () => {
      await loadModuleBankInformationStatesResource();
    },
    { deep: true, immediate: true }
  );

  return {
    formState,
    formRef,
    formRules,
    countries,
    states,
    typeBankAccount,
    bank,
    typeDocument,
    stateDisabled,
    showFormComponent,
    getShowFormComponent,
    getIsEditFormComponent,
    getLoadingFormComponent,
    getLoadingButtonComponent,
    getDisabledComponent,
    getListItem,
    getIsFormValid,
    filterOption,
    handleClose,
    handleSave,
    handleShowForm,
    handleDisabledSpecific,
  };
}
