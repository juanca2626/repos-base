import type { Rule } from 'ant-design-vue/es/form';
import { storeToRefs } from 'pinia';
import { computed, onMounted } from 'vue';
import { useSupplierGlobalComposable } from '@/modules/negotiations/supplier-new/composables/form/supplier-global.composable';
import { handleError, handleCompleteResponse } from '@/modules/negotiations/api/responseApi';
import { useSupplierService } from '@/modules/negotiations/supplier-new/service/supplier.service';
import { FormComponentEnum } from '@/modules/negotiations/supplier-new/enums/form-component.enum';
import { useSunatInformationStore } from '@/modules/negotiations/supplier-new/store/accounting/supplier-registration/sunat-information.store';
import { SupplierProgressModulesEnum } from '@/modules/negotiations/supplier-new/enums/supplier-progress-modules.enum';

export function useSunatInformationComposable() {
  const {
    isEditMode,
    supplierId,
    supplierTaxConditionId,
    supplierTaxCondition,
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
    handleProgressFormSpecific,
  } = useSupplierGlobalComposable();

  const { updateOrCreateSupplierTaxCondition } = useSupplierService;

  const sunatInformationStore = useSunatInformationStore();

  const { formState, initialFormData, formRef, taxRates, ivaOptions } =
    storeToRefs(sunatInformationStore);

  const { loadSunatInformation } = sunatInformationStore;

  const formRules: Record<string, Rule[]> = {
    iva_options_id: [
      { required: true, message: 'El campo Tipo de IGV / IVA es obligatorio', trigger: 'change' },
    ],
    tax_rates_id: [
      { required: true, message: 'El campo IGV / IVA es obligatorio', trigger: 'change' },
    ],
    last_billing_date: [
      {
        required: true,
        message: 'La Última fecha de facturación es obligatoria',
        trigger: 'change',
      },
    ],
  };

  onMounted(async () => {
    if (taxRates.value?.length > 0 && ivaOptions.value?.length > 0) return;

    try {
      const response = await loadSunatInformation();
      taxRates.value = response.taxRates;
      ivaOptions.value = response.ivaOptions;
    } catch {
      taxRates.value = [];
      ivaOptions.value = [];
    }
  });

  const getListItem = computed(() => {
    const formatFieldValue = (fieldKey: string, fieldValue: number) => {
      const sourceList = fieldKey === 'iva_options_id' ? ivaOptions.value : taxRates.value;
      const matchedItem: any = sourceList.find((item: any) => item.id === fieldValue) || {};
      return matchedItem.name || '';
    };

    const fieldDefinitions = [
      {
        key: 'iva_options_id',
        label: 'Tipo de IGV / IVA:',
        format: formatFieldValue,
      },
      {
        key: 'tax_rates_id',
        label: 'IGV / IVA:',
        format: formatFieldValue,
      },
      { key: 'last_billing_date', label: 'Última fecha de facturación:' },
    ];

    return fieldDefinitions.map(({ key, label, format }) => ({
      title: label,
      value: format ? format(key, formState.value[key]) : formState.value[key],
    }));
  });

  const getIsFormValid = computed(() => {
    const requiredFields = ['iva_options_id', 'tax_rates_id', 'last_billing_date'];
    return requiredFields.every((key) => formState.value[key]);
  });

  const getRequestData = () => {
    return {
      iva_options_id: formState.value.iva_options_id,
      tax_rates_id: formState.value.tax_rates_id,
      last_billing_date: formState.value.last_billing_date,
    };
  };

  const handleClose = () => {
    const isValid = getIsFormValid.value;
    const isSaved = getSavedFormComponent.value(FormComponentEnum.SUNAT_INFORMATION);

    if (isValid && isSaved) {
      handleIsEditFormSpecific(FormComponentEnum.SUNAT_INFORMATION, true);
      return;
    }

    formState.value = { ...initialFormData.value };
    handleShowFormSpecific(FormComponentEnum.SUNAT_INFORMATION, false);
  };

  const handleSaveForm = async () => {
    const request = getRequestData();

    try {
      handleLoadingFormSpecific(FormComponentEnum.SUNAT_INFORMATION, true);
      handleLoadingButtonSpecific(FormComponentEnum.SUNAT_INFORMATION, true);

      const { data } = await updateOrCreateSupplierTaxCondition(request, supplierId.value);

      if (!supplierTaxConditionId.value) {
        supplierTaxConditionId.value = data.id;
      }

      handleCompleteResponse(data);

      const { percentage = 0 } =
        data.supplier_progress_bar.find(
          (e: any) => e.module_id === SupplierProgressModulesEnum.TAX_CONDITIONS
        ) || {};
      handleProgressFormSpecific(FormComponentEnum.SUNAT_INFORMATION, true, percentage);
      handleIsEditFormSpecific(FormComponentEnum.SUNAT_INFORMATION, true);
      handleSavedFormSpecific(FormComponentEnum.SUNAT_INFORMATION, true);
    } catch (error: any) {
      handleError(error);
      console.log('⛔ Error save: ', error.message);
    } finally {
      handleLoadingFormSpecific(FormComponentEnum.SUNAT_INFORMATION, false);
      handleLoadingButtonSpecific(FormComponentEnum.SUNAT_INFORMATION, false);
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
    handleIsEditFormSpecific(FormComponentEnum.SUNAT_INFORMATION, false);
  };

  const initializeFormData = () => {
    try {
      const { tax_condition, supplier_progress_bar } = supplierTaxCondition.value || {};

      const {
        tax_rate = undefined,
        iva_option = undefined,
        last_billing_date = undefined,
      } = tax_condition;

      const data = { tax_rates_id: tax_rate.id, iva_options_id: iva_option.id, last_billing_date };
      initialFormData.value = { ...data };
      formState.value = { ...data };

      const nextProgress = supplier_progress_bar.find(
        (spb: any) => spb.supplier_progress_module_id === SupplierProgressModulesEnum.TAX_CONDITIONS
      );

      if (nextProgress?.percentage) {
        handleDisabledSpecific(FormComponentEnum.SUNAT_INFORMATION, false);
        handleSavedFormSpecific(FormComponentEnum.SUNAT_INFORMATION, true);
        handleIsEditFormSpecific(FormComponentEnum.SUNAT_INFORMATION, true);
        handleShowFormSpecific(FormComponentEnum.SUNAT_INFORMATION, true);
      }
    } catch (error: any) {
      console.log('⛔ Error al inicializar datos del formulario: ', error.message);
    }
  };

  onMounted(async () => {
    if (isEditMode.value) {
      initializeFormData();
    }
  });

  return {
    formState,
    formRef,
    formRules,
    showFormComponent,
    getShowFormComponent,
    getIsEditFormComponent,
    getLoadingFormComponent,
    getLoadingButtonComponent,
    getDisabledComponent,
    getListItem,
    getIsFormValid,
    taxRates,
    ivaOptions,
    handleClose,
    handleSave,
    handleShowForm,
    handleDisabledSpecific,
  };
}
