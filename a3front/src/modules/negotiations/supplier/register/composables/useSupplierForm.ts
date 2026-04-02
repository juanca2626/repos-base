import { useRoute } from 'vue-router';
import { useSupplierRouteAction } from '@/modules/negotiations/supplier/composables/useSupplierRouteAction';
import { useSupplierFormStoreFacade } from '@/modules/negotiations/supplier/register/composables/useSupplierFormStoreFacade';

export function useSupplierForm() {
  const route = useRoute();

  const { handleBackToList } = useSupplierRouteAction();

  const { isLoadingForm, isFormEditMode, resetFormData } = useSupplierFormStoreFacade();

  // Método para cancelar el formulario
  const handleCancel = () => {
    resetFormData();
    handleBackToList();
  };

  const getRouteSupplierId = (): number => {
    return Number(route.params.id);
  };

  return {
    isLoadingForm,
    handleCancel,
    isFormEditMode,
    getRouteSupplierId,
  };
}
