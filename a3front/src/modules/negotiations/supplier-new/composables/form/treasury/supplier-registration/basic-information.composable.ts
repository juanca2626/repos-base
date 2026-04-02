import type { Rule } from 'ant-design-vue/es/form';
import { storeToRefs } from 'pinia';
import { computed, onMounted } from 'vue';
import { useSupplierGlobalComposable } from '@/modules/negotiations/supplier-new/composables/form/supplier-global.composable';
import { handleError, handleCompleteResponse } from '@/modules/negotiations/api/responseApi';
import { useSupplierService } from '@/modules/negotiations/supplier-new/service/supplier.service';
import { FormComponentEnum } from '@/modules/negotiations/supplier-new/enums/form-component.enum';
import { useBasicInformationStore } from '@/modules/negotiations/supplier-new/store/treasury/supplier-registration/basic-information.store';
import { SupplierProgressModulesEnum } from '@/modules/negotiations/supplier-new/enums/supplier-progress-modules.enum';

export function useBasicInformationComposable() {
  const {
    isEditMode,
    supplierId,
    supplierPaymentId,
    supplierPayment,
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

  const { updateOrCreateSupplierPaymentTerm } = useSupplierService;

  const basicInformationStore = useBasicInformationStore();

  const { formState, initialFormData, formRef } = storeToRefs(basicInformationStore);

  const formRules: Record<string, Rule[]> = {
    credit_days: [
      { required: true, message: 'El campo días de crédito es obligatorio', trigger: 'change' },
    ],
    credit_days_sunat: [
      { required: true, message: 'La fecha de inicio SUNAT es obligatoria', trigger: 'change' },
    ],
    sunat_start_date: [
      {
        required: true,
        message: 'El campo días de crédito SUNAT es obligatorio',
        trigger: 'change',
      },
    ],
  };

  const getListItem = computed(() => {
    const fields = [
      { key: 'credit_days', label: 'Días de crédito:' },
      {
        key: 'credit_days_sunat',
        label: 'Fecha de inicio SUNAT:',
      },
      { key: 'sunat_start_date', label: 'Días de crédito SUNAT:' },
    ];

    return fields.map(({ key, label }) => ({
      title: label,
      value: formState.value[key],
    }));
  });

  const getIsFormValid = computed(() => {
    const requiredFields = ['credit_days', 'credit_days_sunat', 'sunat_start_date'];
    return requiredFields.every((key) => formState.value[key]);
  });

  const getRequestData = () => {
    return {
      credit_days: formState.value.credit_days,
      credit_days_sunat: formState.value.credit_days_sunat,
      sunat_start_date: formState.value.sunat_start_date,
    };
  };

  const handleClose = () => {
    const isValid = getIsFormValid.value;
    const isSaved = getSavedFormComponent.value(FormComponentEnum.BASIC_INFORMATION);

    if (isValid && isSaved) {
      handleIsEditFormSpecific(FormComponentEnum.BASIC_INFORMATION, true);
      return;
    }

    formState.value = { ...initialFormData.value };
    handleShowFormSpecific(FormComponentEnum.BASIC_INFORMATION, false);
  };

  const handleSaveForm = async () => {
    const request = getRequestData();

    try {
      handleLoadingFormSpecific(FormComponentEnum.BASIC_INFORMATION, true);
      handleLoadingButtonSpecific(FormComponentEnum.BASIC_INFORMATION, true);

      const { data } = await updateOrCreateSupplierPaymentTerm(request, supplierId.value);

      if (!supplierPaymentId.value) {
        supplierPaymentId.value = data.id;
      }

      handleCompleteResponse(data);

      handleIsEditFormSpecific(FormComponentEnum.BASIC_INFORMATION, true);
      handleSavedFormSpecific(FormComponentEnum.BASIC_INFORMATION, true);
      handleDisabledSpecific(FormComponentEnum.SUNAT_INFORMATION, false);
    } catch (error: any) {
      handleError(error);
      console.log('⛔ Error save: ', error.message);
    } finally {
      handleLoadingFormSpecific(FormComponentEnum.BASIC_INFORMATION, false);
      handleLoadingButtonSpecific(FormComponentEnum.BASIC_INFORMATION, false);
    }
  };

  const handleSave = async () => {
    try {
      await formRef.value?.validate();
      await handleSaveForm();
    } catch (error: any) {
      console.log('⛔ Validation error Basic Information: ', error.message);
    }
  };

  const handleShowForm = () => {
    handleIsEditFormSpecific(FormComponentEnum.BASIC_INFORMATION, false);
  };

  const initializeFormData = () => {
    try {
      const { payment_Term, supplier_progress_bar } = supplierPayment.value || {};

      const {
        credit_days = undefined,
        credit_days_sunat = undefined,
        sunat_start_date = undefined,
      } = payment_Term;

      const data = { credit_days, credit_days_sunat, sunat_start_date };
      initialFormData.value = { ...data };
      formState.value = { ...data };

      const nextProgress = supplier_progress_bar.find(
        (spb: any) => spb.supplier_progress_module_id === SupplierProgressModulesEnum.TAX_CONDITIONS
      );

      handleDisabledSpecific(FormComponentEnum.SUNAT_INFORMATION, false);
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
    handleClose,
    handleSave,
    handleShowForm,
    handleDisabledSpecific,
  };
}
