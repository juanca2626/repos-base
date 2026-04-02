import { storeToRefs } from 'pinia';
import { onMounted, watch } from 'vue';
import debounce from 'lodash/debounce';

import { useSupplierClassificationStore } from '@/modules/negotiations/supplier-new/store/negotiations/supplier-registration/supplier-classification.store';
import { useSupplierGlobalComposable } from '@/modules/negotiations/supplier-new/composables/form/supplier-global.composable';
import { useSupplierModulesComposable } from '@/modules/negotiations/supplier-new/composables/supplier-modules.composable';
import { useSupplierService } from '@/modules/negotiations/supplier-new/service/supplier.service';
import { handleErrorResponse } from '@/modules/negotiations/api/responseApi';
import { useSupplierClassificationGlobalComposable } from '@/modules/negotiations/supplier-new/composables/supplier-classification-global.composable';

export function useSupplierClassificationComposable() {
  const { supplierId, supplier, subClassificationSupplierId, showSubForm } =
    useSupplierGlobalComposable();
  const { loadSupplierModules } = useSupplierModulesComposable();
  const { supplierClassification: supplierClassificationId } =
    useSupplierClassificationGlobalComposable();

  const supplierClassificationStore = useSupplierClassificationStore();

  const { updateOrCreateSupplierClassification } = useSupplierService;

  const {
    supplierClassification,
    supplierSubClassification,
    supplierSubClassificationId,
    supplierSubClassificationLoaded,
    loadingForm,
    loadingButton,
    disabledButton,
    showList,
    isLoadingFormGeneral,
  } = storeToRefs(supplierClassificationStore);

  const { loadSupplierSubClassification } = supplierClassificationStore;

  const getRequestData = () => ({
    supplier_sub_classification_id: supplierSubClassificationId.value,
  });

  const createNameGetter = (collection: any, idRef: any) => {
    return () => {
      const option = collection.value.find((opt: any) => opt.id === idRef.value);
      return option ? option.name : '';
    };
  };

  const getNameClassification = createNameGetter(supplierClassification, supplierClassificationId);
  const getNameSubClassification = createNameGetter(
    supplierSubClassification,
    supplierSubClassificationId
  );

  const loadSupplierSubClassificationData = async () => {
    try {
      supplierSubClassificationLoaded.value = true;
      const response = await loadSupplierSubClassification();

      if (response) {
        supplierClassification.value = response.supplierClassification || [];

        console.log('Supplier Classification Data Loaded:', supplierClassificationId.value);
        if (supplierClassificationId.value) {
          supplierSubClassification.value = (response.supplierSubClassification || []).filter(
            (item: any) => item.supplier_classification_id === supplierClassificationId.value
          );
        } else {
          supplierSubClassification.value = response.supplierSubClassification || [];
        }

        supplierSubClassificationId.value =
          supplier.value && supplier.value.supplier_sub_classification
            ? supplier.value.supplier_sub_classification.id || null
            : null;

        if (supplierId.value) {
          showList.value = true;
          showSubForm.value = true;
        }
      }

      return response;
    } catch (error) {
      console.error('Error al cargar las clasificaciones:', error);
      supplierClassification.value = [];
      supplierSubClassification.value = [];
      supplierSubClassificationLoaded.value = false;
      return null;
    } finally {
      isLoadingFormGeneral.value = false;
    }
  };

  const handleEditForm = () => {
    showList.value = false;
    showSubForm.value = showList.value;
  };

  const handleCompleteForm = async () => {
    try {
      loadingForm.value = true;
      loadingButton.value = true;
      const request = getRequestData();

      if (!request.supplier_sub_classification_id) {
        console.error('Debe seleccionar un subtipo de proveedor');
        loadingButton.value = false;
        return;
      }

      const response = await updateOrCreateSupplierClassification({
        ...request,
        supplier_id: supplierId.value,
      });

      if (response && response.success) {
        const { data } = response;
        supplierId.value = data.id || supplierId.value;
        subClassificationSupplierId.value = data.supplier_sub_classification_id;
        supplierClassificationId.value =
          data.supplier_sub_classification.supplier_classification.id;
        await loadSupplierModules();

        showList.value = true;
        showSubForm.value = true;
      } else {
        handleErrorResponse();
        console.error(response?.message || 'Error al guardar la clasificación del proveedor');
      }
    } catch (error) {
      handleErrorResponse();
      console.error('Error al completar el formulario:', error);
    } finally {
      loadingButton.value = false;
      loadingForm.value = false;
    }
  };

  watch(supplierSubClassificationId, (value) => {
    disabledButton.value = !value;
  });

  const debouncedClassificationChange = debounce(async () => {
    loadingForm.value = true;
    showList.value = false;
    showSubForm.value = false;
    supplierId.value = undefined;
    supplier.value = undefined;

    await Promise.all([loadSupplierSubClassificationData(), loadSupplierModules()]);

    loadingForm.value = false;
  }, 100);

  onMounted(async () => {
    if (!supplierSubClassificationLoaded.value) {
      isLoadingFormGeneral.value = true;
      await loadSupplierSubClassificationData();
    }
  });

  return {
    // State
    supplierClassification,
    supplierSubClassification,
    loadingForm,
    disabledButton,
    loadingButton,
    showList,
    supplierClassificationId,
    supplierSubClassificationId,
    isLoadingFormGeneral,

    // Getters
    getNameClassification,
    getNameSubClassification,

    // Actions
    handleEditForm,
    handleCompleteForm,
    loadSupplierSubClassificationData,
    debouncedClassificationChange,
  };
}
