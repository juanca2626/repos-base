import type { Rule } from 'ant-design-vue/es/form';
import { storeToRefs } from 'pinia';
import { computed, onMounted, watch } from 'vue';
import { useSupplierGlobalComposable } from '@/modules/negotiations/supplier-new/composables/form/supplier-global.composable';
import { handleError, handleCompleteResponse } from '@/modules/negotiations/api/responseApi';
import { useSupplierService } from '@/modules/negotiations/supplier-new/service/supplier.service';
import { FormComponentEnum } from '@/modules/negotiations/supplier-new/enums/form-component.enum';
import { useModuleSunatInformationStore } from '@/modules/negotiations/supplier-new/store/negotiations/module-configuration/module-sunat-information.store';

export function useModuleSunatInformationComposable() {
  const { subClassificationSupplierId } = useSupplierGlobalComposable();

  const {
    supplierTributaryInformationId,
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
    handleSavedFormSpecific,
  } = useSupplierGlobalComposable();

  const {
    createSupplierTributaryInformation,
    updateSupplierTributaryInformation,
    showSupplierBankInformation,
  } = useSupplierService;

  const moduleSunatInformationStore = useModuleSunatInformationStore();

  const { formState, initialFormData, formRef, typeTaxDocument, cities } = storeToRefs(
    moduleSunatInformationStore
  );

  const { loadSunatInformation } = moduleSunatInformationStore;

  const formRules: Record<string, Rule[]> = {
    types_tax_documents_id: [
      {
        required: true,
        message: 'Por favor, seleccione el tipo de documento tributario.',
        trigger: 'change',
      },
    ],
    city_id: [
      {
        required: true,
        message: 'Por favor, ingrese la ciudad.',
        trigger: 'change',
      },
    ],
  };

  const getListItem = computed(() => {
    const formatFieldValue = (fieldKey: string, fieldValue: number) => {
      const sourceList =
        fieldKey === 'types_tax_documents_id' ? typeTaxDocument.value : cities.value;
      const matchedItem: any = sourceList.find((item: any) => item.id === fieldValue) || {};
      return matchedItem.name || '';
    };

    const fields = [
      { key: 'types_tax_documents_id', label: 'Tipo de documento:', format: formatFieldValue },
      { key: 'city_id', label: 'Ubicación geográfica:', format: formatFieldValue },
    ];

    return fields.map(({ key, label, format }) => ({
      title: label,
      value: format ? format(key, formState.value[key]) : formState.value[key],
    }));
  });

  const getIsFormValid = computed(() => {
    const requiredFields = ['types_tax_documents_id', 'city_id'];
    return requiredFields.every((key) => formState.value[key]);
  });

  const getRequestData = () => {
    return {
      types_tax_documents_id: formState.value.types_tax_documents_id,
      city_id: formState.value.city_id,
      sub_classification_supplier_id: subClassificationSupplierId.value,
    };
  };

  const handleClose = () => {
    const isValid = getIsFormValid.value;
    const isSaved = getSavedFormComponent.value(FormComponentEnum.MODULE_SUNAT_INFORMATION);

    if (isValid && isSaved) {
      handleIsEditFormSpecific(FormComponentEnum.MODULE_SUNAT_INFORMATION, true);
      return;
    }

    formState.value = { ...initialFormData.value };
    handleShowFormSpecific(FormComponentEnum.MODULE_SUNAT_INFORMATION, false);
  };

  const handleSaveForm = async () => {
    const request = getRequestData();

    try {
      handleLoadingFormSpecific(FormComponentEnum.MODULE_SUNAT_INFORMATION, true);
      handleLoadingButtonSpecific(FormComponentEnum.MODULE_SUNAT_INFORMATION, true);

      const { data } = supplierTributaryInformationId.value
        ? await updateSupplierTributaryInformation(supplierTributaryInformationId.value, request)
        : await createSupplierTributaryInformation(request);

      if (!supplierTributaryInformationId.value) {
        supplierTributaryInformationId.value = data.id;
      }

      handleCompleteResponse(data);

      handleIsEditFormSpecific(FormComponentEnum.MODULE_SUNAT_INFORMATION, true);
      handleSavedFormSpecific(FormComponentEnum.MODULE_SUNAT_INFORMATION, true);
    } catch (error: any) {
      handleError(error);
      console.log('⛔ Error save: ', error.message);
    } finally {
      handleLoadingFormSpecific(FormComponentEnum.MODULE_SUNAT_INFORMATION, false);
      handleLoadingButtonSpecific(FormComponentEnum.MODULE_SUNAT_INFORMATION, false);
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
    handleIsEditFormSpecific(FormComponentEnum.MODULE_SUNAT_INFORMATION, false);
  };

  const fetchSunatInformation = async () => {
    const { data } = await showSupplierBankInformation({
      sub_classification_supplier_id: subClassificationSupplierId.value,
    });

    formState.value = {};
    formState.value = { ...initialFormData.value };

    if (data?.id) {
      supplierTributaryInformationId.value = data.id;
      formState.value = {
        types_tax_documents_id: data.types_tax_documents.types_tax_document_id,
        city_id: data.city.city_id,
      };
      handleIsEditFormSpecific(FormComponentEnum.MODULE_SUNAT_INFORMATION, true);
      handleSavedFormSpecific(FormComponentEnum.MODULE_SUNAT_INFORMATION, true);
      handleShowFormSpecific(FormComponentEnum.MODULE_SUNAT_INFORMATION, true);
    } else {
      handleIsEditFormSpecific(FormComponentEnum.MODULE_SUNAT_INFORMATION, false);
      handleSavedFormSpecific(FormComponentEnum.MODULE_SUNAT_INFORMATION, false);
      handleShowFormSpecific(FormComponentEnum.MODULE_SUNAT_INFORMATION, false);
    }
  };

  watch(
    () => subClassificationSupplierId.value,
    async () => {
      await fetchSunatInformation();
    },
    { deep: true, immediate: true }
  );

  onMounted(async () => {
    if (typeTaxDocument.value?.length > 0 && cities.value?.length > 0) return;

    try {
      const response = await loadSunatInformation();
      typeTaxDocument.value = response.typeTaxDocument;
      cities.value = response.cities;

      await fetchSunatInformation();
    } catch {
      typeTaxDocument.value = [];
      cities.value = [];
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
    typeTaxDocument,
    cities,
    handleClose,
    handleSave,
    handleShowForm,
  };
}
